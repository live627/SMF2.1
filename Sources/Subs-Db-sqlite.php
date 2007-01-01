<?php
/**********************************************************************************
* Subs-Db-sqlite.php                                                              *
***********************************************************************************
* SMF: Simple Machines Forum                                                      *
* Open-Source Project Inspired by Zef Hemel (zef@zefhemel.com)                    *
* =============================================================================== *
* Software Version:           SMF 2.0 Alpha                                       *
* Software by:                Simple Machines (http://www.simplemachines.org)     *
* Copyright 2006 by:          Simple Machines LLC (http://www.simplemachines.org) *
*           2001-2006 by:     Lewis Media (http://www.lewismedia.com)             *
* Support, News, Updates at:  http://www.simplemachines.org                       *
***********************************************************************************
* This program is free software; you may redistribute it and/or modify it under   *
* the terms of the provided license as published by Simple Machines LLC.          *
*                                                                                 *
* This program is distributed in the hope that it is and will be useful, but      *
* WITHOUT ANY WARRANTIES; without even any implied warranty of MERCHANTABILITY    *
* or FITNESS FOR A PARTICULAR PURPOSE.                                            *
*                                                                                 *
* See the "license.txt" file for details of the Simple Machines license.          *
* The latest version can always be found at http://www.simplemachines.org.        *
**********************************************************************************/
if (!defined('SMF'))
	die('Hacking attempt...');

/*	This file has all the main functions in it that relate to the database.

	// !!!

*/

// Initialize the database settings
function smf_db_initiate($db_server, $db_name, $db_user, $db_passwd, $db_prefix, $db_options = array())
{
	global $smfFunc, $mysql_set_mode, $db_in_transact;

	// Just some debugging code, make sure to remove it before release.
	$parameters = array(
		'server' => $db_server,
		'name' => $db_name,
		'user' => $db_user,
		'pass' => $db_passwd,
		'opts' => $db_options,
	);
	//echo '<pre>'; print_r($parameters); echo '</pre>';

	// Map some database specific functions, only do this once.
	if (!isset($smfFunc['db_fetch_assoc']) || $smfFunc['db_fetch_assoc'] != 'mysql_fetch_assoc')
		$smfFunc += array(
			'db_query' => 'smf_db_query',
			'db_fetch_assoc' => 'sqlite_fetch_array',
			'db_fetch_row' => 'smf_sqlite_fetch_row',
			'db_free_result' => 'smf_sqlite_free_result',
			'db_insert' => 'db_insert',
			'db_num_rows' => 'sqlite_num_rows',
			'db_data_seek' => 'sqlite_seek',
			'db_num_fields' => 'sqlite_num_fields',
			'db_escape_string' => 'sqlite_escape_string',
			'db_unescape_string' => 'smf_sqlite_unescape_string',
			'db_server_info' => 'sqlite_libversion',
   			'db_tablename' => 'mysql_tablename',
			'db_affected_rows' => 'db_affected_rows',
			'db_transaction' => 'smf_db_transaction',
			'db_error' => 'smf_sqlite_last_error',
			'db_select_db' => '',
			'db_title' => 'SQLite',
			'db_sybase' => true,
		);

	if (substr($db_name, -3) != 'db')
		$db_name .= '.db';

	if (!empty($db_options['persist']))
		$connection = @sqlite_popen($db_name, 0666, $sqlite_error);
	else
		$connection = @sqlite_open($db_name, 0666, $sqlite_error);

	// Something's wrong, show an error if its fatal (which we assume it is)
	if (!$connection)
	{
		if (!empty($db_options['non_fatal']))
		{
			return $sqlite_error;
		}
		else
		{
			db_fatal_error();
		}
	}
	$db_in_transact = false;

	// This is frankly stupid - stop SQLite returning alias names!
	@sqlite_query('PRAGMA short_column_names = 1', $connection);

	// Make some user defined functions!
	sqlite_create_function($connection, 'unix_timestamp', 'smf_udf_unix_timestamp', 0);
	sqlite_create_function($connection, 'inet_aton', 'smf_udf_inet_aton', 1);
	sqlite_create_function($connection, 'inet_ntoa', 'smf_udf_inet_ntoa', 1);
	sqlite_create_function($connection, 'find_in_set', 'smf_udf_find_in_set', 2);
	sqlite_create_function($connection, 'year', 'smf_udf_year', 1);
	sqlite_create_function($connection, 'month', 'smf_udf_month', 1);
	sqlite_create_function($connection, 'dayofmonth', 'smf_udf_dayofmonth', 1);

	return $connection;
}

