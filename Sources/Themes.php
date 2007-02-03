<?php
/**********************************************************************************
* Themes.php                                                                      *
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

/*	This file concerns itself almost completely with theme administration.
	Its tasks include changing theme settings, installing and removing
	themes, choosing the current theme, and editing themes.  This is done in:

	void ThemesMain()
		- manages the action and delegates control to the proper sub action.
		- loads both the Themes and Settings language files.
		- checks the session by GET or POST to verify the sent data.
		- requires the user not be a guest.
		- is accessed via ?action=admin;area=theme.

	void ThemeAdmin()
		- administrates themes and their settings, as well as global theme
		  settings.
		- sets the settings theme_allow, theme_guests, and knownThemes.
		- loads the template Themes.
		- requires the admin_forum permission.
		- accessed with ?action=admin;area=theme;sa=admin.

	void ThemeList()
		- lists the available themes.
		- provides an interface to reset the paths of all the installed themes.

	void SetThemeOptions()
		// !!!

	void SetThemeSettings()
		- saves and requests global theme settings. ($settings)
		- loads the Admin language file.
		- calls ThemeAdmin() if no theme is specified. (the theme center.)
		- requires an administrator.
		- accessed with ?action=admin;area=theme;sa=settings&id=xx.

	void RemoveTheme()
		- removes an installed theme.
		- requires an administrator.
		- accessed with ?action=admin;area=theme;sa=remove.

	void PickTheme()
		- allows user or administrator to pick a new theme with an interface.
		- can edit everyone's (u = 0), guests' (u = -1), or a specific user's.
		- uses the Themes template. (pick sub template.)
		- accessed with ?action=admin;area=theme;sa=pick.

	void ThemeInstall()
		- installs new themes, either from a gzip or copy of the default.
		- requires an administrator.
		- puts themes in $boardurl/Themes.
		- assumes the gzip has a root directory in it. (ie default.)
		- accessed with ?action=admin;area=theme;sa=install.

	void WrapAction()
		- allows the theme to take care of actions.
		- happens if $settings['catch_action'] is set and action isn't found
		  in the action array.
		- can use a template, layers, sub_template, filename, and/or function.

	void SetJavaScript()
		- sets a theme option without outputting anything.
		- can be used with javascript, via a dummy image... (which doesn't
		  require the page to reload.)
		- requires someone who is logged in.
		- accessed via ?action=jsoption;var=variable;val=value;sesc=sess_id.
		- does not log access to the Who's Online log. (in index.php..)

	void EditTheme()
		- shows an interface for editing the templates.
		- uses the Themes template and edit_template/edit_style sub template.
		- accessed via ?action=admin;area=theme;sa=edit

	function convert_template($output_dir, $old_template = '')
		// !!!

	function phpcodefix(string string)
		// !!!

	function makeStyleChanges(&$old_template)
		// !!!

	// !!! Update this for the new package manager?
	Creating and distributing theme packages:
	---------------------------------------------------------------------------
		There isn't that much required to package and distribute your own
		themes... just do the following:
		 - create a theme_info.xml file, with the root element theme-info.
		 - its name should go in a name element, just like description.
		 - your name should go in author. (email in the email attribute.)
		 - any support website for the theme should be in website.
		 - layers and templates (non-default) should go in those elements ;).
		 - if the images dir isn't images, specify in the images element.
		 - any extra rows for themes should go in extra, serialized.
		   (as in array(variable => value).)
		 - tar and gzip the directory - and you're done!
		 - please include any special license in a license.txt file.
	// !!! Thumbnail?
*/

// Subaction handler.
function ThemesMain()
{
	global $txt, $context, $scripturl;

	// Load the important language files...
	loadLanguage('Themes');
	loadLanguage('Settings');

	// No funny business - guests only.
	is_not_guest();

	// Default the page title to Theme Administration by default.
	$context['page_title'] = &$txt['themeadmin_title'];

	// Theme administration, removal, choice, or installation...
	$subActions = array(
		'admin' => 'ThemeAdmin',
		'list' => 'ThemeList',
		'reset' => 'SetThemeOptions',
		'settings' => 'SetThemeSettings',
		'options' => 'SetThemeOptions',
		'install' => 'ThemeInstall',
		'remove' => 'RemoveTheme',
		'pick' => 'PickTheme',
		'edit' => 'EditTheme',
		'copy' => 'CopyTemplate',
	);

	// !!! Layout Settings?
	$context['admin_tabs'] = array(
		'title' => &$txt['themeadmin_title'],
		'help' => 'themes',
		'description' => $txt['themeadmin_description'],
		'tabs' => array(
			'admin' => array(
				'title' => $txt['themeadmin_admin_title'],
				'description' => $txt['themeadmin_admin_desc'],
				'href' => $scripturl . '?action=admin;area=theme;sesc=' . $context['session_id'] . ';sa=admin',
			),
			'list' => array(
				'title' => $txt['themeadmin_list_title'],
				'description' => $txt['themeadmin_list_desc'],
				'href' => $scripturl . '?action=admin;area=theme;sesc=' . $context['session_id'] . ';sa=list',
			),
			'reset' => array(
				'title' => $txt['themeadmin_reset_title'],
				'description' => $txt['themeadmin_reset_desc'],
				'href' => $scripturl . '?action=admin;area=theme;sesc=' . $context['session_id'] . ';sa=reset',
			),
			'edit' => array(
				'title' => $txt['themeadmin_edit_title'],
				'description' => $txt['themeadmin_edit_desc'],
				'href' => $scripturl . '?action=admin;area=theme;sesc=' . $context['session_id'] . ';sa=edit',
				'is_last' => true,
			),
		),
	);

	// Follow the sa or just go to administration.
	if (!empty($subActions[$_GET['sa']]))
	{
		if (isset($context['admin_tabs']['tabs'][$_GET['sa']]))
			$context['admin_tabs']['tabs'][$_GET['sa']]['is_selected'] = true;
		$subActions[$_GET['sa']]();
	}
	else
	{
		$context['admin_tabs']['tabs']['admin']['is_selected'] = true;
		$subActions['admin']();
	}
}

