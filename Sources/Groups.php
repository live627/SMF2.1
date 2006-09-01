<?php
/******************************************************************************
* Groups.php                                                                  *
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

/* This file currently just shows group info, and allows certain privaledged members to add/remove members.

	void Groups()
		- allows moderators and users to access the group showing functions.
		- handles permission checks, and puts the moderation bar on as required.

	void MembergroupMembers()
		- can be called from ManageMembergroups if it needs templating within the admin environment.
		- show a list of members that are part of a given membergroup.
		- called by ?action=admin;area=membergroups;sa=members;group=x
		- requires the manage_membergroups permission.
		- uses the group_members sub template of ManageMembergroups.
		- allows to add and remove members from the selected membergroup.
		- allows sorting on several columns.
		- redirects to itself.
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
	if (allowedTo('access_mod_center') || !empty($user_info['mod_cache']['bq']) || !empty($user_info['mod_cache']['gq']) || allowedTo('manage_membergroups'))
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
	global $txt, $db_prefix, $scripturl, $user_profile, $user_info, $context, $settings, $modSettings;

	// Yep, find the groups...
	$request = db_query("
		SELECT mg.ID_GROUP, mg.groupName, mg.description, mg.groupType, mg.onlineColor, mg.hidden,
			mg.stars, !ISNULL(gm.ID_MEMBER) AS can_moderate
		FROM {$db_prefix}membergroups AS mg
			LEFT JOIN {$db_prefix}group_moderators AS gm ON (gm.ID_GROUP = mg.ID_GROUP AND gm.ID_MEMBER = $user_info[id])
		WHERE mg.minPosts = -1
			AND mg.ID_GROUP != 3
		ORDER BY groupName", __FILE__, __LINE__);
	// This is where we store our groups.
	$context['groups'] = array();
	$group_ids = array();
	$context['can_moderate'] = allowedTo('manage_membergroups');
	while ($row = mysql_fetch_assoc($request))
	{
		// We only list the groups they can see.
		if ($row['hidden'] && !$row['can_moderate'] && !allowedTo('manage_membergroups'))
			continue;

		$row['stars'] = explode('#', $row['stars']);

		$context['groups'][$row['ID_GROUP']] = array(
			'id' => $row['ID_GROUP'],
			'name' => $row['groupName'],
			'desc' => $row['description'],
			'color' => $row['onlineColor'],
			'type' => $row['groupType'],
			'num_members' => 0,
			'stars' => !empty($row['stars'][0]) && !empty($row['stars'][1]) ? str_repeat('<img src="' . $settings['images_url'] . '/' . $row['stars'][1] . '" alt="*" border="0" />', $row['stars'][0]) : '',
		);

		$context['can_moderate'] |= $row['can_moderate'];
		$group_ids[] = $row['ID_GROUP'];
	}
	mysql_free_result($request);

	// Count up the members separately...
	if (!empty($group_ids))
	{
		$query = db_query("
			SELECT ID_GROUP, COUNT(*) AS num_members
			FROM {$db_prefix}members
			WHERE ID_GROUP IN (" . implode(', ', $group_ids) . ")
			GROUP BY ID_GROUP", __FILE__, __LINE__);
		while ($row = mysql_fetch_assoc($query))
			$context['groups'][$row['ID_GROUP']]['num_members'] += $row['num_members'];
		mysql_free_result($query);

		// Only do additional groups if we can moderate...
		if ($context['can_moderate'])
		{
			$query = db_query("
				SELECT mg.ID_GROUP, COUNT(*) AS num_members
				FROM ({$db_prefix}membergroups AS mg, {$db_prefix}members AS mem)
				WHERE mg.ID_GROUP IN (" . implode(', ', $group_ids) . ")
					AND mem.additionalGroups != ''
					AND mem.ID_GROUP != mg.ID_GROUP
					AND FIND_IN_SET(mg.ID_GROUP, mem.additionalGroups)
				GROUP BY mg.ID_GROUP", __FILE__, __LINE__);
			while ($row = mysql_fetch_assoc($query))
				$context['groups'][$row['ID_GROUP']]['num_members'] += $row['num_members'];
			mysql_free_result($query);
		}
	}

	$context['sub_template'] = 'group_index';
	$context['page_title'] = $txt['viewing_groups'];
}

// Display members of a group, and allow adding of members to a group. Silly function name though ;)
function MembergroupMembers()
{
	global $txt, $scripturl, $db_prefix, $context, $modSettings, $sourcedir, $user_info, $settings, $smffunc;

	$_REQUEST['group'] = isset($_REQUEST['group']) ? (int) $_REQUEST['group'] : 0;

	// No browsing of guests, membergroup 0 or moderators.
	if (in_array($_REQUEST['group'], array(-1, 0, 3)))
		fatal_lang_error('membergroup_does_not_exist', false);

	// Load up the group details.
	$request = db_query("
		SELECT ID_GROUP AS id, groupName AS name, minPosts = -1 AS assignable, hidden, onlineColor,
			stars, description, minPosts != -1 AS is_post_group
		FROM {$db_prefix}membergroups
		WHERE ID_GROUP = $_REQUEST[group]
		LIMIT 1", __FILE__, __LINE__);
	// Doesn't exist?
	if (mysql_num_rows($request) == 0)
		fatal_lang_error('membergroup_does_not_exist', false);
	$context['group'] = mysql_fetch_assoc($request);
	mysql_free_result($request);

	// Fix the stars.
	$context['group']['stars'] = explode('#', $context['group']['stars']);
	$context['group']['stars'] = !empty($context['group']['stars'][0]) && !empty($context['group']['stars'][1]) ? str_repeat('<img src="' . $settings['images_url'] . '/' . $context['group']['stars'][1] . '" alt="*" border="0" />', $context['group']['stars'][0]) : '';
	$context['group']['can_moderate'] = allowedTo('manage_membergroups');

	$context['linktree'][] = array(
		'url' => $scripturl . '?action=groups;sa=members;group=' . $context['group']['id'],
		'name' => $context['group']['name'],
	);

	// Load all the group moderators, for fun.
	$request = db_query("
		SELECT mem.ID_MEMBER, mem.realName
		FROM ({$db_prefix}group_moderators AS mods, {$db_prefix}members AS mem)
		WHERE mods.ID_GROUP = $_REQUEST[group]
			AND mem.ID_MEMBER = mods.ID_MEMBER", __FILE__, __LINE__);
	$context['group']['moderators'] = array();
	while ($row = mysql_fetch_assoc($request))
	{
		$context['group']['moderators'][] = array(
			'id' => $row['ID_MEMBER'],
			'name' => $row['realName']
		);

		if ($user_info['id'] == $row['ID_MEMBER'])
			$context['group']['can_moderate'] = true;
	}
	mysql_free_result($request);

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
	elseif (isset($_REQUEST['add']) && !empty($_REQUEST['toAdd']) && $context['group']['assignable'])
	{
		checkSession();

		// Get all the members to be added... taking into account names can be quoted ;)
		$_REQUEST['toAdd'] = strtr($smffunc['htmlspecialchars'](stripslashes($_REQUEST['toAdd']), ENT_QUOTES), array('&quot;' => '"'));
		preg_match_all('~"([^"]+)"~', $_REQUEST['toAdd'], $matches);
		$memberNames = array_unique(array_merge($matches[1], explode(',', preg_replace('~"([^"]+)"~', '', $_REQUEST['toAdd']))));

		foreach ($memberNames as $index => $memberName)
		{
			$memberNames[$index] = trim($memberNames[$index]);

			if (strlen($memberNames[$index]) == 0)
				unset($memberNames[$index]);
		}

		$request = db_query("
			SELECT ID_MEMBER
			FROM {$db_prefix}members
			WHERE (memberName IN ('" . implode("', '", $memberNames) . "') OR realName IN ('" . implode("', '", $memberNames) . "'))
				AND ID_GROUP != $_REQUEST[group]
				AND NOT FIND_IN_SET($_REQUEST[group], additionalGroups)
			LIMIT " . count($memberNames), __FILE__, __LINE__);
		$members = array();
		while ($row = mysql_fetch_assoc($request))
			$members[] = $row['ID_MEMBER'];
		mysql_free_result($request);

		// !!! Add $_POST['additional'] to templates!

		// Do the updates...
		require_once($sourcedir . '/Subs-Membergroups.php');
		addMembersToGroup($members, $_REQUEST['group'], isset($_POST['additional']) || $context['group']['hidden'] ? 'only_additional' : 'auto', true);
	}

	// Sort out the sorting!
	$sort_methods = array(
		'name' => 'realName',
		'email' => 'emailAddress',
		'active' => 'lastLogin',
		'registered' => 'dateRegistered',
		'posts' => 'posts',
	);

	// They didn't pick one, default to by name..
	if (!isset($_REQUEST['sort']) || !isset($sort_methods[$_REQUEST['sort']]))
	{
		$context['sort_by'] = 'name';
		$querySort = 'realName';
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
		$where = $context['group']['is_post_group'] ? "ID_POST_GROUP = $_REQUEST[group]" : "ID_GROUP = $_REQUEST[group] OR FIND_IN_SET($_REQUEST[group], additionalGroups)";
	else
		$where = $context['group']['is_post_group'] ? "ID_POST_GROUP = $_REQUEST[group]" : "ID_GROUP = $_REQUEST[group]";

	// Count members of the group.
	$request = db_query("
		SELECT COUNT(*)
		FROM {$db_prefix}members
		WHERE $where", __FILE__, __LINE__);
	list ($context['total_members']) = mysql_fetch_row($request);
	mysql_free_result($request);

	// Create the page index.
	$context['page_index'] = constructPageIndex($scripturl . '?action=admin;area=membergroups;sa=members;group=' . $_REQUEST['group'] . ';sort=' . $context['sort_by'] . (isset($_REQUEST['desc']) ? ';desc' : ''), $_REQUEST['start'], $context['total_members'], $modSettings['defaultMaxMembers']);
	$context['start'] = $_REQUEST['start'];

	// Load up all members of this group.
	$request = db_query("
		SELECT ID_MEMBER, memberName, realName, emailAddress, memberIP, dateRegistered, lastLogin, posts, is_activated
		FROM {$db_prefix}members
		WHERE $where
		ORDER BY $querySort " . ($context['sort_direction'] == 'down' ? 'DESC' : 'ASC') . "
		LIMIT $context[start], $modSettings[defaultMaxMembers]", __FILE__, __LINE__);
	$context['members'] = array();
	while ($row = mysql_fetch_assoc($request))
	{
		$last_online = empty($row['lastLogin']) ? $txt['never'] : timeformat($row['lastLogin']);

		// Italicize the online note if they aren't activated.
		if ($row['is_activated'] % 10 != 1)
			$last_online = '<i title="' . $txt['not_activated'] . '">' . $last_online . '</i>';

		$context['members'][] = array(
			'id' => $row['ID_MEMBER'],
			'name' => '<a href="' . $scripturl . '?action=profile;u=' . $row['ID_MEMBER'] . '">' . $row['realName'] . '</a>',
			'email' => '<a href="mailto:' . $row['emailAddress'] . '">' . $row['emailAddress'] . '</a>',
			'ip' => '<a href="' . $scripturl . '?action=trackip;searchip=' . $row['memberIP'] . '">' . $row['memberIP'] . '</a>',
			'registered' => timeformat($row['dateRegistered']),
			'last_online' => $last_online,
			'posts' => $row['posts'],
			'is_activated' => $row['is_activated'] % 10 == 1,
		);
	}
	mysql_free_result($request);

	// Select the template.
	$context['sub_template'] = 'group_members';
	$context['page_title'] = $txt['membergroups_members_title'] . ': ' . $context['group']['name'];
}

// Show and manage all group requests.
function GroupRequests()
{
	global $txt, $db_prefix, $context, $scripturl, $user_info, $sourcedir;

	// Set up the template stuff...
	$context['page_title'] = $txt['mc_group_requests'];
	$context['sub_template'] = 'group_requests';

	// Verify we can be here.
	if (empty($user_info['mod_cache']['gq']))
		isAllowedTo('manage_membergroups');

	// Normally, we act normally...
	$where = $user_info['mod_cache']['gq'] == 1 ? '1' : 'lgr.' . $user_info['mod_cache']['gq'];

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
			$where .= ' AND lgr.ID_REQUEST IN (' . implode(',', $_POST['groupr']) . ')';
		}
		// Otherwise we do something!
		else
		{
			// Get the details of all the members concerned...
			$request = db_query("
				SELECT lgr.ID_REQUEST, lgr.ID_MEMBER, lgr.ID_GROUP, mem.emailAddress, mem.ID_GROUP AS primary_group,
					mem.additionalGroups AS additional_groups, mem.lngfile, mem.memberName, mem.notifyTypes,
					mg.hidden, mg.groupName
				FROM ({$db_prefix}log_group_requests AS lgr, {$db_prefix}members AS mem, {$db_prefix}membergroups AS mg)
				WHERE $where
					AND lgr.ID_REQUEST IN (" . implode(',', $_POST['groupr']) . ")
					AND mem.ID_MEMBER = lgr.ID_MEMBER
					AND mg.ID_GROUP = lgr.ID_GROUP
				ORDER BY mem.lngfile", __FILE__, __LINE__);
			$email_details = array();
			$group_changes = array();
			while ($row = mysql_fetch_assoc($request))
			{
				// If we are approving work out what their new group is.
				if ($_POST['req_action'] == 'approve')
				{
					// For people with more than one request at once.
					if (isset($group_changes[$row['ID_MEMBER']]))
					{
						$row['additional_groups'] = $group_changes[$row['ID_MEMBER']]['add'];
						$row['primary_group'] = $group_changes[$row['ID_MEMBER']]['primary'];
					}
					else
						$row['additional_groups'] = explode(',', $row['additional_groups']);

					// Don't have it already?
					if ($row['primary_group'] == $row['ID_GROUP'] || in_array($row['ID_GROUP'], $row['additional_groups']))
						continue;

					// Should it become their primary?
					if ($row['primary_group'] == 0 && $row['hidden'] == 0)
						$row['primary_group'] = $row['ID_GROUP'];
					else
						$row['additional_groups'][] = $row['ID_GROUP'];

					// Add them to the group master list.
					$group_changes[$row['ID_MEMBER']] = array(
						'primary' => $row['primary_group'],
						'add' => $row['additional_groups'],
					);
				}

				// Add required information to email them.
				if ($row['notifyTypes'] != 4)
					$email_details[] = array(
						'rid' => $row['ID_REQUEST'],
						'member_id' => $row['ID_MEMBER'],
						'member_name' => $row['memberName'],
						'group_id' => $row['ID_GROUP'],
						'group_name' => $row['groupName'],
						'email' => $row['emailAddress'],
						'language' => $row['lngfile'],
					);					
			}
			mysql_free_result($request);

			// Remove the evidence...
			db_query("
				DELETE FROM {$db_prefix}log_group_requests
				WHERE ID_REQUEST IN (" . implode(',', $_POST['groupr']) . ")", __FILE__, __LINE__);

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

						db_query("
							UPDATE {$db_prefix}members
							SET ID_GROUP = $groups[primary], additionalGroups = '" . implode(',', $groups['add']) . "'
							WHERE ID_MEMBER = $id", __FILE__, __LINE__);
					}

					$lastLng = $user_info['language'];
					foreach ($email_details as $email)
					{
						// Need to change the language?
						if ($lastLng != $email['language'])
						{
							$lastLng = $email['language'];
							loadLanguage('ModerationCenter', $email['language'], false);
						}
						sendmail($email['email'], $txt['mc_group_email_sub_approve'], sprintf($txt['mc_group_email_request_approve'], $email['member_name'], $email['group_name'], ''));
					}
				}
				// Otherwise, they are getting rejected (With or without a reason).
				else
				{
					// Same as for approving, kind of.
					$lastLng = $user_info['language'];
					foreach ($email_details as $email)
					{
						// Need to change the language?
						if ($lastLng != $email['language'])
						{
							$lastLng = $email['language'];
							loadLanguage('ModerationCenter', $email['language'], false);
						}
						$custom_reason = isset($_POST['groupreason']) && isset($_POST['groupreason'][$email['rid']]) ? $_POST['groupreason'][$email['rid']] : '';
						sendmail($email['email'], $txt['mc_group_email_sub_reject'], sprintf($txt['mc_group_email_request_' . ($custom_reason == '' ? 'reject' : 'reject_reason')], $email['member_name'], $email['group_name'], $custom_reason));
					}
				}
			}

			// Restore the current language.
			loadLanguage('ModerationCenter');
		}
	}

	// There *could* be many, so paginate.
	$request = db_query("
		SELECT COUNT(*)
		FROM {$db_prefix}log_group_requests AS lgr
		WHERE $where", __FILE__, __LINE__);
	list ($context['total_requests']) = mysql_fetch_row($request);
	mysql_free_result($request);

	// So, that means we can page index, yes?
	$context['page_index'] = constructPageIndex($scripturl . '?action=groups;sa=requests', $_GET['start'], $context['total_requests'], 10);
	$context['start'] = $_GET['start'];

	// Fetch all the group requests...
	//!!! What can they actually see?
	$request = db_query("
		SELECT lgr.ID_REQUEST, lgr.ID_MEMBER, lgr.ID_GROUP, lgr.time_applied, lgr.reason,
			mem.memberName, mg.groupName, mg.onlineColor
		FROM ({$db_prefix}log_group_requests AS lgr, {$db_prefix}members AS mem, {$db_prefix}membergroups AS mg)
		WHERE $where
			AND mem.ID_MEMBER = lgr.ID_MEMBER
			AND mg.ID_GROUP = lgr.ID_GROUP
		ORDER BY lgr.ID_REQUEST DESC
		LIMIT 10", __FILE__, __LINE__);
	$context['group_requests'] = array();
	while ($row = mysql_fetch_assoc($request))
	{
		$context['group_requests'][] = array(
			'id' => $row['ID_REQUEST'],
			'member' => array(
				'id' => $row['ID_MEMBER'],
				'name' => $row['memberName'],
				'link' => '<a href="' . $scripturl . '?action=profile;u=' . $row['ID_MEMBER'] . '">' . $row['memberName'] . '</a>',
				'href' => $scripturl . '?action=profile;u=' . $row['ID_MEMBER'],
			),
			'group' => array(
				'id' => $row['ID_GROUP'],
				'name' => $row['groupName'],
				'color' => $row['onlineColor'],
				'link' => '<span style="color: ' . $row['onlineColor'] . '">' . $row['groupName'] . '</span>',
			),
			'reason' => censorText($row['reason']),
			'time_submitted' => timeformat($row['time_applied']),
		);
	}
	mysql_free_result($request);
}

?>