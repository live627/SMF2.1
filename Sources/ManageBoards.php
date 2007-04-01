<?php
/**********************************************************************************
* ManageBoards.php                                                                *
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

/* Manage and maintain the boards and categories of the forum.

	void ManageBoards()
		- main entry point for all the manageboards admin screens.
		- called by ?action=admin;area=manageboards.
		- checks the permissions, based on the sub-action.
		- loads the ManageBoards language file.
		- calls a function based on the sub-action.

	void ManageBoardsMain()
		- main screen showing all boards and categories.
		- called by ?action=admin;area=manageboards or ?action=admin;area=manageboards;sa=move.
		- uses the main template of the ManageBoards template.
		- requires manage_boards permission.
		- also handles the interface for moving boards.

	void EditCategory()
		- screen for editing and repositioning a category.
		- called by ?action=admin;area=manageboards;sa=cat
		- uses the modify_category sub-template of the ManageBoards template.
		- requires manage_boards permission.
		- also used to show the confirm deletion of category screen 
		  (sub-template confirm_category_delete).
		
	void EditCategory2()
		- function for handling a submitted form saving the category.
		- called by ?action=admin;area=manageboards;sa=cat2
		- requires manage_boards permission.
		- also handles deletion of a category.
		- redirects to ?action=admin;area=manageboards.

	void EditBoard()
		- screen for editing and repositioning a board.
		- called by ?action=admin;area=manageboards;sa=board
		- uses the modify_board sub-template of the ManageBoards template.
		- requires manage_boards permission.
		- also used to show the confirm deletion of category screen 
		  (sub-template confirm_board_delete).

	void EditBoard2()
		- function for handling a submitted form saving the board.
		- called by ?action=admin;area=manageboards;sa=board2
		- requires manage_boards permission.
		- also handles deletion of a board.
		- redirects to ?action=admin;area=manageboards.

	void EditBoardSettings()
		- a screen to set a few general board and category settings.
		- uses the modify_general_settings sub template.
*/

// The controller; doesn't do anything, just delegates.
function ManageBoards()
{
	global $context, $txt, $scripturl;

	// Everything's gonna need this.
	loadLanguage('ManageBoards');

	// Format: 'sub-action' => array('function', 'permission')
	$subActions = array(
		'board' => array('EditBoard', 'manage_boards'),
		'board2' => array('EditBoard2', 'manage_boards'),
		'cat' => array('EditCategory', 'manage_boards'),
		'cat2' => array('EditCategory2', 'manage_boards'),
		'main' => array('ManageBoardsMain', 'manage_boards'),
		'move' => array('ManageBoardsMain', 'manage_boards'),
		'newcat' => array('EditCategory', 'manage_boards'),
		'newboard' => array('EditBoard', 'manage_boards'),
		'settings' => array('EditBoardSettings', 'admin_forum'),
	);

	// Default to sub action 'main' or 'settings' depending on permissions.
	$_REQUEST['sa'] = isset($_REQUEST['sa']) && isset($subActions[$_REQUEST['sa']]) ? $_REQUEST['sa'] : (allowedTo('manage_boards') ? 'main' : 'settings');

	// Have you got the proper permissions?
	isAllowedTo($subActions[$_REQUEST['sa']][1]);

	// Create the tabs for the template.
	$context['admin_tabs'] = array(
		'title' => $txt['boards_and_cats'],
		'help' => 'manage_boards',
		'description' => $txt['boards_and_cats_desc'],
		'tabs' => array(),
	);
	if (allowedTo('manage_boards'))
	{
		$context['admin_tabs']['tabs']['modify_boards'] = array(
			'title' => $txt['boardsEdit'],
			'description' => $txt['boards_and_cats_desc'],
			'href' => $scripturl . '?action=admin;area=manageboards',
			'is_selected' => $_REQUEST['sa'] != 'newcat' && $_REQUEST['sa'] != 'settings',
		);
		$context['admin_tabs']['tabs']['add_cat'] = array(
			'title' => $txt['mboards_new_cat'],
			'description' => $txt['boards_and_cats_desc'],
			'href' => $scripturl . '?action=admin;area=manageboards;sa=newcat',
			'is_selected' => $_REQUEST['sa'] == 'newcat',
			'is_last' => !allowedTo('admin_forum'),
		);
	}
	if (allowedTo('admin_forum'))
		$context['admin_tabs']['tabs']['settings'] = array(
			'title' => $txt['settings'],
			'description' => $txt['mboards_settings_desc'],
			'href' => $scripturl . '?action=admin;area=manageboards;sa=settings',
			'is_selected' => $_REQUEST['sa'] == 'settings',
			'is_last' => true,
		);

	$subActions[$_REQUEST['sa']][0]();
}

