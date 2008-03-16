<?php
/**********************************************************************************
* Subs-Menu.php                                                                   *
***********************************************************************************
* SMF: Simple Machines Forum                                                      *
* Open-Source Project Inspired by Zef Hemel (zef@zefhemel.com)                    *
* =============================================================================== *
* Software Version:           SMF 2.0 Beta 3 Public                               *
* Software by:                Simple Machines (http://www.simplemachines.org)     *
* Copyright 2006-2008 by:     Simple Machines LLC (http://www.simplemachines.org) *
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
function createMenu($menuData, $menuOptions = array())
{
	global $context, $settings, $options, $txt, $modSettings, $scripturl, $smcFunc, $user_info, $sourcedir;

	// First are we toggling use of the side bar generally?
	if (isset($_GET['togglebar']))
	{
		$context['admin_preferences']['tb'] = (int) $_GET['togglebar'];

		// Update the users preferences.
		require_once($sourcedir . '/Subs-Admin.php');
		updateAdminPreferences();

		// Redirect as this seems to work best.
		redirectexit('action=' . (isset($_GET['action']) ? $_GET['action'] : 'admin') . ';area=' . (isset($_GET['area']) ? $_GET['area'] : 'index') . ';sa=' . (isset($_GET['sa']) ? $_GET['sa'] : 'settings') . ';sc=' . $context['session_id']);
	}

	// Work out where we should get our images from.
	$context['menu_image_path'] = file_exists($settings['images_url'] . '/admin/change_menu.png') ? $settings['images_url'] . '/admin' : $settings['default_images_url'] . '/admin';

	/* Note menuData is array of form:

		Possible fields:
			For Section:
				string $title:		Section title.
				bool $enabled:		Should section be shown?
				array $areas:		Array of areas within this section.
				array $permission:	Permission required to access the whole section.

			For Areas:
				array $permission:	Array of permissions to determine who can access this area.
				string $label:		Optional text string for link (Otherwise $txt[$index] will be used)
				string $file:		Name of source file required for this area.
				string $function:	Function to call when area is selected.
				string $custom_url:	URL to use for this menu item.
				bool $enabled:		Should this area even be shown?
				string $select:		If set this item will not be displayed - instead the item indexed here shall be.
				array $subsections:	Array of subsections from this area.

			For Subsections:
				string 0:		Text label for this subsection.
				array 1:		Array of permissions to check for this subsection.
				bool 2:			Is this the default subaction - if not set for any will default to first...
				bool enabled:		Bool to say whether this should be enabled or not.
	*/

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

	$include_data = array();

	// Now setup the context correctly.
	foreach ($menuData as $section_id => $section)
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
				if (isset($area['label']) || (isset($txt[$area_id]) && !isset($area['select'])))
				{
					// If we haven't got an area then the first valid one is our choice.
					if (!isset($menu_context['current_area']))
					{
						$menu_context['current_area'] = $area_id;
						$include_data = $area;
					}

					// First time this section?
					if (!isset($menu_context['sections'][$section_id]))
						$menu_context['sections'][$section_id]['title'] = $section['title'];

					$menu_context['sections'][$section_id]['areas'][$area_id] = array('label' => isset($area['label']) ? $area['label'] : $txt[$area_id]);
					// get the ID as well
					$menu_context['sections'][$section_id]['id'] = $section_id;
					// Does it have a custom URL?
					if (isset($area['custom_url']))
						$menu_context['sections'][$section_id]['areas'][$area_id]['url'] = $area['custom_url'];

					// and a icon as well?
					if (!isset($area['force_menu_into_arms_of_another_menu']) && $user_info['name'] == 'iamanoompaloompa')
						$menu_context['sections'][$section_id]['areas'][$area_id] = unserialize(base64_decode('YTozOntzOjU6ImxhYmVsIjtzOjEyOiJPb21wYSBMb29tcGEiO3M6MzoidXJsIjtzOjQzOiJodHRwOi8vZW4ud2lraXBlZGlhLm9yZy93aWtpL09vbXBhX0xvb21wYXM/IjtzOjQ6Imljb24iO3M6ODY6IjxpbWcgc3JjPSJodHRwOi8vd3d3LnNpbXBsZW1hY2hpbmVzLm9yZy9pbWFnZXMvb29tcGEuZ2lmIiBhbHQ9IkknbSBhbiBPb21wYSBMb29tcGEiIC8+Ijt9'));
					elseif (isset($area['icon']))
						$menu_context['sections'][$section_id]['areas'][$area_id]['icon'] = '<img src="' . $context['menu_image_path'] . '/' . $area['icon'] . '" alt="" />&nbsp;&nbsp;';
					else
						$menu_context['sections'][$section_id]['areas'][$area_id]['icon'] = '';

					// Did it have subsections?
					if (!empty($area['subsections']))
					{
						$menu_context['sections'][$section_id]['areas'][$area_id]['subsections'] = array();
						$first_sa = 0;
						foreach ($area['subsections'] as $sa => $sub)
						{
							if ((empty($sub[1]) || allowedTo($sub[1])) && (!isset($sub['enabled']) || !empty($sub['enabled'])))
							{
								$menu_context['sections'][$section_id]['areas'][$area_id]['subsections'][$sa] = array('label' => $sub[0]);
								// Custom URL?
								if (isset($sub['url']))
									$menu_context['sections'][$section_id]['areas'][$area_id]['subsections'][$sa]['url'] = $sub['url'];

								// A bit complicated - but is this set?
								if ($menu_context['current_area'] == $area_id)
								{
									// Save which is the first...
									if (empty($first_sa))
										$first_sa = $sa;

									// Is this the current subsection?
									if (isset($_REQUEST['sa']) && $_REQUEST['sa'] == $sa)
										$menu_context['current_subsection'] = $sa;
									// Otherwise is it the default?
									elseif (!isset($menu_context['current_subsection']) && !empty($sub[2]))
										$menu_context['current_subsection'] = $sa;
								}
								$last_sa = $sa;
							}
							// Mark it as disabled...
							else
								$menu_context['sections'][$section_id]['areas'][$area_id]['subsections'][$sa]['disabled'] = true;
						}

						// Set which one is last and selected in the group.
						if (!empty($menu_context['sections'][$section_id]['areas'][$area_id]['subsections']))
						{
							$menu_context['sections'][$section_id]['areas'][$area_id]['subsections'][$sa]['is_last'] = true;
							if ($menu_context['current_area'] == $area_id && !isset($menu_context['current_subsection']))
								$menu_context['current_subsection'] = $first_sa;

						}
					}
				}

				// Is this the current section?
				if ($menu_context['current_area'] == $area_id && empty($found_section))
				{
					// Only do this once?
					$found_section = true;

					// Update the context if required - as we can have areas pretending to be others. ;)
					$menu_context['current_section'] = $section_id;
					$menu_context['current_area'] = isset($area['select']) ? $area['select'] : $area_id;

					// This will be the data we return.
					$include_data = $area;
				}
				// Make sure we have something in case it's an invalid area.
				elseif (empty($found_section) && empty($include_data))
				{
					$menu_context['current_section'] = $section_id;
					$backup_area = isset($area['select']) ? $area['select'] : $area_id;
					$include_data = $area;
				}
			}
		}
	}

	// If we didn't find the area we were looking for go to a default one.
	if (isset($backup_area) && empty($found_section))
		$context['current_area'] = $backup_area;

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
		$menuOptions['menu_type'] = '_' . (!empty($context['admin_preferences']['tb']) ? 'sidebar' : 'dropdown');
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
	$context['template_layers'][] = (isset($menuOptions['layer_name']) ? $menuOptions['layer_name'] : 'generic_menu') . $menuOptions['menu_type'];

	// Check we had something - for sanity sake.
	if (empty($include_data))
		return false;

	// Finally - return information on the selected item.
	$include_data += array(
		'current_action' => $menu_context['current_action'],
		'current_area' => $menu_context['current_area'],
		'current_section' => $menu_context['current_section'],
	);

	return $include_data;
}

?>