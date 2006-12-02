<?php
/**********************************************************************************
* ModerationCenter.php                                                            *
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
	//!!!
*/

// Entry point for the moderation center.
function ModerationMain($dont_call = false)
{
	global $txt, $context, $scripturl, $sc, $modSettings, $user_info, $settings, $sourcedir;

	// Don't run twice!
	if (isset($context['admin_areas']))
		return;

	// We are always moderating here.
	$context['bar_area'] = 'moderate';

	// Everyone using this area must be allowed here!
	if (empty($user_info['mod_cache']['gq']) && empty($user_info['mod_cache']['bq']) && !allowedTo('manage_membergroups'))
		isAllowedTo('access_mod_center');

	// Load the language, and the template.
	loadLanguage('ModerationCenter');
	//!!! The above/below needs to be moved to a small shared template!
	loadTemplate('Admin');

	// Moderation area 'Main'.
	$context['admin_areas']['main'] = array(
		'title' => $txt['mc_main'],
		'areas' => array(
			'index' => array($txt['moderation_center'], 'ModerationCenter.php', 'ModerationHome'),
			'modlog' => array($txt['modlog_view'], 'Modlog.php', 'ViewModlog'),
		),
	);

	// Moderation area 'Posts'.
	$context['admin_areas']['posts'] = array(
		'title' => $txt['mc_posts'],
		'areas' => array(
			'postmod' => array($txt['mc_unapproved_posts'], 'PostModeration.php', 'PostModerationMain', $scripturl . '?action=moderate;area=postmod;sa=posts'),
			'attachmod' => array($txt['mc_unapproved_attachments'], 'PostModeration.php', 'PostModerationMain', $scripturl . '?action=moderate;area=attachmod;sa=attachments'),
			'reports' => array($txt['mc_reported_posts'], 'ModerationCenter.php', 'ReportedPosts'),
		),
	);

	// Moderation area 'Groups'.
	if (!empty($user_info['mod_cache']['gq']) || allowedTo('manage_membergroups'))
	{
		$context['admin_areas']['groups'] = array(
			'title' => $txt['mc_groups'],
			'areas' => array(
				'groups' => array($txt['mc_group_requests'], 'Groups.php', 'Groups', $scripturl . '?action=moderate;area=groups;sa=requests'),
				'viewgroups' => array($txt['mc_view_groups'], 'Groups.php', 'Groups'),
			),
		);
	}

	// I don't know where we're going - I don't know where we've been...
	$area = isset($_GET['area']) ? $_GET['area'] : 'index';
	foreach ($context['admin_areas'] as $id => $section)
	{
		if (isset($section['areas'][$area]))
		{
			$context['admin_section'] = $id;
			foreach ($section['areas'] as $id => $elements)
				if ($id == $area)
				{
					$actual_area = $area;
					$context['admin_area'] = isset($elements['select']) ? $elements['select'] : $area;
				}
		}
	}

	if (empty($context['admin_area']))
	{
		$actual_area = 'index';
		$context['admin_area'] = 'index';
		$context['admin_section'] = 'main';
	}

	// And put the lovely surround around it all, beutiful.
	$context['template_layers'][] = 'admin';

	// Now - finally - call the right place!
	if (!$dont_call)
	{
		require_once($sourcedir . '/' . $context['admin_areas'][$context['admin_section']]['areas'][$actual_area][1]);
		$context['admin_areas'][$context['admin_section']]['areas'][$actual_area][2]();
	}
}

// This function basically is the home page of the moderation center.
function ModerationHome()
{
	global $txt, $context, $scripturl, $modSettings, $user_info;

	loadTemplate('ModerationCenter');

	$context['page_title'] = $txt['moderation_center'];
	$context['sub_template'] = 'moderation_center';

	//!!! Load what blocks the user actually wants...
	$mod_blocks = array('LatestNews', 'WatchedUsers', 'ReportedPosts', 'GroupRequests');

	$context['mod_blocks'] = array();
	foreach ($mod_blocks as $block)
	{
		$block = 'ModBlock' . $block;
		if (function_exists($block))
			$context['mod_blocks'][] = $block();
	}
}

// Just prepares the time stuff for the simple machines latest news.
function ModBlockLatestNews()
{
	global $context, $user_info;

	$context['time_format'] = urlencode($user_info['time_format']);

	// Return the template to use.
	return 'latest_news';
}

// Show a list of the most active watched users.
function ModBlockWatchedUsers()
{
	global $context, $db_prefix;

	$context['watched_users'] = array();

	return 'watched_users';
}

