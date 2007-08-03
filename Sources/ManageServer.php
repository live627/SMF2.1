<?php
/**********************************************************************************
* ManageServer.php                                                                *
***********************************************************************************
* SMF: Simple Machines Forum                                                      *
* Open-Source Project Inspired by Zef Hemel (zef@zefhemel.com)                    *
* =============================================================================== *
* Software Version:           SMF 2.0 Beta 1                                       *
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

/*	This file contains all the functionality required to be able to edit the
	core server settings. This includes anything from which an error may result
	in the forum destroying itself in a firey fury.

	void ModifySettings()
		// !!!

	void ModifySettings2()
		// !!!

	void ModifyCoreSettings()
		- shows an interface for the settings in Settings.php to be changed.
		- uses the rawdata sub template (not theme-able.)
		- requires the admin_forum permission.
		- uses the edit_settings administration area.
		- contains the actual array of settings to show from Settings.php.
		- accessed from ?action=admin;area=serversettings.

	void ModifyCoreSettings2()
		- saves those settings set from ?action=admin;area=serversettings to the
		  Settings.php file.
		- requires the admin_forum permission.
		- contains arrays of the types of data to save into Settings.php.
		- redirects back to ?action=admin;area=serversettings.
		- accessed from ?action=admin;area=serversettings;save.

	void ModifyOtherSettings()
		// !!!

	void ModifyCacheSettings()
		// !!!

	void prepareDBSettingContext(array config_vars)
		// !!!

	void saveDBSettings(array config_vars)
		// !!!
*/

/*	Adding options to one of the setting screens isn't hard. Call prepareDBSettingsContext;
	The basic format for a checkbox is:
		array('check', 'nameInModSettingsAndSQL'),

	   And for a text box:
		array('text', 'nameInModSettingsAndSQL')
	   (NOTE: You have to add an entry for this at the bottom!)

	   In these cases, it will look for $txt['nameInModSettingsAndSQL'] as the description,
	   and $helptxt['nameInModSettingsAndSQL'] as the help popup description.

	Here's a quick explanation of how to add a new item:

	 * A text input box.  For textual values.
	ie.	array('text', 'nameInModSettingsAndSQL', 'OptionalInputBoxWidth'),

	 * A text input box.  For numerical values.
	ie.	array('int', 'nameInModSettingsAndSQL', 'OptionalInputBoxWidth'),

	 * A text input box.  For floating point values.
	ie.	array('float', 'nameInModSettingsAndSQL', 'OptionalInputBoxWidth'),
			
	 * A large text input box. Used for textual values spanning multiple lines.
	ie.	array('large_text', 'nameInModSettingsAndSQL', 'OptionalNumberOfRows'),

	 * A check box.  Either one or zero. (boolean)
	ie.	array('check', 'nameInModSettingsAndSQL'),

	 * A selection box.  Used for the selection of something from a list.
	ie.	array('select', 'nameInModSettingsAndSQL', array('valueForSQL' => &$txt['displayedValue'])),
	Note that just saying array('first', 'second') will put 0 in the SQL for 'first'.

	 * A password input box. Used for passwords, no less!
	ie.	array('password', 'nameInModSettingsAndSQL', 'OptionalInputBoxWidth'),

	* A permission - for picking groups who have a permission.
	ie.	array('permission', 'manage_groups'),

	* A BBC selection box.
	ie.	array('bbc', 'sig_bbc'),

	For each option:
		type (see above), variable name, size/possible values.
	OR	make type '' for an empty string for a horizontal rule.
	SET	preinput - to put some HTML prior to the input box.
	SET	postinput - to put some HTML following the input box.
	SET	invalid - to mark the data as invalid.
	PLUS	You can override label and help parameters by forcing their keys in the array, for example:
		array('text', 'invalidlabel', 3, 'label' => 'Actual Label') */

