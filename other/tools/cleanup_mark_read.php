<?php
/**********************************************************************************
* cleanup_mark_read.php                                                           *
***********************************************************************************
* SMF: Simple Machines Forum                                                      *
* Open-Source Project Inspired by Zef Hemel (zef@zefhemel.com)                    *
* =============================================================================== *
* Software Version:           SMF 2.0 Beta 4                                      *
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
	SELECT id_board
	FROM {$db_prefix}boards", __FILE__, __LINE__);
$boards = array();
while ($row = mysql_fetch_assoc($request))
	$boards[$row['id_board']] = $row['id_board'];
mysql_free_result($request);

$request = db_query("
	SELECT DISTINCT id_member
	FROM {$db_prefix}log_topics
	WHERE id_topic > 0
		AND log_time < $time_threshold
	LIMIT 400", __FILE__, __LINE__);
// Note that this will only do 400 members at a time.
$members = array();
$setString = '';
while ($row = mysql_fetch_assoc($request))
{
	$members[] = $row['id_member'];
	$this_boards = $boards;

	// Don't reset boards that are newer!
	$request2 = db_query("
		SELECT id_board, log_time
		FROM {$db_prefix}log_boards
		WHERE id_board > 0
			AND id_member = $row[id_member]", __FILE__, __LINE__);
	while ($row2 = mysql_fetch_assoc($request2))
	{
		if ($row2['log_time'] >= $time_threshold)
			unset($this_boards[$row2['id_board']]);
	}
	mysql_free_result($request2);

	foreach ($this_boards as $board)
		$setString .= "
			($time_threshold, $row[id_member], $board),";
}
mysql_free_result($request);

if ($setString != '')
	db_query("
		REPLACE INTO {$db_prefix}log_mark_read
			(log_time, id_member, id_board)
		VALUES" . substr($setString, 0, -1), __FILE__, __LINE__);

if (!empty($members))
	db_query("
		DELETE FROM {$db_prefix}log_topics
		WHERE id_topic > 0
			AND id_member IN (" . implode(', ', $members) . ")
			AND log_time < $time_threshold", __FILE__, __LINE__);

?>