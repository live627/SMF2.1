<?php
/**********************************************************************************
* Admin.php                                                                       *
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
*/

// The main admin handling function.
function AdminMain()
{
	global $txt, $context, $scripturl, $sc, $modSettings, $user_info, $settings, $sourcedir;

	// Load the language and templates....
	loadLanguage('Admin');
	loadTemplate('Admin');

	// Ensure we are admin'ing.
	$context['bar_area'] = 'admin';

	// Note - format for section is 'key' => array(label, source_file, function_name, additional_url).
	// Create the admin side bar... start with 'Main'.
	$context['admin_areas']['forum'] = array(
		'title' => $txt['admin_main'],
		'areas' => array(
			'index' => array($txt['admin_center'], 'Admin.php', 'AdminHome'),
			'credits' => array($txt['support_credits_title'], 'Admin.php', 'AdminHome'),
			'version' => array('', 'Admin.php', 'VersionDetail', 'select' => 'index'),
			'copyright' => array('', 'Admin.php', 'ManageCopyright', 'select' => 'index'),
		),
	);

	if (allowedTo(array('edit_news', 'send_mail', 'admin_forum')))
		$context['admin_areas']['forum']['areas']['news'] = array($txt['news_title'], 'ManageNews.php', 'ManageNews');

	if (allowedTo('admin_forum'))
	{
		$context['admin_areas']['forum']['areas']['packages'] =  array($txt['package'], 'Packages.php', 'Packages');
		$context['admin_areas']['forum']['areas']['cleanperms'] =  array('', 'Admin.php', 'CleanupPermissions', 'select' => 'packages');
	}

	// Admin area 'Configuration'.
	if (allowedTo('admin_forum'))
	{
		$context['admin_areas']['config'] = array(
			'title' => $txt['admin_config'],
			'areas' => array(
				'featuresettings' => array($txt['modSettings_title'], 'ModSettings.php', 'ModifyFeatureSettings'),
				'serversettings' => array($txt['admin_server_settings'], 'ManageServer.php', 'ModifySettings'),
				'current_theme' => array($txt['theme_current_settings'], 'Themes.php', 'ThemesMain', $scripturl . '?action=admin;area=theme;sa=settings;th=' . $settings['theme_id']),
				'theme' => array($txt['theme_admin'], 'Themes.php', 'ThemesMain', $scripturl . '?action=admin;area=theme;sa=admin'),
			),
		);
	}

	// Admin area 'Forum'.
	if (allowedTo(array('manage_boards', 'admin_forum', 'manage_smileys', 'manage_attachments', 'moderate_forum')))
	{
		$context['admin_areas']['layout'] = array(
			'title' => $txt['layout_controls'],
			'areas' => array()
		);

		if (allowedTo('manage_boards'))
			$context['admin_areas']['layout']['areas']['manageboards'] =  array($txt['admin_boards'], 'ManageBoards.php', 'ManageBoards');

		if (allowedTo(array('admin_forum', 'moderate_forum')))
			$context['admin_areas']['layout']['areas']['postsettings'] = array($txt['manageposts'], 'ManagePosts.php', 'ManagePostSettings');
		if (allowedTo('admin_forum'))
		{
			$context['admin_areas']['layout']['areas']['managecalendar'] = array($txt['manage_calendar'], 'ManageCalendar.php', 'ManageCalendar');
			$context['admin_areas']['layout']['areas']['managesearch'] = array($txt['manage_search'], 'ManageSearch.php', 'ManageSearch');
		}
		if (allowedTo('manage_smileys'))
			$context['admin_areas']['layout']['areas']['smileys'] = array($txt['smileys_manage'], 'ManageSmileys.php', 'ManageSmileys');

		if (allowedTo('manage_attachments'))
			$context['admin_areas']['layout']['areas']['manageattachments'] = array($txt['attachments_avatars'], 'ManageAttachments.php', 'ManageAttachments');
	}

	// Admin area 'Members'.
	if (allowedTo(array('moderate_forum', 'manage_membergroups', 'manage_bans', 'manage_permissions', 'admin_forum')))
	{
		$context['admin_areas']['members'] = array(
			'title' => $txt[426],
			'areas' => array()
		);

		if (allowedTo('moderate_forum'))
			$context['admin_areas']['members']['areas']['viewmembers'] = array($txt['admin_users'], 'ManageMembers.php', 'ViewMembers');

		if (allowedTo('manage_membergroups'))
			$context['admin_areas']['members']['areas']['membergroups'] = array($txt['admin_groups'], 'ManageMembergroups.php', 'ModifyMembergroups');

		if (allowedTo('manage_permissions'))
			$context['admin_areas']['members']['areas']['permissions'] = array($txt['edit_permissions'], 'ManagePermissions.php', 'ModifyPermissions');

		if (allowedTo(array('admin_forum', 'moderate_forum')))
			$context['admin_areas']['members']['areas']['regcenter'] = array($txt['registration_center'], 'ManageRegistration.php', 'RegCenter');

		if (allowedTo('manage_bans'))
			$context['admin_areas']['members']['areas']['ban'] = array($txt['ban_title'], 'ManageBans.php', 'Ban');
	}

	// Admin area 'Maintenance Controls'.
	if (allowedTo('admin_forum'))
	{
		$context['admin_areas']['maintenance'] = array(
			'title' => $txt['admin_maintenace'],
			'areas' => array(
				'mailqueue' => array($txt['mailqueue_title'], 'ManageMail.php', 'ManageMail'),
				'maintain' => array($txt['maintain_title'], 'ManageMaintenance.php', 'ManageMaintenance'),
				'reports' => array($txt['generate_reports'], 'Reports.php', 'ReportsMain'),
				'errorlog' => array($txt['errlog'], 'ManageErrors.php', 'ViewErrorLog', $scripturl . '?action=admin;area=errorlog;desc'),
				'dumpdb' => array('', 'DumpDatabase.php', 'DumpDatabase2', 'select' => 'maintain'),
				'repairboards' => array('', 'RepairBoards.php', 'RepairBoards', 'select' => 'maintain'),
			),
		);
	}

	// Make sure the administrator has a valid session...
	validateSession();

	// Figure out which one we're in now...
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
		$context['admin_section'] = 'forum';
	}

	// obExit will know what to do!
	$context['template_layers'][] = 'admin';

	// Now - finally - call the right place!
	require_once($sourcedir . '/' . $context['admin_areas'][$context['admin_section']]['areas'][$actual_area][1]);
	$context['admin_areas'][$context['admin_section']]['areas'][$actual_area][2]();
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

?>