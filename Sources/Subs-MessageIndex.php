<?php
/**********************************************************************************
* Subs-MessageIndex.php                                                           *
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

/*


*/

function getBoardList($boardListOptions = array())
{
	global $smfFunc, $db_prefix, $user_info;

	if (isset($boardListOptions['excluded_boards']) && isset($boardListOptions['included_boards']))
		trigger_error('getBoardList(): Setting both excluded_boards and included_boards is not allowed.', E_USER_ERROR);

	$where = array();
	if (isset($boardListOptions['excluded_boards']))
		$where[] = 'b.id_board NOT IN (' . implode(', ', $boardListOptions['excluded_boards']) . ')';

	if (isset($boardListOptions['included_boards']))
		$where[] = 'b.id_board IN (' . implode(', ', $boardListOptions['included_boards']) . ')';

	if (!empty($boardListOptions['ignore_boards']))
		$where[] = $user_info['query_wanna_see_board'];

	elseif (!empty($boardListOptions['use_permissions']))
		$where[] = $user_info['query_see_board'];


	$request = $smfFunc['db_query']('', "
		SELECT c.name AS cat_name, c.id_cat, b.id_board, b.name AS board_name, b.child_level
		FROM {$db_prefix}boards AS b
			LEFT JOIN {$db_prefix}categories AS c ON (c.id_cat = b.id_cat)" . (empty($where) ? '' : "
		WHERE " . implode("
			AND ", $where)), __FILE__, __LINE__);

	$return_value = array();
	if ($smfFunc['db_num_rows']($request) !== 0)
	{
		while ($row = $smfFunc['db_fetch_assoc']($request))
		{
			if (!isset($return_value[$row['id_cat']]))
				$return_value[$row['id_cat']] = array(
					'id' => $row['id_cat'],
					'name' => $row['cat_name'],
					'boards' => array(),
				);

			$return_value[$row['id_cat']]['boards'][] = array(
				'id' => $row['id_board'],
				'name' => $row['board_name'],
				'child_level' => $row['child_level'],
				'selected' => isset($boardListOptions['selected_board']) && $boardListOptions['selected_board'] == $row['id_board'],
			);
		}
	}
	$smfFunc['db_free_result']($request);

	return $return_value;
}

?>