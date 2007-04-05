<?php
/**********************************************************************************
* Admin.php                                                                       *
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

/*	This file, unpredictable as this might be, handles basic administration.
	The most important function in this file for mod makers happens to be the
	updateSettingsFile() function, but it shouldn't be used often anyway.

	void AdminMain()
		- initialises all the basic context required for the admin center.
		- passes execution onto the relevant admin section.
		- if the passed section is not found it shows the admin home page.

	void adminIndex($area)
		// !!!

	void AdminHome()
		- prepares all the data necessary for the administration front page.
		- uses the Admin template along with the admin sub template.
		- requires the moderate_forum, manage_membergroups, manage_bans,
		  admin_forum, manage_permissions, manage_attachments, manage_smileys,
		  manage_boards, edit_news, or send_mail permission.
		- uses the index administrative area.
		- can be found by going to ?action=admin.

	void VersionDetail()
		- parses the comment headers in all files for their version information
		  and outputs that for some javascript to check with simplemacines.org.
		- does not connect directly with simplemachines.org, but rather
		  expects the client to.
		- requires the admin_forum permission.
		- uses the view_versions admin area.
		- loads the view_versions sub template (in the Admin template.)
		- accessed through ?action=admin;area=version.

	void ManageCopyright()
		// !!!

	void CleanupPermissions()
		- cleans up file permissions, in the hopes of making things work
		  smoother and potentially more securely.
		- can set permissions to either restrictive, free, or standard.
		- accessed via ?action=admin;area=cleanperms.

	void AdminSearch()
		// !!

	void AdminSearchInteral()
		// !!

	void AdminSearchMember()
		// !!

*/