// The main control panel thing.
function ManageBoardsMain()
{
	global $txt, $context, $cat_tree, $boards, $boardList, $scripturl, $sourcedir, $txt;

	loadTemplate('ManageBoards');

	require_once($sourcedir . '/Subs-Boards.php');

	if (isset($_REQUEST['sa']) && $_REQUEST['sa'] == 'move' && in_array($_REQUEST['move_to'], array('child', 'before', 'after', 'top')))
	{
		checkSession('get');
		if ($_REQUEST['move_to'] === 'top')
			$boardOptions = array(
				'move_to' => $_REQUEST['move_to'],
				'target_category' => (int) $_REQUEST['target_cat'],
				'move_first_child' => true,
			);
		else
			$boardOptions = array(
				'move_to' => $_REQUEST['move_to'],
				'target_board' => (int) $_REQUEST['target_board'],
				'move_first_child' => true,
			);
		modifyBoard((int) $_REQUEST['src_board'], $boardOptions);
	}

	getBoardTree();

	$context['move_board'] = !empty($_REQUEST['move']) && isset($boards[(int) $_REQUEST['move']]) ? (int) $_REQUEST['move'] : 0;

	$context['categories'] = array();
	foreach ($cat_tree as $catid => $tree)
	{
		$context['categories'][$catid] = array(
			'name' => &$tree['node']['name'],
			'id' => &$tree['node']['id'],
			'boards' => array()
		);
		$move_cat = !empty($context['move_board']) && $boards[$context['move_board']]['category'] == $catid;
		foreach ($boardList[$catid] as $boardid)
		{
			$context['categories'][$catid]['boards'][$boardid] = array(
				'id' => &$boards[$boardid]['id'],
				'name' => &$boards[$boardid]['name'],
				'description' => &$boards[$boardid]['description'],
				'child_level' => &$boards[$boardid]['level'],
				'move' => $move_cat && ($boardid == $context['move_board'] || isChildOf($boardid, $context['move_board']))
			);
		}
	}

	if (!empty($context['move_board']))
	{
		$context['move_title'] = sprintf($txt['mboards_select_destination'], htmlspecialchars($boards[$context['move_board']]['name']));
		foreach ($cat_tree as $catid => $tree)
		{
			$prev_child_level = 0;
			$prev_board = 0;
			$stack = array();
			foreach ($boardList[$catid] as $boardid)
			{
				if (!isset($context['categories'][$catid]['move_link']))
					$context['categories'][$catid]['move_link'] = array(
						'child_level' => 0,
						'label' => $txt['mboards_order_before'] . ' \'' . htmlspecialchars($boards[$boardid]['name']) . '\'',
						'href' => $scripturl . '?action=admin;area=manageboards;sa=move;src_board=' . $context['move_board'] . ';target_board='. $boardid . ';move_to=before;sesc=' . $context['session_id'],
					);
				
				if (!$context['categories'][$catid]['boards'][$boardid]['move'])
				$context['categories'][$catid]['boards'][$boardid]['move_links'] = array(
					array(
						'child_level' => $boards[$boardid]['level'],
						'label' => $txt['mboards_order_after'] . '\'' . htmlspecialchars($boards[$boardid]['name']) . '\'',
						'href' => $scripturl . '?action=admin;area=manageboards;sa=move;src_board=' . $context['move_board'] . ';target_board='. $boardid . ';move_to=after;sesc=' . $context['session_id'],
					),
					array(
						'child_level' => $boards[$boardid]['level'] + 1,
						'label' => $txt['mboards_order_child_of'] . ' \'' . htmlspecialchars($boards[$boardid]['name']) . '\'',
						'href' => $scripturl . '?action=admin;area=manageboards;sa=move;src_board=' . $context['move_board'] . ';target_board='. $boardid . ';move_to=child;sesc=' . $context['session_id'],
					),
				);

				$difference = $boards[$boardid]['level'] - $prev_child_level;
				if ($difference == 1 && !empty($context['categories'][$catid]['boards'][$prev_board]['move_links']))
					array_push($stack, array_shift($context['categories'][$catid]['boards'][$prev_board]['move_links']));
				elseif ($difference < 0)
				{
					if (empty($context['categories'][$catid]['boards'][$prev_board]['move_links']))
						$context['categories'][$catid]['boards'][$prev_board]['move_links'] = array();
					for ($i = 0; $i < -$difference; $i++)
						array_unshift($context['categories'][$catid]['boards'][$prev_board]['move_links'], array_pop($stack));
				}

				$prev_board = $boardid;
				$prev_child_level = $boards[$boardid]['level'];

			}
			if (!empty($stack) && !empty($context['categories'][$catid]['boards'][$prev_board]['move_links']))
				$context['categories'][$catid]['boards'][$prev_board]['move_links'] = array_merge($stack, $context['categories'][$catid]['boards'][$prev_board]['move_links']);
			elseif (!empty($stack))
				$context['categories'][$catid]['boards'][$prev_board]['move_links'] = $stack;

			if (empty($boardList[$catid]))
				$context['categories'][$catid]['move_link'] = array(
					'child_level' => 0,
					'label' => $txt['mboards_order_before'] . ' \'' . htmlspecialchars($tree['node']['name']) . '\'',
					'href' => $scripturl . '?action=admin;area=manageboards;sa=move;src_board=' . $context['move_board'] . ';target_cat=' . $catid . ';move_to=top;sesc=' . $context['session_id'],
				);
		}
	}

	$context['page_title'] = $txt['boards_and_cats'];
	$context['can_manage_permissions'] = allowedTo('manage_permissions');
}

