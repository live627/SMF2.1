<?php
/**********************************************************************************
* Profile.php                                                                     *
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

/*	This file has the primary job of showing and editing people's profiles.
	It also allows the user to change some of their or another's preferences,
	and such things.  It uses the following functions:

	void ModifyProfile(array errors = none)
		// !!!

	void ModifyProfile2()
		// !!!

	void saveProfileChanges(array &profile_variables, array &errors, int id_member)
		// !!!

	void makeThemeChanges(int id_member, int id_theme)
		// !!!

	void makeNotificationChanges(int id_member)
		// !!!

	void makeAvatarChanges(int id_member, array &errors)
		// !!!

	void makeCustomFieldChanges(int id_member, string area)
		// !!!

	void summary(int id_member)
		// !!!

	void showPosts(int id_member)
		// !!!

	void showAttachments(int id_member)
		// !!!

	void statPanel(int id_member)
		// !!!

	void trackUser(int id_member)
		// !!!

	void TrackIP(int id_member = none)
		// !!!

	void showPermissions(int id_member)
		// !!!

	void account(int id_member)
		// !!!

	void forumProfile(int id_member)
		// !!!

	array getAvatars(string directory, int level)
		// !!!

	void theme(int id_member)
		// !!!

	void notification(int id_member)
		// !!!

	void groupMembership(int id_member)
		// !!!

	void deleteAccount(int id_member)
		// !!!

	void deleteAccount2(array profile_variables, array &errors, int id_member)
		// !!!

	void rememberPostData()
		// !!!

	void loadThemeOptions(int id_member)
		// !!!

	void loadCustomFields(int id_member, string area)
		// !!!
	
	void ignoreboards(int id_member)
		// !!!

	Adding new fields to the profile:
	---------------------------------------------------------------------------
		// !!!
*/

// Allow the change or view of profiles...
function ModifyProfile($post_errors = array())
{
	global $txt, $scripturl, $user_info, $context, $sourcedir, $user_profile, $modSettings;

	// Don't reload this as we may have processed error strings.
	if (empty($post_errors))
		loadLanguage('Profile');
	loadTemplate('Profile');

	/* Set allowed sub-actions.

	 The format of $sa_allowed is as follows:

	$sa_allowed = array(
		'sub-action' => array(permission_array_for_editing_OWN_profile, permission_array_for_editing_ANY_profile[, require_validation]),
		...
	);

	*/

	$sa_allowed = array(
		'summary' => array(array('profile_view_any', 'profile_view_own'), array('profile_view_any')),
		'statPanel' => array(array('profile_view_any', 'profile_view_own'), array('profile_view_any')),
		'showPosts' => array(array('profile_view_any', 'profile_view_own'), array('profile_view_any')),
		'editBuddies' => array(array('profile_extra_any', 'profile_extra_own'), array()),
		'trackUser' => array(array('moderate_forum'), array('moderate_forum')),
		'trackIP' => array(array('moderate_forum'), array('moderate_forum')),
		'showPermissions' => array(array('manage_permissions'), array('manage_permissions')),
		'account' => array(array('manage_membergroups', 'profile_identity_any', 'profile_identity_own'), array('manage_membergroups', 'profile_identity_any')),
		'forumProfile' => array(array('profile_extra_any', 'profile_extra_own'), array('profile_extra_any')),
		'theme' => array(array('profile_extra_any', 'profile_extra_own'), array('profile_extra_any')),
		'notification' => array(array('profile_extra_any', 'profile_extra_own'), array('profile_extra_any')),
		'groupMembership' => array(array('profile_view_own'), array('manage_membergroups')),
		'deleteAccount' => array(array('profile_remove_any', 'profile_remove_own'), array('profile_remove_any')),
		'ignoreboards' => array(array('profile_extra_any', 'profile_extra_own'), array('profile_extra_any')),
	);

	// Set the profile layer to be displayed.
	$context['template_layers'][] = 'profile';

	// Did we get the user by name...
	if (isset($_REQUEST['user']))
		$memberResult = loadMemberData($_REQUEST['user'], true, 'profile');
	// ... or by id_member?
	elseif (!empty($_REQUEST['u']))
		$memberResult = loadMemberData((int) $_REQUEST['u'], false, 'profile');
	// If it was just ?action=profile, edit your own profile.
	else
		$memberResult = loadMemberData($user_info['id'], false, 'profile');

	// Check if loadMemberData() has returned a valid result.
	if (!is_array($memberResult))
		fatal_lang_error('not_a_user', false);

	// If all went well, we have a valid member ID!
	list ($memID) = $memberResult;

	// Is this the profile of the user himself or herself?
	$context['user']['is_owner'] = $memID == $user_info['id'];

	// No Subaction?
	if (!isset($_REQUEST['sa']) || !isset($sa_allowed[$_REQUEST['sa']]))
	{
		// Pick the first subaction you're allowed to see.
		if ((allowedTo('profile_view_own') && $context['user']['is_owner']) || allowedTo('profile_view_any'))
			$_REQUEST['sa'] = 'summary';
		elseif (allowedTo('moderate_forum'))
			$_REQUEST['sa'] = 'trackUser';
		elseif (allowedTo('manage_permissions'))
			$_REQUEST['sa'] = 'showPermissions';
		elseif ((allowedTo('profile_identity_own') && $context['user']['is_owner']) || allowedTo('profile_identity_any') || allowedTo('manage_membergroups'))
			$_REQUEST['sa'] = 'account';
		elseif ((allowedTo('profile_extra_own') && $context['user']['is_owner']) || allowedTo('profile_extra_any'))
			$_REQUEST['sa'] = 'forumProfile';
		elseif ((allowedTo('profile_remove_own') && $context['user']['is_owner']) || allowedTo('profile_remove_any'))
			$_REQUEST['sa'] = 'deleteAccount';
		else
			isAllowedTo('profile_view_' . ($context['user']['is_owner'] ? 'own' : 'any'));
	}

	// Check the permissions for the given sub action.
	isAllowedTo($sa_allowed[$_REQUEST['sa']][$context['user']['is_owner'] ? 0 : 1]);

	// Make sure the user is who he claims to be, before any important account stuff is changed.
	if (!empty($sa_allowed[$_REQUEST['sa']][2]))
		validateSession();

	// No need for this anymore.
	unset($sa_allowed);

	$context['profile_areas'] = array();

	// Set the menu items in the left bar...
	if (!$user_info['is_guest'] && (($context['user']['is_owner'] && allowedTo('profile_view_own')) || allowedTo(array('profile_view_any', 'moderate_forum', 'manage_permissions'))))
	{
		$context['profile_areas']['info'] = array(
			'title' => $txt['profileInfo'],
			'areas' => array()
		);

		if (($context['user']['is_owner'] && allowedTo('profile_view_own')) || allowedTo('profile_view_any'))
		{
			$context['profile_areas']['info']['areas']['summary'] = '<a href="' . $scripturl . '?action=profile;u=' . $memID . ';sa=summary">' . $txt['summary'] . '</a>';
			$context['profile_areas']['info']['areas']['statPanel']	= '<a href="' . $scripturl . '?action=profile;u=' . $memID . ';sa=statPanel">' . $txt['statPanel'] . '</a>';
			$context['profile_areas']['info']['areas']['showPosts']	= '<a href="' . $scripturl . '?action=profile;u=' . $memID . ';sa=showPosts">' . $txt['showPosts'] . '</a>';
		}

		// Groups with moderator permissions can also....
		if (allowedTo('moderate_forum'))
		{
			$context['profile_areas']['info']['areas']['trackUser'] = '<a href="' . $scripturl . '?action=profile;u=' . $memID . ';sa=trackUser">' . $txt['trackUser'] . '</a>';
			$context['profile_areas']['info']['areas']['trackIP'] = '<a href="' . $scripturl . '?action=profile;u=' . $memID . ';sa=trackIP">' . $txt['trackIP'] . '</a>';
		}
		if (allowedTo('manage_permissions'))
			$context['profile_areas']['info']['areas']['showPermissions'] = '<a href="' . $scripturl . '?action=profile;u=' . $memID . ';sa=showPermissions">' . $txt['showPermissions'] . '</a>';
	}

	// Edit your/this person's profile?
	if (($context['user']['is_owner'] && (allowedTo(array('profile_identity_own', 'profile_extra_own')))) || allowedTo(array('profile_identity_any', 'profile_extra_any', 'manage_membergroups')))
	{
		$context['profile_areas']['edit_profile'] = array(
			'title' => $txt['profileEdit'],
			'areas' => array()
		);

		if (($context['user']['is_owner'] && allowedTo('profile_identity_own')) || allowedTo(array('profile_identity_any', 'manage_membergroups')))
			$context['profile_areas']['edit_profile']['areas']['account'] = '<a href="' . $scripturl . '?action=profile;u=' . $memID . ';sa=account">' . $txt['account'] . '</a>';

		if (($context['user']['is_owner'] && allowedTo('profile_extra_own')) || allowedTo('profile_extra_any'))
		{
			$context['profile_areas']['edit_profile']['areas']['forumProfile'] = '<a href="' . $scripturl . '?action=profile;u=' . $memID . ';sa=forumProfile">' . $txt['forumProfile'] . '</a>';
			$context['profile_areas']['edit_profile']['areas']['theme'] = '<a href="' . $scripturl . '?action=profile;u=' . $memID . ';sa=theme">' . $txt['theme'] . '</a>';
			$context['profile_areas']['edit_profile']['areas']['notification'] = '<a href="' . $scripturl . '?action=profile;u=' . $memID . ';sa=notification">' . $txt['notification'] . '</a>';
			if (!empty($modSettings['allow_ignore_boards']))
				$context['profile_areas']['edit_profile']['areas']['ignoreboards'] = '<a href="' . $scripturl . '?action=profile;u=' . $memID . ';sa=ignoreboards">' . $txt['ignoreboards'] . '</a>';
		}

		// !!! I still don't think this warrants a new section by any means, but it's definitely not part of viewing a person's profile, if only the owner can do it.
		if (!empty($modSettings['enable_buddylist']) && $context['user']['is_owner'] && allowedTo(array('profile_extra_own', 'profile_extra_any')))
			$context['profile_areas']['edit_profile']['areas']['editBuddies'] = '<a href="' . $scripturl . '?action=profile;u=' . $memID . ';sa=editBuddies">' . $txt['editBuddies'] . '</a>';
		if (!empty($modSettings['show_group_membership']) && $context['user']['is_owner'] && allowedTo(array('profile_extra_own', 'profile_extra_any')))
			$context['profile_areas']['edit_profile']['areas']['groupMembership'] = '<a href="' . $scripturl . '?action=profile;u=' . $memID . ';sa=groupMembership">' . $txt['groupMembership'] . '</a>';
	}

	// If you have permission to do something with this profile, you'll see one or more actions.
	if (($context['user']['is_owner'] && allowedTo('profile_remove_own')) || allowedTo('profile_remove_any') || (!$context['user']['is_owner'] && allowedTo('pm_send')))
	{
		// Initialize the action menu group...
		$context['profile_areas']['profile_action'] = array(
			'title' => $txt['profileAction'],
			'areas' => array()
		);

		// You shouldn't PM (or ban really..) yourself!! (only administrators see this because it's not in the menu.)
		if (!$context['user']['is_owner'] && allowedTo('pm_send'))
			$context['profile_areas']['profile_action']['areas']['send_pm'] = '<a href="' . $scripturl . '?action=pm;sa=send;u=' . $memID . '">' . $txt['profileSendIm'] . '</a>';
		// We don't wanna ban admins, do we?
		if (allowedTo('manage_bans') && $user_profile[$memID]['id_group'] != 1 && !in_array(1, explode(',', $user_profile[$memID]['additional_groups'])))
			$context['profile_areas']['profile_action']['areas']['banUser'] = '<a href="' . $scripturl . '?action=admin;area=ban;sa=add;u=' . $memID . '">' . $txt['profileBanUser'] . '</a>';

		// You may remove your own account 'cuz it's yours or you're an admin.
		if (($context['user']['is_owner'] && allowedTo('profile_remove_own')) || allowedTo('profile_remove_any'))
			$context['profile_areas']['profile_action']['areas']['deleteAccount'] = '<a href="' . $scripturl . '?action=profile;u=' . $memID . ';sa=deleteAccount">' . $txt['deleteAccount'] . '</a>';
	}

	// This is here so the menu won't be shown unless it's actually needed.
	if (!isset($context['profile_areas']['info']['areas']['trackUser']) && !isset($context['profile_areas']['info']['areas']['showPermissions']) && !isset($context['profile_areas']['edit_profile']) && !isset($context['profile_areas']['profile_action']['areas']['banUser']) && !isset($context['profile_areas']['profile_action']['areas']['deleteAccount']))
		$context['profile_areas'] = array();

	// Set the selected items.
	$context['menu_item_selected'] = $_REQUEST['sa'];
	$context['sub_template'] = $_REQUEST['sa'];

	// All the subactions that require a user password in order to validate.
	$context['require_password'] = in_array($context['menu_item_selected'], array('account'));

	$context['member'] = array(
		'id' => $memID,
		'username' => $user_profile[$memID]['member_name'],
		'name' => !isset($user_profile[$memID]['real_name']) || $user_profile[$memID]['real_name'] == '' ? '' : $user_profile[$memID]['real_name'],
		'email' => $user_profile[$memID]['email_address'],
		'posts' => empty($user_profile[$memID]['posts']) ? 0: (int) $user_profile[$memID]['posts'],
		'hide_email' => empty($user_profile[$memID]['hide_email']) ? 0 : $user_profile[$memID]['hide_email'],
		'show_online' => empty($user_profile[$memID]['show_online']) ? 0 : $user_profile[$memID]['show_online'],
		'registered' => empty($user_profile[$memID]['date_registered']) ? $txt['not_applicable'] : strftime('%Y-%m-%d', $user_profile[$memID]['date_registered'] + ($user_info['time_offset'] + $modSettings['time_offset']) * 3600),
		'group' => $user_profile[$memID]['id_group'],
		'gender' => array('name' => empty($user_profile[$memID]['gender']) ? '' : ($user_profile[$memID]['gender'] == 2 ? 'f' : 'm')),
		'karma' => array(
			'good' => empty($user_profile[$memID]['karma_good']) ? '0' : $user_profile[$memID]['karma_good'],
			'bad' => empty($user_profile[$memID]['karma_bad']) ? '0' : $user_profile[$memID]['karma_bad'],
		),
		'avatar' => array(
			'name' => &$user_profile[$memID]['avatar'],
			'href' => empty($user_profile[$memID]['id_attach']) ? '' : (empty($user_profile[$memID]['attachment_type']) ? $scripturl . '?action=dlattach;attach=' . $user_profile[$memID]['id_attach'] . ';type=avatar' : $modSettings['custom_avatar_url'] . '/' . $user_profile[$memID]['filename']),
			'custom' => stristr($user_profile[$memID]['avatar'], 'http://') ? $user_profile[$memID]['avatar'] : 'http://',
			'selection' => $user_profile[$memID]['avatar'] == '' || stristr($user_profile[$memID]['avatar'], 'http://') ? '' : $user_profile[$memID]['avatar'],
			'id_attach' => &$user_profile[$memID]['id_attach'],
			'filename' => &$user_profile[$memID]['filename'],
			'allow_server_stored' => allowedTo('profile_server_avatar') || !$context['user']['is_owner'],
			'allow_upload' => allowedTo('profile_upload_avatar') || !$context['user']['is_owner'],
			'allow_external' => allowedTo('profile_remote_avatar') || !$context['user']['is_owner'],
		),
		'icq' => array('name' => !isset($user_profile[$memID]['icq']) ? '' : $user_profile[$memID]['icq']),
		'aim' => array('name' => empty($user_profile[$memID]['aim']) ? '' : str_replace('+', ' ', $user_profile[$memID]['aim'])),
		'yim' => array('name' => empty($user_profile[$memID]['yim']) ? '' : $user_profile[$memID]['yim']),
		'msn' => array('name' => empty($user_profile[$memID]['msn']) ? '' : $user_profile[$memID]['msn']),
		'website' => array(
			'title' => !isset($user_profile[$memID]['website_title']) ? '' : $user_profile[$memID]['website_title'],
			'url' => !isset($user_profile[$memID]['website_url']) ? '' : $user_profile[$memID]['website_url'],
		),
	);

	// Call the appropriate subaction function.
	$_REQUEST['sa']($memID);

	if (!empty($post_errors))
	{
		// Set all the errors so the template knows what went wrong.
		foreach ($post_errors as $error_type)
			$context['modify_error'][$error_type] = true;
		rememberPostData();
	}

	// Set the page title if it's not already set...
	if (!isset($context['page_title']))
		$context['page_title'] = $txt['profile'] . ' - ' . $txt[$_REQUEST['sa']];

	if (isset($_REQUEST['updated']) && empty($post_errors))
		$context['profile_updated'] = $context['user']['is_owner'] ? $txt['profile_updated_own'] : sprintf($txt['profile_updated_else'], $context['member']['name']);
}

