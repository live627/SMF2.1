<?php
/******************************************************************************
* eblah_to_smf.php                                                            *
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

// !!! Polls.

$convert_data = array(
	'name' => 'E-Blah Platinum 9',
	'version' => 'SMF 2.0 Alpha',
	'flatfile' => true,
	'settings' => array('/Settings.pl'),
	'parameters' => array(
		array(
			'id' => 'db_purge',
			'type' => 'checked',
			'label' => 'Clear current SMF posts and members during conversion.',
		),
	),
);

if (!function_exists('convert_query'))
{
	if (file_exists(dirname(__FILE__) . '/convert.php'))
		header('Location: http://' . (empty($_SERVER['HTTP_HOST']) ? $_SERVER['SERVER_NAME'] . (empty($_SERVER['SERVER_PORT']) || $_SERVER['SERVER_PORT'] == '80' ? '' : ':' . $_SERVER['SERVER_PORT']) : $_SERVER['HTTP_HOST']) . (strtr(dirname($_SERVER['PHP_SELF']), '\\', '/') == '/' ? '' : strtr(dirname($_SERVER['PHP_SELF']), '\\', '/')) . '/convert.php?convert_script=' . basename(__FILE__));
	else
	{
		echo '<html>
	<head>
		<title>Unable to continue!</title>
	</head>
	<body>
		<h1>Sorry, this file can\'t work alone</h1>

		<p>Please download convert.php from <a href="http://www.simplemachines.org/">www.simplemachines.org</a> and use it.  This file should be in the same directory as it.</p>
	</body>
</html>';
	}

	exit;
}

if (empty($preparsing))
{
	// Memory, please!!
	@ini_set('memory_limit', '128M');

	function load_converter_settings()
	{
		global $eblah;

		if (isset($_SESSION['convert_parameters']['db_purge']))
			$_SESSION['purge'] = !empty($_SESSION['convert_parameters']['db_purge']);

		if (!isset($_POST['path_from']) || !file_exists($_POST['path_from'] . '/Settings.pl'))
			return;

		$data = file($_POST['path_from'] . '/Settings.pl');
		foreach ($data as $line)
		{
			$line = trim($line);
			if (empty($line) || substr($line, 0, 1) == '#')
				continue;

			if (preg_match('~\$([^ =]+?)\s*=\s*[q]?([\^"\']?)(.+?)\\2;~', $line, $match) != 0)
				$eblah[$match[1]] = $match[2] == '^' ? addslashes($match[3]) : $match[3];
		}

		$paths = array('root', 'code', 'boards', 'prefs', 'members', 'messages', 'uploaddir');
		foreach ($paths as $path)
		{
			if (isset($eblah[$path]))
				$eblah[$path] = fixRelativePath($eblah[$path], $_POST['path_from']);
		}
	}

	function convertStep1()
	{
		global $to_prefix, $eblah;

		echo 'Converting membergroups...';

		$knownGroups = array();
		$extraGroups = array();
		$newbie = false;

		// Add a temp column for members.
		if ($_REQUEST['start'] == 0)
		{
			convert_query("
				ALTER TABLE {$to_prefix}membergroups
				DROP COLUMN tempMembers,
				DROP COLUMN tempID", true);
			convert_query("
				ALTER TABLE {$to_prefix}membergroups
				ADD COLUMN tempMembers longtext,
				ADD COLUMN tempID int NOT NULL default 0");
		}

		$groups = file($eblah['prefs'] . '/Ranks2.txt');
		$current_group = null;
		for ($i = 0, $n = count($groups); $i < $n; $i++)
		{
			$group = trim($groups[$i]);

			if (preg_match('~^(Administrator|Moderators) => \{$~', $group, $match) != 0)
			{
				$current_group = &$knownGroups[$match[1] == 'Administrator' ? 1 : 3];
				$current_group = array();
			}
			elseif (preg_match('~(\d+) => \{$~', $group, $match) != 0)
			{
				$current_group = &$extraGroups[];
				$current_group['tempID'] = $match[1];
			}
			elseif (isset($current_group) && preg_match('~^(.+?) = [\'(](.+?)[\'\)]$~', $group, $match) != 0)
			{
				if ($match[1] == 'name')
					$current_group['groupName'] = addslashes($match[2]);
				elseif ($match[1] == 'members')
					$current_group['tempMembers'] = addslashes($match[2]);
				elseif ($match[1] == 'pcount')
				{
					$current_group['minPosts'] = max(0, (int) $match[2]);
					if ($match[2] < 1)
						$newbie = true;
				}
				elseif ($match[1] == 'color')
					$current_group['onlineColor'] = addslashes($match[2]);
			}
		}

		unset($knownGroups[3]['tempMembers']);

		if (!empty($_SESSION['purge']))
		{
			convert_query("
				DELETE FROM {$to_prefix}permissions
				WHERE ID_GROUP > " . ($newbie ? 3 : 4));
			convert_query("
				DELETE FROM {$to_prefix}membergroups
				WHERE ID_GROUP > " . ($newbie ? 3 : 4));
		}

		if (!empty($knownGroups))
		{
			foreach ($knownGroups as $i => $v)
				$knownGroups[$i] = "$i, SUBSTRING('$v[groupName]', 1, 80), SUBSTRING('" . @$v['onlineColor'] . "', 1, 20), 0, '', '" . @$v['tempMembers'] . "'";

			convert_query("
				REPLACE INTO {$to_prefix}membergroups
					(ID_GROUP, groupName, onlineColor, minPosts, stars, tempMembers)
				VALUES (" . implode("),
					(", $knownGroups) . ")");
		}

		if (!empty($extraGroups))
		{
			foreach ($extraGroups as $i => $v)
				$extraGroups[$i] = "$v[tempID], SUBSTRING('$v[groupName]', 1, 80), SUBSTRING('" . @$v['onlineColor'] . "', 1, 20), " . (isset($v['minPosts']) ? $v['minPosts'] : -1) . ", '', '" . @$v['tempMembers'] . "'";

			convert_query("
				REPLACE INTO {$to_prefix}membergroups
					(tempID, groupName, onlineColor, minPosts, stars, tempMembers)
				VALUES (" . implode("),
					(", $extraGroups) . ")");
		}
	}

	function convertStep2()
	{
		global $to_prefix, $eblah;

		echo 'Converting members...';

		if ($_GET['substep'] == 0 && !empty($_SESSION['purge']))
		{
			convert_query("
				TRUNCATE {$to_prefix}members");
		}
		if ($_GET['substep'] == 0)
		{
			// Get rid of the primary key... we have to resort anyway.
			convert_query("
				ALTER TABLE {$to_prefix}members
				DROP PRIMARY KEY,
				CHANGE COLUMN ID_MEMBER ID_MEMBER mediumint(8) unsigned NOT NULL default 0");
		}

		pastTime(0);

		$request = convert_query("
			SELECT ID_GROUP, tempMembers
			FROM {$to_prefix}membergroups
			WHERE tempMembers != ''");
		$groups = array();
		$addGroups = array();
		while ($row = mysql_fetch_assoc($request))
		{
			$members = explode(',', $row['tempMembers']);
			foreach ($members as $member)
			{
				if (trim($member) == '')
					continue;

				// Additional group?
				if (isset($groups[$member]))
					$addGroups[$member] = empty($addGroups[$member]) ? $row['ID_GROUP'] : $addGroups[$member] . ',' . $row['ID_GROUP'];
				else
					$groups[$member] = $row['ID_GROUP'];
			}
		}
		mysql_free_result($request);

		$file_n = 0;
		$dir = dir($eblah['members']);
		$block = array();
		$text_columns = array(
			'memberName' => 80,
			'lngfile' => 255,
			'realName' => 255,
			'buddy_list' => 255,
			'pm_ignore_list' => 255,
			'messageLabels' => 65534,
			'passwd' => 64,
			'emailAddress' => 255,
			'personalText' => 255,
			'websiteTitle' => 255,
			'websiteUrl' => 255,
			'location' => 255,
			'ICQ' => 255,
			'AIM' => 16,
			'YIM' => 32,
			'MSN' => 255,
			'timeFormat' => 80,
			'signature' => 255,
			'avatar' => 255,
			'usertitle' => 255,
			'memberIP' => 255,
			'secretQuestion' => 255,
			'secretAnswer' => 64,
			'validation_code' => 10,
			'additionalGroups' => 255,
			'smileySet' => 48,
			'passwordSalt' => 5,
		);
		while ($entry = $dir->read())
		{
			if ($_GET['substep'] < 0)
				break;
			if ($file_n++ < $_GET['substep'])
				continue;
			if (strrchr($entry, '.') != '.dat')
				continue;

			$userData = file($eblah['members'] . '/' . $entry);
			foreach ($userData as $i => $v)
				$userData[$i] = rtrim($userData[$i]);
			if (count($userData) < 3)
				continue;

			$name = substr($entry, 0, -4);

			$row = array(
				'memberName' => substr(htmlspecialchars($name), 0, 80),
				'ID_GROUP' => isset($groups[$name]) ? $groups[$name] : 0,
				'additionalGroups' => isset($addGroups[$name]) ? $addGroups[$name] : '',
				'passwd' => empty($eblah['yabbconver']) && strlen($userData[0]) == 32 ? md5($userData[0]) : substr($userData[0], 0, 64),
				'realName' => @$userData[1] == 'Guest' || @$userData[1] == '' ? htmlspecialchars($name) : htmlspecialchars($userData[1]),
				'emailAddress' => htmlspecialchars(@$userData[2]),
				'posts' => (int) @$userData[3],
				'usertitle' => htmlspecialchars(@$userData[4]),
				'personalText' => htmlspecialchars(@$userData[6]),
				'gender' => @$userData[7] <= 2 ? (int) @$userData[7] : 0,
				'ICQ' => htmlspecialchars(@$userData[8]),
				'AIM' => substr(htmlspecialchars(@$userData[9]), 0, 16),
				'MSN' => htmlspecialchars(@$userData[10]),
				'signature' => str_replace(array('&lt;br&gt;'), array('<br />'), htmlspecialchars(@$userData[11], ENT_QUOTES)),
				'hideEmail' => (int) @$userData[12],
				'dateRegistered' => @$userData[14],
				'timeOffset' => (float) @$userData[15],
				'birthdate' => @$userData[16] == '' || strtotime(@$userData[16]) == 0 ? '0001-01-01' : strftime('%Y-%m-%d', strtotime($userData[16])),
				'showOnline' => empty($userData[18]),
				'websiteTitle' => htmlspecialchars(@$userData[19]),
				'websiteUrl' => htmlspecialchars(@$userData[20]),
				'location' => htmlspecialchars(@$userData[21]),
				'notifyAnnouncements' => empty($userData[25]),
				'YIM' => substr(htmlspecialchars(@$userData[27]), 0, 32),
			);

			if ($row['birthdate'] === '0001-01-01' && parse_time(@$userData[16], false) != 0)
				$row['birthdate'] = strftime('%Y-%m-%d', parse_time(@$userData[16], false));

			// Make sure these columns have a value and don't exceed max width.
			foreach ($text_columns as $text_column => $max_size)
				$row[$text_column] = isset($row[$text_column]) ? substr($row[$text_column], 0, $max_size) : '';

			if (file_exists($eblah['members'] . '/' . substr($entry, 0, -4) . '.prefs'))
			{
				$imconfig = file($eblah['members'] . '/' . substr($entry, 0, -4) . '.prefs');
				//$row['pm_ignore_list'] = !empty($imconfig[0]) ? strtr(trim($imconfig[0]), '|', ',') : '';
				$row['pm_email_notify'] = empty($imconfig[1]) || trim($imconfig[1]) == '' ? '0' : '1';
			}
			else
			{
				//$row['pm_ignore_list'] = '';
				$row['pm_email_notify'] = '1';
			}

			$block[] = addslashes_recursive($row);

			if (count($block) > 100)
			{
				doBlock('members', $block);
				pastTime($file_n);
			}
		}
		$dir->close();

		doBlock('members', $block);

		pastTime(-1);

		// Part 2: Now we get to resort the members table!
		if ($_GET['substep'] >= -1)
		{
			convert_query("
				ALTER TABLE {$to_prefix}members
				ORDER BY ID_MEMBER = 0, dateRegistered");
			pastTime(-2);
		}
		if ($_GET['substep'] >= -2)
		{
			convert_query("
				ALTER TABLE {$to_prefix}members
				CHANGE COLUMN ID_MEMBER ID_MEMBER mediumint(8) unsigned NOT NULL auto_increment PRIMARY KEY");

			pastTime(-3);
		}
		if ($_GET['substep'] >= -3)
		{
			convert_query("
				ALTER TABLE {$to_prefix}members
				ORDER BY ID_MEMBER");

			pastTime(-4);
		}
		if ($_GET['substep'] >= -3)
		{
			convert_query("
				ALTER TABLE {$to_prefix}membergroups
				DROP tempMembers");
		}
	}

	function convertStep3()
	{
		global $to_prefix, $eblah;

		echo 'Converting settings...';

		$settings = array();
		$settings['news'] = addslashes(strtr(implode('', file($eblah['prefs'] . '/News.txt')), array("\r" => '')));
		$settings['requireAgreement'] = !empty($eblah['showreg']) ? 1 : 0;
		$settings['registration_method'] = !empty($eblah['creg']) ? 3 : (!empty($eblah['vradmin']) ? ($eblah['vradmin'] == 2 ? 2 : 1) : 0);
		$settings['mail_type'] = empty($eblah['mailuse']) || $eblah['mailuse'] != 2 ? 0 : 1;
		$settings['smtp_host'] = isset($eblah['mailhost']) ? $eblah['mailhost'] : '';
		$settings['smtp_username'] = isset($eblah['mailusername']) ? $eblah['mailusername'] : '';
		$settings['smtp_password'] = isset($eblah['mailpassword']) ? $eblah['mailpassword'] : '';
		$settings['time_offset'] = isset($eblah['gtoff']) ? (int) $eblah['gtoff'] : 0;
		$settings['defaultMaxMembers'] = !empty($eblah['mmpp']) ? (int) $eblah['mmpp'] : 20;
		$settings['defaultMaxTopics'] = !empty($eblah['totalpp']) ? (int) $eblah['totalpp'] : 20;
		$settings['defaultMaxMessages'] = !empty($eblah['maxmess']) ? (int) $eblah['maxmess'] : 15;
		$settings['spamWaitTime'] = (int) $eblah['iptimeout'];
		$settings['avatar_max_width_external'] = (int) $eblah['picwidth'];
		$settings['avatar_max_height_external'] = (int) $eblah['picheight'];
		$settings['avatar_max_width_upload'] = (int) $eblah['picwidth'];
		$settings['avatar_max_height_upload'] = (int) $eblah['picheight'];

		$temp = file_exists($eblah['prefs'] . '/Names.txt') ? file($eblah['prefs'] . '/Names.txt') : array();
		$names = array();
		foreach ($temp as $name)
		{
			if (trim($name) == '')
				continue;

			list ($res_name) = explode('|', $name);
			$names[] = trim($res_name);
		}
		if (!empty($names))
			$settings['reserveNames'] = addslashes(implode("\n", $names));

		$vulgar = array();
		$proper = array();
		$temp = file($eblah['prefs'] . '/Censor.txt');
		foreach ($temp as $word)
		{
			if (trim($word) == '')
				continue;

			list ($word, $word2) = explode('|', $word);
			$vulgar[] = trim($word);
			$proper[] = trim($word2);
		}
		$settings['censor_vulgar'] = addslashes(implode("\n", $vulgar));
		$settings['censor_proper'] = addslashes(implode("\n", $proper));

		$setString = '';
		foreach ($settings as $var => $val)
			$setString .= "
				('$var', SUBSTRING('$val', 1, 65534)),";

		convert_query("
			REPLACE INTO {$to_prefix}settings
				(variable, value)
			VALUES" . substr($setString, 0, -1));
	}

	function convertStep4()
	{
		global $to_prefix, $eblah;

		if ($_GET['substep'] == 0 && !empty($_SESSION['purge']))
		{
			convert_query("
				TRUNCATE {$to_prefix}personal_messages");
			convert_query("
				TRUNCATE {$to_prefix}pm_recipients");
		}
		if ($_GET['substep'] == 0)
		{
			convert_query("
				ALTER TABLE {$to_prefix}personal_messages
				DROP COLUMN temp_toName", true);
			convert_query("
				ALTER TABLE {$to_prefix}personal_messages
				CHANGE COLUMN ID_PM ID_PM int(10) unsigned NOT NULL default 0,
				DROP PRIMARY KEY,
				ADD temp_toName tinytext");
		}

		echo 'Converting personal messages...';

		$names = array();

		$file_n = 0;
		$dir = dir($eblah['members']);
		$block = array();
		while ($entry = $dir->read())
		{
			if ($_GET['substep'] < 0)
				break;
			if ($file_n++ < $_GET['substep'])
				continue;
			if (strrchr($entry, '.') != '.pm')
				continue;

			$userData = file($eblah['members'] . '/' . $entry);
			foreach ($userData as $i => $v)
			{
				$userData[$i] = explode('|', rtrim($userData[$i]));
				if ($userData[$i][0] == 2)
					continue;

				$row = array(
					'msgtime' => $userData[$i][1],
					'subject' => substr($userData[$i][2], 0, 255),
					'fromName' => substr(htmlspecialchars($userData[$i][3]), 0, 255),
					'body' => substr(strtr($userData[$i][4], array('<br>' => '<br />')), 0, 65534),
					'ID_MEMBER_FROM' => '0',
					'deletedBySender' => '1',
					'temp_toName' => htmlspecialchars(substr($entry, 0, -4)),
				);

				$names[strtolower(addslashes($row['fromName']))][] = &$row['ID_MEMBER_FROM'];

				$block[] = addslashes_recursive($row);
			}

			if (count($block) > 100)
			{
				$result = convert_query("
					SELECT ID_MEMBER, memberName
					FROM {$to_prefix}members
					WHERE memberName IN ('" . implode("', '", array_keys($names)) . "')
					LIMIT " . count($names));
				while ($row = mysql_fetch_assoc($result))
				{
					foreach ($names[strtolower(addslashes($row['memberName']))] as $k => $v)
						$names[strtolower(addslashes($row['memberName']))][$k] = $row['ID_MEMBER'];
				}
				mysql_free_result($result);
				$names = array();

				doBlock('personal_messages', $block);
				pastTime($file_n);
			}
		}
		$dir->close();

		if (!empty($block))
		{
			$result = convert_query("
				SELECT ID_MEMBER, memberName
				FROM {$to_prefix}members
				WHERE memberName IN ('" . implode("', '", array_keys($names)) . "')
				LIMIT " . count($names));
			while ($row = mysql_fetch_assoc($result))
			{
				foreach ($names[strtolower(addslashes($row['memberName']))] as $k => $v)
					$names[strtolower(addslashes($row['memberName']))][$k] = $row['ID_MEMBER'];
			}
			mysql_free_result($result);
			$names = array();

			doBlock('personal_messages', $block);
		}

		pastTime(-1);

		// Part 2: Now we get to resort the personal messages table!
		if ($_GET['substep'] >= -1)
		{
			convert_query("
				ALTER TABLE {$to_prefix}personal_messages
				ORDER BY ID_PM = 0, msgtime");

			pastTime(-2);
		}
		if ($_GET['substep'] >= -2)
		{
			convert_query("
				ALTER TABLE {$to_prefix}personal_messages
				CHANGE COLUMN ID_PM ID_PM int(10) unsigned NOT NULL auto_increment PRIMARY KEY");

			pastTime(-3);
		}
		if ($_GET['substep'] >= -3)
		{
			convert_query("
				INSERT INTO {$to_prefix}pm_recipients
					(ID_PM, ID_MEMBER, labels)
				SELECT pm.ID_PM, mem.ID_MEMBER, '' AS labels
				FROM ({$to_prefix}personal_messages AS pm, {$to_prefix}members AS mem)
				WHERE mem.memberName = pm.temp_toName
					AND pm.temp_toName != ''");

			pastTime(-4);
		}
		if ($_GET['substep'] >= -4)
		{
			convert_query("
				ALTER TABLE {$to_prefix}personal_messages
				DROP temp_toName");

			pastTime(-5);
		}
		if ($_GET['substep'] >= -5)
		{
			convert_query("
				ALTER TABLE {$to_prefix}personal_messages
				ORDER BY ID_PM");
		}
	}

	function convertStep5()
	{
		global $to_prefix, $eblah;

		echo 'Converting boards and categories...';

		if ($_GET['substep'] == 0 && !empty($_SESSION['purge']))
		{
			convert_query("
				TRUNCATE {$to_prefix}categories");
			convert_query("
				TRUNCATE {$to_prefix}boards");
			convert_query("
				TRUNCATE {$to_prefix}moderators");
		}
		if ($_GET['substep'] == 0)
		{
			convert_query("
				ALTER TABLE {$to_prefix}categories
				DROP COLUMN tempID", true);
			convert_query("
				ALTER TABLE {$to_prefix}categories
				ADD tempID tinytext");
			convert_query("
				ALTER TABLE {$to_prefix}boards
				DROP COLUMN tempID,
				DROP COLUMN tempCatID", true);
			convert_query("
				ALTER TABLE {$to_prefix}boards
				ADD tempID tinytext,
				ADD tempCatID tinytext");
		}

		pastTime(0);

		$request = convert_query("
			SELECT ID_GROUP, groupName, tempID
			FROM {$to_prefix}membergroups
			WHERE ID_GROUP NOT IN (2, 3)");
		$groups = array('Administrator' => 1, 'member' => 0);
		while ($row = mysql_fetch_assoc($request))
		{
			$groups[$row['groupName']] = $row['ID_GROUP'];
			if ($row['tempID'] != '')
				$groups[$row['tempID']] = $row['ID_GROUP'];
		}
		mysql_free_result($request);

		$cats = file($eblah['boards'] . '/bdscats.db');
		$cat_rows = array();
		$board_cats = array();
		foreach ($cats as $i => $cat)
		{
			$data = explode('|', $cat);
			foreach ($data as $i => $v)
				$data[$i] = rtrim($data[$i]);

			$row = array(
				'name' => substr($data[0], 0, 255),
				'catOrder' => $i + 1,
				'tempID' => trim($data[1]),
			);

			$cat_rows[] = addslashes_recursive($row);

			$boards = explode('/', $data[3]);
			foreach ($boards as $board)
				$board_cats[trim($board)] = trim($data[1]);
		}

		doBlock('categories', $cat_rows);

		$boards = file($eblah['boards'] . '/bdindex.db');
		$moderators = array();
		$board_rows = array();
		foreach ($boards as $i => $board)
		{
			$data = explode('/', rtrim($board));

			$row = array(
				'name' => substr($data[3], 0, 255),
				'description' => substr($data[1], 0, 255),
				'boardOrder' => $i + 1,
				'tempCatID' => isset($board_cats[trim($data[0])]) ? $board_cats[trim($data[0])] : 1,
				'countPosts' => !empty($data[9]),
				'tempID' => $data[0],
				'memberGroups' => '-1,0',
			);

			$board_rows[] = addslashes_recursive($row);

			$mods = explode('|', $data[2]);
			foreach ($mods as $mod)
			{
				if (trim($mod) != '' && $mod[0] != '(')
					$moderators[$data[0]][] = $mod;
			}
		}

		doBlock('boards', $board_rows);

		$result = convert_query("
			SELECT ID_CAT, tempID
			FROM {$to_prefix}categories
			WHERE tempID != ''");
		while ($row = mysql_fetch_assoc($result))
		{
			convert_query("
				UPDATE {$to_prefix}boards
				SET ID_CAT = $row[ID_CAT]
				WHERE tempCatID = '$row[tempID]'");
		}
		mysql_free_result($result);

		foreach ($moderators as $boardid => $names)
		{
			$result = convert_query("
				SELECT ID_BOARD
				FROM {$to_prefix}boards
				WHERE tempID = '$boardid'
				LIMIT 1");
			list ($ID_BOARD) = mysql_fetch_row($result);
			mysql_free_result($result);

			convert_query("
				INSERT INTO {$to_prefix}moderators
					(ID_BOARD, ID_MEMBER)
				SELECT $ID_BOARD, ID_MEMBER
				FROM {$to_prefix}members
				WHERE memberName IN ('" . implode("', '", addslashes_recursive($names)) . "')
				LIMIT " . count($names));
		}

		convert_query("
			ALTER TABLE {$to_prefix}categories
			DROP COLUMN tempID");

		convert_query("
			ALTER TABLE {$to_prefix}boards
			DROP COLUMN tempCatID");

		convert_query("
			ALTER TABLE {$to_prefix}membergroups
			DROP COLUMN tempID");
	}

	function convertStep6()
	{
		global $to_prefix, $eblah;

		if ($_GET['substep'] == 0 && !empty($_SESSION['purge']))
		{
			convert_query("
				TRUNCATE {$to_prefix}log_boards");
			convert_query("
				TRUNCATE {$to_prefix}log_mark_read");
			convert_query("
				TRUNCATE {$to_prefix}log_topics");
		}
		if ($_GET['substep'] == 0)
		{
			convert_query("
				ALTER TABLE {$to_prefix}log_topics
				DROP COLUMN tempID", true);
			convert_query("
				ALTER TABLE {$to_prefix}log_topics
				ADD tempID int(10) unsigned NOT NULL default 0,
				DROP PRIMARY KEY");
		}

		echo 'Converting mark read data...';

		$result = convert_query("
			SELECT ID_BOARD, tempID
			FROM {$to_prefix}boards");
		$boards = array();
		while ($row = mysql_fetch_assoc($result))
			$boards[$row['tempID']] = $row['ID_BOARD'];
		mysql_free_result($result);

		$file_n = 0;
		$dir = dir($eblah['members']);
		$mark_read_block = array();
		$boards_block = array();
		$topics_block = array();
		while ($entry = $dir->read())
		{
			if ($_GET['substep'] < 0)
				break;
			if ($file_n++ < $_GET['substep'])
				continue;
			if (strrchr($entry, '.') != '.log')
				continue;

			$result = convert_query("
				SELECT ID_MEMBER
				FROM {$to_prefix}members
				WHERE memberName = '" . substr($entry, 0, -4) . "'
				LIMIT 1");
			list ($ID_MEMBER) = mysql_fetch_row($result);
			mysql_free_result($result);

			$logData = file($eblah['members'] . '/' . $entry);
			foreach ($logData as $log)
			{
				$parts = array_pad(explode('|', $log), 2, '');
				if (trim($parts[0]) == '')
					continue;

				$row = array();
				$row['logTime'] = (int) trim($parts[1]);
				$row['ID_MEMBER'] = $ID_MEMBER;

				if (is_numeric(trim($parts[0])) && trim($parts[0]) > 10000)
				{
					$row['tempID'] = trim($parts[0]);
					$topics_block[] = $row;
				}
				else
				{
					// !!! This causes duplicates.
					/*if (trim($parts[0]) == 'AllBoards')
					{
						foreach ($boards as $id)
						{
							$row['ID_BOARD'] = $id;
							$mark_read_block[] = $row;
						}
					}
					else*/if (isset($boards[trim($parts[0])]))
					{
						$row['ID_BOARD'] = $boards[trim($parts[0])];
						$boards_block[] = $row;
					}
					elseif (substr(trim($parts[0]), 0, 8) == 'AllRead_' && isset($boards[substr(trim($parts[0]), 8)]))
					{
						$row['ID_BOARD'] = $boards[substr(trim($parts[0]), 8)];
						$mark_read_block[] = $row;
					}
				}
			}

			// Because of the way steps are done, we have to flush all of these at once, or none.
			if (count($mark_read_block) > 250 || count($boards_block) > 250 || count($topics_block) > 250)
			{
				doBlock('log_mark_read', $mark_read_block);
				doBlock('log_boards', $boards_block);
				doBlock('log_topics', $topics_block);

				pastTime($file_n);
			}
		}
		$dir->close();

		doBlock('log_mark_read', $mark_read_block);
		doBlock('log_boards', $boards_block);
		doBlock('log_topics', $topics_block);
	}

	function convertStep7()
	{
		global $to_prefix, $eblah;

		if ($_GET['substep'] == 0 && !empty($_SESSION['purge']))
		{
			convert_query("
				TRUNCATE {$to_prefix}topics");
		}
		if ($_GET['substep'] == 0)
		{
			convert_query("
				ALTER TABLE {$to_prefix}topics
				DROP COLUMN tempID,
				DROP COLUMN tempSubject", true);
			convert_query("
				ALTER TABLE {$to_prefix}topics
				CHANGE COLUMN ID_TOPIC ID_TOPIC mediumint(8) unsigned NOT NULL default 0,
				DROP PRIMARY KEY,
				ADD tempID int(10) unsigned NOT NULL default 0,
				ADD tempSubject tinytext NOT NULL default '',
				DROP INDEX lastMessage,
				DROP INDEX firstMessage,
				DROP INDEX poll");
		}

		echo 'Converting topics (part 1)...';

		pastTime(0);

		$stickies = array();
		if (file_exists($eblah['boards'] . '/Stick.txt'))
		{
			$stickyData = file($eblah['boards'] . '/Stick.txt');

			foreach ($stickyData as $line)
			{
				if (trim($line) != '')
					list (, $stickies[]) = explode('|', trim($line));
			}
		}

		$result = convert_query("
			SELECT ID_BOARD, tempID
			FROM {$to_prefix}boards
			WHERE tempID != ''");
		$boards = array();
		while ($row = mysql_fetch_assoc($result))
			$boards[$row['tempID']] = $row['ID_BOARD'];
		mysql_free_result($result);

		$data_n = 0;
		$block = array();
		foreach ($boards as $boardname => $ID_BOARD)
		{
			if ($_GET['substep'] < 0)
				break;
			if (!file_exists($eblah['boards'] . '/' . $boardname . '.msg'))
				continue;

			$topicListing = file($eblah['boards'] . '/' . $boardname . '.msg');
			foreach ($topicListing as $topicData)
			{
				if ($data_n++ < $_GET['substep'])
					continue;

				$topicInfo = explode('|', rtrim($topicData));
				$tempID = (int) $topicInfo[0];

				if (!file_exists($eblah['messages'] . '/' . $tempID . '.txt'))
					continue;

				$block[] = array(
					'tempID' => $tempID,
					'tempSubject' => addslashes($topicInfo[1]),
					'ID_BOARD' => (int) $ID_BOARD,
					'isSticky' => (int) in_array($tempID, $stickies),
					'locked' => (int) $topicInfo[6],
					'numViews' => (int) @implode('', @file($eblah['messages'] . '/' . $tempID . '.view')),
				);

				if (count($block) > 100)
				{
					doBlock('topics', $block);
					pastTime($data_n);
				}
			}
		}

		doBlock('topics', $block);

		pastTime(-1);

		if ($_GET['substep'] >= -1)
		{
			convert_query("
				UPDATE {$to_prefix}topics
				SET tempID = ID_TOPIC
				WHERE tempID = 0");

			pastTime(-2);
		}
		if ($_GET['substep'] >= -2)
		{
			convert_query("
				ALTER TABLE {$to_prefix}topics
				ORDER BY ID_TOPIC = 0, tempID");

			pastTime(-3);
		}
		if ($_GET['substep'] >= -3)
		{
			convert_query("
				ALTER TABLE {$to_prefix}topics
				CHANGE COLUMN ID_TOPIC ID_TOPIC mediumint(8) unsigned NOT NULL auto_increment PRIMARY KEY");

			pastTime(-4);
		}
		if ($_GET['substep'] >= -4)
		{
			convert_query("
				ALTER TABLE {$to_prefix}topics
				ORDER BY ID_TOPIC");
		}
	}

	function convertStep8()
	{
		global $to_prefix, $eblah;

		if ($_GET['substep'] == 0)
		{
			convert_query("
				ALTER TABLE {$to_prefix}boards
				DROP COLUMN tempID");
		}

		echo 'Converting topics (part 2)...';

		while (true)
		{
			pastTime($_GET['substep']);

			$result = convert_query("
				SELECT ID_TOPIC, tempID
				FROM {$to_prefix}topics
				WHERE tempID != ID_TOPIC
				LIMIT $_GET[substep], 150");
			while ($row = mysql_fetch_assoc($result))
			{
				convert_query("
					UPDATE {$to_prefix}log_topics
					SET ID_TOPIC = $row[ID_TOPIC]
					WHERE tempID = $row[tempID]");
			}

			$_GET['substep'] += 150;
			if (mysql_num_rows($result) < 150)
				break;

			mysql_free_result($result);
		}

		convert_query("
			DELETE FROM {$to_prefix}log_topics
			WHERE ID_TOPIC = 0 OR ID_MEMBER = 0");

		convert_query("
			ALTER IGNORE TABLE {$to_prefix}log_topics
			DROP COLUMN tempID,
			ADD PRIMARY KEY (ID_TOPIC, ID_MEMBER)");
	}

	function convertStep9()
	{
		global $to_prefix, $eblah;

		if ($_GET['substep'] == 0 && !empty($_SESSION['purge']))
		{
			convert_query("
				TRUNCATE {$to_prefix}log_notify");
		}

		echo 'Converting notifications...';

		while (true)
		{
			pastTime($_GET['substep']);

			$result = convert_query("
				SELECT ID_TOPIC, tempID
				FROM {$to_prefix}topics
				WHERE tempID != ID_TOPIC
				LIMIT $_GET[substep], 150");
			while ($row = mysql_fetch_assoc($result))
			{
				if (!file_exists($eblah['messages'] . '/Mail/' . $row['tempID'] . '.mail'))
					continue;

				$list = file($eblah['messages'] . '/Mail/' . $row['tempID'] . '.mail');
				foreach ($list as $k => $v)
					$list[$k] = addslashes(htmlspecialchars(rtrim($v)));

				convert_query("
					INSERT INTO {$to_prefix}log_notify
						(ID_TOPIC, ID_MEMBER)
					SELECT $row[ID_TOPIC], ID_MEMBER
					FROM {$to_prefix}members
					WHERE memberName IN ('" . implode("', '", $list) . "')");
			}

			$_GET['substep'] += 150;
			if (mysql_num_rows($result) < 150)
				break;

			mysql_free_result($result);
		}
	}

	function convertStep10()
	{
		global $to_prefix, $eblah;

		if ($_GET['substep'] == 0 && !empty($_SESSION['purge']))
		{
			convert_query("
				TRUNCATE {$to_prefix}messages");
			convert_query("
				TRUNCATE {$to_prefix}attachments");
		}
		if ($_GET['substep'] == 0)
		{
			// Remove the auto_incrementing so we know we get the right order.
			convert_query("
				ALTER TABLE {$to_prefix}messages
				CHANGE COLUMN ID_MSG ID_MSG int(10) unsigned NOT NULL default 0,
				DROP PRIMARY KEY,
				DROP INDEX topic,
				DROP INDEX ID_BOARD");

			if (isset($eblah['uploaddir']))
			{
				convert_query("
					ALTER TABLE {$to_prefix}messages
					DROP COLUMN temp_filename", true);
				convert_query("
					ALTER TABLE {$to_prefix}messages
					ADD COLUMN temp_filename tinytext NOT NULL default ''");
			}
		}

		echo 'Converting posts (part 1 - this may take some time)...';

		$block = array();
		while (true)
		{
			$result = convert_query("
				SELECT ID_TOPIC, tempID, ID_BOARD, tempSubject
				FROM {$to_prefix}topics
				WHERE tempID != ID_TOPIC
				LIMIT $_GET[substep], 100");
			while ($topic = mysql_fetch_assoc($result))
			{
				$messages = file($eblah['messages'] . '/' . $topic['tempID'] . '.txt');
				if (empty($messages))
				{
					convert_query("
						DELETE FROM {$to_prefix}topics
						WHERE ID_TOPIC = $topic[ID_TOPIC]
						LIMIT 1");

					pastTime($_GET['substep']);
					continue;
				}

				foreach ($messages as $msgn => $message)
				{
					if (trim($message) == '')
						continue;

					$message = array_pad(explode('|', $message), 10, '');
					foreach ($message as $k => $v)
						$message[$k] = rtrim($v);

					$message[9] = explode('/', $message[9]);

					$row = array(
						'ID_TOPIC' => $topic['ID_TOPIC'],
						'ID_BOARD' => $topic['ID_BOARD'],
						'subject' => substr(($msgn == 0 ? '' : 'Re: ') . $topic['tempSubject'], 0, 255),
						'posterName' => substr($message[0], 0, 255),
						'body' => substr(preg_replace('~\[quote author=.+? link=.+?\]~i', '[quote]', strtr($message[1], array('<br>' => '<br />'))), 0, 65534),
						'posterIP' => substr($message[2], 0, 255),
						'posterEmail' => substr(htmlspecialchars($message[3]), 0, 255),
						'posterTime' => $message[4],
						'smileysEnabled' => empty($message[5]),
						'modifiedTime' => $message[9][0],
						'modifiedName' => isset($message[9][1]) ? substr(htmlspecialchars($message[9][1]), 0, 255) : '',
						'icon' => 'xx',
					);

					if (isset($eblah['uploaddir']))
						$row['temp_filename'] = $message[8];


					$block[] = addslashes_recursive($row);

					if (count($block) > 100)
						doBlock('messages', $block);
				}

				doBlock('messages', $block);
				pastTime(++$_GET['substep']);
			}

			if (mysql_num_rows($result) < 100)
				break;

			mysql_free_result($result);
		}

		doBlock('messages', $block);
	}

	function convertStep11()
	{
		global $to_prefix, $eblah;

		if ($_GET['substep'] == 0)
		{
			mysql_query("
				ALTER TABLE {$to_prefix}messages
				ORDER BY posterTime");
		}

		echo 'Converting posts (part 2)...';

		$request = convert_query("
			SELECT @msg := IFNULL(MAX(ID_MSG), 0)
			FROM {$to_prefix}messages");
		mysql_free_result($request);

		while (true)
		{
			pastTime($_GET['substep']);

			mysql_query("
				UPDATE {$to_prefix}messages
				SET ID_MSG = (@msg := @msg + 1)
				WHERE ID_MSG = 0
				LIMIT 150");

			$_GET['substep'] += 150;
			if (mysql_affected_rows() < 150)
				break;
		}

		convert_query("
			ALTER TABLE {$to_prefix}messages
			CHANGE COLUMN ID_MSG ID_MSG int(10) unsigned NOT NULL auto_increment PRIMARY KEY");
	}

	function convertStep12()
	{
		global $to_prefix, $eblah;

		echo 'Converting posts (part 3)...';

		while (true)
		{
			pastTime($_GET['substep']);

			$result = convert_query("
				SELECT m.ID_MSG, mem.ID_MEMBER
				FROM ({$to_prefix}messages AS m, {$to_prefix}members AS mem)
				WHERE m.posterName = mem.memberName
					AND m.ID_MEMBER = 0
				LIMIT 150");
			while ($row = mysql_fetch_assoc($result))
			{
				convert_query("
					UPDATE {$to_prefix}messages
					SET ID_MEMBER = $row[ID_MEMBER]
					WHERE ID_MSG = $row[ID_MSG]
					LIMIT 1");
			}

			$_GET['substep'] += 150;
			if (mysql_num_rows($result) < 150)
				break;

			mysql_free_result($result);
		}
	}

	function convertStep13()
	{
		global $to_prefix, $eblah;

		echo 'Converting attachments (if the mod is installed)...';

		if (!isset($eblah['uploaddir']))
			return;

		$result = convert_query("
			SELECT value
			FROM {$to_prefix}settings
			WHERE variable = 'attachmentUploadDir'
			LIMIT 1");
		list ($attachmentUploadDir) = mysql_fetch_row($result);
		mysql_free_result($result);

		// Danger, Will Robinson!
		if ($eblah['uploaddir'] == $attachmentUploadDir)
			return;

		$result = convert_query("
			SELECT MAX(ID_ATTACH)
			FROM {$to_prefix}attachments");
		list ($ID_ATTACH) = mysql_fetch_row($result);
		mysql_free_result($result);

		$ID_ATTACH++;

		while (true)
		{
			pastTime($_GET['substep']);

			$setString = '';

			$result = convert_query("
				SELECT ID_MSG, temp_filename
				FROM {$to_prefix}messages
				WHERE temp_filename != ''
				LIMIT $_GET[substep], 100");
			while ($row = mysql_fetch_assoc($result))
			{
				$files = explode('/', $row['temp_filename']);
				foreach ($files as $file)
					if (trim($file) != '' && file_exists($eblah['uploaddir'] . '/' . $file))
					{
						$size = filesize($eblah['uploaddir'] . '/' . $file);
						$filename = getAttachmentFilename($file, $ID_ATTACH);

						if (strlen($file) <= 255 && copy($eblah['uploaddir'] . '/' . $file, $attachmentUploadDir . '/' . $filename))
						{
							$setString .= "
								($ID_ATTACH, $size, 0, '" . addslashes($file) . "', $row[ID_MSG]),";

							$ID_ATTACH++;
						}
					}
			}

			if ($setString != '')
				convert_query("
					INSERT INTO {$to_prefix}attachments
						(ID_ATTACH, size, downloads, filename, ID_MSG)
					VALUES" . substr($setString, 0, -1));

			$_GET['substep'] += 100;
			if (mysql_num_rows($result) < 100)
				break;

			mysql_free_result($result);
		}

		convert_query("
			ALTER TABLE {$to_prefix}messages
			DROP temp_filename");
	}

	function convertStep14()
	{
		global $to_prefix, $eblah;

		echo 'Cleaning up (part 1)...';

		if ($_GET['substep'] <= 0)
		{
			convert_query("
				ALTER TABLE {$to_prefix}topics
				DROP COLUMN tempID");

			pastTime(1);
		}
		if ($_GET['substep'] <= 1)
		{
			convert_query("
				ALTER TABLE {$to_prefix}messages
				ADD UNIQUE INDEX topic (ID_TOPIC, ID_MSG)");

			pastTime(2);
		}
		if ($_GET['substep'] <= 2)
		{
			convert_query("
				ALTER TABLE {$to_prefix}topics
				ADD UNIQUE INDEX poll (ID_POLL, ID_TOPIC)");

			pastTime(3);
		}
		if ($_GET['substep'] <= 3)
		{
			convert_query("
				ALTER TABLE {$to_prefix}topics
				DROP COLUMN tempSubject");

			pastTime(4);
		}
		if ($_GET['substep'] <= 4)
		{
			convert_query("
				ALTER TABLE {$to_prefix}messages
				ADD UNIQUE INDEX ID_BOARD (ID_BOARD, ID_MSG)");
		}
	}

	function convertStep15()
	{
		global $to_prefix, $eblah;

		echo 'Cleaning up (part 2)...';

		while ($_GET['substep'] >= 0)
		{
			pastTime($_GET['substep']);

			$result = convert_query("
				SELECT t.ID_TOPIC, MIN(m.ID_MSG) AS ID_FIRST_MSG, MAX(m.ID_MSG) AS ID_LAST_MSG
				FROM ({$to_prefix}topics AS t, {$to_prefix}messages AS m)
				WHERE m.ID_TOPIC = t.ID_TOPIC
				GROUP BY t.ID_TOPIC
				LIMIT $_GET[substep], 150");
			while ($row = mysql_fetch_assoc($result))
			{
				$result2 = convert_query("
					SELECT ID_MEMBER
					FROM {$to_prefix}messages
					WHERE ID_MSG = $row[ID_LAST_MSG]
					LIMIT 1");
				list ($row['ID_MEMBER_UPDATED']) = mysql_fetch_row($result2);
				mysql_free_result($result2);

				$result2 = convert_query("
					SELECT ID_MEMBER
					FROM {$to_prefix}messages
					WHERE ID_MSG = $row[ID_FIRST_MSG]
					LIMIT 1");
				list ($row['ID_MEMBER_STARTED']) = mysql_fetch_row($result2);
				mysql_free_result($result2);

				convert_query("
					UPDATE {$to_prefix}topics
					SET ID_FIRST_MSG = '$row[ID_FIRST_MSG]', ID_LAST_MSG = '$row[ID_LAST_MSG]',
						ID_MEMBER_STARTED = '$row[ID_MEMBER_STARTED]', ID_MEMBER_UPDATED = '$row[ID_MEMBER_UPDATED]'
					WHERE ID_TOPIC = $row[ID_TOPIC]
					LIMIT 1");
			}

			$_GET['substep'] += 150;
			if (mysql_num_rows($result) < 150)
				break;

			mysql_free_result($result);
		}

		if ($_GET['substep'] > -1)
		{
			convert_query("
				ALTER TABLE {$to_prefix}topics
				ADD UNIQUE INDEX lastMessage (ID_LAST_MSG, ID_BOARD)");

			pastTime(-2);
		}
		if ($_GET['substep'] > -2)
		{
			convert_query("
				ALTER TABLE {$to_prefix}topics
				ADD UNIQUE INDEX firstMessage (ID_FIRST_MSG, ID_BOARD)");
		}
	}

	function fixRelativePath($path, $cwd_path)
	{
		// Fix the . at the start, clear any duplicate slashes, and fix any trailing slash...
		return addslashes(preg_replace(array('~^\.([/\\\]|$)~', '~[/]+~', '~[\\\]+~', '~[/\\\]$~'), array($cwd_path . '$1', '/', '\\', ''), $path));
	}

	function parse_time($field, $use_now = true)
	{
		$field = trim(str_replace(array(' um ', ' de ', ' en ', ' la ', ' om '), ' at ', $field));

		if ($field == '')
			$field = $use_now ? time() : 0;
		elseif (strtotime($field) != -1)
			$field = strtotime($field);
		elseif (preg_match('~(\d\d)/(\d\d)/(\d\d)(.*?)(\d\d)\:(\d\d)\:(\d\d)~i', $field, $matches) != 0)
			$field = strtotime("$matches[5]:$matches[6]:$matches[7] $matches[1]/$matches[2]/$matches[3]");
		else
			$field = $use_now ? time() : 0;

		return $field;
	}

	function doBlock($table, &$block)
	{
		global $to_prefix;

		if (empty($block))
			return;

		if ($table == 'members')
		{
			$block_names = array();
			foreach ($block as $i => $row)
				$block_names[$row['memberName']] = $i;

			$request = convert_query("
				SELECT memberName
				FROM {$to_prefix}members
				WHERE memberName IN ('" . implode("', '", array_keys($block_names)) . "')
				LIMIT " . count($block_names));
			while ($row = mysql_fetch_assoc($request))
				unset($block[$block_names[$row['memberName']]]);
			mysql_free_result($request);

			if (empty($block))
				return;

			unset($block_names);
		}

		$insert_block = array();
		foreach ($block as $row)
			$insert_block[] = '\'' . implode('\', \'', $row) . '\'';

		convert_query("
			INSERT INTO {$to_prefix}$table
				(" . implode(', ', array_keys($block[0])) . ")
			VALUES (" . implode("),
				(", $insert_block) . ")");

		$block = array();
	}
}

?>