<?php
/******************************************************************************
* Packages.php                                                                *
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

/* // !!!

	void Packages()
		// !!!

	void PackageInstallTest()
		// !!!

	void PackageInstall()
		// !!!

	void PackageList()
		// !!!

	void ExamineFile()
		// !!!

	void InstalledList()
		// !!!

	void FlushInstall()
		// !!!

	void PackageRemove()
		// !!!

	void PackageBrowse()
		// !!!

	void PackageOptions()
		// !!!
*/

// This is the notoriously defunct package manager..... :/.
function Packages()
{
	global $txt, $scripturl, $sourcedir, $context;

	//!!! Remove this!
	if (isset($_GET['get']) || isset($_GET['pgdownload']))
	{
		require_once($sourcedir . '/PackageGet.php');
		return PackageGet();
	}

	isAllowedTo('admin_forum');

	// Load all the basic stuff.
	require_once($sourcedir . '/Subs-Package.php');
	loadLanguage('Packages');
	loadTemplate('Packages');

	// Set up the linktree and title so it's already done.
	$context['linktree'][] = array(
		'url' => $scripturl . '?action=admin;area=packages',
		'name' => &$txt['package1']
	);
	$context['page_title'] = $txt['package1'];

	// Delegation makes the world... that is, the package manager go 'round.
	$subActions = array(
		'browse' => 'PackageBrowse',
		'remove' => 'PackageRemove',
		'list' => 'PackageList',
		'install' => 'PackageInstallTest',
		'install2' => 'PackageInstall',
		'uninstall' => 'PackageInstallTest',
		'uninstall2' => 'PackageInstall',
		'installed' => 'InstalledList',
		'options' => 'PackageOptions',
		'flush' => 'FlushInstall',
		'examine' => 'ExamineFile'
	);

	// Work out exactly who it is we are calling.
	if (isset($_REQUEST['sa']) && isset($subActions[$_REQUEST['sa']]))
		$context['sub_action'] = $_REQUEST['sa'];
	else
		$context['sub_action'] = 'browse';

	// Set up some tabs...
	$context['admin_tabs'] = array(
		'title' => &$txt['package1'],
		// !!! 'help' => 'registrations',
		'description' => $txt['package_manager_desc'],
		'tabs' => array(
			'browse' => array(
				'title' => $txt['package3'],
				'href' => $scripturl . '?action=admin;area=packages;sa=browse',
			),
			'packageget' => array(
				'title' => $txt['download_packages'],
				'description' => $txt['download_packages_desc'],
				'href' => $scripturl . '?action=admin;area=packages;get',
			),
			'installed' => array(
				'title' => $txt['installed_packages'],
				'description' => $txt['installed_packages_desc'],
				'href' => $scripturl . '?action=admin;area=packages;sa=installed',
			),
			'options' => array(
				'title' => $txt['package_settings'],
				'description' => $txt['package_install_options_ftp_why'],
				'href' => $scripturl . '?action=admin;area=packages;sa=options',
				'is_last' => true,
			),
		),
	);

	// Attempt to automatically select the right tab.
	if (isset($context['admin_tabs']['tabs'][$context['sub_action']]))
		$context['admin_tabs']['tabs'][$context['sub_action']]['is_selected'] = true;
	// Otherwise it's going to be the browse anyway...
	else
		$context['admin_tabs']['tabs']['browse']['is_selected'] = true;

	// Call the function we're handing control to.
	$subActions[$context['sub_action']]();
}

