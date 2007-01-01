<?php
/**********************************************************************************
* admin.smf.php                                                                   *
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



global $database, $mosConfig_db, $mosConfig_absolute_path;
 
if (!defined('_VALID_MOS'))
	die('Direct Access to this location is not allowed.');

$new_bridge_version = '1.1.7';

$database->setQuery("
				SELECT `variable`, `value1`
				FROM #__smf_config
				");
$variables = $database->loadAssocList();
	
foreach ($variables as $variable){
	$variable_name = $variable['variable'];
	$$variable_name = $variable['value1'];
}

//This must be 1.1.6 or older
if (!isset($bridge_version)){
	$database->setQuery("
				INSERT INTO #__smf_config
				 (`variable`, `value1`)
				VALUES ('bridge_version','$new_bridge_version')
				");
	$result['upgrade'] = $database->query();
	echo 'Bridge successfully upgraded to version 1.1.7.';
} else {
	echo 'You already have bridge version 1.1.7, no need to upgrade at this time';
}
	
?>