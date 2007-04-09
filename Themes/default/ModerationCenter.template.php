<?php
// Version: 2.0 Alpha; ModerationCenter

function template_moderation_center()
{
	global $settings, $options, $context, $txt, $scripturl;

	// Show a welcome message to the user.
	echo '
		<table width="100%" cellpadding="3" cellspacing="1" border="0" class="bordercolor">
			<tr class="titlebg">
				<td align="center" colspan="2" class="largetext">', $txt['moderation_center'], '</td>
			</tr><tr>
				<td class="windowbg" valign="top" style="padding: 7px;">
					<b>', $txt['hello_guest'], ' ', $context['user']['name'], '!</b>
					<div style="font-size: 0.85em; padding-top: 1ex;">', $txt['mc_description'], '</div>
				</td>
			</tr>
		</table>';

	$alternate = 0;
	// Show all the blocks they want to see.
	foreach ($context['mod_blocks'] as $block)
	{
		$block_function = 'template_' . $block;

		// Start of a new row?
		if (!$alternate)
			echo '
		<table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-top: 1.5ex;">
			<tr valign="top">';

		echo '
				<td width="50%">', function_exists($block_function) ? $block_function() : '', '</td>';

		// If was first in a row, put in a spacer.
		if (!$alternate)
			echo '
				<td style="width: 1ex;">&nbsp;</td>';
		// If the last one, end the row...
		else
			echo '
			</tr>
		</table>';

		$alternate = !$alternate;			
	}

	// If alternate is 1, we never quite finished off a row.
	if ($alternate)
		echo '
				<td width="50%"></td>
			</tr>
		</table>';	
}

function template_latest_news()
{
	global $settings, $options, $context, $txt, $scripturl;

	echo '
	<table width="100%" cellpadding="5" cellspacing="1" border="0" class="bordercolor" style="height: 180px;">
		<tr>
			<td class="catbg">
				<a href="', $scripturl, '?action=helpadmin;help=live_news" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt['help'], '" align="top" /></a> ', $txt['mc_latest_news'], '
			</td>
		</tr><tr>
			<td class="windowbg2" valign="top" style="height: 18ex; padding: 0;">
				<div id="smfAnnouncements" style="height: 18ex; overflow: auto; padding-right: 1ex;"><div style="margin: 4px; font-size: 0.85em;">', $txt['mc_cannot_connect_sm'], '</div></div>
			</td>
		</tr>
	</table>';

	// This requires a lot of javascript...
	//!!! Put this in it's own file!!
	echo '
		<script language="JavaScript" type="text/javascript" src="', $scripturl, '?action=viewsmfile;filename=latest-news.js"></script>
		<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
			function smfSetAnnouncements()
			{
				if (typeof(window.smfAnnouncements) == "undefined" || typeof(window.smfAnnouncements.length) == "undefined")
					return;

				var str = "<div style=\"margin: 4px; font-size: 0.85em;\">";

				for (var i = 0; i < window.smfAnnouncements.length; i++)
				{
					str += "\n	<div style=\"padding-bottom: 2px;\"><a hre" + "f=\"" + window.smfAnnouncements[i].href + "\">" + window.smfAnnouncements[i].subject + "</a> ', $txt['on'], ' " + window.smfAnnouncements[i].time + "</div>";
					str += "\n	<div style=\"padding-left: 2ex; margin-bottom: 1.5ex; border-top: 1px dashed;\">"
					str += "\n		" + window.smfAnnouncements[i].message;
					str += "\n	</div>";
				}

				setInnerHTML(document.getElementById("smfAnnouncements"), str + "</div>");
			}

			var oldonload;
			if (typeof(window.onload) != "undefined")
				oldonload = window.onload;

			window.onload = function ()
			{
				smfSetAnnouncements();';

	echo '
				if (oldonload)
					oldonload();
			}
		// ]]></script>';

}

