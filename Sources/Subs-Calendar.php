<?php
/**********************************************************************************
* Subs-Calendar.php                                                               *
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

/*	!!!	

*/

// Retrieve all events for the given days, independently of the users offset.
function cache_getOffsetIndependentEvents($days_to_index)
{
	global $sourcedir;

	require_once($sourcedir . '/Calendar.php');

	$low_date = strftime('%Y-%m-%d', forum_time(false) - 24 * 3600);
	$high_date = strftime('%Y-%m-%d', forum_time(false) + $days_to_index * 24 * 3600);

	return array(
		'data' => array(
			'holidays' => calendarHolidayArray($low_date, $high_date),
			'birthdays' => calendarBirthdayArray($low_date, $high_date),
			'events' => calendarEventArray($low_date, $high_date, false),
		),
		'refresh_eval' => 'return \'' . strftime('%Y%m%d', forum_time(false)) . '\' != strftime(\'%Y%m%d\', forum_time(false)) || (!empty($modSettings[\'calendar_updated\']) && ' . time() . ' < $modSettings[\'calendar_updated\']);',
		'expires' => time() + 3600,
	);
}

// Called from the BoardIndex to display the current day's events on the board index.
function cache_getRecentEvents($eventOptions)
{
	global $modSettings, $context, $user_info, $scripturl;

	// Make sure at least one of the options is enabled.
//	if (empty($eventOptions['include_holidays']) && empty($eventOptions['include_birthdays']) && empty($eventOptions['include_events']))
//		return false;

	// With the 'static' cached data we can calculate the user-specific data.
	$cached_data = cache_quick_get('calendar_index', 'Subs-Calendar.php', 'cache_getOffsetIndependentEvents', array($eventOptions['num_days_shown']));

	// No events, birthdays, or holidays... don't show anything.  Simple.
//	if (empty($cached_data['holidays']) && empty($cached_data['birthdays']) && empty($cached_data['events']))
//		return false;

	$return_data = array(
		'calendar_holidays' => array(),
		'calendar_birthdays' => array(),
		'calendar_events' => array(),
	);

	// Set the event span to be shown in seconds.
	$days_for_index = $eventOptions['num_days_shown'] * 86400;

	// Get the current member time/date.
	$now = forum_time();

	// Holidays between now and now + days.
	for ($i = $now; $i < $now + $days_for_index; $i += 86400)
	{
		if (isset($cached_data['holidays'][strftime('%Y-%m-%d', $i)]))
			$return_data['calendar_holidays'] = array_merge($return_data['calendar_holidays'], $cached_data['holidays'][strftime('%Y-%m-%d', $i)]);
	}

	// Happy Birthday, guys and gals!
	for ($i = $now; $i < $now + $days_for_index; $i += 86400)
		if (isset($cached_data['birthdays'][strftime('%Y-%m-%d', $i)]))
		{
			foreach ($cached_data['birthdays'][strftime('%Y-%m-%d', $i)] as $index => $dummy)
				$cached_data['birthdays'][strftime('%Y-%m-%d', $i)][$index]['is_today'] = strftime('%Y-%m-%d', $i) == strftime('%Y-%m-%d', forum_time());
			$return_data['calendar_birthdays'] = array_merge($return_data['calendar_birthdays'], $cached_data['birthdays'][strftime('%Y-%m-%d', $i)]);
		}

	$duplicates = array();
	for ($i = $now; $i < $now + $days_for_index; $i += 86400)
	{
		// Determine the date of the current loop step.
		$loop_date = strftime('%Y-%m-%d', $i);

		// No events today? Check the next day.
		if (empty($cached_data['events'][$loop_date]))
			continue;

		// Loop through all events to add a few last-minute values.
		foreach ($cached_data['events'][$loop_date] as $ev => $event)
		{
			// Create a shortcut variable for easier access.
			$this_event = &$cached_data['events'][$loop_date][$ev];

			// Skip duplicates.
			if (isset($duplicates[$this_event['topic'] . $this_event['title']]))
			{
				unset($cached_data['events'][$loop_date][$ev]);
				continue;
			}
			else
				$duplicates[$this_event['topic'] . $this_event['title']] = true;

			// Might be set to true afterwards, depending on the permissions.
			$this_event['can_edit'] = false;
			$this_event['is_today'] = strftime('%Y-%m-%d', forum_time()) === $loop_date;
			$this_event['date'] = $loop_date;
		}

		if (!empty($cached_data['events'][$loop_date]))
			$return_data['calendar_events'] = array_merge($return_data['calendar_events'], $cached_data['events'][$loop_date]);
	}

	for ($i = 0, $n = count($return_data['calendar_birthdays']); $i < $n; $i++)
		$return_data['calendar_birthdays'][$i]['is_last'] = !isset($return_data['calendar_birthdays'][$i + 1]);
	for ($i = 0, $n = count($return_data['calendar_events']); $i < $n; $i++)
		$return_data['calendar_events'][$i]['is_last'] = !isset($return_data['calendar_events'][$i + 1]);

	return array(
		'data' => $return_data,
		'expires' => time() + 3600,
		'refresh_eval' => 'return \'' . strftime('%Y%m%d', forum_time(false)) . '\' != strftime(\'%Y%m%d\', forum_time(false)) || (!empty($modSettings[\'calendar_updated\']) && ' . time() . ' < $modSettings[\'calendar_updated\']);',
		'post_retri_eval' => '
			foreach ($cache_block[\'data\'][\'calendar_events\'] as $k => $event)
			{
				// Remove events that the user may not see or wants to ignore.
				if ((array_intersect($user_info[\'groups\'], $event[\'allowed_groups\']) === 0 && !allowedTo(\'admin_forum\')) || in_array($event[\'id_board\'], $user_info[\'ignoreboards\']))
					unset($cache_block[\'data\'][\'calendar_events\'][$k]);
				else
				{
					// Whether the event can be edited depends on the permissions.
					$cache_block[\'data\'][\'calendar_events\'][$k][\'can_edit\'] = allowedTo(\'calendar_edit_any\') || ($event[\'poster\'] == $user_info[\'id\'] && allowedTo(\'calendar_edit_own\'));

					// The added session code makes this URL not cachable.
					$cache_block[\'data\'][\'calendar_events\'][$k][\'modify_href\'] = $scripturl . \'?action=\' . ($this_event[\'topic\'] == 0 ? \'calendar;sa=post;\' : \'post;msg=\' . $this_event[\'msg\'] . \';topic=\' . $this_event[\'topic\'] . \'.0;calendar;\') . \'eventid=\' . $this_event[\'id\'] . \';sesc=\' . $GLOBALS[\'sc\'];
				}
			}
			
			if (empty($params[\'include_holidays\']))
				$return_data[\'calendar_holidays\'] = array();
			if (empty($params[\'include_birthdays\']))
				$return_data[\'calendar_birthdays\'] = array();
			if (empty($params[\'include_events\']))
				$return_data[\'calendar_events\'] = array();
				
			$cache_block[\'data\'][\'show_calendar\'] = !empty($cache_block[\'data\'][\'calendar_holidays\']) || !empty($cache_block[\'data\'][\'calendar_birthdays\']) || !empty($cache_block[\'data\'][\'calendar_events\']);',
	);
}
				
?>