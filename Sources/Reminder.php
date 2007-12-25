<?php
/**********************************************************************************
* Reminder.php                                                                    *
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

/*	This file deals with sending out reminders, and checking the secret answer
	and question.  It uses just a few functions to do this, which are:

	void RemindMe()
		- this is just the controlling delegator.
		- uses the Profile language files and Reminder template.

	void RemindMail()
		// !!!

	void setPassword()
		// !!!

	void setPassword2()
		// !!!

	void secret_answerInput()
		// !!!

	void secret_answer2()
		// !!!
*/

// Forgot 'yer password?
function RemindMe()
{
	global $txt, $context;

	loadLanguage('Profile');
	loadTemplate('Reminder');

	$context['page_title'] = $context['forum_name'] . ' ' . $txt['password_reminder'];

	// Delegation can be useful sometimes.
	$subActions = array(
		'mail' => 'RemindMail',
		'secret' => 'secret_answerInput',
		'secret2' => 'secret_answer2',
		'setpassword' =>'setPassword',
		'setpassword2' =>'setPassword2'
	);

	// Any subaction?  If none, fall through to the main template, which will ask for one.
	if (isset($_REQUEST['sa']) && isset($subActions[$_REQUEST['sa']]))
		$subActions[$_REQUEST['sa']]();
}

// Email a reminder.
function RemindMail()
{
	global $db_prefix, $context, $txt, $scripturl, $sourcedir, $user_info, $webmaster_email, $smfFunc;

	checkSession();

	// You must enter a username/email address.
	if (!isset($_POST['user']) || $_POST['user'] == '')
		fatal_lang_error('username_no_exist', false);

	// Find the user!
	$request = $smfFunc['db_query']('', '
		SELECT id_member, real_name, member_name, email_address, is_activated, validation_code
		FROM {db_prefix}members
		WHERE member_name = {string:inject_string_1}
		LIMIT 1',
		array(
			'inject_string_1' => $_POST['user'],
		)
	);
	if ($smfFunc['db_num_rows']($request) == 0)
	{
		$smfFunc['db_free_result']($request);

		$request = $smfFunc['db_query']('', '
			SELECT id_member, real_name, member_name, email_address, is_activated, validation_code
			FROM {db_prefix}members
			WHERE email_address = {string:inject_string_1}
			LIMIT 1',
			array(
				'inject_string_1' => $_POST['user'],
			)
		);
		if ($smfFunc['db_num_rows']($request) == 0)
			fatal_lang_error('username_no_exist', false);
	}

	$row = $smfFunc['db_fetch_assoc']($request);
	$smfFunc['db_free_result']($request);

	// If the user isn't activated/approved, give them some feedback on what to do next.
	if ($row['is_activated'] != 1)
	{
		// Awaiting approval...
		if (trim($row['validation_code']) == '')
			fatal_error($txt['registration_not_approved'] . ' <a href="' . $scripturl . '?action=activate;user=' . $_POST['user'] . '">' . $txt['here'] . '</a>.', false);
		else
			fatal_error($txt['registration_not_activated'] . ' <a href="' . $scripturl . '?action=activate;user=' . $_POST['user'] . '">' . $txt['here'] . '</a>.', false);
	}

	// You can't get emailed if you have no email address.
	$row['email_address'] = trim($row['email_address']);
	if ($row['email_address'] == '')
		fatal_error('<b>' . $txt['no_reminder_email'] . '<br />' . $txt['send_email'] . ' <a href="mailto:' . $webmaster_email . '">webmaster</a> ' . $txt['to_ask_password'] . '.');

	// Randomly generate a new password, with only alpha numeric characters that is a max length of 10 chars.
	$password = substr(preg_replace('/\W/', '', md5(rand())), 0, 10);

	// Set the password in the database.
	updateMemberData($row['id_member'], array('validation_code' => '\'' . substr(md5($password), 0, 10) . '\''));

	require_once($sourcedir . '/Subs-Post.php');

	$replacements = array(
		'REALNAME' => $row['real_name'],
		'REMINDLINK' => $scripturl . '?action=reminder;sa=setpassword;u=' . $row['id_member'] . ';code=' . $password,
		'IP' => $user_info['ip'],
		'MEMBERNAME' => $row['member_name'],
	);

	$emaildata = loadEmailTemplate('forgot_password', $replacements);

	sendmail($row['email_address'], $emaildata['subject'], $emaildata['body']);

	// Set up the template.
	$context += array(
		'page_title' => &$txt['password_reminder'],
		'sub_template' => 'sent',
		'description' => &$txt['reminder_sent']
	);
}

