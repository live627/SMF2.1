<?php
/**********************************************************************************
* Subs-Boards.php                                                                 *
***********************************************************************************
* SMF: Simple Machines Forum                                                      *
* Open-Source Project Inspired by Zef Hemel (zef@zefhemel.com)                    *
* =============================================================================== *
* Software Version:           SMF 2.0 Alpha                                       *
* Software by:                Simple Machines (http://www.simplemachines.org)     *
* Copyright 2006-2007 by:     Simple Machines LLC (http://www.simplemachines.org) *
*           2001-2006 by:     Lewis Media (http://www.lewismedia.com)             *
* Support, News, Updates at:  http://www.simplemachines.org                       *
***********************************************************************************
* This program is free software; you may redistribute it and/or modify it         *
* under the terms of the provided license as published by Lewis Media.            *
*                                                                                 *
* This program is distributed in the hope that it is and will be useful,          *
* but WITHOUT ANY WARRANTIES; without even any implied warranty of                *
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.                            *
*                                                                                 *
* See the "license.txt" file for details of the Simple Machines license.          *
* The latest version can always be found at http://www.simplemachines.org.        *
**********************************************************************************/
if (!defined('SMF'))
	die('Hacking attempt...');

/*	This file is mainly concerned with minor tasks relating to boards, such as
	marking them read, collapsing categories, or quick moderation.  It defines
	the following list of functions:

	void markBoardsRead(array boards)
		// !!!

	void MarkRead()
		// !!!

	int getMsgMemberID(int id_msg)
		// !!!

	void modifyBoard(int board_id, array boardOptions)
		- general function to modify the settings and position of a board.
		- used by ManageBoards.php to change the settings of a board.

	int createBoard(array boardOptions)
		- general function to create a new board and set its position.
		- allows (almost) the same options as the modifyBoard() function.
		- with the option inherit_permissions set, the parent board permissions
		  will be inherited.
		- returns the ID of the newly created board.

	void deleteBoards(array boards_to_remove, moveChildrenTo = null)
		- general function to delete one or more boards.
		- allows to move the children of the board before deleting it
		- if moveChildrenTo is set to null, the child boards will be deleted.
		- deletes all topics that are on the given boards.
		- deletes all information that's associated with the given boards.
		- updates the statistics to reflect the new situation.

	void reorderBoards()
		- updates the database to put all boards in the right order.
		- sorts the records of the boards table.
		- used by modifyBoard(), deleteBoards(), modifyCategory(), and 
		  deleteCategories() functions.

	void fixChildren(int parent, int newLevel, int newParent)
		- recursively updates the children of parent's child_level and
		  id_parent to newLevel and newParent.
		- used when a board is deleted or moved, to affect its children.

	bool isChildOf(int child, int parent)
		- determines if child is a child of parent.
		- recurses down the tree until there are no more parents.
		- returns true if child is a child of parent.

	void getBoardTree()
		- load information regarding the boards and categories.
		- the information retrieved is stored in globals:
			- $boards		properties of each board.
			- $boardList	a list of boards grouped by category ID.
			- $cat_tree		properties of each category.

	void recursiveBoards()
		- function used by getBoardTree to recursively get a list of boards.
	
	bool isChildOf(int child, int parent)
		- determine if a certain board id is a child of another board.
		- the parent might be several levels higher than the child.
*/

