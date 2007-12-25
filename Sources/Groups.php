<?php
/**********************************************************************************
* Groups.php                                                                      *
***********************************************************************************
* SMF: Simple Machines Forum                                                      *
* Open-Source Project Inspired by Zef Hemel (zef@zefhemel.com)                    *
* =============================================================================== *
* Software Version:           SMF 2.0 Beta 1.1                                    *
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

/* This file currently just shows group info, and allows certain privaledged members to add/remove members.

	void Groups()
		- allows moderators and users to access the group showing functions.
		- handles permission checks, and puts the moderation bar on as required.

	void MembergroupMembers()
		- can be called from ManageMembergroups if it needs templating within the admin environment.
		- show a list of members that are part of a given membergroup.
		- called by ?action=moderate;area=viewgroups;sa=members;group=x
		- requires the manage_membergroups permission.
		- uses the group_members sub template of ManageMembergroups.
		- allows to add and remove members from the selected membergroup.
		- allows sorting on several columns.
		- redirects to itself.

	int list_getGroupRequestCount(string where)
		// !!!

	array list_getGroupRequests(int start, int items_per_page, string sort, string where)
		// !!!

*/

// Entry point, permission checks, admin bars, etc.
function Groups()
{
	global $context, $txt, $scripturl, $sourcedir, $user_info;

	// The sub-actions that we can do. Format "Function Name, Mod Bar Index if appropriate".
	$subActions = array(
		'index' => array('GroupList', 'view_groups'),
		'members' => array('MembergroupMembers', 'view_groups'),
		'requests' => array('GroupRequests', 'group_requests'),
	);

	// Default to sub action 'index' or 'settings' depending on permissions.
	$_REQUEST['sa'] = isset($_REQUEST['sa']) && isset($subActions[$_REQUEST['sa']]) ? $_REQUEST['sa'] : 'index';

	// If we can see the moderation center, and this has a mod bar entry, add the mod center bar.
	if (allowedTo('access_mod_center') || $user_info['mod_cache']['bq'] != '0=1' || $user_info['mod_cache']['gq'] != '0=1' || allowedTo('manage_membergroups'))
	{
		require_once($sourcedir . '/ModerationCenter.php');
		$_GET['area'] = $_REQUEST['sa'] == 'requests' ? 'groups' : 'viewgroups';
		ModerationMain(true);
	}

	// Get the template stuff up and running.
	loadLanguage('ManageMembers');
	loadLanguage('ModerationCenter');
	loadTemplate('ManageMembergroups');

	// Add something to the link tree, for normal people.
	$context['linktree'][] = array(
		'url' => $scripturl . '?action=groups',
		'name' => $txt['groups'],
	);

	// Call the actual function.
	$subActions[$_REQUEST['sa']][0]();
}

