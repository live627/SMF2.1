<?php
/**********************************************************************************
* smf.class.php                                                                   *
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


/* This file is the core of the iGaming-SMF bridge module

	 configure()
	 -SMF module configuration form

	 insertConfig ()
	- writes the SMF module configuration to the database
	 
*/

global $db;

class Module
{
	function configure()
	{
		global $db, $spconfig;

		$smf_path = $spconfig['smf_path'];

		$ig_admin_group = $spconfig['ig_admin_group'];
		
		do_form_header('smf.php');
		do_table_header('Configure SMF Module');
		do_text_row('absolute path to SMF','smf_path', $smf_path);
		do_text_row('SMF member group allowed to admin iGaming','ig_admin_group', $ig_admin_group);
		do_submit_row();
		do_table_footer();
		echo '
			<input type="hidden" name="do" value="insert_config" />
		</form>';
	}

	function insertConfig()
	{
		global $db;

		$keys = array(
			'smf_path',
			'ig_admin_group',
		);

		foreach($keys as $key)
		{
			$value = $_REQUEST[$key];
			echo $value;
			$rs = $db->Execute("
				UPDATE sp_configuration
				SET `value` = '$value'
				WHERE `key` = '$key'");
		}
		SPMessage('Success | Configuration Saved Successfully', 'smf.php');
	}
}

?>