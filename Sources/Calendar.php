<?php
/**********************************************************************************
* Calendar.php                                                                    *
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

/* Original module by Aaron O'Neil - aaron@mud-master.com                     *
******************************************************************************/
if (!defined('SMF'))
	die('Hacking attempt...');

/*	This file has only one real task... showing the calendar.  Posting is done
	in Post.php - this just has the following functions:

	void CalendarMain()
		- loads the specified month's events, holidays, and birthdays.
		- requires the calendar_view permission.
		- depends on the cal_enabled setting, and many of the other cal_
		  settings.
		- uses the calendar_start_day theme option. (Monday/Sunday)
		- uses the main sub template in the Calendar template.
		- goes to the month and year passed in 'month' and 'year' by
		  get or post.
		- accessed through ?action=calendar.

	array calendarBirthdayArray(string earliest_date, string latest_date)
		- finds all the birthdays in the specified range of days.
		- earliest_date and latest_date are inclusive, and should both be in
		  the YYYY-MM-DD format.
		- works with birthdays set for no year, or any other year, and
		  respects month and year boundaries.
		- returns an array of days, each of which an array of birthday
		  information for the context.

	array calendarEventArray(string earliest_date, string latest_date,
			bool use_permissions = true)
		- finds all the posted calendar events within a date range.
		- both the earliest_date and latest_date should be in the standard
		  YYYY-MM-DD format.
		- censors the posted event titles.
		- uses the current user's permissions if use_permissions is true,
		  otherwise it does nothing "permission specific".
		- returns an array of contextual information if use_permissions is
		  true, and an array of the data needed to build that otherwise.

	array calendarHolidayArray(string earliest_date, string latest_date)
		- finds all the applicable holidays for the specified date range.
		- earliest_date and latest_date should be YYYY-MM-DD.
		- returns an array of days, which are all arrays of holiday names.

	void calendarInsertEvent(int id_board, int id_topic, string title,
			int id_member, int month, int day, int year, int span)
		- inserts the passed event information into the calendar table.
		- recaches the calendar information after doing so.
		- expects the passed title not to have html characters.
		- handles spanned events by inserting them multiple times.
		- does not check any permissions of any sort.

	bool calendarCanLink()
		- checks if the current user can link the current topic to the
		  calendar, permissions et al.
		- this requires the calendar_post permission, a forum moderator, or a
		  topic starter.
		- expects the $topic and $board variables to be set.
		- returns true or false corresponding to whether they can or cannot
		  link this topic to the calendar.

	void CalendarPost()
		- processes posting/editing/deleting a calendar event.
		- calls Post() function if event is linked to a post.
		- calls calendarInsertEvent() to insert the event if not linked to post.
		- requires the calendar_post permission to use.
		- uses the event_post sub template in the Calendar template.
		- is accessed with ?action=calendar;sa=post.
*/

