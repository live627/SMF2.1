<?php
/**********************************************************************************
* Register.php                                                                    *
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

/*	This file has two main jobs, but they really are one.  It registers new
	members, and it helps the administrator moderate member registrations.
	Similarly, it handles account activation as well.

	void Register()
		// !!!

	void Register2()
		// !!!

	void Activate()
		// !!!

	void CoppaForm()
		// !!!

	void RegisterCheckUsername()
		// !!!
*/

// Begin the registration process.
function Register()
{
	global $txt, $boarddir, $context, $settings, $modSettings, $user_info;
	global $db_prefix, $language, $scripturl, $smfFunc, $sourcedir, $smfFunc;

	if (isset($_GET['sa']) && $_GET['sa'] == 'usernamecheck')
		return RegisterCheckUsername();

	// Check if the administrator has it disabled.
	if (!empty($modSettings['registration_method']) && $modSettings['registration_method'] == 3)
		fatal_lang_error('registration_disabled', false);

	// If this user is an admin - redirect them to the admin registration page.
	if (allowedTo('moderate_forum') && !$user_info['is_guest'])
		redirectexit('action=admin;area=regcenter;sa=register');
	// You are not a guest so you are a member - and members don't get to register twice!
	elseif (empty($user_info['is_guest']))
		redirectexit();

	loadLanguage('Login');
	loadTemplate('Register');

	// All the basic template information...
	$context['sub_template'] = 'before';
	$context['allow_hide_email'] = !empty($modSettings['allow_hide_email']);
	$context['require_agreement'] = !empty($modSettings['requireAgreement']);

	// Under age restrictions?
	if (!empty($modSettings['coppaAge']))
	{
		$context['show_coppa'] = true;
		$context['coppa_desc'] = sprintf($txt['register_age_confirmation'], $modSettings['coppaAge']);
	}

	$context['page_title'] = $txt['register'];

	// If you have to agree to the agreement, it needs to be fetched from the file.
	if ($context['require_agreement'])
		$context['agreement'] = file_exists($boarddir . '/agreement.txt') ? parse_bbc(file_get_contents($boarddir . '/agreement.txt'), true, 'agreement') : '';

	if (!empty($modSettings['userLanguage']))
	{
		$selectedLanguage = empty($_SESSION['language']) ? $language : $_SESSION['language'];

		$language_directories = array(
			$settings['default_theme_dir'] . '/languages',
			$settings['actual_theme_dir'] . '/languages',
		);
		if (!empty($settings['base_theme_dir']))
			$language_directories[] = $settings['base_theme_dir'] . '/languages';
		$language_directories = array_unique($language_directories);

		foreach ($language_directories as $language_dir)
		{
			// Can't look in here... doesn't exist!
			if (!file_exists($language_dir))
				continue;

			$dir = dir($language_dir);
			while ($entry = $dir->read())
			{
				// Look for the index language file....
				if (preg_match('~^index\.(.+)\.php$~', $entry, $matches) == 0)
					continue;

				$context['languages'][] = array(
					'name' => $smfFunc['ucwords'](strtr($matches[1], array('_' => ' ', '-utf8' => ''))),
					'selected' => $selectedLanguage == $matches[1],
					'filename' => $matches[1],
				);
			}
			$dir->close();
		}
	}

	// Any custom fields we want filled in?
	require_once($sourcedir . '/Profile.php');
	loadCustomFields(0, 'register');

	// Generate a visual verification code to make sure the user is no bot.
	$context['visual_verification'] = empty($modSettings['visual_verification_type']) || $modSettings['visual_verification_type'] != 1;
	if ($context['visual_verification'])
	{
		$context['use_graphic_library'] = in_array('gd', get_loaded_extensions());
		$context['verificiation_image_href'] = $scripturl . '?action=verificationcode;rand=' . md5(rand());

		// Only generate a new code if one hasn't been set yet
		if (!isset($_SESSION['visual_verification_code']))
		{
			// Skip I, J, L, O, Q, S and Z.
			$character_range = array_merge(range('A', 'H'), array('K', 'M', 'N', 'P'), range('R', 'Z'));

			// Generate a new code.
			$_SESSION['visual_verification_code'] = '';
			for ($i = 0; $i < 5; $i++)
				$_SESSION['visual_verification_code'] .= $character_range[array_rand($character_range)];
		}
	}
}

