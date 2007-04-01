<?php
// Version: 2.0 Alpha; PersonalMessage

// This is the main sidebar for the personal messages section.
function template_pm_above()
{
	global $context, $settings, $options, $txt;

	echo '
		<div style="padding: 3px;">', theme_linktree(), '</div>';

	echo '
			<table width="100%" border="0" cellpadding="0" cellspacing="0"><tr>
				<td width="125" valign="top">
					<table border="0" cellpadding="4" cellspacing="1" class="bordercolor" width="100">';
	// Loop through every main area - giving a nice section heading.
	foreach ($context['pm_areas'] as $section)
	{
		echo '
						<tr>
							<td class="catbg">', $section['title'], '</td>
						</tr>
						<tr class="windowbg2">
							<td class="smalltext" style="padding-bottom: 2ex;">';
		// Each sub area.
		foreach ($section['areas'] as $i => $area)
		{
			if (empty($area))
				echo '<br />';
			// Special case for the capacity bar.
			elseif (!empty($area['limit_bar']))
			{
				// !!! Hardcoded colors = bad.
				echo '
								<br /><br />
								<div align="center">
									<b>', $txt['pm_capacity'], '</b>
									<div align="left" style="border: 1px solid black; height: 7px; width: 100px;">
										<div style="border: 0; background-color: ', $context['limit_bar']['percent'] > 85 ? '#A53D05' : ($context['limit_bar']['percent'] > 40 ? '#EEA800' : '#468008'), '; height: 7px; width: ', $context['limit_bar']['bar'], 'px;"></div>
									</div>
									<span', ($context['limit_bar']['percent'] > 90 ? ' style="color: red;"' : ''), '>', $context['limit_bar']['text'], '</span>
								</div>
								<br />';
			}
			else
			{
				if ($i == $context['pm_area'])
					echo '
								<b>', $area['link'], (empty($area['unread_messages']) ? '' : ' (<b>' . $area['unread_messages'] . '</b>)'), '</b><br />';
				else
					echo '
								', $area['link'], (empty($area['unread_messages']) ? '' : ' (<b>' . $area['unread_messages'] . '</b>)'), '<br />';
			}
		}
		echo '
							</td>
						</tr>';
	}
	echo '
					</table>
					<br />
				</td>
				<td valign="top">';
}

// Just the end of the index bar, nothing special.
function template_pm_below()
{
	global $context, $settings, $options;

	echo '
				</td>
			</tr></table>';
}

