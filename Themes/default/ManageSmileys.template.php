<?php
// Version: 2.0 RC1; ManageSmileys

// Editing the smiley sets.
function template_editsets()
{
	global $context, $settings, $options, $scripturl, $txt, $modSettings;

	template_show_list('smiley_set_list');

	echo '<br />
	<table width="100%" cellpadding="4" cellspacing="0" border="0" align="center" class="tborder">
		<tr class="titlebg">
			<td>', $txt['smiley_sets_latest'], '</td>
		</tr>
		<tr class="windowbg2">
			<td id="smileysLatest">', $txt['smiley_sets_latest_fetch'], '</td>
		</tr>
	</table>
	<script type="text/javascript"><!-- // --><![CDATA[
		window.smfForum_scripturl = "', $scripturl, '";
		window.smfForum_sessionid = "', $context['session_id'], '";
		window.smfForum_sessionvar = "', $context['session_var'], '";
	// ]]></script>';

	if (empty($modSettings['disable_smf_js']))
		echo '
	<script type="text/javascript" src="', $scripturl, '?action=viewsmfile;filename=latest-smileys.js"></script>';

	echo '
	<script type="text/javascript"><!-- // --><![CDATA[
		function smfSetLatestSmileys()
		{
			if (typeof(window.smfLatestSmileys) != "undefined")
				setInnerHTML(document.getElementById("smileysLatest"), window.smfLatestSmileys);';

		if (!empty($context['selected_set']))
			echo '

			changeSet("', $context['selected_set'], '");';
		if (!empty($context['selected_smiley']))
			echo '
			loadSmiley(', $context['selected_smiley'], ');';

		echo '
		}';

		// Oh well, could be worse - at least it's only IE4.
		if ($context['browser']['is_ie4'])
			echo '
			add_load_event(smfSetLatestSmileys);';
		else
			echo '

			smfSetLatestSmileys();';

		echo '
	// ]]></script>';
}

// Modifying a smiley set.
function template_modifyset()
{
	global $context, $settings, $options, $scripturl, $txt, $modSettings;

	echo '
	<form action="', $scripturl, '?action=admin;area=smileys;sa=editsets" method="post" accept-charset="', $context['character_set'], '">
		<table border="0" cellspacing="0" cellpadding="4" align="center" width="80%" class="tborder">
			<tr class="titlebg">
				<td colspan="2">', $context['current_set']['is_new'] ? $txt['smiley_set_new'] : $txt['smiley_set_modify_existing'], '</td>
			</tr>';

		// If this is an existing set, and there are still un-added smileys - offer an import opportunity.
		if (!empty($context['current_set']['can_import']))
		{
			echo '
			<tr class="windowbg">
				<td align="left" colspan="2">
					<span class="smalltext">', $context['current_set']['can_import'] == 1 ? $txt['smiley_set_import_single'] : $txt['smiley_set_import_multiple'], ' <a href="', $scripturl, '?action=admin;area=smileys;sa=import;set=', $context['current_set']['id'], ';', $context['session_var'], '=', $context['session_id'], '">', $txt['here'], '</a> ', $context['current_set']['can_import'] == 1 ? $txt['smiley_set_to_import_single'] : $txt['smiley_set_to_import_multiple'], '</span>
				</td>
			</tr>';
		}

		echo '
			<tr class="windowbg2">
				<td align="right"><strong><label for="smiley_sets_name">', $txt['smiley_sets_name'], '</label>: </strong></td>
				<td><input type="text" name="smiley_sets_name" id="smiley_sets_name" value="', $context['current_set']['name'], '" class="input_text" /></td>
			</tr>
			<tr class="windowbg2">
				<td align="right"><strong><label for="smiley_sets_path">', $txt['smiley_sets_url'], '</label>: </strong></td>
				<td>
					', $modSettings['smileys_url'], '/';
		if ($context['current_set']['id'] == 'default')
			echo '<strong>default</strong><input type="hidden" name="smiley_sets_path" id="smiley_sets_path" value="default" />';
		elseif (empty($context['smiley_set_dirs']))
			echo '
					<input type="text" name="smiley_sets_path" id="smiley_sets_path" value="', $context['current_set']['path'], '" class="input_text" /> ';
		else
		{
			echo '
					<select name="smiley_sets_path" id="smiley_sets_path">';
			foreach ($context['smiley_set_dirs'] as $smiley_set_dir)
				echo '
						<option value="', $smiley_set_dir['id'], '"', $smiley_set_dir['current'] ? ' selected="selected"' : '', $smiley_set_dir['selectable'] ? '' : ' disabled="disabled"', '>', $smiley_set_dir['id'], '</option>';
			echo '
					</select> ';
		}
		echo '/..
				</td>
			</tr>
			<tr class="windowbg2">
				<td align="right"><strong><label for="smiley_sets_default">', $txt['smiley_set_select_default'], '</label>: </strong></td>
				<td><input type="checkbox" name="smiley_sets_default" id="smiley_sets_default" value="1"', $context['current_set']['selected'] ? ' checked="checked"' : '', ' class="input_check" /></td>
			</tr>';

		// If this is a new smiley set they have the option to import smileys already in the directory.
		if ($context['current_set']['is_new'] && !empty($modSettings['smiley_enable']))
			echo '
			<tr class="windowbg2">
				<td align="right"><strong><label for="smiley_sets_import">', $txt['smiley_set_import_directory'], '</label>: </strong></td>
				<td><input type="checkbox" name="smiley_sets_import" id="smiley_sets_import" value="1" class="input_check" /></td>
			</tr>';

		echo '
			<tr class="windowbg2">
				<td align="right" colspan="2">
					<input type="submit" value="', $txt['smiley_sets_save'], '" class="button_submit" />
				</td>
			</tr>
		</table>
		<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
		<input type="hidden" name="set" value="', $context['current_set']['id'], '" />
	</form>';
}

