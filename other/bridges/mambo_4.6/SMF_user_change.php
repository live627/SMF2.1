<?php
/**********************************************************************************
* SMF_login.php                                                                   *
***********************************************************************************
* SMF: Simple Machines Forum                                                      *
* Open-Source Project Inspired by Zef Hemel (zef@zefhemel.com)                    *
* =============================================================================== *
* Software Version:           SMF 1.1.2                                         *
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

class SMF_user_change {

	function register () {
		return 'userChange';
	}

	function perform( $loginfo ) {
	
		global $db_name, $db_prefix, $database, $user_info;
		
		//Start up the integration
		$database =& mamboDatabase::getInstance();
		$mainframe =& mosMainFrame::getInstance();
		$configuration =& mamboCore::getMamboCore();
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
		
		$username = $user_info['username'];
		if (!isset($username) || $username == '')
			return;
		$password = $loginfo['password'];
		$hashed_password = sha1(strtolower($username) . un_htmlspecialchars(stripslashes($password)));

		$new_username = mosGetParam( $_POST, 'username', '' );
		$new_name = mosGetParam( $_POST, 'name', '' );
		$new_email = mosGetParam( $_POST, 'email', '' );
		
		mysql_select_db($db_name);
		
		mysql_query ("UPDATE {$db_prefix}members
						SET 
							passwd = '$hashed_password',
							memberName = '$new_username',
							realName = '$new_name',
							emailAddress = '$new_email'
						WHERE memberName = '$username'
						LIMIT 1");
						
		mysql_select_db ($configuration->get('mosConfig_db')); 
				
	}
}	
?>