// This is the main pass through function, it creates tabs and the like.
function ModifySettings()
{
	global $context, $txt, $scripturl, $modSettings;

	if (isset($_GET['save']))
		return ModifySettings2();

	// This is just to keep the database password more secure.
	isAllowedTo('admin_forum');
	checkSession('get');

	$context['page_title'] = $txt['admin_server_settings'];
	$context['sub_template'] = 'show_settings';

	$subActions = array(
		'core' => 'ModifyCoreSettings',
		'other' => 'ModifyOtherSettings',
		'cache' => 'ModifyCacheSettings',
	);

	// By default we're editing the core settings
	$_REQUEST['sa'] = isset($_REQUEST['sa']) && isset($subActions[$_REQUEST['sa']]) ? $_REQUEST['sa'] : 'core';
	$context['sub_action'] = $_REQUEST['sa'];

	// Load up all the tabs...
	$context[$context['admin_menu_name']]['tab_data'] = array(
		'title' => &$txt['admin_server_settings'],
		'help' => 'serversettings',
		'description' => $txt['admin_basic_settings'],
	);

	// Call the right function for this sub-acton.
	$subActions[$_REQUEST['sa']]();
}

// This function basically just redirects to the right save function.
function ModifySettings2()
{
	global $context, $txt, $scripturl, $modSettings;

	isAllowedTo('admin_forum');

	// Quick session check...
	checkSession();

	$subActions = array(
		'core' => 'ModifyCoreSettings2',
		'other' => 'ModifyOtherSettings',
		'cache' => 'ModifyCacheSettings',
	);

	// Default to core (I assume)
	$_REQUEST['sa'] = isset($_REQUEST['sa']) && isset($subActions[$_REQUEST['sa']]) ? $_REQUEST['sa'] : 'core';

	// Actually call the saving function.
	$subActions[$_REQUEST['sa']]();
}

// Basic forum settings - database name, host, etc.
function ModifyCoreSettings()
{
	global $scripturl, $context, $settings, $txt, $sc, $boarddir, $smfFunc;

	// Warn the user if the backup of Settings.php failed.
	$settings_not_writable = !is_writable($boarddir . '/Settings.php');
	$settings_backup_fail = !@is_writable($boarddir . '/Settings_bak.php') || !@copy($boarddir . '/Settings.php', $boarddir . '/Settings_bak.php');

	/* If you're writing a mod, it's a bad idea to add things here....
	For each option:
		variable name, description, type (constant), size/possible values, helptext.
	OR	an empty string for a horizontal rule.
	OR	a string for a titled section. */
	$config_vars = array(
		array('db_server', &$txt['database_server'], 'text'),
		array('db_user', &$txt['database_user'], 'text'),
		array('db_passwd', &$txt['database_password'], 'password'),
		array('db_name', &$txt['database_name'], 'text'),
		array('db_prefix', &$txt['database_prexfix'], 'text'),
		array('db_persist', &$txt['db_persist'], 'check', null, 'db_persist'),
		array('db_error_send', &$txt['db_error_send'], 'check'),
		array('ssi_db_user', &$txt['ssi_db_user'], 'text', null, 'ssi_db_user'),
		array('ssi_db_passwd', &$txt['ssi_db_passwd'], 'password'),
		'',
		array('maintenance', &$txt['admin_maintain'], 'check'),
		array('mtitle', &$txt['maintenance_subject'], 'text', 36),
		array('mmessage', &$txt['maintenance_message'], 'text', 36),
		'',
		array('mbname', &$txt['admin_title'], 'text', 30),
		array('webmaster_email', &$txt['admin_webmaster_email'], 'text', 30),
		array('cookiename', &$txt['cookie_name'], 'text', 20),
		'language' => array('language', &$txt['default_language'], 'select', array()),
		'',
		array('boardurl', &$txt['admin_url'], 'text', 36),
		array('boarddir', &$txt['boarddir'], 'text', 36),
		array('sourcedir', &$txt['sourcesdir'], 'text', 36),
		array('cachedir', &$txt['cachedir'], 'text', 36),
		'',
	);

	// Find the available language files.
	$language_directories = array(
		$settings['default_theme_dir'] . '/languages',
		$settings['actual_theme_dir'] . '/languages',
	);
	if (!empty($settings['base_theme_dir']))
		$language_directories[] = $settings['base_theme_dir'] . '/languages';
	$language_directories = array_unique($language_directories);

	foreach ($language_directories as $language_dir)
	{
		if (!file_exists($language_dir))
			continue;

		$dir = dir($language_dir);
		while ($entry = $dir->read())
			if (preg_match('~^index\.(.+)\.php$~', $entry, $matches))
				$config_vars['language'][3][$matches[1]] = array($matches[1], $smfFunc['ucwords'](strtr($matches[1], '_', ' ')));
		$dir->close();
	}

	// Setup the template stuff.
	$context['post_url'] = $scripturl . '?action=admin;area=serversettings;save;sa=core';
	$context['settings_title'] = $txt['core_configuration'];
	$context['save_disabled'] = $settings_not_writable;

	if ($settings_not_writable)
		$context['settings_message'] = '<div align="center"><b>' . $txt['settings_not_writable'] . '</b></div><br />';
	elseif ($settings_backup_fail)
		$context['settings_message'] = '<div align="center"><b>' . $txt['admin_backup_fail'] . '</b></div><br />';

	// Fill the config array.
	$context['config_vars'] = array();
	foreach ($config_vars as $config_var)
	{
		if (!is_array($config_var) || !isset($config_var[1]))
			$context['config_vars'][] = $config_var;
		else
		{
			$varname = $config_var[0];
			global $$varname;

			$context['config_vars'][] = array(
				'label' => $config_var[1],
				'help' => isset($config_var[4]) ? $config_var[4] : '',
				'type' => $config_var[2],
				'size' => empty($config_var[3]) ? 0 : $config_var[3],
				'data' => isset($config_var[3]) && is_array($config_var[3]) ? $config_var[3] : array(),
				'name' => $config_var[0],
				'value' => htmlspecialchars($$varname),
				'disabled' => $settings_not_writable,
				'invalid' => false,
				'javascript' => '',
				'preinput' => '',
				'postinput' => '',
			);
		}
	}
}

