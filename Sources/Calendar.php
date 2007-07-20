<?php
/**********************************************************************************
* Calendar.php                                                                    *
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

	void CalendarPost()
		- processes posting/editing/deleting a calendar event.
		- calls Post() function if event is linked to a post.
		- calls insertEvent() to insert the event if not linked to post.
		- requires the calendar_post permission to use.
		- uses the event_post sub template in the Calendar template.
		- is accessed with ?action=calendar;sa=post.
*/

// Show the calendar.
function CalendarMain()
{
	global $txt, $context, $modSettings, $scripturl, $options, $sourcedir;

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
	require_once($sourcedir . '/Subs-Calendar.php');
	$today = getTodayInfo();

	// If the month and year are not passed in, use today's date as a starting point.
	$curPage = array(
		'month' => isset($_REQUEST['month']) ? (int) $_REQUEST['month'] : $today['month'],
		'year' => isset($_REQUEST['year']) ? (int) $_REQUEST['year'] : $today['year']
	);

	// Make sure the year and month are in valid ranges.
	if ($curPage['month'] < 1 || $curPage['month'] > 12)
		fatal_lang_error('invalid_month', false);
	if ($curPage['year'] < $modSettings['cal_minyear'] || $curPage['year'] > $modSettings['cal_maxyear'])
		fatal_lang_error('invalid_year', false);

	// Load all the context information needed to show the calendar grid.
	$calendarOptions = array(
		'start_day' => !empty($options['calendar_start_day']) ? $options['calendar_start_day'] : 0,
		'show_birthdays' => in_array($modSettings['cal_showbdays'], array(1, 2)),
		'show_events' => in_array($modSettings['cal_showevents'], array(1, 2)),
		'show_holidays' => in_array($modSettings['cal_showholidays'], array(1, 2)),
		'show_week_num' => !empty($modSettings['cal_showweeknum']),
	);
	$context += getCalendarGrid($curPage['month'], $curPage['year'], $calendarOptions);

	// Basic template stuff.
	$context['can_post'] = allowedTo('calendar_post');
	$context['current_month'] = $curPage['month'];
	$context['current_year'] = $curPage['year'];

	// Load up the linktree!
	$context['linktree'][] = array(
		'url' => $scripturl . '?action=calendar;year=' . $context['current_year'] . ';month=' . $context['current_month'],
		'name' => $txt['months'][$context['current_month']] . ' ' . $context['current_year']
	);
}

function CalendarPost()
{
	global $context, $txt, $db_prefix, $user_info, $sourcedir, $scripturl;
	global $modSettings, $topic, $smfFunc;

	// Well - can they?
	isAllowedTo('calendar_post');

	// We need this for all kinds of useful functions.
	require_once($sourcedir . '/Subs-Calendar.php');

	// Cast this for safety...
	if (isset($_REQUEST['eventid']))
		$_REQUEST['eventid'] = (int) $_REQUEST['eventid'];

	// Submitting?
	if (isset($_POST['sc'], $_REQUEST['eventid']))
	{
		checkSession();

		// Validate the post...
		if (!isset($_POST['link_to_board']))
			validateEventPost();

		// If you're not allowed to edit any events, you have to be the poster.
		if ($_REQUEST['eventid'] > 0 && !allowedTo('calendar_edit_any'))
			isAllowedTo('calendar_edit_' . (!empty($user_info['id']) && getEventPoster($_REQUEST['eventid']) == $user_info['id'] ? 'own' : 'any'));

		// New - and directing?
		if ($_REQUEST['eventid'] == -1 && isset($_POST['link_to_board']))
		{
			$_REQUEST['calendar'] = 1;
			require_once($sourcedir . '/Post.php');
			return Post();
		}
		// New...
		elseif ($_REQUEST['eventid'] == -1)
		{
			$eventOptions = array(
				'board' => 0,
				'topic' => 0,
				'title' => $_POST['evtitle'],
				'member' => $user_info['id'],
				'start_date' => sprintf('%04d-%02d-%02d', $_POST['year'], $_POST['month'], $_POST['day']),
				'span' => isset($_POST['span']) && $_POST['span'] > 0 ? min((int) $modSettings['cal_maxspan'], (int) $_POST['span'] - 1) : 0,
			);
			insertEvent($eventOptions);
		}

		// Deleting...
		elseif (isset($_REQUEST['deleteevent']))
			removeEvent($_REQUEST['eventid']);

		// ... or just update it?
		else
		{
			$eventOptions = array(
				'title' => $_REQUEST['evtitle'],
				'span' => empty($modSettings['cal_allowspan']) || empty($_POST['span']) || $_POST['span'] == 1 || empty($modSettings['cal_maxspan']) || $_POST['span'] > $modSettings['cal_maxspan'] ? 0 : min((int) $modSettings['cal_maxspan'], (int) $_POST['span'] - 1),
				'start_date' => strftime('%Y-%m-%d', mktime(0, 0, 0, (int) $_REQUEST['month'], (int) $_REQUEST['day'], (int) $_REQUEST['year'])),
			);

			modifyEvent($_REQUEST['eventid'], $eventOptions);
		}

		updateSettings(array(
			'calendar_updated' => time(),
		));

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
		$context['event']['last_day'] = (int) strftime('%d', mktime(0, 0, 0, $context['event']['month'] == 12 ? 1 : $context['event']['month'] + 1, 0, $context['event']['month'] == 12 ? $context['event']['year'] + 1 : $context['event']['year']));

		// Get list of boards that can be posted in.
		$boards = boardsAllowedTo('post_new');
		if (empty($boards))
			fatal_lang_error('cannot_post_new', 'permission');

		// Load the list of boards and categories in the context.
		require_once($sourcedir . '/Subs-MessageIndex.php');
		$boardListOptions = array(
			'included_boards' => in_array(0, $boards) ? null : $boards,
			'use_permissions' => true,
			'selected_board' => $modSettings['cal_defaultboard'],
		);
		$context['event']['categories'] = getBoardList($boardListOptions);
	}
	else
	{
		$context['event'] = getEventProperties($_REQUEST['eventid']);

		if ($context['event'] === false)
			fatal_lang_error('no_access');

		// If it has a board, then they should be editing it within the topic.
		if (!empty($context['event']['topic']['id']) && !empty($context['event']['topic']['first_msg']))
		{
			// We load the board up, for a check on the board access rights...
			$topic = $context['event']['topic']['id'];
			loadBoard();
		}

		// Make sure the user is allowed to edit this event.
		if ($context['event']['member'] != $user_info['id'])
			isAllowedTo('calendar_edit_any');
		elseif (!allowedTo('calendar_edit_any'))
			isAllowedTo('calendar_edit_own');
	}

	// Template, sub template, etc.
	loadTemplate('Calendar');
	$context['sub_template'] = 'event_post';

	$context['page_title'] = isset($_REQUEST['eventid']) ? $txt['calendar_edit'] : $txt['calendar_post_event'];
	$context['linktree'][] = array(
		'name' => $context['page_title'],
	);
}

?>