// Show the calendar.
function CalendarMain()
{
	global $txt, $context, $modSettings, $scripturl, $options;

	// If we are posting a new event defect to the posting function.
	if (isset($_GET['sa']) && $_GET['sa'] == 'post')
		return CalendarPost();

	// This is gonna be needed...
	loadTemplate('Calendar');

	// Permissions, permissions, permissions.
	isAllowedTo('calendar_view');

	// You can't do anything if the calendar is off.
	if (empty($modSettings['cal_enabled']))
		fatal_lang_error('calendar_off', false);

	// Set the page title to mention the calendar ;).
	$context['page_title'] = $context['forum_name'] . ': ' . $txt['calendar'];

	// Get the current day of month...
	$today = array(
		'day' => (int) strftime('%d', forum_time()),
		'month' => (int) strftime('%m', forum_time()),
		'year' => (int) strftime('%Y', forum_time()),
	);
	$today['date'] = sprintf('%04d-%02d-%02d', $today['year'], $today['month'], $today['day']);

	// If the month and year are not passed in, use today's date as a starting point.
	$curPage = array(
		'month' => isset($_REQUEST['month']) ? (int) $_REQUEST['month'] : $today['month'],
		'year' => isset($_REQUEST['year']) ? (int) $_REQUEST['year'] : $today['year']
	);

	// Make sure the year and month are in valid ranges.
	if ($curPage['month'] < 1 || $curPage['month'] > 12)
		fatal_lang_error('calendar1', false);
	if ($curPage['year'] < $modSettings['cal_minyear'] || $curPage['year'] > $modSettings['cal_maxyear'])
		fatal_lang_error('calendar2', false);

	// Get information about the first day of this month.
	$firstDayOfMonth = array(
		'dayOfWeek' => (int) strftime('%w', mktime(0, 0, 0, $curPage['month'], 1, $curPage['year'])),
		'weekNum' => (int) strftime('%U', mktime(0, 0, 0, $curPage['month'], 1, $curPage['year']))
	);

	// Find the last day of the month.
	$nLastDay = (int) strftime('%d', mktime(0, 0, 0, $curPage['month'] == 12 ? 1 : $curPage['month'] + 1, 0, $curPage['month'] == 12 ? $curPage['year'] + 1 : $curPage['year']));

	// The number of days the first row is shifted to the right for the starting day.
	$nShift = $firstDayOfMonth['dayOfWeek'];

	// Calendar start day- default Sunday.
	$nStartDay = !empty($options['calendar_start_day']) ? $options['calendar_start_day'] : 0;

	// Starting any day other than Sunday means a shift...
	if ($nStartDay)
	{
		$nShift -= $nStartDay;
		if ($nShift < 0)
			$nShift = 7 + $nShift;
	}

	// Number of rows required to fit the month.
	$nRows = floor(($nLastDay + $nShift) / 7);
	if (($nLastDay + $nShift) % 7)
		$nRows++;

	// Get the lowest and highest days of this month, in YYYY-MM-DD format. ($nLastDay is always 2 digits.)
	$low = $curPage['year'] . '-' . sprintf('%02d', $curPage['month']) . '-01';
	$high = $curPage['year'] . '-' . sprintf('%02d', $curPage['month']) . '-' . $nLastDay;

	// Fetch the arrays for birthdays, posted events, and holidays.
	$bday = $modSettings['cal_showbdays'] && $modSettings['cal_showbdays'] != 3 ? calendarBirthdayArray($low, $high) : array();
	$events = $modSettings['cal_showevents'] && $modSettings['cal_showevents'] != 3 ? calendarEventArray($low, $high) : array();
	$holidays = $modSettings['cal_showholidays'] && $modSettings['cal_showholidays'] != 3 ? calendarHolidayArray($low, $high) : array();

	// Days of the week taking into consideration that they may want it to start on any day.
	$context['week_days'] = array();
	$count = $nStartDay;
	for ($i = 0; $i < 7; $i++)
	{
		$context['week_days'][] = $count;
		$count++;
		if ($count == 7)
			$count = 0;
	}

	// An adjustment value to apply to all calculated week numbers.
	if (!empty($modSettings['cal_showweeknum']))
	{
		// Need to know what day the first of the year was on.
		$foy = (int) strftime('%w', mktime(0, 0, 0, 1, 1, $curPage['year']));

		// If the first day of the year is a Sunday, then there is no adjustment
		// to be made. However, if the first day of the year is not a Sunday, then there is a partial
		// week at the start of the year that needs to be accounted for.
		if ($nStartDay == 0)
			$nWeekAdjust = $foy == 0 ? 0 : 1;
		// If we are viewing the weeks, with a starting date other than Sunday, then things get complicated!
		// Basically, as PHP is calculating the weeks with a Sunday starting date, we need to take this into account
		// and offset the whole year dependant on whether the first day in the year is above or below our starting date.
		// Note that we offset by two, as some of this will get undone quite quickly by the statement below.
		else
			$nWeekAdjust = $nStartDay > $foy && $foy != 0 ? 2 : 1;

		// If our week starts on a day greater than the day the month starts on, then our week numbers will be one too high.
		// So we need to reduce it by one - all these thoughts of offsets makes my head hurt...
		if ($firstDayOfMonth['dayOfWeek'] < $nStartDay)
			$nWeekAdjust--;
	}
	else
		$nWeekAdjust = 0;

	// Basic template stuff.
	$context['can_post'] = allowedTo('calendar_post');
	$context['last_day'] = $nLastDay;
	$context['current_month'] = $curPage['month'];
	$context['current_year'] = $curPage['year'];

	// Load up the linktree!
	$context['linktree'][] = array(
		'url' => $scripturl . '?action=calendar;year=' . $context['current_year'] . ';month=' . $context['current_month'],
		'name' => $txt['months'][$context['current_month']] . ' ' . $context['current_year']
	);

	// Iterate through each week.
	$context['weeks'] = array();
	for ($nRow = 0; $nRow < $nRows; $nRow++)
	{
		// Start off the week - and don't let it go above 52, since that's the number of weeks in a year.
		$context['weeks'][$nRow] = array(
			'days' => array(),
			'number' => $firstDayOfMonth['weekNum'] + $nRow + $nWeekAdjust
		);
		// Handle the dreaded "week 53", it can happen, but only once in a blue moon ;)
		if ($context['weeks'][$nRow]['number'] == 53 && $nShift != 4)
			$context['weeks'][$nRow]['number'] = 1;

		// And figure out all the days.
		for ($nCol = 0; $nCol < 7; $nCol++)
		{
			$nDay = ($nRow * 7) + $nCol - $nShift + 1;

			if ($nDay < 1 || $nDay > $context['last_day'])
				$nDay = 0;

			$date = sprintf('%04d-%02d-%02d', $curPage['year'], $curPage['month'], $nDay);

			$context['weeks'][$nRow]['days'][$nCol] = array(
				'day' => $nDay,
				'date' => $date,
				'is_today' => $date == $today['date'],
				'is_first_day' => !empty($modSettings['cal_showweeknum']) && (($firstDayOfMonth['dayOfWeek'] + $nDay - 1) % 7 == $nStartDay),
				'holidays' => !empty($holidays[$date]) ? $holidays[$date] : array(),
				'events' => !empty($events[$date]) ? $events[$date] : array(),
				'birthdays' => !empty($bday[$date]) ? $bday[$date] : array()
			);
		}
	}

	// Find the previous month. (if we can go back that far.)
	if ($curPage['month'] > 1 || ($curPage['month'] == 1 && $curPage['year'] > $modSettings['cal_minyear']))
	{
		// Need to roll the year back one?
		$context['previous_calendar'] = array(
			'year' => $curPage['month'] == 1 ? $curPage['year'] - 1 : $curPage['year'],
			'month' => $curPage['month'] == 1 ? 12 : $curPage['month'] - 1,
		);
		$context['previous_calendar']['href'] = $scripturl . '?action=calendar;year=' . $context['previous_calendar']['year'] . ';month=' . $context['previous_calendar']['month'];
	}

	// The next month... (or can we go that far?)
	if ($curPage['month'] < 12 || ($curPage['month'] == 12 && $curPage['year'] < $modSettings['cal_maxyear']))
	{
		$context['next_calendar'] = array(
			'year' => $curPage['month'] == 12 ? $curPage['year'] + 1 : $curPage['year'],
			'month' => $curPage['month'] == 12 ? 1 : $curPage['month'] + 1
		);
		$context['next_calendar']['href'] = $scripturl . '?action=calendar;year=' . $context['next_calendar']['year'] . ';month=' . $context['next_calendar']['month'];
	}
}

