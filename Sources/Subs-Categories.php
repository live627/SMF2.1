<?php
/******************************************************************************
* Subs-Categories.php                                                         *
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

/*	This file contains the functions to add, modify, remove, collapse and expand 
	categories.

	void modifyCategory(int category_id, array catOptions)
		- general function to modify the settings and position of a category.
		- used by ManageBoards.php to change the settings of a category.

	int createCategory(array catOptions)
		- general function to create a new category and set its position.
		- allows (almost) the same options as the modifyCat() function.
		- returns the ID of the newly created category.

	void deleteCategories(array categories_to_remove, moveChildrenTo = null)
		- general function to delete one or more categories.
		- allows to move all boards in the categories to a different category
		  before deleting them.
		- if moveChildrenTo is set to null, all boards inside the given 
		  categorieswill be deleted.
		- deletes all information that's associated with the given categories.
		- updates the statistics to reflect the new situation.
	
	void collapseCategories(array categories, string new_status, array members = null, bool check_collapsable = true)
		- collapses or expands one or more categories for one or more members.
		- if members is null, the category is collapsed/expanded for all members.
		- allows three changes to the status: 'expand', 'collapse' and 'toggle'.
		- if check_collapsable is set, only category allowed to be collapsed,
		  will be collapsed.
*/

