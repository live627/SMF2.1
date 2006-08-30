<?php
/******************************************************************************
* Subs-Boards.php                                                             *
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

/*	This file is mainly concerned with minor tasks relating to boards, such as
	marking them read, collapsing categories, or quick moderation.  It defines
	the following list of functions:

	void markBoardsRead(array boards)
		// !!!

	void MarkRead()
		// !!!

	int getMsgMemberID(int ID_MSG)
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
		- recursively updates the children of parent's childLevel and
		  ID_PARENT to newLevel and newParent.
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
	global $db_prefix, $ID_MEMBER, $modSettings;

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
		db_query("
			DELETE FROM {$db_prefix}log_mark_read
			WHERE ID_BOARD IN (" . implode(', ', $boards) . ")
				AND ID_MEMBER = $ID_MEMBER", __FILE__, __LINE__);
		db_query("
			DELETE FROM {$db_prefix}log_boards
			WHERE ID_BOARD IN (" . implode(', ', $boards) . ")
				AND ID_MEMBER = $ID_MEMBER", __FILE__, __LINE__);
	}
	// Otherwise mark the board as read.
	else
	{
		$setString = '';
		foreach ($boards as $board)
			$setString .= '
				(' . $modSettings['maxMsgID'] . ', ' . $ID_MEMBER . ', ' . $board . '),';
		$setString = substr($setString, 0, -1);

		// Update log_mark_read and log_boards.
		db_query("
			REPLACE INTO {$db_prefix}log_mark_read
				(ID_MSG, ID_MEMBER, ID_BOARD)
			VALUES$setString", __FILE__, __LINE__);
		db_query("
			REPLACE INTO {$db_prefix}log_boards
				(ID_MSG, ID_MEMBER, ID_BOARD)
			VALUES$setString", __FILE__, __LINE__);
	}

	// Get rid of useless log_topics data, because log_mark_read is better for it - even if marking unread - I think so...
	$result = db_query("
		SELECT MIN(ID_TOPIC)
		FROM {$db_prefix}log_topics
		WHERE ID_MEMBER = $ID_MEMBER", __FILE__, __LINE__);
	list ($lowest_topic) = mysql_fetch_row($result);
	mysql_free_result($result);

	if (empty($lowest_topic))
		return;

	// !!!SLOW This query seems to eat it sometimes.
	$result = db_query("
		SELECT lt.ID_TOPIC
		FROM ({$db_prefix}log_topics AS lt, {$db_prefix}topics AS t /*!40000 USE INDEX (PRIMARY) */)
		WHERE t.ID_TOPIC = lt.ID_TOPIC
			AND t.ID_TOPIC >= $lowest_topic
			AND t.ID_BOARD IN (" . implode(', ', $boards) . ")
			AND lt.ID_MEMBER = $ID_MEMBER", __FILE__, __LINE__);
	$topics = array();
	while ($row = mysql_fetch_assoc($result))
		$topics[] = $row['ID_TOPIC'];
	mysql_free_result($result);

	if (!empty($topics))
		db_query("
			DELETE FROM {$db_prefix}log_topics
			WHERE ID_MEMBER = $ID_MEMBER
				AND ID_TOPIC IN (" . implode(', ', $topics) . ")
			LIMIT " . count($topics), __FILE__, __LINE__);
}