// This is used by the board index to only find members of the current day. (month PLUS one!)
function calendarBirthdayArray($low_date, $high_date)
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

// Create an array of events occurring in this day/month.
function calendarEventArray($low_date, $high_date, $use_permissions = true)
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

// Builds an array of holiday strings for a particular month.  Note... month PLUS 1 not just month.
function calendarHolidayArray($low_date, $high_date)
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

// Consolidating the various INSERT statements into this function.
function calendarInsertEvent($id_board, $id_topic, $title, $id_member, $month, $day, $year, $span)
{
	global $db_prefix, $modSettings, $smfFunc;

	// Add special chars to the title.
	$title = $smfFunc['db_escape_string']($smfFunc['htmlspecialchars']($smfFunc['db_unescape_string']($title), ENT_QUOTES));

	// Add some sanity checking to the span.
	$span = empty($span) || trim($span) == '' ? 0 : min((int) $modSettings['cal_maxspan'], (int) $span - 1);

	// Insert the event!
	$smfFunc['db_query']('', "
		INSERT INTO {$db_prefix}calendar
			(id_board, id_topic, title, id_member, start_date, end_date)
		VALUES ($id_board, $id_topic, SUBSTRING('$title', 1, 48), $id_member, '" . strftime('%Y-%m-%d', mktime(0, 0, 0, $month, $day, $year)) . "', '" . strftime('%Y-%m-%d', mktime(0, 0, 0, $month, $day, $year) + $span * 86400) . "')", __FILE__, __LINE__);

	updateStats('calendar');
}

// Returns true if this user is allowed to link the topic in question.
function calendarCanLink()
{
	global $db_prefix, $user_info, $topic, $board, $smfFunc;

	// If you can't post, you can't link.
	isAllowedTo('calendar_post');

	// No board?  No topic?!?
	if (!isset($board))
		fatal_lang_error('calendar38', false);
	if (!isset($topic))
		fatal_lang_error('calendar39', false);

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
				fatal_lang_error('calendar41', 'user');
		}
		// Topic/Board doesn't exist.....
		else
			fatal_lang_error('calendar40', 'general');
		$smfFunc['db_free_result']($result);
	}

	// If you got this far, it's okay.
	return true;
}

