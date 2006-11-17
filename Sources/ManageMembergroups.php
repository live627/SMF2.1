<?php
/******************************************************************************
* ManageMembergroups.php                                                      *
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

/* This file is concerned with anything in the Manage Membergroups screen.

	void ModifyMembergroups()
		- entrance point of the 'Manage Membergroups' center.
		- called by ?action=admin;area=membergroups.
		- loads the ManageMembergroups template.
		- loads the MangeMembers language file.
		- requires the manage_membergroups or the admin_forum permission.
		- calls a function based on the given subaction.
		- defaults to sub action 'index' or without manage_membergroup
		  permissions to 'settings'.

	void MembergroupIndex()
		- shows an overview of the current membergroups.
		- called by ?action=admin;area=membergroups.
		- requires the manage_membergroups permission.
		- uses the main ManageMembergroups template.
		- splits the membergroups in regular ones and post count based groups.
		- also counts the number of members part of each membergroup.

	void AddMembergroup()
		- allows to add a membergroup and set some initial properties.
		- called by ?action=admin;area=membergroups;sa=add.
		- requires the manage_membergroups permission.
		- uses the new_group sub template of ManageMembergroups.
		- allows to use a predefined permission profile or copy one from
		  another group.
		- redirects to action=admin;area=membergroups;sa=edit;group=x.

	void DeleteMembergroup()
		- deletes a membergroup by URL.
		- called by ?action=admin;area=membergroups;sa=delete;group=x;sesc=y.
		- requires the manage_membergroups permission.
		- redirects to ?action=admin;area=membergroups.

	void EditMembergroup()
		- screen to edit a specific membergroup.
		- called by ?action=admin;area=membergroups;sa=edit;group=x.
		- requires the manage_membergroups permission.
		- uses the edit_group sub template of ManageMembergroups.
		- also handles the delete button of the edit form.
		- redirects to ?action=admin;area=membergroups.

	void ModifyMembergroupsettings()
		- set some general membergroup settings and permissions.
		- called by ?action=admin;area=membergroups;sa=settings
		- requires the admin_forum permission (and manage_permissions for
		  changing permissions)
		- uses membergroup_settings sub template of ManageMembergroups.
		- redirects to itself.

	void cacheGroups()
		- gets a list of all the public membergroups.
		- saves this list serialised into the settings table.
*/

// The entrance point for all 'Manage Membergroup' actions.
function ModifyMembergroups()
{
	global $context, $txt, $scripturl, $sourcedir;

	$subActions = array(
		'add' => array('AddMembergroup', 'manage_membergroups'),
		'delete' => array('DeleteMembergroup', 'manage_membergroups'),
		'edit' => array('EditMembergroup', 'manage_membergroups'),
		'index' => array('MembergroupIndex', 'manage_membergroups'),
		'members' => array('MembergroupMembers', 'manage_membergroups', 'Groups.php'),
		'settings' => array('ModifyMembergroupsettings', 'admin_forum'),
	);

	// Default to sub action 'index' or 'settings' depending on permissions.
	$_REQUEST['sa'] = isset($_REQUEST['sa']) && isset($subActions[$_REQUEST['sa']]) ? $_REQUEST['sa'] : (allowedTo('manage_membergroups') ? 'index' : 'settings');

	// Is it elsewhere?
	if (isset($subActions[$_REQUEST['sa']][2]))
		require_once($sourcedir . '/' . $subActions[$_REQUEST['sa']][2]);

	// Do the permission check, you might not be allowed her.
	isAllowedTo($subActions[$_REQUEST['sa']][1]);

	// Language and template stuff, the usual.
	loadLanguage('ManageMembers');
	loadTemplate('ManageMembergroups');

	// Setup the admin tabs.
	$context['admin_tabs'] = array(
		'title' => $txt['membergroups_title'],
		'help' => 'membergroups',
		'description' => $txt['membergroups_description'],
		'tabs' => array(),
	);
	if (allowedTo('manage_membergroups'))
	{
		$context['admin_tabs']['tabs']['index'] = array(
			'title' => $txt['membergroups_edit_groups'],
			'description' => $txt['membergroups_description'],
			'href' => $scripturl . '?action=admin;area=membergroups',
			'is_selected' => $_REQUEST['sa'] != 'add' && $_REQUEST['sa'] != 'settings',
		);
		$context['admin_tabs']['tabs']['add_cat'] = array(
			'title' => $txt['membergroups_new_group'],
			'description' => $txt['membergroups_description'],
			'href' => $scripturl . '?action=admin;area=membergroups;sa=add',
			'is_selected' => $_REQUEST['sa'] == 'add',
			'is_last' => !allowedTo('admin_forum'),
		);
	}
	if (allowedTo('admin_forum'))
		$context['admin_tabs']['tabs']['settings'] = array(
			'title' => $txt['settings'],
			'description' => $txt['membergroups_description'],
			'href' => $scripturl . '?action=admin;area=membergroups;sa=settings',
			'is_selected' => $_REQUEST['sa'] == 'settings',
			'is_last' => true,
		);

	// Call the right function.
	$subActions[$_REQUEST['sa']][0]();
}

