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
	// No message is complete without a topic.
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



<edit file>
$sourcedir/Subs-Auth.php
</edit file>
<search for>
* Software Version:           SMF 2.0 RC1                                         *
</search for>

<replace>
* Software Version:           SMF 2.0 RC1.2                                       *
</replace>


<search for>
		// Version 4.3.2 didn't store the cookie of the new session.
		if (version_compare(PHP_VERSION, '4.3.2') === 0)
			setcookie(session_name(), session_id(), time() + $cookie_length, $cookie_url[1], '', !empty($modSettings['secureCookies']));
</search for>

<replace>
		// Version 4.3.2 didn't store the cookie of the new session.
		if (version_compare(PHP_VERSION, '4.3.2') === 0 || (isset($_COOKIE[session_name()]) && $_COOKIE[session_name()] != session_id()))
			setcookie(session_name(), session_id(), time() + $cookie_length, $cookie_url[1], '', !empty($modSettings['secureCookies']));
</replace>



<edit file>
$sourcedir/Packages.php
</edit file>
<search for>
* Software Version:           SMF 2.0 RC1                                         *
</search for>

<replace>
* Software Version:           SMF 2.0 RC1.2                                       *
</replace>


<search for>
					'type' => $txt['execute_modification'],
					'action' => strtr($action['filename'], array($boarddir => '.')),
</search for>

<replace>
					'type' => $txt['execute_modification'],
					'action' => $smcFunc['htmlspecialchars'](strtr($action['filename'], array($boarddir => '.'))),
</replace>


