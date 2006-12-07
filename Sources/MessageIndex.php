<?php
/**********************************************************************************
* MessageIndex.php                                                                *
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

/*	This file is what shows the listing of topics in a board.  It's just one
	function, but don't under estimate it ;).

	void MessageIndex()
		// !!!

	void QuickModeration()
		// !!!

*/

// Show the list of topics in this board, along with any child boards.
function MessageIndex()
{
	global $txt, $scripturl, $board, $db_prefix, $modSettings;
	global $context, $options, $settings, $board_info, $user_info, $smfFunc;

	if (WIRELESS)
		$context['sub_template'] = WIRELESS_PROTOCOL . '_messageindex';
	else
		loadTemplate('MessageIndex');

	$context['name'] = $board_info['name'];
	$context['description'] = $board_info['description'];
	// How many topics do we have in total?
	$board_info['total_topics'] = allowedTo('approve_posts') ? $board_info['num_topics'] + $board_info['num_unapproved_topics'] : $board_info['num_topics'];

	// View all the topics, or just a few?
	$maxindex = isset($_REQUEST['all']) && !empty($modSettings['enableAllMessages']) ? $board_info['total_topics'] : $modSettings['defaultMaxTopics'];

	// Make sure the starting place makes sense and construct the page index.
	if (isset($_REQUEST['sort']))
		$context['page_index'] = constructPageIndex($scripturl . '?board=' . $board . '.%d;sort=' . $_REQUEST['sort'] . (isset($_REQUEST['desc']) ? ';desc' : ''), $_REQUEST['start'], $board_info['total_topics'], $maxindex, true);
	else
		$context['page_index'] = constructPageIndex($scripturl . '?board=' . $board . '.%d', $_REQUEST['start'], $board_info['total_topics'], $maxindex, true);
	$context['start'] = &$_REQUEST['start'];

	$context['links'] = array(
		'first' => $_REQUEST['start'] >= $modSettings['defaultMaxTopics'] ? $scripturl . '?board=' . $board . '.0' : '',
		'prev' => $_REQUEST['start'] >= $modSettings['defaultMaxTopics'] ? $scripturl . '?board=' . $board . '.' . ($_REQUEST['start'] - $modSettings['defaultMaxTopics']) : '',
		'next' => $_REQUEST['start'] + $modSettings['defaultMaxTopics'] < $board_info['total_topics'] ? $scripturl . '?board=' . $board . '.' . ($_REQUEST['start'] + $modSettings['defaultMaxTopics']) : '',
		'last' => $_REQUEST['start'] + $modSettings['defaultMaxTopics'] < $board_info['total_topics'] ? $scripturl . '?board=' . $board . '.' . (floor(($board_info['total_topics'] - 1) / $modSettings['defaultMaxTopics']) * $modSettings['defaultMaxTopics']) : '',
		'up' => $board_info['parent'] == 0 ? $scripturl . '?' : $scripturl . '?board=' . $board_info['parent'] . '.0'
	);

	$context['page_info'] = array(
		'current_page' => $_REQUEST['start'] / $modSettings['defaultMaxTopics'] + 1,
		'num_pages' => floor(($board_info['total_topics'] - 1) / $modSettings['defaultMaxTopics']) + 1
	);

	if (isset($_REQUEST['all']) && !empty($modSettings['enableAllMessages']) && $maxindex > $modSettings['enableAllMessages'])
	{
		$maxindex = $modSettings['enableAllMessages'];
		$_REQUEST['start'] = 0;
	}

	// Build a list of the board's moderators.
	$context['moderators'] = &$board_info['moderators'];
	$context['link_moderators'] = array();
	if (!empty($board_info['moderators']))
	{
		foreach ($board_info['moderators'] as $mod)
			$context['link_moderators'][] ='<a href="' . $scripturl . '?action=profile;u=' . $mod['id'] . '" title="' . $txt['board_moderator'] . '">' . $mod['name'] . '</a>';

		$context['linktree'][count($context['linktree']) - 1]['extra_after'] = ' (' . (count($context['link_moderators']) == 1 ? $txt['moderator'] : $txt['moderators']) . ': ' . implode(', ', $context['link_moderators']) . ')';
	}

	// Mark current and parent boards as seen.
	if (!$user_info['is_guest'])
	{
		// We can't know they read it if we allow prefetches.
		if (isset($_SERVER['HTTP_X_MOZ']) && $_SERVER['HTTP_X_MOZ'] == 'prefetch')
		{
			ob_end_clean();
			header('HTTP/1.1 403 Prefetch Forbidden');
			die;
		}

		$smfFunc['db_insert']('replace',
			"{$db_prefix}log_boards",
			array('id_msg', 'id_member', 'id_board'),
			array($modSettings['maxMsgID'], $user_info['id'], $board),
			array('id_member', 'id_board'));

		if (!empty($board_info['parent_boards']))
		{
			$smfFunc['db_query']('', "
				UPDATE {$db_prefix}log_boards
				SET id_msg = $modSettings[maxMsgID]
				WHERE id_member = $user_info[id]
					AND id_board IN (" . implode(',', array_keys($board_info['parent_boards'])) . ")", __FILE__, __LINE__);

			// We've seen all these boards now!
			foreach ($board_info['parent_boards'] as $k => $dummy)
				if (isset($_SESSION['topicseen_cache'][$k]))
					unset($_SESSION['topicseen_cache'][$k]);
		}

		if (isset($_SESSION['topicseen_cache'][$board]))
			unset($_SESSION['topicseen_cache'][$board]);

		$request = $smfFunc['db_query']('', "
			SELECT sent
			FROM {$db_prefix}log_notify
			WHERE id_board = $board
				AND id_member = $user_info[id]
			LIMIT 1", __FILE__, __LINE__);
		$context['is_marked_notify'] = $smfFunc['db_num_rows']($request) != 0;
		if ($context['is_marked_notify'])
		{
			list ($sent) = $smfFunc['db_fetch_row']($request);
			if (!empty($sent))
			{
				$smfFunc['db_query']('', "
					UPDATE {$db_prefix}log_notify
					SET sent = 0
					WHERE id_board = $board
						AND id_member = $user_info[id]", __FILE__, __LINE__);
			}
		}
		$smfFunc['db_free_result']($request);
	}
	else
		$context['is_marked_notify'] = false;

	// 'Print' the header and board info.
	$context['page_title'] = strip_tags($board_info['name']);

	// Set the variables up for the template.
	$context['can_mark_notify'] = allowedTo('mark_notify') && !$user_info['is_guest'];
	$context['can_post_new'] = allowedTo('post_new') || allowedTo('post_unapproved_topics');
	$context['can_post_poll'] = $modSettings['pollMode'] == '1' && allowedTo('poll_post');
	$context['can_moderate_forum'] = allowedTo('moderate_forum');
	$context['can_approve_posts'] = allowedTo('approve_posts');

	// Aren't children wonderful things?
	$result = $smfFunc['db_query']('messageindex_fetch_boards', "
		SELECT
			b.id_board, b.name, b.description, b.num_topics, b.num_posts, b.unapproved_topics,
			b.unapproved_posts, m.poster_name, m.poster_time, m.subject, m.id_msg, m.id_topic,
			IFNULL(mem.real_name, m.poster_name) AS real_name, " . (!$user_info['is_guest'] ? "
			(IFNULL(lb.id_msg, 0) >= b.id_msg_updated) AS isRead," : "1 AS isRead,") . "
			IFNULL(mem.id_member, 0) AS id_member, IFNULL(mem2.id_member, 0) AS ID_MODERATOR,
			mem2.real_name AS modRealName
		FROM {$db_prefix}boards AS b
			LEFT JOIN {$db_prefix}messages AS m ON (m.id_msg = b.id_last_msg)
			LEFT JOIN {$db_prefix}members AS mem ON (mem.id_member = m.id_member)" . (!$user_info['is_guest'] ? "
			LEFT JOIN {$db_prefix}log_boards AS lb ON (lb.id_board = b.id_board AND lb.id_member = $user_info[id])" : '') . "
			LEFT JOIN {$db_prefix}moderators AS mods ON (mods.id_board = b.id_board)
			LEFT JOIN {$db_prefix}members AS mem2 ON (mem2.id_member = mods.id_member)
		WHERE b.id_parent = $board
			AND $user_info[query_see_board]", __FILE__, __LINE__);
	if ($smfFunc['db_num_rows']($result) != 0)
	{
		$theboards = array();
		while ($row_board = $smfFunc['db_fetch_assoc']($result))
		{
			$ignoreThisBoard = in_array($row_board['id_board'], $user_info['ignoreboards']);
			$row_board['isRead'] = !empty($row_board['isRead']) || $ignoreThisBoard ? '1' : '0';
			if (!isset($context['boards'][$row_board['id_board']]))
			{
				$theboards[] = $row_board['id_board'];

				// Make sure the subject isn't too long.
				censorText($row_board['subject']);
				$short_subject = shorten_subject($row_board['subject'], 24);

				$context['boards'][$row_board['id_board']] = array(
					'id' => $row_board['id_board'],
					'last_post' => array(
						'id' => $row_board['id_msg'],
						'time' => $row_board['poster_time'] > 0 ? timeformat($row_board['poster_time']) : $txt[470],
						'timestamp' => forum_time(true, $row_board['poster_time']),
						'subject' => $short_subject,
						'member' => array(
							'id' => $row_board['id_member'],
							'username' => $row_board['poster_name'] != '' ? $row_board['poster_name'] : $txt[470],
							'name' => $row_board['real_name'],
							'href' => !empty($row_board['id_member']) ? $scripturl . '?action=profile;u=' . $row_board['id_member'] : '',
							'link' => $row_board['poster_name'] != '' ? (!empty($row_board['id_member']) ? '<a href="' . $scripturl . '?action=profile;u=' . $row_board['id_member'] . '">' . $row_board['real_name'] . '</a>' : $row_board['real_name']) : $txt[470],
						),
						'start' => 'new',
						'topic' => $row_board['id_topic'],
						'href' => $row_board['subject'] != '' ? $scripturl . '?topic=' . $row_board['id_topic'] . '.new' . (empty($row_board['isRead']) ? ';boardseen' : '') . '#new' : '',
						'link' => $row_board['subject'] != '' ? '<a href="' . $scripturl . '?topic=' . $row_board['id_topic'] . '.new' . (empty($row_board['isRead']) ? ';boardseen' : '') . '#new" title="' . $row_board['subject'] . '">' . $short_subject . '</a>' : $txt[470]
					),
					'new' => empty($row_board['isRead']) && $row_board['poster_name'] != '',
					'name' => $row_board['name'],
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
					'link' => '<a href="' . $scripturl . '?board=' . $row_board['id_board'] . '.0">' . $row_board['name'] . '</a>'
				);
			}
			if (!empty($row_board['ID_MODERATOR']))
			{
				$context['boards'][$row_board['id_board']]['moderators'][$row_board['ID_MODERATOR']] = array(
					'id' => $row_board['ID_MODERATOR'],
					'name' => $row_board['modRealName'],
					'href' => $scripturl . '?action=profile;u=' . $row_board['ID_MODERATOR'],
					'link' => '<a href="' . $scripturl . '?action=profile;u=' . $row_board['ID_MODERATOR'] . '" title="' . $txt['board_moderator'] . '">' . $row_board['modRealName'] . '</a>'
				);
				$context['boards'][$row_board['id_board']]['link_moderators'][] = '<a href="' . $scripturl . '?action=profile;u=' . $row_board['ID_MODERATOR'] . '" title="' . $txt['board_moderator'] . '">' . $row_board['modRealName'] . '</a>';
			}
		}
		$smfFunc['db_free_result']($result);

		// Load up the child boards.
		$result = $smfFunc['db_query']('', "
			SELECT
				b.id_board, b.id_parent, b.name, b.description, b.num_topics, b.num_posts,
				m.poster_name, IFNULL(m.poster_time, 0) AS poster_time, m.subject, m.id_msg, m.id_topic,
				IFNULL(mem.real_name, m.poster_name) AS real_name, id_parent, b.unapproved_posts, b.unapproved_topics,
				" . ($user_info['is_guest'] ? '1' : '(IFNULL(lb.id_msg, 0) >= b.id_msg_updated)') . " AS isRead,
				IFNULL(mem.id_member, 0) AS id_member
			FROM {$db_prefix}boards AS b
				LEFT JOIN {$db_prefix}messages AS m ON (m.id_msg = b.id_last_msg)
				LEFT JOIN {$db_prefix}members AS mem ON (mem.id_member = m.id_member)" . (!$user_info['is_guest'] ? "
				LEFT JOIN {$db_prefix}log_boards AS lb ON (lb.id_board = b.id_board AND lb.id_member = $user_info[id])" : '') . "
			WHERE " . (empty($modSettings['countChildPosts']) ? "b.id_parent IN (" . implode(',', $theboards) . ")" : "child_level > 0") . "
				AND $user_info[query_see_board]", __FILE__, __LINE__);
		$parent_map = array();
		while ($row = $smfFunc['db_fetch_assoc']($result))
		{
			// We've got a child of a child, then... possibly.
			if (!in_array($row['id_parent'], $theboards))
			{
				if (!isset($parent_map[$row['id_parent']]))
					continue;

				$parent_map[$row['id_parent']][0]['posts'] += $row['num_posts'];
				$parent_map[$row['id_parent']][0]['topics'] += $row['num_topics'];
				$parent_map[$row['id_parent']][1]['posts'] += $row['num_posts'];
				$parent_map[$row['id_parent']][1]['topics'] += $row['num_topics'];
				$parent_map[$row['id_board']] = $parent_map[$row['id_parent']];

				continue;
			}

			if ($context['boards'][$row['id_parent']]['last_post']['timestamp'] < forum_time(true, $row['poster_time']))
			{
				// Make sure the subject isn't too long.
				censorText($row['subject']);
				$short_subject = shorten_subject($row['subject'], 24);

				$context['boards'][$row['id_parent']]['last_post'] = array(
					'id' => $row['id_msg'],
					'time' => $row['poster_time'] > 0 ? timeformat($row['poster_time']) : $txt[470],
					'timestamp' => forum_time(true, $row['poster_time']),
					'subject' => $short_subject,
					'member' => array(
						'username' => $row['poster_name'] != '' ? $row['poster_name'] : $txt[470],
						'name' => $row['real_name'],
						'id' => $row['id_member'],
						'href' => !empty($row['id_member']) ? $scripturl . '?action=profile;u=' . $row['id_member'] : '',
						'link' => $row['poster_name'] != '' ? (!empty($row['id_member']) ? '<a href="' . $scripturl . '?action=profile;u=' . $row['id_member'] . '">' . $row['real_name'] . '</a>' : $row['real_name']) : $txt[470],
					),
					'start' => 'new',
					'topic' => $row['id_topic'],
					'href' => $scripturl . '?topic=' . $row['id_topic'] . '.new' . (empty($row['isRead']) ? ';boardseen' : '') . '#new'
				);
				$context['boards'][$row['id_parent']]['last_post']['link'] = $row['subject'] != '' ? '<a href="' . $context['boards'][$row['id_parent']]['last_post']['href'] . '" title="' . $row['subject'] . '">' . $short_subject . '</a>' : $txt[470];
			}
			$context['boards'][$row['id_parent']]['children'][$row['id_board']] = array(
				'id' => $row['id_board'],
				'name' => $row['name'],
				'description' => $row['description'],
				'new' => empty($row['isRead']) && $row['poster_name'] != '',
				'topics' => $row['num_topics'],
				'posts' => $row['num_posts'],
				'unapproved_topics' => $row['unapproved_topics'],
				'unapproved_posts' => $row['unapproved_posts'] - $row['unapproved_topics'],
				'can_approve_posts' => !empty($user_info['mod_cache']['ap']) && ($user_info['mod_cache']['ap'] == array(0) || in_array($row['id_board'], $user_info['mod_cache']['ap'])),
				'href' => $scripturl . '?board=' . $row['id_board'] . '.0',
				'link' => '<a href="' . $scripturl . '?board=' . $row['id_board'] . '.0">' . $row['name'] . '</a>'
			);
			$context['boards'][$row['id_parent']]['link_children'][] = '<a href="' . $scripturl . '?board=' . $row['id_board'] . '.0">' . $row['name'] . '</a>';
			$context['boards'][$row['id_parent']]['children_new'] |= empty($row['isRead']) && $row['poster_name'] != '';

			if (!empty($modSettings['countChildPosts']))
			{
				$context['boards'][$row['id_parent']]['posts'] += $row['num_posts'];
				$context['boards'][$row['id_parent']]['topics'] += $row['num_topics'];

				$parent_map[$row['id_board']] = array(&$context['boards'][$row['id_parent']], &$context['boards'][$row['id_parent']]['children'][$row['id_board']]);
			}
		}
	}
	$smfFunc['db_free_result']($result);

	// Nosey, nosey - who's viewing this topic?
	if (!empty($settings['display_who_viewing']))
	{
		$context['view_members'] = array();
		$context['view_members_list'] = array();
		$context['view_num_hidden'] = 0;

		$request = $smfFunc['db_query']('', "
			SELECT
				lo.id_member, lo.log_time, mem.real_name, mem.member_name, mem.show_online,
				mg.online_color, mg.id_group, mg.group_name
			FROM {$db_prefix}log_online AS lo
				LEFT JOIN {$db_prefix}members AS mem ON (mem.id_member = lo.id_member)
				LEFT JOIN {$db_prefix}membergroups AS mg ON (mg.id_group = CASE WHEN mem.id_group = 0 THEN mem.id_post_group ELSE mem.id_group END)
			WHERE INSTR(lo.url, 's:5:\"board\";i:$board;') OR lo.session = '" . ($user_info['is_guest'] ? 'ip' . $user_info['ip'] : session_id()) . "'", __FILE__, __LINE__);
		while ($row = $smfFunc['db_fetch_assoc']($request))
		{
			if (empty($row['id_member']))
				continue;

			if (!empty($row['online_color']))
				$link = '<a href="' . $scripturl . '?action=profile;u=' . $row['id_member'] . '" style="color: ' . $row['online_color'] . ';">' . $row['real_name'] . '</a>';
			else
				$link = '<a href="' . $scripturl . '?action=profile;u=' . $row['id_member'] . '">' . $row['real_name'] . '</a>';

			$is_buddy = in_array($row['id_member'], $user_info['buddies']);
			if ($is_buddy)
				$link = '<b>' . $link . '</b>';

			if (!empty($row['show_online']) || allowedTo('moderate_forum'))
				$context['view_members_list'][$row['log_time'] . $row['member_name']] = empty($row['show_online']) ? '<i>' . $link . '</i>' : $link;
			$context['view_members'][$row['log_time'] . $row['member_name']] = array(
				'id' => $row['id_member'],
				'username' => $row['member_name'],
				'name' => $row['real_name'],
				'group' => $row['id_group'],
				'href' => $scripturl . '?action=profile;u=' . $row['id_member'],
				'link' => $link,
				'is_buddy' => $is_buddy,
				'hidden' => empty($row['show_online']),
			);

			if (empty($row['show_online']))
				$context['view_num_hidden']++;
		}
		$context['view_num_guests'] = $smfFunc['db_num_rows']($request) - count($context['view_members']);
		$smfFunc['db_free_result']($request);

		// Put them in "last clicked" order.
		krsort($context['view_members_list']);
		krsort($context['view_members']);
	}

	// Default sort methods.
	$sort_methods = array(
		'subject' => 'mf.subject',
		'starter' => 'IFNULL(memf.real_name, mf.poster_name)',
		'last_poster' => 'IFNULL(meml.real_name, ml.poster_name)',
		'replies' => 't.num_replies',
		'views' => 't.num_views',
		'first_post' => 't.id_topic',
		'last_post' => 't.id_last_msg'
	);

	// They didn't pick one, default to by last post descending.
	if (!isset($_REQUEST['sort']) || !isset($sort_methods[$_REQUEST['sort']]))
	{
		$context['sort_by'] = 'last_post';
		$_REQUEST['sort'] = 'id_last_msg';
		$ascending = isset($_REQUEST['asc']);
	}
	// Otherwise default to ascending.
	else
	{
		$context['sort_by'] = $_REQUEST['sort'];
		$_REQUEST['sort'] = $sort_methods[$_REQUEST['sort']];
		$ascending = !isset($_REQUEST['desc']);
	}

	$context['sort_direction'] = $ascending ? 'up' : 'down';

	// Calculate the fastest way to get the topics.
	$start = $_REQUEST['start'];
	if ($start > ($board_info['total_topics']  - 1) / 2)
	{
		$ascending = !$ascending;
		$fake_ascending = true;
		$maxindex = $board_info['total_topics'] < $start + $maxindex + 1 ? $board_info['total_topics'] - $start : $maxindex;
		$start = $board_info['total_topics'] < $start + $maxindex + 1 ? 0 : $board_info['total_topics'] - $start - $maxindex;
	}
	else
		$fake_ascending = false;

	// Setup the default topic icons...
	$stable_icons = array('xx', 'thumbup', 'thumbdown', 'exclamation', 'question', 'lamp', 'smiley', 'angry', 'cheesy', 'grin', 'sad', 'wink', 'moved', 'recycled', 'wireless');
	$context['icon_sources'] = array();
	foreach ($stable_icons as $icon)
		$context['icon_sources'][$icon] = 'images_url';

	$topic_ids = array();
	$context['topics'] = array();

	// Sequential pages are often not optimized, so we add an additional query.
	$pre_query = $start > 0;
	if ($pre_query)
	{
		$request = $smfFunc['db_query']('', "
			SELECT t.id_topic
			FROM {$db_prefix}topics AS t" . ($context['sort_by'] === 'last_poster' ? "
				INNER JOIN {$db_prefix}messages AS ml ON (ml.id_msg = t.id_last_msg)" : (in_array($context['sort_by'], array('starter', 'subject')) ? "
				INNER JOIN {$db_prefix}messages AS mf ON (mf.id_msg = t.id_first_msg)" : '')) . ($context['sort_by'] === 'starter' ? "
				LEFT JOIN {$db_prefix}members AS memf ON (memf.id_member = mf.id_member)" : '') . ($context['sort_by'] === 'last_poster' ? "
				LEFT JOIN {$db_prefix}members AS meml ON (meml.id_member = ml.id_member)" : '') . "
			WHERE t.id_board = $board
				" . ($context['can_approve_posts'] ? '' : ' AND t.approved = 1') . "
			ORDER BY " . (!empty($modSettings['enableStickyTopics']) ? 'is_sticky' . ($fake_ascending ? '' : ' DESC') . ', ' : '') . $_REQUEST['sort'] . ($ascending ? '' : ' DESC') . "
			LIMIT $start, $maxindex", __FILE__, __LINE__);
		$topic_ids = array();
		while ($row = $smfFunc['db_fetch_assoc']($request))
			$topic_ids[] = $row['id_topic'];
	}

	// Grab the appropriate topic information...
	if (!$pre_query || !empty($topic_ids))
	{
		$result = $smfFunc['db_query']('main_topics_query', "
			SELECT
				t.id_topic, t.num_replies, t.locked, t.num_views, t.is_sticky, t.id_poll,
				" . ($user_info['is_guest'] ? '0' : 'IFNULL(lt.id_msg, IFNULL(lmr.id_msg, -1)) + 1') . " AS new_from,
				t.id_last_msg, t.approved, t.unapproved_posts, ml.poster_time AS last_poster_time,
				ml.id_msg_modified, ml.subject AS last_subject, ml.icon AS last_icon,
				ml.poster_name AS last_member_name, ml.id_member AS last_id_member,
				IFNULL(meml.real_name, ml.poster_name) AS last_display_name, t.id_first_msg,
				mf.poster_time AS first_poster_time, mf.subject AS first_subject, mf.icon AS first_icon,
				mf.poster_name AS first_member_name, mf.id_member AS first_id_member,
				IFNULL(memf.real_name, mf.poster_name) AS first_display_name, SUBSTRING(ml.body, 0, 384) AS last_body,
				SUBSTRING(mf.body, 0, 384) AS first_body, ml.smileys_enabled AS last_smileys, mf.smileys_enabled AS first_smileys
			FROM {$db_prefix}topics AS t
				INNER JOIN {$db_prefix}messages AS ml ON (ml.id_msg = t.id_last_msg)
				INNER JOIN {$db_prefix}messages AS mf ON (mf.id_msg = t.id_first_msg)
				LEFT JOIN {$db_prefix}members AS meml ON (meml.id_member = ml.id_member)
				LEFT JOIN {$db_prefix}members AS memf ON (memf.id_member = mf.id_member)" . ($user_info['is_guest'] ? '' : "
				LEFT JOIN {$db_prefix}log_topics AS lt ON (lt.id_topic = t.id_topic AND lt.id_member = $user_info[id])
				LEFT JOIN {$db_prefix}log_mark_read AS lmr ON (lmr.id_board = $board AND lmr.id_member = $user_info[id])"). "
			WHERE " . ($pre_query ? 't.id_topic IN (' . implode(', ', $topic_ids) . ')' : "t.id_board = $board") . "
				" . ($context['can_approve_posts'] ? '' : ' AND t.approved = 1') . "
			ORDER BY " . ($pre_query ? "FIND_IN_SET(t.id_topic, '" . implode(',', $topic_ids) . "')" : (!empty($modSettings['enableStickyTopics']) ? 'is_sticky' . ($fake_ascending ? '' : ' DESC') . ', ' : '') . $_REQUEST['sort'] . ($ascending ? '' : ' DESC')) . "
			LIMIT " . ($pre_query ? '' : "$start, ") . "$maxindex", __FILE__, __LINE__);

		// Begin 'printing' the message index for current board.
		while ($row = $smfFunc['db_fetch_assoc']($result))
		{
			if ($row['id_poll'] > 0 && $modSettings['pollMode'] == '0')
				continue;

			if (!$pre_query)
				$topic_ids[] = $row['id_topic'];

			// Limit them to 128 characters - do this FIRST because it's a lot of wasted censoring otherwise.
			$row['first_body'] = strip_tags(strtr(parse_bbc($row['first_body'], $row['first_smileys'], $row['id_first_msg']), array('<br />' => '&#10;')));
			if ($smfFunc['strlen']($row['first_body']) > 128)
				$row['first_body'] = $smfFunc['substr']($row['first_body'], 0, 128) . '...';
			$row['last_body'] = strip_tags(strtr(parse_bbc($row['last_body'], $row['last_smileys'], $row['id_last_msg']), array('<br />' => '&#10;')));
			if ($smfFunc['strlen']($row['last_body']) > 128)
				$row['last_body'] = $smfFunc['substr']($row['last_body'], 0, 128) . '...';

			// Censor the subject and message preview.
			censorText($row['first_subject']);
			censorText($row['first_body']);

			// Don't censor them twice!
			if ($row['id_first_msg'] == $row['id_last_msg'])
			{
				$row['last_subject'] = $row['first_subject'];
				$row['last_body'] = $row['first_body'];
			}
			else
			{
				censorText($row['last_subject']);
				censorText($row['last_body']);
			}

			// Decide how many pages the topic should have.
			$topic_length = $row['num_replies'] + 1;
			if ($topic_length > $modSettings['defaultMaxMessages'])
			{
				$tmppages = array();
				$tmpa = 1;
				for ($tmpb = 0; $tmpb < $topic_length; $tmpb += $modSettings['defaultMaxMessages'])
				{
					$tmppages[] = '<a href="' . $scripturl . '?topic=' . $row['id_topic'] . '.' . $tmpb . '">' . $tmpa . '</a>';
					$tmpa++;
				}
				// Show links to all the pages?
				if (count($tmppages) <= 5)
					$pages = '&#171; ' . implode(' ', $tmppages);
				// Or skip a few?
				else
					$pages = '&#171; ' . $tmppages[0] . ' ' . $tmppages[1] . ' ... ' . $tmppages[count($tmppages) - 2] . ' ' . $tmppages[count($tmppages) - 1];

				if (!empty($modSettings['enableAllMessages']) && $topic_length < $modSettings['enableAllMessages'])
					$pages .= ' &nbsp;<a href="' . $scripturl . '?topic=' . $row['id_topic'] . '.0;all">' . $txt['all'] . '</a>';
				$pages .= ' &#187;';
			}
			else
				$pages = '';

			// We need to check the topic icons exist...
			if (empty($modSettings['messageIconChecks_disable']))
			{
				if (!isset($context['icon_sources'][$row['first_icon']]))
					$context['icon_sources'][$row['first_icon']] = file_exists($settings['theme_dir'] . '/images/post/' . $row['first_icon'] . '.gif') ? 'images_url' : 'default_images_url';
				if (!isset($context['icon_sources'][$row['last_icon']]))
					$context['icon_sources'][$row['last_icon']] = file_exists($settings['theme_dir'] . '/images/post/' . $row['last_icon'] . '.gif') ? 'images_url' : 'default_images_url';
			}
			else
			{
				if (!isset($context['icon_sources'][$row['first_icon']]))
					$context['icon_sources'][$row['first_icon']] = 'images_url';
				if (!isset($context['icon_sources'][$row['last_icon']]))
					$context['icon_sources'][$row['last_icon']] = 'images_url';
			}

			// 'Print' the topic info.
			$context['topics'][$row['id_topic']] = array(
				'id' => $row['id_topic'],
				'first_post' => array(
					'id' => $row['id_first_msg'],
					'member' => array(
						'username' => $row['first_member_name'],
						'name' => $row['first_display_name'],
						'id' => $row['first_id_member'],
						'href' => !empty($row['first_id_member']) ? $scripturl . '?action=profile;u=' . $row['first_id_member'] : '',
						'link' => !empty($row['first_id_member']) ? '<a href="' . $scripturl . '?action=profile;u=' . $row['first_id_member'] . '" title="' . $txt['profile_of'] . ' ' . $row['first_display_name'] . '">' . $row['first_display_name'] . '</a>' : $row['first_display_name']
					),
					'time' => timeformat($row['first_poster_time']),
					'timestamp' => forum_time(true, $row['first_poster_time']),
					'subject' => $row['first_subject'],
					'preview' => $row['first_body'],
					'icon' => $row['first_icon'],
					'icon_url' => $settings[$context['icon_sources'][$row['first_icon']]] . '/post/' . $row['first_icon'] . '.gif',
					'href' => $scripturl . '?topic=' . $row['id_topic'] . '.0',
					'link' => '<a href="' . $scripturl . '?topic=' . $row['id_topic'] . '.0">' . $row['first_subject'] . '</a>'
				),
				'last_post' => array(
					'id' => $row['id_last_msg'],
					'member' => array(
						'username' => $row['last_member_name'],
						'name' => $row['last_display_name'],
						'id' => $row['last_id_member'],
						'href' => !empty($row['last_id_member']) ? $scripturl . '?action=profile;u=' . $row['last_id_member'] : '',
						'link' => !empty($row['last_id_member']) ? '<a href="' . $scripturl . '?action=profile;u=' . $row['last_id_member'] . '">' . $row['last_display_name'] . '</a>' : $row['last_display_name']
					),
					'time' => timeformat($row['last_poster_time']),
					'timestamp' => forum_time(true, $row['last_poster_time']),
					'subject' => $row['last_subject'],
					'preview' => $row['last_body'],
					'icon' => $row['last_icon'],
					'icon_url' => $settings[$context['icon_sources'][$row['last_icon']]] . '/post/' . $row['last_icon'] . '.gif',
					'href' => $scripturl . '?topic=' . $row['id_topic'] . ($row['num_replies'] == 0 ? '.0' : '.msg' . $row['id_last_msg']) . '#new',
					'link' => '<a href="' . $scripturl . '?topic=' . $row['id_topic'] . ($row['num_replies'] == 0 ? '.0' : '.msg' . $row['id_last_msg']) . '#new">' . $row['last_subject'] . '</a>'
				),
				'is_sticky' => !empty($modSettings['enableStickyTopics']) && !empty($row['is_sticky']),
				'is_locked' => !empty($row['locked']),
				'is_poll' => $modSettings['pollMode'] == '1' && $row['id_poll'] > 0,
				'is_hot' => $row['num_replies'] >= $modSettings['hotTopicPosts'],
				'is_very_hot' => $row['num_replies'] >= $modSettings['hotTopicVeryPosts'],
				'is_posted_in' => false,
				'icon' => $row['first_icon'],
				'icon_url' => $settings[$context['icon_sources'][$row['first_icon']]] . '/post/' . $row['first_icon'] . '.gif',
				'subject' => $row['first_subject'],
				'new' => $row['new_from'] <= $row['id_msg_modified'],
				'new_from' => $row['new_from'],
				'newtime' => $row['new_from'],
				'new_href' => $scripturl . '?topic=' . $row['id_topic'] . '.msg' . $row['new_from'] . '#new',
				'pages' => $pages,
				'replies' => $row['num_replies'],
				'views' => $row['num_views'],
				'approved' => $row['approved'],
				'unapproved_posts' => $row['unapproved_posts'],
			);

			determineTopicClass($context['topics'][$row['id_topic']]);
		}
		$smfFunc['db_free_result']($result);

		// Fix the sequence of topics if they were retrieved in the wrong order. (for speed reasons...)
		if ($fake_ascending)
			$context['topics'] = array_reverse($context['topics'], true);

		if (!empty($modSettings['enableParticipation']) && !$user_info['is_guest'] && !empty($topic_ids))
		{
			$result = $smfFunc['db_query']('', "
				SELECT id_topic
				FROM {$db_prefix}messages
				WHERE id_topic IN (" . implode(', ', $topic_ids) . ")
					AND id_member = $user_info[id]
				GROUP BY id_topic
				LIMIT " . count($topic_ids), __FILE__, __LINE__);
			while ($row = $smfFunc['db_fetch_assoc']($result))
			{
				$context['topics'][$row['id_topic']]['is_posted_in'] = true;
				$context['topics'][$row['id_topic']]['class'] = 'my_' . $context['topics'][$row['id_topic']]['class'];
			}
			$smfFunc['db_free_result']($result);
		}
	}

	$context['jump_to'] = array(
		'label' => addslashes(un_htmlspecialchars($txt['jump_to'])),
		'board_name' => un_htmlspecialchars($board_info['name']),
		'child_level' => $board_info['child_level'],
	);

	// Is Quick Moderation active?
	if (!empty($options['display_quick_mod']))
	{
		$context['can_lock'] = allowedTo('lock_any');
		$context['can_sticky'] = allowedTo('make_sticky') && !empty($modSettings['enableStickyTopics']);
		$context['can_move'] = allowedTo('move_any');
		$context['can_remove'] = allowedTo('remove_any');
		$context['can_merge'] = allowedTo('merge_any');
		// Ignore approving own topics as it's unlikely to come up...
		$context['can_approve'] = allowedTo('approve_posts');

		// Set permissions for all the topics.
		foreach ($context['topics'] as $t => $topic)
		{
			$started = $topic['first_post']['member']['id'] == $user_info['id'];
			$context['topics'][$t]['quick_mod'] = array(
				'lock' => allowedTo('lock_any') || ($started && allowedTo('lock_own')),
				'sticky' => allowedTo('make_sticky') && !empty($modSettings['enableStickyTopics']),
				'move' => allowedTo('move_any') || ($started && allowedTo('move_own')),
				'modify' => allowedTo('modify_any') || ($started && allowedTo('modify_own')),
				'remove' => allowedTo('remove_any') || ($started && allowedTo('remove_own')),
				'approve' => $context['can_approve'] && $topic['unapproved_posts']
			);
			$context['can_lock'] |= ($started && allowedTo('lock_own'));
			$context['can_move'] |= ($started && allowedTo('move_own'));
			$context['can_remove'] |= ($started && allowedTo('remove_own'));
		}

		// Find the boards/cateogories they can move their topic to.
		if ($options['display_quick_mod'] == 1 && $context['can_move'] && !empty($context['topics']))
		{
			$request = $smfFunc['db_query']('', "
				SELECT c.name AS cat_name, c.id_cat, b.id_board, b.name AS board_name, b.child_level
				FROM {$db_prefix}boards AS b
					LEFT JOIN {$db_prefix}categories AS c ON (c.id_cat = b.id_cat)
				WHERE b.id_board != $board
					AND $user_info[query_see_board]", __FILE__, __LINE__);

			// You can only see just this one board?
			if ($smfFunc['db_num_rows']($request) === 0)
				$context['can_move'] = false;
			else
			{
				$context['move_to_boards'] = array();
				while ($row = $smfFunc['db_fetch_assoc']($request))
				{
					if (!isset($context['move_to_boards'][$row['id_cat']]))
						$context['move_to_boards'][$row['id_cat']] = array(
							'id' => $row['id_cat'],
							'name' => $row['cat_name'],
							'boards' => array(),
						);

					$context['move_to_boards'][$row['id_cat']]['boards'][] = array(
						'id' => $row['id_board'],
						'name' => $row['board_name'],
						'child_level' => $row['child_level'],
						'selected' => !empty($_SESSION['move_to_topic']) && $_SESSION['move_to_topic'] == $row['id_board'],
					);
				}
			}
			$smfFunc['db_free_result']($request);
		}
	}

	// If there are children, but no topics and no ability to post topics...
	$context['no_topic_listing'] = !empty($context['boards']) && empty($context['topics']) && !$context['can_post_new'];
}

// Allows for moderation from the message index.
function QuickModeration()
{
	global $db_prefix, $sourcedir, $board, $user_info, $modSettings, $sourcedir, $smfFunc;

	// Check the session = get or post.
	checkSession('request');

	if (isset($_SESSION['topicseen_cache']))
		$_SESSION['topicseen_cache'] = array();

	// This is going to be needed to send off the notifications and for updateLastMessages().
	require_once($sourcedir . '/Subs-Post.php');

	// Remember the last board they moved things to.
	if (isset($_REQUEST['move_to']))
		$_SESSION['move_to_topic'] = $_REQUEST['move_to'];

	// Only a few possible actions.
	$possibleActions = array('markread');

	if (!empty($board))
	{
		$boards_can = array(
			'make_sticky' => allowedTo('make_sticky') ? array($board) : array(),
			'move_any' => allowedTo('move_any') ? array($board) : array(),
			'move_own' => allowedTo('move_own') ? array($board) : array(),
			'remove_any' => allowedTo('remove_any') ? array($board) : array(),
			'remove_own' => allowedTo('remove_own') ? array($board) : array(),
			'lock_any' => allowedTo('lock_any') ? array($board) : array(),
			'lock_own' => allowedTo('lock_own') ? array($board) : array(),
			'merge_any' => allowedTo('merge_any') ? array($board) : array(),
			'approve_posts' => allowedTo('approve_posts') ? array($board) : array(),
		);

		$redirect_url = 'board=' . $board . '.' . $_REQUEST['start'];
	}
	else
	{
		// !!! Ugly.  There's no getting around this, is there?
		// !!! Maybe just do this on the actions people want to use?
		$boards_can = array(
			'make_sticky' => boardsAllowedTo('make_sticky'),
			'move_any' => boardsAllowedTo('move_any'),
			'move_own' => boardsAllowedTo('move_own'),
			'remove_any' => boardsAllowedTo('remove_any'),
			'remove_own' => boardsAllowedTo('remove_own'),
			'lock_any' => boardsAllowedTo('lock_any'),
			'lock_own' => boardsAllowedTo('lock_own'),
			'merge_any' => boardsAllowedTo('merge_any'),
			'approve_posts' => boardsAllowedTo('approve_posts'),
		);

		$redirect_url = isset($_POST['redirect_url']) ? $_POST['redirect_url'] : (isset($_SESSION['old_url']) ? $_SESSION['old_url'] : '');
	}

	if (!empty($boards_can['make_sticky']) && !empty($modSettings['enableStickyTopics']))
		$possibleActions[] = 'sticky';
	if (!empty($boards_can['move_any']) || !empty($boards_can['move_own']))
		$possibleActions[] = 'move';
	if (!empty($boards_can['remove_any']) || !empty($boards_can['remove_own']))
		$possibleActions[] = 'remove';
	if (!empty($boards_can['lock_any']) || !empty($boards_can['lock_own']))
		$possibleActions[] = 'lock';
	if (!empty($boards_can['merge_any']))
		$possibleActions[] = 'merge';
	if (!empty($boards_can['approve_posts']))
		$possibleActions[] = 'approve';

	// Two methods: $_REQUEST['actions'] (id_topic => action), and $_REQUEST['topics'] and $_REQUEST['qaction'].
	// (if action is 'move', $_REQUEST['move_to'] or $_REQUEST['move_tos'][$topic] is used.)
	if (!empty($_REQUEST['topics']))
	{
		// If the action isn't valid, just quit now.
		if (empty($_REQUEST['qaction']) || !in_array($_REQUEST['qaction'], $possibleActions))
			redirectexit($redirect_url);

		// Merge requires all topics as one parameter and can be done at once.
		if ($_REQUEST['qaction'] == 'merge')
		{
			// Merge requires at least two topics.
			if (empty($_REQUEST['topics']) || count($_REQUEST['topics']) < 2)
				redirectexit($redirect_url);

			require_once($sourcedir . '/SplitTopics.php');
			return MergeExecute($_REQUEST['topics']);
		}

		// Just convert to the other method, to make it easier.
		foreach ($_REQUEST['topics'] as $topic)
			$_REQUEST['actions'][(int) $topic] = $_REQUEST['qaction'];
	}

	// Weird... how'd you get here?
	if (empty($_REQUEST['actions']))
		redirectexit($redirect_url);

	// Validate each action.
	$temp = array();
	foreach ($_REQUEST['actions'] as $topic => $action)
	{
		if (in_array($action, $possibleActions))
			$temp[(int) $topic] = $action;
	}
	$_REQUEST['actions'] = $temp;

	if (!empty($_REQUEST['actions']))
	{
		// Find all topics...
		$request = $smfFunc['db_query']('', "
			SELECT id_topic, id_member_started, id_board, locked, approved, unapproved_posts
			FROM {$db_prefix}topics
			WHERE id_topic IN (" . implode(', ', array_keys($_REQUEST['actions'])) . ")
			LIMIT " . count($_REQUEST['actions']), __FILE__, __LINE__);
		while ($row = $smfFunc['db_fetch_assoc']($request))
		{
			if (!empty($board))
			{
				if ($row['id_board'] != $board || (!$row['approved'] && !allowedTo('approve_posts')))
					unset($_REQUEST['actions'][$row['id_topic']]);
			}
			else
			{
				// Don't allow them to act on unapproved posts they can't see...
				if (!$row['approved'] && !in_array(0, $boards_can['approve_posts']) && !in_array($row['id_board'], $boards_can['approve_posts']))
					unset($_REQUEST['actions'][$row['id_topic']]);
				// Goodness, this is fun.  We need to validate the action.
				elseif ($_REQUEST['actions'][$row['id_topic']] == 'sticky' && !in_array(0, $boards_can['make_sticky']) && !in_array($row['id_board'], $boards_can['make_sticky']))
					unset($_REQUEST['actions'][$row['id_topic']]);
				elseif ($_REQUEST['actions'][$row['id_topic']] == 'move' && !in_array(0, $boards_can['move_any']) && !in_array($row['id_board'], $boards_can['move_any']) && ($row['id_member_started'] != $user_info['id'] || (!in_array(0, $boards_can['move_own']) && !in_array($row['id_board'], $boards_can['move_own']))))
					unset($_REQUEST['actions'][$row['id_topic']]);
				elseif ($_REQUEST['actions'][$row['id_topic']] == 'remove' && !in_array(0, $boards_can['remove_any']) && !in_array($row['id_board'], $boards_can['remove_any']) && ($row['id_member_started'] != $user_info['id'] || (!in_array(0, $boards_can['remove_own']) && !in_array($row['id_board'], $boards_can['remove_own']))))
					unset($_REQUEST['actions'][$row['id_topic']]);
				elseif ($_REQUEST['actions'][$row['id_topic']] == 'lock' && !in_array(0, $boards_can['lock_any']) && !in_array($row['id_board'], $boards_can['lock_any']) && ($row['id_member_started'] != $user_info['id'] || $locked == 1 || (!in_array(0, $boards_can['lock_own']) && !in_array($row['id_board'], $boards_can['lock_own']))))
					unset($_REQUEST['actions'][$row['id_topic']]);
				// If the topic is approved then you need permission to approve the posts within.
				elseif ($_REQUEST['actions'][$row['id_topic']] == 'approve' && (!$row['unapproved_posts'] || (!in_array(0, $boards_can['approve_posts']) && !in_array($row['id_board'], $boards_can['approve_posts']))))
					unset($_REQUEST['actions'][$row['id_topic']]);
			}
		}
		$smfFunc['db_free_result']($request);
	}

	$stickyCache = array();
	$moveCache = array(0 => array(), 1 => array());
	$removeCache = array();
	$lockCache = array();
	$markCache = array();
	$approveCache = array();

	// Separate the actions.
	foreach ($_REQUEST['actions'] as $topic => $action)
	{
		$topic = (int) $topic;

		if ($action == 'markread')
			$markCache[] = $topic;
		elseif ($action == 'sticky')
			$stickyCache[] = $topic;
		elseif ($action == 'move')
		{
			// $moveCache[0] is the topic, $moveCache[1] is the board to move to.
			$moveCache[1][$topic] = (int) (isset($_REQUEST['move_tos'][$topic]) ? $_REQUEST['move_tos'][$topic] : $_REQUEST['move_to']);

			if (empty($moveCache[1][$topic]))
				continue;

			$moveCache[0][] = $topic;
		}
		elseif ($action == 'remove')
			$removeCache[] = $topic;
		elseif ($action == 'lock')
			$lockCache[] = $topic;
		elseif ($action == 'approve')
			$approveCache[] = $topic;
	}

	if (empty($board))
		$affectedBoards = array();
	else
		$affectedBoards = array($board => array(0, 0));

	// Do all the stickies...
	if (!empty($stickyCache))
	{
		$smfFunc['db_query']('', "
			UPDATE {$db_prefix}topics
			SET is_sticky = CASE WHEN is_sticky = 1 THEN 0 ELSE 1 END
			WHERE id_topic IN (" . implode(', ', $stickyCache) . ")", __FILE__, __LINE__);
			
		// Get the board IDs
		$request = $smfFunc['db_query']('', "
			SELECT id_topic, id_board
			FROM {$db_prefix}topics
			WHERE id_topic IN (" . implode(', ', $stickyCache) . ")
			LIMIT " . count($stickyCache), __FILE__, __LINE__);
		$stickyCacheBoards = array();
		while ($row = $smfFunc['db_fetch_assoc']($request))
			$stickyCacheBoards[$row['id_topic']] = $row['id_board'];
		$smfFunc['db_free_result']($request);
	}

	// Move sucka! (this is, by the by, probably the most complicated part....)
	if (!empty($moveCache[0]))
	{
		// I know - I just KNOW you're trying to beat the system.  Too bad for you... we CHECK :P.
		$request = $smfFunc['db_query']('', "
			SELECT id_topic, id_board
			FROM {$db_prefix}topics
			WHERE id_topic IN (" . implode(', ', $moveCache[0]) . ")" . (!empty($board) && !allowedTo('move_any') ? "
				AND id_member_started = $user_info[id]" : '') . "
			LIMIT " . count($moveCache[0]), __FILE__, __LINE__);
		$moveTos = array();
		$moveCache2 = array();
		while ($row = $smfFunc['db_fetch_assoc']($request))
		{
			$to = $moveCache[1][$row['id_topic']];

			if (empty($to))
				continue;

			if (!isset($moveTos[$to]))
				$moveTos[$to] = array();

			$moveTos[$to][] = $row['id_topic'];

			// For reporting...
			$moveCache2[] = array($row['id_topic'], $row['id_board'], $to);
		}
		$smfFunc['db_free_result']($request);

		$moveCache = $moveCache2;

		require_once($sourcedir . '/MoveTopic.php');

		// Do the actual moves...
		foreach ($moveTos as $to => $topics)
			moveTopics($topics, $to);
	}

	// Now delete the topics...
	if (!empty($removeCache))
	{
		// They can only delete their own topics. (we wouldn't be here if they couldn't do that..)
		if (!empty($board) && !allowedTo('remove_any'))
		{
			$result = $smfFunc['db_query']('', "
				SELECT id_topic, id_board
				FROM {$db_prefix}topics
				WHERE id_topic IN (" . implode(', ', $removeCache) . ")
					AND id_member_started = $user_info[id]
				LIMIT " . count($removeCache), __FILE__, __LINE__);
			$removeCache = array();
			while ($row = $smfFunc['db_fetch_assoc']($result))
				$removeCache[] = $row;
			$smfFunc['db_free_result']($result);
		}

		// Maybe *none* were their own topics.
		if (!empty($removeCache))
		{
			// Gotta send the notifications *first*!
			foreach ($removeCache as $topic)
			{
				logAction('remove', array('topic' => $topic['id_topic'], 'board' => $topic['id_board']));
				sendNotifications($topic, 'remove');
			}

			require_once($sourcedir . '/RemoveTopic.php');
			removeTopics($removeCache);
		}
	}

	// And (almost) lastly, lock the topics...
	if (!empty($lockCache))
	{
		$lockStatus = array();

		// Gotta make sure they CAN lock/unlock these topics...
		if (!empty($board) && !allowedTo('lock_any'))
		{
			// Make sure they started the topic AND it isn't already locked by someone with higher priv's.
			$result = $smfFunc['db_query']('', "
				SELECT id_topic, locked, id_board
				FROM {$db_prefix}topics
				WHERE id_topic IN (" . implode(', ', $lockCache) . ")
					AND id_member_started = $user_info[id]
					AND locked IN (2, 0)
				LIMIT " . count($lockCache), __FILE__, __LINE__);
			$lockCache = array();
			$lockCacheBoards = array();
			while ($row = $smfFunc['db_fetch_assoc']($result))
			{
				$lockCache[] = $row['id_topic'];
				$lockCacheBoards[$row['id_topic']] = $row['id_board'];
				$lockStatus[$row['id_topic']] = empty($row['locked']);
			}
			$smfFunc['db_free_result']($result);
		}
		else
		{
			$result = $smfFunc['db_query']('', "
				SELECT id_topic, locked, id_board
				FROM {$db_prefix}topics
				WHERE id_topic IN (" . implode(', ', $lockCache) . ")
				LIMIT " . count($lockCache), __FILE__, __LINE__);
			$lockCacheBoards = array();
			while ($row = $smfFunc['db_fetch_assoc']($result))
			{
				$lockStatus[$row['id_topic']] = empty($row['locked']);
				$lockCacheBoards[$row['id_topic']] = $row['id_board'];
			}
			$smfFunc['db_free_result']($result);
		}

		// It could just be that *none* were their own topics...
		if (!empty($lockCache))
		{
			// Alternate the locked value.
			$smfFunc['db_query']('', "
				UPDATE {$db_prefix}topics
				SET locked = CASE WHEN locked = 0 THEN " . (allowedTo('lock_any') ? '1' : '2') . " ELSE 0 END
				WHERE id_topic IN (" . implode(', ', $lockCache) . ")", __FILE__, __LINE__);
		}
	}

	// Topics/posts to approve, eh?
	if (!empty($approveCache))
	{
		// This function returns the outcome...
		//!!! Add logging!
		$approveCache = approveTopics($approveCache);
	}

	if (!empty($markCache))
	{
		$markArray = array();
		foreach ($markCache as $topic)
			$markArray[] = array($modSettings['maxMsgID'], $user_info['id'], $topic);

		$smfFunc['db_query']('replace',
			"{$db_prefix}log_topics",
			array('id_msg', 'id_member', 'id_topic'),
			$markArray,
			array('id_member', 'id_topic')
		);
	}

	foreach ($moveCache as $topic)
	{
		// Didn't actually move anything!
		if (!isset($topic[0]))
			break;

		logAction('move', array('topic' => $topic[0], 'board_from' => $topic[1], 'board_to' => $topic[2]));
		sendNotifications($topic[0], 'move');
	}
	foreach ($lockCache as $topic)
	{
		logAction('lock', array('topic' => $topic, 'board' => $lockCacheBoards[$topic]));
		sendNotifications($topic, $lockStatus ? 'lock' : 'unlock');
	}
	foreach ($stickyCache as $topic)
	{
		logAction('sticky', array('topic' => $topic, 'board' => $stickyCacheBoards[$topic]));
		sendNotifications($topic, 'sticky');
	}

	updateStats('topic');
	updateStats('message');
	updateStats('calendar');

	if (!empty($affectedBoards))
		updateLastMessages(array_keys($affectedBoards));

	redirectexit($redirect_url);
}

?>
