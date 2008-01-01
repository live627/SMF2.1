<?php
/**********************************************************************************
* Profile-Modify.php                                                              *
***********************************************************************************
* SMF: Simple Machines Forum                                                      *
* Open-Source Project Inspired by Zef Hemel (zef@zefhemel.com)                    *
* =============================================================================== *
* Software Version:           SMF 2.0 Beta 2                                    *
* Software by:                Simple Machines (http://www.simplemachines.org)     *
* Copyright 2006-2008 by:     Simple Machines LLC (http://www.simplemachines.org) *
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

	void pmprefs(int id_member)
		// !!!

	array getAvatars(string directory, int level)
		// !!!

	void theme(int id_member)
		// !!!

	void notification(int id_member)
		// !!!

	int list_getTopicNotificationCount(int memID)
		// !!!

	array list_getTopicNotifications(int start, int items_per_page, string sort, int memID)
		// !!!

	array list_getBoardNotifications(int start, int items_per_page, string sort, int memID)
		// !!!

	void groupMembership(int id_member)
		// !!!

	void loadThemeOptions(int id_member)
		// !!!

	void ignoreboards(int id_member)
		// !!!

	void groupMembership(int id_member)
		// !!!

	Adding new fields to the profile:
	---------------------------------------------------------------------------
		// !!!
*/

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

				$profile_vars[\'birthdate\'] = $value;
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
					$value = empty($cur_profile[\'birthdate\']) ? \'0004-01-01\' : $cur_profile[\'birthdate\'];
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
					$value = $cur_profile[\'date_registered\'];
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
					WHERE id_theme = {int:id_theme}
						AND variable = {string:variable}
					LIMIT 1", array(
						\'id_theme\' => $cur_profile[\'id_theme\'],
						\'variable\' => \'name\',
					)
				);
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
					$value = $cur_profile[\'lngfile\'];
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
			'subtext' => $txt['msn_email_address'],
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
				$value = sha1(strtolower($old_profile[\'member_name\']) . un_htmlspecialchars($value));
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
		'pm_ignore_list' => array(
			'type' => 'callback',
			'callback_func' => 'ignore_list_modify',
			'preload' => create_function('', '
				global $context, $cur_profile, $smfFunc, $db_prefix;

				if ($cur_profile[\'pm_ignore_list\'] != \'*\')
				{
					$result = $smfFunc[\'db_query\'](\'\', \'
						SELECT real_name
						FROM {db_prefix}members
						WHERE FIND_IN_SET(id_member, {string:pm_ignore_list})
						LIMIT \' . (substr_count($cur_profile[\'pm_ignore_list\'], \',\') + 1),
						array(
							\'pm_ignore_list\' => $cur_profile[\'pm_ignore_list\'],
						)
					);
					$pm_ignore_list = \'\';
					while ($row = $smfFunc[\'db_fetch_assoc\']($result))
						$pm_ignore_list .= "\n" . $row[\'real_name\'];
					$smfFunc[\'db_free_result\']($result);

					$context[\'ignore_list\'] = substr($pm_ignore_list, 1);
				}
				else
					$context[\'ignore_list\'] = \'*\';

				return true;
			'),
			'permission' => 'pm_read',
			'input_validate' => create_function('&$value', '
				global $smfFunc, $db_prefix;

				// Validate and set the ignorelist...
				$value = preg_replace(\'~&amp;#(\d{4,5}|[2-9]\d{2,4}|1[2-9]\d);~\', \'&#$1;\', $value);
				$value = strtr(trim($value), array(\'\\\'\' => \'&#039;\', "\n" => "\', \'", "\r" => \'\', \'&quot;\' => \'\'));

				if (preg_match(\'~(\A|,)\*(\Z|,)~s\', $value) == 0)
				{
					$result = $smfFunc[\'db_query\'](\'\', \'
						SELECT id_member
						FROM {db_prefix}members
						WHERE member_name IN ({raw:name}) OR real_name IN ({raw:name})
						LIMIT \' . (substr_count($value, \'\\\', \\\'\') + 1),
						array(
							\'name\' => \'\\\'\' . $value . \'\\\'\',
						)
					);
					$value = \'\';
					while ($row = $smfFunc[\'db_fetch_assoc\']($result))
						$value .= $row[\'id_member\'] . \',\';
					$smfFunc[\'db_free_result\']($result);

					// !!! Did we find all the members?
					$value = substr($value, 0, -1);
				}
				else
					$value = \'*\';

				return true;
			'),
		),
		// This does ALL the pm settings
		'pm_prefs' => array(
			'type' => 'callback',
			'callback_func' => 'pm_settings',
			'permission' => 'profile_extra',
			'preload' => create_function('', '
				global $context, $cur_profile;
				$context[\'display_mode\'] = $cur_profile[\'pm_prefs\'] & 3;
				$context[\'send_email\'] = $cur_profile[\'pm_email_notify\'];
				return true;
			'),
			'input_validate' => create_function('&$value', '
				global $cur_profile, $profile_vars;

				// Simple validate and apply the two "sub settings"
				$value = max(min($value, 2), 0);

				$cur_profile[\'pm_email_notify\'] = max(min((int) $_POST[\'pm_email_notify\'], 2), 0);
				$profile_vars[\'pm_email_notify\'] = $cur_profile[\'pm_email_notify\'];

				return true;
			'),
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
				global $context, $smfFunc, $sourcedir, $cur_profile;

				$value = trim(preg_replace(\'~[\s]~\' . ($context[\'utf8\'] ? \'u\' : \'\'), \' \', $value));

				if (trim($value) == \'\')
					return \'no_name\';
				elseif ($smfFunc[\'strlen\']($value) > 60)
					return \'name_too_long\';
				elseif ($cur_profile[\'real_name\'] != $value)
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
				$cur_profile[$key] = $_POST[$key];
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
			$profile_vars[$db_key] = $_POST[$key];
			// And update the user profile.
			$cur_profile[$key] = $_POST[$key];
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
				$profile_vars[$var] = $_POST[$var] != '' ? (int) $_POST[$var] : '';
		foreach ($profile_floats as $var)
			if (isset($_POST[$var]))
				$profile_vars[$var] = (float) $_POST[$var];
		foreach ($profile_strings as $var)
			if (isset($_POST[$var]))
				$profile_vars[$var] = $_POST[$var];
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

			$themeSetArray[] = array($memID, $id_theme, $opt, is_array($val) ? implode(',', $val) : $val);
		}
	}

	$erase_options = array();
	if (isset($_POST['default_options']) && is_array($_POST['default_options']))
		foreach ($_POST['default_options'] as $opt => $val)
		{
			// These need to be controlled.
			if ($opt == 'topics_per_page' || $opt == 'messages_per_page')
				$val = max(0, min($val, 50));

			$themeSetArray[] = array($memID, 1, $opt, is_array($val) ? implode(',', $val) : $val);
			$erase_options[] = $opt;
		}

	// If themeSetArray isn't still empty, send it to the database.
	if (empty($context['password_auth_failed']))
	{
		if (!empty($themeSetArray))
		{
			$smfFunc['db_insert']('replace',
				$db_prefix . 'themes',
				array('id_member' => 'int', 'id_theme' => 'int', 'variable' => 'string-255', 'value' => 'string-65534'),
				$themeSetArray,
				array('id_member', 'id_theme', 'variable')
			);
		}

		if (!empty($erase_options))
		{
			$smfFunc['db_query']('', '
				DELETE FROM {db_prefix}themes
				WHERE id_theme != {int:id_theme}
					AND variable IN ({array_string:erase_variables})
					AND id_member = {int:id_member}',
				array(
					'id_theme' => 1,
					'id_member' => $memID,
					'erase_variables' => $erase_options
				)
			);
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

		$smfFunc['db_query']('', '
			DELETE FROM {db_prefix}log_notify
			WHERE id_board IN ({array_int:board_list})
				AND id_member = {int:selected_member}',
			array(
				'board_list' => $_POST['notify_boards'],
				'selected_member' => $memID,
			)
		);
	}

	// We are editing topic notifications......
	elseif (isset($_POST['edit_notify_topics']) && !empty($_POST['notify_topics']))
	{
		foreach ($_POST['notify_topics'] as $index => $id)
			$_POST['notify_topics'][$index] = (int) $id;

		// Make sure there are no zeros left.
		$_POST['notify_topics'] = array_diff($_POST['notify_topics'], array(0));

		$smfFunc['db_query']('', '
			DELETE FROM {db_prefix}log_notify
			WHERE id_topic IN ({array_int:topic_list})
				AND id_member = {int:selected_member}',
			array(
				'topic_list' => $_POST['notify_topics'],
				'selected_member' => $memID,
			)
		);
	}
}

// Save any changes to the custom profile fields...
function makeCustomFieldChanges($memID, $area)
{
	global $db_prefix, $context, $smfFunc, $user_profile;

	$where = $area == 'register' ? 'show_reg != 0' : 'show_profile = {string:area}';

	// Load the fields we are saving too - make sure we save valid data (etc).
	$request = $smfFunc['db_query']('', '
		SELECT col_name, field_name, field_desc, field_type, field_length, field_options, default_value, mask, private
		FROM {db_prefix}custom_fields
		WHERE ' . $where . '
			AND active = {int:is_active}',
		array(
			'is_active' => 1,
			'area' => $area,
		)
	);
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
				if ($row['mask'] == 'email' && (preg_match('~^[0-9A-Za-z=_+\-/][0-9A-Za-z=_\'+\-/\.]*@[\w\-]+(\.[\w\-]+)*(\.[\w]{2,6})$~', $value) === 0 || strlen($value) > 255))
					$value = '';
				elseif ($row['mask'] == 'number')
				{
					$value = (int) $value;
				}
				elseif (substr($row['mask'], 0, 5) == 'regex' && preg_match(substr($row['mask'], 5), $value) === 0)
					$value = '';
			}
		}

		$user_profile[$memID]['options'][$row['col_name']] = $value;
		$changes[] = array(1, $row['col_name'], $value, $memID);
	}
	$smfFunc['db_free_result']($request);

	// Make those changes!
	if (!empty($changes) && empty($context['password_auth_failed']))
		$smfFunc['db_insert']('replace',
			$db_prefix . 'themes',
			array('id_theme' => 'int', 'variable' => 'string-255', 'value' => 'string-65534', 'id_member' => 'int'),
			$changes,
			array('id_theme', 'variable', 'id_member')
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
		updateMemberData($context['id_member'], array('is_activated' => $user_profile[$memID]['is_activated'] >= 10 ? 11 : 1, 'validation_code' => ''));

		// If we are doing approval, update the stats for the member just in case.
		if (in_array($user_profile[$memID]['is_activated'], array(3, 4, 13, 14)))
			updateSettings(array('unapprovedMembers' => ($modSettings['unapprovedMembers'] > 1 ? $modSettings['unapprovedMembers'] - 1 : 0)));

		// Make sure we update the stats too.
		updateStats('member', false);
	}

	// Leave it be...
	redirectexit('action=profile;sa=summary;u=' . $memID);
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
		updateMemberData($memID, array('buddy_list' => $user_profile[$memID]['buddy_list']));

		// Redirect off the page because we don't like all this ugly query stuff to stick in the history.
		redirectexit('action=profile;u=' . $memID . ';sa=editBuddies');
	}
	elseif (isset($_POST['new_buddy']))
	{
		// Prepare the string for extraction...
		$_POST['new_buddy'] = strtr($smfFunc['htmlspecialchars']($_POST['new_buddy'], ENT_QUOTES), array('&quot;' => '"'));
		preg_match_all('~"([^"]+)"~', $_POST['new_buddy'], $matches);
		$new_buddies = array_unique(array_merge($matches[1], explode(',', preg_replace('~"([^"]+)"~', '', $_POST['new_buddy']))));

		foreach ($new_buddies as $k => $dummy)
		{
			$new_buddies[$k] = strtr(trim($new_buddies[$k]), array('\'' => '&#039;'));

			if (strlen($new_buddies[$k]) == 0)
				unset($new_buddies[$k]);
		}

		if (!empty($new_buddies))
		{
			// Now find out the id_member of the buddy.
			$request = $smfFunc['db_query']('', '
				SELECT id_member
				FROM {db_prefix}members
				WHERE member_name IN ({array_string:new_buddies}) OR real_name IN ({array_string:new_buddies})
				LIMIT ' . count($new_buddies),
				array(
					'new_buddies' => $new_buddies,
				)
			);

			// Add the new member to the buddies array.
			while ($row = $smfFunc['db_fetch_assoc']($request))
				$buddiesArray[] = (int) $row['id_member'];
			$smfFunc['db_free_result']($request);

			// Now update the current users buddy list.
			$user_profile[$memID]['buddy_list'] = implode(',', $buddiesArray);
			updateMemberData($memID, array('buddy_list' => $user_profile[$memID]['buddy_list']));
		}

		// Back to the buddy list!
		redirectexit('action=profile;u=' . $memID . ';sa=editBuddies');
	}

	// Get all the users "buddies"...
	$buddies = array();

	if (!empty($buddiesArray))
	{
		$result = $smfFunc['db_query']('', '
			SELECT id_member
			FROM {db_prefix}members
			WHERE id_member IN ({array_int:buddy_list})
			ORDER BY real_name
			LIMIT ' . (substr_count($user_profile[$memID]['buddy_list'], ',') + 1),
			array(
				'buddy_list' => $buddiesArray,
			)
		);
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

// Allow the edit of *someone elses* personal message settings.
function pmprefs($memID)
{
	global $sourcedir, $context, $txt, $scripturl;

	loadThemeOptions($memID);
	loadCustomFields($memID, 'pmprefs');

	$context['sub_template'] = 'edit_options';
	$context['page_desc'] = $txt['pm_settings_desc'];

	setupProfileContext(
		array(
			'pm_ignore_list', 'hr',
			'pm_prefs',
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
	global $txt, $db_prefix, $scripturl, $user_profile, $user_info, $context, $modSettings, $smfFunc, $sourcedir, $settings;

	// Gonna want this for the list.
	require_once($sourcedir . '/Subs-List.php');

	// Fine, start with the board list.
	$listOptions = array(
		'id' => 'board_notification_list',
		'title' => '&nbsp;<img src="' . $settings['images_url'] . '/icons/notify_sm.gif" alt="" align="top" />&nbsp;' . $txt['notifications_boards'],
		'width' => '85%',
		'no_items_label' => $txt['notifications_boards_none'] . '<br /><br />' . $txt['notifications_boards_howto'],
		'no_items_align' => 'left',
		'base_href' => $scripturl . '?action=profile;sa=notification;u=' . $memID,
		'default_sort_col' => 'board_name',
		'get_items' => array(
			'function' => 'list_getBoardNotifications',
			'params' => array(
				$memID,
			),
		),
		'columns' => array(
			'board_name' => array(
				'header' => array(
					'value' => $txt['board'],
					'style' => 'text-align: left',
				),
				'data' => array(
					'function' => create_function('$board', '
						global $settings, $txt;

						$link = $board[\'link\'];

						if ($board[\'new\'])
							$link .= \'<a href="\' . $board[\'href\'] . \'"><img src="\' . $settings[\'lang_images_url\'] . \'/new.gif" alt="\' . $txt[\'new\'] . \'" /></a>\';

						return $link;
					'),
				),
				'sort' => array(
					'default' => 'name',
					'reverse' => 'name DESC',
				),
			),
			'delete' => array(
				'header' => array(
					'value' => '<input type="checkbox" class="check" onclick="invertAll(this, this.form);" />',
					'style' => 'width: 4%;',
				),
				'data' => array(
					'sprintf' => array(
						'format' => '<input type="checkbox" name="notify_boards[]" value="%1$d" class="check" />',
						'params' => array(
							'id' => false,
						),
					),
					'style' => 'text-align: center;',
				),
			),
		),
		'form' => array(
			'href' => $scripturl . '?action=profile;save',
			'include_sort' => true,
			'include_start' => true,
			'hidden_fields' => array(
				'u' => $memID,
				'sa' => $context['menu_item_selected'],
				'sc' => $context['session_id'],
			),
		),
		'additional_rows' => array(
			array(
				'position' => 'bottom_of_list',
				'value' => '<input type="submit" name="edit_notify_boards" value="' . $txt['notifications_update'] . '" />',
				'class' => 'windowbg',
				'align' => 'right',
			),
		),
	);

	// Create the board notification list.
	createList($listOptions);

	// Now do the topic notifications.
	$listOptions = array(
		'id' => 'topic_notification_list',
		'title' => '&nbsp;<img src="' . $settings['images_url'] . '/icons/notify_sm.gif" alt="" align="top" />&nbsp;' . $txt['notifications_topics'],
		'width' => '85%',
		'items_per_page' => $modSettings['defaultMaxMessages'],
		'no_items_label' => $txt['notifications_topics_none'] . '<br /><br />' . $txt['notifications_topics_howto'],
		'no_items_align' => 'left',
		'base_href' => $scripturl . '?action=profile;sa=notification;u=' . $memID,
		'default_sort_col' => 'last_post',
		'get_items' => array(
			'function' => 'list_getTopicNotifications',
			'params' => array(
				$memID,
			),
		),
		'get_count' => array(
			'function' => 'list_getTopicNotificationCount',
			'params' => array(
				$memID,
			),
		),
		'columns' => array(
			'subject' => array(
				'header' => array(
					'value' => $txt['subject'],
				),
				'data' => array(
					'function' => create_function('$topic', '
						global $settings, $txt;

						$link = $topic[\'link\'];

						if ($topic[\'new\'])
							$link .= \'<a href="\' . $topic[\'new_href\'] . \'"><img src="\' . $settings[\'lang_images_url\'] . \'/new.gif" alt="\' . $txt[\'new\'] . \'" /></a>\';

						$link .= \'<br /><span class="smalltext"><i>\' . $txt[\'in\'] . \' \' . $topic[\'board_link\'] . \'</i></span>\';

						return $link;
					'),
				),
				'sort' => array(
					'default' => 'ms.subject',
					'reverse' => 'ms.subject DESC',
				),
			),
			'started_by' => array(
				'header' => array(
					'value' => $txt['started_by'],
				),
				'data' => array(
					'db' => 'poster_link',
				),
				'sort' => array(
					'default' => 'real_name',
					'reverse' => 'real_name DESC',
				),
			),
			'last_post' => array(
				'header' => array(
					'value' => $txt['last_post'],
				),
				'data' => array(
					'sprintf' => array(
						'format' => '<span class="smalltext">%1$s<br />' . $txt['by'] . ' %2$s</span>',
						'params' => array(
							'updated' => false,
							'poster_updated_link' => false,
						),
					),
				),
				'sort' => array(
					'default' => 'ml.id_msg DESC',
					'reverse' => 'ml.id_msg',
				),
			),
			'delete' => array(
				'header' => array(
					'value' => '<input type="checkbox" class="check" onclick="invertAll(this, this.form);" />',
					'style' => 'width: 4%;',
				),
				'data' => array(
					'sprintf' => array(
						'format' => '<input type="checkbox" name="notify_topics[]" value="%1$d" class="check" />',
						'params' => array(
							'id' => false,
						),
					),
					'style' => 'text-align: center;',
				),
			),
		),
		'form' => array(
			'href' => $scripturl . '?action=profile;save',
			'include_sort' => true,
			'include_start' => true,
			'hidden_fields' => array(
				'u' => $memID,
				'sa' => $context['menu_item_selected'],
				'sc' => $context['session_id'],
			),
		),
		'additional_rows' => array(
			array(
				'position' => 'bottom_of_list',
				'value' => '<input type="submit" name="edit_notify_topics" value="' . $txt['notifications_update'] . '" />',
				'class' => 'windowbg',
				'align' => 'right',
			),
		),
	);

	// Create the notification list.
	createList($listOptions);

	// What options are set?
	$context['member'] = array(
		'notify_announcements' => $user_profile[$memID]['notify_announcements'],
		'notify_send_body' => $user_profile[$memID]['notify_send_body'],
		'notify_types' => $user_profile[$memID]['notify_types'],
		'notify_regularity' => $user_profile[$memID]['notify_regularity'],
	);

	loadThemeOptions($memID);
}

function list_getTopicNotificationCount($memID)
{
	global $smfFunc, $db_prefix, $user_info;

	$request = $smfFunc['db_query']('', '
		SELECT COUNT(*)
		FROM {db_prefix}log_notify AS ln
			INNER JOIN {db_prefix}topics AS t ON (t.id_topic = ln.id_topic)
			INNER JOIN {db_prefix}boards AS b ON (b.id_board = t.id_board)
		WHERE ln.id_member = {int:selected_member}
			AND ' . $user_info['query_see_board'] . '
			AND t.approved = {int:is_approved}',
		array(
			'selected_member' => $memID,
			'is_approved' => 1,
		)
	);
	list ($totalNotifications) = $smfFunc['db_fetch_row']($request);
	$smfFunc['db_free_result']($request);

	return $totalNotifications;
}

function list_getTopicNotifications($start, $items_per_page, $sort, $memID)
{
	global $smfFunc, $db_prefix, $txt, $scripturl, $user_info;

	// All the topics with notification on...
	$request = $smfFunc['db_query']('', '
		SELECT
			IFNULL(lt.id_msg, IFNULL(lmr.id_msg, -1)) + 1 AS new_from, b.id_board, b.name,
			t.id_topic, ms.subject, ms.id_member, IFNULL(mem.real_name, ms.poster_name) AS real_name,
			ml.id_msg_modified, ml.poster_time, ml.id_member AS id_member_updated,
			IFNULL(mem2.real_name, ml.poster_name) AS last_real_name
		FROM {db_prefix}log_notify AS ln
			INNER JOIN {db_prefix}topics AS t ON (t.id_topic = ln.id_topic AND t.approved = {int:is_approved})
			INNER JOIN {db_prefix}boards AS b ON (b.id_board = t.id_board AND ' . $user_info['query_see_board'] . ')
			INNER JOIN {db_prefix}messages AS ms ON (ms.id_msg = t.id_first_msg)
			INNER JOIN {db_prefix}messages AS ml ON (ml.id_msg = t.id_last_msg)
			LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = ms.id_member)
			LEFT JOIN {db_prefix}members AS mem2 ON (mem2.id_member = ml.id_member)
			LEFT JOIN {db_prefix}log_topics AS lt ON (lt.id_topic = t.id_topic AND lt.id_member = {int:current_member})
			LEFT JOIN {db_prefix}log_mark_read AS lmr ON (lmr.id_board = b.id_board AND lmr.id_member = {int:current_member})
		WHERE ln.id_member = {int:selected_member}
		ORDER BY ' . $sort . '
		LIMIT ' . $start . ', ' . $items_per_page,
		array(
			'current_member' => $user_info['id'],
			'is_approved' => 1,
			'selected_member' => $memID,
		)
	);
	$notification_topics = array();
	while ($row = $smfFunc['db_fetch_assoc']($request))
	{
		censorText($row['subject']);

		$notification_topics[] = array(
			'id' => $row['id_topic'],
			'poster_link' => empty($row['id_member']) ? $row['real_name'] : '<a href="' . $scripturl . '?action=profile;u=' . $row['id_member'] . '">' . $row['real_name'] . '</a>',
			'poster_updated_link' => empty($row['id_member_updated']) ? $row['last_real_name'] : '<a href="' . $scripturl . '?action=profile;u=' . $row['id_member_updated'] . '">' . $row['last_real_name'] . '</a>',
			'subject' => $row['subject'],
			'href' => $scripturl . '?topic=' . $row['id_topic'] . '.0',
			'link' => '<a href="' . $scripturl . '?topic=' . $row['id_topic'] . '.0">' . $row['subject'] . '</a>',
			'new' => $row['new_from'] <= $row['id_msg_modified'],
			'new_from' => $row['new_from'],
			'updated' => timeformat($row['poster_time']),
			'new_href' => $scripturl . '?topic=' . $row['id_topic'] . '.msg' . $row['new_from'] . '#new',
			'new_link' => '<a href="' . $scripturl . '?topic=' . $row['id_topic'] . '.msg' . $row['new_from'] . '#new">' . $row['subject'] . '</a>',
			'board_link' => '<a href="' . $scripturl . '?board=' . $row['id_board'] . '.0">' . $row['name'] . '</a>',
		);
	}
	$smfFunc['db_free_result']($request);

	return $notification_topics;
}

function list_getBoardNotifications($start, $items_per_page, $sort, $memID)
{
	global $smfFunc, $db_prefix, $txt, $scripturl, $user_info;

	$request = $smfFunc['db_query']('', '
		SELECT b.id_board, b.name, IFNULL(lb.id_msg, 0) AS board_read, b.id_msg_updated
		FROM {db_prefix}log_notify AS ln
			INNER JOIN {db_prefix}boards AS b ON (b.id_board = ln.id_board)
			LEFT JOIN {db_prefix}log_boards AS lb ON (lb.id_board = b.id_board AND lb.id_member = {int:current_member})
		WHERE ln.id_member = {int:selected_member}
			AND ' . $user_info['query_see_board'] . '
		ORDER BY ' . $sort,
		array(
			'current_member' => $user_info['id'],
			'selected_member' => $memID,
		)
	);
	$notification_boards = array();
	while ($row = $smfFunc['db_fetch_assoc']($request))
		$notification_boards[] = array(
			'id' => $row['id_board'],
			'name' => $row['name'],
			'href' => $scripturl . '?board=' . $row['id_board'] . '.0',
			'link' => '<a href="' . $scripturl . '?board=' . $row['id_board'] . '.0">' . $row['name'] . '</a>',
			'new' => $row['board_read'] < $row['id_msg_updated']
		);
	$smfFunc['db_free_result']($request);

	return $notification_boards;
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
		$request = $smfFunc['db_query']('', '
			SELECT id_member, variable, value
			FROM {db_prefix}themes
			WHERE id_theme IN (1, {int:member_theme})
				AND id_member IN (-1, {int:selected_member})',
			array(
				'member_theme' => (int) $cur_profile['id_theme'],
				'selected_member' => $memID,
			)
		);
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

function ignoreboards($memID)
{
	global $txt, $user_info, $db_prefix, $context, $db_prefix, $modSettings, $smfFunc, $cur_profile;

	// Have the admins enabled this option?
	if (empty($modSettings['allow_ignore_boards']))
		fatal_lang_error('ignoreboards_disallowed', 'user');

	// Find all the boards this user is allowed to see.
	$request = $smfFunc['db_query']('', '
		SELECT b.id_cat, c.name AS cat_name, b.id_board, b.name, b.child_level,
			'. (!empty($cur_profile['ignore_boards']) ? 'b.id_board IN ({array_int:ignore_boards})' : 'false') . ' AS is_ignored
		FROM {db_prefix}boards AS b
			LEFT JOIN {db_prefix}categories AS c ON (c.id_cat = b.id_cat)
		WHERE ' . $user_info['query_see_board'],
		array(
			'ignore_boards' => !empty($cur_profile['ignore_boards']) ? $cur_profile['ignore_boards'] : array(),
		)
	);
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
	$request = $smfFunc['db_query']('', '
		SELECT group_name, id_group, hidden
		FROM {db_prefix}membergroups
		WHERE id_group != {int:moderator_group}
			AND min_posts = {int:min_posts}
		ORDER BY min_posts, CASE WHEN id_group < {int:newbie_group} THEN id_group ELSE 4 END, group_name',
		array(
			'moderator_group' => 3,
			'min_posts' => -1,
			'newbie_group' => 4,
		)
	);
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
			$profile_vars['additional_groups'] = implode(',', $_POST['additional_groups']);
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
			$request = $smfFunc['db_query']('', '
				SELECT id_member
				FROM {db_prefix}members
				WHERE (id_group = {int:admin_group} OR FIND_IN_SET({int:admin_group}, additional_groups))
					AND id_member != {int:selected_member}
				LIMIT 1',
				array(
					'admin_group' => 1,
					'selected_member' => $context['id_member'],
				)
			);
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
		removeAttachments(array('id_member' => $memID));
	}
	elseif ($value == 'external' && allowedTo('profile_remote_avatar') && strtolower(substr($_POST['userpicpersonal'], 0, 7)) == 'http://' && empty($modSettings['avatar_download_external']))
	{
		// Remove any attached avatar...
		removeAttachments(array('id_member' => $memID));

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
				removeAttachments(array('id_member' => $memID));

				if (!rename($_FILES['attachment']['tmp_name'], $uploadDir . '/' . $destName))
					fatal_lang_error('attach_timeout', 'critical');

				$smfFunc['db_insert']('',
					$db_prefix . 'attachments',
					array(
						'id_member' => 'int', 'attachment_type' => 'int', 'filename' => 'string', 'fileext' => 'string', 'size' => 'int',
						'width' => 'int', 'height' => 'int', 'mime_type' => 'string',
					),
					array(
						$memID, (empty($modSettings['custom_avatar_enabled']) ? 0 : 1), $destName, $extension, filesize($uploadDir . '/' . $destName),
						(int) $width, (int) $height, $mime_type,
					),
					array('id_attach')
				);

				$cur_profile['id_attach'] = $smfFunc['db_insert_id']( $db_prefix . 'attachments', 'id_attach');
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
	$profile_vars['avatar'] = $profile_vars['avatar'];
	return false;
}

// Validate the signature!
function profileValidateSignature(&$value)
{
	global $sourcedir, $modSettings, $smfFunc, $txt;

	require_once($sourcedir . '/Subs-Post.php');

	// Admins can do whatever they hell they want!
	if (!allowedTo('admin_forum'))
	{
		// Load all the signature limits.
		list ($sig_limits, $sig_bbc) = explode(':', $modSettings['signature_settings']);
		$sig_limits = explode(',', $sig_limits);
		$disabledTags = !empty($sig_bbc) ? explode(',', $sig_bbc) : array();

		$unparsed_signature = strtr(un_htmlspecialchars($value), array("\r" => '', '&#039' => '\''));
		// Too long?
		if (!empty($sig_limits[1]) && $smfFunc['strlen']($unparsed_signature) > $sig_limits[1])
		{
			$_POST['signature'] = trim(htmlspecialchars($smfFunc['substr']($unparsed_signature, 0, $sig_limits[1]), ENT_QUOTES));
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
		if (!empty($sig_limits[3]) && (substr_count(strtolower($unparsed_signature), '[img') + substr_count(strtolower($unparsed_signature), '<img')) > $sig_limits[3])
		{
			$txt['profile_error_signature_max_image_count'] = sprintf($txt['profile_error_signature_max_image_count'], $sig_limits[3]);
			return 'signature_max_image_count';
		}
		// What about too many smileys!
		$smiley_parsed = $unparsed_signature;
		parsesmileys($smiley_parsed);
		if (!empty($sig_limits[4]) && (substr_count(strtolower($smiley_parsed), '<img') - substr_count(strtolower($unparsed_signature), '<img')) > $sig_limits[4])
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
			// Get all BBC tags...
			preg_match_all('~\[img(\s+width=([\d]+))?(\s+height=([\d]+))?(\s+width=([\d]+))?\s*\](?:<br />)*([^<">]+?)(?:<br />)*\[/img\]~i', $unparsed_signature, $matches);
			// ... and all HTML ones.
			preg_match_all('~<img\s+src=(?:")?((?:http://|ftp://|https://|ftps://).+?)(?:")?(?:\s+alt=(?:")?(.*?)(?:")?)?(?:\s?/)?>~i', $unparsed_signature, $matches2, PREG_PATTERN_ORDER);
			// And stick the HTML in the BBC.
			if (!empty($matches2))
			{
				foreach ($matches2[0] as $ind => $dummy)
				{
					$matches[0][] = $matches2[0][$ind];
					$matches[1][] = '';
					$matches[2][] = '';
					$matches[3][] = '';
					$matches[4][] = '';
					$matches[5][] = '';
					$matches[6][] = '';
					$matches[7][] = $matches2[1][$ind];
				}
			}

			$replaces = array();
			// Try to find all the images!
			if (!empty($matches))
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

// Validate an email address.
function profileValidateEmail($email, $memID = 0)
{
	global $smfFunc, $db_prefix, $context;

	$email = strtr($email, array('&#039;' => '\''));

	// Check the name and email for validity.
	if (trim($email) == '')
		return 'no_email';
	if (preg_match('~^[0-9A-Za-z=_+\-/][0-9A-Za-z=_\'+\-/\.]*@[\w\-]+(\.[\w\-]+)*(\.[\w]{2,6})$~', $email) == 0)
		return 'bad_email';

	// Email addresses should be and stay unique.
	$request = $smfFunc['db_query']('', '
		SELECT id_member
		FROM {db_prefix}members
		WHERE ' . ($memID != 0 ? 'id_member != {int:selected_member} AND ' : '') . '
			email_address = {string:email_address}
		LIMIT 1',
		array(
			'selected_member' => $memID,
			'email_address' => $email,
		)
	);
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
		setLoginCookie(60 * $modSettings['cookieTime'], $context['id_member'], sha1(sha1(strtolower($cur_profile['member_name']) . un_htmlspecialchars($_POST['passwrd2'])) . $cur_profile['password_salt']));
	}

	loadUserSettings();
	writeLog();
}

// Send the user a new activation email if they need to reactivate!
function profileSendActivation()
{
	global $sourcedir, $profile_vars, $txt, $context, $scripturl, $smfFunc, $db_prefix, $cookiename;

	require_once($sourcedir . '/Subs-Post.php');

	// Shouldn't happen but just in case.
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
	$smfFunc['db_query']('', '
		DELETE FROM {db_prefix}log_online
		WHERE id_member = {int:selected_member}',
		array(
			'selected_member' => $context['id_member'],
		)
	);
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
	$request = $smfFunc['db_query']('', '
		SELECT mg.id_group, mg.group_name, mg.description, mg.group_type, mg.online_color, mg.hidden,
			IFNULL(lgr.id_member, 0) AS pending
		FROM {db_prefix}membergroups AS mg
			LEFT JOIN {db_prefix}log_group_requests AS lgr ON (lgr.id_member = {int:selected_member} AND lgr.id_group = mg.id_group)
		WHERE (mg.id_group IN ({array_int:group_list})
			OR mg.group_type > {int:nonjoin_group_id})
			AND mg.min_posts = {int:min_posts}
			AND mg.id_group != {int:moderator_group}
		ORDER BY group_name',
		array(
			'group_list' => $groups,
			'selected_member' => $memID,
			'nonjoin_group_id' => 0,
			'min_posts' => -1,
			'moderator_group' => 3,
		)
	);
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
	$request = $smfFunc['db_query']('', '
		SELECT id_group, group_type, hidden, group_name
		FROM {db_prefix}membergroups
		WHERE id_group IN ({array_int:group_list}, {int:current_group})',
		array(
			'group_list' => $group_id,
			'current_group' => $old_profile['id_group'],
		)
	);
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
		$request = $smfFunc['db_query']('', '
			SELECT COUNT(permission)
			FROM {db_prefix}permissions
			WHERE id_group = {int:selected_group}
				AND permission = {string:admin_forum}
				AND add_deny = {int:not_denied}',
			array(
				'selected_group' => $group_id,
				'not_denied' => 1,
				'admin_forum' => 'admin_forum',
			)
		);
		list ($disallow) = $smfFunc['db_fetch_row']($request);
		$smfFunc['db_free_result']($request);

		if ($disallow)
			isAllowedTo('admin_forum');
	}

	// If we're requesting, add the note then return.
	if ($changeType == 'request')
	{
		$reason = htmlspecialchars($_POST['reason']);

		$request = $smfFunc['db_query']('', '
			SELECT id_member
			FROM {db_prefix}log_group_requests
			WHERE id_member = {int:selected_member}
				AND id_group = {int:selected_group}',
			array(
				'selected_member' => $memID,
				'selected_group' => $group_id,
			)
		);
		if ($smfFunc['db_num_rows']($request) != 0)
			fatal_lang_error('profile_error_already_requested_group');
		$smfFunc['db_free_result']($request);

		// Log the request.
		$smfFunc['db_insert']('',
			$db_prefix . 'log_group_requests',
			array(
				'id_member' => 'int', 'id_group' => 'int', 'time_applied' => 'int', 'reason' => 'string-65534',
			),
			array(
				$memID, $group_id, time(), $reason,
			),
			array('id_request')
		);

		// Send an email to all group moderators etc.
		require_once($sourcedir . '/Subs-Post.php');

		// Do we have any group moderators?
		$request = $smfFunc['db_query']('', '
			SELECT id_member
			FROM {db_prefix}group_moderators
			WHERE id_group = {int:selected_group}',
			array(
				'selected_group' => $group_id,
			)
		);
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
			$request = $smfFunc['db_query']('', '
				SELECT id_member, email_address, lngfile, member_name
				FROM {db_prefix}members
				WHERE id_member IN ({array_int:moderator_list})
					AND notify_types != {int:no_notifications}
				ORDER BY lngfile',
				array(
					'moderator_list' => $moderators,
					'no_notifications' => 4,
				)
			);
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

	updateMemberData($memID, array('id_group' => $newPrimary, 'additional_groups' => $addGroups));

	return $changeType;
}

?>