// Extend the database functionality.
function db_extend ($type = 'extra')
{
	global $sourcedir, $db_type;

	require_once($sourcedir . '/Db' . strtoupper($type{0}) . substr($type, 1) . '-' . $db_type . '.php');
	$initFunc = 'db_' . $type . '_init';
	$initFunc();
}

// Fix up the prefix so it doesn't require the database to be selected.
function db_fix_prefix (&$db_prefix, $db_name)
{
	$db_prefix = is_numeric(substr($db_prefix, 0, 1)) ? $db_name . '.' . $db_prefix : '`' . $db_name . '`.' . $db_prefix;
}

// Do a query.  Takes care of errors too.
function smf_db_query($identifier, $db_string, $file, $line, $connection = null)
{
	global $db_cache, $db_count, $db_connection, $db_show_debug, $modSettings;

	// Decide which connection to use.
	$connection = $connection == null ? $db_connection : $connection;

	// Special queries that need processing.
	$replacements = array(
		'birthday_array' => array(
			'~DATE_FORMAT\(([^,]+),\s*([^\)]+)\s*\)~' => 'strftime($2, $1)'
		),
		'main_topics_query' => array(
			'~SUBSTRING~' => 'SUBSTR',
		),
		'memberlist_find_page' => array(
			'~SUBSTRING~' => 'SUBSTR',
		),
		'recent_show_all_temp' => array(
			'~SUBSTRING~' => 'SUBSTR',
		),
		'recent_is_topics_only' => array(
			'~SUBSTRING~' => 'SUBSTR',
		),
		'recent_get_everything' => array(
			'~SUBSTRING~' => 'SUBSTR',
		),
		'get_last_post' => array(
			'~SUBSTRING~' => 'SUBSTR',
		),
		'get_last_posts' => array(
			'~SUBSTRING~' => 'SUBSTR',
		),
		'truncate_table' => array(
			'~TRUNCATE~i' => 'DELETE FROM',
		),
		'user_activity_by_time' => array(
			'~HOUR\(FROM_UNIXTIME\((poster_time\s+\+\s+\d+)\)\)~' => 'strftime(\'%H\', datetime($1, \'unixepoch\'))',
		),
		'unread_fetch_topic_count' => array(
			'~\s*SELECT\sCOUNT\(DISTINCT\st\.id_topic\),\sMIN\(t\.id_last_msg\)(.+)$~is' => 'SELECT COUNT(id_topic), MIN(id_last_msg) FROM (SELECT DISTINCT t.id_topic, t.id_last_msg $1)',
		),
	);

	if (isset($replacements[$identifier]))
		$db_string = preg_replace(array_keys($replacements[$identifier]), array_values($replacements[$identifier]), $db_string);

	// SQLite doesn't support count(distinct).
	$db_string = trim($db_string);
	$db_string = preg_replace('~^\s*SELECT\s+?COUNT\(DISTINCT\s+?(.+?)\)(\s*AS\s*(.+?))*\s*(FROM.+)~is', 'SELECT COUNT($1) $2 FROM (SELECT DISTINCT $1 $4)', $db_string);

	// One more query....
	$db_count = !isset($db_count) ? 1 : $db_count + 1;

	// Debugging.
	if (isset($db_show_debug) && $db_show_debug === true)
	{
		// Initialize $db_cache if not already initialized.
		if (!isset($db_cache))
			$db_cache = array();

		if (!empty($_SESSION['debug_redirect']))
		{
			$db_cache = array_merge($_SESSION['debug_redirect'], $db_cache);
			$db_count = count($db_cache) + 1;
			$_SESSION['debug_redirect'] = array();
		}

		$db_cache[$db_count]['q'] = $db_string;
		$db_cache[$db_count]['f'] = $file;
		$db_cache[$db_count]['l'] = $line;
		$st = microtime();
	}

	// Do most of the substrings!
	$db_string = preg_replace('~SUBSTRING\(\s*\'~i', 'SUBSTR(\'', $db_string);

	$ret = @sqlite_query($db_string, $connection, SQLITE_BOTH, $err_msg);
	if ($ret === false && $file !== false)
	{
		$ret = db_error($db_string . '#!#' . $err_msg, $file, $line, $connection);
	}

	// Debugging.
	if (isset($db_show_debug) && $db_show_debug === true)
		$db_cache[$db_count]['t'] = array_sum(explode(' ', microtime())) - array_sum(explode(' ', $st));

	return $ret;
}

