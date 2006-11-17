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

$convert_data = array(
	'name' => 'YaBB 1 Gold',
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
		global $yabb;

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
				$yabb[$match[1]] = $match[2] == '^' ? addslashes($match[3]) : $match[3];
		}

		$paths = array('boarddir', 'boardsdir', 'datadir', 'memberdir', 'sourcedir', 'vardir', 'facesdir', 'upload_dir');
		foreach ($paths as $path)
		{
			if (isset($yabb[$path]))
				$yabb[$path] = fixRelativePath($yabb[$path], $_POST['path_from']);
		}
	}

	function convertStep1()
	{
		global $to_prefix, $yabb;

		echo 'Converting membergroups...';

		if ($_GET['substep'] == 0 && !empty($_SESSION['purge']))
		{
			convert_query("
				DELETE FROM {$to_prefix}permissions
				WHERE id_group > 8");
			convert_query("
				DELETE FROM {$to_prefix}membergroups
				WHERE id_group > 8");
		}

		$groups = file($yabb['vardir'] . '/membergroups.txt');
		foreach ($groups as $i => $group)
			$groups[$i] = addslashes(trim($group));

		convert_query("
			UPDATE {$to_prefix}membergroups
			SET group_name = '$groups[0]'
			WHERE id_group = 1
			LIMIT 1");

		convert_query("
			UPDATE {$to_prefix}membergroups
			SET group_name = '$groups[1]'
			WHERE id_group = 3
			LIMIT 1");

		convert_query("
			UPDATE {$to_prefix}membergroups
			SET group_name = '$groups[2]', min_posts = 0
			WHERE id_group = 4
			LIMIT 1");

		convert_query("
			UPDATE {$to_prefix}membergroups
			SET group_name = '$groups[3]', min_posts = " . (int) $yabb['JrPostNum'] . "
			WHERE id_group = 5
			LIMIT 1");

		convert_query("
			UPDATE {$to_prefix}membergroups
			SET group_name = '$groups[4]', min_posts = " . (int) $yabb['FullPostNum'] . "
			WHERE id_group = 6
			LIMIT 1");

		convert_query("
			UPDATE {$to_prefix}membergroups
			SET group_name = '$groups[5]', min_posts = " . (int) $yabb['SrPostNum'] . "
			WHERE id_group = 7
			LIMIT 1");

		convert_query("
			UPDATE {$to_prefix}membergroups
			SET group_name = '$groups[6]', min_posts = " . (int) $yabb['GodPostNum'] . "
			WHERE id_group = 8
			LIMIT 1");

		for ($i = 7; $i < count($groups); $i++)
		{
			if ($groups[$i] == '')
				continue;

			convert_query("
				INSERT INTO {$to_prefix}membergroups
					(group_name, online_color, min_posts, stars)
				VALUES (SUBSTRING('" . $groups[$i] . "', 1, 80), '', -1, ''");
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
				CHANGE COLUMN id_member id_member mediumint(8) unsigned NOT NULL default 0");
		}

		pastTime(0);

		$request = convert_query("
			SELECT id_group, group_name
			FROM {$to_prefix}membergroups
			WHERE id_group NOT IN (2, 3)");
		$groups = array('Administrator' => 1, 'Moderator' => 0);
		while ($row = mysql_fetch_assoc($request))
			$groups[$row['group_name']] = $row['id_group'];
		mysql_free_result($request);

		$file_n = 0;
		$dir = dir($yabb['memberdir']);
		$block = array();
		$text_columns = array(
			'member_name' => 80,
			'lngfile' => 255,
			'real_name' => 255,
			'buddy_list' => 255,
			'pm_ignore_list' => 255,
			'message_labels' => 65534,
			'passwd' => 64,
			'email_address' => 255,
			'personal_text' => 255,
			'website_title' => 255,
			'website_url' => 255,
			'location' => 255,
			'icq' => 255,
			'aim' => 16,
			'yim' => 32,
			'msn' => 255,
			'time_format' => 80,
			'signature' => 255,
			'avatar' => 255,
			'usertitle' => 255,
			'member_ip' => 255,
			'secret_question' => 255,
			'secret_answer' => 64,
			'validation_code' => 10,
			'additional_groups' => 255,
			'smiley_set' => 48,
			'password_salt' => 5,
		);
		while ($entry = $dir->read())
		{
			if ($_GET['substep'] < 0)
				break;
			if ($file_n++ < $_GET['substep'])
				continue;
			if (strrchr($entry, '.') != '.dat')
				continue;

			$userData = file($yabb['memberdir'] . '/' . $entry);
			foreach ($userData as $i => $v)
				$userData[$i] = rtrim($userData[$i]);
			if (count($userData) < 3)
				continue;

			$row = array(
				'member_name' => substr(htmlspecialchars(substr($entry, 0, -4)), 0, 80),
				'passwd' => md5($userData[0]),
				'real_name' => @$userData[1] == 'Guest' || @$userData[1] == '' ? $row['member_name'] : htmlspecialchars($userData[1]),
				'email_address' => htmlspecialchars(@$userData[2]),
				'website_title' => htmlspecialchars(@$userData[3]),
				'website_url' => htmlspecialchars(@$userData[4]),
				'signature' => str_replace(array('&amp;&amp;', '&amp;lt;'), array('<br />', '&lt;'), htmlspecialchars(@$userData[5], ENT_QUOTES)),
				'posts' => (int) @$userData[6],
				'id_group' => isset($groups[@$userData[7]]) ? $groups[@$userData[7]] : 0,
				'icq' => htmlspecialchars(@$userData[8]),
				'aim' => substr(htmlspecialchars(@$userData[9]), 0, 16),
				'yim' => substr(htmlspecialchars(@$userData[10]), 0, 32),
				'gender' => @$userData[11] == 'Male' ? 1 : (@$userData[11] == 'Female' ? 2 : 0),
				'personal_text' => htmlspecialchars(@$userData[12]),
				'avatar' => @$userData[13],
				'date_registered' => parse_time(@$userData[14]),
				'location' => htmlspecialchars(@$userData[15]),
				'birthdate' => @$userData[16] == '' || strtotime(@$userData[16]) == 0 ? '0001-01-01' : strftime('%Y-%m-%d', strtotime($userData[16])),
				'time_offset' => (float) @$userData[18],
				'hide_email' => @$userData[19] == 'checked' ? '1' : '0',
			);

			if ($row['birthdate'] == '0001-01-01' && parse_time(@$userData[16], false) != 0)
				$row['birthdate'] = strftime('%Y-%m-%d', parse_time(@$userData[16], false));

			// Make sure these columns have a value and don't exceed max width.
			foreach ($text_columns as $text_column => $max_size)
				$row[$text_column] = isset($row[$text_column]) ? substr($row[$text_column], 0, $max_size) : '';

			if (file_exists($yabb['memberdir'] . '/' . substr($entry, 0, -4) . '.karma'))
			{
				$karma = (int) implode('', file($yabb['memberdir'] . '/' . substr($entry, 0, -4) . '.karma'));
				$row['karma_good'] = $karma > 0 ? $karma : 0;
				$row['karma_bad'] = $karma < 0 ? -$karma : 0;
			}
			else
			{
				$row['karma_good'] = 0;
				$row['karma_bad'] = 0;
			}
			if (file_exists($yabb['memberdir'] . '/' . substr($entry, 0, -4) . '.imconfig'))
			{
				$imconfig = file($yabb['memberdir'] . '/' . substr($entry, 0, -4) . '.imconfig');
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
				ORDER BY id_member = 0, date_registered");
			pastTime(-2);
		}
		if ($_GET['substep'] >= -2)
		{
			convert_query("
				ALTER TABLE {$to_prefix}members
				CHANGE COLUMN id_member id_member mediumint(8) unsigned NOT NULL auto_increment PRIMARY KEY");

			pastTime(-3);
		}
		if ($_GET['substep'] >= -3)
		{
			convert_query("
				ALTER TABLE {$to_prefix}members
				ORDER BY id_member");
		}
	}

	function convertStep3()
	{
		global $to_prefix, $yabb;

		echo 'Converting settings...';

		$fp = @fopen($_POST['path_to'] . '/agreement.txt', 'wb');
		if ($fp)
		{
			fwrite($fp, strtr(implode('', file($yabb['vardir'] . '/agreement.txt')), array("\r" => '')));
			fclose($fp);
		}

		$settings = array();
		$settings['news'] = addslashes(strtr(implode('', file($yabb['vardir'] . '/news.txt')), array("\r" => '')));
		$settings['maxdays'] = (int) implode('', file($yabb['vardir'] . '/oldestmes.txt'));
		$settings['cookieTime'] = !empty($yabb['Cookie_Length']) ? (int) $yabb['Cookie_Length'] : 60;
		$settings['requireAgreement'] = !empty($yabb['RegAgree']) ? 1 : 0;
		$settings['registration_method'] = !empty($yabb['emailpassword']) ? 1 : 0;
		$settings['send_validation_onChange'] = !empty($yabb['emailnewpass']) ? 1 : 0;
		$settings['send_welcomeEmail'] = !empty($yabb['emailwelcome']) ? 1 : 0;
		$settings['mail_type'] = empty($yabb['mailtype']) ? 0 : 1;
		$settings['smtp_host'] = isset($yabb['smtp_server']) ? $yabb['smtp_server'] : '';
		$settings['time_offset'] = (int) $yabb['timeoffset'];
		$settings['defaultMaxMembers'] = !empty($yabb['MembersPerPage']) ? (int) $yabb['MembersPerPage'] : 20;
		$settings['defaultMaxTopics'] = !empty($yabb['maxdisplay']) ? (int) $yabb['maxdisplay'] : 20;
		$settings['defaultMaxMessages'] = !empty($yabb['maxmessagedisplay']) ? (int) $yabb['maxmessagedisplay'] : 15;
		$settings['max_messageLength'] = !empty($yabb['MaxMessLen']) ? (int) $yabb['MaxMessLen'] : 10000;
		$settings['signature_settings'] = '1,' . (int) $yabb['MaxSigLen'] . ',5,0,1,0,0,0:';
		$settings['spamWaitTime'] = (int) $yabb['timeout'];
		$settings['avatar_max_width_external'] = (int) $yabb['userpic_width'];
		$settings['avatar_max_height_external'] = (int) $yabb['userpic_height'];
		$settings['avatar_max_width_upload'] = (int) $yabb['userpic_width'];
		$settings['avatar_max_height_upload'] = (int) $yabb['userpic_height'];

		$temp = file($yabb['vardir'] . '/reservecfg.txt');
		$settings['reserveWord'] = trim($temp[0]) == 'checked' ? '1' : '0';
		$settings['reserveCase'] = trim($temp[1]) == 'checked' ? '1' : '0';
		$settings['reserveUser'] = trim($temp[2]) == 'checked' ? '1' : '0';
		$settings['reserveName'] = trim($temp[3]) == 'checked' ? '1' : '0';
		$settings['reserveNames'] = addslashes(strtr(implode('', file($yabb['vardir'] . '/reserve.txt')), array("\r" => '')));

		$vulgar = array();
		$proper = array();
		$temp = file($yabb['vardir'] . '/censor.txt');
		foreach ($temp as $word)
		{
			if (trim($word) == '')
				continue;

			list ($word, $word2) = explode('=', $word);
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
				CHANGE COLUMN id_pm id_pm int(10) unsigned NOT NULL default 0,
				DROP PRIMARY KEY,
				ADD temp_to_name tinytext");
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

				if (substr($userData[$i][3], -10) == '#nosmileys')
					$userData[$i][3] = substr($userData[$i][3], 0, -10);

				$row = array(
					'from_name' => substr(htmlspecialchars($userData[$i][0]), 0, 255),
					'subject' => substr($userData[$i][1], 0, 255),
					'msgtime' => parse_time($userData[$i][2]),
					'body' => substr($userData[$i][3], 0, 65534),
					'id_member_from' => 0,
					'deleted_by_sender' => 1,
					'temp_to_name' => htmlspecialchars(substr($entry, 0, -4)),
				);

				$names[strtolower(addslashes($row['from_name']))][] = &$row['id_member_from'];

				$block[] = addslashes_recursive($row);
			}

			if (count($block) > 100)
			{
				$result = convert_query("
					SELECT id_member, member_name
					FROM {$to_prefix}members
					WHERE member_name IN ('" . implode("', '", array_keys($names)) . "')
					LIMIT " . count($names));
				while ($row = mysql_fetch_assoc($result))
				{
					foreach ($names[strtolower(addslashes($row['member_name']))] as $k => $v)
						$names[strtolower(addslashes($row['member_name']))][$k] = $row['id_member'];
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
				SELECT id_member, member_name
				FROM {$to_prefix}members
				WHERE member_name IN ('" . implode("', '", array_keys($names)) . "')
				LIMIT " . count($names));
			while ($row = mysql_fetch_assoc($result))
			{
				foreach ($names[strtolower(addslashes($row['member_name']))] as $k => $v)
					$names[strtolower(addslashes($row['member_name']))][$k] = $row['id_member'];
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
				ORDER BY id_pm = 0, msgtime");

			pastTime(-2);
		}
		if ($_GET['substep'] >= -2)
		{
			convert_query("
				ALTER TABLE {$to_prefix}personal_messages
				CHANGE COLUMN id_pm id_pm int(10) unsigned NOT NULL auto_increment PRIMARY KEY");

			pastTime(-3);
		}
		if ($_GET['substep'] >= -3)
		{
			convert_query("
				INSERT INTO {$to_prefix}pm_recipients
					(id_pm, id_member, labels)
				SELECT pm.id_pm, mem.id_member, '' AS labels
				FROM ({$to_prefix}personal_messages AS pm, {$to_prefix}members AS mem)
				WHERE mem.member_name = pm.temp_to_name
					AND pm.temp_to_name != ''");

			pastTime(-4);
		}
		if ($_GET['substep'] >= -4)
		{
			convert_query("
				ALTER TABLE {$to_prefix}personal_messages
				DROP temp_to_name");

			pastTime(-5);
		}
		if ($_GET['substep'] >= -5)
		{
			convert_query("
				ALTER TABLE {$to_prefix}personal_messages
				ORDER BY id_pm");
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
			SELECT id_group, group_name
			FROM {$to_prefix}membergroups
			WHERE id_group NOT IN (2, 3)");
		$groups = array('Administrator' => 1, 'Moderator' => 0);
		while ($row = mysql_fetch_assoc($request))
			$groups[$row['group_name']] = $row['id_group'];
		mysql_free_result($request);

		$cats = file($yabb['vardir'] . '/cat.txt');
		$board_order = 1;
		$cat_rows = array();
		$board_rows = array();
		foreach ($cats as $i => $cat)
		{
			if (!file_exists($yabb['boardsdir'] . '/' . trim($cat) . '.cat'))
				continue;

			$data = file($yabb['boardsdir'] . '/' . trim($cat) . '.cat');
			foreach ($data as $i => $v)
				$data[$i] = rtrim($data[$i]);

			$row = array();
			$row['name'] = $data[0];
			$row['cat_order'] = $i + 1;
			$row['tempID'] = trim($cat);
			$cat_rows[] = addslashes_recursive($row);

			$data[1] = explode(',', $data[1]);
			if ($data[1][0] == '')
				$cat_groups = array_merge($groups, array(2, 0, -1));
			else
			{
				$cat_groups = array(2);
				foreach ($data[1] as $group)
				{
					if (isset($groups[trim($group)]))
						$cat_groups[] = $groups[trim($group)];
				}
			}
			$cat_groups = implode(',', $cat_groups);

			for ($j = 2; $j < count($data); $j++)
			{
				if ($data[$j] == '')
					continue;

				$row2 = array(
					'tempID' => $data[$j],
					'tempCatID' => trim($cat),
					'member_groups' => $cat_groups,
					'board_order' => $board_order++,
				);

				$board_rows[addslashes($data[$j])] = $row2;
			}
		}

		doBlock('categories', $cat_rows);

		$moderators = array();
		foreach ($board_rows as $boardid => $v)
		{
			if (!file_exists($yabb['boardsdir'] . '/' . stripslashes($boardid) . '.dat') || !file_exists($yabb['boardsdir'] . '/' . stripslashes($boardid) . '.txt'))
				continue;

			$data = file($yabb['boardsdir'] . '/' . stripslashes($boardid) . '.dat');
			$board_rows[$boardid]['member_groups'] = '-1,0';
			$board_rows[$boardid]['name'] = substr(trim($data[0]), 0, 255);
			$board_rows[$boardid]['description'] = substr(trim($data[1]), 0, 65534);

			$moderators[$boardid] = explode('|', trim(@$data[2]));
		}

		$board_rows = addslashes_recursive(array_values($board_rows));
		doBlock('boards', $board_rows);

		$result = convert_query("
			SELECT id_cat, tempID
			FROM {$to_prefix}categories
			WHERE tempID != ''");
		while ($row = mysql_fetch_assoc($result))
		{
			convert_query("
				UPDATE {$to_prefix}boards
				SET id_cat = $row[id_cat]
				WHERE tempCatID = '$row[tempID]'");
		}
		mysql_free_result($result);

		foreach ($moderators as $boardid => $names)
		{
			$result = convert_query("
				SELECT id_board
				FROM {$to_prefix}boards
				WHERE tempID = '$boardid'
				LIMIT 1");
			list ($id_board) = mysql_fetch_row($result);
			mysql_free_result($result);

			convert_query("
				INSERT INTO {$to_prefix}moderators
					(id_board, id_member)
				SELECT $id_board, id_member
				FROM {$to_prefix}members
				WHERE member_name IN ('" . implode("', '", addslashes_recursive($names)) . "')
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
			SELECT id_board, tempID
			FROM {$to_prefix}boards");
		$boards = array();
		while ($row = mysql_fetch_assoc($result))
			$boards[$row['tempID']] = $row['id_board'];
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
				SELECT id_member
				FROM {$to_prefix}members
				WHERE member_name = '" . substr($entry, 0, -4) . "'
				LIMIT 1");
			list ($id_member) = mysql_fetch_row($result);
			mysql_free_result($result);

			$logData = file($yabb['memberdir'] . '/' . $entry);
			foreach ($logData as $log)
			{
				$parts = array_pad(explode('|', $log), 3, '');
				if (trim($parts[0]) == '')
					continue;

				$row = array();
				$row['log_time'] = $parts[1] != '' ? (int) $parts[1] : (int) trim($parts[2]);
				$row['id_member'] = $id_member;

				if (is_numeric(trim($parts[0])) && trim($parts[0]) > 10000)
				{
					$row['tempID'] = trim($parts[0]);
					$topics_block[] = $row;
				}
				else
				{
					if (substr(trim($parts[0]), -6) == '--mark' && isset($boards[substr(trim($parts[0]), 0, -6)]))
					{
						$row['id_board'] = $boards[substr(trim($parts[0]), 0, -6)];
						$mark_read_block[] = $row;
					}
					elseif (isset($boards[trim($parts[0])]))
					{
						$row['id_board'] = $boards[trim($parts[0])];
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
				CHANGE COLUMN id_topic id_topic mediumint(8) unsigned NOT NULL default 0,
				DROP PRIMARY KEY,
				ADD tempID int(10) unsigned NOT NULL default 0,
				DROP INDEX lastMessage,
				DROP INDEX firstMessage,
				DROP INDEX poll");
		}

		echo 'Converting topics (part 1)...';

		pastTime(0);

		$stickies = array();
		if (file_exists($yabb['boardsdir'] . '/sticky.stk'))
		{
			$stickyData = file($yabb['boardsdir'] . '/sticky.stk');

			foreach ($stickyData as $line)
			{
				if (trim($line) != '')
					$stickies[] = (int) trim($line);
			}
		}

		$result = convert_query("
			SELECT id_board, tempID
			FROM {$to_prefix}boards
			WHERE tempID != ''");
		$boards = array();
		while ($row = mysql_fetch_assoc($result))
			$boards[$row['tempID']] = $row['id_board'];
		mysql_free_result($result);

		$data_n = 0;
		$block = array();
		foreach ($boards as $boardname => $id_board)
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

				$block[] = array(
					'tempID' => $tempID,
					'id_board' => (int) $id_board,
					'is_sticky' => (int) in_array($tempID, $stickies),
					'locked' => (int) $topicInfo[8],
					'num_views' => (int) @implode('', @file($yabb['datadir'] . '/' . $tempID . '.data')),
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
				SET tempID = id_topic
				WHERE tempID = 0");

			pastTime(-2);
		}
		if ($_GET['substep'] >= -2)
		{
			convert_query("
				ALTER TABLE {$to_prefix}topics
				ORDER BY id_topic = 0, tempID");

			pastTime(-3);
		}
		if ($_GET['substep'] >= -3)
		{
			convert_query("
				ALTER TABLE {$to_prefix}topics
				CHANGE COLUMN id_topic id_topic mediumint(8) unsigned NOT NULL auto_increment PRIMARY KEY");

			pastTime(-4);
		}
		if ($_GET['substep'] >= -4)
		{
			convert_query("
				ALTER TABLE {$to_prefix}topics
				ORDER BY id_topic");
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
				SELECT id_topic, tempID
				FROM {$to_prefix}topics
				WHERE tempID != id_topic
				LIMIT $_GET[substep], 150");
			while ($row = mysql_fetch_assoc($result))
			{
				convert_query("
					UPDATE {$to_prefix}log_topics
					SET id_topic = $row[id_topic]
					WHERE tempID = $row[tempID]");
			}

			$_GET['substep'] += 150;
			if (mysql_num_rows($result) < 150)
				break;

			mysql_free_result($result);
		}

		convert_query("
			DELETE FROM {$to_prefix}log_topics
			WHERE id_topic = 0 OR id_member = 0");

		convert_query("
			ALTER IGNORE TABLE {$to_prefix}log_topics
			DROP COLUMN tempID,
			ADD PRIMARY KEY (id_topic, id_member)");
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
				SELECT id_topic, tempID
				FROM {$to_prefix}topics
				WHERE tempID != id_topic
				LIMIT $_GET[substep], 150");
			while ($row = mysql_fetch_assoc($result))
			{
				if (!file_exists($yabb['datadir'] . '/' . $row['tempID'] . '.mail'))
					continue;

				$list = file($yabb['datadir'] . '/' . $row['tempID'] . '.mail');
				foreach ($list as $k => $v)
					$list[$k] = addslashes(htmlspecialchars(rtrim($v)));

				convert_query("
					INSERT INTO {$to_prefix}log_notify
						(id_topic, id_member)
					SELECT $row[id_topic], id_member
					FROM {$to_prefix}members
					WHERE email_address IN ('" . implode("', '", $list) . "')");
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
				CHANGE COLUMN id_msg id_msg int(10) unsigned NOT NULL default 0,
				DROP PRIMARY KEY,
				DROP INDEX topic,
				DROP INDEX id_board");

			if (isset($yabb['upload_dir']))
				convert_query("
					ALTER TABLE {$to_prefix}messages
					ADD COLUMN temp_filename tinytext NOT NULL default ''");
		}

		echo 'Converting posts (part 1 - this may take some time)...';

		$block = array();
		while (true)
		{
			$result = convert_query("
				SELECT id_topic, tempID, id_board
				FROM {$to_prefix}topics
				WHERE tempID != id_topic
				LIMIT $_GET[substep], 100");
			while ($topic = mysql_fetch_assoc($result))
			{
				$messages = file($yabb['datadir'] . '/' . $topic['tempID'] . '.txt');
				if (empty($messages))
				{
					convert_query("
						DELETE FROM {$to_prefix}topics
						WHERE id_topic = $topic[id_topic]
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
						'id_topic' => $topic['id_topic'],
						'id_board' => $topic['id_board'],
						'subject' => substr($message[0], 0, 255),
						'poster_name' => substr(htmlspecialchars($message[4] == 'Guest' ? $message[1] : $message[4]), 0, 255),
						'poster_email' => substr(htmlspecialchars($message[2]), 0, 255),
						'poster_time' => parse_time($message[3]),
						'icon' => substr($message[5], 0, 16),
						'poster_ip' => substr($message[7], 0, 255),
						'body' => substr(preg_replace('~\[quote author=.+? link=.+?\]~i', '[quote]', $message[8]), 0, 65534),
						'smileys_enabled' => empty($message[9]),
						'modified_time' => parse_time($message[10], false),
						'modified_name' => substr($message[11], 0, 255),
					);

					if (isset($yabb['upload_dir']) && isset($message[13]) && file_exists($yabb['upload_dir'] . '/' . $message[13]))
						$row['temp_filename'] = $message[13];
					elseif (isset($yabb['upload_dir']))
						$row['temp_filename'] = '';

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
				ORDER BY poster_time");
		}

		echo 'Converting posts (part 2)...';

		$request = convert_query("
			SELECT @msg := IFNULL(MAX(id_msg), 0)
			FROM {$to_prefix}messages");
		mysql_free_result($request);

		while (true)
		{
			pastTime($_GET['substep']);

			mysql_query("
				UPDATE {$to_prefix}messages
				SET id_msg = (@msg := @msg + 1)
				WHERE id_msg = 0
				LIMIT 150");

			$_GET['substep'] += 150;
			if (mysql_affected_rows() < 150)
				break;
		}

		convert_query("
			ALTER TABLE {$to_prefix}messages
			CHANGE COLUMN id_msg id_msg int(10) unsigned NOT NULL auto_increment PRIMARY KEY");
	}

	function convertStep12()
	{
		global $to_prefix, $yabb;

		echo 'Converting posts (part 3)...';

		while (true)
		{
			pastTime($_GET['substep']);

			$result = convert_query("
				SELECT m.id_msg, mem.id_member
				FROM ({$to_prefix}messages AS m, {$to_prefix}members AS mem)
				WHERE m.poster_name = mem.member_name
					AND m.id_member = 0
				LIMIT 150");
			while ($row = mysql_fetch_assoc($result))
			{
				convert_query("
					UPDATE {$to_prefix}messages
					SET id_member = $row[id_member]
					WHERE id_msg = $row[id_msg]
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

		if (!isset($yabb['upload_dir']))
			return;

		$result = convert_query("
			SELECT value
			FROM {$to_prefix}settings
			WHERE variable = 'attachmentUploadDir'
			LIMIT 1");
		list ($attachmentUploadDir) = mysql_fetch_row($result);
		mysql_free_result($result);

		// Danger, Will Robinson!
		if ($yabb['upload_dir'] == $attachmentUploadDir)
			return;

		$result = convert_query("
			SELECT MAX(id_attach)
			FROM {$to_prefix}attachments");
		list ($id_attach) = mysql_fetch_row($result);
		mysql_free_result($result);

		$id_attach++;

		while (true)
		{
			pastTime($_GET['substep']);

			$setString = '';

			$result = convert_query("
				SELECT id_msg, temp_filename
				FROM {$to_prefix}messages
				WHERE temp_filename != ''
				LIMIT $_GET[substep], 100");
			while ($row = mysql_fetch_assoc($result))
			{
				$size = filesize($yabb['upload_dir'] . '/' . $row['temp_filename']);
				$filename = getAttachmentFilename($row['temp_filename'], $id_attach);

				if (strlen($filename) <= 255 && copy($yabb['upload_dir'] . '/' . $row['temp_filename'], $attachmentUploadDir . '/' . $filename))
				{
					$setString .= "
						($id_attach, $size, 0, '" . addslashes($row['temp_filename']) . "', $row[id_msg]),";

					$id_attach++;
				}
			}

			if ($setString != '')
				convert_query("
					INSERT INTO {$to_prefix}attachments
						(id_attach, size, downloads, filename, id_msg)
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
				ADD UNIQUE INDEX topic (id_topic, id_msg)");

			pastTime(2);
		}
		if ($_GET['substep'] <= 2)
		{
			convert_query("
				ALTER TABLE {$to_prefix}topics
				ADD UNIQUE INDEX poll (id_poll, id_topic)");

			pastTime(3);
		}
		if ($_GET['substep'] <= 3)
		{
			convert_query("
				ALTER TABLE {$to_prefix}messages
				ADD UNIQUE INDEX id_board (id_board, id_msg)");
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
				SELECT t.id_topic, MIN(m.id_msg) AS id_first_msg, MAX(m.id_msg) AS id_last_msg
				FROM ({$to_prefix}topics AS t, {$to_prefix}messages AS m)
				WHERE m.id_topic = t.id_topic
				GROUP BY t.id_topic
				LIMIT $_GET[substep], 150");
			while ($row = mysql_fetch_assoc($result))
			{
				$result2 = convert_query("
					SELECT id_member
					FROM {$to_prefix}messages
					WHERE id_msg = $row[id_last_msg]
					LIMIT 1");
				list ($row['id_member_updated']) = mysql_fetch_row($result2);
				mysql_free_result($result2);

				$result2 = convert_query("
					SELECT id_member
					FROM {$to_prefix}messages
					WHERE id_msg = $row[id_first_msg]
					LIMIT 1");
				list ($row['id_member_started']) = mysql_fetch_row($result2);
				mysql_free_result($result2);

				convert_query("
					UPDATE {$to_prefix}topics
					SET id_first_msg = '$row[id_first_msg]', id_last_msg = '$row[id_last_msg]',
						id_member_started = '$row[id_member_started]', id_member_updated = '$row[id_member_updated]'
					WHERE id_topic = $row[id_topic]
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
				ADD UNIQUE INDEX lastMessage (id_last_msg, id_board)");

			pastTime(-2);
		}
		if ($_GET['substep'] > -2)
		{
			convert_query("
				ALTER TABLE {$to_prefix}topics
				ADD UNIQUE INDEX firstMessage (id_first_msg, id_board)");
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
				$block_names[$row['member_name']] = $i;

			$request = convert_query("
				SELECT member_name
				FROM {$to_prefix}members
				WHERE member_name IN ('" . implode("', '", array_keys($block_names)) . "')
				LIMIT " . count($block_names));
			while ($row = mysql_fetch_assoc($request))
				unset($block[$block_names[$row['member_name']]]);
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