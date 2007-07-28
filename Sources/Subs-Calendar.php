<?php
/**********************************************************************************
* Subs-Calendar.php                                                               *
***********************************************************************************
* SMF: Simple Machines Forum                                                      *
* Open-Source Project Inspired by Zef Hemel (zef@zefhemel.com)                    *
* =============================================================================== *
* Software Version:           SMF 2.0 Beta 1                                       *
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

/*	This file contains several functions for retrieving and manipulating
	calendar events, birthdays and holidays.

	array getBirthdayRange(string earliest_date, string latest_date)
		- finds all the birthdays in the specified range of days.
		- earliest_date and latest_date are inclusive, and should both be in
		  the YYYY-MM-DD format.
		- works with birthdays set for no year, or any other year, and
		  respects month and year boundaries.
		- returns an array of days, each of which an array of birthday
		  information for the context.

	array getEventRange(string earliest_date, string latest_date,
			bool use_permissions = true)
		- finds all the posted calendar events within a date range.
		- both the earliest_date and latest_date should be in the standard
		  YYYY-MM-DD format.
		- censors the posted event titles.
		- uses the current user's permissions if use_permissions is true,
		  otherwise it does nothing "permission specific".
		- returns an array of contextual information if use_permissions is
		  true, and an array of the data needed to build that otherwise.

	array getHolidayRange(string earliest_date, string latest_date)
		- finds all the applicable holidays for the specified date range.
		- earliest_date and latest_date should be YYYY-MM-DD.
		- returns an array of days, which are all arrays of holiday names.

	void canLinkEvent()
		- checks if the current user can link the current topic to the
		  calendar, permissions et al.
		- this requires the calendar_post permission, a forum moderator, or a
		  topic starter.
		- expects the $topic and $board variables to be set.
		- if the user doesn't have proper permissions, an error will be shown.

	array getTodayInfo()
		- returns an array with the current date, day, month, and year.
		- takes the users time offset into account.
	
	array getCalendarGrid(int month, int year, array calendarOptions)
		- returns an array containing all the information needed to show a
		  calendar grid for the given month.
		- also provides information (link, month, year) about the previous and
		  next month.

	array cache_getOffsetIndependentEvents(int days_to_index)
		- cache callback function used to retrieve the birthdays, holidays, and
		  events between now and now + days_to_index.
		- widens the search range by an extra 24 hours to support time offset
		  shifts.
		- used by the cache_getRecentEvents function to get the information
		  needed to calculate the events taking the users time offset into
		  account.

	array cache_getRecentEvents(array eventOptions)
		- cache callback function used to retrieve the upcoming birthdays,
		  holidays, and events within the given period, taking into account
		  the users time offset.
		- used by the board index and SSI to show the upcoming events.

	void validateEventPost()
		- checks if the calendar post was valid.

	int getEventPoster(int event_id)
		- gets the member_id of an event identified by event_id.
		- returns false if the event was not found.

	void insertEvent(array eventOptions)
		- inserts the passed event information into the calendar table.
		- allows to either set a time span (in days) or an end_date.
		- does not check any permissions of any sort.

	void modifyEvent(int event_id, array eventOptions)
		- modifies an event.
		- allows to either set a time span (in days) or an end_date.
		- does not check any permissions of any sort.

	void removeEvent(int event_id)
		- removes an event.
		- does no permission checks.
*/

