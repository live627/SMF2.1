<?php
/**********************************************************************************
* install.smf.php                                                                 *
***********************************************************************************
* SMF: Simple Machines Forum                                                      *
* Open-Source Project Inspired by Zef Hemel (zef@zefhemel.com)                    *
* =============================================================================== *
* Software Version:           SMF 1.1 RC3                                         *
* Software by:                Simple Machines (http://www.simplemachines.org)     *
* Copyright 2006 by:          Simple Machines LLC (http://www.simplemachines.org) *
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

if (!defined('_VALID_MOS'))
	die('Direct Access to this location is not allowed.');
/*
      This is the installation file for the SMF-Mambo bridge component.  It will auto-install the 
       main component, the registration component, and the login module.
*/

function com_install() 
	{
		global $database, $mosConfig_absolute_path, $_VERSION;

	$database =& mamboDatabase::getInstance();
	// Set up new icons for admin menu
	//$database->setQuery("UPDATE #__components SET admin_menu_img='js/ThemeOffice/edit.png' WHERE admin_menu_link='option=com_smf&task=view'");
	//$iconresult[0] = $database->query();
	$database->setQuery("
		UPDATE #__components 
		SET admin_menu_img = 'js/ThemeOffice/config.png' 
		WHERE admin_menu_link = 'option=com_smf&task=config'");
	$iconresult[1] = $database->query();
	//$database->setQuery("UPDATE #__components SET admin_menu_img='js/ThemeOffice/user.png' WHERE admin_menu_link='option=com_smf&task=language'");
	//$iconresult[2] = $database->query();
	//$database->setQuery("UPDATE #__components SET admin_menu_img='js/ThemeOffice/credits.png' WHERE admin_menu_link='option=com_smf&task=about'");
	//$iconresult[3] = $database->query();

	//Create the config table
	$database->setQuery("
		DROP TABLE IF EXISTS #__smf_config");
	$result[7] = $database->query();
	
	$database->setQuery("
		CREATE TABLE #__smf_config (
		`variable` VARCHAR( 20 ) NOT NULL ,
		`value1` VARCHAR( 80 ) NOT NULL ,
		`value2` VARCHAR( 50 ) ,
		`value3` VARCHAR( 50 ) ,
		`value4` VARCHAR( 50 ) ,
		INDEX ( `variable` ) 
		) TYPE = MYISAM ; ");
	$result[8] = $database->query();
	
	//Populate the config table
	
	$default_variables = array (
								'smf_path' => '..\/forum',
								'bridge_reg' => 'bridge',
								'wrapped' => 'true',
								'smf_css' => 'true',
								'synch_lang' => 'true',
								'agreement_required' => 'on',
								'im' => 'on',
								'pmOnReg' =>'on',
								'use_realname' => false,
								'cb_reg' => 'off',
								);
	
	foreach ($default_variables as $variable=>$default){
		$database->setQuery("
					INSERT INTO #__smf_config
					(`variable`, `value1`)
					VALUES ('$variable', '$default')
					");
		$result[$variable] = $database->query();
	}

	//This will sync admins in SMF to superadmins in Mambo
	$database->setQuery("
					INSERT INTO #__smf_config
					(`variable`, `value1`, `value2`)
					VALUES ('sync_group', '1', '25')
					");
	$result['admin'] = $database->query();
	
	//This is to sync everyone else.  Registered user as default.
	$count = 2;
	while ($count < 9){
		$database->setQuery("
					INSERT INTO #__smf_config
					(`variable`, `value1`, `value2`)
					VALUES ('sync_group', '$count', '18')
					");
		$result[$count+7] = $database->query();
		$count++;
	}
	

	// Show installation result to user
	echo '
<div style="text-align: center">
	<table width="100%" border="0">
		<tr>
		<td>
			<strong>SMF Bridge Component</strong><br /><br />
		</td>
		</tr>
		<tr>
			<td>
				<code>Installation: <font color="green">successful</font></code>
			</td>
		</tr>
	</table>
</div>';
}

?>