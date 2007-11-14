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


/* This file is the core of the Mambo-SMF bridge component

	string ob_mambofix(string $buffer)
	- fixes URLs in SMF to point to Mambo component

	bool integrate_redirect (&$setLocation, $refresh)
	- sets redirection in forum for form submissions

	mambo_smf_exit(string $with_output)
	- exits SMF securely

	integrate_change_email($username, $email)
	- updates Mambo with email changes made in SMF
	
	integrate_change_member_data ( array $memberNames, string $var, string $value)
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

/** ensure this file is being included by a parent file and stop direct linking */
defined( '_VALID_MOS' ) or die( 'Direct Access to this location is not allowed.' );

//This helps Mambo allow posts with HTML.  Please note that this does not necessarily work with all installations.
if (!defined('_MOS_ALLOWHTML'))
	define('_MOS_ALLOWHTML', 0x0002);
	
// Raise the memory limit -- Loading Mambo/Joomla and SMF can take a lot of RAM
@ini_set('memory_limit', '16M');

// Just to make sure nobody tries it
@ini_set('allow_url_fopen', 0);

global $params, $database, $mosConfig_dbprefix, $db_prefix, $mosConfig_db, $txt;
global $mosConfig_absolute_path, $wrapped, $mosConfig_live_site, $mosConfig_sef;
global $bridge_reg, $menu, $Itemid, $context, $cb_reg, $database, $smf_css, $synch_lang, $language_conversion;

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


