<?php
// Version: 2.0 Beta 1; GenericControls

// This function displays all the stuff you get with a richedit box - BBC, smileys etc.
function template_control_richedit($editor_id)
{
	global $context, $settings, $options, $txt, $modSettings;

	$editor_context = &$context['controls']['richedit'][$editor_id];

	// Assuming BBC code is enabled then print the buttons and some javascript to handle it.
	if ($context['show_bbc'])
	{
		echo '
			<tr>
				<td align="right"></td>
				<td valign="middle">';

		// The below array makes it dead easy to add images to this page. Add it to the array and everything else is done for you!
		$context['bbc_tags'] = array();
		$context['bbc_tags'][] = array(
			'bold' => array('code' => 'b', 'before' => '[b]', 'after' => '[/b]', 'description' => $txt['bold']),
			'italicize' => array('code' => 'i', 'before' => '[i]', 'after' => '[/i]', 'description' => $txt['italic']),
			'underline' => array('code' => 'u', 'before' => '[u]', 'after' => '[/u]', 'description' => $txt['underline']),
			'strike' => array('code' => 's', 'before' => '[s]', 'after' => '[/s]', 'description' => $txt['strike']),
			array(),
			'pre' => array('code' => 'pre', 'before' => '[pre]', 'after' => '[/pre]', 'description' => $txt['preformatted']),
			'left' => array('code' => 'left', 'before' => '[left]', 'after' => '[/left]', 'description' => $txt['left_align']),
			'center' => array('code' => 'center', 'before' => '[center]', 'after' => '[/center]', 'description' => $txt['center']),
			'right' => array('code' => 'right', 'before' => '[right]', 'after' => '[/right]', 'description' => $txt['right_align']),
		);
		$context['bbc_tags'][] = array(
			'flash' => array('code' => 'flash', 'before' => '[flash=200,200]', 'after' => '[/flash]', 'description' => $txt['flash']),
			'img' => array('code' => 'img', 'before' => '[img]', 'after' => '[/img]', 'description' => $txt['image']),
			'url' => array('code' => 'url', 'before' => '[url]', 'after' => '[/url]', 'description' => $txt['hyperlink']),
			'email' => array('code' => 'email', 'before' => '[email]', 'after' => '[/email]', 'description' => $txt['insert_email']),
			'ftp' => array('code' => 'ftp', 'before' => '[ftp]', 'after' => '[/ftp]', 'description' => $txt['ftp']),
			array(),
			'glow' => array('code' => 'glow', 'before' => '[glow=red,2,300]', 'after' => '[/glow]', 'description' => $txt['glow']),
			'shadow' => array('code' => 'shadow', 'before' => '[shadow=red,left]', 'after' => '[/shadow]', 'description' => $txt['shadow']),
			'move' => array('code' => 'move', 'before' => '[move]', 'after' => '[/move]', 'description' => $txt['marquee']),
			array(),
			'sup' => array('code' => 'sup', 'before' => '[sup]', 'after' => '[/sup]', 'description' => $txt['superscript']),
			'sub' => array('code' => 'sub', 'before' => '[sub]', 'after' => '[/sub]', 'description' => $txt['subscript']),
			'tele' => array('code' => 'tt', 'before' => '[tt]', 'after' => '[/tt]', 'description' => $txt['teletype']),
			array(),
			'table' => array('code' => 'table', 'before' => '[table]\n[tr]\n[td]', 'after' => '[/td]\n[/tr]\n[/table]', 'description' => $txt['table']),
			'code' => array('code' => 'code', 'before' => '[code]', 'after' => '[/code]', 'description' => $txt['bbc_code']),
			'quote' => array('code' => 'quote', 'before' => '[quote]', 'after' => '[/quote]', 'description' => $txt['bbc_quote']),
			array(),
			'list' => array('code' => 'list', 'before' => '[list]\n[li]', 'after' => '[/li]\n[li][/li]\n[/list]', 'description' => $txt['list']),
			'hr' => array('code' => 'hr', 'before' => '[hr]', 'description' => $txt['horizontal_rule']),
		);

		// Show the toggle?
		if (empty($modSettings['disable_wysiwyg']))
		{
			$context['bbc_tags'][1][] = array();
			$context['bbc_tags'][1]['toggle'] = array('code' => 'toggle', 'before' => '', 'description' => $txt['toggle_view']);
		}

		$found_button = false;
		// Here loop through the array, printing the images/rows/separators!
		foreach ($context['bbc_tags'][0] as $image => $tag)
		{
			// Is there a "before" part for this bbc button? If not, it can't be a button!!
			if (isset($tag['before']))
			{
				// Is this tag disabled?
				if (!empty($context['disabled_tags'][$tag['code']]))
					continue;

				$found_button = true;

				// Okay... we have the link. Now for the image and the closing </a>!
				echo '<a href="javascript:void(0);" onclick="return false;"><img id="cmd_', $tag['code'], '" src="', $settings['images_url'], '/bbc/', $image, '.gif" align="bottom" width="23" height="22" alt="', $tag['description'], '" title="', $tag['description'], '" style="background-image: url(', $settings['images_url'], '/bbc/bbc_bg.gif); margin: 1px 2px 1px 1px;" /></a>';
			}
			// I guess it's a divider...
			elseif ($found_button)
			{
				echo '<img src="', $settings['images_url'], '/bbc/divider.gif" alt="|" style="margin: 0 3px 0 3px;" />';
				$found_button = false;
			}
		}

		// Show the font drop down...
		if (!isset($context['disabled_tags']['face']))
			echo '
						<select name="sel_face" id="sel_face" style="margin-bottom: 1ex; font-size: x-small;">
							<option value="" selected="selected">', $txt['font_face'], '</option>
							<option value="courier">Courier</option>
						</select>';

		// Font sizes anyone?
		if (!isset($context['disabled_tags']['size']))
			echo '
						<select name="sel_size" id="sel_size" style="margin-bottom: 1ex; font-size: x-small;">
							<option value="" selected="selected">', $txt['font_size'], '</option>
							<option value="1">8pt</option>
							<option value="2">10pt</option>
							<option value="3">12pt</option>
							<option value="4">14pt</option>
							<option value="5">18pt</option>
							<option value="6">24pt</option>
							<option value="7">36pt</option>
						</select>';

		// Print a drop down list for all the colors we allow!
		if (!isset($context['disabled_tags']['color']))
			echo ' <select name="sel_color" id="sel_color" style="margin-bottom: 1ex; font-size: x-small;">
							<option value="" selected="selected">', $txt['change_color'], '</option>
							<option value="black">', $txt['black'], '</option>
							<option value="red">', $txt['red'], '</option>
							<option value="yellow">', $txt['yellow'], '</option>
							<option value="pink">', $txt['pink'], '</option>
							<option value="green">', $txt['green'], '</option>
							<option value="orange">', $txt['orange'], '</option>
							<option value="purple">', $txt['purple'], '</option>
							<option value="blue">', $txt['blue'], '</option>
							<option value="beige">', $txt['beige'], '</option>
							<option value="brown">', $txt['brown'], '</option>
							<option value="teal">', $txt['teal'], '</option>
							<option value="navy">', $txt['navy'], '</option>
							<option value="maroon">', $txt['maroon'], '</option>
							<option value="limeGreen">', $txt['lime_green'], '</option>
						</select>';
		echo '<br />';

		$found_button = false;
		// Print the buttom row of buttons!
		foreach ($context['bbc_tags'][1] as $image => $tag)
		{
			if (isset($tag['before']))
			{
				// Is this tag disabled?
				if (!empty($context['disabled_tags'][$tag['code']]))
					continue;

				$found_button = true;

				// Okay... we have the link. Now for the image and the closing </a>!
				echo '<a href="javascript:void(0);" onclick="return false;"><img id="cmd_', $tag['code'], '" src="', $settings['images_url'], '/bbc/', $image, '.gif" align="bottom" width="23" height="22" alt="', $tag['description'], '" title="', $tag['description'], '" style="background-image: url(', $settings['images_url'], '/bbc/bbc_bg.gif); margin: 1px 2px 1px 1px;" /></a>';
			}
			// I guess it's a divider...
			elseif ($found_button)
			{
				echo '<img src="', $settings['images_url'], '/bbc/divider.gif" alt="|" style="margin: 0 3px 0 3px;" />';
				$found_button = false;
			}
		}

		echo '
				</td>
			</tr>';
	}

	// Now start printing all of the smileys.
	if (!empty($context['smileys']['postform']) && !$editor_context['disable_smiley_box'])
	{
		echo '
			<tr>
				<td align="right"></td>
				<td valign="middle">';

		// Show each row of smileys ;).
		foreach ($context['smileys']['postform'] as $smiley_row)
		{
			foreach ($smiley_row['smileys'] as $smiley)
				echo '
					<a href="javascript:void(0);"><img src="', $settings['smileys_url'], '/', $smiley['filename'], '" id="sml_' . $smiley['filename'] . '" align="bottom" alt="', $smiley['description'], '" title="', $smiley['description'], '" /></a>';

			// If this isn't the last row, show a break.
			if (empty($smiley_row['last']))
				echo '<br />';
		}

		// If the smileys popup is to be shown... show it!
		if (!empty($context['smileys']['popup']))
			echo '
					<a href="javascript:editorHandle', $editor_id, '.showMoreSmileys(\'', $editor_id, '\', \'', $txt['more_smileys_title'], '\', \'', $txt['more_smileys_pick'], '\', \'', $txt['more_smileys_close_window'], '\', \'', $settings['theme_url'], '\');">[', $txt['more_smileys'], ']</a>';

		echo '
				</td>
			</tr>';
	}

	// Finally the most important bit - the actual text box to write in!
	echo '
			<tr>
				<td valign="top" align="right"></td>
				<td>
					<textarea class="editor" name="', $editor_id, '" id="', $editor_id, '" rows="', $editor_context['rows'], '" cols="', $editor_context['columns'], '" onselect="storeCaret(this);" onclick="storeCaret(this);" onkeyup="storeCaret(this);" onchange="storeCaret(this);" tabindex="', $context['tabindex']++, '" style="width: ', $editor_context['width'], '; height: ', $editor_context['height'], ';', isset($context['post_error']['no_message']) || isset($context['post_error']['long_message']) ? 'border: 1px solid red;' : '', '">', $editor_context['value'], '</textarea>
					<input type="hidden" name="', $editor_id, '_mode" id="', $editor_id, '_mode" value="', $editor_context['rich_active'] ? 1 : 0, '" />
				</td>
			</tr>';

	// Now it's all drawn out we'll actually setup the box.
	echo '
			<tr><td colspan="2">
	<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
		var editorHandle', $editor_id, ' = new smfEditor(\'', $context['session_id'], '\', \'', $editor_id, '\', ', $editor_context['rich_active'] ? 'true' : 'false', ', \'', $editor_context['rich_active'] ? $editor_context['rich_value'] : '', '\', \'', $editor_context['width'], '\', \'', $editor_context['height'], '\');';

	// Create the controls.
	if (!empty($context['bbc_tags']))
	{
		foreach ($context['bbc_tags'] as $row)
			foreach ($row as $image => $tag)
			{
				if (isset($tag['before']) && empty($context['disabled_tags'][$tag['code']]))
					echo '
					editorHandle', $editor_id, '.addButton(\'', $tag['code'], '\', \'', $tag['before'], '\', \'', empty($tag['after']) ? '' : $tag['after'], '\');';
			}
	}

	// Setup the smileys.
	if (!empty($context['smileys']['postform']) && !$editor_context['disable_smiley_box'])
	{
		foreach ($context['smileys']['postform'] as $row)
			foreach ($row['smileys'] as $smiley)
				echo '
					editorHandle', $editor_id, '.addSmiley(\'', $smiley['code'], '\', \'', $smiley['filename'], '\', \'', $smiley['js_description'], '\');';
	}

	// Setup the data for the popup smileys.
	if (!empty($context['smileys']['popup']) && !$editor_context['disable_smiley_box'])
	{
		echo '
		var smileys = [';
		foreach ($context['smileys']['popup'] as $smiley_row)
		{
			echo '
					[';
			foreach ($smiley_row['smileys'] as $smiley)
			{
				echo '
						["', $smiley['code'], '","', $smiley['filename'], '","', $smiley['js_description'], '"]';
				if (empty($smiley['last']))
					echo ',';
			}

			echo ']';
			if (empty($smiley_row['last']))
				echo ',';
		}
		echo ']';
	}

	// Create the drop downs and then initialise my friend!
	echo '
		editorHandle', $editor_id, '.addSelect(\'face\');
		editorHandle', $editor_id, '.addSelect(\'size\');
		editorHandle', $editor_id, '.addSelect(\'color\');
		smf_editorArray[smf_editorArray.length] = editorHandle', $editor_id, ';
	// ]]></script>
			</td></tr>';
}

// Display an auto suggest box.
function template_control_autosuggest($suggest_id)
{
	global $context, $settings, $options, $txt, $modSettings;

	$suggest_context = &$context['controls']['autosuggest'][$suggest_id];

	echo '
	<input autocomplete="off" type="text" name="', $suggest_id, '" id="', $suggest_id, '" value="', $suggest_context['value'], '" tabindex="', $context['tabindex']++, '" size="', $suggest_context['size'], '" style="width: ', $suggest_context['width'], ';" />';

	if (!empty($suggest_context['button']))
		echo '
	<input type="submit" name="', $suggest_id, '_submit" value="', $suggest_context['button'], '" onclick="return suggestHandle', $suggest_id, '.onSubmit();"/>';

	echo '
	<div class="auto_suggest_div" id="suggest_div_', $suggest_id, '" style="visibility: hidden;"></div>
	<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
		var suggestHandle', $suggest_id, ' = new smfSuggest(\'', $context['session_id'], '\', \'', $suggest_id, '\');';

	if (!empty($suggest_context['callbacks']))
		foreach ($suggest_context['callbacks'] as $type => $function)
			echo '
			suggestHandle', $suggest_id, '.registerCallback(\'', $type, '\', ', $function, ');';
	echo '
	// ]]></script>';
}

?>