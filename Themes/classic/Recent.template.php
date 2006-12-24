<?php
// Version: 2.0 Alpha; Recent

function template_main()
{
	global $context, $settings, $options, $txt, $scripturl;

	echo '
			<div style="padding: 3px;">', theme_linktree(), '</div>
			<table width="100%" cellpadding="3" cellspacing="0" border="0" class="tborder">
				<tr>
					<td align="left" class="catbg" width="100%" height="30">
						<b>', $txt['pages'], ':</b> ', $context['page_index'], '
					</td>
				</tr>
			</table>
			<br />';

	foreach ($context['posts'] as $post)
	{
		echo '
			<table width="100%" cellpadding="4" cellspacing="1" border="0" class="bordercolor">
				<tr class="titlebg">
					<td>
						<div style="float: left; width: 3ex;">&nbsp;', $post['counter'], '&nbsp;</div>
						<div style="float: left;">&nbsp;', $post['category']['link'], ' / ', $post['board']['link'], ' / ', $post['link'], '</div>
						<div align="right">&nbsp;', $txt['on'], ': ', $post['time'], '&nbsp;</div>
					</td>
				</tr>
				<tr>
					<td class="catbg" colspan="3">
						', $txt['started_by'], ' ', $post['first_poster']['link'], ' - ', $txt['last_post'], ' ', $txt['by'], ' ', $post['poster']['link'], '
					</td>
				</tr>
				<tr>
					<td class="windowbg2" colspan="3" valign="top" height="40">
						<div class="post">', $post['message'], '</div>
					</td>
				</tr>
				<tr>
					<td class="catbg" colspan="3" align="right">';

		if ($post['can_delete'])
			echo '
								<a href="', $scripturl, '?action=recent;delete=', $post['id'], ';sesc=', $context['session_id'], '" onclick="return confirm(\'', $txt['remove_message'], '?\');">', $settings['use_image_buttons'] ? '<img src="' . $settings['lang_images_url'] . '/delete.gif" alt="' . $txt['remove_message'] . '" border="0" />' : $txt['remove'], '</a>';
		if ($post['can_delete'] && ($post['can_mark_notify'] || $post['can_reply']))
			echo '
								', $context['menu_separator'];
		if ($post['can_reply'])
			echo '
						<a href="', $scripturl, '?action=post;topic=', $post['topic'], '.', $post['start'], '">', $settings['use_image_buttons'] ? '<img src="' . $settings['lang_images_url'] . '/reply_sm.gif" alt="' . $txt['reply'] . '" border="0" />' : $txt['reply'], '</a>', $context['menu_separator'], '
						<a href="', $scripturl, '?action=post;topic=', $post['topic'], '.', $post['start'], ';quote=', $post['id'], ';sesc=', $context['session_id'], '">', $settings['use_image_buttons'] ? '<img src="' . $settings['lang_images_url'] . '/quote.gif" alt="' . $txt['reply_quote'] . '" border="0" />' : $txt['reply_quote'], '</a>';
		if ($post['can_reply'] && $post['can_mark_notify'])
			echo '
						', $context['menu_separator'];
		if ($post['can_mark_notify'])
			echo '
						<a href="', $scripturl, '?action=notify;topic=', $post['topic'], '.', $post['start'], '">', $settings['use_image_buttons'] ? '<img src="' . $settings['lang_images_url'] . '/notify_sm.gif" alt="' . $txt['notify_replies'] . '" border="0" />' : $txt['notify_replies'], '</a>';

		echo '</td>
				</tr>
			</table>
			<br />';
	}

	echo '
			<table width="100%" cellpadding="3" cellspacing="0" border="0" class="tborder">
				<tr>
					<td align="left" class="catbg" width="100%" height="30">
						<b>', $txt['pages'], ':</b> ', $context['page_index'], '
					</td>
				</tr>
			</table>';
	if ($settings['linktree_inline'])
		echo '
			<div style="padding: 3px;">', theme_linktree(), '</div>';
}

