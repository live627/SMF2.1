<?php
/**********************************************************************************
* Memberlist.php                                                                  *
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

/*	This file contains the functions for displaying and searching in the
	members list.  It does so with these functions:

	void MemberList()
		- shows a list of registered members.
		- if a subaction is not specified, lists all registered members.
		- allows searching for members with the 'search' sub action.
		- calls MLAll or MLSearch depending on the sub action.
		- uses the Memberlist template with the main sub template.
		- requires the view_mlist permission.
		- is accessed via ?action=mlist.

	void MLAll()
		- used to display all members on a page by page basis with sorting.
		- called from MemberList().
		- can be passed a sort parameter, to order the display of members.
		- calls printMemberListRows to retrieve the results of the query.

	void MLSearch()
		- used to search for members or display search results.
		- called by MemberList().
		- if variable 'search' is empty displays search dialog box, using the
		  search sub template.
		- calls printMemberListRows to retrieve the results of the query.

	void printMemberListRows(resource request)
		- retrieves results of the request passed to it
		- puts results of request into the context for the sub template.
*/

// Show a listing of the registered members.
function Memberlist()
{
	global $scripturl, $txt, $modSettings, $context, $settings;

	// Make sure they can view the memberlist.
	isAllowedTo('view_mlist');

	loadTemplate('Memberlist');

	$context['listing_by'] = !empty($_GET['sa']) ? $_GET['sa'] : 'all';

	// $subActions array format:
	// 'subaction' => array('label', 'function', 'is_selected')
	$subActions = array(
		'all' => array(&$txt['view_all_members'], 'MLAll', $context['listing_by'] == 'all'),
		'search' => array(&$txt['mlist_search'], 'MLSearch', $context['listing_by'] == 'search'),
	);

	// Set up the sort links.
	$context['sort_links'] = array();
	foreach ($subActions as $act => $text)
		$context['sort_links'][] = array(
			'label' => $text[0],
			'action' => $act,
			'selected' => $text[2],
		);

	$context['num_members'] = $modSettings['totalMembers'];

	// Set up the columns...
	$context['columns'] = array(
		'is_online' => array(
			'label' => $txt['online8'],
			'width' => '20'
		),
		'real_name' => array(
			'label' => $txt['username']
		),
		'email_address' => array(
			'label' => $txt['email'],
			'width' => '25'
		),
		'website_url' => array(
			'label' => $txt['website'],
			'width' => '25'
		),
		'icq' => array(
			'label' => $txt[513],
			'width' => '25'
		),
		'aim' => array(
			'label' => $txt[603],
			'width' => '25'
		),
		'yim' => array(
			'label' => $txt[604],
			'width' => '25'
		),
		'msn' => array(
			'label' => $txt['msn'],
			'width' => '25'
		),
		'id_group' => array(
			'label' => $txt['position']
		),
		'registered' => array(
			'label' => $txt['date_registered']
		),
		'posts' => array(
			'label' => $txt['posts'],
			'width' => '115',
			'colspan' => '2'
		)
	);

	$context['linktree'][] = array(
		'url' => $scripturl . '?action=mlist',
		'name' => &$txt[332]
	);

	$context['can_send_pm'] = allowedTo('pm_send');

	// Jump to the sub action.
	if (isset($subActions[$context['listing_by']]))
		$subActions[$context['listing_by']][1]();
	else
		$subActions['all'][1]();
}

