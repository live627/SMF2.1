<?php
/******************************************************************************
* Subs-Admin.php                                                              *
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
	$smfFunc['db_query']("
		DELETE FROM {$db_prefix}membergroups
		WHERE ID_GROUP IN (" . implode(', ', $groups) . ")
		LIMIT " . count($groups), __FILE__, __LINE__);

	// Remove the permissions of the membergroups.
	$smfFunc['db_query']("
		DELETE FROM {$db_prefix}permissions
		WHERE ID_GROUP IN (" . implode(', ', $groups) . ')', __FILE__, __LINE__);
	$smfFunc['db_query']("
		DELETE FROM {$db_prefix}board_permissions
		WHERE ID_GROUP IN (" . implode(', ', $groups) . ')', __FILE__, __LINE__);
	$smfFunc['db_query']("
		DELETE FROM {$db_prefix}group_moderators
		WHERE ID_GROUP IN (" . implode(', ', $groups) . ')', __FILE__, __LINE__);

	// Delete any outstanding requests.
	$smfFunc['db_query']("
		DELETE FROM {$db_prefix}log_group_requests
		WHERE ID_GROUP IN (" . implode(', ', $groups) . ')', __FILE__, __LINE__);

	// Update the primary groups of members.
	$smfFunc['db_query']("
		UPDATE {$db_prefix}members
		SET ID_GROUP = 0
		WHERE ID_GROUP IN (" . implode(', ', $groups) . ')', __FILE__, __LINE__);

	// Update any inherited groups (Lose inheritance).
	$smfFunc['db_query']("
		UPDATE {$db_prefix}membergroups
		SET ID_PARENT = -2
		WHERE ID_PARENT IN (" . implode(', ', $groups) . ')', __FILE__, __LINE__);

	// Update the additional groups of members.
	$request = $smfFunc['db_query']("
		SELECT ID_MEMBER, additionalGroups
		FROM {$db_prefix}members
		WHERE FIND_IN_SET(" . implode(', additionalGroups) OR FIND_IN_SET(', $groups) . ', additionalGroups)', __FILE__, __LINE__);
	$updates = array();
	while ($row = $smfFunc['db_fetch_assoc']($request))
		$updates[$row['additionalGroups']][] = $row['ID_MEMBER'];
	$smfFunc['db_free_result']($request);

	foreach ($updates as $additionalGroups => $memberArray)
		updateMemberData($memberArray, array('additionalGroups' => '\'' . implode(',', array_diff(explode(',', $additionalGroups), $groups)) . '\''));

	// No boards can provide access to these membergroups anymore.
	$request = $smfFunc['db_query']("
		SELECT ID_BOARD, memberGroups
		FROM {$db_prefix}boards
		WHERE FIND_IN_SET(" . implode(', memberGroups) OR FIND_IN_SET(', $groups) . ', memberGroups)', __FILE__, __LINE__);
	$updates = array();
	while ($row = $smfFunc['db_fetch_assoc']($request))
		$updates[$row['memberGroups']][] = $row['ID_BOARD'];
	$smfFunc['db_free_result']($request);

	foreach ($updates as $memberGroups => $boardArray)
		$smfFunc['db_query']("
			UPDATE {$db_prefix}boards
			SET memberGroups = '" . implode(',', array_diff(explode(',', $memberGroups), $groups)) . "'
			WHERE ID_BOARD IN (" . implode(', ', $boardArray) . ")
			LIMIT 1", __FILE__, __LINE__);

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
		$smfFunc['db_query']("
			UPDATE {$db_prefix}members
			SET
				ID_GROUP = 0,
				additionalGroups = ''
			WHERE ID_MEMBER IN (" . implode(', ', $members) . ")" . (allowedTo('admin_forum') ? '' : "
				AND ID_GROUP != 1
				AND NOT FIND_IN_SET(1, additionalGroups)") . "
			LIMIT " . count($members), __FILE__, __LINE__);

		updateStats('postgroups', 'ID_MEMBER IN (' . implode(', ', $members) . ')');

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
	$request = $smfFunc['db_query']("
		SELECT ID_GROUP
		FROM {$db_prefix}membergroups
		WHERE minPosts != -1", __FILE__, __LINE__);
	while ($row = $smfFunc['db_fetch_assoc']($request))
		$implicitGroups[] = $row['ID_GROUP'];
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
	$smfFunc['db_query']("
		UPDATE {$db_prefix}members
		SET ID_GROUP = 0
		WHERE ID_GROUP IN (" . implode(', ', $groups) . ")
			AND ID_MEMBER IN (" . implode(', ', $members) . ")
		LIMIT " . count($members), __FILE__, __LINE__);

	// Those who have it as part of their additional group must be updated the long way... sadly.
	$request = $smfFunc['db_query']("
		SELECT ID_MEMBER, additionalGroups
		FROM {$db_prefix}members
		WHERE (FIND_IN_SET(" . implode(', additionalGroups) OR FIND_IN_SET(', $groups) . ", additionalGroups))
			AND ID_MEMBER IN (" . implode(', ', $members) . ")
		LIMIT " . count($members), __FILE__, __LINE__);
	$updates = array();
	while ($row = $smfFunc['db_fetch_assoc']($request))
		$updates[$row['additionalGroups']][] = $row['ID_MEMBER'];
	$smfFunc['db_free_result']($request);

	foreach ($updates as $additionalGroups => $memberArray)
		$smfFunc['db_query']("
			UPDATE {$db_prefix}members
			SET additionalGroups = '" . implode(',', array_diff(explode(',', $additionalGroups), $groups)) . "'
			WHERE ID_MEMBER IN (" . implode(', ', $memberArray) . ")
			LIMIT " . count($memberArray), __FILE__, __LINE__);

	// Their post groups may have changed now...
	updateStats('postgroups', 'ID_MEMBER IN (' . implode(', ', $members) . ')');

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
	$request = $smfFunc['db_query']("
		SELECT ID_GROUP
		FROM {$db_prefix}membergroups
		WHERE minPosts != -1", __FILE__, __LINE__);
	$implicitGroups = array(-1, 0, 3);
	while ($row = $smfFunc['db_fetch_assoc']($request))
		$implicitGroups[] = $row['ID_GROUP'];
	$smfFunc['db_free_result']($request);

	// Sorry, you can't join an implicit group.
	if (in_array($group, $implicitGroups) || empty($members))
		return false;

	// Only admins can add admins.
	if ($group == 1 && !allowedTo('admin_forum'))
		return false;

	// Do the actual updates.
	if ($type == 'only_additional')
		$smfFunc['db_query']("
			UPDATE {$db_prefix}members
			SET additionalGroups = IF(additionalGroups = '', '$group', CONCAT(additionalGroups, ',$group'))
			WHERE ID_MEMBER IN (" . implode(', ', $members) . ")
				AND ID_GROUP != $group
				AND NOT FIND_IN_SET($group, additionalGroups)
			LIMIT " . count($members), __FILE__, __LINE__);
	elseif ($type == 'only_primary' || $type == 'force_primary')
		$smfFunc['db_query']("
			UPDATE {$db_prefix}members
			SET ID_GROUP = $group
			WHERE ID_MEMBER IN (" . implode(', ', $members) . ")" . ($type == 'force_primary' ? '' : "
				AND ID_GROUP = 0
				AND NOT FIND_IN_SET($group, additionalGroups)") . "
			LIMIT " . count($members), __FILE__, __LINE__);
	elseif ($type == 'auto')
		$smfFunc['db_query']("
			UPDATE {$db_prefix}members
			SET
				additionalGroups = IF(ID_GROUP = 0, additionalGroups, IF(additionalGroups = '', '$group', CONCAT(additionalGroups, ',$group'))),
				ID_GROUP = IF(ID_GROUP = 0, $group, ID_GROUP)
			WHERE ID_MEMBER IN (" . implode(', ', $members) . ")
				AND ID_GROUP != $group
				AND NOT FIND_IN_SET($group, additionalGroups)
			LIMIT " . count($members), __FILE__, __LINE__);
	// Ack!!?  What happened?
	else
		trigger_error('addMembersToGroup(): Unknown type \'' . $type . '\'', E_USER_WARNING);

	// Update their postgroup statistics.
	updateStats('postgroups', 'ID_MEMBER IN (' . implode(', ', $members) . ')');

	return true;
}

function listMembergroupMembers_Href(&$members, $membergroup, $limit = null)
{
	global $db_prefix, $scripturl, $txt, $smfFunc;

	$request = $smfFunc['db_query']("
		SELECT ID_MEMBER, realName
		FROM {$db_prefix}members
		WHERE ID_GROUP = $membergroup OR FIND_IN_SET($membergroup, additionalGroups)" . ($limit === null ? '' : "
		LIMIT " . ($limit + 1)), __FILE__, __LINE__);
	$members = array();
	while ($row = $smfFunc['db_fetch_assoc']($request))
		$members[] = '<a href="' . $scripturl . '?action=profile;u=' . $row['ID_MEMBER'] . '">' . $row['realName'] . '</a>';
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