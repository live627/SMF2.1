<?php
/**********************************************************************************
* repair.php                                                                      *
***********************************************************************************
* SMF: Simple Machines Forum                                                      *
* Open-Source Project Inspired by Zef Hemel (zef@zefhemel.com)                    *
* =============================================================================== *
* Software Version:           SMF 2.0 RC2                                         *
* Software by:                Simple Machines (http://www.simplemachines.org)     *
* Copyright 2006-2010 by:     Simple Machines LLC (http://www.simplemachines.org) *
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

initialize_inputs();

show_header();

if (function_exists('action_' . $_GET['a']))
	call_user_func('action_' . $_GET['a']);
else
	call_user_func('action_splash');

show_footer();

function initialize_inputs()
{
	// Turn off magic quotes runtime and enable error reporting.
	if (function_exists('set_magic_quotes_runtime'))
		@set_magic_quotes_runtime(0);
	error_reporting(E_ALL);

	// Add slashes, as long as they aren't already being added.
	if (function_exists('get_magic_quotes_gpc') && @get_magic_quotes_gpc() == 0)
		foreach ($_POST as $k => $v)
			$_POST[$k] = addslashes($v);

	$_GET['a'] = (string) @$_GET['a'];
	$GLOBALS['this_url'] = 'http://' . (empty($_SERVER['HTTP_HOST']) ? $_SERVER['SERVER_NAME'] . (empty($_SERVER['SERVER_PORT']) || $_SERVER['SERVER_PORT'] == '80' ? '' : ':' . $_SERVER['SERVER_PORT']) : $_SERVER['HTTP_HOST']) . $_SERVER['PHP_SELF'];
}

function db_query($id, $query, $values)
{
	global $smcFunc;

	$string = $smcFunc['db_quote']($query, $values);

	echo 'I would run: <pre>', $string, '</pre>';

	if (substr(trim($string), 0, 6) == 'SELECT')
	{
		$ret = $smcFunc['db_query']($id, $string, array());
		if (!$ret)
			exit($smcFunc['db_error']());
		return $ret;
	}
}

function action_splash()
{
	echo '
		<div class="panel">
			<h2>Repair options and tools</h2>

			<p>Please use this tool with caution and care.  Many of the more advanced options can cause all old and obselete links to stop working, or worse break things if anything goes wrong.  Please do not use them without having made a backup.</p>

			The following repair options are available:

			<ul>
				<li><a href="', $_SERVER['PHP_SELF'], '?a=paths">Detect and correct path and URL settings</a></li>
				<li><a href="', $_SERVER['PHP_SELF'], '?a=spell">Test the spell checker</a></li>
				<li><a href="', $_SERVER['PHP_SELF'], '?a=recount">Recount statistics</a></li>
				<li><a href="', $_SERVER['PHP_SELF'], '?a=attachments&amp;sa=encrypt">Encrypt all attachment filenames</a></li>
				<li><a href="', $_SERVER['PHP_SELF'], '?a=resort&amp;sa=members">Resort and renumber the members table</a></li>
				<li><a href="', $_SERVER['PHP_SELF'], '?a=resort&amp;sa=messages">Resort and renumber posts</a></li>
				<li><a href="', $_SERVER['PHP_SELF'], '?a=resort&amp;sa=topics">Resort and renumber topics</a></li>
				<li><a href="', $_SERVER['PHP_SELF'], '?a=personal_messages">Resort and renumber personal messages</a></li>
				<li><a href="', $_SERVER['PHP_SELF'], '?a=attachments&amp;sa=resort">Resort and renumber attachments</a></li>
				<li><a href="', $_SERVER['PHP_SELF'], '?a=resort&amp;sa=boards">Renumber boards</a></li>
				<li><a href="', $_SERVER['PHP_SELF'], '?a=resort&amp;sa=categories">Renumber categories</a></li>
			</ul>
		</div>';

// This file is just barebones.  I'm gonna write some stuff in it hilter-skilter.

// Fix Settings.php: $boarddir, $sourcedir, $boardurl, $db_*.
// Fix paths in settings: attachmentUploadDir, avatar_directory, avatar_url, simleys_url.
// Fix in themes: theme_dir, theme_url, images_url.

// Resort personal_messages: this affects id_pm on pm_recipients.
// Should sort on msgtime.
// This would probably make things faster... not needed too much, hopefully.

// Update all the statistics manually, including post groups and all boards ;).
// Very recommended if something was left un recounted, although recount should do it.

// Recount everyone's personal messages.
// This should be useful, but it's already in admin...

// Recount member's post counts and post groups.
// This should not be used unless you really want to.

// Convert attachments from encrypted --> unencrypted, and vice versa.
// This could be incredibly useful after doing an upgrade ;).

/*
Things for repair.php to do:
-------------------------------------------------------------------------------
Check for orphaned attachments
Check for orphaned avatars.
Check for orphaned posts.
Check for orphaned topics.
Check for orphaned boards. (by category.)
Check for orphaned boards. (by parent.)
Check for orphaned bans. (members don't exist, etc.)
Check for orphaned permissions/board permissions. (groups/boards.)
Check for orphaned calendar events.
Check for orphaned collapsed categories.
Check for orphaned personal messages. (from/to no one.)
Check for orphaned mark read data. (by topics, boards, and members.)
Check for orphaned notification data. (topic/board/member)
Check for orphaned poll data. (member/poll/topic)
Check for members with non-existent language files.
Check for members with non-existent smiley sets.
Check for messages with non-existent icons.
Check for orphaned moderators. (board/member)
Check for non-existent theme directories.
*/
}

