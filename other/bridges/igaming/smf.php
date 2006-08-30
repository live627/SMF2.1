<?php
/******************************************************************************
* smf.php                                                                     *
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

/* This file is the core of the iGaming-SMF bridge module

	string ob_igfix(string $buffer)
	-fixes URLs in SMF to point to iGaming module

	integrate_pre_load ()
	- starts up the SMF session variables for use in the iGaming module

	bool integrate_redirect (&$setLocation, $refresh)
	- sets redirection in forum for form submissions

	ig_smf_exit(string $with_output)
	- exits SMF securely

	integrate_login (username, $password, $cookietime)
	- migrates users from a preset memberGroup into the iGaming database
	- after migration, make sure to change password in SMF profile!

	integrate_reset_pass ($old_username, $username, $password)
	- changes passwords in iGaming on change in SMF profile
	- this will need to be done after each user integration

	integrate_outgoing_email ($subject, &$message, $headers)
	- fixes URLs in email messages to point to iGaming module

*/
error_reporting(E_ALL & ~E_NOTICE);
@set_magic_quotes_runtime(0);

$location .= ' > <b>Forum</b>';

require_once('global.php');

global $db, $spconfig;

$title .= ' > Forum';

$smf_path = $spconfig['smf_path'];

$ig_admin_group = $spconfig['ig_admin_group'];

define('SMF_INTEGRATION_SETTINGS', serialize(array(
	'integrate_change_email' => 'integrate_change_email',
	'integrate_reset_pass' => 'integrate_reset_pass',
	'integrate_exit' => 'ig_smf_exit',
	'integrate_logout' => 'integrate_logout',
	'integrate_outgoing_email' => 'integrate_outgoing_email',
	'integrate_login' => 'integrate_login',
	'integrate_validate_login' => 'integrate_validate_login',
	'integrate_redirect' => 'integrate_redirect',
	'integrate_delete_member' => 'integrate_delete_member',
	'integrate_register' => 'integrate_register',
	'integrate_pre_load' => 'integrate_pre_load'
)));

if (empty($_REQUEST['do']))
{
	ob_start();
	do_header();
	require ($smf_path . '/index.php');
	$buffer = ob_get_contents();
	ob_end_clean();

	echo ob_igfix($buffer);

	do_footer();
}

function ob_igfix($buffer)
{
	global $scripturl, $sp_url;

	$buffer = str_replace($scripturl, $sp_url . '/smf.php', $buffer);
	$buffer = str_replace($sp_url . '/smf.php?action=dlattach', $scripturl . '/index.php?action=dlattach', $buffer);
	$buffer = str_replace('name="seqnum" value="0"', 'name="seqnum" value="1"', $buffer);

	return $buffer;
}

function integrate_pre_load ()
{
	global $modsettings, $db;

	//if (isset($_POST['sc'])){
	//  $GLOBALS['sc'] = $_POST['sc'];
	//  $_SESSION['rand_code'] = $_POST['sc'];}

	$_SESSION['USER_AGENT'] = $_SERVER['HTTP_USER_AGENT'];
	$modSettings['disableCheckUA'] = true;

	return true;
}

function integrate_redirect (&$setLocation, &$refresh)
{
	global $boardurl, $sp_url;

	$myurl = $sp_url . '/smf.php';

	if ($setLocation == '')
		$setLocation = $myurl;

	$setLocation = un_htmlspecialchars(ob_igfix($setLocation));

	return true;
}

function integrate_outgoing_email ($subject, &$message, $headers)
{
	global $boardurl, $sp_url;

	$myurl = $sp_url . '/smf.php';

	$message = un_htmlspecialchars(ob_igfix($message));
}

function ig_smf_exit($with_output)
{
	$buffer = ob_get_contents();
	ob_end_clean();


	$with_output = true;

	if (!$with_output)
	{
		echo ob_igfix($buffer);
		exit;
	}
	
	echo ob_igfix($buffer);
	do_footer();
	
	die;
}

function integrate_login($username, $password, $cookietime)
{
	global $ig_admin_group, $db, $user_settings;
	
	//If $admin_group is true, we want to migrate this user to iGaming.
	$groups = explode(',', $user_settings['additionalGroups']);
	$admin_group = $user_settings['ID_GROUP'] == $ig_admin_group || in_array($ig_admin_group, $user_settings['additionalGroups']);
	
	//Note that this is going to be a SHA1 password, so this user will need to change their password in their profile 
	//before they will be able to login to the iGaming admin panel
	
	if ($admin_group == true)
		$rs = $db->Execute("
			INSERT INTO sp_members
				(`ID` , `PSEUDO` , `PASS` , `EMAIL` , `NOM` , `PRIV` , `ACTIF` )
			VALUES ('' , '$username' , '$password' , '$user_settings[emailAddress]' , 'Administrator' , '0' , '1' )");
}

function integrate_reset_pass($old_username, $username, $password)
{
	global $ig_admin_group, $db;
	
	$groups = explode(',', $user_settings['additionalGroups']);
	$admin_group = $user_settings['ID_GROUP'] == $ig_admin_group || in_array($ig_admin_group, $user_settings['additionalGroups']);

	$password = md5($password);

	if ($admin_group == true)
		$rs = $db->Execute("
			UPDATE sp_members
			SET PASS = '$password'
			WHERE PSEUDO = '$username'");
}

?>