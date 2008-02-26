<?php
/**********************************************************************************
* ModerationCenter.php                                                            *
***********************************************************************************
* SMF: Simple Machines Forum                                                      *
* Open-Source Project Inspired by Zef Hemel (zef@zefhemel.com)                    *
* =============================================================================== *
* Software Version:           SMF 2.0 Beta 2.1                                    *
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

/*
	//!!!
*/

// Entry point for the moderation center.
function ModerationMain($dont_call = false)
{
	global $txt, $context, $scripturl, $sc, $modSettings, $user_info, $settings, $sourcedir, $options, $smcFunc;

	// Don't run this twice... and don't conflict with the admin bar.
	if (isset($context['admin_area']))
		return;

	// Everyone using this area must be allowed here!
	if ($user_info['mod_cache']['gq'] == '0=1' && $user_info['mod_cache']['bq'] == '0=1' && !allowedTo('manage_membergroups'))
		isAllowedTo('access_mod_center');

	// We're gonna want a menu of some kind.
	require_once($sourcedir .'/Subs-Menu.php');

	// Load the language, and the template.
	loadLanguage('ModerationCenter');

	$context['admin_preferences'] = !empty($options['admin_preferences']) ? unserialize($options['admin_preferences']) : array();

	// This is the menu structure - refer to Subs-Menu.php for the details.
	$moderation_areas = array(
		'main' => array(
			'title' => $txt['mc_main'],
			'areas' => array(
				'index' => array(
					'label' => $txt['moderation_center'],
					'function' => 'ModerationHome',
				),
				'modlog' => array(
					'enabled' => !empty($modSettings['modlog_enabled']),
					'label' => $txt['modlog_view'],
					'file' => 'Modlog.php',
					'function' => 'ViewModlog',
				),
				'notice' => array(
					'file' => 'ModerationCenter.php',
					'function' => 'ShowNotice',
					'select' => 'index'
				),
				'warnlog' => array(
					'label' => $txt['mc_warning_log'],
					'enabled' => $modSettings['warning_settings']{0} == 1,
					'function' => 'ViewWarningLog',
				),
				'userwatch' => array(
					'label' => $txt['mc_watched_users_title'],
					'function' => 'ViewWatchedUsers',
					'subsections' => array(
						'member' => array($txt['mc_watched_users_member']),
						'post' => array($txt['mc_watched_users_post']),
					),
				),
			),
		),
		'posts' => array(
			'title' => $txt['mc_posts'],
			'areas' => array(
				'postmod' => array(
					'label' => $txt['mc_unapproved_posts'],
					'enabled' => in_array('pm', $context['admin_features']),
					'file' => 'PostModeration.php',
					'function' => 'PostModerationMain',
					'custom_url' => $scripturl . '?action=moderate;area=postmod;sa=posts',
					'subsections' => array(
						'posts' => array($txt['mc_unapproved_replies']),
						'topics' => array($txt['mc_unapproved_topics']),
					),
				),
				'attachmod' => array(
					'label' => $txt['mc_unapproved_attachments'],
					'enabled' => in_array('pm', $context['admin_features']),
					'file' => 'PostModeration.php',
					'function' => 'PostModerationMain',
					'custom_url' => $scripturl . '?action=moderate;area=attachmod;sa=attachments',
				),
				'reports' => array(
					'label' => $txt['mc_reported_posts'],
					'file' => 'ModerationCenter.php',
					'function' => 'ReportedPosts',
					'subsections' => array(
						'open' => array($txt['mc_reportedp_open']),
						'closed' => array($txt['mc_reportedp_closed']),
					),
				),
			),
		),
		'groups' => array(
			'title' => $txt['mc_groups'],
			'enabled' => $user_info['mod_cache']['gq'] != '0=1' || allowedTo('manage_membergroups'),
			'areas' => array(
				'groups' => array(
					'label' => $txt['mc_group_requests'],
					'file' => 'Groups.php',
					'function' => 'Groups',
					'custom_url' => $scripturl . '?action=moderate;area=groups;sa=requests',
				),
				'viewgroups' => array(
					'label' => $txt['mc_view_groups'],
					'file' => 'Groups.php',
					'function' => 'Groups',
				),
			),
		),
		'prefs' => array(
			'title' => $txt['mc_prefs'],
			'areas' => array(
				'settings' => array(
					'label' => $txt['mc_settings'],
					'function' => 'ModerationSettings',
				),
			),
		),
	);

	// I don't know where we're going - I don't know where we've been...
	$menuOptions = array(
		'action' => 'moderate',
	);
	$mod_include_data = createMenu($moderation_areas, $menuOptions);
	unset($moderation_areas);

	// Retain the ID information in case required by a subaction.
	$context['moderation_menu_id'] = $context['max_menu_id'];
	$context['moderation_menu_name'] = 'menu_data_' . $context['moderation_menu_id'];

	// We got something - didn't we? DIDN'T WE!
	if ($mod_include_data == false)
		fatal_lang_error('no_access');

	// What a pleasant shortcut - even tho we're not *really* on the admin screen who cares...
	$context['admin_area'] = $mod_include_data['current_area'];

	// Now - finally - the bit before the encore - the main performance of course!
	if (!$dont_call)
	{
		if (isset($mod_include_data['file']))
			require_once($sourcedir . '/' . $mod_include_data['file']);

		$mod_include_data['function']();
	}
}