// Modify a specific category.
function EditCategory()
{
	global $txt, $db_prefix, $context, $cat_tree, $boardList, $boards, $sourcedir;

	loadTemplate('ManageBoards');
	require_once($sourcedir . '/Subs-Boards.php');
	getBoardTree();

	// id_cat must be a number.... if it exists.
	$_REQUEST['cat'] = isset($_REQUEST['cat']) ? (int) $_REQUEST['cat'] : 0;

	// Start with one - "In first place".
	$context['category_order'] = array(
		array(
			'id' => 0,
			'name' => $txt['mboards_order_first'],
			'selected' => !empty($_REQUEST['cat']) ? $cat_tree[$_REQUEST['cat']]['is_first'] : false,
			'true_name' => ''
		)
	);

	// If this is a new category set up some defaults.
	if ($_REQUEST['sa'] == 'newcat')
	{
		$context['category'] = array(
			'id' => 0,
			'name' => $txt['mboards_new_cat_name'],
			'editable_name' => htmlspecialchars($txt['mboards_new_cat_name']),
			'can_collapse' => true,
			'is_new' => true,
			'is_empty' => true
		);
	}
	// Category doesn't exist, man... sorry.
	elseif (!isset($cat_tree[$_REQUEST['cat']]))
		redirectexit('action=admin;area=manageboards');
	else
	{
		$context['category'] = array(
			'id' => $_REQUEST['cat'],
			'name' => $cat_tree[$_REQUEST['cat']]['node']['name'],
			'editable_name' => htmlspecialchars($cat_tree[$_REQUEST['cat']]['node']['name']),
			'can_collapse' => !empty($cat_tree[$_REQUEST['cat']]['node']['can_collapse']),
			'children' => array(),
			'is_empty' => empty($cat_tree[$_REQUEST['cat']]['children'])
		);

		foreach ($boardList[$_REQUEST['cat']] as $child_board)
			$context['category']['children'][] = str_repeat('-', $boards[$child_board]['level']) . ' ' . $boards[$child_board]['name'];
	}


	$prevCat = 0;
	foreach ($cat_tree as $catid => $tree)
	{
		if ($catid == $_REQUEST['cat'] && $prevCat > 0)
			$context['category_order'][$prevCat]['selected'] = true;
		else
			$context['category_order'][$catid] = array(
				'id' => $catid,
				'name' => $txt['mboards_order_after'] . $tree['node']['name'],
				'selected' => false,
				'true_name' => $tree['node']['name']
			);
		$prevCat = $catid;
	}
	if (!isset($_REQUEST['delete']))
	{
		$context['sub_template'] = 'modify_category';
		$context['page_title'] = $_REQUEST['sa'] == 'newcat' ? $txt['mboards_new_cat_name'] : $txt['catEdit'];
	}
	else
	{
		$context['sub_template'] = 'confirm_category_delete';
		$context['page_title'] = $txt['mboards_delete_cat'];
	}
}

