<?php
// Version: 2.0 Beta 2.1; ManageMaintenance

// Template for forum maintenance page.
function template_maintain()
{
	global $context, $settings, $options, $txt, $scripturl, $modSettings;

	// If maintenance has finished tell the user.
	if ($context['maintenance_finished'])
		echo '
			<div class="windowbg" style="margin: 1ex; padding: 1ex 2ex; border: 1px dashed green; color: green;">
				', $txt['maintain_done'], '
			</div>';

	// Starts off with general maintenance procedures.
	echo '
		<table width="100%" cellpadding="4" cellspacing="1" border="0" class="bordercolor">
			<tr class="titlebg">
				<td><a href="', $scripturl, '?action=helpadmin;help=maintenance_general" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt['help'], '" align="top" /></a> ', $txt['maintain_common'], '</td>
			</tr>
			<tr>
				<td class="windowbg2" style="line-height: 1.3; padding-bottom: 2ex;">
					', $txt['maintain_common_task_database'], ':
					<ul style="margin-top: 0px; margin-bottom: 0px;">
						<li>
							<a href="', $scripturl, '?action=admin;area=maintain;sa=optimize;sesc=', $context['session_id'], '">', $txt['maintain_optimize'], '</a><br />
						</li>
						<li>
							<a href="', $scripturl, '?action=admin;area=maintain;sa=admintask;activity=maintain_backup;sesc=', $context['session_id'], '">', $txt['maintain_backup'], '</a><br />
						</li>
					</ul>
					', $txt['maintain_common_task_routine'], ':
					<ul style="margin-top: 0px; margin-bottom: 0px;">
						<li>
							<a href="', $scripturl, '?action=admin;area=maintain;sa=version">', $txt['maintain_version'], '</a><br />
						</li>
						<li>
							<a href="', $scripturl, '?action=admin;area=repairboards">', $txt['maintain_errors'], '</a><br />
						</li>
						<li>
							<a href="', $scripturl, '?action=admin;area=maintain;sa=recount;sesc=', $context['session_id'], '">', $txt['maintain_recount'], '</a><br />
						</li>
					</ul>
					', $txt['maintain_common_task_members'], ':
					<ul style="margin-top: 0px; margin-bottom: 0px;">
						<li>
							<a href="', $scripturl, '?action=admin;area=maintain;sa=admintask;activity=maintain_members;sesc=', $context['session_id'], '">', $txt['maintain_members'], '</a><br />
						</li>
						<li>
							<a href="', $scripturl, '?action=admin;area=maintain;sa=admintask;activity=maintain_reattribute_posts;sesc=', $context['session_id'], '">', $txt['maintain_reattribute_posts'], '</a><br />
						</li>
					</ul>
					', $txt['maintain_common_task_topics'], ':
					<ul style="margin-top: 0px; margin-bottom: 0px;">
						<li>
							<a href="', $scripturl, '?action=admin;area=maintain;sa=admintask;activity=maintain_old;sesc=', $context['session_id'], '">', $txt['maintain_old'], '</a><br />
						</li>
						<li>
							<a href="', $scripturl, '?action=admin;area=maintain;sa=admintask;activity=move_topics_maintenance;sesc=', $context['session_id'], '">', $txt['move_topics_maintenance'], '</a><br />
						</li>
					</ul>
					', $txt['maintain_common_task_misc'], ':
					<ul style="margin-top: 0px; margin-bottom: 0px;">
						<li>
							<a href="', $scripturl, '?action=admin;area=maintain;sa=logs;sesc=', $context['session_id'], '">', $txt['maintain_logs'], '</a><br />', $context['convert_utf8'] ? '
						</li>
						<li>
							<a href="' . $scripturl . '?action=admin;area=maintain;sa=convertutf8">' . $txt['utf8_title'] . '</a><br />' : '', $context['convert_entities'] ? '
						</li>
						<li>
							<a href="' . $scripturl . '?action=admin;area=maintain;sa=convertentities">' . $txt['entity_convert_title'] . '</a><br />' : '', '
						</li>
						<li>
							<a href="', $scripturl, '?action=admin;area=maintain;sa=cleancache">', $txt['maintain_cache'], '</a><br />
						</li>
					</ul>
				</td>
			</tr>
		</table>';
}