// Show all the group requests the user can see.
function template_group_requests_block()
{
	global $settings, $options, $context, $txt, $scripturl;

	echo '
	<table width="100%" cellpadding="5" cellspacing="1" border="0" class="bordercolor" style="height: 180px;">
		<tr>
			<td class="catbg">
				<a href="', $scripturl, '?action=groups;sa=requests">', $txt['mc_group_requests'], '</a>
			</td>
		</tr>';

	foreach ($context['group_requests'] as $request)
	{
		echo '
		<tr class="windowbg2">
			<td class="smalltext">
				<a href="', $request['request_href'], '">', $request['group']['name'], '</a> ', $txt['mc_groupr_by'], ' ', $request['member']['link'], '
			</td>
		</tr>';
	}

	// Don't have any watched users right now?
	if (empty($context['group_requests']))
		echo '
		<tr>
			<td class="windowbg2" align="center" valign="top" style="height: 18ex; padding: 2;">
				<b class="smalltext">', $txt['mc_group_requests_none'], '</b>
			</td>
		</tr>';

	echo '
		<tr class="windowbg" style="height: 100%;">
			<td></td>
		</tr>
	</table>';
}

// A block to show the current top reported posts.
function template_reported_posts_block()
{
	global $settings, $options, $context, $txt, $scripturl;

	echo '
	<table width="100%" cellpadding="5" cellspacing="1" border="0" class="bordercolor" style="height: 180px;">
		<tr>
			<td class="catbg">
				<a href="', $scripturl, '?action=moderate;area=reports">', $txt['mc_recent_reports'], '</a>
			</td>
		</tr>';

	foreach ($context['reported_posts'] as $report)
	{
		echo '
		<tr class="windowbg2">
			<td class="smalltext">
				<a href="', $report['report_href'], '">', $report['subject'], '</a> ', $txt['mc_reportedp_by'], ' ', $report['author']['link'], '
			</td>
		</tr>';
	}

	// Don't have any watched users right now?
	if (empty($context['reported_posts']))
		echo '
		<tr>
			<td class="windowbg2" align="center" valign="top" style="height: 18ex; padding: 2;">
				<b class="smalltext">', $txt['mc_recent_reports_none'], '</b>
			</td>
		</tr>';

	echo '
		<tr class="windowbg" style="height: 100%;">
			<td></td>
		</tr>
	</table>';
}

function template_watched_users()
{
	global $settings, $options, $context, $txt, $scripturl;

	echo '
	<table width="100%" cellpadding="5" cellspacing="1" border="0" class="bordercolor" style="height: 180px;">
		<tr>
			<td class="catbg">
				<a href="', $scripturl, '?action=moderate;area=userwatch">', $txt['mc_watched_users'], '</a>
			</td>
		</tr>';

	foreach ($context['watched_users'] as $user)
	{
		echo '
		<tr class="windowbg2">
			<td>
				<span class="smalltext">', $user['link'], ' ', $txt['mc_seen'], ' ', $user['last_login'], '</span>
			</td>
		</tr>';
	}

	// Don't have any watched users right now?
	if (empty($context['watched_users']))
		echo '
		<tr>
			<td class="windowbg2" align="center" valign="top" style="height: 18ex; padding: 2;">
				<b class="smalltext">', $txt['mc_watched_users_none'], '</b>
			</td>
		</tr>';

	echo '
		<tr class="windowbg" style="height: 100%;">
			<td></td>
		</tr>
	</table>';
}

// Little section for making... notes.
function template_notes()
{
	global $settings, $options, $context, $txt, $scripturl;

	echo '
	<form action="', $scripturl, '?action=moderate;area=index" method="post">
	<table width="100%" cellpadding="5" cellspacing="1" border="0" class="bordercolor" style="height: 180px;">
		<tr>
			<td class="catbg">
				', $txt['mc_notes'], '
			</td>
		</tr>';

	// Cycle through the notes.
	foreach ($context['notes'] as $note)
		echo '
		<tr>
			<td class="windowbg2" align="left">
				<span class="smalltext">', $note['author']['link'], ': ', $note['text'], '</span>
			</td>
		</tr>';

	echo '
		<tr class="windowbg" style="height: 100%;">
			<td></td>
		</tr>
		<tr class="windowbg">
			<td>
				<div style="float: left;" style="width: 90%;">
					<input type="text" name="new_note" value="', $txt['mc_click_add_note'], '" style="width: 100%;" onclick="if (this.value == \'', $txt['mc_click_add_note'], '\') this.value = \'\';" />
				</div>
				<div style="float: right;">
					<input type="submit" name="makenote" value="', $txt['mc_add_note'], '" style="width: 100%;" />
				</div>
			</td>
		</tr>
	</table>
		<input type="hidden" name="sc" value="', $context['session_id'], '" />
	</form>';
}

