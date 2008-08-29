<?php
/**********************************************************************************
* yabb21_to_smf.php                                                               *
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

$convert_data = array(
	'name' => 'YaBB 2.2',
	'version' => 'SMF 1.1',
	'flatfile' => true,
	'settings' => array('/Paths.pl', '/Variables/Paths.pl'),
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
		if (php_sapi_name() == 'cli' && empty($_SERVER['REMOTE_ADDR']))
		{
			print_line('This converter is not a standalone converter. Please download convert.php from http://www.simplemachines.org and use it. This file should be in the same directory as it.');
			exit;
		}

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
		global $yabb;

		if (isset($_SESSION['convert_parameters']['db_purge']))
			$_SESSION['purge'] = !empty($_SESSION['convert_parameters']['db_purge']);

		if (!isset($_POST['path_from']) || (!file_exists($_POST['path_from'] . '/Paths.pl') && !file_exists($_POST['path_from'] . '/Variables/Paths.pl')))
			return;

		if (file_exists($_POST['path_from'] . '/Paths.pl'))
			$data = file($_POST['path_from'] . '/Paths.pl');
		else
			$data = file($_POST['path_from'] . '/Variables/Paths.pl');
		foreach ($data as $line)
		{
			$line = trim($line);
			if (empty($line) || substr($line, 0, 1) == '#')
				continue;

			if (preg_match('~\$([^ =]+?)\s*=\s*[q]?([\^"\']?)(.+?)\\2;~', $line, $match) != 0)
				$yabb[$match[1]] = $match[2] == '^' ? addslashes($match[3]) : $match[3];
		}

		$paths = array('boarddir', 'boardsdir', 'datadir', 'memberdir', 'sourcedir', 'vardir', 'facesdir', 'uploaddir');
		foreach ($paths as $path)
			$yabb[$path] = fixRelativePath($yabb[$path], $_POST['path_from']);

		// In some cases $boarddir is not parsed causing the paths to be incorrect.
		foreach ($paths as $path)
			if (substr($yabb[$path], 0, 9) == '$boarddir')
				$yabb[$path] = str_replace('$boarddir', $yabb['boarddir'], $yabb[$path]);

		$data = file($yabb['vardir'] . '/Settings.pl');
		foreach ($data as $line)
		{
			$line = trim($line);
			if (empty($line) || substr($line, 0, 1) == '#')
				continue;

			if (preg_match('~\$([^ =]+?)\s*=\s*[q]?([\^"\']?)(.+?)\\2;~', $line, $match) != 0)
				$yabb[$match[1]] = $match[2] == '^' ? addslashes($match[3]) : $match[3];
		}
	}

	function convertStep1()
	{
		global $to_prefix, $yabb;

		print_line('Converting membergroups...');

		$knownGroups = array();
		$extraGroups = array();
		$newbie = false;

		$groups = file($yabb['vardir'] . '/membergroups.txt');
		foreach ($groups as $i => $group)
		{
			if (preg_match('~^\$Group\{\'(Administrator|Global Moderator|Moderator)\'\} = [\'|"]([^|]*)\|(\d*)\|([^|]*)\|([^|]*)~', $group, $match) != 0)
			{
				$match = addslashes_recursive($match);
				$ID_GROUP = $match[1] == 'Administrator' ? 1 : ($match[1] == 'Global Moderator' ? 2 : 3);
				$knownGroups[] = "$ID_GROUP, SUBSTRING('$match[2]', 1, 80), SUBSTRING('$match[5]', 1, 20), '-1', SUBSTRING('$match[3]#$match[4]', 1, 255)";
			}
			elseif (preg_match('~\$Post\{\'(\d+)\'\} = [\'|"]([^|]*)\|(\d*)\|([^|]*)\|([^|]*)~', $group, $match) != 0)
			{
				$match = addslashes_recursive($match);
				$extraGroups[] = "SUBSTRING('$match[2]', 1, 80), SUBSTRING('$match[5]', 1, 20), " . max(0, $match[3]) . ", SUBSTRING('$match[3]#$match[4]', 1, 255)";

				if ($match[3] < 1)
					$newbie = true;
			}
			elseif (preg_match('~\$NoPost\[(\d+)\] = [\'|"]([^|]*)\|(\d*)\|([^|]*)\|([^|]*)~', $group, $match) != 0)
			{
				$match = addslashes_recursive($match);
				$extraGroups[] = "SUBSTRING('$match[2]', 1, 80), SUBSTRING('$match[5]', 1, 20), 0, SUBSTRING('$match[3]#$match[4]', 1, 255)";
			}
		}

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
			convert_query("
				REPLACE INTO {$to_prefix}membergroups
					(ID_GROUP, groupName, onlineColor, minPosts, stars)
				VALUES (" . implode("),
					(", $knownGroups) . ")");
		}

		if (!empty($extraGroups))
		{
			convert_query("
				REPLACE INTO {$to_prefix}membergroups
					(groupName, onlineColor, minPosts, stars)
				VALUES (" . implode("),
					(", $extraGroups) . ")");
		}
	}

	function convertStep2()
	{
		global $to_prefix, $yabb;

		// Change the block size as needed
		$block_size = 100;

		print_line('Converting members...');

		if ($_GET['substep'] == 0 && !empty($_SESSION['purge']))
		{
			convert_query("
				TRUNCATE {$to_prefix}members");
		}
		if ($_GET['substep'] == 0)
		{
			// Get rid of the primary key... we have to resort anyway.
			$knownKeys = array(
				'PRIMARY' => 'DROP PRIMARY KEY',
			);
			$alterColumns = array(
				'ID_MEMBER' => 'CHANGE COLUMN ID_MEMBER ID_MEMBER mediumint(8) unsigned NOT NULL default 0',
			);
			alterTable('members', $knownKeys, '', $alterColumns);
		}

		pastTime(0);

		$request = convert_query("
			SELECT ID_GROUP, groupName
			FROM {$to_prefix}membergroups
			WHERE ID_GROUP != 3");
		$groups = array('Administrator' => 1, 'Global Moderator' => 2, 'Moderator' => 0);
		while ($row = mysql_fetch_assoc($request))
			$groups[$row['groupName']] = $row['ID_GROUP'];
		mysql_free_result($request);

		$file_n = 0;
		$dir = dir($yabb['memberdir']);
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
			'memberIP2' => 255,
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
			if (strrchr($entry, '.') != '.vars' && strrchr($entry, '.') != '.dat')
				continue;

			$name = substr($entry, 0, strrpos($entry, '.'));

			$userData = file($yabb['memberdir'] . '/' . $entry);
			if (count($userData) < 3)
				continue;

			$data = array();
			foreach ($userData as $i => $v)
			{
				$userData[$i] = trim($userData[$i]);

				if (strrchr($entry, '.') == '.vars' && preg_match('~^\'([^\']+)\',"([^"]+)"~', $userData[$i], $match) != 0)
					$data[$match[1]] = $match[2];
			}

			// Is it an invalid user?
			if (empty($data))
				continue;

			if (strrchr($entry, '.') != '.vars')
			{
				$userData = array_pad($userData, 31, '');
				$data = array(
					'password' => $userData[0],
					'realname' => $userData[1],
					'email' => $userData[2],
					'webtitle' => $userData[3],
					'weburl' => $userData[4],
					'signature' => $userData[5],
					'postcount' => $userData[6],
					'position' => $userData[7],
					'icq' => $userData[8],
					'aim' => $userData[9],
					'yim' => $userData[10],
					'gender' => $userData[11],
					'usertext' => $userData[12],
					'userpic' => $userData[13],
					'regdate' => $userData[14],
					'location' => $userData[15],
					'bday' => $userData[16],
					'hidemail' => $userData[19],
					'msn' => $userData[20],
					'lastonline' => $userData[23],
					'im_ignorelist' => $userData[26],
					'im_notify' => $userData[27],
					// !!! 'cathide' => $userData[30],
					// !!! 'postlayout' => $userData[31],
				);
			}

			$row = array(
				'memberName' => substr(htmlspecialchars(trim($name)), 0, 80),
				'passwd' => strlen($data['password']) == 22 ? bin2hex(base64_decode($data['password'])) : md5($data['password']),
				'realName' => htmlspecialchars($data['realname']),
				'emailAddress' => htmlspecialchars($data['email']),
				'websiteTitle' => isset($data['website']) ? htmlspecialchars($data['webtitle']) : '',
				'websiteUrl' => isset($data['weburl']) ? htmlspecialchars($data['weburl']) : '',
				'signature' => isset($data['signature']) ? str_replace(array('&amp;&amp;', '&amp;lt;', '&amp;gt;'), array('<br />', '&lt;'. '&gt;'), strtr($data['signature'], array('\'' => '&#039;'))) : '',
				'posts' => (int) $data['postcount'],
				'ID_GROUP' => isset($data['position']) && isset($groups[$data['position']]) ? $groups[$data['position']] : 0,
				'ICQ' => isset($data['icq']) ? htmlspecialchars($data['icq']) : '',
				'AIM' => isset($data['aim']) ? substr(htmlspecialchars($data['aim']), 0, 16) : '',
				'YIM' => isset($data['yim']) ? substr(htmlspecialchars($data['yim']), 0, 32) : '',
				'MSN' => isset($data['msn']) ? htmlspecialchars($data['msn']) : '',
				'gender' => isset($data['gender']) ? ($data['gender'] == 'Male' ? 1 : ($data['gender'] == 'Female' ? 2 : 0)) : '0',
				'personalText' => isset($data['usertext']) ? htmlspecialchars($data['usertext']) : '',
				'avatar' => $data['userpic'],
				'dateRegistered' => (int) parse_time($data['regdate']),
				'location' => isset($data['location']) ? htmlspecialchars($data['location']) : '0',
				'birthdate' => isset($data['bday']) ? ($data['bday'] == '' || strtotime($data['bday']) == 0 ? '0001-01-01' : strftime('%Y-%m-%d', strtotime($data['bday']))) : '0001-01-01',
				'hideEmail' => isset($data['hidemail']) && $data['hidemail'] == 'checked' ? '1' : '0',
				'lastLogin' => isset($data['lastonline']) ? $data['lastonline'] : '0',
				'pm_email_notify' => empty($data['im_notify']) || trim($data['im_notify']) == '' ? '0' : '1',
				'karmaGood' => 0,
				'karmaBad' => 0,
			);

			// Make sure these columns have a value and don't exceed max width.
			foreach ($text_columns as $text_column => $max_size)
				$row[$text_column] = isset($row[$text_column]) ? substr($row[$text_column], 0, $max_size) : '';

			if (!empty($data['bday']) && $row['birthdate'] == '0001-01-01' && parse_time($data['bday'], false) != 0)
				$row['birthdate'] = strftime('%Y-%m-%d', parse_time($data['bday'], false));

			if (file_exists($yabb['memberdir'] . '/' . substr($entry, 0, -4) . '.karma'))
			{
				$karma = (int) implode('', file($yabb['memberdir'] . '/' . substr($entry, 0, -4) . '.karma'));
				$row['karmaGood'] = $karma > 0 ? $karma : 0;
				$row['karmaBad'] = $karma < 0 ? -$karma : 0;
			}

			$block[] = addslashes_recursive($row);

			if (count($block) > $block_size)
			{
				doBlock('members', $block);
				pastTime($file_n);
			}
		}
		$dir->close();

		doBlock('members', $block);

		pastTime(-1);

		// Part 2: Now we get to resort the members table!
		if ($_GET['substep'] <= -1)
		{
			convert_query("
				ALTER TABLE {$to_prefix}members
				ORDER BY ID_MEMBER, dateRegistered");
			pastTime(-2);
		}
		if ($_GET['substep'] <= -2)
		{
			$knownKeys = array(
				'PRIMARY' => 'ADD PRIMARY KEY (ID_MEMBER)',
			);
			$alterColumns = array(
				'ID_MEMBER' => 'CHANGE COLUMN ID_MEMBER ID_MEMBER mediumint(8) unsigned NOT NULL auto_increment',
			);
			alterTable('members', $knownKeys, '', $alterColumns, true);

			pastTime(-3);
		}
		if ($_GET['substep'] <= -3)
		{
			convert_query("
				ALTER TABLE {$to_prefix}members
				ORDER BY ID_MEMBER");
		}
	}

	function convertStep3()
	{
		global $to_prefix, $yabb;

		print_line('Converting settings...');

		$temp = file($yabb['vardir'] . '/reservecfg.txt');
		$settings = array(
			'allow_guestAccess' => isset($yabb['guestaccess']) ? (int) $yabb['guestaccess'] : 0,
			'news' => addslashes(strtr(implode('', file($yabb['vardir'] . '/news.txt')), array("\r" => ''))),
			'cookieTime' => !empty($yabb['Cookie_Length']) && $yabb['Cookie_Length'] > 1 ? (int) $yabb['Cookie_Length'] : 60,
			'requireAgreement' => !empty($yabb['RegAgree']) ? 1 : 0,
			'registration_method' => !empty($yabb['emailpassword']) ? 1 : 0,
			'send_validation_onChange' => !empty($yabb['emailnewpass']) ? 1 : 0,
			'send_welcomeEmail' => !empty($yabb['emailwelcome']) ? 1 : 0,
			'mail_type' => empty($yabb['mailtype']) ? 0 : 1,
			'smtp_host' => isset($yabb['smtp_server']) ? $yabb['smtp_server'] : '',
			'smtp_username' => !empty($yabb['smtp_auth_required']) && isset($yabb['authuser']) ? $yabb['authuser'] : '',
			'smtp_password' => !empty($yabb['smtp_auth_required']) && isset($yabb['authpass']) ? $yabb['authpass'] : '',
			'defaultMaxTopics' => !empty($yabb['maxdisplay']) ? (int) $yabb['maxdisplay'] : 20,
			'defaultMaxMessages' => !empty($yabb['maxmessagedisplay']) ? (int) $yabb['maxmessagedisplay'] : 15,
			'max_messageLength' => !empty($yabb['MaxMessLen']) ? (int) $yabb['MaxMessLen'] : 10000,
			'max_signatureLength' => (int) $yabb['MaxSigLen'],
			'spamWaitTime' => (int) $yabb['timeout'],
			'hotTopicPosts' => isset($yabb['HotTopic']) ? (int) $yabb['HotTopic'] : 15,
			'hotTopicVeryPosts' => isset($yabb['VeryHotTopic']) ? (int) $yabb['VeryHotTopic'] : 25,
			'avatar_max_width_external' => (int) $yabb['userpic_width'],
			'avatar_max_height_external' => (int) $yabb['userpic_height'],
			'avatar_max_width_upload' => (int) $yabb['userpic_width'],
			'avatar_max_height_upload' => (int) $yabb['userpic_height'],
			'reserveWord' => trim($temp[0]) == 'checked' ? '1' : '0',
			'reserveCase' => trim($temp[1]) == 'checked' ? '1' : '0',
			'reserveUser' => trim($temp[2]) == 'checked' ? '1' : '0',
			'reserveName' => trim($temp[3]) == 'checked' ? '1' : '0',
			'reserveNames' => addslashes(strtr(implode('', file($yabb['vardir'] . '/reserve.txt')), array("\r" => ''))),
		);

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
		global $to_prefix, $yabb;

		// Change the block size as needed
		$block_size = 100;

		if ($_GET['substep'] == 0 && !empty($_SESSION['purge']))
		{
			convert_query("
				TRUNCATE {$to_prefix}personal_messages");
			convert_query("
				TRUNCATE {$to_prefix}pm_recipients");
		}
		if ($_GET['substep'] == 0)
		{
			// Set the keys and columns to alter.
			$knownKeys = array(
				'PRIMARY' => 'DROP PRIMARY KEY',
			);
			$knownColumns = array(
				'temp_toName' => 'ADD COLUMN temp_toName tinytext',
			);
			$alterColumns = array(
				'ID_PM' => 'CHANGE COLUMN ID_PM ID_PM int(10) unsigned NOT NULL default 0',
			);
			alterTable('personal_messages', $knownKeys, $knownColumns, $alterColumns);
		}

		print_line('Converting personal messages...');

		$names = array();

		$file_n = 0;
		$dir = dir($yabb['memberdir']);
		$block = array();
		while ($entry = $dir->read())
		{
			if ($_GET['substep'] < 0)
				break;
			if ($file_n++ < $_GET['substep'])
				continue;
			if (strrchr($entry, '.') != '.msg')
				continue;

			$userData = file($yabb['memberdir'] . '/' . $entry);
			foreach ($userData as $i => $v)
			{
				$userData[$i] = explode('|', rtrim($userData[$i]));
				if (count($userData[$i]) <= 2 || empty($userData[$i]))
					continue;

				if (substr($userData[$i][7], -10) == '#nosmileys')
					$userData[$i][7] = substr($userData[$i][7], 0, -10);

				$row = array(
					'fromName' => substr(htmlspecialchars($userData[$i][1]), 0, 255),
					'subject' => substr($userData[$i][5], 0, 255),
					'msgtime' => empty($userData[$i][6]) ? (int) $userData[$i][6] : '0',
					'body' => substr($userData[$i][7], 0, 65534),
					'ID_MEMBER_FROM' => 0,
					'deletedBySender' => 1,
					'temp_toName' => htmlspecialchars(substr($entry, 0, -4)),
				);

				$names[strtolower(addslashes($row['fromName']))][] = &$row['ID_MEMBER_FROM'];

				$block[] = addslashes_recursive($row);
			}

			if (count($block) > $block_size)
			{
				$result = convert_query("
					SELECT ID_MEMBER, memberName
					FROM {$to_prefix}members
					WHERE memberName IN ('" . implode("', '", array_keys($names)) . "')
					LIMIT " . count($names));
				while ($row = mysql_fetch_assoc($result))
					foreach ($names[strtolower(addslashes($row['memberName']))] as $k => $v)
						$names[strtolower(addslashes($row['memberName']))][$k] = $row['ID_MEMBER'];
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
		if ($_GET['substep'] <= -1)
		{
			convert_query("
				ALTER TABLE {$to_prefix}personal_messages
				ORDER BY ID_PM, msgtime");

			pastTime(-2);
		}
		if ($_GET['substep'] <= -2)
		{
			$knownKeys = array(
				'PRIMARY' => 'ADD PRIMARY KEY (ID_PM)',
			);
			$alterColumns = array(
				'ID_PM' => 'CHANGE COLUMN ID_PM ID_PM int(10) unsigned NOT NULL auto_increment',
			);
			alterTable('personal_messages', $knownKeys, '', $alterColumns, true);

			pastTime(-3);
		}
		if ($_GET['substep'] <= -3)
		{
			convert_query("
				INSERT IGNORE INTO {$to_prefix}pm_recipients
					(ID_PM, ID_MEMBER, labels, is_read)
				SELECT pm.ID_PM, mem.ID_MEMBER, -1 AS labels, 1 AS is_read
				FROM ({$to_prefix}personal_messages AS pm, {$to_prefix}members AS mem)
				WHERE mem.memberName = pm.temp_toName
					AND pm.temp_toName != ''");

			pastTime(-4);
		}
		if ($_GET['substep'] <= -4)
		{
			$knownColumns = array(
				'temp_toName' => 'DROP temp_toName',
			);
			alterTable('personal_messages', '', $knownColumns, false, true);

			pastTime(-5);
		}
		if ($_GET['substep'] <= -5)
		{
			convert_query("
				ALTER TABLE {$to_prefix}personal_messages
				ORDER BY ID_PM");
		}
	}

	function convertStep5()
	{
		global $to_prefix, $yabb;

		print_line('Converting boards and categories...');

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
			// Handle the categories table and columns
			$knownColumns = array(
				'tempID' => 'ADD COLUMN tempID tinytext',
			);
			alterTable('categories', '', $knownColumns);

			// Use the $knownColumns frm before and add tempCatID then alter boards table.
			$knownColumns += array(
				'tempCatID' => 'ADD COLUMN tempCatID tinytext',
			);
			alterTable('boards', '', $knownColumns);

			// Drop the primary key for moderators
			$knownKeys = array(
				'PRIMARY' => 'DROP PRIMARY KEY',
			);
			alterTable('moderators', $knownKeys);
		}

		$request = convert_query("
			SELECT ID_GROUP, groupName
			FROM {$to_prefix}membergroups
			WHERE ID_GROUP != 3");
		$groups = array('Administrator' => 1, 'Global Moderator' => 2, 'Moderator' => 0);
		while ($row = mysql_fetch_assoc($request))
			$groups[$row['groupName']] = $row['ID_GROUP'];
		mysql_free_result($request);

		$cat_data = file($yabb['boardsdir'] . '/forum.master');
		$cat_order = array();
		$cats = array();
		$boards = array();
		foreach ($cat_data as $line)
		{
			if (preg_match('~^\$board\{\'(.+?)\'\} = ([^|]+)~', $line, $match) != 0)
				$boards[$match[1]] = trim($match[2], '"');
			elseif (preg_match('~^\$catinfo\{\'(.+?)\'\} = ([^|]+?)\|([^|]*?)\|([^|]+?);~', $line, $match) != 0)
			{
				$match[3] = explode(',', $match[3]);
				if (trim($match[3][0]) == '')
					$cat_groups = array_merge($groups, array(2, 0, -1));
				else
				{
					$cat_groups = array(2);
					foreach ($match[3] as $group)
					{
						if (isset($groups[trim($group)]))
							$cat_groups[] = $groups[trim($group)];
					}
				}

				// Make the tempCatID lowercase
				$match[1] = strtolower(trim($match[1]));

				$cats[$match[1]]['name'] = trim($match[2], '"');
				$cats[$match[1]]['groups'] = implode(',', $cat_groups);
				$cats[$match[1]]['canCollapse'] = !empty($match[4]);
			}
			elseif (preg_match('~^@categoryorder = qw\((.+?)\);~', $line, $match) != 0)
				$cat_order = array_flip(explode(' ', ' ', strtolower(trim($match[1]))));
		}

		$cat_rows = array();
		foreach ($cats as $tempID => $cat)
		{
			$tempID = strtolower(trim($tempID));
			$row = array(
				'name' => str_replace(array('qq~', 'qw~'), '', substr($cat['name'], 0, 255)),
				'catOrder' => (int) @$cat_order[$tempID],
				'tempID' => $tempID,
			);
			$cat_rows[] = addslashes_recursive($row);
		}
		doBlock('categories', $cat_rows);

		$board_data = file($yabb['boardsdir'] . '/forum.control');
		$boardOrder = 1;
		$moderators = array();
		$board_rows = array();
		foreach ($board_data as $line)
		{
			list ($tempCatID, $tempID, , $description, $mods, , , , , $doCountPosts, , , $is_recycle) = explode('|', rtrim($line));
			// !!! is_recycle -> set recycle board?

			// Set lower case since case matters in PHP
			$tempCatID = strtolower(trim($tempCatID));

			$row = array(
				'name' => str_replace(array('qq~', 'qw~'), '', substr($boards[$tempID], 0, 255)),
				'description' => substr($description, 0, 255),
				'countPosts' => empty($doCountPosts),
				'boardOrder' => $boardOrder++,
				'memberGroups' => $cats[$tempCatID]['groups'],
				'tempID' => $tempID,
				'tempCatID' => $tempCatID,
			);

			$board_rows[] = addslashes_recursive($row);

			$moderators[$tempID] = preg_split('~(, | |,)~', $mods);
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

		pastTime(-1);
		if ($_GET['substep'] <= -1)
		{
			$knownColumns = array(
				'tempID' => 'DROP COLUMN tempID',
			);
			alterTable('categories', '', $knownColumns, '', false, true);

			pastTime(-2);
		}
		if ($_GET['substep'] <= -2)
		{
			$knownColumns = array(
				'tempCatID' => 'DROP COLUMN tempCatID',
			);
			alterTable('boards', '', $knownColumns, '', false, true);

			pastTime(-3);
		}
		if ($_GET['substep'] <= -3)
		{
			$knownKeys = array(
				'PRIMARY' => 'ADD PRIMARY KEY (ID_BOARD, ID_MEMBER)',
			);
			alterTable('moderators', $knownKeys, '', '', true);
		}
	}

	function convertStep6()
	{
		global $to_prefix, $yabb;

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
			$knownKeys = array(
				'PRIMARY' => 'DROP PRIMARY KEY',
			);
			$knownColumns = array(
				'tempID' => 'ADD COLUMN tempID int(10) unsigned NOT NULL default 0',
			);
			alterTable('log_topics', $knownKeys, $knownColumns);
		}

		print_line('Converting mark read data...');

		$result = convert_query("
			SELECT ID_BOARD, tempID
			FROM {$to_prefix}boards");
		$boards = array();
		while ($row = mysql_fetch_assoc($result))
			$boards[$row['tempID']] = $row['ID_BOARD'];
		mysql_free_result($result);

		$file_n = 0;
		$dir = dir($yabb['memberdir']);
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

			$logData = file($yabb['memberdir'] . '/' . $entry);
			foreach ($logData as $log)
			{
				$parts = array_pad(explode('|', $log), 3, '');
				if (trim($parts[0]) == '')
					continue;

				$row = array();
				$row['ID_MEMBER'] = $ID_MEMBER;

				if (is_numeric(trim($parts[0])) && trim($parts[0]) > 10000)
				{
					$row['tempID'] = trim($parts[0]);
					$topics_block[] = $row;
				}
				else
				{
					if (substr(trim($parts[0]), -6) == '--mark' && isset($boards[substr(trim($parts[0]), 0, -6)]))
					{
						$row['ID_BOARD'] = $boards[substr(trim($parts[0]), 0, -6)];
						$mark_read_block[] = $row;
					}
					elseif (isset($boards[trim($parts[0])]))
					{
						$row['ID_BOARD'] = $boards[trim($parts[0])];
						$boards_block[] = $row;
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
		global $to_prefix, $yabb;

		// Change the block size as needed
		$block_size = 100;

		if ($_GET['substep'] == 0 && !empty($_SESSION['purge']))
		{
			convert_query("
				TRUNCATE {$to_prefix}topics");
		}
		if ($_GET['substep'] == 0)
		{
			$knownKeys = array(
				'PRIMARY' => 'DROP PRIMARY KEY',
				'poll' => 'DROP INDEX poll',
				'firstMessage' => 'DROP INDEX firstMessage',
				'lastMessage' => 'DROP INDEX lastMessage',
			);
			$knownColumns = array(
				'tempID' => 'ADD COLUMN tempID int(10) unsigned NOT NULL default 0'
			);
			$alterColumns = array(
				'ID_TOPIC' => 'CHANGE COLUMN ID_TOPIC ID_TOPIC mediumint(8) unsigned NOT NULL default 0',
			);
			alterTable('topics', $knownKeys, $knownColumns, $alterColumns);
		}

		print_line('Converting topics (part 1)...');

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
			if (!file_exists($yabb['boardsdir'] . '/' . $boardname . '.txt'))
				continue;

			$topicListing = file($yabb['boardsdir'] . '/' . $boardname . '.txt');
			foreach ($topicListing as $topicData)
			{
				if ($data_n++ < $_GET['substep'])
					continue;

				$topicInfo = explode('|', rtrim($topicData));
				$tempID = (int) $topicInfo[0];

				if (!file_exists($yabb['datadir'] . '/' . $tempID . '.txt'))
					continue;

				$views = @file($yabb['datadir'] . '/' . $tempID . '.ctb');
				$views = empty($views[2]) || $views[2] < 1 ? 0 : $views[2] - 1;

				$block[] = array(
					'tempID' => $tempID,
					'ID_BOARD' => (int) $ID_BOARD,
					'isSticky' => isset($topicInfo[8]) && strpos($topicInfo[8], 's') !== false ? 1 : 0,
					'locked' => isset($topicInfo[8]) && strpos($topicInfo[8], 'l') !== false ? 1 : 0,
					'numViews' => $views < 0 ? 0 : $views,
				);

				if (count($block) > $block_size)
				{
					doBlock('topics', $block);
					pastTime($data_n);
				}
			}
		}

		doBlock('topics', $block);

		pastTime(-1);

		if ($_GET['substep'] <= -1)
		{
			convert_query("
				DELETE FROM {$to_prefix}topics
				WHERE ID_TOPIC = 0");

			convert_query("
				UPDATE {$to_prefix}topics
				SET tempID = ID_TOPIC
				WHERE tempID = 0");

			pastTime(-2);
		}
		if ($_GET['substep'] <= -2)
		{
			convert_query("
				ALTER TABLE {$to_prefix}topics
				ORDER BY ID_TOPIC, tempID");

			pastTime(-3);
		}
		if ($_GET['substep'] <= -3)
		{
			$knownKeys = array(
				'PRIMARY' => 'ADD PRIMARY KEY (ID_TOPIC)',
			);
			$alterColumns = array(
				'ID_TOPIC' => 'CHANGE COLUMN ID_TOPIC ID_TOPIC mediumint(8) unsigned NOT NULL auto_increment',
			);
			alterTable('topics', $knownKeys, '', $alterColumns, true);

			pastTime(-4);
		}
		if ($_GET['substep'] <= -4)
		{
			convert_query("
				ALTER TABLE {$to_prefix}topics
				ORDER BY ID_TOPIC");
		}
	}

	function convertStep8()
	{
		global $to_prefix, $yabb;

	return;
		// Change the block size as needed
		$block_size = 10000; //250;

		if ($_GET['substep'] == 0)
		{
			$knownColumns = array(
				'tempID' => 'DROP COLUMN tempID',
			);
			alterTable('boards', '', $knownColumns, '', false, true);
		}

		print_line('Converting topics (part 2)...');

		$request = convert_query("
			SELECT COUNT(*)
			FROM {$to_prefix}topics
			WHERE tempID != ID_TOPIC");
		list ($topicCount) = mysql_fetch_row($request);
		mysql_free_result($request);

		while ($_GET['substep'] <= $topicCount)
		{
			pastTime($_GET['substep']);

			$result = convert_query("
				SELECT ID_TOPIC, tempID
				FROM {$to_prefix}topics
				WHERE tempID != ID_TOPIC
				LIMIT $_GET[substep], $block_size");
			while ($row = mysql_fetch_assoc($result))
			{
				convert_query("
					UPDATE {$to_prefix}log_topics
					SET ID_TOPIC = $row[ID_TOPIC]
					WHERE tempID = $row[tempID]");
			}

			$_GET['substep'] += $block_size;

			mysql_free_result($result);
		}

		pastTime(-1);

		if ($_GET['substep'] <= -1)
		{
			convert_query("
				DELETE FROM {$to_prefix}log_topics
				WHERE ID_TOPIC = 0 
					OR ID_MEMBER = 0");

			pastTime(-2);
		}
		if ($_GET['substep'] <= -2)
		{
			$result = convert_query("
				SELECT ID_TOPIC
				FROM {$to_prefix}log_topics
				GROUP BY ID_TOPIC
				HAVING COUNT(ID_MEMBER) > 1");

			while ($row = mysql_fetch_assoc($result))
				convert_query("
					DELETE FROM {$to_prefix}log_topics
					WHERE ID_TOPIC = $row[ID_TOPIC]
					LIMIT 1");
			mysql_free_result($result);

			pastTime(-3);
		}
		if ($_GET['substep'] <= -3)
		{
			$knownKeys = array(
				'PRIMARY' => 'ADD PRIMARY KEY (ID_TOPIC, ID_MEMBER)',
			);
			$knownColumns = array(
				'tempID' => 'DROP COLUMN tempID',
			);
			alterTable('log_topics', $knownKeys, $knownColumns, '', true, true);
		}
	}

	function convertStep9()
	{
		global $to_prefix, $yabb;

		// Change the block size as needed
		$block_size = 150;

		if ($_GET['substep'] == 0 && !empty($_SESSION['purge']))
		{
			convert_query("
				TRUNCATE {$to_prefix}log_notify");
		}

		print_line('Converting notifications...');

		$request = convert_query("
			SELECT COUNT(*)
			FROM {$to_prefix}topics
			WHERE tempID != ID_TOPIC");
		list ($count) = mysql_fetch_row($request);
		mysql_free_result($request);

		while ($_GET['substep'] < $count)
		{
			pastTime($_GET['substep']);

			$result = convert_query("
				SELECT ID_TOPIC, tempID
				FROM {$to_prefix}topics
				WHERE tempID != ID_TOPIC
				LIMIT $_GET[substep], $block_size");
			while ($row = mysql_fetch_assoc($result))
			{
				if (!file_exists($yabb['datadir'] . '/' . $row['tempID'] . '.mail'))
					continue;

				$list = file($yabb['datadir'] . '/' . $row['tempID'] . '.mail');
				foreach ($list as $k => $v)
					list ($list[$k]) = explode('|', htmlspecialchars(addslashes(rtrim($v))));

				convert_query("
					INSERT IGNORE INTO {$to_prefix}log_notify
						(ID_TOPIC, ID_MEMBER)
					SELECT $row[ID_TOPIC], ID_MEMBER
					FROM {$to_prefix}members
					WHERE memberName IN ('" . implode("', '", $list) . "')
					LIMIT " . count($list));
			}

			$_GET['substep'] += $block_size;
			if (mysql_num_rows($result) < $block_size)
				break;

			mysql_free_result($result);
		}
	}

	function convertStep10()
	{
		global $to_prefix, $yabb;

		// Change the block size as needed
		$block_size = 100;

		if ($_GET['substep'] == 0 && !empty($_SESSION['purge']))
		{
			convert_query("
				TRUNCATE {$to_prefix}messages");
			convert_query("
				TRUNCATE {$to_prefix}attachments");
		}
		if ($_GET['substep'] == 0)
		{
			$knownKeys = array(
				'PRIMARY' => 'DROP PRIMARY KEY',
				'topic' => 'DROP INDEX topic',
				'ID_BOARD' => 'DROP INDEX ID_BOARD',
				'ID_TOPIC' => 'DROP INDEX ID_TOPIC',
				'ID_MEMBER' => 'DROP INDEX ID_MEMBER',
			);
			$alterColumns = array(
				'ID_MSG' => 'CHANGE COLUMN ID_MSG ID_MSG int(10) unsigned NOT NULL default 0',
			);

			// Do we have attachments?
			if (isset($yabb['uploaddir']))
				$knownColumns = array(
					'temp_filename' => "ADD COLUMN temp_filename tinytext NOT NULL",
				);
			else
				$knownColumns = array();

			alterTable('messages', $knownKeys, $knownColumns, $alterColumns);
		}

		print_line('Converting posts (part 1 - this may take some time)...');

		$block = array();
		while (true)
		{
			$result = convert_query("
				SELECT ID_TOPIC, tempID, ID_BOARD
				FROM {$to_prefix}topics
				WHERE tempID != ID_TOPIC
				LIMIT $_GET[substep], $block_size");
			while ($topic = mysql_fetch_assoc($result))
			{
				$messages = file($yabb['datadir'] . '/' . $topic['tempID'] . '.txt');
				if (empty($messages))
				{
					convert_query("
						DELETE FROM {$to_prefix}topics
						WHERE ID_TOPIC = $topic[ID_TOPIC]
						LIMIT 1");

					pastTime($_GET['substep']);
					continue;
				}

				foreach ($messages as $message)
				{
					if (trim($message) == '')
						continue;

					$message = array_pad(explode('|', $message), 12, '');
					foreach ($message as $k => $v)
						$message[$k] = rtrim($v);

					if (substr($message[8], -10) == '#nosmileys')
						$message[8] = substr($message[8], 0, -10);

					$row = array(
						'ID_TOPIC' => (int) $topic['ID_TOPIC'],
						'ID_BOARD' => (int) $topic['ID_BOARD'],
						'subject' => substr($message[0], 0, 255),
						'posterName' => substr(htmlspecialchars($message[4] == 'Guest' ? trim($message[1]) : trim($message[4])), 0, 255),
						'posterEmail' => substr(htmlspecialchars($message[2]), 0, 255),
						'posterTime' => !empty($message[3]) ? $message[3] : 0,
						'icon' => substr($message[5], 0, 16),
						'posterIP' => substr($message[7], 0, 255),
						'body' => substr(preg_replace('~\[quote author=.+? link=.+?\]~i', '[quote]', $message[8]), 0, 65534),
						'smileysEnabled' => (int) !empty($message[9]) ? 1 : 0,
						'modifiedTime' => !empty($message[10]) ? $message[10] : 0,
						'modifiedName' => substr($message[11], 0, 255),
					);

					if (isset($yabb['uploaddir']))
					{
						if (isset($message[12]) && file_exists($yabb['uploaddir'] . '/' . $message[12]))
							$row['temp_filename'] = $message[12];
						else
							$row['temp_filename'] = '';
					}

					$block[] = addslashes_recursive($row);

					if (count($block) > $block_size)
						doBlock('messages', $block);
				}

				doBlock('messages', $block);
				pastTime(++$_GET['substep']);
			}

			if (mysql_num_rows($result) < $block_size)
				break;

			mysql_free_result($result);
		}

		doBlock('messages', $block);
	}

	function convertStep11()
	{
		global $to_prefix, $yabb;

		// Change the block size as needed
		$block_size = 100000; //150;

		if ($_GET['substep'] == 0)
		{
			mysql_query("
				ALTER TABLE {$to_prefix}messages
				ORDER BY posterTime");

			// At least give it something, since we don't use it in a query.	
			$_GET['substep'] = 1;
		}

		print_line('Converting posts (part 2)...');

		pastTime($_GET['substep']);

		$request = convert_query("
			SELECT COUNT(*)
			FROM {$to_prefix}messages
			WHERE ID_MSG = 0");
		list($uncompleted_messages) = mysql_fetch_row($request);
		mysql_free_result($request);

		if (!empty($uncompleted_messages))
		{
			$request = convert_query("
				SELECT @msg := IFNULL(MAX(ID_MSG), 0)
				FROM {$to_prefix}messages");
			mysql_free_result($request);

			while(!empty($uncompleted_messages))
			{
				mysql_query("
					UPDATE {$to_prefix}messages
					SET ID_MSG = (@msg := @msg + 1)
					WHERE ID_MSG = 0
					LIMIT $block_size");

				$_GET['substep'] += $block_size;
				pastTime($_GET['substep']);

		$request = convert_query("
			SELECT COUNT(*)
			FROM {$to_prefix}messages
			WHERE ID_MSG = 0");
		list($uncompleted_messages) = mysql_fetch_row($request);	
		if empty($uncompleted_messages))
			break;
			}
		}

		pastTime(-1);

		if ($_GET['substep'] <= -1)
		{
			$knownKeys = array(
				'PRIMARY' => 'ADD PRIMARY KEY (ID_MSG)',
			);
			$alterColumns = array(
				'ID_MSG' => 'CHANGE COLUMN ID_MSG ID_MSG int(10) unsigned NOT NULL auto_increment',
			);
			alterTable('messages', $knownKeys, '', $alterColumns, true);
		}
	}

	function convertStep12()
	{
		global $to_prefix, $yabb;

		// Change the block size as needed
		$block_size = 100000; // 150

		print_line('Converting posts (part 3)...');

		while (true)
		{
			$result = convert_query("
				SELECT m.ID_MSG, mem.ID_MEMBER
				FROM ({$to_prefix}messages AS m, {$to_prefix}members AS mem)
				WHERE m.posterName = mem.memberName
					AND m.ID_MEMBER = 0
				LIMIT $block_size");
			$numRows = mysql_num_rows($result);

			while ($row = mysql_fetch_assoc($result))
				convert_query("
					UPDATE {$to_prefix}messages
					SET ID_MEMBER = $row[ID_MEMBER]
					WHERE ID_MSG = $row[ID_MSG]");
			mysql_free_result($result);

			// Just so the user knows something occured.
			$_GET['substep'] += $block_size;

			if ($numRows < 1)
				break;
			else
				pastTime($_GET['substep']);
		}
	}

	function convertStep13()
	{
		global $to_prefix, $yabb;

		// Change the block size as needed
		$block_size = 100;

		print_line('Converting attachments (if the mod is installed)...');

		if (!isset($yabb['uploaddir']))
			return;

		$result = convert_query("
			SELECT value
			FROM {$to_prefix}settings
			WHERE variable = 'attachmentUploadDir'
			LIMIT 1");
		list ($attachmentUploadDir) = mysql_fetch_row($result);
		mysql_free_result($result);

		// Danger, Will Robinson!
		if ($yabb['uploaddir'] == $attachmentUploadDir)
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
				LIMIT $_GET[substep], $block_size");
			while ($row = mysql_fetch_assoc($result))
			{
				$size = filesize($yabb['uploaddir'] . '/' . $row['temp_filename']);
				$filename = getAttachmentFilename($row['temp_filename'], $ID_ATTACH);

				// Is this an image???
				$attachmentExtension = strtolower(substr(strrchr($row['temp_filename'], '.'), 1));
				if (!in_array($attachmentExtension, array('jpg', 'jpeg', 'gif', 'png')))
					$attachmentExtention = '';

				if (strlen($filename) <= 255 &&  copy($yabb['uploaddir'] . '/' . $row['temp_filename'], $attachmentUploadDir . '/' . $filename))
				{
					// Set the default empty values.
					$width = '0';
					$height = '0';

					// Is an an image?
					if (!empty($attachmentExtension))
						list ($width, $height) = getimagesize($yabb['uploaddir'] . '/' . $row['temp_filename']);

					$setString .= "
						($ID_ATTACH, $size, 0, '" . addslashes($row['temp_filename']) . "', $row[ID_MSG], '$width', '$height'),";

					$ID_ATTACH++;
				}
			}

			if ($setString != '')
				convert_query("
					INSERT INTO {$to_prefix}attachments
						(ID_ATTACH, size, downloads, filename, ID_MSG, width, height)
					VALUES" . substr($setString, 0, -1));

			$_GET['substep'] += $block_size;
			if (mysql_num_rows($result) < $block_size)
				break;

			mysql_free_result($result);
		}

		pastTime(-1);

		if ($_GET['substep'] <= -1)
		{
			$knownColumns = array(
				'temp_filename' => 'DROP COLUMN temp_filename'
			);
			alterTable('messages', '', $knownColumns, '', false, true);
		}
	}

	function convertStep14()
	{
		global $to_prefix, $yabb;

		print_line('Cleaning up (part 1)...');

		if ($_GET['substep'] <= 0)
		{
			$knownKeys = array(
				'topic' => 'ADD UNIQUE INDEX topic (ID_TOPIC, ID_MSG)'
			);
			alterTable('messages', $knownKeys, '', true);

			pastTime(1);
		}
		if ($_GET['substep'] <= 1)
		{
			$knownKeys = array(
				'ID_BOARD' => 'ADD UNIQUE INDEX ID_BOARD (ID_BOARD, ID_MSG)',
			);
			alterTable('messages', $knownKeys, '', true);

			pastTime(2);
		}
		if ($_GET['substep'] <= 2)
		{
			$knownKeys = array(
				'ID_TOPIC' => 'ADD KEY ID_TOPIC (ID_TOPIC)',
			);
			alterTable('messages', $knownKeys, '', true);

			pastTime(3);
		}
		if ($_GET['substep'] <= 3)
		{
			$knownKeys = array(
				'ID_MEMBER' => 'ADD UNIQUE INDEX ID_MEMBER (ID_MEMBER, ID_MSG)',
			);
			alterTable('messages', $knownKeys, '', true);

		}
	}

	function convertStep15()
	{
		global $to_prefix, $yabb;

		// Change the block size as needed
		$block_size = 150; // 150;

		print_line('Cleaning up (part 2)...');

		while ($_GET['substep'] > -1)
		{
			pastTime($_GET['substep']);

			$result = convert_query("
				SELECT t.ID_TOPIC, MIN(m.ID_MSG) AS ID_FIRST_MSG, MAX(m.ID_MSG) AS ID_LAST_MSG
				FROM ({$to_prefix}topics AS t, {$to_prefix}messages AS m)
				WHERE m.ID_TOPIC = t.ID_TOPIC
				GROUP BY t.ID_TOPIC
				LIMIT $_GET[substep], $block_size");
			
			if (!mysql_num_rows($result))
			{
				$_GET['substep'] = -1;
				pastTime(-1);
			}

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

			$_GET['substep'] += $block_size;
			if (mysql_num_rows($result) < $block_size)
				break;

			mysql_free_result($result);
		}

		if ($_GET['substep'] > -1)
		{
			$knownKeys = array(
				'lastMessage' => 'ADD UNIQUE INDEX lastMessage (ID_LAST_MSG, ID_BOARD)',
			);
			alterTable('topics', $knownKeys, '', true);

			pastTime(-2);
		}
		if ($_GET['substep'] >= -2)
		{
			$knownKeys = array(
				'firstMessage' => 'ADD UNIQUE INDEX firstMessage (ID_FIRST_MSG, ID_BOARD)',
			);
			alterTable('topics', $knownKeys, '', true);
		}
	}

	function convertStep16()
	{
		global $to_prefix, $yabb;

		// Change the block size as needed
		$block_size = 25; // 50

		// If set remove the old data
		if ($_GET['substep'] == 0 && !empty($_SESSION['purge']))
		{
			convert_query("
				TRUNCATE {$to_prefix}polls");
			convert_query("
				TRUNCATE {$to_prefix}poll_choices");
			convert_query("
				TRUNCATE {$to_prefix}log_polls");
		}

		// Drop the indexes to prevent Dup keys errors
		if ($_GET['substep'] == 0)
		{
			$knownKeys = array(
				'PRIMARY' => 'DROP PRIMARY KEY',
			);
			$knownColumns = array(
				'tempID' => 'ADD tempID int(10) unsigned NOT NULL default 0',
			);
			$alterColumns = array(
				'ID_POLL' => 'CHANGE COLUMN ID_POLL ID_POLL MEDIUMINT(8) unsigned NOT NULL default 0',
			);
			alterTable('polls', $knownKeys, $knownColumns, $alterColumns);
			alterTable('poll_choices', $knownKeys);
		}

		print_line('Converting polls and poll choices...');

		$file_n = 0;
		$dir = dir($yabb['datadir']);
		$pollQuestionsBlock = array();
		$pollChoicesBlock = array();
		while ($entry = $dir->read())
		{
			if ($_GET['substep'] < 0)
				break;
			if ($file_n++ < $_GET['substep'])
				continue;
			if (strrchr($entry, '.') != '.poll')
			{
				++$_GET['substep'];
				continue;
			}

			$pollData = file($yabb['datadir'] . '/' . $entry);

			$ID_POLL = substr($entry, 0, strrpos($entry, '.'));

			foreach ($pollData as $i => $v)
			{
				$pollData[$i] = explode('|', rtrim($pollData[$i]));

				// Is this the poll option/question?  If so set the data.
				if (count($pollData[$i]) > 3)
				{
					$pollQuestions = array(
						'question' => substr(htmlspecialchars($pollData[$i][0]), 0, 255),
						'votingLocked' => (int) $pollData[$i][1],
						'maxVotes' => (int) $pollData[$i][8],
						'expireTime' => 0,
						'hideResults' => (int) $pollData[$i][7],
						'changeVote' => 0,
						'ID_MEMBER' => 0,
						'posterName' => empty($pollData[$i][3]) ? 'Guest' : substr(htmlspecialchars($pollData[$i][3]), 0, 255),
						'tempID' => (int) $ID_POLL,
					);
					$pollQuestionsBlock[] = addslashes_recursive($pollQuestions);
				}

				// Are these the choices?
				if (count($pollData[$i]) == 2)
				{
					$pollChoices = array(
						'ID_POLL' => $ID_POLL,
						'ID_CHOICE' => $i - 1, // Make sure to subtract the first row since that's the question
						'label' => $pollData[$i][1],
						'votes' => (int) $pollData[$i][0],
					);
					$pollChoicesBlock[] = addslashes_recursive($pollChoices);
				}
			}

			// Since we are basing this off questions lets put the number of rows to a lower ammount since it will be more with the choices
			if (count($file_n) > $block_size)
			{
				// Set the ID_TOPIC
				$topics = array();
				foreach ($pollQuestionsBlock as $question)
					$topics[] = $question['tempID'];

				// Select the members
				$request = convert_query("
					SELECT ID_MEMBER_STARTED AS ID_MEMBER, tempID, ID_TOPIC
					FROM {$to_prefix}topics
					WHERE tempID IN (" . implode(',', $topics) . ")");

				while ($row = mysql_fetch_assoc($request))
				{
					// Assign ID_POLL ID_MEMBER to the pollQuestion
					foreach ($pollQuestionsBlock as $keyID => $questions)
					{
						if (isset($pollQuestionsBlock[$keyID]['ID_MEMBER']) && $pollQuestionsBlock[$keyID]['ID_MEMBER'] == $row['ID_MEMBER'])
								$pollQuestionsBlock[$keyID]['ID_MEMBER'] = $row['ID_MEMBER'];
					}
					// Assign ID_POLL to the choices
					foreach ($pollChoicesBlock as $keyID => $choices)
					{
						foreach ($choices as $key => $choice)
							if ($key == 'ID_POLL' && $choice == $row['tempID'])
								$pollChoicesBlock[$keyID]['ID_POLL'] = $row['ID_TOPIC'];
					}
				}
				mysql_free_result($request);

				doBlock('polls', $pollQuestionsBlock);
				doBlock('poll_choices', $pollChoicesBlock);

				// Increase the time
				pastTime($file_n);
			}
		}
		$dir->close();

		if (!empty($pollQuestionsBlock) && !empty($pollChoicesBlock))
		{
			$topics = array();
			foreach ($pollQuestionsBlock as $question)
				$topics[] = $question['tempID'];

			// Select the members
			$request = convert_query("
				SELECT ID_MEMBER_STARTED AS ID_MEMBER, tempID, ID_TOPIC
				FROM {$to_prefix}topics
				WHERE tempID IN (" . implode(',', $topics) . ")");

			while ($row = mysql_fetch_assoc($request))
			{
				// Assign ID_POLL ID_MEMBER to the pollQuestion
				foreach ($pollQuestionsBlock as $keyID => $questions)
				{
					if (isset($pollQuestionsBlock[$keyID]['ID_MEMBER']) && $pollQuestionsBlock[$keyID]['ID_MEMBER'] == $row['tempID'])
							$pollQuestionsBlock[$keyID]['ID_MEMBER'] = $row['ID_MEMBER'];
				}
				// Assign ID_POLL to the choices
				foreach ($pollChoicesBlock as $keyID => $choices)
				{
					foreach ($choices as $key => $choice)
						if ($key == 'ID_POLL' && $choice == $row['tempID'])
							$pollChoicesBlock[$keyID]['ID_POLL'] = $row['ID_TOPIC'];
				}
			}
			mysql_free_result($request);

			doBlock('polls', $pollQuestionsBlock);
			doBlock('poll_choices', $pollChoicesBlock);
		}

		pastTime(-1);

		if ($_GET['substep'] <= -1)
		{
			$knownKeys = array(
				'PRIMARY' => 'ADD PRIMARY KEY (ID_POLL)',
			);
			$alterColumns = array(
				'ID_POLL' => 'CHANGE COLUMN ID_POLL ID_POLL MEDIUMINT(8) unsigned NOT NULL auto_increment',
			);
			alterTable('polls', $knownKeys, '', $alterColumns, true, true);
		}
	}

	function convertStep17()
	{
		global $to_prefix;

		// Change the block size as needed
		$block_size = 200;

		print_line('Converting polls and poll choices (part 2)...');

		while (true)
		{
			pastTime($_GET['substep']);

			$request = convert_query("
				SELECT p.ID_POLL, t.ID_TOPIC
				FROM ({$to_prefix}polls AS p, {$to_prefix}topics AS t)
				WHERE p.tempID = t.tempID
				LIMIT $_GET[substep], $block_size");

			while ($row = mysql_fetch_assoc($request))
			{
				convert_query("
					UPDATE {$to_prefix}topics
					SET ID_POLL = $row[ID_POLL]
					WHERE ID_TOPIC = $row[ID_TOPIC]");
				convert_query("
					UPDATE {$to_prefix}poll_choices
					SET ID_POLL = $row[ID_POLL]
					WHERE ID_POLL = $row[ID_TOPIC]");
			}

			$_GET['substep'] += $block_size;
			if (mysql_num_rows($request) < $block_size)
				break;

			mysql_free_result($request);
		}
	}

	function convertStep18()
	{
		global $to_prefix, $yabb;

		// Change the block size as needed
		$block_size = 50;

		print_line('Converting poll votes...');

		$file_n = 0;
		$dir = dir($yabb['datadir']);
		$pollVotesBlock = array();
		$members = array();
		$pollIdsBlock = array();
		while ($entry = $dir->read())
		{
			if ($_GET['substep'] < 0)
				break;
			if ($file_n++ < $_GET['substep'])
				continue;
			if (strrchr($entry, '.') != '.polled')
				continue;

			$pollVotesData = file($yabb['datadir'] . '/' . $entry);
			$ID_POLL = substr($entry, 0, strrpos($entry, '.'));

			$pollIdsBlock[] = $ID_POLL;
			// Get the data from each line/
			foreach ($pollVotesData as $i => $votes)
			{
				$pollVotesData[$i] = explode('|', rtrim($pollVotesData[$i]));

				// We just need the memberName and ID_CHOICE here.
				if (count($pollVotesData) > 2)
				{
					// Set the members.
					$members[] = $pollVotesData[$i][1];

					// Set the other poll data
					$pollVotes = array(
						'ID_POLL' => 0,
						'ID_MEMBER' => 0,
						'ID_CHOICE' => !empty($pollVotesData[$i][2]) ? (int) $pollVotesData[$i][2] : 0,
						'tempID' => $ID_POLL,
						'memberName' => trim($pollVotesData[$i][1])
					);

					$pollVotesBlock[] = addslashes_recursive($pollVotes);
				}
			}

			// Now time to insert the votes.
			if (count($pollVotesBlock) > $block_size)
			{
				$request = convert_query("
					SELECT ID_MEMBER, memberName
					FROM {$to_prefix}members
					WHERE memberName IN ('" . implode("','", $members) . "')");

				// Asssign the ID_MEMBER to the poll.
				while ($row = mysql_fetch_assoc($request))
				{
					foreach ($pollVotesBlock as $key => $avlue)
					{
						if (isset($pollVotesBlock[$key]['memberName']) && $pollVotesBlock[$key]['memberName'] == $row['memberName'])
						{
							// Assign ID_MEMBER
							$pollVotesBlock[$key]['ID_MEMBER'] = $row['ID_MEMBER'];

							// Now lets unset memberName since we don't need it any more
							unset($pollVotesBlock[$key]['memberName'], $pollVotesBlock[$key]['memberName']);
						}
					}
				}

				// Get the ID_POLL form the temp ID
				$request = convert_query("
					SELECT ID_POLL, tempID
					FROM {$to_prefix}polls
					WHERE tempID IN (" . implode(',', $pollIdsBlock) . ")");

				// Assign the ID_POLL
				while ($row = mysql_fetch_assoc($request))
				{
					foreach ($pollVotesBlock as $key => $value)
					{
						if (isset($pollVotesBlock[$key]['tempID']) && $pollVotesBlock[$key]['tempID'] == $row['tempID'])
						{
							$pollVotesBlock[$key]['ID_POLL'] = $row['ID_POLL'];
							//unset($pollVotesBlock[$key]['tempID'], $pollVotesBlock[$key]['tempID']);
							$pollVotesBlock[$key]['tempID'] = 0;
						}
					}
				}

				// Lets unset the remaining memberNames
				foreach ($pollVotesBlock as $key => $value)
				{
					if (isset($pollVotesBlock[$key]['memberName']))
						unset($pollVotesBlock[$key]['memberName'], $pollVotesBlock[$key]['memberName']);
				}

				doBlock('log_polls', $pollVotesBlock, true);

				// Some time has passed so do something
				pastTime($file_n);
			}
		}
		$dir->close();

		if (!empty($members))
		{
			$request = convert_query("
				SELECT ID_MEMBER, memberName
				FROM {$to_prefix}members
				WHERE memberName IN ('" . implode("','", $members) . "')");

			// Asssign the ID_MEMBER to the poll.
			while ($row = mysql_fetch_assoc($request))
			{
				foreach ($pollVotesBlock as $key => $avlue)
				{
					if (isset($pollVotesBlock[$key]['memberName']) && $pollVotesBlock[$key]['memberName'] == $row['memberName'])
					{
						// Assign ID_MEMBER
						$pollVotesBlock[$key]['ID_MEMBER'] = $row['ID_MEMBER'];

						// Now lets unset memberName since we don't need it any more
						unset($pollVotesBlock[$key]['memberName'], $pollVotesBlock[$key]['memberName']);
					}
				}
			}
		}

		if (!empty($pollIdsBlock))
		{
			// Get the ID_POLL form the temp ID
			$request = convert_query("
				SELECT ID_POLL, tempID
				FROM {$to_prefix}polls
				WHERE tempID IN (" . implode(',', $pollIdsBlock) . ")");

			// Assign the ID_POLL
			while ($row = mysql_fetch_assoc($request))
			{
				foreach ($pollVotesBlock as $key => $value)
				{
					if (isset($pollVotesBlock[$key]['tempID']) && $pollVotesBlock[$key]['tempID'] == $row['tempID'])
					{
						// Assign the ID_POLL
						$pollVotesBlock[$key]['ID_POLL'] = $row['ID_POLL'];

						// We don't need you any more so...BYE!!!
						//unset($pollVotesBlock[$key]['tempID'], $pollVotesBlock[$key]['tempID']);
						$pollVotesBlock[$key]['tempID'] = 0;
					}
				}
			}
		}

		// Lets unset the remaining memberNames
		foreach ($pollVotesBlock as $key => $value)
		{
			if (isset($pollVotesBlock[$key]['memberName']))
				unset($pollVotesBlock[$key]['memberName'], $pollVotesBlock[$key]['memberName']);
		}

		// Do the remaining block
		if (!empty($pollVotesBlock))
			doBlock('log_polls', $pollVotesBlock);

		pastTime(-1);

		// Remove the temp column from the table and put the primary key back.
		if ($_GET['substep'] <= -1)
		{
			$result = convert_query("
				SELECT id_poll
				FROM {$to_prefix}poll_choices
				GROUP BY id_poll
				HAVING COUNT(id_choice) > 1");

			while ($row = mysql_fetch_assoc($result))
				convert_query("
					DELETE FROM {$to_prefix}poll_choices
					WHERE id_poll = $row[id_poll]
					LIMIT 1");
			mysql_free_result($result);

			pastTime(-2);
		}
		if ($_GET['substep'] <= -2)
		{
			$knownKeys = array(
				'PRIMARY' => 'ADD PRIMARY KEY (ID_POLL, ID_CHOICE)',
			);
			alterTable('poll_choices', $knownKeys, '', '', true);

			pastTime(-3);
		}
		if ($_GET['substep'] <= -3)
		{
			$knownKeys = array(
				'poll' => 'ADD UNIQUE INDEX poll (ID_POLL, ID_TOPIC)',
			);
			alterTable('topics', $knownKeys, '', true);

			pastTime(-4);
		}
		if ($_GET['substep'] <= -4)
		{
			$knownColumns = array(
				'tempID' => 'DROP COLUMN tempID'
			);
			alterTable('topics', '', $knownColumns, '', false, true);

			pastTime(-5);
		}
		if ($_GET['substep'] <= -5)
		{
			$knownColumns = array(
				'tempID' => 'DROP COLUMN tempID',
			);
			alterTable('polls', '', $knownColumns, '', true, true);
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

	function doBlock($table, &$block, $force_ignore = false)
	{
		global $to_prefix;

		if (empty($block))
			return;

		// If converting mark as read data make the insert be ignore into
		if (!empty($force_ignore) || in_array($table, array('log_mark_read', 'log_boards', 'log_topics', 'log_polls')))
			$ignore = TRUE;

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
			{
				if (isset($block_names[$row['memberName']]))
					unset($block[$block_names[$row['memberName']]]);
			}
			mysql_free_result($request);

			if (empty($block))
				return;

			unset($block_names);
		}

		$insert_block = array();
		foreach ($block as $row)
			$insert_block[] = '\'' . implode('\', \'', $row) . '\'';

		convert_query("
			INSERT" . (!empty($ignore) ? ' IGNORE' : '') . " INTO {$to_prefix}$table
				(" . implode(', ', array_keys($block[0])) . ")
			VALUES (" . implode("),
				(", $insert_block) . ")");

		$block = array();
	}
}

?>