function action_paths()
{
	addslashes(dirname(__FILE__));

	header('Location: ' . $GLOBALS['this_url']);
}

function action_resort()
{
	global $db_prefix;

	smc_compat_dtabase();

	$tables = array(
		'topics' => array(
			'primary' => 'id_topic',
			'join_alias' => 't',
			'join' => array(
				'messages AS m' => 'm.id_msg = t.id_first_msg',
			),
			'sort' => 'm.poster_time',
			'depends' => array(
				'messages' => array('id_topic'),
				'log_notify' => array('id_topic'),
				'log_topics' => array('id_topic'),
				'calendar' => array('id_topic'),
			),
		),
// Resort members: pm_ignore_list, buddies on members.
// Affects the members statistics. (duh!)
// Resorting members is not recommended and maybe shouldn't be available.
		'members' => array(
			'primary' => 'id_member',
			'join_alias' => 'mem',
			'join' => array(),
			'sort' => 'date_registered',
			'depends' => array(
				'attachments' => array('id_member'),
				'ban_items' => array('id_member'),
				'calendar' => array('id_member'),
				'collapsed_categories' => array('id_member'),
				'log_actions' => array('id_member'),
				'log_banned' => array('id_member'),
				'log_boards' => array('id_member'),
				'log_errors' => array('id_member'),
				'log_karma' => array('ID_TARGET', 'ID_EXECUTOR'),
				'log_mark_read' => array('id_member'),
				'log_notify' => array('id_member'),
				'log_online' => array('id_member'),
				'log_polls' => array('id_member'),
				'log_topics' => array('id_member'),
				'messages' => array('id_member'),
				'moderators' => array('id_member'),
				'personal_messages' => array('id_member_from'),
				'pm_recipients' => array('id_member'),
				'polls' => array('id_member'),
				'themes' => array('id_member'),
				'topics' => array('id_member_started', 'id_member_updated'),
			),
		),
		'messages' => array(
			'primary' => 'id_msg',
			'join_alias' => 'm',
			'join' => array(),
			'sort' => 'm.poster_time',
			'depends' => array(
				'attachments' => array('id_msg'),
				'topics' => array('id_last_msg', 'id_first_msg'),
				'boards' => array('id_last_msg'),
			),
		),
		'boards' => array(
			'primary' => 'id_board',
			'join_alias' => 'b',
			'join' => array(),
			'sort' => 'b.board_order',
			'depends' => array(
				'board_permissions' => array('id_board'),
				'calendar' => array('id_board'),
				'log_boards' => array('id_board'),
				'log_mark_read' => array('id_board'),
				'log_notify' => array('id_board'),
				'message_icons' => array('id_board'),
				'messages' => array('id_board'),
				'moderators' => array('id_board'),
				'topics' => array('id_board'),
			),
		),
		'categories' => array(
			'primary' => 'id_cat',
			'join_alias' => 'c',
			'sort' => 'c.cat_order',
			'depends' => array(
				'boards' => array('id_cat'),
				'collapsed_categories' => array('id_cat'),
			),
		),
	);

	if (!isset($_GET['sa']) || !isset($tables[$_GET['sa']]))
		header('Location: ' . $GLOBALS['this_url']);
	$table = $_GET['sa'];
	$table_data = $tables[$table];

	$start_time = microtime();

	$_GET['step'] = isset($_GET['step']) ? (int) $_GET['step'] : 0;
	$_GET['start'] = isset($_GET['start']) ? (int) $_GET['start'] : 0;

	if ($_GET['step'] <= 0)
		$smcFunc['db_add_column']($table, array('name' => 'resort_id', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => '0'));

	if ($_GET['step'] <= 1)
	{
		protectTimeOut('step=1');

		$rows = 500;

		$query = '
				SELECT ' . $table_data['join_alias'] . '.' . $table_data['primary'] . ' AS id
				FROM ' . $db_prefix . $table . ' AS ' . $table_data['join_alias'];

		foreach ($table_data['join'] as $t => $on)
			$query .= ', ' . $db_prefix . $t;
		if (!empty($table_data['join']))
			$query .= '
				WHERE ' . implode('
					AND', $table_data['join']);
		$query .= '
				ORDER BY ' . $table_data['sort'];

		while (true)
		{
			protectTimeOut('step=1&start=' . $_GET['start']);

			$request = $smcFunc['db_query']('', $query . '
				LIMIT {int:start}, {int:limit}',
				array(
					'start' => $_GET['start'],
					'limit' => $rows
			));
			while ($row = $smcFunc['db_fetch_assoc']($request))
				$smcFunc['db_query']('', '
					UPDATE {db_prefix}{raw:table}
					SET resort_id = {int:resort_id}
					WHERE {raw:column} = {raw:value}
					LIMIT 1',
					array(
						'table' => $table,
						'resort_id' => ++$_GET['start'],
						'column' => $table_data['primary'],
						'value' => $row['id'],
				));

			if ($smcFunc['db_num_rows']($request) < $rows)
				break;
			$smcFunc['db_free_result']($request);
		}

		$_GET['start'] = 0;
	}

	if ($_GET['step'] <= 2)
	{
		protectTimeOut('step=2');

		foreach ($table_data['depends'] as $t => $dep)
			foreach ($dep as $c)
				$smcFunc['db_add_column']($table, array('name' => 'resort_' . $c, 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => '0'));
	}

	if ($_GET['step'] <= 3)
	{
		$rows = 100;

		while (true)
		{
			protectTimeOut('step=3&start=' . $_GET['start']);

			$request = $smcFunc['db_query']('', '
				SELECT {raw:column_id} AS id, resort_id
				FROM {db_prefix}{raw:table}
				LIMIT {int:start}, {int:limit}',
				array(
					'table' => $table,
					'column_id' => $table_data['primary'],
					'start' => $_GET['start'],
					'limit' => $rows,
			));
			while ($row = $smcFunc['db_fetch_assoc']($request))
				foreach ($table_data['depends'] as $t => $dep)
					foreach ($dep as $c)
						$smcFunc['db_query']('', '
							UPDATE {db_prefix}{raw:table}
							SET resort_{raw:column} = {int:value}
							WHERE {raw:column} = {int:id}',
							array(
								'table' => $t,
								'column' => $c,
								'value' => $row['resort_id'],
								'id' => $row['id'],
						));
			if ($smcFunc['db_num_rows']($request) < $rows)
				break;
			$smcFunc['db_free_result']($request);
		}

		$_GET['start'] = 0;
	}

	if ($_GET['step'] <= 4)
	{
		protectTimeOut('step=4');

		$smcFunc['db_query']('', '
			UPDATE {db_prefix}{raw:table}
			SET {raw:column} = resort_id',
			array(
				'table' => $table,
				'column' => $table_data['primary'],
		));

		$smcFunc['db_remove_column']($table, 'resort_id');
	}

	if ($_GET['step'] <= 5)
	{
		protectTimeOut('step=5');

		foreach ($table_data['depends'] as $t => $dep)
		{
			foreach ($dep as $c)
			{
				$smcFunc['db_query']('', '
					UPDATE {db_prefix}{raw:table}
					SET {raw:column} = resort_{raw:column}',
					array(
						'table' => $t,
						'column' => $c
				));

				$smcFunc['db_remove_column']($t, 'resort_' . $c);
			}
		}
	}
}