// Put the core settings in Settings.php.
function ModifyCoreSettings2()
{
	global $boarddir, $sc, $cookiename, $modSettings, $user_settings;
	global $sourcedir, $context, $cachedir;

	// Unescape off of the post vars.
	foreach ($_POST as $key => $val)
		$_POST[$key] = unescapestring__recursive($val);

	// Fix the darn stupid cookiename! (more may not be allowed, but these for sure!)
	if (isset($_POST['cookiename']))
		$_POST['cookiename'] = preg_replace('~[,;\s\.$]+~' . ($context['utf8'] ? 'u' : ''), '', $_POST['cookiename']);

	// Fix the forum's URL if necessary.
	if (substr($_POST['boardurl'], -10) == '/index.php')
		$_POST['boardurl'] = substr($_POST['boardurl'], 0, -10);
	elseif (substr($_POST['boardurl'], -1) == '/')
		$_POST['boardurl'] = substr($_POST['boardurl'], 0, -1);
	if (substr($_POST['boardurl'], 0, 7) != 'http://' && substr($_POST['boardurl'], 0, 7) != 'file://' && substr($_POST['boardurl'], 0, 8) != 'https://')
		$_POST['boardurl'] = 'http://' . $_POST['boardurl'];

	// Any passwords?
	$config_passwords = array(
		'db_passwd',
		'ssi_db_passwd',
	);

	// All the strings to write.
	$config_strs = array(
		'mtitle', 'mmessage',
		'language', 'mbname', 'boardurl',
		'cookiename',
		'webmaster_email',
		'db_name', 'db_user', 'db_server', 'db_prefix', 'ssi_db_user',
		'boarddir', 'sourcedir', 'cachedir',
	);
	// All the numeric variables.
	$config_ints = array(
	);
	// All the checkboxes.
	$config_bools = array(
		'db_persist', 'db_error_send',
		'maintenance',
	);

	// Now sort everything into a big array, and figure out arrays and etc.
	$config_vars = array();
	foreach ($config_passwords as $config_var)
	{
		if (isset($_POST[$config_var][1]) && $_POST[$config_var][0] == $_POST[$config_var][1])
			$config_vars[$config_var] = '\'' . addcslashes($_POST[$config_var][0], "'\\") . '\'';
	}
	foreach ($config_strs as $config_var)
	{
		if (isset($_POST[$config_var]))
			$config_vars[$config_var] = '\'' . addcslashes($_POST[$config_var], "'\\") . '\'';
	}
	foreach ($config_ints as $config_var)
	{
		if (isset($_POST[$config_var]))
			$config_vars[$config_var] = (int) $_POST[$config_var];
	}
	foreach ($config_bools as $key)
	{
		if (!empty($_POST[$key]))
			$config_vars[$key] = '1';
		else
			$config_vars[$key] = '0';
	}

	require_once($sourcedir . '/Subs-Admin.php');
	updateSettingsFile($config_vars);

	// If the cookie name was changed, reset the cookie.
	if (isset($config_vars['cookiename']) && $cookiename != $_POST['cookiename'])
	{
		include_once($sourcedir . '/Subs-Auth.php');
		$cookiename = $_POST['cookiename'];
		setLoginCookie(60 * $modSettings['cookieTime'], $user_settings['id_member'], sha1($user_settings['passwd'] . $user_settings['password_salt']));

		redirectexit('action=admin;area=serversettings;sa=core;sesc=' . $sc, $context['server']['needs_login_fix']);
	}

	redirectexit('action=admin;area=serversettings;sa=core;sesc=' . $sc);
}

