<?php 
/******************************************************************************
* toolbar.smf.html.php (Mambo/Joomla Bridge)                                                                     *
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


// Ensure this file is being included by a parent file.
if (!defined('_VALID_MOS'))
	die('Direct Access to this location is not allowed.');

class TOOLBAR_smf
{
	/**
	 * Draws the menu to add or edit an item
	 */
	function _EDIT()
	{
		mosMenuBar::startTable();
		mosMenuBar::save();
		mosMenuBar::cancel();
		mosMenuBar::spacer();
		mosMenuBar::endTable();
	}

	function _DEFAULT()
	{
		mosMenuBar::startTable();
		mosMenuBar::save();
		mosMenuBar::cancel();
		mosMenuBar::spacer();
		mosMenuBar::endTable();
	}
}

?>