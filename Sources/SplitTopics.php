<?php
/**********************************************************************************
* SplitTopics.php                                                                 *
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
/* Original module by Mach8 - We'll never forget you.                            */
/*********************************************************************************/
if (!defined('SMF'))
	die('Hacking attempt...');

/*	This file handles merging and splitting topics... it does this with:

	void SplitTopics()
		- splits a topic into two topics.
		- delegates to the other functions (based on the URL parameter 'sa').
		- loads the SplitTopics template.
		- requires the split_any permission.
		- is accessed with ?action=splittopics.

	void SplitIndex()
		- screen shown before the actual split.
		- is accessed with ?action=splittopics;sa=index.
		- default sub action for ?action=splittopics.
		- uses 'ask' sub template of the SplitTopics template.
		- redirects to SplitSelectTopics if the message given turns out to be
		  the first message of a topic.
		- shows the user three ways to split the current topic.

	void SplitExecute()
		- do the actual split.
		- is accessed with ?action=splittopics;sa=execute.
		- uses the main SplitTopics template.
		- supports three ways of splitting:
		   (1) only one message is split off.
		   (2) all messages after and including a given message are split off.
		   (3) select topics to split (redirects to SplitSelectTopics()).
		- uses splitTopic function to do the actual splitting.

	void SplitSelectTopics()
		- allows the user to select the messages to be split.
		- is accessed with ?action=splittopics;sa=selectTopics.
		- uses 'select' sub template of the SplitTopics template or (for
		  XMLhttp) the 'split' sub template of the Xml template.
		- supports XMLhttp for adding/removing a message to the selection.
		- uses a session variable to store the selected topics.
		- shows two independent page indexes for both the selected and
		  not-selected messages (;topic=1.x;start2=y).

	void SplitSelectionExecute()
		- do the actual split of a selection of topics.
		- is accessed with ?action=splittopics;sa=splitSelection.
		- uses the main SplitTopics template.
		- uses splitTopic function to do the actual splitting.

	int splitTopic(int topicID, array messagesToBeSplit, string newSubject)
		- general function to split off a topic.
		- creates a new topic and moves the messages with the IDs in
		  array messagesToBeSplit to the new topic.
		- the subject of the newly created topic is set to 'newSubject'.
		- marks the newly created message as read for the user splitting it.
		- updates the statistics to reflect a newly created topic.
		- logs the action in the moderation log.
		- a notification is sent to all users monitoring this topic.
		- returns the topic ID of the new split topic.

	void MergeTopics()
		- merges two or more topics into one topic.
		- delegates to the other functions (based on the URL parameter sa).
		- loads the SplitTopics template.
		- requires the merge_any permission.
		- is accessed with ?action=mergetopics.

	void MergeIndex()
		- allows to pick a topic to merge the current topic with.
		- is accessed with ?action=mergetopics;sa=index
		- default sub action for ?action=mergetopics.
		- uses 'merge' sub template of the SplitTopics template.
		- allows to set a different target board.

	void MergeExecute(array topics = request)
		- set merge options and do the actual merge of two or more topics.
		- the merge options screen:
			- shows topics to be merged and allows to set some merge options.
			- is accessed by ?action=mergetopics;sa=options.and can also
			  internally be called by QuickModeration() (Subs-Boards.php).
			- uses 'merge_extra_options' sub template of the SplitTopics
			  template.
		- the actual merge:
			- is accessed with ?action=mergetopics;sa=execute.
			- updates the statistics to reflect the merge.
			- logs the action in the moderation log.
			- sends a notification is sent to all users monitoring this topic.
			- redirects to ?action=mergetopics;sa=done.

	void MergeDone()
		- shows a 'merge completed' screen.
		- is accessed with ?action=mergetopics;sa=done.
		- uses 'merge_done' sub template of the SplitTopics template.
*/

// Split a topic into two separate topics... in case it got offtopic, etc.
function SplitTopics()
{
	global $topic, $sourcedir;

	// And... which topic were you splitting, again?
	if (empty($topic))
		fatal_lang_error('numbers_one_to_nine', false);

	// Are you allowed to split topics?
	isAllowedTo('split_any');

	// Load up the "dependencies" - the template, getMsgMemberID(), and sendNotifications().
	if (!isset($_REQUEST['xml']))
		loadTemplate('SplitTopics');
	require_once($sourcedir . '/Subs-Boards.php');
	require_once($sourcedir . '/Subs-Post.php');

	$subActions = array(
		'selectTopics' => 'SplitSelectTopics',
		'execute' => 'SplitExecute',
		'index' => 'SplitIndex',
		'splitSelection' => 'SplitSelectionExecute',
	);

	// ?action=splittopics;sa=LETSBREAKIT won't work, sorry.
	if (empty($_REQUEST['sa']) || !isset($subActions[$_REQUEST['sa']]))
		SplitIndex();
	else
		$subActions[$_REQUEST['sa']]();
}

