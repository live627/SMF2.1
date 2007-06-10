<?php
// Version: 2.0 Alpha; ManageMail

function template_browse()
{
	global $context, $settings, $options, $scripturl, $txt;

	echo '
	<table border="0" align="center" cellspacing="0" cellpadding="4" class="tborder" width="100%">
		<tr class="titlebg">
			<td colspan="2">', $txt['mailqueue_stats'], '</td>
		</tr>
		<tr class="windowbg">
			<td width="30%">', $txt['mailqueue_size'], ':</td>
			<td width="70%">', $context['mail_queue_size'], '</td>
		</tr>
		<tr class="windowbg">
			<td width="30%">', $txt['mailqueue_oldest'], ':</td>
			<td width="70%">', $context['oldest_mail'], '</td>
		</tr>
	</table>
	<br />';

	template_show_list('mail_queue');


/*	echo'
	<table border="0" align="center" cellspacing="1" cellpadding="4" class="bordercolor" width="100%">
		<tr class="titlebg">
			<td colspan="4">', $txt['mailqueue_browse'], '</td>
		</tr>
		<tr class="catbg">
			<td width="35%">', $txt['mailqueue_subject'], '</td>
			<td width="35%">', $txt['mailqueue_recipient'], '</td>
			<td width="5%">', $txt['mailqueue_priority'], '</td>
			<td width="25%">', $txt['mailqueue_age'], '</td>
		</tr>';

	$alternate = false;
	foreach ($context['mails'] as $mail)
	{
		echo '
		<tr class="', $alternate ? 'windowbg' : 'windowbg2', '">
			<td class="smalltext">', $mail['subject'], '</td>
			<td class="smalltext">', $mail['recipient'], '</td>
			<td class="smalltext">', $mail['priority_text'], '</td>
			<td class="smalltext">', $mail['age'], '</td>
		</tr>';
		$alternate = !$alternate;
	}
	echo '
		<tr class="titlebg">
			<td align="right" colspan="4">
				<a href="', $scripturl, '?action=admin;area=mailqueue;sa=clear;sesc=', $context['session_id'], '" onclick="return confirm(\'', $txt['mailqueue_clear_list_warning'], '\');">', $txt['mailqueue_clear_list'], '</a>
			</td>
		</tr>
		<tr class="catbg">
			<td align="left" colspan="4" style="padding: 5px;"><b>', $txt['pages'], ':</b> ', $context['page_index'], '</td>
		</tr>
	</table>';*/
}

?>