// This function basically is the home page of the moderation center.
function ModerationHome()
{
	global $txt, $context, $scripturl, $modSettings, $user_info, $user_settings;

	loadTemplate('ModerationCenter');

	$context['page_title'] = $txt['moderation_center'];
	$context['sub_template'] = 'moderation_center';

	// Load what blocks the user actually wants...
	$valid_blocks = array(
		'n' => 'LatestNews',
		'p' => 'Notes',
		'w' => 'WatchedUsers',
		'r' => 'ReportedPosts',
		'g' => 'GroupRequests'
	);

	if (empty($user_settings['mod_prefs']))
		$user_blocks = 'nwrg';
	else
		list (, $user_blocks) = explode('|', $user_settings['mod_prefs']);

	$user_blocks = str_split($user_blocks);

	$context['mod_blocks'] = array();
	foreach ($valid_blocks as $k => $block)
	{
		if (in_array($k, $user_blocks))
		{
			$block = 'ModBlock' . $block;
			if (function_exists($block))
				$context['mod_blocks'][] = $block();
		}
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
	global $context, $smcFunc, $scripturl, $modSettings;

	if (($watched_users = cache_get_data('recent_user_watches', 240)) === null)
	{
		$modSettings['warning_watch'] = empty($modSettings['warning_watch']) ? 1 : $modSettings['warning_watch'];
		$request = $smcFunc['db_query']('', '
			SELECT id_member, real_name, last_login
			FROM {db_prefix}members
			WHERE warning >= {int:warning_watch}
			ORDER BY last_login DESC
			LIMIT 10',
			array(
				'warning_watch' => $modSettings['warning_watch'],
			)
		);
		$watched_users = array();
		while ($row = $smcFunc['db_fetch_assoc']($request))
			$watched_users[] = $row;
		$smcFunc['db_free_result']($request);

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

// Show an area for the moderator to type into.
function ModBlockNotes()
{
	global $context, $smcFunc, $scripturl, $txt, $user_info;

	// Are we saving a note?
	if (isset($_POST['makenote']) && isset($_POST['new_note']))
	{
		checkSession();

		$_POST['new_note'] = $smcFunc['htmlspecialchars'](trim($_POST['new_note']));
		// Make sure they actually entered something.
		if (!empty($_POST['new_note']) && $_POST['new_note'] !== $txt['mc_click_add_note'])
		{
			// Insert it into the database then!
			$smcFunc['db_insert']('',
				'{db_prefix}log_comments',
				array(
					'id_member' => 'int', 'member_name' => 'string', 'comment_type' => 'string', 'recipient_name' => 'string',
					'body' => 'string', 'log_time' => 'int',
				),
				array(
					$user_info['id'], $user_info['name'], 'modnote', '', $_POST['new_note'], time(),
				),
				array('id_comment')
			);

			// Clear the cache.
			cache_put_data('moderator_notes', null, 240);
		}

		// Redirect otherwise people can resubmit.
		redirectexit('action=moderate');
	}

	// Bye... bye...
	if (isset($_GET['notes']) && isset($_GET['delete']) && is_numeric($_GET['delete']))
	{
		checkSession('get');

		// Lets delete it.
		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}log_comments
			WHERE id_comment = {int:note}
				AND comment_type = {string:type}',
			array(
				'note' => $_GET['delete'],
				'type' => 'modnote',
			)
		);

		// Clear the cache.
		cache_put_data('moderator_notes', null, 240);
		cache_put_data('moderator_notes_total', null, 240);

		redirectexit('action=moderate');
	}

	// How many notes in total?
	if (($moderator_notes_total = cache_get_data('moderator_notes_total', 240)) === null)
	{
		$request = $smcFunc['db_query']('', '
			SELECT COUNT(*)
			FROM {db_prefix}log_comments AS lc
				LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = lc.id_member)
			WHERE lc.comment_type = {string:modnote}',
			array(
				'modnote' => 'modnote',
			)
		);
		list ($moderator_notes_total) = $smcFunc['db_fetch_row']($request);
		$smcFunc['db_free_result']($request);

		cache_put_data('moderator_notes_total', $moderator_notes_total, 240);
	}

	// Grab the current notes.
	if (($moderator_notes = cache_get_data('moderator_notes', 240)) === null)
	{
		$request = $smcFunc['db_query']('', '
			SELECT IFNULL(mem.id_member, 0) AS id_member, IFNULL(mem.real_name, lc.member_name) AS member_name,
				lc.log_time, lc.body, lc.id_comment AS id_note
			FROM {db_prefix}log_comments AS lc
				LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = lc.id_member)
			WHERE lc.comment_type = {string:modnote}
			ORDER BY id_comment DESC
			LIMIT {int:offset}, 10',
			array(
				'modnote' => 'modnote',
				'offset' => isset($_GET['notes']) && isset($_GET['start']) ? $_GET['start'] : 0,
			)
		);
		$moderator_notes = array();
		while ($row = $smcFunc['db_fetch_assoc']($request))
			$moderator_notes[] = $row;
		$smcFunc['db_free_result']($request);

		cache_put_data('moderator_notes', $moderator_notes, 240);
	}

	// Lets construct a page index.
	$context['page_index'] = constructPageIndex($scripturl . '?action=moderate;area=index;notes', $_GET['start'], $moderator_notes_total, 10);
	$context['start'] = $_GET['start'];

	$context['notes'] = array();
	foreach ($moderator_notes as $note)
	{
		$context['notes'][] = array(
			'author' => array(
				'id' => $note['id_member'],
				'link' => $note['id_member'] ? ('<a href="' . $scripturl . '?action=profile;u=' . $note['id_member'] . '" title="' . $txt['on'] . ' ' . timeformat($note['log_time']) . '">' . $note['member_name'] . '</a>') : $note['member_name'],
			),
			'time' => timeformat($note['log_time']),
			'text' => parse_bbc($note['body']),
			'delete_href' => $scripturl . '?action=moderate;area=index;notes;delete=' . $note['id_note'] . ';sesc=' . $context['session_id'],
		);
	}

	return 'notes';
}

