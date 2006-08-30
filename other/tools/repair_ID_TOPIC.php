<?php

// Minor bug: [quote]'s won't refer to the right ID_TOPIC!

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
			ID_TOPIC mediumint(8) unsigned NOT NULL auto_increment,
			isSticky tinyint(4) NOT NULL default '0',
			ID_BOARD smallint(5) unsigned NOT NULL default '0',
			ID_FIRST_MSG int(10) unsigned NOT NULL default '0',
			ID_LAST_MSG int(10) unsigned NOT NULL default '0',
			ID_MEMBER_STARTED mediumint(8) unsigned NOT NULL default '0',
			ID_MEMBER_UPDATED mediumint(8) unsigned NOT NULL default '0',
			ID_POLL mediumint(8) unsigned NOT NULL default '0',
			numReplies int(11) NOT NULL default '0',
			numViews int(11) NOT NULL default '0',
			locked tinyint(4) NOT NULL default '0',
			OLD_ID_TOPIC mediumint(8) unsigned NOT NULL,
			PRIMARY KEY (ID_TOPIC),
			UNIQUE lastMessage (ID_LAST_MSG, ID_BOARD),
			UNIQUE firstMessage (ID_FIRST_MSG, ID_BOARD),
			UNIQUE poll (ID_POLL, ID_TOPIC),
			KEY isSticky (isSticky),
			KEY ID_BOARD (ID_BOARD)
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
				NULL, isSticky, ID_BOARD, ID_FIRST_MSG, ID_LAST_MSG, ID_MEMBER_STARTED, ID_MEMBER_UPDATED, ID_POLL,
				numReplies, numViews, locked, ID_TOPIC
			FROM {$db_prefix}topics
			ORDER BY ID_FIRST_MSG
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
			SELECT m.ID_MSG, t.ID_TOPIC
			FROM ({$db_prefix}messages AS m, {$db_prefix}temp_topics AS t)
			WHERE m.ID_TOPIC = t.OLD_ID_TOPIC
			LIMIT $start, $maxOnce", __FILE__, __LINE__);

		// All done!  No more attachments!
		if (mysql_num_rows($result) < $maxOnce)
			break;

		while ($row = mysql_fetch_assoc($result))
			db_query("
				UPDATE {$db_prefix}messages
				SET ID_TOPIC = $row[ID_TOPIC]
				WHERE ID_MSG = $row[ID_MSG]
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
		SELECT t.ID_BOARD, MAX(t.ID_LAST_MSG) AS ID_LAST_MSG, IFNULL(m.posterTime, 0) AS posterTime
		FROM {$db_prefix}topics AS t
			LEFT JOIN {$db_prefix}messages AS m ON (m.ID_MSG = t.ID_LAST_MSG)
		GROUP BY t.ID_BOARD", __FILE__, __LINE__);
	while ($row = mysql_fetch_assoc($request))
	{
		db_query("
			UPDATE {$db_prefix}boards
			SET ID_LAST_MSG = $row[ID_LAST_MSG], lastUpdated = $row[posterTime]
			WHERE ID_BOARD = $row[ID_BOARD]", __FILE__, __LINE__);
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