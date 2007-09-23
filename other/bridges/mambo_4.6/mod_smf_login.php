<?php
/**********************************************************************************
* mod_smf_login.php                                                               *
***********************************************************************************
* SMF: Simple Machines Forum                                                      *
* Open-Source Project Inspired by Zef Hemel (zef@zefhemel.com)                    *
* =============================================================================== *
* Software Version:           SMF 1.1 RC3                                         *
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

if (!defined('_VALID_MOS'))
	die('Direct Access to this location is not allowed.');

	global $smf_path, $bridge_reg, $maintenance, $sourcedir, $context, $user, $settings;
$configuration =& mamboCore::getMamboCore();
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

//This entire structure only needs to be executed on non-SMF pages.
if (!defined('SMF_INTEGRATION_SETTINGS')){	
	define('SMF_INTEGRATION_SETTINGS', serialize(array(
		'integrate_pre_load' => 'integrate_pre_load',
	)));
	
	function integrate_pre_load () {

	global $lang, $mosConfig_lang, $synch_lang, $smf_lang, $smf_path;

	$language_conversion = array(
							'aa' => 'afar',
							'ab' => 'abkhaz',
							'ae' => 'avestan',
							'af' => 'afrikaans',
							'ak' => 'akan',
							'ar' => 'arabic',
							'am' => 'amharic',
							'an' => 'aragonese',
							'as' => 'assamese',
							'av' => 'avaric',
							'ay' => 'aymara',
							'az' => 'azerbaijani',
							'ba' => 'bashkir',
							'be' => 'belarusian',
							'bg' => 'bulgarian',
							'bh' => 'bihari',
							'bi' => 'bislama',
							'bm' => 'bambara',
							'bn' => 'bangla',
							'br' => 'breton',
							'bs' => 'bosnian',
							'cr' => 'cree',
							'da' => 'danish',
							'de' => 'german',
							'dv' => 'divehi',
							'dz' => 'dzongkha',
							'en' => 'english',
							'fa' => 'farsi',
							'es' => 'spanish',
							'fr' => 'french',
							'gn' => 'guarani',
							'hr' => 'croatian',
							'hu' => 'hungarian',
							'hy' => 'armenian',
							'it' => 'italian',
							'kr' => 'kanuri',
							'ml' => 'malayalam',
							'mo' => 'moldovan',
							'nb' => 'bokm&#229;l',
							'nl' => 'dutch',
							'nn' => 'nynorsk',
							'no' => 'norsk',
							'pl' => 'polish',
							'pt' => 'portuguese',
							'sh' => 'serbo-croatian',
							'sr' => 'serbian',
							'sq' => 'albanian',
							'tg' => 'tajik',
							'th' => 'thai',
							'tr' => 'turkish',
							'iu' => 'inuktitut',
							'za' => 'zhuang',
							'zh' => 'chinese',
							'zu' => 'zulu',
							);
		if($configuration->get('mosConfig_lang') && $synch_lang == 'true'){

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
				$GLOBALS['language'] = $configuration->get('mosConfig_lang');
		$context['forum_name'] = $forum_name;
		loadLanguage ('index', $language);		
		}	

	}
}	

	
if (!defined('SMF'))
{	
	require_once($smf_path . '/SSI.php');	
}

global $context, $txt, $scripturl, $boardurl, $settings, $db_prefix, $db_name, $smf_date;

mysql_select_db($configuration->get('mosConfig_db'));

$result = mysql_query("
	SELECT id 
	FROM " . $configuration->get('mosConfig_dbprefix') . "menu 
	WHERE link = 'index.php?option=com_smf'");

if ($result !== false)
	list($menu_item['id']) = mysql_fetch_row($result);
else
	$menu_item['id'] = 1;

$myurl = 'index.php?option=com_smf&amp;Itemid=' . $menu_item['id'] . '&amp;';

if ($configuration->get('mosConfig_sef') == '1'){
	$scripturl = $myurl;
} else {
	$scripturl = $configuration->get('mosConfig_live_site')  . '/' . $myurl;
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
			$_SESSION['return'] = $configuration->get('mosConfig_sef')=='1' ? sefReltoAbs(basename($_SERVER['PHP_SELF']) . '?' . $_SERVER['QUERY_STRING']) : $configuration->get('mosConfig_live_site')  . '/' . basename($_SERVER['PHP_SELF']) . '?' . $_SERVER['QUERY_STRING'];


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
		', sprintf($txt['welcome_guest'], $txt['guest_title']), '<br />
		', $context['current_time'], '<br />

			<script language="JavaScript" type="text/javascript" src="', $settings['default_theme_url'], '/sha1.js"></script>

			<form action="', sefReltoAbs($scripturl . 'action=login2'), '" method="post" style="margin: 3px 1ex 1px 0;"', empty($context['disable_login_hashing']) ? ' onsubmit="hashLoginPassword(this, \'' . $context['session_id'] . '\');"' : '', '>
				',$txt['username'],': <input type="text" name="user" size="10" /> 
				',$txt['password'],': <input type="password" name="passwrd" size="10" />
				<select name="cookielength">
					<option value="60">', $txt['one_hour'], '</option>
					<option value="1440">', $txt['one_day'], '</option>
					<option value="10080">', $txt['one_week'], '</option>
					<option value="302400">', $txt['one_month'], '</option>
					<option value="-1" selected="selected">', $txt['forever'], '</option>
				</select>
				<input type="submit" value="', $txt['login'], '" /><br />
				<span class="middletext">', $txt['quick_login_dec'], '</span>
				<input type="hidden" name="hash_passwrd" value="" />
				<input type="hidden" name="op2" value="login" />
				<input type="hidden" name="option" value="com_smf" />
				<input type="hidden" name="Itemid" value="', $menu_item['id'], '" />
				<input type="hidden" name="action" value="login2" />
				<input type="hidden" name="returnurl" value="', $params->get('login'), '" />
				<input type="hidden" name="lang" value="', $configuration->get('mosConfig_lang'), '" />
				<input type="hidden" name="return" value="', $configuration->get('mosConfig_sef')=='1' ? sefReltoAbs('index.php?' . $_SERVER['QUERY_STRING']) : $configuration->get('mosConfig_live_site')  . '/index.php?' . $_SERVER['QUERY_STRING'], '" />
				<input type="hidden" name="message" value="', $message_login, '" />

			</form><br />
			<a href="', ($bridge_reg!='SMF' ? sefReltoAbs('index.php?option=com_smf_registration&amp;task=lostPassword') : sefReltoAbs($scripturl . 'action=reminder')) , '">',$txt[315],'</a>';
	}
	if ($params->get('login') == '2' && (!isset($_REQUEST['action']) || $_REQUEST['action'] != 'login') && (!isset($_REQUEST['option']) || $_REQUEST['option'] != 'com_smf_registration'))
		$_SESSION['return'] = $configuration->get('mosConfig_sef')=='1' ? sefReltoAbs('index.php?' . $_SERVER['QUERY_STRING']) : $configuration->get('mosConfig_live_site')  . '/index.php?' . $_SERVER['QUERY_STRING'];

	echo '
		</td>
	</tr></table>
</div>';

mysql_select_db($configuration->get('mosConfig_db'));

?>