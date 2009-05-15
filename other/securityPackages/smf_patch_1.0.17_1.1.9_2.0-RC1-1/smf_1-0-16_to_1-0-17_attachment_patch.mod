<edit file>
$sourcedir/Display.php
</edit file>

<search for>
				SELECT ID_ATTACH, ID_MSG, filename, IFNULL(size, 0) AS filesize, downloads
</search for>

<replace>
				SELECT ID_ATTACH, ID_MSG, filename, file_hash, IFNULL(size, 0) AS filesize, downloads
</replace>


<search for>
			SELECT filename, ID_ATTACH
</search for>

<replace>
			SELECT filename, ID_ATTACH, file_hash
</replace>


<search for>
			SELECT a.filename, a.ID_ATTACH
</search for>

<replace>
			SELECT a.filename, a.ID_ATTACH, a.file_hash
</replace>


<search for>
	list ($real_filename, $ID_ATTACH) = mysql_fetch_row($request);
</search for>

<replace>
	list ($real_filename, $ID_ATTACH, $file_hash) = mysql_fetch_row($request);
</replace>


<search for>
	$filename = getAttachmentFilename($real_filename, $_REQUEST['id']);
</search for>

<replace>
	$filename = getAttachmentFilename($real_filename, $_REQUEST['id'], false, $file_hash);
</replace>


<search for>
			$filename = getAttachmentFilename($attachment['filename'], $attachment['ID_ATTACH']);
</search for>

<replace>
			$filename = getAttachmentFilename($attachment['filename'], $attachment['ID_ATTACH'], false, $attachment['file_hash']);
</replace>



<edit file>
$sourcedir/ManageAttachments.php
</edit file>

<search for>
* Software Version:           SMF 1.0.12                                          *
</search for>

<replace>
* Software Version:           SMF 1.0.17                                          *
</replace>


