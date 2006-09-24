<?php
/******************************************************************************
* ManagePermissions.php                                                       *
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

/*	ManagePermissions handles all possible permission stuff. The following
	functions are used:

   void ModifyPermissions()
      - calls the right function based on the given subaction.
      - checks the permissions, based on the sub-action.
      - called by ?action=managepermissions.
      - loads the ManagePermissions language file.

   void PermissionIndex()
      - sets up the permissions by membergroup index page.
      - called by ?action=managepermissions
      - uses the permission_index template of the ManageBoards template.
      - loads the ManagePermissions language and template.
      - creates an array of all the groups with the number of members and permissions.

	void SetQuickGroups()
		- handles permission modification actions from the upper part of the
		  permission manager index.
		// !!!

	void SwitchBoard()
		// !!!

	void ModifyMembergroup()
		- modify (local and global) permissions.
		// !!!

	void ModifyMembergroup2()
		// !!!

	void GeneralPermissionSettings()
		- a screen to set some general settings for permissions.

	void setPermissionLevel(string level, int group, int profile = 'null')
		- internal function to modify permissions to a pre-defined profile.
		// !!!

	void loadAllPermissions()
		- internal function to load permissions into $context['permissions'].
		// !!!

	void loadPermissionProfiles()
		// !!!

	void EditPermissionProfiles()
		// !!!

	void init_inline_permissions(array permissions)
		- internal function to initialise the inline permission settings.
		- loads the ManagePermissions language and template.
		- loads a context variables for each permission.
		- used by several settings screens to set specific permissions.

	void theme_inline_permissions(string permission)
		- function called by templates to show a list of permissions settings.
		- calls the template function template_inline_permissions().

	save_inline_permissions(array permissions)
		- general function to save the inline permissions sent by a form.
		- does no session check.

	void updateChildPermissions(array parent, int profile = null)
		// !!!
*/

function ModifyPermissions()
{
	global $txt, $scripturl, $context;

	loadLanguage('ManagePermissions');
	loadTemplate('ManagePermissions');

	// Format: 'sub-action' => array('function_to_call', 'permission_needed'),
	$subActions = array(
		'board' => array('PermissionByBoard', 'manage_permissions'),
		'index' => array('PermissionIndex', 'manage_permissions'),
		'modify' => array('ModifyMembergroup', 'manage_permissions'),
		'modify2' => array('ModifyMembergroup2', 'manage_permissions'),
		'quick' => array('SetQuickGroups', 'manage_permissions'),
		'quickboard' => array('SetQuickBoards', 'manage_permissions'),
		'profiles' => array('EditPermissionProfiles', 'manage_permissions'),
		'settings' => array('GeneralPermissionSettings', 'admin_forum'),
		'switch' => array('SwitchBoard', 'manage_permissions'),
	);

	$_REQUEST['sa'] = isset($_REQUEST['sa']) && isset($subActions[$_REQUEST['sa']]) ? $_REQUEST['sa'] : (allowedTo('manage_permissions') ? 'index' : 'settings');
	isAllowedTo($subActions[$_REQUEST['sa']][1]);

	// Create the tabs for the template.
	$context['admin_tabs'] = array(
		'title' => $txt['permissions_title'],
		'help' => 'permissions',
		'description' => '',
		'tabs' => array(),
	);
	if (allowedTo('manage_permissions'))
	{
		$context['admin_tabs']['tabs']['index'] = array(
			'title' => $txt['permissions_groups'],
			'description' => $txt['permission_by_membergroup_desc'],
			'href' => $scripturl . '?action=admin;area=permissions',
			'is_selected' => in_array($_REQUEST['sa'], array('modify', 'index')) && empty($_REQUEST['boardid']),
		);
		$context['admin_tabs']['tabs']['board_permissions'] = array(
			'title' => $txt['permissions_boards'],
			'description' => $txt['permission_by_board_desc'],
			'href' => $scripturl . '?action=admin;area=permissions;sa=board',
			'is_selected' => in_array($_REQUEST['sa'], array('board', 'switch', 'profiles')) || (in_array($_REQUEST['sa'], array('modify', 'index')) && !empty($_REQUEST['boardid'])),
			'is_last' => !allowedTo('admin_forum'),
		);
	}
	if (allowedTo('admin_forum'))
		$context['admin_tabs']['tabs']['settings'] = array(
			'title' => $txt['settings'],
			'description' => $txt['permission_settings_desc'],
			'href' => $scripturl . '?action=admin;area=permissions;sa=settings',
			'is_selected' => $_REQUEST['sa'] == 'settings',
			'is_last' => true,
		);

	$subActions[$_REQUEST['sa']][0]();
}

