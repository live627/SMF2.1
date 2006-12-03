<?php
/**********************************************************************************
* Subs-Membergroups.php                                                           *
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

/*	This file contains functions regarding manipulation of and information
	about membergroups.

	bool deleteMembergroups(array groups)
		- delete one of more membergroups.
		- requires the manage_membergroups permission.
		- returns true on success or false on failure.
		- has protection against deletion of protected membergroups.
		- deletes the permissions linked to the membergroup.
		- takes members out of the deleted membergroups.

	bool removeMembersFromGroups(array members, array groups = null)
		- remove one or more members from one or more membergroups.
		- requires the manage_membergroups permission.
		- returns true on success or false on failure.
		- if groups is null, the specified members are stripped from all their
		  membergroups.
		- function includes a protection against removing from implicit groups.
		- non-admins are not able to remove members from the admin group.

	bool addMembersToGroup(array members, group, type = 'auto')
		- add one or more members to a specified group.
		- requires the manage_membergroups permission.
		- returns true on success or false on failure.
		- the type parameter specifies whether the group is added as primary or
		  as additional group.
		- function has protection against adding members to implicit groups.
		- non-admins are not able to add members to the admin group.

	bool listMembergroupMembers_Href(&array members, int membergroup, int limit = null)
		- get a list of all members that are part of a membergroup.
		- if limit is set to null, all members are returned.
		- returns a list of href-links in $members.
		- returns true if there are more than limit members.

*/

