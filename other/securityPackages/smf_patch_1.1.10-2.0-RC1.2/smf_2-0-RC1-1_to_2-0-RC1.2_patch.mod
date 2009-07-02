<edit file>
$boarddir/index.php
</edit file>
<search for>
* Software Version:           SMF 2.0 RC1-1                                       *
</search for>

<replace>
* Software Version:           SMF 2.0 RC1.2                                       *
</replace>


<search for>

$forum_version = 'SMF 2.0 RC1-1';
</search for>

<replace>

$forum_version = 'SMF 2.0 RC1.2';
</replace>


<edit file>
$sourcedir/Post.php
</edit file>
<search for>
* Software Version:           SMF 2.0 RC1-1                                       *
</search for>

<replace>
* Software Version:           SMF 2.0 RC1.2                                       *
</replace>


<search for>
	// Check if it's locked.  It isn't locked if no topic is specified.
	if (!empty($topic))
</search for>

<replace>
	// No message is comlete without a topic.
	if (empty($topic) && !empty($_REQUEST['msg']))
	{
		$request = $smcFunc['db_query']('', '
			SELECT id_topic
			FROM {db_prefix}messages
			WHERE id_msg = {int:msg}',
			array(
				'msg' => (int) $_REQUEST['msg'],
		));
		if ($smcFunc['db_num_rows']($request) != 1)
			unset($_REQUEST['msg'], $_POST['msg'], $_GET['msg']);
		else
			list($topic) = $smcFunc['db_fetch_row']($request);
		$smcFunc['db_free_result']($request);
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
			$request = $smcFunc['db_query']('', '
				SELECT
					m.id_member, m.modified_time, m.smileys_enabled, m.body,
					m.poster_name, m.poster_email, m.subject, m.icon, m.approved,
					IFNULL(a.size, -1) AS filesize, a.filename, a.id_attach,
					a.approved AS attachment_approved, t.id_member_started AS id_member_poster,
					m.poster_time
			FROM {db_prefix}messages AS m
					INNER JOIN {db_prefix}topics AS t ON (t.id_topic = {int:current_topic})
					LEFT JOIN {db_prefix}attachments AS a ON (a.id_msg = m.id_msg AND a.attachment_type = {int:attachment_type})
				WHERE m.id_msg = {int:id_msg}
					AND m.id_topic = {int:current_topic}',
				array(
					'current_topic' => $topic,
					'attachment_type' => 0,
					'id_msg' => $_REQUEST['msg'],
				)
			);
			// The message they were trying to edit was most likely deleted.
			// !!! Change this error message?
			if ($smcFunc['db_num_rows']($request) == 0)
				fatal_lang_error('no_board', false);
			$row = $smcFunc['db_fetch_assoc']($request);

			$attachment_stuff = array($row);
			while ($row2 = $smcFunc['db_fetch_assoc']($request))
				$attachment_stuff[] = $row2;
			$smcFunc['db_free_result']($request);

			if ($row['id_member'] == $user_info['id'] && !allowedTo('modify_any'))
			{
				// Give an extra five minutes over the disable time threshold, so they can type - assuming the post is public.
				if ($row['approved'] && !empty($modSettings['edit_disable_time']) && $row['poster_time'] + ($modSettings['edit_disable_time'] + 5) * 60 < time())
					fatal_lang_error('modify_post_time_passed', false);
				elseif ($row['id_member_poster'] == $user_info['id'] && !allowedTo('modify_own'))
					isAllowedTo('modify_replies');
				else
					isAllowedTo('modify_own');
			}
			elseif ($row['id_member_poster'] == $user_info['id'] && !allowedTo('modify_any'))
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