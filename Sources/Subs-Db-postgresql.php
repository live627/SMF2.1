<?php
/**********************************************************************************
* Subs-Db-postgresql.php                                                          *
***********************************************************************************
* SMF: Simple Machines Forum                                                      *
* Open-Source Project Inspired by Zef Hemel (zef@zefhemel.com)                    *
* =============================================================================== *
* Software Version:           SMF 2.0 Alpha                                       *
* Software by:                Simple Machines (http://www.simplemachines.org)     *
* Copyright 2006-2007 by:     Simple Machines LLC (http://www.simplemachines.org) *
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
function smf_db_initiate($db_server, $db_name, $db_user, $db_passwd, &$db_prefix, $db_options = array())
{
	global $smfFunc, $mysql_set_mode;

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
	if (!isset($smfFunc['db_fetch_assoc']) || $smfFunc['db_fetch_assoc'] != 'postg_fetch_assoc')
		$smfFunc += array(
			'db_query' => 'smf_db_query',
			'db_insert' => 'db_insert',
			'db_insert_id' => 'db_insert_id',
			'db_fetch_assoc' => 'postg_fetch_assoc',
			'db_fetch_row' => 'postg_fetch_row',
			'db_free_result' => 'pg_free_result',
			'db_num_rows' => 'pg_num_rows',
			'db_data_seek' => 'db_data_seek',
			'db_num_fields' => 'pg_num_fields',
			'db_escape_string' => 'pg_escape_string',
			'db_unescape_string' => 'smf_postg_unescape_string',
			'db_server_info' => 'postg_version',
			'db_tablename' => 'mysql_tablename',
			'db_affected_rows' => 'db_affected_rows',
			'db_transaction' => 'smf_db_transaction',
			'db_error' => 'pg_last_error',
			'db_select_db' => 'postg_select_db',
			'db_title' => 'PostgreSQL',
			'db_sybase' => true,
			'db_case_sensitive' => true,
		);

	if (!empty($db_options['persist']))
		$connection = @pg_pconnect("host=$db_server dbname=$db_name user=$db_user password=$db_passwd");
	else
		$connection = @pg_connect("host=$db_server dbname=$db_name user=$db_user password=$db_passwd");

	// Something's wrong, show an error if its fatal (which we assume it is)
	if (!$connection)
	{
		if (!empty($db_options['non_fatal']))
		{
			return null;
		}
		else
		{
			db_fatal_error();
		}
	}

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

// Do nothing on postgreSQL
function db_fix_prefix (&$db_prefix, $db_name)
{
	return;
}

// Do a query.  Takes care of errors too.
function smf_db_query($identifier, $db_string, $file, $line, $connection = null)
{
	global $db_cache, $db_count, $db_connection, $db_show_debug, $modSettings, $db_last_result, $db_replace_result;

	// Decide which connection to use.
	$connection = $connection == null ? $db_connection : $connection;

	// Special queries that need processing.
	$replacements = array(
		'alter_table_boards' => array(
			'~(.+)~' => '',
		),
		'alter_table_icons' => array(
			'~(.+)~' => '',
		),
		'alter_table_smileys' => array(
			'~(.+)~' => '',
		),
		'attach_download_increase' => array(
			'~LOW_PRIORITY~' => '',
		),
		'boardindex_fetch_boards' => array(
			'~(.)$~' => '$1 ORDER BY b.board_order',
		),
		'messageindex_fetch_boards' => array(
			'~(.)$~' => '$1 ORDER BY b.board_order',
		),
		'select_message_icons' => array(
			'~(.)$~' => '$1 ORDER BY icon_order',
		),
	);

	if (isset($replacements[$identifier]))
		$db_string = preg_replace(array_keys($replacements[$identifier]), array_values($replacements[$identifier]), $db_string);

	if (trim($db_string) == '')
		return false;

	// Comments that are allowed in a query are preg_removed.
	static $allowed_comments_from = array(
		'~\s+~s', 
		'~/\*!40001 SQL_NO_CACHE \*/~', 
		'~/\*!40000 USE INDEX \([A-Za-z\_]+?\) \*/~',
		'~/\*!40100 ON DUPLICATE KEY UPDATE id_msg = \d+ \*/~',
	);
	static $allowed_comments_to = array(
		' ', 
		'', 
		'',
		'',
	);

	// One more query....
	$db_count = !isset($db_count) ? 1 : $db_count + 1;
	$db_replace_result = 0;

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

	// First, we clean strings out of the query, reduce whitespace, lowercase, and trim - so we can check it over.
	if (empty($modSettings['disableQueryCheck']))
	{
		$clean = '';
		$old_pos = 0;
		$pos = -1;
		while (true)
		{
			$pos = strpos($db_string, '\'', $pos + 1);
			if ($pos === false)
				break;
			$clean .= substr($db_string, $old_pos, $pos - $old_pos);

			while (true)
			{
				$pos1 = strpos($db_string, '\'', $pos + 1);
				$pos2 = strpos($db_string, '\\', $pos + 1);
				if ($pos1 === false)
					break;
				elseif ($pos2 == false || $pos2 > $pos1)
				{
					$pos = $pos1;
					break;
				}

				$pos = $pos2 + 1;
			}
			$clean .= '%s';

			$old_pos = $pos + 1;
		}
		$clean .= substr($db_string, $old_pos);
		$clean = trim(strtolower(preg_replace($allowed_comments_from, $allowed_comments_to, $clean)));

		// We don't use UNION in SMF, at least so far.  But it's useful for injections.
		if (strpos($clean, 'union') !== false && preg_match('~(^|[^a-z])union($|[^[a-z])~s', $clean) != 0)
			$fail = true;
		// Comments?  We don't use comments in our queries, we leave 'em outside!
		elseif (strpos($clean, '/*') > 2 || strpos($clean, '--') !== false || strpos($clean, ';') !== false)
			$fail = true;
		// Trying to change passwords, slow us down, or something?
		elseif (strpos($clean, 'set password') !== false && preg_match('~(^|[^a-z])set password($|[^[a-z])~s', $clean) != 0)
			$fail = true;
		elseif (strpos($clean, 'benchmark') !== false && preg_match('~(^|[^a-z])benchmark($|[^[a-z])~s', $clean) != 0)
			$fail = true;
		// Sub selects?  We don't use those either.
		elseif (preg_match('~\([^)]*?select~s', $clean) != 0)
			$fail = true;

		if (!empty($fail) && function_exists('log_error'))
		{
			log_error('Hacking attempt...' . "\n" . $db_string, $file, $line);
			fatal_error('Hacking attempt...', false);
		}
	}

	// Limits need to be a little different.
	$db_string = preg_replace('~\sLIMIT\s(\d+),\s*(\d+)\s*$~i', 'LIMIT $2 OFFSET $1', $db_string);

	$db_last_result = @pg_query($connection, $db_string);

	if ($db_last_result === false && $file !== false)
		$db_last_result = db_error($db_string, $file, $line, $connection);

	// Debugging.
	if (isset($db_show_debug) && $db_show_debug === true)
		$db_cache[$db_count]['t'] = array_sum(explode(' ', microtime())) - array_sum(explode(' ', $st));

	return $db_last_result;
}