// Part 1: General stuff.
function SplitIndex()
{
	global $txt, $topic, $db_prefix, $context, $smfFunc;

	// Validate "at".
	if (empty($_GET['at']))
		fatal_lang_error('numbers_one_to_nine', false);
	$_GET['at'] = (int) $_GET['at'];

	// Can they view unapproved?
	$approveQuery = allowedTo('approve_posts') ? ' AND m.approved = 1' : '';

	// Retrieve the subject and stuff of the specific topic/message.
	$request = $smfFunc['db_query']('', "
		SELECT m.subject, t.num_replies, t.id_first_msg, t.approved
		FROM {$db_prefix}messages AS m
			INNER JOIN {$db_prefix}topics AS t ON (t.id_topic = $topic)
		WHERE m.id_msg = $_GET[at]
			$approveQuery
			AND m.id_topic = $topic
		LIMIT 1", __FILE__, __LINE__);
	if ($smfFunc['db_num_rows']($request) == 0)
		fatal_lang_error('cant_find_messages');
	list ($_REQUEST['subname'], $num_replies, $id_first_msg, $approved) = $smfFunc['db_fetch_row']($request);
	$smfFunc['db_free_result']($request);

	// If not approved validate they can see it.
	if (!$approved)
		isAllowedTo('approve_posts');

	// Check if there is more than one message in the topic.  (there should be.)
	if ($num_replies < 1)
		fatal_lang_error('topic_one_post', false);

	// Check if this is the first message in the topic (if so, the first and second option won't be available)
	if ($id_first_msg == $_GET['at'])
		return SplitSelectTopics();

	// Basic template information....
	$context['message'] = array(
		'id' => $_GET['at'],
		'subject' => $_REQUEST['subname']
	);
	$context['sub_template'] = 'ask';
	$context['page_title'] = $txt['split'];
}

// Alright, you've decided what you want to do with it.... now to do it.
function SplitExecute()
{
	global $txt, $board, $topic, $db_prefix, $context, $user_info, $smfFunc;

	// They blanked the subject name.
	if (!isset($_POST['subname']) || $_POST['subname'] == '')
		$_POST['subname'] = $txt['new_topic'];

	// Redirect to the selector if they chose selective.
	if ($_POST['step2'] == 'selective')
	{
		$_REQUEST['subname'] = $_POST['subname'];
		return SplitSelectTopics();
	}

	// Check the session to make sure they meant to do this.
	checkSession();

	$_POST['at'] = (int) $_POST['at'];
	$messagesToBeSplit = array();

	if ($_POST['step2'] == 'afterthis')
	{
		// Fetch the message IDs of the topic that are at or after the message.
		$request = $smfFunc['db_query']('', "
			SELECT id_msg
			FROM {$db_prefix}messages
			WHERE id_topic = $topic
				AND id_msg >= $_POST[at]", __FILE__, __LINE__);
		while ($row = $smfFunc['db_fetch_assoc']($request))
			$messagesToBeSplit[] = $row['id_msg'];
		$smfFunc['db_free_result']($request);
	}
	// Only the selected message has to be split. That should be easy.
	elseif ($_POST['step2'] == 'onlythis')
		$messagesToBeSplit[] = $_POST['at'];
	// There's another action?!
	else
		fatal_lang_error('no_access', false);

	$context['old_topic'] = $topic;
	$context['new_topic'] = splitTopic($topic, $messagesToBeSplit, $_POST['subname']);
	$context['page_title'] = $txt['split'];
}

// Get a selective list of topics...
function SplitSelectTopics()
{
	global $txt, $scripturl, $topic, $db_prefix, $context, $modSettings, $original_msgs, $smfFunc;

	// For determining what they can see...
	$approveQuery = allowedTo('approve_posts') ? '1=1' : 'approved = 1';

	$context['page_title'] = $txt['split'] . ' - ' . $txt['select_split_posts'];

	// Haven't selected anything have we?
	$_SESSION['split_selection'][$topic] = empty($_SESSION['split_selection'][$topic]) ? array() : $_SESSION['split_selection'][$topic];

	$context['not_selected'] = array(
		'num_messages' => 0,
		'start' => empty($_REQUEST['start']) ? 0 : (int) $_REQUEST['start'],
		'messages' => array(),
	);

	$context['selected'] = array(
		'num_messages' => 0,
		'start' => empty($_REQUEST['start2']) ? 0 : (int) $_REQUEST['start2'],
		'messages' => array(),
	);

	$context['topic'] = array(
		'id' => $topic,
		'subject' => urlencode($_REQUEST['subname']),
	);

	// Some stuff for our favorite template.
	$context['new_subject'] = $smfFunc['db_unescape_string']($_REQUEST['subname']);

	// Using the "select" sub template.
	$context['sub_template'] = isset($_REQUEST['xml']) ? 'split' : 'select';

	// Get the message ID's from before the move.
	if (isset($_REQUEST['xml']))
	{
		$original_msgs = array(
			'not_selected' => array(),
			'selected' => array(),
		);
		$request = $smfFunc['db_query']('', "
			SELECT id_msg
			FROM {$db_prefix}messages
			WHERE id_topic = $topic" . (empty($_SESSION['split_selection'][$topic]) ? '' : "
				AND id_msg NOT IN (" . implode(', ', $_SESSION['split_selection'][$topic]) . ')') . "
				AND $approveQuery
			ORDER BY id_msg DESC
			LIMIT " . $context['not_selected']['start'] . ", $modSettings[defaultMaxMessages]", __FILE__, __LINE__);
		// You can't split the last message off.
		if (empty($context['not_selected']['start']) && $smfFunc['db_num_rows']($request) <= 1 && $_REQUEST['move'] == 'down')
			$_REQUEST['move'] = '';
		while ($row = $smfFunc['db_fetch_assoc']($request))
			$original_msgs['not_selected'][] = $row['id_msg'];
		$smfFunc['db_free_result']($request);
		if (!empty($_SESSION['split_selection'][$topic]))
		{
			$request = $smfFunc['db_query']('', "
				SELECT id_msg
				FROM {$db_prefix}messages
				WHERE id_topic = $topic
					AND id_msg IN (" . implode(', ', $_SESSION['split_selection'][$topic]) . ")
					AND $approveQuery
				ORDER BY id_msg DESC
				LIMIT " . $context['selected']['start'] . ", $modSettings[defaultMaxMessages]", __FILE__, __LINE__);
			while ($row = $smfFunc['db_fetch_assoc']($request))
				$original_msgs['selected'][] = $row['id_msg'];
			$smfFunc['db_free_result']($request);
		}
	}

	// (De)select a message..
	if (!empty($_REQUEST['move']))
	{
		$_REQUEST['msg'] = (int) $_REQUEST['msg'];

		if ($_REQUEST['move'] == 'reset')
			$_SESSION['split_selection'][$topic] = array();
		elseif ($_REQUEST['move'] == 'up')
			$_SESSION['split_selection'][$topic] = array_diff($_SESSION['split_selection'][$topic], array($_REQUEST['msg']));
		else
			$_SESSION['split_selection'][$topic][] = $_REQUEST['msg'];
	}

	// Make sure the selection is still accurate.
	if (!empty($_SESSION['split_selection'][$topic]))
	{
		$request = $smfFunc['db_query']('', "
			SELECT id_msg
			FROM {$db_prefix}messages
			WHERE id_topic = $topic
				AND id_msg IN (" . implode(', ', $_SESSION['split_selection'][$topic]) . ")
				AND $approveQuery", __FILE__, __LINE__);
		$_SESSION['split_selection'][$topic] = array();
		while ($row = $smfFunc['db_fetch_assoc']($request))
			$_SESSION['split_selection'][$topic][] = $row['id_msg'];
		$smfFunc['db_free_result']($request);
	}

	// Get the number of messages (not) selected to be split.
	$request = $smfFunc['db_query']('', "
		SELECT " . (empty($_SESSION['split_selection'][$topic]) ? '0' : 'm.id_msg IN (' .implode(', ', $_SESSION['split_selection'][$topic]) . ')') . " AS is_selected, COUNT(*) AS num_messages
		FROM {$db_prefix}messages AS m
		WHERE m.id_topic = $topic
			AND $approveQuery
		GROUP BY is_selected", __FILE__, __LINE__);
	while ($row = $smfFunc['db_fetch_assoc']($request))
		$context[empty($row['is_selected']) ? 'not_selected' : 'selected']['num_messages'] = $row['num_messages'];
	$smfFunc['db_free_result']($request);

	// Fix an oversized starting page (to make sure both pageindexes are properly set).
	if ($context['selected']['start'] >= $context['selected']['num_messages'])
		$context['selected']['start'] = $context['selected']['num_messages'] <= $modSettings['defaultMaxMessages'] ? 0 : ($context['selected']['num_messages'] - (($context['selected']['num_messages'] % $modSettings['defaultMaxMessages']) == 0 ? $modSettings['defaultMaxMessages'] : ($context['selected']['num_messages'] % $modSettings['defaultMaxMessages'])));

	// Build a page list of the not-selected topics...
	$context['not_selected']['page_index'] = constructPageIndex($scripturl . '?action=splittopics;sa=selectTopics;subname=' . strtr(urlencode($_REQUEST['subname']), array('%' => '%%')) . ';topic=' . $topic . '.%d;start2=' . $context['selected']['start'], $context['not_selected']['start'], $context['not_selected']['num_messages'], $modSettings['defaultMaxMessages'], true);
	// ...and one of the selected topics.
	$context['selected']['page_index'] = constructPageIndex($scripturl . '?action=splittopics;sa=selectTopics;subname=' . strtr(urlencode($_REQUEST['subname']), array('%' => '%%')) . ';topic=' . $topic . '.' . $context['not_selected']['start'] . ';start2=%d', $context['selected']['start'], $context['selected']['num_messages'], $modSettings['defaultMaxMessages'], true);

	// Get the messages and stick them into an array.
	$request = $smfFunc['db_query']('', "
		SELECT m.subject, IFNULL(mem.real_name, m.poster_name) AS real_name, m.body, m.id_msg, m.smileys_enabled
		FROM {$db_prefix}messages AS m
			LEFT JOIN {$db_prefix}members AS mem ON (mem.id_member = m.id_member)
		WHERE m.id_topic = $topic" . (empty($_SESSION['split_selection'][$topic]) ? '' : "
			AND id_msg NOT IN (" . implode(', ', $_SESSION['split_selection'][$topic]) . ')') . "
			AND $approveQuery
		ORDER BY m.id_msg DESC
		LIMIT " . $context['not_selected']['start'] . ", $modSettings[defaultMaxMessages]", __FILE__, __LINE__);
	$context['messages'] = array();
	while ($row = $smfFunc['db_fetch_assoc']($request))
	{
		censorText($row['subject']);
		censorText($row['body']);

		$row['body'] = parse_bbc($row['body'], $row['smileys_enabled'], $row['id_msg']);

		$context['not_selected']['messages'][$row['id_msg']] = array(
			'id' => $row['id_msg'],
			'subject' => $row['subject'],
			'body' => $row['body'],
			'poster' => $row['real_name'],
		);
	}
	$smfFunc['db_free_result']($request);

	// Now get the selected messages.
	if (!empty($_SESSION['split_selection'][$topic]))
	{
		// Get the messages and stick them into an array.
		$request = $smfFunc['db_query']('', "
			SELECT m.subject, IFNULL(mem.real_name, m.poster_name) AS real_name, m.body, m.id_msg, m.smileys_enabled
			FROM {$db_prefix}messages AS m
				LEFT JOIN {$db_prefix}members AS mem ON (mem.id_member = m.id_member)
			WHERE m.id_topic = $topic
				AND id_msg IN (" . implode(', ', $_SESSION['split_selection'][$topic]) . ")
				AND $approveQuery
			ORDER BY m.id_msg DESC
			LIMIT " . $context['selected']['start'] . ", $modSettings[defaultMaxMessages]", __FILE__, __LINE__);
		$context['messages'] = array();
		while ($row = $smfFunc['db_fetch_assoc']($request))
		{
			censorText($row['subject']);
			censorText($row['body']);

			$row['body'] = parse_bbc($row['body'], $row['smileys_enabled'], $row['id_msg']);

			$context['selected']['messages'][$row['id_msg']] = array(
				'id' => $row['id_msg'],
				'subject' => $row['subject'],
				'body' => $row['body'],
				'poster' => $row['real_name']
			);
		}
		$smfFunc['db_free_result']($request);
	}

	// The XMLhttp method only needs the stuff that changed, so let's compare.
	if (isset($_REQUEST['xml']))
	{
		$changes = array(
			'remove' => array(
				'not_selected' => array_diff($original_msgs['not_selected'], array_keys($context['not_selected']['messages'])),
				'selected' => array_diff($original_msgs['selected'], array_keys($context['selected']['messages'])),
			),
			'insert' => array(
				'not_selected' => array_diff(array_keys($context['not_selected']['messages']), $original_msgs['not_selected']),
				'selected' => array_diff(array_keys($context['selected']['messages']), $original_msgs['selected']),
			),
		);

		$context['changes'] = array();
		foreach ($changes as $change_type => $change_array)
			foreach ($change_array as $section => $msg_array)
			{
				if (empty($msg_array))
					continue;

				foreach ($msg_array as $id_msg)
				{
					$context['changes'][$change_type . $id_msg] = array(
						'id' => $id_msg,
						'type' => $change_type,
						'section' => $section,
					);
					if ($change_type == 'insert')
						$context['changes']['insert' . $id_msg]['insert_value'] = $context[$section]['messages'][$id_msg];
				}
			}
	}
}

// Actually and selectively split the topics out.
function SplitSelectionExecute()
{
	global $txt, $board, $topic, $db_prefix, $context, $user_info;

	// Make sure the session id was passed with post.
	checkSession();

	// Default the subject in case it's blank.
	if (!isset($_POST['subname']) || $_POST['subname'] == '')
		$_POST['subname'] = $txt['new_topic'];

	// The old topic's ID is the current one.
	$split1_ID_TOPIC = $topic;

	// You must've selected some messages!  Can't split out none!
	if (empty($_SESSION['split_selection'][$topic]))
		fatal_lang_error('no_posts_selected', false);

	$context['old_topic'] = $topic;
	$context['new_topic'] = splitTopic($topic, $_SESSION['split_selection'][$topic], $_POST['subname']);
	$context['page_title'] = $txt['split'];
}

// Split a topic in two topics.
function splitTopic($split1_ID_TOPIC, $splitMessages, $new_subject)
{
	global $db_prefix, $user_info, $topic, $board, $modSettings;
	global $smfFunc;

	// Nothing to split?
	if (empty($splitMessages))
		fatal_lang_error('no_posts_selected', false);

	// No sense in imploding it over and over again.
	$postList = implode(',', $splitMessages);

	// Get some board info.
	$request = $smfFunc['db_query']('', "
		SELECT id_board, approved
		FROM {$db_prefix}topics
		WHERE id_topic = $split1_ID_TOPIC
		LIMIT 1", __FILE__, __LINE__);
	list ($id_board, $split1_approved) = $smfFunc['db_fetch_row']($request);
	$smfFunc['db_free_result']($request);

	// Find the new first and last not in the list. (old topic)
	$request = $smfFunc['db_query']('', "
		SELECT MIN(m.id_msg) AS myid_first_msg, MAX(m.id_msg) AS myid_last_msg, COUNT(*) AS message_count,
			m.approved
		FROM {$db_prefix}messages AS m
			INNER JOIN {$db_prefix}topics AS t ON (t.id_topic = $split1_ID_TOPIC)
		WHERE m.id_msg NOT IN ($postList)
			AND m.id_topic = $split1_ID_TOPIC
		GROUP BY m.approved
		ORDER BY m.approved DESC
		LIMIT 2", __FILE__, __LINE__);
	// You can't select ALL the messages!
	if ($smfFunc['db_num_rows']($request) == 0)
		fatal_lang_error('slected_all_posts', false);
	while ($row = $smfFunc['db_fetch_assoc']($request))
	{
		// Get the right first and last message dependant on approved state...
		if (empty($split1_first_msg) || $row['myid_first_msg'] < $split1_first_msg)
			$split1_first_msg = $row['myid_first_msg'];
		if (empty($split1_last_msg) || $row['approved'])
			$split1_last_msg = $row['myid_last_msg'];

		// Get the counts correct...
		if ($row['approved'])
		{
			$split1_replies = $row['message_count'] - 1;
			$split1_unapprovedposts = 0;
		}
		else
		{
			if (!isset($split1_replies))
				$split1_replies = 0;
			// If the topic isn't approved then num replies must go up by one... as first post wouldn't be counted.
			elseif (!$split1_approved)
				$split1_replies++;

			$split1_unapprovedposts = $row['message_count'];
		}
	}
	$smfFunc['db_free_result']($request);
	$split1_firstMem = getMsgMemberID($split1_first_msg);
	$split1_lastMem = getMsgMemberID($split1_last_msg);

	// Find the first and last in the list. (new topic)
	$request = $smfFunc['db_query']('', "
		SELECT MIN(id_msg) AS myid_first_msg, MAX(id_msg) AS myid_last_msg, COUNT(*) AS message_count,
			approved
		FROM {$db_prefix}messages
		WHERE id_msg IN ($postList)
			AND id_topic = $split1_ID_TOPIC
		GROUP BY id_topic, approved
		ORDER BY approved DESC
		LIMIT 2", __FILE__, __LINE__);
	while ($row = $smfFunc['db_fetch_assoc']($request))
	{
		// As before get the right first and last message dependant on approved state...
		if (empty($split2_first_msg) || $row['myid_first_msg'] < $split2_first_msg)
			$split2_first_msg = $row['myid_first_msg'];
		if (empty($split2_last_msg) || $row['approved'])
			$split2_last_msg = $row['myid_last_msg'];

		// Then do the counts again...
		if ($row['approved'])
		{
			$split2_approved = true;
			$split2_replies = $row['message_count'] - 1;
			$split2_unapprovedposts = 0;
		}
		else
		{
			// Should this one be approved??
			if ($split2_first_msg == $row['myid_first_msg'])
				$split2_approved = false;

			if (!isset($split2_replies))
				$split2_replies = 0;
			// As before, fix number of replies.
			elseif (!$split2_approved)
				$split2_replies++;

			$split2_unapprovedposts = $row['message_count'];
		}
	}
	$smfFunc['db_free_result']($request);
	$split2_firstMem = getMsgMemberID($split2_first_msg);
	$split2_lastMem = getMsgMemberID($split2_last_msg);

	// No database changes yet, so let's double check to see if everything makes at least a little sense.
	if ($split1_first_msg <= 0 || $split1_last_msg <= 0 || $split2_first_msg <= 0 || $split2_last_msg <= 0 || $split1_replies < 0 || $split2_replies < 0 || $split1_unapprovedposts < 0 || $split2_unapprovedposts < 0 || !isset($split1_approved) || !isset($split2_approved))
		fatal_lang_error('cant_find_messages');

	// You cannot split off the first message of a topic.
	if ($split1_first_msg > $split2_first_msg)
		fatal_lang_error('split_first_post', false);

	// We're off to insert the new topic!  Use 0 for now to avoid UNIQUE errors.
	$smfFunc['db_query']('', "
		INSERT INTO {$db_prefix}topics
			(id_board, id_member_started, id_member_updated, id_first_msg, id_last_msg, num_replies, unapproved_posts, approved, is_sticky)
		VALUES ($id_board, $split2_firstMem, $split2_lastMem, 0, 0, $split2_replies, $split2_unapprovedposts, $split2_approved, 0)", __FILE__, __LINE__);
	$split2_ID_TOPIC = $smfFunc['db_insert_id']("{$db_prefix}topics", 'id_topic');
	if ($split2_ID_TOPIC <= 0)
		fatal_lang_error('cant_insert_topic');

	// Move the messages over to the other topic.
	 $new_subject = $smfFunc['htmlspecialchars']($new_subject);
	$smfFunc['db_query']('', "
		UPDATE {$db_prefix}messages
		SET
			id_topic = $split2_ID_TOPIC,
			subject = '$new_subject'
		WHERE id_msg IN ($postList)", __FILE__, __LINE__);

	// Cache the new topics subject... we can do it now as all the subjects are the same!
	updateStats('subject', $split2_ID_TOPIC, $new_subject);

	// Any associated reported posts better follow...
	$smfFunc['db_query']('', "
		UPDATE {$db_prefix}log_reported
		SET id_topic = $split2_ID_TOPIC
		WHERE id_msg IN ($postList)", __FILE__, __LINE__);

	// Mess with the old topic's first, last, and number of messages.
	$smfFunc['db_query']('', "
		UPDATE {$db_prefix}topics
		SET
			num_replies = $split1_replies,
			id_first_msg = $split1_first_msg,
			id_last_msg = $split1_last_msg,
			id_member_started = $split1_firstMem,
			id_member_updated = $split1_lastMem,
			unapproved_posts = $split1_unapprovedposts
		WHERE id_topic = $split1_ID_TOPIC", __FILE__, __LINE__);

	// Now, put the first/last message back to what they should be.
	$smfFunc['db_query']('', "
		UPDATE {$db_prefix}topics
		SET
			id_first_msg = $split2_first_msg,
			id_last_msg = $split2_last_msg
		WHERE id_topic = $split2_ID_TOPIC", __FILE__, __LINE__);

	// If the new topic isn't approved ensure the first message flags this just in case.
	if (!$split2_approved)
		$smfFunc['db_query']('', "
			UPDATE {$db_prefix}messages
			SET approved = 0
			WHERE id_msg = $split2_first_msg
				AND id_topic = $split2_ID_TOPIC", __FILE__, __LINE__);

	// The board has more topics now (Or more unapproved ones!).
	$smfFunc['db_query']('', "
		UPDATE {$db_prefix}boards
		SET " . ($split2_approved ? "
			num_topics = num_topics + 1" : "
			unapproved_topics = unapproved_topics + 1") . "
		WHERE id_board = $id_board", __FILE__, __LINE__);

	// We're going to assume they bothered to read it before splitting it.
	if (!$user_info['is_guest'])
		$smfFunc['db_insert']('replace',
			"{$db_prefix}log_topics",
			array('id_msg', 'id_member', 'id_topic'),
			array($modSettings['maxMsgID'], $user_info['id'], $split2_ID_TOPIC),
			array('id_member', 'id_topic'), __FILE__, __LINE__
		);

	// Housekeeping.
	updateStats('topic');
	updateLastMessages($id_board);

	logAction('split', array('topic' => $split1_ID_TOPIC, 'new_topic' => $split2_ID_TOPIC, 'board' => $id_board));

	// Notify people that this topic has been split?
	sendNotifications($split1_ID_TOPIC, 'split');

	// Return the ID of the newly created topic.
	return $split2_ID_TOPIC;
}

// Merge two topics into one topic... useful if they have the same basic subject.
function MergeTopics()
{
	// Load the template....
	loadTemplate('SplitTopics');

	$subActions = array(
		'done' => 'MergeDone',
		'execute' => 'MergeExecute',
		'index' => 'MergeIndex',
		'options' => 'MergeExecute',
	);

	// ?action=mergetopics;sa=LETSBREAKIT won't work, sorry.
	if (empty($_REQUEST['sa']) || !isset($subActions[$_REQUEST['sa']]))
		MergeIndex();
	else
		$subActions[$_REQUEST['sa']]();
}

// Merge two topics together.
function MergeIndex()
{
	global $txt, $board, $context, $smfFunc;
	global $scripturl, $topic, $db_prefix, $user_info, $modSettings;

	$_REQUEST['targetboard'] = isset($_REQUEST['targetboard']) ? (int) $_REQUEST['targetboard'] : $board;
	$context['target_board'] = $_REQUEST['targetboard'];

	// Prepare a handy query bit for approval...
	$can_approve_boards = boardsAllowedTo('approve_posts');
	$approveQuery = $can_approve_boards == array(0) || in_array($_REQUEST['targetboard'], $can_approve_boards) ? 'AND t.approved = 1' : '';

	// How many topics are on this board?  (used for paging.)
	$request = $smfFunc['db_query']('', "
		SELECT COUNT(*)
		FROM {$db_prefix}topics AS t
		WHERE t.id_board = $_REQUEST[targetboard]
			$approveQuery", __FILE__, __LINE__);
	list ($topiccount) = $smfFunc['db_fetch_row']($request);
	$smfFunc['db_free_result']($request);

	// Make the page list.
	$context['page_index'] = constructPageIndex($scripturl . '?action=mergetopics;from=' . $_GET['from'] . ';targetboard=' . $_REQUEST['targetboard'] . ';board=' . $board . '.%d', $_REQUEST['start'], $topiccount, $modSettings['defaultMaxTopics'], true);

	// Get the topic's subject.
	$request = $smfFunc['db_query']('', "
		SELECT m.subject
		FROM {$db_prefix}topics AS t
			INNER JOIN {$db_prefix}messages AS m ON (m.id_msg = t.id_first_msg)
		WHERE t.id_topic = $_GET[from]
			AND t.id_board = $board
			$approveQuery
		LIMIT 1", __FILE__, __LINE__);
	if ($smfFunc['db_num_rows']($request) == 0)
		fatal_lang_error('no_board');
	list ($subject) = $smfFunc['db_fetch_row']($request);
	$smfFunc['db_free_result']($request);

	// Tell the template a few things..
	$context['origin_topic'] = $_GET['from'];
	$context['origin_subject'] = $subject;
	$context['origin_js_subject'] = addcslashes(addslashes($subject), '/');
	$context['page_title'] = $txt['merge'];

	// Check which boards you have merge permissions on.
	$merge_boards = boardsAllowedTo('merge_any');

	if (empty($merge_boards))
		fatal_lang_error('cannot_merge_any', 'user');

	// Get a list of boards they can navigate to to merge.
	$request = $smfFunc['db_query']('', "
		SELECT b.id_board, b.name AS board_name, c.name AS cat_name
		FROM {$db_prefix}boards AS b
			LEFT JOIN {$db_prefix}categories AS c ON (c.id_cat = b.id_cat)
		WHERE $user_info[query_see_board]" . (!in_array(0, $merge_boards) ? "
			AND b.id_board IN (" . implode(', ', $merge_boards) . ")" : ''), __FILE__, __LINE__);
	$context['boards'] = array();
	while ($row = $smfFunc['db_fetch_assoc']($request))
		$context['boards'][] = array(
			'id' => $row['id_board'],
			'name' => $row['board_name'],
			'category' => $row['cat_name']
		);
	$smfFunc['db_free_result']($request);

	// Get some topics to merge it with.
	$request = $smfFunc['db_query']('', "
		SELECT t.id_topic, m.subject, m.id_member, IFNULL(mem.real_name, m.poster_name) AS poster_name
		FROM {$db_prefix}topics AS t
			INNER JOIN {$db_prefix}messages AS m ON (m.id_msg = t.id_first_msg)
			LEFT JOIN {$db_prefix}members AS mem ON (mem.id_member = m.id_member)
		WHERE t.id_board = $_REQUEST[targetboard]
			AND t.id_topic != $_GET[from]
			$approveQuery
		ORDER BY " . (!empty($modSettings['enableStickyTopics']) ? 't.is_sticky DESC, ' : '') . "t.id_last_msg DESC
		LIMIT $_REQUEST[start], $modSettings[defaultMaxTopics]", __FILE__, __LINE__);
	$context['topics'] = array();
	while ($row = $smfFunc['db_fetch_assoc']($request))
	{
		censorText($row['subject']);

		$context['topics'][] = array(
			'id' => $row['id_topic'],
			'poster' => array(
				'id' => $row['id_member'],
				'name' => $row['poster_name'],
				'href' => empty($row['id_member']) ? '' : $scripturl . '?action=profile;u=' . $row['id_member'],
				'link' => empty($row['id_member']) ? $row['poster_name'] : '<a href="' . $scripturl . '?action=profile;u=' . $row['id_member'] . '" class="extern">' . $row['poster_name'] . '</a>'
			),
			'subject' => $row['subject'],
			'js_subject' => addcslashes(addslashes($row['subject']), '/')
		);
	}
	$smfFunc['db_free_result']($request);

	if (empty($context['topics']) && count($context['boards']) <= 1)
		fatal_lang_error('merge_need_more_topics');

	$context['sub_template'] = 'merge';
}

// Now that the topic IDs are known, do the proper merging.
function MergeExecute($topics = array())
{
	global $db_prefix, $user_info, $txt, $context, $scripturl, $sourcedir;
	global $smfFunc, $language, $modSettings;

	// The parameters of MergeExecute were set, so this must've been an internal call.
	if (!empty($topics))
	{
		isAllowedTo('merge_any');
		loadTemplate('SplitTopics');
	}

	// Handle URLs from MergeIndex.
	if (!empty($_GET['from']) && !empty($_GET['to']))
		$topics = array($_GET['from'], $_GET['to']);

	// If we came from a form, the topic IDs came by post.
	if (!empty($_POST['topics']) && is_array($_POST['topics']))
		$topics = $_POST['topics'];

	// There's nothing to merge with just one topic...
	if (empty($topics) || !is_array($topics) || count($topics) == 1)
		fatal_lang_error('merge_need_more_topics');

	// Make sure every topic is numeric, or some nasty things could be done with the DB.
	foreach ($topics as $id => $topic)
		$topics[$id] = (int) $topic;

	// Joy of all joys, make sure they're not pi**ing about with unapproved topics they can't see :P
	$can_approve_boards = boardsAllowedTo('approve_posts');

	// Get info about the topics and polls that will be merged.
	$request = $smfFunc['db_query']('', "
		SELECT
			t.id_topic, t.id_board, t.id_poll, t.num_views, t.is_sticky, t.approved, t.num_replies, t.unapproved_posts,
			m1.subject, m1.poster_time AS time_started, IFNULL(mem1.id_member, 0) AS id_member_started, IFNULL(mem1.real_name, m1.poster_name) AS name_started,
			m2.poster_time AS time_updated, IFNULL(mem2.id_member, 0) AS id_member_updated, IFNULL(mem2.real_name, m2.poster_name) AS name_updated
		FROM {$db_prefix}topics AS t
			INNER JOIN {$db_prefix}messages AS m1 ON (m1.id_msg = t.id_first_msg)
			INNER JOIN {$db_prefix}messages AS m2 ON (m2.id_msg = t.id_last_msg)
			LEFT JOIN {$db_prefix}members AS mem1 ON (mem1.id_member = m1.id_member)
			LEFT JOIN {$db_prefix}members AS mem2 ON (mem2.id_member = m2.id_member)
		WHERE t.id_topic IN (" . implode(', ', $topics) . ")
		ORDER BY t.id_first_msg
		LIMIT " . count($topics), __FILE__, __LINE__);
	if ($smfFunc['db_num_rows']($request) < 2)
		fatal_lang_error('no_topic_id');
	$num_views = 0;
	$is_sticky = 0;
	$boardTotals = array();
	$boards = array();
	$polls = array();
	while ($row = $smfFunc['db_fetch_assoc']($request))
	{
		// Make a note for the board counts...
		if (!isset($boardTotals[$row['id_board']]))
			$boardTotals[$row['id_board']] = array(
				'posts' => 0,
				'topics' => 0,
				'unapproved_posts' => 0,
				'unapproved_topics' => 0
			);

		// We can't see unapproved topics here?
		if (!$row['approved'] && $can_approve_boards != array(0) && in_array($row['id_board'], $can_approve_boards))
			continue;
		elseif (!$row['approved'])
			$boardTotals[$row['id_board']]['unapproved_topics']++;
		else
			$boardTotals[$row['id_board']]['topics']++;

		$boardTotals[$row['id_board']]['unapproved_posts'] += $row['unapproved_posts'];
		$boardTotals[$row['id_board']]['posts'] += $row['num_replies'] + ($row['approved'] ? 1 : 0);

		$topic_data[$row['id_topic']] = array(
			'id' => $row['id_topic'],
			'board' => $row['id_board'],
			'poll' => $row['id_poll'],
			'num_views' => $row['num_views'],
			'subject' => $row['subject'],
			'started' => array(
				'time' => timeformat($row['time_started']),
				'timestamp' => forum_time(true, $row['time_started']),
				'href' => empty($row['id_member_started']) ? '' : $scripturl . '?action=profile;u=' . $row['id_member_started'],
				'link' => empty($row['id_member_started']) ? $row['name_started'] : '<a href="' . $scripturl . '?action=profile;u=' . $row['id_member_started'] . '">' . $row['name_started'] . '</a>'
			),
			'updated' => array(
				'time' => timeformat($row['time_updated']),
				'timestamp' => forum_time(true, $row['time_updated']),
				'href' => empty($row['id_member_updated']) ? '' : $scripturl . '?action=profile;u=' . $row['id_member_updated'],
				'link' => empty($row['id_member_updated']) ? $row['name_updated'] : '<a href="' . $scripturl . '?action=profile;u=' . $row['id_member_updated'] . '">' . $row['name_updated'] . '</a>'
			)
		);
		$num_views += $row['num_views'];
		$boards[] = $row['id_board'];

		// If there's no poll, id_poll == 0...
		if ($row['id_poll'] > 0)
			$polls[] = $row['id_poll'];
		// Store the id_topic with the lowest id_first_msg.
		if (empty($firstTopic))
			$firstTopic = $row['id_topic'];

		$is_sticky = max($is_sticky, $row['is_sticky']);
	}
	$smfFunc['db_free_result']($request);

	// If we didn't get any topics then they've been messing with unapproved stuff.
	if (empty($topic_data))
		fatal_lang_error('no_topic_id');

	$boards = array_values(array_unique($boards));

	// Get the boards a user is allowed to merge in.
	$merge_boards = boardsAllowedTo('merge_any');
	if (empty($merge_boards))
		fatal_lang_error('cannot_merge_any', 'user');

	// Make sure they can see all boards....
	$request = $smfFunc['db_query']('', "
		SELECT b.id_board
		FROM {$db_prefix}boards AS b
		WHERE b.id_board IN (" . implode(', ', $boards) . ")
			AND $user_info[query_see_board]" . (!in_array(0, $merge_boards) ? "
			AND b.id_board IN (" . implode(', ', $merge_boards) . ")" : '') . "
		LIMIT " . count($boards), __FILE__, __LINE__);
	// If the number of boards that's in the output isn't exactly the same as we've put in there, you're in trouble.
	if ($smfFunc['db_num_rows']($request) != count($boards))
		fatal_lang_error('no_board');
	$smfFunc['db_free_result']($request);

	if (empty($_REQUEST['sa']) || $_REQUEST['sa'] == 'options')
	{
		if (count($polls) > 1)
		{
			$request = $smfFunc['db_query']('', "
				SELECT t.id_topic, t.id_poll, m.subject, p.question
				FROM {$db_prefix}polls AS p
					INNER JOIN {$db_prefix}topics AS t ON (t.id_poll = p.id_poll)
					INNER JOIN {$db_prefix}messages AS m ON (m.id_msg = t.id_first_msg)
				WHERE p.id_poll IN (" . implode(', ', $polls) . ")
				LIMIT " . count($polls), __FILE__, __LINE__);
			while ($row = $smfFunc['db_fetch_assoc']($request))
				$context['polls'][] = array(
					'id' => $row['id_poll'],
					'topic' => array(
						'id' => $row['id_topic'],
						'subject' => $row['subject']
					),
					'question' => $row['question'],
					'selected' => $row['id_topic'] == $firstTopic
				);
			$smfFunc['db_free_result']($request);
		}
		if (count($boards) > 1)
		{
			$request = $smfFunc['db_query']('', "
				SELECT id_board, name
				FROM {$db_prefix}boards
				WHERE id_board IN (" . implode(', ', $boards) . ")
				ORDER BY name
				LIMIT " . count($boards), __FILE__, __LINE__);
			while ($row = $smfFunc['db_fetch_assoc']($request))
				$context['boards'][] = array(
					'id' => $row['id_board'],
					'name' => $row['name'],
					'selected' => $row['id_board'] == $topic_data[$firstTopic]['board']
				);
			$smfFunc['db_free_result']($request);
		}

		$context['topics'] = $topic_data;
		foreach ($topic_data as $id => $topic)
			$context['topics'][$id]['selected'] = $topic['id'] == $firstTopic;

		$context['page_title'] = $txt['merge'];
		$context['sub_template'] = 'merge_extra_options';
		return;
	}

	// Determine target board.
	$target_board = count($boards) > 1 ? (int) $_REQUEST['board'] : $boards[0];
	if (!in_array($target_board, $boards))
		fatal_lang_error('no_board');

	// Determine which poll will survive and which polls won't.
	$target_poll = count($polls) > 1 ? (int) $_POST['poll'] : (count($polls) == 1 ? $polls[0] : 0);
	if ($target_poll > 0 && !in_array($target_poll, $polls))
		fatal_lang_error('no_access', false);
	$deleted_polls = empty($target_poll) ? $polls : array_diff($polls, array($target_poll));

	// Determine the subject of the newly merged topic - was a custom subject specified?
	if (empty($_POST['subject']) && isset($_POST['custom_subject']) && $_POST['custom_subject'] != '')
		$target_subject = $smfFunc['htmlspecialchars']($_POST['custom_subject']);
	// A subject was selected from the list.
	elseif (!empty($topic_data[(int) $_POST['subject']]['subject']))
		$target_subject = $smfFunc['db_escape_string']($topic_data[(int) $_POST['subject']]['subject']);
	// Nothing worked? Just take the subject of the first message.
	else
		$target_subject = $smfFunc['db_escape_string']($topic_data[$firstTopic]['subject']);

	// Get the first and last message and the number of messages....
	$request = $smfFunc['db_query']('', "
		SELECT approved, MIN(id_msg) AS first_msg, MAX(id_msg) AS last_msg, COUNT(*) AS message_count
		FROM {$db_prefix}messages
		WHERE id_topic IN (" . implode(', ', $topics) . ")
		GROUP BY approved
		ORDER BY approved DESC", __FILE__, __LINE__);
	$topic_approved = 1;
	while ($row = $smfFunc['db_fetch_assoc']($request))
	{
		// If this is approved, or is fully unapproved.
		if ($row['approved'] || !isset($first_msg))
		{
			$first_msg = $row['first_msg'];
			$last_msg = $row['last_msg'];
			if ($row['approved'])
			{
				$num_replies = $row['message_count'] - 1;
				$num_unapproved = 0;
			}
			else
			{
				$topic_approved = 0;
				$num_replies = 0;
				$num_unapproved = $row['message_count'];
			}
		}
		else
		{
			// If this has a lower first_msg then the first post is not approved and hence the number of replies was wrong!
			if ($first_msg > $row['first_msg'])
			{
				$first_msg = $row['first_msg'];
				$num_replies++;
				$topic_approved = 0;
			}
			$num_unapproved = $row['message_count'];
		}
	}
	$smfFunc['db_free_result']($request);

	// Ensure we have a board stat for the target board.
	if (!isset($boardTotals[$target_board]))
	{
		$boardTotals[$target_board] = array(
			'posts' => 0,
			'topics' => 0,
			'unapproved_posts' => 0,
			'unapproved_topics' => 0
		);
	}

	// Fix the topic count stuff depending on what the new one counts as.
	if ($topic_approved)
		$boardTotals[$target_board]['topics']--;
	else
		$boardTotals[$target_board]['unapproved_topics']--;

	$boardTotals[$target_board]['unapproved_posts'] -= $num_unapproved;
	$boardTotals[$target_board]['posts'] -= $topic_approved ? $num_replies + 1 : $num_replies;

	// Get the member ID of the first and last message.
	$request = $smfFunc['db_query']('', "
		SELECT id_member
		FROM {$db_prefix}messages
		WHERE id_msg IN ($first_msg, $last_msg)
		ORDER BY id_msg
		LIMIT 2", __FILE__, __LINE__);
	list ($member_started) = $smfFunc['db_fetch_row']($request);
	list ($member_updated) = $smfFunc['db_fetch_row']($request);
	$smfFunc['db_free_result']($request);

	// Assign the first topic ID to be the merged topic.
	$id_topic = min($topics);

	// Delete the remaining topics.
	$deleted_topics = array_diff($topics, array($id_topic));
	$smfFunc['db_query']('', "
		DELETE FROM {$db_prefix}topics
		WHERE id_topic IN (" . implode(', ', $deleted_topics) . ")", __FILE__, __LINE__);
	$smfFunc['db_query']('', "
		DELETE FROM {$db_prefix}log_search_subjects
		WHERE id_topic IN (" . implode(', ', $deleted_topics) . ")", __FILE__, __LINE__);

	// Asssign the properties of the newly merged topic.
	$smfFunc['db_query']('', "
		UPDATE {$db_prefix}topics
		SET
			id_board = $target_board,
			id_member_started = $member_started,
			id_member_updated = $member_updated,
			id_first_msg = $first_msg,
			id_last_msg = $last_msg,
			id_poll = $target_poll,
			num_replies = $num_replies,
			unapproved_posts = $num_unapproved,
			num_views = $num_views,
			is_sticky = $is_sticky,
			approved = $topic_approved
		WHERE id_topic = $id_topic", __FILE__, __LINE__);

	// Grab the response prefix (like 'Re: ') in the default forum language.	
	if (!isset($context['response_prefix']) && !($context['response_prefix'] = cache_get_data('response_prefix')))
	{
		if ($language === $user_info['language'])
			$context['response_prefix'] = $txt['response_prefix'];
		else
		{
			loadLanguage('index', $language, false);
			$context['response_prefix'] = $txt['response_prefix'];
			loadLanguage('index');
		}
		cache_put_data('response_prefix', $context['response_prefix'], 600);
	}

	// Change the topic IDs of all messages that will be merged.  Also adjust subjects if 'enforce subject' was checked.
	$smfFunc['db_query']('', "
		UPDATE {$db_prefix}messages
		SET
			id_topic = $id_topic,
			id_board = $target_board" . (empty($_POST['enforce_subject']) ? '' : ",
			subject = '$context[response_prefix]$target_subject'") . "
		WHERE id_topic IN (" . implode(', ', $topics) . ")", __FILE__, __LINE__);

	// Any reported posts should reflect the new board.
	$smfFunc['db_query']('', "
		UPDATE {$db_prefix}log_reported
		SET
			id_topic = $id_topic,
			id_board = $target_board
		WHERE id_topic IN (" . implode(', ', $topics) . ")", __FILE__, __LINE__);

	// Change the subject of the first message...
	$smfFunc['db_query']('', "
		UPDATE {$db_prefix}messages
		SET subject = '$target_subject'
		WHERE id_msg = $first_msg", __FILE__, __LINE__);

	// Adjust all calendar events to point to the new topic.
	$smfFunc['db_query']('', "
		UPDATE {$db_prefix}calendar
		SET
			id_topic = $id_topic,
			id_board = $target_board
		WHERE id_topic IN (" . implode(', ', $deleted_topics) . ")", __FILE__, __LINE__);

	// Merge log topic entries.
	$request = $smfFunc['db_query']('', "
		SELECT id_member, MIN(id_msg) AS new_id_msg
		FROM {$db_prefix}log_topics
		WHERE id_topic IN (" . implode(', ', $topics) . ")
		GROUP BY id_member", __FILE__, __LINE__);
	if ($smfFunc['db_num_rows']($request) > 0)
	{
		$replaceEntries = array();
		while ($row = $smfFunc['db_fetch_assoc']($request))
			$replaceEntries[] = array($row['id_member'], $id_topic, $row['new_id_msg']);

		$smfFunc['db_insert']('replace',
			"{$db_prefix}log_topics",
			array('id_member', 'id_topic', 'id_msg'),
			$replaceEntries,
			array('id_member', 'id_topic'), __FILE__, __LINE__
		);
		unset($replaceEntries);

		// Get rid of the old log entries.
		$smfFunc['db_query']('', "
			DELETE FROM {$db_prefix}log_topics
			WHERE id_topic IN (" . implode(', ', $deleted_topics) . ")", __FILE__, __LINE__);
	}
	$smfFunc['db_free_result']($request);

	// Merge topic notifications.
	if (!empty($_POST['notifications']) && is_array($_POST['notifications']))
	{
		// Check if the notification array contains valid topics.
		if (count(array_diff($_POST['notifications'], $topics)) > 0)
			fatal_lang_error('no_board');
		$request = $smfFunc['db_query']('', "
			SELECT id_member, MAX(sent) AS sent
			FROM {$db_prefix}log_notify
			WHERE id_topic IN (" . implode(', ', $_POST['notifications']) . ")
			GROUP BY id_member", __FILE__, __LINE__);
		if ($smfFunc['db_num_rows']($request) > 0)
		{
			$replaceEntries = array();
			while ($row = $smfFunc['db_fetch_assoc']($request))
				$replaceEntries[] = array($row['id_member'], $id_topic, 0, $row['sent']);

			$smfFunc['db_insert']('replace',
					"{$db_prefix}log_notify",
					array('id_member', 'id_topic', 'id_board', 'sent'),
					$replaceEntries,
					array('id_member', 'id_topic', 'id_board'), __FILE__, __LINE__
				);
			unset($replaceEntries);

			$smfFunc['db_query']('', "
				DELETE FROM {$db_prefix}log_topics
				WHERE id_topic IN (" . implode(', ', $deleted_topics) . ")", __FILE__, __LINE__);
		}
		$smfFunc['db_free_result']($request);
	}

	// Get rid of the redundant polls.
	if (!empty($deleted_polls))
	{
		$smfFunc['db_query']('', "
			DELETE FROM {$db_prefix}polls
			WHERE id_poll IN (" . implode(', ', $deleted_polls) . ")", __FILE__, __LINE__);
		$smfFunc['db_query']('', "
			DELETE FROM {$db_prefix}poll_choices
			WHERE id_poll IN (" . implode(', ', $deleted_polls) . ")", __FILE__, __LINE__);
		$smfFunc['db_query']('', "
			DELETE FROM {$db_prefix}log_polls
			WHERE id_poll IN (" . implode(', ', $deleted_polls) . ")", __FILE__, __LINE__);
	}

	// Cycle through each board...
	foreach ($boardTotals as $id_board => $stats)
	{
		$smfFunc['db_query']('', "
			UPDATE {$db_prefix}boards
			SET
				num_topics = CASE WHEN $stats[topics] > num_topics THEN 0 ELSE num_topics - $stats[topics] END,
				unapproved_topics = CASE WHEN $stats[unapproved_topics] > unapproved_topics THEN 0 ELSE unapproved_topics - $stats[unapproved_topics] END,
				num_posts = CASE WHEN $stats[posts] > num_posts THEN 0 ELSE num_posts - $stats[posts] END,
				unapproved_posts = CASE WHEN $stats[unapproved_posts] > unapproved_posts THEN 0 ELSE unapproved_posts - $stats[unapproved_posts] END
			WHERE id_board = $id_board", __FILE__, __LINE__);
	}

	// Determine the board the final topic resides in
	$request = $smfFunc['db_query']('', "
		SELECT id_board
		FROM {$db_prefix}topics
		WHERE id_topic=$id_topic
		LIMIT 1", __FILE__, __LINE__);
	list($id_board) = $smfFunc['db_fetch_row']($request);
	$smfFunc['db_free_result']($request);

	require_once($sourcedir . '/Subs-Post.php');

	// Update all the statistics.
	updateStats('topic');
	updateStats('subject', $id_topic, $target_subject);
	updateLastMessages($boards);

	logAction('merge', array('topic' => $id_topic, 'board' => $id_board));

	// Notify people that these topics have been merged?
	sendNotifications($id_topic, 'merge');

	// Send them to the all done page.
	redirectexit('action=mergetopics;sa=done;to=' . $id_topic . ';targetboard=' . $target_board);
}

// Tell the user the move was done properly.
function MergeDone()
{
	global $txt, $context;

	// Make sure the template knows everything...
	$context['target_board'] = $_GET['targetboard'];
	$context['target_topic'] = $_GET['to'];

	$context['page_title'] = $txt['merge'];
	$context['sub_template'] = 'merge_done';
}

?>