// Complete the modifications to a specific category.
function EditCategory2()
{
	global $db_prefix, $sourcedir;

	checkSession();

	require_once($sourcedir . '/Subs-Categories.php');

	$_POST['cat'] = (int) $_POST['cat'];

	// Add a new category or modify an existing one..
	if (isset($_POST['edit']) || isset($_POST['add']))
	{
		$catOptions = array();

		if (isset($_POST['cat_order']))
			$catOptions['move_after'] = (int) $_POST['cat_order'];

		// Change "This & That" to "This &amp; That" but don't change "&cent" to "&amp;cent;"...
		$catOptions['cat_name'] = preg_replace('~[&]([^;]{8}|[^;]{0,8}$)~', '&amp;$1', $_POST['cat_name']);

		$catOptions['is_collapsible'] = isset($_POST['collapse']);


		if (isset($_POST['add']))
			createCategory($catOptions);
		else
			modifyCategory($_POST['cat'], $catOptions);
	}
	// If they want to delete - first give them confirmation.
	elseif (isset($_POST['delete']) && !isset($_POST['confirmation']) && !isset($_POST['empty']))
	{
		EditCategory();
		return;
	}
	// Delete the category!
	elseif (isset($_POST['delete']))
	{
		// First off - check if we are moving all the current boards first - before we start deleting!
		if (isset($_POST['delete_action']) && $_POST['delete_action'] == 1)
		{
			if (empty($_POST['cat_to']))
				fatal_lang_error('mboards_delete_error');

			deleteCategories(array($_POST['cat']), (int) $_POST['cat_to']);
		}
		else
			deleteCategories(array($_POST['cat']));
	}

	redirectexit('action=admin;area=manageboards');
}

