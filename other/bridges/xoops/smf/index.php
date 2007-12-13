<?php
/**********************************************************************************
* index.php                                                                       *
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


/*	This file is a Xoops module for SMF.  It uses
	the following functions:

	string ob_xoopsfix(string text)
	- fixes URLs in SMF to point to Xoops module

	bool integrate_redirect (&$setLocation, $refresh)
	- sets redirection in forum for form submissions

	integrate_pre_load ()
	- starts up the SMF session variables for use in the Xoops module

	xoops_smf_exit(string $with_output)
	- exits SMF securely

	integrate_login ($user, $password, $cookietime)
	- logs in user to Xoops upon validation in SMF

	integrate_logout($user)
	- logs user out of Xoops

	integrate_outgoing_email ($subject, &$message, $headers)
	- fixes URLs in email messages to point to Xoops module

	integrate register ($regOptions, $theme_vars)
	- registers new users in Xoops

	integrate_validate_login ($username, $password, $cookietime)
	- validates user exists in Xoops, and writes the user into SMF if there is no user in SMF
	
*/

global $scripturl, $context, $sc, $xoopsTpl, $xoopsModuleConfig, $settings, $smf_header, $xoopsTpl, $xoopsOption;

$context['disable_login_hashing'] = true;  
//define the integration functions
define('SMF_INTEGRATION_SETTINGS', serialize(array(
	'integrate_change_email' => 'integrate_change_email',
	'integrate_reset_pass' => 'integrate_reset_pass',
	'integrate_exit' => 'xoops_smf_exit',
	'integrate_logout' => 'integrate_logout',
	'integrate_outgoing_email' => 'integrate_outgoing_email',
	'integrate_login' => 'integrate_login',
	'integrate_validate_login' => 'integrate_validate_login',
	'integrate_redirect' => 'integrate_redirect',
	'integrate_delete_member' => 'integrate_delete_member',
	'integrate_register' => 'integrate_register',
	'integrate_pre_load' => 'integrate_pre_load',
)));

// Let's get this integration started...
include("../../mainfile.php");

// Just in case it gets flushed in the middle for any reason..
ob_start('ob_xoopsfix');
ob_start();
	$xoopsOption['template_main'] = 'smf_index.html';
		require(XOOPS_ROOT_PATH . '/header.php');

$xoopsOption['show_rblock'] = 1;
$sc = $_SESSION['rand_code'];

require($xoopsModuleConfig['smf_path'] . '/index.php');

$buffer = ob_get_contents();
ob_end_clean();
ob_start();

// --- This means that the buffer may be xoopsfix'd twice - see above ob_start.
echo ob_xoopsfix($buffer);

if (!in_array('main', $context['template_layers']))
{
	mysql_select_db($db_name);	
	die;
}

function ob_xoopsfix($buffer)
{
	global $scripturl, $sc;

	$buffer = str_replace($scripturl, XOOPS_URL . '/modules/smf/index.php', $buffer);
	$buffer = str_replace('name="seqnum" value="0"', 'name="seqnum" value="1"', $buffer);

	return $buffer;
}

function integrate_redirect (&$setLocation, $refresh)
{
	global $boardurl;

	if ($setLocation == '')
		$setLocation = XOOPS_URL . '/modules/smf/index.php';
	
	$setLocation = un_htmlspecialchars(ob_xoopsfix($setLocation));

	return true;
}

function integrate_outgoing_email($subject, &$message, $headers)
{

	global $boardurl;
	
	$myurl = XOOPS_URL . '/modules/smf/index.php';
	
	$message = un_htmlspecialchars(ob_xoopsfix($message));

	return true;
}

function integrate_pre_load ()
{
	global $modSettings, $sc, $context;

	loadSession();
	cleanRequest();

	//Turn off compressed output
	$modSettings['enableCompressedOutput'] = '0';
	//Turn off local cookies
	$modSettings['localCookies'] = '0';
	//Turn off SEF in SMF
	$modSettings['queryless_urls'] = '';
	
	if (isset($_GET['sesc']))
		$_SESSION['rand_code'] = $_GET['sesc'];

	if (isset($_POST['sc']))
		$_SESSION['rand_code'] = $_POST['sc'];

	$sc = $_SESSION['rand_code'];
	$_SESSION['USER_AGENT'] = $_SERVER['HTTP_USER_AGENT'];
	$modSettings['disableCheckUA'] = true;
	
	if (!isset($_SESSION['rand_code']))
		$_SESSION['rand_code'] = md5(session_id() . rand());
	
	$context['disable_login_hashing'] = true;
	$_SESSION['old_url'] = XOOPS_URL . '/modules/smf/index.php';
}

