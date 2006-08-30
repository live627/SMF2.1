<?php
/******************************************************************************
* Subs-members.php                                                            *
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

/* This file contains some useful functions for members and membergroups.

	void deleteMembers(array $users)
		- delete of one or more members.
		- requires profile_remove_own or profile_remove_any permission for
		  respectively removing your own account or any account.
		- non-admins cannot delete admins.
		- changes author of messages, topics and polls to guest authors.
		- removes all log entries concerning the deleted members, except the
		  error logs, ban logs and moderation logs.
		- removes these members' personal messages (only the inbox), avatars,
		  ban entries, theme settings, moderator positions, poll votes, and
		  karma votes.
		- updates member statistics afterwards.

	int registerMember(array options)
		- registers a member to the forum.
		- returns the ID of the newly created member.
		- allows two types of interface: 'guest' and 'admin'. The first
		  includes hammering protection, the latter can perform the
		  registration silently.
		- the strings used in the options array are assumed to be escaped.
		- allows to perform several checks on the input, e.g. reserved names.
		- adjusts member statistics.

	bool isReservedName(string name, int ID_MEMBER = 0, bool is_name = true)
		- checks if name is a reserved name or username.
		- if is_name is false, the name is assumed to be a username.
		- the ID_MEMBER variable is used to ignore duplicate matches with the
		  current member.

	array groupsAllowedTo(string permission, int board_id = null)
		- retrieves a list of membergroups that are allowed to do the given 
		  permission.
		- if board_id is not null, a board permission is assumed.
		- takes different permission settings into account.
		- returns an array containing an array for the allowed membergroup ID's
		  and an array for the denied membergroup ID's.

	array membersAllowedTo(string permission, int board_id = null)
		- retrieves a list of members that are allowed to do the given
		  permission.
		- if board_id is not null, a board permission is assumed.
		- takes different permission settings into account.
		- takes possible moderators (on board 'board_id') into account.
		- returns an array containing member ID's.

	int reattributePosts(int ID_MEMBER, string email = none, bool add_to_post_count = false)
		- reattribute guest posts to a specified member.
		- does not check for any permissions.
		- returns the number of successful reattributed posts.
		- if add_to_post_count is set, the member's post count is increased.

	void BuddyListToggle()
		- add a member to your buddy list or remove it.
		- requires profile_identity_own permission.
		- called by ?action=buddy;u=x;sesc=y.
		- redirects to ?action=profile;u=x.

*/

