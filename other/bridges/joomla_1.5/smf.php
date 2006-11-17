<?php
/******************************************************************************
* smf.php                                                                     *
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

/* This file is the core of the Mambo-SMF bridge component

	string ob_mambofix(string $buffer)
	- fixes URLs in SMF to point to Mambo component

	bool integrate_redirect (&$setLocation, $refresh)
	- sets redirection in forum for form submissions

	mambo_smf_exit(string $with_output)
	- exits SMF securely

	integrate_change_email($username, $email)
	- updates Mambo with email changes made in SMF
	
	integrate_change_member_data (array $member_names, string $var, string $value)
	- updates Mambo with member data changes in SMF

	integrate_reset_pass($old_username, $username, $password)
	- updates Mambo username and password changes made in SMF

	integrate_logout($username)
	- logs user out of Mambo

	integrate_outgoing_email($subject, &$message, $headers)
	- fixes URLs in outgoing emails to direct traffic to the component correctly

	integrate_login($username, $passwd, $cookielength)
	- writes user into Mambo if user doesn't already exist
	- logs user into Mambo

	integrate_validate_login($username, $password, $cookietime)
	- validates the existence of the user in Mambo
	- checks for user in SMF
	- writes user to SMF if none exists

	integrate_delete_member($user)
	- deletes mamber from Mambo when admin deletes SMF member

	integrate_register($regOptions, $theme_vars)
	- writes user into Mambo upon registration in SMF.  Function for use with "SMF Registration" option in Mambo admin panel.
*/

//This helps Mambo allow posts with HTML.  Please note that this does not necessarily work with all installations.
if (!defined('_MOS_ALLOWHTML'))
	define('_MOS_ALLOWHTML', 0x0002);
	
// Raise the memory limit -- Loading Mambo/Joomla and SMF can take a lot of RAM
@ini_set('memory_limit', '16M');

global $params, $database, $mosConfig_dbprefix, $db_prefix, $mosConfig_db, $settings, $modSettings;
global $mosConfig_absolute_path, $wrapped, $mosConfig_live_site, $mosConfig_sef, $boardurl, $scripturl;
global $bridge_reg, $menu, $Itemid, $context, $cb_reg, $database, $boarddir, $sourcedir, $db_prefix, $db_name;
global $db_server, $db_passwd, $db_persist, $db_error_send, $db_user, $db_connection, $themedir, $language, $document;

