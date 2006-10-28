<?php
/******************************************************************************
* ManageMaintenance.php                                                       *
*******************************************************************************
* SMF: Simple Machines Forum                                                  *
* Open-Source Project Inspired by Zef Hemel (zef@zefhemel.com)                *
* =========================================================================== *
* Software Version:           SMF 2.0 Alpha                                   *
* Software by:                Simple Machines (http://www.simplemachines.org) *
* Copyright 2001-2006 by:     Lewis Media (http://www.lewismedia.com)         *
* Support, News, Updates at:  http://www.simplemachines.org                   *
*******************************************************************************
* This program is free software; you may redistribute it and/or modify it     *
* under the terms of the provided license as published by Lewis Media.        *
*                                                                             *
* This program is distributed in the hope that it is and will be useful,      *
* but WITHOUT ANY WARRANTIES; without even any implied warranty of            *
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.                        *
*                                                                             *
* See the "license.txt" file for details of the Simple Machines license.      *
* The latest version can always be found at http://www.simplemachines.org.    *
******************************************************************************/
if (!defined('SMF'))
	die('Hacking attempt...');

/* /!!!

	void ManageMaintenance()
		// !!!

	void Maintenance()
		- shows a listing of maintenance options - including repair, recount,
		  optimize, database dump, clear logs, and remove old posts.
		- handles directly the tasks of clearing logs.
		- requires the admin_forum permission.
		- uses the maintain_forum admin area.
		- shows the maintain sub template of the Admin template.
		- accessed by ?action=admin;area=maintain.

	void ScheduledTasks()
		// !!!

	void EditTask()

	void TaskLog()
		// !!!

	void ConvertUtf8()
		- converts the data and database tables to UTF-8 character set.
		- requires the admin_forum permission.
		- uses the convert_utf8 sub template of the Admin template.
		- only works if UTF-8 is not the global character set.
		- supports all character sets used by SMF's language files.
		- redirects to ?action=admin;area=maintain after finishing.
		- is linked from the maintenance screen (if applicable).
		- accessed by ?action=admin;area=maintain;sa=convertutf8.

	void ConvertEntities()
		- converts HTML-entities to UTF-8 characters.
		- requires the admin_forum permission.
		- uses the convert_entities sub template of the Admin template.
		- only works if UTF-8 has been set as database and global character set.
		- is divided in steps of 10 seconds.
		- is linked from the maintenance screen (if applicable).
		- accessed by ?action=admin;area=maintain;sa=convertentities.

	void OptimizeTables()
		- optimizes all tables in the database and lists how much was saved.
		- requires the admin_forum permission.
		- uses the rawdata sub template (built in.)
		- shows as the maintain_forum admin area.
		- updates the optimize scheduled task such that the tables are not
		  automatically optimized again too soon.
		- accessed from ?action=admin;area=maintain;sa=optimize.

	void AdminBoardRecount()
		- recounts many forum totals that can be recounted automatically
		  without harm.
		- requires the admin_forum permission.
		- shows the maintain_forum admin area.
		- fixes topics with wrong numReplies.
		- updates the numPosts and numTopics of all boards.
		- recounts instantMessages but not unreadMessages.
		- repairs messages pointing to boards with topics pointing to
		  other boards.
		- updates the last message posted in boards and children.
		- updates member count, latest member, topic count, and message count.
		- redirects back to ?action=admin;area=maintain when complete.
		- accessed via ?action=admin;area=maintain;sa=recount.

	bool cacheLanguage(string template_name, string language, bool fatal, string theme_name)
		// !!
*/

// The maintenance access point.
function ManageMaintenance()
{
	global $txt, $db_prefix, $modSettings, $scripturl, $context, $options;

	// You absolutely must be an admin by here!
	isAllowedTo('admin_forum');

	// So many things you can - but frankly I won't let you - just these!
	$subActions = array(
		'cleancache' => 'Maintenance',
		'convertentities' => 'ConvertEntities',
		'convertutf8' => 'ConvertUtf8',
		'destroy' => 'Maintenance',
		'logs' => 'Maintenance',
		'general' => 'Maintenance',
		'optimize' => 'OptimizeTables',
		'recount' => 'AdminBoardRecount',
		'taskedit' => 'EditTask',
		'tasklog' => 'TaskLog',
		'tasks' => 'ScheduledTasks',
	);

	// Yep, sub-action time!
	if (isset($_REQUEST['sa']) && isset($subActions[$_REQUEST['sa']]))
		$context['sub_action'] = $_REQUEST['sa'];
	else
		$context['sub_action'] = 'general';

	// This uses admin tabs - as it should!
	$context['admin_tabs'] = array(
		'title' => &$txt['maintain_title'],
		'help' => '',
		'description' => $txt['maintain_info'],
		'tabs' => array(
			'general' => array(
				'title' => $txt['maintain_common'],
				'description' => $txt['maintain_common_desc'],
				'href' => $scripturl . '?action=admin;area=maintain;sa=general',
			),
			'tasks' => array(
				'title' => $txt['maintain_tasks'],
				'description' => $txt['maintain_tasks_desc'],
				'href' => $scripturl . '?action=admin;area=maintain;sa=tasks',
				'is_last' => true,
			),
		),
	);

	// Select the right tab based on the sub action.
	if (isset($context['admin_tabs']['tabs'][$context['sub_action']]))
	{
		$context['page_title'] = $context['admin_tabs']['tabs'][$context['sub_action']]['title'];
		$context['admin_tabs']['tabs'][$context['sub_action']]['is_selected'] = true;
	}

	// Finally fall through to what we are doing.
	$subActions[$context['sub_action']]();
}

