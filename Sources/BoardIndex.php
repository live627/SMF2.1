<?php
/******************************************************************************
* BoardIndex.php                                                              *
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

	// Find all boards and categories, as well as related information.  This will be sorted by the natural order of boards and categories, which we control.
	$result_boards = $smfFunc['db_query']('boardindex_fetch_boards', "
		SELECT
			c.name AS cat_name, c.id_cat, b.id_board, b.name AS board_name, b.description,
			b.num_posts, b.num_topics, b.unapproved_posts, b.unapproved_topics, b.id_parent,
			IFNULL(m.poster_time, 0) AS poster_time, IFNULL(mem.member_name, m.poster_name) AS poster_name,
			m.subject, m.id_topic, IFNULL(mem.real_name, m.poster_name) AS real_name,
			" . ($user_info['is_guest'] ? "	1 AS is_read, 0 AS new_from" : "
			(IFNULL(lb.id_msg, 0) >= b.id_msg_updated) AS is_read, IFNULL(lb.id_msg, -1) + 1 AS new_from,
			c.can_collapse, IFNULL(cc.id_member, 0) AS is_collapsed") . ",
			IFNULL(mem.id_member, 0) AS id_member, m.id_msg,
			IFNULL(mods_mem.id_member, 0) AS ID_MODERATOR, mods_mem.real_name AS modRealName
		FROM {$db_prefix}boards AS b
			LEFT JOIN {$db_prefix}categories AS c ON (c.id_cat = b.id_cat)
			LEFT JOIN {$db_prefix}messages AS m ON (m.id_msg = b.id_last_msg)
			LEFT JOIN {$db_prefix}members AS mem ON (mem.id_member = m.id_member)" . (!$user_info['is_guest'] ? "
			LEFT JOIN {$db_prefix}log_boards AS lb ON (lb.id_board = b.id_board AND lb.id_member = $user_info[id])
			LEFT JOIN {$db_prefix}collapsed_categories AS cc ON (cc.id_cat = c.id_cat AND cc.id_member = $user_info[id])" : '') . "
			LEFT JOIN {$db_prefix}moderators AS mods ON (mods.id_board = b.id_board)
			LEFT JOIN {$db_prefix}members AS mods_mem ON (mods_mem.id_member = mods.id_member)
		WHERE $user_info[query_see_board]" . (empty($modSettings['countChildPosts']) ? "
			AND b.child_level <= 1" : ''), __FILE__, __LINE__);

	// Run through the categories and boards....
	$context['categories'] = array();
	while ($row_board = $smfFunc['db_fetch_assoc']($result_boards))
	{
		$ignoreThisBoard = in_array($row_board['id_board'], $user_info['ignoreboards']);
		$row_board['is_read'] = !empty($row_board['is_read']) || $ignoreThisBoard ? '1' : '0';
		// Haven't set this category yet.
		if (empty($context['categories'][$row_board['id_cat']]))
		{
			$context['categories'][$row_board['id_cat']] = array(
				'id' => $row_board['id_cat'],
				'name' => $row_board['cat_name'],
				'is_collapsed' => isset($row_board['can_collapse']) && $row_board['can_collapse'] == 1 && $row_board['is_collapsed'] > 0,
				'can_collapse' => isset($row_board['can_collapse']) && $row_board['can_collapse'] == 1,
				'collapse_href' => isset($row_board['can_collapse']) ? $scripturl . '?action=collapse;c=' . $row_board['id_cat'] . ';sa=' . ($row_board['is_collapsed'] > 0 ? 'expand' : 'collapse;') . '#' . $row_board['id_cat'] : '',
				'collapse_image' => isset($row_board['can_collapse']) ? '<img src="' . $settings['images_url'] . '/' . ($row_board['is_collapsed'] > 0 ? 'expand.gif" alt="+"' : 'collapse.gif" alt="-"') . ' border="0" />' : '',
				'href' => $scripturl . '#' . $row_board['id_cat'],
				'boards' => array(),
				'new' => false
			);
			$context['categories'][$row_board['id_cat']]['link'] = '<a name="' . $row_board['id_cat'] . '" href="' . (isset($row_board['can_collapse']) ? $context['categories'][$row_board['id_cat']]['collapse_href'] : $context['categories'][$row_board['id_cat']]['href']) . '">' . $row_board['cat_name'] . '</a>';
		}

		// If this board has new posts in it (and isn't the recycle bin!) then the category is new.
		if (empty($modSettings['recycle_enable']) || $modSettings['recycle_board'] != $row_board['id_board'])
			$context['categories'][$row_board['id_cat']]['new'] |= empty($row_board['is_read']) && $row_board['poster_name'] != '';

		// Collapsed category - don't do any of this.
		if ($context['categories'][$row_board['id_cat']]['is_collapsed'])
			continue;

		// Let's save some typing.  Climbing the array might be slower, anyhow.
		$this_category = &$context['categories'][$row_board['id_cat']]['boards'];

		// This is a parent board.
		if (empty($row_board['id_parent']))
		{
			// Is this a new board, or just another moderator?
			if (!isset($this_category[$row_board['id_board']]))
			{
				// Not a child.
				$isChild = false;

				$this_category[$row_board['id_board']] = array(
					'new' => empty($row_board['is_read']),
					'id' => $row_board['id_board'],
					'name' => $row_board['board_name'],
					'description' => $row_board['description'],
					'moderators' => array(),
					'link_moderators' => array(),
					'children' => array(),
					'link_children' => array(),
					'children_new' => false,
					'topics' => $row_board['num_topics'],
					'posts' => $row_board['num_posts'],
					'unapproved_topics' => $row_board['unapproved_topics'],
					'unapproved_posts' => $row_board['unapproved_posts'] - $row_board['unapproved_topics'],
					'can_approve_posts' => !empty($user_info['mod_cache']['ap']) && ($user_info['mod_cache']['ap'] == array(0) || in_array($row_board['id_board'], $user_info['mod_cache']['ap'])),
					'href' => $scripturl . '?board=' . $row_board['id_board'] . '.0',
					'link' => '<a href="' . $scripturl . '?board=' . $row_board['id_board'] . '.0">' . $row_board['board_name'] . '</a>'
				);
			}
			if (!empty($row_board['ID_MODERATOR']))
			{
				$this_category[$row_board['id_board']]['moderators'][$row_board['ID_MODERATOR']] = array(
					'id' => $row_board['ID_MODERATOR'],
					'name' => $row_board['modRealName'],
					'href' => $scripturl . '?action=profile;u=' . $row_board['ID_MODERATOR'],
					'link' => '<a href="' . $scripturl . '?action=profile;u=' . $row_board['ID_MODERATOR'] . '" title="' . $txt['board_moderator'] . '">' . $row_board['modRealName'] . '</a>'
				);
				$this_category[$row_board['id_board']]['link_moderators'][] = '<a href="' . $scripturl . '?action=profile;u=' . $row_board['ID_MODERATOR'] . '" title="' . $txt['board_moderator'] . '">' . $row_board['modRealName'] . '</a>';
			}
		}
		// Found a child board.... make sure we've found its parent and the child hasn't been set already.
		elseif (isset($this_category[$row_board['id_parent']]['children']) && !isset($this_category[$row_board['id_parent']]['children'][$row_board['id_board']]))
		{
			// A valid child!
			$isChild = true;

			$this_category[$row_board['id_parent']]['children'][$row_board['id_board']] = array(
				'id' => $row_board['id_board'],
				'name' => $row_board['board_name'],
				'description' => $row_board['description'],
				'new' => empty($row_board['is_read']) && $row_board['poster_name'] != '',
				'topics' => $row_board['num_topics'],
				'posts' => $row_board['num_posts'],
				'unapproved_topics' => $row_board['unapproved_topics'],
				'unapproved_posts' => $row_board['unapproved_posts'] - $row_board['unapproved_topics'],
				'can_approve_posts' => !empty($user_info['mod_cache']['ap']) && ($user_info['mod_cache']['ap'] == array(0) || in_array($row_board['id_board'], $user_info['mod_cache']['ap'])),
				'href' => $scripturl . '?board=' . $row_board['id_board'] . '.0',
				'link' => '<a href="' . $scripturl . '?board=' . $row_board['id_board'] . '.0">' . $row_board['board_name'] . '</a>'
			);

			// Counting child board posts is... slow :/.
			if (!empty($modSettings['countChildPosts']))
			{
				$this_category[$row_board['id_parent']]['posts'] += $row_board['num_posts'];
				$this_category[$row_board['id_parent']]['topics'] += $row_board['num_topics'];
			}

			// Does this board contain new boards?
			$this_category[$row_board['id_parent']]['children_new'] |= empty($row_board['is_read']);

			// This is easier to use in many cases for the theme....
			$this_category[$row_board['id_parent']]['link_children'][] = &$this_category[$row_board['id_parent']]['children'][$row_board['id_board']]['link'];
		}
		// Child of a child... just add it on...
		elseif (!empty($modSettings['countChildPosts']))
		{
			if (!isset($parent_map))
				$parent_map = array();

			if (!isset($parent_map[$row_board['id_parent']]))
				foreach ($this_category as $id => $board)
				{
					if (!isset($board['children'][$row_board['id_parent']]))
						continue;

					$parent_map[$row_board['id_parent']] = array(&$this_category[$id], &$this_category[$id]['children'][$row_board['id_parent']]);
					$parent_map[$row_board['id_board']] = array(&$this_category[$id], &$this_category[$id]['children'][$row_board['id_parent']]);

					break;
				}

			if (isset($parent_map[$row_board['id_parent']]))
			{
				$parent_map[$row_board['id_parent']][0]['posts'] += $row_board['num_posts'];
				$parent_map[$row_board['id_parent']][0]['topics'] += $row_board['num_topics'];
				$parent_map[$row_board['id_parent']][1]['posts'] += $row_board['num_posts'];
				$parent_map[$row_board['id_parent']][1]['topics'] += $row_board['num_topics'];

				continue;
			}

			continue;
		}
		// Found a child of a child - skip.
		else
			continue;

		// Prepare the subject, and make sure it's not too long.
		censorText($row_board['subject']);
		$row_board['short_subject'] = shorten_subject($row_board['subject'], 24);
		$this_last_post = array(
			'id' => $row_board['id_msg'],
			'time' => $row_board['poster_time'] > 0 ? timeformat($row_board['poster_time']) : $txt[470],
			'timestamp' => forum_time(true, $row_board['poster_time']),
			'subject' => $row_board['short_subject'],
			'member' => array(
				'id' => $row_board['id_member'],
				'username' => $row_board['poster_name'] != '' ? $row_board['poster_name'] : $txt[470],
				'name' => $row_board['real_name'],
				'href' => $row_board['poster_name'] != '' && !empty($row_board['id_member']) ? $scripturl . '?action=profile;u=' . $row_board['id_member'] : '',
				'link' => $row_board['poster_name'] != '' ? (!empty($row_board['id_member']) ? '<a href="' . $scripturl . '?action=profile;u=' . $row_board['id_member'] . '">' . $row_board['real_name'] . '</a>' : $row_board['real_name']) : $txt[470],
			),
			'start' => 'msg' . $row_board['new_from'],
			'topic' => $row_board['id_topic']
		);

		// Provide the href and link.
		if ($row_board['subject'] != '')
		{
			$this_last_post['href'] = $scripturl . '?topic=' . $row_board['id_topic'] . '.msg' . ($user_info['is_guest'] ? $modSettings['maxMsgID'] : $row_board['new_from']) . (empty($row_board['is_read']) ? ';boardseen' : '') . '#new';
			$this_last_post['link'] = '<a href="' . $this_last_post['href'] . '" title="' . $row_board['subject'] . '">' . $row_board['short_subject'] . '</a>';
		}
		else
		{
			$this_last_post['href'] = '';
			$this_last_post['link'] = $txt[470];
		}

		// Set the last post in the parent board.
		if (empty($row_board['id_parent']) || ($isChild && !empty($row_board['poster_time']) && $this_category[$row_board['id_parent']]['last_post']['timestamp'] < forum_time(true, $row_board['poster_time'])))
			$this_category[$isChild ? $row_board['id_parent'] : $row_board['id_board']]['last_post'] = $this_last_post;
		// Just in the child...?
		if ($isChild)
		{
			$this_category[$row_board['id_parent']]['children'][$row_board['id_board']]['last_post'] = $this_last_post;

			// If there are no posts in this board, it really can't be new...
			$this_category[$row_board['id_parent']]['children'][$row_board['id_board']]['new'] &= $row_board['poster_name'] != '';
		}
		// No last post for this board?  It's not new then, is it..?
		elseif ($row_board['poster_name'] == '')
			$this_category[$row_board['id_board']]['new'] = false;

		// Determine a global most recent topic.
		if (!empty($row_board['poster_time']) && forum_time(true, $row_board['poster_time']) > $most_recent_topic['timestamp'] && !$ignoreThisBoard)
			$most_recent_topic = array(
				'timestamp' => forum_time(true, $row_board['poster_time']),
				'ref' => &$this_category[$isChild ? $row_board['id_parent'] : $row_board['id_board']]['last_post'],
			);
	}
	$smfFunc['db_free_result']($result_boards);

	// Load the users online right now.
	$result = $smfFunc['db_query']('', "
		SELECT
			lo.id_member, lo.log_time, mem.real_name, mem.member_name, mem.show_online,
			mg.online_color, mg.id_group, mg.group_name
		FROM {$db_prefix}log_online AS lo
			LEFT JOIN {$db_prefix}members AS mem ON (mem.id_member = lo.id_member)
			LEFT JOIN {$db_prefix}membergroups AS mg ON (mg.id_group = CASE WHEN mem.id_group = 0 THEN mem.id_post_group ELSE mem.id_group END)", __FILE__, __LINE__);

	$context['users_online'] = array();
	$context['list_users_online'] = array();
	$context['online_groups'] = array();
	$context['num_guests'] = 0;
	$context['num_buddies'] = 0;
	$context['num_users_hidden'] = 0;

	$context['show_buddies'] = !empty($user_info['buddies']);

	while ($row = $smfFunc['db_fetch_assoc']($result))
	{
		if (empty($row['real_name']))
		{
			$context['num_guests']++;
			continue;
		}
		elseif (empty($row['show_online']) && !allowedTo('moderate_forum'))
		{
			$context['num_users_hidden']++;
			continue;
		}

		// Some basic color coding...
		if (!empty($row['online_color']))
			$link = '<a href="' . $scripturl . '?action=profile;u=' . $row['id_member'] . '" style="color: ' . $row['online_color'] . ';">' . $row['real_name'] . '</a>';
		else
			$link = '<a href="' . $scripturl . '?action=profile;u=' . $row['id_member'] . '">' . $row['real_name'] . '</a>';

		$is_buddy = in_array($row['id_member'], $user_info['buddies']);
		if ($is_buddy)
		{
			$context['num_buddies']++;
			$link = '<b>' . $link . '</b>';
		}

		$context['users_online'][$row['log_time'] . $row['member_name']] = array(
			'id' => $row['id_member'],
			'username' => $row['member_name'],
			'name' => $row['real_name'],
			'group' => $row['id_group'],
			'href' => $scripturl . '?action=profile;u=' . $row['id_member'],
			'link' => $link,
			'is_buddy' => $is_buddy,
			'hidden' => empty($row['show_online']),
		);

		$context['list_users_online'][$row['log_time'] . $row['member_name']] = empty($row['show_online']) ? '<i>' . $link . '</i>' : $link;

		if (!isset($context['online_groups'][$row['id_group']]))
			$context['online_groups'][$row['id_group']] = array(
				'id' => $row['id_group'],
				'name' => $row['group_name'],
				'color' => $row['online_color']
			);
	}
	$smfFunc['db_free_result']($result);

	krsort($context['users_online']);
	krsort($context['list_users_online']);
	ksort($context['online_groups']);

	$context['num_users_online'] = count($context['users_online']) + $context['num_users_hidden'];

	// Are we showing all membergroups on the board index?
	if (!empty($settings['show_group_key']) && !empty($modSettings['groupCache']))
	{
		$context['membergroups'] = array();
		$groupCache = unserialize(stripslashes($modSettings['groupCache']));
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
					array('date')
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

	// Load the most recent post?
	if ((!empty($settings['number_recent_posts']) && $settings['number_recent_posts'] == 1) || $settings['show_stats_index'])
		$context['latest_post'] = $most_recent_topic['ref'];

	if (!empty($settings['number_recent_posts']) && $settings['number_recent_posts'] > 1)
	{
		require_once($sourcedir . '/Recent.php');

		if (($context['latest_posts'] = cache_get_data('boardindex-latest_posts:' . md5($user_info['query_wanna_see_board'] . $user_info['language']), 180)) == null)
		{
			$context['latest_posts'] = getLastPosts($settings['number_recent_posts']);
			cache_put_data('boardindex-latest_posts:' . md5($user_info['query_wanna_see_board'] . $user_info['language']), $context['latest_posts'], 180);
		}
		// We have to clean up the cached data a bit.
		else
		{
			foreach ($context['latest_posts'] as $k => $post)
			{
				$context['latest_posts'][$k]['time'] = timeformat($post['raw_timestamp']);
				$context['latest_posts'][$k]['timestamp'] = forum_time(true, $post['raw_timestamp']);
			}
		}
	}

	$settings['display_recent_bar'] = !empty($settings['number_recent_posts']) ? $settings['number_recent_posts'] : 0;
	$settings['show_member_bar'] &= allowedTo('view_mlist');
	$context['show_stats'] = allowedTo('view_stats') && !empty($modSettings['trackStats']);
	$context['show_member_list'] = allowedTo('view_mlist');
	$context['show_who'] = allowedTo('who_view') && !empty($modSettings['who_enabled']);

	// Set some permission related settings.
	$context['show_login_bar'] = $user_info['is_guest'] && empty($modSettings['enableVBStyleLogin']);
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