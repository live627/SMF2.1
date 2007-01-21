<?php
/**********************************************************************************
* Subs-MembersOnline.php                                                          *
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

/*	This file currently only holds the function for showing a list of online
	users used by the board index and SSI. In the future it'll also contain
	functions used by the Who's online page.

	array getMembersOnlineStats(array membersOnlineOptions)
		- retrieve a list and several other statistics of the users currently
		  online on the forum.
		- used by the board index and SSI.
		- also returns the membergroups of the users that are currently online.
		- (optionally) hides members that chose to hide their online presense.
*/

// Retrieve a list and several other statistics of the users currently online.
function getMembersOnlineStats($membersOnlineOptions)
{
	global $db_prefix, $smfFunc, $context, $scripturl, $user_info;

	// The list can be sorted in several ways.
	$allowed_sort_options = array(
		'log_time',
		'real_name',
		'show_online',
		'online_color',
		'group_name',
	);
	// Default the sorting method to 'most recent online members first'.
	if (!isset($memberOnlineOptions['sort']))
	{
		$memberOnlineOptions['sort'] = 'log_time';
		$memberOnlineOptions['reverse_sort'] = true;
	}
	
	// Not allowed sort method? Bang! Error!
	elseif (!in_array($memberOnlineOptions['sort'], $allowed_sort_options)) 
		trigger_error('Sort method for getMembersOnlineStats() function is not allowed', E_USER_NOTICE);

	// Initialize the array that'll be returned later on.
	$memberOnlineStats = array(
		'users_online' => array(),
		'list_users_online' => array(),
		'online_groups' => array(),
		'num_guests' => 0,
		'num_buddies' => 0,
		'num_users_hidden' => 0,
		'num_users_online' => 0,
	);

	// Load the users online right now.
	$request = $smfFunc['db_query']('', "
		SELECT
			lo.id_member, lo.log_time, mem.real_name, mem.member_name, mem.show_online,
			mg.online_color, mg.id_group, mg.group_name
		FROM {$db_prefix}log_online AS lo
			LEFT JOIN {$db_prefix}members AS mem ON (mem.id_member = lo.id_member)
			LEFT JOIN {$db_prefix}membergroups AS mg ON (mg.id_group = CASE WHEN mem.id_group = 0 THEN mem.id_post_group ELSE mem.id_group END)", __FILE__, __LINE__);


	while ($row = $smfFunc['db_fetch_assoc']($request))
	{
		if (empty($row['real_name']))
		{
			// Guests are only nice for statistics.
			$memberOnlineStats['num_guests']++;
			continue;
		}

		elseif (empty($row['show_online']) && empty($membersOnlineOptions['show_hidden']))
		{
			// Just increase the stats and don't add this hidden user to any list.
			$memberOnlineStats['num_users_hidden']++;
			continue;
		}

		// Some basic color coding...
		if (!empty($row['online_color']))
			$link = '<a href="' . $scripturl . '?action=profile;u=' . $row['id_member'] . '" style="color: ' . $row['online_color'] . ';">' . $row['real_name'] . '</a>';
		else
			$link = '<a href="' . $scripturl . '?action=profile;u=' . $row['id_member'] . '">' . $row['real_name'] . '</a>';

		// Buddies get counted and highlighted.
		$is_buddy = in_array($row['id_member'], $user_info['buddies']);
		if ($is_buddy)
		{
			$memberOnlineStats['num_buddies']++;
			$link = '<b>' . $link . '</b>';
		}

		// A lot of useful information for each member.
		$memberOnlineStats['users_online'][$row[$memberOnlineOptions['sort']] . $row['member_name']] = array(
			'id' => $row['id_member'],
			'username' => $row['member_name'],
			'name' => $row['real_name'],
			'group' => $row['id_group'],
			'href' => $scripturl . '?action=profile;u=' . $row['id_member'],
			'link' => $link,
			'is_buddy' => $is_buddy,
			'hidden' => empty($row['show_online']),
			'is_last' => false,
		);

		// This is the compact version, simply implode it to show.
		$memberOnlineStats['list_users_online'][$row[$memberOnlineOptions['sort']] . $row['member_name']] = empty($row['show_online']) ? '<i>' . $link . '</i>' : $link;

		// Store all distinct (primary) membergroups that are shown.
		if (!isset($memberOnlineStats['online_groups'][$row['id_group']]))
			$memberOnlineStats['online_groups'][$row['id_group']] = array(
				'id' => $row['id_group'],
				'name' => $row['group_name'],
				'color' => $row['online_color']
			);
	}
	$smfFunc['db_free_result']($request);

	// Time to sort the list a bit.
	if (!empty($memberOnlineStats['users_online']))
	{
		// Determine the sort direction.
		$sortFunction = empty($memberOnlineOptions['reverse_sort']) ? 'ksort' : 'krsort';

		// Sort the two lists.
		$sortFunction($memberOnlineStats['users_online']);
		$sortFunction($memberOnlineStats['list_users_online']);

		// Mark the last list item as 'is_last'.
		$userKeys = array_keys($memberOnlineStats['users_online']);
		$memberOnlineStats['users_online'][end($userKeys)]['is_last'] = true;
	}

	// Also sort the membergroups.
	ksort($memberOnlineStats['online_groups']);

	// Hidden and non-hidden members make up all online members.
	$memberOnlineStats['num_users_online'] = count($memberOnlineStats['users_online']) + $memberOnlineStats['num_users_hidden'];

	return $memberOnlineStats;
}

// Check if the number of users online is a record and store it.
function trackStatsUsersOnline($total_users_online)
{
	global $modSettings, $db_prefix, $smfFunc;

	$settingsToUpdate = array();

	// More members on now than ever were?  Update it!
	if (!isset($modSettings['mostOnline']) || $total_users_online >= $modSettings['mostOnline'])
		$settingsToUpdate = array(
			'mostOnline' => $total_users_online,
			'mostDate' => time()
		);

	$date = strftime('%Y-%m-%d', forum_time(false));

	// No entry exists for today yet?
	if (!isset($modSettings['mostOnlineUpdated']) || $modSettings['mostOnlineUpdated'] != $date)
	{
		$request = $smfFunc['db_query']('', "
			SELECT most_on
			FROM {$db_prefix}log_activity
			WHERE date = '$date'
			LIMIT 1", __FILE__, __LINE__);

		// The log_activity hasn't got an entry for today?
		if ($smfFunc['db_num_rows']($request) === 0)
		{
			$smfFunc['db_insert']('ignore',
				"{$db_prefix}log_activity",
				array('date', 'most_on'),
				array('\'' . $date . '\'', $total_users_online),
				array('date'), __FILE__, __LINE__
			);
		}
		// There's an entry in log_activity on today...
		else
		{
			list ($modSettings['mostOnlineToday']) = $smfFunc['db_fetch_row']($request);

			if ($total_users_online > $modSettings['mostOnlineToday'])
				trackStats(array('most_on' => $total_users_online));

			$total_users_online = max($total_users_online, $modSettings['mostOnlineToday']);
		}
		$smfFunc['db_free_result']($request);

		$settingsToUpdate['mostOnlineUpdated'] = $date;
		$settingsToUpdate['mostOnlineToday'] = $total_users_online;
	}

	// Highest number of users online today?
	elseif ($total_users_online > $modSettings['mostOnlineToday'])
	{
		trackStats(array('most_on' => $total_users_online));
		$settingsToUpdate['mostOnlineToday'] = $total_users_online;
	}

	if (!empty($settingsToUpdate))
		updateSettings($settingsToUpdate);
}

?>