function ThemeAdmin()
{
	global $context, $db_prefix, $sc, $boarddir, $modSettings, $smfFunc;

	loadLanguage('Admin');
	isAllowedTo('admin_forum');

	// If we aren't submitting - that is, if we are about to...
	if (!isset($_POST['submit']))
	{
		checkSession('get');

		loadTemplate('Themes');

		// Make our known themes a little easier to work with.
		$knownThemes = !empty($modSettings['knownThemes']) ? explode(',',$modSettings['knownThemes']) : array();

		// Load up all the themes.
		$request = $smfFunc['db_query']('', "
			SELECT id_theme, value AS name
			FROM {$db_prefix}themes
			WHERE variable = 'name'
				AND id_member = 0
			ORDER BY id_theme", __FILE__, __LINE__);
		$context['themes'] = array();
		while ($row = $smfFunc['db_fetch_assoc']($request))
		{
			$context['themes'][] = array(
				'id' => $row['id_theme'],
				'name' => $row['name'],
				'known' => in_array($row['id_theme'], $knownThemes),
			);
		}
		$smfFunc['db_free_result']($request);

		// Can we create a new theme?
		$context['can_create_new'] = is_writable($boarddir . '/Themes');
		$context['new_theme_dir'] = substr(realpath($boarddir . '/Themes/default'), 0, -7);

		// Look for a non existent theme directory. (ie theme87.)
		$theme_dir = $boarddir . '/Themes/theme';
		$i = 1;
		while (file_exists($theme_dir . $i))
			$i++;
		$context['new_theme_name'] = 'theme' . $i;
	}
	else
	{
		checkSession();

		if (isset($_POST['options']['known_themes']))
			foreach($_POST['options']['known_themes'] AS $key => $id)
				$_POST['options']['known_themes'][$key] = (int) $id;
		else
			fatal_lang_error('themes_none_selectable', false);

		if (!in_array($_POST['options']['theme_guests'], $_POST['options']['known_themes']))
				fatal_lang_error('themes_default_selectable', false);

		// Commit the new settings.
		updateSettings(array(
			'theme_allow' => $_POST['options']['theme_allow'],
			'theme_guests' => $_POST['options']['theme_guests'],
			'knownThemes' => implode(',', $_POST['options']['known_themes']),
		));
		if ((int) $_POST['theme_reset'] == 0 || in_array($_POST['theme_reset'], $_POST['options']['known_themes']))
			updateMemberData(null, array('id_theme' => (int) $_POST['theme_reset']));

		redirectexit('action=admin;area=theme;sesc=' . $sc . ';sa=admin');
	}
}

function ThemeList()
{
	global $context, $db_prefix, $boarddir, $boardurl, $smfFunc;

	loadLanguage('Admin');
	isAllowedTo('admin_forum');

	if (isset($_POST['submit']))
	{
		checkSession();

		$request = $smfFunc['db_query']('', "
			SELECT id_theme, variable, value
			FROM {$db_prefix}themes
			WHERE variable IN ('theme_dir', 'theme_url', 'images_url', 'base_theme_dir', 'base_theme_url', 'base_images_url')
				AND id_member = 0", __FILE__, __LINE__);
		$themes = array();
		while ($row = $smfFunc['db_fetch_assoc']($request))
			$themes[$row['id_theme']][$row['variable']] = $row['value'];
		$smfFunc['db_free_result']($request);

		$_POST['reset_dir'] = $smfFunc['db_unescape_string']($_POST['reset_dir']);
		$_POST['reset_url'] = $smfFunc['db_unescape_string']($_POST['reset_url']);

		$setValues = array();
		foreach ($themes as $id => $theme)
		{
			if (file_exists($_POST['reset_dir'] . '/' . basename($theme['theme_dir'])))
			{
				$setValues[] = array($id, 0, "'theme_dir'", '\'' . $smfFunc['db_escape_string'](realpath($_POST['reset_dir'] . '/' . basename($theme['theme_dir']))) . '\'');
				$setValues[] = array($id, 0, "'theme_url'", '\'' . $smfFunc['db_escape_string']($_POST['reset_url'] . '/' . basename($theme['theme_dir'])) . '\'');
				$setValues[] = array($id, 0, "'images_url'", '\'' . $smfFunc['db_escape_string']($_POST['reset_url'] . '/' . basename($theme['theme_dir'])) . "/" . basename($theme['images_url']) . '\'');
			}

			if (isset($theme['base_theme_dir']) && file_exists($_POST['reset_dir'] . '/' . basename($theme['base_theme_dir'])))
			{
				$setValues[] = array($id, 0, "'base_theme_dir'", '\'' . $smfFunc['db_escape_string'](realpath($_POST['reset_dir'] . '/' . basename($theme['base_theme_dir']))) . '\'');
				$setValues[] = array($id, 0, "'base_theme_url'", '\'' . $smfFunc['db_escape_string']($_POST['reset_url'] . '/' . basename($theme['base_theme_dir'])) . '\'');
				$setValues[] = array($id, 0, "'base_images_url'", '\'' . $smfFunc['db_escape_string']($_POST['reset_url'] . '/' . basename($theme['base_theme_dir'])) . "/" . basename($theme['base_images_url']) . '\'');
			}

			cache_put_data('theme_settings-' . $id, null, 90);
		}

		if (!empty($setValues))
		{
			$smfFunc['db_insert']('replace',
				"{$db_prefix}themes",
				array('id_theme', 'id_member', 'variable', 'value'),
				$setValues,
				array('id_theme', 'variable', 'id_member'), __FILE__, __LINE__
			);
		}

		redirectexit('action=admin;area=theme;sa=list;sesc=' . $context['session_id']);
	}

	checkSession('get');

	loadTemplate('Themes');

	$request = $smfFunc['db_query']('', "
		SELECT id_theme, variable, value
		FROM {$db_prefix}themes
		WHERE variable IN ('name', 'theme_dir', 'theme_url', 'images_url')
			AND id_member = 0", __FILE__, __LINE__);
	$context['themes'] = array();
	while ($row = $smfFunc['db_fetch_assoc']($request))
	{
		if (!isset($context['themes'][$row['id_theme']]))
			$context['themes'][$row['id_theme']] = array(
				'id' => $row['id_theme'],
			);
		$context['themes'][$row['id_theme']][$row['variable']] = $row['value'];
	}
	$smfFunc['db_free_result']($request);

	foreach ($context['themes'] as $i => $theme)
	{
		$context['themes'][$i]['theme_dir'] = realpath($context['themes'][$i]['theme_dir']);

		if (file_exists($context['themes'][$i]['theme_dir'] . '/index.template.php'))
		{
			// Fetch the header... a good 256 bytes should be more than enough.
			$fp = fopen($context['themes'][$i]['theme_dir'] . '/index.template.php', 'rb');
			$header = fread($fp, 256);
			fclose($fp);

			// Can we find a version comment, at all?
			if (preg_match('~(?://|/\*)\s*Version:\s+(.+?);\s*index(?:[\s]{2}|\*/)~i', $header, $match) == 1)
				$context['themes'][$i]['version'] = $match[1];
		}

		$context['themes'][$i]['valid_path'] = file_exists($context['themes'][$i]['theme_dir']) && is_dir($context['themes'][$i]['theme_dir']);
	}

	$context['reset_dir'] = realpath($boarddir . '/Themes');
	$context['reset_url'] = $boardurl . '/Themes';

	$context['sub_template'] = 'list_themes';
}

// Administrative global settings.
function SetThemeOptions()
{
	global $txt, $sc, $context, $settings, $db_prefix, $modSettings, $smfFunc;

	$_GET['th'] = isset($_GET['th']) ? (int) $_GET['th'] : (isset($_GET['id']) ? (int) $_GET['id'] : 0);

	isAllowedTo('admin_forum');

	if (empty($_GET['th']) && empty($_GET['id']))
	{
		checkSession('get');

		$request = $smfFunc['db_query']('', "
			SELECT id_theme, variable, value
			FROM {$db_prefix}themes
			WHERE variable IN ('name', 'theme_dir')
				AND id_member = 0", __FILE__, __LINE__);
		$context['themes'] = array();
		while ($row = $smfFunc['db_fetch_assoc']($request))
		{
			if (!isset($context['themes'][$row['id_theme']]))
				$context['themes'][$row['id_theme']] = array(
					'id' => $row['id_theme'],
					'num_default_options' => 0,
					'num_members' => 0,
				);
			$context['themes'][$row['id_theme']][$row['variable']] = $row['value'];
		}
		$smfFunc['db_free_result']($request);

		$request = $smfFunc['db_query']('', "
			SELECT id_theme, COUNT(*) AS value
			FROM {$db_prefix}themes
			WHERE id_member = -1
			GROUP BY id_theme", __FILE__, __LINE__);
		while ($row = $smfFunc['db_fetch_assoc']($request))
			$context['themes'][$row['id_theme']]['num_default_options'] = $row['value'];
		$smfFunc['db_free_result']($request);

		// Need to make sure we don't do custom fields.
		$request = $smfFunc['db_query']('', "
			SELECT col_name
			FROM {$db_prefix}custom_fields", __FILE__, __LINE__);
		$customFields = array();
		while ($row = $smfFunc['db_fetch_assoc']($request))
			$customFields[] = $row['col_name'];
		$smfFunc['db_free_result']($request);
		$customFieldsQuery = empty($customFields) ? '' : ('AND variable NOT IN (\'' . implode("', '", $customFields) . '\')');

		$request = $smfFunc['db_query']('', "
			SELECT id_theme, COUNT(DISTINCT id_member) AS value
			FROM {$db_prefix}themes
			WHERE id_member > 0
				$customFieldsQuery
			GROUP BY id_theme", __FILE__, __LINE__);
		while ($row = $smfFunc['db_fetch_assoc']($request))
			$context['themes'][$row['id_theme']]['num_members'] = $row['value'];
		$smfFunc['db_free_result']($request);

		foreach ($context['themes'] as $k => $v)
		{
			// There has to be a Settings template!
			if (!file_exists($v['theme_dir'] . '/Settings.template.php') && empty($v['num_members']))
				unset($context['themes'][$k]);
		}

		loadTemplate('Themes');
		$context['sub_template'] = 'reset_list';

		return;
	}

	// Submit?
	if (isset($_POST['submit']) && empty($_POST['who']))
	{
		checkSession();

		if (empty($_POST['options']))
			$_POST['options'] = array();
		if (empty($_POST['default_options']))
			$_POST['default_options'] = array();

		// Set up the sql query.
		$setValues = array();

		foreach ($_POST['options'] as $opt => $val)
			$setValues[] = array(-1, $_GET[th], "SUBSTRING('$opt', 1, 255)", "SUBSTRING('" . (is_array($val) ? implode(',', $val) : $val) . "', 1, 65534)");

		$old_settings = array();
		foreach ($_POST['default_options'] as $opt => $val)
		{
			$old_settings[] = $opt;

			$setValues[] = array(-1, 1, "SUBSTRING('$opt', 1, 255)", "SUBSTRING('" . (is_array($val) ? implode(',', $val) : $val) . "', 1, 65534)");
		}

		// If we're actually inserting something..
		if (!empty($setValues))
		{
			// Are there options in non-default themes set that should be cleared?
			if (!empty($old_settings))
				$smfFunc['db_query']('', "
					DELETE FROM {$db_prefix}themes
					WHERE id_theme != 1
						AND id_member = -1
						AND variable IN ('" . implode("', '", $old_settings) . "')", __FILE__, __LINE__);

			$smfFunc['db_insert']('replace',
				"{$db_prefix}themes",
				array('id_member', 'id_theme', 'variable', 'value'),
				$setValues,
				array('id_theme', 'variable', 'id_member'), __FILE__, __LINE__
			);
		}

		cache_put_data('theme_settings-' . $_GET['th'], null, 90);
		cache_put_data('theme_settings-1', null, 90);

		redirectexit('action=admin;area=theme;sesc=' . $sc . ';sa=reset');
	}
	elseif (isset($_POST['submit']) && $_POST['who'] == 1)
	{
		checkSession();

		$_POST['options'] = empty($_POST['options']) ? array() : $_POST['options'];
		$_POST['options_master'] = empty($_POST['options_master']) ? array() : $_POST['options_master'];
		$_POST['default_options'] = empty($_POST['default_options']) ? array() : $_POST['default_options'];
		$_POST['default_options_master'] = empty($_POST['default_options_master']) ? array() : $_POST['default_options_master'];

		$old_settings = array();
		foreach ($_POST['default_options'] as $opt => $val)
		{
			if ($_POST['default_options_master'][$opt] == 0)
				continue;
			elseif ($_POST['default_options_master'][$opt] == 1)
			{
				// Delete then insert for ease of database compatibility!
				$smfFunc['db_query']('', "
					DELETE FROM {$db_prefix}themes
					WHERE id_theme = 1
						AND id_member != 0
						AND variable = SUBSTRING('$opt', 1, 255)", __FILE__, __LINE__);
				$smfFunc['db_query']('', "
					INSERT INTO {$db_prefix}themes
						(id_member, id_theme, variable, value)
					SELECT id_member, 1, SUBSTRING('$opt', 1, 255), SUBSTRING('" . (is_array($val) ? implode(',', $val) : $val) . "', 1, 65534)
					FROM {$db_prefix}members", __FILE__, __LINE__);

				$old_settings[] = $opt;
			}
			elseif ($_POST['default_options_master'][$opt] == 2)
			{
				$smfFunc['db_query']('', "
					DELETE FROM {$db_prefix}themes
					WHERE variable = '$opt'
						AND id_member > 0", __FILE__, __LINE__);
			}
		}

		// Delete options from other themes.
		if (!empty($old_settings))
			$smfFunc['db_query']('', "
				DELETE FROM {$db_prefix}themes
				WHERE id_theme != 1
					AND id_member > 0
					AND variable IN ('" . implode("', '", $old_settings) . "')", __FILE__, __LINE__);

		foreach ($_POST['options'] as $opt => $val)
		{
			if ($_POST['options_master'][$opt] == 0)
				continue;
			elseif ($_POST['options_master'][$opt] == 1)
			{
				// Delete then insert for ease of database compatibility - again!
				$smfFunc['db_query']('', "
					DELETE FROM {$db_prefix}themes
					WHERE id_theme = $_GET[th]
						AND id_member != 0
						AND variable = SUBSTRING('$opt', 1, 255)", __FILE__, __LINE__);
				$smfFunc['db_query']('', "
					INSERT INTO {$db_prefix}themes
						(id_member, id_theme, variable, value)
					SELECT id_member, $_GET[th], SUBSTRING('$opt', 1, 255), SUBSTRING('" . (is_array($val) ? implode(',', $val) : $val) . "', 1, 65534)
					FROM {$db_prefix}members", __FILE__, __LINE__);
			}
			elseif ($_POST['options_master'][$opt] == 2)
			{
				$smfFunc['db_query']('', "
					DELETE FROM {$db_prefix}themes
					WHERE variable = '$opt'
						AND id_member > 0
						AND id_theme = $_GET[th]", __FILE__, __LINE__);
			}
		}

		redirectexit('action=admin;area=theme;sesc=' . $sc . ';sa=reset');
	}
	elseif (!empty($_GET['who']) && $_GET['who'] == 2)
	{
		checkSession('get');

		// Don't delete custom fields!!
		if ($_GET['th'] == 1)
		{
			$request = $smfFunc['db_query']('', "
				SELECT col_name
				FROM {$db_prefix}custom_fields", __FILE__, __LINE__);
			$customFields = array();
			while ($row = $smfFunc['db_fetch_assoc']($request))
				$customFields[] = $row['col_name'];
			$smfFunc['db_free_result']($request);
		}
		$customFieldsQuery = empty($customFields) ? '' : ('AND variable NOT IN (\'' . implode("', '", $customFields) . '\')');

		$smfFunc['db_query']('', "
			DELETE FROM {$db_prefix}themes
			WHERE id_member > 0
				AND id_theme = $_GET[th]
				$customFieldsQuery", __FILE__, __LINE__);

		redirectexit('action=admin;area=theme;sesc=' . $sc . ';sa=reset');
	}

	checkSession('get');

	$old_id = $settings['theme_id'];
	$old_settings = $settings;

	loadTheme($_GET['th'], false);

	loadLanguage('Profile');
	//!!! Should we just move these options so they are no longer theme dependant?
	loadLanguage('PersonalMessage');

	// Let the theme take care of the settings.
	loadTemplate('Settings');
	loadSubTemplate('options');

	$context['sub_template'] = 'set_options';
	$context['page_title'] = $txt['theme_settings'];

	$context['options'] = $context['theme_options'];
	$context['theme_settings'] = $settings;

	if (empty($_REQUEST['who']))
	{
		$request = $smfFunc['db_query']('', "
			SELECT variable, value
			FROM {$db_prefix}themes
			WHERE id_theme IN (1, " . $_GET['th'] . ")
				AND id_member = -1", __FILE__, __LINE__);
		$context['theme_options'] = array();
		while ($row = $smfFunc['db_fetch_assoc']($request))
			$context['theme_options'][$row['variable']] = $row['value'];
		$smfFunc['db_free_result']($request);

		$context['theme_options_reset'] = false;
	}
	else
	{
		$context['theme_options'] = array();
		$context['theme_options_reset'] = true;
	}

	foreach ($context['options'] as $i => $setting)
	{
		if (!isset($setting['type']) || $setting['type'] == 'bool')
			$context['options'][$i]['type'] = 'checkbox';
		elseif ($setting['type'] == 'int' || $setting['type'] == 'integer')
			$context['options'][$i]['type'] = 'number';
		elseif ($setting['type'] == 'string')
			$context['options'][$i]['type'] = 'text';

		if (isset($setting['options']))
			$context['options'][$i]['type'] = 'list';

		$context['options'][$i]['value'] = !isset($context['theme_options'][$setting['id']]) ? '' : $context['theme_options'][$setting['id']];
	}

	// Restore the existing theme.
	loadTheme($old_id, false);
	$settings = $old_settings;

	loadTemplate('Themes');
}

// Administrative global settings.
function SetThemeSettings()
{
	global $txt, $sc, $context, $settings, $db_prefix, $modSettings, $sourcedir, $smfFunc;

	if (empty($_GET['th']) && empty($_GET['id']))
		return ThemeAdmin();
	$_GET['th'] = isset($_GET['th']) ? (int) $_GET['th'] : (int) $_GET['id'];

	// Select the best fitting tab.
	$context['admin_tabs']['tabs']['list']['is_selected'] = true;

	loadLanguage('Admin');
	isAllowedTo('admin_forum');

	// If editing the current theme highlight the right bit on the admin menu.
	if ($settings['theme_id'] == $_GET['th'])
		$context['admin_area'] = 'current_theme';

	// Validate inputs/user.
	if (empty($_GET['th']))
		fatal_lang_error('no_theme', false);

	// Submitting!
	if (isset($_POST['submit']))
	{
		checkSession();

		if (empty($_POST['options']))
			$_POST['options'] = array();
		if (empty($_POST['default_options']))
			$_POST['default_options'] = array();

		// Set up the sql query.
		$inserts = array();
		foreach ($_POST['options'] as $opt => $val)
			$inserts[] = array(0, $_GET['th'], "SUBSTRING('$opt', 1, 255)", "SUBSTRING('" . (is_array($val) ? implode(',', $val) : $val) . "', 1, 65534)");
		foreach ($_POST['default_options'] as $opt => $val)
			$inserts[] = array(0, 1, "SUBSTRING('$opt', 1, 255)", "SUBSTRING('" . (is_array($val) ? implode(',', $val) : $val) . "', 1, 65534)");
		// If we're actually inserting something..
		if (!empty($inserts))
		{
			$smfFunc['db_insert']('replace',
				"{$db_prefix}themes",
				array('id_member', 'id_theme', 'variable', 'value'),
				$inserts,
				array('id_member', 'id_theme', 'variable'), __FILE__, __LINE__
			);
		}

		cache_put_data('theme_settings-' . $_GET['th'], null, 90);
		cache_put_data('theme_settings-1', null, 90);

		redirectexit('action=admin;area=theme;sa=settings;th=' . $_GET['th'] . ';sesc=' . $sc);
	}

	checkSession('get');

	// Fetch the smiley sets...
	$sets = explode(',', 'none,' . $modSettings['smiley_sets_known']);
	$set_names = explode("\n", $txt['smileys_none'] . "\n" . $modSettings['smiley_sets_names']);
	$context['smiley_sets'] = array(
		'' => $txt['smileys_no_default']
	);
	foreach ($sets as $i => $set)
		$context['smiley_sets'][$set] = $set_names[$i];

	$old_id = $settings['theme_id'];
	$old_settings = $settings;

	loadTheme($_GET['th'], false);

	// Let the theme take care of the settings.
	loadTemplate('Settings');
	loadSubTemplate('settings');

	$context['sub_template'] = 'set_settings';
	$context['page_title'] = $txt['theme_settings'];

	foreach ($settings as $setting => $dummy)
	{
		if (!in_array($setting, array('theme_url', 'theme_dir', 'images_url')))
			$settings[$setting] = htmlspecialchars($settings[$setting]);
	}

	$context['settings'] = $context['theme_settings'];
	$context['theme_settings'] = $settings;

	foreach ($context['settings'] as $i => $setting)
	{
		if (!isset($setting['type']) || $setting['type'] == 'bool')
			$context['settings'][$i]['type'] = 'checkbox';
		elseif ($setting['type'] == 'int' || $setting['type'] == 'integer')
			$context['settings'][$i]['type'] = 'number';
		elseif ($setting['type'] == 'string')
			$context['settings'][$i]['type'] = 'text';

		if (isset($setting['options']))
			$context['settings'][$i]['type'] = 'list';

		$context['settings'][$i]['value'] = !isset($settings[$setting['id']]) ? '' : $settings[$setting['id']];
	}

	// Restore the current theme.
	loadTheme($old_id, false);
	$settings = $old_settings;

	loadTemplate('Themes');
}

// Remove a theme from the database.
function RemoveTheme()
{
	global $db_prefix, $modSettings, $sc, $smfFunc;

	checkSession('get');

	isAllowedTo('admin_forum');

	// The theme's ID must be an integer.
	$_GET['th'] = isset($_GET['th']) ? (int) $_GET['th'] : (int) $_GET['id'];

	// You can't delete the default theme!
	if ($_GET['th'] == 1)
		fatal_lang_error('no_access', false);

	$known = explode(',', $modSettings['knownThemes']);
	for ($i = 0, $n = count($known); $i < $n; $i++)
	{
		if ($known[$i] == $_GET['th'])
			unset($known[$i]);
	}

	$smfFunc['db_query']('', "
		DELETE FROM {$db_prefix}themes
		WHERE id_theme = $_GET[th]", __FILE__, __LINE__);

	$smfFunc['db_query']('', "
		UPDATE {$db_prefix}members
		SET id_theme = 0
		WHERE id_theme = $_GET[th]", __FILE__, __LINE__);

	$known = strtr(implode(',', $known), array(',,' => ','));

	// Fix it if the theme was the overall default theme.
	if ($modSettings['theme_guests'] == $_GET['th'])
		updateSettings(array('theme_guests' => '1', 'knownThemes' => $known));
	else
		updateSettings(array('knownThemes' => $known));

	// Remove any cached language files to keep space minimum!
	clean_cache('lang');

	redirectexit('action=admin;area=theme;sa=list;sesc=' . $sc);
}

// Choose a theme from a list.
function PickTheme()
{
	global $txt, $db_prefix, $sc, $context, $modSettings, $user_info, $language, $smfFunc, $settings;

	checkSession('get');

	loadLanguage('Profile');
	loadTemplate('Themes');

	$_SESSION['id_theme'] = 0;

	if (isset($_GET['id']))
		$_GET['th'] = $_GET['id'];

	// Have we made a desicion, or are we just browsing?
	if (isset($_GET['th']))
	{
		// Save for this user.
		if (!isset($_REQUEST['u']) || !allowedTo('admin_forum'))
		{
			updateMemberData($user_info['id'], array('id_theme' => (int) $_GET['th']));

			redirectexit('action=profile;sa=theme');
		}
		// For everyone.
		elseif ($_REQUEST['u'] == '0')
		{
			updateMemberData(null, array('id_theme' => (int) $_GET['th']));

			redirectexit('action=admin;area=theme;sa=admin;sesc=' . $sc);
		}
		// Change the default/guest theme.
		elseif ($_REQUEST['u'] == '-1')
		{
			updateSettings(array('theme_guests' => (int) $_GET['th']));

			redirectexit('action=admin;area=theme;sa=admin;sesc=' . $sc);
		}
		// Change a specific member's theme.
		else
		{
			updateMemberData((int) $_REQUEST['u'], array('id_theme' => (int) $_GET['th']));

			redirectexit('action=profile;u=' . (int) $_REQUEST['u'] . ';sa=theme');
		}
	}

	// Figure out who the member of the minute is, and what theme they've chosen.
	if (!isset($_REQUEST['u']) || !allowedTo('admin_forum'))
	{
		$context['current_member'] = $user_info['id'];
		$context['current_theme'] = $user_info['theme'];
	}
	// Everyone can't chose just one.
	elseif ($_REQUEST['u'] == '0')
	{
		$context['current_member'] = 0;
		$context['current_theme'] = 0;
	}
	// Guests and such...
	elseif ($_REQUEST['u'] == '-1')
	{
		$context['current_member'] = -1;
		$context['current_theme'] = $modSettings['theme_guests'];
	}
	// Someones else :P.
	else
	{
		$context['current_member'] = (int) $_REQUEST['u'];

		$request = $smfFunc['db_query']('', "
			SELECT id_theme
			FROM {$db_prefix}members
			WHERE id_member = $context[current_member]
			LIMIT 1", __FILE__, __LINE__);
		list ($context['current_theme']) = $smfFunc['db_fetch_row']($request);
		$smfFunc['db_free_result']($request);
	}
	// Get the theme name and descriptions.
	$context['available_themes'] = array();
	if (!empty($modSettings['knownThemes']))
	{
		$knownThemes = implode("', '", explode(',', $modSettings['knownThemes']));

		$request = $smfFunc['db_query']('', "
			SELECT id_theme, variable, value
			FROM {$db_prefix}themes
			WHERE variable IN ('name', 'theme_url', 'theme_dir', 'images_url')" . (!allowedTo('admin_forum') ? "
				AND id_theme IN ('$knownThemes')" : '') . "
				AND id_theme != 0
			LIMIT " . count(explode(',', $modSettings['knownThemes'])) * 8, __FILE__, __LINE__);
		while ($row = $smfFunc['db_fetch_assoc']($request))
		{
			if (!isset($context['available_themes'][$row['id_theme']]))
				$context['available_themes'][$row['id_theme']] = array(
					'id' => $row['id_theme'],
					'selected' => $context['current_theme'] == $row['id_theme'],
					'num_users' => 0
				);
			$context['available_themes'][$row['id_theme']][$row['variable']] = $row['value'];
		}
		$smfFunc['db_free_result']($request);
	}

	// Okay, this is a complicated problem: the default theme is 1, but they aren't allowed to access 1!
	if (!isset($context['available_themes'][$modSettings['theme_guests']]))
	{
		$context['available_themes'][0] = array(
			'num_users' => 0
		);
		$guest_theme = 0;
	}
	else
		$guest_theme = $modSettings['theme_guests'];

	$request = $smfFunc['db_query']('', "
		SELECT id_theme, COUNT(*) AS the_count
		FROM {$db_prefix}members
		GROUP BY id_theme
		ORDER BY id_theme DESC", __FILE__, __LINE__);
	while ($row = $smfFunc['db_fetch_assoc']($request))
	{
		// Figure out which theme it is they are REALLY using.
		if (!empty($modSettings['knownThemes']) && !in_array($row['id_theme'], explode(',',$modSettings['knownThemes'])))
			$row['id_theme'] = $guest_theme;
		elseif (empty($modSettings['theme_allow']))
			$row['id_theme'] = $guest_theme;

		if (isset($context['available_themes'][$row['id_theme']]))
			$context['available_themes'][$row['id_theme']]['num_users'] += $row['the_count'];
		else
			$context['available_themes'][$guest_theme]['num_users'] += $row['the_count'];
	}
	$smfFunc['db_free_result']($request);

	foreach ($context['available_themes'] as $id_theme => $theme_data)
	{
		// Don't try to load the forum or board default theme's data... it doesn't have any!
		if ($id_theme == 0)
			continue;

		$settings = $theme_data;
		$settings['theme_id'] = $id_theme;

		if (file_exists($settings['theme_dir'] . '/languages/Settings.' . $user_info['language'] . '.php'))
			include($settings['theme_dir'] . '/languages/Settings.' . $user_info['language'] . '.php');
		elseif (file_exists($settings['theme_dir'] . '/languages/Settings.' . $language . '.php'))
			include($settings['theme_dir'] . '/languages/Settings.' . $language . '.php');
		else
		{
			$txt['theme_thumbnail_href'] = $settings['images_url'] . '/thumbnail.gif';
			$txt['theme_description'] = '';
		}

		$context['available_themes'][$id_theme]['thumbnail_href'] = $txt['theme_thumbnail_href'];
		$context['available_themes'][$id_theme]['description'] = $txt['theme_description'];
	}

	// As long as we're not doing the default theme...
	if (!isset($_REQUEST['u']) || $_REQUEST['u'] >= 0)
	{
		if ($guest_theme != 0)
			$context['available_themes'][0] = $context['available_themes'][$guest_theme];

		$context['available_themes'][0]['id'] = 0;
		$context['available_themes'][0]['name'] = $txt['theme_forum_default'];
		$context['available_themes'][0]['selected'] = $context['current_theme'] == 0;
		$context['available_themes'][0]['description'] = $txt['theme_global_description'];
	}

	ksort($context['available_themes']);

	$context['page_title'] = &$txt['theme_pick'];
	$context['sub_template'] = 'pick';
}

function ThemeInstall()
{
	global $sourcedir, $boarddir, $boardurl, $db_prefix, $txt, $context, $settings, $modSettings, $smfFunc;

	checkSession('request');

	isAllowedTo('admin_forum');
	checkSession('request');

	require_once($sourcedir . '/Subs-Package.php');

	loadTemplate('Themes');

	if (isset($_GET['theme_id']))
	{
		$result = $smfFunc['db_query']('', "
			SELECT value
			FROM {$db_prefix}themes
			WHERE id_theme = " . (int) $_GET['theme_id'] . "
				AND id_member = 0
				AND variable = 'name'
			LIMIT 1", __FILE__, __LINE__);
		list ($theme_name) = $smfFunc['db_fetch_row']($result);
		$smfFunc['db_free_result']($result);

		$context['sub_template'] = 'installed';
		$context['page_title'] = $txt['theme_installed'];
		$context['installed_theme'] = array(
			'id' => (int) $_GET['theme_id'],
			'name' => $theme_name,
		);

		return;
	}

	if ((!empty($_FILES['theme_gz']) && (!isset($_FILES['theme_gz']['error']) || $_FILES['theme_gz']['error'] != 4)) || !empty($_REQUEST['theme_gz']))
		$method = 'upload';
	elseif (isset($_REQUEST['theme_dir']) && realpath($smfFunc['db_unescape_string']($_REQUEST['theme_dir'])) != realpath($boarddir . '/Themes') && file_exists($smfFunc['db_unescape_string']($_REQUEST['theme_dir'])))
		$method = 'path';
	else
		$method = 'copy';

	if (!empty($_REQUEST['copy']) && $method == 'copy')
	{
		// Hopefully the themes directory is writable, or we might have a problem.
		if (!is_writable($boarddir . '/Themes'))
			fatal_lang_error('theme_install_write_error', 'critical');

		$theme_dir = $boarddir . '/Themes/' . preg_replace('~[^A-Za-z0-9_\- ]~', '', $_REQUEST['copy']);

		umask(0);
		mkdir($theme_dir, 0777);

		// Copy over the default non-theme files.
		$to_copy = array('/style.css', '/index.php', '/index.template.php');
		foreach ($to_copy as $file)
		{
			copy($settings['default_theme_dir'] . $file, $theme_dir . $file);
			@chmod($theme_dir . $file, 0777);
		}

		// And now the entire images directory!
		copytree($settings['default_theme_dir'] . '/images', $theme_dir . '/images');
		package_flush_cache();

		$theme_name = $_REQUEST['copy'];
		$images_url = $boardurl . '/Themes/' . basename($theme_dir) . '/images';
		$theme_dir = realpath($theme_dir);
	}
	elseif (isset($_REQUEST['theme_dir']) && $method == 'path')
	{
		if (!is_dir($smfFunc['db_unescape_string']($_REQUEST['theme_dir'])) || !file_exists($smfFunc['db_unescape_string']($_REQUEST['theme_dir']) . '/theme_info.xml'))
			fatal_lang_error('theme_install_error', false);

		$theme_name = basename($_REQUEST['theme_dir']);
		$theme_dir = $smfFunc['db_unescape_string']($_REQUEST['theme_dir']);
	}
	elseif ($method = 'upload')
	{
		// Hopefully the themes directory is writable, or we might have a problem.
		if (!is_writable($boarddir . '/Themes'))
			fatal_lang_error('theme_install_write_error', 'critical');

		require_once($sourcedir . '/Subs-Package.php');

		// Set the default settings...
		$theme_name = strtok(basename(isset($_FILES['theme_gz']) ? $_FILES['theme_gz']['name'] : $_REQUEST['theme_gz']), '.');
		$theme_name = preg_replace(array('/\s/', '/\.[\.]+/', '/[^\w_\.\-]/'), array('_', '.', ''), $theme_name);
		$theme_dir = $boarddir . '/Themes/' . $theme_name;

		if (isset($_FILES['theme_gz']) && is_uploaded_file($_FILES['theme_gz']['tmp_name']) && (@ini_get('open_basedir') != '' || file_exists($_FILES['theme_gz']['tmp_name'])))
			$extracted = read_tgz_file($_FILES['theme_gz']['tmp_name'], $boarddir . '/Themes/' . $theme_name, false, true);
		elseif (isset($_REQUEST['theme_gz']))
		{
			// Check that the theme is from simplemachines.org, for now... maybe add mirroring later.
			if (preg_match('~^http://[\w_\-]+\.simplemachines\.org/~', $_REQUEST['theme_gz']) == 0 || strpos($_REQUEST['theme_gz'], 'dlattach') !== false)
				fatal_lang_error('not_on_simplemachines');

			$extracted = read_tgz_file($_REQUEST['theme_gz'], $boarddir . '/Themes/' . $theme_name, false, true);
		}
		else
			redirectexit('action=admin;area=theme;sa=admin;sesc=' . $context['session_id']);
	}

	// Something go wrong?
	if ($theme_dir != '' && basename($theme_dir) != 'Themes')
	{
		// Defaults.
		$install_info = array(
			'theme_url' => $boardurl . '/Themes/' . basename($theme_dir),
			'images_url' => isset($images_url) ? $images_url : $boardurl . '/Themes/' . basename($theme_dir) . '/images',
			'theme_dir' => $theme_dir,
			'name' => $theme_name
		);

		if (file_exists($theme_dir . '/theme_info.xml'))
		{
			$theme_info = file_get_contents($theme_dir . '/theme_info.xml');

			$xml_elements = array(
				'name' => 'name',
				'theme_layers' => 'layers',
				'theme_templates' => 'templates',
				'based_on' => 'based-on',
			);
			foreach ($xml_elements as $var => $name)
			{
				if (preg_match('~<' . $name . '>(?:<!\[CDATA\[)?(.+?)(?:\]\]>)?</' . $name . '>~', $theme_info, $match) == 1)
					$install_info[$var] = $match[1];
			}

			if (preg_match('~<images>(?:<!\[CDATA\[)?(.+?)(?:\]\]>)?</images>~', $theme_info, $match) == 1)
			{
				$install_info['images_url'] = $install_info['theme_url'] . '/' . $match[1];
				$explicit_images = true;
			}
			if (preg_match('~<extra>(?:<!\[CDATA\[)?(.+?)(?:\]\]>)?</extra>~', $theme_info, $match) == 1)
				$install_info += unserialize($match[1]);
		}

		if (isset($install_info['based_on']))
		{
			if ($install_info['based_on'] == 'default')
			{
				$install_info['theme_url'] = $settings['default_theme_url'];
				$install_info['images_url'] = $settings['default_images_url'];
			}
			elseif ($install_info['based_on'] != '')
			{
				$install_info['based_on'] = preg_replace('~[^A-Za-z0-9\-_ ]~', '', $install_info['based_on']);

				$request = $smfFunc['db_query']('', "
					SELECT th.value AS base_theme_dir, th2.value AS base_theme_url" . (!empty($explicit_images) ? '' : ", th3.value AS images_url") . "
					FROM {$db_prefix}themes AS th
						INNER JOIN {$db_prefix}themes AS th2 ON (th2.id_theme = th.id_theme
							AND th2.id_member = 0
							AND th2.variable = 'theme_url')" . (!empty($explicit_images) ? '' : "
						INNER JOIN {$db_prefix}themes AS th3 ON (th3.id_theme = th.id_theme
							AND th3.id_member = 0
							AND th3.variable = 'images_url')") . "
					WHERE th.id_member = 0
						AND (th.value LIKE '%/$install_info[based_on]' OR th.value LIKE '%\\$install_info[based_on]')
						AND th.variable = 'theme_dir'
					LIMIT 1", __FILE__, __LINE__);
				$temp = $smfFunc['db_fetch_assoc']($request);
				$smfFunc['db_free_result']($request);

				// !!! An error otherwise?
				if (is_array($temp))
				{
					$install_info = $temp + $install_info;

					if (empty($explicit_images) && !empty($install_info['base_theme_url']))
						$install_info['theme_url'] = $install_info['base_theme_url'];
				}
			}

			unset($install_info['based_on']);
		}

		// Find the newest id_theme.
		$result = $smfFunc['db_query']('', "
			SELECT MAX(id_theme)
			FROM {$db_prefix}themes", __FILE__, __LINE__);
		list ($id_theme) = $smfFunc['db_fetch_row']($result);
		$smfFunc['db_free_result']($result);

		// This will be theme number...
		$id_theme++;

		$inserts = array();
		foreach ($install_info as $var => $val)
			$inserts[] = array($id_theme, "SUBSTRING('" . $smfFunc['db_escape_string']($var) . "', 1, 255)", "SUBSTRING('" . $smfFunc['db_escape_string']($val) . "', 1, 65534)");

		if (!empty($inserts))
			$smfFunc['db_insert']('insert',
				"{$db_prefix}themes",
				array('id_theme', 'variable', 'value'),
				$inserts,
				array('id_theme', 'variable'), __FILE__, __LINE__
			);

		updateSettings(array('knownThemes' => strtr($modSettings['knownThemes'] . ',' . $id_theme, array(',,' => ','))));
	}

	redirectexit('action=admin;area=theme;sa=install;theme_id=' . $id_theme . ';sesc=' . $context['session_id']);
}

// Possibly the simplest and best example of how to ues the template system.
function WrapAction()
{
	global $context, $settings, $sourcedir;

	// Load any necessary template(s)?
	if (isset($settings['catch_action']['template']))
	{
		// Load both the template and language file. (but don't fret if the language file isn't there...)
		loadTemplate($settings['catch_action']['template']);
		loadLanguage($settings['catch_action']['template'], '', false);
	}

	// Any special layers?
	if (isset($settings['catch_action']['layers']))
		$context['template_layers'] = $settings['catch_action']['layers'];

	// Just call a function?
	if (isset($settings['catch_action']['function']))
	{
		if (isset($settings['catch_action']['filename']))
			template_include($sourcedir . '/' . $settings['catch_action']['filename'], true);

		$settings['catch_action']['function']();
	}
	// And finally, the main sub template ;).
	elseif (isset($settings['catch_action']['sub_template']))
		$context['sub_template'] = $settings['catch_action']['sub_template'];
}

// Set an option via javascript.
function SetJavaScript()
{
	global $db_prefix, $settings, $user_info, $smfFunc;

	// Sorry, guests can't do this.
	if ($user_info['is_guest'])
		obExit(false);

	// Check the session id.
	checkSession('get');

	// This good-for-nothing pixel is being used to keep the session alive.
	if (empty($_GET['var']) || !isset($_GET['val']))
		redirectexit($settings['images_url'] . '/blank.gif');

	// Use a specific theme?
	if (isset($_GET['th']) || isset($_GET['id']))
		$settings['theme_id'] = isset($_GET['th']) ? (int) $_GET['th'] : (int) $_GET['id'];

	// Update the option.
	$smfFunc['db_insert']('replace',
		"{$db_prefix}themes",
		array('id_theme', 'id_member', 'variable', 'value'),
		array($settings['theme_id'], $user_info['id'], "SUBSTRING('$_GET[var]', 1, 255)", "SUBSTRING('" . (is_array($_GET['val']) ? implode(',', $_GET['val']) : $_GET['val']) . "', 1, 65534)"),
		array('id_theme', 'id_member', 'variable'), __FILE__, __LINE__
	);

	cache_put_data('theme_settings-' . $settings['theme_id'] . ':' . $user_info['id'], null, 60);

	// Don't output anything...
	redirectexit($settings['images_url'] . '/blank.gif');
}

function EditTheme()
{
	global $context, $settings, $db_prefix, $scripturl, $boarddir, $smfFunc;

	if (isset($_REQUEST['preview']))
	{
		// !!! Should this be removed?
		die;
	}

	isAllowedTo('admin_forum');
	loadTemplate('Themes');

	$_GET['th'] = isset($_GET['th']) ? (int) $_GET['th'] : (int) @$_GET['id'];

	if (empty($_GET['th']))
	{
		checkSession('get');

		$request = $smfFunc['db_query']('', "
			SELECT id_theme, variable, value
			FROM {$db_prefix}themes
			WHERE variable IN ('name', 'theme_dir', 'theme_templates', 'theme_layers')
				AND id_member = 0
				AND id_theme != 1", __FILE__, __LINE__);
		$context['themes'] = array();
		while ($row = $smfFunc['db_fetch_assoc']($request))
		{
			if (!isset($context['themes'][$row['id_theme']]))
				$context['themes'][$row['id_theme']] = array(
					'id' => $row['id_theme'],
					'num_default_options' => 0,
					'num_members' => 0,
				);
			$context['themes'][$row['id_theme']][$row['variable']] = $row['value'];
		}
		$smfFunc['db_free_result']($request);

		foreach ($context['themes'] as $k => $v)
		{
			// There has to be a Settings template!
			if (!file_exists($v['theme_dir'] . '/index.template.php') && !file_exists($v['theme_dir'] . '/style.css'))
				unset($context['themes'][$k]);
			else
			{
				if (!isset($v['theme_templates']))
					$templates = array('index');
				else
					$templates = explode(',', $v['theme_templates']);

				foreach ($templates as $template)
					if (file_exists($v['theme_dir'] . '/' . $template . '.template.php'))
					{
						// Fetch the header... a good 256 bytes should be more than enough.
						$fp = fopen($v['theme_dir'] . '/' . $template . '.template.php', 'rb');
						$header = fread($fp, 256);
						fclose($fp);

						// Can we find a version comment, at all?
						if (preg_match('~(?://|/\*)\s*Version:\s+(.+?);\s*' . $template . '(?:[\s]{2}|\*/)~i', $header, $match) == 1)
						{
							$ver = $match[1];
							if (!isset($context['themes'][$k]['version']) || $context['themes'][$k]['version'] > $ver)
								$context['themes'][$k]['version'] = $ver;
						}
					}

				$context['themes'][$k]['can_edit_style'] = file_exists($v['theme_dir'] . '/style.css');
			}
		}

		loadTemplate('Themes');
		$context['sub_template'] = 'edit_list';

		return;
	}

	$context['session_error'] = false;

	// Get the directory of the theme we are editing.
	$request = $smfFunc['db_query']('', "
		SELECT value, id_theme
		FROM {$db_prefix}themes
		WHERE variable = 'theme_dir'
			AND id_theme = $_GET[th]
		LIMIT 1", __FILE__, __LINE__);
	list ($theme_dir, $context['theme_id']) = $smfFunc['db_fetch_row']($request);
	$smfFunc['db_free_result']($request);

	if (!isset($_REQUEST['filename']))
	{
		if (isset($_GET['directory']))
		{
			if (substr($_GET['directory'], 0, 1) == '.')
				$_GET['directory'] = '';
			else
			{
				$_GET['directory'] = preg_replace(array('~^[\./\\:\0\n\r]+~', '~[\\\\]~', '~/[\./]+~'), array('', '/', '/'), $_GET['directory']);

				$temp = realpath($theme_dir . '/' . $_GET['directory']);
				if (empty($temp) || substr($temp, 0, strlen(realpath($theme_dir))) != realpath($theme_dir))
					$_GET['directory'] = '';
			}
		}

		if (isset($_GET['directory']) && $_GET['directory'] != '')
		{
			$context['theme_files'] = get_file_listing($theme_dir . '/' . $_GET['directory'], $_GET['directory'] . '/');

			$temp = dirname($_GET['directory']);
			array_unshift($context['theme_files'], array(
				'filename' => $temp == '.' || $temp == '' ? '/ (..)' : $temp . ' (..)',
				'is_writable' => is_writable($theme_dir . '/' . $temp),
				'is_directory' => true,
				'is_template' => false,
				'is_image' => false,
				'is_editable' => false,
				'href' => $scripturl . '?action=admin;area=theme;th=' . $_GET['th'] . ';sesc=' . $context['session_id'] . ';sa=edit;directory=' . $temp,
				'size' => '',
			));
		}
		else
			$context['theme_files'] = get_file_listing($theme_dir, '');

		loadTemplate('Themes');
		$context['sub_template'] = 'edit_browse';

		return;
	}
	else
	{
		if (substr($_REQUEST['filename'], 0, 1) == '.')
			$_REQUEST['filename'] = '';
		else
		{
			$_REQUEST['filename'] = preg_replace(array('~^[\./\\:\0\n\r]+~', '~[\\\\]~', '~/[\./]+~'), array('', '/', '/'), $_REQUEST['filename']);

			$temp = realpath($theme_dir . '/' . $_REQUEST['filename']);
			if (empty($temp) || substr($temp, 0, strlen(realpath($theme_dir))) != realpath($theme_dir))
				$_REQUEST['filename'] = '';
		}

		if (empty($_REQUEST['filename']))
			fatal_lang_error('theme_edit_missing', false);
	}

	if (isset($_POST['submit']))
	{
		if (checkSession('post', '', false) == '')
		{
			if (is_array($_POST['entire_file']))
				$_POST['entire_file'] = implode("\n", $_POST['entire_file']);
			$_POST['entire_file'] = rtrim(strtr($smfFunc['db_unescape_string']($_POST['entire_file']), array("\r" => '', '   ' => "\t")));

			// Check for a parse error!
			if (substr($_REQUEST['filename'], -13) == '.template.php' && is_writable($theme_dir) && @ini_get('display_errors'))
			{
				$request = $smfFunc['db_query']('', "
					SELECT value
					FROM {$db_prefix}themes
					WHERE variable = 'theme_url'
						AND id_theme = $_GET[th]
					LIMIT 1", __FILE__, __LINE__);
				list ($theme_url) = $smfFunc['db_fetch_row']($request);
				$smfFunc['db_free_result']($request);

				$fp = fopen($theme_dir . '/tmp_' . session_id() . '.php', 'w');
				fwrite($fp, $_POST['entire_file']);
				fclose($fp);

				// !!! Use fetch_web_data()?
				$error = @file_get_contents($theme_url . '/tmp_' . session_id() . '.php');
				if (preg_match('~ <b>(\d+)</b><br( /)?' . '>$~i', $error) != 0)
					$error_file = $theme_dir . '/tmp_' . session_id() . '.php';
				else
					unlink($theme_dir . '/tmp_' . session_id() . '.php');
			}

			if (!isset($error_file))
			{
				$fp = fopen($theme_dir . '/' . $_REQUEST['filename'], 'w');
				fwrite($fp, $_POST['entire_file']);
				fclose($fp);

				redirectexit('action=admin;area=theme;th=' . $_GET['th'] . ';sesc=' . $context['session_id'] . ';sa=edit;directory=' . dirname($_REQUEST['filename']));
			}
		}
		// Session timed out.
		else
		{
			loadLanguage('Errors');

			$context['session_error'] = true;
			$context['sub_template'] = 'edit_file';

			// Recycle the submitted data.
			$context['entire_file'] = htmlspecialchars($smfFunc['db_unescape_string']($_POST['entire_file']));

			// You were able to submit it, so it's reasonable to assume you are allowed to save.
			$context['allow_save'] = true;

			return;
		}
	}
	else
		checkSession('get');

	$context['allow_save'] = is_writable($theme_dir . '/' . $_REQUEST['filename']);
	$context['allow_save_filename'] = strtr($theme_dir . '/' . $_REQUEST['filename'], array($boarddir => '...'));
	$context['edit_filename'] = htmlspecialchars($_REQUEST['filename']);

	if (substr($_REQUEST['filename'], -4) == '.css')
	{
		$context['sub_template'] = 'edit_style';

		$context['entire_file'] = htmlspecialchars(strtr(file_get_contents($theme_dir . '/' . $_REQUEST['filename']), array("\t" => '   ')));
	}
	elseif (substr($_REQUEST['filename'], -13) == '.template.php')
	{
		$context['sub_template'] = 'edit_template';

		if (!isset($error_file))
			$file_data = file($theme_dir . '/' . $_REQUEST['filename']);
		else
		{
			if (preg_match('~(<b>.+?</b>:.+?<b>).+?(</b>.+?<b>\d+</b>)<br( /)?' . '>$~i', $error, $match) != 0)
				$context['parse_error'] = $match[1] . $_REQUEST['filename'] . $match[2];
			$file_data = file($error_file);
			unlink($error_file);
		}

		$j = 0;
		$context['file_parts'] = array(array('lines' => 0, 'line' => 1, 'data' => ''));
		for ($i = 0, $n = count($file_data); $i < $n; $i++)
		{
			if (isset($file_data[$i + 1]) && substr($file_data[$i + 1], 0, 9) == 'function ')
			{
				// Try to format the functions a little nicer...
				$context['file_parts'][$j]['data'] = trim($context['file_parts'][$j]['data']) . "\n";

				if (empty($context['file_parts'][$j]['lines']))
					unset($context['file_parts'][$j]);
				$context['file_parts'][++$j] = array('lines' => 0, 'line' => $i + 1, 'data' => '');
			}

			$context['file_parts'][$j]['lines']++;
			$context['file_parts'][$j]['data'] .= htmlspecialchars(strtr($file_data[$i], array("\t" => '   ')));
		}

		$context['entire_file'] = htmlspecialchars(strtr(implode('', $file_data), array("\t" => '   ')));
	}
	else
	{
		$context['sub_template'] = 'edit_file';

		$context['entire_file'] = htmlspecialchars(strtr(file_get_contents($theme_dir . '/' . $_REQUEST['filename']), array("\t" => '   ')));
	}
}

function get_file_listing($path, $relative)
{
	global $scripturl, $txt, $context;

	$dir = dir($path);
	$entries = array();
	while ($entry = $dir->read())
		$entries[] = $entry;
	$dir->close();

	natcasesort($entries);

	$listing1 = array();
	$listing2 = array();

	foreach ($entries as $entry)
	{
		// Skip all dot files, including .htaccess.
		if (substr($entry, 0, 1) == '.' || $entry == 'CVS')
			continue;

		if (is_dir($path . '/' . $entry))
			$listing1[] = array(
				'filename' => $entry,
				'is_writable' => is_writable($path . '/' . $entry),
				'is_directory' => true,
				'is_template' => false,
				'is_image' => false,
				'is_editable' => false,
				'href' => $scripturl . '?action=admin;area=theme;th=' . $_GET['th'] . ';sesc=' . $context['session_id'] . ';sa=edit;directory=' . $relative . $entry,
				'size' => '',
			);
		else
		{
			$size = filesize($path . '/' . $entry);
			if ($size > 2048 || $size == 1024)
				$size = comma_format($size / 1024) . ' ' . $txt['themeadmin_edit_kilobytes'];
			else
				$size = comma_format($size) . ' ' . $txt['themeadmin_edit_bytes'];

			$listing2[] = array(
				'filename' => $entry,
				'is_writable' => is_writable($path . '/' . $entry),
				'is_directory' => false,
				'is_template' => preg_match('~\.template\.php$~', $entry) != 0,
				'is_image' => preg_match('~\.(jpg|jpeg|gif|bmp|png)$~', $entry) != 0,
				'is_editable' => is_writable($path . '/' . $entry) && preg_match('~\.(php|pl|css|js|vbs|xml|xslt|txt|xsl|html|htm|shtm|shtml|asp|aspx|cgi|py)$~', $entry) != 0,
				'href' => $scripturl . '?action=admin;area=theme;th=' . $_GET['th'] . ';sesc=' . $context['session_id'] . ';sa=edit;filename=' . $relative . $entry,
				'size' => $size,
				'last_modified' => timeformat(filemtime($path . '/' . $entry)),
			);
		}
	}

	return array_merge($listing1, $listing2);
}

function CopyTemplate()
{
	global $context, $settings, $db_prefix, $smfFunc;

	isAllowedTo('admin_forum');
	loadTemplate('Themes');

	$context['admin_tabs']['tabs']['edit']['is_selected'] = true;

	$_GET['th'] = isset($_GET['th']) ? (int) $_GET['th'] : (int) $_GET['id'];

	$request = $smfFunc['db_query']('', "
		SELECT th1.value, th1.id_theme, th2.value
		FROM {$db_prefix}themes AS th1
			LEFT JOIN {$db_prefix}themes AS th2 ON (th2.variable = 'base_theme_dir' AND th2.id_theme = $_GET[th])
		WHERE th1.variable = 'theme_dir'
			AND th1.id_theme = $_GET[th]
		LIMIT 1", __FILE__, __LINE__);
	list ($theme_dir, $context['theme_id'], $base_theme_dir) = $smfFunc['db_fetch_row']($request);
	$smfFunc['db_free_result']($request);

	if (isset($_REQUEST['template']) && preg_match('~[\./\\\\:\0]~', $_REQUEST['template']) == 0)
	{
		if (!empty($base_theme_dir) && file_exists($base_theme_dir . '/' . $_REQUEST['template'] . '.template.php'))
			$filename = $base_theme_dir . '/' . $_REQUEST['template'] . '.template.php';
		elseif (file_exists($settings['default_theme_dir'] . '/' . $_REQUEST['template'] . '.template.php'))
			$filename = $settings['default_theme_dir'] . '/' . $_REQUEST['template'] . '.template.php';
		else
			fatal_lang_error('no_access', false);

		$fp = fopen($theme_dir . '/' . $_REQUEST['template'] . '.template.php', 'w');
		fwrite($fp, file_get_contents($filename));
		fclose($fp);

		redirectexit('action=admin;area=theme;th=' . $context['theme_id'] . ';sesc=' . $context['session_id'] . ';sa=copy');
	}
	elseif (isset($_REQUEST['lang_file']) && preg_match('~^[^\./\\\\:\0]\.[^\./\\\\:\0]$~', $_REQUEST['lang_file']) != 0)
	{
		if (!empty($base_theme_dir) && file_exists($base_theme_dir . '/languages/' . $_REQUEST['lang_file'] . '.php'))
			$filename = $base_theme_dir . '/languages/' . $_REQUEST['template'] . '.php';
		elseif (file_exists($settings['default_theme_dir'] . '/languages/' . $_REQUEST['template'] . '.php'))
			$filename = $settings['default_theme_dir'] . '/languages/' . $_REQUEST['template'] . '.php';
		else
			fatal_lang_error('no_access', false);

		$fp = fopen($theme_dir . '/languages/' . $_REQUEST['lang_file'] . '.php', 'w');
		fwrite($fp, file_get_contents($filename));
		fclose($fp);

		redirectexit('action=admin;area=theme;th=' . $context['theme_id'] . ';sesc=' . $context['session_id'] . ';sa=copy');
	}

	$templates = array();
	$lang_files = array();

	$dir = dir($settings['default_theme_dir']);
	while ($entry = $dir->read())
	{
		if (substr($entry, -13) == '.template.php')
			$templates[] = substr($entry, 0, -13);
	}
	$dir->close();

	$dir = dir($settings['default_theme_dir'] . '/languages');
	while ($entry = $dir->read())
	{
		if (preg_match('~^([^\.]+\.[^\.]+)\.php$~', $entry, $matches))
			$lang_files[] = $matches[1];
	}
	$dir->close();

	if (!empty($base_theme_dir))
	{
		$dir = dir($base_theme_dir);
		while ($entry = $dir->read())
		{
			if (substr($entry, -13) == '.template.php' && !in_array(substr($entry, 0, -13), $templates))
				$templates[] = substr($entry, 0, -13);
		}
		$dir->close();

		if (file_exists($base_theme_dir . '/languages'))
		{
			$dir = dir($base_theme_dir . '/languages');
			while ($entry = $dir->read())
			{
				if (preg_match('~^([^\.]+\.[^\.]+)\.php$~', $entry, $matches) && !in_array($matches[1], $lang_files))
					$lang_files[] = $matches[1];
			}
			$dir->close();
		}
	}

	natcasesort($templates);
	natcasesort($lang_files);

	$context['available_templates'] = array();
	foreach ($templates as $template)
		$context['available_templates'][$template] = array(
			'filename' => $template . '.template.php',
			'value' => $template,
			'already_exists' => false,
			'can_copy' => is_writable($theme_dir),
		);
	$context['available_language_files'] = array();
	foreach ($lang_files as $file)
		$context['available_language_files'][$file] = array(
			'filename' => $file . '.php',
			'value' => $file,
			'already_exists' => false,
			'can_copy' => file_exists($theme_dir . '/languages') ? is_writable($theme_dir . '/languages') : is_writable($theme_dir),
		);

	$dir = dir($theme_dir);
	while ($entry = $dir->read())
	{
		if (substr($entry, -13) == '.template.php' && isset($context['available_templates'][substr($entry, 0, -13)]))
		{
			$context['available_templates'][substr($entry, 0, -13)]['already_exists'] = true;
			$context['available_templates'][substr($entry, 0, -13)]['can_copy'] = is_writable($theme_dir . '/' . $entry);
		}
	}
	$dir->close();

	if (file_exists($theme_dir . '/languages'))
	{
		$dir = dir($theme_dir . '/languages');
		while ($entry = $dir->read())
		{
			if (preg_match('~^([^\.]+\.[^\.]+)\.php$~', $entry, $matches) && isset($context['available_language_files'][$matches[1]]))
			{
				$context['available_language_files'][$matches[1]]['already_exists'] = true;
				$context['available_language_files'][$matches[1]]['can_copy'] = is_writable($theme_dir . '/languages/' . $entry);
			}
		}
		$dir->close();
	}

	$context['sub_template'] = 'copy_template';
}

function convert_template($output_dir, $old_template = '')
{
	global $boarddir;

	if ($old_template == '')
	{
		// Step 1: Get the template.php file.
		if (file_exists($boarddir . '/template.php'))
			$old_template = file_get_contents($boarddir . '/template.php');
		elseif (file_exists($boarddir . '/template.html'))
			$old_template = file_get_contents($boarddir . '/template.html');
		else
			fatal_lang_error('theme_convert_error');
	}

	// Step 2: Change any single quotes to \'.
	$old_template = strtr($old_template, array('\'' => '\\\''));

	// Step 3: Parse out any existing PHP code.
	$old_template = preg_replace('~\<\?php(.*)\?\>~es', "phpcodefix('\$1')", $old_template);

	// Step 4: Now we add the beginning and end...
	$old_template = '<?php
// Version: 2.0 Alpha; index

// Initialize the template... mainly little settings.
function template_init()
{
	global $context, $settings, $options, $txt;

	/* Use images from default theme when using templates from the default theme?
		if this is always, images from the default theme will be used.
		if this is defaults, images from the default theme will only be used with default templates.
		if this is never, images from the default theme will not be used. */
	$settings[\'use_default_images\'] = \'never\';
}

// The main sub template above the content.
function template_main_above()
{
	global $context, $settings, $options, $scripturl, $txt;

	// Show right to left and the character set for ease of translating.
	echo ' . "'" . $old_template . "'" . ';
}

// Show a linktree.  This is that thing that shows "My Community | General Category | General Discussion"..
function theme_linktree()
{
	global $context, $settings, $options;

	// Folder style or inline?  Inline has a smaller font.
	echo \'<span class="nav"\', $settings[\'linktree_inline\'] ? \' style="font-size: smaller;"\' : \'\', \'>\';

	// Each tree item has a URL and name.  Some may have extra_before and extra_after.
	foreach ($context[\'linktree\'] as $k => $tree)
	{
		// Show the | | |-[] Folders.
		if (!$settings[\'linktree_inline\'])
		{
			if ($k > 0)
				echo str_repeat(\'<img src="\' . $settings[\'images_url\'] . \'/icons/linktree_main.gif" alt="| " border="0" />\', $k - 1), \'<img src="\' . $settings[\'images_url\'] . \'/icons/linktree_side.gif" alt="|-" border="0" />\';
			echo \'<img src="\' . $settings[\'images_url\'] . \'/icons/folder_open.gif" alt="+" border="0" />&nbsp; \';
		}

		if (isset($tree[\'extra_before\']))
			echo $tree[\'extra_before\'];
		echo \'<b>\', $settings[\'linktree_link\'] && isset($tree[\'url\']) ? \'<a href="\' . $tree[\'url\'] . \'" class="nav">\' . $tree[\'name\'] . \'</a>\' : $tree[\'name\'], \'</b>\';
		if (isset($tree[\'extra_after\']))
			echo $tree[\'extra_after\'];

		// Don\'t show a separator for the last one.
		if ($k != count($context[\'linktree\']) - 1)
			echo $settings[\'linktree_inline\'] ? \' &nbsp;|&nbsp; \' : \'<br />\';
	}

	echo \'</span>\';
}

// Show the menu up top.  Something like [home] [help] [profile] [logout]...
function template_menu()
{
	global $context, $settings, $options, $scripturl, $txt;

	// Show the [home] and [help] buttons.
	echo \'
				<a href="\', $scripturl, \'">\', ($settings[\'use_image_buttons\'] ? \'<img src="\' . $settings[\'images_url\'] . \'/\' . $context[\'user\'][\'language\'] . \'/home.gif" alt="\' . $txt[\'home\'] . \'" border="0" />\' : $txt[\'home\']), \'</a>\', $context[\'menu_separator\'], \'
				<a href="\', $scripturl, \'?action=help" target="_blank">\', ($settings[\'use_image_buttons\'] ? \'<img src="\' . $settings[\'images_url\'] . \'/\' . $context[\'user\'][\'language\'] . \'/help.gif" alt="\' . $txt[\'help\'] . \'" border="0" />\' : $txt[\'help\']), \'</a>\', $context[\'menu_separator\'];

	// How about the [search] button?
	if ($context[\'allow_search\'])
		echo \'
				<a href="\', $scripturl, \'?action=search">\', ($settings[\'use_image_buttons\'] ? \'<img src="\' . $settings[\'images_url\'] . \'/\' . $context[\'user\'][\'language\'] . \'/search.gif" alt="\' . $txt[\'search\'] . \'" border="0" />\' : $txt[\'search\']), \'</a>\', $context[\'menu_separator\'];

	// Is the user allowed to administrate at all? ([admin])
	if ($context[\'allow_admin\'])
		echo \'
				<a href="\', $scripturl, \'?action=admin">\', ($settings[\'use_image_buttons\'] ? \'<img src="\' . $settings[\'images_url\'] . \'/\' . $context[\'user\'][\'language\'] . \'/admin.gif" alt="\' . $txt[\'admin\'] . \'" border="0" />\' : $txt[\'admin\']), \'</a>\', $context[\'menu_separator\'];

	// Edit Profile... [profile]
	if ($context[\'allow_edit_profile\'])
		echo \'
				<a href="\', $scripturl, \'?action=profile">\', ($settings[\'use_image_buttons\'] ? \'<img src="\' . $settings[\'images_url\'] . \'/\' . $context[\'user\'][\'language\'] . \'/profile.gif" alt="\' . $txt[\'profile\'] . \'" border="0" />\' : $txt[\'profile\']), \'</a>\', $context[\'menu_separator\'];

	// The [calendar]!
	if ($context[\'allow_calendar\'])
		echo \'
				<a href="\', $scripturl, \'?action=calendar">\', ($settings[\'use_image_buttons\'] ? \'<img src="\' . $settings[\'images_url\'] . \'/\' . $context[\'user\'][\'language\'] . \'/calendar.gif" alt="\' . $txt[\'calendar\'] . \'" border="0" />\' : $txt[\'calendar\']), \'</a>\', $context[\'menu_separator\'];

	// If the user is a guest, show [login] and [register] buttons.
	if ($context[\'user\'][\'is_guest\'])
	{
		echo \'
				<a href="\', $scripturl, \'?action=login">\', ($settings[\'use_image_buttons\'] ? \'<img src="\' . $settings[\'images_url\'] . \'/\' . $context[\'user\'][\'language\'] . \'/login.gif" alt="\' . $txt[\'login\'] . \'" border="0" />\' : $txt[\'login\']), \'</a>\', $context[\'menu_separator\'], \'
				<a href="\', $scripturl, \'?action=register">\', ($settings[\'use_image_buttons\'] ? \'<img src="\' . $settings[\'images_url\'] . \'/\' . $context[\'user\'][\'language\'] . \'/register.gif" alt="\' . $txt[\'register\'] . \'" border="0" />\' : $txt[\'register\']), \'</a>\';
	}
	// Otherwise, they might want to [logout]...
	else
		echo \'
				<a href="\', $scripturl, \'?action=logout;sesc=\', $context[\'session_id\'], \'">\', ($settings[\'use_image_buttons\'] ? \'<img src="\' . $settings[\'images_url\'] . \'/\' . $context[\'user\'][\'language\'] . \'/logout.gif" alt="\' . $txt[\'logout\'] . \'" border="0" />\' : $txt[\'logout\']), \'</a>\';
}

?>';

	// Step 5: Do the html tag.
	$old_template = preg_replace('~\<html\>~i', '<html\', $context[\'right_to_left\'] ? \' dir="rtl"\' : \'\', \'>', $old_template);

	// Step 6: The javascript stuff.
	$old_template = preg_replace('~\<head\>~i', '<head>
	<script language="JavaScript" type="text/javascript" src="\', $settings[\'default_theme_url\'], \'/script.js"></script>
	<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
		var smf_theme_url = "\', $settings[\'theme_url\'], \'";
		var smf_images_url = "\', $settings[\'images_url\'], \'";
	// ]]></script>
	\' . $context[\'html_headers\'] . \'', $old_template);

	// Step 7: The character set.
	$old_template = preg_replace('~\<meta[^>]+http-equiv=["]?Content-Type["]?[^>]*?\>~i', '<meta http-equiv="Content-Type" content="text/html; charset=\', $context[\'character_set\'], \'" />', $old_template);

	// Step 8: The wonderous <yabb ...> tags.
	$tags = array(
		// <yabb title>
		'title' => '\' . $context[\'page_title\'] . \'',
		// <yabb boardname>
		'boardname' => '\' . $context[\'forum_name\'] . \'',
		// <yabb uname>
		'uname' => '\';

	// If the user is logged in, display stuff like their name, new messages, etc.
	if ($context[\'user\'][\'is_logged\'])
	{
		echo \'
				\', $txt[\'hello_member\'], \' <b>\', $context[\'user\'][\'name\'], \'</b>, \';

		// Are there any members waiting for approval?
		if (!empty($context[\'unapproved_members\']))
			echo \'<br />
				\', $context[\'unapproved_members\'] == 1 ? $txt[\'approve_thereis\'] : $txt[\'approve_thereare\'], \' <a href="\', $scripturl, \'?action=admin;area=viewmembers;sa=browse;type=approve">\', $context[\'unapproved_members\'] == 1 ? $txt[\'approve_member\'] : $context[\'unapproved_members\'] . \' \' . $txt[\'approve_members\'], \'</a> \', $txt[\'approve_members_waiting\'];

		// Is the forum in maintenance mode?
		if ($context[\'in_maintenance\'] && $context[\'user\'][\'is_admin\'])
			echo \'<br />
				<b>\', $txt[\'maintain_mode_on\'], \'</b>\';
	}
	// Otherwise they\'re a guest - so politely ask them to register or login.
	else
		echo \'
				\', $txt[\'welcome_guest\'];

	echo ' . "'",
		// <yabb im>
		'im' => '\';
	if ($context[\'user\'][\'is_logged\'] && $context[\'allow_pm\'])
		echo $txt[\'msg_alert_you_have\'], \' <a href="\', $scripturl, \'?action=pm">\', $context[\'user\'][\'messages\'], \' \', ($context[\'user\'][\'messages\'] != 1 ? $txt[\'msg_alert_messages\'] : $txt[\'message_lowercase\']), \'</a>\', $txt[\'newmessages4\'], \'  \', $context[\'user\'][\'unread_messages\'], \' \', ($context[\'user\'][\'unread_messages\'] == 1 ? $txt[\'newmessages0\'] : $txt[\'newmessages1\']), \'.\';
	echo ' . "'",
		// <yabb time>
		'time' => '\' . $context[\'current_time\'] . \'',
		// <yabb menu>
		'menu' => '\';

	// Show the menu here, according to the menu sub template.
	template_menu();

	echo ' . "'",
		// <yabb position>
		'position' => '\' . $context[\'page_title\'] . \'',
		// <yabb news>
		'news' => '\';

	// Show a random news item? (or you could pick one from news_lines...)
	if (!empty($settings[\'enable_news\']))
		echo \'<b>\', $txt[\'news\'], \':</b> \', $context[\'random_news_line\'];

	echo ' . "'",
		// <yabb main>
		'main' => '\';
}

function template_main_below()
{
	global $context, $settings, $options, $scripturl, $txt;

	echo ' ."'",
		// <yabb vbStyleLogin>
		'vbstylelogin' => '\';

	// Show a vB style login for quick login?
	if ($context[\'show_quick_login\'])
		echo \'
	<table cellspacing="0" cellpadding="0" border="0" align="center" width="90%">
		<tr><td nowrap="nowrap" align="right">
			<form action="\', $scripturl, \'?action=login2" method="post" accept-charset="', $context['character_set'], '"><br />
				<input type="text" name="user" size="7" />
				<input type="password" name="passwrd" size="7" />
				<select name="cookielength">
					<option value="60">\', $txt[\'one_hour\'], \'</option>
					<option value="1440">\', $txt[\'one_day\'], \'</option>
					<option value="10080">\', $txt[\'one_week\'], \'</option>
					<option value="43200">\', $txt[\'one_month\'], \'</option>
					<option value="-1" selected="selected">\', $txt[\'forever\'], \'</option>
				</select>
				<input type="submit" value="\', $txt[\'login\'], \'" /><br />
				\', $txt[\'quick_login_dec\'], \'
			</form>
		</td></tr>
	</table>\';
	else
		echo \'<br />\';

	echo ' . "'",
		// <yabb copyright>
		'copyright' => '\', theme_copyright(), \'',
	);

	foreach ($tags as $yy => $val)
		$old_template = preg_replace('~\<yabb\s+' . $yy . '\>~i', $val, $old_template);

	// Step 9: Add the time creation code.
	$old_template = preg_replace('~\</body\>~i', '\';

	// Show the load time?
	if ($context[\'show_load_time\'])
		echo \'
	<div align="center" class="smalltext">
		\', $txt[\'page_created\'], $context[\'load_time\'], $txt[\'seconds_with\'], $context[\'load_queries\'], $txt[\'queries\'], \'
	</div>\';

	echo \'</body>', $old_template);

	// Step 10: Try to make the style changes.  (function because it's a lot of work...)
	$style = makeStyleChanges($old_template);

	$fp = @fopen($output_dir . '/index.template.php', 'w');
	fwrite($fp, $old_template);
	fclose($fp);
}

// This is here because it's sorta complex.
function phpcodefix($string)
{
	global $smfFunc;

	// First remove the slashes from the single quotes.
	$string = strtr($smfFunc['db_unescape_string']($string), array('\\\'' => '\''));

	// Now add on an end echo and begin echo ;).
	$string = "';
$string
	echo '";

	return $string;
}

function makeStyleChanges(&$old_template)
{
	if (preg_match('~</style>~i', $old_template) == 0)
		return false;

	preg_match('~(<style[^<]+)(</style>)~is', $old_template, $style);

	if (empty($style[1]))
		return false;

	$new_style = $style[1];

	// Add some extra stuff...
	$new_style .= '
.quoteheader, .codeheader {color: black; text-decoration: none; font-style: normal; font-weight: bold;}
.smalltext {font-size: 8pt;}
.normaltext {font-size: 10pt;}
.largetext {font-size: 12pt;}
input.check {background-color: transparent;}';

	// Add some stuff to .code and .quote...
	$new_style = preg_replace('~(\.code\s*[{][^}]+)}~is', '$1; border: 1px solid black; margin: 1px; padding: 1px;}', $new_style);
	$new_style = preg_replace('~(\.quote\s*[{][^}]+)}~is', '$1; border: 1px solid black; margin: 1px; padding: 1px;}', $new_style);
	$new_style = preg_replace('~(\.code,\s*\.quote\s*[{][^}]+)}~is', '$1; border: 1px solid black; margin: 1px; padding: 1px;}', $new_style);

	// Copy from .text1 => .titlebg.
	preg_match('~\.text1\s*[{]([^}]+)}~is', $new_style, $temp);
	if (isset($temp[1]))
	{
		$new_style = preg_replace('~\.titlebg(\s*[{])([^}]+)}~is', '.titlebg, tr.titlebg th, tr.titlebg td, .titlebg a:link, .titlebg a:visited, .titlebg a:hover$1' . $temp[1] . ';$2}', $new_style);
		$new_style = preg_replace('~\.text1\s*[{]([^}]+)}~is', '', $new_style);
	}
	else
		$new_style = preg_replace('~\.titlebg(\s*[{][^}]+)}~is', '.titlebg, tr.titlebg th, tr.titlebg td, .titlebg a:link, .titlebg a:visited, .titlebg a:hover$1}', $new_style);

	// Look for the background-color of bordercolor... if it's not found, try black. (dumb guess!)
	preg_match('~\.bordercolor\s*[{]([^}]+)}~is', $new_style, $temp);
	if (!empty($temp[1]))
		preg_match('~background(?:-color)?:\s*([^;}\s]+)~is', $temp[1], $temp);
	if (empty($temp[1]))
		$temp[1] = 'black';

	$new_style .= '
.tborder {border: 1px solid ' . $temp[1] . ';}';

	$old_template = str_replace($style[0], $new_style . "\n" . $style[2], $old_template);

	return true;
}

?>