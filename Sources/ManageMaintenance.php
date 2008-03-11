<?php
/**********************************************************************************
* ManageMaintenance.php                                                           *
***********************************************************************************
* SMF: Simple Machines Forum                                                      *
* Open-Source Project Inspired by Zef Hemel (zef@zefhemel.com)                    *
* =============================================================================== *
* Software Version:           SMF 2.0 Beta 3                                      *
* Software by:                Simple Machines (http://www.simplemachines.org)     *
* Copyright 2006-2008 by:     Simple Machines LLC (http://www.simplemachines.org) *
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
		- fixes topics with wrong num_replies.
		- updates the num_posts and num_topics of all boards.
		- recounts instant_messages but not unread_messages.
		- repairs messages pointing to boards with topics pointing to
		  other boards.
		- updates the last message posted in boards and children.
		- updates member count, latest member, topic count, and message count.
		- redirects back to ?action=admin;area=maintain when complete.
		- accessed via ?action=admin;area=maintain;sa=recount.

	void VersionDetail()
		- parses the comment headers in all files for their version information
		  and outputs that for some javascript to check with simplemacines.org.
		- does not connect directly with simplemachines.org, but rather
		  expects the client to.
		- requires the admin_forum permission.
		- uses the view_versions admin area.
		- loads the view_versions sub template (in the Admin template.)
		- accessed through ?action=admin;area=maintain;sa=version.

	bool cacheLanguage(string template_name, string language, bool fatal, string theme_name)
		// !!!

	mixed activity_move_topics_maintenance(bool do_action = true)
		- Moves topics from one board to another.
		- User the not_done template to pause the process.

	void activity_maintain_reattribute_posts(bool do_action = true)
		// !!!

	void activity_maintain_backup(bool do_action = true)
		// !!!

	void activity_maintain_members(bool do_action = true)
		// !!!

	void activity_maintain_old(bool do_action = true)
		// !!!

	void AdminTask()
		- Handles minor maintenance tasks.
		- Sets up a layer, some key context information, and passes execution to the task function.
*/

// The maintenance access point.
function ManageMaintenance()
{
	global $txt, $modSettings, $scripturl, $context, $options;

	// You absolutely must be an admin by here!
	isAllowedTo('admin_forum');

	// Need something to talk about?
	loadLanguage('ManageMaintenance');
	loadTemplate('ManageMaintenance');

	// So many things you can - but frankly I won't let you - just these!
	$subActions = array(
		'admintask' => 'AdminTask',
		'cleancache' => 'Maintenance',
		'convertentities' => 'ConvertEntities',
		'convertutf8' => 'ConvertUtf8',
		'destroy' => 'Maintenance',
		'logs' => 'Maintenance',
		'general' => 'Maintenance',
		'movetopics' => 'activity_move_topics_maintenance',
		'optimize' => 'OptimizeTables',
		'recount' => 'AdminBoardRecount',
		'taskedit' => 'EditTask',
		'tasklog' => 'TaskLog',
		'tasks' => 'ScheduledTasks',
		'version' => 'VersionDetail',
	);

	// Yep, sub-action time!
	if (isset($_REQUEST['sa']) && isset($subActions[$_REQUEST['sa']]))
		$context['sub_action'] = $_REQUEST['sa'];
	else
		$context['sub_action'] = 'general';

	// This uses admin tabs - as it should!
	$context[$context['admin_menu_name']]['tab_data'] = array(
		'title' => &$txt['maintain_title'],
		'help' => '',
		'description' => $txt['maintain_info'],
		'tabs' => array(
			'general' => array(
				'description' => $txt['maintain_common_desc'],
			),
			'tasks' => array(
				'description' => $txt['maintain_tasks_desc'],
			),
		),
	);

	// Finally fall through to what we are doing.
	$subActions[$context['sub_action']]();
}

// Miscellaneous maintenance..
function Maintenance()
{
	global $context, $txt, $user_info, $db_character_set, $db_type;
	global $modSettings, $sourcedir, $cachedir, $smcFunc;

	if (isset($_GET['sa']) && $_GET['sa'] == 'logs')
	{
		checkSession('get');

		// No one's online now.... MUHAHAHAHA :P.
		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}log_online',
			array(
			)
		);

		// Dump the banning logs.
		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}log_banned',
			array(
			)
		);

		// Start id_error back at 0 and dump the error log.
		$smcFunc['db_query']('truncate_table', '
			TRUNCATE {db_prefix}log_errors',
			array(
			)
		);

		// Clear out the spam log.
		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}log_floodcontrol',
			array(
			)
		);

		// Clear out the karma actions.
		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}log_karma',
			array(
			)
		);

		// Last but not least, the search logs!
		$smcFunc['db_query']('truncate_table', '
			TRUNCATE {db_prefix}log_search_topics',
			array(
			)
		);
		$smcFunc['db_query']('truncate_table', '
			TRUNCATE {db_prefix}log_search_messages',
			array(
			)
		);
		$smcFunc['db_query']('truncate_table', '
			TRUNCATE {db_prefix}log_search_results',
			array(
			)
		);

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

	$context['convert_utf8'] = $db_type == 'mysql' && (!isset($db_character_set) || $db_character_set !== 'utf8' || empty($modSettings['global_character_set']) || $modSettings['global_character_set'] !== 'UTF-8') && version_compare('4.1.2', preg_replace('~\-.+?$~', '', $smcFunc['db_server_info']())) <= 0;
	$context['convert_entities'] = $db_type == 'mysql' && isset($db_character_set, $modSettings['global_character_set']) && $db_character_set === 'utf8' && $modSettings['global_character_set'] === 'UTF-8';

	$context['sub_template'] = 'maintain';
	$context['page_title'] = $txt['maintain_title'];
}