// Test install a package.
function PackageInstallTest()
{
	global $boarddir, $txt, $context, $scripturl, $sourcedir, $modSettings, $db_prefix, $smfFunc;

	// You have to specify a file!!
	if (!isset($_REQUEST['package']) || $_REQUEST['package'] == '')
		redirectexit('action=admin;area=packages');
	$context['filename'] = preg_replace('~[\.]+~', '.', $_REQUEST['package']);

	// Do we have an existing id, for uninstalls and the like.
	$context['install_id'] = isset($_REQUEST['pid']) ? (int) $_REQUEST['pid'] : 0;

	require_once($sourcedir . '/Subs-Package.php');

	// Load up the package FTP information?
	if (isset($_SESSION['pack_ftp']))
		packageRequireFTP($scripturl . '?action=admin;area=packages;sa=' . $_REQUEST['sa'] . ';package=' . $_REQUEST['package']);

	// Make sure temp directory exists and is empty.
	if (file_exists($boarddir . '/Packages/temp'))
		deltree($boarddir . '/Packages/temp', false);

	if (!mktree($boarddir . '/Packages/temp', 0755))
	{
		deltree($boarddir . '/Packages/temp', false);
		if (!mktree($boarddir . '/Packages/temp', 0777))
		{
			deltree($boarddir . '/Packages/temp', false);
			packageRequireFTP($scripturl . '?action=admin;area=packages;sa=' . $_REQUEST['sa'] . ';package=' . $_REQUEST['package'], array($boarddir . '/Packages/temp/delme.tmp'));

			deltree($boarddir . '/Packages/temp', false);
			if (!mktree($boarddir . '/Packages/temp', 0777))
				fatal_lang_error('package_cant_download', false);
		}
	}

	$context['uninstalling'] = $_REQUEST['sa'] == 'uninstall';

	// Set up the linktree...
	$context['linktree'][] = array(
		'url' => $scripturl . '?action=admin;area=packages;sa=browse',
		'name' => $context['uninstalling'] ? $txt['package_uninstall_actions'] : $txt['package42']
	);
	$context['page_title'] .= ' - ' . ($context['uninstalling'] ? $txt['package_uninstall_actions'] : $txt['package42']);

	$context['sub_template'] = 'view_package';

	if (!file_exists($boarddir . '/Packages/' . $context['filename']))
	{
		deltree($boarddir . '/Packages/temp');
		fatal_lang_error('package_no_file', false);
	}

	// Extract the files so we can get things like the readme, etc.
	if (is_file($boarddir . '/Packages/' . $context['filename']))
	{
		$context['extracted_files'] = read_tgz_file($boarddir . '/Packages/' . $context['filename'], $boarddir . '/Packages/temp');

		if ($context['extracted_files'] && !file_exists($boarddir . '/Packages/temp/package-info.xml'))
			foreach ($context['extracted_files'] as $file)
				if (basename($file['filename']) == 'package-info.xml')
				{
					$context['base_path'] = dirname($file['filename']) . '/';
					break;
				}

		if (!isset($context['base_path']))
			$context['base_path'] = '';
	}
	elseif (is_dir($boarddir . '/Packages/' . $context['filename']))
	{
		copytree($boarddir . '/Packages/' . $context['filename'], $boarddir . '/Packages/temp');
		$context['extracted_files'] = listtree($boarddir . '/Packages/temp');
		$context['base_path'] = '';
	}
	else
		fatal_lang_error(1, false);

	// Load up any custom themes we may want to install into...
	$request = $smfFunc['db_query']('', "
		SELECT id_theme, variable, value
		FROM {$db_prefix}themes
		WHERE (id_theme = 1 OR id_theme IN ($modSettings[knownThemes]))
			AND variable IN ('name', 'theme_dir')", __FILE__, __LINE__);
	$theme_paths = array();
	while ($row = $smfFunc['db_fetch_assoc']($request))
	{
		$theme_paths[$row['id_theme']][$row['variable']] = $row['value'];
	}
	$smfFunc['db_free_result']($request);

	// Get the package info...
	$packageInfo = getPackageInfo($context['filename']);
	$packageInfo['filename'] = $context['filename'];
	$context['package_name'] = isset($packageInfo['name']) ? $packageInfo['name'] : $context['filename'];

	// Set the type of extraction...
	$context['extract_type'] = isset($packageInfo['type']) ? $packageInfo['type'] : 'modification';

	// The mod isn't installed.... unless proven otherwise.
	$context['is_installed'] = false;

	// See if it is installed?
	$request = $smfFunc['db_query']('', "
		SELECT version, themes_installed
		FROM {$db_prefix}log_packages
		WHERE package_id = '" . addslashes($packageInfo['id']) . "'
			AND install_state = 1", __FILE__, __LINE__);
	while ($row = $smfFunc['db_fetch_assoc']($request))
	{
		$old_themes = explode(',', $row['themes_installed']);
		$old_version = $row['version'];
	}
	$smfFunc['db_free_result']($request);

	// Wait, it's not installed yet!
	if (!isset($old_version) && $context['uninstalling'])
	{
		deltree($boarddir . '/Packages/temp');
		fatal_lang_error('package_cant_uninstall', false);
	}
	// Uninstalling?
	elseif ($context['uninstalling'])
	{
		$actions = parsePackageInfo($packageInfo['xml'], true, 'uninstall');

		// Gadzooks!  There's no uninstaller at all!?
		if (empty($actions))
		{
			deltree($boarddir . '/Packages/temp');
			fatal_lang_error('package_uninstall_cannot', false);
		}

		// Can't edit the custom themes it's edited if you're unisntalling, they must be removed.
		$context['themes_locked'] = true;

		// Only let them uninstall themes it was installed into.
		foreach ($theme_paths as $id => $data)
			if ($id != 1 && !in_array($id, $old_themes))
				unset($theme_paths[$id]);
	}
	elseif (isset($old_version) && $old_version != $packageInfo['version'])
	{
		// Look for an upgrade...
		$actions = parsePackageInfo($packageInfo['xml'], true, 'upgrade', $old_version);

		// There was no upgrade....
		if (empty($actions))
			$context['is_installed'] = true;
		else
		{
			// Otherwise they can only upgrade themes from the first time around.
			foreach ($theme_paths as $id => $data)
				if ($id != 1 && !in_array($id, $old_themes))
					unset($theme_paths[$id]);
		}
	}
	elseif (isset($old_version) && $old_version == $packageInfo['version'])
		$context['is_installed'] = true;

	if (!isset($old_version) || $context['is_installed'])
		$actions = parsePackageInfo($packageInfo['xml'], true, 'install');

	$context['actions'] = array();
	$context['ftp_needed'] = false;
	$context['has_failure'] = false;
	$chmod_files = array();

	if (empty($actions))
		return;

	foreach ($actions as $action)
	{
		if ($action['type'] == 'chmod')
		{
			$context['ftp_needed'] = true;
			$chmod_files[] = $action['filename'];
			continue;
		}
		elseif ($action['type'] == 'readme')
		{
			if (file_exists($boarddir . '/Packages/temp/' . $context['base_path'] . $action['filename']))
				$context['package_readme'] = htmlspecialchars(trim(file_get_contents($boarddir . '/Packages/temp/' . $context['base_path'] . $action['filename']), "\n\r"));
			elseif (file_exists($action['filename']))
				$context['package_readme'] = htmlspecialchars(trim(file_get_contents($action['filename']), "\n\r"));

			if (!empty($action['parse_bbc']))
				$context['package_readme'] = parse_bbc($context['package_readme']);
			else
				$context['package_readme'] = nl2br($context['package_readme']);

			continue;
		}
		elseif ($action['type'] == 'error')
			$context['has_failure'] = true;
		elseif ($action['type'] == 'modification')
		{
			if (!file_exists($boarddir . '/Packages/temp/' . $context['base_path'] . $action['filename']))
			{
				$context['has_failure'] = true;

				$context['actions'][] = array(
					'type' => $txt['package56'],
					'action' => strtr($action['filename'], array($boarddir => '.')),
					'description' => $txt['package_action_error']
				);
			}

			if ($action['boardmod'])
				$mod_actions = parseBoardMod(@file_get_contents($boarddir . '/Packages/temp/' . $context['base_path'] . $action['filename']), true, $action['reverse'], $theme_paths);
			else
				$mod_actions = parseModification(@file_get_contents($boarddir . '/Packages/temp/' . $context['base_path'] . $action['filename']), true, $action['reverse'], $theme_paths);

			foreach ($mod_actions as $mod_action)
			{
				if ($mod_action['type'] == 'opened')
					$failed = false;
				elseif ($mod_action['type'] == 'failure' && empty($mod_action['is_custom']))
				{
					$context['has_failure'] = true;
					$failed = true;
				}
				elseif ($mod_action['type'] == 'chmod')
				{
					$context['ftp_needed'] = true;
					$chmod_files[] = $mod_action['filename'];
				}
				elseif ($mod_action['type'] == 'saved')
				{
					if (!empty($mod_action['is_custom']))
					{
						if (!isset($context['theme_actions'][$mod_action['is_custom']]))
							$context['theme_actions'][$mod_action['is_custom']] = array(
								'name' => $theme_paths[$mod_action['is_custom']]['name'],
								'actions' => array(),
								'has_failure' => $failed,
							);
						else
							$context['theme_actions'][$mod_action['is_custom']]['has_failure'] |= $failed;

						$context['theme_actions'][$mod_action['is_custom']]['actions'][] = array(
							'type' => $txt['package56'],
							'action' => strtr($mod_action['filename'], array($boarddir => '.')),
							'description' => $failed ? $txt['package_action_failure'] : $txt['package_action_success']
						);
					}
					else
					{
						$context['actions'][] = array(
							'type' => $txt['package56'],
							'action' => strtr($mod_action['filename'], array($boarddir => '.')),
							'description' => $failed ? $txt['package_action_failure'] : $txt['package_action_success']
						);
					}
				}
				elseif ($mod_action['type'] == 'missing' && empty($mod_action['is_custom']))
				{
					$context['has_failure'] = true;
					$context['actions'][] = array(
						'type' => $txt['package56'],
						'action' => strtr($mod_action['filename'], array($boarddir => '.')),
						'description' => $txt['package_action_missing']
					);
				}
				elseif ($mod_action['type'] == 'error')
					$context['actions'][] = array(
						'type' => $txt['package56'],
						'action' => strtr($mod_action['filename'], array($boarddir => '.')),
						'description' => $txt['package_action_error']
					);
			}

			// Don't add anything else.
			$thisAction = array();
		}
		elseif ($action['type'] == 'code')
			$thisAction = array(
				'type' => $txt['package57'],
				'action' => $action['filename']
			);
		elseif (in_array($action['type'], array('create-dir', 'create-file')))
			$thisAction = array(
				'type' => $txt['package50'] . ' ' . ($action['type'] == 'create-dir' ? $txt['package55'] : $txt['package54']),
				'action' => strtr($action['destination'], array($boarddir => '.'))
			);
		elseif (in_array($action['type'], array('require-dir', 'require-file')))
			$thisAction = array(
				'type' => $txt['package53'] . ' ' . ($action['type'] == 'require-dir' ? $txt['package55'] : $txt['package54']),
				'action' => strtr($action['destination'], array($boarddir => '.'))
			);
		elseif (in_array($action['type'], array('move-dir', 'move-file')))
			$thisAction = array(
				'type' => $txt['package51'] . ' ' . ($action['type'] == 'move-dir' ? $txt['package55'] : $txt['package54']),
				'action' => strtr($action['source'], array($boarddir => '.')) . ' => ' . strtr($action['destination'], array($boarddir => '.'))
			);
		elseif (in_array($action['type'], array('remove-dir', 'remove-file')))
			$thisAction = array(
				'type' => $txt['package52'] . ' ' . ($action['type'] == 'remove-dir' ? $txt['package55'] : $txt['package54']),
				'action' => strtr($action['filename'], array($boarddir => '.'))
			);

		if (empty($thisAction))
			continue;

		// !!! None given?
		$thisAction['description'] = isset($action['description']) ? $action['description'] : '';
		$context['actions'][] = $thisAction;
	}

	// Trash the cache... which will also check permissions for us!
	package_flush_cache(true);

	if (file_exists($boarddir . '/Packages/temp'))
		deltree($boarddir . '/Packages/temp');

	if ($context['ftp_needed'])
		packageRequireFTP($scripturl . '?action=admin;area=packages;sa=' . $_REQUEST['sa'] . ';package=' . $_REQUEST['package'], $chmod_files);
	$context['ftp_needed'] = false;
}

// Apply another type of (avatar, language, etc.) package.
function PackageInstall()
{
	global $boarddir, $txt, $context, $boardurl, $scripturl, $sourcedir, $modSettings;
	global $db_prefix, $user_info, $id_member, $smfFunc;

	// If there's no file, what are we installing?
	if (!isset($_REQUEST['package']) || $_REQUEST['package'] == '')
		redirectexit('action=admin;area=packages');
	$context['filename'] = $_REQUEST['package'];

	// If this is an uninstall, we'll have an id.
	$context['install_id'] = isset($_REQUEST['pid']) ? (int) $_REQUEST['pid'] : 0;

	require_once($sourcedir . '/Subs-Package.php');

	// !!! TODO: Perhaps do it in steps, if necessary?

	$context['uninstalling'] = $_REQUEST['sa'] == 'uninstall2';

	// Set up the linktree for other.
	$context['linktree'][] = array(
		'url' => $scripturl . '?action=admin;area=packages;sa=browse',
		'name' => $context['uninstalling'] ? $txt['smf198b'] : $txt['package37']
	);
	$context['page_title'] .= ' - ' . ($context['uninstalling'] ? $txt['smf198b'] : $txt['package37']);

	$context['sub_template'] = 'extract_package';

	if (!file_exists($boarddir . '/Packages/' . $context['filename']))
		fatal_lang_error('package_no_file', false);

	// Load up the package FTP information?
	if (isset($_SESSION['pack_ftp']))
		packageRequireFTP($scripturl . '?action=admin;area=packages;sa=' . $_REQUEST['sa'] . ';package=' . $_REQUEST['package']);

	// Make sure temp directory exists and is empty!
	if (file_exists($boarddir . '/Packages/temp'))
		deltree($boarddir . '/Packages/temp', false);
	else
		mktree($boarddir . '/Packages/temp', 0777);

	// Let the unpacker do the work.
	if (is_file($boarddir . '/Packages/' . $context['filename']))
	{
		$context['extracted_files'] = read_tgz_file($boarddir . '/Packages/' . $context['filename'], $boarddir . '/Packages/temp');

		if (!file_exists($boarddir . '/Packages/temp/package-info.xml'))
			foreach ($context['extracted_files'] as $file)
				if (basename($file['filename']) == 'package-info.xml')
				{
					$context['base_path'] = dirname($file['filename']) . '/';
					break;
				}

		if (!isset($context['base_path']))
			$context['base_path'] = '';
	}
	elseif (is_dir($boarddir . '/Packages/' . $context['filename']))
	{
		copytree($boarddir . '/Packages/' . $context['filename'], $boarddir . '/Packages/temp');
		$context['extracted_files'] = listtree($boarddir . '/Packages/temp');
		$context['base_path'] = '';
	}
	else
		fatal_lang_error(1, false);

	// Are we installing this into any custom themes?
	$custom_themes = array(1);
	$known_themes = explode(',', $modSettings['knownThemes']);
	if (!empty($_POST['custom_theme']))
	{
		foreach ($_POST['custom_theme'] as $tid)
			if (in_array($tid, $known_themes))
				$custom_themes[] = (int) $tid;
	}

	// Now load up the paths of the themes that we need to know about.
	$request = $smfFunc['db_query']('', "
		SELECT id_theme, variable, value
		FROM {$db_prefix}themes
		WHERE id_theme IN (" . implode(',', $custom_themes) . ")
			AND variable IN ('name', 'theme_dir')", __FILE__, __LINE__);
	$theme_paths = array();
	while ($row = $smfFunc['db_fetch_assoc']($request))
	{
		$theme_paths[$row['id_theme']][$row['variable']] = $row['value'];
	}
	$smfFunc['db_free_result']($request);

	// Get the package info...
	$packageInfo = getPackageInfo($context['filename']);
	$packageInfo['filename'] = $context['filename'];

	// Set the type of extraction...
	$context['extract_type'] = isset($packageInfo['type']) ? $packageInfo['type'] : 'modification';

	// Create a backup file to roll back to! (but if they do this more than once, don't run it a zillion times.)
	if (!empty($modSettings['package_make_backups']) && (!isset($_SESSION['last_backup_for']) || $_SESSION['last_backup_for'] != $context['filename'] . ($context['uninstalling'] ? '$$' : '$')))
	{
		$_SESSION['last_backup_for'] = $context['filename'] . ($context['uninstalling'] ? '$$' : '$');
		// !!! Internationalize this?
		package_create_backup(($context['uninstalling'] ? 'backup_' : 'before_') . strtok($context['filename'], '.'));
	}

	// The mod isn't installed.... unless proven otherwise.
	$context['is_installed'] = false;

	// Is it actually installed?
	$request = $smfFunc['db_query']('', "
		SELECT version, themes_installed
		FROM {$db_prefix}log_packages
		WHERE package_id = '" . addslashes($packageInfo['id']) . "'
			AND install_state = 1", __FILE__, __LINE__);
	while ($row = $smfFunc['db_fetch_assoc']($request))
	{
		$old_themes = explode(',', $row['themes_installed']);
		$old_version = $row['version'];
	}
	$smfFunc['db_free_result']($request);

	// Wait, it's not installed yet!
	// !!! TODO: Replace with a better error message!
	if (!isset($old_version) && $context['uninstalling'])
	{
		deltree($boarddir . '/Packages/temp');
		fatal_error('Hacker?', false);
	}
	// Uninstalling?
	elseif ($context['uninstalling'])
	{
		$install_log = parsePackageInfo($packageInfo['xml'], false, 'uninstall');

		// Gadzooks!  There's no uninstaller at all!?
		if (empty($install_log))
			fatal_lang_error('package_uninstall_cannot', false);

		// They can only uninstall from what it was originally installed into.
		foreach ($theme_paths as $id => $data)
			if ($id != 1 && !in_array($id, $old_themes))
				unset($theme_paths[$id]);
	}
	elseif (isset($old_version) && $old_version != $packageInfo['version'])
	{
		// Look for an upgrade...
		$install_log = parsePackageInfo($packageInfo['xml'], false, 'upgrade', $old_version);

		// There was no upgrade....
		if (empty($install_log))
			$context['is_installed'] = true;
		else
		{
			// Upgrade previous themes only!
			foreach ($theme_paths as $id => $data)
				if ($id != 1 && !in_array($id, $old_themes))
					unset($theme_paths[$id]);
		}
	}
	elseif (isset($old_version) && $old_version == $packageInfo['version'])
		$context['is_installed'] = true;

	if (!isset($old_version) || $context['is_installed'])
		$install_log = parsePackageInfo($packageInfo['xml'], false, 'install');

	$context['install_finished'] = false;

	// !!! TODO: Make a log of any errors that occurred and output them?

	if (!empty($install_log))
	{
		$failed_steps = array();
		$failed_count = 0;
		$themes_installed = array(1);

		foreach ($install_log as $action)
		{
			$failed_count++;

			if ($action['type'] == 'modification' && !empty($action['filename']))
			{
				if ($action['boardmod'])
					$mod_actions = parseBoardMod(file_get_contents($boarddir . '/Packages/temp/' . $context['base_path'] . $action['filename']), false, $action['reverse'], $theme_paths);
				else
					$mod_actions = parseModification(file_get_contents($boarddir . '/Packages/temp/' . $context['base_path'] . $action['filename']), false, $action['reverse'], $theme_paths);

				// Any errors worth noting?
				foreach ($mod_actions as $key => $action)
				{
					if ($action['type'] == 'failure')
						$failed_steps[] = array(
							'file' => $action['filename'],
							'large_step' => $failed_count,
							'sub_step' => $key,
							'theme' => 1,
						);
					// Gather the themes we installed into.
					if (!empty($action['is_custom']))
						$themes_installed[] = $action['is_custom'];
				}
			}
			elseif ($action['type'] == 'code' && !empty($action['filename']))
			{
				// This is just here as reference for what is available.
				global $txt, $boarddir, $sourcedir, $modSettings, $context, $settings, $db_prefix, $forum_version;

				// Now include the file and be done with it ;).
				require($boarddir . '/Packages/temp/' . $context['base_path'] . $action['filename']);
			}
			// Handle a redirect...
			elseif ($action['type'] == 'redirect' && !empty($action['redirect_url']))
			{
				$context['redirect_url'] = $action['redirect_url'];
				$context['redirect_text'] = file_get_contents($boarddir . '/Packages/temp/' . $context['base_path'] . $action['filename']);
				$context['redirect_timeout'] = $action['redirect_timeout'];

				// Parse out a couple of common urls.
				$urls = array(
					'$boardurl' => $boardurl,
					'$scripturl' => $scripturl,
					'$session_id' => $context['session_id'],
				);
			
				$context['redirect_url'] = strtr($context['redirect_url'], $urls);
			}
		}

		package_flush_cache();

		// First, ensure this change doesn't get removed by putting a stake in the ground (So to speak).
		package_put_contents($boarddir . '/Packages/installed.list', time());

		// See if this is already installed, and change it's state as required.
		$request = $smfFunc['db_query']('', "
			SELECT id_install, install_state
			FROM {$db_prefix}log_packages
			WHERE install_state != 0
				AND package_id = '" . addslashes($packageInfo['id']) . "'
				" . ($context['install_id'] ? " AND id_install = $context[install_id] " : '') . "
			ORDER BY time_installed DESC
			LIMIT 1", __FILE__, __LINE__);
		$is_upgrade = false;
		while ($row = $smfFunc['db_fetch_assoc']($request))
		{
			// Uninstalling?
			if ($context['uninstalling'])
			{
				$smfFunc['db_query']('', "
					UPDATE {$db_prefix}log_packages
					SET install_state = 0, member_removed = '$user_info[name]', id_member_removed = $id_member,
						time_removed = " . time() . "
					WHERE id_install = $row[id_install]", __FILE__, __LINE__);
			}
			// Otherwise must be an upgrade.
			else
				$is_upgrade = true;
		}

		// Assuming we're not uninstalling, add the entry.
		if (!$context['uninstalling'])
		{
			// What themes did we actually install?
			$themes_installed = array_unique($themes_installed);
			$themes_installed = implode(',', $themes_installed);

			// What failed steps?
			$failed_step_insert = addslashes(serialize($failed_steps));
		
			$smfFunc['db_query']('', "
				INSERT INTO {$db_prefix}log_packages
					(filename, name, package_id, version, id_member_installed, member_installed, time_installed,
					install_state, failed_steps, themes_installed)
				VALUES
					('" . addslashes($packageInfo['filename']) . "', '" . addslashes($packageInfo['name']) . "',
					'" . addslashes($packageInfo['id']) . "', '" . addslashes($packageInfo['version']) . "',
					$id_member, '$user_info[name]', " . time() . ", " . ($is_upgrade ? 2 : 1) . ", '$failed_step_insert',
					'$themes_installed')", __FILE__, __LINE__);
		}
		$smfFunc['db_free_result']($request);

		$context['install_finished'] = true;
	}

	// Clean house... get rid of the evidence ;).
	if (file_exists($boarddir . '/Packages/temp'))
		deltree($boarddir . '/Packages/temp');

	// Just incase it's modified any language files let's remove them all.
	clean_cache('lang');
}

// List the files in a package.
function PackageList()
{
	global $txt, $scripturl, $boarddir, $context, $sourcedir;

	require_once($sourcedir . '/Subs-Package.php');

	// No package?  Show him or her the door.
	if (!isset($_REQUEST['package']) || $_REQUEST['package'] == '')
		redirectexit('action=admin;area=packages');

	$context['linktree'][] = array(
		'url' => $scripturl . '?action=admin;area=packages;sa=list;package=' . $_REQUEST['package'],
		'name' => &$txt['smf180']
	);
	$context['page_title'] .= ' - ' . $txt['smf180'];
	$context['sub_template'] = 'list';

	// The filename...
	$context['filename'] = $_REQUEST['package'];

	// Let the unpacker do the work.
	if (is_file($boarddir . '/Packages/' . $context['filename']))
		$context['files'] = read_tgz_file($boarddir . '/Packages/' . $context['filename'], null);
	elseif (is_dir($boarddir . '/Packages/' . $context['filename']))
		$context['files'] = listtree($boarddir . '/Packages/' . $context['filename']);
}

// List the files in a package.
function ExamineFile()
{
	global $txt, $scripturl, $boarddir, $context, $sourcedir;

	require_once($sourcedir . '/Subs-Package.php');

	// No package?  Show him or her the door.
	if (!isset($_REQUEST['package']) || $_REQUEST['package'] == '')
		redirectexit('action=admin;area=packages');

	// No file?  Show him or her the door.
	if (!isset($_REQUEST['file']) || $_REQUEST['file'] == '')
		redirectexit('action=admin;area=packages');

	$_REQUEST['package'] = preg_replace('~[\.]+~', '.', $_REQUEST['package']);
	$_REQUEST['file'] = preg_replace('~[\.]+~', '.', $_REQUEST['file']);

	if (isset($_REQUEST['raw']))
	{
		if (is_file($boarddir . '/Packages/' . $_REQUEST['package']))
			echo read_tgz_file($boarddir . '/Packages/' . $_REQUEST['package'], $_REQUEST['file'], true);
		elseif (is_dir($boarddir . '/Packages/' . $_REQUEST['package']))
			echo file_get_contents($boarddir . '/Packages/' . $_REQUEST['package'] . '/' . $_REQUEST['file']);

		obExit(false);
	}

	$context['linktree'][] = array(
		'url' => $scripturl . '?action=admin;area=packages;sa=list;package=' . $_REQUEST['package'],
		'name' => &$txt['package_examine_file']
	);
	$context['page_title'] .= ' - ' . $txt['package_examine_file'];
	$context['sub_template'] = 'examine';

	// The filename...
	$context['package'] = $_REQUEST['package'];
	$context['filename'] = $_REQUEST['file'];

	// Let the unpacker do the work.... but make sure we handle images properly.
	if (in_array(strtolower(strrchr($_REQUEST['file'], '.')), array('.bmp', '.gif', '.jpeg', '.jpg', '.png')))
		$context['filedata'] = '<img src="' . $scripturl . '?action=admin;area=packages;sa=examine;package=' . $_REQUEST['package'] . ';file=' . $_REQUEST['file'] . ';raw" alt="' . $_REQUEST['file'] . '" />';
	else
	{
		if (is_file($boarddir . '/Packages/' . $_REQUEST['package']))
			$context['filedata'] = htmlspecialchars(read_tgz_file($boarddir . '/Packages/' . $_REQUEST['package'], $_REQUEST['file'], true));
		elseif (is_dir($boarddir . '/Packages/' . $_REQUEST['package']))
			$context['filedata'] = htmlspecialchars(file_get_contents($boarddir . '/Packages/' . $_REQUEST['package'] . '/' . $_REQUEST['file']));

		if (strtolower(strrchr($_REQUEST['file'], '.')) == '.php')
			$context['filedata'] = highlight_php_code($context['filedata']);
	}
}

// List the installed packages.
function InstalledList()
{
	global $txt, $scripturl, $context;

	// Set up the linktree so things are purdy.
	$context['linktree'][] = array(
		'url' => $scripturl . '?action=admin;area=packages;sa=installed',
		'name' => &$txt['package6']
	);
	$context['page_title'] .= ' - ' . $txt['installed_packages'];
	$context['sub_template'] = 'view_installed';

	// Load the installed mods and send them to the template.
	$context['installed_mods'] = loadInstalledPackages();
}

// Empty out the installed list.
function FlushInstall()
{
	global $boarddir, $sourcedir, $db_prefix, $smfFunc;

	include_once($sourcedir . '/Subs-Package.php');

	// Record when we last did this.
	package_put_contents($boarddir . '/Packages/installed.list', time());

	// Set everything as uninstalled.
	$smfFunc['db_query']('', "
		UPDATE {$db_prefix}log_packages
		SET install_state = 0", __FILE__, __LINE__);

	redirectexit('action=admin;area=packages;sa=installed');
}

// Delete a package.
function PackageRemove()
{
	global $scripturl, $boarddir;

	// Ack, don't allow deletion of arbitrary files here, could become a security hole somehow!
	if (!isset($_GET['package']) || $_GET['package'] == 'index.php' || $_GET['package'] == 'installed.list')
		redirectexit('action=admin;area=packages;sa=browse');
	$_GET['package'] = preg_replace('~[\.]+~', '.', strtr($_GET['package'], '/', '_'));

	// Can't delete what's not there.
	if (file_exists($boarddir . '/Packages/' . $_GET['package']))
	{
		packageRequireFTP($scripturl . '?action=admin;area=packages;sa=remove;package=' . $_GET['package'], array($boarddir . '/Packages/' . $_GET['package']));

		if (is_dir($boarddir . '/Packages/' . $_GET['package']))
			deltree($boarddir . '/Packages/' . $_GET['package']);
		else
		{
			@chmod($boarddir . '/Packages/' . $_GET['package'], 0777);
			unlink($boarddir . '/Packages/' . $_GET['package']);
		}
	}

	redirectexit('action=admin;area=packages;sa=browse');
}

// Browse a list of installed packages.
function PackageBrowse()
{
	global $txt, $boarddir, $scripturl, $context, $forum_version, $db_prefix;

	$context['linktree'][] = array(
		'url' => $scripturl . '?action=admin;area=packages;sa=browse',
		'name' => &$txt['package3']
	);
	$context['page_title'] .= ' - ' . $txt['package3'];
	$context['sub_template'] = 'browse';

	$context['forum_version'] = $forum_version;

	$instmods = loadInstalledPackages();

	$installed_mods = array();
	// Look through the list of installed mods...
	foreach ($instmods as $installed_mod)
		$installed_mods[$installed_mod['package_id']] = array(
			'id' => $installed_mod['id'],
			'version' => $installed_mod['version'],
		);

	$the_version = strtr($forum_version, array('SMF ' => ''));

	// Here we have a little code to help those who class themselves as something of gods, version emulation ;)
	if (isset($_GET['version_emulate']))
	{
		if ($_GET['version_emulate'] == 0 && isset($_SESSION['version_emulate']))
			unset($_SESSION['version_emulate']);
		elseif ($_GET['version_emulate'] != 0)
			$_SESSION['version_emulate'] = strtr($_GET['version_emulate'], '-', ' ');
	}
	if (!empty($_SESSION['version_emulate']))
	{
		$context['forum_version'] = 'SMF ' . $_SESSION['version_emulate'];
		$the_version = $_SESSION['version_emulate'];
	}

	// Get a list of all the ids installed, so the latest packages won't include already installed ones.
	$context['installed_mods'] = array_keys($installed_mods);

	// Empty lists for now.
	$context['available_mods'] = array();
	$context['available_avatars'] = array();
	$context['available_languages'] = array();
	$context['available_other'] = array();
	$context['available_all'] = array();

	if ($dir = @opendir($boarddir . '/Packages'))
	{
		$dirs = array();
		while ($package = readdir($dir))
		{
			if ($package == '.' || $package == '..' || $package == 'temp' || (!(is_dir($boarddir . '/Packages/' . $package) && file_exists($boarddir . '/Packages/' . $package . '/package-info.xml')) && substr($package, -7) != '.tar.gz' && substr($package, -4) != '.tgz' && substr($package, -4) != '.zip'))
				continue;

			// Skip directories or files that are named the same.
			if (is_dir($boarddir . '/Packages/' . $package))
			{
				if (in_array($package, $dirs))
					continue;
				$dirs[] = $package;
			}
			elseif (substr($package, -7) == '.tar.gz')
			{
				if (in_array(substr($package, 0, -7), $dirs))
					continue;
				$dirs[] = substr($package, 0, -7);
			}
			elseif (substr($package, -4) == '.zip' || substr($package, -4) == '.tgz')
			{
				if (in_array(substr($package, 0, -4), $dirs))
					continue;
				$dirs[] = substr($package, 0, -4);
			}

			$packageInfo = getPackageInfo($package);
			if ($packageInfo === false)
				continue;

			$packageInfo['installed_id'] = isset($installed_mods[$packageInfo['id']]) ? $installed_mods[$packageInfo['id']]['id'] : 0;

			$packageInfo['is_installed'] = isset($installed_mods[$packageInfo['id']]);
			$packageInfo['is_current'] = $packageInfo['is_installed'] && ($installed_mods[$packageInfo['id']]['version'] == $packageInfo['version']);
			$packageInfo['is_newer'] = $packageInfo['is_installed'] && ($installed_mods[$packageInfo['id']]['version'] > $packageInfo['version']);

			$packageInfo['can_install'] = false;
			$packageInfo['can_uninstall'] = false;
			$packageInfo['can_upgrade'] = false;

			// This package is currently NOT installed.  Check if it can be.
			if (!$packageInfo['is_installed'] && $packageInfo['xml']->exists('install'))
			{
				// Check if there's an install for *THIS* version of SMF.
				$installs = $packageInfo['xml']->set('install');
				foreach ($installs as $install)
				{
					if (!$install->exists('@for') || matchPackageVersion($the_version, $install->fetch('@for')))
					{
						// Okay, this one is good to go.
						$packageInfo['can_install'] = true;
						break;
					}
				}
			}
			// An already installed, but old, package.  Can we upgrade it?
			elseif ($packageInfo['is_installed'] && !$packageInfo['is_current'] && $packageInfo['xml']->exists('upgrade'))
			{
				$upgrades = $packageInfo['xml']->set('upgrade');

				// First go through, and check against the current version of SMF.
				foreach ($upgrades as $upgrade)
				{
					// Even if it is for this SMF, is it for the installed version of the mod?
					if (!$upgrade->exists('@for') || matchPackageVersion($the_version, $upgrade->fetch('@for')))
						if (!$upgrade->exists('@from') || matchPackageVersion($installed_mods[$packageInfo['id']]['version'], $upgrade->fetch('@from')))
						{
							$packageInfo['can_upgrade'] = true;
							break;
						}
				}
			}
			// Note that it has to be the current version to be uninstallable.  Shucks.
			elseif ($packageInfo['is_installed'] && $packageInfo['is_current'] && $packageInfo['xml']->exists('uninstall'))
			{
				$uninstalls = $packageInfo['xml']->set('uninstall');

				// Can we find any uninstallation methods that work for this SMF version?
				foreach ($uninstalls as $uninstall)
					if (!$uninstall->exists('@for') || matchPackageVersion($the_version, $uninstall->fetch('@for')))
					{
						$packageInfo['can_uninstall'] = true;
						break;
					}
			}

			// Store a complete list.
			$context['available_all'][] = $packageInfo;

			// Modification.
			if ($packageInfo['type'] == 'modification' || $packageInfo['type'] == 'mod')
				$context['available_mods'][] = $packageInfo;
			// Avatar package.
			elseif ($packageInfo['type'] == 'avatar')
				$context['available_avatars'][] = $packageInfo;
			// Language package.
			elseif ($packageInfo['type'] == 'language')
				$context['available_languages'][] = $packageInfo;
			// Other stuff.
			else
				$context['available_other'][] = $packageInfo;
		}
		closedir($dir);
	}
}

function PackageOptions()
{
	global $txt, $scripturl, $context, $sourcedir, $modSettings;

	if (isset($_POST['submit']))
	{
		updateSettings(array(
			'package_server' => $_POST['pack_server'],
			'package_port' => $_POST['pack_port'],
			'package_username' => $_POST['pack_user'],
			'package_make_backups' => !empty($_POST['package_make_backups'])
		));

		redirectexit('action=admin;area=packages;sa=options');
	}

	if (preg_match('~^/home/([^/]+?)/public_html~', $_SERVER['DOCUMENT_ROOT'], $match))
		$default_username = $match[1];
	else
		$default_username = '';

	$context['linktree'][] = array(
		'url' => $scripturl . '?action=admin;area=packages;sa=options',
		'name' => &$txt['package_install_options']
	);
	$context['page_title'] = $txt['package_settings'];
	$context['sub_template'] = 'install_options';

	$context['package_ftp_server'] = isset($modSettings['package_server']) ? $modSettings['package_server'] : 'localhost';
	$context['package_ftp_port'] = isset($modSettings['package_port']) ? $modSettings['package_port'] : '21';
	$context['package_ftp_username'] = isset($modSettings['package_username']) ? $modSettings['package_username'] : $default_username;
	$context['package_make_backups'] = !empty($modSettings['package_make_backups']);
}

?>