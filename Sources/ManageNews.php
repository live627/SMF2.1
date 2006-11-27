<?php
/**********************************************************************************
* ManageNews.php                                                                  *
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

/*
	void ManageNews()
		- the entrance point for all News and Newsletter screens.
		- called by ?action=admin;area=news.
		- does the permission checks.
		- calls the appropriate function based on the requested sub-action.

	void EditNews()
		- changes the current news items for the forum.
		- uses the ManageNews template and edit_news sub template.
		- called by ?action=admin;area=news.
		- requires the edit_news permission.
		- writes an entry into the moderation log.
		- uses the edit_news administration area.
		- can be accessed with ?action=editnews.

	void SelectMailingMembers()
		- allows a user to select the membergroups to send their mailing to.
		- uses the ManageNews template and email_members sub template.
		- called by ?action=admin;area=news;sa=mailingmembers.
		- requires the send_mail permission.
		- form is submitted to ?action=admin;area=news;mailingcompose.

	void ComposeMailing()
		- shows a form to edit a forum mailing and its recipients.
		- uses the ManageNews template and email_members_compose sub template.
		- called by ?action=admin;area=news;sa=mailingcompose.
		- requires the send_mail permission.
		- form is submitted to ?action=admin;area=news;sa=mailingsend.

	void SendMailing()
		- handles the sending of the forum mailing in batches.
		- uses the ManageNews template and email_members_send sub template.
		- called by ?action=admin;area=news;sa=mailingsend
		- requires the send_mail permission.
		- redirects to itself when more batches need to be sent.
		- redirects to ?action=admin after everything has been sent.

	void NewsSettings()
		- set general news and newsletter settings and permissions.
		- uses the ManageNews template and news_settings sub template.
		- called by ?action=admin;area=news;sa=settings.
		- requires the forum_admin permission.
*/

// The controller; doesn't do anything, just delegates.
function ManageNews()
{
	global $context, $txt, $scripturl;

	// First, let's do a quick permissions check for the best error message possible.
	isAllowedTo(array('edit_news', 'send_mail', 'admin_forum'));

	loadTemplate('ManageNews');

	// Format: 'sub-action' => array('function', 'permission')
	$subActions = array(
		'editnews' => array('EditNews', 'edit_news'),
		'mailingmembers' => array('SelectMailingMembers', 'send_mail'),
		'mailingcompose' => array('ComposeMailing', 'send_mail'),
		'mailingsend' =>  array('SendMailing', 'send_mail'),
		'settings' => array('ModifyNewsSettings', 'admin_forum'),
	);

	// Default to sub action 'main' or 'settings' depending on permissions.
	$_REQUEST['sa'] = isset($_REQUEST['sa']) && isset($subActions[$_REQUEST['sa']]) ? $_REQUEST['sa'] : (allowedTo('edit_news') ? 'editnews' : (allowedTo('send_mail') ? 'mailingmembers' : 'settings'));

	// Have you got the proper permissions?
	isAllowedTo($subActions[$_REQUEST['sa']][1]);

	// Create the tabs for the template.
	$context['admin_tabs'] = array(
		'title' => $txt['news_title'],
		'help' => 'edit_news',
		'description' => $txt[670],
		'tabs' => array(),
	);
	if (allowedTo('edit_news'))
		$context['admin_tabs']['tabs'][] = array(
			'title' => $txt['admin_news'],
			'description' => $txt[670],
			'href' => $scripturl . '?action=admin;area=news',
			'is_selected' => $_REQUEST['sa'] == 'editnews',
		);
	if (allowedTo('send_mail'))
		$context['admin_tabs']['tabs'][] = array(
			'title' => $txt['admin_newsletters'],
			'description' => $txt['news_mailing_desc'],
			'href' => $scripturl . '?action=admin;area=news;sa=mailingmembers',
			'is_selected' => substr($_REQUEST['sa'], 0, 7) == 'mailing',
		);
	if (allowedTo('admin_forum'))
		$context['admin_tabs']['tabs'][] = array(
			'title' => $txt['settings'],
			'description' => $txt['news_settings_desc'],
			'href' => $scripturl . '?action=admin;area=news;sa=settings',
			'is_selected' => $_REQUEST['sa'] == 'settings',
		);

	$context['admin_tabs']['tabs'][count($context['admin_tabs']['tabs']) - 1]['is_last'] = true;

	$subActions[$_REQUEST['sa']][0]();
}