// Delete one or more membergroups.
function deleteMembergroups($groups)
{
	global $db_prefix, $sourcedir, $smfFunc;

	// Make sure it's an array.
	if (!is_array($groups))
		$groups = array((int) $groups);
	else
	{
		$groups = array_unique($groups);

		// Make sure all groups are integer.
		foreach ($groups as $key => $value)
			$groups[$key] = (int) $value;
	}

	// Some groups are protected (guests, administrators, moderators, newbies).
	$groups = array_diff($groups, array(-1, 0, 1, 3, 4));
	if (empty($groups))
		return false;

	// Remove the membergroups themselves.
	$smfFunc['db_query']('', "
		DELETE FROM {$db_prefix}membergroups
		WHERE id_group IN (" . implode(', ', $groups) . ")", __FILE__, __LINE__);

	// Remove the permissions of the membergroups.
	$smfFunc['db_query']('', "
		DELETE FROM {$db_prefix}permissions
		WHERE id_group IN (" . implode(', ', $groups) . ')', __FILE__, __LINE__);
	$smfFunc['db_query']('', "
		DELETE FROM {$db_prefix}board_permissions
		WHERE id_group IN (" . implode(', ', $groups) . ')', __FILE__, __LINE__);
	$smfFunc['db_query']('', "
		DELETE FROM {$db_prefix}group_moderators
		WHERE id_group IN (" . implode(', ', $groups) . ')', __FILE__, __LINE__);

	// Delete any outstanding requests.
	$smfFunc['db_query']('', "
		DELETE FROM {$db_prefix}log_group_requests
		WHERE id_group IN (" . implode(', ', $groups) . ')', __FILE__, __LINE__);

	// Update the primary groups of members.
	$smfFunc['db_query']('', "
		UPDATE {$db_prefix}members
		SET id_group = 0
		WHERE id_group IN (" . implode(', ', $groups) . ')', __FILE__, __LINE__);

	// Update any inherited groups (Lose inheritance).
	$smfFunc['db_query']('', "
		UPDATE {$db_prefix}membergroups
		SET id_parent = -2
		WHERE id_parent IN (" . implode(', ', $groups) . ')', __FILE__, __LINE__);

	// Update the additional groups of members.
	$request = $smfFunc['db_query']('', "
		SELECT id_member, additional_groups
		FROM {$db_prefix}members
		WHERE FIND_IN_SET(" . implode(', additional_groups) OR FIND_IN_SET(', $groups) . ', additional_groups)', __FILE__, __LINE__);
	$updates = array();
	while ($row = $smfFunc['db_fetch_assoc']($request))
		$updates[$row['additional_groups']][] = $row['id_member'];
	$smfFunc['db_free_result']($request);

	foreach ($updates as $additional_groups => $memberArray)
		updateMemberData($memberArray, array('additional_groups' => '\'' . implode(',', array_diff(explode(',', $additional_groups), $groups)) . '\''));

	// No boards can provide access to these membergroups anymore.
	$request = $smfFunc['db_query']('', "
		SELECT id_board, member_groups
		FROM {$db_prefix}boards
		WHERE FIND_IN_SET(" . implode(', member_groups) OR FIND_IN_SET(', $groups) . ', member_groups)', __FILE__, __LINE__);
	$updates = array();
	while ($row = $smfFunc['db_fetch_assoc']($request))
		$updates[$row['member_groups']][] = $row['id_board'];
	$smfFunc['db_free_result']($request);

	foreach ($updates as $member_groups => $boardArray)
		$smfFunc['db_query']('', "
			UPDATE {$db_prefix}boards
			SET member_groups = '" . implode(',', array_diff(explode(',', $member_groups), $groups)) . "'
			WHERE id_board IN (" . implode(', ', $boardArray) . ")", __FILE__, __LINE__);

	// Recalculate the post groups, as they likely changed.
	updateStats('postgroups');
	// Make a note of the fact that the cache may be wrong.
	updateSettings(array('settings_updated' => time()));

	// Update the group cache.
	require_once($sourcedir . '/ManageMembergroups.php');
	cacheGroups();

	// It was a success.
	return true;
}

// Remove one or more members from one or more membergroups.
function removeMembersFromGroups($members, $groups = null, $permissionCheckDone = false)
{
	global $db_prefix, $smfFunc;

	// You're getting nowhere without this permission, unless of course you are the group's moderator.
	if (!$permissionCheckDone)
		isAllowedTo('manage_membergroups');

	// Assume something will happen.
	updateSettings(array('settings_updated' => time()));

	// Cleaning the input.
	if (!is_array($members))
		$members = array((int) $members);
	else
	{
		$members = array_unique($members);

		// Cast the members to integer.
		foreach ($members as $key => $value)
			$members[$key] = (int) $value;
	}

	// Just incase.
	if (empty($members))
		return false;
	elseif ($groups === null)
	{
		// Wanna remove all groups from these members? That's easy.
		$smfFunc['db_query']('', "
			UPDATE {$db_prefix}members
			SET
				id_group = 0,
				additional_groups = ''
			WHERE id_member IN (" . implode(', ', $members) . ")" . (allowedTo('admin_forum') ? '' : "
				AND id_group != 1
				AND NOT FIND_IN_SET(1, additional_groups)"), __FILE__, __LINE__);

		updateStats('postgroups', 'id_member IN (' . implode(', ', $members) . ')');

		return true;
	}
	elseif (!is_array($groups))
		$groups = array((int) $groups);
	else
	{
		$groups = array_unique($groups);

		// Make sure all groups are integer.
		foreach ($groups as $key => $value)
			$groups[$key] = (int) $value;
	}

	// Fetch a list of groups members cannot be assigned to explicitely.
	$implicitGroups = array(-1, 0, 3);
	$request = $smfFunc['db_query']('', "
		SELECT id_group
		FROM {$db_prefix}membergroups
		WHERE min_posts != -1", __FILE__, __LINE__);
	while ($row = $smfFunc['db_fetch_assoc']($request))
		$implicitGroups[] = $row['id_group'];
	$smfFunc['db_free_result']($request);

	// Now get rid of those groups.
	$groups = array_diff($groups, $implicitGroups);

	// If you're not an admin yourself, you can't de-admin others.
	if (!allowedTo('admin_forum'))
		$groups = array_diff($groups, array(1));

	// Only continue if there are still groups and members left.
	if (empty($groups) || empty($members))
		return false;

	// First, reset those who have this as their primary group - this is the easy one.
	$smfFunc['db_query']('', "
		UPDATE {$db_prefix}members
		SET id_group = 0
		WHERE id_group IN (" . implode(', ', $groups) . ")
			AND id_member IN (" . implode(', ', $members) . ")", __FILE__, __LINE__);

	// Those who have it as part of their additional group must be updated the long way... sadly.
	$request = $smfFunc['db_query']('', "
		SELECT id_member, additional_groups
		FROM {$db_prefix}members
		WHERE (FIND_IN_SET(" . implode(', additional_groups) OR FIND_IN_SET(', $groups) . ", additional_groups))
			AND id_member IN (" . implode(', ', $members) . ")
		LIMIT " . count($members), __FILE__, __LINE__);
	$updates = array();
	while ($row = $smfFunc['db_fetch_assoc']($request))
		$updates[$row['additional_groups']][] = $row['id_member'];
	$smfFunc['db_free_result']($request);

	foreach ($updates as $additional_groups => $memberArray)
		$smfFunc['db_query']('', "
			UPDATE {$db_prefix}members
			SET additional_groups = '" . implode(',', array_diff(explode(',', $additional_groups), $groups)) . "'
			WHERE id_member IN (" . implode(', ', $memberArray) . ")", __FILE__, __LINE__);

	// Their post groups may have changed now...
	updateStats('postgroups', 'id_member IN (' . implode(', ', $members) . ')');

	// Mission successful.
	return true;
}

// Add one or more members to a membergroup.
/* Supported types:
	- only_primary      - Assigns a membergroup as primary membergroup, but only
	                      if a member has not yet a primary membergroup assigned,
	                      unless the member is already part of the membergroup.
	- only_additional   - Assigns a membergroup to the additional membergroups,
	                      unless the member is already part of the membergroup.
	- force_primary     - Assigns a membergroup as primary membergroup no matter
	                      what the previous primary membergroup was.
	- auto              - Assigns a membergroup to the primary group if it's still
	                      available. If not, assign it to the additional group. */
function addMembersToGroup($members, $group, $type = 'auto', $permissionCheckDone = false)
{
	global $db_prefix, $smfFunc;

	// Show your licence, but only if it hasn't been done yet.
	if (!$permissionCheckDone)
		isAllowedTo('manage_membergroups');

	// Make sure we don't keep old stuff cached.
	updateSettings(array('settings_updated' => time()));

	if (!is_array($members))
		$members = array((int) $members);
	else
	{
		$members = array_unique($members);

		// Make sure all members are integer.
		foreach ($members as $key => $value)
			$members[$key] = (int) $value;
	}
	$group = (int) $group;

	// Some groups just don't like explicitly having members.
	$request = $smfFunc['db_query']('', "
		SELECT id_group
		FROM {$db_prefix}membergroups
		WHERE min_posts != -1", __FILE__, __LINE__);
	$implicitGroups = array(-1, 0, 3);
	while ($row = $smfFunc['db_fetch_assoc']($request))
		$implicitGroups[] = $row['id_group'];
	$smfFunc['db_free_result']($request);

	// Sorry, you can't join an implicit group.
	if (in_array($group, $implicitGroups) || empty($members))
		return false;

	// Only admins can add admins.
	if ($group == 1 && !allowedTo('admin_forum'))
		return false;

	// Do the actual updates.
	if ($type == 'only_additional')
		$smfFunc['db_query']('', "
			UPDATE {$db_prefix}members
			SET additional_groups = CASE WHEN additional_groups = '' THEN '$group' ELSE CONCAT(additional_groups, ',$group') END
			WHERE id_member IN (" . implode(', ', $members) . ")
				AND id_group != $group
				AND NOT FIND_IN_SET($group, additional_groups)", __FILE__, __LINE__);
	elseif ($type == 'only_primary' || $type == 'force_primary')
		$smfFunc['db_query']('', "
			UPDATE {$db_prefix}members
			SET id_group = $group
			WHERE id_member IN (" . implode(', ', $members) . ")" . ($type == 'force_primary' ? '' : "
				AND id_group = 0
				AND NOT FIND_IN_SET($group, additional_groups)"), __FILE__, __LINE__);
	elseif ($type == 'auto')
		$smfFunc['db_query']('', "
			UPDATE {$db_prefix}members
			SET
				additional_groups = CASE WHEN id_group = 0 THEN additional_groups
					WHEN additional_groups = '' THEN '$group'
					ELSE CONCAT(additional_groups, ',$group') END,
				id_group = CASE WHEN id_group = 0 THEN $group ELSE id_group END
			WHERE id_member IN (" . implode(', ', $members) . ")
				AND id_group != $group
				AND NOT FIND_IN_SET($group, additional_groups)", __FILE__, __LINE__);
	// Ack!!?  What happened?
	else
		trigger_error('addMembersToGroup(): Unknown type \'' . $type . '\'', E_USER_WARNING);

	// Update their postgroup statistics.
	updateStats('postgroups', 'id_member IN (' . implode(', ', $members) . ')');

	return true;
}

function listMembergroupMembers_Href(&$members, $membergroup, $limit = null)
{
	global $db_prefix, $scripturl, $txt, $smfFunc;

	$request = $smfFunc['db_query']('', "
		SELECT id_member, real_name
		FROM {$db_prefix}members
		WHERE id_group = $membergroup OR FIND_IN_SET($membergroup, additional_groups)" . ($limit === null ? '' : "
		LIMIT " . ($limit + 1)), __FILE__, __LINE__);
	$members = array();
	while ($row = $smfFunc['db_fetch_assoc']($request))
		$members[] = '<a href="' . $scripturl . '?action=profile;u=' . $row['id_member'] . '">' . $row['real_name'] . '</a>';
	$smfFunc['db_free_result']($request);

	// If there are more than $limit members, add a 'more' link.
	if ($limit !== null && count($members) > $limit)
	{
		unset($members[$limit]);
		return true;
	}
	else
		return false;
}

?>