// Show a list of the most recent reported posts.
function ModBlockReportedPosts()
{
	global $context, $user_info, $scripturl, $smcFunc;

	// Got the info already?
	$cachekey = md5(serialize($user_info['mod_cache']['bq']));
	$context['reported_posts'] = array();
	if ($user_info['mod_cache']['bq'] == '0=1')
		return 'reported_posts_block';

	if (($reported_posts = cache_get_data('reported_posts_' . $cachekey, 90)) === null)
	{
		// By George, that means we in a position to get the reports, jolly good.
		$request = $smcFunc['db_query']('', '
			SELECT lr.id_report, lr.id_msg, lr.id_topic, lr.id_board, lr.id_member, lr.subject,
				lr.num_reports, IFNULL(mem.real_name, lr.membername) AS author_name,
				IFNULL(mem.id_member, 0) AS id_author
			FROM {db_prefix}log_reported AS lr
				LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = lr.id_member)
			WHERE ' . ($user_info['mod_cache']['bq'] == '1=1' || $user_info['mod_cache']['bq'] == '0=1' ? $user_info['mod_cache']['bq'] : 'lr.' . $user_info['mod_cache']['bq']) . '
				AND lr.closed = {int:not_closed}
				AND lr.ignore_all = {int:not_ignored}
			ORDER BY lr.time_updated DESC
			LIMIT 10',
			array(
				'not_closed' => 0,
				'not_ignored' => 0,
			)
		);
		$reported_posts = array();
		while ($row = $smcFunc['db_fetch_assoc']($request))
			$reported_posts[] = $row;
		$smcFunc['db_free_result']($request);

		// Cache it.
		cache_put_data('reported_posts_' . $cachekey, $reported_posts, 90);
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
	global $context, $user_info, $scripturl, $smcFunc;

	$context['group_requests'] = array();
	// Make sure they can even moderate someone!
	if ($user_info['mod_cache']['gq'] == '0=1')
		return 'group_requests_block';

	// What requests are outstanding?
	$request = $smcFunc['db_query']('', '
		SELECT lgr.id_request, lgr.id_member, lgr.id_group, lgr.time_applied, mem.member_name, mg.group_name
		FROM {db_prefix}log_group_requests AS lgr
			INNER JOIN {db_prefix}members AS mem ON (mem.id_member = lgr.id_member)
			INNER JOIN {db_prefix}membergroups AS mg ON (mg.id_group = lgr.id_group)
		WHERE ' . ($user_info['mod_cache']['gq'] == '1=1' || $user_info['mod_cache']['gq'] == '0=1' ? $user_info['mod_cache']['gq'] : 'lgr.' . $user_info['mod_cache']['gq']) . '
		ORDER BY lgr.id_request DESC
		LIMIT 10',
		array(
		)
	);
	while ($row = $smcFunc['db_fetch_assoc']($request))
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
	$smcFunc['db_free_result']($request);

	return 'group_requests_block';
}

//!!! This needs to be given it's own file ;)
// Browse all the reported posts...
function ReportedPosts()
{
	global $txt, $context, $scripturl, $modSettings, $user_info, $smcFunc;

	// This comes under the umbrella of moderating posts.
	if ($user_info['mod_cache']['bq'] == '0=1')
		isAllowedTo('moderate_forum');

	// First load the template.
	loadTemplate('ModerationCenter');

	// Put the open and closed options into tabs, because we can...
	$context[$context['moderation_menu_name']]['tab_data'] = array(
		'title' => $txt['mc_reported_posts'],
		'help' => '',
		'description' => $txt['mc_reported_posts_desc'],
	);

	// Are they wanting to view a particular report?
	if (!empty($_REQUEST['report']))
		return ModReport();

	// Set up the comforting bits...
	$context['page_title'] = $txt['mc_reported_posts'];
	$context['sub_template'] = 'reported_posts';

	// Are we viewing open or closed reports?
	$context['view_closed'] = isset($_GET['sa']) && $_GET['sa'] == 'closed' ? 1 : 0;

	// Are we doing any work?
	if ((isset($_GET['ignore']) || isset($_GET['close'])) && isset($_GET['rid']))
	{
		checkSession('get');
		$_GET['rid'] = (int) $_GET['rid'];

		// Update the report...
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}log_reported
			SET ' . (isset($_GET['ignore']) ? 'ignore_all = {int:ignore_all}' : 'closed = {int:closed}') . '
			WHERE id_report = {int:id_report}
				AND ' . $user_info['mod_cache']['bq'],
			array(
				'ignore_all' => isset($_GET['ignore']) ? (int) $_GET['ignore'] : 0,
				'closed' => isset($_GET['close']) ? (int) $_GET['close'] : 0,
				'id_report' => $_GET['rid'],
			)
		);

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
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}log_reported
				SET closed = {int:is_closed}
				WHERE id_report IN ({array_int:report_list})
					AND ' . $user_info['mod_cache']['bq'],
				array(
					'report_list' => $toClose,
					'is_closed' => 1,
				)
			);

			// Time to update.
			updateSettings(array('last_mod_report_action' => time()));
			recountOpenReports();
		}
	}

	// How many entries are we viewing?
	$request = $smcFunc['db_query']('', '
		SELECT COUNT(*)
		FROM {db_prefix}log_reported AS lr
		WHERE lr.closed = {int:view_closed}
			AND ' . ($user_info['mod_cache']['bq'] == '1=1' || $user_info['mod_cache']['bq'] == '0=1' ? $user_info['mod_cache']['bq'] : 'lr.' . $user_info['mod_cache']['bq']),
		array(
			'view_closed' => $context['view_closed'],
		)
	);
	list ($context['total_reports']) = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);

	// So, that means we can page index, yes?
	$context['page_index'] = constructPageIndex($scripturl . '?action=moderate;area=reports' . ($context['view_closed'] ? ';sa=closed' : ''), $_GET['start'], $context['total_reports'], 10);
	$context['start'] = $_GET['start'];

	// By George, that means we in a position to get the reports, golly good.
	$request = $smcFunc['db_query']('', '
		SELECT lr.id_report, lr.id_msg, lr.id_topic, lr.id_board, lr.id_member, lr.subject, lr.body,
			lr.time_started, lr.time_updated, lr.num_reports, lr.closed, lr.ignore_all,
			IFNULL(mem.real_name, lr.membername) AS author_name, IFNULL(mem.id_member, 0) AS id_author
		FROM {db_prefix}log_reported AS lr
			LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = lr.id_member)
		WHERE lr.closed = {int:view_closed}
			AND ' . ($user_info['mod_cache']['bq'] == '1=1' || $user_info['mod_cache']['bq'] == '0=1' ? $user_info['mod_cache']['bq'] : 'lr.' . $user_info['mod_cache']['bq']) . '
		ORDER BY lr.time_updated DESC
		LIMIT ' . $context['start'] . ', 10',
		array(
			'view_closed' => $context['view_closed'],
		)
	);
	$context['reports'] = array();
	$report_ids = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$report_ids[] = $row['id_report'];
		$context['reports'][$row['id_report']] = array(
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
			'time_started' => timeformat($row['time_started']),
			'last_updated' => timeformat($row['time_updated']),
			'subject' => $row['subject'],
			'body' => parse_bbc($row['body']),
			'num_reports' => $row['num_reports'],
			'closed' => $row['closed'],
			'ignore' => $row['ignore_all']
		);
	}
	$smcFunc['db_free_result']($request);

	// Now get all the people who reported it.
	if (!empty($report_ids))
	{
		$request = $smcFunc['db_query']('', '
			SELECT lrc.id_comment, lrc.id_report, lrc.time_sent, lrc.comment,
				IFNULL(mem.id_member, 0) AS id_member, IFNULL(mem.real_name, lrc.membername) AS reporter
			FROM {db_prefix}log_reported_comments AS lrc
				LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = lrc.id_member)
			WHERE lrc.id_report IN ({array_int:report_list})',
			array(
				'report_list' => $report_ids,
			)
		);
		while ($row = $smcFunc['db_fetch_assoc']($request))
		{
			$context['reports'][$row['id_report']]['comments'][] = array(
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
		$smcFunc['db_free_result']($request);
	}
}

