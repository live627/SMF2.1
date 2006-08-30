<?php
/******************************************************************************
* install.smf.php                                                                   *
*******************************************************************************
* SMF: Simple Machines Forum                                                  *
* Open-Source Project Inspired by Zef Hemel (zef@zefhemel.com)                *
* =========================================================================== *
* Software Version:           SMF 1.1 RC2                                     *
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

/*
      This is the installation file for the SMF-Mambo bridge component.  It will auto-install the 
       main component, the registration component, and the login module.
*/

function com_install() 
{
	global $database, $mosConfig_absolute_path;

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
	mkdir($mosConfig_absolute_path . '/components/com_smf_registration', 0777);
	mkdir($mosConfig_absolute_path . '/administrator/components/com_smf_registration', 0777);

	$rename_admin_files = array(
		'admin.smf_registration.php',
		'config.smf_registration.php',
		'install.smf_registration.php',
		'smf_registration.xml',
		'toolbar.smf_registration.html.php',
		'toolbar.smf_registration.php',
		'uninstall.smf_registration.php',
	);

	foreach ($rename_admin_files as $rename_admin_file)
		rename($mosConfig_absolute_path . '/administrator/components/com_smf/' . $rename_admin_file, $mosConfig_absolute_path . '/administrator/components/com_smf_registration/' . $rename_admin_file);

	$rename_files = array(
		'smf_registration.php',
		'smf_registration.html.php',
	);
	
	foreach ($rename_files as $rename_file)
		rename($mosConfig_absolute_path . '/administrator/components/com_smf/' . $rename_file, $mosConfig_absolute_path . '/components/com_smf_registration/' . $rename_file);
	
		
	$database->setQuery("
		INSERT INTO #__components 
			(`id`, `name`, `link`, `menuid`, `parent`, `admin_menu_link`, `admin_menu_alt`, `option`, `ordering`, `admin_menu_img`, `iscore`, `params`)
		VALUES ('', 'Simple Machines Forum Registration', 'option=com_smf_registration', 0, 0, 'option=com_smf_registration&task=config', 'Simple Machines Forum Registration', 'com_smf_registration', 0, 'js/ThemeOffice/component.png', 0, '')");
	$result[1] = $database->query();

	mkdir($mosConfig_absolute_path . '/modules/mod_smf_login', 0777);
	rename($mosConfig_absolute_path . '/administrator/components/com_smf/modules/mod_smf_login.php', $mosConfig_absolute_path . '/modules/mod_smf_login/mod_smf_login.php');
	rename($mosConfig_absolute_path . '/administrator/components/com_smf/modules/mod_smf_login.x', $mosConfig_absolute_path . '/modules/mod_smf_login/mod_smf_login.xml');

	$database->setQuery("
		INSERT INTO #__modules 
			(id, title, content, ordering, position, checked_out, checked_out_time, published, module,numnews,access, showtitle, params, iscore, client_id)
		VALUES ('', 'Login Form', '', 11, 'left', 0, '0000-00-00 00:00:00', 1, 'mod_smf_login', 0, 0, 1, 'moduleclass_sfx=\n pretext=\n posttext=\n smf_align=center\n login=2\n logout=2\n login_message=0\n logout_message=0\n greeting=1\n name=0\n smf_personal_welcome=1\n smf_notification=1\n smf_unread=1\n smf_new_answers=1\n smf_new_pms=1\n smf_loggedin_time=1', 0, 0);");
	$result[3] = $database->query();
	
	$database->setQuery("
		SELECT id
		FROM #__modules
		WHERE module = 'mod_smf_login'
		LIMIT 1");
	$id = $database->loadResult();
	
	$database->setQuery("
		INSERT INTO #__modules_menu
			(moduleid,menuid)
		VALUES ($id,'0')");
	$result[4] = $database->query();

	$database->setQuery("
		UPDATE #__modules 
		SET published = 0 
		WHERE module = 'mod_login'");
	$result[5] = $database->query();

	chmod ($mosConfig_absolute_path . '/plugins/system', 0777);	
	rename($mosConfig_absolute_path . '/administrator/components/com_smf/bots/SMF_header_include.php', $mosConfig_absolute_path . '/plugins/system/SMF_header_include.php');
	rename($mosConfig_absolute_path . '/administrator/components/com_smf/bots/SMF_header_include.x', $mosConfig_absolute_path . '/plugins/system/SMF_header_include.xml');
	
	$database->setQuery("
		INSERT INTO #__plugins 
			(id, name, element, folder, access, ordering, published, iscore, client_id, checked_out, checked_out_time, params)
		VALUES ('', 'SMF_header_include', 'SMF_header_include', 'system', 0, 0, 1, 0, 0, 0, '0000-00-00 00:00:00', '');");
	$result[6] = $database->query();

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