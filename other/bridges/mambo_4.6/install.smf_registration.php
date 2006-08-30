<?php
/******************************************************************************
* install.smf_registration.php                                                                   *
*******************************************************************************
* SMF: Simple Machines Forum                                                  *
* Open-Source Project Inspired by Zef Hemel (zef@zefhemel.com)                *
* =========================================================================== *
* Software Version:           SMF 1.1 RC3                                     *
* Software by:                Simple Machines (http://www.simplemachines.org) *
* Copyright 2001-2006 by:     Lewis Media (http://www.lewismedia.com)         *
* Support, News, Updates at:  http://www.simplemachines.org                   *
*******************************************************************************
* This program is free software; you may redistribute it and/or modify it     *
* under the terms of the provided license as published by Lewis Media.        *
*                                                                             *
* This program is distributed in the hope that it is and will be useful,      *
* but WITHOUT ANY WARRANTIES; without even any implied warranty of            *
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.                        *
*                                                                             *
* See the "license.txt" file for details of the Simple Machines license.      *
* The latest version can always be found at http://www.simplemachines.org.    *
******************************************************************************/


function com_install() 
{
	global $database;

	# Set up new icons for admin menu
	$database->setQuery("
		UPDATE #__components 
		SET admin_menu_img = 'js/ThemeOffice/edit.png' 
		WHERE admin_menu_link = 'option=com_smf&task=view'");
	$iconresult[0] = $database->query();

	$database->setQuery("
		UPDATE #__components 
		SET admin_menu_img = 'js/ThemeOffice/config.png' 
		WHERE admin_menu_link = 'option=com_smf&task=config'");
	$iconresult[1] = $database->query();

	$database->setQuery("
		UPDATE #__components 
		SET admin_menu_img = 'js/ThemeOffice/user.png' 
		WHERE admin_menu_link = 'option=com_smf&task=language'");
	$iconresult[2] = $database->query();

	$database->setQuery("
		UPDATE #__components 
		SET admin_menu_img='js/ThemeOffice/credits.png' 
		WHERE admin_menu_link='option=com_smf&task=about'");
	$iconresult[3] = $database->query();

	// Show installation result to user
	echo '
<div style="text-align: center;">
	<table width="100%" border="0">
		<tr>
			<td>
				<strong>MOS-SMF Registration Component</strong><br /><br />
				This component is released under the terms and conditions of the <a href="http://www.simplemachines.org/about/license.php">Simple Machines License</a>.
			</td>
		</tr><tr>
			<td>
				<code>Installation: <font color="green">successful</font></code>
			</td>
		</tr>
	</table>
</div>';
}

?>