// Get the configuration.  This will tell Mambo where SMF is, and some integration settings
	$database->setQuery("
				SELECT `variable`, `value1`
				FROM #__smf_config
				");
	$variables = $database->loadAssocList();
	
	foreach ($variables as $variable){
		$variable_name = $variable['variable'];
		$$variable_name = $variable['value1'];
	}
	
//Retrofit the query string
$_SERVER['QUERY_STRING'] = strtr($_SERVER['QUERY_STRING'], array('&amp;?' => '&amp;', '&?' => '&amp;' , '#' => '.'));


//define the integration functions
define('SMF_INTEGRATION_SETTINGS', serialize(array(
	'integrate_change_email' => 'integrate_change_email',
	'integrate_change_member_data' => 'integrate_change_member_data',
	'integrate_reset_pass' => 'integrate_reset_pass',
	'integrate_exit' => 'mambo_smf_exit',
	'integrate_logout' => 'integrate_logout',
	'integrate_outgoing_email' => 'integrate_outgoing_email',
	'integrate_login' => 'integrate_login',
	'integrate_validate_login' => 'integrate_validate_login',
	'integrate_redirect' => 'integrate_redirect',
	'integrate_delete_member' => 'integrate_delete_member',
	'integrate_register' => 'integrate_register'
)));

// Are Mambo and SMF using the same database connection?
if (empty($Itemid) && $database->_resource == $db_connection)
{
	$database->setQuery("
		SELECT id
		FROM #__menu
		WHERE link = 'index.php?option=com_smf'
		LIMIT 1");
	$Itemid = $database->loadResult();
}

// This fixes automatic links that might look like Itemid=43?action=...
if (strpos($_SERVER['QUERY_STRING'], 'Itemid=' . $Itemid . '?') !== false)
	$_SERVER['QUERY_STRING'] = strtr($_SERVER['QUERY_STRING'], array('Itemid=' . $Itemid . '?' => 'Itemid=' . $Itemid . '&amp;'));

// Just in case it gets flushed in the middle for any reason..
ob_start('ob_mambofix');
ob_start();
require_once ($smf_path.'/Settings.php');
require($smf_path . '/index.php');
$buffer = ob_get_contents();
ob_end_clean();
ob_start();

// --- This means that the buffer may be mambofix'd twice - see above ob_start.
echo ob_mambofix($buffer);

// Ignore notices from Mambo.
error_reporting(E_ALL & !E_NOTICE);

if ($database->_resource == $db_connection)
	mysql_select_db($mosConfig_db);

// --- Changed to use action in a notice-friendly way.  I would still recommend instead: (more mod friendly!)
if ($wrapped != 'true' || !in_array('main', $context['template_layers']))
{
	mysql_select_db($db_name);	
	die;
}

echo '
<div class="componentheading">
	', $menu->name, '
</div>';

// Rewrite URLs to include the session ID.
function ob_mambofix($buffer)
{
	global $scripturl, $mosConfig_live_site, $mosConfig_sef, $boardurl;
	global $bridge_reg, $Itemid;

	$myurl = $mosConfig_live_site . '/' . basename($_SERVER['PHP_SELF']) . '?option=com_smf&amp;Itemid=' . $Itemid . '&amp;';
	
	$buffer = str_replace('"?board=', $mosConfig_sef=='1' ? '"/board,' : '"&amp;board=', $buffer);
	$buffer = str_replace('"?action=', $mosConfig_sef=='1' ? '"/action,' : '"&amp;action=', $buffer);
	$buffer = str_replace('href="#', 'href="'.$mosConfig_live_site . '/' . basename($_SERVER['PHP_SELF']) . '?'.$_SERVER['QUERY_STRING'].'#', $buffer);
	$buffer = str_replace('"' . $scripturl . '?', '"' . $myurl, $buffer);
	$buffer = str_replace('"' . $scripturl . '"', '"' . substr($myurl, 0, -5) . '"', $buffer);
	$buffer = str_replace('\'' . $scripturl, '\'' . $myurl, $buffer);

	$buffer = str_replace($scripturl . '#', substr($myurl, 0, -5) . '#', $buffer);
	$buffer = str_replace('option=com_smf;Itemid=' . $Itemid . ';', '', $buffer);

	// New bridged profile options.  Not yet available
	//$buffer = str_replace($scripturl . 'action=profile;', $mosConfig_live_site . '/' . basename($_SERVER['PHP_SELF']) . '?option=com_smf_profile&task=view&', $buffer);
	//$buffer = str_replace($scripturl . 'action=profile', $mosConfig_live_site . '/' . basename($_SERVER['PHP_SELF']) . '?option=com_smf_profile&task=view', $buffer);

	// Bridge registration links
	switch ($bridge_reg){  
		case "bridge":
			$buffer = str_replace($myurl . 'action=register', $mosConfig_live_site . '/' . basename($_SERVER['PHP_SELF']) . '?option=com_smf_registration&amp;task=register', $buffer);
			$buffer = str_replace($myurl . 'action=activate', $mosConfig_live_site . '/' . basename($_SERVER['PHP_SELF']) . '?option=com_smf_registration&amp;task=lostCode', $buffer);
			$buffer = str_replace($myurl . 'action=reminder', $mosConfig_live_site . '/' . basename($_SERVER['PHP_SELF']) . '?option=com_smf_registration&amp;task=lostPassword', $buffer);
		break;
		
		case "default":
			$buffer = str_replace($myurl . 'action=register', $mosConfig_live_site . '/' . basename($_SERVER['PHP_SELF']) . '?option=com_registration&amp;task=register', $buffer);
			$buffer = str_replace($myurl . 'action=activate', $mosConfig_live_site . '/' . basename($_SERVER['PHP_SELF']) . '?option=com_smf_registration&amp;task=lostCode', $buffer);
			$buffer = str_replace($myurl . 'action=reminder', $mosConfig_live_site . '/' . basename($_SERVER['PHP_SELF']) . '?option=com_smf_registration&amp;task=lostPassword', $buffer);
		break;
		
		case "CB":
			$buffer = str_replace($myurl . 'action=register', $mosConfig_live_site . '/' . basename($_SERVER['PHP_SELF']) . '?option=com_comprofiler&amp;task=registers', $buffer);
			$buffer = str_replace($myurl . 'action=activate', $mosConfig_live_site . '/' . basename($_SERVER['PHP_SELF']) . '?option=com_smf_registration&amp;task=lostCode', $buffer);
			$buffer = str_replace($myurl . 'action=reminder', $mosConfig_live_site . '/' . basename($_SERVER['PHP_SELF']) . '?option=com_smf_registration&amp;task=lostPassword', $buffer);
		break;
		
		case "jw":
			$buffer = str_replace($myurl . 'action=register', $mosConfig_live_site . '/' . basename($_SERVER['PHP_SELF']) . '?option=com_jw_registration&amp;task=register', $buffer);
			$buffer = str_replace($myurl . 'action=activate', $mosConfig_live_site . '/' . basename($_SERVER['PHP_SELF']) . '?option=com_smf_registration&amp;task=lostCode', $buffer);
			$buffer = str_replace($myurl . 'action=reminder', $mosConfig_live_site . '/' . basename($_SERVER['PHP_SELF']) . '?option=com_smf_registration&amp;task=lostPassword', $buffer);
		break;
	}

	// Don't forget attachments
	$buffer = str_replace($myurl . 'action=dlattach', $boardurl . '/' . basename($_SERVER['PHP_SELF']) . '?action=dlattach', $buffer);
	//And Niko's Arcade Mod
	$buffer = str_replace($myurl . 'action=arcade;sa=download;file=swf', $boardurl . '/' . basename($_SERVER['PHP_SELF']) . '?action=arcade;sa=download;file=swf', $buffer);
	// and now for SEF
	if (!empty($mosConfig_sef) && $mosConfig_sef == '1')
	{
		preg_match_all('~([\(=]")' . preg_quote($mosConfig_live_site . '/index.php?option=com_smf') . '([^"]*)"{1}~', $buffer, $nonsefurls);
		foreach($nonsefurls[0] as $nonsefurl)
		{
			$sefurl = sefReltoAbs(substr($nonsefurl, strlen($mosConfig_live_site) + 3, strlen($nonsefurl) - strlen($mosConfig_live_site) - 4));
			$sefurl = str_replace(";", "/", $sefurl);
			$sefurl = str_replace("=", ",", $sefurl);
			$sefurl = substr($sefurl, 0, strpos($sefurl, 'option')) . preg_replace('/(\/)([^,]*)(#)/', '$1$2,$2$3', substr($sefurl, strpos($sefurl, 'option'), strlen($sefurl)));
			$sefurl = substr($sefurl, 0, strpos($sefurl, 'option')) . preg_replace('/(\/)([^,]*)(\/)/', '$1$2,$2$3', substr($sefurl, strpos($sefurl, 'option'), strlen($sefurl)));
			if (substr($sefurl, strlen($sefurl) - 1, 1) == '/')
				$sefurl = substr($sefurl, 0, strlen($sefurl) - 1);
			$buffer = str_replace(substr($nonsefurl,1,strlen($nonsefurl)), '"' . $sefurl . '"', $buffer);
		}
	}
	return $buffer;
}

function mambo_smf_url($url)
{
	global $scripturl, $Itemid, $mosConfig_live_site;

	if ($Itemid == 0)
		$Itemid = (int) $_REQUEST['Itemid'];

	$myurl = $mosConfig_live_site . '/' . basename($_SERVER['PHP_SELF']) . '?option=com_smf&amp;Itemid=' . $Itemid . '&amp;';
	$url = str_replace($scripturl . '?', $myurl, $url);
	$url = str_replace($scripturl, $myurl, $url);
	

	return $url;
}

function mambo_smf_exit($with_output)
{
	global $wrapped, $mosConfig_db, $database, $cur_template, $mainframe;
	global $boardurl, $_MOS_OPTION, $mosConfig_dbprefix, $settings, $context;
	global $scripturl, $modSettings, $db_connection, $mosConfig_sef;
	global $mosConfig_live_site, $options, $document;

	$buffer = ob_get_contents();
	ob_end_clean();

	if (!$with_output || $wrapped != 'true')
	{		
		$buffer = mambo_smf_url($buffer);
		$buffer = ob_mambofix($buffer);
		echo $buffer;	
		exit;
	}

	$_MOS_OPTION['buffer'] = ob_mambofix($buffer);

	if ($database->_resource == $db_connection)
		mysql_select_db($mosConfig_db);
	
	$result = mysql_query("
			SELECT id 
			FROM {$mosConfig_dbprefix}menu 
			WHERE link = 'index.php?option=com_smf'");

	if ($result !== false)
		list($menu_item['id']) = mysql_fetch_row($result);
	else
		$menu_item['id'] = 1;	

	$myurl = basename($_SERVER['PHP_SELF']) . '?option=com_smf&amp;Itemid=' . $menu_item['id'] . '&amp;';

	$mainframe->addCustomHeadTag('<script language="JavaScript" type="text/javascript" src="' . $settings['default_theme_url'] . '/script.js?rc2"></script>');
	$mainframe->addCustomHeadTag('<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
		var smf_theme_url = "' . $settings['theme_url'] . '";
		var smf_images_url = "' . $settings['images_url'] . '";
		var smf_scripturl = "' . un_htmlspecialchars(mambo_smf_url($scripturl)) . '";
		var smf_session_id = "' . $context['session_id'] . '";
		// ]]></script>');
	$mainframe->addCustomHeadTag('<link rel="stylesheet" type="text/css" href="' . $settings['theme_url'] . '/style.css?rc2" />');
	$mainframe->addCustomHeadTag('<link rel="stylesheet" type="text/css" href="' . $settings['default_theme_url'] . '/print.css?rc2" media="print" />');
	$mainframe->addCustomHeadTag('<link rel="help" href="' . mambo_smf_url($scripturl . 'action=help') . '" target="_blank" />');
	$mainframe->addCustomHeadTag('<link rel="search" href="' . mambo_smf_url($scripturl . 'action=search') . '" />');
	$mainframe->addCustomHeadTag('<link rel="contents" href="' . mambo_smf_url($scripturl) . '" />');

	// If RSS feeds are enabled, advertise the presence of one. 
	if (!empty($modSettings['xmlnews_enable']))  
		$mainframe->addCustomHeadTag('<link rel="alternate" type="application/rss+xml" title="' . $context['forum_name'] . ' - RSS" href="' . ($mosConfig_sef == 1 ? sefReltoAbs($myurl . 'type=rss;action=.xml') : $mosConfig_live_site . '/' . $myurl . 'type=rss;action=.xml') . '" />'); 

	// If we're viewing a topic, these should be the previous and next topics, respectively. 
	if (!empty($context['current_topic'])){ 
		$mainframe->addCustomHeadTag('<link rel="prev" href="' . ($mosConfig_sef == 1 ? sefReltoAbs($myurl . 'topic=' . $context['current_topic'] . '.0&amp;prev_next=prev') : $mosConfig_live_site . '/' . $myurl . 'topic=' . $context['current_topic'] . '.0;prev_next=prev') . '" />'); 
		$mainframe->addCustomHeadTag('<link rel="next" href="' . ($mosConfig_sef == 1 ? sefReltoAbs($myurl . 'topic=' . $context['current_topic'] . '.0&amp;prev_next=next') : $mosConfig_live_site . '/' . $myurl . 'topic=' . $context['current_topic'] . '.0;prev_next=next') . '" />');
	}
	// If we're in a board, or a topic for that matter, the index will be the board's index. 
	if (!empty($context['current_board'])) 
		$mainframe->addCustomHeadTag('<link rel="index" href="' . ($mosConfig_sef == 1 ? sefReltoAbs($myurl . 'board=' . $context['current_board'] . '.0') :  $mosConfig_live_site . '/' . $myurl . 'board=' . $context['current_board'] . '.0') . '" />'); 

	// We'll have to use the cookie to remember the header... 
	if ($context['user']['is_guest']) 
		$options['collapse_header'] = !empty($_COOKIE['upshrink']);

	// Output any remaining HTML headers. (from mods, maybe?) 
	$mainframe->addCustomHeadTag(ob_mambofix($context['html_headers']) . '
			<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[ 
			var current_header = ' . (empty($options['collapse_header']) ? 'false' : 'true') . '; 
 
          function shrinkHeader(mode) 
          {'); 
 
	// Guests don't have theme options!! 
	if ($context['user']['is_guest']) 
		$mainframe->addCustomHeadTag(' document.cookie = "upshrink=" + (mode ? 1 : 0);'); 
	else 
		$mainframe->addCustomHeadTag(' smf_setThemeOption("collapse_header", mode ? 1 : 0, null, "' . $context['session_id'] . '");'); 
 
	$mainframe->addCustomHeadTag(' 
               document.getElementById("upshrink").src = smf_images_url + (mode ? "/upshrink2.gif" : "/upshrink.gif"); 
 
               document.getElementById("upshrinkHeader").style.display = mode ? "none" : ""; 
               document.getElementById("upshrinkHeader2").style.display = mode ? "none" : ""; 
 
               current_header = mode; 
				} 
				// ]]></script>'); 
 
	// the routine for the info center upshrink 
	$mainframe->addCustomHeadTag(' 
          <script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[ 
               var current_header_ic = ' . (empty($options['collapse_header_ic']) ? 'false' : 'true') . '; 
 
               function shrinkHeaderIC(mode) 
               {'); 
 
	if ($context['user']['is_guest']) 
		$mainframe->addCustomHeadTag(' 
                    document.cookie = "upshrinkIC=" + (mode ? 1 : 0);'); 
	else 
		$mainframe->addCustomHeadTag(' 
                    smf_setThemeOption("collapse_header_ic", mode ? 1 : 0, null, "' . $context['session_id'] . '");'); 
 
	$mainframe->addCustomHeadTag(' 
                    document.getElementById("upshrink_ic").src = smf_images_url + (mode ? "/expand.gif" : "/collapse.gif"); 
 
                    document.getElementById("upshrinkHeaderIC").style.display = mode ? "none" : ""; 
 
                    current_header_ic = mode; 
               } 
          // ]]></script>');

	// Ignore notices from Mambo.
	error_reporting(0);
	echo $buffer;

}

function integrate_reset_pass($old_username, $username, $password)
{
	global $mosConfig_db, $mosConfig_dbprefix, $db_name;

	$newpass = md5($password);
	mysql_select_db($mosConfig_db);

	$request = mysql_query("
		UPDATE {$mosConfig_dbprefix}users
		SET 
			password = '$newpass',
			username = '$username'
		WHERE username = '" . addslashes($old_username) . "'
		LIMIT 1");
	mysql_select_db($db_name);

	return true;
}

function integrate_change_email($username, $email)
{
	global $mosConfig_db, $mosConfig_dbprefix, $db_name;

	mysql_select_db($mosConfig_db);
	$request = mysql_query("
		UPDATE {$mosConfig_dbprefix}users
		SET email_address = '$email'
		WHERE username = '" . addslashes($username) . "'
		LIMIT 1");
	mysql_select_db($db_name);

	return true;
}

function integrate_change_member_data ($member_names, $var, $value)
{

	global $mosConfig_db, $db_name, $mosConfig_dbprefix;
	
	$synch_mambo_fields = array(
   			'member_name' => 'username',
			'real_name' => 'name',
			'email_address' => 'email',
			'id_group' => '',
			'gender'=>'',
			'birthdate'=>'',
			'website_title'=>'',
			'website_url'=>'',
			'location'=>'',
			'hide_email'=>'',
			'time_format'=>'',
			'time_offset'=>'',
			'avatar'=>'',
			'lngfile'=>'',
			);

	$field = $synch_mambo_fields[$var];

	if ($field != ''){
		mysql_select_db($mosConfig_db);
	
		foreach ($member_names as $member_name){
			mysql_query ("UPDATE {$mosConfig_dbprefix}users
						SET `$field` = $value
						WHERE username = '$member_name'
						LIMIT 1");
						
			//  If the real name is changed, we need to make sure to update the ACL
			if ($var == 'real_name'){
				$mos_find_id = mysql_query("
					SELECT `id`
					FROM {$mosConfig_dbprefix}users
					WHERE name = $value
					LIMIT 1");
				$mos_id_array = mysql_fetch_array($mos_find_id);
				$mos_id = $mos_id_array[0];
				$mos_write = mysql_query("
					UPDATE {$mosConfig_dbprefix}core_acl_aro 
					SET `name` = $value 
					WHERE `value` = '$mos_id'");
			}
		}
		mysql_select_db($db_name);
	}
		
	if ($var == 'id_group'){
		mysql_select_db($mosConfig_db);
		
		$query = mysql_query (" SELECT `value2`
					FROM {$mosConfig_dbprefix}smf_config
					WHERE `variable` = 'sync_group' AND `value1` = $value");
		list($group) = mysql_fetch_row($query);
		
		//Just in case....
		if (!isset($group) || $group == '' || $group == 0)
			$group = '18';
		
		foreach ($member_names as $member_name){

			mysql_query ("UPDATE {$mosConfig_dbprefix}users
						SET `gid` = '$group'
						WHERE username = '$member_name'
						");
			$mos_find_name = mysql_query("
						SELECT `name`
						FROM {$mosConfig_dbprefix}users
						WHERE username = '$member_name'
						LIMIT 1");
			list($mos_name) = mysql_fetch_row($mos_find_name);
			$mos_map_sql = mysql_query("
						SELECT id
						FROM {$mosConfig_dbprefix}core_acl_aro
						WHERE name = '$mos_name'
						LIMIT 1");
			list($aro_id) = mysql_fetch_row($mos_map_sql);
			mysql_query ("UPDATE {$mosConfig_dbprefix}core_acl_groups_aro_map
						SET `group_id` = '$group'
						WHERE aro_id = '$aro_id'
						");
		}
		mysql_select_db($db_name);
	
	}
}

function integrate_outgoing_email($subject, &$message, $headers)
{
	global $boardurl, $mosConfig_live_site, $Itemid, $scripturl, $mosConfig_sef, $modSettings;
	
	$message = str_replace ('&amp;?', '&amp;', $message);
	if (strpos($message, $scripturl) != 0)
	{
		$message = str_replace ($scripturl, '="' . $scripturl, $message);
		$message = un_htmlspecialchars(ob_mambofix($message));
		$message = str_replace ('="', '', $message);
		if ($mosConfig_sef == '1'){		
			$message = str_replace ($mosConfig_live_site . '/index.php', 'index.php', $message);
			preg_match ('~index\.php.+~', $message, $url);
			if (isset($url[0])){
				$new_url = sefReltoAbs(trim($url[0]));
				$new_url = str_replace(';', '/', $new_url);			
				$message = str_replace($url[0], $new_url, $message);
			}
		}
	}
}


function integrate_login($username, $passwd, $cookielength)
{
	global $mosConfig_db, $mosConfig_dbprefix, $user_settings, $db_name, $_VERSION;
	global $scripturl, $cb_reg, $mosConfig_absolute_path, $mosConfig_live_site, $mainframe;

	if (!isset($passwd) || $passwd == '' || $passwd == null)
		$passwd = 'migrated';
	
	mysql_select_db($mosConfig_db);

	// Let's see if the user already exists in Mambo
	$mos_sql = "
		SELECT username, block, password 
		FROM {$mosConfig_dbprefix}users 
		WHERE username = '$username';";
	$mos_result = mysql_query($mos_sql);
	$mos_array = mysql_fetch_array($mos_result);
	$mos_user = $mos_array[0];

	//if the user doesn't exist in Mambo, they've already been verified by SMF, so register them into Mambo as well
	if (!isset($mos_user))
	{
		// What Mambo group do we put you in?  Let's find the sync with $user_settings
		$mos_sync_groups = mysql_query("
				SELECT `value2`
				FROM {$mosConfig_dbprefix}smf_config
				WHERE `variable` = 'sync_group' AND `value1`='".$user_settings['id_group']."'
				");
		list($group) = mysql_fetch_row($mos_sync_groups);

		//Just in case....
		if (!isset($group) || $group == '' || $group == 0)
			$group = '18';
	
		$mos_write = mysql_query("
			INSERT INTO {$mosConfig_dbprefix}users 
				(name,username,email,password,gid) 
			VALUES ('$username', '$username', '$user_settings[email_address]', '$passwd', '$group')");

		$mos_find_id = mysql_query("
			SELECT id
			FROM {$mosConfig_dbprefix}users
			WHERE name = '$username'
			LIMIT 1");
		$mos_id_array = mysql_fetch_array($mos_find_id);
		$mos_id = $mos_id_array[0];

		$mos_write = mysql_query("
			INSERT INTO {$mosConfig_dbprefix}core_acl_aro 
				(section_value, value, order_value, name, hidden) 
			VALUES ('users', '$mos_id', '0', '$username', '0');");
		
		$mos_map_sql = mysql_query("
			SELECT id
			FROM {$mosConfig_dbprefix}core_acl_aro
			WHERE name = '$username'
			LIMIT 1");
		$mos_map_array = mysql_fetch_array($mos_map_sql);
		$aro_id = $mos_map_array[0];
		$mos_write = mysql_query ("
			INSERT INTO {$mosConfig_dbprefix}core_acl_groups_aro_map 
				(group_id, section_value, aro_id) 
			VALUES ('$group', '', '$aro_id');");

		//Do you have Community Builder?  Might has well get them in there too...
		if ($cb_reg=="on")
			$sql = mysql_query("
				INSERT INTO {$mosConfig_dbprefix}comprofiler 
					(id, user_id) 
				VALUES ('$mos_id', '$mos_id')");

		//maybe this user exists, but has not yet activated their account?

	} 
	elseif ($mos_array[1] == 1)
	{
		echo '
			<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
				alert("' . _LOGIN_BLOCKED . '"); 
				window.history.go(-1);
			// ]]></script>', "\n";
		exit();
	}
		
	$mos_pwd_qry = mysql_query("
		SELECT password 
		FROM {$mosConfig_dbprefix}users
		WHERE username = '$username'
		LIMIT 1");
	list($passwd) = mysql_fetch_row($mos_pwd_qry);
		
	$currentDate = date("Y-m-d\TH:i:s");
	mysql_query("
		UPDATE {$mosConfig_dbprefix}users
		SET lastvisitDate = '$currentDate'
		WHERE username = '$username'");

	// Get rid of the online entry for that old guest....
	
	// Log into Joomla now.....
	$request = mysql_query("
		SELECT username, password, id, gid, block, usertype
		FROM {$mosConfig_dbprefix}users
		WHERE username = '$username'
			AND password = '$passwd'");
	$row = mysql_fetch_array($request);

	//Some people are confused by case sensitivity....
	$credentials['username'] = $row['username'];	
    $credentials['password'] = $row['password'];
	
	// Get the global database connector object
	$db = $mainframe->getDBO();
	
	// Create a new user model and load the authenticated userid
	$user =& JModel::getInstance('user', $db);
	$user->load($row['id']);
	
	// Fudge the ACL stuff for now...
	// TODO: Implement ACL :)
	$acl = &JFactory::getACL();
	$grp = $acl->getAroGroup($user->id);
	$row->gid = 1;

	if ($acl->is_group_child_of($grp->name, 'Registered', 'ARO') || $acl->is_group_child_of($grp->name, 'Public Backend', 'ARO')) {
		// fudge Authors, Editors, Publishers and Super Administrators into the Special Group
		$user->gid = 2;
	}
	$user->usertype = $grp->name;

	// TODO: JRegistry will make this unnecessary
	// Register the needed session variables
	JSession::set('guest', 0);
	JSession::set('username', $user->username);
	JSession::set('userid', intval($user->id));
	JSession::set('usertype', $user->usertype);
	JSession::set('gid', intval($user->gid));
		// Register session variables to prevent spoofing
	JSession::set('JAuthenticate_RemoteAddr', $_SERVER['REMOTE_ADDR']);
	JSession::set('JAuthenticate_UserAgent', $_SERVER['HTTP_USER_AGENT']);

		// Get the session object
	$session = & $mainframe->_session;
	
	$session->guest = 0;
	$session->username = $user->username;
	$session->userid = intval($user->id);
	$session->usertype = $user->usertype;
	$session->gid = intval($user->gid);

	$session->update();

	// Hit the user last visit field
	$user->setLastVisit();

	// TODO: If we aren't going to use the database session we need to fix this
	// Set remember me option
	$remember = JRequest::getVar('remember');
	if ($remember == 'yes') {
		$session->remember($user->username, $user->password);
	}

	// Clean the cache for this user
	$cache = JFactory::getCache();
	$cache->cleanCache();

	//Try to set the redirect by the login module params...

	$sql = mysql_query ("
		SELECT params
		FROM {$mosConfig_dbprefix}modules
		WHERE module='mod_smf_login'");
	$result = mysql_fetch_array($sql);
	$paramlist = $result[0];
	$paramslogin = mosParseParams($paramlist);
	$returncheck = $paramslogin->login;

	if (!isset($Itemid) || $Itemid == 0)
		$Itemid = (int) $_REQUEST['Itemid'];

	$myurl = $mosConfig_live_site . '/' . basename($_SERVER['PHP_SELF']) . '?option=com_smf&amp;Itemid=' . $Itemid . '&amp;';


	// Default, in case we couldn't find anything, or the admin forgot to configure the login module
	// direct to SMF => 1
	// direct to frontpage => 0
	// option 2 cannot be used for default
	if (empty($_SESSION['return']) && ($returncheck == '' || $returncheck == '2'))
		$returncheck = '1';

	//Set the redirection URL
	switch ($returncheck)
	{
		//Redirect to Mambo
		case '0';
		$_SESSION['login_url'] = $mosConfig_live_site;
		break;

		//Redirect to SMF
		case '1';
		$_SESSION['login_url'] = $myurl;
		break;

		//Redirect back to login page
		case '2';
		$_SESSION['login_url'] = sefReltoAbs($_SESSION['return']);
		break;

		//There still might be nothing....
		case '';
		$_SESSION['login_url'] = $mosConfig_live_site;
		break;
	}

	mysql_select_db($db_name);
	
	return true;
}

function integrate_redirect (&$setLocation, $refresh)
{
	global $boardurl, $mosConfig_live_site, $mosConfig_sef;

	$myurl = basename($_SERVER['PHP_SELF']) . '?option=com_smf&amp;Itemid=' . $_REQUEST['Itemid'] . '&amp;' ;
	
	if ($setLocation == '')
		$setLocation = $mosConfig_sef == 1 ? sefReltoAbs($myurl) : $mosConfig_live_site . '/' . $myurl;
	
	$setLocation = ob_mambofix('="'.$setLocation.'"');
	$setLocation = str_replace('="','',$setLocation);
	$setLocation = str_replace('"','', $setLocation);
	$setLocation = un_htmlspecialchars($setLocation);	
}


function integrate_logout ($username)
{
	global $mosConfig_db, $mosConfig_dbprefix, $db_name, $mosConfig_live_site, $scripturl, $_VERSION, $mosConfig_sef;

	mysql_select_db($mosConfig_db);
    $jlogout = JAuthenticate::logout();
	
	//Try to set up the logout redirection
	if (!isset($_REQUEST['returnurl']))
	{
		$sql = mysql_query("
			SELECT params 
			FROM {$mosConfig_dbprefix}modules 
			WHERE module='mod_smf_login'");
		$result = mysql_fetch_array($sql);
		$paramlist = $result[0];
		$paramslogin = mosParseParams($paramlist);
		$returncheck = $paramslogin->logout;
	}
	else
		$returncheck = $_REQUEST['returnurl'];
	
	// Default, in case we couldn't find anything, or the admin forgot to configure the login module
	// direct to SMF => 1
	// direct to frontpage => 0
	// option 2 cannot be used for default	
	if (empty($_SESSION['return']) && ($returncheck == '' || $returncheck == '2'))
		$returncheck = "1";

	$myurl = basename($_SERVER['PHP_SELF']) . '?option=com_smf&amp;Itemid=' . $_REQUEST['Itemid'] . '&amp;' ;
		
	//Set the redirection URL
	switch ($returncheck)
	{
		//Redirect to Mambo frontpage
		case "0";
		$_SESSION['logout_url'] = $mosConfig_live_site;
		break;

		//Redirect to SMF
		case "1";
		$_SESSION['logout_url'] = $mosConfig_sef == 1 ? sefReltoAbs($myurl) : $mosConfig_live_site . '/' . $myurl;
		break;

		//Redirect back to logout page
		case "2";
		$_SESSION['logout_url'] = sefReltoAbs($_SESSION['return']);
		break;
	}
	
	mysql_select_db($db_name);
}

function integrate_delete_member($user)
{
	global $db_name, $db_prefix, $mosConfig_db, $mosConfig_dbprefix;

	$query = mysql_query ("
		SELECT member_name
		FROM {$db_prefix}members
		WHERE id_member = '$user'");
	list($username) = mysql_fetch_row($query);

	mysql_select_db($mosConfig_db);
	$query = mysql_query ("
		DELETE FROM {$mosConfig_dbprefix}users
		WHERE username = '$username'");

	mysql_select_db($db_name);
}

function integrate_validate_login($username, $password, $cookietime)
{
	global $db_name, $db_prefix, $mosConfig_db, $mosConfig_dbprefix, $use_realname;

	// Check if the user already exists in SMF.
	mysql_select_db($db_name);
	$request = mysql_query("
		SELECT id_member
		FROM {$db_prefix}members
		WHERE member_name = '$username'
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
			SELECT name, password, email, UNIX_TIMESTAMP(registerDate) AS date_registered, activation
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
				(member_name, real_name, passwd, email_address, date_registered, id_post_group, lngfile, buddy_list, pm_ignore_list, message_labels, personal_text, website_title, website_url, location, icq, msn, signature, avatar, usertitle, member_ip, secret_question, additional_groups)
			VALUES ('$username', '$name', '$mos_user[password]', '$mos_user[email]', $mos_user[date_registered], '4', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '')");
		$memberID = db_insert_id();
		
		updateStats('member', $memberID, $name);
			
		mysql_query("
			UPDATE {$db_prefix}log_activity 
			SET registers = registers + 1 
			WHERE date ='" . date("Y-m-d") . "' 
			LIMIT 1");
				
		return 'retry';
	}
}

function integrate_register($Options, $theme_vars)
{
	global $db_name, $db_prefix, $mosConfig_db, $mosConfig_dbprefix;
	global $cb_reg, $bridge_reg;
	
	//This function is only for SMF registration
	if ($bridge_reg!='SMF')
		return;
	
	mysql_select_db($mosConfig_db);

	// What Mambo group do we put you in?  SMF Newbies are group #4...
	$mos_sync_groups = mysql_query("
				SELECT `value2`
				FROM {$mosConfig_dbprefix}smf_config
				WHERE `variable` = 'sync_group' AND `value1`='4'
				");
	list($group) = mysql_fetch_row($mos_sync_groups);

	//Just in case....
	if (!isset($group) || $group == '' || $group == 0)
		$group = '18';
		
	//What if the real_name field isn't being used?
	if (!isset($Options['register_vars']['real_name']) || $Options['register_vars']['real_name']=='')
		$Options['register_vars']['real_name'] = $Options['register_vars']['member_name'];
				
	mysql_query("
		INSERT INTO {$mosConfig_dbprefix}users 
			(name, username, email, password, gid) 
		VALUES (" . $Options['register_vars']['real_name'] . ", " . $Options['register_vars']['member_name'] . ", " . $Options['register_vars']['email_address'] . ", '" . md5($_POST['passwrd1']) . "', '$group')");
	
	$mos_find_userid = mysql_query("
		SELECT `id`
		FROM {$mosConfig_dbprefix}users
		WHERE username = ".$Options['register_vars']['member_name']."
		LIMIT 1");
	list($mos_id) = mysql_fetch_row($mos_find_userid); 

	mysql_query("
		INSERT INTO {$mosConfig_dbprefix}core_acl_aro 
			(aro_id, section_value, value, order_value, name, hidden)
		VALUES ('', 'users', '$mos_id', '0', " . $Options['register_vars']['real_name'] . ", '0');");

	$mos_map_sql = mysql_query("
		SELECT aro_id
		FROM {$mosConfig_dbprefix}core_acl_aro
		WHERE name = ".$Options['register_vars']['real_name']."
		LIMIT 1");
	list($aro_id) = mysql_fetch_row($mos_map_sql);

	mysql_query ("
		INSERT INTO {$mosConfig_dbprefix}core_acl_groups_aro_map 
			(group_id, section_value, aro_id) 
		VALUES ('$group', '', '" . $aro_id . "');");

	//Do you have Community Builder?  Might has well get them in there too...
	if ($cb_reg == 'on')
		mysql_query("
			INSERT INTO {$mosConfig_dbprefix}comprofiler
				(id, user_id)
			VALUES ('$mos_id', '$mos_id')");

	mysql_select_db($db_name);
}

?>