// Act as an entrace for all group related activity.
//!!! As for most things in this file, this needs to be moved somewhere appropriate.
function ModerateGroups()
{
	global $txt, $context, $scripturl, $modSettings, $user_info;

	// You need to be allowed to moderate groups...
	if ($user_info['mod_cache']['gq'] == '0=1')
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
	global $user_info, $context, $smcFunc;

	$request = $smcFunc['db_query']('', '
		SELECT COUNT(*)
		FROM {db_prefix}log_reported
		WHERE ' . $user_info['mod_cache']['bq'] . '
			AND closed = {int:not_closed}
			AND ignore_all = {int:not_ignored}',
		array(
			'not_closed' => 0,
			'not_ignored' => 0,
		)
	);
	list ($open_reports) = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);

	$_SESSION['rc'] = array(
		'id' => $user_info['id'],
		'time' => time(),
		'reports' => $open_reports,
	);

	$context['open_mod_reports'] = $open_reports;
}

function ModReport()
{
	global $user_info, $context, $sourcedir, $scripturl, $txt, $smcFunc;

	// Have to at least give us something
	if (empty($_REQUEST['report']))
		fatal_lang_error('mc_no_modreport_specified');

	// Integers only please
	$_REQUEST['report'] = (int) $_REQUEST['report'];

	// Get the report details, need this so we can limit access to a particular board
	$request = $smcFunc['db_query']('', '
		SELECT lr.id_report, lr.id_msg, lr.id_topic, lr.id_board, lr.id_member, lr.subject, lr.body,
			lr.time_started, lr.time_updated, lr.num_reports, lr.closed, lr.ignore_all,
			IFNULL(mem.real_name, lr.membername) AS author_name, IFNULL(mem.id_member, 0) AS id_author
		FROM {db_prefix}log_reported AS lr
			LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = lr.id_member)
		WHERE lr.id_report = {int:id_report}
			AND ' . ($user_info['mod_cache']['bq'] == '1=1' || $user_info['mod_cache']['bq'] == '0=1' ? $user_info['mod_cache']['bq'] : 'lr.' . $user_info['mod_cache']['bq']) . '
		LIMIT 1',
		array(
			'id_report' => $_REQUEST['report'],
		)
	);

	// So did we find anything?
	if (!$smcFunc['db_num_rows']($request))
		fatal_lang_error('mc_no_modreport_found');

	// Woohoo we found a report and they can see it!  Bad news is we have more work to do
	$row = $smcFunc['db_fetch_assoc']($request);
	$smcFunc['db_free_result']($request);

	// If they are adding a comment then... add a comment.
	if (isset($_POST['add_comment']) && !empty($_POST['mod_comment']))
	{
		$newComment = trim($smcFunc['htmlspecialchars']($_POST['mod_comment']));

		// In it goes.
		if (!empty($newComment))
			$smcFunc['db_insert']('',
				'{db_prefix}log_comments',
				array(
					'id_member' => 'int', 'member_name' => 'string', 'comment_type' => 'string', 'recipient_name' => 'string',
					'id_notice' => 'int', 'body' => 'string', 'log_time' => 'int',
				),
				array(
					$user_info['id'], $user_info['name'], 'reportc', '',
					$_REQUEST['report'], $newComment, time(),
				),
				array('id_comment')
			);
	}

	$context['report'] = array(
		'id' => $row['id_report'],
		'topic_id' => $row['id_topic'],
		'board_id' => $row['id_board'],
		'message_id' => $row['id_msg'],
		'message_href' => $scripturl . '?msg=' . $row['id_msg'],
		'message_link' => '<a href="' . $scripturl . '?msg=' . $row['id_msg'] . '">' . $row['subject'] . '</a>',
		'report_href' => $scripturl . '?action=moderate;area=reports;report=' . $row['id_report'],
		'author' => array(
			'id' => $row['id_author'],
			'name' => $row['author_name'],
			'link' => $row['id_author'] ? '<a href="' . $scripturl . '?action=profile;u=' . $row['id_author'] . '">' . $row['author_name'] . '</a>' : $row['author_name'],
			'href' => $scripturl . '?action=profile;u=' . $row['id_author'],
		),
		'comments' => array(),
		'mod_comments' => array(),
		'time_started' => timeformat($row['time_started']),
		'last_updated' => timeformat($row['time_updated']),
		'subject' => $row['subject'],
		'body' => parse_bbc($row['body']),
		'num_reports' => $row['num_reports'],
		'closed' => $row['closed'],
		'ignore' => $row['ignore_all']
	);

	// So what bad things do the reporters have to say about it?
	$request = $smcFunc['db_query']('', '
		SELECT lrc.id_comment, lrc.id_report, lrc.time_sent, lrc.comment,
			IFNULL(mem.id_member, 0) AS id_member, IFNULL(mem.real_name, lrc.membername) AS reporter
		FROM {db_prefix}log_reported_comments AS lrc
			LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = lrc.id_member)
		WHERE lrc.id_report = {int:id_report}',
		array(
			'id_report' => $context['report']['id'],
		)
	);
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$context['report']['comments'][] = array(
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
	$smcFunc['db_free_result']($request);

	// Hang about old chap, any comments from moderators on this one?
	$request = $smcFunc['db_query']('', '
		SELECT lc.id_comment, lc.id_notice, lc.log_time, lc.body,
			IFNULL(mem.id_member, 0) AS id_member, IFNULL(mem.real_name, lc.member_name) AS moderator
		FROM {db_prefix}log_comments AS lc
			LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = lc.id_member)
		WHERE lc.id_notice = {int:id_report}
			AND lc.comment_type = {string:reportc}',
		array(
			'id_report' => $context['report']['id'],
			'reportc' => 'reportc',
		)
	);
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$context['report']['mod_comments'][] = array(
			'id' => $row['id_comment'],
			'message' => parse_bbc($row['body']),
			'time' => timeformat($row['log_time']),
			'member' => array(
				'id' => $row['id_member'],
				'name' => $row['moderator'],
				'link' => $row['id_member'] ? '<a href="' . $scripturl . '?action=profile;u=' . $row['id_member'] . '">' . $row['moderator'] . '</a>' : $row['moderator'],
				'href' => $scripturl . '?action=profile;u=' . $row['id_member'],
			),
		);
	}
	$smcFunc['db_free_result']($request);

	// What have the other moderators done to this message?
	require_once($sourcedir . '/Modlog.php');
	require_once($sourcedir . '/Subs-List.php');
	loadLanguage('Modlog');

	// This is all the information required for a watched user listing.
	$listOptions = array(
		'id' => 'moderation_actions_list',
		'title' => $txt['mc_modreport_modactions'],
		'items_per_page' => 15,
		'no_items_label' => $txt['modlog_no_entries_found'],
		'base_href' => $scripturl . '?action=moderate;area=reports;id=' . $context['report']['id'],
		'default_sort_col' => 'time',
		'get_items' => array(
			'function' => 'list_getModLogEntries',
			'params' => array(
				'lm.id_msg = {int:id_msg}',
				array('id_msg' => $context['report']['message_id']),
				1,
			),
		),
		'get_count' => array(
			'function' => 'list_getModLogEntryCount',
			'params' => array(
				'lm.id_msg = {int:id_msg}',
				array('id_msg' => $context['report']['message_id']),
				1,
			),
		),
		// This assumes we are viewing by user.
		'columns' => array(
			'action' => array(
				'header' => array(
					'value' => $txt['modlog_action'],
				),
				'data' => array(
					'db' => 'action_text',
					'class' => 'smalltext',
				),
				'sort' => array(
					'default' => 'lm.action',
					'reverse' => 'lm.action DESC',
				),
			),
			'time' => array(
				'header' => array(
					'value' => $txt['modlog_date'],
				),
				'data' => array(
					'db' => 'time',
					'class' => 'smalltext',
				),
				'sort' => array(
					'default' => 'lm.log_time',
					'reverse' => 'lm.log_time DESC',
				),
			),
			'moderator' => array(
				'header' => array(
					'value' => $txt['modlog_member'],
				),
				'data' => array(
					'db' => 'moderator_link',
					'class' => 'smalltext',
				),
				'sort' => array(
					'default' => 'mem.real_name',
					'reverse' => 'mem.real_name DESC',
				),
			),
			'position' => array(
				'header' => array(
					'value' => $txt['modlog_position'],
				),
				'data' => array(
					'db' => 'position',
					'class' => 'smalltext',
				),
				'sort' => array(
					'default' => 'mg.group_name',
					'reverse' => 'mg.group_name DESC',
				),
			),
			'ip' => array(
				'header' => array(
					'value' => $txt['modlog_ip'],
				),
				'data' => array(
					'db' => 'ip',
					'class' => 'smalltext',
				),
				'sort' => array(
					'default' => 'lm.ip',
					'reverse' => 'lm.ip DESC',
				),
			),
		),
	);

	// Create the watched user list.
	createList($listOptions);

	// Finally we are done :P
	loadTemplate('ModerationCenter');
	$context['page_title'] = sprintf($txt['mc_viewmodreport'], $context['report']['subject'], $context['report']['author']['name']);
	$context['sub_template'] = 'viewmodreport';

}

