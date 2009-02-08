<?php
// Version: 2.0 RC1; Who

// The only template in the file.
function template_main()
{
	global $context, $settings, $options, $scripturl, $txt;

	// Display the table header and linktree.
	echo '
	<div class="tborder" id="whos_online">
		<form action="', $scripturl, '?action=who" method="post" id="whoFilter" accept-charset="', $context['character_set'], '">
			<h3 class="catbg"><span class="left"></span><span class="right"></span>', $txt['who_title'], '</h3>
			<h4 class="titlebg"><span class="left"></span><span class="right"></span>
				<span class="who"><a href="' . $scripturl . '?action=who;start=', $context['start'], ';show=', $context['show_by'], ';sort=user', $context['sort_direction'] != 'down' && $context['sort_by'] == 'user' ? '' : ';asc', '" rel="nofollow">', $txt['who_user'], ' ', $context['sort_by'] == 'user' ? '<img src="' . $settings['images_url'] . '/sort_' . $context['sort_direction'] . '.gif" alt="" />' : '', '</a></span>
				<span class="time"><a href="' . $scripturl . '?action=who;start=', $context['start'], ';show=', $context['show_by'], ';sort=time', $context['sort_direction'] == 'down' && $context['sort_by'] == 'time' ? ';asc' : '', '" rel="nofollow">', $txt['who_time'], ' ', $context['sort_by'] == 'time' ? '<img src="' . $settings['images_url'] . '/sort_' . $context['sort_direction'] . '.gif" alt="" />' : '', '</a></span>
				<span class="action">', $txt['who_action'], '</span>
			</h4>';

	// This is used to alternate the color of the background.
	$alternate = true;

	// For every member display their name, time and action (and more for admin).
	foreach ($context['members'] as $member)
	{
		// $alternate will either be true or false. If it's true, use "windowbg2" and otherwise use "windowbg".
		echo '
			<div class="clearfix">
				<ul class="windowbg', $alternate ? '2' : '', '">
					<li class="who">';

		// Guests don't have information like icq, msn, y!, and aim... and they can't be messaged.
		if (!$member['is_guest'])
			echo '
						<span>
							', $context['can_send_pm'] ? '<a href="' . $member['online']['href'] . '" title="' . $member['online']['label'] . '">' : '', $settings['use_image_buttons'] ? '<img src="' . $member['online']['image_href'] . '" alt="' . $member['online']['text'] . '" />' : $member['online']['text'], $context['can_send_pm'] ? '</a>' : '', '
							', $member['icq']['link'], ' ', $member['msn']['link'], ' ', $member['yim']['link'], ' ', $member['aim']['link'], '
						</span>';

		echo $member['is_hidden'] ? '<em>' : '', $member['is_guest'] ? $member['name'] : '<a href="' . $member['href'] . '" title="' . $txt['profile_of'] . ' ' . $member['name'] . '"' . (empty($member['color']) ? '' : ' style="color: ' . $member['color'] . '"') . '>' . $member['name'] . '</a>', $member['is_hidden'] ? '</em>' : '';

		if (!empty($member['ip']))
			echo '
						(<a href="' . $scripturl . '?action=', ($member['is_guest'] ? 'trackip' : 'profile;area=tracking;sa=ip;u=' . $member['id']), ';searchip=' . $member['ip'] . '">' . $member['ip'] . '</a>)';

		echo '
					</li>
					<li class="time">', $member['time'], '</li>
					<li class="action">', $member['action'], '</li>
				</ul>
			</div>';

		// Switch alternate to whatever it wasn't this time. (true -> false -> true -> false, etc.)
		$alternate = !$alternate;
	}

	// No members?
	if (empty($context['members']))
		echo '
			<div id="whos_none" class="windowbg2">
				', $txt['who_no_online_' . ($context['show_by'] == 'guests' || $context['show_by'] == 'spiders' ? $context['show_by'] : 'members')], '
			</div>';

	echo '
			<h4 id="pages_below" class="titlebg"><span class="left"></span><span class="right"></span>
				<span class="selectbox">', $txt['who_show1'], '
					<select name="show" onchange="document.forms.whoFilter.submit();">';

	foreach ($context['show_methods'] as $value => $label)
		echo '
						<option value="', $value, '" ', $value == $context['show_by'] ? ' selected="selected"' : '', '>', $label, '</option>';
	echo '
					</select>
					<noscript>
						<input type="submit" value="', $txt['go'], '" />
					</noscript>
				</span>
				', $txt['pages'], ': ', $context['page_index'], '
			</h4>
		</form>
	</div>';
}

function template_credits()
{
	global $context, $txt;

	// The most important part - the credits :P.
	echo '
	<div class="tborder windowbg2" id="credits">
		<h3 class="catbg"><span class="left"></span><span class="right"></span>', $txt['credits'], '</h3>';

	foreach ($context['credits'] as $section)
	{
		if (isset($section['pretext']))
			echo '
		<p>', $section['pretext'], '</p>';

		if (isset($section['title']))
			echo '
		<h4>', $section['title'], '</h4>';

		echo '
		<ul>';

		foreach ($section['groups'] as $group)
		{
			echo '
			<li>';

			if (isset($group['title']))
			echo '
				<strong>', $group['title'], '</strong>: ';

			// Try to make this read nicely.
			if (count($group['members']) <= 2)
				echo implode($txt['credits_and'], $group['members']);
			else
			{
				$last_peep = array_pop($group['members']);
				echo implode(', ', $group['members']), ', ', $txt['credits_and'], ' ', $last_peep;
			}

			echo '
			</li>';
		}
		echo '
		</ul>';

		if (isset($section['posttext']))
			echo '
		<p>', $section['posttext'], '</p>';
	}

	echo '
		<h3>', $txt['credits_copyright'], '</h3>
		<h4>', $txt['credits_forum'], '</h4>', '
		<p>', $context['copyrights']['smf'];

	if (!empty($context['copyright_removal_validate']))
		echo '<br />
			', $context['copyright_removal_validate'];

	echo '
		</p>';

	if (!empty($context['copyrights']['mods']))
	{
		echo '
		<h4>', $txt['credits_modifications'], '</h4>
		<p>', implode("<br />\n", $context['copyrights']['mods']), '</p>';
	}

	echo '
	</div>';
}
?>