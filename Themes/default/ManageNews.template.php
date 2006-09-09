<?php
// Version: 2.0 Alpha; ManageNews

// Form for editing current news on the site.
function template_edit_news()
{
	global $context, $settings, $options, $scripturl, $txt;

	echo '
		<form action="', $scripturl, '?action=admin;area=news;sa=editnews" method="post" accept-charset="', $context['character_set'], '" name="postmodify" id="postmodify">
			<table width="85%" cellpadding="3" cellspacing="0" border="0" align="center" class="tborder">
				<tr class="titlebg">
					<th width="50%"></th>
					<th align="left" width="45%">', $txt[507], '</th>
					<th align="center" width="5%"><input type="checkbox" class="check" onclick="invertAll(this, this.form);" /></th>
				</tr>';

	// Loop through all the current news items so you can edit/remove them.
	foreach ($context['admin_current_news'] as $admin_news)
		echo '
				<tr class="windowbg2">
					<td align="center">
						<div style="margin-bottom: 2ex;"><textarea rows="3" cols="65" name="news[]" style="width: 85%;">', $admin_news['unparsed'], '</textarea></div>
					</td><td align="left" valign="top">
						<div style="overflow: auto; width: 100%; height: 10ex;">', $admin_news['parsed'], '</div>
					</td><td align="center">
						<input type="checkbox" name="remove[]" value="', $admin_news['id'], '" class="check" />
					</td>
				</tr>';

	// This provides an empty text box to add a news item to the site.
	echo '
				<tr class="windowbg2">
					<td align="center">
						<div id="moreNewsItems"></div><div id="moreNewsItems_link" style="display: none;"><a href="javascript:void(0);" onclick="addNewsItem(); return false;">', $txt['editnews_clickadd'], '</a></div>
						<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
							document.getElementById("moreNewsItems_link").style.display = "";

							function addNewsItem()
							{
								setOuterHTML(document.getElementById("moreNewsItems"), \'<div style="margin-bottom: 2ex;"><textarea rows="3" cols="65" name="news[]" style="width: 85%;"></textarea></div><div id="moreNewsItems"></div>\');
							}
						// ]]></script>
						<noscript>
							<div style="margin-bottom: 2ex;"><textarea rows="3" cols="65" style="width: 85%;" name="news[]"></textarea></div>
						</noscript>
					</td>
					<td colspan="2" valign="bottom" align="right" style="padding: 1ex;">
						<input type="submit" name="save_items" value="', $txt['save'], '" /> <input type="submit" name="delete_selection" value="', $txt['editnews_remove_selected'], '" onclick="return confirm(\'', $txt['editnews_remove_confirm'], '\');" />
					</td>
				</tr>
			</table>
			<input type="hidden" name="sc" value="', $context['session_id'], '" />
		</form>';
}

function template_email_members()
{
	global $context, $settings, $options, $txt, $scripturl;

	echo '
		<form action="', $scripturl, '?action=admin;area=news;sa=mailingcompose" method="post" accept-charset="', $context['character_set'], '">
			<table width="600" cellpadding="5" cellspacing="0" border="0" align="center" class="tborder">
				<tr class="titlebg">
					<td>', $txt['admin_newsletters'], '</td>
				</tr><tr class="windowbg">
					<td class="smalltext" style="padding: 2ex;">', $txt['smf250'], '</td>
				</tr><tr>
					<td class="windowbg2">';

	foreach ($context['groups'] as $group)
				echo '
						<label for="who[', $group['id'], ']"><input type="checkbox" name="who[', $group['id'], ']" id="who[', $group['id'], ']" value="', $group['id'], '" checked="checked" class="check" /> ', $group['name'], '</label> <i>(', $group['member_count'], ')</i><br />';

	echo '
						<br />
						<label for="checkAllGroups"><input type="checkbox" id="checkAllGroups" checked="checked" onclick="invertAll(this, this.form, \'who\');" class="check" /> <i>', $txt[737], '</i></label><br />

						<hr />
					</td>
				</tr><tr>
					<td class="windowbg2">';

	if ($context['can_send_pm'])
		echo '
					<label for="sendPM"><input type="checkbox" name="sendPM" id="sendPM" value="1" class="check" /> ', $txt['email_as_pms'], '</label><br />';

	echo '
						<label for="email_force"><input type="checkbox" name="email_force" id="email_force" value="1" class="check" /> ', $txt['email_force'], '</label>
					</td>
				</tr><tr>
					<td class="windowbg2" style="padding-bottom: 1ex;" align="center">
						<input type="submit" value="', $txt[65], '" />
					</td>
				</tr>
			</table>
			<input type="hidden" name="sc" value="', $context['session_id'], '" />
		</form>';
}

function template_email_members_compose()
{
	global $context, $settings, $options, $txt, $scripturl;

	echo '
		<form action="', $scripturl, '?action=admin;area=news;sa=mailingsend" method="post" accept-charset="', $context['character_set'], '">
			<table width="600" cellpadding="4" cellspacing="0" border="0" align="center" class="tborder">
				<tr class="titlebg">
					<td>
						<a href="', $scripturl, '?action=helpadmin;help=email_members" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt['help'], '" align="top" /></a> ', $txt['admin_newsletters'], '
					</td>
				</tr><tr class="windowbg">
					<td class="smalltext" style="padding: 2ex;">', $txt[735], '</td>
				</tr><tr>
					<td class="windowbg2" align="center">
						<textarea cols="70" rows="7" name="emails" class="editor">', $context['addresses'], '</textarea>
					</td>
				</tr>
			</table>
			<br />
			<table width="600" cellpadding="5" cellspacing="0" border="0" align="center" class="tborder">
				<tr class="titlebg">
					<td>', $txt[338], '</td>
				</tr><tr class="windowbg">
					<td class="smalltext" style="padding: 2ex;">', $txt['email_variables'], '</td>
				</tr><tr>
					<td class="windowbg2">
						<input type="text" name="subject" size="60" value="', $context['default_subject'], '" /><br />
						<br />
						<textarea cols="70" rows="9" name="message" class="editor">', $context['default_message'], '</textarea><br />
						<br />
						<label for="send_html"><input type="checkbox" name="send_html" id="send_html" class="check" onclick="this.form.parse_html.disabled = !this.checked;" /> ', $txt['email_as_html'], '</label><br />
						<label for="parse_html"><input type="checkbox" name="parse_html" id="parse_html" checked="checked" disabled="disabled" class="check" /> ', $txt['email_parsed_html'], '</label><br />
						<br />
						<div align="center"><input type="submit" value="', $txt['sendtopic_send'], '" /></div>
					</td>
				</tr>
			</table>
			<input type="hidden" name="sc" value="', $context['session_id'], '" />
		</form>';
}

function template_email_members_send()
{
	global $context, $settings, $options, $txt, $scripturl;

	echo '
		<form action="', $scripturl, '?action=admin;area=news;sa=mailingsend" method="post" accept-charset="', $context['character_set'], '" name="autoSubmit" id="autoSubmit">
			<table width="600" cellpadding="4" cellspacing="0" border="0" align="center" class="tborder">
				<tr class="titlebg">
					<td>
						<a href="', $scripturl, '?action=helpadmin;help=email_members" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt['help'], '" align="top" /></a> ', $txt['admin_newsletters'], '
					</td>
				</tr><tr>
					<td class="windowbg2"><b>', $context['percentage_done'], '% ', $txt['email_done'], '</b></td>
				</tr><tr>
					<td class="windowbg2" style="padding-bottom: 1ex;" align="center">
						<input type="submit" name="b" value="', $txt['email_continue'], '" />
					</td>
				</tr>
			</table>
			<input type="hidden" name="sc" value="', $context['session_id'], '" />
			<input type="hidden" name="emails" value="', $context['emails'], '" />
			<input type="hidden" name="subject" value="', $context['subject'], '" />
			<input type="hidden" name="message" value="', $context['message'], '" />
			<input type="hidden" name="start" value="', $context['start'], '" />
			<input type="hidden" name="send_html" value="', $context['send_html'], '" />
			<input type="hidden" name="parse_html" value="', $context['parse_html'], '" />
		</form>
		<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
			var countdown = 2;
			doAutoSubmit();

			function doAutoSubmit()
			{
				if (countdown == 0)
					document.forms.autoSubmit.submit();
				else if (countdown == -1)
					return;

				document.forms.autoSubmit.b.value = "', $txt['email_continue'], ' (" + countdown + ")";
				countdown--;

				setTimeout("doAutoSubmit();", 1000);
			}
		// ]]></script>';
}

?>