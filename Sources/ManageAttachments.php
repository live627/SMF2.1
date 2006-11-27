<?php
/**********************************************************************************
* ManageAttachments.php                                                           *
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

/* /!!!

	void ManageAttachments()
		- main 'Attachments and Avatars' center function.
		- entry point for index.php?action=admin;area=manageattachments.
		- requires the manage_attachments permission.
		- load the ManageAttachments template.
		- uses the Admin language file.
		- uses the template layer 'manage_files' for showing the tab bar.
		- calls a function based on the sub-action.

	void ManageAttachmentSettings()
		- show/change attachment settings.
		- default sub action for the 'Attachments and Avatars' center.
		- uses the 'attachments' sub template.
		- called by index.php?action=admin;area=manageattachments;sa=attachements.

	void ManageAvatarSettings()
		- show/change avatar settings.
		- called by index.php?action=admin;area=manageattachments;sa=avatars.
		- uses the 'avatars' sub template.
		- show/set permissions for permissions: 'profile_server_avatar',
		  'profile_upload_avatar' and 'profile_remote_avatar'.

	void BrowseFiles()
		- show a list of attachment or avatar files.
		- called by ?action=admin;area=manageattachments;sa=browse for attachments and
		  ?action=admin;area=manageattachments;sa=browse;avatars for avatars.
		- uses the 'browse' sub template
		- allows sorting by name, date, size and member.
		- paginates results.

	void MaintainFiles()
		- show several file maintenance options.
		- called by ?action=admin;area=manageattachments;sa=maintain.
		- uses the 'maintain' sub template.
		- calculates file statistics (total file size, number of attachments,
		  number of avatars, attachment space available).

	void MoveAvatars()
		- move avatars from or to the attachment directory.
		- called from the maintenance screen by
		  ?action=admin;area=manageattachments;sa=moveAvatars.

	void RemoveAttachmentByAge()
		- remove attachments older than a given age.
		- called from the maintenance screen by
		  ?action=admin;area=manageattachments;sa=byAge.
		- optionally adds a certain text to the messages the attachments were
		  removed from.

	void RemoveAttachmentBySize()
		- remove attachments larger than a given size.
		- called from the maintenance screen by
		  ?action=admin;area=manageattachments;sa=bySize.
		- optionally adds a certain text to the messages the attachments were
		  removed from.

	void RemoveAttachment()
		- remove a selection of attachments or avatars.
		- called from the browse screen as submitted form by
		  ?action=admin;area=manageattachments;sa=remove

	void RemoveAllAttachments()
		- removes all attachments in a single click
		- called from the maintenance screen by
		  ?action=admin;area=manageattachments;sa=removeall.

	array removeAttachments(string condition, string query_type = '', bool return_affected_messages = false, bool autoThumbRemoval = true)
		- removes attachments or avatars based on a given query condition.
		- called by several remove avatar/attachment functions in this file.
		- removes attachments based that match the $condition.
		- allows query_types 'messages' and 'members', whichever is need by the
		  $condition parameter.

	void RepairAttachments()
		// !!!

	void PauseAttachmentMaintenance()
		// !!!

	void ApproveAttach()
		// !!!

	void ApproveAttachments()
		// !!!
*/

// The main attachment management function.
function ManageAttachments()
{
	global $txt, $db_prefix, $modSettings, $scripturl, $context, $options;

	// You have to be able to moderate the forum to do this.
	isAllowedTo('manage_attachments');

	// Setup the template stuff we'll probably need.
	loadTemplate('ManageAttachments');

	// If they want to delete attachment(s), delete them. (otherwise fall through..)
	$subActions = array(
		'attachments' => 'ManageAttachmentSettings',
		'avatars' => 'ManageAvatarSettings',
		'browse' => 'BrowseFiles',
		'byAge' => 'RemoveAttachmentByAge',
		'bySize' => 'RemoveAttachmentBySize',
		'maintenance' => 'MaintainFiles',
		'moveAvatars' => 'MoveAvatars',
		'repair' => 'RepairAttachments',
		'remove' => 'RemoveAttachment',
		'removeall' => 'RemoveAllAttachments'
	);

	// Pick the correct sub-action.
	if (isset($_REQUEST['sa']) && isset($subActions[$_REQUEST['sa']]))
		$context['sub_action'] = $_REQUEST['sa'];
	else
		$context['sub_action'] = 'browse';

	// This uses admin tabs - as it should!
	$context['admin_tabs'] = array(
		'title' => &$txt['smf201'],
		'help' => 'manage_files',
		'description' => $txt['smf202'],
		'tabs' => array(
			'browse' => array(
				'title' => $txt['attachment_manager_browse'],
				'href' => $scripturl . '?action=admin;area=manageattachments;sa=browse',
			),
			'attachments' => array(
				'title' => $txt['attachment_manager_settings'],
				'href' => $scripturl . '?action=admin;area=manageattachments;sa=attachments',
			),
			'avatars' => array(
				'title' => $txt['attachment_manager_avatar_settings'],
				'href' => $scripturl . '?action=admin;area=manageattachments;sa=avatars',
			),
			'maintenance' => array(
				'title' => $txt['attachment_manager_maintenance'],
				'href' => $scripturl . '?action=admin;area=manageattachments;sa=maintenance',
				'is_last' => true,
			),
		),
	);

	// Select the right tab based on the sub action.
	if (isset($context['admin_tabs']['tabs'][$context['sub_action']]))
	{
		$context['page_title'] = $context['admin_tabs']['tabs'][$context['sub_action']]['title'];
		$context['admin_tabs']['tabs'][$context['sub_action']]['is_selected'] = true;
	}

	// Finally fall through to what we are doing.
	$subActions[$context['sub_action']]();
}

