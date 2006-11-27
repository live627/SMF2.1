<?php
/**********************************************************************************
* smf.php                                                                         *
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


/* This file is the core of the iGaming-SMF bridge module admin panel

*/
include "global.php";
require_once( '../sources/admin/smf.class.php' );

$cp->header();

$links = '<a href="smf.php?do=configure">Configure Module</a> ';

do_module_header('SMF Module Manager', $links);
$module = new Module;

switch($_REQUEST['do'])
{
	case 'configure':
		$module->configure();
		break;
		
	case 'insert_config':
		$module->insertConfig();
		break;
}

$cp->footer();

?>