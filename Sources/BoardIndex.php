<?php
/**********************************************************************************
* BoardIndex.php                                                                  *
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

/*	The single function this file contains is used to display the main
	board index.  It uses just the following functions:

	void BoardIndex()
		- shows the board index.
		- uses the BoardIndex template, and main sub template.
		- may use the boardindex subtemplate for wireless support.
		- updates the most online statistics.
		- is accessed by ?action=boardindex.

	bool calendarDoIndex()
		- prepares the calendar data for the board index.
		- takes care of caching it for speed.
		- depends upon these settings: cal_showevents,
		  cal_showbdays, cal_showholidays.
		- returns whether there is anything to display.

	void CollapseCategory()
		// !!!
*/

// Show the board index!
function BoardIndex()
{
	global $txt, $scripturl, $db_prefix, $user_info, $sourcedir;
	global $modSettings, $context, $settings, $smfFunc;

	// For wireless, we use the Wireless template...
	if (WIRELESS)
		$context['sub_template'] = WIRELESS_PROTOCOL . '_boardindex';
	else
		loadTemplate('BoardIndex');

	// Remember the most recent topic for optimizing the recent posts feature.
	$most_recent_topic = array(
		'timestamp' => 0,
		'ref' => null
	);

	// Retrieve the categories and boards.
	require_once($sourcedir . '/Subs-BoardIndex.php');
	$boardIndexOptions = array(
		'include_categories' => true,
		'base_level' => 0,
		'parent_id' => 0,
		'set_latest_post' => true,
		'countChildPosts' => !empty($modSettings['countChildPosts']),
	);
	$context['categories'] = getBoardIndex($boardIndexOptions);

	// Get the user online list.
	require_once($sourcedir . '/Subs-MembersOnline.php');
	$membersOnlineOptions = array(
		'show_hidden' => allowedTo('moderate_forum'),
		'sort' => 'log_time',
		'reverse_sort' => true,
	);
	$membersOnlineStats = getMembersOnlineStats($membersOnlineOptions);
	$context += $membersOnlineStats;

	$context['show_buddies'] = !empty($user_info['buddies']);

	// Are we showing all membergroups on the board index?
	if (!empty($settings['show_group_key']) && !empty($modSettings['groupCache']))
	{
		$context['membergroups'] = array();
		$groupCache = unserialize($smfFunc['db_unescape_string']($modSettings['groupCache']));
		foreach ($groupCache as $link)
			$context['membergroups'][] = '<a href="' . $scripturl . '?action=groups;sa=members;group=' . $link . '</a>';
	}

	// Track most online statistics?
	if (!empty($modSettings['trackStats']))
	{
		// Determine the most users online - both all time and per day.
		$total_users = $context['num_guests'] + $context['num_users_online'];

		// More members on now than ever were?  Update it!
		if (!isset($modSettings['mostOnline']) || $total_users >= $modSettings['mostOnline'])
			updateSettings(array('mostOnline' => $total_users, 'mostDate' => time()));

		$date = strftime('%Y-%m-%d', forum_time(false));

		// One or more stats are not up-to-date?
		if (!isset($modSettings['mostOnlineUpdated']) || $modSettings['mostOnlineUpdated'] != $date)
		{
			$request = $smfFunc['db_query']('', "
				SELECT most_on
				FROM {$db_prefix}log_activity
				WHERE date = '$date'
				LIMIT 1", __FILE__, __LINE__);

			// The log_activity hasn't got an entry for today?
			if ($smfFunc['db_num_rows']($request) == 0)
			{
				$smfFunc['db_insert']('ignore',
					"{$db_prefix}log_activity",
					array('date', 'most_on'),
					array('\'' . $date . '\'', $total_users),
					array('date'), __FILE__, __LINE__
				);
			}
			// There's an entry in log_activity on today...
			else
			{
				list ($modSettings['mostOnlineToday']) = $smfFunc['db_fetch_row']($request);

				if ($total_users > $modSettings['mostOnlineToday'])
					trackStats(array('most_on' => $total_users));

				$total_users = max($total_users, $modSettings['mostOnlineToday']);
			}
			$smfFunc['db_free_result']($request);

			updateSettings(array('mostOnlineUpdated' => $date, 'mostOnlineToday' => $total_users));
		}
		// Highest number of users online today?
		elseif ($total_users > $modSettings['mostOnlineToday'])
		{
			trackStats(array('most_on' => $total_users));
			updateSettings(array('mostOnlineUpdated' => $date, 'mostOnlineToday' => $total_users));
		}
	}

	// Set the latest member.
	$context['latest_member'] = &$context['common_stats']['latest_member'];

	if (!empty($settings['number_recent_posts']) && $settings['number_recent_posts'] > 1)
	{
		require_once($sourcedir . '/Recent.php');

		if (($context['latest_posts'] = cache_get_data('boardindex-latest_posts:' . md5($user_info['query_wanna_see_board'] . $user_info['language']), 180)) == null)
		{
			$context['latest_posts'] = getLastPosts($settings['number_recent_posts']);
			cache_put_data('boardindex-latest_posts:' . md5($user_info['query_wanna_see_board'] . $user_info['language']), $context['latest_posts'], 180);
		}

		// We have to clean up the cached data a bit.
		foreach ($context['latest_posts'] as $k => $post)
		{
			$context['latest_posts'][$k]['time'] = timeformat($post['raw_timestamp']);
			$context['latest_posts'][$k]['timestamp'] = forum_time(true, $post['raw_timestamp']);
		}
	}

	$settings['display_recent_bar'] = !empty($settings['number_recent_posts']) ? $settings['number_recent_posts'] : 0;
	$settings['show_member_bar'] &= allowedTo('view_mlist');
	$context['show_stats'] = allowedTo('view_stats') && !empty($modSettings['trackStats']);
	$context['show_member_list'] = allowedTo('view_mlist');
	$context['show_who'] = allowedTo('who_view') && !empty($modSettings['who_enabled']);

	// Set some permission related settings.
	$context['show_login_bar'] = $user_info['is_guest'] && !empty($modSettings['enableVBStyleLogin']);
	$context['show_calendar'] = allowedTo('calendar_view') && !empty($modSettings['cal_enabled']);

	// Load the calendar?
	if ($context['show_calendar'])
		$context['show_calendar'] = calendarDoIndex();

	$context['page_title'] = sprintf($txt['forum_index'], $context['forum_name']);
}

// Called from the BoardIndex to display the current day's events on the board index.
function calendarDoIndex()
{
	global $modSettings, $context, $user_info, $scripturl, $sc;

	// Make sure at least one of the options is enabled.
	if ($modSettings['cal_showevents'] < 2 && $modSettings['cal_showbdays'] < 2 && $modSettings['cal_showholidays'] < 2)
		return false;

	// Get the current forum time and check whether the statistics are up to date.
	if (empty($modSettings['cal_today_updated']) || $modSettings['cal_today_updated'] != strftime('%Y%m%d', forum_time(false)))
		updateStats('calendar');

	// Load the holidays for today, ...
	if ($modSettings['cal_showholidays'] > 1 && isset($modSettings['cal_today_holiday']))
		$holidays = unserialize($modSettings['cal_today_holiday']);
	// ... the birthdays for today, ...
	if ($modSettings['cal_showbdays'] > 1 && isset($modSettings['cal_today_birthday']))
		$bday = unserialize($modSettings['cal_today_birthday']);
	// ... and the events for today.
	if ($modSettings['cal_showevents'] > 1 && isset($modSettings['cal_today_event']))
		$events = unserialize($modSettings['cal_today_event']);

	// No events, birthdays, or holidays... don't show anything.  Simple.
	if (empty($holidays) && empty($bday) && empty($events))
		return false;

	// This shouldn't be less than one!
	if (empty($modSettings['cal_days_for_index']) || $modSettings['cal_days_for_index'] < 1)
		$days_for_index = 86400;
	else
		$days_for_index = $modSettings['cal_days_for_index'] * 86400;

	$context['calendar_only_today'] = $modSettings['cal_days_for_index'] == 1;

	// Get the current member time/date.
	$now = forum_time();

	// This is used to show the "how-do-I-edit" help.
	$context['calendar_can_edit'] = allowedTo('calendar_edit_any');

	// Holidays between now and now + days.
	$context['calendar_holidays'] = array();
	for ($i = $now; $i < $now + $days_for_index; $i += 86400)
	{
		if (isset($holidays[strftime('%Y-%m-%d', $i)]))
			$context['calendar_holidays'] = array_merge($context['calendar_holidays'], $holidays[strftime('%Y-%m-%d', $i)]);
	}

	// Happy Birthday, guys and gals!
	$context['calendar_birthdays'] = array();
	for ($i = $now; $i < $now + $days_for_index; $i += 86400)
		if (isset($bday[strftime('%Y-%m-%d', $i)]))
		{
			foreach ($bday[strftime('%Y-%m-%d', $i)] as $index => $dummy)
				$bday[strftime('%Y-%m-%d', $i)][$index]['is_today'] = strftime('%Y-%m-%d', $i) == strftime('%Y-%m-%d', forum_time());
			$context['calendar_birthdays'] = array_merge($context['calendar_birthdays'], $bday[strftime('%Y-%m-%d', $i)]);
		}

	$context['calendar_events'] = array();
	$duplicates = array();
	for ($i = $now; $i < $now + $days_for_index; $i += 86400)
	{
		if (empty($events[strftime('%Y-%m-%d', $i)]))
			continue;

		foreach ($events[strftime('%Y-%m-%d', $i)] as $ev => $event)
		{
			if (empty($event['topic']) || ((count(array_intersect($user_info['groups'], $event['allowed_groups'])) != 0 || allowedTo('admin_forum'))) && !in_array($event['id_board'], $user_info['ignoreboards']))
			{
				if (isset($duplicates[$events[strftime('%Y-%m-%d', $i)][$ev]['topic'] . $events[strftime('%Y-%m-%d', $i)][$ev]['title']]))
				{
					unset($events[strftime('%Y-%m-%d', $i)][$ev]);
					continue;
				}

				$this_event = &$events[strftime('%Y-%m-%d', $i)][$ev];
				$this_event['href'] = $this_event['topic'] == 0 ? '' : $scripturl . '?topic=' . $this_event['topic'] . '.0';
				$this_event['modify_href'] = $scripturl . '?action=' . ($this_event['topic'] == 0 ? 'calendar;sa=post;' : 'post;msg=' . $this_event['msg'] . ';topic=' . $this_event['topic'] . '.0;calendar;') . 'eventid=' . $this_event['id'] . ';sesc=' . $sc;
				$this_event['can_edit'] = allowedTo('calendar_edit_any') || ($this_event['poster'] == $user_info['id'] && allowedTo('calendar_edit_own'));
				$this_event['is_today'] = (strftime('%Y-%m-%d', $i)) == strftime('%Y-%m-%d', forum_time());
				$this_event['date'] = strftime('%Y-%m-%d', $i);

				$duplicates[$this_event['topic'] . $this_event['title']] = true;
			}
			else
				unset($events[strftime('%Y-%m-%d', $i)][$ev]);
		}

		if (!empty($events[strftime('%Y-%m-%d', $i)]))
			$context['calendar_events'] = array_merge($context['calendar_events'], $events[strftime('%Y-%m-%d', $i)]);
	}

	for ($i = 0, $n = count($context['calendar_birthdays']); $i < $n; $i++)
		$context['calendar_birthdays'][$i]['is_last'] = !isset($context['calendar_birthdays'][$i + 1]);
	for ($i = 0, $n = count($context['calendar_events']); $i < $n; $i++)
		$context['calendar_events'][$i]['is_last'] = !isset($context['calendar_events'][$i + 1]);

	// This is used to make sure the header should be displayed.
	return !empty($context['calendar_holidays']) || !empty($context['calendar_birthdays']) || !empty($context['calendar_events']);
}

// Collapse or expand a category
function CollapseCategory()
{
	global $user_info, $sourcedir;

	// Check if the input values are correct.
	if (in_array($_REQUEST['sa'], array('expand', 'collapse', 'toggle')) && isset($_REQUEST['c']))
	{
		// And collapse/expand/toggle the category.
		require_once($sourcedir . '/Subs-Categories.php');
		collapseCategories(array((int) $_REQUEST['c']), $_REQUEST['sa'],array($user_info['id']));
	}

	// And go back to the board index.
	BoardIndex();
}

?>