<?php
// Version: 2.0 Alpha; ManageAttachments

// Template template wraps around the simple settings page to add javascript functionality.
function template_avatar_settings_above()
{
}

function template_avatar_settings_below()
{
	echo '
	<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
	function updateStatus()
	{
		document.getElementById("avatar_max_width_external").disabled = document.getElementById("avatar_download_external").checked;
		document.getElementById("avatar_max_height_external").disabled = document.getElementById("avatar_download_external").checked;
		document.getElementById("avatar_action_too_large").disabled = document.getElementById("avatar_download_external").checked;
		document.getElementById("custom_avatar_dir").disabled = document.getElementById("custom_avatar_enabled").value == 0;
		document.getElementById("custom_avatar_url").disabled = document.getElementById("custom_avatar_enabled").value == 0;

	}
	window.onload = updateStatus;
// ]]></script>
';
}

function template_browse()
{
	global $context, $settings, $options, $scripturl, $txt;

	echo '
	<table border="0" align="center" cellspacing="1" cellpadding="4" class="bordercolor" width="100%">
		<tr class="titlebg">
			<td colspan="5">', $txt['attachment_manager_browse_files'], '</td>
		</tr>';

	// shall we use the tabs?
	if (!empty($settings['use_tabs']))
	{
		echo '
	</table>';

		echo '
	<table cellpadding="0" cellspacing="0" border="0" style="margin-bottom: 1ex; margin-left: 10px;">
		<tr>
			<td class="maintab_first">&nbsp;</td>';

		echo $context['browse_type'] == 'attachments' ? '
			<td class="maintab_active_first">&nbsp;</td>' : '' , '
			<td class="maintab_' , $context['browse_type'] == 'attachments' ? 'active_' : '' , 'back"><a href="', $scripturl, '?action=admin;area=manageattachments;sa=browse">', $txt['attachment_manager_attachments'], '</a></td>' , $context['browse_type'] == 'attachments' ? '
			<td class="maintab_active_last">&nbsp;</td>' : '';

		echo $context['browse_type'] == 'avatars' ? '
			<td class="maintab_active_first">&nbsp;</td>' : '' , '
			<td class="maintab_' , $context['browse_type'] == 'avatars' ? 'active_' : '' , 'back"><a href="', $scripturl, '?action=admin;area=manageattachments;sa=browse;avatars">', $txt['attachment_manager_avatars'], '</a></td>' , $context['browse_type'] == 'avatars' ? '
			<td class="maintab_active_last">&nbsp;</td>' : '';

		echo $context['browse_type'] == 'thumbs' ? '
			<td class="maintab_active_first">&nbsp;</td>' : '' , '
			<td class="maintab_' , $context['browse_type'] == 'thumbs' ? 'active_' : '' , 'back"><a href="', $scripturl, '?action=admin;area=manageattachments;sa=browse;thumbs">', $txt['attachment_manager_thumbs'], '</a></td>' , $context['browse_type'] == 'thumbs' ? '
			<td class="maintab_active_last">&nbsp;</td>' : '';

		echo '
			<td class="maintab_last">&nbsp;</td>
		</tr>
	</table>';
	}
	// if not, use the old style
	else
	{	
		echo '
		<tr class="catbg">
			<td colspan="5">
				<a href="', $scripturl, '?action=admin;area=manageattachments;sa=browse;sort=', $context['sort_by'], $context['sort_direction'] == 'down' ? ';desc' : '', '">', $context['browse_type'] == 'attachments' ? '<img src="' . $settings['images_url'] . '/selected.gif" alt="&gt;" border="0" /> ' : '', $txt['attachment_manager_attachments'], '</a>&nbsp;|&nbsp;
				<a href="', $scripturl, '?action=admin;area=manageattachments;sa=browse;avatars;sort=', $context['sort_by'], $context['sort_direction'] == 'down' ? ';desc' : '', '">', $context['browse_type'] == 'avatars' ? '<img src="' . $settings['images_url'] . '/selected.gif" alt="&gt;" border="0" /> ' : '', $txt['attachment_manager_avatars'], '</a>&nbsp;|&nbsp;
				<a href="', $scripturl, '?action=admin;area=manageattachments;sa=browse;thumbs;sort=', $context['sort_by'], $context['sort_direction'] == 'down' ? ';desc' : '', '">', $context['browse_type'] == 'thumbs' ? '<img src="' . $settings['images_url'] . '/selected.gif" alt="&gt;" border="0" /> ' : '', $txt['attachment_manager_thumbs'], '</a>
			</td>
		</tr>
	</table>';
	}

	template_show_list('file_list');
/*
	echo '
<form action="', $scripturl, '?action=admin;area=manageattachments;sort=', $context['sort_by'], $context['sort_direction'] == 'down' ? ';desc' : '', ';sa=remove" method="post" accept-charset="', $context['character_set'], '" onsubmit="return confirm(\'', $txt['confirm_delete_attachments'], '\');">
	<table border="0" align="center" cellspacing="1" cellpadding="4" class="bordercolor" width="100%">
		<tr class="titlebg">';

			<td nowrap="nowrap"><a href="', $scripturl, '?action=admin;area=manageattachments;sa=browse;', $context['browse_type'], ';sort=name', $context['sort_by'] == 'name' && $context['sort_direction'] == 'up' ? ';desc' : '', '">', $txt['attachment_name'], $context['sort_by'] == 'name' ? ' <img src="' . $settings['images_url'] . '/sort_' . $context['sort_direction'] . '.gif" alt="" />' : '', '</a></td>
			<td nowrap="nowrap"><a href="', $scripturl, '?action=admin;area=manageattachments;sa=browse;', $context['browse_type'], ';sort=size', $context['sort_by'] == 'size' && $context['sort_direction'] == 'up' ? ';desc' : '', '">', $txt['attachment_file_size'], $context['sort_by'] == 'size' ? ' <img src="' . $settings['images_url'] . '/sort_' . $context['sort_direction'] . '.gif" alt="" />' : '', '</a></td>
			<td nowrap="nowrap"><a href="', $scripturl, '?action=admin;area=manageattachments;sa=browse;', $context['browse_type'], ';sort=member', $context['sort_by'] == 'member' && $context['sort_direction'] == 'up' ? ';desc' : '', '">', $context['browse_type'] == 'avatars' ? $txt['attachment_manager_member'] : $txt['posted_by'], $context['sort_by'] == 'member' ? ' <img src="' . $settings['images_url'] . '/sort_' . $context['sort_direction'] . '.gif" alt="" />' : '', '</a></td>
			<td nowrap="nowrap"><a href="', $scripturl, '?action=admin;area=manageattachments;sa=browse;', $context['browse_type'], ';sort=date', $context['sort_by'] == 'date' && $context['sort_direction'] == 'up' ? ';desc' : '', '">', $context['browse_type'] == 'avatars' ? $txt['attachment_manager_last_active'] : $txt['date'], $context['sort_by'] == 'date' ? ' <img src="' . $settings['images_url'] . '/sort_' . $context['sort_direction'] . '.gif" alt="" />' : '', '</a></td>
			<td nowrap="nowrap" align="center"><input type="checkbox" onclick="invertAll(this, this.form);" class="check" /></td>
		</tr>';
	$alternate = false;
	foreach ($context['posts'] as $post)
	{
		echo '
		<tr class="', $alternate ? 'windowbg' : 'windowbg2', '">
			<td>', $post['attachment']['link'], empty($post['attachment']['width']) || empty($post['attachment']['height']) ? '' : ' <span class="smalltext">' . $post['attachment']['width'] . 'x' . $post['attachment']['height'] . '</span>', '</td>
			<td align="right">', $post['attachment']['size'], $txt['kilobyte'], '</td>
			<td>', $post['poster']['link'], '</td>
			<td class="smalltext">', $post['time'], $context['browse_type'] != 'avatars' ? '<br />' . $txt['in'] . ' ' . $post['link'] : '', '</td>
			<td align="center"><input type="checkbox" name="remove[', $post['attachment']['id'], ']" class="check" /></td>
		</tr>';
		$alternate = !$alternate;
	}
	echo '
		<tr class="', $alternate ? 'windowbg' : 'windowbg2', '">
			<td align="right" colspan="5">
				<input type="submit" name="remove_submit" value="', $txt['delete'], '" />
				<input type="hidden" name="sc" value="', $context['session_id'], '" />
				<input type="hidden" name="type" value="', $context['browse_type'], '" />
				<input type="hidden" name="start" value="', $context['start'], '" />
			</td>
		</tr>
		<tr class="catbg">
			<td align="left" colspan="5" style="padding: 5px;"><b>', $txt['pages'], ':</b> ', $context['page_index'], '</td>
		</tr>
	</table>
</form>';
*/
}