// Let the administrator(s) edit the news.
function EditNews()
{
	global $txt, $modSettings, $context, $db_prefix, $sourcedir, $user_info;
	global $smfFunc;

	require_once($sourcedir . '/Subs-Post.php');

	// The 'remove selected' button was pressed.
	if (!empty($_POST['delete_selection']) && !empty($_POST['remove']))
	{
		checkSession();

		// Store the news temporarily in this array.
		$temp_news = explode("\n", $modSettings['news']);

		// Remove the items that were selected.
		foreach ($temp_news as $i => $news)
			if (in_array($i, $_POST['remove']))
				unset($temp_news[$i]);

		// Update the database.
		updateSettings(array('news' => addslashes(implode("\n", $temp_news))));

		logAction('news');
	}
	// The 'Save' button was pressed.
	elseif (!empty($_POST['save_items']))
	{
		checkSession();

		foreach ($_POST['news'] as $i => $news)
		{
			if (trim($news) == '')
				unset($_POST['news'][$i]);
			else
				preparsecode($_POST['news'][$i]);
		}

		// Send the new news to the database.
		updateSettings(array('news' => implode("\n", $_POST['news'])));

		// Log this into the moderation log.
		logAction('news');
	}

	// Ready the current news.
	foreach (explode("\n", $modSettings['news']) as $id => $line)
		$context['admin_current_news'][$id] = array(
			'id' => $id,
			'unparsed' => $smfFunc['htmlspecialchars'](un_preparsecode($line)),
			'parsed' => preg_replace('~<([/]?)form[^>]*?[>]*>~i', '<em class="smalltext">&lt;$1form&gt;</em>', parse_bbc($line)),
		);

	$context['sub_template'] = 'edit_news';
	$context['page_title'] = $txt['admin_news'];
}

