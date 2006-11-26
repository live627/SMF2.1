<?php
/******************************************************************************
* DbSearch-mysql.php                                                          *
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
if (!defined('SMF'))
	die('Hacking attempt...');

/*	This file contains database functions specific to search related activity.

	// !!!

*/

// Add the file functions to the $smfFunc array.
function db_search_init()
{
	global $smfFunc;

	if (!isset($smfFunc['db_backup_table']) || $smfFunc['db_backup_table'] != 'db_backup_table')
		$smfFunc += array(
			'db_search_query' => 'smf_db_search_query',
			'db_search_support' => 'smf_db_search_support',
		);
}

// Does this database type support this search type?
function smf_db_search_support($search_type)
{
	$supported_types = array('custom');

	return in_array($search_type, $supported_types);
}

// Returns the correct query for this search type.
function smf_db_search_query($identifier, $db_string, $file, $line, $connection = null)
{
	global $smfFunc;

	// Search queries that need processing are here!
	$nonFatal = array(
		'drop_tmp_log_search_topics', 
		'drop_tmp_log_search_messages',
	);

	$replacements = array(
		'create_tmp_log_search_topics' => array(
			'~mediumint\(\d\)~i' => 'int',
			'~TYPE=HEAP~i' => '',
		),
		'create_tmp_log_search_messages' => array(
			'~mediumint\(\d\)~i' => 'int',
			'~TYPE=HEAP~i' => '',
		),
		'drop_tmp_log_search_topics' => array(
			'~IF\sEXISTS~i' => '',
		),
		'drop_tmp_log_search_messages' => array(
			'~IF\sEXISTS~i' => '',
		),
	);

	if (isset($replacements[$identifier]))
		$db_string = preg_replace(array_keys($replacements[$identifier]), array_values($replacements[$identifier]), $db_string);
	elseif (preg_match('~^\s*INSERT\sIGNORE~i', $db_string) != 0)
	{
		$db_string = preg_replace('~^\s*INSERT\sIGNORE~i', 'INSERT', $db_string);
		// Don't error on multi-insert.
		$file = false;
	}

	// Should we not error on this?
	if (in_array($identifier, $nonFatal))
		$file = false;

	return $smfFunc['db_query']('', $db_string, $file, $line);
}

?>