// Set your new password
function setPassword()
{
	global $txt, $context;

	loadLanguage('Login');

	// You need a code!
	if (!isset($_REQUEST['code']))
		fatal_lang_error('no_access');

	// Fill the context array.
	$context += array(
		'page_title' => &$txt['reminder_set_password'],
		'sub_template' => 'set_password',
		'code' => $_REQUEST['code'],
		'memID' => (int) $_REQUEST['u']
	);
}

function setPassword2()
{
	global $db_prefix, $context, $txt, $modSettings, $smfFunc, $sourcedir;

	if (empty($_POST['u']) || !isset($_POST['passwrd1']) || !isset($_POST['passwrd2']))
		fatal_lang_error('no_access', false);

	$_POST['u'] = (int) $_POST['u'];

	if ($_POST['passwrd1'] !=  $_POST['passwrd2'])
		fatal_lang_error('passwords_dont_match', false);

	if ($_POST['passwrd1'] == '')
		fatal_lang_error('no_password', false);

	loadLanguage('Login');

	// Get the code as it should be from the database.
	$request = $smfFunc['db_query']('', '
		SELECT validation_code, member_name, email_address
		FROM {db_prefix}members
		WHERE id_member = {int:inject_int_1}
			AND is_activated = {int:inject_int_2}
			AND validation_code != {string:inject_string_1}
		LIMIT 1',
		array(
			'inject_int_1' => $_POST['u'],
			'inject_int_2' => 1,
			'inject_string_1' => '',
		)
	);

	// Does this user exist at all?
	if ($smfFunc['db_num_rows']($request) == 0)
		fatal_lang_error('invalid_userid', false);

	list ($realCode, $username, $email) = $smfFunc['db_fetch_row']($request);
	$smfFunc['db_free_result']($request);

	// Is the password actually valid?
	require_once($sourcedir . '/Subs-Auth.php');
	$passwordError = validatePassword($_POST['passwrd1'], $username, array($email));

	// What - it's not?
	if ($passwordError != null)
		fatal_lang_error('profile_error_password_' . $passwordError, false);

	// Quit if this code is not right.
	if (empty($_POST['code']) || substr($realCode, 0, 10) != substr(md5($_POST['code']), 0, 10))
		fatal_error($txt['invalid_activation_code'], false);

	// User validated.  Update the database!
	updateMemberData($_POST['u'], array('validation_code' => '\'\'', 'passwd' => '\'' . sha1(strtolower($username) . $_POST['passwrd1']) . '\''));

	if (isset($modSettings['integrate_reset_pass']) && function_exists($modSettings['integrate_reset_pass']))
		call_user_func($modSettings['integrate_reset_pass'], $username, $username, $_POST['passwrd1']);

	loadTemplate('Login');
	$context += array(
		'page_title' => &$txt['reminder_password_set'],
		'sub_template' => 'login',
		'default_username' => $username,
		'default_password' => $_POST['passwrd1'],
		'never_expire' => false,
		'description' => &$txt['reminder_password_set']
	);
}

