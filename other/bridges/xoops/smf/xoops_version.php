<?php
/******************************************************************************
*xoops_version.php (Xoops-SMF bridge)                                                                    *
*******************************************************************************
* SMF: Simple Machines Forum                                                  *
* Open-Source Project Inspired by Zef Hemel (zef@zefhemel.com)                *
* =========================================================================== *
* Software Version:           SMF 2.0 Alpha                                   *
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

$modversion['name'] = 'SMF Module for Xoops';
$modversion['version'] = 1.00;
$modversion['description'] = 'This Module will display SMF wrapped in Xoops';
$modversion['credits'] = 'Simple Machines Forum';
$modversion['author'] = 'Theodore Hildebrandt';
$modversion['help'] = '';
$modversion['license'] = 'SMF LICENSE';
$modversion['official'] = 1;
$modversion['image'] = 'images/smf_logo.png';
$modversion['dirname'] = 'smf';

// Admin things
$modversion['hasAdmin'] = 1;
$modversion['adminindex'] = 'admin/index.php';
$modversion['adminmenu'] = 'admin/menu.php';

// Menu/Sub Menu
$modversion['hasMain'] = 1;//make 0 to not have this appear in main menu
$modversion['sub'][1]['name'] = 'Forum';
$modversion['sub'][1]['url'] = 'index.php';

$modversion['templates'][1]['file'] = 'smf_index.html';
$modversion['templates'][1]['description'] = '';

$modversion['config'][1]['name'] = "smf_path";
$modversion['config'][1]['title'] = "_MI_SMF_PATH";
$modversion['config'][1]['description'] = "_MI_SMF_PATH_DESC";
$modversion['config'][1]['formtype'] = "textbox";
$modversion['config'][1]['valuetype'] = "text";
$modversion['config'][1]['default'] = "";

$modversion['blocks'][1]['file'] = "smf_blocks.php";
$modversion['blocks'][1]['name'] = _MI_SMF_BNAME1;
$modversion['blocks'][1]['description'] = "Shows integrated login";
$modversion['blocks'][1]['show_func'] = "b_smf_login_show";
$modversion['blocks'][1]['template'] = 'smf_block_login.html';

?>