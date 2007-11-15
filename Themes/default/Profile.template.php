<?php
// Version: 2.0 Beta 1; Profile

// Template for the profile side bar - goes before any other profile template.
function template_profile_above()
{
	global $context, $settings, $options, $scripturl, $modSettings, $txt;

	echo '
	<script language="JavaScript" type="text/javascript" src="', $settings['default_theme_url'], '/scripts/profile.js"></script>';

	// Assuming there are actually some areas the user can visit...
	if (!empty($context['profile_areas']))
	{
		echo '
		<table width="100%" border="0" cellpadding="0" cellspacing="0" style="padding-top: 1ex;">
			<tr>
				<td width="180" valign="top">
					<table border="0" cellpadding="4" cellspacing="1" class="bordercolor" width="170">';

		// Loop through every area, displaying its name as a header.
		foreach ($context['profile_areas'] as $section)
		{
			echo '
						<tr>
							<td class="catbg">', $section['title'], '</td>
						</tr>
						<tr class="windowbg2">
							<td class="smalltext">';

			// For every section of the area display it, and bold it if it's the current area.
			foreach ($section['areas'] as $i => $area)
				if ($i == $context['menu_item_selected'])
					echo '
								<b>', $area, '</b><br />';
				else
					echo '
								', $area, '<br />';
			echo '
								<br />
							</td>
						</tr>';
		}
		echo '
					</table>
				</td>
				<td width="100%" valign="top">';
	}
	// If no areas exist just open up a containing table.
	else
	{
		echo '
		<table width="100%" border="0" cellpadding="0" cellspacing="0" style="padding-top: 1ex;">
			<tr>
				<td width="100%" valign="top">';
	}

	// If an error occurred while trying to save previously, give the user a clue!
	if (!empty($context['post_errors']))
	{
		echo '
					<table width="85%" cellpadding="0" cellspacing="0" border="0" align="center">
						<tr>
							<td>', template_error_message(), '</td>
						</tr>
					</table>';
	}

	// If the profile was update successfully let the user know this
	if (!empty($context['profile_updated']))
	{
		echo '
					<table width="85%" cellpadding="0" cellspacing="0" border="0" align="center">
						<tr>
							<td>
								<div class="windowbg" style="margin: 1ex; padding: 1ex 2ex; border: 1px dashed green; color: green;">
									', $context['profile_updated'], '
								</div>
							</td>
						</tr>
					</table>';
	}
}

// Template for closing off table started in profile_above.
function template_profile_below()
{
	global $context, $settings, $options;

	echo '
				</td>
			</tr>
		</table>';
}

// This template displays users details without any option to edit them.
function template_summary()
{
	global $context, $settings, $options, $scripturl, $modSettings, $txt;

	// First do the containing table and table header.
	echo '
<table border="0" cellpadding="4" cellspacing="1" align="center" class="bordercolor">
	<tr class="titlebg">
		<td width="420" height="26">
			<img src="', $settings['images_url'], '/icons/profile_sm.gif" alt="" align="top" />&nbsp;
			', $txt['summary'], ' - ', $context['member']['name'], '
		</td>
		<td align="center" width="150">', $txt['picture_text'], '</td>
	</tr>';

	// Do the left hand column - where all the important user info is displayed.
	echo '
	<tr>
		<td class="windowbg" width="420">
			<table border="0" cellspacing="0" cellpadding="2" width="100%">
				<tr>
					<td><b>', $txt['name'], ': </b></td>
					<td>', $context['member']['name'], '</td>
				</tr>';
	if (!empty($modSettings['titlesEnable']) && $context['member']['title'] != '')
	{
		echo '
				<tr>
					<td><b>', $txt['custom_title'], ': </b></td>
					<td>', $context['member']['title'], '</td>
				</tr>';
	}

	if (!isset($context['disabled_fields']['posts']))
		echo '
				<tr>
					<td><b>', $txt['profile_posts'], ': </b></td>
					<td>', $context['member']['posts'], ' (', $context['member']['posts_per_day'], ' ', $txt['posts_per_day'], ')</td>
				</tr>';

	echo '
				<tr>
					<td><b>', $txt['position'], ': </b></td>
					<td>', (!empty($context['member']['group']) ? $context['member']['group'] : $context['member']['post_group']), '</td>
				</tr>';

	// Can they issue a warning?
	if ($context['can_issue_warning'] && !$context['user']['is_owner'] && $context['member']['warning'])
	{
		echo '
				<tr>
					<td><b>', $txt['profile_warning_level'], ': </b></td>
					<td><a href="', $scripturl, '?action=profile;u=', $context['id_member'], ';sa=issueWarning">', $context['member']['warning'], '%</a>';

		// Can we provide information on what this means?
		if (!empty($context['warning_status']))
			echo '
					<span class="smalltext">(', $context['warning_status'], ')</span>';

		echo '
					</td>
				</tr>';
	}

	// If the person looking is allowed, they can check the members IP address and hostname.
	if ($context['can_see_ip'])
	{
		echo '
				<tr>
					<td width="40%">
						<b>', $txt['ip'], ': </b>
					</td><td>
						<a href="', $scripturl, '?action=trackip;searchip=', $context['member']['ip'], '" target="_blank">', $context['member']['ip'], '</a>
					</td>
				</tr><tr>
					<td width="40%">
						<b>', $txt['hostname'], ': </b>
					</td><td width="55%">
						<div title="', $context['member']['hostname'], '" style="width: 100%; overflow: hidden; font-style: italic;">', $context['member']['hostname'], '</div>
					</td>
				</tr>';
	}

	// If karma enabled show the members karma.
	if ($modSettings['karmaMode'] == '1')
		echo '
				<tr>
					<td>
						<b>', $modSettings['karmaLabel'], ' </b>
					</td><td>
						', ($context['member']['karma']['good'] - $context['member']['karma']['bad']), '
					</td>
				</tr>';
	elseif ($modSettings['karmaMode'] == '2')
		echo '
				<tr>
					<td>
						<b>', $modSettings['karmaLabel'], ' </b>
					</td><td>
						+', $context['member']['karma']['good'], '/-', $context['member']['karma']['bad'], '
					</td>
				</tr>';
	echo '
				<tr>
					<td><b>', $txt['date_registered'], ': </b></td>
					<td>', $context['member']['registered'], '</td>
				</tr><tr>
					<td><b>', $txt['lastLoggedIn'], ': </b></td>
					<td>', $context['member']['last_login'], '</td>
				</tr>';

	// Is this member requiring activation and/or banned?
	if (!empty($context['activate_message']) || !empty($context['member']['bans']))
	{
		echo '
				<tr>
					<td colspan="2"><hr size="1" width="100%" class="hrcolor" /></td>
				</tr>';

		// If the person looking at the summary has permission, and the account isn't activated, give the viewer the ability to do it themselves.
		if (!empty($context['activate_message']))
			echo '
				<tr>
					<td colspan="2">
						<span style="color: red;">', $context['activate_message'], '</span>&nbsp;(<a href="' . $scripturl . '?action=profile;save;sa=activateAccount;u=' . $context['id_member'] . ';sesc=' . $context['session_id'] . '" ', ($context['activate_type'] == 4 ? 'onclick="return confirm(\'' . $txt['profileConfirm'] . '\');"' : ''), '>', $context['activate_link_text'], '</a>)
					</td>
				</tr>';

		// If the current member is banned, show a message and possibly a link to the ban.
		if (!empty($context['member']['bans']))
		{
			echo '
				<tr>
					<td colspan="2">
						<span style="color: red;">', $txt['user_is_banned'], '</span>&nbsp;[<a href="#" onclick="document.getElementById(\'ban_info\').style.display = document.getElementById(\'ban_info\').style.display == \'none\' ? \'\' : \'none\';return false;">' . $txt['view_ban'] . '</a>]
					</td>
				</tr>
				<tr id="ban_info" style="display: none;">
					<td colspan="2">
						<b>', $txt['user_banned_by_following'], ':</b>';

			foreach ($context['member']['bans'] as $ban)
				echo '
						<br /><span class="smalltext">', $ban['explanation'], '</span>';

			echo '
					</td>
				</tr>';
		}
	}

	// Messenger type information.
	echo '
				<tr>
					<td colspan="2"><hr size="1" width="100%" class="hrcolor" /></td>
				</tr>';

	if (!isset($context['disabled_fields']['icq']))
		echo '
				<tr>
					<td><b>', $txt['icq'], ':</b></td>
					<td>', $context['member']['icq']['link_text'], '</td>
				</tr>';

	if (!isset($context['disabled_fields']['aim']))
		echo '
				<tr>
					<td><b>', $txt['aim'], ': </b></td>
					<td>', $context['member']['aim']['link_text'], '</td>
				</tr>';

	if (!isset($context['disabled_fields']['msn']))
		echo '
				<tr>
					<td><b>', $txt['msn'], ': </b></td>
					<td>', $context['member']['msn']['link_text'], '</td>
				</tr>';

	if (!isset($context['disabled_fields']['yim']))
		echo '
				<tr>
					<td><b>', $txt['yim'], ': </b></td>
					<td>', $context['member']['yim']['link_text'], '</td>
				</tr>';

	echo '
				<tr>
					<td><b>', $txt['email'], ': </b></td>
					<td>';

	// Only show the email address fully if it's not hidden - and we reveal the email.
	if ($context['member']['show_email'] == 'yes')
		echo '
						<a href="mailto:', $context['member']['email'], '">', $context['member']['email'], '</a>';
	// What about if we allow email only via the forum??
	elseif ($context['member']['show_email'] == 'no_through_forum')
		echo '
						<a href="', $scripturl, '?action=emailuser;sa=email;uid=', $context['member']['id'], '"><img src="', $settings['images_url'], '/email_sm.gif" alt="', $txt['email'], '" /></a>';

	// ... Or if the one looking at the profile is an admin they can see it anyway.
	elseif ($context['member']['show_email'] == 'yes_permission_override')
		echo '
						<i><a href="mailto:', $context['member']['email'], '">', $context['member']['email'], '</a></i>';

	// That must mean the email is hidden.
	else
		echo '
						<i>', $txt['hidden'], '</i>';

	// Some more information.
	echo '
					</td>
				</tr>';

	if (!isset($context['disabled_fields']['website']))
		echo '
				<tr>
					<td><b>', $txt['website'], ': </b></td>
					<td><a href="', $context['member']['website']['url'], '" target="_blank">', $context['member']['website']['title'], '</a></td>
				</tr>';

	echo '
				<tr>
					<td><b>', $txt['current_status'], ' </b></td>
					<td>
						<i>', $context['can_send_pm'] ? '<a href="' . $context['member']['online']['href'] . '" title="' . $context['member']['online']['label'] . '">' : '', $settings['use_image_buttons'] ? '<img src="' . $context['member']['online']['image_href'] . '" alt="' . $context['member']['online']['text'] . '" align="middle" />' : $context['member']['online']['text'], $context['can_send_pm'] ? '</a>' : '', $settings['use_image_buttons'] ? '<span class="smalltext"> ' . $context['member']['online']['text'] . '</span>' : '', '</i>';

	// Can they add this member as a buddy?
	if (!empty($context['can_have_buddy']) && !$context['user']['is_owner'])
		echo '
						&nbsp;&nbsp;<a href="', $scripturl, '?action=buddy;u=', $context['id_member'], ';sesc=', $context['session_id'], '">[', $txt['buddy_' . ($context['member']['is_buddy'] ? 'remove' : 'add')], ']</a>';

	echo '
					</td>
				</tr><tr>
					<td colspan="2"><hr size="1" width="100%" class="hrcolor" /></td>
				</tr>';

	if (!isset($context['disabled_fields']['gender']))
		echo '
				<tr>
					<td><b>', $txt['gender'], ': </b></td>
					<td>', $context['member']['gender']['name'], '</td>
				</tr>';

	echo '
				<tr>
					<td><b>', $txt['age'], ':</b></td>
					<td>', $context['member']['age'] . ($context['member']['today_is_birthday'] ? ' &nbsp; <img src="' . $settings['images_url'] . '/bdaycake.gif" width="40" alt="" />' : ''), '</td>
				</tr>';

	if (!isset($context['disabled_fields']['location']))
		echo '
				<tr>
					<td><b>', $txt['location'], ':</b></td>
					<td>', $context['member']['location'], '</td>
				</tr>';

	echo '
				<tr>
					<td><b>', $txt['local_time'], ':</b></td>
					<td>', $context['member']['local_time'], '</td>
				</tr><tr>';

	if (!empty($modSettings['userLanguage']))
		echo '
					<td><b>', $txt['language'], ':</b></td>
					<td>', $context['member']['language'], '</td>
				</tr><tr>';

	echo '
					<td colspan="2"><hr size="1" width="100%" class="hrcolor" /></td>
				</tr>';

	// Are there any custom profile fields for the summary?
	if (!empty($context['custom_fields']))
	{
		foreach ($context['custom_fields'] as $field)
		{
			echo '
				<tr valign="top">
					<td><b>', $field['name'], ':</b></td>
					<td>', $field['output_html'], '</td>
				</tr>';
		}

		echo '
				<tr>
					<td colspan="2"><hr size="1" width="100%" class="hrcolor" /></td>
				</tr>';
	}

	// Show the users signature.
	if ($context['signature_enabled'])
		echo '
				<tr>
					<td colspan="2" height="25">
						<table width="100%" cellpadding="0" cellspacing="0" border="0" style="table-layout: fixed;">
							<tr>
								<td style="padding-bottom: 0.5ex;"><b>', $txt['signature'], ':</b></td>
							</tr><tr>
								<td colspan="2" width="100%" class="smalltext"><div class="signature">', $context['member']['signature'], '</div></td>
							</tr>
						</table>
					</td>
				</tr>';

	echo '
			</table>
		</td>';

	// Now print the second column where the members avatar/text is shown.
	echo '
		<td class="windowbg" valign="middle" align="center" width="150">
			', $context['member']['avatar']['image'], '<br /><br />
			', $context['member']['blurb'], '
		</td>
	</tr>';

	// Finally, if applicable, span the bottom of the table with links to other useful member functions.
	echo '
	<tr class="titlebg">
		<td colspan="2">', $txt['additional_info'], ':</td>
	</tr>
	<tr>
		<td class="windowbg2" colspan="2">';
	if (!$context['user']['is_owner'] && $context['can_send_pm'])
		echo '
			<a href="', $scripturl, '?action=pm;sa=send;u=', $context['id_member'], '">', $txt['send_member_pm'], '.</a><br />
			<br />';
	echo '
			<a href="', $scripturl, '?action=profile;u=', $context['id_member'], ';sa=showPosts">', $txt['show_latest'], ' ', $txt['posts_member'], '.</a><br />
			<a href="', $scripturl, '?action=profile;u=', $context['id_member'], ';sa=statPanel">', $txt['statPanel_show'], '.</a><br />
			<br />
		</td>
	</tr>
</table>';
}

