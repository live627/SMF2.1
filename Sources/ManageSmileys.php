<?php
/**********************************************************************************
* ManageSmileys.php                                                               *
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

/* // !!!

	void ManageSmileys()
		// !!!

	void EditSmileySettings()
		// !!!

	void EditSmileySets()
		// !!!

	void AddSmiley()
		// !!!

	void EditSmileys()
		// !!!

	void Editsmiley_order()
		// !!!

	void InstallSmileySet()
		// !!!

	void ImportSmileys($smileyPath)
		// !!!
*/

function ManageSmileys()
{
	global $context, $txt, $scripturl, $modSettings;

	isAllowedTo('manage_smileys');

	loadLanguage('ManageSmileys');
	loadTemplate('ManageSmileys');

	$subActions = array(
		'addsmiley' => 'AddSmiley',
		'editicon' => 'EditMessageIcons',
		'editicons' => 'EditMessageIcons',
		'editsets' => 'EditSmileySets',
		'editsmileys' => 'EditSmileys',
		'import' => 'EditSmileySets',
		'modifyset' => 'EditSmileySets',
		'modifysmiley' => 'EditSmileys',
		'setorder' => 'Editsmiley_order',
		'settings' => 'EditSmileySettings',
		'install' => 'InstallSmileySet'
	);

	// Default the sub-action to 'edit smiley settings'.
	$_REQUEST['sa'] = isset($_REQUEST['sa']) && isset($subActions[$_REQUEST['sa']]) ? $_REQUEST['sa'] : 'settings';

	$context['page_title'] = &$txt['smileys_manage'];
	$context['sub_action'] = $_REQUEST['sa'];
	$context['sub_template'] = &$context['sub_action'];

	// Load up all the tabs...
	$context['admin_tabs'] = array(
		'title' => &$txt['smileys_manage'],
		'help' => 'smileys',
		'description' => $txt['smiley_settings_explain'],
		'tabs' => array(
			'editsets' => array(
				'title' => $txt['smiley_sets'],
				'description' => $txt['smiley_editsets_explain'],
				'href' => $scripturl . '?action=admin;area=smileys;sa=editsets',
			),
			'addsmiley' => array(
				'title' => $txt['smileys_add'],
				'description' => $txt['smiley_addsmiley_explain'],
				'href' => $scripturl . '?action=admin;area=smileys;sa=addsmiley',
			),
			'editsmileys' => array(
				'title' => $txt['smileys_edit'],
				'description' => $txt['smiley_editsmileys_explain'],
				'href' => $scripturl . '?action=admin;area=smileys;sa=editsmileys',
			),
			'setorder' => array(
				'title' => $txt['smileys_set_order'],
				'description' => $txt['smiley_setorder_explain'],
				'href' => $scripturl . '?action=admin;area=smileys;sa=setorder',
			),
			'editicons' => array(
				'title' => $txt['icons_edit_message_icons'],
				'description' => $txt['icons_edit_icons_explain'],
				'href' => $scripturl . '?action=admin;area=smileys;sa=editicons',
			),
			'settings' => array(
				'title' => $txt['settings'],
				'description' => $txt['smiley_settings_explain'],
				'href' => $scripturl . '?action=admin;area=smileys;sa=settings',
				'is_last' => true,
			),
		),
	);

	// Select the right tab based on the sub action.
	if (isset($context['admin_tabs']['tabs'][$context['sub_action']]))
		$context['admin_tabs']['tabs'][$context['sub_action']]['is_selected'] = true;

	// Some settings may not be enabled, disallow these from the tabs as appropriate.
	if (empty($modSettings['messageIcons_enable']))
		unset($context['admin_tabs']['tabs']['editicons']);
	if (empty($modSettings['smiley_enable']))
	{
		unset($context['admin_tabs']['tabs']['addsmiley']);
		unset($context['admin_tabs']['tabs']['editsmileys']);
		unset($context['admin_tabs']['tabs']['setorder']);
	}

	// Call the right function for this sub-acton.
	$subActions[$_REQUEST['sa']]();
}

function EditSmileySettings()
{
	global $modSettings, $context, $settings, $db_prefix, $txt, $boarddir, $sourcedir, $scripturl;

	// Setup the basics of the settings template.
	require_once($sourcedir .'/ManageServer.php');
	$context['sub_template'] = 'show_settings';

	// The directories...
	$context['smileys_dir'] = empty($modSettings['smileys_dir']) ? $boarddir . '/Smileys' : $modSettings['smileys_dir'];
	$context['smileys_dir_found'] = is_dir($context['smileys_dir']);

	// Get the names of the smiley sets.
	$smiley_sets = explode(',', $modSettings['smiley_sets_known']);
	$set_names = explode("\n", $modSettings['smiley_sets_names']);

	$smiley_context = array();
	foreach ($smiley_sets as $i => $set)
		$smiley_context[$set] = $set_names[$i];

	// All the settings for the page...
	$config_vars = array(
		array('title', 'settings'),
			// Inline permissions.
			array('permissions', 'manage_smileys'),
		'',
			array('select', 'smiley_sets_default', $smiley_context),
			array('check', 'smiley_sets_enable'),
			array('check', 'smiley_enable', 'subtext' => $txt['smileys_enable_note']),
			array('text', 'smileys_url'),
			array('text', 'smileys_dir', 'invalid' => !$context['smileys_dir_found']),
		'',
			// Message icons.
			array('check', 'messageIcons_enable', 'subtext' => $txt['setting_messageIcons_enable_note']),
	);

	// Finish up the form...
	$context['post_url'] = $scripturl . '?action=admin;area=smileys;save;sa=settings';
	$context['permissions_excluded'] = array(-1);

	// Saving the settings?
	if (isset($_GET['save']))
	{
		// Validate the smiley set name.
		$_POST['smiley_sets_default'] = empty($smiley_context[$_POST['smiley_sets_default']]) ? 'default' : $_POST['smiley_sets_default'];

		saveDBSettings($config_vars);

		cache_put_data('parsing_smileys', null, 480);
		cache_put_data('posting_smileys', null, 480);

		redirectexit('action=admin;area=smileys;sa=settings');
	}

	prepareDBSettingContext($config_vars);
}