function template_reported_posts()
{
	global $settings, $options, $context, $txt, $scripturl;

	echo '
	<form action="', $scripturl, '?action=moderate;area=reports', $context['view_closed'] ? ';sa=closed' : '', ';start=', $context['start'], '" method="post" accept-charset="', $context['character_set'], '">
		<table width="100%" cellpadding="3" cellspacing="1" border="0" class="bordercolor">
			<tr class="titlebg">
				<td>', $context['view_closed'] ? $txt['mc_reportedp_closed'] : $txt['mc_reportedp_open'], '</td>
			</tr><tr class="catbg">
				<td>', $txt['pages'], ': ', $context['page_index'], '</td>
			</tr>';

	// Loop through and print out each report!
	$alternate = 0;

	// Make the buttons.
	$close_button = create_button('close.gif', $context['view_closed'] ? 'mc_reportedp_open' : 'mc_reportedp_close', $context['view_closed'] ? 'mc_reportedp_open' : 'mc_reportedp_close', 'align="middle"');
	$ignore_button = create_button('ignore.gif', 'mc_reportedp_ignore', 'mc_reportedp_ignore', 'align="middle"');
	$unignore_button = create_button('ignore.gif', 'mc_reportedp_unignore', 'mc_reportedp_unignore', 'align="middle"');

	foreach ($context['reports'] as $report)
	{
		echo '
			<tr class="', $report['ignore'] ? 'windowbg3' : ($alternate ? 'windowbg' : 'windowbg2'), '">
				<td>
					<div>
						<div style="float: left">
							<b><a href="', $report['report_href'], '">', $report['subject'], '</a></b> ', $txt['mc_reportedp_by'], ' <b>', $report['author']['link'], '</b>
						</div>
						<div style="float: right">
							<a href="', $scripturl, '?action=moderate;area=reports', $context['view_closed'] ? ';sa=closed' : '', ';ignore=', !$report['ignore'], ';rid=', $report['id'], ';start=', $context['start'], ';sesc=', $context['session_id'], '" ', !$report['ignore'] ? 'onclick="return confirm(\'' . $txt['mc_reportedp_ignore_confirm'] . '\');"' : '', '>', $report['ignore'] ? $unignore_button : $ignore_button, '</a>
							<a href="', $scripturl, '?action=moderate;area=reports', $context['view_closed'] ? ';sa=closed' : '', ';close=', !$report['closed'], ';rid=', $report['id'], ';start=', $context['start'], ';sesc=', $context['session_id'], '">', $close_button, '</a>
							', !$context['view_closed'] ? '<input type="checkbox" name="close[]" value="' . $report['id'] . '" class="check" />' : '', '
						</div>
					</div><br />
					<div class="smalltext">
						&#171; ', $txt['mc_reportedp_last_reported'], ': ', $report['last_updated'], ' &#187;<br />';

		// Prepare the comments...
		$comments = array();
		foreach ($report['comments'] as $comment)
			$comments[] = '<a href="' . $comment['member']['href'] . '" title="' . $comment['message'] . '">' . $comment['member']['name'] . '</a>';
		echo '
						&#171; ', $txt['mc_reportedp_reported_by'], ': ', implode(', ', $comments), ' &#187;
					</div>
					<hr />
					', $report['body'], '
				</td>
			</tr>';
		$alternate = !$alternate;
	}

	// Were none found?
	if (empty($context['reports']))
		echo '
			<tr class="windowbg">
				<td align="center">', $txt['mc_reportedp_none_found'], '</td>
			</tr>';

	echo '
			<tr class="catbg">
				<td>
					<div style="float: left;">
						', $txt['pages'], ': ', $context['page_index'], '
					</div>
					<div style="float: right;">
						', !$context['view_closed'] ? '<input type="submit" name="close_selected" value="' . $txt['mc_reportedp_close_selected'] . '" />' : '', '
					</div>
				</td>
			</tr>
		</table>
		<input type="hidden" name="sc" value="', $context['session_id'], '" />
	</form>';
}