// Delete a group of/single member.
function deleteMembers($users)
{
	global $db_prefix, $sourcedir, $modSettings, $ID_MEMBER;

	// If it's not an array, make it so!
	if (!is_array($users))
		$users = array($users);
	else
		$users = array_unique($users);

	// Make sure there's no void user in here.
	$users = array_diff($users, array(0));

	// How many are they deleting?
	if (empty($users))
		return;
	elseif (count($users) == 1)
	{
		list ($user) = $users;
		$condition = '= ' . $user;

		if ($user == $ID_MEMBER)
			isAllowedTo('profile_remove_own');
		else
			isAllowedTo('profile_remove_any');
	}
	else
	{
		foreach ($users as $k => $v)
			$users[$k] = (int) $v;
		$condition = 'IN (' . implode(', ', $users) . ')';

		// Deleting more than one?  You can't have more than one account...
		isAllowedTo('profile_remove_any');
	}

	// Make sure they aren't trying to delete administrators if they aren't one.  But don't bother checking if it's just themself.
	if (!allowedTo('admin_forum') && (count($users) != 1 || $users[0] != $ID_MEMBER))
	{
		$request = db_query("
			SELECT ID_MEMBER
			FROM {$db_prefix}members
			WHERE ID_MEMBER IN (" . implode(', ', $users) . ")
				AND (ID_GROUP = 1 OR FIND_IN_SET(1, additionalGroups) != 0)
			LIMIT " . count($users), __FILE__, __LINE__);
		$admins = array();
		while ($row = mysql_fetch_assoc($request))
			$admins[] = $row['ID_MEMBER'];
		mysql_free_result($request);

		if (!empty($admins))
			$users = array_diff($users, $admins);
	}

	if (empty($users))
		return;

	// Log the action - regardless of who is deleting it.
	foreach ($users as $user)
	{
		// Integration rocks!
		if (isset($modSettings['integrate_delete_member']) && function_exists($modSettings['integrate_delete_member']))
			call_user_func($modSettings['integrate_delete_member'], $user);

		logAction('delete_member', array('member' => $user));
	}

	// Make these peoples' posts guest posts.
	db_query("
		UPDATE {$db_prefix}messages
		SET ID_MEMBER = 0" . (!empty($modSettings['allow_hideEmail']) ? ", posterEmail = ''" : '') . "
		WHERE ID_MEMBER $condition", __FILE__, __LINE__);
	db_query("
		UPDATE {$db_prefix}polls
		SET ID_MEMBER = 0
		WHERE ID_MEMBER $condition", __FILE__, __LINE__);

	// Make these peoples' posts guest first posts and last posts.
	db_query("
		UPDATE {$db_prefix}topics
		SET ID_MEMBER_STARTED = 0
		WHERE ID_MEMBER_STARTED $condition", __FILE__, __LINE__);
	db_query("
		UPDATE {$db_prefix}topics
		SET ID_MEMBER_UPDATED = 0
		WHERE ID_MEMBER_UPDATED $condition", __FILE__, __LINE__);

	db_query("
		UPDATE {$db_prefix}log_actions
		SET ID_MEMBER = 0
		WHERE ID_MEMBER $condition", __FILE__, __LINE__);

	db_query("
		UPDATE {$db_prefix}log_banned
		SET ID_MEMBER = 0
		WHERE ID_MEMBER $condition", __FILE__, __LINE__);

	db_query("
		UPDATE {$db_prefix}log_errors
		SET ID_MEMBER = 0
		WHERE ID_MEMBER $condition", __FILE__, __LINE__);

	// Delete the member.
	db_query("
		DELETE FROM {$db_prefix}members
		WHERE ID_MEMBER $condition
		LIMIT " . count($users), __FILE__, __LINE__);

	// Delete the logs...
	db_query("
		DELETE FROM {$db_prefix}log_boards
		WHERE ID_MEMBER $condition", __FILE__, __LINE__);
	db_query("
		DELETE FROM {$db_prefix}log_group_requests
		WHERE ID_MEMBER $condition", __FILE__, __LINE__);
	db_query("
		DELETE FROM {$db_prefix}log_karma
		WHERE ID_TARGET $condition
			OR ID_EXECUTOR $condition", __FILE__, __LINE__);
	db_query("
		DELETE FROM {$db_prefix}log_mark_read
		WHERE ID_MEMBER $condition", __FILE__, __LINE__);
	db_query("
		DELETE FROM {$db_prefix}log_notify
		WHERE ID_MEMBER $condition", __FILE__, __LINE__);
	db_query("
		DELETE FROM {$db_prefix}log_online
		WHERE ID_MEMBER $condition", __FILE__, __LINE__);
	db_query("
		DELETE FROM {$db_prefix}log_polls
		WHERE ID_MEMBER $condition", __FILE__, __LINE__);
	db_query("
		DELETE FROM {$db_prefix}log_topics
		WHERE ID_MEMBER $condition", __FILE__, __LINE__);
	db_query("
		DELETE FROM {$db_prefix}collapsed_categories
		WHERE ID_MEMBER $condition", __FILE__, __LINE__);

	// Delete personal messages.
	require_once($sourcedir . '/PersonalMessage.php');
	deleteMessages(null, null, $users);

	db_query("
		UPDATE {$db_prefix}personal_messages
		SET ID_MEMBER_FROM = 0
		WHERE ID_MEMBER_FROM $condition", __FILE__, __LINE__);

	// Delete avatar.
	require_once($sourcedir . '/ManageAttachments.php');
	removeAttachments('a.ID_MEMBER ' . $condition);

	// It's over, no more moderation for you.
	db_query("
		DELETE FROM {$db_prefix}moderators
		WHERE ID_MEMBER $condition", __FILE__, __LINE__);
	db_query("
		DELETE FROM {$db_prefix}group_moderators
		WHERE ID_MEMBER $condition", __FILE__, __LINE__);

	// If you don't exist we can't ban you.
	db_query("
		DELETE FROM {$db_prefix}ban_items
		WHERE ID_MEMBER $condition", __FILE__, __LINE__);

	// Remove individual theme settings.
	db_query("
		DELETE FROM {$db_prefix}themes
		WHERE ID_MEMBER $condition", __FILE__, __LINE__);

	// These users are nobody's buddy nomore.
	$request = db_query("
		SELECT ID_MEMBER, pm_ignore_list, buddy_list
		FROM {$db_prefix}members
		WHERE FIND_IN_SET(" . implode(', pm_ignore_list) OR FIND_IN_SET(', $users) . ', pm_ignore_list) OR FIND_IN_SET(' . implode(', buddy_list) OR FIND_IN_SET(', $users) . ', buddy_list)', __FILE__, __LINE__);
	while ($row = mysql_fetch_assoc($request))
		db_query("
			UPDATE {$db_prefix}members
			SET
				pm_ignore_list = '" . implode(',', array_diff(explode(',', $row['pm_ignore_list']), $users)) . "',
				buddy_list = '" . implode(',', array_diff(explode(',', $row['buddy_list']), $users)) . "'
			WHERE ID_MEMBER = $row[ID_MEMBER]
			LIMIT 1", __FILE__, __LINE__);
	mysql_free_result($request);

	// Make sure no member's birthday is still sticking in the calendar...
	updateStats('calendar');
	updateStats('member');
}

function registerMember(&$regOptions)
{
	global $scripturl, $txt, $modSettings, $db_prefix, $context, $sourcedir;
	global $user_info, $options, $settings, $func;

	loadLanguage('Login');

	// We'll need some external functions.
	require_once($sourcedir . '/Subs-Auth.php');
	require_once($sourcedir . '/Subs-Post.php');

	// Registration from the admin center, let them sweat a little more.
	if ($regOptions['interface'] == 'admin')
	{
		is_not_guest();
		isAllowedTo('moderate_forum');
	}
	// If you're an admin, you're special ;).
	elseif ($regOptions['interface'] == 'guest')
	{
		spamProtection('register');

		// You cannot register twice...
		if (empty($user_info['is_guest']))
			redirectexit();

		// Make sure they didn't just register with this session.
		if (!empty($_SESSION['just_registered']) && empty($modSettings['disableRegisterCheck']))
			fatal_lang_error('register_only_once', false);
	}

	// No name?!  How can you register with no name?
	if (empty($regOptions['username']))
		fatal_lang_error(37, false);

	// Spaces and other odd characters are evil...
	$regOptions['username'] = preg_replace('~[\t\n\r\x0B\0' . ($context['utf8'] ? '\x{C2A0}' : '\xA0') . ']+~' . ($context['utf8'] ? 'u' : ''), ' ', $regOptions['username']);

	// Don't use too long a name.
	if ($func['strlen']($regOptions['username']) > 25)
		$regOptions['username'] = $func['htmltrim']($func['substr']($regOptions['username'], 0, 25));

	// Only these characters are permitted.
	if (preg_match('~[<>&"\'=\\\]~', $regOptions['username']) != 0 || $regOptions['username'] == '_' || $regOptions['username'] == '|' || strpos($regOptions['username'], '[code') !== false || strpos($regOptions['username'], '[/code') !== false)
		fatal_lang_error(240, false);

	if (stristr($regOptions['username'], $txt[28]) !== false)
		fatal_lang_error(244, 'general', array($txt[28]));

	// !!! Separate the sprintf?
	if (empty($regOptions['email']) || preg_match('~^[0-9A-Za-z=_+\-/][0-9A-Za-z=_\'+\-/\.]*@[\w\-]+(\.[\w\-]+)*(\.[\w]{2,6})$~', stripslashes($regOptions['email'])) === 0 || strlen(stripslashes($regOptions['email'])) > 255)
		fatal_error(sprintf($txt[500], $regOptions['username']), false);

	if (!empty($regOptions['check_reserved_name']) && isReservedName($regOptions['username'], 0, false))
	{
		if ($regOptions['password'] == 'chocolate cake')
			fatal_error('Sorry, I don\'t take bribes... you\'ll need to come up with a different name.', false);
		fatal_error('(' . htmlspecialchars($regOptions['username']) . ') ' . $txt[473], false);
	}

	// Generate a validation code if it's supposed to be emailed.
	$validation_code = '';
	if ($regOptions['require'] == 'activation')
		$validation_code = substr(preg_replace('/\W/', '', md5(rand())), 0, 10);

	// If you haven't put in a password generated one.
	if ($regOptions['interface'] == 'admin' && $regOptions['password'] == '')
	{
		srand(time() + 1277);
		$regOptions['password'] = substr(preg_replace('/\W/', '', md5(rand())), 0, 10);
		$regOptions['password_check'] = $regOptions['password'];
	}
	// Does the first password match the second?
	elseif ($regOptions['password'] != $regOptions['password_check'])
		fatal_lang_error(213, false);

	// That's kind of easy to guess...
	if ($regOptions['password'] == '')
		fatal_lang_error(91, false);

	// Now perform hard password validation as required.
	if (!empty($regOptions['check_password_strength']))
	{
		$passwordError = validatePassword($regOptions['password'], $regOptions['username'], array($regOptions['email']));

		// Password isn't legal?
		if ($passwordError != null)
			fatal_lang_error('profile_error_password_' . $passwordError, false);
	}

	// You may not be allowed to register this email.
	if (!empty($regOptions['check_email_ban']))
		isBannedEmail($regOptions['email'], 'cannot_register', $txt['ban_register_prohibited']);

	// Check if the email address is in use.
	$request = db_query("
		SELECT ID_MEMBER
		FROM {$db_prefix}members
		WHERE emailAddress = '$regOptions[email]'
			OR emailAddress = '$regOptions[username]'
		LIMIT 1", __FILE__, __LINE__);
	// !!! Separate the sprintf?
	if (mysql_num_rows($request) != 0)
		fatal_error(sprintf($txt[730], htmlspecialchars($regOptions['email'])), false);
	mysql_free_result($request);

	// Some of these might be overwritten. (the lower ones that are in the arrays below.)
	$regOptions['register_vars'] = array(
		'memberName' => "'$regOptions[username]'",
		'emailAddress' => "'$regOptions[email]'",
		'passwd' => '\'' . sha1(strtolower($regOptions['username']) . $regOptions['password']) . '\'',
		'passwordSalt' => '\'' . substr(md5(rand()), 0, 4) . '\'',
		'posts' => 0,
		'dateRegistered' => time(),
		'memberIP' => "'$user_info[ip]'",
		'validation_code' => "'$validation_code'",
		'realName' => "'$regOptions[username]'",
		'personalText' => '\'' . addslashes($modSettings['default_personalText']) . '\'',
		'pm_email_notify' => 1,
		'ID_THEME' => 0,
		'ID_POST_GROUP' => 4,
		'lngfile' => "''",
		'buddy_list' => "''",
		'pm_ignore_list' => "''",
		'messageLabels' => "''",
		'personalText' => "''",
		'websiteTitle' => "''",
		'websiteUrl' => "''",
		'location' => "''",
		'ICQ' => "''",
		'AIM' => "''",
		'YIM' => "''",
		'MSN' => "''",
		'timeFormat' => "''",
		'signature' => "''",
		'avatar' => "''",
		'usertitle' => "''",
		'secretQuestion' => "''",
		'secretAnswer' => "''",
		'additionalGroups' => "''",
		'smileySet' => "''",
	);

	// Setup the activation status on this new account so it is correct - firstly is it an under age account?
	if ($regOptions['require'] == 'coppa')
	{
		$regOptions['register_vars']['is_activated'] = 5;
		// !!! This should be changed.  To what should be it be changed??
		$regOptions['register_vars']['validation_code'] = "''";
	}
	// Maybe it can be activated right away?
	elseif ($regOptions['require'] == 'nothing')
		$regOptions['register_vars']['is_activated'] = 1;
	// Maybe it must be activated by email?
	elseif ($regOptions['require'] == 'activation')
		$regOptions['register_vars']['is_activated'] = 0;
	// Otherwise it must be awaiting approval!
	else
		$regOptions['register_vars']['is_activated'] = 3;

	if (isset($regOptions['memberGroup']))
	{
		// Make sure the ID_GROUP will be valid, if this is an administator.
		$regOptions['register_vars']['ID_GROUP'] = $regOptions['memberGroup'] == 1 && !allowedTo('admin_forum') ? 0 : $regOptions['memberGroup'];

		// Check if this group is assignable.
		$unassignableGroups = array(-1, 3);
		$request = db_query("
			SELECT ID_GROUP
			FROM {$db_prefix}membergroups
			WHERE minPosts != -1", __FILE__, __LINE__);
		while ($row = mysql_fetch_assoc($request))
			$unassignableGroups[] = $row['ID_GROUP'];
		mysql_free_result($request);

		if (in_array($regOptions['register_vars']['ID_GROUP'], $unassignableGroups))
			$regOptions['register_vars']['ID_GROUP'] = 0;
	}

	// Integrate optional member settings to be set.
	if (!empty($regOptions['extra_register_vars']))
		foreach ($regOptions['extra_register_vars'] as $var => $value)
			$regOptions['register_vars'][$var] = $value;

	// Integrate optional user theme options to be set.
	$theme_vars = array();
	if (!empty($regOptions['theme_vars']))
		foreach ($regOptions['theme_vars'] as $var => $value)
			$theme_vars[$var] = $value;

	// Call an optional function to validate the users' input.
	if (isset($modSettings['integrate_register']) && function_exists($modSettings['integrate_register']))
		$modSettings['integrate_register']($regOptions, $theme_vars);

	// Register them into the database.
	db_query("
		INSERT INTO {$db_prefix}members
			(" . implode(', ', array_keys($regOptions['register_vars'])) . ")
		VALUES (" . implode(', ', $regOptions['register_vars']) . ')', __FILE__, __LINE__);
	$memberID = db_insert_id();

	// Grab their real name and send emails using it.
	$realName = substr($regOptions['register_vars']['realName'], 1, -1);

	// Update the number of members and latest member's info - and pass the name, but remove the 's.
	updateStats('member', $memberID, $realName);

	// Theme variables too?
	if (!empty($theme_vars))
	{
		$setString = '';
		foreach ($theme_vars as $var => $val)
			$setString .= "
				($memberID, SUBSTRING('$var', 1, 255), SUBSTRING('$val', 1, 65534)),";
		db_query("
			INSERT INTO {$db_prefix}themes
				(ID_MEMBER, variable, value)
			VALUES " . substr($setString, 0, -1), __FILE__, __LINE__);
	}

	// If it's enabled, increase the registrations for today.
	trackStats(array('registers' => '+'));

	// Administrative registrations are a bit different...
	if ($regOptions['interface'] == 'admin')
	{
		if ($regOptions['require'] == 'activation')
			$email_message = 'register_activate_message';
		elseif (!empty($regOptions['send_welcome_email']))
			$email_message = 'register_immediate_message';

		if (isset($email_message))
			sendmail($regOptions['email'], $txt['register_subject'], sprintf($txt[$email_message], $realName, $regOptions['username'], $regOptions['password'], $validation_code, $scripturl . '?action=activate;u=' . $memberID . ';code=' . $validation_code), null, null, false, 3);

		// All admins are finished here.
		return $memberID;
	}

	// Can post straight away - welcome them to your fantastic community...
	if ($regOptions['require'] == 'nothing')
	{
		if (!empty($regOptions['send_welcome_email']))
			sendmail($regOptions['email'], $txt['register_subject'], sprintf($txt['register_immediate_message'], $realName, $regOptions['username'], $regOptions['password']), null, null, false, 4);

		// Send admin their notification.
		adminNotify('standard', $memberID, $regOptions['username']);
	}
	// Need to activate their account - or fall under COPPA.
	elseif ($regOptions['require'] == 'activation' || $regOptions['require'] == 'coppa')
		sendmail($regOptions['email'], $txt['register_subject'], sprintf($txt['register_activate_message'], $realName, $regOptions['username'], $regOptions['password'], $validation_code, $scripturl . '?action=activate;u=' . $memberID . ';code=' . $validation_code), null, null, false, 4);
	// Must be awaiting approval.
	else
	{
		sendmail($regOptions['email'], $txt['register_subject'], sprintf($txt['register_pending_message'], $realName, $regOptions['username'], $regOptions['password']), null, null, false, 3);

		// Admin gets informed here...
		adminNotify('approval', $memberID, $regOptions['username']);
	}

	// Okay, they're for sure registered... make sure the session is aware of this for security. (Just married :P!)
	$_SESSION['just_registered'] = 1;

	return $memberID;
}

// Check if a name is in the reserved words list. (name, current member id, name/username?.)
function isReservedName($name, $current_ID_MEMBER = 0, $is_name = true)
{
	global $user_info, $modSettings, $db_prefix, $func;

	 $checkName = $func['strtolower']($name);

	// Administrators are never restricted ;).
	if (!allowedTo('moderate_forum') && ((!empty($modSettings['reserveName']) && $is_name) || !empty($modSettings['reserveUser']) && !$is_name))
	{
		$reservedNames = explode("\n", $modSettings['reserveNames']);
		// Case sensitive check?
		$checkMe = empty($modSettings['reserveCase']) ? $checkName : $name;

		// Check each name in the list...
		foreach ($reservedNames as $reserved)
		{
			if ($reserved == '')
				continue;

			// Case sensitive name?
			$reservedCheck = empty($modSettings['reserveCase']) ? $func['strtolower']($reserved) : $reserved;
			// If it's not just entire word, check for it in there somewhere...
			if ($checkMe == $reservedCheck || ($func['strpos']($checkMe, $reservedCheck) !== false && empty($modSettings['reserveWord'])))
				fatal_lang_error(244, 'password', array($reserved));
		}

		$censor_name = $name;
		if (censorText($censor_name) != $name)
			fatal_lang_error('name_censored', 'password', array($name));
	}

	// Get rid of any SQL parts of the reserved name...
	$checkName = strtr($name, array('_' => '\\_', '%' => '\\%'));

	// Make sure they don't want someone else's name.
	$request = db_query("
		SELECT ID_MEMBER
		FROM {$db_prefix}members
		WHERE " . (empty($current_ID_MEMBER) ? '' : "ID_MEMBER != $current_ID_MEMBER
			AND ") . "(realName LIKE '$checkName' OR memberName LIKE '$checkName')
		LIMIT 1", __FILE__, __LINE__);
	if (mysql_num_rows($request) > 0)
	{
		mysql_free_result($request);
		return true;
	}

	// Does name case insensitive match a member group name?
	$request = db_query("
		SELECT ID_GROUP
		FROM {$db_prefix}membergroups
		WHERE groupName LIKE '$checkName'
		LIMIT 1", __FILE__, __LINE__);
	if (mysql_num_rows($request) > 0)
	{
		mysql_free_result($request);
		return true;
	}

	// Okay, they passed.
	return false;
}

// Get a list of groups that have a given permission (on a given board).
function groupsAllowedTo($permission, $board_id = null)
{
	global $db_prefix, $modSettings, $board_info;

	// Admins are allowed to do anything.
	$memberGroups = array(
		'allowed' => array(1),
		'denied' => array(),
	);

	// Assume we're dealing with regular permissions (like profile_view_own).
	if ($board_id === null)
	{
		$request = db_query("
			SELECT ID_GROUP, addDeny
			FROM {$db_prefix}permissions
			WHERE permission = '$permission'", __FILE__, __LINE__);
		while ($row = mysql_fetch_assoc($request))
			$memberGroups[$row['addDeny'] === '1' ? 'allowed' : 'denied'][] = $row['ID_GROUP'];
		mysql_free_result($request);
	}

	// Otherwise it's time to look at the board.
	else
	{
		// First get the profile of the given board.
		if (isset($board_info['id']) && $board_info['id'] == $board_id)
			$profile_id = $board_info['profile'];
		elseif ($board_id !== 0)
		{
			$request = db_query("
				SELECT ID_PROFILE
				FROM {$db_prefix}boards
				WHERE ID_BOARD = $board_id
				LIMIT 1", __FILE__, __LINE__);
			if (mysql_num_rows($request) == 0)
				fatal_lang_error('smf232');
			list ($profile_id) = mysql_fetch_row($request);
			mysql_free_result($request);
		}
		else
			$profile_id = 1;

		$request = db_query("
			SELECT bp.ID_GROUP, bp.addDeny
			FROM {$db_prefix}board_permissions AS bp
			WHERE bp.permission = '$permission'
				AND bp.ID_PROFILE = $profile_id", __FILE__, __LINE__);
		while ($row = mysql_fetch_assoc($request))
			$memberGroups[$row['addDeny'] === '1' ? 'allowed' : 'denied'][] = $row['ID_GROUP'];
		mysql_free_result($request);
	}

	// Denied is never allowed.
	$memberGroups['allowed'] = array_diff($memberGroups['allowed'], $memberGroups['denied']);

	return $memberGroups;
}

// Get a list of members that have a given permission (on a given board).
function membersAllowedTo($permission, $board_id = null)
{
	global $db_prefix;

	$memberGroups = groupsAllowedTo($permission, $board_id);

	$include_moderators = in_array(3, $memberGroups['allowed']) && $board_id !== null;
	$memberGroups['allowed'] = array_diff($memberGroups['allowed'], array(3));

	$exclude_moderators = in_array(3, $memberGroups['denied']) && $board_id !== null;
	$memberGroups['denied'] = array_diff($memberGroups['denied'], array(3));

	$request = db_query("
		SELECT mem.ID_MEMBER
		FROM {$db_prefix}members AS mem" . ($include_moderators || $exclude_moderators ? "
			LEFT JOIN {$db_prefix}moderators AS mods ON (mods.ID_MEMBER = mem.ID_MEMBER AND ID_BOARD = $board_id)" : '') . "
		WHERE (" . ($include_moderators ? "mods.ID_MEMBER IS NOT NULL OR " : '') . 'ID_GROUP IN (' . implode(', ', $memberGroups['allowed']) . ") OR FIND_IN_SET(" . implode(', mem.additionalGroups) OR FIND_IN_SET(', $memberGroups['allowed']) . ", mem.additionalGroups))" . (empty($memberGroups['denied']) ? '' : "
			AND NOT (" . ($exclude_moderators ? "mods.ID_MEMBER IS NOT NULL OR " : '') . 'ID_GROUP IN (' . implode(', ', $memberGroups['denied']) . ") OR FIND_IN_SET(" . implode(', mem.additionalGroups) OR FIND_IN_SET(', $memberGroups['denied']) . ", mem.additionalGroups))"), __FILE__, __LINE__);
	$members = array();
	while ($row = mysql_fetch_assoc($request))
		$members[] = $row['ID_MEMBER'];
	mysql_free_result($request);

	return $members;
}

// This function is used to reassociate members with relevant posts.
function reattributePosts($memID, $email = false, $post_count = false)
{
	global $db_prefix;

	// !!! This should be done by memberName not email, or by both.

	// Firstly, if $email isn't passed find out the members email address.
	if ($email === false)
	{
		$request = db_query("
			SELECT emailAddress
			FROM {$db_prefix}members
			WHERE ID_MEMBER = $memID
			LIMIT 1", __FILE__, __LINE__);
		list ($email) = mysql_fetch_row($request);
		mysql_free_result($request);
	}

	// If they want the post count restored then we need to do some research.
	if ($post_count)
	{
		$request = db_query("
			SELECT COUNT(*)
			FROM ({$db_prefix}messages AS m, {$db_prefix}boards AS b)
			WHERE m.ID_MEMBER = 0
				AND m.posterEmail = '$email'
				AND m.icon != 'recycled'
				AND b.ID_BOARD = m.ID_BOARD
				AND b.countPosts = 1", __FILE__, __LINE__);
		list ($messageCount) = mysql_fetch_row($request);
		mysql_free_result($request);

		updateMemberData($memID, array('posts' => 'posts + ' . $messageCount));
	}

	// Finally, update the posts themselves!
	db_query("
		UPDATE {$db_prefix}messages
		SET ID_MEMBER = $memID
		WHERE posterEmail = '$email'", __FILE__, __LINE__);

	return db_affected_rows();
}

// This simple function adds/removes the passed user from the current users buddy list.
function BuddyListToggle()
{
	global $user_info, $ID_MEMBER;

	checkSession('get');

	isAllowedTo('profile_identity_own');
	is_not_guest();

	if (empty($_REQUEST['u']))
		fatal_lang_error(1, false);
	$_REQUEST['u'] = (int) $_REQUEST['u'];

	// Remove if it's already there...
	if (in_array($_REQUEST['u'], $user_info['buddies']))
		$user_info['buddies'] = array_diff($user_info['buddies'], array($_REQUEST['u']));
	// ...or add if it's not.
	else
		$user_info['buddies'][] = (int) $_REQUEST['u'];

	// Update the settings.
	updateMemberData($ID_MEMBER, array('buddy_list' => "'" . implode(',', $user_info['buddies']) . "'"));

	// Redirect back to the profile
	redirectexit('action=profile;u=' . $_REQUEST['u']);
}

?>