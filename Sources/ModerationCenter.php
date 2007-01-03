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
			'notice' => array('', 'ModerationCenter.php', 'ShowNotice'),
		),
	);

	// Show the warning log?
	if ($modSettings['warning_settings']{0} == 1)
		$context['admin_areas']['main']['areas']['warnlog'] = array($txt['mc_warning_log'], 'ModerationCenter.php', 'ViewWarningLog');
	// Add user watch.
	$context['admin_areas']['main']['areas']['userwatch'] = array($txt['mc_watched_users_title'], 'ModerationCenter.php', 'ViewWatchedUsers');

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
	global $context, $db_prefix, $smfFunc, $scripturl, $modSettings;

	if (($watched_users = cache_get_data('recent_user_watches', 240)) === null)
	{
		list (, $modSettings['warn_watch']) = explode(',', $modSettings['warning_settings']);
		$modSettings['warn_watch'] = empty($modSettings['warn_watch']) ? 1 : $modSettings['warn_watch'];
		$request = $smfFunc['db_query']('', "
			SELECT id_member, real_name, last_login
			FROM {$db_prefix}members
			WHERE warning >= $modSettings[warn_watch]
			ORDER BY last_login DESC
			LIMIT 10", __FILE__, __LINE__);
		$watched_users = array();
		while ($row = $smfFunc['db_fetch_assoc']($request))
			$watched_users[] = $row;
		$smfFunc['db_free_result']($request);
	
		cache_put_data('recent_user_watches', $watched_users, 240);
	}

	$context['watched_users'] = array();
	foreach ($watched_users as $user)
	{
		$context['watched_users'][] = array(
			'id' => $user['id_member'],
			'name' => $user['real_name'],
			'link' => '<a href="' . $scripturl . '?action=profile;u=' . $user['id_member'] . '">' . $user['real_name'] . '</a>',
			'href' => $scripturl . '?action=profile;u=' . $user['id_member'],
			'last_login' => timeformat($user['last_login']),
		);
	}

	return 'watched_users';
}