// List all members, page by page.
function MLAll()
{
	global $txt, $scripturl, $db_prefix, $user_info;
	global $modSettings, $context, $smfFunc;

	// The chunk size for the cached index.
	$cache_step_size = 500;

	// Only use caching if:
	// 1. there are at least 2k members,
	// 2. the default sorting method (real_name) is being used,
	// 3. the page shown is high enough to make a DB filesort unprofitable.
	$use_cache = $modSettings['totalMembers'] > 2000 && (!isset($_REQUEST['sort']) || $_REQUEST['sort'] === 'real_name') && isset($_REQUEST['start']) && $_REQUEST['start'] > $cache_step_size;

	if ($use_cache)
	{
		// Maybe there's something cached already.
		if (!empty($modSettings['memberlist_cache']))
			$memberlist_cache = @unserialize($modSettings['memberlist_cache']);

		// The chunk size for the cached index.
		$cache_step_size = 500;

		// Only update the cache if something changed or no cache existed yet.
		if (empty($memberlist_cache) || empty($modSettings['memberlist_updated']) || $memberlist_cache['last_update'] < $modSettings['memberlist_updated'])
		{
			$request = $smfFunc['db_query']('', "
				SELECT real_name
				FROM {$db_prefix}members
				WHERE is_activated = 1
				ORDER BY real_name", __FILE__, __LINE__);

			$memberlist_cache = array(
				'last_update' => time(),
				'num_members' => $smfFunc['db_num_rows']($request),
				'index' => array(),
			);

			for ($i = 0, $n = $smfFunc['db_num_rows']($request); $i < $n; $i += $cache_step_size)
			{
				$smfFunc['db_data_seek']($request, $i);
				list($memberlist_cache['index'][$i]) = $smfFunc['db_fetch_row']($request);
			}
			$smfFunc['db_data_seek']($request, $memberlist_cache['num_members'] - 1);
			list($memberlist_cache['index'][$i]) = $smfFunc['db_fetch_row']($request);
			$smfFunc['db_free_result']($request);

			// Now we've got the cache...store it.
			updateSettings(array('memberlist_cache' => addslashes(serialize($memberlist_cache))));
		}

		$context['num_members'] = $memberlist_cache['num_members'];
	}

	// Without cache we need an extra query to get the amount of members.
	else
	{
		$request = $smfFunc['db_query']('', "
			SELECT COUNT(*)
			FROM {$db_prefix}members
			WHERE is_activated = 1", __FILE__, __LINE__);
		list ($context['num_members']) = $smfFunc['db_fetch_row']($request);
		$smfFunc['db_free_result']($request);
	}

	// Set defaults for sort (real_name) and start. (0)
	if (!isset($_REQUEST['sort']) || !isset($context['columns'][$_REQUEST['sort']]))
		$_REQUEST['sort'] = 'real_name';

	if (!is_numeric($_REQUEST['start']))
	{
		if (preg_match('~^[^\'\\\\/]~' . ($context['utf8'] ? 'u' : ''), $smfFunc['strtolower']($_REQUEST['start']), $match) === 0)
			fatal_error('Hacker?', false);

		$_REQUEST['start'] = $match[0];

		$request = $smfFunc['db_query']('', "
			SELECT COUNT(*)
			FROM {$db_prefix}members
			WHERE LOWER(SUBSTRING(real_name, 1, 1)) < '$_REQUEST[start]'
				AND is_activated = 1", __FILE__, __LINE__);
		list ($_REQUEST['start']) = $smfFunc['db_fetch_row']($request);
		$smfFunc['db_free_result']($request);
	}

	$context['letter_links'] = '';
	for ($i = 97; $i < 123; $i++)
		$context['letter_links'] .= '<a href="' . $scripturl . '?action=mlist;sa=all;start=' . chr($i) . '#letter' . chr($i) . '">' . strtoupper(chr($i)) . '</a> ';

	// Sort out the column information.
	foreach ($context['columns'] as $col => $dummy)
	{
		$context['columns'][$col]['href'] = $scripturl . '?action=mlist;sort=' . $col . ';start=0';

		if (!isset($_REQUEST['desc']) && $col == $_REQUEST['sort'])
			$context['columns'][$col]['href'] .= ';desc';

		$context['columns'][$col]['link'] = '<a href="' . $context['columns'][$col]['href'] . '">' . $context['columns'][$col]['label'] . '</a>';
		$context['columns'][$col]['selected'] = $_REQUEST['sort'] == $col;
	}

	$context['sort_by'] = $_REQUEST['sort'];
	$context['sort_direction'] = !isset($_REQUEST['desc']) ? 'down' : 'up';

	// Construct the page index.
	$context['page_index'] = constructPageIndex($scripturl . '?action=mlist;sort=' . $_REQUEST['sort'] . (isset($_REQUEST['desc']) ? ';desc' : ''), $_REQUEST['start'], $context['num_members'], $modSettings['defaultMaxMembers']);

	// Send the data to the template.
	$context['start'] = $_REQUEST['start'] + 1;
	$context['end'] = min($_REQUEST['start'] + $modSettings['defaultMaxMembers'], $context['num_members']);

	$context['page_title'] = sprintf($txt['viewing_members'], $context['start'], $context['end']);
	$context['linktree'][] = array(
		'url' => $scripturl . '?action=mlist;sort=' . $_REQUEST['sort'] . ';start=' . $_REQUEST['start'],
		'name' => &$context['page_title'],
		'extra_after' => ' (' . sprintf($txt['of_total_members'], $context['num_members']) . ')'
	);

	// List out the different sorting methods...
	$sort_methods = array(
		'is_online' => array(
			'down' => '(ISNULL(lo.log_time)' . (!allowedTo('moderate_forum') ? ' OR NOT mem.show_online' : '') . ') ASC, real_name ASC',
			'up' => '(ISNULL(lo.log_time)' . (!allowedTo('moderate_forum') ? ' OR NOT mem.show_online' : '') . ') DESC, real_name DESC'
		),
		'real_name' => array(
			'down' => 'mem.real_name ASC',
			'up' => 'mem.real_name DESC'
		),
		'email_address' => array(
			'down' => (allowedTo('moderate_forum') || empty($modSettings['allow_hide_email'])) ? 'mem.email_address ASC' : 'mem.hide_email ASC, mem.email_address ASC',
			'up' => (allowedTo('moderate_forum') || empty($modSettings['allow_hide_email'])) ? 'mem.email_address DESC' : 'mem.hide_email DESC, mem.email_address DESC'
		),
		'website_url' => array(
			'down' => 'LENGTH(mem.websiteURL) > 0 DESC, ISNULL(mem.websiteURL) ASC, mem.websiteURL ASC',
			'up' => 'LENGTH(mem.websiteURL) > 0 ASC, ISNULL(mem.websiteURL) DESC, mem.websiteURL DESC'
		),
		'icq' => array(
			'down' => 'LENGTH(mem.icq) > 0 DESC, ISNULL(mem.icq) OR mem.icq = 0 ASC, mem.icq ASC',
			'up' => 'LENGTH(mem.icq) > 0 ASC, ISNULL(mem.icq) OR mem.icq = 0 DESC, mem.icq DESC'
		),
		'aim' => array(
			'down' => 'LENGTH(mem.aim) > 0 DESC, ISNULL(mem.aim) ASC, mem.aim ASC',
			'up' => 'LENGTH(mem.aim) > 0 ASC, ISNULL(mem.aim) DESC, mem.aim DESC'
		),
		'yim' => array(
			'down' => 'LENGTH(mem.yim) > 0 DESC, ISNULL(mem.yim) ASC, mem.yim ASC',
			'up' => 'LENGTH(mem.yim) > 0 ASC, ISNULL(mem.yim) DESC, mem.yim DESC'
		),
		'msn' => array(
			'down' => 'LENGTH(mem.msn) > 0 DESC, ISNULL(mem.msn) ASC, mem.msn ASC',
			'up' => 'LENGTH(mem.msn) > 0 ASC, ISNULL(mem.msn) DESC, mem.msn DESC'
		),
		'registered' => array(
			'down' => 'mem.date_registered ASC',
			'up' => 'mem.date_registered DESC'
		),
		'id_group' => array(
			'down' => 'ISNULL(mg.group_name) ASC, mg.group_name ASC',
			'up' => 'ISNULL(mg.group_name) DESC, mg.group_name DESC'
		),
		'posts' => array(
			'down' => 'mem.posts DESC',
			'up' => 'mem.posts ASC'
		)
	);

	$limit = $_REQUEST['start'];

	// Using cache allows to narrow down the list to be retrieved.
	if ($use_cache && $_REQUEST['sort'] === 'real_name' && !isset($_REQUEST['desc']))
	{
		$first_offset = $_REQUEST['start'] - ($_REQUEST['start'] % $cache_step_size);
		$second_offset = ceil(($_REQUEST['start'] + $modSettings['defaultMaxMembers']) / $cache_step_size) * $cache_step_size;
		$where = "mem.real_name BETWEEN '" . addslashes($memberlist_cache['index'][$first_offset]) . "' AND '" . addslashes($memberlist_cache['index'][$second_offset]) . "'";
		$limit -= $first_offset;
	}

	// Reverse sorting is a bit more complicated...
	elseif ($use_cache && $_REQUEST['sort'] === 'real_name')
	{
		$first_offset = floor(($memberlist_cache['num_members'] - $modSettings['defaultMaxMembers'] - $_REQUEST['start']) / $cache_step_size) * $cache_step_size;
		if ($first_offset < 0)
			$first_offset = 0;
		$second_offset = ceil(($memberlist_cache['num_members'] - $_REQUEST['start']) / $cache_step_size) * $cache_step_size;
		$where = "mem.real_name BETWEEN '" . addslashes($memberlist_cache['index'][$first_offset]) . "' AND '" . addslashes($memberlist_cache['index'][$second_offset]) . "'";
		$limit = $second_offset - ($memberlist_cache['num_members'] - $_REQUEST['start']) - ($second_offset > $memberlist_cache['num_members'] ? $cache_step_size - ($memberlist_cache['num_members'] % $cache_step_size) : 0);
	}

	// Select the members from the database.
	$request = $smfFunc['db_query']('', "
		SELECT mem.id_member
		FROM {$db_prefix}members AS mem" . ($_REQUEST['sort'] === 'is_online' ? "
			LEFT JOIN {$db_prefix}log_online AS lo ON (lo.id_member = mem.id_member)" : '') . ($_REQUEST['sort'] === 'id_group' ? "
			LEFT JOIN {$db_prefix}membergroups AS mg ON (mg.id_group = IF(mem.id_group = 0, mem.id_post_group, mem.id_group))" : '') . "
		WHERE mem.is_activated = 1" . (empty($where) ? '' : "
			AND $where") . "
		ORDER BY " . $sort_methods[$_REQUEST['sort']][$context['sort_direction']] . "
		LIMIT $limit, $modSettings[defaultMaxMembers]", __FILE__, __LINE__);
	printMemberListRows($request);
	$smfFunc['db_free_result']($request);

	// Add anchors at the start of each letter.
	if ($_REQUEST['sort'] == 'real_name')
	{
		$last_letter = '';
		foreach ($context['members'] as $i => $dummy)
		{
			$this_letter = $smfFunc['strtolower']($smfFunc['substr']($context['members'][$i]['name'], 0, 1));

			if ($this_letter != $last_letter && preg_match('~[a-z]~', $this_letter) === 1)
			{
				$context['members'][$i]['sort_letter'] = htmlspecialchars($this_letter);
				$last_letter = $this_letter;
			}
		}
	}
}

// Search for members...
function MLSearch()
{
	global $txt, $scripturl, $db_prefix, $context, $user_info, $modSettings, $smfFunc;

	$context['page_title'] = $txt['mlist_search'];

	// They're searching..
	if (isset($_REQUEST['search']) && isset($_REQUEST['fields']))
	{
		$_POST['search'] = trim(isset($_GET['search']) ? $_GET['search'] : $_POST['search']);
		$_POST['fields'] = isset($_GET['fields']) ? explode(',', $_GET['fields']) : $_POST['fields'];

		$context['old_search'] = $_REQUEST['search'];
		$context['old_search_value'] = urlencode($_REQUEST['search']);

		// No fields?  Use default...
		if (empty($_POST['fields']))
			$_POST['fields'] = array('name');

		// Search for a name?
		if (in_array('name', $_POST['fields']))
			$fields = array('member_name', 'real_name');
		else
			$fields = array();
		// Search for messengers...
		if (in_array('messenger', $_POST['fields']) && (!$user_info['is_guest'] || empty($modSettings['guest_hideContacts'])))
			$fields += array(3 => 'msn', 'aim', 'icq', 'yim');
		// Search for websites.
		if (in_array('website', $_POST['fields']))
			$fields += array(7 => 'website_title', 'website_url');
		// Search for groups.
		if (in_array('group', $_POST['fields']))
			$fields += array(9 => 'IFNULL(group_name, \'\')');
		// Search for an email address?
		if (in_array('email', $_POST['fields']))
		{
			$fields += array(2 => allowedTo('moderate_forum') ? 'email_address' : '(hide_email = 0 AND email_address');
			$condition = allowedTo('moderate_forum') ? '' : ')';
		}
		else
			$condition = '';

		$query = $_POST['search'] == '' ? "= ''" : "LIKE '%" . strtr($_POST['search'], array('_' => '\\_', '%' => '\\%', '*' => '%')) . "%'";

		$request = $smfFunc['db_query']('', "
			SELECT COUNT(*)
			FROM {$db_prefix}members AS mem
				LEFT JOIN {$db_prefix}membergroups AS mg ON (mg.id_group = IF(mem.id_group = 0, mem.id_post_group, mem.id_group))
			WHERE " . implode(" $query OR ", $fields) . " $query$condition
				AND is_activated = 1", __FILE__, __LINE__);
		list ($numResults) = $smfFunc['db_fetch_row']($request);
		$smfFunc['db_free_result']($request);

		$context['page_index'] = constructPageIndex($scripturl . '?action=mlist;sa=search;search=' . $_POST['search'] . ';fields=' . implode(',', $_POST['fields']), $_REQUEST['start'], $numResults, $modSettings['defaultMaxMembers']);

		// Find the members from the database.
		// !!!SLOW This query is slow.
		$request = $smfFunc['db_query']('', "
			SELECT mem.id_member
			FROM {$db_prefix}members AS mem
				LEFT JOIN {$db_prefix}log_online AS lo ON (lo.id_member = mem.id_member)
				LEFT JOIN {$db_prefix}membergroups AS mg ON (mg.id_group = IF(mem.id_group = 0, mem.id_post_group, mem.id_group))
			WHERE " . implode(" $query OR ", $fields) . " $query$condition
				AND is_activated = 1
			LIMIT $_REQUEST[start], $modSettings[defaultMaxMembers]", __FILE__, __LINE__);
		printMemberListRows($request);
		$smfFunc['db_free_result']($request);
	}
	else
	{
		$context['sub_template'] = 'search';
		$context['old_search'] = isset($_REQUEST['search']) ? htmlspecialchars($_REQUEST['search']) : '';
	}

	$context['linktree'][] = array(
		'url' => $scripturl . '?action=mlist;sa=search',
		'name' => &$context['page_title']
	);
}

function printMemberListRows($request)
{
	global $scripturl, $txt, $db_prefix, $user_info, $modSettings;
	global $context, $settings, $memberContext, $smfFunc;

	// Get the most posts.
	$result = $smfFunc['db_query']('', "
		SELECT MAX(posts)
		FROM {$db_prefix}members", __FILE__, __LINE__);
	list ($MOST_POSTS) = $smfFunc['db_fetch_row']($result);
	$smfFunc['db_free_result']($result);

	// Avoid division by zero...
	if ($MOST_POSTS == 0)
		$MOST_POSTS = 1;

	$members = array();
	while ($row = $smfFunc['db_fetch_assoc']($request))
		$members[] = $row['id_member'];

	// Load all the members for display.
	loadMemberData($members);

	$context['members'] = array();
	foreach ($members as $member)
	{
		if (!loadMemberContext($member))
			continue;

		$context['members'][$member] = $memberContext[$member];
		$context['members'][$member]['post_percent'] = round(($context['members'][$member]['real_posts'] * 100) / $MOST_POSTS);
		$context['members'][$member]['registered_date'] = strftime('%Y-%m-%d', $context['members'][$member]['registered_timestamp']);
	}
}

?>