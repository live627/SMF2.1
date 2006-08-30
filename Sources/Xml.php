<?php
/******************************************************************************
* Xml.php                                                                     *
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

/*	This file maintains all XML-based interaction (mainly XMLhttp).

	void GetJumpTo()

*/

function XMLhttpMain()
{
	loadTemplate('Xml');

	$sub_actions = array(
		'jumpto' => array(
			'function' => 'GetJumpTo',
		),
	);
	if (!isset($_REQUEST['sa'], $sub_actions[$_REQUEST['sa']]))
		fatal_error('Action doesn\'t exist');

	$sub_actions[$_REQUEST['sa']]['function']();
}


// Get a list of boards and categories used for the jumpto dropdown.
function GetJumpTo()
{
	global $db_prefix, $user_info, $context;

	// Find the boards/cateogories they can see.
	$request = db_query("
		SELECT c.name AS catName, c.ID_CAT, b.ID_BOARD, b.name AS boardName, b.childLevel
		FROM {$db_prefix}boards AS b
			LEFT JOIN {$db_prefix}categories AS c ON (c.ID_CAT = b.ID_CAT)
		WHERE $user_info[query_see_board]", __FILE__, __LINE__);
	$context['jump_to'] = array();
	$this_cat = array('id' => -1);
	while ($row = mysql_fetch_assoc($request))
	{
		if ($this_cat['id'] != $row['ID_CAT'])
		{
			$this_cat = &$context['jump_to'][];
			$this_cat['id'] = $row['ID_CAT'];
			$this_cat['name'] = un_htmlspecialchars($row['catName']);
			$this_cat['boards'] = array();
		}

		$this_cat['boards'][] = array(
			'id' => $row['ID_BOARD'],
			'name' => un_htmlspecialchars($row['boardName']),
			'child_level' => $row['childLevel'],
			'is_current' => isset($context['current_board']) && $row['ID_BOARD'] == $context['current_board']
		);
	}
	mysql_free_result($request);

	$context['sub_template'] = 'jump_to';
}

?>