// This very simply lists the groups, nothing snazy.
function GroupList()
{
	global $txt, $db_prefix, $scripturl, $user_profile, $user_info, $context, $settings, $modSettings, $smfFunc;

	// Yep, find the groups...
	$request = $smfFunc['db_query']('', '
		SELECT mg.id_group, mg.group_name, mg.description, mg.group_type, mg.online_color, mg.hidden,
			mg.stars, IFNULL(gm.id_member, 0) AS can_moderate
		FROM {db_prefix}membergroups AS mg
			LEFT JOIN {db_prefix}group_moderators AS gm ON (gm.id_group = mg.id_group AND gm.id_member = {int:current_member})
		WHERE mg.min_posts = {int:inject_int_1}
			AND mg.id_group != {int:inject_int_2}
		ORDER BY group_name',
		array(
			'current_member' => $user_info['id'],
			'inject_int_1' => -1,
			'inject_int_2' => 3,
		)
	);
	// This is where we store our groups.
	$context['groups'] = array();
	$group_ids = array();
	$context['can_moderate'] = allowedTo('manage_membergroups');
	while ($row = $smfFunc['db_fetch_assoc']($request))
	{
		// We only list the groups they can see.
		if ($row['hidden'] && !$row['can_moderate'] && !allowedTo('manage_membergroups'))
			continue;

		$row['stars'] = explode('#', $row['stars']);

		$context['groups'][$row['id_group']] = array(
			'id' => $row['id_group'],
			'name' => $row['group_name'],
			'desc' => $row['description'],
			'color' => $row['online_color'],
			'type' => $row['group_type'],
			'num_members' => 0,
			'stars' => !empty($row['stars'][0]) && !empty($row['stars'][1]) ? str_repeat('<img src="' . $settings['images_url'] . '/' . $row['stars'][1] . '" alt="*" border="0" />', $row['stars'][0]) : '',
		);

		$context['can_moderate'] |= $row['can_moderate'];
		$group_ids[] = $row['id_group'];
	}
	$smfFunc['db_free_result']($request);

	// Count up the members separately...
	if (!empty($group_ids))
	{
		$query = $smfFunc['db_query']('', '
			SELECT id_group, COUNT(*) AS num_members
			FROM {db_prefix}members
			WHERE id_group IN ({array_int:inject_array_int_1})
			GROUP BY id_group',
			array(
				'inject_array_int_1' => $group_ids,
			)
		);
		while ($row = $smfFunc['db_fetch_assoc']($query))
			$context['groups'][$row['id_group']]['num_members'] += $row['num_members'];
		$smfFunc['db_free_result']($query);

		// Only do additional groups if we can moderate...
		if ($context['can_moderate'])
		{
			$query = $smfFunc['db_query']('', '
				SELECT mg.id_group, COUNT(*) AS num_members
				FROM {db_prefix}membergroups AS mg
					INNER JOIN {db_prefix}members AS mem ON (mem.additional_groups != {string:inject_string_1}
						AND mem.id_group != mg.id_group
						AND FIND_IN_SET(mg.id_group, mem.additional_groups))
				WHERE mg.id_group IN ({array_int:inject_array_int_1})
				GROUP BY mg.id_group',
				array(
					'inject_array_int_1' => $group_ids,
					'inject_string_1' => '',
				)
			);
			while ($row = $smfFunc['db_fetch_assoc']($query))
				$context['groups'][$row['id_group']]['num_members'] += $row['num_members'];
			$smfFunc['db_free_result']($query);
		}
	}

	$context['sub_template'] = 'group_index';
	$context['page_title'] = $txt['viewing_groups'];
}

