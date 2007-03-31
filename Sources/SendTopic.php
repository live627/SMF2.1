<?php
/**********************************************************************************
* SendTopic.php                                                                   *
***********************************************************************************
* SMF: Simple Machines Forum                                                      *
* Open-Source Project Inspired by Zef Hemel (zef@zefhemel.com)                    *
* =============================================================================== *
* Software Version:           SMF 2.0 Alpha                                       *
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

/*	The functions in this file deal with sending topics to a friend or
	moderator, and those functions are:

	void SendTopic()
		- sends information about a topic to a friend.
		- uses the SendTopic template, with the main sub template.
		- requires the send_topic permission.
		- redirects back to the first page of the topic when done.
		- is accessed via ?action=emailuser;sa=sendtopic.

	void CustomEmail()
		- send an email to the user - allow the sender to write the message.
		- can either be passed a user ID as uid or a message id as msg.
		- does not check permissions for a message ID as there is no information disclosed.

	void ReportToModerator()
		- gathers data from the user to report abuse to the moderator(s).
		- uses the ReportToModerator template, main sub template.
		- requires the report_any permission.
		- uses ReportToModerator2() if post data was sent.
		- accessed through ?action=reporttm.

	void ReportToModerator2()
		- sends off emails to all the moderators.
		- sends to administrators and global moderators. (1 and 2)
		- called by ReportToModerator(), and thus has the same permission
		  and setting requirements as it does.
		- accessed through ?action=reporttm when posting.

	void BrowseMessageReports()
		// !!!
*/

// The main handling function for sending specialist (Or otherwise) emails to a user.
function EmailUser()
{
	global $topic, $txt, $db_prefix, $context, $scripturl, $sourcedir, $smfFunc;

	// Load the template.
	loadTemplate('SendTopic');

	$sub_actions = array(
		'email' => 'CustomEmail',
		'sendtopic' => 'SendTopic',
	);

	if (!isset($_GET['sa']) || !isset($sub_actions[$_GET['sa']]))
		$_GET['sa'] = 'sendtopic';

	$sub_actions[$_GET['sa']]();
}