// Editing an individual smiley
function template_modifysmiley()
{
	global $context, $settings, $options, $scripturl, $txt, $modSettings;

	echo '
	<form action="', $scripturl, '?action=admin;area=smileys;sa=editsmileys" method="post" accept-charset="', $context['character_set'], '" name="smileyForm" id="smileyForm">
		<table border="0" cellspacing="0" cellpadding="4" align="center" width="80%" class="tborder">
			<tr class="titlebg">
				<td colspan="2">', $txt['smiley_modify_existing'], '</td>
			</tr>
			<tr class="windowbg2">
				<td align="right"><strong>', $txt['smiley_preview'], ': </strong></td>
				<td><img src="', $modSettings['smileys_url'], '/', $modSettings['smiley_sets_default'], '/', $context['current_smiley']['filename'], '" id="preview" alt="" /> (', $txt['smiley_preview_using'], ': <select name="set" onchange="updatePreview();">';

		foreach ($context['smiley_sets'] as $smiley_set)
			echo '
						<option value="', $smiley_set['path'], '"', $context['selected_set'] == $smiley_set['path'] ? ' selected="selected"' : '', '>', $smiley_set['name'], '</option>';

		echo '
					</select>)
				</td>
			</tr>
			<tr class="windowbg2">
				<td align="right"><strong><label for="smiley_code">', $txt['smileys_code'], '</label>: </strong></td>
				<td><input type="text" name="smiley_code" id="smiley_code" value="', $context['current_smiley']['code'], '" class="input_text" /></td>
			</tr>
			<tr class="windowbg2">
				<td align="right"><strong><label for="smiley_filename">', $txt['smileys_filename'], '</label>: </strong></td>
				<td>';
			if (empty($context['filenames']))
				echo '
					<input type="text" name="smiley_filename" id="smiley_filename" value="', $context['current_smiley']['filename'], '" class="input_text" />';
			else
			{
				echo '
					<select name="smiley_filename" id="smiley_filename" onchange="updatePreview();">';
				foreach ($context['filenames'] as $filename)
					echo '
						<option value="', $filename['id'], '"', $filename['selected'] ? ' selected="selected"' : '', '>', $filename['id'], '</option>';
				echo '
					</select>';
			}
			echo '
				</td>
			</tr>
			<tr class="windowbg2">
				<td align="right"><strong><label for="smiley_description">', $txt['smileys_description'], '</label>: </strong></td>
				<td><input type="text" name="smiley_description" id="smiley_description" value="', $context['current_smiley']['description'], '" class="input_text" /></td>
			</tr>
			<tr class="windowbg2">
				<td align="right"><strong><label for="smiley_location">', $txt['smileys_location'], '</label>: </strong></td>
				<td>
					<select name="smiley_location" id="smiley_location">
						<option value="0"', $context['current_smiley']['location'] == 0 ? ' selected="selected"' : '', '>
							', $txt['smileys_location_form'], '
						</option>
						<option value="1"', $context['current_smiley']['location'] == 1 ? ' selected="selected"' : '', '>
							', $txt['smileys_location_hidden'], '
						</option>
						<option value="2"', $context['current_smiley']['location'] == 2 ? ' selected="selected"' : '', '>
							', $txt['smileys_location_popup'], '
						</option>
					</select>
				</td>
			</tr>
			<tr class="windowbg2">
				<td align="right" colspan="2">
					<input type="submit" value="', $txt['smileys_save'], '" class="button_submit" />
					<input type="submit" name="deletesmiley" value="', $txt['smileys_delete'], '" onclick="return confirm(\'', $txt['smileys_delete_confirm'], '\');" class="button_submit" />
				</td>
			</tr>
		</table>
		<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
		<input type="hidden" name="smiley" value="', $context['current_smiley']['id'], '" />
	</form>
	<script type="text/javascript"><!-- // --><![CDATA[
		function updatePreview()
		{
			var currentImage = document.getElementById("preview");
			currentImage.src = "', $modSettings['smileys_url'], '/" + document.forms.smileyForm.set.value + "/" + document.forms.smileyForm.smiley_filename.value;
		}
	// ]]></script>';
}