// Mark one or more boards as read.
function MarkRead()
{
	global $board, $topic, $user_info, $board_info, $ID_MEMBER, $db_prefix, $modSettings;

	// No Guests allowed!
	is_not_guest();

	checkSession('get');

	if (isset($_REQUEST['sa']) && $_REQUEST['sa'] == 'all')
	{
		// Find all the boards this user can see.
		$result = db_query("
			SELECT b.ID_BOARD
			FROM {$db_prefix}boards AS b
			WHERE $user_info[query_see_board]", __FILE__, __LINE__);
		$boards = array();
		while ($row = mysql_fetch_assoc($result))
			$boards[] = $row['ID_BOARD'];
		mysql_free_result($result);

		if (!empty($boards))
			markBoardsRead($boards, isset($_REQUEST['unread']));

		$_SESSION['ID_MSG_LAST_VISIT'] = $modSettings['maxMsgID'];
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

		$setString = '';
		foreach ($topics as $ID_TOPIC)
			$setString .= "
				($modSettings[maxMsgID], $ID_MEMBER, " . (int) $ID_TOPIC . "),";

		db_query("
			REPLACE INTO {$db_prefix}log_topics
				(ID_MSG, ID_MEMBER, ID_TOPIC)
			VALUES" . substr($setString, 0, -1), __FILE__, __LINE__);

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
			$result = db_query("
				SELECT MAX(ID_MSG)
				FROM {$db_prefix}messages
				WHERE ID_TOPIC = $topic
					AND ID_MSG < " . (int) $_GET['t'], __FILE__, __LINE__);
			list ($earlyMsg) = mysql_fetch_row($result);
			mysql_free_result($result);
		}

		if (empty($earlyMsg))
		{
			$result = db_query("
				SELECT ID_MSG
				FROM {$db_prefix}messages
				WHERE ID_TOPIC = $topic
				ORDER BY ID_MSG
				LIMIT " . (int) $_REQUEST['start'] . ", 1", __FILE__, __LINE__);
			list ($earlyMsg) = mysql_fetch_row($result);
			mysql_free_result($result);
		}

		$earlyMsg--;

		// Use a time one second earlier than the first time: blam, unread!
		db_query("
			REPLACE INTO {$db_prefix}log_topics
				(ID_MSG, ID_MEMBER, ID_TOPIC)
			VALUES ($earlyMsg, $ID_MEMBER, $topic)", __FILE__, __LINE__);

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

			$request = db_query("
				SELECT b.ID_BOARD, b.ID_PARENT
				FROM {$db_prefix}boards AS b
				WHERE $user_info[query_see_board]
					AND childLevel > 0
					AND b.ID_BOARD NOT IN (" . implode(', ', $boards) . ")
				ORDER BY childLevel ASC
				", __FILE__, __LINE__);
			while ($row = mysql_fetch_assoc($request))
				if (in_array($row['ID_PARENT'], $boards))
					$boards[] = $row['ID_BOARD'];
			mysql_free_result($request);
			
		}

		$clauses = array();
		if (!empty($categories))
			$clauses[] = "ID_CAT IN (" . implode(', ', $categories) . ")";
		if (!empty($boards))
			$clauses[] = "ID_BOARD IN (" . implode(', ', $boards) . ")";

		if (empty($clauses))
			redirectexit();

		$request = db_query("
			SELECT b.ID_BOARD
			FROM {$db_prefix}boards AS b
			WHERE $user_info[query_see_board]
				AND b." . implode(" OR b.", $clauses), __FILE__, __LINE__);
		$boards = array();
		while ($row = mysql_fetch_assoc($request))
			$boards[] = $row['ID_BOARD'];
		mysql_free_result($request);

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
			$result = db_query("
				SELECT b.ID_BOARD
				FROM {$db_prefix}boards AS b
				WHERE b.ID_PARENT IN (" . implode(', ', $boards) . ")
					AND $user_info[query_see_board]", __FILE__, __LINE__);
			if (mysql_num_rows($result) > 0)
			{
				$setString = '';
				while ($row = mysql_fetch_assoc($result))
					$setString .= "
						($modSettings[maxMsgID], $ID_MEMBER, $row[ID_BOARD]),";

				db_query("
					REPLACE INTO {$db_prefix}log_boards
						(ID_MSG, ID_MEMBER, ID_BOARD)
					VALUES" . substr($setString, 0, -1), __FILE__, __LINE__);
			}
			mysql_free_result($result);

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

// Get the ID_MEMBER associated with the specified message.
function getMsgMemberID($messageID)
{
	global $db_prefix;

	// Find the topic and make sure the member still exists.
	$result = db_query("
		SELECT IFNULL(mem.ID_MEMBER, 0)
		FROM {$db_prefix}messages AS m
			LEFT JOIN {$db_prefix}members AS mem ON (mem.ID_MEMBER = m.ID_MEMBER)
		WHERE m.ID_MSG = " . (int) $messageID . "
		LIMIT 1", __FILE__, __LINE__);
	if (mysql_num_rows($result) > 0)
		list ($memberID) = mysql_fetch_row($result);
	// The message doesn't even exist.
	else
		$memberID = 0;
	mysql_free_result($result);

	return $memberID;
}

// Modify the settings and position of a board.
function modifyBoard($board_id, &$boardOptions)
{
	global $sourcedir, $cat_tree, $boards, $boardList, $modSettings, $db_prefix, $func;

	// Get some basic information about all boards and categories.
	getBoardTree();

	// Make sure given boards and categories exist.
	if (!isset($boards[$board_id]) || (isset($boardOptions['target_board']) && !isset($boards[$boardOptions['target_board']])) || (isset($boardOptions['target_category']) && !isset($cat_tree[$boardOptions['target_category']])))
		fatal_lang_error('smf232');

	// All things that will be updated in the database will be in $boardUpdates.
	$boardUpdates = array();

	// In case the board has to be moved
	if (isset($boardOptions['move_to']))
	{
		// Move the board to the top of a given category.
		if ($boardOptions['move_to'] == 'top')
		{
			$ID_CAT = $boardOptions['target_category'];
			$childLevel = 0;
			$ID_PARENT = 0;
			$after = $cat_tree[$ID_CAT]['last_board_order'];
		}
		
		// Move the board to the bottom of a given category.
		elseif ($boardOptions['move_to'] == 'bottom')
		{
			$ID_CAT = $boardOptions['target_category'];
			$childLevel = 0;
			$ID_PARENT = 0;
			$after = 0;
			foreach ($cat_tree[$ID_CAT]['children'] as $id_board => $dummy)
				$after = max($after, $boards[$id_board]['order']);
		}

		// Make the board a child of a given board.
		elseif ($boardOptions['move_to'] == 'child')
		{
			$ID_CAT = $boards[$boardOptions['target_board']]['category'];
			$childLevel = $boards[$boardOptions['target_board']]['level'] + 1;
			$ID_PARENT = $boardOptions['target_board'];

			// !!! Change error message.
			if (isChildOf($ID_PARENT, $board_id))
				fatal_error('Unable to make a parent its own child');

			$after = $boards[$boardOptions['target_board']]['order'];

			// Check if there are already children and (if so) get the max board order.
			if (!empty($boards[$ID_PARENT]['tree']['children']) && empty($boardOptions['move_first_child']))
				foreach ($boards[$ID_PARENT]['tree']['children'] as $childBoard_id => $dummy)
					$after = max($after, $boards[$childBoard_id]['order']);
		}

		// Place a board before or after another board, on the same child level.
		elseif (in_array($boardOptions['move_to'], array('before', 'after')))
		{
			$ID_CAT = $boards[$boardOptions['target_board']]['category'];
			$childLevel = $boards[$boardOptions['target_board']]['level'];
			$ID_PARENT = $boards[$boardOptions['target_board']]['parent'];
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
		$levelDiff = $childLevel - $boards[$board_id]['level'];
		if ($levelDiff != 0)
			$childUpdates[] = 'childLevel = childLevel ' . ($levelDiff > 0 ? '+ ' : '') . $levelDiff;
		if ($ID_CAT != $boards[$board_id]['category'])
			$childUpdates[] = "ID_CAT = $ID_CAT";

		// Fix the children of this board.
		if (!empty($childList) && !empty($childUpdates))
			db_query("
				UPDATE {$db_prefix}boards
				SET " . implode(',
					', $childUpdates) . "
				WHERE ID_BOARD IN (" . implode(', ', $childList) . ')', __FILE__, __LINE__);

		// Make some room for this spot.
		db_query("
			UPDATE {$db_prefix}boards
			SET boardOrder = boardOrder + " . (1 + count($childList)) . "
			WHERE boardOrder > $after
				AND ID_BOARD != $board_id", __FILE__, __LINE__);

		$boardUpdates[] = 'ID_CAT = ' . $ID_CAT;
		$boardUpdates[] = 'ID_PARENT = ' . $ID_PARENT;
		$boardUpdates[] = 'childLevel = ' . $childLevel;
		$boardUpdates[] = 'boardOrder = ' . ($after + 1);
	}

	// This setting is a little twisted in the database...
	if (isset($boardOptions['posts_count']))
		$boardUpdates[] = 'countPosts = ' . ($boardOptions['posts_count'] ? '0' : '1');

	// Set the theme for this board.
	if (isset($boardOptions['board_theme']))
		$boardUpdates[] = 'ID_THEME = ' . (int) $boardOptions['board_theme'];

	// Should the board theme override the user preferred theme?
	if (isset($boardOptions['override_theme']))
		$boardUpdates[] = 'override_theme = ' . ($boardOptions['override_theme'] ? '1' : '0');

	// Who's allowed to access this board.
	if (isset($boardOptions['access_groups']))
		$boardUpdates[] = 'memberGroups = \'' . implode(',', $boardOptions['access_groups']) . '\'';

	if (isset($boardOptions['board_name']))
		$boardUpdates[] = 'name = \'' . $boardOptions['board_name'] . '\'';

	if (isset($boardOptions['board_description']))
		$boardUpdates[] = 'description = \'' . $boardOptions['board_description'] . '\'';

	if (isset($boardOptions['profile']))
		$boardUpdates[] = 'ID_PROFILE = ' . $boardOptions['profile'];

	// Do the updates (if any).
	if (!empty($boardUpdates))
		$request = db_query("
			UPDATE {$db_prefix}boards
			SET
				" . implode(',
				', $boardUpdates) . "
			WHERE ID_BOARD = $board_id
			LIMIT 1", __FILE__, __LINE__);

	// Set moderators of this board.
	if (isset($boardOptions['moderators']) || isset($boardOptions['moderator_string']))
	{
		// Reset current moderators for this board - if there are any!
		db_query("
			DELETE FROM {$db_prefix}moderators
			WHERE ID_BOARD = $board_id", __FILE__, __LINE__);

		// Validate and get the IDs of the new moderators.
		if (isset($boardOptions['moderator_string']) && trim($boardOptions['moderator_string']) != '')
		{
			// Divvy out the usernames, remove extra space.
			$moderator_string = strtr($func['htmlspecialchars'](stripslashes($boardOptions['moderator_string']), ENT_QUOTES), array('&quot;' => '"'));
			preg_match_all('~"([^"]+)"~', $moderator_string, $matches);
			$moderators = array_merge($matches[1], explode(',', preg_replace('~"([^"]+)"~', '', $moderator_string)));
			for ($k = 0, $n = count($moderators); $k < $n; $k++)
			{
				$moderators[$k] = trim($moderators[$k]);

				if (strlen($moderators[$k]) == 0)
					unset($moderators[$k]);
			}

			// Find all the ID_MEMBERs for the memberName's in the list.
			$boardOptions['moderators'] = array();
			if (!empty($moderators))
			{
				$request = db_query("
					SELECT ID_MEMBER
					FROM {$db_prefix}members
					WHERE memberName IN ('" . implode("','", $moderators) . "') OR realName IN ('" . implode("','", $moderators) . "')
					LIMIT " . count($moderators), __FILE__, __LINE__);
				while ($row = mysql_fetch_assoc($request))
					$boardOptions['moderators'][] = $row['ID_MEMBER'];
				mysql_free_result($request);
			}
		}

		// Add the moderators to the board.
		if (!empty($boardOptions['moderators']))
		{
			$setString = '';
			foreach ($boardOptions['moderators'] as $moderator)
				$setString .= "
						($board_id, $moderator),";

			db_query("
				INSERT INTO {$db_prefix}moderators
					(ID_BOARD, ID_MEMBER)
				VALUES" . substr($setString, 0, -1), __FILE__, __LINE__);
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
	global $boards, $db_prefix, $modSettings;

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
	db_query("
		INSERT INTO {$db_prefix}boards
			(ID_CAT, name, description, boardOrder, memberGroups)
		VALUES ($boardOptions[target_category], SUBSTRING('$boardOptions[board_name]', 1, 255), '', 0, '-1,0')", __FILE__, __LINE__);
	$board_id = db_insert_id();

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
			$request = db_query("
				SELECT ID_PROFILE
				FROM {$db_prefix}boards
				WHERE ID_BOARD = " . (int) $boards[$board_id]['parent'] . "
				LIMIT 1", __FILE__, __LINE__);
			list ($boardOptions['profile']) = mysql_fetch_row($request);
			mysql_free_result($request);

			db_query("
				UPDATE {$db_prefix}boards
				SET ID_PROFILE = $boardOptions[profile]
				WHERE ID_BOARD = $board_id", __FILE__, __LINE__);
		}
	}

	// Here you are, a new board, ready to be spammed.
	return $board_id;
}

// Remove one or more boards.
function deleteBoards($boards_to_remove, $moveChildrenTo = null)
{
	global $db_prefix, $sourcedir, $boards;

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
	$request = db_query("
		SELECT ID_TOPIC
		FROM {$db_prefix}topics
		WHERE ID_BOARD IN (" . implode(', ', $boards_to_remove) . ')', __FILE__, __LINE__);
	$topics = array();
	while ($row = mysql_fetch_assoc($request))
		$topics[] = $row['ID_TOPIC'];
	mysql_free_result($request);

	require_once($sourcedir . '/RemoveTopic.php');
	removeTopics($topics, false);

	// Delete the board's logs.
	db_query("
		DELETE FROM {$db_prefix}log_mark_read
		WHERE ID_BOARD IN (" . implode(', ', $boards_to_remove) . ')', __FILE__, __LINE__);
	db_query("
		DELETE FROM {$db_prefix}log_boards
		WHERE ID_BOARD IN (" . implode(', ', $boards_to_remove) . ')', __FILE__, __LINE__);
	db_query("
		DELETE FROM {$db_prefix}log_notify
		WHERE ID_BOARD IN (" . implode(', ', $boards_to_remove) . ')', __FILE__, __LINE__);

	// Delete this board's moderators.
	db_query("
		DELETE FROM {$db_prefix}moderators
		WHERE ID_BOARD IN (" . implode(', ', $boards_to_remove) . ')', __FILE__, __LINE__);

	// Delete any extra events in the calendar.
	db_query("
		DELETE FROM {$db_prefix}calendar
		WHERE ID_BOARD IN (" . implode(', ', $boards_to_remove) . ')', __FILE__, __LINE__);

	// Delete any message icons that only appear on these boards.
	db_query("
		DELETE FROM {$db_prefix}message_icons
		WHERE ID_BOARD IN (" . implode(', ', $boards_to_remove) . ')', __FILE__, __LINE__);

	// Delete the boards.
	db_query("
		DELETE FROM {$db_prefix}boards
		WHERE ID_BOARD IN (" . implode(', ', $boards_to_remove) . ")
		LIMIT " . count($boards_to_remove), __FILE__, __LINE__);

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
		$request = db_query("
			SELECT p.ID_PROFILE, p.ID_PARENT, IFNULL(b.ID_BOARD, 0) AS remainingBoard
			FROM {$db_prefix}permission_profiles AS p
				LEFT JOIN {$db_prefix}boards AS b ON (b.ID_PROFILE = p.ID_PROFILE AND b.ID_BOARD NOT IN (" . implode(', ', $boards_to_remove) . "))
			GROUP BY ID_PROFILE", __FILE__, __LINE__);
		$profiles = array();
		while ($row = mysql_fetch_assoc($request))
		{
			// Nothing left, and can be deleted?
			if (empty($row['remainingBoard']) && $row['ID_PARENT'])
				$profiles['delete'][] = $row['ID_PROFILE'];
			// It's old parent is gonna be gone - find a new!
			elseif ($row['ID_PARENT'] && in_array($row['ID_PARENT'], $boards_to_remove))
				$profiles['update'][$row['ID_PROFILE']] = $row['remainingBoard'];
		}
		mysql_free_result($request);

		// Delete now defunct ones.
		if (!empty($profiles['delete']))
		{
			db_query("
				DELETE FROM {$db_prefix}permission_profiles
				WHERE ID_PROFILE IN (" . implode(', ', $profiles['delete']) . ")", __FILE__, __LINE__);
			db_query("
				DELETE FROM {$db_prefix}board_permissions
				WHERE ID_PROFILE IN (" . implode(', ', $profiles['delete']) . ")", __FILE__, __LINE__);
		}

		// And the ones that need fixing!
		if (!empty($profiles['update']))
		{
			foreach ($profiles['update'] as $profile => $board)
				db_query("
					UPDATE {$db_prefix}permission_profiles
					SET ID_PARENT = $board
					WHERE ID_PROFILE = $profile", __FILE__, __LINE__);
		}
	}

	// Latest message/topic might not be there anymore.
	updateStats('message');
	updateStats('topic');
	updateStats('calendar');

	// Plus reset the cache to stop people getting odd results.
	updateSettings(array('settings_updated' => time()));

	reorderBoards();
}

// Put all boards in the right order.
function reorderBoards()
{
	global $db_prefix, $cat_tree, $boardList, $boards;

	getBoardTree();

	// Set the board order for each category.
	$boardOrder = 0;
	foreach ($cat_tree as $catID => $dummy)
	{
		foreach ($boardList[$catID] as $boardID)
			if ($boards[$boardID]['order'] != ++$boardOrder)
				db_query("
					UPDATE {$db_prefix}boards
					SET boardOrder = $boardOrder
					WHERE ID_BOARD = $boardID
					LIMIT 1", __FILE__, __LINE__);
	}

	// Sort the records of the boards table on the boardOrder value.
	db_query("
		ALTER TABLE {$db_prefix}boards
		ORDER BY boardOrder", __FILE__, __LINE__);
}


// Fixes the children of a board by setting their childLevels to new values.
function fixChildren($parent, $newLevel, $newParent)
{
	global $db_prefix;

	// Grab all children of $parent...
	$result = db_query("
		SELECT ID_BOARD
		FROM {$db_prefix}boards
		WHERE ID_PARENT = $parent", __FILE__, __LINE__);
	$children = array();
	while ($row = mysql_fetch_assoc($result))
		$children[] = $row['ID_BOARD'];
	mysql_free_result($result);

	// ...and set it to a new parent and childLevel.
	db_query("
		UPDATE {$db_prefix}boards
		SET ID_PARENT = $newParent, childLevel = $newLevel
		WHERE ID_PARENT = $parent
		LIMIT " . count($children), __FILE__, __LINE__);

	// Recursively fix the children of the children.
	foreach ($children as $child)
		fixChildren($child, $newLevel + 1, $child);
}

// Load a lot of usefull information regarding the boards and categories.
function getBoardTree()
{
	global $db_prefix, $cat_tree, $boards, $boardList, $txt, $modSettings;

	// Getting all the board and category information you'd ever wanted.
	$request = db_query("
		SELECT
			IFNULL(b.ID_BOARD, 0) AS ID_BOARD, b.ID_PARENT, b.name AS bName, b.description, b.childLevel,
			b.boardOrder, b.countPosts, b.memberGroups, b.ID_THEME, b.override_theme, b.ID_PROFILE,
			c.ID_CAT, c.name AS cName, c.catOrder, c.canCollapse
		FROM {$db_prefix}categories AS c
			LEFT JOIN {$db_prefix}boards AS b ON (b.ID_CAT = c.ID_CAT)
		ORDER BY c.catOrder, b.childLevel, b.boardOrder", __FILE__, __LINE__);
	$cat_tree = array();
	$boards = array();
	$last_board_order = 0;
	while ($row = mysql_fetch_assoc($request))
	{
		if (!isset($cat_tree[$row['ID_CAT']]))
		{
			$cat_tree[$row['ID_CAT']] = array(
				'node' => array(
					'id' => $row['ID_CAT'],
					'name' => $row['cName'],
					'order' => $row['catOrder'],
					'canCollapse' => $row['canCollapse']
				),
				'is_first' => empty($cat_tree),
				'last_board_order' => $last_board_order,
				'children' => array()
			);
			$prevBoard = 0;
			$curLevel = 0;
		}

		if (!empty($row['ID_BOARD']))
		{
			if ($row['childLevel'] != $curLevel)
				$prevBoard = 0;

			$boards[$row['ID_BOARD']] = array(
				'id' => $row['ID_BOARD'],
				'category' => $row['ID_CAT'],
				'parent' => $row['ID_PARENT'],
				'level' => $row['childLevel'],
				'order' => $row['boardOrder'],
				'name' => $row['bName'],
				'memberGroups' => explode(',', $row['memberGroups']),
				'description' => $row['description'],
				'count_posts' => empty($row['countPosts']),
				'theme' => $row['ID_THEME'],
				'override_theme' => $row['override_theme'],
				'profile' => $row['ID_PROFILE'],
				'prev_board' => $prevBoard
			);
			$prevBoard = $row['ID_BOARD'];
			$last_board_order = $row['boardOrder'];

			if (empty($row['childLevel']))
			{
				$cat_tree[$row['ID_CAT']]['children'][$row['ID_BOARD']] = array(
					'node' => &$boards[$row['ID_BOARD']],
					'is_first' => empty($cat_tree[$row['ID_CAT']]['children']),
					'children' => array()
				);
				$boards[$row['ID_BOARD']]['tree'] = &$cat_tree[$row['ID_CAT']]['children'][$row['ID_BOARD']];
			}
			else
			{
				// Parent doesn't exist!
				if (!isset($boards[$row['ID_PARENT']]['tree']))
					fatal_lang_error('no_valid_parent', false, array($row['bName']));

				// Wrong childlevel...we can silently fix this...
				if ($boards[$row['ID_PARENT']]['tree']['node']['level'] != $row['childLevel'] - 1)
					db_query("
						UPDATE {$db_prefix}boards
						SET childLevel = " . ($boards[$row['ID_PARENT']]['tree']['node']['level'] + 1) . "
						WHERE ID_BOARD = $row[ID_BOARD]", __FILE__, __LINE__);

				$boards[$row['ID_PARENT']]['tree']['children'][$row['ID_BOARD']] = array(
					'node' => &$boards[$row['ID_BOARD']],
					'is_first' => empty($boards[$row['ID_PARENT']]['tree']['children']),
					'children' => array()
				);
				$boards[$row['ID_BOARD']]['tree'] = &$boards[$row['ID_PARENT']]['tree']['children'][$row['ID_BOARD']];
			}
		}
	}
	mysql_free_result($request);

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