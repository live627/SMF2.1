<edit file>
$boarddir/index.php
</edit file>
<search for>
* Software Version:           SMF 1.1.9                                           *
</search for>

<replace>
* Software Version:           SMF 1.1.10                                          *
</replace>


<search for>

$forum_version = 'SMF 1.1.9';
</search for>

<replace>

$forum_version = 'SMF 1.1.10';
</replace>


<edit file>
$sourcedir/Post.php
</edit file>
<search for>
* Software Version:           SMF 1.1.9                                           *
</search for>

<replace>
* Software Version:           SMF 1.1.10                                          *
</replace>


<search for>
	// Check if it's locked.  It isn't locked if no topic is specified.
	if (!empty($topic))
</search for>

<replace>
	// No message is comlete without a topic.
	if (empty($topic) && !empty($_REQUEST['msg']))
	{
		$request = db_query("
			SELECT id_topic
			FROM {$db_prefix}messages
			WHERE id_msg = " . (int) $_REQUEST['msg'], __FILE__, __LINE__);
		if (mysql_num_rows($request) != 1)
			unset($_REQUEST['msg'], $_POST['msg'], $_GET['msg']);
		else
			list($topic) = mysql_fetch_row($request);
		mysql_free_result($request);
	}

	// Check if it's locked.  It isn't locked if no topic is specified.
	if (!empty($topic))
</replace>


<search for>
		// Previewing an edit?
		if (isset($_REQUEST['msg']))
		{
</search for>

<replace>
		// Previewing an edit?
		if (isset($_REQUEST['msg']) && !empty($topic))
		{
			// Get the existing message.
			$request = db_query("
				SELECT
					m.ID_MEMBER, m.modifiedTime, m.smileysEnabled, m.body,
					m.posterName, m.posterEmail, m.subject, m.icon,
					IFNULL(a.size, -1) AS filesize, a.filename, a.ID_ATTACH,
					t.ID_MEMBER_STARTED AS ID_MEMBER_POSTER, m.posterTime
				FROM ({$db_prefix}messages AS m, {$db_prefix}topics AS t)
					LEFT JOIN {$db_prefix}attachments AS a ON (a.ID_MSG = m.ID_MSG AND a.attachmentType = 0)
				WHERE m.ID_MSG = " . (int) $_REQUEST['msg'] . "
					AND m.ID_TOPIC = $topic
					AND t.ID_TOPIC = $topic", __FILE__, __LINE__);
			// The message they were trying to edit was most likely deleted.
			// !!! Change this error message?
			if (mysql_num_rows($request) == 0)
				fatal_lang_error('smf232', false);
			$row = mysql_fetch_assoc($request);
	
			$attachment_stuff = array($row);
			while ($row2 = mysql_fetch_assoc($request))
				$attachment_stuff[] = $row2;
			mysql_free_result($request);

			if ($row['ID_MEMBER'] == $ID_MEMBER && !allowedTo('modify_any'))
			{
				// Give an extra five minutes over the disable time threshold, so they can type.
				if (!empty($modSettings['edit_disable_time']) && $row['posterTime'] + ($modSettings['edit_disable_time'] + 5) * 60 < time())
					fatal_lang_error('modify_post_time_passed', false);
				elseif ($row['ID_MEMBER_POSTER'] == $ID_MEMBER && !allowedTo('modify_own'))
					isAllowedTo('modify_replies');
				else
					isAllowedTo('modify_own');
			}
			elseif ($row['ID_MEMBER_POSTER'] == $ID_MEMBER && !allowedTo('modify_any'))
				isAllowedTo('modify_replies');
			else
				isAllowedTo('modify_any');
</replace>


<search for>
	// Editing a message...
	elseif (isset($_REQUEST['msg']))
</search for>

<replace>
	// Editing a message...
	elseif (isset($_REQUEST['msg']) && !empty($topic))
</replace>



<search for>
	// Posting a new topic.
	elseif (empty($topic))
	{
</search for>

<replace>
	// Posting a new topic.
	elseif (empty($topic))
	{
		// Now don't be silly, new topics will get their own id_msg soon enough.
		unset($_REQUEST['msg'], $_POST['msg'], $_GET['msg']);

</replace>