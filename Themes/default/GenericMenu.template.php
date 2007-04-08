<?php
// Version: 2.0 Alpha; GenericMenu

// This contains the html for the side bar of the admin center, which is used for all admin pages.
function template_generic_menu_sidebar_above()
{
	global $context, $settings, $options, $scripturl, $txt, $modSettings;

	// This is the main table - we need it so we can keep the content to the right of it.
	echo '
		<table width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-top: 0; clear: left;"><tr>
			<td width="150" valign="top" style="width: 23ex; padding-right: 10px; padding-bottom: 10px;">
				<table width="100%" cellpadding="4" cellspacing="1" border="0" class="bordercolor">';

	// What one are we rendering?
	$context['cur_menu_id'] = isset($context['cur_menu_id']) ? $context['cur_menu_id'] + 1 : 1;
	$menu_context = &$context['menu_data_' . $context['cur_menu_id']];

	// For every section that appears on the sidebar...
	$firstSection = true;
	foreach ($menu_context['sections'] as $section)
	{
		// Show the section header - and pump up the line spacing for readability.
		echo '
					<tr>
						<td class="catbg">', $section['title'];

		if ($firstSection && !empty($menu_context['can_toggle_drop_down']))
			echo '
						<a href="', $scripturl, '?action=', $menu_context['current_action'], ';area=', $menu_context['current_area'], ';sa=', $menu_context['current_section'], ';sc=', $context['session_id'], ';togglebar=0"><img style="margin: 0 0 0 5px;" src="' , $settings['images_url'] , '/admin/change_menu2.png" alt="" /></a>';
		echo '
						</td>
					</tr>
					<tr class="windowbg2">
						<td class="smalltext" style="line-height: 1.3; padding-bottom: 3ex;">';

		// For every area of this section show a link to that area (bold if it's currently selected.)
		foreach ($section['areas'] as $i => $area)
		{
			// Not supposed to be printed?
			if (empty($area['label']))
				continue;

			// Is this the current area, or just some area?
			if ($i == $menu_context['current_area'])
			{
				echo '
							<b><a href="', (isset($area['url']) ? $area['url'] : $scripturl . '?action=' . $menu_context['current_action'] . ';area=' . $i), ';sesc=', $context['session_id'], '">', $area['label'], '</a></b><br />';
				$context['tabs'] = isset($area['subsections']) ? $area['subsections'] : array();
    		}
			else
				echo '
							<a href="', (isset($area['url']) ? $area['url'] : $scripturl . '?action=' . $menu_context['current_action'] . ';area=' . $i), ';sesc=', $context['session_id'], '">', $area['label'], '</a><br />';
		}

		echo '
						</td>
					</tr>';

		$firstSection = false;
	}

	// This is where the actual "main content" area for the admin section starts.
	echo '
			</table>
		</td>
		<td valign="top">';

	// If there are any "tabs" setup, this is the place to shown them.
	if (!empty($context['tabs']))
	{
		echo '
				<table border="0" cellspacing="0" cellpadding="4" align="center" width="100%" class="tborder" ' , (isset($settings['use_tabs']) && $settings['use_tabs']) ? '' : 'style="margin-bottom: 2ex;"' , '>
					<tr class="titlebg">
						<td>';
		// Show a help item?
		if (!empty($context['tabs']['help']))
			echo '
							<a href="', $scripturl, '?action=helpadmin;help=', $context['admin_tabs']['help'], '" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt['help'], '" align="top" /></a> ';
		echo '
							', $context['admin_tabs']['title'], '
						</td>
					</tr>
					<tr class="windowbg">';

		// Shall we use the tabs?
		if (!empty($settings['use_tabs']))
		{
			// Find the selected tab
			foreach($context['tabs'] as $tab)
				if (!empty($tab['is_selected']))
					$selected_tab = $tab;
			echo '
						<td class="smalltext" style="padding: 2ex;">', !empty($selected_tab['description']) ? $selected_tab['description'] : $context['admin_tabs']['description'], '</td>
					</tr>
				</table>';

			// The admin tabs.
			echo '
				<table cellpadding="0" cellspacing="0" border="0" style="margin-left: 10px;">
					<tr>
						<td class="maintab_first">&nbsp;</td>';

			// Print out all the items in this tab.
			foreach ($context['tabs'] as $sa => $tab)
			{
				if ($menu_context['current_subsection'] == $sa)
				{
					echo '
						<td class="maintab_active_first">&nbsp;</td>
						<td valign="top" class="maintab_active_back">
							<a href="', (isset($tab['url']) ? $tab['url'] : $scripturl . '?action=' . $menu_context['current_action'] . ';area=' . $menu_context['current_area']), ';sa=', $sa, ';sesc=', $context['session_id'], '">' , $tab['label'], '</a>
						</td>
						<td class="maintab_active_last">&nbsp;</td>';
				}
				else
					echo '
						<td valign="top" class="maintab_back">
							<a href="', (isset($tab['url']) ? $tab['url'] : $scripturl . '?action=' . $menu_context['current_action'] . ';area=' . $menu_context['current_area']), ';sa=', $sa, ';sesc=', $context['session_id'], '">' , $tab['label'], '</a>
						</td>';
			}

			// the end of tabs
			echo '
						<td class="maintab_last">&nbsp;</td>
					</tr>
				</table><br />';
		}
		// ...if not use the old style
		else
		{
			echo '
						<td align="left"><b>';

			// Print out all the items in this tab.
			foreach ($context['tabs'] as $sa => $tab)
			{
				if ($menu_context['current_subsection'] == $sa)
				{
					echo '
							<img src="', $settings['images_url'], '/selected.gif" alt="*" /> <b><a href="', (isset($tab['url']) ? $tab['url'] : $scripturl . '?action=' . $menu_context['current_action'] . ';area=' . $menu_context['current_area']), ';sa=', $sa, ';sesc=', $context['session_id'], '">' , $tab['label'], '</a></b>';

					$selected_tab = $tab;
				}
				else
					echo '
							<a href="', (isset($tab['url']) ? $tab['url'] : $scripturl . '?action=' . $menu_context['current_action'] . ';area=' . $menu_context['current_area']), ';sa=', $sa, ';sesc=', $context['session_id'], '">' , $tab['label'], '</a>';

				if (empty($tab['is_last']))
					echo ' | ';
			}

			echo '
						</b></td>
					</tr>
					<tr class="windowbg">
						<td class="smalltext" style="padding: 2ex;">', isset($selected_tab['description']) ? $selected_tab['description'] : $context['admin_tabs']['description'], '</td>
					</tr>
				</table>';
		}
	}
}

