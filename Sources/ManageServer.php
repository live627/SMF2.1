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

	void ModifyLanguageSettings()
		// !!!

	void ModifyLanguage()
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
		'editlang' => 'ModifyLanguage',
		'languages' => 'ModifyLanguageSettings',
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

// This lists all the current languages and allows editing of them.
function ModifyLanguageSettings()
{
	global $txt, $db_prefix, $context, $scripturl;
	global $user_info, $smfFunc, $sourcedir, $language, $boarddir, $forum_version;

	// Setting a new default?
	if (!empty($_POST['set_default']) && !empty($_POST['def_language']))
	{
		if ($_POST['def_language'] != $language)
		{
			require_once($sourcedir . '/Subs-Admin.php');
			updateSettingsFile(array('language' => "'$_POST[def_language]'"));
			$language = $_POST['def_language'];
		}
	}

	loadLanguage('ManageSettings');
	$context['page_title'] = $txt['edit_languages'];

	$listOptions = array(
		'id' => 'language_list',
		'items_per_page' => 20,
		'base_href' => $scripturl . '?action=admin;area=serversettings;sa=languages;sesc=' . $context['session_id'],
		'title' => $txt['edit_languages'],
		'get_items' => array(
			'function' => 'list_getLanguages',
		),
		'get_count' => array(
			'function' => 'list_getNumLanguages',
		),
		'columns' => array(
			'default' => array(
				'header' => array(
					'value' => $txt['languages_default'],
				),
				'data' => array(
					'function' => create_function('$rowData', '					
						return \'<input type="radio" name="def_language" value="\' . $rowData[\'id\'] . \'" \' . ($rowData[\'default\'] ? \'checked="checked"\' : \'\') . \' onclick="highlightSelected(\\\'list_language_list_\' . $rowData[\'id\'] . \'\\\');" class="check" />\';
					'),
					'style' => 'text-align: center; width: 4%;',
				),
			),
			'name' => array(
				'header' => array(
					'value' => $txt['languages_lang_name'],
				),
				'data' => array(
					'function' => create_function('$rowData', '
						global $scripturl, $context;
					
						return sprintf(\'<a href="%1$s?action=admin;area=serversettings;sa=editlang;lid=%2$s;sesc=%3$s">%4$s</a>\', $scripturl, $rowData[\'id\'], $context[\'session_id\'], $rowData[\'name\']);
					'),
				),
			),
			'character_set' => array(
				'header' => array(
					'value' => $txt['languages_character_set'],
				),
				'data' => array(
					'db_htmlsafe' => 'char_set',
				),
			),
			'count' => array(
				'header' => array(
					'value' => $txt['languages_users'],
				),
				'data' => array(
					'db_htmlsafe' => 'count',
					'style' => 'text-align: center',
				),
			),
			'locale' => array(
				'header' => array(
					'value' => $txt['languages_locale'],
				),
				'data' => array(
					'db_htmlsafe' => 'locale',
				),
			),
		),
		'form' => array(
			'href' => $scripturl . '?action=admin;area=serversettings;sa=languages;sesc=' . $context['session_id'],
		),
		'additional_rows' => array(
			array(
				'position' => 'below_table_data',
				'value' => '<input type="submit" name="set_default" value="' . $txt['save'] . '" ' . (is_writable($boarddir . '/Settings.php') ? '' : 'disabled="disabled"') . '/>',
				'class' => 'titlebg',
				'style' => 'text-align: right;',
			),
		),
		// For highlighting the default.
		'javascript' => '
					var prevClass = "";
					var prevDiv = "";
					function highlightSelected(box)
					{
						if (prevClass != "")
						{
							prevDiv.className = prevClass;
						}
						prevDiv = document.getElementById(box);
						prevClass = prevDiv.className;

						prevDiv.className = "highlight2";				
					}
					highlightSelected("list_language_list_' . ($language == '' ? 'english' : $language). '");
		',
	);

	// Display a warning if we cannot edit the default setting.
	if (!is_writable($boarddir . '/Settings.php'))
		$listOptions['additional_rows'][] = array(
				'position' => 'after_title',
				'value' => '<span class="smalltext" style="color: red;">' . $txt['language_settings_writable'] . '</span>',
				'class' => 'windowbg',
			);

	require_once($sourcedir . '/Subs-List.php');
	createList($listOptions);

	// Are we searching for new languages courtesy of Simple Machines?
	if (!empty($_POST['smf_add_sub']))
	{
		// Need fetch_web_data.
		require_once($sourcedir . '/Subs-Package.php');
		
		$context['smf_search_term'] = trim($_POST['smf_add']);

		// We're going to use this URL.
		$url = 'http://www.simplemachines.org/download/fetch_language.php?version=' . urlencode(strtr($forum_version, array('SMF ' => '')));

		// Load the class file and stick it into an array.
		loadClassFile('Class-Package.php');
		$language_list = new xmlArray(fetch_web_data($url), true);

		// Check it exists.
		if (!$language_list->exists('languages'))
			$context['smf_error'] = 'no_response';
		else
		{
			$language_list = $language_list->path('languages[0]');
			$lang_files = $language_list->set('language');
			$context['smf_languages'] = array();
			foreach ($lang_files as $file)
			{
				// Were we searching?
				if (!empty($context['smf_search_term']) && strpos($file->fetch('name'), $context['smf_search_term']) === false)
					continue;

				$context['smf_languages'][] = array(
					'id' => $file->fetch('id'),
					'name' => $smfFunc['ucwords']($file->fetch('name')),
					'version' => $file->fetch('version'),
					'utf8' => $file->fetch('utf8'),
					'description' => $file->fetch('description'),
					'link' => $scripturl . '?action=admin;area=packages;get;sa=download;package=' . $url . ';fetch=' . urlencode($file->fetch('id')) . ';sesc=' . $context['session_id'],
				);
			}
			if (empty($context['smf_languages']))
				$context['smf_error'] = 'no_files';
		}
	}

	$context['sub_template'] = 'language_files';
	$context['default_list'] = 'language_list';
}

// How many languages?
function list_getNumLanguages()
{
	global $settings;

	$count = 0;

	$dir = dir($settings['default_theme_dir'] . '/languages');
	while ($entry = $dir->read())
	{
		// We're only after the index.language.php file.
		if (preg_match('~^index\.(.+)\.php$~', $entry, $matches) == 0)
			continue;

		$count++;
	}
	$dir->close();

	// Return how many we have.
	return $count;
}

// Fetch the actual language information.
function list_getLanguages()
{
	global $settings, $smfFunc, $language, $db_prefix, $txt;

	$languages = array();
	// Keep our old entries.
	$old_txt = $txt;

	// Get the language files and data...
	$dir = dir($settings['default_theme_dir'] . '/languages');
	while ($entry = $dir->read())
	{
		// We're only after the index.language.php file.
		if (preg_match('~^index\.([A-Za-z0-9]+)\.php$~', $entry, $matches) == 0)
			continue;

		// Load the file to get the character set.
		require_once($settings['default_theme_dir'] . '/languages/' . $matches[0]);

		$languages[$matches[1]] = array(
			'id' => $matches[1],
			'count' => 0,
			'char_set' => $txt['lang_character_set'],
			'default' => $language == $matches[1] || ($language == '' && $matches[1] == 'english'),
			'locale' => $txt['lang_locale'],
			'name' => $smfFunc['ucwords'](strtr($matches[1], array('_' => ' ', '-utf8' => ''))),
		);
	}
	$dir->close();

	// Work out how many people are using each language.
	$request = $smfFunc['db_query']('', "
		SELECT lngfile, COUNT(*) AS num_users
		FROM {$db_prefix}members
		GROUP BY lngfile", __FILE__, __LINE__);
	while ($row = $smfFunc['db_fetch_assoc']($request))
	{
		// Default?
		if (empty($row['lngfile']) || !isset($languages[$row['lngfile']]))
			$row['lngfile'] = $language;

		if (!isset($languages[$row['lngfile']]) && isset($languages['english']))
			$languages['english']['count'] += $row['num_users'];
		elseif (isset($languages[$row['lngfile']]))
			$languages[$row['lngfile']]['count'] += $row['num_users'];
	}
	$smfFunc['db_free_result']($request);

	// Restore the current users language.
	$txt = $old_txt;

	// Return how many we have.
	return $languages;
}

// Edit a particular set of language entries.
function ModifyLanguage()
{
	global $settings, $context, $smfFunc, $db_prefix, $txt;

	loadLanguage('ManageSettings');

	// Select the languages tab.
	$context['menu_data_' . $context['admin_menu_id']]['current_subsection'] = 'languages';
	$context['page_title'] = $txt['edit_languages'];
	$context['sub_template'] = 'modify_language_entries';

	$context['lang_id'] = $_GET['lid'];
	list($theme_id, $file_id) = empty($_REQUEST['tfid']) ? array(1, '') : explode('+', $_REQUEST['tfid']);

	// Clean the ID - just incase.
	preg_match('~([A-Za-z0-9]+)~', $context['lang_id'], $matches);
	$context['lang_id'] = $matches[1];

	// Get all the theme data.
	$request = $smfFunc['db_query']('', "
		SELECT id_theme, variable, value
		FROM {$db_prefix}themes
		WHERE id_theme != 1
			AND id_member = 0
			AND variable IN ('name', 'theme_dir')", __FILE__, __LINE__);
	$themes = array(
		1 => array(
			'name' => $txt['dvc_default'],
			'theme_dir' => $settings['default_theme_dir'],
		),
	);
	while ($row = $smfFunc['db_fetch_assoc']($request))
		$themes[$row['id_theme']][$row['variable']] = $row['value'];
	$smfFunc['db_free_result']($request);

	// This will be where we look
	$lang_dirs = array();
	// Check we have themes with a path and a name - just incase - and add the path.
	foreach ($themes as $id => $data)
	{
		if (count($data) != 2)
			unset($themes[$id]);
		elseif (is_dir($data['theme_dir'] . '/languages'))
			$lang_dirs[$id] = $data['theme_dir'] . '/languages';
	}

	$current_file = $file_id ? $lang_dirs[$theme_id] . '/' . $file_id . '.' . $context['lang_id'] . '.php' : '';

	// Now for every theme get all the files and stick them in context!
	$context['possible_files'] = array();
	foreach ($lang_dirs as $theme => $theme_dir)
	{
		// Open it up.
		$dir = dir($theme_dir);
		while ($entry = $dir->read())
		{
			// We're only after the files for this language.
			if (preg_match('~^([A-Za-z]+)\.' . $context['lang_id'] . '\.php$~', $entry, $matches) == 0)
				continue;
	
			//!!! Temp!
			if ($matches[1] == 'EmailTemplates')
				continue;

			if (!isset($context['possible_files'][$theme]))
				$context['possible_files'][$theme] = array(
					'id' => $theme,
					'name' => $themes[$theme]['name'],
					'files' => array()
				);

			$context['possible_files'][$theme]['files'][] = array(
				'id' => $matches[1],
				'name' => isset($txt['lang_file_desc_' . $matches[1]]) ? $txt['lang_file_desc_' . $matches[1]] : $matches[1],
				'selected' => $theme_id == $theme && $file_id == $matches[1],
			);
		}
		$dir->close();
	}

	// Saving primary settings?
	if (!empty($_POST['save_main']))
	{
		// Read in the current file.
		$current_data = implode('', file($settings['default_theme_dir'] . '/languages/index.' . $context['lang_id'] . '.php'));
		// These are the replacements. old => new
		$replace_array = array(
			'~\$txt\[\'lang_character_set\'\]\s=\s(\'|")[^\'"]+(\'|");~' => '$txt[\'lang_character_set\'] = \'' . addslashes($_POST['character_set']) . '\';',
			'~\$txt\[\'lang_locale\'\]\s=\s(\'|")[^\'"]+(\'|");~' => '$txt[\'lang_locale\'] = \'' . addslashes($_POST['locale']) . '\';',
			'~\$txt\[\'lang_dictionary\'\]\s=\s(\'|")[^\'"]+(\'|");~' => '$txt[\'lang_dictionary\'] = \'' . addslashes($_POST['dictionary']) . '\';',
			'~\$txt\[\'lang_spelling\'\]\s=\s(\'|")[^\'"]+(\'|");~' => '$txt[\'lang_spelling\'] = \'' . addslashes($_POST['spelling']) . '\';',
			'~\$txt\[\'lang_rtl\'\]\s=\s[A-Za-z0-9]+;~' => '$txt[\'lang_rtl\'] = ' . (!empty($_POST['rtl']) ? 'true' : 'false') . ';',
		);
		$current_data = preg_replace(array_keys($replace_array), array_values($replace_array), $current_data);
		$fp = fopen($settings['default_theme_dir'] . '/languages/index.' . $context['lang_id'] . '.php', 'w+');
		fwrite($current_data, $fp);
		fclose($fp);
	}

	// Quickly load index language entries.
	$old_txt = $txt;
	require($settings['default_theme_dir'] . '/languages/index.' . $context['lang_id'] . '.php');
	$context['lang_file_not_writable_message'] = is_writable($settings['default_theme_dir'] . '/languages/index.' . $context['lang_id'] . '.php') ? '' : sprintf($txt['lang_file_not_writable'], $settings['default_theme_dir'] . '/languages/index.' . $context['lang_id'] . '.php');
	// Setup the primary settings context.
	$context['primary_settings'] = array(
		'name' => $smfFunc['ucwords'](strtr($context['lang_id'], array('_' => ' ', '-utf8' => ''))),
		'character_set' => $txt['lang_character_set'],
		'locale' => $txt['lang_locale'],
		'dictionary' => $txt['lang_dictionary'],
		'spelling' => $txt['lang_spelling'],
		'rtl' => $txt['lang_rtl'],
	);

	// Restore normal service.
	$txt = $old_txt;

	// Are we saving?
	$save_strings = array();
	if (isset($_POST['save_entries']) && !empty($_POST['entry']))
	{
		// Clean each entry!
		foreach ($_POST['entry'] as $k => $v)
		{
			// Only try to save if it's changed!
			if ($_POST['entry'][$k] != $_POST['comp'][$k])
				$save_strings[$k] = cleanLangString($v, false);
		}
	}

	// If we are editing a file work away at that.
	if ($current_file)
	{
		$context['entries_not_writable_message'] = is_writable($current_file) ? '' : sprintf($txt['lang_entries_not_writable'], $current_file);

		$entries = array();
		// We can't just require it I'm afraid - otherwise we pass in all kinds of variables!
		$multiline_cache = '';
		foreach (file($current_file) as $line)
		{
			// Got a new entry?
			if ($line{0} == '$' && !empty($multiline_cache))
			{
				preg_match('~\$(helptxt|txt)\[\'(.+)\'\]\s=\s(.+);~', strtr($multiline_cache, array("\n" => '', "\t" => '')), $matches);
				if (!empty($matches[3]))
				{
					$entries[$matches[2]] = array(
						'type' => $matches[1],
						'full' => $matches[0],
						'entry' => $matches[3],
					);
					$multiline_cache = '';
				}
			}
			$multiline_cache .= $line . "\n";
		}
		// Last entry to add?
		if ($multiline_cache)
		{
			preg_match('~\$(helptxt|txt)\[\'(.+)\'\]\s=\s(.+);~', strtr($multiline_cache, array("\n" => '', "\t" => '')), $matches);
			if (!empty($matches[3]))
				$entries[$matches[2]] = array(
					'type' => $matches[1],
					'full' => $matches[0],
					'entry' => $matches[3],
				);
		}

		// These are the entries we can definitely save.
		$final_saves = array();

		$context['file_entries'] = array();
		foreach ($entries as $k => $v)
		{
			// Ignore some things we set separately.
			$ignore_files = array('lang_character_set', 'lang_locale', 'lang_dictionary', 'lang_spelling', 'lang_rtl');
			if (in_array($k, $ignore_files))
				continue;

			// These are arrays that need breaking out.
			$arrays = array('days', 'days_short', 'months', 'months_titles', 'months_short');
			if (in_array($k, $arrays))
			{
				// Get off the first bits.
				$v['entry'] = substr($v['entry'], strpos($v['entry'], "(") + 1, strrpos($v['entry'], ")") - strpos($v['entry'], "("));
				$v['entry'] = explode(",", strtr($v['entry'], array(' ' => '')));

				// Now create an entry for each item.
				$cur_index = 0;
				$save_cache = array(
					'enabled' => false,
					'entries' => array(),
				);
				foreach ($v['entry'] as $id => $v2)
				{
					// Is this a new index?
					if (preg_match('~^(\d+)~', $v2, $matches))
					{
						$cur_index = $matches[1];
						$v2 = substr($v2, strpos($v2, "'"));
					}

					// Clean up some bits.
					$v2 = strtr($v2, array('"' => '', "'" => '', ')' => ''));

					// Can we save?
					if (isset($save_strings[$k . '-+- ' . $cur_index]))
					{
						$save_cache['entries'][$cur_index] = strtr($save_strings[$k . '-+- ' . $cur_index], array("'" => ''));
						$save_cache['enabled'] = true;
					}
					else
						$save_cache['entries'][$cur_index] = $v2;

					$context['file_entries'][] = array(
						'key' => $k . '-+- ' . $cur_index,
						'value' => $v2,
						'rows' => 1,
					);
					$cur_index++;
				}

				// Do we need to save?
				if ($save_cache['enabled'])
				{
					// Format the string, checking the indexes first.
					$items = array();
					$cur_index = 0;
					foreach ($save_cache['entries'] as $k2 => $v2)
					{
						// Manually show the custom index.
						if ($k2 != $cur_index)
						{
							$items[] = $k2 . " => '" . $v2 . "'";
							$cur_index = $k2;
						}
						else
							$items[] = "'$v2'";

						$cur_index++;
					}
					// Now create the string!
					$final_saves[$k] = array(
						'find' => $v['full'],
						'replace' => '$' . $v['type'] . '[\'' . $k . '\'] = array(' . implode(', ', $items) . ');',
					);
				}
			}
			else
			{
				// Saving?
				if (isset($save_strings[$k]) && $save_strings[$k] != $v['entry'])
				{
					// Set the new value.
					$v['entry'] = $save_strings[$k];
					// And we know what to save now!
					$final_saves[$k] = array(
						'find' => $v['full'],
						'replace' => '$' . $v['type'] . '[\'' . $k . '\'] = ' . $save_strings[$k] . ';',
					);
				}

				$context['file_entries'][] = array(
					'key' => $k,
					'value' => cleanLangString($v['entry'], true),
					'rows' => 1,
				);
			}
		}

		// Any saves to make?
		if (!empty($final_saves))
		{
			$file_contents = implode('', file($current_file));
			foreach ($final_saves as $save)
				$file_contents = strtr($file_contents, array($save['find'] => $save['replace']));

			// Save the actual changes.
			//$fp = fopen($current_file, 'w+');
			//fwrite($fp, $file_contents);
			//fclose($fp);

			clean_cache('lang');
		}

		// Another restore.
		$txt = $old_txt;
	}
}

// This function could be two functions - either way it cleans language entries to/from display.
function cleanLangString($string, $to_display = true)
{
	global $smfFunc;

	// If going to display we make sure it doesn't have any HTML in it - etc.
	$new_string = '';
	if ($to_display)
	{
		// Are we in a string (0 = no, 1 = single quote, 2 = parsed)
		$in_string = 0;
		$is_escape = false;
		for ($i = 0; $i < strlen($string); $i++)
		{
			// Handle ecapes first.
			if ($string{$i} == '\\')
			{
				// Toggle the escape.
				$is_escape = !$is_escape;
				// If we're now escaped don't add this string.
				if ($is_escape)
					continue;
			}
			// Special case - parsed string with line break etc?
			elseif (($string{$i} == 'n' || $string{$i} == 't') && $in_string == 2 && $is_escape)
			{
				// Put the escape back...
				$new_string .= $string{$i} == 'n' ? "\n" : "\t";
				$is_escape = false;
				continue;
			}
			// Have we got a single quote?
			elseif ($string{$i} == "'")
			{
				// Already in a parsed string, or escaped in a linear string, means we print it - otherwise something special.
				if ($in_string != 2 && ($in_string != 1 || !$is_escape))
				{
					// Is it the end of a single quote string?
					if ($in_string == 1)
						$in_string = 0;
					// Otherwise it's the start!
					else
						$in_string = 1;

					// Don't actually include this character!
					continue;
				}
			}
			// Otherwise a double quote?
			elseif ($string{$i} == '"')
			{
				// Already in a single quote string, or escaped in a parsed string, means we print it - otherwise something special.
				if ($in_string != 1 && ($in_string != 2 || !$is_escape))
				{
					// Is it the end of a double quote string?
					if ($in_string == 2)
						$in_string = 0;
					// Otherwise it's the start!
					else
						$in_string = 2;

					// Don't actually include this character!
					continue;
				}
			}
			// A join/space outside of a string is simply removed.
			elseif ($in_string == 0 && (empty($string{$i}) || $string{$i} == '.'))
				continue;
			// Start of a variable?
			elseif ($in_string == 0 && $string{$i} == '$')
			{
				// Find the whole of it!
				preg_match('~([\$A-Za-z0-9\'\[\]_-]+)~', substr($string, $i), $matches);
				if (!empty($matches[1]))
				{
					// Come up with some pseudo thing to indicate this is a var.
					//!!! Do better than this, please!
					$new_string .= '{%' . $matches[1] . '%}';

					// We're not going to reparse this.
					$i += strlen($matches[1]) - 1;
				}

				continue;
			}
			// Right, if we're outside of a string we have DANGER, DANGER!
			elseif ($in_string == 0)
			{
				continue;
			}

			// Actually add the character to the string!
			$new_string .= $string{$i};
			// If anything was escaped it ain't any longer!
			$is_escape = false;
		}
		
		// Unhtml then rehtml the whole thing!
		$new_string = htmlspecialchars(un_htmlspecialchars($new_string));
	}
	else
	{
		// Would have been escaped - sadly!
		$string = $smfFunc['db_unescape_string']($string);

		// Keep track of what we're doing...
		$in_string = 0;
		// This is for deciding whether to HTML a quote.
		$in_html = false;
		for ($i = 0; $i < strlen($string); $i++)
		{
			// Handle line breaks!
			if ($string{$i} == "\n" || $string{$i} == "\t")
			{
				// Are we in a string? Is it the right type?
				if ($in_string == 1)
				{
					// Change type!
					$new_string .= '\' . "\\' . ($string{$i} == "\n" ? 'n' : 't');
					$in_string = 2;
				}
				elseif ($in_string == 2)
					$new_string .= '\\' . ($string{$i} == "\n" ? 'n' : 't');
				// Otherwise start one off - joining if required.
				else
					$new_string .= ($new_string ? ' . ' : '') . '"\\' . ($string{$i} == "\n" ? 'n' : 't');

				continue;
			}
			// We don't do parsed strings apart from for breaks.
			elseif ($in_string == 2)
			{
				$in_string = 0;
				$new_string .= '"';
			}

			// Not in a string yet?
			if ($in_string != 1)
			{
				$in_string = 1;
				$new_string .= ($new_string ? ' . ' : '') . "'";
			}

			// Is this a variable?
			if ($string{$i} == '{' && $string{$i + 1} == '%' && $string{$i + 2} == '$')
			{
				// Grab the variable.
				preg_match('~\{%([\$A-Za-z0-9\'\[\]_-]+)%\}~', substr($string, $i), $matches);
				if (!empty($matches[1]))
				{
					if ($in_string == 1)
						$new_string .= "' . ";
					elseif ($new_string)
						$new_string .= ' . ';

					$new_string .= $matches[1];
					$i += strlen($matches[1]) + 3;
					$in_string = 0;
				}

				continue;
			}
			// Is this a lt sign?
			elseif ($string{$i} == '<')
			{
				// Probably HTML?
				if ($string{$i + 1} != ' ')
					$in_html = true;
				// Assume we need an entity...
				else
				{
					$new_string .= '&lt;';
					continue;
				}
			}
			// What about gt?
			elseif ($string{$i} == '>')
			{
				// Will it be HTML?
				if ($in_html)
					$in_html = false;
				// Otherwise we need an entity...
				else
				{
					$new_string .= '&gt;';
					continue;
				}
			}
			// Is it a slash? If so escape it...
			if ($string{$i} == '\\')
				$new_string .= '\\';
			// The infamous double quote?
			elseif ($string{$i} == '"')
			{
				// If we're in HTML we leave it as a quote - otherwise we entity it.
				if (!$in_html)
				{
					$new_string .= '&quot;';
					continue;
				}
			}
			// A single quote?
			elseif ($string{$i} == "'")
			{
				// Must be in a string so escape it.
				$new_string .= '\\';
			}

			// Finally add the character to the string!
			$new_string .= $string{$i};
		}

		// If we ended as a string then close it off.
		if ($in_string == 1)
			$new_string .= "'";
		elseif ($in_string == 2)
			$new_string .= '"';
	}

	return $new_string;
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