function template_folder()
{
	global $context, $settings, $options, $scripturl, $modSettings, $txt;

	// The every helpful javascript!
	echo '
	<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
		var allLabels = {};
		var currentLabels = {};
		function loadLabelChoices()
		{
			var listing = document.forms.pmFolder.elements;
			var theSelect = document.forms.pmFolder.pm_action;
			var add, remove, toAdd = {length: 0}, toRemove = {length: 0};

			if (theSelect.childNodes.length == 0)
				return;';

	// This is done this way for internationalization reasons.
	echo '
			if (typeof(allLabels[-1]) == "undefined")
			{
				for (var o = 0; o < theSelect.options.length; o++)
					if (theSelect.options[o].value.substr(0, 4) == "rem_")
						allLabels[theSelect.options[o].value.substr(4)] = theSelect.options[o].text;
			}

			for (var i = 0; i < listing.length; i++)
			{
				if (listing[i].name != "pms[]" || !listing[i].checked)
					continue;

				var alreadyThere = [], x;
				for (x in currentLabels[listing[i].value])
				{
					if (typeof(toRemove[x]) == "undefined")
					{
						toRemove[x] = allLabels[x];
						toRemove.length++;
					}
					alreadyThere[x] = allLabels[x];
				}

				for (x in allLabels)
				{
					if (typeof(alreadyThere[x]) == "undefined")
					{
						toAdd[x] = allLabels[x];
						toAdd.length++;
					}
				}
			}

			while (theSelect.options.length > 2)
				theSelect.options[2] = null;

			if (toAdd.length != 0)
			{
				theSelect.options[theSelect.options.length] = new Option("', $txt['pm_msg_label_apply'], '", "");
				setInnerHTML(theSelect.options[theSelect.options.length - 1], "', $txt['pm_msg_label_apply'], '");
				theSelect.options[theSelect.options.length - 1].disabled = true;

				for (i in toAdd)
				{
					if (i != "length")
						theSelect.options[theSelect.options.length] = new Option(toAdd[i], "add_" + i);
				}
			}

			if (toRemove.length != 0)
			{
				theSelect.options[theSelect.options.length] = new Option("', $txt['pm_msg_label_remove'], '", "");
				setInnerHTML(theSelect.options[theSelect.options.length - 1], "', $txt['pm_msg_label_remove'], '");
				theSelect.options[theSelect.options.length - 1].disabled = true;

				for (i in toRemove)
				{
					if (i != "length")
						theSelect.options[theSelect.options.length] = new Option(toRemove[i], "rem_" + i);
				}
			}
		}
	// ]]></script>';

	echo '
<form action="', $scripturl, '?action=pm;sa=pmactions;f=', $context['folder'], ';start=', $context['start'], $context['current_label_id'] != -1 ? ';l=' . $context['current_label_id'] : '', '" method="post" accept-charset="', $context['character_set'], '" name="pmFolder">';

	// If we are not in single display mode show the subjects on the top!
	if ($context['display_mode'] != 1)
	{
		template_subject_list();
		echo '<br />';
	}

	// Got some messages to display?
	if ($context['get_pmessage']('message', true))
	{
		// Show the helpful titlebar - generally.
		if ($context['display_mode'] != 1)
			echo '
		<table cellpadding="4" cellspacing="0" border="0" width="100%" class="bordercolor">
			<tr class="titlebg">
				<td width="16%">&nbsp;', $txt['author'], '</td>
				<td>', $txt['topic'], '</td>
			</tr>
		</table>';

		echo '
		<table cellpadding="0" cellspacing="0" border="0" width="100%" class="bordercolor">';

		// Cache some handy buttons.
		$quote_button = create_button('quote.gif', 'reply_quote', 'quote', 'align="middle"');
		$reply_button = create_button('im_reply.gif', 'reply', 'reply', 'align="middle"');
		$reply_all_button = create_button('im_reply_all.gif', 'reply_to_all', 'reply_to_all', 'align="middle"');
		$forward_button = create_button('quote.gif', 'reply_quote', 'reply_quote', 'align="middle"');
		$delete_button = create_button('delete.gif', 'remove_message', 'remove', 'align="middle"');

		while ($message = $context['get_pmessage']('message'))
		{
			$windowcss = $message['alternate'] == 0 ? 'windowbg' : 'windowbg2';

			echo '
		<tr><td style="padding: 1px 1px 0 1px;">
			<a name="msg', $message['id'], '"></a>
			<table width="100%" cellpadding="3" cellspacing="0" border="0">
				<tr><td colspan="2" class="', $windowcss, '">
					<table width="100%" cellpadding="4" cellspacing="1" style="table-layout: fixed;">
						<tr>
							<td valign="top" width="16%" rowspan="2" style="overflow: hidden;">
								<b>', $message['member']['link'], '</b>
								<div class="smalltext">';
			if (isset($message['member']['title']) && $message['member']['title'] != '')
				echo '
									', $message['member']['title'], '<br />';
			if (isset($message['member']['group']) && $message['member']['group'] != '')
				echo '
									', $message['member']['group'], '<br />';

			if (!$message['member']['is_guest'])
			{
				// Show the post group if and only if they have no other group or the option is on, and they are in a post group.
				if ((empty($settings['hide_post_group']) || $message['member']['group'] == '') && $message['member']['post_group'] != '')
					echo '
									', $message['member']['post_group'], '<br />';
				echo '
									', $message['member']['group_stars'], '<br />';

				// Is karma display enabled? Total or +/-?
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
									<a href="', $scripturl, '?action=modifykarma;sa=applaud;uid=', $message['member']['id'], ';f=', $context['folder'], ';start=', $context['start'], ';', $context['current_label_id'] != -1 ? ';l=' . $context['current_label_id'] : '', ';pm=', $message['id'], ';sesc=', $context['session_id'], '">', $modSettings['karmaApplaudLabel'], '</a> <a href="', $scripturl, '?action=modifykarma;sa=smite;uid=', $message['member']['id'], ';f=', $context['folder'], ';start=', $context['start'], ';', $context['current_label_id'] != -1 ? ';l=' . $context['current_label_id'] : '', ';pm=', $message['id'], ';sesc=', $context['session_id'], '">', $modSettings['karmaSmiteLabel'], '</a><br />';

				// Show online and offline buttons?
				if (!empty($modSettings['onlineEnable']) && !$message['member']['is_guest'])
				echo '
									', $context['can_send_pm'] ? '<a href="' . $message['member']['online']['href'] . '" title="' . $message['member']['online']['label'] . '">' : '', $settings['use_image_buttons'] ? '<img src="' . $message['member']['online']['image_href'] . '" style="margin-top: 4px;" alt="' . $message['member']['online']['text'] . '" />' : $message['member']['online']['text'], $context['can_send_pm'] ? '</a>' : '', $settings['use_image_buttons'] ? '<span class="smalltext"> ' . $message['member']['online']['text'] . '</span>' : '', '<br /><br />';

				// Show the member's gender icon?
				if (!empty($settings['show_gender']) && $message['member']['gender']['image'] != '')
					echo '
									', $txt['gender'], ': ', $message['member']['gender']['image'], '<br />';

				// Show how many posts they have made.
				echo '
									', $txt['member_postcount'], ': ', $message['member']['posts'], '<br />
									<br />';

				// Show avatars, images, etc.?
				if (!empty($settings['show_user_images']) && empty($options['show_no_avatars']))
					echo '
									', $message['member']['avatar']['image'], '<br />';

				// Show their personal text?
				if (!empty($settings['show_blurb']) && $message['member']['blurb'] != '')
					echo '
									', $message['member']['blurb'], '<br />
									<br />';
				echo '
									', $message['member']['icq']['link'], '
									', $message['member']['msn']['link'], '
									', $message['member']['yim']['link'], '
									', $message['member']['aim']['link'], '<br />';

				// Show the profile, website, email address, and personal message buttons.
				if ($settings['show_profile_buttons'])
				{
					echo '
									<a href="', $message['member']['href'], '">', ($settings['use_image_buttons'] ? '<img src="' . $settings['images_url'] . '/icons/profile_sm.gif" alt="' . $txt['view_profile'] . '" title="' . $txt['view_profile'] . '" />' : $txt['view_profile']), '</a>';
					if ($message['member']['website']['url'] != '')
						echo '
									<a href="', $message['member']['website']['url'], '" target="_blank">', ($settings['use_image_buttons'] ? '<img src="' . $settings['images_url'] . '/www_sm.gif" alt="' . $txt['www'] . '" title="' . $message['member']['website']['title'] . '" />' : $txt['www']), '</a>';
					if (empty($message['member']['hide_email']))
						echo '
									<a href="', !empty($modSettings['make_email_viewable']) ? 'mailto:' . $message['member']['email'] : $scripturl . '?action=emailuser;sa=email;msg=' . $message['member']['id'], '">', ($settings['use_image_buttons'] ? '<img src="' . $settings['images_url'] . '/email_sm.gif" alt="' . $txt['email'] . '" title="' . $txt['email'] . '" />' : $txt['email']), '</a>';
					if (!$context['user']['is_guest'] && $context['can_send_pm'])
						echo '
									<a href="', $scripturl, '?action=pm;sa=send;u=', $message['member']['id'], '" title="', $message['member']['online']['label'], '">', $settings['use_image_buttons'] ? '<img src="' . $settings['images_url'] . '/im_' . ($message['member']['online']['is_online'] ? 'on' : 'off') . '.gif" alt="' . $message['member']['online']['label'] . '" />' : $message['member']['online']['label'], '</a>';
				}
			}
			elseif (empty($message['member']['hide_email']))
				echo '
									<br />
									<br />
									<a href="mailto:', $message['member']['email'], '">', ($settings['use_image_buttons'] ? '<img src="' . $settings['images_url'] . '/email_sm.gif" alt="' . $txt['email'] . '" title="' . $txt['email'] . '" />' : $txt['email']), '</a>';
			echo '
								</div>
							</td>
							<td class="', $windowcss, '" valign="top" width="85%" height="100%">
								<table width="100%" border="0"><tr>
									<td align="left" valign="middle">
										<b>', $message['subject'], '</b>';

			// Show who the message was sent to.
			echo '
										<div class="smalltext">&#171; <b> ', $txt['sent_to'], ':</b> ';

			// People it was sent directly to....
			if (!empty($message['recipients']['to']))
				echo implode(', ', $message['recipients']['to']);
			// Otherwise, we're just going to say "some people"...
			elseif ($context['folder'] != 'sent')
				echo '(', $txt['pm_undisclosed_recipients'], ')';

			echo ' <b> ', $txt['on'], ':</b> ', $message['time'], ' &#187;</div>';

			// If we're in the sent items, show who it was sent to besides the "To:" people.
			if (!empty($message['recipients']['bcc']))
				echo '
										<div class="smalltext">&#171; <b> ', $txt['pm_bcc'], ':</b> ', implode(', ', $message['recipients']['bcc']), ' &#187;</div>';

			if (!empty($message['is_replied_to']))
				echo '
										<div class="smalltext">&#171; ', $txt['pm_is_replied_to'], ' &#187;</div>';

			echo '
									</td>
									<td align="right" valign="bottom" height="20" nowrap="nowrap" style="font-size: smaller;">';

			// Show reply buttons if you have the permission to send PMs.
			if ($context['can_send_pm'])
			{
				// You can't really reply if the member is gone.
				if (!$message['member']['is_guest'])
				{
					// Were than more than one recipient you can reply to? (Only in the "button style", or text)
					if ($message['number_recipients'] > 1 && (!empty($settings['use_buttons']) || !$settings['use_image_buttons']))
						echo '
										<a href="', $scripturl, '?action=pm;sa=send;f=', $context['folder'], $context['current_label_id'] != -1 ? ';l=' . $context['current_label_id'] : '', ';pmsg=', $message['id'], ';quote;u=all">', $reply_all_button, '</a>', $context['menu_separator'];
					echo '
										<a href="', $scripturl, '?action=pm;sa=send;f=', $context['folder'], $context['current_label_id'] != -1 ? ';l=' . $context['current_label_id'] : '', ';pmsg=', $message['id'], ';quote;u=', $context['folder'] == 'sent' ? '' : $message['member']['id'], '">', $quote_button, '</a>', $context['menu_separator'], '
										<a href="', $scripturl, '?action=pm;sa=send;f=', $context['folder'], $context['current_label_id'] != -1 ? ';l=' . $context['current_label_id'] : '', ';pmsg=', $message['id'], ';u=', $message['member']['id'], '">', $reply_button, '</a> ', $context['menu_separator'];
				}
				// This is for "forwarding" - even if the member is gone.
				else
					echo '
										<a href="', $scripturl, '?action=pm;sa=send;f=', $context['folder'], $context['current_label_id'] != -1 ? ';l=' . $context['current_label_id'] : '', ';pmsg=', $message['id'], ';quote">', $forward_button, '</a>', $context['menu_separator'];
			}
			echo '
										<a href="', $scripturl, '?action=pm;sa=pmactions;pm_actions[', $message['id'], ']=delete;f=', $context['folder'], ';start=', $context['start'], ';', $context['current_label_id'] != -1 ? ';l=' . $context['current_label_id'] : '', ';sesc=', $context['session_id'], '" onclick="return confirm(\'', addslashes($txt['remove_message']), '?\');">', $delete_button, '</a>';

			if (empty($context['display_mode']))
				echo '
										<input style="vertical-align: middle;" type="checkbox" name="pms[]" id="deletedisplay', $message['id'], '" value="', $message['id'], '" class="check" onclick="document.getElementById(\'deletelisting', $message['id'], '\').checked = this.checked;" />';

			echo '
									</td>
								</tr></table>
								<hr width="100%" size="1" class="hrcolor" />
								<div class="personalmessage">', $message['body'], '</div>
							</td>
						</tr>
						<tr class="', $windowcss, '">
							<td valign="bottom" class="smalltext" width="85%">
								', (!empty($modSettings['enableReportPM']) && $context['folder'] != 'sent' ? '<div align="right"><a href="' . $scripturl . '?action=pm;sa=report;l=' . $context['current_label_id'] . ';pmsg=' . $message['id'] . '" class="smalltext">' . $txt['pm_report_to_admin'] . '</a></div>' : '');

			// Show the member's signature?
			if (!empty($message['member']['signature']) && empty($options['show_no_signatures']) && $context['signature_enabled'])
				echo '
								<hr width="100%" size="1" class="hrcolor" />
								<div class="signature">', $message['member']['signature'], '</div>';

			echo '
							</td>
						</tr>';

		// Add an extra line at the bottom if we have labels enabled.
		if ($context['folder'] != 'sent' && !empty($context['currently_using_labels']))
		{
			echo '
						<tr class="', $windowcss, '">
							<td valign="bottom" colspan="2" width="100%" align="right">';
			// Add the label drop down box.
			if (!empty($context['currently_using_labels']))
			{
				echo '
								<select name="pm_actions[', $message['id'], ']" onchange="if (this.options[this.selectedIndex].value) form.submit();">
									<option value="">', $txt['pm_msg_label_title'], ':</option>
									<option value="" disabled="disabled">---------------</option>';
				// Are there any labels which can be added to this?
				if (!$message['fully_labeled'])
				{
					echo '
									<option value="" disabled="disabled">', $txt['pm_msg_label_apply'], ':</option>';
					foreach ($context['labels'] as $label)
					{
						if (!isset($message['labels'][$label['id']]))
							echo '
										<option value="', $label['id'], '">&nbsp;', $label['name'], '</option>';
					}
				}
				// ... and are there any that can be removed?
				if (!empty($message['labels']) && (count($message['labels']) > 1 || !isset($message['labels'][-1])))
				{
					echo '
									<option value="" disabled="disabled">', $txt['pm_msg_label_remove'], ':</option>';
					foreach ($message['labels'] as $label)
						echo '
									<option value="', $label['id'], '">&nbsp;', $label['name'], '</option>';
				}
				echo '
								</select>
								<noscript>
								<input type="submit" value="', $txt['pm_apply'], '" />
								</noscript>';
			}
			echo '
							</td>
						</tr>';
		}

		echo '
					</table>
				</td></tr>
			</table>
		</td></tr>';
		}

		echo '
			<tr><td style="padding: 0 0 1px 0;"></td></tr>
	</table>';

	if (empty($context['display_mode']))
		echo '
	<div class="tborder" style="padding: 1px; margin-top: 1ex;">
		<table cellpadding="3" cellspacing="0" border="0" width="100%">
			<tr class="catbg" valign="middle">
				<td height="25">
					<div style="float: left;">', $txt['pages'], ': ', $context['page_index'], '</div>
					<div style="float: right;"><input type="submit" name="del_selected" value="', $txt['quickmod_delete_selected'], '" style="font-weight: normal;" onclick="if (!confirm(\'', $txt['delete_selected_confirm'], '\')) return false;" /></div>
				</td>
			</tr>
		</table>
	</div>';

		echo '
		<br />';
	}

	// Individual messages = buttom list!
	if ($context['display_mode'] == 1)
	{
		template_subject_list();
		echo '<br />';
	}

	echo '
	<input type="hidden" name="sc" value="', $context['session_id'], '" />
</form>';
}