// This function basically edits anything which is configuration and stored in the database, except for caching.
function ModifyOtherSettings($return_config = false)
{
	global $context, $scripturl, $txt, $helptxt, $sc, $modSettings;

	// In later life we may move the setting definitions out of the language files, but for now it's RC2 and I can't be bothered.
	loadLanguage('ManageSettings');

	// Define the variables we want to edit.
	$config_vars = array(
			// Cookies...
			array('int', 'cookieTime'),
			array('check', 'localCookies'),
			array('check', 'globalCookies'),
		'',
			// Database repair, optimization, etc.
			array('int', 'autoOptMaxOnline'),
			array('check', 'autoFixDatabase'),
		'',
			array('check', 'enableCompressedOutput'),
			array('check', 'databaseSession_enable'),
			array('check', 'databaseSession_loose'),
			array('int', 'databaseSession_lifetime'),
	);

	if ($return_config)
		return $config_vars;

	// Are we saving?
	if (isset($_GET['save']))
	{
		// Make the SMTP password a little harder to see in a backup etc.
		if (!empty($_POST['smtp_password'][1]))
		{
			$_POST['smtp_password'][0] = base64_encode($_POST['smtp_password'][0]);
			$_POST['smtp_password'][1] = base64_encode($_POST['smtp_password'][1]);
		}
		saveDBSettings($config_vars);
		redirectexit('action=admin;area=serversettings;sa=other;sesc=' . $sc);
	}

	$context['post_url'] = $scripturl . '?action=admin;area=serversettings;save;sa=other';
	$context['settings_title'] = $txt['other_configuration'];

	// Prepare the template.
	prepareDBSettingContext($config_vars);
}

// Simply modifying cache functions
function ModifyCacheSettings($return_config = false)
{
	global $context, $scripturl, $txt, $helptxt, $sc, $modSettings;

	// Cache information is in here, honest.
	loadLanguage('ManageSettings');

	// Define the variables we want to edit.
	$config_vars = array(
		// Only a couple of settings, but they are important
		array('select', 'cache_enable', array($txt['cache_off'], $txt['cache_level1'], $txt['cache_level2'], $txt['cache_level3'])),
		array('text', 'cache_memcached'),
	);

	if ($return_config)
		return $config_vars;

	// Saving again?
	if (isset($_GET['save']))
	{
		saveDBSettings($config_vars);
		redirectexit('action=admin;area=serversettings;sa=cache;sesc=' . $sc);
	}

	$context['post_url'] = $scripturl . '?action=admin;area=serversettings;save;sa=cache';
	$context['settings_title'] = $txt['caching_settings'];
	$context['settings_message'] = $txt['caching_information'];

	// Detect an optimizer?
	if (function_exists('eaccelerator_put'))
		$detected = 'eAccelerator';
	elseif (function_exists('mmcache_put'))
		$detected = 'MMCache';
	elseif (function_exists('apc_store'))
		$detected = 'APC';
	elseif (function_exists('output_cache_put'))
		$detected = 'Zend';
	else
		$detected = 'no_caching';

	$context['settings_message'] = sprintf($context['settings_message'], $txt['detected_' . $detected]);

	// Prepare the template.
	prepareDBSettingContext($config_vars);
}

