<?php
/**********************************************************************************
* RepairBoards.php                                                                *
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

/*	This is here for the "repair any errors" feature in the admin center.  It
	uses just two simple functions:

	void RepairBoards()
		- finds or repairs errors in the database to fix possible problems.
		- requires the admin_forum permission.
		- uses the raw_data sub template.
		- calls createSalvageArea() to create a new board, if necesary.
		- accessed by ?action=admin;area=repairboards.

	void pauseRepairProcess(array to_fix, int max_substep = none)
		- show the not_done template to avoid CGI timeouts and similar.
		- called when 3 or more seconds have passed while searching for errors.
		- if max_substep is set, $_GET['substep'] / $max_substep is the percent
		  done this step is.

	array findForumErrors()
		- checks for errors in steps, until 5 seconds have passed.
		- keeps track of the errors it did find, so that the actual repair
		  won't have to recheck everything.
		- returns the array of errors found.

	void createSalvageArea()
		- creates a salvage board/category if one doesn't already exist.
		- uses the forum's default language, and checks based on that name.
*/

function RepairBoards()
{
	global $db_prefix, $txt, $scripturl, $db_connection, $sc, $context, $sourcedir;
	global $salvageCatID, $salvageBoardID, $smfFunc;

	isAllowedTo('admin_forum');

	// Print out the top of the webpage.
	$context['page_title'] = $txt[610];
	$context['sub_template'] = 'rawdata';

	// Start displaying errors without fixing them.
	if (isset($_GET['fixErrors']))
		checkSession('get');

	// Giant if/else. The first displays the forum errors if a variable is not set and asks
	// if you would like to continue, the other fixes the errors.
	if (!isset($_GET['fixErrors']))
	{
		$context['repair_errors'] = array();
		$to_fix = findForumErrors();
		if (!empty($to_fix))
		{
			$_SESSION['repairboards_to_fix'] = $to_fix;
			$_SESSION['repairboards_to_fix2'] = null;

			if (empty($context['repair_errors']))
				$context['repair_errors'][] = '???';
		}

		$context['raw_data'] = '
			<table width="100%" border="0" cellspacing="0" cellpadding="4" class="tborder">
				<tr class="titlebg">
					<td>' . $txt['smf73'] . '</td>
				</tr><tr>
					<td class="windowbg">';

		if (!empty($to_fix))
		{
			$context['raw_data'] .= '
						' . $txt['smf74'] . ':<br />
						' . implode('
						<br />', $context['repair_errors']) . '<br />
						<br />
						' . $txt['smf85'] . '<br />
						<b><a href="' . $scripturl . '?action=admin;area=repairboards;fixErrors;sesc=' . $sc . '">' . $txt['yes'] . '</a> - <a href="' . $scripturl . '?action=admin;area=maintain">' . $txt['no'] . '</a></b>';
		}
		else
			$context['raw_data'] .= '
						' . $txt['maintain_no_errors'] . '<br />
						<br />
						<a href="' . $scripturl . '?action=admin;area=maintain">' . $txt['maintain_return'] . '</a>';

		$context['raw_data'] .= '
					</td>
				</tr>
			</table>';
	}
	else
	{
		$to_fix = isset($_SESSION['repairboards_to_fix']) ? $_SESSION['repairboards_to_fix'] : array();

		require_once($sourcedir . '/Subs-Boards.php');

		// Get the MySQL version for future reference.
		$mysql_version = $smfFunc['db_server_info']($db_connection);

		if (empty($to_fix) || in_array('zero_ids', $to_fix))
		{
			// We don't allow 0's in the IDs...
			$smfFunc['db_query']('', "
				UPDATE {$db_prefix}topics
				SET id_topic = NULL
				WHERE id_topic = 0", __FILE__, __LINE__);

			$smfFunc['db_query']('', "
				UPDATE {$db_prefix}messages
				SET id_msg = NULL
				WHERE id_msg = 0", __FILE__, __LINE__);
		}

		// Remove all topics that have zero messages in the messages table.
		if (empty($to_fix) || in_array('missing_messages', $to_fix))
		{
			$resultTopic = $smfFunc['db_query']('', "
				SELECT t.id_topic, COUNT(m.id_msg) AS num_msg
				FROM {$db_prefix}topics AS t
					LEFT JOIN {$db_prefix}messages AS m ON (m.id_topic = t.id_topic)
				GROUP BY t.id_topic
				HAVING COUNT(m.id_msg) = 0", __FILE__, __LINE__);
			if ($smfFunc['db_num_rows']($resultTopic) > 0)
			{
				$stupidTopics = array();
				while ($topicArray = $smfFunc['db_fetch_assoc']($resultTopic))
					$stupidTopics[] = $topicArray['id_topic'];
				$smfFunc['db_query']('', "
					DELETE FROM {$db_prefix}topics
					WHERE id_topic IN (" . implode(',', $stupidTopics) . ')', __FILE__, __LINE__);
				$smfFunc['db_query']('', "
					DELETE FROM {$db_prefix}log_topics
					WHERE id_topic IN (" . implode(',', $stupidTopics) . ')', __FILE__, __LINE__);
			}
			$smfFunc['db_free_result']($resultTopic);
		}

		// Fix all messages that have a topic ID that cannot be found in the topics table.
		if (empty($to_fix) || in_array('missing_topics', $to_fix))
		{
			$result = $smfFunc['db_query']('', "
				SELECT
					m.id_board, m.id_topic, MIN(m.id_msg) AS myid_first_msg, MAX(m.id_msg) AS myid_last_msg,
					COUNT(*) - 1 AS myNumReplies
				FROM {$db_prefix}messages AS m
					LEFT JOIN {$db_prefix}topics AS t ON (t.id_topic = m.id_topic)
				WHERE t.id_topic IS NULL
				GROUP BY m.id_topic", __FILE__, __LINE__);
			while ($row = $smfFunc['db_fetch_assoc']($result))
			{
				// Only if we don't have a reasonable idea of where to put it.
				if ($row['id_board'] == 0)
				{
					createSalvageArea();
					$row['id_board'] = $salvageBoardID;
				}

				$memberStartedID = getMsgMemberID($row['myid_first_msg']);
				$memberUpdatedID = getMsgMemberID($row['myid_last_msg']);

				$smfFunc['db_query']('', "
					INSERT INTO {$db_prefix}topics
						(id_board, id_member_started, id_member_updated, id_first_msg, id_last_msg, num_replies)
					VALUES ($row[id_board], $memberStartedID, $memberUpdatedID,
						$row[myid_first_msg], $row[myid_last_msg], $row[myNumReplies])", __FILE__, __LINE__);
				$newTopicID = db_insert_id("{$db_prefix}topics", 'id_topic');

				$smfFunc['db_query']('', "
					UPDATE {$db_prefix}messages
					SET id_topic = $newTopicID, id_board = $row[id_board]
					WHERE id_topic = $row[id_topic]", __FILE__, __LINE__);
			}
			$smfFunc['db_free_result']($result);

			// Force the check of unapproved posts for this.
			$to_fix[] = 'stats_topics';
		}

		// Fix all id_first_msg, id_last_msg in the topic table.
		if (empty($to_fix) || in_array('stats_topics', $to_fix))
		{
			$resultTopic = $smfFunc['db_query']('', "
				SELECT
					t.id_topic, t.id_first_msg, t.id_last_msg,
					IF (MIN(ma.id_msg),
						IF (MIN(mu.id_msg),
							IF (MIN(mu.id_msg) < MIN(ma.id_msg), mu.id_msg, ma.id_msg),
						MIN(ma.id_msg)),
					MIN(mu.id_msg)) AS myid_first_msg,
					IF (MAX(ma.id_msg), MAX(ma.id_msg), MIN(mu.id_msg)) AS myid_last_msg,
					t.approved, mf.approved AS myApproved
				FROM {$db_prefix}topics AS t
					LEFT JOIN {$db_prefix}messages AS ma ON (ma.id_topic = t.id_topic AND ma.approved = 1)
					LEFT JOIN {$db_prefix}messages AS mu ON (mu.id_topic = t.id_topic AND mu.approved = 0)
					LEFT JOIN {$db_prefix}messages AS mf ON (mf.id_msg = t.id_first_msg)
				GROUP BY t.id_topic
				HAVING id_first_msg != myid_first_msg OR id_last_msg != myid_last_msg
					OR approved != myApproved", __FILE__, __LINE__);
			while ($topicArray = $smfFunc['db_fetch_assoc']($resultTopic))
			{
				$memberStartedID = getMsgMemberID($topicArray['myid_first_msg']);
				$memberUpdatedID = getMsgMemberID($topicArray['myid_last_msg']);
				$smfFunc['db_query']('', "
					UPDATE {$db_prefix}topics
					SET id_first_msg = '$topicArray[myid_first_msg]',
						id_member_started = '$memberStartedID', id_last_msg = '$topicArray[myid_last_msg]',
						id_member_updated = '$memberUpdatedID', approved = '$topicArray[myApproved]'
					WHERE id_topic = $topicArray[id_topic]", __FILE__, __LINE__);
			}
			$smfFunc['db_free_result']($resultTopic);
		}

		// Fix all num_replies in the topic table.
		if (empty($to_fix) || in_array('stats_topics2', $to_fix))
		{
			$resultTopic = $smfFunc['db_query']('', "
				SELECT
					t.id_topic, t.num_replies,
					IF (COUNT(ma.id_msg), IF (mf.approved, COUNT(ma.id_msg) - 1, COUNT(ma.id_msg)), 0) AS myNumReplies
				FROM {$db_prefix}topics AS t
					LEFT JOIN {$db_prefix}messages AS ma ON (ma.id_topic = t.id_topic AND ma.approved = 1)
					LEFT JOIN {$db_prefix}messages AS mf ON (mf.id_msg = t.id_first_msg)
				GROUP BY t.id_topic
				HAVING num_replies != myNumReplies", __FILE__, __LINE__);
			while ($topicArray = $smfFunc['db_fetch_assoc']($resultTopic))
			{
				$smfFunc['db_query']('', "
					UPDATE {$db_prefix}topics
					SET num_replies = '$topicArray[myNumReplies]'
					WHERE id_topic = $topicArray[id_topic]", __FILE__, __LINE__);
			}
			$smfFunc['db_free_result']($resultTopic);
		}

		// Fix all unapproved_posts in the topic table.
		if (empty($to_fix) || in_array('stats_topics3', $to_fix))
		{
			$resultTopic = $smfFunc['db_query']('', "
				SELECT
					t.id_topic, t.unapproved_posts, COUNT(mu.id_msg) AS myUnapprovedPosts
				FROM {$db_prefix}topics AS t
					LEFT JOIN {$db_prefix}messages AS mu ON (mu.id_topic = t.id_topic AND mu.approved = 0)
				GROUP BY t.id_topic
				HAVING unapproved_posts != myUnapprovedPosts", __FILE__, __LINE__);
			while ($topicArray = $smfFunc['db_fetch_assoc']($resultTopic))
			{
				$smfFunc['db_query']('', "
					UPDATE {$db_prefix}topics
					SET unapproved_posts = '$topicArray[myUnapprovedPosts]'
					WHERE id_topic = $topicArray[id_topic]", __FILE__, __LINE__);
			}
			$smfFunc['db_free_result']($resultTopic);
		}

		// Fix all topics that have a board ID that cannot be found in the boards table.
		if (empty($to_fix) || in_array('missing_boards', $to_fix))
		{
			$resultTopics = $smfFunc['db_query']('', "
				SELECT t.id_board, COUNT(*) AS myNumTopics, COUNT(m.id_msg) AS myNumPosts
				FROM {$db_prefix}topics AS t
					LEFT JOIN {$db_prefix}boards AS b ON (b.id_board = t.id_board)
					LEFT JOIN {$db_prefix}messages AS m ON (m.id_topic = t.id_topic)
				WHERE b.id_board IS NULL
				GROUP BY t.id_board", __FILE__, __LINE__);
			if ($smfFunc['db_num_rows']($resultTopics) > 0)
				createSalvageArea();
			while ($topicArray = $smfFunc['db_fetch_assoc']($resultTopics))
			{
				$smfFunc['db_query']('', "
					INSERT INTO {$db_prefix}boards
						(id_cat, name, description, num_topics, num_posts, member_groups)
					VALUES ($salvageCatID, 'Salvaged board', '', $topicArray[myNumTopics], $topicArray[myNumPosts], '1')", __FILE__, __LINE__);
				$newBoardID = db_insert_id("{$db_prefix}boards", 'id_board');

				$smfFunc['db_query']('', "
					UPDATE {$db_prefix}topics
					SET id_board = $newBoardID
					WHERE id_board = $topicArray[id_board]", __FILE__, __LINE__);
				$smfFunc['db_query']('', "
					UPDATE {$db_prefix}messages
					SET id_board = $newBoardID
					WHERE id_board = $topicArray[id_board]", __FILE__, __LINE__);
			}
			$smfFunc['db_free_result']($resultTopics);
		}

		// Fix all boards that have a cat ID that cannot be found in the cats table.
		if (empty($to_fix) || in_array('missing_categories', $to_fix))
		{
			$resultBoards = $smfFunc['db_query']('', "
				SELECT b.id_cat
				FROM {$db_prefix}boards AS b
					LEFT JOIN {$db_prefix}categories AS c ON (c.id_cat = b.id_cat)
				WHERE c.id_cat IS NULL
				GROUP BY b.id_cat", __FILE__, __LINE__);
			if ($smfFunc['db_num_rows']($resultBoards) > 0)
				createSalvageArea();
			while ($boardArray = $smfFunc['db_fetch_assoc']($resultBoards))
			{
				$smfFunc['db_query']('', "
					UPDATE {$db_prefix}boards
					SET id_cat = $salvageCatID
					WHERE id_cat = $boardArray[id_cat]", __FILE__, __LINE__);

			}
			$smfFunc['db_free_result']($resultBoards);
		}

		// Last step-make sure all non-guest posters still exist.
		if (empty($to_fix) || in_array('missing_posters', $to_fix))
		{
			$result = $smfFunc['db_query']('', "
				SELECT m.id_msg
				FROM {$db_prefix}messages AS m
					LEFT JOIN {$db_prefix}members AS mem ON (mem.id_member = m.id_member)
				WHERE m.id_member != 0
					AND mem.id_member IS NULL", __FILE__, __LINE__);
			if ($smfFunc['db_num_rows']($result) > 0)
			{
				$guestMessages = array();
				while ($row = $smfFunc['db_fetch_assoc']($result))
					$guestMessages[] = $row['id_msg'];
				$smfFunc['db_query']('', "
					UPDATE {$db_prefix}messages
					SET id_member = 0
					WHERE id_msg IN (" . implode(',', $guestMessages) . ')', __FILE__, __LINE__);
			}
			$smfFunc['db_free_result']($result);
		}

		// Fix all boards that have a parent ID that cannot be found in the boards table.
		if (empty($to_fix) || in_array('missing_parents', $to_fix))
		{
			$resultParents = $smfFunc['db_query']('', "
				SELECT b.id_parent
				FROM {$db_prefix}boards AS b
					LEFT JOIN {$db_prefix}boards AS p ON (p.id_board = b.id_parent)
				WHERE b.id_parent != 0
					AND (p.id_board IS NULL OR p.id_board = b.id_board)
				GROUP BY b.id_parent", __FILE__, __LINE__);
			if ($smfFunc['db_num_rows']($resultParents) > 0)
				createSalvageArea();
			while ($parentArray = $smfFunc['db_fetch_assoc']($resultParents))
			{
				$smfFunc['db_query']('', "
					UPDATE {$db_prefix}boards
					SET id_parent = $salvageBoardID, id_cat = $salvageCatID, child_level = 1
					WHERE id_parent = $parentArray[id_parent]", __FILE__, __LINE__);
			}
			$smfFunc['db_free_result']($resultParents);
		}

		if (empty($to_fix) || in_array('missing_polls', $to_fix))
		{
			if (version_compare($mysql_version, '4.0.4') >= 0)
			{
				$smfFunc['db_query']('', "
					UPDATE {$db_prefix}topics AS t
						LEFT JOIN {$db_prefix}polls AS p ON (p.id_poll = t.id_poll)
					SET t.id_poll = 0
					WHERE t.id_poll != 0
						AND p.id_poll IS NULL", __FILE__, __LINE__);
			}
			else
			{
				$resultPolls = $smfFunc['db_query']('', "
					SELECT t.id_poll
					FROM {$db_prefix}topics AS t
						LEFT JOIN {$db_prefix}polls AS p ON (p.id_poll = t.id_poll)
					WHERE t.id_poll != 0
						AND p.id_poll IS NULL
					GROUP BY t.id_poll", __FILE__, __LINE__);
				$polls = array();
				while ($rowPolls = $smfFunc['db_fetch_assoc']($resultPolls))
					$polls[] = $rowPolls['id_poll'];
				$smfFunc['db_free_result']($resultPolls);

				if (!empty($polls))
					$smfFunc['db_query']('', "
						UPDATE {$db_prefix}topics
						SET id_poll = 0
						WHERE id_poll IN (" . implode(', ', $polls) . ")", __FILE__, __LINE__);
			}
		}

		if (empty($to_fix) || in_array('missing_calendar_topics', $to_fix))
		{
			if (version_compare($mysql_version, '4.0.4') >= 0)
			{
				$smfFunc['db_query']('', "
					UPDATE {$db_prefix}calendar AS cal
						LEFT JOIN {$db_prefix}topics AS t ON (t.id_topic = cal.id_topic)
					SET cal.id_board = 0, cal.id_topic = 0
					WHERE cal.id_topic != 0
						AND t.id_topic IS NULL", __FILE__, __LINE__);
			}
			else
			{
				$resultEvents = $smfFunc['db_query']('', "
					SELECT cal.id_topic
					FROM {$db_prefix}calendar AS cal
						LEFT JOIN {$db_prefix}topics AS t ON (t.id_topic = cal.id_topic)
					WHERE cal.id_topic != 0
						AND t.id_topic IS NULL
					GROUP BY cal.id_topic", __FILE__, __LINE__);
				$events = array();
				while ($rowEvents = $smfFunc['db_fetch_assoc']($resultEvents))
					$events[] = $rowEvents['id_topic'];
				$smfFunc['db_free_result']($resultEvents);

				if (!empty($events))
					$smfFunc['db_query']('', "
						UPDATE {$db_prefix}calendar
						SET id_topic = 0, id_board = 0
						WHERE id_topic IN (" . implode(', ', $events) . ")", __FILE__, __LINE__);
			}
		}

		if (empty($to_fix) || in_array('missing_log_topics', $to_fix))
		{
			$result = $smfFunc['db_query']('', "
				SELECT lt.id_topic
				FROM {$db_prefix}log_topics AS lt
					LEFT JOIN {$db_prefix}topics AS t ON (t.id_topic = lt.id_topic)
				WHERE t.id_topic IS NULL
				GROUP BY lt.id_topic", __FILE__, __LINE__);
			$topics = array();
			while ($row = $smfFunc['db_fetch_assoc']($result))
				$topics[] = $row['id_topic'];
			$smfFunc['db_free_result']($result);

			if (!empty($topics))
			{
				$smfFunc['db_query']('', "
					DELETE FROM {$db_prefix}log_topics
					WHERE id_topic IN (" . implode(', ', $topics) . ")", __FILE__, __LINE__);
			}
		}

		if (empty($to_fix) || in_array('missing_log_topics_members', $to_fix))
		{
			$result = $smfFunc['db_query']('', "
				SELECT lt.id_member
				FROM {$db_prefix}log_topics AS lt
					LEFT JOIN {$db_prefix}members AS mem ON (mem.id_member = lt.id_member)
				WHERE mem.id_member IS NULL
				GROUP BY lt.id_member", __FILE__, __LINE__);
			$members = array();
			while ($row = $smfFunc['db_fetch_assoc']($result))
				$members[] = $row['id_member'];
			$smfFunc['db_free_result']($result);

			if (!empty($members))
			{
				$smfFunc['db_query']('', "
					DELETE FROM {$db_prefix}log_topics
					WHERE id_member IN (" . implode(', ', $members) . ")", __FILE__, __LINE__);
			}
		}

		if (empty($to_fix) || in_array('missing_log_boards', $to_fix))
		{
			$result = $smfFunc['db_query']('', "
				SELECT lb.id_board
				FROM {$db_prefix}log_boards AS lb
					LEFT JOIN {$db_prefix}boards AS b ON (b.id_board = lb.id_board)
				WHERE b.id_board IS NULL
				GROUP BY lb.id_board", __FILE__, __LINE__);
			$boards = array();
			while ($row = $smfFunc['db_fetch_assoc']($result))
				$boards[] = $row['id_board'];
			$smfFunc['db_free_result']($result);

			if (!empty($boards))
			{
				$smfFunc['db_query']('', "
					DELETE FROM {$db_prefix}log_boards
					WHERE id_board IN (" . implode(', ', $boards) . ")", __FILE__, __LINE__);
			}
		}

		if (empty($to_fix) || in_array('missing_log_boards_members', $to_fix))
		{
			$result = $smfFunc['db_query']('', "
				SELECT lb.id_member
				FROM {$db_prefix}log_boards AS lb
					LEFT JOIN {$db_prefix}members AS mem ON (mem.id_member = lb.id_member)
				WHERE mem.id_member IS NULL
				GROUP BY lb.id_member", __FILE__, __LINE__);
			$members = array();
			while ($row = $smfFunc['db_fetch_assoc']($result))
				$members[] = $row['id_member'];
			$smfFunc['db_free_result']($result);

			if (!empty($members))
			{
				$smfFunc['db_query']('', "
					DELETE FROM {$db_prefix}log_boards
					WHERE id_member IN (" . implode(', ', $members) . ")", __FILE__, __LINE__);
			}
		}

		if (empty($to_fix) || in_array('missing_log_mark_read', $to_fix))
		{
			$result = $smfFunc['db_query']('', "
				SELECT lmr.id_board
				FROM {$db_prefix}log_mark_read AS lmr
					LEFT JOIN {$db_prefix}boards AS b ON (b.id_board = lmr.id_board)
				WHERE b.id_board IS NULL
				GROUP BY lmr.id_board", __FILE__, __LINE__);
			$boards = array();
			while ($row = $smfFunc['db_fetch_assoc']($result))
				$boards[] = $row['id_board'];
			$smfFunc['db_free_result']($result);

			if (!empty($boards))
			{
				$smfFunc['db_query']('', "
					DELETE FROM {$db_prefix}log_mark_read
					WHERE id_board IN (" . implode(', ', $boards) . ")", __FILE__, __LINE__);
			}
		}

		if (empty($to_fix) || in_array('missing_log_mark_read_members', $to_fix))
		{
			$result = $smfFunc['db_query']('', "
				SELECT lmr.id_member
				FROM {$db_prefix}log_mark_read AS lmr
					LEFT JOIN {$db_prefix}members AS mem ON (mem.id_member = lmr.id_member)
				WHERE mem.id_member IS NULL
				GROUP BY lmr.id_member", __FILE__, __LINE__);
			$members = array();
			while ($row = $smfFunc['db_fetch_assoc']($result))
				$members[] = $row['id_member'];
			$smfFunc['db_free_result']($result);

			if (!empty($members))
			{
				$smfFunc['db_query']('', "
					DELETE FROM {$db_prefix}log_mark_read
					WHERE id_member IN (" . implode(', ', $members) . ")", __FILE__, __LINE__);
			}
		}

		if (empty($to_fix) || in_array('missing_pms', $to_fix))
		{
			$result = $smfFunc['db_query']('', "
				SELECT pmr.id_pm
				FROM {$db_prefix}pm_recipients AS pmr
					LEFT JOIN {$db_prefix}personal_messages AS pm ON (pm.id_pm = pmr.id_pm)
				WHERE pm.id_pm IS NULL
				GROUP BY pmr.id_pm", __FILE__, __LINE__);
			$pms = array();
			while ($row = $smfFunc['db_fetch_assoc']($result))
				$pms[] = $row['id_pm'];
			$smfFunc['db_free_result']($result);

			if (!empty($pms))
			{
				$smfFunc['db_query']('', "
					DELETE FROM {$db_prefix}pm_recipients
					WHERE id_pm IN (" . implode(', ', $pms) . ")", __FILE__, __LINE__);
			}
		}

		if (empty($to_fix) || in_array('missing_recipients', $to_fix))
		{
			$result = $smfFunc['db_query']('', "
				SELECT pmr.id_member
				FROM {$db_prefix}pm_recipients AS pmr
					LEFT JOIN {$db_prefix}members AS mem ON (mem.id_member = pmr.id_member)
				WHERE pmr.id_member != 0
					AND mem.id_member IS NULL
				GROUP BY pmr.id_member", __FILE__, __LINE__);
			$members = array();
			while ($row = $smfFunc['db_fetch_assoc']($result))
				$members[] = $row['id_member'];
			$smfFunc['db_free_result']($result);

			if (!empty($members))
			{
				$smfFunc['db_query']('', "
					DELETE FROM {$db_prefix}pm_recipients
					WHERE id_member IN (" . implode(', ', $members) . ")", __FILE__, __LINE__);
			}
		}

		if (empty($to_fix) || in_array('missing_senders', $to_fix))
		{
			$result = $smfFunc['db_query']('', "
				SELECT pm.id_pm
				FROM {$db_prefix}personal_messages AS pm
					LEFT JOIN {$db_prefix}members AS mem ON (mem.id_member = pm.id_member_from)
				WHERE pm.id_member_from != 0
					AND mem.id_member IS NULL", __FILE__, __LINE__);
			if ($smfFunc['db_num_rows']($result) > 0)
			{
				$guestMessages = array();
				while ($row = $smfFunc['db_fetch_assoc']($result))
					$guestMessages[] = $row['id_pm'];

				$smfFunc['db_query']('', "
					UPDATE {$db_prefix}personal_messages
					SET id_member_from = 0
					WHERE id_pm IN (" . implode(',', $guestMessages) . ')', __FILE__, __LINE__);
			}
			$smfFunc['db_free_result']($result);
		}

		if (empty($to_fix) || in_array('missing_notify_members', $to_fix))
		{
			$result = $smfFunc['db_query']('', "
				SELECT ln.id_member
				FROM {$db_prefix}log_notify AS ln
					LEFT JOIN {$db_prefix}members AS mem ON (mem.id_member = ln.id_member)
				WHERE mem.id_member IS NULL
				GROUP BY ln.id_member", __FILE__, __LINE__);
			$members = array();
			while ($row = $smfFunc['db_fetch_assoc']($result))
				$members[] = $row['id_member'];
			$smfFunc['db_free_result']($result);

			if (!empty($members))
			{
				$smfFunc['db_query']('', "
					DELETE FROM {$db_prefix}log_notify
					WHERE id_member IN (" . implode(', ', $members) . ")", __FILE__, __LINE__);
			}
		}

		if (empty($to_fix) || in_array('missing_cached_subject', $to_fix))
		{
			$request = $smfFunc['db_query']('', "
				SELECT t.id_topic, m.subject
				FROM ({$db_prefix}topics AS t, {$db_prefix}messages AS m)
					LEFT JOIN {$db_prefix}log_search_subjects AS lss ON (lss.id_topic = t.id_topic)
				WHERE m.id_msg = t.id_first_msg
					AND lss.id_topic IS NULL", __FILE__, __LINE__);
			$insertRows = array();
			while ($row = $smfFunc['db_fetch_assoc']($request))
			{
				foreach (text2words($row['subject']) as $word)
					$insertRows[] = "'$word', $row[id_topic]";
				if (count($insertRows) > 500)
				{
					$smfFunc['db_query']('', "
						INSERT IGNORE INTO {$db_prefix}log_search_subjects
							(word, id_topic)
						VALUES (" . implode('),
							(', $insertRows) . ")", __FILE__, __LINE__);
					$insertRows = array();
				}

			}
			$smfFunc['db_free_result']($request);

			if (!empty($insertRows))
				$smfFunc['db_query']('', "
					INSERT IGNORE INTO {$db_prefix}log_search_subjects
						(word, id_topic)
					VALUES (" . implode('),
						(', $insertRows) . ")", __FILE__, __LINE__);
		}

		if (empty($to_fix) || in_array('missing_topic_for_cache', $to_fix))
		{
			$request = $smfFunc['db_query']('', "
				SELECT lss.id_topic
				FROM {$db_prefix}log_search_subjects AS lss
					LEFT JOIN {$db_prefix}topics AS t ON (t.id_topic = lss.id_topic)
				WHERE t.id_topic IS NULL
				GROUP BY lss.id_topic", __FILE__, __LINE__);
			$deleteTopics = array();
			while ($row = $smfFunc['db_fetch_assoc']($request))
				$deleteTopics[] = $row['id_topic'];
			$smfFunc['db_free_result']($request);

			if (!empty($deleteTopics))
				$smfFunc['db_query']('', "
					DELETE FROM {$db_prefix}log_search_subjects
					WHERE id_topic IN (" . implode(', ', $deleteTopics) . ')', __FILE__, __LINE__);
		}

		if (empty($to_fix) || in_array('missing_member_vote', $to_fix))
		{
			$result = $smfFunc['db_query']('', "
				SELECT lp.id_member
				FROM {$db_prefix}log_polls AS lp
					LEFT JOIN {$db_prefix}members AS mem ON (mem.id_member = lp.id_member)
				WHERE mem.id_member IS NULL
				GROUP BY lp.id_member", __FILE__, __LINE__);
			$members = array();
			while ($row = $smfFunc['db_fetch_assoc']($result))
				$members[] = $row['id_member'];
			$smfFunc['db_free_result']($result);

			if (!empty($members))
			{
				$smfFunc['db_query']('', "
					DELETE FROM {$db_prefix}log_polls
					WHERE id_member IN (" . implode(', ', $members) . ")", __FILE__, __LINE__);
			}
		}

		if (empty($to_fix) || in_array('missing_log_poll_vote', $to_fix))
		{
			$request = $smfFunc['db_query']('', "
				SELECT lp.id_poll
				FROM {$db_prefix}log_polls AS lp
					LEFT JOIN {$db_prefix}polls AS p ON (p.id_poll = lp.id_poll)
				WHERE p.id_poll IS NULL
				GROUP BY lp.id_poll", __FILE__, __LINE__);
			$polls = array();
			while ($row = $smfFunc['db_fetch_assoc']($request))
				$polls[] = $row['id_poll'];
			$smfFunc['db_free_result']($request);

			if (!empty($polls))
			{
				$smfFunc['db_query']('', "
					DELETE FROM {$db_prefix}log_polls
					WHERE id_poll IN (" . implode(', ', $polls) . ")", __FILE__, __LINE__);
			}
		}

		if (empty($to_fix) || in_array('report_missing_comments', $to_fix))
		{
			$result = $smfFunc['db_query']('', "
				SELECT lr.id_report
				FROM {$db_prefix}log_reported AS lr
					LEFT JOIN {$db_prefix}log_reported_comments AS lrc ON (lrc.id_report = lr.id_report)
				WHERE lrc.id_report IS NULL
				GROUP BY lr.id_report", __FILE__, __LINE__);
			$reports = array();
			while ($row = $smfFunc['db_fetch_assoc']($result))
				$reports[] = $row['id_report'];
			$smfFunc['db_free_result']($result);

			if (!empty($reports))
			{
				$smfFunc['db_query']('', "
					DELETE FROM {$db_prefix}log_reported
					WHERE id_report IN (" . implode(', ', $reports) . ")", __FILE__, __LINE__);
			}
		}

		if (empty($to_fix) || in_array('comments_missing_report', $to_fix))
		{
			$result = $smfFunc['db_query']('', "
				SELECT lrc.id_report
				FROM {$db_prefix}log_reported_comments AS lrc
					LEFT JOIN {$db_prefix}log_reported AS lr ON (lr.id_report = lrc.id_report)
				WHERE lr.id_report IS NULL
				GROUP BY lrc.id_report", __FILE__, __LINE__);
			$reports = array();
			while ($row = $smfFunc['db_fetch_assoc']($result))
				$reports[] = $row['id_report'];
			$smfFunc['db_free_result']($result);

			if (!empty($reports))
			{
				$smfFunc['db_query']('', "
					DELETE FROM {$db_prefix}log_reported_comments
					WHERE id_report IN (" . implode(', ', $reports) . ")", __FILE__, __LINE__);
			}
		}

		if (empty($to_fix) || in_array('group_request_missing_member', $to_fix))
		{
			$result = $smfFunc['db_query']('', "
				SELECT lgr.id_member
				FROM {$db_prefix}log_group_requests AS lgr
					LEFT JOIN {$db_prefix}members AS mem ON (mem.id_member = lgr.id_member)
				WHERE mem.id_member IS NULL
				GROUP BY lgr.id_member", __FILE__, __LINE__);
			$members = array();
			while ($row = $smfFunc['db_fetch_assoc']($result))
				$members[] = $row['id_member'];
			$smfFunc['db_free_result']($result);

			if (!empty($members))
			{
				$smfFunc['db_query']('', "
					DELETE FROM {$db_prefix}log_group_requests
					WHERE id_member IN (" . implode(', ', $members) . ")", __FILE__, __LINE__);
			}
		}

		if (empty($to_fix) || in_array('group_request_missing_group', $to_fix))
		{
			$result = $smfFunc['db_query']('', "
				SELECT lgr.id_group
				FROM {$db_prefix}log_group_requests AS lgr
					LEFT JOIN {$db_prefix}membergroups AS mg ON (mg.id_group = lgr.id_group)
				WHERE mg.id_group IS NULL
				GROUP BY lgr.id_group", __FILE__, __LINE__);
			$groups = array();
			while ($row = $smfFunc['db_fetch_assoc']($result))
				$groups[] = $row['id_group'];
			$smfFunc['db_free_result']($result);

			if (!empty($groups))
			{
				$smfFunc['db_query']('', "
					DELETE FROM {$db_prefix}log_group_requests
					WHERE id_group IN (" . implode(', ', $groups) . ")", __FILE__, __LINE__);
			}
		}

		updateSettings(array('settings_updated' => 0));
		updateStats('message');
		updateStats('topic');
		updateStats('calendar');

		// Force a group cache refresh.
		require_once($sourcedir . '/ManageMembergroups.php');
		cacheGroups();

		$context['raw_data'] = '
			<table width="100%" border="0" cellspacing="0" cellpadding="4" class="tborder">
				<tr class="titlebg">
					<td>' . $txt['smf86'] . '</td>
				</tr><tr>
					<td class="windowbg">
						' . $txt['smf92'] . '<br />
						<br />
						<a href="' . $scripturl . '?action=admin;area=maintain">' . $txt['maintain_return'] . '</a>
					</td>
				</tr>
			</table>';

		$_SESSION['repairboards_to_fix'] = null;
		$_SESSION['repairboards_to_fix2'] = null;
	}
}

function pauseRepairProcess($to_fix, $max_substep = 0)
{
	global $context, $txt, $time_start;

	// More time, I need more time!
	@set_time_limit(600);
	if (function_exists('apache_reset_timeout'))
		apache_reset_timeout();

	// Errr, wait.  How much time has this taken already?
	if (time() - array_sum(explode(' ', $time_start)) < 3)
		return;

	$context['continue_get_data'] = '?action=admin;area=repairboards' . (isset($_GET['fixErrors']) ? ';fixErrors' : '') . ';step=' . $_GET['step'] . ';substep=' . $_GET['substep'];
	$context['page_title'] = $txt['not_done_title'];
	$context['continue_post_data'] = '';
	$context['continue_countdown'] = '2';
	$context['sub_template'] = 'not_done';

	// Change these two if more steps are added!
	if (empty($max_substep))
		$context['continue_percent'] = round(($_GET['step'] * 100) / 25);
	else
		$context['continue_percent'] = round(($_GET['step'] * 100 + ($_GET['substep'] * 100) / $max_substep) / 25);

	// Never more than 100%!
	$context['continue_percent'] = min($context['continue_percent'], 100);

	$_SESSION['repairboards_to_fix'] = $to_fix;
	$_SESSION['repairboards_to_fix2'] = $context['repair_errors'];

	obExit();
}

function findForumErrors()
{
	global $db_prefix, $context, $txt, $smfFunc;

	// This may take some time...
	@set_time_limit(600);

	$to_fix = !empty($_SESSION['repairboards_to_fix']) ? $_SESSION['repairboards_to_fix'] : array();
	$context['repair_errors'] = isset($_SESSION['repairboards_to_fix2']) ? $_SESSION['repairboards_to_fix2'] : array();

	$_GET['step'] = empty($_GET['step']) ? 0 : (int) $_GET['step'];
	$_GET['substep'] = empty($_GET['substep']) ? 0 : (int) $_GET['substep'];

	if ($_GET['step'] <= 0)
	{
		// Make a last-ditch-effort check to get rid of topics with zeros..
		$result = $smfFunc['db_query']('', "
			SELECT COUNT(*)
			FROM {$db_prefix}topics
			WHERE id_topic = 0", __FILE__, __LINE__);
		list ($zeroTopics) = $smfFunc['db_fetch_row']($result);
		$smfFunc['db_free_result']($result);

		// This is only going to be 1 or 0, but...
		$result = $smfFunc['db_query']('', "
			SELECT COUNT(*)
			FROM {$db_prefix}messages
			WHERE id_msg = 0", __FILE__, __LINE__);
		list ($zeroMessages) = $smfFunc['db_fetch_row']($result);
		$smfFunc['db_free_result']($result);

		if (!empty($zeroTopics) || !empty($zeroMessages))
		{
			$context['repair_errors'][] = $txt['repair_zero_ids'];
			$to_fix[] = 'zero_ids';
		}

		$_GET['step'] = 1;
		$_GET['substep'] = 0;
		pauseRepairProcess($to_fix);
	}

	if ($_GET['step'] <= 1)
	{
		// Find messages that don't have existing topics.
		$result = $smfFunc['db_query']('', "
			SELECT m.id_topic, m.id_msg
			FROM {$db_prefix}messages AS m
				LEFT JOIN {$db_prefix}topics AS t ON (t.id_topic = m.id_topic)
			WHERE t.id_topic IS NULL
			ORDER BY m.id_topic, m.id_msg", __FILE__, __LINE__);
		while ($row = $smfFunc['db_fetch_assoc']($result))
			$context['repair_errors'][] = sprintf($txt['repair_missing_topics'], $row['id_msg'], $row['id_topic']);
		if ($smfFunc['db_num_rows']($result) != 0)
			$to_fix[] = 'missing_topics';
		$smfFunc['db_free_result']($result);

		$_GET['step'] = 2;
		$_GET['substep'] = 0;
		pauseRepairProcess($to_fix);
	}

	if ($_GET['step'] <= 2)
	{
		// Find messages that don't have existing topics.
		$result = $smfFunc['db_query']('', "
			SELECT m.id_topic, m.id_msg
			FROM {$db_prefix}messages AS m
				LEFT JOIN {$db_prefix}topics AS t ON (t.id_topic = m.id_topic)
			WHERE t.id_topic IS NULL
			ORDER BY m.id_topic, m.id_msg", __FILE__, __LINE__);
		while ($row = $smfFunc['db_fetch_assoc']($result))
			$context['repair_errors'][] = sprintf($txt['repair_missing_topics'], $row['id_msg'], $row['id_topic']);
		if ($smfFunc['db_num_rows']($result) != 0)
			$to_fix[] = 'missing_topics';
		$smfFunc['db_free_result']($result);

		$_GET['step'] = 3;
		$_GET['substep'] = 0;
		pauseRepairProcess($to_fix);
	}

	if ($_GET['step'] <= 3)
	{
		$result = $smfFunc['db_query']('', "
			SELECT MAX(id_topic)
			FROM {$db_prefix}topics", __FILE__, __LINE__);
		list ($topics) = $smfFunc['db_fetch_row']($result);
		$smfFunc['db_free_result']($result);

		// Find topics with no messages.
		for (; $_GET['substep'] < $topics; $_GET['substep'] += 1000)
		{
			pauseRepairProcess($to_fix, $topics);

			$result = $smfFunc['db_query']('', "
				SELECT t.id_topic, COUNT(m.id_msg) AS num_msg
				FROM {$db_prefix}topics AS t
					LEFT JOIN {$db_prefix}messages AS m ON (m.id_topic = t.id_topic)
				WHERE t.id_topic BETWEEN $_GET[substep] AND $_GET[substep] + 999
				GROUP BY t.id_topic
				HAVING COUNT(m.id_msg) = 0", __FILE__, __LINE__);
			while ($row = $smfFunc['db_fetch_assoc']($result))
				$context['repair_errors'][] = sprintf($txt['repair_missing_messages'], $row['id_topic']);
			if ($smfFunc['db_num_rows']($result) != 0)
				$to_fix[] = 'missing_messages';
			$smfFunc['db_free_result']($result);
		}

		$_GET['step'] = 4;
		$_GET['substep'] = 0;
		pauseRepairProcess($to_fix);
	}

	if ($_GET['step'] <= 4)
	{
		$result = $smfFunc['db_query']('', "
			SELECT MAX(id_topic)
			FROM {$db_prefix}topics", __FILE__, __LINE__);
		list ($topics) = $smfFunc['db_fetch_row']($result);
		$smfFunc['db_free_result']($result);

		// Find topics with incorrect id_first_msg/id_last_msg.
		for (; $_GET['substep'] < $topics; $_GET['substep'] += 1000)
		{
			pauseRepairProcess($to_fix, $topics);

			$result = $smfFunc['db_query']('', "
				SELECT
					t.id_topic, t.id_first_msg, t.id_last_msg,
					IF (MIN(ma.id_msg),
						IF (MIN(mu.id_msg),
							IF (MIN(mu.id_msg) < MIN(ma.id_msg), mu.id_msg, ma.id_msg),
						MIN(ma.id_msg)),
					MIN(mu.id_msg)) AS myid_first_msg,
					IF (MAX(ma.id_msg), MAX(ma.id_msg), MIN(mu.id_msg)) AS myid_last_msg,
					t.approved, mf.approved AS myApproved
				FROM {$db_prefix}topics AS t
					LEFT JOIN {$db_prefix}messages AS ma ON (ma.id_topic = t.id_topic AND ma.approved = 1)
					LEFT JOIN {$db_prefix}messages AS mu ON (mu.id_topic = t.id_topic AND mu.approved = 0)
					LEFT JOIN {$db_prefix}messages AS mf ON (mf.id_msg = t.id_first_msg)
				WHERE t.id_topic BETWEEN $_GET[substep] AND $_GET[substep] + 999
				GROUP BY t.id_topic
				HAVING id_first_msg != myid_first_msg OR id_last_msg != myid_last_msg
					OR approved != myApproved
				ORDER BY t.id_topic", __FILE__, __LINE__);
			while ($row = $smfFunc['db_fetch_assoc']($result))
			{
				if ($row['id_first_msg'] != $row['myid_first_msg'])
					$context['repair_errors'][] = sprintf($txt['repair_stats_topics_1'], $row['id_topic'], $row['id_first_msg']);
				if ($row['id_last_msg'] != $row['myid_last_msg'])
					$context['repair_errors'][] = sprintf($txt['repair_stats_topics_2'], $row['id_topic'], $row['id_last_msg']);
				if ($row['approved'] != $row['myApproved'])
					$context['repair_errors'][] = sprintf($txt['repair_stats_topics_5'], $row['id_topic']);
			}
			if ($smfFunc['db_num_rows']($result) != 0)
				$to_fix[] = 'stats_topics';
			$smfFunc['db_free_result']($result);
		}

		$_GET['step'] = 5;
		$_GET['substep'] = 0;
		pauseRepairProcess($to_fix);
	}

	if ($_GET['step'] <= 5)
	{
		$result = $smfFunc['db_query']('', "
			SELECT MAX(id_topic)
			FROM {$db_prefix}topics", __FILE__, __LINE__);
		list ($topics) = $smfFunc['db_fetch_row']($result);
		$smfFunc['db_free_result']($result);

		// Find topics with incorrect num_replies.
		for (; $_GET['substep'] < $topics; $_GET['substep'] += 1000)
		{
			pauseRepairProcess($to_fix, $topics);

			$result = $smfFunc['db_query']('', "
				SELECT
					t.id_topic, t.num_replies,
					IF (COUNT(ma.id_msg), IF (mf.approved, COUNT(ma.id_msg) - 1, COUNT(ma.id_msg)), 0) AS myNumReplies
				FROM {$db_prefix}topics AS t
					LEFT JOIN {$db_prefix}messages AS ma ON (ma.id_topic = t.id_topic AND ma.approved = 1)
					LEFT JOIN {$db_prefix}messages AS mf ON (mf.id_msg = t.id_first_msg)
				WHERE t.id_topic BETWEEN $_GET[substep] AND $_GET[substep] + 999
				GROUP BY t.id_topic
				HAVING num_replies != myNumReplies
				ORDER BY t.id_topic", __FILE__, __LINE__);
			while ($row = $smfFunc['db_fetch_assoc']($result))
			{
				if ($row['num_replies'] != $row['myNumReplies'])
					$context['repair_errors'][] = sprintf($txt['repair_stats_topics_3'], $row['id_topic'], $row['num_replies']);
			}
			if ($smfFunc['db_num_rows']($result) != 0)
				$to_fix[] = 'stats_topics2';
			$smfFunc['db_free_result']($result);
		}

		$_GET['step'] = 6;
		$_GET['substep'] = 0;
		pauseRepairProcess($to_fix);
	}

	if ($_GET['step'] <= 6)
	{
		$result = $smfFunc['db_query']('', "
			SELECT MAX(id_topic)
			FROM {$db_prefix}topics", __FILE__, __LINE__);
		list ($topics) = $smfFunc['db_fetch_row']($result);
		$smfFunc['db_free_result']($result);

		// Find topics with incorrect unapproved_posts.
		for (; $_GET['substep'] < $topics; $_GET['substep'] += 1000)
		{
			pauseRepairProcess($to_fix, $topics);

			$result = $smfFunc['db_query']('', "
				SELECT
					t.id_topic, t.unapproved_posts, COUNT(mu.id_msg) AS myUnapprovedPosts
				FROM {$db_prefix}topics AS t
					LEFT JOIN {$db_prefix}messages AS mu ON (mu.id_topic = t.id_topic AND mu.approved = 0)
				WHERE t.id_topic BETWEEN $_GET[substep] AND $_GET[substep] + 999
				GROUP BY t.id_topic
				HAVING unapproved_posts != myUnapprovedPosts
				ORDER BY t.id_topic", __FILE__, __LINE__);
			while ($row = $smfFunc['db_fetch_assoc']($result))
			{
				if ($row['unapproved_posts'] != $row['myUnapprovedPosts'])
					$context['repair_errors'][] = sprintf($txt['repair_stats_topics_4'], $row['id_topic'], $row['unapproved_posts']);
			}
			if ($smfFunc['db_num_rows']($result) != 0)
				$to_fix[] = 'stats_topics3';
			$smfFunc['db_free_result']($result);
		}

		$_GET['step'] = 7;
		$_GET['substep'] = 0;
		pauseRepairProcess($to_fix);
	}

	if ($_GET['step'] <= 7)
	{
		$result = $smfFunc['db_query']('', "
			SELECT MAX(id_topic)
			FROM {$db_prefix}topics", __FILE__, __LINE__);
		list ($topics) = $smfFunc['db_fetch_row']($result);
		$smfFunc['db_free_result']($result);

		// Find topics with nonexistent boards.
		for (; $_GET['substep'] < $topics; $_GET['substep'] += 1000)
		{
			pauseRepairProcess($to_fix, $topics);

			$result = $smfFunc['db_query']('', "
				SELECT t.id_topic, t.id_board
				FROM {$db_prefix}topics AS t
					LEFT JOIN {$db_prefix}boards AS b ON (b.id_board = t.id_board)
				WHERE b.id_board IS NULL
					AND t.id_topic BETWEEN $_GET[substep] AND $_GET[substep] + 999
				ORDER BY t.id_board, t.id_topic", __FILE__, __LINE__);
			while ($row = $smfFunc['db_fetch_assoc']($result))
				$context['repair_errors'][] = sprintf($txt['repair_missing_boards'], $row['id_topic'], $row['id_board']);
			if ($smfFunc['db_num_rows']($result) != 0)
				$to_fix[] = 'missing_boards';
			$smfFunc['db_free_result']($result);
		}

		$_GET['step'] = 8;
		$_GET['substep'] = 0;
		pauseRepairProcess($to_fix);
	}

	if ($_GET['step'] <= 8)
	{
		// Find boards with nonexistent categories.
		$result = $smfFunc['db_query']('', "
			SELECT b.id_board, b.id_cat
			FROM {$db_prefix}boards AS b
				LEFT JOIN {$db_prefix}categories AS c ON (c.id_cat = b.id_cat)
			WHERE c.id_cat IS NULL
			ORDER BY b.id_cat, b.id_board", __FILE__, __LINE__);
		while ($row = $smfFunc['db_fetch_assoc']($result))
			$context['repair_errors'][] = sprintf($txt['repair_missing_categories'], $row['id_board'], $row['id_cat']);
		if ($smfFunc['db_num_rows']($result) != 0)
			$to_fix[] = 'missing_categories';
		$smfFunc['db_free_result']($result);

		$_GET['step'] = 9;
		$_GET['substep'] = 0;
		pauseRepairProcess($to_fix);
	}

	if ($_GET['step'] <= 9)
	{
		$result = $smfFunc['db_query']('', "
			SELECT MAX(id_msg)
			FROM {$db_prefix}messages", __FILE__, __LINE__);
		list ($messages) = $smfFunc['db_fetch_row']($result);
		$smfFunc['db_free_result']($result);

		// Find messages with nonexistent members.
		for (; $_GET['substep'] < $messages; $_GET['substep'] += 2000)
		{
			pauseRepairProcess($to_fix, $messages);

			$result = $smfFunc['db_query']('', "
				SELECT m.id_msg, m.id_member
				FROM {$db_prefix}messages AS m
					LEFT JOIN {$db_prefix}members AS mem ON (mem.id_member = m.id_member)
				WHERE mem.id_member IS NULL
					AND m.id_member != 0
					AND m.id_msg BETWEEN $_GET[substep] AND $_GET[substep] + 1999
				ORDER BY m.id_msg", __FILE__, __LINE__);
			while ($row = $smfFunc['db_fetch_assoc']($result))
				$context['repair_errors'][] = sprintf($txt['repair_missing_posters'], $row['id_msg'], $row['id_member']);
			if ($smfFunc['db_num_rows']($result) != 0)
				$to_fix[] = 'missing_posters';
			$smfFunc['db_free_result']($result);
		}

		$_GET['step'] = 10;
		$_GET['substep'] = 0;
		pauseRepairProcess($to_fix);
	}

	if ($_GET['step'] <= 10)
	{
		// Find boards with nonexistent parents.
		$result = $smfFunc['db_query']('', "
			SELECT b.id_board, b.id_parent
			FROM {$db_prefix}boards AS b
				LEFT JOIN {$db_prefix}boards AS p ON (p.id_board = b.id_parent)
			WHERE b.id_parent != 0
				AND (p.id_board IS NULL OR p.id_board = b.id_board)
			ORDER BY b.id_parent, b.id_board", __FILE__, __LINE__);
		while ($row = $smfFunc['db_fetch_assoc']($result))
			$context['repair_errors'][] = sprintf($txt['repair_missing_parents'], $row['id_board'], $row['id_parent']);
		if ($smfFunc['db_num_rows']($result) != 0)
			$to_fix[] = 'missing_parents';
		$smfFunc['db_free_result']($result);

		$_GET['step'] = 11;
		$_GET['substep'] = 0;
		pauseRepairProcess($to_fix);
	}

	if ($_GET['step'] <= 11)
	{
		$result = $smfFunc['db_query']('', "
			SELECT MAX(id_poll)
			FROM {$db_prefix}topics", __FILE__, __LINE__);
		list ($polls) = $smfFunc['db_fetch_row']($result);
		$smfFunc['db_free_result']($result);

		for (; $_GET['substep'] < $polls; $_GET['substep'] += 500)
		{
			pauseRepairProcess($to_fix, $polls);

			$result = $smfFunc['db_query']('', "
				SELECT t.id_poll, t.id_topic
				FROM {$db_prefix}topics AS t
					LEFT JOIN {$db_prefix}polls AS p ON (p.id_poll = t.id_poll)
				WHERE t.id_poll != 0
					AND t.id_poll BETWEEN $_GET[substep] AND $_GET[substep] + 499
					AND p.id_poll IS NULL
				GROUP BY t.id_poll", __FILE__, __LINE__);
			while ($row = $smfFunc['db_fetch_assoc']($result))
				$context['repair_errors'][] = sprintf($txt['repair_missing_polls'], $row['id_topic'], $row['id_poll']);
			if ($smfFunc['db_num_rows']($result) != 0)
				$to_fix[] = 'missing_polls';
			$smfFunc['db_free_result']($result);
		}

		$_GET['step'] = 12;
		$_GET['substep'] = 0;
		pauseRepairProcess($to_fix);
	}

	if ($_GET['step'] <= 12)
	{
		$result = $smfFunc['db_query']('', "
			SELECT MAX(id_topic)
			FROM {$db_prefix}calendar", __FILE__, __LINE__);
		list ($topics) = $smfFunc['db_fetch_row']($result);
		$smfFunc['db_free_result']($result);

		for (; $_GET['substep'] < $topics; $_GET['substep'] += 1000)
		{
			pauseRepairProcess($to_fix, $topics);

			$result = $smfFunc['db_query']('', "
				SELECT cal.id_topic, cal.id_event
				FROM {$db_prefix}calendar AS cal
					LEFT JOIN {$db_prefix}topics AS t ON (t.id_topic = cal.id_topic)
				WHERE cal.id_topic != 0
					AND cal.id_topic BETWEEN $_GET[substep] AND $_GET[substep] + 999
					AND t.id_topic IS NULL
				ORDER BY cal.id_topic", __FILE__, __LINE__);
			while ($row = $smfFunc['db_fetch_assoc']($result))
				$context['repair_errors'][] = sprintf($txt['repair_missing_calendar_topics'], $row['id_event'], $row['id_topic']);
			if ($smfFunc['db_num_rows']($result) != 0)
				$to_fix[] = 'missing_calendar_topics';
			$smfFunc['db_free_result']($result);
		}

		$_GET['step'] = 13;
		$_GET['substep'] = 0;
		pauseRepairProcess($to_fix);
	}

	if ($_GET['step'] <= 13)
	{
		$result = $smfFunc['db_query']('', "
			SELECT MAX(id_member)
			FROM {$db_prefix}log_topics", __FILE__, __LINE__);
		list ($members) = $smfFunc['db_fetch_row']($result);
		$smfFunc['db_free_result']($result);

		for (; $_GET['substep'] < $members; $_GET['substep'] += 250)
		{
			pauseRepairProcess($to_fix, $members);

			$result = $smfFunc['db_query']('', "
				SELECT lt.id_topic
				FROM {$db_prefix}log_topics AS lt
					LEFT JOIN {$db_prefix}topics AS t ON (t.id_topic = lt.id_topic)
				WHERE t.id_topic IS NULL
					AND lt.id_member BETWEEN $_GET[substep] AND $_GET[substep] + 249", __FILE__, __LINE__);
			while ($row = $smfFunc['db_fetch_assoc']($result))
				$context['repair_errors'][] = sprintf($txt['repair_missing_log_topics'], $row['id_topic']);
			if ($smfFunc['db_num_rows']($result) != 0 && !in_array('missing_log_topics', $to_fix))
				$to_fix[] = 'missing_log_topics';
			$smfFunc['db_free_result']($result);
		}

		$_GET['step'] = 14;
		$_GET['substep'] = 0;
		pauseRepairProcess($to_fix);
	}

	if ($_GET['step'] <= 14)
	{
		$result = $smfFunc['db_query']('', "
			SELECT MAX(id_member)
			FROM {$db_prefix}log_topics", __FILE__, __LINE__);
		list ($members) = $smfFunc['db_fetch_row']($result);
		$smfFunc['db_free_result']($result);

		for (; $_GET['substep'] < $members; $_GET['substep'] += 150)
		{
			pauseRepairProcess($to_fix, $members);

			$result = $smfFunc['db_query']('', "
				SELECT lt.id_member
				FROM {$db_prefix}log_topics AS lt
					LEFT JOIN {$db_prefix}members AS mem ON (mem.id_member = lt.id_member)
				WHERE mem.id_member IS NULL
					AND lt.id_member BETWEEN $_GET[substep] AND $_GET[substep] + 149
				GROUP BY lt.id_member", __FILE__, __LINE__);
			while ($row = $smfFunc['db_fetch_assoc']($result))
				$context['repair_errors'][] = sprintf($txt['repair_missing_log_topics_members'], $row['id_member']);
			if ($smfFunc['db_num_rows']($result) != 0 && !in_array('missing_log_topics_members', $to_fix))
				$to_fix[] = 'missing_log_topics_members';
			$smfFunc['db_free_result']($result);
		}

		$_GET['step'] = 15;
		$_GET['substep'] = 0;
		pauseRepairProcess($to_fix);
	}

	if ($_GET['step'] <= 15)
	{
		$result = $smfFunc['db_query']('', "
			SELECT MAX(id_member)
			FROM {$db_prefix}log_boards", __FILE__, __LINE__);
		list ($members) = $smfFunc['db_fetch_row']($result);
		$smfFunc['db_free_result']($result);

		for (; $_GET['substep'] < $members; $_GET['substep'] += 500)
		{
			pauseRepairProcess($to_fix, $members);

			$result = $smfFunc['db_query']('', "
				SELECT lb.id_board
				FROM {$db_prefix}log_boards AS lb
					LEFT JOIN {$db_prefix}boards AS b ON (b.id_board = lb.id_board)
				WHERE b.id_board IS NULL
					AND lb.id_member BETWEEN $_GET[substep] AND $_GET[substep] + 499
				GROUP BY lb.id_board", __FILE__, __LINE__);
			while ($row = $smfFunc['db_fetch_assoc']($result))
				$context['repair_errors'][] = sprintf($txt['repair_missing_log_boards'], $row['id_board']);
			if ($smfFunc['db_num_rows']($result) != 0)
				$to_fix[] = 'missing_log_boards';
			$smfFunc['db_free_result']($result);
		}

		$_GET['step'] = 16;
		$_GET['substep'] = 0;
		pauseRepairProcess($to_fix);
	}

	if ($_GET['step'] <= 16)
	{
		$result = $smfFunc['db_query']('', "
			SELECT MAX(id_member)
			FROM {$db_prefix}log_boards", __FILE__, __LINE__);
		list ($members) = $smfFunc['db_fetch_row']($result);
		$smfFunc['db_free_result']($result);

		for (; $_GET['substep'] < $members; $_GET['substep'] += 500)
		{
			pauseRepairProcess($to_fix, $members);

			$result = $smfFunc['db_query']('', "
				SELECT lb.id_member
				FROM {$db_prefix}log_boards AS lb
					LEFT JOIN {$db_prefix}members AS mem ON (mem.id_member = lb.id_member)
				WHERE mem.id_member IS NULL
					AND lb.id_member BETWEEN $_GET[substep] AND $_GET[substep] + 499
				GROUP BY lb.id_member", __FILE__, __LINE__);
			while ($row = $smfFunc['db_fetch_assoc']($result))
				$context['repair_errors'][] = sprintf($txt['repair_missing_log_boards_members'], $row['id_member']);
			if ($smfFunc['db_num_rows']($result) != 0)
				$to_fix[] = 'missing_log_boards_members';
			$smfFunc['db_free_result']($result);
		}

		$_GET['step'] = 17;
		$_GET['substep'] = 0;
		pauseRepairProcess($to_fix);
	}

	if ($_GET['step'] <= 17)
	{
		$result = $smfFunc['db_query']('', "
			SELECT MAX(id_member)
			FROM {$db_prefix}log_mark_read", __FILE__, __LINE__);
		list ($members) = $smfFunc['db_fetch_row']($result);
		$smfFunc['db_free_result']($result);

		for (; $_GET['substep'] < $members; $_GET['substep'] += 500)
		{
			pauseRepairProcess($to_fix, $members);

			$result = $smfFunc['db_query']('', "
				SELECT lmr.id_board
				FROM {$db_prefix}log_mark_read AS lmr
					LEFT JOIN {$db_prefix}boards AS b ON (b.id_board = lmr.id_board)
				WHERE b.id_board IS NULL
					AND lmr.id_member BETWEEN $_GET[substep] AND $_GET[substep] + 499
				GROUP BY lmr.id_board", __FILE__, __LINE__);
			while ($row = $smfFunc['db_fetch_assoc']($result))
				$context['repair_errors'][] = sprintf($txt['repair_missing_log_mark_read'], $row['id_board']);
			if ($smfFunc['db_num_rows']($result) != 0)
				$to_fix[] = 'missing_log_mark_read';
			$smfFunc['db_free_result']($result);
		}

		$_GET['step'] = 18;
		$_GET['substep'] = 0;
		pauseRepairProcess($to_fix);
	}

	if ($_GET['step'] <= 18)
	{
		$result = $smfFunc['db_query']('', "
			SELECT MAX(id_member)
			FROM {$db_prefix}log_mark_read", __FILE__, __LINE__);
		list ($members) = $smfFunc['db_fetch_row']($result);
		$smfFunc['db_free_result']($result);

		for (; $_GET['substep'] < $members; $_GET['substep'] += 500)
		{
			pauseRepairProcess($to_fix, $members);

			$result = $smfFunc['db_query']('', "
				SELECT lmr.id_member
				FROM {$db_prefix}log_mark_read AS lmr
					LEFT JOIN {$db_prefix}members AS mem ON (mem.id_member = lmr.id_member)
				WHERE mem.id_member IS NULL
					AND lmr.id_member BETWEEN $_GET[substep] AND $_GET[substep] + 499
				GROUP BY lmr.id_member", __FILE__, __LINE__);
			while ($row = $smfFunc['db_fetch_assoc']($result))
				$context['repair_errors'][] = sprintf($txt['repair_missing_log_mark_read_members'], $row['id_member']);
			if ($smfFunc['db_num_rows']($result) != 0)
				$to_fix[] = 'missing_log_mark_read_members';
			$smfFunc['db_free_result']($result);
		}

		$_GET['step'] = 19;
		$_GET['substep'] = 0;
		pauseRepairProcess($to_fix);
	}

	if ($_GET['step'] <= 19)
	{
		$result = $smfFunc['db_query']('', "
			SELECT MAX(id_pm)
			FROM {$db_prefix}pm_recipients", __FILE__, __LINE__);
		list ($pms) = $smfFunc['db_fetch_row']($result);
		$smfFunc['db_free_result']($result);

		for (; $_GET['substep'] < $pms; $_GET['substep'] += 500)
		{
			pauseRepairProcess($to_fix, $pms);

			$result = $smfFunc['db_query']('', "
				SELECT pmr.id_pm
				FROM {$db_prefix}pm_recipients AS pmr
					LEFT JOIN {$db_prefix}personal_messages AS pm ON (pm.id_pm = pmr.id_pm)
				WHERE pm.id_pm IS NULL
					AND pmr.id_pm BETWEEN $_GET[substep] AND $_GET[substep] + 499
				GROUP BY pmr.id_pm", __FILE__, __LINE__);
			while ($row = $smfFunc['db_fetch_assoc']($result))
				$context['repair_errors'][] = sprintf($txt['repair_missing_pms'], $row['id_pm']);
			if ($smfFunc['db_num_rows']($result) != 0)
				$to_fix[] = 'missing_pms';
			$smfFunc['db_free_result']($result);
		}

		$_GET['step'] = 20;
		$_GET['substep'] = 0;
		pauseRepairProcess($to_fix);
	}

	if ($_GET['step'] <= 20)
	{
		$result = $smfFunc['db_query']('', "
			SELECT MAX(id_member)
			FROM {$db_prefix}pm_recipients", __FILE__, __LINE__);
		list ($members) = $smfFunc['db_fetch_row']($result);
		$smfFunc['db_free_result']($result);

		for (; $_GET['substep'] < $members; $_GET['substep'] += 500)
		{
			pauseRepairProcess($to_fix, $members);

			$result = $smfFunc['db_query']('', "
				SELECT pmr.id_member
				FROM {$db_prefix}pm_recipients AS pmr
					LEFT JOIN {$db_prefix}members AS mem ON (mem.id_member = pmr.id_member)
				WHERE pmr.id_member != 0
					AND pmr.id_member BETWEEN $_GET[substep] AND $_GET[substep] + 499
					AND mem.id_member IS NULL
				GROUP BY pmr.id_member", __FILE__, __LINE__);
			while ($row = $smfFunc['db_fetch_assoc']($result))
				$context['repair_errors'][] = sprintf($txt['repair_missing_recipients'], $row['id_member']);
			if ($smfFunc['db_num_rows']($result) != 0)
				$to_fix[] = 'missing_recipients';
			$smfFunc['db_free_result']($result);
		}

		$_GET['step'] = 21;
		$_GET['substep'] = 0;
		pauseRepairProcess($to_fix);
	}

	if ($_GET['step'] <= 21)
	{
		$result = $smfFunc['db_query']('', "
			SELECT MAX(id_pm)
			FROM {$db_prefix}personal_messages", __FILE__, __LINE__);
		list ($pms) = $smfFunc['db_fetch_row']($result);
		$smfFunc['db_free_result']($result);

		for (; $_GET['substep'] < $pms; $_GET['substep'] += 500)
		{
			pauseRepairProcess($to_fix, $pms);

			$result = $smfFunc['db_query']('', "
				SELECT pm.id_pm, pm.id_member_from
				FROM {$db_prefix}personal_messages AS pm
					LEFT JOIN {$db_prefix}members AS mem ON (mem.id_member = pm.id_member_from)
				WHERE pm.id_member_from != 0
					AND pm.id_pm BETWEEN $_GET[substep] AND $_GET[substep] + 499
					AND mem.id_member IS NULL", __FILE__, __LINE__);
			while ($row = $smfFunc['db_fetch_assoc']($result))
				$context['repair_errors'][] = sprintf($txt['repair_missing_senders'], $row['id_pm'], $row['id_member_from']);
			if ($smfFunc['db_num_rows']($result) != 0)
				$to_fix[] = 'missing_senders';
			$smfFunc['db_free_result']($result);
		}

		$_GET['step'] = 22;
		$_GET['substep'] = 0;
		pauseRepairProcess($to_fix);
	}

	if ($_GET['step'] <= 22)
	{
		$result = $smfFunc['db_query']('', "
			SELECT MAX(id_member)
			FROM {$db_prefix}log_notify", __FILE__, __LINE__);
		list ($members) = $smfFunc['db_fetch_row']($result);
		$smfFunc['db_free_result']($result);

		for (; $_GET['substep'] < $members; $_GET['substep'] += 500)
		{
			pauseRepairProcess($to_fix, $members);

			$result = $smfFunc['db_query']('', "
				SELECT ln.id_member
				FROM {$db_prefix}log_notify AS ln
					LEFT JOIN {$db_prefix}members AS mem ON (mem.id_member = ln.id_member)
				WHERE ln.id_member BETWEEN $_GET[substep] AND $_GET[substep] + 499
					AND mem.id_member IS NULL
				GROUP BY ln.id_member", __FILE__, __LINE__);
			while ($row = $smfFunc['db_fetch_assoc']($result))
				$context['repair_errors'][] = sprintf($txt['repair_missing_notify_members'], $row['id_member']);
			if ($smfFunc['db_num_rows']($result) != 0)
				$to_fix[] = 'missing_notify_members';
			$smfFunc['db_free_result']($result);
		}

		$_GET['step'] = 23;
		$_GET['substep'] = 0;
		pauseRepairProcess($to_fix);
	}

	if ($_GET['step'] <= 23)
	{
		$request = $smfFunc['db_query']('', "
			SELECT t.id_topic, fm.subject
			FROM ({$db_prefix}topics AS t, {$db_prefix}messages AS fm)
				LEFT JOIN {$db_prefix}log_search_subjects AS lss ON (lss.id_topic = t.id_topic)
			WHERE fm.id_msg = t.id_first_msg
				AND lss.id_topic IS NULL", __FILE__, __LINE__);
		$found_error = false;
		while ($row = $smfFunc['db_fetch_assoc']($request))
			if (count(text2words($row['subject'])) != 0)
			{
				$context['repair_errors'][] = sprintf($txt['repair_missing_cached_subject'], $row['id_topic']);
				$found_error = true;
			}
		$smfFunc['db_free_result']($request);

		if ($found_error)
			$to_fix[] = 'missing_cached_subject';

		$_GET['step'] = 24;
		$_GET['substep'] = 0;
		pauseRepairProcess($to_fix);
	}

	if ($_GET['step'] <= 24)
	{
		$request = $smfFunc['db_query']('', "
			SELECT lss.word
			FROM {$db_prefix}log_search_subjects AS lss
				LEFT JOIN {$db_prefix}topics AS t ON (t.id_topic = lss.id_topic)
			WHERE t.id_topic IS NULL", __FILE__, __LINE__);
		while ($row = $smfFunc['db_fetch_assoc']($request))
			$context['repair_errors'][] = sprintf($txt['repair_missing_topic_for_cache'], htmlspecialchars($row['word']));
		if ($smfFunc['db_num_rows']($request) != 0)
			$to_fix[] = 'missing_topic_for_cache';
		$smfFunc['db_free_result']($request);

		$_GET['step'] = 25;
		$_GET['substep'] = 0;
		pauseRepairProcess($to_fix);
	}

	if ($_GET['step'] <= 25)
	{
		$result = $smfFunc['db_query']('', "
			SELECT MAX(id_member)
			FROM {$db_prefix}log_polls", __FILE__, __LINE__);
		list ($members) = $smfFunc['db_fetch_row']($result);
		$smfFunc['db_free_result']($result);

		for (; $_GET['substep'] < $members; $_GET['substep'] += 500)
		{
			$result = $smfFunc['db_query']('', "
				SELECT lp.id_poll, lp.id_member
				FROM {$db_prefix}log_polls AS lp
					LEFT JOIN {$db_prefix}members AS mem ON (mem.id_member = lp.id_member)
				WHERE lp.id_member BETWEEN $_GET[substep] AND $_GET[substep] + 499
					AND mem.id_member IS NULL
				GROUP BY lp.id_member", __FILE__, __LINE__);
			while ($row = $smfFunc['db_fetch_assoc']($result))
				$context['repair_errors'][] = sprintf($txt['repair_missing_log_poll_member'], $row['id_poll'], $row['id_member']);
			if ($smfFunc['db_num_rows']($result) != 0)
				$to_fix[] = 'missing_member_vote';
			$smfFunc['db_free_result']($result);

			pauseRepairProcess($to_fix, $members);
		}

		$_GET['step'] = 26;
		$_GET['substep'] = 0;
		pauseRepairProcess($to_fix);
	}

	if ($_GET['step'] <= 26)
	{
		$result = $smfFunc['db_query']('', "
			SELECT MAX(id_poll)
			FROM {$db_prefix}log_polls", __FILE__, __LINE__);
		list ($polls) = $smfFunc['db_fetch_row']($result);
		$smfFunc['db_free_result']($result);

		for (; $_GET['substep'] < $polls; $_GET['substep'] += 500)
		{
			pauseRepairProcess($to_fix, $polls);

			$result = $smfFunc['db_query']('', "
				SELECT lp.id_poll, lp.id_member
				FROM {$db_prefix}log_polls AS lp
					LEFT JOIN {$db_prefix}polls AS p ON (p.id_poll = lp.id_poll)
				WHERE lp.id_poll BETWEEN $_GET[substep] AND $_GET[substep] + 499
					AND p.id_poll IS NULL
				GROUP BY lp.id_poll", __FILE__, __LINE__);
			while ($row = $smfFunc['db_fetch_assoc']($result))
				$context['repair_errors'][] = sprintf($txt['repair_missing_log_poll_vote'], $row['id_member'], $row['id_poll']);
			if ($smfFunc['db_num_rows']($result) != 0)
				$to_fix[] = 'missing_log_poll_vote';
			$smfFunc['db_free_result']($result);
		}

		$_GET['step'] = 27;
		$_GET['substep'] = 0;
		pauseRepairProcess($to_fix);
	}

	if ($_GET['step'] <= 27)
	{
		$result = $smfFunc['db_query']('', "
			SELECT MAX(id_report)
			FROM {$db_prefix}log_reported", __FILE__, __LINE__);
		list ($polls) = $smfFunc['db_fetch_row']($result);
		$smfFunc['db_free_result']($result);

		for (; $_GET['substep'] < $polls; $_GET['substep'] += 500)
		{
			pauseRepairProcess($to_fix, $polls);

			$result = $smfFunc['db_query']('', "
				SELECT lr.id_report, lr.subject
				FROM {$db_prefix}log_reported AS lr
					LEFT JOIN {$db_prefix}log_reported_comments AS lrc ON (lrc.id_report = lr.id_report)
				WHERE lr.id_report BETWEEN $_GET[substep] AND $_GET[substep] + 499
					AND lrc.id_report IS NULL
				GROUP BY lr.id_report", __FILE__, __LINE__);
			while ($row = $smfFunc['db_fetch_assoc']($result))
				$context['repair_errors'][] = sprintf($txt['repair_report_missing_comments'], $row['id_report'], $row['subject']);
			if ($smfFunc['db_num_rows']($result) != 0)
				$to_fix[] = 'report_missing_comments';
			$smfFunc['db_free_result']($result);
		}

		$_GET['step'] = 28;
		$_GET['substep'] = 0;
		pauseRepairProcess($to_fix);
	}

	if ($_GET['step'] <= 28)
	{
		$result = $smfFunc['db_query']('', "
			SELECT MAX(id_report)
			FROM {$db_prefix}log_reported_comments", __FILE__, __LINE__);
		list ($polls) = $smfFunc['db_fetch_row']($result);
		$smfFunc['db_free_result']($result);

		for (; $_GET['substep'] < $polls; $_GET['substep'] += 500)
		{
			pauseRepairProcess($to_fix, $polls);

			$result = $smfFunc['db_query']('', "
				SELECT lrc.id_report, lrc.membername
				FROM {$db_prefix}log_reported_comments AS lrc
					LEFT JOIN {$db_prefix}log_reported AS lr ON (lr.id_report = lrc.id_report)
				WHERE lrc.id_report BETWEEN $_GET[substep] AND $_GET[substep] + 499
					AND lr.id_report IS NULL
				GROUP BY lrc.id_report", __FILE__, __LINE__);
			while ($row = $smfFunc['db_fetch_assoc']($result))
				$context['repair_errors'][] = sprintf($txt['repair_comments_missing_report'], $row['id_report'], $row['membername']);
			if ($smfFunc['db_num_rows']($result) != 0)
				$to_fix[] = 'comments_missing_report';
			$smfFunc['db_free_result']($result);
		}

		$_GET['step'] = 29;
		$_GET['substep'] = 0;
		pauseRepairProcess($to_fix);
	}

	if ($_GET['step'] <= 29)
	{
		$result = $smfFunc['db_query']('', "
			SELECT MAX(id_member)
			FROM {$db_prefix}log_group_requests", __FILE__, __LINE__);
		list ($members) = $smfFunc['db_fetch_row']($result);
		$smfFunc['db_free_result']($result);

		for (; $_GET['substep'] < $members; $_GET['substep'] += 500)
		{
			pauseRepairProcess($to_fix, $members);

			$result = $smfFunc['db_query']('', "
				SELECT lgr.id_member
				FROM {$db_prefix}log_group_requests AS lgr
					LEFT JOIN {$db_prefix}members AS mem ON (mem.id_member = lgr.id_member)
				WHERE lgr.id_member BETWEEN $_GET[substep] AND $_GET[substep] + 499
					AND mem.id_member IS NULL
				GROUP BY lgr.id_member", __FILE__, __LINE__);
			while ($row = $smfFunc['db_fetch_assoc']($result))
				$context['repair_errors'][] = sprintf($txt['repair_group_request_missing_member'], $row['id_member']);
			if ($smfFunc['db_num_rows']($result) != 0)
				$to_fix[] = 'group_request_missing_member';
			$smfFunc['db_free_result']($result);
		}

		$_GET['step'] = 30;
		$_GET['substep'] = 0;
		pauseRepairProcess($to_fix);
	}

	if ($_GET['step'] <= 30)
	{
		$result = $smfFunc['db_query']('', "
			SELECT MAX(id_group)
			FROM {$db_prefix}log_group_requests", __FILE__, __LINE__);
		list ($groups) = $smfFunc['db_fetch_row']($result);
		$smfFunc['db_free_result']($result);

		for (; $_GET['substep'] < $groups; $_GET['substep'] += 500)
		{
			pauseRepairProcess($to_fix, $groups);

			$result = $smfFunc['db_query']('', "
				SELECT lgr.id_group
				FROM {$db_prefix}log_group_requests AS lgr
					LEFT JOIN {$db_prefix}membergroups AS mg ON (mg.id_group = lgr.id_group)
				WHERE lgr.id_group BETWEEN $_GET[substep] AND $_GET[substep] + 499
					AND mg.id_group IS NULL
				GROUP BY lgr.id_group", __FILE__, __LINE__);
			while ($row = $smfFunc['db_fetch_assoc']($result))
				$context['repair_errors'][] = sprintf($txt['repair_group_request_missing_group'], $row['id_group']);
			if ($smfFunc['db_num_rows']($result) != 0)
				$to_fix[] = 'group_request_missing_group';
			$smfFunc['db_free_result']($result);
		}

		$_GET['step'] = 31;
		$_GET['substep'] = 0;
		pauseRepairProcess($to_fix);
	}

	return $to_fix;
}

// Create a salvage area for repair purposes.
function createSalvageArea()
{
	global $db_prefix, $txt, $language, $salvageBoardID, $salvageCatID, $smfFunc;
	static $createOnce = false;

	// Have we already created it?
	if ($createOnce)
		return;
	else
		$createOnce = true;

	// Back to the forum's default language.
	loadLanguage('Admin', $language);

	// Check to see if a 'Salvage Category' exists, if not => insert one.
	$result = $smfFunc['db_query']('', "
		SELECT id_cat
		FROM {$db_prefix}categories
		WHERE name = '" . $smfFunc['db_escape_string']($txt['salvaged_category_name']) . "'
		LIMIT 1", __FILE__, __LINE__);
	if ($smfFunc['db_num_rows']($result) != 0)
		list ($salvageCatID) = $smfFunc['db_fetch_row']($result);
	$smfFunc['db_free_result']($result);

	if (empty($salveageCatID))
	{
		$smfFunc['db_query']('', "
			INSERT INTO {$db_prefix}categories
				(name, cat_order)
			VALUES (SUBSTRING('" . $smfFunc['db_escape_string']($txt['salvaged_category_name']) . "', 1, 255), -1)", __FILE__, __LINE__);
		if (db_affected_rows() <= 0)
		{
			loadLanguage('Admin');
			fatal_lang_error('salvaged_category_error', false);
		}

		$salvageCatID = db_insert_id("{$db_prefix}categories", 'id_cat');
	}

	// Check to see if a 'Salvage Board' exists, if not => insert one.
	$result = $smfFunc['db_query']('', "
		SELECT id_board
		FROM {$db_prefix}boards
		WHERE id_cat = $salvageCatID
			AND name = '" . $smfFunc['db_escape_string']($txt['salvaged_board_name']) . "'
		LIMIT 1", __FILE__, __LINE__);
	if ($smfFunc['db_num_rows']($result) != 0)
		list ($salvageBoardID) = $smfFunc['db_fetch_row']($result);
	$smfFunc['db_free_result']($result);

	if (empty($salvageBoardID))
	{
		$smfFunc['db_query']('', "
			INSERT INTO {$db_prefix}boards
				(name, description, id_cat, member_groups, board_order)
			VALUES (SUBSTRING('" . $smfFunc['db_escape_string']($txt['salvaged_board_name']) . "', 1, 255), SUBSTRING('" . addslashes($txt['salvaged_board_description']) . "', 1, 255), $salvageCatID, '1', -1)", __FILE__, __LINE__);
		if (db_affected_rows() <= 0)
		{
			loadLanguage('Admin');
			fatal_lang_error('salvaged_board_error', false);
		}

		$salvageBoardID = db_insert_id("{$db_prefix}boards", 'id_board');
	}

	$smfFunc['db_query']('alter_table_boards', "
		ALTER TABLE {$db_prefix}boards
		ORDER BY board_order", __FILE__, __LINE__);

	// Restore the user's language.
	loadLanguage('Admin');
}

?>