// Just list all the personal message subjects - to make templates easier.
function template_subject_list()
{
	global $context, $options, $settings, $modSettings, $txt, $scripturl;

	echo '
		<table border="0" width="100%" cellpadding="2" cellspacing="1" class="bordercolor">
		<tr class="titlebg">
			<td align="center" width="2%"><a href="', $scripturl, '?action=pm;view;f=', $context['folder'], ';start=', $context['start'], ';sort=', $context['sort_by'], ($context['sort_direction'] == 'up' ? ';' : ';desc'), ($context['current_label_id'] != -1 ? ';l=' . $context['current_label_id'] : ''), '"><img src="', $settings['images_url'], '/icons/info.gif" alt="', $txt['pm_change_view'], '" width="16" height="16" /></a></td>
			<td style="width: 32ex;"><a href="', $scripturl, '?action=pm;f=', $context['folder'], ';start=', $context['start'], ';sort=date', $context['sort_by'] == 'date' && $context['sort_direction'] == 'up' ? ';desc' : '', ';', $context['current_label_id'] != -1 ? ';l=' . $context['current_label_id'] : '', '">', $txt['date'], $context['sort_by'] == 'date' ? ' <img src="' . $settings['images_url'] . '/sort_' . $context['sort_direction'] . '.gif" alt="" />' : '', '</a></td>
			<td width="46%"><a href="', $scripturl, '?action=pm;f=', $context['folder'], ';start=', $context['start'], ';sort=subject', $context['sort_by'] == 'subject' && $context['sort_direction'] == 'up' ? ';desc' : '', ';', $context['current_label_id'] != -1 ? ';l=' . $context['current_label_id'] : '', '">', $txt['subject'], $context['sort_by'] == 'subject' ? ' <img src="' . $settings['images_url'] . '/sort_' . $context['sort_direction'] . '.gif" alt="" />' : '', '</a></td>
			<td><a href="', $scripturl, '?action=pm;f=', $context['folder'], ';start=', $context['start'], ';sort=name', $context['sort_by'] == 'name' && $context['sort_direction'] == 'up' ? ';desc' : '', ';', $context['current_label_id'] != -1 ? ';l=' . $context['current_label_id'] : '', '">', ($context['from_or_to'] == 'from' ? $txt['from'] : $txt['to']), $context['sort_by'] == 'name' ? ' <img src="' . $settings['images_url'] . '/sort_' . $context['sort_direction'] . '.gif" alt="" />' : '', '</a></td>
			<td align="center" width="24"><input type="checkbox" onclick="invertAll(this, this.form);" class="check" /></td>
		</tr>';
	if (!$context['show_delete'])
		echo '
		<tr>
			<td class="windowbg" colspan="5">', $txt['msg_alert_none'], '</td>
		</tr>';
	$next_alternate = 0;
	while ($message = $context['get_pmessage']('subject'))
	{
		echo '
		<tr class="', $next_alternate ? 'windowbg' : 'windowbg2', '">
			<td align="center" width="2%">
			<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
				currentLabels[', $message['id'], '] = {';

		if (!empty($message['labels']))
		{
			$first = true;
			foreach ($message['labels'] as $label)
			{
				echo $first ? '' : ',', '
				"', $label['id'], '": "', $label['name'], '"';
				$first = false;
			}
		}

		echo '
				};
			// ]]></script>
				', $message['is_replied_to'] ? '<img src="' . $settings['images_url'] . '/icons/pm_replied.gif" style="margin-right: 4px;" alt="' . $txt['pm_replied'] . '" />' : '<img src="' . $settings['images_url'] . '/icons/pm_read.gif" style="margin-right: 4px;" alt="' . $txt['pm_read'] . '" />', '</td>
			<td>', $message['time'], '</td>
			<td>', ($context['display_mode'] != 0 && $context['current_pm'] == $message['id'] ? '<img src="' . $settings['images_url'] . '/selected.gif" alt="*" />' : ''), '<a href="', ($context['display_mode'] == 0 || $context['current_pm'] == $message['id'] ? '' : ($scripturl . '?action=pm;pmid=' . $message['id'] . ';kstart;f=' . $context['folder'] . ';start=' . $context['start'] . ';sort=' . $context['sort_by'] . ($context['sort_direction'] == 'up' ? ';' : ';desc') . ($context['current_label_id'] != -1 ? ';l=' . $context['current_label_id'] : ''))), '#msg', $message['id'], '">', $message['subject'], '</a>', $message['is_unread'] ? '&nbsp;<img src="' . $settings['lang_images_url'] . '/new.gif" alt="' . $txt['new'] . '" />' : '', '</td>
			<td>', ($context['from_or_to'] == 'from' ? $message['member']['link'] : (empty($message['recipients']['to']) ? '' : implode(', ', $message['recipients']['to']))), '</td>
			<td align="center"><input type="checkbox" name="pms[]" id="deletelisting', $message['id'], '" value="', $message['id'], '"', $message['is_selected'] ? ' checked="checked"' : '', ' onclick="if (document.getElementById(\'deletedisplay', $message['id'], '\')) document.getElementById(\'deletedisplay', $message['id'], '\').checked = this.checked;" class="check" /></td>
		</tr>';
			$next_alternate = !$next_alternate;
	}

	echo '
	</table>
	<div class="bordercolor" style="padding: 1px; ', $context['browser']['needs_size_fix'] && !$context['browser']['is_ie6'] ? 'width: 100%;' : '', '">
		<table width="100%" cellpadding="2" cellspacing="0" border="0"><tr class="catbg" valign="middle">
			<td>
				<div style="float: left;">', $txt['pages'], ': ', $context['page_index'], '</div>
				<div style="float: right;">&nbsp;';

	if ($context['show_delete'])
	{
		if (!empty($context['currently_using_labels']) && $context['folder'] != 'sent')
		{
			echo '
				<select name="pm_action" onchange="if (this.options[this.selectedIndex].value) this.form.submit();" onfocus="loadLabelChoices();">
					<option value="">', $txt['pm_sel_label_title'], ':</option>
					<option value="" disabled="disabled">---------------</option>';

			echo '
									<option value="" disabled="disabled">', $txt['pm_msg_label_apply'], ':</option>';
			foreach ($context['labels'] as $label)
				if ($label['id'] != $context['current_label_id'])
					echo '
					<option value="add_', $label['id'], '">&nbsp;', $label['name'], '</option>';
			echo '
					<option value="" disabled="disabled">', $txt['pm_msg_label_remove'], ':</option>';
			foreach ($context['labels'] as $label)
				echo '
					<option value="rem_', $label['id'], '">&nbsp;', $label['name'], '</option>';
			echo '
				</select>
				<noscript>
					<input type="submit" value="', $txt['pm_apply'], '" />
				</noscript>';
		}

		echo '
				<input type="submit" name="del_selected" value="', $txt['quickmod_delete_selected'], '" style="font-weight: normal;" onclick="if (!confirm(\'', $txt['delete_selected_confirm'], '\')) return false;" />';
	}

	echo '
				</div>
			</td>
		</tr></table>
	</div>';
}

function template_search()
{
	global $context, $settings, $options, $scripturl, $modSettings, $txt;

	echo '
	<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
		function expandCollapseLabels()
		{
			var current = document.getElementById("searchLabelsExpand").style.display != "none";

			document.getElementById("searchLabelsExpand").style.display = current ? "none" : "";
			document.getElementById("expandLabelsIcon").src = smf_images_url + (current ? "/expand.gif" : "/collapse.gif");
		}
	// ]]></script>
<form action="', $scripturl, '?action=pm;sa=search2" method="post" accept-charset="', $context['character_set'], '" name="pmSearchForm">
	<table border="0" width="75%" align="center" cellpadding="3" cellspacing="0" class="tborder">
		<tr class="titlebg">
			<td colspan="2">', $txt['pm_search_title'], '</td>
		</tr>';

	if (!empty($context['search_errors']))
	{
		echo '
			<tr>
				<td class="windowbg">
					<div style="color: red; margin: 1ex 0 2ex 3ex;">
						', implode('<br />', $context['search_errors']['messages']), '
					</div>
				</td>
			</tr>';
	}

	echo '
			<tr>
				<td class="windowbg">';

	if ($context['simple_search'])
	{
		echo '
					<b>', $txt['pm_search_text'], ':</b><br />
					<input type="text" name="search"', !empty($context['search_params']['search']) ? ' value="' . $context['search_params']['search'] . '"' : '', ' size="40" />&nbsp;
					<input type="submit" name="submit" value="', $txt['pm_search_go'], '" /><br />
					<a href="', $scripturl, '?action=pm;sa=search;advanced" onclick="this.href += \';search=\' + escape(document.forms.pmSearchForm.search.value);">', $txt['pm_search_advanced'], '</a>
					<input type="hidden" name="advanced" value="0" />';
	}
	else
	{
		echo '
					<input type="hidden" name="advanced" value="1" />
					<table cellpadding="1" cellspacing="3" border="0">
						<tr>
							<td>
								<b>', $txt['pm_search_text'], ':</b>
							</td>
							<td></td>
							<td>
								<b>', $txt['pm_search_user'], ':</b>
							</td>
						</tr><tr>
							<td>
								<input type="text" name="search"', !empty($context['search_params']['search']) ? ' value="' . $context['search_params']['search'] . '"' : '', ' size="40" />
								<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
									function initSearch()
									{
										if (document.forms.pmSearchForm.search.value.indexOf("%u") != -1)
											document.forms.pmSearchForm.search.value = unescape(document.forms.pmSearchForm.search.value);
									}
									createEventListener(window);
									window.addEventListener("load", initSearch, false);
								// ]]></script>
							</td><td style="padding-right: 2ex;">
								<select name="searchtype">
									<option value="1"', empty($context['search_params']['searchtype']) ? ' selected="selected"' : '', '>', $txt['pm_search_match_all'], '</option>
									<option value="2"', !empty($context['search_params']['searchtype']) ? ' selected="selected"' : '', '>', $txt['pm_search_match_any'], '</option>
								</select>
							</td><td>
								<input type="text" name="userspec" value="', empty($context['search_params']['userspec']) ? '*' : $context['search_params']['userspec'], '" size="40" />
							</td>
						</tr><tr>
							<td style="padding-top: 2ex;" colspan="2"><b>', $txt['pm_search_options'], ':</b></td>
							<td style="padding-top: 2ex;"><b>', $txt['pm_search_post_age'], ': </b></td>
						</tr><tr>
							<td colspan="2">
								<label for="show_complete"><input type="checkbox" name="show_complete" id="show_complete" value="1"', !empty($context['search_params']['show_complete']) ? ' checked="checked"' : '', ' class="check" /> ', $txt['pm_search_show_complete'], '</label><br />
								<label for="subject_only"><input type="checkbox" name="subject_only" id="subject_only" value="1"', !empty($context['search_params']['subject_only']) ? ' checked="checked"' : '', ' class="check" /> ', $txt['pm_search_subject_only'], '</label>
							</td>
							<td>
								', $txt['pm_search_between'], ' <input type="text" name="minage" value="', empty($context['search_params']['minage']) ? '0' : $context['search_params']['minage'], '" size="5" maxlength="5" />&nbsp;', $txt['pm_search_between_and'], '&nbsp;<input type="text" name="maxage" value="', empty($context['search_params']['maxage']) ? '9999' : $context['search_params']['maxage'], '" size="5" maxlength="5" /> ', $txt['pm_search_between_days'], '.
							</td>
						</tr><tr>
							<td style="padding-top: 2ex;" colspan="2"><b>', $txt['pm_search_order'], ':</b></td>
							<td></td>
						</tr><tr>
							<td colspan="2">
								<select name="sort">
		<!--- <option value="relevance|desc">', $txt['pm_search_orderby_relevant_first'], '</option> --->
									<option value="id_pm|desc">', $txt['pm_search_orderby_recent_first'], '</option>
									<option value="id_pm|asc">', $txt['pm_search_orderby_old_first'], '</option>
								</select>
							</td>
							<td></td>
						</tr>';

		// Do we have some labels setup? If so offer to search by them!
		if ($context['currently_using_labels'])
		{
			echo '
						<tr>
							<td colspan="4">
					<a href="javascript:void(0);" onclick="expandCollapseLabels(); return false;"><img src="', $settings['images_url'], '/expand.gif" id="expandLabelsIcon" alt="" /></a> <a href="javascript:void(0);" onclick="expandCollapseLabels(); return false;"><b>', $txt['pm_search_choose_label'], '</b></a><br />

					<table id="searchLabelsExpand" width="90%" border="0" cellpadding="1" cellspacing="0" align="center" ', $context['check_all'] ? 'style="display: none;"' : '', '>';

			$alternate = true;
			foreach ($context['search_labels'] as $label)
			{
				if ($alternate)
					echo '
						<tr>';
				echo '
							<td width="50%">
								<label for="searchlabel_', $label['id'], '"><input type="checkbox" id="searchlabel_', $label['id'], '" name="searchlabel[', $label['id'], ']" value="', $label['id'], '" ', $label['checked'] ? 'checked="checked"' : '', ' class="check" />
								', $label['name'], '</label>
							</td>';
				if (!$alternate)
					echo '
						</tr>';

				$alternate = !$alternate;
			}

			// If we haven't ended cleanly fix it...
			if ($alternate % 2 == 0)
				echo '
						<td width="50%"></td>
					</tr>';

			echo '
					</table>

					<br />
					<input type="checkbox" name="all" id="check_all" value="" ', $context['check_all'] ? 'checked="checked"' : '', ' onclick="invertAll(this, this.form, \'searchlabel\');" class="check" /><i> <label for="check_all">', $txt['check_all'], '</label></i><br />
							</td>
						</tr>';
		}

		echo '
					</table>
					<br />

					<div style="padding: 2px;"><input type="submit" name="submit" value="', $txt['pm_search_go'], '" /></div>';
	}

	echo '
				</td>
			</tr>
		</table>
	</form>';
}

