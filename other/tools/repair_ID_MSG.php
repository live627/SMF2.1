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
	$smcFunc['db_drop_table']('temp_messages');

	// Create a new messages table.
	$smcFunc['db_create_table']('temp_messages',
		// Columns.
		array(
			array('name' => 'id_msg', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'auto' => true),
			array('name' => 'id_topic', 'type' => 'mediumint', 'size' => 8, 'unsigned' => true, 'default' => '0'),
			array('name' => 'id_board', 'type' => 'smallint', 'size' => 5, 'unsigned' => true, 'default' => '0'),
			array('name' => 'poster_time', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => '0'),
			array('name' => 'id_member', 'type' => 'mediumint', 'size' => 8, 'unsigned' => true, 'default' => '0'),
			array('name' => 'id_msg_modified', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => '0'),
			array('name' => 'subject', 'type' => 'tinytext', 'default' => ''),
			array('name' => 'poster_name', 'type' => 'tinytext', 'default' => ''),
			array('name' => 'poster_email', 'type' => 'tinytext', 'default' => ''),
			array('name' => 'poster_ip', 'type' => 'tinytext', 'default' => ''),
			array('name' => 'smileys_enabled', 'type' => 'tinyint', 'size' => 4, 'default' => '1'),
			array('name' => 'modified_time', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => '0'),
			array('name' => 'modified_name', 'type' => 'tinytext'),
			array('name' => 'body', 'type' => 'text'),
			array('name' => 'icon', 'type' => 'varchar', 'size' => 16, 'default' => 'xx'),
			array('name' => 'old_id_msg', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => '0'),
		),
		// Keys
		array(
			array('type' => 'primary', 'columns' => array('id_msg')),
			array('name' => 'topic', 'type' => 'unique', 'columns' => array('id_topic', 'id_msg')),
			array('name' => 'id_board', 'type' => 'unique', 'columns' => array('id_board', 'id_msg')),
			array('name' => 'id_member', 'type' => 'unique', 'columns' => array('id_member', 'id_msg')),
			array('name' => 'approved', 'columns' => array('approved')),
			array('name' => 'ip_index', 'columns' => array('poster_ip(15)', 'id_topic')),
			array('name' => 'participation', 'columns' => array('id_topic', 'id_member')),
			array('name' => 'show_posts', 'columns' => array('id_member', 'id_board')),
			array('name' => 'id_topic', 'columns' => array('id_topic')),
	));

	// Drop the old table if it's there.
	$smcFunc['db_drop_table']('old_messages');
}

// Step 1: Copy in the data from the old table.
if ($_GET['step'] <= 1)
{
	for ($start = $_GET['start']; true; $start += $maxOnce)
	{
		protectTimeOut('step=1;start=' . $start);

		$smcFunc['db_query']('
			INSERT INTO {db_prefix}temp_messages
			SELECT
				NULL, id_topic, id_board, poster_time, id_member, id_msg_modified, subject, poster_name, poster_email, poster_ip,
				smileys_enabled, modified_time, modified_name, body, icon, approved, id_msg
			FROM {db_prefix}messages
			ORDER BY poster_time
			LIMIT {int:start}, {int:max_once}',
			array(
				'start' = $start,
				'max_once' => $maxOnce,
		));

		// If less rows were inserted than selected, we're done!
		if ($smcFunc['db_affected_rows']() < $maxOnce)
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

		$result = $smcFunc['db_query']('', '
			SELECT a.id_attach, m.id_msg
			FROM {db_prefix}attachments AS a
				INNER JOIN {db_prefix}temp_messages AS m ON (m.old_id_msg = a.id_msg)
			LIMIT {int:start}, {int:max_once}',
			array(
				'start' => $start,
				'max_once' => $maxOnce,
		));

		// All done!  No more attachments!
		if ($smcFunc['db_num_rows']($result) < $maxOnce)
			break;

		while ($row = $smcFunc['db_fetch_assoc']($result))
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}attachments
				SET id_msg = {int:id_msg}
				WHERE id_attach = {int:id_attach}
				LIMIT 1',
				array(
					'id_msg' => $row['id_msg'],
					'id_attach' => $row['id_attach'],
			));
		$smcFunc['db_free_result']($result);
	}

	$_GET['start'] = -1;
}

// Step 3: Use the new table!  Fix topics.
if ($_GET['step'] <= 3)
{
	protectTimeOut('step=3;start=1-');

	if ($_GET['start'] == -1)
	{
		$smcFunc['db_query']('', '
			RENAME TABLE {db_prefix}messages TO {db_prefix}old_messages,
				{db_prefix}temp_messages TO {db_prefix}messages', array());
		$smcFunc['db_remove_column']('messages', 'old_id_msg');
		$_GET['start'] = 0;

		$smcFunc['db_remove_index']('topics', 'first_message');
		$smcFunc['db_remove_index']('topics', 'last_message');
	}

	for ($start = $_GET['start']; true; $start += $maxOnce)
	{
		protectTimeOut('step=3;start=' . $start);

		$result = $smcFunc['db_query']('', '
			SELECT id_topic, MAX(id_msg) AS id_last_msg, MIN(id_msg) AS id_first_msg
			FROM {db_prefix}messages
			GROUP BY id_topic
			LIMIT {int:start}, {int:maxOnce}',
			array(
				'start' => $start,
				'max_once' => $maxOnce
		));
		while ($row = $smcFunc['db_fetch_assoc']($result))
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}topics
				SET id_first_msg = {int:id_first_msg},
					id_last_msg = {int:id_last_msg}
				WHERE id_topic = {int:id_topic}
				LIMIT 1',
				array(
					'id_first_msg' => $row['id_first_msg'],
					'id_last_msg' => $row['id_last_msg'],
					'id_topic' => $row['id_topic']
			));

		// No more rows... hurrah.
		if ($smcFunc['db_num_rows']($result) < $maxOnce)
			break;

		$smcFunc['db_free_result']($result);
	}
}

if ($_GET['step'] <= 4)
{
	$smcFunc['db_add_index']('topics', array('name' => 'first_message', 'type' => 'unique', 'columns' => array('id_first_msg', 'id_board')));
	$smcFunc['db_add_index']('topics', array('name' => 'last_message', 'type' => 'unique', 'columns' => array('id_last_msg', 'id_board')));

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