// Actually register the member.
function Register2()
{
	global $scripturl, $txt, $modSettings, $db_prefix, $context, $sourcedir;
	global $user_info, $options, $settings, $smfFunc;

	// Well, if you don't agree, you can't register.
	if (!empty($modSettings['requireAgreement']) && (empty($_POST['regagree']) || $_POST['regagree'] == 'no'))
		redirectexit();

	// Make sure they came from *somewhere*, have a session.
	if (!isset($_SESSION['old_url']))
		redirectexit('action=register');

	// You can't register if it's disabled.
	if (!empty($modSettings['registration_method']) && $modSettings['registration_method'] == 3)
		fatal_lang_error('registration_disabled', false);

	foreach ($_POST as $key => $value)
	{
		if (!is_array($_POST[$key]))
			$_POST[$key] = htmltrim__recursive(str_replace(array("\n", "\r"), '', $_POST[$key]));
	}

	// Are they under age, and under age users are banned?
	if (!empty($modSettings['coppaAge']) && empty($modSettings['coppaType']) && !isset($_POST['skip_coppa']))
	{
		// !!! This should be put in Errors, imho.
		loadLanguage('Login');
		fatal_lang_error('under_age_registration_prohibited', false, array($modSettings['coppaAge']));
	}

	// Check whether the visual verification code was entered correctly.
	if ((empty($modSettings['visual_verification_type']) || $modSettings['visual_verification_type'] != 1) && (empty($_REQUEST['visual_verification_code']) || strtoupper($_REQUEST['visual_verification_code']) !== $_SESSION['visual_verification_code']))
	{
		// Don't allow lots of errors!
		$_SESSION['visual_errors'] = isset($_SESSION['visual_errors']) ? $_SESSION['visual_errors'] + 1 : 1;
		if ($_SESSION['visual_errors'] > 3 && isset($_SESSION['visual_verification_code']))
			unset($_SESSION['visual_verification_code']);

		if (!empty($_REQUEST['visual_verification_code']) && in_array(md5(strtolower($_REQUEST['visual_verification_code'])), array('7921903964212cc383bf910a8bf2d7f4', '9726255eec083aa56dc0449a21b33190')))
		{
			loadLanguage('Errors');
			fatal_error($txt['error_wrong_verification_code'] . '<br />And we don\'t take bribes', false);
		}
		fatal_lang_error('error_wrong_verification_code', false);
	}
	elseif (isset($_SESSION['visual_errors']))
		unset($_SESSION['visual_errors']);

	// Collect all extra registration fields someone might have filled in.
	$possible_strings = array(
		'website_url', 'website_title',
		'aim', 'yim',
		'location', 'birthdate',
		'time_format',
		'buddy_list',
		'pm_ignore_list',
		'smiley_set',
		'signature', 'personal_text', 'avatar',
		'lngfile',
		'secret_question', 'secret_answer',
	);
	$possible_ints = array(
		'pm_email_notify',
		'notify_types',
		'icq',
		'gender',
		'id_theme',
	);
	$possible_floats = array(
		'time_offset',
	);
	$possible_bools = array(
		'notify_announcements', 'notify_regularity', 'notify_send_body',
		'hide_email', 'show_online',
	);

	if (isset($_POST['secret_answer']) && $_POST['secret_answer'] != '')
		$_POST['secret_answer'] = md5($_POST['secret_answer']);

	// Needed for isReservedName() and registerMember().
	require_once($sourcedir . '/Subs-Members.php');

	// Validation... even if we're not a mall.
	if (isset($_POST['real_name']) && (!empty($modSettings['allow_editDisplayName']) || allowedTo('moderate_forum')))
	{
		$_POST['real_name'] = trim(preg_replace('~[\s]~' . ($context['utf8'] ? 'u' : ''), ' ', $_POST['real_name']));
		if (trim($_POST['real_name']) != '' && !isReservedName($_POST['real_name'], $memID) && $smfFunc['strlen']($_POST['real_name']) > 60)
			$possible_strings[] = 'real_name';
	}

	if (isset($_POST['msn']) && preg_match('~^[0-9A-Za-z=_+\-/][0-9A-Za-z=_\'+\-/\.]*@[\w\-]+(\.[\w\-]+)*(\.[\w]{2,6})$~', $_POST['msn']) != 0)
		$profile_strings[] = 'msn';

	// Handle a string as a birthdate...
	if (isset($_POST['birthdate']) && $_POST['birthdate'] != '')
		$_POST['birthdate'] = strftime('%Y-%m-%d', strtotime($_POST['birthdate']));
	// Or birthdate parts...
	elseif (!empty($_POST['bday1']) && !empty($_POST['bday2']))
		$_POST['birthdate'] = sprintf('%04d-%02d-%02d', empty($_POST['bday3']) ? 0 : (int) $_POST['bday3'], (int) $_POST['bday1'], (int) $_POST['bday2']);

	// Validate the passed langauge file.
	if (isset($_POST['lngfile']) && !empty($modSettings['userLanguage']))
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
					// Got it!
					$found = true;
					$_SESSION['language'] = $_POST['lngfile'];
					break 2;
				}
			$dir->close();
		}

		if (empty($found))
			unset($_POST['lngfile']);
	}
	else
		unset($_POST['lngfile']);

	// Set the options needed for registration.
	$regOptions = array(
		'interface' => 'guest',
		'username' => $_POST['user'],
		'email' => $_POST['email'],
		'password' => $_POST['passwrd1'],
		'password_check' => $_POST['passwrd2'],
		'check_reserved_name' => true,
		'check_password_strength' => true,
		'check_email_ban' => true,
		'send_welcome_email' => !empty($modSettings['send_welcomeEmail']),
		'require' => !empty($modSettings['coppaAge']) && !isset($_POST['skip_coppa']) ? 'coppa' : (empty($modSettings['registration_method']) ? 'nothing' : ($modSettings['registration_method'] == 1 ? 'activation' : 'approval')),
		'extra_register_vars' => array(),
		'theme_vars' => array(),
	);

	// Include the additional options that might have been filled in.
	foreach ($possible_strings as $var)
		if (isset($_POST[$var]))
			$regOptions['extra_register_vars'][$var] = '\'' . $smfFunc['htmlspecialchars']($_POST[$var]) . '\'';
	foreach ($possible_ints as $var)
		if (isset($_POST[$var]))
			$regOptions['extra_register_vars'][$var] = (int) $_POST[$var];
	foreach ($possible_floats as $var)
		if (isset($_POST[$var]))
			$regOptions['extra_register_vars'][$var] = (float) $_POST[$var];
	foreach ($possible_bools as $var)
		if (isset($_POST[$var]))
			$regOptions['extra_register_vars'][$var] = empty($_POST[$var]) ? 0 : 1;

	// Registration options are always default options...
	if (isset($_POST['default_options']))
		$_POST['options'] = isset($_POST['options']) ? $_POST['options'] + $_POST['default_options'] : $_POST['default_options'];
	$regOptions['theme_vars'] = isset($_POST['options']) && is_array($_POST['options']) ? $_POST['options'] : array();

	$memberID = registerMember($regOptions);

	// We'll do custom fields after as then we get to use the helper function!
	require_once($sourcedir . '/Profile.php');
	makeCustomFieldChanges($memberID, 'register');
 
	// If COPPA has been selected then things get complicated, setup the template.
	if (!empty($modSettings['coppaAge']) && !isset($_POST['skip_coppa']))
		redirectexit('action=coppa;member=' . $memberID);
	// Basic template variable setup.
	elseif (!empty($modSettings['registration_method']))
	{
		loadTemplate('Register');

		$context += array(
			'page_title' => &$txt['register'],
			'sub_template' => 'after',
			'description' => $modSettings['registration_method'] == 2 ? $txt['approval_after_registration'] : $txt['activate_after_registration']
		);
	}
	else
	{
		setLoginCookie(60 * $modSettings['cookieTime'], $memberID, sha1(sha1(strtolower($regOptions['username']) . $regOptions['password']) . substr($regOptions['register_vars']['password_salt'], 1, -1)));

		redirectexit('action=login2;sa=check;member=' . $memberID, $context['server']['needs_login_fix']);
	}
}

