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
	// No message is complete without a topic.
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



<edit file>
$sourcedir/PersonalMessage.php
</edit file>
<search for>
* Software Version:           SMF 1.1.6                                           *
</search for>

<replace>
* Software Version:           SMF 1.1.10                                          *
</replace>


<search for>
		$context['post_error'][$error_type] = true;
		if (isset($txt['error_' . $error_type]))
			$context['post_error']['messages'][] = $txt['error_' . $error_type];
</search for>

<replace>
		// There is no compatible language string. So lets work around that.
		if ($error_type == 'wrong_verification_code')
			$txt['error_wrong_verification_code'] = $txt['visual_verification_failed'];

		$context['post_error'][$error_type] = true;
		if (isset($txt['error_' . $error_type]))
			$context['post_error']['messages'][] = $txt['error_' . $error_type];
</replace>



<edit file>
$sourcedir/ManageAttachments.php
</edit file>
<search for>
* Software Version:           SMF 1.1.9                                           *
</search for>

<replace>
* Software Version:           SMF 1.1.10                                          *
</replace>


<search for>
				'link' => '<a href="' . ($row['attachmentType'] == 1 ? $modSettings['custom_avatar_url'] . '/' . $row['filename'] : ($scripturl . '?action=dlattach;' . ($context['browse_type'] == 'avatars' ? 'type=avatar;' : 'topic=' . $row['ID_TOPIC'] . '.0;') . 'id=' . $row['ID_ATTACH'])) . '"' . (empty($row['width']) || empty($row['height']) ? '' : ' onclick="return reqWin(this.href + \';image\', ' . ($row['width'] + 20) . ', ' . ($row['height'] + 20) . ', true);"') . '>' . htmlspecialchars($row['filename']) . '</a>'
</search for>

<replace>
				'link' => '<a href="' . ($row['attachmentType'] == 1 ? $modSettings['custom_avatar_url'] . '/' . $row['filename'] : ($scripturl . '?action=dlattach;' . ($context['browse_type'] == 'avatars' ? 'type=avatar;' : 'topic=' . $row['ID_TOPIC'] . '.0;') . 'id=' . $row['ID_ATTACH'])) . '"' . (empty($row['width']) || empty($row['height']) ? '' : ' onclick="return reqWin(this.href + \'' . ($modSettings['custom_avatar_url'] ? '' : ';image') . '\', ' . ($row['width'] + 20) . ', ' . ($row['height'] + 20) . ', true);"') . '>' . htmlspecialchars($row['filename']) . '</a>'
</replace>



<edit file>
$sourcedir/Profile.php
</edit file>
<search for>
* Software Version:           SMF 1.1.9                                           *
</search for>

<replace>
* Software Version:           SMF 1.1.10                                          *
</replace>


<search for>
				'url' => 'http://www.apnic.net/apnic-bin/whois2.pl?searchtext=' . $context['ip'],
</search for>

<replace>
				'url' => 'http://wq.apnic.net/apnic-bin/whois.pl?searchtext=' . $context['ip'],
</replace>


<search for>
				'url' => 'http://ws.arin.net/cgi-bin/whois.pl?queryinput=' . $context['ip'],
</search for>

<replace>
				'url' => 'http://ws.arin.net/whois/?queryinput=' . $context['ip'],
</replace>


<search for>
				'url' => 'http://www.ripe.net/perl/whois?searchtext=' . $context['ip'],
</search for>

<replace>
				'url' => 'http://www.db.ripe.net/whois?searchtext=' . $context['ip'],
</replace>



<edit file>
$sourcedir/ManageBans.php
</edit file>
<search for>
* Software Version:           SMF 1.1                                             *
</search for>

<replace>
* Software Version:           SMF 1.1.10                                          *
</replace>


<search for>
	if (!empty($updates))
		foreach ($updates as $newStatus => $members)
			updateMemberData($members, array('is_activated' => $newStatus));
</search for>

<replace>
	if (!empty($updates))
		foreach ($updates as $newStatus => $members)
			updateMemberData($members, array('is_activated' => $newStatus));

	// Update the amount of members awaiting approval
	updateStats('member');
</replace>



<edit file>
$sourcedir/Subs-Auth.php
</edit file>
<search for>
* Software Version:           SMF 1.1.6                                           *
</search for>

<replace>
* Software Version:           SMF 1.1.10                                          *
</replace>


<search for>
		// Version 4.3.2 didn't store the cookie of the new session.
		if (version_compare(PHP_VERSION, '4.3.2') === 0)
			setcookie(session_name(), session_id(), time() + $cookie_length, $cookie_url[1], '', 0);
</search for>

<replace>
		// Version 4.3.2 didn't store the cookie of the new session.
		if (version_compare(PHP_VERSION, '4.3.2') === 0 || (isset($_COOKIE[session_name()]) && $_COOKIE[session_name()] != session_id()))
			setcookie(session_name(), session_id(), time() + $cookie_length, $cookie_url[1], '', 0);