// Show a notice sent to a user.
function ShowNotice()
{
	global $smcFunc, $txt, $context;

	$context['page_title'] = $txt['show_notice'];
	$context['sub_template'] = 'show_notice';
	$context['template_layers'] = array();

	loadTemplate('ModerationCenter');

	//!!! Assumes nothing needs permission more than accessing moderation center!
	$id_notice = (int) $_GET['nid'];
	$request = $smcFunc['db_query']('', '
		SELECT body, subject
		FROM {db_prefix}log_member_notices
		WHERE id_notice = {int:id_notice}
			AND comment_type = {string:warning}',
		array(
			'id_notice' => $id_notice,
			'warning' => 'warning',
		)
	);
	if ($smcFunc['db_num_rows']($request) == 0)
		fatal_lang_error('no_access');
	list ($context['notice_body'], $context['notice_subject']) = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);

	$context['notice_body'] = strtr($context['notice_body'], array("\n" => '<br />'));
}

// View watched users.
function ViewWatchedUsers()
{
	global $smcFunc, $modSettings, $context, $txt, $scripturl, $user_info, $sourcedir;

	// Some important context!
	$context['page_title'] = $txt['mc_watched_users_title'];
	$context['view_posts'] = isset($_GET['sa']) && $_GET['sa'] == 'post';
	$context['start'] = isset($_REQUEST['start']) ? (int) $_REQUEST['start'] : 0;

	loadTemplate('ModerationCenter');

	// Get some key settings!
	$modSettings['warning_watch'] = empty($modSettings['warning_watch']) ? 1 : $modSettings['warning_watch'];

	// Put some pretty tabs on cause we're gonna be doing hot stuff here...
	$context[$context['moderation_menu_name']]['tab_data'] = array(
		'title' => $txt['mc_watched_users_title'],
		'help' => '',
		'description' => $txt['mc_watched_users_desc'],
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

	// Start preparing the list by grabbing relevant permissions.
	if (!$context['view_posts'])
	{
		$approve_query = '';
		$delete_boards = array();
	}
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
	}

	require_once($sourcedir . '/Subs-List.php');

	// This is all the information required for a watched user listing.
	$listOptions = array(
		'id' => 'watch_user_list',
		'title' => $txt['mc_watched_users_title'] . ' - ' . $txt['mc_watched_users_member'],
		'width' => '85%',
		'items_per_page' => $modSettings['defaultMaxMessages'],
		'no_items_label' => $context['view_posts'] ? $txt['mc_watched_users_no_posts'] : $txt['mc_watched_users_none'],
		'base_href' => $scripturl . '?action=moderate;area=userwatch;sa=' . ($context['view_posts'] ? 'post' : 'member'),
		'default_sort_col' => $context['view_posts'] ? '' : 'member',
		'get_items' => array(
			'function' => $context['view_posts'] ? 'list_getWatchedUserPosts' : 'list_getWatchedUsers',
			'params' => array(
				$approve_query,
				$delete_boards,
			),
		),
		'get_count' => array(
			'function' => $context['view_posts'] ? 'list_getWatchedUserPostsCount' : 'list_getWatchedUserCount',
			'params' => array(
				$approve_query,
			),
		),
		// This assumes we are viewing by user.
		'columns' => array(
			'member' => array(
				'header' => array(
					'value' => $txt['name'],
				),
				'data' => array(
					'sprintf' => array(
						'format' => '<a href="' . $scripturl . '?action=profile;u=%1$d">%2$s</a>',
						'params' => array(
							'id' => false,
							'name' => false,
						),
					),
				),
				'sort' => array(
					'default' => 'member_name',
					'reverse' => 'member_name DESC',
				),
			),
			'warning' => array(
				'header' => array(
					'value' => $txt['mc_watched_users_warning'],
				),
				'data' => array(
					'function' => create_function('$member', '
						global $scripturl;

						return allowedTo(\'issue_warning\') ? \'<a href="\' . $scripturl . \'?action=profile;u=\' . $member[\'id\'] . \';sa=issueWarning">\' . $member[\'warning\'] . \'%</a>\' : $member[\'warning\'] . \'%\';
					'),
				),
				'sort' => array(
					'default' => 'warning',
					'reverse' => 'warning DESC',
				),
			),
			'posts' => array(
				'header' => array(
					'value' => $txt['posts'],
				),
				'data' => array(
					'sprintf' => array(
						'format' => '<a href="' . $scripturl . '?action=profile;u=%1$d;sa=showPosts">%2$s</a>',
						'params' => array(
							'id' => false,
							'posts' => false,
						),
					),
				),
				'sort' => array(
					'default' => 'posts',
					'reverse' => 'posts DESC',
				),
			),
			'last_login' => array(
				'header' => array(
					'value' => $txt['mc_watched_users_last_login'],
				),
				'data' => array(
					'db' => 'last_login',
				),
				'sort' => array(
					'default' => 'last_login',
					'reverse' => 'last_login DESC',
				),
			),
			'last_post' => array(
				'header' => array(
					'value' => $txt['mc_watched_users_last_post'],
				),
				'data' => array(
					'function' => create_function('$member', '
						global $scripturl;

						if ($member[\'last_post_id\'])
							return \'<a href="\' . $scripturl . \'?msg=\' . $member[\'last_post_id\'] . \'">\' . $member[\'last_post\'] . \'</a>\';
						else
							return $member[\'last_post\'];
					'),
				),
			),
		),
		'form' => array(
			'href' => $scripturl . '?action=moderate;area=userwatch;sa=post',
			'include_sort' => true,
			'include_start' => true,
			'hidden_fields' => array(
				'sc' => $context['session_id'],
			),
		),
		'additional_rows' => array(
			$context['view_posts'] ?
			array(
				'position' => 'bottom_of_list',
				'value' => '
					<input type="submit" name="delete_selected" value="' . $txt['quickmod_delete_selected'] . '" />',
				'class' => 'windowbg',
				'align' => 'right',
			) : array(),
		),
	);

	// If this is being viewed by posts we actually change the columns to call a template each time.
	if ($context['view_posts'])
	{
		$listOptions['columns'] = array(
			'posts' => array(
				'data' => array(
					'function' => create_function('$post', '
						return template_user_watch_post_callback($post);
					'),
				),
			),
		);
	}

	// Create the watched user list.
	createList($listOptions);

	$context['sub_template'] = 'show_list';
	$context['default_list'] = 'watch_user_list';
}

function list_getWatchedUserCount($approve_query)
{
	global $smcFunc, $modSettings;

	$request = $smcFunc['db_query']('', '
		SELECT COUNT(*)
		FROM {db_prefix}members
		WHERE warning >= {int:warning_watch}',
		array(
			'warning_watch' => $modSettings['warning_watch'],
		)
	);
	list ($totalMembers) = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);

	return $totalMembers;
}

function list_getWatchedUsers($start, $items_per_page, $sort, $approve_query, $dummy)
{
	global $smcFunc, $txt, $scripturl, $modSettings, $user_info;

	$request = $smcFunc['db_query']('', '
		SELECT id_member, member_name, last_login, posts, warning
		FROM {db_prefix}members
		WHERE warning >= {int:warning_watch}
		ORDER BY {raw:sort}
		LIMIT ' . $start . ', ' . $items_per_page,
		array(
			'warning_watch' => $modSettings['warning_watch'],
			'sort' => $sort,
		)
	);
	$watched_users = array();
	$members = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$watched_users[$row['id_member']] = array(
			'id' => $row['id_member'],
			'name' => $row['member_name'],
			'last_login' => $row['last_login'] ? timeformat($row['last_login']) : $txt['never'],
			'last_post' => $txt['not_applicable'],
			'last_post_id' => 0,
			'warning' => $row['warning'],
			'posts' => $row['posts'],
		);
		$members[] = $row['id_member'];
	}
	$smcFunc['db_free_result']($request);

	if (!empty($members))
	{
		$request = $smcFunc['db_query']('', '
			SELECT MAX(m.poster_time) AS last_post, MAX(m.id_msg) AS last_post_id, m.id_member
			FROM {db_prefix}messages AS m
				INNER JOIN {db_prefix}boards AS b ON (b.id_board = m.id_board)
			WHERE m.id_member IN ({array_int:member_list})
				AND {query_see_board}
				AND m.approved = {int:is_approved}
			GROUP BY m.id_member
			ORDER BY m.poster_time DESC',
			array(
				'member_list' => $members,
				'is_approved' => 1,
			)
		);
		while ($row = $smcFunc['db_fetch_assoc']($request))
		{
			$watched_users[$row['id_member']]['last_post'] = timeformat($row['last_post']);
			$watched_users[$row['id_member']]['last_post_id'] = $row['last_post_id'];
		}
		$smcFunc['db_free_result']($request);
	}

	return $watched_users;
}

function list_getWatchedUserPostsCount($approve_query)
{
	global $smcFunc, $modSettings, $user_info;

	$request = $smcFunc['db_query']('', '
		SELECT COUNT(*)
			FROM {db_prefix}messages AS m
				INNER JOIN {db_prefix}members AS mem ON (mem.id_member = m.id_member)
				INNER JOIN {db_prefix}boards AS b ON (b.id_board = m.id_board)
			WHERE mem.warning >= {int:warning_watch}
				AND {query_see_board}
				' . $approve_query,
		array(
			'warning_watch' => $modSettings['warning_watch'],
		)
	);
	list ($totalMemberPosts) = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);

	return $totalMemberPosts;
}

function list_getWatchedUserPosts($start, $items_per_page, $sort, $approve_query, $delete_boards)
{
	global $smcFunc, $txt, $scripturl, $modSettings, $user_info;

	$request = $smcFunc['db_query']('', '
		SELECT m.id_msg, m.id_topic, m.id_board, m.id_member, m.subject, m.body, m.poster_time,
			m.approved, mem.member_name
		FROM {db_prefix}messages AS m
			INNER JOIN {db_prefix}members AS mem ON (mem.id_member = m.id_member)
			INNER JOIN {db_prefix}boards AS b ON (b.id_board = m.id_board)
		WHERE mem.warning >= {int:warning_watch}
			AND {query_see_board}
			' . $approve_query . '
		ORDER BY m.id_msg DESC
		LIMIT ' . $start . ', ' . $items_per_page,
		array(
			'warning_watch' => $modSettings['warning_watch'],
		)
	);
	$member_posts = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$row['subject'] = censorText($row['subject']);
		$row['body'] = censorText($row['body']);

		$member_posts[$row['id_msg']] = array(
			'id' => $row['id_msg'],
			'id_topic' => $row['id_topic'],
			'author_link' => '<a href="' . $scripturl . '?action=profile;u=' . $row['id_member'] . '">' . $row['member_name'] . '</a>',
			'subject' => $row['subject'],
			'body' => parse_bbc($row['body']),
			'poster_time' => timeformat($row['poster_time']),
			'approved' => $row['approved'],
			'can_delete' => $delete_boards == array(0) || in_array($row['id_board'], $delete_boards),
		);
	}
	$smcFunc['db_free_result']($request);

	return $member_posts;
}

// Simply put, look at the warning log!
function ViewWarningLog()
{
	global $smcFunc, $modSettings, $context, $txt, $scripturl, $sourcedir;

	// Setup context as always.
	$context['page_title'] = $txt['mc_warning_log'];
	$context['sub_template'] = 'warning_log';

	loadTemplate('ModerationCenter');
	loadLanguage('Profile');

	// Fine - how many warnings have we issued?
	$request = $smcFunc['db_query']('', '
			SELECT COUNT(*)
			FROM {db_prefix}log_comments
			WHERE comment_type = {string:warning}',
			array(
				'warning' => 'warning',
			)
		);
	list ($context['total_warnings']) = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);

	// Do the page index.
	$perPage = (int) $modSettings['defaultMaxMessages'];
	$context['start'] = (int) $_REQUEST['start'];
	$context['page_index'] = constructPageIndex($scripturl . '?action=moderate;area=warnlog', $context['start'], $context['total_warnings'], $perPage);

	// Load them up, boyo.
	$request = $smcFunc['db_query']('', '
		SELECT IFNULL(mem.id_member, 0) AS id_member, IFNULL(mem.real_name, lc.member_name) AS member_name,
			IFNULL(mem2.id_member, 0) AS id_recipient, IFNULL(mem2.real_name, lc.recipient_name) AS recipient_name,
			lc.log_time, lc.body, lc.id_notice, lc.counter
		FROM {db_prefix}log_comments AS lc
			LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = lc.id_member)
			LEFT JOIN {db_prefix}members AS mem2 ON (mem2.id_member = lc.id_recipient)
		WHERE lc.comment_type = {string:warning}
		ORDER BY lc.log_time DESC
		LIMIT ' . $context['start'] . ', ' . $perPage,
		array(
			'warning' => 'warning',
		)
	);
	$context['warnings'] = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
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
	$smcFunc['db_free_result']($request);

	require_once($sourcedir . '/Subs-List.php');

	// This is all the information required for a watched user listing.
	$listOptions = array(
		'id' => 'warning_list',
		'title' => $txt['mc_warning_log'],
		'items_per_page' => $modSettings['defaultMaxMessages'],
		'no_items_label' => $txt['mc_warnings_none'],
		'base_href' => $scripturl . '?action=moderate;area=warnlog;sesc=' . $context['session_id'],
		'default_sort_col' => 'time',
		'get_items' => array(
			'function' => 'list_getWarnings',
		),
		'get_count' => array(
			'function' => 'list_getWarningCount',
		),
		// This assumes we are viewing by user.
		'columns' => array(
			'issuer' => array(
				'header' => array(
					'value' => $txt['profile_warning_previous_issued'],
				),
				'data' => array(
					'db' => 'issuer_link',
				),
				'sort' => array(
					'default' => 'member_name_col',
					'reverse' => 'member_name_col DESC',
				),
			),
			'recipient' => array(
				'header' => array(
					'value' => $txt['mc_warnings_recipient'],
				),
				'data' => array(
					'db' => 'recipient_link',
				),
				'sort' => array(
					'default' => 'recipient_name',
					'reverse' => 'recipient_name DESC',
				),
			),
			'time' => array(
				'header' => array(
					'value' => $txt['profile_warning_previous_time'],
				),
				'data' => array(
					'db' => 'time',
				),
				'sort' => array(
					'default' => 'lc.log_time DESC',
					'reverse' => 'lc.log_time',
				),
			),
			'reason' => array(
				'header' => array(
					'value' => $txt['profile_warning_previous_reason'],
				),
				'data' => array(
					'function' => create_function('$warning', '
						global $scripturl, $settings, $txt;

						$output = \'
							<div style="float: left;">
								\' . $warning[\'reason\'] . \'
							</div>\';

						if (!empty($warning[\'id_notice\']))
							$output .= \'
							<div style="float: right;">
								<a href="\' . $scripturl . \'?action=moderate;area=notice;nid=\' . $warning[\'id_notice\'] . \'" onclick="window.open(this.href, \\\'\\\', \\\'scrollbars=yes,resizable=yes,width=400,height=250\\\');return false;" target="_blank" class="new_win" title="\' . $txt[\'profile_warning_previous_notice\'] . \'"><img src="\' . $settings[\'default_images_url\'] . \'/filter.gif" alt="\' . $txt[\'profile_warning_previous_notice\'] . \'" /></a>
							</div>\';

						return $output;
					'),
				),
			),
			'points' => array(
				'header' => array(
					'value' => $txt['profile_warning_previous_level'],
				),
				'data' => array(
					'db' => 'counter',
				),
			),
		),
	);

	// Create the watched user list.
	createList($listOptions);

	$context['sub_template'] = 'show_list';
	$context['default_list'] = 'warning_list';
}

function list_getWarningCount()
{
	global $smcFunc, $modSettings;

	$request = $smcFunc['db_query']('', '
		SELECT COUNT(*)
		FROM {db_prefix}log_comments
		WHERE comment_type = {string:warning}',
		array(
			'warning' => 'warning',
		)
	);
	list ($totalWarns) = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);

	return $totalWarns;
}

function list_getWarnings($start, $items_per_page, $sort)
{
	global $smcFunc, $txt, $scripturl, $modSettings, $user_info;

	$request = $smcFunc['db_query']('', '
		SELECT IFNULL(mem.id_member, 0) AS id_member, IFNULL(mem.real_name, lc.member_name) AS member_name_col,
			IFNULL(mem2.id_member, 0) AS id_recipient, IFNULL(mem2.real_name, lc.recipient_name) AS recipient_name,
			lc.log_time, lc.body, lc.id_notice, lc.counter
		FROM {db_prefix}log_comments AS lc
			LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = lc.id_member)
			LEFT JOIN {db_prefix}members AS mem2 ON (mem2.id_member = lc.id_recipient)
		WHERE lc.comment_type = {string:warning}
		ORDER BY ' . $sort . '
		LIMIT ' . $start . ', ' . $items_per_page,
		array(
			'warning' => 'warning',
		)
	);
	$warnings = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$warnings[] = array(
			'issuer_link' => $row['id_member'] ? ('<a href="' . $scripturl . '?action=profile;u=' . $row['id_member'] . '">' . $row['member_name_col'] . '</a>') : $row['member_name_col'],
			'recipient_link' => $row['id_recipient'] ? ('<a href="' . $scripturl . '?action=profile;u=' . $row['id_recipient'] . '">' . $row['recipient_name'] . '</a>') : $row['recipient_name'],
			'time' => timeformat($row['log_time']),
			'reason' => $row['body'],
			'counter' => $row['counter'] > 0 ? '+' . $row['counter'] : $row['counter'],
			'id_notice' => $row['id_notice'],
		);
	}
	$smcFunc['db_free_result']($request);

	return $warnings;
}

// Change moderation preferences.
function ModerationSettings()
{
	global $context, $smcFunc, $txt, $sourcedir, $scripturl, $user_settings, $user_info;

	// Some useful context stuff.
	loadTemplate('ModerationCenter');
	$context['page_title'] = $txt['mc_settings'];
	$context['sub_template'] = 'moderation_settings';

	// They can only change some settings if they can moderate boards/groups.
	$context['can_moderate_boards'] = $user_info['mod_cache']['bq'] != '0=1';
	$context['can_moderate_groups'] = $user_info['mod_cache']['gq'] != '0=1';

	// What blocks can this user see?
	$context['homepage_blocks'] = array(
		'n' => $txt['mc_prefs_latest_news'],
		'p' => $txt['mc_notes'],
		'w' => $txt['mc_watched_users'],
	);
	if ($context['can_moderate_groups'])
		$context['homepage_blocks']['g'] = $txt['mc_group_requests'];
	if ($context['can_moderate_boards'])
		$context['homepage_blocks']['r'] = $txt['mc_reported_posts'];

	// Does the user have any settings yet?
	if (empty($user_settings['mod_prefs']))
	{
		$mod_blocks = 'nwrg';
		$pref_binary = 5;
		$show_reports = 1;
	}
	else
	{
		list ($show_reports, $mod_blocks, $pref_binary) = explode('|', $user_settings['mod_prefs']);
	}

	// Are we saving?
	if (isset($_POST['save']))
	{
		/* Current format of mod_prefs is:
			x|ABCD|yyy

			WHERE:
				x = Show report count on forum header.
				ABCD = Block indexes to show on moderation main page.
				yyy = Integer with the following bit status:
					- yyy & 1 = Always notify on reports.
					- yyy & 2 = Notify on reports for moderators only.
					- yyy & 4 = Notify about posts awaiting approval.
		*/

		// Do blocks first!
		$mod_blocks = '';
		if (!empty($_POST['mod_homepage']))
			foreach ($_POST['mod_homepage'] as $k => $v)
			{
				// Sanitise my friend!
				if (preg_match('~([a-zA-Z])~', $k, $matches))
					$mod_blocks .= $matches[0];
			}

		// Do we have the option of changing other settings?
		if ($context['can_moderate_boards'])
		{
			$pref_binary = 0;
			if (!empty($_POST['mod_notify_approval']))
				$pref_binary |= 4;
			if (!empty($_POST['mod_notify_report']))
				$pref_binary |= ($_POST['mod_notify_report'] == 2 ? 1 : 2);

			$show_reports = !empty($_POST['mod_show_reports']) ? 1 : 0;
		}

		// Put it all together.
		$mod_prefs = $show_reports . '|' . $mod_blocks . '|' . $pref_binary;
		updateMemberData($user_info['id'], array('mod_prefs' => $mod_prefs));
	}

	// What blocks does the user currently have selected?
	$context['mod_settings'] = array(
		'show_reports' => $show_reports,
		'notify_report' => $pref_binary & 2 ? 1 : ($pref_binary & 1 ? 2 : 0),
		'notify_approval' => $pref_binary & 4,
		'user_blocks' => str_split($mod_blocks),
	);
}

?>