function Activate()
{
	global $db_prefix, $context, $txt, $modSettings, $scripturl, $sourcedir, $smfFunc;

	loadLanguage('Login');
	loadTemplate('Login');

	if (empty($_REQUEST['u']) && empty($_POST['user']))
	{
		if (empty($modSettings['registration_method']) || $modSettings['registration_method'] == 3)
			fatal_lang_error('no_access');

		$context['member_id'] = 0;
		$context['sub_template'] = 'resend';
		$context['page_title'] = $txt['invalid_activation_resend'];
		$context['can_activate'] = empty($modSettings['registration_method']) || $modSettings['registration_method'] == 1;
		$context['default_username'] = isset($_GET['user']) ? $_GET['user'] : '';

		return;
	}

	// Get the code from the database...
	$request = $smfFunc['db_query']('', "
		SELECT id_member, validation_code, member_name, real_name, email_address, is_activated, passwd
		FROM {$db_prefix}members" . (empty($_REQUEST['u']) ? "
		WHERE member_name = '$_POST[user]' OR email_address = '$_POST[user]'" : "
		WHERE id_member = " . (int) $_REQUEST['u']) . "
		LIMIT 1", __FILE__, __LINE__);

	// Does this user exist at all?
	if ($smfFunc['db_num_rows']($request) == 0)
	{
		$context['sub_template'] = 'retry_activate';
		$context['page_title'] = $txt['invalid_userid'];
		$context['member_id'] = 0;

		return;
	}

	$row = $smfFunc['db_fetch_assoc']($request);
	$smfFunc['db_free_result']($request);

	// Change their email address? (they probably tried a fake one first :P.)
	if (isset($_POST['new_email'], $_REQUEST['passwd']) && sha1(strtolower($row['member_name']) . $_REQUEST['passwd']) == $row['passwd'])
	{
		if (empty($modSettings['registration_method']) || $modSettings['registration_method'] == 3)
			fatal_lang_error('no_access');

		// !!! Separate the sprintf?
		if (preg_match('~^[0-9A-Za-z=_+\-/][0-9A-Za-z=_\'+\-/\.]*@[\w\-]+(\.[\w\-]+)*(\.[\w]{2,6})$~', $smfFunc['db_unescape_string']($_POST['new_email'])) == 0)
			fatal_error(sprintf($txt['valid_email_needed'], htmlspecialchars($_POST['new_email'])), false);

		// Make sure their email isn't banned.
		isBannedEmail($_POST['new_email'], 'cannot_register', $txt['ban_register_prohibited']);

		// Ummm... don't even dare try to take someone else's email!!
		$request = $smfFunc['db_query']('', "
			SELECT id_member
			FROM {$db_prefix}members
			WHERE email_address = '$_POST[new_email]'
			LIMIT 1", __FILE__, __LINE__);
		// !!! Separate the sprintf?
		if ($smfFunc['db_num_rows']($request) != 0)
			fatal_lang_error('email_in_use', false, array(htmlspecialchars($_POST['new_email'])));
		$smfFunc['db_free_result']($request);

		updateMemberData($row['id_member'], array('email_address' => "'$_POST[new_email]'"));
		$row['email_address'] = $smfFunc['db_unescape_string']($_POST['new_email']);

		$email_change = true;
	}

	// Resend the password, but only if the account wasn't activated yet.
	if (!empty($_REQUEST['sa']) && $_REQUEST['sa'] == 'resend' && ($row['is_activated'] == 0 || $row['is_activated'] == 2) && (!isset($_REQUEST['code']) || $_REQUEST['code'] == ''))
	{
		require_once($sourcedir . '/Subs-Post.php');

		$replacements = array(
			'REALNAME' => $row['real_name'],
			'USERNAME' => $row['member_name'],
			'ACTIVATIONLINK' => $scripturl . '?action=activate;u=' . $row['id_member'] . ';code=' . $row['validation_code'],
			'ACTIVATIONCODE' => $row['validation_code'],
		);

		$emaildata = loadEmailTemplate(empty($modSettings['registration_method']) || $modSettings['registration_method'] == 1 ? 'resend_activate_message' : 'resend_pending_message', $replacements);

		sendmail($row['email_address'], $emaildata['subject'], $emaildata['body'], null, null, false, 3);

		$context['page_title'] = $txt['invalid_activation_resend'];
		fatal_lang_error(!empty($email_change) ? 'change_email_success' : 'resend_email_success', false);
	}

	// Quit if this code is not right.
	if (empty($_REQUEST['code']) || $row['validation_code'] != $_REQUEST['code'])
	{
		if (!empty($row['is_activated']))
			fatal_lang_error('already_activated', false);
		elseif ($row['validation_code'] == '')
		{
			loadLanguage('Profile');
			fatal_error($txt['registration_not_approved'] . ' <a href="' . $scripturl . '?action=activate;user=' . $row['member_name'] . '">' . $txt['here'] . '</a>.', false);
		}

		$context['sub_template'] = 'retry_activate';
		$context['page_title'] = $txt['invalid_activation_code'];
		$context['member_id'] = $row['id_member'];

		return;
	}

	// Let the integration know that they've been activated!
	if (isset($modSettings['integrate_activate']) && function_exists($modSettings['integrate_activate']))
		call_user_func($modSettings['integrate_activate'], $row['member_name']);

	// Validation complete - update the database!
	updateMemberData($row['id_member'], array('is_activated' => 1, 'validation_code' => '\'\''));

	// Also do a proper member stat re-evaluation.
	updateStats('member', false);

	if (!isset($_POST['new_email']))
	{
		require_once($sourcedir . '/Subs-Post.php');

		adminNotify('activation', $row['id_member'], $row['member_name']);
	}

	$context += array(
		'page_title' => &$txt['registration_successful'],
		'sub_template' => 'login',
		'default_username' => $row['member_name'],
		'default_password' => '',
		'never_expire' => false,
		'description' => &$txt['activate_success']
	);
}

// This function will display the contact information for the forum, as well a form to fill in.
function CoppaForm()
{
	global $context, $modSettings, $txt, $db_prefix, $smfFunc;

	loadLanguage('Login');
	loadTemplate('Register');

	// No User ID??
	if (!isset($_GET['member']))
		fatal_lang_error('no_access');

	// Get the user details...
	$request = $smfFunc['db_query']('', "
		SELECT member_name
		FROM {$db_prefix}members
		WHERE id_member = " . (int) $_GET['member'] . "
			AND is_activated = 5", __FILE__, __LINE__);
	if ($smfFunc['db_num_rows']($request) == 0)
		fatal_lang_error('no_access');
	list ($username) = $smfFunc['db_fetch_row']($request);
	$smfFunc['db_free_result']($request);

	if (isset($_GET['form']))
	{
		// Some simple contact stuff for the forum.
		$context['forum_contacts'] = (!empty($modSettings['coppaPost']) ? $modSettings['coppaPost'] . '<br /><br />' : '') . (!empty($modSettings['coppaFax']) ? $modSettings['coppaFax'] . '<br />' : '');
		$context['forum_contacts'] = !empty($context['forum_contacts']) ? $context['forum_name'] . '<br />' . $context['forum_contacts'] : '';

		// Showing template?
		if (!isset($_GET['dl']))
		{
			// Shortcut for producing underlines.
			$context['ul'] = '<u>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</u>';
			$context['template_layers'] = array();
			$context['sub_template'] = 'coppa_form';
			$context['page_title'] = $txt['coppa_form_title'];
			$context['coppa_body'] = str_replace(array('{PARENT_NAME}', '{CHILD_NAME}', '{USER_NAME}'), array($context['ul'], $context['ul'], $username), $txt['coppa_form_body']);
		}
		// Downloading.
		else
		{
			// The data.
			$ul = '                ';
			$crlf = "\r\n";
			$data = $context['forum_contacts'] . "$crlf" . $txt['coppa_form_address'] . ":$crlf" . $txt['coppa_form_date'] . ":$crlf$crlf$crlf" . $txt['coppa_form_body'];
			$data = str_replace(array('{PARENT_NAME}', '{CHILD_NAME}', '{USER_NAME}', '<br>', '<br />'), array($ul, $ul, $username, $crlf, $crlf), $data);

			// Send the headers.
			header('Connection: close');
			header('Content-Disposition: attachment; filename="approval.txt"');
			header('Content-Type: application/octet-stream');
			header('Content-Length: ' . count($data));

			echo $data;
			obExit(false);
		}
	}
	else
	{
		$context += array(
			'page_title' => &$txt['coppa_title'],
			'sub_template' => 'coppa',
		);

		$context['coppa'] = array(
			'body' => str_replace('{MINIMUM_AGE}', $modSettings['coppaAge'], $txt['coppa_after_registration']),
			'many_options' => !empty($modSettings['coppaPost']) && !empty($modSettings['coppaFax']),
			'post' => empty($modSettings['coppaPost']) ? '' : $modSettings['coppaPost'],
			'fax' => empty($modSettings['coppaFax']) ? '' : $modSettings['coppaFax'],
			'phone' => empty($modSettings['coppaPhone']) ? '' : str_replace('{PHONE_NUMBER}', $modSettings['coppaPhone'], $txt['coppa_send_by_phone']),
			'id' => $_GET['member'],
		);
	}
}

// Show the verification code or let it hear.
function VerificationCode()
{
	global $sourcedir, $modSettings, $context, $scripturl;

	// Somehow no code was generated or the session was lost.
	if (empty($_SESSION['visual_verification_code']))
		header('HTTP/1.1 408 - Request Timeout');

	// Show a window that will play the verification code.
	elseif (isset($_REQUEST['sound']))
	{
		loadLanguage('Login');
		loadTemplate('Register');

		$context['verificiation_sound_href'] = $scripturl . '?action=verificationcode;rand=' . md5(rand()) . ';format=.wav';
		$context['sub_template'] = 'verification_sound';
		$context['template_layers'] = array();

		obExit();
	}

	// If we have GD, try the nice code.
	elseif (empty($_REQUEST['format']))
	{
		require_once($sourcedir . '/Subs-Graphics.php');

		if (in_array('gd', get_loaded_extensions()) && !showCodeImage($_SESSION['visual_verification_code']))
			header('HTTP/1.1 400 Bad Request');

		// Otherwise just show a pre-defined letter.
		elseif (isset($_REQUEST['letter']))
		{
			$_REQUEST['letter'] = (int) $_REQUEST['letter'];
			if ($_REQUEST['letter'] > 0 && $_REQUEST['letter'] <= strlen($_SESSION['visual_verification_code']) && !showLetterImage(strtolower($_SESSION['visual_verification_code']{$_REQUEST['letter'] - 1})))
				header('HTTP/1.1 400 Bad Request');
		}
		// You must be up to no good.
		else
			header('HTTP/1.1 400 Bad Request');
	}

	elseif ($_REQUEST['format'] === '.wav')
	{
		require_once($sourcedir . '/Subs-Sound.php');

		if (!createWaveFile($_SESSION['visual_verification_code']))
			header('HTTP/1.1 400 Bad Request');
	}

	// We all die one day...
	die();
}

// See if a username already exists.
function RegisterCheckUsername()
{
	global $sourcedir, $smfFunc, $context;

	// This is XML!
	loadTemplate('Xml');
	$context['sub_template'] = 'check_username';
	$context['checked_username'] = isset($_GET['username']) ? $_GET['username'] : '';

	if (empty($_GET['username']))
		$context['valid_username'] = false;
	else
	{
		require_once($sourcedir . '/Subs-Members.php');
		$context['valid_username'] = $smfFunc['strlen']($_GET['username']) <= 60;
		$context['valid_username'] &= isReservedName($_GET['username'], 0, false, false) ? 0 : 1;
	}
}

?>