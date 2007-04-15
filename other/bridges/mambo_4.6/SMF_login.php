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
				WHERE ID_MEMBER = $ID_MEMBER
				LIMIT 1");
					
			// Empty the cookie! (set it in the past, and for ID_MEMBER = 0)
			setLoginCookie(-3600, 0);
			mysql_select_db($mosConfig_db);

			setcookie("usercookie[username]", "", 0, "/");
			setcookie("usercookie[password]", "", 0, "/");
		
			return true;
		//If we're not logging out, then we're logging in	
		} else {		
			mysql_select_db($db_name);
			//check to make sure this user even exists in SMF
			$request = mysql_query("
				SELECT ID_MEMBER
				FROM {$db_prefix}members
				WHERE memberName = '$username'
				LIMIT 1");
			if ($request !== false && mysql_num_rows($request) === 1)
			{
				mysql_free_result($request);
				return false;
			}

			//OK, so no user in SMF.  Does this user exist in Mambo?
			else
			{
				mysql_select_db($mosConfig_db);

				//!!! How about sendEmail and activation?
				$request = mysql_query("
					SELECT name, password, email, UNIX_TIMESTAMP(registerDate) AS dateRegistered, activation
					FROM {$mosConfig_dbprefix}users
					WHERE username = '$username'");

				//No user in Mambo, either.  This guy is just guessing....
				if (mysql_num_rows($request) === 0){
					mysql_select_db($db_name);
					return false;
				}

				$mos_user = mysql_fetch_assoc($request);
				mysql_free_result($request);
		
				//If there's no user in SMF, and the Mambo/Joomla user isn't activated, we don't want them logging in
				if ($mos_user['activation']!='')
					fatal_lang_error('still_awaiting_approval');

				mysql_select_db($db_name);

				//Do we want to use real names on the forum?
				if ($use_realname == 'true')
					$name = $mos_user['name'];
				else
					$name = $username;

				//There must be a result, so let's write this one into SMF....
				mysql_query("
					INSERT INTO {$db_prefix}members 
						(memberName, realName, passwd, emailAddress, dateRegistered, ID_POST_GROUP, lngfile, buddy_list, pm_ignore_list, messageLabels, personalText, websiteTitle, websiteUrl, location, ICQ, MSN, signature, avatar, usertitle, memberIP, memberIP2, secretQuestion, additionalGroups)
					VALUES ('$username', '$name', '$mos_user[password]', '$mos_user[email]', $mos_user[dateRegistered], '4', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '')");
				$memberID = db_insert_id();
		
				updateStats('member', $memberID, $name);
			
				mysql_query("
					UPDATE {$db_prefix}log_activity 
					SET registers = registers + 1 
					WHERE date ='" . date("Y-m-d") . "' 
					LIMIT 1");
				
				return 'retry';
			}
			
			$get_password_hash = mysql_query ("
				SELECT passwd, ID_MEMBER, ID_GROUP, lngfile, is_activated, emailAddress, additionalGroups, memberName, passwordSalt
				FROM {$db_prefix}members
				WHERE memberName = '$_user'
				LIMIT 1");
			$user_settings = mysql_fetch_assoc($get_password_hash);

			$hash_input_password = sha1(strtolower($user_settings['memberName']) . un_htmlspecialchars(stripslashes($_password)));

			if ($user_settings['passwd']==$hash_input_password)
			{				
				if ($_remember)
					$cookielength = 3153600;
				else 
					$cookielength = 3600;
		

				setLoginCookie( $cookielength, $user_settings['ID_MEMBER'], sha1($user_settings['passwd'] . $user_settings['passwordSalt']));

				mysql_select_db($mosConfig_db);
				if ($session === null) $session =& mosSession::getCurrent();
				$database =& mamboDatabase::getInstance();
				$database->setQuery( "SELECT id, gid, block, usertype"
					. "\nFROM #__users"
					. "\nWHERE username='$user_settings[memberName]' AND password='".md5($_password)."'"
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
					setcookie("usercookie[username]", $user_settings['memberName'], $cookielength, "/");
					setcookie("usercookie[password]", md5($_password), $cookielength, "/");
				}
			}
			mysql_select_db($mosConfig_db);
			return true;
		}
	}
}

?>