// Edit the position and properties of a category.
function modifyCategory($category_id, $catOptions)
{
	global $db_prefix, $sourcedir;

	$catUpdates = array();

	// Wanna change the categories position?
	if (isset($catOptions['move_after']))
	{
		// Store all categories in the proper order.
		$cats = array();
		$catOrder = array();

		// Setting 'move_after' to '0' moves the category to the top.
		if ($catOptions['move_after'] == 0)
			$cats[] = $category_id;

		// Grab the categories sorted by catOrder.
		$request = db_query("
			SELECT ID_CAT, catOrder
			FROM {$db_prefix}categories
			ORDER BY catOrder", __FILE__, __LINE__);
		while ($row = mysql_fetch_assoc($request))
		{
			if ($row['ID_CAT'] != $category_id)
				$cats[] = $row['ID_CAT'];
			if ($row['ID_CAT'] == $catOptions['move_after'])
				$cats[] = $category_id;
			$catOrder[$row['ID_CAT']] = $row['catOrder'];
		}
		mysql_free_result($request);

		// Set the new order for the categories.
		foreach ($cats as $index => $cat)
			if ($index != $catOrder[$cat])
				db_query("
					UPDATE {$db_prefix}categories
					SET catOrder = $index
					WHERE ID_CAT = $cat
					LIMIT 1", __FILE__, __LINE__);

		// If the category order changed, so did the board order.
		require_once($sourcedir . '/Subs-Boards.php');
		reorderBoards();
	}

	if (isset($catOptions['cat_name']))
		$catUpdates[] = 'name = \'' . $catOptions['cat_name'] . '\'';

	// Can a user collapse this category or is it too important?
	if (isset($catOptions['is_collapsible']))
		$catUpdates[] = 'canCollapse = ' . ($catOptions['is_collapsible'] ? '1' : '0');	

	// Do the updates (if any).
	if (!empty($catUpdates))
		db_query("
			UPDATE {$db_prefix}categories
			SET 
				" . implode(',
				', $catUpdates) . "
			WHERE ID_CAT = $category_id
			LIMIT 1", __FILE__, __LINE__);
}

// Create a new category.
function createCategory($catOptions)
{
	global $db_prefix;

	// Check required values.
	if (!isset($catOptions['cat_name']) || trim($catOptions['cat_name']) == '')
		trigger_error('createCategory(): A category name is required', E_USER_ERROR);

	// Set default values.
	if (!isset($catOptions['move_after']))
		$catOptions['move_after'] = 0;
	if (!isset($catOptions['is_collapsible']))
		$catOptions['is_collapsible'] = true;

	// Add the category to the database.
	db_query("
		INSERT INTO {$db_prefix}categories
			(name)
		VALUES (SUBSTRING('$catOptions[cat_name]', 1, 48))", __FILE__, __LINE__);

	// Grab the new category ID.
	$category_id = db_insert_id();

	// Set the given properties to the newly created category.
	modifyCategory($category_id, $catOptions);

	// Return the database ID of the category.
	return $category_id;
}

// Remove one or more categories.
function deleteCategories($categories, $moveBoardsTo = null)
{
	global $db_prefix, $sourcedir;

	require_once($sourcedir . '/Subs-Boards.php');

	// With no category set to move the boards to, delete them all.
	if ($moveBoardsTo === null)
	{
		$request = db_query("
			SELECT ID_BOARD
			FROM {$db_prefix}boards
			WHERE ID_CAT IN (" . implode(', ', $categories) . ')', __FILE__, __LINE__);
		$boards_inside = array();
		while ($row = mysql_fetch_assoc($request))
			$boards_inside[] = $row['ID_BOARD'];
		mysql_free_result($request);

		if (!empty($boards_inside))
			deleteBoards($boards_inside, null);
	}

	// Make sure the safe category is really safe.
	elseif (in_array($moveBoardsTo, $categories))
		trigger_error('deleteCategories(): You cannot move the boards to a category that\'s being deleted', E_USER_ERROR);

	// Move the boards inside the categories to a safe category.
	else
		db_query("
			UPDATE {$db_prefix}boards
			SET ID_CAT = $moveBoardsTo
			WHERE ID_CAT IN (" . implode(', ', $categories) . ')', __FILE__, __LINE__);

	// Noone will ever be able to collapse these categories anymore.
	db_query("
		DELETE FROM {$db_prefix}collapsed_categories
		WHERE ID_CAT IN (" . implode(', ', $categories) . ")", __FILE__, __LINE__);

	// Do the deletion of the category itself
	db_query("
		DELETE FROM {$db_prefix}categories
		WHERE ID_CAT IN (" . implode(', ', $categories) . ")
		LIMIT 1", __FILE__, __LINE__);

	// Get all boards back into the right order.
	reorderBoards();
}

// Collapse, expand or toggle one or more categories for one or more members.
function collapseCategories($categories, $new_status, $members = null, $check_collapsable = true)
{
	global $db_prefix;

	// Collapse the categories so they won't be shown on the Board Index.
	if ($new_status === 'collapse')
		db_query("
			INSERT IGNORE INTO {$db_prefix}collapsed_categories
				(ID_CAT, ID_MEMBER)
			SELECT c.ID_CAT, mem.ID_MEMBER
			FROM ({$db_prefix}members AS mem, {$db_prefix}categories AS c)
			WHERE c.ID_CAT IN (" . implode(', ', $categories) . ')' . ($members === null ? '' : "
				AND mem.ID_MEMBER IN (" . implode(', ', $members) . ')') . ($check_collapsable ? "
				AND c.canCollapse = 1" : ''), __FILE__, __LINE__);

	// Get the categories back to how they were.
	elseif ($new_status === 'expand')
		db_query("
			DELETE FROM {$db_prefix}collapsed_categories
			WHERE ID_CAT IN (" . implode(', ', $categories) . ')' . ($members === null ? '' : "
				AND ID_MEMBER IN (" . implode(', ', $members) . ')'), __FILE__, __LINE__);

	// Toggle the categories: collapsed get expanded and expanded get collapsed.
	elseif ($new_status === 'toggle')
	{
		// Get the current state of the categories.
		$updates = array(
			'insert' => array(),
			'remove' => array(),
		);
		$request = db_query("
			SELECT mem.ID_MEMBER, c.ID_CAT, IFNULL(cc.ID_CAT, 0) AS is_collapsed, c.canCollapse
			FROM {$db_prefix}members AS mem, {$db_prefix}categories AS c
				LEFT JOIN {$db_prefix}collapsed_categories AS cc ON (cc.ID_CAT = c.ID_CAT AND cc.ID_MEMBER = mem.ID_MEMBER)
			WHERE c.ID_CAT IN (" . implode(', ', $categories) . ')' . ($members === null ? '' : "
				AND ID_MEMBER IN (" . implode(', ', $members) . ')'), __FILE__, __LINE__);
		while ($row = mysql_fetch_assoc($request))
		{
			if (empty($row['is_collapsed']) && (!empty($row['canCollapse']) || !$check_collapsable))
				$updates['insert'][] = "$row[ID_MEMBER], $row[ID_CAT]";
			elseif (!empty($row['is_collapsed']))
				$updates['remove'][] = "$row[ID_MEMBER] AND ID_CAT = $row[ID_CAT]";
		}
		mysql_free_result($request);

		// Collapse the ones that were originally expanded...
		if (!empty($updates['insert']))
			db_query("
				INSERT IGNORE INTO {$db_prefix}collapsed_categories
					(ID_CAT, ID_MEMBER)
				VALUES
					(" . implode("),
					(", $updates['insert']) . ')', __FILE__, __LINE__);
		
		// And expand the ones that were originally collapsed.
		if (!empty($updates['remove']))
			db_query("
				DELETE FROM {$db_prefix}collapsed_categories
				WHERE (ID_MEMBER = " . implode(') OR (ID_MEMBER = ', $updates['remove']) . ")
				LIMIT " . count($updates['remove']), __FILE__, __LINE__);
	}
}

?>