function ManageAttachmentSettings()
{
	global $txt, $db_prefix, $modSettings, $scripturl, $context, $options, $sourcedir;

	// These are very likely to come in handy! (i.e. without them we're doomed!)
	require_once($sourcedir .'/ManagePermissions.php');
	require_once($sourcedir .'/ManageServer.php');

	$context['valid_upload_dir'] = is_dir($modSettings['attachmentUploadDir']) && is_writable($modSettings['attachmentUploadDir']);

	$config_vars = array(
		array('title', 'attachment_manager_settings'),
			// Are attachments enabled?
			array('select', 'attachmentEnable', array(&$txt['attachmentEnable_deactivate'], &$txt['attachmentEnable_enable_all'], &$txt['attachmentEnable_disable_new'])),
		'',
			// Extension checks etc.
			array('check', 'attachmentCheckExtensions'),
			array('text', 'attachmentExtensions', 40),
			array('check', 'attachmentEncryptFilenames'),
		'',
			// Directory and size limits.
			array('text', 'attachmentUploadDir', 40, 'invalid' => !$context['valid_upload_dir']),
			array('text', 'attachmentDirSizeLimit', 6, 'postinput' => $txt['smf211']),
			array('text', 'attachmentPostLimit', 6, 'postinput' => $txt['smf211']),
			array('text', 'attachmentSizeLimit', 6, 'postinput' => $txt['smf211']),
			array('text', 'attachmentNumPerPostLimit', 6),
		'',
			// Thumbnail settings.
			array('check', 'attachmentShowImages'),
			array('check', 'attachmentThumbnails'),
			array('text', 'attachmentThumbWidth', 6),
			array('text', 'attachmentThumbHeight', 6),
	);

	// Saving settings?
	if (isset($_GET['save']))
	{
		saveDBSettings($config_vars);
		redirectexit('action=admin;area=manageattachments;sa=attachments');
	}

	$context['post_url'] = $scripturl . '?action=admin;area=manageattachments;save;sa=attachments';
	prepareDBSettingContext($config_vars);

	$context['page_title'] = $txt['smf201'];
	$context['sub_template'] = 'show_settings';
}

function ManageAvatarSettings()
{
	global $txt, $context, $db_prefix, $modSettings, $sourcedir, $scripturl;

	// Perform a test to see if the GD module is installed.
	$testGD = get_extension_funcs('gd');

	// We need these files for the inline permission settings, and the settings template.
	require_once($sourcedir .'/ManagePermissions.php');
	require_once($sourcedir .'/ManageServer.php');

	$context['valid_avatar_dir'] = is_dir($modSettings['avatar_directory']);
	$context['valid_custom_avatar_dir'] = empty($modSettings['custom_avatar_enabled']) || (is_dir($modSettings['custom_avatar_dir']) && is_writable($modSettings['custom_avatar_dir']));

	$config_vars = array(
		// Server stored avatars!
		array('title', 'avatar_server_stored'),
			array('warning', empty($testGD) ? 'avatar_gd_warning' : ''),
			array('permissions', 'profile_server_avatar', 0, $txt['avatar_server_stored_groups']),
			array('text', 'avatar_directory', 40, 'invalid' => !$context['valid_avatar_dir']),
			array('text', 'avatar_url', 40),
		// External avatars?
		array('title', 'avatar_external'),
			array('permissions', 'profile_remote_avatar', 0, $txt['avatar_external_url_groups']),
			array('check', 'avatar_download_external', 0, 'onchange' => 'updateStatus();'),
			array('text', 'avatar_max_width_external', 6),
			array('text', 'avatar_max_height_external', 6),
			array('select', 'avatar_action_too_large',
				array(
					'option_refuse' => &$txt['option_refuse'],
					'option_html_resize' => &$txt['option_html_resize'],
					'option_js_resize' => &$txt['option_js_resize'],
					'option_download_and_resize' => &$txt['option_download_and_resize'],
				),
			),
		// Uploadable avatars?
		array('title', 'avatar_upload'),
			array('permissions', 'profile_upload_avatar', 0, $txt['avatar_upload_groups']),
			array('text', 'avatar_max_width_upload', 6),
			array('text', 'avatar_max_height_upload', 6),
			array('check', 'avatar_resize_upload'),
			array('check', 'avatar_download_png'),
			array('select', 'custom_avatar_enabled', array(&$txt['option_attachment_dir'], &$txt['option_specified_dir']), 'onchange' => 'updateStatus();'),
			array('text', 'custom_avatar_dir', 40, 'subtext' => $txt['custom_avatar_dir_desc'], 'invalid' => !$context['valid_custom_avatar_dir']),
			array('text', 'custom_avatar_url', 40),
	);

	// Saving avatar settings?
	if (isset($_GET['save']))
	{
		saveDBSettings($config_vars);
		redirectexit('action=admin;area=manageattachments;sa=avatars');
	}

	// Prepare the context.
	$context['post_url'] = $scripturl . '?action=admin;area=manageattachments;save;sa=avatars';
	prepareDBSettingContext($config_vars);

	// Add a layer for the javascript.
	$context['template_layers'][] = 'avatar_settings';
	$context['sub_template'] = 'show_settings';
}