require_once ($mosConfig_absolute_path . '/components/com_smf/smf_integration_arrays.php');

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
	global $bridge_reg, $Itemid, $_VERSION, $wrapped;

	//Don't rewrite URLs if this is a database dump 
	if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'dumpdb')
		return;

	$myurl = $mosConfig_live_site . '/' . basename($_SERVER['PHP_SELF']) . '?option=com_smf&amp;Itemid=' . $Itemid . '&amp;';
	
	// jumpto redirects
	$buffer = str_replace('"?board=', $mosConfig_sef=='1' ? '"/board,' : '"&amp;board=', $buffer);
	$buffer = str_replace('"?action=', $mosConfig_sef=='1' ? '"/action,' : '"&amp;action=', $buffer);
	//relative anchors
	$buffer = str_replace('href="#', 'href="' . $mosConfig_live_site . '/' . basename($_SERVER['PHP_SELF']) . '?' . $_SERVER['QUERY_STRING'] . '#', $buffer);
	//get rid of the question mark
	$buffer = str_replace('"' . $scripturl . '?', '"' . $myurl, $buffer);
	//if it's the forum index, we don't need a trailing ampersand
	$buffer = str_replace('"' . $scripturl . '"', '"' . substr($myurl, 0, -5) . '"', $buffer);
	//make sure there are no html entities in the javascript of the unwrapped forum
	if ($mosConfig_sef != '1' && $wrapped != 'true')
		$buffer = str_replace('var smf_scripturl = "'.substr($myurl, 0, -5).'"', un_htmlspecialchars('var smf_scripturl = "'.substr($myurl, 0, -5).'"'), $buffer);
	//Sometimes links are inside single quotes
	$buffer = str_replace('\'' . $scripturl . '?', '\'' . $myurl, $buffer);
	$buffer = str_replace('\'' . $scripturl, '\'' . $myurl, $buffer);
	//Don't forget XML feeds
	$buffer = str_replace('<link>'.$scripturl . '?', '<link>'.$myurl, $buffer );
	$buffer = str_replace('<link>'.$scripturl, '<link>'.$myurl, $buffer );
	$buffer = str_replace('<comments>'.$scripturl . '?', '<comments>'.$myurl, $buffer );
	$buffer = str_replace('<comments>'.$scripturl, '<comments>'.$myurl, $buffer );
	$buffer = str_replace('<guid>'.$scripturl . '?', '<guid>'.$myurl, $buffer );
	$buffer = str_replace('<guid>'.$scripturl, '<guid>'.$myurl, $buffer );
	//An ampersand followed by a # is not kosher
	$buffer = str_replace($scripturl . '#', substr($myurl, 0, -5) . '#', $buffer);
	//SMF admin panel does some funky things after admin login...luckily it's easy to fix
	$buffer = str_replace('option=com_smf;Itemid=' . $Itemid . ';', '', $buffer);

	// New bridged profile options.  Not yet available
	//$buffer = str_replace($scripturl . 'action=profile;', $mosConfig_live_site . '/' . basename($_SERVER['PHP_SELF']) . '?option=com_smf_profile&task=view&', $buffer);
	//$buffer = str_replace($scripturl . 'action=profile', $mosConfig_live_site . '/' . basename($_SERVER['PHP_SELF']) . '?option=com_smf_profile&task=view', $buffer);

	// Bridge registration links
	switch ($bridge_reg){  
		case "bridge":
			$buffer = str_replace($myurl . 'action=register', $mosConfig_live_site . '/' . basename($_SERVER['PHP_SELF']) . '?option=com_smf_registration&amp;task=register', $buffer);
			$buffer = str_replace($myurl . 'action=activate"', $mosConfig_live_site . '/' . basename($_SERVER['PHP_SELF']) . '?option=com_smf_registration&amp;task=lostCode"', $buffer);
			$buffer = str_replace($myurl . 'action=reminder', $mosConfig_live_site . '/' . basename($_SERVER['PHP_SELF']) . '?option=com_smf_registration&amp;task=lostPassword', $buffer);
		break;
		
		case "default":
			$buffer = str_replace($myurl . 'action=register', $mosConfig_live_site . '/' . basename($_SERVER['PHP_SELF']) . '?option=com_registration&amp;task=register', $buffer);
			$buffer = str_replace($myurl . 'action=activate"', $mosConfig_live_site . '/' . basename($_SERVER['PHP_SELF']) . '?option=com_smf_registration&amp;task=lostCode"', $buffer);
			$buffer = str_replace($myurl . 'action=reminder', $mosConfig_live_site . '/' . basename($_SERVER['PHP_SELF']) . '?option=com_smf_registration&amp;task=lostPassword', $buffer);
		break;
		
		case "CB":
			$buffer = str_replace($myurl . 'action=register', $mosConfig_live_site . '/' . basename($_SERVER['PHP_SELF']) . '?option=com_comprofiler&amp;task=registers', $buffer);
			$buffer = str_replace($myurl . 'action=activate"', $mosConfig_live_site . '/' . basename($_SERVER['PHP_SELF']) . '?option=com_smf_registration&amp;task=lostCode"', $buffer);
			$buffer = str_replace($myurl . 'action=reminder', $mosConfig_live_site . '/' . basename($_SERVER['PHP_SELF']) . '?option=com_smf_registration&amp;task=lostPassword', $buffer);
		break;
		
		case "jw":
			$buffer = str_replace($myurl . 'action=register', $mosConfig_live_site . '/' . basename($_SERVER['PHP_SELF']) . '?option=com_jw_registration&amp;task=register', $buffer);
			$buffer = str_replace($myurl . 'action=activate"', $mosConfig_live_site . '/' . basename($_SERVER['PHP_SELF']) . '?option=com_smf_registration&amp;task=lostCode"', $buffer);
			$buffer = str_replace($myurl . 'action=reminder', $mosConfig_live_site . '/' . basename($_SERVER['PHP_SELF']) . '?option=com_smf_registration&amp;task=lostPassword', $buffer);
		break;
		
		case "AEC":
			$buffer = str_replace($myurl . 'action=register', $mosConfig_live_site . '/' . basename($_SERVER['PHP_SELF']) . '?option=com_acctexp&amp;task=register', $buffer);
			$buffer = str_replace($myurl . 'action=activate"', $mosConfig_live_site . '/' . basename($_SERVER['PHP_SELF']) . '?option=com_smf_registration&amp;task=lostCode"', $buffer);
			$buffer = str_replace($myurl . 'action=reminder', $mosConfig_live_site . '/' . basename($_SERVER['PHP_SELF']) . '?option=com_smf_registration&amp;task=lostPassword', $buffer);
		break;		
	}

	// Don't forget attachments and CAPTCHA
	$buffer = str_replace($myurl . 'action=dlattach', $boardurl . '/' . basename($_SERVER['PHP_SELF']) . '?action=dlattach', $buffer);
	$buffer = str_replace($myurl . 'action=verificationcode', $boardurl . '/' . basename($_SERVER['PHP_SELF']) . '?action=verificationcode', $buffer);

	//And Niko's Arcade Mod
	$buffer = str_replace($myurl . 'action=arcade;sa=download;file=swf', $boardurl . '/' . basename($_SERVER['PHP_SELF']) . '?action=arcade;sa=download;file=swf', $buffer);
	// and now for SEF
	if (!empty($mosConfig_sef) && $mosConfig_sef == '1')
	{
		preg_match_all('~([\(=]")' . preg_quote($mosConfig_live_site . '/index.php?option=com_smf') . '([^"]*)"{1}~', $buffer, $nonsefurls);
		foreach($nonsefurls[0] as $nonsefurl)
		{
			$nqsefurl = substr($nonsefurl, 0, strpos($nonsefurl, 'option')) . preg_replace('/(\;)([^=#]*)([\;#"])/', '$1$2=$2$3', substr($nonsefurl, strpos($nonsefurl, 'option'), strlen($nonsefurl)));
			$sefurl = sefReltoAbs(substr($nqsefurl, strlen($mosConfig_live_site) + 3, strlen($nqsefurl) - strlen($mosConfig_live_site) - 4));
			$sefurl = str_replace(";", "/", $sefurl);
			$sefurl = str_replace("=", ",", $sefurl);
			if (substr($sefurl, strlen($sefurl) - 1, 1) == '/')
				$sefurl = substr($sefurl, 0, strlen($sefurl) - 1);
			$buffer = str_replace(substr($nonsefurl,1,strlen($nonsefurl)), '"' . $sefurl . '"', $buffer);
			//Fix for Joomla! 1.0.10 fragment
			$buffer = str_replace("/#", "#", $buffer);
		}
	}
	return $buffer;
}

