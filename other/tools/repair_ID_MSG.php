<?php

// Minor bug: [quote]'s won't refer to the right id_msg anymore, but will refer to the correct topic.

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
		  id_msg int(10) unsigned NOT NULL auto_increment,
		  id_topic mediumint(8) unsigned NOT NULL default '0',
		  poster_time int(10) unsigned NOT NULL default '0',
		  id_member mediumint(8) unsigned NOT NULL default '0',
		  subject tinytext NOT NULL default '',
		  poster_name tinytext NOT NULL default '',
		  poster_email tinytext NOT NULL default '',
		  poster_ip tinytext NOT NULL default '',
		  smileys_enabled tinyint(4) NOT NULL default '1',
		  modified_time int(10) unsigned NOT NULL default '0',
		  modified_name tinytext,
		  body text,
		  icon varchar(16) NOT NULL default 'xx',
		  id_board smallint(5) unsigned NOT NULL default '0',
		  old_id_msg int(10) unsigned NOT NULL default '0',
		  PRIMARY KEY (id_msg),
		  UNIQUE topic (id_topic, id_msg),
		  KEY id_topic (id_topic),
		  KEY id_member (id_member),
		  KEY poster_time (poster_time),
		  KEY ipIndex (poster_ip(15), id_topic),
		  KEY participation (id_topic, id_member)
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
				NULL, id_topic, poster_time, id_member, subject, poster_name, poster_email, poster_ip,
				smileys_enabled, modified_time, modified_name, body, icon, id_board, id_msg
			FROM {$db_prefix}messages
			ORDER BY poster_time
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
			SELECT a.id_attach, m.id_msg
			FROM {$db_prefix}attachments AS a
				INNER JOIN {$db_prefix}temp_messages AS m ON (m.old_id_msg = a.id_msg)
			LIMIT $start, $maxOnce", __FILE__, __LINE__);

		// All done!  No more attachments!
		if (mysql_num_rows($result) < $maxOnce)
			break;

		while ($row = mysql_fetch_assoc($result))
			db_query("
				UPDATE {$db_prefix}attachments
				SET id_msg = $row[id_msg]
				WHERE id_attach = $row[id_attach]
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
			DROP COLUMN old_id_msg", __FILE__, __LINE__);
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
			SELECT id_topic, MAX(id_msg) AS id_last_msg, MIN(id_msg) AS id_first_msg
			FROM {$db_prefix}messages
			GROUP BY id_topic
			LIMIT $start, $maxOnce", __FILE__, __LINE__);
		while ($row = mysql_fetch_assoc($result))
			db_query("
				UPDATE {$db_prefix}topics
				SET id_first_msg = $row[id_first_msg],
					id_last_msg = $row[id_last_msg]
				WHERE id_topic = $row[id_topic]
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
		ADD UNIQUE firstMessage (id_first_msg, id_board),
		ADD UNIQUE lastMessage (id_last_msg, id_board)");

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
		<em>This repair has paused to avoid overloading your server, please click continue.</em><br />
		<br />
		<form action="', $_SERVER['PHP_SELF'], '?', $request, '" method="post">
			<input type="submit" value="Continue" class="button_submit" />
		</form>';
	exit;
}

?>