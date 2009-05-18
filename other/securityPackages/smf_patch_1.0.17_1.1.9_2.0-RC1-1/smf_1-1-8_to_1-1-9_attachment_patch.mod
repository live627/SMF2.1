<edit file>
$sourcedir/Display.php
</edit file>

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
$sourcedir/Profile.php
</edit file>

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
* Copyright 2006-2007 by:     Simple Machines LLC (http://www.simplemachines.org) *
</search for>

<replace>
* Copyright 2006-2009 by:     Simple Machines LLC (http://www.simplemachines.org) *
</replace>

<search for>
					'name' => getAttachmentFilename($name, false, true),
</search for>

<replace>
					'name' => $name,
</replace>



<edit file>
$sourcedir/Subs.php
</edit file>

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
	}
	// We can't get to the file.
</search for>

<replace>

		// Though not an exhaustive list, better safe than sorry.
		if (preg_match('~(iframe|\\<\\?php|\\<\\?[\s=]|\\<%[\s=]|html|eval|body|script)~', file_get_contents($destName)) === 1)
		{
			unlink($destName);
			return false;
		}
	}
	// We can't get to the file.
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


<edit file>
$sourcedir/Subs-Post.php
</edit file>

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