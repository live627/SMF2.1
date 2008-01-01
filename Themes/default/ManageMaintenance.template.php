<?php
// Version: 2.0 Beta 2; ManageMaintenance

// Template for forum maintenance page.
function template_maintain()
{
	global $context, $settings, $options, $txt, $scripturl, $modSettings;

	// Starts off with general maintenance procedures.
	echo '
		<table width="100%" cellpadding="4" cellspacing="1" border="0" class="bordercolor">
			<tr class="titlebg">
				<td><a href="', $scripturl, '?action=helpadmin;help=maintenance_general" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt['help'], '" align="top" /></a> ', $txt['maintain_general'], '</td>
			</tr>
			<tr>
				<td class="windowbg2" style="line-height: 1.3; padding-bottom: 2ex;">
					<a href="', $scripturl, '?action=admin;area=maintain;sa=optimize;sesc=', $context['session_id'], '">', $txt['maintain_optimize'], '</a><br />
					<a href="', $scripturl, '?action=admin;area=version">', $txt['maintain_version'], '</a><br />
					<a href="', $scripturl, '?action=admin;area=repairboards">', $txt['maintain_errors'], '</a><br />
					<a href="', $scripturl, '?action=admin;area=maintain;sa=recount;sesc=', $context['session_id'], '">', $txt['maintain_recount'], '</a><br />
					<a href="', $scripturl, '?action=admin;area=maintain;sa=logs;sesc=', $context['session_id'], '">', $txt['maintain_logs'], '</a><br />', $context['convert_utf8'] ? '
					<a href="' . $scripturl . '?action=admin;area=maintain;sa=convertutf8">' . $txt['utf8_title'] . '</a><br />' : '', $context['convert_entities'] ? '
					<a href="' . $scripturl . '?action=admin;area=maintain;sa=convertentities">' . $txt['entity_convert_title'] . '</a><br />' : '', '
					<a href="', $scripturl, '?action=admin;area=maintain;sa=cleancache">', $txt['maintain_cache'], '</a><br />
				</td>
			</tr>';

	// Backing up the database...?  Good idea!
	echo '
			<tr class="titlebg">
				<td><a href="', $scripturl, '?action=helpadmin;help=maintenance_backup" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt['help'], '" align="top" /></a> ', $txt['maintain_backup'], '</td>
			</tr>
			<tr>
				<td class="windowbg2" style="padding-bottom: 1ex;">
					<form action="', $scripturl, '" method="get" accept-charset="', $context['character_set'], '" onsubmit="return this.struct.checked || this.data.checked;">
						<label for="struct"><input type="checkbox" name="struct" id="struct" onclick="this.form.submitDump.disabled = !this.form.struct.checked &amp;&amp; !this.form.data.checked;" class="check" /> ', $txt['maintain_backup_struct'], '</label><br />
						<label for="data"><input type="checkbox" name="data" id="data" onclick="this.form.submitDump.disabled = !this.form.struct.checked &amp;&amp; !this.form.data.checked;" checked="checked" class="check" /> ', $txt['maintain_backup_data'], '</label><br />
						<br />
						<label for="compress"><input type="checkbox" name="compress" id="compress" value="gzip" checked="checked" class="check" /> ', $txt['maintain_backup_gz'], '</label>
						<div align="right" style="margin: 1ex;"><input type="submit" id="submitDump" value="', $txt['maintain_backup_save'], '" /></div>
						<input type="hidden" name="action" value="admin" />
						<input type="hidden" name="area" value="dumpdb" />
						<input type="hidden" name="sesc" value="', $context['session_id'], '" />
					</form>
				</td>
			</tr>';

	// Pruning Members?
	echo '
			<tr class="titlebg">
				<td><a href="', $scripturl, '?action=helpadmin;help=maintenance_members" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt['help'], '" align="top" /></a> ', $txt['maintain_members'], '</td>
			</tr>
			<tr>
				<td class="windowbg2">
					<a name="membersLink"></a>';

	// Simple javascript for showing and hiding membergroups.
	echo '
					<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
						var membersSwap = false;
						function swapMembers()
						{
							membersSwap = !membersSwap;

							document.getElementById("membersIcon").src = smf_images_url + (membersSwap ? "/collapse.gif" : "/expand.gif");
							setInnerHTML(document.getElementById("membersText"), membersSwap ? "', $txt['maintain_members_choose'], '" : "', $txt['maintain_members_all'], '");
							document.getElementById("membersPanel").style.display = (membersSwap ? "block" : "none");

							for (var i = 0; i < document.forms.membersForm.length; i++)
							{
								if (document.forms.membersForm.elements[i].type.toLowerCase() == "checkbox")
									document.forms.membersForm.elements[i].checked = !membersSwap;
							}
						}
					// ]]></script>';

	// Give them the options.
	echo '
					<form action="', $scripturl, '?action=admin;area=maintain;sa=members" method="post" accept-charset="', $context['character_set'], '" name="membersForm" id="membersForm">
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

						<div align="right" style="margin: 1ex;"><input type="submit" value="', $txt['maintain_old_remove'], '" onclick="return confirm(\'', $txt['maintain_members_confirm'], '\');" /></div>
						<input type="hidden" name="sc" value="', $context['session_id'], '" />
					</form>
				</td>
			</tr>';

	// Pruning any older posts.
	echo '
			<tr class="titlebg">
				<td><a href="', $scripturl, '?action=helpadmin;help=maintenance_rot" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt['help'], '" align="top" /></a> ', $txt['maintain_old'], '</td>
			</tr>
			<tr>
				<td class="windowbg2">
					<a name="rotLink"></a>';

	// Bit of javascript for showing which boards to prune in an otherwise hidden list.
	echo '
					<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
						var rotSwap = false;
						function swapRot()
						{
							rotSwap = !rotSwap;

							document.getElementById("rotIcon").src = smf_images_url + (rotSwap ? "/collapse.gif" : "/expand.gif");
							setInnerHTML(document.getElementById("rotText"), rotSwap ? "', $txt['maintain_old_choose'], '" : "', $txt['maintain_old_all'], '");
							document.getElementById("rotPanel").style.display = (rotSwap ? "block" : "none");

							for (var i = 0; i < document.forms.rotForm.length; i++)
							{
								if (document.forms.rotForm.elements[i].type.toLowerCase() == "checkbox" && document.forms.rotForm.elements[i].id != "delete_old_not_sticky")
									document.forms.rotForm.elements[i].checked = !rotSwap;
							}
						}
					// ]]></script>';

	// The otherwise hidden "choose which boards to prune".
	echo '
					<form action="', $scripturl, '?action=removeoldtopics2" method="post" accept-charset="', $context['character_set'], '" name="rotForm" id="rotForm">
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

						<div align="right" style="margin: 1ex;"><input type="submit" value="', $txt['maintain_old_remove'], '" onclick="return confirm(\'', $txt['maintain_old_confirm'], '\');" /></div>
						<input type="hidden" name="sc" value="', $context['session_id'], '" />
					</form>
				</td>
			</tr>
		</table>';

	// Pop up a box to say function completed if the user has been redirected back here from a function they ran.
	if ($context['maintenance_finished'])
		echo '
	<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
		setTimeout("alert(\"', $txt['maintain_done'], '\")", 120);
	// ]]></script>';
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

	// Starts off with general maintenance procedures.
	echo '
	<form action="', $scripturl, '?action=admin;area=maintain;sa=tasks" method="post" accept-charset="', $context['character_set'], '">
		<table width="100%" cellpadding="4" cellspacing="1" border="0" class="bordercolor">
			<tr class="titlebg">
				<td colspan="5">', $txt['maintain_tasks'], '</td>
			</tr>
			<tr class="windowbg2">
				<td colspan="5">
					<span class="smalltext">
						', $txt['scheduled_tasks_time_offset'], '
					</span>
				</td>
			</tr>
			<tr class="catbg">
				<td colspan="5">', $txt['scheduled_tasks_header'], '</td>
			</tr>
			<tr class="titlebg">
				<td width="40%">', $txt['scheduled_tasks_name'], '</td>
				<td>', $txt['scheduled_tasks_next_time'], '</td>
				<td>', $txt['scheduled_tasks_regularity'], '</td>
				<td width="6%">', $txt['scheduled_tasks_enabled'], '</td>
				<td width="6%">', $txt['scheduled_tasks_run_now'], '</td>
			</tr>';

	$alternate = 0;
	foreach ($context['tasks'] as $task)
	{
		echo '
			<tr class="', $alternate ? 'windowbg' : 'windowbg2', '">
				<td>
					<a href="', $scripturl, '?action=admin;area=maintain;sa=taskedit;tid=', $task['id'], '">', $task['name'], '</a><br />
					<span class="smalltext">', $task['desc'], '</span>
				</td>
				<td><span class="smalltext">', $task['next_time'], '</span></td>
				<td><span class="smalltext">', $task['regularity'], '</span></td>
				<td align="center">
					<input type="hidden" name="task[', $task['id'], ']" id="task_', $task['id'], '" value="0" />
					<input type="checkbox" name="task[', $task['id'], ']" id="task_check_', $task['id'], '" ', !$task['disabled'] ? 'checked="checked"' : '', ' class="check" />
				</td>
				<td align="center">
					<input type="checkbox" name="run_task[', $task['id'], ']" id="run_task_', $task['id'], '" class="check" />
				</td>
			</tr>';
		$alternate = !$alternate;
	}

	echo '
			<tr class="titlebg">
				<td colspan="5" align="right">
					<div style="float: left;">
						[<a href="', $scripturl, '?action=admin;area=maintain;sa=tasklog">', $txt['scheduled_view_log'], '</a>]
					</div>
					<div style="float: right;">
						<input type="hidden" name="sc" value="', $context['session_id'], '" />
						<input type="submit" name="save" value="', $txt['scheduled_tasks_save_changes'], '" />
						<input type="submit" name="run" value="', $txt['scheduled_tasks_run_now'], '" />
					</div>
				</td>
			</tr>
		</table>
	</form>';
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

// Show the task log.
function template_task_log()
{
	global $context, $settings, $options, $txt, $scripturl, $modSettings;

	echo '
	<form action="', $scripturl, '?action=admin;area=maintain;sa=tasklog" method="post" accept-charset="', $context['character_set'], '">
		<table width="100%" cellpadding="4" cellspacing="1" border="0" class="bordercolor">
			<tr class="titlebg">
				<td colspan="3">', $txt['scheduled_log'], '</td>
			</tr>
			<tr class="catbg">
				<td colspan="3">', $context['page_index'], '</td>
			</tr>
			<tr class="titlebg">
				<td>', $txt['scheduled_tasks_name'], '</td>
				<td>', $txt['scheduled_log_time_run'], '</td>
				<td>', $txt['scheduled_log_time_taken'], '</td>
			</tr>';

	// Nothing?
	if (empty($context['log_entries']))
	{
		echo '
			<tr class="windowbg2">
				<td colspan="3" align="center">
					', $txt['scheduled_log_empty'], '
				</td>
			</tr>';
	}

	$alternate = 0;
	foreach ($context['log_entries'] as $entry)
	{
		echo '
			<tr class="', $alternate ? 'windowbg' : 'windowbg2', '">
				<td>
					', $entry['name'], '
				</td>
				<td>', $entry['time_run'], '</td>
				<td>', $entry['time_taken'], ' ', $txt['scheduled_log_time_taken_seconds'], '</td>
			</tr>';
		$alternate = !$alternate;
	}

	echo '
			<tr class="catbg">
				<td colspan="3">', $context['page_index'], '</td>
			</tr>
			<tr class="titlebg">
				<td colspan="3" align="right">
					<input type="hidden" name="sc" value="', $context['session_id'], '" />
					<input type="submit" name="deleteAll" value="', $txt['scheduled_log_empty_log'], '" />
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
			</tr>';
	if ($context['first_step'])
	{
		echo '
			<tr>
				<td class="windowbg2" align="center">
					<form action="', $scripturl, '?action=admin;area=maintain;sa=convertentities;start=0;sesc=', $context['session_id'], '" method="post" accept-charset="', $context['character_set'], '">
						<input type="submit" value="', $txt['entity_convert_proceed'], '" />
					</form>
				</td>
			</tr>';
	}
	else
	{
		echo '
			<tr>
				<td>
					<div style="padding-left: 20%; padding-right: 20%; margin-top: 1ex;">
						<div style="font-size: 8pt; height: 12pt; border: 1px solid black; background-color: white; padding: 1px; position: relative;">
							<div style="padding-top: ', $context['browser']['is_safari'] || $context['browser']['is_konqueror'] ? '2pt' : '1pt', '; width: 100%; z-index: 2; color: black; position: absolute; text-align: center; font-weight: bold;">', $context['percent_done'], '%</div>
							<div style="width: ', $context['percent_done'], '%; height: 12pt; z-index: 1; background-color: red;">&nbsp;</div>
						</div>
					</div>
					<form action="', $scripturl, $context['continue_get_data'], '" method="post" accept-charset="', $context['character_set'], '" style="margin: 0;" name="autoSubmit" id="autoSubmit">
						<div style="margin: 1ex; text-align: right;"><input type="submit" name="cont" value="', $txt['not_done_continue'], '" /></div>
					</form>
					<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
						var countdown = ', $context['last_step'] ? '-1' : '2', ';
						doAutoSubmit();

						function doAutoSubmit()
						{
							if (countdown == 0)
								document.forms.autoSubmit.submit();
							else if (countdown == -1)
								return;

							document.forms.autoSubmit.cont.value = "', $txt['not_done_continue'], ' (" + countdown + ")";
							countdown--;

							setTimeout("doAutoSubmit();", 1000);
						}
					// ]]></script>
				</td>
			</tr>';
	}
	echo '
		</table>';
}

?>