// Template for showing all the posts of the user, in chronological order.
function template_showPosts()
{
	global $context, $settings, $options, $scripturl, $modSettings, $txt;

	echo '
		<table border="0" width="85%" cellspacing="1" cellpadding="4" class="bordercolor" align="center">
			<tr class="titlebg">
				<td colspan="3" height="26">
					&nbsp;<img src="', $settings['images_url'], '/icons/profile_sm.gif" alt="" align="top" />&nbsp;', (!isset($context['attachments']) && empty($context['is_topics']) ? $txt['showPosts'] : (!empty($context['is_topics']) ? $txt['showTopics'] : $txt['showAttachments'])), ' - ', $context['member']['name'], '
				</td>
			</tr>
			<tr class="windowbg" valign="middle">
				<td colspan="3">
					<span>
						', (isset($context['attachments']) || !empty($context['is_topics']) ? '' : '<img src="' . $settings['images_url'] . '/selected.gif" width="12" height="12" alt="*" /> '), '<a href="', $scripturl, '?action=profile;u=', $context['current_member'], ';sa=showPosts">', $txt['show_member_posts'], '</a> |
						', (empty($context['is_topics']) ? '' : '<img src="' . $settings['images_url'] . '/selected.gif" width="12" height="12" alt="*" /> '), '<a href="', $scripturl, '?action=profile;u=', $context['current_member'], ';sa=showPosts;topics">', $txt['show_member_topics'], '</a> |
						', (!isset($context['attachments']) ? '' : '<img src="' . $settings['images_url'] . '/selected.gif" width="12" height="12" alt="*" /> '), '<a href="', $scripturl, '?action=profile;u=', $context['current_member'], ';sa=showPosts;attach">', $txt['show_member_attachments'], '</a>
					</span>
				</td>
			</tr>
			<tr class="catbg3">
				<td colspan="3">
					', $txt['pages'], ': ', $context['page_index'], '
				</td>
			</tr>
		</table>';

	// Button shortcuts
	$quote_button = create_button('quote.gif', 'reply_quote', 'quote', 'align="middle"');
	$reply_button = create_button('reply_sm.gif', 'reply', 'reply', 'align="middle"');
	$remove_button = create_button('delete.gif', 'remove_message', 'remove', 'align="middle"');
	$notify_button = create_button('notify_sm.gif', 'notify_replies', 'notify', 'align="middle"');

	// Are we displaying posts or attachments?
	if (!isset($context['attachments']))
	{
		// For every post to be displayed, give it its own subtable, and show the important details of the post.
		foreach ($context['posts'] as $post)
		{
			echo '
		<table border="0" width="85%" cellspacing="1" cellpadding="0" class="bordercolor" align="center">
			<tr>
				<td width="100%">
					<table border="0" width="100%" cellspacing="0" cellpadding="4" class="bordercolor" align="center">
						<tr class="titlebg2">
							<td style="padding: 0 1ex;">
								', $post['counter'], '
							</td>
							<td width="75%" class="middletext">
								&nbsp;<a href="', $scripturl, '#', $post['category']['id'], '">', $post['category']['name'], '</a> / <a href="', $scripturl, '?board=', $post['board']['id'], '.0">', $post['board']['name'], '</a> / <a href="', $scripturl, '?topic=', $post['topic'], '.', $post['start'], '#msg', $post['id'], '">', $post['subject'], '</a>
							</td>
							<td class="middletext" align="right" style="padding: 0 1ex; white-space: nowrap;">
								', $txt['on'], ': ', $post['time'], '
							</td>
						</tr>
						<tr>
							<td width="100%" height="80" colspan="3" valign="top" class="windowbg2">
								<div class="post">', $post['body'], '</div>
							</td>
						</tr>
						<tr>
							<td colspan="3" class="windowbg2" align="', !$context['right_to_left'] ? 'right' : 'left', '"><span class="middletext">';

			if ($post['can_delete'])
				echo '
					<a href="', $scripturl, '?action=profile;u=', $context['current_member'], ';sa=showPosts;start=', $context['start'], ';delete=', $post['id'], ';sesc=', $context['session_id'], '" onclick="return confirm(\'', $txt['remove_message'], '?\');">', $remove_button, '</a>';
			if ($post['can_delete'] && ($post['can_mark_notify'] || $post['can_reply']))
				echo '
								', $context['menu_separator'];
			if ($post['can_reply'])
				echo '
					<a href="', $scripturl, '?action=post;topic=', $post['topic'], '.', $post['start'], '">', $reply_button, '</a>', $context['menu_separator'], '
					<a href="', $scripturl, '?action=post;topic=', $post['topic'], '.', $post['start'], ';quote=', $post['id'], ';sesc=', $context['session_id'], '">', $quote_button, '</a>';
			if ($post['can_reply'] && $post['can_mark_notify'])
				echo '
								', $context['menu_separator'];
			if ($post['can_mark_notify'])
				echo '
					<a href="' . $scripturl . '?action=notify;topic=' . $post['topic'] . '.' . $post['start'] . '">' . $notify_button . '</a>';

			echo '
							</span></td>
						</tr>
					</table>
				</td>
			</tr>
		</table>';
		}
	}
	else
	{
		echo '
		<table border="0" width="85%" cellspacing="1" cellpadding="2" class="bordercolor" align="center">
			<tr class="titlebg">
				<td width="25%">
					<a href="', $scripturl, '?action=profile;u=', $context['current_member'], ';sa=showPosts;attach;sort=filename', ($context['sort_direction'] == 'down' && $context['sort_order'] == 'filename' ? ';asc' : ''), '">
						', $txt['show_attach_filename'], '
						', ($context['sort_order'] == 'filename' ? '<img src="' . $settings['images_url'] . '/sort_' . ($context['sort_direction'] == 'down' ? 'down' : 'up') . '.gif" alt="" />' : ''), '
					</a>
				</td>
				<td width="12%" align="center">
					<a href="', $scripturl, '?action=profile;u=', $context['current_member'], ';sa=showPosts;attach;sort=downloads', ($context['sort_direction'] == 'down' && $context['sort_order'] == 'downloads' ? ';asc' : ''), '">
						', $txt['show_attach_downloads'], '
						', ($context['sort_order'] == 'downloads' ? '<img src="' . $settings['images_url'] . '/sort_' . ($context['sort_direction'] == 'down' ? 'down' : 'up') . '.gif" alt="" />' : ''), '
					</a>
				</td>
				<td width="30%">
					<a href="', $scripturl, '?action=profile;u=', $context['current_member'], ';sa=showPosts;attach;sort=subject', ($context['sort_direction'] == 'down' && $context['sort_order'] == 'subject' ? ';asc' : ''), '">
						', $txt['message'], '
						', ($context['sort_order'] == 'subject' ? '<img src="' . $settings['images_url'] . '/sort_' . ($context['sort_direction'] == 'down' ? 'down' : 'up') . '.gif" alt="" />' : ''), '
					</a>
				</td>
				<td>
					<a href="', $scripturl, '?action=profile;u=', $context['current_member'], ';sa=showPosts;attach;sort=posted', ($context['sort_direction'] == 'down' && $context['sort_order'] == 'posted' ? ';asc' : ''), '">
					', $txt['show_attach_posted'], '
					', ($context['sort_order'] == 'posted' ? '<img src="' . $settings['images_url'] . '/sort_' . ($context['sort_direction'] == 'down' ? 'down' : 'up') . '.gif" alt="" />' : ''), '
					</a>
				</td>
			</tr>';

		// Looks like we need to do all the attachments instead!
		$alternate = false;
		foreach ($context['attachments'] as $attachment)
		{
			echo '
			<tr class="', $alternate ? 'windowbg' : 'windowbg2', '">
				<td><a href="', $scripturl, '?action=dlattach;topic=', $attachment['topic'], '.0;attach=', $attachment['id'], '">', $attachment['filename'], '</a></td>
				<td align="center">', $attachment['downloads'], '</td>
				<td><a href="', $scripturl, '?topic=', $attachment['topic'], '.msg', $attachment['msg'], '#msg', $attachment['msg'], '" rel="nofollow">', $attachment['subject'], '</a></td>
				<td>', $attachment['posted'], '</td>
			</tr>';
			$alternate = !$alternate;
		}

		echo '
		</table>';
	}

	// Start the bottom bit.
	echo '
		<table border="0" width="85%" cellspacing="1" cellpadding="4" class="bordercolor" align="center">';

	// No posts? Just end the table with a informative message.
	if ((isset($context['attachments']) && empty($context['attachments'])) || (!isset($context['attachments']) && empty($context['posts'])))
		echo '
			<tr class="windowbg2">
				<td align="center">
					', isset($context['attachments']) ? $txt['show_attachments_none'] : $txt['show_posts_none'], '
				</td>
			</tr>';

	// Show more page numbers.
	echo '
				<tr>
				<td colspan="3" class="catbg3">
					', $txt['pages'], ': ', $context['page_index'], '
				</td>
			</tr>
		</table>';
}

// Template for showing all the buddies of the current user.
function template_editBuddies()
{
	global $context, $settings, $options, $scripturl, $modSettings, $txt;

	echo '
		<table border="0" width="85%" cellspacing="1" cellpadding="4" class="bordercolor" align="center">
			<tr class="titlebg">
				<td colspan="8" height="26">
					&nbsp;<img src="', $settings['images_url'], '/icons/profile_sm.gif" alt="" align="top" />&nbsp;', $txt['editBuddies'], '
				</td>
			</tr>
			<tr class="catbg3">
				<td width="20%">', $txt['name'], '</td>
				<td>', $txt['status'], '</td>
				<td>', $txt['email'], '</td>
				<td align="center">', $txt['icq'], '</td>
				<td align="center">', $txt['aim'], '</td>
				<td align="center">', $txt['yim'], '</td>
				<td align="center">', $txt['msn'], '</td>
				<td></td>
			</tr>';

	// If they don't have any buddies don't list them!
	if (empty($context['buddies']))
		echo '
			<tr class="windowbg">
				<td colspan="8" align="center"><b>', $txt['no_buddies'], '</b></td>
			</tr>';

	// Now loop through each buddy showing info on each.
	$alternate = false;
	foreach ($context['buddies'] as $buddy)
	{
		echo '
			<tr class="', $alternate ? 'windowbg' : 'windowbg2', '">
				<td>', $buddy['link'], '</td>
				<td align="center"><a href="', $buddy['online']['href'], '"><img src="', $buddy['online']['image_href'], '" alt="', $buddy['online']['label'], '" title="', $buddy['online']['label'], '" /></a></td>
				<td align="center">', ($buddy['show_email'] == 'no' ? '' : '<a href="' . ($buddy['show_email'] == 'no_through_forum' ? $scripturl . '?action=emailuser;sa=email;uid=' . $buddy['id'] : 'mailto:' . $buddy['email']) . '"><img src="' . $settings['images_url'] . '/email_sm.gif" alt="' . $txt['email'] . '" title="' . $txt['email'] . ' ' . $buddy['name'] . '" /></a>'), '</td>
				<td align="center">', $buddy['icq']['link'], '</td>
				<td align="center">', $buddy['aim']['link'], '</td>
				<td align="center">', $buddy['yim']['link'], '</td>
				<td align="center">', $buddy['msn']['link'], '</td>
				<td align="center"><a href="', $scripturl, '?action=profile;u=', $context['id_member'], ';sa=editBuddies;remove=', $buddy['id'], '"><img src="', $settings['images_url'], '/icons/delete.gif" alt="', $txt['buddy_remove'], '" title="', $txt['buddy_remove'], '" /></a></td>
			</tr>';

		$alternate = !$alternate;
	}

	echo '
		</table>';

	// Add a new buddy?
	echo '
	<br />
	<form action="', $scripturl, '?action=profile;u=', $context['id_member'], ';sa=editBuddies" method="post" accept-charset="', $context['character_set'], '">
		<table width="65%" cellpadding="4" cellspacing="0" class="tborder" align="center">
			<tr class="titlebg">
				<td colspan="2">', $txt['buddy_add'], '</td>
			</tr>
			<tr class="windowbg">
				<td width="45%">
					<b>', $txt['who_member'], ':</b>
				</td>
				<td width="55%">
					<input type="text" name="new_buddy" id="new_buddy" size="25" />
					<a href="', $scripturl, '?action=findmember;input=new_buddy;quote=1;sesc=', $context['session_id'], '" onclick="return reqWin(this.href, 350, 400);"><img src="', $settings['images_url'], '/icons/assist.gif" alt="', $txt['find_members'], '" align="top" /></a>
				</td>
			</tr>
			<tr class="windowbg">
				<td colspan="2" align="right">
					<input type="submit" value="', $txt['buddy_add_button'], '" />
				</td>
			</tr>
		</table>
	</form>';
}

// This template shows an admin information on a users IP addresses used and errors attributed to them.
function template_trackUser()
{
	global $context, $settings, $options, $scripturl, $txt;

	// The first table shows IP information about the user.
	echo '
		<table cellpadding="0" cellspacing="0" border="0" class="bordercolor" align="center" width="90%"><tr><td>
			<table border="0" cellspacing="1" cellpadding="4" align="left" width="100%">
				<tr class="titlebg">
					<td colspan="2">
						<b>', $txt['view_ips_by'], ' ', $context['member']['name'], '</b>
					</td>
				</tr>';

	// The last IP the user used.
	echo '
				<tr valign="top">
					<td class="windowbg2" align="left" width="300">', $txt['most_recent_ip'], ':
						', (empty($context['last_ip2']) ? '' : '<br /><span class="smalltext">(<a href="' . $scripturl . '?action=helpadmin;help=whytwoip" onclick="return reqWin(this.href);">' . $txt['why_two_ip_address'] . '</a>)</span>'), '
					</td>
					<td class="windowbg2" align="left">
						<a href="', $scripturl, '?action=trackip;searchip=', $context['last_ip'], ';">', $context['last_ip'], '</a>';

	// Second address detected?
	if (!empty($context['last_ip2']))
		echo '
						, <a href="', $scripturl, '?action=trackip;searchip=', $context['last_ip2'], ';">', $context['last_ip2'], '</a>';

	echo '
					</td>
				</tr>';

	// Lists of IP addresses used in messages / error messages.
	echo '
				<tr>
					<td class="windowbg2" align="left">', $txt['ips_in_messages'], ':</td>
					<td class="windowbg2" align="left">
						', (count($context['ips']) > 0 ? implode(', ', $context['ips']) : '(' . $txt['none'] . ')'), '
					</td>
				</tr><tr>
					<td class="windowbg2" align="left">', $txt['ips_in_errors'], ':</td>
					<td class="windowbg2" align="left">
						', (count($context['ips']) > 0 ? implode(', ', $context['error_ips']) : '(' . $txt['none'] . ')'), '
					</td>
				</tr>';

	// List any members that have used the same IP addresses as the current member.
	echo '
				<tr>
					<td class="windowbg2" align="left">', $txt['members_in_range'], ':</td>
					<td class="windowbg2" align="left">
						', (count($context['members_in_range']) > 0 ? implode(', ', $context['members_in_range']) : '(' . $txt['none'] . ')'), '
					</td>
				</tr>
			</table>
		</td></tr></table>
		<br />';

	// The second table lists all the error messages the user has caused/received.
	echo '
		<table cellpadding="0" cellspacing="0" border="0" class="bordercolor" align="center" width="90%"><tr><td>
			<table border="0" cellspacing="1" cellpadding="4" align="center" width="100%">
				<tr class="titlebg">
					<td colspan="4">
						', $txt['errors_by'], ' ', $context['member']['name'], '
					</td>
				</tr><tr class="windowbg">
					<td class="smalltext" colspan="4" style="padding: 2ex;">
						', $txt['errors_desc'], '
					</td>
				</tr><tr class="titlebg">
					<td colspan="4">
						', $txt['pages'], ': ', $context['page_index'], '
					</td>
				</tr><tr class="catbg3">
					<td>', $txt['ip_address'], '</td>
					<td>', $txt['message'], '</td>
					<td>', $txt['date'], '</td>
				</tr>';

	// If there arn't any messages just give a message stating this.
	if (empty($context['error_messages']))
		echo '
				<tr><td class="windowbg2" colspan="4"><i>', $txt['no_errors_from_user'], '</i></td></tr>';

	// Otherwise print every error message out.
	else
		// For every error message print the IP address that caused it, the message displayed and the date it occurred.
		foreach ($context['error_messages'] as $error)
			echo '
				<tr>
					<td class="windowbg2">
						<a href="', $scripturl, '?action=trackip;searchip=', $error['ip'], ';">', $error['ip'], '</a>
					</td>
					<td class="windowbg2">
						', $error['message'], '<br />
						<a href="', $error['url'], '">', $error['url'], '</a>
					</td>
					<td class="windowbg2">', $error['time'], '</td>
				</tr>';
	echo '
			</table>
		</td></tr></table>';
}