// Adding a new smiley.
function template_addsmiley()
{
	global $context, $settings, $options, $scripturl, $txt, $modSettings;

	echo '
	<script type="text/javascript"><!-- // --><![CDATA[
		function swapUploads()
		{
			document.getElementById("uploadMore").style.display = document.getElementById("uploadSmiley").disabled ? "none" : "";
			document.getElementById("uploadSmiley").disabled = !document.getElementById("uploadSmiley").disabled;
		}

		function selectMethod(element)
		{
			document.getElementById("method-existing").checked = element != "upload";
			document.getElementById("method-upload").checked = element == "upload";
		}
	// ]]></script>

	<form action="', $scripturl, '?action=admin;area=smileys;sa=addsmiley" method="post" accept-charset="', $context['character_set'], '" name="smileyForm" id="smileyForm" enctype="multipart/form-data">
		<table border="0" cellspacing="0" cellpadding="4" align="center" width="80%" class="tborder">
			<tr class="titlebg">
				<td colspan="2">', $txt['smileys_add_method'], ':</td>
			</tr>
			<tr class="windowbg">
				<td width="40%"><label for="method-existing"><input type="radio" name="method" id="method-existing" value="existing" checked="checked" class="input_radio" /> ', $txt['smileys_add_existing'], '</label></td>
				<td>
					<img src="', $modSettings['smileys_url'], '/', $modSettings['smiley_sets_default'], '/', $context['filenames'][0]['id'], '" id="preview" alt="" />  (', $txt['smiley_preview_using'], ': <select name="set" onchange="updatePreview();selectMethod(\'existing\');">';

		foreach ($context['smiley_sets'] as $smiley_set)
			echo '
							<option value="', $smiley_set['path'], '"', $context['selected_set'] == $smiley_set['path'] ? ' selected="selected"' : '', '>', $smiley_set['name'], '</option>';

		echo '
						</select>)
				</td>
			</tr>
			<tr class="windowbg" valign="bottom">
				<td style="padding-bottom: 2ex;" align="right" width="40%">
					<strong><label for="smiley_filename">', $txt['smileys_filename'], '</label>: </strong>
				</td>
				<td style="padding-bottom: 2ex;" width="60%">';
	if (empty($context['filenames']))
		echo '
					<input type="text" name="smiley_filename" id="smiley_filename" value="', $context['current_smiley']['filename'], '" onchange="selectMethod(\'existing\');" class="input_text" />';
	else
	{
		echo '
						<select name="smiley_filename" id="smiley_filename" onchange="updatePreview();selectMethod(\'existing\');">';
		foreach ($context['filenames'] as $filename)
			echo '
							<option value="', $filename['id'], '"', $filename['selected'] ? ' selected="selected"' : '', '>', $filename['id'], '</option>';
		echo '
						</select>';
		echo '
						';
	}

	echo '
				</td>
			</tr>
			<tr class="windowbg2">
				<td colspan="2"><label for="method-upload"><input type="radio" name="method" id="method-upload" value="upload" class="input_radio" /> ', $txt['smileys_add_upload'], '</label></td>
			</tr>
			<tr class="windowbg2">
				<td style="padding-bottom: 2ex;" width="40%" align="right"><strong>', $txt['smileys_add_upload_choose'], ':</strong><div class="smalltext">', $txt['smileys_add_upload_choose_desc'], '</div></td>
				<td style="padding-bottom: 2ex;" width="60%"><input type="file" name="uploadSmiley" id="uploadSmiley" onchange="selectMethod(\'upload\');" class="input_file" /></td>
			</tr>
			<tr class="windowbg2">
				<td width="40%" align="right"><strong><label for="sameall">', $txt['smileys_add_upload_all'], ':</label></strong></td>
				<td width="60%"><input type="checkbox" name="sameall" id="sameall" checked="checked" class="input_check" onclick="swapUploads(); selectMethod(\'upload\');" /></td>
			</tr>
			<tr class="windowbg2" id="uploadMore" style="display: none;">
				<td colspan="2">
					<table width="100%" cellpadding="4" cellspacing="0" border="0" class="windowbg2">';
	foreach ($context['smiley_sets'] as $smiley_set)
		echo '
						<tr class="windowbg2">
							<td width="40%" align="right">', $txt['smileys_add_upload_for1'], ' <strong>', $smiley_set['name'], '</strong> ', $txt['smileys_add_upload_for2'], ':</td>
							<td width="60%"><input type="file" name="individual_', $smiley_set['name'], '" onchange="selectMethod(\'upload\');" class="input_file" /></td>
						</tr>';
	echo '
					</table>
				</td>
			</tr>
		</table>
		<br />
		<table width="80%" cellpadding="4" cellspacing="0" border="0" align="center" class="tborder">
			<tr class="titlebg">
				<td colspan="2">', $txt['smiley_new'], '</td>
			</tr>
			<tr class="windowbg2">
				<td align="right" width="40%"><strong><label for="smiley_code">', $txt['smileys_code'], '</label>: </strong></td>
				<td width="60%"><input type="text" name="smiley_code" id="smiley_code" value="" class="input_text" /></td>
			</tr>
			<tr class="windowbg2">
				<td align="right" width="40%"><strong><label for="smiley_description">', $txt['smileys_description'], '</label>: </strong></td>
				<td width="60%"><input type="text" name="smiley_description" id="smiley_description" value="" class="input_text" /></td>
			</tr>
			<tr class="windowbg2">
				<td align="right" width="40%"><strong><label for="smiley_location">', $txt['smileys_location'], '</label>: </strong></td>
				<td width="60%">
					<select name="smiley_location" id="smiley_location">
						<option value="0" selected="selected">
							', $txt['smileys_location_form'], '
						</option>
						<option value="1">
							', $txt['smileys_location_hidden'], '
						</option>
						<option value="2">
							', $txt['smileys_location_popup'], '
						</option>
					</select>
				</td>
			</tr>
			<tr class="windowbg">
				<td align="right" colspan="2"><input type="submit" value="', $txt['smileys_save'], '" class="button_submit" /></td>
			</tr>
		</table>
		<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
	</form>
	<script type="text/javascript"><!-- // --><![CDATA[
		function updatePreview()
		{
			var currentImage = document.getElementById("preview");
			currentImage.src = "', $modSettings['smileys_url'], '/" + document.forms.smileyForm.set.value + "/" + document.forms.smileyForm.smiley_filename.value;
		}
	// ]]></script>';
}

