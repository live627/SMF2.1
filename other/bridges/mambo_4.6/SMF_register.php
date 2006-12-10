<?php
/**********************************************************************************
* SMF_register.php                                                                   *
***********************************************************************************
* SMF: Simple Machines Forum                                                      *
* Open-Source Project Inspired by Zef Hemel (zef@zefhemel.com)                    *
* =============================================================================== *
* Software Version:           SMF 1.1                                         *
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


// no direct access
if (!defined('_VALID_MOS'))
	die('Direct Access to this location is not allowed.');

global $db_name, $database;

	$database =& mamboDatabase::getInstance();
	$mainframe =& mosMainFrame::getInstance();

class SMF_register {

	function register () {
		return 'userRegister';
	}

	function perform( $loginfo ) {

		global $db_name, $db_prefix, $mosConfig_db, $mosConfig_dbprefix, $mosConfig_live_site, $sourcedir;


		//Start up the integration
		$database =& mamboDatabase::getInstance();
		$mainframe =& mosMainFrame::getInstance();
		
		// Get the configuration.  This will tell Mambo where SMF is, and some integration settings
		$database->setQuery("
				SELECT `variable`, `value1`
				FROM #__smf_config
				");
		$variables = $database->loadRowList();
	
		foreach ($variables as $variable){
			$variable_name = $variable[0];
			$$variable_name = $variable[1];
		}
	
		//We're going to need SSI for this
		require_once ($smf_path."/SSI.php");
		foreach ($loginfo as $k => $v){
				$$k = $v;
			}
			
		mysql_select_db($db_name);
		$possible_strings = array(
			'websiteUrl', 'websiteTitle',
			'AIM', 'YIM',
			'location', 'birthdate',
			'timeFormat',
			'buddy_list',
			'pm_ignore_list',
			'smileySet',
			'signature', 'personalText', 'avatar',
			'lngfile',
			'secretQuestion', 'secretAnswer',
			'realName'
		);
		$possible_ints = array(
			'pm_email_notify',
			'notifyTypes',
			'ICQ',
			'gender',
			'ID_THEME',
		);
		$possible_floats = array(
			'timeOffset',
		);
		$possible_bools = array(
			'notifyAnnouncements', 'notifyOnce', 'notifySendBody',
			'hideEmail', 'showOnline',
		);
	
		// Needed for isReservedName() and registerMember().
		require_once($sourcedir . '/Subs-Members.php');
			
		// Set the options needed for registration.
		$regOptions = array(
			'interface' => 'guest',
			'username' => $_POST['username'],
			'email' => $_POST['email'],
			'password' => $_POST['password'],
			'password_check' => $_POST['password2'],
			'check_reserved_name' => true,
			'check_password_strength' => true,
			'check_email_ban' => true,
			'send_welcome_email' => false,  //let Mambo handle this
			'require' => 'nothing',
			'extra_register_vars' => array(),
			'theme_vars' => array(),
		);
		
		$_POST['realName'] = $use_realname=='true' ? $_POST['name'] : $_POST['username'];
	
		// Include the additional options that might have been filled in.
		foreach ($possible_strings as $var)
			if (isset($_POST[$var]))
				$regOptions['extra_register_vars'][$var] = '\'' . $_POST[$var] . '\'';
		foreach ($possible_ints as $var)
			if (isset($_POST[$var]) && $_POST[$var]>0)
				$regOptions['extra_register_vars'][$var] = (int) $_POST[$var];
		foreach ($possible_floats as $var)
			if (isset($_POST[$var]))
				$regOptions['extra_register_vars'][$var] = (float) $_POST[$var];
		foreach ($possible_bools as $var)
			if (isset($_POST[$var]))
				$regOptions['extra_register_vars'][$var] = empty($_POST[$var]) ? 0 : 1;

		// Registration options are always default options...
		if (isset($_POST['default_options']))
			$_POST['options'] = isset($_POST['options']) ? $_POST['options'] + $_POST['default_options'] : $_POST['default_options'];
		$regOptions['theme_vars'] = isset($_POST['options']) && is_array($_POST['options']) ? $_POST['options'] : array();

		if(isReservedName($_POST['realName']))
			$regOptions['password'] = 'chocolate cake';

		//Make sure that Mambo/Joomla handles admin notification here
		$modSettings['notify_new_registration'] = '';
		
		//Register in SMF
		$memberID = registerMember($regOptions);

		//Update the SMF stats
		mysql_query("UPDATE {$db_prefix}log_activity 
				SET registers = registers + 1 
				WHERE date ='" . date("Y-m-d") . "' 
				LIMIT 1");
		mysql_select_db($mosConfig_db);
		return true;
	}
}


?>