<search for>
* Copyright 2006 by:          Simple Machines LLC (http://www.simplemachines.org) *
</search for>

<replace>
* Copyright 2006-2009 by:     Simple Machines LLC (http://www.simplemachines.org) *
</replace>



<search for>
		SELECT a.filename, a.ID_ATTACH
</search for>

<replace>
		SELECT a.filename, a.ID_ATTACH, file_hash
</replace>


<search for>
		// Figure out the "encrypted" filename and unlink it ;).
		@unlink(getAttachmentFilename($row['filename'], $row['ID_ATTACH']));
</search for>

<replace>
		// Figure out the "encrypted" filename and unlink it ;).
		@unlink(getAttachmentFilename($row['filename'], $row['ID_ATTACH'], false, $row['file_hash']));
</replace>



<search for>
		SELECT ID_ATTACH, ID_MSG, ID_MEMBER, filename, IFNULL(size, 0) AS size, downloads
</search for>

<replace>
		SELECT ID_ATTACH, ID_MSG, ID_MEMBER, filename, file_hash, IFNULL(size, 0) AS size, downloads
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



<edit file>
$sourcedir/Profile.php
</edit file>

<search for>
				db_query("
					INSERT INTO {$db_prefix}attachments
						(ID_MEMBER, filename, size)
					VALUES ($memID, '$destName', " . filesize($_FILES['attachment']['tmp_name']) . ")", __FILE__, __LINE__);
				$attachID = db_insert_id();
				$destName = $modSettings['attachmentUploadDir'] . '/' . $destName;

				if (!move_uploaded_file($_FILES['attachment']['tmp_name'], $destName))
					fatal_lang_error('smf124');

				// Attempt to chmod it.
				@chmod($destName, 0644);
</search for>

<replace>
				$file_hash = empty($modSettings['custom_avatar_enabled']) ? getAttachmentFilename($destName, false, true) : '';

				db_query("
					INSERT INTO {$db_prefix}attachments
						(ID_MEMBER, filename, file_hash, size)
					VALUES ($memID, '$destName', '" . (empty($file_hash))  ? "" : "$file_hash") . "', " . filesize($_FILES['attachment']['tmp_name']) . ")", __FILE__, __LINE__);
				$attachID = db_insert_id();

				$destName = $modSettings['attachmentUploadDir'] . '/' . (empty($file_hash) ? $destName : $attachID . '_' . $file_hash);

				if (!move_uploaded_file($_FILES['attachment']['tmp_name'], $destName))
				{
					removeAttachments('a.ID_MEMBER = ' . $memID);
					fatal_lang_error('smf124');
				}

				// Attempt to chmod it.
				@chmod($destName, 0644);
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
* Software Version:           SMF 1.0.10                                          *
</search for>

<replace>
* Software Version:           SMF 1.0.17                                          *
</replace>


<search for>
* Copyright 2006 by:          Simple Machines LLC (http://www.simplemachines.org) *
</search for>

<replace>
* Copyright 2006-2009 by:     Simple Machines LLC (http://www.simplemachines.org) *
</replace>



<search for>
	db_query("
		INSERT INTO {$db_prefix}attachments
			(ID_MEMBER, filename, size)
		VALUES ($memID, '$destName', 1)", __FILE__, __LINE__);
</search for>

<replace>

	$avatar_hash = empty($modSettings['custom_avatar_enabled']) ? getAttachmentFilename($destName, false, true) : '';

	db_query("
		INSERT INTO {$db_prefix}attachments
			(ID_MEMBER, filename, file_hash, size)
		VALUES ($memID, '$destName', '" . (empty($avatar_hash) ? "" : "$avatar_hash") . "', 1)", __FILE__, __LINE__);
</replace>



<search for>
		if (rename($destName . '.tmp', $destName))
		{
			// Write filesize in the database.
			db_query("
				UPDATE {$db_prefix}attachments
				SET size = " . filesize($destName) . "
</search for>

<replace>
		if (rename($destName . '.tmp', empty($avatar_hash) ? $destName : $modSettings['attachmentUploadDir'] . '/' . $attachID . '_' . $avatar_hash))
		{
			// Write filesize in the database.
			db_query("
				UPDATE {$db_prefix}attachments
				SET size = " . filesize(empty($avatar_hash) ? $destName : $modSettings['attachmentUploadDir'] . '/' . $attachID . '_' . $avatar_hash) . "
</replace>



<edit file>
$sourcedir/Post.php
</edit file>

<search for>
* Software Version:           SMF 1.0.13                                          *
</search for>

<replace>
* Software Version:           SMF 1.0.17                                          *
</replace>


<search for>
* Copyright 2006 by:          Simple Machines LLC (http://www.simplemachines.org) *
</search for>

<replace>
* Copyright 2006-2009 by:     Simple Machines LLC (http://www.simplemachines.org) *
</replace>


<search for>
			// Remove special foreign characters from the filename.
			if (empty($modSettings['attachmentEncryptFilenames']))
				$_FILES['attachment']['name'][$n] = getAttachmentFilename($_FILES['attachment']['name'][$n], false, true);
</search for>

<replace>
			// Remove special foreign characters from the filename.
			if (empty($_FILES['attachment']['file_hash'][$n]))
				$_FILES['attachment']['file_hash'][$n] = getAttachmentFilename($_FILES['attachment']['name'][$n], false, true);
</replace>


<search for>
			db_query("
				INSERT INTO {$db_prefix}attachments
					(" . (!empty($_REQUEST['msg']) ? 'ID_MSG, ' : '') . "filename, size)
				VALUES (" . (!empty($_REQUEST['msg']) ? (int) $_REQUEST['msg'] . ', ' : '') . "'" . $_FILES['attachment']['name'][$n] . "', " . $_FILES['attachment']['size'][$n] . ')', __FILE__, __LINE__);
			$attachID = db_insert_id();
			$attachIDs[] = $attachID;

			$destName = $modSettings['attachmentUploadDir'] . '/' . getAttachmentFilename($destName, $attachID, true);
</search for>

<replace>
			db_query("
				INSERT INTO {$db_prefix}attachments
					(" . (!empty($_REQUEST['msg']) ? 'ID_MSG, ' : '') . "filename, file_hash, size)
				VALUES (" . (!empty($_REQUEST['msg']) ? (int) $_REQUEST['msg'] . ', ' : '') . "'" . $_FILES['attachment']['name'][$n] . "', '" . $_FILES['attachment']['file_hash'][$n] . "', " . $_FILES['attachment']['size'][$n] . ')', __FILE__, __LINE__);
			$attachID = db_insert_id();
			$attachIDs[] = $attachID;

			$destName = getAttachmentFilename($destName, $attachID, false, $_FILES['attachment']['file_hash'][$n]);
</replace>