// Display members of a group, and allow adding of members to a group. Silly function name though ;)
function MembergroupMembers()
{
	global $txt, $scripturl, $db_prefix, $context, $modSettings, $sourcedir, $user_info, $settings, $smfFunc;

	$_REQUEST['group'] = isset($_REQUEST['group']) ? (int) $_REQUEST['group'] : 0;

	// No browsing of guests, membergroup 0 or moderators.
	if (in_array($_REQUEST['group'], array(-1, 0, 3)))
		fatal_lang_error('membergroup_does_not_exist', false);

	// Load up the group details.
	$request = $smfFunc['db_query']('', '
		SELECT id_group AS id, group_name AS name, min_posts = {int:inject_int_1} AS assignable, hidden, online_color,
			stars, description, min_posts != {int:inject_int_1} AS is_post_group
		FROM {db_prefix}membergroups
		WHERE id_group = {int:inject_int_2}
		LIMIT 1',
		array(
			'inject_int_1' => -1,
			'inject_int_2' => $_REQUEST['group'],
		)
	);
	// Doesn't exist?
	if ($smfFunc['db_num_rows']($request) == 0)
		fatal_lang_error('membergroup_does_not_exist', false);
	$context['group'] = $smfFunc['db_fetch_assoc']($request);
	$smfFunc['db_free_result']($request);

	// Fix the stars.
	$context['group']['stars'] = explode('#', $context['group']['stars']);
	$context['group']['stars'] = !empty($context['group']['stars'][0]) && !empty($context['group']['stars'][1]) ? str_repeat('<img src="' . $settings['images_url'] . '/' . $context['group']['stars'][1] . '" alt="*" border="0" />', $context['group']['stars'][0]) : '';
	$context['group']['can_moderate'] = allowedTo('manage_membergroups');

	$context['linktree'][] = array(
		'url' => $scripturl . '?action=groups;sa=members;group=' . $context['group']['id'],
		'name' => $context['group']['name'],
	);

	// Load all the group moderators, for fun.
	$request = $smfFunc['db_query']('', '
		SELECT mem.id_member, mem.real_name
		FROM {db_prefix}group_moderators AS mods
			INNER JOIN {db_prefix}members AS mem ON (mem.id_member = mods.id_member)
		WHERE mods.id_group = {int:inject_int_1}',
		array(
			'inject_int_1' => $_REQUEST['group'],
		)
	);
	$context['group']['moderators'] = array();
	while ($row = $smfFunc['db_fetch_assoc']($request))
	{
		$context['group']['moderators'][] = array(
			'id' => $row['id_member'],
			'name' => $row['real_name']
		);

		if ($user_info['id'] == $row['id_member'])
			$context['group']['can_moderate'] = true;
	}
	$smfFunc['db_free_result']($request);

	// If this group is hidden then it can only "exists" if the user can moderate it!
	if ($context['group']['hidden'] && !$context['group']['can_moderate'])
		fatal_lang_error('membergroup_does_not_exist', false);

	// You can only assign membership if you are the moderator and/or can manage groups!
	if (!$context['group']['can_moderate'])
		$context['group']['assignable'] = 0;
	// Non-admins cannot assign admins.
	elseif ($context['group']['id'] == 1 && !allowedTo('admin_forum'))
		$context['group']['assignable'] = 0;

	// Removing member from group?
	if (isset($_POST['remove']) && !empty($_REQUEST['rem']) && is_array($_REQUEST['rem']) && $context['group']['assignable'])
	{
		checkSession();

		// Make sure we're dealing with integers only.
		foreach ($_REQUEST['rem'] as $key => $group)
			$_REQUEST['rem'][$key] = (int) $group;

		require_once($sourcedir . '/Subs-Membergroups.php');
		removeMembersFromGroups($_REQUEST['rem'], $_REQUEST['group'], true);
	}
	// Must be adding new members to the group...
	elseif (isset($_REQUEST['add']) && (!empty($_REQUEST['toAdd']) || !empty($_REQUEST['member_add'])) && $context['group']['assignable'])
	{
		checkSession();

		$member_query = array();

		// Get all the members to be added... taking into account names can be quoted ;)
		$_REQUEST['toAdd'] = strtr($smfFunc['db_escape_string']($smfFunc['htmlspecialchars']($smfFunc['db_unescape_string']($_REQUEST['toAdd']), ENT_QUOTES)), array('&quot;' => '"'));
		preg_match_all('~"([^"]+)"~', $_REQUEST['toAdd'], $matches);
		$member_names = array_unique(array_merge($matches[1], explode(',', preg_replace('~"([^"]+)"~', '', $_REQUEST['toAdd']))));

		foreach ($member_names as $index => $member_name)
		{
			$member_names[$index] = trim($smfFunc['db_escape_string']($smfFunc['strtolower']($member_names[$index])));

			if (strlen($member_names[$index]) == 0)
				unset($member_names[$index]);
		}

		// Any passed by ID?
		$member_ids = array();
		if (!empty($_REQUEST['member_add']))
			foreach ($_REQUEST['member_add'] as $id)
				if ($id > 0)
					$member_ids[] = (int) $id;

		// Construct the query pelements.
		if (!empty($member_ids))
			$member_query[] = 'id_member IN (' . implode(',', $member_ids) . ')';
		if (!empty($member_names))
		{
			$member_query[] = 'LOWER(member_name) IN (\'' . implode('\', \'', $member_names) . '\')';
			$member_query[] = 'LOWER(real_name) IN (\'' . implode('\', \'', $member_names) . '\')';
		}

		$members = array();
		if (!empty($member_query))
		{
			$request = $smfFunc['db_query']('', '
				SELECT id_member
				FROM {db_prefix}members
				WHERE (' . implode(' OR ', $member_query) . ')
					AND id_group != {int:inject_int_1}
					AND NOT FIND_IN_SET({string:inject_string_1}, additional_groups)',
				array(
					'inject_int_1' => $_REQUEST['group'],
					'inject_string_1' => $_REQUEST['group'],
				)
			);
			while ($row = $smfFunc['db_fetch_assoc']($request))
				$members[] = $row['id_member'];
			$smfFunc['db_free_result']($request);
		}

		// !!! Add $_POST['additional'] to templates!

		// Do the updates...
		if (!empty($members))
		{
			require_once($sourcedir . '/Subs-Membergroups.php');
			addMembersToGroup($members, $_REQUEST['group'], isset($_POST['additional']) || $context['group']['hidden'] ? 'only_additional' : 'auto', true);
		}
	}

	// Sort out the sorting!
	$sort_methods = array(
		'name' => 'real_name',
		'email' => allowedTo('moderate_forum') ? 'email_address' : 'hide_email ' . (isset($_REQUEST['desc']) ? 'DESC' : 'ASC') . ', email_address',
		'active' => 'last_login',
		'registered' => 'date_registered',
		'posts' => 'posts',
	);

	// They didn't pick one, default to by name..
	if (!isset($_REQUEST['sort']) || !isset($sort_methods[$_REQUEST['sort']]))
	{
		$context['sort_by'] = 'name';
		$querySort = 'real_name';
	}
	// Otherwise default to ascending.
	else
	{
		$context['sort_by'] = $_REQUEST['sort'];
		$querySort = $sort_methods[$_REQUEST['sort']];
	}

	$context['sort_direction'] = isset($_REQUEST['desc']) ? 'down' : 'up';

	// The where on the query is interesting. Non-moderators should only see people who are in this group as primary.
	if ($context['group']['can_moderate'])
		$where = $context['group']['is_post_group'] ? 'id_post_group = ' . $_REQUEST['group'] : 'id_group = ' . $_REQUEST['group'] . ' OR FIND_IN_SET(' . $_REQUEST['group'] . ', additional_groups)';
	else
		$where = $context['group']['is_post_group'] ? 'id_post_group = ' . $_REQUEST['group'] : 'id_group = ' . $_REQUEST['group'];

	// Count members of the group.
	$request = $smfFunc['db_query']('', '
		SELECT COUNT(*)
		FROM {db_prefix}members
		WHERE ' . $where,
		array(
		)
	);
	list ($context['total_members']) = $smfFunc['db_fetch_row']($request);
	$smfFunc['db_free_result']($request);

	// Create the page index.
	$context['page_index'] = constructPageIndex($scripturl . '?action=moderate;area=viewgroups;sa=members;group=' . $_REQUEST['group'] . ';sort=' . $context['sort_by'] . (isset($_REQUEST['desc']) ? ';desc' : ''), $_REQUEST['start'], $context['total_members'], $modSettings['defaultMaxMembers']);
	$context['start'] = $_REQUEST['start'];
	$context['can_moderate_forum'] = allowedTo('moderate_forum');

	// Load up all members of this group.
	$request = $smfFunc['db_query']('', '
		SELECT id_member, member_name, real_name, email_address, member_ip, date_registered, last_login,
			hide_email, posts, is_activated
		FROM {db_prefix}members
		WHERE ' . $where . '
		ORDER BY ' . $querySort . ' ' . ($context['sort_direction'] == 'down' ? 'DESC' : 'ASC') . '
		LIMIT ' . $context['start'] . ', ' . $modSettings['defaultMaxMembers'],
		array(
		)
	);
	$context['members'] = array();
	while ($row = $smfFunc['db_fetch_assoc']($request))
	{
		$last_online = empty($row['last_login']) ? $txt['never'] : timeformat($row['last_login']);

		// Italicize the online note if they aren't activated.
		if ($row['is_activated'] % 10 != 1)
			$last_online = '<i title="' . $txt['not_activated'] . '">' . $last_online . '</i>';

		$context['members'][] = array(
			'id' => $row['id_member'],
			'name' => '<a href="' . $scripturl . '?action=profile;u=' . $row['id_member'] . '">' . $row['real_name'] . '</a>',
			'email' => $row['email_address'],
			'show_email' => showEmailAddress(!empty($row['hide_email']), $row['id_member']),
			'ip' => '<a href="' . $scripturl . '?action=trackip;searchip=' . $row['member_ip'] . '">' . $row['member_ip'] . '</a>',
			'registered' => timeformat($row['date_registered']),
			'last_online' => $last_online,
			'posts' => $row['posts'],
			'is_activated' => $row['is_activated'] % 10 == 1,
		);
	}
	$smfFunc['db_free_result']($request);

	// Create a pretty auto suggest box for the member names.
	require_once($sourcedir . '/Subs-Editor.php');

	$suggestOptions = array(
		'id' => 'toAdd',
		'search_type' => 'member',
		'width' => '130px',
	);
	create_control_autosuggest($suggestOptions);

	// Select the template.
	$context['sub_template'] = 'group_members';
	$context['page_title'] = $txt['membergroups_members_title'] . ': ' . $context['group']['name'];
}

