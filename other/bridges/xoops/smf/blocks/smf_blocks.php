<?php
/******************************************************************************
*smf_blocks.php (Xoops-SMF bridge)                                                                    *
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

function b_smf_login_show()
{
    
    global $xoopsUser, $xoopsConfig;
	
    if (!$xoopsUser) {
        $block = array();
        $block['lang_username'] = _MB_SMF_USERNAME;
        $block['unamevalue'] = "";
        if (isset($_COOKIE[$xoopsConfig['usercookie']])) {
            $block['unamevalue'] = $_COOKIE[$xoopsConfig['usercookie']];
        }
        $block['lang_password'] = _MB_SMF_PASSWORD;
        $block['lang_login'] = _LOGIN;
        $block['lang_lostpass'] = _MB_SMF_LPASS;
        $block['lang_registernow'] = _MB_SMF_RNOW;
        //$block['lang_rememberme'] = _MB_SYSTEM_REMEMBERME;
        return $block;
    }
    return false;
}
?>