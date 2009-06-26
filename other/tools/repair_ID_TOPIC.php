<?php

// Minor bug: [quote]'s won't refer to the right id_topic!

require_once(dirname(__FILE__) . '/SSI.php');
db_extend('packages');
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
	$smcFunc['db_drop_table']('temp_topics');

	// Create a new topics table.
	$smcFunc['db_create_table']('temp_topics',
		// Columns.
		array(
			array('name' => 'id_topic', 'type' => 'mediumint', 'size' => 8, 'unsigned' => true, 'auto' => true),
			array('name' => 'is_sticky', 'type' => 'tinyint', 'size' => 4, 'default' => '0'),
			array('name' => 'id_board', 'type' => 'smallint', 'size' => 5, 'unsigned' => true, 'default' => '0'),
			array('name' => 'id_first_msg', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => '0'),
			array('name' => 'id_last_msg', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => '0'),
			array('name' => 'id_member_started', 'type' => 'mediumint', 'size' => 8, 'unsigned' => true, 'default' => '0'),
			array('name' => 'id_member_updated', 'type' => 'mediumint', 'size' => 8, 'unsigned' => true, 'default' => '0'),
			array('name' => 'id_poll', 'type' => 'mediumint', 'size' => 8, 'unsigned' => true, 'default' => '0'),
			array('name' => 'id_previous_board', 'type' => 'smallint', 'size' => 5, 'default' => '0'),
			array('name' => 'id_previous_topic', 'type' => 'mediumint', 'size' => 8, 'default' => '0'),
			array('name' => 'num_replies', 'type' => 'int', 'size' => 11, 'default' => '0'),
			array('name' => 'num_views', 'type' => 'int', 'size' => 11, 'unsigned' => true, 'default' => '0'),
			array('name' => 'locked', 'type' => 'tinyint', 'size' => 4, 'default' => '0'),
			array('name' => 'unapproved_posts', 'type' => 'smallint', 'size' => 5, 'default' => '0'),
			array('name' => 'approved', 'type' => 'tinyint', 'size' => 3, 'default' => '1'),
			array('name' => 'old_id_topic', 'type' => 'mediumint', 'size' => 8, 'unsigned' => true),
		),
		// Keys
		array(
			array('type' => 'primary', 'columns' => array('id_topic')),
			array('name' => 'last_message', 'type' => 'unique', 'columns' => array('id_last_msg', 'id_board')),
			array('name' => 'first_message', 'type' => 'unique', 'columns' => array('id_first_msg', 'id_board')),
			array('name' => 'poll', 'type' => 'unique', 'columns' => array('id_poll', 'id_topic')),
			array('name' => 'is_sticky', 'columns' => array('is_sticky')),
			array('name' => 'approved', 'columns' => array('approved')),
			array('name' => 'id_board', 'columns' => array('id_board')),
			array('name' => 'member_started', 'columns' => array('id_member_started', 'id_board')),
			array('name' => 'last_message_sticky', 'columns' => array('id_board', 'is_sticky', 'id_last_msg')),
	));

	// Drop the old table if it's there.
	$smcFunc['db_drop_table']('old_topics');
}

// Step 1: Copy in the data from the old table.
if ($_GET['step'] <= 1)
{
	for ($start = $_GET['start']; true; $start += $maxOnce)
	{
		protectTimeOut('step=1;start=' . $start);
		$smcFunc['db_query']('', '
			INSERT INTO {db_prefix}temp_topics
			SELECT
				NULL, is_sticky, id_board, id_first_msg, id_last_msg, id_member_started, id_member_updated, id_poll, id_previous_board, id_previous_topic,
				num_replies, num_views, locked, id_topic, unapproved_posts, approved, id_topic
			FROM {db_prefix}topics
			ORDER BY id_first_msg
			LIMIT {int:start}, {int:max_once}',
			array(
				'start' => $start,
				'max_once' => $maxOnce,);

		// If less rows were inserted than selected, we're done!
		if ($smcFunc['db_affected_rows']() < $maxOnce)
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

		$result = $smcFunc('', '
			SELECT m.id_msg, t.id_topic
			FROM {db_prefix}messages AS m
				INNER JOIN {db_prefix}temp_topics AS t ON (t.old_id_topic = m.id_topic)
			LIMIT {int:start}, {int:max_once}',
			array(
				'start' => $start,
				'max_once' => $maxOnce
		));

		// All done!  No more attachments!
		if ($smcFunc['db_num_rows']($result) < $maxOnce)
			break;

		while ($row = $smcFunc['db_fetch_assoc']($result))
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}messages
				SET id_topic = {int:id_topic}
				WHERE id_msg = {int:id_msg}
				LIMIT 1',
				array(
					'id_topic' => $row['id_topic'],
					'id_msg' => $row['id_msg']
			));
		$smcFunc['db_free_result']($result);
	}

	$_GET['start'] = -1;
}

// Step 3: Use the new table!
if ($_GET['step'] <= 3)
{
	protectTimeOut('step=3;start=1-');

	if ($_GET['start'] == -1)
	{
		$smcFunc['db_query']('', '
			RENAME TABLE {db_prefix}topics TO {db_prefix}old_topics,
				{db_prefix}temp_topics TO {db_prefix}topics', array());
		$smcFunc['db_remove_column']('topics', 'old_id_topic');
		$_GET['start'] = 0;
	}

	// And fix the boards.
	$request = $smcFunc['db_query']('', '
		SELECT t.id_board, MAX(t.id_last_msg) AS id_last_msg, IFNULL(m.poster_time, 0) AS poster_time
		FROM {db_prefix}topics AS t
			LEFT JOIN {db_prefix}messages AS m ON (m.id_msg = t.id_last_msg)
		GROUP BY t.id_board', array());
	while ($row = $smcFunc['db_fetch_assoc']($request))
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}boards
			SET id_last_msg = {int:id_last_msg}, last_updated = {int:last_updated}
			WHERE id_board = {int:id_board}',
			array(
				'id_lsg_msg' => $row['id_last_msg'],
				'last_updated' => $row['poster_time'],
				'id_board' => $row['id_board']
		));
	$smcFunc['db_free_result']($request);

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
		<em>This repair has paused to avoid overloading your server, please click continue.</em><br />
		<br />
		<form action="', $_SERVER['PHP_SELF'], '?' . $request, '" method="post" name="autoSubmit">
			<input type="submit" value="Continue" class="button_submit" />
		</form>
		<script type="text/javascript"><!-- // --><![CDATA[
			window.onload = doAutoSubmit;
			var countdown = 3;

			function doAutoSubmit()
			{
				if (countdown == 0)
					document.autoSubmit.submit();
				else if (countdown == -1)
					return;

				document.autoSubmit.b.value = "Continue (" + countdown + ")";
				countdown--;

				setTimeout("doAutoSubmit();", 1000);
			}
		// ]]></script>';
	exit;
}

?>