function SelectMailingMembers()
{
	global $txt, $db_prefix, $context, $modSettings, $smfFunc;

	$context['page_title'] = $txt['admin_newsletters'];

	$context['sub_template'] = 'email_members';

	$context['groups'] = array();
	$postGroups = array();
	$normalGroups = array();

	// If we have post groups disabled then we need to give a "ungrouped members" option.
	if (empty($modSettings['permission_enable_postgroups']))
	{
		$context['groups'][0] = array(
			'id' => 0,
			'name' => $txt['membergroups_members'],
			'member_count' => 0,
		);
		$normalGroups[0] = 0;
	}

	// Get all the extra groups as well as Administrator and Global Moderator.
	$request = $smfFunc['db_query']('', "
		SELECT mg.id_group, mg.group_name, mg.min_posts
		FROM {$db_prefix}membergroups AS mg" . (empty($modSettings['permission_enable_postgroups']) ? "
		WHERE mg.min_posts = -1" : '') . "
		GROUP BY mg.id_group, mg.min_posts, mg.group_name
		ORDER BY mg.min_posts, CASE WHEN mg.id_group < 4 THEN mg.id_group ELSE 4 END, mg.group_name", __FILE__, __LINE__);
	while ($row = $smfFunc['db_fetch_assoc']($request))
	{
		$context['groups'][$row['id_group']] = array(
			'id' => $row['id_group'],
			'name' => $row['group_name'],
			'member_count' => 0,
		);

		if ($row['min_posts'] == -1)
			$normalGroups[$row['id_group']] = $row['id_group'];
		else
			$postGroups[$row['id_group']] = $row['id_group'];
	}
	$smfFunc['db_free_result']($request);

	// If we have post groups, let's count the number of members...
	if (!empty($postGroups))
	{
		$query = $smfFunc['db_query']('', "
			SELECT mem.id_post_group AS id_group, COUNT(*) AS member_count
			FROM {$db_prefix}members AS mem
			WHERE mem.id_post_group IN (" . implode(', ', $postGroups) . ")
			GROUP BY mem.id_post_group", __FILE__, __LINE__);
		while ($row = $smfFunc['db_fetch_assoc']($query))
			$context['groups'][$row['id_group']]['member_count'] += $row['member_count'];
		$smfFunc['db_free_result']($query);
	}

	if (!empty($normalGroups))
	{
		// Find people who are members of this group...
		$query = $smfFunc['db_query']('', "
			SELECT id_group, COUNT(*) AS member_count
			FROM {$db_prefix}members
			WHERE id_group IN (" . implode(',', $normalGroups) . ")
			GROUP BY id_group", __FILE__, __LINE__);
		while ($row = $smfFunc['db_fetch_assoc']($query))
			$context['groups'][$row['id_group']]['member_count'] += $row['member_count'];
		$smfFunc['db_free_result']($query);

		// Also do those who have it as an additional membergroup - this ones more yucky...
		$query = $smfFunc['db_query']('', "
			SELECT mg.id_group, COUNT(*) AS member_count
			FROM ({$db_prefix}membergroups AS mg, {$db_prefix}members AS mem)
			WHERE mg.id_group IN (" . implode(',', $normalGroups) . ")
				AND mem.additional_groups != ''
				AND mem.id_group != mg.id_group
				AND FIND_IN_SET(mg.id_group, mem.additional_groups)
			GROUP BY mg.id_group", __FILE__, __LINE__);
		while ($row = $smfFunc['db_fetch_assoc']($query))
			$context['groups'][$row['id_group']]['member_count'] += $row['member_count'];
		$smfFunc['db_free_result']($query);
	}

	// Any moderators?
	$request = $smfFunc['db_query']('', "
		SELECT COUNT(DISTINCT id_member) AS num_distinct_mods
		FROM {$db_prefix}moderators
		LIMIT 1", __FILE__, __LINE__);
	list ($context['groups'][3]['member_count']) = $smfFunc['db_fetch_row']($request);
	$smfFunc['db_free_result']($request);

	$context['can_send_pm'] = allowedTo('pm_send');
}

// Email your members...
function ComposeMailing()
{
	global $txt, $db_prefix, $sourcedir, $context, $smfFunc;

	$list = array();
	$do_pm = !empty($_POST['sendPM']);

	// Opt-out?
	$condition = isset($_POST['email_force']) ? '' : '
				AND mem.notify_announcements = 1';
				
	// Get a list of all full banned users.  Use their Username and email to find them.  Only get the ones that can't login to turn off notification.
	$request = $smfFunc['db_query']('', "
		SELECT mem.id_member
		FROM {$db_prefix}ban_groups AS bg
		LEFT JOIN {$db_prefix}ban_items AS bi ON (bg.id_ban_group = bi.id_ban_group)
		LEFT JOIN {$db_prefix}members AS mem ON (bi.id_member = mem.id_member OR mem.email_address LIKE bi.email_address)
		WHERE (bg.cannot_access = 1 OR bg.cannot_login = 1) AND (ISNULL(bg.expire_time) OR bg.expire_time > " . time() . ")
			AND NOT ISNULL(mem.id_member)
		GROUP BY mem.id_member", __FILE__, __LINE__);

	$banMembers = array();
	while ($row = $smfFunc['db_fetch_row']($request))
		list ($banMembers[]) = $row;
	$smfFunc['db_free_result']($request);

	$condition .= empty($banMembers) ? '' : '
				AND mem.id_member NOT IN (' . implode(', ', $banMembers) . ')';

	// Did they select moderators too?
	if (!empty($_POST['who']) && in_array(3, $_POST['who']))
	{
		$request = $smfFunc['db_query']('', "
			SELECT DISTINCT " . ($do_pm ? 'mem.member_name' : 'mem.email_address') . " AS identifier
			FROM ({$db_prefix}members AS mem, {$db_prefix}moderators AS mods)
			WHERE mem.id_member = mods.id_member
				AND mem.is_activated = 1$condition", __FILE__, __LINE__);
		while ($row = $smfFunc['db_fetch_assoc']($request))
			$list[] = $row['identifier'];
		$smfFunc['db_free_result']($request);

		unset($_POST['who'][3], $_POST['who'][3]);
	}

	// How about regular members?
	if (!empty($_POST['who']) && in_array(0, $_POST['who']))
	{
		$request = $smfFunc['db_query']('', "
			SELECT " . ($do_pm ? 'mem.member_name' : 'mem.email_address') . " AS identifier
			FROM {$db_prefix}members AS mem
			WHERE mem.id_group = 0
				AND mem.is_activated = 1$condition", __FILE__, __LINE__);
		while ($row = $smfFunc['db_fetch_assoc']($request))
			$list[] = $row['identifier'];
		$smfFunc['db_free_result']($request);

		unset($_POST['who'][0], $_POST['who'][0]);
	}

	// Load all the other groups.
	if (!empty($_POST['who']))
	{
		foreach ($_POST['who'] as $k => $v)
			$_POST['who'][$k] = (int) $v;

		$request = $smfFunc['db_query']('', "
			SELECT " . ($do_pm ? 'mem.member_name' : 'mem.email_address') . " AS identifier
			FROM ({$db_prefix}members AS mem, {$db_prefix}membergroups AS mg)
			WHERE (mg.id_group = mem.id_group OR FIND_IN_SET(mg.id_group, mem.additional_groups) OR mg.id_group = mem.id_post_group)
				AND mg.id_group IN (" . implode(',', $_POST['who']) . ")
				AND mem.is_activated = 1$condition", __FILE__, __LINE__);
		while ($row = $smfFunc['db_fetch_assoc']($request))
			$list[] = $row['identifier'];
		$smfFunc['db_free_result']($request);
	}

	// Tear out duplicates....
	$list = array_unique($list);

	// Sending as a personal message?
	if ($do_pm)
	{
		require_once($sourcedir . '/PersonalMessage.php');
		require_once($sourcedir . '/Subs-Post.php');
		$_REQUEST['bcc'] = implode(',', $list);
		MessagePost();
	}
	else
	{
		$context['page_title'] = $txt['admin_newsletters'];

		// Just send the to list to the template.
		$context['addresses'] = implode('; ', $list);
		$context['default_subject'] = $context['forum_name'] . ': ' . $txt['subject'];
		$context['default_message'] = $txt['message'] . "\n\n" . sprintf($txt['regards_team'], $context['forum_name']) . "\n\n{\$board_url}";

		$context['sub_template'] = 'email_members_compose';
	}
}

function SendMailing()
{
	global $txt, $db_prefix, $sourcedir, $context, $smfFunc;
	global $scripturl, $modSettings, $user_info;

	checkSession();

	require_once($sourcedir . '/Subs-Post.php');

	// How many to send at once? Quantity depends on whether we are queueing or not.
	$num_at_once = empty($modSettings['mail_queue']) ? 60 : 1000;

	// Get all the receivers.
	$addressed = array_unique(explode(';', stripslashes($_POST['emails'])));
	$cleanlist = array();
	foreach ($addressed as $curmem)
	{
		$curmem = trim($curmem);
		if ($curmem != '')
			$cleanlist[$curmem] = $curmem;
	}

	$context['emails'] = implode(';', $cleanlist);
	$context['subject'] = htmlspecialchars(stripslashes($_POST['subject']));
	$context['message'] = htmlspecialchars(stripslashes($_POST['message']));
	$context['send_html'] = !empty($_POST['send_html']) ? '1' : '0';
	$context['parse_html'] = !empty($_POST['parse_html']) ? '1' : '0';
	$context['start'] = isset($_REQUEST['start']) ? $_REQUEST['start'] : 0;

	$send_list = array();
	$i = 0;
	foreach ($cleanlist as $email)
	{
		if (++$i <= $context['start'])
			continue;
		if ($i > $context['start'] + $num_at_once)
			break;

		$send_list[$email] = $email;
	}

	$context['start'] += $num_at_once;
	$context['percentage_done'] = round(($context['start'] * 100) / count($cleanlist), 2);

	// Prepare the message for HTML.
	if (!empty($_POST['send_html']) && !empty($_POST['parse_html']))
		$_POST['message'] = str_replace(array("\n", '  '), array("<br />\n", '&nbsp; '), stripslashes($_POST['message']));
	else
		$_POST['message'] = stripslashes($_POST['message']);

	// Use the default time format.
	$user_info['time_format'] = $modSettings['time_format'];

	$variables = array(
		'{$board_url}',
		'{$current_time}',
		'{$latest_member.link}',
		'{$latest_member.id}',
		'{$latest_member.name}'
	);

	// Replace in all the standard things.
	$_POST['message'] = str_replace($variables,
		array(
			!empty($_POST['send_html']) ? '<a href="' . $scripturl . '">' . $scripturl . '</a>' : $scripturl,
			timeformat(forum_time(), false),
			!empty($_POST['send_html']) ? '<a href="' . $scripturl . '?action=profile;u=' . $modSettings['latestMember'] . '">' . $modSettings['latestRealName'] . '</a>' : $modSettings['latestRealName'],
			$modSettings['latestMember'],
			$modSettings['latestRealName']
		), $_POST['message']);
	$_POST['subject'] = str_replace($variables,
		array(
			$scripturl,
			timeformat(forum_time(), false),
			$modSettings['latestRealName'],
			$modSettings['latestMember'],
			$modSettings['latestRealName']
		), stripslashes($_POST['subject']));

	$from_member = array(
		'{$member.email}',
		'{$member.link}',
		'{$member.id}',
		'{$member.name}'
	);

	// This is here to prevent spam filters from tagging this as spam.
	if (!empty($_POST['send_html']) && preg_match('~\<html~i', $_POST['message']) == 0)
	{
		if (preg_match('~\<body~i', $_POST['message']) == 0)
			$_POST['message'] = '<html><head><title>' . $_POST['subject'] . '</title></head>' . "\n" . '<body>' . $_POST['message'] . '</body></html>';
		else
			$_POST['message'] = '<html>' . $_POST['message'] . '</html>';
	}

	$result = $smfFunc['db_query']('', "
		SELECT real_name, member_name, id_member, email_address
		FROM {$db_prefix}members
		WHERE email_address IN ('" . implode("', '", $send_list) . "')", __FILE__, __LINE__);
	while ($row = $smfFunc['db_fetch_assoc']($result))
	{
		unset($send_list[$row['email_address']]);

		$to_member = array(
			$row['email_address'],
			!empty($_POST['send_html']) ? '<a href="' . $scripturl . '?action=profile;u=' . $row['id_member'] . '">' . $row['real_name'] . '</a>' : $scripturl . '?action=profile;u=' . $row['id_member'],
			$row['id_member'],
			$row['real_name']
		);

		// Send the actual email off, replacing the member dependent variables.
		sendmail($row['email_address'], str_replace($from_member, $to_member, addslashes($_POST['subject'])), str_replace($from_member, $to_member, addslashes($_POST['message'])), null, null, !empty($_POST['send_html']), 0);
	}
	$smfFunc['db_free_result']($result);

	// Send the emails to people who weren't members....
	if (!empty($send_list))
		foreach ($send_list as $email)
		{
			$to_member = array(
				$email,
				!empty($_POST['send_html']) ? '<a href="mailto:' . $email . '">' . $email . '</a>' : $email,
				'??',
				$email
			);

			sendmail($email, str_replace($from_member, $to_member, addslashes($_POST['subject'])), str_replace($from_member, $to_member, addslashes($_POST['message'])), null, null, !empty($_POST['send_html']), 0);
		}

	// Still more to do?
	if (count($cleanlist) > $context['start'])
	{
		$context['page_title'] = $txt['admin_newsletters'];

		$context['sub_template'] = 'email_members_send';
		return;
	}

	redirectexit('action=admin');
}

function ModifyNewsSettings()
{
	global $context, $db_prefix, $sourcedir, $modSettings, $txt, $scripturl;

	$context['page_title'] = $txt['admin_news'] . ' - ' . $txt['settings'];
	$context['sub_template'] = 'show_settings';

	// Needed for the inline permission functions, and the settings template.
	require_once($sourcedir .'/ManagePermissions.php');
	require_once($sourcedir .'/ManageServer.php');

	$config_vars = array(
		array('title', 'settings'),
			// Inline permissions.
			array('permissions', 'edit_news', 'help' => ''),
			array('permissions', 'send_mail'),
		'',
			// Just the remaining settings.
			array('check', 'xmlnews_enable', 'onclick' => 'document.getElementById(\'xmlnews_maxlen\').disabled = !this.checked;'),
			array('text', 'xmlnews_maxlen', 10),
	);

	// Wrap it all up nice and warm...
	$context['post_url'] = $scripturl . '?action=admin;area=news;save;sa=settings';
	$context['permissions_excluded'] = array(-1);

	// Add some javascript at the bottom...
	$context['settings_insert_below'] = '
		<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
			document.getElementById("xmlnews_maxlen").disabled = !document.getElementById("xmlnews_enable").checked;
		// ]]></script>';

	// Saving the settings?
	if (isset($_GET['save']))
	{
		saveDBSettings($config_vars);
		redirectexit('action=admin;area=news;sa=settings');
	}

	prepareDBSettingContext($config_vars);
}

?>