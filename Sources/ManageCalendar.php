<?php
/******************************************************************************
* ManageCalendar.php                                                          *
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

// The main controlling function doesn't have much to do... yet.
function ManageCalendar()
{
	global $context, $txt, $scripturl, $modSettings;

	isAllowedTo('admin_forum');

	// Everything's gonna need this.
	loadLanguage('ManageCalendar');

	// Default text.
	$context['explain_text'] = &$txt['calendar_desc'];

	// Little short on the ground of functions here... but things can and maybe will change...
	$subActions = array(
		'editholiday' => 'EditHoliday',
		'holidays' => 'ModifyHolidays',
		'settings' => 'ModifyCalendarSettings'
	);

	$_REQUEST['sa'] = isset($_REQUEST['sa']) && isset($subActions[$_REQUEST['sa']]) ? $_REQUEST['sa'] : 'settings';

	// Set up the two tabs here...
	$context['admin_tabs'] = array(
		'title' => &$txt['manage_calendar'],
		'help' => 'calendar',
		'description' => $txt['calendar_settings_desc'],
		'tabs' => array(
			'holidays' => array(
				'title' => $txt['manage_holidays'],
				'description' => $txt['manage_holidays_desc'],
				'href' => $scripturl . '?action=admin;area=managecalendar;sa=holidays',
			),
			'settings' => array(
				'title' => $txt['calendar_settings'],
				'description' => $txt['calendar_settings_desc'],
				'href' => $scripturl . '?action=admin;area=managecalendar;sa=settings',
				'is_last' => true,
			),
		),
	);

	// Select the tab they're at...
	if (isset($context['admin_tabs']['tabs'][$_REQUEST['sa']]))
		$context['admin_tabs']['tabs'][$_REQUEST['sa']]['is_selected'] = true;

	// Some settings may not be enabled, disallow these from the tabs as appropriate.
	if (empty($modSettings['cal_enabled']))
		unset($context['admin_tabs']['tabs']['holidays']);

	$subActions[$_REQUEST['sa']]();
}

// The function that handles adding, and deleting holiday data
function ModifyHolidays()
{
	global $txt, $context, $db_prefix, $scripturl;

	loadTemplate('ManageCalendar');

	$context['page_title'] = $txt['manage_holidays'];
	$context['sub_template'] = 'manage_holidays';

	// Submitting something...
	if (isset($_REQUEST['delete']) && !empty($_REQUEST['holiday']))
	{
		checkSession();

		foreach ($_REQUEST['holiday'] AS $id => $value)
			$_REQUEST['holiday'][$id] = (int) $id;

		// Now the IDs are "safe" do the delete...
		db_query("
			DELETE FROM {$db_prefix}calendar_holidays
			WHERE ID_HOLIDAY IN (" . implode(', ', $_REQUEST['holiday']) . ")
			LIMIT " . count($_REQUEST['holiday']), __FILE__, __LINE__);

		updateStats('calendar');
	}

	// Total amount of holidays... for pagination.
	$request = db_query("
		SELECT COUNT(*)
		FROM {$db_prefix}calendar_holidays", __FILE__, __LINE__);
	list ($context['holidayCount']) = mysql_fetch_row($request);
	mysql_free_result($request);

	$context['page_index'] = constructPageIndex($scripturl . '?action=admin;area=managecalendar;sa=holidays', $_REQUEST['start'], $context['holidayCount'], 20);

	// Now load up all the holidays into a lovely large array.
	$request = db_query("
		SELECT ID_HOLIDAY, YEAR(eventDate) AS year, MONTH(eventDate) AS month, DAYOFMONTH(eventDate) AS day, title
		FROM {$db_prefix}calendar_holidays
		ORDER BY title
		LIMIT $_REQUEST[start], 20", __FILE__, __LINE__);
	$context['holidays'] = array();
	while ($row = mysql_fetch_assoc($request))
		$context['holidays'][] = array(
			'id' => $row['ID_HOLIDAY'],
			'date' => $row['day'] . ' ' . $txt['months'][$row['month']] . ' ' . ($row['year'] == '0004' ? '(' . $txt['every_year'] . ')' : $row['year']),
			'title' => $row['title']
		);
	mysql_free_result($request);
}

// This function is used for adding/editing a specific holiday
function EditHoliday()
{
	global $txt, $context, $db_prefix, $scripturl;

	loadTemplate('ManageCalendar');

	$context['is_new'] = !isset($_REQUEST['holiday']);
	$context['page_title'] = $context['is_new'] ? $txt['holidays_add'] : $txt['holidays_edit'];
	$context['sub_template'] = 'edit_holiday';
	$context['admin_tabs']['tabs']['holidays']['is_selected'] = true;

	// Submitting?
	if (isset($_POST['sc']) && (isset($_REQUEST['delete']) || $_REQUEST['title'] != ''))
	{
		checkSession();

		if (isset($_REQUEST['delete']))
			db_query("
				DELETE FROM {$db_prefix}calendar_holidays
				WHERE ID_HOLIDAY = $_REQUEST[holiday]", __FILE__, __LINE__);
		else
		{
			$date = strftime($_REQUEST['year'] <= 4 ? '0004-%m-%d' : '%Y-%m-%d', mktime(0, 0, 0, $_REQUEST['month'], $_REQUEST['day'], $_REQUEST['year']));
			if (isset($_REQUEST['edit']))
				db_query("
					UPDATE {$db_prefix}calendar_holidays
					SET eventDate = '$date', title = '$_REQUEST[title]'
					WHERE ID_HOLIDAY = $_REQUEST[holiday]", __FILE__, __LINE__);
			else
				db_query("
					INSERT INTO {$db_prefix}calendar_holidays
						(eventDate, title)
					VALUES
						('$date', SUBSTRING('$_REQUEST[title]', 1, 48))", __FILE__, __LINE__);
		}

		updateStats('calendar');

		redirectexit('action=admin;area=managecalendar;sa=holidays');
	}

	// Default states...
	if ($context['is_new'])
		$context['holiday'] = array(
			'id' => 0,
			'day' => date('d'),
			'month' => date('m'),
			'year' => '0000',
			'title' => ''
		);
	// If it's not new load the data.
	else
	{
		$request = db_query("
			SELECT ID_HOLIDAY, YEAR(eventDate) AS year, MONTH(eventDate) AS month, DAYOFMONTH(eventDate) AS day, title
			FROM {$db_prefix}calendar_holidays
			WHERE ID_HOLIDAY = $_REQUEST[holiday]
			LIMIT 1", __FILE__, __LINE__);
		while ($row = mysql_fetch_assoc($request))
			$context['holiday'] = array(
				'id' => $row['ID_HOLIDAY'],
				'day' => $row['day'],
				'month' => $row['month'],
				'year' => $row['year'] <= 4 ? 0 : $row['year'],
				'title' => $row['title']
			);
		mysql_free_result($request);
	}

	// Last day for the drop down?
	$context['holiday']['last_day'] = (int) strftime('%d', mktime(0, 0, 0, $context['holiday']['month'] == 12 ? 1 : $context['holiday']['month'] + 1, 0, $context['holiday']['month'] == 12 ? $context['holiday']['year'] + 1 : $context['holiday']['year']));
}

function ModifyCalendarSettings()
{
	global $modSettings, $context, $settings, $db_prefix, $txt, $boarddir, $sourcedir, $scripturl;

	// Get the settings template fired up.
	require_once($sourcedir .'/ManageServer.php');

	// Some important context stuff
	$context['page_title'] = $txt['calendar_settings'];
	$context['sub_template'] = 'modify_settings';
	$context['sub_template'] = 'show_settings';

	// Load the boards list.
	$boards = array('');
	$request = db_query("
		SELECT b.ID_BOARD, b.name AS bName, c.name AS cName
		FROM {$db_prefix}boards AS b
			LEFT JOIN {$db_prefix}categories AS c ON (c.ID_CAT = b.ID_CAT)", __FILE__, __LINE__);
	while ($row = mysql_fetch_assoc($request))
		$boards[$row['ID_BOARD']] = $row['cName'] . ' - ' . $row['bName'];
	mysql_free_result($request);

	// Look, all the calendar settings - of which there are many!
	$config_vars = array(
			// All the permissions:
			array('check', 'cal_enabled'),
			array('permissions', 'calendar_view'),
			array('permissions', 'calendar_post'),
			array('permissions', 'calendar_edit_own'),
			array('permissions', 'calendar_edit_any'),
		'',
			// Some visual settings.
			array('check', 'cal_daysaslink'),
			array('check', 'cal_showweeknum'),
		'',
			// How many days to show on board index, and where to display events etc?
			array('int', 'cal_days_for_index'),
			array('select', 'cal_showholidays', array(0 => $txt['setting_cal_show_never'], 1 => $txt['setting_cal_show_cal'], 3 => $txt['setting_cal_show_index'], 2 => $txt['setting_cal_show_all'])),
			array('select', 'cal_showbdays', array(0 => $txt['setting_cal_show_never'], 1 => $txt['setting_cal_show_cal'], 3 => $txt['setting_cal_show_index'], 2 => $txt['setting_cal_show_all'])),
			array('select', 'cal_showevents', array(0 => $txt['setting_cal_show_never'], 1 => $txt['setting_cal_show_cal'], 3 => $txt['setting_cal_show_index'], 2 => $txt['setting_cal_show_all'])),
		'',
			// Linking events etc...
			array('select', 'cal_defaultboard', $boards),
			array('check', 'cal_allow_unlinked'),
			array('check', 'cal_showInTopic'),
		'',
			// Dates of calendar...
			array('int', 'cal_minyear'),
			array('int', 'cal_maxyear'),
		'',
			// Calendar spanning...
			array('check', 'cal_allowspan'),
			array('int', 'cal_maxspan'),
	);

	// Get the final touches in place.
	$context['post_url'] = $scripturl . '?action=admin;area=managecalendar;save;sa=settings';
	$context['settings_title'] = $txt['calendar_settings'];

	// Saving the settings?
	if (isset($_GET['save']))
	{
		saveDBSettings($config_vars);

		// Update the stats incase.
		updateStats('calendar');

		redirectexit('action=admin;area=managecalendar;sa=settings');
	}

	// Prepare the settings...
	prepareDBSettingContext($config_vars);
}

?>