// Ordering smileys.
function template_setorder()
{
	global $context, $settings, $options, $scripturl, $txt, $modSettings;

	foreach ($context['smileys'] as $location)
	{
		echo '
	<br />
	<form action="', $scripturl, '?action=admin;area=smileys;sa=editsmileys" method="post" accept-charset="', $context['character_set'], '">
	<table border="0" cellspacing="1" cellpadding="4" align="center" width="80%" class="tborder" style="padding: 1px;">
			<tr class="titlebg">
				<td>', $location['title'], '</td>
			</tr>
			<tr class="windowbg">
				<td class="smalltext">', $location['description'], '</td>
			</tr>
			<tr class="windowbg2">
				<td>
					<strong>', empty($context['move_smiley']) ? $txt['smileys_move_select_smiley'] : $txt['smileys_move_select_destination'], '...</strong><br />';
		foreach ($location['rows'] as $row)
		{
			if (!empty($context['move_smiley']))
				echo '
					<a href="', $scripturl, '?action=admin;area=smileys;sa=setorder;location=', $location['id'], ';source=', $context['move_smiley'], ';row=', $row[0]['row'], ';reorder=1;', $context['session_var'], '=', $context['session_id'], '"><img src="', $settings['images_url'], '/smiley_select_spot.gif" alt="', $txt['smileys_move_here'], '" /></a>';

			foreach ($row as $smiley)
			{
				if (empty($context['move_smiley']))
					echo '<a href="', $scripturl, '?action=admin;area=smileys;sa=setorder;move=', $smiley['id'], '"><img src="', $modSettings['smileys_url'], '/', $modSettings['smiley_sets_default'], '/', $smiley['filename'], '" style="padding: 2px; border: 0px solid black;" alt="', $smiley['description'], '" /></a>';
				else
					echo '<img src="', $modSettings['smileys_url'], '/', $modSettings['smiley_sets_default'], '/', $smiley['filename'], '" style="padding: 2px; border: ', $smiley['selected'] ? '2px solid red' : '0px solid black', ';" alt="', $smiley['description'], '" /><a href="', $scripturl, '?action=admin;area=smileys;sa=setorder;location=', $location['id'], ';source=', $context['move_smiley'], ';after=', $smiley['id'], ';reorder=1;', $context['session_var'], '=', $context['session_id'], '" title="', $txt['smileys_move_here'], '"><img src="', $settings['images_url'], '/smiley_select_spot.gif" alt="', $txt['smileys_move_here'], '" /></a>';
			}

			echo '
					<br />';
		}
		if (!empty($context['move_smiley']))
			echo '
					<a href="', $scripturl, '?action=admin;area=smileys;sa=setorder;location=', $location['id'], ';source=', $context['move_smiley'], ';row=', $location['last_row'], ';reorder=1;', $context['session_var'], '=', $context['session_id'], '"><img src="', $settings['images_url'], '/smiley_select_spot.gif" alt="', $txt['smileys_move_here'], '" /></a>';
		echo '
				</td>
			</tr>
		</table>
		<input type="hidden" name="reorder" value="1" />
	</form>';
	}
}

