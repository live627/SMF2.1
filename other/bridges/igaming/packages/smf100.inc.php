<?php
/**********************************************************************************
* smf100.inc.php                                                                  *
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


class Patch
{
	function main()
	{
		do_table_header( 'Package Details' );
		echo '
			<tr><td class="formlabel">
				Package: SMF Module Package - 1.0.0<br />
				<br />
				<b>Do not run this patch more than once, or you risk corrupting your database.</b><br />
				<br />
				<a href="loadplugin.php?load=../packages/smf100.inc.php&do=install" style="font-weight: bold; background-color: #FFFFFF; border: 1px solid #000000; padding: 6px;">Install Package</a><br />
				<br />
			</td></tr>';
		do_table_footer();
	}

	function install()
	{
		global $db;

		$db->Execute("
			INSERT INTO sp_configuration
			VALUES ('', 'smf_path', '');") or die($db->ErrorMsg());

		$db->Execute("
			INSERT INTO sp_configuration 
			VALUES ('', 'ig_admin_group', '');") or die($db->ErrorMsg());

		SPMessage( 'Package has been installed successfully', 'index2.php' );
	}
}

$patch = new Patch;

switch ($_REQUEST['do'])
{
	case 'install':
		$patch->install();
		break;
	default:
		$patch->main();
		break;
}

?>