function action_spell()
{
	@set_time_limit(3);

	if (!function_exists('pspell_new'))
	{
		echo '
		<div class="panel">
			<h2>Unable to continue!</h2>

			<p>The spell checking library required by SMF\'s spell checker has not been compiled into PHP.  Please contact your host and tell them that they need to get Aspell and then compile PHP with <tt>--with-pspell</tt>.</p>

			<a href="', $_SERVER['PHP_SELF'], '">Go back</a>
		</div>';
		return;
	}

	error_reporting(E_ALL);
	@ini_set('display_errors', 1);
	ob_implicit_flush();

	// With some versions of Windows Aspell, the first spell check link returned is broken.
	pspell_new('en');

	// Now get one in earnest.
	$test = pspell_new('en');

	echo '
		<div class="panel">
			<h2>Spell Checker Test</h2>
			<h4>If you see any error messages from PHP, the spell checker is not properly configured.</h2>

			<p>The spell checker thinks that &quot;mispelin&quot; is spelled ', pspell_check($test, 'mispelin') ? 'correctly' : 'incorrectly', '.  It also thinks that &quot;machines&quot; is spelled ', pspell_check($test, 'machines') ? 'correctly' : 'incorrectly', '.</p>

			<p>If your host doesn\'t trust this test, you can ask them to use the following code.  If the below does not work as expected, it is not configured properly - the expected output would be "<tt>pass pass</tt>".</p>

			<div class="code">
				&lt;?php<br />
				<br />
				<span class="comment">// Show any and all errors, just in case!</span><br />
				error_reporting(E_ALL);<br />
				<br />
				$test = pspell_new(\'en\');<br />
				<br />
				<span class="comment">// Some Windows builds of PHP won\'t return a good link the first time.</span><br />
				if (!$test)<br />
				&nbsp; &nbsp; $test = pspell_new(\'en\');<br />
				<br />
				<span class="comment">// Try both a misspelled and a correctly spell word, to make sure nothing funny is happening.</span><br />
				echo pspell_check($test, \'mispelin\') ? \'fail\' : \'pass\', \' \', pspell_check($test, \'machines\') ? \'pass\' : \'fail\';<br />
				<br />
				?&gt;
			</div>

			<a href="', $_SERVER['PHP_SELF'], '">Go back</a>
		</div>';
}

function show_header()
{
	global $start_time;
	$start_time = time();

	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<title>Repair Tool</title>
		<style type="text/css">
			body
			{
				font-family: Verdana, sans-serif;
				background-color: #d4d4d4;
				margin: 0;
			}
			body, td
			{
				font-size: 10pt;
			}
			div#header
			{
				background-color: white;
				padding: 22px 4% 12px 4%;
				font-family: Georgia, serif;
				font-size: xx-large;
				border-bottom: 1px solid black;
				height: 40px;
			}
			div#content
			{
				padding: 20px 30px;
			}
			div.error_message
			{
				border: 2px dashed red;
				background-color: #e1e1e1;
				margin: 1ex 4ex;
				padding: 1.5ex;
			}
			div.panel
			{
				border: 1px solid gray;
				background-color: #f0f0f0;
				margin: 1ex 0;
				padding: 1.2ex;
			}
			div.panel h2
			{
				margin: 0;
				margin-bottom: 0.5ex;
				padding-bottom: 3px;
				border-bottom: 1px dashed black;
				font-size: 14pt;
				font-weight: normal;
			}
			div.panel h3
			{
				margin: 0;
				margin-bottom: 2ex;
				font-size: 10pt;
				font-weight: normal;
			}
			form
			{
				margin: 0;
			}
			td.textbox
			{
				padding-top: 2px;
				font-weight: bold;
				white-space: nowrap;
				padding-right: 2ex;
			}

			div.code
			{
				margin: 1ex 3ex 2ex 3ex;
				padding: 3px;
				background-color: #fafafa;
				font-family: monospace;
				overflow: auto;
			}
			div.code span.comment
			{
				font-style: italic;
				color: #000066;
			}
		</style>
	</head>
	<body>
		<div id="header">
			', file_exists(dirname(__FILE__) . '/Themes/default/images/smflogo.gif') ? '<a href="http://www.simplemachines.org/" target="_blank"><img src="Themes/default/images/smflogo.gif" style="width: 250px; float: right;" alt="Simple Machines" border="0" /></a>
			' : '', '<div title="Ta-Kumsaw">Repair Tool</div>
		</div>
		<div id="content">';
}

function show_footer()
{
	echo '
		</div>
	</body>
</html>';
}

// Don't let the script timeout on us...
function protectTimeOut($request)
{
	global $startTime;

	@set_time_limit(300);

	if (array_sum(explode(' ', microtime())) - array_sum(explode(' ', $startTime)) < 10)
		return;

	echo '
		<em>This repair has paused to avoid overloading your server, please click continue.</em><br />
		<br />
		<form action="', $_SERVER['PHP_SELF'], '?action=', $_REQUEST['action'], '&', (isset($_REQUEST['sa']) ? 'sa=' . $_REQUEST['sa'] : '') . '&' . $request, '" method="post" name="autoSubmit">
			<input type="submit" value="Continue" class="button_submit" />
		</form>
		<script type="text/javascript"><!-- // --><![CDATA[
			window.onload = doAutoSubmit;
			var countdown = 3;

			function doAutoSubmit()
			{
				if (countdown == 0)
					document.autoSubmit.submit();
				else if (countdown == -1)
					return;

				document.autoSubmit.b.value = "Continue (" + countdown + ")";
				countdown--;

				setTimeout("doAutoSubmit();", 1000);
			}
		// ]]></script>';
	exit;
}

// Compat mode!
function smc_compat_initiate($db_server, $db_name, $db_user, $db_passwd, $db_prefix, $db_options = array())
{
	global $mysql_set_mod, $sourcedir, $db_connection, $db_prefix, $smcFunc;

	if (!empty($db_options['persist']))
		$db_connection = @mysql_pconnect($db_server, $db_user, $db_passwd);
	else
		$db_connection = @mysql_connect($db_server, $db_user, $db_passwd);

	// Something's wrong, show an error if its fatal (which we assume it is)
	if (!$db_connection)
	{
		if (!empty($db_options['non_fatal']))
			return null;
		else
		{
			if (file_exists($sourcedir . '/Subs-Auth.php'))
			{
				require_once($sourcedir . '/Subs-Auth.php');
				show_db_error();
			}
			exit('Sorry, SMF was unable to connect to database.');
		}
	}

	// Select the database, unless told not to
	if (empty($db_options['dont_select_db']) && !@mysql_select_db($db_name, $connection) && empty($db_options['non_fatal']))
	{
		if (file_exists($sourcedir . '/Subs-Auth.php'))
		{
			require_once($sourcedir . '/Subs-Auth.php');
			show_db_error();
		}
		exit('Sorry, SMF was unable to connect to database.');
	}
	else
		$db_prefix = is_numeric(substr($db_prefix, 0, 1)) ? $db_name . '.' . $db_prefix : '`' . $db_name . '`.' . $db_prefix;

	// Some core functions.
	function smf_db_replacement__callback($matches)
	{
		global $db_callback, $user_info, $db_prefix;

		list ($values, $connection) = $db_callback;

		if ($matches[1] === 'db_prefix')
			return $db_prefix;

		if ($matches[1] === 'query_see_board')
			return $user_info['query_see_board'];

		if ($matches[1] === 'query_wanna_see_board')
			return $user_info['query_wanna_see_board'];

		if (!isset($matches[2]))
			smf_db_error_backtrace('Invalid value inserted or no type specified.', '', E_USER_ERROR, __FILE__, __LINE__);

		if (!isset($values[$matches[2]]))
			smf_db_error_backtrace('The database value you\'re trying to insert does not exist: ' . htmlspecialchars($matches[2]), '', E_USER_ERROR, __FILE__, __LINE__);

		$replacement = $values[$matches[2]];

		switch ($matches[1])
		{
			case 'int':
				if (!is_numeric($replacement) || (string) $replacement !== (string) (int) $replacement)
					smf_db_error_backtrace('Wrong value type sent to the database. Integer expected. (' . $matches[2] . ')', '', E_USER_ERROR, __FILE__, __LINE__);
				return (string) (int) $replacement;
			break;

			case 'string':
			case 'text':
				return sprintf('\'%1$s\'', mysql_real_escape_string($replacement, $connection));
			break;

			case 'array_int':
				if (is_array($replacement))
				{
					if (empty($replacement))
						smf_db_error_backtrace('Database error, given array of integer values is empty. (' . $matches[2] . ')', '', E_USER_ERROR, __FILE__, __LINE__);

					foreach ($replacement as $key => $value)
					{
						if (!is_numeric($value) || (string) $value !== (string) (int) $value)
							smf_db_error_backtrace('Wrong value type sent to the database. Array of integers expected. (' . $matches[2] . ')', '', E_USER_ERROR, __FILE__, __LINE__);

						$replacement[$key] = (string) (int) $value;
					}

					return implode(', ', $replacement);
				}
				else
					smf_db_error_backtrace('Wrong value type sent to the database. Array of integers expected. (' . $matches[2] . ')', '', E_USER_ERROR, __FILE__, __LINE__);

			break;

			case 'array_string':
				if (is_array($replacement))
				{
					if (empty($replacement))
						smf_db_error_backtrace('Database error, given array of string values is empty. (' . $matches[2] . ')', '', E_USER_ERROR, __FILE__, __LINE__);

					foreach ($replacement as $key => $value)
						$replacement[$key] = sprintf('\'%1$s\'', mysql_real_escape_string($value, $connection));

					return implode(', ', $replacement);
				}
				else
					smf_db_error_backtrace('Wrong value type sent to the database. Array of strings expected. (' . $matches[2] . ')', '', E_USER_ERROR, __FILE__, __LINE__);
			break;

			case 'date':
				if (preg_match('~^(\d{4})-([0-1]?\d)-([0-3]?\d)$~', $replacement, $date_matches) === 1)
					return sprintf('\'%04d-%02d-%02d\'', $date_matches[1], $date_matches[2], $date_matches[3]);
				else
					smf_db_error_backtrace('Wrong value type sent to the database. Date expected. (' . $matches[2] . ')', '', E_USER_ERROR, __FILE__, __LINE__);
			break;

			case 'float':
				if (!is_numeric($replacement))
					smf_db_error_backtrace('Wrong value type sent to the database. Floating point number expected. (' . $matches[2] . ')', '', E_USER_ERROR, __FILE__, __LINE__);
				return (string) (float) $replacement;
			break;

			case 'identifier':
				// Backticks inside identifiers are supported as of MySQL 4.1. We don't need them for SMF.
				return '`' . strtr($replacement, array('`' => '', '.' => '')) . '`';
			break;

			case 'raw':
				return $replacement;
			break;

			default:
				smf_db_error_backtrace('Undefined type used in the database query. (' . $matches[1] . ':' . $matches[2] . ')', '', false, __FILE__, __LINE__);
			break;
		}
	}

	// Because this is just compat mode, this is good enough.
	function smf_db_query($execute = true, $db_string, $db_values)
	{
		global $db_callback, $db_connection;

		// Only bother if there's something to replace.
		if (strpos($db_string, '{') !== false)
		{
			// This is needed by the callback function.
			$db_callback = array($db_values, $db_connection);

			// Do the quoting and escaping
			$db_string = preg_replace_callback('~{([a-z_]+)(?::([a-zA-Z0-9_-]+))?}~', 'smf_db_replacement__callback', $db_string);

			// Clear this global variable.
			$db_callback = array();
		}

		// We actually make the query in compat mode.
		if ($execute === false)
			return $db_string;
		return mysql_query($db_string, $db_connection);
	}

	// Insert some data...
	function smf_db_insert($method = 'replace', $table, $columns, $data, $keys, $disable_trans = false)
	{
		global $smcFunc, $db_connection, $db_prefix;

		// With nothing to insert, simply return.
		if (empty($data))
			return;

		// Replace the prefix holder with the actual prefix.
		$table = str_replace('{db_prefix}', $db_prefix, $table);

		// Inserting data as a single row can be done as a single array.
		if (!is_array($data[array_rand($data)]))
			$data = array($data);

		// Create the mold for a single row insert.
		$insertData = '(';
		foreach ($columns as $columnName => $type)
		{
			// Are we restricting the length?
			if (strpos($type, 'string-') !== false)
				$insertData .= sprintf('SUBSTRING({string:%1$s}, 1, ' . substr($type, 7) . '), ', $columnName);
			else
				$insertData .= sprintf('{%1$s:%2$s}, ', $type, $columnName);
		}
		$insertData = substr($insertData, 0, -2) . ')';

		// Create an array consisting of only the columns.
		$indexed_columns = array_keys($columns);

		// Here's where the variables are injected to the query.
		$insertRows = array();
		foreach ($data as $dataRow)
			$insertRows[] = smf_db_query(true, $insertData, array_combine($indexed_columns, $dataRow));

		// Determine the method of insertion.
		$queryTitle = $method == 'replace' ? 'REPLACE' : ($method == 'ignore' ? 'INSERT IGNORE' : 'INSERT');

		// Do the insert.
		$smcFunc['db_query']('', '
			' . $queryTitle . ' INTO ' . $table . '(`' . implode('`, `', $indexed_columns) . '`)
			VALUES
				' . implode(',
				', $insertRows),
			array(
				'security_override' => true,
			)
		);
	}

	// Now, go functions, spread your love.
	$smcFunc['db_free_result'] = 'mysql_free_result';
	$smcFunc['db_fetch_row'] = 'mysql_fetch_row';
	$smcFunc['db_fetch_assoc'] = 'mysql_fetch_assoc';
	$smcFunc['db_num_rows'] = 'mysql_num_rows';
	$smcFunc['db_insert'] = 'smf_db_insert';
	$smcFunc['db_query'] = 'smf_db_query';
	$smcFunc['db_quote'] = 'smf_db_query';
	$smcFunc['db_error'] = 'mysql_error';

	return $db_connection;
}

function smc_compat_database($db_type, $db_server, $db_user, $db_passwd, $db_name)
{
	global $smcFunc, $db_connection, $modSettings;

	// Gonna need a lot of memory.
	if (@ini_get('memory_limit') < 128)
		@ini_set('memory_limit', '128M');
	@set_time_limit(300);
	ignore_user_abort(true);
	if (function_exists('apache_reset_timeout'))
		@apache_reset_timeout();

	// Attempt to make a connection.
	$db_connection = false;
	if (file_exists(dirname(__FILE__) . '/Settings.php'))
		require_once(dirname(__FILE__) . '/Settings.php');
	if (isset($sourcedir))
	{
		define('SMF', 1);

		if (empty($smcFunc))
			$smcFunc = array();

		// Default the database type to MySQL.
		if (empty($db_type) || !file_exists($sourcedir . '/Subs-Db-' . $db_type . '.php'))
			$db_type = 'mysql';

		require_once($sourcedir . '/Errors.php');
		require_once($sourcedir . '/Subs.php');
		require_once($sourcedir . '/Load.php');
		require_once($sourcedir . '/Security.php');
		require_once($sourcedir . '/Subs-Auth.php');

		// compat mode. Active!
		if (!file_exists($sourcedir . '/Subs-Db-' . $db_type . '.php') && $db_type == 'mysql')
		{
			// First try a persistent connection.
			$db_connection = smc_compat_initiate($db_server, $db_name, $db_user, $db_passwd, $db_prefix, array('non_fatal' => true, 'persist' => true));

			if (!$db_connection)
				$db_connection = smc_compat_initiate($db_server, $db_name, $db_user, $db_passwd, $db_prefix, array('non_fatal' => true));
		}
		else
		{
			require_once($sourcedir . '/Subs-Db-' . $db_type . '.php');
			$db_connection = smf_db_initiate($db_server, $db_name, $db_user, $db_passwd, $db_prefix, array('non_fatal' => true, 'persist' => true));

			if (!$db_connection)
				$db_connection = smf_db_initiate($db_server, $db_name, $db_user, $db_passwd, $db_prefix, array('non_fatal' => true));
		}
	}
	else
	{
		$db_connection = smc_compat_initiate($db_server, $db_name, $db_user, $db_passwd, $db_prefix, array('non_fatal' => true, 'persist' => true));

		if (!$db_connection)
			$db_connection = smc_compat_initiate($db_server, $db_name, $db_user, $db_passwd, $db_prefix, array('non_fatal' => true));
	}

	// No version?
	if (empty($smcFunc['db_get_version']) && function_exists('db_extend'))
		db_extend('extra');
	if (empty($smcFunc['db_get_version']))
	{
		$request = $smcFunc['db_query']('', '
			SELECT VERSION()',
			array(
		));
		list ($smcFunc['db_get_version']) = $smcFunc['db_fetch_row']($request);
		$smcFunc['db_free_result']($request);
	}

	// For create backup, we tell it to ignore security checks.
	$modSettings['disableQueryCheck'] = 1;

	return $db_connection;
}

// Compat array_combine
if (!function_exists('array_combine'))
{
	function array_combine($keys, $values)
	{
		$ret = array();
		if (($array_error = !is_array($keys) || !is_array($values)) || empty($values) || ($count=count($keys)) != count($values))
		{
			trigger_error('array_combine(): Both parameters should be non-empty arrays with an equal number of elements', E_USER_WARNING);

			if ($array_error)
				return;
			return false;
		}

		// Ensure that both arrays aren't associative arrays.
		$keys = array_values($keys);
		$values = array_values($values);

		for ($i=0; $i < $count; $i++)
			$ret[$keys[$i]] = $values[$i];

		return $ret;
	}
}

?>