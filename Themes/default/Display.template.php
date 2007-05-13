<?php
// Version: 2.0 Alpha; Display

function template_main()
{
	global $context, $settings, $options, $txt, $scripturl, $modSettings;

	// Show the anchor for the top and for the first message. If the first message is new, say so.
	echo '
<a name="top"></a>
<a name="msg', $context['first_message'], '"></a>', $context['first_new_message'] ? '<a name="new"></a>' : '';

		// Show the linktree
	echo '
<div>', theme_linktree(), '</div>';

	// Is this topic also a poll?
	if ($context['is_poll'])
	{
		echo '
<table cellpadding="3" cellspacing="0" border="0" width="100%" class="tborder" style="padding-top: 0; margin-bottom: 2ex;">
	<tr>
		<td class="titlebg" colspan="2" valign="middle" style="padding-left: 6px;">
			<img src="', $settings['images_url'], '/topic/', $context['poll']['is_locked'] ? 'normal_poll_locked' : 'normal_poll', '.gif" alt="" align="bottom" /> ', $txt['poll'], '
		</td>
	</tr>
	<tr>
		<td width="5%" valign="top" class="windowbg"><b>', $txt['poll_question'], ':</b></td>
		<td class="windowbg">
			', $context['poll']['question'];
		if (!empty($context['poll']['expire_time']))
			echo '
					&nbsp;(', ($context['poll']['is_expired'] ? $txt['poll_expired_on'] : $txt['poll_expires_on']), ': ', $context['poll']['expire_time'], ')';

		// Are they not allowed to vote but allowed to view the options?
		if ($context['poll']['show_results'] || !$context['allow_vote'])
		{
			echo '
			<table>
				<tr>
					<td style="padding-top: 2ex;">
						<table border="0" cellpadding="0" cellspacing="0">';

				// Show each option with its corresponding percentage bar.
			foreach ($context['poll']['options'] as $option)
				echo '
							<tr>
								<td style="padding-right: 2ex;', $option['voted_this'] ? 'font-weight: bold;' : '', '">', $option['option'], '</td>', $context['allow_poll_view'] ? '
								<td nowrap="nowrap">' . $option['bar'] . ' ' . $option['votes'] . ' (' . $option['percent'] . '%)</td>' : '', '
							</tr>';

			echo '
						</table>
					</td>
					<td valign="bottom" style="padding-left: 15px;">';

			// If they are allowed to revote - show them a link!
			if ($context['allow_change_vote'])
				echo '
					<a href="', $scripturl, '?action=vote;topic=', $context['current_topic'], '.', $context['start'], ';poll=', $context['poll']['id'], ';sesc=', $context['session_id'], '">', $txt['poll_change_vote'], '</a><br />';

			// If we're viewing the results... maybe we want to go back and vote?
			if ($context['poll']['show_results'] && $context['allow_vote'])
				echo '
						<a href="', $scripturl, '?topic=', $context['current_topic'], '.', $context['start'], '">', $txt['poll_return_vote'], '</a><br />';

			// If they're allowed to lock the poll, show a link!
			if ($context['poll']['lock'])
				echo '
						<a href="', $scripturl, '?action=lockVoting;topic=', $context['current_topic'], '.', $context['start'], ';sesc=', $context['session_id'], '">', !$context['poll']['is_locked'] ? $txt['poll_lock'] : $txt['poll_unlock'], '</a><br />';

			// If they're allowed to edit the poll... guess what... show a link!
			if ($context['poll']['edit'])
				echo '
						<a href="', $scripturl, '?action=editpoll;topic=', $context['current_topic'], '.', $context['start'], '">', $txt['poll_edit'], '</a>';

			echo '
					</td>
				</tr>', $context['allow_poll_view'] ? '
				<tr>
					<td colspan="2"><b>' . $txt['poll_total_voters'] . ': ' . $context['poll']['total_votes'] . '</b></td>
				</tr>' : '', '
			</table><br />';
		}
		// They are allowed to vote! Go to it!
		else
		{
			echo '
			<form action="', $scripturl, '?action=vote;topic=', $context['current_topic'], '.', $context['start'], ';poll=', $context['poll']['id'], '" method="post" accept-charset="', $context['character_set'], '" style="margin: 0px;">
				<table>
					<tr>
						<td colspan="2">';

			// Show a warning if they are allowed more than one option.
			if ($context['poll']['allowed_warning'])
				echo '
							', $context['poll']['allowed_warning'], '
						</td>
					</tr><tr>
						<td>';

			// Show each option with its button - a radio likely.
			foreach ($context['poll']['options'] as $option)
				echo '
							', $option['vote_button'], ' <label for="options-', $option['id'], '">', $option['option'], '</label><br />';

			echo '
						</td>
						<td valign="bottom" style="padding-left: 15px;">';

			// Allowed to view the results? (without voting!)
			if ($context['allow_poll_view'])
				echo '
							<a href="', $scripturl, '?topic=', $context['current_topic'], '.', $context['start'], ';viewResults">', $txt['poll_results'], '</a><br />';

			// Show a link for locking the poll as well...
			if ($context['poll']['lock'])
				echo '
							<a href="', $scripturl, '?action=lockVoting;topic=', $context['current_topic'], '.', $context['start'], ';sesc=', $context['session_id'], '">', (!$context['poll']['is_locked'] ? $txt['poll_lock'] : $txt['poll_unlock']), '</a><br />';

			// Want to edit it? Click right here......
			if ($context['poll']['edit'])
				echo '
							<a href="', $scripturl, '?action=editpoll;topic=', $context['current_topic'], '.', $context['start'], '">', $txt['poll_edit'], '</a>';

				echo '
						</td>
					</tr><tr>
						<td colspan="2"><input type="submit" value="', $txt['poll_vote'], '" /></td>
					</tr>
				</table>
				<input type="hidden" name="sc" value="', $context['session_id'], '" />
			</form>';
		}

		echo '
		</td>
	</tr>
</table>';
	}

	// Does this topic have some events linked to it?
	if (!empty($context['linked_calendar_events']))
	{
		echo '
<table cellpadding="3" cellspacing="0" border="0" width="100%" class="tborder" style="padding-top: 0; margin-bottom: 3ex;">
		<tr>
				<td class="titlebg" valign="middle" align="left" style="padding-left: 6px;">
						', $txt['calendar_linked_events'], '
				</td>
		</tr>
		<tr>
				<td width="5%" valign="top" class="windowbg">
						<ul>';
		foreach ($context['linked_calendar_events'] as $event)
			echo '
								<li>
									', ($event['can_edit'] ? '<a href="' . $event['modify_href'] . '" style="color: red;">*</a> ' : ''), '<b>', $event['title'], '</b>: ', $event['start_date'], ($event['start_date'] != $event['end_date'] ? ' - ' . $event['end_date'] : ''), '
								</li>';
		echo '
						</ul>
				</td>
		</tr>
</table>';
	}

	// Build the normal button array.
	$normal_buttons = array(
		'reply' => array('test' => 'can_reply', 'text' => 'reply', 'image' => 'reply.gif', 'lang' => true, 'url' => $scripturl . '?action=post;topic=' . $context['current_topic'] . '.' . $context['start'] . ';num_replies=' . $context['num_replies']),
		'notify' => array('test' => 'can_mark_notify', 'text' => 'notify', 'image' => 'notify.gif', 'lang' => true, 'custom' => 'onclick="return confirm(\'' . ($context['is_marked_notify'] ? $txt['notification_disable_topic'] : $txt['notification_enable_topic']) . '\');"', 'url' => $scripturl . '?action=notify;sa=' . ($context['is_marked_notify'] ? 'off' : 'on') . ';topic=' . $context['current_topic'] . '.' . $context['start'] . ';sesc=' . $context['session_id']),
		'custom' => array(),
		'send' => array('test' => 'can_send_topic', 'text' => 'send_topic', 'image' => 'sendtopic.gif', 'lang' => true, 'url' => $scripturl . '?action=emailuser;sa=sendtopic;topic=' . $context['current_topic'] . '.0'),
		'print' => array('text' => 'print', 'image' => 'print.gif', 'lang' => true, 'custom' => 'target="_blank" rel="nofollow"', 'url' => $scripturl . '?action=printpage;topic=' . $context['current_topic'] . '.0'),
	);

	// Special case for the custom one.
	if ($context['user']['is_logged'] && $settings['show_mark_read'])
		$normal_buttons['custom'] = array('text' => 'mark_unread', 'image' => 'markunread.gif', 'lang' => true, 'url' => $scripturl . '?action=markasread;sa=topic;t=' . $context['mark_unread_time'] . ';topic=' . $context['current_topic'] . '.' . $context['start'] . ';sesc=' . $context['session_id']);
	elseif ($context['can_add_poll'])
		$normal_buttons['custom'] = array('text' => 'add_poll', 'image' => 'add_poll.gif', 'lang' => true, 'url' => $scripturl . '?action=editpoll;add;topic=' . $context['current_topic'] . '.' . $context['start'] . ';sesc=' . $context['session_id']);
	else
		unset($normal_buttons['custom']);

	// Show the page index... "Pages: [1]".
	echo '
<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td class="middletext" valign="bottom" style="padding-bottom: 4px;">', $txt['pages'], ': ', $context['page_index'], !empty($modSettings['topbottomEnable']) ? $context['menu_separator'] . ' &nbsp;&nbsp;<a href="#lastPost"><b>' . $txt['go_down'] . '</b></a>' : '', '</td>
		<td align="right" style="padding-right: 1ex;">
			<div class="nav" style="margin-bottom: 2px;"> ', $context['previous_next'], '</div>
			<table cellpadding="0" cellspacing="0">
				<tr>
					', template_button_strip($normal_buttons, 'bottom'), '
				</tr>
			</table>
		</td>
	</tr>
</table>';

	// Show the topic information - icon, subject, etc.
	echo '
<table width="100%" cellpadding="3" cellspacing="0" border="0" class="tborder" style="border-bottom: 0;">
		<tr class="catbg3">
				<td valign="middle" width="2%" style="padding-left: 6px;">
						<img src="', $settings['images_url'], '/topic/', $context['class'], '.gif" align="bottom" alt="" />
				</td>
				<td width="13%"> ', $txt['author'], '</td>
				<td valign="middle" width="85%" style="padding-left: 6px;" id="top_subject">
						', $txt['topic'], ': ', $context['subject'], ' &nbsp;(', $txt['read'], ' ', $context['num_views'], ' ', $txt['times'], ')
				</td>
		</tr>';
	if (!empty($settings['display_who_viewing']))
	{
		echo '
		<tr>
				<td colspan="3" class="smalltext">';

		// Show just numbers...?
		if ($settings['display_who_viewing'] == 1)
				echo count($context['view_members']), ' ', count($context['view_members']) == 1 ? $txt['who_member'] : $txt['members'];
		// Or show the actual people viewing the topic?
		else
			echo empty($context['view_members_list']) ? '0 ' . $txt['members'] : implode(', ', $context['view_members_list']) . ((empty($context['view_num_hidden']) || $context['can_moderate_forum']) ? '' : ' (+ ' . $context['view_num_hidden'] . ' ' . $txt['hidden'] . ')');

		// Now show how many guests are here too.
		echo $txt['who_and'], $context['view_num_guests'], ' ', $context['view_num_guests'] == 1 ? $txt['guest'] : $txt['guests'], $txt['who_viewing_topic'], '
				</td>
		</tr>';
	}

	echo '
</table>';

	echo '
<form action="', $scripturl, '?action=quickmod2;topic=', $context['current_topic'], '.', $context['start'], '" method="post" accept-charset="', $context['character_set'], '" name="quickModForm" id="quickModForm" style="margin: 0;" onsubmit="return oQuickEdit.bInEditMode ? oQuickEdit.modifySave(\'' . $context['session_id'] . '\') : confirm(\'' . $txt['quickmod_confirm'] . '\');">';

	// These are some cache image buttons we may want.
	$reply_button = create_button('quote.gif', 'reply_quote', 'quote', 'align="middle"');
	$modify_button = create_button('modify.gif', 'modify_msg', 'modify', 'align="middle"');
	$remove_button = create_button('delete.gif', 'remove_message', 'remove', 'align="middle"');
	$split_button = create_button('split.gif', 'split', 'split', 'align="middle"');
	$approve_button = create_button('approve.gif', 'approve', 'approve', 'align="middle"');

// Time to display all the posts
	echo '
<table cellpadding="0" cellspacing="0" border="0" width="100%" class="bordercolor">';

	$ignoredMsgs = array();

	// Get all the messages...
	while ($message = $context['get_message']())
	{
		$ignoring = false;
		echo '
	<tr><td style="padding: 1px 1px 0 1px;">';

		if (in_array($message['member']['id'], $context['user']['ignoreusers']))
		{
			$ignoring = true;
			$ignoredMsgs[] = $message['id'];
		}

		// Show the message anchor and a "new" anchor if this message is new.
		if ($message['id'] != $context['first_message'])
			echo '
		<a name="msg', $message['id'], '"></a>', $message['first_new'] ? '<a name="new"></a>' : '';

		echo '
		<table width="100%" cellpadding="3" cellspacing="0" border="0">
			<tr><td class="', $message['approved'] ? ($message['alternate'] == 0 ? 'windowbg' : 'windowbg2') : 'approvebg', '">';

		// Show information about the poster of this message.
		echo '
				<table width="100%" cellpadding="5" cellspacing="0" style="table-layout: fixed;">
					<tr>
						<td valign="top" width="16%" rowspan="2" style="overflow: hidden;">
							<b>', $message['member']['link'], '</b>
							<div class="smalltext" id="msg_', $message['id'], '_extra_info">';

		// Show the member's custom title, if they have one.
		if (isset($message['member']['title']) && $message['member']['title'] != '')
			echo '
								', $message['member']['title'], '<br />';

		// Show the member's primary group (like 'Administrator') if they have one.
		if (isset($message['member']['group']) && $message['member']['group'] != '')
			echo '
								', $message['member']['group'], '<br />';

		// Don't show these things for guests.
		if (!$message['member']['is_guest'])
		{
			// Show the post group if and only if they have no other group or the option is on, and they are in a post group.
			if ((empty($settings['hide_post_group']) || $message['member']['group'] == '') && $message['member']['post_group'] != '')
				echo '
								', $message['member']['post_group'], '<br />';
			echo '
								', $message['member']['group_stars'], '<br />';

			// Is karma display enabled?  Total or +/-?
			if ($modSettings['karmaMode'] == '1')
				echo '
								<br />
								', $modSettings['karmaLabel'], ' ', $message['member']['karma']['good'] - $message['member']['karma']['bad'], '<br />';
			elseif ($modSettings['karmaMode'] == '2')
				echo '
								<br />
								', $modSettings['karmaLabel'], ' +', $message['member']['karma']['good'], '/-', $message['member']['karma']['bad'], '<br />';

			// Is this user allowed to modify this member's karma?
			if ($message['member']['karma']['allow'])
				echo '
								<a href="', $scripturl, '?action=modifykarma;sa=applaud;uid=', $message['member']['id'], ';topic=', $context['current_topic'], '.' . $context['start'], ';m=', $message['id'], ';sesc=', $context['session_id'], '">', $modSettings['karmaApplaudLabel'], '</a>
								<a href="', $scripturl, '?action=modifykarma;sa=smite;uid=', $message['member']['id'], ';topic=', $context['current_topic'], '.', $context['start'], ';m=', $message['id'], ';sesc=', $context['session_id'], '">', $modSettings['karmaSmiteLabel'], '</a><br />';

			// Show online and offline buttons?
			if (!empty($modSettings['onlineEnable']) && !$message['member']['is_guest'])
				echo '
								', $context['can_send_pm'] ? '<a href="' . $message['member']['online']['href'] . '" title="' . $message['member']['online']['label'] . '">' : '', $settings['use_image_buttons'] ? '<img src="' . $message['member']['online']['image_href'] . '" alt="' . $message['member']['online']['text'] . '" border="0" style="margin-top: 2px;" />' : $message['member']['online']['text'], $context['can_send_pm'] ? '</a>' : '', $settings['use_image_buttons'] ? '<span class="smalltext"> ' . $message['member']['online']['text'] . '</span>' : '', '<br /><br />';

			// Show the member's gender icon?
			if (!empty($settings['show_gender']) && $message['member']['gender']['image'] != '' && !isset($context['disabled_fields']['gender']))
				echo '
								', $txt['gender'], ': ', $message['member']['gender']['image'], '<br />';

			// Show how many posts they have made.
			if (!isset($context['disabled_fields']['posts']))
				echo '
								', $txt['member_postcount'], ': ', $message['member']['posts'], '<br />';

			// Any custom fields?
			if (!empty($modSettings['displayFields']))
			{
				foreach (explode('|', $modSettings['displayFields']) as $custom)
				{
					@list ($field, $title) = explode(';', $custom);
					if (!empty($title) && !empty($message['member']['options'][$field]))
						echo '
								', $title, ': ', $message['member']['options'][$field], '<br />';
				}
			}

			echo '<br />';

			// Show avatars, images, etc.?
			if (!empty($settings['show_user_images']) && empty($options['show_no_avatars']) && !empty($message['member']['avatar']['image']))
				echo '
								<div style="overflow: auto; width: 100%;">', $message['member']['avatar']['image'], '</div><br />';

			// Show their personal text?
			if (!empty($settings['show_blurb']) && $message['member']['blurb'] != '')
				echo '
								', $message['member']['blurb'], '<br />
								<br />';

			// This shows the popular messaging icons.
			echo '
								', !isset($context['disabled_fields']['icq']) ? $message['member']['icq']['link'] : '', '
								', !isset($context['disabled_fields']['msn']) ? $message['member']['msn']['link'] : '', '
								', !isset($context['disabled_fields']['aim']) ? $message['member']['aim']['link'] : '', '
								', !isset($context['disabled_fields']['yim']) ? $message['member']['yim']['link'] : '', '
								<br />';

			// Show the profile, website, email address, and personal message buttons.
			if ($settings['show_profile_buttons'])
			{
				// Don't show the profile button if you're not allowed to view the profile.
				if ($message['member']['can_view_profile'])
					echo '
								<a href="', $message['member']['href'], '">', ($settings['use_image_buttons'] ? '<img src="' . $settings['images_url'] . '/icons/profile_sm.gif" alt="' . $txt['view_profile'] . '" title="' . $txt['view_profile'] . '" border="0" />' : $txt['view_profile']), '</a>';

				// Don't show an icon if they haven't specified a website.
				if ($message['member']['website']['url'] != '' && !isset($context['disabled_fields']['website']))
					echo '
								<a href="', $message['member']['website']['url'], '" title="' . $message['member']['website']['title'] . '" target="_blank">', ($settings['use_image_buttons'] ? '<img src="' . $settings['images_url'] . '/www_sm.gif" alt="' . $txt['www'] . '" border="0" />' : $txt['www']), '</a>';

				// Don't show the email address if they want it hidden.
				if (empty($message['member']['hide_email']))
					echo '
								<a href="', !empty($modSettings['make_email_viewable']) ? 'mailto:' . $message['member']['email'] : $scripturl . '?action=emailuser;sa=email;msg=' . $message['id'], '">', ($settings['use_image_buttons'] ? '<img src="' . $settings['images_url'] . '/email_sm.gif" alt="' . $txt['email'] . '" title="' . $txt['email'] . '" />' : $txt['email']), '</a>';

				// Since we know this person isn't a guest, you *can* message them.
				if ($context['can_send_pm'])
					echo '
								<a href="', $scripturl, '?action=pm;sa=send;u=', $message['member']['id'], '" title="', $message['member']['online']['label'], '">', $settings['use_image_buttons'] ? '<img src="' . $settings['images_url'] . '/im_' . ($message['member']['online']['is_online'] ? 'on' : 'off') . '.gif" alt="' . $message['member']['online']['label'] . '" border="0" />' : $message['member']['online']['label'], '</a>';
			}

			// Are we showing the warning status?
			if (!isset($context['disabled_fields']['warning_status']) && $message['member']['warning_status'] && ($context['user']['can_mod'] || !empty($modSettings['warning_show'])))
				echo '
								<br />
								', $context['can_issue_warning'] ? '<a href="' . $scripturl . '?action=profile;u=' . $message['member']['id'] . ';sa=issueWarning">' : '', '<img src="', $settings['images_url'], '/warning_', $message['member']['warning_status'], '.gif" alt="', $txt['user_warn_' . $message['member']['warning_status']], '"/>', $context['can_issue_warning'] ? '</a>' : '', '<span class="warn_', $message['member']['warning_status'], '">', $txt['warn_' . $message['member']['warning_status']], '</span>';
		}
		// Otherwise, show the guest's email.
		elseif (empty($message['member']['hide_email']))
			echo '
								<br />
								<br />
								<a href="mailto:', $message['member']['email'], '">', ($settings['use_image_buttons'] ? '<img src="' . $settings['images_url'] . '/email_sm.gif" alt="' . $txt['email'] . '" title="' . $txt['email'] . '" border="0" />' : $txt['email']), '</a>';

		// Done with the information about the poster... on to the post itself.
		echo '
							</div>
						</td>
						<td valign="top" width="85%" height="100%">
							<table width="100%" border="0"><tr>
								<td valign="middle"><img src="', $message['icon_url'] . '" alt="" border="0"', $message['can_modify'] ? ' id="msg_icon_' . $message['id'] . '"' : '', ' /></td>
								<td valign="middle">
									<div style="font-weight: bold;" id="subject_', $message['id'], '">
										<a href="', $message['href'], '">', $message['subject'], '</a>
									</div>';

		// If this is the first post, (#0) just say when it was posted - otherwise give the reply #.
		echo '
									<div class="smalltext">&#171; <b>', !empty($message['counter']) ? $txt['reply'] . ' #' . $message['counter'] : '', ' ', $txt['on'], ':</b> ', $message['time'], ' &#187;</div></td>
								<td align="', !$context['right_to_left'] ? 'right' : 'left', '" valign="bottom" height="20" style="font-size: smaller;"><div id="msg_', $message['id'], '_quick_mod">';

		// Maybe we can approve it, maybe we should?
		if ($message['can_approve'])
			echo '
					<a href="', $scripturl, '?action=moderate;area=postmod;sa=approve;topic=', $context['current_topic'], '.', $context['start'], ';msg=', $message['id'], ';sesc=', $context['session_id'], '">', $approve_button, '</a>';

		// Can they reply? Have they turned on quick reply?
		if ($context['can_reply'] && !empty($options['display_quick_reply']))
			echo '
					<a href="', $scripturl, '?action=post;quote=', $message['id'], ';topic=', $context['current_topic'], '.', $context['start'], ';num_replies=', $context['num_replies'], ';sesc=', $context['session_id'], '" onclick="oQuickReply.quote(', $message['id'], ', \'', $context['session_id'], '\'); return false;">', $reply_button, '</a>';

		// So... quick reply is off, but they *can* reply?
		elseif ($context['can_reply'])
			echo '
					<a href="', $scripturl, '?action=post;quote=', $message['id'], ';topic=', $context['current_topic'], '.', $context['start'], ';num_replies=', $context['num_replies'], ';sesc=', $context['session_id'], '">', $reply_button, '</a>';

		// Can the user modify the contents of this post?
		if ($message['can_modify'])
			echo '
					<a href="', $scripturl, '?action=post;msg=', $message['id'], ';topic=', $context['current_topic'], '.', $context['start'], ';sesc=', $context['session_id'], '">', $modify_button, '</a>';

		// How about... even... remove it entirely?!
		if ($message['can_remove'])
			echo '
					<a href="', $scripturl, '?action=deletemsg;topic=', $context['current_topic'], '.', $context['start'], ';msg=', $message['id'], ';sesc=', $context['session_id'], '" onclick="return confirm(\'', $txt['remove_message'], '?\');">', $remove_button, '</a>';

		// What about splitting it off the rest of the topic?
		if ($context['can_split'])
			echo '
					<a href="', $scripturl, '?action=splittopics;topic=', $context['current_topic'], '.0;at=', $message['id'], '">', $split_button, '</a>';

		// Show a checkbox for quick moderation?
		if (!empty($options['display_quick_mod']) && $options['display_quick_mod'] == 1 && $message['can_remove'])
			echo '
									<input type="checkbox" name="msgs[]" value="', $message['id'], '" class="check" ', empty($settings['use_tabs']) ? 'onclick="document.getElementById(\'quickmodSubmit\').style.display = \'\';"' : '', ' />';

		// Show the post itself, finally!
		echo '
								</div></td>
							</tr></table>
							<hr width="100%" size="1" class="hrcolor" />';
		if ($ignoring)
		{
			echo '				<div id="msg_', $message['id'], '_ignored_prompt" style="display: none;">', $txt['ignoring_user'], '  <a href="#" onclick="return ignoreToggles[', $message['id'], '].toggle()">', $txt['show_ignore_user_post'], '</a></div>';
		}

		echo '
							<div class="post" id="msg_', $message['id'], '"', '>';

		if (!$message['approved'] && $message['member']['id'] != 0 && $message['member']['id'] == $context['user']['id'])
			echo '
								<div style="margin: 2ex; padding: 1ex; border: 2px dashed #cc3344; color: black; font-weight: bold;">
									', $txt['post_awaiting_approval'], '
								</div>';
		echo '
								', $message['body'], '
							</div>', $message['can_modify'] ? '
							<img src="' . $settings['images_url'] . '/icons/modify_inline.gif" alt="" align="right" id="modify_button_' . $message['id'] . '" style="cursor: ' . ($context['browser']['is_ie5'] || $context['browser']['is_ie5.5'] ? 'hand' : 'pointer') . '; display: none;" onclick="oQuickModify.modifyMsg(\'' . $message['id'] . '\', \'' . $context['session_id'] . '\')" />' : '' , '
						</td>
					</tr>';

		// Now for the attachments, signature, ip logged, etc...
		echo '
					<tr id="msg_', $message['id'], '_footer">
						<td valign="bottom" class="smalltext" width="85%">
							<table width="100%" border="0" style="table-layout: fixed;"><tr>
								<td colspan="2" class="smalltext" width="100%">';

		// Assuming there are attachments...
		if (!empty($message['attachment']))
		{
			echo '
									<hr width="100%" size="1" class="hrcolor" />
									<div style="overflow: auto; width: 100%;">';
			$last_approved_state = 1;
			foreach ($message['attachment'] as $attachment)
			{
				// Show a special box for unapproved attachments...
				if ($attachment['is_approved'] != $last_approved_state)
				{
					$last_approved_state = 0;
					echo '
									<fieldset>
										<legend>', $txt['attach_awaiting_approve'], '&nbsp;[<a href="', $scripturl, '?action=attachapprove;sa=all;mid=', $message['id'], ';sesc=', $context['session_id'], '">', $txt['approve_all'], '</a>]</legend>';
				}

				if ($attachment['is_image'])
				{
					if ($attachment['thumbnail']['has_thumb'])
						echo '
									<a href="', $attachment['href'], ';image" id="link_', $attachment['id'], '" onclick="', $attachment['thumbnail']['javascript'], '"><img src="', $attachment['thumbnail']['href'], '" alt="" id="thumb_', $attachment['id'], '" border="0" /></a><br />';
					else
						echo '
									<img src="' . $attachment['href'] . ';image" alt="" width="' . $attachment['width'] . '" height="' . $attachment['height'] . '" border="0" /><br />';
				}
				echo '
										<a href="' . $attachment['href'] . '"><img src="' . $settings['images_url'] . '/icons/clip.gif" align="middle" alt="*" border="0" />&nbsp;' . $attachment['name'] . '</a> ';

				if (!$attachment['is_approved'])
					echo '
										[<a href="', $scripturl, '?action=attachapprove;sa=approve;aid=', $attachment['id'], ';sesc=', $context['session_id'], '">', $txt['approve'], '</a>]&nbsp;|&nbsp;[<a href="', $scripturl, '?action=attachapprove;sa=reject;aid=', $attachment['id'], ';sesc=', $context['session_id'], '">', $txt['delete'], '</a>] ';
				echo '
										(', $attachment['size'], ($attachment['is_image'] ? ', ' . $attachment['real_width'] . 'x' . $attachment['real_height'] . ' - ' . $txt['attach_viewed'] : ' - ' . $txt['attach_downloaded']) . ' ' . $attachment['downloads'] . ' ' . $txt['attach_times'] . '.)<br />';
			}

			// If we had unapproved attachments clean up.
			if ($last_approved_state == 0)
				echo ' 
									</fieldset>';

			echo '
									</div>';
		}

		echo '
								</td>
							</tr><tr>
								<td valign="bottom" class="smalltext" id="modified_', $message['id'], '">';

		// Show "« Last Edit: Time by Person »" if this post was edited.
		if ($settings['show_modify'] && !empty($message['modified']['name']))
			echo '
									&#171; <i>', $txt['last_edit'], ': ', $message['modified']['time'], ' ', $txt['by'], ' ', $message['modified']['name'], '</i> &#187;';

		echo '
								</td>
								<td align="', !$context['right_to_left'] ? 'right' : 'left', '" valign="bottom" class="smalltext">';

		// Maybe they want to report this post to the moderator(s)?
		if ($context['can_report_moderator'])
			echo '
									<a href="', $scripturl, '?action=reporttm;topic=', $context['current_topic'], '.', $message['counter'], ';msg=', $message['id'], '">', $txt['report_to_mod'], '</a> &nbsp;';
		echo '
									<img src="', $settings['images_url'], '/ip.gif" alt="" border="0" />';

		// Show the IP to this user for this post - because you can moderate?
		if ($context['can_moderate_forum'] && !empty($message['member']['ip']))
			echo '
									<a href="', $scripturl, '?action=trackip;searchip=', $message['member']['ip'], '">', $message['member']['ip'], '</a> <a href="', $scripturl, '?action=helpadmin;help=see_admin_ip" onclick="return reqWin(this.href);" class="help">(?)</a>';
		// Or, should we show it because this is you?
		elseif ($message['can_see_ip'])
			echo '
									<a href="', $scripturl, '?action=helpadmin;help=see_member_ip" onclick="return reqWin(this.href);" class="help">', $message['member']['ip'], '</a>';
		// Okay, are you at least logged in?  Then we can show something about why IPs are logged...
		elseif (!$context['user']['is_guest'])
			echo '
									<a href="', $scripturl, '?action=helpadmin;help=see_member_ip" onclick="return reqWin(this.href);" class="help">', $txt['logged'], '</a>';
		// Otherwise, you see NOTHING!
		else
			echo '
									', $txt['logged'];

		echo '
								</td>
							</tr></table>';

		// Show the member's signature?
		if (!empty($message['member']['signature']) && empty($options['show_no_signatures']) && $context['signature_enabled'])
			echo '
							<hr width="100%" size="1" class="hrcolor" />
							<div class="signature">', $message['member']['signature'], '</div>';

		echo '
						</td>
					</tr>
				</table>
			</td></tr>
		</table>
	</td></tr>';
	}
	echo '
	<tr><td style="padding: 0 0 1px 0;"></td></tr>
</table>
<a name="lastPost"></a>';

	// As before, build the custom button right.
	if ($context['can_add_poll'])
		$normal_buttons['custom'] = array('text' => 'add_poll', 'image' => 'add_poll.gif', 'lang' => true, 'url' => $scripturl . '?action=editpoll;add;topic=' . $context['current_topic'] . '.' . $context['start'] . ';sesc=' . $context['session_id']);
	elseif ($context['user']['is_logged'] && $settings['show_mark_read'])
		$normal_buttons['custom'] = array('text' => 'mark_unread', 'image' => 'markunread.gif', 'lang' => true, 'url' => $scripturl . '?action=markasread;sa=topic;t=' . $context['mark_unread_time'] . ';topic=' . $context['current_topic'] . '.' . $context['start'] . ';sesc=' . $context['session_id']);

	echo '
<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td class="middletext">', $txt['pages'], ': ', $context['page_index'], !empty($modSettings['topbottomEnable']) ? $context['menu_separator'] . ' &nbsp;&nbsp;<a href="#top"><b>' . $txt['go_up'] . '</b></a>' : '', '</td>
		<td align="right" style="padding-right: 1ex;">
			<table cellpadding="0" cellspacing="0">
				<tr>
					', template_button_strip($normal_buttons, 'top', true), '
				</tr>
			</table>
		</td>
	</tr>
</table>

<table border="0" width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 1ex;">
		<tr>';
	if ($settings['linktree_inline'])
			echo '
				<td valign="top">', theme_linktree(), '</td> ';
	echo '
				<td valign="top" align="', !$context['right_to_left'] ? 'right' : 'left', '" class="nav"> ', $context['previous_next'], '</td>
		</tr>
</table>';

	$mod_buttons = array(
		'move' => array('test' => 'can_move', 'text' => 'move_topic', 'image' => 'admin_move.gif', 'lang' => true, 'url' => $scripturl . '?action=movetopic;topic=' . $context['current_topic'] . '.0'),
		'delete' => array('test' => 'can_delete', 'text' => 'remove_topic', 'image' => 'admin_rem.gif', 'lang' => true, 'custom' => 'onclick="return confirm(\'' . $txt['are_sure_remove_topic'] . '\');"', 'url' => $scripturl . '?action=removetopic2;topic=' . $context['current_topic'] . '.0;sesc=' . $context['session_id']),
		'lock' => array('test' => 'can_lock', 'text' => empty($context['is_locked']) ? 'set_lock' : 'set_unlock', 'image' => 'admin_lock.gif', 'lang' => true, 'url' => $scripturl . '?action=lock;topic=' . $context['current_topic'] . '.' . $context['start'] . ';sesc=' . $context['session_id']),
		'sticky' => array('test' => 'can_sticky', 'text' => empty($context['is_sticky']) ? 'set_sticky' : 'set_nonsticky', 'image' => 'admin_sticky.gif', 'lang' => true, 'url' => $scripturl . '?action=sticky;topic=' . $context['current_topic'] . '.' . $context['start'] . ';sesc=' . $context['session_id']),
		'merge' => array('test' => 'can_merge', 'text' => 'merge', 'image' => 'merge.gif', 'lang' => true, 'url' => $scripturl . '?action=mergetopics;board=' . $context['current_board'] . '.0;from=' . $context['current_topic']),
		'remove_poll' => array('test' => 'can_remove_poll', 'text' => 'poll_remove', 'image' => 'admin_remove_poll.gif', 'lang' => true, 'custom' => 'onclick="return confirm(\'' . $txt['poll_remove_warn'] . '\');"', 'url' => $scripturl . '?action=removepoll;topic=' . $context['current_topic'] . '.' . $context['start']),
		'calendar' => array('test' => 'calendar_post', 'text' => 'calendar_link', 'image' => 'linktocal.gif', 'lang' => true, 'url' => $scripturl . '?action=post;calendar;msg=' . $context['topic_first_message'] . ';topic=' . $context['current_topic'] . '.0;sesc=' . $context['session_id']),
	);

	if ($context['can_remove_post'] && !empty($options['display_quick_mod']) && $options['display_quick_mod'] == 1)
		$mod_buttons[] = array('text' => 'quickmod_delete_selected', 'image' => 'delete_selected.gif', 'lang' => true, 'custom' => 'onclick="return confirm(\'' . $txt['quickmod_confirm'] . '\');" id="quickmodSubmit"', 'url' => 'javascript:document.quickModForm.submit();');

	echo '
	<table cellpadding="0" cellspacing="0" border="0" style="margin-left: 1ex;">
		<tr>
			', template_button_strip($mod_buttons, 'bottom') , '
		</tr>
	</table>';

	if (!empty($options['display_quick_mod']) && $options['display_quick_mod'] == 1 && $context['can_remove_post'])
		echo '
	<input type="hidden" name="sc" value="', $context['session_id'], '" />';

	echo '
</form>';

	// Show the jumpto box, or actually...let Javascript do it.
	echo '
<div class="tborder">
	<div class="titlebg2" style="padding: 4px;" align="', !$context['right_to_left'] ? 'right' : 'left', '" id="display_jump_to">&nbsp;</div>
</div><br />';

	if ($context['can_reply'] && !empty($options['display_quick_reply']))
	{
		echo '
<a name="quickreply"></a>
<table border="0" cellspacing="1" cellpadding="3" class="bordercolor" width="100%" style="clear: both;">
	<tr>
		<td colspan="2" class="catbg"><a href="javascript:oQuickReply.swap();"><img src="', $settings['images_url'], '/', $options['display_quick_reply'] == 2 ? 'collapse' : 'expand', '.gif" alt="+" id="quickReplyExpand" /></a> <a href="javascript:oQuickReply.swap();">', $txt['quick_reply'], '</a></td>
		</tr>
	<tr id="quickReplyOptions"', $options['display_quick_reply'] == 2 ? '' : ' style="display: none"', '>
		<td class="windowbg" width="25%" valign="top">', $txt['quick_reply_desc'], $context['is_locked'] ? '<br /><br /><b>' . $txt['quick_reply_warning'] . '</b>' : '', '</td>
		<td class="windowbg" width="75%" align="center">
			', $context['can_reply_approved'] ? '' : '<i>' . $txt['wait_for_approval'] . '</i>', '
			<form action="', $scripturl, '?action=post2" method="post" accept-charset="', $context['character_set'], '" name="postmodify" id="postmodify" onsubmit="submitonce(this);" style="margin: 0;">
				<input type="hidden" name="topic" value="', $context['current_topic'], '" />
				<input type="hidden" name="subject" value="', $context['response_prefix'], $context['subject'], '" />
				<input type="hidden" name="icon" value="xx" />
				<input type="hidden" name="notify" value="', $context['is_marked_notify'] || !empty($options['auto_notify']) ? '1' : '0', '" />
				<input type="hidden" name="not_approved" value="', !$context['can_reply_approved'], '" />
				<input type="hidden" name="goback" value="', empty($options['return_to_post']) ? '0' : '1', '" />
				<input type="hidden" name="num_replies" value="', $context['num_replies'], '" />
				<textarea cols="75" rows="7" style="width: 95%; height: 100px;" name="message" tabindex="1"></textarea><br />
				<input type="submit" name="post" value="', $txt['post'], '" onclick="return submitThisOnce(this);" accesskey="s" tabindex="2" />
				<input type="submit" name="preview" value="', $txt['preview'], '" onclick="return submitThisOnce(this);" accesskey="p" tabindex="4" />';
		if ($context['show_spellchecking'])
			echo '
				<input type="button" value="', $txt['spell_check'], '" onclick="spellCheck(\'postmodify\', \'message\');" tabindex="5" />';
		echo '
				<input type="hidden" name="sc" value="', $context['session_id'], '" />
				<input type="hidden" name="seqnum" value="', $context['form_sequence_number'], '" />
			</form>
		</td>
	</tr>
</table>';
	}

	if ($context['show_spellchecking'])
		echo '
<form action="', $scripturl, '?action=spellcheck" method="post" accept-charset="', $context['character_set'], '" name="spell_form" id="spell_form" target="spellWindow"><input type="hidden" name="spellstring" value="" /></form>
<script language="JavaScript" type="text/javascript" src="' . $settings['default_theme_url'] . '/scripts/spellcheck.js"></script>';

	echo '
<script language="JavaScript" type="text/javascript" src="' . $settings['default_theme_url'] . '/scripts/xml_topic.js"></script>
<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[';

	if (!empty($options['display_quick_reply']))
		echo '
	var oQuickReply = new QuickReply({
		bDefaultCollapsed: ', !empty($options['display_quick_reply']) && $options['display_quick_reply'] == 2 ? 'false' : 'true', ',
		iTopicId: ', $context['current_topic'], ',
		iStart: ', $context['start'], ',
		sScriptUrl: "', $scripturl, '",
		sImagesUrl: "', $settings['images_url'], '",
		sContainerId: "quickReplyOptions",
		sImageId: "quickReplyExpand",
		sImageCollapsed: "collapse.gif",
		sImageExpanded: "expand.gif",
		sJumpAnchor: "quickreply"
	});';

	if (empty($settings['use_tabs']))
		echo '
	document.getElementById("quickmodSubmit").style.display = "none";';

	echo '
	if (typeof(window.XMLHttpRequest) != "undefined")
	{
		var oQuickModify = new QuickModify({
			sScriptUrl: "', $scripturl, '",
			bShowModify: ', $settings['show_modify'] ? 'true' : 'false', ',
			iTopicId: ', $context['current_topic'], ',
			sTemplateBodyEdit: \'<div id="error_box" style="padding: 4px; color: red;"></div><textarea class="editor" name="message" rows="12" style="width: 94%; margin-bottom: 10px;">%body%</textarea><br /><input type="hidden" name="sc" value="', $context['session_id'], '" /><input type="hidden" name="topic" value="', $context['current_topic'], '" /><input type="hidden" name="msg" value="%msg_id%" /><div style="text-align: center;"><input type="submit" name="post" value="', $txt['save'], '" onclick="return oQuickModify.modifySave(\\\'' . $context['session_id'] . '\\\');" accesskey="s" />&nbsp;&nbsp;', $context['show_spellchecking'] ? '<input type="button" value="' . $txt['spell_check'] . '" onclick="spellCheck(\\\'quickModForm\\\', \\\'message\\\');" />&nbsp;&nbsp;' : '', '<input type="submit" name="cancel" value="', $txt['modify_cancel'], '" onclick="return oQuickModify.modifyCancel();" /></div>\',
			sTemplateSubjectEdit: \'<input type="text" name="subject" value="%subject%" size="60" style="width: 99%;"  maxlength="80" />\',
			sTemplateBodyNormal: \'%body%\',
			sTemplateSubjectNormal: \'<a href="', $scripturl, '?topic=', $context['current_topic'], '.msg%msg_id%#msg%msg_id%">%subject%</a>\',
			sTemplateTopSubject: "', $txt['topic'], ': %subject% &nbsp;(', $txt['read'], ' ', $context['num_views'], ' ', $txt['times'], ')",
			sErrorBorderStyle: "1px solid red"
		});

		aJumpTo[aJumpTo.length] = new JumpTo({
			sContainerId: "display_jump_to",
			sJumpToTemplate: "<label class=\"smalltext\" for=\"%select_id%\">', $context['jump_to']['label'], ':</label> %dropdown_list%",
			iCurBoardId: ', $context['current_board'], ',
			iCurBoardChildLevel: ', $context['jump_to']['child_level'], ',
			sCurBoardName: "', $context['jump_to']['board_name'], '",
			sBoardChildLevelIndicator: "==",
			sBoardPrefix: "=> ",
			sCatSeparator: "-----------------------------",
			sCatPrefix: "",
			sGoButtonLabel: "', $txt['go'], '"
		});

		aIconLists[aIconLists.length] = new IconList({
			sBackReference: "aIconLists[" + aIconLists.length + "]",
			sIconIdPrefix: "msg_icon_",
			sScriptUrl: "', $scripturl, '",
			bShowModify: ', $settings['show_modify'] ? 'true' : 'false', ',
			iBoardId: ', $context['current_board'], ',
			iTopicId: ', $context['current_topic'], ',
			sSessionId: "', $context['session_id'], '",
			sLabelIconList: "Message Icon",
			sBoxBackground: "transparent",
			sBoxBackgroundHover: "#ffffff",
			iBoxBorderWidthHover: 1,
			sBoxBorderColorHover: "#adadad" ,
			sContainerBackground: "#ffffff",
			sContainerBorder: "1px solid #adadad",
			sItemBorder: "1px solid #ffffff",
			sItemBorderHover: "1px dotted gray",
			sItemBackground: "transparent",
			sItemBackgroundHover: "#e0e0f0"
		});
	}';

	if (!empty($ignoredMsgs))
	{
		echo '
	var ignoreToggles = new Array()';

		foreach($ignoredMsgs AS $msgid)
		{
			echo '
		ignoreToggles[', $msgid, '] = new smfToggle("ignore_msg_', $msgid, '", false);
			ignoreToggles[', $msgid, '].addTogglePanel("msg_', $msgid, '_extra_info");
			ignoreToggles[', $msgid, '].addTogglePanel("msg_', $msgid, '");
			ignoreToggles[', $msgid, '].addTogglePanel("msg_', $msgid, '_footer");
			ignoreToggles[', $msgid, '].addTogglePanel("msg_', $msgid, '_quick_mod");
			ignoreToggles[', $msgid, '].addTogglePanel("modify_button_', $msgid, '");
			ignoreToggles[', $msgid, '].addTogglePanel("msg_', $msgid, '_ignored_prompt", true);
		ignoreToggles[', $msgid, '].toggle()';
		}
	}

	echo '
	// ]]></script>';
}

?>