// Show a list of all the unapproved posts
function template_unapproved_posts()
{
	global $settings, $options, $context, $txt, $scripturl;

	// Just a big table of it all really...
	echo '
	<form action="', $scripturl, '?action=moderate;area=postmod;sa=posts;start=', $context['start'], ';sa=', $context['current_view'], '" method="post" accept-charset="', $context['character_set'], '">
		<table width="100%" cellpadding="3" cellspacing="1" border="0" class="bordercolor">
			<tr class="titlebg">
				<td>', $txt['mc_unapproved_posts'], '</td>
			</tr>';

	// Loop through and print out each outstanding post ;)
	$alternate = 0;

	// Make up some buttons
	$approve_button = create_button('approve.gif', 'approve', 'approve', 'align="middle"');
	$remove_button = create_button('delete.gif', 'remove_message', 'remove', 'align="middle"');

	// No posts?
	if (empty($context['unapproved_items']))
		echo '
			<tr class="windowbg">
				<td align="center">', $txt['mc_unapproved_' . $context['current_view'] . '_none_found'], '</td>
			</tr>';
	else
		echo '
			<tr class="catbg">
				<td>', $txt['pages'], ': ', $context['page_index'], '</td>
			</tr>';

	echo '
		</table>';

	foreach ($context['unapproved_items'] as $item)
	{
		echo '
		<table width="100%" cellpadding="0" cellspacing="1" border="0" class="bordercolor">
			<tr>
				<td width="100%">
					<table border="0" width="100%" cellspacing="0" cellpadding="4" class="bordercolor" align="center">
						<tr class="titlebg2">
							<td style="padding: 0 1ex;">
								', $item['counter'], '
							</td>
							<td width="75%" class="middletext">
								&nbsp;<a href="', $scripturl, '#', $item['category']['id'], '">', $item['category']['name'], '</a> / <a href="', $scripturl, '?board=', $item['board']['id'], '.0">', $item['board']['name'], '</a> / <a href="', $scripturl, '?topic=', $item['topic'], '.msg', $item['id'], '#msg', $item['id'], '">', $item['subject'], '</a>
							</td>
							<td class="middletext" align="right" style="padding: 0 1ex; white-space: nowrap;">
								', $txt['mc_unapproved_by'], ' ', $item['poster']['link'], ' ', $txt['on'], ': ', $item['time'], '
							</td>
						</tr>
						<tr>
							<td width="100%" height="80" colspan="3" valign="top" class="windowbg2">
								<div class="post">', $item['body'], '</div>
							</td>
						</tr>
						<tr>
							<td colspan="3" class="windowbg2" align="', !$context['right_to_left'] ? 'right' : 'left', '"><span class="middletext">

					<a href="', $scripturl, '?action=moderate;area=postmod;sa=posts;sa=', $context['current_view'], ';start=', $context['start'], ';sesc=', $context['session_id'], ';approve=', $item['id'], '">', $approve_button, '</a>';

			if ($item['can_delete'])
				echo '
					', $context['menu_separator'], '
					<a href="', $scripturl, '?action=moderate;area=postmod;sa=posts;sa=', $context['current_view'], ';start=', $context['start'], ';sesc=', $context['session_id'], ';delete=', $item['id'], '">', $remove_button, '</a>';

			echo '
					<input type="checkbox" name="item[]" value="', $item['id'], '" checked="checked" class="check" /> ';

			echo '
							</span></td>
						</tr>
					</table>
				</td>
			</tr>
		</table>';
		$alternate = !$alternate;
	}

	echo '
		<table width="100%" cellpadding="3" cellspacing="1" border="0" class="bordercolor">
			<tr class="titlebg">
				<td align="right">
					<select name="do" onchange="if (this.value != 0 && confirm(\'', $txt['mc_unapproved_sure'], '\')) submit();">
						<option value="0">', $txt['with_selected'], ':</option>
						<option value="0">-------------------</option>
						<option value="approve">&nbsp;--&nbsp;', $txt['approve'], '</option>
						<option value="delete">&nbsp;--&nbsp;', $txt['delete'], '</option>
					</select>
					<noscript><input type="submit" name="submit" value="', $txt['go'], '" /></noscript>
				</td>
			</tr>
		</table>
		<input type="hidden" name="sc" value="', $context['session_id'], '" />
	</form>';
}