// Editing Message Icons
function template_editicons()
{
	global $context, $settings, $options, $scripturl, $txt, $modSettings;

	template_show_list('message_icon_list');
	return;

	echo '
	<form action="', $scripturl, '?action=admin;area=smileys;sa=editicons" method="post" accept-charset="', $context['character_set'], '">
		<table border="0" cellspacing="1" cellpadding="4" align="center" width="100%" class="tborder">
			<tr class="titlebg">
				<td>&nbsp;</td>
				<td>', $txt['smileys_filename'], '</td>
				<td>', $txt['smileys_description'], '</td>
				<td>', $txt['icons_board'], '</td>
				<td>', $txt['smileys_modify'], '</td>
				<td width="4%">&nsbp;</td>
			</tr>';
		foreach ($context['icons'] as $icon)
			echo '
			<tr class="windowbg2">
				<td valign="top">
					<img src="', $icon['image_url'], '" alt="', $icon['title'], '" style="padding: 2px;" />
				</td><td valign="top">
					', $icon['filename'], '.gif
				</td><td valign="top" class="windowbg">
					', $icon['title'], '
				</td><td valign="top">
					', $icon['board'], '
				</td><td valign="top">
					<a href="', $scripturl, '?action=admin;area=smileys;sa=editicon;icon=', $icon['id'], '">', $txt['smileys_modify'], '</a>
				</td><td valign="top" align="center" width="4%">
					<input type="checkbox" name="checked_icons[]" value="', $icon['id'], '" class="input_check" />
				</td>
			</tr>';
		echo '
			<tr class="windowbg">
				<td colspan="6" align="right">
					<br />
					<input type="submit" name="add" value="', $txt['icons_add_new'], '" class="button_submit" />
					<input type="submit" name="delete" value="', $txt['smileys_remove'], '" onclick="return confirm(\'', $txt['icons_confirm'], '\');" class="button_submit" />
				</td>
			</tr>
		</table>
		<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
	</form>';
}

