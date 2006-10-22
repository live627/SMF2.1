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
	global $db_prefix, $txt, $scripturl, $context, $settings;

	$context['page_title'] = $txt['membergroups_title'];

	$context['groups'] = array(
		'regular' => array(),
		'post' => array()
	);

	$query = db_query("
		SELECT ID_GROUP, groupName, minPosts, onlineColor, stars
		FROM {$db_prefix}membergroups
		ORDER BY minPosts, IF(ID_GROUP < 4, ID_GROUP, 4), groupName", __FILE__, __LINE__);
	while ($row = mysql_fetch_assoc($query))
	{
		$row['stars'] = explode('#', $row['stars']);
		$context['groups'][$row['minPosts'] == -1 ? 'regular' : 'post'][$row['ID_GROUP']] = array(
			'id' => $row['ID_GROUP'],
			'name' => $row['groupName'],
			'num_members' => $row['ID_GROUP'] != 3 ? 0 : $txt['membergroups_guests_na'],
			'allow_delete' => $row['ID_GROUP'] > 4,
			'can_search' => $row['ID_GROUP'] != 3,
			'href' => $scripturl . '?action=admin;area=membergroups;sa=members;group=' . $row['ID_GROUP'],
			'is_post_group' => $row['minPosts'] != -1,
			'min_posts' => $row['minPosts'] == -1 ? '-' : $row['minPosts'],
			'color' => empty($row['onlineColor']) ? '' : $row['onlineColor'],
			'stars' => !empty($row['stars'][0]) && !empty($row['stars'][1]) ? str_repeat('<img src="' . $settings['images_url'] . '/' . $row['stars'][1] . '" alt="*" border="0" />', $row['stars'][0]) : '',
		);
	}
	mysql_free_result($query);

	if (!empty($context['groups']['post']))
	{
		$query = db_query("
			SELECT ID_POST_GROUP AS ID_GROUP, COUNT(*) AS num_members
			FROM {$db_prefix}members
			WHERE ID_POST_GROUP IN (" . implode(', ', array_keys($context['groups']['post'])) . ")
			GROUP BY ID_POST_GROUP", __FILE__, __LINE__);
		while ($row = mysql_fetch_assoc($query))
			$context['groups']['post'][$row['ID_GROUP']]['num_members'] += $row['num_members'];
		mysql_free_result($query);
	}

	if (!empty($context['groups']['regular']))
	{
		$query = db_query("
			SELECT ID_GROUP, COUNT(*) AS num_members
			FROM {$db_prefix}members
			WHERE ID_GROUP IN (" . implode(', ', array_keys($context['groups']['regular'])) . ")
			GROUP BY ID_GROUP", __FILE__, __LINE__);
		while ($row = mysql_fetch_assoc($query))
			$context['groups']['regular'][$row['ID_GROUP']]['num_members'] += $row['num_members'];
		mysql_free_result($query);

		$query = db_query("
			SELECT mg.ID_GROUP, COUNT(*) AS num_members
			FROM ({$db_prefix}membergroups AS mg, {$db_prefix}members AS mem)
			WHERE mg.ID_GROUP IN (" . implode(', ', array_keys($context['groups']['regular'])) . ")
				AND mem.additionalGroups != ''
				AND mem.ID_GROUP != mg.ID_GROUP
				AND FIND_IN_SET(mg.ID_GROUP, mem.additionalGroups)
			GROUP BY mg.ID_GROUP", __FILE__, __LINE__);
		while ($row = mysql_fetch_assoc($query))
			$context['groups']['regular'][$row['ID_GROUP']]['num_members'] += $row['num_members'];
		mysql_free_result($query);
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
	global $db_prefix, $context, $txt, $sourcedir, $modSettings;

	// A form was submitted, we can start adding.
	if (!empty($_POST['group_name']))
	{
		checkSession();

		$postCountBasedGroup = isset($_POST['min_posts']) && (!isset($_POST['postgroup_based']) || !empty($_POST['postgroup_based']));

		// !!! Check for members with same name too?

		$request = db_query("
			SELECT MAX(ID_GROUP)
			FROM {$db_prefix}membergroups", __FILE__, __LINE__);
		list ($ID_GROUP) = mysql_fetch_row($request);
		mysql_free_result($request);
		$ID_GROUP++;

		db_query("
			INSERT INTO {$db_prefix}membergroups
				(ID_GROUP, groupName, minPosts, stars, onlineColor)
			VALUES ($ID_GROUP, SUBSTRING('$_POST[group_name]', 1, 80), " . ($postCountBasedGroup ? (int) $_POST['min_posts'] : '-1') . ", '1#star.gif', '')", __FILE__, __LINE__);

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
			setPermissionLevel($_POST['level'], $ID_GROUP, 'null');
		}
		// Copy or inherit the permissions!
		elseif ($_POST['perm_type'] == 'copy' || $_POST['perm_type'] == 'inherit')
		{
			$copy_id = $_POST['perm_type'] == 'copy' ? (int) $_POST['copyperm'] : (int) $_POST['inheritperm'];

			$request = db_query("
				SELECT permission, addDeny
				FROM {$db_prefix}permissions
				WHERE ID_GROUP = $copy_id", __FILE__, __LINE__);
			$setString = '';
			while ($row = mysql_fetch_assoc($request))
				$setString .= "
					($ID_GROUP, '$row[permission]', $row[addDeny]),";
			mysql_free_result($request);

			if (!empty($setString))
				db_query("
					INSERT INTO {$db_prefix}permissions
						(ID_GROUP, permission, addDeny)
					VALUES" . substr($setString, 0, -1), __FILE__, __LINE__);

			$request = db_query("
				SELECT ID_PROFILE, permission, addDeny
				FROM {$db_prefix}board_permissions
				WHERE ID_GROUP = $copy_id", __FILE__, __LINE__);
			$setString = '';
			while ($row = mysql_fetch_assoc($request))
				$setString .= "
					($ID_GROUP, $row[ID_PROFILE], '$row[permission]', $row[addDeny]),";
			mysql_free_result($request);

			if (!empty($setString))
				db_query("
					INSERT INTO {$db_prefix}board_permissions
						(ID_GROUP, ID_PROFILE, permission, addDeny)
					VALUES" . substr($setString, 0, -1), __FILE__, __LINE__);

			// Also get some membergroup information if we're copying and not copying from guests...
			if ($copy_id > 0 && $_POST['perm_type'] == 'copy')
			{
				$request = db_query("
					SELECT onlineColor, maxMessages, stars
					FROM {$db_prefix}membergroups
					WHERE ID_GROUP = $copy_id
					LIMIT 1", __FILE__, __LINE__);
				$group_info = mysql_fetch_assoc($request);
				mysql_free_result($request);

				// ...and update the new membergroup with it.
				db_query("
					UPDATE {$db_prefix}membergroups
					SET
						onlineColor = '$group_info[onlineColor]',
						maxMessages = $group_info[maxMessages],
						stars = '$group_info[stars]'
					WHERE ID_GROUP = $ID_GROUP
					LIMIT 1", __FILE__, __LINE__);
			}
			// If inheriting say so...
			elseif ($_POST['perm_type'] == 'inherit')
			{
				db_query("
					UPDATE {$db_prefix}membergroups
					SET ID_PARENT = $copy_id
					WHERE ID_GROUP = $ID_GROUP
					LIMIT 1", __FILE__, __LINE__);
			}
		}

		// Make sure all boards selected are stored in a proper array.
		$_POST['boardaccess'] = empty($_POST['boardaccess']) || !is_array($_POST['boardaccess']) ? array() : $_POST['boardaccess'];
		foreach ($_POST['boardaccess'] as $key => $value)
			$_POST['boardaccess'][$key] = (int) $value;

		// Only do this if they have special access requirements.
		if (!empty($_POST['boardaccess']))
			db_query("
				UPDATE {$db_prefix}boards
				SET memberGroups = IF(memberGroups = '', '$ID_GROUP', CONCAT(memberGroups, ',$ID_GROUP'))
				WHERE ID_BOARD IN (" . implode(', ', $_POST['boardaccess']) . ")
				LIMIT " . count($_POST['boardaccess']), __FILE__, __LINE__);

		// Rebuild the group cache.
		cacheGroups();

		// Go change some more settings.
		redirectexit('action=admin;area=membergroups;sa=edit;group=' . $ID_GROUP);
	}

	// Just show the 'add membergroup' screen.
	$context['page_title'] = $txt['membergroups_new_group'];
	$context['sub_template'] = 'new_group';
	$context['post_group'] = !empty($_REQUEST['postgroup']);
	$context['undefined_group'] = empty($_REQUEST['postgroup']) && empty($_REQUEST['generalgroup']);

	$result = db_query("
		SELECT ID_GROUP, groupName
		FROM {$db_prefix}membergroups
		WHERE (ID_GROUP > 3 OR ID_GROUP = 2)" . (empty($modSettings['permission_enable_postgroups']) ? "
			AND minPosts = -1" : '') . "
		ORDER BY minPosts, ID_GROUP != 2, groupName", __FILE__, __LINE__);
	$context['groups'] = array();
	while ($row = mysql_fetch_assoc($result))
		$context['groups'][] = array(
			'id' => $row['ID_GROUP'],
			'name' => $row['groupName']
		);
	mysql_free_result($result);

	$result = db_query("
		SELECT ID_BOARD, name, childLevel
		FROM {$db_prefix}boards", __FILE__, __LINE__);
	$context['boards'] = array();
	while ($row = mysql_fetch_assoc($result))
		$context['boards'][] = array(
			'id' => $row['ID_BOARD'],
			'name' => $row['name'],
			'child_level' => $row['childLevel'],
			'selected' => false
		);
	mysql_free_result($result);
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
	global $db_prefix, $context, $txt, $sourcedir, $modSettings;

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

		// !!! Don't set onlineColor for the Moderators group?

		// Do the update of the membergroup settings.
		db_query("
			UPDATE {$db_prefix}membergroups
			SET groupName = '$_POST[group_name]', onlineColor = '$_POST[online_color]',
				maxMessages = $_POST[max_messages], minPosts = $_POST[min_posts], stars = '$_POST[stars]',
				description = '$_POST[group_desc]', groupType = $_POST[group_type], hidden = $_POST[group_hidden],
				ID_PARENT = $_POST[group_inherit]
			WHERE ID_GROUP = " . (int) $_REQUEST['group'] . "
			LIMIT 1", __FILE__, __LINE__);

		// Time to update the boards this membergroup has access to.
		if ($_REQUEST['group'] == 2 || $_REQUEST['group'] > 3)
		{
			$_POST['boardaccess'] = empty($_POST['boardaccess']) || !is_array($_POST['boardaccess']) ? array() : $_POST['boardaccess'];
			foreach ($_POST['boardaccess'] as $key => $value)
				$_POST['boardaccess'][$key] = (int) $value;

			// Find all board this group is in, but shouldn't be in.
			$request = db_query("
				SELECT ID_BOARD, memberGroups
				FROM {$db_prefix}boards
				WHERE FIND_IN_SET(" . (int) $_REQUEST['group'] . ", memberGroups)" . (empty($_POST['boardaccess']) ? '' : "
					AND ID_BOARD NOT IN (" . implode(', ', $_POST['boardaccess']) . ')'), __FILE__, __LINE__);
			while ($row = mysql_fetch_assoc($request))
				db_query("
					UPDATE {$db_prefix}boards
					SET memberGroups = '" . implode(',', array_diff(explode(',', $row['memberGroups']), array($_REQUEST['group']))) . "'
					WHERE ID_BOARD = $row[ID_BOARD]
					LIMIT 1", __FILE__, __LINE__);
			mysql_free_result($request);

			// Add the membergroup to all boards that hadn't been set yet.
			if (!empty($_POST['boardaccess']))
				db_query("
					UPDATE {$db_prefix}boards
					SET memberGroups = IF(memberGroups = '', '" . (int) $_REQUEST['group'] . "', CONCAT(memberGroups, '," . (int) $_REQUEST['group'] . "'))
					WHERE ID_BOARD IN (" . implode(', ', $_POST['boardaccess']) . ")
						AND NOT FIND_IN_SET(" . (int) $_REQUEST['group'] . ", memberGroups)", __FILE__, __LINE__);
		}

		// Remove everyone from this group!
		if ($_POST['min_posts'] != -1)
		{
			db_query("
				UPDATE {$db_prefix}members
				SET ID_GROUP = 0
				WHERE ID_GROUP = " . (int) $_REQUEST['group'], __FILE__, __LINE__);

			$request = db_query("
				SELECT ID_MEMBER, additionalGroups
				FROM {$db_prefix}members
				WHERE FIND_IN_SET(" . (int) $_REQUEST['group'] . ", additionalGroups)", __FILE__, __LINE__);
			$updates = array();
			while ($row = mysql_fetch_assoc($request))
				$updates[$row['additionalGroups']][] = $row['ID_MEMBER'];
			mysql_free_result($request);

			foreach ($updates as $additionalGroups => $memberArray)
				updateMemberData($memberArray, array('additionalGroups' => '\'' . implode(',', array_diff(explode(',', $additionalGroups), array((int) $_REQUEST['group']))) . '\''));
		}
		elseif ($_REQUEST['group'] != 3)
		{
			// Making it a hidden group? If so remove everyone with it as primary group (Actually, just make them additional).
			if ($_POST['group_hidden'])
			{
				$request = db_query("
					SELECT ID_MEMBER, additionalGroups
					FROM {$db_prefix}members
					WHERE ID_GROUP = " . (int) $_REQUEST['group'] . "
						AND NOT FIND_IN_SET(" . (int) $_REQUEST['group'] . ", additionalGroups)", __FILE__, __LINE__);
				$updates = array();
				while ($row = mysql_fetch_assoc($request))
					$updates[$row['additionalGroups']][] = $row['ID_MEMBER'];
				mysql_free_result($request);

				foreach ($updates as $additionalGroups => $memberArray)
					updateMemberData($memberArray, array('additionalGroups' => '\'' . implode(',', array_merge(explode(',', $additionalGroups), array((int) $_REQUEST['group']))) . '\''));

				db_query("
					UPDATE {$db_prefix}members
					SET ID_GROUP = 0
					WHERE ID_GROUP = " . $_REQUEST['group'], __FILE__, __LINE__);
			}

			// Either way, let's check our "show group membership" setting is correct.
			$request = db_query("
				SELECT COUNT(*)
				FROM {$db_prefix}membergroups
				WHERE groupType != 0", __FILE__, __LINE__);
			list ($have_joinable) = mysql_fetch_row($request);
			mysql_free_result($request);

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
		db_query("
			DELETE FROM {$db_prefix}group_moderators
			WHERE ID_GROUP = $_REQUEST[group]", __FILE__, __LINE__);
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

			// Find all the ID_MEMBERs for the memberName's in the list.
			$group_moderators = array();
			if (!empty($moderators))
			{
				$request = db_query("
					SELECT ID_MEMBER
					FROM {$db_prefix}members
					WHERE memberName IN ('" . implode("','", $moderators) . "') OR realName IN ('" . implode("','", $moderators) . "')
					LIMIT " . count($moderators), __FILE__, __LINE__);
				while ($row = mysql_fetch_assoc($request))
					$group_moderators[] = $row['ID_MEMBER'];
				mysql_free_result($request);
			}

			// Found some?
			if (!empty($group_moderators))
			{
				$mod_insert = array();
				foreach ($group_moderators as $moderator)
					$mod_insert[] = "($_REQUEST[group], $moderator)";

				db_query("
					INSERT INTO {$db_prefix}group_moderators
						(ID_GROUP, ID_MEMBER)
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
	$request = db_query("
		SELECT groupName, description, minPosts, onlineColor, maxMessages, stars, groupType, hidden, ID_PARENT
		FROM {$db_prefix}membergroups
		WHERE ID_GROUP = " . (int) $_REQUEST['group'] . "
		LIMIT 1", __FILE__, __LINE__);
	if (mysql_num_rows($request) == 0)
		fatal_lang_error('membergroup_does_not_exist', false);
	$row = mysql_fetch_assoc($request);
	mysql_free_result($request);

	$row['stars'] = explode('#', $row['stars']);

	$context['group'] = array(
		'id' => $_REQUEST['group'],
		'name' => $row['groupName'],
		'description' => htmlspecialchars($row['description']),
		'editable_name' => htmlspecialchars($row['groupName']),
		'color' => $row['onlineColor'],
		'min_posts' => $row['minPosts'],
		'max_messages' => $row['maxMessages'],
		'star_count' => (int) $row['stars'][0],
		'star_image' => isset($row['stars'][1]) ? $row['stars'][1] : '',
		'is_post_group' => $row['minPosts'] != -1,
		'type' => $row['minPosts'] != -1 ? 0 : $row['groupType'],
		'hidden' => $row['minPosts'] == -1 ? $row['hidden'] : 0,
		'inherited_from' => $row['ID_PARENT'],
		'allow_post_group' => $_REQUEST['group'] == 2 || $_REQUEST['group'] > 4,
		'allow_delete' => $_REQUEST['group'] == 2 || $_REQUEST['group'] > 4,
	);

	// Get any moderators for this group
	$request = db_query("
		SELECT mem.realName
		FROM ({$db_prefix}group_moderators AS mods, {$db_prefix}members AS mem)
		WHERE mods.ID_GROUP = $_REQUEST[group]
			AND mem.ID_MEMBER = mods.ID_MEMBER", __FILE__, __LINE__);
	$context['group']['moderators'] = array();
	while ($row = mysql_fetch_assoc($request))
		$context['group']['moderators'][] = $row['realName'];
	mysql_free_result($request);

	$context['group']['moderator_list'] = empty($context['group']['moderators']) ? '' : '&quot;' . implode('&quot;, &quot;', $context['group']['moderators']) . '&quot;';

	// Get a list of boards this membergroup is allowed to see.
	$context['boards'] = array();
	if ($_REQUEST['group'] == 2 || $_REQUEST['group'] > 3)
	{
		$result = db_query("
			SELECT ID_BOARD, name, childLevel, FIND_IN_SET(" . (int) $_REQUEST['group'] . ", memberGroups) AS can_access
			FROM {$db_prefix}boards", __FILE__, __LINE__);
		while ($row = mysql_fetch_assoc($result))
			$context['boards'][] = array(
				'id' => $row['ID_BOARD'],
				'name' => $row['name'],
				'child_level' => $row['childLevel'],
				'selected' => !empty($row['can_access']),
			);
		mysql_free_result($result);
	}

	// Finally, get all the groups this could be inherited off.
	$request = db_query("
		SELECT ID_GROUP, groupName
		FROM {$db_prefix}membergroups
		WHERE ID_GROUP != " . (int) $_REQUEST['group'] .
			(empty($modSettings['permission_enable_postgroups']) ? "
			AND minPosts = -1" : '') . "
			AND ID_GROUP NOT IN (1, 3)
			AND ID_PARENT = -2", __FILE__, __LINE__);
	$context['inheritable_groups'] = array();
	while ($row = mysql_fetch_assoc($request))
		$context['inheritable_groups'][$row['ID_GROUP']] = $row['groupName'];
	mysql_free_result($request);

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
	global $db_prefix, $settings, $modSettings;

	// Check whether we need to cache anything?
	$request = db_query("
		SELECT value
		FROM {$db_prefix}themes
		WHERE variable = 'show_group_key'
		LIMIT 1", __FILE__, __LINE__);
	$enabled = mysql_num_rows($request) != 0;
	mysql_free_result($request);

	// If we don't need it delete it.
	if (!$enabled && isset($modSettings['groupCache']))
	{
		db_query("
			DELETE FROM {$db_prefix}settings
			WHERE variable = 'groupCache'", __FILE__, __LINE__);
	}
	elseif ($enabled)
	{
		$request = db_query("
			SELECT ID_GROUP, groupName, onlineColor
			FROM {$db_prefix}membergroups
			WHERE minPosts = -1
				AND hidden = 0
				AND ID_GROUP != 3
				AND onlineColor != ''", __FILE__, __LINE__);
		$groupCache = array();
		// This looks weird but it's here for speed!
		while ($row = mysql_fetch_assoc($request))
			$groupCache[] = $row['ID_GROUP'] . '" ' . ($row['onlineColor'] ? 'style="color: ' . $row['onlineColor'] . '"' : '') . '>' . $row['groupName'];
		$groupCache = addslashes(serialize($groupCache));

		updateSettings(array('groupCache' => $groupCache));
	}
}

?>