</replace>



<edit file>
$sourcedir/Register.php
</edit file>
<search for>
* Software Version:           SMF 1.1.6                                           *
</search for>

<replace>
* Software Version:           SMF 1.1.10                                          *
</replace>


<search for>
		if (trim($_POST['realName']) != '' && !isReservedName($_POST['realName'], $memID) && $func['strlen']($_POST['realName']) <= 60)
</search for>

<replace>
		if (trim($_POST['realName']) != '' && !isReservedName($_POST['realName']) && $func['strlen']($_POST['realName']) <= 60)
</replace>



<edit file>
$sourcedir/Packages.php
</edit file>
<search for>
* Software Version:           SMF 1.1.8                                           *
</search for>

<replace>
* Software Version:           SMF 1.1.10                                          *
</replace>


<search for>
					'type' => $txt['package56'],
					'action' => strtr($action['filename'], array($boarddir => '.')),
</search for>

<replace>
					'type' => $txt['package56'],
					'action' => htmlspecialchars(strtr($action['filename'], array($boarddir => '.'))),
</replace>


<search for>
						'action' => strtr($mod_action['filename'], array($boarddir => '.')),
						'description' => $failed ? $txt['package_action_failure'] : $txt['package_action_success']
</search for>

<replace>
						'action' => htmlspecialchars(strtr($mod_action['filename'], array($boarddir => '.'))),
						'description' => $failed ? $txt['package_action_failure'] : $txt['package_action_success']
</replace>


<search for>
						'action' => strtr($mod_action['filename'], array($boarddir => '.')),
						'description' => $txt['package_action_skipping']
</search for>

<replace>
						'action' => htmlspecialchars(strtr($mod_action['filename'], array($boarddir => '.'))),
						'description' => $txt['package_action_skipping']
</replace>


<search for>
						'action' => strtr($mod_action['filename'], array($boarddir => '.')),
						'description' => $txt['package_action_missing']
</search for>

<replace>
						'action' => htmlspecialchars(strtr($mod_action['filename'], array($boarddir => '.'))),
						'description' => $txt['package_action_missing']
</replace>


<search for>
						'action' => strtr($mod_action['filename'], array($boarddir => '.')),
						'description' => $txt['package_action_error']
</search for>

<replace>
						'action' => htmlspecialchars(strtr($mod_action['filename'], array($boarddir => '.'))),
						'description' => $txt['package_action_error']
</replace>


<search for>
				'type' => $txt['package57'],
				'action' => $action['filename']
</search for>

<replace>
				'type' => $txt['package57'],
				'action' => htmlspecialchars($action['filename'])
</replace>


<search for>
				'type' => $txt['package50'] . ' ' . ($action['type'] == 'create-dir' ? $txt['package55'] : $txt['package54']),
				'action' => strtr($action['destination'], array($boarddir => '.'))
</search for>

<replace>
				'type' => $txt['package50'] . ' ' . ($action['type'] == 'create-dir' ? $txt['package55'] : $txt['package54']),
				'action' => htmlspecialchars(strtr($action['destination'], array($boarddir => '.')))
</replace>


<search for>
				'type' => $txt['package53'] . ' ' . ($action['type'] == 'require-dir' ? $txt['package55'] : $txt['package54']),
				'action' => strtr($action['destination'], array($boarddir => '.'))
</search for>

<replace>
				'type' => $txt['package53'] . ' ' . ($action['type'] == 'require-dir' ? $txt['package55'] : $txt['package54']),
				'action' => htmlspecialchars(strtr($action['destination'], array($boarddir => '.')))
</replace>


<search for>
				'type' => $txt['package51'] . ' ' . ($action['type'] == 'move-dir' ? $txt['package55'] : $txt['package54']),
				'action' => strtr($action['source'], array($boarddir => '.')) . ' => ' . strtr($action['destination'], array($boarddir => '.'))
</search for>

<replace>
				'type' => $txt['package51'] . ' ' . ($action['type'] == 'move-dir' ? $txt['package55'] : $txt['package54']),
				'action' => htmlspecialchars(strtr($action['source'], array($boarddir => '.'))) . ' => ' . htmlspecialchars(strtr($action['destination'], array($boarddir => '.')))
</replace>


<search for>
				'type' => $txt['package52'] . ' ' . ($action['type'] == 'remove-dir' ? $txt['package55'] : $txt['package54']),
				'action' => strtr($action['filename'], array($boarddir => '.'))
</search for>

<replace>
				'type' => $txt['package52'] . ' ' . ($action['type'] == 'remove-dir' ? $txt['package55'] : $txt['package54']),
				'action' => htmlspecialchars(strtr($action['filename'], array($boarddir => '.')))
</replace>