function xoops_smf_exit($with_output)
{
	global $xoopsUser, $xoopsUserIsAdmin, $xoopsConfig, $xoopsLogger;
	global $xoopsOption, $xoopsTpl, $sc, $smf_header, $scripturl, $settings, $xoopsModuleConfig;
	global $context;

	$buffer = ob_get_contents();
	ob_end_clean();


	$smf_header = '<script language="JavaScript" type="text/javascript" src="'. $settings['default_theme_url']. '/scripts/script.js?fin11"></script>
	<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
		var smf_theme_url = "' . $settings['theme_url'] . '";
		var smf_default_theme_url = "' . $settings['default_theme_url'] . '";
		var smf_images_url = "' . $settings['images_url'] . '";
		var smf_scripturl = "' . ob_xoopsfix($scripturl) . '";
		var smf_session_id = "'. $context['session_id'] . '";
	// ]]></script>
	<title>' . $context['page_title'] . '</title>';
	
	if (!$with_output || $xoopsModuleConfig['wrapped']==0)
	{
		echo $buffer;
		exit;
	}
	$xoopsTpl->assign('xoops_module_header', $smf_header);
	echo $buffer;

//	$xoopsTpl->assign('smf_content', ob_xoopsfix($buffer));	
	require(XOOPS_ROOT_PATH . '/footer.php');
	die;
}