// Helper function, it sets up the context for database settings.
function prepareDBSettingContext(&$config_vars)
{
	global $txt, $helptxt, $context, $modSettings, $sourcedir;

	loadLanguage('Help');

	$context['config_vars'] = array();
	$inlinePermissions = array();
	$bbcChoice = array();
	foreach ($config_vars as $config_var)
	{
		// HR?
		if (!is_array($config_var))
			$context['config_vars'][] = $config_var;
		else
		{
			// If it has no name it doesn't have any purpose!
			if (empty($config_var[1]))
				continue;

			// Special case for inline permissions
			if ($config_var[0] == 'permissions' && allowedTo('manage_permissions'))
				$inlinePermissions[] = $config_var[1];
			elseif ($config_var[0] == 'permissions')
				continue;

			// Are we showing the BBC selection box?
			if ($config_var[0] == 'bbc')
				$bbcChoice[] = $config_var[1];

			$context['config_vars'][$config_var[1]] = array(
				'label' => isset($txt[$config_var[1]]) ? $txt[$config_var[1]] : (isset($config_var[3]) && !is_array($config_var[3]) ? $config_var[3] : ''),
				'help' => isset($helptxt[$config_var[1]]) ? $config_var[1] : '',
				'type' => $config_var[0],
				'size' => !empty($config_var[2]) && !is_array($config_var[2]) ? $config_var[2] : (in_array($config_var[0], array('int', 'float')) ? 6 : 0),
				'data' => array(),
				'name' => $config_var[1],
				'value' => isset($modSettings[$config_var[1]]) ? htmlspecialchars($modSettings[$config_var[1]]) : (in_array($config_var[0], array('int', 'float')) ? 0 : ''),
				'disabled' => false,
				'invalid' => !empty($config_var['invalid']),
				'javascript' => '',
				'preinput' => isset($config_var['preinput']) ? $config_var['preinput'] : '',
				'postinput' => isset($config_var['postinput']) ? $config_var['postinput'] : '',
			);

			// If this is a select box handle any data.
			if (!empty($config_var[2]) && is_array($config_var[2]))
			{
				// If it's associative
				if (isset($config_var[2][0]) && is_array($config_var[2][0]))
					$context['config_vars'][$config_var[1]]['data'] = $config_var[2];
				else
				{
					foreach ($config_var[2] as $key => $item)
						$context['config_vars'][$config_var[1]]['data'][] = array($key, $item);
				}
			}

			// Finally allow overrides - and some final cleanups.
			foreach ($config_var as $k => $v)
			{
				if (!is_numeric($k))
				{
					if (substr($k, 0, 2) == 'on')
						$context['config_vars'][$config_var[1]]['javascript'] .= " $k=\"$v\"";
					else
						$context['config_vars'][$config_var[1]][$k] = $v;
				}

				// See if there are any other labels that might fit?
				if (isset($txt['setting_' . $config_var[1]]))
					$context['config_vars'][$config_var[1]]['label'] = $txt['setting_' . $config_var[1]];
				elseif (isset($txt['groups_' . $config_var[1]]))
					$context['config_vars'][$config_var[1]]['label'] = $txt['groups_' . $config_var[1]];
			}
		}
	}

	// If we have inline permissions we need to prep them.
	if (!empty($inlinePermissions) && allowedTo('manage_permissions'))
	{
		require_once($sourcedir .'/ManagePermissions.php');
		init_inline_permissions($inlinePermissions, isset($context['permissions_excluded']) ? $context['permissions_excluded'] : array());
	}

	// What about any BBC selection boxes?
	if (!empty($bbcChoice))
	{
		// What are the options, eh?
		$temp = parse_bbc(false);
		$bbcTags = array();
		foreach ($temp as $tag)
			$bbcTags[] = $tag['tag'];

		$bbcTags = array_unique($bbcTags);
		$totalTags = count($bbcTags);

		// The number of columns we want to show the BBC tags in.
		$numColumns = isset($context['num_bbc_columns']) ? $context['num_bbc_columns'] : 3;

		// Start working out the context stuff.
		$context['bbc_columns'] = array();
		$tagsPerColumn = ceil($totalTags / $numColumns);

		$col = 0; $i = 0;
		foreach ($bbcTags as $tag)
		{
			if ($i % $tagsPerColumn == 0 && $i != 0)
				$col++;

			$context['bbc_columns'][$col][] = array(
				'tag' => $tag,
				// !!! 'tag_' . ?
				'show_help' => isset($helptxt[$tag]),
			);

			$i++;
		}

		// Now put whatever BBC options we may have into context too!
		$context['bbc_sections'] = array();
		foreach ($bbcChoice as $bbc)
		{
			$context['bbc_sections'][$bbc] = array(
				'title' => isset($txt['bbc_title_' . $bbc]) ? $txt['bbc_title_' . $bbc] : $txt['bbcTagsToUse_select'],
				'disabled' => empty($modSettings['bbc_disabled_' . $bbc]) ? array() : $modSettings['bbc_disabled_' . $bbc],
				'all_selected' => empty($modSettings['bbc_disabled_' . $bbc]),
			);
		}
	}
}

