<?php
/******************************************************************************
* admin.smf.php   (Mambo/Joomla Bridge)                                                               *
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

global $database, $db_name, $db_prefix, $mosConfig_db, $mosConfig_absolute_path, $mosConfig_lang;
 
if (!defined('_VALID_MOS'))
	die('Direct Access to this location is not allowed.');

if (!defined("_MOS_ALLOWHTML"))
	define("_MOS_ALLOWHTML", 0x0002);

// get language file
if (file_exists($mosConfig_absolute_path . '/components/com_smf/language/' . $mosConfig_lang . '.php'))
	require($mosConfig_absolute_path . '/components/com_smf/language/' . $mosConfig_lang . '.php');
else
	require($mosConfig_absolute_path. '/components/com_smf/language/english.php');


$smfbLanguage =& new smfbLanguage();

$variables = array_keys($_REQUEST);
foreach ($variables as $input){
	$$input = mosGetParam ($_REQUEST, $input);
}

if (!isset($pmOnReg) || $pmOnReg != 'on')
	$pmOnReg = 'off';

if (!isset($cb_reg) || $cb_reg != 'on')
	$cb_reg = 'off';

	
switch ($task) 
{
	case "config":
		showConfig($option, $cb_reg);
	break;

	case "save":
	    	$database->setQuery("
				SELECT `value1`
				FROM #__smf_config
				WHERE `variable`='smf_path'
				");
			//Have to be careful not to overwrite $smf_path
			$smfpath = $database->loadResult();
	
			if (! @include_once ($smfpath . "/Settings.php")){
				$saved = false;
			} else { $saved = true; }
			//if we can't get the SMF groups, we can't write the membergroup sync
			if ($saved == true){
				mysql_select_db($db_name);
				$database->setQuery("SELECT ID_GROUP, groupName
						FROM {$db_prefix}membergroups
						");
				$smf_groups = $database->loadAssocList();
	
				mysql_select_db($mosConfig_db);
	
				$sync_group = array();
	
				foreach ($smf_groups as $smf_group=>$group_info){
					$sync_group_name = strtr($group_info['groupName'], array(' ' => '_', '.'=>'_', '\''=>''));
					$sync_group_value = $$sync_group_name;
					$sync_group[$group_info['ID_GROUP']] = $sync_group_value;
				}
			}

			saveConfig ($option, $smf_path, $wrapped, $smf_css, $synch_lang, $bridge_reg, $agreement_required, $im, $pmOnReg, $use_realname, $cb_reg, $sync_group);
	break;
		
	case "mos2smf":
		mos2smf($option);
	break;
	
	case "smf2mos":
		smf2mos($option);
	break;

	case "synch_groups":
		synch_groups($option);
	break;

	case 'cancel':
	default:
		mosRedirect( 'index2.php' );	
}

function showConfig($option, $cb_reg)
{
	global $mosConfig_absolute_path, $database, $mosConfig_live_site;
	global $smfbLanguage, $mosConfig_dbprefix, $smf_path, $bridge_reg;
	global $wrapped, $db_name, $db_prefix, $mosConfig_db;

	$tabs = new mosTabs(0);

	$database->setQuery("
				SELECT `variable`, `value1`
				FROM #__smf_config
				");
	$variables = $database->loadAssocList();
	
	foreach ($variables as $variable){
		$variable_name = $variable['variable'];
		$$variable_name = $variable['value1'];
	}

	$database->setQuery("
				SELECT `group_id`, `name`
				FROM #__core_acl_aro_groups
				");
	$mambo_groups = $database->loadAssocList();
	$mambo_groups[] = array('group_id'=>'0', 'name'=>'Guest');
	
	$database->setQuery("
				SELECT `value1`, `value2`
				FROM #__smf_config
				WHERE variable = 'sync_group'
				");
	$sync_groups = $database->loadAssocList();	
	
	if (! @include_once ($smf_path . "/Settings.php")){
		$not_saved = true;
	}
	mysql_select_db($db_name);
	$database->setQuery("SELECT ID_GROUP, groupName
				FROM {$db_prefix}membergroups
				");
	$smf_groups = $database->loadAssocList();
	
	mysql_select_db($mosConfig_db);
	
	echo '
	<div id="overDiv" style="position:absolute; visibility:hidden; z-index:10000;"></div>
	<script language="JavaScript" type="text/javascript" src="', $mosConfig_live_site, '/includes/js/overlib_mini.js"></script>
	
	<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
		function submitbutton(pressbutton)
		{
			var form = document.adminForm;
			submitform(pressbutton);
			return;
		}
	// ]]></script>

	<form action="index2.php" method="POST" name="adminForm">';
	
	$tabs->startPane('configPane');
	$tabs->startTab($smfbLanguage->SMBF_A_CONF_TAB1, 'general-page');

	echo '
		<table width="100%" border="0" cellpadding="4" cellspacing="2" class="adminForm">
			<tr>
				<td width="25%" align="left" valign="top">', $smfbLanguage->SMBF_A_CONF_PATH, ':</td>
				<td align="left" valign="top">';
	if (!file_exists($smf_path))
	{
		// Let's find SMF!
		$mambo_path = dirname(dirname(dirname(dirname(__FILE__))));

		$paths_to_try = array (
			'/forum',
			'/community',
			'/board',
			'/smf',
			'/yabbse',
			'/../forum',
			'/../community',
			'/../board',
			'/../smf', 
			'/../yabbse',
			'/components/com_smf',
			'/../../forum',
			'/../../community',
			'/../../board',
			'/../../smf', 
			'/../../yabbse',
		);

		foreach ($paths_to_try as $possible)
		{
			if (file_exists($mambo_path . $possible . '/Sources/Load.php'))
			{
				$smfpath = realpath($mambo_path . $possible);
				break;
			}
		}
	}
	echo  '
					<input type="text" name="smf_path" value="', isset($smf_path) ? $smf_path : $smfpath, '" size="60" />
					&nbsp;&nbsp;
					', mosToolTip($smfbLanguage->SMBF_A_CONF_PATH_TT . '</span>', $smfbLanguage->SMBF_A_CONF_PATH_TT_HEADER), '
					&nbsp;&nbsp;
					<input type="button" value="', $smfbLanguage->SMBF_A_CONF_PATH_BUTTON, '" name="Reset" onclick="document.adminForm.smf_path.value=\'', $smfpath, '\';">
				</td>
			</tr>
			<tr>
				<td align="left" valign="top">', $smfbLanguage->SMBF_A_CONF_WRAPPED_TITLE, ':</td>
				<td align="left" valign="top">
					<select name="wrapped">
						<option value="true"', $wrapped == 'true' ? ' selected="selected"' : '', '>', $smfbLanguage->SMBF_A_CONF_WRAPPED, '</option>
						<option value="false"', $wrapped == 'false' ? ' selected="selected"' : '', '>', $smfbLanguage->SMBF_A_CONF_UNWRAPPED, '</option>
					</select>
					&nbsp;&nbsp;
					', mosToolTip($smfbLanguage->SMBF_A_CONF_WRAPPED_TITLE_TT . '</span>', $smfbLanguage->SMBF_A_CONF_WRAPPED_TITLE), '
				</td>
			</tr>
			<tr>
				<td align="left" valign="top">Use SMF CSS in other pages?:</td>
				<td align="left" valign="top">
					<select name="smf_css">
						<option value="true"', $smf_css == 'true' ? ' selected="selected"' : '', '>Yes</option>
						<option value="false"', $smf_css == 'false' ? ' selected="selected"' : '', '>No</option>
					</select>
				</td>
			</tr>			
			<tr>
				<td align="left" valign="top">Synchronize Language from Mambo/Joomla to SMF?:</td>
				<td align="left" valign="top">
					<select name="synch_lang">
						<option value="true"', $synch_lang == 'true' ? ' selected="selected"' : '', '>Yes</option>
						<option value="false"', $synch_lang == 'false' ? ' selected="selected"' : '', '>No</option>
					</select>
				</td>
			</tr>
		</table>
		', 		
		$tabs->endTab(),
		$tabs->startTab("Registration","Registration-page"),
		'
		<table width="100%" border="0" cellpadding="4" cellspacing="2" class="adminForm">
			<tr>
				<td align="left" valign="top">', $smfbLanguage->SMBF_A_CONF_WRAPPED_TITLE, ':</td>
				<td align="left" valign="top">
					<select name="bridge_reg">
						<option value="bridge"', $bridge_reg == 'bridge' ? ' selected="selected"' : '', '>Use Bridge Registration</option>
						<option value="SMF"', $bridge_reg == 'SMF' ? ' selected="selected"' : '', '>Use SMF Registration</option>
						<option value="default"', $bridge_reg == 'default' ? ' selected="selected"' : '', '>Use Mambo/Joomla Registration</option>
						<option value="CB"', $bridge_reg == 'CB' ? ' selected="selected"' : '', '>Use Community Builder Registration</option>
						<option value="jw"', $bridge_reg == 'jw' ? ' selected="selected"' : '', '>Use MamboCharge Registration</option>
					</select>
					&nbsp;&nbsp;
				</td>
			</tr>
						<tr>
				<td width="25%" align="left" valign="top">Require Registration Agreement:</td>
				<td align="left" valign="top">
					<input type="checkbox" name="agreement_required"', $agreement_required == 'on' ? ' checked="checked"' : '' , ' />&nbsp;&nbsp;
				</td>
			</tr>
			<tr>
				<td width="25%" align="left" valign="top">Ask for ICQ, AIM, YIM, MSN?</td>
				<td align="left" valign="top">
					<input type="checkbox" name="im"', $im == 'on' ? ' checked="checked"' : '', ' />&nbsp;&nbsp;
				</td>
			</tr>
			<tr>
				<td width="25%" align="left" valign="top">Send a SMF PM to the user on registration?</td>
				<td align="left" valign="top">
					<input type="checkbox" name="pmOnReg"', $pmOnReg == 'on' ? ' checked="checked"' : '' ,' />&nbsp;&nbsp;
				</td>
			</tr>
			<tr>
				<td width="25%" align="left" valign="top">Use real name or username as display?</td>
				<td align="left" valign="top">
					<select name="use_realname">
						<option value="true"', $use_realname == 'true' ? ' selected="selected"' : '', '>Real Name</option>
						<option value="false"', $use_realname == 'false' ? ' selected="selected"' : '', '>User Name</option>
					</select>
					&nbsp;&nbsp;
				</td>
			</tr>
			<tr>
				<td width="25%" align="left" valign="top">Also register into Community Builder? (You must have Community Builder already installed, and be using Bridge Registration)</td>
				<td align="left" valign="top">
					<input type="checkbox" name="cb_reg"', $cb_reg=='on' ? ' checked="checked"' : '' ,' />&nbsp;&nbsp;
				</td>
			</tr>
		</table>',
		$tabs->endTab(),
		$tabs->startTab("Groups","Groups-page");
		if (isset($not_saved) && $not_saved == true){
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="2" class="adminForm">
				<tr>
					<td width="25%" align="left" valign="top">You must first set your path to SMF and save once</td>
				</tr>';
		}
		else {
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="2" class="adminForm">
				<tr>
					<td width="25%" align="left" valign="top">Select which Groups in Mambo correspond to those in SMF</td>
					<td align="left" valign="top"><strong>SMF Group</strong></td>
					<td align="left" valign="top"><strong>Mambo Group</strong></td>
				</tr>
				';
		
			foreach ($smf_groups as $smf_group){
				echo '<tr>
						<td width="25%" align="left" valign="top"></td>
						<td align="left" valign="top">',$smf_group['groupName'],'</td>
						<td align="left" valign="top">
						<select name="', $smf_group['groupName'],'">';
						//Has this group already been sync'd?
						$sync_selected = '';
						foreach ($sync_groups as $sync_group) {							
							if ($sync_group['value1'] == $smf_group['ID_GROUP']){
								$sync_selected = $sync_group['value2'];
							}
						}
						foreach ($mambo_groups as $mambo_group) {							
							echo '
								<option value="',$mambo_group['group_id'],'"', (($mambo_group['name'] == 'Registered' && $sync_selected == '' ) || $mambo_group['group_id'] == $sync_selected ) ? ' selected="selected"' : '', '>', $mambo_group['name'], '</option>';
						}
						echo '</select>
						</td>
					</tr>';
			}
		}
		echo '	
		</table>',
		$tabs->endTab(),
		$tabs->startTab("Synch","Synch-page");
		if (isset($not_saved) && $not_saved == true){
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="2" class="adminForm">
				<tr>
					<td width="25%" align="left" valign="top">You must first set your path to SMF and save once</td>
				</tr>
			</table>';
		}
		else {
			echo '<table width="100%" border="0" cellpadding="4" cellspacing="2" class="adminForm">
				<tr>
					<td colspan="3" align="left">
						The following buttons will perform mass synching.  You do not NEED to click any of them.  Users will be migrated when they login.  These buttons are useful for migrating all users at once.  Please be careful, especially with the Group Synch button.  You can remove all of your Super Admins if you are not absolutely certain of what you are doing.
					</td>
				</tr>
				<tr>
					<td width="25%" align="left" valign="top">
						This button will migrate your Mambo/Joomla users to SMF.  It will not grant usergroup status to them.  All migrated users will be regular users in SMF.
					</td>
					<td width="25%" align="left" valign="top">
						<script language = "Javascript">
							function m2s(){
									document.location.href = "index2.php?option=com_smf&task=mos2smf";
							}
						</script>
						<input type="button" value = "Migrate Mambo/Joomla users to SMF" onclick="m2s()" />
					</td>
				</tr>
				<tr>
					<td width="25%" align="left" valign="top">
						This button will migrate your SMF users to Mambo/Joomla.  It will not grant usergroup status to them.  All migrated users will be regular users in Mambo/Joomla.
					</td>
					<td width="25%" align="left" valign="top">
						<script language = "Javascript">
							function s2m(){
									document.location.href = "index2.php?option=com_smf&task=smf2mos";
							}
						</script>
						<input type="button" value = "Migrate SMF users to Mambo/Joomla" onclick="s2m()" />
					</td>
				</tr>
				<tr>
					<td width="25%" align="left" valign="top">
						This button will synch your Mambo/Joomla groups according to your SMF groups.  It will not change the status of users in SMF.  It will change the status of users in Mambo/Joomla.  If you have just migrated users from Mambo/Joomla to SMF, DO NOT CLICK THIS BUTTON.  That situation will certainly result in admin users in Mambo/Joomla being changed to regular users.
					</td>
					<td width="25%" align="left" valign="top">
						<script language = "Javascript">
							function synch(){
									document.location.href = "index2.php?option=com_smf&task=synch_groups";
							}
						</script>
						<input type="button" value = "Synchronize Mambo/Joomla groups according to saved settings" onclick="synch()" />
					</td>
				</tr>
			</table>';
		}
		$database->setQuery("
				SELECT `value1`
				FROM #__smf_config
				WHERE `variable` = '3rdPartyTab'
				");		
		$tp_tabs = $database->loadAssocList();
		
		if ($tp_tabs!=''){
			foreach ($tp_tabs as $tp_tab){
				@include ($tp_tab['value1']);
			}
		}
		
		echo $tabs->endPane(), '
		
		<input type="hidden" name="option" value="', $option, '" />
		<input type="hidden" name="task" value=" " />
		<input type="hidden" name="act" value="', $act, '" />
		<input type="hidden" name="boxchecked" value="on" />
	</form>';
}

function saveConfig ($option, $smf_path, $wrapped, $smf_css, $synch_lang, $bridge_reg, $agreement_required, $im, $pmOnReg, $use_realname, $cb_reg, $sync_group)
{
	global $smbfLanguage, $database;
	
	$database->setQuery("
			UPDATE #__smf_config
			SET `value1` = '$smf_path'
			WHERE `variable` = 'smf_path'");
	$result[0] = $database->query();
	
	$database->setQuery("
			UPDATE #__smf_config
			SET `value1` = '$wrapped'
			WHERE `variable` = 'wrapped'");
	$result[1] = $database->query();
	
	$database->setQuery("
			UPDATE #__smf_config
			SET `value1` = '$smf_css'
			WHERE `variable` = 'smf_css'");
	$result[2] = $database->query();	
	
	$database->setQuery("
			UPDATE #__smf_config
			SET `value1` = '$synch_lang'
			WHERE `variable` = 'synch_lang'");
	$result[3] = $database->query();
	
	$database->setQuery("
			UPDATE #__smf_config
			SET `value1` = '$bridge_reg'
			WHERE `variable` = 'bridge_reg'");
	$result[4] = $database->query();
	
	$database->setQuery("
			UPDATE #__smf_config
			SET `value1` = '$agreement_required'
			WHERE `variable` = 'agreement_required'");
	$result[5] = $database->query();
	
	$database->setQuery("
			UPDATE #__smf_config
			SET `value1` = '$im'
			WHERE `variable` = 'im'");
	$result[6] = $database->query();
	
	$database->setQuery("
			UPDATE #__smf_config
			SET `value1` = '$pmOnReg'
			WHERE `variable` = 'pmOnReg'");
	$result[7] = $database->query();
	
	$database->setQuery("
			UPDATE #__smf_config
			SET `value1` = '$use_realname'
			WHERE `variable` = 'use_realname'");
	$result[8] = $database->query();
	
	$database->setQuery("
			UPDATE #__smf_config
			SET `value1` = '$cb_reg'
			WHERE `variable` = 'cb_reg'");
	$result[9] = $database->query();
	
	//Now for the group sync...
	foreach($sync_group as $smf_group => $mambo_group) {
		$database->setQuery("
			SELECT `value2`
			FROM #__smf_config
			WHERE (`variable` = 'sync_group' AND `value1`='$smf_group')");
		$result['check'] = $database->loadAssocList();
		if ($result['check']['value2']){
			$database->setQuery("
				UPDATE #__smf_config
				SET `value2` = '$mambo_group'
				WHERE (`variable` = 'sync_group' AND `value1`='$smf_group')");
			$result[$smf_group] = $database->query();
		} else {
			$database->setQuery("
				INSERT INTO #__smf_config
				(`variable`, `value1`, `value2`)
				VALUES ('sync_group', '$smf_group', '$mambo_group')");
			$result[$smf_group] = $database->query();
		}
	}
	
	mosRedirect('index2.php?option=' . $option . '&task=config', $smbfLanguage->SMBF_A_CONF_SETT_SAVED);
}

function mos2smf ($option){

	global $smf_path, $db_name, $db_prefix, $mosConfig_db, $mosConfig_dbprefix, $database, $use_realname;

	$database->setQuery("
				SELECT `variable`, `value1`
				FROM #__smf_config
				");
	$variables = $database->loadAssocList();
	
	foreach ($variables as $variable){
		$variable_name = $variable['variable'];
		$$variable_name = $variable['value1'];
	}

	$database->setQuery("
				SELECT `group_id`, `name`
				FROM #__core_acl_aro_groups
				");
	$mambo_groups = $database->loadAssocList();
	
	$database->setQuery("
				SELECT `value1`, `value2`
				FROM #__smf_config
				WHERE variable = 'sync_group'
				");
	$sync_groups = $database->loadAssocList();

	require_once ($smf_path . "/SSI.php");

	$tabs = new mosTabs(0);

	echo '
	<div id="overDiv" style="position:absolute; visibility:hidden; z-index:10000;"></div>
	<script language="JavaScript" type="text/javascript" src="', $mosConfig_live_site, '/includes/js/overlib_mini.js"></script>
	
	<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
		function submitbutton(pressbutton)
		{
			var form = document.adminForm;
			submitform(pressbutton);
			return;
		}
	// ]]></script>
	';
	
	$tabs->startPane('configPane');
	$tabs->startTab($smfbLanguage->SMBF_A_CONF_TAB1, 'general-page');

	echo '
		<table width="100%" border="0" cellpadding="4" cellspacing="2" class="adminForm">
			<tr>
				<td width="100%" align="left" valign="top">';
				
	//get usernames already existing in Mambo, one by one
	mysql_select_db($mosConfig_db);
	$mos_sql = "SELECT username, password, email, UNIX_TIMESTAMP(registerDate) AS dateRegistered, name
		FROM {$mosConfig_dbprefix}users";

	$mos_result = mysql_query($mos_sql);

	while ($mos_row = mysql_fetch_array($mos_result,MYSQL_NUM)) {
		$mos_user = $mos_row[0];
		// try to find a match in the SMF users
		mysql_select_db($db_name);
		$smf_sql = "SELECT memberName FROM {$db_prefix}members WHERE (memberName ='".$mos_user."')";
		$smf_result = mysql_query ($smf_sql);

		$smf_row = mysql_fetch_array($smf_result);

		// if the username already exists in both, don't do anything
		if ($smf_row[0]!='')
			echo "<font color=red><strong>" . $smf_row[0] . " already exists<br /></strong></font>";
		else {
			// if the username doesn't exist in SMF, create it
			$write_user = "INSERT INTO {$db_prefix}members 
							(memberName, realName, passwd, emailAddress, dateRegistered) 
							VALUES ('$mos_row[0]','".($use_realname=='true' ? $mos_row[4] : $mos_row[0])."','$mos_row[1]','$mos_row[2]', '$mos_row[3]')";
			$write_result = mysql_query ($write_user);
			echo "<font color=green>" . $mos_row[0] . " added to SMF <br /></font>";	  
		}
		mysql_free_result($smf_result);
		mysql_select_db($mosConfig_db);
	}
	mysql_free_result($mos_result);
	
	echo '</td></tr></table>', $tabs->endPane();
}

function smf2mos ($option){

	global $smf_path, $db_name, $db_prefix, $mosConfig_db, $mosConfig_dbprefix, $database;

	$database->setQuery("
				SELECT `variable`, `value1`
				FROM #__smf_config
				");
	$variables = $database->loadAssocList();
	
	foreach ($variables as $variable){
		$variable_name = $variable['variable'];
		$$variable_name = $variable['value1'];
	}

	$database->setQuery("
				SELECT `group_id`, `name`
				FROM #__core_acl_aro_groups
				");
	$mambo_groups = $database->loadAssocList();
	
	$database->setQuery("
				SELECT `value1`, `value2`
				FROM #__smf_config
				WHERE variable = 'sync_group'
				");
	$sync_groups = $database->loadAssocList();

	require_once ($smf_path . "/SSI.php");

	$tabs = new mosTabs(0);

	echo '
	<div id="overDiv" style="position:absolute; visibility:hidden; z-index:10000;"></div>
	<script language="JavaScript" type="text/javascript" src="', $mosConfig_live_site, '/includes/js/overlib_mini.js"></script>
	
	<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
		function submitbutton(pressbutton)
		{
			var form = document.adminForm;
			submitform(pressbutton);
			return;
		}
	// ]]></script>
	';
	
	$tabs->startPane('configPane');
	$tabs->startTab($smfbLanguage->SMBF_A_CONF_TAB1, 'general-page');

	echo '
		<table width="100%" border="0" cellpadding="4" cellspacing="2" class="adminForm">
			<tr>
				<td width="100%" align="left" valign="top">';
				
	//get usernames already existing in SMF, one by one
	mysql_select_db($db_name);
	$smf_sql = "SELECT memberName, realName, passwd, emailAddress FROM {$db_prefix}members";

	$smf_result = mysql_query($smf_sql);
// Redirect users who try to access /forum directly
if (strpos($_SERVER['QUERY_STRING'], 'dlattach') === false)
{
        if(!defined('_VALID_MOS')){ header("Location: /index.php?option=com_smf&Itemid=XX");}
}

	while ($smf_row = mysql_fetch_array($smf_result,MYSQL_NUM)) {
		$smf_user = $smf_row[0];
		// try to find a match in the Mambo/Joomla users
		mysql_select_db($mosConfig_db);
		$mos_sql = "SELECT username FROM {$mosConfig_dbprefix}users WHERE (username ='".$smf_user."')";
		$mos_result = mysql_query ($mos_sql);

		$mos_row = mysql_fetch_array($mos_result);
		$smf_row[1] = addslashes($smf_row[1]);
		// if the username already exists in both, don't do anything
		if ($mos_row[0]!='')
			echo "<font color=red><strong>" . $mos_row[0] . " already exists<br /></strong></font>";
		else {
			// if the username doesn't exist in Mambo/Joomla, create it
			$write_user = "
				INSERT INTO {$mosConfig_dbprefix}users 
					(username, name, password, email) 
				VALUES ('$smf_row[0]','$smf_row[1]','migrated','$smf_row[3]')";
			$write_result = mysql_query ($write_user);
			$mos_find_id = mysql_query("
				SELECT `id` 
				FROM {$mosConfig_dbprefix}users 
				WHERE name = '$smf_row[1]' 
				LIMIT 1");
			list($mos_id) = mysql_fetch_row($mos_find_id);
			$write_user_acl = mysql_query("
				INSERT INTO {$mosConfig_dbprefix}core_acl_aro 
					(aro_id , section_value , value , order_value , name , hidden)
				VALUES ('', 'users', '$mos_id', '0', '$smf_row[1]', '0');");
			$mos_map_sql = mysql_query("
				SELECT aro_id
				FROM {$mosConfig_dbprefix}core_acl_aro
				WHERE name = '".$smf_row[1]."' 
				LIMIT 1");
			list($aro_id) = mysql_fetch_row($mos_map_sql);

			$mos_write = mysql_query ("
				INSERT INTO {$mosConfig_dbprefix}core_acl_groups_aro_map 
					(group_id , section_value , aro_id) 
				VALUES ('18', '', '$aro_id');");
			echo "<font color=green>" . $smf_row[0] . " added to Mambo/Joomla <br /></font>";	  
		}
		mysql_free_result($mos_result);
	}
	mysql_free_result($smf_result);
	
	echo '</td></tr></table>', $tabs->endPane();
}

function synch_groups ($option){

	global $database, $mosConfig_db, $mosConfig_dbprefix, $db_name, $db_prefix;

	$tabs = new mosTabs(0);

	$database->setQuery("
				SELECT `group_id`, `name`
				FROM #__core_acl_aro_groups
				");
	$mambo_groups = $database->loadAssocList();

	$mgroups = array();
	
    foreach ($mambo_groups as $mgroup){
		$mgroups[$mgroup['group_id']] = $mgroup['name'];
	}
	
	echo '
		<div id="overDiv" style="position:absolute; visibility:hidden; z-index:10000;"></div>
		<script language="JavaScript" type="text/javascript" src="', $mosConfig_live_site, '/includes/js/overlib_mini.js"></script>
	';
	
	$tabs->startPane('configPane');
	$tabs->startTab($smfbLanguage->SMBF_A_CONF_TAB1, 'general-page');

	echo '
		<table width="100%" border="0" cellpadding="4" cellspacing="2" class="adminForm">
			<tr>
				<td width="100%" align="left" valign="top">';	

	$database->setQuery("
				SELECT `variable`, `value1`
				FROM #__smf_config
				");
	$variables = $database->loadAssocList();
	
	foreach ($variables as $variable){
		$variable_name = $variable['variable'];
		$$variable_name = $variable['value1'];
	}
	
	require_once ($smf_path . "/SSI.php");	

	$members = array();
	
	mysql_select_db($db_name);
	
	$database->setQuery("
				SELECT `ID_MEMBER`, `realName`, `memberName`, `ID_GROUP`
				FROM {$db_prefix}members
				ORDER BY `ID_MEMBER` ASC
				");
				
	$members = $database->loadRowList();
	
		mysql_select_db($mosConfig_db);
	
	echo '<a href="index2.php?option=com_smf&task=config">Return to Bridge configuration</a><br />';
	
		foreach ($members as $smf_member){
	        
			$smf_group = $smf_member[3];
			// In case they don't have a group in SMF
			if ($smf_group == 0 || $smf_group == '')
				$smf_group = '4';
			//Let's first use the SMF group to determine the Mambo/Joomla group				
			$database->setQuery("SELECT `value2`
					FROM #__smf_config
					WHERE `variable` = 'sync_group' 
					AND `value1` = '$smf_group'");
			$group = $database->loadResult();
			
			//Then write it into Mambo/Joomla
			$name = $smf_member[1]!='' ? $smf_member[1] : $smf_member[2];

			//Some people like to delete their admin status and then complain about it.  Let's make sure they can't overwrite the first admin user.
			$check_admin = mysql_query("SELECT `id`
					FROM {$mosConfig_dbprefix}users
					WHERE (`name` = '$name' OR `username` = '$name')
					");
			list($user_id) = mysql_fetch_row($check_admin);
			
			if ($user_id != '62'){
				$x = mysql_query ("UPDATE {$mosConfig_dbprefix}users
						SET `usertype` = '".$mgroups[$group]."', `gid` = '$group'
						WHERE (`name` = '$name' OR `username` = '$name')
						");
				$mos_map_sql = mysql_query("
						SELECT aro_id
						FROM {$mosConfig_dbprefix}core_acl_aro
						WHERE `value` = '$user_id'
						LIMIT 1");
				list($aro_id) = mysql_fetch_row($mos_map_sql);
				$x = mysql_query ("UPDATE {$mosConfig_dbprefix}core_acl_groups_aro_map
						SET `group_id` = '$group'
						WHERE aro_id = '$aro_id'
						");
				echo "User: ", $name," Group: ", $mgroups[$group], "<br />";
			}
		}
	echo '<a href="index2.php?option=com_smf&task=config">Return to Bridge configuration</a>
	
	</td></tr></table>', $tabs->endPane();
}

?>