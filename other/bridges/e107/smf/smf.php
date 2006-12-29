<?php
/**********************************************************************************
* smf.php                                                                         *
***********************************************************************************
* SMF: Simple Machines Forum                                                      *
* Open-Source Project Inspired by Zef Hemel (zef@zefhemel.com)                    *
* =============================================================================== *
* Software Version:           SMF 1.1                                             *
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

global $context, $pref;

@ini_set('memory_limit', '32M');

define('SMF_INTEGRATION_SETTINGS', serialize(array(
	'integrate_change_email' => 'integrate_change_email',
	'integrate_change_member_data' => 'integrate_change_member_data',
	'integrate_reset_pass' => 'integrate_reset_pass',
	'integrate_exit' => 'e107_smf_exit',
	'integrate_logout' => 'integrate_logout',
	'integrate_outgoing_email' => 'integrate_outgoing_email',
	'integrate_login' => 'integrate_login',
	'integrate_validate_login' => 'integrate_validate_login',
	'integrate_redirect' => 'integrate_redirect',
	'integrate_delete_member' => 'integrate_delete_member',
	'integrate_register' => 'integrate_register',
	'integrate_pre_load' => 'integrate_pre_load',
)));

$_SERVER['QUERY_STRING'] = str_replace('&amp;', '&', $_SERVER['QUERY_STRING']);
$_SERVER['QUERY_STRING'] = str_replace(';', '&', $_SERVER['QUERY_STRING']);
   // always include the class2.php file - this is the main e107 file
   require_once("../../class2.php");



   // Include plugin language file, check first for site's preferred language
   include_lan(e_PLUGIN."smf/languages/smf_".e_LANGUAGE.".php");

if ($pref['wrapped']=='true')   
	require_once(HEADERF);
ob_start('ob_e107fix');
ob_start();

require($pref['smf_path'] . '/index.php');
$buffer = ob_get_contents();
ob_end_clean();

if ($pref['wrapped']=='true'){
   $ns->tablerender($context['page_title'], $buffer);
   require_once(FOOTERF);
} else {
	echo ob_e107fix($buffer);
}


function ob_e107fix($buffer) {

	global $scripturl, $sc;

	$buffer = str_replace($scripturl, SITEURL.'e107_plugins/smf/smf.php', $buffer);
	$buffer = str_replace(SITEURL.'e107_plugins/smf/smf.php?action=dlattach', $scripturl.'?action=dlattach', $buffer);
	$buffer = str_replace(SITEURL.'e107_plugins/smf/smf.php?action=verificationcode', $scripturl.'?action=verificationcode', $buffer);
	$buffer = str_replace('action=admin;g=;e107=;e107_plugins=;smf=;smf_php?action=admin', 'action=admin', $buffer);
	$buffer = str_replace('name="seqnum" value="0"', 'name="seqnum" value="1"', $buffer);
	

	return $buffer;
}

function e107_smf_exit($with_output) {

	global $context, $ns, $sql, $pref, $e107_popup, $ph, $compression_level, $queryinfo, $server_support, $mySQLdefaultdb, $db_name, $mySQLprefix;
	$buffer = ob_get_contents();
	ob_end_clean();
	$pref['compression_level'] = 6;
	//If the admin has chosen Unwrapped, or the page is one that shouldn't be wrapped
	if (!$with_output || $pref['wrapped']!='true'){
		echo ob_e107fix($buffer);
		exit();
	}
	
   // Ensure the pages HTML is rendered using the theme layout.
   $ns->tablerender($context['page_title'], $buffer);
   
      // this generates all the HTML (menus etc.) after the end of the main section
   require_once(FOOTERF);

   exit();
}

function integrate_pre_load ()
{
	global $context;
	
	$context['disable_login_hashing'] = true;
}

function integrate_redirect (&$setLocation, $refresh)
{
	global $boardurl;

	if ($setLocation == '')
		$setLocation = e_PLUGIN.'smf/smf.php';
	
	$setLocation = un_htmlspecialchars(ob_e107fix($setLocation));

	return true;
}

function integrate_login ($username, $password, $cookietime)
{
	global $pref, $mySQLdefaultdb, $sql, $db_name, $e_event, $user_settings, $e107;

	if (!isset($password) || $password == '' || $password == null)
		$password = 'migrated';
	
	mysql_select_db($mySQLdefaultdb);	

	// Let's see if the user already exists in e107
	if (!$sql->db_Select("user", "*", "user_loginname = '".$username."' ")){
		//if the user doesn't exist in e107, they've already been verified by SMF, so register them into e107 as well
		$ip = $e107->getip();
		$time = time();
		$u_key = md5(uniqid(rand(), 1));
		$nid = $sql->db_Insert("user", "0, {$username}, {$username}, '', '$password', '{$u_key}', '$user_settings[emailAddress]', '', '', '', '', '".$time."', '0', '".$time."', '0', '0', '0', '0', '".$ip."', '0', '0', '', '', '0', '0', '{$username}', '', '', '', '0', '' ");
	}
	
	$sql->db_Select("user", "*", "user_loginname = '".$username."' ");
	$lode = $sql -> db_Fetch();
	$user_id = intval($lode['user_id']);
	$user_name = $lode['user_name'];
	$user_xup = $lode['user_xup'];
	$pwd = md5($lode['user_password']);
	
	$cookieval = $user_id.".".$pwd;

	if ($pref['user_tracking'] == "session") {
		$_SESSION[$pref['cookie_name']] = $cookieval;
	} else {
		cookie($pref['cookie_name'], $cookieval, (time() + 3600 * 24 * 30));
	}

	$edata_li = array("user_id" => $user_id, "user_name" => $username);
	$e_event->trigger("login", $edata_li);
	mysql_select_db($db_name);
}

function integrate_logout()
{
	global $e107, $pref, $sql, $e_event;

	$ip = $e107->getip();
	$udata=(USER === TRUE) ? USERID.".".USERNAME : "0";
	$sql->db_Update("online", "online_user_id = '0', online_pagecount=online_pagecount+1 WHERE online_user_id = '{$udata}' LIMIT 1");

	if ($pref['user_tracking'] == "session") {
		session_destroy();
		$_SESSION[$pref['cookie_name']]="";
	}

	cookie($pref['cookie_name'], "", (time() - 2592000));
}

function integrate_register($Options, $theme_vars){

	global $db_name, $db_prefix, $e107, $pref, $sql, $e_event, $mySQLdefaultdb, $tp;

	//What if the realName field isn't being used?
	if (!isset($Options['register_vars']['realName']) || $Options['register_vars']['realName']=='')
		$Options['register_vars']['realName'] = $Options['register_vars']['memberName'];
		
	$ip = $e107->getip();
	$time = time();

	mysql_select_db($mySQLdefaultdb);
	$u_key = md5(uniqid(rand(), 1));
	$username =  $Options['register_vars']['realName'];
	$loginname = $Options['register_vars']['memberName'];	
	$nid = $sql->db_Insert("user", "0, {$username}, {$loginname}, '', '". md5($Options['password']) ."', '{$u_key}', ".$Options['register_vars']['emailAddress'] .", '', '', '', '', '".$time."', '0', '".$time."', '0', '0', '0', '0', '".$ip."', '0', '0', '', '', '0', '0', ".$Options['register_vars']['realName'].", '', '', '', '0', '' ");

	mysql_select_db($db_name);
}

function integrate_outgoing_email($subject, &$message, $headers){

	global $boardurl;
	
	$message = un_htmlspecialchars(ob_e107fix($message));

	return true;
}

function integrate_validate_login($username, $password, $cookietime){

	global $db_name, $db_prefix, $mySQLdefaultdb, $sql;
	
	// Check if the user already exists in SMF.
	mysql_select_db($db_name);
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

	//OK, so no user in SMF.  Does this user exist in e107?
	else
	{
		mysql_select_db($mySQLdefaultdb);
		
		//No user in e107, either.  This guy is just guessing....
		if (!$sql->db_Select("user", "*", "user_loginname='$username'")){
			mysql_select_db($db_name);
			return false;
		}
		$row = $sql -> db_Fetch();
		//If there's no user in SMF, and the e107 user isn't activated or is banned, we don't want them logging in
		if ($row['user_ban'] != '0')
			fatal_lang_error('still_awaiting_approval');
		
		mysql_select_db($db_name);

				//There must be a result, so let's write this one into SMF....
		mysql_query("
			INSERT INTO {$db_prefix}members 
				(memberName, realName, passwd, emailAddress, dateRegistered, ID_POST_GROUP, lngfile, buddy_list, pm_ignore_list, messageLabels, personalText, websiteTitle, websiteUrl, location, ICQ, MSN, signature, avatar, usertitle, memberIP, memberIP2, secretQuestion, additionalGroups)
			VALUES ('$username', '$row[user_name]', '$row[user_password]', '$row[user_email]', $row[user_join], '4', '', '', '', '', '', '', '', '', '', '', '$row[user_signature]', '', '', '$row[user_ip]', '', '', '')");
		$memberID = db_insert_id();
		
		updateStats('member', $memberID, $row['user_name']);
			
		mysql_query("
			UPDATE {$db_prefix}log_activity 
			SET registers = registers + 1 
			WHERE date ='" . date("Y-m-d") . "' 
			LIMIT 1");
		//Retry so that the password can be migrated correctly		
		return 'retry';
	}
}

function integrate_reset_pass($old_username, $username, $password){

	global $db_name, $mySQLdefaultdb;
	
	$newpass = md5($password);

	mysql_select_db($mySQLdefaultdb);	

	$sql->db_Update("user", "user_password='$newpass', user_loginname = '$username' WHERE user_loginname='$old_username' ");

	mysql_select_db($db_name);

	return true;
}
?>