// The template for trackIP, allowing the admin to see where/who a certain IP has been used.
function template_trackIP()
{
	global $context, $settings, $options, $scripturl, $txt;

	// This function always defaults to the last IP used by a member but can be set to track any IP.
	echo '
		<form action="', $scripturl, '?action=trackip" method="post" accept-charset="', $context['character_set'], '">';

	// The first table in the template gives an input box to allow the admin to enter another IP to track.
	echo '
			<table cellpadding="0" cellspacing="0" border="0" class="bordercolor" align="center" width="90%"><tr><td>
				<table border="0" cellspacing="1" cellpadding="4" align="center" width="100%">
					<tr class="titlebg">
						<td>', $txt['trackIP'], '</td>
					</tr><tr>
						<td class="windowbg2">
							', $txt['enter_ip'], ':&nbsp;&nbsp;<input type="text" name="searchip" value="', $context['ip'], '" />&nbsp;&nbsp;<input type="submit" value="', $txt['trackIP'], '" />
						</td>
					</tr>
				</table>
			</td></tr></table>
		</form>
		<br />';

	// The table inbetween the first and second table shows links to the whois server for every region.
	if ($context['single_ip'])
	{
		echo '
		<table cellpadding="0" cellspacing="0" border="0" class="bordercolor" align="center" width="90%"><tr><td>
			<table border="0" cellspacing="1" cellpadding="4" align="center" width="100%">
				<tr class="titlebg">
					<td colspan="2">
						', $txt['whois_title'], ' ', $context['ip'], '
					</td>
				</tr><tr>
					<td class="windowbg2">';
		foreach ($context['whois_servers'] as $server)
			echo '
						<a href="', $server['url'], '" target="_blank"', isset($context['auto_whois_server']) && $context['auto_whois_server']['name'] == $server['name'] ? ' style="font-weight: bold;"' : '', '>', $server['name'], '</a><br />';
		echo '
					</td>
				</tr>
			</table>
		</td></tr></table>
		<br />';
	}

	// The second table lists all the members who have been logged as using this IP address.
	echo '
		<table cellpadding="0" cellspacing="0" border="0" class="bordercolor" align="center" width="90%"><tr><td>
			<table border="0" cellspacing="1" cellpadding="4" align="center" width="100%">
				<tr class="titlebg">
					<td colspan="2">
						', $txt['members_from_ip'], ' ', $context['ip'], '
					</td>
				</tr><tr class="catbg3">
					<td>', $txt['ip_address'], '</td>
					<td>', $txt['display_name'], '</td>
				</tr>';
	if (empty($context['ips']))
		echo '
				<tr><td class="windowbg2" colspan="2"><i>', $txt['no_members_from_ip'], '</i></td></tr>';
	else
		// Loop through each of the members and display them.
		foreach ($context['ips'] as $ip => $memberlist)
			echo '
				<tr>
					<td class="windowbg2"><a href="', $scripturl, '?action=trackip;searchip=', $ip, ';">', $ip, '</a></td>
					<td class="windowbg2">', implode(', ', $memberlist), '</td>
				</tr>';
	echo '
			</table>
		</td></tr></table>
		<br />';

	// The third table in the template displays a list of all the messages sent using this IP (can be quite long).
	echo '
		<table cellpadding="0" cellspacing="0" border="0" class="bordercolor" align="center" width="90%"><tr><td>
			<table border="0" cellspacing="1" cellpadding="4" align="center" width="100%">
				<tr class="titlebg">
					<td colspan="4">
						', $txt['messages_from_ip'], ' ', $context['ip'], '
					</td>
				</tr><tr class="windowbg">
					<td class="smalltext" colspan="4" style="padding: 2ex;">
						', $txt['messages_from_ip_desc'], '
					</td>
				</tr><tr class="titlebg">
					<td colspan="4">
						<b>', $txt['pages'], ':</b> ', $context['message_page_index'], '
					</td>
				</tr><tr class="catbg3">
					<td>', $txt['ip_address'], '</td>
					<td>', $txt['poster'], '</td>
					<td>', $txt['subject'], '</td>
					<td>', $txt['date'], '</td>
				</tr>';

	// No message means nothing to do!
	if (empty($context['messages']))
		echo '
				<tr><td class="windowbg2" colspan="4"><i>', $txt['no_messages_from_ip'], '</i></td></tr>';
	else
		// For every message print the IP, member who posts it, subject (with link) and date posted.
		foreach ($context['messages'] as $message)
			echo '
				<tr>
					<td class="windowbg2">
						<a href="', $scripturl, '?action=trackip;searchip=', $message['ip'], '">', $message['ip'], '</a>
					</td>
					<td class="windowbg2">
						', $message['member']['link'], '
					</td>
					<td class="windowbg2">
						<a href="', $scripturl, '?topic=', $message['topic'], '.msg', $message['id'], '#msg', $message['id'], '" rel="nofollow">
							', $message['subject'], '
						</a>
					</td>
					<td class="windowbg2">', $message['time'], '</td>
				</tr>';
	echo '
			</table>
		</td></tr></table>
		<br />';

	// The final table in the template lists all the error messages caused/received by anyone using this IP address.
	echo '
		<table cellpadding="0" cellspacing="0" border="0" class="bordercolor" align="center" width="90%"><tr><td>
			<table border="0" cellspacing="1" cellpadding="4" align="center" width="100%">
				<tr class="titlebg">
					<td colspan="4">
						', $txt['errors_from_ip'], ' ', $context['ip'], '
					</td>
				</tr><tr class="windowbg">
					<td class="smalltext" colspan="4" style="padding: 2ex;">
						', $txt['errors_from_ip_desc'], '
					</td>
				</tr><tr class="titlebg">
					<td colspan="4">
						', $txt['pages'], ': ', $context['error_page_index'], '
					</td>
				</tr><tr class="catbg3">
					<td>', $txt['ip_address'], '</td>
					<td>', $txt['display_name'], '</td>
					<td>', $txt['message'], '</td>
					<td>', $txt['date'], '</td>
				</tr>';
	if (empty($context['error_messages']))
		echo '
				<tr><td class="windowbg2" colspan="4"><i>', $txt['no_errors_from_ip'], '</i></td></tr>';
	else
		// For each error print IP address, member, message received and date caused.
		foreach ($context['error_messages'] as $error)
			echo '
				<tr>
					<td class="windowbg2">
						<a href="', $scripturl, '?action=trackip;searchip=', $error['ip'], '">', $error['ip'], '</a>
					</td>
					<td class="windowbg2">
						', $error['member']['link'], '
					</td>
					<td class="windowbg2">
						', $error['message'], '<br />
						<a href="', $error['url'], '">', $error['url'], '</a>
					</td>
					<td class="windowbg2">', $error['error_time'], '</td>
				</tr>';
	echo '
			</table>
		</td></tr></table>';
}

function template_showPermissions()
{
	global $context, $settings, $options, $scripturl, $txt;

	echo '
		<table width="90%" border="0" cellspacing="1" cellpadding="4" align="center" class="bordercolor">
			<tr class="titlebg">
				<td colspan="2" height="26">
					&nbsp;<img src="', $settings['images_url'], '/icons/profile_sm.gif" alt="" align="top" />&nbsp;', $txt['showPermissions'], '
					</td>
			</tr>';
	if ($context['member']['has_all_permissions'])
	{
		echo '
			<tr class="windowbg2">
				<td colspan="2">', $txt['showPermissions_all'], '</td>
			</tr>';
	}
	else
	{
		if (!empty($context['no_access_boards']))
		{
			echo '
			<tr class="catbg">
				<td align="left" colspan="2">', $txt['showPermissions_restricted_boards'], '</td>
			</tr><tr class="windowbg">
				<td colspan="2" class="smalltext">
					', $txt['showPermissions_restricted_boards_desc'], ':<br />';
			foreach ($context['no_access_boards'] as $no_access_board)
				echo '
					<a href="', $scripturl, '?board=', $no_access_board['id'], '">', $no_access_board['name'], '</a>', $no_access_board['is_last'] ? '' : ', ';
			echo '
				</td>
			</tr>';
		}

		// General Permissions section.
		echo '
			<tr class="catbg">
				<td align="left" colspan="2">', $txt['showPermissions_general'], '</td>
			</tr>';
		if (!empty($context['member']['permissions']['general']))
		{
			echo '
			<tr class="titlebg">
				<td width="50%">', $txt['showPermissions_permission'], '</td>
				<td width="50%"></td>
			</tr>';

			foreach ($context['member']['permissions']['general'] as $permission)
			{
				echo '
			<tr>
				<td class="windowbg" valign="top">
					', $permission['is_denied'] ? '<del>' . $permission['id'] . '</del>' : $permission['id'], '<br />
					<span class="smalltext">', $permission['name'], '</span>
				</td>
				<td class="windowbg2" valign="top"><span class="smalltext">';
				if ($permission['is_denied'])
					echo '
					<span style="color: red; font-weight: bold;">', $txt['showPermissions_denied'], ': </span>', implode(', ', $permission['groups']['denied']);
				else
					echo '
					<span style="font-weight: bold;">', $txt['showPermissions_given'], ': </span>', implode(', ', $permission['groups']['allowed']);
				echo '
				</span></td>
			</tr>';
			}
		}
		else
			echo '
			<tr class="windowbg2">
				<td colspan="2">', $txt['showPermissions_none_general'], '</td>
			</tr>';

		// Board permission section.
		echo '
			<tr class="catbg">
				<td align="left" colspan="2">
					<a name="board_permissions"></a>
					<form action="' . $scripturl . '?action=profile;u=', $context['id_member'], ';sa=showPermissions#board_permissions" method="post" accept-charset="', $context['character_set'], '">
						', $txt['showPermissions_select'], ':
						<select name="board" onchange="if (this.options[this.selectedIndex].value) this.form.submit();">
							<option value="0"', $context['board'] == 0 ? ' selected="selected"' : '', '>', $txt['showPermissions_global'], '</option>';
		if (!empty($context['boards']))
			echo '
							<option value="" disabled="disabled">---------------------------</option>';

		// Fill the box with any local permission boards.
		foreach ($context['boards'] as $board)
			echo '
							<option value="', $board['id'], '"', $board['selected'] ? ' selected="selected"' : '', '>', $board['name'], ' (', $board['profile_name'], ')</option>';

		echo '
						</select>
					</form>
				</td>
			</tr>';
		if (!empty($context['member']['permissions']['board']))
		{
			echo '
			<tr class="titlebg">
				<td>', $txt['showPermissions_permission'], '</td>
				<td></td>
			</tr>';
			foreach ($context['member']['permissions']['board'] as $permission)
			{
				echo '
			<tr>
				<td class="windowbg" valign="top">
					', $permission['is_denied'] ? '<del>' . $permission['id'] . '</del>' : $permission['id'], '<br />
					<span class="smalltext">', $permission['name'], '</span>
				</td>
				<td class="windowbg2" valign="top"><span class="smalltext">';
				if ($permission['is_denied'])
				{
					echo '
					<span style="color: red; font-weight: bold;">', $txt['showPermissions_denied'], ': </span>', implode(', ', $permission['groups']['denied']), '<br />';
				}
				else
				{
					echo '
					<span style="font-weight: bold;">', $txt['showPermissions_given'], ': </span>', implode(', ', $permission['groups']['allowed']), '<br />';
				}
				echo '
				</span></td>
			</tr>';
			}
		}
		else
			echo '
			<tr class="windowbg2">
				<td colspan="2">', $txt['showPermissions_none_board'], '</td>
			</tr>';
	}
	echo '
		</table><br />';
}