function template_unread()
{
	global $context, $settings, $options, $txt, $scripturl, $modSettings;

	echo '
<table width="100%" border="0" cellspacing="0" cellpadding="3">
	<tr>
		<td>', theme_linktree(), '</td>
	</tr>
</table>

<table width="100%" cellpadding="0" cellspacing="0" border="0" class="bordercolor">
	<tr><td>
		<div style="padding: 1px;" class="bordercolor">
			<table width="100%" cellpadding="4" cellspacing="0" border="0" class="catbg">
				<tr>
					<td valign="middle"><b>', $txt['pages'], ':</b> ', $context['page_index'], '</td>', $settings['show_mark_read'] ? '
					<td align="right" nowrap="nowrap" style="font-size: smaller;"><a href="' . $scripturl . '?action=markasread;sa=' . (!empty($context['no_board_limits']) ? 'all' : 'board' . $context['querystring_board_limits']) . ';sesc=' . $context['session_id'] . '">' . ($settings['use_image_buttons'] ? '<img src="' . $settings['lang_images_url'] . '/markread.gif" alt="' . $txt['mark_as_read'] . '" border="0" />' : $txt['mark_as_read']) . '</a></td>' : '', '
				</tr>
			</table>
		</div>

		<table border="0" width="100%" cellspacing="1" cellpadding="4" class="bordercolor">
			<tr class="titlebg">';
	if (!empty($context['topics']))
		echo '
				<td width="10%" colspan="2">&nbsp;</td>
				<td><a href="', $scripturl, '?action=unread', $context['showing_all_topics'] ? ';all' : '', $context['querystring_board_limits'], ';sort=subject', $context['sort_by'] == 'subject' && $context['sort_direction'] == 'up' ? ';desc' : '', '">', $txt['subject'], $context['sort_by'] == 'subject' ? ' <img src="' . $settings['images_url'] . '/sort_' . $context['sort_direction'] . '.gif" alt="" border="0" />' : '', '</a></td>
				<td width="14%"><a href="', $scripturl, '?action=unread', $context['showing_all_topics'] ? ';all' : '', $context['querystring_board_limits'], ';sort=starter', $context['sort_by'] == 'starter' && $context['sort_direction'] == 'up' ? ';desc' : '', '">', $txt['started_by'], $context['sort_by'] == 'starter' ? ' <img src="' . $settings['images_url'] . '/sort_' . $context['sort_direction'] . '.gif" alt="" border="0" />' : '', '</a></td>
				<td width="4%" align="center"><a href="', $scripturl, '?action=unread', $context['showing_all_topics'] ? ';all' : '', $context['querystring_board_limits'], ';sort=replies', $context['sort_by'] == 'replies' && $context['sort_direction'] == 'up' ? ';desc' : '', '">', $txt['replies'], $context['sort_by'] == 'replies' ? ' <img src="' . $settings['images_url'] . '/sort_' . $context['sort_direction'] . '.gif" alt="" border="0" />' : '', '</a></td>
				<td width="4%" align="center"><a href="', $scripturl, '?action=unread', $context['showing_all_topics'] ? ';all' : '', $context['querystring_board_limits'], ';sort=views', $context['sort_by'] == 'views' && $context['sort_direction'] == 'up' ? ';desc' : '', '">', $txt['views'], $context['sort_by'] == 'views' ? ' <img src="' . $settings['images_url'] . '/sort_' . $context['sort_direction'] . '.gif" alt="" border="0" />' : '', '</a></td>
				<td width="24%"><a href="', $scripturl, '?action=unread', $context['showing_all_topics'] ? ';all' : '', $context['querystring_board_limits'], ';sort=last_post', $context['sort_by'] == 'last_post' && $context['sort_direction'] == 'up' ? ';desc' : '', '">', $txt['last_post'], $context['sort_by'] == 'last_post' ? ' <img src="' . $settings['images_url'] . '/sort_' . $context['sort_direction'] . '.gif" alt="" border="0" />' : '', '</a></td>';
	else
		echo '
				<td width="100%" colspan="7">', $context['showing_all_topics'] ? $txt['msg_alert_none'] : $txt['unread_topics_visit_none'], '</td>';
	echo '
			</tr>';

	foreach ($context['topics'] as $topic)
	{
		echo '
			<tr>
				<td class="windowbg2" valign="middle" align="center" width="6%">
					<img src="', $settings['images_url'], '/topic/', $topic['class'], '.gif" alt="" /></td>
				<td class="windowbg2" valign="middle" align="center" width="4%">
					<img src="', $settings[$context['icon_sources'][$topic['first_post']['icon']]], '/post/', $topic['first_post']['icon'], '.gif" alt="" border="0" align="middle" /></td>
				<td class="windowbg" valign="middle" width="48%">
					', $topic['first_post']['link'], ' <a href="', $topic['new_href'], '"><img src="', $settings['lang_images_url'], '/new.gif" alt="', $txt['new'], '" border="0" /></a> <span class="smalltext">', $topic['pages'], '</span>
					<div class="smalltext"><i>', $txt['in'], ' ', $topic['board']['link'], '</i></div></td>
				<td class="windowbg2" valign="middle" width="14%">
					', $topic['first_post']['member']['link'], '</td>
				<td class="windowbg" valign="middle" width="4%" align="center">
					', $topic['replies'], '</td>
				<td class="windowbg" valign="middle" width="4%" align="center">
					', $topic['views'] . '</td>
				<td class="windowbg2" valign="middle" width="27%">
					<span class="smalltext">
						', $topic['last_post']['time'], '<br />
						', $txt['by'], ' ', $topic['last_post']['member']['link'], '
					</span></td>
			</tr>';
	}

	if (!empty($context['topics']) && !$context['showing_all_topics'])
		echo '
			<tr class="titlebg">
				<td colspan="7" align="right"><a href="', $scripturl, '?action=unread;all', $context['querystring_board_limits'], '">', $txt['unread_topics_all'], '</a></td>
			</tr>';

	echo '
		</table>

		<div style="padding: 1px;" class="bordercolor">
			<table width="100%" cellpadding="4" cellspacing="0" border="0" class="catbg">
				<tr>
					<td valign="middle"><b>', $txt['pages'], ':</b> ', $context['page_index'], '</td>', $settings['show_mark_read'] ? '
					<td align="right" nowrap="nowrap" style="font-size: smaller;"><a href="' . $scripturl . '?action=markasread;sa=' . (!empty($context['no_board_limits']) ? 'all' : 'board' . $context['querystring_board_limits']) . ';sesc=' . $context['session_id'] . '">' . ($settings['use_image_buttons'] ? '<img src="' . $settings['lang_images_url'] . '/markread.gif" alt="' . $txt['mark_as_read'] . '" border="0" />' : $txt['mark_as_read']) . '</a></td>' : '', '
				</tr>
			</table>
		</div>
	</td></tr>
</table>
<table cellpadding="0" cellspacing="0" width="55%">
	<tr>
		<td class="smalltext" align="left" style="padding-top: 1ex;">', !empty($modSettings['enableParticipation']) ? '
			<img src="' . $settings['images_url'] . '/topic/my_normal_post.gif" alt="" align="middle" /> ' . $txt['participation_caption'] . '<br />' : '', '
			<img src="' . $settings['images_url'] . '/topic/normal_post.gif" alt="" align="middle" /> ' . $txt['normal_topic'] . '<br />
			<img src="' . $settings['images_url'] . '/topic/hot_post.gif" alt="" align="middle" /> ' . sprintf($txt['hot_topics'], $modSettings['hotTopicPosts']) . '<br />
			<img src="' . $settings['images_url'] . '/topic/veryhot_post.gif" alt="" align="middle" /> ' . sprintf($txt['very_hot_topics'], $modSettings['hotTopicVeryPosts']) . '
		</td>
		<td class="smalltext" align="left" valign="top" style="padding-top: 1ex;">
			<img src="' . $settings['images_url'] . '/topic/normal_post_locked.gif" alt="" align="middle" /> ' . $txt['locked_topic'] . '<br />' . ($modSettings['enableStickyTopics'] == '1' ? '
			<img src="' . $settings['images_url'] . '/topic/normal_post_sticky.gif" alt="" align="middle" /> ' . $txt['smf96'] . '<br />' : '') . ($modSettings['pollMode'] == '1' ? '
			<img src="' . $settings['images_url'] . '/topic/normal_poll.gif" alt="" align="middle" /> ' . $txt['poll'] : '') . '
		</td>
	</tr>
</table>';
}

