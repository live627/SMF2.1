<?php

// Minor bug: [quote]'s won't refer to the right ID_MSG anymore, but will refer to the correct topic.

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
		DROP TABLE {$db_prefix}temp_messages");

	// Create a new messages table.
	db_query("
		CREATE TABLE {$db_prefix}temp_messages (
		  ID_MSG int(10) unsigned NOT NULL auto_increment,
		  ID_TOPIC mediumint(8) unsigned NOT NULL default '0',
		  posterTime int(10) unsigned NOT NULL default '0',
		  ID_MEMBER mediumint(8) unsigned NOT NULL default '0',
		  subject tinytext NOT NULL default '',
		  posterName tinytext NOT NULL default '',
		  posterEmail tinytext NOT NULL default '',
		  posterIP tinytext NOT NULL default '',
		  smileysEnabled tinyint(4) NOT NULL default '1',
		  modifiedTime int(10) unsigned NOT NULL default '0',
		  modifiedName tinytext,
		  body text,
		  icon varchar(16) NOT NULL default 'xx',
		  ID_BOARD smallint(5) unsigned NOT NULL default '0',
		  OLD_ID_MSG int(10) unsigned NOT NULL default '0',
		  PRIMARY KEY (ID_MSG),
		  UNIQUE topic (ID_TOPIC, ID_MSG),
		  KEY ID_TOPIC (ID_TOPIC),
		  KEY ID_MEMBER (ID_MEMBER),
		  KEY posterTime (posterTime),
		  KEY ipIndex (posterIP(15), ID_TOPIC),
		  KEY participation (ID_TOPIC, ID_MEMBER)
		) TYPE=MyISAM", __FILE__, __LINE__);

	// Drop the old table if it's there.
	mysql_query("
		DROP TABLE {$db_prefix}old_messages");
}

// Step 1: Copy in the data from the old table.
if ($_GET['step'] <= 1)
{
	for ($start = $_GET['start']; true; $start += $maxOnce)
	{
		protectTimeOut('step=1;start=' . $start);

		db_query("
			INSERT INTO {$db_prefix}temp_messages
			SELECT
				NULL, ID_TOPIC, posterTime, ID_MEMBER, subject, posterName, posterEmail, posterIP,
				smileysEnabled, modifiedTime, modifiedName, body, icon, ID_BOARD, ID_MSG
			FROM {$db_prefix}messages
			ORDER BY posterTime
			LIMIT $start, $maxOnce", __FILE__, __LINE__);

		// If less rows were inserted than selected, we're done!
		if (db_affected_rows() < $maxOnce)
			break;
	}

	$_GET['start'] = 0;
}

// Step 2: Fix any attachments pointing to said message.
if ($_GET['step'] <= 2)
{
	for ($start = $_GET['start']; true; $start += $maxOnce)
	{
		protectTimeOut('step=2;start=' . $start);

		$result = db_query("
			SELECT a.ID_ATTACH, m.ID_MSG
			FROM ({$db_prefix}attachments AS a, {$db_prefix}temp_messages AS m)
			WHERE a.ID_MSG = m.OLD_ID_MSG
			LIMIT $start, $maxOnce", __FILE__, __LINE__);

		// All done!  No more attachments!
		if (mysql_num_rows($result) < $maxOnce)
			break;

		while ($row = mysql_fetch_assoc($result))
			db_query("
				UPDATE {$db_prefix}attachments
				SET ID_MSG = $row[ID_MSG]
				WHERE ID_ATTACH = $row[ID_ATTACH]
				LIMIT 1", __FILE__, __LINE__);
		mysql_free_result($result);
	}

	$_GET['start'] = -1;
}

// Step 3: Use the new table!  Fix topics.
if ($_GET['step'] <= 3)
{
	protectTimeOut('step=3;start=1-');

	if ($_GET['start'] == -1)
	{
		db_query("
			RENAME TABLE {$db_prefix}messages TO {$db_prefix}old_messages,
				{$db_prefix}temp_messages TO {$db_prefix}messages", __FILE__, __LINE__);
		db_query("
			ALTER TABLE {$db_prefix}messages
			DROP COLUMN OLD_ID_MSG", __FILE__, __LINE__);
		$_GET['start'] = 0;

		mysql_query("
			ALTER TABLE {$db_prefix}topics
			DROP INDEX firstMessage,
			DROP INDEX lastMessage");
	}

	for ($start = $_GET['start']; true; $start += $maxOnce)
	{
		protectTimeOut('step=3;start=' . $start);

		$result = db_query("
			SELECT ID_TOPIC, MAX(ID_MSG) AS ID_LAST_MSG, MIN(ID_MSG) AS ID_FIRST_MSG
			FROM {$db_prefix}messages
			GROUP BY ID_TOPIC
			LIMIT $start, $maxOnce", __FILE__, __LINE__);
		while ($row = mysql_fetch_assoc($result))
			db_query("
				UPDATE {$db_prefix}topics
				SET ID_FIRST_MSG = $row[ID_FIRST_MSG],
					ID_LAST_MSG = $row[ID_LAST_MSG]
				WHERE ID_TOPIC = $row[ID_TOPIC]
				LIMIT 1", __FILE__, __LINE__);

		// No more rows... hurrah.
		if (mysql_num_rows($result) < $maxOnce)
			break;

		mysql_free_result($result);
	}
}

if ($_GET['step'] <= 4)
{
	mysql_query("
		ALTER TABLE {$db_prefix}topics
		ADD UNIQUE firstMessage (ID_FIRST_MSG, ID_BOARD),
		ADD UNIQUE lastMessage (ID_LAST_MSG, ID_BOARD)");

	echo 'Done!';
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