// Template for user statistics, showing graphs and the like.
function template_statPanel()
{
	global $context, $settings, $options, $scripturl, $modSettings, $txt;

	echo '
		<table border="0" width="85%" cellspacing="1" cellpadding="4" class="bordercolor" align="center">
			<tr class="titlebg">
				<td colspan="4" height="26">&nbsp;<img src="', $settings['images_url'], '/icons/profile_sm.gif" alt="" align="top" />&nbsp;', $txt['statPanel_generalStats'], ' - ', $context['member']['name'], '</td>
			</tr>';

	// First, show a few text statistics such as post/topic count.
	echo '
		<tr>
				<td class="windowbg" width="20" valign="middle" align="center"><img src="', $settings['images_url'], '/stats_info.gif" width="20" height="20" alt="" /></td>
				<td class="windowbg2" valign="top" colspan="3">
					<table border="0" cellpadding="1" cellspacing="0" width="100%">
						<tr>
							<td nowrap="nowrap">', $txt['statPanel_total_time_online'], ':</td>
							<td align="right">', $context['time_logged_in'], '</td>
						</tr><tr>
							<td nowrap="nowrap">', $txt['statPanel_total_posts'], ':</td>
							<td align="right">', $context['num_posts'], ' ', $txt['statPanel_posts'], '</td>
						</tr><tr>
							<td nowrap="nowrap">', $txt['statPanel_total_topics'], ':</td>
							<td align="right">', $context['num_topics'], ' ', $txt['statPanel_topics'], '</td>
						</tr><tr>
							<td nowrap="nowrap">', $txt['statPanel_users_polls'], ':</td>
							<td align="right">', $context['num_polls'], ' ', $txt['statPanel_polls'], '</td>
						</tr><tr>
							<td nowrap="nowrap">', $txt['statPanel_users_votes'], ':</td>
							<td align="right">', $context['num_votes'], ' ', $txt['statPanel_votes'], '</td>
						</tr>
					</table>
				</td>
			</tr>';

	// This next section draws a graph showing what times of day they post the most.
	echo '
			<tr class="titlebg">
				<td colspan="4" width="100%">', $txt['statPanel_activityTime'], '</td>
			</tr><tr>
				<td class="windowbg" width="20" valign="middle" align="center"><img src="', $settings['images_url'], '/stats_views.gif" width="20" height="20" alt="" /></td>
				<td colspan="3" class="windowbg2" width="100%" valign="top">
					<table border="0" cellpadding="0" cellspacing="0" width="100%">';

	// If they haven't post at all, don't draw the graph.
	if (empty($context['posts_by_time']))
		echo '
						<tr>
							<td width="100%" valign="top" align="center">', $txt['statPanel_noPosts'], '</td>
						</tr>';
	// Otherwise do!
	else
	{
		echo '
						<tr>
							<td width="2%" valign="bottom"></td>';

		// Loops through each hour drawing the bar to the correct height.
		foreach ($context['posts_by_time'] as $time_of_day)
			echo '
							<td width="4%" valign="bottom" align="center"><img src="', $settings['images_url'], '/bar.gif" width="12" height="', $time_of_day['posts_percent'], '" alt="" /></td>';
		echo '
							<td width="2%" valign="bottom"></td>
						</tr><tr>
							<td width="2%" valign="bottom"></td>';
		// The labels.
		foreach ($context['posts_by_time'] as $time_of_day)
			echo '
							<td width="4%" valign="bottom" align="center" style="border-color: black; border-style: solid; border-width: 1px ', $time_of_day['hour'] != 23 ? '1px' : '0px', ' 0px 0px">', $time_of_day['hour'], '</td>';
		echo '
							<td width="2%" valign="bottom"></td>
						</tr><tr>
							<td width="100%" colspan="26" align="center"><b>', $txt['statPanel_timeOfDay'], '</b></td>
						</tr>';
	}
	echo '
					</table>
				</td>
			</tr>';

	// The final section is two columns with the most popular boards by posts and activity (activity = users posts / total posts).
	echo '
			<tr class="titlebg">
				<td colspan="2" width="50%">', $txt['statPanel_topBoards'], '</td>
				<td colspan="2" width="50%">', $txt['statPanel_topBoardsActivity'], '</td>
			</tr><tr>
				<td class="windowbg" width="20" valign="middle" align="center"><img src="', $settings['images_url'], '/stats_replies.gif" width="20" height="20" alt="" /></td>
				<td class="windowbg2" width="50%" valign="top">
					<table border="0" cellpadding="1" cellspacing="0" width="100%">';
	if (empty($context['popular_boards']))
		echo '
						<tr>
							<td width="100%" valign="top" align="center">', $txt['statPanel_noPosts'], '</td>
						</tr>';
	else
	{
		// Draw a bar for every board.
		foreach ($context['popular_boards'] as $board)
		{
			echo '
						<tr>
							<td width="60%" valign="top">', $board['link'], '</td>
							<td width="20%" valign="top">', $board['posts'] > 0 ? '<img src="' . $settings['images_url'] . '/bar.gif" width="' . $board['posts_percent'] . '" height="15" alt="" />' : '&nbsp;', '</td>
							<td width="20%" align="', !$context['right_to_left'] ? 'right' : 'left', '" valign="top">', empty($context['hide_num_posts']) ? $board['posts'] : '', '</td>
						</tr>';
		}
	}
	echo '
					</table>
				</td>
				<td class="windowbg" width="20" valign="middle" align="center"><img src="', $settings['images_url'], '/stats_replies.gif" width="20" height="20" alt="" /></td>
				<td class="windowbg2" width="100%" valign="top">
					<table border="0" cellpadding="1" cellspacing="0" width="100%">';
	if (empty($context['board_activity']))
		echo '
						<tr>
							<td width="100%" valign="top" align="center">', $txt['statPanel_noPosts'], '</td>
						</tr>';
	else
	{
		// Draw a bar for every board.
		foreach ($context['board_activity'] as $activity)
		{
			echo '
						<tr>
							<td width="60%" valign="top">', $activity['link'], '</td>
							<td width="20%" valign="top">', $activity['percent'] > 0 ? '<img src="' . $settings['images_url'] . '/bar.gif" width="' . $activity['relative_percent'] . '" height="15" alt="" />' : '&nbsp;', '</td>
							<td width="20%" align="', !$context['right_to_left'] ? 'right' : 'left', '" valign="top">', $activity['percent'], '%</td>
						</tr>';
		}
	}
	echo '
					</table>
				</td>
			</tr>
		</table>';
}

// Template for editing profile options.
function template_edit_options()
{
	global $context, $settings, $options, $scripturl, $modSettings, $txt;

	// The main header!
	echo '
		<form action="', $scripturl, '?action=profile;save" method="post" accept-charset="', $context['character_set'], '" name="creator" id="creator" enctype="multipart/form-data" onsubmit="return checkProfileSubmit();">
			<table border="0" width="85%" cellspacing="1" cellpadding="4" align="center" class="bordercolor">
				<tr class="titlebg">
					<td height="26">
						&nbsp;<img src="', $settings['images_url'], '/icons/profile_sm.gif" alt="" align="top" />&nbsp;
						', $txt['profile'], '
					</td>
				</tr>';

	// Have we some description?
	if ($context['page_desc'])
		echo '
				<tr class="windowbg">
					<td class="smalltext" height="25" style="padding: 2ex;">
						', $context['page_desc'], '
					</td>
				</tr>';

	echo '
				<tr>
					<td class="windowbg2" style="padding-bottom: 2ex;">
						<table width="100%" cellpadding="3" cellspacing="0" border="0">';

	// Any bits at the start?
	if (!empty($context['profile_prehtml']))
		echo '
							<tr>
								<td colspan="2">', $context['profile_prehtml'], '</td>
							</tr>';

	// Start the big old loop 'of love.
	$lastItem = 'hr';
	foreach ($context['profile_fields'] as $key => $field)
	{
		// We add a little hack to be sure we never get two hr in a row!
		if ($lastItem == 'hr' && $field['type'] == 'hr')
			continue;

		$lastItem = $field['type'];
		if ($field['type'] == 'hr')
		{
			echo '
							<tr>
								<td colspan="2"><hr width="100%" size="1" class="hrcolor" /></td>
							</tr>';
		}
		elseif ($field['type'] == 'callback')
		{
			if (isset($field['callback_func']) && function_exists('template_profile_' . $field['callback_func']))
			{
				$callback_func = 'template_profile_' . $field['callback_func'];
				$callback_func();
			}
		}
		else
		{
			echo '
							<tr valign="top">
								<td width="40%">
									<b', !empty($field['is_error']) ? ' style="color: red;"' : '', '>', $field['label'], '</b>';

			// Does it have any subtext to show?
			if (!empty($field['subtext']))
				echo '
									<div class="smalltext">', $field['subtext'], '</div>';

			echo '
								</td>
								<td>';

			// Want to put something infront of the box?
			if (!empty($field['preinput']))
				echo '
									', $field['preinput'];

			// What type of data are we showing?
			if ($field['type'] == 'label')
				echo '
									', $field['value'];

			// Maybe it's a text box - very likely!
			elseif (in_array($field['type'], array('int', 'float', 'text', 'password')))
				echo '
									<input type="', $field['type'] == 'password' ? 'password' : 'text', '" name="', $key, '" id="', $key, '" size="', empty($field['size']) ? 30 : $field['size'], '" value="', $field['value'], '" ', $field['input_attr'], ' />';

			// You "checking" me out? ;)
			elseif ($field['type'] == 'check')
				echo '
									<input type="hidden" name="', $key, '" value="0" /><input type="checkbox" name="', $key, '" id="', $key, '" ', !empty($field['value']) ? ' checked="checked"' : '', ' value="1" class="check" ', $field['input_attr'], ' />';

			// Always fun - select boxes!
			elseif ($field['type'] == 'select')
			{
				echo '
									<select name="', $key, '" id="', $key, '">';

				if (isset($field['options']))
				{
					// Is this some code to generate the options?
					if (!is_array($field['options']))
						$field['options'] = eval($field['options']);
					// Assuming we now have some!
					if (is_array($field['options']))
						foreach ($field['options'] as $value => $name)
							echo '
										<option value="', $value, '" ', $value == $field['value'] ? 'selected="selected"' : '', '>', $name, '</option>';
				}

				echo '
									</select>';
			}

			// Something to end with?
			if (!empty($field['postinput']))
				echo '
									', $field['postinput'];

			echo '
								</td>
							</tr>';
		}
	}

	// Are there any custom profile fields - if so print them!
	if (!empty($context['custom_fields']))
	{
		if ($lastItem != 'hr')
			echo '
							<tr>
								<td colspan="2"><hr width="100%" size="1" class="hrcolor" /></td>
							</tr>';

		foreach ($context['custom_fields'] as $field)
		{
			echo '
							<tr valign="top">
								<td width="40%"><b>', $field['name'], ': </b><div class="smalltext">', $field['desc'], '</div></td>
								<td>', $field['input_html'], '</td>
							</tr>';
		}
	}

	// Any closing HTML?
	if (!empty($context['profile_posthtml']))
		echo '
							<tr>
								<td colspan="2">', $context['profile_posthtml'], '</td>
							</tr>';

	echo '
							<tr>
								<td colspan="2"><hr width="100%" size="1" class="hrcolor" /></td>
							</tr><tr>';

	// Only show the password box if it's actually needed.
	if ($context['user']['is_owner'] && $context['require_password'])
		echo '
								<td width="40%">
									<b', isset($context['modify_error']['bad_password']) || isset($context['modify_error']['no_password']) ? ' style="color: red;"' : '', '>', $txt['current_password'], ': </b>
									<div class="smalltext">', $txt['required_security_reasons'], '</div>
								</td>
								<td>
									<input type="password" name="oldpasswrd" size="20" style="margin-right: 4ex;" />';
	else
		echo '
								<td align="right" colspan="2">';

	echo '
									<input type="submit" value="', $txt['change_profile'], '" />
									<input type="hidden" name="sc" value="', $context['session_id'], '" />
									<input type="hidden" name="u" value="', $context['id_member'], '" />
									<input type="hidden" name="sa" value="', $context['menu_item_selected'], '" />
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
		</form>';

	// Some javascript!
	echo '
		<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
			function checkProfileSubmit()
			{';

	// If this part requires a password, make sure to give a warning.
	if ($context['user']['is_owner'] && $context['require_password'])
		echo '
				// Did you forget to type your password?
				if (document.forms.creator.oldpasswrd.value == "")
				{
					alert("', $txt['required_security_reasons'], '");
					return false;
				}';

	// Any onsubmit javascript?
	if (!empty($context['profile_onsubmit_javascript']))
		echo '
				', $context['profile_javascript'];

	echo '
			}';

	// Any totally custom stuff?
	if (!empty($context['profile_javascript']))
		echo '
			', $context['profile_javascript'];

	echo '
		// ]]></script>';

	// Any final spellchecking stuff?
	if (!empty($context['show_spellchecking']))
		echo '
		<form name="spell_form" id="spell_form" method="post" accept-charset="', $context['character_set'], '" target="spellWindow" action="', $scripturl, '?action=spellcheck"><input type="hidden" name="spellstring" value="" /></form>';
}

