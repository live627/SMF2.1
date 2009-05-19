<edit file>
$boarddir/index.php
</edit file>

<search for>
* Software Version:           SMF 1.1.8                                           *
</search for>

<replace>
* Software Version:           SMF 1.1.9                                           *
</replace>


<search for>
$forum_version = 'SMF 1.1.8';
</search for>

<replace>
$forum_version = 'SMF 1.1.9';
</replace>


<search for>
	elseif (empty($modSettings['allow_guestAccess']) && $user_info['is_guest'] && (!isset($_REQUEST['action']) || !in_array($_REQUEST['action'], array('coppa', 'login', 'login2', 'register', 'register2', 'reminder', 'activate', 'smstats', 'help', '.xml', 'verificationcode'))))
</search for>

<replace>
	elseif (empty($modSettings['allow_guestAccess']) && $user_info['is_guest'] && (!isset($_REQUEST['action']) || !in_array($_REQUEST['action'], array('coppa', 'login', 'login2', 'register', 'register2', 'reminder', 'activate', 'smstats', 'help', 'verificationcode'))))
</replace>



<edit file>
$sourcedir/Display.php
</edit file>

<search for>
* Software Version:           SMF 1.1.4                                           *
</search for>

<replace>
* Software Version:           SMF 1.1.9                                           *
</replace>


<search for>
					a.ID_ATTACH, a.ID_MSG, a.filename, IFNULL(a.size, 0) AS filesize, a.downloads,
</search for>

<replace>
					a.ID_ATTACH, a.ID_MSG, a.filename, a.file_hash, IFNULL(a.size, 0) AS filesize, a.downloads,
</replace>


<search for>
			SELECT filename, ID_ATTACH, attachmentType
</search for>

<replace>
			SELECT filename, ID_ATTACH, attachmentType, file_hash
</replace>

<search for>
			SELECT a.filename, a.ID_ATTACH, a.attachmentType
</search for>

<replace>
			SELECT a.filename, a.ID_ATTACH, a.attachmentType, a.file_hash
</replace>


<search for>
	list ($real_filename, $ID_ATTACH, $attachmentType) = mysql_fetch_row($request);
</search for>

<replace>
	list ($real_filename, $ID_ATTACH, $attachmentType, $file_hash) = mysql_fetch_row($request);
</replace>


<search for>
	$filename = getAttachmentFilename($real_filename, $_REQUEST['attach']);
</search for>

<replace>
	$filename = getAttachmentFilename($real_filename, $_REQUEST['attach'], false, $file_hash);
</replace>


<search for>
	if (filesize($filename) != 0)
</search for>

<replace>
	// IE 6 just doesn't play nice. As dirty as this seems, it works.
	if ($context['browser']['is_ie6'] && isset($_REQUEST['image']))
		unset($_REQUEST['image']);

	elseif (filesize($filename) != 0)
</replace>


<search for>
				6 => 'bmp',
</search for>

<replace>
				6 => 'x-ms-bmp',
</replace>


<search for>
			if (!empty($size['mime']))
				header('Content-Type: ' . $size['mime']);
</search for>

<replace>
			if (!empty($size['mime']) && !in_array($size[2], array(4, 13)))
				header('Content-Type: ' . strtr($size['mime'], array('image/bmp' => 'image/x-ms-bmp')));
</replace>


<search for>
	if (!isset($_REQUEST['image']))
	{
		header('Content-Disposition: attachment; filename="' . $real_filename . '"');
		header('Content-Type: application/octet-stream');
	}
</search for>

<replace>
	header('Content-Disposition: ' . (isset($_REQUEST['image']) ? 'inline' : 'attachment') . '; filename="' . $real_filename . '"');
	if (!isset($_REQUEST['image']))
		header('Content-Type: application/octet-stream');
</replace>


<search for>
					$filename = getAttachmentFilename($attachment['filename'], $attachment['ID_ATTACH']);
</search for>

<replace>
					$filename = getAttachmentFilename($attachment['filename'], $attachment['ID_ATTACH'], false, $attachment['file_hash']);
</replace>


