<?php
/******************************************************************************
* admin.smf_registration.php (Mambo/Joomla Bridge)                                                                     *
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


if (!defined('_VALID_MOS'))
	die('Direct Access to this location is not allowed.');

// get language file
if(file_exists($mosConfig_absolute_path . '/components/com_smf/language/' . $mosConfig_lang . '.php'))
	require($mosConfig_absolute_path . '/components/com_smf/language/' . $mosConfig_lang . '.php');
else
	require($mosConfig_absolute_path. '/components/com_smf/language/english.php');

$smfbLanguage =& new smfbLanguage();
// end language

$agreement_required = mosGetParam($_REQUEST, 'agreement_required');
$im = mosGetParam($_REQUEST, 'im');
$pm_on_reg = mosGetParam ($_REQUEST, 'pm_on_reg');
$use_realname = mosGetParam($_REQUEST, 'use_realname');
$cb_reg = mosGetParam ($_REQUEST, 'cb_reg');

switch ($task) 
{
	case 'config':
		showConfig($option);
	break;

	case 'save':
		saveConfig ($option,$agreement_required, $im, $pm_on_reg, $use_realname, $cb_reg);
	break;
	
	case '';
		showConfig($option);
	break;
}

function showConfig($option)
{
	global $mosConfig_absolute_path, $database, $mosConfig_live_site;
	global $smfbLanguage, $mosConfig_dbprefix, $agreement_required;

	$tabs = new mosTabs(0);
	
	require($mosConfig_absolute_path . '/administrator/components/com_smf_registration/config.smf_registration.php');
	
	echo '
	<div id="overDiv" style="position:absolute; visibility:hidden; z-index:10000;"></div>
	<script language="JavaScript" type="text/javascript" src="', $mosConfig_live_site, '/includes/js/overlib_mini.js"></script>
	<table class="adminheading">
		<tr>
			<th class="config">', $smfbLanguage->SMFB_A_CONF_HEADER, '<span class="componentheading">&nbsp;::&nbsp;
				', $smfbLanguage->SMFB_A_CONF_CONFIG_IS, '
				 ', is_writable($mosConfig_absolute_path . '/administrator/components/com_smf_registration/config.smf_registration.php') ? '<b><font color="green">' . $smfbLanguage->SMFB_A_CONF_WRITEABLE . '</font></b>' : '<b><font color="red">' . $smfbLanguage->SMBF_A_CONF_NOT_WRITEABLE . '</font></b>', '
			</span></th>
		</tr>
	</table>
	
	<script language="javascript" type="text/javascript"><!-- // --><![CDATA[
		function submitbutton(pressbutton) 
		{
			var form = document.adminForm;
			submitform(pressbutton);
			return;
		}
		//-->
	</script>

	<form action="index2.php" method="POST" name="adminForm">';
	$tabs->startPane('configPane');
	$tabs->startTab($smfbLanguage->SMBF_A_CONF_TAB1, 'general-page');
	
	echo '
		<table width="100%" border="0" cellpadding="4" cellspacing="2" class="adminForm">
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
					<input type="checkbox" name="pm_on_reg"', $pm_on_reg == 'on' ? ' checked="checked"' : '' ,' />&nbsp;&nbsp;
				</td>
			</tr>
			<tr>
				<td width="25%" align="left" valign="top">Use real name or username as display?</td>
				<td align="left" valign="top">
					<select name="use_realname">
						<option value="true"', $use_realname == true ? ' selected="selected"' : '', '>Real Name</option>
						<option value="false"', $use_realname == false ? ' selected="selected"' : '', '>User Name</option>
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


		</table>
		', $tabs->endPane(), '
		
		<input type="hidden" name="option" value="', $option, '" />
		<input type="hidden" name="act" value="', $act, '" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
	</form>';
}

function saveConfig ($option,$agreement_required, $im, $pm_on_reg, $use_realname, $cb_reg) 
{
	global $smbfLanguage;
	
	$configfile = 'components/com_smf_registration/config.smf_registration.php';
	@chmod($configfile, 0777);
	$permission = @is_writable($configfile);
	if (!$permission) 
	{
		mosRedirect('index2.php?option=' . $option . '&act=config', $smbfLanguage->SMBF_A_CONF_ERR_CONF_NOT_WRITEABLE);
		return;
	}
	
	$config  = "<?php\n";
	$config  .= "global \$agreement_required, \$im, \$pm_on_reg, \$use_realname, \$cb_reg;\n";
	$config  .= "\$agreement_required = \"$agreement_required\";\n";
	$config  .= "\$im = \"$im\";\n";
	$config  .= "\$pm_on_reg = \"$pm_on_reg\";\n";
	$config  .= "\$use_realname = $use_realname;\n";
	$config  .= "\$cb_reg = \"$cb_reg\";\n";
	$config  .= "?>";
	
	if ($fp = @fopen($configfile, 'w'))
	{
		@fputs($fp, $config, strlen($config));
		@fclose($fp);
	}
	
	mosRedirect('index2.php?option=' . $option . '&task=config', $smbfLanguage->SMBF_A_CONF_SETT_SAVED);
}

?>