function template_search_results()
{
	global $context, $settings, $options, $scripturl, $modSettings, $txt;

	// This splits broadly into two types of template... complete results first.
	if (!empty($context['search_params']['show_complete']))
	{
		echo '
		<table border="0" width="98%" align="center" cellpadding="3" cellspacing="1" class="bordercolor">
			<tr class="titlebg">
				<td colspan="3">', $txt['pm_search_results'], '</td>
			</tr>
			<tr class="catbg" height="30">
				<td colspan="3"><b>', $txt['pages'], ':</b> ', $context['page_index'], '</td>
			</tr>
		</table><br />';
	}
	else
	{
		echo '
		<table border="0" width="98%" align="center" cellpadding="3" cellspacing="1" class="bordercolor">
			<tr class="titlebg">
				<td colspan="3">', $txt['pm_search_results'], '</td>
			</tr>
			<tr class="catbg">
				<td colspan="3"><b>', $txt['pages'], ':</b> ', $context['page_index'], '</td>
			</tr>
			<tr class="titlebg">
				<td width="30%">', $txt['date'], '</td>
				<td width="50%">', $txt['subject'], '</td>
				<td width="20%">', $txt['from'], '</td>
			</tr>';
	}

	$alternate = true;
	// Print each message out...
	foreach ($context['personal_messages'] as $message)
	{
		// We showing it all?
		if (!empty($context['search_params']['show_complete']))
		{
			// !!! This still needs to be made pretty.
			echo '
		<table width="98%" align="center" cellpadding="3" cellspacing="1" border="0" class="bordercolor">
			<tr class="titlebg">
				<td align="left">
					<div style="float: left;">
					', $message['counter'], '&nbsp;&nbsp;<a href="', $message['href'], '">', $message['subject'], '</a>
					</div>
					<div style="float: right;">
						', $txt['search_on'], ': ', $message['time'], '
					</div>
				</td>
			</tr>
			<tr class="catbg">
				<td>', $txt['from'], ': ', $message['member']['link'], ', ', $txt['to'], ': ';

			// Show the recipients.
			// !!! This doesn't deal with the sent item searching quite right for bcc.
			if (!empty($message['recipients']['to']))
				echo implode(', ', $message['recipients']['to']);
			// Otherwise, we're just going to say "some people"...
			elseif ($context['folder'] != 'sent')
				echo '(', $txt['pm_undisclosed_recipients'], ')';

			echo '
				</td>
			</tr>
			<tr class="windowbg2" valign="top">
				<td>', $message['body'], '</td>
			</tr>
			<tr class="windowbg">
				<td align="right" class="middletext">';

			if ($context['can_send_pm'])
			{
				$quote_button = create_button('quote.gif', 'reply_quote', 'reply_quote', 'align="middle"');
				$reply_button = create_button('im_reply.gif', 'reply', 'reply', 'align="middle"');
				// You can only reply if they are not a guest...
				if (!$message['member']['is_guest'])
					echo '
							<a href="', $scripturl, '?action=pm;sa=send;f=', $context['folder'], $context['current_label_id'] != -1 ? ';l=' . $context['current_label_id'] : '', ';pmsg=', $message['id'], ';quote;u=', $context['folder'] == 'sent' ? '' : $message['member']['id'], '">', $quote_button , '</a>', $context['menu_separator'], '
							<a href="', $scripturl, '?action=pm;sa=send;f=', $context['folder'], $context['current_label_id'] != -1 ? ';l=' . $context['current_label_id'] : '', ';pmsg=', $message['id'], ';u=', $message['member']['id'], '">', $reply_button , '</a> ', $context['menu_separator'];
				// This is for "forwarding" - even if the member is gone.
				else
					echo '
							<a href="', $scripturl, '?action=pm;sa=send;f=', $context['folder'], $context['current_label_id'] != -1 ? ';l=' . $context['current_label_id'] : '', ';pmsg=', $message['id'], ';quote">', $quote_button , '</a>', $context['menu_separator'];
			}

			echo '
				</td>
			</tr>
		</table><br />';
		}
		// Otherwise just a simple list!
		else
		{
			// !!! No context at all of the search?
			echo '
			<tr class="', $alternate ? 'windowbg' : 'windowbg2', '" valign="top">
				<td>', $message['time'], '</td>
				<td>', $message['link'], '</td>
				<td>', $message['member']['link'], '</td>
			</tr>';
		}

		$alternate = !$alternate;
	}

	// Finish off the page...
	if (!empty($context['search_params']['show_complete']))
	{
		// No results?
		if (empty($context['personal_messages']))
			echo '
		<table width="98%" align="center" cellpadding="3" cellspacing="0" border="0" class="tborder" style="border-width: 0 1px 1px 1px;">
			<tr class="windowbg">
				<td>', $txt['pm_search_none_found'], '</td>
			</tr>
		</table><br />';

		echo '
		<table width="98%" align="center" cellpadding="3" cellspacing="0" border="0" class="tborder" style="border-width: 0 1px 1px 1px;">
			<tr class="catbg" height="30">
				<td colspan="3"><b>', $txt['pages'], ':</b> ', $context['page_index'], '</td>
			</tr>
		</table>';
	}
	else
	{
		if (empty($context['personal_messages']))
			echo '
			<tr class="windowbg2">
				<td colspan="3">', $txt['pm_search_none_found'], '</td>
			</tr>';

		echo '
			<tr class="catbg">
				<td colspan="3"><b>', $txt['pages'], ':</b> ', $context['page_index'], '</td>
			</tr>
		</table>';
	}
}

