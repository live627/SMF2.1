<?php
// Version: 2.0 Beta 1; BoardIndex

function template_main()
{
	global $context, $settings, $options, $txt, $scripturl, $modSettings;

	// Show some statistics next to the link tree if stat info is off.
	echo '
	<table width="100%" cellpadding="0" cellspacing="0">
		<tr>
			<td valign="bottom">', theme_linktree(), '</td>
			<td align="right">';
	if (!$settings['show_stats_index'])
		echo '
				', $txt['members'], ': ', $context['common_stats']['total_members'], ' &nbsp;&#8226;&nbsp; ', $txt['posts_made'], ': ', $context['common_stats']['total_posts'], ' &nbsp;&#8226;&nbsp; ', $txt['topics'], ': ', $context['common_stats']['total_topics'], '
				', ($settings['show_latest_member'] ? '<br />' . $txt['welcome_member'] . ' <b>' . $context['common_stats']['latest_member']['link'] . '</b>' . $txt['newest_member'] : '');
	echo '
			</td>
		</tr>
	</table>';

	// Show the news fader?  (assuming there are things to show...)
	if ($settings['show_newsfader'] && !empty($context['fader_news_lines']))
	{
	echo '
	<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
		// Create the main header object.
		var smfNewsFadeToggle = new smfToggle("smfNewsFadeScroller", ', empty($options['collapse_news_fader']) ? 'false' : 'true', ');
		smfNewsFadeToggle.useCookie(', $context['user']['is_guest'] ? 1 : 0, ');
		smfNewsFadeToggle.setOptions("collapse_news_fader", "', $context['session_id'], '");
		smfNewsFadeToggle.addToggleImage("newsupshrink", "/collapse.gif", "/expand.gif");
		smfNewsFadeToggle.addTogglePanel("smfNewsFader");
	// ]]></script>
	<table border="0" width="100%" class="tborder" cellspacing="' , ($context['browser']['is_ie'] || $context['browser']['is_opera6']) ? '1' : '0' , '" cellpadding="4" style="margin-bottom: 2ex;">
		<tr>
			<td class="catbg"><a href="#" onclick="smfNewsFadeToggle.toggle(); return false;"><img id="newsupshrink" src="', $settings['images_url'], '/', empty($options['collapse_news_fader']) ? 'collapse.gif' : 'expand.gif', '" alt="*" title="', $txt['upshrink_description'], '" align="bottom" style="margin: 0 1ex;" /></a>&nbsp;', $txt['news'], '</td>
		</tr>
		<tr>
			<td valign="middle" align="center" height="60" id="smfNewsFader"', empty($options['collapse_news_fader']) ? '' : ' style="display: none;"', '>';

		// Prepare all the javascript settings.
		echo '
				<div id="smfFadeScroller" style="width: 90%; padding: 2px;"><b>', $context['news_lines'][0], '</b></div>
				<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
					// The fading delay (in ms.)
					var smfFadeDelay = ', empty($settings['newsfader_time']) ? 5000 : $settings['newsfader_time'], ';
					// Fade from... what text color? To which background color?
					var smfFadeFrom = {"r": 0, "g": 0, "b": 0}, smfFadeTo = {"r": 255, "g": 255, "b": 255};
					// Surround each item with... anything special?
					var smfFadeBefore = "<b>", smfFadeAfter = "</b>";

					var foreColor, foreEl, backEl, backColor;

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

						foreEl = document.getElementById(\'smfFadeScroller\');

						while (document.defaultView.getComputedStyle(foreEl, null).getPropertyCSSValue("color") == null && typeof(foreEl.parentNode) != "undefined" && typeof(foreEl.parentNode.tagName) != "undefined")
							foreEl = foreEl.parentNode;

						foreColor = document.defaultView.getComputedStyle(foreEl, null).getPropertyValue("color").match(/rgb\((\d+), (\d+), (\d+)\)/);
						smfFadeFrom = {"r": parseInt(foreColor[1]), "g": parseInt(foreColor[2]), "b": parseInt(foreColor[3])};

						backEl = document.getElementById(\'smfFadeScroller\');

						while (document.defaultView.getComputedStyle(backEl, null).getPropertyCSSValue("background-color") == null && typeof(backEl.parentNode) != "undefined" && typeof(backEl.parentNode.tagName) != "undefined")
							backEl = backEl.parentNode;

						backColor = document.defaultView.getComputedStyle(backEl, null).getPropertyValue("background-color");//.match(/rgb\((\d+), (\d+), (\d+)\)/);
						smfFadeTo = {"r": parseInt(backColor[1]), "g": parseInt(backColor[2]), "b": parseInt(backColor[3])};
					}

					// List all the lines of the news for display.
					var smfFadeContent = new Array(
						"', implode('",
						"', $context['fader_news_lines']), '"
					);
				// ]]></script>
				<script language="JavaScript" type="text/javascript" src="', $settings['default_theme_url'], '/scripts/fader.js"></script>
			</td>
		</tr>
	</table>';
	}

	/* Each category in categories is made up of:
	id, href, link, name, is_collapsed (is it collapsed?), can_collapse (is it okay if it is?),
	new (is it new?), collapse_href (href to collapse/expand), collapse_image (up/down image),
	and boards. (see below.) */
	$first = true;
	foreach ($context['categories'] as $category)
	{
		echo '
	<div class="tborder" style="margin-top: ' , $first ? '0;' : '1ex;' , '' , $context['browser']['needs_size_fix'] && !$context['browser']['is_ie6'] ? 'width: 100%;' : '', '">
		<div class="catbg', $category['new'] ? '2' : '', '" style="padding: 5px 5px 5px 10px;">';
		$first = false;

		if (!$context['user']['is_guest'])
			echo '
				<div style="float: ', $context['right_to_left'] ? 'left' : 'right', ';">
					<a href="', $scripturl, '?action=unread;c=', $category['id'], '">', $txt['view_unread_category'], '</a>
				</div>';

		// If this category even can collapse, show a link to collapse it.
		if ($category['can_collapse'])
			echo '
				<a href="', $category['collapse_href'], '" rel="nofollow">', $category['collapse_image'], '</a>';

		echo '
				', $category['link'];

		echo '
		</div>';

		// Assuming the category hasn't been collapsed...
		if (!$category['is_collapsed'])
		{
			echo '
		<table border="0" width="100%" cellspacing="1" cellpadding="5" class="bordercolor" style="margin-top: 1px;">';

			/* Each board in each category's boards has:
			new (is it new?), id, name, description, moderators (see below), link_moderators (just a list.),
			children (see below.), link_children (easier to use.), children_new (are they new?),
			topics (# of), posts (# of), link, href, and last_post. (see below.) */
			foreach ($category['boards'] as $board)
			{
				echo '
			<tr>
				<td ' , !empty($board['children']) ? 'rowspan="2"' : '' , ' class="windowbg" width="6%" align="center" valign="top"><a href="', $scripturl, '?action=unread;board=', $board['id'], '.0;children">';

				// If the board or children is new, show an indicator.
				if ($board['new'] || $board['children_new'])
					echo '<img src="', $settings['images_url'], '/on', $board['new'] ? '' : '2', '.gif" alt="', $txt['new_posts'], '" title="', $txt['new_posts'], '" border="0" />';
				// Is it a redirection board?
				elseif ($board['is_redirect'])
					echo '<img src="', $settings['images_url'], '/redirect.gif" alt="*" title="*" border="0" />';
				// No new posts at all! The agony!!
				else
					echo '<img src="', $settings['images_url'], '/off.gif" alt="', $txt['old_posts'], '" title="', $txt['old_posts'], '" />';

				echo '</a>
				</td>
				<td class="windowbg2">
					<b><a href="', $board['href'], '" name="b', $board['id'], '">', $board['name'], '</a></b>';

				// Has it outstanding posts for approval?
				if ($board['can_approve_posts'] && ($board['unapproved_posts'] | $board['unapproved_topics']))
					echo '
					<a href="', $scripturl, '?action=moderate;area=postmod;sa=topics;brd=', $board['id'], ';sesc=', $context['session_id'], '" title="', sprintf($txt['unapproved_posts'], $board['unapproved_topics'], $board['unapproved_posts']), '" class="moderation_link">(!)</a>';

				echo '
					<br />
						', $board['description'];

				// Show the "Moderators: ". Each has name, href, link, and id. (but we're gonna use link_moderators.)
				if (!empty($board['moderators']))
					echo '
					<div style="padding-top: 1px;" class="smalltext"><i>', count($board['moderators']) == 1 ? $txt['moderator'] : $txt['moderators'], ': ', implode(', ', $board['link_moderators']), '</i></div>';

				// Show some basic information about the number of posts, etc.
					echo '
				</td>
				<td class="windowbg" valign="middle" align="center" style="width: 12ex;"><span class="smalltext">
					', $board['posts'], ' ', $board['is_redirect'] ? $txt['redirects'] : $txt['posts'], ' <br />
					', $board['is_redirect'] ? '' : $board['topics'] . ' ' . $txt['board_topics'], '
				</span></td>
				<td class="windowbg2" valign="middle" width="22%">
					<span class="smalltext">';

				/* The board's and children's 'last_post's have:
				time, timestamp (a number that represents the time.), id (of the post), topic (topic id.),
				link, href, subject, start (where they should go for the first unread post.),
				and member. (which has id, name, link, href, username in it.) */
				if (!empty($board['last_post']['id']))
					echo '
						<b>', $txt['last_post'], '</b>  ', $txt['by'], ' ', $board['last_post']['member']['link'] , '<br />
						', $txt['in'], ' ', $board['last_post']['link'], '<br />
						', $txt['on'], ' ', $board['last_post']['time'];
				else
					echo '
						&nbsp;';
				echo '
					</span>
				</td>
			</tr>';
				// Show the "Child Boards: ". (there's a link_children but we're going to bold the new ones...)
				if (!empty($board['children']))
				{
					// Sort the links into an array with new boards bold so it can be imploded.
					$children = array();
					/* Each child in each board's children has:
							id, name, description, new (is it new?), topics (#), posts (#), href, link, and last_post. */
					foreach ($board['children'] as $child)
					{
						if (!$child['is_redirect'])
							$child['link'] = '<a href="' . $child['href'] . '" title="' . ($child['new'] ? $txt['new_posts'] : $txt['old_posts']) . ' (' . $txt['board_topics'] . ': ' . $child['topics'] . ', ' . $txt['posts'] . ': ' . $child['posts'] . ')">' . $child['name'] . '</a>';
						else
							$child['link'] = '<a href="' . $child['href'] . '" title="' . $child['posts'] . ' ' . $txt['redirects'] . '">' . $child['name'] . '</a>';

						// Has it posts awaiting approval?
						if ($child['can_approve_posts'] && ($child['unapproved_posts'] | $child['unapproved_topics']))
							$child['link'] .= '<b style="color: red;" title="' . sprintf($txt['unapproved_posts'], $child['unapproved_topics'], $child['unapproved_posts']) . '">(!)</b>';

						$children[] = $child['new'] ? '<b>' . $child['link'] . '</b>' : $child['link'];
					}

					echo '
			<tr>
				<td colspan="3" class="windowbg', !empty($settings['seperate_sticky_lock']) ? '3' : '', '">
					<span class="smalltext"><b>', $txt['parent_boards'], '</b>: ', implode(', ', $children), '</span>
				</td>
			</tr>';
				}
			}
			echo '
		</table>';
		}
		echo '
	</div>';
	}

	if ($context['user']['is_logged'])
	{
		echo '
	<table border="0" width="100%" cellspacing="0" cellpadding="5">
		<tr>
			<td align="', !$context['right_to_left'] ? 'left' : 'right', '" class="smalltext">
				<img src="' . $settings['images_url'] . '/new_some.gif" alt="" align="middle" /> ', $txt['new_posts'], '
				<img src="' . $settings['images_url'] . '/new_none.gif" alt="" align="middle" style="margin-left: 4ex;" /> ', $txt['old_posts'], '
			</td>
			<td align="', !$context['right_to_left'] ? 'right' : 'left', '">';

		// Mark read button.
		$mark_read_button = array('markread' => array('text' => 'mark_as_read', 'image' => 'markread.gif', 'lang' => true, 'url' => $scripturl . '?action=markasread;sa=all;sesc=' . $context['session_id']));

		// Show the mark all as read button?
		if ($settings['show_mark_read'] && !empty($context['categories']))
				echo '
				<table cellpadding="0" cellspacing="0" border="0" style="position: relative; top: -5px;">
					<tr>
							 ', template_button_strip($mark_read_button, 'top'), '
					</tr>
				</table>';
		echo '
			</td>
		</tr>
	</table>';
	}

	template_info_center();
}

function template_info_center()
{
	global $context, $settings, $options, $txt, $scripturl, $modSettings;

	// Here's where the "Info Center" starts...
	echo '<br />
	<div class="tborder" ', $context['browser']['needs_size_fix'] && !$context['browser']['is_ie6'] ? 'style="width: 100%;"' : '', '>
		<div class="catbg" style="padding: 6px; vertical-align: middle; text-align: center; ">
			<a href="#" onclick="infoHeader.toggle(); return false;"><img id="upshrink_ic" src="', $settings['images_url'], '/', empty($options['collapse_header_ic']) ? 'collapse.gif' : 'expand.gif', '" alt="*" title="', $txt['upshrink_description'], '" style="margin-right: 2ex;" align="right" /></a>
			', sprintf($txt['info_center_title'], $context['forum_name']), '
		</div>
		<div id="upshrinkHeaderIC"', empty($options['collapse_header_ic']) ? '' : ' style="display: none;"', '>
			<table border="0" width="100%" cellspacing="1" cellpadding="4" class="bordercolor">';

	// This is the "Recent Posts" bar.
	if (!empty($settings['number_recent_posts']))
	{
		echo '
				<tr>
					<td class="titlebg" colspan="2">', $txt['recent_posts'], '</td>
				</tr>
				<tr>
					<td class="windowbg" width="20" valign="middle" align="center">
						<a href="', $scripturl, '?action=recent"><img src="', $settings['images_url'], '/post/xx.gif" alt="', $txt['recent_posts'], '" /></a>
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
						<table cellpadding="0" cellspacing="0" width="100%" border="0">';

			/* Each post in latest_posts has:
					board (with an id, name, and link.), topic (the topic's id.), poster (with id, name, and link.),
					subject, short_subject (shortened with...), time, link, and href. */
			foreach ($context['latest_posts'] as $post)
				echo '
							<tr>
								<td class="middletext" valign="top"><b>', $post['link'], '</b> ', $txt['by'], ' ', $post['poster']['link'], ' (', $post['board']['link'], ')</td>
								<td class="middletext" align="right" valign="top" nowrap="nowrap">', $post['time'], '</td>
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
					<td class="titlebg" colspan="2">', $context['calendar_only_today'] ? $txt['calendar_today'] : $txt['calendar_upcoming'], '</td>
				</tr><tr>
					<td class="windowbg" width="20" valign="middle" align="center">
						<a href="', $scripturl, '?action=calendar"><img src="', $settings['images_url'], '/icons/calendar.gif" alt="', $txt['calendar'], '" /></a>
					</td>
					<td class="windowbg2" width="100%">
						<span class="smalltext">';

		// Holidays like "Christmas", "Chanukah", and "We Love [Unknown] Day" :P.
		if (!empty($context['calendar_holidays']))
				echo '
							<span class="holiday">', $txt['calendar_prompt'], ' ', implode(', ', $context['calendar_holidays']), '</span><br />';

		// People's birthdays. Like mine. And yours, I guess. Kidding.
		if (!empty($context['calendar_birthdays']))
		{
				echo '
							<span class="birthday">', $context['calendar_only_today'] ? $txt['birthdays'] : $txt['birthdays_upcoming'], '</span> ';
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
							<span class="event">', $context['calendar_only_today'] ? $txt['events'] : $txt['events_upcoming'], '</span> ';
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


	// Show statistical style information...
	if ($settings['show_stats_index'])
	{
		echo '
				<tr>
					<td class="titlebg" colspan="2">', $txt['forum_stats'], '</td>
				</tr>
				<tr>
					<td class="windowbg" width="20" valign="middle" align="center">
						<a href="', $scripturl, '?action=stats"><img src="', $settings['images_url'], '/icons/info.gif" alt="', $txt['forum_stats'], '" /></a>
					</td>
					<td class="windowbg2" width="100%">
						<span class="middletext">
							', $context['common_stats']['total_posts'], ' ', $txt['posts_made'], ' ', $txt['in'], ' ', $context['common_stats']['total_topics'], ' ', $txt['topics'], ' ', $txt['by'], ' ', $context['common_stats']['total_members'], ' ', $txt['members'], '. ', !empty($settings['show_latest_member']) ? $txt['latest_member'] . ': <b> ' . $context['common_stats']['latest_member']['link'] . '</b>' : '', '
							<br /> ' . $txt['latest_post'] . ': <b>&quot;' . $context['latest_post']['link'] . '&quot;</b>  ( ' . $context['latest_post']['time'] . ' )<br />
							<a href="', $scripturl, '?action=recent">', $txt['recent_view'], '</a>', $context['show_stats'] ? '<br />
							<a href="' . $scripturl . '?action=stats">' . $txt['more_stats'] . '</a>' : '', '
						</span>
					</td>
				</tr>';
	}

	// "Users online" - in order of activity.
	echo '
				<tr>
					<td class="titlebg" colspan="2">', $txt['online_users'], '</td>
				</tr><tr>
					<td rowspan="2" class="windowbg" width="20" valign="middle" align="center">
						', $context['show_who'] ? '<a href="' . $scripturl . '?action=who">' : '', '<img src="', $settings['images_url'], '/icons/online.gif" alt="', $txt['online_users'], '" />', $context['show_who'] ? '</a>' : '', '
					</td>
					<td class="windowbg2" width="100%">';

	echo '
						', $context['show_who'] ? '<a href="' . $scripturl . '?action=who">' : '', $context['num_guests'], ' ', $context['num_guests'] == 1 ? $txt['guest'] : $txt['guests'], ', ' . $context['num_users_online'], ' ', $context['num_users_online'] == 1 ? $txt['user'] : $txt['users'];

	// Handle hidden users and buddies.
	if (!empty($context['num_users_hidden']) || ($context['show_buddies'] && !empty($context['num_buddies'])))
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

	echo $context['show_who'] ? '</a>' : '', '
						<div class="smalltext">';

	// Assuming there ARE users online... each user in users_online has an id, username, name, group, href, and link.
	if (!empty($context['users_online']))
	{
		echo '
							', sprintf($txt['users_active'], $modSettings['lastActive']), ':<br />', implode(', ', $context['list_users_online']);

		// Showing membergroups?
		if (!empty($settings['show_group_key']) && !empty($context['membergroups']))
			echo '
							<br />[' . implode(']&nbsp;&nbsp;[', $context['membergroups']) . ']';
	}

	echo '
						</div>
					</td>
				</tr>
				<tr>
					<td class="windowbg2" width="100%">
						<span class="middletext">
							', $txt['most_online_today'], ': <b>', $modSettings['mostOnlineToday'], '</b>.
							', $txt['most_online_ever'], ': ', $modSettings['mostOnline'], ' (' , timeformat($modSettings['mostDate']), ')
						</span>
					</td>
				</tr>';

	// If they are logged in, but statistical information is off... show a personal message bar.
	if ($context['user']['is_logged'] && !$settings['show_stats_index'])
	{
		 echo '
				<tr>
					<td class="titlebg" colspan="2">', $txt['personal_message'], '</td>
				</tr><tr>
					<td class="windowbg" width="20" valign="middle" align="center">
						', $context['allow_pm'] ? '<a href="' . $scripturl . '?action=pm">' : '', '<img src="', $settings['images_url'], '/message_sm.gif" alt="', $txt['personal_message'], '" />', $context['allow_pm'] ? '</a>' : '', '
					</td>
					<td class="windowbg2" valign="top">
						<b><a href="', $scripturl, '?action=pm">', $txt['personal_message'], '</a></b>
						<div class="smalltext">
							', $txt['you_have'], ' ', $context['user']['messages'], ' ', $context['user']['messages'] == 1 ? $txt['message_lowercase'] : $txt['msg_alert_messages'], '.... ', $txt['click'], ' <a href="', $scripturl, '?action=pm">', $txt['here'], '</a> ', $txt['to_view'], '
						</div>
					</td>
				</tr>';
	}

	// Show the login bar. (it's only true if they are logged out anyway.)
	if ($context['show_login_bar'])
	{
		echo '
				<tr>
					<td class="titlebg" colspan="2">', $txt['login'], ' <a href="', $scripturl, '?action=reminder" class="smalltext">(' . $txt['forgot_your_password'] . ')</a></td>
				</tr>
				<tr>
					<td class="windowbg" width="20" align="center">
						<a href="', $scripturl, '?action=login"><img src="', $settings['images_url'], '/icons/login.gif" alt="', $txt['login'], '" /></a>
					</td>
					<td class="windowbg2" valign="middle">
						<form action="', $scripturl, '?action=login2" method="post" accept-charset="', $context['character_set'], '" style="margin: 0;">
							<table border="0" cellpadding="2" cellspacing="0" align="center" width="100%"><tr>
								<td valign="middle" align="left">
									<label for="user"><b>', $txt['username'], ':</b><br />
									<input type="text" name="user" id="user" size="15" /></label>
								</td>
								<td valign="middle" align="left">
									<label for="passwrd"><b>', $txt['password'], ':</b><br />
									<input type="password" name="passwrd" id="passwrd" size="15" /></label>
								</td>
								<td valign="middle" align="left">
									<label for="cookielength"><b>', $txt['mins_logged_in'], ':</b><br />
									<input type="text" name="cookielength" id="cookielength" size="4" maxlength="4" value="', $modSettings['cookieTime'], '" /></label>
								</td>
								<td valign="middle" align="left">
									<label for="cookieneverexp"><b>', $txt['always_logged_in'], ':</b><br />
									<input type="checkbox" name="cookieneverexp" id="cookieneverexp" checked="checked" class="check" /></label>
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
			</table>
		</div>
	</div>';
}

?>