// Template for showing theme settings. Note: template_options() actually adds the theme specific options.
function template_profile_theme_settings()
{
	global $context, $settings, $options, $scripturl, $modSettings, $txt;

	echo '
							<tr>
								<td colspan="2">
									<table width="100%" cellspacing="0" cellpadding="3">
										<tr>
											<td colspan="2">
												<input type="hidden" name="default_options[show_board_desc]" value="0" />
												<label for="show_board_desc"><input type="checkbox" name="default_options[show_board_desc]" id="show_board_desc" value="1"', !empty($context['member']['options']['show_board_desc']) ? ' checked="checked"' : '', ' class="check" /> ', $txt['board_desc_inside'], '</label>
											</td>
										</tr><tr>
											<td colspan="2">
												<input type="hidden" name="default_options[show_children]" value="0" />
												<label for="show_children"><input type="checkbox" name="default_options[show_children]" id="show_children" value="1"', !empty($context['member']['options']['show_children']) ? ' checked="checked"' : '', ' class="check" /> ', $txt['show_children'], '</label>
											</td>
										</tr><tr>
											<td colspan="2">
												<input type="hidden" name="default_options[show_no_avatars]" value="0" />
												<label for="show_no_avatars"><input type="checkbox" name="default_options[show_no_avatars]" id="show_no_avatars" value="1"', !empty($context['member']['options']['show_no_avatars']) ? ' checked="checked"' : '', ' class="check" /> ', $txt['show_no_avatars'], '</label>
											</td>
										</tr><tr>
											<td colspan="2">
												<input type="hidden" name="default_options[show_no_signatures]" value="0" />
												<label for="show_no_signatures"><input type="checkbox" name="default_options[show_no_signatures]" id="show_no_signatures" value="1"', !empty($context['member']['options']['show_no_signatures']) ? ' checked="checked"' : '', ' class="check" /> ', $txt['show_no_signatures'], '</label>
											</td>
										</tr>';

	if ($settings['allow_no_censored'])
		echo '
										<tr>
											<td colspan="2">
												<input type="hidden" name="default_options[show_no_censored]" value="0" />
												<label for="show_no_censored"><input type="checkbox" name="default_options[show_no_censored]" id="show_no_censored" value="1"' . (!empty($context['member']['options']['show_no_censored']) ? ' checked="checked"' : '') . ' class="check" /> ' . $txt['show_no_censored'] . '</label>
											</td>
										</tr>';

	echo '
										<tr>
											<td colspan="2">
												<input type="hidden" name="default_options[return_to_post]" value="0" />
												<label for="return_to_post"><input type="checkbox" name="default_options[return_to_post]" id="return_to_post" value="1"', !empty($context['member']['options']['return_to_post']) ? ' checked="checked"' : '', ' class="check" /> ', $txt['return_to_post'], '</label>
											</td>
										</tr>
										<tr>
											<td colspan="2">
												<input type="hidden" name="default_options[no_new_reply_warning]" value="0" />
												<label for="no_new_reply_warning"><input type="checkbox" name="default_options[no_new_reply_warning]" id="no_new_reply_warning" value="1"', !empty($context['member']['options']['no_new_reply_warning']) ? ' checked="checked"' : '', ' class="check" /> ', $txt['no_new_reply_warning'], '</label>
											</td>
										</tr>
										<tr>
											<td colspan="2">
												<input type="hidden" name="default_options[view_newest_first]" value="0" />
												<label for="view_newest_first"><input type="checkbox" name="default_options[view_newest_first]" id="view_newest_first" value="1"', !empty($context['member']['options']['view_newest_first']) ? ' checked="checked"' : '', ' class="check" /> ', $txt['recent_posts_at_top'], '</label>
											</td>
										</tr><tr>
											<td colspan="2">
												<input type="hidden" name="default_options[view_newest_pm_first]" value="0" />
												<label for="view_newest_pm_first"><input type="checkbox" name="default_options[view_newest_pm_first]" id="view_newest_pm_first" value="1"', !empty($context['member']['options']['view_newest_pm_first']) ? ' checked="checked"' : '', ' class="check" /> ', $txt['recent_pms_at_top'], '</label>
											</td>
										</tr>';

	// Choose WYSIWYG settings?
	if (empty($modSettings['disable_wysiwyg']))
		echo '
										<tr>
											<td colspan="2">
												<input type="hidden" name="default_options[wysiwyg_default]" value="0" />
												<label for="wysiwyg_default"><input type="checkbox" name="default_options[wysiwyg_default]" id="wysiwyg_default" value="1"', !empty($context['member']['options']['wysiwyg_default']) ? ' checked="checked"' : '', ' class="check" /> ', $txt['wysiwyg_default'], '</label>
											</td>
										</tr>';

	if (empty($modSettings['disableCustomPerPage']))
	{
		echo '
										<tr>
											<td colspan="2"><label for="topics_per_page">', $txt['topics_per_page'], '</label>
												<select name="default_options[topics_per_page]" id="topics_per_page">
													<option value="0"', empty($context['member']['options']['topics_per_page']) ? ' selected="selected"' : '', '>', $txt['per_page_default'], ' (', $modSettings['defaultMaxTopics'], ')</option>
													<option value="5"', !empty($context['member']['options']['topics_per_page']) && $context['member']['options']['topics_per_page'] == 5 ? ' selected="selected"' : '', '>5</option>
													<option value="10"', !empty($context['member']['options']['topics_per_page']) && $context['member']['options']['topics_per_page'] == 10 ? ' selected="selected"' : '', '>10</option>
													<option value="25"', !empty($context['member']['options']['topics_per_page']) && $context['member']['options']['topics_per_page'] == 25 ? ' selected="selected"' : '', '>25</option>
													<option value="50"', !empty($context['member']['options']['topics_per_page']) && $context['member']['options']['topics_per_page'] == 50 ? ' selected="selected"' : '', '>50</option>
												</select>
											</td>
										</tr>
										<tr>
											<td colspan="2"><label for="messages_per_page">', $txt['messages_per_page'], '</label>
												<select name="default_options[messages_per_page]" id="messages_per_page">
													<option value="0"', empty($context['member']['options']['messages_per_page']) ? ' selected="selected"' : '', '>', $txt['per_page_default'], ' (', $modSettings['defaultMaxMessages'], ')</option>
													<option value="5"', !empty($context['member']['options']['messages_per_page']) && $context['member']['options']['messages_per_page'] == 5 ? ' selected="selected"' : '', '>5</option>
													<option value="10"', !empty($context['member']['options']['messages_per_page']) && $context['member']['options']['messages_per_page'] == 10 ? ' selected="selected"' : '', '>10</option>
													<option value="25"', !empty($context['member']['options']['messages_per_page']) && $context['member']['options']['messages_per_page'] == 25 ? ' selected="selected"' : '', '>25</option>
													<option value="50"', !empty($context['member']['options']['messages_per_page']) && $context['member']['options']['messages_per_page'] == 50 ? ' selected="selected"' : '', '>50</option>
												</select>
											</td>
										</tr>';
	}

	if (!empty($modSettings['cal_enabled']))
		echo '
										<tr>
											<td colspan="2"><label for="calendar_start_day">', $txt['calendar_start_day'], ':</label>
												<select name="default_options[calendar_start_day]" id="calendar_start_day">
													<option value="0"', empty($context['member']['options']['calendar_start_day']) ? ' selected="selected"' : '', '>', $txt['days'][0], '</option>
													<option value="1"', !empty($context['member']['options']['calendar_start_day']) && $context['member']['options']['calendar_start_day'] == 1 ? ' selected="selected"' : '', '>', $txt['days'][1], '</option>
													<option value="6"', !empty($context['member']['options']['calendar_start_day']) && $context['member']['options']['calendar_start_day'] == 6 ? ' selected="selected"' : '', '>', $txt['days'][6], '</option>
												</select>
											</td>
										</tr>';

	echo '
										<tr>
											<td colspan="2"><label for="display_quick_reply">', $txt['display_quick_reply'], '</label>
												<select name="default_options[display_quick_reply]" id="display_quick_reply">
													<option value="0"', empty($context['member']['options']['display_quick_reply']) ? ' selected="selected"' : '', '>', $txt['display_quick_reply1'], '</option>
													<option value="1"', !empty($context['member']['options']['display_quick_reply']) && $context['member']['options']['display_quick_reply'] == 1 ? ' selected="selected"' : '', '>', $txt['display_quick_reply2'], '</option>
													<option value="2"', !empty($context['member']['options']['display_quick_reply']) && $context['member']['options']['display_quick_reply'] == 2 ? ' selected="selected"' : '', '>', $txt['display_quick_reply3'], '</option>
												</select>
											</td>
										</tr><tr>
											<td colspan="2"><label for="display_quick_mod">', $txt['display_quick_mod'], '</label>
												<select name="default_options[display_quick_mod]" id="display_quick_mod">
													<option value="0"', empty($context['member']['options']['display_quick_mod']) ? ' selected="selected"' : '', '>', $txt['display_quick_mod_none'], '</option>
													<option value="1"', !empty($context['member']['options']['display_quick_mod']) && $context['member']['options']['display_quick_mod'] == 1 ? ' selected="selected"' : '', '>', $txt['display_quick_mod_check'], '</option>
													<option value="2"', !empty($context['member']['options']['display_quick_mod']) && $context['member']['options']['display_quick_mod'] != 1 ? ' selected="selected"' : '', '>', $txt['display_quick_mod_image'], '</option>
												</select>
											</td>
										</tr>';
}

function template_notification()
{
	global $context, $settings, $options, $txt, $scripturl, $modSettings;

	// The main containing header.
	echo '
			<table border="0" width="85%" cellspacing="1" cellpadding="4" align="center" class="bordercolor">
				<tr class="titlebg">
					<td height="26">
						&nbsp;<img src="', $settings['images_url'], '/icons/profile_sm.gif" alt="" align="top" />&nbsp;
						', $txt['profile'], '
					</td>
				</tr><tr class="windowbg">
					<td class="smalltext" height="25" style="padding: 2ex;">
						', $txt['notification_info'], '
					</td>
				</tr><tr>
					<td class="windowbg2" width="100%">
						<form action="', $scripturl, '?action=profile;save" method="post" accept-charset="', $context['character_set'], '" style="margin: 0;">';

	// Allow notification on announcements to be disabled?
	if (!empty($modSettings['allow_disableAnnounce']))
		echo '
							<input type="hidden" name="notify_announcements" value="0" />
							<label for="notify_announcements"><input type="checkbox" id="notify_announcements" name="notify_announcements"', !empty($context['member']['notify_announcements']) ? ' checked="checked"' : '', ' class="check" /> ', $txt['notify_important_email'], '</label><br />';

	// More notification options.
	echo '
							<input type="hidden" name="default_options[auto_notify]" value="0" />
							<label for="auto_notify"><input type="checkbox" id="auto_notify" name="default_options[auto_notify]" value="1"', !empty($context['member']['options']['auto_notify']) ? ' checked="checked"' : '', ' class="check" /> ', $txt['auto_notify'], '</label><br />';

	if (empty($modSettings['disallow_sendBody']))
		echo '
							<input type="hidden" name="notify_send_body" value="0" />
							<label for="notify_send_body"><input type="checkbox" id="notify_send_body" name="notify_send_body"', !empty($context['member']['notify_send_body']) ? ' checked="checked"' : '', ' class="check" /> ', $txt['notify_send_body'], '</label><br />';

	echo '
							<br />
							<label for="notify_regularity">', $txt['notify_regularity'], ':</label>
							<select name="notify_regularity" id="notify_regularity">
								<option value="0"', $context['member']['notify_regularity'] == 0 ? ' selected="selected"' : '', '>', $txt['notify_regularity_instant'], '</option>
								<option value="1"', $context['member']['notify_regularity'] == 1 ? ' selected="selected"' : '', '>', $txt['notify_regularity_first_only'], '</option>
								<option value="2"', $context['member']['notify_regularity'] == 2 ? ' selected="selected"' : '', '>', $txt['notify_regularity_daily'], '</option>
								<option value="3"', $context['member']['notify_regularity'] == 3 ? ' selected="selected"' : '', '>', $txt['notify_regularity_weekly'], '</option>
							</select>
							<br /><br />
							<label for="notify_types">', $txt['notify_send_types'], ':</label>
							<select name="notify_types" id="notify_types">
								<option value="1"', $context['member']['notify_types'] == 1 ? ' selected="selected"' : '', '>', $txt['notify_send_type_everything'], '</option>
								<option value="2"', $context['member']['notify_types'] == 2 ? ' selected="selected"' : '', '>', $txt['notify_send_type_everything_own'], '</option>
								<option value="3"', $context['member']['notify_types'] == 3 ? ' selected="selected"' : '', '>', $txt['notify_send_type_only_replies'], '</option>
								<option value="4"', $context['member']['notify_types'] == 4 ? ' selected="selected"' : '', '>', $txt['notify_send_type_nothing'], '</option>
							</select><br />

							<div align="', !$context['right_to_left'] ? 'right' : 'left', '">
								<input type="submit" style="margin: 0 1ex 1ex 1ex;" value="', $txt['notify_save'], '" />
								<input type="hidden" name="sc" value="', $context['session_id'], '" />
								<input type="hidden" name="u" value="', $context['id_member'], '" />
								<input type="hidden" name="sa" value="', $context['menu_item_selected'], '" />
							</div>
						</form>
					</td>
				</tr>
			</table>
			<br />
			<form action="', $scripturl, '?action=profile;save" method="post" accept-charset="', $context['character_set'], '" style="margin: 0;">
			<table border="0" width="85%" cellspacing="1" cellpadding="4" align="center" class="bordercolor">
				<tr class="titlebg">
					<td height="26" colspan="4">
						&nbsp;<img src="', $settings['images_url'], '/icons/notify_sm.gif" alt="" align="top" />&nbsp;
						', $txt['notifications_topics'], '
					</td>
				</tr>';
	if (!empty($context['topic_notifications']))
	{
		echo '

				<tr class="catbg">
					<td>' . $txt['subject'] . '</td>
					<td width="18%">' . $txt['started_by'] . '</td>
					<td width="32%">' . $txt['last_post'] . '</td>
					<td width="3%" align="center"><input type="checkbox" class="check" onclick="invertAll(this, this.form);" /></td>
				</tr>';
		foreach ($context['topic_notifications'] as $topic)
		{
			echo '
				<tr>
					<td class="windowbg" valign="middle">
						', $topic['link'];

			if ($topic['new'])
				echo ' <a href="', $topic['new_href'], '"><img src="' . $settings['lang_images_url'] . '/new.gif" alt="', $txt['new'], '" /></a>';

			echo '<br />
						<span class="smalltext"><i>' . $txt['in'] . ' ' . $topic['board']['link'] . '</i></span>
					</td>
					<td class="windowbg2" valign="middle">' . $topic['poster']['link'] . '</td>
					<td class="windowbg2" valign="middle">
						<span class="smalltext">
							' . $topic['updated'] . '<br />' . $txt['by'] . ' ' . $topic['poster_updated']['link'] . '</span>
					</td>
					<td class="windowbg2" valign="middle" align="center">
						<input type="checkbox" name="notify_topics[]" value="', $topic['id'], '" class="check" />
					</td>
				</tr>';
		}

		echo '
				<tr class="titlebg">
					<td colspan="4">
						<b>', $txt['pages'], ':</b> ', $context['page_index'], '
					</td>
				</tr>
				<tr>
					<td colspan="4" class="windowbg2" align="right">
						<input type="submit" name="edit_notify_topics" value="', $txt['notifications_update'], '" />
					</td>
				</tr>';
	}
	else
		echo '
				<tr class="windowbg2">
					<td width="100%" colspan="4" class="windowbg2">
						', $txt['notifications_topics_none'], '<br />
						<br />', $txt['notifications_topics_howto'], '<br />
						<br />
					</td>
				</tr>';
	echo '
			</table>
			<input type="hidden" name="sc" value="', $context['session_id'], '" />
			<input type="hidden" name="u" value="', $context['id_member'], '" />
			<input type="hidden" name="sa" value="', $context['menu_item_selected'], '" />
		</form>
		<br />
		<form action="', $scripturl, '?action=profile;save" method="post" accept-charset="', $context['character_set'], '" style="margin: 0;">
			<table border="0" width="85%" cellspacing="1" cellpadding="4" align="center" class="bordercolor">
				<tr class="titlebg">
					<td height="26" colspan="2">
						&nbsp;<img src="', $settings['images_url'], '/icons/notify_sm.gif" alt="" align="top" />&nbsp;
						', $txt['notifications_boards'], '
					</td>
				</tr>';

	if (!empty($context['board_notifications']))
	{
		echo '
				<tr class="catbg">
					<td width="95%">' . $txt['board'] . '</td>
					<td width="3%" align="center"><input type="checkbox" class="check" onclick="invertAll(this, this.form);" /></td>
				</tr>';
		foreach ($context['board_notifications'] as $board)
		{
			echo '
				<tr>
					<td class="windowbg" valign="middle" width="48%">', $board['link'];

		if ($board['new'])
			echo ' <a href="', $board['href'], '"><img src="' . $settings['lang_images_url'] . '/new.gif" alt="', $txt['new'], '" /></a>';

		echo '</td>
					<td class="windowbg2" valign="middle" width="3%" align="center">
						<input type="checkbox" name="notify_boards[]" value="', $board['id'], '" />
					</td>
				</tr>';
		}

		echo '
				<tr>
					<td colspan="2" class="windowbg2" align="right">
						<input type="submit" name="edit_notify_boards" value="', $txt['notifications_update'], '" />
					</td>
				</tr>';
	}
	else
		echo '
				<tr class="windowbg2">
					<td width="100%" colspan="2" class="windowbg2">
						', $txt['notifications_boards_none'], '<br />
						<br />', $txt['notifications_boards_howto'], '<br />
						<br />
					</td>
				</tr>';
	echo '
			</table>
			<input type="hidden" name="sc" value="', $context['session_id'], '" />
			<input type="hidden" name="u" value="', $context['id_member'], '" />
			<input type="hidden" name="sa" value="', $context['menu_item_selected'], '" />
		</form>';
}