// Show and manage all group requests.
function GroupRequests()
{
	global $txt, $db_prefix, $context, $scripturl, $user_info, $sourcedir, $smfFunc, $modSettings;

	// Set up the template stuff...
	$context['page_title'] = $txt['mc_group_requests'];

	// Verify we can be here.
	if ($user_info['mod_cache']['gq'] == '0=1')
		isAllowedTo('manage_membergroups');

	// Normally, we act normally...
	$where = $user_info['mod_cache']['gq'] == '1=1' || $user_info['mod_cache']['gq'] == '0=1' ? $user_info['mod_cache']['gq'] : 'lgr.' . $user_info['mod_cache']['gq'];

	// We've submitted?
	if (isset($_POST['sc']) && !empty($_POST['groupr']) && !empty($_POST['req_action']))
	{
		checkSession('post');

		// Clean the values.
		foreach ($_POST['groupr'] as $k => $request)
			$_POST['groupr'][$k] = (int) $request;

		// If we are giving a reason (And why shouldn't we?), then we don't actually do much.
		if ($_POST['req_action'] == 'reason')
		{
			// Different sub template...
			$context['sub_template'] = 'group_request_reason';
			// And a limitation. We don't care that the page number bit makes no sense, as we don't need it!
			$where .= ' AND lgr.id_request IN (' . implode(',', $_POST['groupr']) . ')';
		}
		// Otherwise we do something!
		else
		{
			// Get the details of all the members concerned...
			$request = $smfFunc['db_query']('', '
				SELECT lgr.id_request, lgr.id_member, lgr.id_group, mem.email_address, mem.id_group AS primary_group,
					mem.additional_groups AS additional_groups, mem.lngfile, mem.member_name, mem.notify_types,
					mg.hidden, mg.group_name
				FROM {db_prefix}log_group_requests AS lgr
					INNER JOIN {db_prefix}members AS mem ON (mem.id_member = lgr.id_member)
					INNER JOIN {db_prefix}membergroups AS mg ON (mg.id_group = lgr.id_group)
				WHERE ' . $where . '
					AND lgr.id_request IN ({array_int:inject_array_int_1})
				ORDER BY mem.lngfile',
				array(
					'inject_array_int_1' => $_POST['groupr'],
				)
			);
			$email_details = array();
			$group_changes = array();
			while ($row = $smfFunc['db_fetch_assoc']($request))
			{
				// If we are approving work out what their new group is.
				if ($_POST['req_action'] == 'approve')
				{
					// For people with more than one request at once.
					if (isset($group_changes[$row['id_member']]))
					{
						$row['additional_groups'] = $group_changes[$row['id_member']]['add'];
						$row['primary_group'] = $group_changes[$row['id_member']]['primary'];
					}
					else
						$row['additional_groups'] = explode(',', $row['additional_groups']);

					// Don't have it already?
					if ($row['primary_group'] == $row['id_group'] || in_array($row['id_group'], $row['additional_groups']))
						continue;

					// Should it become their primary?
					if ($row['primary_group'] == 0 && $row['hidden'] == 0)
						$row['primary_group'] = $row['id_group'];
					else
						$row['additional_groups'][] = $row['id_group'];

					// Add them to the group master list.
					$group_changes[$row['id_member']] = array(
						'primary' => $row['primary_group'],
						'add' => $row['additional_groups'],
					);
				}

				// Add required information to email them.
				if ($row['notify_types'] != 4)
					$email_details[] = array(
						'rid' => $row['id_request'],
						'member_id' => $row['id_member'],
						'member_name' => $row['member_name'],
						'group_id' => $row['id_group'],
						'group_name' => $row['group_name'],
						'email' => $row['email_address'],
						'language' => $row['lngfile'],
					);					
			}
			$smfFunc['db_free_result']($request);

			// Remove the evidence...
			$smfFunc['db_query']('', '
				DELETE FROM {db_prefix}log_group_requests
				WHERE id_request IN ({array_int:inject_array_int_1})',
				array(
					'inject_array_int_1' => $_POST['groupr'],
				)
			);

			// Ensure everyone who is online gets their changes right away.
			updateSettings(array('settings_updated' => time()));

			if (!empty($email_details))
			{
				require_once($sourcedir . '/Subs-Post.php');

				// They are being approved?
				if ($_POST['req_action'] == 'approve')
				{
					// Make the group changes.
					foreach ($group_changes as $id => $groups)
					{
						// Sanity check!
						foreach ($groups['add'] as $key => $value)
							if ($value == 0 || trim($value) == '')
								unset($groups['add'][$key]);

						$smfFunc['db_query']('', '
							UPDATE {db_prefix}members
							SET id_group = {int:inject_int_1}, additional_groups = {string:inject_string_1}
							WHERE id_member = {int:inject_int_2}',
							array(
								'inject_int_1' => $groups['primary'],
								'inject_int_2' => $id,
								'inject_string_1' => implode(',', $groups['add']),
							)
						);
					}

					$lastLng = $user_info['language'];
					foreach ($email_details as $email)
					{
						$replacements = array(
							'USERNAME' => $email['member_name'],
							'GROUPNAME' => $email['group_name'],
						);

						$emaildata = loadEmailTemplate('mc_group_approve', $replacements, $email['language']);

						sendmail($email['email'], $emaildata['subject'], $emaildata['body']);

						//!!! DELETE ME DELETE ME
						//sendmail($email['email'], $txt['mc_group_email_sub_approve'], sprintf($txt['mc_group_email_request_approve'], $email['member_name'], $email['group_name'], ''));
					}
				}
				// Otherwise, they are getting rejected (With or without a reason).
				else
				{
					// Same as for approving, kind of.
					$lastLng = $user_info['language'];
					foreach ($email_details as $email)
					{
						$custom_reason = isset($_POST['groupreason']) && isset($_POST['groupreason'][$email['rid']]) ? $_POST['groupreason'][$email['rid']] : '';

						$replacements = array(
							'USERNAME' => $email['member_name'],
							'GROUPNAME' => $email['group_name'],
						);

						if (!empty($custom_reason))
							$replacements['REASON'] = $custom_reason;

						$emaildata = loadEmailTemplate(empty($custom_reason) ? 'mc_group_reject' : 'mc_group_reject_reason', $replacements, $email['language']);

						sendmail($email['email'], $emaildata['subject'], $emaildata['body']);

						//!!! DELETE ME DELETE ME
						//sendmail($email['email'], $txt['mc_group_email_sub_reject'], sprintf($txt['mc_group_email_request_' . ($custom_reason == '' ? 'reject' : 'reject_reason')], $email['member_name'], $email['group_name'], $custom_reason));
					}
				}
			}

			// Restore the current language.
			loadLanguage('ModerationCenter');
		}
	}

	// We're going to want this for making our list.
	require_once($sourcedir . '/Subs-List.php');

	// This is all the information required for a group listing.
	$listOptions = array(
		'id' => 'group_request_list',
		'title' => $txt['mc_group_requests'],
		'width' => '85%',
		'items_per_page' => $modSettings['defaultMaxMessages'],
		'no_items_label' => $txt['mc_groupr_none_found'],
		'base_href' => $scripturl . '?action=groups;sa=requests',
		'default_sort_col' => 'member',
		'get_items' => array(
			'function' => 'list_getGroupRequests',
			'params' => array(
				$where,
			),
		),
		'get_count' => array(
			'function' => 'list_getGroupRequestCount',
			'params' => array(
				$where,
			),
		),
		'columns' => array(
			'member' => array(
				'header' => array(
					'value' => $txt['mc_groupr_member'],
				),
				'data' => array(
					'db' => 'member_link',
				),
				'sort' => array(
					'default' => 'mem.member_name',
					'reverse' => 'mem.member_name DESC',
				),
			),
			'group' => array(
				'header' => array(
					'value' => $txt['mc_groupr_group'],
				),
				'data' => array(
					'db' => 'group_link',
				),
				'sort' => array(
					'default' => 'mg.group_name',
					'reverse' => 'mg.group_name DESC',
				),
			),
			'reason' => array(
				'header' => array(
					'value' => $txt['mc_groupr_reason'],
				),
				'data' => array(
					'db_htmlsafe' => 'reason',
				),
			),
			'action' => array(
				'header' => array(
					'value' => '<input type="checkbox" class="check" onclick="invertAll(this, this.form);" />',
					'style' => 'width: 4%;',
				),
				'data' => array(
					'sprintf' => array(
						'format' => '<input type="checkbox" name="groupr[]" value="%1$d" class="check" />',
						'params' => array(
							'id' => false,
						),
					),
					'style' => 'text-align: center;',
				),
			),
		),
		'form' => array(
			'href' => $scripturl . '?action=groups;sa=requests',
			'include_sort' => true,
			'include_start' => true,
			'hidden_fields' => array(
				'sc' => $context['session_id'],
			),
		),
		'additional_rows' => array(
			array(
				'position' => 'bottom_of_list',
				'value' => '
					<select name="req_action" onchange="if (this.value != 0 && (this.value == \'reason\' || confirm(\'' . $txt['mc_groupr_warning'] . '\'))) this.form.submit();">
						<option value="0">' . $txt['with_selected'] . ':</option>
						<option value="0">---------------------</option>
						<option value="approve">' . $txt['mc_groupr_approve'] . '</option>
						<option value="reject">' . $txt['mc_groupr_reject'] . '</option>
						<option value="reason">' . $txt['mc_groupr_reject_w_reason'] . '</option>
					</select>
					<input type="submit" name="go" value="' . $txt['go'] . '" onclick="var sel = document.getElementById(\'req_action\'); if (sel.value != 0 && sel.value != \'reason\' && !confirm(\'' . $txt['mc_groupr_warning'] . '\')) return false;" />',
				'class' => 'windowbg',
				'align' => 'right',
			),
		),
	);

	// Create the request list.
	createList($listOptions);

	$context['sub_template'] = 'show_list';
	$context['default_list'] = 'group_request_list';
}