// List all attachments awaiting approval.
function template_unapproved_attachments()
{
	global $settings, $options, $context, $txt, $scripturl;

	// Show all the attachments still oustanding.
	echo '
	<form action="', $scripturl, '?action=moderate;area=attachmod;sa=attachments;start=', $context['start'], '" method="post" accept-charset="', $context['character_set'], '">
		<table width="100%" cellpadding="3" cellspacing="1" border="0" class="bordercolor">
			<tr class="titlebg">
				<td colspan="5">', $txt['mc_unapproved_attachments'], '</td>
			</tr>';

	// The ever popular approve button, with the massively unpopular delete.
	$approve_button = create_button('approve.gif', 'approve', 'approve', 'align="middle"');
	$remove_button = create_button('delete.gif', 'remove_message', 'remove', 'align="middle"');

	// None awaiting?
	if (empty($context['unapproved_items']))
		echo '
			<tr class="windowbg">
				<td colspan="5" align="center">', $txt['mc_unapproved_attachments_none_found'], '</td>
			</tr>';
	else
		echo '
			<tr class="catbg">
				<td colspan="5">', $txt['pages'], ': ', $context['page_index'], '</td>
			</tr>
			<tr class="titlebg">
				<td>', $txt['mc_unapproved_attach_name'], '</td>
				<td>', $txt['mc_unapproved_attach_size'], '</td>
				<td>', $txt['mc_unapproved_attach_poster'], '</td>
				<td>', $txt['date'], '</td>
				<td nowrap="nowrap" align="center"><input type="checkbox" onclick="invertAll(this, this.form);" class="check" checked="checked" /></td>
			</tr>';

	$alternate = 0;
	foreach ($context['unapproved_items'] as $item)
	{
		echo '
			<tr class="', $alternate ? 'windowbg' : 'windowbg2' , '">
				<td>
					', $item['filename'], '
				</td>
				<td align="right">
					', $item['size'], 'kB
				</td>
				<td>
					', $item['poster']['link'], '
				</td>
				<td class="smalltext">
					', $item['time'], '<br />', $txt['in'], ' <a href="', $item['message']['href'], '">', $item['message']['subject'], '</a>
				</td>
				<td width="4%" align="center">
					<input type="checkbox" name="item[]" value="', $item['id'], '" checked="checked" class="check" />
				</td>
			</tr>';

		$alternate = !$alternate;
	}

	echo '
			<tr class="titlebg">
				<td colspan="5" align="right">
					<select name="do" onchange="if (this.value != 0 && confirm(\'', $txt['mc_unapproved_sure'], '\')) submit();">
						<option value="0">', $txt['with_selected'], ':</option>
						<option value="0">-------------------</option>
						<option value="approve">&nbsp;--&nbsp;', $txt['approve'], '</option>
						<option value="delete">&nbsp;--&nbsp;', $txt['delete'], '</option>
					</select>
					<noscript><input type="submit" name="submit" value="', $txt['go'], '" /></noscript>
				</td>
			</tr>
		</table>
		<input type="hidden" name="sc" value="', $context['session_id'], '" />
	</form>';
}