function CalendarPost()
{
	global $context, $txt, $db_prefix, $user_info, $sourcedir, $scripturl;
	global $modSettings, $topic, $smfFunc;

	// Well - can they?
	isAllowedTo('calendar_post');

	// Cast this for safety...
	if (isset($_REQUEST['eventid']))
		$_REQUEST['eventid'] = (int) $_REQUEST['eventid'];

	// Submitting?
	if (isset($_POST['sc'], $_REQUEST['eventid']))
	{
		checkSession();

		// Validate the post...
		if (!isset($_POST['link_to_board']))
		{
			require_once($sourcedir . '/Subs-Post.php');
			calendarValidatePost();
		}

		// If you're not allowed to edit any events, you have to be the poster.
		if ($_REQUEST['eventid'] > 0 && !allowedTo('calendar_edit_any'))
		{
			// Get the event's poster.
			$request = $smfFunc['db_query']('', "
				SELECT id_member
				FROM {$db_prefix}calendar
				WHERE id_event = $_REQUEST[eventid]
				LIMIT 1", __FILE__, __LINE__);
			list ($poster) = $smfFunc['db_fetch_row']($request);
			$smfFunc['db_free_result']($request);

			// Finally, test if they can either edit ANY, or just their own...
			if (!allowedTo('calendar_edit_any'))
				isAllowedTo('calendar_edit_' . ($poster == $user_info['id'] ? 'own' : 'any'));
		}

		// New - and directing?
		if ($_REQUEST['eventid'] == -1 && isset($_POST['link_to_board']))
		{
			$_REQUEST['calendar'] = 1;
			require_once($sourcedir . '/Post.php');
			return Post();
		}
		// New...
		elseif ($_REQUEST['eventid'] == -1)
			calendarInsertEvent(0, 0, $_POST['evtitle'], $user_info['id'], $_POST['month'], $_POST['day'], $_POST['year'], isset($_POST['span']) ? $_POST['span'] : null);
		// Deleting...
		elseif (isset($_REQUEST['deleteevent']))
			$smfFunc['db_query']('', "
				DELETE FROM {$db_prefix}calendar
				WHERE id_event = $_REQUEST[eventid]", __FILE__, __LINE__);
		// ... or just update it?
		else
		{
			// Calculate the event_date depending on span.
			$span = empty($modSettings['cal_allowspan']) || empty($_POST['span']) || $_POST['span'] == 1 || empty($modSettings['cal_maxspan']) || $_POST['span'] > $modSettings['cal_maxspan'] ? 0 : min((int) $modSettings['cal_maxspan'], (int) $_POST['span'] - 1);
			$start_time = mktime(0, 0, 0, (int) $_REQUEST['month'], (int) $_REQUEST['day'], (int) $_REQUEST['year']);

			$smfFunc['db_query']('', "
				UPDATE {$db_prefix}calendar
				SET 
					start_date = '" . strftime('%Y-%m-%d', $start_time) . "',
					end_date = '" . strftime('%Y-%m-%d', $start_time + $span * 86400) . "', 
					title = '" . $smfFunc['db_escape_string']($smfFunc['htmlspecialchars']($smfFunc['db_unescape_string']($_REQUEST['evtitle']), ENT_QUOTES)) . "'
				WHERE id_event = $_REQUEST[eventid]", __FILE__, __LINE__);
		}

		updateStats('calendar');

		// No point hanging around here now...
		redirectexit($scripturl . '?action=calendar;month=' . $_POST['month'] . ';year=' . $_POST['year']);
	}

	// If we are not enabled... we are not enabled.
	if (empty($modSettings['cal_allow_unlinked']) && empty($_REQUEST['eventid']))
	{
		$_REQUEST['calendar'] = 1;
		require_once($sourcedir . '/Post.php');
		return Post();
	}

	// New?
	if (!isset($_REQUEST['eventid']))
	{
		$today = getdate();

		$context['event'] = array(
			'boards' => array(),
			'board' => 0,
			'new' => 1,
			'eventid' => -1,
			'year' => isset($_REQUEST['year']) ? $_REQUEST['year'] : $today['year'],
			'month' => isset($_REQUEST['month']) ? $_REQUEST['month'] : $today['mon'],
			'day' => isset($_REQUEST['day']) ? $_REQUEST['day'] : $today['mday'],
			'title' => '',
			'span' => 1,
		);

		// Get list of boards that can be posted in.
		$boards = boardsAllowedTo('post_new');
		if (empty($boards))
			fatal_lang_error('cannot_post_new', 'permission');

		$request = $smfFunc['db_query']('', "
			SELECT c.name AS cat_name, c.id_cat, b.id_board, b.name AS board_name, b.child_level
			FROM {$db_prefix}boards AS b
				LEFT JOIN {$db_prefix}categories AS c ON (c.id_cat = b.id_cat)
			WHERE $user_info[query_see_board]" . (in_array(0, $boards) ? '' : "
				AND b.id_board IN (" . implode(', ', $boards) . ")"), __FILE__, __LINE__);
		while ($row = $smfFunc['db_fetch_assoc']($request))
			$context['event']['boards'][] = array(
				'id' => $row['id_board'],
				'name' => $row['board_name'],
				'child_level' => $row['child_level'],
				'prefix' => str_repeat('&nbsp;', $row['child_level'] * 3),
				'cat' => array(
					'id' => $row['id_cat'],
					'name' => $row['cat_name']
				)
			);
		$smfFunc['db_free_result']($request);
	}
	else
	{
		$request = $smfFunc['db_query']('', "
			SELECT
				c.id_event, c.id_board, c.id_topic, MONTH(c.start_date) AS month,
				DAYOFMONTH(c.start_date) AS day, YEAR(c.start_date) AS year,
				(TO_DAYS(c.end_date) - TO_DAYS(c.start_date)) AS span, c.id_member, c.title,
				t.id_first_msg, t.id_member_started
			FROM {$db_prefix}calendar AS c
				LEFT JOIN {$db_prefix}topics AS t ON (t.id_topic = c.id_topic)
			WHERE c.id_event = $_REQUEST[eventid]", __FILE__, __LINE__);
		// If nothing returned, we are in poo, poo.
		if ($smfFunc['db_num_rows']($request) == 0)
			fatal_lang_error('no_access');
		$row = $smfFunc['db_fetch_assoc']($request);
		$smfFunc['db_free_result']($request);

		// If it has a board, then they should be editing it within the topic.
		if ($row['id_topic'] && $row['id_first_msg'])
		{
			// We load the board up, for a check on the board access rights...
			$topic = $row['id_topic'];
			loadBoard();
		}

		// Make sure the user is allowed to edit this event.
		if ($row['id_member'] != $user_info['id'])
			isAllowedTo('calendar_edit_any');
		elseif (!allowedTo('calendar_edit_any'))
			isAllowedTo('calendar_edit_own');

		$context['event'] = array(
			'boards' => array(),
			'board' => $row['id_board'],
			'new' => 0,
			'eventid' => $_REQUEST['eventid'],
			'year' => $row['year'],
			'month' => $row['month'],
			'day' => $row['day'],
			'title' => $row['title'],
			'span' => 1 + $row['span'],
		);
	}

	$context['event']['last_day'] = (int) strftime('%d', mktime(0, 0, 0, $context['event']['month'] == 12 ? 1 : $context['event']['month'] + 1, 0, $context['event']['month'] == 12 ? $context['event']['year'] + 1 : $context['event']['year']));

	// Template, sub template, etc.
	loadTemplate('Calendar');
	$context['sub_template'] = 'event_post';

	$context['page_title'] = isset($_REQUEST['eventid']) ? $txt['calendar_edit'] : $txt['calendar_post_event'];
	$context['linktree'][] = array(
		'name' => $context['page_title'],
	);
}

?>