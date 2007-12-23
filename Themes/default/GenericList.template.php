<?php
// Version: 2.0 Beta 1; GenericList

function template_show_list($list_id = null)
{
	global $context, $settings, $options, $scripturl, $txt, $modSettings;

	// Get a shortcut to the current list.
	$list_id = $list_id === null ? $context['default_list'] : $list_id;
	$cur_list = &$context[$list_id];

	if (isset($cur_list['form']))
		echo '
	<form action="', $cur_list['form']['href'], '" method="post"', empty($cur_list['form']['name']) ? '' : ' name="'. $cur_list['form']['name'] . '" id="' . $cur_list['form']['name'] . '"', ' accept-charset="', $context['character_set'], '">';

	echo '
		<table border="0" width="', $cur_list['width'] ? $cur_list['width'] : '100%', '" cellspacing="1" cellpadding="4" class="bordercolor" align="center">';

	if (isset($cur_list['additional_rows']['top_of_list']))
		template_additional_rows('top_of_list', $cur_list);

	// Show the title of the table (if any).
	if (!empty($cur_list['title']))
		echo '
			<tr class="titlebg">
				<td colspan="', $cur_list['num_columns'], '">', $cur_list['title'], '</td>
			</tr>';

	if (isset($cur_list['additional_rows']['after_title']))
		template_additional_rows('after_title', $cur_list);

	// Show the page index (if this list doesn't intend to show all items).
	if (!empty($cur_list['items_per_page']))
		echo '
			<tr class="catbg">
				<td align="left" colspan="', $cur_list['num_columns'], '">
					<b>', $txt['pages'], ':</b> ', $cur_list['page_index'], '
				</td>
			</tr>';

	if (isset($cur_list['additional_rows']['above_column_headers']))
		template_additional_rows('above_column_headers', $cur_list);
	
	// Show the column headers.
	echo '
			<tr class="titlebg">';

	// Loop through each column and add a table header.
	foreach ($cur_list['headers'] as $col_header)
		echo '
				<th valign="top"', empty($col_header['class']) ? '' : ' class="' . $col_header['class'] . '"', empty($col_header['style']) ? '' : ' style="' . $col_header['style'] . '"', '>', empty($col_header['href']) ? '' : '<a href="' . $col_header['href'] . '">', $col_header['label'], empty($col_header['href']) ? '' : '</a>', empty($col_header['sort_image']) ? '' : ' <img src="' . $settings['images_url'] . '/sort_' . $col_header['sort_image'] . '.gif" alt="" />', '</th>';
	
	echo '
			</tr>';

	// Show a nice message informing there are no items in this list.
	if (empty($cur_list['rows']) && !empty($cur_list['no_items_label']))
		echo '
			<tr>
				<td class="windowbg" colspan="', $cur_list['num_columns'], '">(', $cur_list['no_items_label'], ')</td>
			</tr>';
	
	// Show the list rows.
	elseif (!empty($cur_list['rows']))
	{
		foreach ($cur_list['rows'] as $id => $row)
		{
			echo '
			<tr class="windowbg2" id="list_' . $list_id . '_' . $id. '">';
			foreach ($row as $row_data)
				echo '
				<td', empty($row_data['class']) ? '' : ' class="' . $row_data['class'] . '"', empty($row_data['style']) ? '' : ' style="' . $row_data['style'] . '"', '>', $row_data['value'], '</td>';
			echo '
			</tr>';
		}
	}

	if (isset($cur_list['additional_rows']['below_table_data']))
		template_additional_rows('below_table_data', $cur_list);

	// Show the page index again.
	if (!empty($cur_list['items_per_page']))
		echo '
			<tr class="catbg">
				<td align="left" colspan="', $cur_list['num_columns'], '">
					<b>', $txt['pages'], ':</b> ', $cur_list['page_index'], '
				</td>
			</tr>';
	
	if (isset($cur_list['additional_rows']['bottom_of_list']))
		template_additional_rows('bottom_of_list', $cur_list);

	echo '
		</table>';

	if (isset($cur_list['form']))
	{
		foreach ($cur_list['form']['hidden_fields'] as $name => $value)
			echo '
		<input type="hidden" name="', $name, '" value="', $value, '" />';
		echo '
	</form>';
	}

	if (isset($cur_list['javascript']))
		echo '
	<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
		', $cur_list['javascript'], '
	// ]]></script>';
}

function template_additional_rows($row_position, $cur_list)
{
	global $context, $settings, $options;

	foreach ($cur_list['additional_rows'][$row_position] as $row)
		echo '
			<tr', empty($row['class']) ? '' : ' class="' . $row['class'] . '"', '>
				<td', empty($row['style']) ? '' : ' style="' . $row['style'] . '"', empty($row['align']) ? '' : ' align="' . $row['align'] . '"', empty($row['valign']) ? '' : ' valign="' . $row['valign'] . '"', ' colspan="', $cur_list['num_columns'], '">', $row['value'], '</td>
			</tr>';
}

?>