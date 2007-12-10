<?php
/**********************************************************************************
* RepairBoards.php                                                                *
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
	global $salvageCatID, $salvageBoardID, $smfFunc, $errorTests;

	isAllowedTo('admin_forum');

	// Print out the top of the webpage.
	$context['page_title'] = $txt['admin_repair'];
	$context['sub_template'] = 'rawdata';

	// Make sure the tabs stay nice.
	$context[$context['admin_menu_name']]['tab_data'] = array(
		'title' => &$txt['maintain_title'],
		'help' => '',
		'description' => $txt['maintain_info'],
		'tabs' => array(),
	);

	// Start displaying errors without fixing them.
	if (isset($_GET['fixErrors']))
		checkSession('get');

	// Will want this.
	loadForumTests();

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
					<td>' . $txt['errors_list'] . '</td>
				</tr><tr>
					<td class="windowbg">';

		if (!empty($to_fix))
		{
			$context['raw_data'] .= '
						' . $txt['errors_found'] . ':<br />
						' . implode('
						<br />', $context['repair_errors']) . '<br />
						<br />
						' . $txt['errors_fix'] . '<br />
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

		// Actually do the fix.
		findForumErrors(true);

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
				$topicArray['myApproved'] = (int) $topicArray['myApproved'];

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
				$newTopicID = $smfFunc['db_insert_id']("{$db_prefix}topics", 'id_topic');

				$smfFunc['db_query']('', "
					UPDATE {$db_prefix}messages
					SET id_topic = $newTopicID, id_board = $row[id_board]
					WHERE id_topic = $row[id_topic]", __FILE__, __LINE__);
			}
			$smfFunc['db_free_result']($result);

			// Force the check of unapproved posts for this.
			$to_fix[] = 'stats_topics';
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
				$newBoardID = $smfFunc['db_insert_id']("{$db_prefix}boards", 'id_board');

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

		if (empty($to_fix) || in_array('missing_cached_subject', $to_fix))
		{
			$request = $smfFunc['db_query']('', "
				SELECT t.id_topic, m.subject
				FROM {$db_prefix}topics AS t
					INNER JOIN {$db_prefix}messages AS m ON (m.id_msg = t.id_first_msg)
					LEFT JOIN {$db_prefix}log_search_subjects AS lss ON (lss.id_topic = t.id_topic)
				WHERE lss.id_topic IS NULL", __FILE__, __LINE__);
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

		updateSettings(array(
			'settings_updated' => time(),
		));
		updateStats('message');
		updateStats('topic');
		updateSettings(array(
			'calendar_updated' => time(),
		));

		$context['raw_data'] = '
			<table width="100%" border="0" cellspacing="0" cellpadding="4" class="tborder">
				<tr class="titlebg">
					<td>' . $txt['errors_fixing'] . '</td>
				</tr><tr>
					<td class="windowbg">
						' . $txt['errors_fixed'] . '<br />
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

// Load up all the tests we might want to do ;)
function loadForumTests()
{
	global $smfFunc, $db_prefix, $errorTests;

	/* Here this array is defined like so:
		string check_query:	Query to be executed when testing if errors exist.
		string check_type:	Defines how it knows if a problem was found. If set to count looks for the first variable from check_query
					being > 0. Anything else it looks for some results. If not set assumes you want results.
		string fix_it_query:	When doing fixes if an error was detected this query is executed to "fix" it.
		string fix_query:	The query to execute to get data when doing a fix. If not set check_query is used again.
		array fix_collect:	This array is used if the fix is basically gathering all broken ids and then doing something with it.
			- string index:		The value returned from the main query and passed to the processing function.
			- process:		A function passed an array of ids to execute the fix on.
	*/

	// This great array contains all of our error checks, fixes, etc etc etc.
	$errorTests = array(
		// Make a last-ditch-effort check to get rid of topics with zeros..
		'zero_topics' => array(
			'check_query' => "
				SELECT COUNT(*)
				FROM {$db_prefix}topics
				WHERE id_topic = 0",
			'check_type' => 'count',
			'fix_it_query' => "
				UPDATE {$db_prefix}topics
				SET id_topic = NULL
				WHERE id_topic = 0",
			'message' => 'repair_zero_ids',
		),
		// ... and same with messages.
		'zero_messages' => array(
			'check_query' => "
				SELECT COUNT(*)
				FROM {$db_prefix}messages
				WHERE id_msg = 0",
			'check_type' => 'count',
			'fix_it_query' => "
				UPDATE {$db_prefix}messages
				SET id_msg = NULL
				WHERE id_msg = 0",
			'message' => 'repair_zero_ids',
		),
		// Find messages that don't have existing topics.
		'missing_topics' => array(
			'check_query' => "
				SELECT m.id_topic, m.id_msg
				FROM {$db_prefix}messages AS m
					LEFT JOIN {$db_prefix}topics AS t ON (t.id_topic = m.id_topic)
				WHERE t.id_topic IS NULL
				ORDER BY m.id_topic, m.id_msg",
			'fix_query' => "
				SELECT
					m.id_board, m.id_topic, MIN(m.id_msg) AS myid_first_msg, MAX(m.id_msg) AS myid_last_msg,
					COUNT(*) - 1 AS myNumReplies
				FROM {$db_prefix}messages AS m
					LEFT JOIN {$db_prefix}topics AS t ON (t.id_topic = m.id_topic)
				WHERE t.id_topic IS NULL
				GROUP BY m.id_topic",
			'fix_processing' => create_function('$row', '
				global $smfFunc, $db_prefix;

				// Only if we don\'t have a reasonable idea of where to put it.
				if ($row[\'id_board\'] == 0)
				{
					createSalvageArea();
					$row[\'id_board\'] = $salvageBoardID;
				}

				$memberStartedID = getMsgMemberID($row[\'myid_first_msg\']);
				$memberUpdatedID = getMsgMemberID($row[\'myid_last_msg\']);

				$smfFunc[\'db_query\'](\'\', "
					INSERT INTO {$db_prefix}topics
						(id_board, id_member_started, id_member_updated, id_first_msg, id_last_msg, num_replies)
					VALUES ($row[id_board], $memberStartedID, $memberUpdatedID,
						$row[myid_first_msg], $row[myid_last_msg], $row[myNumReplies])", __FILE__, __LINE__);
				$newTopicID = $smfFunc[\'db_insert_id\']("{$db_prefix}topics", \'id_topic\');

				$smfFunc[\'db_query\'](\'\', "
					UPDATE {$db_prefix}messages
					SET id_topic = $newTopicID, id_board = $row[id_board]
					WHERE id_topic = $row[id_topic]", __FILE__, __LINE__);
				'),
			'force_fix' => 'stats_topics',
			'messages' => array('repair_missing_topics', 'id_msg', 'id_topic'),
		),
		// Find topics with no messages.
		'missing_messages' => array(
			'substeps' => array(
				'step_size' => 1000,
				'step_max' => "
					SELECT MAX(id_topic)
					FROM {$db_prefix}topics"
			),
			'check_query' => "
				SELECT t.id_topic, COUNT(m.id_msg) AS num_msg
				FROM {$db_prefix}topics AS t
					LEFT JOIN {$db_prefix}messages AS m ON (m.id_topic = t.id_topic)
				WHERE t.id_topic BETWEEN {STEP_LOW} AND {STEP_HIGH}
				GROUP BY t.id_topic
				HAVING COUNT(m.id_msg) = 0",
			// Remove all topics that have zero messages in the messages table.
			'fix_collect' => array(
				'index' => 'id_topic',
				'process' => create_function('$topics', '
					global $smfFunc, $db_prefix;
					$smfFunc[\'db_query\'](\'\', "
						DELETE FROM {$db_prefix}topics
						WHERE id_topic IN (" . implode(\',\', $topics) . ")", __FILE__, __LINE__);
					$smfFunc[\'db_query\'](\'\', "
						DELETE FROM {$db_prefix}log_topics
						WHERE id_topic IN (" . implode(\',\', $topics) . ")", __FILE__, __LINE__);
				'),
			),
			'messages' => array('repair_missing_messages', 'id_topic'),
		),
		'stats_topics' => array(
			'substeps' => array(
				'step_size' => 1000,
				'step_max' => "
					SELECT MAX(id_topic)
					FROM {$db_prefix}topics"
			),
			'check_query' => "
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
				WHERE t.id_topic BETWEEN {STEP_LOW} AND {STEP_HIGH}
				GROUP BY t.id_topic
				HAVING id_first_msg != myid_first_msg OR id_last_msg != myid_last_msg
					OR approved != myApproved
				ORDER BY t.id_topic",
			'message_function' => create_function('$row', '
				global $txt, $context;

				if ($row[\'id_first_msg\'] != $row[\'myid_first_msg\'])
					$context[\'repair_errors\'][] = sprintf($txt[\'repair_stats_topics_1\'], $row[\'id_topic\'], $row[\'id_first_msg\']);
				if ($row[\'id_last_msg\'] != $row[\'myid_last_msg\'])
					$context[\'repair_errors\'][] = sprintf($txt[\'repair_stats_topics_2\'], $row[\'id_topic\'], $row[\'id_last_msg\']);
				if ($row[\'approved\'] != $row[\'myApproved\'])
					$context[\'repair_errors\'][] = sprintf($txt[\'repair_stats_topics_5\'], $row[\'id_topic\']);

				return true;
			'),
		),
		// Find topics with incorrect num_replies.
		'stats_topics2' => array(
			'substeps' => array(
				'step_size' => 1000,
				'step_max' => "
					SELECT MAX(id_topic)
					FROM {$db_prefix}topics"
			),
			'check_query' => "
				SELECT
					t.id_topic, t.num_replies,
					IF (COUNT(ma.id_msg), IF (mf.approved, COUNT(ma.id_msg) - 1, COUNT(ma.id_msg)), 0) AS myNumReplies
				FROM {$db_prefix}topics AS t
					LEFT JOIN {$db_prefix}messages AS ma ON (ma.id_topic = t.id_topic AND ma.approved = 1)
					LEFT JOIN {$db_prefix}messages AS mf ON (mf.id_msg = t.id_first_msg)
				WHERE t.id_topic BETWEEN {STEP_LOW} AND {STEP_HIGH}
				GROUP BY t.id_topic
				HAVING num_replies != myNumReplies
				ORDER BY t.id_topic",
			'messages' => array('repair_stats_topics_3', 'id_topic', 'num_replies'),
		),
		// Find topics with incorrect unapproved_posts.
		'stats_topics3' => array(
			'substeps' => array(
				'step_size' => 1000,
				'step_max' => "
					SELECT MAX(id_topic)
					FROM {$db_prefix}topics"
			),
			'check_query' => "
				SELECT
					t.id_topic, t.unapproved_posts, COUNT(mu.id_msg) AS myUnapprovedPosts
				FROM {$db_prefix}topics AS t
					LEFT JOIN {$db_prefix}messages AS mu ON (mu.id_topic = t.id_topic AND mu.approved = 0)
				WHERE t.id_topic BETWEEN {STEP_LOW} AND {STEP_HIGH}
				GROUP BY t.id_topic
				HAVING unapproved_posts != myUnapprovedPosts
				ORDER BY t.id_topic",
			'messages' => array('repair_stats_topics_4', 'id_topic', 'unapproved_posts'),
		),
		// Find topics with nonexistent boards.
		'missing_boards' => array(
			'substeps' => array(
				'step_size' => 1000,
				'step_max' => "
					SELECT MAX(id_topic)
					FROM {$db_prefix}topics"
			),
			'check_query' => "
				SELECT t.id_topic, t.id_board
				FROM {$db_prefix}topics AS t
					LEFT JOIN {$db_prefix}boards AS b ON (b.id_board = t.id_board)
				WHERE b.id_board IS NULL
					AND t.id_topic BETWEEN {STEP_LOW} AND {STEP_HIGH}
				ORDER BY t.id_board, t.id_topic",
			'messages' => array('repair_missing_boards', 'id_topic', 'id_board'),
		),
		// Find boards with nonexistent categories.
		'missing_categories' => array(
			'check_query' => "
				SELECT b.id_board, b.id_cat
				FROM {$db_prefix}boards AS b
					LEFT JOIN {$db_prefix}categories AS c ON (c.id_cat = b.id_cat)
				WHERE c.id_cat IS NULL
				ORDER BY b.id_cat, b.id_board",
			'fix_collect' => array(
				'index' => 'id_cat',
				'process' => create_function('$cats', '
					global $smfFunc, $db_prefix, $salvageCatID;
					createSalvageArea();
					$smfFunc[\'db_query\'](\'\', "
						UPDATE {$db_prefix}boards
						SET id_cat = $salvageCatID
						WHERE id_cat IN (" . implode(\',\', $cats) . ")", __FILE__, __LINE__);
				'),
			),
			'messages' => array('repair_missing_categories', 'id_board', 'id_cat'),
		),
		// Find messages with nonexistent members.
		'missing_posters' => array(
			'substeps' => array(
				'step_size' => 2000,
				'step_max' => "
					SELECT MAX(id_msg)
					FROM {$db_prefix}messages"
			),
			'check_query' => "
				SELECT m.id_msg, m.id_member
				FROM {$db_prefix}messages AS m
					LEFT JOIN {$db_prefix}members AS mem ON (mem.id_member = m.id_member)
				WHERE mem.id_member IS NULL
					AND m.id_member != 0
					AND m.id_msg BETWEEN {STEP_LOW} AND {STEP_HIGH}
				ORDER BY m.id_msg",
			// Last step-make sure all non-guest posters still exist.
			'fix_collect' => array(
				'index' => 'id_msg',
				'process' => create_function('$msgs', '
					global $smfFunc, $db_prefix;
					$smfFunc[\'db_query\'](\'\', "
						UPDATE {$db_prefix}messages
						SET id_member = 0
						WHERE id_msg IN (" . implode(\',\', $msgs) . ")", __FILE__, __LINE__);
				'),
			),
			'messages' => array('repair_missing_posters', 'id_msg', 'id_member'),
		),
		// Find boards with nonexistent parents.
		'missing_parents' => array(
			'check_query' => "
				SELECT b.id_board, b.id_parent
				FROM {$db_prefix}boards AS b
					LEFT JOIN {$db_prefix}boards AS p ON (p.id_board = b.id_parent)
				WHERE b.id_parent != 0
					AND (p.id_board IS NULL OR p.id_board = b.id_board)
				ORDER BY b.id_parent, b.id_board",
			'fix_collect' => array(
				'index' => 'id_parent',
				'process' => create_function('$parents', '
					global $smfFunc, $db_prefix, $salvageBoardID, $salvageCatID;
					createSalvageArea();
					$smfFunc[\'db_query\'](\'\', "
						UPDATE {$db_prefix}boards
						SET id_parent = $salvageBoardID, id_cat = $salvageCatID, child_level = 1
						WHERE id_parent IN (" . implode(\',\', $parents) . ")", __FILE__, __LINE__);
				'),
			),
			'messages' => array('repair_missing_parents', 'id_board', 'id_parent'),
		),
		'missing_polls' => array(
			'substeps' => array(
				'step_size' => 500,
				'step_max' => "
					SELECT MAX(id_poll)
					FROM {$db_prefix}topics"
			),
			'check_query' => "
				SELECT t.id_poll, t.id_topic
				FROM {$db_prefix}topics AS t
					LEFT JOIN {$db_prefix}polls AS p ON (p.id_poll = t.id_poll)
				WHERE t.id_poll != 0
					AND t.id_poll BETWEEN {STEP_LOW} AND {STEP_HIGH}
					AND p.id_poll IS NULL
				GROUP BY t.id_poll",
			'fix_collect' => array(
				'index' => 'id_poll',
				'process' => create_function('$polls', '
					global $smfFunc, $db_prefix;
					$smfFunc[\'db_query\'](\'\', "
						UPDATE {$db_prefix}topics
						SET id_poll = 0
						WHERE id_poll IN (" . implode(\',\', $polls) . ")", __FILE__, __LINE__);
				'),
			),			
			'messages' => array('repair_missing_polls', 'id_topic', 'id_poll'),
		),
		'missing_calendar_topics' => array(
			'substeps' => array(
				'step_size' => 1000,
				'step_max' => "
					SELECT MAX(id_topic)
					FROM {$db_prefix}calendar"
			),
			'check_query' => "
				SELECT cal.id_topic, cal.id_event
				FROM {$db_prefix}calendar AS cal
					LEFT JOIN {$db_prefix}topics AS t ON (t.id_topic = cal.id_topic)
				WHERE cal.id_topic != 0
					AND cal.id_topic BETWEEN {STEP_LOW} AND {STEP_HIGH}
					AND t.id_topic IS NULL
				ORDER BY cal.id_topic",
			'fix_collect' => array(
				'index' => 'id_topic',
				'process' => create_function('$events', '
					global $smfFunc, $db_prefix;
					$smfFunc[\'db_query\'](\'\', "
						UPDATE {$db_prefix}calendar
						SET id_topic = 0, id_board = 0
						WHERE id_topic IN (" . implode(\',\', $events) . ")", __FILE__, __LINE__);
				'),
			),		
			'messages' => array('repair_missing_calendar_topics', 'id_event', 'id_topic'),
		),
		'missing_log_topics' => array(
			'substeps' => array(
				'step_size' => 250,
				'step_max' => "
					SELECT MAX(id_member)
					FROM {$db_prefix}log_topics"
			),
			'check_query' => "
				SELECT lt.id_topic
				FROM {$db_prefix}log_topics AS lt
					LEFT JOIN {$db_prefix}topics AS t ON (t.id_topic = lt.id_topic)
				WHERE t.id_topic IS NULL
					AND lt.id_member BETWEEN {STEP_LOW} AND {STEP_HIGH}",
			'fix_collect' => array(
				'index' => 'id_topic',
				'process' => create_function('$topics', '
					global $smfFunc, $db_prefix;
					$smfFunc[\'db_query\'](\'\', "
						DELETE FROM {$db_prefix}log_topics
						WHERE id_topic IN (" . implode(\',\', $topics) . ")", __FILE__, __LINE__);
				'),
			),
			'messages' => array('repair_missing_log_topics', 'id_topic'),
		),
		'missing_log_topics_members' => array(
			'substeps' => array(
				'step_size' => 150,
				'step_max' => "
					SELECT MAX(id_member)
					FROM {$db_prefix}log_topics"
			),
			'check_query' => "
				SELECT lt.id_member
				FROM {$db_prefix}log_topics AS lt
					LEFT JOIN {$db_prefix}members AS mem ON (mem.id_member = lt.id_member)
				WHERE mem.id_member IS NULL
					AND lt.id_member BETWEEN {STEP_LOW} AND {STEP_HIGH}
				GROUP BY lt.id_member",
			'fix_collect' => array(
				'index' => 'id_member',
				'process' => create_function('$members', '
					global $smfFunc, $db_prefix;
					$smfFunc[\'db_query\'](\'\', "
						DELETE FROM {$db_prefix}log_topics
						WHERE id_member IN (" . implode(\',\', $members) . ")", __FILE__, __LINE__);
				'),
			),
			'messages' => array('repair_missing_log_topics_members', 'id_member'),
		),
		'missing_log_boards' => array(
			'substeps' => array(
				'step_size' => 500,
				'step_max' => "
					SELECT MAX(id_member)
					FROM {$db_prefix}log_boards"
			),
			'check_query' => "
				SELECT lb.id_board
				FROM {$db_prefix}log_boards AS lb
					LEFT JOIN {$db_prefix}boards AS b ON (b.id_board = lb.id_board)
				WHERE b.id_board IS NULL
					AND lb.id_member BETWEEN {STEP_LOW} AND {STEP_HIGH}
				GROUP BY lb.id_board",
			'fix_collect' => array(
				'index' => 'id_board',
				'process' => create_function('$boards', '
					global $smfFunc, $db_prefix;
					$smfFunc[\'db_query\'](\'\', "
						DELETE FROM {$db_prefix}log_boards
						WHERE id_board IN (" . implode(\',\', $boards) . ")", __FILE__, __LINE__);
				'),
			),
			'messages' => array('repair_missing_log_boards', 'id_board'),
		),
		'missing_log_boards_members' => array(
			'substeps' => array(
				'step_size' => 500,
				'step_max' => "
					SELECT MAX(id_member)
					FROM {$db_prefix}log_boards"
			),
			'check_query' => "
				SELECT lb.id_member
				FROM {$db_prefix}log_boards AS lb
					LEFT JOIN {$db_prefix}members AS mem ON (mem.id_member = lb.id_member)
				WHERE mem.id_member IS NULL
					AND lb.id_member BETWEEN {STEP_LOW} AND {STEP_HIGH}
				GROUP BY lb.id_member",
			'fix_collect' => array(
				'index' => 'id_member',
				'process' => create_function('$members', '
					global $smfFunc, $db_prefix;
					$smfFunc[\'db_query\'](\'\', "
						DELETE FROM {$db_prefix}log_boards
						WHERE id_member IN (" . implode(\',\', $members) . ")", __FILE__, __LINE__);
				'),
			),
			'messages' => array('repair_missing_log_boards_members', 'id_member'),
		),
		'missing_log_mark_read' => array(
			'substeps' => array(
				'step_size' => 500,
				'step_max' => "
					SELECT MAX(id_member)
					FROM {$db_prefix}log_mark_read"
			),
			'check_query' => "
				SELECT lmr.id_board
				FROM {$db_prefix}log_mark_read AS lmr
					LEFT JOIN {$db_prefix}boards AS b ON (b.id_board = lmr.id_board)
				WHERE b.id_board IS NULL
					AND lmr.id_member BETWEEN {STEP_LOW} AND {STEP_HIGH}
				GROUP BY lmr.id_board",
			'fix_collect' => array(
				'index' => 'id_board',
				'process' => create_function('$boards', '
					global $smfFunc, $db_prefix;
					$smfFunc[\'db_query\'](\'\', "
						DELETE FROM {$db_prefix}log_mark_read
						WHERE id_board IN (" . implode(\',\', $boards) . ")", __FILE__, __LINE__);
				'),
			),
			'messages' => array('repair_missing_log_mark_read', 'id_board'),
		),
		'missing_log_mark_read_members' => array(
			'substeps' => array(
				'step_size' => 500,
				'step_max' => "
					SELECT MAX(id_member)
					FROM {$db_prefix}log_mark_read"
			),
			'check_query' => "
				SELECT lmr.id_member
				FROM {$db_prefix}log_mark_read AS lmr
					LEFT JOIN {$db_prefix}members AS mem ON (mem.id_member = lmr.id_member)
				WHERE mem.id_member IS NULL
					AND lmr.id_member BETWEEN {STEP_LOW} AND {STEP_HIGH}
				GROUP BY lmr.id_member",
			'fix_collect' => array(
				'index' => 'id_member',
				'process' => create_function('$members', '
					global $smfFunc, $db_prefix;
					$smfFunc[\'db_query\'](\'\', "
						DELETE FROM {$db_prefix}log_mark_read
						WHERE id_member IN (" . implode(\',\', $members) . ")", __FILE__, __LINE__);
				'),
			),
			'messages' => array('repair_missing_log_mark_read_members', 'id_member'),
		),
		'missing_pms' => array(
			'substeps' => array(
				'step_size' => 500,
				'step_max' => "
					SELECT MAX(id_pm)
					FROM {$db_prefix}pm_recipients"
			),
			'check_query' => "
				SELECT pmr.id_pm
				FROM {$db_prefix}pm_recipients AS pmr
					LEFT JOIN {$db_prefix}personal_messages AS pm ON (pm.id_pm = pmr.id_pm)
				WHERE pm.id_pm IS NULL
					AND pmr.id_pm BETWEEN {STEP_LOW} AND {STEP_HIGH}
				GROUP BY pmr.id_pm",
			'fix_collect' => array(
				'index' => 'id_pm',
				'process' => create_function('$pms', '
					global $smfFunc, $db_prefix;
					$smfFunc[\'db_query\'](\'\', "
						DELETE FROM {$db_prefix}pm_recipients
						WHERE id_pm IN (" . implode(\',\', $pms) . ")", __FILE__, __LINE__);
				'),
			),
			'messages' => array('repair_missing_pms', 'id_pm'),
		),
		'missing_recipients' => array(
			'substeps' => array(
				'step_size' => 500,
				'step_max' => "
					SELECT MAX(id_member)
					FROM {$db_prefix}pm_recipients"
			),
			'check_query' => "
				SELECT pmr.id_member
				FROM {$db_prefix}pm_recipients AS pmr
					LEFT JOIN {$db_prefix}members AS mem ON (mem.id_member = pmr.id_member)
				WHERE pmr.id_member != 0
					AND pmr.id_member BETWEEN {STEP_LOW} AND {STEP_HIGH}
					AND mem.id_member IS NULL
				GROUP BY pmr.id_member",
			'fix_collect' => array(
				'index' => 'id_member',
				'process' => create_function('$members', '
					global $smfFunc, $db_prefix;
					$smfFunc[\'db_query\'](\'\', "
						DELETE FROM {$db_prefix}pm_recipients
						WHERE id_member IN (" . implode(\',\', $members) . ")", __FILE__, __LINE__);
				'),
			),	
			'messages' => array('repair_missing_recipients', 'id_member'),
		),
		'missing_senders' => array(
			'substeps' => array(
				'step_size' => 500,
				'step_max' => "
					SELECT MAX(id_pm)
					FROM {$db_prefix}personal_messages"
			),
			'check_query' => "
				SELECT pm.id_pm, pm.id_member_from
				FROM {$db_prefix}personal_messages AS pm
					LEFT JOIN {$db_prefix}members AS mem ON (mem.id_member = pm.id_member_from)
				WHERE pm.id_member_from != 0
					AND pm.id_pm BETWEEN {STEP_LOW} AND {STEP_HIGH}
					AND mem.id_member IS NULL",
			'fix_collect' => array(
				'index' => 'id_pm',
				'process' => create_function('$guestMessages', '
					global $smfFunc, $db_prefix;
					$smfFunc[\'db_query\'](\'\', "
						UPDATE {$db_prefix}personal_messages
						SET id_member_from = 0
						WHERE id_pm IN (" . implode(\',\', $guestMessages) . ")", __FILE__, __LINE__);
				'),
			),	
			'messages' => array('repair_missing_senders', 'id_pm', 'id_member_from'),
		),
		'missing_notify_members' => array(
			'substeps' => array(
				'step_size' => 500,
				'step_max' => "
					SELECT MAX(id_member)
					FROM {$db_prefix}log_notify"
			),
			'check_query' => "
				SELECT ln.id_member
				FROM {$db_prefix}log_notify AS ln
					LEFT JOIN {$db_prefix}members AS mem ON (mem.id_member = ln.id_member)
				WHERE ln.id_member BETWEEN {STEP_LOW} AND {STEP_HIGH}
					AND mem.id_member IS NULL
				GROUP BY ln.id_member",
			'fix_collect' => array(
				'index' => 'id_member',
				'process' => create_function('$members', '
					global $smfFunc, $db_prefix;
					$smfFunc[\'db_query\'](\'\', "
						DELETE FROM {$db_prefix}log_notify
						WHERE id_member IN (" . implode(\',\', $members) . ")", __FILE__, __LINE__);
				'),
			),		
			'messages' => array('repair_missing_notify_members', 'id_member'),
		),
		'missing_cached_subject' => array(
			'substeps' => array(
				'step_size' => 500,
				'step_max' => "
					SELECT MAX(id_topic)
					FROM {$db_prefix}topics"
			),
			'check_query' => "
				SELECT t.id_topic, fm.subject
				FROM {$db_prefix}topics AS t
					INNER JOIN {$db_prefix}messages AS fm ON (fm.id_msg = t.id_first_msg)
					LEFT JOIN {$db_prefix}log_search_subjects AS lss ON (lss.id_topic = t.id_topic)
				WHERE t.id_topic BETWEEN {STEP_LOW} AND {STEP_HIGH}
					AND lss.id_topic IS NULL",
			'message_function' => create_function('$row', '
				global $txt, $context;

				if (count(text2words($row[\'subject\'])) != 0)
				{
					$context[\'repair_errors\'][] = sprintf($txt[\'repair_missing_cached_subject\'], $row[\'id_topic\']);
					return true;
				}

				return false;
			'),
		),
		'missing_topic_for_cache' => array(
			'substeps' => array(
				'step_size' => 500,
				'step_max' => "
					SELECT MAX(id_topic)
					FROM {$db_prefix}log_search_subjects"
			),
			'check_query' => "
				SELECT lss.id_topic, lss.word
				FROM {$db_prefix}log_search_subjects AS lss
					LEFT JOIN {$db_prefix}topics AS t ON (t.id_topic = lss.id_topic)
				WHERE lss.id_topic BETWEEN {STEP_LOW} AND {STEP_HIGH}
					AND t.id_topic IS NULL",
			'fix_collect' => array(
				'index' => 'id_topic',
				'process' => create_function('$deleteTopics', '
					global $smfFunc, $db_prefix;
					$smfFunc[\'db_query\'](\'\', "
						DELETE FROM {$db_prefix}log_search_subjects
						WHERE id_topic IN (" . implode(\',\', $deleteTopics) . ")", __FILE__, __LINE__);
				'),
			),
			'messages' => array('repair_missing_topic_for_cache', 'word'),
		),
		'missing_member_vote' => array(
			'substeps' => array(
				'step_size' => 500,
				'step_max' => "
					SELECT MAX(id_member)
					FROM {$db_prefix}log_polls"
			),
			'check_query' => "
				SELECT lp.id_poll, lp.id_member
				FROM {$db_prefix}log_polls AS lp
					LEFT JOIN {$db_prefix}members AS mem ON (mem.id_member = lp.id_member)
				WHERE lp.id_member BETWEEN {STEP_LOW} AND {STEP_HIGH}
					AND lp.id_member > 0
					AND mem.id_member IS NULL
				GROUP BY lp.id_member",
			'fix_collect' => array(
				'index' => 'id_member',
				'process' => create_function('$members', '
					global $smfFunc, $db_prefix;
					$smfFunc[\'db_query\'](\'\', "
						DELETE FROM {$db_prefix}log_polls
						WHERE id_member IN (" . implode(\',\', $members) . ")", __FILE__, __LINE__);
				'),
			),
			'messages' => array('repair_missing_log_poll_member', 'id_poll', 'id_member'),
		),
		'missing_log_poll_vote' => array(
			'substeps' => array(
				'step_size' => 500,
				'step_max' => "
					SELECT MAX(id_poll)
					FROM {$db_prefix}log_polls"
			),
			'check_query' => "
				SELECT lp.id_poll, lp.id_member
				FROM {$db_prefix}log_polls AS lp
					LEFT JOIN {$db_prefix}polls AS p ON (p.id_poll = lp.id_poll)
				WHERE lp.id_poll BETWEEN {STEP_LOW} AND {STEP_HIGH}
					AND p.id_poll IS NULL
				GROUP BY lp.id_poll",
			'fix_collect' => array(
				'index' => 'id_poll',
				'process' => create_function('$polls', '
					global $smfFunc, $db_prefix;
					$smfFunc[\'db_query\'](\'\', "
						DELETE FROM {$db_prefix}log_polls
						WHERE id_poll IN (" . implode(\',\', $polls) . ")", __FILE__, __LINE__);
				'),
			),
			'messages' => array('repair_missing_log_poll_vote', 'id_member', 'id_poll'),
		),
		'report_missing_comments' => array(
			'substeps' => array(
				'step_size' => 500,
				'step_max' => "
					SELECT MAX(id_report)
					FROM {$db_prefix}log_reported"
			),
			'check_query' => "
				SELECT lr.id_report, lr.subject
				FROM {$db_prefix}log_reported AS lr
					LEFT JOIN {$db_prefix}log_reported_comments AS lrc ON (lrc.id_report = lr.id_report)
				WHERE lr.id_report BETWEEN {STEP_LOW} AND {STEP_HIGH}
					AND lrc.id_report IS NULL
				GROUP BY lr.id_report",
			'fix_collect' => array(
				'index' => 'id_report',
				'process' => create_function('$reports', '
					global $smfFunc, $db_prefix;
					$smfFunc[\'db_query\'](\'\', "
						DELETE FROM {$db_prefix}log_reported
						WHERE id_report IN (" . implode(\',\', $reports) . ")", __FILE__, __LINE__);
				'),
			),
			'messages' => array('repair_report_missing_comments', 'id_report', 'subject'),
		),
		'comments_missing_report' => array(
			'substeps' => array(
				'step_size' => 500,
				'step_max' => "
					SELECT MAX(id_report)
					FROM {$db_prefix}log_reported_comments"
			),
			'check_query' => "
				SELECT lrc.id_report, lrc.membername
				FROM {$db_prefix}log_reported_comments AS lrc
					LEFT JOIN {$db_prefix}log_reported AS lr ON (lr.id_report = lrc.id_report)
				WHERE lrc.id_report BETWEEN {STEP_LOW} AND {STEP_HIGH}
					AND lr.id_report IS NULL
				GROUP BY lrc.id_report",
			'fix_collect' => array(
				'index' => 'id_report',
				'process' => create_function('$reports', '
					global $smfFunc, $db_prefix;
					$smfFunc[\'db_query\'](\'\', "
						DELETE FROM {$db_prefix}log_reported_comments
						WHERE id_report IN (" . implode(\',\', $reports) . ")", __FILE__, __LINE__);
				'),
			),	
			'messages' => array('repair_comments_missing_report', 'id_report', 'membername'),
		),
		'group_request_missing_member' => array(
			'substeps' => array(
				'step_size' => 500,
				'step_max' => "
					SELECT MAX(id_member)
					FROM {$db_prefix}log_group_requests"
			),
			'check_query' => "
				SELECT lgr.id_member
				FROM {$db_prefix}log_group_requests AS lgr
					LEFT JOIN {$db_prefix}members AS mem ON (mem.id_member = lgr.id_member)
				WHERE lgr.id_member BETWEEN {STEP_LOW} AND {STEP_HIGH}
					AND mem.id_member IS NULL
				GROUP BY lgr.id_member",
			'fix_collect' => array(
				'index' => 'id_member',
				'process' => create_function('$members', '
					global $smfFunc, $db_prefix;
					$smfFunc[\'db_query\'](\'\', "
						DELETE FROM {$db_prefix}log_group_requests
						WHERE id_member IN (" . implode(\',\', $members) . ")", __FILE__, __LINE__);
				'),
			),
			'messages' => array('repair_group_request_missing_member', 'id_member'),
		),
		'group_request_missing_group' => array(
			'substeps' => array(
				'step_size' => 500,
				'step_max' => "
					SELECT MAX(id_group)
					FROM {$db_prefix}log_group_requests"
			),
			'check_query' => "
				SELECT lgr.id_group
				FROM {$db_prefix}log_group_requests AS lgr
					LEFT JOIN {$db_prefix}membergroups AS mg ON (mg.id_group = lgr.id_group)
				WHERE lgr.id_group BETWEEN {STEP_LOW} AND {STEP_HIGH}
					AND mg.id_group IS NULL
				GROUP BY lgr.id_group",
			'fix_collect' => array(
				'index' => 'id_group',
				'process' => create_function('$groups', '
					global $smfFunc, $db_prefix;
					$smfFunc[\'db_query\'](\'\', "
						DELETE FROM {$db_prefix}log_group_requests
						WHERE id_group IN (" . implode(\',\', $groups) . ")", __FILE__, __LINE__);
				'),
			),
			'messages' => array('repair_group_request_missing_group', 'id_group'),
		),
	);
}

function findForumErrors($do_fix = false)
{
	global $db_prefix, $context, $txt, $smfFunc, $errorTests;

	// This may take some time...
	@set_time_limit(600);

	$to_fix = !empty($_SESSION['repairboards_to_fix']) ? $_SESSION['repairboards_to_fix'] : array();
	$context['repair_errors'] = isset($_SESSION['repairboards_to_fix2']) ? $_SESSION['repairboards_to_fix2'] : array();

	$_GET['step'] = empty($_GET['step']) ? 0 : (int) $_GET['step'];
	$_GET['substep'] = empty($_GET['substep']) ? 0 : (int) $_GET['substep'];

	// For all the defined error types do the necessary tests.
	$current_step = -1;
	foreach ($errorTests as $error_type => $test)
	{
		$current_step++;

		// Already done this?
		if ($_GET['step'] > $current_step)
			continue;

		// If we're fixing it but it ain't broke why try?
		if ($do_fix && !in_array($error_type, $to_fix))
		{
			$_GET['step']++;
			continue;
		}

		// Has it got substeps?
		if (isset($test['substeps']))
		{
			$step_size = isset($test['substeps']['step_size']) ? $test['substeps']['step_size'] : 100;
			$request = $smfFunc['db_query']('',
				$test['substeps']['step_max'], __FILE__, __LINE__);
			list ($step_max) = $smfFunc['db_fetch_row']($request);
			$smfFunc['db_free_result']($request);

			$test['check_query'] = strtr($test['check_query'], array('{STEP_LOW}' => $_GET['substep'], '{STEP_HIGH}' => $_GET['substep'] + $step_size - 1));

			// Nothing?
			if ($step_max == 0)
			{
				$_GET['step']++;
				continue;
			}
		}

		// What is the testing query (Changes if we are testing or fixing)
		if (!$do_fix)
			$test_query = 'check_query';
		else
			$test_query = isset($test['fix_query']) ? 'fix_query' : 'check_query';

		// Do the test...
		$request = $smfFunc['db_query']('',
			$test[$test_query], __FILE__, __LINE__);
		$needs_fix = false;
		// Does it need a fix?
		if (!empty($test['check_type']) && $test['check_type'] == 'count')
			list ($needs_fix) = $smfFunc['db_fetch_row']($request);
		else
			$needs_fix = $smfFunc['db_num_rows']($request);

		if ($needs_fix)
		{
			// What about a message to the user?
			if (!$do_fix)
			{
				// Assume need to fix.
				$found_errors = true;

				if (isset($test['message']))
					$context['repair_errors'][] = $txt[$test['message']];
				// One per row!
				elseif (isset($test['messages']))
				{
					while ($row = $smfFunc['db_fetch_assoc']($request))
					{
						$variables = $test['messages'];
						foreach ($variables as $k => $v)
						{
							if ($k == 0 && isset($txt[$v]))
								$variables[$k] = $txt[$v];
							elseif ($k > 0 && isset($row[$v]))
								$variables[$k] = $row[$v];
						}
						$context['repair_errors'][] = call_user_func_array('sprintf', $variables);
					}
				}
				// A function to process?
				elseif (isset($test['message_function']))
				{
					// Find out if there are actually errors.
					$found_errors = false;
					while ($row = $smfFunc['db_fetch_assoc']($request))
						$found_errors |= $test['message_function']($row);
				}

				// Actually have something to fix?
				if ($found_errors)
					$to_fix[] = $error_type;
			}
			// We want to fix, we need to fix - so work out what exactly to do!
			else
			{
				// Are we simply getting a collection of ids?
				if (isset($test['fix_collect']))
				{
					$ids = array();
					while ($row = $smfFunc['db_fetch_assoc']($request))
						$ids[] = $row[$test['fix_collect']['index']];
					if (!empty($ids))
					{
						// Fix it!
						$test['fix_collect']['process']($ids);
					}
				}
				// Simply executing a fix it query?
				if (isset($test['fix_it_query']))
					$smfFunc['db_query']('',
						$test['fix_it_query'], __FILE__, __LINE__);
			}
		}

		// Free the result.
		$smfFunc['db_free_result']($request);

		// Are we done yet?
		if (isset($test['substeps']))
		{
			$_GET['substep'] += $step_size;
			// Not done?
			if ($_GET['substep'] < $step_max)
			{
				pauseRepairProcess($to_fix, $_GET['substep']);
				continue;
			}
		}

		// Keep going.
		$_GET['step']++;
		$_GET['substep'] = 0;

		$to_fix = array_unique($to_fix);

		// If we're doing fixes and this needed a fix and we're all done then don't do it again.
		if ($do_fix)
		{
			$key = array_search($error_type, $to_fix);
			if ($key !== false && isset($to_fix[$key]))
				unset($to_fix[$key]);
		}
	
		// Are we done?
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
		if ($smfFunc['db_affected_rows']() <= 0)
		{
			loadLanguage('Admin');
			fatal_lang_error('salvaged_category_error', false);
		}

		$salvageCatID = $smfFunc['db_insert_id']("{$db_prefix}categories", 'id_cat');
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
		if ($smfFunc['db_affected_rows']() <= 0)
		{
			loadLanguage('Admin');
			fatal_lang_error('salvaged_board_error', false);
		}

		$salvageBoardID = $smfFunc['db_insert_id']("{$db_prefix}boards", 'id_board');
	}

	$smfFunc['db_query']('alter_table_boards', "
		ALTER TABLE {$db_prefix}boards
		ORDER BY board_order", __FILE__, __LINE__);

	// Restore the user's language.
	loadLanguage('Admin');
}

?>