function template_maintenance()
{
	global $context, $settings, $options, $scripturl, $txt;

	echo '
	<table width="100%" cellpadding="4" cellspacing="0" align="center" border="0" class="tborder">
		<tr>
			<td class="titlebg">', $txt['attachment_stats'], '</td>
		</tr><tr>
			<td class="windowbg2" width="100%" valign="top" style="padding-bottom: 2ex;">
				<table border="0" cellspacing="0" cellpadding="3">
					<tr>
						<td>', $txt['attachment_total'], ':</td><td>', $context['num_attachments'], '</td>
					</tr><tr>
						<td>', $txt['attachment_manager_total_avatars'], ':</td><td>', $context['num_avatars'], '</td>
					</tr><tr>
						<td>', $txt['attachmentdir_size'], ':</td><td>', $context['attachment_total_size'], ' ', $txt['kilobyte'], ' <a href="', $scripturl, '?action=admin;area=manageattachments;sa=repair;sesc=', $context['session_id'], '">[', $txt['attachment_manager_repair'], ']</a></td>
					</tr><tr>
						<td>', $txt['attachment_space'], ':</td><td>', isset($context['attachment_space']) ? $context['attachment_space'] . ' ' . $txt['kilobyte'] : $txt['attachmentdir_size_not_set'], '</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
	<br />
	<table width="100%" cellpadding="4" cellspacing="0" align="center" border="0" class="tborder">
		<tr>
			<td class="titlebg">', $txt['attachment_options'], '</td>
		</tr><tr>
			<td class="windowbg2" width="100%" valign="top">
				<form action="', $scripturl, '?action=admin;area=manageattachments" method="post" accept-charset="', $context['character_set'], '" onsubmit="return confirm(\'', $txt['confirm_delete_attachments'], '\');" style="margin: 0 0 2ex 0;">
					', $txt['message'], ': <input type="text" name="notice" value="', $txt['attachment_delete_admin'], '" size="40" /><br />
					', $txt['attachment_remove_old'], ' <input type="text" name="age" value="25" size="4" /> ', $txt['days_word'], ' <input type="submit" name="submit" value="', $txt['remove'], '" />
					<input type="hidden" name="type" value="attachments" />
					<input type="hidden" name="sc" value="', $context['session_id'], '" />
					<input type="hidden" name="sa" value="byAge" />
				</form>
				<form action="', $scripturl, '?action=admin;area=manageattachments" method="post" accept-charset="', $context['character_set'], '" onsubmit="return confirm(\'', $txt['confirm_delete_attachments'], '\');" style="margin: 0 0 2ex 0;">
					', $txt['message'], ': <input type="text" name="notice" value="', $txt['attachment_delete_admin'], '" size="40" /><br />
					', $txt['attachment_remove_size'], ' <input type="text" name="size" id="size" value="100" size="4" /> ', $txt['kilobyte'], ' <input type="submit" name="submit" value="', $txt['remove'], '" />
					<input type="hidden" name="type" value="attachments" />
					<input type="hidden" name="sc" value="', $context['session_id'], '" />
					<input type="hidden" name="sa" value="bySize" />
				</form>
				<form action="', $scripturl, '?action=admin;area=manageattachments" method="post" accept-charset="', $context['character_set'], '" onsubmit="return confirm(\'', $txt['confirm_delete_attachments'], '\');" style="margin: 0 0 2ex 0;">
					', $txt['attachment_manager_avatars_older'], ' <input type="text" name="age" value="45" size="4" /> ', $txt['days_word'], ' <input type="submit" name="submit" value="', $txt['remove'], '" />
					<input type="hidden" name="type" value="avatars" />
					<input type="hidden" name="sc" value="', $context['session_id'], '" />
					<input type="hidden" name="sa" value="byAge" />
				</form>
			</td>
		</tr>
	</table>';
}

