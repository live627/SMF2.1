<?php
/******************************************************************************
* yabb_to_smf.php                                                             *
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
	'name' => 'YaBB 2',
	'version' => 'SMF 2.0 Alpha',
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

		echo 'Converting membergroups...';

		$knownGroups = array();
		$extraGroups = array();
		$newbie = false;

		$groups = file($yabb['vardir'] . '/membergroups.txt');
		foreach ($groups as $i => $group)
		{
			$group = addslashes(trim($groups[$i]));

			if (preg_match('~^\$Group\{\'(Administrator|Global Moderator|Moderator)\'\} = "([^|]*)\|(\d*)\|([^|]*)\|([^|]*)~', $group, $match) != 0)
			{
				$match = addslashes_recrusive($match);
				$ID_GROUP = $match[1] == 'Administrator' ? 1 : ($match[1] == 'Global Moderator' ? 2 : 3);
				$knownGroups[] = "$ID_GROUP, SUBSTRING('$match[2]', 1, 80), SUBSTRING('$match[5]', 1, 20), 0, SUBSTRING('$match[3]#$match[4]', 1, 255)";
			}
			elseif (preg_match('~\$Post\{\'(\d+)\'\} = "([^|]*)\|(\d*)\|([^|]*)\|([^|]*)~', $group, $match) != 0)
			{
				$match = addslashes_recrusive($match);
				$extraGroups[] = "SUBSTRING('$match[2]', 1, 80), SUBSTRING('$match[5]', 1, 20), " . max(0, $match[3]) . ", SUBSTRING('$match[3]#$match[4]', 1, 255)";

				if ($match[3] < 1)
					$newbie = true;
			}
			elseif (preg_match('~\$NoPost\[(\d+)\] = "([^|]*)\|(\d*)\|([^|]*)\|([^|]*)~', $group, $match) != 0)
			{
				$match = addslashes_recrusive($match);
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
				'memberName' => substr(htmlspecialchars($name), 0, 80),
				'passwd' => strlen($data['password']) == 22 ? bin2hex(base64_decode($data['password'])) : md5($data['password']),
				'realName' => htmlspecialchars($data['realname']),
				'emailAddress' => htmlspecialchars($data['email']),
				'websiteTitle' => htmlspecialchars($data['webtitle']),
				'websiteUrl' => htmlspecialchars($data['weburl']),
				'signature' => str_replace(array('&amp;&amp;', '&amp;lt;', '&amp;gt;'), array('<br />', '&lt;'. '&gt;'), strtr($data['signature'], array('\'' => '&#039;'))),
				'posts' => (int) $data['postcount'],
				'ID_GROUP' => isset($groups[$data['position']]) ? $groups[$data['position']] : 0,
				'ICQ' => htmlspecialchars($data['icq']),
				'AIM' => substr(htmlspecialchars($data['aim']), 0, 16),
				'YIM' => substr(htmlspecialchars($data['yim']), 0, 32),
				'MSN' => htmlspecialchars($data['msn']),
				'gender' => $data['gender'] == 'Male' ? 1 : ($data['gender'] == 'Female' ? 2 : 0),
				'personalText' => htmlspecialchars($data['usertext']),
				'avatar' => $data['userpic'],
				'dateRegistered' => parse_time($data['regdate']),
				'location' => htmlspecialchars($data['location']),
				'birthdate' => $data['bday'] == '' || strtotime($data['bday']) == 0 ? '0001-01-01' : strftime('%Y-%m-%d', strtotime($data['bday'])),
				'hideEmail' => $data['hidemail'] == 'checked' ? '1' : '0',
				'lastLogin' => $data['lastonline'],
				'pm_email_notify' => empty($data['im_notify']) || trim($data['im_notify']) == '' ? '0' : '1',
				'karmaGood' => 0,
				'karmaBad' => 0,
			);

			// Make sure these columns have a value and don't exceed max width.
			foreach ($text_columns as $text_column => $max_size)
				$row[$text_column] = isset($row[$text_column]) ? substr($row[$text_column], 0, $max_size) : '';

			if ($row['birthdate'] == '0001-01-01' && parse_time($data['bday'], false) != 0)
				$row['birthdate'] = strftime('%Y-%m-%d', parse_time($data['bday'], false));

			if (file_exists($yabb['memberdir'] . '/' . substr($entry, 0, -4) . '.karma'))
			{
				$karma = (int) implode('', file($yabb['memberdir'] . '/' . substr($entry, 0, -4) . '.karma'));
				$row['karmaGood'] = $karma > 0 ? $karma : 0;
				$row['karmaBad'] = $karma < 0 ? -$karma : 0;
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
		}
	}

	function convertStep3()
	{
		global $to_prefix, $yabb;

		echo 'Converting settings...';

		$temp = file($yabb['vardir'] . '/reservecfg.txt');
		$settings = array(
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
			'signature_settings' => '1,' . (int) $yabb['MaxSigLen'] . ',5,0,1,0,0,0:',
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
				CHANGE COLUMN ID_PM ID_PM int(10) unsigned NOT NULL default 0,
				DROP PRIMARY KEY,
				ADD temp_toName tinytext");
		}

		echo 'Converting personal messages...';

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
				if (count($userData[$i]) < 2)
					continue;

				if (substr($userData[$i][3], -10) == '#nosmileys')
					$userData[$i][3] = substr($userData[$i][3], 0, -10);

				$row = array(
					'fromName' => substr(htmlspecialchars($userData[$i][0]), 0, 255),
					'subject' => substr($userData[$i][1], 0, 255),
					'msgtime' => $userData[$i][2],
					'body' => substr($userData[$i][3], 0, 65534),
					'ID_MEMBER_FROM' => 0,
					'deletedBySender' => 1,
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
				WHERE BINARY mem.memberName = pm.temp_toName
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
		global $to_prefix, $yabb;

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
				ADD tempID tinytext");
			convert_query("
				ALTER TABLE {$to_prefix}boards
				ADD tempID tinytext,
				ADD tempCatID tinytext");
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

		$cat_data = file($yabb['boardsdir'] . '/forum.master');
		$cat_order = array();
		$cats = array();
		$boards = array();
		foreach ($cat_data as $line)
		{
			if (preg_match('~^\$board\{\'(.+?)\'\} = "([^|]+)~', $line, $match) != 0)
				$boards[$match[1]] = $match[2];
			elseif (preg_match('~^\$catinfo\{\'(.+?)\'\} = "([^|]+?)\|([^|]*?)\|([^|]+?)";~', $line, $match) != 0)
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

				$cats[$match[1]]['name'] = $match[2];
				$cats[$match[1]]['groups'] = implode(',', $cat_groups);
				$cats[$match[1]]['canCollapse'] = !empty($match[4]);
			}
			elseif (preg_match('~^@categoryorder = qw\((.+?)\);~', $line, $match) != 0)
				$cat_order = array_flip(explode(' ', ' ', $match[1]));
		}

		$cat_rows = array();
		foreach ($cats as $tempID => $cat)
		{
			$row = array(
				'name' => substr($cat['name'], 0, 255),
				'catOrder' => @$cat_order[$tempID],
				'tempID' => $tempID,
			);
			$cat_rows[] = addslashes_recursive($row);
		}

		doBlock('categories', $cat_rows);

		$board_data = file($yabb['boardsdir'] . '/forum.control');
		$boardOrder = 1;
		$moderators = array();
		foreach ($board_data as $line)
		{
			list ($tempCatID, $tempID, , $description, $mods, , , , , $doCountPosts, , , $is_recycle) = explode('|', rtrim($line));
			// !!! is_recycle -> set recycle board?

			$row = array(
				'name' => substr($boards[$tempID], 0, 255),
				'description' => substr($description, 0, 255),
				'countPosts' => empty($doCountPosts),
				'boardOrder' => $boardOrder++,
				'memberGroups' => $cats[$tempCatID]['groups'],
				'tempID' => $tempID,
				'tempCatID' => $tempCatID,
				'membergroups' => '-1,0',
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

		convert_query("
			ALTER TABLE {$to_prefix}categories
			DROP COLUMN tempID");

		convert_query("
			ALTER TABLE {$to_prefix}boards
			DROP COLUMN tempCatID");
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
				$row['logTime'] = $parts[1] != '' ? (int) $parts[1] : (int) trim($parts[2]);
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

		if ($_GET['substep'] == 0 && !empty($_SESSION['purge']))
		{
			convert_query("
				TRUNCATE {$to_prefix}topics");
		}
		if ($_GET['substep'] == 0)
		{
			convert_query("
				ALTER TABLE {$to_prefix}topics
				CHANGE COLUMN ID_TOPIC ID_TOPIC mediumint(8) unsigned NOT NULL default 0,
				DROP PRIMARY KEY,
				ADD tempID int(10) unsigned NOT NULL default 0,
				DROP INDEX lastMessage,
				DROP INDEX firstMessage,
				DROP INDEX poll");
		}

		echo 'Converting topics (part 1)...';

		pastTime(0);

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
				$views = $views[2] - 1;

				$block[] = array(
					'tempID' => $tempID,
					'ID_BOARD' => (int) $ID_BOARD,
					'isSticky' => strpos($topicInfo[8], 's') !== false,
					'locked' => strpos($topicInfo[8], 'l') !== false,
					'numViews' => $views,
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
		global $to_prefix, $yabb;

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
		global $to_prefix, $yabb;

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
				if (!file_exists($yabb['datadir'] . '/' . $row['tempID'] . '.mail'))
					continue;

				$list = file($yabb['datadir'] . '/' . $row['tempID'] . '.mail');
				foreach ($list as $k => $v)
					list ($list[$k]) = explode('|', htmlspecialchars(addslashes(rtrim($v))));

				convert_query("
					INSERT INTO {$to_prefix}log_notify
						(ID_TOPIC, ID_MEMBER)
					SELECT $row[ID_TOPIC], ID_MEMBER
					FROM {$to_prefix}members
					WHERE memberName IN ('" . implode("', '", $list) . "')
					LIMIT " . count($list));
			}

			$_GET['substep'] += 150;
			if (mysql_num_rows($result) < 150)
				break;

			mysql_free_result($result);
		}
	}

	function convertStep10()
	{
		global $to_prefix, $yabb;

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

			if (isset($yabb['uploaddir']))
				convert_query("
					ALTER TABLE {$to_prefix}messages
					ADD COLUMN temp_filename tinytext NOT NULL default ''");
		}

		echo 'Converting posts (part 1 - this may take some time)...';

		$block = array();
		while (true)
		{
			$result = convert_query("
				SELECT ID_TOPIC, tempID, ID_BOARD
				FROM {$to_prefix}topics
				WHERE tempID != ID_TOPIC
				LIMIT $_GET[substep], 100");
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
						'ID_TOPIC' => $topic['ID_TOPIC'],
						'ID_BOARD' => $topic['ID_BOARD'],
						'subject' => substr($message[0], 0, 255),
						'posterName' => substr(htmlspecialchars($message[4] == 'Guest' ? $message[1] : $message[4]), 0, 255),
						'posterEmail' => substr(htmlspecialchars($message[2]), 0, 255),
						'posterTime' => $message[3],
						'icon' => substr($message[5], 0, 16),
						'posterIP' => substr($message[7], 0, 255),
						'body' => substr(preg_replace('~\[quote author=.+? link=.+?\]~i', '[quote]', $message[8]), 0, 65534),
						'smileysEnabled' => empty($message[9]),
						'modifiedTime' => parse_time($message[10], false),
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
		global $to_prefix, $yabb;

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
		global $to_prefix, $yabb;

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
		global $to_prefix, $yabb;

		echo 'Converting attachments (if the mod is installed)...';

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
				LIMIT $_GET[substep], 100");
			while ($row = mysql_fetch_assoc($result))
			{
				$size = filesize($yabb['uploaddir'] . '/' . $row['temp_filename']);
				$filename = getAttachmentFilename($row['temp_filename'], $ID_ATTACH);

				if (strlen($filename) <= 255 &&  copy($yabb['uploaddir'] . '/' . $row['temp_filename'], $attachmentUploadDir . '/' . $filename))
				{
					$setString .= "
						($ID_ATTACH, $size, 0, '" . addslashes($row['temp_filename']) . "', $row[ID_MSG]),";

					$ID_ATTACH++;
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
		global $to_prefix, $yabb;

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
				ALTER TABLE {$to_prefix}messages
				ADD UNIQUE INDEX ID_BOARD (ID_BOARD, ID_MSG)");
		}
	}

	function convertStep15()
	{
		global $to_prefix, $yabb;

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