function template_send()
{
	global $context, $settings, $options, $scripturl, $modSettings, $txt;

	if ($context['show_spellchecking'])
		echo '
		<script language="JavaScript" type="text/javascript" src="', $settings['default_theme_url'], '/scripts/spellcheck.js"></script>';

	if ($context['visual_verification'])
		echo '
		<script language="JavaScript" type="text/javascript" src="', $settings['default_theme_url'], '/scripts/captcha.js"></script>';

	// Show which messages were sent successfully and which failed.
	if (!empty($context['send_log']))
	{
		echo '
		<br />
		<table border="0" width="80%" cellspacing="1" cellpadding="3" class="bordercolor" align="center">
			<tr class="titlebg">
				<td>', $txt['pm_send_report'], '</td>
			</tr>
			<tr>
				<td class="windowbg">';
		foreach ($context['send_log']['sent'] as $log_entry)
			echo '<span style="color: green">', $log_entry, '</span><br />';
		foreach ($context['send_log']['failed'] as $log_entry)
			echo '<span style="color: red">', $log_entry, '</span><br />';
		echo '
				</td>
			</tr>
		</table><br />';
	}

	// Show the preview of the personal message.
	if (isset($context['preview_message']))
	echo '
		<br />
		<table border="0" width="80%" cellspacing="1" cellpadding="3" class="bordercolor" align="center">
			<tr class="titlebg">
				<td>', $context['preview_subject'], '</td>
			</tr>
			<tr>
				<td class="windowbg">
					', $context['preview_message'], '
				</td>
			</tr>
		</table><br />';

	// Main message editing box.
	echo '
		<table border="0" width="80%" align="center" cellpadding="3" cellspacing="1" class="bordercolor">
			<tr class="titlebg">
				<td><img src="', $settings['images_url'], '/icons/im_newmsg.gif" alt="', $txt['new_message'], '" title="', $txt['new_message'], '" />&nbsp;', $txt['new_message'], '</td>
			</tr><tr>
				<td class="windowbg">
					<form action="', $scripturl, '?action=pm;sa=send2" method="post" accept-charset="', $context['character_set'], '" name="postmodify" id="postmodify" onsubmit="submitonce(this);saveEntities();">
						<table border="0" cellpadding="3" width="100%">';

	// If there were errors for sending the PM, show them.
	if (!empty($context['post_error']['messages']))
	{
		echo '
							<tr>
								<td></td>
								<td align="left">
									<b>', $txt['error_while_submitting'], '</b>
									<div style="color: red; margin: 1ex 0 2ex 3ex;">
										', implode('<br />', $context['post_error']['messages']), '
									</div>
								</td>
							</tr>';
	}

	// To and bcc. Include a button to search for members.
	echo '
							<tr>
								<td align="right"><b', (isset($context['post_error']['no_to']) || isset($context['post_error']['bad_to']) ? ' style="color: red;"' : ''), '>', $txt['pm_to'], ':</b></td>
								<td class="smalltext">
									<input type="text" name="to" id="to" value="', $context['to'], '" tabindex="', $context['tabindex']++, '" size="40" />&nbsp;
									<a href="', $scripturl, '?action=findmember;input=to;quote=1;sesc=', $context['session_id'], '" onclick="return reqWin(this.href, 350, 400);"><img src="', $settings['images_url'], '/icons/assist.gif" alt="', $txt['find_members'], '" /></a> <a href="', $scripturl, '?action=findmember;input=to;quote=1;sesc=', $context['session_id'], '" onclick="return reqWin(this.href, 350, 400);">', $txt['find_members'], '</a>
								</td>
							</tr><tr>
								<td align="right"><b', (isset($context['post_error']['bad_bcc']) ? ' style="color: red;"' : ''), '>', $txt['pm_bcc'], ':</b></td>
								<td class="smalltext">
									<input type="text" name="bcc" id="bcc" value="', $context['bcc'], '" tabindex="', $context['tabindex']++, '" size="40" />&nbsp;
									<a href="', $scripturl, '?action=findmember;input=bcc;quote=1;sesc=', $context['session_id'], '" onclick="return reqWin(this.href, 350, 400);"><img src="', $settings['images_url'], '/icons/assist.gif" alt="', $txt['find_members'], '" /></a> ', $txt['pm_multiple'], '
								</td>
							</tr>';
	// Subject of personal message.
	echo '
							<tr>
								<td align="right"><b', (isset($context['post_error']['no_subject']) ? ' style="color: red;"' : ''), '>', $txt['subject'], ':</b></td>
								<td><input type="text" name="subject" value="', $context['subject'], '" tabindex="', $context['tabindex']++, '" size="40" maxlength="50" /></td>
							</tr>';

	// Require an image to be typed to save spamming?
	if ($context['visual_verification'])
	{
		echo '
							<tr>
								<td align="right" valign="top">
									<b>', $txt['pm_visual_verification_label'], ':</b>
								</td>
								<td>';
		if ($context['use_graphic_library'])
			echo '
									<img src="', $context['verification_image_href'], '" id="verification_image" alt="', $txt['pm_visual_verification_desc'], '" /><br />';
		else
			echo '
									<img src="', $context['verification_image_href'], ';letter=1" id="verification_image_1" alt="', $txt['pm_visual_verification_desc'], '" />
									<img src="', $context['verification_image_href'], ';letter=2" id="verification_image_2" alt="', $txt['pm_visual_verification_desc'], '" />
									<img src="', $context['verification_image_href'], ';letter=3" id="verification_image_3" alt="', $txt['pm_visual_verification_desc'], '" />
									<img src="', $context['verification_image_href'], ';letter=4" id="verification_image_4" alt="', $txt['pm_visual_verification_desc'], '" />
									<img src="', $context['verification_image_href'], ';letter=5" id="verification_image_5" alt="', $txt['pm_visual_verification_desc'], '" /><br />';
		echo '
									<span class="smalltext">
										<a href="', $context['verification_image_href'], ';sound" id="visual_verification_sound">', $txt['visual_verification_sound'], '</a> / <a href="#" id="visual_verification_refresh">', $txt['visual_verification_request_new'], '</a><br />
									</span>
									<input type="text" name="visual_verification_code" size="30" tabindex="', $context['tabindex']++, '" />
									<div class="smalltext">', $txt['pm_visual_verification_desc'], '</div>
								</td>
							</tr>';
	}

	// Show BBC buttons, smileys and textbox.
	$context['post_box_width'] = '90%';
	theme_postbox($context['message']);

	// Send, Preview, spellcheck buttons.
	echo '
							<tr>
								<td align="right" colspan="2">
									<input type="submit" value="', $txt['send_message'], '" tabindex="', $context['tabindex']++, '" onclick="return submitThisOnce(this);" accesskey="s" />
									<input type="submit" name="preview" value="', $txt['preview'], '" tabindex="', $context['tabindex']++, '" onclick="return submitThisOnce(this);" accesskey="p" />';
	if ($context['show_spellchecking'])
		echo '
									<input type="button" value="', $txt['spell_check'], '" tabindex="', $context['tabindex']++, '" onclick="spellCheck(\'postmodify\', \'message\');" />';
	echo '
								</td>
							</tr>
							<tr>
								<td></td>
								<td align="left">
									<label for="outbox"><input type="checkbox" name="outbox" id="outbox" value="1" tabindex="', $context['tabindex']++, '"', $context['copy_to_outbox'] ? ' checked="checked"' : '', ' class="check" /> ', $txt['pm_save_outbox'], '</label>
								</td>
							</tr>
						</table>
						<input type="hidden" name="sc" value="', $context['session_id'], '" />
						<input type="hidden" name="seqnum" value="', $context['form_sequence_number'], '" />
						<input type="hidden" name="replied_to" value="', !empty($context['quoted_message']['id']) ? $context['quoted_message']['id'] : 0, '" />
						<input type="hidden" name="pm_head" value="', !empty($context['quoted_message']['pm_head']) ? $context['quoted_message']['pm_head'] : 0, '" />
						<input type="hidden" name="f" value="', isset($context['folder']) ? $context['folder'] : '', '" />
						<input type="hidden" name="l" value="', isset($context['current_label_id']) ? $context['current_label_id'] : -1, '" />
					</form>
				</td>
			</tr>
		</table>';

	// Some hidden information is needed in order to make the spell checking work.
	if ($context['show_spellchecking'])
		echo '
		<form name="spell_form" id="spell_form" method="post" accept-charset="', $context['character_set'], '" target="spellWindow" action="', $scripturl, '?action=spellcheck"><input type="hidden" name="spellstring" value="" /></form>';

	// Show the message you're replying to.
	if ($context['reply'])
		echo '
		<br />
		<br />
		<table width="100%" border="0" cellspacing="1" cellpadding="4" class="bordercolor">
			<tr>
				<td colspan="2" class="windowbg"><b>', $txt['subject'], ': ', $context['quoted_message']['subject'], '</b></td>
			</tr>
			<tr>
				<td class="windowbg2">
					<table width="100%" border="0" cellspacing="0" cellpadding="0">
						<tr>
							<td class="windowbg2">', $txt['from'], ': ', $context['quoted_message']['member']['name'], '</td>
							<td class="windowbg2" align="right">', $txt['on'], ': ', $context['quoted_message']['time'], '</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td colspan="2" class="windowbg">', $context['quoted_message']['body'], '</td>
			</tr>
		</table>';

	echo '
		<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
			function autocompleter(element)
			{
				if (typeof(element) != "object")
					element = document.getElementById(element);

				this.element = element;
				this.key = null;
				this.request = null;
				this.source = null;
				this.lastSearch = "";
				this.oldValue = "";
				this.cache = [];

				this.change = function (ev, force)
				{
					if (window.event)
						this.key = window.event.keyCode + 0;
					else
						this.key = ev.keyCode + 0;
					if (this.key == 27)
						return true;
					if (this.key == 34 || this.key == 8 || this.key == 13 || (this.key >= 37 && this.key <= 40))
						force = false;

					if (isEmptyText(this.element))
						return true;

					if (this.request != null && typeof(this.request) == "object")
						this.request.abort();

					var element = this.element, search = this.element.value.replace(/^("[^"]+",[ ]*)+/, "").replace(/^([^,]+,[ ]*)+/, "");
					this.oldValue = this.element.value.substr(0, this.element.value.length - search.length);
					if (search.substr(0, 1) == \'"\')
						search = search.substr(1);

					if (search == "" || search.substr(search.length - 1) == \'"\')
						return true;

					if (this.lastSearch == search)
					{
						if (force)
							this.select(this.cache[0]);

						return true;
					}
					else if (search.substr(0, this.lastSearch.length) == this.lastSearch && this.cache.length != 100)
					{
						// Instead of hitting the server again, just narrow down the results...
						var newcache = [], j = 0;
						for (var k = 0; k < this.cache.length; k++)
						{
							if (this.cache[k].substr(0, search.length) == search)
								newcache[j++] = this.cache[k];
						}

						if (newcache.length != 0)
						{
							this.lastSearch = search;
							this.cache = newcache;

							if (force)
								this.select(newcache[0]);

							return true;
						}
					}

					this.request = new XMLHttpRequest();
					this.request.onreadystatechange = function ()
					{
						element.autocompleter.handler(force);
					}

					this.request.open("GET", this.source + escape(textToEntities(search).replace(/&#(\d+);/g, "%#$1%")).replace(/%26/g, "%25%23038%25") + ";" + (new Date().getTime()), true);
					this.request.send(null);

					return true;
				}
				this.keyup = function (ev)
				{
					this.change(ev, true);

					return true;
				}
				this.keydown = function ()
				{
					if (this.request != null && typeof(this.request) == "object")
						this.request.abort();
				}
				this.handler = function (force)
				{
					if (this.request.readyState != 4)
						return true;

					var response = this.request.responseText.split("\n");
					this.lastSearch = this.element.value;
					this.cache = response;

					if (response.length < 2)
						return true;

					if (force)
						this.select(response[0]);

					return true;
				}
				this.select = function (value)
				{
					if (value == "")
						return;

					var i = this.element.value.length + (this.element.value.substr(this.oldValue.length, 1) == \'"\' ? 0 : 1);
					this.element.value = this.oldValue + \'"\' + value + \'"\';

					if (typeof(this.element.createTextRange) != "undefined")
					{
						var d = this.element.createTextRange();
						d.moveStart("character", i);
						d.select();
					}
					else if (this.element.setSelectionRange)
					{
						this.element.focus();
						this.element.setSelectionRange(i, this.element.value.length);
					}
				}

				this.element.autocompleter = this;
				this.element.setAttribute("autocomplete", "off");

				this.element.onchange = function (ev)
				{
					this.autocompleter.change(ev);
				}
				this.element.onkeyup = function (ev)
				{
					this.autocompleter.keyup(ev);
				}
				this.element.onkeydown = function (ev)
				{
					this.autocompleter.keydown(ev);
				}
			}

			if (window.XMLHttpRequest)
			{
				var toComplete = new autocompleter("to"), bccComplete = new autocompleter("bcc");
				toComplete.source = "', $scripturl, '?action=requestmembers;sesc=', $context['session_id'], ';search=";
				bccComplete.source = "', $scripturl, '?action=requestmembers;sesc=', $context['session_id'], ';search=";
			}

			function saveEntities()
			{
				var textFields = ["subject", "message"];
				for (i in textFields)
					if (document.forms.postmodify.elements[textFields[i]])
						document.forms.postmodify[textFields[i]].value = document.forms.postmodify[textFields[i]].value.replace(/&#/g, "&#38;#");
			}';

	if ($context['visual_verification'])
		echo '
		captchaHandle = new smfCaptcha("', $context['verification_image_href'], '", ', $context['use_graphic_library'] ? 1 : 0, ');';

	echo '
		// ]]></script>';
}

// This template asks the user whether they wish to empty out their folder/messages.
function template_ask_delete()
{
	global $context, $settings, $options, $scripturl, $modSettings, $txt;

	echo '
		<table border="0" width="80%" cellpadding="4" cellspacing="1" class="bordercolor" align="center">
			<tr class="titlebg">
				<td>', ($context['delete_all'] ? $txt['delete_message'] : $txt['delete_all']), '</td>
			</tr>
			<tr>
				<td class="windowbg">
					', $txt['delete_all_confirm'], '<br />
					<br />
					<b><a href="', $scripturl, '?action=pm;sa=removeall2;f=', $context['folder'], ';', $context['current_label_id'] != -1 ? ';l=' . $context['current_label_id'] : '', ';sesc=', $context['session_id'], '">', $txt['yes'], '</a> - <a href="javascript:history.go(-1);">', $txt['no'], '</a></b>
				</td>
			</tr>
		</table>';
}

// This template asks the user what messages they want to prune.
function template_prune()
{
	global $context, $settings, $options, $scripturl, $txt;

	echo '
	<form action="', $scripturl, '?action=pm;sa=prune" method="post" accept-charset="', $context['character_set'], '" onsubmit="return confirm(\'', $txt['pm_prune_warning'], '\');">
		<table width="60%" cellpadding="4" cellspacing="0" border="0" align="center" class="tborder">
			<tr class="catbg">
				<td>', $txt['pm_prune'], '</td>
			</tr>
			<tr class="windowbg">
				<td>', $txt['pm_prune_desc1'], ' <input type="text" name="age" size="3" value="14" /> ', $txt['pm_prune_desc2'], '</td>
			</tr>
			<tr class="windowbg">
				<td align="right"><input type="submit" value="', $txt['delete'], '" /></td>
			</tr>
		</table>
		<input type="hidden" name="sc" value="', $context['session_id'], '" />
	</form>';
}

// Here we allow the user to setup labels, remove labels and change rules for labels (i.e, do quite a bit)
function template_labels()
{
	global $context, $settings, $options, $scripturl, $txt;

	echo '
	<form action="', $scripturl, '?action=pm;sa=manlabels" method="post" accept-charset="', $context['character_set'], '">
		<table width="60%" cellpadding="4" cellspacing="0" border="0" align="center" class="tborder">
			<tr class="titlebg">
				<td colspan="2">', $txt['pm_manage_labels'], '</td>
			</tr>
			<tr class="windowbg2">
				<td colspan="2" style="padding: 1ex;"><span class="smalltext">', $txt['pm_labels_desc'], '</span></td>
			</tr>
			<tr class="catbg3">
				<td colspan="2">
					<div style="float: right; width: 4%; text-align: center;"><input type="checkbox" class="check" onclick="invertAll(this, this.form);" /></div>
					', $txt['pm_label_name'], '
				</td>
			</tr>';
	if (count($context['labels']) < 2)
		echo '
			<tr class="windowbg2">
				<td colspan="2" align="center">', $txt['pm_labels_no_exist'], '</td>
			</tr>';
	else
	{
		$alternate = true;
		foreach ($context['labels'] as $label)
		{
			if ($label['id'] != -1)
			{
				echo '
				<tr class="', $alternate ? 'windowbg2' : 'windowbg', '">
					<td>
						<input type="text" name="label_name[', $label['id'], ']" value="', $label['name'], '" size="30" maxlength="30" />
					</td>
					<td width="4%" align="center"><input type="checkbox" class="check" name="delete_label[', $label['id'], ']" /></td>
				</tr>';
				$alternate = !$alternate;
			}
		}

		echo '
			<tr class="catbg3">
				<td align="right" colspan="2">
					<input type="submit" name="save" value="', $txt['save'], '" style="font-weight: normal;" />
					<input type="submit" name="delete" value="', $txt['quickmod_delete_selected'], '" style="font-weight: normal;" onclick="return confirm(\'', $txt['pm_labels_delete'], '\');" />
				</td>
			</tr>';
	}
	echo '
		</table>
		<input type="hidden" name="sc" value="', $context['session_id'], '" />
	</form>
	<form action="', $scripturl, '?action=pm;sa=manlabels" method="post" accept-charset="', $context['character_set'], '" style="margin-top: 1ex;">
		<table width="60%" cellpadding="4" cellspacing="0" border="0" align="center" class="tborder">
			<tr class="titlebg">
				<td colspan="2" align="left">
					', $txt['pm_label_add_new'], '
				</td>
			</tr>
			<tr class="windowbg2">
				<td align="right" width="40%">
					<b>', $txt['pm_label_name'], ':</b>
				</td>
				<td align="left">
					<input type="text" name="label" value="" size="30" maxlength="20" />
				</td>
			</tr>
			<tr class="catbg3">
				<td colspan="2" align="right">
					<input type="submit" name="add" value="', $txt['pm_label_add_new'], '" style="font-weight: normal;" />
				</td>
			</tr>
		</table>
		<input type="hidden" name="sc" value="', $context['session_id'], '" />
	</form>';
}

// Template for options related to personal messages.
function template_message_settings()
{
	global $context, $settings, $options, $scripturl, $modSettings, $txt;

	// The main containing header.
	echo '
		<form action="', $scripturl, '?action=pm;sa=settings;save" method="post" accept-charset="', $context['character_set'], '" name="creator" id="creator">
			<table border="0" width="85%" cellspacing="0" cellpadding="4" align="center" class="tborder">
				<tr class="titlebg">
					<td>
						', $txt['pm_settings'], '
					</td>
				</tr><tr class="windowbg">
					<td class="smalltext" style="padding: 2ex;">
						', $txt['pm_settings_desc'], '
					</td>
				</tr><tr>
					<td class="windowbg2" style="padding-bottom: 2ex;">
						<table border="0" width="100%" cellpadding="3">';

	// A text box for the user to input usernames of everyone they want to ignore personal messages from.
	echo '
							<tr>
								<td valign="top">
									<b>', $txt['ignorelist'], ':</b>
									<div class="smalltext">
										', $txt['username_line'], '<br />
										<br />
										<a href="', $scripturl, '?action=findmember;input=pm_ignore_list;delim=\\\\n;sesc=', $context['session_id'], '" onclick="return reqWin(this.href, 350, 400);"><img src="', $settings['images_url'], '/icons/assist.gif" alt="', $txt['find_members'], '" align="middle" /> ', $txt['find_members'], '</a>
									</div>
								</td>
								<td>
									<textarea name="pm_ignore_list" id="pm_ignore_list" rows="10" cols="50">', $context['ignore_list'], '</textarea>
								</td>
							</tr>
							<tr>
								<td colspan="2">
									<hr />
								</td>
							</tr>';

	// Extra options available to the user for personal messages.
	echo '
							<tr>
								<td colspan="2">
									<label for="pm_display_mode">', $txt['pm_display_mode'], ':</label>
									<select name="pm_display_mode" id="pm_display_mode">
										<option value="0"', $context['display_mode'] == 0 ? ' selected="selected"' : '', '>', $txt['pm_display_mode_all'], '</option>
										<option value="1"', $context['display_mode'] == 1 ? ' selected="selected"' : '', '>', $txt['pm_display_mode_one'], '</option>
										<option value="2"', $context['display_mode'] == 2 ? ' selected="selected"' : '', '>', $txt['pm_display_mode_linked'], '</option>
									</select><br />
									<label for="pm_email_notify">', $txt['email_notify'], '</label>
									<select name="pm_email_notify" id="pm_email_notify">
										<option value="0"', empty($context['send_email']) ? ' selected="selected"' : '', '>', $txt['email_notify_never'], '</option>
										<option value="1"', !empty($context['send_email']) && ($context['send_email'] == 1 || (empty($modSettings['enable_buddylist']) && $context['send_email'] > 1)) ? ' selected="selected"' : '', '>', $txt['email_notify_always'], '</option>';

	if (!empty($modSettings['enable_buddylist']))
		echo '
										<option value="2"', !empty($context['send_email']) && $context['send_email'] > 1 ? ' selected="selected"' : '', '>', $txt['email_notify_buddies'], '</option>';

	echo '
									</select><br />
									<input type="hidden" name="default_options[copy_to_outbox]" value="0" />
									<label for="copy_to_outbox"><input type="checkbox" name="default_options[copy_to_outbox]" id="copy_to_outbox" value="1"', !empty($options['copy_to_outbox']) ? ' checked="checked"' : '', ' class="check" /> ', $txt['copy_to_outbox'], '</label><br />
									<input type="hidden" name="default_options[popup_messages]" value="0" />
									<label for="popup_messages"><input type="checkbox" name="default_options[popup_messages]" id="popup_messages" value="1"', !empty($options['popup_messages']) ? ' checked="checked"' : '', ' class="check" /> ', $txt['popup_messages'], '</label><br />
									<input type="hidden" name="default_options[pm_remove_inbox_label]" value="0" />
									<label for="pm_remove_inbox_label"><input type="checkbox" name="default_options[pm_remove_inbox_label]" id="pm_remove_inbox_label" value="1"', !empty($options['pm_remove_inbox_label']) ? ' checked="checked"' : '', ' class="check" /> ', $txt['pm_remove_inbox_label'], '</label><br />
								</td>
							</tr>';

	echo '
						</table>
					</td>
				</tr><tr class="catbg">
					<td align="right">
						<input type="submit" name="save" value="', $txt['pm_settings_save'], '" />
					</td>
				</tr>
			</table>
		</form>';
}

// Template for reporting a personal message.
function template_report_message()
{
	global $context, $settings, $options, $txt, $scripturl;

	echo '
	<form action="', $scripturl, '?action=pm;sa=report;l=', $context['current_label_id'], '" method="post" accept-charset="', $context['character_set'], '">
		<input type="hidden" name="pmsg" value="', $context['pm_id'], '" />
		<table border="0" width="80%" cellspacing="0" class="tborder" align="center" cellpadding="4">
			<tr class="titlebg">
				<td>', $txt['pm_report_title'], '</td>
			</tr>
			<tr class="windowbg2">
				<td align="left">
					<span class="smalltext">', $txt['pm_report_desc'], '</span>
				</td>
			</tr>';

	// If there is more than one admin on the forum, allow the user to choose the one they want to direct to.
	// !!! Why?
	if ($context['admin_count'] > 1)
	{
		echo '
			<tr class="windowbg">
				<td align="left">
					<b>', $txt['pm_report_admins'], ':</b>
					<select name="ID_ADMIN">
						<option value="0">', $txt['pm_report_all_admins'], '</option>';
		foreach ($context['admins'] as $id => $name)
			echo '
						<option value="', $id, '">', $name, '</option>';
		echo '
					</select>
				</td>
			</tr>';
	}

	echo '
			<tr class="windowbg">
				<td align="left">
					<b>', $txt['pm_report_reason'], ':</b>
				</td>
			</tr>
			<tr class="windowbg">
				<td align="center">
					<textarea name="reason" rows="4" cols="70" style="width: 80%;"></textarea>
				</td>
			</tr>
			<tr class="windowbg">
				<td align="center">
					<input type="submit" name="report" value="', $txt['pm_report_message'], '" />
				</td>
			</tr>
		</table>
		<input type="hidden" name="sc" value="', $context['session_id'], '" />
	</form>';
}

// Little template just to say "Yep, it's been submitted"
function template_report_message_complete()
{
	global $context, $settings, $options, $txt, $scripturl;

	echo '
		<table border="0" width="80%" cellspacing="0" class="tborder" align="center" cellpadding="4">
			<tr class="titlebg">
				<td>', $txt['pm_report_title'], '</td>
			</tr>
			<tr class="windowbg">
				<td align="left">
					', $txt['pm_report_done'], '
				</td>
			</tr>
			<tr class="windowbg">
				<td align="center">
					<br /><a href="', $scripturl, '?action=pm;l=', $context['current_label_id'], '">', $txt['pm_report_return'], '</a>
				</td>
			</tr>
		</table>';
}

// Manage rules.
function template_rules()
{
	global $context, $settings, $options, $txt, $scripturl;

	echo '
	<form action="', $scripturl, '?action=pm;sa=manrules" method="post" accept-charset="', $context['character_set'], '" name="manRules">
		<table cellpadding="4" cellspacing="0" border="0" align="center" width="80%" class="tborder">
			<tr class="titlebg">
				<td colspan="2">
					', $txt['pm_manage_rules'], '
				</td>
			</tr>
			<tr class="windowbg2">
				<td colspan="2">
					<span class="smalltext">', $txt['pm_manage_rules_desc'], '</span>
				</td>
			</tr>
			<tr class="catbg">
				<td>
					', $txt['pm_rule_title'], '
				</td>
				<td width="4%" align="center">';

	if (!empty($context['rules']))
		echo '
					<input type="checkbox" onclick="invertAll(this, this.form);" class="check" />';

	echo '
				</td>
			</tr>';

	if (empty($context['rules']))
		echo '
			<tr class="windowbg2">
				<td colspan="2" align="center">
					', $txt['pm_rules_none'], '
				</td>
			</tr>';

	$alternate = false;
	foreach ($context['rules'] as $rule)
	{
		echo '
			<tr class="', $alternate ? 'windowbg' : 'windowbg2', '">
				<td>
					<a href="', $scripturl, '?action=pm;sa=manrules;add;rid=', $rule['id'], '">', $rule['name'], '</a>
				</td>
				<td width="4%" align="center">
					<input type="checkbox" name="delrule[', $rule['id'], ']" type="check" />
				</td>
			</tr>';
		$alternate = !$alternate;
	}

	echo '
			<tr class="catbg">
				<td colspan="2">
					<div style="float: left;">
						[<a href="', $scripturl, '?action=pm;sa=manrules;add;rid=0">', $txt['pm_add_rule'], '</a>]';

	if (!empty($context['rules']))
		echo '
						[<a href="', $scripturl, '?action=pm;sa=manrules;apply" onclick="return confirm(\'', $txt['pm_js_apply_rules_confirm'], '\');">', $txt['pm_apply_rules'], '</a>]';

	echo '
					</div>';

	if (!empty($context['rules']))
		echo '
					<div style="float: right;">
						<input type="submit" name="delselected" value="', $txt['pm_delete_selected_rule'], '" onclick="return confirm(\'', $txt['pm_js_delete_rule_confirm'], '\');" />
					</div>';

	echo '
				</td>
			</tr>
		</table>
	</form>';

}

// Template for adding/editing a rule.
function template_add_rule()
{
	global $context, $settings, $options, $txt, $scripturl;

	echo '
	<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
			var criteriaNum = 0;
			var actionNum = 0;
			var groups = new Array()
			var labels = new Array()';

	foreach ($context['groups'] as $id => $title)
		echo '
			groups[', $id, '] = "', addslashes($title), '";';

	foreach ($context['labels'] as $label)
		if ($label['id'] != -1)
			echo '
			labels[', ($label['id'] + 1), '] = "', addslashes($label['name']), '";';

	echo '
			function addCriteriaOption()
			{
				if (criteriaNum == 0)
				{
					for (var i = 0; i < document.forms.addrule.elements.length; i++)
						if (document.forms.addrule.elements[i].id.substr(0, 8) == "ruletype")
							criteriaNum++;
				}
				criteriaNum++

				setOuterHTML(document.getElementById("criteriaAddHere"), \'<br /><select name="ruletype[\' + criteriaNum + \']" id="ruletype\' + criteriaNum + \'" onchange="updateRuleDef(\' + criteriaNum + \'); rebuildRuleDesc();"><option value="">', addslashes($txt['pm_rule_criteria_pick']), ':</option><option value="mid">', addslashes($txt['pm_rule_mid']), '</option><option value="gid">', addslashes($txt['pm_rule_gid']), '</option><option value="sub">', addslashes($txt['pm_rule_sub']), '</option><option value="msg">', addslashes($txt['pm_rule_msg']), '</option><option value="bud">', addslashes($txt['pm_rule_bud']), '</option></select>&nbsp;<span id="defdiv\' + criteriaNum + \'" style="display: none;"><input type="text" name="ruledef[\' + criteriaNum + \']" id="ruledef\' + criteriaNum + \'" onkeyup="rebuildRuleDesc();" value="" /></span><span id="defseldiv\' + criteriaNum + \'" style="display: none;"><select name="ruledefgroup[\' + criteriaNum + \']" id="ruledefgroup\' + criteriaNum + \'" onchange="rebuildRuleDesc();"><option value="">', addslashes($txt['pm_rule_sel_group']), '</option>';

	foreach ($context['groups'] as $id => $group)
		echo '<option value="', $id, '">', strtr($group, array("'" => "\'")), '</option>';

	echo '</select></span><span id="criteriaAddHere"></span>\');
			}

			function addActionOption()
			{
				if (actionNum == 0)
				{
					for (var i = 0; i < document.forms.addrule.elements.length; i++)
						if (document.forms.addrule.elements[i].id.substr(0, 7) == "acttype")
							actionNum++;
				}
				actionNum++

				setOuterHTML(document.getElementById("actionAddHere"), \'<br /><select name="acttype[\' + actionNum + \']" id="acttype\' + actionNum + \'" onchange="updateActionDef(\' + actionNum + \'); rebuildRuleDesc();"><option value="">', addslashes($txt['pm_rule_sel_action']), ':</option><option value="lab">', addslashes($txt['pm_rule_label']), '</option><option value="del">', addslashes($txt['pm_rule_delete']), '</option></select>&nbsp;<span id="labdiv\' + actionNum + \'" style="display: none;"><select name="labdef[\' + actionNum + \']" id="labdef\' + actionNum + \'" onchange="rebuildRuleDesc();"><option value="">', addslashes($txt['pm_rule_sel_label']), '</option>';

	foreach ($context['labels'] as $label)
		if ($label['id'] != -1)
			echo '<option value="', ($label['id'] + 1), '">', addslashes($label['name']), '</option>';

	echo '</select></span><span id="actionAddHere"></span>\');
			}

			function updateRuleDef(optNum)
			{
				if (document.getElementById("ruletype" + optNum).value == "gid")
				{
					document.getElementById("defdiv" + optNum).style.display = "none";
					document.getElementById("defseldiv" + optNum).style.display = "";
				}
				else if (document.getElementById("ruletype" + optNum).value == "bud" || document.getElementById("ruletype" + optNum).value == "")
				{
					document.getElementById("defdiv" + optNum).style.display = "none";
					document.getElementById("defseldiv" + optNum).style.display = "none";
				}
				else
				{
					document.getElementById("defdiv" + optNum).style.display = "";
					document.getElementById("defseldiv" + optNum).style.display = "none";
				}
			}

			function updateActionDef(optNum)
			{
				if (document.getElementById("acttype" + optNum).value == "lab")
				{
					document.getElementById("labdiv" + optNum).style.display = "";
				}
				else
				{
					document.getElementById("labdiv" + optNum).style.display = "none";
				}
			}

			// Rebuild the rule description!
			function rebuildRuleDesc()
			{
				// Start with nothing.
				text = "";
				joinText = "";
				actionText = "";
				hadBuddy = false;
				foundCriteria = false;
				foundAction = false;

				for (var i = 0; i < document.forms.addrule.elements.length; i++)
				{
					if (document.forms.addrule.elements[i].id.substr(0, 8) == "ruletype")
					{
						if (foundCriteria)
							joinText = document.getElementById("logic").value == "and" ? " ', $txt['pm_readable_and'], ' " : " ', $txt['pm_readable_or'], ' ";
						else
							joinText = "";
						foundCriteria = true;

						curNum = document.forms.addrule.elements[i].id.match(/\d+/);
						curVal = document.forms.addrule.elements[i].value;
						if (curVal == "gid")
							curDef = document.getElementById("ruledefgroup" + curNum).value;
						else if (curVal != "bud")
							curDef = document.getElementById("ruledef" + curNum).value;

						curDef = smf_htmlspecialchars(curDef);
						// What type of test is this?
						if (curVal == "mid" && curDef)
							text += joinText + "', $txt['pm_readable_member'], '".replace("{MEMBER}", curDef);
						else if (curVal == "gid" && curDef && groups[curDef])
							text += joinText + "', $txt['pm_readable_group'], '".replace("{GROUP}", groups[curDef]);
						else if (curVal == "sub" && curDef)
							text += joinText + "', $txt['pm_readable_subject'], '".replace("{SUBJECT}", curDef);
						else if (curVal == "msg" && curDef)
							text += joinText + "', $txt['pm_readable_body'], '".replace("{BODY}", curDef);
						else if (curVal == "bud" && !hadBuddy)
						{
							text += joinText + "', $txt['pm_readable_buddy'], '";
							hadBuddy = true;
						}
					}
					if (document.forms.addrule.elements[i].id.substr(0, 7) == "acttype")
					{
						if (foundAction)
							joinText = " ', $txt['pm_readable_and'], ' ";
						else
							joinText = "";
						foundAction = true;

						curNum = document.forms.addrule.elements[i].id.match(/\d+/);
						curVal = document.forms.addrule.elements[i].value;
						if (curVal == "lab")
							curDef = document.getElementById("labdef" + curNum).value;

						curDef = smf_htmlspecialchars(curDef);
						// Now pick the actions.
						if (curVal == "lab" && curDef && labels[curDef])
							actionText += joinText + "', $txt['pm_readable_label'], '".replace("{LABEL}", labels[curDef]);
						else if (curVal == "del")
							actionText += joinText + "', $txt['pm_readable_delete'], '";
					}
				}

				// If still nothing make it default!
				if (text == "" || !foundCriteria)
					text = "', $txt['pm_rule_not_defined'], '";
				else
				{
					if (actionText != "")
						text += " ', $txt['pm_readable_then'], ' " + actionText;
					text = "', $txt['pm_readable_start'], '" + text + "', $txt['pm_readable_end'], '";
				}

				// Set the actual HTML!
				setInnerHTML(document.getElementById("ruletext"), text);
			}
	// ]]></script>';

	echo '
	<form action="', $scripturl, '?action=pm;sa=manrules;save;rid=', $context['rid'], '" method="post" accept-charset="', $context['character_set'], '" name="addrule" id="addrule">
		<table cellpadding="4" cellspacing="0" border="0" align="center" width="80%" class="tborder">
			<tr class="titlebg">
				<td colspan="2">
					', $context['rid'] == 0 ? $txt['pm_add_rule'] : $txt['pm_edit_rule'], '
				</td>
			</tr>
			<tr class="windowbg">
				<td>
					<b>', $txt['pm_rule_name'], ':</b>
					<div class="smalltext">', $txt['pm_rule_name_desc'], '</div>
				</td>
				<td width="50%">
					<input type="text" name="rule_name" value="', empty($context['rule']['name']) ? $txt['pm_rule_name_default'] : $context['rule']['name'], '" />
				</td>
			</tr>
		</table><br />
		<table cellpadding="4" cellspacing="0" border="0" align="center" width="80%" class="tborder">
			<tr class="titlebg">
				<td colspan="2">
					', $txt['pm_rule_description'], '
				</td>
			</tr>
			<tr class="windowbg">
				<td colspan="2">
					<div id="ruletext" class="smalltext">', $txt['pm_rule_js_disabed'], '</div>
				</td>
			</tr>
		</table><br />
		<table cellpadding="4" cellspacing="0" border="0" align="center" width="80%" class="tborder">
			<tr class="titlebg">
				<td colspan="2">
					', $txt['pm_rule_criteria'], '
				</td>
			</tr>
			<tr class="windowbg">
				<td colspan="2">';

	// Add a dummy criteria to allow expansion for none js users.
	$context['rule']['criteria'][] = array('t' => '', 'v' => '');

	// For each criteria print it out.
	$isFirst = true;
	foreach ($context['rule']['criteria'] as $k => $criteria)
	{
		if ($isFirst)
			$isFirst = false;
		elseif ($criteria['t'] == '')
			echo '<div id="removeonjs1">';
		else
			echo '<br />';

		echo '
					<select name="ruletype[', $k, ']" id="ruletype', $k, '" onchange="updateRuleDef(', $k, '); rebuildRuleDesc();">
						<option value="">', $txt['pm_rule_criteria_pick'], ':</option>
						<option value="mid" ', $criteria['t'] == 'mid' ? 'selected="selected"' : '', '>', $txt['pm_rule_mid'], '</option>
						<option value="gid" ', $criteria['t'] == 'gid' ? 'selected="selected"' : '', '>', $txt['pm_rule_gid'], '</option>
						<option value="sub" ', $criteria['t'] == 'sub' ? 'selected="selected"' : '', '>', $txt['pm_rule_sub'], '</option>
						<option value="msg" ', $criteria['t'] == 'msg' ? 'selected="selected"' : '', '>', $txt['pm_rule_msg'], '</option>
						<option value="bud" ', $criteria['t'] == 'bud' ? 'selected="selected"' : '', '>', $txt['pm_rule_bud'], '</option>
					</select>
					<span id="defdiv', $k, '" ', !in_array($criteria['t'], array('gid', 'bud')) ? '' : 'style="display: none;"', '>
						<input type="text" name="ruledef[', $k, ']" id="ruledef', $k, '" onkeyup="rebuildRuleDesc();" value="', in_array($criteria['t'], array('mid', 'sub', 'msg')) ? $criteria['v'] : '', '" />
					</span>
					<span id="defseldiv', $k, '" ', $criteria['t'] == 'gid' ? '' : 'style="display: none;"', '>
						<select name="ruledefgroup[', $k, ']" id="ruledefgroup', $k, '" onchange="rebuildRuleDesc();">	
							<option value="">', $txt['pm_rule_sel_group'], '</option>';

		foreach ($context['groups'] as $id => $group)
			echo '
							<option value="', $id, '" ', $criteria['t'] == 'gid' && $criteria['v'] == $id ? 'selected="selected"' : '', '>', $group, '</option>';
		echo '
						</select>
					</span>';

		// If this is the dummy we add a means to hide for non js users.
		if ($criteria['t'] == '')
			echo '</div>';
	}

	echo '
					<span id="criteriaAddHere"></span> <a href="javascript:addCriteriaOption(); void(0);" id="addonjs1" style="display: none;">(', $txt['pm_rule_criteria_add'], ')</a>
				</td>
			</tr>
			<tr class="windowbg">
				<td colspan="2">
					', $txt['pm_rule_logic'], ':
					<select name="rule_logic" id="logic" onchange="rebuildRuleDesc();">
						<option value="and" ', $context['rule']['logic'] == 'and' ? 'selected="selected"' : '', '>', $txt['pm_rule_logic_and'], '</option>
						<option value="or" ', $context['rule']['logic'] == 'or' ? 'selected="selected"' : '', '>', $txt['pm_rule_logic_or'], '</option>
					</select>
				</td>
			</tr>
		</table><br />
		<table cellpadding="4" cellspacing="0" border="0" align="center" width="80%" class="tborder">
			<tr class="titlebg">
				<td colspan="2">
					', $txt['pm_rule_actions'], '
				</td>
			</tr>
			<tr class="windowbg2">
				<td colspan="2">';

	// As with criteria - add a dummy action for "expansion".
	$context['rule']['actions'][] = array('t' => '', 'v' => '');

	// Print each action.
	$isFirst = true;
	foreach ($context['rule']['actions'] as $k => $action)
	{
		if ($isFirst)
			$isFirst = false;
		elseif ($action['t'] == '')
			echo '<div id="removeonjs2">';
		else
			echo '<br />';

		echo '
					<select name="acttype[', $k, ']" id="acttype', $k, '" onchange="updateActionDef(', $k, '); rebuildRuleDesc();">
						<option value="">', $txt['pm_rule_sel_action'] , ':</option>
						<option value="lab" ', $action['t'] == 'lab' ? 'selected="selected"' : '', '>', $txt['pm_rule_label'] , '</option>
						<option value="del" ', $action['t'] == 'del' ? 'selected="selected"' : '', '>', $txt['pm_rule_delete'] , '</option>
					</select>
					<span id="labdiv', $k, '">
						<select name="labdef[', $k, ']" id="labdef', $k, '" onchange="rebuildRuleDesc();">
							<option value="">', $txt['pm_rule_sel_label'], '</option>';
		foreach ($context['labels'] as $label)
			if ($label['id'] != -1)
				echo '
							<option value="', ($label['id'] + 1), '" ', $action['t'] == 'lab' && $action['v'] == $label['id'] ? 'selected="selected"' : '', '>', $label['name'], '</option>';

		echo '
						</select>
					</span>';

		if ($action['t'] == '')
			echo '
				</div>';
	}

	echo '
					<span id="actionAddHere"></span> <a href="javascript:addActionOption(); void(0);" id="addonjs2" style="display: none;">(', $txt['pm_rule_add_action'], ')</a>
				</td>
			</tr>
		</table>
		<table cellpadding="4" cellspacing="0" border="0" align="center" width="80%">
			<tr>
				<td align="right">
					<input type="submit" name="save" value="', $txt['pm_rule_save'], '" />
				</td>
			</tr>
		</table>
	</form>';

	// Now setup all the bits!
		echo '
	<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[';

	foreach ($context['rule']['criteria'] as $k => $c)
		echo '
			updateRuleDef(', $k, ');';

	foreach ($context['rule']['actions'] as $k => $c)
		echo '
			updateActionDef(', $k, ');';

	echo '
			rebuildRuleDesc();';

	// If this isn't a new rule and we have JS enabled remove the JS compatibility stuff.
	if ($context['rid'])
		echo '
			document.getElementById("removeonjs1").style.display = "none";
			document.getElementById("removeonjs2").style.display = "none";';

	echo '
			document.getElementById("addonjs1").style.display = "";
			document.getElementById("addonjs2").style.display = "";';

	echo '
		// ]]></script>';
}

?>