function template_attachment_repair()
{
	global $context, $txt, $scripturl;

	// If we've completed just let them know!
	if ($context['completed'])
	{
		echo '
	<table width="100%" cellpadding="4" cellspacing="0" align="center" border="0" class="tborder">
		<tr>
			<td class="titlebg">', $txt['repair_attachments_complete'], '</td>
		</tr><tr>
			<td class="windowbg2" width="100%">
				', $txt['repair_attachments_complete_desc'], '
			</td>
		</tr>
	</table>';
	}
	// What about if no errors were even found?
	elseif (!$context['errors_found'])
	{
		echo '
	<table width="100%" cellpadding="4" cellspacing="0" align="center" border="0" class="tborder">
		<tr>
			<td class="titlebg">', $txt['repair_attachments_complete'], '</td>
		</tr><tr>
			<td class="windowbg2" width="100%">
				', $txt['repair_attachments_no_errors'], '
			</td>
		</tr>
	</table>';
	}
	// Otherwise, I'm sad to say, we have a problem!
	else
	{
		echo '
	<form action="', $scripturl, '?action=admin;area=manageattachments;sa=repair;fixErrors=1;step=0;substep=0;sesc=', $context['session_id'], '" method="post" accept-charset="', $context['character_set'], '">
	<table width="100%" cellpadding="4" cellspacing="0" align="center" border="0" class="tborder">
		<tr>
			<td class="titlebg">', $txt['repair_attachments'], '</td>
		</tr><tr>
			<td class="windowbg2">
				', $txt['repair_attachments_error_desc'], '
			</td>
		</tr>';

		// Loop through each error reporting the status
		foreach ($context['repair_errors'] as $error => $number)
		{
			if (!empty($number))
			echo '
		<tr class="windowbg2">
			<td>
				<input type="checkbox" name="to_fix[]" id="', $error, '" value="', $error, '" />
				<label for="', $error, '">', sprintf($txt['attach_repair_' . $error], $number), '</label>
			</td>
		</tr>';
		}

		echo '
		<tr>
			<td align="center" class="windowbg2">
				<input type="submit" value="', $txt['repair_attachments_continue'], '" />
				<input type="submit" name="cancel" value="', $txt['repair_attachments_cancel'], '" />
			</td>
		</tr>
	</table>
	</form>';
	}
}

?>