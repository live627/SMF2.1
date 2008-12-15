<?php
// Version: 2.0 RC1; Poll

function template_main()
{
	global $context, $settings, $options, $txt, $scripturl;

	// Some javascript for adding more options.
	echo '
		<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
			var pollOptionNum = 0;

			function addPollOption()
			{
				if (pollOptionNum == 0)
				{
					for (var i = 0; i < document.forms.postmodify.elements.length; i++)
						if (document.forms.postmodify.elements[i].id.substr(0, 8) == "options-")
							pollOptionNum++;
				}
				pollOptionNum++

				setOuterHTML(document.getElementById("pollMoreOptions"), \'<br /><label for="options-\' + pollOptionNum + \'" ', (isset($context['poll_error']['no_question']) ? ' class="error"' : ''), '>', $txt['option'], ' \' + pollOptionNum + \'</label>: <input type="text" name="options[\' + (pollOptionNum - 1) + \']" id="options-\' + (pollOptionNum - 1) + \'" value="" size="25" /><span id="pollMoreOptions"></span>\');
			}

			function saveEntities()
			{
				document.forms.postmodify.question.value = document.forms.postmodify.question.value.replace(/&#/g, "&#38;#");
				for (var i = 0; i < document.forms.postmodify.elements.length; i++)
					if (document.forms.postmodify.elements[i].id.substr(0, 8) == "options-")
						document.forms.postmodify[i].value = document.forms.postmodify[i].value.replace(/&#/g, "&#38;#");
			}
		// ]]></script>';

	// Start the main poll form.
	echo '
		<form action="' . $scripturl . '?action=editpoll2', $context['is_edit'] ? '' : ';add', ';topic=' . $context['current_topic'] . '.' . $context['start'] . '" method="post" accept-charset="', $context['character_set'], '" onsubmit="submitonce(this); saveEntities();" name="postmodify" id="postmodify">
			<table width="75%" align="center" cellpadding="3" cellspacing="0">
				<tr>
					<td valign="bottom" colspan="2">', theme_linktree(), '</td>
				</tr>
			</table>
			<table border="0"  width="75%" align="center" cellspacing="1" cellpadding="3" class="bordercolor">
				<tr class="titlebg">
					<td>' . $context['page_title'] . '</td>
				</tr><tr>
					<td class="windowbg">
						<input type="hidden" name="poll" value="' . $context['poll']['id'] . '" />
						<table border="0" cellpadding="3" width="100%">';

	if (!empty($context['poll_error']['messages']))
		echo '
							<tr>
								<td></td>
								<td align="left">
									<div style="padding: 0px; font-weight: bold;">
										', $context['is_edit'] ? $txt['error_while_editing_poll'] : $txt['error_while_adding_poll'], ':
									</div>
									<div class="error" style="margin: 1ex 0 2ex 3ex;">
										', empty($context['poll_error']['messages']) ? '' : implode('<br />', $context['poll_error']['messages']), '
									</div>
								</td>
							</tr>';

	echo '
							<tr>
								<td align="right" ', (isset($context['poll_error']['no_question']) ? ' class="error"' : ''), '><b>' . $txt['poll_question'] . ':</b></td>
								<td align="left"><input type="text" name="question" size="40" value="' . $context['poll']['question'] . '" /></td>
							</tr><tr>
								<td></td>
								<td>';

	foreach ($context['choices'] as $choice)
	{
		echo '
									<label for="options-', $choice['id'], '" ', (isset($context['poll_error']['poll_few']) ? ' class="error"' : ''), '>', $txt['option'], ' ', $choice['number'], '</label>: <input type="text" name="options[', $choice['id'], ']" id="options-', $choice['id'], '" size="25" value="', $choice['label'], '" />';

		// Does this option have a vote count yet, or is it new?
		if ($choice['votes'] != -1)
			echo ' (', $choice['votes'], ' ', $txt['votes'], ')';

		if (!$choice['is_last'])
			echo '<br />';
	}

	echo '
									<span id="pollMoreOptions"></span> <a href="javascript:addPollOption(); void(0);">(', $txt['poll_add_option'], ')</a>
								</td>
							</tr><tr>';

	if ($context['can_moderate_poll'])
	{
		echo '
								<td align="right"><b>', $txt['poll_options'], ':</b></td>
								<td class="smalltext"><input type="text" name="poll_max_votes" size="2" value="', $context['poll']['max_votes'], '" /> ', $txt['poll_max_votes'], '</td>
							</tr><tr>
								<td align="right"></td>
								<td class="smalltext">', $txt['poll_run'], ' <input type="text" name="poll_expire" size="2" value="', $context['poll']['expiration'], '" onchange="this.form.poll_hide[2].disabled = isEmptyText(this) || this.value == 0; if (this.form.poll_hide[2].checked) this.form.poll_hide[1].checked = true;" /> ', $txt['poll_run_days'], '</td>
							</tr><tr>
								<td align="right"></td>
								<td class="smalltext">
									<label for="poll_change_vote"><input type="checkbox" id="poll_change_vote" name="poll_change_vote"', !empty($context['poll']['change_vote']) ? ' checked="checked"' : '', ' class="check" /> ', $txt['poll_do_change_vote'], '</label>';

		if ($context['poll']['guest_vote_allowed'])
			echo '
									<br /><label for="poll_guest_vote"><input type="checkbox" id="poll_guest_vote" name="poll_guest_vote"', !empty($context['poll']['guest_vote']) ? ' checked="checked"' : '', ' class="check" /> ', $txt['poll_guest_vote'], '</label>';

		echo '
								</td>
							</tr><tr>
								<td align="right"></td>';
	}
	else
		echo '
								<td align="right" valign="top"><b>', $txt['poll_options'], ':</b></td>';

	echo '
								<td class="smalltext">
									<input type="radio" name="poll_hide" value="0"', $context['poll']['hide_results'] == 0 ? ' checked="checked"' : '', ' class="check" /> ' . $txt['poll_results_anyone'] . '<br />
									<input type="radio" name="poll_hide" value="1"', $context['poll']['hide_results'] == 1 ? ' checked="checked"' : '', ' class="check" /> ' . $txt['poll_results_voted'] . '<br />
									<input type="radio" name="poll_hide" value="2"', $context['poll']['hide_results'] == 2 ? ' checked="checked"' : '', empty($context['poll']['expiration']) ? 'disabled="disabled"' : '', ' class="check" /> ' . $txt['poll_results_expire'] . '<br />
									<br />
								</td>';
	// If this is an edit, we can allow them to reset the vote counts.
	if ($context['is_edit'])
		echo '
							</tr><tr>
								<td align="right"><b>' . $txt['reset_votes'] . ':</b></td>
								<td class="smalltext"><input type="checkbox" name="resetVoteCount" value="on" class="check" /> ' . $txt['reset_votes_check'] . '</td>';
	echo '
							</tr><tr>
								<td align="center" colspan="2">
									<input type="submit" name="post" value="', $txt['save'],  '" onclick="return submitThisOnce(this);" accesskey="s" />
								</td>
							</tr><tr>
								<td colspan="2"></td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
			<input type="hidden" name="seqnum" value="', $context['form_sequence_number'], '" />
			<input type="hidden" name="sc" value="' . $context['session_id'] . '" />
		</form>';
}

?>