// An overview of the current membergroups.
function MembergroupIndex()
{
	global $db_prefix, $txt, $scripturl, $context, $settings, $smfFunc;

	$context['page_title'] = $txt['membergroups_title'];

	$context['groups'] = array(
		'regular' => array(),
		'post' => array()
	);

	$query = $smfFunc['db_query']('', "
		SELECT id_group, group_name, min_posts, online_color, stars
		FROM {$db_prefix}membergroups
		ORDER BY min_posts, CASE WHEN id_group < 4 THEN id_group ELSE 4 END, group_name", __FILE__, __LINE__);
	while ($row = $smfFunc['db_fetch_assoc']($query))
	{
		$row['stars'] = explode('#', $row['stars']);
		$context['groups'][$row['min_posts'] == -1 ? 'regular' : 'post'][$row['id_group']] = array(
			'id' => $row['id_group'],
			'name' => $row['group_name'],
			'num_members' => $row['id_group'] != 3 ? 0 : $txt['membergroups_guests_na'],
			'allow_delete' => $row['id_group'] > 4,
			'can_search' => $row['id_group'] != 3,
			'href' => $scripturl . '?action=admin;area=membergroups;sa=members;group=' . $row['id_group'],
			'is_post_group' => $row['min_posts'] != -1,
			'min_posts' => $row['min_posts'] == -1 ? '-' : $row['min_posts'],
			'color' => empty($row['online_color']) ? '' : $row['online_color'],
			'stars' => !empty($row['stars'][0]) && !empty($row['stars'][1]) ? str_repeat('<img src="' . $settings['images_url'] . '/' . $row['stars'][1] . '" alt="*" border="0" />', $row['stars'][0]) : '',
		);
	}
	$smfFunc['db_free_result']($query);

	if (!empty($context['groups']['post']))
	{
		$query = $smfFunc['db_query']('', "
			SELECT id_post_group AS id_group, COUNT(*) AS num_members
			FROM {$db_prefix}members
			WHERE id_post_group IN (" . implode(', ', array_keys($context['groups']['post'])) . ")
			GROUP BY id_post_group", __FILE__, __LINE__);
		while ($row = $smfFunc['db_fetch_assoc']($query))
			$context['groups']['post'][$row['id_group']]['num_members'] += $row['num_members'];
		$smfFunc['db_free_result']($query);
	}

	if (!empty($context['groups']['regular']))
	{
		$query = $smfFunc['db_query']('', "
			SELECT id_group, COUNT(*) AS num_members
			FROM {$db_prefix}members
			WHERE id_group IN (" . implode(', ', array_keys($context['groups']['regular'])) . ")
			GROUP BY id_group", __FILE__, __LINE__);
		while ($row = $smfFunc['db_fetch_assoc']($query))
			$context['groups']['regular'][$row['id_group']]['num_members'] += $row['num_members'];
		$smfFunc['db_free_result']($query);

		$query = $smfFunc['db_query']('', "
			SELECT mg.id_group, COUNT(*) AS num_members
			FROM ({$db_prefix}membergroups AS mg, {$db_prefix}members AS mem)
			WHERE mg.id_group IN (" . implode(', ', array_keys($context['groups']['regular'])) . ")
				AND mem.additional_groups != ''
				AND mem.id_group != mg.id_group
				AND FIND_IN_SET(mg.id_group, mem.additional_groups)
			GROUP BY mg.id_group", __FILE__, __LINE__);
		while ($row = $smfFunc['db_fetch_assoc']($query))
			$context['groups']['regular'][$row['id_group']]['num_members'] += $row['num_members'];
		$smfFunc['db_free_result']($query);
	}

	foreach ($context['groups'] as $temp => $dummy)
		foreach ($dummy as $id => $data)
		{
			if ($data['href'] != '')
				$context['groups'][$temp][$id]['link'] = '<a href="' . $data['href'] . '">' . $data['name'] . '</a>';
			else
				$context['groups'][$temp][$id]['link'] = '';
		}
}

// Add a membergroup.
function AddMembergroup()
{
	global $db_prefix, $context, $txt, $sourcedir, $modSettings, $smfFunc;

	// A form was submitted, we can start adding.
	if (!empty($_POST['group_name']))
	{
		checkSession();

		$postCountBasedGroup = isset($_POST['min_posts']) && (!isset($_POST['postgroup_based']) || !empty($_POST['postgroup_based']));

		// !!! Check for members with same name too?

		$request = $smfFunc['db_query']('', "
			SELECT MAX(id_group)
			FROM {$db_prefix}membergroups", __FILE__, __LINE__);
		list ($id_group) = $smfFunc['db_fetch_row']($request);
		$smfFunc['db_free_result']($request);
		$id_group++;

		$smfFunc['db_query']('', "
			INSERT INTO {$db_prefix}membergroups
				(id_group, group_name, min_posts, stars, online_color)
			VALUES ($id_group, SUBSTRING('$_POST[group_name]', 1, 80), " . ($postCountBasedGroup ? (int) $_POST['min_posts'] : '-1') . ", '1#star.gif', '')", __FILE__, __LINE__);

		// Update the post groups now, if this is a post group!
		if (isset($_POST['min_posts']))
			updateStats('postgroups');

		// You cannot set permissions for post groups if they are disabled.
		if ($postCountBasedGroup && empty($modSettings['permission_enable_postgroups']))
			$_POST['perm_type'] = '';

		if ($_POST['perm_type'] == 'predefined')
		{
			// Set default permission level.
			require_once($sourcedir . '/ManagePermissions.php');
			setPermissionLevel($_POST['level'], $id_group, 'null');
		}
		// Copy or inherit the permissions!
		elseif ($_POST['perm_type'] == 'copy' || $_POST['perm_type'] == 'inherit')
		{
			$copy_id = $_POST['perm_type'] == 'copy' ? (int) $_POST['copyperm'] : (int) $_POST['inheritperm'];

			$request = $smfFunc['db_query']('', "
				SELECT permission, add_deny
				FROM {$db_prefix}permissions
				WHERE id_group = $copy_id", __FILE__, __LINE__);
			$setString = '';
			while ($row = $smfFunc['db_fetch_assoc']($request))
				$setString .= "
					($id_group, '$row[permission]', $row[add_deny]),";
			$smfFunc['db_free_result']($request);

			if (!empty($setString))
				$smfFunc['db_query']('', "
					INSERT INTO {$db_prefix}permissions
						(id_group, permission, add_deny)
					VALUES" . substr($setString, 0, -1), __FILE__, __LINE__);

			$request = $smfFunc['db_query']('', "
				SELECT id_profile, permission, add_deny
				FROM {$db_prefix}board_permissions
				WHERE id_group = $copy_id", __FILE__, __LINE__);
			$setString = '';
			while ($row = $smfFunc['db_fetch_assoc']($request))
				$setString .= "
					($id_group, $row[id_profile], '$row[permission]', $row[add_deny]),";
			$smfFunc['db_free_result']($request);

			if (!empty($setString))
				$smfFunc['db_query']('', "
					INSERT INTO {$db_prefix}board_permissions
						(id_group, id_profile, permission, add_deny)
					VALUES" . substr($setString, 0, -1), __FILE__, __LINE__);

			// Also get some membergroup information if we're copying and not copying from guests...
			if ($copy_id > 0 && $_POST['perm_type'] == 'copy')
			{
				$request = $smfFunc['db_query']('', "
					SELECT online_color, max_messages, stars
					FROM {$db_prefix}membergroups
					WHERE id_group = $copy_id
					LIMIT 1", __FILE__, __LINE__);
				$group_info = $smfFunc['db_fetch_assoc']($request);
				$smfFunc['db_free_result']($request);

				// ...and update the new membergroup with it.
				$smfFunc['db_query']('', "
					UPDATE {$db_prefix}membergroups
					SET
						online_color = '$group_info[online_color]',
						max_messages = $group_info[max_messages],
						stars = '$group_info[stars]'
					WHERE id_group = $id_group
					LIMIT 1", __FILE__, __LINE__);
			}
			// If inheriting say so...
			elseif ($_POST['perm_type'] == 'inherit')
			{
				$smfFunc['db_query']('', "
					UPDATE {$db_prefix}membergroups
					SET id_parent = $copy_id
					WHERE id_group = $id_group
					LIMIT 1", __FILE__, __LINE__);
			}
		}

		// Make sure all boards selected are stored in a proper array.
		$_POST['boardaccess'] = empty($_POST['boardaccess']) || !is_array($_POST['boardaccess']) ? array() : $_POST['boardaccess'];
		foreach ($_POST['boardaccess'] as $key => $value)
			$_POST['boardaccess'][$key] = (int) $value;

		// Only do this if they have special access requirements.
		if (!empty($_POST['boardaccess']))
			$smfFunc['db_query']('', "
				UPDATE {$db_prefix}boards
				SET member_groups = CASE WHEN member_groups = '' THEN '$id_group' ELSE CONCAT(member_groups, ',$id_group') END
				WHERE id_board IN (" . implode(', ', $_POST['boardaccess']) . ")
				LIMIT " . count($_POST['boardaccess']), __FILE__, __LINE__);

		// Rebuild the group cache.
		cacheGroups();

		// Go change some more settings.
		redirectexit('action=admin;area=membergroups;sa=edit;group=' . $id_group);
	}

	// Just show the 'add membergroup' screen.
	$context['page_title'] = $txt['membergroups_new_group'];
	$context['sub_template'] = 'new_group';
	$context['post_group'] = !empty($_REQUEST['postgroup']);
	$context['undefined_group'] = empty($_REQUEST['postgroup']) && empty($_REQUEST['generalgroup']);

	$result = $smfFunc['db_query']('', "
		SELECT id_group, group_name
		FROM {$db_prefix}membergroups
		WHERE (id_group > 3 OR id_group = 2)" . (empty($modSettings['permission_enable_postgroups']) ? "
			AND min_posts = -1" : '') . "
		ORDER BY min_posts, id_group != 2, group_name", __FILE__, __LINE__);
	$context['groups'] = array();
	while ($row = $smfFunc['db_fetch_assoc']($result))
		$context['groups'][] = array(
			'id' => $row['id_group'],
			'name' => $row['group_name']
		);
	$smfFunc['db_free_result']($result);

	$result = $smfFunc['db_query']('', "
		SELECT id_board, name, child_level
		FROM {$db_prefix}boards", __FILE__, __LINE__);
	$context['boards'] = array();
	while ($row = $smfFunc['db_fetch_assoc']($result))
		$context['boards'][] = array(
			'id' => $row['id_board'],
			'name' => $row['name'],
			'child_level' => $row['child_level'],
			'selected' => false
		);
	$smfFunc['db_free_result']($result);
}

// Deleting a membergroup by URL (not implemented).
function DeleteMembergroup()
{
	global $sourcedir;

	checkSession('get');

	require_once($sourcedir . '/Subs-Membergroups.php');
	deleteMembergroups((int) $_REQUEST['group']);

	// Go back to the membergroup index.
	redirectexit('action=admin;area=membergroups;');
}

// Editing a membergroup.
function EditMembergroup()
{
	global $db_prefix, $context, $txt, $sourcedir, $modSettings, $smfFunc;

	// Make sure this group is editable.
	if (empty($_REQUEST['group']) || (int) $_REQUEST['group'] < 1)
		fatal_lang_error('membergroup_does_not_exist', false);
	$_REQUEST['group'] = (int) $_REQUEST['group'];

	// The delete this membergroup button was pressed.
	if (isset($_POST['delete']))
	{
		checkSession();

		require_once($sourcedir . '/Subs-Membergroups.php');
		deleteMembergroups($_REQUEST['group']);

		redirectexit('action=admin;area=membergroups;');
	}
	// A form was submitted with the new membergroup settings.
	elseif (isset($_POST['submit']))
	{
		// Validate the session.
		checkSession();

		// Set variables to their proper value.
		$_POST['max_messages'] = isset($_POST['max_messages']) ? (int) $_POST['max_messages'] : 0;
		$_POST['min_posts'] = isset($_POST['min_posts']) && isset($_POST['group_type']) && $_POST['group_type'] == -1 && $_REQUEST['group'] > 3 ? abs($_POST['min_posts']) : ($_REQUEST['group'] == 4 ? 0 : -1);
		$_POST['stars'] = (empty($_POST['star_count']) || $_POST['star_count'] < 0) ? '' : min((int) $_POST['star_count'], 99) . '#' . $_POST['star_image'];
		$_POST['group_desc'] = isset($_POST['group_desc']) && ($_REQUEST['group'] == 1 || (isset($_POST['group_type']) && $_POST['group_type'] != -1)) ? trim($_POST['group_desc']) : '';
		$_POST['group_type'] = isset($_POST['group_type']) && $_POST['group_type'] >= 0 && $_POST['group_type'] <= 2 ? (int) $_POST['group_type'] : 0;
		$_POST['group_hidden'] = empty($_POST['group_hidden']) && $_POST['min_posts'] == -1 && $_REQUEST['group'] != 3 ? 1 : 0;
		$_POST['group_inherit'] = $_REQUEST['group'] > 1 ? (int) $_POST['group_inherit'] : -2;

		// !!! Don't set online_color for the Moderators group?

		// Do the update of the membergroup settings.
		$smfFunc['db_query']('', "
			UPDATE {$db_prefix}membergroups
			SET group_name = '$_POST[group_name]', online_color = '$_POST[online_color]',
				max_messages = $_POST[max_messages], min_posts = $_POST[min_posts], stars = '$_POST[stars]',
				description = '$_POST[group_desc]', group_type = $_POST[group_type], hidden = $_POST[group_hidden],
				id_parent = $_POST[group_inherit]
			WHERE id_group = " . (int) $_REQUEST['group'] . "
			LIMIT 1", __FILE__, __LINE__);

		// Time to update the boards this membergroup has access to.
		if ($_REQUEST['group'] == 2 || $_REQUEST['group'] > 3)
		{
			$_POST['boardaccess'] = empty($_POST['boardaccess']) || !is_array($_POST['boardaccess']) ? array() : $_POST['boardaccess'];
			foreach ($_POST['boardaccess'] as $key => $value)
				$_POST['boardaccess'][$key] = (int) $value;

			// Find all board this group is in, but shouldn't be in.
			$request = $smfFunc['db_query']('', "
				SELECT id_board, member_groups
				FROM {$db_prefix}boards
				WHERE FIND_IN_SET(" . (int) $_REQUEST['group'] . ", member_groups)" . (empty($_POST['boardaccess']) ? '' : "
					AND id_board NOT IN (" . implode(', ', $_POST['boardaccess']) . ')'), __FILE__, __LINE__);
			while ($row = $smfFunc['db_fetch_assoc']($request))
				$smfFunc['db_query']('', "
					UPDATE {$db_prefix}boards
					SET member_groups = '" . implode(',', array_diff(explode(',', $row['member_groups']), array($_REQUEST['group']))) . "'
					WHERE id_board = $row[id_board]
					LIMIT 1", __FILE__, __LINE__);
			$smfFunc['db_free_result']($request);

			// Add the membergroup to all boards that hadn't been set yet.
			if (!empty($_POST['boardaccess']))
				$smfFunc['db_query']('', "
					UPDATE {$db_prefix}boards
					SET member_groups = CASE WHEN member_groups = '' THEN '" . (int) $_REQUEST['group'] . "' ELSE CONCAT(member_groups, '," . (int) $_REQUEST['group'] . "') END
					WHERE id_board IN (" . implode(', ', $_POST['boardaccess']) . ")
						AND NOT FIND_IN_SET(" . (int) $_REQUEST['group'] . ", member_groups)", __FILE__, __LINE__);
		}

		// Remove everyone from this group!
		if ($_POST['min_posts'] != -1)
		{
			$smfFunc['db_query']('', "
				UPDATE {$db_prefix}members
				SET id_group = 0
				WHERE id_group = " . (int) $_REQUEST['group'], __FILE__, __LINE__);

			$request = $smfFunc['db_query']('', "
				SELECT id_member, additional_groups
				FROM {$db_prefix}members
				WHERE FIND_IN_SET(" . (int) $_REQUEST['group'] . ", additional_groups)", __FILE__, __LINE__);
			$updates = array();
			while ($row = $smfFunc['db_fetch_assoc']($request))
				$updates[$row['additional_groups']][] = $row['id_member'];
			$smfFunc['db_free_result']($request);

			foreach ($updates as $additional_groups => $memberArray)
				updateMemberData($memberArray, array('additional_groups' => '\'' . implode(',', array_diff(explode(',', $additional_groups), array((int) $_REQUEST['group']))) . '\''));
		}
		elseif ($_REQUEST['group'] != 3)
		{
			// Making it a hidden group? If so remove everyone with it as primary group (Actually, just make them additional).
			if ($_POST['group_hidden'])
			{
				$request = $smfFunc['db_query']('', "
					SELECT id_member, additional_groups
					FROM {$db_prefix}members
					WHERE id_group = " . (int) $_REQUEST['group'] . "
						AND NOT FIND_IN_SET(" . (int) $_REQUEST['group'] . ", additional_groups)", __FILE__, __LINE__);
				$updates = array();
				while ($row = $smfFunc['db_fetch_assoc']($request))
					$updates[$row['additional_groups']][] = $row['id_member'];
				$smfFunc['db_free_result']($request);

				foreach ($updates as $additional_groups => $memberArray)
					updateMemberData($memberArray, array('additional_groups' => '\'' . implode(',', array_merge(explode(',', $additional_groups), array((int) $_REQUEST['group']))) . '\''));

				$smfFunc['db_query']('', "
					UPDATE {$db_prefix}members
					SET id_group = 0
					WHERE id_group = " . $_REQUEST['group'], __FILE__, __LINE__);
			}

			// Either way, let's check our "show group membership" setting is correct.
			$request = $smfFunc['db_query']('', "
				SELECT COUNT(*)
				FROM {$db_prefix}membergroups
				WHERE group_type != 0", __FILE__, __LINE__);
			list ($have_joinable) = $smfFunc['db_fetch_row']($request);
			$smfFunc['db_free_result']($request);

			// Do we need to update the setting?
			if ((empty($modSettings['show_group_membership']) && $have_joinable) || (!empty($modSettings['show_group_membership']) && !$have_joinable))
				updateSettings(array('show_group_membership' => $have_joinable ? 1 : 0));
		}

		// Do we need to set inherited permissions?
		if ($_POST['group_inherit'] != -2 && $_POST['group_inherit'] != $_POST['old_inherit'])
		{
			require_once($sourcedir . '/ManagePermissions.php');
			updateChildPermissions($_POST['group_inherit']);
		}

		// Finally, moderators!
		$moderator_string = isset($_POST['group_moderators']) ? trim($_POST['group_moderators']) : '';
		$smfFunc['db_query']('', "
			DELETE FROM {$db_prefix}group_moderators
			WHERE id_group = $_REQUEST[group]", __FILE__, __LINE__);
		if (!empty($moderator_string) && $_POST['min_posts'] == -1 && $_REQUEST['group'] != 3)
		{
			// Get all the usernames from the string
			$moderator_string = strtr(preg_replace('~&amp;#(\d{4,5}|[2-9]\d{2,4}|1[2-9]\d);~', '&#$1;', htmlspecialchars(stripslashes($moderator_string), ENT_QUOTES)), array('&quot;' => '"'));
			preg_match_all('~"([^"]+)"~', $moderator_string, $matches);
			$moderators = array_merge($matches[1], explode(',', preg_replace('~"([^"]+)"~', '', $moderator_string)));
			for ($k = 0, $n = count($moderators); $k < $n; $k++)
			{
				$moderators[$k] = trim($moderators[$k]);

				if (strlen($moderators[$k]) == 0)
					unset($moderators[$k]);
			}

			// Find all the ID_MEMBERs for the member_name's in the list.
			$group_moderators = array();
			if (!empty($moderators))
			{
				$request = $smfFunc['db_query']('', "
					SELECT id_member
					FROM {$db_prefix}members
					WHERE member_name IN ('" . implode("','", $moderators) . "') OR real_name IN ('" . implode("','", $moderators) . "')
					LIMIT " . count($moderators), __FILE__, __LINE__);
				while ($row = $smfFunc['db_fetch_assoc']($request))
					$group_moderators[] = $row['id_member'];
				$smfFunc['db_free_result']($request);
			}

			// Found some?
			if (!empty($group_moderators))
			{
				$mod_insert = array();
				foreach ($group_moderators as $moderator)
					$mod_insert[] = "($_REQUEST[group], $moderator)";

				$smfFunc['db_query']('', "
					INSERT INTO {$db_prefix}group_moderators
						(id_group, id_member)
					VALUES " . implode(', ', $mod_insert), __FILE__, __LINE__);
			}
		}

		// There might have been some post group changes.
		updateStats('postgroups');
		// We've definetely changed some group stuff.
		updateSettings(array('settings_updated' => time()));

		// Finally, recache the groups.
		cacheGroups();

		redirectexit('action=admin;area=membergroups');
	}

	// Fetch the current group information.
	$request = $smfFunc['db_query']('', "
		SELECT group_name, description, min_posts, online_color, max_messages, stars, group_type, hidden, id_parent
		FROM {$db_prefix}membergroups
		WHERE id_group = " . (int) $_REQUEST['group'] . "
		LIMIT 1", __FILE__, __LINE__);
	if ($smfFunc['db_num_rows']($request) == 0)
		fatal_lang_error('membergroup_does_not_exist', false);
	$row = $smfFunc['db_fetch_assoc']($request);
	$smfFunc['db_free_result']($request);

	$row['stars'] = explode('#', $row['stars']);

	$context['group'] = array(
		'id' => $_REQUEST['group'],
		'name' => $row['group_name'],
		'description' => htmlspecialchars($row['description']),
		'editable_name' => htmlspecialchars($row['group_name']),
		'color' => $row['online_color'],
		'min_posts' => $row['min_posts'],
		'max_messages' => $row['max_messages'],
		'star_count' => (int) $row['stars'][0],
		'star_image' => isset($row['stars'][1]) ? $row['stars'][1] : '',
		'is_post_group' => $row['min_posts'] != -1,
		'type' => $row['min_posts'] != -1 ? 0 : $row['group_type'],
		'hidden' => $row['min_posts'] == -1 ? $row['hidden'] : 0,
		'inherited_from' => $row['id_parent'],
		'allow_post_group' => $_REQUEST['group'] == 2 || $_REQUEST['group'] > 4,
		'allow_delete' => $_REQUEST['group'] == 2 || $_REQUEST['group'] > 4,
	);

	// Get any moderators for this group
	$request = $smfFunc['db_query']('', "
		SELECT mem.real_name
		FROM ({$db_prefix}group_moderators AS mods, {$db_prefix}members AS mem)
		WHERE mods.id_group = $_REQUEST[group]
			AND mem.id_member = mods.id_member", __FILE__, __LINE__);
	$context['group']['moderators'] = array();
	while ($row = $smfFunc['db_fetch_assoc']($request))
		$context['group']['moderators'][] = $row['real_name'];
	$smfFunc['db_free_result']($request);

	$context['group']['moderator_list'] = empty($context['group']['moderators']) ? '' : '&quot;' . implode('&quot;, &quot;', $context['group']['moderators']) . '&quot;';

	// Get a list of boards this membergroup is allowed to see.
	$context['boards'] = array();
	if ($_REQUEST['group'] == 2 || $_REQUEST['group'] > 3)
	{
		$result = $smfFunc['db_query']('', "
			SELECT id_board, name, child_level, FIND_IN_SET(" . (int) $_REQUEST['group'] . ", member_groups) AS can_access
			FROM {$db_prefix}boards", __FILE__, __LINE__);
		while ($row = $smfFunc['db_fetch_assoc']($result))
			$context['boards'][] = array(
				'id' => $row['id_board'],
				'name' => $row['name'],
				'child_level' => $row['child_level'],
				'selected' => !empty($row['can_access']),
			);
		$smfFunc['db_free_result']($result);
	}

	// Finally, get all the groups this could be inherited off.
	$request = $smfFunc['db_query']('', "
		SELECT id_group, group_name
		FROM {$db_prefix}membergroups
		WHERE id_group != " . (int) $_REQUEST['group'] .
			(empty($modSettings['permission_enable_postgroups']) ? "
			AND min_posts = -1" : '') . "
			AND id_group NOT IN (1, 3)
			AND id_parent = -2", __FILE__, __LINE__);
	$context['inheritable_groups'] = array();
	while ($row = $smfFunc['db_fetch_assoc']($request))
		$context['inheritable_groups'][$row['id_group']] = $row['group_name'];
	$smfFunc['db_free_result']($request);

	$context['sub_template'] = 'edit_group';
	$context['page_title'] = $txt['membergroups_edit_group'];
}

// Set general membergroup settings.
function ModifyMembergroupsettings()
{
	global $context, $db_prefix, $sourcedir, $modSettings, $txt;

	$context['sub_template'] = 'membergroup_settings';
	$context['page_title'] = $txt['membergroups_settings'];

	// Needed for the inline permission functions.
	require_once($sourcedir . '/ManagePermissions.php');

	if (!empty($_POST['save_settings']))
	{
		checkSession();

		// Save the permissions.
		save_inline_permissions(array('manage_membergroups'));
	}

	// Initialize permissions.
	init_inline_permissions(array('manage_membergroups'), array(-1));
}

// Cache the public membergroups.
function cacheGroups()
{
	global $db_prefix, $settings, $modSettings, $smfFunc;

	// Check whether we need to cache anything?
	$request = $smfFunc['db_query']('', "
		SELECT value
		FROM {$db_prefix}themes
		WHERE variable = 'show_group_key'
		LIMIT 1", __FILE__, __LINE__);
	$enabled = $smfFunc['db_num_rows']($request) != 0;
	$smfFunc['db_free_result']($request);

	// If we don't need it delete it.
	if (!$enabled && isset($modSettings['groupCache']))
	{
		$smfFunc['db_query']('', "
			DELETE FROM {$db_prefix}settings
			WHERE variable = 'groupCache'", __FILE__, __LINE__);
	}
	elseif ($enabled)
	{
		$request = $smfFunc['db_query']('', "
			SELECT id_group, group_name, online_color
			FROM {$db_prefix}membergroups
			WHERE min_posts = -1
				AND hidden = 0
				AND id_group != 3
				AND online_color != ''", __FILE__, __LINE__);
		$groupCache = array();
		// This looks weird but it's here for speed!
		while ($row = $smfFunc['db_fetch_assoc']($request))
			$groupCache[] = $row['id_group'] . '" ' . ($row['online_color'] ? 'style="color: ' . $row['online_color'] . '"' : '') . '>' . $row['group_name'];
		$groupCache = addslashes(serialize($groupCache));

		updateSettings(array('groupCache' => $groupCache));
	}
}

?>