function list_getGroupRequestCount($where)
{
	global $smfFunc, $db_prefix;

	$request = $smfFunc['db_query']('', '
		SELECT COUNT(*)
		FROM {db_prefix}log_group_requests AS lgr
		WHERE ' . $where,
		array(
		)
	);
	list ($totalRequests) = $smfFunc['db_fetch_row']($request);
	$smfFunc['db_free_result']($request);

	return $totalRequests;
}

function list_getGroupRequests($start, $items_per_page, $sort, $where)
{
	global $smfFunc, $db_prefix, $txt, $scripturl;

	$request = $smfFunc['db_query']('', '
		SELECT lgr.id_request, lgr.id_member, lgr.id_group, lgr.time_applied, lgr.reason,
			mem.member_name, mg.group_name, mg.online_color
		FROM {db_prefix}log_group_requests AS lgr
			INNER JOIN {db_prefix}members AS mem ON (mem.id_member = lgr.id_member)
			INNER JOIN {db_prefix}membergroups AS mg ON (mg.id_group = lgr.id_group)
		WHERE ' . $where . '
		ORDER BY ' . $sort . '
		LIMIT ' . $start . ', ' . $items_per_page,
		array(
		)
	);
	$group_requests = array();
	while ($row = $smfFunc['db_fetch_assoc']($request))
	{
		$group_requests[] = array(
			'id' => $row['id_request'],
			'member_link' => '<a href="' . $scripturl . '?action=profile;u=' . $row['id_member'] . '">' . $row['member_name'] . '</a>',
			'group_link' => '<span style="color: ' . $row['online_color'] . '">' . $row['group_name'] . '</span>',
			'reason' => censorText($row['reason']),
			'time_submitted' => timeformat($row['time_applied']),
		);
	}
	$smfFunc['db_free_result']($request);

	return $group_requests;
}

?>