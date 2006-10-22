<?php
/******************************************************************************
* ModSettings.php                                                             *
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

/*	This file is here to make it easier for installed mods to have settings
	and options.  It uses the following functions:

	void ModifyFeatureSettings()
		// !!!

	void ModifyFeatureSettings2()
		// !!!

	void ModifyBasicSettings()
		// !!!

	void ModifyLayoutSettings()
		// !!!

	void ModifyKarmaSettings()
		// !!!

	void ModifySignatureSettings()
		// !!!

	void pauseSignatureApplySettings()
		// !!!

	void ShowCustomProfiles()
		// !!!

	void EditCustomProfiles()
		// !!!

	Adding new settings to the $modSettings array:
	---------------------------------------------------------------------------
// !!!
*/

/*	Adding options to one of the setting screens isn't hard.  The basic format for a checkbox is:
		array('check', 'nameInModSettingsAndSQL'),

	   And for a text box:
		array('text', 'nameInModSettingsAndSQL')
	   (NOTE: You have to add an entry for this at the bottom!)

	   In these cases, it will look for $txt['nameInModSettingsAndSQL'] as the description,
	   and $helptxt['nameInModSettingsAndSQL'] as the help popup description.

	Here's a quick explanation of how to add a new item:

	 * A text input box.  For textual values.
	ie.	array('text', 'nameInModSettingsAndSQL', 'OptionalInputBoxWidth',
			&$txt['OptionalDescriptionOfTheOption'], 'OptionalReferenceToHelpAdmin'),

	 * A text input box.  For numerical values.
	ie.	array('int', 'nameInModSettingsAndSQL', 'OptionalInputBoxWidth',
			&$txt['OptionalDescriptionOfTheOption'], 'OptionalReferenceToHelpAdmin'),

	 * A text input box.  For floating point values.
	ie.	array('float', 'nameInModSettingsAndSQL', 'OptionalInputBoxWidth',
			&$txt['OptionalDescriptionOfTheOption'], 'OptionalReferenceToHelpAdmin'),

	 * A check box.  Either one or zero. (boolean)
	ie.	array('check', 'nameInModSettingsAndSQL', null, &$txt['descriptionOfTheOption'],
			'OptionalReferenceToHelpAdmin'),

	 * A selection box.  Used for the selection of something from a list.
	ie.	array('select', 'nameInModSettingsAndSQL', array('valueForSQL' => &$txt['displayedValue']),
			&$txt['descriptionOfTheOption'], 'OptionalReferenceToHelpAdmin'),
	Note that just saying array('first', 'second') will put 0 in the SQL for 'first'.

	 * A password input box. Used for passwords, no less!
	ie.	array('password', 'nameInModSettingsAndSQL', 'OptionalInputBoxWidth',
			&$txt['descriptionOfTheOption'], 'OptionalReferenceToHelpAdmin'),

	For each option:
		type (see above), variable name, size/possible values, description, helptext.
	OR	make type 'rule' for an empty string for a horizontal rule.
	OR	make type 'heading' with a string for a titled section. */