// Get the secret answer.
function secret_answerInput()
{
	global $txt, $db_prefix, $context, $smfFunc;

	checkSession();

	// Please provide an email or user....
	if (!isset($_POST['user']) || $_POST['user'] == '')
		fatal_lang_error('username_no_exist', false);

	// Get the stuff....
	$request = $smfFunc['db_query']('', '
		SELECT real_name, member_name, secret_question
		FROM {db_prefix}members
		WHERE member_name = {string:inject_string_1}
		LIMIT 1',
		array(
			'inject_string_1' => $_POST['user'],
		)
	);
	if ($smfFunc['db_num_rows']($request) == 0)
	{
		$smfFunc['db_free_result']($request);

		$request = $smfFunc['db_query']('', '
			SELECT real_name, member_name, secret_question
			FROM {db_prefix}members
			WHERE email_address = {string:inject_string_1}
			LIMIT 1',
			array(
				'inject_string_1' => $_POST['user'],
			)
		);
		if ($smfFunc['db_num_rows']($request) == 0)
			fatal_lang_error('username_no_exist', false);
	}

	$row = $smfFunc['db_fetch_assoc']($request);
	$smfFunc['db_free_result']($request);

	// If there is NO secret question - then throw an error.
	if (trim($row['secret_question']) == '')
		fatal_lang_error('registration_no_secret_question', false);

	// Ask for the answer...
	$context['remind_user'] = $row['member_name'];
	$context['remind_type'] = '';
	$context['secret_question'] = $row['secret_question'];

	$context['sub_template'] = 'ask';
}

function secret_answer2()
{
	global $txt, $db_prefix, $context, $modSettings, $smfFunc;

	checkSession();

	// Hacker?  How did you get this far without an email or username?
	if (!isset($_POST['user']) || $_POST['user'] == '')
		fatal_lang_error('username_no_exist', false);

	loadLanguage('Login');

	// Get the information from the database.
	$request = $smfFunc['db_query']('', '
		SELECT id_member, real_name, member_name, secret_answer, secret_question
		FROM {db_prefix}members
		WHERE member_name = {string:inject_string_1}
		LIMIT 1',
		array(
			'inject_string_1' => $_POST['user'],
		)
	);
	if ($smfFunc['db_num_rows']($request) == 0)
	{
		$smfFunc['db_free_result']($request);

		$request = $smfFunc['db_query']('', '
			SELECT id_member, real_name, member_name, secret_answer, secret_question
			FROM {db_prefix}members
			WHERE email_address = {string:inject_string_1}
			LIMIT 1',
			array(
				'inject_string_1' => $_POST['user'],
			)
		);
		if ($smfFunc['db_num_rows']($request) == 0)
			fatal_lang_error('username_no_exist', false);
	}

	$row = $smfFunc['db_fetch_assoc']($request);
	$smfFunc['db_free_result']($request);

	// Check if the secret answer is correct.
	if ($row['secret_question'] == '' || $row['secret_answer'] == '' || md5($smfFunc['db_unescape_string']($_POST['secret_answer'])) != $row['secret_answer'])
	{
		log_error(sprintf($txt['reminder_error'], $row['member_name']));
		fatal_lang_error('incorrect_answer', false);
	}

	// You can't use a blank one!
	if (strlen(trim($_POST['passwrd1'])) === 0)
		fatal_lang_error('no_password', false);

	// They have to be the same too.
	if ($_POST['passwrd1'] != $_POST['passwrd2'])
		fatal_lang_error('passwords_dont_match', false);

	// Alright, so long as 'yer sure.
	updateMemberData($row['id_member'], array('passwd' => '\'' . sha1(strtolower($row['member_name']) . $_POST['passwrd1']) . '\''));

	if (isset($modSettings['integrate_reset_pass']) && function_exists($modSettings['integrate_reset_pass']))
		call_user_func($modSettings['integrate_reset_pass'], $row['member_name'], $row['member_name'], $_POST['passwrd1']);

	// Tell them it went fine.
	loadTemplate('Login');
	$context += array(
		'page_title' => &$txt['reminder_password_set'],
		'sub_template' => 'login',
		'default_username' => $row['member_name'],
		'default_password' => $_POST['passwrd1'],
		'never_expire' => false,
		'description' => &$txt['reminder_password_set']
	);
}

?>