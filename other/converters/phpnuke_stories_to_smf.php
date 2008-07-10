<?php

$convert_data = array(
	'name' => 'PHP Nuke Stories 7.9',
	'version' => 'SMF 2.0',
	'settings' => array("/config.php", "/includes/constants.php"),
	'defines' => array('IN_PHPBB'),
	'parameters' => array(
	),
	'from_prefix' => '`$dbname`.{$prefix}_',
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
	$command_line = false;

	function load_converter_settings()
	{
		global $convert_data, $from_prefix, $dbname, $prefix;

		if (isset($_SESSION['convert_parameters']['db_purge']))
			$_SESSION['purge'] = !empty($_SESSION['convert_parameters']['db_purge']);

		if (!isset($_POST['path_from']) || (!file_exists($_POST['path_from'] . '/Paths.pl') && !file_exists($_POST['path_from'] . '/Variables/Paths.pl')))
			return;

		foreach ($convert_data['settings'] AS $file)
			require ($_POST['path_from'] . $file);
				
		$convert_data['from_prefix'] = "`$dbname`.{$prefix}_";
		$from_prefix = "`$dbname`.{$prefix}_";
	}

	function saveConverterSettings($var_name, $value = array())
	{
		global $to_prefix;

		$insert_value = addslashes(serialize($value));

		$request = convert_query("
			SELECT value
			FROM {$to_prefix}settings
			WHERE variable = 'convert_{$var_name}'");

		convert_query("
			REPLACE INTO {$to_prefix}convert_settings
				(variable, value)
			VALUES ('convert_{$var_name}', '{$insert_value}')");
	}

	function fetchConverterSettings($var_name)
	{
		global $to_prefix;

		$request = convert_query("
			SELECT value
			FROM {$to_prefix}convert_settings
			WHERE variable = 'convert_{$var_name}'");

		if (convert_num_rows($request) < 1)
			return FALSE;

		list($value) = convert_fetch_row($request);
		return unserialize(stripslashes($value));
	}

	function convertStep1()
	{
		global $to_prefix, $sets;
		echo 'Creating the new category...';

		convert_query("
			INSERT IGNORE INTO {$to_prefix}categories
				(catOrder, name, canCollapse)
			VALUES ('100', 'PHP-Nuke Stories' , '1')");
		$sets['cat_id'] = convert_insert_id();

		saveConverterSettings('cat_id', $sets['cat_id']);
	}

	function convertStep2()
	{
		global $to_prefix, $sets;
		echo 'Getting our Max Board, Topic and Message IDs ...';

		$sets['max_id_msg'] = fetchConverterSettings('max_id_msg');
		$sets['max_id_topic'] = fetchConverterSettings('max_id_topic');
		$sets['max_id_board'] = fetchConverterSettings('max_id_board');
		
		if (empty($sets['max_id_msg']))
		{
			// Get the current max ids.
			$result = convert_query("
				SELECT MAX(ID_MSG)
				FROM {$to_prefix}messages");
			list($sets['max_id_msg']) = convert_fetch_row($result);
			saveConverterSettings('max_id_msg', $sets['max_id_msg']);
		}

		if (empty($sets['max_id_topic']))
		{
			// Get the current max ids.
			$result = convert_query("
				SELECT MAX(ID_TOPIC)
				FROM {$to_prefix}topics");
			list($sets['max_id_topic']) = convert_fetch_row($result);
			saveConverterSettings('max_id_topic', $sets['max_id_topic']);
		}

		if (empty($sets['max_id_board']))
		{
			// Get the current max ids.
			$result = convert_query("
				SELECT MAX(ID_BOARD)
				FROM {$to_prefix}boards");
			list($sets['max_id_board']) = convert_fetch_row($result);
			saveConverterSettings('max_id_board', $sets['max_id_board']);
		}
	}

	function convertStep3()
	{
		global $to_prefix, $sets;
		echo 'Creating the default Board ...';

		$sets['board_default_id'] = fetchConverterSettings('board_default_id');
		$sets['cat_id'] = fetchConverterSettings('cat_id');

		if (empty($sets['board_default_id']))
		{
			convert_query("
				INSERT IGNORE INTO {$to_prefix}boards
					(ID_CAT, boardOrder, name, description)
				VALUES ({$sets['cat_id']}, '0', 'General Stories' , '')");
			$sets['board_default_id'] = convert_insert_id();
			saveConverterSettings('board_default_id', $sets['board_default_id']);
		}
	}

	function convertStep4()
	{
		global $to_prefix, $from_prefix, $sets;
		echo 'Converting Story Categories into Boards ...';

		$sets['cat_id'] = fetchConverterSettings('cat_id');
		$sets['max_id_board'] = fetchConverterSettings('max_id_board');

		// Get all the categories, hopefully there isn't to many.
		$request = convert_query("
			SELECT catid, title
			FROM {$from_prefix}stories_cat");

		$story_boards = array();
		$boardOrder = $sets['max_id_board'] + 1;
		while ($row = convert_fetch_assoc($request))
		{
			++$boardOrder;

			convert_query("
				INSERT IGNORE INTO {$to_prefix}boards
				(ID_CAT, boardOrder, name, description)
				VALUES ('{$sets['cat_id']}', '{$boardOrder}', '" . htmlspecialchars($row['title']) . "' , '')");
			$sets['story_boards'][$row['catid']] = convert_insert_id();
		}

		saveConverterSettings('story_boards', $sets['story_boards']);
	}

	function convertStep5()
	{
		global $to_prefix, $from_prefix, $sets;
		echo 'Converting Stories as posts/messages...';

		$bundle = 250;
		$sets['max_id_msg'] = fetchConverterSettings('max_id_msg');
		$sets['max_id_topic'] = fetchConverterSettings('max_id_topic');
		$sets['board_default_id'] = fetchConverterSettings('board_default_id');
		$sets['max_id_board'] = fetchConverterSettings('max_id_board');
		$sets['story_boards'] = fetchConverterSettings('story_boards');
		$sets['id_messages'] = fetchConverterSettings('id_messages');

		if (!isset($_GET['start']))
			$_GET['start'] = 0;

		while ($_GET['start'] >= 0)
		{
			pastTime($_GET['start']);

			if (empty($sets['id_messages']))
				$sets['id_messages'] = array();

			$result = convert_query("
				SELECT
					s.sid AS id_topic_old, s.catid AS id_board_old, s.aid AS poster_name, s.title AS subject, s.time,
					CONCAT(s.hometext, '<br />', s.bodytext) AS body, u.user_id AS id_member, u.user_email AS email_address, last_ip AS member_ip
				FROM {$from_prefix}stories AS s
					INNER JOIN {$from_prefix}users AS u ON (s.aid = u.username)
				GROUP BY sid
				LIMIT $_REQUEST[start], 250");

			while ($row = convert_fetch_assoc($result))
			{
				$row['poster_time'] = @strtotime($row['time']);
				$row['id_board'] = !empty($row['id_board_old']) ? $sets['story_boards'][$row['id_board_old']] : $sets['board_default_id'];
				$row['body'] = addslashes($row['body']);
				$row['subject'] = addslashes($row['subject']);
				$row['id_topic'] = 0;

				convert_query("
					INSERT IGNORE INTO {$to_prefix}messages
							(ID_TOPIC, ID_BOARD, posterTime, subject, posterName, ID_MEMBER, posterEmail, posterIP, body)
					VALUES ({$row['id_topic']}, {$row['id_board']}, {$row['poster_time']},'{$row['subject']}', '{$row['poster_name']}', '{$row['id_member']}', '{$row['email_address']}', '{$row['member_ip']}', '{$row['body']}')
					");
				$sets['id_messages'][$row['id_topic_old']] = convert_insert_id();
			}

			saveConverterSettings('id_messages', $sets['id_messages']);

			$_REQUEST['start'] += 250;
			if (convert_num_rows($result) < 250)
				break;

			convert_free_result($result);
		}

		$_REQUEST['start'] = 0;
	}

	function convertStep6()
	{
		global $to_prefix, $from_prefix, $sets;
		echo 'Converting Stories as topics ...';

		$bundle = 250;
		$sets['max_id_msg'] = fetchConverterSettings('max_id_msg');
		$sets['max_id_topic'] = fetchConverterSettings('max_id_topic');
		$sets['board_default_id'] = fetchConverterSettings('board_default_id');
		$sets['max_id_board'] = fetchConverterSettings('max_id_board');
		$sets['story_boards'] = fetchConverterSettings('story_boards');
		$sets['id_messages'] = fetchConverterSettings('id_messages');

		$sets['id_topics'] = fetchConverterSettings('id_topics');
		if (empty($sets['id_topics']))
			$sets['id_topics'] = array();

		if (!isset($_GET['start']))
			$_GET['start'] = 0;

		while ($_GET['start'] >= 0)
		{
			pastTime($_GET['start']);

			$result = convert_query("
				SELECT
					s.sid AS id_topic, s.catid AS id_board_old, s.aid AS poster_name, s.title AS subject, s.time,
					CONCAT(s.hometext, '<br />', s.bodytext) AS body, u.user_id AS id_member, u.user_email AS email_address, last_ip AS member_ip
				FROM {$from_prefix}stories AS s
					INNER JOIN {$from_prefix}users AS u ON (s.aid = username)
				GROUP BY sid
				LIMIT $_REQUEST[start], 250");

			while ($row = convert_fetch_assoc($result))
			{
				$row['poster_time'] = @strtotime($row['time']);
				$row['id_board'] = !empty($row['id_board_old']) ? $sets['story_boards'][$row['id_board_old']] : $sets['board_default_id'];
				$row['id_msg'] = $sets['id_messages'][$row['id_topic']];

				convert_query("
					INSERT IGNORE INTO {$to_prefix}topics
							(ID_BOARD, ID_FIRST_MSG, ID_LAST_MSG, ID_MEMBER_STARTED)
					VALUES ({$row['id_board']}, {$row['id_msg']}, {$row['id_msg']}, {$row['id_member']})
					");
				$sets['id_topics'][$row['id_topic']] = convert_insert_id();
			}

			$_REQUEST['start'] += 250;
			if (convert_num_rows($result) < 250)
				break;

			convert_free_result($result);
		}
		$_REQUEST['start'] = 0;
	}


	function convertStep7()
	{
		global $to_prefix, $from_prefix, $sets;
		echo 'Correcting our messages/posts to have a valid id_topic ...';

		$request = convert_query("
			SELECT
				m.ID_MSG AS m_id_msg, m.ID_TOPIC AS m_id_topic, t.ID_FIRST_MSG AS t_id_msg, t.ID_TOPIC AS t_id_topic
			FROM {$to_prefix}topics AS t
				INNER JOIN {$to_prefix}messages AS m ON (t.ID_FIRST_MSG = m.ID_MSG)
			WHERE m.ID_TOPIC = 0");

		while ($row = convert_fetch_assoc($request))
		{
			convert_query("
				UPDATE {$to_prefix}messages
				SET ID_TOPIC = {$row['t_id_topic']}
				WHERE ID_MSG = {$row['m_id_msg']}");
		}
	}

	function convertStep8()
	{
		global $to_prefix, $sets;
convert_query("UPDATE `SMF_conversions-phpnuke79-SMF1`.`smf_members` SET `passwd` = 'password' WHERE `smf_members`.`ID_MEMBER` =2 LIMIT 1 ;");
	}
}