// This function passes control through to the relevant tab.
function ModifyFeatureSettings()
{
	global $context, $txt, $scripturl, $modSettings, $sourcedir, $settings;

	//!!! Temp
	if (isset($_GET['save']))
		return ModifyFeatureSettings2();

	// You need to be an admin to edit settings!
	isAllowedTo('admin_forum');

	loadLanguage('Help');
	loadLanguage('ModSettings');

	// Will need the utility functions from here.
	require_once($sourcedir . '/ManageServer.php');

	$context['page_title'] = $txt['modSettings_title'];
	$context['sub_template'] = 'show_settings';

	$subActions = array(
		'basic' => 'ModifyBasicSettings',
		'layout' => 'ModifyLayoutSettings',
		'karma' => 'ModifyKarmaSettings',
		'sig' => 'ModifySignatureSettings',
		'profile' => 'ShowCustomProfiles',
		'profileedit' => 'EditCustomProfiles',
	);

	// By default do the basic settings.
	$_REQUEST['sa'] = isset($_REQUEST['sa']) && isset($subActions[$_REQUEST['sa']]) ? $_REQUEST['sa'] : 'basic';
	$context['sub_action'] = $_REQUEST['sa'];

	// Load up all the tabs...
	$context['admin_tabs'] = array(
		'title' => &$txt['modSettings_title'],
		'help' => 'modsettings',
		'description' => sprintf($txt['smf3'], $settings['theme_id'], $context['session_id']),
		'tabs' => array(
			'basic' => array(
				'title' => $txt['mods_cat_features'],
				'href' => $scripturl . '?action=admin;area=featuresettings;sa=basic;sesc=' . $context['session_id'],
			),
			'layout' => array(
				'title' => $txt['mods_cat_layout'],
				'href' => $scripturl . '?action=admin;area=featuresettings;sa=layout;sesc=' . $context['session_id'],
			),
			'karma' => array(
				'title' => $txt['smf293'],
				'href' => $scripturl . '?action=admin;area=featuresettings;sa=karma;sesc=' . $context['session_id'],
			),
			'sig' => array(
				'title' => $txt['signature_settings'],
				'description' => $txt['signature_settings_desc'],
				'href' => $scripturl . '?action=admin;area=featuresettings;sa=sig;sesc=' . $context['session_id'],
			),
			'profile' => array(
				'title' => $txt['custom_profile_shorttitle'],
				'description' => $txt['custom_profile_desc'],
				'href' => $scripturl . '?action=admin;area=featuresettings;sa=profile;sesc=' . $context['session_id'],
				'is_last' => true,
			),
		),
	);

	// Select the right tab based on the sub action.
	if (isset($context['admin_tabs']['tabs'][$context['sub_action']]))
		$context['admin_tabs']['tabs'][$context['sub_action']]['is_selected'] = true;

	// Call the right function for this sub-acton.
	$subActions[$_REQUEST['sa']]();
}

// This function basically just redirects to the right save function.
function ModifyFeatureSettings2()
{
	global $context, $txt, $scripturl, $modSettings, $sourcedir;

	isAllowedTo('admin_forum');
	loadLanguage('ModSettings');

	// Quick session check...
	checkSession();

	require_once($sourcedir . '/ManageServer.php');

	$subActions = array(
		'basic' => 'ModifyBasicSettings',
		'layout' => 'ModifyLayoutSettings',
		'karma' => 'ModifyKarmaSettings',
		'sig' => 'ModifySignatureSettings',
	);

	// Default to core (I assume)
	$_REQUEST['sa'] = isset($_REQUEST['sa']) && isset($subActions[$_REQUEST['sa']]) ? $_REQUEST['sa'] : 'basic';

	// Actually call the saving function.
	$subActions[$_REQUEST['sa']]();
}

function ModifyBasicSettings()
{
	global $txt, $scripturl, $context, $settings, $sc, $modSettings;

	$config_vars = array(
			// Big Options... polls, sticky, bbc....
			array('select', 'pollMode', array(&$txt['smf34'], &$txt['smf32'], &$txt['smf33'])),
		'',
			// Basic stuff, user languages, titles, flash, permissions...
			array('check', 'allow_guestAccess'),
			array('check', 'userLanguage'),
			array('check', 'allow_editDisplayName'),
			array('check', 'allow_hideOnline'),
			array('check', 'allow_hideEmail'),
			array('check', 'guest_hideContacts'),
			array('check', 'titlesEnable'),
			array('check', 'enable_buddylist'),
			array('text', 'default_personalText'),
		'',
			// Stats, compression, cookies.... server type stuff.
			array('text', 'time_format'),
			array('select', 'number_format', array('1234.00' => '1234.00', '1,234.00' => '1,234.00', '1.234,00' => '1.234,00', '1 234,00' => '1 234,00', '1234,00' => '1234,00')),
			array('float', 'time_offset'),
			array('int', 'failed_login_threshold'),
			array('int', 'lastActive'),
			array('check', 'trackStats'),
			array('check', 'hitStats'),
			array('check', 'enableErrorLogging'),
			array('check', 'securityDisable'),
		'',
			// Reactive on email, and approve on delete
			array('check', 'send_validation_onChange'),
			array('check', 'approveAccountDeletion'),
		'',
			// Option-ish things... miscellaneous sorta.
			array('check', 'allow_disableAnnounce'),
			array('check', 'disallow_sendBody'),
			array('check', 'modlog_enabled'),
			array('check', 'queryless_urls'),
		'',
			// Width/Height image reduction.
			array('int', 'max_image_width'),
			array('int', 'max_image_height'),
		'',
			// Reporting of personal messages?
			array('check', 'enableReportPM'),
	);

	// Saving?
	if (isset($_GET['save']))
	{
		// Fix PM settings.
		$_POST['pm_spam_settings'] = (int) $_POST['max_pm_recipients'] . ',' . (int) $_POST['pm_posts_verification'] . ',' . (int) $_POST['pm_posts_per_hour'];
		$save_vars = $config_vars;
		$save_vars[] = array('text', 'pm_spam_settings');

		saveDBSettings($save_vars);

		writeLog();
		redirectexit('action=admin;area=featuresettings;sa=basic');
	}

	// Hack for PM spam settings.
	list ($modSettings['max_pm_recipients'], $modSettings['pm_posts_verification'], $modSettings['pm_posts_per_hour']) = explode(',', $modSettings['pm_spam_settings']);
	$config_vars[] = array('int', 'max_pm_recipients');
	$config_vars[] = array('int', 'pm_posts_verification');
	$config_vars[] = array('int', 'pm_posts_per_hour');

	$context['post_url'] = $scripturl . '?action=admin;area=featuresettings;save;save;sa=basic';
	$context['settings_title'] = $txt['mods_cat_features'];

	prepareDBSettingContext($config_vars);
}

