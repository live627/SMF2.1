<?php
// Version: 2.0 Beta 1; ManagePermissions

function template_permission_index()
{
	global $context, $settings, $options, $scripturl, $txt, $modSettings;

	echo '
		<form action="' . $scripturl . '?action=admin;area=permissions;sa=quick" method="post" accept-charset="', $context['character_set'], '" name="permissionForm" id="permissionForm">
			<table width="100%" border="0" cellpadding="2" cellspacing="1" class="tborder">';
	if (!empty($context['profile']))
		echo '
				<tr class="titlebg">
					<td colspan="6" style="padding: 4px;">', $txt['permissions_for_profile'], ': &quot;', $context['profile']['name'], '&quot;</td>
				</tr>';
	echo '
				<tr class="catbg3">
					<td valign="middle">', $txt['membergroups_name'], '</td>
					<td width="10%" align="center" valign="middle">', $txt['membergroups_members_top'], '</td>
					<td width="16%" align="center"', empty($modSettings['permission_enable_deny']) ? '' : ' class="smalltext"', '>
						', $txt['membergroups_permissions'], empty($modSettings['permission_enable_deny']) ? '' : '<br />
						<div style="float: left; width: 50%;">' . $txt['permissions_allowed'] . '</div> ' . $txt['permissions_denied'], '
					</td>
					<td width="10%" align="center" valign="middle">', $txt['permissions_modify'], '</td>
					<td width="4%" align="center" valign="middle">
						<input type="checkbox" class="check" onclick="invertAll(this, this.form, \'group\');" /></td>
				</tr>';

	foreach ($context['groups'] as $group)
	{
		echo '
				<tr>
					<td class="windowbg2">
						', $group['name'], $group['id'] == -1 ? ' (<a href="' . $scripturl . '?action=helpadmin;help=membergroup_guests" onclick="return reqWin(this.href);">?</a>)' : ($group['id'] == 0 ? ' (<a href="' . $scripturl . '?action=helpadmin;help=membergroup_regular_members" onclick="return reqWin(this.href);">?</a>)' : ($group['id'] == 1 ? ' (<a href="' . $scripturl . '?action=helpadmin;help=membergroup_administrator" onclick="return reqWin(this.href);">?</a>)' : ($group['id'] == 3 ? ' (<a href="' . $scripturl . '?action=helpadmin;help=membergroup_moderator" onclick="return reqWin(this.href);">?</a>)' : '')));

		if (!empty($group['children']))
			echo '
						<br /><span class="smalltext">', $txt['permissions_includes_inherited'], ': &quot;', implode('&quot;, &quot;', $group['children']), '&quot;</span>';

		echo '
					</td>
					<td class="windowbg" align="center">', $group['can_search'] ? $group['link'] : $group['num_members'], '</td>
					<td class="windowbg2" align="center"', $group['id'] == 1 ? ' style="font-style: italic;"' : '', '>';
		if (empty($modSettings['permission_enable_deny']))
			echo '
						', $group['num_permissions']['allowed'];
		else
			echo '
						<div style="float: left; width: 50%;">', $group['num_permissions']['allowed'], '</div> ', empty($group['num_permissions']['denied']) || $group['id'] == 1 ? $group['num_permissions']['denied'] : ($group['id'] == -1 ? '<span style="font-style: italic;">' . $group['num_permissions']['denied'] . '</span>' : '<span style="color: red;">' . $group['num_permissions']['denied'] . '</span>');
		echo '
					</td>
					<td class="windowbg2" align="center">', $group['allow_modify'] ? '<a href="' . $scripturl . '?action=admin;area=permissions;sa=modify;group=' . $group['id'] . (empty($context['profile']) ? '' : ';pid=' . $context['profile']['id']) . '">' . $txt['permissions_modify'] . '</a>' : '', '</td>
					<td class="windowbg" align="center">', $group['allow_modify'] ? '<input type="checkbox" name="group[]" value="' . $group['id'] . '" class="check" />' : '', '</td>
				</tr>';
	}

	echo '
				<tr class="windowbg">
					<td colspan="7">
						<a href="#" onclick="smfPermissionsPanelToggle.toggle(); return false;"><img src="', $settings['images_url'], '/', empty($context['show_advanced_options']) ? 'expand' : 'sort_down', '.gif" id="permissions_panel_toggle" alt="*" /> ', $txt['permissions_advanced_options'], '</a>
					</td>
				</tr>
				<tr class="windowbg" id="permissions_panel_advanced">
					<td colspan="6" style="padding-top: 1ex; padding-bottom: 1ex; text-align: right;">
						<table width="100%" cellspacing="0" cellpadding="3" border="0"><tr><td>
							<div style="margin-bottom: 1ex;"><b>', $txt['permissions_with_selection'], '...</b></div>
							', $txt['permissions_apply_pre_defined'], ' <a href="' . $scripturl . '?action=helpadmin;help=permissions_quickgroups" onclick="return reqWin(this.href);">(?)</a>:
							<select name="predefined">
								<option value="">(' . $txt['permissions_select_pre_defined'] . ')</option>
								<option value="restrict">' . $txt['permitgroups_restrict'] . '</option>
								<option value="standard">' . $txt['permitgroups_standard'] . '</option>
								<option value="moderator">' . $txt['permitgroups_moderator'] . '</option>
								<option value="maintenance">' . $txt['permitgroups_maintenance'] . '</option>
							</select><br /><br />';

	echo '
							', $txt['permissions_like_group'], ':
							<select name="copy_from">
								<option value="empty">(', $txt['permissions_select_membergroup'], ')</option>';
	foreach ($context['groups'] as $group)
	{
		if ($group['id'] != 1)
			echo '
								<option value="', $group['id'], '">', $group['name'], '</option>';
	}

	echo '
							</select><br /><br />
							<select name="add_remove">
								<option value="add">', $txt['permissions_add'], '...</option>
								<option value="clear">', $txt['permissions_remove'], '...</option>';
	if (!empty($modSettings['permission_enable_deny']))
		echo '
								<option value="deny">', $txt['permissions_deny'], '...</option>';
	echo '
							</select>&nbsp;<select name="permissions">
								<option value="">(', $txt['permissions_select_permission'], ')</option>';
	foreach ($context['permissions'] as $permissionType)
	{
		if ($permissionType['id'] == 'membergroup' && !empty($context['profile']))
			continue;

		foreach ($permissionType['columns'] as $column)
		{
			foreach ($column as $permissionGroup)
			{
				if ($permissionGroup['hidden'])
					continue;

				echo '
								<option value="" disabled="disabled">[', $permissionGroup['name'], ']</option>';
				foreach ($permissionGroup['permissions'] as $perm)
				{
					if ($perm['hidden'])
						continue;

					if ($perm['has_own_any'])
						echo '
								<option value="', $permissionType['id'], '/', $perm['own']['id'], '">&nbsp;&nbsp;&nbsp;', $perm['name'], ' (', $perm['own']['name'], ')</option>
								<option value="', $permissionType['id'], '/', $perm['any']['id'], '">&nbsp;&nbsp;&nbsp;', $perm['name'], ' (', $perm['any']['name'], ')</option>';
					else
						echo '
								<option value="', $permissionType['id'], '/', $perm['id'], '">&nbsp;&nbsp;&nbsp;', $perm['name'], '</option>';
				}
			}
		}
	}
	echo '
							</select>
						</td><td valign="bottom" width="16%">
							<input type="submit" value="', $txt['permissions_set_permissions'], '" onclick="return checkSubmit();" />
						</td></tr></table>
					</td>
				</tr>
			</table>';

	// Javascript for the advanced stuff.
	echo '
	<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
		var smfPermissionsPanelToggle = new smfToggle("smfPermissionsPanelToggle", ', empty($context['show_advanced_options']) ? 1 : 0, ');
		smfPermissionsPanelToggle.addToggleImage("permissions_panel_toggle", "/sort_down.gif", "/selected.gif");
		smfPermissionsPanelToggle.addTogglePanel("permissions_panel_advanced");';

	if (empty($context['show_advanced_options']))
		echo '
		document.getElementById(\'permissions_panel_advanced\').style.display = "none";';

	echo '
	
		function checkSubmit()
		{
			if ((document.forms.permissionForm.predefined.value != "" && (document.forms.permissionForm.copy_from.value != "empty" || document.forms.permissionForm.permissions.value != "")) || (document.forms.permissionForm.copy_from.value != "empty" && document.forms.permissionForm.permissions.value != ""))
			{
				alert("', $txt['permissions_only_one_option'], '");
				return false;
			}
			if (document.forms.permissionForm.predefined.value == "" && document.forms.permissionForm.copy_from.value == "" && document.forms.permissionForm.permissions.value == "")
			{
				alert("', $txt['permissions_no_action'], '");
				return false;
			}
			if (document.forms.permissionForm.permissions.value != "" && document.forms.permissionForm.add_remove.value == "deny")
				return confirm("', $txt['permissions_deny_dangerous'], '");
	
			return true;
		}
	// ]]></script>';

	if (!empty($context['profile']))
		echo '
			<input type="hidden" name="pid" value="', $context['profile']['id'], '" />';

	echo '
			<input type="hidden" name="sc" value="' . $context['session_id'] . '" />
		</form>';
}

function template_by_board()
{
	global $context, $settings, $options, $scripturl, $txt, $modSettings;

	echo '
		<form action="', $scripturl, '?action=admin;area=permissions;sa=board" method="post" accept-charset="', $context['character_set'], '">
			<table width="60%" align="center" border="0" cellpadding="4" cellspacing="1" class="tborder" style="margin-top: 2ex;">
				<tr class="titlebg">
					<td colspan="2">', $txt['permissions_boards'], '</td>
				</tr>
				<tr class="windowbg2">
					<td colspan="2" class="smalltext">', $txt['permissions_boards_desc'], '</td>
				</tr>
				<tr class="catbg">
					<td>', $txt['board_name'], '</td>
					<td>', $txt['permission_profile'], '</td>
				</tr>';

	foreach ($context['categories'] as $category)
	{
		echo '
				<tr class="windowbg">
					<td colspan="2">
						<i>', $category['name'], '</i>
					</td>
				</tr>';

		foreach ($category['boards'] as $board)
		{
			echo '
				<tr class="windowbg2">
					<td width="60%" align="left">
						<a href="', $scripturl, '?action=admin;area=manageboards;sa=board;boardid=', $board['id'], ';sesc=', $context['session_id'], '">', str_repeat('-', $board['child_level']), ' ', $board['name'], '</a>
					</td>
					<td width="40%" align="left">';
			if ($context['edit_all'])
			{
				echo '
						<select name="boardprofile[', $board['id'], ']">';

				foreach ($context['profiles'] as $id => $profile)
					echo '
							<option value="', $id, '" ', $id == $board['profile'] ? 'selected="selected"' : '', '>', $profile['name'], '</option>';

				echo '
						</select>';
			}
			else
				echo '
						<a href="', $scripturl, '?action=admin;area=permissions;sa=index;pid=', $board['profile'], ';sesc=', $context['session_id'], '">', $board['profile_name'], '</a>';

			echo '
					</td>
				</tr>';
		}
	}

	echo '
				<tr class="catbg">
					<td colspan="2" align="right">';

	if ($context['edit_all'])
		echo '
						<input type="submit" name="save_changes" value="', $txt['save'], '" />';
	else
		echo '
						<a href="', $scripturl, '?action=admin;area=permissions;sa=board;edit;sesc=', $context['session_id'], '">', $txt['permissions_board_all'], '</a>';

	echo '
					</td>
				</tr>
			</table>
			<input type="hidden" name="sc" value="', $context['session_id'], '" />
		</form>';
}

// Edit permission profiles (predefined).
function template_edit_profiles()
{
	global $context, $settings, $options, $scripturl, $txt, $modSettings;

	echo '
		<form action="', $scripturl, '?action=admin;area=permissions;sa=profiles" method="post" accept-charset="', $context['character_set'], '">
			<table width="50%" align="center" border="0" cellpadding="3" cellspacing="1" class="tborder" style="margin-top: 2ex;">
				<tr class="titlebg">
						<td colspan="3">', $txt['permissions_profile_edit'], '</td>
				</tr>
				<tr class="catbg">
					<td>', $txt['permissions_profile_name'], '</td>
					<td>', $txt['permissions_profile_used_by'], '</td>
					<td width="5%">', $txt['delete'], '</td>
				</tr>';
	$alternate = false;
	foreach ($context['profiles'] as $profile)
	{
		echo '
				<tr class="', $alternate ? 'windowbg' : 'windowbg2', '">
					<td>';
		
		if (!empty($context['show_rename_boxes']) && $profile['can_edit'])
			echo '
						<input type="text" name="rename_profile[', $profile['id'], ']" value="', $profile['name'], '" />';
		else
			echo '
						<a href="', $scripturl, '?action=admin;area=permissions;sa=index;pid=', $profile['id'], ';sesc=', $context['session_id'], '">', $profile['name'], '</a>';

		echo '
					</td><td>
						', !empty($profile['boards_text']) ? $profile['boards_text'] : $txt['permissions_profile_used_by_none'], '
					</td><td align="center">
						<input type="checkbox" name="delete_profile[]" value="', $profile['id'], '" ', $profile['can_delete'] ? '' : 'disabled="disabled"', ' class="check" />
					</td>
				</tr>';
		$alternate = !$alternate;
	}

	echo '
				<tr class="titlebg">
					<td colspan="3" align="right">
						<input type="hidden" name="sc" value="', $context['session_id'], '" />';

	if ($context['can_edit_something'])
		echo '
						<input type="submit" name="rename" value="', empty($context['show_rename_boxes']) ? $txt['permissions_profile_rename'] : $txt['scheduled_tasks_save_changes'], '" />';

	echo '
						<input type="submit" name="delete" value="', $txt['quickmod_delete_selected'], '" />
					</td>
				</tr>
			</table>
		</form>

		<form action="', $scripturl, '?action=admin;area=permissions;sa=profiles" method="post" accept-charset="', $context['character_set'], '">
			<table width="50%" align="center" border="0" cellpadding="3" cellspacing="0" class="tborder" style="margin-top: 2ex;">
				<tr class="titlebg">
					<td colspan="2">', $txt['permissions_profile_new'], '</td>
				</tr>
				<tr class="windowbg2">
					<td width="50%">
						<b>', $txt['permissions_profile_name'], ':</b>
					</td>
					<td width="50%">
						<input type="text" name="profile_name" value="" />
					</td>
				</tr>
				<tr class="windowbg2">
					<td width="50%">
						<b>', $txt['permissions_profile_copy_from'], ':</b>
					</td>
					<td width="50%">
						<select name="copy_from">';

	foreach ($context['profiles'] as $id => $profile)
		echo '
							<option value="', $id, '">', $profile['name'], '</option>';

	echo '
						</select>
					</td>
				</tr>
				<tr class="titlebg">
					<td align="right" colspan="2">
						<input type="hidden" name="sc" value="', $context['session_id'], '" />
						<input type="submit" name="create" value="', $txt['permissions_profile_new_create'], '" />
					</td>
				</tr>
			</table>
		</form>';
}

function template_modify_group()
{
	global $context, $settings, $options, $scripturl, $txt, $modSettings;

	echo '
		<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
			window.smf_usedDeny = false;

			function warnAboutDeny()
			{
				if (window.smf_usedDeny)
					return confirm("', $txt['permissions_deny_dangerous'], '");
				else
					return true;
			}
		// ]]></script>
		<form action="', $scripturl, '?action=admin;area=permissions;sa=modify2;group=', $context['group']['id'], ';pid=', $context['profile']['id'], '" method="post" accept-charset="', $context['character_set'], '" name="permissionForm" id="permissionForm" onsubmit="return warnAboutDeny();">
			<table width="100%" cellpadding="4" cellspacing="0" border="0" class="tborder">';

	if (!empty($modSettings['permission_enable_deny']) && $context['group']['id'] != -1)
		echo '
				<tr class="windowbg">
					<td colspan="2" class="smalltext" style="padding: 2ex;">', $txt['permissions_option_desc'], '</td>
				</tr>';

	echo '
				<tr class="catbg">
					<td colspan="2" align="center">';
	if ($context['permission_type'] == 'board')
		echo '
						', $txt['permissions_local_for'], ' &quot;', $context['group']['name'], '&quot; ', $txt['permissions_on'], ' &quot;', $context['profile']['name'], '&quot;';
	else
		echo '
						', $context['permission_type'] == 'membergroup' ? $txt['permissions_general'] : $txt['permissions_board'], ' - &quot;', $context['group']['name'], '&quot;';
	echo '
					</td>
				</tr>
				<tr class="windowbg">
					<td colspan="2">
						', $txt['permissions_change_view'], ': ', ($context['view_type'] == 'simple' ? '<img src="' . $settings['images_url'] . '/selected.gif" alt="*" />' : ''), '<a href="', $scripturl, '?action=admin;area=permissions;sa=modify;group=', $context['group']['id'], ($context['permission_type'] == 'board' ? ';pid=' . $context['profile']['id'] : ''), ';view=simple">', $txt['permissions_view_simple'], '</a> |
						', ($context['view_type'] == 'classic' ? '<img src="' . $settings['images_url'] . '/selected.gif" alt="*" />' : ''), '<a href="', $scripturl, '?action=admin;area=permissions;sa=modify;group=', $context['group']['id'], ($context['permission_type'] == 'board' ? ';pid=' . $context['profile']['id'] : ''), ';view=classic">', $txt['permissions_view_classic'], '</a>
					</td>
				</tr>';

	// Draw out the main bits.
	if ($context['view_type'] == 'simple')
		template_modify_group_classic($context['permission_type']);
	else
		template_modify_group_classic($context['permission_type']);

	echo '
				<tr class="windowbg2">
					<td colspan="2" align="right"><input type="submit" value="', $txt['permissions_commit'], '" />&nbsp;</td>
				</tr>
			</table>
			<input type="hidden" name="sc" value="', $context['session_id'], '" />
		</form>';

}
function template_modify_group_classic($type)
{
	global $context, $settings, $options, $scripturl, $txt, $modSettings;

	$permission_type = &$context['permissions'][$type];

	echo '
				<tr class="windowbg2">';
	foreach ($permission_type['columns'] as $column)
	{
		echo '
					<td valign="top" width="50%">
						<table width="100%" cellpadding="1" cellspacing="0" border="0">';
		foreach ($column as $permissionGroup)
		{
			if (empty($permissionGroup['permissions']))
				continue;

					// Are we likely to have something in this group to display or is it all hidden?
			$has_display_content = false;
			if (!$permissionGroup['hidden'])
			{
				// Before we go any further check we are going to have some data to print otherwise we just have a silly heading.
				foreach ($permissionGroup['permissions'] as $permission)
					if (!$permission['hidden'])
						$has_display_content = true;

				if ($has_display_content)
				{
					echo '
							<tr class="windowbg2">
								<td colspan="2" width="100%" align="left"><div style="border-bottom: 1px solid; padding-bottom: 2px; margin-bottom: 2px;"><b>', $permissionGroup['name'], '</b></div></td>';
					if (empty($modSettings['permission_enable_deny']) || $context['group']['id'] == -1)
						echo '
								<td colspan="3" width="10"><div style="border-bottom: 1px solid; padding-bottom: 2px; margin-bottom: 2px;">&nbsp;</div></td>';
					else
						echo '
								<td align="center"><div style="border-bottom: 1px solid; padding-bottom: 2px; margin-bottom: 2px;">', $txt['permissions_option_on'], '</div></td>
								<td align="center"><div style="border-bottom: 1px solid; padding-bottom: 2px; margin-bottom: 2px;">', $txt['permissions_option_off'], '</div></td>
								<td align="center"><div style="border-bottom: 1px solid; padding-bottom: 2px; margin-bottom: 2px; color: red;">', $txt['permissions_option_deny'], '</div></td>';
					echo '
							</tr>';
				}
			}

			$alternate = false;
			foreach ($permissionGroup['permissions'] as $permission)
			{
				// If it's hidden keep the last value.
				if ($permission['hidden'] || $permissionGroup['hidden'])
				{
					if ($permission['has_own_any'])
						echo '
									<input type="hidden" name="perm[', $permission_type['id'], '][', $permission['own']['id'], ']" value="', $permission['own']['select'] == 'denied' && !empty($modSettings['permission_enable_deny']) ? 'deny' : $permission['own']['select'], '" />
									<input type="hidden" name="perm[', $permission_type['id'], '][', $permission['any']['id'], ']" value="', $permission['any']['select'] == 'denied' && !empty($modSettings['permission_enable_deny']) ? 'deny' : $permission['any']['select'], '" />';
					else
						echo '
									<input type="hidden" name="perm[', $permission_type['id'], '][', $permission['id'], ']" value="', $permission['select'] == 'denied' && !empty($modSettings['permission_enable_deny']) ? 'deny' : $permission['select'], '" />';
				}
				else
				{
					echo '
							<tr class="', $alternate ? 'windowbg' : 'windowbg2', '">
								<td valign="top" width="10" style="padding-right: 1ex;">
									', $permission['show_help'] ? '<a href="' . $scripturl . '?action=helpadmin;help=permissionhelp_' . $permission['id'] . '" onclick="return reqWin(this.href);" class="help"><img src="' . $settings['images_url'] . '/helptopics.gif" alt="' . $txt['help'] . '" /></a>' : '', '
								</td>';
					if ($permission['has_own_any'])
					{
						echo '
								<td colspan="4" width="100%" valign="top" align="left">', $permission['name'], '</td>
							</tr><tr class="', $alternate ? 'windowbg' : 'windowbg2', '">
								<td></td>
								<td width="100%" class="smalltext" align="right">', $permission['own']['name'], ':</td>';

						if (empty($modSettings['permission_enable_deny']) || $context['group']['id'] == -1)
							echo '
								<td colspan="3"><input type="checkbox" name="perm[', $permission_type['id'], '][', $permission['own']['id'], ']"', $permission['own']['select'] == 'on' ? ' checked="checked"' : '', ' value="on" id="', $permission['own']['id'], '_on" class="check" /></td>';
						else
							echo '
								<td valign="top" width="10"><input type="radio" name="perm[', $permission_type['id'], '][', $permission['own']['id'], ']"', $permission['own']['select'] == 'on' ? ' checked="checked"' : '', ' value="on" id="', $permission['own']['id'], '_on" class="check" /></td>
								<td valign="top" width="10"><input type="radio" name="perm[', $permission_type['id'], '][', $permission['own']['id'], ']"', $permission['own']['select'] == 'off' ? ' checked="checked"' : '', ' value="off" class="check" /></td>
								<td valign="top" width="10"><input type="radio" name="perm[', $permission_type['id'], '][', $permission['own']['id'], ']"', $permission['own']['select'] == 'denied' ? ' checked="checked"' : '', ' value="deny" class="check" /></td>';

						echo '
							</tr><tr class="', $alternate ? 'windowbg' : 'windowbg2', '">
								<td></td>
								<td width="100%" class="smalltext" align="right" style="padding-bottom: 1.5ex;">', $permission['any']['name'], ':</td>';

						if (empty($modSettings['permission_enable_deny']) || $context['group']['id'] == -1)
							echo '
								<td colspan="3" style="padding-bottom: 1.5ex;"><input type="checkbox" name="perm[', $permission_type['id'], '][', $permission['any']['id'], ']"', $permission['any']['select'] == 'on' ? ' checked="checked"' : '', ' value="on" class="check" /></td>';
						else
							echo '
								<td valign="top" width="10" style="padding-bottom: 1.5ex;"><input type="radio" name="perm[', $permission_type['id'], '][', $permission['any']['id'], ']"', $permission['any']['select'] == 'on' ? ' checked="checked"' : '', ' value="on" onclick="document.forms.permissionForm.', $permission['own']['id'], '_on.checked = true;" class="check" /></td>
								<td valign="top" width="10" style="padding-bottom: 1.5ex;"><input type="radio" name="perm[', $permission_type['id'], '][', $permission['any']['id'], ']"', $permission['any']['select'] == 'off' ? ' checked="checked"' : '', ' value="off" class="check" /></td>
								<td valign="top" width="10" style="padding-bottom: 1.5ex;"><input type="radio" name="perm[', $permission_type['id'], '][', $permission['any']['id'], ']"', $permission['any']['select']== 'denied' ? ' checked="checked"' : '', ' value="deny" id="', $permission['any']['id'], '_deny" onclick="window.smf_usedDeny = true;" class="check" /></td>';

						echo '
							</tr>';
					}
					else
					{
						echo '
								<td valign="top" width="100%" align="left" style="padding-bottom: 2px;">', $permission['name'], '</td>';

						if (empty($modSettings['permission_enable_deny']) || $context['group']['id'] == -1)
							echo '
								<td valign="top" style="padding-bottom: 2px;"><input type="checkbox" name="perm[', $permission_type['id'], '][', $permission['id'], ']"', $permission['select'] == 'on' ? ' checked="checked"' : '', ' value="on" class="check" /></td>';
						else
							echo '
								<td valign="top" width="10" style="padding-bottom: 2px;"><input type="radio" name="perm[', $permission_type['id'], '][', $permission['id'], ']"', $permission['select'] == 'on' ? ' checked="checked"' : '', ' value="on" class="check" /></td>
								<td valign="top" width="10" style="padding-bottom: 2px;"><input type="radio" name="perm[', $permission_type['id'], '][', $permission['id'], ']"', $permission['select'] == 'off' ? ' checked="checked"' : '', ' value="off" class="check" /></td>
								<td valign="top" width="10" style="padding-bottom: 2px;"><input type="radio" name="perm[', $permission_type['id'], '][', $permission['id'], ']"', $permission['select'] == 'denied' ? ' checked="checked"' : '', ' value="deny" onclick="window.smf_usedDeny = true;" class="check" /></td>';

						echo '
					</tr>';
					}
				}
				$alternate = !$alternate;
			}

			if (!$permissionGroup['hidden'] && $has_display_content)
				echo '
							<tr class="windowbg2">
								<td colspan="5" width="100%"><div style="border-top: 1px solid; padding-bottom: 1.5ex; margin-top: 2px;">&nbsp;</div></td>
							</tr>';
		}
	echo '
						</table>
					</td>';
	}
	echo '
				</tr>';
}

function template_inline_permissions()
{
	global $context, $settings, $options, $txt, $modSettings;

	echo '
		<fieldset id="', $context['current_permission'], '_groups">
			<legend><a href="javascript:void(0);" onclick="document.getElementById(\'', $context['current_permission'], '_groups\').style.display = \'none\';document.getElementById(\'', $context['current_permission'], '_groups_link\').style.display = \'block\'; return false;">', $txt['avatar_select_permission'], '</a></legend>';
	if (empty($modSettings['permission_enable_deny']))
		echo '
			<table width="100%" border="0">';
	else
		echo '
			<div class="smalltext" style="padding: 2em;">', $txt['permissions_option_desc'], '</div>
			<table width="100%" border="0">
				<tr>
					<th align="center">', $txt['permissions_option_on'], '</th>
					<th align="center">', $txt['permissions_option_off'], '</th>
					<th align="center" style="color: red;">', $txt['permissions_option_deny'], '</th>
					<td></td>
				</tr>';
	foreach ($context['member_groups'] as $group)
	{
		echo '
				<tr>';
		if (empty($modSettings['permission_enable_deny']))
			echo '
					<td align="center"><input type="checkbox" name="', $context['current_permission'], '[', $group['id'], ']" value="on"', $group['status'] == 'on' ? ' checked="checked"' : '', ' class="check" /></td>';
		else
			echo '
					<td align="center"><input type="radio" name="', $context['current_permission'], '[', $group['id'], ']" value="on"', $group['status'] == 'on' ? ' checked="checked"' : '', ' class="check" /></td>
					<td align="center"><input type="radio" name="', $context['current_permission'], '[', $group['id'], ']" value="off"', $group['status'] == 'off' ? ' checked="checked"' : '', ' class="check" /></td>
					<td align="center"><input type="radio" name="', $context['current_permission'], '[', $group['id'], ']" value="deny"', $group['status'] == 'deny' ? ' checked="checked"' : '', ' class="check" /></td>';
		echo '
					<td', $group['is_postgroup'] ? ' style="font-style: italic;"' : '', '>', $group['name'], '</td>
				</tr>';
	}
	echo '
			</table>
		</fieldset>

		<a href="javascript:void(0);" onclick="document.getElementById(\'', $context['current_permission'], '_groups\').style.display = \'block\'; document.getElementById(\'', $context['current_permission'], '_groups_link\').style.display = \'none\'; return false;" id="', $context['current_permission'], '_groups_link" style="display: none;">[ ', $txt['avatar_select_permission'], ' ]</a>

		<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
			document.getElementById("', $context['current_permission'], '_groups").style.display = "none";
			document.getElementById("', $context['current_permission'], '_groups_link").style.display = "";
		// ]]></script>';
}

// Edit post moderation permissions.
function template_postmod_permissions()
{
	global $context, $settings, $options, $scripturl, $txt, $modSettings;

	echo '
		<form action="' . $scripturl . '?action=admin;area=permissions;sa=postmod;sesc=', $context['session_id'], '" method="post" name="postmodForm" id="postmodForm" accept-charset="', $context['character_set'], '">
			<table width="100%" border="0" cellpadding="5" cellspacing="1" class="tborder">
				<tr class="catbg">
					<td colspan="13">
						', $txt['permissions_post_moderation'], '
					</td>
				</tr>';

	// Got advanced permissions - if so warn!
	if (!empty($modSettings['permission_enable_deny']))
		echo '
				<tr class="catbg">
					<td colspan="13">
						<span class="smalltext">', $txt['permissions_post_moderation_deny_note'], '</span>
					</td>
				</tr>';

		echo '
				<tr class="titlebg">
					<td colspan="13" align="right">
						', $txt['permissions_post_moderation_select'], ':
						<select name="pid" onchange="document.forms.postmodForm.submit();">';

	foreach ($context['profiles'] as $profile)
		echo '
							<option value="', $profile['id'], '" ', $profile['id'] == $context['current_profile'] ? 'selected="selected"' : '', '>', $profile['name'], '</option>';

	echo '
						</select>
						<input type="submit" value="', $txt['go'], '" />
					</td>
				</tr>
				<tr class="catbg3">
					<td></td>
					<td align="center" colspan="3">
						', $txt['permissions_post_moderation_new_topics'], '
					</td>
					<td align="center" colspan="3">
						', $txt['permissions_post_moderation_replies_own'], '
					</td>
					<td align="center" colspan="3">
						', $txt['permissions_post_moderation_replies_any'], '
					</td>
					<td align="center" colspan="3">
						', $txt['permissions_post_moderation_attachments'], '
					</td>
				</tr>
				<tr class="titlebg">
					<td width="30%">
						', $txt['permissions_post_moderation_group'], '
					</td>
					<td align="center"><img src="', $settings['default_images_url'], '/admin/post_moderation_allow.gif" alt="', $txt['permissions_post_moderation_allow'], '" /></td>
					<td align="center"><img src="', $settings['default_images_url'], '/admin/post_moderation_moderate.gif" alt="', $txt['permissions_post_moderation_moderate'], '" /></td>
					<td align="center"><img src="', $settings['default_images_url'], '/admin/post_moderation_deny.gif" alt="', $txt['permissions_post_moderation_disallow'], '" /></td>
					<td align="center"><img src="', $settings['default_images_url'], '/admin/post_moderation_allow.gif" alt="', $txt['permissions_post_moderation_allow'], '" /></td>
					<td align="center"><img src="', $settings['default_images_url'], '/admin/post_moderation_moderate.gif" alt="', $txt['permissions_post_moderation_moderate'], '" /></td>
					<td align="center"><img src="', $settings['default_images_url'], '/admin/post_moderation_deny.gif" alt="', $txt['permissions_post_moderation_disallow'], '" /></td>
					<td align="center"><img src="', $settings['default_images_url'], '/admin/post_moderation_allow.gif" alt="', $txt['permissions_post_moderation_allow'], '" /></td>
					<td align="center"><img src="', $settings['default_images_url'], '/admin/post_moderation_moderate.gif" alt="', $txt['permissions_post_moderation_moderate'], '" /></td>
					<td align="center"><img src="', $settings['default_images_url'], '/admin/post_moderation_deny.gif" alt="', $txt['permissions_post_moderation_disallow'], '" /></td>
					<td align="center"><img src="', $settings['default_images_url'], '/admin/post_moderation_allow.gif" alt="', $txt['permissions_post_moderation_allow'], '" /></td>
					<td align="center"><img src="', $settings['default_images_url'], '/admin/post_moderation_moderate.gif" alt="', $txt['permissions_post_moderation_moderate'], '" /></td>
					<td align="center"><img src="', $settings['default_images_url'], '/admin/post_moderation_deny.gif" alt="', $txt['permissions_post_moderation_disallow'], '" /></td>
				</tr>';

	foreach ($context['profile_groups'] as $group)
	{
		echo '
				<tr>
					<td width="40%" class="windowbg">
						<span ', ($group['color'] ? 'style="color: ' . $group['color'] . '"' : ''), '>', $group['name'], '</span>';
		if (!empty($group['children']))
			echo '
						<br /><span class="smalltext">', $txt['permissions_includes_inherited'], ': &quot;', implode('&quot;, &quot;', $group['children']), '&quot;</span>';

		echo '
					</td>
					<td align="center" class="windowbg2"><input type="radio" name="new_topic[', $group['id'], ']" value="allow" ', $group['new_topic'] == 'allow' ? 'checked="checked"' : '', ' /></td>
					<td align="center" class="windowbg2"><input type="radio" name="new_topic[', $group['id'], ']" value="moderate" ', $group['new_topic'] == 'moderate' ? 'checked="checked"' : '', ' /></td>
					<td align="center" class="windowbg2"><input type="radio" name="new_topic[', $group['id'], ']" value="disallow" ', $group['new_topic'] == 'disallow' ? 'checked="checked"' : '', ' /></td>
					<td align="center" class="windowbg"><input type="radio" name="replies_own[', $group['id'], ']" value="allow" ', $group['replies_own'] == 'allow' ? 'checked="checked"' : '', ' /></td>
					<td align="center" class="windowbg"><input type="radio" name="replies_own[', $group['id'], ']" value="moderate" ', $group['replies_own'] == 'moderate' ? 'checked="checked"' : '', ' /></td>
					<td align="center" class="windowbg"><input type="radio" name="replies_own[', $group['id'], ']" value="disallow" ', $group['replies_own'] == 'disallow' ? 'checked="checked"' : '', ' /></td>
					<td align="center" class="windowbg2"><input type="radio" name="replies_any[', $group['id'], ']" value="allow" ', $group['replies_any'] == 'allow' ? 'checked="checked"' : '', ' /></td>
					<td align="center" class="windowbg2"><input type="radio" name="replies_any[', $group['id'], ']" value="moderate" ', $group['replies_any'] == 'moderate' ? 'checked="checked"' : '', ' /></td>
					<td align="center" class="windowbg2"><input type="radio" name="replies_any[', $group['id'], ']" value="disallow" ', $group['replies_any'] == 'disallow' ? 'checked="checked"' : '', ' /></td>
					<td align="center" class="windowbg"><input type="radio" name="attachment[', $group['id'], ']" value="allow" ', $group['attachment'] == 'allow' ? 'checked="checked"' : '', ' /></td>
					<td align="center" class="windowbg"><input type="radio" name="attachment[', $group['id'], ']" value="moderate" ', $group['attachment'] == 'moderate' ? 'checked="checked"' : '', ' /></td>
					<td align="center" class="windowbg"><input type="radio" name="attachment[', $group['id'], ']" value="disallow" ', $group['attachment'] == 'disallow' ? 'checked="checked"' : '', ' /></td>
				</tr>';
	}

	echo '
				<tr class="titlebg">
					<td align="right" colspan="13">
						<input type="submit" name="save_changes" value="', $txt['scheduled_tasks_save_changes'], '" />
					</td>
				</tr>
			</table>
	<p class="smalltext" style="padding-left: 10px;">
		<b>', $txt['permissions_post_moderation_legend'], ':</b><br />
		<img src="', $settings['default_images_url'], '/admin/post_moderation_allow.gif" alt="', $txt['permissions_post_moderation_allow'], '" /> - ', $txt['permissions_post_moderation_allow'], '<br />
		<img src="', $settings['default_images_url'], '/admin/post_moderation_moderate.gif" alt="', $txt['permissions_post_moderation_moderate'], '" /> - ', $txt['permissions_post_moderation_moderate'], '<br />
		<img src="', $settings['default_images_url'], '/admin/post_moderation_deny.gif" alt="', $txt['permissions_post_moderation_disallow'], '" /> - ', $txt['permissions_post_moderation_disallow'], '
	</p>';
}

?>