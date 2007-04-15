<?php
/**********************************************************************************
* admin_smf_config.php                                                                         *
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

require_once("../../class2.php");
require_once(e_HANDLER."userclass_class.php");
if (!getperms("P")) {
	header("location:".e_BASE."index.php");
}

$lan_file = e_PLUGIN."smf/languages/".e_LANGUAGE.".php";
include_once(file_exists($lan_file) ? $lan_file : e_PLUGIN."smf/languages/English.php");

if (isset($_POST['updatesettings'])) {
	$pref['smf_path'] = $_POST['smf_path'];
	$pref['wrapped'] = $_POST['wrapped'];
	save_prefs();
	$message = SMF_LAN_ADMIN_H7; // "Calendar settings updated.";
}

require_once(e_ADMIN."auth.php");

if (isset($message)) {
	$ns->tablerender("", "<div style='text-align:center'><b>$message</b></div>");
}

	$text = "<div style='text-align:center'>
	<form method='post' action='".e_SELF."'>
	<table style='width:97%' class='fborder'>
	<tr><td style='vertical-align:top;' colspan='2' class='fcaption'>".SMF_LAN_ADMIN_H1." </td></tr>
	<tr>
		<td style='width:40%;vertical-align:top;' class='forumheader3'>".SMF_LAN_ADMIN_H2."<br /><span class='smalltext'><em>".SMF_LAN_ADMIN_H3."</em></span></td>
		<td style='width:60%;vertical-align:top;' class='forumheader3'><input class='tbox' type='text' name='smf_path' size='60' value='".$pref['smf_path']."' maxlength='200' />
		</td>
	</tr>
	<tr>
		<td style='width:40%;vertical-align:top;' class='forumheader3'>".SMF_LAN_ADMIN_H4."</td>
		<td style='width:60%;vertical-align:top;' class='forumheader3'>
			<select name='wrapped' class='tbox'>
			<option value='true' ".($pref['wrapped']=='true'?" selected='selected' ":"")." >".SMF_LAN_ADMIN_H5."</option>
			<option value='false' ".($pref['wrapped']=='false'?" selected='selected' ":"")." >".SMF_LAN_ADMIN_H6."</option>
			</select>
		</td>
	</tr>
	<tr><td colspan='2'  style='text-align:left' class='fcaption'><input class='button' type='submit' name='updatesettings' value='".SMF_LAN_ADMIN_H11."' /></td></tr>
	</table>
	</form>
	</div>";
	
	$ns->tablerender("<div style='text-align:center'>".SMF_LAN_ADMIN_H1."</div>", $text);
	
	require_once(e_ADMIN."footer.php");
?>