// Mark a board or multiple boards read.
function markBoardsRead($boards, $unread = false)
{
	global $db_prefix, $user_info, $modSettings, $smfFunc;

	// Force $boards to be an array.
	if (!is_array($boards))
		$boards = array($boards);
	else
		$boards = array_unique($boards);

	// No boards, nothing to mark as read.
	if (empty($boards))
		return;

	// Allow the user to mark a board as unread.
	if ($unread)
	{
		// Clear out all the places where this lovely info is stored.
		// !! Maybe not log_mark_read?
		$smfFunc['db_query']('', "
			DELETE FROM {$db_prefix}log_mark_read
			WHERE id_board IN (" . implode(', ', $boards) . ")
				AND id_member = $user_info[id]", __FILE__, __LINE__);
		$smfFunc['db_query']('', "
			DELETE FROM {$db_prefix}log_boards
			WHERE id_board IN (" . implode(', ', $boards) . ")
				AND id_member = $user_info[id]", __FILE__, __LINE__);
	}
	// Otherwise mark the board as read.
	else
	{
		$markRead = array();
		foreach ($boards as $board)
			$markRead[] = array($modSettings['maxMsgID'], $user_info['id'], $board);

		// Update log_mark_read and log_boards.
		$smfFunc['db_insert']('replace',
			"{$db_prefix}log_mark_read",
			array('id_msg', 'id_member', 'id_board'),
			$markRead,
			array('id_board', 'id_member'), __FILE__, __LINE__
		);

		$smfFunc['db_insert']('replace',
			"{$db_prefix}log_boards",
			array('id_msg', 'id_member', 'id_board'),
			$markRead,
			array('id_board', 'id_member'), __FILE__, __LINE__
		);
	}

	// Get rid of useless log_topics data, because log_mark_read is better for it - even if marking unread - I think so...
	$result = $smfFunc['db_query']('', "
		SELECT MIN(id_topic)
		FROM {$db_prefix}log_topics
		WHERE id_member = $user_info[id]", __FILE__, __LINE__);
	list ($lowest_topic) = $smfFunc['db_fetch_row']($result);
	$smfFunc['db_free_result']($result);

	if (empty($lowest_topic))
		return;

	// !!!SLOW This query seems to eat it sometimes.
	$result = $smfFunc['db_query']('', "
		SELECT lt.id_topic
		FROM {$db_prefix}log_topics AS lt
			INNER JOIN {$db_prefix}topics AS t /*!40000 USE INDEX (PRIMARY) */ ON (t.id_topic = lt.id_topic
				AND t.id_board IN (" . implode(', ', $boards) . "))
		WHERE lt.id_member = $user_info[id]
			AND lt.id_topic >= $lowest_topic", __FILE__, __LINE__);
	$topics = array();
	while ($row = $smfFunc['db_fetch_assoc']($result))
		$topics[] = $row['id_topic'];
	$smfFunc['db_free_result']($result);

	if (!empty($topics))
		$smfFunc['db_query']('', "
			DELETE FROM {$db_prefix}log_topics
			WHERE id_member = $user_info[id]
				AND id_topic IN (" . implode(', ', $topics) . ")", __FILE__, __LINE__);
}

// Mark one or more boards as read.
function MarkRead()
{
	global $board, $topic, $user_info, $board_info, $db_prefix, $modSettings, $smfFunc;

	// No Guests allowed!
	is_not_guest();

	checkSession('get');

	if (isset($_REQUEST['sa']) && $_REQUEST['sa'] == 'all')
	{
		// Find all the boards this user can see.
		$result = $smfFunc['db_query']('', "
			SELECT b.id_board
			FROM {$db_prefix}boards AS b
			WHERE $user_info[query_see_board]", __FILE__, __LINE__);
		$boards = array();
		while ($row = $smfFunc['db_fetch_assoc']($result))
			$boards[] = $row['id_board'];
		$smfFunc['db_free_result']($result);

		if (!empty($boards))
			markBoardsRead($boards, isset($_REQUEST['unread']));

		$_SESSION['id_msg_last_visit'] = $modSettings['maxMsgID'];
		if (!empty($_SESSION['old_url']) && strpos($_SESSION['old_url'], 'action=unread') !== false)
			redirectexit('action=unread');

		if (isset($_SESSION['topicseen_cache']))
			$_SESSION['topicseen_cache'] = array();

		redirectexit();
	}
	elseif (isset($_REQUEST['sa']) && $_REQUEST['sa'] == 'unreadreplies')
	{
		// Make sure all the boards are integers!
		$topics = explode('-', $_REQUEST['topics']);

		$markRead = array();
		foreach ($topics as $id_topic)
			$markRead[] = array($modSettings['maxMsgID'], $user_info['id'], (int) $id_topic);

		$smfFunc['db_insert']('replace',
			"{$db_prefix}log_topics",
			array('id_msg', 'id_member', 'id_topic'),
			$markRead,
			array('id_member', 'id_topic'), __FILE__, __LINE__
		);

		if (isset($_SESSION['topicseen_cache']))
			$_SESSION['topicseen_cache'] = array();

		redirectexit('action=unreadreplies');
	}
	// Special case: mark a topic unread!
	elseif (isset($_REQUEST['sa']) && $_REQUEST['sa'] == 'topic')
	{
		if (!empty($_GET['t']))
		{
			// Get the latest message before this one.
			$result = $smfFunc['db_query']('', "
				SELECT MAX(id_msg)
				FROM {$db_prefix}messages
				WHERE id_topic = $topic
					AND id_msg < " . (int) $_GET['t'], __FILE__, __LINE__);
			list ($earlyMsg) = $smfFunc['db_fetch_row']($result);
			$smfFunc['db_free_result']($result);
		}

		if (empty($earlyMsg))
		{
			$result = $smfFunc['db_query']('', "
				SELECT id_msg
				FROM {$db_prefix}messages
				WHERE id_topic = $topic
				ORDER BY id_msg
				LIMIT " . (int) $_REQUEST['start'] . ", 1", __FILE__, __LINE__);
			list ($earlyMsg) = $smfFunc['db_fetch_row']($result);
			$smfFunc['db_free_result']($result);
		}

		$earlyMsg--;

		// Use a time one second earlier than the first time: blam, unread!
		$smfFunc['db_insert']('replace',
			"{$db_prefix}log_topics",
			array('id_msg', 'id_member', 'id_topic'),
			array($earlyMsg, $user_info['id'], $topic),
			array('id_member', 'id_topic'), __FILE__, __LINE__
		);

		redirectexit('board=' . $board . '.0');
	}
	else
	{
		$categories = array();
		$boards = array();

		if (isset($_REQUEST['c']))
		{
			$_REQUEST['c'] = explode(',', $_REQUEST['c']);
			foreach ($_REQUEST['c'] as $c)
				$categories[] = (int) $c;
		}
		if (isset($_REQUEST['boards']))
		{
			$_REQUEST['boards'] = explode(',', $_REQUEST['boards']);
			foreach ($_REQUEST['boards'] as $b)
				$boards[] = (int) $b;
		}
		if (!empty($board))
			$boards[] = (int) $board;

		if (isset($_REQUEST['children']) && !empty($boards))
		{
			// They want to mark the entire tree starting with the boards specified
			// The easist thing is to just get all the boards they can see, but since we've specified the top of tree we ignore some of them

			$request = $smfFunc['db_query']('', "
				SELECT b.id_board, b.id_parent
				FROM {$db_prefix}boards AS b
				WHERE $user_info[query_see_board]
					AND child_level > 0
					AND b.id_board NOT IN (" . implode(', ', $boards) . ")
				ORDER BY child_level ASC
				", __FILE__, __LINE__);
			while ($row = $smfFunc['db_fetch_assoc']($request))
				if (in_array($row['id_parent'], $boards))
					$boards[] = $row['id_board'];
			$smfFunc['db_free_result']($request);
			
		}

		$clauses = array();
		if (!empty($categories))
			$clauses[] = "id_cat IN (" . implode(', ', $categories) . ")";
		if (!empty($boards))
			$clauses[] = "id_board IN (" . implode(', ', $boards) . ")";

		if (empty($clauses))
			redirectexit();

		$request = $smfFunc['db_query']('', "
			SELECT b.id_board
			FROM {$db_prefix}boards AS b
			WHERE $user_info[query_see_board]
				AND b." . implode(" OR b.", $clauses), __FILE__, __LINE__);
		$boards = array();
		while ($row = $smfFunc['db_fetch_assoc']($request))
			$boards[] = $row['id_board'];
		$smfFunc['db_free_result']($request);

		if (empty($boards))
			redirectexit();

		markBoardsRead($boards, isset($_REQUEST['unread']));

		foreach ($boards as $b)
		{
			if (isset($_SESSION['topicseen_cache'][$b]))
				$_SESSION['topicseen_cache'][$b] = array();
		}

		if (!isset($_REQUEST['unread']))
		{
			// Find all the boards this user can see.
			$result = $smfFunc['db_query']('', "
				SELECT b.id_board
				FROM {$db_prefix}boards AS b
				WHERE b.id_parent IN (" . implode(', ', $boards) . ")
					AND $user_info[query_see_board]", __FILE__, __LINE__);
			if ($smfFunc['db_num_rows']($result) > 0)
			{
				$logBoardInserts = '';
				while ($row = $smfFunc['db_fetch_assoc']($result))
					$logBoardInserts[] = array($modSettings['maxMsgID'], $user_info['id'], $row['id_board']);

				$smfFunc['db_insert']('replace',
					"{$db_prefix}log_boards",
					array('id_msg', 'id_member', 'id_board'),
					$logBoardInserts,
					array('id_member', 'id_board'), __FILE__, __LINE__
				);
			}
			$smfFunc['db_free_result']($result);

			if (empty($board))
				redirectexit();
			else
				redirectexit('board=' . $board . '.0');
		}
		else
		{
			if (empty($board_info['parent']))
				redirectexit();
			else
				redirectexit('board=' . $board_info['parent'] . '.0');
		}
	}
}

// Get the id_member associated with the specified message.
function getMsgMemberID($messageID)
{
	global $db_prefix, $smfFunc;

	// Find the topic and make sure the member still exists.
	$result = $smfFunc['db_query']('', "
		SELECT IFNULL(mem.id_member, 0)
		FROM {$db_prefix}messages AS m
			LEFT JOIN {$db_prefix}members AS mem ON (mem.id_member = m.id_member)
		WHERE m.id_msg = " . (int) $messageID . "
		LIMIT 1", __FILE__, __LINE__);
	if ($smfFunc['db_num_rows']($result) > 0)
		list ($memberID) = $smfFunc['db_fetch_row']($result);
	// The message doesn't even exist.
	else
		$memberID = 0;
	$smfFunc['db_free_result']($result);

	return $memberID;
}

// Modify the settings and position of a board.
function modifyBoard($board_id, &$boardOptions)
{
	global $sourcedir, $cat_tree, $boards, $boardList, $modSettings, $db_prefix, $smfFunc;

	// Get some basic information about all boards and categories.
	getBoardTree();

	// Make sure given boards and categories exist.
	if (!isset($boards[$board_id]) || (isset($boardOptions['target_board']) && !isset($boards[$boardOptions['target_board']])) || (isset($boardOptions['target_category']) && !isset($cat_tree[$boardOptions['target_category']])))
		fatal_lang_error('no_board');

	// All things that will be updated in the database will be in $boardUpdates.
	$boardUpdates = array();

	// In case the board has to be moved
	if (isset($boardOptions['move_to']))
	{
		// Move the board to the top of a given category.
		if ($boardOptions['move_to'] == 'top')
		{
			$id_cat = $boardOptions['target_category'];
			$child_level = 0;
			$id_parent = 0;
			$after = $cat_tree[$id_cat]['last_board_order'];
		}
		
		// Move the board to the bottom of a given category.
		elseif ($boardOptions['move_to'] == 'bottom')
		{
			$id_cat = $boardOptions['target_category'];
			$child_level = 0;
			$id_parent = 0;
			$after = 0;
			foreach ($cat_tree[$id_cat]['children'] as $id_board => $dummy)
				$after = max($after, $boards[$id_board]['order']);
		}

		// Make the board a child of a given board.
		elseif ($boardOptions['move_to'] == 'child')
		{
			$id_cat = $boards[$boardOptions['target_board']]['category'];
			$child_level = $boards[$boardOptions['target_board']]['level'] + 1;
			$id_parent = $boardOptions['target_board'];

			// !!! Change error message.
			if (isChildOf($id_parent, $board_id))
				fatal_error('Unable to make a parent its own child');

			$after = $boards[$boardOptions['target_board']]['order'];

			// Check if there are already children and (if so) get the max board order.
			if (!empty($boards[$id_parent]['tree']['children']) && empty($boardOptions['move_first_child']))
				foreach ($boards[$id_parent]['tree']['children'] as $childBoard_id => $dummy)
					$after = max($after, $boards[$childBoard_id]['order']);
		}

		// Place a board before or after another board, on the same child level.
		elseif (in_array($boardOptions['move_to'], array('before', 'after')))
		{
			$id_cat = $boards[$boardOptions['target_board']]['category'];
			$child_level = $boards[$boardOptions['target_board']]['level'];
			$id_parent = $boards[$boardOptions['target_board']]['parent'];
			$after = $boards[$boardOptions['target_board']]['order'] - ($boardOptions['move_to'] == 'before' ? 1 : 0);
		}

		// Oops...?
		else
			trigger_error('modifyBoard(): The move_to value \'' . $boardOptions['move_to'] . '\' is incorrect', E_USER_ERROR);

		// Get a list of children of this board.
		$childList = array();
		recursiveBoards($childList, $boards[$board_id]['tree']);

		// See if there are changes that affect children.
		$childUpdates = array();
		$levelDiff = $child_level - $boards[$board_id]['level'];
		if ($levelDiff != 0)
			$childUpdates[] = 'child_level = child_level ' . ($levelDiff > 0 ? '+ ' : '') . $levelDiff;
		if ($id_cat != $boards[$board_id]['category'])
			$childUpdates[] = "id_cat = $id_cat";

		// Fix the children of this board.
		if (!empty($childList) && !empty($childUpdates))
			$smfFunc['db_query']('', "
				UPDATE {$db_prefix}boards
				SET " . implode(',
					', $childUpdates) . "
				WHERE id_board IN (" . implode(', ', $childList) . ')', __FILE__, __LINE__);

		// Make some room for this spot.
		$smfFunc['db_query']('', "
			UPDATE {$db_prefix}boards
			SET board_order = board_order + " . (1 + count($childList)) . "
			WHERE board_order > $after
				AND id_board != $board_id", __FILE__, __LINE__);

		$boardUpdates[] = 'id_cat = ' . $id_cat;
		$boardUpdates[] = 'id_parent = ' . $id_parent;
		$boardUpdates[] = 'child_level = ' . $child_level;
		$boardUpdates[] = 'board_order = ' . ($after + 1);
	}

	// This setting is a little twisted in the database...
	if (isset($boardOptions['posts_count']))
		$boardUpdates[] = 'count_posts = ' . ($boardOptions['posts_count'] ? '0' : '1');

	// Set the theme for this board.
	if (isset($boardOptions['board_theme']))
		$boardUpdates[] = 'id_theme = ' . (int) $boardOptions['board_theme'];

	// Should the board theme override the user preferred theme?
	if (isset($boardOptions['override_theme']))
		$boardUpdates[] = 'override_theme = ' . ($boardOptions['override_theme'] ? '1' : '0');

	// Who's allowed to access this board.
	if (isset($boardOptions['access_groups']))
		$boardUpdates[] = 'member_groups = \'' . implode(',', $boardOptions['access_groups']) . '\'';

	if (isset($boardOptions['board_name']))
		$boardUpdates[] = 'name = \'' . $boardOptions['board_name'] . '\'';

	if (isset($boardOptions['board_description']))
		$boardUpdates[] = 'description = \'' . $boardOptions['board_description'] . '\'';

	if (isset($boardOptions['profile']))
		$boardUpdates[] = 'id_profile = ' . $boardOptions['profile'];

	// Do the updates (if any).
	if (!empty($boardUpdates))
		$request = $smfFunc['db_query']('', "
			UPDATE {$db_prefix}boards
			SET
				" . implode(',
				', $boardUpdates) . "
			WHERE id_board = $board_id", __FILE__, __LINE__);

	// Set moderators of this board.
	if (isset($boardOptions['moderators']) || isset($boardOptions['moderator_string']))
	{
		// Reset current moderators for this board - if there are any!
		$smfFunc['db_query']('', "
			DELETE FROM {$db_prefix}moderators
			WHERE id_board = $board_id", __FILE__, __LINE__);

		// Validate and get the IDs of the new moderators.
		if (isset($boardOptions['moderator_string']) && trim($boardOptions['moderator_string']) != '')
		{
			// Divvy out the usernames, remove extra space.
			$moderator_string = strtr($smfFunc['htmlspecialchars']($smfFunc['db_unescape_string']($boardOptions['moderator_string']), ENT_QUOTES), array('&quot;' => '"'));
			preg_match_all('~"([^"]+)"~', $moderator_string, $matches);
			$moderators = array_merge($matches[1], explode(',', preg_replace('~"([^"]+)"~', '', $moderator_string)));
			for ($k = 0, $n = count($moderators); $k < $n; $k++)
			{
				$moderators[$k] = trim($moderators[$k]);

				if (strlen($moderators[$k]) == 0)
					unset($moderators[$k]);
			}

			// Find all the ID_MEMBERs for the member_name's in the list.
			$boardOptions['moderators'] = array();
			if (!empty($moderators))
			{
				$request = $smfFunc['db_query']('', "
					SELECT id_member
					FROM {$db_prefix}members
					WHERE member_name IN ('" . implode("','", $moderators) . "') OR real_name IN ('" . implode("','", $moderators) . "')
					LIMIT " . count($moderators), __FILE__, __LINE__);
				while ($row = $smfFunc['db_fetch_assoc']($request))
					$boardOptions['moderators'][] = $row['id_member'];
				$smfFunc['db_free_result']($request);
			}
		}

		// Add the moderators to the board.
		if (!empty($boardOptions['moderators']))
		{
			$inserts = array();
			foreach ($boardOptions['moderators'] as $moderator)
				$inserts[] = array($board_id, $moderator);

			$smfFunc['db_insert']('insert',
				"{$db_prefix}moderators",
				array('id_board', 'id_member'),
				$inserts,
				array('id_board', 'id_member'), __FILE__, __LINE__
			);
		}

		// Note that caches can now be wrong!
		updateSettings(array('settings_updated' => time()));
	}

	if (isset($boardOptions['move_to']))
		reorderBoards();
}

// Create a new board and set it's properties and position.
function createBoard($boardOptions)
{
	global $boards, $db_prefix, $modSettings, $smfFunc;

	// Trigger an error if one of the required values is not set.
	if (!isset($boardOptions['board_name']) || trim($boardOptions['board_name']) == '' || !isset($boardOptions['move_to']) || !isset($boardOptions['target_category']))
		trigger_error('createBoard(): One or more of the required options is not set', E_USER_ERROR);

	if (in_array($boardOptions['move_to'], array('child', 'before', 'after')) && !isset($boardOptions['target_board']))
		trigger_error('createBoard(): Target board is not set', E_USER_ERROR);

	// Set every optional value to its default value.
	$boardOptions += array(
		'posts_count' => true,
		'override_theme' => false,
		'board_theme' => 0,
		'access_groups' => array(),
		'board_description' => '',
		'profile' => 1,
		'moderators' => '',
		'inherit_permissions' => true,
	);

	// Insert a board, the settings are dealt with later.
	$smfFunc['db_query']('', "
		INSERT INTO {$db_prefix}boards
			(id_cat, name, description, board_order, member_groups)
		VALUES ($boardOptions[target_category], SUBSTRING('$boardOptions[board_name]', 1, 255), '', 0, '-1,0')", __FILE__, __LINE__);
	$board_id = db_insert_id("{$db_prefix}boards", 'id_board');

	if (empty($board_id))
		return 0;

	// Change the board according to the given specifications.
	modifyBoard($board_id, $boardOptions);

	// Do we want the parent permissions to be inherited?
	if ($boardOptions['inherit_permissions'])
	{
		getBoardTree();

		if (!empty($boards[$board_id]['parent']))
		{
			$request = $smfFunc['db_query']('', "
				SELECT id_profile
				FROM {$db_prefix}boards
				WHERE id_board = " . (int) $boards[$board_id]['parent'] . "
				LIMIT 1", __FILE__, __LINE__);
			list ($boardOptions['profile']) = $smfFunc['db_fetch_row']($request);
			$smfFunc['db_free_result']($request);

			$smfFunc['db_query']('', "
				UPDATE {$db_prefix}boards
				SET id_profile = $boardOptions[profile]
				WHERE id_board = $board_id", __FILE__, __LINE__);
		}
	}

	// Here you are, a new board, ready to be spammed.
	return $board_id;
}

// Remove one or more boards.
function deleteBoards($boards_to_remove, $moveChildrenTo = null)
{
	global $db_prefix, $sourcedir, $boards, $smfFunc;

	// No boards to delete? Return!
	if (empty($boards_to_remove))
		return;

	getBoardTree();

	// If $moveChildrenTo is set to null, include the children in the removal.
	if ($moveChildrenTo === null)
	{
		// Get a list of the child boards that will also be removed.
		$child_boards_to_remove = array();
		foreach ($boards_to_remove as $board_to_remove)
			recursiveBoards($child_boards_to_remove, $boards[$board_to_remove]['tree']);

		// Merge the children with their parents.
		if (!empty($child_boards_to_remove))
			$boards_to_remove = array_unique(array_merge($boards_to_remove, $child_boards_to_remove));
	}
	// Move the children to a safe home.
	else
	{
		foreach ($boards_to_remove as $id_board)
		{
			// !!! Separate category?
			if ($moveChildrenTo === 0)
				fixChildren($id_board, 0, 0);
			else
				fixChildren($id_board, $boards[$moveChildrenTo]['level'] + 1, $moveChildrenTo);
		}
	}

	// Delete ALL topics in the selected boards (done first so topics can't be marooned.)
	$request = $smfFunc['db_query']('', "
		SELECT id_topic
		FROM {$db_prefix}topics
		WHERE id_board IN (" . implode(', ', $boards_to_remove) . ')', __FILE__, __LINE__);
	$topics = array();
	while ($row = $smfFunc['db_fetch_assoc']($request))
		$topics[] = $row['id_topic'];
	$smfFunc['db_free_result']($request);

	require_once($sourcedir . '/RemoveTopic.php');
	removeTopics($topics, false);

	// Delete the board's logs.
	$smfFunc['db_query']('', "
		DELETE FROM {$db_prefix}log_mark_read
		WHERE id_board IN (" . implode(', ', $boards_to_remove) . ')', __FILE__, __LINE__);
	$smfFunc['db_query']('', "
		DELETE FROM {$db_prefix}log_boards
		WHERE id_board IN (" . implode(', ', $boards_to_remove) . ')', __FILE__, __LINE__);
	$smfFunc['db_query']('', "
		DELETE FROM {$db_prefix}log_notify
		WHERE id_board IN (" . implode(', ', $boards_to_remove) . ')', __FILE__, __LINE__);

	// Delete this board's moderators.
	$smfFunc['db_query']('', "
		DELETE FROM {$db_prefix}moderators
		WHERE id_board IN (" . implode(', ', $boards_to_remove) . ')', __FILE__, __LINE__);

	// Delete any extra events in the calendar.
	$smfFunc['db_query']('', "
		DELETE FROM {$db_prefix}calendar
		WHERE id_board IN (" . implode(', ', $boards_to_remove) . ')', __FILE__, __LINE__);

	// Delete any message icons that only appear on these boards.
	$smfFunc['db_query']('', "
		DELETE FROM {$db_prefix}message_icons
		WHERE id_board IN (" . implode(', ', $boards_to_remove) . ')', __FILE__, __LINE__);

	// Delete the boards.
	$smfFunc['db_query']('', "
		DELETE FROM {$db_prefix}boards
		WHERE id_board IN (" . implode(', ', $boards_to_remove) . ")", __FILE__, __LINE__);

	// Sort out any permission profiles.
	$profiles = array();
	foreach ($boards as $id => $board)
	{
		// One we're removing?
		if (in_array($id, $boards_to_remove))
			$profiles[] = $board['profile'];
	}

	// Make sure we only delete profiles not in use, and not predefined.
	if (!empty($profiles))
	{
		$request = $smfFunc['db_query']('', "
			SELECT p.id_profile, p.id_parent, IFNULL(b.id_board, 0) AS remainingBoard
			FROM {$db_prefix}permission_profiles AS p
				LEFT JOIN {$db_prefix}boards AS b ON (b.id_profile = p.id_profile AND b.id_board NOT IN (" . implode(', ', $boards_to_remove) . "))
			GROUP BY id_profile", __FILE__, __LINE__);
		$profiles = array();
		while ($row = $smfFunc['db_fetch_assoc']($request))
		{
			// Nothing left, and can be deleted?
			if (empty($row['remainingBoard']) && $row['id_parent'])
				$profiles['delete'][] = $row['id_profile'];
			// It's old parent is gonna be gone - find a new!
			elseif ($row['id_parent'] && in_array($row['id_parent'], $boards_to_remove))
				$profiles['update'][$row['id_profile']] = $row['remainingBoard'];
		}
		$smfFunc['db_free_result']($request);

		// Delete now defunct ones.
		if (!empty($profiles['delete']))
		{
			$smfFunc['db_query']('', "
				DELETE FROM {$db_prefix}permission_profiles
				WHERE id_profile IN (" . implode(', ', $profiles['delete']) . ")", __FILE__, __LINE__);
			$smfFunc['db_query']('', "
				DELETE FROM {$db_prefix}board_permissions
				WHERE id_profile IN (" . implode(', ', $profiles['delete']) . ")", __FILE__, __LINE__);
		}

		// And the ones that need fixing!
		if (!empty($profiles['update']))
		{
			foreach ($profiles['update'] as $profile => $board)
				$smfFunc['db_query']('', "
					UPDATE {$db_prefix}permission_profiles
					SET id_parent = $board
					WHERE id_profile = $profile", __FILE__, __LINE__);
		}
	}

	// Latest message/topic might not be there anymore.
	updateStats('message');
	updateStats('topic');
	updateSettings(array(
		'calendar_updated' => time(),
	));

	// Plus reset the cache to stop people getting odd results.
	updateSettings(array('settings_updated' => time()));

	reorderBoards();
}

// Put all boards in the right order.
function reorderBoards()
{
	global $db_prefix, $cat_tree, $boardList, $boards, $smfFunc;

	getBoardTree();

	// Set the board order for each category.
	$board_order = 0;
	foreach ($cat_tree as $catID => $dummy)
	{
		foreach ($boardList[$catID] as $boardID)
			if ($boards[$boardID]['order'] != ++$board_order)
				$smfFunc['db_query']('', "
					UPDATE {$db_prefix}boards
					SET board_order = $board_order
					WHERE id_board = $boardID", __FILE__, __LINE__);
	}

	// Sort the records of the boards table on the board_order value.
	$smfFunc['db_query']('alter_table_boards', "
		ALTER TABLE {$db_prefix}boards
		ORDER BY board_order", __FILE__, __LINE__);
}


// Fixes the children of a board by setting their child_levels to new values.
function fixChildren($parent, $newLevel, $newParent)
{
	global $db_prefix, $smfFunc;

	// Grab all children of $parent...
	$result = $smfFunc['db_query']('', "
		SELECT id_board
		FROM {$db_prefix}boards
		WHERE id_parent = $parent", __FILE__, __LINE__);
	$children = array();
	while ($row = $smfFunc['db_fetch_assoc']($result))
		$children[] = $row['id_board'];
	$smfFunc['db_free_result']($result);

	// ...and set it to a new parent and child_level.
	$smfFunc['db_query']('', "
		UPDATE {$db_prefix}boards
		SET id_parent = $newParent, child_level = $newLevel
		WHERE id_parent = $parent", __FILE__, __LINE__);

	// Recursively fix the children of the children.
	foreach ($children as $child)
		fixChildren($child, $newLevel + 1, $child);
}

// Load a lot of usefull information regarding the boards and categories.
function getBoardTree()
{
	global $db_prefix, $cat_tree, $boards, $boardList, $txt, $modSettings, $smfFunc;

	// Getting all the board and category information you'd ever wanted.
	$request = $smfFunc['db_query']('', "
		SELECT
			IFNULL(b.id_board, 0) AS id_board, b.id_parent, b.name AS board_name, b.description, b.child_level,
			b.board_order, b.count_posts, b.member_groups, b.id_theme, b.override_theme, b.id_profile,
			c.id_cat, c.name AS cat_name, c.cat_order, c.can_collapse
		FROM {$db_prefix}categories AS c
			LEFT JOIN {$db_prefix}boards AS b ON (b.id_cat = c.id_cat)
		ORDER BY c.cat_order, b.child_level, b.board_order", __FILE__, __LINE__);
	$cat_tree = array();
	$boards = array();
	$last_board_order = 0;
	while ($row = $smfFunc['db_fetch_assoc']($request))
	{
		if (!isset($cat_tree[$row['id_cat']]))
		{
			$cat_tree[$row['id_cat']] = array(
				'node' => array(
					'id' => $row['id_cat'],
					'name' => $row['cat_name'],
					'order' => $row['cat_order'],
					'can_collapse' => $row['can_collapse']
				),
				'is_first' => empty($cat_tree),
				'last_board_order' => $last_board_order,
				'children' => array()
			);
			$prevBoard = 0;
			$curLevel = 0;
		}

		if (!empty($row['id_board']))
		{
			if ($row['child_level'] != $curLevel)
				$prevBoard = 0;

			$boards[$row['id_board']] = array(
				'id' => $row['id_board'],
				'category' => $row['id_cat'],
				'parent' => $row['id_parent'],
				'level' => $row['child_level'],
				'order' => $row['board_order'],
				'name' => $row['board_name'],
				'member_groups' => explode(',', $row['member_groups']),
				'description' => $row['description'],
				'count_posts' => empty($row['count_posts']),
				'theme' => $row['id_theme'],
				'override_theme' => $row['override_theme'],
				'profile' => $row['id_profile'],
				'prev_board' => $prevBoard
			);
			$prevBoard = $row['id_board'];
			$last_board_order = $row['board_order'];

			if (empty($row['child_level']))
			{
				$cat_tree[$row['id_cat']]['children'][$row['id_board']] = array(
					'node' => &$boards[$row['id_board']],
					'is_first' => empty($cat_tree[$row['id_cat']]['children']),
					'children' => array()
				);
				$boards[$row['id_board']]['tree'] = &$cat_tree[$row['id_cat']]['children'][$row['id_board']];
			}
			else
			{
				// Parent doesn't exist!
				if (!isset($boards[$row['id_parent']]['tree']))
					fatal_lang_error('no_valid_parent', false, array($row['board_name']));

				// Wrong childlevel...we can silently fix this...
				if ($boards[$row['id_parent']]['tree']['node']['level'] != $row['child_level'] - 1)
					$smfFunc['db_query']('', "
						UPDATE {$db_prefix}boards
						SET child_level = " . ($boards[$row['id_parent']]['tree']['node']['level'] + 1) . "
						WHERE id_board = $row[id_board]", __FILE__, __LINE__);

				$boards[$row['id_parent']]['tree']['children'][$row['id_board']] = array(
					'node' => &$boards[$row['id_board']],
					'is_first' => empty($boards[$row['id_parent']]['tree']['children']),
					'children' => array()
				);
				$boards[$row['id_board']]['tree'] = &$boards[$row['id_parent']]['tree']['children'][$row['id_board']];
			}
		}
	}
	$smfFunc['db_free_result']($request);

	// Get a list of all the boards in each category (using recursion).
	$boardList = array();
	foreach ($cat_tree as $catID => $node)
	{
		$boardList[$catID] = array();
		recursiveBoards($boardList[$catID], $node);
	}
}

// Recursively get a list of boards.
function recursiveBoards(&$_boardList, &$_tree)
{
	if (empty($_tree['children']))
		return;

	foreach ($_tree['children'] as $id => $node)
	{
		$_boardList[] = $id;
		recursiveBoards($_boardList, $node);
	}
}

// Returns whether the child board id is actually a child of the parent (recursive).
function isChildOf($child, $parent)
{
	global $boards;

	if (empty($boards[$child]['parent']))
		return false;

	if ($boards[$child]['parent'] == $parent)
		return true;

	return isChildOf($boards[$child]['parent'], $parent);
}

?>