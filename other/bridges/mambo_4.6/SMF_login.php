<?php
/******************************************************************************
* SMF_header_include.php   (Mambo/Joomla Bridge)                                                               *
*******************************************************************************
* SMF: Simple Machines Forum                                                  *
* Open-Source Project Inspired by Zef Hemel (zef@zefhemel.com)                *
* =========================================================================== *
* Software Version:           SMF 2.0                                    *
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

// no direct access
if (!defined('_VALID_MOS'))
	die('Direct Access to this location is not allowed.');

global $db_name, $database;

	$database =& mamboDatabase::getInstance();
	$mainframe =& mosMainFrame::getInstance();

class SMF_Login {

	function register () {
		return 'goodLogin';
	}

	function perform( $loginfo ) {

		global $db_name, $db_prefix, $mosConfig_db, $mosConfig_dbprefix, $mosConfig_live_site;


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
		require_once($smf_path.'/Sources/Subs-Auth.php');			
		//Are we logging out?
		if ($_REQUEST['option']=='logout'){
			mysql_select_db($db_name);
			
			mysql_query("			
				DELETE FROM {$db_prefix}log_online
				WHERE id_member = $id_member
				LIMIT 1");
					
			// Empty the cookie! (set it in the past, and for id_member = 0)
			setLoginCookie(-3600, 0);
			mysql_select_db($mosConfig_db);

			setcookie("usercookie[username]", "", 0, "/");
			setcookie("usercookie[password]", "", 0, "/");
		
			return true;
		//If we're not logging out, then we're logging in	
		} else {
		
			mysql_select_db($db_name);
			//check to make sure this user even exists in SMF
			$get_password_hash = mysql_query ("
				SELECT passwd, id_member, id_group, lngfile, is_activated, email_address, additional_groups, member_name, password_salt
				FROM {$db_prefix}members
				WHERE member_name = '$_user'
				LIMIT 1");
			$user_settings = mysql_fetch_assoc($get_password_hash);

			$hash_input_password = sha1(strtolower($user_settings['member_name']) . un_htmlspecialchars(stripslashes($_password)));

			if ($user_settings['passwd']==$hash_input_password)
			{				
				if ($_remember)
					$cookielength = 3153600;
				else 
					$cookielength = 3600;
		

				setLoginCookie( $cookielength, $user_settings['id_member'], sha1($user_settings['passwd'] . $user_settings['password_salt']));

				mysql_select_db($mosConfig_db);
				if ($session === null) $session =& mosSession::getCurrent();
				$database =& mamboDatabase::getInstance();
				$database->setQuery( "SELECT id, gid, block, usertype"
					. "\nFROM #__users"
					. "\nWHERE username='$user_settings[member_name]' AND password='".md5($_password)."'"
				);
				if ($database->loadObject($row)) {
					if ($row->block) {
						$message = T_('Your login has been blocked. Please contact the administrator.');
						return false;
					}
				// fudge the group stuff
				//			$grp = $acl->getAroGroup( $row->id );
				//			if ($acl->is_group_child_of( $grp->name, 'Registered', 'ARO' ) ||
				//			$acl->is_group_child_of( $grp->name, 'Public Backend', 'ARO' )) {
				// fudge Authors, Editors, Publishers and Super Administrators into the Special Group
				//			$row->usertype = $grp->name;
					$session->guest = 0;
					$session->username = $username;
					$session->userid = $row->id;
					$session->usertype = $row->usertype;
					if ($row->usertype == 'Registered') $session->gid = 1;
					else $session->gid = 2;
					$session->gid = intval( $row->gid ); # what is going on here???
					$session->update();
					$currentDate = date("Y-m-d\TH:i:s");
					$query = "UPDATE #__users SET lastvisitDate='$currentDate' where id='$session->userid'";
					$database->setQuery($query);
					if (!$database->query()) {
						die($database->stderr(true));
					}
					setcookie("usercookie[username]", $user_settings['member_name'], $cookielength, "/");
					setcookie("usercookie[password]", md5($_password), $cookielength, "/");
				}
			}
			mysql_select_db($mosConfig_db);
			return true;
		}
	}
}

?>