function template_viewmodreport()
{
	global $context, $scripturl, $txt;
	echo '
	<table width="100%" cellpadding="3" cellspacing="1" border="0" class="bordercolor">
		<tr class="catbg">
			<td colspan="2">
				', sprintf($txt['mc_viewmodreport'], $context['report']['message_link'], $context['report']['author']['link']), '
			</td>
		</tr><tr class="windowbg">
			<td>
				', sprintf($txt['mc_modreport_summary'], $context['report']['num_reports'], $context['report']['last_updated']), '
			</td>
			<td align="right">';
		
	// Make the buttons.
	$close_button = create_button('close.gif', $context['report']['closed'] ? 'mc_reportedp_open' : 'mc_reportedp_close', $context['report']['closed'] ? 'mc_reportedp_open' : 'mc_reportedp_close', 'align="middle"');
	$ignore_button = create_button('ignore.gif', 'mc_reportedp_ignore', 'mc_reportedp_ignore', 'align="middle"');
	$unignore_button = create_button('ignore.gif', 'mc_reportedp_unignore', 'mc_reportedp_unignore', 'align="middle"');

	echo '
				<a href="', $scripturl, '?action=moderate;area=reports;ignore=', !$context['report']['ignore'], ';rid=', $context['report']['id'], ';sesc=', $context['session_id'], '" ', !$context['report']['ignore'] ? 'onclick="return confirm(\'' . $txt['mc_reportedp_ignore_confirm'] . '\');"' : '', '>', $context['report']['ignore'] ? $unignore_button : $ignore_button, '</a>
				<a href="', $scripturl, '?action=moderate;area=reports;close=', !$context['report']['closed'], ';rid=', $context['report']['id'], ';sesc=', $context['session_id'], '">', $close_button, '</a>
			</td>
		</tr><tr class="windowbg2">
			<td colspan="2">
				', $context['report']['body'], '
			</td>
		</tr>
	</table><br />
	<table width="100%" cellpadding="3" cellspacing="1" border="0" class="bordercolor">
		<tr class="catbg">
			<td>', $txt['mc_modreport_whoreported_title'], '</td>
		</tr>';
	$alt = false;

	foreach($context['report']['comments'] AS $comment)
	{
		echo '<tr class="', $alt ? 'windowbg2' : 'windowbg', '">
			<td>
				', sprintf($txt['mc_modreport_whoreported_data'], $comment['member']['link'], $comment['time']), '<br />', $comment['message'], '
			</td>
		</tr>';
	}
	echo '
	</table><br />';

	$alt = false;
	
	if (!empty($context['entries']))
	{
		echo '
	<table width="100%" cellpadding="3" cellspacing="1" border="0" class="bordercolor">
		<tr class="catbg">
			<td colspan="5">
				', $txt['mc_modreport_modactions'], '
			</td>
		</tr><tr class="titlebg">
			<td>', $txt['modlog_action'], '</td>
			<td>', $txt['modlog_date'], '</td>
			<td>', $txt['modlog_member'], '</td>
			<td>', $txt['modlog_position'], '</td>
			<td>', $txt['modlog_ip'], '</td>
		</tr>';

		foreach($context['entries'] AS $entry)
		{
			echo '
		<tr class="', $alt ? 'windowbg2' : 'windowbg', '">
			<td>', $entry['action'], '</td>
			<td>', $entry['time'], '</td>
			<td>', $entry['moderator']['link'], '</td>
			<td>', $entry['position'], '</td>
			<td>', $entry['ip'], '</td>
		</tr><tr>
			<td colspan="5" class="', $alt ? 'windowbg2' : 'windowbg', '">';

			foreach ($entry['extra'] as $key => $value)
				echo '
				<i>', $key, '</i>: ', $value;
			echo '
			</td>
		</tr>';
		}
		echo '
	</table>';
	}
}

// View members who are being watched
function template_user_watches_member()
{
	global $settings, $options, $context, $txt, $scripturl;

	echo '
		<table width="100%" cellpadding="3" cellspacing="1" border="0" class="bordercolor">
			<tr class="titlebg">
				<td colspan="5">', $txt['mc_watched_users_title'], ' - ', $txt['mc_watched_users_member'], '</td>
			</tr><tr class="catbg">
				<td width="20%">', $txt['name'], '</td>
				<td width="18%" align="center">', $txt['mc_watched_users_warning'], '</td>
				<td width="8%" align="center">', $txt['posts'], '</td>
				<td width="25%">', $txt['mc_watched_users_last_login'], '</td>
				<td width="25%">', $txt['mc_watched_users_last_post'], '</td>
			</tr>';

	// Loop through each member.
	$alternate = 0;
	foreach ($context['member_watches'] as $member)
	{
		echo '
			<tr class="', $alternate ? 'windowbg' : 'windowbg2', '">
				<td><a href="', $scripturl, '?action=profile;u=', $member['id'], '">', $member['name'], '</a></td>
				<td align="center">', ($context['can_issue_warnings'] ? '<a href="' . $scripturl . '?action=profile;u=' . $member['id'] . ';sa=issueWarning">' . $member['warning'] . '%</a>' : $member['warning'] . '%'), '</td>
				<td align="center"><a href="', $scripturl, '?action=profile;u=', $member['id'], ';sa=showPosts">', $member['posts'], '</a></td>
				<td>', $member['last_login'], '</td>
				<td>';

		if ($member['last_post_id'])
			echo '
					<a href="', $scripturl, '?msg=', $member['last_post_id'], '">', $member['last_post'], '</a>';
		else
			echo '
				', $member['last_post'];

		echo '
				</td>
			</tr>';
		$alternate = !$alternate;
	}

	// Were none found?
	if (empty($context['member_watches']))
		echo '
			<tr class="windowbg">
				<td colspan="5" align="center">', $txt['mc_watched_users_none'], '</td>
			</tr>';

	echo '
			<tr class="catbg">
				<td colspan="5">
					<div style="float: left;">
						', $txt['pages'], ': ', $context['page_index'], '
					</div>
				</td>
			</tr>
		</table>';
}