// List all the scheduled task in place on the forum.
function ScheduledTasks()
{
	global $context, $txt, $sourcedir, $smcFunc, $user_info, $modSettings, $scripturl;

	// Mama, setup the template first - cause it's like the most important bit, like pickle in a sandwich.
	// ... ironically I don't like pickle. </grudge>
	$context['sub_template'] = 'view_scheduled_tasks';
	$context['page_title'] = $txt['maintain_tasks'];

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
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}scheduled_tasks
			SET disabled = CASE WHEN id_task IN ({array_int:id_task_enable}) THEN 0 ELSE 1 END',
			array(
				'id_task_enable' => $enablers,
			)
		);

		// Pop along...
		CalculateNextTrigger();
	}

	// Want to run any of the tasks?
	if (isset($_REQUEST['run']) && isset($_POST['run_task']))
	{
		// Lets figure out which ones they want to run.
		$tasks = array();
		foreach ($_POST['run_task'] as $task => $dummy)
			$tasks[] = (int) $task;

		// Load up the tasks.
		$request = $smcFunc['db_query']('', '
			SELECT id_task, task
			FROM {db_prefix}scheduled_tasks
			WHERE id_task IN ({array_int:tasks})
			LIMIT ' . count($tasks),
			array(
				'tasks' => $tasks,
			)
		);

		// Lets get it on!
		require_once($sourcedir . '/ScheduledTasks.php');
		ignore_user_abort(true);
		while ($row = $smcFunc['db_fetch_assoc']($request))
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
				$smcFunc['db_insert']('',
					'{db_prefix}log_scheduled_tasks',
					array('id_task' => 'int', 'time_run' => 'int', 'time_taken' => 'float'),
					array($row['id_task'], time(), $total_time),
					array('id_task')
				);
			}
		}

		redirectexit('action=admin;area=maintain;sa=tasks;done');
	}

	$listOptions = array(
		'id' => 'scheduled_tasks',
		'title' => $txt['maintain_tasks'],
		'base_href' => $scripturl . '?action=admin;area=maintain;sa=tasks',
		'get_items' => array(
			'function' => 'list_getScheduledTasks',
		),
		'columns' => array(
			'name' => array(
				'header' => array(
					'value' => $txt['scheduled_tasks_name'],
					'style' => 'width: 40%;',
				),
				'data' => array(
					'sprintf' => array(
						'format' => '
							<a href="' . $scripturl . '?action=admin;area=maintain;sa=taskedit;tid=%1$d">%2$s</a><br /><span class="smalltext">%3$s</span>',
						'params' => array(
							'id' => false,
							'name' => false,
							'desc' => false,
						),
					),
				),
			),
			'next_due' => array(
				'header' => array(
					'value' => $txt['scheduled_tasks_next_time'],
				),
				'data' => array(
					'db' => 'next_time',
					'class' => 'smalltext',
				),
			),
			'regularity' => array(
				'header' => array(
					'value' => $txt['scheduled_tasks_regularity'],
				),
				'data' => array(
					'db' => 'regularity',
					'class' => 'smalltext',
				),
			),
			'enabled' => array(
				'header' => array(
					'value' => $txt['scheduled_tasks_enabled'],
					'style' => 'width: 6%;',
				),
				'data' => array(
					'sprintf' => array(
						'format' => 
							'<input type="hidden" name="task[%1$d]" id="task_%1$d" value="0" /><input type="checkbox" name="task[%1$d]" id="task_check_%1$d" %2$s class="check" />',
						'params' => array(
							'id' => false,
							'checked_state' => false,
						),
					),
					'style' => 'text-align: center;',
				),
			),
			'run_now' => array(
				'header' => array(
					'value' => $txt['scheduled_tasks_run_now'],
					'style' => 'width: 6%;',
				),
				'data' => array(
					'sprintf' => array(
						'format' => 
							'<input type="checkbox" name="run_task[%1$d]" id="run_task_%1$d" class="check" />',
						'params' => array(
							'id' => false,
						),
					),
					'style' => 'text-align: center;',
				),
			),
		),
		'form' => array(
			'href' => $scripturl . '?action=admin;area=maintain;sa=tasks',
		),
		'additional_rows' => array(
			array(
				'position' => 'below_table_data',
				'value' => '
					<div style="float: left;">
						[<a href="' . $scripturl . '?action=admin;area=maintain;sa=tasklog">' . $txt['scheduled_view_log'] . '</a>]
					</div>
					<div style="float: right;">
						<input type="submit" name="save" value="' . $txt['scheduled_tasks_save_changes'] . '" />
						<input type="submit" name="run" value="' . $txt['scheduled_tasks_run_now'] . '" />
					</div>',
				'class' => 'titlebg',
				'style' => 'text-align: right;',
			),
			array(
				'position' => 'after_title',
				'value' => '
					<span class="smalltext">' . $txt['scheduled_tasks_time_offset'] . '</span>',
				'class' => 'windowbg2',
			),
		),
	);

	require_once($sourcedir . '/Subs-List.php');
	createList($listOptions);

	$context['sub_template'] = 'view_scheduled_tasks';

	$context['tasks_were_run'] = isset($_GET['done']);
}

function list_getScheduledTasks($start, $items_per_page, $sort)
{
	global $smcFunc, $txt, $scripturl;

	$request = $smcFunc['db_query']('', '
		SELECT id_task, next_time, time_offset, time_regularity, time_unit, disabled, task
		FROM {db_prefix}scheduled_tasks',
		array(
		)
	);
	$known_tasks = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		// Find the next for regularity - don't offset as it's always server time!
		$offset = sprintf($txt['scheduled_task_reg_starting'], date('H:i', $row['time_offset']));
		$repeating = sprintf($txt['scheduled_task_reg_repeating'], $row['time_regularity'], $txt['scheduled_task_reg_unit_' . $row['time_unit']]);

		$known_tasks[] = array(
			'id' => $row['id_task'],
			'function' => $row['task'],
			'name' => isset($txt['scheduled_task_' . $row['task']]) ? $txt['scheduled_task_' . $row['task']] : $row['task'],
			'desc' => isset($txt['scheduled_task_desc_' . $row['task']]) ? $txt['scheduled_task_desc_' . $row['task']] : '',
			'next_time' => $row['disabled'] ? $txt['scheduled_tasks_na'] : timeformat(($row['next_time'] == 0 ? time() : $row['next_time']), true, 'server'),
			'disabled' => $row['disabled'],
			'checked_state' => $row['disabled'] ? '' : 'checked="checked"',
			'regularity' => $offset . ', ' . $repeating,
		);
	}
	// You see - Mike sucks - and can't work out how to do in C++ what PHP can do with it's eyes closed - pah! He no programmer ;)
	// TWlrZSBTdWNrcyBCYWxscyBhbmQgSXMgTXkgQmlhdGNoISE=
	$smcFunc['db_free_result']($request);

	return $known_tasks;
}

// Function for editing a task.
function EditTask()
{
	global $context, $txt, $sourcedir, $smcFunc, $user_info, $modSettings;

	// Just set up some lovely context stuff.
	$context[$context['admin_menu_name']]['current_subsection'] = 'tasks';
	$context['sub_template'] = 'edit_scheduled_tasks';
	$context['page_title'] = $txt['scheduled_task_edit'];
	$context['server_time'] = timeformat(time(), false, 'server');

	// Cleaning...
	if (!isset($_GET['tid']))
		fatal_lang_error('no_access');
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
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}scheduled_tasks
			SET disabled = {int:disabled}, time_offset = {int:time_offset}, time_unit = {string:time_unit},
				time_regularity = {int:time_regularity}
			WHERE id_task = {int:id_task}',
			array(
				'disabled' => $disabled,
				'time_offset' => $offset,
				'time_regularity' => $interval,
				'id_task' => $_GET['tid'],
				'time_unit' => $unit,
			)
		);

		// Check the next event.
		CalculateNextTrigger($_GET['tid'], true);

		// Return to the main list.
		redirectexit('action=admin;area=maintain;sa=tasks');
	}

	// Load the task, understand? Que? Que?
	$request = $smcFunc['db_query']('', '
		SELECT id_task, next_time, time_offset, time_regularity, time_unit, disabled, task
		FROM {db_prefix}scheduled_tasks
		WHERE id_task = {int:id_task}',
		array(
			'id_task' => $_GET['tid'],
		)
	);

	// Should never, ever, happen!
	if ($smcFunc['db_num_rows']($request) == 0)
		fatal_lang_error('no_access');

	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$context['task'] = array(
			'id' => $row['id_task'],
			'function' => $row['task'],
			'name' => isset($txt['scheduled_task_' . $row['task']]) ? $txt['scheduled_task_' . $row['task']] : $row['task'],
			'desc' => isset($txt['scheduled_task_desc_' . $row['task']]) ? $txt['scheduled_task_desc_' . $row['task']] : '',
			'next_time' => $row['disabled'] ? $txt['scheduled_tasks_na'] : timeformat($row['next_time'] == 0 ? time() : $row['next_time'], true, 'server'),
			'disabled' => $row['disabled'],
			'offset' => $row['time_offset'],
			'regularity' => $row['time_regularity'],
			'offset_formatted' => date('H:i', $row['time_offset']),
			'unit' => $row['time_unit'],
		);
	}
	$smcFunc['db_free_result']($request);
}