// Part of the sidebar layer - closes off the main bit.
function template_generic_menu_sidebar_below()
{
	global $context, $settings, $options;

	echo '
			</td>
		</tr>
	</table>';
}

// This contains the html for the side bar of the admin center, which is used for all admin pages.
function template_generic_menu_dropdown_above()
{
	global $context, $settings, $options, $scripturl, $txt, $modSettings;

	// Which menu are we rendering?
	$context['cur_menu_id'] = isset($context['cur_menu_id']) ? $context['cur_menu_id'] + 1 : 1;
	$menu_context = &$context['menu_data_' . $context['cur_menu_id']];

	echo '
		<script language="JavaScript" type="text/javascript" src="', $settings['default_theme_url'], '/scripts/menu.js"></script>
		<div id="adm_container"><ul class="admin_menu" id="dropdown_menu_', $context['cur_menu_id'], '">';

	// Main areas first.
	foreach ($menu_context['sections'] as $section)
	{
		if (isset($section['selected']))
		{
			echo '
			<li class="chosen"><h4>', $section['title'] , '</h4>
				<ul>';
		}
		else
			echo '
			<li><h4>', $section['title'] , '</h4>
				<ul>';

		// For every area of this section show a link to that area (bold if it's currently selected.)
		foreach ($section['areas'] as $i => $area)
		{
			// Not supposed to be printed?
			if (empty($area['label']))
				continue;

			echo '
					<li>';

			// Is this the current area, or just some area?
			if ($i == $menu_context['current_area'])
				echo '
						<a class="chosen" href="', (isset($area['url']) ? $area['url'] : $scripturl . '?action=' . $menu_context['current_action'] . ';area=' . $i), ';sesc=', $context['session_id'], '">' , $area['icon'] , $area['label'], '</a>';
			else
				echo '
						<a href="', (isset($area['url']) ? $area['url'] : $scripturl . '?action=' . $menu_context['current_action'] . ';area=' . $i), ';sesc=', $context['session_id'], '">', $area['icon'] , $area['label'], '</a>';

			// Is there any subsections?
			if (!empty($area['subsections']))
			{
				echo '
						<ul>';

				foreach ($area['subsections'] as $sa => $sub)
				{
					echo '
							<li>';

					if (!empty($sub['selected']))
						echo '
								<a class="chosen" href="', (isset($area['url']) ? $area['url'] : $scripturl . '?action=' . $menu_context['current_action'] . ';area=' . $i), ';sa=', $sa, ';sesc=', $context['session_id'], '">' , $sub['label'], '</a>';
					else
						echo '
								<a href="', (isset($area['url']) ? $area['url'] : $scripturl . '?action=' . $menu_context['current_action'] . ';area=' . $i), ';sa=', $sa, ';sesc=', $context['session_id'], '">' , $sub['label'], '</a>';

					echo '
							</li>';
				}

				echo '
						</ul>';
			}

			echo '
					</li>';
		}
		echo '
				</ul>
			</li>';
	}

	echo '
			<li style="white-space: nowrap;">
				<a href="', $scripturl, '?action=', $menu_context['current_action'], ';area=', $menu_context['current_area'], ';sa=', $menu_context['current_section'], ';sc=', $context['session_id'], ';togglebar=1"><img style="margin: 4px 10px 0 0;" src="' , $settings['images_url'] , '/admin/change_menu.png" alt="" /></a>
			</li>
		</ul></div>
		<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
			menuHandle = new smfMenu("dropdown_menu_', $context['cur_menu_id'], '");
		// ]]></script>';

	// This is the main table - we need it so we can keep the content to the right of it.
	echo '
		<table width="100%" cellspacing="0" cellpadding="4" class="tborder" border="0" style="margin-top: 0; clear: left;"><tr>
			<td valign="top">';
}

// Part of the admin layer - used with admin_above to close the table started in it.
function template_generic_menu_dropdown_below()
{
	global $context, $settings, $options;

	echo '
			</td>
		</tr>
	</table>';
}

?>
