<?php
/**********************************************************************************
* Admin.php                                                                       *
***********************************************************************************
* SMF: Simple Machines Forum                                                      *
* Open-Source Project Inspired by Zef Hemel (zef@zefhemel.com)                    *
* =============================================================================== *
* Software Version:           SMF 2.0 Beta 2                                       *
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
	global $txt, $context, $scripturl, $sc, $modSettings, $user_info, $settings, $sourcedir, $options, $smfFunc;

	// Load the language and templates....
	loadLanguage('Admin');
	loadTemplate('Admin');

	// We have our own special stylesheet for admin like stuff.
	$context['html_headers'] .= '<link rel="stylesheet" type="text/css" href="' . $settings['default_theme_url'] . '/css/admin.css" />';

	// The admin centre uses PNG sometimes.
	$context['html_headers'] .= '
		<!--[if lt IE 7]>
		<script defer type="text/javascript" src="' . $settings['default_theme_url'] . '/scripts/pngfix.js"></script>
		<![endif]-->';

	require_once($sourcedir . '/Subs-Menu.php');

	// Some preferences.
	$context['admin_preferences'] = !empty($options['admin_preferences']) ? unserialize($options['admin_preferences']) : array();

	// Define all the menu structure - see Subs-Menu.php for details!
	$admin_areas = array(
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
					'permission' => array('admin_forum'),
					'icon' => 'packages.gif',
					'subsections' => array(
						'browse' => array($txt['browse_packages']),
						'packageget' => array($txt['download_packages'], 'url' => $scripturl . '?action=admin;area=packages;get'),
						'installed' => array($txt['installed_packages']),
						'options' => array($txt['package_settings']),
					),
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
					'file' => 'ManageSettings.php',
					'function' => 'ModifyFeatureSettings',
					'icon' => 'features.gif',
					'subsections' => array(
						'core' => array($txt['core_settings_title']),
						'basic' => array($txt['mods_cat_features']),
						'security' => array($txt['mods_cat_security']),
						'layout' => array($txt['mods_cat_layout']),
						'karma' => array($txt['karma'], 'enabled' => in_array('k', $context['admin_features'])),
						'moderation' => array($txt['moderation_settings_short'], 'enabled' => substr($modSettings['warning_settings'], 0, 1) == 1),
						'sig' => array($txt['signature_settings_short']),
						'profile' => array($txt['custom_profile_shorttitle'], 'enabled' => in_array('cp', $context['admin_features'])),
						'pruning' => array($txt['pruning_title']),
					),
				),
				'serversettings' => array(
					'label' => $txt['admin_server_settings'],
					'file' => 'ManageServer.php',
					'function' => 'ModifySettings',
					'icon' => 'server.gif',
					'subsections' => array(
						'core' => array($txt['core_configuration']),
						'other' => array($txt['other_configuration']),
						'languages' => array($txt['language_configuration']),
						'cache' => array($txt['caching_settings']),
					),
				),
				'current_theme' => array(
					'label' => $txt['theme_current_settings'],
					'file' => 'Themes.php',
					'function' => 'ThemesMain',
					'custom_url' => $scripturl . '?action=admin;area=theme;sa=settings;th=' . $settings['theme_id'],
					'icon' => 'current_theme.gif',
					'subsections' => array(
						'admin' => array($txt['themeadmin_admin_title']),
						'list' => array($txt['themeadmin_list_title']),
						'reset' => array($txt['themeadmin_reset_title']),
						'edit' => array($txt['themeadmin_edit_title']),
					),
				),
				'theme' => array(
					'label' => $txt['theme_admin'],
					'file' => 'Themes.php',
					'function' => 'ThemesMain',
					'custom_url' => $scripturl . '?action=admin;area=theme;sa=admin',
					'icon' => 'themes.gif',
					'subsections' => array(
						'admin' => array($txt['themeadmin_admin_title']),
						'list' => array($txt['themeadmin_list_title']),
						'reset' => array($txt['themeadmin_reset_title']),
						'edit' => array($txt['themeadmin_edit_title']),
					),
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
					'subsections' => array(
						'main' => array($txt['boardsEdit']),
						'newcat' => array($txt['mboards_new_cat']),
						'settings' => array($txt['settings'], 'admin_forum'),
					),
				),
				'postsettings' => array(
					'label' => $txt['manageposts'],
					'file' => 'ManagePosts.php',
					'function' => 'ManagePostSettings',
					'permission' => array('admin_forum', 'moderate_forum'),
					'subsections' => array(
						'posts' => array($txt['manageposts_settings'], 'admin_forum'),
						'bbc' => array($txt['manageposts_bbc_settings'], 'admin_forum'),
						'censor' => array($txt['admin_censored_words'], 'moderate_forum'),
						'topics' => array($txt['manageposts_topic_settings'], 'admin_forum'),
					),
				),
				'managecalendar' => array(
					'label' => $txt['manage_calendar'],
					'file' => 'ManageCalendar.php',
					'function' => 'ManageCalendar',
					'permission' => array('admin_forum'),
					'enabled' => in_array('cd', $context['admin_features']),
					'subsections' => array(
						'holidays' => array($txt['manage_holidays'], 'admin_forum', 'enabled' => !empty($modSettings['cal_enabled'])),
						'settings' => array($txt['calendar_settings'], 'admin_forum'),
					),
				),
				'managesearch' => array(
					'label' => $txt['manage_search'],
					'file' => 'ManageSearch.php',
					'function' => 'ManageSearch',
					'permission' => array('admin_forum'),
					'subsections' => array(
						'weights' => array($txt['search_weights']),
						'method' => array($txt['search_method']),
						'settings' => array($txt['settings']),
					),
				),
				'smileys' => array(
					'label' => $txt['smileys_manage'],
					'file' => 'ManageSmileys.php',
					'function' => 'ManageSmileys',
					'permission' => array('manage_smileys'),
					'subsections' => array(
						'editsets' => array($txt['smiley_sets']),
						'addsmiley' => array($txt['smileys_add'], 'enabled' => !empty($modSettings['smiley_enable'])),
						'editsmileys' => array($txt['smileys_edit'], 'enabled' => !empty($modSettings['smiley_enable'])),
						'setorder' => array($txt['smileys_set_order'], 'enabled' => !empty($modSettings['smiley_enable'])),
						'editicons' => array($txt['icons_edit_message_icons'], 'enabled' => !empty($modSettings['messageIcons_enable'])),
						'settings' => array($txt['settings']),
					),
				),
				'manageattachments' => array(
					'label' => $txt['attachments_avatars'],
					'file' => 'ManageAttachments.php',
					'function' => 'ManageAttachments',
					'permission' => array('manage_attachments'),
					'subsections' => array(
						'browse' => array($txt['attachment_manager_browse']),
						'attachments' => array($txt['attachment_manager_settings']),
						'avatars' => array($txt['attachment_manager_avatar_settings']),
						'maintenance' => array($txt['attachment_manager_maintenance']),
					),
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
					'subsections' => array(
						'all' => array($txt['view_all_members']),
						'search' => array($txt['mlist_search']),
					),
				),
				'membergroups' => array(
					'label' => $txt['admin_groups'],
					'file' => 'ManageMembergroups.php',
					'function' => 'ModifyMembergroups',
					'permission' => array('manage_members'),
					'subsections' => array(
						'index' => array($txt['membergroups_edit_groups'], 'manage_membergroups'),
						'add' => array($txt['membergroups_new_group'], 'manage_membergroups'),
						'settings' => array($txt['settings'], 'admin_forum'),
					),
				),
				'permissions' => array(
					'label' => $txt['edit_permissions'],
					'file' => 'ManagePermissions.php',
					'function' => 'ModifyPermissions',
					'permission' => array('manage_permissions'),
					'subsections' => array(
						'index' => array($txt['permissions_groups'], 'manage_permissions'),
						'board' => array($txt['permissions_boards'], 'manage_permissions'),
						'profiles' => array($txt['permissions_profiles'], 'manage_permissions'),
						'postmod' => array($txt['permissions_post_moderation'], 'manage_permissions', 'enabled' => in_array('pm', $context['admin_features'])),
						'settings' => array($txt['settings'], 'admin_forum'),
					),
				),
				'regcenter' => array(
					'label' => $txt['registration_center'],
					'file' => 'ManageRegistration.php',
					'function' => 'RegCenter',
					'permission' => array('admin_forum', 'moderate_forum'),
					'subsections' => array(
						'register' => array($txt['admin_browse_register_new'], 'moderate_forum'),
						'agreement' => array($txt['registration_agreement'], 'admin_forum'),
						'reservednames' => array($txt['admin_reserved_set'], 'admin_forum'),
						'settings' => array($txt['settings'], 'admin_forum'),
					),
				),
				'ban' => array(
					'label' => $txt['ban_title'],
					'file' => 'ManageBans.php',
					'function' => 'Ban',
					'permission' => 'manage_bans',
					'subsections' => array(
						'list' => array($txt['ban_edit_list']),
						'add' => array($txt['ban_add_new']),
						'browse' => array($txt['ban_trigger_browse']),
						'log' => array($txt['ban_log']),
					),
				),
				'paidsubscribe' => array(
					'label' => $txt['paid_subscriptions'],
					'enabled' => in_array('ps', $context['admin_features']),
					'file' => 'ManagePaid.php',
					'function' => 'ManagePaidSubscriptions',
					'permission' => 'admin_forum',
					'subsections' => array(
						'view' => array($txt['paid_subs_view']),
						'settings' => array($txt['settings']),
					),
				),
				'sengines' => array(
					'label' => $txt['search_engines'],
					'enabled' => in_array('sp', $context['admin_features']),
					'file' => 'ManageSearchEngines.php',
					'function' => 'SearchEngines',
					'permission' => 'admin_forum',
					'subsections' => array(
						'stats' => array($txt['spider_stats']),
						'logs' => array($txt['spider_logs']),
						'spiders' => array($txt['spiders']),
						'settings' => array($txt['settings']),
					),
				),
			),
		),
		'maintenance' => array(
			'title' => $txt['admin_maintenance'],
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
					'enabled' => in_array('rg', $context['admin_features']),
					'label' => $txt['generate_reports'],
					'file' => 'Reports.php',
					'function' => 'ReportsMain',
				),
				'logs' => array(
					'label' => $txt['logs'],
					'function' => 'AdminLogs',
					'subsections' => array(
						'errorlog' => array($txt['errlog'], 'admin_forum'),
						'adminlog' => array($txt['admin_log'], 'admin_forum', 'enabled' => in_array('ml', $context['admin_features'])),
						'modlog' => array($txt['moderation_log'], 'admin_forum', 'enabled' => in_array('ml', $context['admin_features'])),
					),
				),
				'repairboards' => array(
					'file' => 'RepairBoards.php',
					'function' => 'RepairBoards',
					'select' => 'maintain',
				),
			),
		),
	);

	$menuOptions = array();

	// Add a work around for editing current theme.
	if (isset($_GET['area']) && $_GET['area'] == 'theme' && isset($_GET['th']) && $_GET['th'] == $settings['theme_id'])
		$menuOptions['current_area'] = 'current_theme';

	// Make sure the administrator has a valid session...
	validateSession();

	// Actually create the menu!
	$admin_include_data = createMenu($admin_areas, $menuOptions);
	unset($admin_areas);

	// Make a note of the Unique ID for this menu.
	$context['admin_menu_id'] = $context['max_menu_id'];
	$context['admin_menu_name'] = 'menu_data_' . $context['admin_menu_id'];

	// Nothing valid?
	if ($admin_include_data == false)
		fatal_lang_error('no_access');

	// Why on the admin are we?
	$context['admin_area'] = $admin_include_data['current_area'];

	// Now - finally - call the right place!
	if (isset($admin_include_data['file']))
		require_once($sourcedir . '/' . $admin_include_data['file']);

	$admin_include_data['function']();
}

// The main administration section.
function AdminHome()
{
	global $sourcedir, $forum_version, $txt, $scripturl, $context, $user_info, $boardurl, $modSettings, $smfFunc;

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
<i>Simple Machines wants to thank everyone who helped make SMF 2.0 what it is today; shaping and directing our project, all through the thick and the thin. It wouldn\'t have been possible without you.</i><br />
<div style="margin-top: 1ex;"><i>This includes our users and especially Charter Members - thanks for installing and using our software as well as providing valuable feedback, bug reports, and opinions.</i></div>
<div style="margin-top: 2ex;"><b>Project Managers:</b> Amacythe, David Recordon, Joseph Fung, and Jeff Lewis.</div>
<div style="margin-top: 1ex;"><b>Developers:</b> Hendrik Jan &quot;Compuart&quot; Visser, Matt &quot;Grudge&quot; Wolf, Michael &quot;Thantos&quot; Miller, Theodore &quot;Orstio&quot; Hildebrandt, and Unknown W. &quot;[Unknown]&quot; Brackets</div>
<div style="margin-top: 1ex;"><b>Support Specialists:</b> Ben Scott, Michael &quot;Oldiesmann&quot; Eshom, Jan-Olof &quot;Owdy&quot; Eriksson, A&auml;ron van Geffen, Alexandre &quot;Ap2&quot; Patenaude, Andrea Hubacher, Chris Cromer, [darksteel], dtm.exe, Nick &quot;Fizzy&quot; Dyer, Horseman, Huw Ayling-Miller, jerm, Justyne, kegobeer, Kindred, Matthew &quot;Mattitude&quot; Hall, Mediman, Metho, Omar Bazavilvazo, Pitti, redone, Tomer &quot;Lamper&quot; Dean, Tony, and xenovanis.</div>
<div style="margin-top: 1ex;"><b>Mod Developers:</b> snork13, Cristi&aacute;n &quot;Anguz&quot; L&aacute;vaque, Goosemoose, Jack.R.Abbit, James &quot;Cheschire&quot; Yarbro, Jesse &quot;Gobalopper&quot; Reid, Juan &quot;JayBachatero&quot; Hernandez, Kirby, vbgamer45, and winrules.</div>
<div style="margin-top: 1ex;"><b>Documentation Writers:</b> akabugeyes, eldacar, Gary M. &quot;AwwLilMaggie&quot; Gadsdon, Jerry, and Nave.</div>
<div style="margin-top: 1ex;"><b>Language Coordinators:</b> Daniel Diehl.</div>
<div style="margin-top: 1ex;"><b>Graphic Designers:</b> Bjoern &quot;Bloc&quot; Kristiansen, Alienine (Adrian), A.M.A, babylonking, BlackouT, Burpee, diplomat, Eren &quot;forsakenlad&quot; Yasarkurt, Hyper Piranha, Killer Possum, Mystica, Nico &quot;aliencowfarm&quot; Boer, Philip &quot;Meriadoc&quot; Renich and Tippmaster.</div>
<div style="margin-top: 1ex;"><b>Site team:</b> dschwab9 and Tim.</div>
<div style="margin-top: 1ex;"><b>Marketing:</b> Douglas &quot;The Bear&quot; Hazard, RickC and Trekkie101.</div>
<div style="margin-top: 1ex;"><b>Translators:</b> Thank you for your efforts which make it possible for people all around the world to use SMF.</div>
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
			$fp = @fsockopen('www.simplemachines.org', 80, $errno, $errstr, 1);
			if ($fp)
			{
				$out = 'GET /smf/copyright/check_copyright.php?site=' . base64_encode($boardurl) . '&key=' . $key . '&version=' . base64_encode($forum_version) . ' HTTP/1.1' . "\r\n";
				$out .= 'Host: www.simplemachines.org' . "\r\n";
				$out .= 'Connection: Close' . "\r\n\r\n";
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
					$smfFunc['db_query']('', '
						DELETE FROM {db_prefix}settings
						WHERE variable = {string:copy_settings}
							OR variable = {string:copyright_key}',
						array(
							'copy_settings' => 'copy_settings',
							'copyright_key' => 'copyright_key',
						)
					);
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
		'include_subscriptions' => true,
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
		$fp = @fsockopen('www.simplemachines.org', 80, $errno, $errstr);
		if ($fp)
		{
			$out = 'GET /smf/copyright/check_copyright.php?site=' . base64_encode($boardurl) . '&key=' . $_POST['copy_code'] . '&version=' . base64_encode($forum_version) . ' HTTP/1.1' . "\r\n";
			$out .= 'Host: www.simplemachines.org' . "\r\n";
			$out .= 'Connection: Close' . "\r\n\r\n";
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
	global $boarddir, $sourcedir, $scripturl, $package_ftp, $context, $txt, $modSettings;

	isAllowedTo('admin_forum');
	umask(0);

	loadTemplate('Packages');
	loadLanguage('Packages');

	if (!isset($_REQUEST['perm_type']) || !in_array($_REQUEST['perm_type'], array('free', 'restrictive', 'standard')))
		$_REQUEST['perm_type'] = 'free';

	checkSession();

	// Make sure the user gets the right description.
	$context[$context['admin_menu_name']]['current_subsection'] = 'options';
	$context[$context['admin_menu_name']]['tab_data'] = array(
		'title' => &$txt['package_manager'],
		'description' => $txt['package_manager_desc'],
		'tabs' => array(
			'browse' => array(),
			'packageget' => array(),
			'installed' => array(),
			'options' => array(
				'description' => $txt['package_install_options_ftp_why'],
			),
		),
	);

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
	global $context, $modSettings, $smfFunc;

	$request = $smfFunc['db_query']('', '
		SELECT data, filetype
		FROM {db_prefix}admin_info_files
		WHERE filename = {string:current_filename}
		LIMIT 1',
		array(
			'current_filename' => $_REQUEST['filename'],
		)
	);

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
	$context['search_term'] = $_REQUEST['search_term'];

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
		'Help', 'ManageMail', 'ManageSettings', 'ManageCalendar', 'ManageBoards', 'ManagePaid', 'ManagePermissions', 'Search',
		'ManageSmileys',
	);
	loadLanguage(implode('+', $language_files));

	// All the files we need to include.
	$include_files = array(
		'ManageSettings', 'ManageBoards', 'ManageNews', 'ManageAttachments', 'ManageCalendar', 'ManageMail', 'ManagePaid', 'ManagePermissions',
		'ManagePosts', 'ManageSearch', 'ManageSearchEngines', 'ManageServer', 'ManageSmileys',
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
	foreach ($context[$context['admin_menu_name']]['sections'] as $section)
	{
		foreach ($section['areas'] as $menu_key => $menu_item)
		{
			$search_data['sections'][] = array($menu_item['label'], 'area=' . $menu_key);
			if (!empty($menu_item['subsections']))
				foreach ($menu_item['subsections'] as $key => $sublabel)
				{
					if (isset($sublabel['label']))
						$search_data['sections'][] = array($sublabel['label'], 'area=' . $menu_key . ';sa=' . $key);
				}
		}
	}

	// This is a special array of functions that contain setting data - we query all these to simply pull all setting bits!
	$settings_search = array(
		array('ModifyCoreFeatures', 'area=featuresettings;sa=core'),
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
		array('ManageSearchEngineSettings', 'area=sengines;sa=settings'),
		array('ModifySubscriptionSettings', 'area=paidsubscribe;sa=settings'),
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
	global $context, $sourcedir;

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

	// Get the results from the doc site.
	require_once($sourcedir . '/Subs-Package.php');
	$search_results = fetch_web_data($context['doc_scripturl'] . '?action=search2&xml', $postVars);

	// If we didn't get any xml back we are in trouble - perhaps the doc site is overloaded?
	if (!$search_results || preg_match('~<' . '\?xml\sversion="\d+\.\d+"\sencoding=".+?"\?' . '>\s*(<smf>.+?</smf>)~is', $search_results, $matches) != true)
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

// This function decides which log to load.
function AdminLogs()
{
	global $sourcedir, $context, $txt, $scripturl;

	// These are the logs they can load.
	$log_functions = array(
		'errorlog' => array('ManageErrors.php', 'ViewErrorLog'),
		'adminlog' => array('Modlog.php', 'ViewModlog'),
		'modlog' => array('Modlog.php', 'ViewModlog'),
	);

	$sub_action = isset($_REQUEST['sa']) && isset($log_functions[$_REQUEST['sa']]) ? $_REQUEST['sa'] : 'errorlog';
	// If it's not got a sa set it must have come here for first time, pretend error log should be reversed.
	if (!isset($_REQUEST['sa']))
		$_REQUEST['desc'] = true;

	// Setup some tab stuff.
	$context[$context['admin_menu_name']]['tab_data'] = array(
		'title' => &$txt['logs'],
		'help' => '',
		'description' => $txt['maintain_info'],
		'tabs' => array(
			'errorlog' => array(
				'url' => $scripturl . '?action=admin;area=logs;sa=errorlog;desc',
				'description' => sprintf($txt['errlog_desc'], $txt['remove']),
			),
			'adminlog' => array(
				'description' => $txt['admin_log_desc'],
			),
			'modlog' => array(
				'description' => $txt['moderation_log_desc'],
			),
		),
	);

	require_once($sourcedir . '/' . $log_functions[$sub_action][0]);
	$log_functions[$sub_action][1]();
}

?>