function db_affected_rows($result = null)
{
	global $db_last_result, $db_replace_result;

	if ($db_replace_result)
		return $db_replace_result;
	elseif ($result == null && !$db_last_result)
		return 0;

	return pg_affected_rows($result == null ? $db_last_result : $result);
}

function db_insert_id($table, $field, $connection = null)
{
	global $db_connection, $smfFunc;

	if ($connection === false)
		$connection = $db_connection;

	// Try get the last ID for the auto increment field.
	$request = $smfFunc['db_query']('', "SELECT CURRVAL('{$table}_seq') AS insertID", __FILE__, __LINE__);
	if (!$request)
		return false;
	list ($lastID) = $smfFunc['db_fetch_row']($request);
	$smfFunc['db_free_result']($request);

	return $lastID;
}

// Do a transaction.
function smf_db_transaction($type = 'commit')
{
	global $db_connection;

	if ($type == 'begin')
		return @pg_query($db_connection, 'BEGIN');
	elseif ($type == 'rollback')
		return @pg_query($db_connection, 'ROLLBACK');
	elseif ($type == 'commit')
		return @pg_query($db_connection, 'COMMIT');

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
	$query_error = @pg_last_error($connection);
	$query_errno = 0;

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

// A PostgreSQL specific function for tracking the current row...
function postg_fetch_row($request, $counter = false)
{
	global $db_row_count;

	if ($counter !== false)
		return pg_fetch_row($request, $counter);

	// Reset the row counter...
	if (!isset($db_row_count[(int) $request]))
		$db_row_count[(int) $request] = 0;

	// Return the right row.
	return @pg_fetch_row($request, $db_row_count[(int) $request]++);
}

// Get an associative array
function postg_fetch_assoc($request, $counter = false)
{
	global $db_row_count;

	if ($counter !== false)
		return pg_fetch_assoc($request, $counter);

	// Reset the row counter...
	if (!isset($db_row_count[(int) $request]))
		$db_row_count[(int) $request] = 0;

	// Return the right row.
	return @pg_fetch_assoc($request, $db_row_count[(int) $request]++);
}

// Reset the pointer...
function db_data_seek($request, $counter)
{
	global $db_row_count;

	$db_row_count[(int) $request] = $counter;

	return true;
}

// Unescape an escaped string!
function smf_postg_unescape_string($string)
{
	return strtr($string, array("''" => "'"));
}

// For inserting data in a special way...
function db_insert($method = 'replace', $table, $columns, $data, $keys, $file = false, $line = false, $disable_trans = false)
{
	global $db_replace_result, $db_in_transact, $smfFunc;

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
			// Make a note that the replace actually overwrote.
			if (db_affected_rows() != 0)
			{
				unset($data[$k]);
				$db_replace_result = 2;
			}
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

// Dummy function really.
function postg_select_db($db_name, $db_connection)
{
	return true;
}

// Get the current version.
function postg_version()
{
	$version = pg_version();

	return $version['client'];
}

?>