<?php
/**********************************************************************************
* index.php                                                                       *
***********************************************************************************
* SMF: Simple Machines Forum                                                      *
* Open-Source Project Inspired by Zef Hemel (zef@zefhemel.com)                    *
* =============================================================================== *
* Software Version:           SMF 2.0 Beta 2                                       *
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


/*	This file is a PhpNuke module for SMF.  It uses
	the following functions:

	string ob_nukefix(string text)
	-fixes URLs in SMF to point to PhpNuke module

	bool integrate_redirect (&$setLocation, $refresh)
	- sets redirection in forum for form submissions

	nuke_smf_exit(string $with_output)
	- exits SMF securely

	integrate_login ($user, $password, $cookietime)
	- logs in user to PhpNuke upon validation in SMF

	integrate_logout($user)
	- logs user out of PhpNuke

	integrate_outgoing_email ($subject, &$message, $headers)
	- fixes URLs in email messages to point to PhpNuke module

	integrate register ($regOptions, $theme_vars)
	- registers new users in PhpNuke
	
*/

global $scripturl, $context, $sc, $settings, $smf_header, $smf_path;
global $prefix, $db, $sitename, $admin, $module_name, $db_name, $db_prefix, $db_connection, $db_user, $db_passwd;
global $time_start, $maintenance, $msubject, $mmessage, $mbname, $language;
global $boardurl, $boarddir, $sourcedir, $webmaster_email, $cookiename;
global $db_server, $db_name, $db_user, $db_prefix, $db_persist, $db_error_send, $db_last_error;
global $db_connection, $modSettings, $context, $sc, $user_info, $topic, $board, $txt;

include ('modules/smf/config.php');

define('SMF_INTEGRATION_SETTINGS', serialize(array(
	'integrate_change_email' => 'integrate_change_email',
	'integrate_reset_pass' => 'integrate_reset_pass',
	'integrate_exit' => 'nuke_smf_exit',
	'integrate_logout' => 'integrate_logout',
	'integrate_outgoing_email' => 'integrate_outgoing_email',
	'integrate_login' => 'integrate_login',
	'integrate_validate_login' => 'integrate_validate_login',
	'integrate_redirect' => 'integrate_redirect',
	'integrate_delete_member' => 'integrate_delete_member',
	'integrate_register' => 'integrate_register'
)));

//Retrofit the query string
$_SERVER['QUERY_STRING'] = strtr($_SERVER['QUERY_STRING'], array('&amp;?' => '&amp;', '&?' => '&amp;' , '#' => '.'));

ob_start('nuke');

if (!isset($_REQUEST['action']) || $_REQUEST['action']!='login2')
{
	require("header.php");
	OpenTable();
}
	
require($smf_path . '/index.php');
$buffer = ob_get_contents();
ob_end_clean();

echo ob_nukefix($buffer);
require('footer.php');

function ob_nukefix($buffer)
{
	global $scripturl, $sc, $boardurl;

	$buffer = str_replace($scripturl, $_SERVER['PHP_SELF'] . '?name=smf&amp;', $buffer);
	$buffer = str_replace('&amp;?', '&amp;', $buffer);	
	$buffer = str_replace('name="seqnum" value="0"', 'name="seqnum" value="1"', $buffer);
	$buffer = str_replace('&amp;name=smf;action=admin;g=;phpnuke=;html=;modules_php?name=smf', '&amp;action=admin', $buffer);	
	$buffer = str_replace($_SERVER['PHP_SELF'] . '?name=smf&amp;action=dlattach', $boardurl . '/index.php?action=dlattach', $buffer);


	return $buffer;
}

function integrate_redirect (&$setLocation, $refresh)
{
	global $boardurl;

	if ($setLocation == '')
		$setLocation = $_SERVER['PHP_SELF'] . '?name=smf&amp;';
	
	$setLocation = un_htmlspecialchars(ob_nukefix($setLocation));

	return true;
}

function integrate_outgoing_email ($subject, &$message, $headers)
{

	global $boardurl;
	
	$myurl = $_SERVER['PHP_SELF'] . '?name=smf&amp;';
	
	$message = un_htmlspecialchars(ob_nukefix($message));

	return true;
}

function integrate_pre_load ()
{

}

function nuke_smf_exit($with_output)
{
	global $sc, $smf_header, $scripturl,$settings,$context;

	$buffer = ob_get_contents();
	ob_end_clean();
	
	if (!$with_output)
	{
		echo ob_nukefix($buffer);
		CloseTable();

		require('footer.php');
		exit;
	}
	
	echo ob_nukefix($buffer);
	CloseTable();

	require("footer.php");
	die;
}

function integrate_login ($username, $password, $cookietime)
{
	global $db_name, $dbname, $db, $prefix, $user, $cookie, $redirect;
	
	require('config.php');

	mysql_select_db($dbname);
	$request = mysql_query("
		SELECT user_password, user_id, storynum, umode, uorder, thold, noscore, ublockon, theme, commentmax
		FROM {$prefix}_users 
		WHERE username = '$username'
		LIMIT 1");
	$setinfo = mysql_fetch_assoc($request);
	mysql_free_result($request);


	$info = base64_encode($setinfo['user_id'] . ':' . $username . ':' . $setinfo['user_password'] . ':' . $setinfo['storynum']  . ':' . $setinfo['umode'] . ':' . $setinfo['uorder'] . ':' . $setinfo['thold'] . ':' . $setinfo['noscore'] . ':' . $setinfo['ublockon'] . ':' . $setinfo['theme'] . ':' . $setinfo['commentmax']);
	echo $info;

	setcookie('user', $info, time() + 2592000, '/');

	mysql_query("
		DELETE FROM {$prefix}_session 
		WHERE uname = '$_SERVER[REMOTE_ADDR]' 
			AND guest = '1'
		LIMIT 1");

	mysql_query("
		UPDATE {$prefix}_users 
		SET last_ip = '$_SERVER[REMOTE_ADDR]' 
		WHERE username = '$_SERVER[REMOTE_ADDR]'
		LIMIT 1");

	mysql_select_db($db_name);

	return true;
}

function integrate_logout ($username)
{
	global $prefix, $db, $user, $cookie, $redirect, $db_name, $dbname;
	
	cookiedecode($user);
	$r_uid = $cookie[0];
	$r_username = $cookie[1];
	setcookie("user","",-3600,"/");
	
	mysql_select_db($dbname);

	mysql_query("
		DELETE FROM {$prefix}_session
		WHERE uname = '$r_username'");
	mysql_query("
		DELETE FROM {$prefix}_bbsessions 
		WHERE session_user_id = '$r_uid'");
	$user = '';

	mysql_select_db($db_name);
}

function integrate_register ($Options, $theme_vars)
{
	global $prefix, $db, $db_name, $dbname;

	mysql_select_db($dbname);
	mysql_query("
		INSERT INTO {$prefix}_users 
			(name, username, user_email,user_password, user_regdate)
		VALUES 
			(" . $Options['register_vars']['real_name'] . ', ' . $Options['register_vars']['member_name'] . ', ' . $Options['register_vars']['email_address'].", '".md5($_POST['passwrd1'])."', '" . $Options['register_vars']['date_registered'] . "')");

	mysql_select_db($db_name);
}

?>