<?php
/******************************************************************************
* PostModeration.php                                                          *
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

/*	
	//!!!
*/

// This is a handling function for all things post moderation...
function PostModerationMain()
{
	global $sourcedir;

	//!!! We'll shift these later bud.
	loadLanguage('ModerationCenter');
	loadTemplate('ModerationCenter');

	// Probably need this...
	require_once($sourcedir . '/ModerationCenter.php');

	// Allowed sub-actions, you know the drill by now!
	$subactions = array(
		'approve' => 'ApproveMessage',
		'attachments' => 'UnapprovedAttachments',
		'posts' => 'UnapprovedPosts',
	);

	// Pick something valid...
	if (!isset($_REQUEST['sa']) || !isset($subactions[$_REQUEST['sa']]))
		$_REQUEST['sa'] = 'posts';

	$subactions[$_REQUEST['sa']]();
}

// View all unapproved posts.
function UnapprovedPosts()
{
	global $txt, $scripturl, $context, $db_prefix, $user_info, $sourcedir;

	$context['current_view'] = isset($_GET['from']) && $_GET['from'] == 'topics' ? 'topics' : 'replies';
	$context['page_title'] = $txt['mc_unapproved_posts'];

	// Work out what boards we can work in!
	$approve_boards = boardsAllowedTo('approve_posts');

	if ($approve_boards == array(0))
		$approve_query = '';
	elseif (!empty($approve_boards))
		$approve_query = ' AND m.ID_BOARD IN (' . implode(',', $approve_boards) . ')';
	// Nada, zip, etc...
	else
		$approve_query = ' AND 0';

	// We also need to know where we can delete topics and/or replies to.
	if ($context['current_view'] == 'topics')
	{
		$delete_own_boards = boardsAllowedTo('remove_own');
		$delete_any_boards = boardsAllowedTo('remove_any');
		$delete_own_replies = array();
	}
	else
	{
		$delete_own_boards = boardsAllowedTo('delete_own');
		$delete_any_boards = boardsAllowedTo('delete_any');
		$delete_own_replies = boardsAllowedTo('delete_own_replies');
	}

	$toAction = array();
	// Check if we have something to do?
	if (isset($_GET['approve']))
		$toAction[] = (int) $_GET['approve'];
	// Just a deletion?
	elseif (isset($_GET['delete']))
		$toAction[] = (int) $_GET['delete'];
	// Lots of approvals?
	elseif (isset($_POST['item']))
		foreach ($_POST['item'] as $item)
			$toAction[] = (int) $item;

	// What are we actually doing.
	if (isset($_GET['approve']) || (isset($_POST['do']) && $_POST['do'] == 'approve'))
		$curAction = 'approve';
	elseif (isset($_GET['delete']) || (isset($_POST['do']) && $_POST['do'] == 'delete'))
		$curAction = 'delete';

	// Right, so we have something to do?
	if (!empty($toAction) && isset($curAction))
	{
		// Handy shortcut.
		$any_array = $curAction == 'approve' ? $approve_boards : $delete_any_boards;

		// Now for each message work out whether it's actually a topic, and what board it's on.
		$request = db_query("
			SELECT m.ID_MSG, m.ID_MEMBER, m.ID_BOARD, t.ID_TOPIC, t.ID_FIRST_MSG, t.ID_MEMBER_STARTED
			FROM ({$db_prefix}messages AS m, {$db_prefix}topics AS t)
			WHERE m.ID_MSG IN (" . implode(',', $toAction) . ")
				AND m.approved = 0
				AND t.ID_TOPIC = m.ID_TOPIC
				AND $user_info[query_see_board]", __FILE__, __LINE__);
		$toAction = array();
		while ($row = mysql_fetch_assoc($request))
		{
			// If it's not within what our view is ignore it...
			if (($row['ID_MSG'] == $row['ID_FIRST_MSG'] && $context['current_view'] != 'topics') || ($row['ID_MSG'] != $row['ID_FIRST_MSG'] && $context['current_view'] != 'replies'))
				continue;

			$can_add = false;
			// If we're approving this is simple.
			if ($curAction == 'approve' && ($any_array == array(0) || in_array($row['ID_BOARD'], $any_array)))
			{
				$can_add = true;
			}
			// Delete requires more permission checks...
			elseif ($curAction == 'delete')
			{
				// Own post is easy!
				if ($row['ID_MEMBER'] == $user_info['id'] && ($delete_own_boards == array(0) || in_array($row['ID_BOARD'], $delete_own_boards)))
					$can_add = true;
				// Is it a reply to their own topic?
				elseif ($row['ID_MEMBER'] == $row['ID_MEMBER_STARTED'] && $row['ID_MSG'] != $row['ID_FIRST_MSG'] && ($delete_own_replies == array(0) || in_array($row['ID_BOARD'], $delete_own_replies)))
					$can_add = true;
				// Someone elses?
				elseif ($row['ID_MEMBER'] != $user_info['id'] && ($delete_any_boards == array(0) || in_array($row['ID_BOARD'], $delete_any_boards)))
					$can_add = true;
			}

			if ($can_add)
				$toAction[] = $context['current_view'] == 'topics' ? $row['ID_TOPIC'] : $row['ID_MSG'];
		}
		mysql_free_result($request);

		// If we have anything left we can actually do the approving (etc).
		if (!empty($toAction))
		{
			if ($curAction == 'approve')
			{
				require_once($sourcedir . '/Subs-Post.php');
				if ($context['current_view'] == 'topics')
					approveTopics($toAction);
				else
					approvePosts($toAction);
			}
			else
			{
				require_once($sourcedir . '/RemoveTopic.php');
				if ($context['current_view'] == 'topics')
					removeTopics($toAction);
				else
				{
					foreach ($toAction as $id)
						removeMessage($id);
				}
			}
		}
	}

	// How many unapproved posts are there?
	$request = db_query("
		SELECT COUNT(*)
		FROM ({$db_prefix}messages AS m, {$db_prefix}topics AS t, {$db_prefix}boards AS b)
		WHERE m.approved = 0
			AND t.ID_TOPIC = m.ID_TOPIC
			AND b.ID_BOARD = t.ID_BOARD
			AND t.ID_FIRST_MSG != m.ID_MSG
			AND $user_info[query_see_board]
			$approve_query", __FILE__, __LINE__);
	list ($context['total_unapproved_posts']) = mysql_fetch_row($request);
	mysql_free_result($request);

	// What about topics?  Normally we'd use the table alias t for topics but lets use m so we don't have to redo our approve query.
	$request = db_query("
		SELECT COUNT(m.ID_TOPIC)
		FROM ({$db_prefix}topics AS m, {$db_prefix}boards AS b)
		WHERE m.approved = 0
			AND b.ID_BOARD = m.ID_BOARD
			AND $user_info[query_see_board]
			$approve_query", __FILE__, __LINE__);
	list ($context['total_unapproved_topics']) = mysql_fetch_row($request);
	mysql_free_result($request);

	$context['page_index'] = constructPageIndex($scripturl . '?action=moderate;area=postmod;sa=posts', $_GET['start'], $context['current_view'] == 'topics' ? $context['total_unapproved_topics'] : $context['total_unapproved_posts'], 10);
	$context['start'] = $_GET['start'];

	// We have enough to make some pretty tabs!
	$context['admin_tabs'] = array(
		'title' => &$txt['mc_unapproved_posts'],
		'help' => 'postmod',
		'description' => $txt['mc_unapproved_posts_desc'],
		'tabs' => array(
			'posts' => array(
				'title' => $txt['mc_unapproved_replies'] . ' (' . $context['total_unapproved_posts'] . ')',
				'href' => $scripturl . '?action=moderate;area=postmod;sa=posts',
				'is_selected' => $context['current_view'] == 'replies',
			),
			'topics' => array(
				'title' => $txt['mc_unapproved_topics'] . ' (' . $context['total_unapproved_topics'] . ')',
				'href' => $scripturl . '?action=moderate;area=postmod;sa=posts;from=topics',
				'is_selected' => $context['current_view'] == 'topics',
				'is_last' => true,
			)
		)
	);

	// Get all unapproved posts.
	$request = db_query("
		SELECT m.ID_MSG, m.ID_TOPIC, m.ID_BOARD, m.subject, m.body, m.ID_MEMBER,
			IFNULL(mem.realName, m.posterName) AS posterName, m.posterTime,
			t.ID_MEMBER_STARTED, t.ID_FIRST_MSG, b.name AS boardName, c.ID_CAT, c.name AS catName
		FROM ({$db_prefix}messages AS m, {$db_prefix}topics AS t, {$db_prefix}boards AS b)
			LEFT JOIN {$db_prefix}members AS mem ON (mem.ID_MEMBER = m.ID_MEMBER)
			LEFT JOIN {$db_prefix}categories AS c ON (c.ID_CAT = b.ID_CAT)
		WHERE m.approved = 0
			AND t.ID_TOPIC = m.ID_TOPIC
			AND b.ID_BOARD = m.ID_BOARD
			AND t.ID_FIRST_MSG " . ($context['current_view'] == 'topics' ? '=' : '!=') . " m.ID_MSG
			AND $user_info[query_see_board]
			$approve_query
		LIMIT $context[start], 10", __FILE__, __LINE__);
	$context['unapproved_items'] = array();
	$count = 1;
	while ($row = mysql_fetch_assoc($request))
	{
		// Can delete is complicated, let's solve it first... is it their own post?
		if ($row['ID_MEMBER'] == $user_info['id'] && ($delete_own_boards == array(0) || in_array($row['ID_BOARD'], $delete_own_boards)))
			$can_delete = true;
		// Is it a reply to their own topic?
		elseif ($row['ID_MEMBER'] == $row['ID_MEMBER_STARTED'] && $row['ID_MSG'] != $row['ID_FIRST_MSG'] && ($delete_own_replies == array(0) || in_array($row['ID_BOARD'], $delete_own_replies)))
			$can_delete = true;
		// Someone elses?
		elseif ($row['ID_MEMBER'] != $user_info['id'] && ($delete_any_boards == array(0) || in_array($row['ID_BOARD'], $delete_any_boards)))
			$can_delete = true;
		else
			$can_delete = false;

		$context['unapproved_items'][] = array(
			'id' => $row['ID_MSG'],
			'counter' => $context['start'] + $count++,
			'href' => $scripturl . '?topic=' . $row['ID_TOPIC'] . '.msg' . $row['ID_MSG'] . '#msg' . $row['ID_MSG'],
			'subject' => $row['subject'],
			'body' => parse_bbc($row['body']),
			'time' => timeformat($row['posterTime']),
			'poster' => array(
				'id' => $row['ID_MEMBER'],
				'name' => $row['posterName'],
				'link' => $row['ID_MEMBER'] ? '<a href="' . $scripturl . '?action=profile;u=' . $row['ID_MEMBER'] . '">' . $row['posterName'] . '</a>' : $row['posterName'],
				'href' => $scripturl . '?action=profile;u=' . $row['ID_MEMBER'],
			),
			'topic' => array(
				'id' => $row['ID_TOPIC'],
			),
			'board' => array(
				'id' => $row['ID_BOARD'],
				'name' => $row['boardName'],
			),
			'category' => array(
				'id' => $row['ID_CAT'],
				'name' => $row['catName'],
			),
			'can_delete' => $can_delete,
		);
	}
	mysql_free_result($request);

	$context['sub_template'] = 'unapproved_posts';
}

// View all unapproved attachments.
function UnapprovedAttachments()
{
	global $txt, $scripturl, $context, $db_prefix, $user_info, $sourcedir;

	$context['page_title'] = $txt['mc_unapproved_attachments'];

	// Once again, permissions are king!
	$approve_boards = boardsAllowedTo('approve_posts');

	if ($approve_boards == array(0))
		$approve_query = '';
	elseif (!empty($approve_boards))
		$approve_query = ' AND m.ID_BOARD IN (' . implode(',', $approve_boards) . ')';
	else
		$approve_query = ' AND 0';

	// Get together the array of things to act on, if any.
	$attachments = array();
	if (isset($_GET['approve']))
		$attachments[] = (int) $_GET['approve'];
	elseif (isset($_GET['delete']))
		$attachments[] = (int) $_GET['delete'];
	elseif (isset($_POST['item']))
		foreach ($_POST['item'] as $item)
			$attachments[] = (int) $item;

	// Are we approving or deleting?
	if (isset($_GET['approve']) || (isset($_POST['do']) && $_POST['do'] == 'approve'))
		$curAction = 'approve';
	elseif (isset($_GET['delete']) || (isset($_POST['do']) && $_POST['do'] == 'delete'))
		$curAction = 'delete';

	// Something to do, let's do it!
	if (!empty($attachments) && isset($curAction))
	{
		// This will be handy.
		require_once($sourcedir . '/ManageAttachments.php');

		// Confirm the attachments are eligible for changing!
		$request = db_query("
			SELECT a.ID_ATTACH
			FROM ({$db_prefix}attachments AS a, {$db_prefix}messages AS m)
			WHERE a.ID_ATTACH IN (" . implode(',', $attachments) . ")
				AND a.approved = 0
				AND a.attachmentType = 0
				AND m.ID_MSG = a.ID_MSG
				AND $user_info[query_see_board]
				$approve_query", __FILE__, __LINE__);
		$attachments = array();
		while ($row = mysql_fetch_assoc($request))
			$attachments[] = $row['ID_ATTACH'];
		mysql_free_result($request);

		// Assuming it wasn't all like, proper illegal, we can do the approving.
		if (!empty($attachments))
		{
			if ($curAction == 'approve')
				ApproveAttachments($attachments);
			else
				removeAttachments('a.ID_ATTACH IN (' . implode(', ', $attachments) . ')');
		}
	}

	// How many unapproved attachments in total?
	$request = db_query("
		SELECT COUNT(*)
		FROM ({$db_prefix}attachments AS a, {$db_prefix}messages AS m, {$db_prefix}boards AS b)
		WHERE a.approved = 0
			AND a.attachmentType = 0
			AND m.ID_MSG = a.ID_MSG
			AND b.ID_BOARD = m.ID_BOARD
			AND $user_info[query_see_board]
			$approve_query", __FILE__, __LINE__);
	list ($context['total_unapproved_attachments']) = mysql_fetch_row($request);
	mysql_free_result($request);

	$context['page_index'] = constructPageIndex($scripturl . '?action=moderate;area=attachmod;sa=attachments', $_GET['start'], $context['total_unapproved_attachments'], 10);
	$context['start'] = $_GET['start'];

	// Get all unapproved attachments.
	$request = db_query("
		SELECT a.ID_ATTACH, a.filename, a.size, m.ID_MSG, m.ID_TOPIC, m.ID_BOARD, m.subject, m.body, m.ID_MEMBER,
			IFNULL(mem.realName, m.posterName) AS posterName, m.posterTime,
			t.ID_MEMBER_STARTED, t.ID_FIRST_MSG, b.name AS boardName, c.ID_CAT, c.name AS catName
		FROM ({$db_prefix}attachments AS a, {$db_prefix}messages AS m, {$db_prefix}topics AS t, {$db_prefix}boards AS b)
			LEFT JOIN {$db_prefix}members AS mem ON (mem.ID_MEMBER = m.ID_MEMBER)
			LEFT JOIN {$db_prefix}categories AS c ON (c.ID_CAT = b.ID_CAT)
		WHERE a.approved = 0
			AND a.attachmentType = 0
			AND m.ID_MSG = a.ID_MSG
			AND t.ID_TOPIC = m.ID_TOPIC
			AND b.ID_BOARD = m.ID_BOARD
			AND $user_info[query_see_board]
			$approve_query
		LIMIT $context[start], 10", __FILE__, __LINE__);
	$context['unapproved_items'] = array();
	while ($row = mysql_fetch_assoc($request))
	{
		$context['unapproved_items'][] = array(
			'id' => $row['ID_ATTACH'],
			'filename' => $row['filename'],
			'size' => round($row['size'] / 1024, 2),
			'time' => timeformat($row['posterTime']),
			'poster' => array(
				'id' => $row['ID_MEMBER'],
				'name' => $row['posterName'],
				'link' => $row['ID_MEMBER'] ? '<a href="' . $scripturl . '?action=profile;u=' . $row['ID_MEMBER'] . '">' . $row['posterName'] . '</a>' : $row['posterName'],
				'href' => $scripturl . '?action=profile;u=' . $row['ID_MEMBER'],
			),
			'message' => array(
				'id' => $row['ID_MSG'],
				'subject' => $row['subject'],
				'body' => parse_bbc($row['body']),
				'time' => timeformat($row['posterTime']),
				'href' => $scripturl . '?topic=' . $row['ID_TOPIC'] . '.msg' . $row['ID_MSG'] . '#msg' . $row['ID_MSG'],
			),
			'topic' => array(
				'id' => $row['ID_TOPIC'],
			),
			'board' => array(
				'id' => $row['ID_BOARD'],
				'name' => $row['boardName'],
			),
			'category' => array(
				'id' => $row['ID_CAT'],
				'name' => $row['catName'],
			),
		);
	}
	mysql_free_result($request);

	$context['sub_template'] = 'unapproved_attachments';
}

// Approve a post, just the one.
function ApproveMessage()
{
	global $ID_MEMBER, $db_prefix, $topic, $board, $sourcedir;

	checkSession('get');

	$_REQUEST['msg'] = (int) $_REQUEST['msg'];

	require_once($sourcedir . '/Subs-Post.php');

	isAllowedTo('approve_posts');

	$request = db_query("
		SELECT t.ID_MEMBER_STARTED, t.ID_FIRST_MSG, m.ID_MEMBER, m.subject, m.approved
		FROM ({$db_prefix}topics AS t, {$db_prefix}messages AS m)
		WHERE t.ID_TOPIC = $topic
			AND m.ID_TOPIC = $topic
			AND m.ID_MSG = $_REQUEST[msg]
		LIMIT 1", __FILE__, __LINE__);
	list ($starter, $first_msg, $poster, $subject, $approved) = mysql_fetch_row($request);
	mysql_free_result($request);

	// If it's the first in a topic then the whole topic gets approved!
	if ($first_msg == $_REQUEST['msg'])
	{
		approveTopics($topic, !$approved);

		if ($starter != $ID_MEMBER)
			logAction('approve_topic', array('topic' => $topic, 'subject' => $subject, 'member' => $starter, 'board' => $board));
	}
	else
	{
		approvePosts($_REQUEST['msg'], !$approved);

		if ($poster != $ID_MEMBER)
			logAction('approve', array('topic' => $topic, 'subject' => $subject, 'member' => $poster, 'board' => $board));
	}

	redirectexit('topic=' . $topic . '.msg' . $_REQUEST['msg']. '#msg' . $_REQUEST['msg']);
}

?>