// Get all birthdays within the given time range.
function getBirthdayRange($low_date, $high_date)
{
	global $db_prefix, $scripturl, $modSettings, $smfFunc;

	// Birthdays people set without specifying a year (no age, see?) are the easiest ;).
	if (substr($low_date, 0, 4) != substr($high_date, 0, 4))
		$allyear_part = "birthdate BETWEEN '0004" . substr($low_date, 4) . "' AND '0004-12-31'
			OR birthdate BETWEEN '0004-01-01' AND '0004" . substr($high_date, 4) . "'";
	else
		$allyear_part = "birthdate BETWEEN '0004" . substr($low_date, 4) . "' AND '0004" . substr($high_date, 4) . "'";

	// We need to search for any birthday in this range, and whatever year that birthday is on.
	$year_low = (int) substr($low_date, 0, 4);
	$year_high = (int) substr($high_date, 0, 4);

	// Collect all of the birthdays for this month.  I know, it's a painful query.
	$result = $smfFunc['db_query']('birthday_array', "
		SELECT id_member, real_name, YEAR(birthdate) AS birthYear, birthdate
		FROM {$db_prefix}members
		WHERE YEAR(birthdate) != '0001'
			AND	($allyear_part
				OR DATE_FORMAT(birthdate, '{$year_low}-%m-%d') BETWEEN '$low_date' AND '$high_date'" . ($year_low == $year_high ? '' : "
				OR DATE_FORMAT(birthdate, '{$year_high}-%m-%d') BETWEEN '$low_date' AND '$high_date'") . ")
			AND is_activated = 1", __FILE__, __LINE__);
	$bday = array();
	while ($row = $smfFunc['db_fetch_assoc']($result))
	{
		if ($year_low != $year_high)
			$age_year = substr($row['birthdate'], 5) < substr($high_date, 5) ? $year_high : $year_low;
		else
			$age_year = $year_low;

		$bday[$age_year . substr($row['birthdate'], 4)][] = array(
			'id' => $row['id_member'],
			'name' => $row['real_name'],
			'age' => $row['birthYear'] > 4 && $row['birthYear'] <= $age_year ? $age_year - $row['birthYear'] : null,
			'is_last' => false
		);
	}
	$smfFunc['db_free_result']($result);

	// Set is_last, so the themes know when to stop placing separators.
	foreach ($bday as $mday => $array)
		$bday[$mday][count($array) - 1]['is_last'] = true;

	return $bday;
}

// Get all events within the given time range.
function getEventRange($low_date, $high_date, $use_permissions = true)
{
	global $db_prefix, $scripturl, $modSettings, $user_info, $sc, $smfFunc;

	$low_date_time = sscanf($low_date, '%04d-%02d-%02d');
	$low_date_time = mktime(0, 0, 0, $low_date_time[1], $low_date_time[2], $low_date_time[0]);
	$high_date_time = sscanf($high_date, '%04d-%02d-%02d');
	$high_date_time = mktime(0, 0, 0, $high_date_time[1], $high_date_time[2], $high_date_time[0]);

	// Find all the calendar info...
	$result = $smfFunc['db_query']('', "
		SELECT
			cal.id_event, cal.start_date, cal.end_date, cal.title, cal.id_member, cal.id_topic,
			cal.id_board, b.member_groups, t.id_first_msg, t.approved, b.id_board
		FROM {$db_prefix}calendar AS cal
			LEFT JOIN {$db_prefix}boards AS b ON (b.id_board = cal.id_board)
			LEFT JOIN {$db_prefix}topics AS t ON (t.id_topic = cal.id_topic)
		WHERE cal.start_date <= '$high_date'
			AND cal.end_date >= '$low_date'" . ($use_permissions ? "
			AND (cal.id_board = 0 OR $user_info[query_wanna_see_board])" : ''), __FILE__, __LINE__);
	$events = array();
	while ($row = $smfFunc['db_fetch_assoc']($result))
	{
		// If the attached topic is not approved then for the moment pretend it doesn't exist
		//!!! This should be fixed to show them all and then sort by approval state later?
		if (!empty($row['id_first_msg']) && !$row['approved'])
			continue;

		// Force a censor of the title - as often these are used by others.
		censorText($row['title'], $use_permissions ? false : true);

		$start_date = sscanf($row['start_date'], '%04d-%02d-%02d');
		$start_date = max(mktime(0, 0, 0, $start_date[1], $start_date[2], $start_date[0]), $low_date_time);
		$end_date = sscanf($row['end_date'], '%04d-%02d-%02d');
		$end_date = min(mktime(0, 0, 0, $end_date[1], $end_date[2], $end_date[0]), $high_date_time);

		$lastDate = '';
		for ($date = $start_date; $date <= $end_date; $date += 86400)
		{
			// Attempt to avoid DST problems.
			//!!! Resolve this properly at some point.
			if (strftime('%Y-%m-%d', $date) == $lastDate)
				$date += 3601;
			$lastDate = strftime('%Y-%m-%d', $date);

			// If we're using permissions (calendar pages?) then just ouput normal contextual style information.
			if ($use_permissions)
				$events[strftime('%Y-%m-%d', $date)][] = array(
					'id' => $row['id_event'],
					'title' => $row['title'],
					'can_edit' => allowedTo('calendar_edit_any') || ($row['id_member'] == $user_info['id'] && allowedTo('calendar_edit_own')),
					'modify_href' => $scripturl . '?action=' . ($row['id_board'] == 0 ? 'calendar;sa=post;' : 'post;msg=' . $row['id_first_msg'] . ';topic=' . $row['id_topic'] . '.0;calendar;') . 'eventid=' . $row['id_event'] . ';sesc=' . $sc,
					'href' => $row['id_board'] == 0 ? '' : $scripturl . '?topic=' . $row['id_topic'] . '.0',
					'link' => $row['id_board'] == 0 ? $row['title'] : '<a href="' . $scripturl . '?topic=' . $row['id_topic'] . '.0">' . $row['title'] . '</a>',
					'start_date' => $row['start_date'],
					'end_date' => $row['end_date'],
					'is_last' => false,
					'id_board' => $row['id_board'],
				);
			// Otherwise, this is going to be cached and the VIEWER'S permissions should apply... just put together some info.
			else
				$events[strftime('%Y-%m-%d', $date)][] = array(
					'id' => $row['id_event'],
					'title' => $row['title'],
					'topic' => $row['id_topic'],
					'msg' => $row['id_first_msg'],
					'poster' => $row['id_member'],
					'start_date' => $row['start_date'],
					'end_date' => $row['end_date'],
					'is_last' => false,
					'allowed_groups' => explode(',', $row['member_groups']),
					'id_board' => $row['id_board'],
					'href' => $row['id_topic'] == 0 ? '' : $scripturl . '?topic=' . $row['id_topic'] . '.0',
				);
		}
	}
	$smfFunc['db_free_result']($result);

	// If we're doing normal contextual data, go through and make things clear to the templates ;).
	if ($use_permissions)
	{
		foreach ($events as $mday => $array)
			$events[$mday][count($array) - 1]['is_last'] = true;
	}

	return $events;
}

// Get all holidays within the given time range.
function getHolidayRange($low_date, $high_date)
{
	global $db_prefix, $smfFunc;

	// Get the lowest and highest dates for "all years".
	if (substr($low_date, 0, 4) != substr($high_date, 0, 4))
		$allyear_part = "event_date BETWEEN '0004" . substr($low_date, 4) . "' AND '0004-12-31'
			OR event_date BETWEEN '0004-01-01' AND '0004" . substr($high_date, 4) . "'";
	else
		$allyear_part = "event_date BETWEEN '0004" . substr($low_date, 4) . "' AND '0004" . substr($high_date, 4) . "'";

	// Find some holidays... ;).
	$result = $smfFunc['db_query']('', "
		SELECT event_date, YEAR(event_date) AS year, title
		FROM {$db_prefix}calendar_holidays
		WHERE event_date BETWEEN '$low_date' AND '$high_date'
			OR $allyear_part", __FILE__, __LINE__);
	$holidays = array();
	while ($row = $smfFunc['db_fetch_assoc']($result))
	{
		if (substr($low_date, 0, 4) != substr($high_date, 0, 4))
			$event_year = substr($row['event_date'], 5) < substr($high_date, 5) ? substr($high_date, 0, 4) : substr($low_date, 0, 4);
		else
			$event_year = substr($low_date, 0, 4);

		$holidays[$event_year . substr($row['event_date'], 4)][] = $row['title'];
	}
	$smfFunc['db_free_result']($result);

	return $holidays;
}

// Does permission checks to see if an event can be linked to a board/topic.
function canLinkEvent()
{
	global $db_prefix, $user_info, $topic, $board, $smfFunc;

	// If you can't post, you can't link.
	isAllowedTo('calendar_post');

	// No board?  No topic?!?
	if (!isset($board))
		fatal_lang_error('missing_board_id', false);
	if (!isset($topic))
		fatal_lang_error('missing_topic_id', false);

	// Administrator, Moderator, or owner.  Period.
	if (!allowedTo('admin_forum') && !allowedTo('moderate_board'))
	{
		// Not admin or a moderator of this board. You better be the owner - or else.
		$result = $smfFunc['db_query']('', "
			SELECT id_member_started
			FROM {$db_prefix}topics
			WHERE id_topic = $topic
			LIMIT 1", __FILE__, __LINE__);
		if ($row = $smfFunc['db_fetch_assoc']($result))
		{
			// Not the owner of the topic.
			if ($row['id_member_started'] != $user_info['id'])
				fatal_lang_error('not_your_topic', 'user');
		}
		// Topic/Board doesn't exist.....
		else
			fatal_lang_error('calendar_no_topic', 'general');
		$smfFunc['db_free_result']($result);
	}
}

// Returns date information about 'today' relative to the users time offset.
function getTodayInfo()
{
	return array(
		'day' => (int) strftime('%d', forum_time()),
		'month' => (int) strftime('%m', forum_time()),
		'year' => (int) strftime('%Y', forum_time()),
		'date' => strftime('%Y-%m-%d', forum_time()),
	);
}

// Returns the information needed to show a calendar grid for the given month.
function getCalendarGrid($month, $year, $calendarOptions)
{
	global $scripturl, $modSettings;

	// Eventually this is what we'll be returning.
	$calendarGrid = array(
		'week_days' => array(),
		'weeks' => array(),
		'previous_calendar' => array(
			'year' => $month == 1 ? $year - 1 : $year,
			'month' => $month == 1 ? 12 : $month - 1,
			'disabled' => $modSettings['cal_minyear'] > ($month == 1 ? $year - 1 : $year),
		),
		'next_calendar' => array(
			'year' => $month == 12 ? $year + 1 : $year,
			'month' => $month == 12 ? 1 : $month + 1,
			'disabled' => $modSettings['cal_maxyear'] < ($month == 12 ? $year + 1 : $year),
		),
	);

	// Get todays date.
	$today = getTodayInfo();

	// Get information about this month.
	$month_info = array(
		'first_day' => array(
			'day_of_week' => (int) strftime('%w', mktime(0, 0, 0, $month, 1, $year)),
			'week_num' => (int) strftime('%U', mktime(0, 0, 0, $month, 1, $year)),
			'date' => strftime('%Y-%m-%d', mktime(0, 0, 0, $month, 1, $year)),
		),
		'last_day' => array(
			'day_of_month' => (int) strftime('%d', mktime(0, 0, 0, $month == 12 ? 1 : $month + 1, 0, $month == 12 ? $year + 1 : $year)),
			'date' => strftime('%Y-%m-%d', mktime(0, 0, 0, $month == 12 ? 1 : $month + 1, 0, $month == 12 ? $year + 1 : $year)),
		),
		'first_day_of_year' => (int) strftime('%w', mktime(0, 0, 0, 1, 1, $year)),
	);

	// The number of days the first row is shifted to the right for the starting day.
	$nShift = $month_info['first_day']['day_of_week'];

	$calendarOptions['start_day'] = empty($calendarOptions['start_day']) ? 0 : (int) $calendarOptions['start_day'];

	// Starting any day other than Sunday means a shift...
	if (!empty($calendarOptions['start_day']))
	{
		$nShift -= $calendarOptions['start_day'];
		if ($nShift < 0)
			$nShift = 7 + $nShift;
	}

	// Number of rows required to fit the month.
	$nRows = floor(($month_info['last_day']['day_of_month'] + $nShift) / 7);
	if (($month_info['last_day']['day_of_month'] + $nShift) % 7)
		$nRows++;

	// Fetch the arrays for birthdays, posted events, and holidays.
	$bday = $calendarOptions['show_birthdays'] ? getBirthdayRange($month_info['first_day']['date'], $month_info['last_day']['date']) : array();
	$events = $calendarOptions['show_events'] ? getEventRange($month_info['first_day']['date'], $month_info['last_day']['date']) : array();
	$holidays = $calendarOptions['show_holidays'] ? getHolidayRange($month_info['first_day']['date'], $month_info['last_day']['date']) : array();

	// Days of the week taking into consideration that they may want it to start on any day.
	$count = $calendarOptions['start_day'];
	for ($i = 0; $i < 7; $i++)
	{
		$calendarGrid['week_days'][] = $count;
		$count++;
		if ($count == 7)
			$count = 0;
	}

	// An adjustment value to apply to all calculated week numbers.
	if (!empty($calendarOptions['show_week_num']))
	{
		// If the first day of the year is a Sunday, then there is no
		// adjustment to be made. However, if the first day of the year is not
		// a Sunday, then there is a partial week at the start of the year
		// that needs to be accounted for.
		if ($calendarOptions['start_day'] === 0)
			$nWeekAdjust = $month_info['first_day_of_year'] === 0 ? 0 : 1;
		// If we are viewing the weeks, with a starting date other than Sunday,
		// then things get complicated! Basically, as PHP is calculating the
		// weeks with a Sunday starting date, we need to take this into account
		// and offset the whole year dependant on whether the first day in the
		// year is above or below our starting date. Note that we offset by
		// two, as some of this will get undone quite quickly by the statement
		// below.
		else
			$nWeekAdjust = $calendarOptions['start_day'] > $month_info['first_day_of_year'] && $month_info['first_day_of_year'] !== 0 ? 2 : 1;

		// If our week starts on a day greater than the day the month starts
		// on, then our week numbers will be one too high. So we need to
		// reduce it by one - all these thoughts of offsets makes my head
		// hurt...
		if ($month_info['first_day']['day_of_week'] < $calendarOptions['start_day'])
			$nWeekAdjust--;
	}
	else
		$nWeekAdjust = 0;

	// Iterate through each week.
	$calendarGrid['weeks'] = array();
	for ($nRow = 0; $nRow < $nRows; $nRow++)
	{
		// Start off the week - and don't let it go above 52, since that's the number of weeks in a year.
		$calendarGrid['weeks'][$nRow] = array(
			'days' => array(),
			'number' => $month_info['first_day']['week_num'] + $nRow + $nWeekAdjust
		);
		// Handle the dreaded "week 53", it can happen, but only once in a blue moon ;)
		if ($calendarGrid['weeks'][$nRow]['number'] == 53 && $nShift != 4)
			$calendarGrid['weeks'][$nRow]['number'] = 1;

		// And figure out all the days.
		for ($nCol = 0; $nCol < 7; $nCol++)
		{
			$nDay = ($nRow * 7) + $nCol - $nShift + 1;

			if ($nDay < 1 || $nDay > $month_info['last_day']['day_of_month'])
				$nDay = 0;

			$date = sprintf('%04d-%02d-%02d', $year, $month, $nDay);

			$calendarGrid['weeks'][$nRow]['days'][$nCol] = array(
				'day' => $nDay,
				'date' => $date,
				'is_today' => $date == $today['date'],
				'is_first_day' => !empty($calendarOptions['show_week_num']) && (($month_info['first_day']['day_of_week'] + $nDay - 1) % 7 == $calendarOptions['start_day']),
				'holidays' => !empty($holidays[$date]) ? $holidays[$date] : array(),
				'events' => !empty($events[$date]) ? $events[$date] : array(),
				'birthdays' => !empty($bday[$date]) ? $bday[$date] : array()
			);
		}
	}

	// Set the previous and the next month's links.
	$calendarGrid['previous_calendar']['href'] = $scripturl . '?action=calendar;year=' . $calendarGrid['previous_calendar']['year'] . ';month=' . $calendarGrid['previous_calendar']['month'];
	$calendarGrid['next_calendar']['href'] = $scripturl . '?action=calendar;year=' . $calendarGrid['next_calendar']['year'] . ';month=' . $calendarGrid['next_calendar']['month'];

	return $calendarGrid;
}

// Retrieve all events for the given days, independently of the users offset.
function cache_getOffsetIndependentEvents($days_to_index)
{
	global $sourcedir;

	require_once($sourcedir . '/Calendar.php');

	$low_date = strftime('%Y-%m-%d', forum_time(false) - 24 * 3600);
	$high_date = strftime('%Y-%m-%d', forum_time(false) + $days_to_index * 24 * 3600);

	return array(
		'data' => array(
			'holidays' => getHolidayRange($low_date, $high_date),
			'birthdays' => getBirthdayRange($low_date, $high_date),
			'events' => getEventRange($low_date, $high_date, false),
		),
		'refresh_eval' => 'return \'' . strftime('%Y%m%d', forum_time(false)) . '\' != strftime(\'%Y%m%d\', forum_time(false)) || (!empty($modSettings[\'calendar_updated\']) && ' . time() . ' < $modSettings[\'calendar_updated\']);',
		'expires' => time() + 3600,
	);
}

// Called from the BoardIndex to display the current day's events on the board index.
function cache_getRecentEvents($eventOptions)
{
	global $modSettings, $user_info, $scripturl;

	// With the 'static' cached data we can calculate the user-specific data.
	$cached_data = cache_quick_get('calendar_index', 'Subs-Calendar.php', 'cache_getOffsetIndependentEvents', array($eventOptions['num_days_shown']));

	// Get the information about today (from user perspective).
	$today = getTodayInfo();

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
	{
		$loop_date = strftime('%Y-%m-%d', $i);
		if (isset($cached_data['birthdays'][$loop_date]))
		{
			foreach ($cached_data['birthdays'][$loop_date] as $index => $dummy)
				$cached_data['birthdays'][strftime('%Y-%m-%d', $i)][$index]['is_today'] = $loop_date === $today['date'];
			$return_data['calendar_birthdays'] = array_merge($return_data['calendar_birthdays'], $cached_data['birthdays'][$loop_date]);
		}
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
			$this_event['is_today'] = $loop_date === $today['date'];
			$this_event['date'] = $loop_date;
		}

		if (!empty($cached_data['events'][$loop_date]))
			$return_data['calendar_events'] = array_merge($return_data['calendar_events'], $cached_data['events'][$loop_date]);
	}

	// Mark the last item so that a list seperator can be used in the template.
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
				if ((array_intersect($GLOBALS[\'user_info\'][\'groups\'], $event[\'allowed_groups\']) === 0 && !allowedTo(\'admin_forum\')) || in_array($event[\'id_board\'], $GLOBALS[\'user_info\'][\'ignoreboards\']))
					unset($cache_block[\'data\'][\'calendar_events\'][$k]);
				else
				{
					// Whether the event can be edited depends on the permissions.
					$cache_block[\'data\'][\'calendar_events\'][$k][\'can_edit\'] = allowedTo(\'calendar_edit_any\') || ($event[\'poster\'] == $GLOBALS[\'user_info\'][\'id\'] && allowedTo(\'calendar_edit_own\'));

					// The added session code makes this URL not cachable.
					$cache_block[\'data\'][\'calendar_events\'][$k][\'modify_href\'] = $GLOBALS[\'scripturl\'] . \'?action=\' . ($event[\'topic\'] == 0 ? \'calendar;sa=post;\' : \'post;msg=\' . $event[\'msg\'] . \';topic=\' . $event[\'topic\'] . \'.0;calendar;\') . \'eventid=\' . $event[\'id\'] . \';sesc=\' . $GLOBALS[\'sc\'];
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

// Makes sure the calendar post is valid.
function validateEventPost()
{
	global $modSettings, $txt, $sourcedir, $smfFunc;

	if (!isset($_POST['deleteevent']))
	{
		// No month?  No year?
		if (!isset($_POST['month']))
			fatal_lang_error('event_month_missing', false);
		if (!isset($_POST['year']))
			fatal_lang_error('event_year_missing', false);

		// Check the month and year...
		if ($_POST['month'] < 1 || $_POST['month'] > 12)
			fatal_lang_error('invalid_month', false);
		if ($_POST['year'] < $modSettings['cal_minyear'] || $_POST['year'] > $modSettings['cal_maxyear'])
			fatal_lang_error('invalid_year', false);
	}

	// Make sure they're allowed to post...
	isAllowedTo('calendar_post');

	if (isset($_POST['span']))
	{
		// Make sure it's turned on and not some fool trying to trick it.
		if (empty($modSettings['cal_allowspan']))
			fatal_lang_error('no_span', false);
		if ($_POST['span'] < 1 || $_POST['span'] > $modSettings['cal_maxspan'])
			fatal_lang_error('invalid_days_numb', false);
	}

	// There is no need to validate the following values if we are just deleting the event.
	if (!isset($_POST['deleteevent']))
	{
		// No day?
		if (!isset($_POST['day']))
			fatal_lang_error('event_day_missing', false);
		if (!isset($_POST['evtitle']) && !isset($_POST['subject']))
			fatal_lang_error('event_title_missing', false);
		elseif (!isset($_POST['evtitle']))
			$_POST['evtitle'] = $_POST['subject'];

		// Bad day?
		if (!checkdate($_POST['month'], $_POST['day'], $_POST['year']))
			fatal_lang_error('invalid_date', false);

		// No title?
		if ($smfFunc['htmltrim']($_POST['evtitle']) === '')
			fatal_lang_error('no_event_title', false);
		if ($smfFunc['strlen']($_POST['evtitle']) > 30)
			$_POST['evtitle'] = $smfFunc['substr']($_POST['evtitle'], 0, 30);
		$_POST['evtitle'] = str_replace(';', '', $_POST['evtitle']);
	}
}

// Get the event's poster.
function getEventPoster($event_id)
{
	global $smfFunc, $db_prefix;

	// A simple database query, how hard can that be?
	$request = $smfFunc['db_query']('', "
		SELECT id_member
		FROM {$db_prefix}calendar
		WHERE id_event = $event_id
		LIMIT 1", __FILE__, __LINE__);
	
	// No results, return false.
	if ($smfFunc['db_num_results'] === 0)
		return false;

	// Grab the results and return.
	list ($poster) = $smfFunc['db_fetch_row']($request);
	$smfFunc['db_free_result']($request);
	return $poster;
}

// Consolidating the various INSERT statements into this function.
function insertEvent(&$eventOptions)
{
	global $db_prefix, $modSettings, $smfFunc;

	// Add special chars to the title.
	$eventOptions['title'] = $smfFunc['db_escape_string']($smfFunc['htmlspecialchars']($smfFunc['db_unescape_string']($eventOptions['title']), ENT_QUOTES));

	// Add some sanity checking to the span.
	$eventOptions['span'] = isset($eventOptions['span']) && $eventOptions['span'] > 0 ? (int) $eventOptions['span'] : 0;

	// Make sure the start date is in ISO order.
	if (($num_results = sscanf($eventOptions['start_date'], '%d-%d-%d', $year, $month, $day)) !== 3)
		trigger_error('modifyEvent(): invalid start date format given', E_USER_ERROR);

	// Set the end date (if not yet given)
	if (!isset($eventOptions['end_date']))
		$eventOptions['end_date'] = strftime('%Y-%m-%d', mktime(0, 0, 0, $month, $day, $year) + $eventOptions['span'] * 86400);

	// If no topic and board are given, they are not linked to a topic.
	$eventOptions['board'] = isset($eventOptions['board']) ? (int) $eventOptions['board'] : 0;
	$eventOptions['topic'] = isset($eventOptions['topic']) ? (int) $eventOptions['topic'] : 0;

	// Insert the event!
	$smfFunc['db_query']('', "
		INSERT INTO {$db_prefix}calendar
			(id_board, id_topic, title, id_member, start_date, end_date)
		VALUES ($eventOptions[board], $eventOptions[topic], SUBSTRING('$eventOptions[title]', 1, 48), $eventOptions[member], '$eventOptions[start_date]', '$eventOptions[end_date]')", __FILE__, __LINE__);

	// Store the just inserted id_event for future reference.
	$eventOptions['id'] = $smfFunc['db_insert_id'];

	// Update the settings to show something calendarish was updated.
	updateSettings(array(
		'calendar_updated' => time(),
	));
}

function modifyEvent($event_id, &$eventOptions)
{
	global $smfFunc, $db_prefix;

	// Properly sanitize the title.
	$eventOptions['title'] = $smfFunc['db_escape_string']($smfFunc['htmlspecialchars']($smfFunc['db_unescape_string']($eventOptions['title']), ENT_QUOTES));

	// Scan the start date for validity and get its components.
	if (($num_results = sscanf($eventOptions['start_date'], '%d-%d-%d', $year, $month, $day)) !== 3)
		trigger_error('modifyEvent(): invalid start date format given', E_USER_ERROR);

	// Default span to 0 days.
	$eventOptions['span'] = isset($eventOptions['span']) ? (int) $eventOptions['span'] : 0;

	// Set the end date to the start date + span (if the end date wasn't already given).
	if (!isset($eventOptions['end_date']))
		$eventOptions['end_date'] = strftime('%Y-%m-%d', mktime(0, 0, 0, $month, $day, $year) + $eventOptions['span'] * 86400);

	$smfFunc['db_query']('', "
		UPDATE {$db_prefix}calendar
		SET 
			start_date = '$eventOptions[start_date]',
			end_date = '$eventOptions[end_date]', 
			title = SUBSTRING('$eventOptions[title]', 1, 48),
			id_board = " . (isset($eventOptions['board']) ? (int) $eventOptions['board'] : 'id_board') . ",
			id_topic = " . (isset($eventOptions['topic']) ? (int) $eventOptions['topic'] : 'id_board') . "
		WHERE id_event = $event_id", __FILE__, __LINE__);

	updateSettings(array(
		'calendar_updated' => time(),
	));
}

function removeEvent($event_id)
{
	global $smfFunc, $db_prefix;

	$smfFunc['db_query']('', "
		DELETE FROM {$db_prefix}calendar
		WHERE id_event = $_REQUEST[eventid]", __FILE__, __LINE__);

	updateSettings(array(
		'calendar_updated' => time(),
	));
}

function getEventProperties($event_id)
{
	global $smfFunc, $db_prefix;

	$request = $smfFunc['db_query']('', "
		SELECT
			c.id_event, c.id_board, c.id_topic, MONTH(c.start_date) AS month,
			DAYOFMONTH(c.start_date) AS day, YEAR(c.start_date) AS year,
			(TO_DAYS(c.end_date) - TO_DAYS(c.start_date)) AS span, c.id_member, c.title,
			t.id_first_msg, t.id_member_started
		FROM {$db_prefix}calendar AS c
			LEFT JOIN {$db_prefix}topics AS t ON (t.id_topic = c.id_topic)
		WHERE c.id_event = $event_id", __FILE__, __LINE__);

	// If nothing returned, we are in poo, poo.
	if ($smfFunc['db_num_rows']($request) === 0)
		return false;

	$row = $smfFunc['db_fetch_assoc']($request);
	$smfFunc['db_free_result']($request);

	$return_value = array(
		'boards' => array(),
		'board' => $row['id_board'],
		'new' => 0,
		'eventid' => $_REQUEST['eventid'],
		'year' => $row['year'],
		'month' => $row['month'],
		'day' => $row['day'],
		'title' => $row['title'],
		'span' => 1 + $row['span'],
		'member' => $row['id_member'],
		'topic' => array(
			'id' => $row['id_topic'],
			'member_started' => $row['id_member_started'],
			'first_msg' => $row['id_first_msg'],
		),
	);

	$return_value['last_day'] = (int) strftime('%d', mktime(0, 0, 0, $return_value['month'] == 12 ? 1 : $return_value['month'] + 1, 0, $return_value['month'] == 12 ? $return_value['year'] + 1 : $return_value['year']));

	return $return_value;
}

function list_getHolidays($start, $items_per_page, $sort)
{
	global $smfFunc, $db_prefix;

	$request = $smfFunc['db_query']('', "
		SELECT id_holiday, YEAR(event_date) AS year, MONTH(event_date) AS month, DAYOFMONTH(event_date) AS day, title
		FROM {$db_prefix}calendar_holidays
		ORDER BY $sort
		LIMIT $start, $items_per_page", __FILE__, __LINE__);
	$holidays = array();
	while ($row = $smfFunc['db_fetch_assoc']($request))
		$holidays[] = $row;
	$smfFunc['db_free_result']($request);

	return $holidays;
}

function list_getNumHolidays()
{
	global $smfFunc, $db_prefix;

	$request = $smfFunc['db_query']('', "
		SELECT COUNT(*)
		FROM {$db_prefix}calendar_holidays", __FILE__, __LINE__);
	list($num_items) = $smfFunc['db_fetch_row']($request);
	$smfFunc['db_free_result']($request);

	return $num_items;
}

function removeHolidays($holiday_ids)
{
	global $smfFunc, $db_prefix;

	$smfFunc['db_query']('', "
		DELETE FROM {$db_prefix}calendar_holidays
		WHERE id_holiday IN (" . implode(', ', $_REQUEST['holiday']) . ")", __FILE__, __LINE__);

	updateSettings(array(
		'calendar_updated' => time(),
	));
}

?>