function db_affected_rows($connection = null)
{
	global $db_connection;

	return sqlite_changes($connection == null ? $db_connection : $connection);
}

function db_insert_id($table, $field, $connection = null)
{
	global $db_connection;

	// SQLite doesn't need the table or field information.
	return sqlite_last_insert_rowid($connection == null ? $db_connection : $connection);
}

// Keeps the connection handle.
function smf_sqlite_last_error()
{
	global $db_connection;

	$query_errno = sqlite_last_error($db_connection);
	return sqlite_error_string($query_errno);
}

// Do a transaction.
function smf_db_transaction($type = 'commit')
{
	global $db_connection, $db_in_transact;

	if ($type == 'begin')
	{
		$db_in_transact = true;
		return @sqlite_query('BEGIN', $db_connection);
	}
	elseif ($type == 'rollback')
	{
		$db_in_transact = false;
		return @sqlite_query('ROLLBACK', $db_connection);
	}
	elseif ($type == 'commit')
	{
		$db_in_transact = false;
		return @sqlite_query('COMMIT', $db_connection);
	}

	return false; 
}

// Database error!
function db_error($db_string, $file, $line, $connection = null)
{
	global $txt, $context, $sourcedir, $webmaster_email, $modSettings;
	global $forum_version, $db_connection, $db_last_error, $db_persist;
	global $db_server, $db_user, $db_passwd, $db_name, $db_show_debug, $ssi_db_user, $ssi_db_passwd;
	global $smfFunc;

	// Decide which connection to use
	$connection = $connection == null ? $db_connection : $connection;

	// This is the error message...
	$query_errno = sqlite_last_error($connection);
	$query_error = sqlite_error_string($query_errno);

	// Get the extra error message.
	$errStart = strrpos($db_string, '#!#');
	$query_error .= '<br />' . substr($db_string, $errStart + 3);
	$db_string = substr($db_string, 0, $errStart);

	// Error numbers:
	//    1016: Can't open file '....MYI'
	//    1030: Got error ??? from table handler.
	//    1034: Incorrect key file for table.
	//    1035: Old key file for table.
	//    1205: Lock wait timeout exceeded.
	//    1213: Deadlock found.
	//    2006: Server has gone away.
	//    2013: Lost connection to server during query.

	// Log the error.
	if ($query_errno != 1213 && $query_errno != 1205 && function_exists('log_error'))
		log_error($txt['database_error'] . ': ' . $query_error, 'database', $file, $line);

	// Database error auto fixing ;).
	if (function_exists('cache_get_data') && (!isset($modSettings['autoFixDatabase']) || $modSettings['autoFixDatabase'] == '1'))
	{
		// Force caching on, just for the error checking.
		$old_cache = @$modSettings['cache_enable'];
		$modSettings['cache_enable'] = '1';

		if (($temp = cache_get_data('db_last_error', 600)) !== null)
			$db_last_error = max(@$db_last_error, $temp);

		if (@$db_last_error < time() - 3600 * 24 * 3)
		{
			// We know there's a problem... but what?  Try to auto detect.
			if ($query_errno == 1030 && strpos($query_error, ' 127 ') !== false)
			{
				preg_match_all('~(?:[\n\r]|^)[^\']+?(?:FROM|JOIN|UPDATE|TABLE) ((?:[^\n\r(]+?(?:, )?)*)~s', $db_string, $matches);

				$fix_tables = array();
				foreach ($matches[1] as $tables)
				{
					$tables = array_unique(explode(',', $tables));
					foreach ($tables as $table)
					{
						// Now, it's still theoretically possible this could be an injection.  So backtick it!
						if (trim($table) != '')
							$fix_tables[] = '`' . strtr(trim($table), array('`' => '')) . '`';
					}
				}

				$fix_tables = array_unique($fix_tables);
			}
			// Table crashed.  Let's try to fix it.
			elseif ($query_errno == 1016)
			{
				if (preg_match('~\'([^\.\']+)~', $query_error, $match) != 0)
					$fix_tables = array('`' . $match[1] . '`');
			}
			// Indexes crashed.  Should be easy to fix!
			elseif ($query_errno == 1034 || $query_errno == 1035)
			{
				preg_match('~\'([^\']+?)\'~', $query_error, $match);
				$fix_tables = array('`' . $match[1] . '`');
			}
		}

		// Check for errors like 145... only fix it once every three days, and send an email. (can't use empty because it might not be set yet...)
		if (!empty($fix_tables))
		{
			// Subs-Admin.php for updateSettingsFile(), Subs-Post.php for sendmail().
			require_once($sourcedir . '/Subs-Admin.php');
			require_once($sourcedir . '/Subs-Post.php');

			// Make a note of the REPAIR...
			cache_put_data('db_last_error', time(), 600);
			if (($temp = cache_get_data('db_last_error', 600)) === null)
				updateSettingsFile(array('db_last_error' => time()));

			// Attempt to find and repair the broken table.
			foreach ($fix_tables as $table)
				$smfFunc['db_query']('', "
					REPAIR TABLE $table", false, false);

			// And send off an email!
			sendmail($webmaster_email, $txt['database_error'], $txt['tried_to_repair']);

			$modSettings['cache_enable'] = $old_cache;

			// Try the query again...?
			$ret = $smfFunc['db_query']('', $db_string, false, false);
			if ($ret !== false)
				return $ret;
		}
		else
			$modSettings['cache_enable'] = $old_cache;

		// Check for the "lost connection" or "deadlock found" errors - and try it just one more time.
		if (in_array($query_errno, array(1205, 1213, 2006, 2013)))
		{
			if (in_array($query_errno, array(2006, 2013)) && $db_connection == $connection)
			{
				// Are we in SSI mode?  If so try that username and password first
				if (SMF == 'SSI' && !empty($ssi_db_user) && !empty($ssi_db_passwd))
				{
					if (empty($db_persist))
						$db_connection = @mysql_connect($db_server, $ssi_db_user, $ssi_db_passwd);
					else
						$db_connection = @mysql_pconnect($db_server, $ssi_db_user, $ssi_db_passwd);
				}
				// Fall back to the regular username and password if need be
				if (!$db_connection)
				{
					if (empty($db_persist))
						$db_connection = @mysql_connect($db_server, $db_user, $db_passwd);
					else
						$db_connection = @mysql_pconnect($db_server, $db_user, $db_passwd);
				}

				if (!$db_connection || !@mysql_select_db($db_name, $db_connection))
					$db_connection = false;
			}

			if ($db_connection)
			{
				// Try a deadlock more than once more.
				for ($n = 0; $n < 4; $n++)
				{
					$ret = $smfFunc['db_query']('', $db_string, false, false);

					$new_errno = mysql_errno($db_connection);
					if ($ret !== false || in_array($new_errno, array(1205, 1213)))
						break;
				}

				// If it failed again, shucks to be you... we're not trying it over and over.
				if ($ret !== false)
					return $ret;
			}
		}
		// Are they out of space, perhaps?
		elseif ($query_errno == 1030 && (strpos($query_error, ' -1 ') !== false || strpos($query_error, ' 28 ') !== false || strpos($query_error, ' 12 ') !== false))
		{
			if (!isset($txt))
				$query_error .= ' - check database storage space.';
			else
			{
				if (!isset($txt['mysql_error_space']))
					loadLanguage('Errors');

				$query_error .= !isset($txt['mysql_error_space']) ? ' - check database storage space.' : $txt['mysql_error_space'];
			}
		}
	}

	// Nothing's defined yet... just die with it.
	if (empty($context) || empty($txt))
		die($query_error);

	// Show an error message, if possible.
	$context['error_title'] = $txt['database_error'];
	if (allowedTo('admin_forum'))
		$context['error_message'] = nl2br($query_error) . '<br />' . $txt['file'] . ': ' . $file . '<br />' . $txt['line'] . ': ' . $line;
	else
		$context['error_message'] = $txt['try_again'];

	// A database error is often the sign of a database in need of updgrade.  Check forum versions, and if not identical suggest an upgrade... (not for Demo/CVS versions!)
	if (allowedTo('admin_forum') && !empty($forum_version) && $forum_version != 'SMF ' . @$modSettings['smfVersion'] && strpos($forum_version, 'Demo') === false && strpos($forum_version, 'CVS') === false)
		$context['error_message'] .= '<br /><br />' . sprintf($txt['database_error_versions'], $forum_version, $modSettings['smfVersion']);

	if (allowedTo('admin_forum') && isset($db_show_debug) && $db_show_debug === true)
	{
		$context['error_message'] .= '<br /><br />' . nl2br($db_string);
	}

	// It's already been logged... don't log it again.
	fatal_error($context['error_message'], false);
}

// Insert some data...
function db_insert($method = 'replace', $table, $columns, $data, $keys, $file = false, $line = false, $disable_trans = false)
{
	global $db_in_transact, $smfFunc;

	if (!is_array($data[array_rand($data)]))
		$data = array($data);

	$priv_trans = false;
	if (count($data) > 1 && !$db_in_transact && !$disable_trans)
	{
		$smfFunc['db_transaction']('begin');
		$priv_trans = true;
	}

	// PostgreSQL doesn't support replace or insert ignore so we need to work around it.
	if ($method == 'replace')
	{
		// Try and update the entries.
		foreach ($data as $k => $entry)
		{
			$sql = "UPDATE $table SET";
			$where = '';
			foreach ($columns as $k1 => $v)
			{
				$sql .= " $v = {$entry[$k1]}, ";
				// Has it got a key?
				if (in_array($v, $keys))
					$where .= (empty($where) ? '' : ' AND') . " $v = {$entry[$k1]}";
			}
			$sql = substr($sql, 0, -2) . " WHERE $where";

			$smfFunc['db_query']('', $sql, $file, $line);
			if (db_affected_rows() != 0)
				unset($data[$k]);
		}
	}

	if (!empty($data))
	{
		foreach ($data as $entry)
			$smfFunc['db_query']('', "
				INSERT INTO $table
					(" . implode(', ', $columns) . ")
				VALUES
					(" . implode(', ', $entry) . ")", $method == 'ignore' ? false : $file, $line);
	}

	if ($priv_trans)
		$smfFunc['db_transaction']('commit');
}

// Doesn't do anything on sqlite!
function smf_sqlite_free_result($handle = false)
{
	return true;
}

// Make sure we return no string indexes!
function smf_sqlite_fetch_row($handle)
{
	return sqlite_fetch_array($handle, SQLITE_NUM);
}

// Unescape an escaped string!
function smf_sqlite_unescape_string($string)
{
	return strtr($string, array("''" => "'"));
}

// Emulate UNIX_TIMESTAMP.
function smf_udf_unix_timestamp()
{
	return strftime('%s', 'now');
}

// Emulate INET_ATON.
function smf_udf_inet_aton($ip)
{
	$chunks = explode('.', $ip);
	return @$chunks[0] * pow(256, 3) + @$chunks[1] * pow(256, 2) + @$chunks[2] * 256 + @$chunks[3];
}

// Emulate INET_NTOA.
function smf_udf_inet_ntoa($n)
{
	$t = array(0, 0, 0, 0);
	$msk = 16777216.0;
	$n += 0.0;
		if ($n < 1)
			return('0.0.0.0');

	for ($i = 0; $i < 4; $i++)
	{
		$k = (int) ($n / $msk);
		$n -= $msk * $k;
		$t[$i]= $k;
		$msk /= 256.0;
	};

	$a = join('.', $t);
	return($a);
} 

// Emulate FIND_IN_SET.
function smf_udf_find_in_set($find, $groups)
{
	foreach (explode(',', $groups) as $group)
	{  
		if ($group == $find)
			return true;
	}

	return false;
}

// Emulate YEAR.
function smf_udf_year($date)
{
	return substr($date, 0, 4);
}

// Emulate MONTH.
function smf_udf_month($date)
{
	return substr($date, 5, 2);
}

// Emulate DAYOFMONTH.
function smf_udf_dayofmonth($date)
{
	return substr($date, 8, 2);
}

?>