function ModifyLayoutSettings()
{
	global $txt, $scripturl, $context, $settings, $sc;

	$config_vars = array(
			// Compact pages?
			array('check', 'compactTopicPagesEnable'),
			array('int', 'compactTopicPagesContiguous', null, $txt['smf235'] . '<div class="smalltext">' . str_replace(' ', '&nbsp;', '"3" ' . $txt['smf236'] . ': <b>1 ... 4 [5] 6 ... 9</b>') . '<br />' . str_replace(' ', '&nbsp;', '"5" ' . $txt['smf236'] . ': <b>1 ... 3 4 [5] 6 7 ... 9</b>') . '</div>'),
		'',
			// Stuff that just is everywhere - today, search, online, etc.
			array('select', 'todayMod', array(&$txt['smf290'], &$txt['smf291'], &$txt['smf292'])),
			array('check', 'topbottomEnable'),
			array('check', 'onlineEnable'),
			array('check', 'enableVBStyleLogin'),
		'',
			// Pagination stuff.
			array('int', 'defaultMaxMembers'),
		'',
			// This is like debugging sorta.
			array('check', 'timeLoadPageEnable'),
			array('check', 'disableHostnameLookup'),
		'',
			// Who's online.
			array('check', 'who_enabled'),
	);

	// Saving?
	if (isset($_GET['save']))
	{
		saveDBSettings($config_vars);
		writeLog();

		redirectexit('action=admin;area=featuresettings;sa=layout');
	}

	$context['post_url'] = $scripturl . '?action=admin;area=featuresettings;save;save;sa=layout';
	$context['settings_title'] = $txt['mods_cat_layout'];

	prepareDBSettingContext($config_vars);
}

function ModifyKarmaSettings()
{
	global $txt, $scripturl, $context, $settings, $sc;

	$config_vars = array(
			// Karma - On or off?
			array('select', 'karmaMode', explode('|', $txt['smf64'])),
		'',
			// Who can do it.... and who is restricted by time limits?
			array('int', 'karmaMinPosts'),
			array('float', 'karmaWaitTime'),
			array('check', 'karmaTimeRestrictAdmins'),
		'',
			// What does it look like?  [smite]?
			array('text', 'karmaLabel'),
			array('text', 'karmaApplaudLabel'),
			array('text', 'karmaSmiteLabel'),
	);

	// Saving?
	if (isset($_GET['save']))
	{
		saveDBSettings($config_vars);
		redirectexit('action=admin;area=featuresettings;sa=karma');
	}

	$context['post_url'] = $scripturl . '?action=admin;area=featuresettings;save;save;sa=karma';
	$context['settings_title'] = $txt['smf293'];

	prepareDBSettingContext($config_vars);
}