function BrowseFiles()
{
	global $context, $db_prefix, $txt, $scripturl, $options, $modSettings, $smfFunc;

	$context['page_title'] = $txt['smf201'];
	$context['sub_template'] = 'browse';

	// Attachments or avatars?
	$context['browse_type'] = isset($_REQUEST['avatars']) ? 'avatars' : (isset($_REQUEST['thumbs']) ? 'thumbs' : 'attachments');

	// Get the number of attachments.
	$context['num_attachments'] = 0;
	$context['num_thumbs'] = 0;
	$request = $smfFunc['db_query']('', "
		SELECT attachment_type, COUNT(*) AS num_attach
		FROM {$db_prefix}attachments
		WHERE attachment_type IN (0, 3)
			AND id_member = 0
		GROUP BY attachment_type", __FILE__, __LINE__);
	while ($row = $smfFunc['db_fetch_assoc']($request))
		$context[empty($row['attachment_type']) ? 'num_attachments' : 'num_thumbs'] = $row['num_attach'];
	$smfFunc['db_free_result']($request);

	// Also get the avatar amount.
	$request = $smfFunc['db_query']('', "
		SELECT COUNT(*)
		FROM {$db_prefix}attachments
		WHERE id_member != 0", __FILE__, __LINE__);
	list ($context['num_avatars']) = $smfFunc['db_fetch_row']($request);
	$smfFunc['db_free_result']($request);

	// Allow for sorting of each column...
	$sort_methods = array(
		'name' => 'a.filename',
		'date' => $context['browse_type'] == 'avatars' ? 'mem.last_login' : 'm.id_msg',
		'size' => 'a.size',
		'member' => 'mem.real_name'
	);

	// Set up the importantant sorting variables... if they picked one...
	if (!isset($_GET['sort']) || !isset($sort_methods[$_GET['sort']]))
	{
		$_GET['sort'] = 'date';
		$descending = !empty($options['view_newest_first']);
	}
	// ... and if they didn't...
	else
		$descending = isset($_GET['desc']);

	$context['sort_by'] = $_GET['sort'];
	$_GET['sort'] = $sort_methods[$_GET['sort']];
	$context['sort_direction'] = $descending ? 'down' : 'up';

	// Get the page index ready......
	$context['page_index'] = constructPageIndex($scripturl . '?action=admin;area=manageattachments;sa=' . $context['sub_action'] . ($context['browse_type'] == 'attachments' ? '' : ';' . $context['browse_type']) . ';sort=' . $context['sort_by'] . ($context['sort_direction'] == 'down' ? ';desc' : ''), $_REQUEST['start'], $context['num_' . $context['browse_type']], $modSettings['defaultMaxMessages']);
	$context['start'] = $_REQUEST['start'];

	// Choose a query depending on what we are viewing.
	if ($context['browse_type'] == 'avatars')
		$request = $smfFunc['db_query']('', "
			SELECT
				'' AS id_msg, IFNULL(mem.real_name, '$txt[470]') AS poster_name, mem.last_login AS poster_time, 0 AS id_topic, a.id_member,
				a.id_attach, a.filename, a.attachment_type, a.size, a.width, a.height, a.downloads, '' AS subject, 0 AS id_board
			FROM {$db_prefix}attachments AS a
				LEFT JOIN {$db_prefix}members AS mem ON (mem.id_member = a.id_member)
			WHERE a.id_member != 0
			ORDER BY $_GET[sort] " . ($descending ? 'DESC' : 'ASC') . "
			LIMIT $context[start], $modSettings[defaultMaxMessages]", __FILE__, __LINE__);
	else
		$request = $smfFunc['db_query']('', "
			SELECT
				m.id_msg, IFNULL(mem.real_name, m.poster_name) AS poster_name, m.poster_time, m.id_topic, m.id_member,
				a.id_attach, a.filename, a.attachment_type, a.size, a.width, a.height, a.downloads, mf.subject, t.id_board
			FROM {$db_prefix}attachments AS a
				INNER JOIN {$db_prefix}messages AS m ON (m.id_msg = a.id_msg)
				INNER JOIN {$db_prefix}topics AS t ON (t.id_topic = m.id_topic)
				INNER JOIN {$db_prefix}messages AS mf ON (mf.id_msg = t.id_first_msg)
				LEFT JOIN {$db_prefix}members AS mem ON (mem.id_member = m.id_member)
			WHERE a.attachment_type = " . ($context['browse_type'] == 'attachments' ? '0' : '3') . "
			ORDER BY $_GET[sort] " . ($descending ? 'DESC' : 'ASC') . "
			LIMIT $context[start], $modSettings[defaultMaxMessages]", __FILE__, __LINE__);
	$context['posts'] = array();
	while ($row = $smfFunc['db_fetch_assoc']($request))
		$context['posts'][] = array(
			'id' => $row['id_msg'],
			'poster' => array(
				'id' => $row['id_member'],
				'name' => $row['poster_name'],
				'href' => empty($row['id_member']) ? '' : $scripturl . '?action=profile;u=' . $row['id_member'],
				'link' => empty($row['id_member']) ? $row['poster_name'] : '<a href="' . $scripturl . '?action=profile;u=' . $row['id_member'] . '">' . $row['poster_name'] . '</a>'
			),
			'time' => empty($row['poster_time']) ? $txt['never'] : timeformat($row['poster_time']),
			'timestamp' => forum_time(true, $row['poster_time']),
			'attachment' => array(
				'id' => $row['id_attach'],
				'size' => round($row['size'] / 1024, 2),
				'width' => $row['width'],
				'height' => $row['height'],
				'name' => $row['filename'],
				'downloads' => $row['downloads'],
				'href' => $row['attachment_type'] == 1 ? $modSettings['custom_avatar_url'] . '/' . $row['filename'] : ($scripturl . '?action=dlattach;' . ($context['browse_type'] == 'avatars' ? 'type=avatar;' : 'topic=' . $row['id_topic'] . '.0;') . 'id=' . $row['id_attach']),
				'link' => '<a href="' . ($row['attachment_type'] == 1 ? $modSettings['custom_avatar_url'] . '/' . $row['filename'] : ($scripturl . '?action=dlattach;' . ($context['browse_type'] == 'avatars' ? 'type=avatar;' : 'topic=' . $row['id_topic'] . '.0;') . 'id=' . $row['id_attach'])) . '"' . (empty($row['width']) || empty($row['height']) ? '' : ' onclick="return reqWin(this.href + \';image\', ' . ($row['width'] + 20) . ', ' . ($row['height'] + 20) . ', true);"') . '>' . $row['filename'] . '</a>'
			),
			'topic' => $row['id_topic'],
			'subject' => $row['subject'],
			'link' => '<a href="' . $scripturl . '?topic=' . $row['id_topic'] . '.0">' . $row['subject'] . '</a>'
		);
	$smfFunc['db_free_result']($request);
}

function MaintainFiles()
{
	global $db_prefix, $context, $modSettings, $txt, $smfFunc;

	$context['page_title'] = $txt['smf201'];
	$context['sub_template'] = 'maintenance';

	// Get the number of attachments....
	$request = $smfFunc['db_query']('', "
		SELECT COUNT(*)
		FROM {$db_prefix}attachments
		WHERE attachment_type = 0
			AND id_member = 0", __FILE__, __LINE__);
	list ($context['num_attachments']) = $smfFunc['db_fetch_row']($request);
	$smfFunc['db_free_result']($request);

	// Also get the avatar amount....
	$request = $smfFunc['db_query']('', "
		SELECT COUNT(*)
		FROM {$db_prefix}attachments
		WHERE id_member != 0", __FILE__, __LINE__);
	list ($context['num_avatars']) = $smfFunc['db_fetch_row']($request);
	$smfFunc['db_free_result']($request);

	// Find out how big the directory is.
	$attachmentDirSize = 0;
	$dir = @opendir($modSettings['attachmentUploadDir']) or fatal_lang_error('smf115b', 'critical');
	while ($file = readdir($dir))
	{
		if (substr($file, 0, -1) == '.')
			continue;

		if (preg_match('~^post_tmp_\d+_\d+$~', $file) != 0)
		{
			// Temp file is more than 5 hours old!
			if (filemtime($modSettings['attachmentUploadDir'] . '/' . $file) < time() - 18000)
				@unlink($modSettings['attachmentUploadDir'] . '/' . $file);
			continue;
		}

		$attachmentDirSize += filesize($modSettings['attachmentUploadDir'] . '/' . $file);
	}
	closedir($dir);
	// Divide it into kilobytes.
	$attachmentDirSize /= 1024;

	// If they specified a limit only....
	if (!empty($modSettings['attachmentDirSizeLimit']))
		$context['attachment_space'] = max(round($modSettings['attachmentDirSizeLimit'] - $attachmentDirSize, 2), 0);
	$context['attachment_total_size'] = round($attachmentDirSize, 2);
}

// !!! Not implemented yet.
function MoveAvatars()
{
	global $db_prefix, $modSettings, $smfFunc;

	// First make sure the custom avatar dir is writable.
	if (!is_writable($modSettings['custom_avatar_dir']))
	{
		// Try to fix it.
		@chmod($modSettings['custom_avatar_dir'], 0777);

		// Guess that didn't work :/?
		if (!is_writable($modSettings['custom_avatar_dir']))
			fatal_lang_error('attachments_no_write', 'critical');
	}

	$request = $smfFunc['db_query']('', "
		SELECT id_attach, id_member, filename
		FROM {$db_prefix}attachments
		WHERE attachment_type = 0
			AND id_member > 0", __FILE__, __LINE__);
	$updatedAvatars = array();
	while ($row = $smfFunc['db_fetch_assoc']($request))
	{
		$filename = getAttachmentFilename($row['filename'], $row['id_attach']);

		if (rename($filename, $modSettings['custom_avatar_dir'] . '/' . $row['filename']))
			$updatedAvatars[] = $row['id_attach'];
	}
	$smfFunc['db_free_result']($request);

	if (!empty($updatedAvatars))
		$smfFunc['db_query']('', "
			UPDATE {$db_prefix}attachments
			SET attachment_type = 1
			WHERE id_attach IN (" . implode(', ', $updatedAvatars) . ')', __FILE__, __LINE__);

	redirectexit('action=admin;area=manageattachments;sa=maintenance');
}

function RemoveAttachmentByAge()
{
	global $db_prefix, $modSettings, $smfFunc;

	checkSession('post', 'manageattachments');

	// !!! Ignore messages in topics that are stickied?

	// Deleting an attachment?
	if ($_REQUEST['type'] != 'avatars')
	{
		// Get all the old attachments.
		$messages = removeAttachments('a.attachment_type = 0 AND m.poster_time < ' . (time() - 24 * 60 * 60 * $_POST['age']), 'messages', true);

		// Update the messages to reflect the change.
		if (!empty($messages))
			$smfFunc['db_query']('', "
				UPDATE {$db_prefix}messages
				SET body = " . (!empty($_POST['notice']) ? "CONCAT(body, '<br /><br />$_POST[notice]')" : '') . "
				WHERE id_msg IN (" . implode(', ', $messages) . ")", __FILE__, __LINE__);
	}
	else
	{
		// Remove all the old avatars.
		removeAttachments('a.id_member != 0 AND mem.last_login < ' . (time() - 24 * 60 * 60 * $_POST['age']), 'members');
	}
	redirectexit('action=admin;area=manageattachments' . (empty($_REQUEST['avatars']) ? '' : ';avatars'));
}

function RemoveAttachmentBySize()
{
	global $db_prefix, $modSettings, $smfFunc;

	checkSession('post', 'manageattachments');

	// Find humungous attachments.
	$messages = removeAttachments('a.attachment_type = 0 AND a.size > ' . (1024 * $_POST['size']), 'messages', true);

	// And make a note on the post.
	if (!empty($messages))
		$smfFunc['db_query']('', "
			UPDATE {$db_prefix}messages
			SET body = " . (!empty($_POST['notice']) ? "CONCAT(body, '<br /><br />$_POST[notice]')" : '') . "
			WHERE id_msg IN (" . implode(',', $messages) . ")", __FILE__, __LINE__);

	redirectexit('action=admin;area=manageattachments;sa=maintenance');
}

function RemoveAttachment()
{
	global $db_prefix, $modSettings, $txt, $smfFunc;

	checkSession('post');

	if (!empty($_POST['remove']))
	{
		$attachments = array();
		// There must be a quicker way to pass this safety test??
		foreach ($_POST['remove'] as $removeID => $dummy)
			$attachments[] = (int) $removeID;

		if ($_REQUEST['type'] == 'avatars' && !empty($attachments))
			removeAttachments('a.id_attach IN (' . implode(', ', $attachments) . ')');
		else if (!empty($attachments))
		{
			$messages = removeAttachments('a.id_attach IN (' . implode(', ', $attachments) . ')', 'messages', true);

			// And change the message to reflect this.
			if (!empty($messages))
				$smfFunc['db_query']('', "
					UPDATE {$db_prefix}messages
					SET body = CONCAT(body, '<br /><br />" . addslashes($txt['smf216']) . "')
					WHERE id_msg IN (" . implode(', ', $messages) . ")", __FILE__, __LINE__);
		}
	}

	$_GET['sort'] = isset($_GET['sort']) ? $_GET['sort'] : 'date';
	redirectexit('action=admin;area=manageattachments;sa=browse;' . $_REQUEST['type'] . ';sort=' . $_GET['sort'] . (isset($_GET['desc']) ? ';desc' : '') . ';start=' . $_REQUEST['start']);
}

// !!! Not implemented (yet?)
function RemoveAllAttachments()
{
	global $db_prefix, $txt, $smfFunc;

	checkSession('get', 'manageattachments');

	$messages = removeAttachments('a.attachment_type = 0', '', true);

	if (!isset($_POST['notice']))
		$_POST['notice'] = $txt['smf216'];

	// Add the notice on the end of the changed messages.
	if (!empty($messages))
		$smfFunc['db_query']('', "
			UPDATE {$db_prefix}messages
			SET body = CONCAT(body, '<br /><br />$_POST[notice]')
			WHERE id_msg IN (" . implode(',', $messages) . ")", __FILE__, __LINE__);

	redirectexit('action=admin;area=manageattachments;sa=maintenance');
}

// Removes attachments - allowed query_types: '', 'messages', 'members'
function removeAttachments($condition, $query_type = '', $return_affected_messages = false, $autoThumbRemoval = true)
{
	global $db_prefix, $modSettings, $smfFunc;

	// Delete it only if it exists...
	$msgs = array();
	$attach = array();
	$parents = array();

	// Get all the attachment names and ID_MSGs.
	$request = $smfFunc['db_query']('', "
		SELECT
			a.filename, a.attachment_type, a.id_attach, a.id_member" . ($query_type == 'messages' ? ', m.id_msg' : ', a.id_msg') . ",
			IFNULL(thumb.id_attach, 0) AS id_thumb, thumb.filename AS thumb_filename, thumb_parent.id_attach AS id_parent
		FROM {$db_prefix}attachments AS a" .($query_type == 'members' ? "
			INNER JOIN {$db_prefix}members AS mem ON (mem.id_member = a.id_member)" : ($query_type == 'messages' ? "
			INNER JOIN {$db_prefix}messages AS m ON (m.id_msg = a.id_msg)" : '')) . "
			LEFT JOIN {$db_prefix}attachments AS thumb ON (thumb.id_attach = a.id_thumb)
			LEFT JOIN {$db_prefix}attachments AS thumb_parent ON (a.attachment_type = 3 AND thumb_parent.id_thumb = a.id_attach)
		WHERE $condition", __FILE__, __LINE__);
	while ($row = $smfFunc['db_fetch_assoc']($request))
	{
		// Figure out the "encrypted" filename and unlink it ;).
		if ($row['attachment_type'] == 1)
			@unlink($modSettings['custom_avatar_dir'] . '/' . $row['filename']);
		else
		{
			$filename = getAttachmentFilename($row['filename'], $row['id_attach']);
			@unlink($filename);

			// If this was a thumb, the parent attachment should know about it.
			if (!empty($row['id_parent']))
				$parents[] = $row['id_parent'];

			// If this attachments has a thumb, remove it as well.
			if (!empty($row['id_thumb']) && $autoThumbRemoval)
			{
				$thumb_filename = getAttachmentFilename($row['thumb_filename'], $row['id_thumb']);
				@unlink($thumb_filename);
				$attach[] = $row['id_thumb'];
			}
		}

		// Make a list.
		if ($return_affected_messages && empty($row['attachment_type']))
			$msgs[] = $row['id_msg'];
		$attach[] = $row['id_attach'];
	}
	$smfFunc['db_free_result']($request);

	// Removed attachments don't have to be updated anymore.
	$parents = array_diff($parents, $attach);
	if (!empty($parents))
		$smfFunc['db_query']('', "
			UPDATE {$db_prefix}attachments
			SET id_thumb = 0
			WHERE id_attach IN (" . implode(', ', $parents) . ")", __FILE__, __LINE__);

	if (!empty($attach))
		$smfFunc['db_query']('', "
			DELETE FROM {$db_prefix}attachments
			WHERE id_attach IN (" . implode(', ', $attach) . ")", __FILE__, __LINE__);

	if ($return_affected_messages)
		return array_unique($msgs);
}

// This function should find attachments in the database that no longer exist and clear them, and fix filesize issues.
function RepairAttachments()
{
	global $db_prefix, $modSettings, $context, $txt, $smfFunc;

	checkSession('get');

	// If we choose cancel, redirect right back.
	if (isset($_POST['cancel']))
		redirectexit('action=admin;area=manageattachments;sa=maintenance');

	// Try give us a while to sort this out...
	@set_time_limit(600);

	$_GET['step'] = empty($_GET['step']) ? 0 : (int) $_GET['step'];
	$_GET['substep'] = empty($_GET['substep']) ? 0 : (int) $_GET['substep'];

	// Don't recall the session just incase.
	if ($_GET['step'] == 0 && $_GET['substep'] == 0)
	{
		unset($_SESSION['attachments_to_fix']);
		unset($_SESSION['attachments_to_fix2']);

		// If we're actually fixing stuff - work out what.
		if (isset($_GET['fixErrors']))
		{
			// Nothing?
			if (empty($_POST['to_fix']))
				redirectexit('action=admin;area=manageattachments;sa=maintenance');
	
			$_SESSION['attachments_to_fix'] = array();
			//!!! No need to do this I think.
			foreach ($_POST['to_fix'] as $key => $value)
				$_SESSION['attachments_to_fix'][] = $value;
		}
	}
	
	$to_fix = !empty($_SESSION['attachments_to_fix']) ? $_SESSION['attachments_to_fix'] : array();
	$context['repair_errors'] = isset($_SESSION['attachments_to_fix2']) ? $_SESSION['attachments_to_fix2'] : array();
	$fix_errors = isset($_GET['fixErrors']) ? true : false;

	// All the valid problems are here:
	$context['repair_errors'] = array(
		'missing_thumbnail_parent' => 0,
		'parent_missing_thumbnail' => 0,
		'file_missing_on_disk' => 0,
		'file_wrong_size' => 0,
		'file_size_of_zero' => 0,
		'attachment_no_msg' => 0,
		'avatar_no_member' => 0,
	);

	// Get stranded thumbnails.
	if ($_GET['step'] <= 0)
	{
		$result = $smfFunc['db_query']('', "
			SELECT MAX(id_attach)
			FROM {$db_prefix}attachments
			WHERE attachment_type = 3", __FILE__, __LINE__);
		list ($thumbnails) = $smfFunc['db_fetch_row']($result);
		$smfFunc['db_free_result']($result);

		for (; $_GET['substep'] < $thumbnails; $_GET['substep'] += 500)
		{
			$to_remove = array();

			$result = $smfFunc['db_query']('', "
				SELECT thumb.id_attach, thumb.filename
				FROM {$db_prefix}attachments AS thumb
					LEFT JOIN {$db_prefix}attachments AS tparent ON (tparent.id_thumb = thumb.id_attach)
				WHERE thumb.id_attach BETWEEN $_GET[substep] AND $_GET[substep] + 499
					AND thumb.attachment_type = 3
					AND tparent.id_attach IS NULL
				GROUP BY thumb.id_attach", __FILE__, __LINE__);
			while ($row = $smfFunc['db_fetch_assoc']($result))
			{
				$to_remove[] = $row['id_attach'];
				$context['repair_errors']['missing_thumbnail_parent']++;

				// If we are repairing remove the file from disk now.
				if ($fix_errors && in_array('missing_thumbnail_parent', $to_fix))
				{
					$filename = getAttachmentFilename($row['filename'], $row['id_attach']);
					@unlink($filename);
				}
			}
			if ($smfFunc['db_num_rows']($result) != 0)
				$to_fix[] = 'missing_thumbnail_parent';
			$smfFunc['db_free_result']($result);

			// Do we need to delete what we have?
			if ($fix_errors && !empty($to_remove) && in_array('missing_thumbnail_parent', $to_fix))
				$smfFunc['db_query']('', "
					DELETE FROM {$db_prefix}attachments
					WHERE id_attach IN (" . implode(', ', $to_remove) . ")
						AND attachment_type = 3", __FILE__, __LINE__);
			
			pauseAttachmentMaintenance($to_fix, $thumbnails);
		}

		$_GET['step'] = 1;
		$_GET['substep'] = 0;
		pauseAttachmentMaintenance($to_fix);
	}

	// Find parents which think they have thumbnails, but actually, don't.
	if ($_GET['step'] <= 1)
	{
		$result = $smfFunc['db_query']('', "
			SELECT MAX(id_attach)
			FROM {$db_prefix}attachments
			WHERE id_thumb != 0", __FILE__, __LINE__);
		list ($thumbnails) = $smfFunc['db_fetch_row']($result);
		$smfFunc['db_free_result']($result);

		for (; $_GET['substep'] < $thumbnails; $_GET['substep'] += 500)
		{
			$to_update = array();

			$result = $smfFunc['db_query']('', "
				SELECT a.id_attach
				FROM {$db_prefix}attachments AS a
					LEFT JOIN {$db_prefix}attachments AS thumb ON (thumb.id_attach = a.id_thumb)
				WHERE a.id_attach BETWEEN $_GET[substep] AND $_GET[substep] + 499
					AND a.id_thumb != 0
					AND thumb.id_attach IS NULL", __FILE__, __LINE__);
			while ($row = $smfFunc['db_fetch_assoc']($result))
			{
				$to_update[] = $row['id_attach'];
				$context['repair_errors']['parent_missing_thumbnail']++;
			}
			if ($smfFunc['db_num_rows']($result) != 0)
				$to_fix[] = 'parent_missing_thumbnail';
			$smfFunc['db_free_result']($result);

			// Do we need to delete what we have?
			if ($fix_errors && !empty($to_update) && in_array('parent_missing_thumbnail', $to_fix))
				$smfFunc['db_query']('', "
					UPDATE {$db_prefix}attachments
					SET id_thumb = 0
					WHERE id_attach IN (" . implode(', ', $to_update) . ")", __FILE__, __LINE__);
			
			pauseAttachmentMaintenance($to_fix, $thumbnails);
		}

		$_GET['step'] = 2;
		$_GET['substep'] = 0;
		pauseAttachmentMaintenance($to_fix);
	}

	// This may take forever I'm afraid, but life sucks... recount EVERY attachments!
	if ($_GET['step'] <= 2)
	{
		$result = $smfFunc['db_query']('', "
			SELECT MAX(id_attach)
			FROM {$db_prefix}attachments", __FILE__, __LINE__);
		list ($thumbnails) = $smfFunc['db_fetch_row']($result);
		$smfFunc['db_free_result']($result);

		for (; $_GET['substep'] < $thumbnails; $_GET['substep'] += 250)
		{
			$to_remove = array();
			$errors_found = array();

			$result = $smfFunc['db_query']('', "
				SELECT id_attach, filename, size, attachment_type
				FROM {$db_prefix}attachments
				WHERE id_attach BETWEEN $_GET[substep] AND $_GET[substep] + 249", __FILE__, __LINE__);
			while ($row = $smfFunc['db_fetch_assoc']($result))
			{
				// Get the filename.
				if ($row['attachment_type'] == 1)
					$filename = $modSettings['custom_avatar_dir'] . '/' . $row['filename'];
				else
					$filename = getAttachmentFilename($row['filename'], $row['id_attach']);

				// File doesn't exist?
				if (!file_exists($filename))
				{
					$to_remove[] = $row['id_attach'];
					$context['repair_errors']['file_missing_on_disk']++;
					$errors_found[] = 'file_missing_on_disk';

					// Are we fixing this?
					if ($fix_errors && in_array('file_missing_on_disk', $to_fix))
						$to_remove[] = $row['id_attach'];

				}
				elseif (filesize($filename) == 0)
				{
					$context['repair_errors']['file_size_of_zero']++;
					$errors_found[] = 'file_size_of_zero';

					// Fixing?
					if ($fix_errors && in_array('file_size_of_zero', $to_fix))
					{
						$to_remove[] = $row['id_attach'];
						@unlink($filename);
					}
				}
				elseif (filesize($filename) != $row['size'])
				{
					$context['repair_errors']['file_wrong_size']++;
					$errors_found[] = 'file_wrong_size';

					// Fix it here?
					if ($fix_errors && in_array('file_wrong_size', $to_fix))
					{
						$smfFunc['db_query']('', "
							UPDATE {$db_prefix}attachments
							SET size = " . filesize($filename) . "
							WHERE id_attach = $row[id_attach]", __FILE__, __LINE__);
					}
				}
			}

			if (in_array('file_missing_on_disk', $errors_found))
				$to_fix[] = 'file_missing_on_disk';
			if (in_array('file_size_of_zero', $errors_found))
				$to_fix[] = 'file_size_of_zero';
			if (in_array('file_wrong_size', $errors_found))
				$to_fix[] = 'file_wrong_size';
			$smfFunc['db_free_result']($result);

			// Do we need to delete what we have?
			if ($fix_errors && !empty($to_remove))
			{
				$smfFunc['db_query']('', "
					DELETE FROM {$db_prefix}attachments
					WHERE id_attach IN (" . implode(', ', $to_remove) . ")", __FILE__, __LINE__);
				$smfFunc['db_query']('', "
					UPDATE {$db_prefix}attachments
					SET id_thumb = 0
					WHERE id_thumb IN (" . implode(', ', $to_remove) . ")", __FILE__, __LINE__);
			}
			
			pauseAttachmentMaintenance($to_fix, $thumbnails);
		}

		$_GET['step'] = 3;
		$_GET['substep'] = 0;
		pauseAttachmentMaintenance($to_fix);
	}

	// Get avatars with no members associated with them.
	if ($_GET['step'] <= 3)
	{
		$result = $smfFunc['db_query']('', "
			SELECT MAX(id_attach)
			FROM {$db_prefix}attachments", __FILE__, __LINE__);
		list ($thumbnails) = $smfFunc['db_fetch_row']($result);
		$smfFunc['db_free_result']($result);

		for (; $_GET['substep'] < $thumbnails; $_GET['substep'] += 500)
		{
			$to_remove = array();

			$result = $smfFunc['db_query']('', "
				SELECT a.id_attach, a.filename, a.attachment_type
				FROM {$db_prefix}attachments AS a
					LEFT JOIN {$db_prefix}members AS mem ON (mem.id_member = a.id_member)
				WHERE a.id_attach BETWEEN $_GET[substep] AND $_GET[substep] + 499
					AND a.id_member != 0
					AND a.id_msg = 0
					AND mem.id_member IS NULL", __FILE__, __LINE__);
			while ($row = $smfFunc['db_fetch_assoc']($result))
			{
				$to_remove[] = $row['id_attach'];
				$context['repair_errors']['avatar_no_member']++;

				// If we are repairing remove the file from disk now.
				if ($fix_errors && in_array('avatar_no_member', $to_fix))
				{
					if ($row['attachment_type'] == 1)
						$filename = $modSettings['custom_avatar_dir'] . '/' . $row['filename'];
					else
						$filename = getAttachmentFilename($row['filename'], $row['id_attach']);
					@unlink($filename);
				}
			}
			if ($smfFunc['db_num_rows']($result) != 0)
				$to_fix[] = 'avatar_no_member';
			$smfFunc['db_free_result']($result);

			// Do we need to delete what we have?
			if ($fix_errors && !empty($to_remove) && in_array('avatar_no_member', $to_fix))
				$smfFunc['db_query']('', "
					DELETE FROM {$db_prefix}attachments
					WHERE id_attach IN (" . implode(', ', $to_remove) . ")
						AND id_member != 0
						AND id_msg = 0", __FILE__, __LINE__);
			
			pauseAttachmentMaintenance($to_fix, $thumbnails);
		}

		$_GET['step'] = 4;
		$_GET['substep'] = 0;
		pauseAttachmentMaintenance($to_fix);
	}

	// What about attachments, who are missing a message :'(
	if ($_GET['step'] <= 4)
	{
		$result = $smfFunc['db_query']('', "
			SELECT MAX(id_attach)
			FROM {$db_prefix}attachments", __FILE__, __LINE__);
		list ($thumbnails) = $smfFunc['db_fetch_row']($result);
		$smfFunc['db_free_result']($result);

		for (; $_GET['substep'] < $thumbnails; $_GET['substep'] += 500)
		{
			$to_remove = array();

			$result = $smfFunc['db_query']('', "
				SELECT a.id_attach, a.filename
				FROM {$db_prefix}attachments AS a
					LEFT JOIN {$db_prefix}messages AS m ON (m.id_msg = a.id_msg)
				WHERE a.id_attach BETWEEN $_GET[substep] AND $_GET[substep] + 499
					AND a.id_member = 0
					AND a.id_msg != 0
					AND m.id_msg IS NULL", __FILE__, __LINE__);
			while ($row = $smfFunc['db_fetch_assoc']($result))
			{
				$to_remove[] = $row['id_attach'];
				$context['repair_errors']['attachment_no_msg']++;

				// If we are repairing remove the file from disk now.
				if ($fix_errors && in_array('attachment_no_msg', $to_fix))
				{
					$filename = getAttachmentFilename($row['filename'], $row['id_attach']);
					@unlink($filename);
				}
			}
			if ($smfFunc['db_num_rows']($result) != 0)
				$to_fix[] = 'attachment_no_msg';
			$smfFunc['db_free_result']($result);

			// Do we need to delete what we have?
			if ($fix_errors && !empty($to_remove) && in_array('attachment_no_msg', $to_fix))
				$smfFunc['db_query']('', "
					DELETE FROM {$db_prefix}attachments
					WHERE id_attach IN (" . implode(', ', $to_remove) . ")
						AND id_member = 0
						AND id_msg != 0", __FILE__, __LINE__);
			
			pauseAttachmentMaintenance($to_fix, $thumbnails);
		}

		$_GET['step'] = 5;
		$_GET['substep'] = 0;
		pauseAttachmentMaintenance($to_fix);
	}

	// Got here we must be doing well - just the template! :D
	$context['page_title'] = $txt['repair_attachments'];
	$context['admin_tabs']['tabs']['maintenance']['is_selected'] = true;
	$context['sub_template'] = 'attachment_repair';

	// What stage are we at?
	$context['completed'] = $fix_errors ? true : false;
	$context['errors_found'] = !empty($to_fix) ? true : false;
	
}

function pauseAttachmentMaintenance($to_fix, $max_substep = 0)
{
	global $context, $txt, $time_start;

	// Try get more time...
	@set_time_limit(600);
	if (function_exists('apache_reset_timeout'))
		apache_reset_timeout();

	// Have we already used our maximum time?
	if (time() - array_sum(explode(' ', $time_start)) < 3)
		return;

	$context['continue_get_data'] = '?action=admin;area=manageattachments;sa=repair' . (isset($_GET['fixErrors']) ? ';fixErrors' : '') . ';step=' . $_GET['step'] . ';substep=' . $_GET['substep'] . ';sesc=' . $context['session_id'];
	$context['page_title'] = $txt['not_done_title'];
	$context['continue_post_data'] = '';
	$context['continue_countdown'] = '2';
	$context['sub_template'] = 'not_done';

	// Specific stuff to not break this template!
	$context['admin_tabs']['tabs']['maintenance']['is_selected'] = true;

	// Change these two if more steps are added!
	if (empty($max_substep))
		$context['continue_percent'] = round(($_GET['step'] * 100) / 25);
	else
		$context['continue_percent'] = round(($_GET['step'] * 100 + ($_GET['substep'] * 100) / $max_substep) / 25);

	// Never more than 100%!
	$context['continue_percent'] = min($context['continue_percent'], 100);

	$_SESSION['attachments_to_fix'] = $to_fix;
	$_SESSION['attachments_to_fix2'] = $context['repair_errors'];

	obExit();
}

// Called from a mouse click, works out what we want to do with attachments and actions it.
function ApproveAttach()
{
	global $db_prefix, $smfFunc;

	// Security is our primary concern...
	checkSession('get');

	// If it approve or delete?
	$is_approve = !isset($_GET['sa']) || $_GET['sa'] != 'reject' ? true : false;

	$attachments = array();
	// If we are approving all ID's in a message , get the ID's.
	if ($_GET['sa'] == 'all' && !empty($_GET['mid']))
	{
		$id_msg = (int) $_GET['mid'];

		$request = $smfFunc['db_query']('', "
			SELECT id_attach
			FROM {$db_prefix}attachments
			WHERE id_msg = $id_msg
				AND approved = 0
				AND attachment_type = 0", __FILE__, __LINE__);
		while ($row = $smfFunc['db_fetch_assoc']($request))
			$attachments[] = $row['id_attach'];
		$smfFunc['db_free_result']($request);
	}
	elseif (!empty($_GET['aid']))
		$attachments[] = (int) $_GET['aid'];

	if (empty($attachments))
		fatal_lang_error(1);

	// Now we have some ID's cleaned and ready to approve, but first - let's check we have permission!
	$allowed_boards = boardsAllowedTo('approve_posts');

	// Validate the attachments exist and are the right approval state.
	$request = $smfFunc['db_query']('', "
		SELECT a.id_attach, m.id_board, m.id_msg, m.id_topic
		FROM ({$db_prefix}attachments AS a, {$db_prefix}messages AS m)
		WHERE a.id_attach IN (" . implode(',', $attachments) . ")
			AND m.id_msg = a.id_msg
			AND a.attachment_type = 0
			AND a.approved = 0", __FILE__, __LINE__);
	$attachments = array();
	while ($row = $smfFunc['db_fetch_assoc']($request))
	{
		// We can only add it if we can approve in this board!
		if ($allowed_boards = array(0) || in_array($row['id_board'], $allowed_boards))
		{
			$attachments[] = $row['id_attach'];

			// Also come up witht he redirection URL.
			$redirect = 'topic=' . $row['id_topic'] . '.msg' . $row['id_msg'] . '#msg' . $row['id_msg'];
		}
	}
	$smfFunc['db_free_result']($request);

	if (empty($attachments))
		fatal_lang_error(1);

	// Finally, we are there. Follow through!
	if ($is_approve)
		ApproveAttachments($attachments);
	else
		removeAttachments('a.id_attach IN (' . implode(', ', $attachments) . ')');

	// Return to the topic....
	redirectexit($redirect);
}

// Approve an attachment, or maybe even more - no permission check!
function ApproveAttachments($attachments)
{
	global $db_prefix, $smfFunc;

	if (empty($attachments))
		return 0;

	// For safety, check for thumbnails...
	$request = $smfFunc['db_query']('', "
		SELECT
			a.id_attach, a.id_member, IFNULL(thumb.id_attach, 0) AS id_thumb
		FROM {$db_prefix}attachments AS a
			LEFT JOIN {$db_prefix}attachments AS thumb ON (thumb.id_attach = a.id_thumb)
		WHERE a.id_attach IN (" . implode(', ', $attachments) . ")
			AND a.attachment_type = 0", __FILE__, __LINE__);
	$attachments = array();
	while ($row = $smfFunc['db_fetch_assoc']($request))
	{
		// Update the thumbnail too...
		if (!empty($row['id_thumb']))
			$attachments[] = $row['id_thumb'];

		$attachments[] = $row['id_attach'];
	}
	$smfFunc['db_free_result']($request);

	// Approving an attachment is not hard - it's easy.
	$smfFunc['db_query']('', "
		UPDATE {$db_prefix}attachments
		SET approved = 1
		WHERE id_attach IN (" . implode(', ', $attachments) . ")", __FILE__, __LINE__);

	// Remove from the approval queue.
	$smfFunc['db_query']('', "
		DELETE FROM {$db_prefix}approval_queue
		WHERE id_attach IN (" . implode(', ', $attachments) . ")", __FILE__, __LINE__);
}

?>