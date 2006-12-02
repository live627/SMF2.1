<?php
/**********************************************************************************
* ManageBans.php                                                                  *
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

/* This file contains all the functions used for the ban center.

	void Ban()
		- the main entrance point for all ban center functions.
		- is accesssed by ?action=admin;area=ban.
		- choses a function based on the 'sa' parameter.
		- defaults to BanList().
		- requires the ban_members permission.
		- initializes the admin tabs.
		- load the ManageBans template.

	void BanList()
		- shows a list of bans currently set.
		- is accesssed by ?action=admin;area=ban;sa=list.
		- uses the main ManageBans template.
		- removes expired bans.
		- allows sorting on different criteria.
		- also handles removal of selected ban items.

	array getBanEntry(bool $reset = false)
		- callback function for BanList, loading a single ban.
		- called by the main ManageBans template.

	void BanEdit()
		- the screen for adding new bans and modifying existing ones.
		- adding new bans:
			- is accesssed by ?action=admin;area=ban;sa=add.
			- uses the ban_edit sub template of the ManageBans template.
		- modifying existing bans:
			- is accesssed by ?action=admin;area=ban;sa=edit;bg=x
			- uses the ban_edit sub template of the ManageBans template.
			- shows a list of ban triggers for the specified ban.
		- handles submitted forms that add, modify or remove ban triggers.

	void BanEditTrigger()
		- the screen for adding new ban triggers or modifying existing ones.
		- adding new ban triggers:
			- is accessed by ?action=admin;area=ban;sa=edittrigger;bg=x
			- uses the ban_edit_trigger sub template of ManageBans.
		-editing existing ban triggers:
			- is accessed by ?action=admin;area=ban;sa=edittrigger;bg=x;bi=y
			- uses the ban_edit_trigger sub template of ManageBans.

	void BanBrowseTriggers()
		- screen for showing the banned enities
		- is accessed by ?action=admin;area=ban;sa=browse
		- uses the browse_triggers sub template of the ManageBans template.
		- uses sub-tabs for browsing by IP, hostname, email or username.

	array BanLog()
		- show a list of logged access attempts by banned users.
		- is accessed by ?action=admin;area=ban;sa=log.
		- allows sorting of several columns.
		- also handles deletion of (a selection of) log entries.

	string range2ip(array $low, array $high)
		- converts a given array of ip numbers to a single string
		- internal function used to convert a format suitable for the database
		   to a user-readable format.
		- range2ip(array(10, 10, 10, 0), array(10, 10, 20, 255)) returns
		   '10.10.10-20.*
		- returns 'unknown' if the ip in the input was '255.255.255.255'.

	array ip2range(string $fullip)
		- converts a given IP string to an array.
		- reverse function of range2ip().

	void updateBanMembers()
		- updates the members table to match the new bans.
		- is_activated >= 10: a member is banned.
*/

// Ban center.
function Ban()
{
	global $context, $txt, $scripturl;

	isAllowedTo('manage_bans');

	loadTemplate('ManageBans');

	$subActions = array(
		'add' => 'BanEdit',
		'browse' => 'BanBrowseTriggers',
		'edittrigger' => 'BanEditTrigger',
		'edit' => 'BanEdit',
		'list' => 'BanList',
		'log' => 'BanLog',
	);

	// Default the sub-action to 'view ban list'.
	$_REQUEST['sa'] = isset($_REQUEST['sa']) && isset($subActions[$_REQUEST['sa']]) ? $_REQUEST['sa'] : 'list';

	$context['page_title'] = &$txt['ban_title'];
	$context['sub_action'] = $_REQUEST['sa'];

	// Tabs for browsing the different ban functions.
	$context['admin_tabs'] = array(
		'title' => &$txt['ban_title'],
		'help' => 'ban_members',
		'description' => $txt['ban_description'],
		'tabs' => array(
			'list' => array(
				'title' => $txt['ban_edit_list'],
				'description' => $txt['ban_description'],
				'href' => $scripturl . '?action=admin;area=ban;sa=list',
				'is_selected' => $_REQUEST['sa'] == 'list' || $_REQUEST['sa'] == 'edit' || $_REQUEST['sa'] == 'edittrigger',
			),
			'add' => array(
				'title' => $txt['ban_add_new'],
				'description' => $txt['ban_description'],
				'href' => $scripturl . '?action=admin;area=ban;sa=add',
				'is_selected' => $_REQUEST['sa'] == 'add',
			),
			'browse' => array(
				'title' => $txt['ban_trigger_browse'],
				'description' => $txt['ban_trigger_browse_description'],
				'href' => $scripturl . '?action=admin;area=ban;sa=browse',
				'is_selected' => $_REQUEST['sa'] == 'browse',
			),
			'register' => array(
				'title' => $txt['ban_log'],
				'description' => $txt['ban_log_description'],
				'href' => $scripturl . '?action=admin;area=ban;sa=log',
				'is_selected' => $_REQUEST['sa'] == 'log',
				'is_last' => true,
			),
		),
	);

	// Call the right function for this sub-acton.
	$subActions[$_REQUEST['sa']]();
}