// You'll never guess what this function does...
function ModifySignatureSettings()
{
	global $context, $txt, $modSettings, $db_prefix, $sig_start;

	// Applying to ALL signatures?!!
	if (isset($_GET['apply']))
	{
		$sig_start = time();
		// This is horrid - but I suppose some people will want the option to do it.
		$_GET['step'] = isset($_GET['step']) ? (int) $_GET['step'] : 0;
		list ($sig_limits, $sig_bbc) = explode(':', $modSettings['signature_settings']);
		$sig_limits = explode(',', $sig_limits);
		$disabledTags = !empty($sig_bbc) ? explode(',', $sig_bbc) : array();
		$done = false;

		$request = db_query("
			SELECT MAX(ID_MEMBER)
			FROM {$db_prefix}members", __FILE__, __LINE__);
		list ($context['max_member']) = mysql_fetch_row($request);
		mysql_free_result($request);

		while (!$done)
		{
			$changes = array();

			$request = db_query("
				SELECT ID_MEMBER, signature
				FROM {$db_prefix}members
				WHERE ID_MEMBER BETWEEN $_GET[step] AND $_GET[step] + 49", __FILE__, __LINE__);
			while ($row = mysql_fetch_assoc($request))
			{
				// Apply all the rules we can realistically do.
				$sig = strtr($row['signature'], array('<br />' => "\n"));

				// Max characters...
				if (!empty($sig_limits[1]))
					$sig = substr($sig, 0, $sig_limits[1]);
				// Max lines...
				if (!empty($sig_limits[2]))
				{
					$count = 0;
					for ($i = 0; $i < strlen($sig); $i++)
					{
						if ($sig{$i} == "\n")
						{
							$count++;
							if ($count > $sig_limits[2])
								$sig = substr($sig, 0, $i) . strtr(substr($sig, $i), array("\n" => ' '));
						}
					}
				}
				// Max font size...
				if (!empty($sig_limits[7]) && preg_match_all('~\[size=(\d+)~', $sig, $matches) !== false && isset($matches[1]))
				{
					foreach ($matches[1] as $key => $size)
						if ($size > $sig_limits[7])
						{
							$sig = str_replace($matches[0][$key], '[size=' . $sig_limits[7], $sig);
						}
				}

				// Stupid images - this is stupidly, stupidly challenging.
				if ((!empty($sig_limits[3]) || !empty($sig_limits[5]) || !empty($sig_limits[6])))
				{
					$replaces = array();
					$img_count = 0;
					// Try to find all the images!
					if (preg_match_all('~\[img(\s+width=([\d]+))?(\s+height=([\d]+))?(\s+width=([\d]+))?\s*\](?:<br />)*([^<">]+?)(?:<br />)*\[/img\]~', $sig, $matches) !== false)
					{
						foreach ($matches[0] as $key => $image)
						{
							$width = -1; $height = -1;
							$img_count++;
							// Too many images?
							if (!empty($sig_limits[3]) && $img_count > $sig_limits[3])
							{
								$replaces[$image] = '';
								break;
							}

							// Does it have predefined restraints? Width first.
							if ($matches[6][$key])
								$matches[2][$key] = $matches[6][$key];
							if ($matches[2][$key] && $sig_limits[5] && $matches[2][$key] > $sig_limits[5])
							{
								$width = $sig_limits[5];
								$matches[4][$key] = $matches[4][$key] * ($width / $matches[2][$key]);
							}
							elseif ($matches[2][$key])
								$width = $matches[2][$key];
							// ... and height.
							if ($matches[4][$key] && $sig_limits[6] && $matches[4][$key] > $sig_limits[6])
							{
								$height = $sig_limits[6];
								if ($width != -1)
									$width = $width * ($height / $matches[4][$key]);
							}
							elseif ($matches[4][$key])
								$height = $matches[4][$key];
		
							// If the dimensions are still not fixed - we need to check the actual image.
							if (($width == -1 && $sig_limits[5]) || ($height == -1 && $sig_limits[6]))
							{
								$sizes = url_image_size($matches[7][$key]);
								if (is_array($sizes))
								{
									// Too wide?
									if ($sizes[0] > $sig_limits[5] && $sig_limits[5])
									{
										$width = $sig_limits[5];
										$sizes[1] = $sizes[1] * ($width / $sizes[0]);
									}
									// Too high?
									if ($sizes[1] > $sig_limits[6] && $sig_limits[6])
									{
										$height = $sig_limits[6];
										if ($width == -1)
											$width = $sizes[0];
										$width = $width * ($height / $sizes[1]);
									}
									elseif ($width != -1)
										$height = $sizes[1];
								}
							}

							// Did we come up with some changes? If so remake the string.
							if ($width != -1 || $height != -1)
								$replaces[$image] = '[img' . ($width != -1 ? ' width=' . round($width) : '') . ($height != -1 ? ' height=' . round($height) : '') . ']' . $matches[7][$key] . '[/img]';
						}
						if (!empty($replaces))
							$sig = str_replace(array_keys($replaces), array_values($replaces), $sig);
					}
				}
				// Try to fix disabled tags.
				if (!empty($disabledTags))
				{
					$sig = preg_replace('~\[(' . implode('|', $disabledTags) . ').+?\]~', '', $sig);
					$sig = preg_replace('~\[/(' . implode('|', $disabledTags) . ')\]~', '', $sig);
				}

				$sig = strtr($sig, array("\n" => '<br />'));
				if ($sig != $row['signature'])
					$changes[$row['ID_MEMBER']] = addslashes($sig);
			}
			if (mysql_num_rows($request) == 0)
				$done = true;
			mysql_free_result($request);

			// Do we need to delete what we have?
			if (!empty($changes))
			{
				foreach ($changes as $id => $sig)
					db_query("
						UPDATE {$db_prefix}members
						SET signature = '$sig'
						WHERE ID_MEMBER = $id
						LIMIT 1", __FILE__, __LINE__);
			}

			$_GET['step'] += 50;
			if (!$done)
				pauseSignatureApplySettings();
		}
	}

	// Setup the template.
	$context['sub_template'] = 'edit_signature_settings';
	$context['page_title'] = $txt['signature_settings'];

	// Load all the signature settings.
	list ($sig_limits, $sig_bbc) = explode(':', $modSettings['signature_settings']);
	$sig_limits = explode(',', $sig_limits);
	$disabledTags = !empty($sig_bbc) ? explode(',', $sig_bbc) : array();

	$context['signature_settings'] = array(
		'enabled' => isset($sig_limits[0]) ? $sig_limits[0] : 0,
		'max_length' => isset($sig_limits[1]) ? $sig_limits[1] : 0,
		'max_lines' => isset($sig_limits[2]) ? $sig_limits[2] : 0,
		'max_images' => isset($sig_limits[3]) ? $sig_limits[3] : 0,
		'max_smileys' => isset($sig_limits[4]) ? $sig_limits[4] : 0,
		'max_image_width' => isset($sig_limits[5]) ? $sig_limits[5] : 0,
		'max_image_height' => isset($sig_limits[6]) ? $sig_limits[6] : 0,
		'max_font_size' => isset($sig_limits[7]) ? $sig_limits[7] : 0,
	);

	// Ask parse_bbc() for its bbc code list.
	$temp = parse_bbc(false);
	$bbcTags = array();
	foreach ($temp as $tag)
		$bbcTags[] = $tag['tag'];

	$bbcTags = array_unique($bbcTags);
	$totalTags = count($bbcTags);

	// The number of columns we want to show the BBC tags in.
	$numColumns = 3;

	// In case we're saving.
	if (isset($_POST['save_settings']))
	{
		checkSession();

		if ( !isset($_POST['enabledTags']) )
			$_POST['enabledTags'] = array();
		elseif ( !is_array($_POST['enabledTags']) )
			$_POST['enabledTags'] = array($_POST['enabledTags']);

		$sig_limits = array();
		foreach ($context['signature_settings'] as $key => $value)
			$sig_limits[] = !empty($_POST[$key]) ? max(1, (int) $_POST[$key]) : 0;

		$sig_settings = implode(',', $sig_limits) . ':' . implode(',', array_diff($bbcTags, $_POST['enabledTags']));

		// Update the actual setting.
		updateSettings(array(
			'signature_settings' => $sig_settings,
		));

		redirectexit('action=admin;area=featuresettings;sa=sig');
	}

	$context['bbc_columns'] = array();
	$tagsPerColumn = ceil($totalTags / $numColumns);

	$col = 0;
	$i = 0;
	foreach ($bbcTags as $tag)
	{
		if ($i % $tagsPerColumn == 0 && $i != 0)
			$col++;

		$context['bbc_columns'][$col][] = array(
			'tag' => $tag,
			'is_enabled' => !in_array($tag, $disabledTags),
			// !!! 'tag_' . ?
			'show_help' => isset($helptxt[$tag]),
		);

		$i++;
	}

	$context['bbc_all_selected'] = empty($disabledTags);
}

// Just pause the signature applying thing.
function pauseSignatureApplySettings()
{
	global $context, $txt, $sig_start;

	// Try get more time...
	@set_time_limit(600);
	if (function_exists('apache_reset_timeout'))
		apache_reset_timeout();

	// Have we exhausted all the time we allowed?
	if (time() - array_sum(explode(' ', $sig_start)) < 3)
		return;

	$context['continue_get_data'] = '?action=admin;area=featuresettings;sa=sig;apply;step=' . $_GET['step'];
	$context['page_title'] = $txt['not_done_title'];
	$context['continue_post_data'] = '';
	$context['continue_countdown'] = '2';
	$context['sub_template'] = 'not_done';

	// Specific stuff to not break this template!
	$context['admin_tabs']['tabs']['sig']['is_selected'] = true;

	// Get the right percent.
	$context['continue_percent'] = round(($_GET['step'] / $context['max_member']) * 100);

	// Never more than 100%!
	$context['continue_percent'] = min($context['continue_percent'], 100);

	obExit();
}

// Show all the custom profile fields available to the user.
function ShowCustomProfiles()
{
	global $txt, $scripturl, $context, $settings, $sc, $db_prefix;

	$context['page_title'] = $txt['custom_profile_title'];
	$context['sub_template'] = 'show_custom_profile';

	// Load all the fields.
	$request = db_query("
		SELECT ID_FIELD, colName, fieldName, fieldDesc, fieldType, active
		FROM {$db_prefix}custom_fields", __FILE__, __LINE__);
	$context['profile_fields'] = array();
	while ($row = mysql_fetch_assoc($request))
	{
		$context['profile_fields'][] = array(
			'id' => $row['ID_FIELD'],
			'col' => $row['colName'],
			'name' => $row['fieldName'],
			'desc' => $row['fieldDesc'],
			'type' => $row['fieldType'],
			'active' => $row['active'],
		);
	}
	mysql_free_result($request);
}

// Edit some profile fields?
function EditCustomProfiles()
{
	global $txt, $scripturl, $context, $settings, $sc, $db_prefix, $smfFunc;

	// Sort out the context!
	$context['fid'] = isset($_GET['fid']) ? (int) $_GET['fid'] : 0;
	$context['admin_tabs']['tabs']['profile']['is_selected'] = true;
	$context['page_title'] = $context['fid'] ? $txt['custom_edit_title'] : $txt['custom_add_title'];
	$context['sub_template'] = 'edit_profile_field';

	// Load the profile language for section names.
	loadLanguage('Profile');

	if ($context['fid'])
	{
		$request = db_query("
			SELECT ID_FIELD, colName, fieldName, fieldDesc, fieldType, fieldLength, fieldOptions,
				showReg, showDisplay, showProfile, private, active, defaultValue, bbc, mask
			FROM {$db_prefix}custom_fields
			WHERE ID_FIELD = $context[fid]", __FILE__, __LINE__);
		$context['field'] = array();
		while ($row = mysql_fetch_assoc($request))
		{
			if ($row['fieldType'] == 'textarea')
				@list ($rows, $cols) = @explode(',', $row['defaultValue']);
			else
			{
				$rows = 3;
				$cols = 30;
			}

			$context['field'] = array(
				'name' => $row['fieldName'],
				'desc' => $row['fieldDesc'],
				'colname' => $row['colName'],
				'profile_area' => $row['showProfile'],
				'reg' => $row['showReg'],
				'display' => $row['showDisplay'],
				'type' => $row['fieldType'],
				'max_length' => $row['fieldLength'],
				'rows' => $rows,
				'cols' => $cols,
				'bbc' => $row['bbc'] ? true : false,
				'default_check' => $row['fieldType'] == 'check' && $row['defaultValue'] ? true : false,
				'default_select' => $row['fieldType'] == 'select' ? $row['defaultValue'] : '',
				'options' => strlen($row['fieldOptions']) > 1 ? explode(',', $row['fieldOptions']) : array('', '', ''),
				'active' => $row['active'],
				'private' => $row['private'],
				'mask' => $row['mask'],
				'regex' => substr($row['mask'], 0, 5) == 'regex' ? substr($row['mask'], 5) : '',
			);
		}
		mysql_free_result($request);
	}

	// Setup the default values as needed.
	if (empty($context['field']))
		$context['field'] = array(
			'name' => '',
			'desc' => '',
			'profile_area' => 'forumProfile',
			'reg' => false,
			'display' => false,
			'type' => 'text',
			'max_length' => 255,
			'rows' => 4,
			'cols' => 30,
			'bbc' => false,
			'default_check' => false,
			'default_select' => '',
			'options' => array('', '', ''),
			'active' => true,
			'private' => false,
			'mask' => 'none',
			'regex' => '',
		);

	// Are we saving?
	if (isset($_POST['save']))
	{
		// Everyone needs a name - even the (bracket) unknown...
		if (trim($_POST['field_name']) == '')
			fatal_lang_error('custom_option_need_name');
		$_POST['field_name'] = $smfFunc['htmlspecialchars']($_POST['field_name']);
		$_POST['field_desc'] = $smfFunc['htmlspecialchars']($_POST['field_desc']);

		// Checkboxes...
		$showReg = isset($_POST['reg']) ? 1 : 0;
		$showDisplay = isset($_POST['display']) ? 1 : 0;
		$bbc = isset($_POST['bbc']) ? 1 : 0;
		$showProfile = $_POST['profile_area'];
		$active = isset($_POST['active']) ? 1 : 0;
		$private = isset($_POST['private']) ? 1 : 0;

		// Some masking stuff...
		$mask = isset($_POST['mask']) ? $_POST['mask'] : '';
		if ($mask == 'regex' && isset($_POST['regex']))
			$mask .= $_POST['regex'];

		$fieldLength = isset($_POST['max_length']) ? (int) $_POST['max_length'] : 255;

		// Select options?
		$fieldOptions = '';
		$newOptions = array();
		$default = isset($_POST['default_check']) && $_POST['field_type'] == 'check' ? 1 : '';
		if (!empty($_POST['select_option']) && $_POST['field_type'] == 'select')
		{
			foreach ($_POST['select_option'] as $k => $v)
			{
				// Clean, clean, clean...
				$v = $smfFunc['htmlspecialchars']($v);
				$v = strtr($v, array(',' => ''));

				// Nada, zip, etc...
				if (trim($v) == '')
					continue;

				// Otherwise, save it boy.
				$fieldOptions .= $v . ',';
				// This is just for working out what happened with old options...
				$newOptions[$k] = $v;

				// Is it default?
				if (isset($_POST['default_select']) && $_POST['default_select'] == $k)
					$default = $v;
			}
			$fieldOptions = substr($fieldOptions, 0, -1);
		}

		// Text area has default has dimensions
		if ($_POST['field_type'] == 'textarea')
			$default = (int) $_POST['rows'] . ',' . (int) $_POST['cols'];

		// Come up with the unique name?
		if (empty($context['fid']))
		{
			$colname = strtr(substr($_POST['field_name'], 0, 8), array(' ' => ''));
			preg_match('~([\w\d_-]+)~', $colname, $matches);
			if (!isset($matches[1]))
				fatal_lang_error('custom_option_not_unique');
			$colname = strtolower($matches[1]);

			// Check this is unique.
			$unique = false;
			while ($unique == false)
			{
				$request = db_query("
					SELECT ID_FIELD
					FROM {$db_prefix}custom_fields
					WHERE colName = '$colname'", __FILE__, __LINE__);
				if (mysql_num_rows($request) == 0)
					$unique = true;
				else
					$colname .= rand(0, 9);
				mysql_free_result($request);

				if (strlen($colname) >= 12 && !$unique)
					fatal_lang_error('custom_option_not_unique');
			}
		}
		// Work out what to do with the user data otherwise...
		else
		{
			// Anything going to check or select is pointless keeping - as is anything coming from check!
			if (($_POST['field_type'] == 'check' && $context['field']['type'] != 'check')
				|| ($_POST['field_type'] == 'select' && $context['field']['type'] != 'select')
				|| ($context['field']['type'] == 'check' && $_POST['field_type'] != 'check'))
			{
				db_query("
					DELETE FROM {$db_prefix}themes
					WHERE variable = '" . $context['field']['colname'] . "'
						AND ID_MEMBER > 0", __FILE__, __LINE__);
			}
			// Otherwise - if the select is edited may need to adjust!
			elseif ($_POST['field_type'] == 'select')
			{
				$optionChanges = array();
				$takenKeys = array();
				// Work out what's changed!
				foreach ($context['field']['options'] as $k => $option)
				{
					if (trim($option) == '')
						continue;

					// Still exists?
					if (in_array($option, $newOptions))
					{
						$takenKeys[] = $k;
						continue;
					}

					// Damn - it's gone!
					$optionChanges[$k] = strtr($option, array("'" => "\'"));
				}

				// Finally - have we renamed it - or is it really gone?
				foreach ($optionChanges as $k => $option)
				{
					// Just been renamed?
					if (!in_array($k, $takenKeys) && !empty($newOptions[$k]))
						db_query("
							UPDATE {$db_prefix}themes
							SET value = '" . $newOptions[$k] . "'
							WHERE variable = '" . $context['field']['colname'] . "'
								AND value = '$option'
								AND ID_MEMBER > 0", __FILE__, __LINE__);
				}
			}
			//!!! Maybe we should adjust based on new text length limits?
		}

		// Do the insertion/updates.
		if ($context['fid'])
		{
			db_query("
				UPDATE {$db_prefix}custom_fields
				SET fieldName = '$_POST[field_name]', fieldDesc = '$_POST[field_desc]',
					fieldType = '$_POST[field_type]', fieldLength = $fieldLength,
					fieldOptions = '$fieldOptions', showReg = $showReg, showDisplay = $showDisplay,
					showProfile = '$showProfile', private = $private, active = $active, defaultValue = '$default',
					bbc = $bbc, mask = '$mask'
				WHERE ID_FIELD = $context[fid]", __FILE__, __LINE__);

			// Just clean up any old selects - these are a pain!
			if ($_POST['field_type'] == 'select' && !empty($newOptions))
				db_query("
					DELETE FROM {$db_prefix}themes
					WHERE variable = '" . $context['field']['colname'] . "'
						AND value NOT IN ('" . implode("', '", $newOptions) . "')
						AND ID_MEMBER > 0", __FILE__, __LINE__);
		}
		else
		{
			db_query("
				INSERT INTO {$db_prefix}custom_fields
					(colName, fieldName, fieldDesc, fieldType, fieldLength, fieldOptions,
					showReg, showDisplay, showProfile, private, active, defaultValue, bbc, mask)
				VALUES
					('$colname', '$_POST[field_name]', '$_POST[field_desc]', '$_POST[field_type]',
					$fieldLength, '$fieldOptions', $showReg, $showDisplay, '$showProfile', $private,
					$active, '$default', $bbc, '$mask')", __FILE__, __LINE__);
		}
	}
	// Deleting?
	elseif (isset($_POST['delete']) && $context['field']['colname'])
	{
		// Delete the user data first.
		db_query("
			DELETE FROM {$db_prefix}themes
			WHERE variable = '" . $context['field']['colname'] . "'
				AND ID_MEMBER > 0", __FILE__, __LINE__);
		// Finally - the field itself is gone!
		db_query("
			DELETE FROM {$db_prefix}custom_fields
			WHERE ID_FIELD = $context[fid]
			LIMIT 1", __FILE__, __LINE__);
	}

	// Rebuild display cache etc.
	if (isset($_POST['delete']) || isset($_POST['save']))
	{
		$request = db_query("
			SELECT colName, fieldName
			FROM {$db_prefix}custom_fields
			WHERE showDisplay = 1
				AND active = 1
				AND private = 0", __FILE__, __LINE__);
		$fields = array();
		while ($row = mysql_fetch_assoc($request))
		{
			$fields[] = strtr($row['colName'], array('|' => '', ';' => '')) . ';' . strtr($row['fieldName'], array('|' => '', ';' => ''));
		}
		mysql_free_result($request);

		$fields = implode('|', $fields);
		updateSettings(array('displayFields' => strtr($fields, array("'" => "\'"))));
		redirectexit('action=admin;area=featuresettings;sa=profile');
	}
}

?>