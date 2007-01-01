<?php
/**********************************************************************************
* Subs-Members.php                                                                *
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

	bool isReservedName(string name, int id_member = 0, bool is_name = true, bool fatal = true)
		- checks if name is a reserved name or username.
		- if is_name is false, the name is assumed to be a username.
		- the id_member variable is used to ignore duplicate matches with the
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

	int reattributePosts(int id_member, string email = none, bool add_to_post_count = false)
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
	global $db_prefix, $sourcedir, $modSettings, $user_info, $smfFunc;

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

		if ($user == $user_info['id'])
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
	if (!allowedTo('admin_forum') && (count($users) != 1 || $users[0] != $user_info['id']))
	{
		$request = $smfFunc['db_query']('', "
			SELECT id_member
			FROM {$db_prefix}members
			WHERE id_member IN (" . implode(', ', $users) . ")
				AND (id_group = 1 OR FIND_IN_SET(1, additional_groups) != 0)
			LIMIT " . count($users), __FILE__, __LINE__);
		$admins = array();
		while ($row = $smfFunc['db_fetch_assoc']($request))
			$admins[] = $row['id_member'];
		$smfFunc['db_free_result']($request);

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
	$smfFunc['db_query']('', "
		UPDATE {$db_prefix}messages
		SET id_member = 0" . (!empty($modSettings['allow_hide_email']) ? ", poster_email = ''" : '') . "
		WHERE id_member $condition", __FILE__, __LINE__);
	$smfFunc['db_query']('', "
		UPDATE {$db_prefix}polls
		SET id_member = 0
		WHERE id_member $condition", __FILE__, __LINE__);

	// Make these peoples' posts guest first posts and last posts.
	$smfFunc['db_query']('', "
		UPDATE {$db_prefix}topics
		SET id_member_started = 0
		WHERE id_member_started $condition", __FILE__, __LINE__);
	$smfFunc['db_query']('', "
		UPDATE {$db_prefix}topics
		SET id_member_updated = 0
		WHERE id_member_updated $condition", __FILE__, __LINE__);

	$smfFunc['db_query']('', "
		UPDATE {$db_prefix}log_actions
		SET id_member = 0
		WHERE id_member $condition", __FILE__, __LINE__);

	$smfFunc['db_query']('', "
		UPDATE {$db_prefix}log_banned
		SET id_member = 0
		WHERE id_member $condition", __FILE__, __LINE__);

	$smfFunc['db_query']('', "
		UPDATE {$db_prefix}log_errors
		SET id_member = 0
		WHERE id_member $condition", __FILE__, __LINE__);

	// Delete the member.
	$smfFunc['db_query']('', "
		DELETE FROM {$db_prefix}members
		WHERE id_member $condition", __FILE__, __LINE__);

	// Delete the logs...
	$smfFunc['db_query']('', "
		DELETE FROM {$db_prefix}log_boards
		WHERE id_member $condition", __FILE__, __LINE__);
	$smfFunc['db_query']('', "
		DELETE FROM {$db_prefix}log_group_requests
		WHERE id_member $condition", __FILE__, __LINE__);
	$smfFunc['db_query']('', "
		DELETE FROM {$db_prefix}log_karma
		WHERE id_target $condition
			OR id_executor $condition", __FILE__, __LINE__);
	$smfFunc['db_query']('', "
		DELETE FROM {$db_prefix}log_mark_read
		WHERE id_member $condition", __FILE__, __LINE__);
	$smfFunc['db_query']('', "
		DELETE FROM {$db_prefix}log_notify
		WHERE id_member $condition", __FILE__, __LINE__);
	$smfFunc['db_query']('', "
		DELETE FROM {$db_prefix}log_online
		WHERE id_member $condition", __FILE__, __LINE__);
	$smfFunc['db_query']('', "
		DELETE FROM {$db_prefix}log_polls
		WHERE id_member $condition", __FILE__, __LINE__);
	$smfFunc['db_query']('', "
		DELETE FROM {$db_prefix}log_topics
		WHERE id_member $condition", __FILE__, __LINE__);
	$smfFunc['db_query']('', "
		DELETE FROM {$db_prefix}collapsed_categories
		WHERE id_member $condition", __FILE__, __LINE__);

	// Delete personal messages.
	require_once($sourcedir . '/PersonalMessage.php');
	deleteMessages(null, null, $users);

	$smfFunc['db_query']('', "
		UPDATE {$db_prefix}personal_messages
		SET id_member_from = 0
		WHERE id_member_from $condition", __FILE__, __LINE__);

	// Delete avatar.
	require_once($sourcedir . '/ManageAttachments.php');
	removeAttachments('a.id_member ' . $condition);

	// It's over, no more moderation for you.
	$smfFunc['db_query']('', "
		DELETE FROM {$db_prefix}moderators
		WHERE id_member $condition", __FILE__, __LINE__);
	$smfFunc['db_query']('', "
		DELETE FROM {$db_prefix}group_moderators
		WHERE id_member $condition", __FILE__, __LINE__);

	// If you don't exist we can't ban you.
	$smfFunc['db_query']('', "
		DELETE FROM {$db_prefix}ban_items
		WHERE id_member $condition", __FILE__, __LINE__);

	// Remove individual theme settings.
	$smfFunc['db_query']('', "
		DELETE FROM {$db_prefix}themes
		WHERE id_member $condition", __FILE__, __LINE__);

	// These users are nobody's buddy nomore.
	$request = $smfFunc['db_query']('', "
		SELECT id_member, pm_ignore_list, buddy_list
		FROM {$db_prefix}members
		WHERE FIND_IN_SET(" . implode(', pm_ignore_list) OR FIND_IN_SET(', $users) . ', pm_ignore_list) OR FIND_IN_SET(' . implode(', buddy_list) OR FIND_IN_SET(', $users) . ', buddy_list)', __FILE__, __LINE__);
	while ($row = $smfFunc['db_fetch_assoc']($request))
		$smfFunc['db_query']('', "
			UPDATE {$db_prefix}members
			SET
				pm_ignore_list = '" . implode(',', array_diff(explode(',', $row['pm_ignore_list']), $users)) . "',
				buddy_list = '" . implode(',', array_diff(explode(',', $row['buddy_list']), $users)) . "'
			WHERE id_member = $row[id_member]", __FILE__, __LINE__);
	$smfFunc['db_free_result']($request);

	// Make sure no member's birthday is still sticking in the calendar...
	updateStats('calendar');
	updateStats('member');
}

function registerMember(&$regOptions)
{
	global $scripturl, $txt, $modSettings, $db_prefix, $context, $sourcedir;
	global $user_info, $options, $settings, $smfFunc;

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
		fatal_lang_error('need_username', false);

	// Spaces and other odd characters are evil...
	$regOptions['username'] = preg_replace('~[\t\n\r\x0B\0' . ($context['utf8'] ? ($context['server']['complex_preg_chars'] ? '\x{C2A0}' : chr(0xC2) . chr(0xA0)) : '\xA0') . ']+~' . ($context['utf8'] ? 'u' : ''), ' ', $regOptions['username']);

	// Don't use too long a name.
	if ($smfFunc['strlen']($regOptions['username']) > 25)
		$regOptions['username'] = $smfFunc['htmltrim']($smfFunc['substr']($regOptions['username'], 0, 25));

	// Only these characters are permitted.
	if (preg_match('~[<>&"\'=\\\]~', $regOptions['username']) != 0 || $regOptions['username'] == '_' || $regOptions['username'] == '|' || strpos($regOptions['username'], '[code') !== false || strpos($regOptions['username'], '[/code') !== false)
		fatal_lang_error('error_invalid_characters_username', false);

	if (stristr($regOptions['username'], $txt['guest_title']) !== false)
		fatal_lang_error('username_reserved', 'general', array($txt['guest_title']));

	// !!! Separate the sprintf?
	if (empty($regOptions['email']) || preg_match('~^[0-9A-Za-z=_+\-/][0-9A-Za-z=_\'+\-/\.]*@[\w\-]+(\.[\w\-]+)*(\.[\w]{2,6})$~', $smfFunc['db_unescape_string']($regOptions['email'])) === 0 || strlen($smfFunc['db_unescape_string']($regOptions['email'])) > 255)
		fatal_error(sprintf($txt['valid_email_needed'], $regOptions['username']), false);

	if (!empty($regOptions['check_reserved_name']) && isReservedName($regOptions['username'], 0, false))
	{
		if ($regOptions['password'] == 'chocolate cake')
			fatal_error('Sorry, I don\'t take bribes... you\'ll need to come up with a different name.', false);
		fatal_error('(' . htmlspecialchars($regOptions['username']) . ') ' . $txt['name_in_use'], false);
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
		fatal_lang_error('passwords_dont_match', false);

	// That's kind of easy to guess...
	if ($regOptions['password'] == '')
		fatal_lang_error('no_password', false);

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
	$request = $smfFunc['db_query']('', "
		SELECT id_member
		FROM {$db_prefix}members
		WHERE email_address = '$regOptions[email]'
			OR email_address = '$regOptions[username]'
		LIMIT 1", __FILE__, __LINE__);
	// !!! Separate the sprintf?
	if ($smfFunc['db_num_rows']($request) != 0)
		fatal_lang_error('email_in_use', false, array(htmlspecialchars($regOptions['email'])));
	$smfFunc['db_free_result']($request);

	// Some of these might be overwritten. (the lower ones that are in the arrays below.)
	$regOptions['register_vars'] = array(
		'member_name' => "'$regOptions[username]'",
		'email_address' => "'$regOptions[email]'",
		'passwd' => '\'' . sha1(strtolower($regOptions['username']) . $regOptions['password']) . '\'',
		'password_salt' => '\'' . substr(md5(rand()), 0, 4) . '\'',
		'posts' => 0,
		'date_registered' => time(),
		'member_ip' => "'$user_info[ip]'",
		'member_ip2' => "'$_SERVER[BAN_CHECK_IP]'",
		'validation_code' => "'$validation_code'",
		'real_name' => "'$regOptions[username]'",
		'personal_text' => '\'' . $smfFunc['db_escape_string']($modSettings['default_personal_text']) . '\'',
		'pm_email_notify' => 1,
		'id_theme' => 0,
		'id_post_group' => 4,
		'lngfile' => "''",
		'buddy_list' => "''",
		'pm_ignore_list' => "''",
		'message_labels' => "''",
		'personal_text' => "''",
		'website_title' => "''",
		'website_url' => "''",
		'location' => "''",
		'icq' => "''",
		'aim' => "''",
		'yim' => "''",
		'msn' => "''",
		'time_format' => "''",
		'signature' => "''",
		'avatar' => "''",
		'usertitle' => "''",
		'secret_question' => "''",
		'secret_answer' => "''",
		'additional_groups' => "''",
		'ignore_boards' => "''",
		'smiley_set' => "''",
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
		// Make sure the id_group will be valid, if this is an administator.
		$regOptions['register_vars']['id_group'] = $regOptions['memberGroup'] == 1 && !allowedTo('admin_forum') ? 0 : $regOptions['memberGroup'];

		// Check if this group is assignable.
		$unassignableGroups = array(-1, 3);
		$request = $smfFunc['db_query']('', "
			SELECT id_group
			FROM {$db_prefix}membergroups
			WHERE min_posts != -1", __FILE__, __LINE__);
		while ($row = $smfFunc['db_fetch_assoc']($request))
			$unassignableGroups[] = $row['id_group'];
		$smfFunc['db_free_result']($request);

		if (in_array($regOptions['register_vars']['id_group'], $unassignableGroups))
			$regOptions['register_vars']['id_group'] = 0;
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
	$smfFunc['db_query']('', "
		INSERT INTO {$db_prefix}members
			(" . implode(', ', array_keys($regOptions['register_vars'])) . ")
		VALUES (" . implode(', ', $regOptions['register_vars']) . ')', __FILE__, __LINE__);
	$memberID = db_insert_id("{$db_prefix}members", 'id_member');

	// Grab their real name and send emails using it.
	$real_name = substr($regOptions['register_vars']['real_name'], 1, -1);

	// Update the number of members and latest member's info - and pass the name, but remove the 's.
	updateStats('member', $memberID, $real_name);

	// Theme variables too?
	if (!empty($theme_vars))
	{
		$inserts = array();
		foreach ($theme_vars as $var => $val)
			$inserts[] = array($memberID, "SUBSTRING('$var', 1, 255)", "SUBSTRING('$val', 1, 65534)");
		$smfFunc['db_insert']('insert',
			"{$db_prefix}themes",
			array('id_member', 'variable', 'value'),
			$inserts,
			array('id_member', 'variable'), __FILE__, __LINE__
		);
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
			sendmail($regOptions['email'], $txt['register_subject'], sprintf($txt[$email_message], $real_name, $regOptions['username'], $regOptions['password'], $validation_code, $scripturl . '?action=activate;u=' . $memberID . ';code=' . $validation_code), null, null, false, 3);

		// All admins are finished here.
		return $memberID;
	}

	// Can post straight away - welcome them to your fantastic community...
	if ($regOptions['require'] == 'nothing')
	{
		if (!empty($regOptions['send_welcome_email']))
			sendmail($regOptions['email'], $txt['register_subject'], sprintf($txt['register_immediate_message'], $real_name, $regOptions['username'], $regOptions['password']), null, null, false, 4);

		// Send admin their notification.
		adminNotify('standard', $memberID, $regOptions['username']);
	}
	// Need to activate their account - or fall under COPPA.
	elseif ($regOptions['require'] == 'activation' || $regOptions['require'] == 'coppa')
		sendmail($regOptions['email'], $txt['register_subject'], sprintf($txt['register_activate_message'], $real_name, $regOptions['username'], $regOptions['password'], $validation_code, $scripturl . '?action=activate;u=' . $memberID . ';code=' . $validation_code), null, null, false, 4);
	// Must be awaiting approval.
	else
	{
		sendmail($regOptions['email'], $txt['register_subject'], sprintf($txt['register_pending_message'], $real_name, $regOptions['username'], $regOptions['password']), null, null, false, 3);

		// Admin gets informed here...
		adminNotify('approval', $memberID, $regOptions['username']);
	}

	// Okay, they're for sure registered... make sure the session is aware of this for security. (Just married :P!)
	$_SESSION['just_registered'] = 1;

	return $memberID;
}

// Check if a name is in the reserved words list. (name, current member id, name/username?.)
function isReservedName($name, $current_ID_MEMBER = 0, $is_name = true, $fatal = true)
{
	global $user_info, $modSettings, $db_prefix, $smfFunc;

	 $checkName = $smfFunc['strtolower']($name);

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
			$reservedCheck = empty($modSettings['reserveCase']) ? $smfFunc['strtolower']($reserved) : $reserved;
			// If it's not just entire word, check for it in there somewhere...
			if ($checkMe == $reservedCheck || ($smfFunc['strpos']($checkMe, $reservedCheck) !== false && empty($modSettings['reserveWord'])))
				if ($fatal)
					fatal_lang_error('username_reserved', 'password', array($reserved));
				else
					return true;
		}

		$censor_name = $name;
		if (censorText($censor_name) != $name)
			if ($fatal)
				fatal_lang_error('name_censored', 'password', array($name));
			else
				return true;
	}

	// Get rid of any SQL parts of the reserved name...
	$checkName = strtr($name, array('_' => '\\_', '%' => '\\%'));

	// Make sure they don't want someone else's name.
	$request = $smfFunc['db_query']('', "
		SELECT id_member
		FROM {$db_prefix}members
		WHERE " . (empty($current_ID_MEMBER) ? '' : "id_member != $current_ID_MEMBER
			AND ") . "(real_name LIKE '$checkName' OR member_name LIKE '$checkName')
		LIMIT 1", __FILE__, __LINE__);
	if ($smfFunc['db_num_rows']($request) > 0)
	{
		$smfFunc['db_free_result']($request);
		return true;
	}

	// Does name case insensitive match a member group name?
	$request = $smfFunc['db_query']('', "
		SELECT id_group
		FROM {$db_prefix}membergroups
		WHERE group_name LIKE '$checkName'
		LIMIT 1", __FILE__, __LINE__);
	if ($smfFunc['db_num_rows']($request) > 0)
	{
		$smfFunc['db_free_result']($request);
		return true;
	}

	// Okay, they passed.
	return false;
}

// Get a list of groups that have a given permission (on a given board).
function groupsAllowedTo($permission, $board_id = null)
{
	global $db_prefix, $modSettings, $board_info, $smfFunc;

	// Admins are allowed to do anything.
	$member_groups = array(
		'allowed' => array(1),
		'denied' => array(),
	);

	// Assume we're dealing with regular permissions (like profile_view_own).
	if ($board_id === null)
	{
		$request = $smfFunc['db_query']('', "
			SELECT id_group, add_deny
			FROM {$db_prefix}permissions
			WHERE permission = '$permission'", __FILE__, __LINE__);
		while ($row = $smfFunc['db_fetch_assoc']($request))
			$member_groups[$row['add_deny'] === '1' ? 'allowed' : 'denied'][] = $row['id_group'];
		$smfFunc['db_free_result']($request);
	}

	// Otherwise it's time to look at the board.
	else
	{
		// First get the profile of the given board.
		if (isset($board_info['id']) && $board_info['id'] == $board_id)
			$profile_id = $board_info['profile'];
		elseif ($board_id !== 0)
		{
			$request = $smfFunc['db_query']('', "
				SELECT id_profile
				FROM {$db_prefix}boards
				WHERE id_board = $board_id
				LIMIT 1", __FILE__, __LINE__);
			if ($smfFunc['db_num_rows']($request) == 0)
				fatal_lang_error('no_board');
			list ($profile_id) = $smfFunc['db_fetch_row']($request);
			$smfFunc['db_free_result']($request);
		}
		else
			$profile_id = 1;

		$request = $smfFunc['db_query']('', "
			SELECT bp.id_group, bp.add_deny
			FROM {$db_prefix}board_permissions AS bp
			WHERE bp.permission = '$permission'
				AND bp.id_profile = $profile_id", __FILE__, __LINE__);
		while ($row = $smfFunc['db_fetch_assoc']($request))
			$member_groups[$row['add_deny'] === '1' ? 'allowed' : 'denied'][] = $row['id_group'];
		$smfFunc['db_free_result']($request);
	}

	// Denied is never allowed.
	$member_groups['allowed'] = array_diff($member_groups['allowed'], $member_groups['denied']);

	return $member_groups;
}

// Get a list of members that have a given permission (on a given board).
function membersAllowedTo($permission, $board_id = null)
{
	global $db_prefix, $smfFunc;

	$member_groups = groupsAllowedTo($permission, $board_id);

	$include_moderators = in_array(3, $member_groups['allowed']) && $board_id !== null;
	$member_groups['allowed'] = array_diff($member_groups['allowed'], array(3));

	$exclude_moderators = in_array(3, $member_groups['denied']) && $board_id !== null;
	$member_groups['denied'] = array_diff($member_groups['denied'], array(3));

	$request = $smfFunc['db_query']('', "
		SELECT mem.id_member
		FROM {$db_prefix}members AS mem" . ($include_moderators || $exclude_moderators ? "
			LEFT JOIN {$db_prefix}moderators AS mods ON (mods.id_member = mem.id_member AND id_board = $board_id)" : '') . "
		WHERE (" . ($include_moderators ? "mods.id_member IS NOT NULL OR " : '') . 'id_group IN (' . implode(', ', $member_groups['allowed']) . ") OR FIND_IN_SET(" . implode(', mem.additional_groups) OR FIND_IN_SET(', $member_groups['allowed']) . ", mem.additional_groups))" . (empty($member_groups['denied']) ? '' : "
			AND NOT (" . ($exclude_moderators ? "mods.id_member IS NOT NULL OR " : '') . 'id_group IN (' . implode(', ', $member_groups['denied']) . ") OR FIND_IN_SET(" . implode(', mem.additional_groups) OR FIND_IN_SET(', $member_groups['denied']) . ", mem.additional_groups))"), __FILE__, __LINE__);
	$members = array();
	while ($row = $smfFunc['db_fetch_assoc']($request))
		$members[] = $row['id_member'];
	$smfFunc['db_free_result']($request);

	return $members;
}

// This function is used to reassociate members with relevant posts.
function reattributePosts($memID, $email = false, $post_count = false)
{
	global $db_prefix, $smfFunc;

	// !!! This should be done by member_name not email, or by both.

	// Firstly, if $email isn't passed find out the members email address.
	if ($email === false)
	{
		$request = $smfFunc['db_query']('', "
			SELECT email_address
			FROM {$db_prefix}members
			WHERE id_member = $memID
			LIMIT 1", __FILE__, __LINE__);
		list ($email) = $smfFunc['db_fetch_row']($request);
		$smfFunc['db_free_result']($request);
	}

	// If they want the post count restored then we need to do some research.
	if ($post_count)
	{
		$request = $smfFunc['db_query']('', "
			SELECT COUNT(*)
			FROM {$db_prefix}messages AS m
				INNER JOIN {$db_prefix}boards AS b ON (b.id_board = m.id_board AND b.count_posts = 1)
			WHERE m.id_member = 0
				AND m.poster_email = '$email'
				AND m.icon != 'recycled'", __FILE__, __LINE__);
		list ($messageCount) = $smfFunc['db_fetch_row']($request);
		$smfFunc['db_free_result']($request);

		updateMemberData($memID, array('posts' => 'posts + ' . $messageCount));
	}

	// Finally, update the posts themselves!
	$smfFunc['db_query']('', "
		UPDATE {$db_prefix}messages
		SET id_member = $memID
		WHERE poster_email = '$email'", __FILE__, __LINE__);

	return db_affected_rows();
}

// This simple function adds/removes the passed user from the current users buddy list.
function BuddyListToggle()
{
	global $user_info;

	checkSession('get');

	isAllowedTo('profile_identity_own');
	is_not_guest();

	if (empty($_REQUEST['u']))
		fatal_lang_error('no_access', false);
	$_REQUEST['u'] = (int) $_REQUEST['u'];

	// Remove if it's already there...
	if (in_array($_REQUEST['u'], $user_info['buddies']))
		$user_info['buddies'] = array_diff($user_info['buddies'], array($_REQUEST['u']));
	// ...or add if it's not.
	else
		$user_info['buddies'][] = (int) $_REQUEST['u'];

	// Update the settings.
	updateMemberData($user_info['id'], array('buddy_list' => "'" . implode(',', $user_info['buddies']) . "'"));

	// Redirect back to the profile
	redirectexit('action=profile;u=' . $_REQUEST['u']);
}

?>