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
	global $db_prefix, $sourcedir, $smfFunc;

	$catUpdates = array();

	// Wanna change the categories position?
	if (isset($catOptions['move_after']))
	{
		// Store all categories in the proper order.
		$cats = array();
		$cat_order = array();

		// Setting 'move_after' to '0' moves the category to the top.
		if ($catOptions['move_after'] == 0)
			$cats[] = $category_id;

		// Grab the categories sorted by cat_order.
		$request = $smfFunc['db_query']('', "
			SELECT id_cat, cat_order
			FROM {$db_prefix}categories
			ORDER BY cat_order", __FILE__, __LINE__);
		while ($row = $smfFunc['db_fetch_assoc']($request))
		{
			if ($row['id_cat'] != $category_id)
				$cats[] = $row['id_cat'];
			if ($row['id_cat'] == $catOptions['move_after'])
				$cats[] = $category_id;
			$cat_order[$row['id_cat']] = $row['cat_order'];
		}
		$smfFunc['db_free_result']($request);

		// Set the new order for the categories.
		foreach ($cats as $index => $cat)
			if ($index != $cat_order[$cat])
				$smfFunc['db_query']('', "
					UPDATE {$db_prefix}categories
					SET cat_order = $index
					WHERE id_cat = $cat", __FILE__, __LINE__);

		// If the category order changed, so did the board order.
		require_once($sourcedir . '/Subs-Boards.php');
		reorderBoards();
	}

	if (isset($catOptions['cat_name']))
		$catUpdates[] = 'name = \'' . $catOptions['cat_name'] . '\'';

	// Can a user collapse this category or is it too important?
	if (isset($catOptions['is_collapsible']))
		$catUpdates[] = 'can_collapse = ' . ($catOptions['is_collapsible'] ? '1' : '0');	

	// Do the updates (if any).
	if (!empty($catUpdates))
		$smfFunc['db_query']('', "
			UPDATE {$db_prefix}categories
			SET 
				" . implode(',
				', $catUpdates) . "
			WHERE id_cat = $category_id", __FILE__, __LINE__);
}

// Create a new category.
function createCategory($catOptions)
{
	global $db_prefix, $smfFunc;

	// Check required values.
	if (!isset($catOptions['cat_name']) || trim($catOptions['cat_name']) == '')
		trigger_error('createCategory(): A category name is required', E_USER_ERROR);

	// Set default values.
	if (!isset($catOptions['move_after']))
		$catOptions['move_after'] = 0;
	if (!isset($catOptions['is_collapsible']))
		$catOptions['is_collapsible'] = true;

	// Add the category to the database.
	$smfFunc['db_query']('', "
		INSERT INTO {$db_prefix}categories
			(name)
		VALUES (SUBSTRING('$catOptions[cat_name]', 1, 48))", __FILE__, __LINE__);

	// Grab the new category ID.
	$category_id = db_insert_id("{$db_prefix}categories", 'id_cat');

	// Set the given properties to the newly created category.
	modifyCategory($category_id, $catOptions);

	// Return the database ID of the category.
	return $category_id;
}

// Remove one or more categories.
function deleteCategories($categories, $moveBoardsTo = null)
{
	global $db_prefix, $sourcedir, $smfFunc;

	require_once($sourcedir . '/Subs-Boards.php');

	// With no category set to move the boards to, delete them all.
	if ($moveBoardsTo === null)
	{
		$request = $smfFunc['db_query']('', "
			SELECT id_board
			FROM {$db_prefix}boards
			WHERE id_cat IN (" . implode(', ', $categories) . ')', __FILE__, __LINE__);
		$boards_inside = array();
		while ($row = $smfFunc['db_fetch_assoc']($request))
			$boards_inside[] = $row['id_board'];
		$smfFunc['db_free_result']($request);

		if (!empty($boards_inside))
			deleteBoards($boards_inside, null);
	}

	// Make sure the safe category is really safe.
	elseif (in_array($moveBoardsTo, $categories))
		trigger_error('deleteCategories(): You cannot move the boards to a category that\'s being deleted', E_USER_ERROR);

	// Move the boards inside the categories to a safe category.
	else
		$smfFunc['db_query']('', "
			UPDATE {$db_prefix}boards
			SET id_cat = $moveBoardsTo
			WHERE id_cat IN (" . implode(', ', $categories) . ')', __FILE__, __LINE__);

	// Noone will ever be able to collapse these categories anymore.
	$smfFunc['db_query']('', "
		DELETE FROM {$db_prefix}collapsed_categories
		WHERE id_cat IN (" . implode(', ', $categories) . ")", __FILE__, __LINE__);

	// Do the deletion of the category itself
	$smfFunc['db_query']('', "
		DELETE FROM {$db_prefix}categories
		WHERE id_cat IN (" . implode(', ', $categories) . ")", __FILE__, __LINE__);

	// Get all boards back into the right order.
	reorderBoards();
}

// Collapse, expand or toggle one or more categories for one or more members.
function collapseCategories($categories, $new_status, $members = null, $check_collapsable = true)
{
	global $db_prefix, $smfFunc;

	// Collapse the categories so they won't be shown on the Board Index.
	if ($new_status === 'collapse')
		$smfFunc['db_query']('', "
			INSERT IGNORE INTO {$db_prefix}collapsed_categories
				(id_cat, id_member)
			SELECT c.id_cat, mem.id_member
			FROM ({$db_prefix}members AS mem, {$db_prefix}categories AS c)
			WHERE c.id_cat IN (" . implode(', ', $categories) . ')' . ($members === null ? '' : "
				AND mem.id_member IN (" . implode(', ', $members) . ')') . ($check_collapsable ? "
				AND c.can_collapse = 1" : ''), __FILE__, __LINE__);

	// Get the categories back to how they were.
	elseif ($new_status === 'expand')
		$smfFunc['db_query']('', "
			DELETE FROM {$db_prefix}collapsed_categories
			WHERE id_cat IN (" . implode(', ', $categories) . ')' . ($members === null ? '' : "
				AND id_member IN (" . implode(', ', $members) . ')'), __FILE__, __LINE__);

	// Toggle the categories: collapsed get expanded and expanded get collapsed.
	elseif ($new_status === 'toggle')
	{
		// Get the current state of the categories.
		$updates = array(
			'insert' => array(),
			'remove' => array(),
		);
		$request = $smfFunc['db_query']('', "
			SELECT mem.id_member, c.id_cat, IFNULL(cc.id_cat, 0) AS is_collapsed, c.can_collapse
			FROM {$db_prefix}members AS mem, {$db_prefix}categories AS c
				LEFT JOIN {$db_prefix}collapsed_categories AS cc ON (cc.id_cat = c.id_cat AND cc.id_member = mem.id_member)
			WHERE c.id_cat IN (" . implode(', ', $categories) . ')' . ($members === null ? '' : "
				AND id_member IN (" . implode(', ', $members) . ')'), __FILE__, __LINE__);
		while ($row = $smfFunc['db_fetch_assoc']($request))
		{
			if (empty($row['is_collapsed']) && (!empty($row['can_collapse']) || !$check_collapsable))
				$updates['insert'][] = "$row[id_member], $row[id_cat]";
			elseif (!empty($row['is_collapsed']))
				$updates['remove'][] = "$row[id_member] AND id_cat = $row[id_cat]";
		}
		$smfFunc['db_free_result']($request);

		// Collapse the ones that were originally expanded...
		if (!empty($updates['insert']))
			$smfFunc['db_query']('', "
				INSERT IGNORE INTO {$db_prefix}collapsed_categories
					(id_cat, id_member)
				VALUES
					(" . implode("),
					(", $updates['insert']) . ')', __FILE__, __LINE__);
		
		// And expand the ones that were originally collapsed.
		if (!empty($updates['remove']))
			$smfFunc['db_query']('', "
				DELETE FROM {$db_prefix}collapsed_categories
				WHERE (id_member = " . implode(') OR (id_member = ', $updates['remove']) . ")", __FILE__, __LINE__);
	}
}

?>