function template_replies()
{
	global $context, $settings, $options, $txt, $scripturl, $modSettings;

	echo '
<table width="100%" border="0" cellspacing="0" cellpadding="3" align="center">
	<tr>
		<td>', theme_linktree(), '</td>
	</tr>
</table>
<table border="0" width="100%" cellspacing="0" cellpadding="0" class="bordercolor">
	<tr><td>
		<div style="padding: 1px;" class="bordercolor">
			<table width="100%" cellpadding="4" cellspacing="0" border="0" class="catbg">
				<tr>
					<td valign="middle"><b>' . $txt['pages'] . ':</b> ' . $context['page_index'] . '</td>
					<td align="right" nowrap="nowrap" style="font-size: smaller;">';
	if (isset($context['topics_to_mark']) && !empty($settings['show_mark_read']))
		echo '
						<a href="' . $scripturl . '?action=markasread;sa=unreadreplies;topics=' . $context['topics_to_mark'] . ';sesc=' . $context['session_id'] . '">' . ($settings['use_image_buttons'] ? '<img src="' . $settings['lang_images_url'] . '/markread.gif" alt="' . $txt['mark_as_read'] . '" border="0" />' : $txt['mark_as_read']) . '</a>';
	echo '
					</td>
				</tr>
			</table>
		</div>

		<table border="0" width="100%" cellspacing="1" cellpadding="4" class="bordercolor">
			<tr class="titlebg">';
	if (!empty($context['topics']))
		echo '
				<td width="10%" colspan="2">&nbsp;</td>
				<td><a href="', $scripturl, '?action=unreadreplies', $context['querystring_board_limits'], ';sort=subject', $context['sort_by'] == 'subject' && $context['sort_direction'] == 'up' ? ';desc' : '', '">', $txt['subject'], $context['sort_by'] == 'subject' ? ' <img src="' . $settings['images_url'] . '/sort_' . $context['sort_direction'] . '.gif" alt="" border="0" />' : '', '</a></td>
				<td width="14%"><a href="', $scripturl, '?action=unreadreplies', $context['querystring_board_limits'], ';sort=starter', $context['sort_by'] == 'starter' && $context['sort_direction'] == 'up' ? ';desc' : '', '">', $txt['started_by'], $context['sort_by'] == 'starter' ? ' <img src="' . $settings['images_url'] . '/sort_' . $context['sort_direction'] . '.gif" alt="" border="0" />' : '', '</a></td>
				<td width="4%" align="center"><a href="', $scripturl, '?action=unreadreplies', $context['querystring_board_limits'], ';sort=replies', $context['sort_by'] == 'replies' && $context['sort_direction'] == 'up' ? ';desc' : '', '">', $txt['replies'], $context['sort_by'] == 'replies' ? ' <img src="' . $settings['images_url'] . '/sort_' . $context['sort_direction'] . '.gif" alt="" border="0" />' : '', '</a></td>
				<td width="4%" align="center"><a href="', $scripturl, '?action=unreadreplies', $context['querystring_board_limits'], ';sort=views', $context['sort_by'] == 'views' && $context['sort_direction'] == 'up' ? ';desc' : '', '">', $txt['views'], $context['sort_by'] == 'views' ? ' <img src="' . $settings['images_url'] . '/sort_' . $context['sort_direction'] . '.gif" alt="" border="0" />' : '', '</a></td>
				<td width="24%"><a href="', $scripturl, '?action=unreadreplies', $context['querystring_board_limits'], ';sort=last_post', $context['sort_by'] == 'last_post' && $context['sort_direction'] == 'up' ? ';desc' : '', '">', $txt['last_post'], $context['sort_by'] == 'last_post' ? ' <img src="' . $settings['images_url'] . '/sort_' . $context['sort_direction'] . '.gif" alt="" border="0" />' : '', '</a></td>';
	else
		echo '
				<td width="100%" colspan="7">' . $txt['msg_alert_none'] . '</td>';
	echo '
			</tr>';

	foreach ($context['topics'] as $topic)
	{
		echo '
			<tr>
				<td class="windowbg2" valign="middle" align="center" width="6%">
					<img src="', $settings['images_url'], '/topic/', $topic['class'], '.gif" alt="" /></td>
				<td class="windowbg2" valign="middle" align="center" width="4%">
					<img src="', $settings[$context['icon_sources'][$topic['first_post']['icon']]], '/post/', $topic['first_post']['icon'], '.gif" alt="" border="0" align="middle" /></td>
				<td class="windowbg" valign="middle" width="48%">
					', $topic['first_post']['link'], ' <a href="', $topic['new_href'], '"><img src="', $settings['lang_images_url'], '/new.gif" alt="', $txt['new'], '" border="0" /></a> <span class="smalltext">', $topic['pages'], '</span>
					<div class="smalltext"><i>', $txt['in'], ' ', $topic['board']['link'], '</i></div></td>
				<td class="windowbg2" valign="middle" width="14%">
					', $topic['first_post']['member']['link'], '</td>
				<td class="windowbg" valign="middle" width="4%" align="center">
					', $topic['replies'], '</td>
				<td class="windowbg" valign="middle" width="4%" align="center">
					', $topic['views'], '</td>
				<td class="windowbg2" valign="middle" width="27%">
					<span class="smalltext">', $topic['last_post']['time'], '<br />', $txt['by'], ' ', $topic['last_post']['member']['link'], '</span></td>
			</tr>';
	}

	echo '
		</table>

		<div style="padding: 1px;" class="bordercolor">
			<table width="100%" cellpadding="4" cellspacing="0" border="0" class="catbg">
				<tr>
					<td valign="middle"><b>', $txt['pages'], ':</b> ', $context['page_index'], '</td>
					<td align="right" nowrap="nowrap" style="font-size: smaller;">';

	if (isset($context['topics_to_mark']) && !empty($settings['show_mark_read']))
		echo '
						<a href="', $scripturl, '?action=markasread;sa=unreadreplies;topics=', $context['topics_to_mark'], ';sesc=', $context['session_id'], '">', $settings['use_image_buttons'] ? '<img src="' . $settings['lang_images_url'] . '/markread.gif" alt="' . $txt['mark_as_read'] . '" border="0" />' : $txt['mark_as_read'], '</a>';

	echo '
					</td>
				</tr>
			</table>
		</div>
	</td></tr>
</table>

<table cellpadding="0" cellspacing="0" width="55%">
	<tr>
		<td class="smalltext" align="left" style="padding-top: 1ex;">
			<img src="', $settings['images_url'], '/topic/my_normal_post.gif" alt="" align="middle" /> ', $txt['normal_topic'], '<br />
			<img src="', $settings['images_url'], '/topic/my_hot_post.gif" alt="" align="middle" /> ', sprintf($txt['hot_topics'], $modSettings['hotTopicPosts']), '<br />
			<img src="', $settings['images_url'], '/topic/my_veryhot_post.gif" alt="" align="middle" /> ', sprintf($txt['very_hot_topics'], $modSettings['hotTopicVeryPosts']), '
		</td>
		<td class="smalltext" align="left" valign="top" style="padding-top: 1ex;">
			<img src="', $settings['images_url'], '/topic/my_normal_post_locked.gif" alt="" align="middle" /> ', $txt['locked_topic'], '<br />', $modSettings['enableStickyTopics'] == '1' ? '
			<img src="' . $settings['images_url'] . '/topic/my_normal_post_sticky.gif" alt="" align="middle" /> ' . $txt['smf96'] . '<br />' : '', $modSettings['pollMode'] == '1' ? '
			<img src="' . $settings['images_url'] . '/topic/my_normal_poll.gif" alt="" align="middle" /> ' . $txt['poll'] : '', '
		</td>
	</tr>
</table>';
}

?>