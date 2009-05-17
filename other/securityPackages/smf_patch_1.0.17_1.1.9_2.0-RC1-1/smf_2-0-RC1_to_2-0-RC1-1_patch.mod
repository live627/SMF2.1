<edit file>
$sourcedir/Subs.php
</edit file>
<search for>
* =============================================================================== *
* Software Version:           SMF 2.0 RC1                                         *
</search for>

<replace>
* =============================================================================== *
* Software Version:           SMF 2.0 RC1-1                                       *
</replace>


<search for>
// Get an attachment's encrypted filename.  If $new is true, won't check for file existence.
function getAttachmentFilename($filename, $attachment_id, $dir = null, $new = false)
</search for>

<replace>
// Get an attachment's encrypted filename.  If $new is true, won't check for file existence.
function getAttachmentFilename($filename, $attachment_id, $dir = null, $new = false, $file_hash = '')
{
	global $modSettings, $smcFunc;

	// Just make up a nice hash...
	if ($new)
		return sha1(md5($filename . time()) . mt_rand());

	// Grab the file hash if it wasn't added.
	if ($file_hash === '')
	{
		$request = $smcFunc['db_query']('', '
			SELECT file_hash
			FROM {db_prefix}attachments
			WHERE id_attach = {int:id_attach}',
			array(
				'id_attach' => $attachment_id,
		));

		if ($smcFunc['db_num_rows']($request) === 0)
			return false;

		list ($file_hash) = $smcFunc['db_fetch_row']($request);
		$smcFunc['db_free_result']($request);
	}

	// In case of files from the old system, do a legacy call.
	if (empty($file_hash))
		return getLegacyAttachmentFilename($filename, $attachment_id, $dir, $new);

	// Are we using multiple directories?
	if (!empty($modSettings['currentAttachmentUploadDir']))
	{
		if (!is_array($modSettings['attachmentUploadDir']))
			$modSettings['attachmentUploadDir'] = unserialize($modSettings['attachmentUploadDir']);
		$path = $modSettings['attachmentUploadDir'][$modSettings['attachmentUploadDir']];
	}
	else
		$path = $modSettings['attachmentUploadDir'];

	return $path . '/' . $attachment_id . '_' . $file_hash;
}

function getLegacyAttachmentFilename($filename, $attachment_id, $dir = null, $new = false)
</replace>


<edit file>
$sourcedir/Subs-Post.php
</edit file>
<search for>
* =============================================================================== *
* Software Version:           SMF 2.0 RC1                                         *
</search for>

<replace>
* =============================================================================== *
* Software Version:           SMF 2.0 RC1-1                                       *
</replace>


<search for>
	// Change breaks back to \n's.
	return preg_replace('~<br( /)?' . '>~', "\n", implode('', $parts));
</search for>

<replace>
	// Change breaks back to \n's.
	return preg_replace('~<br( /)?' . '>~', "\n", str_replace('&nbsp;', ' ', implode('', $parts)));
</replace>


<search for>
	// Remove special foreign characters from the filename.
	if (empty($modSettings['attachmentEncryptFilenames']))
		$attachmentOptions['name'] = getAttachmentFilename($attachmentOptions['name'], false, $id_folder, true);
</search for>

<replace>
	// Get the hash if no hash has been given yet.
	if (empty($attachmentOptions['file_hash']))
		$attachmentOptions['file_hash'] = getAttachmentFilename($attachmentOptions['name'], false, null, true);
</replace>


