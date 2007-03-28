<?php
/**********************************************************************************
* ModerationCenter.php                                                            *
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

	/* Define all the sections on the moderation center - these are then properly converted into context!

		Possible fields:
			For Section:
				string $title:		Section title.
				bool $enabled:		Should section be shown?
				array $areas:		Array of areas within this section.
				array $permission:	Permission required to access the whole section.

			For Areas:
				array $permission:	Array of permissions to determine who can access this area.
				string $label:		Optional text string for link (Otherwise $txt[$index] will be used)
				string $file:		Name of source file required for this area.
				string $function:	Function to call when area is selected.
				string $custom_url:	URL to use for this menu item.
				bool $enabled:		Should this area even be shown?
				string $select:		If set this item will not be displayed - instead the item indexed here shall be.
				array $subsections:	Array of subsections from this area.

			For Subsections:
				string 0:		Text label for this subsection.
				array 0:		Array of permissions to check for this subsection.
	*/

	$context['admin_areas'] = array(
		'main' => array(
			'title' => $txt['mc_main'],
			'areas' => array(
				'index' => array(
					'label' => $txt['moderation_center'],
					'function' => 'ModerationHome',
				),
				'modlog' => array(
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
				),
			),
		),
		'posts' => array(
			'title' => $txt['mc_posts'],
			'areas' => array(
				'postmod' => array(
					'label' => $txt['mc_unapproved_posts'],
					'file' => 'PostModeration.php',
					'function' => 'PostModerationMain',
					'custom_url' => $scripturl . '?action=moderate;area=postmod;sa=posts',
				),
				'attachmod' => array(
					'label' => $txt['mc_unapproved_attachments'],
					'file' => 'PostModeration.php',
					'function' => 'PostModerationMain',
					'custom_url' => $scripturl . '?action=moderate;area=attachmod;sa=attachments',
				),
				'reports' => array(
					'label' => $txt['mc_reported_posts'],
					'file' => 'ModerationCenter.php',
					'function' => 'ReportedPosts',
				),
			),
		),
		'groups' => array(
			'title' => $txt['mc_groups'],
			'enabled' => !empty($user_info['mod_cache']['gq']) || allowedTo('manage_membergroups'),
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
			'enabled' => !empty($user_info['mod_cache']['gq']) || allowedTo('manage_membergroups'),
			'areas' => array(
				'settings' => array(
					'label' => $txt['mc_settings'],
					'function' => 'ModerationSettings',
				),
			),
		),
	);

	// I don't know where we're going - I don't know where we've been...
	$mod_area = isset($_GET['area']) ? $_GET['area'] : 'index';
	$mod_include_data = $context['admin_areas']['main']['areas']['index'];

	// Now do all the formatting!
	foreach ($context['admin_areas'] as $section_id => $section)
	{
		// Is this enabled - or has as permission check!
		if ((isset($section['enabled']) && $section['enabled'] == false) || (isset($section['permission']) && !allowedTo($section['permission'])))
		{
			continue;
			unset($context['admin_areas'][$section_id]);
		}

		foreach ($section['areas'] as $area_id => $area)
		{
			// Is this what we are looking for?
			if ($mod_area == $area_id && (!isset($area['enabled']) || $area['enabled'] != false) && (empty($area['permission']) || allowedTo($area['permission'])))
			{
				// Found and validated where we want to be!
				$context['admin_section'] = $section_id;
				$context['admin_area'] = isset($area['select']) ? $area['select'] : $area_id;
				$mod_include_data = $area;

				// Flag to the section that this is active.
				$context['admin_areas'][$section_id]['selected'] = true;
			}

			// Can we do this?
			if ((!isset($area['enabled']) || $area['enabled'] != false) && (empty($area['permission']) || allowedTo($area['permission'])))
			{
				// Replace the contents with some ickle data - assuming it has a label.
				if (isset($area['label']) || isset($txt[$area_id]))
				{
					$context['admin_areas'][$section_id]['areas'][$area_id] = array('label' => isset($area['label']) ? $area['label'] : $txt[$area_id]);
					// Does it have a custom URL?
					if (isset($area['custom_url']))
						$context['admin_areas'][$section_id]['areas'][$area_id]['url'] = $area['custom_url'];

					// Did it have subsections?
					if (isset($area['subsections']))
					{
						$context['admin_areas'][$section_id]['areas'][$area_id]['subsections'] = array();
						foreach ($area['subsections'] as $sa => $sub)
							if (empty($sub[1]) || allowedTo($sub[1]))
								$context['admin_areas'][$section_id]['areas'][$area_id]['subsections'][$sa] = array('label' => $sub[0]);
					}
				}
				else
					unset($context['admin_areas'][$section_id]['areas'][$area_id]);
			}
			// Otherwise unset it!
			else
				unset($context['admin_areas'][$section_id]['areas'][$area_id]);
		}

		// Did we remove every possible area?
		if (empty($context['admin_areas'][$section_id]['areas']))
			unset($context['admin_areas'][$section_id]);
	}

	if (empty($context['admin_area']))
	{
		$context['admin_area'] = 'index';
		$context['admin_section'] = 'main';
	}

	// And put the lovely surround around it all, beutiful.
	$context['template_layers'][] = 'admin';
	$context['show_drop_down'] = empty($modSettings['showsidebarAdmin']) && isset($settings['theme_version']) && $settings['theme_version'] >= 2.0 && !isset($settings['disable_drop_down']);

	// We want a menu, but missing the stylesheet? Get the fallback stylesheet then!
	if ($context['show_drop_down'] && file_exists($settings['theme_dir'].'/css/dropmenu.css'))
		$context['html_headers'] .= '<link rel="stylesheet" type="text/css" href="' . $settings['theme_url'] . '/css/dropmenu.css" />';
	elseif ($context['show_drop_down'] && !file_exists($settings['theme_dir'].'/css/dropmenu.css'))
		$context['html_headers'] .= '<link rel="stylesheet" type="text/css" href="' . $settings['default_theme_url'] . '/css/dropmenu_default.css" />';

	// Now - finally - call the right place!
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
	global $context, $db_prefix, $smfFunc, $scripturl, $modSettings;

	if (($watched_users = cache_get_data('recent_user_watches', 240)) === null)
	{
		list (, $modSettings['warning_watch']) = explode(',', $modSettings['warning_settings']);
		$modSettings['warning_watch'] = empty($modSettings['warning_watch']) ? 1 : $modSettings['warning_watch'];
		$request = $smfFunc['db_query']('', "
			SELECT id_member, real_name, last_login
			FROM {$db_prefix}members
			WHERE warning >= $modSettings[warning_watch]
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
	list ($modSettings['warn_enabled'], $modSettings['warning_watch']) = explode(',', $modSettings['warning_settings']);
	$modSettings['warning_watch'] = empty($modSettings['warning_watch']) ? 1 : $modSettings['warning_watch'];

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
			WHERE warning >= $modSettings[warning_watch]", __FILE__, __LINE__);
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
			WHERE warning >= $modSettings[warning_watch]
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
			WHERE warning >= $modSettings[warning_watch]
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
			WHERE mem.warning >= $modSettings[warning_watch]
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

// Change moderation preferences.
function ModerationSettings()
{
	global $context, $db_prefix, $smfFunc, $txt, $sourcedir, $scripturl, $user_settings, $user_info;

	// Some useful context stuff.
	loadTemplate('ModerationCenter');
	$context['page_title'] = $txt['mc_settings'];
	$context['sub_template'] = 'moderation_settings';

	// They can only change some settings if they can moderate boards/groups.
	$context['can_moderate_boards'] = !empty($user_info['mod_cache']['bq']);
	$context['can_moderate_groups'] = !empty($user_info['mod_cache']['gq']);

	// What blocks can this user see?
	$context['homepage_blocks'] = array(
		'n' => $txt['mc_prefs_latest_news'],
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
			foreach ($_POST['mod_homepage'] as $v)
			{
				// Sanitise my friend!
				if (preg_match('~([a-zA-Z])~', $v, $matches))
					$mod_blocks .= $matches[0];
			}

		// Do we have the option of changing other settings?
		if ($context['can_moderate_boards'])
		{
			$pref_binary = 0;
			if (!empty($_POST['mod_notify_approval']))
				$pref_binary |= 4;
			if (!empty($_POST['mod_notify_report']))
			{
				$pref_binary |= ($_POST['mod_notify_report'] == 2 ? 1 : 2);
			}

			$show_reports = !empty($_POST['mod_show_reports']) ? 1 : 0;
		}

		// Put it all together.
		$mod_prefs = $show_reports . '|' . $mod_blocks . '|' . $pref_binary;
		updateMemberData($user_info['id'], array('mod_prefs' => "'$mod_prefs'"));
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