// Show the log of all tasks that have taken place.
function TaskLog()
{
	global $scripturl, $context, $txt, $smcFunc, $sourcedir;

	// Empty the log?
	if (!empty($_POST['deleteAll']))
	{
		checkSession();

		$smcFunc['db_query']('truncate_table', '
			TRUNCATE {db_prefix}log_scheduled_tasks',
			array(
			)
		);
	}

	// Setup the list.
	$listOptions = array(
		'id' => 'task_log',
		'items_per_page' => 30,
		'title' => $txt['scheduled_log'],
		'no_items_label' => $txt['scheduled_log_empty'],
		'base_href' => $scripturl . '?action=admin;area=maintain;sa=tasklog',
		'default_sort_col' => 'date',
		'get_items' => array(
			'function' => 'list_getTaskLogEntries',
		),
		'get_count' => array(
			'function' => 'list_getNumTaskLogEntries',
		),
		'columns' => array(
			'name' => array(
				'header' => array(
					'value' => $txt['scheduled_tasks_name'],
				),
				'data' => array(
					'db' => 'name'
				),
			),
			'date' => array(
				'header' => array(
					'value' => $txt['scheduled_log_time_run'],
				),
				'data' => array(
					'function' => create_function('$rowData', '
						return timeformat($rowData[\'time_run\'], true, \'server\');
					'),
				),
				'sort' => array(
					'default' => 'lst.id_log DESC',
					'reverse' => 'lst.id_log',
				),
			),
			'time_taken' => array(
				'header' => array(
					'value' => $txt['scheduled_log_time_taken'],
				),
				'data' => array(
					'sprintf' => array(
						'format' => $txt['scheduled_log_time_taken_seconds'],
						'params' => array(
							'time_taken' => false,
						),
					),
				),
				'sort' => array(
					'default' => 'lst.time_taken',
					'reverse' => 'lst.time_taken DESC',
				),
			),
		),
		'form' => array(
			'href' => $scripturl . '?action=admin;area=maintain;sa=tasklog',
		),
		'additional_rows' => array(
			array(
				'position' => 'below_table_data',
				'value' => '
					<input type="submit" name="removeAll" value="' . $txt['scheduled_log_empty_log'] . '" />',
				'class' => 'titlebg',
				'style' => 'text-align: right;',
			),
			array(
				'position' => 'after_title',
				'value' => '
					<span class="smalltext">' . $txt['scheduled_tasks_time_offset'] . '</span>',
				'class' => 'windowbg2',
			),
		),
	);

	require_once($sourcedir . '/Subs-List.php');
	createList($listOptions);

	$context['sub_template'] = 'show_list';
	$context['default_list'] = 'task_log';

	// Make it all look tify.
	$context[$context['admin_menu_name']]['current_subsection'] = 'tasks';
	$context['page_title'] = $txt['scheduled_log'];
}

function list_getTaskLogEntries($start, $items_per_page, $sort)
{
	global $smcFunc, $txt;

	$request = $smcFunc['db_query']('', '
		SELECT lst.id_log, lst.id_task, lst.time_run, lst.time_taken, st.task
		FROM {db_prefix}log_scheduled_tasks AS lst
			INNER JOIN {db_prefix}scheduled_tasks AS st ON (st.id_task = lst.id_task)
		ORDER BY ' . $sort . '
		LIMIT ' . $start . ', ' . $items_per_page,
		array(
		)
	);
	$log_entries = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
		$log_entries[] = array(
			'id' => $row['id_log'],
			'name' => isset($txt['scheduled_task_' . $row['task']]) ? $txt['scheduled_task_' . $row['task']] : $row['task'],
			'time_run' => $row['time_run'],
			'time_taken' => $row['time_taken'],
		);
	$smcFunc['db_free_result']($request);

	return $log_entries;
}

function list_getNumTaskLogEntries()
{
	global $smcFunc;

	$request = $smcFunc['db_query']('', '
		SELECT COUNT(*)
		FROM {db_prefix}log_scheduled_tasks',
		array(
		)
	);
	list ($num_entries) = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);

	return $num_entries;
}

// Convert both data and database tables to UTF-8 character set.
function ConvertUtf8()
{
	global $scripturl, $context, $txt, $language, $db_character_set;
	global $modSettings, $user_info, $sourcedir, $smcFunc, $db_prefix;

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
		// West European with Euro sign.
		'ISO-8859-15' => 'latin9',
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
	$request = $smcFunc['db_query']('', '
		SHOW CHARACTER SET',
		array(
		)
	);
	$db_charsets = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
		$db_charsets[] = $row['Charset'];

	// Character sets supported by both MySQL and SMF's language files.
	$charsets = array_intersect($charsets, $db_charsets);

	// This is for the first screen telling backups is good.
	if (!isset($_POST['proceed']))
	{
		// Character set conversions are only supported as of MySQL 4.1.2.
		if (version_compare('4.1.2', preg_replace('~\-.+?$~', '', $smcFunc['db_server_info']())) > 0)
			fatal_lang_error('utf8_db_version_too_low');

		// Use the messages.body column as indicator for the database charset.
		$request = $smcFunc['db_query']('', '
			SHOW FULL COLUMNS
			FROM {db_prefix}messages
			LIKE {string:body_like}',
			array(
				'body_like' => 'body',
			)
		);
		$column_info = $smcFunc['db_fetch_assoc']($request);
		$smcFunc['db_free_result']($request);

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
		if (!isset($charsets[strtr(strtolower($context['charset_detected']), array('utf' => 'UTF', 'iso' => 'ISO'))]))
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
			'0x81' => '\'\'',			'0x88' => '\'\'',			'0x8A' => '\'\'',
			'0x8C' => '\'\'',			'0x8D' => '\'\'',			'0x8E' => '\'\'',
			'0x8F' => '\'\'',			'0x90' => '\'\'',			'0x98' => '\'\'',
			'0x9A' => '\'\'',			'0x9C' => '\'\'',			'0x9D' => '\'\'',
			'0x9E' => '\'\'',			'0x9F' => '\'\'',			'0xAA' => '\'\'',
			'0xD2' => '\'\'',			'0xFF' => '\'\'',			'0xCE' => '0xCE9E',
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
			$replace = 'REPLACE(' . $replace . ', ' . $from . ', ' . $to . ')';
	}

	// Grab a list of tables.
	if (preg_match('~^`(.+?)`\.(.+?)$~', $db_prefix, $match) === 1)
		$queryTables = $smcFunc['db_query']('', '
			SHOW TABLE STATUS
			FROM `' . strtr($match[1], array('`' => '')) . '`
			LIKE {string:table_name}',
			array(
				'table_name' => str_replace('_', '\_', $match[2]) . '%',
			)
		);
	else
		$queryTables = $smcFunc['db_query']('', '
			SHOW TABLE STATUS
			LIKE {string:table_name}',
			array(
				'table_name' => str_replace('_', '\_', $db_prefix) . '%',
			)
		);

	while ($table_info = $smcFunc['db_fetch_assoc']($queryTables))
	{
		// Just to make sure it doesn't time out.
		if (function_exists('apache_reset_timeout'))
			apache_reset_timeout();

		$table_charsets = array();

		// Loop through each column.
		$queryColumns = $smcFunc['db_query']('', '
			SHOW FULL COLUMNS
			FROM ' . $table_info['Name'],
			array(
			)
		);
		while ($column_info = $smcFunc['db_fetch_assoc']($queryColumns))
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
		$smcFunc['db_free_result']($queryColumns);

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
						$updates_blob .= '
							CHANGE COLUMN ' . $column['Field'] . ' ' . $column['Field'] . ' ' . strtr($column['Type'], array('text' => 'blob', 'char' => 'binary')) . ($column['Null'] === 'YES' ? ' NULL' : ' NOT NULL') . (strpos($column['Type'], 'char') === false ? '' : ' default \'' . $column['Default'] . '\'') . ',';
						$updates_text .= '
							CHANGE COLUMN ' . $column['Field'] . ' ' . $column['Field'] . ' ' . $column['Type'] . ' CHARACTER SET ' . $charsets[$_POST['src_charset']] . ($column['Null'] === 'YES' ? '' : ' NOT NULL') . (strpos($column['Type'], 'char') === false ? '' : ' default \'' . $column['Default'] . '\'') . ',';
					}
				}
			}

			// Change the columns to binary form.
			$smcFunc['db_query']('', '
				ALTER TABLE {raw:table_name}{raw:updates_blob}',
				array(
					'table_name' => $table_info['Name'],
					'updates_blob' => substr($updates_blob, 0, -1),
				)
			);

			// Convert the character set if MySQL has no native support for it.
			if (isset($translation_tables[$_POST['src_charset']]))
			{
				$update = '';
				foreach ($table_charsets as $charset => $columns)
					foreach ($columns as $column)
						$update .= '
							' . $column['Field'] . ' = ' . strtr($replace, array('%field%' => $column['Field'])) . ',';

				$smcFunc['db_query']('', '
					UPDATE {raw:table_name}
					SET {raw:updates}',
					array(
						'table_name' => $table_info['Name'],
						'updates' => substr($update, 0, -1),
					)
				);
			}

			// Change the columns back, but with the proper character set.
			$smcFunc['db_query']('', '
				ALTER TABLE {raw:table_name}{raw:updates_text}',
				array(
					'table_name' => $table_info['Name'],
					'updates_text' => substr($updates_text, 0, -1),
				)
			);
		}

		// Now do the actual conversion (if still needed).
		if ($charsets[$_POST['src_charset']] !== 'utf8')
			$smcFunc['db_query']('', '
				ALTER TABLE {raw:table_name}
				CONVERT TO CHARACTER SET utf8',
				array(
					'table_name' => $table_info['Name'],
				)
			);
	}
	$smcFunc['db_free_result']($queryTables);

	// Let the settings know we have a new character set.
	updateSettings(array('global_character_set' => 'UTF-8'));

	// Store it in Settings.php too because it's needed before db connection.
	require_once($sourcedir . '/Subs-Admin.php');
	updateSettingsFile(array('db_character_set' => '\'utf8\''));

	// The conversion might have messed up some serialized strings. Fix them!
	require_once($sourcedir . '/Subs-Charset.php');
	fix_serialized_columns();

	redirectexit('action=admin;area=maintain');
}

// Convert HTML-entities to their UTF-8 character equivalents.
function ConvertEntities()
{
	global $db_character_set, $modSettings, $context, $sourcedir, $smcFunc;

	isAllowedTo('admin_forum');

	// Check to see if UTF-8 is currently the default character set.
	if ($modSettings['global_character_set'] !== 'UTF-8' || !isset($db_character_set) || $db_character_set !== 'utf8')
		fatal_lang_error('entity_convert_only_utf8');

	// Some starting values.
	$context['table'] = empty($_REQUEST['table']) ? 0 : (int) $_REQUEST['table'];
	$context['start'] = empty($_REQUEST['start']) ? 0 : (int) $_REQUEST['start'];

	$context['start_time'] = time();

	$context['first_step'] = !isset($_REQUEST['sesc']);
	$context['last_step'] = false;

	// The first step is just a text screen with some explanation.
	if ($context['first_step'])
	{
		$context['sub_template'] = 'convert_entities';
		return;
	}
	// Otherwise use the generic "not done" template.
	$context['sub_template'] = 'not_done';
	$context['continue_post_data'] = '';
	$context['continue_countdown'] = 3;

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
		// Make sure we keep stuff unique!
		$primary_keys = array();

		if (function_exists('apache_reset_timeout'))
			apache_reset_timeout();

		// Get a list of text columns.
		$columns = array();
		$request = $smcFunc['db_query']('', '
			SHOW FULL COLUMNS
			FROM {db_prefix}' . $cur_table,
			array(
			)
		);
		while ($column_info = $smcFunc['db_fetch_assoc']($request))
			if (strpos($column_info['Type'], 'text') !== false || strpos($column_info['Type'], 'char') !== false)
				$columns[] = strtolower($column_info['Field']);

		// Get the column with the (first) primary key.
		$request = $smcFunc['db_query']('', '
			SHOW KEYS
			FROM {db_prefix}' . $cur_table,
			array(
			)
		);
		while ($row = $smcFunc['db_fetch_assoc']($request))
		{
			if ($row['Key_name'] === 'PRIMARY')
			{
				if (empty($primary_key) || ($row['Seq_in_index'] == 1 && !in_array(strtolower($row['Column_name']), $columns)))
					$primary_key = $row['Column_name'];
					
				$primary_keys[] = $row['Column_name'];
			}
		}
		$smcFunc['db_free_result']($request);

		// No primary key, no glory.
		if (empty($primary_key))
			continue;

		// Get the maximum value for the primary key.
		$request = $smcFunc['db_query']('', '
			SELECT MAX(' . $primary_key . ')
			FROM {db_prefix}' . $cur_table,
			array(
			)
		);
		list($max_value) = $smcFunc['db_fetch_row']($request);
		$smcFunc['db_free_result']($request);

		if (empty($max_value))
			continue;

		while ($context['start'] <= $max_value)
		{
			// Retrieve a list of rows that has at least one entity to convert.
			$request = $smcFunc['db_query']('', '
				SELECT {raw:primary_keys}, {raw:columns}
				FROM {db_prefix}{raw:cur_table}
				WHERE {raw:primary_key} BETWEEN {int:start} AND {int:start} + 499
					AND {raw:like_compare}
				LIMIT 500',
				array(
					'primary_keys' => implode(', ', $primary_keys),
					'columns' => implode(', ', $columns),
					'cur_table' => $cur_table,
					'primary_key' => $primary_key,
					'start' => $context['start'],
					'like_compare' => '(' . implode(' LIKE \'%&#%\' OR ', $columns) . ' LIKE \'%&#%\')',
				)
			);
			while ($row = $smcFunc['db_fetch_assoc']($request))
			{
				$insertion_variables = array();
				$changes = array();
				foreach ($row as $column_name => $column_value)
					if ($column_name !== $primary_key && strpos($column_value, '&#') !== false)
					{
						$changes[] = $column_name . ' = {string:changes_' . $column_name . '}';
						$insertion_variables['changes_' . $column_name] = preg_replace('~(&#(\d{1,7}|x[0-9a-fA-F]{1,6});)~e', '$entity_replace(\'\\2\')', $column_value);
					}

				$where = array();
				foreach ($primary_keys as $key)
				{
					$where[] = $key . ' = {string:where_' . $key . '}';
					$insertion_variables['where_'  . $key] = $row[$key];
				}


				// Update the row.
				if (!empty($changes))
					$smcFunc['db_query']('', '
						UPDATE {db_prefix}' . $cur_table . '
						SET
							' . implode(',
							', $changes) . '
						WHERE ' . implode(' AND ', $where),
						$insertion_variables
					);
			}
			$smcFunc['db_free_result']($request);
			$context['start'] += 500;

			// After ten seconds interrupt.
			if (time() - $context['start_time'] > 10)
			{
				// Calculate an approximation of the percentage done.
				$context['continue_percent'] = round(100 * ($context['table'] + ($context['start'] / $max_value)) / $context['num_tables'], 1);
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
	$context['continue_percent'] = 100;
	$context['continue_get_data'] = '?action=admin;area=maintain';
	$context['last_step'] = true;
	$context['continue_countdown'] = -1;
}

// Optimize the database's tables.
function OptimizeTables()
{
	global $db_name, $txt, $context, $scripturl, $sourcedir, $smcFunc;

	isAllowedTo('admin_forum');

	checkSession('get');

	ignore_user_abort(true);
	db_extend();

	// Start with no tables optimized.
	$opttab = 0;

	$context['page_title'] = $txt['database_optimize'];
	$context['sub_template'] = 'optimize';

	// Get a list of tables, as well as how many there are.
	$temp_tables = $smcFunc['db_list_tables']();
	$tables = array();
	foreach ($temp_tables as $table)
		$tables[] = array('table_name' => $table);

	// If there aren't any tables then I believe that would mean the world has exploded...
	$context['num_tables'] = count($tables);
	if ($context['num_tables'] == 0)
		fatal_error('You appear to be running SMF in a flat file mode... fantastic!', false);

	// For each table....
	$context['optimized_tables'] = array();
	foreach ($tables as $table)
	{
		// Optimize the table!  We use backticks here because it might be a custom table.
		$data_freed = $smcFunc['db_optimize_table']($table['table_name']);

		if ($data_freed > 0)
			$context['optimized_tables'][] = array(
				'name' => $table['table_name'],
				'data_freed' => $data_freed,
			);
	}

	// Number of tables, etc....
	$txt['database_numb_tables'] = sprintf($txt['database_numb_tables'], $context['num_tables']);
	$context['num_tables_optimized'] = count($context['optimized_tables']);

	// Check that we don't auto optimise again too soon!
	require_once($sourcedir . '/ScheduledTasks.php');
	CalculateNextTrigger('auto_optimize', true);
}

// Recount all the important board totals.
function AdminBoardRecount()
{
	global $txt, $context, $scripturl, $modSettings, $sourcedir;
	global $time_start, $smcFunc;

	isAllowedTo('admin_forum');

	checkSession('get');

	$context['page_title'] = $txt['not_done_title'];
	$context['continue_post_data'] = '';
	$context['continue_countdown'] = '3';
	$context['sub_template'] = 'not_done';

	// Try for as much time as possible.
	@set_time_limit(600);

	// Step the number of topics at a time so things don't time out...
	$request = $smcFunc['db_query']('', '
		SELECT MAX(id_topic)
		FROM {db_prefix}topics',
		array(
		)
	);
	list ($max_topics) = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);

	$increment = min(max(50, ceil($max_topics / 4)), 2000);
	if (empty($_REQUEST['start']))
		$_REQUEST['start'] = 0;

	$total_steps = 8;

	// Get each topic with a wrong reply count and fix it - let's just do some at a time, though.
	if (empty($_REQUEST['step']))
	{
		$_REQUEST['step'] = 0;

		while ($_REQUEST['start'] < $max_topics)
		{
			$request = $smcFunc['db_query']('', '
				SELECT /*!40001 SQL_NO_CACHE */ t.id_topic, MAX(t.num_replies) AS num_replies, MAX(t.unapproved_posts) AS unapproved_posts,
					CASE WHEN COUNT(ma.id_msg) >= 1 THEN COUNT(ma.id_msg) - 1 ELSE 0 END AS real_num_replies, COUNT(mu.id_msg) AS real_unapproved_posts
				FROM {db_prefix}topics AS t
					LEFT JOIN {db_prefix}messages AS ma ON (ma.id_topic = t.id_topic AND ma.approved = {int:is_approved})
					LEFT JOIN {db_prefix}messages AS mu ON (mu.id_topic = t.id_topic AND mu.approved = {int:not_approved})
				WHERE t.id_topic > {int:start}
					AND t.id_topic <= {int:max_id}
				GROUP BY t.id_topic
				HAVING CASE WHEN COUNT(ma.id_msg) >= 1 THEN COUNT(ma.id_msg) - 1 ELSE 0 END != MAX(t.num_replies)
					OR COUNT(mu.id_msg) != MAX(t.unapproved_posts)',
				array(
					'is_approved' => 1,
					'not_approved' => 0,
					'start' => $_REQUEST['start'],
					'max_id' => $_REQUEST['start'] + $increment,
				)
			);
			while ($row = $smcFunc['db_fetch_assoc']($request))
				$smcFunc['db_query']('', '
					UPDATE {db_prefix}topics
					SET num_replies = {int:num_replies}, unapproved_posts = {int:unapproved_posts}
					WHERE id_topic = {int:id_topic}',
					array(
						'num_replies' => $row['real_num_replies'],
						'unapproved_posts' => $row['real_unapproved_posts'],
						'id_topic' => $row['id_topic'],
					)
				);
			$smcFunc['db_free_result']($request);

			$_REQUEST['start'] += $increment;

			if (array_sum(explode(' ', microtime())) - array_sum(explode(' ', $time_start)) > 3)
			{
				$context['continue_get_data'] = '?action=admin;area=maintain;sa=recount;step=0;start=' . $_REQUEST['start'] . ';sesc=' . $context['session_id'];
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
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}boards
				SET num_posts = {int:num_posts}
				WHERE redirect = {string:redirect}',
				array(
					'num_posts' => 0,
					'redirect' => '',
				)
			);

		while ($_REQUEST['start'] < $max_topics)
		{
			$request = $smcFunc['db_query']('', '
				SELECT /*!40001 SQL_NO_CACHE */ m.id_board, COUNT(*) AS real_num_posts
				FROM {db_prefix}messages AS m
				WHERE m.id_topic > {int:id_topic_min}
					AND m.id_topic <= {int:id_topic_max}
					AND m.approved = {int:is_approved}
				GROUP BY m.id_board',
				array(
					'id_topic_min' => $_REQUEST['start'],
					'id_topic_max' => $_REQUEST['start'] + $increment,
					'is_approved' => 1,
				)
			);
			while ($row = $smcFunc['db_fetch_assoc']($request))
				$smcFunc['db_query']('', '
					UPDATE {db_prefix}boards
					SET num_posts = num_posts + {int:real_num_posts}
					WHERE id_board = {int:id_board}',
					array(
						'id_board' => $row['id_board'],
						'real_num_posts' => $row['real_num_posts'],
					)
				);
			$smcFunc['db_free_result']($request);

			$_REQUEST['start'] += $increment;

			if (array_sum(explode(' ', microtime())) - array_sum(explode(' ', $time_start)) > 3)
			{
				$context['continue_get_data'] = '?action=admin;area=maintain;sa=recount;step=1;start=' . $_REQUEST['start'] . ';sesc=' . $context['session_id'];
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
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}boards
				SET num_topics = {int:num_topics}',
				array(
					'num_topics' => 0,
				)
			);

		while ($_REQUEST['start'] < $max_topics)
		{
			$request = $smcFunc['db_query']('', '
				SELECT /*!40001 SQL_NO_CACHE */ t.id_board, COUNT(*) AS real_num_topics
				FROM {db_prefix}topics AS t
				WHERE t.approved = {int:is_approved}
					AND t.id_topic > {int:id_topic_min}
					AND t.id_topic <= {int:id_topic_max}
				GROUP BY t.id_board',
				array(
					'is_approved' => 1,
					'id_topic_min' => $_REQUEST['start'],
					'id_topic_max' => $_REQUEST['start'] + $increment,
				)
			);
			while ($row = $smcFunc['db_fetch_assoc']($request))
				$smcFunc['db_query']('', '
					UPDATE {db_prefix}boards
					SET num_topics = num_topics + {int:real_num_topics}
					WHERE id_board = {int:id_board}',
					array(
						'id_board' => $row['id_board'],
						'real_num_topics' => $row['real_num_topics'],
					)
				);
			$smcFunc['db_free_result']($request);

			$_REQUEST['start'] += $increment;

			if (array_sum(explode(' ', microtime())) - array_sum(explode(' ', $time_start)) > 3)
			{
				$context['continue_get_data'] = '?action=admin;area=maintain;sa=recount;step=2;start=' . $_REQUEST['start'] . ';sesc=' . $context['session_id'];
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
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}boards
				SET unapproved_posts = {int:unapproved_posts}',
				array(
					'unapproved_posts' => 0,
				)
			);

		while ($_REQUEST['start'] < $max_topics)
		{
			$request = $smcFunc['db_query']('', '
				SELECT /*!40001 SQL_NO_CACHE */ m.id_board, COUNT(*) AS real_unapproved_posts
				FROM {db_prefix}messages AS m
				WHERE m.id_topic > {int:id_topic_min}
					AND m.id_topic <= {int:id_topic_max}
					AND m.approved = {int:is_approved}
				GROUP BY m.id_board',
				array(
					'id_topic_min' => $_REQUEST['start'],
					'id_topic_max' => $_REQUEST['start'] + $increment,
					'is_approved' => 0,
				)
			);
			while ($row = $smcFunc['db_fetch_assoc']($request))
				$smcFunc['db_query']('', '
					UPDATE {db_prefix}boards
					SET unapproved_posts = unapproved_posts + {int:unapproved_posts}
					WHERE id_board = {int:id_board}',
					array(
						'id_board' => $row['id_board'],
						'unapproved_posts' => $row['unapproved_posts'],
					)
				);
			$smcFunc['db_free_result']($request);

			$_REQUEST['start'] += $increment;

			if (array_sum(explode(' ', microtime())) - array_sum(explode(' ', $time_start)) > 3)
			{
				$context['continue_get_data'] = '?action=admin;area=maintain;sa=recount;step=3;start=' . $_REQUEST['start'] . ';sesc=' . $context['session_id'];
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
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}boards
				SET unapproved_topics = {int:unapproved_topics}',
				array(
					'unapproved_topics' => 0,
				)
			);

		while ($_REQUEST['start'] < $max_topics)
		{
			$request = $smcFunc['db_query']('', '
				SELECT /*!40001 SQL_NO_CACHE */ t.id_board, COUNT(*) AS real_unapproved_topics
				FROM {db_prefix}topics AS t
				WHERE t.approved = {int:is_approved}
					AND t.id_topic > {int:id_topic_min}
					AND t.id_topic <= {int:id_topic_max}
				GROUP BY t.id_board',
				array(
					'is_approved' => 0,
					'id_topic_min' => $_REQUEST['start'],
					'id_topic_max' => $_REQUEST['start'] + $increment,
				)
			);
			while ($row = $smcFunc['db_fetch_assoc']($request))
				$smcFunc['db_query']('', '
					UPDATE {db_prefix}boards
					SET unapproved_topics = unapproved_topics + {int:real_unapproved_topics}
					WHERE id_board = {int:id_board}',
					array(
						'id_board' => $row['id_board'],
						'real_unapproved_topics' => $row['real_unapproved_topics'],
					)
				);
			$smcFunc['db_free_result']($request);

			$_REQUEST['start'] += $increment;

			if (array_sum(explode(' ', microtime())) - array_sum(explode(' ', $time_start)) > 3)
			{
				$context['continue_get_data'] = '?action=admin;area=maintain;sa=recount;step=4;start=' . $_REQUEST['start'] . ';sesc=' . $context['session_id'];
				$context['continue_percent'] = round((500 + 100 * $_REQUEST['start'] / $max_topics) / $total_steps);

				return;
			}
		}

		$_REQUEST['start'] = 0;
	}

	// Get all members with wrong number of personal messages.
	if ($_REQUEST['step'] <= 5)
	{
		$request = $smcFunc['db_query']('', '
			SELECT /*!40001 SQL_NO_CACHE */ mem.id_member, COUNT(pmr.id_pm) AS real_num,
				MAX(mem.instant_messages) AS instant_messages
			FROM {db_prefix}members AS mem
				LEFT JOIN {db_prefix}pm_recipients AS pmr ON (mem.id_member = pmr.id_member AND pmr.deleted = {int:is_not_deleted})
			GROUP BY mem.id_member
			HAVING COUNT(pmr.id_pm) != MAX(mem.instant_messages)',
			array(
				'is_not_deleted' => 0,
			)
		);
		while ($row = $smcFunc['db_fetch_assoc']($request))
			updateMemberData($row['id_member'], array('instant_messages' => $row['real_num']));
		$smcFunc['db_free_result']($request);

		$request = $smcFunc['db_query']('', '
			SELECT /*!40001 SQL_NO_CACHE */ mem.id_member, COUNT(pmr.id_pm) AS real_num,
				MAX(mem.unread_messages) AS unread_messages
			FROM {db_prefix}members AS mem
				LEFT JOIN {db_prefix}pm_recipients AS pmr ON (mem.id_member = pmr.id_member AND pmr.deleted = {int:is_not_deleted} AND pmr.is_read = {int:is_not_read})
			GROUP BY mem.id_member
			HAVING COUNT(pmr.id_pm) != MAX(mem.unread_messages)',
			array(
				'is_not_deleted' => 0,
				'is_not_read' => 0,
			)
		);
		while ($row = $smcFunc['db_fetch_assoc']($request))
			updateMemberData($row['id_member'], array('unread_messages' => $row['real_num']));
		$smcFunc['db_free_result']($request);

		if (array_sum(explode(' ', microtime())) - array_sum(explode(' ', $time_start)) > 3)
		{
			$context['continue_get_data'] = '?action=admin;area=maintain;sa=recount;step=6;start=0;sesc=' . $context['session_id'];
			$context['continue_percent'] = round(700 / $total_steps);

			return;
		}
	}

	// Any messages pointing to the wrong board?
	if ($_REQUEST['step'] <= 6)
	{
		while ($_REQUEST['start'] < $modSettings['maxMsgID'])
		{
			$request = $smcFunc['db_query']('', '
				SELECT /*!40001 SQL_NO_CACHE */ t.id_board, m.id_msg
				FROM {db_prefix}messages AS m
					INNER JOIN {db_prefix}topics AS t ON (t.id_topic = m.id_topic AND t.id_board != m.id_board)
				WHERE m.id_msg > {int:id_msg_min}
					AND m.id_msg <= {int:id_msg_max}',
				array(
					'id_msg_min' => $_REQUEST['start'],
					'id_msg_max' => $_REQUEST['start'] + $increment,
				)
			);
			$boards = array();
			while ($row = $smcFunc['db_fetch_assoc']($request))
				$boards[$row['id_board']][] = $row['id_msg'];
			$smcFunc['db_free_result']($request);

			foreach ($boards as $board_id => $messages)
				$smcFunc['db_query']('', '
					UPDATE {db_prefix}messages
					SET id_board = {int:id_board}
					WHERE id_msg IN ({array_int:id_msg_array})',
					array(
						'id_msg_array' => $messages,
						'id_board' => $board_id,
					)
				);

			$_REQUEST['start'] += $increment;

			if (array_sum(explode(' ', microtime())) - array_sum(explode(' ', $time_start)) > 3)
			{
				$context['continue_get_data'] = '?action=admin;area=maintain;sa=recount;step=6;start=' . $_REQUEST['start'] . ';sesc=' . $context['session_id'];
				$context['continue_percent'] = round((700 + 100 * $_REQUEST['start'] / $modSettings['maxMsgID']) / $total_steps);

				return;
			}
		}

		$_REQUEST['start'] = 0;
	}

	// Update the latest message of each board.
	$request = $smcFunc['db_query']('', '
		SELECT m.id_board, MAX(m.id_msg) AS local_last_msg
		FROM {db_prefix}messages AS m
		WHERE m.approved = {int:is_approved}
		GROUP BY m.id_board',
		array(
			'is_approved' => 1,
		)
	);
	$realBoardCounts = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
		$realBoardCounts[$row['id_board']] = $row['local_last_msg'];
	$smcFunc['db_free_result']($request);

	$request = $smcFunc['db_query']('', '
		SELECT /*!40001 SQL_NO_CACHE */ id_board, id_parent, id_last_msg, child_level
		FROM {db_prefix}boards',
		array(
		)
	);
	$resort_me = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$row['local_last_msg'] = isset($realBoardCounts[$row['id_board']]) ? $realBoardCounts[$row['id_board']] : 0;
		$resort_me[$row['child_level']][] = $row;
	}
	$smcFunc['db_free_result']($request);

	krsort($resort_me);

	$lastMsg = array();
	foreach ($resort_me as $rows)
		foreach ($rows as $row)
		{
			// The latest message is the latest of the current board and its children.
			if (isset($lastMsg[$row['id_board']]))
				$curLastMsg = max($row['local_last_msg'], $lastMsg[$row['id_board']]);
			else
				$curLastMsg = $row['local_last_msg'];

			// If what is and what should be the latest message differ, an update is necessary.
			if ($curLastMsg != $row['id_last_msg'])
				$smcFunc['db_query']('', '
					UPDATE {db_prefix}boards
					SET id_last_msg = {int:id_last_msg}
					WHERE id_board = {int:id_board}',
					array(
						'id_last_msg' => $curLastMsg,
						'id_board' => $row['id_board'],
					)
				);

			// Parent boards inherit the latest message of their children.
			if (isset($lastMsg[$row['id_parent']]))
				$lastMsg[$row['id_parent']] = max($row['local_last_msg'], $lastMsg[$row['id_parent']]);
			else
				$lastMsg[$row['id_parent']] = $row['local_last_msg'];
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

// Perform a detailed version check.  A very good thing ;).
function VersionDetail()
{
	global $forum_version, $txt, $sourcedir, $context;

	isAllowedTo('admin_forum');

	// Call the function that'll get all the version info we need.
	require_once($sourcedir . '/Subs-Admin.php');
	$versionOptions = array(
		'include_ssi' => true,
		'include_subscriptions' => true,
		'sort_results' => true,
	);
	$version_info = getFileVersions($versionOptions);

	// Add the new info to the template context.
	$context += array(
		'file_versions' => $version_info['file_versions'],
		'default_template_versions' => $version_info['default_template_versions'],
		'template_versions' => $version_info['template_versions'],
		'default_language_versions' => $version_info['default_language_versions'],
		'default_known_languages' => array_keys($version_info['default_language_versions']),
	);

	// Make it easier to manage for the template.
	$context['forum_version'] = $forum_version;

	$context['sub_template'] = 'view_versions';
	$context['page_title'] = $txt['admin_version_check'];
}

// This function caches the relevant language files, and if the cache doesn't work includes them with eval.
function cacheLanguage($template_name, $lang, $fatal, $theme_name)
{
	global $language, $settings, $txt, $modSettings;
	global $sourcedir, $cachedir, $smcFunc;

	// Is the file writable?
	$can_write = !empty($modSettings['cache_enable']) && is_writable($cachedir) ? 1 : 0;

	// By default include it afterwards.
	$do_include = $can_write;
	$invalid_file_found = false;

	// Make sure we have $settings - if not we're in trouble and need to find it!
	if (empty($settings['default_theme_dir']))
	{
		require_once($sourcedir . '/ScheduledTasks.php');
		loadEssentialThemeData();
	}

	// Open the file to write to.
	if ($can_write)
	{
		$fh = fopen($cachedir . '/lang_' . $template_name . '_' . $lang . '_' . $theme_name . '.php', 'w');
		@flock($fp, LOCK_EX);
		fwrite($fh, '<?php' . "\n");
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
				// Are we caching?
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
					require($file[0] . '/languages/' . $file[1] . '.' . $file[2] . '.php');

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
			$invalid_file_found = true;

			if ($fatal)
				log_error(sprintf($txt['theme_language_error'], $template_name . '.' . $lang, 'template'));
			break;
		}
		else
			unset($language_url);
	}

	if ($can_write)
	{
		fwrite($fh, '?>');
		@flock($fp, LOCK_UN);
		fclose($fh);

		// If we couldn't find the file don't cache it!
		if ($invalid_file_found)
		{
			@unlink($cachedir . '/lang_' . $template_name . '_' . $lang . '_' . $theme_name . '.php');
			$do_include = false;
		}
	}

	return $do_include;
}

// Get things ready for minor admin tasks.
function AdminTask()
{
	global $txt, $scripturl, $context, $helptxt, $user_info, $smcFunc;

	checkSession('request');

	loadLanguage('Help');

	if (!isset($_GET['activity']) || !function_exists('activity_' . $_GET['activity']))
		fatal_lang_error('no_access');

	$context['maintain_activity'] = $_GET['activity'];

	// Let's load up the boards in case they are useful.
	$result = $smcFunc['db_query']('', '
		SELECT b.id_board, b.name, b.child_level, c.name AS cat_name, c.id_cat
		FROM {db_prefix}boards AS b
			LEFT JOIN {db_prefix}categories AS c ON (c.id_cat = b.id_cat)
		WHERE {query_see_board}',
		array(
		)
	);
	$context['categories'] = array();
	while ($row = $smcFunc['db_fetch_assoc']($result))
	{
		if (!isset($context['categories'][$row['id_cat']]))
			$context['categories'][$row['id_cat']] = array(
				'name' => $row['cat_name'],
				'boards' => array()
			);

		$context['categories'][$row['id_cat']]['boards'][] = array(
			'id' => $row['id_board'],
			'name' => $row['name'],
			'child_level' => $row['child_level']
		);
	}
	$smcFunc['db_free_result']($result);

	// Get membergroups - for deleting members and the like.
	$result = $smcFunc['db_query']('', '
		SELECT id_group, group_name
		FROM {db_prefix}membergroups',
		array(
		)
	);
	$context['membergroups'] = array(
		array(
			'id' => 0,
			'name' => $txt['maintain_members_ungrouped']
		),
	);
	while ($row = $smcFunc['db_fetch_assoc']($result))
	{
		$context['membergroups'][] = array(
			'id' => $row['id_group'],
			'name' => $row['group_name']
		);
	}
	$smcFunc['db_free_result']($result);

	// Just loading the template?
	if (empty($_POST['do']))
	{
		$context['sub_template'] = 'activity_' . $context['maintain_activity'];

		// Help text or page title?
		$context['help_text'] = isset($helptxt[$context['maintain_activity']]) ? $context['maintain_activity'] : false;
		$context['page_title'] = isset($txt[$context['maintain_activity']]) ? $txt[$context['maintain_activity']] : $txt['maintain_general'];

		// Setup the layer.
		$context['template_layers'][] = 'maintain';
	}

	// Call the function - telling them what we're up to.
	call_user_func_array('activity_' . $_GET['activity'], array('do_action' => !empty($_POST['do'])));

	// If we're done then go away.
	if (!empty($_POST['do']))
		redirectexit('action=admin;area=maintain;done');
}

// Removing old posts doesn't take much as we really pass through.
function activity_maintain_reattribute_posts($do_action = true)
{
	global $sourcedir, $context;

	if (!$do_action || !isset($_POST['to']))
		return true;

	// Find the member.
	require_once($sourcedir . '/Subs-Auth.php');
	$members = findMembers($_POST['to']);

	if (empty($members))
		fatal_lang_error('reattribute_cannot_find_member');

	$memID = array_shift($members);
	$memID = $memID['id'];

	$email = $_POST['type'] == 'email' ? $_POST['from_email'] : '';
	$membername = $_POST['type'] == 'name' ? $_POST['from_name'] : '';

	// Now call the reattribute function.
	require_once($sourcedir . '/Subs-Members.php');
	reattributePosts($memID, $email, $membername, !empty($_POST['posts']));
}

// Handling function for the backup stuff.
function activity_maintain_backup($do_action = true)
{
	global $sourcedir, $context, $smcFunc;

	if (!$do_action)
	{
		// Setup the help button.
		$context['help_text'] = 'maintenance_backup';

		return true;
	}

	require_once($sourcedir . '/DumpDatabase.php');
	DumpDatabase2();
}

// Removing old members?
function activity_maintain_members($do_action = true)
{
	global $sourcedir, $context, $smcFunc;

	if (!$do_action)
	{
		// Setup the help button.
		$context['help_text'] = 'maintenance_members';

		return true;
	}

	$_POST['maxdays'] = (int) $_POST['maxdays'];
	if (!empty($_POST['groups']) && $_POST['maxdays'])
	{
		$groups = array();
		foreach ($_POST['groups'] as $id => $dummy)
			$groups[] = (int) $id;
		$time_limit = (time() - ($_POST['maxdays'] * 24 * 3600));
		$where_vars = array(
			'last_login' => $time_limit,
		);
		$where = 'mem.last_login < {int:last_login}';
		if ($_POST['del_type'] == 'activated')
		{
			$where .= ' AND mem.is_activated = {int:is_activated}';
			$where_vars['is_activated'] = 0;
		}

		// Need to get *all* groups then work out which (if any) we avoid.
		$request = $smcFunc['db_query']('', '
			SELECT id_group, group_name, min_posts
			FROM {db_prefix}membergroups',
			array(
			)
		);
		while ($row = $smcFunc['db_fetch_assoc']($request))
		{
			// Avoid this one?
			if (!in_array($row['id_group'], $groups))
			{
				// Post group?
				if ($row['min_posts'] != -1)
				{
					$where .= ' AND mem.id_post_group != {int:id_post_group_' . $row['id_group'] . '}';
					$where_vars['id_post_group_' . $row['id_group']] = $row['id_group'];
				}
				else
				{
					$where .= ' AND mem.id_group != {int:id_group_' . $row['id_group'] . '} AND NOT FIND_IN_SET({int:id_group_' . $row['id_group'] . '}, mem.additional_groups)';
					$where_vars['id_group_' . $row['id_group']] = $row['id_group'];
				}
			}
		}
		$smcFunc['db_free_result']($request);

		// If we have ungrouped unselected we need to avoid those guys.
		if (!in_array(0, $groups))
		{
			$where .= ' AND (mem.id_group != 0 OR mem.additional_groups != {string:blank_add_groups})';
			$where_vars['blank_add_groups'] = '';
		}

		// Select all the members we're about to murder/remove...
		$request = $smcFunc['db_query']('', '
			SELECT mem.id_member, IFNULL(m.id_member, 0) AS is_mod
			FROM {db_prefix}members AS mem
				LEFT JOIN {db_prefix}moderators AS m ON (m.id_member = mem.id_member)
			WHERE ' . $where,
			$where_vars
		);
		$members = array();
		while ($row = $smcFunc['db_fetch_assoc']($request))
		{
			if (!$row['is_mod'] || !in_array(3, $groups))
				$members[] = $row['id_member'];
		}
		$smcFunc['db_free_result']($request);

		require_once($sourcedir . '/Subs-Members.php');
		deleteMembers($members);
	}
}

// Removing old posts doesn't take much as we really pass through.
function activity_maintain_old($do_action = true)
{
	global $sourcedir, $context;

	if (!$do_action)
	{
		// We have a different help string.
		$context['help_text'] = 'maintenance_rot';

		return true;
	}

	// Actually do what we're told!
	require_once($sourcedir . '/RemoveTopic.php');
	RemoveOldTopics2();
}

function activity_move_topics_maintenance($do_action = true)
{
	global $smcFunc, $sourcedir, $context, $txt;

	// Not doing it - nothing to do!
	if (!$do_action)
		return true;

	// Only admins.
	isAllowedTo('admin_forum');

	checkSession('request');

	// Set up to the context.
	$context['page_title'] =  $txt['not_done_title'];
	$context['continue_countdown'] = '3';
	$context['continue_post_data'] = '';
	$context['continue_get_data'] = '';
	$context['sub_template'] = 'not_done';
	$context['start'] = empty($_REQUEST['start']) ? 0 : (int) $_REQUEST['start'];
	$context['start_time'] =  time();

	// First time we do this?
	$id_board_from = isset($_POST['id_board_from']) ? (int) $_POST['id_board_from'] : (int) $_REQUEST['id_board_from'];
	$id_board_to = isset($_POST['id_board_to']) ? (int) $_POST['id_board_to'] : (int) $_REQUEST['id_board_to'];

	// No boards then this is your stop.
	if (empty($id_board_from) || empty($id_board_to))
		return;

	// How many topics are we converting?
	if (!isset($_REQUEST['totaltopics']))
	{
		$request = $smcFunc['db_query']('', '
			SELECT COUNT(*)
			FROM {db_prefix}topics
			WHERE id_board = {int:id_board_from}',
			array(
				'id_board_from' => $id_board_from,
			)
		);
		list ($total_topics) = $smcFunc['db_fetch_row']($request);
		$smcFunc['db_free_result']($request);
	}
	else
		$total_topics = (int) $_REQUEST['totaltopics'];

	// Seems like we need this here.
	$context['continue_get_data'] = '?action=admin;area=maintain;sa=movetopics;id_board_from=' . $id_board_from . ';id_board_to=' . $id_board_to . ';totaltopics=' . $total_topics . ';start=' . $context['start'] . ';sesc=' . $context['session_id'];

	// We have topics to move so start the process.
	if (!empty($total_topics))
	{
		while ($context['start'] <= $total_topics)
		{
			// Lets get the topics.
			$request = $smcFunc['db_query']('', '
				SELECT id_topic
				FROM {db_prefix}topics
				WHERE id_board = {int:id_board_from}
				LIMIT 10',
				array(
					'id_board_from' => $id_board_from,
				)
			);

			// Get the ids.
			$topics = array();
			while ($row = $smcFunc['db_fetch_assoc']($request))
				$topics[] = $row['id_topic'];

			// Just return if we don't have any topics left to move.
			if (empty($topics))
			{
				cache_put_data('board-' . $id_board_from, null, 120);
				cache_put_data('board-' . $id_board_to, null, 120);
				redirectexit('action=admin;area=maintain;done');
			}

			// Lets move them.
			require_once($sourcedir . '/MoveTopic.php');
			moveTopics($topics, $id_board_to);

			// We've done at least ten more topics.
			$context['start'] += 10;

			// Lets wait a while.
			if (time() - $context['start_time'] > 3)
			{
				// What's the percent?
				$context['continue_percent'] = round(100 * ($context['start'] / $total_topics), 1);
				$context['continue_get_data'] = '?action=admin;area=maintain;sa=movetopics;id_board_from=' . $id_board_from . ';id_board_to=' . $id_board_to . ';totaltopics=' . $total_topics . ';start=' . $context['start'] . ';sesc=' . $context['session_id'];

				// Let the template system do it's thang.
				return;
			}
		}
	}

	// Don't confuse admins by having an out of date cache.
	cache_put_data('board-' . $id_board_from, null, 120);
	cache_put_data('board-' . $id_board_to, null, 120);

	redirectexit('action=admin;area=maintain;done');
}

?>