// Show a list of the most recent reported posts.
function ModBlockReportedPosts()
{
	global $context, $db_prefix, $user_info, $scripturl, $smfFunc;

	$context['reported_posts'] = array();
	// Can they even moderate any boards?
	if (empty($user_info['mod_cache']['bq']))
		return 'reported_posts_block';

	// By George, that means we in a position to get the reports, jolly good.
	$request = $smfFunc['db_query']('', "
		SELECT lr.id_report, lr.id_msg, lr.id_topic, lr.id_board, lr.id_member, lr.subject,
			lr.num_reports, IFNULL(mem.real_name, lr.membername) AS author_name,
			IFNULL(mem.id_member, 0) AS ID_AUTHOR		
		FROM {$db_prefix}log_reported AS lr
			LEFT JOIN {$db_prefix}members AS mem ON (mem.id_member = lr.id_member)
		WHERE " . ($user_info['mod_cache']['bq'] == 1 ? '1=1' : 'lr.' . $user_info['mod_cache']['bq']) . "
			AND lr.closed = 0
			AND lr.ignore_all = 0
		ORDER BY lr.time_updated DESC
		LIMIT 10", __FILE__, __LINE__);
	while ($row = $smfFunc['db_fetch_assoc']($request))
	{
		$context['reported_posts'][] = array(
			'id' => $row['id_report'],
			'topic_href' => $scripturl . '?topic=' . $row['id_topic'] . '.msg' . $row['id_msg'] . '#msg' . $row['id_msg'],
			'report_href' => $scripturl . '?action=moderate;area=reports;report=' . $row['id_report'],
			'author' => array(
				'id' => $row['ID_AUTHOR'],
				'name' => $row['author_name'],
				'link' => $row['ID_AUTHOR'] ? '<a href="' . $scripturl . '?action=profile;u=' . $row['ID_AUTHOR'] . '">' . $row['author_name'] . '</a>' : $row['author_name'],
				'href' => $scripturl . '?action=profile;u=' . $row['ID_AUTHOR'],
			),
			'comments' => array(),
			'subject' => $row['subject'],
			'num_reports' => $row['num_reports'],
		);
	}
	$smfFunc['db_free_result']($request);

	return 'reported_posts_block';
}

// Show a list of all the group requests they can see.
function ModBlockGroupRequests()
{
	global $context, $db_prefix, $user_info, $scripturl, $smfFunc;

	$context['group_requests'] = array();
	// Make sure they can even moderate someone!
	if (empty($user_info['mod_cache']['gq']))
		return 'group_requests_block';

	// What requests are outstanding?
	$request = $smfFunc['db_query']('', "
		SELECT lgr.id_request, lgr.id_member, lgr.id_group, lgr.time_applied, mem.member_name, mg.group_name
		FROM {$db_prefix}log_group_requests AS lgr
			INNER JOIN {$db_prefix}members AS mem ON (mem.id_member = lgr.id_member)
			INNER JOIN {$db_prefix}membergroups AS mg ON (mg.id_group = lgr.id_group)
		WHERE " . ($user_info['mod_cache']['gq'] == 1 ? '1=1' : 'lgr.' . $user_info['mod_cache']['gq']) . "
		ORDER BY lgr.id_request DESC
		LIMIT 10", __FILE__, __LINE__);
	while ($row = $smfFunc['db_fetch_assoc']($request))
	{
		$context['group_requests'][] = array(
			'id' => $row['id_request'],
			'request_href' => $scripturl . '?action=groups;sa=requests;gid=' . $row['id_group'],
			'member' => array(
				'id' => $row['id_member'],
				'name' => $row['member_name'],
				'link' => '<a href="' . $scripturl . '?action=profile;u=' . $row['id_member'] . '">' . $row['member_name'] . '</a>',
				'href' => $scripturl . '?action=profile;u=' . $row['id_member'],
			),
			'group' => array(
				'id' => $row['id_group'],
				'name' => $row['group_name'],
			),
			'time_submitted' => timeformat($row['time_applied']),
		);
	}
	$smfFunc['db_free_result']($request);

	return 'group_requests_block';
}

//!!! This needs to be given it's own file ;)
// Browse all the reported posts...
function ReportedPosts()
{
	global $txt, $context, $scripturl, $modSettings, $user_info, $db_prefix, $smfFunc;

	// This comes under the umbrella of moderating posts.
	if (empty($user_info['mod_cache']['bq']))
		isAllowedTo('moderate_forum');

	// Are they wanting to view a particular report?
	if (!empty($_REQUEST['report']))
		return ModReport();

	// First load the template.
	loadTemplate('ModerationCenter');

	// Set up the comforting bits...
	$context['page_title'] = $txt['mc_reported_posts'];
	$context['sub_template'] = 'reported_posts';

	// Are we viewing open or closed reports?
	$context['view_closed'] = isset($_GET['c']) ? 1 : 0;

	// Put the open and closed options into tabs, because we can...
	$context['admin_tabs'] = array(
		'title' => $txt['mc_reported_posts'],
		'help' => '',
		'description' => $txt['mc_reported_posts_desc'],
		'tabs' => array(
			'browse' => array(
				'title' => $txt['mc_reportedp_open'],
				'href' => $scripturl . '?action=moderate;area=reports',
				'is_selected' => !$context['view_closed'],
			),
			'settings' => array(
				'title' => $txt['mc_reportedp_closed'],
				'href' => $scripturl . '?action=moderate;area=reports;c=1',
				'is_last' => true,
				'is_selected' => $context['view_closed'],
			),
		),
	);

	// Are we doing any work?
	if ((isset($_GET['ignore']) || isset($_GET['close'])) && isset($_GET['rid']))
	{
		checkSession('get');
		$_GET['rid'] = (int) $_GET['rid'];

		// Update the report...
		$smfFunc['db_query']('', "
			UPDATE {$db_prefix}log_reported
			SET " . (isset($_GET['ignore']) ? 'ignore_all = ' . (int) $_GET['ignore'] : 'closed = ' . (int) $_GET['close']) . "
			WHERE id_report = $_GET[rid]
				AND " . ($user_info['mod_cache']['bq'] == 1 ? '1=1' : $user_info['mod_cache']['bq']), __FILE__, __LINE__);

		// Time to update.
		updateSettings(array('last_mod_report_action' => time()));
		recountOpenReports();
	}
	elseif (isset($_POST['close']) && isset($_POST['close_selected']))
	{
		checkSession('post');

		// All the ones to update...
		$toClose = array();
		foreach ($_POST['close'] as $rid)
			$toClose[] = (int) $rid;

		if (!empty($toClose))
		{
			$smfFunc['db_query']('', "
				UPDATE {$db_prefix}log_reported
				SET closed = 1
				WHERE id_report IN (" . implode(',', $toClose) . ")
					AND " . ($user_info['mod_cache']['bq'] == 1 ? '1=1' : $user_info['mod_cache']['bq']), __FILE__, __LINE__);

			// Time to update.
			updateSettings(array('last_mod_report_action' => time()));
			recountOpenReports();
		}
	}

	// How many entries are we viewing?
	$request = $smfFunc['db_query']('', "
		SELECT COUNT(*)
		FROM {$db_prefix}log_reported AS lr
		WHERE closed = $context[view_closed]
			AND " . ($user_info['mod_cache']['bq'] == 1 ? '1=1' : 'lr.' . $user_info['mod_cache']['bq']), __FILE__, __LINE__);
	list ($context['total_reports']) = $smfFunc['db_fetch_row']($request);
	$smfFunc['db_free_result']($request);

	// So, that means we can page index, yes?
	$context['page_index'] = constructPageIndex($scripturl . '?action=moderate;area=reports' . ($context['view_closed'] ? ';c=1' : ''), $_GET['start'], $context['total_reports'], 10);
	$context['start'] = $_GET['start'];

	// By George, that means we in a position to get the reports, golly good.
	$request = $smfFunc['db_query']('', "
		SELECT lr.id_report, lr.id_msg, lr.id_topic, lr.id_board, lr.id_member, lr.subject, lr.body,
			lr.time_started, lr.time_updated, lr.num_reports, lr.closed, lr.ignore_all,
			IFNULL(mem.real_name, lr.membername) AS author_name, IFNULL(mem.id_member, 0) AS ID_AUTHOR		
		FROM {$db_prefix}log_reported AS lr
			LEFT JOIN {$db_prefix}members AS mem ON (mem.id_member = lr.id_member)
		WHERE lr.closed = $context[view_closed]
			AND " . ($user_info['mod_cache']['bq'] == 1 ? '1=1' : 'lr.' . $user_info['mod_cache']['bq']) . "
		ORDER BY lr.time_updated DESC
		LIMIT $context[start], 10", __FILE__, __LINE__);
	$context['reports'] = array();
	$report_ids = array();
	while ($row = $smfFunc['db_fetch_assoc']($request))
	{
		$report_ids[] = $row['id_report'];
		$context['reports'][$row['id_report']] = array(
			'id' => $row['id_report'],
			'topic_href' => $scripturl . '?topic=' . $row['id_topic'] . '.msg' . $row['id_msg'] . '#msg' . $row['id_msg'],
			'report_href' => $scripturl . '?action=moderate;area=reports;report=' . $row['id_report'],
			'author' => array(
				'id' => $row['ID_AUTHOR'],
				'name' => $row['author_name'],
				'link' => $row['ID_AUTHOR'] ? '<a href="' . $scripturl . '?action=profile;u=' . $row['ID_AUTHOR'] . '">' . $row['author_name'] . '</a>' : $row['author_name'],
				'href' => $scripturl . '?action=profile;u=' . $row['ID_AUTHOR'],
			),
			'comments' => array(),
			'time_started' => timeformat($row['time_started']),
			'last_updated' => timeformat($row['time_updated']),
			'subject' => $row['subject'],
			'body' => parse_bbc($row['body']),
			'num_reports' => $row['num_reports'],
			'closed' => $row['closed'],
			'ignore' => $row['ignore_all']
		);
	}
	$smfFunc['db_free_result']($request);

	// Now get all the people who reported it.
	if (!empty($report_ids))
	{
		$request = $smfFunc['db_query']('', "
			SELECT lrc.id_comment, lrc.id_report, lrc.time_sent, lrc.comment,
				IFNULL(mem.id_member, 0) AS id_member, IFNULL(mem.real_name, lrc.membername) AS reporter
			FROM {$db_prefix}log_reported_comments AS lrc
				LEFT JOIN {$db_prefix}members AS mem ON (mem.id_member = lrc.id_member)
			WHERE id_report IN (" . implode(',', $report_ids) . ")", __FILE__, __LINE__);
		while ($row = $smfFunc['db_fetch_assoc']($request))
		{
			if ($row['id_member'] == 0 || !isset($context['reports'][$row['id_report']]['comments'][$row['id_member']]))
				$context['reports'][$row['id_report']]['comments'][$row['id_member']] = array(
					'id' => $row['id_comment'],
					'message' => $row['comment'],
					'time' => timeformat($row['time_sent']),
					'member' => array(
						'id' => $row['id_member'],
						'name' => $row['reporter'],
						'link' => $row['id_member'] ? '<a href="' . $scripturl . '?action=profile;u=' . $row['id_member'] . '">' . $row['reporter'] . '</a>' : $row['reporter'],
						'href' => $scripturl . '?action=profile;u=' . $row['id_member'],
					),
				);
		}
		$smfFunc['db_free_result']($request);
	}
}

// Act as an entrace for all group related activity.
//!!! As for most things in this file, this needs to be moved somewhere appropriate.
function ModerateGroups()
{
	global $txt, $context, $scripturl, $modSettings, $user_info, $db_prefix;

	// You need to be allowed to moderate groups...
	if (empty($user_info['mod_cache']['gq']))
		isAllowedTo('manage_membergroups');

	// Load the group templates.
	loadTemplate('ModerationCenter');

	// Setup the subactions...
	$subactions = array(
		'requests' => 'GroupRequests',
		'view' => 'ViewGroups',
	);

	if (!isset($_GET['sa']) || !isset($subactions[$_GET['sa']]))
		$_GET['sa'] = 'view';
	$context['sub_action'] = $_GET['sa'];

	// Call the relevant function.
	$subactions[$context['sub_action']]();
}

// How many open reports do we have?
function recountOpenReports()
{
	global $user_info, $db_prefix, $context, $smfFunc;

	$request = $smfFunc['db_query']('', "
		SELECT COUNT(*)
		FROM {$db_prefix}log_reported
		WHERE " . $user_info['mod_cache']['bq'] . "
			AND closed = 0
			AND ignore_all = 0", __FILE__, __LINE__);
	list ($open_reports) = $smfFunc['db_fetch_row']($request);
	$smfFunc['db_free_result']($request);

	$_SESSION['rc'] = array(
		'id' => $user_info['id'],
		'time' => time(),
		'reports' => $open_reports,
	);

	$context['open_mod_reports'] = $open_reports;
}

function ModReport()
{
	global $db_prefix, $user_info, $context, $sourcedir, $scripturl, $txt, $smfFunc;

	// Have to at least give us something
	if (empty($_REQUEST['report']))
		fatal_lang_error('mc_no_modreport_specified');

	// Integers only please
	$_REQUEST['report'] = (int) $_REQUEST['report'];

	// Get the report details, need this so we can limit access to a particular board
	$request = $smfFunc['db_query']('', "
		SELECT lr.id_report, lr.id_msg, lr.id_topic, lr.id_board, lr.id_member, lr.subject, lr.body,
			lr.time_started, lr.time_updated, lr.num_reports, lr.closed, lr.ignore_all,
			IFNULL(mem.real_name, lr.membername) AS author_name, IFNULL(mem.id_member, 0) AS ID_AUTHOR		
		FROM {$db_prefix}log_reported AS lr
			LEFT JOIN {$db_prefix}members AS mem ON (mem.id_member = lr.id_member)
		WHERE lr.id_report = $_REQUEST[report]
			AND " . ($user_info['mod_cache']['bq'] == 1 ? '1=1' : 'lr.' . $user_info['mod_cache']['bq']) . "
		LIMIT 1", __FILE__, __LINE__);
	
	// So did we find anything?
	if (!$smfFunc['db_num_rows']($request))
		fatal_lang_error('mc_no_modreport_found');

	// Woohoo we found a report and they can see it!  Bad news is we have more work to do
	$row = $smfFunc['db_fetch_assoc']($request);
	$smfFunc['db_free_result']($request);
	
	$context['report'] = array(
		'id' => $row['id_report'],
		'topic_id' => $row['id_topic'],
		'board_id' => $row['id_board'],
		'message_id' => $row['id_msg'],
		'message_href' => $scripturl . '?msg=' . $row['id_msg'],
		'message_link' => '<a href="' . $scripturl . '?msg=' . $row['id_msg'] . '">' . $row['subject'] . '</a>',
		'report_href' => $scripturl . '?action=moderate;area=reports;report=' . $row['id_report'],
		'author' => array(
			'id' => $row['ID_AUTHOR'],
			'name' => $row['author_name'],
			'link' => $row['ID_AUTHOR'] ? '<a href="' . $scripturl . '?action=profile;u=' . $row['ID_AUTHOR'] . '">' . $row['author_name'] . '</a>' : $row['author_name'],
			'href' => $scripturl . '?action=profile;u=' . $row['ID_AUTHOR'],
		),
		'comments' => array(),
		'time_started' => timeformat($row['time_started']),
		'last_updated' => timeformat($row['time_updated']),
		'subject' => $row['subject'],
		'body' => parse_bbc($row['body']),
		'num_reports' => $row['num_reports'],
		'closed' => $row['closed'],
		'ignore' => $row['ignore_all']
	);

	// So what bad things do the reporters have to say about it?
	$request = $smfFunc['db_query']('', "
		SELECT lrc.id_comment, lrc.id_report, lrc.time_sent, lrc.comment,
			IFNULL(mem.id_member, 0) AS id_member, IFNULL(mem.real_name, lrc.membername) AS reporter
		FROM {$db_prefix}log_reported_comments AS lrc
			LEFT JOIN {$db_prefix}members AS mem ON (mem.id_member = lrc.id_member)
		WHERE id_report = " . $context['report']['id'], __FILE__, __LINE__);
	while ($row = $smfFunc['db_fetch_assoc']($request))
	{
		if ($row['id_member'] == 0 || !isset($context['report']['comments'][$row['id_member']]))
			$context['report']['comments'][$row['id_member']] = array(
				'id' => $row['id_comment'],
				'message' => $row['comment'],
				'time' => timeformat($row['time_sent']),
				'member' => array(
					'id' => $row['id_member'],
					'name' => $row['reporter'],
					'link' => $row['id_member'] ? '<a href="' . $scripturl . '?action=profile;u=' . $row['id_member'] . '">' . $row['reporter'] . '</a>' : $row['reporter'],
					'href' => $scripturl . '?action=profile;u=' . $row['id_member'],
				),
			);
	}
	$smfFunc['db_free_result']($request);

	// What have the other moderators done to this message?
	// !!! Should this limit the results to the boards the mod can see or not?
	
	require_once($sourcedir . '/Modlog.php');
	getModLogEntries('lm.id_msg = ' . $context['report']['message_id']);

	// Finally we are done :P
	loadTemplate('ModerationCenter');
	$context['page_title'] = sprintf($txt['mc_viewmodreport'], $context['report']['subject'], $context['report']['author']['name']);
	$context['sub_template'] = 'viewmodreport';

}

?>