<search for>
						db_query("
							INSERT INTO {$db_prefix}attachments
								(ID_MSG, attachmentType, filename, size, width, height)
							VALUES ($ID_MSG, 3, '$thumb_filename', " . (int) $thumb_size . ", " . (int) $attachment['thumb_width'] . ", " . (int) $attachment['thumb_height'] . ")", __FILE__, __LINE__);
</search for>

<replace>
						$thumb_hash = getAttachmentFilename($thumb_filename, false, true);
						db_query("
							INSERT INTO {$db_prefix}attachments
								(ID_MSG, attachmentType, filename, file_hash, size, width, height)
							VALUES ($ID_MSG, 3, '$thumb_filename', '$thumb_hash', " . (int) $thumb_size . ", " . (int) $attachment['thumb_width'] . ", " . (int) $attachment['thumb_height'] . ")", __FILE__, __LINE__);
</replace>


<search for>
							$thumb_realname = getAttachmentFilename($thumb_filename, $attachment['ID_THUMB'], true);
							rename($filename . '_thumb', $modSettings['attachmentUploadDir'] . '/' . $thumb_realname);
</search for>

<replace>
							$thumb_realname = getAttachmentFilename($thumb_filename, $attachment['ID_THUMB'], false, $thumb_hash);
							rename($filename . '_thumb', $thumb_realname);
</replace>



<edit file>
$sourcedir/Load.php
</edit file>

<search for>
* Software Version:           SMF 1.1.6                                           *
</search for>

<replace>
* Software Version:           SMF 1.1.9                                           *
</replace>


<search for>
			// If this is the theme_dir of the default theme, store it.
</search for>

<replace>
			// There are just things we shouldn't be able to change as members.
			if ($row['ID_MEMBER'] != 0 && in_array($row['variable'], array('actual_theme_url', 'actual_images_url', 'base_theme_dir', 'base_theme_url', 'default_images_url', 'default_theme_dir', 'default_theme_url', 'default_template', 'images_url', 'number_recent_posts', 'smiley_sets_default', 'theme_dir', 'theme_id', 'theme_layers', 'theme_templates', 'theme_url')))
				continue;

			// If this is the theme_dir of the default theme, store it.
</replace>


<edit file>
$sourcedir/ManageAttachments.php
</edit file>

<search for>
* Software Version:           SMF 1.1.4                                           *
</search for>

<replace>
* Software Version:           SMF 1.1.9                                           *
</replace>


<search for>
* Copyright 2006 by:          Simple Machines LLC (http://www.simplemachines.org) *
</search for>

<replace>
* Copyright 2006-2009 by:     Simple Machines LLC (http://www.simplemachines.org) *
</replace>



<search for>
			'attachmentShowImages' => empty($_POST['attachmentShowImages']) ? '0' : '1',
			'attachmentEncryptFilenames' => empty($_POST['attachmentEncryptFilenames']) ? '0' : '1',
</search for>

<replace>
			'attachmentShowImages' => empty($_POST['attachmentShowImages']) ? '0' : '1',
</replace>



<search for>
		SELECT ID_ATTACH, ID_MEMBER, filename
</search for>

<replace>
		SELECT ID_ATTACH, ID_MEMBER, filename, file_hash
</replace>


<search for>
	while ($row = mysql_fetch_assoc($request))
	{
		$filename = getAttachmentFilename($row['filename'], $row['ID_ATTACH']);
</search for>

<replace>
	while ($row = mysql_fetch_assoc($request))
	{
		$filename = getAttachmentFilename($row['filename'], $row['ID_ATTACH'], false, $row['file_hash']);
</replace>


<search for>
			a.filename, a.attachmentType, a.ID_ATTACH, a.ID_MEMBER" . ($query_type == 'messages' ? ', m.ID_MSG' : ', a.ID_MSG') . ",
</search for>

<replace>
			a.filename, a.file_hash, a.attachmentType, a.ID_ATTACH, a.ID_MEMBER" . ($query_type == 'messages' ? ', m.ID_MSG' : ', a.ID_MSG') . ",
</replace>


<search for>
			$filename = getAttachmentFilename($row['filename'], $row['ID_ATTACH']);
			@unlink($filename);

			// If this was a thumb, the parent attachment should know about it.
</search for>

<replace>
			$filename = getAttachmentFilename($row['filename'], $row['ID_ATTACH'], false, $row['file_hash']);
			@unlink($filename);

			// If this was a thumb, the parent attachment should know about it.
</replace>


<search for>
				$thumb_filename = getAttachmentFilename($row['thumb_filename'], $row['ID_THUMB']);
				@unlink($thumb_filename);
				$attach[] = $row['ID_THUMB'];
</search for>

<replace>
				$thumb_filename = getAttachmentFilename($row['thumb_filename'], $row['ID_THUMB'], false, $row['file_hash']);
				@unlink($thumb_filename);
				$attach[] = $row['ID_THUMB'];
</replace>


<search for>
				SELECT thumb.ID_ATTACH, thumb.filename
</search for>

<replace>
				SELECT thumb.ID_ATTACH, thumb.filename, thumb.file_hash
</replace>


<search for>
				// If we are repairing remove the file from disk now.
				if ($fix_errors && in_array('missing_thumbnail_parent', $to_fix))
				{
					$filename = getAttachmentFilename($row['filename'], $row['ID_ATTACH']);
</search for>

<replace>
				// If we are repairing remove the file from disk now.
				if ($fix_errors && in_array('missing_thumbnail_parent', $to_fix))
				{
					$filename = getAttachmentFilename($row['filename'], $row['ID_ATTACH'], false, $row['file_hash']);
</replace>


<search for>
				SELECT ID_ATTACH, filename, size, attachmentType
</search for>

<replace>
				SELECT ID_ATTACH, filename, file_hash, size, attachmentType
</replace>


<search for>
					$filename = getAttachmentFilename($row['filename'], $row['ID_ATTACH']);

				// File doesn't exist?
</search for>

<replace>
					$filename = getAttachmentFilename($row['filename'], $row['ID_ATTACH'], false, $row['file_hash']);

				// File doesn't exist?
</replace>


<search for>
				SELECT a.ID_ATTACH, a.filename, a.attachmentType
</search for>

<replace>
				SELECT a.ID_ATTACH, a.filename, a.file_hash, a.attachmentType
</replace>


<search for>
					if ($row['attachmentType'] == 1)
						$filename = $modSettings['custom_avatar_dir'] . '/' . $row['filename'];
					else
						$filename = getAttachmentFilename($row['filename'], $row['ID_ATTACH']);
					@unlink($filename);
				}
</search for>

<replace>
					if ($row['attachmentType'] == 1)
						$filename = $modSettings['custom_avatar_dir'] . '/' . $row['filename'];
					else
						$filename = getAttachmentFilename($row['filename'], $row['ID_ATTACH'], false, $row['file_hash']);
					@unlink($filename);
				}
</replace>


<search for>
				SELECT a.ID_ATTACH, a.filename
				FROM {$db_prefix}attachments AS a
</search for>

<replace>
				SELECT a.ID_ATTACH, a.filename, a.file_hash
				FROM {$db_prefix}attachments AS a
</replace>


<search for>
				// If we are repairing remove the file from disk now.
				if ($fix_errors && in_array('attachment_no_msg', $to_fix))
				{
					$filename = getAttachmentFilename($row['filename'], $row['ID_ATTACH']);
</search for>

<replace>
				// If we are repairing remove the file from disk now.
				if ($fix_errors && in_array('attachment_no_msg', $to_fix))
				{
					$filename = getAttachmentFilename($row['filename'], $row['ID_ATTACH'], false, $row['file_hash']);
</replace>



<edit file>
$sourcedir/PackageGet.php
</edit file>
<search for>
* Software Version:           SMF 1.1.8                                           *
</search for>

<replace>
* Software Version:           SMF 1.1.9                                           *
</replace>


<search for>
* Copyright 2006 by:          Simple Machines LLC (http://www.simplemachines.org) *
</search for>

<replace>
* Copyright 2006-2009 by:     Simple Machines LLC (http://www.simplemachines.org) *
</replace>


<search for>
		$default_author = $listing->fetch('default-author');
</search for>

<replace>
		$default_author = htmlspecialchars($listing->fetch('default-author'));
</replace>


<search for>
			$default_title = $listing->fetch('default-website/@title');
</search for>

<replace>
			$default_title = htmlspecialchars($listing->fetch('default-website/@title'));
</replace>


<search for>
			if (in_array($package['type'], array('title', 'heading', 'text', 'rule')))
				$package['name'] = $thisPackage->fetch('.');
</search for>

<replace>
			if (in_array($package['type'], array('title', 'heading', 'text', 'rule')))
				$package['name'] = htmlspecialchars($thisPackage->fetch('.'));
</replace>


<search for>
				$package['name'] = $thisPackage->fetch('.');
				$package['link'] = '<a href="' . $package['href'] . '">' . $package['name'] . '</a>';
</search for>

<replace>
				$package['name'] = htmlspecialchars($thisPackage->fetch('.'));
				$package['link'] = '<a href="' . $package['href'] . '">' . $package['name'] . '</a>';
</replace>


<search for>
				if ($package['description'] == '')
					$package['description'] = $txt['pacman8'];
</search for>

<replace>
				if ($package['description'] == '')
					$package['description'] = $txt['pacman8'];
				else
					$package['description'] = parse_bbc(preg_replace('~\[[/]?html\]~i', '', htmlspecialchars($package['description'])));				
</replace>


<search for>
				$package['href'] = $url . '/' . $package['filename'];
</search for>

<replace>
				$package['href'] = $url . '/' . $package['filename'];
				$package['name'] = htmlspecialchars($package['name']);
</replace>


<search for>
						$package['author']['email'] = $thisPackage->fetch('author/@email');
</search for>

<replace>
						$package['author']['email'] = htmlspecialchars($thisPackage->fetch('author/@email'));
</replace>


<search for>
						$package['author']['name'] = $thisPackage->fetch('author');
</search for>

<replace>
						$package['author']['name'] = htmlspecialchars($thisPackage->fetch('author'));
</replace>


<search for>
						$package['author']['website']['name'] = $thisPackage->fetch('website/@title');
					elseif (isset($default_title))
						$package['author']['website']['name'] = $default_title;
					elseif ($thisPackage->exists('website'))
						$package['author']['website']['name'] = $thisPackage->fetch('website');
</search for>

<replace>
						$package['author']['website']['name'] = htmlspecialchars($thisPackage->fetch('website/@title'));
					elseif (isset($default_title))
						$package['author']['website']['name'] = $default_title;
					elseif ($thisPackage->exists('website'))
						$package['author']['website']['name'] = htmlspecialchars($thisPackage->fetch('website'));
</replace>



<edit file>
$sourcedir/Post.php
</edit file>

<search for>
* Software Version:           SMF 1.1.5                                           *
</search for>

<replace>
* Software Version:           SMF 1.1.9                                           *
</replace>

<search for>
					'name' => getAttachmentFilename($name, false, true),
</search for>

<replace>
					'name' => $name,
</replace>



<edit file>
$sourcedir/Profile.php
</edit file>

<search for>
* Software Version:           SMF 1.1.6                                           *
</search for>

<replace>
* Software Version:           SMF 1.1.9                                           *
</replace>


<search for>
	// These are the theme changes...
</search for>

<replace>
	$reservedVars = array(
		'actual_theme_url',
		'actual_images_url',
		'base_theme_dir',
		'base_theme_url',
		'default_images_url',
		'default_theme_dir',
		'default_theme_url',
		'default_template',
		'images_url',
		'number_recent_posts',
		'smiley_sets_default',
		'theme_dir',
		'theme_id',
		'theme_layers',
		'theme_templates',
		'theme_url',
	);

	// Can't change reserved vars.
	if ((isset($_POST['options']) && array_intersect(array_keys($_POST['options']), $reservedVars) != array()) || (isset($_POST['default_options']) && array_intersect(array_keys($_POST['default_options']), $reservedVars) != array()))
		fatal_lang_error(1);

	// These are the theme changes...
</replace>


<search for>
				$extensions = array(
</search for>

<replace>
				// Though not an exhaustive list, better safe than sorry.
				$fp = fopen($_FILES['attachment']['tmp_name'], 'rb');
				if (!$fp)
					fatal_lang_error('smf124');

				// Now try to find an infection.
				while (!feof($fp))
				{
					if (preg_match('~(iframe|\\<\\?php|\\<\\?[\s=]|\\<%[\s=]|html|eval|body|script\W)~', fgets($fp, 4096)) === 1)
					{
						if (file_exists($uploadDir . '/avatar_tmp_' . $memID))
							@unlink($uploadDir . '/avatar_tmp_' . $memID);

						fatal_lang_error('smf124');
					}
				}
				fclose($fp);

				$extensions = array(
</replace>


<search for>
				if (!rename($_FILES['attachment']['tmp_name'], $uploadDir . '/' . $destName))
					fatal_lang_error('smf124');

				db_query("
					INSERT INTO {$db_prefix}attachments
						(ID_MEMBER, attachmentType, filename, size, width, height)
					VALUES ($memID, " . (empty($modSettings['custom_avatar_enabled']) ? '0' : '1') . ", '$destName', " . filesize($uploadDir . '/' . $destName) . ", " . (int) $width . ", " . (int) $height . ")", __FILE__, __LINE__);

				// Attempt to chmod it.
				@chmod($uploadDir . '/' . $destName, 0644);
</search for>

<replace>
				$file_hash = empty($modSettings['custom_avatar_enabled']) ? getAttachmentFilename($destName, false, true) : '';

				db_query("
					INSERT INTO {$db_prefix}attachments
						(ID_MEMBER, attachmentType, filename, file_hash, size, width, height)
					VALUES ($memID, " . (empty($modSettings['custom_avatar_enabled']) ? '0' : '1') . ", '$destName', '" . (empty($file_hash) ? "" : "$file_hash") . "', " . filesize($_FILES['attachment']['tmp_name']) . ", " . (int) $width . ", " . (int) $height . ")", __FILE__, __LINE__);
				$attachID = db_insert_id();

				// Try to move this avatar.
				$destinationPath = $uploadDir . '/' . (empty($file_hash) ? $destName : $attachID . '_' . $file_hash);
				if (!rename($_FILES['attachment']['tmp_name'], $destinationPath))
				{
					// The move failed, get rid of it and die.
					db_query("
						DELETE FROM {$db_prefix}attachments
						WHERE ID_ATTACH = $attachID", __FILE__, __LINE__);

					fatal_lang_error('smf124');
				}

				// Attempt to chmod it.
				@chmod($destinationPath, 0644);
</replace>


<search for>
		$context['activate_message'] = isset($txt['account_activate_method_' . $context['member']['is_activated'] % 10]) ? $txt['account_activate_method_' . $context['member']['is_activated']] : $txt['account_not_activated'];
</search for>

<replace>
		$context['activate_message'] = isset($txt['account_activate_method_' . $context['member']['is_activated'] % 10]) ? $txt['account_activate_method_' . $context['member']['is_activated'] % 10] : $txt['account_not_activated'];
</replace>



<edit file>
$sourcedir/QueryString.php
</edit file>

<search for>
* Software Version:           SMF 1.1.7                                           *
</search for>

<replace>
* Software Version:           SMF 1.1.9                                           *
</replace>


<search for>
* Copyright 2006 by:          Simple Machines LLC (http://www.simplemachines.org) *
</search for>

<replace>
* Copyright 2006-2009 by:     Simple Machines LLC (http://www.simplemachines.org) *
</replace>


<search for>
	if (!empty($_SERVER['HTTP_X_FORWARDED_FOR']) && !empty($_SERVER['HTTP_CLIENT_IP']) && (preg_match('~^((0|10|172\.16|192\.168|255|127\.0)\.|unknown)~', $_SERVER['HTTP_CLIENT_IP']) == 0 || preg_match('~^((0|10|172\.16|192\.168|255|127\.0)\.|unknown)~', $_SERVER['REMOTE_ADDR']) != 0))
	{
		// We have both forwarded for AND client IP... check the first forwarded for as the block - only switch if it's better that way.
		if (strtok($_SERVER['HTTP_X_FORWARDED_FOR'], '.') != strtok($_SERVER['HTTP_CLIENT_IP'], '.') && '.' . strtok($_SERVER['HTTP_X_FORWARDED_FOR'], '.') == strrchr($_SERVER['HTTP_CLIENT_IP'], '.') && (preg_match('~^((0|10|172\.16|192\.168|255|127\.0)\.|unknown)~', $_SERVER['HTTP_X_FORWARDED_FOR']) == 0 || preg_match('~^((0|10|172\.16|192\.168|255|127\.0)\.|unknown)~', $_SERVER['REMOTE_ADDR']) != 0))
</search for>

<replace>
	if (!empty($_SERVER['HTTP_X_FORWARDED_FOR']) && !empty($_SERVER['HTTP_CLIENT_IP']) && (preg_match('~^((0|10|172\.(1[6-9]|2[0-9]|3[01])|192\.168|255|127)\.|unknown)~', $_SERVER['HTTP_CLIENT_IP']) == 0 || preg_match('~^((0|10|172\.(1[6-9]|2[0-9]|3[01])|192\.168|255|127)\.|unknown)~', $_SERVER['REMOTE_ADDR']) != 0))
	{
		// We have both forwarded for AND client IP... check the first forwarded for as the block - only switch if it's better that way.
		if (strtok($_SERVER['HTTP_X_FORWARDED_FOR'], '.') != strtok($_SERVER['HTTP_CLIENT_IP'], '.') && '.' . strtok($_SERVER['HTTP_X_FORWARDED_FOR'], '.') == strrchr($_SERVER['HTTP_CLIENT_IP'], '.') && (preg_match('~^((0|10|172\.(1[6-9]|2[0-9]|3[01])|192\.168|255|127)\.|unknown)~', $_SERVER['HTTP_X_FORWARDED_FOR']) == 0 || preg_match('~^((0|10|172\.(1[6-9]|2[0-9]|3[01])|192\.168|255|127)\.|unknown)~', $_SERVER['REMOTE_ADDR']) != 0))
</replace>


<search for>
	if (!empty($_SERVER['HTTP_CLIENT_IP']) && (preg_match('~^((0|10|172\.16|192\.168|255|127\.0)\.|unknown)~', $_SERVER['HTTP_CLIENT_IP']) == 0 || preg_match('~^((0|10|172\.16|192\.168|255|127\.0)\.|unknown)~', $_SERVER['REMOTE_ADDR']) != 0))
</search for>

<replace>
	if (!empty($_SERVER['HTTP_CLIENT_IP']) && (preg_match('~^((0|10|172\.(1[6-9]|2[0-9]|3[01])|192\.168|255|127)\.|unknown)~', $_SERVER['HTTP_CLIENT_IP']) == 0 || preg_match('~^((0|10|172\.(1[6-9]|2[0-9]|3[01])|192\.168|255|127)\.|unknown)~', $_SERVER['REMOTE_ADDR']) != 0))
</replace>


<search for>
				if (preg_match('~^((0|10|172\.16|192\.168|255|127\.0)\.|unknown)~', $ip) != 0 && preg_match('~^((0|10|172\.16|192\.168|255|127\.0)\.|unknown)~', $_SERVER['REMOTE_ADDR']) == 0)
</search for>

<replace>
				if (preg_match('~^((0|10|172\.(1[6-9]|2[0-9]|3[01])|192\.168|255|127)\.|unknown)~', $ip) != 0 && preg_match('~^((0|10|172\.(1[6-9]|2[0-9]|3[01])|192\.168|255|127)\.|unknown)~', $_SERVER['REMOTE_ADDR']) == 0)
</replace>


<search for>
		elseif (preg_match('~^((0|10|172\.16|192\.168|255|127\.0)\.|unknown)~', $_SERVER['HTTP_X_FORWARDED_FOR']) == 0 || preg_match('~^((0|10|172\.16|192\.168|255|127\.0)\.|unknown)~', $_SERVER['REMOTE_ADDR']) != 0)
</search for>

<replace>
		elseif (preg_match('~^((0|10|172\.(1[6-9]|2[0-9]|3[01])|192\.168|255|127)\.|unknown)~', $_SERVER['HTTP_X_FORWARDED_FOR']) == 0 || preg_match('~^((0|10|172\.(1[6-9]|2[0-9]|3[01])|192\.168|255|127)\.|unknown)~', $_SERVER['REMOTE_ADDR']) != 0)
</replace>



<edit file>
$sourcedir/Security.php
</edit file>

<search for>
* Software Version:           SMF 1.1.8                                           *
</search for>

<replace>
* Software Version:           SMF 1.1.9                                           *
</replace>


<search for>
	if (isset($_GET['confirm']) && isset($_SESSION['confirm_' . $action]) && md5($_GET['confirm'] . $_SERVER['HTTP_USER_AGENT']) !== $_SESSION['confirm_' . $action])
		return true;
		
	else
	{
		$token = md5(mt_rand() . session_id() . (string) microtime() . $modSettings['rand_seed']);
		$_SESSION['confirm_' . $action] = md5($token, $_SERVER['HTTP_USER_AGENT']);
</search for>

<replace>
	if (isset($_GET['confirm']) && isset($_SESSION['confirm_' . $action]) && md5($_GET['confirm'] . $_SERVER['HTTP_USER_AGENT']) == $_SESSION['confirm_' . $action])
		return true;
		
	else
	{
		$token = md5(mt_rand() . session_id() . (string) microtime() . $modSettings['rand_seed']);
		$_SESSION['confirm_' . $action] = md5($token . $_SERVER['HTTP_USER_AGENT']);
</replace>




<edit file>
$sourcedir/Subs.php
</edit file>

<search for>
* Software Version:           SMF 1.1.6                                           *
</search for>

<replace>
* Software Version:           SMF 1.1.9                                           *
</replace>


<search for>
			<div style="white-space: normal;">The administrator doesn\'t want a copyright notice saying this is copyright 2006 - 2007 by <a href="http://www.simplemachines.org/about/copyright.php" target="_blank">Simple Machines LLC</a>, and named <a href="http://www.simplemachines.org/">SMF</a>, so the forum will honor this request and be quiet.</div>';
</search for>

<replace>
			<div style="white-space: normal;">The administrator doesn\'t want a copyright notice saying this is copyright 2006 - 2009 by <a href="http://www.simplemachines.org/about/copyright.php" target="_blank">Simple Machines LLC</a>, and named <a href="http://www.simplemachines.org/">SMF</a>, so the forum will honor this request and be quiet.</div>';
</replace>


<search for>
// Get an attachment's encrypted filename.  If $new is true, won't check for file existence.
function getAttachmentFilename($filename, $attachment_id, $new = false)
</search for>

<replace>
// Get an attachment's encrypted filename.  If $new is true, won't check for file existence.
function getAttachmentFilename($filename, $attachment_id, $new = false, $file_hash = '')
{
	global $modSettings, $db_prefix;

	// Just make up a nice hash...
	if ($new)
		return sha1(md5($filename . time()) . mt_rand());

	// Grab the file hash if it wasn't added.
	if ($file_hash === '')
	{
		$request = db_query("
			SELECT file_hash
			FROM {$db_prefix}attachments
			WHERE ID_ATTACH = " . (int) $attachment_id, __FILE__, __LINE__);

		if (mysql_num_rows($request) === 0)
			return false;

		list ($file_hash) = mysql_fetch_row($request);

		mysql_free_result($request);
	}

	// In case of files from the old system, do a legacy call.
	if (empty($file_hash))
		return getLegacyAttachmentFilename($filename, $attachment_id, $new);

	return $modSettings['attachmentUploadDir'] . '/' . $attachment_id . '_' . $file_hash;
}

function getLegacyAttachmentFilename($filename, $attachment_id, $new = false)
</replace>



<edit file>
$sourcedir/Subs-Graphics.php
</edit file>

<search for>
* Software Version:           SMF 1.1.7                                           *
</search for>

<replace>
* Software Version:           SMF 1.1.9                                           *
</replace>


<search for>
	db_query("
		INSERT INTO {$db_prefix}attachments
			(ID_MEMBER, attachmentType, filename, size)
		VALUES ($memID, " . (empty($modSettings['custom_avatar_enabled']) ? '0' : '1') . ", '$destName', 1)", __FILE__, __LINE__);
</search for>

<replace>

	$avatar_hash = empty($modSettings['custom_avatar_enabled']) ? getAttachmentFilename($destName, false, true) : '';

	db_query("
		INSERT INTO {$db_prefix}attachments
			(ID_MEMBER, attachmentType, filename, file_hash, size)
		VALUES ($memID, " . (empty($modSettings['custom_avatar_enabled']) ? '0' : '1') . ", '$destName', '" . (empty($avatar_hash) ? "" : "$avatar_hash") . "', 1)", __FILE__, __LINE__);
</replace>


<search for>
		if (preg_match('~(iframe|\\<\\?php|\\<\\?|\\<%|html|eval|body|script)~', $fileContents) === 1)
		{
			fclose($fp);
</search for>

<replace>
		if (preg_match('~(iframe|\\<\\?php|\\<\\?[\s=]|\\<%[\s=]|html|eval|body|script\W)~', $fileContents) === 1)
		{
			fclose($fp);
</replace>


<search for>
		$fp2 = fopen($url, 'rb');
		while (!feof($fp2))
			fwrite($fp, fread($fp2, 8192));
		fclose($fp2);
</search for>

<replace>
		$fp2 = fopen($url, 'rb');
		$prev_chunk = '';
		while (!feof($fp2))
		{
			$cur_chunk = fread($fp2, 8192);

			// Make sure nothing odd came through.
			if (preg_match('~(iframe|\\<\\?php|\\<\\?[\s=]|\\<%[\s=]|html|eval|body|script\W)~', $prev_chunk . $cur_chunk) === 1)
			{
				fclose($fp2);
				fclose($fp);
				unlink($destName);
				return false;
			}

			fwrite($fp, $cur_chunk);
			$prev_chunk = $cur_chunk;
		}
		fclose($fp2);
</replace>


<search for>
		if (rename($destName . '.tmp', $destName))
		{
</search for>

<replace>
		if (rename($destName . '.tmp', empty($avatar_hash) ? $destName : $modSettings['attachmentUploadDir'] . '/' . $attachID . '_' . $avatar_hash))
		{
			$destName = empty($avatar_hash) ? $destName : $modSettings['attachmentUploadDir'] . '/' . $attachID . '_' . $avatar_hash;
</replace>


<search for>
	$code_image = imagecreate($total_width, $max_height);
</search for>

<replace>
	$code_image = $gd2 ? imagecreatetruecolor($total_width, $max_height) : imagecreate($total_width, $max_height);
</replace>



<edit file>
$sourcedir/Subs-Members.php
</edit file>

<search for>
* Software Version:           SMF 1.1.6                                           *
</search for>

<replace>
* Software Version:           SMF 1.1.9                                           *
</replace>


<search for>
	// Some of these might be overwritten. (the lower ones that are in the arrays below.)
</search for>

<replace>
	$reservedVars = array(
		'actual_theme_url',
		'actual_images_url',
		'base_theme_dir',
		'base_theme_url',
		'default_images_url',
		'default_theme_dir',
		'default_theme_url',
		'default_template',
		'images_url',
		'number_recent_posts',
		'smiley_sets_default',
		'theme_dir',
		'theme_id',
		'theme_layers',
		'theme_templates',
		'theme_url',
	);

	// Can't change reserved vars.
	if (isset($regOptions['theme_vars']) && array_intersect(array_keys($regOptions['theme_vars']), $reservedVars) != array())
		fatal_lang_error('theme3');

	// Some of these might be overwritten. (the lower ones that are in the arrays below.)
</replace>



<edit file>
$sourcedir/Subs-Post.php
</edit file>

<search for>
* Software Version:           SMF 1.1.8                                           *
</search for>

<replace>
* Software Version:           SMF 1.1.9                                           *
</replace>


<search for>
			$parts[$i] = preg_replace('~\[([/]?)(list|li|table|tr|td)([^\]]*)\]~ie', '\'[$1\' . strtolower(\'$2\') . \'$3]\'', $parts[$i]);
</search for>

<replace>
			$parts[$i] = preg_replace('~\[([/]?)(list|li|table|tr|td)((\s[^\]]+)*)\]~ie', '\'[$1\' . strtolower(\'$2\') . \'$3]\'', $parts[$i]);
</replace>


<search for>
	// Change breaks back to \n's.
	return preg_replace('~<br( /)?' . '>~', "\n", implode('', $parts));
</search for>

<replace>
	// Change breaks back to \n's and &nsbp; back to spaces.
	return preg_replace('~<br( /)?' . '>~', "\n", str_replace('&nbsp;', ' ', implode('', $parts)));
</replace>


<search for>
	// Remove special foreign characters from the filename.
	if (empty($modSettings['attachmentEncryptFilenames']))
		$attachmentOptions['name'] = getAttachmentFilename($attachmentOptions['name'], false, true);
</search for>

<replace>
	// Get the hash if no hash has been given yet.
	if (empty($attachmentOptions['file_hash']))
		$attachmentOptions['file_hash'] = getAttachmentFilename($attachmentOptions['name'], false, true);
</replace>


<search for>
			(ID_MSG, filename, size, width, height)
		VALUES (" . (int) $attachmentOptions['post'] . ", SUBSTRING('" . $attachmentOptions['name'] . "', 1, 255), " . (int) $attachmentOptions['size'] . ', ' . (empty($attachmentOptions['width']) ? '0' : (int) $attachmentOptions['width']) . ', ' . (empty($attachmentOptions['height']) ? '0' : (int) $attachmentOptions['height']) . ')', __FILE__, __LINE__);
</search for>

<replace>
			(ID_MSG, filename, file_hash, size, width, height)
		VALUES (" . (int) $attachmentOptions['post'] . ", SUBSTRING('" . $attachmentOptions['name'] . "', 1, 255), '$attachmentOptions[file_hash]', " . (int) $attachmentOptions['size'] . ', ' . (empty($attachmentOptions['width']) ? '0' : (int) $attachmentOptions['width']) . ', ' . (empty($attachmentOptions['height']) ? '0' : (int) $attachmentOptions['height']) . ')', __FILE__, __LINE__);
</replace>


<search for>
	$attachmentOptions['destination'] = $modSettings['attachmentUploadDir'] . '/' . getAttachmentFilename(basename($attachmentOptions['name']), $attachmentOptions['id'], true);
</search for>

<replace>
	$attachmentOptions['destination'] = getAttachmentFilename(basename($attachmentOptions['name']), $attachmentOptions['id'], false, $attachmentOptions['file_hash']);
</replace>


<search for>
			// To the database we go!
			db_query("
				INSERT INTO {$db_prefix}attachments
					(ID_MSG, attachmentType, filename, size, width, height)
				VALUES (" . (int) $attachmentOptions['post'] . ", 3, SUBSTRING('$thumb_filename', 1, 255), " . (int) $thumb_size . ", " . (int) $thumb_width . ", " . (int) $thumb_height . ")", __FILE__, __LINE__);
</search for>

<replace>
			// To the database we go!
			$thumb_file_hash = getAttachmentFilename($thumb_filename, false, true);
			db_query("
				INSERT INTO {$db_prefix}attachments
					(ID_MSG, attachmentType, filename, file_hash, size, width, height)
				VALUES (" . (int) $attachmentOptions['post'] . ", 3, SUBSTRING('$thumb_filename', 1, 255), '$thumb_file_hash', " . (int) $thumb_size . ", " . (int) $thumb_width . ", " . (int) $thumb_height . ")", __FILE__, __LINE__);
</replace>


<search for>
				rename($attachmentOptions['destination'] . '_thumb', $modSettings['attachmentUploadDir'] . '/' . getAttachmentFilename($thumb_filename, $attachmentOptions['thumb'], true));
</search for>

<replace>
				rename($attachmentOptions['destination'] . '_thumb', getAttachmentFilename($thumb_filename, $attachmentOptions['thumb'], false, $thumb_file_hash));
</replace>



<edit file>
$themedir/ManageAttachments.template.php
</edit file>

<search for>
// Version: 1.1; ManageAttachments
</search for>

<replace>
// Version: 1.1.9; ManageAttachments
</replace>

<search for>
			<td><input type="text" name="attachmentExtensions" id="attachmentExtensions" value="', $modSettings['attachmentExtensions'], '" size="40" /></td>
		</tr><tr class="windowbg2">
			<td width="50%" align="right"><label for="attachmentEncryptFilenames">', $txt['attachmentEncryptFilenames'], ' <a href="', $scripturl, '?action=helpadmin;help=attachmentEncryptFilenames" onclick="return reqWin(this.href);" class="help">(?)</a>:</label></td>
			<td><input type="checkbox" name="attachmentEncryptFilenames" id="attachmentEncryptFilenames" value="1" class="check"', empty($modSettings['attachmentEncryptFilenames']) ? '' : ' checked="checked"', ' /></td>
</search for>

<replace>
			<td><input type="text" name="attachmentExtensions" id="attachmentExtensions" value="', $modSettings['attachmentExtensions'], '" size="40" /></td>
</replace>



<edit file>
$themedir/Recent.template.php
</edit file>

<search for>
// Version: 1.1.5; Recent
</search for>

<replace>
// Version: 1.1.9; Recent
</replace>


<search for>
			$button_set['delete'] = array('text' => 31, 'image' => 'delete.gif', 'lang' => true, 'custom' => 'onclick="return confirm(\'' . $txt[154] . '?\');"', 'url' => $scripturl . '?action=deletemsg2;msg=' . $post['id'] . ';topic=' . $post['topic'] . ';recent;sesc=' . $context['session_id']);
</search for>

<replace>
			$button_set['delete'] = array('text' => 31, 'image' => 'delete.gif', 'lang' => true, 'custom' => 'onclick="return confirm(\'' . $txt[154] . '?\');"', 'url' => $scripturl . '?action=deletemsg;msg=' . $post['id'] . ';topic=' . $post['topic'] . ';recent;sesc=' . $context['session_id']);
</replace>