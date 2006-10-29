<?php
/******************************************************************************
* SendTopic.php                                                               *
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

/*	The functions in this file deal with sending topics to a friend or
	moderator, and those functions are:

	void SendTopic()
		- sends information about a topic to a friend.
		- uses the SendTopic template, with the main sub template.
		- requires the send_topic permission.
		- redirects back to the first page of the topic when done.
		- is accessed via ?action=sendtopic.

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

// Send a topic to a friend.
function SendTopic()
{
	global $topic, $txt, $db_prefix, $context, $scripturl, $sourcedir, $smfFunc;

	// Check permissions...
	isAllowedTo('send_topic');

	// We need at least a topic... go away if you don't have one.
	if (empty($topic))
		fatal_lang_error(472, false);

	// Get the topic's subject.
	$request = $smfFunc['db_query']("
		SELECT m.subject
		FROM ({$db_prefix}messages AS m, {$db_prefix}topics AS t)
		WHERE t.ID_TOPIC = $topic
			AND t.ID_FIRST_MSG = m.ID_MSG
		LIMIT 1", __FILE__, __LINE__);
	if ($smfFunc['db_num_rows']($request) == 0)
		fatal_lang_error(472, false);
	$row = $smfFunc['db_fetch_assoc']($request);
	$smfFunc['db_free_result']($request);

	// Censor the subject....
	censorText($row['subject']);

	// Sending yet, or just getting prepped?
	if (empty($_POST['send']))
	{
		loadTemplate('SendTopic');
		$context['page_title'] = sprintf($txt['sendtopic_title'], $row['subject']);
		$context['start'] = $_REQUEST['start'];

		return;
	}

	// Actually send the message...
	checkSession();
	spamProtection('spam');

	// This is needed for sendmail().
	require_once($sourcedir . '/Subs-Post.php');

	// Trim the names..
	$_POST['y_name'] = trim($_POST['y_name']);
	$_POST['r_name'] = trim($_POST['r_name']);

	// Make sure they aren't playing "let's use a fake email".
	if ($_POST['y_name'] == '_' || !isset($_POST['y_name']) || $_POST['y_name'] == '')
		fatal_lang_error(75, false);
	if (!isset($_POST['y_email']) || $_POST['y_email'] == '')
		fatal_lang_error(76, false);
	if (preg_match('~^[0-9A-Za-z=_+\-/][0-9A-Za-z=_\'+\-/\.]*@[\w\-]+(\.[\w\-]+)*(\.[\w]{2,6})$~', stripslashes($_POST['y_email'])) == 0)
		fatal_lang_error(243, false);

	// The receiver should be valid to.
	if ($_POST['r_name'] == '_' || !isset($_POST['r_name']) || $_POST['r_name'] == '')
		fatal_lang_error(75, false);
	if (!isset($_POST['r_email']) || $_POST['r_email'] == '')
		fatal_lang_error(76, false);
	if (preg_match('~^[0-9A-Za-z=_+\-/][0-9A-Za-z=_\'+\-/\.]*@[\w\-]+(\.[\w\-]+)*(\.[\w]{2,6})$~', stripslashes($_POST['r_email'])) == 0)
		fatal_lang_error(243, false);

	// Emails don't like entities...
	$row['subject'] = un_htmlspecialchars($row['subject']);

	// And off we go!
	sendmail($_POST['r_email'], $txt['topic'] . ': ' . $row['subject'] . ' (' . $txt[318] . ' ' . $_POST['y_name'] . ')',
		sprintf($txt['sendtopic_dear'], $_POST['r_name']) . "\n\n" .
		sprintf($txt['sendtopic_this_topic'], $row['subject'], $context['forum_name']) . ":\n\n" .
		$scripturl . '?topic=' . $topic . ".0\n\n" .
		(!empty($_POST['comment']) ? $txt['sendtopic2'] . ":\n" . $_POST['comment'] . "\n\n" : '') .
		$txt['sendtopic_thanks'] . ",\n" .
		$_POST['y_name'], $_POST['y_email']);

	// Back to the topic!
	redirectexit('topic=' . $topic . '.0');
}

// Report a post to the moderator... ask for a comment.
function ReportToModerator()
{
	global $txt, $db_prefix, $topic, $modSettings, $user_info, $ID_MEMBER, $context, $smfFunc;

	// You can't use this if it's off or you are not allowed to do it.
	isAllowedTo('report_any');

	// If they're posting, it should be processed by ReportToModerator2.
	if (isset($_POST['sc']) || isset($_POST['submit']))
		ReportToModerator2();

	// We need a message ID to check!
	if (empty($_GET['msg']) && empty($_GET['mid']))
		fatal_lang_error(1, false);

	// For compatibility, accept mid, but we should be using msg. (not the flavor kind!)
	$_GET['msg'] = empty($_GET['msg']) ? (int) $_GET['mid'] : (int) $_GET['msg'];

	// Check the message's ID - don't want anyone reporting a post they can't even see!
	$result = $smfFunc['db_query']("
		SELECT m.ID_MSG, m.ID_MEMBER, t.ID_MEMBER_STARTED
		FROM ({$db_prefix}messages AS m, {$db_prefix}topics AS t)
		WHERE m.ID_MSG = $_GET[msg]
			AND m.ID_TOPIC = $topic
			AND t.ID_TOPIC = $topic
		LIMIT 1", __FILE__, __LINE__);
	if ($smfFunc['db_num_rows']($result) == 0)
		fatal_lang_error('smf232');
	list ($_GET['msg'], $member, $starter) = $smfFunc['db_fetch_row']($result);
	$smfFunc['db_free_result']($result);

	// If they can't modify their post, then they should be able to report it... otherwise it is illogical.
	if ($member == $ID_MEMBER && (allowedTo(array('modify_own', 'modify_any')) || ($ID_MEMBER == $starter && allowedTo('modify_replies'))))
		fatal_lang_error('rtm_not_own', false);

	// Show the inputs for the comment, etc.
	loadLanguage('Post');
	loadTemplate('SendTopic');

	// This is here so that the user could, in theory, be redirected back to the topic.
	$context['start'] = $_REQUEST['start'];
	$context['message_id'] = $_GET['msg'];

	$context['page_title'] = $txt['rtm1'];
	$context['sub_template'] = 'report';
}

// Send the emails.
function ReportToModerator2()
{
	global $txt, $scripturl, $db_prefix, $topic, $board, $user_info, $ID_MEMBER, $modSettings, $sourcedir, $language, $context, $smfFunc;

	// Check their session... don't want them redirected here without their knowledge.
	checkSession();
	spamProtection('spam');

	// You must have the proper permissions!
	isAllowedTo('report_any');

	require_once($sourcedir . '/Subs-Post.php');

	// Get the basic topic information, and make sure they can see it.
	$_POST['msg'] = (int) $_POST['msg'];

	$request = $smfFunc['db_query']("
		SELECT m.ID_TOPIC, m.ID_BOARD, m.subject, m.body, m.ID_MEMBER AS ID_POSTER, m.posterName, mem.realName
		FROM {$db_prefix}messages AS m
			LEFT JOIN {$db_prefix}members AS mem ON (m.ID_MEMBER = mem.ID_MEMBER)
		WHERE m.ID_MSG = $_POST[msg]
			AND m.ID_TOPIC = $topic
		LIMIT 1", __FILE__, __LINE__);
	if ($smfFunc['db_num_rows']($request) == 0)
		fatal_lang_error('smf232');
	$message = $smfFunc['db_fetch_assoc']($request);
	$smfFunc['db_free_result']($request);

	if ($message['ID_POSTER'] == $ID_MEMBER)
		fatal_lang_error('rtm_not_own', false);

	$posterName = un_htmlspecialchars($message['realName']) . ($message['realName'] != $message['posterName'] ? ' (' . $message['posterName'] . ')' : '');
	$reporterName = un_htmlspecialchars($user_info['name']) . ($user_info['name'] != $user_info['username'] && $user_info['username'] != '' ? ' (' . $user_info['username'] . ')' : '');
	$subject = un_htmlspecialchars($message['subject']);

	// Get a list of members with the moderate_board permission.
	require_once($sourcedir . '/Subs-Members.php');
	$moderators = membersAllowedTo('moderate_board', $board);

	$request = $smfFunc['db_query']("
		SELECT ID_MEMBER, emailAddress, lngfile
		FROM {$db_prefix}members
		WHERE ID_MEMBER IN (" . implode(', ', $moderators) . ")
			AND notifyTypes != 4
		ORDER BY lngfile", __FILE__, __LINE__);

	// Check that moderators do exist!
	if ($smfFunc['db_num_rows']($request) == 0)
		fatal_lang_error('rtm11', false);

	// If we get here, I believe we should make a record of this, for historical significance, yabber.
	if (empty($modSettings['disable_log_report']))
	{
		$request2 = $smfFunc['db_query']("
			SELECT ID_REPORT, ignore_all
			FROM {$db_prefix}log_reported
			WHERE ID_MSG = $_POST[msg]
				AND (closed = 0 OR ignore_all = 1)
			ORDER BY ignore_all DESC", __FILE__, __LINE__);
		if ($smfFunc['db_num_rows']($request2) != 0)
			list ($ID_REPORT, $ignore) = $smfFunc['db_fetch_row']($request2);
		$smfFunc['db_free_result']($request2);

		// If we're just going to ignore these, then who gives a monkeys...
		if (!empty($ignore))
			redirectexit('board=' . $board . '.0');

		// Already reported? My god, we could be dealing with a real rogue here...
		if (!empty($ID_REPORT))
			$smfFunc['db_query']("
				UPDATE {$db_prefix}log_reported
				SET num_reports = num_reports + 1, time_updated = " . time() . "
				WHERE ID_REPORT = $ID_REPORT", __FILE__, __LINE__);
		// Otherwise, we shall make one!
		else
		{
			// Serve, Protect!
			$message = addslashes__recursive($message);
			if (empty($message['realName']))
				$message['realName'] = $message['posterName'];

			$smfFunc['db_query']("
				INSERT INTO {$db_prefix}log_reported
					(ID_MSG, ID_TOPIC, ID_BOARD, ID_MEMBER, membername, subject, body, time_started, time_updated,
						num_reports, closed)
				VALUES
					($_POST[msg], $message[ID_TOPIC], $message[ID_BOARD], $message[ID_POSTER], '$message[realName]', '$message[subject]', '$message[body]', " . time() . ",
						" . time() . ", 1, 0)", __FILE__, __LINE__);
			$ID_REPORT = db_insert_id("{$db_prefix}log_reported", 'ID_REPORT');
		}

		// Now just add our report...
		if ($ID_REPORT)
		{
			$posterComment = strtr(htmlspecialchars($_POST['comment']), array("\r" => '', "\n" => '', "\t" => ''));

			$smfFunc['db_query']("
				INSERT INTO {$db_prefix}log_reported_comments
					(ID_REPORT, ID_MEMBER, membername, comment, time_sent)
				VALUES
					($ID_REPORT, $user_info[id], '$user_info[name]', '$posterComment', " . time() . ")", __FILE__, __LINE__);
		}
	}

	// Send every moderator an email.
	while ($row = $smfFunc['db_fetch_assoc']($request))
	{
		loadLanguage('Post', empty($row['lngfile']) || empty($modSettings['userLanguage']) ? $language : $row['lngfile'], false);

		// Send it to the moderator.
		sendmail($row['emailAddress'], $txt['rtm3'] . ': ' . $subject . ' ' . $txt['rtm4'] . ' ' . $posterName,
			sprintf($txt['rtm_email1'], $subject) . ' ' . $posterName . ' ' . $txt['rtm_email2'] . ' ' . (empty($ID_MEMBER) ? $txt['guest'] . ' (' . $user_info['ip'] . ')' : $reporterName) . ' ' . $txt['rtm_email3'] . ":\n\n" .
			$scripturl . '?topic=' . $topic . '.msg' . $_POST['msg'] . '#msg' . $_POST['msg'] . "\n\n" .
			$txt['rtm_email_comment'] . ":\n" .
			$_POST['comment'] . "\n\n" .
			sprintf($txt['regards_team'], $context['forum_name']), $user_info['email']);
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