function PermissionIndex()
{
	global $db_prefix, $txt, $scripturl, $context, $settings, $modSettings;

	$context['page_title'] = $txt['permissions_title'];

	// Load all the permissions. We'll need them in the template.
	loadAllPermissions();

	// Also load profiles, we may want to reset.
	loadPermissionProfiles();

	// Determine the number of ungrouped members.
	$request = db_query("
		SELECT COUNT(*)
		FROM {$db_prefix}members
		WHERE ID_GROUP = 0", __FILE__, __LINE__);
	list ($num_members) = mysql_fetch_row($request);
	mysql_free_result($request);

	// Fill the context variable with 'Guests' and 'Regular Members'.
	$context['groups'] = array(
		-1 => array(
			'id' => -1,
			'name' => $txt['membergroups_guests'],
			'num_members' => $txt['membergroups_guests_na'],
			'allow_delete' => false,
			'allow_modify' => true,
			'can_search' => false,
			'href' => '',
			'link' => '',
			'is_post_group' => false,
			'color' => '',
			'stars' => '',
			'children' => array(),
			'num_permissions' => array(
				'allowed' => 0,
				// Can't deny guest permissions!
				'denied' => '(' . $txt['permissions_none'] . ')'
			),
			'access' => false
		),
		0 => array(
			'id' => 0,
			'name' => $txt['membergroups_members'],
			'num_members' => $num_members,
			'allow_delete' => false,
			'allow_modify' => true,
			'can_search' => true,
			'href' => $scripturl . '?action=admin;area=viewmembers;sa=query;params=' . base64_encode('ID_GROUP = 0'),
			'is_post_group' => false,
			'color' => '',
			'stars' => '',
			'children' => array(),
			'num_permissions' => array(
				'allowed' => 0,
				'denied' => 0
			),
			'access' => false
		),
	);

	$postGroups = array();
	$normalGroups = array();

	// Query the database defined membergroups.
	$query = db_query("
		SELECT ID_GROUP, ID_PARENT, groupName, minPosts, onlineColor, stars
		FROM {$db_prefix}membergroups" . (empty($modSettings['permission_enable_postgroups']) ? "
		WHERE minPosts = -1" : '') . "
		ORDER BY ID_PARENT = -2 DESC, minPosts, IF(ID_GROUP < 4, ID_GROUP, 4), groupName", __FILE__, __LINE__);
	while ($row = mysql_fetch_assoc($query))
	{
		// If it's inherited just ass it as a child.
		if ($row['ID_PARENT'] != -2)
		{
			if (isset($context['groups'][$row['ID_PARENT']]))
			{
				$context['groups'][$row['ID_PARENT']]['children'][$row['ID_GROUP']] = $row['groupName'];
			}
			continue;
		}

		$row['stars'] = explode('#', $row['stars']);
		$context['groups'][$row['ID_GROUP']] = array(
			'id' => $row['ID_GROUP'],
			'name' => $row['groupName'],
			'num_members' => $row['ID_GROUP'] != 3 ? 0 : $txt['membergroups_guests_na'],
			'allow_delete' => $row['ID_GROUP'] > 4,
			'allow_modify' => $row['ID_GROUP'] > 1,
			'can_search' => $row['ID_GROUP'] != 3,
			'href' => $scripturl . '?action=admin;area=viewmembers;sa=query;params=' . base64_encode($row['minPosts'] == -1 ? "ID_GROUP = $row[ID_GROUP] OR FIND_IN_SET($row[ID_GROUP], additionalGroups)" : "ID_POST_GROUP = $row[ID_GROUP]"),
			'is_post_group' => $row['minPosts'] != -1,
			'color' => empty($row['onlineColor']) ? '' : $row['onlineColor'],
			'stars' => !empty($row['stars'][0]) && !empty($row['stars'][1]) ? str_repeat('<img src="' . $settings['images_url'] . '/' . $row['stars'][1] . '" alt="*" border="0" />', $row['stars'][0]) : '',
			'children' => array(),
			'num_permissions' => array(
				'allowed' => $row['ID_GROUP'] == 1 ? '(' . $txt['permissions_all'] . ')' : 0,
				'denied' => $row['ID_GROUP'] == 1 ? '(' . $txt['permissions_none'] . ')' : 0
			),
			'access' => false,
		);

		if ($row['minPosts'] == -1)
			$normalGroups[$row['ID_GROUP']] = $row['ID_GROUP'];
		else
			$postGroups[$row['ID_GROUP']] = $row['ID_GROUP'];
	}
	mysql_free_result($query);

	// Get the number of members in this post group.
	if (!empty($postGroups))
	{
		$query = db_query("
			SELECT ID_POST_GROUP AS ID_GROUP, COUNT(*) AS num_members
			FROM {$db_prefix}members
			WHERE ID_POST_GROUP IN (" . implode(', ', $postGroups) . ")
			GROUP BY ID_POST_GROUP", __FILE__, __LINE__);
		while ($row = mysql_fetch_assoc($query))
			$context['groups'][$row['ID_GROUP']]['num_members'] += $row['num_members'];
		mysql_free_result($query);
	}

	if (!empty($normalGroups))
	{
		// First, the easy one!
		$query = db_query("
			SELECT ID_GROUP, COUNT(*) AS num_members
			FROM {$db_prefix}members
			WHERE ID_GROUP IN (" . implode(', ', $normalGroups) . ")
			GROUP BY ID_GROUP", __FILE__, __LINE__);
		while ($row = mysql_fetch_assoc($query))
			$context['groups'][$row['ID_GROUP']]['num_members'] += $row['num_members'];
		mysql_free_result($query);

		// This one is slower, but it's okay... careful not to count twice!
		$query = db_query("
			SELECT mg.ID_GROUP, COUNT(*) AS num_members
			FROM ({$db_prefix}membergroups AS mg, {$db_prefix}members AS mem)
			WHERE mg.ID_GROUP IN (" . implode(', ', $normalGroups) . ")
				AND mem.additionalGroups != ''
				AND mem.ID_GROUP != mg.ID_GROUP
				AND FIND_IN_SET(mg.ID_GROUP, mem.additionalGroups)
			GROUP BY mg.ID_GROUP", __FILE__, __LINE__);
		while ($row = mysql_fetch_assoc($query))
			$context['groups'][$row['ID_GROUP']]['num_members'] += $row['num_members'];
		mysql_free_result($query);
	}

	foreach ($context['groups'] as $id => $data)
	{
		if ($data['href'] != '')
			$context['groups'][$id]['link'] = '<a href="' . $data['href'] . '">' . $data['num_members'] . '</a>';
	}

/*
	// !!! Why is this here and commented out?

	$board_groups = array();
	foreach ($context['groups'] as $group)
		if ($group['allow_modify'])
			$board_groups[$group['id']] = array(
				'id' => &$group['id'],
				'name' => &$group['name'],
				'num_permissions' => array(
					'allowed' => 0,
					'denied' => 0
				),
			);
*/

	if (empty($_REQUEST['pid']))
	{
		$request = db_query("
			SELECT ID_GROUP, COUNT(*) AS numPermissions, addDeny
			FROM {$db_prefix}permissions
			GROUP BY ID_GROUP, addDeny", __FILE__, __LINE__);
		while ($row = mysql_fetch_assoc($request))
			if (isset($context['groups'][(int) $row['ID_GROUP']]) && (!empty($row['addDeny']) || $row['ID_GROUP'] != -1))
				$context['groups'][(int) $row['ID_GROUP']]['num_permissions'][empty($row['addDeny']) ? 'denied' : 'allowed'] = $row['numPermissions'];
		mysql_free_result($request);

		// Get the "default" profile permissions too.
		$request = db_query("
			SELECT ID_PROFILE, ID_GROUP, COUNT(*) AS numPermissions, addDeny
			FROM {$db_prefix}board_permissions
			WHERE ID_PROFILE = 1
			GROUP BY ID_GROUP, addDeny", __FILE__, __LINE__);
		while ($row = mysql_fetch_assoc($request))
		{
			if (isset($context['groups'][(int) $row['ID_GROUP']]) && (!empty($row['addDeny']) || $row['ID_GROUP'] != -1))
				$context['groups'][(int) $row['ID_GROUP']]['num_permissions'][empty($row['addDeny']) ? 'denied' : 'allowed'] += $row['numPermissions'];
		}
		mysql_free_result($request);
	}
	else
	{
		$_REQUEST['pid'] = (int) $_REQUEST['pid'];

		// Change the selected tab to better reflect that this really is a board profile.
		$context['admin_tabs']['tabs']['board_permissions']['is_selected'] = true;
		$context['admin_tabs']['tabs']['index']['is_selected'] = false;

		$request = db_query("
			SELECT ID_PROFILE, ID_GROUP, COUNT(*) AS numPermissions, addDeny
			FROM {$db_prefix}board_permissions
			WHERE ID_PROFILE = $_REQUEST[pid]
			GROUP BY ID_PROFILE, ID_GROUP, addDeny", __FILE__, __LINE__);
		while ($row = mysql_fetch_assoc($request))
		{
			if (isset($context['groups'][(int) $row['ID_GROUP']]) && (!empty($row['addDeny']) || $row['ID_GROUP'] != -1))
				$context['groups'][(int) $row['ID_GROUP']]['num_permissions'][empty($row['addDeny']) ? 'denied' : 'allowed'] += $row['numPermissions'];
		}
		mysql_free_result($request);

		$context['profile'] = array(
			'id' => $_REQUEST['pid'],
			'name' => $context['profiles'][$_REQUEST['pid']]['parent'] ? ' &quot;' . $context['profiles'][$_REQUEST['pid']]['name'] . '&quot;' : $context['profiles'][$_REQUEST['pid']]['name'],
		);
	}

	// Load the proper template.
	$context['sub_template'] = 'permission_index';
}

function PermissionByBoard()
{
	global $context, $db_prefix, $modSettings, $txt;

	$context['page_title'] = $txt['permissions_boards'];

	// Load all permission profiles.
	loadPermissionProfiles();

	$request = db_query("
		SELECT b.ID_BOARD, b.name, COUNT(mods.ID_MEMBER) AS moderators, b.memberGroups, b.childLevel,
			b.ID_PROFILE
		FROM {$db_prefix}boards AS b
			LEFT JOIN {$db_prefix}categories AS c ON (c.ID_CAT = b.ID_CAT)
			LEFT JOIN {$db_prefix}moderators AS mods ON (mods.ID_BOARD = b.ID_BOARD)
		GROUP BY ID_BOARD
		ORDER BY boardOrder", __FILE__, __LINE__);
	$context['boards'] = array();
	while ($row = mysql_fetch_assoc($request))
	{
		// Format the profile name.
		$profile_name = $context['profiles'][$row['ID_PROFILE']]['name'];

		// If it has a parent format the text accordingly.
		if ($context['profiles'][$row['ID_PROFILE']]['parent'])
			$profile_name = '<i>' . sprintf($txt['permissions_profile_' . ($context['profiles'][$row['ID_PROFILE']]['parent'] == $row['ID_BOARD'] ? 'custom' : 'as_board')], $profile_name) . '</i>';
		else
			$profile_name = '<b>' . $profile_name . '</b>';

		$row['memberGroups'] = explode(',', $row['memberGroups']);
		$context['boards'][$row['ID_BOARD']] = array(
			'id' => $row['ID_BOARD'],
			'child_level' => $row['childLevel'],
			'name' => $row['name'],
			'num_moderators' => $row['moderators'],
			'public' => in_array(0, $row['memberGroups']) || in_array(-1, $row['memberGroups']),
			'membergroups' => $row['memberGroups'],
			'profile' => $row['ID_PROFILE'],
			'profile_name' => $profile_name,
		);
	}
	mysql_free_result($request);

	$context['sub_template'] = 'by_board';
}

function SetQuickGroups()
{
	global $db_prefix;

	checkSession();

	// Make sure only one of the quick options was selected.
	if ((!empty($_POST['predefined']) && ((isset($_POST['copy_from']) && $_POST['copy_from'] != 'empty') || !empty($_POST['permissions']))) || (!empty($_POST['copy_from']) && $_POST['copy_from'] != 'empty' && !empty($_POST['permissions'])))
		fatal_lang_error('permissions_only_one_option', false);

	if (empty($_POST['group']) || !is_array($_POST['group']))
		$_POST['group'] = array();

	// Only accept numeric values for selected membergroups.
	foreach ($_POST['group'] as $id => $group_id)
		$_POST['group'][$id] = (int) $group_id;
	$_POST['group'] = array_unique($_POST['group']);

	if (empty($_REQUEST['pid']))
		$_REQUEST['pid'] = 0;
	else
		$_REQUEST['pid'] = (int) $_REQUEST['pid'];

	// Fix up the old global to the new default!
	$bid = max(1, $_REQUEST['pid']);

	// Clear out any cached authority.
	updateSettings(array('settings_updated' => time()));

	// No groups where selected.
	if (empty($_POST['group']))
		redirectexit('action=admin;area=permissions;pid=' . $_REQUEST['pid']);

	// Set a predefined permission profile.
	if (!empty($_POST['predefined']))
	{
		// Make sure it's a predefined permission set we expect.
		if (!in_array($_POST['predefined'], array('restrict', 'standard', 'moderator', 'maintenance')))
			redirectexit('action=admin;area=permissions;pid=' . $_REQUEST['pid']);

		foreach ($_POST['group'] as $group_id)
		{
			if (!empty($_REQUEST['pid']))
				setPermissionLevel($_POST['predefined'], $group_id, $_REQUEST['pid']);
			else
				setPermissionLevel($_POST['predefined'], $group_id);
		}
	}
	// Set a permission profile based on the permissions of a selected group.
	elseif ($_POST['copy_from'] != 'empty')
	{
		// Just checking the input.
		if (!is_numeric($_POST['copy_from']))
			redirectexit('action=admin;area=permissions;pid=' . $_REQUEST['pid']);

		// Make sure the group we're copying to is never included.
		$_POST['group'] = array_diff($_POST['group'], array($_POST['copy_from']));

		// No groups left? Too bad.
		if (empty($_POST['group']))
			redirectexit('action=admin;area=permissions;pid=' . $_REQUEST['pid']);

		if (empty($_REQUEST['pid']))
		{
			// Retrieve current permissions of group.
			$request = db_query("
				SELECT permission, addDeny
				FROM {$db_prefix}permissions
				WHERE ID_GROUP = $_POST[copy_from]", __FILE__, __LINE__);
			$target_perm = array();
			while ($row = mysql_fetch_assoc($request))
				$target_perm[$row['permission']] = $row['addDeny'];
			mysql_free_result($request);

			$insert_string = '';
			foreach ($_POST['group'] as $group_id)
				foreach ($target_perm as $perm => $addDeny)
					$insert_string .= "('$perm', $group_id, $addDeny),";

			// Delete the previous permissions...
			db_query("
				DELETE FROM {$db_prefix}permissions
				WHERE ID_GROUP IN (" . implode(', ', $_POST['group']) . ")", __FILE__, __LINE__);

			if (!empty($insert_string))
			{
				// Cut off the last comma.
				$insert_string = substr($insert_string, 0, -1);

				// ..and insert the new ones.
				db_query("
					INSERT IGNORE INTO {$db_prefix}permissions
						(permission, ID_GROUP, addDeny)
					VALUES $insert_string", __FILE__, __LINE__);
			}
		}

		// Now do the same for the board permissions.
		$request = db_query("
			SELECT permission, addDeny
			FROM {$db_prefix}board_permissions
			WHERE ID_GROUP = $_POST[copy_from]
				AND ID_PROFILE = $bid", __FILE__, __LINE__);
		$target_perm = array();
		while ($row = mysql_fetch_assoc($request))
			$target_perm[$row['permission']] = $row['addDeny'];
		mysql_free_result($request);

		$insert_string = '';
		foreach ($_POST['group'] as $group_id)
			foreach ($target_perm as $perm => $addDeny)
				$insert_string .= "('$perm', $group_id, $bid, $addDeny),";

		// Delete the previous global board permissions...
		db_query("
			DELETE FROM {$db_prefix}board_permissions
			WHERE ID_GROUP IN (" . implode(', ', $_POST['group']) . ")
				AND ID_PROFILE = $bid", __FILE__, __LINE__);

		// And insert the copied permissions.
		if (!empty($insert_string))
		{
			$insert_string = substr($insert_string, 0, -1);

			db_query("
				INSERT IGNORE INTO {$db_prefix}board_permissions
					(permission, ID_GROUP, ID_PROFILE, addDeny)
				VALUES $insert_string", __FILE__, __LINE__);
		}

		// Update any children out there!
		updateChildPermissions($_POST['group'], $_REQUEST['pid']);
	}
	// Set or unset a certain permission for the selected groups.
	elseif (!empty($_POST['permissions']))
	{
		// Unpack two variables that were transported.
		list ($permissionType, $permission) = explode('/', $_POST['permissions']);

		// Check whether our input is within expected range.
		if (!in_array($_POST['add_remove'], array('add', 'clear', 'deny')) || !in_array($permissionType, array('membergroup', 'board')))
			redirectexit('action=admin;area=permissions;pid=' . $_REQUEST['pid']);

		if ($_POST['add_remove'] == 'clear')
		{
			if ($permissionType == 'membergroup')
				db_query("
					DELETE FROM {$db_prefix}permissions
					WHERE ID_GROUP IN (" . implode(', ', $_POST['group']) . ")
						AND permission = '$permission'", __FILE__, __LINE__);
			else
				db_query("
					DELETE FROM {$db_prefix}board_permissions
					WHERE ID_GROUP IN (" . implode(', ', $_POST['group']) . ")
						AND ID_PROFILE = $bid
						AND permission = '$permission'", __FILE__, __LINE__);
		}
		// Add a permission (either 'set' or 'deny').
		else
		{
			$addDeny = $_POST['add_remove'] == 'add' ? '1' : '0';
			if ($permissionType == 'membergroup')
				db_query("
					REPLACE INTO {$db_prefix}permissions
						(permission, ID_GROUP, addDeny)
					VALUES
						('$permission', " . implode(", $addDeny),
						('$permission', ", $_POST['group']) . ", $addDeny)", __FILE__, __LINE__);
			// Board permissions go into the other table.
			else
				db_query("
					REPLACE INTO {$db_prefix}board_permissions
						(permission, ID_GROUP, ID_PROFILE, addDeny)
					VALUES
						('$permission', " . implode(", $bid, $addDeny),
						('$permission', ", $_POST['group']) . ", $bid, $addDeny)", __FILE__, __LINE__);
		}

		// Another child update!
		updateChildPermissions($_POST['group'], $_REQUEST['pid']);
	}

	redirectexit('action=admin;area=permissions;pid=' . $_REQUEST['pid']);
}

// Switch a board from one permission profile to another.
function SwitchBoard()
{
	global $db_prefix, $modSettings, $context, $txt;

	$_GET['boardid'] = (int) $_GET['boardid'];

	// Get the permission profile for this board ;)
	loadPermissionProfiles();

	// Load the board details.
	$request = db_query("
		SELECT ID_BOARD, name, ID_PROFILE
		FROM {$db_prefix}boards
		WHERE ID_BOARD = $_GET[boardid]", __FILE__, __LINE__);
	if (mysql_num_rows($request) == 0)
		redirectexit('action=admin;area=permissions;sa=board');
	while ($row = mysql_fetch_assoc($request))
	{
		$context['board'] = array(
			'id' => $row['ID_BOARD'],
			'name' => $row['name'],
			'profile' => $row['ID_PROFILE'],
		);
	}
	mysql_free_result($request);

	// Cycle through the permission profile types and sort them.
	$context['predefined_profiles'] = array();
	$context['board_profiles'] = array();
	$context['profile_type'] = 'predefined';
	foreach ($context['profiles'] as $id => $profile)
	{
		// Current profile?
		if ($id == $context['board']['profile'])
		{
			// Is custom for this one?
			if ($profile['parent'] == $context['board']['id'])
				$context['profile_type'] = 'custom';
			// Or a slave?
			elseif ($profile['parent'])
				$context['profile_type'] = 'as_board';
		}

		// No parent? Must be predefined!
		if (!$profile['parent'])
			$context['predefined_profiles'][] = array(
				'id' => $profile['id'],
				'name' => $profile['name'],
			);
		// Otherwise it's an another board!
		elseif ($profile['parent'] != $context['board']['id'])
			$context['board_profiles'][] = array(
				'id' => $profile['id'],
				'name' => $profile['name'],
			);
			
	}

	// Are we doing some saving?
	if (isset($_REQUEST['save']))
	{
		// Security above all.
		checkSession(isset($_GET['customize']) ? 'get' : 'post');
		validateSession();

		// If the user clicked customize of some form, we need to save it and direct to the customize page.
		if (isset($_GET['customize']) || $_POST['profile_type'] == 'custom')
		{
			// If it was already a custom one then nothing changes.
			if ($context['profile_type'] == 'custom')
				$profile_id = $context['board']['profile'];
			// Otherwise we need to create a new profile for this board.
			else
			{
				db_query("
					INSERT INTO {$db_prefix}permission_profiles
						(profile_name, ID_PARENT)
					VALUES
						('', $_GET[boardid])", __FILE__, __LINE__);
				// Get the new number.
				$profile_id = db_insert_id();

				// Assuming it worked copy the previous profile across.
				$request = db_query("
					SELECT ID_GROUP, permission, addDeny
					FROM {$db_prefix}board_permissions
					WHERE ID_PROFILE = " . $context['board']['profile'], __FILE__, __LINE__);
				$inserts = array();
				while ($row = mysql_fetch_assoc($request))
					$inserts[] = "($profile_id, $row[ID_GROUP], '$row[permission]', $row[addDeny])";
				mysql_free_result($request);

				if (!empty($inserts))
					db_query("
						INSERT INTO {$db_prefix}board_permissions
							(ID_PROFILE, ID_GROUP, permission, addDeny)
						VALUES
							" . implode(',', $inserts), __FILE__, __LINE__);

				// Link the board to the profile.
				db_query("
					UPDATE {$db_prefix}boards
					SET ID_PROFILE = $profile_id
					WHERE ID_BOARD = $_GET[boardid]", __FILE__, __LINE__);

				updateSettings(array('settings_updated' => time()));
			}

			// Customize right away?
			if (isset($_GET['customize']) || $context['profile_type'] != 'custom')
				redirectexit('action=admin;area=permissions;sa=index;pid=' . $profile_id);
			else
				redirectexit('action=admin;area=permissions;sa=board');
		}

		// Otherwise it's a simple case of using another profile.
		$profile_id = $_POST['profile_type'] == 'as_board' ? $_POST['as_board'] : $_POST['predefined'];
		$profile_id = (int) $profile_id;

		// Just for sanity!
		if (!$profile_id || !isset($context['profiles'][$profile_id]))
			fatal_lang_error(1);

		// Only bother with bits if it's changing!
		if ($profile_id != $context['board']['profile'])
		{
			// Update the board first.
			db_query("
				UPDATE {$db_prefix}boards
				SET ID_PROFILE = $profile_id
				WHERE ID_BOARD = $_GET[boardid]", __FILE__, __LINE__);

			$old_profile = $context['board']['profile'];
			// If this board used to have "slaves" they should follow?
			if ($old_profile != 1 && $context['profiles'][$old_profile]['parent'] == $_GET['boardid'])
			{
				// All the old sk00l boards get this new profile.
				db_query("
					UPDATE {$db_prefix}boards
					SET ID_PROFILE = $profile_id
					WHERE ID_PROFILE = $old_profile", __FILE__, __LINE__);

				// The old permissions are gone, the old profile is dead!
				db_query("
					DELETE FROM {$db_prefix}board_permissions
					WHERE ID_PROFILE = $old_profile", __FILE__, __LINE__);
				db_query("
					DELETE FROM {$db_prefix}permission_profiles
					WHERE ID_PROFILE = $old_profile", __FILE__, __LINE__);
			}

			// Void the caches...
			updateSettings(array('settings_updated' => time()));
		}

		// Back to permissions.
		redirectexit('action=admin;area=permissions;sa=board');
	}

	// Finally, just the template stuff.
	$context['sub_template'] = 'switch_profiles';
	$context['page_title'] = sprintf($txt['permissions_profiles_change_for_board'], $context['board']['name']);
}

function ModifyMembergroup()
{
	global $db_prefix, $context, $txt, $modSettings;

	// It's not likely you'd end up here with this setting disabled.
	if ($_GET['group'] == 1)
		redirectexit('action=admin;area=permissions');

	$context['group']['id'] = (int) $_GET['group'];

	loadAllPermissions();
	loadPermissionProfiles();

	if ($context['group']['id'] > 0)
	{
		$result = db_query("
			SELECT groupName, ID_PARENT
			FROM {$db_prefix}membergroups
			WHERE ID_GROUP = {$context['group']['id']}
			LIMIT 1", __FILE__, __LINE__);
		list ($context['group']['name'], $parent) = mysql_fetch_row($result);
		mysql_free_result($result);

		// Cannot edit an inherited group!
		if ($parent != -2)
			fatal_lang_error('cannot_edit_permissions_inherited');
	}
	elseif ($context['group']['id'] == -1)
		$context['group']['name'] = &$txt['membergroups_guests'];
	else
		$context['group']['name'] = &$txt['membergroups_members'];

	$context['profile']['id'] = empty($_GET['pid']) ? 0 : (int) $_GET['pid'];
	$context['local'] = !empty($_GET['pid']);

	// Set up things a little nicer for board related stuff...
	if ($context['local'])
	{
		$context['profile']['name'] = $context['profiles'][$_GET['pid']]['name'];
		$context['admin_tabs']['tabs']['board_permissions']['is_selected'] = true;
		$context['admin_tabs']['tabs']['index']['is_selected'] = false;
	}

	// Fetch the current permissions.
	$permissions = array(
		'membergroup' => array('allowed' => array(), 'denied' => array()),
		'board' => array('allowed' => array(), 'denied' => array())
	);
	if ($context['group']['id'] != 3 && !$context['local'])
	{
		$result = db_query("
			SELECT permission, addDeny
			FROM {$db_prefix}permissions
			WHERE ID_GROUP = $_GET[group]", __FILE__, __LINE__);
		while ($row = mysql_fetch_assoc($result))
			$permissions['membergroup'][empty($row['addDeny']) ? 'denied' : 'allowed'][] = $row['permission'];
		mysql_free_result($result);
		$context['permissions']['membergroup']['show'] = true;
	}
	else
		$context['permissions']['membergroup']['show'] = false;

	// Fetch current board permissions.
	$result = db_query("
		SELECT permission, addDeny
		FROM {$db_prefix}board_permissions
		WHERE ID_GROUP = {$context['group']['id']}
			AND ID_PROFILE = " . max(1, $context['profile']['id']), __FILE__, __LINE__);

	while ($row = mysql_fetch_assoc($result))
		$permissions['board'][empty($row['addDeny']) ? 'denied' : 'allowed'][] = $row['permission'];
	mysql_free_result($result);
	$context['permissions']['board']['show'] = true;

	// Loop through each permission and set whether it's checked.
	foreach ($context['permissions'] as $permissionType => $tmp)
	{
		foreach ($tmp['columns'] as $position => $permissionGroups)
		{
			foreach ($permissionGroups as $permissionGroup => $permissionArray)
			{
				foreach ($permissionArray['permissions'] as $perm)
				{
					// Create a shortcut for the current permission.
					$curPerm = &$context['permissions'][$permissionType]['columns'][$position][$permissionGroup]['permissions'][$perm['id']];
					if ($perm['has_own_any'])
					{
						$curPerm['any']['select'] = in_array($perm['id'] . '_any', $permissions[$permissionType]['allowed']) ? 'on' : (in_array($perm['id'] . '_any', $permissions[$permissionType]['denied']) ? 'denied' : 'off');
						$curPerm['own']['select'] = in_array($perm['id'] . '_own', $permissions[$permissionType]['allowed']) ? 'on' : (in_array($perm['id'] . '_own', $permissions[$permissionType]['denied']) ? 'denied' : 'off');
					}
					else
						$curPerm['select'] = in_array($perm['id'], $permissions[$permissionType]['denied']) ? 'denied' : (in_array($perm['id'], $permissions[$permissionType]['allowed']) ? 'on' : 'off');
				}
			}
		}
	}
	$context['sub_template'] = 'modify_group';
	$context['page_title'] = $txt['permissions_modify_group'];
}

function ModifyMembergroup2()
{
	global $db_prefix, $modSettings;

	checkSession();

	$_GET['group'] = (int) $_GET['group'];
	$_GET['pid'] = (int) $_GET['pid'];

	// Verify this isn't inherited.
	if ($_GET['group'] == -1 || $_GET['group'] == 0)
		$parent = -2;
	else
	{
		$result = db_query("
			SELECT ID_PARENT
			FROM {$db_prefix}membergroups
			WHERE ID_GROUP = $_GET[group]
			LIMIT 1", __FILE__, __LINE__);
		list ($parent) = mysql_fetch_row($result);
		mysql_free_result($result);
	}

	if ($parent != -2)
		fatal_lang_error('cannot_edit_permissions_inherited');

	$givePerms = array('membergroup' => array(), 'board' => array());

	// Prepare all permissions that were set or denied for addition to the DB.
	if (isset($_POST['perm']) && is_array($_POST['perm']))
	{
		foreach ($_POST['perm'] as $perm_type => $perm_array)
		{
			if (is_array($perm_array))
			{
				foreach ($perm_array as $permission => $value)
					if ($value == 'on' || $value == 'deny')
						$givePerms[$perm_type][] = "$permission', " . ($value == 'deny' ? '0' : '1');
			}
		}
	}

	// Insert the general permissions.
	if ($_GET['group'] != 3 && empty($_GET['pid']))
	{
		db_query("
			DELETE FROM {$db_prefix}permissions
			WHERE ID_GROUP = $_GET[group]", __FILE__, __LINE__);
		if (!empty($givePerms['membergroup']))
			db_query("
				INSERT IGNORE INTO {$db_prefix}permissions
					(ID_GROUP, permission, addDeny)
				VALUES ($_GET[group], '" . implode("),
					($_GET[group], '", $givePerms['membergroup']) . ")", __FILE__, __LINE__);
	}

	// Insert the boardpermissions.
	$profileid = max(1, $_GET['pid']);
	db_query("
		DELETE FROM {$db_prefix}board_permissions
		WHERE ID_GROUP = $_GET[group]
			AND ID_PROFILE = $profileid", __FILE__, __LINE__);
	if (!empty($givePerms['board']))
		db_query("
			INSERT IGNORE INTO {$db_prefix}board_permissions
				(ID_GROUP, ID_PROFILE, permission, addDeny)
			VALUES ($_GET[group], $profileid, '" . implode("),
				($_GET[group], $profileid, '", $givePerms['board']) . ")", __FILE__, __LINE__);

	// Update any inherited permissions as required.
	updateChildPermissions($_GET['group'], $_GET['pid']);

	// Clear cached privs.
	updateSettings(array('settings_updated' => time()));

	redirectexit('action=admin;area=permissions;pid=' . $_GET['pid']);
}

// Screen for modifying general permission settings.
function GeneralPermissionSettings()
{
	global $context, $db_prefix, $modSettings, $sourcedir, $txt, $scripturl;

	$context['page_title'] = $txt['permission_settings_title'];
	$context['sub_template'] = 'show_settings';

	// Needed for the inline permission functions, and the settings template.
	require_once($sourcedir .'/ManagePermissions.php');
	require_once($sourcedir .'/ManageServer.php');

	// All the setting variables
	$config_vars = array(
		array('title', 'settings'),
			// Inline permissions.
			array('permissions', 'manage_permissions'),
		'',
			// A few useful settings
			array('check', 'permission_enable_deny', 0, $txt['permission_settings_enable_deny'], 'help' => 'permissions_deny'),
			array('check', 'permission_enable_postgroups', 0, $txt['permission_settings_enable_postgroups'], 'help' => 'permissions_postgroups'),
	);

	// Don't let guests have these permissions.
	$context['post_url'] = $scripturl . '?action=admin;area=permissions;save;sa=settings';
	$context['permissions_excluded'] = array(-1);

	// Saving the settings?
	if (isset($_GET['save']))
	{
		saveDBSettings($config_vars);

		// Clear all deny permissions...if we want that.
		if (empty($modSettings['permission_enable_deny']))
		{
			db_query("
				DELETE FROM {$db_prefix}permissions
				WHERE addDeny = 0", __FILE__, __LINE__);
			db_query("
				DELETE FROM {$db_prefix}board_permissions
				WHERE addDeny = 0", __FILE__, __LINE__);
		}

		// Make sure there are no postgroup based permissions left.
		if (empty($modSettings['permission_enable_postgroups']))
		{
			// Get a list of postgroups.
			$post_groups = array();
			$request = db_query("
				SELECT ID_GROUP
				FROM {$db_prefix}membergroups
				WHERE minPosts != -1", __FILE__, __LINE__);
			while ($row = mysql_fetch_assoc($request))
				$post_groups[] = $row['ID_GROUP'];
			mysql_free_result($request);

			// Remove'em.
			db_query("
				DELETE FROM {$db_prefix}permissions
				WHERE ID_GROUP IN (" . implode(', ', $post_groups) . ')', __FILE__, __LINE__);
			db_query("
				DELETE FROM {$db_prefix}board_permissions
				WHERE ID_GROUP IN (" . implode(', ', $post_groups) . ')', __FILE__, __LINE__);
			db_query("
				UPDATE {$db_prefix}membergroups
				SET ID_PARENT = -2
				WHERE ID_PARENT IN (" . implode(', ', $post_groups) . ')', __FILE__, __LINE__);
		}

		redirectexit('action=admin;area=permissions;sa=settings');
	}

	prepareDBSettingContext($config_vars);
}

// Set the permission level for a specific profile, group, or group for a profile.
function setPermissionLevel($level, $group, $profile = 'null')
{
	global $db_prefix;

	// Levels by group... restrict, standard, moderator, maintenance.
	$groupLevels = array(
		'board' => array('inherit' => array()),
		'group' => array('inherit' => array())
	);
	// Levels by board... standard, publish, free.
	$boardLevels = array('inherit' => array());

	// Restrictive - ie. guests.
	$groupLevels['global']['restrict'] = array(
		'search_posts',
		'calendar_view',
		'view_stats',
		'who_view',
		'profile_view_own',
		'profile_identity_own',
	);
	$groupLevels['board']['restrict'] = array(
		'poll_view',
		'post_new',
		'post_reply_own',
		'post_reply_any',
		'delete_own',
		'modify_own',
		'mark_any_notify',
		'mark_notify',
		'report_any',
		'send_topic',
	);

	// Standard - ie. members.  They can do anything Restrictive can.
	$groupLevels['global']['standard'] = array_merge($groupLevels['global']['restrict'], array(
		'view_mlist',
		'karma_edit',
		'pm_read',
		'pm_send',
		'profile_view_any',
		'profile_extra_own',
		'profile_server_avatar',
		'profile_upload_avatar',
		'profile_remote_avatar',
		'profile_remove_own',
	));
	$groupLevels['board']['standard'] = array_merge($groupLevels['board']['restrict'], array(
		'poll_vote',
		'poll_edit_own',
		'poll_post',
		'poll_add_own',
		'post_attachment',
		'lock_own',
		'remove_own',
		'view_attachments',
	));

	// Moderator - ie. moderators :P.  They can do what standard can, and more.
	$groupLevels['global']['moderator'] = array_merge($groupLevels['global']['standard'], array(
		'calendar_post',
		'calendar_edit_own',
		'access_mod_center',
	));
	$groupLevels['board']['moderator'] = array_merge($groupLevels['board']['standard'], array(
		'make_sticky',
		'poll_edit_any',
		'delete_any',
		'modify_any',
		'lock_any',
		'remove_any',
		'move_any',
		'merge_any',
		'split_any',
		'poll_lock_any',
		'poll_remove_any',
		'poll_add_any',
		'approve_posts',
	));

	// Maintenance - wannabe admins.  They can do almost everything.
	$groupLevels['global']['maintenance'] = array_merge($groupLevels['global']['moderator'], array(
		'manage_attachments',
		'manage_smileys',
		'manage_boards',
		'moderate_forum',
		'manage_membergroups',
		'manage_bans',
		'admin_forum',
		'manage_permissions',
		'edit_news',
		'calendar_edit_any',
		'profile_identity_any',
		'profile_extra_any',
		'profile_title_any',
	));
	$groupLevels['board']['maintenance'] = array_merge($groupLevels['board']['moderator'], array(
	));

	// Standard - nothing above the group permissions. (this SHOULD be empty.)
	$boardLevels['standard'] = array(
	);

	// Locked - just that, you can't post here.
	$boardLevels['locked'] = array(
		'poll_view',
		'mark_notify',
		'report_any',
		'send_topic',
		'view_attachments',
	);

	// Publisher - just a little more...
	$boardLevels['publish'] = array_merge($boardLevels['locked'], array(
		'post_new',
		'post_reply_own',
		'post_reply_any',
		'delete_own',
		'modify_own',
		'mark_any_notify',
		'delete_replies',
		'modify_replies',
		'poll_vote',
		'poll_edit_own',
		'poll_post',
		'poll_add_own',
		'poll_remove_own',
		'post_attachment',
		'lock_own',
		'remove_own',
	));

	// Free for All - Scary.  Just scary.
	$boardLevels['free'] = array_merge($boardLevels['publish'], array(
		'poll_lock_any',
		'poll_edit_any',
		'poll_add_any',
		'poll_remove_any',
		'make_sticky',
		'lock_any',
		'remove_any',
		'delete_any',
		'split_any',
		'merge_any',
		'modify_any',
		'approve_posts',
	));

	// Reset all cached permissions.
	updateSettings(array('settings_updated' => time()));

	// Setting group permissions.
	if ($profile === 'null' && $group !== 'null')
	{
		$group = (int) $group;

		if (empty($groupLevels['global'][$level]))
			return;

		db_query("
			DELETE FROM {$db_prefix}permissions
			WHERE ID_GROUP = $group", __FILE__, __LINE__);
		db_query("
			DELETE FROM {$db_prefix}board_permissions
			WHERE ID_GROUP = $group
				AND ID_PROFILE = 1", __FILE__, __LINE__);

		db_query("
			INSERT INTO {$db_prefix}permissions
				(ID_GROUP, permission)
			VALUES ($group, '" . implode("'),
				($group, '", $groupLevels['global'][$level]) . "')", __FILE__, __LINE__);
		db_query("
			INSERT INTO {$db_prefix}board_permissions
				(ID_PROFILE, ID_GROUP, permission)
			VALUES (1, $group, '" . implode("'),
				(1, $group, '", $groupLevels['board'][$level]) . "')", __FILE__, __LINE__);
	}
	// Setting profile permissions for a specific group.
	elseif ($profile !== 'null' && $group !== 'null')
	{
		$group = (int) $group;
		$profile = (int) $profile;

		if (!empty($groupLevels['global'][$level]))
		{
			db_query("
				DELETE FROM {$db_prefix}board_permissions
				WHERE ID_GROUP = $group
					AND ID_PROFILE = $profile", __FILE__, __LINE__);
		}

		if (!empty($groupLevels['board'][$level]))
		{
			db_query("
				INSERT INTO {$db_prefix}board_permissions
					(ID_PROFILE, ID_GROUP, permission)
				VALUES ($profile, $group, '" . implode("'),
					($profile, $group, '", $groupLevels['board'][$level]) . "')", __FILE__, __LINE__);
		}
	}
	// Setting profile permissions for all groups.
	elseif ($profile !== 'null' && $group === 'null')
	{
		$profile = (int) $profile;

		db_query("
			DELETE FROM {$db_prefix}board_permissions
			WHERE ID_PROFILE = $profile", __FILE__, __LINE__);

		if (empty($boardLevels[$level]))
			return;

		// Get all the groups...
		$query = db_query("
			SELECT ID_GROUP
			FROM {$db_prefix}membergroups
			WHERE ID_GROUP > 3
			ORDER BY minPosts, IF(ID_GROUP < 4, ID_GROUP, 4), groupName", __FILE__, __LINE__);
		while ($row = mysql_fetch_row($query))
		{
			$group = $row[0];

			db_query("
				INSERT INTO {$db_prefix}board_permissions
					(ID_PROFILE, ID_GROUP, permission)
				VALUES ($profile, $group, '" . implode("'),
					($profile, $group, '", $boardLevels[$level]) . "')", __FILE__, __LINE__);
		}
		mysql_free_result($query);

		// Add permissions for ungrouped members.
		db_query("
			INSERT INTO {$db_prefix}board_permissions
				(ID_PROFILE, ID_GROUP, permission)
			VALUES ($profile, 0, '" . implode("'),
				($profile, 0, '", $boardLevels[$level]) . "')", __FILE__, __LINE__);
	}
	// $profile and $group are both null!
	else
		fatal_lang_error(1, false);
}

function loadAllPermissions()
{
	global $context, $txt;

/*	 The format of this list is as follows:
		'permission_group' => array(
			'permissions_inside' => has_multiple_options,
		),

	   It should be noted that if the permission_group starts with $ it is not treated as a permission.
	   However, if it does not start with $, it is treated as a normal permission.
		$txt['permissionname_' . $permission] is used for the names of permissions.
		$txt['permissiongroup_' . $group] is used for names of groups that start with $.
		$txt['permissionhelp_' . $permission] is used for extended information.
		$txt['permissionicon_' . $permission_or_group] is used for the icons, if it exists.
*/

	$permissionList = array(
		'membergroup' => array(
			'general' => array(
				'view_stats' => false,
				'view_mlist' => false,
				'who_view' => false,
				'search_posts' => false,
				'karma_edit' => false,
			),
			'pm' => array(
				'pm_read' => false,
				'pm_send' => false,
			),
			'calendar' => array(
				'calendar_view' => false,
				'calendar_post' => false,
				'calendar_edit' => true,
			),
			'maintenance' => array(
				'admin_forum' => false,
				'manage_boards' => false,
				'manage_attachments' => false,
				'manage_smileys' => false,
				'edit_news' => false,
				'access_mod_center' => false,
			),
			'member_admin' => array(
				'moderate_forum' => false,
				'manage_membergroups' => false,
				'manage_permissions' => false,
				'manage_bans' => false,
				'send_mail' => false,
			),
			'profile' => array(
				'profile_view' => true,
				'profile_identity' => true,
				'profile_extra' => true,
				'profile_title' => true,
				'profile_remove' => true,
				'profile_server_avatar' => false,
				'profile_upload_avatar' => false,
				'profile_remote_avatar' => false,
			)
		),
		'board' => array(
			'general_board' => array(
				'moderate_board' => false,
			),
			'topic' => array(
				'post_new' => false,
				'merge_any' => false,
				'split_any' => false,
				'send_topic' => false,
				'make_sticky' => false,
				'move' => true,
				'lock' => true,
				'remove' => true,
				'post_reply' => true,
				'modify_replies' => false,
				'delete_replies' => false,
				'announce_topic' => false,
			),
			'post' => array(
				'delete' => true,
				'modify' => true,
				'report_any' => false,
			),
			'poll' => array(
				'poll_view' => false,
				'poll_vote' => false,
				'poll_post' => false,
				'poll_add' => true,
				'poll_edit' => true,
				'poll_lock' => true,
				'poll_remove' => true,
			),
			'approval' => array(
				'approve_posts' => false,
				'post_unapproved_topics' => false,
				'post_unapproved_replies' => true,
				'post_unapproved_attachments' => false,
			),
			'notification' => array(
				'mark_any_notify' => false,
				'mark_notify' => false,
			),
			'attachment' => array(
				'view_attachments' => false,
				'post_attachment' => false,
			)
		)
	);

	// This is just a helpful array of permissions guests... cannot have.
	$non_guest_permissions = array(
		'karma_edit',
		'pm_read',
		'pm_send',
		'profile_identity',
		'profile_extra',
		'profile_title',
		'profile_remove',
		'profile_server_avatar',
		'profile_upload_avatar',
		'profile_remote_avatar',
		'poll_vote',
		'mark_any_notify',
		'mark_notify',
		'admin_forum',
		'manage_boards',
		'manage_attachments',
		'manage_smileys',
		'edit_news',
		'access_mod_center',
		'moderate_forum',
		'manage_membergroups',
		'manage_permissions',
		'manage_bans',
		'send_mail',
	);

	// All permission groups that will be shown in the left column.
	$leftPermissionGroups = array(
		'general',
		'calendar',
		'maintenance',
		'member_admin',
		'general_board',
		'topic',
		'post',
	);

	$context['permissions'] = array();
	foreach ($permissionList as $permissionType => $permissionGroups)
	{
		$context['permissions'][$permissionType] = array(
			'id' => $permissionType,
			'columns' => array(
				'left' => array(),
				'right' => array()
			)
		);
		foreach ($permissionGroups as $permissionGroup => $permissionArray)
		{
			$position = in_array($permissionGroup, $leftPermissionGroups) ? 'left' : 'right';
			$context['permissions'][$permissionType]['columns'][$position][$permissionGroup] = array(
				'type' => $permissionType,
				'id' => $permissionGroup,
				'name' => &$txt['permissiongroup_' . $permissionGroup],
				'icon' => isset($txt['permissionicon_' . $permissionGroup]) ? $txt['permissionicon_' . $permissionGroup] : $txt['permissionicon'],
				'help' => isset($txt['permissionhelp_' . $permissionGroup]) ? $txt['permissionhelp_' . $permissionGroup] : '',
				'permissions' => array()
			);

			foreach ($permissionArray as $perm => $has_own_any)
			{
				if (isset($context['group']['id']) && $context['group']['id'] == -1 && in_array($perm, $non_guest_permissions))
					continue;

				$context['permissions'][$permissionType]['columns'][$position][$permissionGroup]['permissions'][$perm] = array(
					'id' => $perm,
					'name' => &$txt['permissionname_' . $perm],
					'show_help' => isset($txt['permissionhelp_' . $perm]),
					'has_own_any' => $has_own_any,
					'own' => array(
						'id' => $perm . '_own',
						'name' => $has_own_any ? $txt['permissionname_' . $perm . '_own'] : ''
					),
					'any' => array(
						'id' => $perm . '_any',
						'name' => $has_own_any ? $txt['permissionname_' . $perm . '_any'] : ''
					)
				);
			}

			if (empty($context['permissions'][$permissionType]['columns'][$position][$permissionGroup]['permissions']))
				unset($context['permissions'][$permissionType]['columns'][$position][$permissionGroup]);
		}
	}
}

// Initialize a form with inline permissions.
function init_inline_permissions($permissions, $excluded_groups = array())
{
	global $context, $db_prefix, $txt, $modSettings;

	loadLanguage('ManagePermissions');
	loadTemplate('ManagePermissions');
	$context['can_change_permissions'] = allowedTo('manage_permissions');

	// Nothing to initialize here.
	if (!$context['can_change_permissions'])
		return;

	// Load the permission settings for guests
	foreach ($permissions as $permission)
		$context[$permission] = array(
			-1 => array(
				'id' => -1,
				'name' => $txt['membergroups_guests'],
				'is_postgroup' => false,
				'status' => 'off',
			),
			0 => array(
				'id' => 0,
				'name' => $txt['membergroups_members'],
				'is_postgroup' => false,
				'status' => 'off',
			),
		);

	$request = db_query("
		SELECT ID_GROUP, IF(addDeny = 0, 'deny', 'on') AS status, permission
		FROM {$db_prefix}permissions
		WHERE ID_GROUP IN (-1, 0)
			AND permission IN ('" . implode("', '", $permissions) . "')", __FILE__, __LINE__);
	while ($row = mysql_fetch_assoc($request))
		$context[$row['permission']][$row['ID_GROUP']]['status'] = $row['status'];
	mysql_free_result($request);

	$request = db_query("
		SELECT mg.ID_GROUP, mg.groupName, mg.minPosts, IFNULL(p.addDeny, -1) AS status, p.permission
		FROM {$db_prefix}membergroups AS mg
			LEFT JOIN {$db_prefix}permissions AS p ON (p.ID_GROUP = mg.ID_GROUP AND p.permission  IN ('" . implode("', '", $permissions) . "'))
		WHERE mg.ID_GROUP NOT IN (1, 3)
			AND mg.ID_PARENT = -2" . (empty($modSettings['permission_enable_postgroups']) ? "
			AND mg.minPosts = -1" : '') . "
		ORDER BY mg.minPosts, IF(mg.ID_GROUP < 4, mg.ID_GROUP, 4), mg.groupName", __FILE__, __LINE__);
	while ($row = mysql_fetch_assoc($request))
	{
		// Initialize each permission as being 'off' until proven otherwise.
		foreach ($permissions as $permission)
			if (!isset($context[$permission][$row['ID_GROUP']]))
				$context[$permission][$row['ID_GROUP']] = array(
					'id' => $row['ID_GROUP'],
					'name' => $row['groupName'],
					'is_postgroup' => $row['minPosts'] != -1,
					'status' => 'off',
				);

		$context[$row['permission']][$row['ID_GROUP']]['status'] = empty($row['status']) ? 'deny' : ($row['status'] == 1 ? 'on' : 'off');
	}
	mysql_free_result($request);

	// Some permissions cannot be given to certain groups. Remove the groups.
	foreach ($excluded_groups as $group)
	{
		foreach ($permissions as $permission)
		{
			if (isset($context[$permission][$group]))
				unset($context[$permission][$group]);
		}
	}
}

// Show a collapsible box to set a specific permission.
function theme_inline_permissions($permission)
{
	global $context;

	$context['current_permission'] = $permission;
	$context['member_groups'] = $context[$permission];

	template_inline_permissions();
}

// Save the permissions of a form containing inline permissions.
function save_inline_permissions($permissions)
{
	global $context, $db_prefix;

	// No permissions? Not a great deal to do here.
	if (!allowedTo('manage_permissions'))
		return;

	$insertRows = '';
	foreach ($permissions as $permission)
	{
		if (!isset($_POST[$permission]))
			continue;

		foreach ($_POST[$permission] as $ID_GROUP => $value)
		{
			if (in_array($value, array('on', 'deny')))
				$insertRows .= ', (' . (int) $ID_GROUP . ", '$permission', " . ($value == 'on' ? '1' : '0') . ')';
		}
	}

	// Remove the old permissions...
	db_query("
		DELETE FROM {$db_prefix}permissions
		WHERE permission IN ('" . implode("', '", $permissions) . "')", __FILE__, __LINE__);

	// ...and replace them with new ones.
	if ($insertRows != '')
		db_query("
			INSERT INTO {$db_prefix}permissions
				(ID_GROUP, permission, addDeny)
			VALUES " . substr($insertRows, 2), __FILE__, __LINE__);

	// Do a full child update.
	updateChildPermissions(array(), -1);

	// Just incase we cached this.
	updateSettings(array('settings_updated' => time()));
}

function loadPermissionProfiles()
{
	global $context, $db_prefix, $txt;

	$request = db_query("
		SELECT pp.ID_PROFILE, pp.profile_name, IFNULL(b.ID_BOARD, 0) AS ID_PARENT, IFNULL(b.name, '') AS boardName
		FROM {$db_prefix}permission_profiles AS pp
			LEFT JOIN {$db_prefix}boards AS b ON (b.ID_BOARD = pp.ID_PARENT)
		ORDER BY ID_PARENT", __FILE__, __LINE__);
	$context['profiles'] = array();
	while ($row = mysql_fetch_assoc($request))
	{
		// Format the label nicely.
		if (!empty($row['ID_PARENT']))
			$name = $row['boardName'];
		elseif (isset($txt['permissions_profile_' . $row['profile_name']]))
			$name = $txt['permissions_profile_' . $row['profile_name']];
		else
			$name = $row['profile_name'];

		$context['profiles'][$row['ID_PROFILE']] = array(
			'id' => $row['ID_PROFILE'],
			'name' => $name,
			'unformatted_name' => $row['profile_name'],
			'parent' => $row['ID_PARENT'],
		);
	}
	mysql_free_result($request);
}

// Add/Edit/Delete profiles.
function EditPermissionProfiles()
{
	global $db_prefix, $context, $txt;

	// Setup the template, first for fun.
	$context['page_title'] = $txt['permissions_profile_edit'];
	$context['sub_template'] = 'edit_profiles';

	// If we're creating a new one do it first.
	if (isset($_POST['create']))
	{
		checkSession();

		$_POST['copy_from'] = (int) $_POST['copy_from'];

		// Insert the profile itself.
		db_query("
			INSERT INTO {$db_prefix}permission_profiles
				(profile_name, ID_PARENT)
			VALUES
				('$_POST[profile_name]', 0)", __FILE__, __LINE__);
		$profile_id = db_insert_id();

		// Load the permissions from the one it's being copied from.
		$request = db_query("
			SELECT ID_GROUP, permission, addDeny
			FROM {$db_prefix}board_permissions
			WHERE ID_PROFILE = $_POST[copy_from]", __FILE__, __LINE__);
		$inserts = array();
		while ($row = mysql_fetch_assoc($request))
			$inserts[] = "($profile_id, $row[ID_GROUP], '$row[permission]', $row[addDeny])";
		mysql_free_result($request);

		if (!empty($inserts))
			db_query("
				INSERT INTO {$db_prefix}board_permissions
					(ID_PROFILE, ID_GROUP, permission, addDeny)
				VALUES
					" . implode(',', $inserts), __FILE__, __LINE__);
	}
	// Saving changes?
	elseif (isset($_POST['save']) && !empty($_POST['predef']))
	{
		checkSession();

		foreach ($_POST['predef'] as $id => $label)
			db_query("
				UPDATE {$db_prefix}permission_profiles
				SET profile_name = '$label'
				WHERE ID_PROFILE = $id", __FILE__, __LINE__);
	}
	// Deleting?
	elseif (isset($_GET['delete']))
	{
		checkSession('get');

		$_GET['pid'] = (int) $_GET['pid'];

		// Verify it's not in use...
		$request = db_query("
			SELECT ID_BOARD
			FROM {$db_prefix}boards
			WHERE ID_PROFILE = $_GET[pid]
			LIMIT 1", __FILE__, __LINE__);
		if (mysql_num_rows($request) != 0 || $_GET['pid'] == 1)
			fatal_lang_error(1);
		mysql_free_result($request);

		// Oh well, delete.
		db_query("
			DELETE FROM {$db_prefix}permission_profiles
			WHERE ID_PROFILE = $_GET[pid]", __FILE__, __LINE__);
	}

	// Clearly, we'll need this!
	loadPermissionProfiles();

	// Work out what ones are in use.
	$request = db_query("
		SELECT ID_PROFILE
		FROM {$db_prefix}boards
		GROUP BY ID_PROFILE", __FILE__, __LINE__);
	while ($row = mysql_fetch_assoc($request))
		if (isset($context['profiles'][$row['ID_PROFILE']]))
			$context['profiles'][$row['ID_PROFILE']]['in_use'] = true;
	mysql_free_result($request);

	// Actually, what are predefined?
	$context['predefined'] = array();
	foreach ($context['profiles'] as $id => $profile)
	{
		// If it's got no parent it should at least exist.
		if ($profile['parent'] == 0)
		{
			$context['predefined'][$id] = $profile;

			// Can't delete special ones.
			$context['predefined'][$id]['can_edit'] = isset($txt['permissions_profile_' . $profile['unformatted_name']]) ? false : true;

			// You can only delete it if you can edit it AND it's not in use.
			$context['predefined'][$id]['can_delete'] = $context['predefined'][$id]['can_edit'] && empty($profile['in_use']) ? true : false;
		}
		// If it's a board highlight it.
		else
		{
			$context['profiles'][$id]['name'] = $txt['smf82'] . ': &quot;' . $context['profiles'][$id]['name'] . '&quot;';
		}
	}
}

// This function updates the permissions of any groups based off this group.
function updateChildPermissions($parents, $profile = null)
{
	global $db_prefix;

	// All the parent groups to sort out.
	if (!is_array($parents))
		$parents = array($parents);

	// Find all the children of this group.
	$request = db_query("
		SELECT ID_PARENT, ID_GROUP
		FROM {$db_prefix}membergroups
		WHERE ID_PARENT != -2
			" . (empty($parents) ? '' : 'AND ID_PARENT IN (' . implode(', ', $parents) . ')'), __FILE__, __LINE__);
	$children = array();
	$parents = array();
	$child_groups = array();
	while ($row = mysql_fetch_assoc($request))
	{
		$children[$row['ID_PARENT']][] = $row['ID_GROUP'];
		$child_groups[] = $row['ID_GROUP'];
		$parents[] = $row['ID_PARENT'];
	}
	mysql_free_result($request);

	$parents = array_unique($parents);

	// Not a sausage, or a child?
	if (empty($children))
		return false;

	// First off, are we doing general permissions?
	if ($profile < 1 || $profile === null)
	{
		// Fetch all the parent permissions.
		$request = db_query("
			SELECT ID_GROUP, permission, addDeny
			FROM {$db_prefix}permissions
			WHERE ID_GROUP IN (" . implode(', ', $parents) . ")", __FILE__, __LINE__);
		$permissions = array();
		while ($row = mysql_fetch_assoc($request))
			foreach ($children[$row['ID_GROUP']] as $child)
				$permissions[] = "($child, '$row[permission]', $row[addDeny])";
		mysql_free_result($request);

		db_query("
			DELETE FROM {$db_prefix}permissions
			WHERE ID_GROUP IN (" . implode(',', $child_groups) . ")", __FILE__, __LINE__);

		// Finally insert.
		if (!empty($permissions))
		{
			db_query("
				INSERT INTO {$db_prefix}permissions
					(ID_GROUP, permission, addDeny)
				VALUES
					" . implode(',', $permissions), __FILE__, __LINE__);
		}
	}

	// Then, what about board profiles?
	if ($profile != -1)
	{
		$profileQuery = $profile === null ? '' : ' AND ID_PROFILE = ' . ($profile ? $profile : 1);

		// Again, get all the parent permissions.
		$request = db_query("
			SELECT ID_PROFILE, ID_GROUP, permission, addDeny
			FROM {$db_prefix}board_permissions
			WHERE ID_GROUP IN (" . implode(', ', $parents) . ")
				$profileQuery", __FILE__, __LINE__);
		$permissions = array();
		while ($row = mysql_fetch_assoc($request))
			foreach ($children[$row['ID_GROUP']] as $child)
				$permissions[] = "($child, $row[ID_PROFILE], '$row[permission]', $row[addDeny])";
		mysql_free_result($request);

		db_query("
			DELETE FROM {$db_prefix}board_permissions
			WHERE ID_GROUP IN (" . implode(',', $child_groups) . ")
				$profileQuery", __FILE__, __LINE__);

		// Do the insert.
		if (!empty($permissions))
		{
			db_query("
				INSERT INTO {$db_prefix}board_permissions
					(ID_GROUP, ID_PROFILE, permission, addDeny)
				VALUES
					" . implode(',', $permissions), __FILE__, __LINE__);
		}
	}
}

?>