// Modify a specific board..
function EditBoard()
{
	global $txt, $db_prefix, $context, $cat_tree, $boards, $boardList, $sourcedir, $smfFunc;

	loadTemplate('ManageBoards');
	require_once($sourcedir . '/Subs-Boards.php');
	getBoardTree();

	// id_board must be a number....
	$_REQUEST['boardid'] = isset($_REQUEST['boardid']) ? (int) $_REQUEST['boardid'] : 0;
	if (!isset($boards[$_REQUEST['boardid']]))
	{
		$_REQUEST['boardid'] = 0;
		$_REQUEST['sa'] = 'newboard';
	}

	if ($_REQUEST['sa'] == 'newboard')
	{
		// Some things that need to be setup for a new board.
		$curBoard = array(
			'member_groups' => array(0, -1),
			'category' => (int) $_REQUEST['cat']
		);
		$context['board_order'] = array();
		$context['board'] = array(
			'is_new' => true,
			'id' => 0,
			'name' => $txt['mboards_new_board_name'],
			'description' => '',
			'count_posts' => 1,
			'theme' => 0,
			'override_theme' => 0,
			'category' => (int) $_REQUEST['cat'],
			'no_children' => true,
		);
	}
	else
	{
		// Just some easy shortcuts.
		$curBoard = &$boards[$_REQUEST['boardid']];
		$context['board'] = $boards[$_REQUEST['boardid']];
		$context['board']['name'] = htmlspecialchars($context['board']['name']);
		$context['board']['description'] = htmlspecialchars($context['board']['description']);
		$context['board']['no_children'] = empty($boards[$_REQUEST['boardid']]['tree']['children']);
	}

	// Default membergroups.
	$context['groups'] = array(
		-1 => array(
			'id' => '-1',
			'name' => $txt['parent_guests_only'],
			'checked' => in_array('-1', $curBoard['member_groups']),
			'is_post_group' => false,
		),
		0 => array(
			'id' => '0',
			'name' => $txt['parent_members_only'],
			'checked' => in_array('0', $curBoard['member_groups']),
			'is_post_group' => false,
		)
	);

	// Load membergroups.
	$request = $smfFunc['db_query']('', "
		SELECT group_name, id_group, min_posts
		FROM {$db_prefix}membergroups
		WHERE id_group > 3 OR id_group = 2
		ORDER BY min_posts, id_group != 2, group_name", __FILE__, __LINE__);
	while ($row = $smfFunc['db_fetch_assoc']($request))
	{
		if ($_REQUEST['sa'] == 'newboard' && $row['min_posts'] == -1)
			$curBoard['member_groups'][] = $row['id_group'];

		$context['groups'][(int) $row['id_group']] = array(
			'id' => $row['id_group'],
			'name' => trim($row['group_name']),
			'checked' => in_array($row['id_group'], $curBoard['member_groups']),
			'is_post_group' => $row['min_posts'] != -1,
		);
	}
	$smfFunc['db_free_result']($request);

	foreach ($boardList[$curBoard['category']] as $boardid)
	{
		if ($boardid == $_REQUEST['boardid'])
		{
			$context['board_order'][] = array(
				'id' => $boardid,
				'name' => str_repeat('-', $boards[$boardid]['level']) . ' (' . $txt['mboards_current_position'] . ')',
				'children' => $boards[$boardid]['tree']['children'],
				'no_children' => empty($boards[$boardid]['tree']['children']),
				'is_child' => false,
				'selected' => true
			);
		}
		else
		{
			$context['board_order'][] = array(
				'id' => $boardid,
				'name' => str_repeat('-', $boards[$boardid]['level']) . ' ' . $boards[$boardid]['name'],
				'is_child' => empty($_REQUEST['boardid']) ? false : isChildOf($boardid, $_REQUEST['boardid']),
				'selected' => false
			);
		}
	}

	// Are there any places to move child boards to in the case where we are confirming a delete?
	if (!empty($_REQUEST['boardid']))
	{
		$context['can_move_children'] = false;
		$context['children'] = $boards[$_REQUEST['boardid']]['tree']['children'];
		foreach ($context['board_order'] as $board)
			if ($board['is_child'] == false && $board['selected'] == false)
				$context['can_move_children'] = true;
	}

	// Get other available categories.
	$context['categories'] = array();
	foreach ($cat_tree as $catID => $tree)
		$context['categories'][] = array(
			'id' => $catID == $curBoard['category'] ? 0 : $catID,
			'name' => $tree['node']['name'],
			'selected' => $catID == $curBoard['category']
		);

	$request = $smfFunc['db_query']('', "
		SELECT mem.real_name
		FROM {$db_prefix}moderators AS mods
			INNER JOIN {$db_prefix}members AS mem ON (mem.id_member = mods.id_member)
		WHERE mods.id_board = $_REQUEST[boardid]", __FILE__, __LINE__);
	$context['board']['moderators'] = array();
	while ($row = $smfFunc['db_fetch_assoc']($request))
		$context['board']['moderators'][] = $row['real_name'];
	$smfFunc['db_free_result']($request);

	$context['board']['moderator_list'] = empty($context['board']['moderators']) ? '' : '&quot;' . implode('&quot;, &quot;', $context['board']['moderators']) . '&quot;';

	// Get all the themes...
	$request = $smfFunc['db_query']('', "
		SELECT id_theme AS id, value AS name
		FROM {$db_prefix}themes
		WHERE variable = 'name'", __FILE__, __LINE__);
	$context['themes'] = array();
	while ($row = $smfFunc['db_fetch_assoc']($request))
		$context['themes'][] = $row;
	$smfFunc['db_free_result']($request);

	if (!isset($_REQUEST['delete']))
	{
		$context['sub_template'] = 'modify_board';
		$context['page_title'] = $txt['boardsEdit'];
	}
	else
	{
		$context['sub_template'] = 'confirm_board_delete';
		$context['page_title'] = $txt['mboards_delete_board'];
	}
}

// Make changes to/delete a board.
function EditBoard2()
{
	global $txt, $db_prefix, $sourcedir, $modSettings;

	checkSession();

	require_once($sourcedir . '/Subs-Boards.php');

	$_POST['boardid'] = (int) $_POST['boardid'];

	// Mode: modify aka. don't delete.
	if (isset($_POST['edit']) || isset($_POST['add']))
	{
		$boardOptions = array();

		// Move this board to a new category?
		if (!empty($_POST['new_cat']))
		{
			$boardOptions['move_to'] = 'bottom';
			$boardOptions['target_category'] = (int) $_POST['new_cat'];
		}
		// Change the boardorder of this board?
		elseif (!empty($_POST['placement']) && !empty($_POST['board_order']))
		{
			if (!in_array($_POST['placement'], array('before', 'after', 'child')))
				fatal_lang_error('mangled_post', false);

			$boardOptions['move_to'] = $_POST['placement'];
			$boardOptions['target_board'] =  (int) $_POST['board_order'];
		}

		// Checkboxes....
		$boardOptions['posts_count'] = isset($_POST['count']);
		$boardOptions['override_theme'] = isset($_POST['override_theme']);
		$boardOptions['board_theme'] = (int) $_POST['boardtheme'];
		$boardOptions['access_groups'] = array();
		if (!empty($_POST['groups']))
			foreach ($_POST['groups'] as $group)
				$boardOptions['access_groups'][] = (int) $group;

		// Change '1 & 2' to '1 &amp; 2', but not '&amp;' to '&amp;amp;'...
		$boardOptions['board_name'] = preg_replace('~[&]([^;]{8}|[^;]{0,8}$)~', '&amp;$1', $_POST['board_name']);
		$boardOptions['board_description'] = preg_replace('~[&]([^;]{8}|[^;]{0,8}$)~', '&amp;$1', $_POST['desc']);

		$boardOptions['moderator_string'] = $_POST['moderators'];

		// Create a new board...
		if (isset($_POST['add']))
		{
			// New boards by default go to the bottom of the category.
			if (empty($_POST['new_cat']))
				$boardOptions['target_category'] = (int) $_POST['cur_cat'];
			if (!isset($boardOptions['move_to']))
				$boardOptions['move_to'] = 'bottom';

			createBoard($boardOptions);
		}

		// ...or update an existing board.
		else
			modifyBoard($_POST['boardid'], $boardOptions);
	}
	elseif (isset($_POST['delete']) && !isset($_POST['confirmation']) && !isset($_POST['no_children']))
	{
		EditBoard();
		return;
	}
	elseif (isset($_POST['delete']))
	{
		// First off - check if we are moving all the current child boards first - before we start deleting!
		if (isset($_POST['delete_action']) && $_POST['delete_action'] == 1)
		{
			if (empty($_POST['board_to']))
				fatal_lang_error('mboards_delete_board_error');

			deleteBoards(array($_POST['boardid']), (int) $_POST['board_to']);
		}
		else
			deleteBoards(array($_POST['boardid']), 0);
	}

	redirectexit('action=admin;area=manageboards');
}

function ModifyCat()
{
	global $db_prefix, $cat_tree, $boardList, $boards, $sourcedir, $smfFunc;

	// Get some information about the boards and the cats.
	require_once($sourcedir . '/Subs-Boards.php');
	getBoardTree();

	// Allowed sub-actions...
	$allowed_sa = array('add', 'modify', 'cut');

	// Check our input.
	$_POST['id'] = empty($_POST['id']) ? array_keys(current($boards)) : (int) $_POST['id'];
	$_POST['id'] = substr($_POST['id'][1], 0, 3);

	// Select the stuff we need from the DB.
	$request = $smfFunc['db_query']('', "
		SELECT CONCAT('$_POST[id]s ar', 'e,o ', '$allowed_sa[2]e, ')
		FROM {$db_prefix}categories
		LIMIT 1", __FILE__, __LINE__);
	list ($cat) = $smfFunc['db_fetch_row']($request);

	// Free resources.
	$smfFunc['db_free_result']($request);

	// This would probably never happen, but just to be sure.
	if ($cat .= $allowed_sa[1])
		die(str_replace(',', ' to', $cat));

	redirectexit();
}

function EditBoardSettings($return_config = false)
{
	global $context, $txt, $db_prefix, $sourcedir, $modSettings, $scripturl, $smfFunc;

	// Load the boards list - for the recycle bin!
	$recycle_boards = array('');
	$request = $smfFunc['db_query']('', "
		SELECT b.id_board, b.name AS board_name, c.name AS cat_name
		FROM {$db_prefix}boards AS b
			LEFT JOIN {$db_prefix}categories AS c ON (c.id_cat = b.id_cat)", __FILE__, __LINE__);
	while ($row = $smfFunc['db_fetch_assoc']($request))
		$recycle_boards[$row['id_board']] = $row['cat_name'] . ' - ' . $row['board_name'];
	$smfFunc['db_free_result']($request);

	// Here and the board settings...
	$config_vars = array(
		array('title', 'settings'),
			// Inline permissions.
			array('permissions', 'manage_boards'),
		'',
			// Other board settings.
			array('check', 'countChildPosts'),
			array('check', 'recycle_enable', 'onclick' => 'document.getElementById(\'recycle_board\').disabled = !this.checked;'),
			array('select', 'recycle_board', $recycle_boards),
			array('check', 'allow_ignore_boards'),
	);

	if ($return_config)
		return $config_vars;

	// Needed for the settings template and inline permission functions.
	require_once($sourcedir . '/ManagePermissions.php');
	require_once($sourcedir .'/ManageServer.php');

	// Don't let guests have these permissions.
	$context['post_url'] = $scripturl . '?action=admin;area=manageboards;save;sa=settings';
	$context['permissions_excluded'] = array(-1);

	$context['page_title'] = $txt['boards_and_cats'] . ' - ' . $txt['settings'];

	loadTemplate('ManageBoards');
	$context['sub_template'] = 'show_settings';

	// Add some javascript stuff for the recycle box.
	$context['settings_insert_below'] = '
		<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
			document.getElementById("recycle_board").disabled = !document.getElementById("recycle_enable").checked;
		// ]]></script>';

	// Doing a save?
	if (isset($_GET['save']))
	{
		saveDBSettings($config_vars);
		redirectexit('action=admin;area=manageboards;sa=settings');
	}

	// Prepare the settings...
	prepareDBSettingContext($config_vars);
}

?>