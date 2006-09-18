<?php
/******************************************************************************
* smf_registration.php   (Mambo/Joomla Bridge)                                                               *
*******************************************************************************
* SMF: Simple Machines Forum                                                  *
* Open-Source Project Inspired by Zef Hemel (zef@zefhemel.com)                *
* =========================================================================== *
* Software Version:           SMF 1.1                                         *
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


/** ensure this file is being included by a parent file */
if (!defined('_VALID_MOS'))
	die('Direct Access to this location is not allowed.');
	
global $mosConfig_absolute_path, $database, $db_name, $mosConfig_db, $sourcedir;
global $mosConfig_dbprefix,$db_prefix, $pm_on_reg, $use_realname, $cb_reg, $im, $agreement_required;
	
$database =& mamboDatabase::getInstance();
$mainframe =& mosMainFrame::getInstance();

$task = mosGetParam($_REQUEST, 'task', '');
require_once($mainframe->getPath('front_html'));

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
	
	if (!defined('SMF'))
	{	
		require($smf_path . '/SSI.php');
	}

mysql_select_db($mosConfig_db);

$result = mysql_query ("
	SELECT id
	FROM {$mosConfig_dbprefix}menu
	WHERE link = 'index.php?option=com_smf'");
if ($result !== false)
	$row = mysql_fetch_array($result);
$myurl = basename($_SERVER['PHP_SELF']) . '?option=com_smf&amp;Itemid=' . $row[0] . '&amp;';
$scripturl = $myurl;

switch($task) 
{
	case 'lostPassword':
		lostPassForm($option);
	break;

	case 'sendNewPass':
		sendNewPass($option);
	break;
	
	case 'sendNewCode':
		resend_activation($option);
	break;

	case 'lostCode':
		resendActivationForm($option);
	break;

	case 'register':
		registerForm($option, $mosConfig_useractivation);
	break;
	
	case '':
		registerForm($option, $mosConfig_useractivation);
	break;

	case 'saveRegistration':
	saveRegistration($option);
	break;

	case 'activate':
	activate($option);
	break;
}

function lostPassForm($option) 
{
	global $mainframe;

	$mainframe->SetPageTitle(_PROMPT_PASSWORD);
	HTML_smf_registration::lostPassForm($option);
}

function sendNewPass($option)
{
	global $database, $Itemid, $mosConfig_live_site, $mosConfig_sitename;
	global $db_prefix ,$mosConfig_dbprefix, $mosConfig_db, $db_name;
	global $mosConfig_mailfrom, $mosConfig_fromname;

	$_live_site = $mosConfig_live_site;
	$_sitename = $mosConfig_sitename;

	// Ensure no malicous sql gets past.
	$checkusername = trim(mosGetParam($_POST, 'checkusername', ''));
	$checkusername = $database->getEscaped($checkusername);
	$confirmEmail = trim(mosGetParam($_POST, 'confirmEmail', ''));
	$confirmEmail = $database->getEscaped($confirmEmail);

	$database->setQuery("
		SELECT id, username
		FROM {$mosConfig_dbprefix}users
		WHERE username = '$checkusername'
			AND email = '$confirmEmail'");
	$user_id = $database->loadResult();
	if (!$user_id || !$checkusername || !$confirmEmail) 
	{
		mysql_select_db($db_name);

		// Check if the email address  and/or username is in use.
		$request = mysql_query("
			SELECT ID_MEMBER
			FROM {$db_prefix}members
			WHERE emailAddress = '$confirmEmail'
				AND memberName = '$checkusername'
			LIMIT 1");
		if (mysql_num_rows($request) != 0 && !$userid)
		{

			//OK then this user exists in SMF, let's get this user into Mambo
			mysql_select_db($mosConfig_db);

			// What Mambo group do we put you in?  SMF Newbies are group #4...
			$mos_sync_groups = mysql_query("
				SELECT `value2`
				FROM {$mosConfig_dbprefix}smf_config
				WHERE `variable` = 'sync_group' AND `value1`='4'
				");
			list($group) = mysql_fetch_row($mos_sync_groups);

			//Just in case....
			if (!isset($group) || $group == '' || $group = 0 )
				$group = '18';
			
			mysql_query("
				INSERT INTO {$mosConfig_dbprefix}users
					(name,username,email,password,gid)
				VALUES ('$checkusername','$checkusername','$confirmEmail','$passwd','$group');");

			$mos_find_id = mysql_query("
				SELECT id
				FROM {$mosConfig_dbprefix}users
				WHERE name = '$checkusername'
				LIMIT 1");
			list($mos_id) = mysql_fetch_row($mos_find_id);

			mysql_query("
				INSERT INTO {$mosConfig_dbprefix}core_acl_aro 
					(aro_id, section_value, value, order_value, name, hidden) 
				VALUES ('', 'users', '$mos_id', '0', '$checkusername', '0');");
			
			$mos_map_sql = mysql_query("
				SELECT aro_id
				FROM {$mosConfig_dbprefix}core_acl_aro
				WHERE name = '$checkusername'
				LIMIT 1");
			list($aro_id) = mysql_fetch_row($mos_map_sql);

			mysql_query ("
				INSERT INTO {$mosConfig_dbprefix}core_acl_groups_aro_map
					(group_id , section_value , aro_id)
				VALUES ('$group', '', '$aro_id');");

			//Do you have Community Builder?  Might has well get them in there too...
			if ($cb_reg=="on")
				$sql = mysql_query("
					INSERT INTO {$mosConfig_dbprefix}comprofiler 
						(id, user_id) 
					VALUES ('$mos_id', '$mos_id')");
		}
		else
			mosRedirect("index.php?option=$option&task=lostPassword&mosmsg="._ERROR_PASS);
	}

	$database->setQuery("
		SELECT name, email 
		FROM {$mosConfig_dbprefix}users
		WHERE usertype = 'superadministrator'");
	$rows = $database->loadObjectList();
	foreach ($rows AS $row) 
	{
		$adminName = $row->name;
		$adminEmail = $row->email;
	}

	$newpass = mosMakePassword();
	$message = _NEWPASS_MSG;
	eval ('$message = "' . $message . '";');
	$subject = _NEWPASS_SUB;
	eval ('$subject = "' . $subject . '";');

	mosMail($mosConfig_mailfrom, $mosConfig_fromname, $confirmEmail, $subject, $message);

	$mos_newpass = md5($newpass);
	$smf_newpass = sha1(strtolower($checkusername) . $newpass);
	$database->setQuery("
		UPDATE {$mosConfig_dbprefix}users 
		SET password = '$mos_newpass' 
		WHERE id = '$user_id'");
	if (!$database->query())
		die("SQL error" . $database->stderr(true));

	mysql_select_db($db_name);
	mysql_query("
		UPDATE {$db_prefix}members 
		SET passwd = '$smf_newpass' 
		WHERE memberName = '$checkusername' 
			AND emailAddress = '$confirmEmail'");

	mysql_select_db($mosConfig_db);
	mosRedirect('index.php?mosmsg=' . _NEWPASS_SENT);
}

function registerForm($option, $useractivation) 
{
	global $context, $mainframe, $database, $my, $acl, $boarddir;
	global $agreement_required, $im;

	if (!$mainframe->getCfg('allowUserRegistration')) 
	{
		mosNotAuth();
		return;
	}
	$mainframe->SetPageTitle(_REGISTER_TITLE);
	HTML_smf_registration::registerForm($option, $useractivation, $context, $agreement_required, $im);
}

function saveRegistration($option)
{
	global $modSettings,$user_settings,$context, $database, $my, $acl, $db_name;
	global $user_info, $mosConfig_sitename, $mosConfig_live_site, $mosConfig_sef;
	global $mosConfig_useractivation, $mosConfig_allowUserRegistration;
	global $mosConfig_mailfrom, $mosConfig_fromname;
	global $mosConfig_dbprefix, $db_prefix, $pm_on_reg;
	global $smf_path, $use_realname, $sourcedir, $mosConfig_db, $cb_reg;

	if ($mosConfig_allowUserRegistration == '0') 
	{
		mosNotAuth();
		return;
	}

	mysql_select_db($mosConfig_db);

	$row = new mosUser($database);

	if (!$row->bind($_POST, "usertype")) 
	{
		echo "<script> alert('", $row->getError(), "'); window.history.go(-1); </script>\n";
		exit();
	}

	mosMakeHtmlSafe($row);

	$row->id = 0;
	$row->usertype = '';
	
	// What Mambo group do we put you in?  SMF Newbies are group #4...
	$mos_sync_groups = mysql_query("
		SELECT `value2`
		FROM {$mosConfig_dbprefix}smf_config
		WHERE `variable` = 'sync_group' AND `value1`='4'
		");
	list($group) = mysql_fetch_row($mos_sync_groups);

	//Just in case....
	if (!isset($group) || $group == '' || $group == 0 )
		$group = '18';
	
	$row->gid = $group;

	if ($mosConfig_useractivation == '1') 
	{
		$row->activation = md5(mosMakePassword());
		$row->block = '1';
	}

	if (!$row->check()) 
	{
		echo "<script> alert('", $row->getError(), "'); window.history.go(-1); </script>\n";
		exit();
	}
		
	//We also need to check for unique names for SMF....
	$query = "SELECT id 
			FROM #__users 
			WHERE name = '" . $row->name . "'
			AND id != " . (int)$row->id;
				
	$database->setQuery( $query );
	$xid = intval( $database->loadResult() );
	if ($xid && $xid != intval( $row->id )) {
		echo "<script> alert('", _REGWARN_INUSE, "'); window.history.go(-1); </script>\n";
		exit();
	}

	$pwd = $row->password;
	$row->password = md5($row->password);
	$row->registerDate = date("Y-m-d H:i:s");

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
		'send_welcome_email' => false,  //let Mambo handle this in bridge registration mode
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

	//OK then, let's get this user into Mambo
	
	mysql_select_db($mosConfig_db);
	$row->checkin();
	$name = $row->name;
	$email = $row->email;
	$username = $row->username;
	
	if (!$row->store()) 
	{
		echo "<script> alert('", $row->getError(), "'); window.history.go(-1); </script>\n";
		exit();
	}
	
	//Do you have Community Builder?  Might has well get them in there too...
	if ($cb_reg=="on")
		$sql = mysql_query("
			INSERT INTO {$mosConfig_dbprefix}comprofiler 
				(id, user_id) 
			VALUES ('".$row->id."', '".$row->id."')");

	$subject = sprintf(_SEND_SUB, $name, $mosConfig_sitename);
	$subject = html_entity_decode($subject, ENT_QUOTES);
	
	if ($mosConfig_sef ==1){
		$activation_url = sefReltoAbs('index.php?option=com_smf_registration&task=activate&activation=' . $row->activation);
	} else {
		$activation_url = $mosConfig_live_site . '/index.php?option=com_smf_registration&task=activate&activation=' . $row->activation;
	}
	
	$message = $mosConfig_useractivation == '1' ? sprintf(_USEND_MSG_ACTIVATE, $name, $mosConfig_sitename, $activation_url, $mosConfig_live_site, $username, $pwd) : sprintf(_USEND_MSG, $name, $mosConfig_sitename, $mosConfig_live_site);
	$message = html_entity_decode($message, ENT_QUOTES);

	// Send email to user.
	if ($mosConfig_mailfrom != '' && $mosConfig_fromname != '')
	{
		$adminName2 = $mosConfig_fromname;
		$adminEmail2 = $mosConfig_mailfrom;
	}
	else 
	{
		$database->setQuery("
			SELECT name, email 
			FROM {$mosConfig_dbprefix}users
			WHERE usertype = 'superadministrator'");
		$rows = $database->loadObjectList();
		$row2 = $rows[0];
		$adminName2 = $row2->name;
		$adminEmail2 = $row2->email;
	}

	mosMail($adminEmail2, $adminName2, $email, $subject, $message);

	// Send notification to all administrators
	$subject2 = sprintf (_SEND_SUB, $name, $mosConfig_sitename);
	$message2 = sprintf (_ASEND_MSG, $adminName2, $mosConfig_sitename, $row->name, $email, $username);
	$subject2 = html_entity_decode($subject2, ENT_QUOTES);
	$message2 = html_entity_decode($message2, ENT_QUOTES);

	// get superadministrators id
	$admins = $acl->get_group_objects(25, 'ARO');

	foreach ($admins['users'] AS $id) 
	{
		$database->setQuery("
			SELECT email, sendEmail 
			FROM {$mosConfig_dbprefix}users
			WHERE id = '$id'");
		$rows = $database->loadObjectList();
		$row = $rows[0];
		if ($row->sendEmail)
			mosMail($adminEmail2, $adminName2, $row->email, $subject2, $message2);
	}

	echo $mosConfig_useractivation == "1" ? _REG_COMPLETE_ACTIVATE : _REG_COMPLETE;
}

function activate($option) 
{
	global $database, $db_prefix, $mosConfig_dbprefix, $mosConfig_db, $db_name;

	mysql_select_db($mosConfig_db);
	$activation = trim(mosGetParam($_REQUEST, 'activation', ''));

	$request = mysql_query("
		SELECT id,username 
		FROM {$mosConfig_dbprefix}users 
		WHERE activation = '$activation' 
			AND block = '1'");
	$result = mysql_fetch_array($request);
	if ($result[0]) 
	{
		$database->setQuery("
			UPDATE {$mosConfig_dbprefix}users 
			SET 
				block = '0', 
				activation = ''
			WHERE activation = '$activation'
				AND block = '1'");
		if (!$database->query())
			echo "SQL error" . $database->stderr(true);
		mysql_select_db($db_name);
		mysql_query("
			UPDATE {$db_prefix}members 
			SET 
				is_activated = '1',
				validation_code = '' 
			WHERE memberName = '$result[1]'
			LIMIT 1");
		mysql_select_db($mosConfig_db);

		echo _REG_ACTIVATE_COMPLETE;
	}
	else
		echo _REG_ACTIVATE_NOT_FOUND;
}

function resendActivationForm($option) 
{
	global $mainframe;

	$mainframe->SetPageTitle(_PROMPT_PASSWORD);
	HTML_smf_registration::resendActivationForm($option);
}

function resend_activation($option)
{
	global $database, $Itemid, $mosConfig_live_site, $mosConfig_sitename;
	global $db_prefix,$mosConfig_dbprefix, $mosConfig_sef;

	$_live_site = $mosConfig_live_site;
	$_sitename = $mosConfig_sitename;

	// Ensure no malicous sql gets past.
	$checkusername = trim(mosGetParam($_POST, 'checkusername', ''));
	$checkusername = $database->getEscaped($checkusername);
	$confirmEmail = trim(mosGetParam($_POST, 'confirmEmail', ''));
	$confirmEmail = $database->getEscaped($confirmEmail);

	$database->setQuery("
		SELECT id, username 
		FROM {$mosConfig_dbprefix}users
		WHERE username = '$checkusername' 
			AND email = '$confirmEmail'");

	if (!($user_id = $database->loadResult()) || !$checkusername || !$confirmEmail)
		mosRedirect("index.php?option=$option&task=lostCode&mosmsg="._ERROR_PASS);

	$database->setQuery("
		SELECT activation , username, name, email
		FROM {$mosConfig_dbprefix}users
		WHERE username = '$checkusername'
			AND email = '$confirmEmail'
		LIMIT 1");
	$row = $database->LoadObjectList();
	$activation = $row[0];
	$username = $row[1];
	$name = $row[2];
	$email = $row[3];

	$subject = sprintf (_SEND_SUB, $name, $mosConfig_sitename);
	$subject = html_entity_decode($subject, ENT_QUOTES);
	
	if ($mosConfig_sef ==1){
		$activation_url = sefReltoAbs('index.php?option=com_smf_registration&task=activate&activation=' . $activation);
	} else {
		$activation_url = $mosConfig_live_site . '/index.php?option=com_smf_registration&task=activate&activation=' . $activation;
	}

	$message = sprintf (_USEND_MSG_ACTIVATE, $name, $mosConfig_sitename, $activation_url, $mosConfig_live_site, $username, '');
	$message = html_entity_decode($message, ENT_QUOTES);

	// Send email to user
	if ($mosConfig_mailfrom != '' && $mosConfig_fromname != '')
	{
		$adminName2 = $mosConfig_fromname;
		$adminEmail2 = $mosConfig_mailfrom;
	}
	else
	{
		$database->setQuery("
			SELECT name, email 
			FROM {$mosConfig_dbprefix}users
			WHERE usertype = 'superadministrator'");
		$rows = $database->loadObjectList();
		$row2 = $rows[0];
		$adminName2 = $row2->name;
		$adminEmail2 = $row2->email;
	}

	mosMail($adminEmail2, $adminName2, $email, $subject, $message);
	
	echo _REG_COMPLETE_ACTIVATE;
}


function is_email($email)
{
	return preg_match("/[\w\.\-]+@\w+[\w\.\-]*?\.\w{1,4}/", $email) == 1;
}

mysql_select_db($mosConfig_db);

?>