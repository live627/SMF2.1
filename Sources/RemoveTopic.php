<?php
/******************************************************************************
* RemoveTopic.php                                                             *
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

/*	The contents of this file handle the deletion of topics, posts, and related
	paraphernalia.  It has the following functions:

	void RemoveTopic2()
		// !!!

	void DeleteMessage()
		// !!!

	void RemoveOldTopics2()
		// !!!

	void removeTopics(array topics, bool decreasePostCount = true, bool ignoreRecycling = false)
		// !!!

	bool removeMessage(int id_msg, bool decreasePostCount = true)
		// !!!
*/

// Completely remove an entire topic.
function RemoveTopic2()
{
	global $id_member, $db_prefix, $topic, $board, $sourcedir, $smfFunc;

	// Make sure they aren't being lead around by someone. (:@)
	checkSession('get');

	// This file needs to be included for sendNotifications().
	require_once($sourcedir . '/Subs-Post.php');

	$request = $smfFunc['db_query']('', "
		SELECT t.id_member_started, ms.subject, t.approved
		FROM ({$db_prefix}topics AS t, {$db_prefix}messages AS ms)
		WHERE t.id_topic = $topic
			AND ms.id_msg = t.id_first_msg
		LIMIT 1", __FILE__, __LINE__);
	list ($starter, $subject, $approved) = $smfFunc['db_fetch_row']($request);
	$smfFunc['db_free_result']($request);

	if ($starter == $id_member && !allowedTo('remove_any'))
		isAllowedTo('remove_own');
	else
		isAllowedTo('remove_any');

	// Can they see the topic?
	if (!$approved)
		isAllowedTo('approve_posts');

	// Notify people that this topic has been removed.
	sendNotifications($topic, 'remove');

	removeTopics($topic);

	if (allowedTo('remove_any') && (!allowedTo('remove_own') || $starter != $id_member))
		logAction('remove', array('topic' => $topic, 'subject' => $subject, 'member' => $starter, 'board' => $board));

	redirectexit('board=' . $board . '.0');
}

// Remove just a single post.
function DeleteMessage()
{
	global $id_member, $db_prefix, $topic, $board, $modSettings, $smfFunc;

	checkSession('get');

	$_REQUEST['msg'] = (int) $_REQUEST['msg'];

	$request = $smfFunc['db_query']('', "
		SELECT t.id_member_started, m.id_member, m.subject, m.poster_time, m.approved
		FROM ({$db_prefix}topics AS t, {$db_prefix}messages AS m)
		WHERE t.id_topic = $topic
			AND m.id_topic = $topic
			AND m.id_msg = $_REQUEST[msg]
		LIMIT 1", __FILE__, __LINE__);
	list ($starter, $poster, $subject, $post_time, $approved) = $smfFunc['db_fetch_row']($request);
	$smfFunc['db_free_result']($request);

	// Verify they can see this!
	if (!$approved)
		isAllowedTo('approve_posts');

	if ($poster == $id_member)
	{
		if (!allowedTo('delete_own'))
		{
			if ($starter == $id_member && !allowedTo('delete_any'))
				isAllowedTo('delete_replies');
			elseif (!allowedTo('delete_any'))
				isAllowedTo('delete_own');
		}
		elseif (!allowedTo('delete_any') && ($starter != $id_member || !allowedTo('delete_replies')) && !empty($modSettings['edit_disable_time']) && $post_time + $modSettings['edit_disable_time'] * 60 < time())
			fatal_lang_error('modify_post_time_passed', false);
	}
	elseif ($starter == $id_member && !allowedTo('delete_any'))
		isAllowedTo('delete_replies');
	else
		isAllowedTo('delete_any');

	// If the full topic was removed go back to the board.
	$full_topic = removeMessage($_REQUEST['msg']);

	if (allowedTo('delete_any') && (!allowedTo('delete_own') || $poster != $id_member))
		logAction('delete', array('topic' => $topic, 'subject' => $subject, 'member' => $starter, 'board' => $board));

	if ($full_topic)
		redirectexit('board=' . $board . '.0');
	else
		redirectexit('topic=' . $topic . '.' . $_REQUEST['start']);
}

// So long as you are sure... all old posts will be gone.
function RemoveOldTopics2()
{
	global $db_prefix, $modSettings, $smfFunc;

	isAllowedTo('admin_forum');
	checkSession('post', 'admin');

	// No boards at all?  Forget it then :/.
	if (empty($_POST['boards']))
		redirectexit('action=admin;area=maintain');

	// This should exist, but we can make sure.
	$_POST['delete_type'] = isset($_POST['delete_type']) ? $_POST['delete_type'] : 'nothing';

	// Custom conditions.
	$condition = '';

	// Just moved notice topics?
	if ($_POST['delete_type'] == 'moved')
		$condition .= '
			AND m.icon = \'moved\'
			AND t.locked = 1';
	// Otherwise, maybe locked topics only?
	elseif ($_POST['delete_type'] == 'locked')
		$condition .= '
			AND t.locked = 1';

	// Exclude stickies?
	if (isset($_POST['delete_old_not_sticky']))
		$condition .= '
			AND t.is_sticky = 0';

	// All we're gonna do here is grab the ID_TOPICs and send them to removeTopics().
	$request = $smfFunc['db_query']('', "
		SELECT t.id_topic
		FROM ({$db_prefix}topics AS t, {$db_prefix}messages AS m)
		WHERE m.id_msg = t.id_last_msg
			AND m.poster_time < " . (time() - 3600 * 24 * $_POST['maxdays']) . "$condition
			AND t.id_board IN (" . implode(', ', array_keys($_POST['boards'])) . ')', __FILE__, __LINE__);
	$topics = array();
	while ($row = $smfFunc['db_fetch_assoc']($request))
		$topics[] = $row['id_topic'];
	$smfFunc['db_free_result']($request);

	removeTopics($topics, false, true);

	// Log an action into the moderation log.
	logAction('pruned', array('days' => $_POST['maxdays']));

	redirectexit('action=admin;area=maintain;done');
}

// Removes the passed ID_TOPICs. (permissions are NOT checked here!)
function removeTopics($topics, $decreasePostCount = true, $ignoreRecycling = false)
{
	global $db_prefix, $sourcedir, $modSettings, $smfFunc;

	// Nothing to do?
	if (empty($topics))
		return;
	// Only a single topic.
	elseif (is_numeric($topics))
	{
		$condition = '= ' . $topics;
		$topics = array($topics);
	}
	elseif (count($topics) == 1)
		$condition = '= ' . $topics[0];
	// More than one topic.
	else
		$condition = 'IN (' . implode(', ', $topics) . ')';

	// Decrease the post counts.
	if ($decreasePostCount)
	{
		$requestMembers = $smfFunc['db_query']('', "
			SELECT m.id_member, COUNT(*) AS posts
			FROM ({$db_prefix}messages AS m, {$db_prefix}boards AS b)
			WHERE m.id_topic $condition
				AND b.id_board = m.id_board
				AND m.icon != 'recycled'
				AND b.count_posts = 0
			GROUP BY m.id_member", __FILE__, __LINE__);
		if ($smfFunc['db_num_rows']($requestMembers) > 0)
		{
			while ($rowMembers = $smfFunc['db_fetch_assoc']($requestMembers))
				updateMemberData($rowMembers['id_member'], array('posts' => 'posts - ' . $rowMembers['posts']));
		}
		$smfFunc['db_free_result']($requestMembers);
	}

	// Recycle topics that aren't in the recycle board...
	if (!empty($modSettings['recycle_enable']) && $modSettings['recycle_board'] > 0 && !$ignoreRecycling)
	{
		$request = $smfFunc['db_query']('', "
			SELECT id_topic
			FROM {$db_prefix}topics
			WHERE id_topic $condition
				AND id_board != $modSettings[recycle_board]
			LIMIT " . count($topics), __FILE__, __LINE__);
		if ($smfFunc['db_num_rows']($request) > 0)
		{
			// Get topics that will be recycled.
			$recycleTopics = array();
			while ($row = $smfFunc['db_fetch_assoc']($request))
				$recycleTopics[] = $row['id_topic'];
			$smfFunc['db_free_result']($request);

			// Mark recycled topics as recycled.
			$smfFunc['db_query']('', "
				UPDATE {$db_prefix}messages
				SET icon = 'recycled'
				WHERE id_topic IN (" . implode(', ', $recycleTopics) . ")", __FILE__, __LINE__);

			// De-sticky and unlock topics.
			$smfFunc['db_query']('', "
				UPDATE {$db_prefix}topics
				SET
					locked = 0,
					is_sticky = 0
				WHERE id_topic IN (" . implode(', ', $recycleTopics) . ")", __FILE__, __LINE__);

			// Move the topics to the recycle board.
			require_once($sourcedir . '/MoveTopic.php');
			moveTopics($recycleTopics, $modSettings['recycle_board']);

			// Topics that were recycled don't need to be deleted, so subtract them.
			$topics = array_diff($topics, $recycleTopics);

			// Topic list has changed, so does the condition to select topics.
			$condition = 'IN (' . implode(', ', $topics) . ')';
		}
		else
			$smfFunc['db_free_result']($request);
	}

	// Still topics left to delete?
	if (empty($topics))
		return;

	$adjustBoards = array();

	// Find out how many posts we are deleting.
	$request = $smfFunc['db_query']('', "
		SELECT id_board, approved, COUNT(*) AS num_topics, SUM(unapproved_posts) AS unapproved_posts,
			SUM(num_replies) AS num_replies
		FROM {$db_prefix}topics
		WHERE id_topic $condition
		GROUP BY id_board, approved", __FILE__, __LINE__);
	while ($row = $smfFunc['db_fetch_assoc']($request))
	{
		if (!isset($adjustBoards[$row['id_board']]['num_posts']))
		{
			$adjustBoards[$row['id_board']] = array(
				'num_posts' => 0,
				'num_topics' => 0,
				'unapproved_posts' => 0,
				'unapproved_topics' => 0,
				'id_board' => $row['id_board']
			);
		}
		// Posts = (num_replies + 1) for each approved topic.
		$adjustBoards[$row['id_board']]['num_posts'] += $row['num_replies'] + ($row['approved'] ? $row['num_topics'] : 0);
		$adjustBoards[$row['id_board']]['unapproved_posts'] += $row['unapproved_posts'];

		// Add the topics to the right type.
		if ($row['approved'])
			$adjustBoards[$row['id_board']]['num_topics'] += $row['num_topics'];
		else
			$adjustBoards[$row['id_board']]['unapproved_topics'] += $row['num_topics'];
	}
	$smfFunc['db_free_result']($request);

	// Decrease the posts/topics...
	foreach ($adjustBoards as $stats)
	{
		$smfFunc['db_query']('', "
			UPDATE {$db_prefix}boards
			SET
				num_posts = CASE WHEN $stats[num_posts] > num_posts THEN 0 ELSE num_posts - $stats[num_posts] END,
				num_topics = CASE WHEN $stats[num_topics] > num_topics THEN 0 ELSE num_topics - $stats[num_topics] END,
				unapproved_posts = CASE WHEN $stats[unapproved_posts] > unapproved_posts THEN 0 ELSE unapproved_posts - $stats[unapproved_posts] END,
				unapproved_topics = CASE WHEN $stats[unapproved_topics] > unapproved_topics THEN 0 ELSE unapproved_topics - $stats[unapproved_topics] END
			WHERE id_board = $stats[id_board]", __FILE__, __LINE__);
	}

	// Remove Polls.
	$request = $smfFunc['db_query']('', "
		SELECT id_poll
		FROM {$db_prefix}topics
		WHERE id_topic $condition
			AND id_poll > 0
		LIMIT " . count($topics), __FILE__, __LINE__);
	$polls = array();
	while ($row = $smfFunc['db_fetch_assoc']($request))
		$polls[] = $row['id_poll'];
	$smfFunc['db_free_result']($request);

	if (!empty($polls))
	{
		$pollCondition = count($polls) == 1 ? '= ' . $polls[0] : 'IN (' . implode(', ', $polls) . ')';

		$smfFunc['db_query']('', "
			DELETE FROM {$db_prefix}polls
			WHERE id_poll $pollCondition", __FILE__, __LINE__);
		$smfFunc['db_query']('', "
			DELETE FROM {$db_prefix}poll_choices
			WHERE id_poll $pollCondition", __FILE__, __LINE__);
		$smfFunc['db_query']('', "
			DELETE FROM {$db_prefix}log_polls
			WHERE id_poll $pollCondition", __FILE__, __LINE__);
	}

	// Get rid of the attachment, if it exists.
	require_once($sourcedir . '/ManageAttachments.php');
	removeAttachments('a.attachment_type = 0 AND m.id_topic ' . $condition, 'messages');

	// Delete possible search index entries.
	if (!empty($modSettings['search_custom_index_config']))
	{
		$customIndexSettings = unserialize($modSettings['search_custom_index_config']);

		$words = array();
		$messages = array();
		$request = $smfFunc['db_query']('', "
			SELECT id_msg, body
			FROM {$db_prefix}messages
			WHERE id_topic $condition", __FILE__, __LINE__);
		while ($row = $smfFunc['db_fetch_assoc']($request))
		{
			$words = array_merge($words, text2words($row['body'], $customIndexSettings['bytes_per_word'], true));
			$messages[] = $row['id_msg'];
		}
		$smfFunc['db_free_result']($request);
		$words = array_unique($words);

		if (!empty($words) && !empty($messages))
			$smfFunc['db_query']('', "
				DELETE FROM {$db_prefix}log_search_words
				WHERE ID_WORD IN (" . implode(', ', $words) . ")
					AND id_msg IN (" . implode(', ', $messages) . ')', __FILE__, __LINE__);
	}

	// Delete anything related to the topic.
	$smfFunc['db_query']('', "
		DELETE FROM {$db_prefix}messages
		WHERE id_topic $condition", __FILE__, __LINE__);
	$smfFunc['db_query']('', "
		DELETE FROM {$db_prefix}calendar
		WHERE id_topic $condition", __FILE__, __LINE__);
	$smfFunc['db_query']('', "
		DELETE FROM {$db_prefix}log_topics
		WHERE id_topic $condition", __FILE__, __LINE__);
	$smfFunc['db_query']('', "
		DELETE FROM {$db_prefix}log_notify
		WHERE id_topic $condition", __FILE__, __LINE__);
	$smfFunc['db_query']('', "
		DELETE FROM {$db_prefix}topics
		WHERE id_topic $condition", __FILE__, __LINE__);
	$smfFunc['db_query']('', "
		DELETE FROM {$db_prefix}log_search_subjects
		WHERE id_topic $condition", __FILE__, __LINE__);

	// Update the totals...
	updateStats('message');
	updateStats('topic');
	updateStats('calendar');

	require_once($sourcedir . '/Subs-Post.php');
	$updates = array();
	foreach ($adjustBoards as $stats)
		$updates[] = $stats['id_board'];
	updateLastMessages($updates);
}

// Remove a specific message (including permission checks).
function removeMessage($message, $decreasePostCount = true)
{
	global $db_prefix, $board, $sourcedir, $modSettings, $id_member, $user_info, $smfFunc;

	if (empty($message) || !is_numeric($message))
		return false;

	$request = $smfFunc['db_query']('', "
		SELECT
			m.id_member, m.icon, m.poster_time, m.subject," . (empty($modSettings['search_custom_index_config']) ? '' : ' m.body,') . "
			m.approved, t.id_topic, t.id_first_msg, t.id_last_msg, t.num_replies, t.id_board,
			t.id_member_started AS ID_MEMBER_POSTER,
			b.count_posts
		FROM ({$db_prefix}messages AS m, {$db_prefix}topics AS t, {$db_prefix}boards AS b)
		WHERE m.id_msg = $message
			AND t.id_topic = m.id_topic
			AND b.id_board = t.id_board
		LIMIT 1", __FILE__, __LINE__);
	if ($smfFunc['db_num_rows']($request) == 0)
		return false;
	$row = $smfFunc['db_fetch_assoc']($request);
	$smfFunc['db_free_result']($request);

	if (empty($board) || $row['id_board'] != $board)
	{
		$delete_any = boardsAllowedTo('delete_any');

		if (!in_array(0, $delete_any) && !in_array($row['id_board'], $delete_any))
		{
			$delete_own = boardsAllowedTo('delete_own');
			$delete_own = in_array(0, $delete_own) || in_array($row['id_board'], $delete_own);
			$delete_replies = boardsAllowedTo('delete_replies');
			$delete_replies = in_array(0, $delete_replies) || in_array($row['id_board'], $delete_replies);

			if ($row['id_member'] == $id_member)
			{
				if (!$delete_own)
				{
					if ($row['ID_MEMBER_POSTER'] == $id_member)
					{
						if (!$delete_replies)
							fatal_lang_error('cannot_delete_replies', 'permission');
					}
					else
						fatal_lang_error('cannot_delete_own', 'permission');
				}
				elseif (($row['ID_MEMBER_POSTER'] != $id_member || !$delete_replies) && !empty($modSettings['edit_disable_time']) && $row['poster_time'] + $modSettings['edit_disable_time'] * 60 < time())
					fatal_lang_error('modify_post_time_passed', false);
			}
			elseif ($row['ID_MEMBER_POSTER'] == $id_member)
			{
				if (!$delete_replies)
					fatal_lang_error('cannot_delete_replies', 'permission');
			}
			else
				fatal_lang_error('cannot_delete_any', 'permission');
		}

		// Can't delete an unapproved message, if you can't see it!
		if (!$row['approved'])
		{
			$approve_posts = boardsAllowedTo('approve_posts');
			if (!in_array(0, $approve_posts) && !in_array($row['id_board'], $approve_posts))
				return false;
		}
	}
	else
	{
		// Check permissions to delete this message.
		if ($row['id_member'] == $id_member)
		{
			if (!allowedTo('delete_own'))
			{
				if ($row['ID_MEMBER_POSTER'] == $id_member && !allowedTo('delete_any'))
					isAllowedTo('delete_replies');
				elseif (!allowedTo('delete_any'))
					isAllowedTo('delete_own');
			}
			elseif (!allowedTo('delete_any') && ($row['ID_MEMBER_POSTER'] != $id_member || !allowedTo('delete_replies')) && !empty($modSettings['edit_disable_time']) && $row['poster_time'] + $modSettings['edit_disable_time'] * 60 < time())
				fatal_lang_error('modify_post_time_passed', false);
		}
		elseif ($row['ID_MEMBER_POSTER'] == $id_member && !allowedTo('delete_any'))
			isAllowedTo('delete_replies');
		else
			isAllowedTo('delete_any');

		if (!$row['approved'])
			isAllowedTo('approve_posts');
	}

	// Delete the *whole* topic, but only if the topic consists of one message.
	if ($row['id_first_msg'] == $message)
	{
		if (empty($board) || $row['id_board'] != $board)
		{
			$remove_any = boardsAllowedTo('remove_any');
			$remove_any = in_array(0, $remove_any) || in_array($row['id_board'], $remove_any);
			if (!$remove_any)
			{
				$remove_own = boardsAllowedTo('remove_own');
				$remove_own = in_array(0, $remove_own) || in_array($row['id_board'], $remove_own);
			}

			if ($row['id_member'] != $id_member && !$remove_any)
				fatal_lang_error('cannot_remove_any', 'permission');
			elseif (!$remove_any && !$remove_own)
				fatal_lang_error('cannot_remove_own', 'permission');
		}
		else
		{
			// Check permissions to delete a whole topic.
			if ($row['id_member'] != $id_member)
				isAllowedTo('remove_any');
			elseif (!allowedTo('remove_any'))
				isAllowedTo('remove_own');
		}

		// ...if there is only one post.
		if (!empty($row['num_replies']))
			fatal_lang_error('delFirstPost', false);

		removeTopics($row['id_topic']);
		return true;
	}

	// Default recycle to false.
	$recycle = false;

	// If recycle topics has been set, make a copy of this message in the recycle board.
	// Make sure we're not recycling messages that are already on the recycle board.
	if (!empty($modSettings['recycle_enable']) && $row['id_board'] != $modSettings['recycle_board'] && $row['icon'] != 'recycled')
	{
		// Check if the recycle board exists and if so get the read status.
		$request = $smfFunc['db_query']('', "
			SELECT (IFNULL(lb.id_msg, 0) >= b.id_msg_updated) AS isSeen
			FROM {$db_prefix}boards AS b
				LEFT JOIN {$db_prefix}log_boards AS lb ON (lb.id_board = b.id_board AND lb.id_member = $id_member)
			WHERE b.id_board = $modSettings[recycle_board]", __FILE__, __LINE__);
		if ($smfFunc['db_num_rows']($request) == 0)
			fatal_lang_error('recycle_no_valid_board');
		list ($isRead) = $smfFunc['db_fetch_row']($request);
		$smfFunc['db_free_result']($request);

		// Even if it's being recycled respect approval state.
		$unapproved_posts = $row['approved'] ? 0 : 1;
		$approved = !$unapproved_posts;

		// Insert a new topic in the recycle board.
		$smfFunc['db_query']('', "
			INSERT INTO {$db_prefix}topics
				(id_board, id_member_started, id_member_updated, id_first_msg, id_last_msg, unapproved_posts, approved)
			VALUES ($modSettings[recycle_board], $row[id_member], $row[id_member], $message, $message, $unapproved_posts, $approved)", __FILE__, __LINE__);

		// Capture the ID of the new topic...
		$topicID = db_insert_id("{$db_prefix}topics", 'id_topic');

		// If the topic creation went successful, move the message.
		if ($topicID > 0)
		{
			$smfFunc['db_query']('', "
				UPDATE {$db_prefix}messages
				SET
					id_topic = $topicID,
					id_board = $modSettings[recycle_board],
					icon = 'recycled'
				WHERE id_msg = $message", __FILE__, __LINE__);

			// Take any reported posts with us...
			$smfFunc['db_query']('', "
				UPDATE {$db_prefix}log_reported
				SET
					id_topic = $topicID,
					id_board = $modSettings[recycle_board]
				WHERE id_msg = $message", __FILE__, __LINE__);

			// Mark recycled topic as read.
			if (!$user_info['is_guest'])
				$smfFunc['db_insert']('replace',
					"{$db_prefix}log_topics",
					array('id_topic', 'id_member', 'id_msg'),
					array($topicID, $id_member, $modSettings['maxMsgID']),
					array('id_topic', 'id_member')
				);

			// Mark recycle board as seen, if it was marked as seen before.
			if (!empty($isRead) && !$user_info['is_guest'])
				$smfFunc['db_insert']('replace',
					"{$db_prefix}log_boards",
					array('id_board', 'id_member', 'id_msg'),
					array($modSettings['recycle_board'], $id_member, $modSettings['maxMsgID']),
					array('id_board', 'id_member')
				);

			// Add one topic and post to the recycle bin board.
			if ($approved)
				$smfFunc['db_query']('', "
					UPDATE {$db_prefix}boards
					SET
						num_topics = num_topics + 1,
						num_posts = num_posts + 1
					WHERE id_board = $modSettings[recycle_board]", __FILE__, __LINE__);
			else
				$smfFunc['db_query']('', "
					UPDATE {$db_prefix}boards
					SET
						unapproved_topics = unapproved_topics + 1,
						unapproved_posts = unapproved_posts + 1
					WHERE id_board = $modSettings[recycle_board]", __FILE__, __LINE__);

			// Make sure this message isn't getting deleted later on.
			$recycle = true;

			// Make sure we update the search subject index.
			updateStats('subject', $topicID, $row['subject']);
		}
	}

	// Deleting a recycled message can not lower anyone's post count.
	if ($row['icon'] == 'recycled')
		$decreasePostCount = false;

	// This is the last post, update the last post on the board.
	if ($row['id_last_msg'] == $message)
	{
		// Find the last message, set it, and decrease the post count.
		$request = $smfFunc['db_query']('', "
			SELECT id_msg, id_member
			FROM {$db_prefix}messages
			WHERE id_topic = $row[id_topic]
				AND id_msg != $message
			ORDER BY approved DESC, id_msg DESC
			LIMIT 1", __FILE__, __LINE__);
		$row2 = $smfFunc['db_fetch_assoc']($request);
		$smfFunc['db_free_result']($request);

		$smfFunc['db_query']('', "
			UPDATE {$db_prefix}topics
			SET
				id_last_msg = $row2[id_msg],
				id_member_updated = $row2[id_member]" . ($row['approved'] ? ",
				num_replies = CASE WHEN num_replies = 0 THEN 0 ELSE num_replies - 1 END" : ",
				unapproved_posts = CASE WHEN unapproved_posts = 0 THEN 0 ELSE unapproved_posts - 1 END") . "
			WHERE id_topic = $row[id_topic]", __FILE__, __LINE__);
	}
	// Only decrease post counts.
	else
		$smfFunc['db_query']('', "
			UPDATE {$db_prefix}topics
			SET " . ($row['approved'] ? "
				num_replies = CASE WHEN num_replies = 0 THEN 0 ELSE num_replies - 1 END" : "
				unapproved_posts = CASE WHEN unapproved_posts = 0 THEN 0 ELSE unapproved_posts - 1 END") . "
			WHERE id_topic = $row[id_topic]", __FILE__, __LINE__);

	$smfFunc['db_query']('', "
		UPDATE {$db_prefix}boards
		SET " . ($row['approved'] ? "
			num_posts = CASE WHEN num_posts = 0 THEN 0 ELSE num_posts - 1 END" : "
			unapproved_posts = CASE WHEN unapproved_posts = 0 THEN 0 ELSE unapproved_posts - 1 END") . "
		WHERE id_board = $row[id_board]", __FILE__, __LINE__);

	// If the poster was registered and the board this message was on incremented
	// the member's posts when it was posted, decrease his or her post count.
	if (!empty($row['id_member']) && $decreasePostCount && empty($row['count_posts']))
		updateMemberData($row['id_member'], array('posts' => '-'));

	// Only remove posts if they're not recycled.
	if (!$recycle)
	{
		// Remove the message!
		$smfFunc['db_query']('', "
			DELETE FROM {$db_prefix}messages
			WHERE id_msg = $message", __FILE__, __LINE__);

		if (!empty($modSettings['search_custom_index_config']))
		{
			$customIndexSettings = unserialize($modSettings['search_custom_index_config']);
			$words = text2words($row['body'], $customIndexSettings['bytes_per_word'], true);
			if (!empty($words))
				$smfFunc['db_query']('', "
					DELETE FROM {$db_prefix}log_search_words
					WHERE ID_WORD IN (" . implode(', ', $words) . ")
						AND id_msg = $message", __FILE__, __LINE__);
		}

		// Delete attachment(s) if they exist.
		require_once($sourcedir . '/ManageAttachments.php');
		removeAttachments('a.attachment_type = 0 AND a.id_msg = ' . $message);
	}

	// Update the pesky statistics.
	updateStats('message');
	updateStats('topic');
	updateStats('calendar');

	// And now to update the last message of each board we messed with.
	require_once($sourcedir . '/Subs-Post.php');
	if ($recycle)
		updateLastMessages(array($row['id_board'], $modSettings['recycle_board']));
	else
		updateLastMessages($row['id_board']);

	return false;
}

?>