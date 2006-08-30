<?php
/******************************************************************************
* mod_smf_login.php                                                                     *
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
if (!defined('_VALID_MOS'))
	die('Direct Access to this location is not allowed.');

	global $smf_path, $bridge_reg, $maintenance, $sourcedir, $context, $user, $mosConfig_live_site, $mosConfig_db, $mosConfig_dbprefix;

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
	require_once($smf_path . '/SSI.php');	
}

global $context, $txt, $scripturl, $boardurl, $settings, $mosConfig_dbprefix, $db_prefix, $db_name, $smf_date, $mosConfig_db, $mosConfig_sef;

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

if ($mosConfig_sef == '1'){
	$scripturl = $myurl;
} else {
	$scripturl = $mosConfig_live_site . '/' . $myurl;
}

$smf_align = $params->get('smf_align');
$smf_personal_welcome = $params->get('smf_personal_welcome');
$smf_notification = $params->get('smf_notification');
$smf_unread = $params->get('smf_unread');
$smf_new_answers = $params->get('smf_new_answers');
$smf_new_pms = $params->get('smf_new_pms');
$smf_loggedin_time = $params->get('smf_loggedin_time');
$smf_notify_logged_in = $params->get('smf_notify_logged_in');
$smf_logout_button = $params->get('smf_logout_button');
$smf_logout_button_image = $params->get('smf_logout_button_image');

mysql_select_db($db_name);
echo '
<div class="module" style="position: relative; margin-right: 5px;">
	<table width="99%" cellpadding="0" cellspacing="5" border="0" align="', $smf_align, '">
		<tr>', empty($context['user']['avatar']) ? '' : '
			<td valign="top" align="' . $smf_align . '">' . $context['user']['avatar']['image'] . '
			</td>
		</tr>
		<tr>', '
			<td width="100%" valign="top" class="smalltext" style="font-family: verdana, arial, sans-serif;" align="', $smf_align, '">';
	
	// If the user is logged in, display stuff like their name, new messages, etc.
	if ($context['user']['is_logged']){
		if ($smf_personal_welcome){
			echo '
				', $txt[247], ' <b>', $context['user']['name'], '</b>,';
        }
	    // If defined in parameters mod_smf a special message for logged in users will displayed.
		if ($smf_personal_welcome && $smf_notify_logged_in)
			echo '<br />';

		if ($smf_notify_logged_in) 
			echo $smf_notify_logged_in;

		// Only tell them about their messages if they can read their messages!
		if($smf_new_pms && $context['allow_pm'])
			echo 
			' ', $txt[152], ' <a href="', sefReltoAbs($scripturl. 'action=pm'), '">', $context['user']['messages'], ' ', $context['user']['messages'] != 1 ? $txt[153] : $txt[471], '</a>';

		// if defined user can read their new messages
		if($smf_unread)
			echo 
			$txt['newmessages4'], ' ', $context['user']['unread_messages'], ' ', $context['user']['unread_messages'] == 1 ? $txt['newmessages0'] : $txt['newmessages1'] . '.';

		// Is the forum in maintenance mode?
		if ($context['in_maintenance'] && $context['user']['is_admin'])
			echo '<br />
			<b>', $txt[616], '</b>';

		// Are there any members waiting for approval?
		if (!empty($context['unapproved_members']))
			echo '<br />
			', $context['unapproved_members'] == 1 ? $txt['approve_thereis'] : $txt['approve_thereare'], ' <a href="', sefReltoAbs($scripturl. 'action=regcenter').'">', $context['unapproved_members'] == 1 ? $txt['approve_member'] : $context['unapproved_members'] . ' ' . $txt['approve_members'], '</a> ', $txt['approve_members_waiting'];


		// Show the total time logged in?
		if($smf_loggedin_time && !empty($context['user']['total_time_logged_in']))
		{
			echo '<br />
			', $txt['totalTimeLogged1'];

			// If days is just zero, don't bother to show it.
			if ($context['user']['total_time_logged_in']['days'] > 0)
				echo $context['user']['total_time_logged_in']['days'] . $txt['totalTimeLogged2'];

			// Same with hours - only show it if it's above zero.
			if ($context['user']['total_time_logged_in']['hours'] > 0)
				echo $context['user']['total_time_logged_in']['hours'] . $txt['totalTimeLogged3'];

			// But, let's always show minutes - Time wasted here: 0 minutes ;).
			echo $context['user']['total_time_logged_in']['minutes'], $txt['totalTimeLogged4'];
		}

		if ($smf_unread)
			echo '<br />
			<a href="', sefReltoAbs($scripturl . 'action=unread'),'">', $txt['unread_since_visit'], '</a>';

		if ($smf_new_answers)
			echo '<br />
			<a href="', sefReltoAbs($scripturl. 'action=unreadreplies'),'">', $txt['show_unread_replies'], '</a>';
		
		if ($smf_date)
			echo '<br />
			' . $context['current_time'];

		if ($params->get('logout')=="2")
			$_SESSION['return'] = $mosConfig_sef=='1' ? sefReltoAbs(basename($_SERVER['PHP_SELF']) . '?' . $_SERVER['QUERY_STRING']) : $mosConfig_live_site . '/' . basename($_SERVER['PHP_SELF']) . '?' . $_SERVER['QUERY_STRING'];


		echo '<br />
			<a href="', sefReltoAbs($scripturl . 'action=logout&amp;returnurl='.$params->get('logout').'&amp;sesc='. $context['session_id']), '">', $smf_logout_button ? '<img src="' . (!empty($smf_logout_button_image) && $smf_logout_button_image!="" ? $smf_logout_button_image : $settings['images_url'] . '/' . $context['user']['language'] . '/logout.gif').'" alt="' . $txt[108] . '" style="margin: 2px 0;" border="0" />' : $txt[108], '</a>';
	}
	// Otherwise they're a guest - so politely ask them to register or login.
	else
	{
		$txt['welcome_guest'] = str_replace($boardurl.'/index.php?', $scripturl , $txt['welcome_guest']);
		$txt['welcome_guest'] = str_replace($scripturl.'?', $scripturl, $txt['welcome_guest']);
		$txt['welcome_guest'] = str_replace($scripturl.'action=login', sefReltoAbs($scripturl.'action=login'), $txt['welcome_guest']);
		
	switch ($bridge_reg){
		case "bridge":
			$txt['welcome_guest'] = str_replace($scripturl.'action=register', sefReltoAbs(basename($_SERVER['PHP_SELF']) . '?option=com_smf_registration&amp;task=register'), $txt['welcome_guest']);
			$txt['welcome_guest'] = str_replace($scripturl.'action=activate', sefReltoAbs(basename($_SERVER['PHP_SELF']) . '?option=com_smf_registration&amp;task=lostCode'), $txt['welcome_guest']);
		break;
		
		case "SMF":
			$txt['welcome_guest'] = str_replace($scripturl.'action=register', sefReltoAbs($scripturl.'action=register'),$txt['welcome_guest']); 
			$txt['welcome_guest'] = str_replace($scripturl.'action=activate', sefReltoAbs($scripturl.'action=activate'),$txt['welcome_guest']); 
		break;

		case "default":
			$txt['welcome_guest'] = str_replace($scripturl.'action=register', sefReltoAbs(basename($_SERVER['PHP_SELF']) . '?option=com_registration&amp;task=register'), $txt['welcome_guest']);
			$txt['welcome_guest'] = str_replace($scripturl.'action=activate', sefReltoAbs(basename($_SERVER['PHP_SELF']) . '?option=com_smf_registration&amp;task=lostCode'), $txt['welcome_guest']);
		break;
		
		case "CB":
			$txt['welcome_guest'] = str_replace($scripturl.'action=register', sefReltoAbs(basename($_SERVER['PHP_SELF']) . '?option=com_comprofiler&amp;task=registers'), $txt['welcome_guest']);
			$txt['welcome_guest'] = str_replace($scripturl.'action=activate', sefReltoAbs(basename($_SERVER['PHP_SELF']) . '?option=com_smf_registration&amp;task=lostCode'), $txt['welcome_guest']);
		break;

		case "jw":
			$txt['welcome_guest'] = str_replace($scripturl.'action=register', sefReltoAbs(basename($_SERVER['PHP_SELF']) . '?option=com_jw_registration&amp;task=register'), $txt['welcome_guest']);
			$txt['welcome_guest'] = str_replace($scripturl.'action=activate', sefReltoAbs(basename($_SERVER['PHP_SELF']) . '?option=com_smf_registration&amp;task=lostCode'), $txt['welcome_guest']);
		break;		
	}
		$txt[34] = str_replace('&?','&', $txt[34]);
		if (!isset($login))
			{$login = '';}
		if (!isset($message_login))
			{$message_login = '';}

		echo '
		', $txt['welcome_guest'], '<br />
		', $context['current_time'], '<br />

			<script language="JavaScript" type="text/javascript" src="', $settings['default_theme_url'], '/sha1.js"></script>

			<form action="', sefReltoAbs($scripturl . 'action=login2'), '" method="post" style="margin: 3px 1ex 1px 0;"', empty($context['disable_login_hashing']) ? ' onsubmit="hashLoginPassword(this, \'' . $context['session_id'] . '\');"' : '', '>
				',$txt[35],': <input type="text" name="user" size="10" /> 
				',$txt[36],': <input type="password" name="passwrd" size="10" />
				<select name="cookielength">
					<option value="60">', $txt['smf53'], '</option>
					<option value="1440">', $txt['smf47'], '</option>
					<option value="10080">', $txt['smf48'], '</option>
					<option value="302400">', $txt['smf49'], '</option>
					<option value="-1" selected="selected">', $txt['smf50'], '</option>
				</select>
				<input type="submit" value="', $txt[34], '" /><br />
				<span class="middletext">', $txt['smf52'], '</span>
				<input type="hidden" name="hash_passwrd" value="" />
				<input type="hidden" name="op2" value="login" />
				<input type="hidden" name="option" value="com_smf" />
				<input type="hidden" name="Itemid" value="', $menu_item['id'], '" />
				<input type="hidden" name="action" value="login2" />
				<input type="hidden" name="returnurl" value="', $params->get('login'), '" />
				<input type="hidden" name="lang" value="', $mosConfig_lang, '" />
				<input type="hidden" name="return" value="', $mosConfig_sef=='1' ? sefReltoAbs(basename($_SERVER['PHP_SELF']) . '?' . $_SERVER['QUERY_STRING']) : $mosConfig_live_site . '/' . basename($_SERVER['PHP_SELF']) . '?' . $_SERVER['QUERY_STRING'], '" />
				<input type="hidden" name="message" value="', $message_login, '" />

			</form><br />
			<a href="', ($bridge_reg!='SMF' ? sefReltoAbs(basename($_SERVER['PHP_SELF']). '?option=com_smf_registration&amp;task=lostPassword') : sefReltoAbs($scripturl . 'action=reminder')) , '">',$txt[315],'</a>';
	}
	if ($params->get('login') == '2' && (!isset($_REQUEST['action']) || $_REQUEST['action'] != 'login') && (!isset($_REQUEST['option']) || $_REQUEST['option'] != 'com_smf_registration'))
		$_SESSION['return'] = $mosConfig_sef=='1' ? sefReltoAbs(basename($_SERVER['PHP_SELF']) . '?' . $_SERVER['QUERY_STRING']) : $mosConfig_live_site . '/' . basename($_SERVER['PHP_SELF']) . '?' . $_SERVER['QUERY_STRING'];

	echo '
		</td>
	</tr></table>
</div>';

mysql_select_db($mosConfig_db);

?>