// Show a list of the most recent reported posts.
function ModBlockReportedPosts()
{
	global $context, $db_prefix, $user_info, $scripturl, $smfFunc;

	// Got the info already?
	$cachekey = md5(serialize($user_info['mod_cache']['bq']));
	$context['reported_posts'] = array();
	if (empty($user_info['mod_cache']['bq']))
		return 'reported_posts_block';

	if (($reported_posts = cache_get_data('reported_posts_' . $cachekey, 240)) === null)
	{
		// By George, that means we in a position to get the reports, jolly good.
		$request = $smfFunc['db_query']('', "
			SELECT lr.id_report, lr.id_msg, lr.id_topic, lr.id_board, lr.id_member, lr.subject,
				lr.num_reports, IFNULL(mem.real_name, lr.membername) AS author_name,
				IFNULL(mem.id_member, 0) AS id_author		
			FROM {$db_prefix}log_reported AS lr
				LEFT JOIN {$db_prefix}members AS mem ON (mem.id_member = lr.id_member)
			WHERE " . ($user_info['mod_cache']['bq'] == 1 ? '1=1' : 'lr.' . $user_info['mod_cache']['bq']) . "
				AND lr.closed = 0
				AND lr.ignore_all = 0
			ORDER BY lr.time_updated DESC
			LIMIT 10", __FILE__, __LINE__);
		$reported_posts = array();
		while ($row = $smfFunc['db_fetch_assoc']($request))
			$reported_posts[] = $row;
		$smfFunc['db_free_result']($request);

		// Cache it.
		cache_put_data('reported_posts_' . $cachekey, $reported_posts, 240);
	}

	$context['reported_posts'] = array();
	foreach ($reported_posts as $row)
	{
		$context['reported_posts'][] = array(
			'id' => $row['id_report'],
			'topic_href' => $scripturl . '?topic=' . $row['id_topic'] . '.msg' . $row['id_msg'] . '#msg' . $row['id_msg'],
			'report_href' => $scripturl . '?action=moderate;area=reports;report=' . $row['id_report'],
			'author' => array(
				'id' => $row['id_author'],
				'name' => $row['author_name'],
				'link' => $row['id_author'] ? '<a href="' . $scripturl . '?action=profile;u=' . $row['id_author'] . '">' . $row['author_name'] . '</a>' : $row['author_name'],
				'href' => $scripturl . '?action=profile;u=' . $row['id_author'],
			),
			'comments' => array(),
			'subject' => $row['subject'],
			'num_reports' => $row['num_reports'],
		);
	}

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

// Show a notice sent to a user.
function ShowNotice()
{
	global $db_prefix, $smfFunc, $txt, $context;

	$context['page_title'] = $txt['show_notice'];
	$context['sub_template'] = 'show_notice';
	$context['template_layers'] = array();

	//!!! Assumes nothing needs permission more than accessing moderation center!
	$id_notice = (int) $_GET['nid'];
	$request = $smfFunc['db_query']('', "
		SELECT body, subject
		FROM {$db_prefix}log_member_notices
		WHERE id_notice = $id_notice", __FILE__, __LINE__);
	if ($smfFunc['db_num_rows']($request) == 0)
		fatal_lang_error('no_access');
	list ($context['notice_body'], $context['notice_subject']) = $smfFunc['db_fetch_row']($request);
	$smfFunc['db_free_result']($request);

	$context['notice_body'] = strtr($context['notice_body'], array("\n" => '<br />'));
}

// View watched users.
function ViewWatchedUsers()
{
	global $db_prefix, $smfFunc, $modSettings, $context, $txt, $scripturl, $user_info, $sourcedir;

	// Some important context!
	$context['page_title'] = $txt['mc_watched_users_title'];
	$context['view_posts'] = isset($_GET['post']);
	$context['sub_template'] = $context['view_posts'] ? 'user_watches_posts' : 'user_watches_member';

	loadTemplate('ModerationCenter');

	// Get some key settings!
	list ($modSettings['warn_enabled'], $modSettings['warn_watch']) = explode(',', $modSettings['warning_settings']);
	$modSettings['warn_watch'] = empty($modSettings['warn_watch']) ? 1 : $modSettings['warn_watch'];

	// Put some pretty tabs on cause we're gonna be doing hot stuff here...
	$context['admin_tabs'] = array(
		'title' => $txt['mc_watched_users_title'],
		'help' => '',
		'description' => $txt['mc_watched_users_desc'],
		'tabs' => array(
			'member' => array(
				'title' => $txt['mc_watched_users_member'],
				'href' => $scripturl . '?action=moderate;area=userwatch',
				'is_selected' => !$context['view_posts'],
			),
			'post' => array(
				'title' => $txt['mc_watched_users_post'],
				'href' => $scripturl . '?action=moderate;area=userwatch;post',
				'is_last' => true,
				'is_selected' => $context['view_posts'],
			),
		),
	);

	// First off - are we deleting?
	if (!empty($_REQUEST['delete']))
	{
		checkSession(!is_array($_REQUEST['delete']) ? 'get' : 'post');

		$toDelete = array();
		if (!is_array($_REQUEST['delete']))
			$toDelete[] = (int) $_REQUEST['delete'];
		else
			foreach ($_REQUEST['delete'] as $did)
				$toDelete[] = (int) $did;

		if (!empty($toDelete))
		{
			require_once($sourcedir . '/RemoveTopic.php');
			// If they don't have permission we'll let it error - either way no chance of a security slip here!
			foreach ($toDelete as $did)
				removeMessage($did);
		}
	}

	// Get the number of entries.
	if (!$context['view_posts'])
		$request = $smfFunc['db_query']('', "
			SELECT COUNT(*)
			FROM {$db_prefix}members
			WHERE warning >= $modSettings[warn_watch]", __FILE__, __LINE__);
	else
	{
		// Still obey permissions!
		$approve_boards = boardsAllowedTo('approve_posts');
		$delete_boards = boardsAllowedTo('delete_any');

		if ($approve_boards == array(0))
			$approve_query = '';
		elseif (!empty($approve_boards))
			$approve_query = ' AND m.id_board IN (' . implode(',', $approve_boards) . ')';
		// Nada, zip, etc...
		else
			$approve_query = ' AND 0';

		$request = $smfFunc['db_query']('', "
			SELECT COUNT(*)
			FROM {$db_prefix}messages AS m
				INNER JOIN {$db_prefix}members AS mem ON (mem.id_member = m.id_member)
				INNER JOIN {$db_prefix}boards AS b ON (b.id_board = m.id_board)
			WHERE warning >= $modSettings[warn_watch]
				AND $user_info[query_see_board]
				$approve_query", __FILE__, __LINE__);
	}
	list ($context['total_entries']) = $smfFunc['db_fetch_row']($request);
	$smfFunc['db_free_result']($request);

	// Do the page index.
	$perPage = (int) $modSettings['defaultMaxMessages'];
	$context['start'] = (int) $_REQUEST['start'];
	$context['page_index'] = constructPageIndex($scripturl . '?action=moderate;area=userwatch' . $context['view_posts'] ? ';post' : '', $context['start'], $context['total_entries'], $perPage);

	$context['can_issue_warnings'] = allowedTo('issue_warning');

	// Now get the data itself.
	if (!$context['view_posts'])
	{
		$request = $smfFunc['db_query']('', "
			SELECT id_member, member_name, last_login, posts, warning
			FROM {$db_prefix}members
			WHERE warning >= $modSettings[warn_watch]
			ORDER BY last_login DESC
			LIMIT $context[start], $perPage", __FILE__, __LINE__);
		$context['member_watches'] = array();
		$members = array();
		while ($row = $smfFunc['db_fetch_assoc']($request))
		{
			$context['member_watches'][$row['id_member']] = array(
				'id' => $row['id_member'],
				'name' => $row['member_name'],
				'last_login' => timeformat($row['last_login']),
				'last_post' => $txt['not_applicable'],
				'last_post_id' => 0,
				'warning' => $row['warning'],
				'posts' => $row['posts'],
			);
			$members[] = $row['id_member'];
		}
		$smfFunc['db_free_result']($request);

		if (!empty($members))
		{
			$request = $smfFunc['db_query']('', "
				SELECT MAX(m.poster_time) AS last_post, MAX(m.id_msg) AS last_post_id, m.id_member
				FROM {$db_prefix}messages AS m
					INNER JOIN {$db_prefix}boards AS b ON (b.id_board = m.id_board)
				WHERE m.id_member IN (" . implode(',', $members) . ")
					AND $user_info[query_see_board]
					AND m.approved = 1
				GROUP BY m.id_member
				ORDER BY m.poster_time DESC", __FILE__, __LINE__);
			while ($row = $smfFunc['db_fetch_assoc']($request))
			{
				$context['member_watches'][$row['id_member']]['last_post'] = timeformat($row['last_post']);
				$context['member_watches'][$row['id_member']]['last_post_id'] = $row['last_post_id'];
			}
			$smfFunc['db_free_result']($request);
		}
	}
	else
	{
		$request = $smfFunc['db_query']('', "
			SELECT m.id_msg, m.id_topic, m.id_board, m.id_member, m.subject, m.body, m.poster_time,
				m.approved, mem.member_name
			FROM {$db_prefix}messages AS m
				INNER JOIN {$db_prefix}members AS mem ON (mem.id_member = m.id_member)
				INNER JOIN {$db_prefix}boards AS b ON (b.id_board = m.id_board)
			WHERE mem.warning >= $modSettings[warn_watch]
				AND $user_info[query_see_board]
				$approve_query
			ORDER BY m.id_msg DESC
			LIMIT $context[start], $perPage", __FILE__, __LINE__);
		$context['member_posts'] = array();
		$members = array();
		while ($row = $smfFunc['db_fetch_assoc']($request))
		{
			$row['subject'] = censorText($row['subject']);
			$row['body'] = censorText($row['body']);

			$context['member_posts'][$row['id_msg']] = array(
				'id' => $row['id_msg'],
				'id_topic' => $row['id_topic'],
				'author' => array(
					'id' => $row['id_member'],
					'name' => $row['member_name'],
					'link' => '<a href="' . $scripturl . '?action=profile;u=' . $row['id_member'] . '">' . $row['member_name'] . '</a>',
					'href' => $scripturl . '?action=profile;u=' . $row['id_member'],
				),
				'subject' => $row['subject'],
				'body' => parse_bbc($row['body']),
				'poster_time' => timeformat($row['poster_time']),
				'approved' => $row['approved'],
				'can_delete' => $delete_boards == array(0) || in_array($row['id_board'], $delete_boards),
			);
		}
		$smfFunc['db_free_result']($request);
	}
}

// Simply put, look at the warning log!
function ViewWarningLog()
{
	global $db_prefix, $smfFunc, $modSettings, $context, $txt, $scripturl;

	// Setup context as always.
	$context['page_title'] = $txt['mc_warning_log'];
	$context['sub_template'] = 'warning_log';

	loadTemplate('ModerationCenter');
	loadLanguage('Profile');

	// Fine - how many warnings have we issued?
	$request = $smfFunc['db_query']('', "
			SELECT COUNT(*)
			FROM {$db_prefix}log_comments
			WHERE comment_type = 'warning'", __FILE__, __LINE__);
	list ($context['total_warnings']) = $smfFunc['db_fetch_row']($request);
	$smfFunc['db_free_result']($request);

	// Do the page index.
	$perPage = (int) $modSettings['defaultMaxMessages'];
	$context['start'] = (int) $_REQUEST['start'];
	$context['page_index'] = constructPageIndex($scripturl . '?action=moderate;area=warnlog', $context['start'], $context['total_warnings'], $perPage);

	// Load them up, boyo.
	$request = $smfFunc['db_query']('', "
		SELECT IFNULL(mem.id_member, 0) AS id_member, IFNULL(mem.real_name, lc.member_name) AS member_name,
			IFNULL(mem2.id_member, 0) AS id_recipient, IFNULL(mem2.real_name, lc.recipient_name) AS recipient_name,
			lc.log_time, lc.body, lc.id_notice, lc.counter
		FROM {$db_prefix}log_comments AS lc
			LEFT JOIN {$db_prefix}members AS mem ON (mem.id_member = lc.id_member)
			LEFT JOIN {$db_prefix}members AS mem2 ON (mem2.id_member = lc.id_recipient)
		WHERE lc.comment_type = 'warning'
		ORDER BY log_time DESC
		LIMIT $context[start], $perPage", __FILE__, __LINE__);
	$context['warnings'] = array();
	while ($row = $smfFunc['db_fetch_assoc']($request))
	{
			$context['warnings'][] = array(
			'issuer' => array(
				'id' => $row['id_member'],
				'link' => $row['id_member'] ? ('<a href="' . $scripturl . '?action=profile;u=' . $row['id_member'] . '">' . $row['member_name'] . '</a>') : $row['member_name'],
			),
			'recipient' => array(
				'id' => $row['id_recipient'],
				'link' => $row['id_recipient'] ? ('<a href="' . $scripturl . '?action=profile;u=' . $row['id_recipient'] . '">' . $row['recipient_name'] . '</a>') : $row['recipient_name'],
			),
			'time' => timeformat($row['log_time']),
			'reason' => $row['body'],
			'counter' => $row['counter'] > 0 ? '+' . $row['counter'] : $row['counter'],
			'id_notice' => $row['id_notice'],
		);
	}
	$smfFunc['db_free_result']($request);
}

?>