<search for>
						$context['theme_actions'][$mod_action['is_custom']]['actions'][$actual_filename] = array(
							'type' => $txt['execute_modification'],
							'action' => strtr($mod_action['filename'], array($boarddir => '.')),
</search for>

<replace>
						$context['theme_actions'][$mod_action['is_custom']]['actions'][$actual_filename] = array(
							'type' => $txt['execute_modification'],
							'action' => $smcFunc['htmlspecialchars'](strtr($mod_action['filename'], array($boarddir => '.'))),
</replace>


<search for>
						$context['actions'][$actual_filename] = array(
							'type' => $txt['execute_modification'],
							'action' => strtr($mod_action['filename'], array($boarddir => '.')),
</search for>

<replace>
						$context['actions'][$actual_filename] = array(
							'type' => $txt['execute_modification'],
							'action' => $smcFunc['htmlspecialchars'](strtr($mod_action['filename'], array($boarddir => '.'))),
</replace>


<search for>
						'action' => strtr($mod_action['filename'], array($boarddir => '.')),
						'description' => $txt['package_action_skipping']
</search for>

<replace>
						'action' => $smcFunc['htmlspecialchars'](strtr($mod_action['filename'], array($boarddir => '.'))),
						'description' => $txt['package_action_skipping']
</replace>


<search for>
						'action' => strtr($mod_action['filename'], array($boarddir => '.')),
						'description' => $txt['package_action_missing']
</search for>

<replace>
						'action' => $smcFunc['htmlspecialchars'](strtr($mod_action['filename'], array($boarddir => '.'))),
						'description' => $txt['package_action_missing']
</replace>


<search for>
						'action' => strtr($mod_action['filename'], array($boarddir => '.')),
						'description' => $txt['package_action_error']
</search for>

<replace>
						'action' => $smcFunc['htmlspecialchars'](strtr($mod_action['filename'], array($boarddir => '.'))),
						'description' => $txt['package_action_error']
</replace>


<search for>
						$context['actions'][$actual_filename]['operations'][] = array(
							'type' => $txt['execute_modification'],
							'action' => strtr($mod_action['filename'], array($boarddir => '.')),
</search for>

<replace>
						$context['actions'][$actual_filename]['operations'][] = array(
							'type' => $txt['execute_modification'],
							'action' => $smcFunc['htmlspecialchars'](strtr($mod_action['filename'], array($boarddir => '.'))),
</replace>


<search for>
						$context['theme_actions'][$mod_action['is_custom']]['actions'][$actual_filename]['operations'][] = array(
							'type' => $txt['execute_modification'],
							'action' => strtr($mod_action['filename'], array($boarddir => '.')),
</search for>

<replace>
						$context['theme_actions'][$mod_action['is_custom']]['actions'][$actual_filename]['operations'][] = array(
							'type' => $txt['execute_modification'],
							'action' => $smcFunc['htmlspecialchars'](strtr($mod_action['filename'], array($boarddir => '.'))),
</replace>


<search for>
				'type' => $txt['execute_code'],
				'action' => $action['filename']
</search for>

<replace>
				'type' => $txt['execute_code'],
				'action' => $smcFunc['htmlspecialchars']($action['filename'])
</replace>


<search for>
				'type' => $txt['execute_database_changes'],
				'action' => $action['filename']
</search for>

<replace>
				'type' => $txt['execute_database_changes'],
				'action' => $smcFunc['htmlspecialchars']($action['filename'])
</replace>


<search for>
				'type' => $txt['package_create'] . ' ' . ($action['type'] == 'create-dir' ? $txt['package_tree'] : $txt['package_file']),
				'action' => strtr($action['destination'], array($boarddir => '.'))
</search for>

<replace>
				'type' => $txt['package_create'] . ' ' . ($action['type'] == 'create-dir' ? $txt['package_tree'] : $txt['package_file']),
				'action' => $smcFunc['htmlspecialchars'](strtr($action['destination'], array($boarddir => '.')))
</replace>


<search for>
				'type' => $txt['package_extract'] . ' ' . ($action['type'] == 'require-dir' ? $txt['package_tree'] : $txt['package_file']),
				'action' => strtr($action['destination'], array($boarddir => '.'))
</search for>

<replace>
				'type' => $txt['package_extract'] . ' ' . ($action['type'] == 'require-dir' ? $txt['package_tree'] : $txt['package_file']),
				'action' => $smcFunc['htmlspecialchars'](strtr($action['destination'], array($boarddir => '.')))
</replace>


<search for>
				'type' => $txt['package_move'] . ' ' . ($action['type'] == 'move-dir' ? $txt['package_tree'] : $txt['package_file']),
				'action' => strtr($action['source'], array($boarddir => '.')) . ' => ' . strtr($action['destination'], array($boarddir => '.'))
</search for>

<replace>
				'type' => $txt['package_move'] . ' ' . ($action['type'] == 'move-dir' ? $txt['package_tree'] : $txt['package_file']),
				'action' => $smcFunc['htmlspecialchars'](strtr($action['source'], array($boarddir => '.'))) . ' => ' . $smcFunc['htmlspecialchars'](strtr($action['destination'], array($boarddir => '.')))
</replace>


<search for>
				'type' => $txt['package_delete'] . ' ' . ($action['type'] == 'remove-dir' ? $txt['package_tree'] : $txt['package_file']),
				'action' => strtr($action['filename'], array($boarddir => '.'))
</search for>

<replace>
				'type' => $txt['package_delete'] . ' ' . ($action['type'] == 'remove-dir' ? $txt['package_tree'] : $txt['package_file']),
				'action' => $smcFunc['htmlspecialchars'](strtr($action['filename'], array($boarddir => '.')))
</replace>



<edit file>
$sourcedir/ManageRegistration.php
</edit file>

<search for>
* Software Version:           SMF 2.0 RC1                                         *
</search for>

<replace>
* Software Version:           SMF 2.0 RC1.2                                       *
</replace>


<search for>
			'send_welcome_email' => isset($_POST['emailPassword']),
</search for>

<replace>
			'send_welcome_email' => isset($_POST['emailPassword']) || empty($_POST['password']),
</replace>



<edit file>
$themedir/Register.template.php
</edit file>

<search for>
				if (document.forms.postForm.emailActivate.checked)
</search for>

<replace>
				if (document.forms.postForm.emailActivate.checked || document.forms.postForm.password.value == \'\')
</replace>


<search for>
					<input type="password" name="password" id="password_input" tabindex="', $context['tabindex']++, '" size="30" /><br />
</search for>

<replace>
					<input type="password" name="password" id="password_input" tabindex="', $context['tabindex']++, '" size="30" onchange="onCheckChange();" /><br />
</replace>


<search for>
					<input type="checkbox" name="emailPassword" id="emailPassword_check" tabindex="', $context['tabindex']++, '" checked="checked"', !empty($modSettings['registration_method']) && $modSettings['registration_method'] == 1 ? ' disabled="disabled"' : '', ' class="check" /><br />
</search for>

<replace>
					<input type="checkbox" name="emailPassword" id="emailPassword_check" tabindex="', $context['tabindex']++, '" checked="checked" disabled="disabled" class="check" /><br />
</replace>