<?php
/**********************************************************************************
* Profile.php                                                                     *
***********************************************************************************
* SMF: Simple Machines Forum                                                      *
* Open-Source Project Inspired by Zef Hemel (zef@zefhemel.com)                    *
* =============================================================================== *
* Software Version:           SMF 2.0 Beta 1.1                                    *
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

/*	This file has the primary job of showing and editing people's profiles.
	It also allows the user to change some of their or another's preferences,
	and such things.  It uses the following functions:

	void ModifyProfile(array errors = none)
		// !!!

	void saveProfileChanges(array &profile_variables, array &errors, int id_member)
		// !!!

	void makeThemeChanges(int id_member, int id_theme)
		// !!!

	void makeNotificationChanges(int id_member)
		// !!!

	void profileSaveAvatarData(int id_member, array &errors)
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

	void issueWarning(int id_member)
		// !!!

	void deleteAccount(int id_member)
		// !!!

	void deleteAccount2(array profile_variables, array &errors, int id_member)
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
	global $txt, $scripturl, $user_info, $context, $sourcedir, $user_profile, $cur_profile;
	global $modSettings, $memberContext, $profile_vars, $smfFunc, $post_errors;

	// Don't reload this as we may have processed error strings.
	if (empty($post_errors))
		loadLanguage('Profile');
	loadTemplate('Profile');

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
	$context['id_member'] = $memID;
	$cur_profile = $user_profile[$memID];

	// Is this the profile of the user himself or herself?
	$context['user']['is_owner'] = $memID == $user_info['id'];

	/* Define all the sections within the profile area!
		We start by defining the permission required - then SMF takes this and turns it into the relevant context ;)
		Possible fields:
			For Section:
				string $title:		Section title.
				bool $enabled:		Should section be shown?
				array $areas:		Array of areas within this section.

			For Areas:
				array $own:		Array of permissions to determine who can access this area - if the user is the owner of the profile.
				array $any:		As above if the user is not the owner of the profile.
				string $label:		Optional text string for link (Otherwise $txt[$index] will be used)
				string $href:		Optional href for area.
				bool $enabled:		Should area be shown?
				bool $validate:		Does the session need to be checked before accessing this section on save?
				string $sc:		Session check validation to do on save - note without this save will get unset - if set.
				bool $hidden:		Does this not actually appear on the menu?
				bool $load_member:	Should we load the member context for this area?
	*/
	$context['profile_areas'] = array(
		'info' => array(
			'title' => $txt['profileInfo'],
			'areas' => array(
				'summary' => array(
					'own' => array('profile_view_any', 'profile_view_own'),
					'any' => array('profile_view_any'),
				),
				'statPanel' => array(
					'own' => array('profile_view_any', 'profile_view_own'),
					'any' => array('profile_view_any'),
					'load_member' => true,
				),
				'showPosts' => array(
					'own' => array('profile_view_any', 'profile_view_own'),
					'any' => array('profile_view_any'),
					'load_member' => true,
				),
				'trackUser' => array(
					'own' => array('moderate_forum'),
					'any' => array('moderate_forum'),
				),
				'trackIP' => array(
					'own' => array('moderate_forum'),
					'any' => array('moderate_forum'),
				),
				'showPermissions' => array(
					'own' => array('manage_permissions'),
					'any' => array('manage_permissions'),
				),
			),
		),
		'edit_profile' => array(
			'title' => $txt['profileEdit'],
			'areas' => array(
				'account' => array(
					'own' => array('profile_identity_any', 'profile_identity_own', 'manage_membergroups'),
					'any' => array('profile_identity_any', 'manage_membergroups'),
					'sc' => 'post',
					'password' => true,
				),
				'forumProfile' => array(
					'own' => array('profile_extra_any', 'profile_extra_own'),
					'any' => array('profile_extra_any'),
					'sc' => 'post',
				),
				'theme' => array(
					'own' => array('profile_extra_any', 'profile_extra_own'),
					'any' => array('profile_extra_any'),
					'sc' => 'post',
				),
				'notification' => array(
					'own' => array('profile_extra_any', 'profile_extra_own'),
					'any' => array('profile_extra_any'),
					'sc' => 'post',
				),
				'ignoreboards' => array(
					'own' => array('profile_extra_any', 'profile_extra_own'),
					'any' => array('profile_extra_any'),
					'enabled' => !empty($modSettings['allow_ignore_boards']),
					'sc' => 'post',
				),
				'editBuddies' => array(
					'own' => array('profile_extra_any', 'profile_extra_own'),
					'any' => array(),
					'enabled' => !empty($modSettings['enable_buddylist']) && $context['user']['is_owner'],
					'sc' => 'post',
				),
				'groupMembership' => array(
					'own' => array('profile_view_own'),
					'any' => array('manage_membergroups'),
					'enabled' => !empty($modSettings['show_group_membership']) && $context['user']['is_owner'],
					'sc' => 'request',
				),
			),
		),
		'profile_action' => array(
			'title' => $txt['profileAction'],
			'areas' => array(
				'send_pm' => array(
					'own' => array(),
					'any' => array('pm_send'),
					'enabled' => !$context['user']['is_owner'],
					'href' => $scripturl . '?action=pm;sa=send;u=' . $memID,
					'label' => $txt['profileSendIm'],
				),
				'issueWarning' => array(
					'own' => array(),
					'any' => array('issue_warning'),
					'enabled' => $modSettings['warning_settings']{0} == 1 && !$context['user']['is_owner'],
					'label' => $txt['profile_issue_warning'],
				),
				'banUser' => array(
					'own' => array(),
					'any' => array('manage_bans'),
					'enabled' => $cur_profile['id_group'] != 1 && !in_array(1, explode(',', $cur_profile['additional_groups'])),
					'href' => $scripturl . '?action=admin;area=ban;sa=add;u=' . $memID,
					'label' => $txt['profileBanUser'],
				),
				'deleteAccount' => array(
					'own' => array('profile_remove_any', 'profile_remove_own'),
					'any' => array('profile_remove_any'),
					'sc' => 'post',
					'password' => true,
				),
				'activateAccount' => array(
					'own' => array(),
					'any' => array('moderate_forum'),
					'sc' => 'get',
					'hidden' => true,
				),
			),
		),
	);

	// Auto populate the above!
	$defaultAction = false;
	$context['completed_save'] = false;
	$context['password_areas'] = array();
	$security_checks = array();
	foreach ($context['profile_areas'] as $section_id => $section)
	{
		// Not even enabled?
		if (isset($section['enabled']) && $section['enabled'] == false)
		{
			unset($context['profile_areas'][$section_id]);
			continue;
		}

		foreach ($section['areas'] as $area_id => $area)
		{
			// Were we trying to see this?
			if (isset($_REQUEST['sa']) && $_REQUEST['sa'] == $area_id && (!isset($area['enabled']) || $area['enabled'] != false) && !empty($area[$context['user']['is_owner'] ? 'own' : 'any']))
			{
				$security_checks['permission'] = $area[$context['user']['is_owner'] ? 'own' : 'any'];

				// Are we saving data in a valid area?
				if (isset($area['sc']) && isset($_REQUEST['save']))
				{
					$security_checks['session'] = $area['sc'];
					$context['completed_save'] = true;
				}

				// Does this require session validating?
				if (!empty($area['validate']))
					$security_checks['validate'] = true;

				// Load this users data?
				if (!empty($area['load_member']))
				{
					loadMemberContext($memID);
					$context['member'] = $memberContext[$memID];
				}
			}

			// Can we do this?
			if ((!isset($area['enabled']) || $area['enabled'] != false) && !empty($area[$context['user']['is_owner'] ? 'own' : 'any']) && allowedTo($area[$context['user']['is_owner'] ? 'own' : 'any']) && empty($area['hidden']))
			{
				// Replace the contents with a link.
				$context['profile_areas'][$section_id]['areas'][$area_id] = '<a href="' . (isset($area['href']) ? $area['href'] : $scripturl . '?action=profile;u=' . $memID . ';sa=' . $area_id) . '">' . (isset($area['label']) ? $area['label'] : $txt[$area_id]) . '</a>';
				// Should we do this by default?
				if ($defaultAction === false)
					$defaultAction = $area_id;
				// Password required?
				if (!empty($area['password']))
					$context['password_areas'][] = $area_id;
			}
			// Otherwise unset it!
			else
				unset($context['profile_areas'][$section_id]['areas'][$area_id]);
		}

		// Is there nothing left?
		if (empty($context['profile_areas'][$section_id]['areas']))
			unset($context['profile_areas'][$section_id]);
	}

	// If we have no sub-action find the default or drop out.
	$context['menu_item_selected'] = '';
	if (!isset($_REQUEST['sa']) && $defaultAction !== false)
		$_REQUEST['sa'] = $defaultAction;
	else
		isAllowedTo('profile_view_' . ($context['user']['is_owner'] ? 'own' : 'any'));

	// Set the selected items.
	$context['menu_item_selected'] = $_REQUEST['sa'];
	$context['sub_template'] = $_REQUEST['sa'];

	// Now the context is setup have we got any security checks to carry out additional to that above?
	if (isset($security_checks['session']))
		checkSession($security_checks['session']);
	if (isset($security_checks['validate']))
		validateSession();
	if (isset($security_checks['permission']))
		isAllowedTo($security_checks['permission']);

	// All the subactions that require a user password in order to validate.
	$context['require_password'] = in_array($context['menu_item_selected'], $context['password_areas']);

	// Is there an updated message to show?
	if (isset($_GET['updated']))
		$context['profile_updated'] = $txt['profile_updated_own'];

	// This is here so the menu won't be shown unless it's actually needed.
	if (!isset($context['profile_areas']['info']['areas']['trackUser']) && !isset($context['profile_areas']['info']['areas']['showPermissions']) && !isset($context['profile_areas']['edit_profile']) && !isset($context['profile_areas']['profile_action']['areas']['banUser']) && !isset($context['profile_areas']['profile_action']['areas']['issueWarning']) && !isset($context['profile_areas']['profile_action']['areas']['deleteAccount']))
		$context['profile_areas'] = array();

	// Make sure that the subaction function does exist!
	if (!function_exists($_REQUEST['sa']))
		fatal_lang_error('no_access');

	// If we're in wireless then we have a cut down template...
	if (WIRELESS && $context['sub_template'] == 'summary' && WIRELESS_PROTOCOL != 'wap')
		$context['sub_template'] = WIRELESS_PROTOCOL . '_profile';
	else
		$context['template_layers'][] = 'profile';

	// These will get populated soon!
	$post_errors = array();
	$profile_vars = array();

	// Right - are we saving - if so let's save the old data first.
	if ($context['completed_save'])
	{
		// If it's someone elses profile then validate the session.
		if (!$context['user']['is_owner'])
			validateSession();

		// Clean up the POST variables.
		$_POST = htmltrim__recursive($_POST);
		$_POST = unescapestring__recursive($_POST);
		$_POST = htmlspecialchars__recursive($_POST);
		$_POST = escapestring__recursive($_POST);

		if ($context['user']['is_owner'] && $context['require_password'])
		{
			// You didn't even enter a password!
			if (trim($_POST['oldpasswrd']) == '')
				$post_errors[] = 'no_password';

			// Since the password got modified due to all the $_POST cleaning, lets undo it so we can get the correct password
			$_POST['oldpasswrd'] = $smfFunc['db_escape_string'](un_htmlspecialchars($smfFunc['db_unescape_string']($_POST['oldpasswrd'])));

			// Does the integration want to check passwords?
			$good_password = false;
			if (isset($modSettings['integrate_verify_password']) && function_exists($modSettings['integrate_verify_password']))
				if (call_user_func($modSettings['integrate_verify_password'], $cur_profile['member_name'], $_POST['oldpasswrd'], false) === true)
					$good_password = true;

			// Bad password!!!
			if (!$good_password && $user_info['passwd'] != sha1(strtolower($cur_profile['member_name']) . $_POST['oldpasswrd']))
				$post_errors[] = 'bad_password';

			// Warn other elements not to jump the gun and do custom changes!
			if (in_array('bad_password', $post_errors))
				$context['password_auth_failed'] = true;
		}

		// Change the IP address in the database.
		if ($context['user']['is_owner'])
			$profile_vars['member_ip'] = "'$user_info[ip]'";

		// Now call the sub-action function...
		if (isset($_REQUEST['sa']) && $_REQUEST['sa'] == 'activateAccount' && empty($post_errors))
		{
			activateAccount($memID);
		}
		if (isset($_REQUEST['sa']) && $_REQUEST['sa'] == 'deleteAccount' && empty($post_errors))
		{
			deleteAccount2($profile_vars, $post_errors, $memID);

			if (empty($post_errors))
				redirectexit();
		}
		elseif (isset($_REQUEST['sa']) && $_REQUEST['sa'] == 'groupMembership' && empty($post_errors))
		{
			$msg = groupMembership2($profile_vars, $post_errors, $memID);

			// Whatever we've done, we have nothing else to do here...
			redirectexit('action=profile;u=' . $memID . ';sa=groupMembership' . (!empty($msg) ? ';msg=' . $msg : ''));
		}
		elseif (isset($_REQUEST['sa']) && in_array($_REQUEST['sa'], array('account', 'forumProfile', 'theme')))
			saveProfileFields();
		else
		{
			$force_redirect = true;
			saveProfileChanges($profile_vars, $post_errors, $memID);
		}

		// There was a problem, let them try to re-enter.
		if (!empty($post_errors))
		{
			// Load the language file so we can give a nice explanation of the errors.
			loadLanguage('Errors');
			$context['post_errors'] = $post_errors;
		}
		elseif (!empty($profile_vars))
		{
			// If we've changed the password, notify any integration that may be listening in.
			if (isset($profile_vars['passwd']) && isset($modSettings['integrate_reset_pass']) && function_exists($modSettings['integrate_reset_pass']))
				call_user_func($modSettings['integrate_reset_pass'], $cur_profile['member_name'], $cur_profile['member_name'], $_POST['passwrd1']);

			updateMemberData($memID, $profile_vars);

			// What if this is the newest member?
			if ($modSettings['latestMember'] == $memID)
				updateStats('member');
			elseif (isset($profile_vars['real_name']))
				updateSettings(array('memberlist_updated' => time()));

			// If the member changed his/her birthdate, update calendar statistics.
			if (isset($profile_vars['birthdate']) || isset($profile_vars['real_name']))
				updateSettings(array(
					'calendar_updated' => time(),
				));

			// Have we got any post save functions to execute?
			if (!empty($context['profile_execute_on_save']))
				foreach ($context['profile_execute_on_save'] as $saveFunc)
					$saveFunc();

			// Let them know it worked!
			$context['profile_updated'] = $context['user']['is_owner'] ? $txt['profile_updated_own'] : sprintf($txt['profile_updated_else'], $cur_profile['member_name']);

			// Invalidate any cached data.
			cache_put_data('member_data-profile-' . $memID, null, 0);
		}
	}

	// Have some errors for some reason?
	if (!empty($post_errors))
	{
		// Set all the errors so the template knows what went wrong.
		foreach ($post_errors as $error_type)
			$context['modify_error'][$error_type] = true;
	}
	// If it's you then we should redirect upon save.
	elseif (!empty($profile_vars) && $context['user']['is_owner'])
		redirectexit('action=profile;sa=' . $_REQUEST['sa'] . ';updated');
	elseif (!empty($force_redirect))
		redirectexit('action=profile;u=' . $memID . ';sa=' . $_REQUEST['sa']);

	// Call the appropriate subaction function.
	$_REQUEST['sa']($memID);

	// Set the page title if it's not already set...
	if (!isset($context['page_title']))
		$context['page_title'] = $txt['profile'] . ' - ' . $txt[$_REQUEST['sa']];
}

