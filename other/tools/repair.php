<?php
/**********************************************************************************
* repair.php                                                                      *
***********************************************************************************
* SMF: Simple Machines Forum                                                      *
* Open-Source Project Inspired by Zef Hemel (zef@zefhemel.com)                    *
* =============================================================================== *
* Software Version:           SMF 2.0 Beta 2                                      *
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
	@set_magic_quotes_runtime(0);
	error_reporting(E_ALL);

	// Add slashes, as long as they aren't already being added.
	if (get_magic_quotes_gpc() == 0)
	{
		foreach ($_POST as $k => $v)
			$_POST[$k] = addslashes($v);
	}

	$_GET['a'] = (string) @$_GET['a'];
	$GLOBALS['this_url'] = 'http://' . (empty($_SERVER['HTTP_HOST']) ? $_SERVER['SERVER_NAME'] . (empty($_SERVER['SERVER_PORT']) || $_SERVER['SERVER_PORT'] == '80' ? '' : ':' . $_SERVER['SERVER_PORT']) : $_SERVER['HTTP_HOST']) . $_SERVER['PHP_SELF'];
}

function initialize_database()
{
	global $db_prefix;

	if (!file_exists(dirname(__FILE__) . '/Settings.php'))
	{
		echo '
		<div class="error_message">
			This utility was unable to find your Settings.php file!  Please make sure this script exists in the same directory as SMF is installed.
		</div>';

		show_footer();
		die;
	}

	// For now.
	require_once(dirname(__FILE__) . '/Settings.php');
	mysql_connect($db_server, $db_user, $db_passwd);
	mysql_select_db($db_name);
}

function db_query($string)
{
	echo 'I would run: <pre>', $string, '</pre>';

	if (substr(trim($string), 0, 6) == 'SELECT')
	{
		$ret = mysql_query($string);
		if (!$ret)
			die(mysql_error());
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

	initialize_database();

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
	{
		db_query("
			ALTER TABLE {$db_prefix}$table
			ADD COLUMN resort_id int(10) unsigned NOT NULL default 0");

		// Timeout?
	}

	if ($_GET['step'] <= 1)
	{
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
			// Timeout?

			$request = db_query($query . "
				LIMIT $_GET[start], $rows");
			while ($row = mysql_fetch_assoc($request))
				db_query("
					UPDATE {$db_prefix}$table
					SET resort_id = " . (++$_GET['start']) . "
					WHERE " . $table_data['primary'] . " = $row[id]
					LIMIT 1");

			if (mysql_num_rows($request) < $rows)
				break;
			mysql_free_result($request);
		}

		$_GET['start'] = 0;
	}

	if ($_GET['step'] <= 2)
	{
		foreach ($table_data['depends'] as $t => $dep)
		{
			$add = array();
			foreach ($dep as $c)
				$add[] = "ADD COLUMN resort_$c int(10) unsigned NOT NULL default 0";

			db_query("
				ALTER TABLE {$db_prefix}$t
				" . implode(',
				', $add));
		}

		// Timeout?
	}

	if ($_GET['step'] <= 3)
	{
		$rows = 100;

		while (true)
		{
			// Timeout?

			$request = db_query("
				SELECT $table_data[primary] AS id, resort_id
				FROM {$db_prefix}$table
				LIMIT $_GET[start], $rows");
			while ($row = mysql_fetch_assoc($request))
			{
				foreach ($table_data['depends'] as $t => $dep)
					foreach ($dep as $c)
					{
						db_query("
							UPDATE {$db_prefix}$t
							SET resort_$c = $row[resort_id]
							WHERE $c = $row[id]");
					}
			}

			if (mysql_num_rows($request) < $rows)
				break;
			mysql_free_result($request);
		}

		$_GET['start'] = 0;
	}

	if ($_GET['step'] <= 4)
	{
		db_query("
			UPDATE {$db_prefix}$table
			SET $table_data[primary] = resort_id");

		db_query("
			ALTER TABLE {$db_prefix}$table
			DROP COLUMN resort_id");

		// Timeout?
	}

	if ($_GET['step'] <= 5)
	{
		foreach ($table_data['depends'] as $t => $dep)
		{
			foreach ($dep as $c)
			{
				db_query("
					UPDATE {$db_prefix}$t
					SET $c = resort_$c");

				db_query("
					ALTER TABLE {$db_prefix}$t
					DROP COLUMN resort_$c");
			}

			// Timeout?
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
				background-color: #D4D4D4;
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
				background-color: #E1E1E1;
				margin: 1ex 4ex;
				padding: 1.5ex;
			}
			div.panel
			{
				border: 1px solid gray;
				background-color: #F0F0F0;
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
				background-color: #FAFAFA;
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

?>