// Editing an individual message icon
function template_editicon()
{
	global $context, $settings, $options, $scripturl, $txt, $modSettings;

	echo '
	<form action="', $scripturl, '?action=admin;area=smileys;sa=editicon;icon=', $context['new_icon'] ? '0' : $context['icon']['id'], '" method="post" accept-charset="', $context['character_set'], '">
		<table border="0" cellspacing="0" cellpadding="4" align="center" width="80%" class="tborder">
			<tr class="titlebg">
				<td colspan="2">', $context['new_icon'] ? $txt['icons_new_icon'] : $txt['icons_edit_icon'], '</td>
			</tr>';
	if (!$context['new_icon'])
		echo '
			<tr class="windowbg2">
				<td align="right"><strong>', $txt['smiley_preview'], ': </strong></td>
				<td><img src="', $context['icon']['image_url'], '" alt="', $context['icon']['title'], '" /></td>
			</tr>';
	echo '
			<tr class="windowbg2">
				<td align="right"><strong><label for="icon_filename">', $txt['smileys_filename'], '</label>: </strong><br /><span class="smalltext">', $txt['icons_filename_all_gif'], '</span></td>
				<td><input type="text" name="icon_filename" id="icon_filename" value="', !empty($context['icon']['filename']) ? $context['icon']['filename'] . '.gif' : '', '" class="input_text" /></td>
			</tr>
			<tr class="windowbg2">
				<td align="right"><strong><label for="icon_description">', $txt['smileys_description'], '</label>: </strong></td>
				<td><input type="text" name="icon_description" id="icon_description" value="', !empty($context['icon']['title']) ? $context['icon']['title'] : '', '" class="input_text" /></td>
			</tr>
			<tr class="windowbg2">
				<td align="right"><strong><label for="icon_board_select">', $txt['icons_board'], '</label>: </strong></td>
				<td>
					<select name="icon_board" id="icon_board_select">
						<option value="0"', empty($context['icon']['board_id']) ? ' selected="selected"' : '', '>', $txt['icons_edit_icons_all_boards'], '</option>';

	foreach ($context['categories'] as $category)
	{
		echo '
						<optgroup label="', $category['name'], '">';
		foreach ($category['boards'] as $board)
			echo '
							<option value="', $board['id'], '"', $board['selected'] ? ' selected="selected"' : '', '>', $board['child_level'] > 0 ? str_repeat('==', $board['child_level'] - 1) . '=&gt;' : '', ' ', $board['name'], '</option>';
		echo '
						</optgroup>';
	}
	echo '
					</select>
				</td>
			</tr>
			<tr class="windowbg2">
				<td align="right"><strong><label for="icon_location">', $txt['smileys_location'], '</label>: </strong></td>
				<td>
					<select name="icon_location" id="icon_location">
						<option value="0"', empty($context['icon']['after']) ? ' selected="selected"' : '', '>', $txt['icons_location_first_icon'], '</option>';

	// Print the list of all the icons it can be put after...
	foreach ($context['icons'] as $id => $data)
		if (empty($context['icon']['id']) || $id != $context['icon']['id'])
			echo '
						<option value="', $id, '"', !empty($context['icon']['after']) && $id == $context['icon']['after'] ? ' selected="selected"' : '', '>', $txt['icons_location_after'], ': ', $data['title'], '</option>';

	echo '
					</select>
				</td>
			</tr>
			<tr class="windowbg2">
				<td align="right" colspan="2">
					<input type="submit" value="', $txt['smileys_save'], '" class="button_submit" />
				</td>
			</tr>
		</table>';

	if (!$context['new_icon'])
		echo '
		<input type="hidden" name="icon" value="', $context['icon']['id'], '" />';

	echo '
		<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
	</form>';
}

?>