// Viewing user watches - by posts.
function template_user_watches_posts()
{
	global $settings, $options, $context, $txt, $scripturl;

	echo '
	<form action="', $scripturl, '?action=moderate;area=userwatch;sa=post;start=', $context['start'], '" method="post" accept-charset="', $context['character_set'], '" onsubmit="return confirm(\'', $txt['mc_watched_users_delete_posts'], '\');">
		<table width="100%" cellpadding="3" cellspacing="1" border="0" class="bordercolor">
			<tr class="titlebg">
				<td>', $txt['mc_watched_users_title'], ' - ', $txt['mc_watched_users_member'], '</td>
			</tr><tr class="catbg">
				<td>', $txt['pages'], ': ', $context['page_index'], '</td>
			</tr>';


	// We'll have a delete please bob.
	$delete_button = create_button('delete.gif', 'remove_message', 'remove', 'align="middle"');

	// Do the posts!
	$alternate = 0;
	foreach ($context['member_posts'] as $post)
	{
		echo '
			<tr class="', $post['approved'] ? ($alternate ? 'windowbg' : 'windowbg2') : 'approvebg', '">
				<td>
					<div>
						<div style="float: left">
							<b><a href="', $scripturl, '?topic=', $post['id_topic'], '.', $post['id'], '#msg', $post['id'], '">', $post['subject'], '</a></b> ', $txt['mc_reportedp_by'], ' <b>', $post['author']['link'], '</b>
						</div>
						<div style="float: right">
							<a href="', $scripturl, '?action=moderate;area=userwatch;sa=post;delete=', $post['id'], ';start=', $context['start'], ';sesc=', $context['session_id'], '" onclick="return confirm(\'' . $txt['mc_watched_users_delete_post'] . '\');">', $delete_button, '</a>
							<input type="checkbox" name="delete[]" value="', $post['id'], '" class="check" />
						</div>
					</div><br />
					<div class="smalltext">
						&#171; ', $txt['mc_watched_users_posted'], ': ', $post['poster_time'], ' &#187;
					</div>
					<hr />
					', $post['body'], '
				</td>
			</tr>';
		$alternate = !$alternate;
	}

	// No posts?
	if (empty($context['member_posts']))
		echo '
			<tr class="windowbg">
				<td align="center">', $txt['mc_watched_users_no_posts'], '</td>
			</tr>';

	echo '
			<tr class="catbg">
				<td>
					<div style="float: left;">
						', $txt['pages'], ': ', $context['page_index'], '
					</div>
					<div style="float: right;">
						<input type="submit" name="delete_selected" value="', $txt['quickmod_delete_selected'], '" />
					</div>
				</td>
			</tr>
		</table>
		<input type="hidden" name="sc" value="', $context['session_id'], '" />
	</form>';
}

