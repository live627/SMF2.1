<?php
/**********************************************************************************
* SMF_header_include.php                                                          *
***********************************************************************************
* SMF: Simple Machines Forum                                                      *
* Open-Source Project Inspired by Zef Hemel (zef@zefhemel.com)                    *
* =============================================================================== *
* Software Version:           SMF 1.1 RC2                                         *
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
defined( '_VALID_MOS' ) or die( 'Restricted access' );

global $db_name;

$_MAMBOTS->registerFunction( 'onAfterStart', 'SMF_header_include' );

function SMF_header_include( ) {

	global $mainframe, $database, $scripturl, $db_connection, $db_passwd, $maintenance, $db_server, $options;
	global $db_name, $db_user, $db_prefix, $db_persist, $db_error_send, $db_last_error, $sc, $context;
	global $settings, $mosConfig_db, $sourcedir, $mosConfig_live_site, $mosConfig_sef, $mosConfig_dbprefix;
    
	//Gallery2 bridge+SEF compatibility (Damage control)
	if (isset($_REQUEST['option']) && $_REQUEST['option'] == 'com_gallery2' && $mosConfig_sef == 1)
		$_SERVER['QUERY_STRING'] = strtr($_SERVER['QUERY_STRING'],array('?'=>'','&amp;'=>'/','&'=>'/','='=>','));
		
	if (basename($_SERVER['PHP_SELF'])=='index2.php')
		return;
		
	if (!defined('SMF') && $_REQUEST['option'] != 'com_smf'){
		// Get the configuration. This will tell Mambo where SMF is, and some integration settings
		$database->setQuery("
			SELECT `variable`, `value1`
			FROM #__smf_config
			");
		$variables = $database->loadAssocList();

		foreach ($variables as $variable){
			$variable_name = $variable['variable'];
			$$variable_name = $variable['value1'];
		}

		$result = mysql_query("
			SELECT id 
			FROM {$mosConfig_dbprefix}menu 
			WHERE link = 'index.php?option=com_smf'");

		if ($result !== false)
			list($menu_item['id']) = mysql_fetch_row($result);
		else
			$menu_item['id'] = 1;	

		$myurl = basename($_SERVER['PHP_SELF']) . '?option=com_smf&amp;Itemid=' . $menu_item['id'] . '&amp;';
			
		require_once ($smf_path."/SSI.php");
		
		$mainframe->addCustomHeadTag( '<script language="JavaScript" type="text/javascript" src="'. $settings['default_theme_url']. '/script.js?rc2"></script>' );
		$mainframe->addCustomHeadTag( '<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
			var smf_theme_url = "'. $settings['theme_url']. '";
			var smf_images_url = "'. $settings['images_url']. '";
			var smf_scripturl = "'. ( $mosConfig_sef == 1 ? sefReltoAbs($myurl) : $mosConfig_live_site . '/'. $myurl ) . '";
			var smf_session_id = "'. $context['session_id'] . '";
			// ]]></script>' );
		$mainframe->addCustomHeadTag( '<link rel="stylesheet" type="text/css" href="'. $settings['theme_url']. '/style.css?rc2" />' );
		$mainframe->addCustomHeadTag( '<link rel="stylesheet" type="text/css" href="'. $settings['default_theme_url']. '/print.css?rc2" media="print" />' );
		$mainframe->addCustomHeadTag( '<link rel="help" href="'. ( $mosConfig_sef == 1 ? sefReltoAbs($myurl. 'action=help') : $mosConfig_live_site . '/'. $myurl  . 'action=help' ) .'" target="_blank" />' );
		$mainframe->addCustomHeadTag( '<link rel="search" href="' . ( $mosConfig_sef == 1 ? sefReltoAbs($myurl. 'action=search') : $mosConfig_live_site . '/'. $myurl . 'action=search' ) .'" />' );
		$mainframe->addCustomHeadTag( '<link rel="contents" href="'. ( $mosConfig_sef == 1 ? sefReltoAbs($myurl) : $mosConfig_live_site . '/'. $myurl ) . '" />' );
		// If RSS feeds are enabled, advertise the presence of one. 
		if (!empty($modSettings['xmlnews_enable']))  
			$mainframe->addCustomHeadTag( '<link rel="alternate" type="application/rss+xml" title="'. $context['forum_name']. ' - RSS" href="'. ( $mosConfig_sef == 1 ? sefReltoAbs($myurl. 'type=rss;action=.xml') : $mosConfig_live_site . '/'. $myurl . 'type=rss;action=.xml') . '" />' ); 

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
		$context['html_headers'] = str_replace($scripturl, $mosConfig_live_site . '/'. $myurl, $context['html_headers']);
		
		$mainframe->addCustomHeadTag( $context['html_headers'] . '
			<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[ 
			var current_header = ' . (empty($options['collapse_header']) ? 'false' : 'true') . '; 
 
          function shrinkHeader(mode) 
          {' . ($context['user']['is_guest'] ? ' document.cookie = "upshrink=" + (mode ? 1 : 0);' : ' smf_setThemeOption("collapse_header", mode ? 1 : 0, null, "'. $context['session_id']. '");') . 
			'   document.getElementById("upshrink").src = smf_images_url + (mode ? "/upshrink2.gif" : "/upshrink.gif"); 
 
               document.getElementById("upshrinkHeader").style.display = mode ? "none" : ""; 
               document.getElementById("upshrinkHeader2").style.display = mode ? "none" : ""; 
 
               current_header = mode; 
				} 
				// ]]></script>'); 
 
		// the routine for the info center upshrink 
		$mainframe->addCustomHeadTag(' 
          <script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[ 
               var current_header_ic = '. (empty($options['collapse_header_ic']) ? 'false' : 'true') . '; 
 
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

	}

	$sc = &$context['session_id'];
	$_SESSION['USER_AGENT'] = $_SERVER['HTTP_USER_AGENT'];

	mysql_select_db($mosConfig_db);
	
	return true;
}