<?php
/**********************************************************************************
* Subs-Menu.php                                                                   *
***********************************************************************************
* SMF: Simple Machines Forum                                                      *
* Open-Source Project Inspired by Zef Hemel (zef@zefhemel.com)                    *
* =============================================================================== *
* Software Version:           SMF 2.0 Alpha                                       *
* Software by:                Simple Machines (http://www.simplemachines.org)     *
* Copyright 2006-2007 by:     Simple Machines LLC (http://www.simplemachines.org) *
*           2001-2006 by:     Lewis Media (http://www.lewismedia.com)             *
* Support, News, Updates at:  http://www.simplemachines.org                       *
***********************************************************************************
* This program is free software; you may redistribute it and/or modify it under   *
* the terms of the provided license as published by Simple Machines LLC.          *
*                                                                                 *
* This program is distributed in the hope that it is and will be useful, but      *
* WITHOUT ANY WARRANTIES; without even any implied warranty of MERCHANTABILITY    *
* or FITNESS FOR A PARTICULAR PURPOSE.                                            *
*                                                                                 *
* See the "license.txt" file for details of the Simple Machines license.          *
* The latest version can always be found at http://www.simplemachines.org.        *
**********************************************************************************/

if (!defined('SMF'))
	die('Hacking attempt...');

/*	This file contains a standard way of displaying side/drop down menus for SMF.
*/

// Create a menu...
function createMenu($menuData, $menuOptions)
{
	global $context, $settings, $options, $txt, $modSettings, $scripturl;

	// Every menu gets a unique ID, these are shown in first in, first out order.
	$context['max_menu_id'] = isset($context['max_menu_id']) ? $context['max_menu_id'] + 1 : 1;

	// This will be all the data for this menu - and we'll make a shortcut to it to aid readability here.
	$context['menu_data_' . $context['max_menu_id']] = array();
	$menu_context = &$context['menu_data_' . $context['max_menu_id']];

	// What is the general action of this menu (i.e. $scripturl?action=XXXX.
	$menu_context['current_action'] = isset($menuOptions['action']) ? $menuOptions['action'] : $context['current_action'];

	// What is the current area selected?
	if (isset($menuOptions['current_area']) || isset($_GET['area']))
		$menu_context['current_area'] = isset($menuOptions['current_area']) ? $menuOptions['current_area'] : $_GET['area'];

	$include_data = false;

	// Now setup the context correctly.
	foreach ($menu_data as $section_id => $section)
	{
		// Is this enabled - or has as permission check - which fails?
		if ((isset($section['enabled']) && $section['enabled'] == false) || (isset($section['permission']) && !allowedTo($section['permission'])))
			continue;

		// Now we cycle through the sections to pick the right area.
		foreach ($section['areas'] as $area_id => $area)
		{
			// Can we do this?
			if ((!isset($area['enabled']) || $area['enabled'] != false) && (empty($area['permission']) || allowedTo($area['permission'])))
			{
				// Add it to the context... if it has some form of name!
				if (isset($area['label']) || isset($txt[$area_id]))
				{
					// If we haven't got an area then the first valid one is our choice.
					if (!isset($menu_context['current_area']))
					{
						$menu_context['current_area'] = $area_id;
						$include_data = $area;
     				}

					$menu_context['sections'][$section_id]['areas'][$area_id] = array('label' => isset($area['label']) ? $area['label'] : $txt[$area_id]);
					// Does it have a custom URL?
					if (isset($area['custom_url']))
						$context['admin_areas'][$section_id]['areas'][$area_id]['url'] = $area['custom_url'];

					// and a icon as well?
					if (isset($area['icon']))
						$context['admin_areas'][$section_id]['areas'][$area_id]['icon'] = '<img src="' . $settings['images_url'] . '/admin/' . $area['icon'] . '" alt="" />&nbsp;&nbsp;';
					else
						$context['admin_areas'][$section_id]['areas'][$area_id]['icon'] = '';

					// Did it have subsections?
					if (isset($area['subsections']))
					{
						$menu_context['sections'][$section_id]['areas'][$area_id]['subsections'] = array();
						foreach ($area['subsections'] as $sa => $sub)
							if (empty($sub[1]) || allowedTo($sub[1]))
							{
								$menu_context['sections'][$section_id]['areas'][$area_id]['subsections'][$sa] = array('label' => $sub[0]);
								// A bit complicated - but is this set?
								if ($menu_context['current_area'] == $area_id && (isset($_REQUEST['sa']) && $_REQUEST['sa'] == $sa))
									$menu_context['current_subsection'] = $sa;
							}
					}
				}

				// Is this the current section?
				if ($menu_context['current_area'] == $area_id && empty($found_section))
				{
					// Only get one section - ever.
					$found_section = true;

					// Update the context if required - as we can have areas pretending to be others. ;)
					$menu_context['current_section'] = $section_id;
					$menu_context['current_area'] = isset($area['select']) ? $area['select'] : $area_id;

					// This will be the data we return.
					$include_data = $area;
				}
			}
		}
	}

	// If still no data then return - nothing to show!
	if (empty($menu_context['sections']))
	{
		// Never happened!
		$context['max_menu_id']--;
		if ($context['max_menu_id'] == 0)
			unset($context['max_menu_id']);

		return false;
 	}

	// What type of menu is this?
	if (!isset($menuOptions['menu_type']))
	{
		$menuOptions['menu_type'] = '_' . ($options['use_side_bar'] ? 'sidebar' : 'dropdown');
		$menu_context['can_toggle_drop_down'] = isset($settings['theme_version']) && $settings['theme_version'] >= 2.0;
	}
	else
		$menu_context['can_toggle_drop_down'] = !empty($menuOptions['can_toggle_drop_down']);

	// We want a menu, but missing the stylesheet? Get the fallback stylesheet then!
	if ($context['max_menu_id'] == 1 && $menuOptions['menu_type'] == '_dropdown' && !isset($context['dropdown_html_inserted']))
	{
		$context['dropdown_html_inserted'] = true;
		if (file_exists($settings['theme_dir']. '/css/dropmenu.css'))
			$context['html_headers'] .= '<link rel="stylesheet" type="text/css" href="' . $settings['theme_url'] . '/css/dropmenu.css" />';
		else
			$context['html_headers'] .= '<link rel="stylesheet" type="text/css" href="' . $settings['default_theme_url'] . '/css/dropmenu_default.css" />';
	}

	// Almost there - load the template and add to the template layers.
	loadTemplate(isset($menuOptions['template_name']) ? $menuOptions['template_name'] : 'GenericMenu');
	$context['template_layers'][] = (isset($menuOptions['layer_name']) ? $menuOptions['layer_name'] : 'generic_menu') . $options['use_side_bar'];

	// Finally - return information on the selected item.
	return $include_data;
}

?>