// Execute the modifications!
function ModifyProfile2()
{
	global $txt, $modSettings;
	global $cookiename, $context;
	global $sourcedir, $scripturl, $db_prefix, $user_info;
	global $context, $newpassemail, $user_profile, $validationCode, $smfFunc;

	loadLanguage('Profile');

	/* Set allowed sub-actions.

	 The format of $sa_allowed is as follows:

	$sa_allowed = array(
		'sub-action' => array(permission_array_for_editing_OWN_profile, permission_array_for_editing_ANY_profile, session_validation_method[, require_password]),
		...
	);

	*/

	$sa_allowed = array(
		'account' => array(array('manage_membergroups', 'profile_identity_any', 'profile_identity_own'), array('manage_membergroups', 'profile_identity_any'), 'post', true),
		'forumProfile' => array(array('profile_extra_any', 'profile_extra_own'), array('profile_extra_any'), 'post'),
		'theme' => array(array('profile_extra_any', 'profile_extra_own'), array('profile_extra_any'), 'post'),
		'notification' => array(array('profile_extra_any', 'profile_extra_own'), array('profile_extra_any'), 'post'),
		'groupMembership' => array(array('profile_view_own'), array('manage_membergroups'), ''),
		'deleteAccount' => array(array('profile_remove_any', 'profile_remove_own'), array('profile_remove_any'), 'post', true),
		'activateAccount' => array(array(), array('moderate_forum'), 'get'),
		'ignoreboards' => array(array('profile_extra_any', 'profile_extra_own'), array('profile_extra_any'), 'post'),
	);

	// Is the current sub-action allowed?
	if (empty($_REQUEST['sa']) || !isset($sa_allowed[$_REQUEST['sa']]))
		fatal_lang_error('not_a_user', false);

	checkSession($sa_allowed[$_REQUEST['sa']][2]);

	// Start with no updates and no errors.
	$profile_vars = array();
	$post_errors = array();

	// Normally, don't send an email.
	$newpassemail = false;

	// Clean up the POST variables.
	$_POST = htmltrim__recursive($_POST);
	$_POST = unescapestring__recursive($_POST);
	$_POST = htmlspecialchars__recursive($_POST);
	$_POST = escapestring__recursive($_POST);

	// Search for the member being edited and put the information in $user_profile.
	$memberResult = loadMemberData((int) $_REQUEST['userID'], false, 'profile');

	if (!is_array($memberResult))
		fatal_lang_error('not_a_user', false);

	list ($memID) = $memberResult;

	// Are you modifying your own, or someone else's?
	if ($user_info['id'] == $memID)
		$context['user']['is_owner'] = true;
	else
	{
		$context['user']['is_owner'] = false;
		validateSession();
	}

	// Check profile editing permissions.
	isAllowedTo($sa_allowed[$_REQUEST['sa']][$context['user']['is_owner'] ? 0 : 1]);

	// If this is yours, check the password.
	if ($context['user']['is_owner'] && !empty($sa_allowed[$_REQUEST['sa']][3]))
	{
		// You didn't even enter a password!
		if (trim($_POST['oldpasswrd']) == '')
			$post_errors[] = 'no_password';

		// Since the password got modified due to all the $_POST cleaning, lets undo it so we can get the correct password
		$_POST['oldpasswrd'] = $smfFunc['db_escape_string'](un_htmlspecialchars($smfFunc['db_unescape_string']($_POST['oldpasswrd'])));

		// Does the integration want to check passwords?
		$good_password = false;
		if (isset($modSettings['integrate_verify_password']) && function_exists($modSettings['integrate_verify_password']))
			if (call_user_func($modSettings['integrate_verify_password'], $user_profile[$memID]['member_name'], $_POST['oldpasswrd'], false) === true)
				$good_password = true;

		// Bad password!!!
		if (!$good_password && $user_info['passwd'] != sha1(strtolower($user_profile[$memID]['member_name']) . $_POST['oldpasswrd']))
			$post_errors[] = 'bad_password';
	}

	// No need for the sub action array.
	unset($sa_allowed);

	// If the user is an admin - see if they are resetting someones username.
	if ($user_info['is_admin'] && isset($_POST['member_name']))
	{
		// We'll need this...
		require_once($sourcedir . '/Subs-Auth.php');

		// Do the reset... this will send them an email too.
		resetPassword($memID, $_POST['member_name']);
	}

	// Change the IP address in the database.
	if ($context['user']['is_owner'])
		$profile_vars['member_ip'] = "'$user_info[ip]'";

	if (isset($_POST['sa']) && $_POST['sa'] == 'ignoreboards' && empty($_POST['ignore_brd']))
		$_POST['ignore_brd'] = array();	

	// Now call the sub-action function...
	if (isset($_POST['sa']) && $_POST['sa'] == 'deleteAccount')
	{
		deleteAccount2($profile_vars, $post_errors, $memID);

		if (empty($post_errors))
			redirectexit();
	}
	elseif (isset($_GET['sa']) && $_GET['sa'] == 'groupMembership')
	{
		$msg = groupMembership2($profile_vars, $post_errors, $memID);

		// Whatever we've done, we have nothing else to do here...
		redirectexit('action=profile;u=' . $memID . ';sa=groupMembership' . (!empty($msg) ? ';msg=' . $msg : ''));
	}
	else
		saveProfileChanges($profile_vars, $post_errors, $memID);

	// There was a problem, let them try to re-enter.
	if (!empty($post_errors))
	{
		// Load the language file so we can give a nice explanation of the errors.
		loadLanguage('Errors');
		$context['post_errors'] = $post_errors;

		$_REQUEST['sa'] = $_POST['sa'];
		$_REQUEST['u'] = $memID;
		return ModifyProfile($post_errors);
	}

	if (!empty($profile_vars))
	{
		// If we've changed the password, notify any integration that may be listening in.
		if (isset($profile_vars['passwd']) && isset($modSettings['integrate_reset_pass']) && function_exists($modSettings['integrate_reset_pass']))
			call_user_func($modSettings['integrate_reset_pass'], $user_profile[$memID]['member_name'], $user_profile[$memID]['member_name'], $_POST['passwrd1']);

		updateMemberData($memID, $profile_vars);
	}

	// What if this is the newest member?
	if ($modSettings['latestMember'] == $memID)
		updateStats('member');
	elseif (isset($profile_vars['real_name']))
		updateSettings(array('memberlist_updated' => time()));

	// If the member changed his/her birthdate, update calendar statistics.
	if (isset($profile_vars['birthdate']) || isset($profile_vars['real_name']))
		updateStats('calendar');

	// Send an email?
	if ($newpassemail)
	{
		require_once($sourcedir . '/Subs-Post.php');

		// Send off the email.
		sendmail($_POST['email_address'], $txt['activate_reactivate_title'] . ' ' . $context['forum_name'],
			"$txt[activate_reactivate_mail]\n\n" .
			"$scripturl?action=activate;u=$memID;code=$validationCode\n\n" .
			"$txt[activate_code]: $validationCode\n\n" .
			sprintf($txt['regards_team'], $context['forum_name']));

		// Log the user out.
		$smfFunc['db_query']('', "
			DELETE FROM {$db_prefix}log_online
			WHERE id_member = $memID", __FILE__, __LINE__);
		$_SESSION['log_time'] = 0;
		$_SESSION['login_' . $cookiename] = serialize(array(0, '', 0));

		if (isset($_COOKIE[$cookiename]))
			$_COOKIE[$cookiename] = '';

		loadUserSettings();

		$context['user']['is_logged'] = false;
		$context['user']['is_guest'] = true;

		// Send them to the done-with-registration-login screen.
		loadTemplate('Register');
		$context += array(
			'page_title' => &$txt['profile'],
			'sub_template' => 'after',
			'description' => &$txt['activate_changed_email']
		);
		return;
	}
	elseif ($context['user']['is_owner'])
	{
		// Log them back in.
		if (isset($_POST['passwrd1']) && $_POST['passwrd1'] != '')
		{
			require_once($sourcedir . '/Subs-Auth.php');
			setLoginCookie(60 * $modSettings['cookieTime'], $memID, sha1(sha1(strtolower($user_profile[$memID]['member_name']) . un_htmlspecialchars($smfFunc['db_unescape_string']($_POST['passwrd1']))) . $user_profile[$memID]['password_salt']));
		}

		loadUserSettings();
		writeLog();
	}

	// Back to same subaction page..
	redirectexit('action=profile;u=' . $memID . ';sa=' . $_REQUEST['sa'] . ';updated', (isset($_POST['passwrd1']) && $context['server']['needs_login_fix']) || ($context['browser']['is_ie'] && isset($_FILES['attachment'])));
}

// Save the profile changes....
function saveProfileChanges(&$profile_vars, &$post_errors, $memID)
{
	global $db_prefix, $user_info, $txt, $modSettings, $user_profile;
	global $newpassemail, $validationCode, $context, $settings, $sourcedir;
	global $smfFunc;

	// These make life easier....
	$old_profile = &$user_profile[$memID];

	// Permissions...
	if ($context['user']['is_owner'])
	{
		$changeIdentity = allowedTo(array('profile_identity_any', 'profile_identity_own'));
		$changeOther = allowedTo(array('profile_extra_any', 'profile_extra_own'));
	}
	else
	{
		$changeIdentity = allowedTo('profile_identity_any');
		$changeOther = allowedTo('profile_extra_any');
	}

	// Arrays of all the changes - makes things easier.
	$profile_bools = array(
		'notify_announcements', 'notify_send_body',
	);
	$profile_ints = array(
		'notify_regularity',
		'notify_types',
		'icq',
		'gender',
		'id_theme',
	);
	$profile_floats = array(
		'time_offset',
	);
	$profile_strings = array(
		'website_url', 'website_title',
		'aim', 'yim',
		'location', 'birthdate',
		'time_format',
		'buddy_list',
		'smiley_set',
		'signature', 'personal_text', 'avatar',
		'ignore_boards',
	);

	// Fix the spaces in messenger screennames...
	$fix_spaces = array('msn', 'aim', 'yim');
	foreach ($fix_spaces as $var)
	{
		// !!! Why?
		if (isset($_POST[$var]))
			$_POST[$var] = strtr($_POST[$var], ' ', '+');
	}

	// Make sure the msn one is an email address, not something like 'none' :P.
	if (isset($_POST['msn']) && ($_POST['msn'] == '' || preg_match('~^[0-9A-Za-z=_+\-/][0-9A-Za-z=_\'+\-/\.]*@[\w\-]+(\.[\w\-]+)*(\.[\w]{2,6})$~', $_POST['msn']) != 0))
		$profile_strings[] = 'msn';

	// Validate the title...
	if (!empty($modSettings['titlesEnable']) && (allowedTo('profile_title_any') || (allowedTo('profile_title_own') && $context['user']['is_owner'])))
		$profile_strings[] = 'usertitle';

	// Validate the time_offset...
	if (isset($_POST['time_offset']))
	{
		$_POST['time_offset'] = strtr($_POST['time_offset'], ',', '.');

		if ($_POST['time_offset'] < -23.5 || $_POST['time_offset'] > 23.5)
			$post_errors[] = 'bad_offset';
	}

	// Fix the URL...
	if (isset($_POST['website_url']))
	{
		if (strlen(trim($_POST['website_url'])) > 0 && strpos($_POST['website_url'], '://') === false)
			$_POST['website_url'] = 'http://' . $_POST['website_url'];
		if (strlen($_POST['website_url']) < 8)
			$_POST['website_url'] = '';
	}

	// !!! Should we check for this year and tell them they made a mistake :P? (based on coppa at least?)
	if (isset($_POST['birthdate']))
	{
		if (preg_match('/(\d{4})[\-\., ](\d{2})[\-\., ](\d{2})/', $_POST['birthdate'], $dates) === 1)
			$_POST['birthdate'] = checkdate($dates[2], $dates[3], $dates[1] < 4 ? 4 : $dates[1]) ? sprintf('%04d-%02d-%02d', $dates[1] < 4 ? 4 : $dates[1], $dates[2], $dates[3]) : '0001-01-01';
		else
			unset($_POST['birthdate']);
	}
	elseif (isset($_POST['bday1'], $_POST['bday2']) && $_POST['bday1'] > 0 && $_POST['bday2'] > 0)
		$_POST['birthdate'] = checkdate($_POST['bday1'], $_POST['bday2'], $_POST['bday3'] < 4 ? 4 : $_POST['bday3']) ? sprintf('%04d-%02d-%02d', $_POST['bday3'] < 4 ? 4 : $_POST['bday3'], $_POST['bday1'], $_POST['bday2']) : '0001-01-01';
	elseif (isset($_POST['bday1']) || isset($_POST['bday2']) || isset($_POST['bday3']))
		$_POST['birthdate'] = '0001-01-01';

	if (isset($_POST['ignore_brd']))
	{
		if (!is_array($_POST['ignore_brd']))
			$_POST['ignore_brd'] = array ( $_POST['ignore_brd'] );

		foreach($_POST['ignore_brd'] AS $k => $d )
		{
			$d = (int) $d;
			if ($d != 0)
				$_POST['ignore_brd'][$k] = $d;
			else
				unset($_POST['ignore_brd'][$k]);
		}
		$_POST['ignore_boards'] = implode(',', $_POST['ignore_brd']);
		unset($_POST['ignore_brd']);
		
	}

	// Validate the smiley set.
	if (isset($_POST['smiley_set']))
	{
		$smiley_sets = explode(',', $modSettings['smiley_sets_known']);
		if (!in_array($_POST['smiley_set'], $smiley_sets) && $_POST['smiley_set'] != 'none')
			$_POST['smiley_set'] = '';
	}

	// Make sure the signature isn't invalid.
	if (isset($_POST['signature']))
	{
		require_once($sourcedir . '/Subs-Post.php');

		if (!allowedTo('admin_forum'))
		{
			// Load all the signature limits.
			list ($sig_limits, $sig_bbc) = explode(':', $modSettings['signature_settings']);
			$sig_limits = explode(',', $sig_limits);
			$disabledTags = !empty($sig_bbc) ? explode(',', $sig_bbc) : array();
	
			$unparsed_signature = strtr(un_htmlspecialchars($_POST['signature']), array("\r" => '', '&#039' => '\''));
			// Too long?
			if (!empty($sig_limits[1]) && $smfFunc['strlen']($unparsed_signature) > $sig_limits[1])
			{
				$_POST['signature'] = trim($smfFunc['db_escape_string'](htmlspecialchars($smfFunc['db_unescape_string']($smfFunc['substr']($unparsed_signature, 0, $sig_limits[1])), ENT_QUOTES)));
				$txt['profile_error_signature_max_length'] = sprintf($txt['profile_error_signature_max_length'], $sig_limits[1]);
				$post_errors[] = 'signature_max_length';
			}
			// Too many lines?
			if (!empty($sig_limits[2]) && substr_count($unparsed_signature, "\n") > $sig_limits[2])
			{
				$txt['profile_error_signature_max_lines'] = sprintf($txt['profile_error_signature_max_lines'], $sig_limits[2]);
				$post_errors[] = 'signature_max_lines';
			}
			// Too many images?!
			if (!empty($sig_limits[3]) && substr_count(strtolower($unparsed_signature), '[img') > $sig_limits[3])
			{
				$txt['profile_error_signature_max_image_count'] = sprintf($txt['profile_error_signature_max_image_count'], $sig_limits[3]);
				$post_errors[] = 'signature_max_image_count';
			}
			// What about too many smileys!
			$smiley_parsed = $unparsed_signature;
			parsesmileys($smiley_parsed);
			if (!empty($sig_limits[4]) && (substr_count(strtolower($smiley_parsed), "<img") - substr_count(strtolower($unparsed_signature), "<img")) > $sig_limits[4])
			{
				$txt['profile_error_signature_max_smileys'] = sprintf($txt['profile_error_signature_max_smileys'], $sig_limits[4]);
				$post_errors[] = 'signature_max_smileys';
			}
			// Maybe we are abusing font sizes?
			if (!empty($sig_limits[7]) && preg_match_all('~\[size=(\d+)~i', $unparsed_signature, $matches) !== false && isset($matches[1]))
			{
				foreach ($matches[1] as $size)
					if ($size > $sig_limits[7])
					{
						$txt['profile_error_signature_max_font_size'] = sprintf($txt['profile_error_signature_max_font_size'], $sig_limits[7]);
						$post_errors[] = 'signature_max_font_size';
						break;
					}
			}
			// The difficult one - image sizes! Don't error on this - just fix it.
			if ((!empty($sig_limits[5]) || !empty($sig_limits[6])))
			{
				$replaces = array();
				// Try to find all the images!
				if (preg_match_all('~\[img(\s+width=([\d]+))?(\s+height=([\d]+))?(\s+width=([\d]+))?\s*\](?:<br />)*([^<">]+?)(?:<br />)*\[/img\]~i', $unparsed_signature, $matches) !== false)
				{
					foreach ($matches[0] as $key => $image)
					{
						$width = -1; $height = -1;
	
						// Does it have predefined restraints? Width first.
						if ($matches[6][$key])
							$matches[2][$key] = $matches[6][$key];
						if ($matches[2][$key] && $sig_limits[5] && $matches[2][$key] > $sig_limits[5])
						{
							$width = $sig_limits[5];
							$matches[4][$key] = $matches[4][$key] * ($width / $matches[2][$key]);
						}
						elseif ($matches[2][$key])
							$width = $matches[2][$key];
						// ... and height.
						if ($matches[4][$key] && $sig_limits[6] && $matches[4][$key] > $sig_limits[6])
						{
							$height = $sig_limits[6];
							if ($width != -1)
								$width = $width * ($height / $matches[4][$key]);
						}
						elseif ($matches[4][$key])
							$height = $matches[4][$key];

						// If the dimensions are still not fixed - we need to check the actual image.
						if (($width == -1 && $sig_limits[5]) || ($height == -1 && $sig_limits[6]))
						{
							$sizes = url_image_size($matches[7][$key]);
							if (is_array($sizes))
							{
								// Too wide?
								if ($sizes[0] > $sig_limits[5] && $sig_limits[5])
								{
									$width = $sig_limits[5];
									$sizes[1] = $sizes[1] * ($width / $sizes[0]);
								}
								// Too high?
								if ($sizes[1] > $sig_limits[6] && $sig_limits[6])
								{
									$height = $sig_limits[6];
									if ($width == -1)
										$width = $sizes[0];
									$width = $width * ($height / $sizes[1]);
								}
								elseif ($width != -1)
									$height = $sizes[1];
							}
						}
	
						// Did we come up with some changes? If so remake the string.
						if ($width != -1 || $height != -1)
							$replaces[$image] = '[img' . ($width != -1 ? ' width=' . round($width) : '') . ($height != -1 ? ' height=' . round($height) : '') . ']' . $matches[7][$key] . '[/img]';
					}
					if (!empty($replaces))
						$_POST['signature'] = str_replace(array_keys($replaces), array_values($replaces), $unparsed_signature);
				}
			}
			// Any disabled BBC?
			$disabledSigBBC = implode('|', $disabledTags);
			if (!empty($disabledSigBBC))
			{
				if (preg_match('~\[(' . $disabledSigBBC . ')~', $unparsed_signature, $matches) !== false && isset($matches[1]))
				{
					$txt['profile_error_signature_disabled_bbc'] = sprintf($txt['profile_error_signature_disabled_bbc'], implode(', ', $disabledTags));
					$post_errors[] = 'signature_disabled_bbc';
				}
			}
		}
		if (empty($post_errors))
			preparsecode($_POST['signature']);
	}

	// Identity-only changes...
	if ($changeIdentity)
	{
		// This block is only concerned with display name validation.
		if (isset($_POST['real_name']) && (!empty($modSettings['allow_editDisplayName']) || allowedTo('moderate_forum')) && trim($_POST['real_name']) != $old_profile['real_name'])
		{
			$_POST['real_name'] = trim(preg_replace('~[\s]~' . ($context['utf8'] ? 'u' : ''), ' ', $_POST['real_name']));
			if (trim($_POST['real_name']) == '')
				$post_errors[] = 'no_name';
			else
			{
				require_once($sourcedir . '/Subs-Members.php');
				if (isReservedName($_POST['real_name'], $memID))
					$post_errors[] = 'name_taken';
			}

			if (isset($_POST['real_name']))
				$profile_vars['real_name'] = '\'' . $_POST['real_name'] . '\'';
		}

		// Change the registration date.
		if (!empty($_POST['date_registered']) && allowedTo('moderate_forum'))
		{
			// Bad date!  Go try again - please?
			if (($_POST['date_registered'] = strtotime($_POST['date_registered'])) === -1)
				fatal_error($txt['invalid_registration'] . ' ' . strftime('%d %b %Y ' . (strpos($user_info['time_format'], '%H') !== false ? '%I:%M:%S %p' : '%H:%M:%S'), forum_time(false)), false);
			// As long as it doesn't equal 'N/A'...
			elseif ($_POST['date_registered'] != $txt['not_applicable'] && $_POST['date_registered'] != strtotime(strftime('%Y-%m-%d', $user_profile[$memID]['date_registered'] + ($user_info['time_offset'] + $modSettings['time_offset']) * 3600)))
				$profile_vars['date_registered'] = $_POST['date_registered'] - ($user_info['time_offset'] + $modSettings['time_offset']) * 3600;
		}

		// Change the number of posts.
		if (isset($_POST['posts']) && allowedTo('moderate_forum'))
			$profile_vars['posts'] = $_POST['posts'] != '' ? (int) strtr($_POST['posts'], array(',' => '', '.' => '', ' ' => '')) : '\'\'';

		// This block is only concerned with email address validation..
		if (isset($_POST['email_address']) && strtolower($_POST['email_address']) != strtolower($old_profile['email_address']))
		{
			$_POST['email_address'] = strtr($_POST['email_address'], array('&#039;' => '\\\''));

			// Prepare the new password, or check if they want to change their own.
			if (!empty($modSettings['send_validation_onChange']) && !allowedTo('moderate_forum'))
			{
				$validationCode = substr(preg_replace('/\W/', '', md5(rand())), 0, 10);
				$profile_vars['validation_code'] = '\'' . $validationCode . '\'';
				$profile_vars['is_activated'] = '2';
				$newpassemail = true;
			}

			// Check the name and email for validity.
			if (trim($_POST['email_address']) == '')
				$post_errors[] = 'no_email';
			if (preg_match('~^[0-9A-Za-z=_+\-/][0-9A-Za-z=_\'+\-/\.]*@[\w\-]+(\.[\w\-]+)*(\.[\w]{2,6})$~', $smfFunc['db_unescape_string']($_POST['email_address'])) == 0)
				$post_errors[] = 'bad_email';

			// Email addresses should be and stay unique.
			$request = $smfFunc['db_query']('', "
				SELECT id_member
				FROM {$db_prefix}members
				WHERE id_member != $memID
					AND email_address = '$_POST[email_address]'
				LIMIT 1", __FILE__, __LINE__);
			if ($smfFunc['db_num_rows']($request) > 0)
				$post_errors[] = 'email_taken';
			$smfFunc['db_free_result']($request);

			$profile_vars['email_address'] = '\'' . $_POST['email_address'] . '\'';
		}

		// Hide email address?
		if (isset($_POST['hide_email']) && (!empty($modSettings['allow_hide_email']) || allowedTo('moderate_forum')))
			$profile_vars['hide_email'] = empty($_POST['hide_email']) ? '0' : '1';

		// Are they allowed to change their hide status?
		if (isset($_POST['show_online']) && (!empty($modSettings['allow_hideOnline']) || allowedTo('moderate_forum')))
			$profile_vars['show_online'] = empty($_POST['show_online']) ? '0' : '1';

		// If they're trying to change the password, let's check they pick a sensible one.
		if (isset($_POST['passwrd1']) && $_POST['passwrd1'] != '')
		{
			// Do the two entries for the password even match?
			if ($_POST['passwrd1'] != $_POST['passwrd2'])
				$post_errors[] = 'bad_new_password';

			// Let's get the validation function into play...
			require_once($sourcedir . '/Subs-Auth.php');
			$passwordErrors = validatePassword($_POST['passwrd1'], $user_info['username'], array($user_info['name'], $user_info['email']));

			// Were there errors?
			if ($passwordErrors != null)
				$post_errors[] = 'password_' . $passwordErrors;

			// Set up the new password variable... ready for storage.
			$profile_vars['passwd'] = '\'' . sha1(strtolower($old_profile['member_name']) . un_htmlspecialchars($smfFunc['db_unescape_string']($_POST['passwrd1']))) . '\'';
		}

		if (isset($_POST['secret_question']))
			$profile_vars['secret_question'] = '\'' . $_POST['secret_question'] . '\'';

		// Do you have a *secret* password?
		if (isset($_POST['secret_answer']) && $_POST['secret_answer'] != '')
			$profile_vars['secret_answer'] = '\'' . md5($_POST['secret_answer']) . '\'';
	}

	// Things they can do if they are a forum moderator.
	if (allowedTo('moderate_forum'))
	{
		if (($_REQUEST['sa'] == 'activateAccount' || !empty($_POST['is_activated'])) && isset($old_profile['is_activated']) && $old_profile['is_activated'] != 1)
		{
			// If we are approving the deletion of an account, we do something special ;)
			if ($old_profile['is_activated'] == 4)
			{
				require_once($sourcedir . '/Subs-Members.php');
				deleteMembers($memID);
				redirectexit();
			}

			if (isset($modSettings['integrate_activate']) && function_exists($modSettings['integrate_activate']))
				call_user_func($modSettings['integrate_activate'], $old_profile['member_name']);

			// Actually update this member now, as it guarantees the unapproved count can't get corrupted.
			updateMemberData($memID, array('is_activated' => $old_profile['is_activated'] >= 10 ? '11' : '1', 'validation_code' => '\'\''));

			// If we are doing approval, update the stats for the member just incase.
			if (in_array($old_profile['is_activated'], array(3, 4, 13, 14)))
				updateSettings(array('unapprovedMembers' => ($modSettings['unapprovedMembers'] > 1 ? $modSettings['unapprovedMembers'] - 1 : 0)));

			// Make sure we update the stats too.
			updateStats('member', false);
		}

		if (isset($_POST['karma_good']))
			$profile_vars['karma_good'] = $_POST['karma_good'] != '' ? (int) $_POST['karma_good'] : '\'\'';
		if (isset($_POST['karma_bad']))
			$profile_vars['karma_bad'] = $_POST['karma_bad'] != '' ? (int) $_POST['karma_bad'] : '\'\'';
	}

	// Assigning membergroups (you need admin_forum permissions to change an admins' membergroups).
	if (allowedTo('manage_membergroups'))
	{
		// The account page allows the change of your id_group - but not to admin!.
		if (isset($_POST['id_group']) && (allowedTo('admin_forum') || ((int) $_POST['id_group'] != 1 && $old_profile['id_group'] != 1)))
			$profile_vars['id_group'] = (int) $_POST['id_group'];

		// Find the additional membergroups (if any)
		if (isset($_POST['additional_groups']) && is_array($_POST['additional_groups']))
		{
			foreach ($_POST['additional_groups'] as $i => $group_id)
			{
				if ((int) $group_id == 0 || (!allowedTo('admin_forum') && (int) $group_id == 1))
					unset($_POST['additional_groups'][$i], $_POST['additional_groups'][$i]);
				else
					$_POST['additional_groups'][$i] = (int) $group_id;
			}

			// Put admin back in there if you don't have permission to take it away.
			if (!allowedTo('admin_forum') && in_array(1, explode(',', $old_profile['additional_groups'])))
				$_POST['additional_groups'][] = 1;

			$profile_vars['additional_groups'] = '\'' . implode(',', $_POST['additional_groups']) . '\'';
		}

		// Too often, people remove delete their own account, or something.
		if (in_array(1, explode(',', $old_profile['additional_groups'])) || $old_profile['id_group'] == 1)
		{
			$stillAdmin = !isset($profile_vars['id_group']) || $profile_vars['id_group'] == 1 || (isset($_POST['additional_groups']) && in_array(1, $_POST['additional_groups']));

			// If they would no longer be an admin, look for any other...
			if (!$stillAdmin)
			{
				$request = $smfFunc['db_query']('', "
					SELECT id_member
					FROM {$db_prefix}members
					WHERE (id_group = 1 OR FIND_IN_SET(1, additional_groups))
						AND id_member != $memID
					LIMIT 1", __FILE__, __LINE__);
				list ($another) = $smfFunc['db_fetch_row']($request);
				$smfFunc['db_free_result']($request);

				if (empty($another))
					fatal_lang_error('at_least_one_admin', 'critical');
			}
		}

		// If we are changing group status, update permission cache as necessary.
		if (isset($profile_vars['id_group']) || isset($profile_vars['additional_groups']))
		{
			if ($context['user']['is_owner'])
				$_SESSION['mc']['time'] = 0;
			else
				updateSettings(array('settings_updated' => time()));
		}
	}

	// Validate the language file...
	if (($changeIdentity || $changeOther) && isset($_POST['lngfile']) && !empty($modSettings['userLanguage']))
	{
		$language_directories = array(
			$settings['default_theme_dir'] . '/languages',
			$settings['actual_theme_dir'] . '/languages',
		);
		if (!empty($settings['base_theme_dir']))
			$language_directories[] = $settings['base_theme_dir'] . '/languages';
		$language_directories = array_unique($language_directories);

		foreach ($language_directories as $language_dir)
		{
			if (!file_exists($language_dir))
				continue;

			$dir = dir($language_dir);
			while ($entry = $dir->read())
				if (preg_match('~^index\.(.+)\.php$~', $entry, $matches) && $matches[1] == $_POST['lngfile'])
				{
					$profile_vars['lngfile'] = "'$_POST[lngfile]'";

					// If they are the owner, make this persist even after they log out.
					if ($context['user']['is_owner'])
						$_SESSION['language'] = $_POST['lngfile'];
				}
			$dir->close();
		}
	}

	// Here's where we sort out all the 'other' values...
	if ($changeOther)
	{
		makeThemeChanges($memID, isset($_POST['id_theme']) ? (int) $_POST['id_theme'] : $old_profile['id_theme']);
		makeAvatarChanges($memID, $post_errors);
		makeNotificationChanges($memID);
		if (!empty($_REQUEST['sa']))
			makeCustomFieldChanges($memID, $_REQUEST['sa']);

		foreach ($profile_bools as $var)
			if (isset($_POST[$var]))
				$profile_vars[$var] = empty($_POST[$var]) ? '0' : '1';
		foreach ($profile_ints as $var)
			if (isset($_POST[$var]))
				$profile_vars[$var] = $_POST[$var] != '' ? (int) $_POST[$var] : '\'\'';
		foreach ($profile_floats as $var)
			if (isset($_POST[$var]))
				$profile_vars[$var] = (float) $_POST[$var];
		foreach ($profile_strings as $var)
			if (isset($_POST[$var]))
				$profile_vars[$var] = '\'' . $_POST[$var] . '\'';
	}

	if (isset($profile_vars['icq']) && $profile_vars['icq'] == '0')
		$profile_vars['icq'] = '\'\'';
}

// Make any theme changes that are sent with the profile..
function makeThemeChanges($memID, $id_theme)
{
	global $db_prefix, $modSettings, $smfFunc;

	// These are the theme changes...
	$themeSetArray = array();
	if (isset($_POST['options']) && is_array($_POST['options']))
	{
		foreach ($_POST['options'] as $opt => $val)
			$themeSetArray[] = array($memID, $id_theme, "SUBSTRING('" . $smfFunc['db_escape_string']($opt) . "', 1, 255)", "SUBSTRING('" . (is_array($val) ? implode(',', $val) : $val) . "', 1, 65534)");
	}

	$erase_options = array();
	if (isset($_POST['default_options']) && is_array($_POST['default_options']))
		foreach ($_POST['default_options'] as $opt => $val)
		{
			$themeSetArray[] = array($memID, 1, "SUBSTRING('" . $smfFunc['db_escape_string']($opt) . "', 1, 255)", "SUBSTRING('" . (is_array($val) ? implode(',', $val) : $val) . "', 1, 65534)");
			$erase_options[] = $smfFunc['db_escape_string']($opt);
		}

	// If themeSetArray isn't still empty, send it to the database.
	if (!empty($themeSetArray))
	{
		$smfFunc['db_insert']('replace',
			"{$db_prefix}themes",
			array('id_member', 'id_theme', 'variable', 'value'),
			$themeSetArray,
			array('id_member', 'id_theme', 'variable'), __FILE__, __LINE__
		);
	}

	if (!empty($erase_options))
	{
		$smfFunc['db_query']('', "
			DELETE FROM {$db_prefix}themes
			WHERE id_theme != 1
				AND variable IN ('" . implode("', '", $erase_options) . "')
				AND id_member = $memID", __FILE__, __LINE__);
	}

	$themes = explode(',', $modSettings['knownThemes']);
	foreach ($themes as $t)
		cache_put_data('theme_settings-' . $t . ':' . $memID, null, 60);
}

// Make any notification changes that need to be made.
function makeNotificationChanges($memID)
{
	global $db_prefix, $smfFunc;

	// Update the boards they are being notified on.
	if (isset($_POST['edit_notify_boards']) && !empty($_POST['notify_boards']))
	{
		// Make sure only integers are deleted.
		foreach ($_POST['notify_boards'] as $index => $id)
			$_POST['notify_boards'][$index] = (int) $id;

		// id_board = 0 is reserved for topic notifications.
		$_POST['notify_boards'] = array_diff($_POST['notify_boards'], array(0));

		$smfFunc['db_query']('', "
			DELETE FROM {$db_prefix}log_notify
			WHERE id_board IN (" . implode(', ', $_POST['notify_boards']) . ")
				AND id_member = $memID", __FILE__, __LINE__);
	}

	// We are editing topic notifications......
	elseif (isset($_POST['edit_notify_topics']) && !empty($_POST['notify_topics']))
	{
		foreach ($_POST['notify_topics'] as $index => $id)
			$_POST['notify_topics'][$index] = (int) $id;

		// Make sure there are no zeros left.
		$_POST['notify_topics'] = array_diff($_POST['notify_topics'], array(0));

		$smfFunc['db_query']('', "
			DELETE FROM {$db_prefix}log_notify
			WHERE id_topic IN (" . implode(', ', $_POST['notify_topics']) . ")
				AND id_member = $memID", __FILE__, __LINE__);
	}
}

// The avatar is incredibly complicated, what with the options... and what not.
function makeAvatarChanges($memID, &$post_errors)
{
	global $modSettings, $sourcedir, $db_prefix, $smfFunc;

	if (!isset($_POST['avatar_choice']) || empty($memID))
		return;

	require_once($sourcedir . '/ManageAttachments.php');

	$uploadDir = empty($modSettings['custom_avatar_enabled']) ? $modSettings['attachmentUploadDir'] : $modSettings['custom_avatar_dir'];

	$downloadedExternalAvatar = false;
	if ($_POST['avatar_choice'] == 'external' && allowedTo('profile_remote_avatar') && strtolower(substr($_POST['userpicpersonal'], 0, 7)) == 'http://' && strlen($_POST['userpicpersonal']) > 7 && !empty($modSettings['avatar_download_external']))
	{
		if (!is_writable($uploadDir))
			fatal_lang_error('attachments_no_write', 'critical');

		require_once($sourcedir . '/Subs-Package.php');

		$url = parse_url($_POST['userpicpersonal']);
		$contents = fetch_web_data('http://' . $url['host'] . (empty($url['port']) ? '' : ':' . $url['port']) . $url['path']);

		if ($contents != false && $tmpAvatar = fopen($uploadDir . '/avatar_tmp_' . $memID, 'wb'))
		{
			fwrite($tmpAvatar, $contents);
			fclose($tmpAvatar);

			$downloadedExternalAvatar = true;
			$_FILES['attachment']['tmp_name'] = $uploadDir . '/avatar_tmp_' . $memID;
		}
	}

	if ($_POST['avatar_choice'] == 'server_stored' && allowedTo('profile_server_avatar'))
	{
		$_POST['avatar'] = strtr(empty($_POST['file']) ? (empty($_POST['cat']) ? '' : $_POST['cat']) : $_POST['file'], array('&amp;' => '&'));
		$_POST['avatar'] = preg_match('~^([\w _!@%*=\-#()\[\]&.,]+/)?[\w _!@%*=\-#()\[\]&.,]+$~', $_POST['avatar']) != 0 && preg_match('/\.\./', $_POST['avatar']) == 0 && file_exists($modSettings['avatar_directory'] . '/' . $_POST['avatar']) ? ($_POST['avatar'] == 'blank.gif' ? '' : $_POST['avatar']) : '';

		// Get rid of their old avatar. (if uploaded.)
		removeAttachments('a.id_member = ' . $memID);
	}
	elseif ($_POST['avatar_choice'] == 'external' && allowedTo('profile_remote_avatar') && strtolower(substr($_POST['userpicpersonal'], 0, 7)) == 'http://' && empty($modSettings['avatar_download_external']))
	{
		// Remove any attached avatar...
		removeAttachments('a.id_member = ' . $memID);

		$_POST['avatar'] = preg_replace('~action(=|%3d)(?!dlattach)~i', 'action-', $_POST['userpicpersonal']);

		if ($_POST['avatar'] == 'http://' || $_POST['avatar'] == 'http:///')
			$_POST['avatar'] = '';
		// Trying to make us do something we'll regret?
		elseif (substr($_POST['avatar'], 0, 7) != 'http://')
			$post_errors[] = 'bad_avatar';
		// Should we check dimensions?
		elseif (!empty($modSettings['avatar_max_height_external']) || !empty($modSettings['avatar_max_width_external']))
		{
			// Now let's validate the avatar.
			$sizes = url_image_size($_POST['avatar']);

			if (is_array($sizes) && (($sizes[0] > $modSettings['avatar_max_width_external'] && !empty($modSettings['avatar_max_width_external'])) || ($sizes[1] > $modSettings['avatar_max_height_external'] && !empty($modSettings['avatar_max_height_external']))))
			{
				// Houston, we have a problem. The avatar is too large!!
				if ($modSettings['avatar_action_too_large'] == 'option_refuse')
					$post_errors[] = 'bad_avatar';
				elseif ($modSettings['avatar_action_too_large'] == 'option_download_and_resize')
				{
					require_once($sourcedir . '/Subs-Graphics.php');
					if (downloadAvatar($_POST['avatar'], $memID, $modSettings['avatar_max_width_external'], $modSettings['avatar_max_height_external']))
						$_POST['avatar'] = '';
					else
						$post_errors[] = 'bad_avatar';
				}
			}
		}
	}
	elseif (($_POST['avatar_choice'] == 'upload' && allowedTo('profile_upload_avatar') ) || $downloadedExternalAvatar)
	{
		if ((isset($_FILES['attachment']['name']) && $_FILES['attachment']['name'] != '') || $downloadedExternalAvatar)
		{
			// Get the dimensions of the image.
			if (!$downloadedExternalAvatar)
			{
				if (!is_writable($uploadDir))
					fatal_lang_error('attachments_no_write', 'critical');

				if (!move_uploaded_file($_FILES['attachment']['tmp_name'], $uploadDir . '/avatar_tmp_' . $memID))
					fatal_lang_error('attach_timeout', 'critical');

				$_FILES['attachment']['tmp_name'] = $uploadDir . '/avatar_tmp_' . $memID;
			}

			$sizes = @getimagesize($_FILES['attachment']['tmp_name']);

			// No size, then it's probably not a valid pic.
			if ($sizes === false)
				$post_errors[] = 'bad_avatar';
			// Check whether the image is too large.
			elseif ((!empty($modSettings['avatar_max_width_upload']) && $sizes[0] > $modSettings['avatar_max_width_upload']) || (!empty($modSettings['avatar_max_height_upload']) && $sizes[1] > $modSettings['avatar_max_height_upload']))
			{
				if (!empty($modSettings['avatar_resize_upload']))
				{
					// Attempt to chmod it.
					@chmod($uploadDir . '/avatar_tmp_' . $memID, 0644);

					require_once($sourcedir . '/Subs-Graphics.php');
					downloadAvatar($uploadDir . '/avatar_tmp_' . $memID, $memID, $modSettings['avatar_max_width_upload'], $modSettings['avatar_max_height_upload']);
				}
				else
					$post_errors[] = 'bad_avatar';
			}
			elseif (is_array($sizes))
			{
				$extensions = array(
					'1' => '.gif',
					'2' => '.jpg',
					'3' => '.png',
					'6' => '.bmp'
				);
				$extension = isset($extensions[$sizes[2]]) ? $extensions[$sizes[2]] : '.bmp';

				$destName = 'avatar_' . $memID . $extension;
				list ($width, $height) = getimagesize($_FILES['attachment']['tmp_name']);

				// Remove previous attachments this member might have had.
				removeAttachments('a.id_member = ' . $memID);

				if (!rename($_FILES['attachment']['tmp_name'], $uploadDir . '/' . $destName))
					fatal_lang_error('attach_timeout', 'critical');

				$smfFunc['db_query']('', "
					INSERT INTO {$db_prefix}attachments
						(id_member, attachment_type, filename, size, width, height)
					VALUES ($memID, " . (empty($modSettings['custom_avatar_enabled']) ? '0' : '1') . ", '$destName', " . filesize($uploadDir . '/' . $destName) . ", " . (int) $width . ", " . (int) $height . ")", __FILE__, __LINE__);

				// Attempt to chmod it.
				@chmod($uploadDir . '/' . $destName, 0644);
			}
			$_POST['avatar'] = '';

			// Delete any temporary file.
			if (file_exists($uploadDir . '/avatar_tmp_' . $memID))
				@unlink($uploadDir . '/avatar_tmp_' . $memID);
		}
		// Selected the upload avatar option and had one already uploaded before or didn't upload one.
		else
			$_POST['avatar'] = '';
	}
	else
		$_POST['avatar'] = '';
}

// Save any changes to the custom profile fields...
function makeCustomFieldChanges($memID, $area)
{
	global $db_prefix, $context, $smfFunc, $user_profile;

	$where = $area == 'register' ? "show_reg = 1" : "show_profile = '$area'";

	// Load the fields we are saving too - make sure we save valid data (etc).
	$request = $smfFunc['db_query']('', "
		SELECT col_name, field_name, field_desc, field_type, field_length, field_options, default_value, mask
		FROM {$db_prefix}custom_fields
		WHERE $where
			AND active = 1", __FILE__, __LINE__);
	$changes = array();
	while ($row = $smfFunc['db_fetch_assoc']($request))
	{
		// Validate the user data.
		if ($row['field_type'] == 'check')
			$value = isset($_POST['customfield'][$row['col_name']]) ? 1 : 0;
		elseif ($row['field_type'] == 'select' || $row['field_type'] == 'radio')
		{
			$value = $row['default_value'];
			foreach (explode(',', $row['field_options']) as $k => $v)
				if (isset($_POST['customfield'][$row['col_name']]) && $_POST['customfield'][$row['col_name']] == $k)
					$value = $v;
		}
		// Otherwise some form of text!
		else
		{
			$value = isset($_POST['customfield'][$row['col_name']]) ? $_POST['customfield'][$row['col_name']] : '';
			if ($row['field_length'])
				$value = $smfFunc['substr']($value, 0, $row['field_length']);

			// Any masks?
			if ($row['field_type'] == 'text' && !empty($row['mask']) && $row['mask'] != 'none')
			{
				//!!! We never error on this - just ignore it at the moment...
				if ($row['mask'] == 'email' && (preg_match('~^[0-9A-Za-z=_+\-/][0-9A-Za-z=_\'+\-/\.]*@[\w\-]+(\.[\w\-]+)*(\.[\w]{2,6})$~', $smfFunc['db_unescape_string']($value)) === 0 || strlen($smfFunc['db_unescape_string']($value)) > 255))
					$value = '';
				elseif ($row['mask'] == 'number')
				{
					$value = (int) $value;
				}
				elseif (substr($row['mask'], 0, 5) == 'regex' && preg_match(substr($row['mask'], 5), $smfFunc['db_unescape_string']($value)) === 0)
					$value = '';
			}
		}

		$user_profile[$memID]['options'][$row['col_name']] = $value;
		$changes[] = array(1, '\'' . $row['col_name'] . '\'', '\'' . $value . '\'', $memID);
	}
	$smfFunc['db_free_result']($request);

	// Make those changes!
	if (!empty($changes))
		$smfFunc['db_insert']('replace',
			"{$db_prefix}themes",
			array('id_theme', 'variable', 'value', 'id_member'),
			$changes,
			array('id_theme', 'variable', 'id_member'), __FILE__, __LINE__
		);
}

// View a summary.
function summary($memID)
{
	global $context, $memberContext, $txt, $modSettings, $user_info, $user_profile, $sourcedir, $db_prefix, $scripturl, $smfFunc;

	// Attempt to load the member's profile data.
	if (!loadMemberContext($memID) || !isset($memberContext[$memID]))
		fatal_lang_error('not_a_user', false, array($memID));

	// Set up the stuff and load the user.
	$context += array(
		'allow_hide_email' => !empty($modSettings['allow_hide_email']),
		'page_title' => $txt['profile_of'] . ' ' . $memberContext[$memID]['name'],
		'can_send_pm' => allowedTo('pm_send'),
		'can_have_buddy' => allowedTo('profile_identity_own') && !empty($modSettings['enable_buddylist']),
	);
	$context['member'] = &$memberContext[$memID];

	// They haven't even been registered for a full day!?
	$days_registered = (int) ((time() - $user_profile[$memID]['date_registered']) / (3600 * 24));
	if (empty($user_profile[$memID]['date_registered']) || $days_registered < 1)
		$context['member']['posts_per_day'] = $txt['not_applicable'];
	else
		$context['member']['posts_per_day'] = comma_format($context['member']['real_posts'] / $days_registered, 3);

	// Set the age...
	if (empty($context['member']['birth_date']))
	{
		$context['member'] +=  array(
			'age' => &$txt['not_applicable'],
			'today_is_birthday' => false
		);
	}
	else
	{
		list ($birth_year, $birth_month, $birth_day) = sscanf($context['member']['birth_date'], '%d-%d-%d');
		$datearray = getdate(forum_time());
		$context['member'] += array(
			'age' => $birth_year <= 4 ? $txt['not_applicable'] : $datearray['year'] - $birth_year - (($datearray['mon'] > $birth_month || ($datearray['mon'] == $birth_month && $datearray['mday'] >= $birth_day)) ? 0 : 1),
			'today_is_birthday' => $datearray['mon'] == $birth_month && $datearray['mday'] == $birth_day
		);
	}

	if (allowedTo('moderate_forum'))
	{
		// Make sure it's a valid ip address; otherwise, don't bother...
		if (preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/', $memberContext[$memID]['ip']) == 1 && empty($modSettings['disableHostnameLookup']))
			$context['member']['hostname'] = host_from_ip($memberContext[$memID]['ip']);
		else
			$context['member']['hostname'] = '';

		$context['can_see_ip'] = true;
	}
	else
		$context['can_see_ip'] = false;

	if (!empty($modSettings['who_enabled']))
	{
		include_once($sourcedir . '/Who.php');
		$action = determineActions($user_profile[$memID]['url']);

		if ($action !== false)
			$context['member']['action'] = $action;
	}

	// If the user is awaiting activation, and the viewer has permission - setup some activation context messages.
	if ($context['member']['is_activated'] % 10 != 1 && allowedTo('moderate_forum'))
	{
		$context['activate_type'] = $context['member']['is_activated'];
		// What should the link text be?
		$context['activate_link_text'] = in_array($context['member']['is_activated'], array(3, 4, 5, 13, 14, 15)) ? $txt['account_approve'] : $txt['account_activate'];

		// Should we show a custom message?
		$context['activate_message'] = isset($txt['account_activate_method_' . $context['member']['is_activated'] % 10]) ? $txt['account_activate_method_' . $context['member']['is_activated']] : $txt['account_not_activated'];
	}

	// Is the signature even enabled on this forum?
	$context['signature_enabled'] = substr($modSettings['signature_settings'], 0, 1) == 1;

	// How about, are they banned?
	$context['member']['bans'] = array();
	if (allowedTo('moderate_forum'))
	{
		// Can they edit the ban?
		$context['can_edit_ban'] = allowedTo('manage_bans');

		$ban_query = array();
		$ban_query[] = "id_member = " . $context['member']['id'];

		// Valid IP?
		if (preg_match('/^(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})$/', $memberContext[$memID]['ip'], $ip_parts) == 1)
		{
			$ban_query[] = "(($ip_parts[1] BETWEEN bi.ip_low1 AND bi.ip_high1)
						AND ($ip_parts[2] BETWEEN bi.ip_low2 AND bi.ip_high2)
						AND ($ip_parts[3] BETWEEN bi.ip_low3 AND bi.ip_high3)
						AND ($ip_parts[4] BETWEEN bi.ip_low4 AND bi.ip_high4))";

			// Do we have a hostname already?
			if (!empty($context['member']['hostname']))
				$ban_query[] = "('" . $smfFunc['db_escape_string']($context['member']['hostname']) . "' LIKE hostname)";
		}
		// Use '255.255.255.255' for 'unknown' - it's not valid anyway.
		elseif ($memberContext[$memID]['ip'] == 'unknown')
			$ban_query[] = "(bi.ip_low1 = 255 AND bi.ip_high1 = 255
						AND bi.ip_low2 = 255 AND bi.ip_high2 = 255
						AND bi.ip_low3 = 255 AND bi.ip_high3 = 255
						AND bi.ip_low4 = 255 AND bi.ip_high4 = 255)";

		// Check their email as well...
		if (strlen($context['member']['email']) != 0)
			$ban_query[] = "('" . $smfFunc['db_escape_string']($context['member']['email']) . "' LIKE bi.email_address)";

		// So... are they banned?  Dying to know!
		$request = $smfFunc['db_query']('', "
			SELECT bg.id_ban_group, bg.name, bg.cannot_access, bg.cannot_post, bg.cannot_register,
				bg.cannot_login, bg.reason
			FROM {$db_prefix}ban_items AS bi
				INNER JOIN {$db_prefix}ban_groups AS bg ON (bg.id_ban_group = bi.id_ban_group AND (bg.expire_time IS NULL OR bg.expire_time > " . time() . "))
			WHERE (" . implode(' OR ', $ban_query) . ')', __FILE__, __LINE__);
		while ($row = $smfFunc['db_fetch_assoc']($request))
		{
			// Work out what restrictions we actually have.
			$ban_restrictions = array();
			foreach (array('access', 'register', 'login', 'post') as $type)
				if ($row['cannot_' . $type])
					$ban_restrictions[] = $txt['ban_type_' . $type];

			// No actual ban in place?
			if (empty($ban_restrictions))
				continue;

			// Prepare the link for context.
			$ban_explanation = sprintf($txt['user_cannot_due_to'], implode(', ', $ban_restrictions), '<a href="' . $scripturl . '?action=admin;area=ban;sa=edit;bg=' . $row['id_ban_group'] . '">' . $row['name'] . '</a>');

			$context['member']['bans'][$row['id_ban_group']] = array(
				'reason' => empty($row['reason']) ? '' : '<br /><br /><b>' . $txt['ban_reason'] . ':</b> ' . $row['reason'],
				'cannot' => array(
					'access' => !empty($row['cannot_access']),
					'register' => !empty($row['cannot_register']),
					'post' => !empty($row['cannot_post']),
					'login' => !empty($row['cannot_login']),
				),
				'explanation' => $ban_explanation,
			);
		}
		$smfFunc['db_free_result']($request);
	}

	loadCustomFields($memID);
}

// Show all posts by the current user
function showPosts($memID)
{
	global $txt, $user_info, $scripturl, $modSettings, $db_prefix;
	global $context, $user_profile, $sourcedir, $smfFunc;

	// Some initial context.
	$context['start'] = (int) $_REQUEST['start'];
	$context['current_member'] = $memID;

	// Is the load average too high to allow searching just now?
	if (!empty($context['load_average']) && !empty($modSettings['loadavg_show_posts']) && $context['load_average'] >= $modSettings['loadavg_show_posts'])
		fatal_lang_error('loadavg_show_posts_disabled', false);

	// If we're specifically dealing with attachments use that function!
	if (isset($_GET['attach']))
		return showAttachments($memID);

	// Are we just viewing topics?
	$context['is_topics'] = isset($_GET['topics']) ? true : false;

	// If just deleting a message, do it and then redirect back.
	if (isset($_GET['delete']) && !$context['is_topics'])
	{
		checkSession('get');

		// We can be lazy, since removeMessage() will check the permissions for us.
		require_once($sourcedir . '/RemoveTopic.php');
		removeMessage((int) $_GET['delete']);

		// Back to... where we are now ;).
		redirectexit('action=profile;u=' . $memID . ';sa=showPosts;start=' . $_GET['start']);
	}

	// Default to 10.
	if (empty($_REQUEST['viewscount']) || !is_numeric($_REQUEST['viewscount']))
		$_REQUEST['viewscount'] = '10';

	$request = $smfFunc['db_query']('', "
		SELECT COUNT(*)
		FROM {$db_prefix}messages AS m" . ($context['is_topics'] ? "
			INNER JOIN {$db_prefix}topics AS t ON (t.id_first_msg = m.id_msg)" : '') . "
			INNER JOIN {$db_prefix}boards AS b ON (b.id_board = m.id_board AND $user_info[query_see_board])
		WHERE m.id_member = $memID
			AND m.approved = 1", __FILE__, __LINE__);
	list ($msgCount) = $smfFunc['db_fetch_row']($request);
	$smfFunc['db_free_result']($request);

	$request = $smfFunc['db_query']('', "
		SELECT MIN(id_msg), MAX(id_msg)
		FROM {$db_prefix}messages AS m
		WHERE m.id_member = $memID
			AND m.approved = 1", __FILE__, __LINE__);
	list ($min_msg_member, $max_msg_member) = $smfFunc['db_fetch_row']($request);
	$smfFunc['db_free_result']($request);

	$reverse = false;
	$range_limit = '';
	$maxIndex = (int) $modSettings['defaultMaxMessages'];

	// Make sure the starting place makes sense and construct our friend the page index.
	$context['page_index'] = constructPageIndex($scripturl . '?action=profile;u=' . $memID . ';sa=showPosts' . ($context['is_topics'] ? ';topics' : ''), $context['start'], $msgCount, $maxIndex);
	$context['current_page'] = $context['start'] / $maxIndex;

	// Reverse the query if we're past 50% of the pages for better performance.
	$start = $context['start'];
	$reverse = $_REQUEST['start'] > $msgCount / 2;
	if ($reverse)
	{
		$maxIndex = $msgCount < $context['start'] + $modSettings['defaultMaxMessages'] + 1 && $msgCount > $context['start'] ? $msgCount - $context['start'] : (int) $modSettings['defaultMaxMessages'];
		$start = $msgCount < $context['start'] + $modSettings['defaultMaxMessages'] + 1 || $msgCount < $context['start'] + $modSettings['defaultMaxMessages'] ? 0 : $msgCount - $context['start'] - $modSettings['defaultMaxMessages'];
	}

	// Guess the range of messages to be shown.
	if ($msgCount > 1000)
	{
		$margin = floor(($max_msg_member - $min_msg_member) * (($start + $modSettings['defaultMaxMessages']) / $msgCount) + .1 * ($max_msg_member - $min_msg_member));
		// Make a bigger margin for topics only.
		if ($context['is_topics'])
			$margin *= 5;

		$range_limit = $reverse ? 'id_msg < ' . ($min_msg_member + $margin) : 'id_msg > ' . ($max_msg_member - $margin);
	}

	$context['page_title'] = $txt['latest_posts'] . ' ' . $user_profile[$memID]['real_name'];

	// Find this user's posts.  The left join on categories somehow makes this faster, weird as it looks.
	$looped = false;
	while (true)
	{
		$request = $smfFunc['db_query']('', "
			SELECT
				b.id_board, b.name AS bname, c.id_cat, c.name AS cname, m.id_topic, m.id_msg,
				t.id_member_started, t.id_first_msg, t.id_last_msg, m.body, m.smileys_enabled,
				m.subject, m.poster_time
			FROM {$db_prefix}messages AS m
				INNER JOIN {$db_prefix}topics AS t ON (" . ($context['is_topics'] ? 't.id_first_msg = m.id_msg' : 't.id_topic = m.id_topic') . ")
				INNER JOIN {$db_prefix}boards AS b ON (b.id_board = t.id_board)
				LEFT JOIN {$db_prefix}categories AS c ON (c.id_cat = b.id_cat)
			WHERE m.id_member = $memID
				" . (empty($range_limit) ? '' : "
				AND $range_limit") . "
				AND $user_info[query_see_board]
				AND t.approved = 1
				AND m.approved = 1
			ORDER BY m.id_msg " . ($reverse ? 'ASC' : 'DESC') . "
			LIMIT $start, $maxIndex", __FILE__, __LINE__);

		// Make sure we quit this loop.
		if ($smfFunc['db_num_rows']($request) === $maxIndex || $looped)
			break;
		$looped = true;
		$range_limit = '';
	}

	// Start counting at the number of the first message displayed.
	$counter = $reverse ? $context['start'] + $maxIndex + 1 : $context['start'];
	$context['posts'] = array();
	$board_ids = array('own' => array(), 'any' => array());
	while ($row = $smfFunc['db_fetch_assoc']($request))
	{
		// Censor....
		censorText($row['body']);
		censorText($row['subject']);

		// Do the code.
		$row['body'] = parse_bbc($row['body'], $row['smileys_enabled'], $row['id_msg']);

		// And the array...
		$context['posts'][$counter += $reverse ? -1 : 1] = array(
			'body' => $row['body'],
			'counter' => $counter,
			'category' => array(
				'name' => $row['cname'],
				'id' => $row['id_cat']
			),
			'board' => array(
				'name' => $row['bname'],
				'id' => $row['id_board']
			),
			'topic' => $row['id_topic'],
			'subject' => $row['subject'],
			'start' => 'msg' . $row['id_msg'],
			'time' => timeformat($row['poster_time']),
			'timestamp' => forum_time(true, $row['poster_time']),
			'id' => $row['id_msg'],
			'can_reply' => false,
			'can_mark_notify' => false,
			'can_delete' => false,
			'delete_possible' => ($row['id_first_msg'] != $row['id_msg'] || $row['id_last_msg'] == $row['id_msg']) && (empty($modSettings['edit_disable_time']) || $row['poster_time'] + $modSettings['edit_disable_time'] * 60 >= time()),
		);

		if ($user_info['id'] == $row['id_member_started'])
			$board_ids['own'][$row['id_board']][] = $counter;
		$board_ids['any'][$row['id_board']][] = $counter;
	}
	$smfFunc['db_free_result']($request);

	// All posts were retrieved in reverse order, get them right again.
	if ($reverse)
		$context['posts'] = array_reverse($context['posts'], true);

	// These are all the permissions that are different from board to board..
	if ($context['is_topics'])
		$permissions = array(
			'own' => array(
				'post_reply_own' => 'can_reply',
			),
			'any' => array(
				'post_reply_any' => 'can_reply',
				'mark_any_notify' => 'can_mark_notify',
			)
		);
	else
		$permissions = array(
			'own' => array(
				'post_reply_own' => 'can_reply',
				'delete_own' => 'can_delete',
			),
			'any' => array(
				'post_reply_any' => 'can_reply',
				'mark_any_notify' => 'can_mark_notify',
				'delete_any' => 'can_delete',
			)
		);

	// For every permission in the own/any lists...
	foreach ($permissions as $type => $list)
	{
		foreach ($list as $permission => $allowed)
		{
			// Get the boards they can do this on...
			$boards = boardsAllowedTo($permission);

			// Hmm, they can do it on all boards, can they?
			if (!empty($boards) && $boards[0] == 0)
				$boards = array_keys($board_ids[$type]);

			// Now go through each board they can do the permission on.
			foreach ($boards as $board_id)
			{
				// There aren't any posts displayed from this board.
				if (!isset($board_ids[$type][$board_id]))
					continue;

				// Set the permission to true ;).
				foreach ($board_ids[$type][$board_id] as $counter)
					$context['posts'][$counter][$allowed] = true;
			}
		}
	}

	// Clean up after posts that cannot be deleted.
	foreach ($context['posts'] as $counter => $dummy)
		$context['posts'][$counter]['can_delete'] &= $context['posts'][$counter]['delete_possible'];
}

// Show all the attachments of a user.
function showAttachments($memID)
{
	global $txt, $user_info, $scripturl, $modSettings, $db_prefix;
	global $context, $user_profile, $sourcedir, $smfFunc;

	// OBEY permissions!
	$boardsAllowed = boardsAllowedTo('view_attachment');
	// Make sure we can't actually see anything...
	if (empty($boardsAllowed))
		$boardsAllowed = array(-1);

	// Get the total number of attachments they have posted.
	$request = $smfFunc['db_query']('', "
		SELECT COUNT(*)
		FROM {$db_prefix}attachments AS a
			INNER JOIN {$db_prefix}messages AS m ON (m.id_msg = a.id_msg)
			INNER JOIN {$db_prefix}boards AS b ON (b.id_board = m.id_board)
		WHERE a.attachment_type = 0
			AND a.id_msg != 0
			AND m.id_member = $memID
			AND $user_info[query_see_board]" . (!in_array(0, $boardsAllowed) ? "
			AND b.id_board IN (" . implode(', ', $boardsAllowed) . ")" : '') . "
			AND m.approved = 1", __FILE__, __LINE__);
	list ($attachCount) = $smfFunc['db_fetch_row']($request);
	$smfFunc['db_free_result']($request);

	$maxIndex = (int) $modSettings['defaultMaxMessages'];

	// What about ordering?
	$sortTypes = array(
		'filename' => 'a.filename',
		'downloads' => 'a.downloads',
		'subject' => 'm.subject',
		'posted' => 'm.poster_time',
	);
	$context['sort_order'] = isset($_GET['sort']) && isset($sortTypes[$_GET['sort']]) ? $_GET['sort'] : 'posted';
	$context['sort_direction'] = isset($_GET['desc']) ? 'down' : 'up';

	$sort =	$sortTypes[$context['sort_order']];

	// Let's get ourselves a lovely page index.
	$context['page_index'] = constructPageIndex($scripturl . '?action=profile;u=' . $memID . ';sa=showPosts;attach;sort=' . $sort . ($context['sort_direction'] == 'down' ? ';desc' : ''), $context['start'], $attachCount, $maxIndex);

	// Retrieve a some attachments.
	$request = $smfFunc['db_query']('', "
		SELECT a.id_attach, a.id_msg, a.filename, a.downloads, m.id_msg, m.id_topic, m.id_board,
			m.poster_time, m.subject, b.name
		FROM {$db_prefix}attachments AS a
			INNER JOIN {$db_prefix}messages AS m ON (m.id_msg = a.id_msg)
			INNER JOIN {$db_prefix}boards AS b ON (b.id_board = m.id_board)
		WHERE a.attachment_type = 0
			AND a.id_msg != 0
			AND m.id_member = $memID
			AND $user_info[query_see_board]" . (!in_array(0, $boardsAllowed) ? "
			AND b.id_board IN (" . implode(', ', $boardsAllowed) . ")" : '') . "
			AND m.approved = 1
		ORDER BY $sort " . ($context['sort_direction'] == 'down' ? 'DESC' : 'ASC') . "
		LIMIT $context[start], $maxIndex", __FILE__, __LINE__);
	$context['attachments'] = array();
	while ($row = $smfFunc['db_fetch_assoc']($request))
	{
		$row['subject'] = censorText($row['subject']);

		$context['attachments'][] = array(
			'id' => $row['id_attach'],
			'filename' => $row['filename'],
			'downloads' => $row['downloads'],
			'subject' => $row['subject'],
			'posted' => timeformat($row['poster_time']),
			'msg' => $row['id_msg'],
			'topic' => $row['id_topic'],
			'board' => $row['id_board'],
			'board_name' => $row['name'],
		);
	}
	$smfFunc['db_free_result']($request);
}

// Show all the users buddies, as well as a add/delete interface.
function editBuddies($memID)
{
	global $txt, $scripturl, $modSettings, $db_prefix;
	global $context, $user_profile, $memberContext, $smfFunc;

	// Do a quick check to ensure people aren't getting here illegally!
	if (!$context['user']['is_owner'] || empty($modSettings['enable_buddylist']))
		fatal_lang_error('no_access', false);

	// !!! No page_title.

	// For making changes!
	$buddiesArray = explode(',', $user_profile[$memID]['buddy_list']);
	foreach ($buddiesArray as $k => $dummy)
		if ($dummy == '')
			unset($buddiesArray[$k]);

	// Removing a buddy?
	if (isset($_GET['remove']))
	{
		// Heh, I'm lazy, do it the easy way...
		foreach ($buddiesArray as $key => $buddy)
			if ($buddy == (int) $_GET['remove'])
				unset($buddiesArray[$key]);

		// Make the changes.
		$user_profile[$memID]['buddy_list'] = implode(',', $buddiesArray);
		updateMemberData($memID, array('buddy_list' => "'" . $user_profile[$memID]['buddy_list'] . "'"));

		// Redirect off the page because we don't like all this ugly query stuff to stick in the history.
		redirectexit('action=profile;u=' . $memID . ';sa=editBuddies');
	}
	elseif (isset($_POST['new_buddy']))
	{
		// Prepare the string for extraction...
		$_POST['new_buddy'] = strtr($smfFunc['htmlspecialchars']($smfFunc['db_unescape_string']($_POST['new_buddy']), ENT_QUOTES), array('&quot;' => '"'));
		preg_match_all('~"([^"]+)"~', $_POST['new_buddy'], $matches);
		$new_buddies = array_unique(array_merge($matches[1], explode(',', preg_replace('~"([^"]+)"~', '', $_POST['new_buddy']))));

		foreach ($new_buddies as $k => $dummy)
		{
			$new_buddies[$k] = strtr(trim($new_buddies[$k]), array('\\\'' => '&#039;'));

			if (strlen($new_buddies[$k]) == 0)
				unset($new_buddies[$k]);
		}

		if (!empty($new_buddies))
		{
			// Now find out the id_member of the buddy.
			$request = $smfFunc['db_query']('', "
				SELECT id_member
				FROM {$db_prefix}members
				WHERE member_name IN ('" . implode("','", $new_buddies) . "') OR real_name IN ('" . implode("','", $new_buddies) . "')
				LIMIT " . count($new_buddies), __FILE__, __LINE__);

			// Add the new member to the buddies array.
			while ($row = $smfFunc['db_fetch_assoc']($request))
				$buddiesArray[] = (int) $row['id_member'];
			$smfFunc['db_free_result']($request);

			// Now update the current users buddy list.
			$user_profile[$memID]['buddy_list'] = implode(',', $buddiesArray);
			updateMemberData($memID, array('buddy_list' => "'" . $user_profile[$memID]['buddy_list'] . "'"));
		}

		// Back to the buddy list!
		redirectexit('action=profile;u=' . $memID . ';sa=editBuddies');
	}

	// Get all the users "buddies"...
	$buddies = array();

	if (!empty($buddiesArray))
	{
		$result = $smfFunc['db_query']('', "
			SELECT id_member
			FROM {$db_prefix}members
			WHERE id_member IN (" . implode(', ', $buddiesArray) . ")
			ORDER BY real_name
			LIMIT " . (substr_count($user_profile[$memID]['buddy_list'], ',') + 1), __FILE__, __LINE__);
		while ($row = $smfFunc['db_fetch_assoc']($result))
			$buddies[] = $row['id_member'];
		$smfFunc['db_free_result']($result);
	}

	$context['buddy_count'] = count($buddies);

	// Load all the members up.
	loadMemberData($buddies, false, 'profile');

	// Setup the context for each buddy.
	$context['buddies'] = array();
	foreach ($buddies as $buddy)
	{
		loadMemberContext($buddy);
		$context['buddies'][$buddy] = $memberContext[$buddy];
	}
}

function statPanel($memID)
{
	global $txt, $scripturl, $db_prefix, $context, $user_profile, $user_info, $modSettings, $smfFunc;

	$context['page_title'] = $txt['statPanel_showStats'] . ' ' . $user_profile[$memID]['real_name'];

	// General user statistics.
	$timeDays = floor($user_profile[$memID]['total_time_logged_in'] / 86400);
	$timeHours = floor(($user_profile[$memID]['total_time_logged_in'] % 86400) / 3600);
	$context['time_logged_in'] = ($timeDays > 0 ? $timeDays . $txt['totalTimeLogged2'] : '') . ($timeHours > 0 ? $timeHours . $txt['totalTimeLogged3'] : '') . floor(($user_profile[$memID]['total_time_logged_in'] % 3600) / 60) . $txt['totalTimeLogged4'];
	$context['num_posts'] = comma_format($user_profile[$memID]['posts']);

	// Number of topics started.
	// !!!SLOW This query is sorta slow...
	$result = $smfFunc['db_query']('', "
		SELECT COUNT(*)
		FROM {$db_prefix}topics
		WHERE id_member_started = $memID" . (!empty($modSettings['recycle_enable']) && $modSettings['recycle_board'] > 0 ? "
			AND id_board != $modSettings[recycle_board]" : ''), __FILE__, __LINE__);
	list ($context['num_topics']) = $smfFunc['db_fetch_row']($result);
	$smfFunc['db_free_result']($result);

	// Number polls started.
	$result = $smfFunc['db_query']('', "
		SELECT COUNT(*)
		FROM {$db_prefix}topics
		WHERE id_member_started = $memID" . (!empty($modSettings['recycle_enable']) && $modSettings['recycle_board'] > 0 ? "
			AND id_board != $modSettings[recycle_board]" : '') . "
			AND id_poll != 0", __FILE__, __LINE__);
	list ($context['num_polls']) = $smfFunc['db_fetch_row']($result);
	$smfFunc['db_free_result']($result);

	// Number polls voted in.
	$result = $smfFunc['db_query']('distinct_poll_votes', "
		SELECT COUNT(DISTINCT id_poll)
		FROM {$db_prefix}log_polls
		WHERE id_member = $memID", __FILE__, __LINE__);
	list ($context['num_votes']) = $smfFunc['db_fetch_row']($result);
	$smfFunc['db_free_result']($result);

	// Format the numbers...
	$context['num_topics'] = comma_format($context['num_topics']);
	$context['num_polls'] = comma_format($context['num_polls']);
	$context['num_votes'] = comma_format($context['num_votes']);

	// Grab the board this member posted in most often.
	$result = $smfFunc['db_query']('', "
		SELECT
			b.id_board, MAX(b.name) AS name, MAX(b.num_posts) AS num_posts, COUNT(*) AS message_count
		FROM {$db_prefix}messages AS m
			INNER JOIN {$db_prefix}boards AS b ON (b.id_board = m.id_board)
		WHERE m.id_member = $memID
			AND $user_info[query_see_board]
		GROUP BY b.id_board
		ORDER BY message_count DESC
		LIMIT 10", __FILE__, __LINE__);
	$context['popular_boards'] = array();
	$maxPosts = 0;
	while ($row = $smfFunc['db_fetch_assoc']($result))
	{
		if ($row['message_count'] > $maxPosts)
			$maxPosts = $row['message_count'];

		$context['popular_boards'][$row['id_board']] = array(
			'id' => $row['id_board'],
			'posts' => $row['message_count'],
			'href' => $scripturl . '?board=' . $row['id_board'] . '.0',
			'link' => '<a href="' . $scripturl . '?board=' . $row['id_board'] . '.0">' . $row['name'] . '</a>',
			'posts_percent' => 0,
			'total_posts' => $row['num_posts'],
		);
	}
	$smfFunc['db_free_result']($result);

	// Now that we know the total, calculate the percentage.
	foreach ($context['popular_boards'] as $id_board => $board_data)
		$context['popular_boards'][$id_board]['posts_percent'] = $board_data['total_posts'] == 0 ? 0 : comma_format(($board_data['posts'] * 100) / $board_data['total_posts'], 2);

	// Now get the 10 boards this user has most often participated in.
	$result = $smfFunc['db_query']('', "
		SELECT
			b.id_board, MAX(b.name) AS name, CASE WHEN COUNT(*) > MAX(b.num_posts) THEN 1 ELSE COUNT(*) / MAX(b.num_posts) END * 100 AS percentage
		FROM {$db_prefix}messages AS m
			INNER JOIN {$db_prefix}boards AS b ON (b.id_board = m.id_board)
		WHERE m.id_member = $memID
			AND $user_info[query_see_board]
		GROUP BY b.id_board
		ORDER BY percentage DESC
		LIMIT 10", __FILE__, __LINE__);
	$context['board_activity'] = array();
	while ($row = $smfFunc['db_fetch_assoc']($result))
	{
		$context['board_activity'][$row['id_board']] = array(
			'id' => $id_board,
			'href' => $scripturl . '?board=' . $row['id_board'] . '.0',
			'link' => '<a href="' . $scripturl . '?board=' . $row['id_board'] . '.0">' . $row['name'] . '</a>',
			'percent' => $row['percentage'],
		);
	}
	$smfFunc['db_free_result']($result);

	// Posting activity by time.
	$result = $smfFunc['db_query']('user_activity_by_time', "
		SELECT
			HOUR(FROM_UNIXTIME(poster_time + " . (($user_info['time_offset'] + $modSettings['time_offset']) * 3600) . ")) AS hour,
			COUNT(*) AS post_count
		FROM {$db_prefix}messages
		WHERE id_member = $memID" . ($modSettings['totalMessages'] > 100000 ? "
			AND id_topic > " . ($modSettings['totalTopics'] - 10000) : '') . "
		GROUP BY hour", __FILE__, __LINE__);
	$maxPosts = 0;
	$context['posts_by_time'] = array();
	while ($row = $smfFunc['db_fetch_assoc']($result))
	{
		if ($row['post_count'] > $maxPosts)
			$maxPosts = $row['post_count'];

		$context['posts_by_time'][$row['hour']] = array(
			'hour' => $row['hour'],
			'posts_percent' => $row['post_count']
		);
	}
	$smfFunc['db_free_result']($result);

	if ($maxPosts > 0)
		for ($hour = 0; $hour < 24; $hour++)
		{
			if (!isset($context['posts_by_time'][$hour]))
				$context['posts_by_time'][$hour] = array(
					'hour' => $hour,
					'posts_percent' => 0,
				);
			else
				$context['posts_by_time'][$hour]['posts_percent'] = round(($context['posts_by_time'][$hour]['posts_percent'] * 100) / $maxPosts);
		}

	// Put it in the right order.
	ksort($context['posts_by_time']);
}

function trackUser($memID)
{
	global $scripturl, $txt, $db_prefix, $modSettings;
	global $user_profile, $context, $smfFunc;

	// Verify if the user has sufficient permissions.
	isAllowedTo('moderate_forum');

	$context['page_title'] = $txt['trackUser'] . ' - ' . $user_profile[$memID]['real_name'];

	$context['last_ip'] = $user_profile[$memID]['member_ip'];
	if ($context['last_ip'] != $user_profile[$memID]['member_ip2'])
		$context['last_ip2'] = $user_profile[$memID]['member_ip2'];
	$context['member']['name'] = $user_profile[$memID]['real_name'];

	// If this is a big forum, or a large posting user, let's limit the search.
	if ($modSettings['totalMessages'] > 50000 && $user_profile[$memID]['posts'] > 500)
	{
		$request = $smfFunc['db_query']('', "
			SELECT MAX(id_msg)
			FROM {$db_prefix}messages AS m
			WHERE m.id_member = $memID", __FILE__, __LINE__);
		list ($max_msg_member) = $smfFunc['db_fetch_row']($request);
		$smfFunc['db_free_result']($request);

		// There's no point worrying ourselves with messages made yonks ago, just get recent ones!
		$min_msg_member = max(0, $max_msg_member - $user_profile[$memID]['posts'] * 3);
	}
	
	// Default to at least the ones we know about.
	$ips = array(
		$user_profile[$memID]['member_ip'],
		$user_profile[$memID]['member_ip2'],
	);

	// Get all IP addresses this user has used for his messages.
	$request = $smfFunc['db_query']('', "
		SELECT poster_ip
		FROM {$db_prefix}messages
		WHERE id_member = $memID
		" . (isset($min_msg_member) ? "
			AND id_msg >= $min_msg_member AND id_msg <= $max_msg_member" : '') . "
		GROUP BY poster_ip", __FILE__, __LINE__);
	$context['ips'] = array();
	while ($row = $smfFunc['db_fetch_assoc']($request))
	{
		$context['ips'][] = '<a href="' . $scripturl . '?action=trackip;searchip=' . $row['poster_ip'] . '">' . $row['poster_ip'] . '</a>';
		$ips[] = $row['poster_ip'];
	}
	$smfFunc['db_free_result']($request);

	// Now also get the IP addresses from the error messages.
	$request = $smfFunc['db_query']('', "
		SELECT COUNT(*) AS error_count, ip
		FROM {$db_prefix}log_errors
		WHERE id_member = $memID
		GROUP BY ip", __FILE__, __LINE__);
	$context['error_ips'] = array();
	$totalErrors = 0;
	while ($row = $smfFunc['db_fetch_assoc']($request))
	{
		$context['error_ips'][] = '<a href="' . $scripturl . '?action=trackip;searchip=' . $row['ip'] . '">' . $row['ip'] . '</a>';
		$ips[] = $row['ip'];
		$totalErrors += $row['error_count'];
	}
	$smfFunc['db_free_result']($request);

	// Create the page indexes.
	$context['page_index'] = constructPageIndex($scripturl . '?action=profile;u=' . $memID . ';sa=trackUser', $_REQUEST['start'], $totalErrors, 20);
	$context['start'] = $_REQUEST['start'];

	// Get a list of error messages from this ip (range).
	$request = $smfFunc['db_query']('', "
		SELECT
			le.log_time, le.ip, le.url, le.message, IFNULL(mem.id_member, 0) AS id_member,
			IFNULL(mem.real_name, '$txt[guest_title]') AS display_name, mem.member_name
		FROM {$db_prefix}log_errors AS le
			LEFT JOIN {$db_prefix}members AS mem ON (mem.id_member = le.id_member)
		WHERE le.id_member = $memID
		ORDER BY le.id_error DESC
		LIMIT $context[start], 20", __FILE__, __LINE__);
	$context['error_messages'] = array();
	while ($row = $smfFunc['db_fetch_assoc']($request))
		$context['error_messages'][] = array(
			'ip' => $row['ip'],
			'message' => strtr($row['message'], array('&lt;span class=&quot;remove&quot;&gt;' => '', '&lt;/span&gt;' => '')),
			'url' => $row['url'],
			'time' => timeformat($row['log_time']),
			'timestamp' => forum_time(true, $row['log_time'])
		);
	$smfFunc['db_free_result']($request);

	// Find other users that might use the same IP.
	$ips = array_unique($ips);
	$context['members_in_range'] = array();
	if (!empty($ips))
	{
		$request = $smfFunc['db_query']('', "
			SELECT id_member, real_name
			FROM {$db_prefix}members
			WHERE id_member != $memID
				AND member_ip IN ('" . implode("', '", $ips) . "')", __FILE__, __LINE__);
		if ($smfFunc['db_num_rows']($request) > 0)
			while ($row = $smfFunc['db_fetch_assoc']($request))
				$context['members_in_range'][$row['id_member']] = '<a href="' . $scripturl . '?action=profile;u=' . $row['id_member'] . '">' . $row['real_name'] . '</a>';
		$smfFunc['db_free_result']($request);

		$request = $smfFunc['db_query']('', "
			SELECT mem.id_member, mem.real_name
			FROM {$db_prefix}messages AS m
				INNER JOIN {$db_prefix}members AS mem ON (mem.id_member = m.id_member AND mem.id_member != $memID)
			WHERE m.poster_ip IN ('" . implode("', '", $ips) . "')", __FILE__, __LINE__);
		if ($smfFunc['db_num_rows']($request) > 0)
			while ($row = $smfFunc['db_fetch_assoc']($request))
				$context['members_in_range'][$row['id_member']] = '<a href="' . $scripturl . '?action=profile;u=' . $row['id_member'] . '">' . $row['real_name'] . '</a>';
		$smfFunc['db_free_result']($request);
	}
}

function TrackIP($memID = 0)
{
	global $user_profile, $scripturl, $txt, $user_info;
	global $db_prefix, $context, $smfFunc;

	// Can the user do this?
	isAllowedTo('moderate_forum');

	if ($memID == 0)
	{
		$context['ip'] = isset($_REQUEST['searchip']) ? trim($_REQUEST['searchip']) : $user_info['ip'];
		loadTemplate('Profile');
		loadLanguage('Profile');
		$context['sub_template'] = 'trackIP';
		$context['page_title'] = $txt['profile'];
	}
	else
		$context['ip'] = $user_profile[$memID]['member_ip'];

	if (preg_match('/^\d{1,3}\.(\d{1,3}|\*)\.(\d{1,3}|\*)\.(\d{1,3}|\*)$/', $context['ip']) == 0)
		fatal_lang_error('invalid_ip', false);

	$dbip = str_replace('*', '%', $context['ip']);
	$dbip = strpos($dbip, '%') === false ? "= '$dbip'" : "LIKE '$dbip'";

	$context['page_title'] = $txt['trackIP'] . ' - ' . $context['ip'];

	// Get some totals for pagination.
	$request = $smfFunc['db_query']('', "
		SELECT COUNT(*)
		FROM {$db_prefix}messages
		WHERE poster_ip $dbip", __FILE__, __LINE__);
	list ($totalMessages) = $smfFunc['db_fetch_row']($request);
	$smfFunc['db_free_result']($request);
	$request = $smfFunc['db_query']('', "
		SELECT COUNT(*)
		FROM {$db_prefix}log_errors
		WHERE ip $dbip", __FILE__, __LINE__);
	list ($totalErrors) = $smfFunc['db_fetch_row']($request);
	$smfFunc['db_free_result']($request);

	$context['message_start'] = isset($_GET['mesStart']) ? (int) $_GET['mesStart'] : 0;
	$context['error_start'] = isset($_GET['errStart']) ? $_GET['errStart'] : 0;
	$context['message_page_index'] = constructPageIndex($scripturl . '?action=' . ($memID == 0 ? 'trackip;searchip=' . $context['ip'] : 'profile;u=' . $memID . ';sa=trackIP') . ';mesStart=%d;errStart=' . $context['error_start'], $context['message_start'], $totalMessages, 20, true);
	$context['error_page_index'] = constructPageIndex($scripturl . '?action=' . ($memID == 0 ? 'trackip;searchip=' . $context['ip'] : 'profile;u=' . $memID . ';sa=trackIP') . ';mesStart=' . $context['message_start'] . ';errStart=%d', $context['error_start'], $totalErrors, 20, true);

	$request = $smfFunc['db_query']('', "
		SELECT id_member, real_name AS display_name, member_ip
		FROM {$db_prefix}members
		WHERE member_ip $dbip", __FILE__, __LINE__);
	$context['ips'] = array();
	while ($row = $smfFunc['db_fetch_assoc']($request))
		$context['ips'][$row['member_ip']][] = '<a href="' . $scripturl . '?action=profile;u=' . $row['id_member'] . '">' . $row['display_name'] . '</a>';
	$smfFunc['db_free_result']($request);

	ksort($context['ips']);

	// !!!SLOW This query is using a filesort.
	$request = $smfFunc['db_query']('', "
		SELECT
			m.id_msg, m.poster_ip, IFNULL(mem.real_name, m.poster_name) AS display_name, mem.id_member,
			m.subject, m.poster_time, m.id_topic, m.id_board
		FROM {$db_prefix}messages AS m
			LEFT JOIN {$db_prefix}members AS mem ON (mem.id_member = m.id_member)
		WHERE m.poster_ip $dbip
		ORDER BY m.id_msg DESC
		LIMIT $context[message_start], 20", __FILE__, __LINE__);
	$context['messages'] = array();
	while ($row = $smfFunc['db_fetch_assoc']($request))
		$context['messages'][] = array(
			'ip' => $row['poster_ip'],
			'member' => array(
				'id' => $row['id_member'],
				'name' => $row['display_name'],
				'href' => $scripturl . '?action=profile;u=' . $row['id_member'],
				'link' => '<a href="' . $scripturl . '?action=profile;u=' . $row['id_member'] . '">' . $row['display_name'] . '</a>'
			),
			'board' => array(
				'id' => $row['id_board'],
				'href' => $scripturl . '?board=' . $row['id_board']
			),
			'topic' => $row['id_topic'],
			'id' => $row['id_msg'],
			'subject' => $row['subject'],
			'time' => timeformat($row['poster_time']),
			'timestamp' => forum_time(true, $row['poster_time'])
		);
	$smfFunc['db_free_result']($request);

	// !!!SLOW This query is using a filesort.
	$request = $smfFunc['db_query']('', "
		SELECT
			le.log_time, le.ip, le.url, le.message, IFNULL(mem.id_member, 0) AS id_member,
			IFNULL(mem.real_name, '$txt[guest_title]') AS display_name, mem.member_name
		FROM {$db_prefix}log_errors AS le
			LEFT JOIN {$db_prefix}members AS mem ON (mem.id_member = le.id_member)
		WHERE le.ip $dbip
		ORDER BY le.id_error DESC
		LIMIT $context[error_start], 20", __FILE__, __LINE__);
	$context['error_messages'] = array();
	while ($row = $smfFunc['db_fetch_assoc']($request))
		$context['error_messages'][] = array(
			'ip' => $row['ip'],
			'member' => array(
				'id' => $row['id_member'],
				'name' => $row['display_name'],
				'href' => $row['id_member'] > 0 ? $scripturl . '?action=profile;u=' . $row['id_member'] : '',
				'link' => $row['id_member'] > 0 ? '<a href="' . $scripturl . '?action=profile;u=' . $row['id_member'] . '">' . $row['display_name'] . '</a>' : $row['display_name']
			),
			'message' => strtr($row['message'], array('&lt;span class=&quot;remove&quot;&gt;' => '', '&lt;/span&gt;' => '')),
			'url' => $row['url'],
			'error_time' => timeformat($row['log_time'])
		);
	$smfFunc['db_free_result']($request);

	$context['single_ip'] = strpos($context['ip'], '*') === false;
	if ($context['single_ip'])
	{
		$context['whois_servers'] = array(
			'afrinic' => array(
				'name' => &$txt['whois_afrinic'],
				'url' => 'http://www.afrinic.net/cgi-bin/whois?searchtext=' . $context['ip'],
				'range' => array(),
			),
			'apnic' => array(
				'name' => &$txt['whois_apnic'],
				'url' => 'http://www.apnic.net/apnic-bin/whois2.pl?searchtext=' . $context['ip'],
				'range' => array(58, 59, 60, 61, 124, 125, 126, 202, 203, 210, 211, 218, 219, 220, 221, 222),
			),
			'arin' => array(
				'name' => &$txt['whois_arin'],
				'url' => 'http://ws.arin.net/cgi-bin/whois.pl?queryinput=' . $context['ip'],
				'range' => array(63, 64, 65, 66, 67, 68, 69, 70, 71, 72, 199, 204, 205, 206, 207, 208, 209, 216),
			),
			'lacnic' => array(
				'name' => &$txt['whois_lacnic'],
				'url' => 'http://lacnic.net/cgi-bin/lacnic/whois?query=' . $context['ip'],
				'range' => array(200, 201),
			),
			'ripe' => array(
				'name' => &$txt['whois_ripe'],
				'url' => 'http://www.ripe.net/perl/whois?searchtext=' . $context['ip'],
				'range' => array(62, 80, 81, 82, 83, 84, 85, 86, 87, 88, 193, 194, 195, 212, 213, 217),
			),
		);

		foreach ($context['whois_servers'] as $whois)
		{
			// Strip off the "decimal point" and anything following...
			if (in_array((int) $context['ip'], $whois['range']))
				$context['auto_whois_server'] = $whois;
		}
	}
}

function showPermissions($memID)
{
	global $scripturl, $txt, $db_prefix, $board, $modSettings;
	global $user_profile, $context, $user_info, $sourcedir, $smfFunc;

	// Verify if the user has sufficient permissions.
	isAllowedTo('manage_permissions');

	loadLanguage('ManagePermissions');
	loadLanguage('Admin');
	loadTemplate('ManageMembers');

	// Load all the permission profiles.
	require_once($sourcedir . '/ManagePermissions.php');
	loadPermissionProfiles();

	$context['member']['id'] = $memID;
	$context['member']['name'] = $user_profile[$memID]['real_name'];

	$context['page_title'] = $txt['showPermissions'];
	$board = empty($board) ? 0 : (int) $board;
	$context['board'] = $board;

	// Determine which groups this user is in.
	if (empty($user_profile[$memID]['additional_groups']))
		$curGroups = array();
	else
		$curGroups = explode(',', $user_profile[$memID]['additional_groups']);
	$curGroups[] = $user_profile[$memID]['id_group'];
	$curGroups[] = $user_profile[$memID]['id_post_group'];

	// Load a list of boards for the jump box - except the defaults.
	$request = $smfFunc['db_query']('', "
		SELECT b.id_board, b.name, b.id_profile, b.member_groups
		FROM {$db_prefix}boards AS b
			LEFT JOIN {$db_prefix}moderators AS mods ON (mods.id_board = b.id_board AND mods.id_member = $memID)
		WHERE $user_info[query_see_board]
			AND b.id_profile != 1", __FILE__, __LINE__);
	$context['boards'] = array();
	$context['no_access_boards'] = array();
	while ($row = $smfFunc['db_fetch_assoc']($request))
	{
		if (count(array_intersect($curGroups, explode(',', $row['member_groups']))) === 0)
			$context['no_access_boards'][] = array(
				'id' => $row['id_board'],
				'name' => $row['name'],
				'is_last' => false,
			);

		// Format the name of this profile.
		$profile_name = $context['profiles'][$row['id_profile']]['name'];
		if ($context['profiles'][$row['id_profile']]['parent'])
			$profile_name = sprintf($txt['permissions_profile_' . ($context['profiles'][$row['id_profile']]['parent'] == $row['id_board'] ? 'custom' : 'as_board')], $profile_name);

		$context['boards'][$row['id_board']] = array(
			'id' => $row['id_board'],
			'name' => $row['name'],
			'selected' => $board == $row['id_board'],
			'profile' => $row['id_profile'],
			'profile_name' => $profile_name,
		);
	}
	$smfFunc['db_free_result']($request);

	if (!empty($context['no_access_boards']))
		$context['no_access_boards'][count($context['no_access_boards']) - 1]['is_last'] = true;

	$context['member']['permissions'] = array(
		'general' => array(),
		'board' => array()
	);

	// If you're an admin we know you can do everything, we might as well leave.
	$context['member']['has_all_permissions'] = in_array(1, $curGroups);
	if ($context['member']['has_all_permissions'])
		return;

	$denied = array();

	// Get all general permissions.
	$result = $smfFunc['db_query']('', "
		SELECT p.permission, p.add_deny, mg.group_name, p.id_group
		FROM {$db_prefix}permissions AS p
			LEFT JOIN {$db_prefix}membergroups AS mg ON (mg.id_group = p.id_group)
		WHERE p.id_group IN (" . implode(', ', $curGroups) . ")
		ORDER BY p.add_deny DESC, p.permission, mg.min_posts, CASE WHEN mg.id_group < 4 THEN mg.id_group ELSE 4 END, mg.group_name", __FILE__, __LINE__);
	while ($row = $smfFunc['db_fetch_assoc']($result))
	{
		// We don't know about this permission, it doesn't exist :P.
		if (!isset($txt['permissionname_' . $row['permission']]))
			continue;

		if (empty($row['add_deny']))
			$denied[] = $row['permission'];

		// Permissions that end with _own or _any consist of two parts.
		if (in_array(substr($row['permission'], -4), array('_own', '_any')) && isset($txt['permissionname_' . substr($row['permission'], 0, -4)]))
			$name = $txt['permissionname_' . substr($row['permission'], 0, -4)] . ' - ' . $txt['permissionname_' . $row['permission']];
		else
			$name = $txt['permissionname_' . $row['permission']];

		// Add this permission if it doesn't exist yet.
		if (!isset($context['member']['permissions']['general'][$row['permission']]))
			$context['member']['permissions']['general'][$row['permission']] = array(
				'id' => $row['permission'],
				'groups' => array(
					'allowed' => array(),
					'denied' => array()
				),
				'name' => $name,
				'is_denied' => false,
				'is_global' => true,
			);

		// Add the membergroup to either the denied or the allowed groups.
		$context['member']['permissions']['general'][$row['permission']]['groups'][empty($row['add_deny']) ? 'denied' : 'allowed'][] = $row['id_group'] == 0 ? $txt['membergroups_members'] : $row['group_name'];

		// Once denied is always denied.
		$context['member']['permissions']['general'][$row['permission']]['is_denied'] |= empty($row['add_deny']);
	}
	$smfFunc['db_free_result']($result);

	$request = $smfFunc['db_query']('', "
		SELECT
			bp.add_deny, bp.permission, bp.id_group, mg.group_name" . (empty($board) ? '' : ',
			b.id_profile, CASE WHEN mods.id_member IS NULL THEN 0 ELSE 1 END AS is_moderator') . "
		FROM {$db_prefix}board_permissions AS bp" . (empty($board) ? '' : "
			INNER JOIN {$db_prefix}boards AS b ON (b.id_board = $board)
			LEFT JOIN {$db_prefix}moderators AS mods ON (mods.id_board = b.id_board AND mods.id_member = $memID)") . "
			LEFT JOIN {$db_prefix}membergroups AS mg ON (mg.id_group = bp.id_group)
		WHERE bp.id_profile = " . (empty($board) ? '1' : 'b.id_profile') . "
			AND bp.id_group IN (" . implode(', ', $curGroups) . "" . (empty($board) ? ')' : ", 3)
			AND (mods.id_member IS NOT NULL OR bp.id_group != 3)"), __FILE__, __LINE__);

	while ($row = $smfFunc['db_fetch_assoc']($request))
	{
		// We don't know about this permission, it doesn't exist :P.
		if (!isset($txt['permissionname_' . $row['permission']]))
			continue;

		// The name of the permission using the format 'permission name' - 'own/any topic/event/etc.'.
		if (in_array(substr($row['permission'], -4), array('_own', '_any')) && isset($txt['permissionname_' . substr($row['permission'], 0, -4)]))
			$name = $txt['permissionname_' . substr($row['permission'], 0, -4)] . ' - ' . $txt['permissionname_' . $row['permission']];
		else
			$name = $txt['permissionname_' . $row['permission']];

		// Create the structure for this permission.
		if (!isset($context['member']['permissions']['board'][$row['permission']]))
			$context['member']['permissions']['board'][$row['permission']] = array(
				'id' => $row['permission'],
				'groups' => array(
					'allowed' => array(),
					'denied' => array()
				),
				'name' => $name,
				'is_denied' => false,
				'is_global' => empty($board),
			);

		$context['member']['permissions']['board'][$row['permission']]['groups'][empty($row['add_deny']) ? 'denied' : 'allowed'][$row['id_group']] = $row['id_group'] == 0 ? $txt['membergroups_members'] : $row['group_name'];

		$context['member']['permissions']['board'][$row['permission']]['is_denied'] |= empty($row['add_deny']);
	}
	$smfFunc['db_free_result']($request);
}

function account($memID)
{
	global $context, $settings, $user_profile, $txt, $db_prefix;
	global $scripturl, $member_groups, $modSettings, $language, $user_info;
	global $smfFunc;

	// Allow an administrator to edit the username?
	$context['allow_edit_username'] = isset($_GET['changeusername']) && allowedTo('admin_forum');

	// You might be allowed to only assign the membergroups, so let's check.
	$context['allow_edit_membergroups'] = allowedTo('manage_membergroups');
	$context['allow_edit_account'] = ($context['user']['is_owner'] && allowedTo('profile_identity_own')) || allowedTo('profile_identity_any');

	// How about their email address... online status, and name?
	$context['allow_hide_email'] = !empty($modSettings['allow_hide_email']) || allowedTo('moderate_forum');
	$context['allow_hide_online'] = !empty($modSettings['allow_hideOnline']) || allowedTo('moderate_forum');
	$context['allow_edit_name'] = !empty($modSettings['allow_editDisplayName']) || allowedTo('moderate_forum');

	// Load up the existing contextual data.
	$context['member'] += array(
		'is_admin' => !empty($user_profile[$memID]['id_group']) && $user_profile[$memID]['id_group'] == 1,
		'secret_question' => !isset($user_profile[$memID]['secret_question']) ? '' : $user_profile[$memID]['secret_question'],
	);

	// You need 'Manage Membergroups' permission for this.
	if ($context['allow_edit_membergroups'])
	{
		$context['member_groups'] = array(
			0 => array(
				'id' => 0,
				'name' => &$txt['no_primary_membergroup'],
				'is_primary' => $user_profile[$memID]['id_group'] == 0,
				'can_be_additional' => false,
				'can_be_primary' => true,
			)
		);
		$curGroups = explode(',', $user_profile[$memID]['additional_groups']);

		// Load membergroups, but only those groups the user can assign.
		$request = $smfFunc['db_query']('', "
			SELECT group_name, id_group, hidden
			FROM {$db_prefix}membergroups
			WHERE id_group != 3
				AND min_posts = -1
			ORDER BY min_posts, CASE WHEN id_group < 4 THEN id_group ELSE 4 END, group_name", __FILE__, __LINE__);
		while ($row = $smfFunc['db_fetch_assoc']($request))
		{
			// We should skip the administrator group if they don't have the admin_forum permission!
			if ($row['id_group'] == 1 && !allowedTo('admin_forum'))
				continue;

			$context['member_groups'][$row['id_group']] = array(
				'id' => $row['id_group'],
				'name' => $row['group_name'],
				'is_primary' => $user_profile[$memID]['id_group'] == $row['id_group'],
				'is_additional' => in_array($row['id_group'], $curGroups),
				'can_be_additional' => true,
				'can_be_primary' => $row['hidden'] != 2,
			);
		}
		$smfFunc['db_free_result']($request);
	}

	// Are languages user selectable?  If so, get a list.
	$context['languages'] = array();
	if ($context['allow_edit_account'] && !empty($modSettings['userLanguage']))
	{
		// Select the default language if the user has no language selected yet.
		$selectedLanguage = empty($user_profile[$memID]['lngfile']) ? $language : $user_profile[$memID]['lngfile'];

		$language_directories = array(
			$settings['default_theme_dir'] . '/languages',
			$settings['actual_theme_dir'] . '/languages',
		);
		if (!empty($settings['base_theme_dir']))
			$language_directories[] = $settings['base_theme_dir'] . '/languages';
		$language_directories = array_unique($language_directories);

		foreach ($language_directories as $language_dir)
		{
			if (!file_exists($language_dir))
				continue;

			$dir = dir($language_dir);
			while ($entry = $dir->read())
			{
				// Each language file must *at least* have a 'index.LANGUAGENAME.php' file.
				if (preg_match('~^index\.(.+)\.php$~', $entry, $matches) == 0)
					continue;

				$context['languages'][$matches[1]] = array(
					'name' => $smfFunc['ucwords'](strtr($matches[1], array('_' => ' ', '-utf8' => ''))),
					'selected' => $selectedLanguage == $matches[1],
					'filename' => $matches[1],
				);
			}
			$dir->close();
		}
	}

	loadThemeOptions($memID);
	loadCustomFields($memID, 'account');
}

function forumProfile($memID)
{
	global $context, $user_profile, $user_info, $txt, $modSettings;

	$context['avatar_url'] = $modSettings['avatar_url'];
	$context['allow_edit_title'] = allowedTo('profile_title_any') || (allowedTo('profile_title_own') && $context['user']['is_owner']);

	// Signature limits.
	list ($sig_limits, $sig_bbc) = explode(':', $modSettings['signature_settings']);
	$sig_limits = explode(',', $sig_limits);

	$context['signature_enabled'] = isset($sig_limits[0]) ? $sig_limits[0] : 0;
	$context['signature_limits'] = array(
		'max_length' => isset($sig_limits[1]) ? $sig_limits[1] : 0,
		'max_lines' => isset($sig_limits[2]) ? $sig_limits[2] : 0,
		'max_images' => isset($sig_limits[3]) ? $sig_limits[3] : 0,
		'max_smileys' => isset($sig_limits[4]) ? $sig_limits[4] : 0,
		'max_image_width' => isset($sig_limits[5]) ? $sig_limits[5] : 0,
		'max_image_height' => isset($sig_limits[6]) ? $sig_limits[6] : 0,
		'max_font_size' => isset($sig_limits[7]) ? $sig_limits[7] : 0,
		'bbc' => !empty($sig_bbc) ? explode(',', $sig_bbc) : array(),
	);
	// Kept this line in for backwards compatibility!
	$context['max_signature_length'] = $context['signature_limits']['max_length'];
	// Warning message for signature image limits?
	$context['signature_warning'] = '';
	if ($context['signature_limits']['max_image_width'] && $context['signature_limits']['max_image_height'])
		$context['signature_warning'] = sprintf($txt['profile_error_signature_max_image_size'], $context['signature_limits']['max_image_width'], $context['signature_limits']['max_image_height']);
	elseif ($context['signature_limits']['max_image_width'] || $context['signature_limits']['max_image_height'])
		$context['signature_warning'] = sprintf($txt['profile_error_signature_max_image_' . ($context['signature_limits']['max_image_width'] ? 'width' : 'height')], $context['signature_limits'][$context['signature_limits']['max_image_width'] ? 'max_image_width' : 'max_image_height']);

	$context['show_spellchecking'] = !empty($modSettings['enableSpellChecking']) && function_exists('pspell_new');

	$context['member'] += array(
		'birth_date' => empty($user_profile[$memID]['birthdate']) || $user_profile[$memID]['birthdate'] === '0001-01-01' ? '0000-00-00' : (substr($user_profile[$memID]['birthdate'], 0, 4) === '0004' ? '0000' . substr($user_profile[$memID]['birthdate'], 4) : $user_profile[$memID]['birthdate']),
		'location' => !isset($user_profile[$memID]['location']) ? '' : $user_profile[$memID]['location'],
		'title' => !isset($user_profile[$memID]['usertitle']) || $user_profile[$memID]['usertitle'] == '' ? '' : $user_profile[$memID]['usertitle'],
		'blurb' => !isset($user_profile[$memID]['personal_text']) ? '' : str_replace(array('<', '>', '&amp;#039;'), array('&lt;', '&gt;', '&#039;'), $user_profile[$memID]['personal_text']),
		'signature' => !isset($user_profile[$memID]['signature']) ? '' : str_replace(array('<br />', '<', '>', '"', '\''), array("\n", '&lt;', '&gt;', '&quot;', '&#039;'), $user_profile[$memID]['signature']),
	);

	// Split up the birthdate....
	list ($uyear, $umonth, $uday) = explode('-', $context['member']['birth_date']);
	$context['member']['birth_date'] = array(
		'year' => $uyear,
		'month' => $umonth,
		'day' => $uday
	);

	if ($user_profile[$memID]['avatar'] == '' && $user_profile[$memID]['id_attach'] > 0 && $context['member']['avatar']['allow_upload'])
		$context['member']['avatar'] += array(
			'choice' => 'upload',
			'server_pic' => 'blank.gif',
			'external' => 'http://'
		);
	elseif (stristr($user_profile[$memID]['avatar'], 'http://') && $context['member']['avatar']['allow_external'])
		$context['member']['avatar'] += array(
			'choice' => 'external',
			'server_pic' => 'blank.gif',
			'external' => $user_profile[$memID]['avatar']
		);
	elseif (file_exists($modSettings['avatar_directory'] . '/' . $user_profile[$memID]['avatar']) && $context['member']['avatar']['allow_server_stored'])
		$context['member']['avatar'] += array(
			'choice' => 'server_stored',
			'server_pic' => $user_profile[$memID]['avatar'] == '' ? 'blank.gif' : $user_profile[$memID]['avatar'],
			'external' => 'http://'
		);
	else
		$context['member']['avatar'] += array(
			'choice' => 'server_stored',
			'server_pic' => 'blank.gif',
			'external' => 'http://'
		);

	// Get a list of all the avatars.
	if ($context['member']['avatar']['allow_server_stored'])
	{
		$context['avatar_list'] = array();
		$context['avatars'] = is_dir($modSettings['avatar_directory']) ? getAvatars('', 0) : array();
	}
	else
		$context['avatars'] = array();

	// Second level selected avatar...
	$context['avatar_selected'] = substr(strrchr($context['member']['avatar']['server_pic'], '/'), 1);

	loadThemeOptions($memID);
	loadCustomFields($memID, 'forumProfile');
}

// Recursive function to retrieve avatar files
function getAvatars($directory, $level)
{
	global $context, $txt, $modSettings;

	$result = array();

	// Open the directory..
	$dir = dir($modSettings['avatar_directory'] . (!empty($directory) ? '/' : '') . $directory);
	$dirs = array();
	$files = array();

	if (!$dir)
		return array();

	while ($line = $dir->read())
	{
		if (in_array($line, array('.', '..', 'blank.gif', 'index.php')))
			continue;

		if (is_dir($modSettings['avatar_directory'] . '/' . $directory . (!empty($directory) ? '/' : '') . $line))
			$dirs[] = $line;
		else
			$files[] = $line;
	}
	$dir->close();

	// Sort the results...
	natcasesort($dirs);
	natcasesort($files);

	if ($level == 0)
	{
		$result[] = array(
			'filename' => 'blank.gif',
			'checked' => in_array($context['member']['avatar']['server_pic'], array('', 'blank.gif')),
			'name' => &$txt['no_pic'],
			'is_dir' => false
		);
	}

	foreach ($dirs as $line)
	{
		$tmp = getAvatars($directory . (!empty($directory) ? '/' : '') . $line, $level + 1);
		if (!empty($tmp))
			$result[] = array(
				'filename' => htmlspecialchars($line),
				'checked' => strpos($context['member']['avatar']['server_pic'], $line . '/') !== false,
				'name' => '[' . htmlspecialchars(str_replace('_', ' ', $line)) . ']',
				'is_dir' => true,
				'files' => $tmp
		);
		unset($tmp);
	}

	foreach ($files as $line)
	{
		$filename = substr($line, 0, (strlen($line) - strlen(strrchr($line, '.'))));
		$extension = substr(strrchr($line, '.'), 1);

		// Make sure it is an image.
		if (strcasecmp($extension, 'gif') != 0 && strcasecmp($extension, 'jpg') != 0 && strcasecmp($extension, 'jpeg') != 0 && strcasecmp($extension, 'png') != 0 && strcasecmp($extension, 'bmp') != 0)
			continue;

		$result[] = array(
			'filename' => htmlspecialchars($line),
			'checked' => $line == $context['member']['avatar']['server_pic'],
			'name' => htmlspecialchars(str_replace('_', ' ', $filename)),
			'is_dir' => false
		);
		if ($level == 1)
			$context['avatar_list'][] = $directory . '/' . $line;
	}

	return $result;
}

function theme($memID)
{
	global $txt, $context, $user_profile, $db_prefix, $modSettings, $settings, $user_info, $smfFunc;

	$request = $smfFunc['db_query']('', "
		SELECT value
		FROM {$db_prefix}themes
		WHERE id_theme = " . (int) $user_profile[$memID]['id_theme'] . "
			AND variable = 'name'
		LIMIT 1", __FILE__, __LINE__);
	list ($name) = $smfFunc['db_fetch_row']($request);
	$smfFunc['db_free_result']($request);

	$context['member'] += array(
		'theme' => array(
			'id' => $user_profile[$memID]['id_theme'],
			'name' => empty($user_profile[$memID]['id_theme']) ? $txt['theme_forum_default'] : $name
		),
		'smiley_set' => array(
			'id' => empty($user_profile[$memID]['smiley_set']) ? '' : $user_profile[$memID]['smiley_set']
		),
		'time_format' => !isset($user_profile[$memID]['time_format']) ? '' : $user_profile[$memID]['time_format'],
		'time_offset' => empty($user_profile[$memID]['time_offset']) ? '0' : $user_profile[$memID]['time_offset'],
	);

	$context['easy_timeformats'] = array(
		array('format' => '', 'title' => $txt['timeformat_default']),
		array('format' => '%B %d, %Y, %I:%M:%S %p', 'title' => $txt['timeformat_easy1']),
		array('format' => '%B %d, %Y, %H:%M:%S', 'title' => $txt['timeformat_easy2']),
		array('format' => '%Y-%m-%d, %H:%M:%S', 'title' => $txt['timeformat_easy3']),
		array('format' => '%d %B %Y, %H:%M:%S', 'title' => $txt['timeformat_easy4']),
		array('format' => '%d-%m-%Y, %H:%M:%S', 'title' => $txt['timeformat_easy5'])
	);

	$context['current_forum_time'] = timeformat(time() - $user_info['time_offset'] * 3600, false);
	$context['current_forum_time_hour'] = (int) strftime('%H', forum_time(false));

	$context['smiley_sets'] = explode(',', 'none,,' . $modSettings['smiley_sets_known']);
	$set_names = explode("\n", $txt['smileys_none'] . "\n" . $txt['smileys_forum_board_default'] . "\n" . $modSettings['smiley_sets_names']);
	foreach ($context['smiley_sets'] as $i => $set)
	{
		$context['smiley_sets'][$i] = array(
			'id' => $set,
			'name' => $set_names[$i],
			'selected' => $set == $context['member']['smiley_set']['id']
		);

		if ($context['smiley_sets'][$i]['selected'])
			$context['member']['smiley_set']['name'] = $set_names[$i];
	}

	loadThemeOptions($memID);
	loadCustomFields($memID, 'theme');

	loadLanguage('Settings');
}

// Display the notifications and settings for changes.
function notification($memID)
{
	global $txt, $db_prefix, $scripturl, $user_profile, $user_info, $context, $modSettings, $smfFunc;

	// All the boards with notification on..
	$request = $smfFunc['db_query']('', "
		SELECT b.id_board, b.name, IFNULL(lb.id_msg, 0) AS board_read, b.id_msg_updated
		FROM {$db_prefix}log_notify AS ln
			INNER JOIN {$db_prefix}boards AS b ON (b.id_board = ln.id_board)
			LEFT JOIN {$db_prefix}log_boards AS lb ON (lb.id_board = b.id_board AND lb.id_member = $user_info[id])
		WHERE ln.id_member = $memID
			AND $user_info[query_see_board]
		ORDER BY b.board_order", __FILE__, __LINE__);
	$context['board_notifications'] = array();
	while ($row = $smfFunc['db_fetch_assoc']($request))
		$context['board_notifications'][] = array(
			'id' => $row['id_board'],
			'name' => $row['name'],
			'href' => $scripturl . '?board=' . $row['id_board'] . '.0',
			'link' => '<a href="' . $scripturl . '?board=' . $row['id_board'] . '.0">' . $row['name'] . '</a>',
			'new' => $row['board_read'] < $row['id_msg_updated']
		);
	$smfFunc['db_free_result']($request);

	$request = $smfFunc['db_query']('', "
		SELECT COUNT(*)
		FROM {$db_prefix}log_notify AS ln
			INNER JOIN {$db_prefix}topics AS t ON (t.id_topic = ln.id_topic)
			INNER JOIN {$db_prefix}boards AS b ON (b.id_board = t.id_board)
		WHERE ln.id_member = $memID
			AND $user_info[query_see_board]
			AND t.approved = 1", __FILE__, __LINE__);
	list ($context['num_topic_notifications']) = $smfFunc['db_fetch_row']($request);
	$smfFunc['db_free_result']($request);

	$context['page_index'] = constructPageIndex($scripturl . '?action=profile;u=' . $memID . ';sa=notification', $_REQUEST['start'], $context['num_topic_notifications'], $modSettings['defaultMaxMessages']);

	// All the topics with notification on...
	$request = $smfFunc['db_query']('', "
		SELECT
			IFNULL(lt.id_msg, IFNULL(lmr.id_msg, -1)) + 1 AS new_from, b.id_board, b.name,
			t.id_topic, ms.subject, ms.id_member, IFNULL(mem.real_name, ms.poster_name) AS real_name,
			ml.id_msg_modified, ml.poster_time, ml.id_member AS id_member_updated,
			IFNULL(mem2.real_name, ml.poster_name) AS last_real_name
		FROM {$db_prefix}log_notify AS ln
			INNER JOIN {$db_prefix}topics AS t ON (t.id_topic = ln.id_topic AND t.approved = 1)
			INNER JOIN {$db_prefix}boards AS b ON (b.id_board = t.id_board AND $user_info[query_see_board])
			INNER JOIN {$db_prefix}messages AS ms ON (ms.id_msg = t.id_first_msg)
			INNER JOIN {$db_prefix}messages AS ml ON (ml.id_msg = t.id_last_msg)
			LEFT JOIN {$db_prefix}members AS mem ON (mem.id_member = ms.id_member)
			LEFT JOIN {$db_prefix}members AS mem2 ON (mem2.id_member = ml.id_member)
			LEFT JOIN {$db_prefix}log_topics AS lt ON (lt.id_topic = t.id_topic AND lt.id_member = $user_info[id])
			LEFT JOIN {$db_prefix}log_mark_read AS lmr ON (lmr.id_board = b.id_board AND lmr.id_member = $user_info[id])
		WHERE ln.id_member = $memID
		ORDER BY ms.id_msg DESC
		LIMIT $_REQUEST[start], $modSettings[defaultMaxMessages]", __FILE__, __LINE__);
	$context['topic_notifications'] = array();
	while ($row = $smfFunc['db_fetch_assoc']($request))
	{
		censorText($row['subject']);

		$context['topic_notifications'][] = array(
			'id' => $row['id_topic'],
			'poster' => array(
				'id' => $row['id_member'],
				'name' => $row['real_name'],
				'href' => empty($row['id_member']) ? '' : $scripturl . '?action=profile;u=' . $row['id_member'],
				'link' => empty($row['id_member']) ? $row['real_name'] : '<a href="' . $scripturl . '?action=profile;u=' . $row['id_member'] . '">' . $row['real_name'] . '</a>'
			),
			'poster_updated' => array(
				'id' => $row['id_member_updated'],
				'name' => $row['last_real_name'],
				'href' => empty($row['id_member_updated']) ? '' : $scripturl . '?action=profile;u=' . $row['id_member_updated'],
				'link' => empty($row['id_member_updated']) ? $row['last_real_name'] : '<a href="' . $scripturl . '?action=profile;u=' . $row['id_member_updated'] . '">' . $row['last_real_name'] . '</a>'
			),
			'subject' => $row['subject'],
			'href' => $scripturl . '?topic=' . $row['id_topic'] . '.0',
			'link' => '<a href="' . $scripturl . '?topic=' . $row['id_topic'] . '.0">' . $row['subject'] . '</a>',
			'new' => $row['new_from'] <= $row['id_msg_modified'],
			'new_from' => $row['new_from'],
			'updated' => timeformat($row['poster_time']),
			'new_href' => $scripturl . '?topic=' . $row['id_topic'] . '.msg' . $row['new_from'] . '#new',
			'new_link' => '<a href="' . $scripturl . '?topic=' . $row['id_topic'] . '.msg' . $row['new_from'] . '#new">' . $row['subject'] . '</a>',
			'board' => array(
				'id' => $row['id_board'],
				'name' => $row['name'],
				'href' => $scripturl . '?board=' . $row['id_board'] . '.0',
				'link' => '<a href="' . $scripturl . '?board=' . $row['id_board'] . '.0">' . $row['name'] . '</a>'
			)
		);
	}
	$smfFunc['db_free_result']($request);

	// What options are set?
	$context['member'] += array(
		'notify_announcements' => $user_profile[$memID]['notify_announcements'],
		'notify_send_body' => $user_profile[$memID]['notify_send_body'],
		'notify_types' => $user_profile[$memID]['notify_types'],
		'notify_regularity' => $user_profile[$memID]['notify_regularity'],
	);

	// How many rows can we expect?
	$context['num_rows'] = array(
		'topic' => count($context['topic_notifications']) + 3,
		'board' => count($context['board_notifications']) + 2
	);

	loadThemeOptions($memID);
}

// Function to allow the user to choose group membership etc...
function groupMembership($memID)
{
	global $txt, $db_prefix, $scripturl, $user_profile, $user_info, $context, $modSettings, $smfFunc;

	$curMember = $user_profile[$memID];
	$context['primary_group'] = $curMember['id_group'];

	// Can they manage groups?
	$context['can_manage_membergroups'] = allowedTo('manage_membergroups');
	$context['can_edit_primary'] = allowedTo('manage_membergroups');
	$context['update_message'] = isset($_GET['msg']) && isset($txt['group_membership_msg_' . $_GET['msg']]) ? $txt['group_membership_msg_' . $_GET['msg']] : '';

	// Get all the groups this user is a member of.
	$groups = explode(',', $curMember['additional_groups']);
	$groups[] = $curMember['id_group'];

	// Ensure the query doesn't croak!
	if (empty($groups))
		$groups = array(0);
	// Just to be sure...
	foreach ($groups as $k => $v)
		$groups[$k] = (int) $v;

	// Get all the membergroups they can join.
	$request = $smfFunc['db_query']('', "
		SELECT mg.id_group, mg.group_name, mg.description, mg.group_type, mg.online_color, mg.hidden,
			IFNULL(lgr.id_member, 0) AS pending
		FROM {$db_prefix}membergroups AS mg
			LEFT JOIN {$db_prefix}log_group_requests AS lgr ON (lgr.id_member = $memID AND lgr.id_group = mg.id_group)
		WHERE (mg.id_group IN (" . implode(',', $groups) . ")
			OR mg.group_type > 0)
			AND mg.min_posts = -1
			AND mg.id_group != 3
		ORDER BY group_name", __FILE__, __LINE__);
	// This beast will be our group holder.
	$context['groups'] = array(
		'member' => array(),
		'available' => array()
	);
	while ($row = $smfFunc['db_fetch_assoc']($request))
	{
		// Can they edit their primary group?
		if ($row['id_group'] == $context['primary_group'] && $row['group_type'] != 0)
			$context['can_edit_primary'] = true;

		// If they can't manage groups, and it's not publically joinable or already assigned, they can't see it.
		if (!$context['can_manage_membergroups'] && $row['group_type'] == 0 && $row['id_group'] != $context['primary_group'])
			continue;

		$context['groups'][in_array($row['id_group'], $groups) ? 'member' : 'available'][$row['id_group']] = array(
			'id' => $row['id_group'],
			'name' => $row['group_name'],
			'desc' => $row['description'],
			'color' => $row['online_color'],
			'type' => $row['group_type'],
			'pending' => $row['pending'],
			'is_primary' => $row['id_group'] == $context['primary_group'],
			'can_be_primary' => $row['hidden'] != 2,
			// Anything more than this needs to be done through account settings for security.
			'can_leave' => $row['id_group'] != 1 && $row['group_type'] != 0 ? true : false,
		);
	}
	$smfFunc['db_free_result']($request);

	// Add registered members on the end.
	$context['groups']['member'][0] = array(
		'id' => 0,
		'name' => $txt['regular_members'],
		'desc' => $txt['regular_members_desc'],
		'type' => 0,
		'is_primary' => $context['primary_group'] == 0 ? true : false,
		'can_be_primary' => true,
		'can_leave' => 0,
	);

	// In the special case that someone is requesting membership of a group, setup some special context vars.
	if (isset($_REQUEST['request']) && isset($context['groups']['available'][(int) $_REQUEST['request']]) && $context['groups']['available'][(int) $_REQUEST['request']]['type'] == 1)
		$context['group_request'] = $context['groups']['available'][(int) $_REQUEST['request']];
}

// This function actually makes all the group changes...
function groupMembership2($profile_vars, $post_errors, $memID)
{
	global $user_info, $sourcedir, $context, $db_prefix, $user_profile, $modSettings, $txt, $smfFunc;

	// Let's be extra cautious...
	if (!$context['user']['is_owner'] || empty($modSettings['show_group_membership']))
		isAllowedTo('manage_membergroups');
	if (!isset($_REQUEST['gid']) && !isset($_POST['primary']))
		fatal_lang_error('no_access');

	checkSession(isset($_GET['gid']) ? 'get' : 'post');

	$old_profile = &$user_profile[$memID];
	$context['can_manage_membergroups'] = allowedTo('manage_membergroups');

	// By default the new primary is the old one.
	$newPrimary = $old_profile['id_group'];
	$addGroups = array_flip(explode(',', $old_profile['additional_groups']));
	$canChangePrimary = $old_profile['id_group'] == 0 ? 1 : 0;
	$changeType = isset($_POST['primary']) ? 'primary' : (isset($_POST['req']) ? 'request' : 'free');

	// One way or another, we have a target group in mind...
	$group_id = isset($_REQUEST['gid']) ? (int) $_REQUEST['gid'] : (int) $_POST['primary'];
	$foundTarget = $changeType == 'primary' && $group_id == 0 ? true : false;

	// Sanity check!!
	if ($group_id == 1)
		isAllowedTo('admin_forum');

	// What ever we are doing, we need to determine if changing primary is possible!
	$request = $smfFunc['db_query']('', "
		SELECT id_group, group_type, hidden, group_name
		FROM {$db_prefix}membergroups
		WHERE id_group IN ($group_id, $old_profile[id_group])", __FILE__, __LINE__);
	while ($row = $smfFunc['db_fetch_assoc']($request))
	{
		// Is this the new group?
		if ($row['id_group'] == $group_id)
		{
			$foundTarget = true;
			$group_name = $row['group_name'];

			// Does the group type match what we're doing?
			if (($changeType == 'request' && $row['group_type'] != 1) || ($changeType == 'free' && $row['group_type'] != 2))
				fatal_lang_error('no_access');

			// We can't change the primary group if this is hidden!
			if ($row['hidden'] == 2)
				$canChangePrimary = false;
		}

		// If this is their old primary, can we change it?
		if ($row['id_group'] == $old_profile['id_group'] && ($row['group_type'] != 0 || $context['can_manage_membergroups']) && $canChangePrimary !== false)
			$canChangePrimary = 1;

		// If we are not doing a force primary move, don't do it automatically if current primary is not 0.
		if ($changeType != 'primary' && $old_profile['id_group'] != 0)
			$canChangePrimary = false;

		// If this is the one we are acting on, can we even act?
		if (!$context['can_manage_membergroups'] && $row['group_type'] == 0)
			fatal_lang_error('no_access');
	}
	$smfFunc['db_free_result']($request);

	// Didn't find the target?
	if (!$foundTarget)
		fatal_lang_error('no_access');

	// Final security check, don't allow users to promote themselves to admin.
	if ($context['can_manage_membergroups'] && !allowedTo('admin_forum'))
	{
		$request = $smfFunc['db_query']('', "
			SELECT COUNT(permission)
			FROM {$db_prefix}permissions
			WHERE id_group = $group_id
				AND permission = 'admin_forum'
				AND add_deny = 1", __FILE__, __LINE__);
		list ($disallow) = $smfFunc['db_fetch_row']($request);
		$smfFunc['db_free_result']($request);

		if ($disallow)
			isAllowedTo('admin_forum');
	}

	// If we're requesting, add the note then return.
	if ($changeType == 'request')
	{
		$reason = htmlspecialchars($_POST['reason']);

		$request = $smfFunc['db_query']('', "
			SELECT id_member
			FROM {$db_prefix}log_group_requests
			WHERE id_member = $memID
				AND id_group = $group_id", __FILE__, __LINE__);
		if ($smfFunc['db_num_rows']($request) != 0)
			fatal_lang_error('profile_error_already_requested_group');
		$smfFunc['db_free_result']($request);

		// Log the request.
		$smfFunc['db_query']('', "
			INSERT INTO {$db_prefix}log_group_requests
				(id_member, id_group, time_applied, reason)
			VALUES
				($memID, $group_id, " . time() . ", '$reason')", __FILE__, __LINE__);

		// Send an email to all group moderators etc.
		require_once($sourcedir . '/Subs-Post.php');

		// Do we have any group moderators?
		$request = $smfFunc['db_query']('', "
			SELECT id_member
			FROM {$db_prefix}group_moderators
			WHERE id_group = $group_id", __FILE__, __LINE__);
		$moderators = array();
		while ($row = $smfFunc['db_fetch_assoc']($request))
			$moderators[] = $row['id_member'];
		$smfFunc['db_free_result']($request);

		// Otherwise this is the backup!
		if (empty($moderators))
		{
			require_once($sourcedir . '/Subs-Members.php');
			$moderators = membersAllowedTo('manage_membergroups');
		}

		if (!empty($moderators))
		{
			$request = $smfFunc['db_query']('', "
				SELECT id_member, email_address, lngfile, member_name
				FROM {$db_prefix}members
				WHERE id_member IN (" . implode(', ', $moderators) . ")
					AND notify_types != 4
				ORDER BY lngfile", __FILE__, __LINE__);
			$lastLng = $user_info['language'];
			while ($row = $smfFunc['db_fetch_assoc']($request))
			{
				// Do we need to change the language we're sending in?
				if ($lastLng != $row['lngfile'])
				{
					$lastLng = $row['lngfile'];
					loadLanguage('Profile', $row['lngfile'], false);
				}
				sendmail($row['email_address'], $txt['request_membership_email_subject'], sprintf($txt['request_membership_email_subject'], $row['member_name'], $old_profile['member_name'], $group_name, $reason));
			}
			$smfFunc['db_free_result']($request);
		}

		return $changeType;
	}
	// Otherwise we are leaving/joining a group.
	elseif ($changeType == 'free')
	{
		// Are we leaving?
		if ($old_profile['id_group'] == $group_id || isset($addGroups[$group_id]))
		{
			if ($old_profile['id_group'] == $group_id)
				$newPrimary = 0;
			else
				unset($addGroups[$group_id]);
		}
		// ... if not, must be joining.
		else
		{
			// Can we change the primary, and do we want to?
			if ($canChangePrimary)
			{
				if ($old_profile['id_group'] != 0)
					$addGroups[$old_profile['id_group']] = -1;
				$newPrimary = $group_id;
			}
			// Otherwise it's an additional group...
			else
				$addGroups[$group_id] = -1;
		}
	}
	// Finally, we must be setting the primary.
	elseif ($canChangePrimary)
	{
		if ($old_profile['id_group'] != 0)
			$addGroups[$old_profile['id_group']] = -1;
		if (isset($addGroups[$group_id]))
			unset($addGroups[$group_id]);
		$newPrimary = $group_id;
	}

	// Finally, we can make the changes!
	$addGroups = implode(',', array_flip($addGroups));

	// Ensure that we don't cache permissions if the group is changing.
	if ($context['user']['is_owner'])
		$_SESSION['mc']['time'] = 0;
	else
		updateSettings(array('settings_updated' => time()));

	updateMemberData($memID, array('id_group' => $newPrimary, 'additional_groups' => "'$addGroups'"));

	return $changeType;
}

// Present a screen to make sure the user wants to be deleted
function deleteAccount($memID)
{
	global $txt, $context, $user_info, $modSettings, $user_profile, $smfFunc;

	if (!$context['user']['is_owner'])
		isAllowedTo('profile_remove_any');
	elseif (!allowedTo('profile_remove_any'))
		isAllowedTo('profile_remove_own');

	// Permissions for removing stuff...
	$context['can_delete_posts'] = !$context['user']['is_owner'] && allowedto('moderate_forum');

	// Can they do this, or will they need approval?
	$context['needs_approval'] = $context['user']['is_owner'] && !empty($modSettings['approveAccountDeletion']) && !allowedTo('moderate_forum');
	$context['page_title'] = $txt['deleteAccount'] . ': ' . $user_profile[$memID]['real_name'];
}

function deleteAccount2($profile_vars, $post_errors, $memID)
{
	global $user_info, $sourcedir, $context, $db_prefix, $user_profile, $modSettings, $smfFunc;

	// !!! Add a way to delete pms as well?

	if (!$context['user']['is_owner'])
		isAllowedTo('profile_remove_any');
	elseif (!allowedTo('profile_remove_any'))
		isAllowedTo('profile_remove_own');

	checkSession();

	$old_profile = &$user_profile[$memID];

	// Too often, people remove/delete their own only account.
	if (in_array(1, explode(',', $old_profile['additional_groups'])) || $old_profile['id_group'] == 1)
	{
		// Are you allowed to administrate the forum, as they are?
		isAllowedTo('admin_forum');

		$request = $smfFunc['db_query']('', "
			SELECT id_member
			FROM {$db_prefix}members
			WHERE (id_group = 1 OR FIND_IN_SET(1, additional_groups))
				AND id_member != $memID
			LIMIT 1", __FILE__, __LINE__);
		list ($another) = $smfFunc['db_fetch_row']($request);
		$smfFunc['db_free_result']($request);

		if (empty($another))
			fatal_lang_error('at_least_one_admin', 'critical');
	}

	// This file is needed for the deleteMembers function.
	require_once($sourcedir . '/Subs-Members.php');

	// Do you have permission to delete others profiles, or is that your profile you wanna delete?
	if ($memID != $user_info['id'])
	{
		isAllowedTo('profile_remove_any');

		// Now, have you been naughty and need your posts deleting?
		// !!! Should this check board permissions?
		if ($_POST['remove_type'] != 'none' && allowedTo('moderate_forum'))
		{
			// Include RemoveTopics - essential for this type of work!
			require_once($sourcedir . '/RemoveTopic.php');

			// First off we delete any topics the member has started - if they wanted topics being done.
			if ($_POST['remove_type'] == 'topics')
			{
				// Fetch all topics started by this user within the time period.
				$request = $smfFunc['db_query']('', "
					SELECT t.id_topic
					FROM {$db_prefix}topics AS t
					WHERE t.id_member_started = $memID", __FILE__, __LINE__);
				$topicIDs = array();
				while ($row = $smfFunc['db_fetch_assoc']($request))
					$topicIDs[] = $row['id_topic'];
				$smfFunc['db_free_result']($request);

				// Actually remove the topics.
				// !!! This needs to check permissions, but we'll let it slide for now because of moderate_forum already being had.
				removeTopics($topicIDs);
			}

			// Now delete the remaining messages.
			$request = $smfFunc['db_query']('', "
				SELECT m.id_msg
				FROM {$db_prefix}messages AS m
					INNER JOIN {$db_prefix}topics AS t ON (t.id_topic = m.id_topic
						AND t.id_first_msg != m.id_msg)
				WHERE m.id_member = $memID", __FILE__, __LINE__);
			// This could take a while... but ya know it's gonna be worth it in the end.
			while ($row = $smfFunc['db_fetch_assoc']($request))
				removeMessage($row['id_msg']);
			$smfFunc['db_free_result']($request);
		}

		// Only delete this poor members account if they are actually being booted out of camp.
		if (isset($_POST['deleteAccount']))
			deleteMembers($memID);
	}
	// Do they need approval to delete?
	elseif (empty($post_errors) && !empty($modSettings['approveAccountDeletion']) && !allowedTo('moderate_forum'))
	{
		// Setup their account for deletion ;)
		updateMemberData($memID, array('is_activated' => 4));
		// Another account needs approval...
		updateSettings(array('unapprovedMembers' => true), true);
	}
	// Also check if you typed your password correctly.
	elseif (empty($post_errors))
		deleteMembers($memID);
}

// This function 'remembers' the profile changes a user made after erronious input.
function rememberPostData()
{
	global $context, $scripturl, $txt, $modSettings, $user_profile, $user_info, $smfFunc;

	// Overwrite member settings with the ones you selected.
	$context['member'] = array(
		'is_owner' => $_REQUEST['userID'] == $user_info['id'],
		'username' => $user_profile[$_REQUEST['userID']]['member_name'],
		'name' => !isset($_POST['real_name']) || $_POST['real_name'] == '' ? $user_profile[$_REQUEST['userID']]['member_name'] : $smfFunc['db_unescape_string']($_POST['real_name']),
		'id' => (int) $_REQUEST['userID'],
		'title' => !isset($_POST['usertitle']) || $_POST['usertitle'] == '' ? '' : $smfFunc['db_unescape_string']($_POST['usertitle']),
		'email' => isset($_POST['email_address']) ? $_POST['email_address'] : '',
		'hide_email' => empty($_POST['hide_email']) ? 0 : 1,
		'show_online' => empty($_POST['show_online']) ? 0 : 1,
		'registered' => empty($_POST['date_registered']) || $_POST['date_registered'] == '0001-01-01' ? $txt['not_applicable'] : strftime('%Y-%m-%d', $_POST['date_registered']),
		'blurb' => !isset($_POST['personal_text']) ? '' : str_replace(array('<', '>', '&amp;#039;'), array('&lt;', '&gt;', '&#039;'), $smfFunc['db_unescape_string']($_POST['personal_text'])),
		'gender' => array(
			'name' => empty($_POST['gender']) ? '' : ($_POST['gender'] == 2 ? 'f' : 'm')
		),
		'website' => array(
			'title' => !isset($_POST['website_title']) ? '' : $smfFunc['db_unescape_string']($_POST['website_title']),
			'url' => !isset($_POST['website_url']) ? '' : $smfFunc['db_unescape_string']($_POST['website_url']),
		),
		'birth_date' => array(
			'month' => empty($_POST['bday1']) ? '00' : (int) $_POST['bday1'],
			'day' => empty($_POST['bday2']) ? '00' : (int) $_POST['bday2'],
			'year' => empty($_POST['bday3']) ? '0000' : (int) $_POST['bday3']
		),
		'signature' => !isset($_POST['signature']) ? '' : str_replace(array('<', '>'), array('&lt;', '&gt;'), $_POST['signature']),
		'location' => !isset($_POST['location']) ? '' : $smfFunc['db_unescape_string']($_POST['location']),
		'icq' => array(
			'name' => !isset($_POST['icq']) ? '' : $smfFunc['db_unescape_string']($_POST['icq'])
		),
		'aim' => array(
			'name' => empty($_POST['aim']) ? '' : str_replace('+', ' ', $_POST['aim'])
		),
		'yim' => array(
			'name' => empty($_POST['yim']) ? '' : $smfFunc['db_unescape_string']($_POST['yim'])
		),
		'msn' => array(
			'name' => empty($_POST['msn']) ? '' : $smfFunc['db_unescape_string']($_POST['msn'])
		),
		'posts' => empty($_POST['posts']) ? 0 : (int) $_POST['posts'],
		'avatar' => array(
			'name' => &$_POST['avatar'],
			'href' => empty($user_profile[$_REQUEST['userID']]['id_attach']) ? '' : (empty($user_profile[$_REQUEST['userID']]['attachment_type']) ? $scripturl . '?action=dlattach;attach=' . $user_profile[$_REQUEST['userID']]['id_attach'] . ';type=avatar' : $modSettings['custom_avatar_url'] . '/' . $user_profile[$_REQUEST['userID']]['filename']),
			'custom' => stristr($_POST['avatar'], 'http://') ? $_POST['avatar'] : 'http://',
			'selection' => $_POST['avatar'] == '' || stristr($_POST['avatar'], 'http://') ? '' : $_POST['avatar'],
			'choice' => empty($_POST['avatar_choice']) ? 'server_stored' : $_POST['avatar_choice'],
			'external' => empty($_POST['userpicpersonal']) ? 'http://' : $_POST['userpicpersonal'],
			'id_attach' => empty($_POST['id_attach']) ? '0' : $_POST['id_attach'],
			'allow_server_stored' => allowedTo('profile_server_avatar') || !$context['user']['is_owner'],
			'allow_upload' => allowedTo('profile_upload_avatar') || !$context['user']['is_owner'],
			'allow_external' => allowedTo('profile_remote_avatar') || !$context['user']['is_owner'],
		),
		'karma' => array(
			'good' => empty($_POST['karma_good']) ? '0' : $_POST['karma_good'],
			'bad' => empty($_POST['karma_bad']) ? '0' : $_POST['karma_bad'],
		),
		'time_format' => !isset($_POST['time_format']) ? '' : $smfFunc['db_unescape_string']($_POST['time_format']),
		'time_offset' => empty($_POST['time_offset']) ? '0' : $_POST['time_offset'],
		'secret_question' => !isset($_POST['secret_question']) ? '' : $smfFunc['db_unescape_string']($_POST['secret_question']),
		'theme' => array(
			'id' => isset($context['member']['theme']['id']) ? $context['member']['theme']['id'] : 0,
			'name' => isset($context['member']['theme']['name']) ? $context['member']['theme']['name'] : '',
		),
		'notify_announcements' => empty($_POST['notify_announcements']) ? 0 : 1,
		'notify_regularity' => empty($_POST['notify_regularity']) ? 0 : 1,
		'notify_send_body' => empty($_POST['notify_send_body']) ? 0 : (int) $_POST['notify_send_body'],
		'notify_types' => empty($_POST['notify_types']) ? 0 : (int) $_POST['notify_types'],
		'group' => isset($_POST['id_group']) ? $_POST['id_group'] : 0,
		'smiley_set' => array(
			'id' => isset($_POST['smiley_set']) ? $_POST['smiley_set'] : (isset($context['member']['smiley_set']) ? $context['member']['smiley_set']['id'] : ''),
			'name' => isset($context['member']['smiley_set']) ? $context['member']['smiley_set']['name'] : ''
		),
	);

	// Overwrite the currently SET member_groups with those you just selected.
	if (allowedTo('manage_membergroups') && isset($_POST['id_group']))
	{
		foreach ($context['member_groups'] as $id_group => $dummy)
		{
			$context['member_groups'][$id_group]['is_primary'] = $id_group == $_POST['id_group'];
			$context['member_groups'][$id_group]['is_additional'] = !empty($_POST['additional_groups']) && in_array($id_group, $_POST['additional_groups']);
		}
	}

	loadThemeOptions((int) $_REQUEST['userID']);
}

function loadThemeOptions($memID)
{
	global $context, $options, $db_prefix, $user_profile, $smfFunc;

	if (isset($_POST['options'], $_POST['default_options']))
		$_POST['options'] += $_POST['default_options'];

	if ($context['user']['is_owner'])
		$context['member']['options'] = $options;
	else
	{
		$request = $smfFunc['db_query']('', "
			SELECT id_member, variable, value
			FROM {$db_prefix}themes
			WHERE id_theme IN (1, " . (int) $user_profile[$memID]['id_theme'] . ")
				AND id_member IN (-1, $memID)", __FILE__, __LINE__);
		$temp = array();
		while ($row = $smfFunc['db_fetch_assoc']($request))
		{
			if ($row['id_member'] == -1)
			{
				$temp[$row['variable']] = $row['value'];
				continue;
			}

			if (isset($_POST['options'][$row['variable']]))
				$row['value'] = $_POST['options'][$row['variable']];
			$context['member']['options'][$row['variable']] = $row['value'];
		}
		$smfFunc['db_free_result']($request);

		// Load up the default theme options for any missing.
		foreach ($temp as $k => $v)
		{
			if (!isset($context['member']['options'][$k]))
				$context['member']['options'][$k] = $v;
		}
	}
}

// Load any custom fields for this area... no area means load all, 'summary' loads all public ones.
function loadCustomFields($memID, $area = 'summary')
{
	global $db_prefix, $context, $txt, $user_profile, $smfFunc;

	// Get the right restrictions in place...
	$where = 'active = 1';
	if ($area == 'summary' && !allowedTo('admin_forum'))
		$where .= ' AND private = 0';
	elseif ($area == 'register')
		$where .= ' AND show_reg = 1';
	elseif ($area != 'summary' && $area != 'register')
		$where .= " AND show_profile = '$area'";

	// Load all the relevant fields - and data.
	$request = $smfFunc['db_query']('', "
		SELECT col_name, field_name, field_desc, field_type, field_length, field_options,
			default_value, bbc
		FROM {$db_prefix}custom_fields
		WHERE $where", __FILE__, __LINE__);
	$context['custom_fields'] = array();
	while ($row = $smfFunc['db_fetch_assoc']($request))
	{
		// Shortcut.
		$exists = $memID && isset($user_profile[$memID]['options'][$row['col_name']]);
		$value = $exists && $user_profile[$memID]['options'][$row['col_name']] ? $user_profile[$memID]['options'][$row['col_name']] : '';

		// HTML for the input form.
		$output_html = $value;
		if ($row['field_type'] == 'check')
		{
			$true = (!$exists && $row['default_value']) || $value;
			$input_html = '<input type="checkbox" name="customfield[' . $row['col_name'] . ']" ' . ($true ? 'checked="checked"' : '') . ' class="check" />';
			$output_html = $true ? $txt['yes'] : $txt['no'];
		}
		elseif ($row['field_type'] == 'select')
		{
			$input_html = '<select name="customfield[' . $row['col_name'] . ']">';
			$options = explode(',', $row['field_options']);
			foreach ($options as $k => $v)
			{
				$true = (!$exists && $row['default_value'] == $v) || $value == $v;
				$input_html .= '<option value="' . $k . '" ' . ($true ? 'selected="selected"' : '') . '>' . $v . '</option>';
				if ($true)
					$output_html = $v;
			}

			$input_html .= '</select>';
		}
		elseif ($row['field_type'] == 'radio')
		{
			$input_html = '<fieldset>';
			$options = explode(',', $row['field_options']);
			foreach ($options as $k => $v)
			{
				$true = (!$exists && $row['default_value'] == $v) || $value == $v;
				$input_html .= '<label for="customfield_' . $row['col_name'] . '_' . $k . '"><input type="radio" name="customfield[' . $row['col_name'] . ']" id="customfield_' . $row['col_name'] . '_' . $k . '" value="' . $k . '" ' . ($true ? 'checked="checked"' : '') . '>' . $v . '</label><br />';
				if ($true)
					$output_html = $v;
			}
			$input_html .= '</fieldset>';
		}
		elseif ($row['field_type'] == 'text')
			$input_html = '<input type="text" name="customfield[' . $row['col_name'] . ']" ' . ($row['field_length'] != 0 ? 'maxlength="' . $row['field_length'] . '"' : '') . ' value="' . $value . '" />';
		else
		{
			@list ($rows, $cols) = @explode(',', $row['default_value']);
			$input_html = '<textarea name="customfield[' . $row['col_name'] . ']" ' . (!empty($rows) ? 'rows="' . $rows . '"' : '') . ' ' . (!empty($cols) ? 'cols="' . $cols . '"' : '') . '>' . $value . '</textarea>';
			if ($row['bbc'])
				$output_html = parse_bbc($output_html);
		}

		$context['custom_fields'][] = array(
			'name' => $row['field_name'],
			'desc' => $row['field_desc'],
			'type' => $row['field_type'],
			'input_html' => $input_html,
			'output_html' => $output_html,
			'value' => $value,
		);
	}
	$smfFunc['db_free_result']($request);
}

function ignoreboards($memID)
{
	global $txt, $user_info, $user_profile, $db_prefix, $context, $db_prefix, $modSettings, $smfFunc;

	// Have the admins enabled this option?
	if (empty($modSettings['allow_ignore_boards']))
		fatal_lang_error('ignoreboards_disallowed', 'user');

	// Find all the boards this user is allowed to see.
	$request = $smfFunc['db_query']('', "
		SELECT b.id_cat, c.name AS cat_name, b.id_board, b.name, b.child_level, 
			". (!empty($user_profile[$memID]['ignore_boards']) ? 'b.id_board IN (' . $user_profile[$memID]['ignore_boards'] . ')' : 'false') ." AS is_ignored
		FROM {$db_prefix}boards AS b
			LEFT JOIN {$db_prefix}categories AS c ON (c.id_cat = b.id_cat)
		WHERE $user_info[query_see_board]", __FILE__, __LINE__);
	$context['num_boards'] = $smfFunc['db_num_rows']($request);
	$context['categories'] = array();
	while ($row = $smfFunc['db_fetch_assoc']($request))
	{
		// This category hasn't been set up yet..
		if (!isset($context['categories'][$row['id_cat']]))
			$context['categories'][$row['id_cat']] = array(
				'id' => $row['id_cat'],
				'name' => $row['cat_name'],
				'boards' => array()
			);

		// Set this board up, and let the template know when it's a child.  (indent them..)
		$context['categories'][$row['id_cat']]['boards'][$row['id_board']] = array(
			'id' => $row['id_board'],
			'name' => $row['name'],
			'child_level' => $row['child_level'],
			'selected' => $row['is_ignored'],
		);
	}
	$smfFunc['db_free_result']($request);
		
	// Now, let's sort the list of categories into the boards for templates that like that.
	$temp_boards = array();
	foreach ($context['categories'] as $category)
	{
		$temp_boards[] = array(
			'name' => $category['name'],
			'child_ids' => array_keys($category['boards'])
		);
		$temp_boards = array_merge($temp_boards, array_values($category['boards']));
	}

	$max_boards = ceil(count($temp_boards) / 2);
	if ($max_boards == 1)
		$max_boards = 2;

	// Now, alternate them so they can be shown left and right ;).
	$context['board_columns'] = array();
	for ($i = 0; $i < $max_boards; $i++)
	{
		$context['board_columns'][] = $temp_boards[$i];
		if (isset($temp_boards[$i + $max_boards]))
			$context['board_columns'][] = $temp_boards[$i + $max_boards];
		else
			$context['board_columns'][] = array();
	}

	loadThemeOptions($memID);
}

?>