// A record of all warnings issued to members.
function template_warning_log()
{
	global $settings, $options, $context, $txt, $scripturl;

	echo '
		<table width="100%" cellpadding="3" cellspacing="1" border="0" class="bordercolor">
			<tr class="titlebg">
				<td colspan="5">', $txt['mc_warning_log'], '</td>
			</tr><tr class="catbg">
				<td width="18%">', $txt['profile_warning_previous_issued'], '</td>
				<td width="18%">', $txt['mc_warnings_recipient'], '</td>
				<td width="25%">', $txt['profile_warning_previous_time'], '</td>
				<td width="30%">', $txt['profile_warning_previous_reason'], '</td>
				<td width="10%">', $txt['profile_warning_previous_level'], '</td>
			</tr>';

	// Show each warning.
	$alternate = 0;
	foreach ($context['warnings'] as $warning)
	{
		echo '
			<tr class="', $alternate ? 'windowbg' : 'windowbg2', '">
				<td class="smalltext">', $warning['issuer']['link'], '</td>
				<td class="smalltext">', $warning['recipient']['link'], '</td>
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
		$alternate = !$alternate;
	}

	// No warnings yet?
	if (empty($context['warnings']))
		echo '
			<tr class="windowbg">
				<td colspan="5" align="center">', $txt['mc_warnings_none'], '</td>
			</tr>';

	echo '
			<tr class="catbg">
				<td colspan="5">
					<div style="float: left;">
						', $txt['pages'], ': ', $context['page_index'], '
					</div>
				</td>
			</tr>
		</table>';
}

// Moderation settings
function template_moderation_settings()
{
	global $settings, $options, $context, $txt, $scripturl;

	echo '
	<form action="', $scripturl, '?action=moderate;area=settings" method="post" accept-charset="', $context['character_set'], '">
		<table width="80%" align="center" cellpadding="3" cellspacing="0" border="0" class="tborder">
			<tr class="titlebg">
				<td colspan="2">', $txt['mc_prefs_title'], '</td>
			</tr>
			<tr class="windowbg">
				<td colspan="2">
					<span class="smalltext">
						', $txt['mc_prefs_desc'], '
					</span>
				</td>
			</tr>
			<tr class="windowbg2" valign="top">
				<td width="50%">
					<b>', $txt['mc_prefs_homepage'], ':</b>
					<div class="smalltext">', $txt['mc_prefs_homepage_desc'], '</div>
				</td>
				<td width="50%">
					<select name="mod_homepage[]" multiple="multiple">';

	foreach ($context['homepage_blocks'] as $k => $v)
		echo '
						<option value="', $k, '" ', in_array($k, $context['mod_settings']['user_blocks']) ? 'selected="selected"' : '', '>', $v, '</option>';

	echo '
					</select>
				</td>
			</tr>';

	// If they can moderate boards they have more options!
	if ($context['can_moderate_boards'])
	{
		echo '
			<tr class="windowbg2" valign="top">
				<td width="50%">
					<b>', $txt['mc_prefs_show_reports'], ':</b>
				</td>
				<td width="50%">
					<input type="checkbox" name="mod_show_reports" ', $context['mod_settings']['show_reports'] ? 'checked="checked"' : '', ' class="check" />
				</td>
			</tr>
			<tr class="windowbg2" valign="top">
				<td width="50%">
					<b>', $txt['mc_prefs_notify_report'], ':</b>
				</td>
				<td width="50%">
					<select name="mod_notify_report">
						<option value="0" ', $context['mod_settings']['notify_report'] == 0 ? 'selected="selected"' : '', '>', $txt['mc_prefs_notify_report_never'], '</option>
						<option value="1" ', $context['mod_settings']['notify_report'] == 1 ? 'selected="selected"' : '', '>', $txt['mc_prefs_notify_report_moderator'], '</option>
						<option value="2" ', $context['mod_settings']['notify_report'] == 2 ? 'selected="selected"' : '', '>', $txt['mc_prefs_notify_report_always'], '</option>
					</select>
				</td>
			</tr>
			<tr class="windowbg2" valign="top">
				<td width="50%">
					<b>', $txt['mc_prefs_notify_approval'], ':</b>
				</td>
				<td width="50%">
					<input type="checkbox" name="mod_notify_approval" ', $context['mod_settings']['notify_approval'] ? 'checked="checked"' : '', ' class="check" />
				</td>
			</tr>';
	}
	echo '
			<tr class="windowbg">
				<td colspan="2" align="right">
					<input type="submit" name="save" value="', $txt['save'], '" />
				</td>
			</tr>
		</table>
	</form>';
}

?>