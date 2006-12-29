<?php
/**********************************************************************************
* plugin.php                                                                         *
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

if (!defined('e107_INIT')) { exit; }

// Plugin info -------------------------------------------------------------------------------------------------------
$lan_file = e_PLUGIN."smf/languages/".e_LANGUAGE.".php";
include_once(file_exists($lan_file) ? $lan_file : e_PLUGIN."smf/languages/English.php");

$eplug_name = "SMF Bridge";
$eplug_version = "1.1 alpha 1";
$eplug_author = "Simple Machines";
$eplug_url = "http://www.simplemachines.org";
$eplug_email = "admin@simplemachines.org";
$eplug_description = "A bridge between e107 and Simple Machines Forum (SMF)";
$eplug_compatible = "e107v0.7+";
$eplug_readme = "";
$eplug_latest = TRUE; //Show reported threads in admin (use e_latest.php)
$eplug_status = TRUE; //Show post count in admin (use e_status.php)

// Name of the plugin's folder -------------------------------------------------------------------------------------
$eplug_folder = "smf";

// Name of menu item for plugin ----------------------------------------------------------------------------------
$eplug_menu_name = "smf_menu";

// Name of the admin configuration file --------------------------------------------------------------------------
$eplug_conffile = "admin_smf_config.php";

// Icon image and caption text ------------------------------------------------------------------------------------
$eplug_icon = $eplug_folder."/images/smf_32.png";
$eplug_icon_small = $eplug_folder."/images/smf_16.png";
$eplug_caption = SMF_LAN_ADMIN_H9;

// List of preferences -----------------------------------------------------------------------------------------------
$eplug_prefs = array(
	"smf_path" => "../forum",
	"wrapped" => "true",
);

// List of comment_type ids used by this plugin. -----------------------------
$eplug_comment_ids = array("smf");


// List of table names -----------------------------------------------------------------------------------------------
$eplug_table_names = array();

// List of sql requests to create tables -----------------------------------------------------------------------------
$eplug_tables = array();

// Create a link in main menu (yes=TRUE, no=FALSE) -------------------------------------------------------------
$eplug_link = TRUE;
$eplug_link_name = "Forum";
$eplug_link_url = e_PLUGIN."smf/smf.php";


// Text to display after plugin successfully installed ------------------------------------------------------------------
$eplug_done = "Successful Installation";
$upgrade_add_prefs = "";
$upgrade_remove_prefs = "";

// upgrading ... //
$upgrade_alter_tables = array();

$eplug_upgrade_done = SMF_LAN_ADMIN_H10.': '.$eplug_version;

?>