// Template for choosing group membership.
function template_groupMembership()
{
	global $context, $settings, $options, $scripturl, $modSettings, $txt;

	// The main containing header.
	echo '
		<form action="', $scripturl, '?action=profile;save;sa=groupMembership" method="post" accept-charset="', $context['character_set'], '" name="creator" id="creator">
			<table border="0" width="85%" cellspacing="0" cellpadding="4" align="center" class="tborder">
				<tr class="titlebg">
					<td height="26">
						&nbsp;<img src="', $settings['images_url'], '/icons/profile_sm.gif" alt="" align="top" />&nbsp;
						', $txt['profile'], '
					</td>
				</tr><tr class="windowbg">
					<td class="smalltext" style="padding: 2ex;">
						', $txt['groupMembership_info'], '
					</td>
				</tr>';

	// Do we have an update message?
	if (!empty($context['update_message']))
		echo '
				<tr class="windowbg">
					<td align="center" class="error">
						<b>', $context['update_message'], '</b>
					</td>
				</tr>';

	echo '
			</table><br />';

	// Requesting membership to a group?
	if (!empty($context['group_request']))
	{
		echo '
			<table border="0" width="60%" cellspacing="0" cellpadding="4" align="center" class="tborder">
				<tr class="catbg">
					<td>
						', $txt['request_group_membership'], '
					</td>
				</tr><tr class="windowbg">
					<td>
						', $txt['request_group_membership_desc'], ':
					</td>
				</tr><tr class="windowbg">
					<td align="center">
						<textarea name="reason" rows="4" style="width: 95%"></textarea>
					</td>
				</tr><tr class="windowbg">
					<td align="center">
						<input type="hidden" name="gid" value="', $context['group_request']['id'], '" />
						<input type="submit" name="req" value="', $txt['submit_request'], '" />
					</td>
				</tr>
			</table>';
	}
	else
	{
		echo '
			<table border="0" width="85%" cellspacing="0" cellpadding="4" align="center" class="tborder">
				<tr class="catbg">
					<td colspan="2">
						', $txt['current_membergroups'], '
					</td>
				</tr>';

		$alternate = 0;
		foreach ($context['groups']['member'] as $group)
		{
			echo '
				<tr class="', $alternate ? 'windowbg' : 'windowbg2', '" id="primdiv_', $group['id'], '">';

			if ($context['can_edit_primary'])
				echo '
					<td width="4%">
						<input type="radio" name="primary" id="primary_', $group['id'], '" value="', $group['id'], '" ', $group['is_primary'] ? 'checked="checked"' : '', ' onclick="highlightSelected(\'primdiv_' . $group['id'] . '\');" ', $group['can_be_primary'] ? '' : 'disabled="disabled"', '/>
					</td>';

			echo '
					<td>
						<div style="float: left;">
							<label for="primary_', $group['id'], '"><b>', (empty($group['color']) ? $group['name'] : '<span style="color: ' . $group['color'] . '">' . $group['name'] . '</span>'), '</b>', (!empty($group['desc']) ? '<br /><span class="smalltext">' . $group['desc'] . '</span>' : ''), '</label>
						</div>
						<div style="float: right">';

			// Can they leave their group?
			if ($group['can_leave'])
				echo '
							<a href="' . $scripturl . '?action=profile;save;u=' . $context['id_member'] . ';sa=groupMembership;sesc=' . $context['session_id'] . ';gid=' . $group['id'] . '">' . $txt['leave_group'] . '</a>';
			echo '
						</div>
					</td>
				</tr>';
			$alternate = !$alternate;
		}

		if ($context['can_edit_primary'])
			echo '
				<tr class="catbg">
					<td colspan="2" align="right">
						<input type="submit" value="', $txt['make_primary'], '" style="font-weight: normal;" />
					</td>
				</tr>';

		echo '
			</table>';

		// Any groups they can join?
		if (!empty($context['groups']['available']))
		{
			echo '
			<br />
			<table border="0" width="85%" cellspacing="0" cellpadding="4" align="center" class="tborder">
				<tr class="catbg">
					<td>
						', $txt['available_groups'], '
					</td>
				</tr>';

			$alternate = 0;
			foreach ($context['groups']['available'] as $group)
			{
				echo '
				<tr class="', $alternate ? 'windowbg' : 'windowbg2', '">
					<td>
						<div style="float: left;">
							<b>', (empty($group['color']) ? $group['name'] : '<span style="color: ' . $group['color'] . '">' . $group['name'] . '</span>'), '</b>', (!empty($group['desc']) ? '<br /><span class="smalltext">' . $group['desc'] . '</span>' : ''), '
						</div>
						<div style="float: right">
							', $group['type'] == 2 ? '<a href="' . $scripturl . '?action=profile;save;u=' . $context['id_member'] . ';sa=groupMembership;sesc=' . $context['session_id'] . ';gid=' . $group['id'] . '">' . $txt['join_group'] . '</a>' : ($group['pending'] ? $txt['approval_pending'] : '<a href="' . $scripturl . '?action=profile;u=' . $context['id_member'] . ';sa=groupMembership;request=' . $group['id'] . '">' . $txt['request_group'] . '</a>'), '
						</div>
					</td>
				</tr>';
				$alternate = !$alternate;
			}
			echo '
			</table>';
		}

		// Javascript for the selector stuff.
		echo '
		<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
		var prevClass = "";
		var prevDiv = "";
		function highlightSelected(box)
		{
			if (prevClass != "")
			{
				prevDiv.className = prevClass;
			}
			prevDiv = document.getElementById(box);
			prevClass = prevDiv.className;

			prevDiv.className = "highlight2";
		}';
		if (isset($context['groups']['member'][$context['primary_group']]))
			echo '
		highlightSelected("primdiv_' . $context['primary_group'] . '");';
		echo '
	// ]]></script>';
	}

	echo '
				<input type="hidden" name="sc" value="', $context['session_id'], '" />
				<input type="hidden" name="u" value="', $context['id_member'], '" />
			</form>';
}

function template_ignoreboards()
{
	global $context, $txt, $settings, $scripturl;
	// The main containing header.
	echo '
	<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
		function selectBoards(ids)
		{
			var toggle = true;

			for (i = 0; i < ids.length; i++)
				toggle = toggle & document.forms.creator["ignore_brd" + ids[i]].checked;

			for (i = 0; i < ids.length; i++)
				document.forms.creator["ignore_brd" + ids[i]].checked = !toggle;
		}
	// ]]></script>

	<form action="', $scripturl, '?action=profile;save" method="post" accept-charset="', $context['character_set'], '" name="creator" id="creator">
			<table border="0" width="85%" cellspacing="0" cellpadding="4" align="center" class="tborder">
				<tr class="titlebg">
					<td height="26">
						&nbsp;<img src="', $settings['images_url'], '/icons/profile_sm.gif" alt="" align="top" />&nbsp;
						', $txt['profile'], '
					</td>
				</tr><tr class="windowbg">
					<td class="smalltext" style="padding: 2ex;">
						', $txt['ignoreboards_info'], '
					</td>
				</tr><tr>
					<td class="windowbg2" style="padding-bottom: 2ex;">
						<table border="0" width="100%" cellpadding="3">';

	$alternate = true;
	foreach ($context['board_columns'] as $board)
	{
		if ($alternate)
			echo '
							<tr>';
		echo '
								<td width="50%">';

		if (!empty($board) && empty($board['child_ids']))
			echo '
									<label for="ignore_brd', $board['id'], '" style="margin-left: ', $board['child_level'], 'ex;"><input type="checkbox" id="ignore_brd', $board['id'], '" name="ignore_brd[', $board['id'], ']" value="', $board['id'], '"', $board['selected'] ? ' checked="checked"' : '', ' class="check" />', $board['name'], '</label>';
		elseif (!empty($board))
			echo '
									<a href="javascript:void(0);" onclick="selectBoards([', implode(', ', $board['child_ids']), ']); return false;" style="text-decoration: underline;">', $board['name'], '</a>';

		echo '
								</td>';
		if (!$alternate)
			echo '
							</tr>';

		$alternate = !$alternate;
	}


	// Show the standard "Save Settings" profile button.
	template_profile_save();

	echo '
						</table>
					</td>
				</tr>
			</table>
		</form>';
}

