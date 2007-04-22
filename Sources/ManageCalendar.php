<?php
/**********************************************************************************
* ManageCalendar.php                                                              *
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
	$context[$context['admin_menu_name']]['tab_data'] = array(
		'title' => &$txt['manage_calendar'],
		'help' => 'calendar',
		'description' => $txt['calendar_settings_desc'],
		'tabs' => array(
			'holidays' => array(
				'description' => $txt['manage_holidays_desc'],
			),
			'settings' => array(
				'description' => $txt['calendar_settings_desc'],
			),
		),
	);

	$subActions[$_REQUEST['sa']]();
}

// The function that handles adding, and deleting holiday data
function ModifyHolidays()
{
	global $sourcedir, $scripturl, $txt, $context;

	// Submitting something...
	if (isset($_REQUEST['delete']) && !empty($_REQUEST['holiday']))
	{
		checkSession();

		foreach ($_REQUEST['holiday'] AS $id => $value)
			$_REQUEST['holiday'][$id] = (int) $id;

		// Now the IDs are "safe" do the delete...
		require_once($sourcedir . '/Subs-Calendar.php');
		removeHolidays($_REQUEST['holiday']);
	}

	$listOptions = array(
		'id' => 'holiday_list',
		'title' => $txt['current_holidays'],
		'items_per_page' => 20,
		'base_href' => $scripturl . '?action=admin;area=managecalendar;sa=holidays',
		'default_sort_col' => 'name',
		'get_items' => array(
			'file' => $sourcedir . '/Subs-Calendar.php',
			'function' => 'list_getHolidays',
		),
		'get_count' => array(
			'file' => $sourcedir . '/Subs-Calendar.php',
			'function' => 'list_getNumHolidays',
		),
		'columns' => array(
			'name' => array(
				'header' => array(
					'value' => $txt['holidays_title'],
				),
				'data' => array(
					'sprintf' => array(
						'format' => '<a href="' . $scripturl . '?action=admin;area=managecalendar;sa=editholiday;holiday=%1$d">%2$s</a>',
						'params' => array(
							'id_holiday' => false,
							'title' => false,
						),
					),
				),
				'sort' => array(
					'default' => 'title',
					'reverse' => 'title DESC',
				)
			),
			'date' => array(
				'header' => array(
					'value' => $txt['date'],
				),
				'data' => array(
					'eval' => 'return %day% . \' \' . $txt[\'months\'][%month%] . \' \' . (%year% == \'0004\' ? \'(\' . $txt[\'every_year\'] . \')\' : %year%);',
					'class' => 'windowbg',
				),
				'sort' => array(
					'default' => 'event_date',
					'reverse' => 'event_date DESC',
				),
			),
			'check' => array(
				'header' => array(
					'value' => '<input type="checkbox" onclick="invertAll(this, this.form);" class="check" />',
				),
				'data' => array(
					'sprintf' => array(
						'format' => '<input type="checkbox" name="holiday[%1$d]" class="check" />',
						'params' => array(
							'id_holiday' => false,
						),
					),
					'style' => 'text-align: center',
				),
			),
		),
		'form' => array(
			'href' => $scripturl . '?action=admin;area=managecalendar;sa=holidays',
		),
		'additional_rows' => array(
			array(
				'position' => 'below_table_data',
				'value' => '<input type="submit" name="delete" value="' . $txt['quickmod_delete_selected'] . '" />',
				'class' => 'titlebg',
				'style' => 'text-align: right;',
			),
		),
	);

	require_once($sourcedir . '/Subs-List.php');
	createList($listOptions);

	//loadTemplate('ManageCalendar');
	$context['page_title'] = $txt['manage_holidays'];

	// Since the list is the only thing to show, use the default list template.
	$context['default_list'] = 'holiday_list';
	$context['sub_template'] = 'show_list';
}

// This function is used for adding/editing a specific holiday
function EditHoliday()
{
	global $txt, $context, $db_prefix, $scripturl, $smfFunc;

	loadTemplate('ManageCalendar');

	$context['is_new'] = !isset($_REQUEST['holiday']);
	$context['page_title'] = $context['is_new'] ? $txt['holidays_add'] : $txt['holidays_edit'];
	$context['sub_template'] = 'edit_holiday';

	// Submitting?
	if (isset($_POST['sc']) && (isset($_REQUEST['delete']) || $_REQUEST['title'] != ''))
	{
		checkSession();

		if (isset($_REQUEST['delete']))
			$smfFunc['db_query']('', "
				DELETE FROM {$db_prefix}calendar_holidays
				WHERE id_holiday = $_REQUEST[holiday]", __FILE__, __LINE__);
		else
		{
			$date = strftime($_REQUEST['year'] <= 4 ? '0004-%m-%d' : '%Y-%m-%d', mktime(0, 0, 0, $_REQUEST['month'], $_REQUEST['day'], $_REQUEST['year']));
			if (isset($_REQUEST['edit']))
				$smfFunc['db_query']('', "
					UPDATE {$db_prefix}calendar_holidays
					SET event_date = '$date', title = '$_REQUEST[title]'
					WHERE id_holiday = $_REQUEST[holiday]", __FILE__, __LINE__);
			else
				$smfFunc['db_query']('', "
					INSERT INTO {$db_prefix}calendar_holidays
						(event_date, title)
					VALUES
						('$date', SUBSTRING('$_REQUEST[title]', 1, 48))", __FILE__, __LINE__);
		}

		updateSettings(array(
			'calendar_updated' => time(),
		));

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
		$request = $smfFunc['db_query']('', "
			SELECT id_holiday, YEAR(event_date) AS year, MONTH(event_date) AS month, DAYOFMONTH(event_date) AS day, title
			FROM {$db_prefix}calendar_holidays
			WHERE id_holiday = $_REQUEST[holiday]
			LIMIT 1", __FILE__, __LINE__);
		while ($row = $smfFunc['db_fetch_assoc']($request))
			$context['holiday'] = array(
				'id' => $row['id_holiday'],
				'day' => $row['day'],
				'month' => $row['month'],
				'year' => $row['year'] <= 4 ? 0 : $row['year'],
				'title' => $row['title']
			);
		$smfFunc['db_free_result']($request);
	}

	// Last day for the drop down?
	$context['holiday']['last_day'] = (int) strftime('%d', mktime(0, 0, 0, $context['holiday']['month'] == 12 ? 1 : $context['holiday']['month'] + 1, 0, $context['holiday']['month'] == 12 ? $context['holiday']['year'] + 1 : $context['holiday']['year']));
}

function ModifyCalendarSettings($return_config = false)
{
	global $modSettings, $context, $settings, $db_prefix, $txt, $boarddir, $sourcedir, $scripturl, $smfFunc;

	// Load the boards list.
	$boards = array('');
	$request = $smfFunc['db_query']('', "
		SELECT b.id_board, b.name AS board_name, c.name AS cat_name
		FROM {$db_prefix}boards AS b
			LEFT JOIN {$db_prefix}categories AS c ON (c.id_cat = b.id_cat)", __FILE__, __LINE__);
	while ($row = $smfFunc['db_fetch_assoc']($request))
		$boards[$row['id_board']] = $row['cat_name'] . ' - ' . $row['board_name'];
	$smfFunc['db_free_result']($request);

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

	if ($return_config)
		return $config_vars;

	// Get the settings template fired up.
	require_once($sourcedir .'/ManageServer.php');

	// Some important context stuff
	$context['page_title'] = $txt['calendar_settings'];
	$context['sub_template'] = 'modify_settings';
	$context['sub_template'] = 'show_settings';

	// Get the final touches in place.
	$context['post_url'] = $scripturl . '?action=admin;area=managecalendar;save;sa=settings';
	$context['settings_title'] = $txt['calendar_settings'];

	// Saving the settings?
	if (isset($_GET['save']))
	{
		saveDBSettings($config_vars);

		// Update the stats incase.
		updateSettings(array(
			'calendar_updated' => time(),
		));

		redirectexit('action=admin;area=managecalendar;sa=settings');
	}

	// Prepare the settings...
	prepareDBSettingContext($config_vars);
}

?>