// Helper function for saving database settings.
function saveDBSettings(&$config_vars)
{
	global $sourcedir, $context;

	$inlinePermissions = array();
	foreach ($config_vars as $var)
	{
		if (!isset($var[1]) || (!isset($_POST[$var[1]]) && $var[0] != 'check' && ($var[0] != 'bbc' || !isset($_POST[$var[1] . '_enabledTags']))))
			continue;

		// Checkboxes!
		elseif ($var[0] == 'check')
			$setArray[$var[1]] = !empty($_POST[$var[1]]) ? '1' : '0';
		// Select boxes!
		elseif ($var[0] == 'select' && in_array($_POST[$var[1]], array_keys($var[2])))
			$setArray[$var[1]] = $_POST[$var[1]];
		// Integers!
		elseif ($var[0] == 'int')
			$setArray[$var[1]] = (int) $_POST[$var[1]];
		// Floating point!
		elseif ($var[0] == 'float')
			$setArray[$var[1]] = (float) $_POST[$var[1]];
		// Text!
		elseif ($var[0] == 'text' || $var[0] == 'large_text')
			$setArray[$var[1]] = $_POST[$var[1]];
		// Passwords!
		elseif ($var[0] == 'password')
		{
			if (isset($_POST[$var[1]][1]) && $_POST[$var[1]][0] == $_POST[$var[1]][1])
				$setArray[$var[1]] = $_POST[$var[1]][0];
		}
		// BBC.
		elseif ($var[0] == 'bbc')
		{

			$bbcTags = array();
			foreach (parse_bbc(false) as $tag)
				$bbcTags[] = $tag['tag'];

			if (!isset($_POST[$var[1] . '_enabledTags']))
				$_POST[$var[1] . '_enabledTags'] = array();
			elseif (!is_array($_POST[$var[1] . '_enabledTags']))
				$_POST[$var[1] . '_enabledTags'] = array($_POST[$var[1] . '_enabledTags']);

			$setArray[$var[1]] = implode(',', array_diff($bbcTags, $_POST[$var[1] . '_enabledTags']));
		}
		// Permissions?
		elseif ($var[0] == 'permissions')
			$inlinePermissions[] = $var[1];
	}

	updateSettings($setArray);

	// If we have inline permissions we need to save them.
	if (!empty($inlinePermissions) && allowedTo('manage_permissions'))
	{
		require_once($sourcedir .'/ManagePermissions.php');
		save_inline_permissions($inlinePermissions);
	}
}

?>