function mambo_smf_url($url)
{
	global $scripturl, $Itemid, $mosConfig_live_site;

	if ($Itemid == 0)
		$Itemid = (int) $_REQUEST['Itemid'];
	//The ampersands need to be non-entities, because they are used mainly in javascript
	$myurl = $mosConfig_live_site . '/' . basename($_SERVER['PHP_SELF']) . '?option=com_smf&Itemid=' . $Itemid . '&';
	$url = str_replace($scripturl . '?', $myurl, $url);
	$url = str_replace($scripturl, $myurl, $url);
	

	return $url;
}

function mambo_smf_exit($with_output)
{
	global $wrapped, $mosConfig_db, $mosConfig_dbprefix, $database, $cur_template, $mainframe, $boardurl, $smf_css, $mosConfig_sef, $mosConfig_debug, $db_name;

	$buffer = ob_get_contents();
	ob_end_clean();

	if (!$with_output || $wrapped !='true')
	{		
		//$buffer = mambo_smf_url($buffer);
		$buffer = ob_mambofix($buffer);
		echo $buffer;	
		exit;
	}

	//We want to pull in the rest of the globals, but use then as locals, so they won't affect anything outside this function
	foreach ($GLOBALS as $name => $value)
		$$name = &$GLOBALS[$name];

	$_MOS_OPTION['buffer'] = ob_mambofix($buffer);


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

	$mainframe->addCustomHeadTag( '<script language="JavaScript" type="text/javascript" src="'. $settings['default_theme_url']. '/script.js?fin11"></script>' );
	$mainframe->addCustomHeadTag( '<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
		var smf_theme_url = "'. $settings['theme_url']. '";
		var smf_images_url = "'. $settings['images_url']. '";');
	if ($mosConfig_sef=='1')
		$mainframe->addCustomHeadTag( ob_mambofix('var smf_scripturl ="'. $scripturl . '";'));
	else
		$mainframe->addCustomHeadTag( 'var smf_scripturl = "'. un_htmlspecialchars(mambo_smf_url($scripturl)) . '";');

	$mainframe->addCustomHeadTag( '	var smf_iso_case_folding = '. $context['server']['iso_case_folding'] ? 'true' : 'false'. ';
		var smf_charset = "'. $context['character_set']. '";');
	$mainframe->addCustomHeadTag( '	var smf_session_id = "'. $context['session_id'] . '";
		// ]]></script>' );
	if ($smf_css == 'true'){
		$mainframe->addCustomHeadTag( '<link rel="stylesheet" type="text/css" href="'. $settings['theme_url']. '/style.css?fin11" />' );
		$mainframe->addCustomHeadTag( '<link rel="stylesheet" type="text/css" href="'. $settings['default_theme_url']. '/css/print.css?fin11" media="print" />' );
	}
	$mainframe->addCustomHeadTag( '<link rel="help" href="'. ( $mosConfig_sef == 1 ? sefReltoAbs($myurl . 'action=help') : $mosConfig_live_site . '/'. $myurl . 'action=help' ).'" target="_blank" />' );
	$mainframe->addCustomHeadTag( '<link rel="search" href="' . ( $mosConfig_sef == 1 ? sefReltoAbs($myurl . 'action=search') : $mosConfig_live_site . '/'. $myurl . 'action=search' ) .'" />' );
	$mainframe->addCustomHeadTag( '<link rel="contents" href="'. ( $mosConfig_sef == 1 ? sefReltoAbs($myurl) : $mosConfig_live_site . '/'. $myurl ) . '" />' );

	// If RSS feeds are enabled, advertise the presence of one. 
	if (!empty($modSettings['xmlnews_enable']))  
		$mainframe->addCustomHeadTag( '<link rel="alternate" type="application/rss+xml" title="'. $context['forum_name']. ' - RSS" href="'. ( $mosConfig_sef == 1 ? sefReltoAbs($myurl. 'type=rss&amp;action=.xml') : $mosConfig_live_site . '/'. $myurl . 'type=rss&amp;action=.xml') . '" />' ); 

	// If we're viewing a topic, these should be the previous and next topics, respectively. 
	if (!empty($context['current_topic'])){ 
		$mainframe->addCustomHeadTag( '<link rel="prev" href="'. ( $mosConfig_sef == 1 ? sefReltoAbs($myurl. 'topic='. $context['current_topic']. '.0&amp;prev_next=prev') : $mosConfig_live_site . '/'. $myurl . 'topic='. $context['current_topic']. '.0;prev_next=prev') . '" />'); 
		$mainframe->addCustomHeadTag( '<link rel="next" href="'. ( $mosConfig_sef == 1 ? sefReltoAbs($myurl. 'topic='. $context['current_topic']. '.0&amp;prev_next=next') : $mosConfig_live_site . '/'. $myurl . 'topic='. $context['current_topic']. '.0;prev_next=next') . '" />');
	}
	// If we're in a board, or a topic for that matter, the index will be the board's index. 
	if (!empty($context['current_board'])) 
		$mainframe->addCustomHeadTag( '<link rel="index" href="' . ( $mosConfig_sef == 1 ? sefReltoAbs($myurl . 'board=' . $context['current_board'] . '.0') :  $mosConfig_live_site . '/' . $myurl . 'board=' . $context['current_board'] . '.0') .'" />'); 

	// We'll have to use the cookie to remember the header... 
	if ($context['user']['is_guest']) 
		$options['collapse_header'] = !empty($_COOKIE['upshrink']);

	// Output any remaining HTML headers. (from mods, maybe?) 
	$mainframe->addCustomHeadTag(((!isset($_REQUEST['action']) || (isset($_REQUEST['action']) && $_REQUEST['action']!= 'login2')) ? ob_mambofix($context['html_headers']): '') . '
			<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[ 
			var current_header = ' . (empty($options['collapse_header']) ? 'false' : 'true') . '; 
 
          function shrinkHeader(mode) 
          {' ); 
	
	// Guests don't have theme options!! 
	if ($context['user']['is_guest']) 
		$mainframe->addCustomHeadTag( ' document.cookie = "upshrink=" + (mode ? 1 : 0);'); 
	else 
		$mainframe->addCustomHeadTag(' smf_setThemeOption("collapse_header", mode ? 1 : 0, null, "'. $context['session_id']. '");'); 
 
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
               var current_header_ic = '. (empty($options['collapse_header_ic']) ? 'false' : 'true'). '; 
 
               function shrinkHeaderIC(mode) 
               {'); 
 
	if ($context['user']['is_guest']) 
		$mainframe->addCustomHeadTag( ' 
                    document.cookie = "upshrinkIC=" + (mode ? 1 : 0);'); 
	else 
		$mainframe->addCustomHeadTag( ' 
                    smf_setThemeOption("collapse_header_ic", mode ? 1 : 0, null, "'. $context['session_id']. '");'); 
 
	$mainframe->addCustomHeadTag( ' 
                    document.getElementById("upshrink_ic").src = smf_images_url + (mode ? "/expand.gif" : "/collapse.gif"); 
 
                    document.getElementById("upshrinkHeaderIC").style.display = mode ? "none" : ""; 
 
                    current_header_ic = mode; 
               } 
          // ]]></script>');
	
	// Get last item.
	end($context['linktree']);
	$last_link_key = key($context['linktree']);
	
	foreach ($context['linktree'] as $link_num => $tree)
	{
		if ($link_num > 0) { // Don't show first linktree element, because forum menu item will already be in Mambo Pathway.
			//If there is a url and this is not the last link item, show as a link. Otherwise just show.
			$mainframe->appendPathWay(((isset($tree['url']) && ($last_link_key != $link_num)) ? ob_mambofix('<a href="' . $tree['url'] . '" class="pathway">') . $tree['name'] . '</a>' : $tree['name']) . ' ');
		}
	}	

	// Ignore notices from Mambo.
	error_reporting(0);

	initGzip();

	// Loads template file.
	if (!file_exists('templates/' . $cur_template . '/index.php')) 
		echo _TEMPLATE_WARN . $cur_template;
	else
	{
		require_once('templates/' . $cur_template . '/index.php');
		echo '<!-- ' . time() . ' -->';
	}

	// Displays queries performed for page.
	if ($mosConfig_debug) 
	{
		echo $database->_ticker . ' queries executed';
		echo '<pre>';
		foreach ($database->_log as $k => $sql)
			echo $k + 1, "\n", $sql, '<hr />';
	}

	doGzip();
	
	mysql_select_db($db_name);

	die;
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
		SET emailAddress = '$email'
		WHERE username = '" . addslashes($username) . "'
		LIMIT 1");
	mysql_select_db($db_name);

	return true;
}

function integrate_change_member_data ($memberNames, $var, $value)
{

	global $mosConfig_db, $db_name, $mosConfig_dbprefix;
	
	$synch_mambo_fields = array(
   			'memberName' => 'username',
			'realName' => 'name',
			'emailAddress' => 'email',
			'ID_GROUP' => '',
			'gender'=>'',
			'birthdate'=>'',
			'websiteTitle'=>'',
			'websiteUrl'=>'',
			'location'=>'',
			'hideEmail'=>'',
			'timeFormat'=>'',
			'timeOffset'=>'',
			'avatar'=>'',
			'lngfile'=>'',
			);

	$field = $synch_mambo_fields[$var];

	if ($field != ''){
		mysql_select_db($mosConfig_db);
	
		foreach ($memberNames as $memberName){
			mysql_query ("UPDATE {$mosConfig_dbprefix}users
						SET `$field` = $value
						WHERE username = '$memberName'
						LIMIT 1");
						
			//  If the real name is changed, we need to make sure to update the ACL
			if ($var == 'realName'){
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
		
	if ($var == 'ID_GROUP'){
		mysql_select_db($mosConfig_db);
		
		$query = mysql_query (" SELECT `value2`
					FROM {$mosConfig_dbprefix}smf_config
					WHERE `variable` = 'sync_group' AND `value1` = $value");
		list($group) = mysql_fetch_row($query);
		
		//Just in case....
		if (!isset($group) || $group == '' || $group == 0 )
			$group = '18';
		
		foreach ($memberNames as $memberName){

			mysql_query ("UPDATE {$mosConfig_dbprefix}users
						SET `gid` = '$group'
						WHERE username = '$memberName'
						");
			$mos_find_name = mysql_query("
						SELECT `name`
						FROM {$mosConfig_dbprefix}users
						WHERE username = '$memberName'
						LIMIT 1");
			list($mos_name) = mysql_fetch_row($mos_find_name);
			$mos_map_sql = mysql_query("
						SELECT aro_id
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
	global $boardurl, $mosConfig_live_site, $Itemid, $scripturl, $mosConfig_sef, $modSettings, $Itemid, $hotmail_fix;

	//First, we need to set up the email so that ob_mambofix knows what to do with it
	$message = str_replace ($scripturl, '="' . $scripturl, $message);
	$message = preg_replace ('/(http.+)(\b)/', '$1"', $message);
	$message = ob_mambofix($message);
	//Now we need to undo those changes so the email looks normal again
	$message = str_replace ('="', '', $message);
	$message = preg_replace ('/(http.+)(")/', '$1', $message);
	//THis is an email, after all, so let's make sure entities and special characters are text, not HTML
	$message = trim($message);
    $message = html_entity_decode($message);
	$message = un_htmlspecialchars($message);
	$hotmail_fix = false;
	return true;
}


function integrate_login($username, $passwd, $cookielength)
{
	global $mosConfig_db, $mosConfig_dbprefix, $user_settings, $db_name, $_VERSION;
	global $scripturl, $cb_reg, $mosConfig_absolute_path, $mosConfig_live_site, $database;

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
				WHERE `variable` = 'sync_group' AND `value1`='" . $user_settings['ID_GROUP'] . "'
				");
		list($group) = mysql_fetch_row($mos_sync_groups);

		//Just in case....
		if (!isset($group) || $group == '' || $group == 0 )
			$group = '18';
	
		$mos_write = mysql_query("
			INSERT INTO {$mosConfig_dbprefix}users 
				(name,username,email,password,gid) 
			VALUES ('$username', '$username', '$user_settings[emailAddress]', '$passwd', '$group')");

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
			SELECT aro_id
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

	//We'll need the real password to login to Mambo/Joomla, and the id for AEC
	$mos_pwd_qry = mysql_query("
		SELECT id, password 
		FROM {$mosConfig_dbprefix}users
		WHERE username = '$username'
		LIMIT 1");
	list($id, $passwd) = mysql_fetch_row($mos_pwd_qry);
		
	$currentDate = date("Y-m-d\TH:i:s");
	mysql_query("
		UPDATE {$mosConfig_dbprefix}users
		SET lastvisitDate = '$currentDate'
		WHERE username = '$username'");

	//Did we install AEC?  Maybe we should check for an expiration....
	if (file_exists($mosConfig_absolute_path . '/components/com_acctexp/acctexp.php')){
		$exp_query = mysql_query("
			SELECT expiration 
			FROM {$mosConfig_dbprefix}acctexp 
			WHERE userid=$id AND expiration<>'9999-12-31'");
		list($expiration) = mysql_fetch_row($exp_query);
		if ($expiration) {
			$expiration = $expiration . " 00:00:00";
			$expstamp = strtotime($expiration);
			$status_query = mysql_query("
				SELECT status 
				FROM {$mosConfig_dbprefix}acctexp_subscr 
				WHERE userid=$id");
			$expiration = null;
			list($status) = mysql_fetch_row($status_query);
			if ($status=='Pending') {
				mosRedirect( sefReltoAbs('index.php?option=com_acctexp&task=pending&userid=' . $id) );
				exit();
			}
			if ( $status=='Closed' || $status=='Cancelled' || ( ($expstamp > 0) && ( $expstamp-(time()+$mosConfig_offset_user*3600) < 0 ) )) {
				mosRedirect( sefReltoAbs('index.php?option=com_acctexp&task=expired&userid=' . $id . '&expiration=' . strftime(_ACCT_DATE_FORMAT, $expstamp)));
				exit();
			}
		}
	}
	
	// Log into Mambo now.....
	$request = mysql_query("
		SELECT username, id, gid, block, usertype
		FROM {$mosConfig_dbprefix}users
		WHERE username = '$username'
			AND password = '$passwd'");
	$row = mysql_fetch_array($request);

	//Some people are confused by case sensitivity....
	$username = $row['username'];	

	// Fudge the group stuff.
	$session =& $this['_session'];
	$session['guest'] = 0;
	$session['username'] = $username;
	$session['userid'] = intval($row['id']);
	$session['usertype'] = $row['usertype'];
	$session['gid'] = intval($row['gid']);

	$currentDate = date("Y-m-d\TH:i:s");
	mysql_query("
		UPDATE {$mosConfig_dbprefix}users 
		SET lastvisitDate = '$currentDate' 
		WHERE id = '$session[userid]'");

	$lifetime = time() + (60 * $cookielength);
	setcookie('usercookie[username]', $username, $lifetime, '/');
	setcookie('usercookie[password]', $passwd, $lifetime, '/');
	setcookie('sessioncookie', '', -3600, '/');
	
	//Let's make sure this works in both Mambo and Joomla
	$sessionCookieName = md5('site' . $mosConfig_live_site);
	setcookie($sessionCookieName, '', -3600, '/');
	
	//Joomla 1.0.8 compatibility
	if (isset($_VERSION) && $_VERSION->PRODUCT == 'Joomla!' && $_VERSION->DEV_LEVEL >= '8'){
		$remCookieName 	= mosMainFrame::remCookieName_User();
						//Joomla 1.0.9 compatibility
                        if ($_VERSION->DEV_LEVEL>='9')
							$remCookieValue = mosMainFrame::remCookieValue_User( $username ) . mosMainFrame::remCookieValue_Pass( $passwd ) . $row['id'];
                        else
							$remCookieValue = mosMainFrame::remCookieValue_User( $username ) . mosMainFrame::remCookieValue_Pass( $passwd );
		setcookie( $remCookieName, $remCookieValue, $lifetime, '/' );
	}

	
	//Let's try to minimize the effects of those nasty extra sessions
	$sql = mysql_query("
		DELETE FROM {$mosConfig_dbprefix}session
		WHERE username = '$username'");
		
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
		case '0':
		$_SESSION['login_url'] = $mosConfig_live_site;
		break;

		//Redirect to SMF
		case '1':
		$_SESSION['login_url'] = $myurl;
		break;

		//Redirect back to login page
		case '2':
		if (strpos($_SESSION['return'],'register')===false || strpos($_SESSION['return'],'activate')===false)
			$_SESSION['login_url'] = sefReltoAbs($_SESSION['return']);
		else 
			$_SESSION['login_url'] = $myurl;
		break;

		//There still might be nothing....
		case '':
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
	
	$setLocation = ob_mambofix('="' . $setLocation . '"');
	$setLocation = str_replace('="','',$setLocation);
	$setLocation = str_replace('"','', $setLocation);
	$setLocation = un_htmlspecialchars($setLocation);	
}


function integrate_logout ($username)
{
	global $mosConfig_db, $mosConfig_dbprefix, $db_name, $mosConfig_live_site, $scripturl, $_VERSION, $mosConfig_sef;

	setcookie('usercookie[username]', $username, time() - 3600, '/');
	setcookie('usercookie[password]', '', time() - 3600, '/');
	setcookie('sessioncookie' , '' , time() - 3600 , '/');

	//Let's make sure this works in both Mambo and Joomla
	$sessionCookieName = md5('site' . $mosConfig_live_site);
	setcookie($sessionCookieName, '', time() - 3600, '/');
	
	//Joomla 1.0.8 compatibilty
	if (isset($_VERSION) && $_VERSION->PRODUCT == 'Joomla!' && $_VERSION->DEV_LEVEL >= '8'){
		$lifetime 		= time() - 86400;
		$remCookieName 	= mosMainFrame::remCookieName_User();
		setcookie( $remCookieName, ' ', $lifetime, '/' );
	}

	mysql_select_db($mosConfig_db);

	// Let's try to minimize the effects of those nasty extra sessions.
	$sql = mysql_query("
		DELETE FROM {$mosConfig_dbprefix}session
		WHERE username = '$username'");
	
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

	$query1 = mysql_query ("
		SELECT memberName, realName
		FROM {$db_prefix}members
		WHERE ID_MEMBER = '$user'");
	list($username, $name) = mysql_fetch_row($query1);

	mysql_select_db($mosConfig_db);

	$query2 = mysql_query ("
		DELETE FROM {$mosConfig_dbprefix}users
		WHERE username = '$username'");
	$query3 = mysql_query ("
		SELECT aro_id
		FROM {$mosConfig_dbprefix}core_acl_aro
		WHERE name = '$name'");
	list($aro_id) = mysql_fetch_row($query3);
	$query4 = mysql_query ("
		DELETE FROM {$mosConfig_dbprefix}core_acl_aro
		WHERE name = '$name'");
	$query5 = mysql_query ("
		DELETE FROM {$mosConfig_dbprefix}core_acl_groups_aro_map
		WHERE aro_id = '$aro_id'");

	mysql_select_db($db_name);
}

function integrate_validate_login($username, $password, $cookietime)
{
	global $db_name, $db_prefix, $mosConfig_db, $mosConfig_dbprefix, $use_realname, $database;

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
	if (!isset($group) || $group == '' || $group == 0 )
		$group = '18';

	//registration will be considered now
	$r_date = date("Y-m-d H:i:s");
		
	//What if the realName field isn't being used?
	if (!isset($Options['register_vars']['realName']) || $Options['register_vars']['realName']=='')
		$Options['register_vars']['realName'] = $Options['register_vars']['memberName'];
				
	mysql_query("
		INSERT INTO {$mosConfig_dbprefix}users 
			(name, username, email, password, registerDate, gid) 
		VALUES (" . $Options['register_vars']['realName'] . ", " . $Options['register_vars']['memberName'] . ", " . $Options['register_vars']['emailAddress'] . ", '" . md5($Options['password']) . "', '$r_date', '$group')");
	
	$mos_find_userid = mysql_query("
		SELECT `id`
		FROM {$mosConfig_dbprefix}users
		WHERE username = " . $Options['register_vars']['memberName'] . "
		LIMIT 1");
	list($mos_id) = mysql_fetch_row($mos_find_userid); 

	mysql_query( "
		INSERT INTO {$mosConfig_dbprefix}core_acl_aro 
			(aro_id, section_value, value, order_value, name, hidden)
		VALUES ('', 'users', '$mos_id', '0', " . $Options['register_vars']['realName'] . ", '0');");

	$mos_map_sql = mysql_query("
		SELECT aro_id
		FROM {$mosConfig_dbprefix}core_acl_aro
		WHERE name = " . $Options['register_vars']['realName'] . "
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

function integrate_pre_load () {


// Try to modify settings so that bridging is less problematic if people have the wrong settings
	global $modSettings, $smf_path;

	//Turn off compressed output
	$modSettings['enableCompressedOutput'] = '0';
	//Turn off local cookies
	$modSettings['localCookies'] = '0';
	//Turn off SEF in SMF
	$modSettings['queryless_urls'] = '';
	
// Change the SMF language according to the Mambo/Joomla settings

	global $mosConfig_lang, $language, $synch_lang, $language_conversion;


	if(isset($mosConfig_lang) && $synch_lang == 'true'){

		if (isset($_COOKIE['mbfcookie']) || isset($_REQUEST['lang'])){
          
			if (isset($_COOKIE['mbfcookie']['lang'])){ 
				if (isset($language_conversion[$_COOKIE['mbfcookie']['lang']]) && file_exists($smf_path . '/Themes/default/languages/index.' . $language_conversion[$_COOKIE['mbfcookie']['lang']] . '.php'))
					$GLOBALS['language'] = $language_conversion[$_COOKIE['mbfcookie']['lang']];
				else if (isset($language_conversion[$_COOKIE['mbfcookie']['lang']]) && file_exists($smf_path . '/Themes/default/languages/index.' . $language_conversion[$_COOKIE['mbfcookie']['lang']] . '-utf8.php'))
					$GLOBALS['language'] = $language_conversion[$_COOKIE['mbfcookie']['lang']] . '-utf8';
				else if (file_exists($smf_path . '/Themes/default/languages/index.' . $_COOKIE['mbfcookie']['lang'] . '.php'))
					$GLOBALS['language'] = $_COOKIE['mbfcookie']['lang'];
				else if (file_exists($smf_path . '/Themes/default/languages/index.' . $_COOKIE['mbfcookie']['lang'] . '-utf8.php'))
					$GLOBALS['language'] = $_COOKIE['mbfcookie']['lang'] . '-utf8';
			}

			if (isset($_REQUEST['lang'])){
				if (isset($language_conversion[substr($_REQUEST['lang'],0,2)]) && file_exists($smf_path . '/Themes/default/languages/index.' . $language_conversion[substr($_REQUEST['lang'],0,2)] . '.php'))
					$GLOBALS['language'] = $language_conversion[substr($_REQUEST['lang'],0,2)];
				else if (isset($language_conversion[substr($_REQUEST['lang'],0,2)]) && file_exists($smf_path . '/Themes/default/languages/index.' . $language_conversion[substr($_REQUEST['lang'],0,2)] . '-utf8.php'))
					$GLOBALS['language'] = $language_conversion[substr($_REQUEST['lang'],0,2)] . '-utf8';					
				else if (file_exists($smf_path . '/Themes/default/languages/index.' . $_REQUEST['lang'] . '.php'))
					$GLOBALS['language'] = $_REQUEST['lang'];					
				else if (file_exists($smf_path . '/Themes/default/languages/index.' . $_REQUEST['lang'] . '-utf8.php'))
					$GLOBALS['language'] = $_REQUEST['lang'] . '-utf8';					
			}
			
		} else if ($synch_lang == 'true')
			$GLOBALS['language'] = $mosConfig_lang;
	}
}

function integrate_whos_online ($actions) {

	global $txt, $database, $mosConfig_db, $mosConfig_dbprefix, $db_name;
	//First, we need to add the new language strings
	add_to_txt();

	//it must be the main page
	if (!isset($actions['option']))
		return $txt['who_home'];

	//let's make sure there is an option...
	if (isset($actions['option'])){
		//still the main page
		if ($actions['option']=='com_frontpage')
			return $txt['who_home'];
		//It's the forum ;)
		else if ($actions['option']=='com_smf')
			return $txt['who_index'];
		//let's try the content
		else if ($actions['option']=='com_content'){
			if (isset($actions['task'])){
				if($actions['task']=='view'){
					mysql_select_db($mosConfig_db);
					$mos_find_article = mysql_query ("SELECT title
												FROM {$mosConfig_dbprefix}content
												WHERE id = $actions[id]
												LIMIT 1");
					list ($article_name) = mysql_fetch_row($mos_find_article);
					mysql_select_db($db_name);
					return sprintf($txt['who_article'], $actions['id'], $actions['Itemid'], $article_name);
				}
				if ($actions['task']=='section'){
					mysql_select_db($mosConfig_db);
					$mos_find_article = mysql_query ("SELECT title
												FROM {$mosConfig_dbprefix}sections
												WHERE id = $actions[id]
												LIMIT 1");
					list ($section_name) = mysql_fetch_row($mos_find_article);
					mysql_select_db($db_name);
					return sprintf($txt['who_section'], $actions['id'], $actions['Itemid'], $section_name);
				}
				if ($actions['task']=='blogsection'){
					mysql_select_db($mosConfig_db);
					$mos_find_article = mysql_query ("SELECT title
												FROM {$mosConfig_dbprefix}sections
												WHERE id = $actions[id]
												LIMIT 1");
					list ($section_name) = mysql_fetch_row($mos_find_article);
					mysql_select_db($db_name);
					return sprintf($txt['who_blogsection'], $actions['id'], $actions['Itemid'], $section_name);
				}
				if ($actions['task']=='category'){
					mysql_select_db($mosConfig_db);
					$mos_find_article = mysql_query ("SELECT title
												FROM {$mosConfig_dbprefix}categories
												WHERE id = $actions[id]
												LIMIT 1");
					list ($category_name) = mysql_fetch_row($mos_find_article);
					mysql_select_db($db_name);
					return sprintf($txt['who_category'], $actions['id'], $actions['Itemid'], $category_name);
				}
				if ($actions['task']=='blogcategory'){
					mysql_select_db($mosConfig_db);
					$mos_find_article = mysql_query ("SELECT title
												FROM {$mosConfig_dbprefix}categories
												WHERE id = $actions[id]
												LIMIT 1");
					list ($category_name) = mysql_fetch_row($mos_find_article);
					mysql_select_db($db_name);
					return sprintf($txt['who_blogcategory'], $actions['id'], $actions['Itemid'], $category_name);
				}
			}
		}
		else if ($actions['option']=='com_newsfeeds'){
			if (isset($actions['task'])){
				if ($actions['task']=='view')
					return sprintf($txt['who_newsfeeds'], $actions['Itemid']);
			}
		}
		//Site search
		else if ($actions['option']=='com_search')
			return sprintf($txt['who_sitesearch'], $actions['Itemid']);
		
		//Hmm...attention shoppers in aisle Itemid
		else if ($actions['option']=='com_virtuemart'){
			return sprintf($txt['who_virtuemart'], $actions['Itemid']);
		}
		//How about the Wiki?
		else if (($actions['option']=='com_wikidoc') || ($actions['option']=='com_jd-wiki'))  {
			return sprintf($txt['who_wiki'], $actions['option'], $actions['Itemid']);
		}
		//How about the Games?
		else if ($actions['option']=='com_dcs_flashgames')  {
			return sprintf($txt['who_games'], $actions['option'], $actions['Itemid']);
		}
		//How about the Links?
		else if (($actions['option']=='com_bookmarks') || ($actions['option']=='com_links'))  {
			return sprintf($txt['who_links'], $actions['option'], $actions['Itemid']);
		}
		//How about the Link exchange?
		else if ($actions['option']=='com_linkexchange')  {
			return sprintf($txt['who_linkexchange'], $actions['option'], $actions['Itemid']);
		}
		//How about the Guestbook?
		else if (($actions['option']=='com_easygb') || ($actions['option']=='com_guestbook'))  {
			return sprintf($txt['who_guestbook'], $actions['option'], $actions['Itemid']);
		}
		//How about the Contact page?
		else if (($actions['option']=='com_staff') || ($actions['option']=='com_contact'))  {
			return sprintf($txt['who_contact'], $actions['option'], $actions['Itemid']);
		}
		//How about the gallery?
		else if (($actions['option']=='com_gallery2') || ($actions['option']=='com_zoom') || ($actions['option']=='com_rsgallery2') || ($actions['option']=='com_coppermine'))  {
			return sprintf($txt['who_gallery'], $actions['option'], $actions['Itemid']);
		}
		//How about the Polls?
		else if (($actions['option']=='com_exitpoll') || ($actions['option']=='com_poll'))  {
			return sprintf($txt['who_polls'], $actions['option'], $actions['Itemid']);
		}
		//How about the Credits?
		else if ($actions['option']=='com_jm-credits')  {
			return sprintf($txt['who_credits'], $actions['option'], $actions['Itemid']);
		}
		//How about the Glossary?
		else if ($actions['option']=='com_glossary')  {
			return sprintf($txt['who_glossary'], $actions['option'], $actions['Itemid']);
		}
		//How about the Review Pages?
		else if ($actions['option']=='com_simple_review')  {
			return sprintf($txt['who_review'], $actions['option'], $actions['Itemid']);
		}
		//How about the Classifieds?
		else if (($actions['option']=='com_classifieds') || ($actions['option']=='com_noah'))  {
			return sprintf($txt['who_classifieds'], $actions['option'], $actions['Itemid']);
		}
		//How about the Recipes?
		else if (($actions['option']=='com_ricettario') || ($actions['option']=='com_pccookbook'))  {
			return sprintf($txt['who_recipes'], $actions['option'], $actions['Itemid']);
		}
		//How about the Writing Section?
		else if ($actions['option']=='com_ewriting')  {
			return sprintf($txt['who_writing'], $actions['option'], $actions['Itemid']);
		}
		else {
			return sprintf($txt['who_other'], $actions['option'], $actions['Itemid']);
		}
	}	
}

?>