<?php
/******************************************************************************
* cleanup_mark_read.php                                                       *
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

/*	This file is meant to be used as a cron.  However, you can run it manually
	just fine.  It only has one real setting, which is how long it should leave
	unread topics alone before it cleans them up.  It also allows you to choose
	where your forum is, if it's not in the same place as this script.

	It is recommended you only call this once a day, at a time not many members
	are on.  Note that it will not clean up all rows at once, in the hopes of
	not causing any load problems for your server.
*/

// The location of Settings.php, minus the trailing /.  Example: '/home/user/public_html/community'
$path_to_settings = dirname(__FILE__);

// How long topics can go unread, in days.  Default is 60.
$max_unread_days = 60;

@set_time_limit(300);
error_reporting(E_ALL);

if (substr($path_to_settings, -1) == '/')
	$path_to_settings = substr($path_to_settings, 0, -1);
if (!file_exists($path_to_settings . '/SSI.php'))
	$path_to_settings = dirname(__FILE__);

require_once($path_to_settings . '/SSI.php');

$time_threshold = time() - $max_unread_days * 24 * 3600;

// First thing's first - get the boards.
$request = db_query("
	SELECT ID_BOARD
	FROM {$db_prefix}boards", __FILE__, __LINE__);
$boards = array();
while ($row = mysql_fetch_assoc($request))
	$boards[$row['ID_BOARD']] = $row['ID_BOARD'];
mysql_free_result($request);

$request = db_query("
	SELECT DISTINCT ID_MEMBER
	FROM {$db_prefix}log_topics
	WHERE ID_TOPIC > 0
		AND logTime < $time_threshold
	LIMIT 400", __FILE__, __LINE__);
// Note that this will only do 400 members at a time.
$members = array();
$setString = '';
while ($row = mysql_fetch_assoc($request))
{
	$members[] = $row['ID_MEMBER'];
	$this_boards = $boards;

	// Don't reset boards that are newer!
	$request2 = db_query("
		SELECT ID_BOARD, logTime
		FROM {$db_prefix}log_boards
		WHERE ID_BOARD > 0
			AND ID_MEMBER = $row[ID_MEMBER]", __FILE__, __LINE__);
	while ($row2 = mysql_fetch_assoc($request2))
	{
		if ($row2['logTime'] >= $time_threshold)
			unset($this_boards[$row2['ID_BOARD']]);
	}
	mysql_free_result($request2);

	foreach ($this_boards as $board)
		$setString .= "
			($time_threshold, $row[ID_MEMBER], $board),";
}
mysql_free_result($request);

if ($setString != '')
	db_query("
		REPLACE INTO {$db_prefix}log_mark_read
			(logTime, ID_MEMBER, ID_BOARD)
		VALUES" . substr($setString, 0, -1), __FILE__, __LINE__);

if (!empty($members))
	db_query("
		DELETE FROM {$db_prefix}log_topics
		WHERE ID_TOPIC > 0
			AND ID_MEMBER IN (" . implode(', ', $members) . ")
			AND logTime < $time_threshold", __FILE__, __LINE__);

?>