// These layers are for maintenance tasks to make them all look samey.
function template_maintain_above()
{
	global $scripturl, $txt, $context, $settings;

	echo '
	<form action="', $scripturl, '?action=admin;area=maintain;sa=admintask;activity=', $context['maintain_activity'], '" method="post" id="maintain_form" accept-charset="', $context['character_set'], '">
		<table align="center" width="80%" cellpadding="4" cellspacing="1" border="0" class="bordercolor">
			<tr class="titlebg">
				<td>';
	if ($context['help_text'])
		echo '
					<a href="', $scripturl, '?action=helpadmin;help=', $context['help_text'], '" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt['help'], '" align="top" /></a>';

	echo '
					', $context['page_title'], '
				</td>
			</tr>
			<tr class="windowbg">
				<td>';
}

function template_maintain_below()
{
	global $context;

	echo '
				</td>
			</tr>
		</table>
		<input type="hidden" name="do" value="1" />
		<input type="hidden" name="sc" value="', $context['session_id'], '" />
	</form>';
}

// Shall we reattribute some posts to previous LOSERS!
function template_activity_maintain_reattribute_posts()
{
	global $context, $settings, $options, $txt, $scripturl;

	// This will test validity and keep the warning message right.
	echo '
	<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
		var warningMessage = \'\';
		function checkAttributeValidity()
		{
			origText = \'', $txt['reattribute_confirm'], '\';
			valid = true;

			// Do all the fields!
			if (!document.getElementById(\'to\').value)
				valid = false;
			warningMessage = origText.replace(/%member_to%/, document.getElementById(\'to\').value);

			if (document.getElementById(\'type_email\').checked)
			{
				if (!document.getElementById(\'from_email\').value)
					valid = false;
				warningMessage = warningMessage.replace(/%type%/, \'', $txt['reattribute_confirm_email'], '\').replace(/%find%/, document.getElementById(\'from_email\').value);
			}
			else
			{
				if (!document.getElementById(\'from_name\').value)
					valid = false;
				warningMessage = warningMessage.replace(/%type%/, \'', $txt['reattribute_confirm_username'], '\').replace(/%find%/, document.getElementById(\'from_name\').value);
			}

			document.getElementById(\'do_attribute\').disabled = valid ? \'\' : \'disabled\';

			setTimeout("checkAttributeValidity();", 500);
			return valid;
		}
		setTimeout("checkAttributeValidity();", 500);
	// ]]></script>';

	echo '
	', $txt['reattribute_guest_posts'], ':<br />
	&nbsp;&nbsp;<label for="type_email"><input type="radio" name="type" id="type_email" value="email" checked="checked" class="check" />', $txt['reattribute_email'], '&nbsp;<input type="text" name="from_email" id="from_email" value="" onclick="document.getElementById(\'type_email\').checked = \'checked\'; document.getElementById(\'from_name\').value = \'\';" /><br />
	&nbsp;&nbsp;<label for="type_name"><input type="radio" name="type" id="type_name" value="name" class="check" />', $txt['reattribute_username'], '&nbsp;<input type="text" name="from_name" id="from_name" value="" onclick="document.getElementById(\'type_name\').checked = \'checked\'; document.getElementById(\'from_email\').value = \'\';"" /><br />
	<label for="posts"><input type="checkbox" name="posts" id="posts" checked="checked" class="check" />', $txt['reattribute_increase_posts'], '</label><br />
	<br />', $txt['reattribute_current_member'], ': <input type="text" name="to" id="to" value="" /> <a href="', $scripturl, '?action=findmember;input=to;sesc=', $context['session_id'], '" onclick="return reqWin(this.href, 350, 400);"><img src="', $settings['images_url'], '/icons/assist.gif" alt="', $txt['find_members'], '" align="middle" /> ', $txt['find_members'], '</a>

	<div align="right" style="margin: 1ex;"><input type="submit" id="do_attribute" value="', $txt['reattribute'], '" onclick="if (!checkAttributeValidity()) return false; return confirm(warningMessage);" /></div>';
}

// Bit of backup love?
function template_activity_maintain_backup()
{
	global $context, $settings, $options, $txt, $scripturl;

	echo '
	<label for="struct"><input type="checkbox" name="struct" id="struct" onclick="document.getElementById(\'submitDump\').disabled = !document.getElementById(\'struct\').checked &amp;&amp; !document.getElementById(\'data\').checked;" class="check" /> ', $txt['maintain_backup_struct'], '</label><br />
	<label for="data"><input type="checkbox" name="data" id="data" onclick="document.getElementById(\'submitDump\').disabled = !document.getElementById(\'struct\').checked &amp;&amp; !document.getElementById(\'data\').checked;" checked="checked" class="check" /> ', $txt['maintain_backup_data'], '</label><br />
	<br />
	<label for="compress"><input type="checkbox" name="compress" id="compress" value="gzip" checked="checked" class="check" /> ', $txt['maintain_backup_gz'], '</label>
	<div align="right" style="margin: 1ex;"><input type="submit" id="submitDump" value="', $txt['maintain_backup_save'], '" onclick="return document.getElementById(\'struct\').checked || document.getElementById(\'data\').checked;" /></div>';
}

// Pruning old members?
function template_activity_maintain_members()
{
	global $scripturl, $txt, $context, $settings;

	echo '
		<a name="membersLink"></a>';

	// Simple javascript for showing and hiding membergroups.
	echo '
		<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
			var membersSwap = false;
			function swapMembers()
			{
				membersSwap = !membersSwap;
				membersForm = document.getElementById(\'maintain_form\');

				document.getElementById("membersIcon").src = smf_images_url + (membersSwap ? "/collapse.gif" : "/expand.gif");
				setInnerHTML(document.getElementById("membersText"), membersSwap ? "', $txt['maintain_members_choose'], '" : "', $txt['maintain_members_all'], '");
				document.getElementById("membersPanel").style.display = (membersSwap ? "block" : "none");

				for (var i = 0; i < membersForm.length; i++)
				{
					if (membersForm.elements[i].type.toLowerCase() == "checkbox")
						membersForm.elements[i].checked = !membersSwap;
				}
			}
		// ]]></script>';

	// Give them the options.
	echo '
		', $txt['maintain_members_since1'], '
		<select name="del_type">
			<option value="activated" selected="selected">', $txt['maintain_members_activated'], '</option>
			<option value="logged">', $txt['maintain_members_logged_in'], '</option>
		</select>', $txt['maintain_members_since2'], ' <input type="text" name="maxdays" value="30" size="3" />', $txt['maintain_members_since3'], '<br />';

	echo '
		<a href="#membersLink" onclick="swapMembers();"><img src="', $settings['images_url'], '/expand.gif" alt="+" id="membersIcon" /></a> <a href="#membersLink" onclick="swapMembers();"><span id="membersText" style="font-weight: bold;">', $txt['maintain_members_all'], '</span></a>
		<div style="display: none;" id="membersPanel">
			<table width="100%" cellpadding="3" cellspacing="0" border="0">
				<tr>
					<td valign="top">';

	$i = 0;
	foreach ($context['membergroups'] as $group)
	{
		echo '
						<label for="groups[', $group['id'], ']"><input type="checkbox" name="groups[', $group['id'], ']" id="groups[', $group['id'], ']" checked="checked" class="check" /> ', $group['name'], '</label><br />';
	}

	echo '
					</td>
				</tr>
			</table>
		</div>

		<div align="right" style="margin: 1ex;"><input type="submit" value="', $txt['maintain_old_remove'], '" onclick="return confirm(\'', $txt['maintain_members_confirm'], '\');" /></div>';
}

// Remove old board posts?
function template_activity_maintain_old()
{
	global $scripturl, $txt, $context, $settings;

	// Pruning any older posts.
	echo '
		<a name="rotLink"></a>';

	// Bit of javascript for showing which boards to prune in an otherwise hidden list.
	echo '
		<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
			var rotSwap = false;
			function swapRot()
			{
				rotSwap = !rotSwap;
				rotForm = document.getElementById(\'maintain_form\');

				document.getElementById("rotIcon").src = smf_images_url + (rotSwap ? "/collapse.gif" : "/expand.gif");
				setInnerHTML(document.getElementById("rotText"), rotSwap ? "', $txt['maintain_old_choose'], '" : "', $txt['maintain_old_all'], '");
				document.getElementById("rotPanel").style.display = (rotSwap ? "block" : "none");

				for (var i = 0; i < rotForm.length; i++)
				{
					if (rotForm.elements[i].type.toLowerCase() == "checkbox" && rotForm.elements[i].id != "delete_old_not_sticky")
						rotForm.elements[i].checked = !rotSwap;
				}
			}
		// ]]></script>';

	// The otherwise hidden "choose which boards to prune".
	echo '
		', $txt['maintain_old_since_days1'], '<input type="text" name="maxdays" value="30" size="3" />', $txt['maintain_old_since_days2'], '<br />
		<div style="padding-left: 3ex;">
			<label for="delete_type_nothing"><input type="radio" name="delete_type" id="delete_type_nothing" value="nothing" class="check" checked="checked" /> ', $txt['maintain_old_nothing_else'], '</label><br />
			<label for="delete_type_moved"><input type="radio" name="delete_type" id="delete_type_moved" value="moved" class="check" /> ', $txt['maintain_old_are_moved'], '</label><br />
			<label for="delete_type_locked"><input type="radio" name="delete_type" id="delete_type_locked" value="locked" class="check" /> ', $txt['maintain_old_are_locked'], '</label><br />
		</div>';

	if (!empty($modSettings['enableStickyTopics']))
		echo '
		<div style="padding-left: 3ex; padding-top: 1ex;">
			<label for="delete_old_not_sticky"><input type="checkbox" name="delete_old_not_sticky" id="delete_old_not_sticky" class="check" checked="checked" /> ', $txt['maintain_old_are_not_stickied'], '</label><br />
		</div>';

	echo '
		<br />
		<a href="#rotLink" onclick="swapRot();"><img src="', $settings['images_url'], '/expand.gif" alt="+" id="rotIcon" /></a> <a href="#rotLink" onclick="swapRot();"><span id="rotText" style="font-weight: bold;">', $txt['maintain_old_all'], '</span></a>
		<div style="display: none;" id="rotPanel">
			<table width="100%" cellpadding="3" cellspacing="0" border="0">
				<tr>
					<td valign="top">';

	// This is the "middle" of the list.
	$middle = count($context['categories']) / 2;

	$i = 0;
	foreach ($context['categories'] as $category)
	{
		echo '
						<span style="text-decoration: underline;">', $category['name'], '</span><br />';

		// Display a checkbox with every board.
		foreach ($category['boards'] as $board)
			echo '
						<label for="boards_', $board['id'], '"><input type="checkbox" name="boards[', $board['id'], ']" id="boards_', $board['id'], '" checked="checked" class="check" /> ', str_repeat('&nbsp; ', $board['child_level']), $board['name'], '</label><br />';
		echo '
						<br />';

		// Increase $i, and check if we're at the middle yet.
		if (++$i == $middle)
			echo '
					</td>
					<td valign="top">';
	}

	echo '
					</td>
				</tr>
			</table>
		</div>

		<div align="right" style="margin: 1ex;"><input type="submit" value="', $txt['maintain_old_remove'], '" onclick="return confirm(\'', $txt['maintain_old_confirm'], '\');" /></div>';
}

// Move a board?
function template_activity_move_topics_maintenance()
{
	global $scripturl, $txt, $context;

	echo '
		<label for="id_board_from">', $txt['move_topics_from'], ' </label>
		<select name="id_board_from" id="id_board_from">
			<option disabled="disabled">(', $txt['move_topics_select_board'], ')</option>';
	// From boards.
	foreach ($context['categories'] as $category)
	{
		echo '
			<option disabled="disabled">--------------------------------------</option>
			<option disabled="disabled">', $category['name'], '</option>
			<option disabled="disabled">--------------------------------------</option>';
		foreach ($category['boards'] as $board)
			echo '
			<option value="', $board['id'], '"> ', str_repeat('==', $board['child_level']), '=&gt;&nbsp;', $board['name'], '</option>';
	}
	echo '
		</select>
		<label for="id_board_to">', $txt['move_topics_to'], '</label>
		<select name="id_board_to" id="id_board_to">
			<option disabled="disabled">(', $txt['move_topics_select_board'], ')</option>';
	// From boards.
	foreach ($context['categories'] as $category)
	{
		echo '
			<option disabled="disabled">--------------------------------------</option>
			<option disabled="disabled">', $category['name'], '</option>
			<option disabled="disabled">--------------------------------------</option>';
		foreach ($category['boards'] as $board)
			echo '
		<option value="', $board['id'], '"> ', str_repeat('==', $board['child_level']), '=&gt;&nbsp;', $board['name'], '</option>';
	}
	echo '
		</select><br />
		<div align="right" style="margin: 1ex;">
			<input type="submit" value="', $txt['move_topics_now'], '" onclick="if (document.getElementById(\'id_board_from\').options[document.getElementById(\'id_board_from\').selectedIndex].disabled || document.getElementById(\'id_board_from\').options[document.getElementById(\'id_board_to\').selectedIndex].disabled) return false; var confirmText = \'', $txt['move_topics_confirm'] . '\'; return confirm(confirmText.replace(/%board_from%/, document.getElementById(\'id_board_from\').options[document.getElementById(\'id_board_from\').selectedIndex].text.replace(/^=+&gt;&nbsp;/, \'\')).replace(/%board_to%/, document.getElementById(\'id_board_to\').options[document.getElementById(\'id_board_to\').selectedIndex].text.replace(/^=+&gt;&nbsp;/, \'\')));" />
		</div>';			
}

// Simple template for showing results of our optimization...
function template_optimize()
{
	global $context, $settings, $options, $txt, $scripturl;

	echo '
	<div class="tborder">
		<div class="titlebg" style="padding: 4px;">', $txt['maintain_optimize'], '</div>
		<div class="windowbg" style="padding: 4px;">
			', $txt['database_numb_tables'], '<br />
			', $txt['database_optimize_attempt'], '<br />';

	// List each table being optimized...
	foreach ($context['optimized_tables'] as $table)
		echo '
			', sprintf($txt['database_optimizing'], $table['name'], $table['data_freed']), '<br />';

	// How did we go?
	echo '
			<br />', $context['num_tables_optimized'] == 0 ? $txt['database_already_optimized'] : $context['num_tables_optimized'] . ' ' . $txt['database_optimized'];

	echo '
			<br /><br />
			<a href="', $scripturl, '?action=admin;area=maintain">', $txt['maintain_return'], '</a>
		</div>
	</div>';
}

// Template for listing all scheduled tasks.
function template_view_scheduled_tasks()
{
	global $context, $settings, $options, $txt, $scripturl, $modSettings;

	// We completed some tasks?
	if (!empty($context['tasks_were_run']))
		echo '
		<div class="windowbg" style="margin: 1ex; padding: 1ex 2ex; border: 1px dashed green; color: green;">
			', $txt['scheduled_tasks_were_run'], '
		</div>';

	template_show_list('scheduled_tasks');
}

// A template for, you guessed it, editing a task!
function template_edit_scheduled_tasks()
{
	global $context, $settings, $options, $txt, $scripturl, $modSettings;

	// Starts off with general maintenance procedures.
	echo '
	<form action="', $scripturl, '?action=admin;area=maintain;sa=taskedit;save;tid=', $context['task']['id'], '" method="post" accept-charset="', $context['character_set'], '">
		<table align="center" width="80%" cellpadding="4" cellspacing="0" border="0" class="tborder">
			<tr class="titlebg">
				<td colspan="2">', $txt['scheduled_task_edit'], '</td>
			</tr><tr class="windowbg2">
				<td colspan="2">
					<span class="smalltext">
						<em>', sprintf($txt['scheduled_task_time_offset'], $context['server_time']), '</em>
					</span>
				</td>
			</tr><tr class="windowbg" valign="top">
				<td width="30%">
					<b>', $txt['scheduled_tasks_name'], ':</b>
				</td><td width="70%">
					', $context['task']['name'], '</a><br />
					<span class="smalltext">', $context['task']['desc'], '</span>
				</td>
			</tr><tr class="windowbg">
				<td width="30%">
					<b>', $txt['scheduled_task_edit_interval'], ':</b>
				</td><td width="70%">
					', $txt['scheduled_task_edit_repeat'], '
					<input type="text" name="regularity" value="', empty($context['task']['regularity']) ? 1 : $context['task']['regularity'], '" onchange="if (this.value < 1) this.value = 1;" size="2" maxlength="2" />
					<select name="unit">
						<option value="0">', $txt['scheduled_task_edit_pick_unit'], '</option>
						<option value="0">---------------------</option>
						<option value="m" ', empty($context['task']['unit']) || $context['task']['unit'] == 'm' ? 'selected="selected"' : '', '>', $txt['scheduled_task_reg_unit_m'], '</option>
						<option value="h" ', $context['task']['unit'] == 'h' ? 'selected="selected"' : '', '>', $txt['scheduled_task_reg_unit_h'], '</option>
						<option value="d" ', $context['task']['unit'] == 'd' ? 'selected="selected"' : '', '>', $txt['scheduled_task_reg_unit_d'], '</option>
						<option value="w" ', $context['task']['unit'] == 'w' ? 'selected="selected"' : '', '>', $txt['scheduled_task_reg_unit_w'], '</option>
					</select>
				</td>
			</tr><tr class="windowbg" valign="top">
				<td width="30%">
					<b>', $txt['scheduled_task_edit_start_time'], ':</b><br />
					<span class="smalltext">', $txt['scheduled_task_edit_start_time_desc'], '</span>
				</td><td width="70%">
					<input type="text" name="offset" value="', $context['task']['offset_formatted'], '" size="6" maxlength="5" />
				</td>
			</tr><tr class="windowbg">
				<td width="30%">
					<b>', $txt['scheduled_tasks_enabled'], ':</b>
				</td><td width="70%">
					<input type="checkbox" name="enabled" id="enabled" ', !$context['task']['disabled'] ? 'checked="checked"' : '', ' class="check" />
				</td>
			</tr><tr class="windowbg">
				<td colspan="2" align="center">
					<input type="hidden" name="sc" value="', $context['session_id'], '" />
					<input type="submit" name="save" value="', $txt['scheduled_tasks_save_changes'], '" />
				</td>
			</tr>
		</table>
	</form>';
}

function template_convert_utf8()
{
	global $context, $txt, $settings, $scripturl;

	echo '
		<table width="100%" cellpadding="5" cellspacing="0" border="0" class="tborder">
			<tr class="titlebg">
				<td>', $txt['utf8_title'], '</td>
			</tr><tr>
				<td class="windowbg2">
					', $txt['utf8_introduction'], '
				</td>
			</tr><tr>
				<td class="windowbg2">
					', $txt['utf8_warning'], '
				</td>
			</tr><tr>
				<td class="windowbg2">
					', $context['charset_about_detected'], isset($context['charset_warning']) ? ' <span style="color: red;">' . $context['charset_warning'] . '</span>' : '', '<br />
					<br />
				</td>
			</tr><tr>
				<td class="windowbg2" align="center">
					<form action="', $scripturl, '?action=admin;area=maintain;sa=convertutf8" method="post" accept-charset="', $context['character_set'], '">
						<table><tr>
							<th align="right">', $txt['utf8_source_charset'], ': </th>
							<td><select name="src_charset">';
	foreach ($context['charset_list'] as $charset)
		echo '
								<option value="', $charset, '"', $charset === $context['charset_detected'] ? ' selected="selected"' : '', '>', $charset, '</option>';
	echo '
							</select></td>
						</tr><tr>
							<th align="right">', $txt['utf8_database_charset'], ': </th>
							<td>', $context['database_charset'], '</td>
						</tr><tr>
							<th align="right">', $txt['utf8_target_charset'], ': </th>
							<td>', $txt['utf8_utf8'], '</td>
						</tr><tr>
							<td colspan="2" align="right"><br />
								<input type="submit" value="', $txt['utf8_proceed'], '" />
							</td>
						</tr></table>
						<input type="hidden" name="sc" value="', $context['session_id'], '" />
						<input type="hidden" name="proceed" value="1" />
					</form>
				</td>
			</tr>
		</table>';
}

function template_convert_entities()
{
	global $context, $txt, $settings, $scripturl;

	echo '
		<table width="100%" cellpadding="5" cellspacing="0" border="0" class="tborder">
			<tr class="titlebg">
				<td>', $txt['entity_convert_title'], '</td>
			</tr><tr>
				<td class="windowbg2">
					', $txt['entity_convert_introduction'], '
				</td>
			</tr>
			<tr>
				<td class="windowbg2" align="center">
					<form action="', $scripturl, '?action=admin;area=maintain;sa=convertentities;start=0;sesc=', $context['session_id'], '" method="post" accept-charset="', $context['character_set'], '">
						<input type="submit" value="', $txt['entity_convert_proceed'], '" />
					</form>
				</td>
			</tr>
		</table>';
}

?>