// Show a lovely interface for issuing warnings.
function template_issueWarning()
{
	global $context, $settings, $options, $scripturl, $modSettings, $txt;

	$warningBarWidth = 200;
	// Setup the colors - this is a little messy for theming.
	$context['colors'] = array(
		0 => 'green',
		$modSettings['warning_watch'] => 'darkgreen',
		$modSettings['warning_moderate'] => 'orange',
		$modSettings['warning_mute'] => 'red',
	);

	// Setup known notification types.
	$context['notify_types'] = array('spamming', 'offence', 'insulting');

	// Work out the starting color.
	$context['current_color'] = $context['colors'][0];
	foreach ($context['colors'] as $limit => $color)
		if ($context['member']['warning'] >= $limit)
			$context['current_color'] = $color;

	// Format notification types.
	foreach ($context['notify_types'] as $k => $type)
		$context['notify_types'][$k] = array(
			'title' => $txt['profile_warning_notify_title_' . $type],
			'body' => sprintf($txt['profile_warning_notify_template_outline'], $context['member']['name'], $txt['profile_warning_notify_for_' . $type]),
		);

	echo '
	<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
		function setWarningBarPos(curEvent, isMove, changeAmount)
		{
			barWidth = ', $warningBarWidth, ';

			// Are we passing the amount to change it by?
			if (changeAmount)
			{
				if (document.getElementById(\'warning_level\').value == \'SAME\')
					percent = ', $context['member']['warning'], ' + changeAmount;
				else
					percent = parseInt(document.getElementById(\'warning_level\').value) + changeAmount;
			}
			// If not then it\'s a mouse thing.
			else
			{
				if (!curEvent)
					var curEvent = window.event;

				// If it\'s a movement check the button state first!
				if (isMove)
				{
					if (!curEvent.button || curEvent.button != 1)
						return false
				}


				// Get the position of the container.
				contain = document.getElementById(\'warning_contain\');
				position = 0;
				while (contain != null)
				{
					position += contain.offsetLeft;
					contain = contain.offsetParent;
				}

				// Where is the mouse?
				if (curEvent.pageX)
				{
					mouse = curEvent.pageX;
				}
				else
				{
					mouse = curEvent.clientX;
					mouse += document.documentElement.scrollLeft != "undefined" ? document.documentElement.scrollLeft : document.body.scrollLeft;
				}

				// Is this within bounds?
				if (mouse < position || mouse > position + barWidth)
					return;

				percent = Math.round(((mouse - position) / barWidth) * 100);

				// Round percent to the nearest 5 - by kinda cheating!
				percent = Math.round(percent / 5) * 5;
			}

			// What are the limits?
			minLimit = ', $context['min_allowed'], ';
			maxLimit = ', $context['max_allowed'], ';

			percent = Math.max(percent, minLimit);
			percent = Math.min(percent, maxLimit);

			size = barWidth * (percent/100);

			setInnerHTML(document.getElementById(\'warning_text\'), percent + "%");
			document.getElementById(\'warning_level\').value = percent;
			document.getElementById(\'warning_progress\').style.width = size + "px";

			// Get the right color.
			color = "black"';

	foreach ($context['colors'] as $limit => $color)
		echo '
			if (percent >= ', $limit, ')
				color = "', $color, '";';

	echo '
			document.getElementById(\'warning_progress\').style.backgroundColor = color;

			// Also set the right effect.
			effectText = "";';

	foreach ($context['level_effects'] as $limit => $text)
		echo '
			if (percent >= ', $limit, ')
				effectText = "', $text, '";';

	echo '
			setInnerHTML(document.getElementById(\'cur_level_div\'), effectText);
		}

		// Disable notification boxes as required.
		function modifyWarnNotify()
		{
			disable = !document.getElementById(\'warn_notify\').checked;
			document.getElementById(\'warn_sub\').disabled = disable;
			document.getElementById(\'warn_body\').disabled = disable;
			document.getElementById(\'warn_temp\').disabled = disable;
		}

		function changeWarnLevel(amount)
		{
			setWarningBarPos(false, false, amount);
		}

		// Warn template.
		function populateNotifyTemplate()
		{
			index = document.getElementById(\'warn_temp\').value;
			if (index == -1)
				return false;

			// Otherwise see what we can do...';

	foreach ($context['notify_types'] as $k => $type)
		echo '
			if (index == ', $k, ')
				document.getElementById(\'warn_body\').value = "', strtr($type['body'], array('"' => "'", "\n" => '\\n')), '";';

	echo '
		}

	// ]]></script>';

	echo '
	<form action="', $scripturl, '?action=profile;u=', $context['id_member'], ';sa=issueWarning" method="post" accept-charset="', $context['character_set'], '">
		<table border="0" width="85%" cellspacing="1" cellpadding="5" class="bordercolor" align="center">
			<tr class="titlebg">
				<td colspan="2" height="26">
					&nbsp;<img src="', $settings['images_url'], '/icons/profile_sm.gif" alt="" align="top" />&nbsp;', $txt['profile_issue_warning'], '
				</td>
			</tr>
			<tr class="windowbg2">
				<td colspan="2">
					<span class="smalltext">', $txt['profile_warning_desc'], '</span>
				</td>
			</tr>
			<tr class="windowbg">
				<td width="100%">
					<table border="0" width="100%" cellspacing="0" cellpadding="2" align="center">
						<tr class="windowbg">
				<td width="40%">
					<b>', $txt['profile_warning_name'], ':</b>
				</td>
				<td>
					<b>', $context['member']['name'], '</b>
				</td>
			</tr>
			<tr class="windowbg" valign="top">
				<td width="30%">
					<b>', $txt['profile_warning_level'], ':</b>';

	// Is there only so much they can apply?
	if ($context['warning_limit'])
		echo '
					<div class="smalltext">', sprintf($txt['profile_warning_limit_attribute'], $context['warning_limit']), '</div>';

	echo '
				</td>
				<td>
					<div id="warndiv1" style="display: none;">
						<div>
							<span style="float: left">
								<a href="#" onclick="changeWarnLevel(-5); return false;">&#171;</a>
								<a href="#" onclick="changeWarnLevel(5); return false;">&#187;</a>
							</span>
							<div id="warning_contain" style="font-size: 8pt; height: 12pt; width: ', $warningBarWidth, 'px; border: 1px solid black; background-color: white; padding: 1px; position: relative;" onmousedown="setWarningBarPos(event, true);" onmousemove="setWarningBarPos(event, true);" onclick="setWarningBarPos(event);">
								<div id="warning_text" style="padding-top: 1pt; width: 100%; z-index: 2; color: black; position: absolute; text-align: center; font-weight: bold;">', $context['member']['warning'], '%</div>
								<div id="warning_progress" style="width: ', $context['member']['warning'], '%; height: 12pt; z-index: 1; background-color: ', $context['current_color'], ';">&nbsp;</div>
							</div>
						</div>
						<div class="smalltext">', $txt['profile_warning_impact'], ': <span id="cur_level_div" class="smalltext">', $context['level_effects'][$context['current_level']], '</span></div>
						<input type="hidden" name="warning_level" id="warning_level" value="SAME" />
					</div>
					<div id="warndiv2">
						<input type="text" name="warning_level_nojs" size="6" maxlength="4" value="', $context['member']['warning'], '" />&nbsp;', $txt['profile_warning_max'], '
						<div class="smalltext">', $txt['profile_warning_impact'], ':<br />';
	// For non-javascript give a better list.
	foreach ($context['level_effects'] as $limit => $effect)
		echo '
						', sprintf($txt['profile_warning_effect_text'], $limit, $effect), '<br />';

	echo '
						</div>
					</div>
				</td>
			</tr>
			<tr class="windowbg" valign="top">
				<td width="30%">
					<b>', $txt['profile_warning_reason'], ':</b>
					<div class="smalltext">', $txt['profile_warning_reason_desc'], '</div>
				</td>
				<td>
					<input type="text" name="warn_reason" id="warn_reason" value="" size="50" style="width: 80%;" />
				</td>
			</tr>
			<tr class="windowbg">
				<td colspan="2">
					<hr />
				</td>
			</tr>
			<tr class="windowbg">
				<td width="30%">
					<b>', $txt['profile_warning_notify'], ':</b>
				</td>
				<td>
					<input type="checkbox" name="warn_notify" id="warn_notify" onclick="modifyWarnNotify();" />
				</td>
			</tr>
			<tr class="windowbg">
				<td width="30%">
					<b>', $txt['profile_warning_notify_subject'], ':</b>
				</td>
				<td>
					<input type="text" name="warn_sub" id="warn_sub" value="', $txt['profile_warning_notify_template_subject'], '" size="50" style="width: 80%;" />
				</td>
			</tr>
			<tr class="windowbg" valign="top">
				<td width="30%">
					<b>', $txt['profile_warning_notify_body'], ':</b>
				</td>
				<td>
					<select name="warn_temp" id="warn_temp" disabled="disabled" onchange="populateNotifyTemplate();" style="font-size: x-small;">
						<option value="-1">', $txt['profile_warning_notify_template'], '</option>
						<option value="-1">------------------------------</option>';

	foreach ($context['notify_types'] as $k => $v)
		echo '
						<option value="', $k, '">', $v['title'], '</option>';

	echo '
					</select><br />
					<textarea name="warn_body" id="warn_body" cols="40" rows="8" style="width: 80%; font-size: x-small;" ></textarea>
				</td>
			</tr>
			</table>
			</td>
			</tr>
			<tr class="catbg">
				<td colspan="2" align="right">
					<input type="hidden" name="sc" value="', $context['session_id'], '" />
					<input type="submit" name="save" value="', $txt['profile_warning_issue'], '" />
				</td>
			</tr>
		</table>
	</form>';

	// Previous warnings?
	echo '
		<table border="0" width="85%" cellspacing="1" cellpadding="5" class="bordercolor" align="center">
			<tr class="titlebg">
				<td colspan="4">
					', $txt['profile_warning_previous'], '
				</td>
			</tr>
			<tr class="catbg">
				<td width="20%">', $txt['profile_warning_previous_issued'], '</td>
				<td width="30%">', $txt['profile_warning_previous_time'], '</td>
				<td>', $txt['profile_warning_previous_reason'], '</td>
				<td width="6%">', $txt['profile_warning_previous_level'], '</td>
			</tr>';

	// Print the warnings.
	$alternate = 0;
	foreach ($context['previous_warnings'] as $warning)
	{
		$alternate = !$alternate;
		echo '
			<tr class="', $alternate ? 'windowbg' : 'windowbg2', '">
				<td class="smalltext">', $warning['issuer']['link'], '</td>
				<td class="smalltext">', $warning['time'], '</td>
				<td class="smalltext">
					<div style="float: left;">
						', $warning['reason'], '
					</div>';

		if (!empty($warning['id_notice']))
			echo '
					<div style="float: right;">
						<a href="', $scripturl, '?action=moderate;area=notice;nid=', $warning['id_notice'], '" onclick="window.open(this.href, \'\', \'scrollbars=yes,resizable=yes,width=400,height=250\');return false;" target="_blank" title="', $txt['profile_warning_previous_notice'], '">!</a>
					</div>';
		echo '
				</td>
				<td class="smalltext">', $warning['counter'], '</td>
			</tr>';
	}

	if (empty($context['previous_warnings']))
		echo '
			<tr class="windowbg2">
				<td align="center" colspan="4">
					', $txt['profile_warning_previous_none'], '
				</td>
			</tr>';

	echo '
			<tr class="catbg">
				<td colspan="4">
					', $txt['pages'], ': ', $context['page_index'], '
				</td>
			</tr>
		</table>';

	// Do our best to get pretty javascript enabled.
	echo '
	<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
		document.getElementById(\'warndiv1\').style.display = "";
		document.getElementById(\'warndiv2\').style.display = "none";
		document.getElementById(\'warn_temp\').disabled = "disabled";
		document.getElementById(\'warn_sub\').disabled = "disabled";
		document.getElementById(\'warn_body\').disabled = "disabled";
	// ]]></script>';
}

// Template to show for deleting a users account - now with added delete post capability!
function template_deleteAccount()
{
	global $context, $settings, $options, $scripturl, $txt, $scripturl;

	// The main containing header.
	echo '
		<form action="', $scripturl, '?action=profile;save" method="post" accept-charset="', $context['character_set'], '" name="creator" id="creator">
			<table border="0" width="85%" cellspacing="1" cellpadding="4" align="center" class="bordercolor">
				<tr class="titlebg">
					<td height="26">
						&nbsp;<img src="', $settings['images_url'], '/icons/profile_sm.gif" alt="" align="top" />&nbsp;
						', $txt['deleteAccount'], '
					</td>
				</tr>';
	// If deleting another account give them a lovely info box.
	if (!$context['user']['is_owner'])
	echo '
					<tr class="windowbg">
						<td class="smalltext" colspan="2" style="padding-top: 2ex; padding-bottom: 2ex;">
							', $txt['deleteAccount_desc'], '
						</td>
					</tr>';
	echo '
				<tr>
					<td class="windowbg2">
						<table width="100%" cellspacing="0" cellpadding="3"><tr>
							<td align="center" colspan="2">';

	// If they are deleting their account AND the admin needs to approve it - give them another piece of info ;)
	if ($context['needs_approval'])
		echo '
								<div style="color: red; border: 2px dashed red; padding: 4px;">', $txt['deleteAccount_approval'], '</div><br />
							</td>
						</tr><tr>
							<td align="center" colspan="2">';

	// If the user is deleting their own account warn them first - and require a password!
	if ($context['user']['is_owner'])
	{
		echo '
								<span style="color: red;">', $txt['own_profile_confirm'], '</span><br /><br />
							</td>
						</tr><tr>
							<td class="windowbg2" align="', !$context['right_to_left'] ? 'right' : 'left', '">
								<b', (isset($context['modify_error']['bad_password']) || isset($context['modify_error']['no_password']) ? ' style="color: red;"' : ''), '>', $txt['current_password'], ': </b>
							</td>
							<td class="windowbg2" align="', !$context['right_to_left'] ? 'left' : 'right', '">
								<input type="password" name="oldpasswrd" size="20" />&nbsp;&nbsp;&nbsp;&nbsp;
								<input type="submit" value="', $txt['yes'], '" />
								<input type="hidden" name="sc" value="', $context['session_id'], '" />
								<input type="hidden" name="u" value="', $context['id_member'], '" />
								<input type="hidden" name="sa" value="', $context['menu_item_selected'], '" />
							</td>';
	}
	// Otherwise an admin doesn't need to enter a password - but they still get a warning - plus the option to delete lovely posts!
	else
	{
		echo '
								<div style="color: red; margin-bottom: 2ex;">', $txt['deleteAccount_warning'], '</div>
							</td>
						</tr>';

		// Only actually give these options if they are kind of important.
		if ($context['can_delete_posts'])
			echo '
						<tr>
							<td colspan="2" align="center">
								', $txt['deleteAccount_posts'], ': <select name="remove_type">
									<option value="none">', $txt['deleteAccount_none'], '</option>
									<option value="posts">', $txt['deleteAccount_all_posts'], '</option>
									<option value="topics">', $txt['deleteAccount_topics'], '</option>
								</select>
							</td>
						</tr>';

		echo '
						<tr>
							<td colspan="2" align="center">
								<label for="deleteAccount"><input type="checkbox" name="deleteAccount" id="deleteAccount" value="1" class="check" onclick="if (this.checked) return confirm(\'', $txt['deleteAccount_confirm'], '\');" /> ', $txt['deleteAccount_member'], '.</label>
							</td>
						</tr>
						<tr>
							<td colspan="2" class="windowbg2" align="center" style="padding-top: 2ex;">
								<input type="submit" value="', $txt['delete'], '" />
								<input type="hidden" name="sc" value="', $context['session_id'], '" />
								<input type="hidden" name="u" value="', $context['id_member'], '" />
								<input type="hidden" name="sa" value="', $context['menu_item_selected'], '" />
							</td>';
	}
	echo '
						</tr></table>
					</td>
				</tr>
			</table>
		</form>';
}

// Template for the password box/save button stuck at the bottom of every profile page.
function template_profile_save()
{
	global $context, $settings, $options, $txt;

	// Are there any custom profile fields - if so print them!
	if (!empty($context['custom_fields']))
	{
		echo '
							<tr>
								<td colspan="2"><hr width="100%" size="1" class="hrcolor" /></td>
							</tr>';

		foreach ($context['custom_fields'] as $field)
		{
			echo '
							<tr valign="top">
								<td width="40%"><b>', $field['name'], ': </b><div class="smalltext">', $field['desc'], '</div></td>
								<td>', $field['input_html'], '</td>
							</tr>';
		}
	}

	echo '
							<tr>
								<td colspan="2"><hr width="100%" size="1" class="hrcolor" /></td>
							</tr><tr>';

	// Only show the password box if it's actually needed.
	if ($context['user']['is_owner'] && $context['require_password'])
		echo '
								<td width="40%">
									<b', isset($context['modify_error']['bad_password']) || isset($context['modify_error']['no_password']) ? ' style="color: red;"' : '', '>', $txt['current_password'], ': </b>
									<div class="smalltext">', $txt['required_security_reasons'], '</div>
								</td>
								<td>
									<input type="password" name="oldpasswrd" size="20" style="margin-right: 4ex;" />';
	else
		echo '
								<td align="right" colspan="2">';

	echo '
									<input type="submit" value="', $txt['change_profile'], '" />
									<input type="hidden" name="sc" value="', $context['session_id'], '" />
									<input type="hidden" name="u" value="', $context['id_member'], '" />
									<input type="hidden" name="sa" value="', $context['menu_item_selected'], '" />
								</td>
							</tr>';
}

// Small template for showing an error message upon a save problem in the profile.
function template_error_message()
{
	global $context, $txt;

	echo '
		<div class="windowbg" style="margin: 1ex; padding: 1ex 2ex; border: 1px dashed red; color: red;">
			<span style="text-decoration: underline;">', $txt['profile_errors_occurred'], ':</span>
			<ul>';

		// Cycle through each error and display an error message.
		foreach ($context['post_errors'] as $error)
			//if (isset($txt['profile_error_' . $error]))
				echo '
				<li>', $txt['profile_error_' . $error], '.</li>';

		echo '
			</ul>
		</div>';
}

// Display a load of drop down selectors for allowing the user to change group.
function template_profile_group_manage()
{
	global $context, $txt, $scripturl;

	echo '
							<tr>
								<td valign="top">
									<b>', $txt['primary_membergroup'], ': </b>
									<div class="smalltext">(<a href="', $scripturl, '?action=helpadmin;help=moderator_why_missing" onclick="return reqWin(this.href);">', $txt['moderator_why_missing'], '</a>)</div>
								</td>
								<td>
									<select name="id_group" ', ($context['user']['is_owner'] && $context['member']['group'] == 1 ? 'onchange="if (this.value != 1 && !confirm(\'' . $txt['deadmin_confirm'] . '\')) this.value = 1;"' : ''), '>';
		// Fill the select box with all primary member groups that can be assigned to a member.
		foreach ($context['member_groups'] as $member_group)
			if (!empty($member_group['can_be_primary']))
				echo '
										<option value="', $member_group['id'], '"', $member_group['is_primary'] ? ' selected="selected"' : '', '>
											', $member_group['name'], '
										</option>';
		echo '
									</select>
								</td>
							</tr>
							<tr>
								<td valign="top"><b>', $txt['additional_membergroups'], ':</b></td>
								<td>
									<div id="additional_groupsList">
										<input type="hidden" name="additional_groups[]" value="0" />';
		// For each membergroup show a checkbox so members can be assigned to more than one group.
		foreach ($context['member_groups'] as $member_group)
			if ($member_group['can_be_additional'])
				echo '
										<label for="additional_groups-', $member_group['id'], '"><input type="checkbox" name="additional_groups[]" value="', $member_group['id'], '" id="additional_groups-', $member_group['id'], '"', $member_group['is_additional'] ? ' checked="checked"' : '', ' class="check" /> ', $member_group['name'], '</label><br />';
		echo '
									</div>
									<a href="javascript:void(0);" onclick="document.getElementById(\'additional_groupsList\').style.display = \'block\'; document.getElementById(\'additional_groupsLink\').style.display = \'none\'; return false;" id="additional_groupsLink" style="display: none;">', $txt['additional_membergroups_show'], '</a>
									<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
										document.getElementById("additional_groupsList").style.display = "none";
										document.getElementById("additional_groupsLink").style.display = "";
									// ]]></script>
								</td>
							</tr>';

}

// Callback function for entering a birthdate!
function template_profile_birthdate()
{
	global $txt, $context;

	// Just show the pretty box!
	echo '
							<tr>
								<td width="40%">
									<b>', $txt['dob'], ':</b>
									<div class="smalltext">', $txt['dob_year'], ' - ', $txt['dob_month'], ' - ', $txt['dob_day'], '</div>
								</td>
								<td class="smalltext">
									<input type="text" name="bday3" size="4" maxlength="4" value="', $context['member']['birth_date']['year'], '" /> -
									<input type="text" name="bday1" size="2" maxlength="2" value="', $context['member']['birth_date']['month'], '" /> -
									<input type="text" name="bday2" size="2" maxlength="2" value="', $context['member']['birth_date']['day'], '" />
								</td>
							</tr>';
}

// Show the signature editing box?
function template_profile_signature_modify()
{
	global $txt, $context, $settings;

	echo '
							<tr>
								<td width="40%" valign="top">
									<b>', $txt['signature'], ':</b>
									<div class="smalltext">', $txt['sig_info'], '</div><br />
									<br />';

	if ($context['show_spellchecking'])
		echo '
									<input type="button" value="', $txt['spell_check'], '" onclick="spellCheck(\'creator\', \'signature\');" />';

		echo '
								</td>
								<td>
									<textarea class="editor" onkeyup="calcCharLeft();" name="signature" rows="5" cols="50">', $context['member']['signature'], '</textarea><br />';

	// If there is a limit at all!
	if (!empty($context['signature_limits']['max_length']))
		echo '
									<span class="smalltext">', sprintf($txt['max_sig_characters'], $context['signature_limits']['max_length']), ' <span id="signatureLeft">', $context['signature_limits']['max_length'], '</span></span><br />';

	if ($context['signature_warning'])
		echo '
									<span class="smalltext">', $context['signature_warning'], '</span>';

	// Load the spell checker?
	if ($context['show_spellchecking'])
		echo '
									<script language="JavaScript" type="text/javascript" src="', $settings['default_theme_url'], '/scripts/spellcheck.js"></script>';

	// Some javascript used to count how many characters have been used so far in the signature.
	echo '
									<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
										function tick()
										{
											if (typeof(document.forms.creator) != "undefined")
											{
												calcCharLeft();
												setTimeout("tick()", 1000);
											}
											else
												setTimeout("tick()", 800);
										}

										function calcCharLeft()
										{
											var maxLength = ', $context['signature_limits']['max_length'], ';
											var oldSignature = "", currentSignature = document.forms.creator.signature.value;

											if (!document.getElementById("signatureLeft"))
												return;

											if (oldSignature != currentSignature)
											{
												oldSignature = currentSignature;

												if (currentSignature.replace(/\r/, "").length > maxLength)
													document.forms.creator.signature.value = currentSignature.replace(/\r/, "").substring(0, maxLength);
												currentSignature = document.forms.creator.signature.value.replace(/\r/, "");
											}

											setInnerHTML(document.getElementById("signatureLeft"), maxLength - currentSignature.length);
										}

										setTimeout("tick()", 800);
									// ]]></script>
								</td>
							</tr>';
}

function template_profile_avatar_select()
{
	global $context, $txt, $modSettings;

	// If users are allowed to choose avatars stored on the server show selection boxes to choice them from.
	if (!empty($context['member']['avatar']['allow_server_stored']))
	{
		echo '
							<tr>
								<td width="40%" valign="top" style="padding: 0 2px;">
									<table width="100%" cellpadding="5" cellspacing="0" border="0" style="height: 25ex;"><tr>
										<td valign="top" width="20" class="windowbg"><input type="radio" name="avatar_choice" id="avatar_choice_server_stored" value="server_stored"', ($context['member']['avatar']['choice'] == 'server_stored' ? ' checked="checked"' : ''), ' class="check" /></td>
										<td valign="top" style="padding-left: 1ex;">
											<b', (isset($context['modify_error']['bad_avatar']) ? ' style="color: red;"' : ''), '><label for="avatar_choice_server_stored">', $txt['personal_picture'], ':</label></b>
											<div style="margin: 2ex;"><img name="avatar" id="avatar" src="', !empty($context['member']['avatar']['allow_external']) && $context['member']['avatar']['choice'] == 'external' ? $context['member']['avatar']['external'] : $modSettings['avatar_url'] . '/blank.gif', '" alt="Do Nothing" /></div>
										</td>
									</tr></table>
								</td>
								<td>
									<table width="100%" cellpadding="0" cellspacing="0" border="0"><tr>
										<td style="width: 20ex;">
											<select name="cat" id="cat" size="10" onchange="changeSel(\'\');" onfocus="selectRadioByName(document.forms.creator.avatar_choice, \'server_stored\');">';
		// This lists all the file catergories.
		foreach ($context['avatars'] as $avatar)
			echo '
												<option value="', $avatar['filename'] . ($avatar['is_dir'] ? '/' : ''), '"', ($avatar['checked'] ? ' selected="selected"' : ''), '>', $avatar['name'], '</option>';
		echo '
											</select>
										</td>
										<td>
											<select name="file" id="file" size="10" style="display: none;" onchange="showAvatar()" onfocus="selectRadioByName(document.forms.creator.avatar_choice, \'server_stored\');" disabled="disabled"><option></option></select>
										</td>
									</tr></table>
								</td>
							</tr>';
	}

	// If the user can link to an off server avatar, show them a box to input the address.
	if (!empty($context['member']['avatar']['allow_external']))
	{
		echo '
							<tr>
								<td valign="top" style="padding: 0 2px;">
									<table width="100%" cellpadding="5" cellspacing="0" border="0"><tr>
										<td valign="top" width="20" class="windowbg"><input type="radio" name="avatar_choice" id="avatar_choice_external" value="external"', ($context['member']['avatar']['choice'] == 'external' ? ' checked="checked"' : ''), ' class="check" /></td>
										<td valign="top" style="padding-left: 1ex;"><b><label for="avatar_choice_external">', $txt['my_own_pic'], ':</label></b><div class="smalltext">', $txt['avatar_by_url'], '</div></td>
									</tr></table>
								</td>
								<td valign="top">
									<input type="text" name="userpicpersonal" size="45" value="', $context['member']['avatar']['external'], '" onfocus="selectRadioByName(document.forms.creator.avatar_choice, \'external\');" onchange="if (typeof(previewExternalAvatar) != \'undefined\') previewExternalAvatar(this.value);" />
								</td>
							</tr>';
	}

	// If the user is able to upload avatars to the server show them an upload box.
	if (!empty($context['member']['avatar']['allow_upload']))
	{
		echo '
							<tr>
								<td valign="top" style="padding: 0 2px;">
									<table width="100%" cellpadding="5" cellspacing="0" border="0"><tr>
										<td valign="top" width="20" class="windowbg"><input type="radio" name="avatar_choice" id="avatar_choice_upload" value="upload"', ($context['member']['avatar']['choice'] == 'upload' ? ' checked="checked"' : ''), ' class="check" /></td>
										<td valign="top" style="padding-left: 1ex;"><b><label for="avatar_choice_upload">', $txt['avatar_will_upload'], ':</label></b></td>
									</tr></table>
								</td>
								<td valign="top">
									', ($context['member']['avatar']['id_attach'] > 0 ? '<img src="' . $context['member']['avatar']['href'] . '" /><input type="hidden" name="id_attach" value="' . $context['member']['avatar']['id_attach'] . '" /><br /><br />' : ''), '
									<input type="file" size="48" name="attachment" value="" onfocus="selectRadioByName(document.forms.creator.avatar_choice, \'upload\');" />
								</td>
							</tr>';
	}

	/* If the user is allowed to choose avatars stored on the server, the below javascript is used to update the
	file listing of avatars as the user changes catergory. It also updates the preview image as they choose
	different files on the select box. */
	if (!empty($context['member']['avatar']['allow_server_stored']))
		echo '
	<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
		var files = ["' . implode('", "', $context['avatar_list']) . '"];
		var avatar = document.getElementById("avatar");
		var cat = document.getElementById("cat");
		var selavatar = "' . $context['avatar_selected'] . '";
		var avatardir = "' . $modSettings['avatar_url'] . '/";
		var size = avatar.alt.substr(3, 2) + " " + avatar.alt.substr(0, 2) + String.fromCharCode(117, 98, 116);
		var file = document.getElementById("file");

		if (avatar.src.indexOf("blank.gif") > -1)
			changeSel(selavatar);
		else
			previewExternalAvatar(avatar.src)

		function changeSel(selected)
		{
			if (cat.selectedIndex == -1)
				return;

			if (cat.options[cat.selectedIndex].value.indexOf("/") > 0)
			{
				var i;
				var count = 0;

				file.style.display = "inline";
				file.disabled = false;

				for (i = file.length; i >= 0; i = i - 1)
					file.options[i] = null;

				for (i = 0; i < files.length; i++)
					if (files[i].indexOf(cat.options[cat.selectedIndex].value) == 0)
					{
						var filename = files[i].substr(files[i].indexOf("/") + 1);
						var showFilename = filename.substr(0, filename.lastIndexOf("."));
						showFilename = showFilename.replace(/[_]/g, " ");

						file.options[count] = new Option(showFilename, files[i]);

						if (filename == selected)
						{
							if (file.options.defaultSelected)
								file.options[count].defaultSelected = true;
							else
								file.options[count].selected = true;
						}

						count++;
					}

				if (file.selectedIndex == -1 && file.options[0])
					file.options[0].selected = true;

				showAvatar();
			}
			else
			{
				file.style.display = "none";
				file.disabled = true;
				document.getElementById("avatar").src = avatardir + cat.options[cat.selectedIndex].value;
				document.getElementById("avatar").style.width = "";
				document.getElementById("avatar").style.height = "";
			}
		}

		function showAvatar()
		{
			if (file.selectedIndex == -1)
				return;

			document.getElementById("avatar").src = avatardir + file.options[file.selectedIndex].value;
			document.getElementById("avatar").alt = file.options[file.selectedIndex].text;
			document.getElementById("avatar").alt += file.options[file.selectedIndex].text == size ? "!" : "";
			document.getElementById("avatar").style.width = "";
			document.getElementById("avatar").style.height = "";
		}

		function previewExternalAvatar(src)
		{
			if (!document.getElementById("avatar"))
				return;

			var maxHeight = ', !empty($modSettings['avatar_max_height_external']) ? $modSettings['avatar_max_height_external'] : 0, ';
			var maxWidth = ', !empty($modSettings['avatar_max_width_external']) ? $modSettings['avatar_max_width_external'] : 0, ';
			var tempImage = new Image();

			tempImage.src = src;
			if (maxWidth != 0 && tempImage.width > maxWidth)
			{
				document.getElementById("avatar").style.height = parseInt((maxWidth * tempImage.height) / tempImage.width) + "px";
				document.getElementById("avatar").style.width = maxWidth + "px";
			}
			else if (maxHeight != 0 && tempImage.height > maxHeight)
			{
				document.getElementById("avatar").style.width = parseInt((maxHeight * tempImage.width) / tempImage.height) + "px";
				document.getElementById("avatar").style.height = maxHeight + "px";
			}
			document.getElementById("avatar").src = src;
		}
	// ]]></script>';
}

// Callback for modifying karam.
function template_profile_karma_modify()
{
	global $context, $modSettings, $txt;

		echo '
							<tr>
								<td valign="top"><b>', $modSettings['karmaLabel'], '</b></td>
								<td>
									', $modSettings['karmaApplaudLabel'], ' <input type="text" name="karma_good" size="4" value="', $context['member']['karma']['good'], '" onchange="setInnerHTML(document.getElementById(\'karmaTotal\'), this.value - this.form.karma_bad.value);" style="margin-right: 2ex;" /> ', $modSettings['karmaSmiteLabel'], ' <input type="text" name="karma_bad" size="4" value="', $context['member']['karma']['bad'], '" onchange="this.form.karma_good.onchange();" /><br />
									(', $txt['total'], ': <span id="karmaTotal">', ($context['member']['karma']['good'] - $context['member']['karma']['bad']), '</span>)
								</td>
							</tr>';
}

// Select the time format!
function template_profile_timeformat_modify()
{
	global $context, $modSettings, $txt, $scripturl, $settings;

	echo '
							<tr>
								<td width="40%">
									<b>', $txt['time_format'], ':</b><br />
									<a href="', $scripturl, '?action=helpadmin;help=time_format" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt['help'], '" align="', !$context['right_to_left'] ? 'left' : 'right', '" style="', !$context['right_to_left'] ? 'padding-right' : 'padding-left', ': 1ex;" /></a>
									<span class="smalltext">', $txt['date_format'], '</span>
								</td>
								<td>
									<select name="easyformat" onchange="document.forms.creator.time_format.value = this.options[this.selectedIndex].value;" style="margin-bottom: 4px;">';
	// Help the user by showing a list of common time formats.
	foreach ($context['easy_timeformats'] as $time_format)
		echo '
										<option value="', $time_format['format'], '"', $time_format['format'] == $context['member']['time_format'] ? ' selected="selected"' : '', '>', $time_format['title'], '</option>';
	echo '
									</select><br />
									<input type="text" name="time_format" value="', $context['member']['time_format'], '" size="30" />
								</td>
							</tr>';
}

// Time offset?
function template_profile_timeoffset_modify()
{
	global $txt, $context;

	echo '
							<tr>
								<td width="40%"><b', (isset($context['modify_error']['bad_offset']) ? ' style="color: red;"' : ''), '>', $txt['time_offset'], ':</b><div class="smalltext">', $txt['personal_time_offset'], '</div></td>
								<td class="smalltext"><input type="text" name="time_offset" id="time_offset" size="5" maxlength="5" value="', $context['member']['time_offset'], '" /> <a href="javascript:void(0);" onclick="document.getElementById(\'time_offset\').value = autoDetectTimeOffset(\'', $context['current_forum_time'], '\'); return false;">', $txt['timeoffset_autodetect'], '</a><br />', $txt['current_time'], ': <i>', $context['current_forum_time'], '</i></td>
							</tr>';
}

// Theme?
function template_profile_theme_pick()
{
	global $txt, $context, $scripturl;

	echo '
							<tr>
								<td colspan="2" width="40%"><b>', $txt['current_theme'], ':</b> ', $context['member']['theme']['name'], ' <a href="', $scripturl, '?action=theme;sa=pick;u=', $context['id_member'], ';sesc=', $context['session_id'], '">', $txt['change'], '</a></td>
							</tr>';
}

// Smiley set picker.
function template_profile_smiley_pick()
{
	global $txt, $context, $modSettings, $settings;

	echo '
							<tr>
								<td colspan="2" width="40%">
									<b>', $txt['smileys_current'], ':</b>
									<select name="smiley_set" onchange="document.getElementById(\'smileypr\').src = this.selectedIndex == 0 ? \'', $settings['images_url'], '/blank.gif\' : \'', $modSettings['smileys_url'], '/\' + (this.selectedIndex != 1 ? this.options[this.selectedIndex].value : \'', !empty($settings['smiley_sets_default']) ? $settings['smiley_sets_default'] : $modSettings['smiley_sets_default'], '\') + \'/smiley.gif\';">';
	foreach ($context['smiley_sets'] as $set)
		echo '
										<option value="', $set['id'], '"', $set['selected'] ? ' selected="selected"' : '', '>', $set['name'], '</option>';
	echo '
									</select> <img id="smileypr" src="', $context['member']['smiley_set']['id'] != 'none' ? $modSettings['smileys_url'] . '/' . ($context['member']['smiley_set']['id'] != '' ? $context['member']['smiley_set']['id'] : (!empty($settings['smiley_sets_default']) ? $settings['smiley_sets_default'] : $modSettings['smiley_sets_default'])) . '/smiley.gif' : $settings['images_url'] . '/blank.gif', '" alt=":)" align="top" style="padding-left: 20px;" />
								</td>
							</tr>';
}

?>