// The main admin handling function.
function AdminMain()
{
	global $txt, $context, $scripturl, $sc, $modSettings, $user_info, $settings, $sourcedir, $options, $smfFunc, $db_prefix;

	// Load the language and templates....
	loadLanguage('Admin');
	loadTemplate('Admin');

	// Ensure we are admin'ing.
	$context['bar_area'] = 'admin';

	// Are we toggling the bar?
	if (isset($_GET['togglebar']))
	{
		$options['use_side_bar'] = (int) $_GET['togglebar'];
		$smfFunc['db_insert'](
			'replace',
			"{$db_prefix}themes",
			array('id_member', 'id_theme', 'variable', 'value'),
			array($user_info['id'], 1, "'use_side_bar'", $options['use_side_bar']),
			array('id_member', 'id_theme', 'value'), __FILE__, __LINE__
		);
	}

	/* Define all the sections on the admin area - these are then properly converted into context!

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
				array 1:		Array of permissions to check for this subsection.
	*/

	$context['admin_areas'] = array(
		'forum' => array(
			'title' => $txt['admin_main'],
			'areas' => array(
				'index' => array(
					'label' => $txt['admin_center'],
					'function' => 'AdminHome',
					'icon' => 'administration.gif',
				),
				'credits' => array(
					'label' => $txt['support_credits_title'],
					'function' => 'AdminHome',
					'icon' => 'support.gif',
				),
				'news' => array(
					'label' => $txt['news_title'],
					'file' => 'ManageNews.php',
					'function' => 'ManageNews',
					'icon' => 'news.gif',
					'permission' => array('edit_news', 'send_mail', 'admin_forum'),
					'subsections' => array(
						'edit_news' => array($txt['admin_edit_news'], 'edit_news'),
						'mailingmembers' => array($txt['admin_newsletters'], 'send_mail'),
						'settings' => array($txt['settings'], 'admin_forum'),
					),
				),
				'packages' => array(
					'label' => $txt['package'],
					'file' => 'Packages.php',
					'function' => 'Packages',
					'permission' => array('edit_news', 'send_mail', 'admin_forum'),
					'icon' => 'packages.gif',
				),
				'cleanperms' => array(
					'function' => 'CleanupPermissions',
					'permission' => array('admin_forum'),
					'select' => 'packages'
				),
				'version' => array(
					'function' => 'VersionDetail',
					'permission' => array('admin_forum'),
					'select' => 'index'
				),
				'copyright' => array(
					'function' => 'ManageCopyright',
					'permission' => array('admin_forum'),
					'select' => 'index'
				),
				'search' => array(
					'function' => 'AdminSearch',
					'permission' => array('admin_forum'),
					'select' => 'index'
				),
			),
		),
		'config' => array(
			'title' => $txt['admin_config'],
			'permission' => array('admin_forum'),
			'areas' => array(
				'featuresettings' => array(
					'label' => $txt['modSettings_title'],
					'file' => 'ModSettings.php',
					'function' => 'ModifyFeatureSettings',
					'icon' => 'features.gif',
				),
				'serversettings' => array(
					'label' => $txt['admin_server_settings'],
					'file' => 'ManageServer.php',
					'function' => 'ModifySettings',
					'icon' => 'server.gif',
				),
				'current_theme' => array(
					'label' => $txt['theme_current_settings'],
					'file' => 'Themes.php',
					'function' => 'ThemesMain',
					'custom_url' => $scripturl . '?action=admin;area=theme;sa=settings;th=' . $settings['theme_id'],
					'icon' => 'current_theme.gif',
				),
				'theme' => array(
					'label' => $txt['theme_admin'],
					'file' => 'Themes.php',
					'function' => 'ThemesMain',
					'custom_url' => $scripturl . '?action=admin;area=theme;sa=admin',
					'icon' => 'themes.gif',
				),
			),
		),
		'layout' => array(
			'title' => $txt['layout_controls'],
			'permission' => array('manage_boards', 'admin_forum', 'manage_smileys', 'manage_attachments', 'moderate_forum'),
			'areas' => array(
				'manageboards' => array(
					'label' => $txt['admin_boards'],
					'file' => 'ManageBoards.php',
					'function' => 'ManageBoards',
					'permission' => array('manage_boards'),
				),
				'postsettings' => array(
					'label' => $txt['manageposts'],
					'file' => 'ManagePosts.php',
					'function' => 'ManagePostSettings',
					'permission' => array('admin_forum', 'moderate_forum'),
				),
				'managecalendar' => array(
					'label' => $txt['manage_calendar'],
					'file' => 'ManageCalendar.php',
					'function' => 'ManageCalendar',
					'permission' => array('admin_forum'),
				),
				'managesearch' => array(
					'label' => $txt['manage_search'],
					'file' => 'ManageSearch.php',
					'function' => 'ManageSearch',
					'permission' => array('admin_forum'),
				),
				'smileys' => array(
					'label' => $txt['smileys_manage'],
					'file' => 'ManageSmileys.php',
					'function' => 'ManageSmileys',
					'permission' => array('manage_smileys'),
				),
				'manageattachments' => array(
					'label' => $txt['attachments_avatars'],
					'file' => 'ManageAttachments.php',
					'function' => 'ManageAttachments',
					'permission' => array('manage_attachments'),
				),
			),
		),
		'members' => array(
			'title' => $txt['admin_manage_members'],
			'permission' => array('moderate_forum', 'manage_membergroups', 'manage_bans', 'manage_permissions', 'admin_forum'),
			'areas' => array(
				'viewmembers' => array(
					'label' => $txt['admin_users'],
					'file' => 'ManageMembers.php',
					'function' => 'ViewMembers',
					'permission' => array('moderate_forum'),
				),
				'membergroups' => array(
					'label' => $txt['admin_groups'],
					'file' => 'ManageMembergroups.php',
					'function' => 'ModifyMembergroups',
					'permission' => array('moderate_forum'),
				),
				'permissions' => array(
					'label' => $txt['edit_permissions'],
					'file' => 'ManagePermissions.php',
					'function' => 'ModifyPermissions',
					'permission' => array('moderate_forum'),
				),
				'regcenter' => array(
					'label' => $txt['registration_center'],
					'file' => 'ManageRegistration.php',
					'function' => 'RegCenter',
					'permission' => array('admin_forum', 'moderate_forum'),
				),
				'ban' => array(
					'label' => $txt['ban_title'],
					'file' => 'ManageBans.php',
					'function' => 'Ban',
					'permission' => array('manage_bans'),
				),
			),
		),
		'maintenance' => array(
			'title' => $txt['admin_maintenace'],
			'permission' => array('admin_forum'),
			'areas' => array(
				'maintain' => array(
					'label' => $txt['maintain_title'],
					'file' => 'ManageMaintenance.php',
					'function' => 'ManageMaintenance',
					'subsections' => array(
						'general' => array($txt['maintain_common'], 'admin_forum'),
						'tasks' => array($txt['maintain_tasks'], 'admin_forum'),
					),
				),
				'mailqueue' => array(
					'label' => $txt['mailqueue_title'],
					'file' => 'ManageMail.php',
					'function' => 'ManageMail',
					'subsections' => array(
						'browse' => array($txt['mailqueue_browse'], 'admin_forum'),
						'settings' => array($txt['mailqueue_settings'], 'admin_forum'),
					),
				),
				'reports' => array(
					'label' => $txt['generate_reports'],
					'file' => 'Reports.php',
					'function' => 'ReportsMain',
				),
				'errorlog' => array(
					'label' => $txt['errlog'],
					'file' => 'ManageErrors.php',
					'function' => 'ViewErrorLog',
					'custom_url' => $scripturl . '?action=admin;area=errorlog;desc',
				),
				'dumpdb' => array(
					'file' => 'DumpDatabase.php',
					'function' => 'DumpDatabase2',
					'select' => 'maintain',
				),
				'repairboards' => array(
					'file' => 'RepairBoards.php',
					'function' => 'RepairBoards',
					'select' => 'maintain',
				),
			),
		),
	);

	// Figure out which one we're in now... making some defaults if required.
	$admin_area = isset($_GET['area']) ? $_GET['area'] : 'index';
	$admin_include_data = $context['admin_areas']['forum']['areas']['index'];

	// Add a work around for editing current theme.
	if ($admin_area == 'theme' && isset($_GET['th']) && $_GET['th'] == $settings['theme_id'])
		$admin_area = 'current_theme';

	foreach ($context['admin_areas'] as $section_id => $section)
	{
		// Is this enabled - or has as permission check!
		if ((isset($section['enabled']) && $section['enabled'] == false) || (isset($section['permission']) && !allowedTo($section['permission'])))
		{
			unset($context['admin_areas'][$section_id]);
			continue;
		}

		foreach ($section['areas'] as $area_id => $area)
		{
			// Is this what we are looking for?
			if ($admin_area == $area_id && (!isset($area['enabled']) || $area['enabled'] != false) && (empty($area['permission']) || allowedTo($area['permission'])))
			{
				// Found and validated where we want to be!
				$context['admin_section'] = $section_id;
				$context['admin_area'] = isset($area['select']) ? $area['select'] : $area_id;
				$admin_include_data = $area;

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

					// and a icon as well?
					if (isset($area['icon']))
						$context['admin_areas'][$section_id]['areas'][$area_id]['icon'] = '<img src="' . $settings['images_url'] . '/admin/' . $area['icon'] . '" alt="" />&nbsp;&nbsp;';
					else
						$context['admin_areas'][$section_id]['areas'][$area_id]['icon'] = '';

					// Did it have subsections?
					if (isset($area['subsections']))
					{
						$context['admin_areas'][$section_id]['areas'][$area_id]['subsections'] = array();
						foreach ($area['subsections'] as $sa => $sub)
							if (empty($sub[1]) || allowedTo($sub[1]))
							{
								$context['admin_areas'][$section_id]['areas'][$area_id]['subsections'][$sa] = array('label' => $sub[0]);
								// A bit complicated - but is this set?
								if ($admin_area == $area_id && (isset($_REQUEST['sa']) && $_REQUEST['sa'] == $sa))
									$context['admin_areas'][$section_id]['areas'][$area_id]['subsections'][$sa]['selected'] = true;
							}
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

	// Make sure the administrator has a valid session...
	validateSession();

	// If we didn't find it make sure the context is right!
	if (empty($context['admin_area']))
	{
		$context['admin_area'] = 'index';
		$context['admin_section'] = 'forum';
	}

	// obExit will know what to do!
	$context['template_layers'][] = 'admin';
	$context['can_toggle_drop_down'] = isset($settings['theme_version']) && $settings['theme_version'] >= 2.0;
	$context['show_drop_down'] = empty($options['use_side_bar']) && $context['can_toggle_drop_down'];

	// We want a menu, but missing the stylesheet? Get the fallback stylesheet then!
	if ($context['show_drop_down'] && file_exists($settings['theme_dir'].'/css/dropmenu.css'))
		$context['html_headers'] .= '<link rel="stylesheet" type="text/css" href="' . $settings['theme_url'] . '/css/dropmenu.css" />';
	elseif ($context['show_drop_down'] && !file_exists($settings['theme_dir'].'/css/dropmenu.css'))
		$context['html_headers'] .= '<link rel="stylesheet" type="text/css" href="' . $settings['default_theme_url'] . '/css/dropmenu_default.css" />';

	// Now - finally - call the right place!
	if (isset($admin_include_data['file']))
		require_once($sourcedir . '/' . $admin_include_data['file']);

	$admin_include_data['function']();
}

// The main administration section.
function AdminHome()
{
	global $sourcedir, $db_prefix, $forum_version, $txt, $scripturl, $context, $user_info, $boardurl, $modSettings, $smfFunc;

	// You have to be able to do at least one of the below to see this page.
	isAllowedTo(array('admin_forum', 'manage_permissions', 'moderate_forum', 'manage_membergroups', 'manage_bans', 'send_mail', 'edit_news', 'manage_boards', 'manage_smileys', 'manage_attachments'));

	// Find all of this forum's administrators...
	require_once($sourcedir . '/Subs-Membergroups.php');
	if (listMembergroupMembers_Href($context['administrators'], 1, 32) && allowedTo('manage_membergroups'))
	{
		// Add a 'more'-link if there are more than 32.
		$context['more_admins_link'] = '<a href="' . $scripturl . '?action=moderate;area=viewgroups;sa=members;group=1">' . $txt['more'] . '</a>';
	}

	// Some stuff.... :P.
	$context['credits'] = '
<i>Simple Machines wants to thank everyone who helped make SMF 1.1 what it is today; shaping and directing our project, all through the thick and the thin. It wouldn\'t have been possible without you.</i><br />
<div style="margin-top: 1ex;"><i>This includes our users and especially Charter Members - thanks for installing and using our software as well as providing valuable feedback, bug reports, and opinions.</i></div>
<div style="margin-top: 2ex;"><b>Project Managers:</b> Amacythe, David Recordon, Joseph Fung, and Jeff Lewis.</div>
<div style="margin-top: 1ex;"><b>Developers:</b> Hendrik Jan &quot;Compuart&quot; Visser, Matt &quot;Grudge&quot; Wolf, Michael "Thantos" Miller, Theodore "Orstio" Hildebrandt, and Unknown W. &quot;[Unknown]&quot; Brackets</div>
<div style="margin-top: 1ex;"><b>Support Specialists:</b> Ben Scott, Michael &quot;Oldiesmann&quot; Eshom, A&auml;ron van Geffen, Alexandre "Ap2" Patenaude, Andrea Hubacher, Chris Cromer, [darksteel], dtm.exe, Fizzy, Horseman, Huw Ayling-Miller, Jan "Owdy" Eriksson, jerm, Juan "JayBachatero" Hernandez, Justyne, Killer Possum, Kindred, Matthew "Mattitude" Hall, Mediman, Metho, Omar Bazavilvazo, Pitti, redone, Tomer "Lamper" Dean, and xenovanis.</div>
<div style="margin-top: 1ex;"><b>Mod Developers:</b> snork13, Cristi&aacute;n "Anguz" L&aacute;vaque, Goosemoose, Jack.R.Abbit, James "Cheschire" Yarbro, Jesse "Gobalopper" Reid, Kirby, and vbgamer45.</div>
<div style="margin-top: 1ex;"><b>Documentation Writers:</b> akabugeyes, eldacar, Jerry, Nave, Matthew "Mattitude" Hall, and Trekkie101.</div>
<div style="margin-top: 1ex;"><b>Language Coordinators:</b> Daniel Diehl and Adam &quot;Bostasp&quot; Southall.</div>
<div style="margin-top: 1ex;"><b>Graphic Designers:</b> Bjoern "Bloc" Kristiansen, Alienine (Adrian), A.M.A, babylonking, BlackouT, Burpee, diplomat, Eren "forsakenlad" Yasarkurt, Hyper Piranha, Killer Possum, Mystica, Nico "aliencowfarm" Boer, Philip "Meriadoc" Renich and Tippmaster.</div>
<div style="margin-top: 1ex;"><b>Site team:</b> Douglas, dschwab9, and Tim.</div>
<div style="margin-top: 1ex;">And for anyone we may have missed, thank you!</div>';

	// Copyright?
	if (!empty($modSettings['copy_settings']) || !empty($modSettings['copyright_key']))
	{
		if (empty($modSettings['copy_settings']))
			$modSettings['copy_settings'] = 'a,0';

		// Not done it yet...
		if (empty($_SESSION['copy_expire']))
		{
			list ($key, $expires) = explode(',', $modSettings['copy_settings']);
			// Get the expired date.
			$fp = @fsockopen("www.simplemachines.org", 80, $errno, $errstr, 1);
			if ($fp)
			{
				$out = "GET /smf/copyright/check_copyright.php?site=" . base64_encode($boardurl) . "&key=" . $key . "&version=" . base64_encode($forum_version) . " HTTP/1.1\r\n";
				$out .= "Host: www.simplemachines.org\r\n";
				$out .= "Connection: Close\r\n\r\n";
				fwrite($fp, $out);
	
				$return_data = '';
				while (!feof($fp))
					$return_data .= fgets($fp, 128);
				fclose($fp);

				// Get the expire date.
				$return_data = substr($return_data, strpos($return_data, 'STARTCOPY') + 9);
				$return_data = trim(substr($return_data, 0, strpos($return_data, 'ENDCOPY')));

				if ($return_data != 'void')
				{
					list ($_SESSION['copy_expire'], $modSettings['copyright_key']) = explode('|', $return_data);
					$_SESSION['copy_key'] = $key;
					$modSettings['copy_settings'] = $key . ',' . (int) $return_data;
					updateSettings(array('copy_settings' => $modSettings['copy_settings'], 'copyright_key' => $modSettings['copyright_key']));
				}
				else
				{
					$_SESSION['copy_expire'] = '';
					$smfFunc['db_query']('', "
						DELETE FROM {$db_prefix}settings
						WHERE variable = 'copy_settings'
							OR variable = 'copyright_key'", __FILE__, __LINE__);
				}
			}
		}

		if ($_SESSION['copy_expire'] && $_SESSION['copy_expire'] > time())
		{
			$context['copyright_expires'] = (int) (($_SESSION['copy_expire'] - time()) / 3600 / 24);
			$context['copyright_key'] = $_SESSION['copy_key'];
		}
	}		

	// This makes it easier to get the latest news with your time format.
	$context['time_format'] = urlencode($user_info['time_format']);

	$context['current_versions'] = array(
		'php' => array('title' => $txt['support_versions_php'], 'version' => PHP_VERSION),
		'db' => array('title' => sprintf($txt['support_versions_db'], $smfFunc['db_title']), 'version' => ''),
		'server' => array('title' => $txt['support_versions_server'], 'version' => $_SERVER['SERVER_SOFTWARE']),
	);
	$context['forum_version'] = $forum_version;

	// Get a list of current server versions.
	require_once($sourcedir . '/Subs-Admin.php');
	$checkFor = array(
		'gd',
		'db_server',
		'mmcache',
		'eaccelerator',
		'phpa',
		'apc',
		'php',
		'server',
	);
	$context['current_versions'] = getServerVersions($checkFor);

	$context['can_admin'] = allowedTo('admin_forum');

	$context['sub_template'] = $context['admin_area'] == 'credits' ? 'credits' : 'admin';
	$context['page_title'] = $context['admin_area'] == 'credits' ? $txt['support_credits_title'] : $txt['admin_center'];

	// The format of this array is: permission, action, title, description.
	$quick_admin_tasks = array(
		array('', 'credits', 'support_credits_title', 'support_credits_info'),
		array('admin_forum', 'featuresettings', 'modSettings_title', 'modSettings_info'),
		array('admin_forum', 'maintain', 'maintain_title', 'maintain_info'),
		array('manage_permissions', 'permissions', 'edit_permissions', 'edit_permissions_info'),
		array('admin_forum', 'theme;sa=admin;sesc=' . $context['session_id'], 'theme_admin', 'theme_admin_info'),
		array('admin_forum', 'packages', 'package', 'package_info'),
		array('manage_smileys', 'smileys', 'smileys_manage', 'smileys_manage_info'),
		array('moderate_forum', 'viewmembers', 'admin_users', 'member_center_info'),
	);

	$context['quick_admin_tasks'] = array();
	foreach ($quick_admin_tasks as $task)
	{
		if (!empty($task[0]) && !allowedTo($task[0]))
			continue;

		$context['quick_admin_tasks'][] = array(
			'href' => $scripturl . '?action=admin;area=' . $task[1],
			'link' => '<a href="' . $scripturl . '?action=admin;area=' . $task[1] . '">' . $txt[$task[2]] . '</a>',
			'title' => $txt[$task[2]],
			'description' => $txt[$task[3]],
			'is_last' => false
		);
	}

	if (count($context['quick_admin_tasks']) % 2 == 1)
	{
		$context['quick_admin_tasks'][] = array(
			'href' => '',
			'link' => '',
			'title' => '',
			'description' => '',
			'is_last' => true
		);
		$context['quick_admin_tasks'][count($context['quick_admin_tasks']) - 2]['is_last'] = true;
	}
	elseif (count($context['quick_admin_tasks']) != 0)
	{
		$context['quick_admin_tasks'][count($context['quick_admin_tasks']) - 1]['is_last'] = true;
		$context['quick_admin_tasks'][count($context['quick_admin_tasks']) - 2]['is_last'] = true;
	}
}

// Perform a detailed version check.  A very good thing ;).
function VersionDetail()
{
	global $forum_version, $txt, $sourcedir, $context;

	isAllowedTo('admin_forum');

	// Call the function that'll get all the version info we need.
	require_once($sourcedir . '/Subs-Admin.php');
	$versionOptions = array(
		'include_ssi' => true,
		'sort_results' => true,
	);
	$version_info = getFileVersions($versionOptions);

	// Add the new info to the template context.
	$context += array(
		'file_versions' => $version_info['file_versions'],
		'default_template_versions' => $version_info['default_template_versions'],
		'template_versions' => $version_info['template_versions'],
		'default_language_versions' => $version_info['default_language_versions'],
		'default_known_languages' => array_keys($version_info['default_language_versions']),
	);

	// Make it easier to manage for the template.
	$context['forum_version'] = $forum_version;

	$context['sub_template'] = 'view_versions';
	$context['page_title'] = $txt['admin_version_check'];
}

// Allow users to remove their copyright.
function ManageCopyright()
{
	global $forum_version, $txt, $sourcedir, $context, $boardurl, $modSettings;

	isAllowedTo('admin_forum');

	if (isset($_POST['copy_code']))
	{
		checkSession('post');

		$_POST['copy_code'] = urlencode($_POST['copy_code']);

		// Check the actual code.
		$fp = @fsockopen("www.simplemachines.org", 80, $errno, $errstr);
		if ($fp)
		{
			$out = "GET /smf/copyright/check_copyright.php?site=" . base64_encode($boardurl) . "&key=" . $_POST['copy_code'] . "&version=" . base64_encode($forum_version) . " HTTP/1.1\r\n";
			$out .= "Host: www.simplemachines.org\r\n";
			$out .= "Connection: Close\r\n\r\n";
			fwrite($fp, $out);

			$return_data = '';
			while (!feof($fp))
				$return_data .= fgets($fp, 128);
			fclose($fp);

			// Get the data back
			$return_data = substr($return_data, strpos($return_data, 'STARTCOPY') + 9);
			$return_data = trim(substr($return_data, 0, strpos($return_data, 'ENDCOPY')));

			if ($return_data != 'void')
			{
				echo $return_data;
				list ($_SESSION['copy_expire'], $modSettings['copyright_key']) = explode('|', $return_data);
				$_SESSION['copy_key'] = $key;
				$modSettings['copy_settings'] = $key . ',' . (int) $return_data;
				updateSettings(array('copy_settings' => $modSettings['copy_settings'], 'copyright_key' => $modSettings['copyright_key']));
				redirectexit('action=admin');
			}
			else
			{
				fatal_lang_error('copyright_failed');
			}
		}
	}

	$context['sub_template'] = 'manage_copyright';
	$context['page_title'] = $txt['copyright_removal'];
}

// Clean up the permissions one way or another.
function CleanupPermissions()
{
	global $boarddir, $sourcedir, $scripturl, $package_ftp, $modSettings;

	isAllowedTo('admin_forum');
	umask(0);

	loadTemplate('Packages');
	loadLanguage('Packages');

	if (!isset($_REQUEST['perm_type']) || !in_array($_REQUEST['perm_type'], array('free', 'restrictive', 'standard')))
		$_REQUEST['perm_type'] = 'free';

	checkSession();

	// FTP to the rescue!
	require_once($sourcedir . '/Subs-Package.php');
	packageRequireFTP($scripturl . '?action=admin;area=cleanperms;perm_type=' . $_REQUEST['perm_type']);

	// Do the cleanup.
	require_once($sourcedir . '/Subs-Admin.php');
	cleanupFilePermissions($_REQUEST['perm_type']);

	redirectexit('action=admin;area=packages;sa=options');
}

// Get one of the admin information files from Simple Machines.
function DisplayAdminFile()
{
	global $db_prefix, $context, $modSettings, $smfFunc;

	// Danger Will Robinson.
	$_REQUEST['filename'] = $smfFunc['db_escape_string']($_REQUEST['filename']);
	
	$request = $smfFunc['db_query']('', "
		SELECT data, filetype
		FROM {$db_prefix}admin_info_files
		WHERE filename = '$_REQUEST[filename]'
		LIMIT 1", __FILE__, __LINE__);

	if ($smfFunc['db_num_rows']($request) == 0)
		fatal_lang_error('admin_file_not_found', true, array($_REQUEST['filename']));

	list ($file_data, $filetype) = $smfFunc['db_fetch_row']($request);
	$smfFunc['db_free_result']($request);

	$context['template_layers'] = array();
	// Lets make sure we aren't going to output anything nasty.
	@ob_end_clean();
	if (!empty($modSettings['enableCompressedOutput']))
		@ob_start('ob_gzhandler');
	else
		@ob_start();

	// Make sure they know what type of file we are.
	header('Content-Type: ' . $filetype);
	echo $file_data;
	obExit(false);
}

// This allocates out all the search stuff.
function AdminSearch()
{
	global $txt, $context, $smfFunc;

	isAllowedTo('admin_forum');

	// What can we search for?
	$subactions = array(
		'internal' => 'AdminSearchInternal',
		'online' => 'AdminSearchOM',
		'member' => 'AdminSearchMember',
	);

	$context['search_type'] = !isset($_REQUEST['search_type']) || !isset($subactions[$_REQUEST['search_type']]) ? 'internal' : $_REQUEST['search_type'];
	$context['search_term'] = $smfFunc['db_unescape_string']($_REQUEST['search_term']);

	$context['sub_template'] = 'admin_search_results';
	$context['page_title'] = $txt['admin_search_results'];

	$subactions[$context['search_type']]();
}

// A complicated but relatively quick internal search.
function AdminSearchInternal()
{
	global $context, $txt, $helptxt, $scripturl, $sourcedir;

	// Load a lot of language files.
	$language_files = array(
		'Help', 'ManageMail', 'ModSettings', 'ManageCalendar', 'ManageBoards', 'ManagePermissions', 'Search',
		'ManageSmileys',
	);
	loadLanguage(implode('+', $language_files));

	// All the files we need to include.
	$include_files = array(
		'ModSettings', 'ManageBoards', 'ManageNews', 'ManageAttachments', 'ManageCalendar', 'ManageMail', 'ManagePermissions',
		'ManagePosts', 'ManageSearch', 'ManageServer', 'ManageSmileys',
	);
	foreach ($include_files as $file)
		require_once($sourcedir . '/' . $file . '.php');

	/* This is the huge array that defines everything... it's a huge array of items formatted as follows:
		0 = Language index (Can be array of indexes) to search through for this setting.
		1 = URL for this indexes page.
		2 = Help index for help associated with this item (If different from 0)
	*/

	$search_data = array(
		// All the major sections of the forum.
		'sections' => array(
		),
		'settings' => array(
			array('COPPA', 'area=regcenter;sa=settings'),
			array('CAPTCHA', 'area=regcenter;sa=settings'),
		),
	);

	// Go through the admin menu structure trying to find suitably named areas!
	foreach ($context['admin_areas'] as $section)
	{
		foreach ($section['areas'] as $menu_key => $menu_item)
		{
			$search_data['sections'][] = array($menu_item['label'], 'area=' . $menu_key);
			if (!empty($menu_item['subsections']))
				foreach ($menu_item['subsections'] as $key => $sublabel)
					$search_data['sections'][] = array($sublabel['label'], 'area=' . $menu_key . ';sa=' . $key);
		}
	}

	// This is a special array of functions that contain setting data - we query all these to simply pull all setting bits!
	$settings_search = array(
		array('ModifyBasicSettings', 'area=featuresettings;sa=basic'),
		array('ModifySecuritySettings', 'area=featuresettings;sa=security'),
		array('ModifyLayoutSettings', 'area=featuresettings;sa=layout'),
		array('ModifyKarmaSettings', 'area=featuresettings;sa=karma'),
		array('ModifyModerationSettings', 'area=featuresettings;sa=moderate'),
		array('ModifySignatureSettings', 'area=featuresettings;sa=sig'),
		array('ManageAttachmentSettings', 'area=manageattachments;sa=attachments'),
		array('ManageAvatarSettings', 'area=manageattachments;sa=avatars'),
		array('ModifyCalendarSettings', 'area=managecalendar;sa=settings'),
		array('EditBoardSettings', 'area=manageboards;sa=settings'),
		array('ModifyMailSettings', 'area=mailqueue;sa=settings'),
		array('ModifyNewsSettings', 'area=news;sa=settings'),
		array('GeneralPermissionSettings', 'area=permissions;sa=settings'),
		array('ModifyPostSettings', 'area=postsettings;sa=posts'),
		array('ModifyBBCSettings', 'area=postsettings;sa=bbc'),
		array('ModifyTopicSettings', 'area=postsettings;sa=topics'),
		array('EditSearchSettings', 'area=managesearch;sa=settings'),
		array('EditSmileySettings', 'area=smileys;sa=settings'),
		array('ModifyOtherSettings', 'area=serversettings;sa=other'),
		array('ModifyCacheSettings', 'area=serversettings;sa=cache'),
	);

	foreach ($settings_search as $setting_area)
	{
		// Get a list of their variables.
		$config_vars = $setting_area[0](true);

		foreach ($config_vars as $var)
		{
			if (!empty($var[1]) && $var[0] != 'permissions')
				$search_data['settings'][] = array($var[1], $setting_area[1]);
		}
	}

	$context['search_results'] = array();
	$search_term = strtolower($context['search_term']);
	// Go through all the search data trying to find this text!
	foreach ($search_data as $section => $data)
	{
		foreach ($data as $item)
		{
			$found = false;
			if (!is_array($item[0]))
				$item[0] = array($item[0]);
			foreach ($item[0] as $term)
			{
				$lc_term = strtolower($term);
				if (strpos($lc_term, $search_term) !== false || (isset($txt[$term]) && strpos($txt[$term], $search_term) !== false) || (isset($txt['setting_' . $term]) && strpos($txt['setting_' . $term], $search_term) !== false))
				{
					$found = $term;
					break;
				}
			}

			if ($found)
			{
				// Format the name - and remove any descriptions the entry may have.
				$name = isset($txt[$found]) ? $txt[$found] : (isset($txt['setting_' . $found]) ? $txt['setting_' . $found] : $found);
				$name = preg_replace('~<(div|span)\sclass="smalltext">.+?</div>~', '', $name);

				$context['search_results'][] = array(
					'url' => (substr($item[1], 0, 4) == 'area' ? $scripturl . '?action=admin;' . $item[1] : $item[1]) . ';sc=' . $context['session_id'] . ((substr($item[1], 0, 4) == 'area' && $section == 'settings' ? '#' . $item[0][0] : '')),
					'name' => $name,
					'type' => $section,
					'help' => shorten_subject(isset($item[2]) ? $helptxt[$item2] : (isset($helptxt[$found]) ? $helptxt[$found] : ''), 255),
				);
			}
		}
	}
}

// All this does is pass through to manage members.
function AdminSearchMember()
{
	global $context, $sourcedir;

	require_once($sourcedir . '/ManageMembers.php');
	$_REQUEST['sa'] = 'query';

	$_POST['membername'] = $context['search_term'];

	ViewMembers();
}

// This file allows the user to search the SM online manual for a little of help.
function AdminSearchOM()
{
	global $context;

	$docsURL = 'docs.simplemachines.org';
	$context['doc_scripturl'] = 'http://docs.simplemachines.org/index.php';

	// Set all the parameters search might expect.
	$postVars = array(
		'search' => $context['search_term'],
	);

	// Encode the search data.
	foreach ($postVars as $k => $v)
		$postVars[$k] = urlencode($k) . '=' . urlencode($v);

	// This is what we will send.
	$postVars = implode('&', $postVars);

	// Open up a connection to the SM site.
	$fp = @fsockopen($docsURL, 80, $errno, $errstr);
	if ($fp)
	{
		$length = strlen($postVars);

		$out = "POST /index.php?action=search2&xml HTTP/1.1\r\n";
		$out .= "Host: " . $docsURL . "\r\n";
		$out .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$out .= "Content-Length: $length\r\n\r\n";
		$out .= "$postVars\r\n";
		$out .= "Connection: Close\r\n\r\n";
		fwrite($fp, $out);

		// What are we going to get back?
		$search_results = '';
		while (!feof($fp))
			$search_results .= fgets($fp, 128);

		fclose($fp);
	}
	else
	{
	  fatal_lang_error('cannot_connect_doc_site');
 	}

	// If we didn't get any xml back we are in trouble - perhaps the doc site is overloaded?
	if (preg_match('~<' . '\?xml\sversion="\d+\.\d+"\sencoding=".+?"\?' . '>\s*(<smf>.+?</smf>)~is', $search_results, $matches) != true)
		fatal_lang_error('cannot_connect_doc_site');

	$search_results = $matches[1];

	// Otherwise we simply walk through the XML and stick it in context for display.
	$context['search_results'] = array();
	loadClassFile('Class-Package.php');

	// Get the results loaded into an array for processing!
	$results = new xmlArray($search_results, false);

	// Move through the smf layer.
	if (!$results->exists('smf'))
		fatal_lang_error('cannot_connect_doc_site');
	$results = $results->path('smf[0]');

	// Are there actually some results?
	if (!$results->exists('noresults') && !$results->exists('results'))
		fatal_lang_error('cannot_connect_doc_site');
	elseif ($results->exists('results'))
	{
		foreach ($results->set('results/result') as $result)
		{
			if (!$result->exists('messages'))
				continue;

			$context['search_results'][$result->fetch('id')] = array(
				'topic_id' => $result->fetch('id'),
				'relevance' => $result->fetch('relevance'),
				'board' => array(
					'id' => $result->fetch('board/id'),
					'name' => $result->fetch('board/name'),
					'href' => $result->fetch('board/href'),
				),
				'category' => array(
					'id' => $result->fetch('category/id'),
					'name' => $result->fetch('category/name'),
					'href' => $result->fetch('category/href'),
				),
				'messages' => array(),
			);

			// Add the messages.
			foreach ($result->set('messages/message') as $message)
				$context['search_results'][$result->fetch('id')]['messages'][] = array(
					'id' => $message->fetch('id'),
					'subject' => $message->fetch('subject'),
					'body' => $message->fetch('body'),
					'time' => $message->fetch('time'),
					'timestamp' => $message->fetch('timestamp'),
					'start' => $message->fetch('start'),
					'author' => array(
						'id' => $message->fetch('author/id'),
						'name' => $message->fetch('author/name'),
						'href' => $message->fetch('author/href'),
					),
				);
  	}
 	}
}

?>