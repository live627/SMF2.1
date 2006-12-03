<?php

// Minor bug: [quote]'s won't refer to the right id_topic!

require_once(dirname(__FILE__) . '/SSI.php');
set_time_limit(300);

$startTime = microtime();

// Validate inputs.
$_GET['step'] = isset($_GET['step']) ? (int) $_GET['step'] : 0;
$_GET['start'] = isset($_GET['start']) ? (int) $_GET['start'] : 0;

// 500 rows at a time.
$maxOnce = 500;

// Step 0: Create the temporary table.
if ($_GET['step'] <= 0)
{
	mysql_query("
		DROP TABLE {$db_prefix}temp_topics");

	// Create a new topics table.
	db_query("
		CREATE TABLE {$db_prefix}temp_topics (
			id_topic mediumint(8) unsigned NOT NULL auto_increment,
			is_sticky tinyint(4) NOT NULL default '0',
			id_board smallint(5) unsigned NOT NULL default '0',
			id_first_msg int(10) unsigned NOT NULL default '0',
			id_last_msg int(10) unsigned NOT NULL default '0',
			id_member_started mediumint(8) unsigned NOT NULL default '0',
			id_member_updated mediumint(8) unsigned NOT NULL default '0',
			id_poll mediumint(8) unsigned NOT NULL default '0',
			num_replies int(11) NOT NULL default '0',
			num_views int(11) NOT NULL default '0',
			locked tinyint(4) NOT NULL default '0',
			OLD_ID_TOPIC mediumint(8) unsigned NOT NULL,
			PRIMARY KEY (id_topic),
			UNIQUE lastMessage (id_last_msg, id_board),
			UNIQUE firstMessage (id_first_msg, id_board),
			UNIQUE poll (id_poll, id_topic),
			KEY is_sticky (is_sticky),
			KEY id_board (id_board)
		) TYPE=MyISAM", __FILE__, __LINE__);

	// Drop the old table if it's there.
	mysql_query("
		DROP TABLE {$db_prefix}old_topics");
}

// Step 1: Copy in the data from the old table.
if ($_GET['step'] <= 1)
{
	for ($start = $_GET['start']; true; $start += $maxOnce)
	{
		protectTimeOut('step=1;start=' . $start);
		db_query("
			INSERT INTO {$db_prefix}temp_topics
			SELECT
				NULL, is_sticky, id_board, id_first_msg, id_last_msg, id_member_started, id_member_updated, id_poll,
				num_replies, num_views, locked, id_topic
			FROM {$db_prefix}topics
			ORDER BY id_first_msg
			LIMIT $start, $maxOnce", __FILE__, __LINE__);

		// If less rows were inserted than selected, we're done!
		if (db_affected_rows() < $maxOnce)
			break;
	}

	$_GET['start'] = 0;
}

// Step 2: Fix any messages pointing to the topic
if ($_GET['step'] <= 2)
{
	for ($start = $_GET['start']; true; $start += $maxOnce)
	{
		protectTimeOut('step=2;start=' . $start);

		$result = db_query("
			SELECT m.id_msg, t.id_topic
			FROM {$db_prefix}messages AS m
				INNER JOIN {$db_prefix}temp_topics AS t ON (t.OLD_ID_TOPIC = m.id_topic)
			LIMIT $start, $maxOnce", __FILE__, __LINE__);

		// All done!  No more attachments!
		if (mysql_num_rows($result) < $maxOnce)
			break;

		while ($row = mysql_fetch_assoc($result))
			db_query("
				UPDATE {$db_prefix}messages
				SET id_topic = $row[id_topic]
				WHERE id_msg = $row[id_msg]
				LIMIT 1", __FILE__, __LINE__);
		mysql_free_result($result);
	}

	$_GET['start'] = -1;
}

// Step 3: Use the new table!
if ($_GET['step'] <= 3)
{
	protectTimeOut('step=3;start=1-');

	if ($_GET['start'] == -1)
	{
		db_query("
			RENAME TABLE {$db_prefix}topics TO {$db_prefix}old_topics,
				{$db_prefix}temp_topics TO {$db_prefix}topics", __FILE__, __LINE__);
		db_query("
			ALTER TABLE {$db_prefix}topics
			DROP COLUMN OLD_ID_TOPIC", __FILE__, __LINE__);
		$_GET['start'] = 0;
	}

	// And fix the boards.
	$request = db_query("
		SELECT t.id_board, MAX(t.id_last_msg) AS id_last_msg, IFNULL(m.poster_time, 0) AS poster_time
		FROM {$db_prefix}topics AS t
			LEFT JOIN {$db_prefix}messages AS m ON (m.id_msg = t.id_last_msg)
		GROUP BY t.id_board", __FILE__, __LINE__);
	while ($row = mysql_fetch_assoc($request))
	{
		db_query("
			UPDATE {$db_prefix}boards
			SET id_last_msg = $row[id_last_msg], lastUpdated = $row[poster_time]
			WHERE id_board = $row[id_board]", __FILE__, __LINE__);
	}
	mysql_free_result($request);

	echo 'Done';
}

// Don't let the script timeout on us...
function protectTimeOut($request)
{
	global $startTime;

	@set_time_limit(300);

	if (array_sum(explode(' ', microtime())) - array_sum(explode(' ', $startTime)) < 10)
		return;

	echo '
		<i>This repair has paused to avoid overloading your server, please click continue.</i><br />
		<br />
		<form action="', $_SERVER['PHP_SELF'], '?', $request, '" method="post">
			<input type="submit" value="Continue" />
		</form>';
	exit;
}

?>