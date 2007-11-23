<?php
// Version: 2.0 Beta 1; Who

// The only template in the file.
function template_main()
{
	global $context, $settings, $options, $scripturl, $txt;

	// Display the table header and linktree.
	echo '
	<div style="padding: 3px;">', theme_linktree(), '</div>
	<table cellpadding="3" cellspacing="0" border="0" width="100%" class="tborder">
		<tr class="titlebg">
			<td width="30%"><a href="' . $scripturl . '?action=who;start=', $context['start'], ';sort=user', $context['sort_direction'] != 'down' && $context['sort_by'] == 'user' ? '' : ';asc', '">', $txt['who_user'], ' ', $context['sort_by'] == 'user' ? '<img src="' . $settings['images_url'] . '/sort_' . $context['sort_direction'] . '.gif" alt="" />' : '', '</a></td>
			<td style="width: 14ex;"><a href="' . $scripturl . '?action=who;start=', $context['start'], ';sort=time', $context['sort_direction'] == 'down' && $context['sort_by'] == 'time' ? ';asc' : '', '">', $txt['who_time'], ' ', $context['sort_by'] == 'time' ? '<img src="' . $settings['images_url'] . '/sort_' . $context['sort_direction'] . '.gif" alt="" />' : '', '</a></td>
			<td>', $txt['who_action'], '</td>
		</tr>';

	// This is used to alternate the color of the background.
	$alternate = true;

	// For every member display their name, time and action (and more for admin).
	foreach ($context['members'] as $member)
	{
		// $alternate will either be true or false. If it's true, use "windowbg2" and otherwise use "windowbg".
		echo '
		<tr class="windowbg', $alternate ? '2' : '', '">
			<td>';

		// Guests don't have information like icq, msn, y!, and aim... and they can't be messaged.
		if (!$member['is_guest'])
		{
			echo '
				<div style="float: right; width: 14ex;">
					', $context['can_send_pm'] ? '<a href="' . $member['online']['href'] . '" title="' . $member['online']['label'] . '">' : '', $settings['use_image_buttons'] ? '<img src="' . $member['online']['image_href'] . '" alt="' . $member['online']['text'] . '" align="middle" />' : $member['online']['text'], $context['can_send_pm'] ? '</a>' : '', '
					', $member['icq']['link'], ' ', $member['msn']['link'], ' ', $member['yim']['link'], ' ', $member['aim']['link'], '
				</div>';
		}

		echo '
				<span', $member['is_hidden'] ? ' style="font-style: italic;"' : '', '>', $member['is_guest'] ? $member['name'] : '<a href="' . $member['href'] . '" title="' . $txt['profile_of'] . ' ' . $member['name'] . '"' . (empty($member['color']) ? '' : ' style="color: ' . $member['color'] . '"') . '>' . $member['name'] . '</a>', '</span>';

		if (!empty($member['ip']))
			echo '
				(<a href="' . $scripturl . '?action=trackip;searchip=' . $member['ip'] . '" class="extern">' . $member['ip'] . '</a>)';

		echo '
			</td>
			<td nowrap="nowrap">', $member['time'], '</td>
			<td>', $member['action'], '</td>
		</tr>';

		// Switch alternate to whatever it wasn't this time. (true -> false -> true -> false, etc.)
		$alternate = !$alternate;
	}

	echo '
		<tr class="titlebg">
			<td colspan="3"><b>', $txt['pages'], ':</b> ', $context['page_index'], '</td>
		</tr>
	</table>
	<form action="', $scripturl, '?action=who" method="post" accept-charset="', $context['character_set'], '">
		', $txt['who_show1'], '
		<select name="show">';

	foreach ($context['show_methods'] as $value => $label)
		echo '
			<option value="', $value, '" ', $value == $context['show_by'] ? ' selected="selected"' : '', '>', $label, '</option>';
	echo '
		</select>
		', $txt['who_show2'], '
		<select name="sort">';

	foreach ($context['sort_methods'] as $value => $label)
		echo '
			<option value="', $value, '" ', $value == $context['sort_by'] ? ' selected="selected"' : '', '>', $label, '</option>';

	echo '
		</select>
		', $txt['who_show3'], '
		<select name="sort_dir">
			<option value="desc">', $txt['who_sort_desc'], '</option>
			<option value="asc" ', $context['sort_direction'] == 'up' ? 'selected="selected"' : '', '>', $txt['who_sort_asc'], '</option>
		</select>
		', $txt['who_show4'], '
		<input type="submit" />
	</form>';
}

?>