<search for>
		array(
			'id_folder' => 'int', 'id_msg' => 'int', 'filename' => 'string-255', 'fileext' => 'string-8',
</search for>

<replace>
		array(
			'id_folder' => 'int', 'id_msg' => 'int', 'filename' => 'string-255', 'file_hash' => 'string-40', 'fileext' => 'string-8',
</replace>


<search for>
		array(
			$id_folder, (int) $attachmentOptions['post'], $attachmentOptions['name'], $attachmentOptions['fileext'],
</search for>

<replace>
		array(
			$id_folder, (int) $attachmentOptions['post'], $attachmentOptions['name'], $attachmentOptions['file_hash'], $attachmentOptions['fileext'],
</replace>


<search for>

	$attachmentOptions['destination'] = $attach_dir . '/' . getAttachmentFilename(basename($attachmentOptions['name']), $attachmentOptions['id'], $id_folder, true);
</search for>

<replace>

	$attachmentOptions['destination'] = getAttachmentFilename(basename($attachmentOptions['name']), $attachmentOptions['id'], $id_folder, false, $attachmentOptions['file_hash']);
</replace>

<search for>

			// To the database we go!
			$smcFunc['db_insert']('',
</search for>

<replace>
			$thumb_file_hash = getAttachmentFilename($thumb_filename, false, null, true);

			// To the database we go!
			$smcFunc['db_insert']('',
</replace>



<search for>
				array(
					'id_folder' => 'int', 'id_msg' => 'int', 'attachment_type' => 'int', 'filename' => 'string-255', 'fileext' => 'string-8',
</search for>

<replace>
				array(
					'id_folder' => 'int', 'id_msg' => 'int', 'attachment_type' => 'int', 'filename' => 'string-255', 'file_hash' => 'string-40', 'fileext' => 'string-8',
</replace>


<search for>
				array(
					$id_folder, (int) $attachmentOptions['post'], 3, $thumb_filename, $attachmentOptions['fileext'],
</search for>

<replace>
				array(
					$id_folder, (int) $attachmentOptions['post'], 3, $thumb_filename, $thumb_file_hash, $attachmentOptions['fileext'],
</replace>


<search for>

				rename($attachmentOptions['destination'] . '_thumb', $attach_dir . '/' . getAttachmentFilename($thumb_filename, $attachmentOptions['thumb'], $id_folder, true));
</search for>

<replace>

				rename($attachmentOptions['destination'] . '_thumb', getAttachmentFilename($thumb_filename, $attachmentOptions['thumb'], $id_folder, false, $thumb_file_hash));
</replace>


<edit file>
$sourcedir/Subs-Members.php
</edit file>
<search for>
* =============================================================================== *
* Software Version:           SMF 2.0 RC1                                         *
</search for>

<replace>
* =============================================================================== *
* Software Version:           SMF 2.0 RC1-1                                       *
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
	if (isset($regOptions['theme_vars']) && array_intersect($regOptions['theme_vars'], $reservedVars) != array())
		fatal_lang_error('no_theme');

	// Some of these might be overwritten. (the lower ones that are in the arrays below.)
</replace>



<edit file>
$sourcedir/Subs-Graphics.php
</edit file>
<search for>
* =============================================================================== *
* Software Version:           SMF 2.0 RC1                                         *
</search for>

<replace>
* =============================================================================== *
* Software Version:           SMF 2.0 RC1-1                                       *
</replace>

<search for>
	$smcFunc['db_insert']('',
		'{db_prefix}attachments',
		array(
</search for>

<replace>
	$avatar_hash = empty($modSettings['custom_avatar_enabled']) ? getAttachmentFilename($destName, false, null, true) : '';
	$smcFunc['db_insert']('',
		'{db_prefix}attachments',
		array(
</replace>



<search for>
		array(
			'id_member' => 'int', 'attachment_type' => 'int', 'filename' => 'string-255', 'fileext' => 'string-8', 'size' => 'int',
</search for>

<replace>
		array(
			'id_member' => 'int', 'attachment_type' => 'int', 'filename' => 'string-255',  'file_hash' => 'string-255', 'fileext' => 'string-8', 'size' => 'int',
</replace>


<search for>
		array(
			$memID, (empty($modSettings['custom_avatar_enabled']) ? 0 : 1), $destName, $ext, 1,
</search for>

<replace>
		array(
			$memID, (empty($modSettings['custom_avatar_enabled']) ? 0 : 1), $destName, $avatar_hash, $ext, 1,
</replace>

<search for>
		}
		else
			$sizes = array(-1, -1, -1);
</search for>

<replace>

			// Though not an exhaustive list, better safe than sorry.
			if (preg_match('~(iframe|\\<\\?php|\\<\\?|\\<%|html|eval|body|script)~', $destName) === 1)
			{
				unlink($destName);
				return false;
			}
		}
		else
			$sizes = array(-1, -1, -1);
</replace>



<search for>
		// Remove the .tmp extension from the attachment.
		if (rename($destName . '.tmp', $destName))
		{
</search for>

<replace>
		// Walk the right path.
		if (!empty($modSettings['currentAttachmentUploadDir']))
		{
			if (!is_array($modSettings['attachmentUploadDir']))
				$modSettings['attachmentUploadDir'] = unserialize($modSettings['attachmentUploadDir']);
			$path = $modSettings['attachmentUploadDir'][$dir];
		}
		else
			$path = $modSettings['attachmentUploadDir'];

		// Remove the .tmp extension from the attachment.
		if (rename($destName . '.tmp', empty($avatar_hash) ? $destName : $path . '/' . $attachID . '_' . $avatar_hash))
		{
			$destName = empty($avatar_hash) ? $destName : $path . '/' . $attachID . '_' . $avatar_hash;
</replace>



<edit file>
$sourcedir/Security.php
</edit file>
<search for>
* =============================================================================== *
* Software Version:           SMF 2.0 RC1                                         *
</search for>

<replace>
* =============================================================================== *
* Software Version:           SMF 2.0 RC1-1                                       *
</replace>


<search for>
	
	if (isset($_GET['confirm']) && isset($_SESSION['confirm_' . $action]) && md5($_GET['confirm'] . $_SERVER['HTTP_USER_AGENT']) !== $_SESSION['confirm_' . $action])
</search for>

<replace>
	
	if (isset($_GET['confirm']) && isset($_SESSION['confirm_' . $action]) && md5($_GET['confirm'] . $_SERVER['HTTP_USER_AGENT']) == $_SESSION['confirm_' . $action])
</replace>


<search for>
		$token = md5(mt_rand() . session_id() . (string) microtime() . $modSettings['rand_seed']);
		$_SESSION['confirm_' . $action] = md5($token, $_SERVER['HTTP_USER_AGENT']);
</search for>

<replace>
		$token = md5(mt_rand() . session_id() . (string) microtime() . $modSettings['rand_seed']);
		$_SESSION['confirm_' . $action] = md5($token . $_SERVER['HTTP_USER_AGENT']);
</replace>


<edit file>
$sourcedir/QueryString.php
</edit file>
<search for>
* =============================================================================== *
* Software Version:           SMF 2.0 RC1                                         *
</search for>

<replace>
* =============================================================================== *
* Software Version:           SMF 2.0 RC1-1                                       *
</replace>


<search for>
	// Find the user's IP address. (but don't let it give you 'unknown'!)
	if (!empty($_SERVER['HTTP_X_FORWARDED_FOR']) && !empty($_SERVER['HTTP_CLIENT_IP']) && (preg_match('~^((0|10|172\.16|192\.168|255|127\.0)\.|unknown)~', $_SERVER['HTTP_CLIENT_IP']) == 0 || preg_match('~^((0|10|172\.16|192\.168|255|127\.0)\.|unknown)~', $_SERVER['REMOTE_ADDR']) != 0))
</search for>

<replace>
	// Find the user's IP address. (but don't let it give you 'unknown'!)
	if (!empty($_SERVER['HTTP_X_FORWARDED_FOR']) && !empty($_SERVER['HTTP_CLIENT_IP']) && (preg_match('~^((0|10|172\.(1[6-9]|2[0-9]|3[01])|192\.168|255|127)\.|unknown)~', $_SERVER['HTTP_CLIENT_IP']) == 0 || preg_match('~^((0|10|172\.(1[6-9]|2[0-9]|3[01])|192\.168|255|127)\.|unknown)~', $_SERVER['REMOTE_ADDR']) != 0))
</replace>


<search for>
		// We have both forwarded for AND client IP... check the first forwarded for as the block - only switch if it's better that way.
		if (strtok($_SERVER['HTTP_X_FORWARDED_FOR'], '.') != strtok($_SERVER['HTTP_CLIENT_IP'], '.') && '.' . strtok($_SERVER['HTTP_X_FORWARDED_FOR'], '.') == strrchr($_SERVER['HTTP_CLIENT_IP'], '.') && (preg_match('~^((0|10|172\.16|192\.168|255|127\.0)\.|unknown)~', $_SERVER['HTTP_X_FORWARDED_FOR']) == 0 || preg_match('~^((0|10|172\.16|192\.168|255|127\.0)\.|unknown)~', $_SERVER['REMOTE_ADDR']) != 0))
</search for>

<replace>
		// We have both forwarded for AND client IP... check the first forwarded for as the block - only switch if it's better that way.
		if (strtok($_SERVER['HTTP_X_FORWARDED_FOR'], '.') != strtok($_SERVER['HTTP_CLIENT_IP'], '.') && '.' . strtok($_SERVER['HTTP_X_FORWARDED_FOR'], '.') == strrchr($_SERVER['HTTP_CLIENT_IP'], '.') && (preg_match('~^((0|10|172\.(1[6-9]|2[0-9]|3[01])|192\.168|255|127)\.|unknown)~', $_SERVER['HTTP_X_FORWARDED_FOR']) == 0 || preg_match('~^((0|10|172\.(1[6-9]|2[0-9]|3[01])|192\.168|255|127)\.|unknown)~', $_SERVER['REMOTE_ADDR']) != 0))
</replace>


<search for>
			$_SERVER['BAN_CHECK_IP'] = $_SERVER['HTTP_CLIENT_IP'];
	}
	if (!empty($_SERVER['HTTP_CLIENT_IP']) && (preg_match('~^((0|10|172\.16|192\.168|255|127\.0)\.|unknown)~', $_SERVER['HTTP_CLIENT_IP']) == 0 || preg_match('~^((0|10|172\.16|192\.168|255|127\.0)\.|unknown)~', $_SERVER['REMOTE_ADDR']) != 0))
</search for>

<replace>
			$_SERVER['BAN_CHECK_IP'] = $_SERVER['HTTP_CLIENT_IP'];
	}
	if (!empty($_SERVER['HTTP_CLIENT_IP']) && (preg_match('~^((0|10|172\.(1[6-9]|2[0-9]|3[01])|192\.168|255|127)\.|unknown)~', $_SERVER['HTTP_CLIENT_IP']) == 0 || preg_match('~^((0|10|172\.(1[6-9]|2[0-9]|3[01])|192\.168|255|127)\.|unknown)~', $_SERVER['REMOTE_ADDR']) != 0))
</replace>


<search for>
				// Make sure it's in a valid range...
				if (preg_match('~^((0|10|172\.16|192\.168|255|127\.0)\.|unknown)~', $ip) != 0 && preg_match('~^((0|10|172\.16|192\.168|255|127\.0)\.|unknown)~', $_SERVER['REMOTE_ADDR']) == 0)
</search for>

<replace>
				// Make sure it's in a valid range...
				if (preg_match('~^((0|10|172\.(1[6-9]|2[0-9]|3[01])|192\.168|255|127)\.|unknown)~', $ip) != 0 && preg_match('~^((0|10|172\.(1[6-9]|2[0-9]|3[01])|192\.168|255|127)\.|unknown)~', $_SERVER['REMOTE_ADDR']) == 0)
</replace>


<search for>
		// Otherwise just use the only one.
		elseif (preg_match('~^((0|10|172\.16|192\.168|255|127\.0)\.|unknown)~', $_SERVER['HTTP_X_FORWARDED_FOR']) == 0 || preg_match('~^((0|10|172\.16|192\.168|255|127\.0)\.|unknown)~', $_SERVER['REMOTE_ADDR']) != 0)
</search for>

<replace>
		// Otherwise just use the only one.
		elseif (preg_match('~^((0|10|172\.(1[6-9]|2[0-9]|3[01])|192\.168|255|127)\.|unknown)~', $_SERVER['HTTP_X_FORWARDED_FOR']) == 0 || preg_match('~^((0|10|172\.(1[6-9]|2[0-9]|3[01])|192\.168|255|127)\.|unknown)~', $_SERVER['REMOTE_ADDR']) != 0)
</replace>


<edit file>
$sourcedir/Profile-View.php
</edit file>
<search for>
* =============================================================================== *
* Software Version:           SMF 2.0 RC1                                         *
</search for>

<replace>
* =============================================================================== *
* Software Version:           SMF 2.0 RC1-1                                       *
</replace>


<search for>
		// Should we show a custom message?
		$context['activate_message'] = isset($txt['account_activate_method_' . $context['member']['is_activated'] % 10]) ? $txt['account_activate_method_' . $context['member']['is_activated']] : $txt['account_not_activated'];
</search for>

<replace>
		// Should we show a custom message?
		$context['activate_message'] = isset($txt['account_activate_method_' . $context['member']['is_activated'] % 10]) ? $txt['account_activate_method_' . $context['member']['is_activated'] % 10] : $txt['account_not_activated'];
</replace>


<edit file>
$sourcedir/Profile-Modify.php
</edit file>
<search for>
* =============================================================================== *
* Software Version:           SMF 2.0 RC1                                         *
</search for>

<replace>
* =============================================================================== *
* Software Version:           SMF 2.0 RC1-1                                       *
</replace>

<search for>
	// Don't allow any overriding of custom fields with default or non-default options.
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
	if ((isset($_POST['options']) && array_intersect($_POST['options'], $reservedVars) != array()) || (isset($_POST['default_options']) && array_intersect($_POST['default_options'], $reservedVars) != array()))
		fatal_lang_error('no_access');

	// Don't allow any overriding of custom fields with default or non-default options.
</replace>


<search for>
				$extensions = array(
					'1' => 'gif',
					'2' => 'jpg',
</search for>

<replace>
				// Though not an exhaustive list, better safe than sorry.
				$fp = fopen($_FILES['attachment']['tmp_name'], 'rb');
				if (!$fp)
					fatal_lang_error('attach_timeout');

				// Now try to find an infection.
				while (!feof($fp))
				{
					if (preg_match('~(iframe|\\<\\?php|\\<\\?[\s=]|\\<%[\s=]|html|eval|body|script)~', fgets($fp, 4096)) === 1)
					{
						if (file_exists($uploadDir . '/avatar_tmp_' . $memID))
							@unlink($uploadDir . '/avatar_tmp_' . $memID);

						fatal_lang_error('attach_timeout');
					}
				}
				fclose($fp);

				$extensions = array(
					'1' => 'gif',
					'2' => 'jpg',
</replace>



<search for>
				$mime_type = 'image/' . ($extension == 'jpg' ? 'jpeg' : $extension);
				$destName = 'avatar_' . $memID . '.' . $extension;
</search for>

<replace>
				$mime_type = 'image/' . ($extension === 'jpg' ? 'jpeg' : ($extension === 'bmp' ? 'x-ms-bmp' : $extension));
				$destName = 'avatar_' . $memID . '_' . time() . '.' . $extension;
</replace>

<search for>

				// Remove previous attachments this member might have had.
				removeAttachments(array('id_member' => $memID));
</search for>

<replace>
				$file_hash = empty($modSettings['custom_avatar_enabled']) ? getAttachmentFilename($destName, false, null, true) : '';

				// Remove previous attachments this member might have had.
				removeAttachments(array('id_member' => $memID));
</replace>



<search for>
				// Remove previous attachments this member might have had.
				removeAttachments(array('id_member' => $memID));

				if (!rename($_FILES['attachment']['tmp_name'], $uploadDir . '/' . $destName))
					fatal_lang_error('attach_timeout', 'critical');

</search for>

<replace>
				// Remove previous attachments this member might have had.
				removeAttachments(array('id_member' => $memID));

</replace>



<search for>
					array(
						'id_member' => 'int', 'attachment_type' => 'int', 'filename' => 'string', 'fileext' => 'string', 'size' => 'int',
</search for>

<replace>
					array(
						'id_member' => 'int', 'attachment_type' => 'int', 'filename' => 'string', 'file_hash' => 'string', 'fileext' => 'string', 'size' => 'int',
</replace>


<search for>
					array(
						$memID, (empty($modSettings['custom_avatar_enabled']) ? 0 : 1), $destName, $extension, filesize($uploadDir . '/' . $destName),
</search for>

<replace>
					array(
						$memID, (empty($modSettings['custom_avatar_enabled']) ? 0 : 1), $destName, $file_hash, $extension, filesize($_FILES['attachment']['tmp_name']),
</replace>

<search for>
				// Attempt to chmod it.
				@chmod($uploadDir . '/' . $destName, 0644);
			}
</search for>

<replace>
				$destinationPath = $uploadDir . '/' . (empty($file_hash) ? $destName : $cur_profile['id_attach'] . '_' . $file_hash);
				if (!rename($_FILES['attachment']['tmp_name'], $destinationPath))
				{
					removeAttachments(array('id_member' => $memID));
					fatal_lang_error('attach_timeout', 'critical');
				}

				// Attempt to chmod it.
				@chmod($uploadDir . '/' . $destinationPath, 0644);
			}
</replace>

<edit file>
$sourcedir/Post.php
</edit file>
<search for>
* =============================================================================== *
* Software Version:           SMF 2.0 RC1                                         *
</search for>

<replace>
* =============================================================================== *
* Software Version:           SMF 2.0 RC1-1                                       *
</replace>


<search for>
				$context['current_attachments'][] = array(
					'name' => getAttachmentFilename($name, false, null, true),
</search for>

<replace>
				$context['current_attachments'][] = array(
					'name' => $name,
</replace>


<edit file>
$sourcedir/PackageGet.php
</edit file>
<search for>
* =============================================================================== *
* Software Version:           SMF 2.0 RC1                                         *
</search for>

<replace>
* =============================================================================== *
* Software Version:           SMF 2.0 RC1-1                                       *
</replace>


<search for>
		
			$context['page_title'] = $txt['smf183'];
</search for>

<replace>
		
			$context['page_title'] = $txt['package_servers'];
</replace>


<search for>
			$context['confirm_message'] = sprintf($txt['package_confirm_view_package_content'], htmlspecialchars($_GET['absolute']));
			$context['proceed_href'] = $scripturl . '?action=packageget;sa=browse;absolute=' . urlencode($_GET['absolute']) . ';confirm=' . $token;
</search for>

<replace>
			$context['confirm_message'] = sprintf($txt['package_confirm_view_package_content'], htmlspecialchars($_GET['absolute']));
			$context['proceed_href'] = $scripturl . '?action=admin;area=packages;get;sa=browse;absolute=' . urlencode($_GET['absolute']) . ';confirm=' . $token;
</replace>


<search for>
	if ($listing->exists('default-author'))
	{
		$default_author = $listing->fetch('default-author');
</search for>

<replace>
	if ($listing->exists('default-author'))
	{
		$default_author = $smcFunc['htmlspecialchars']($listing->fetch('default-author'));
</replace>


<search for>
		if ($listing->exists('default-website/@title'))
			$default_title = $listing->fetch('default-website/@title');
</search for>

<replace>
		if ($listing->exists('default-website/@title'))
			$default_title = $smcFunc['htmlspecialchars']($listing->fetch('default-website/@title'));
</replace>


<search for>
			if (in_array($package['type'], array('title', 'text')))
				$context['package_list'][$packageSection][$package['type']] = $thisPackage->fetch('.');
</search for>

<replace>
			if (in_array($package['type'], array('title', 'text')))
				$context['package_list'][$packageSection][$package['type']] = $smcFunc['htmlspecialchars']($thisPackage->fetch('.'));
</replace>


<search for>
			elseif (in_array($package['type'], array('heading', 'rule')))
				$package['name'] = $thisPackage->fetch('.');
</search for>

<replace>
			elseif (in_array($package['type'], array('heading', 'rule')))
				$package['name'] = $smcFunc['htmlspecialchars']($thisPackage->fetch('.'));
</replace>


<search for>

				$package['name'] = $thisPackage->fetch('.');
</search for>

<replace>

				$package['name'] = $smcFunc['htmlspecialchars']($thisPackage->fetch('.'));
</replace>

<search for>

				$package['is_installed'] = isset($installed_mods[$package['id']]);
				$package['is_current'] = $package['is_installed'] && ($installed_mods[$package['id']] == $package['version']);
</search for>

<replace>
				else
					$package['description'] = parse_bbc(preg_replace('~\[[/]?html\]~i', '', $smcFunc['htmlspecialchars']($package['description'])));				

				$package['is_installed'] = isset($installed_mods[$package['id']]);
				$package['is_current'] = $package['is_installed'] && ($installed_mods[$package['id']] == $package['version']);
</replace>


<search for>
				$package['link'] = '<a href="' . $package['href'] . '">' . $package['name'] . '</a>';
				$package['download']['href'] = $scripturl . '?action=admin;area=packages;get;sa=download' . $server_att . ';package=' . $current_url . $package['filename'] . ($package['download_conflict'] ? ';conflict' : '') . ';' . $context['session_var'] . '=' . $context['session_id'];
				$package['download']['link'] = '<a href="' . $package['download']['href'] . '">' . $package['name'] . '</a>';
</search for>

<replace>
				$package['name'] = $smcFunc['htmlspecialchars']($package['name']);
				$package['link'] = '<a href="' . $package['href'] . '">' . $package['name'] . '</a>';
				$package['download']['href'] = $scripturl . '?action=admin;area=packages;get;sa=download' . $server_att . ';package=' . $current_url . $package['filename'] . ($package['download_conflict'] ? ';conflict' : '') . ';' . $context['session_var'] . '=' . $context['session_id'];
				$package['download']['link'] = '<a href="' . $package['download']['href'] . '">' . $package['name'] . '</a>';
</replace>



<search for>
					if ($thisPackage->exists('author') && $thisPackage->fetch('author') != '')
						$package['author']['name'] = $thisPackage->fetch('author');
</search for>

<replace>
					if ($thisPackage->exists('author') && $thisPackage->fetch('author') != '')
						$package['author']['name'] = $smcFunc['htmlspecialchars']($thisPackage->fetch('author'));
</replace>


<search for>
					if ($thisPackage->exists('website') && $thisPackage->exists('website/@title'))
						$package['author']['website']['name'] = $thisPackage->fetch('website/@title');
</search for>

<replace>
					if ($thisPackage->exists('website') && $thisPackage->exists('website/@title'))
						$package['author']['website']['name'] = $smcFunc['htmlspecialchars']($thisPackage->fetch('website/@title'));
</replace>


<search for>
					elseif ($thisPackage->exists('website'))
						$package['author']['website']['name'] = $thisPackage->fetch('website');
</search for>

<replace>
					elseif ($thisPackage->exists('website'))
						$package['author']['website']['name'] = $smcFunc['htmlspecialchars']($thisPackage->fetch('website'));
</replace>


<edit file>
$sourcedir/ManageAttachments.php
</edit file>
<search for>
* =============================================================================== *
* Software Version:           SMF 2.0 RC1                                         *
</search for>

<replace>
* =============================================================================== *
* Software Version:           SMF 2.0 RC1-1                                       *
</replace>



<search for>
			array('text', 'attachmentExtensions', 40),
			array('check', 'attachmentEncryptFilenames'),
</search for>

<replace>
			array('text', 'attachmentExtensions', 40),
</replace>



<search for>
				{string:blank_text} AS id_msg, IFNULL(mem.real_name, {string:not_applicable_text}) AS poster_name,
				mem.last_login AS poster_time, 0 AS id_topic, a.id_member, a.id_attach, a.filename, a.attachment_type,
</search for>

<replace>
				{string:blank_text} AS id_msg, IFNULL(mem.real_name, {string:not_applicable_text}) AS poster_name,
				mem.last_login AS poster_time, 0 AS id_topic, a.id_member, a.id_attach, a.filename, a.file_hash, a.attachment_type,
</replace>


<search for>
				m.id_msg, IFNULL(mem.real_name, m.poster_name) AS poster_name, m.poster_time, m.id_topic, m.id_member,
				a.id_attach, a.filename, a.attachment_type, a.size, a.width, a.height, a.downloads, mf.subject, t.id_board
</search for>

<replace>
				m.id_msg, IFNULL(mem.real_name, m.poster_name) AS poster_name, m.poster_time, m.id_topic, m.id_member,
				a.id_attach, a.filename, a.file_hash, a.attachment_type, a.size, a.width, a.height, a.downloads, mf.subject, t.id_board
</replace>


<search for>
	$request = $smcFunc['db_query']('', '
		SELECT id_attach, id_folder, id_member, filename
</search for>

<replace>
	$request = $smcFunc['db_query']('', '
		SELECT id_attach, id_folder, id_member, filename, file_hash
</replace>


<search for>
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$filename = getAttachmentFilename($row['filename'], $row['id_attach'], $row['id_folder']);
</search for>

<replace>
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$filename = getAttachmentFilename($row['filename'], $row['id_attach'], $row['id_folder'], false, $row['file_hash']);
</replace>


<search for>
			a.id_folder, a.filename, a.attachment_type, a.id_attach, a.id_member' . ($query_type == 'messages' ? ', m.id_msg' : ', a.id_msg') . ',
			thumb.id_folder AS thumb_folder, IFNULL(thumb.id_attach, 0) AS id_thumb, thumb.filename AS thumb_filename, thumb_parent.id_attach AS id_parent
</search for>

<replace>
			a.id_folder, a.filename, a.file_hash, a.attachment_type, a.id_attach, a.id_member' . ($query_type == 'messages' ? ', m.id_msg' : ', a.id_msg') . ',
			thumb.id_folder AS thumb_folder, IFNULL(thumb.id_attach, 0) AS id_thumb, thumb.filename AS thumb_filename, thumb.file_hash as thumb_file_hash, thumb_parent.id_attach AS id_parent
</replace>


<search for>
		else
		{
			$filename = getAttachmentFilename($row['filename'], $row['id_attach'], $row['id_folder']);
</search for>

<replace>
		else
		{
			$filename = getAttachmentFilename($row['filename'], $row['id_attach'], $row['id_folder'], false, $row['file_hash']);
</replace>


<search for>
			if (!empty($row['id_thumb']) && $autoThumbRemoval)
			{
				$thumb_filename = getAttachmentFilename($row['thumb_filename'], $row['id_thumb'], $row['thumb_folder']);
</search for>

<replace>
			if (!empty($row['id_thumb']) && $autoThumbRemoval)
			{
				$thumb_filename = getAttachmentFilename($row['thumb_filename'], $row['id_thumb'], $row['thumb_folder'], false, $row['file_hash']);
</replace>


<search for>
			$result = $smcFunc['db_query']('', '
				SELECT thumb.id_attach, thumb.id_folder, thumb.filename
</search for>

<replace>
			$result = $smcFunc['db_query']('', '
				SELECT thumb.id_attach, thumb.id_folder, thumb.filename, thumb.file_hash
</replace>


<search for>
				if ($fix_errors && in_array('missing_thumbnail_parent', $to_fix))
				{
					$filename = getAttachmentFilename($row['filename'], $row['id_attach'], $row['id_folder']);
</search for>

<replace>
				if ($fix_errors && in_array('missing_thumbnail_parent', $to_fix))
				{
					$filename = getAttachmentFilename($row['filename'], $row['id_attach'], $row['id_folder'], false, $row['file_hash']);
</replace>


<search for>
			$result = $smcFunc['db_query']('', '
				SELECT id_attach, id_folder, filename, size, attachment_type
</search for>

<replace>
			$result = $smcFunc['db_query']('', '
				SELECT id_attach, id_folder, filename, file_hash, size, attachment_type
</replace>


<search for>
				else
					$filename = getAttachmentFilename($row['filename'], $row['id_attach'], $row['id_folder']);
</search for>

<replace>
				else
					$filename = getAttachmentFilename($row['filename'], $row['id_attach'], $row['id_folder'], false, $row['file_hash']);
</replace>


<search for>
						// Get the attachment name with out the folder.
						$attachment_name = getAttachmentFilename($row['filename'], $row['id_attach'], null, true);
</search for>

<replace>
						// Get the attachment name with out the folder.
						$attachment_name = !empty($row['file_hash']) ? $row['id_attach'] . '_' . $row['file_hash'] : getLegacyAttachmentFilename($row['filename'], $row['id_attach'], null, true);
</replace>


<search for>
			$result = $smcFunc['db_query']('', '
				SELECT a.id_attach, a.id_folder, a.filename, a.attachment_type
</search for>

<replace>
			$result = $smcFunc['db_query']('', '
				SELECT a.id_attach, a.id_folder, a.filename, a.file_hash, a.attachment_type
</replace>


<search for>
					else
						$filename = getAttachmentFilename($row['filename'], $row['id_attach'], $row['id_folder']);
</search for>

<replace>
					else
						$filename = getAttachmentFilename($row['filename'], $row['id_attach'], $row['id_folder'], false, $row['file_hash']);
</replace>


<search for>
			$result = $smcFunc['db_query']('', '
				SELECT a.id_attach, a.id_folder, a.filename
</search for>

<replace>
			$result = $smcFunc['db_query']('', '
				SELECT a.id_attach, a.id_folder, a.filename, a.file_hash
</replace>


<search for>
				if ($fix_errors && in_array('attachment_no_msg', $to_fix))
				{
					$filename = getAttachmentFilename($row['filename'], $row['id_attach'], $row['id_folder']);
</search for>

<replace>
				if ($fix_errors && in_array('attachment_no_msg', $to_fix))
				{
					$filename = getAttachmentFilename($row['filename'], $row['id_attach'], $row['id_folder'], false, $row['file_hash']);
</replace>


<edit file>
$sourcedir/Load.php
</edit file>
<search for>
* =============================================================================== *
* Software Version:           SMF 2.0 RC1                                         *
</search for>

<replace>
* =============================================================================== *
* Software Version:           SMF 2.0 RC1-1                                       *
</replace>

<search for>
			// If this is the theme_dir of the default theme, store it.
</search for>

<replace>
			// There are just things we shouldn't be able to change as members.
			if ($row['id_member'] != 0 && in_array($row['variable'], array('actual_theme_url', 'actual_images_url', 'base_theme_dir', 'base_theme_url', 'default_images_url', 'default_theme_dir', 'default_theme_url', 'default_template', 'images_url', 'number_recent_posts', 'smiley_sets_default', 'theme_dir', 'theme_id', 'theme_layers', 'theme_templates', 'theme_url')))
				continue;

			// If this is the theme_dir of the default theme, store it.
</replace>



<edit file>
$boarddir/index.php
</edit file>
<search for>
* =============================================================================== *
* Software Version:           SMF 2.0 RC1                                         *
</search for>

<replace>
* =============================================================================== *
* Software Version:           SMF 2.0 RC1-1                                       *
</replace>


<search for>

$forum_version = 'SMF 2.0 RC1';
</search for>

<replace>

$forum_version = 'SMF 2.0 RC1-1';
</replace>


<search for>
	// If guest access is off, a guest can only do one of the very few following actions.
	elseif (empty($modSettings['allow_guestAccess']) && $user_info['is_guest'] && (!isset($_REQUEST['action']) || !in_array($_REQUEST['action'], array('coppa', 'login', 'login2', 'register', 'register2', 'reminder', 'activate', 'help', 'smstats', '.xml', 'mailq', 'verificationcode', 'openidreturn',))))
</search for>

<replace>
	// If guest access is off, a guest can only do one of the very few following actions.
	elseif (empty($modSettings['allow_guestAccess']) && $user_info['is_guest'] && (!isset($_REQUEST['action']) || !in_array($_REQUEST['action'], array('coppa', 'login', 'login2', 'register', 'register2', 'reminder', 'activate', 'help', 'smstats', 'mailq', 'verificationcode', 'openidreturn',))))
</replace>


<edit file>
$sourcedir/Display.php
</edit file>
<search for>
* =============================================================================== *
* Software Version:           SMF 2.0 RC1                                         *
</search for>

<replace>
* =============================================================================== *
* Software Version:           SMF 2.0 RC1-1                                       *
</replace>


<search for>
				SELECT
					a.id_attach, a.id_folder, a.id_msg, a.filename, IFNULL(a.size, 0) AS filesize, a.downloads, a.approved,
</search for>

<replace>
				SELECT
					a.id_attach, a.id_folder, a.id_msg, a.filename, a.file_hash, IFNULL(a.size, 0) AS filesize, a.downloads, a.approved,
</replace>


<search for>
		$request = $smcFunc['db_query']('', '
			SELECT id_folder, filename, fileext, id_attach, attachment_type, mime_type, approved
</search for>

<replace>
		$request = $smcFunc['db_query']('', '
			SELECT id_folder, filename, file_hash, fileext, id_attach, attachment_type, mime_type, approved
</replace>


<search for>
		$request = $smcFunc['db_query']('', '
			SELECT a.id_folder, a.filename, a.fileext, a.id_attach, a.attachment_type, a.mime_type, a.approved
</search for>

<replace>
		$request = $smcFunc['db_query']('', '
			SELECT a.id_folder, a.filename, a.file_hash, a.fileext, a.id_attach, a.attachment_type, a.mime_type, a.approved
</replace>


<search for>
		fatal_lang_error('no_access', false);
	list ($id_folder, $real_filename, $file_ext, $id_attach, $attachment_type, $mime_type, $is_approved) = $smcFunc['db_fetch_row']($request);
</search for>

<replace>
		fatal_lang_error('no_access', false);
	list ($id_folder, $real_filename, $file_hash, $file_ext, $id_attach, $attachment_type, $mime_type, $is_approved) = $smcFunc['db_fetch_row']($request);
</replace>


<search for>

	$filename = getAttachmentFilename($real_filename, $_REQUEST['attach'], $id_folder);
</search for>

<replace>

	$filename = getAttachmentFilename($real_filename, $_REQUEST['attach'], $id_folder, false, $file_hash);
</replace>


<search for>
	// Does this have a mime type?
	if ($mime_type && (isset($_REQUEST['image']) || !in_array($file_ext, array('jpg', 'gif', 'jpeg', 'bmp', 'png', 'psd', 'tiff', 'iff'))))
		header('Content-Type: ' . $mime_type);
</search for>

<replace>
	// IE 6 just doesn't play nice. As dirty as this seems, it works.
	if ($context['browser']['is_ie6'] && isset($_REQUEST['image']))
		unset($_REQUEST['image']);
	// Does this have a mime type?
	elseif ($mime_type && (isset($_REQUEST['image']) || !in_array($file_ext, array('jpg', 'gif', 'jpeg', 'x-ms-bmp', 'png', 'psd', 'tiff', 'iff'))))
		header('Content-Type: ' . strtr($mime_type, array('image/bmp' => 'image/x-ms-bmp')));
</replace>


<search for>

						// Add this beauty to the database.
						$smcFunc['db_insert']('',
</search for>

<replace>
						$thumb_hash = getAttachmentFilename($thumb_filename, false, null, true);

						// Add this beauty to the database.
						$smcFunc['db_insert']('',
</replace>



<search for>
							array('id_folder' => 'int', 'id_msg' => 'int', 'attachment_type' => 'int', 'filename' => 'string', 'size' => 'int', 'width' => 'int', 'height' => 'int'),
							array($id_folder_thumb, $id_msg, 3, $thumb_filename, (int) $thumb_size, (int) $attachment['thumb_width'], (int) $attachment['thumb_height']),
</search for>

<replace>
	// Does this have a mime type?
							array('id_folder' => 'int', 'id_msg' => 'int', 'attachment_type' => 'int', 'filename' => 'string', 'file_hash' => 'string', 'size' => 'int', 'width' => 'int', 'height' => 'int'),
							array($id_folder_thumb, $id_msg, 3, $thumb_filename, $thumb_hash, (int) $thumb_size, (int) $attachment['thumb_width'], (int) $attachment['thumb_height']),
</replace>


<search for>
							$thumb_realname = getAttachmentFilename($thumb_filename, $attachment['id_thumb'], $id_folder_thumb, true);
							rename($filename . '_thumb', $path . '/' . $thumb_realname);
</search for>

<replace>
	// Does this have a mime type?
							$thumb_realname = getAttachmentFilename($thumb_filename, $attachment['id_thumb'], $id_folder_thumb, false, $thumb_hash);
							rename($filename . '_thumb', $thumb_realname);
</replace>