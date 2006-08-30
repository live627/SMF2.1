<?php
/******************************************************************************
* MessageIndex.php                                                            *
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
	global $txt, $scripturl, $board, $db_prefix;
	global $modSettings, $ID_MEMBER;
	global $context, $options, $settings, $board_info, $user_info, $func;

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
			$context['link_moderators'][] ='<a href="' . $scripturl . '?action=profile;u=' . $mod['id'] . '" title="' . $txt[62] . '">' . $mod['name'] . '</a>';

		$context['linktree'][count($context['linktree']) - 1]['extra_after'] = ' (' . (count($context['link_moderators']) == 1 ? $txt[298] : $txt[299]) . ': ' . implode(', ', $context['link_moderators']) . ')';
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

		db_query("
			REPLACE INTO {$db_prefix}log_boards
				(ID_MSG, ID_MEMBER, ID_BOARD)
			VALUES ($modSettings[maxMsgID], $ID_MEMBER, $board)", __FILE__, __LINE__);
		if (!empty($board_info['parent_boards']))
		{
			db_query("
				UPDATE {$db_prefix}log_boards
				SET ID_MSG = $modSettings[maxMsgID]
				WHERE ID_MEMBER = $ID_MEMBER
					AND ID_BOARD IN (" . implode(',', array_keys($board_info['parent_boards'])) . ")
				LIMIT " . count($board_info['parent_boards']), __FILE__, __LINE__);

			// We've seen all these boards now!
			foreach ($board_info['parent_boards'] as $k => $dummy)
				if (isset($_SESSION['topicseen_cache'][$k]))
					unset($_SESSION['topicseen_cache'][$k]);
		}

		if (isset($_SESSION['topicseen_cache'][$board]))
			unset($_SESSION['topicseen_cache'][$board]);

		$request = db_query("
			SELECT sent
			FROM {$db_prefix}log_notify
			WHERE ID_BOARD = $board
				AND ID_MEMBER = $ID_MEMBER
			LIMIT 1", __FILE__, __LINE__);
		$context['is_marked_notify'] = mysql_num_rows($request) != 0;
		if ($context['is_marked_notify'])
		{
			list ($sent) = mysql_fetch_row($request);
			if (!empty($sent))
			{
				db_query("
					UPDATE {$db_prefix}log_notify
					SET sent = 0
					WHERE ID_BOARD = $board
						AND ID_MEMBER = $ID_MEMBER
					LIMIT 1", __FILE__, __LINE__);
			}
		}
		mysql_free_result($request);
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
	$result = db_query("
		SELECT
			b.ID_BOARD, b.name, b.description, b.numTopics, b.numPosts, b.unapprovedTopics,
			b.unapprovedPosts, m.posterName, m.posterTime, m.subject, m.ID_MSG, m.ID_TOPIC,
			IFNULL(mem.realName, m.posterName) AS realName, " . (!$user_info['is_guest'] ? "
			(IFNULL(lb.ID_MSG, 0) >= b.ID_MSG_UPDATED) AS isRead," : "1 AS isRead,") . "
			IFNULL(mem.ID_MEMBER, 0) AS ID_MEMBER, IFNULL(mem2.ID_MEMBER, 0) AS ID_MODERATOR,
			mem2.realName AS modRealName
		FROM {$db_prefix}boards AS b
			LEFT JOIN {$db_prefix}messages AS m ON (m.ID_MSG = b.ID_LAST_MSG)
			LEFT JOIN {$db_prefix}members AS mem ON (mem.ID_MEMBER = m.ID_MEMBER)" . (!$user_info['is_guest'] ? "
			LEFT JOIN {$db_prefix}log_boards AS lb ON (lb.ID_BOARD = b.ID_BOARD AND lb.ID_MEMBER = $ID_MEMBER)" : '') . "
			LEFT JOIN {$db_prefix}moderators AS mods ON (mods.ID_BOARD = b.ID_BOARD)
			LEFT JOIN {$db_prefix}members AS mem2 ON (mem2.ID_MEMBER = mods.ID_MEMBER)
		WHERE b.ID_PARENT = $board
			AND $user_info[query_see_board]", __FILE__, __LINE__);
	if (mysql_num_rows($result) != 0)
	{
		$theboards = array();
		while ($row_board = mysql_fetch_assoc($result))
		{
			$ignoreThisBoard = in_array($row_board['ID_BOARD'], $user_info['ignoreboards']);
			$row_board['isRead'] = !empty($row_board['isRead']) || $ignoreThisBoard ? '1' : '0';
			if (!isset($context['boards'][$row_board['ID_BOARD']]))
			{
				$theboards[] = $row_board['ID_BOARD'];

				// Make sure the subject isn't too long.
				censorText($row_board['subject']);
				$short_subject = shorten_subject($row_board['subject'], 24);

				$context['boards'][$row_board['ID_BOARD']] = array(
					'id' => $row_board['ID_BOARD'],
					'last_post' => array(
						'id' => $row_board['ID_MSG'],
						'time' => $row_board['posterTime'] > 0 ? timeformat($row_board['posterTime']) : $txt[470],
						'timestamp' => forum_time(true, $row_board['posterTime']),
						'subject' => $short_subject,
						'member' => array(
							'id' => $row_board['ID_MEMBER'],
							'username' => $row_board['posterName'] != '' ? $row_board['posterName'] : $txt[470],
							'name' => $row_board['realName'],
							'href' => !empty($row_board['ID_MEMBER']) ? $scripturl . '?action=profile;u=' . $row_board['ID_MEMBER'] : '',
							'link' => $row_board['posterName'] != '' ? (!empty($row_board['ID_MEMBER']) ? '<a href="' . $scripturl . '?action=profile;u=' . $row_board['ID_MEMBER'] . '">' . $row_board['realName'] . '</a>' : $row_board['realName']) : $txt[470],
						),
						'start' => 'new',
						'topic' => $row_board['ID_TOPIC'],
						'href' => $row_board['subject'] != '' ? $scripturl . '?topic=' . $row_board['ID_TOPIC'] . '.new' . (empty($row_board['isRead']) ? ';boardseen' : '') . '#new' : '',
						'link' => $row_board['subject'] != '' ? '<a href="' . $scripturl . '?topic=' . $row_board['ID_TOPIC'] . '.new' . (empty($row_board['isRead']) ? ';boardseen' : '') . '#new" title="' . $row_board['subject'] . '">' . $short_subject . '</a>' : $txt[470]
					),
					'new' => empty($row_board['isRead']) && $row_board['posterName'] != '',
					'name' => $row_board['name'],
					'description' => $row_board['description'],
					'moderators' => array(),
					'link_moderators' => array(),
					'children' => array(),
					'link_children' => array(),
					'children_new' => false,
					'topics' => $row_board['numTopics'],
					'posts' => $row_board['numPosts'],
					'unapproved_topics' => $row_board['unapprovedTopics'],
					'unapproved_posts' => $row_board['unapprovedPosts'] - $row_board['unapprovedTopics'],
					'can_approve_posts' => !empty($user_info['mod_cache']['ap']) && ($user_info['mod_cache']['ap'] == array(0) || in_array($row_board['ID_BOARD'], $user_info['mod_cache']['ap'])),
					'href' => $scripturl . '?board=' . $row_board['ID_BOARD'] . '.0',
					'link' => '<a href="' . $scripturl . '?board=' . $row_board['ID_BOARD'] . '.0">' . $row_board['name'] . '</a>'
				);
			}
			if (!empty($row_board['ID_MODERATOR']))
			{
				$context['boards'][$row_board['ID_BOARD']]['moderators'][$row_board['ID_MODERATOR']] = array(
					'id' => $row_board['ID_MODERATOR'],
					'name' => $row_board['modRealName'],
					'href' => $scripturl . '?action=profile;u=' . $row_board['ID_MODERATOR'],
					'link' => '<a href="' . $scripturl . '?action=profile;u=' . $row_board['ID_MODERATOR'] . '" title="' . $txt[62] . '">' . $row_board['modRealName'] . '</a>'
				);
				$context['boards'][$row_board['ID_BOARD']]['link_moderators'][] = '<a href="' . $scripturl . '?action=profile;u=' . $row_board['ID_MODERATOR'] . '" title="' . $txt[62] . '">' . $row_board['modRealName'] . '</a>';
			}
		}
		mysql_free_result($result);

		// Load up the child boards.
		$result = db_query("
			SELECT
				b.ID_BOARD, b.ID_PARENT, b.name, b.description, b.numTopics, b.numPosts,
				m.posterName, IFNULL(m.posterTime, 0) AS posterTime, m.subject, m.ID_MSG, m.ID_TOPIC,
				IFNULL(mem.realName, m.posterName) AS realName, ID_PARENT, b.unapprovedPosts, b.unapprovedTopics,
				" . ($user_info['is_guest'] ? '1' : '(IFNULL(lb.ID_MSG, 0) >= b.ID_MSG_UPDATED)') . " AS isRead,
				IFNULL(mem.ID_MEMBER, 0) AS ID_MEMBER
			FROM {$db_prefix}boards AS b
				LEFT JOIN {$db_prefix}messages AS m ON (m.ID_MSG = b.ID_LAST_MSG)
				LEFT JOIN {$db_prefix}members AS mem ON (mem.ID_MEMBER = m.ID_MEMBER)" . (!$user_info['is_guest'] ? "
				LEFT JOIN {$db_prefix}log_boards AS lb ON (lb.ID_BOARD = b.ID_BOARD AND lb.ID_MEMBER = $ID_MEMBER)" : '') . "
			WHERE " . (empty($modSettings['countChildPosts']) ? "b.ID_PARENT IN (" . implode(',', $theboards) . ")" : "childLevel > 0") . "
				AND $user_info[query_see_board]", __FILE__, __LINE__);
		$parent_map = array();
		while ($row = mysql_fetch_assoc($result))
		{
			// We've got a child of a child, then... possibly.
			if (!in_array($row['ID_PARENT'], $theboards))
			{
				if (!isset($parent_map[$row['ID_PARENT']]))
					continue;

				$parent_map[$row['ID_PARENT']][0]['posts'] += $row['numPosts'];
				$parent_map[$row['ID_PARENT']][0]['topics'] += $row['numTopics'];
				$parent_map[$row['ID_PARENT']][1]['posts'] += $row['numPosts'];
				$parent_map[$row['ID_PARENT']][1]['topics'] += $row['numTopics'];
				$parent_map[$row['ID_BOARD']] = $parent_map[$row['ID_PARENT']];

				continue;
			}

			if ($context['boards'][$row['ID_PARENT']]['last_post']['timestamp'] < forum_time(true, $row['posterTime']))
			{
				// Make sure the subject isn't too long.
				censorText($row['subject']);
				$short_subject = shorten_subject($row['subject'], 24);

				$context['boards'][$row['ID_PARENT']]['last_post'] = array(
					'id' => $row['ID_MSG'],
					'time' => $row['posterTime'] > 0 ? timeformat($row['posterTime']) : $txt[470],
					'timestamp' => forum_time(true, $row['posterTime']),
					'subject' => $short_subject,
					'member' => array(
						'username' => $row['posterName'] != '' ? $row['posterName'] : $txt[470],
						'name' => $row['realName'],
						'id' => $row['ID_MEMBER'],
						'href' => !empty($row['ID_MEMBER']) ? $scripturl . '?action=profile;u=' . $row['ID_MEMBER'] : '',
						'link' => $row['posterName'] != '' ? (!empty($row['ID_MEMBER']) ? '<a href="' . $scripturl . '?action=profile;u=' . $row['ID_MEMBER'] . '">' . $row['realName'] . '</a>' : $row['realName']) : $txt[470],
					),
					'start' => 'new',
					'topic' => $row['ID_TOPIC'],
					'href' => $scripturl . '?topic=' . $row['ID_TOPIC'] . '.new' . (empty($row['isRead']) ? ';boardseen' : '') . '#new'
				);
				$context['boards'][$row['ID_PARENT']]['last_post']['link'] = $row['subject'] != '' ? '<a href="' . $context['boards'][$row['ID_PARENT']]['last_post']['href'] . '" title="' . $row['subject'] . '">' . $short_subject . '</a>' : $txt[470];
			}
			$context['boards'][$row['ID_PARENT']]['children'][$row['ID_BOARD']] = array(
				'id' => $row['ID_BOARD'],
				'name' => $row['name'],
				'description' => $row['description'],
				'new' => empty($row['isRead']) && $row['posterName'] != '',
				'topics' => $row['numTopics'],
				'posts' => $row['numPosts'],
				'unapproved_topics' => $row['unapprovedTopics'],
				'unapproved_posts' => $row['unapprovedPosts'] - $row['unapprovedTopics'],
				'can_approve_posts' => !empty($user_info['mod_cache']['ap']) && ($user_info['mod_cache']['ap'] == array(0) || in_array($row['ID_BOARD'], $user_info['mod_cache']['ap'])),
				'href' => $scripturl . '?board=' . $row['ID_BOARD'] . '.0',
				'link' => '<a href="' . $scripturl . '?board=' . $row['ID_BOARD'] . '.0">' . $row['name'] . '</a>'
			);
			$context['boards'][$row['ID_PARENT']]['link_children'][] = '<a href="' . $scripturl . '?board=' . $row['ID_BOARD'] . '.0">' . $row['name'] . '</a>';
			$context['boards'][$row['ID_PARENT']]['children_new'] |= empty($row['isRead']) && $row['posterName'] != '';

			if (!empty($modSettings['countChildPosts']))
			{
				$context['boards'][$row['ID_PARENT']]['posts'] += $row['numPosts'];
				$context['boards'][$row['ID_PARENT']]['topics'] += $row['numTopics'];

				$parent_map[$row['ID_BOARD']] = array(&$context['boards'][$row['ID_PARENT']], &$context['boards'][$row['ID_PARENT']]['children'][$row['ID_BOARD']]);
			}
		}
	}
	mysql_free_result($result);

	// Nosey, nosey - who's viewing this topic?
	if (!empty($settings['display_who_viewing']))
	{
		$context['view_members'] = array();
		$context['view_members_list'] = array();
		$context['view_num_hidden'] = 0;

		$request = db_query("
			SELECT
				lo.ID_MEMBER, lo.logTime, mem.realName, mem.memberName, mem.showOnline,
				mg.onlineColor, mg.ID_GROUP, mg.groupName
			FROM {$db_prefix}log_online AS lo
				LEFT JOIN {$db_prefix}members AS mem ON (mem.ID_MEMBER = lo.ID_MEMBER)
				LEFT JOIN {$db_prefix}membergroups AS mg ON (mg.ID_GROUP = IF(mem.ID_GROUP = 0, mem.ID_POST_GROUP, mem.ID_GROUP))
			WHERE INSTR(lo.url, 's:5:\"board\";i:$board;') OR lo.session = '" . ($user_info['is_guest'] ? 'ip' . $user_info['ip'] : session_id()) . "'", __FILE__, __LINE__);
		while ($row = mysql_fetch_assoc($request))
		{
			if (empty($row['ID_MEMBER']))
				continue;

			if (!empty($row['onlineColor']))
				$link = '<a href="' . $scripturl . '?action=profile;u=' . $row['ID_MEMBER'] . '" style="color: ' . $row['onlineColor'] . ';">' . $row['realName'] . '</a>';
			else
				$link = '<a href="' . $scripturl . '?action=profile;u=' . $row['ID_MEMBER'] . '">' . $row['realName'] . '</a>';

			$is_buddy = in_array($row['ID_MEMBER'], $user_info['buddies']);
			if ($is_buddy)
				$link = '<b>' . $link . '</b>';

			if (!empty($row['showOnline']) || allowedTo('moderate_forum'))
				$context['view_members_list'][$row['logTime'] . $row['memberName']] = empty($row['showOnline']) ? '<i>' . $link . '</i>' : $link;
			$context['view_members'][$row['logTime'] . $row['memberName']] = array(
				'id' => $row['ID_MEMBER'],
				'username' => $row['memberName'],
				'name' => $row['realName'],
				'group' => $row['ID_GROUP'],
				'href' => $scripturl . '?action=profile;u=' . $row['ID_MEMBER'],
				'link' => $link,
				'is_buddy' => $is_buddy,
				'hidden' => empty($row['showOnline']),
			);

			if (empty($row['showOnline']))
				$context['view_num_hidden']++;
		}
		$context['view_num_guests'] = mysql_num_rows($request) - count($context['view_members']);
		mysql_free_result($request);

		// Put them in "last clicked" order.
		krsort($context['view_members_list']);
		krsort($context['view_members']);
	}

	// Default sort methods.
	$sort_methods = array(
		'subject' => 'mf.subject',
		'starter' => 'IFNULL(memf.realName, mf.posterName)',
		'last_poster' => 'IFNULL(meml.realName, ml.posterName)',
		'replies' => 't.numReplies',
		'views' => 't.numViews',
		'first_post' => 't.ID_TOPIC',
		'last_post' => 't.ID_LAST_MSG'
	);

	// They didn't pick one, default to by last post descending.
	if (!isset($_REQUEST['sort']) || !isset($sort_methods[$_REQUEST['sort']]))
	{
		$context['sort_by'] = 'last_post';
		$_REQUEST['sort'] = 'ID_LAST_MSG';
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
		$request = db_query("
			SELECT t.ID_TOPIC
			FROM ({$db_prefix}topics AS t" . ($context['sort_by'] === 'last_poster' ? ", {$db_prefix}messages AS ml" : (in_array($context['sort_by'], array('starter', 'subject')) ? ", {$db_prefix}messages AS mf" : '')) . ')' . ($context['sort_by'] === 'starter' ? "
				LEFT JOIN {$db_prefix}members AS memf ON (memf.ID_MEMBER = mf.ID_MEMBER)" : '') . ($context['sort_by'] === 'last_poster' ? "
				LEFT JOIN {$db_prefix}members AS meml ON (meml.ID_MEMBER = ml.ID_MEMBER)" : '') . "
			WHERE t.ID_BOARD = $board
				" . ($context['can_approve_posts'] ? '' : ' AND t.approved = 1') . "
				" . ($context['sort_by'] === 'last_poster' ? "
				AND ml.ID_MSG = t.ID_LAST_MSG" : (in_array($context['sort_by'], array('starter', 'subject')) ? "
				AND mf.ID_MSG = t.ID_FIRST_MSG" : '')) . "
			ORDER BY " . (!empty($modSettings['enableStickyTopics']) ? 'isSticky' . ($fake_ascending ? '' : ' DESC') . ', ' : '') . $_REQUEST['sort'] . ($ascending ? '' : ' DESC') . "
			LIMIT $start, $maxindex", __FILE__, __LINE__);
		$topic_ids = array();
		while ($row = mysql_fetch_assoc($request))
			$topic_ids[] = $row['ID_TOPIC'];
	}

	// Grab the appropriate topic information...
	if (!$pre_query || !empty($topic_ids))
	{
		$result = db_query("
			SELECT
				t.ID_TOPIC, t.numReplies, t.locked, t.numViews, t.isSticky, t.ID_POLL,
				" . ($user_info['is_guest'] ? '0' : 'IFNULL(lt.ID_MSG, IFNULL(lmr.ID_MSG, -1)) + 1') . " AS new_from,
				t.ID_LAST_MSG, t.approved, t.unapprovedPosts, ml.posterTime AS lastPosterTime,
				ml.ID_MSG_MODIFIED, ml.subject AS lastSubject, ml.icon AS lastIcon,
				ml.posterName AS lastMemberName, ml.ID_MEMBER AS lastID_MEMBER,
				IFNULL(meml.realName, ml.posterName) AS lastDisplayName, t.ID_FIRST_MSG,
				mf.posterTime AS firstPosterTime, mf.subject AS firstSubject, mf.icon AS firstIcon,
				mf.posterName AS firstMemberName, mf.ID_MEMBER AS firstID_MEMBER,
				IFNULL(memf.realName, mf.posterName) AS firstDisplayName,LEFT(ml.body, 384) AS lastBody,
				LEFT(mf.body, 384) AS firstBody, ml.smileysEnabled AS lastSmileys, mf.smileysEnabled AS firstSmileys
			FROM ({$db_prefix}topics AS t, {$db_prefix}messages AS ml, {$db_prefix}messages AS mf)
				LEFT JOIN {$db_prefix}members AS meml ON (meml.ID_MEMBER = ml.ID_MEMBER)
				LEFT JOIN {$db_prefix}members AS memf ON (memf.ID_MEMBER = mf.ID_MEMBER)" . ($user_info['is_guest'] ? '' : "
				LEFT JOIN {$db_prefix}log_topics AS lt ON (lt.ID_TOPIC = t.ID_TOPIC AND lt.ID_MEMBER = $ID_MEMBER)
				LEFT JOIN {$db_prefix}log_mark_read AS lmr ON (lmr.ID_BOARD = $board AND lmr.ID_MEMBER = $ID_MEMBER)"). "
			WHERE " . ($pre_query ? 't.ID_TOPIC IN (' . implode(', ', $topic_ids) . ')' : "t.ID_BOARD = $board") . "
				" . ($context['can_approve_posts'] ? '' : ' AND t.approved = 1') . "
				AND ml.ID_MSG = t.ID_LAST_MSG
				AND mf.ID_MSG = t.ID_FIRST_MSG
			ORDER BY " . ($pre_query ? "FIND_IN_SET(t.ID_TOPIC, '" . implode(',', $topic_ids) . "')" : (!empty($modSettings['enableStickyTopics']) ? 'isSticky' . ($fake_ascending ? '' : ' DESC') . ', ' : '') . $_REQUEST['sort'] . ($ascending ? '' : ' DESC')) . "
			LIMIT " . ($pre_query ? '' : "$start, ") . "$maxindex", __FILE__, __LINE__);

		// Begin 'printing' the message index for current board.
		while ($row = mysql_fetch_assoc($result))
		{
			if ($row['ID_POLL'] > 0 && $modSettings['pollMode'] == '0')
				continue;

			if (!$pre_query)
				$topic_ids[] = $row['ID_TOPIC'];

			// Limit them to 128 characters - do this FIRST because it's a lot of wasted censoring otherwise.
			$row['firstBody'] = strip_tags(strtr(parse_bbc($row['firstBody'], $row['firstSmileys'], $row['ID_FIRST_MSG']), array('<br />' => '&#10;')));
			if ($func['strlen']($row['firstBody']) > 128)
				$row['firstBody'] = $func['substr']($row['firstBody'], 0, 128) . '...';
			$row['lastBody'] = strip_tags(strtr(parse_bbc($row['lastBody'], $row['lastSmileys'], $row['ID_LAST_MSG']), array('<br />' => '&#10;')));
			if ($func['strlen']($row['lastBody']) > 128)
				$row['lastBody'] = $func['substr']($row['lastBody'], 0, 128) . '...';

			// Censor the subject and message preview.
			censorText($row['firstSubject']);
			censorText($row['firstBody']);

			// Don't censor them twice!
			if ($row['ID_FIRST_MSG'] == $row['ID_LAST_MSG'])
			{
				$row['lastSubject'] = $row['firstSubject'];
				$row['lastBody'] = $row['firstBody'];
			}
			else
			{
				censorText($row['lastSubject']);
				censorText($row['lastBody']);
			}

			// Decide how many pages the topic should have.
			$topic_length = $row['numReplies'] + 1;
			if ($topic_length > $modSettings['defaultMaxMessages'])
			{
				$tmppages = array();
				$tmpa = 1;
				for ($tmpb = 0; $tmpb < $topic_length; $tmpb += $modSettings['defaultMaxMessages'])
				{
					$tmppages[] = '<a href="' . $scripturl . '?topic=' . $row['ID_TOPIC'] . '.' . $tmpb . '">' . $tmpa . '</a>';
					$tmpa++;
				}
				// Show links to all the pages?
				if (count($tmppages) <= 5)
					$pages = '&#171; ' . implode(' ', $tmppages);
				// Or skip a few?
				else
					$pages = '&#171; ' . $tmppages[0] . ' ' . $tmppages[1] . ' ... ' . $tmppages[count($tmppages) - 2] . ' ' . $tmppages[count($tmppages) - 1];

				if (!empty($modSettings['enableAllMessages']) && $topic_length < $modSettings['enableAllMessages'])
					$pages .= ' &nbsp;<a href="' . $scripturl . '?topic=' . $row['ID_TOPIC'] . '.0;all">' . $txt[190] . '</a>';
				$pages .= ' &#187;';
			}
			else
				$pages = '';

			// We need to check the topic icons exist...
			if (empty($modSettings['messageIconChecks_disable']))
			{
				if (!isset($context['icon_sources'][$row['firstIcon']]))
					$context['icon_sources'][$row['firstIcon']] = file_exists($settings['theme_dir'] . '/images/post/' . $row['firstIcon'] . '.gif') ? 'images_url' : 'default_images_url';
				if (!isset($context['icon_sources'][$row['lastIcon']]))
					$context['icon_sources'][$row['lastIcon']] = file_exists($settings['theme_dir'] . '/images/post/' . $row['lastIcon'] . '.gif') ? 'images_url' : 'default_images_url';
			}
			else
			{
				if (!isset($context['icon_sources'][$row['firstIcon']]))
					$context['icon_sources'][$row['firstIcon']] = 'images_url';
				if (!isset($context['icon_sources'][$row['lastIcon']]))
					$context['icon_sources'][$row['lastIcon']] = 'images_url';
			}

			// 'Print' the topic info.
			$context['topics'][$row['ID_TOPIC']] = array(
				'id' => $row['ID_TOPIC'],
				'first_post' => array(
					'id' => $row['ID_FIRST_MSG'],
					'member' => array(
						'username' => $row['firstMemberName'],
						'name' => $row['firstDisplayName'],
						'id' => $row['firstID_MEMBER'],
						'href' => !empty($row['firstID_MEMBER']) ? $scripturl . '?action=profile;u=' . $row['firstID_MEMBER'] : '',
						'link' => !empty($row['firstID_MEMBER']) ? '<a href="' . $scripturl . '?action=profile;u=' . $row['firstID_MEMBER'] . '" title="' . $txt[92] . ' ' . $row['firstDisplayName'] . '">' . $row['firstDisplayName'] . '</a>' : $row['firstDisplayName']
					),
					'time' => timeformat($row['firstPosterTime']),
					'timestamp' => forum_time(true, $row['firstPosterTime']),
					'subject' => $row['firstSubject'],
					'preview' => $row['firstBody'],
					'icon' => $row['firstIcon'],
					'icon_url' => $settings[$context['icon_sources'][$row['firstIcon']]] . '/post/' . $row['firstIcon'] . '.gif',
					'href' => $scripturl . '?topic=' . $row['ID_TOPIC'] . '.0',
					'link' => '<a href="' . $scripturl . '?topic=' . $row['ID_TOPIC'] . '.0">' . $row['firstSubject'] . '</a>'
				),
				'last_post' => array(
					'id' => $row['ID_LAST_MSG'],
					'member' => array(
						'username' => $row['lastMemberName'],
						'name' => $row['lastDisplayName'],
						'id' => $row['lastID_MEMBER'],
						'href' => !empty($row['lastID_MEMBER']) ? $scripturl . '?action=profile;u=' . $row['lastID_MEMBER'] : '',
						'link' => !empty($row['lastID_MEMBER']) ? '<a href="' . $scripturl . '?action=profile;u=' . $row['lastID_MEMBER'] . '">' . $row['lastDisplayName'] . '</a>' : $row['lastDisplayName']
					),
					'time' => timeformat($row['lastPosterTime']),
					'timestamp' => forum_time(true, $row['lastPosterTime']),
					'subject' => $row['lastSubject'],
					'preview' => $row['lastBody'],
					'icon' => $row['lastIcon'],
					'icon_url' => $settings[$context['icon_sources'][$row['lastIcon']]] . '/post/' . $row['lastIcon'] . '.gif',
					'href' => $scripturl . '?topic=' . $row['ID_TOPIC'] . ($row['numReplies'] == 0 ? '.0' : '.msg' . $row['ID_LAST_MSG']) . '#new',
					'link' => '<a href="' . $scripturl . '?topic=' . $row['ID_TOPIC'] . ($row['numReplies'] == 0 ? '.0' : '.msg' . $row['ID_LAST_MSG']) . '#new">' . $row['lastSubject'] . '</a>'
				),
				'is_sticky' => !empty($modSettings['enableStickyTopics']) && !empty($row['isSticky']),
				'is_locked' => !empty($row['locked']),
				'is_poll' => $modSettings['pollMode'] == '1' && $row['ID_POLL'] > 0,
				'is_hot' => $row['numReplies'] >= $modSettings['hotTopicPosts'],
				'is_very_hot' => $row['numReplies'] >= $modSettings['hotTopicVeryPosts'],
				'is_posted_in' => false,
				'icon' => $row['firstIcon'],
				'icon_url' => $settings[$context['icon_sources'][$row['firstIcon']]] . '/post/' . $row['firstIcon'] . '.gif',
				'subject' => $row['firstSubject'],
				'new' => $row['new_from'] <= $row['ID_MSG_MODIFIED'],
				'new_from' => $row['new_from'],
				'newtime' => $row['new_from'],
				'new_href' => $scripturl . '?topic=' . $row['ID_TOPIC'] . '.msg' . $row['new_from'] . '#new',
				'pages' => $pages,
				'replies' => $row['numReplies'],
				'views' => $row['numViews'],
				'approved' => $row['approved'],
				'unapproved_posts' => $row['unapprovedPosts'],
			);

			determineTopicClass($context['topics'][$row['ID_TOPIC']]);
		}
		mysql_free_result($result);

		// Fix the sequence of topics if they were retrieved in the wrong order. (for speed reasons...)
		if ($fake_ascending)
			$context['topics'] = array_reverse($context['topics'], true);

		if (!empty($modSettings['enableParticipation']) && !$user_info['is_guest'] && !empty($topic_ids))
		{
			$result = db_query("
				SELECT ID_TOPIC
				FROM {$db_prefix}messages
				WHERE ID_TOPIC IN (" . implode(', ', $topic_ids) . ")
					AND ID_MEMBER = $ID_MEMBER
				GROUP BY ID_TOPIC
				LIMIT " . count($topic_ids), __FILE__, __LINE__);
			while ($row = mysql_fetch_assoc($result))
			{
				$context['topics'][$row['ID_TOPIC']]['is_posted_in'] = true;
				$context['topics'][$row['ID_TOPIC']]['class'] = 'my_' . $context['topics'][$row['ID_TOPIC']]['class'];
			}
			mysql_free_result($result);
		}
	}

	$context['jump_to'] = array(
		'label' => addslashes(un_htmlspecialchars($txt[160])),
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
			$started = $topic['first_post']['member']['id'] == $ID_MEMBER;
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
			$request = db_query("
				SELECT c.name AS catName, c.ID_CAT, b.ID_BOARD, b.name AS boardName, b.childLevel
				FROM {$db_prefix}boards AS b
					LEFT JOIN {$db_prefix}categories AS c ON (c.ID_CAT = b.ID_CAT)
				WHERE b.ID_BOARD != $board
					AND $user_info[query_see_board]", __FILE__, __LINE__);

			// You can only see just this one board?
			if (mysql_num_rows($request) === 0)
				$context['can_move'] = false;
			else
			{
				$context['move_to_boards'] = array();
				while ($row = mysql_fetch_assoc($request))
				{
					if (!isset($context['move_to_boards'][$row['ID_CAT']]))
						$context['move_to_boards'][$row['ID_CAT']] = array(
							'id' => $row['ID_CAT'],
							'name' => $row['catName'],
							'boards' => array(),
						);

					$context['move_to_boards'][$row['ID_CAT']]['boards'][] = array(
						'id' => $row['ID_BOARD'],
						'name' => $row['boardName'],
						'child_level' => $row['childLevel'],
						'selected' => !empty($_SESSION['move_to_topic']) && $_SESSION['move_to_topic'] == $row['ID_BOARD'],
					);
				}
			}
			mysql_free_result($request);
		}
	}

	// If there are children, but no topics and no ability to post topics...
	$context['no_topic_listing'] = !empty($context['boards']) && empty($context['topics']) && !$context['can_post_new'];
}

// Allows for moderation from the message index.
function QuickModeration()
{
	global $db_prefix, $sourcedir, $board, $ID_MEMBER, $modSettings, $sourcedir;

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

	// Two methods: $_REQUEST['actions'] (ID_TOPIC => action), and $_REQUEST['topics'] and $_REQUEST['qaction'].
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
		$request = db_query("
			SELECT ID_TOPIC, ID_MEMBER_STARTED, ID_BOARD, locked, approved, unapprovedPosts
			FROM {$db_prefix}topics
			WHERE ID_TOPIC IN (" . implode(', ', array_keys($_REQUEST['actions'])) . ")
			LIMIT " . count($_REQUEST['actions']), __FILE__, __LINE__);
		while ($row = mysql_fetch_assoc($request))
		{
			if (!empty($board))
			{
				if ($row['ID_BOARD'] != $board || (!$row['approved'] && !allowedTo('approve_posts')))
					unset($_REQUEST['actions'][$row['ID_TOPIC']]);
			}
			else
			{
				// Don't allow them to act on unapproved posts they can't see...
				if (!$row['approved'] && !in_array(0, $boards_can['approve_posts']) && !in_array($row['ID_BOARD'], $boards_can['approve_posts']))
					unset($_REQUEST['actions'][$row['ID_TOPIC']]);
				// Goodness, this is fun.  We need to validate the action.
				elseif ($_REQUEST['actions'][$row['ID_TOPIC']] == 'sticky' && !in_array(0, $boards_can['make_sticky']) && !in_array($row['ID_BOARD'], $boards_can['make_sticky']))
					unset($_REQUEST['actions'][$row['ID_TOPIC']]);
				elseif ($_REQUEST['actions'][$row['ID_TOPIC']] == 'move' && !in_array(0, $boards_can['move_any']) && !in_array($row['ID_BOARD'], $boards_can['move_any']) && ($row['ID_MEMBER_STARTED'] != $ID_MEMBER || (!in_array(0, $boards_can['move_own']) && !in_array($row['ID_BOARD'], $boards_can['move_own']))))
					unset($_REQUEST['actions'][$row['ID_TOPIC']]);
				elseif ($_REQUEST['actions'][$row['ID_TOPIC']] == 'remove' && !in_array(0, $boards_can['remove_any']) && !in_array($row['ID_BOARD'], $boards_can['remove_any']) && ($row['ID_MEMBER_STARTED'] != $ID_MEMBER || (!in_array(0, $boards_can['remove_own']) && !in_array($row['ID_BOARD'], $boards_can['remove_own']))))
					unset($_REQUEST['actions'][$row['ID_TOPIC']]);
				elseif ($_REQUEST['actions'][$row['ID_TOPIC']] == 'lock' && !in_array(0, $boards_can['lock_any']) && !in_array($row['ID_BOARD'], $boards_can['lock_any']) && ($row['ID_MEMBER_STARTED'] != $ID_MEMBER || $locked == 1 || (!in_array(0, $boards_can['lock_own']) && !in_array($row['ID_BOARD'], $boards_can['lock_own']))))
					unset($_REQUEST['actions'][$row['ID_TOPIC']]);
				// If the topic is approved then you need permission to approve the posts within.
				elseif ($_REQUEST['actions'][$row['ID_TOPIC']] == 'approve' && (!$row['unapprovedPosts'] || (!in_array(0, $boards_can['approve_posts']) && !in_array($row['ID_BOARD'], $boards_can['approve_posts']))))
					unset($_REQUEST['actions'][$row['ID_TOPIC']]);
			}
		}
		mysql_free_result($request);
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
		db_query("
			UPDATE {$db_prefix}topics
			SET isSticky = IF(isSticky = 1, 0, 1)
			WHERE ID_TOPIC IN (" . implode(', ', $stickyCache) . ")
			LIMIT " . count($stickyCache), __FILE__, __LINE__);
			
		// Get the board IDs
		$request = db_query("
			SELECT ID_TOPIC, ID_BOARD
			FROM {$db_prefix}topics
			WHERE ID_TOPIC IN (" . implode(', ', $stickyCache) . ")
			LIMIT " . count($stickyCache), __FILE__, __LINE__);
		$stickyCacheBoards = array();
		while ($row = mysql_fetch_assoc($request))
			$stickyCacheBoards[$row['ID_TOPIC']] = $row['ID_BOARD'];
		mysql_free_result($request);
	}

	// Move sucka! (this is, by the by, probably the most complicated part....)
	if (!empty($moveCache[0]))
	{
		// I know - I just KNOW you're trying to beat the system.  Too bad for you... we CHECK :P.
		$request = db_query("
			SELECT ID_TOPIC, ID_BOARD
			FROM {$db_prefix}topics
			WHERE ID_TOPIC IN (" . implode(', ', $moveCache[0]) . ")" . (!empty($board) && !allowedTo('move_any') ? "
				AND ID_MEMBER_STARTED = $ID_MEMBER" : '') . "
			LIMIT " . count($moveCache[0]), __FILE__, __LINE__);
		$moveTos = array();
		$moveCache2 = array();
		while ($row = mysql_fetch_assoc($request))
		{
			$to = $moveCache[1][$row['ID_TOPIC']];

			if (empty($to))
				continue;

			if (!isset($moveTos[$to]))
				$moveTos[$to] = array();

			$moveTos[$to][] = $row['ID_TOPIC'];

			// For reporting...
			$moveCache2[] = array($row['ID_TOPIC'], $row['ID_BOARD'], $to);
		}
		mysql_free_result($request);

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
			$result = db_query("
				SELECT ID_TOPIC, ID_BOARD
				FROM {$db_prefix}topics
				WHERE ID_TOPIC IN (" . implode(', ', $removeCache) . ")
					AND ID_MEMBER_STARTED = $ID_MEMBER
				LIMIT " . count($removeCache), __FILE__, __LINE__);
			$removeCache = array();
			while ($row = mysql_fetch_assoc($result))
				$removeCache[] = $row;
			mysql_free_result($result);
		}

		// Maybe *none* were their own topics.
		if (!empty($removeCache))
		{
			// Gotta send the notifications *first*!
			foreach ($removeCache as $topic)
			{
				logAction('remove', array('topic' => $topic['ID_TOPIC'], 'board' => $topic['ID_BOARD']));
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
			$result = db_query("
				SELECT ID_TOPIC, locked, ID_BOARD
				FROM {$db_prefix}topics
				WHERE ID_TOPIC IN (" . implode(', ', $lockCache) . ")
					AND ID_MEMBER_STARTED = $ID_MEMBER
					AND locked IN (2, 0)
				LIMIT " . count($lockCache), __FILE__, __LINE__);
			$lockCache = array();
			$lockCacheBoards = array();
			while ($row = mysql_fetch_assoc($result))
			{
				$lockCache[] = $row['ID_TOPIC'];
				$lockCacheBoards[$row['ID_TOPIC']] = $row['ID_BOARD'];
				$lockStatus[$row['ID_TOPIC']] = empty($row['locked']);
			}
			mysql_free_result($result);
		}
		else
		{
			$result = db_query("
				SELECT ID_TOPIC, locked, ID_BOARD
				FROM {$db_prefix}topics
				WHERE ID_TOPIC IN (" . implode(', ', $lockCache) . ")
				LIMIT " . count($lockCache), __FILE__, __LINE__);
			$lockCacheBoards = array();
			while ($row = mysql_fetch_assoc($result))
			{
				$lockStatus[$row['ID_TOPIC']] = empty($row['locked']);
				$lockCacheBoards[$row['ID_TOPIC']] = $row['ID_BOARD'];
			}
			mysql_free_result($result);
		}

		// It could just be that *none* were their own topics...
		if (!empty($lockCache))
		{
			// Alternate the locked value.
			db_query("
				UPDATE {$db_prefix}topics
				SET locked = IF(locked = 0, " . (allowedTo('lock_any') ? '1' : '2') . ", 0)
				WHERE ID_TOPIC IN (" . implode(', ', $lockCache) . ")
				LIMIT " . count($lockCache), __FILE__, __LINE__);
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
		$setString = '';
		foreach ($markCache as $topic)
			$setString .= "
				($modSettings[maxMsgID], $ID_MEMBER, $topic),";

		db_query("
			REPLACE INTO {$db_prefix}log_topics
				(ID_MSG, ID_MEMBER, ID_TOPIC)
			VALUES" . substr($setString, 0, -1), __FILE__, __LINE__);
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