function integrate_login ($username, $password, $cookietime)
{
	global $xoopsConfig, $db_name, $user_settings;
	
	//Get the user from Xoops
	mysql_select_db(XOOPS_DB_NAME);
	$pwd = $_REQUEST['passwrd'] != '' ? md5($_REQUEST['passwrd']) : 'migrated';
	$sess_id = $_COOKIE[session_name()];
	
	$request = mysql_query("
		SELECT uid, theme
		FROM " . XOOPS_DB_PREFIX . "_users
		WHERE uname = '$username'");
	$user = mysql_fetch_assoc($request);

	//What?  No user in Xoops?
	if ($user === false || mysql_num_rows($request) === 0){
		mysql_query("
			INSERT INTO " . XOOPS_DB_PREFIX . "_users
				(name, uname, email, pass, user_regdate)
			VALUES ('" . (isset($user_settings['member_name']) ? $user_settings['member_name'] : $user_settings['member_name']) . "', '" . $user_settings['member_name'] . "', '" . $user_settings['email_address'] . "', '$pwd', '" . $user_settings['date_registered'] . "')");

		$xoops_id = mysql_insert_id();

		mysql_query( "
			INSERT INTO " . XOOPS_DB_PREFIX . "_groups_users_link
				(groupid, uid)
			VALUES ('2', '$xoops_id');");
			
		mysql_free_result($request);
		//Now there is definitely a user there
		$request = mysql_query("
			SELECT uid, theme
			FROM " . XOOPS_DB_PREFIX . "_users
			WHERE uname = '$username'");
		$user = mysql_fetch_assoc($request);		
	}
	
	mysql_free_result($request);

	$request = mysql_query("
		SELECT *
		FROM " . XOOPS_DB_PREFIX . "_groups_users_link
		WHERE uid = '$user[uid]'");
	$group = mysql_fetch_array($request);
	mysql_free_result($request);

	
$member_handler =& xoops_gethandler('member');
$myts =& MyTextsanitizer::getInstance();

include_once XOOPS_ROOT_PATH.'/class/auth/authfactory.php';
include_once XOOPS_ROOT_PATH.'/language/'.$xoopsConfig['language'].'/user.php';
$xoopsAuth =& XoopsAuthFactory::getAuthConnection($myts->addSlashes($username));
$user = $xoopsAuth->authenticate($myts->addSlashes($username), $myts->addSlashes($_REQUEST['passwrd']));

if (false != $user) {
    if (0 == $user->getVar('level')) {
        redirect_header(XOOPS_URL.'/index.php', 5, _US_NOACTTPADM);
        exit();
    }
    if ($xoopsConfig['closesite'] == 1) {
        $allowed = false;
        foreach ($user->getGroups() as $group) {
            if (in_array($group, $xoopsConfig['closesite_okgrp']) || XOOPS_GROUP_ADMIN == $group) {
                $allowed = true;
                break;
            }
        }
        if (!$allowed) {
            redirect_header(XOOPS_URL.'/index.php', 1, _NOPERM);
            exit();
        }
    }
    $user->setVar('last_login', time());
    if (!$member_handler->insertUser($user)) {
    }
    $_SESSION = array();
    $_SESSION['xoopsUserId'] = $user->getVar('uid');
    $_SESSION['xoopsUserGroups'] = $user->getGroups();
    if ($xoopsConfig['use_mysession'] && $xoopsConfig['session_name'] != '') {
        setcookie($xoopsConfig['session_name'], session_id(), time()+(60 * $xoopsConfig['session_expire']), '/',  '', 0);
    }
    $user_theme = $user->getVar('theme');
    if (in_array($user_theme, $xoopsConfig['theme_set_allowed'])) {
        $_SESSION['xoopsUserTheme'] = $user_theme;
    }
}
	mysql_select_db($db_name);
}

function integrate_validate_login ($username, $password, $cookietime)
{

	global $xoopsConfig, $db_name, $db_prefix;

	// Check if the user already exists in SMF.
	mysql_select_db($db_name);

	$request = mysql_query ("
		SELECT id_member
		FROM {$db_prefix}members
		WHERE member_name = '$username'
		LIMIT 1");
	$smf_user = mysql_fetch_assoc($request);
	
	if ($smf_user !== false && mysql_num_rows($request) === 1)
	{
		mysql_free_result($request);
		return false;
	}

	//OK, so no user in SMF.  Does this user exist in Xoops?
	else
	{
		mysql_select_db(XOOPS_DB_NAME);

		$request = mysql_query ("
			SELECT uname, pass, email, user_regdate
			FROM " . XOOPS_DB_PREFIX . "_users
			WHERE uname = '$username'
			LIMIT 1");

		//No user in Xoops, either.  This guy is just guessing....
		if ($request === false || mysql_num_rows($request) === 0)
			return false;

		$xoops_user = mysql_fetch_assoc($request);
		mysql_free_result($request);


		//There must be a result, so let's write this one into SMF....
		mysql_select_db($db_name);

		mysql_query ("
			INSERT INTO {$db_prefix}members 
				(member_name, real_name, passwd, email_address, date_registered, lngfile, buddy_list, pm_ignore_list, message_labels, personal_text, website_title, website_url, location, icq, msn, signature, avatar, usertitle, member_ip, member_ip2, secret_question, additional_groups) 
			VALUES ('$username', '$xoops_user[uname]', '$xoops_user[pass]', '$xoops_user[email]', '$xoops_user[user_regdate]', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '')");
		$memberID = db_insert_id();
		
		updateStats('member', $memberID, $xoops_user['uname']);
			
		mysql_query("
			UPDATE {$db_prefix}log_activity 
			SET registers = registers + 1 
			WHERE date ='" . date("Y-m-d") . "' 
			LIMIT 1");
			
		return 'retry';
	}
}

function integrate_logout ($username)
{
	global $xoopsConfig, $db_name;

	mysql_select_db(XOOPS_DB_NAME);

	$logout = mysql_query("
		UPDATE ".XOOPS_DB_PREFIX."_session
		SET sess_data = ''
		WHERE sess_id = '" . $_COOKIE[session_name()] . "'");

	setcookie($xoopsConfig['session_name'], '', time()- 3600, '/',  '', 0);

	mysql_select_db($db_name);
}

function integrate_register ($Options, $theme_vars)
{
	global $xoopsConfig, $db_name;

	mysql_select_db(XOOPS_DB_NAME);

	mysql_query("
		INSERT INTO " . XOOPS_DB_PREFIX . "_users
			(name, uname, email, pass, user_regdate)
		VALUES (" . $Options['register_vars']['real_name']. ", " . $Options['register_vars']['member_name'] . ", " . $Options['register_vars']['email_address'] . ", '" . md5($_POST['passwrd1']) . "', " . $Options['register_vars']['date_registered'] . ")");

	$xoops_id = mysql_insert_id();

	mysql_query( "
		INSERT INTO ".XOOPS_DB_PREFIX."_groups_users_link
			(groupid, uid)
		VALUES ('2', '$xoops_id');");
	
	mysql_select_db($db_name);
}
?>