// List all the bans.
function BanList()
{
	global $txt, $db_prefix, $context, $ban_request, $ban_counts, $scripturl, $user_info, $smfFunc;

	// User pressed the 'remove selection button'.
	if (!empty($_POST['removeBans']) && !empty($_POST['remove']) && is_array($_POST['remove']))
	{
		checkSession();

		// Make sure every entry is a proper integer.
		foreach ($_POST['remove'] as $index => $ban_id)
			$_POST['remove'][(int) $index] = (int) $ban_id;

		// Unban them all!
		$smfFunc['db_query']('', "
			DELETE FROM {$db_prefix}ban_groups
			WHERE id_ban_group IN (" . implode(', ', $_POST['remove']) . ')', __FILE__, __LINE__);
		$smfFunc['db_query']('', "
			DELETE FROM {$db_prefix}ban_items
			WHERE id_ban_group IN (" . implode(', ', $_POST['remove']) . ')', __FILE__, __LINE__);

		// No more caching this ban!
		updateSettings(array('banLastUpdated' => time()));

		// Some members might be unbanned now. Update the members table.
		updateBanMembers();
	}

	// Ways we can sort this thing...
	$sort_methods = array(
		'name' =>  array(
			'down' => 'bg.name ASC',
			'up' => 'bg.name DESC'
		),
		'reason' => array(
			'down' => 'LENGTH(bg.reason) > 0 DESC, bg.reason ASC',
			'up' => 'LENGTH(bg.reason) > 0 ASC, bg.reason DESC'
		),
		'notes' => array(
			'down' => 'LENGTH(bg.notes) > 0 DESC, bg.notes ASC',
			'up' => 'LENGTH(bg.notes) > 0 ASC, bg.notes DESC'
		),
		'expires' => array(
			'down' => 'ISNULL(bg.expire_time) DESC, bg.expire_time DESC',
			'up' => 'ISNULL(bg.expire_time) ASC, bg.expire_time ASC'
		),
		'num_entries' => array(
			'down' => 'num_entries DESC',
			'up' => 'num_entries ASC',
		),
		'added' => array(
			'down' => 'bg.ban_time ASC',
			'up' => 'bg.ban_time DESC'
		),
		'expires' => array(
			'down' => 'ISNULL(bg.expire_time) DESC, bg.expire_time DESC',
			'up' => 'ISNULL(bg.expire_time) ASC, bg.expire_time ASC'
		),
	);

	// Columns to show.
	$context['columns'] = array(
		'name' => array(
			'width' => '20%',
			'label' => &$txt['ban_name'],
			'sortable' => true
		),
		'notes' => array(
			'width' => '20%',
			'label' => &$txt['ban_notes'],
			'sortable' => true
		),
		'reason' => array(
			'width' => '20%',
			'label' => &$txt['ban_reason'],
			'sortable' => true
		),
		'added' => array(
			'width' => '18%',
			'label' => &$txt['ban_added'],
			'sortable' => true
		),
		'expires' => array(
			'width' => '20%',
			'label' => &$txt['ban_expires'],
			'sortable' => true
		),
		'num_entries' => array(
			'label' => &$txt['ban_triggers'],
			'sortable' => true,
		),
		'actions' => array(
			'label' => &$txt['ban_actions'],
			'sortable' => false
		)
	);

	// Default the sort method to 'ban name'
	if (!isset($_REQUEST['sort']) || !isset($sort_methods[$_REQUEST['sort']]))
		$_REQUEST['sort'] = 'name';

	// Set some context values for each column.
	foreach ($context['columns'] as $col => $dummy)
	{
		$context['columns'][$col]['selected'] = $col == $_REQUEST['sort'];
		$context['columns'][$col]['href'] = $scripturl . '?action=admin;area=ban;sort=' . $col;

		if (!isset($_REQUEST['desc']) && $col == $_REQUEST['sort'])
			$context['columns'][$col]['href'] .= ';desc';

		$context['columns'][$col]['link'] = '<a href="' . $context['columns'][$col]['href'] . '">' . $context['columns'][$col]['label'] . '</a>';
	}

	$context['sort_by'] = $_REQUEST['sort'];
	$context['sort_direction'] = !isset($_REQUEST['desc']) ? 'down' : 'up';

	// Get the total amount of entries.
	$request = $smfFunc['db_query']('', "
		SELECT COUNT(*)
		FROM {$db_prefix}ban_groups", __FILE__, __LINE__);
	list ($totalBans) = $smfFunc['db_fetch_row']($request);
	$smfFunc['db_free_result']($request);

	// Create the page index.
	$context['page_index'] = constructPageIndex($scripturl . '?action=admin;area=ban;sort=' . $_REQUEST['sort'] . (isset($_REQUEST['desc']) ? ';desc' : ''), $_REQUEST['start'], $totalBans, 20);
	$context['start'] = $_REQUEST['start'];

	// Get the banned values.
	$request = $smfFunc['db_query']('', "
		SELECT bg.id_ban_group, COUNT(bi.id_ban) AS num_entries
		FROM {$db_prefix}ban_groups AS bg
			LEFT JOIN {$db_prefix}ban_items AS bi ON (bi.id_ban_group = bg.id_ban_group)
		GROUP BY bg.id_ban_group, bg.name
		ORDER BY " . $sort_methods[$_REQUEST['sort']][$context['sort_direction']] . "
		LIMIT $context[start], 20", __FILE__, __LINE__);
	$ban_counts = array();
	while ($row = $smfFunc['db_fetch_assoc']($request))
		$ban_counts[$row['id_ban_group']] = $row['num_entries'];
	$smfFunc['db_free_result']($request);

	if ($ban_counts)
		$ban_request = $smfFunc['db_query']('', "
			SELECT bg.id_ban_group, bg.name, bg.ban_time, bg.expire_time, bg.reason, bg.notes
			FROM {$db_prefix}ban_groups AS bg
			WHERE bg.id_ban_group IN (" . implode(', ', array_keys($ban_counts)) . ")
			ORDER BY " . $sort_methods[$_REQUEST['sort']][$context['sort_direction']] . "
			LIMIT $context[start], 20", __FILE__, __LINE__);
	else
		$ban_request = false;

	// Set the value of the callback function.
	$context['get_ban'] = 'getBanEntry';

	// Finally, create a date string so we don't overload them with date info.
	if (preg_match('~%[AaBbCcDdeGghjmuYy](?:[^%]*%[AaBbCcDdeGghjmuYy])*~', $user_info['time_format'], $matches) == 0 || empty($matches[0]))
		$context['ban_time_format'] = $user_info['time_format'];
	else
		$context['ban_time_format'] = $matches[0];
}

// Call-back function for the template to retrieve a row of ban data.
function getBanEntry($reset = false)
{
	global $scripturl, $ban_request, $ban_counts, $txt, $context, $smfFunc;

	if ($ban_request == false)
		return false;

	if (!($row = $smfFunc['db_fetch_assoc']($ban_request)))
		return false;

	$output = array(
		'id' => $row['id_ban_group'],
		'name' => $row['name'],
		'added' => !empty($context['ban_time_format']) ? timeformat($row['ban_time'], $context['ban_time_format']) : timeformat($row['ban_time']),
		'reason' => $row['reason'],
		'notes' => $row['notes'],
		'expires' => $row['expire_time'] === null ? $txt['never'] : ($row['expire_time'] < time() ? '<span style="color: red">' . $txt['ban_expired'] . '</span>' : ceil(($row['expire_time'] - time()) / (60 * 60 * 24)) . '&nbsp;' . $txt['ban_days']),
		'num_entries' => $ban_counts[$row['id_ban_group']],
	);

	return $output;
}

function BanEdit()
{
	global $txt, $db_prefix, $modSettings, $context, $ban_request, $scripturl, $smfFunc;

	$_REQUEST['bg'] = empty($_REQUEST['bg']) ? 0 : (int) $_REQUEST['bg'];

	// Adding or editing a ban trigger?
	if (!empty($_POST['add_new_trigger']) || !empty($_POST['edit_trigger']))
	{
		checkSession();

		$newBan = !empty($_POST['add_new_trigger']);

		// Preset all values that are required.
		if ($newBan)
			$inserts = array(
				'id_ban_group' => $_REQUEST['bg'],
				'hostname' => "''",
				'email_address' => "''",
			);

		if ($_POST['bantype'] == 'ip_ban')
		{
			$ip_parts = ip2range($_POST['ip']);
			if (count($ip_parts) != 4)
				fatal_lang_error('invalid_ip', false);

			if ($newBan)
			{
				$inserts += array(
					'ip_low1' => $ip_parts[0]['low'],
					'ip_high1' => $ip_parts[0]['high'],
					'ip_low2' => $ip_parts[1]['low'],
					'ip_high2' => $ip_parts[1]['high'],
					'ip_low3' => $ip_parts[2]['low'],
					'ip_high3' => $ip_parts[2]['high'],
					'ip_low4' => $ip_parts[3]['low'],
					'ip_high4' => $ip_parts[3]['high'],
				);
			}
			else
				$update = '
					ip_low1 = ' . $ip_parts[0]['low'] . ', ip_high1 = ' . $ip_parts[0]['high'] . ',
					ip_low2 = ' . $ip_parts[1]['low'] . ', ip_high2 = ' . $ip_parts[1]['high'] . ',
					ip_low3 = ' . $ip_parts[2]['low'] . ', ip_high3 = ' . $ip_parts[2]['high'] . ',
					ip_low4 = ' . $ip_parts[3]['low'] . ', ip_high4 = ' . $ip_parts[3]['high'] . ',
					hostname = \'\', email_address = \'\', id_member = 0';

			$modlogInfo['ip_range'] = $_POST['ip'];
		}
		elseif ($_POST['bantype'] == 'hostname_ban')
		{
			if (preg_match("/[^\w.\-*]/", $_POST['hostname']) == 1)
				fatal_lang_error('invalid_hostname', false);

			// Replace the * wildcard by a MySQL compatible wildcard %.
			$_POST['hostname'] = str_replace('*', '%', $_POST['hostname']);

			if ($newBan)
				$inserts['hostname'] = "'$_POST[hostname]'";
			else
				$update = "
					ip_low1 = 0, ip_high1 = 0,
					ip_low2 = 0, ip_high2 = 0,
					ip_low3 = 0, ip_high3 = 0,
					ip_low4 = 0, ip_high4 = 0,
					hostname = '$_POST[hostname]', email_address = '', id_member = 0";

			$modlogInfo['hostname'] = $smfFunc['db_unescape_string']($_POST['hostname']);
		}
		elseif ($_POST['bantype'] == 'email_ban')
		{
			if (preg_match("/[^\w.\-*@]/", $_POST['email']) == 1)
				fatal_lang_error('invalid_email', false);
			$_POST['email'] = strtolower(str_replace('*', '%', $_POST['email']));

			// Check the user is not banning an admin.
			$request = $smfFunc['db_query']('', "
				SELECT id_member
				FROM {$db_prefix}members
				WHERE (id_group = 1 OR FIND_IN_SET(1, additional_groups))
					AND email_address LIKE '$_POST[email]'
				LIMIT 1", __FILE__, __LINE__);
			if ($smfFunc['db_num_rows']($request) != 0)
				fatal_lang_error('no_ban_admin', 'critical');
			$smfFunc['db_free_result']($request);

			if ($newBan)
				$inserts['email_address'] = "'$_POST[email]'";
			else
				$update = "
					ip_low1 = 0, ip_high1 = 0,
					ip_low2 = 0, ip_high2 = 0,
					ip_low3 = 0, ip_high3 = 0,
					ip_low4 = 0, ip_high4 = 0,
					hostname = '', email_address = '$_POST[email]', id_member = 0";

			$modlogInfo['email'] = $smfFunc['db_unescape_string']($_POST['email']);
		}
		elseif ($_POST['bantype'] == 'user_ban')
		{
			$_POST['user'] = preg_replace('~&amp;#(\d{4,5}|[2-9]\d{2,4}|1[2-9]\d);~', '&#$1;', $smfFunc['db_escape_string'](htmlspecialchars($smfFunc['db_unescape_string']($_POST['user']), ENT_QUOTES)));

			$request = $smfFunc['db_query']('', "
				SELECT id_member, (id_group = 1 OR FIND_IN_SET(1, additional_groups)) AS isAdmin
				FROM {$db_prefix}members
				WHERE member_name = '$_POST[user]' OR real_name = '$_POST[user]'
				LIMIT 1", __FILE__, __LINE__);
			if ($smfFunc['db_num_rows']($request) == 0)
				fatal_lang_error('invalid_username', false);
			list ($memberid, $isAdmin) = $smfFunc['db_fetch_row']($request);
			$smfFunc['db_free_result']($request);

			if ($isAdmin)
				fatal_lang_error('no_ban_admin', 'critical');

			if ($newBan)
				$inserts['id_member'] = $memberid;
			else
				$update = "
					ip_low1 = 0, ip_high1 = 0,
					ip_low2 = 0, ip_high2 = 0,
					ip_low3 = 0, ip_high3 = 0,
					ip_low4 = 0, ip_high4 = 0,
					hostname = '', email_address = '', id_member = $memberid";

			$modlogInfo['member'] = $memberid;
		}
		else
			fatal_lang_error('no_bantype_selected', false);

		if ($newBan)
			$smfFunc['db_query']('', "
				INSERT INTO {$db_prefix}ban_items
					(" . implode(', ', array_keys($inserts)) . ")
				VALUES (" . implode(', ', $inserts) . ")", __FILE__, __LINE__);
		else
			$smfFunc['db_query']('', "
				UPDATE {$db_prefix}ban_items
				SET $update
				WHERE id_ban = " . (int) $_REQUEST['bi'] . "
					AND id_ban_group = $_REQUEST[bg]", __FILE__, __LINE__);

		// Log the addion of the ban entry into the moderation log.
		logAction('ban', $modlogInfo + array(
			'new' => $newBan,
			'type' => $_POST['bantype'],
		));

		// Register the last modified date.
		updateSettings(array('banLastUpdated' => time()));

		// Update the member table to represent the new ban situation.
		updateBanMembers();
	}

	// The user pressed 'Remove selected ban entries'.
	elseif (!empty($_POST['remove_selection']) && !empty($_POST['ban_items']) && is_array($_POST['ban_items']))
	{
		checkSession();

		// Making sure every deleted ban item is an integer.
		foreach ($_POST['ban_items'] as $key => $value)
			$_POST['ban_items'][$key] = (int) $value;

		$smfFunc['db_query']('', "
			DELETE FROM {$db_prefix}ban_items
			WHERE id_ban IN (" . implode(', ', $_POST['ban_items']) . ")
				AND id_ban_group = $_REQUEST[bg]", __FILE__, __LINE__);

		// It changed, let the settings and the member table know.
		updateSettings(array('banLastUpdated' => time()));
		updateBanMembers();
	}

	// Modify OR add a ban.
	elseif (!empty($_POST['modify_ban']) || !empty($_POST['add_ban']))
	{
		checkSession();

		$addBan = !empty($_POST['add_ban']);
		if (empty($_POST['ban_name']))
			fatal_lang_error('ban_name_empty', false);
		// Check whether a ban with this name already exists.
		$request = $smfFunc['db_query']('', "
			SELECT id_ban_group
			FROM {$db_prefix}ban_groups
			WHERE name = '$_POST[ban_name]'" . ($addBan ? '' : "
				AND id_ban_group != $_REQUEST[bg]") . "
			LIMIT 1", __FILE__, __LINE__);
		// !!! Separate the sprintf?
		if ($smfFunc['db_num_rows']($request) == 1)
			fatal_lang_error('ban_name_exists', false, array($_POST['ban_name']));
		$smfFunc['db_free_result']($request);

		$_POST['reason'] = $smfFunc['db_escape_string'](htmlspecialchars($smfFunc['db_unescape_string']($_POST['reason']), ENT_QUOTES));
		$_POST['notes'] = $smfFunc['db_escape_string'](htmlspecialchars($smfFunc['db_unescape_string']($_POST['notes']), ENT_QUOTES));
		$_POST['notes'] = str_replace(array("\r", "\n", '  '), array('', '<br />', '&nbsp; '), $_POST['notes']);
		$_POST['expiration'] = $_POST['expiration'] == 'never' ? 'NULL' : ($_POST['expiration'] == 'expired' ? '0' : ($_POST['expire_date'] != $_POST['old_expire'] ? time() + 24 * 60 * 60 * (int) $_POST['expire_date'] : 'expire_time'));
		$_POST['full_ban'] = empty($_POST['full_ban']) ? '0' : '1';
		$_POST['cannot_post'] = !empty($_POST['full_ban']) || empty($_POST['cannot_post']) ? '0' : '1';
		$_POST['cannot_register'] = !empty($_POST['full_ban']) || empty($_POST['cannot_register']) ? '0' : '1';
		$_POST['cannot_login'] = !empty($_POST['full_ban']) || empty($_POST['cannot_login']) ? '0' : '1';

		if ($addBan)
		{
			// Adding some ban triggers?
			if ($addBan && !empty($_POST['ban_suggestion']) && is_array($_POST['ban_suggestion']))
			{
				$ban_triggers = array();
				if (in_array('main_ip', $_POST['ban_suggestion']) && !empty($_POST['main_ip']))
				{
					$ip_parts = ip2range($_POST['main_ip']);
					if (count($ip_parts) != 4)
						fatal_lang_error('invalid_ip', false);

						$ban_triggers[] = $ip_parts[0]['low'] . ', ' . $ip_parts[0]['high'] . ', ' . $ip_parts[1]['low'] . ', ' . $ip_parts[1]['high'] . ', ' . $ip_parts[2]['low'] . ', ' . $ip_parts[2]['high'] . ', ' . $ip_parts[3]['low'] . ', ' . $ip_parts[3]['high'] . ", '', '', 0";
				}
				if (in_array('hostname', $_POST['ban_suggestion']) && !empty($_POST['hostname']))
				{
					if (preg_match("/[^\w.\-*]/", $_POST['hostname']) == 1)
						fatal_lang_error('invalid_hostname', false);

					// Replace the * wildcard by a MySQL wildcard %.
					$_POST['hostname'] = str_replace('*', '%', $_POST['hostname']);

					$ban_triggers[] = "0, 0, 0, 0, 0, 0, 0, 0, '" . substr($_POST['hostname'], 0, 255) . "', '', 0";
				}
				if (in_array('email', $_POST['ban_suggestion']) && !empty($_POST['email']))
				{
					if (preg_match("/[^\w.\-*@]/", $_POST['email']) == 1)
						fatal_lang_error('invalid_email', false);
					$_POST['email'] = strtolower(str_replace('*', '%', $_POST['email']));

					$ban_triggers[] = "0, 0, 0, 0, 0, 0, 0, 0, '', '".substr($_POST['email'], 0, 255)."', 0";
				}
				if (in_array('user', $_POST['ban_suggestion']) && (!empty($_POST['bannedUser']) || !empty($_POST['user'])))
				{
					// We got a username, let's find its ID.
					if (empty($_POST['bannedUser']))
					{
						$_POST['user'] = preg_replace('~&amp;#(\d{4,5}|[2-9]\d{2,4}|1[2-9]\d);~', '&#$1;', $smfFunc['db_escape_string'](htmlspecialchars($smfFunc['db_unescape_string']($_POST['user']), ENT_QUOTES)));

						$request = $smfFunc['db_query']('', "
							SELECT id_member, (id_group = 1 OR FIND_IN_SET(1, additional_groups)) AS isAdmin
							FROM {$db_prefix}members
							WHERE member_name = '$_POST[user]' OR real_name = '$_POST[user]'
							LIMIT 1", __FILE__, __LINE__);
						if ($smfFunc['db_num_rows']($request) == 0)
							fatal_lang_error('invalid_username', false);
						list ($_POST['bannedUser'], $isAdmin) = $smfFunc['db_fetch_row']($request);
						$smfFunc['db_free_result']($request);

						if ($isAdmin)
							fatal_lang_error('no_ban_admin', 'critical');
					}

					$ban_triggers[] = "0, 0, 0, 0, 0, 0, 0, 0, '', '', " . (int) $_POST['bannedUser'];
				}

				if (!empty($_POST['ban_suggestion']['ips']) && is_array($_POST['ban_suggestion']['ips']))
				{
					$_POST['ban_suggestion']['ips'] = array_unique($_POST['ban_suggestion']['ips']);

					// Don't add the main IP again.
					if (in_array('main_ip', $_POST['ban_suggestion']))
						$_POST['ban_suggestion']['ips'] = array_diff($_POST['ban_suggestion']['ips'], array($_POST['main_ip']));
					foreach ($_POST['ban_suggestion']['ips'] as $ip)
					{
						$ip_parts = ip2range($ip);

						// They should be alright, but just to be sure...
						if (count($ip_parts) != 4)
							fatal_lang_error('invalid_ip', false);

						$ban_triggers[] = $ip_parts[0]['low'] . ', ' . $ip_parts[0]['high'] . ', ' . $ip_parts[1]['low'] . ', ' . $ip_parts[1]['high'] . ', ' . $ip_parts[2]['low'] . ', ' . $ip_parts[2]['high'] . ', ' . $ip_parts[3]['low'] . ', ' . $ip_parts[3]['high'] . ", '', '', 0";
					}
				}
			}

			// Yes yes, we're ready to add now.
			$smfFunc['db_query']('', "
				INSERT INTO {$db_prefix}ban_groups
					(name, ban_time, expire_time, cannot_access, cannot_register, cannot_post, cannot_login, reason, notes)
				VALUES
					(SUBSTRING('$_POST[ban_name]', 1, 20), " . time() . ", $_POST[expiration], $_POST[full_ban], $_POST[cannot_register], $_POST[cannot_post], $_POST[cannot_login], SUBSTRING('$_POST[reason]', 1, 255), SUBSTRING('$_POST[notes]', 1, 65534))", __FILE__, __LINE__);
			$_REQUEST['bg'] = db_insert_id("{$db_prefix}ban_groups", 'id_ban_group');

			// Now that the ban group is added, add some triggers as well.
			if (!empty($ban_triggers) && !empty($_REQUEST['bg']))
				$smfFunc['db_query']('', "
					INSERT INTO {$db_prefix}ban_items
						(id_ban_group, ip_low1, ip_high1, ip_low2, ip_high2, ip_low3, ip_high3, ip_low4, ip_high4, hostname, email_address, id_member)
					VALUES ($_REQUEST[bg], " . implode("), ($_REQUEST[bg], ", $ban_triggers) . ')', __FILE__, __LINE__);
		}
		else
			$smfFunc['db_query']('', "
				UPDATE {$db_prefix}ban_groups
				SET
					name = '$_POST[ban_name]',
					reason = '$_POST[reason]',
					notes = '$_POST[notes]',
					expire_time = $_POST[expiration],
					cannot_access = $_POST[full_ban],
					cannot_post = $_POST[cannot_post],
					cannot_register = $_POST[cannot_register],
					cannot_login = $_POST[cannot_login]
				WHERE id_ban_group = $_REQUEST[bg]", __FILE__, __LINE__);

		// No more caching, we have something new here.
		updateSettings(array('banLastUpdated' => time()));
		updateBanMembers();
	}

	// If we're editing an existing ban, get it from the database.
	if (!empty($_REQUEST['bg']))
	{
		$context['ban_items'] = array();
		$request = $smfFunc['db_query']('', "
			SELECT
				bi.id_ban, bi.hostname, bi.email_address, bi.id_member, bi.hits,
				bi.ip_low1, bi.ip_high1, bi.ip_low2, bi.ip_high2, bi.ip_low3, bi.ip_high3, bi.ip_low4, bi.ip_high4,
				bg.id_ban_group, bg.name, bg.ban_time, bg.expire_time, bg.reason, bg.notes, bg.cannot_access, bg.cannot_register, bg.cannot_login, bg.cannot_post,
				IFNULL(mem.id_member, 0) AS id_member, mem.member_name, mem.real_name
			FROM {$db_prefix}ban_groups AS bg
				LEFT JOIN {$db_prefix}ban_items AS bi ON (bi.id_ban_group = bg.id_ban_group)
				LEFT JOIN {$db_prefix}members AS mem ON (mem.id_member = bi.id_member)
			WHERE bg.id_ban_group = $_REQUEST[bg]", __FILE__, __LINE__);
		if ($smfFunc['db_num_rows']($request) == 0)
			fatal_lang_error('ban_not_found', false);
		while ($row = $smfFunc['db_fetch_assoc']($request))
		{
			if (!isset($context['ban']))
			{
				$context['ban'] = array(
					'id' => $row['id_ban_group'],
					'name' => $row['name'],
					'expiration' => array(
						'status' => $row['expire_time'] === null ? 'never' : ($row['expire_time'] < time() ? 'expired' : 'still_active_but_we_re_counting_the_days'),
						'days' => $row['expire_time'] > time() ? floor(($row['expire_time'] - time()) / 86400) : 0
					),
					'reason' => $row['reason'],
					'notes' => $row['notes'],
					'cannot' => array(
						'access' => !empty($row['cannot_access']),
						'post' => !empty($row['cannot_post']),
						'register' => !empty($row['cannot_register']),
						'login' => !empty($row['cannot_login']),
					),
					'is_new' => false,
				);
			}
			if (!empty($row['id_ban']))
			{
				$context['ban_items'][$row['id_ban']] = array(
					'id' => $row['id_ban'],
					'hits' => $row['hits'],
				);
				if (!empty($row['ip_high1']))
				{
					$context['ban_items'][$row['id_ban']]['type'] = 'ip';
					$context['ban_items'][$row['id_ban']]['ip'] = range2ip(array($row['ip_low1'], $row['ip_low2'], $row['ip_low3'], $row['ip_low4']), array($row['ip_high1'], $row['ip_high2'], $row['ip_high3'], $row['ip_high4']));
				}
				elseif (!empty($row['hostname']))
				{
					$context['ban_items'][$row['id_ban']]['type'] = 'hostname';
					$context['ban_items'][$row['id_ban']]['hostname'] = str_replace('%', '*', $row['hostname']);
				}
				elseif (!empty($row['email_address']))
				{
					$context['ban_items'][$row['id_ban']]['type'] = 'email';
					$context['ban_items'][$row['id_ban']]['email'] = str_replace('%', '*', $row['email_address']);
				}
				elseif (!empty($row['id_member']))
				{
					$context['ban_items'][$row['id_ban']]['type'] = 'user';
					$context['ban_items'][$row['id_ban']]['user'] = array(
						'id' => $row['id_member'],
						'name' => $row['real_name'],
						'href' => $scripturl . '?action=profile;u=' . $row['id_member'],
						'link' => '<a href="' . $scripturl . '?action=profile;u=' . $row['id_member'] . '">' . $row['real_name'] . '</a>',
					);
				}
				// Invalid ban (member probably doesn't exist anymore).
				else
				{
					unset($context['ban_items'][$row['id_ban']]);
					$smfFunc['db_query']('', "
						DELETE FROM {$db_prefix}ban_items
						WHERE id_ban = $row[id_ban]", __FILE__, __LINE__);
				}
			}
		}
		$smfFunc['db_free_result']($request);
	}
	// Not an existing one, then it's probably a new one.
	else
	{
		$context['ban'] = array(
			'id' => 0,
			'name' => '',
			'expiration' => array(
				'status' => 'never',
				'days' => 0
			),
			'reason' => '',
			'notes' => '',
			'ban_days' => 0,
			'cannot' => array(
				'access' => true,
				'post' => false,
				'register' => false,
				'login' => false,
			),
			'is_new' => true,
		);
		$context['ban_suggestions'] = array(
			'main_ip' => '',
			'hostname' => '',
			'email' => '',
			'member' => array(
				'id' => 0,
			),
		);

		// Overwrite some of the default form values if a user ID was given.
		if (!empty($_REQUEST['u']))
		{
			$request = $smfFunc['db_query']('', "
				SELECT id_member, real_name, member_ip, email_address
				FROM {$db_prefix}members
				WHERE id_member = " . (int) $_REQUEST['u'] . "
				LIMIT 1", __FILE__, __LINE__);
			if ($smfFunc['db_num_rows']($request) > 0)
			{
				list ($context['ban_suggestions']['member']['id'], $context['ban_suggestions']['member']['name'], $context['ban_suggestions']['main_ip'], $context['ban_suggestions']['email']) = $smfFunc['db_fetch_row']($request);
			}
			$smfFunc['db_free_result']($request);

			if (!empty($context['ban_suggestions']['member']['id']))
			{
				$context['ban_suggestions']['href'] = $scripturl . '?action=profile;u=' . $context['ban_suggestions']['member']['id'];
				$context['ban_suggestions']['member']['link'] = '<a href="' . $context['ban_suggestions']['href'] . '">' . $context['ban_suggestions']['member']['name'] . '</a>';

				// Default the ban name to the name of the banned member.
				$context['ban']['name'] = $context['ban_suggestions']['member']['name'];

				// Would be nice if we could also ban the hostname.
				if (preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/', $context['ban_suggestions']['main_ip']) == 1 && empty($modSettings['disableHostnameLookup']))
					$context['ban_suggestions']['hostname'] = host_from_ip($context['ban_suggestions']['main_ip']);

				// Find some additional IP's used by this member.
				$context['ban_suggestions']['message_ips'] = array();
				$request = $smfFunc['db_query']('', "
					SELECT DISTINCT poster_ip
					FROM {$db_prefix}messages
					WHERE id_member = " . (int) $_REQUEST['u'] . "
						AND poster_ip RLIKE '^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$'
					ORDER BY poster_ip", __FILE__, __LINE__);
				while ($row = $smfFunc['db_fetch_assoc']($request))
					$context['ban_suggestions']['message_ips'][] = $row['poster_ip'];
				$smfFunc['db_free_result']($request);

				$context['ban_suggestions']['error_ips'] = array();
				$request = $smfFunc['db_query']('', "
					SELECT DISTINCT ip
					FROM {$db_prefix}log_errors
					WHERE id_member = " . (int) $_REQUEST['u'] . "
						AND ip RLIKE '^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$'
					ORDER BY ip", __FILE__, __LINE__);
				while ($row = $smfFunc['db_fetch_assoc']($request))
					$context['ban_suggestions']['error_ips'][] = $row['ip'];
				$smfFunc['db_free_result']($request);

				// Borrowing a few language strings from profile.
				loadLanguage('Profile');
			}
		}
	}

	// Template needs this to show errors using javascript
	loadLanguage('Errors');

	$context['sub_template'] = 'ban_edit';
}

function BanEditTrigger()
{
	global $context, $db_prefix, $smfFunc;

	$context['sub_template'] = 'ban_edit_trigger';

	if (empty($_REQUEST['bg']))
		fatal_lang_error('ban_not_found', false);

	if (empty($_REQUEST['bi']))
	{
		$context['ban_trigger'] = array(
			'id' => 0,
			'group' => (int) $_REQUEST['bg'],
			'ip' => array(
				'value' => '',
				'selected' => true,
			),
			'hostname' => array(
				'selected' => false,
				'value' => '',
			),
			'email' => array(
				'value' => '',
				'selected' => false,
			),
			'banneduser' => array(
				'value' => '',
				'selected' => false,
			),
			'is_new' => true,
		);
	}
	else
	{
		$request = $smfFunc['db_query']('', "
			SELECT
				bi.id_ban, bi.id_ban_group, bi.hostname, bi.email_address, bi.id_member,
				bi.ip_low1, bi.ip_high1, bi.ip_low2, bi.ip_high2, bi.ip_low3, bi.ip_high3, bi.ip_low4, bi.ip_high4,
				mem.member_name, mem.real_name
			FROM {$db_prefix}ban_items AS bi
				LEFT JOIN {$db_prefix}members AS mem ON (mem.id_member = bi.id_member)
			WHERE id_ban = " . (int) $_REQUEST['bi'] . "
				AND id_ban_group = " . (int) $_REQUEST['bg'] . "
			LIMIT 1", __FILE__, __LINE__);
		if ($smfFunc['db_num_rows']($request) == 0)
			fatal_lang_error('ban_not_found', false);
		$row = $smfFunc['db_fetch_assoc']($request);
		$smfFunc['db_free_result']($request);

		$context['ban_trigger'] = array(
			'id' => $row['id_ban'],
			'group' => $row['id_ban_group'],
			'ip' => array(
				'value' => empty($row['ip_low1']) ? '' : range2ip(array($row['ip_low1'], $row['ip_low2'], $row['ip_low3'], $row['ip_low4']), array($row['ip_high1'], $row['ip_high2'], $row['ip_high3'], $row['ip_high4'])),
				'selected' => !empty($row['ip_low1']),
			),
			'hostname' => array(
				'value' =>  str_replace('%', '*', $row['hostname']),
				'selected' => !empty($row['hostname']),
			),
			'email' => array(
				'value' => str_replace('%', '*', $row['email_address']),
				'selected' => !empty($row['email_address'])
			),
			'banneduser' => array(
				'value' => $row['member_name'],
				'selected' => !empty($row['member_name'])
			),
			'is_new' => false,
		);
	}
}

function BanBrowseTriggers()
{
	global $db_prefix, $modSettings, $context, $scripturl, $smfFunc;

	if (!empty($_POST['remove_triggers']) && !empty($_POST['remove']) && is_array($_POST['remove']))
	{
		checkSession();

		// Clean the integers.
		foreach ($_POST['remove'] as $key => $value)
			$_POST['remove'][$key] = $value;

		$smfFunc['db_query']('', "
			DELETE FROM {$db_prefix}ban_items
			WHERE id_ban IN (" . implode(', ', $_POST['remove']) . ")", __FILE__, __LINE__);

		// Rehabilitate some members.
		if ($_REQUEST['entity'] == 'member')
			updateBanMembers();

		// Make sure the ban cache is refreshed.
		updateSettings(array('banLastUpdated' => time()));
	}

	$query = array(
		'ip' => array(
			'select' => 'bi.ip_low1, bi.ip_high1, bi.ip_low2, bi.ip_high2, bi.ip_low3, bi.ip_high3, bi.ip_low4, bi.ip_high4',
			'where' => 'bi.ip_low1 > 0',
			'orderby' => 'bi.ip_low1, bi.ip_high1, bi.ip_low2, bi.ip_high2, bi.ip_low3, bi.ip_high3, bi.ip_low4, bi.ip_high4',
		),
		'hostname' => array(
			'select' => 'bi.hostname',
			'where' => "bi.hostname != ''",
			'orderby' => 'bi.hostname',
		),
		'email' => array(
			'select' => 'bi.email_address',
			'where' => "bi.email_address != ''",
			'orderby' => 'bi.email_address',
		),
		'member' => array(
			'select' => 'mem.id_member, mem.real_name',
			'where' => 'mem.id_member = bi.id_member',
			'orderby' => 'mem.real_name',
		)
	);

	$context['selected_entity'] = isset($_REQUEST['entity']) && isset($query[$_REQUEST['entity']]) ? $_REQUEST['entity'] : 'ip';

	$request = $smfFunc['db_query']('', "
		SELECT COUNT(*)
		FROM {$db_prefix}ban_items AS bi" . ($context['selected_entity'] == 'member' ? "
			INNER JOIN {$db_prefix}members AS mem ON (" . $query[$context['selected_entity']]['where'] . ")" : "
		WHERE " . $query[$context['selected_entity']]['where']), __FILE__, __LINE__);
	list ($num_items) = $smfFunc['db_fetch_row']($request);
	$smfFunc['db_free_result']($request);

	$context['page_index'] = constructPageIndex($scripturl . '?action=admin;area=ban;sa=browse;entity=' . $context['selected_entity'], $_REQUEST['start'], $num_items, $modSettings['defaultMaxMessages']);
	$context['start'] = $_REQUEST['start'];
	$context['ban_items'] = array();

	if (!empty($num_items))
	{
		$request = $smfFunc['db_query']('', "
			SELECT bi.id_ban, " . $query[$context['selected_entity']]['select'] . ", bi.hits, bg.id_ban_group, bg.name
			FROM {$db_prefix}ban_items AS bi
				INNER JOIN {$db_prefix}ban_groups AS bg ON (bg.id_ban_group = bi.id_ban_group)" . ($context['selected_entity'] == 'member' ? "
				INNER JOIN {$db_prefix}members AS mem ON (" . $query[$context['selected_entity']]['where'] . ")" : "
			WHERE " . $query[$context['selected_entity']]['where']) . "
			ORDER BY " . $query[$context['selected_entity']]['orderby'] . "
			LIMIT $context[start], $modSettings[defaultMaxMessages]", __FILE__, __LINE__);
		while ($row = $smfFunc['db_fetch_assoc']($request))
		{
			$context['ban_items'][$row['id_ban']] = array(
				'id' => $row['id_ban'],
				'hits' => $row['hits'],
				'group' => array(
					'id' => $row['id_ban_group'],
					'name' => $row['name'],
					'href' => $scripturl . '?action=admin;area=ban;sa=edit;bg=' . $row['id_ban_group'],
					'link' => '<a href="' . $scripturl . '?action=admin;area=ban;sa=edit;bg=' . $row['id_ban_group'] . '">' . $row['name'] . '</a>',
				)
			);
			if ($context['selected_entity'] == 'ip')
				$context['ban_items'][$row['id_ban']]['entity'] = range2ip(array($row['ip_low1'], $row['ip_low2'], $row['ip_low3'], $row['ip_low4']), array($row['ip_high1'], $row['ip_high2'], $row['ip_high3'], $row['ip_high4']));
			elseif ($context['selected_entity'] == 'hostname')
				$context['ban_items'][$row['id_ban']]['entity'] = str_replace('%', '*', $row['hostname']);
			elseif ($context['selected_entity'] == 'email')
				$context['ban_items'][$row['id_ban']]['entity'] = str_replace('%', '*', $row['email_address']);
			else
			{
				$context['ban_items'][$row['id_ban']]['member'] = array(
					'id' => $row['id_member'],
					'name' => $row['real_name'],
					'href' => $scripturl . '?action=profile;u=' . $row['id_member'],
					'link' => '<a href="' . $scripturl . '?action=profile;u=' . $row['id_member'] . '">' . $row['real_name'] . '</a>',
				);
				$context['ban_items'][$row['id_ban']]['entity'] = $context['ban_items'][$row['id_ban']]['member']['link'];
			}
		}
		$smfFunc['db_free_result']($request);
	}
	$context['sub_template'] = 'browse_triggers';
}

function BanLog()
{
	global $db_prefix, $scripturl, $context, $smfFunc;

	$sort_columns = array(
		'name' => 'mem.real_name',
		'ip' => 'lb.ip',
		'email' => 'lb.email',
		'date' => 'lb.log_time',
	);

	// The number of entries to show per page of the ban log.
	$entries_per_page = 30;

	// Delete one or more entries.
	if (!empty($_POST['removeAll']) || (!empty($_POST['removeSelected']) && !empty($_POST['remove'])))
	{
		checkSession();

		// 'Delete all entries' button was pressed.
		if (!empty($_POST['removeAll']))
			$smfFunc['db_query']('', "
				TRUNCATE {$db_prefix}log_banned", __FILE__, __LINE__);

		// 'Delte selection' button was pressed.
		else
		{
			// Make sure every entry is integer.
			foreach ($_POST['remove'] as $index => $log_id)
				$_POST['remove'][$index] = (int) $log_id;

			$smfFunc['db_query']('', "
				DELETE FROM {$db_prefix}log_banned
				WHERE id_ban_log IN (" . implode(', ', $_POST['remove']) . ')', __FILE__, __LINE__);
		}
	}

	// Count the total number of log entries.
	$request = $smfFunc['db_query']('', "
		SELECT COUNT(*)
		FROM {$db_prefix}log_banned", __FILE__, __LINE__);
	list ($num_ban_log_entries) = $smfFunc['db_fetch_row']($request);
	$smfFunc['db_free_result']($request);

	// Set start if not already set.
	$_REQUEST['start'] = empty($_REQUEST['start']) || $_REQUEST['start'] < 0 ? 0 : (int) $_REQUEST['start'];

	// Default to newest entries first.
	if (empty($_REQUEST['sort']) || !isset($sort_columns[$_REQUEST['sort']]))
	{
		$_REQUEST['sort'] = 'date';
		$_REQUEST['desc'] = true;
	}

	$context['sort_direction'] = isset($_REQUEST['desc']) ? 'down' : 'up';
	$context['sort'] = $_REQUEST['sort'];
	$context['page_index'] = constructPageIndex($scripturl . '?action=admin;area=ban;sa=log;sort=' . $context['sort'] . ($context['sort_direction'] == 'down' ? ';desc' : ''), $_REQUEST['start'], $num_ban_log_entries, $entries_per_page);
	$context['start'] = $_REQUEST['start'];

	$request = $smfFunc['db_query']('', "
		SELECT lb.id_ban_log, lb.id_member, IFNULL(lb.ip, '-') AS ip, IFNULL(lb.email, '-') AS email, lb.log_time, IFNULL(mem.real_name, '') AS real_name
		FROM {$db_prefix}log_banned AS lb
			LEFT JOIN {$db_prefix}members AS mem ON (mem.id_member = lb.id_member)
		ORDER BY " . $sort_columns[$context['sort']] . (isset($_REQUEST['desc']) ? ' DESC' : '') . "
		LIMIT $_REQUEST[start], $entries_per_page", __FILE__, __LINE__);
	$context['log_entries'] = array();
	while ($row = $smfFunc['db_fetch_assoc']($request))
		$context['log_entries'][] = array(
			'id' => $row['id_ban_log'],
			'member' => array(
				'id' => $row['id_member'],
				'name' => $row['real_name'],
				'href' => $scripturl . '?action=profile;u=' . $row['id_member'],
				'link' => '<a href="' . $scripturl . '?action=profile;u=' . $row['id_member'] . '">' . $row['real_name'] . '</a>',
			),
			'ip' => $row['ip'],
			'email' => $row['email'],
			'date' => timeformat($row['log_time']),
		);
	$smfFunc['db_free_result']($request);

	$context['sub_template'] = 'ban_log';
}

function range2ip($low, $high)
{
	if (count($low) != 4 || count($high) != 4)
		return '';

	$ip = array();
	for ($i = 0; $i < 4; $i++)
	{
		if ($low[$i] == $high[$i])
			$ip[$i] = $low[$i];
		elseif ($low[$i] == '0' && $high[$i] == '255')
			$ip[$i] = '*';
		else
			$ip[$i] = $low[$i] . '-' . $high[$i];
	}

	// Pretending is fun... the IP can't be this, so use it for 'unknown'.
	if ($ip == array(255, 255, 255, 255))
		return 'unknown';

	return implode('.', $ip);
}

// Convert a single IP to a ranged IP.
function ip2range($fullip)
{
	// Pretend that 'unknown' is 255.255.255.255. (since that can't be an IP anyway.)
	if ($fullip == 'unknown')
		$fullip = '255.255.255.255';

	$ip_parts = explode('.', $fullip);
	$ip_array = array();

	if (count($ip_parts) != 4)
		return array();

	for ($i = 0; $i < 4; $i++)
	{
		if ($ip_parts[$i] == '*')
			$ip_array[$i] = array('low' => '0', 'high' => '255');
		elseif (preg_match('/^(\d{1,3})\-(\d{1,3})$/', $ip_parts[$i], $range) == 1)
			$ip_array[$i] = array('low' => $range[1], 'high' => $range[2]);
		elseif (is_numeric($ip_parts[$i]))
			$ip_array[$i] = array('low' => $ip_parts[$i], 'high' => $ip_parts[$i]);
	}

	return $ip_array;
}

function updateBanMembers()
{
	global $db_prefix, $smfFunc;

	$updates = array();
	$newMembers = array();

	// Find members that haven't been marked as 'banned'...yet.
	$request = $smfFunc['db_query']('', "
		SELECT mem.id_member, mem.is_activated + 10 AS new_value
		FROM {$db_prefix}ban_groups AS bg
			INNER JOIN {$db_prefix}ban_items AS bi ON (bi.id_ban_group = bg.id_ban_group)
			INNER JOIN {$db_prefix}members AS mem ON ((mem.id_member = bi.id_member OR mem.email_address LIKE bi.email_address)
				AND mem.is_activated < 10)
		WHERE bg.cannot_access = 1
			AND (bg.expire_time IS NULL OR bg.expire_time > " . time() . ")", __FILE__, __LINE__);
	while ($row = $smfFunc['db_fetch_assoc']($request))
	{
		$updates[$row['new_value']][] = $row['id_member'];
		$newMembers[] = $row['id_member'];
	}
	$smfFunc['db_free_result']($request);

	// We welcome our new members in the realm of the banned.
	if (!empty($newMembers))
		$smfFunc['db_query']('', "
			DELETE FROM {$db_prefix}log_online
			WHERE id_member IN (" . implode(', ', $newMembers) . ")", __FILE__, __LINE__);

	// Find members that are wrongfully marked as banned.
	$request = $smfFunc['db_query']('', "
		SELECT mem.id_member, mem.is_activated - 10 AS new_value
		FROM {$db_prefix}members AS mem
			LEFT JOIN {$db_prefix}ban_items AS bi ON (bi.id_member = mem.id_member OR mem.email_address LIKE bi.email_address)
			LEFT JOIN {$db_prefix}ban_groups AS bg ON (bg.id_ban_group = bi.id_ban_group AND bg.cannot_access = 1 AND (bg.expire_time IS NULL OR bg.expire_time > " . time() . "))
		WHERE (bi.id_ban IS NULL OR bg.id_ban_group IS NULL)
			AND mem.is_activated >= 10", __FILE__, __LINE__);
	while ($row = $smfFunc['db_fetch_assoc']($request))
		$updates[$row['new_value']][] = $row['id_member'];
	$smfFunc['db_free_result']($request);

	if (!empty($updates))
		foreach ($updates as $newStatus => $members)
			updateMemberData($members, array('is_activated' => $newStatus));
}

?>