function EditSmileySets()
{
	global $modSettings, $context, $settings, $db_prefix, $txt, $boarddir, $smfFunc;

	// Set the right tab to be selected.
	$context['admin_tabs']['tabs']['editsets']['is_selected'] = true;

	// They must've been submitted a form.
	if (isset($_POST['sc']))
	{
		checkSession();

		// Delete selected smiley sets.
		if (!empty($_POST['delete']) && !empty($_POST['smiley_set']))
		{
			$set_paths = explode(',', $modSettings['smiley_sets_known']);
			$set_names = explode("\n", $modSettings['smiley_sets_names']);
			foreach ($_POST['smiley_set'] as $id => $val)
				if (isset($set_paths[$id], $set_names[$id]) && !empty($id))
					unset($set_paths[$id], $set_names[$id]);

			updateSettings(array(
				'smiley_sets_known' => $smfFunc['db_escape_string'](implode(',', $set_paths)),
				'smiley_sets_names' => $smfFunc['db_escape_string'](implode("\n", $set_names)),
				'smiley_sets_default' => $smfFunc['db_escape_string'](in_array($modSettings['smiley_sets_default'], $set_paths) ? $modSettings['smiley_sets_default'] : $set_paths[0]),
			));

			cache_put_data('parsing_smileys', null, 480);
			cache_put_data('posting_smileys', null, 480);
		}
		// Add a new smiley set.
		elseif (!empty($_POST['add']))
			$context['sub_action'] = 'modifyset';
		// Create or modify a smiley set.
		elseif (isset($_POST['set']))
		{
			$set_paths = explode(',', $modSettings['smiley_sets_known']);
			$set_names = explode("\n", $modSettings['smiley_sets_names']);

			// Create a new smiley set.
			if ($_POST['set'] == -1 && isset($_POST['smiley_sets_path']))
			{
				if (in_array($_POST['smiley_sets_path'], $set_paths))
					fatal_lang_error('smiley_set_already_exists');

				updateSettings(array(
					'smiley_sets_known' => $smfFunc['db_escape_string']($modSettings['smiley_sets_known']) . ',' . $_POST['smiley_sets_path'],
					'smiley_sets_names' => $smfFunc['db_escape_string']($modSettings['smiley_sets_names']) . "\n" . $_POST['smiley_sets_name'],
					'smiley_sets_default' => empty($_POST['smiley_sets_default']) ? $modSettings['smiley_sets_default'] : $smfFunc['db_escape_string']($_POST['smiley_sets_path']),
				));
			}
			// Modify an existing smiley set.
			else
			{
				// Make sure the smiley set exists.
				if (!isset($set_paths[$_POST['set']]) || !isset($set_names[$_POST['set']]))
					fatal_lang_error('smiley_set_not_found');

				// Make sure the path is not yet used by another smileyset.
				if (in_array($_POST['smiley_sets_path'], $set_paths) && $_POST['smiley_sets_path'] != $set_paths[$_POST['set']])
					fatal_lang_error('smiley_set_path_already_used');

				$set_paths[$_POST['set']] = $smfFunc['db_unescape_string']($_POST['smiley_sets_path']);
				$set_names[$_POST['set']] = $smfFunc['db_unescape_string']($_POST['smiley_sets_name']);
				updateSettings(array(
					'smiley_sets_known' => $smfFunc['db_escape_string'](implode(',', $set_paths)),
					'smiley_sets_names' => $smfFunc['db_escape_string'](implode("\n", $set_names)),
					'smiley_sets_default' => empty($_POST['smiley_sets_default']) ? $smfFunc['db_escape_string']($modSettings['smiley_sets_default']) : $_POST['smiley_sets_path']
				));
			}

			// The user might have checked to also import smileys.
			if (!empty($_POST['smiley_sets_import']))
				ImportSmileys($_POST['smiley_sets_path']);

			cache_put_data('parsing_smileys', null, 480);
			cache_put_data('posting_smileys', null, 480);
		}
	}

	// Load all available smileysets...
	$context['smiley_sets'] = explode(',', $modSettings['smiley_sets_known']);
	$set_names = explode("\n", $modSettings['smiley_sets_names']);
	foreach ($context['smiley_sets'] as $i => $set)
		$context['smiley_sets'][$i] = array(
			'id' => $i,
			'path' => $set,
			'name' => $set_names[$i],
			'selected' => $set == $modSettings['smiley_sets_default']
		);

	// Importing any smileys from an existing set?
	if ($context['sub_action'] == 'import')
	{
		checkSession('get');
		$_GET['set'] = (int) $_GET['set'];

		// Sanity check - then import.
		if (isset($context['smiley_sets'][$_GET['set']]))
			ImportSmileys($context['smiley_sets'][$_GET['set']]['path']);

		// Force the process to continue.
		$context['sub_action'] = 'modifyset';
	}
	// If we're modifying or adding a smileyset, some context info needs to be set.
	if ($context['sub_action'] == 'modifyset')
	{
		$_GET['set'] = !isset($_GET['set']) ? -1 : (int) $_GET['set'];
		if ($_GET['set'] == -1 || !isset($context['smiley_sets'][$_GET['set']]))
			$context['current_set'] = array(
				'id' => '-1',
				'path' => '',
				'name' => '',
				'selected' => false,
				'is_new' => true,
			);
		else
		{
			$context['current_set'] = &$context['smiley_sets'][$_GET['set']];
			$context['current_set']['is_new'] = false;

			// Calculate whether there are any smileys in the directory that can be imported.
			if (!empty($modSettings['smiley_enable']) && !empty($modSettings['smileys_dir']) && is_dir($modSettings['smileys_dir'] . '/' . $context['current_set']['path']))
			{
				$smileys = array();
				$dir = dir($modSettings['smileys_dir'] . '/' . $context['current_set']['path']);
				while ($entry = $dir->read())
				{
					if (in_array(strrchr($entry, '.'), array('.jpg', '.gif', '.jpeg', '.png')))
						$smileys[strtolower($entry)] = $smfFunc['db_escape_string']($entry);
				}
				$dir->close();

				// Exclude the smileys that are already in the database.
				$request = $smfFunc['db_query']('', "
					SELECT filename
					FROM {$db_prefix}smileys
					WHERE filename IN ('" . implode("', '", $smileys) . "')", __FILE__, __LINE__);
				while ($row = $smfFunc['db_fetch_assoc']($request))
					if (isset($smileys[strtolower($row['filename'])]))
						unset($smileys[strtolower($row['filename'])]);
				$smfFunc['db_free_result']($request);

				$context['current_set']['can_import'] = count($smileys);
				// Setup this string to look nice.
				$txt['smiley_set_import_multiple'] = sprintf($txt['smiley_set_import_multiple'], $context['current_set']['can_import']);
			}
		}

		// Retrieve all potential smiley set directories.
		$context['smiley_set_dirs'] = array();
		if (!empty($modSettings['smileys_dir']) && is_dir($modSettings['smileys_dir']))
		{
			$dir = dir($modSettings['smileys_dir']);
			while ($entry = $dir->read())
			{
				if (!in_array($entry, array('.', '..')) && is_dir($modSettings['smileys_dir'] . '/' . $entry))
					$context['smiley_set_dirs'][] = array(
						'id' => $entry,
						'path' => $modSettings['smileys_dir'] . '/' . $entry,
						'selectable' => $entry == $context['current_set']['path'] || !in_array($entry, explode(',', $modSettings['smiley_sets_known'])),
						'current' => $entry == $context['current_set']['path'],
					);
			}
			$dir->close();
		}
	}
}

function AddSmiley()
{
	global $modSettings, $context, $settings, $db_prefix, $txt, $boarddir, $smfFunc;

	// Get a list of all known smiley sets.
	$context['smileys_dir'] = empty($modSettings['smileys_dir']) ? $boarddir . '/Smileys' : $modSettings['smileys_dir'];
	$context['smileys_dir_found'] = is_dir($context['smileys_dir']);
	$context['smiley_sets'] = explode(',', $modSettings['smiley_sets_known']);
	$set_names = explode("\n", $modSettings['smiley_sets_names']);
	foreach ($context['smiley_sets'] as $i => $set)
		$context['smiley_sets'][$i] = array(
			'id' => $i,
			'path' => $set,
			'name' => $set_names[$i],
			'selected' => $set == $modSettings['smiley_sets_default']
		);

	// Submitting a form?
	if (isset($_POST['sc'], $_POST['smiley_code']))
	{
		checkSession();

		// Some useful arrays... types we allow - and ports we don't!
		$allowedTypes = array('jpeg', 'jpg', 'gif', 'png', 'bmp');
		$disabledFiles = array('con', 'com1', 'com2', 'com3', 'com4', 'prn', 'aux', 'lpt1', '.htaccess', 'index.php');

		$_POST['smiley_code'] = htmltrim__recursive($_POST['smiley_code']);
		$_POST['smiley_location'] = empty($_POST['smiley_location']) || $_POST['smiley_location'] > 2 || $_POST['smiley_location'] < 0 ? 0 : (int) $_POST['smiley_location'];
		$_POST['smiley_filename'] = htmltrim__recursive($_POST['smiley_filename']);

		// Make sure some code was entered.
		if (empty($_POST['smiley_code']))
			fatal_lang_error('smiley_has_no_code');

		// Check whether the new code has duplicates. It should be unique.
		$request = $smfFunc['db_query']('', "
			SELECT id_smiley
			FROM {$db_prefix}smileys
			WHERE code = BINARY '$_POST[smiley_code]'", __FILE__, __LINE__);
		if ($smfFunc['db_num_rows']($request) > 0)
			fatal_lang_error('smiley_not_unique');
		$smfFunc['db_free_result']($request);

		// If we are uploading - check all the smiley sets are writable!
		if ($_POST['method'] != 'existing')
		{
			$writeErrors = array();
			foreach ($context['smiley_sets'] as $set)
			{
				if (!is_writable($context['smileys_dir'] . '/' . $set['path']))
					$writeErrors[] = $set['path'];
			}
			if (!empty($writeErrors))
				fatal_lang_error('smileys_upload_error_notwritable', true, array(implode(', ', $writeErrors)));
		}

		// Uploading just one smiley for all of them?
		if (isset($_POST['sameall']) && isset($_FILES['uploadSmiley']['name']) && $_FILES['uploadSmiley']['name'] != '')
		{
			if (!is_uploaded_file($_FILES['uploadSmiley']['tmp_name']) || (@ini_get('open_basedir') == '' && !file_exists($_FILES['uploadSmiley']['tmp_name'])))
				fatal_lang_error('smileys_upload_error');

			// Sorry, no spaces, dots, or anything else but letters allowed.
			$_FILES['uploadSmiley']['name'] = preg_replace(array('/\s/', '/\.[\.]+/', '/[^\w_\.\-]/'), array('_', '.', ''), $_FILES['uploadSmiley']['name']);

			// We only allow image files - it's THAT simple - no messing around here...
			if (!in_array(strtolower(substr(strrchr($_FILES['uploadSmiley']['name'], '.'), 1)), $allowedTypes))
				fatal_lang_error('smileys_upload_error_types', false, array(implode(', ', $allowedTypes)));

			// We only need the filename...
			$destName = basename($_FILES['uploadSmiley']['name']);

			// Make sure they aren't trying to upload a nasty file - for their own good here!
			if (in_array(strtolower($destName), $disabledFiles))
				fatal_lang_error('smileys_upload_error_illegal');

			// Check if the file already exists... and if not move it to EVERY smiley set directory.
			$i = 0;
			// Keep going until we find a set the file doesn't exist in. (or maybe it exists in all of them?)
			while (isset($context['smiley_sets'][$i]) && file_exists($context['smileys_dir'] . '/' . $context['smiley_sets'][$i]['path'] . '/' . $destName))
				$i++;

			// Okay, we're going to put the smiley right here, since it's not there yet!
			if (isset($context['smiley_sets'][$i]['path']))
			{
				$smileyLocation = $context['smileys_dir'] . '/' . $context['smiley_sets'][$i]['path'] . '/' . $destName;
				move_uploaded_file($_FILES['uploadSmiley']['tmp_name'], $smileyLocation);
				@chmod($smileyLocation, 0644);

				// Now, we want to move it from there to all the other sets.
				for ($n = count($context['smiley_sets']); $i < $n; $i++)
				{
					$currentPath = $context['smileys_dir'] . '/' . $context['smiley_sets'][$i]['path'] . '/' . $destName;

					// The file is already there!  Don't overwrite it!
					if (file_exists($currentPath))
						continue;

					// Okay, so copy the first one we made to here.
					copy($smileyLocation, $currentPath);
					@chmod($currentPath, 0644);
				}
			}

			// Finally make sure it's saved correctly!
			$_POST['smiley_filename'] = $destName;
		}
		// What about uploading several files?
		elseif ($_POST['method'] != 'existing')
		{
			foreach ($_FILES as $name => $data)
			{
				if ($_FILES[$name]['name'] == '')
					fatal_lang_error('smileys_upload_error_blank');

				if (empty($newName))
					$newName = basename($_FILES[$name]['name']);
				elseif (basename($_FILES[$name]['name']) != $newName)
					fatal_lang_error('smileys_upload_error_name');
			}

			foreach ($context['smiley_sets'] as $i => $set)
			{
				if (!isset($_FILES['individual_' . $set['name']]['name']) || $_FILES['individual_' . $set['name']]['name'] == '')
					continue;

				// Got one...
				if (!is_uploaded_file($_FILES['individual_' . $set['name']]['tmp_name']) || (@ini_get('open_basedir') == '' && !file_exists($_FILES['individual_' . $set['name']]['tmp_name'])))
					fatal_lang_error('smileys_upload_error');

				// Sorry, no spaces, dots, or anything else but letters allowed.
				$_FILES['individual_' . $set['name']]['name'] = preg_replace(array('/\s/', '/\.[\.]+/', '/[^\w_\.\-]/'), array('_', '.', ''), $_FILES['individual_' . $set['name']]['name']);

				// We only allow image files - it's THAT simple - no messing around here...
				if (!in_array(strtolower(substr(strrchr($_FILES['individual_' . $set['name']]['name'], '.'), 1)), $allowedTypes))
					fatal_lang_error('smileys_upload_error_types', false, array(implode(', ', $allowedTypes)));

				// We only need the filename...
				$destName = basename($_FILES['individual_' . $set['name']]['name']);

				// Make sure they aren't trying to upload a nasty file - for their own good here!
				if (in_array(strtolower($destName), $disabledFiles))
					fatal_lang_error('smileys_upload_error_illegal');

				// If the file exists - ignore it.
				$smileyLocation = $context['smileys_dir'] . '/' . $set['path'] . '/' . $destName;
				if (file_exists($smileyLocation))
					continue;

				// Finally - move the image!
				move_uploaded_file($_FILES['individual_' . $set['name']]['tmp_name'], $smileyLocation);
				@chmod($smileyLocation, 0644);

				// Should always be saved correctly!
				$_POST['smiley_filename'] = $destName;
			}
		}

		// Also make sure a filename was given.
		if (empty($_POST['smiley_filename']))
			fatal_lang_error('smiley_has_no_filename');

		// Find the position on the right.
		$smiley_order = '0';
		if ($_POST['smiley_location'] != 1)
		{
			$request = $smfFunc['db_query']('', "
				SELECT MAX(smiley_order) + 1
				FROM {$db_prefix}smileys
				WHERE hidden = $_POST[smiley_location]
					AND smiley_row = 0", __FILE__, __LINE__);
			list ($smiley_order) = $smfFunc['db_fetch_row']($request);
			$smfFunc['db_free_result']($request);

			if (empty($smiley_order))
				$smiley_order = '0';
		}
		$smfFunc['db_query']('', "
			INSERT INTO {$db_prefix}smileys
				(code, filename, description, hidden, smiley_order)
			VALUES (SUBSTRING('$_POST[smiley_code]', 1, 30), SUBSTRING('$_POST[smiley_filename]', 1, 48), SUBSTRING('$_POST[smiley_description]', 1, 80), $_POST[smiley_location], $smiley_order)", __FILE__, __LINE__);

		cache_put_data('parsing_smileys', null, 480);
		cache_put_data('posting_smileys', null, 480);

		// No errors? Out of here!
		redirectexit('action=admin;area=smileys;sa=editsmileys');
	}

	$context['selected_set'] = $modSettings['smiley_sets_default'];

	// Get all possible filenames for the smileys.
	$context['filenames'] = array();
	if ($context['smileys_dir_found'])
	{
		foreach ($context['smiley_sets'] as $smiley_set)
		{
			if (!file_exists($context['smileys_dir'] . '/' . $smiley_set['path']))
				continue;

			$dir = dir($context['smileys_dir'] . '/' . $smiley_set['path']);
			while ($entry = $dir->read())
			{
				if (!in_array($entry, $context['filenames']) && in_array(strrchr($entry, '.'), array('.jpg', '.gif', '.jpeg', '.png')))
					$context['filenames'][strtolower($entry)] = array(
						'id' => htmlspecialchars($entry),
						'selected' => false,
					);
			}
			$dir->close();
		}
		ksort($context['filenames']);
	}

	// Create a new smiley from scratch.
	$context['filenames'] = array_values($context['filenames']);
	$context['current_smiley'] = array(
		'id' => 0,
		'code' => '',
		'filename' => $context['filenames'][0]['id'],
		'description' => &$txt['smileys_default_description'],
		'location' => 0,
		'is_new' => true,
	);
}

function EditSmileys()
{
	global $modSettings, $context, $settings, $db_prefix, $txt, $boarddir, $smfFunc;

	// Force the correct tab to be displayed.
	$context['admin_tabs']['tabs']['editsmileys']['is_selected'] = true;

	// Submitting a form?
	if (isset($_POST['sc']))
	{
		checkSession();

		// Changing the selected smileys?
		if (isset($_POST['smiley_action']) && !empty($_POST['checked_smileys']))
		{
			foreach ($_POST['checked_smileys'] as $id => $smiley_id)
				$_POST['checked_smileys'][$id] = (int) $smiley_id;

			if ($_POST['smiley_action'] == 'delete')
				$smfFunc['db_query']('', "
					DELETE FROM {$db_prefix}smileys
					WHERE id_smiley IN (" . implode(', ', $_POST['checked_smileys']) . ')', __FILE__, __LINE__);
			// Changing the status of the smiley?
			else
			{
				// Check it's a valid type.
				$displayTypes = array(
					'post' => 0,
					'hidden' => 1,
					'popup' => 2
				);
				if (isset($displayTypes[$_POST['smiley_action']]))
					$smfFunc['db_query']('', "
						UPDATE {$db_prefix}smileys
						SET hidden = " . $displayTypes[$_POST['smiley_action']] . "
						WHERE id_smiley IN (" . implode(', ', $_POST['checked_smileys']) . ')', __FILE__, __LINE__);
			}
		}
		// Create/modify a smiley.
		elseif (isset($_POST['smiley']))
		{
			$_POST['smiley'] = (int) $_POST['smiley'];
			$_POST['smiley_code'] = htmltrim__recursive($_POST['smiley_code']);
			$_POST['smiley_filename'] = htmltrim__recursive($_POST['smiley_filename']);
			$_POST['smiley_location'] = empty($_POST['smiley_location']) || $_POST['smiley_location'] > 2 || $_POST['smiley_location'] < 0 ? 0 : (int) $_POST['smiley_location'];

			// Make sure some code was entered.
			if (empty($_POST['smiley_code']))
				fatal_lang_error('smiley_has_no_code');

			// Also make sure a filename was given.
			if (empty($_POST['smiley_filename']))
				fatal_lang_error('smiley_has_no_filename');

			// Check whether the new code has duplicates. It should be unique.
			$request = $smfFunc['db_query']('', "
				SELECT id_smiley
				FROM {$db_prefix}smileys
				WHERE code = BINARY '$_POST[smiley_code]'" . (empty($_POST['smiley']) ? '' : "
					AND id_smiley != $_POST[smiley]"), __FILE__, __LINE__);
			if ($smfFunc['db_num_rows']($request) > 0)
				fatal_lang_error('smiley_not_unique');
			$smfFunc['db_free_result']($request);

			$smfFunc['db_query']('', "
				UPDATE {$db_prefix}smileys
				SET
					code = '$_POST[smiley_code]',
					filename = '$_POST[smiley_filename]',
					description = '$_POST[smiley_description]',
					hidden = $_POST[smiley_location]
				WHERE id_smiley = $_POST[smiley]", __FILE__, __LINE__);

			// Sort all smiley codes for more accurate parsing (longest code first).
			$smfFunc['db_query']('alter_table_smileys', "
				ALTER TABLE {$db_prefix}smileys
				ORDER BY LENGTH(code) DESC", __FILE__, __LINE__);
		}

		cache_put_data('parsing_smileys', null, 480);
		cache_put_data('posting_smileys', null, 480);
	}

	// Load all known smiley sets.
	$context['smiley_sets'] = explode(',', $modSettings['smiley_sets_known']);
	$set_names = explode("\n", $modSettings['smiley_sets_names']);
	foreach ($context['smiley_sets'] as $i => $set)
		$context['smiley_sets'][$i] = array(
			'id' => $i,
			'path' => $set,
			'name' => $set_names[$i],
			'selected' => $set == $modSettings['smiley_sets_default']
		);

	// Prepare overview of all (custom) smileys.
	if ($context['sub_action'] == 'editsmileys')
	{
		$sortColumns = array(
			'code',
			'filename',
			'description',
			'hidden',
		);

		// Default to 'order by filename'.
		$context['sort'] = empty($_REQUEST['sort']) || !in_array($_REQUEST['sort'], $sortColumns) ? 'filename' : $_REQUEST['sort'];

		$request = $smfFunc['db_query']('', "
			SELECT id_smiley, code, filename, description, smiley_row, smiley_order, hidden
			FROM {$db_prefix}smileys
			ORDER BY $context[sort]", __FILE__, __LINE__);
		$context['smileys'] = array();
		while ($row = $smfFunc['db_fetch_assoc']($request))
			$context['smileys'][] = array(
				'id' => $row['id_smiley'],
				'code' => htmlspecialchars($row['code']),
				'filename' => htmlspecialchars($row['filename']),
				'description' => htmlspecialchars($row['description']),
				'row' => $row['smiley_row'],
				'order' => $row['smiley_order'],
				'location' => empty($row['hidden']) ? $txt['smileys_location_form'] : ($row['hidden'] == 1 ? $txt['smileys_location_hidden'] : $txt['smileys_location_popup']),
				'sets_not_found' => array(),
			);
		$smfFunc['db_free_result']($request);

		if (!empty($modSettings['smileys_dir']) && is_dir($modSettings['smileys_dir']))
		{
			foreach ($context['smiley_sets'] as $smiley_set)
			{
				foreach ($context['smileys'] as $smiley_id => $smiley)
					if (!file_exists($modSettings['smileys_dir'] . '/' . $smiley_set['path'] . '/' . $smiley['filename']))
						$context['smileys'][$smiley_id]['sets_not_found'][] = $smiley_set['path'];
			}
		}

		$context['selected_set'] = $modSettings['smiley_sets_default'];
	}
	// Modifying smileys.
	elseif ($context['sub_action'] == 'modifysmiley')
	{
		// Get a list of all known smiley sets.
		$context['smileys_dir'] = empty($modSettings['smileys_dir']) ? $boarddir . '/Smileys' : $modSettings['smileys_dir'];
		$context['smileys_dir_found'] = is_dir($context['smileys_dir']);
		$context['smiley_sets'] = explode(',', $modSettings['smiley_sets_known']);
		$set_names = explode("\n", $modSettings['smiley_sets_names']);
		foreach ($context['smiley_sets'] as $i => $set)
			$context['smiley_sets'][$i] = array(
				'id' => $i,
				'path' => $set,
				'name' => $set_names[$i],
				'selected' => $set == $modSettings['smiley_sets_default']
			);

		$context['selected_set'] = $modSettings['smiley_sets_default'];

		// Get all possible filenames for the smileys.
		$context['filenames'] = array();
		if ($context['smileys_dir_found'])
		{
			foreach ($context['smiley_sets'] as $smiley_set)
			{
				if (!file_exists($context['smileys_dir'] . '/' . $smiley_set['path']))
					continue;

				$dir = dir($context['smileys_dir'] . '/' . $smiley_set['path']);
				while ($entry = $dir->read())
				{
					if (!in_array($entry, $context['filenames']) && in_array(strrchr($entry, '.'), array('.jpg', '.gif', '.jpeg', '.png')))
						$context['filenames'][strtolower($entry)] = array(
							'id' => htmlspecialchars($entry),
							'selected' => false,
						);
				}
				$dir->close();
			}
			ksort($context['filenames']);
		}

		$request = $smfFunc['db_query']('', "
			SELECT id_smiley AS id, code, filename, description, hidden AS location, 0 AS is_new
			FROM {$db_prefix}smileys
			WHERE id_smiley = " . (int) $_REQUEST['smiley'], __FILE__, __LINE__);
		if ($smfFunc['db_num_rows']($request) != 1)
			fatal_lang_error('smiley_not_found');
		$context['current_smiley'] = $smfFunc['db_fetch_assoc']($request);
		$smfFunc['db_free_result']($request);

		$context['current_smiley']['code'] = htmlspecialchars($context['current_smiley']['code']);
		$context['current_smiley']['filename'] = htmlspecialchars($context['current_smiley']['filename']);
		$context['current_smiley']['description'] = htmlspecialchars($context['current_smiley']['description']);

		if (isset($context['filenames'][strtolower($context['current_smiley']['filename'])]))
			$context['filenames'][strtolower($context['current_smiley']['filename'])]['selected'] = true;
	}
}

function Editsmiley_order()
{
	global $modSettings, $context, $settings, $db_prefix, $txt, $boarddir, $smfFunc;

	// Move smileys to another position.
	if (isset($_GET['sesc']))
	{
		checkSession('get');

		$_GET['location'] = empty($_GET['location']) || $_GET['location'] != 'popup' ? 0 : 2;
		$_GET['source'] = empty($_GET['source']) ? 0 : (int) $_GET['source'];

		if (empty($_GET['source']))
			fatal_lang_error('smiley_not_found');

		if (!empty($_GET['after']))
		{
			$_GET['after'] = (int) $_GET['after'];

			$request = $smfFunc['db_query']('', "
				SELECT smiley_row, smiley_order, hidden
				FROM {$db_prefix}smileys
				WHERE hidden = $_GET[location]
					AND id_smiley = $_GET[after]", __FILE__, __LINE__);
			if ($smfFunc['db_num_rows']($request) != 1)
				fatal_lang_error('smiley_not_found');
			list ($smiley_row, $smiley_order, $smileyLocation) = $smfFunc['db_fetch_row']($request);
			$smfFunc['db_free_result']($request);
		}
		else
		{
			$smiley_row = (int) $_GET['row'];
			$smiley_order = -1;
			$smileyLocation = (int) $_GET['location'];
		}

		$smfFunc['db_query']('', "
			UPDATE {$db_prefix}smileys
			SET smiley_order = smiley_order + 1
			WHERE hidden = $_GET[location]
				AND smiley_row = $smiley_row
				AND smiley_order > $smiley_order", __FILE__, __LINE__);

		$smfFunc['db_query']('', "
			UPDATE {$db_prefix}smileys
			SET
				smiley_order = $smiley_order + 1,
				smiley_row = $smiley_row,
				hidden = $smileyLocation
			WHERE id_smiley = $_GET[source]", __FILE__, __LINE__);

		cache_put_data('parsing_smileys', null, 480);
		cache_put_data('posting_smileys', null, 480);
	}

	$request = $smfFunc['db_query']('', "
		SELECT id_smiley, code, filename, description, smiley_row, smiley_order, hidden
		FROM {$db_prefix}smileys
		WHERE hidden != 1
		ORDER BY smiley_order, smiley_row", __FILE__, __LINE__);
	$context['smileys'] = array(
		'postform' => array(
			'rows' => array(),
		),
		'popup' => array(
			'rows' => array(),
		),
	);
	while ($row = $smfFunc['db_fetch_assoc']($request))
	{
		$location = empty($row['hidden']) ? 'postform' : 'popup';
		$context['smileys'][$location]['rows'][$row['smiley_row']][] = array(
			'id' => $row['id_smiley'],
			'code' => htmlspecialchars($row['code']),
			'filename' => htmlspecialchars($row['filename']),
			'description' => htmlspecialchars($row['description']),
			'row' => $row['smiley_row'],
			'order' => $row['smiley_order'],
			'selected' => !empty($_REQUEST['move']) && $_REQUEST['move'] == $row['id_smiley'],
		);
	}
	$smfFunc['db_free_result']($request);

	$context['move_smiley'] = empty($_REQUEST['move']) ? 0 : (int) $_REQUEST['move'];

	// Make sure all rows are sequential.
	foreach (array_keys($context['smileys']) as $location)
		$context['smileys'][$location] = array(
			'id' => $location,
			'title' => $location == 'postform' ? $txt['smileys_location_form'] : $txt['smileys_location_popup'],
			'description' => $location == 'postform' ? $txt['smileys_location_form_description'] : $txt['smileys_location_popup_description'],
			'last_row' => count($context['smileys'][$location]['rows']),
			'rows' => array_values($context['smileys'][$location]['rows']),
		);

	// Check & fix smileys that are not ordered properly in the database.
	foreach (array_keys($context['smileys']) as $location)
	{
		foreach ($context['smileys'][$location]['rows'] as $id => $smiley_row)
		{
			// Fix empty rows if any.
			if ($id != $smiley_row[0]['row'])
			{
				$smfFunc['db_query']('', "
					UPDATE {$db_prefix}smileys
					SET smiley_row = $id
					WHERE smiley_row = {$smiley_row[0]['row']}
						AND hidden = " . ($location == 'postform' ? '0' : '2'), __FILE__, __LINE__);
				// Only change the first row value of the first smiley (we don't need the others :P).
				$context['smileys'][$location]['rows'][$id][0]['row'] = $id;
			}
			// Make sure the smiley order is always sequential.
			foreach ($smiley_row as $order_id => $smiley)
				if ($order_id != $smiley['order'])
					$smfFunc['db_query']('', "
						UPDATE {$db_prefix}smileys
						SET smiley_order = $order_id
						WHERE id_smiley = $smiley[id]", __FILE__, __LINE__);
		}
	}

	cache_put_data('parsing_smileys', null, 480);
	cache_put_data('posting_smileys', null, 480);
}

function InstallSmileySet()
{
	global $sourcedir, $boarddir, $modSettings;

	isAllowedTo('manage_smileys');
	checkSession('request');

	require_once($sourcedir . '/Subs-Package.php');

	$name = strtok(basename(isset($_FILES['set_gz']) ? $_FILES['set_gz']['name'] : $_REQUEST['set_gz']), '.');
	$name = preg_replace(array('/\s/', '/\.[\.]+/', '/[^\w_\.\-]/'), array('_', '.', ''), $name);

	// !!! Decide: overwrite or not?
	if (isset($_FILES['set_gz']) && is_uploaded_file($_FILES['set_gz']['tmp_name']) && (@ini_get('open_basedir') != '' || file_exists($_FILES['set_gz']['tmp_name'])))
		$extracted = read_tgz_file($_FILES['set_gz']['tmp_name'], $boarddir . '/Smileys/' . $name);
	elseif (isset($_REQUEST['set_gz']))
	{
		checkSession('request');

		// Check that the theme is from simplemachines.org, for now... maybe add mirroring later.
		if (preg_match('~^http://[\w_\-]+\.simplemachines\.org/~', $_REQUEST['set_gz']) == 0 || strpos($_REQUEST['set_gz'], 'dlattach') !== false)
			fatal_lang_error('not_on_simplemachines');

		$extracted = read_tgz_file($_REQUEST['set_gz'], $boarddir . '/Smileys/' . $name);
	}
	else
		redirectexit('action=admin;area=smileys');

	updateSettings(array(
		'smiley_sets_known' => $smfFunc['db_escape_string']($modSettings['smiley_sets_known'] . ',' . $name),
		'smiley_sets_names' => $smfFunc['db_escape_string']($modSettings['smiley_sets_names'] . "\n" . strtok(basename(isset($_FILES['set_gz']) ? $_FILES['set_gz']['name'] : $_REQUEST['set_gz']), '.'))
	));

	cache_put_data('parsing_smileys', null, 480);
	cache_put_data('posting_smileys', null, 480);

	// !!! Add some confirmation?
	redirectexit('action=admin;area=smileys');
}

// A function to import new smileys from an existing directory into the database.
function ImportSmileys($smileyPath)
{
	global $db_prefix, $modSettings, $smfFunc;

	if (empty($modSettings['smileys_dir']) || !is_dir($modSettings['smileys_dir'] . '/' . $smileyPath))
		fatal_lang_error('smiley_set_unable_to_import');

	$smileys = array();
	$dir = dir($modSettings['smileys_dir'] . '/' . $smileyPath);
	while ($entry = $dir->read())
	{
		if (in_array(strrchr($entry, '.'), array('.jpg', '.gif', '.jpeg', '.png')))
			$smileys[strtolower($entry)] = $smfFunc['db_escape_string']($entry);
	}
	$dir->close();

	// Exclude the smileys that are already in the database.
	$request = $smfFunc['db_query']('', "
		SELECT filename
		FROM {$db_prefix}smileys
		WHERE filename IN ('" . implode("', '", $smileys) . "')", __FILE__, __LINE__);
	while ($row = $smfFunc['db_fetch_assoc']($request))
		if (isset($smileys[strtolower($row['filename'])]))
			unset($smileys[strtolower($row['filename'])]);
	$smfFunc['db_free_result']($request);

	$request = $smfFunc['db_query']('', "
		SELECT MAX(smiley_order)
		FROM {$db_prefix}smileys
		WHERE hidden = 0
			AND smiley_row = 0", __FILE__, __LINE__);
	list ($smiley_order) = $smfFunc['db_fetch_row']($request);
	$smfFunc['db_free_result']($request);

	$new_smileys = array();
	foreach ($smileys as $smiley)
		if (strlen($smiley) <= 48)
			$new_smileys[] = "(SUBSTRING(':" . strtok($smiley, '.') . ":', 1, 30), '$smiley', SUBSTRING('" . strtok($smiley, '.') . "', 1, 80), 0, " . ++$smiley_order . ')';

	if (!empty($new_smileys))
	{
		$smfFunc['db_query']('', "
			INSERT INTO {$db_prefix}smileys
				(code, filename, description, smiley_row, smiley_order)
			VALUES" . implode(',
				', $new_smileys), __FILE__, __LINE__);

		// Make sure the smiley codes are still in the right order.
		$smfFunc['db_query']('alter_table_smileys', "
			ALTER TABLE {$db_prefix}smileys
			ORDER BY LENGTH(code) DESC", __FILE__, __LINE__);

		cache_put_data('parsing_smileys', null, 480);
		cache_put_data('posting_smileys', null, 480);
	}
}

function EditMessageIcons()
{
	global $user_info, $modSettings, $context, $settings, $db_prefix, $txt, $boarddir, $smfFunc;

	$context['admin_tabs']['tabs']['editicons']['is_selected'] = true;

	$context['icons'] = array();
	$request = $smfFunc['db_query']('', "
		SELECT m.id_icon, m.title, m.filename, m.icon_order, m.id_board, b.name AS board_name
		FROM {$db_prefix}message_icons AS m
			LEFT JOIN {$db_prefix}boards AS b ON (b.id_board = m.id_board)
		WHERE $user_info[query_see_board]", __FILE__, __LINE__);
	$last_icon = 0;
	$trueOrder = 0;
	while ($row = $smfFunc['db_fetch_assoc']($request))
	{
		$context['icons'][$row['id_icon']] = array(
			'id' => $row['id_icon'],
			'title' => $row['title'],
			'filename' => $row['filename'],
			'image_url' => $settings[file_exists($settings['theme_dir'] . '/images/post/' . $row['filename'] . '.gif') ? 'actual_images_url' : 'default_images_url'] . '/post/' . $row['filename'] . '.gif',
			'board_id' => $row['id_board'],
			'board' => empty($row['board_name']) ? $txt['icons_edit_icons_all_boards'] : $row['board_name'],
			'order' => $row['icon_order'],
			'true_order' => $trueOrder++,
			'after' => $last_icon,
		);
		$last_icon = $row['id_icon'];
	}
	$smfFunc['db_free_result']($request);

	// Submitting a form?
	if (isset($_POST['sc']))
	{
		checkSession();

		// Deleting icons?
		if (isset($_POST['delete']) && !empty($_POST['checked_icons']))
		{
			$deleteIcons = array();
			foreach ($_POST['checked_icons'] as $icon)
				$deleteIcons[] = (int) $icon;

			// Do the actual delete!
			$smfFunc['db_query']('', "
				DELETE FROM {$db_prefix}message_icons
				WHERE id_icon IN (" . implode(', ', $deleteIcons) . ")", __FILE__, __LINE__);
		}
		// Editing/Adding an icon?
		elseif ($context['sub_action'] == 'editicon' && isset($_GET['icon']))
		{
			$_GET['icon'] = (int) $_GET['icon'];

			// Do some preperation with the data... like check the icon exists *somewhere*
			if (strpos($_POST['icon_filename'], '.gif') !== false)
				$_POST['icon_filename'] = substr($_POST['icon_filename'], 0, -4);
			if (!file_exists($settings['default_theme_dir'] . '/images/post/' . $_POST['icon_filename'] . '.gif'))
				fatal_lang_error('icon_not_found');
			// There is a 16 character limit on message icons...
			elseif (strlen($_POST['icon_filename']) > 16)
				fatal_lang_error('icon_name_too_long');
			elseif ($_POST['icon_location'] == $_GET['icon'] && !empty($_GET['icon']))
				fatal_lang_error('icon_after_itself');

			// First do the sorting... if this is an edit reduce the order of everything after it by one ;)
			if ($_GET['icon'] != 0)
			{
				$oldOrder = $context['icons'][$_GET['icon']]['true_order'];
				foreach ($context['icons'] as $id => $data)
					if ($data['true_order'] > $oldOrder)
						$context['icons'][$id]['true_order']--;
			}

			// Get the new order.
			$newOrder = $_POST['icon_location'] == 0 ? 0 : $context['icons'][$_POST['icon_location']]['true_order'] + 1;
			// Do the same, but with the one that used to be after this icon, done to avoid conflict.
			foreach ($context['icons'] as $id => $data)
				if ($data['true_order'] >= $newOrder)
					$context['icons'][$id]['true_order']++;

			// Finally set the current icon's position!
			$context['icons'][$_GET['icon']]['true_order'] = $newOrder;

			// Simply replace the existing data for the other bits.
			$context['icons'][$_GET['icon']]['title'] = $_POST['icon_description'];
			$context['icons'][$_GET['icon']]['filename'] = $_POST['icon_filename'];
			$context['icons'][$_GET['icon']]['board_id'] = (int) $_POST['icon_board'];

			// Do a huge replace ;)
			$iconInsert = array();
			foreach ($context['icons'] as $id => $icon)
			{
				if ($id != 0)
					$icon['title'] = $smfFunc['db_escape_string']($icon['title']);

				$iconInsert[] = array($id, $icon['board_id'], "SUBSTRING('$icon[title]', 1, 80)", "SUBSTRING('$icon[filename]', 1, 80)", $icon['true_order']);
			}

			$smfFunc['db_insert']('replace',
				"{$db_prefix}message_icons",
				array('id_icon', 'id_board', 'title', 'filename', 'icon_order'),
				$iconInsert,
				array('id_icon'), __FILE__, __LINE__
			);
		}

		// Sort by order, so it is quicker :)
		$smfFunc['db_query']('alter_table_icons', "
			ALTER TABLE {$db_prefix}message_icons
			ORDER BY icon_order", __FILE__, __LINE__);

		// Unless we're adding a new thing, we'll escape
		if (!isset($_POST['add']))
			redirectexit('action=admin;area=smileys;sa=editicons');
	}

	// If we're adding/editing an icon we'll need a list of boards
	if ($context['sub_action'] == 'editicon' || isset($_POST['add']))
	{
		$context['new_icon'] = !isset($_GET['icon']) || !isset($context['icons'][$_GET['icon']]);
		// Force the sub_template just incase.
		$context['sub_template'] = 'editicon';

		if (!$context['new_icon'])
			$context['icon'] = &$context['icons'][$_GET['icon']];

		$request = $smfFunc['db_query']('', "
			SELECT id_board, name
			FROM {$db_prefix}boards
			WHERE $user_info[query_see_board]", __FILE__, __LINE__);
		$context['boards'] = array();
		while ($row = $smfFunc['db_fetch_assoc']($request))
			$context['boards'][$row['id_board']] = $row['name'];
		$smfFunc['db_free_result']($request);
	}
}

?>