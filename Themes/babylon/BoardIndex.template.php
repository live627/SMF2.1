<?php
// Version: 2.0 Alpha; BoardIndex

function template_main()
{
	global $context, $settings, $options, $txt, $scripturl, $modSettings;

	// Show some statistics next to the link tree if SP1 info is off.
	echo '
<table width="100%" cellpadding="3" cellspacing="0">
	<tr>
		<td valign="bottom">', theme_linktree(), '</td>
		<td align="right">';
	if (!$settings['show_stats_index'])
		echo '
			', $txt['members'], ': ', $context['common_stats']['total_members'], ' &nbsp;&#8226;&nbsp; ', $txt['posts_made'], ': ', $context['common_stats']['total_posts'], ' &nbsp;&#8226;&nbsp; ', $txt['topics'], ': ', $context['common_stats']['total_topics'], '
			', ($settings['show_latest_member'] ? '<br />' . $txt['welcome_member'] . ' <b>' . $context['common_stats']['latest_member']['link'] . '</b>' . $txt[581] : '');
	echo '
		</td>
	</tr>
</table>';

	// Show the news fader?  (assuming there are things to show...)
	if ($settings['show_newsfader'] && !empty($context['fader_news_lines']))
	{
		echo '
<div class="tborder" style="margin-bottom: 2ex;">
	<table border="0" width="100%" cellspacing="1" cellpadding="4">
		<tr class="titlebg" align="center">
			<td>', $txt['news'], '</td>
		</tr>
		<tr>
			<td valign="middle" align="center" style="height: 60px;">';

		// Prepare all the javascript settings.
		echo '
				<div id="smfFadeScroller" style="width: 90%; padding: 2px;"><b>', $context['news_lines'][0], '</b></div>
				<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
					// The fading delay (in ms.)
					var smfFadeDelay = ', empty($settings['newsfader_time']) ? 5000 : $settings['newsfader_time'], ';
					// Fade from... what text color?  To which background color?
					var smfFadeFrom = {"r": 0, "g": 0, "b": 0}, smfFadeTo = {"r": 255, "g": 255, "b": 255};
					// Surround each item with... anything special?
					var smfFadeBefore = "<b>", smfFadeAfter = "</b>";

					var foreColor, backEl, backColor;

					if (typeof(document.getElementById(\'smfFadeScroller\').currentStyle) != "undefined")
					{
						foreColor = document.getElementById(\'smfFadeScroller\').currentStyle.color.match(/#([\da-f][\da-f])([\da-f][\da-f])([\da-f][\da-f])/);
						smfFadeFrom = {"r": parseInt(foreColor[1]), "g": parseInt(foreColor[2]), "b": parseInt(foreColor[3])};

						backEl = document.getElementById(\'smfFadeScroller\');
						while (backEl.currentStyle.backgroundColor == "transparent" && typeof(backEl.parentNode) != "undefined")
							backEl = backEl.parentNode;

						backColor = backEl.currentStyle.backgroundColor.match(/#([\da-f][\da-f])([\da-f][\da-f])([\da-f][\da-f])/);
						smfFadeTo = {"r": eval("0x" + backColor[1]), "g": eval("0x" + backColor[2]), "b": eval("0x" + backColor[3])};
					}
					else if (typeof(window.opera) == "undefined" && typeof(document.defaultView) != "undefined")
					{
						foreColor = document.defaultView.getComputedStyle(document.getElementById(\'smfFadeScroller\'), null).color.match(/rgb\((\d+), (\d+), (\d+)\)/);
						smfFadeFrom = {"r": parseInt(foreColor[1]), "g": parseInt(foreColor[2]), "b": parseInt(foreColor[3])};

						backEl = document.getElementById(\'smfFadeScroller\');
						while (document.defaultView.getComputedStyle(backEl, null).backgroundColor == "transparent" && typeof(backEl.parentNode) != "undefined" && typeof(backEl.parentNode.tagName) != "undefined")
							backEl = backEl.parentNode;

						backColor = document.defaultView.getComputedStyle(backEl, null).backgroundColor.match(/rgb\((\d+), (\d+), (\d+)\)/);
						smfFadeTo = {"r": parseInt(backColor[1]), "g": parseInt(backColor[2]), "b": parseInt(backColor[3])};
					}

					// List all the lines of the news for display.
					var smfFadeContent = new Array(
						"', implode('",
						"', $context['fader_news_lines']), '"
					);
				// ]]></script>
				<script language="JavaScript" type="text/javascript" src="', $settings['default_theme_url'], '/fader.js"></script>
			</td>
		</tr>
	</table>
</div>';
	}

	/* Each category in categories is made up of:
		id, href, link, name, is_collapsed (is it collapsed?), can_collapse (is it okay if it is?),
		new (is it new?), collapse_href (href to collapse/expand), collapse_image (up/down iamge),
		and boards. (see below.) */
	foreach ($context['categories'] as $category)
	{
		echo '
<div class="tborder"><table border="0" width="100%" cellspacing="1" cellpadding="5">
	<tr>
		<td colspan="4" class="catbg', $category['new'] ? '2' : '', '" height="18">';

		// If this category even can collapse, show a link to collapse it.
		if ($category['can_collapse'])
			echo '
			<a href="', $category['collapse_href'], '">', $category['collapse_image'], '</a>';

		echo '
			', $category['link'], '
		</td>
	</tr>';

		// Assuming the category hasn't been collapsed...
		if (!$category['is_collapsed'])
		{
			/* Each board in each category's boards has:
				new (is it new?), id, name, description, moderators (see below), link_moderators (just a list.),
				children (see below.), link_children (easier to use.), children_new (are they new?),
				topics (# of), posts (# of), link, href, and last_post. (see below.) */
			foreach ($category['boards'] as $board)
			{
				echo '
	<tr class="windowbg2">
		<td class="windowbg" width="6%" align="center" valign="top"><a href="', $scripturl, '?action=unread;board=', $board['id'], '.0;children">';

				// If the board is new, show a strong indicator.
				if ($board['new'])
					echo '<img src="', $settings['images_url'], '/on.gif" alt="', $txt[333], '" title="', $txt[333], '" border="0" />';
				// This board doesn't have new posts, but its children do.
				elseif ($board['children_new'])
					echo '<img src="', $settings['images_url'], '/on2.gif" alt="', $txt[333], '" title="', $txt[333], '" border="0" />';
				// No new posts at all!  The agony!!
				else
					echo '<img src="', $settings['images_url'], '/off.gif" alt="', $txt[334], '" title="', $txt[334], '" border="0" />';

				echo '</a></td>
		<td>
			<b><a href="', $board['href'], '" name="b', $board['id'], '">', $board['name'], '</a></b>';

				// Has it outstanding posts for approval?
				if ($board['can_approve_posts'] && ($board['unapproved_posts'] | $board['unapproved_topics']))
					echo '
					<b style="color: red;" title="', sprintf($txt['unapproved_posts'], $board['unapproved_topics'], $board['unapproved_posts']), '">(!)</b>';
		echo '
			<br />
			', $board['description'];

				// Show the "Moderators: ".  Each has name, href, link, and id. (but we're gonna use link_moderators.)
				if (!empty($board['moderators']))
					echo '
			<div style="padding-top: 1px;" class="smalltext"><i>', count($board['moderators']) == 1 ? $txt[298] : $txt[299], ': ', implode(', ', $board['link_moderators']), '</i></div>';

				// Show the "Child Boards: ". (there's a link_children but we're going to bold the new ones...)
				if (!empty($board['children']))
				{
					// Sort the links into an array with new boards bold so it can be imploded.
					$children = array();
					/* Each child in each board's children has:
						id, name, description, new (is it new?), topics (#), posts (#), href, link, and last_post. */
					foreach ($board['children'] as $child)
					{
						$child['link'] = '<a href="' . $child['href'] . '" title="' . ($child['new'] ? $txt[333] : $txt[334]) . ' (' . $txt[330] . ': ' . $child['topics'] . ', ' . $txt['posts'] . ': ' . $child['posts'] . ')">' . $child['name'] . '</a>';

						// Does the child have any posts awaiting approval?!
						if ($child['can_approve_posts'] && ($child['unapproved_posts'] | $child['unapproved_topics']))
							$child['link'] .= '<b style="color: red;" title="' . sprintf($txt['unapproved_posts'], $child['unapproved_topics'], $child['unapproved_posts']) . '">(!)</b>';

						$children[] = $child['new'] ? '<b>' . $child['link'] . '</b>' : $child['link'];
					}

					echo '
			<div style="padding-top: 1px;" class="smalltext"><i>', $txt['parent_boards'], ': ', implode(', ', $children), '</i></div>';
				}

				// Show some basic information about the number of posts, etc.
				echo '
		</td>
		<td class="windowbg" valign="middle" align="center" style="width: 12ex;"><span class="smalltext">
			', $board['posts'], ' ', $txt['posts'], ' ', $txt['smf88'], '<br />
			', $board['topics'], ' ', $txt[330], '
		</span></td>
		<td class="smalltext" valign="middle" width="22%">';

				/* The board's and children's 'last_post's have:
					time, timestamp (a number that represents the time.), id (of the post), topic (topic id.),
					link, href, subject, start (where they should go for the first unread post.),
					and member. (which has id, name, link, href, username in it.) */
				if (!empty($board['last_post']['id']))
					echo '
			', $txt['last_post'], ' ', $txt['on'], ' ', $board['last_post']['time'], '<br />
			', $txt['smf88'], ' ', $board['last_post']['link'], ' ', $txt[525], ' ', $board['last_post']['member']['link'];

				echo '
		</td>
	</tr>';
			}
		}

		echo '
</table></div>
<br />';
	}

	if ($context['user']['is_logged'])
	{
		echo '
<div class="tborder" style="padding: 3px;"><table border="0" width="100%" cellspacing="0" cellpadding="5">
	<tr class="titlebg">
		<td align="', !$context['right_to_left'] ? 'left' : 'right', '" class="smalltext">
			<img src="' . $settings['images_url'] . '/new_some.gif" alt="" border="0" align="middle" /> ', $txt[333], '
			<img src="' . $settings['images_url'] . '/new_none.gif" alt="" border="0" align="middle" style="margin-left: 4ex;" /> ', $txt[334], '
		</td>
		<td align="', !$context['right_to_left'] ? 'right' : 'left', '" class="smalltext">';
		// Show the mark all as read button?
		if ($settings['show_mark_read'])
			echo '
			<a href="', $scripturl, '?action=markasread;sa=all;sesc=' . $context['session_id'] . '">', ($settings['use_image_buttons'] ? '<img src="' . $settings['images_url'] . '/' . $context['user']['language'] . '/markread.gif" alt="' . $txt[452] . '" border="0" />' : $txt[452]), '</a>';
		echo '
		</td>
	</tr>
</table></div>';
	}

	// Here's where the "Info Center" starts...
	echo '
<br />
<div class="tborder"><table border="0" width="100%" cellspacing="1" cellpadding="4">
	<tr class="titlebg">
		<td align="center" colspan="2">', $txt[685], '</td>
	</tr>';

	// This is the "Recent Posts" bar.
	if (!empty($settings['number_recent_posts']))
	{
		echo '
	<tr>
		<td class="catbg" colspan="2">', $txt['recent_posts'], '</td>
	</tr>
	<tr>
		<td class="windowbg" width="20" valign="middle" align="center">
			<a href="', $scripturl, '?action=recent">
				<img src="', $settings['images_url'], '/post/xx.gif" alt="', $txt['recent_posts'], '" border="0" /></a>
		</td>
		<td class="windowbg2">';

		// Only show one post.
		if ($settings['number_recent_posts'] == 1)
		{
			// latest_post has link, href, time, subject, short_subject (shortened with...), and topic. (its id.)
			echo '
			<b><a href="', $scripturl, '?action=recent">', $txt['recent_posts'], '</a></b>
			<div class="smalltext">
				', $txt['recent_view'], ' &quot;', $context['latest_post']['link'], '&quot; ', $txt['recent_updated'], ' (', $context['latest_post']['time'], ')<br />
			</div>';
		}
		// Show lots of posts.
		elseif (!empty($context['latest_posts']))
		{
			echo '
			<table width="100%" border="0">';
			/* Each post in latest_posts has:
				board (with an id, name, and link.), topic (the topic's id.), poster (with id, name, and link.),
				subject, short_subject (shortened with...), time, link, and href. */
			foreach ($context['latest_posts'] as $post)
				echo '
				<tr>
					<td align="right" valign="top" nowrap="nowrap">[', $post['board']['link'], ']</td>
					<td valign="top">', $post['link'], ' ', $txt[525], ' ', $post['poster']['link'], '</td>
					<td align="right" valign="top" nowrap="nowrap">', $post['time'], '</td>
				</tr>';
			echo '
			</table>';
		}
		echo '
		</td>
	</tr>';
	}

	// Show information about events, birthdays, and holidays on the calendar.
	if ($context['show_calendar'])
	{
		echo '
	<tr>
		<td class="catbg" colspan="2">', $context['calendar_only_today'] ? $txt['calendar47b'] : $txt['calendar47'], '</td>
	</tr><tr>
		<td class="windowbg" width="20" valign="middle" align="center">
			<a href="', $scripturl, '?action=calendar">
				<img src="', $settings['images_url'], '/icons/calendar.gif" alt="', $txt['calendar24'], '" border="0" /></a>
		</td>
		<td class="windowbg2" width="100%">
			<span class="smalltext">';

		// Holidays like "Christmas", "Chanukah", and "We Love [Unknown] Day" :P.
		if (!empty($context['calendar_holidays']))
			echo '
				<span class="holiday">', $txt['calendar5'], ' ', implode(', ', $context['calendar_holidays']), '</span><br />';

		// People's birthdays.  Like mine.  And yours, I guess.  Kidding.
		if (!empty($context['calendar_birthdays']))
		{
			echo '
				<span class="birthday">', $context['calendar_only_today'] ? $txt['calendar3'] : $txt['calendar3b'], '</span> ';
			/* Each member in calendar_birthdays has:
				id, name (person), age (if they have one set?), is_last. (last in list?), and is_today (birthday is today?) */
			foreach ($context['calendar_birthdays'] as $member)
				echo '
				<a href="', $scripturl, '?action=profile;u=', $member['id'], '">', $member['is_today'] ? '<b>' : '', $member['name'], $member['is_today'] ? '</b>' : '', isset($member['age']) ? ' (' . $member['age'] . ')' : '', '</a>', $member['is_last'] ? '<br />' : ', ';
		}
		// Events like community get-togethers.
		if (!empty($context['calendar_events']))
		{
			echo '
				<span class="event">', $context['calendar_only_today'] ? $txt['calendar4'] : $txt['calendar4b'], '</span> ';
			/* Each event in calendar_events should have:
				title, href, is_last, can_edit (are they allowed?), modify_href, and is_today. */
			foreach ($context['calendar_events'] as $event)
				echo '
				', $event['can_edit'] ? '<a href="' . $event['modify_href'] . '" style="color: #FF0000;">*</a> ' : '', $event['href'] == '' ? '' : '<a href="' . $event['href'] . '">', $event['is_today'] ? '<b>' . $event['title'] . '</b>' : $event['title'], $event['href'] == '' ? '' : '</a>', $event['is_last'] ? '<br />' : ', ';

			// Show a little help text to help them along ;).
			if ($context['calendar_can_edit'])
				echo '
				(<a href="', $scripturl, '?action=helpadmin;help=calendar_how_edit" onclick="return reqWin(this.href);">', $txt['calendar_how_edit'], '</a>)';
		}
		echo '
			</span>
		</td>
	</tr>';
	}

	// Show a member bar.  Not heavily ornate, but functional at least.
	if ($settings['show_member_bar'])
	{
		echo '
	<tr>
		<td class="catbg" colspan="2">', $txt[331], '</td>
	</tr>
	<tr>
		<td class="windowbg" width="20" valign="middle" align="center">
			', $context['show_member_list'] ? '<a href="' . $scripturl . '?action=mlist">' : '', '<img src="', $settings['images_url'], '/icons/members.gif" alt="', $txt[332], '" border="0" />', $context['show_member_list'] ? '</a>' : '', '
		</td>
		<td class="windowbg2" width="100%">
			<b>', $context['show_member_list'] ? '<a href="' . $scripturl . '?action=mlist">' . $txt[332] . '</a>' : $txt[332], '</b>
			<div class="smalltext">', $txt['memberlist_searchable'], '</div>
		</td>
	</tr>';
	}

	// Show stats?
	if ($settings['show_stats_index'])
	{
		echo '
	<tr>
		<td class="catbg" colspan="2">', $txt[645], '</td>
	</tr>
	<tr>
		<td class="windowbg" width="20" valign="middle" align="center">
			<a href="', $scripturl, '?action=stats">
				<img src="', $settings['images_url'], '/icons/info.gif" alt="', $txt[645], '" border="0" /></a>
		</td>
		<td class="windowbg2" width="100%">
			<table border="0" width="90%"><tr>
				<td class="smalltext">
					<div style="float: ', !$context['right_to_left'] ? 'left' : 'right', '; width: 50%;">', $txt[490], ': <b>', $context['common_stats']['total_topics'], '</b></div>', $txt[489], ': <b>', $context['common_stats']['total_posts'], '</b><br />', !empty($context['latest_post']) ? '
					' . $txt[659] . ': &quot;' . $context['latest_post']['link'] . '&quot;  (' . $context['latest_post']['time'] . ')<br />' : '', '
					<a href="', $scripturl, '?action=recent">', $txt['recent_view'], '</a>', $context['show_stats'] ? '<br />
					<a href="' . $scripturl . '?action=stats">' . $txt['smf223'] . '</a>' : '', '
				</td>
				<td width="32%" class="smalltext" valign="top">
					', $txt[488], ': <b><a href="', $scripturl, '?action=mlist">', $context['common_stats']['total_members'], '</a></b><br />
					', $txt[656], ': <b>', $context['common_stats']['latest_member']['link'], '</b><br />';
		// If they are logged in, show their unread message count, etc..
		if ($context['user']['is_logged'])
			echo '
					', $txt['smf199'], ': <b><a href="', $scripturl, '?action=pm">', $context['user']['messages'], '</a></b> ', $txt['newmessages3'], ': <b><a href="', $scripturl, '?action=pm">', $context['user']['unread_messages'], '</a></b>';
		echo '
				</td>
			</tr></table>
		</td>
	</tr>';
	}

	// "Users online" - in order of activity.
	echo '
	<tr>
		<td class="catbg" colspan="2">', $txt['online_users'], '</td>
	</tr><tr>
		<td class="windowbg" width="20" valign="middle" align="center">
			', $context['show_who'] ? '<a href="' . $scripturl . '?action=who">' : '', '<img src="', $settings['images_url'], '/icons/online.gif" alt="', $txt['online_users'], '" border="0" />', $context['show_who'] ? '</a>' : '', '
		</td>
		<td class="windowbg2" width="100%">';

	if ($context['show_who'])
		echo '
			<a href="', $scripturl, '?action=who">';

	echo $context['num_guests'], ' ', $context['num_guests'] == 1 ? $txt['guest'] : $txt['guests'], ', ' . $context['num_users_online'], ' ', $context['num_users_online'] == 1 ? $txt['user'] : $txt['users'];

	// Handle hidden users and buddies.
	if (!empty($context['num_users_hidden']) || ($context['show_buddies'] && !empty($context['show_buddies'])))
	{
		echo ' (';

		// Show the number of buddies online?
		if ($context['show_buddies'])
			echo $context['num_buddies'], ' ', $context['num_buddies'] == 1 ? $txt['buddy'] : $txt['buddies'];

		// How about hidden users?
		if (!empty($context['num_users_hidden']))
			echo $context['show_buddies'] ? ', ' : '', $context['num_users_hidden'] . ' ' . $txt['hidden'];

		echo ')';
	}

	if ($context['show_who'])
		echo '</a>';

	echo '
			<div class="smalltext">';

	// Assuming there ARE users online... each user in users_online has an id, username, name, group, href, and link.
	if (!empty($context['users_online']))
	{
		echo '
				', $txt['users_active'], ':<br />', implode(', ', $context['list_users_online']);

		// Showing membergroups?
		if (!empty($settings['show_group_key']) && !empty($context['membergroups']))
			echo '
				<br />[' . implode(']&nbsp;&nbsp;[', $context['membergroups']) . ']';
	}

	echo '
			</div>
		</td>
	</tr>';

	// If they are logged in, but stat information is off... show a personal message bar.
	if ($context['user']['is_logged'] && !$settings['show_stats_index'])
	{
		echo '
	<tr>
		<td class="catbg" colspan="2">', $txt['personal_message'], '</td>
	</tr><tr>
		<td class="windowbg" width="20" valign="middle" align="center">
			', $context['allow_pm'] ? '<a href="' . $scripturl . '?action=pm">' : '', '<img src="', $settings['images_url'], '/message_sm.gif" alt="', $txt['personal_message'], '" border="0" />', $context['allow_pm'] ? '</a>' : '', '
		</td>
		<td class="windowbg2" valign="top">
			<b><a href="', $scripturl, '?action=pm">', $txt['personal_message'], '</a></b>
			<div class="smalltext">
				', $txt[660], ' ', $context['user']['messages'], ' ', $context['user']['messages'] == 1 ? $txt[471] : $txt['msg_alert_messages'], '.... ', $txt[661], ' <a href="', $scripturl, '?action=pm">', $txt[662], '</a> ', $txt[663], '
			</div>
		</td>
	</tr>';
	}

	// Show the login bar. (it's only true if they are logged out anyway.)
	if ($context['show_login_bar'])
	{
		echo '
	<tr>
		<td class="catbg" colspan="2">', $txt['login'], ' <a href="', $scripturl, '?action=reminder" class="smalltext">(' . $txt[315] . ')</a></td>
	</tr>
	<tr>
		<td class="windowbg" width="20" align="center">
			<a href="', $scripturl, '?action=login">
				<img src="', $settings['images_url'], '/icons/login.gif" alt="', $txt['login'], '" border="0" /></a>
		</td>
		<td class="windowbg2" valign="middle">
			<form action="', $scripturl, '?action=login2" method="post" accept-charset="', $context['character_set'], '" style="margin: 0;">
				<table border="0" cellpadding="2" cellspacing="0" width="100%"><tr>
					<td valign="middle" align="left">
						<label for="user"><b>', $txt['username'], ':</b><br /><input type="text" name="user" id="user" size="15" /></label>
					</td>
					<td valign="middle" align="left">
						<label for="passwrd"><b>', $txt['password'], ':</b><br /><input type="password" name="passwrd" id="passwrd" size="15" /></label>
					</td>
					<td valign="middle" align="left">
						<label for="cookielength"><b>', $txt[497], ':</b><br /><input type="text" name="cookielength" id="cookielength" size="4" maxlength="4" value="', $modSettings['cookieTime'], '" /></label>
					</td>
					<td valign="middle" align="left">
						<label for="cookieneverexp"><b>', $txt[508], ':</b><br /><input type="checkbox" name="cookieneverexp" id="cookieneverexp" checked="checked" class="check" /></label>
					</td>
					<td valign="middle" align="left">
						<input type="submit" value="', $txt['login'], '" />
					</td>
				</tr></table>
			</form>
		</td>
	</tr>';
	}

	echo '
</table></div>';
}

?>