// Send a topic to a friend.
function SendTopic()
{
	global $topic, $txt, $db_prefix, $context, $scripturl, $sourcedir, $smfFunc;

	// Check permissions...
	isAllowedTo('send_topic');

	// We need at least a topic... go away if you don't have one.
	if (empty($topic))
		fatal_lang_error('not_a_topic', false);

	// Get the topic's subject.
	$request = $smfFunc['db_query']('', "
		SELECT m.subject
		FROM {$db_prefix}topics AS t
			INNER JOIN {$db_prefix}messages AS m ON (m.id_msg = t.id_first_msg)
		WHERE t.id_topic = $topic
		LIMIT 1", __FILE__, __LINE__);
	if ($smfFunc['db_num_rows']($request) == 0)
		fatal_lang_error('not_a_topic', false);
	$row = $smfFunc['db_fetch_assoc']($request);
	$smfFunc['db_free_result']($request);

	// Censor the subject....
	censorText($row['subject']);

	// Sending yet, or just getting prepped?
	if (empty($_POST['send']))
	{
		$context['page_title'] = sprintf($txt['sendtopic_title'], $row['subject']);
		$context['start'] = $_REQUEST['start'];

		return;
	}

	// Actually send the message...
	checkSession();
	spamProtection('sendtopc');

	// This is needed for sendmail().
	require_once($sourcedir . '/Subs-Post.php');

	// Trim the names..
	$_POST['y_name'] = trim($_POST['y_name']);
	$_POST['r_name'] = trim($_POST['r_name']);

	// Make sure they aren't playing "let's use a fake email".
	if ($_POST['y_name'] == '_' || !isset($_POST['y_name']) || $_POST['y_name'] == '')
		fatal_lang_error('no_name', false);
	if (!isset($_POST['y_email']) || $_POST['y_email'] == '')
		fatal_lang_error('no_email', false);
	if (preg_match('~^[0-9A-Za-z=_+\-/][0-9A-Za-z=_\'+\-/\.]*@[\w\-]+(\.[\w\-]+)*(\.[\w]{2,6})$~', $smfFunc['db_unescape_string']($_POST['y_email'])) == 0)
		fatal_lang_error('email_invalid_character', false);

	// The receiver should be valid to.
	if ($_POST['r_name'] == '_' || !isset($_POST['r_name']) || $_POST['r_name'] == '')
		fatal_lang_error('no_name', false);
	if (!isset($_POST['r_email']) || $_POST['r_email'] == '')
		fatal_lang_error('no_email', false);
	if (preg_match('~^[0-9A-Za-z=_+\-/][0-9A-Za-z=_\'+\-/\.]*@[\w\-]+(\.[\w\-]+)*(\.[\w]{2,6})$~', $smfFunc['db_unescape_string']($_POST['r_email'])) == 0)
		fatal_lang_error('email_invalid_character', false);

	// Emails don't like entities...
	$row['subject'] = un_htmlspecialchars($row['subject']);

	$replacements = array(
		'TOPICSUBJECT' => $row['subject'],
		'SENDERNAME' => $_POST['y_name'],
		'RECPNAME' => $_POST['r_name'],
		'TOPICLINK' => $scripturl . '?topic=' . $topic . '.0',
	);

	$emailtemplate = 'send_topic';

	if (!empty($_POST['comment']))
	{
		$emailtemplate .= '_comment';
		$replacements['COMMENT'] = $_POST['comment'];
	}

	$emaildata = loadEmailTemplate($emailtemplate, $replacements);
	// And off we go!
	sendmail($_POST['r_email'], $emaildata['subject'], $emaildata['body'], $_POST['y_email']);

	// Back to the topic!
	redirectexit('topic=' . $topic . '.0');
}

// Allow a user to send an email.
function CustomEmail()
{
	global $context, $modSettings, $user_info, $smfFunc, $db_prefix, $txt, $scripturl, $sourcedir;

	// Can the user even see this information?
	if ($user_info['is_guest'] && !empty($modSettings['guest_hideContacts']))
		fatal_lang_error('no_access');

	// Are we sending to a user?
	$context['form_hidden_vars'] = array();
	if (isset($_REQUEST['uid']))
	{
		$request = $smfFunc['db_query']('', "
			SELECT email_address AS email, member_name AS name, id_member
			FROM {$db_prefix}members
			WHERE id_member = " . (int) $_REQUEST['uid'], __FILE__, __LINE__);

		$context['form_hidden_vars']['uid'] = (int) $_REQUEST['uid'];
	}
	elseif (isset($_REQUEST['msg']))
	{
		$request = $smfFunc['db_query']('', "
			SELECT IFNULL(mem.email_address, m.poster_email) AS email, m.poster_name AS name, IFNULL(mem.id_member, 0) AS id_member
			FROM {$db_prefix}messages AS m
				LEFT JOIN {$db_prefix}members AS mem ON (mem.id_member = m.id_member)
			WHERE m.id_msg = " . (int) $_REQUEST['msg'], __FILE__, __LINE__);

		$context['form_hidden_vars']['msg'] = (int) $_REQUEST['msg'];
	}

	if (empty($request) || $smfFunc['db_num_rows']($request) == 0)
		fatal_lang_error('cant_find_user_email');

	$row = $smfFunc['db_fetch_assoc']($request);
	$smfFunc['db_free_result']($request);

	// Setup the context!
	$context['recipient'] = array(
		'id' => $row['id_member'],
		'name' => $row['name'],
		'email' => $row['email'],
		'link' => $row['id_member'] ? '<a href="' . $scripturl . '?action=profile;u=' . $row['id_member'] . '">' . $row['name'] . '</a>' : $row['name'],
	);

	// Are we actually sending it?
	if (isset($_POST['send']) && isset($_POST['email_body']))
	{
		require_once($sourcedir . '/Subs-Post.php');

		checkSession();

		// If it's a guest sort out their names.
		if ($user_info['is_guest'])
		{
			if (empty($_POST['y_name']) || $_POST['y_name'] == '_' || trim($_POST['y_name']) == '')
				fatal_lang_error('no_name', false);
			if (empty($_POST['y_email']))
				fatal_lang_error('no_email', false);
			if (preg_match('~^[0-9A-Za-z=_+\-/][0-9A-Za-z=_\'+\-/\.]*@[\w\-]+(\.[\w\-]+)*(\.[\w]{2,6})$~', $smfFunc['db_unescape_string']($_POST['y_email'])) == 0)
				fatal_lang_error('email_invalid_character', false);

			$from_name = trim($_POST['y_name']);
			$from_email = trim($_POST['y_email']);
		}
		else
		{
			$from_name = $user_info['name'];
			$from_email = $user_info['email'];
		}

		// Check we have a body (etc).
		if (trim($_POST['email_body']) == '' || trim($_POST['email_subject']) == '')
			fatal_lang_error('email_missing_data');

		// We use a template incase they want to customise!
		$replacements = array(
			'EMAILSUBJECT' => $_POST['email_subject'],
			'EMAILBODY' => $_POST['email_body'],
			'SENDERNAME' => $from_name,
			'RECPNAME' => $context['recipient']['name'],
		);

		// Get the template and get out!
		$emaildata = loadEmailTemplate('send_email', $replacements);
		sendmail($context['recipient']['email'], $emaildata['subject'], $emaildata['body'], $from_email);

		// Don't let them send too many!
		spamProtection('sendmail');

		// Now work out where to go!
		if (isset($_REQUEST['uid']))
			redirectexit('action=profile;u=' . (int) $_REQUEST['uid']);
		elseif (isset($_REQUEST['msg']))
			redirectexit('msg=' . (int) $_REQUEST['msg']);
		else
			redirectexit();
	}

	$context['sub_template'] = 'custom_email';
	$context['page_title'] = $txt['send_email'];
}

// Report a post to the moderator... ask for a comment.
function ReportToModerator()
{
	global $txt, $db_prefix, $topic, $modSettings, $user_info, $context, $smfFunc;

	// You can't use this if it's off or you are not allowed to do it.
	isAllowedTo('report_any');

	// If they're posting, it should be processed by ReportToModerator2.
	if (isset($_POST['sc']) || isset($_POST['submit']))
		ReportToModerator2();

	// We need a message ID to check!
	if (empty($_GET['msg']) && empty($_GET['mid']))
		fatal_lang_error('no_access', false);

	// For compatibility, accept mid, but we should be using msg. (not the flavor kind!)
	$_GET['msg'] = empty($_GET['msg']) ? (int) $_GET['mid'] : (int) $_GET['msg'];

	// Check the message's ID - don't want anyone reporting a post they can't even see!
	$result = $smfFunc['db_query']('', "
		SELECT m.id_msg, m.id_member, t.id_member_started
		FROM {$db_prefix}messages AS m
			INNER JOIN {$db_prefix}topics AS t ON (t.id_topic = $topic)
		WHERE m.id_msg = $_GET[msg]
			AND m.id_topic = $topic
		LIMIT 1", __FILE__, __LINE__);
	if ($smfFunc['db_num_rows']($result) == 0)
		fatal_lang_error('no_board');
	list ($_GET['msg'], $member, $starter) = $smfFunc['db_fetch_row']($result);
	$smfFunc['db_free_result']($result);

	// If they can't modify their post, then they should be able to report it... otherwise it is illogical.
	if ($member == $user_info['id'] && (allowedTo(array('modify_own', 'modify_any')) || ($user_info['id'] == $starter && allowedTo('modify_replies'))))
		fatal_lang_error('rtm_not_own', false);

	// Show the inputs for the comment, etc.
	loadLanguage('Post');
	loadTemplate('SendTopic');

	// This is here so that the user could, in theory, be redirected back to the topic.
	$context['start'] = $_REQUEST['start'];
	$context['message_id'] = $_GET['msg'];

	$context['page_title'] = $txt['report_to_mod'];
	$context['sub_template'] = 'report';
}

// Send the emails.
function ReportToModerator2()
{
	global $txt, $scripturl, $db_prefix, $topic, $board, $user_info, $modSettings, $sourcedir, $language, $context, $smfFunc;

	// Check their session... don't want them redirected here without their knowledge.
	checkSession();
	spamProtection('reporttm');

	// You must have the proper permissions!
	isAllowedTo('report_any');

	require_once($sourcedir . '/Subs-Post.php');

	// Get the basic topic information, and make sure they can see it.
	$_POST['msg'] = (int) $_POST['msg'];

	$request = $smfFunc['db_query']('', "
		SELECT m.id_topic, m.id_board, m.subject, m.body, m.id_member AS ID_POSTER, m.poster_name, mem.real_name
		FROM {$db_prefix}messages AS m
			LEFT JOIN {$db_prefix}members AS mem ON (m.id_member = mem.id_member)
		WHERE m.id_msg = $_POST[msg]
			AND m.id_topic = $topic
		LIMIT 1", __FILE__, __LINE__);
	if ($smfFunc['db_num_rows']($request) == 0)
		fatal_lang_error('no_board');
	$message = $smfFunc['db_fetch_assoc']($request);
	$smfFunc['db_free_result']($request);

	if ($message['ID_POSTER'] == $user_info['id'])
		fatal_lang_error('rtm_not_own', false);

	$poster_name = un_htmlspecialchars($message['real_name']) . ($message['real_name'] != $message['poster_name'] ? ' (' . $message['poster_name'] . ')' : '');
	$reporterName = un_htmlspecialchars($user_info['name']) . ($user_info['name'] != $user_info['username'] && $user_info['username'] != '' ? ' (' . $user_info['username'] . ')' : '');
	$subject = un_htmlspecialchars($message['subject']);

	// Get a list of members with the moderate_board permission.
	require_once($sourcedir . '/Subs-Members.php');
	$moderators = membersAllowedTo('moderate_board', $board);

	$request = $smfFunc['db_query']('', "
		SELECT id_member, email_address, lngfile, mod_prefs
		FROM {$db_prefix}members
		WHERE id_member IN (" . implode(', ', $moderators) . ")
			AND notify_types != 4
		ORDER BY lngfile", __FILE__, __LINE__);

	// Check that moderators do exist!
	if ($smfFunc['db_num_rows']($request) == 0)
		fatal_lang_error('no_mods', false);

	// If we get here, I believe we should make a record of this, for historical significance, yabber.
	if (empty($modSettings['disable_log_report']))
	{
		$request2 = $smfFunc['db_query']('', "
			SELECT id_report, ignore_all
			FROM {$db_prefix}log_reported
			WHERE id_msg = $_POST[msg]
				AND (closed = 0 OR ignore_all = 1)
			ORDER BY ignore_all DESC", __FILE__, __LINE__);
		if ($smfFunc['db_num_rows']($request2) != 0)
			list ($id_report, $ignore) = $smfFunc['db_fetch_row']($request2);
		$smfFunc['db_free_result']($request2);

		// If we're just going to ignore these, then who gives a monkeys...
		if (!empty($ignore))
			redirectexit('board=' . $board . '.0');

		// Already reported? My god, we could be dealing with a real rogue here...
		if (!empty($id_report))
			$smfFunc['db_query']('', "
				UPDATE {$db_prefix}log_reported
				SET num_reports = num_reports + 1, time_updated = " . time() . "
				WHERE id_report = $id_report", __FILE__, __LINE__);
		// Otherwise, we shall make one!
		else
		{
			// Serve, Protect!
			$message = escapestring__recursive($message);
			if (empty($message['real_name']))
				$message['real_name'] = $message['poster_name'];

			$smfFunc['db_query']('', "
				INSERT INTO {$db_prefix}log_reported
					(id_msg, id_topic, id_board, id_member, membername, subject, body, time_started, time_updated,
						num_reports, closed)
				VALUES
					($_POST[msg], $message[id_topic], $message[id_board], $message[ID_POSTER], '$message[real_name]', '$message[subject]', '$message[body]', " . time() . ",
						" . time() . ", 1, 0)", __FILE__, __LINE__);
			$id_report = db_insert_id("{$db_prefix}log_reported", 'id_report');
		}

		// Now just add our report...
		if ($id_report)
		{
			$posterComment = strtr(htmlspecialchars($_POST['comment']), array("\r" => '', "\n" => '', "\t" => ''));

			$smfFunc['db_query']('', "
				INSERT INTO {$db_prefix}log_reported_comments
					(id_report, id_member, membername, comment, time_sent)
				VALUES
					($id_report, $user_info[id], '$user_info[name]', '$posterComment', " . time() . ")", __FILE__, __LINE__);
		}
	}

	// Find out who the real moderators are - for mod preferences.
	$request2 = $smfFunc['db_query']('', "
		SELECT id_member
		FROM {$db_prefix}moderators
		WHERE id_board = $board", __FILE__, __LINE__);
	$real_mods = array();
	while ($row = $smfFunc['db_fetch_assoc']($request2))
		$real_mods[] = $row['id_member'];
	$smfFunc['db_free_result']($request2);

	// Send every moderator an email.
	while ($row = $smfFunc['db_fetch_assoc']($request))
	{
		// Maybe they don't want to know?!
		if (!empty($row['mod_prefs']))
		{
			list(,, $pref_binary) = explode('|', $row['mod_prefs']);
			if (!($pref_binary & 1) && (!($pref_binary & 2) || !in_array($row['id_member'], $real_mods)))
				continue;
		}

		$replacements = array(
			'TOPICSUBJECT' => $subject,
			'POSTERNAME' => $poster_name,
			'REPORTERNAME' => $reporterName,
			'TOPICLINK' => $scripturl . '?topic=' . $topic . '.msg' . $_POST['msg'] . '#msg' . $_POST['msg'],
			'REPORTLINK' => !empty($id_report) ? $scripturl . '?action=moderate;area=reports;report=' . $id_report : '',
			'COMMENT' => $_POST['comment'],
		);

		$emaildata = loadEmailTemplate('report_to_moderator', $replacements, empty($row['lngfile']) || empty($modSettings['userLanguage']) ? $language : $row['lngfile']);

		// Send it to the moderator.
		sendmail($row['email_address'], $emaildata['subject'], $emaildata['body'], $user_info['email']);
	}
	$smfFunc['db_free_result']($request);

	// Keep track of when the mod reports get updated, that way we know when we need to look again.
	updateSettings(array('last_mod_report_action' => time()));

	// Back to the board! (you probably don't want to see the post anymore..)
	redirectexit('board=' . $board . '.0');
}

// This function shows all the reported messages, and plenty more ontop.
function BrowseMessageReports()
{
}

?>