// This defines every profile field known to man.
function loadProfileFields($force_reload = false)
{
	global $context, $profile_fields, $txt, $scripturl, $modSettings, $user_info, $old_profile, $smfFunc, $cur_profile;

	// Don't load this twice!
	if (!empty($profile_fields) && !$force_reload)
		return;

	/* This horrific array defines all the profile fields in the whole world!
		In general each "field" has one array - the key of which is the database column name associated with said field. Each item
		can have the following attributes:

				string $type:			The type of field this is - valid types are:
					- callback:		This is a field which has its own callback mechanism for templating.
					- check:		A simple checkbox.
					- hidden:		This doesn't have any visual aspects but may have some validity.
					- password:		A password box.
					- select:		A select box.
					- text:			A string of some description.

				string $label:			The label for this item - default will be $txt[$key] if this isn't set.
				string $subtext:		The subtext (Small label) for this item.
				int $size:			Optional size for a text area.
				array $input_attr:		An array of text strings to be added to the input box for this item.
				string $value:			The value of the item. If not set $cur_profile[$key] is assumed.
				string $permission:		Permission required for this item (Excluded _any/_own subfix which is applied automatically).
				function $input_validate:	A runtime function which validates the element before going to the database. It is passed
								the relevant $_POST element if it exists and should be treated like a reference.

								Return types:
					- true:			Element can be stored.
					- false:		Skip this element.
					- a text string:	An error occured - this is the error message.

				function $preload:		A function that is used to load data required for this element to be displayed. Must return
								true to be displayed at all.

				string $cast_type:		If set casts the element to a certain type. Valid types (bool, int, float).
				string $save_key:		If the index of this element isn't the database column name it can be overriden
								with this string.
				bool $is_dummy:			If set then nothing is acted upon for this element.
				bool $enabled:			A test to determine whether this is even available - if not is unset.
				string $link_with:		Key which links this field to an overall set.

		Note that all elements that have a custom input_validate must ensure they set the value of $cur_profile correct to enable
		the changes to be displayed correctly on submit of the form.

	*/

	$profile_fields = array(
		'aim' => array(
			'type' => 'text',
			'label' => $txt['aim'],
			'subtext' => $txt['your_aim'],
			'size' => 24,
			'input_attr' => array('maxlength="16"'),
			'value' => strtr(empty($cur_profile['aim']) ? '' : $cur_profile['aim'], '+', ' '),
			'permission' => 'profile_extra',
			'input_validate' => create_function('&$value', '
				$value = strtr($value, \' \', \'+\');
				return true;
			'),
		),
		'avatar_choice' => array(
			'type' => 'callback',
			'callback_func' => 'avatar_select',
			// This handles the permissions too.
			'preload' => 'profileLoadAvatarData',
			'input_validate' => 'profileSaveAvatarData',
			'save_key' => 'avatar',
		),
		'bday1' => array(
			'type' => 'callback',
			'callback_func' => 'birthdate',
			'permission' => 'profile_extra',
			'preload' => create_function('', '
				global $cur_profile, $context;

				// Split up the birthdate....
				list ($uyear, $umonth, $uday) = empty($cur_profile[\'birthdate\']) ? \'0000-00-00\' : explode(\'-\', $cur_profile[\'birthdate\']);
				$context[\'member\'][\'birth_date\'] = array(
					\'year\' => $uyear,
					\'month\' => $umonth,
					\'day\' => $uday,
				);

				return true;
			'),
			'input_validate' => create_function('&$value', '
				global $profile_vars, $cur_profile;

				if (isset($_POST[\'bday2\'], $_POST[\'bday3\']) && $value > 0 && $_POST[\'bday2\'] > 0)
				{
					// Set to blank?
					if ((int) $_POST[\'bday3\'] == 1 && (int) $_POST[\'bday2\'] == 1 && (int) $value == 1)
						$value = \'0001-01-01\';
					else
						$value = checkdate($value, $_POST[\'bday2\'], $_POST[\'bday3\'] < 4 ? 4 : $_POST[\'bday3\']) ? sprintf(\'%04d-%02d-%02d\', $_POST[\'bday3\'] < 4 ? 4 : $_POST[\'bday3\'], $_POST[\'bday1\'], $_POST[\'bday2\']) : \'0001-01-01\';
				}
				else
					$value = \'0001-01-01\';

				$profile_vars[\'birthdate\'] = "\'" . $value . "\'";
				$cur_profile[\'birthdate\'] = $value;
				return false;
			'),
		),
		// Setting the birthdate the old style way?
		'birthdate' => array(
			'type' => 'hidden',
			'permission' => 'profile_extra',
			'input_validate' => create_function('&$value', '
				global $cur_profile;
				// !!! Should we check for this year and tell them they made a mistake :P? (based on coppa at least?)
				if (preg_match(\'/(\d{4})[\-\., ](\d{2})[\-\., ](\d{2})/\', $value, $dates) === 1)
				{
					$value = checkdate($dates[2], $dates[3], $dates[1] < 4 ? 4 : $dates[1]) ? sprintf(\'%04d-%02d-%02d\', $dates[1] < 4 ? 4 : $dates[1], $dates[2], $dates[3]) : \'0001-01-01\';
					return true;
				}
				else
				{
					$value = "\'" . empty($cur_profile[\'birthdate\']) ? \'0004-01-01\' : $cur_profile[\'birthdate\'] . "\'";
					return false;
				}
			'),
		),
		'date_registered' => array(
			'type' => 'text',
			'value' => empty($cur_profile['date_registered']) ? $txt['not_applicable'] : strftime('%Y-%m-%d', $cur_profile['date_registered'] + ($user_info['time_offset'] + $modSettings['time_offset']) * 3600),
			'label' => $txt['date_registered'],
			'permission' => 'moderate_forum',
			'input_validate' => create_function('&$value', '
				global $txt, $user_info, $modSettings, $cur_profile, $context;

				// Bad date!  Go try again - please?
				if (($value = strtotime($value)) === -1)
				{
					$value = "\'" . $cur_profile[\'date_registered\'] . "\'";
					return $txt[\'invalid_registration\'] . \' \' . strftime(\'%d %b %Y \' . (strpos($user_info[\'time_format\'], \'%H\') !== false ? \'%I:%M:%S %p\' : \'%H:%M:%S\'), forum_time(false));
				}
				// As long as it doesn\'t equal "N/A"...
				elseif ($value != $txt[\'not_applicable\'] && $value != strtotime(strftime(\'%Y-%m-%d\', $cur_profile[\'date_registered\'] + ($user_info[\'time_offset\'] + $modSettings[\'time_offset\']) * 3600)))
					$value = $value - ($user_info[\'time_offset\'] + $modSettings[\'time_offset\']) * 3600;

				return true;
			'),
		),
		'email_address' => array(
			'type' => 'text',
			'label' => $txt['email'],
			'subtext' => $txt['valid_email'],
			'permission' => 'profile_identity',
			'input_validate' => create_function('&$value', '
				global $context, $old_profile, $context, $profile_vars;

				if (strtolower($value) == strtolower($old_profile[\'email_address\']))
					return false;

				$isValid = profileValidateEmail($value, $context[\'id_member\']);

				// Do they need to revalidate? If so schedule the function!
				if (!empty($modSettings[\'send_validation_onChange\']) && !allowedTo(\'moderate_forum\'))
				{
					$profile_vars[\'validation_code\'] = substr(preg_replace(\'/\W/\', \'\', md5(rand())), 0, 10);
					$profile_vars[\'is_activated\'] = 2;
					$context[\'profile_execute_on_save\'][] = \'profileSendActivation\';
					unset($context[\'profile_execute_on_save\'][\'reload_user\']);
				}

				return $isValid;
			'),
		),
		'gender' => array(
			'type' => 'select',
			'cast_type' => 'int',
			'options' => 'return array(0 => \'\', 1 => $txt[\'male\'], 2 => $txt[\'female\']);',
			'label' => $txt['gender'],
			'permission' => 'profile_extra',
		),
		'hide_email' => array(
			'type' => 'check',
			'value' => empty($cur_profile['hide_email']) ? true : false,
			'label' => $txt['allow_user_email'],
			'permission' => 'profile_identity',
			// Reverse the logic.
			'input_validate' => create_function('&$value', '
				if ($value == 0)
					$value = 1;
				else
					$value = 0;
				return true;
			'),
		),
		'icq' => array(
			'type' => 'text',
			'label' => $txt['icq'],
			'subtext' => $txt['your_icq'],
			'size' => 24,
			'permission' => 'profile_extra',
			// Need to make sure ICQ doesn't equal 0.
			'input_validate' => create_function('&$value', '
				if (empty($value))
					$value = \'\';
				else
					$value = (int) $value;
				return true;
			'),
		),
		// Selecting group membership is a complicated one so we treat it separate!
		'id_group' => array(
			'type' => 'callback',
			'callback_func' => 'group_manage',
			'permission' => 'manage_membergroups',
			'preload' => 'profileLoadGroups',
			'input_validate' => 'profileSaveGroups',
		),
		'id_theme' => array(
			'type' => 'callback',
			'callback_func' => 'theme_pick',
			'permission' => 'profile_extra',
			'enabled' => $modSettings['theme_allow'] || allowedTo('admin_forum'),
			'preload' => create_function('', '
				global $smfFunc, $db_prefix, $context, $cur_profile, $txt;

				$request = $smfFunc[\'db_query\'](\'\', "
					SELECT value
					FROM {$db_prefix}themes
					WHERE id_theme = " . ((int) $cur_profile[\'id_theme\']) . "
						AND variable = \'name\'
					LIMIT 1", __FILE__, __LINE__);
				list ($name) = $smfFunc[\'db_fetch_row\']($request);
				$smfFunc[\'db_free_result\']($request);

				$context[\'member\'][\'theme\'] = array(
					\'id\' => $cur_profile[\'id_theme\'],
					\'name\' => empty($cur_profile[\'id_theme\']) ? $txt[\'theme_forum_default\'] : $name
				);
				return true;
			'),
			'input_validate' => create_function('&$value', '
				$value = (int) $value;
				return true;
			'),
		),
		'karma_good' =>  array(
			'type' => 'callback',
			'callback_func' => 'karma_modify',
			'subtext' => $txt['your_icq'],
			'permission' => 'admin_forum',
			// Set karma_bad too!
			'input_validate' => create_function('&$value', '
				global $profile_vars, $cur_profile;

				$value = (int) $value;
				if (isset($_POST[\'karma_bad\']))
				{
					$profile_vars[\'karma_bad\'] = $_POST[\'karma_bad\'] != \'\' ? (int) $_POST[\'karma_bad\'] : "\'\'";
					$cur_profile[\'karma_bad\'] = $_POST[\'karma_bad\'] != \'\' ? (int) $_POST[\'karma_bad\'] : \'\';
				}
				return true;
			'),
			'preload' => create_function('', '
				global $context, $cur_profile;

				$context[\'member\'][\'karma\'][\'good\'] = $cur_profile[\'karma_good\'];
				$context[\'member\'][\'karma\'][\'bad\'] = $cur_profile[\'karma_bad\'];

				return true;
			'),
			'enabled' => !empty($modSettings['karmaMode']),
		),
		'lngfile' => array(
			'type' => 'select',
			'options' => 'return $context[\'profile_languages\'];',
			'label' => $txt['prefered_language'],
			'permission' => 'profile_identity',
			'preload' => 'profileLoadLanguages',
			'enabled' => !empty($modSettings['userLanguage']),
			'input_validate' => create_function('&$value', '
				global $context, $cur_profile;

				// Load the langauges.
				profileLoadLanguages();

				if (isset($context[\'profile_languages\'][$value]))
				{
					if ($context[\'user\'][\'is_owner\'])
						$_SESSION[\'language\'] = $value;
					return true;
				}
				else
				{
					$value = "\'" . $cur_profile[\'lngfile\'] . "\'";
					return false;
				}
			'),
		),
		'location' => array(
			'type' => 'text',
			'label' => $txt['location'],
			'size' => 50,
			'permission' => 'profile_extra',
		),
		// The username is not always editable - so adjust it as such.
		'member_name' => array(
			'type' => allowedTo('admin_forum') && isset($_GET['changeusername']) ? 'text' : 'label',
			'label' => $txt['username'],
			'subtext' => allowedTo('admin_forum') && !isset($_GET['changeusername']) ? '(<a href="' . $scripturl . '?action=profile;u=' . $context['id_member'] . ';sa=account;changeusername" style="font-style: italic;">' . $txt['username_change'] . '</a>)' : '',
			'permission' => 'profile_identity',
			'prehtml' => allowedTo('admin_forum') && isset($_GET['changeusername']) ? '<div style="color: red;">' . $txt['username_warning'] . '</div>' : '',
			'input_validate' => create_function('&$value', '
				global $sourcedir, $context;

				if (allowedTo(\'admin_forum\'))
				{
					// We\'ll need this...
					require_once($sourcedir . \'/Subs-Auth.php\');

					// Do the reset... this will send them an email too.
					resetPassword($context[\'id_member\'], $value);
				}
				return false;
			'),
		),
		'msn' => array(
			'type' => 'text',
			'label' => $txt['msn'],
			'subtext' => $txt['smf237'],
			'size' => 24,
			'permission' => 'profile_extra',
			'input_validate' => create_function('&$value', '
				global $cur_profile;
				// Make sure the msn one is an email address, not something like \'none\' :P.
				if ($value != \'\' && preg_match(\'~^[0-9A-Za-z=_+\-/][0-9A-Za-z=_\\\'+\-/\.]*@[\w\-]+(\.[\w\-]+)*(\.[\w]{2,6})$~\', $value) == 0)
				{
					$value = $cur_profile[\'msn\'];
					return false;
				}
				return true;
			'),
		),
		'passwrd1' => array(
			'type' => 'password',
			'label' => $txt['choose_pass'],
			'subtext' => $txt['password_strength'],
			'size' => 20,
			'value' => '',
			'permission' => 'profile_identity',
			'save_key' => 'passwd',
			// Note this will only work if passwrd2 also exists!
			'input_validate' => create_function('&$value', '
				global $sourcedir, $user_info, $smfFunc, $old_profile;

				// If we didn\'t try it then ignore it!
				if ($value == \'\')
					return false;

				// Do the two entries for the password even match?
				if (!isset($_POST[\'passwrd2\']) || $value != $_POST[\'passwrd2\'])
					return \'bad_new_password\';

				// Let\'s get the validation function into play...
				require_once($sourcedir . \'/Subs-Auth.php\');
				$passwordErrors = validatePassword($value, $user_info[\'username\'], array($user_info[\'name\'], $user_info[\'email\']));

				// Were there errors?
				if ($passwordErrors != null)
					return \'password_\' . $passwordErrors;

				// Set up the new password variable... ready for storage.
				$value = sha1(strtolower($old_profile[\'member_name\']) . un_htmlspecialchars($smfFunc[\'db_unescape_string\']($value)));
				return true;
			'),
		),
		'passwrd2' => array(
			'type' => 'password',
			'label' => $txt['verify_pass'],
			'size' => 20,
			'value' => '',
			'permission' => 'profile_identity',
			'is_dummy' => true,
		),
		'personal_text' => array(
			'type' => 'text',
			'label' => $txt['personal_text'],
			'input_attr' => array('maxlength="50"'),
			'size' => 50,
			'permission' => 'profile_extra',
		),
		'posts' => array(
			'type' => 'int',
			'label' => $txt['profile_posts'],
			'size' => 4,
			'permission' => 'moderate_forum',
			'input_validate' => create_function('&$value', '
				$value = $value != \'\' ? strtr($value, array(\',\' => \'\', \'.\' => \'\', \' \' => \'\')) : 0;
				return true;
			'),

		),
		'real_name' => array(
			'type' => !empty($modSettings['allow_editDisplayName']) || allowedTo('moderate_forum') ? 'text' : 'label',
			'label' => $txt['name'],
			'subtext' => $txt['display_name_desc'],
			'input_attr' => array('maxlength="60"'),
			'permission' => 'profile_identity',
			'enabled' => !empty($modSettings['allow_editDisplayName']) || allowedTo('moderate_forum'),
			'input_validate' => create_function('&$value', '
				global $context, $smfFunc, $sourcedir;

				$value = trim(preg_replace(\'~[\s]~\' . ($context[\'utf8\'] ? \'u\' : \'\'), \' \', $value));
				if (trim($value) == \'\')
					return \'no_name\';
				elseif ($smfFunc[\'strlen\']($value) > 60)
 					return \'name_too_long\';
				else
				{
					require_once($sourcedir . \'/Subs-Members.php\');
					if (isReservedName($value, $context[\'id_member\']))
						return \'name_taken\';
				}
				return true;
			'),
		),
		'secret_question' => array(
			'type' => 'text',
			'label' => $txt['secret_question'],
			'subtext' => $txt['secret_desc'],
			'size' => 50,
			'permission' => 'profile_identity',
		),
		'secret_answer' => array(
			'type' => 'text',
			'label' => $txt['secret_answer'],
			'subtext' => $txt['secret_desc2'],
			'size' => 20,
			'postinput' => '<span class="smalltext" style="margin-left: 4ex;"><a href="' . $scripturl . '?action=helpadmin;help=secret_why_blank" onclick="return reqWin(this.href);">' . $txt['secret_why_blank'] . '</a></span>',
			'value' => '',
			'permission' => 'profile_identity',
			'input_validate' => create_function('&$value', '
				$value = $value != \'\' ? md5($value) : \'\';
				return true;
			'),
		),
		'signature' => array(
			'type' => 'callback',
			'callback_func' => 'signature_modify',
			'permission' => 'profile_extra',
			'enabled' => substr($modSettings['signature_settings'], 0, 1) == 1,
			'preload' => 'profileLoadSignatureData',
			'input_validate' => 'profileValidateSignature',
		),
		'show_online' => array(
			'type' => 'check',
			'label' => $txt['show_online'],
			'permission' => 'profile_identity',
			'enabled' => !empty($modSettings['allow_hideOnline']) || allowedTo('moderate_forum'),
		),
		'smiley_set' => array(
			'type' => 'callback',
			'callback_func' => 'smiley_pick',
			'enabled' => !empty($modSettings['smiley_sets_enable']),
			'permission' => 'profile_extra',
			'preload' => create_function('', '
				global $modSettings, $context, $txt, $cur_profile;

				$context[\'member\'][\'smiley_set\'][\'id\'] = empty($cur_profile[\'smiley_set\']) ? \'\' : $cur_profile[\'smiley_set\'];
				$context[\'smiley_sets\'] = explode(\',\', \'none,,\' . $modSettings[\'smiley_sets_known\']);
				$set_names = explode("\n", $txt[\'smileys_none\'] . "\n" . $txt[\'smileys_forum_board_default\'] . "\n" . $modSettings[\'smiley_sets_names\']);
				foreach ($context[\'smiley_sets\'] as $i => $set)
				{
					$context[\'smiley_sets\'][$i] = array(
						\'id\' => $set,
						\'name\' => $set_names[$i],
						\'selected\' => $set == $context[\'member\'][\'smiley_set\'][\'id\']
					);

					if ($context[\'smiley_sets\'][$i][\'selected\'])
						$context[\'member\'][\'smiley_set\'][\'name\'] = $set_names[$i];
				}
				return true;
			'),
			'input_validate' => create_function('&$value', '
				global $modSettings;

				$smiley_sets = explode(\',\', $modSettings[\'smiley_sets_known\']);
				if (!in_array($value, $smiley_sets) && $value != \'none\')
					$value = \'\';
				return true;
			'),
		),
		// Pretty much a dummy entry - it populates all the theme settings.
		'theme_settings' => array(
			'type' => 'callback',
			'callback_func' => 'theme_settings',
			'permission' => 'profile_extra',
			'is_dummy' => true,
			'preload' => create_function('', '
				loadLanguage(\'Settings\');
				return true;
			'),
		),
		'time_format' => array(
			'type' => 'callback',
			'callback_func' => 'timeformat_modify',
			'permission' => 'profile_extra',
			'preload' => create_function('', '
				global $context, $user_info, $txt, $cur_profile;

				$context[\'easy_timeformats\'] = array(
					array(\'format\' => \'\', \'title\' => $txt[\'timeformat_default\']),
					array(\'format\' => \'%B %d, %Y, %I:%M:%S %p\', \'title\' => $txt[\'timeformat_easy1\']),
					array(\'format\' => \'%B %d, %Y, %H:%M:%S\', \'title\' => $txt[\'timeformat_easy2\']),
					array(\'format\' => \'%Y-%m-%d, %H:%M:%S\', \'title\' => $txt[\'timeformat_easy3\']),
					array(\'format\' => \'%d %B %Y, %H:%M:%S\', \'title\' => $txt[\'timeformat_easy4\']),
					array(\'format\' => \'%d-%m-%Y, %H:%M:%S\', \'title\' => $txt[\'timeformat_easy5\'])
				);

				$context[\'member\'][\'time_format\'] = $cur_profile[\'time_format\'];
				$context[\'current_forum_time\'] = timeformat(time() - $user_info[\'time_offset\'] * 3600, false);
				$context[\'current_forum_time_hour\'] = (int) strftime(\'%H\', forum_time(false));
				return true;
			'),
		),
		'time_offset' => array(
			'type' => 'callback',
			'callback_func' => 'timeoffset_modify',
			'permission' => 'profile_extra',
			'preload' => create_function('', '
				global $context, $cur_profile;
				$context[\'member\'][\'time_offset\'] = $cur_profile[\'time_offset\'];
				return true;
			'),
			'input_validate' => create_function('&$value', '
				// Validate the time_offset...
				$value = strtr($value, \',\', \'.\');

				if ($value < -23.5 || $value > 23.5)
					return \'bad_offset\';

				return true;
			'),
		),
		'usertitle' => array(
			'type' => 'text',
			'label' => $txt['custom_title'],
			'size' => 50,
			'permission' => 'profile_title',
			'enabled' => !empty($modSettings['titlesEnable']),
		),
		'website_title' => array(
			'type' => 'text',
			'label' => $txt['website_title'],
			'subtext' => $txt['include_website_url'],
			'size' => 50,
			'permission' => 'profile_extra',
			'link_with' => 'website',
		),
		'website_url' => array(
			'type' => 'text',
			'label' => $txt['website_url'],
			'subtext' => $txt['complete_url'],
			'size' => 50,
			'permission' => 'profile_extra',
			// Fix the URL...
			'input_validate' => create_function('&$value', '

				if (strlen(trim($value)) > 0 && strpos($value, \'://\') === false)
					$value = \'http://\' . $value;
				if (strlen($value) < 8)
					$value = \'\';
				return true;
			'),
			'link_with' => 'website',
		),
		'yim' => array(
			'type' => 'text',
			'label' => $txt['yim'],
			'subtext' => $txt['your_yim'],
			'size' => 24,
			'input_attr' => array('maxlength="32"'),
			'permission' => 'profile_extra',
		),
	);

	$disabled_fields = !empty($modSettings['disabled_profile_fields']) ? explode(',', $modSettings['disabled_profile_fields']) : array();
	// For each of the above let's take out the bits which don't apply - to save memory and security!
	foreach ($profile_fields as $key => $field)
	{
		// Do we have permission to do this?
		if (isset($field['permission']) && !allowedTo($field['permission'] . '_' . ($context['user']['is_owner'] ? 'own' : 'any')) && !allowedTo($field['permission']))
			unset($profile_fields[$key]);

		// Is it enabled?
		if (isset($field['enabled']) && !$field['enabled'])
			unset($profile_fields[$key]);

		// Is it specifically disabled?
		if (in_array($key, $disabled_fields) || (isset($field['link_with']) && in_array($field['link_with'], $disabled_fields)))
			unset($profile_fields[$key]);
	}
}

// Setup the context for a page load!
function setupProfileContext($fields)
{
	global $profile_fields, $context, $cur_profile, $smfFunc;

	// Make sure we have this!
	loadProfileFields(true);

	// First check for any linked sets.
	foreach ($profile_fields as $key => $field)
		if (isset($field['link_with']) && in_array($field['link_with'], $fields))
			$fields[] = $key;

	// Some default bits.
	$context['profile_prehtml'] = '';
	$context['profile_posthtml'] = '';
	$context['profile_javascript'] = '';
	$context['profile_onsubmit_javascript'] = '';

	$i = 0;
	$last_type = '';
	foreach ($fields as $key => $field)
	{
		if (isset($profile_fields[$field]))
		{
			// Shortcut.
			$cur_field = &$profile_fields[$field];

			// Does it have a preload and does that preload succeed?
			if (isset($cur_field['preload']) && !$cur_field['preload']())
				continue;

			// If this is anything but complex we need to do more cleaning!
			if ($cur_field['type'] != 'callback' && $cur_field['type'] != 'hidden')
			{
				if (!isset($cur_field['label']))
					$cur_field['label'] = isset($txt[$field]) ? $txt[$field] : $field;

				// Everything has a value!
				if (!isset($cur_field['value']))
				{
					$cur_field['value'] = isset($cur_profile[$field]) ? $cur_profile[$field] : '';
				}

				// Any input attributes?
				$cur_field['input_attr'] = !empty($cur_field['input_attr']) ? implode(',', $cur_field['input_attr']) : '';
			}

			// Was there an error with this field on posting?
			if (isset($context['profile_errors'][$field]))
				$cur_field['is_error'] = true;

			// Any javascript stuff?
			if (!empty($cur_field['js_submit']))
				$context['profile_onsubmit_javascript'] .= $cur_field['js_submit'];
			if (!empty($cur_field['js']))
				$context['profile_javascript'] .= $cur_field['js'];

			// Any template stuff?
			if (!empty($cur_field['prehtml']))
				$context['profile_prehtml'] .= $cur_field['prehtml'];
			if (!empty($cur_field['posthtml']))
				$context['profile_posthtml'] .= $cur_field['posthtml'];

			// Finally put it into context?
			if ($cur_field['type'] != 'hidden')
			{
				$last_type = $cur_field['type'];
				$context['profile_fields'][$field] = &$profile_fields[$field];
			}
		}
		// Bodge in a line break - without doing two in a row ;)
		elseif ($field == 'hr' && $last_type != 'hr' && $last_type != '')
		{
			$last_type = 'hr';
			$context['profile_fields'][$i++]['type'] = 'hr';
		}
	}

	// Free up some memory.
	unset($profile_fields);
}

// Save the profile changes.
function saveProfileFields()
{
	global $profile_fields, $profile_vars, $context, $old_profile, $post_errors, $sourcedir, $modSettings, $cur_profile, $smfFunc;

	// Load them up.
	loadProfileFields();

	// This makes things easier...
	$old_profile = $cur_profile;

	// This allows variables to call activities when they save - by default just to reload their settings
	$context['profile_execute_on_save'] = array();
	if ($context['user']['is_owner'])
		$context['profile_execute_on_save']['reload_user'] = 'profileReloadUser';

	// Cycle through the profile fields working out what to do!
	foreach ($profile_fields as $key => $field)
	{
		if (!isset($_POST[$key]) || !empty($field['is_dummy']))
			continue;

		// What gets updated?
		$db_key = isset($field['save_key']) ? $field['save_key'] : $key;

		// Right - we have something that is enabled, we can act upon and has a value posted to it. Does it have a validation function?
		if (isset($field['input_validate']))
		{
			$is_valid = $field['input_validate']($_POST[$key]);
			// An error occured - set it as such!
			if ($is_valid !== true)
			{
				// Is this an actual error?
				if ($is_valid !== false)
				{
					$post_errors[$key] = $is_valid;
					$profile_fields[$key]['is_error'] = $is_valid;
				}
				// Retain the old value.
				$cur_profile[$key] = $smfFunc['db_unescape_string']($_POST[$key]);
				continue;
			}
		}

		// Are we doing a cast?
		$field['cast_type'] = empty($field['cast_type']) ? $field['type'] : $field['cast_type'];

		// Finally, clean up certain types.
		if ($field['cast_type'] == 'int')
			$_POST[$key] = (int) $_POST[$key];
		elseif ($field['cast_type'] == 'float')
			$_POST[$key] = (float) $_POST[$key];
		elseif ($field['cast_type'] == 'check')
			$_POST[$key] = !empty($_POST[$key]) ? 1 : 0;

		// If we got here we're doing OK.
		if ($field['type'] != 'hidden' && (!isset($old_profile[$key]) || $_POST[$key] != $old_profile[$key]))
		{
			// Set the save variable.
			$profile_vars[$db_key] = in_array($field['cast_type'], array('int', 'float', 'check')) ? $_POST[$key] : '\'' . $_POST[$key] . '\'';
			// And update the user profile.
			$cur_profile[$key] = $smfFunc['db_unescape_string']($_POST[$key]);
		}
	}

	//!!! Temporary
	if ($context['user']['is_owner'])
		$changeOther = allowedTo(array('profile_extra_any', 'profile_extra_own'));
	else
		$changeOther = allowedTo('profile_extra_any');
	if ($changeOther)
	{
		makeThemeChanges($context['id_member'], isset($_POST['id_theme']) ? (int) $_POST['id_theme'] : $old_profile['id_theme']);
		if (!empty($_REQUEST['sa']))
			makeCustomFieldChanges($context['id_member'], $_REQUEST['sa']);
	}

	// Free memory!
	unset($profile_fields);
}

// Save the profile changes....
function saveProfileChanges(&$profile_vars, &$post_errors, $memID)
{
	global $db_prefix, $user_info, $txt, $modSettings, $user_profile;
	global $context, $settings, $sourcedir;
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
	);
	$profile_floats = array(
	);
	$profile_strings = array(
		'buddy_list',
		'ignore_boards',
	);

	if (isset($_POST['sa']) && $_POST['sa'] == 'ignoreboards' && empty($_POST['ignore_brd']))
			$_POST['ignore_brd'] = array();
	if (isset($_POST['ignore_brd']))
	{
		if (!is_array($_POST['ignore_brd']))
			$_POST['ignore_brd'] = array ( $_POST['ignore_brd'] );

		foreach ($_POST['ignore_brd'] as $k => $d )
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

	// Here's where we sort out all the 'other' values...
	if ($changeOther)
	{
		makeThemeChanges($memID, isset($_POST['id_theme']) ? (int) $_POST['id_theme'] : $old_profile['id_theme']);
		//makeAvatarChanges($memID, $post_errors);
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
}

// Make any theme changes that are sent with the profile..
function makeThemeChanges($memID, $id_theme)
{
	global $db_prefix, $modSettings, $smfFunc, $context;

	// These are the theme changes...
	$themeSetArray = array();
	if (isset($_POST['options']) && is_array($_POST['options']))
	{
		foreach ($_POST['options'] as $opt => $val)
		{
			// These need to be controlled.
			if ($opt == 'topics_per_page' || $opt == 'messages_per_page')
				$val = max(0, min($val, 50));

			$themeSetArray[] = array($memID, $id_theme, "SUBSTRING('" . $smfFunc['db_escape_string']($opt) . "', 1, 255)", "SUBSTRING('" . (is_array($val) ? implode(',', $val) : $val) . "', 1, 65534)");
		}
	}

	$erase_options = array();
	if (isset($_POST['default_options']) && is_array($_POST['default_options']))
		foreach ($_POST['default_options'] as $opt => $val)
		{
			// These need to be controlled.
			if ($opt == 'topics_per_page' || $opt == 'messages_per_page')
				$val = max(0, min($val, 50));

			$themeSetArray[] = array($memID, 1, "SUBSTRING('" . $smfFunc['db_escape_string']($opt) . "', 1, 255)", "SUBSTRING('" . (is_array($val) ? implode(',', $val) : $val) . "', 1, 65534)");
			$erase_options[] = $smfFunc['db_escape_string']($opt);
		}

	// If themeSetArray isn't still empty, send it to the database.
	if (empty($context['password_auth_failed']))
	{
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

// Save any changes to the custom profile fields...
function makeCustomFieldChanges($memID, $area)
{
	global $db_prefix, $context, $smfFunc, $user_profile;

	$where = $area == 'register' ? "show_reg = 1" : "show_profile = '$area'";

	// Load the fields we are saving too - make sure we save valid data (etc).
	$request = $smfFunc['db_query']('', "
		SELECT col_name, field_name, field_desc, field_type, field_length, field_options, default_value, mask, private
		FROM {$db_prefix}custom_fields
		WHERE $where
			AND active = 1", __FILE__, __LINE__);
	$changes = array();
	while ($row = $smfFunc['db_fetch_assoc']($request))
	{
		if ($row['private'] != 0 && !allowedTo('admin_forum'))
			continue;

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
	if (!empty($changes) && empty($context['password_auth_failed']))
		$smfFunc['db_insert']('replace',
			"{$db_prefix}themes",
			array('id_theme', 'variable', 'value', 'id_member'),
			$changes,
			array('id_theme', 'variable', 'id_member'), __FILE__, __LINE__
		);
}

// Activate an account.
function activateAccount($memID)
{
	global $sourcedir, $context, $user_profile, $modSettings;

	isAllowedTo('moderate_forum');

	if (isset($_REQUEST['save']) && isset($user_profile[$memID]['is_activated']) && $user_profile[$memID]['is_activated'] != 1)
	{
		// If we are approving the deletion of an account, we do something special ;)
		if ($user_profile[$memID]['is_activated'] == 4)
		{
			require_once($sourcedir . '/Subs-Members.php');
			deleteMembers($context['id_member']);
			redirectexit();
		}

		if (isset($modSettings['integrate_activate']) && function_exists($modSettings['integrate_activate']))
			call_user_func($modSettings['integrate_activate'], $user_profile[$memID]['member_name']);

		// Actually update this member now, as it guarantees the unapproved count can't get corrupted.
		updateMemberData($context['id_member'], array('is_activated' => $user_profile[$memID]['is_activated'] >= 10 ? '11' : '1', 'validation_code' => '\'\''));

		// If we are doing approval, update the stats for the member just incase.
		if (in_array($user_profile[$memID]['is_activated'], array(3, 4, 13, 14)))
			updateSettings(array('unapprovedMembers' => ($modSettings['unapprovedMembers'] > 1 ? $modSettings['unapprovedMembers'] - 1 : 0)));

		// Make sure we update the stats too.
		updateStats('member', false);
	}

	// Leave it be...
	redirectexit('action=profile;sa=summary;u=' . $memID);
}

// View a summary.
function summary($memID)
{
	global $context, $memberContext, $txt, $modSettings, $user_info, $user_profile, $sourcedir, $db_prefix, $scripturl, $smfFunc;

	// Attempt to load the member's profile data.
	if (!loadMemberContext($memID) || !isset($memberContext[$memID]))
		fatal_lang_error('not_a_user', false);

	// Set up the stuff and load the user.
	$context += array(
		'page_title' => $txt['profile_of'] . ' ' . $memberContext[$memID]['name'],
		'can_send_pm' => allowedTo('pm_send'),
		'can_have_buddy' => allowedTo('profile_identity_own') && !empty($modSettings['enable_buddylist']),
		'can_issue_warning' => allowedTo('issue_warning') && $modSettings['warning_settings']{0} == 1,
	);
	$context['member'] = &$memberContext[$memID];

	// Are there things we don't show?
	$context['disabled_fields'] = isset($modSettings['disabled_profile_fields']) ? array_flip(explode(',', $modSettings['disabled_profile_fields'])) : array();

	// See if they have broken any warning levels...
	list ($modSettings['warning_enable'], $modSettings['user_limit']) = explode(',', $modSettings['warning_settings']);
	if (!empty($modSettings['warning_mute']) && $modSettings['warning_mute'] <= $context['member']['warning'])
		$context['warning_status'] = $txt['profile_warning_is_muted'];
	elseif (!empty($modSettings['warning_moderate']) && $modSettings['warning_moderate'] <= $context['member']['warning'])
		$context['warning_status'] = $txt['profile_warning_is_moderation'];
	elseif (!empty($modSettings['warning_watch']) && $modSettings['warning_watch'] <= $context['member']['warning'])
		$context['warning_status'] = $txt['profile_warning_is_watch'];

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
			" . ($context['user']['is_owner'] ? '' : 'AND m.approved = 1'), __FILE__, __LINE__);
	list ($msgCount) = $smfFunc['db_fetch_row']($request);
	$smfFunc['db_free_result']($request);

	$request = $smfFunc['db_query']('', "
		SELECT MIN(id_msg), MAX(id_msg)
		FROM {$db_prefix}messages AS m
		WHERE m.id_member = $memID
			" . ($context['user']['is_owner'] ? '' : 'AND m.approved = 1'), __FILE__, __LINE__);
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
				" . ($context['user']['is_owner'] ? '' : 'AND m.approved = 1 AND t.approved = 1') . "
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
	$boardsAllowed = boardsAllowedTo('view_attachments');
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
			" . ($context['user']['is_owner'] ? '' : 'AND m.approved = 1'), __FILE__, __LINE__);
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
	$context['sort_direction'] = isset($_GET['asc']) ? 'up' : 'down';

	$sort =	$sortTypes[$context['sort_order']];

	// Let's get ourselves a lovely page index.
	$context['page_index'] = constructPageIndex($scripturl . '?action=profile;u=' . $memID . ';sa=showPosts;attach;sort=' . $sort . ($context['sort_direction'] == 'up' ? ';asc' : ''), $context['start'], $attachCount, $maxIndex);

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
			" . ($context['user']['is_owner'] ? '' : 'AND m.approved = 1') . "
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

	// Can we email the user direct?
	$context['can_moderate_forum'] = allowedTo('moderate_forum');

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
		$_POST['new_buddy'] = strtr($smfFunc['db_escape_string']($smfFunc['htmlspecialchars']($smfFunc['db_unescape_string']($_POST['new_buddy']), ENT_QUOTES)), array('&quot;' => '"'));
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
	$max_percent = 0;
	while ($row = $smfFunc['db_fetch_assoc']($result))
	{
		$context['popular_boards'][$row['id_board']] = array(
			'id' => $row['id_board'],
			'posts' => $row['message_count'],
			'href' => $scripturl . '?board=' . $row['id_board'] . '.0',
			'link' => '<a href="' . $scripturl . '?board=' . $row['id_board'] . '.0">' . $row['name'] . '</a>',
			'posts_percent' => $row['num_posts'] == 0 ? 0 : ($row['message_count'] * 100) / $row['num_posts'],
			'total_posts' => $row['num_posts'],
		);

		$max_percent = max($max_percent, $context['popular_boards'][$row['id_board']]['posts_percent']);
	}
	$smfFunc['db_free_result']($result);

	// Now that we know the total, calculate the percentage.
	foreach ($context['popular_boards'] as $id_board => $board_data)
		$context['popular_boards'][$id_board]['posts_percent'] = $max_percent == 0 ? 0 : comma_format(($board_data['posts_percent'] / $max_percent) * 100, 2);

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
	$max_percent = 0;
	while ($row = $smfFunc['db_fetch_assoc']($result))
	{
		$max_percent = max($max_percent, $row['percentage']);
		$context['board_activity'][$row['id_board']] = array(
			'id' => $row['id_board'],
			'href' => $scripturl . '?board=' . $row['id_board'] . '.0',
			'link' => '<a href="' . $scripturl . '?board=' . $row['id_board'] . '.0">' . $row['name'] . '</a>',
			'percent' => $row['percentage'],
		);
	}
	$smfFunc['db_free_result']($result);

	foreach ($context['board_activity'] as $id_board => $board_data)
	{
		$context['board_activity'][$id_board]['relative_percent'] = $max_percent == 0 ? 0 : ($board_data['percent'] / $max_percent) * 100;
		$context['board_activity'][$id_board]['percent'] = $board_data['percent'];
	}

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
	global $context, $txt;

	loadThemeOptions($memID);
	loadCustomFields($memID, 'account');

	$context['sub_template'] = 'edit_options';
	$context['page_desc'] = $txt['account_info'];

	setupProfileContext(
		array(
			'member_name', 'real_name', 'date_registered', 'posts', 'lngfile', 'hr',
			'id_group', 'hr',
			'email_address', 'hide_email', 'show_online', 'hr',
			'passwrd1', 'passwrd2', 'hr',
			'secret_question', 'secret_answer',
		)
	);
}

function forumProfile($memID)
{
	global $context, $user_profile, $user_info, $txt, $modSettings;

	loadThemeOptions($memID);
	loadCustomFields($memID, 'forumProfile');

	$context['sub_template'] = 'edit_options';
	$context['page_desc'] = $txt['forumProfile_info'];

	setupProfileContext(
		array(
			'avatar_choice', 'personal_text', 'hr',
			'bday1', 'location', 'gender', 'hr',
			'icq', 'aim', 'msn', 'yim', 'hr',
			'usertitle', 'signature', 'hr',
			'karma_good', 'hr',
			'website_title', 'website_url',
		)
	);
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

	loadThemeOptions($memID);
	loadCustomFields($memID, 'theme');

	$context['sub_template'] = 'edit_options';
	$context['page_desc'] = $txt['theme_info'];

	setupProfileContext(
		array(
			'id_theme', 'smiley_set', 'hr',
			'time_format', 'time_offset', 'hr',
			'theme_settings',
		)
	);
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
	$context['member'] = array(
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
		if (($row['id_group'] == $context['primary_group'] && $row['group_type'] != 0) || ($row['hidden'] != 2 && $context['primary_group'] == 0 && in_array($row['id_group'], $groups)))
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
	global $user_info, $sourcedir, $context, $db_prefix, $user_profile, $modSettings, $txt, $smfFunc, $scripturl;

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

			// Does the group type match what we're doing - are we trying to request a non-requestable group?
			if ($changeType == 'request' && $row['group_type'] != 1)
				fatal_lang_error('no_access');
			// What about leaving a requestable group we are not a member of?
			elseif ($changeType == 'free' && $row['group_type'] == 1 && $old_profile['id_group'] != $row['id_group'] && !isset($addGroups[$row['id_group']]))
				fatal_lang_error('no_access');
			elseif ($changeType == 'free' && $row['group_type'] != 2 && $row['group_type'] != 1)
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
			$canChangePrimary = false;
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
			while ($row = $smfFunc['db_fetch_assoc']($request))
			{
				$replacements = array(
					'RECPNAME' => $row['member_name'],
					'APPYNAME' => $old_profile['member_name'],
					'GROUPNAME' => $group_name,
					'REASON' => $reason,
					'MODLINK' => $scripturl . '?action=groups;sa=requests',
				);

				$emaildata = loadEmailTemplate('request_membership', $replacements, $row['lngfile']);
				sendmail($row['email_address'], $emaildata['subject'], $emaildata['body']);
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
	foreach ($addGroups as $id => $dummy)
		if (empty($id))
			unset($addGroups[$id]);
	$addGroups = implode(',', array_flip($addGroups));

	// Ensure that we don't cache permissions if the group is changing.
	if ($context['user']['is_owner'])
		$_SESSION['mc']['time'] = 0;
	else
		updateSettings(array('settings_updated' => time()));

	updateMemberData($memID, array('id_group' => $newPrimary, 'additional_groups' => "'$addGroups'"));

	return $changeType;
}

// Issue/manage a users warning status.
function issueWarning($memID)
{
	global $txt, $scripturl, $modSettings, $db_prefix, $user_info;
	global $context, $cur_profile, $memberContext, $smfFunc, $sourcedir;

	// Get all the actual settings.
	list ($modSettings['warning_enable'], $modSettings['user_limit']) = explode(',', $modSettings['warning_settings']);

	// Doesn't hurt to be overly cautious.
	if (empty($modSettings['warning_enable']) || $context['user']['is_owner'] || !allowedTo('issue_warning'))
		fatal_lang_error('no_access', false);

	// Make sure things which are disabled stay disabled.
	$modSettings['warning_watch'] = !empty($modSettings['warning_watch']) ? $modSettings['warning_watch'] : 110;
	$modSettings['warning_moderate'] = !empty($modSettings['warning_moderate']) ? $modSettings['warning_moderate'] : 110;
	$modSettings['warning_mute'] = !empty($modSettings['warning_mute']) ? $modSettings['warning_mute'] : 110;

	$context['warning_limit'] = allowedTo('admin_forum') ? 0 : $modSettings['user_limit'];
	$context['member']['warning'] = $cur_profile['warning'];
	$context['member']['name'] = $cur_profile['member_name'];

	// What are the limits we can apply?
	$context['min_allowed'] = 0;
	$context['max_allowed'] = 100;
	if ($context['warning_limit'] > 0)
	{
		// Make sure we cannot go outside of our limit for the day.
		$request = $smfFunc['db_query']('', "
			SELECT SUM(counter)
			FROM {$db_prefix}log_comments
			WHERE id_recipient = $memID
				AND id_member = $user_info[id]
				AND comment_type = 'warning'
				AND log_time > " . (time() - 86400), __FILE__, __LINE__);
		list ($current_applied) = $smfFunc['db_fetch_row']($request);
		$smfFunc['db_free_result']($request);

		$context['min_allowed'] = max(0, $cur_profile['warning'] - $current_applied - $context['warning_limit']);
		$context['max_allowed'] = min(100, $cur_profile['warning'] - $current_applied + $context['warning_limit']);
	}

	// Are we saving?
	if (isset($_POST['save']))
	{
		// Security is good here.
		checkSession('post');

		// This cannot be empty!
		$_POST['warn_reason'] = trim($_POST['warn_reason']);
		if ($_POST['warn_reason'] == '')
			fatal_lang_error('warning_no_reason');
		$_POST['warn_reason'] = $smfFunc['htmlspecialchars']($_POST['warn_reason']);

		// If the value hasn't changed it's either no JS or a real no change (Which this will pass)
		if ($_POST['warning_level'] == 'SAME')
			$_POST['warning_level'] = $_POST['warning_level_nojs'];

		$_POST['warning_level'] = (int) $_POST['warning_level'];
		$_POST['warning_level'] = max(0, min(100, $_POST['warning_level']));
		if ($_POST['warning_level'] < $context['min_allowed'])
			$_POST['warning_level'] = $context['min_allowed'];
		elseif ($_POST['warning_level'] > $context['max_allowed'])
			$_POST['warning_level'] = $context['max_allowed'];

		// Do we actually have to issue them with a PM?
		$id_notice = 0;
		if (!empty($_POST['warn_notify']))
		{
			$_POST['warn_sub'] = trim($_POST['warn_sub']);
			$_POST['warn_body'] = trim($_POST['warn_body']);
			if (empty($_POST['warn_sub']) || empty($_POST['warn_body']))
				fatal_lang_error('warning_notify_blank');

			// Send the PM!
			require_once($sourcedir . '/Subs-Post.php');
			$from = array(
				'id' => 0,
				'name' => $smfFunc['db_escape_string']($context['forum_name']),
				'username' => $smfFunc['db_escape_string']($context['forum_name']),
			);
			sendpm(array('to' => array($memID), 'bcc' => array()), $_POST['warn_sub'], $_POST['warn_body'], false, $from);

			// Log the notice!
			$smfFunc['db_query']('', "
				INSERT INTO {$db_prefix}log_member_notices
					(subject, body)
				VALUES
					(SUBSTRING('$_POST[warn_sub]', 1, 255), SUBSTRING('$_POST[warn_body]', 1, 65534))", __FILE__, __LINE__);
			$id_notice = $smfFunc['db_insert_id']("{$db_prefix}log_member_notices", 'id_notice');
		}

		// Just incase - make sure notice is valid!
		$id_notice = (int) $id_notice;

		// What have we changed?
		$level_change = $_POST['warning_level'] - $cur_profile['warning'];

		// Log what we've done!
		$smfFunc['db_query']('', "
			INSERT INTO {$db_prefix}log_comments
				(id_member, member_name, comment_type, id_recipient, recipient_name, log_time, id_notice,
					counter, body)
			VALUES
				($user_info[id], '" . $smfFunc['db_escape_string']($user_info['name']) . "', 'warning',
				$memID, '" . $smfFunc['db_escape_string']($cur_profile['real_name']) . "', " . time() . ",
				$id_notice, $level_change, SUBSTRING('$_POST[warn_reason]', 1, 65534))", __FILE__, __LINE__);

		// Make the change.
		updateMemberData($memID, array('warning' => $_POST['warning_level']));

		redirectexit('action=profile;u=' . $memID);
	}

	$context['page_title'] = $txt['profile_issue_warning'];

	// Work our the various levels.
	$context['level_effects'] = array(
		0 => $txt['profile_warning_effect_none'],
		$modSettings['warning_watch'] => $txt['profile_warning_effect_watch'],
		$modSettings['warning_moderate'] => $txt['profile_warning_effect_moderation'],
		$modSettings['warning_mute'] => $txt['profile_warning_effect_mute'],
	);
	$context['current_level'] = 0;
	foreach ($context['level_effects'] as $limit => $dummy)
		if ($cur_profile['warning'] >= $limit)
			$context['current_level'] = $limit;

	// Load up all the old warnings - count first!
	$request = $smfFunc['db_query']('', "
		SELECT COUNT(*)
		FROM {$db_prefix}log_comments
		WHERE id_recipient = $memID
			AND comment_type = 'warning'", __FILE__, __LINE__);
	list ($context['total_warnings']) = $smfFunc['db_fetch_row']($request);
	$smfFunc['db_free_result']($request);

	// Make the page index.
	$context['start'] = (int) $_REQUEST['start'];
	$perPage = (int) $modSettings['defaultMaxMessages'];
	$context['page_index'] = constructPageIndex($scripturl . '?action=profile;u=' . $memID . ';sa=issueWarning', $context['start'], $context['total_warnings'], $perPage);

	// Now do the data itself.
	$request = $smfFunc['db_query']('', "
		SELECT IFNULL(mem.id_member, 0) AS id_member, IFNULL(mem.real_name, lc.member_name) AS member_name,
			lc.log_time, lc.body, lc.counter, lc.id_notice
		FROM {$db_prefix}log_comments AS lc
			LEFT JOIN {$db_prefix}members AS mem ON (mem.id_member = lc.id_member)
		WHERE lc.id_recipient = $memID
			AND lc.comment_type = 'warning'
		ORDER BY log_time DESC
		LIMIT $context[start], $perPage", __FILE__, __LINE__);
	$context['previous_warnings'] = array();
	while ($row = $smfFunc['db_fetch_assoc']($request))
	{
		$context['previous_warnings'][] = array(
			'issuer' => array(
				'id' => $row['id_member'],
				'link' => $row['id_member'] ? ('<a href="' . $scripturl . '?action=profile;u=' . $row['id_member'] . '">' . $row['member_name'] . '</a>') : $row['member_name'],
			),
			'time' => timeformat($row['log_time']),
			'reason' => $row['body'],
			'counter' => $row['counter'] > 0 ? '+' . $row['counter'] : $row['counter'],
			'id_notice' => $row['id_notice'],
		);
	}
	$smfFunc['db_free_result']($request);
}

// Present a screen to make sure the user wants to be deleted
function deleteAccount($memID)
{
	global $txt, $context, $user_info, $modSettings, $cur_profile, $smfFunc;

	if (!$context['user']['is_owner'])
		isAllowedTo('profile_remove_any');
	elseif (!allowedTo('profile_remove_any'))
		isAllowedTo('profile_remove_own');

	// Permissions for removing stuff...
	$context['can_delete_posts'] = !$context['user']['is_owner'] && allowedto('moderate_forum');

	// Can they do this, or will they need approval?
	$context['needs_approval'] = $context['user']['is_owner'] && !empty($modSettings['approveAccountDeletion']) && !allowedTo('moderate_forum');
	$context['page_title'] = $txt['deleteAccount'] . ': ' . $cur_profile['real_name'];
}

function deleteAccount2($profile_vars, $post_errors, $memID)
{
	global $user_info, $sourcedir, $context, $db_prefix, $cur_profile, $modSettings, $smfFunc;

	// !!! Add a way to delete pms as well?

	if (!$context['user']['is_owner'])
		isAllowedTo('profile_remove_any');
	elseif (!allowedTo('profile_remove_any'))
		isAllowedTo('profile_remove_own');

	checkSession();

	$old_profile = &$cur_profile;

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

function loadThemeOptions($memID)
{
	global $context, $options, $db_prefix, $cur_profile, $smfFunc;

	if (isset($_POST['default_options']))
		$_POST['options'] = isset($_POST['options']) ? $_POST['options'] + $_POST['default_options'] : $_POST['default_options'];

	if ($context['user']['is_owner'])
	{
		$context['member']['options'] = $options;
		foreach ($context['member']['options'] as $k => $v)
			if (isset($_POST['options'][$k]))
				$context['member']['options'][$k] = $_POST['options'][$k];
	}
	else
	{
		$request = $smfFunc['db_query']('', "
			SELECT id_member, variable, value
			FROM {$db_prefix}themes
			WHERE id_theme IN (1, " . (int) $cur_profile['id_theme'] . ")
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
	if (!allowedTo('admin_forum'))
		$where .= $area == 'summary' ? ' AND private != 2' : ' AND private = 0';

	if ($area == 'register')
		$where .= ' AND show_reg = 1';
	elseif ($area != 'summary')
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
		$exists = $memID && isset($user_profile[$memID], $user_profile[$memID]['options'][$row['col_name']]);
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
	global $txt, $user_info, $db_prefix, $context, $db_prefix, $modSettings, $smfFunc, $cur_profile;

	// Have the admins enabled this option?
	if (empty($modSettings['allow_ignore_boards']))
		fatal_lang_error('ignoreboards_disallowed', 'user');

	// Find all the boards this user is allowed to see.
	$request = $smfFunc['db_query']('', "
		SELECT b.id_cat, c.name AS cat_name, b.id_board, b.name, b.child_level,
			". (!empty($cur_profile['ignore_boards']) ? 'b.id_board IN (' . $cur_profile['ignore_boards'] . ')' : 'false') ." AS is_ignored
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

// Load all the languages for the profile.
function profileLoadLanguages()
{
	global $context, $modSettings, $settings, $cur_profile, $language, $smfFunc;

	$context['profile_languages'] = array();

	// Select the default language if the user has no language selected yet.
	$selectedLanguage = empty($cur_profile['lngfile']) ? $language : $cur_profile['lngfile'];

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

			$context['profile_languages'][$matches[1]] = $smfFunc['ucwords'](strtr($matches[1], array('_' => ' ', '-utf8' => '')));
		}
		$dir->close();
	}

	// Return whether we should proceed with this.
	return count($context['profile_languages']) > 1 ? true : false;
}

// Load all the group info for the profile.
function profileLoadGroups()
{
	global $cur_profile, $txt, $context, $smfFunc, $db_prefix, $user_settings;

	$context['member_groups'] = array(
		0 => array(
			'id' => 0,
			'name' => &$txt['no_primary_membergroup'],
			'is_primary' => $cur_profile['id_group'] == 0,
			'can_be_additional' => false,
			'can_be_primary' => true,
		)
	);
	$curGroups = explode(',', $cur_profile['additional_groups']);

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
			'is_primary' => $cur_profile['id_group'] == $row['id_group'],
			'is_additional' => in_array($row['id_group'], $curGroups),
			'can_be_additional' => true,
			'can_be_primary' => $row['hidden'] != 2,
		);
	}
	$smfFunc['db_free_result']($request);

	$context['member']['group'] = $user_settings['id_group'];

	return true;
}

// Load key signature context data.
function profileLoadSignatureData()
{
	global $modSettings, $context, $txt, $cur_profile, $smfFunc;

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

	$context['member']['signature'] = empty($cur_profile['signature']) ? '' : str_replace(array('<br />', '<', '>', '"', '\''), array("\n", '&lt;', '&gt;', '&quot;', '&#039;'), $cur_profile['signature']);

	return true;
}

// Load avatar context data.
function profileLoadAvatarData()
{
	global $context, $cur_profile, $modSettings, $scripturl;

	$context['avatar_url'] = $modSettings['avatar_url'];

	// Default context.
	$context['member']['avatar'] = array(
		'name' => $cur_profile['avatar'],
		'href' => empty($cur_profile['id_attach']) ? '' : (empty($cur_profile['attachment_type']) ? $scripturl . '?action=dlattach;attach=' . $cur_profile['id_attach'] . ';type=avatar' : $modSettings['custom_avatar_url'] . '/' . $cur_profile['filename']),
		'custom' => stristr($cur_profile['avatar'], 'http://') ? $cur_profile['avatar'] : 'http://',
		'selection' => $cur_profile['avatar'] == '' || stristr($cur_profile['avatar'], 'http://') ? '' : $cur_profile['avatar'],
		'id_attach' => $cur_profile['id_attach'],
		'filename' => $cur_profile['filename'],
		'allow_server_stored' => allowedTo('profile_server_avatar') || !$context['user']['is_owner'],
		'allow_upload' => allowedTo('profile_upload_avatar') || !$context['user']['is_owner'],
		'allow_external' => allowedTo('profile_remote_avatar') || !$context['user']['is_owner'],
	);

	// Actually - nothing?
	if (!$context['member']['avatar']['allow_external'] && !$context['member']['avatar']['allow_server_stored'] && !$context['member']['avatar']['allow_upload'])
		return false;

	if ($cur_profile['avatar'] == '' && $cur_profile['id_attach'] > 0 && $context['member']['avatar']['allow_upload'])
		$context['member']['avatar'] += array(
			'choice' => 'upload',
			'server_pic' => 'blank.gif',
			'external' => 'http://'
		);
	elseif (stristr($cur_profile['avatar'], 'http://') && $context['member']['avatar']['allow_external'])
		$context['member']['avatar'] += array(
			'choice' => 'external',
			'server_pic' => 'blank.gif',
			'external' => $cur_profile['avatar']
		);
	elseif (file_exists($modSettings['avatar_directory'] . '/' . $cur_profile['avatar']) && $context['member']['avatar']['allow_server_stored'])
		$context['member']['avatar'] += array(
			'choice' => 'server_stored',
			'server_pic' => $cur_profile['avatar'] == '' ? 'blank.gif' : $cur_profile['avatar'],
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
	return true;
}

// Save a members group.
function profileSaveGroups(&$value)
{
	global $profile_vars, $old_profile, $context, $smfFunc, $db_prefix, $cur_profile;

	// The account page allows the change of your id_group - but not to admin!.
	if (allowedTo('admin_forum') || ((int) $value != 1 && $old_profile['id_group'] != 1))
		$value = (int) $value;

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

		if (implode(',', $_POST['additional_groups']) !== $old_profile['additional_groups'])
		{
			$profile_vars['additional_groups'] = '\'' . implode(',', $_POST['additional_groups']) . '\'';
			$cur_profile['additional_groups'] = implode(',', $_POST['additional_groups']);
		}
	}

	// Too often, people remove delete their own account, or something.
	if (in_array(1, explode(',', $old_profile['additional_groups'])) || $old_profile['id_group'] == 1)
	{
		$stillAdmin = $value == 1 || (isset($_POST['additional_groups']) && in_array(1, $_POST['additional_groups']));

		// If they would no longer be an admin, look for any other...
		if (!$stillAdmin)
		{
			$request = $smfFunc['db_query']('', "
				SELECT id_member
				FROM {$db_prefix}members
				WHERE (id_group = 1 OR FIND_IN_SET(1, additional_groups))
					AND id_member != " . $context['id_member'] . "
				LIMIT 1", __FILE__, __LINE__);
			list ($another) = $smfFunc['db_fetch_row']($request);
			$smfFunc['db_free_result']($request);

			if (empty($another))
				fatal_lang_error('at_least_one_admin', 'critical');
		}
	}

	// If we are changing group status, update permission cache as necessary.
	if ($value != $old_profile['id_group'] || isset($profile_vars['additional_groups']))
	{
		if ($context['user']['is_owner'])
			$_SESSION['mc']['time'] = 0;
		else
			updateSettings(array('settings_updated' => time()));
	}

	return true;
}

// The avatar is incredibly complicated, what with the options... and what not.
function profileSaveAvatarData(&$value)
{
	global $modSettings, $sourcedir, $db_prefix, $smfFunc, $profile_vars, $cur_profile, $context;

	$memID = $context['id_member'];
	if (empty($memID) && !empty($context['password_auth_failed']))
		return false;

	// Reset the attach ID.
	$cur_profile['id_attach'] = 0;
	$cur_profile['attachment_type'] = 0;
	$cur_profile['filename'] = '';

	require_once($sourcedir . '/ManageAttachments.php');

	$uploadDir = empty($modSettings['custom_avatar_enabled']) ? $modSettings['attachmentUploadDir'] : $modSettings['custom_avatar_dir'];

	$downloadedExternalAvatar = false;
	if ($value == 'external' && allowedTo('profile_remote_avatar') && strtolower(substr($_POST['userpicpersonal'], 0, 7)) == 'http://' && strlen($_POST['userpicpersonal']) > 7 && !empty($modSettings['avatar_download_external']))
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

	if ($value == 'server_stored' && allowedTo('profile_server_avatar'))
	{
		$profile_vars['avatar'] = strtr(empty($_POST['file']) ? (empty($_POST['cat']) ? '' : $_POST['cat']) : $_POST['file'], array('&amp;' => '&'));
		$profile_vars['avatar'] = preg_match('~^([\w _!@%*=\-#()\[\]&.,]+/)?[\w _!@%*=\-#()\[\]&.,]+$~', $profile_vars['avatar']) != 0 && preg_match('/\.\./', $profile_vars['avatar']) == 0 && file_exists($modSettings['avatar_directory'] . '/' . $profile_vars['avatar']) ? ($profile_vars['avatar'] == 'blank.gif' ? '' : $profile_vars['avatar']) : '';

		// Get rid of their old avatar. (if uploaded.)
		removeAttachments('a.id_member = ' . $memID);
	}
	elseif ($value == 'external' && allowedTo('profile_remote_avatar') && strtolower(substr($_POST['userpicpersonal'], 0, 7)) == 'http://' && empty($modSettings['avatar_download_external']))
	{
		// Remove any attached avatar...
		removeAttachments('a.id_member = ' . $memID);

		$profile_vars['avatar'] = preg_replace('~action(=|%3d)(?!dlattach)~i', 'action-', $_POST['userpicpersonal']);

		if ($profile_vars['avatar'] == 'http://' || $profile_vars['avatar'] == 'http:///')
			$profile_vars['avatar'] = '';
		// Trying to make us do something we'll regret?
		elseif (substr($profile_vars['avatar'], 0, 7) != 'http://')
			return 'bad_avatar';
		// Should we check dimensions?
		elseif (!empty($modSettings['avatar_max_height_external']) || !empty($modSettings['avatar_max_width_external']))
		{
			// Now let's validate the avatar.
			$sizes = url_image_size($profile_vars['avatar']);

			if (is_array($sizes) && (($sizes[0] > $modSettings['avatar_max_width_external'] && !empty($modSettings['avatar_max_width_external'])) || ($sizes[1] > $modSettings['avatar_max_height_external'] && !empty($modSettings['avatar_max_height_external']))))
			{
				// Houston, we have a problem. The avatar is too large!!
				if ($modSettings['avatar_action_too_large'] == 'option_refuse')
					return 'bad_avatar';
				elseif ($modSettings['avatar_action_too_large'] == 'option_download_and_resize')
				{
					require_once($sourcedir . '/Subs-Graphics.php');
					if (downloadAvatar($profile_vars['avatar'], $memID, $modSettings['avatar_max_width_external'], $modSettings['avatar_max_height_external']))
					{
						$profile_vars['avatar'] = '';
						$cur_profile['id_attach'] = $modSettings['new_avatar_data']['id'];
						$cur_profile['filename'] = $modSettings['new_avatar_data']['filename'];
						$cur_profile['attachment_type'] = $modSettings['new_avatar_data']['type'];
					}
					else
						return 'bad_avatar';
				}
			}
		}
	}
	elseif (($value == 'upload' && allowedTo('profile_upload_avatar')) || $downloadedExternalAvatar)
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
				return 'bad_avatar';
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
					return 'bad_avatar';
			}
			elseif (is_array($sizes))
			{
				$extensions = array(
					'1' => 'gif',
					'2' => 'jpg',
					'3' => 'png',
					'6' => 'bmp'
				);

				$extension = isset($extensions[$sizes[2]]) ? $extensions[$sizes[2]] : 'bmp';
				$mime_type = 'image/' . ($extension == 'jpg' ? 'jpeg' : $extension);
				$destName = 'avatar_' . $memID . '.' . $extension;
				list ($width, $height) = getimagesize($_FILES['attachment']['tmp_name']);

				// Remove previous attachments this member might have had.
				removeAttachments('a.id_member = ' . $memID);

				if (!rename($_FILES['attachment']['tmp_name'], $uploadDir . '/' . $destName))
					fatal_lang_error('attach_timeout', 'critical');

				$smfFunc['db_query']('', "
					INSERT INTO {$db_prefix}attachments
						(id_member, attachment_type, filename, fileext, size, width, height, mime_type)
					VALUES ($memID, " . (empty($modSettings['custom_avatar_enabled']) ? '0' : '1') . ", '$destName', '$extension', " . filesize($uploadDir . '/' . $destName) . ", " . (int) $width . ", " . (int) $height . ", '$mime_type')", __FILE__, __LINE__);

				$cur_profile['id_attach'] = $smfFunc['db_insert_id']("{$db_prefix}attachments", 'id_attach');
				$cur_profile['filename'] = $destName;
				$cur_profile['attachment_type'] = empty($modSettings['custom_avatar_enabled']) ? 0 : 1;

				// Attempt to chmod it.
				@chmod($uploadDir . '/' . $destName, 0644);
			}
			$profile_vars['avatar'] = '';

			// Delete any temporary file.
			if (file_exists($uploadDir . '/avatar_tmp_' . $memID))
				@unlink($uploadDir . '/avatar_tmp_' . $memID);
		}
		// Selected the upload avatar option and had one already uploaded before or didn't upload one.
		else
			$profile_vars['avatar'] = '';
	}
	else
		$profile_vars['avatar'] = '';

	// Setup the profile variables so it shows things right on display!
	$cur_profile['avatar'] = $profile_vars['avatar'];

	// If we're here we've done good - but don't save based on avatar_choice - skip it ;)
	$profile_vars['avatar'] = "'" . $profile_vars['avatar'] . "'";
	return false;
}

// Validate the signature!
function profileValidateSignature(&$value)
{
	global $sourcedir, $modSettings, $smfFunc, $txt;

	require_once($sourcedir . '/Subs-Post.php');

	// Admins can do whatever they hell they want!
	if (allowedTo('admin_forum'))
	{
		// Load all the signature limits.
		list ($sig_limits, $sig_bbc) = explode(':', $modSettings['signature_settings']);
		$sig_limits = explode(',', $sig_limits);
		$disabledTags = !empty($sig_bbc) ? explode(',', $sig_bbc) : array();

		$unparsed_signature = strtr(un_htmlspecialchars($value), array("\r" => '', '&#039' => '\''));
		// Too long?
		if (!empty($sig_limits[1]) && $smfFunc['strlen']($smfFunc['db_unescape_string']($unparsed_signature)) > $sig_limits[1])
		{
			$_POST['signature'] = trim($smfFunc['db_escape_string'](htmlspecialchars($smfFunc['db_unescape_string']($smfFunc['substr']($unparsed_signature, 0, $sig_limits[1])), ENT_QUOTES)));
			$txt['profile_error_signature_max_length'] = sprintf($txt['profile_error_signature_max_length'], $sig_limits[1]);
			return 'signature_max_length';
		}
		// Too many lines?
		if (!empty($sig_limits[2]) && substr_count($unparsed_signature, "\n") >= $sig_limits[2])
		{
			$txt['profile_error_signature_max_lines'] = sprintf($txt['profile_error_signature_max_lines'], $sig_limits[2]);
			return 'signature_max_lines';
		}
		// Too many images?!
		if (!empty($sig_limits[3]) && substr_count(strtolower($unparsed_signature), '[img') > $sig_limits[3])
		{
			$txt['profile_error_signature_max_image_count'] = sprintf($txt['profile_error_signature_max_image_count'], $sig_limits[3]);
			return 'signature_max_image_count';
		}
		// What about too many smileys!
		$smiley_parsed = $unparsed_signature;
		parsesmileys($smiley_parsed);
		if (!empty($sig_limits[4]) && (substr_count(strtolower($smiley_parsed), "<img") - substr_count(strtolower($unparsed_signature), "<img")) > $sig_limits[4])
		{
			$txt['profile_error_signature_max_smileys'] = sprintf($txt['profile_error_signature_max_smileys'], $sig_limits[4]);
			return 'signature_max_smileys';
		}
		// Maybe we are abusing font sizes?
		if (!empty($sig_limits[7]) && preg_match_all('~\[size=([\d\.]+)?(px|pt|em|x-large|larger)~i', $unparsed_signature, $matches) !== false && isset($matches[2]))
		{
			foreach ($matches[1] as $ind => $size)
			{
				$limit_broke = 0;
				// Attempt to allow all sizes of abuse, so to speak.
				if ($matches[2][$ind] == 'px' && $size > $sig_limits[7])
					$limit_broke = $sig_limits[7] . 'px';
				elseif ($matches[2][$ind] == 'pt' && $size > ($sig_limits[7] * 0.75))
					$limit_broke = ((int) $sig_limits[7] * 0.75) . 'pt';
				elseif ($matches[2][$ind] == 'em' && $size > ((float) $sig_limits[7] / 16))
					$limit_broke = ((float) $sig_limits[7] / 16) . 'em';
				elseif ($matches[2][$ind] != 'px' && $matches[2][$ind] != 'pt' && $matches[2][$ind] != 'em' && $sig_limits[7] < 18)
					$limit_broke = 'large';

				if ($limit_broke)
				{
					$txt['profile_error_signature_max_font_size'] = sprintf($txt['profile_error_signature_max_font_size'], $limit_broke);
					return 'signature_max_font_size';
				}
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
					$value = str_replace(array_keys($replaces), array_values($replaces), $unparsed_signature);
			}
		}
		// Any disabled BBC?
		$disabledSigBBC = implode('|', $disabledTags);
		if (!empty($disabledSigBBC))
		{
			if (preg_match('~\[(' . $disabledSigBBC . ')~', $unparsed_signature, $matches) !== false && isset($matches[1]))
			{
				$txt['profile_error_signature_disabled_bbc'] = sprintf($txt['profile_error_signature_disabled_bbc'], implode(', ', $disabledTags));
				return 'signature_disabled_bbc';
			}
		}
	}

	preparsecode($value);
	return true;
}

// Validate an email address - requires email to have been escaped!
function profileValidateEmail($email, $memID = 0)
{
	global $smfFunc, $db_prefix, $context;

	$email = strtr($email, array('&#039;' => '\\\''));

	// Check the name and email for validity.
	if (trim($email) == '')
		return 'no_email';
	if (preg_match('~^[0-9A-Za-z=_+\-/][0-9A-Za-z=_\'+\-/\.]*@[\w\-]+(\.[\w\-]+)*(\.[\w]{2,6})$~', $smfFunc['db_unescape_string']($email)) == 0)
		return 'bad_email';

	// Email addresses should be and stay unique.
	$request = $smfFunc['db_query']('', "
		SELECT id_member
		FROM {$db_prefix}members
		WHERE " . ($memID != 0 ? "id_member != $memID AND " : '') . "
			email_address = '$email'
		LIMIT 1", __FILE__, __LINE__);
	if ($smfFunc['db_num_rows']($request) > 0)
		return 'email_taken';
	$smfFunc['db_free_result']($request);

	return true;
}

// Reload a users settings.
function profileReloadUser()
{
	global $sourcedir, $modSettings, $context, $cur_profile, $smfFunc, $profile_vars;

	// Log them back in - using the verify password as they must have matched and this one doesn't get changed by anyone!
	if (isset($_POST['passwrd2']) && $_POST['passwrd2'] != '')
	{
		require_once($sourcedir . '/Subs-Auth.php');
		setLoginCookie(60 * $modSettings['cookieTime'], $context['id_member'], sha1(sha1(strtolower($cur_profile['member_name']) . un_htmlspecialchars($smfFunc['db_unescape_string']($_POST['passwrd2']))) . $cur_profile['password_salt']));
	}

	loadUserSettings();
	writeLog();
}

// Send the user a new activation email if they need to reactivate!
function profileSendActivation()
{
	global $sourcedir, $profile_vars, $txt, $context, $scripturl, $smfFunc, $db_prefix, $cookiename;

	require_once($sourcedir . '/Subs-Post.php');

	// Shouldn't happen but just incase.
	if (empty($profile_vars['email_address']))
		return;

	$replacements = array(
		'ACTIVATIONLINK' => $scripturl . '?action=activate;u=' . $context['id_member'] . ';code=' . $profile_vars['validation_code'],
		'ACTIVATIONCODE' => $profile_vars['validation_code'],
	);

	// Send off the email.
	$emaildata = loadEmailTemplate('activate_reactivate', $replacements);
	sendmail($profile_vars['email_address'], $emaildata['subject'], $emaildata['body']);

	// Log the user out.
	$smfFunc['db_query']('', "
		DELETE FROM {$db_prefix}log_online
		WHERE id_member = " . $context['id_member'], __FILE__, __LINE__);
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

	// We're gone!
	obExit();
}

?>