// Miscellaneous maintenance..
function Maintenance()
{
	global $context, $txt, $db_prefix, $user_info, $db_character_set;
	global $modSettings, $cachedir, $smfFunc;

	if (isset($_GET['sa']) && $_GET['sa'] == 'logs')
	{
		// No one's online now.... MUHAHAHAHA :P.
		$smfFunc['db_query']("
			DELETE FROM {$db_prefix}log_online", __FILE__, __LINE__);

		// Dump the banning logs.
		$smfFunc['db_query']("
			DELETE FROM {$db_prefix}log_banned", __FILE__, __LINE__);

		// Start ID_ERROR back at 0 and dump the error log.
		$smfFunc['db_query']("
			TRUNCATE {$db_prefix}log_errors", __FILE__, __LINE__);

		// Clear out the spam log.
		$smfFunc['db_query']("
			DELETE FROM {$db_prefix}log_floodcontrol", __FILE__, __LINE__);

		// Clear out the karma actions.
		$smfFunc['db_query']("
			DELETE FROM {$db_prefix}log_karma", __FILE__, __LINE__);

		// Last but not least, the search logs!
		$smfFunc['db_query']("
			TRUNCATE {$db_prefix}log_search_topics", __FILE__, __LINE__);
		$smfFunc['db_query']("
			TRUNCATE {$db_prefix}log_search_messages", __FILE__, __LINE__);
		$smfFunc['db_query']("
			TRUNCATE {$db_prefix}log_search_results", __FILE__, __LINE__);

		updateSettings(array('search_pointer' => 0));

		$context['maintenance_finished'] = true;
	}
	elseif (isset($_GET['sa']) && $_GET['sa'] == 'destroy')
	{
		// Oh noes!
		echo '<html><head><title>', $context['forum_name'], ' deleted!</title></head>
			<body style="background-color: orange; font-family: arial, sans-serif; text-align: center;">
			<div style="margin-top: 8%; font-size: 400%; color: black;">Oh my, you killed ', $context['forum_name'], '!</div>
			<div style="margin-top: 7%; font-size: 500%; color: red;"><b>You lazy bum!</b></div>
			</body></html>';
		obExit(false);
	}
	elseif (isset($_GET['sa']) && $_GET['sa'] == 'cleancache' && is_dir($cachedir))
	{
		// Just wipe the whole cache directory!
		clean_cache();

		$context['maintenance_finished'] = true;
	}
	else
		$context['maintenance_finished'] = isset($_GET['done']);

	// Grab some boards maintenance can be done on.
	$result = $smfFunc['db_query']("
		SELECT b.ID_BOARD, b.name, b.childLevel, c.name AS catName, c.ID_CAT
		FROM {$db_prefix}boards AS b
			LEFT JOIN {$db_prefix}categories AS c ON (c.ID_CAT = b.ID_CAT)
		WHERE $user_info[query_see_board]", __FILE__, __LINE__);
	$context['categories'] = array();
	while ($row = $smfFunc['db_fetch_assoc']($result))
	{
		if (!isset($context['categories'][$row['ID_CAT']]))
			$context['categories'][$row['ID_CAT']] = array(
				'name' => $row['catName'],
				'boards' => array()
			);

		$context['categories'][$row['ID_CAT']]['boards'][] = array(
			'id' => $row['ID_BOARD'],
			'name' => $row['name'],
			'child_level' => $row['childLevel']
		);
	}
	$smfFunc['db_free_result']($result);

	$context['convert_utf8'] = (!isset($db_character_set) || $db_character_set !== 'utf8' || empty($modSettings['global_character_set']) || $modSettings['global_character_set'] !== 'UTF-8') && version_compare('4.1.2', preg_replace('~\-.+?$~', '', $smfFunc['db_server_info']())) <= 0;
	$context['convert_entities'] = isset($db_character_set, $modSettings['global_character_set']) && $db_character_set === 'utf8' && $modSettings['global_character_set'] === 'UTF-8';

	$context['sub_template'] = 'maintain';
	$context['page_title'] = $txt['maintain_title'];
	$context['admin_tabs']['tabs']['general']['is_selected'] = true;
}

// List all the scheduled task in place on the forum.
function ScheduledTasks()
{
	global $context, $txt, $db_prefix, $sourcedir, $smfFunc;

	// Mama, setup the template first - cause it's like the most important bit, like pickle in a sandwich.
	// ... ironically I don't like pickle. </grudge>
	$context['sub_template'] = 'view_scheduled_tasks';

	// Saving changes?
	if (isset($_REQUEST['save']) && isset($_POST['task']))
	{
		checkSession();

		// We'll recalculate the dates at the end!
		require_once($sourcedir . '/ScheduledTasks.php');

		// Enable and disable as required.
		$enablers = array(0);
		foreach ($_POST['task'] as $id => $enabled)
			if ($enabled)
				$enablers[] = (int) $id;

		// Do the update!
		$smfFunc['db_query']("
			UPDATE {$db_prefix}scheduled_tasks
			SET disabled = IF (ID_TASK IN (" . implode(', ', $enablers) . "), 0, 1)", __FILE__, __LINE__);

		// Pop along...
		CalculateNextTrigger();
	}

	// Want to run any of the tasks?
	if (isset($_REQUEST['run']) && isset($_POST['run_task']))
	{
		// Lets figure out which ones they want to run.
		$tasks = array();
		foreach($_POST['run_task'] AS $task => $dummy)
			$tasks[] = (int) $task;

		// Load up the tasks.
		$request = $smfFunc['db_query']("
			SELECT ID_TASK, task
			FROM {$db_prefix}scheduled_tasks
			WHERE ID_TASK IN (" . implode(', ', $tasks) . ")
			LIMIT " . count($tasks), __FILE__, __LINE__);
		
		// Lets get it on!
		require_once($sourcedir . '/ScheduledTasks.php');
		ignore_user_abort(true);
		while ($row = $smfFunc['db_fetch_assoc']($request))
		{
			$start_time = microtime();
			// The functions got to exist for us to use it.
			if (!function_exists('scheduled_' . $row['task']))
				continue;

			// Try to stop a timeout, this would be bad...
			@set_time_limit(300);
			if (function_exists('apache_reset_timeout'))
				apache_reset_timeout();

			// Do the task...
			$completed = call_user_func('scheduled_' . $row['task']);

			// Log that we did it ;)
			if ($completed)
			{
				$total_time = round(array_sum(explode(' ', microtime())) - array_sum(explode(' ', $start_time)), 3);
				$smfFunc['db_query']("
					INSERT INTO {$db_prefix}log_scheduled_tasks
						(ID_TASK, timeRun, timeTaken)
					VALUES
						($row[ID_TASK], " . time() . ", $total_time)", __FILE__, __LINE__);
			}

		}
	}

	// Get the tasks, all of them, now - dammit!
	$request = $smfFunc['db_query']("
		SELECT ID_TASK, nextTime, timeOffset, timeRegularity, timeUnit, disabled, task
		FROM {$db_prefix}scheduled_tasks", __FILE__, __LINE__);
	$context['tasks'] = array();
	while ($row = $smfFunc['db_fetch_assoc']($request))
	{
		// Find the next for regularity - don't offset as it's always server time!
		$offset = sprintf($txt['scheduled_task_reg_starting'], date("H:i", $row['timeOffset']));
		$repeating = sprintf($txt['scheduled_task_reg_repeating'], $row['timeRegularity'], $txt['scheduled_task_reg_unit_' . $row['timeUnit']]);

		$context['tasks'][] = array(
			'id' => $row['ID_TASK'],
			'function' => $row['task'],
			'name' => isset($txt['scheduled_task_' . $row['task']]) ? $txt['scheduled_task_' . $row['task']] : $row['task'],
			'desc' => isset($txt['scheduled_task_desc_' . $row['task']]) ? $txt['scheduled_task_desc_' . $row['task']] : '',
			'next_time' => $row['disabled'] ? $txt['scheduled_tasks_na'] : timeformat($row['nextTime'] == 0 ? time() : $row['nextTime']),
			'disabled' => $row['disabled'],
			'regularity' => $offset . ', ' . $repeating,
		);
	}

	// You see - Mike sucks - and can't work out how to do in C++ what PHP can do with it's eyes closed - pah! He no programmer ;)
	// TWlrZSBTdWNrcyBCYWxscyBhbmQgSXMgTXkgQmlhdGNoISE=
	$smfFunc['db_free_result']($request);
}

// Function for editing a task.
function EditTask()
{
	global $context, $txt, $db_prefix, $sourcedir, $smfFunc;

	// Just set up some lovely context stuff.
	$context['admin_tabs']['tabs']['tasks']['is_selected'] = true;
	$context['sub_template'] = 'edit_scheduled_tasks';
	$context['page_title'] = $txt['scheduled_task_edit'];

	// Cleaning...
	if (!isset($_GET['tid']))
		fatal_lang_error(1);
	$_GET['tid'] = (int) $_GET['tid'];

	// Saving?
	if (isset($_GET['save']))
	{
		checkSession();

		// We'll need this for calculating the next event.
		require_once($sourcedir . '/ScheduledTasks.php');

		// Do we have a valid offset?
		preg_match('~(\d{1,2}):(\d{1,2})~', $_POST['offset'], $matches);

		// If a half is empty then assume zero offset!
		if (!isset($matches[2]) || $matches[2] > 59)
			$matches[2] = 0;
		if (!isset($matches[1]) || $matches[1] > 23)
			$matches[1] = 0;

		// Now the offset is easy; easy peasy - except we need to offset by 23 hours...
		$offset = 82800 + $matches[1] * 3600 + $matches[2] * 60;

		// The other time bits are simple!
		$interval = max((int) $_POST['regularity'], 1);
		$unit = in_array(substr($_POST['unit'], 0, 1), array('m', 'h', 'd', 'w')) ? substr($_POST['unit'], 0, 1) : 'd';

		// Don't allow one minute intervals.
		if ($interval == 1 && $unit == 'm')
			$interval = 2;

		// Is it disabled?
		$disabled = !isset($_POST['enabled']) ? 1 : 0;

		// Do the update!
		$smfFunc['db_query']("
			UPDATE {$db_prefix}scheduled_tasks
			SET disabled = $disabled, timeOffset = $offset, timeUnit = '$unit',
				timeRegularity = $interval
			WHERE ID_TASK = $_GET[tid]", __FILE__, __LINE__);

		// Check the next event.
		CalculateNextTrigger($_GET['tid'], true);

		// Return to the main list.
		redirectexit('action=admin;area=maintain;sa=tasks');
	}

	// Load the task, understand? Que? Que?
	$request = $smfFunc['db_query']("
		SELECT ID_TASK, nextTime, timeOffset, timeRegularity, timeUnit, disabled, task
		FROM {$db_prefix}scheduled_tasks
		WHERE ID_TASK = $_GET[tid]", __FILE__, __LINE__);

	// Should never, ever, happen!
	if ($smfFunc['db_num_rows']($request) == 0)
		fatal_lang_error(1);

	while ($row = $smfFunc['db_fetch_assoc']($request))
	{
		$context['task'] = array(
			'id' => $row['ID_TASK'],
			'function' => $row['task'],
			'name' => isset($txt['scheduled_task_' . $row['task']]) ? $txt['scheduled_task_' . $row['task']] : $row['task'],
			'desc' => isset($txt['scheduled_task_desc_' . $row['task']]) ? $txt['scheduled_task_desc_' . $row['task']] : '',
			'next_time' => $row['disabled'] ? $txt['scheduled_tasks_na'] : timeformat($row['nextTime'] == 0 ? time() : $row['nextTime']),
			'disabled' => $row['disabled'],
			'offset' => $row['timeOffset'],
			'regularity' => $row['timeRegularity'],
			'offset_formatted' => date("H:i", $row['timeOffset']),
			'unit' => $row['timeUnit'],			
		);
	}
	$smfFunc['db_free_result']($request);
}

// Show the log of all tasks that have taken place.
function TaskLog()
{
	global $scripturl, $db_prefix, $context, $txt, $smfFunc;

	// How many per page?
	$entries_per_page = 20;

	// Empty the log?
	if (!empty($_POST['deleteAll']))
	{
		checkSession();

		$smfFunc['db_query']("
			TRUNCATE {$db_prefix}log_scheduled_tasks", __FILE__, __LINE__);
	}

	// Count the total number of task log entries.
	$request = $smfFunc['db_query']("
		SELECT COUNT(*)
		FROM {$db_prefix}log_scheduled_tasks", __FILE__, __LINE__);
	list ($num_task_log_entries) = $smfFunc['db_fetch_row']($request);
	$smfFunc['db_free_result']($request);

	$context['page_index'] = constructPageIndex($scripturl . '?action=admin;area=maintain;sa=tasklog', $_REQUEST['start'], $num_task_log_entries, $entries_per_page);
	$context['start'] = $_REQUEST['start'];

	$request = $smfFunc['db_query']("
		SELECT lst.ID_LOG, lst.ID_TASK, lst.timeRun, lst.timeTaken, st.task
		FROM {$db_prefix}log_scheduled_tasks AS lst, {$db_prefix}scheduled_tasks AS st
		WHERE st.ID_TASK = lst.ID_TASK
		ORDER BY ID_LOG DESC
		LIMIT $_REQUEST[start], $entries_per_page", __FILE__, __LINE__);
	$context['log_entries'] = array();
	while ($row = $smfFunc['db_fetch_assoc']($request))
		$context['log_entries'][] = array(
			'id' => $row['ID_LOG'],
			'name' => isset($txt['scheduled_task_' . $row['task']]) ? $txt['scheduled_task_' . $row['task']] : $row['task'],
			'time_run' => timeformat($row['timeRun']),
			'time_taken' => $row['timeTaken'],
		);
	$smfFunc['db_free_result']($request);

	// Just context bits...
	$context['sub_template'] = 'task_log';
	$context['admin_tabs']['tabs']['tasks']['is_selected'] = true;
	$context['page_title'] = $txt['scheduled_log'];
}


// Convert both data and database tables to UTF-8 character set.
function ConvertUtf8()
{
	global $scripturl, $context, $txt, $language, $db_prefix, $db_character_set;
	global $modSettings, $user_info, $sourcedir, $smfFunc;

	// Show me your badge!
	isAllowedTo('admin_forum');

	// The character sets used in SMF's language files with their db equivalent.
	$charsets = array(
		// Chinese-traditional.
		'big5' => 'big5',
		// Chinese-simplified.
		'gbk' => 'gbk',
		// West European.
		'ISO-8859-1' => 'latin1',
		// Romanian.
		'ISO-8859-2' => 'latin2',
		// Turkish.
		'ISO-8859-9' => 'latin5',
		// Thai.
		'tis-620' => 'tis620',
		// Persian, Chinese, etc.
		'UTF-8' => 'utf8',
		// Russian.
		'windows-1251' => 'cp1251',
		// Greek.
		'windows-1253' => 'utf8',
		// Hebrew.
		'windows-1255' => 'utf8',
		// Arabic.
		'windows-1256' => 'cp1256',
	);

	// Get a list of character sets supported by your MySQL server.
	$request = $smfFunc['db_query']("
		SHOW CHARACTER SET", __FILE__, __LINE__);
	$db_charsets = array();
	while ($row = $smfFunc['db_fetch_assoc']($request))
		$db_charsets[] = $row['Charset'];

	// Character sets supported by both MySQL and SMF's language files.
	$charsets = array_intersect($charsets, $db_charsets);

	// This is for the first screen telling backups is good.
	if (!isset($_POST['proceed']))
	{
		// Character set conversions are only supported as of MySQL 4.1.2.
		if (version_compare('4.1.2', preg_replace('~\-.+?$~', '', $smfFunc['db_server_info']())) > 0)
			fatal_lang_error('utf8_db_version_too_low');

		// Use the messages.body column as indicator for the database charset.
		$request = $smfFunc['db_query']("
			SHOW FULL COLUMNS
			FROM {$db_prefix}messages
			LIKE 'body'", __FILE__, __LINE__);
		$column_info = $smfFunc['db_fetch_assoc']($request);
		$smfFunc['db_free_result']($request);

		// A collation looks like latin1_swedish. We only need the character set.
		list($context['database_charset']) = explode('_', $column_info['Collation']);
		$context['database_charset'] = in_array($context['database_charset'], $charsets) ? array_search($context['database_charset'], $charsets) : $context['database_charset'];

		// No need to convert to UTF-8 if it already is.
		if ($db_character_set === 'utf8' && !empty($modSettings['global_character_set']) && $modSettings['global_character_set'] === 'UTF-8')
			fatal_lang_error('utf8_already_utf8');

		// Grab the character set from the default language file.
		loadLanguage('index', $language, true);
		$context['charset_detected'] = $txt['lang_character_set'];
		$context['charset_about_detected'] = sprintf($txt['utf8_detected_charset'], $language, $context['charset_detected']);

		// Go back to your own language.
		loadLanguage('index', $user_info['language'], true);

		// Show a warning if the character set seems not to be supported.
		if (!isset($charsets[$context['charset_detected']]))
		{
			$context['charset_warning'] = sprintf($txt['utf8_charset_not_supported'], $txt['lang_character_set']);

			// Default to ISO-8859-1.
			$context['charset_detected'] = 'ISO-8859-1';
		}

		$context['charset_list'] = array_keys($charsets);

		$context['page_title'] = $txt['utf8_title'];
		$context['sub_template'] = 'convert_utf8';
		return;
	}
	
	// After this point we're starting the conversion. But first: session check.
	checkSession();

	// Translation table for the character sets not native for MySQL.
	$translation_tables = array(
		'windows-1255' => array(
			'0x81' => '\'\'',		'0x8A' => '\'\'',		'0x8C' => '\'\'',
			'0x8D' => '\'\'',		'0x8E' => '\'\'',		'0x8F' => '\'\'',
			'0x90' => '\'\'',		'0x9A' => '\'\'',		'0x9C' => '\'\'',
			'0x9D' => '\'\'',		'0x9E' => '\'\'',		'0x9F' => '\'\'',
			'0xCA' => '\'\'',		'0xD9' => '\'\'',		'0xDA' => '\'\'',
			'0xDB' => '\'\'',		'0xDC' => '\'\'',		'0xDD' => '\'\'',
			'0xDE' => '\'\'',		'0xDF' => '\'\'',		'0xFB' => '\'\'',
			'0xFC' => '\'\'',		'0xFF' => '\'\'',		'0xC2' => '0xFF',
			'0x80' => '0xFC',		'0xE2' => '0xFB',		'0xA0' => '0xC2A0',
			'0xA1' => '0xC2A1',		'0xA2' => '0xC2A2',		'0xA3' => '0xC2A3',
			'0xA5' => '0xC2A5',		'0xA6' => '0xC2A6',		'0xA7' => '0xC2A7',
			'0xA8' => '0xC2A8',		'0xA9' => '0xC2A9',		'0xAB' => '0xC2AB',
			'0xAC' => '0xC2AC',		'0xAD' => '0xC2AD',		'0xAE' => '0xC2AE',
			'0xAF' => '0xC2AF',		'0xB0' => '0xC2B0',		'0xB1' => '0xC2B1',
			'0xB2' => '0xC2B2',		'0xB3' => '0xC2B3',		'0xB4' => '0xC2B4',
			'0xB5' => '0xC2B5',		'0xB6' => '0xC2B6',		'0xB7' => '0xC2B7',
			'0xB8' => '0xC2B8',		'0xB9' => '0xC2B9',		'0xBB' => '0xC2BB',
			'0xBC' => '0xC2BC',		'0xBD' => '0xC2BD',		'0xBE' => '0xC2BE',
			'0xBF' => '0xC2BF',		'0xD7' => '0xD7B3',		'0xD1' => '0xD781',
			'0xD4' => '0xD7B0',		'0xD5' => '0xD7B1',		'0xD6' => '0xD7B2',
			'0xE0' => '0xD790',		'0xEA' => '0xD79A',		'0xEC' => '0xD79C',
			'0xED' => '0xD79D',		'0xEE' => '0xD79E',		'0xEF' => '0xD79F',
			'0xF0' => '0xD7A0',		'0xF1' => '0xD7A1',		'0xF2' => '0xD7A2',
			'0xF3' => '0xD7A3',		'0xF5' => '0xD7A5',		'0xF6' => '0xD7A6',
			'0xF7' => '0xD7A7',		'0xF8' => '0xD7A8',		'0xF9' => '0xD7A9',
			'0x82' => '0xE2809A',	'0x84' => '0xE2809E',	'0x85' => '0xE280A6',
			'0x86' => '0xE280A0',	'0x87' => '0xE280A1',	'0x89' => '0xE280B0',
			'0x8B' => '0xE280B9',	'0x93' => '0xE2809C',	'0x94' => '0xE2809D',
			'0x95' => '0xE280A2',	'0x97' => '0xE28094',	'0x99' => '0xE284A2',
			'0xC0' => '0xD6B0',		'0xC1' => '0xD6B1',		'0xC3' => '0xD6B3',
			'0xC4' => '0xD6B4',		'0xC5' => '0xD6B5',		'0xC6' => '0xD6B6',
			'0xC7' => '0xD6B7',		'0xC8' => '0xD6B8',		'0xC9' => '0xD6B9',
			'0xCB' => '0xD6BB',		'0xCC' => '0xD6BC',		'0xCD' => '0xD6BD',
			'0xCE' => '0xD6BE',		'0xCF' => '0xD6BF',		'0xD0' => '0xD780',
			'0xD2' => '0xD782',		'0xE3' => '0xD793',		'0xE4' => '0xD794',
			'0xE5' => '0xD795',		'0xE7' => '0xD797',		'0xE9' => '0xD799',
			'0xFD' => '0xE2808E',	'0xFE' => '0xE2808F',	'0x92' => '0xE28099',
			'0x83' => '0xC692',		'0xD3' => '0xD783',		'0x88' => '0xCB86',
			'0x98' => '0xCB9C',		'0x91' => '0xE28098',	'0x96' => '0xE28093',
			'0xBA' => '0xC3B7',		'0x9B' => '0xE280BA',	'0xAA' => '0xC397',
			'0xA4' => '0xE282AA',	'0xE1' => '0xD791',		'0xE6' => '0xD796',
			'0xE8' => '0xD798',		'0xEB' => '0xD79B',		'0xF4' => '0xD7A4',
			'0xFA' => '0xD7AA',		'0xFF' => '0xD6B2',		'0xFC' => '0xE282AC',
			'0xFB' => '0xD792',
		),
		'windows-1253' => array(
			'0x81' => "''",			'0x88' => "''",			'0x8A' => "''",
			'0x8C' => "''",			'0x8D' => "''",			'0x8E' => "''",
			'0x8F' => "''",			'0x90' => "''",			'0x98' => "''",
			'0x9A' => "''",			'0x9C' => "''",			'0x9D' => "''",
			'0x9E' => "''",			'0x9F' => "''",			'0xAA' => "''",
			'0xD2' => "''",			'0xFF' => "''",			'0xCE' => '0xCE9E',
			'0xB8' => '0xCE88',		'0xBA' => '0xCE8A',		'0xBC' => '0xCE8C',
			'0xBE' => '0xCE8E',		'0xBF' => '0xCE8F',		'0xC0' => '0xCE90',
			'0xC8' => '0xCE98',		'0xCA' => '0xCE9A',		'0xCC' => '0xCE9C',
			'0xCD' => '0xCE9D',		'0xCF' => '0xCE9F',		'0xDA' => '0xCEAA',
			'0xE8' => '0xCEB8',		'0xEA' => '0xCEBA',		'0xEC' => '0xCEBC',
			'0xEE' => '0xCEBE',		'0xEF' => '0xCEBF',		'0xC2' => '0xFF',
			'0xBD' => '0xC2BD',		'0xED' => '0xCEBD',		'0xB2' => '0xC2B2',
			'0xA0' => '0xC2A0',		'0xA3' => '0xC2A3',		'0xA4' => '0xC2A4',
			'0xA5' => '0xC2A5',		'0xA6' => '0xC2A6',		'0xA7' => '0xC2A7',
			'0xA8' => '0xC2A8',		'0xA9' => '0xC2A9',		'0xAB' => '0xC2AB',
			'0xAC' => '0xC2AC',		'0xAD' => '0xC2AD',		'0xAE' => '0xC2AE',
			'0xB0' => '0xC2B0',		'0xB1' => '0xC2B1',		'0xB3' => '0xC2B3',
			'0xB5' => '0xC2B5',		'0xB6' => '0xC2B6',		'0xB7' => '0xC2B7',
			'0xBB' => '0xC2BB',		'0xE2' => '0xCEB2',		'0x80' => '0xD2',
			'0x82' => '0xE2809A',	'0x84' => '0xE2809E',	'0x85' => '0xE280A6',
			'0x86' => '0xE280A0',	'0xA1' => '0xCE85',		'0xA2' => '0xCE86',
			'0x87' => '0xE280A1',	'0x89' => '0xE280B0',	'0xB9' => '0xCE89',
			'0x8B' => '0xE280B9',	'0x91' => '0xE28098',	'0x99' => '0xE284A2',
			'0x92' => '0xE28099',	'0x93' => '0xE2809C',	'0x94' => '0xE2809D',
			'0x95' => '0xE280A2',	'0x96' => '0xE28093',	'0x97' => '0xE28094',
			'0x9B' => '0xE280BA',	'0xAF' => '0xE28095',	'0xB4' => '0xCE84',
			'0xC1' => '0xCE91',		'0xC3' => '0xCE93',		'0xC4' => '0xCE94',
			'0xC5' => '0xCE95',		'0xC6' => '0xCE96',		'0x83' => '0xC692',
			'0xC7' => '0xCE97',		'0xC9' => '0xCE99',		'0xCB' => '0xCE9B',
			'0xD0' => '0xCEA0',		'0xD1' => '0xCEA1',		'0xD3' => '0xCEA3',
			'0xD4' => '0xCEA4',		'0xD5' => '0xCEA5',		'0xD6' => '0xCEA6',
			'0xD7' => '0xCEA7',		'0xD8' => '0xCEA8',		'0xD9' => '0xCEA9',
			'0xDB' => '0xCEAB',		'0xDC' => '0xCEAC',		'0xDD' => '0xCEAD',
			'0xDE' => '0xCEAE',		'0xDF' => '0xCEAF',		'0xE0' => '0xCEB0',
			'0xE1' => '0xCEB1',		'0xE3' => '0xCEB3',		'0xE4' => '0xCEB4',
			'0xE5' => '0xCEB5',		'0xE6' => '0xCEB6',		'0xE7' => '0xCEB7',
			'0xE9' => '0xCEB9',		'0xEB' => '0xCEBB',		'0xF0' => '0xCF80',
			'0xF1' => '0xCF81',		'0xF2' => '0xCF82',		'0xF3' => '0xCF83',
			'0xF4' => '0xCF84',		'0xF5' => '0xCF85',		'0xF6' => '0xCF86',
			'0xF7' => '0xCF87',		'0xF8' => '0xCF88',		'0xF9' => '0xCF89',
			'0xFA' => '0xCF8A',		'0xFB' => '0xCF8B',		'0xFC' => '0xCF8C',
			'0xFD' => '0xCF8D',		'0xFE' => '0xCF8E',		'0xFF' => '0xCE92',
			'0xD2' => '0xE282AC',
		),
	);

	// Make some preparations.
	if (isset($translation_tables[$_POST['src_charset']]))
	{
		$replace = '%field%';
		foreach ($translation_tables[$_POST['src_charset']] as $from => $to)
			$replace = "REPLACE($replace, $from, $to)";
	}

	// Grab a list of tables.
	if (preg_match('~^`(.+?)`\.(.+?)$~', $db_prefix, $match) === 1)
		$queryTables = $smfFunc['db_query']("
			SHOW TABLE STATUS
			FROM `" . strtr($match[1], array('`' => '')) . "`
			LIKE '" . str_replace('_', '\_', $match[2]) . "%'", __FILE__, __LINE__);
	else
		$queryTables = $smfFunc['db_query']("
			SHOW TABLE STATUS
			LIKE '" . str_replace('_', '\_', $db_prefix) . "%'", __FILE__, __LINE__);

	while ($table_info = $smfFunc['db_fetch_assoc']($queryTables))
	{
		// Just to make sure it doesn't time out.
		if (function_exists('apache_reset_timeout'))
			apache_reset_timeout();

		$table_charsets = array();

		// Loop through each column.
		$queryColumns = $smfFunc['db_query']("
			SHOW FULL COLUMNS 
			FROM $table_info[Name]", __FILE__, __LINE__);
		while ($column_info = $smfFunc['db_fetch_assoc']($queryColumns))
		{
			// Only text'ish columns have a character set and need converting.
			if (strpos($column_info['Type'], 'text') !== false || strpos($column_info['Type'], 'char') !== false)
			{
				$collation = empty($column_info['Collation']) || $column_info['Collation'] === 'NULL' ? $table_info['Collation'] : $column_info['Collation'];
				if (!empty($collation) && $collation !== 'NULL')
				{
					list($charset) = explode('_', $collation);

					if (!isset($table_charsets[$charset]))
						$table_charsets[$charset] = array();

					$table_charsets[$charset][] = $column_info;
				}
			}
		}
		$smfFunc['db_free_result']($queryColumns);

		// Only change the column if the data doesn't match the current charset.
		if ((count($table_charsets) === 1 && key($table_charsets) !== $charsets[$_POST['src_charset']]) || count($table_charsets) > 1)
		{
			$updates_blob = '';
			$updates_text = '';
			foreach ($table_charsets as $charset => $columns)
			{
				if ($charset !== $charsets[$_POST['src_charset']])
				{
					foreach ($columns as $column)
					{
						$updates_blob .= "
							CHANGE COLUMN $column[Field] $column[Field] " . strtr($column['Type'], array('text' => 'blob', 'char' => 'binary')) . ($column['Null'] === 'YES' ? ' NULL' : ' NOT NULL') . (strpos($column['Type'], 'char') === false ? '' : " default '$column[Default]'") . ',';
						$updates_text .= "
							CHANGE COLUMN $column[Field] $column[Field] $column[Type] CHARACTER SET " . $charsets[$_POST['src_charset']] . ($column['Null'] === 'YES' ? '' : ' NOT NULL') . (strpos($column['Type'], 'char') === false ? '' : " default '$column[Default]'") . ',';
					}
				}
			}

			// Change the columns to binary form.
			$smfFunc['db_query']("
				ALTER TABLE $table_info[Name]" . substr($updates_blob, 0, -1), __FILE__, __LINE__);

			// Convert the character set if MySQL has no native support for it.
			if (isset($translation_tables[$_POST['src_charset']]))
			{
				$update = '';
				foreach ($table_charsets as $charset => $columns)
					foreach ($columns as $column)
						$update .= "
							$column[Field] = " . strtr($replace, array('%field%' => $column['Field'])) . ',';
				
				$smfFunc['db_query']("
					UPDATE $table_info[Name]
					SET " . substr($update, 0, -1), __FILE__, __LINE__);
			}

			// Change the columns back, but with the proper character set.
			$smfFunc['db_query']("
				ALTER TABLE $table_info[Name]" . substr($updates_text, 0, -1), __FILE__, __LINE__);
		}

		// Now do the actual conversion (if still needed).
		if ($charsets[$_POST['src_charset']] !== 'utf8')
			$smfFunc['db_query']("
				ALTER TABLE $table_info[Name]
				CONVERT TO CHARACTER SET utf8", __FILE__, __LINE__);
	}
	$smfFunc['db_free_result']($queryTables);

	// Let the settings know we have a new character set.
	updateSettings(array('global_character_set' => 'UTF-8'));

	// Store it in Settings.php too because it's needed before db connection.
	require_once($sourcedir . '/Subs-Admin.php');
	updateSettingsFile(array('db_character_set' => '\'utf8\''));

	// The conversion might have messed up some serialized strings. Fix them!
	require_once($sourcedir . '/Subs-Charset.php');
	fix_serialized_columns();

	redirectExit('action=admin;area=maintain');
}

// Convert HTML-entities to their UTF-8 character equivalents.
function ConvertEntities()
{
	global $db_prefix, $db_character_set, $modSettings, $context, $sourcedir, $smfFunc;

	isAllowedTo('admin_forum');

	// Check to see if UTF-8 is currently the default character set.
	if ($modSettings['global_character_set'] !== 'UTF-8' || !isset($db_character_set) || $db_character_set !== 'utf8')
		fatal_lang_error('entity_convert_only_utf8');

	// Select the sub template from the Admin template.
	$context['sub_template'] = 'convert_entities';

	// Some starting values.
	$context['table'] = empty($_REQUEST['table']) ? 0 : (int) $_REQUEST['table'];
	$context['start'] = empty($_REQUEST['start']) ? 0 : (int) $_REQUEST['start'];

	$context['start_time'] = time();

	$context['first_step'] = !isset($_REQUEST['sesc']);
	$context['last_step'] = false;

	// The first step is just a text screen with some explanation.
	if ($context['first_step'])
		return;

	// Now we're actually going to convert...
	checkSession('get');

	// A list of tables ready for conversion.
	$tables = array(
		'ban_groups',
		'ban_items',
		'boards',
		'calendar',
		'calendar_holidays',
		'categories',
		'log_errors',
		'log_search_subjects',
		'membergroups',
		'members',
		'message_icons',
		'messages',
		'package_servers',
		'personal_messages',
		'pm_recipients',
		'polls',
		'poll_choices',
		'smileys',
		'themes',
	);
	$context['num_tables'] = count($tables);

	// This function will do the conversion later on.
	$entity_replace = create_function('$string', '
		$num = substr($string, 0, 1) === \'x\' ? hexdec(substr($string, 1)) : (int) $string;
		return $num < 0x20 || $num > 0x10FFFF || ($num >= 0xD800 && $num <= 0xDFFF) ? \'\' : ($num < 0x80 ? \'&#\' . $num . \';\' : ($num < 0x800 ? chr(192 | $num >> 6) . chr(128 | $num & 63) : ($num < 0x10000 ? chr(224 | $num >> 12) . chr(128 | $num >> 6 & 63) . chr(128 | $num & 63) : chr(240 | $num >> 18) . chr(128 | $num >> 12 & 63) . chr(128 | $num >> 6 & 63) . chr(128 | $num & 63))));');

	// Loop through all tables that need converting.
	for (; $context['table'] < $context['num_tables']; $context['table']++)
	{
		$cur_table = $tables[$context['table']];
		$primary_key = '';

		if (function_exists('apache_reset_timeout'))
			apache_reset_timeout();

		// Get a list of text columns.
		$columns = array();
		$request = $smfFunc['db_query']("
			SHOW FULL COLUMNS 
			FROM {$db_prefix}$cur_table", __FILE__, __LINE__);
		while ($column_info = $smfFunc['db_fetch_assoc']($request))
			if (strpos($column_info['Type'], 'text') !== false || strpos($column_info['Type'], 'char') !== false)
				$columns[] = $column_info['Field'];

		// Get the column with the (first) primary key.
		$request = $smfFunc['db_query']("
			SHOW KEYS
			FROM {$db_prefix}$cur_table", __FILE__, __LINE__);
		while ($row = $smfFunc['db_fetch_assoc']($request))
		{
			if ($row['Key_name'] === 'PRIMARY' && $row['Seq_in_index'] == 1)
			{
				$primary_key = $row['Column_name'];
				break;
			}
		}
		$smfFunc['db_free_result']($request);

		// No primary key, no glory.
		if (empty($primary_key))
			continue;

		// Get the maximum value for the primary key.
		$request = $smfFunc['db_query']("
			SELECT MAX($primary_key)
			FROM {$db_prefix}$cur_table", __FILE__, __LINE__);
		list($max_value) = $smfFunc['db_fetch_row']($request);
		$smfFunc['db_free_result']($request);

		if (empty($max_value))
			continue;

		while ($context['start'] <= $max_value)
		{
			// Retrieve a list of rows that has at least one entity to convert.
			$request = $smfFunc['db_query']("
				SELECT $primary_key, " . implode(', ', $columns) . "
				FROM {$db_prefix}$cur_table
				WHERE $primary_key BETWEEN $context[start] AND $context[start] + 499
					AND (" . implode(" LIKE '%&#%' OR ", $columns) . " LIKE '%&#%')
				LIMIT 500", __FILE__, __LINE__);
			while ($row = $smfFunc['db_fetch_assoc']($request))
			{
				$changes = array();
				foreach ($row as $column_name => $column_value)
					if ($column_name !== $primary_key && strpos($column_value, '&#') !== false)
						$changes[] = "$column_name = '" . addslashes(preg_replace('~(&#(\d{1,7}|x[0-9a-fA-F]{1,6});)~e', '$entity_replace(\'\\2\')', $column_value)) . "'";
				
				// Update the row.
				if (!empty($changes))
					$smfFunc['db_query']("
						UPDATE {$db_prefix}$cur_table
						SET 
							" . implode(",
							", $changes) . "
						WHERE $primary_key = " . $row[$primary_key] . "
						LIMIT 1", __FILE__, __LINE__);
			}
			$smfFunc['db_free_result']($request);
			$context['start'] += 500;

			// After ten seconds interrupt.
			if (time() - $context['start_time'] > 10)
			{
				// Calculate an approximation of the percentage done.
				$context['percent_done'] = round(100 * ($context['table'] + ($context['start'] / $max_value)) / $context['num_tables'], 1);
				$context['continue_get_data'] = '?action=admin;area=maintain;sa=convertentities;table=' . $context['table'] . ';start=' . $context['start'] . ';sesc=' . $context['session_id'];
				return;
			}
		}
		$context['start'] = 0;
	}

	// Make sure all serialized strings are all right.
	require_once($sourcedir . '/Subs-Charset.php');
	fix_serialized_columns();

	// If we're here, we must be done.
	$context['percent_done'] = 100;
	$context['continue_get_data'] = '?action=admin;area=maintain';
	$context['last_step'] = true;
}

// Optimize the database's tables.
function OptimizeTables()
{
	global $db_name, $txt, $context, $scripturl, $sourcedir, $smfFunc;

	isAllowedTo('admin_forum');

	ignore_user_abort(true);

	// Start with no tables optimized.
	$opttab = 0;

	$context['page_title'] = $txt['smf281'];
	$context['sub_template'] = 'optimize';

	// Get a list of tables, as well as how many there are.
	$result = $smfFunc['db_query']("
		SHOW TABLE STATUS
		FROM `$db_name`", false, false);
	$tables = array();

	if (!$result)
	{
		$result = $smfFunc['db_query']("
			SHOW TABLES
			FROM `$db_name`", __FILE__, __LINE__);
		while ($table = $smfFunc['db_fetch_row']($result))
			$tables[] = array('table_name' => $row[0]);
		$smfFunc['db_free_result']($result);
	}
	else
	{
		$i = 0;
		while ($table = $smfFunc['db_fetch_assoc']($result))
			$tables[] = $table + array('table_name' => $smfFunc['db_tablename']($result, $i++));
		$smfFunc['db_free_result']($result);
	}

	// If there aren't any tables then I believe that would mean the world has exploded...
	$context['num_tables'] = count($tables);
	if ($context['num_tables'] == 0)
		fatal_error('You appear to be running SMF in a flat file mode... fantastic!', false);

	// For each table....
	$context['optimized_tables'] = array();
	foreach ($tables as $table)
	{
		// Optimize the table!  We use backticks here because it might be a custom table.
		$result = $smfFunc['db_query']("
			OPTIMIZE TABLE `$table[table_name]`", __FILE__, __LINE__);
		$row = $smfFunc['db_fetch_assoc']($result);
		$smfFunc['db_free_result']($result);

		if (!isset($row['Msg_text']) || strpos($row['Msg_text'], 'already') === false || !isset($table['Data_free']) || $table['Data_free'] != 0)
			$context['optimized_tables'][] = array(
				'name' => $table['table_name'],
				'data_freed' => isset($table['Data_free']) ? $table['Data_free'] / 1024 : '<i>??</i>',
			);
	}

	// Number of tables, etc....
	$txt['smf282'] = sprintf($txt['smf282'], $context['num_tables']);
	$context['num_tables_optimized'] = count($context['optimized_tables']);

	// Check that we don't auto optimise again too soon!
	require_once($sourcedir . '/ScheduledTasks.php');
	CalculateNextTrigger('auto_optimize', true);
}

// Recount all the important board totals.
function AdminBoardRecount()
{
	global $txt, $db_prefix, $context, $scripturl, $modSettings, $sourcedir;
	global $time_start, $smfFunc;

	isAllowedTo('admin_forum');

	$context['page_title'] = $txt['not_done_title'];
	$context['continue_post_data'] = '';
	$context['continue_countdown'] = '3';
	$context['sub_template'] = 'not_done';

	// Try for as much time as possible.
	@set_time_limit(600);

	// Step the number of topics at a time so things don't time out...
	$request = $smfFunc['db_query']("
		SELECT MAX(ID_TOPIC)
		FROM {$db_prefix}topics", __FILE__, __LINE__);
	list ($max_topics) = $smfFunc['db_fetch_row']($request);
	$smfFunc['db_free_result']($request);

	$increment = min(ceil($max_topics / 4), 2000);
	if (empty($_REQUEST['start']))
		$_REQUEST['start'] = 0;

	$total_steps = 8;

	// Get each topic with a wrong reply count and fix it - let's just do some at a time, though.
	if (empty($_REQUEST['step']))
	{
		$_REQUEST['step'] = 0;

		while ($_REQUEST['start'] < $max_topics)
		{
			$request = $smfFunc['db_query']("
				SELECT /*!40001 SQL_NO_CACHE */ t.ID_TOPIC, t.numReplies, t.unapprovedPosts,
					IF (COUNT(ma.ID_MSG), COUNT(ma.ID_MSG) - 1, 0) AS realNumReplies, COUNT(mu.ID_MSG) AS realUnapprovedPosts
				FROM {$db_prefix}topics AS t
					LEFT JOIN {$db_prefix}messages AS ma ON (ma.ID_TOPIC = t.ID_TOPIC AND ma.approved = 1)
					LEFT JOIN {$db_prefix}messages AS mu ON (mu.ID_TOPIC = t.ID_TOPIC AND mu.approved = 0)
				WHERE t.ID_TOPIC > " . ($_REQUEST['start']) . "
					AND t.ID_TOPIC <= " . ($_REQUEST['start'] + $increment) . "
				GROUP BY t.ID_TOPIC
				HAVING realNumReplies != numReplies OR realUnapprovedPosts != unapprovedPosts", __FILE__, __LINE__);
			while ($row = $smfFunc['db_fetch_assoc']($request))
				$smfFunc['db_query']("
					UPDATE {$db_prefix}topics
					SET numReplies = $row[realNumReplies], unapprovedPosts = $row[realUnapprovedPosts]
					WHERE ID_TOPIC = $row[ID_TOPIC]
					LIMIT 1", __FILE__, __LINE__);
			$smfFunc['db_free_result']($request);

			$_REQUEST['start'] += $increment;

			if (array_sum(explode(' ', microtime())) - array_sum(explode(' ', $time_start)) > 3)
			{
				$context['continue_get_data'] = '?action=admin;area=maintain;sa=recount;step=0;start=' . $_REQUEST['start'];
				$context['continue_percent'] = round((100 * $_REQUEST['start'] / $max_topics) / $total_steps);

				return;
			}
		}

		$_REQUEST['start'] = 0;
	}

	// Update the post count of each board.
	if ($_REQUEST['step'] <= 1)
	{
		if (empty($_REQUEST['start']))
			$smfFunc['db_query']("
				UPDATE {$db_prefix}boards
				SET numPosts = 0", __FILE__, __LINE__);

		while ($_REQUEST['start'] < $max_topics)
		{
			$request = $smfFunc['db_query']("
				SELECT /*!40001 SQL_NO_CACHE */ m.ID_BOARD, COUNT(*) AS realNumPosts
				FROM {$db_prefix}messages AS m
				WHERE m.ID_TOPIC > " . ($_REQUEST['start']) . "
					AND m.ID_TOPIC <= " . ($_REQUEST['start'] + $increment) . "
					AND m.approved = 1
				GROUP BY m.ID_BOARD", __FILE__, __LINE__);
			while ($row = $smfFunc['db_fetch_assoc']($request))
				$smfFunc['db_query']("
					UPDATE {$db_prefix}boards
					SET numPosts = numPosts + $row[realNumPosts]
					WHERE ID_BOARD = $row[ID_BOARD]
					LIMIT 1", __FILE__, __LINE__);
			$smfFunc['db_free_result']($request);

			$_REQUEST['start'] += $increment;

			if (array_sum(explode(' ', microtime())) - array_sum(explode(' ', $time_start)) > 3)
			{
				$context['continue_get_data'] = '?action=admin;area=maintain;sa=recount;step=1;start=' . $_REQUEST['start'];
				$context['continue_percent'] = round((200 + 100 * $_REQUEST['start'] / $max_topics) / $total_steps);

				return;
			}
		}

		$_REQUEST['start'] = 0;
	}

	// Update the topic count of each board.
	if ($_REQUEST['step'] <= 2)
	{
		if (empty($_REQUEST['start']))
			$smfFunc['db_query']("
				UPDATE {$db_prefix}boards
				SET numTopics = 0", __FILE__, __LINE__);

		while ($_REQUEST['start'] < $max_topics)
		{
			$request = $smfFunc['db_query']("
				SELECT /*!40001 SQL_NO_CACHE */ t.ID_BOARD, COUNT(*) AS realNumTopics
				FROM {$db_prefix}topics AS t
				WHERE t.approved = 1
					AND t.ID_TOPIC > " . ($_REQUEST['start']) . "
					AND t.ID_TOPIC <= " . ($_REQUEST['start'] + $increment) . "
				GROUP BY t.ID_BOARD", __FILE__, __LINE__);
			while ($row = $smfFunc['db_fetch_assoc']($request))
				$smfFunc['db_query']("
					UPDATE {$db_prefix}boards
					SET numTopics = numTopics + $row[realNumTopics]
					WHERE ID_BOARD = $row[ID_BOARD]
					LIMIT 1", __FILE__, __LINE__);
			$smfFunc['db_free_result']($request);

			$_REQUEST['start'] += $increment;

			if (array_sum(explode(' ', microtime())) - array_sum(explode(' ', $time_start)) > 3)
			{
				$context['continue_get_data'] = '?action=admin;area=maintain;sa=recount;step=2;start=' . $_REQUEST['start'];
				$context['continue_percent'] = round((300 + 100 * $_REQUEST['start'] / $max_topics) / $total_steps);

				return;
			}
		}

		$_REQUEST['start'] = 0;
	}

	// Update the unapproved post count of each board.
	if ($_REQUEST['step'] <= 3)
	{
		if (empty($_REQUEST['start']))
			$smfFunc['db_query']("
				UPDATE {$db_prefix}boards
				SET unapprovedPosts = 0", __FILE__, __LINE__);

		while ($_REQUEST['start'] < $max_topics)
		{
			$request = $smfFunc['db_query']("
				SELECT /*!40001 SQL_NO_CACHE */ m.ID_BOARD, COUNT(*) AS realUnapprovedPosts
				FROM {$db_prefix}messages AS m
				WHERE m.ID_TOPIC > " . ($_REQUEST['start']) . "
					AND m.ID_TOPIC <= " . ($_REQUEST['start'] + $increment) . "
					AND m.approved = 0
				GROUP BY m.ID_BOARD", __FILE__, __LINE__);
			while ($row = $smfFunc['db_fetch_assoc']($request))
				$smfFunc['db_query']("
					UPDATE {$db_prefix}boards
					SET unapprovedPosts = unapprovedPosts + $row[realUnapprovedPosts]
					WHERE ID_BOARD = $row[ID_BOARD]
					LIMIT 1", __FILE__, __LINE__);
			$smfFunc['db_free_result']($request);

			$_REQUEST['start'] += $increment;

			if (array_sum(explode(' ', microtime())) - array_sum(explode(' ', $time_start)) > 3)
			{
				$context['continue_get_data'] = '?action=admin;area=maintain;sa=recount;step=3;start=' . $_REQUEST['start'];
				$context['continue_percent'] = round((400 + 100 * $_REQUEST['start'] / $max_topics) / $total_steps);

				return;
			}
		}

		$_REQUEST['start'] = 0;
	}

	// Update the unapproved topic count of each board.
	if ($_REQUEST['step'] <= 4)
	{
		if (empty($_REQUEST['start']))
			$smfFunc['db_query']("
				UPDATE {$db_prefix}boards
				SET unapprovedTopics = 0", __FILE__, __LINE__);

		while ($_REQUEST['start'] < $max_topics)
		{
			$request = $smfFunc['db_query']("
				SELECT /*!40001 SQL_NO_CACHE */ t.ID_BOARD, COUNT(*) AS realUnapprovedTopics
				FROM {$db_prefix}topics AS t
				WHERE t.approved = 0
					AND t.ID_TOPIC > " . ($_REQUEST['start']) . "
					AND t.ID_TOPIC <= " . ($_REQUEST['start'] + $increment) . "
				GROUP BY t.ID_BOARD", __FILE__, __LINE__);
			while ($row = $smfFunc['db_fetch_assoc']($request))
				$smfFunc['db_query']("
					UPDATE {$db_prefix}boards
					SET unapprovedTopics = unapprovedTopics + $row[realUnapprovedTopics]
					WHERE ID_BOARD = $row[ID_BOARD]
					LIMIT 1", __FILE__, __LINE__);
			$smfFunc['db_free_result']($request);

			$_REQUEST['start'] += $increment;

			if (array_sum(explode(' ', microtime())) - array_sum(explode(' ', $time_start)) > 3)
			{
				$context['continue_get_data'] = '?action=admin;area=maintain;sa=recount;step=4;start=' . $_REQUEST['start'];
				$context['continue_percent'] = round((500 + 100 * $_REQUEST['start'] / $max_topics) / $total_steps);

				return;
			}
		}

		$_REQUEST['start'] = 0;
	}

	// Get all members with wrong number of personal messages.
	if ($_REQUEST['step'] <= 5)
	{
		$request = $smfFunc['db_query']("
			SELECT /*!40001 SQL_NO_CACHE */ mem.ID_MEMBER, COUNT(pmr.ID_PM) AS realNum, mem.instantMessages
			FROM {$db_prefix}members AS mem
				LEFT JOIN {$db_prefix}pm_recipients AS pmr ON (mem.ID_MEMBER = pmr.ID_MEMBER AND pmr.deleted = 0)
			GROUP BY mem.ID_MEMBER
			HAVING realNum != instantMessages", __FILE__, __LINE__);
		while ($row = $smfFunc['db_fetch_assoc']($request))
			updateMemberData($row['ID_MEMBER'], array('instantMessages' => $row['realNum']));
		$smfFunc['db_free_result']($request);

		$request = $smfFunc['db_query']("
			SELECT /*!40001 SQL_NO_CACHE */ mem.ID_MEMBER, COUNT(pmr.ID_PM) AS realNum, mem.unreadMessages
			FROM {$db_prefix}members AS mem
				LEFT JOIN {$db_prefix}pm_recipients AS pmr ON (mem.ID_MEMBER = pmr.ID_MEMBER AND pmr.deleted = 0 AND pmr.is_read = 0)
			GROUP BY mem.ID_MEMBER
			HAVING realNum != unreadMessages", __FILE__, __LINE__);
		while ($row = $smfFunc['db_fetch_assoc']($request))
			updateMemberData($row['ID_MEMBER'], array('unreadMessages' => $row['realNum']));
		$smfFunc['db_free_result']($request);

		if (array_sum(explode(' ', microtime())) - array_sum(explode(' ', $time_start)) > 3)
		{
			$context['continue_get_data'] = '?action=admin;area=maintain;sa=recount;step=6;start=0';
			$context['continue_percent'] = round(700 / $total_steps);

			return;
		}
	}

	// Any messages pointing to the wrong board?
	if ($_REQUEST['step'] <= 6)
	{
		while ($_REQUEST['start'] < $modSettings['maxMsgID'])
		{
			$request = $smfFunc['db_query']("
				SELECT /*!40001 SQL_NO_CACHE */ t.ID_BOARD, m.ID_MSG
				FROM ({$db_prefix}messages AS m, {$db_prefix}topics AS t)
				WHERE t.ID_TOPIC = m.ID_TOPIC
					AND m.ID_MSG > $_REQUEST[start]
					AND m.ID_MSG <= " . ($_REQUEST['start'] + $increment) . "
					AND m.ID_BOARD != t.ID_BOARD", __FILE__, __LINE__);
			$boards = array();
			while ($row = $smfFunc['db_fetch_assoc']($request))
				$boards[$row['ID_BOARD']][] = $row['ID_MSG'];
			$smfFunc['db_free_result']($request);

			foreach ($boards as $board_id => $messages)
				$smfFunc['db_query']("
					UPDATE {$db_prefix}messages
					SET ID_BOARD = $board_id
					WHERE ID_MSG IN (" . implode(', ', $messages) . ")
					LIMIT " . count($messages), __FILE__, __LINE__);

			$_REQUEST['start'] += $increment;

			if (array_sum(explode(' ', microtime())) - array_sum(explode(' ', $time_start)) > 3)
			{
				$context['continue_get_data'] = '?action=admin;area=maintain;sa=recount;step=6;start=' . $_REQUEST['start'];
				$context['continue_percent'] = round((700 + 100 * $_REQUEST['start'] / $modSettings['maxMsgID']) / $total_steps);

				return;
			}
		}

		$_REQUEST['start'] = 0;
	}

	// Update the latest message of each board.
	$request = $smfFunc['db_query']("
		SELECT /*!40001 SQL_NO_CACHE */ b.ID_BOARD, b.ID_PARENT, b.ID_LAST_MSG, MAX(m.ID_MSG) AS localLastMsg, b.childLevel
		FROM ({$db_prefix}boards AS b, {$db_prefix}messages AS m)
		WHERE b.ID_BOARD = m.ID_BOARD
			AND m.approved = 1
		GROUP BY ID_BOARD", __FILE__, __LINE__);
	$resort_me = array();
	while ($row = $smfFunc['db_fetch_assoc']($request))
		$resort_me[$row['childLevel']][] = $row;
	$smfFunc['db_free_result']($request);

	krsort($resort_me);

	$lastMsg = array();
	foreach ($resort_me as $rows)
		foreach ($rows as $row)
		{
			// The latest message is the latest of the current board and its children.
			if (isset($lastMsg[$row['ID_BOARD']]))
				$curLastMsg = max($row['localLastMsg'], $lastMsg[$row['ID_BOARD']]);
			else
				$curLastMsg = $row['localLastMsg'];

			// If what is and what should be the latest message differ, an update is necessary.
			if ($curLastMsg != $row['ID_LAST_MSG'])
				$smfFunc['db_query']("
					UPDATE {$db_prefix}boards
					SET ID_LAST_MSG = $curLastMsg
					WHERE ID_BOARD = $row[ID_BOARD]
					LIMIT 1", __FILE__, __LINE__);

			// Parent boards inherit the latest message of their children.
			if (isset($lastMsg[$row['ID_PARENT']]))
				$lastMsg[$row['ID_PARENT']] = max($row['localLastMsg'], $lastMsg[$row['ID_PARENT']]);
			else
				$lastMsg[$row['ID_PARENT']] = $row['localLastMsg'];
		}

	// Update all the basic statistics.
	updateStats('member');
	updateStats('message');
	updateStats('topic');

	// Finally, update the latest event times.
	require_once($sourcedir . '/ScheduledTasks.php');
	CalculateNextTrigger();

	redirectexit('action=admin;area=maintain;done');
}

// This function caches the relevant language files, and if the cache doesn't work includes them with eval.
function cacheLanguage($template_name, $lang, $fatal, $theme_name)
{
	global $language, $settings, $txt, $db_prefix;
	global $cachedir, $smfFunc;

	// Is the file writable?
	$can_write = is_writable($cachedir) ? 1 : 0;
	// By default include it afterwards.
	$do_include = true;

	// Open the file to write to.
	if ($can_write)
	{
		$fh = fopen($cachedir . '/lang_' . $template_name . '_' . $lang . '_' . $theme_name . '.php', 'w');
		fwrite($fh, "<?php\n");
	}

	// For each file open it up and write it out!
	foreach (explode('+', $template_name) as $template)
	{
		// Obviously, the current theme is most important to check.
		$attempts = array(
			array($settings['theme_dir'], $template, $lang, $settings['theme_url']),
			array($settings['theme_dir'], $template, $language, $settings['theme_url']),
		);
	
		// Do we have a base theme to worry about?
		if (isset($settings['base_theme_dir']))
		{
			$attempts[] = array($settings['base_theme_dir'], $template, $lang, $settings['base_theme_url']);
			$attempts[] = array($settings['base_theme_dir'], $template, $language, $settings['base_theme_url']);
		}
	
		// Fallback on the default theme if necessary.
		$attempts[] = array($settings['default_theme_dir'], $template, $lang, $settings['default_theme_url']);
		$attempts[] = array($settings['default_theme_dir'], $template, $language, $settings['default_theme_url']);

		// Try to find the language file.
		foreach ($attempts as $k => $file)
			if (file_exists($file[0] . '/languages/' . $file[1] . '.' . $file[2] . '.php'))
			{
				if ($can_write)
				{
					foreach (file($file[0] . '/languages/' . $file[1] . '.' . $file[2] . '.php') as $line)
					{
						if (substr($line, 0, 2) != '?>' && substr($line, 0, 2) != '<?')
						{
							// Some common variables get parsed in...
							$line = preg_replace('~\{NL\}~', '\\\\n', $line);
							fwrite($fh, $line);
						}
					}
				}
				// If the cache directory is not writable we're having a bad day.
				else
				{
					$fc = implode('', file($file[0] . '/languages/' . $file[1] . '.' . $file[2] . '.php'));
					$fc = preg_replace('~\{NL\}~', '\\\\n', $fc);
					$fc = preg_replace('~<\?php~', '', $fc);
					$fc = preg_replace('~\?>~', '', $fc);
					eval($fc);

					// Mark that we're messed up!
					$do_include = false;
				}

				// Hmmm... do we really still need this?
				$language_url = $file[3];
				$lang = $file[2];
	
				break;
			}

		// That couldn't be found!  Log the error, but *try* to continue normally.
		if (!isset($language_url))
		{
			if ($fatal)
				log_error(sprintf($txt['theme_language_error'], $template_name . '.' . $lang, 'template'));
			return false;
		}
		else
			unset($language_url);

		// If this includes the index template put in the language settings too.
		//!!! Remove this for now - we may add it back later.
		/*if ($template == 'index')
		{
			$request = $smfFunc['db_query']("
				SELECT time_format, number_format, charset, locale, dictionary, rtl, image_lang
				FROM {$db_prefix}languages
				WHERE codename = '$lang'", __FILE__, __LINE__);
			$row = $smfFunc['db_fetch_assoc']($request);
			if (!empty($row))
			{
				foreach ($row as $k => $v)
				{
					if ($can_write)
						fwrite($fh, '$txt[\'' . $k . '\'] = \'' . $v . "';\n");
					else
						$txt[$k] = $v;
				}
			}
			$smfFunc['db_free_result']($request);
		}*/
	}

	if ($can_write)
	{
		fwrite($fh, "?>");
		fclose($fh);
	}

	return $do_include;
}

?>