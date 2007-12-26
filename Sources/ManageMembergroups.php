<?php
/**********************************************************************************
* ManageMembergroups.php                                                          *
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
	$context[$context['admin_menu_name']]['tab_data'] = array(
		'title' => $txt['membergroups_title'],
		'help' => 'membergroups',
		'description' => $txt['membergroups_description'],
	);

	// Call the right function.
	$subActions[$_REQUEST['sa']][0]();
}

// An overview of the current membergroups.
function MembergroupIndex()
{
	global $db_prefix, $txt, $scripturl, $context, $settings, $smfFunc, $sourcedir;

	$context['page_title'] = $txt['membergroups_title'];

	// The first list shows the regular membergroups.
	$listOptions = array(
		'id' => 'regular_membergroups_list',
		'title' => $txt['membergroups_regular'],
		'base_href' => $scripturl . '?action=admin;area=membergroups' . (isset($_REQUEST['sort2']) ? ';sort2=' . urlencode($_REQUEST['sort2']) : ''),
		'default_sort_col' => 'name',
		'get_items' => array(
			'file' => 'Subs-Membergroups.php',
			'function' => 'list_getMembergroups',
			'params' => array(
				'regular',
			),
		),
		'columns' => array(
			'name' => array(
				'header' => array(
					'value' => $txt['membergroups_name'],
				),
				'data' => array(
					'function' => create_function('$rowData', '
						global $scripturl;

						// Since the moderator group has no explicit members, no link is needed.
						if ($rowData[\'id_group\'] == 3)
							$group_name = $rowData[\'group_name\'];
						else
						{
							$color_style = empty($rowData[\'online_color\']) ? \'\' : sprintf(\' style="color: %1$s;"\', $rowData[\'online_color\']);
							$group_name = sprintf(\'<a href="%1$s?action=moderate;area=viewgroups;sa=members;group=%2$d"%3$s>%4$s</a>\', $scripturl, $rowData[\'id_group\'], $color_style, $rowData[\'group_name\']);
						}

						// Add a help option for moderator and administrator.
						if ($rowData[\'id_group\'] == 1)
							$group_name .= sprintf(\' (<a href="%1$s?action=helpadmin;help=membergroup_administrator" onclick="return reqWin(this.href);">?</a>)\', $scripturl);
						elseif ($rowData[\'id_group\'] == 3)
							$group_name .= sprintf(\' (<a href="%1$s?action=helpadmin;help=membergroup_moderator" onclick="return reqWin(this.href);">?</a>)\', $scripturl);


						return $group_name;
					'),
				),
				'sort' => array(
					'default' => 'CASE WHEN id_group < 4 THEN id_group ELSE 4 END, group_name',
					'reverse' => 'CASE WHEN id_group < 4 THEN id_group ELSE 4 END, group_name DESC',
				),
			),
			'stars' => array(
				'header' => array(
					'value' => $txt['membergroups_stars'],
				),
				'data' => array(
					'function' => create_function('$rowData', '
						global $settings;

						$stars = explode(\'#\', $rowData[\'stars\']);

						// In case no stars are setup, return with nothing
						if (empty($stars[0]) || empty($stars[1]))
							return \'\';

						// Otherwise repeat the image a given number of times.
						else
						{
							$image = sprintf(\'<img src="%1$s/%2$s" alt="*" border="0" />\', $settings[\'images_url\'], $stars[1]);
							return str_repeat($image, $stars[0]);
						}
					'),

				),
				'sort' => array(
					'default' => 'CASE WHEN id_group < 4 THEN id_group ELSE 4 END, SUBSTRING(stars, 1, LOCATE(\'#\', stars) - 1) DESC, SUBSTRING(stars, LOCATE(\'#\', stars) + 1)',
					'reverse' => 'CASE WHEN id_group < 4 THEN id_group ELSE 4 END, SUBSTRING(stars, 1, LOCATE(\'#\', stars) - 1), SUBSTRING(stars, LOCATE(\'#\', stars) + 1) DESC',
				)
			),
			'members' => array(
				'header' => array(
					'value' => $txt['membergroups_members_top'],
				),
				'data' => array(
					'function' => create_function('$rowData', '
						global $txt;

						// No explicit members for the moderator group.
						return $rowData[\'id_group\'] == 3 ? $txt[\'membergroups_guests_na\'] : $rowData[\'num_members\'];
					'),
					'class' => 'windowbg',
					'style' => 'text-align: center',
				),
				'sort' => array(
					'default' => 'CASE WHEN id_group < 4 THEN id_group ELSE 4 END, -1 DESC',
					'reverse' => 'CASE WHEN id_group < 4 THEN id_group ELSE 4 END, -1',
				),
			),
			'modify' => array(
				'header' => array(
					'value' => $txt['modify'],
				),
				'data' => array(
					'sprintf' => array(
						'format' => '<a href="' . $scripturl . '?action=admin;area=membergroups;sa=edit;group=%1$d">' . $txt['membergroups_modify'] . '</a>',
						'params' => array(
							'id_group' => false,
						),
					),
					'style' => 'text-align: center',
				),
			),
		),
		'additional_rows' => array(
			array(
				'position' => 'below_table_data',
				'value' => '[<a href="' . $scripturl . '?action=admin;area=membergroups;sa=add;generalgroup">' . $txt['membergroups_add_group'] . '</a>]',
				'class' => 'catbg',
			),
		),
	);

	require_once($sourcedir . '/Subs-List.php');
	createList($listOptions);

	// The second list shows the post count based groups.
	$listOptions = array(
		'id' => 'post_count_membergroups_list',
		'title' => $txt['membergroups_post'],
		'base_href' => $scripturl . '?action=admin;area=membergroups' . (isset($_REQUEST['sort']) ? ';sort=' . urlencode($_REQUEST['sort']) : ''),
		'default_sort_col' => 'required_posts',
		'request_vars' => array(
			'sort' => 'sort2',
			'desc' => 'desc2',
		),
		'get_items' => array(
			'file' => 'Subs-Membergroups.php',
			'function' => 'list_getMembergroups',
			'params' => array(
				'post_count',
			),
		),
		'columns' => array(
			'name' => array(
				'header' => array(
					'value' => $txt['membergroups_name'],
				),
				'data' => array(
					'function' => create_function('$rowData', '
						global $scripturl;

						$colorStyle = empty($rowData[\'online_color\']) ? \'\' : sprintf(\' style="color: %1$s;"\', $rowData[\'online_color\']);
						return  sprintf(\'<a href="%1$s?action=moderate;area=viewgroups;sa=members;group=%2$d"%3$s>%4$s</a>\', $scripturl, $rowData[\'id_group\'], $colorStyle, $rowData[\'group_name\']);
					'),
				),
				'sort' => array(
					'default' => 'group_name',
					'reverse' => 'group_name DESC',
				),
			),
			'stars' => array(
				'header' => array(
					'value' => $txt['membergroups_stars'],
				),
				'data' => array(
					'function' => create_function('$rowData', '
						global $settings;

						$stars = explode(\'#\', $rowData[\'stars\']);

						if (empty($stars[0]) || empty($stars[1]))
							return \'\';
						else
						{
							$star_image = sprintf(\'<img src="%1$s/%2$s" alt="*" border="0" />\', $settings[\'images_url\'], $stars[1]);
							return str_repeat($star_image, $stars[0]);
						}
					'),
				),
				'sort' => array(
					'default' => 'CASE WHEN id_group < 4 THEN id_group ELSE 4 END, SUBSTRING(stars, 1, LOCATE(\'#\', stars) - 1) DESC, SUBSTRING(stars, LOCATE(\'#\', stars) + 1)',
					'reverse' => 'CASE WHEN id_group < 4 THEN id_group ELSE 4 END, SUBSTRING(stars, 1, LOCATE(\'#\', stars) - 1), SUBSTRING(stars, LOCATE(\'#\', stars) + 1) DESC',
				)
			),
			'members' => array(
				'header' => array(
					'value' => $txt['membergroups_members_top'],
				),
				'data' => array(
					'db' => 'num_members',
					'class' => 'windowbg',
					'style' => 'text-align: center',
				),
				'sort' => array(
					'default' => '-1 DESC',
					'reverse' => '-1',
				),
			),
			'required_posts' => array(
				'header' => array(
					'value' => $txt['membergroups_min_posts'],
				),
				'data' => array(
					'db' => 'min_posts',
					'class' => 'windowbg',
					'style' => 'text-align: center',
				),
				'sort' => array(
					'default' => 'min_posts',
					'reverse' => 'min_posts DESC',
				),
			),
			'modify' => array(
				'header' => array(
					'value' => $txt['modify'],
				),
				'data' => array(
					'sprintf' => array(
						'format' => '<a href="' . $scripturl . '?action=admin;area=membergroups;sa=edit;group=%1$d">' . $txt['membergroups_modify'] . '</a>',
						'params' => array(
							'id_group' => false,
						),
					),
					'style' => 'text-align: center',
				),
			),
		),
		'additional_rows' => array(
			array(
				'position' => 'below_table_data',
				'value' => '[<a href="' . $scripturl . '?action=admin;area=membergroups;sa=add;postgroup">' . $txt['membergroups_add_group'] . '</a>]',
				'class' => 'catbg',
			),
		),
	);

	createList($listOptions);
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

		$request = $smfFunc['db_query']('', '
			SELECT MAX(id_group)
			FROM {db_prefix}membergroups',
			array(
			)
		);
		list ($id_group) = $smfFunc['db_fetch_row']($request);
		$smfFunc['db_free_result']($request);
		$id_group++;

		$smfFunc['db_query']('', '
			INSERT INTO {db_prefix}membergroups
				(id_group, description, group_name, min_posts, stars, online_color)
			VALUES (' . $id_group . ', \'\', SUBSTRING(\'' . $_POST['group_name'] . '\', 1, 80), ' . ($postCountBasedGroup ? (int) $_POST['min_posts'] : '-1') . ', \'1#star.gif\', \'\')',
			array(
			)
		);

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

			// Don't allow copying of a real priviledged person!
			require_once($sourcedir . '/ManagePermissions.php');
			loadIllegalPermissions();

			$request = $smfFunc['db_query']('', '
				SELECT permission, add_deny
				FROM {db_prefix}permissions
				WHERE id_group = {int:inject_int_1}',
				array(
					'inject_int_1' => $copy_id,
				)
			);
			$inserts = array();
			while ($row = $smfFunc['db_fetch_assoc']($request))
			{
				if (empty($context['illegal_permissions']) || !in_array($row['permission'], $context['illegal_permissions']))
					$inserts[] = array($id_group, $row['permission'], $row['add_deny']);
			}
			$smfFunc['db_free_result']($request);

			if (!empty($inserts))
				$smfFunc['db_insert']('insert',
					$db_prefix . 'permissions',
					array('id_group' => 'int', 'permission' => 'string', 'add_deny' => 'int'),
					$inserts,
					array('id_group', 'permission')
				);

			$request = $smfFunc['db_query']('', '
				SELECT id_profile, permission, add_deny
				FROM {db_prefix}board_permissions
				WHERE id_group = {int:inject_int_1}',
				array(
					'inject_int_1' => $copy_id,
				)
			);
			$inserts = array();
			while ($row = $smfFunc['db_fetch_assoc']($request))
				$inserts[] = array($id_group, $row['id_profile'], $row['permission'], $row['add_deny']);
			$smfFunc['db_free_result']($request);

			if (!empty($inserts))
				$smfFunc['db_insert']('insert',
					$db_prefix . 'board_permissions',
					array('id_group' => 'int', 'id_profile' => 'int', 'permission' => 'string', 'add_deny' => 'int'),
					$inserts,
					array('id_group', 'id_profile', 'permission')
				);

			// Also get some membergroup information if we're copying and not copying from guests...
			if ($copy_id > 0 && $_POST['perm_type'] == 'copy')
			{
				$request = $smfFunc['db_query']('', '
					SELECT online_color, max_messages, stars
					FROM {db_prefix}membergroups
					WHERE id_group = {int:inject_int_1}
					LIMIT 1',
					array(
						'inject_int_1' => $copy_id,
					)
				);
				$group_info = $smfFunc['db_fetch_assoc']($request);
				$smfFunc['db_free_result']($request);

				// ...and update the new membergroup with it.
				$smfFunc['db_query']('', '
					UPDATE {db_prefix}membergroups
					SET
						online_color = {string:inject_string_1},
						max_messages = {int:inject_int_1},
						stars = {string:inject_string_2}
					WHERE id_group = {int:inject_int_2}',
					array(
						'inject_int_1' => $group_info['max_messages'],
						'inject_int_2' => $id_group,
						'inject_string_1' => $group_info['online_color'],
						'inject_string_2' => $group_info['stars'],
					)
				);
			}
			// If inheriting say so...
			elseif ($_POST['perm_type'] == 'inherit')
			{
				$smfFunc['db_query']('', '
					UPDATE {db_prefix}membergroups
					SET id_parent = {int:inject_int_1}
					WHERE id_group = {int:inject_int_2}',
					array(
						'inject_int_1' => $copy_id,
						'inject_int_2' => $id_group,
					)
				);
			}
		}

		// Make sure all boards selected are stored in a proper array.
		$_POST['boardaccess'] = empty($_POST['boardaccess']) || !is_array($_POST['boardaccess']) ? array() : $_POST['boardaccess'];
		foreach ($_POST['boardaccess'] as $key => $value)
			$_POST['boardaccess'][$key] = (int) $value;

		// Only do this if they have special access requirements.
		if (!empty($_POST['boardaccess']))
			$smfFunc['db_query']('', '
				UPDATE {db_prefix}boards
				SET member_groups = CASE WHEN member_groups = {string:inject_string_1} THEN \'' . $id_group . '\' ELSE CONCAT(member_groups, \',' . $id_group . '\') END
				WHERE id_board IN ({array_int:inject_array_int_1})',
				array(
					'inject_array_int_1' => $_POST['boardaccess'],
					'inject_string_1' => '',
				)
			);

		// Rebuild the group cache.
		updateSettings(array(
			'settings_updated' => time(),
		));

		// Go change some more settings.
		redirectexit('action=admin;area=membergroups;sa=edit;group=' . $id_group);
	}

	// Just show the 'add membergroup' screen.
	$context['page_title'] = $txt['membergroups_new_group'];
	$context['sub_template'] = 'new_group';
	$context['post_group'] = isset($_REQUEST['postgroup']);
	$context['undefined_group'] = !isset($_REQUEST['postgroup']) && !isset($_REQUEST['generalgroup']);

	$result = $smfFunc['db_query']('', '
		SELECT id_group, group_name
		FROM {db_prefix}membergroups
		WHERE (id_group > {int:inject_int_1} OR id_group = {int:inject_int_2})' . (empty($modSettings['permission_enable_postgroups']) ? '
			AND min_posts = {int:inject_int_3}' : '') . '
		ORDER BY min_posts, id_group != {int:inject_int_2}, group_name',
		array(
			'inject_int_1' => 3,
			'inject_int_2' => 2,
			'inject_int_3' => -1,
		)
	);
	$context['groups'] = array();
	while ($row = $smfFunc['db_fetch_assoc']($result))
		$context['groups'][] = array(
			'id' => $row['id_group'],
			'name' => $row['group_name']
		);
	$smfFunc['db_free_result']($result);

	$result = $smfFunc['db_query']('', '
		SELECT id_board, name, child_level
		FROM {db_prefix}boards',
		array(
		)
	);
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
		$_POST['group_hidden'] = empty($_POST['group_hidden']) || $_POST['min_posts'] != -1 || $_REQUEST['group'] == 3 ? 0 : (int) $_POST['group_hidden'];
		$_POST['group_inherit'] = $_REQUEST['group'] > 1 && $_REQUEST['group'] != 3 ? (int) $_POST['group_inherit'] : -2;

		// !!! Don't set online_color for the Moderators group?

		// Do the update of the membergroup settings.
		$smfFunc['db_query']('', '
			UPDATE {db_prefix}membergroups
			SET group_name = {string:inject_string_1}, online_color = {string:inject_string_2},
				max_messages = {int:inject_int_1}, min_posts = {int:inject_int_2}, stars = {string:inject_string_3},
				description = {string:inject_string_4}, group_type = {int:inject_int_3}, hidden = {int:inject_int_4},
				id_parent = {int:inject_int_5}
			WHERE id_group = {int:inject_int_6}',
			array(
				'inject_int_1' => $_POST['max_messages'],
				'inject_int_2' => $_POST['min_posts'],
				'inject_int_3' => $_POST['group_type'],
				'inject_int_4' => $_POST['group_hidden'],
				'inject_int_5' => $_POST['group_inherit'],
				'inject_int_6' => (int) $_REQUEST['group'],
				'inject_string_1' => $_POST['group_name'],
				'inject_string_2' => $_POST['online_color'],
				'inject_string_3' => $_POST['stars'],
				'inject_string_4' => $_POST['group_desc'],
			)
		);

		// Time to update the boards this membergroup has access to.
		if ($_REQUEST['group'] == 2 || $_REQUEST['group'] > 3)
		{
			$_POST['boardaccess'] = empty($_POST['boardaccess']) || !is_array($_POST['boardaccess']) ? array() : $_POST['boardaccess'];
			foreach ($_POST['boardaccess'] as $key => $value)
				$_POST['boardaccess'][$key] = (int) $value;

			// Find all board this group is in, but shouldn't be in.
			$request = $smfFunc['db_query']('', '
				SELECT id_board, member_groups
				FROM {db_prefix}boards
				WHERE FIND_IN_SET({string:inject_string_1}, member_groups)' . (empty($_POST['boardaccess']) ? '' : '
					AND id_board NOT IN (' . implode(', ', $_POST['boardaccess']) . ')'),
				array(
					'inject_string_1' => (int) $_REQUEST['group'],
				)
			);
			while ($row = $smfFunc['db_fetch_assoc']($request))
				$smfFunc['db_query']('', '
					UPDATE {db_prefix}boards
					SET member_groups = {string:inject_string_1}
					WHERE id_board = {int:inject_int_1}',
					array(
						'inject_int_1' => $row['id_board'],
						'inject_string_1' => implode(',', array_diff(explode(',', $row['member_groups']), array($_REQUEST['group']))),
					)
				);
			$smfFunc['db_free_result']($request);

			// Add the membergroup to all boards that hadn't been set yet.
			if (!empty($_POST['boardaccess']))
				$smfFunc['db_query']('', '
					UPDATE {db_prefix}boards
					SET member_groups = CASE WHEN member_groups = {string:inject_string_1} THEN \'' . (int) $_REQUEST['group'] . '\' ELSE CONCAT(member_groups, \',' . (int) $_REQUEST['group'] . '\') END
					WHERE id_board IN ({array_int:inject_array_int_1})
						AND NOT FIND_IN_SET({string:inject_string_2}, member_groups)',
					array(
						'inject_array_int_1' => $_POST['boardaccess'],
						'inject_string_1' => '',
						'inject_string_2' => (int) $_REQUEST['group'],
					)
				);
		}

		// Remove everyone from this group!
		if ($_POST['min_posts'] != -1)
		{
			$smfFunc['db_query']('', '
				UPDATE {db_prefix}members
				SET id_group = {int:inject_int_1}
				WHERE id_group = {int:inject_int_2}',
				array(
					'inject_int_1' => 0,
					'inject_int_2' => (int) $_REQUEST['group'],
				)
			);

			$request = $smfFunc['db_query']('', '
				SELECT id_member, additional_groups
				FROM {db_prefix}members
				WHERE FIND_IN_SET({string:inject_string_1}, additional_groups)',
				array(
					'inject_string_1' => (int) $_REQUEST['group'],
				)
			);
			$updates = array();
			while ($row = $smfFunc['db_fetch_assoc']($request))
				$updates[$row['additional_groups']][] = $row['id_member'];
			$smfFunc['db_free_result']($request);

			foreach ($updates as $additional_groups => $memberArray)
				updateMemberData($memberArray, array('additional_groups' => implode(',', array_diff(explode(',', $additional_groups), array((int) $_REQUEST['group'])))));
		}
		elseif ($_REQUEST['group'] != 3)
		{
			// Making it a hidden group? If so remove everyone with it as primary group (Actually, just make them additional).
			if ($_POST['group_hidden'] == 2)
			{
				$request = $smfFunc['db_query']('', '
					SELECT id_member, additional_groups
					FROM {db_prefix}members
					WHERE id_group = {int:inject_int_1}
						AND NOT FIND_IN_SET({string:inject_string_1}, additional_groups)',
					array(
						'inject_int_1' => (int) $_REQUEST['group'],
						'inject_string_1' => (int) $_REQUEST['group'],
					)
				);
				$updates = array();
				while ($row = $smfFunc['db_fetch_assoc']($request))
					$updates[$row['additional_groups']][] = $row['id_member'];
				$smfFunc['db_free_result']($request);

				foreach ($updates as $additional_groups => $memberArray)
					updateMemberData($memberArray, array('additional_groups' => implode(',', array_merge(explode(',', $additional_groups), array((int) $_REQUEST['group'])))));

				$smfFunc['db_query']('', '
					UPDATE {db_prefix}members
					SET id_group = {int:inject_int_1}
					WHERE id_group = {int:inject_int_2}',
					array(
						'inject_int_1' => 0,
						'inject_int_2' => $_REQUEST['group'],
					)
				);
			}

			// Either way, let's check our "show group membership" setting is correct.
			$request = $smfFunc['db_query']('', '
				SELECT COUNT(*)
				FROM {db_prefix}membergroups
				WHERE group_type != {int:inject_int_1}',
				array(
					'inject_int_1' => 0,
				)
			);
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
		$smfFunc['db_query']('', '
			DELETE FROM {db_prefix}group_moderators
			WHERE id_group = {int:inject_int_1}',
			array(
				'inject_int_1' => $_REQUEST['group'],
			)
		);
		if (!empty($moderator_string) && $_POST['min_posts'] == -1 && $_REQUEST['group'] != 3)
		{
			// Get all the usernames from the string
			$moderator_string = strtr(preg_replace('~&amp;#(\d{4,5}|[2-9]\d{2,4}|1[2-9]\d);~', '&#$1;', htmlspecialchars($smfFunc['db_unescape_string']($moderator_string), ENT_QUOTES)), array('&quot;' => '"'));
			preg_match_all('~"([^"]+)"~', $moderator_string, $matches);
			$moderators = array_merge($matches[1], explode(',', preg_replace('~"([^"]+)"~', '', $moderator_string)));
			for ($k = 0, $n = count($moderators); $k < $n; $k++)
			{
				$moderators[$k] = trim($smfFunc['db_escape_string']($moderators[$k]));

				if (strlen($moderators[$k]) == 0)
					unset($moderators[$k]);
			}

			// Find all the ID_MEMBERs for the member_name's in the list.
			$group_moderators = array();
			if (!empty($moderators))
			{
				$request = $smfFunc['db_query']('', '
					SELECT id_member
					FROM {db_prefix}members
					WHERE member_name IN (\'' . implode('\',\'', $moderators) . '\') OR real_name IN (\'' . implode('\',\'', $moderators) . '\')
					LIMIT ' . count($moderators),
					array(
					)
				);
				while ($row = $smfFunc['db_fetch_assoc']($request))
					$group_moderators[] = $row['id_member'];
				$smfFunc['db_free_result']($request);
			}

			// Found some?
			if (!empty($group_moderators))
			{
				$mod_insert = array();
				foreach ($group_moderators as $moderator)
					$mod_insert[] = array($_REQUEST['group'], $moderator);

				$smfFunc['db_insert']('insert',
					$db_prefix . 'group_moderators',
					array('id_group' => 'int', 'id_member' => 'int'),
					$mod_insert,
					array('id_group', 'id_member')
				);
			}
		}

		// There might have been some post group changes.
		updateStats('postgroups');
		// We've definetely changed some group stuff.
		updateSettings(array(
			'settings_updated' => time(),
		));

		redirectexit('action=admin;area=membergroups');
	}

	// Fetch the current group information.
	$request = $smfFunc['db_query']('', '
		SELECT group_name, description, min_posts, online_color, max_messages, stars, group_type, hidden, id_parent
		FROM {db_prefix}membergroups
		WHERE id_group = {int:inject_int_1}
		LIMIT 1',
		array(
			'inject_int_1' => (int) $_REQUEST['group'],
		)
	);
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
	$request = $smfFunc['db_query']('', '
		SELECT mem.real_name
		FROM {db_prefix}group_moderators AS mods
			INNER JOIN {db_prefix}members AS mem ON (mem.id_member = mods.id_member)
		WHERE mods.id_group = {int:inject_int_1}',
		array(
			'inject_int_1' => $_REQUEST['group'],
		)
	);
	$context['group']['moderators'] = array();
	while ($row = $smfFunc['db_fetch_assoc']($request))
		$context['group']['moderators'][] = $row['real_name'];
	$smfFunc['db_free_result']($request);

	$context['group']['moderator_list'] = empty($context['group']['moderators']) ? '' : '&quot;' . implode('&quot;, &quot;', $context['group']['moderators']) . '&quot;';

	// Get a list of boards this membergroup is allowed to see.
	$context['boards'] = array();
	if ($_REQUEST['group'] == 2 || $_REQUEST['group'] > 3)
	{
		$result = $smfFunc['db_query']('', '
			SELECT id_board, name, child_level, FIND_IN_SET({string:inject_string_1}, member_groups) AS can_access
			FROM {db_prefix}boards',
			array(
				'inject_string_1' => (int) $_REQUEST['group'],
			)
		);
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
	$request = $smfFunc['db_query']('', '
		SELECT id_group, group_name
		FROM {db_prefix}membergroups
		WHERE id_group != {int:inject_int_1}' .
			(empty($modSettings['permission_enable_postgroups']) ? '
			AND min_posts = {int:inject_int_2}' : '') . '
			AND id_group NOT IN (1, 3)
			AND id_parent = {int:inject_int_3}',
		array(
			'inject_int_1' => (int) $_REQUEST['group'],
			'inject_int_2' => -1,
			'inject_int_3' => -2,
		)
	);
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
	global $context, $db_prefix, $sourcedir, $scripturl, $modSettings, $txt;

	$context['sub_template'] = 'show_settings';
	$context['page_title'] = $txt['membergroups_settings'];

	// Needed for the settings functions.
	require_once($sourcedir . '/ManageServer.php');

	// Don't allow assignment of guests.
	$context['permissions_excluded'] = array(-1);

	// Only one thing here!
	$config_vars = array(
			array('permissions', 'manage_membergroups'),
	);

	if (isset($_REQUEST['save']))
	{
		checkSession();

		// Yeppers, saving this...
		saveDBSettings($config_vars);
		redirectexit('action=admin;area=membergroups;sa=settings');
	}

	// Some simple context.
	$context['post_url'] = $scripturl . '?action=admin;area=membergroups;save;sa=settings';
	$context['settings_title'] = $txt['membergroups_settings'];

	prepareDBSettingContext($config_vars);
}

?>