<?php

require_once(dirname(__FILE__) . '/SSI.php');
set_time_limit(300);

// !!! This should also resort the personal_messages table while it's at it.

$startTime = microtime();

// Validate inputs.
$_GET['step'] = isset($_GET['step']) ? (int) $_GET['step'] : 0;

// Step 0: Update the PMs of any members who have been deleted.
if ($_GET['step'] <= 0)
{
	// Messages received by deleted members.
	$result = db_query("
		SELECT DISTINCT pmr.id_member
		FROM {$db_prefix}pm_recipients AS pmr
			LEFT JOIN {$db_prefix}members AS mem ON (mem.id_member = pmr.id_member)
		WHERE mem.id_member IS NULL", __FILE__, __LINE__);
	$array = array();
	while ($row = mysql_fetch_row($result))
		$array[] = $row[0];
	mysql_free_result($result);

	if (!empty($array))
		db_query("
			UPDATE {$db_prefix}pm_recipients
			SET id_member = 0, deleted = 1
			WHERE id_member IN (" . implode(', ', $array) . ")", __FILE__, __LINE__);
}

// Step 1: Update the messages of any senders who are no more.
if ($_GET['step'] <= 1)
{
	protectTimeOut('step=1');

	// Messages sent by deleted members.
	$result = db_query("
		SELECT DISTINCT pm.id_member_from
		FROM {$db_prefix}personal_messages AS pm
			LEFT JOIN {$db_prefix}members AS mem ON (mem.id_member = pm.id_member_from)
		WHERE mem.id_member IS NULL", __FILE__, __LINE__);
	$array = array();
	while ($row = mysql_fetch_row($result))
		$array[] = $row[0];
	mysql_free_result($result);

	if (!empty($array))
		db_query("
			UPDATE {$db_prefix}personal_messages
			SET id_member_from = 0, deleted_by_sender = 1
			WHERE id_member_from IN (" . implode(', ', $array) . ")", __FILE__, __LINE__);
}

// Step 2: Remove any messages that should but haven't been deleted yet.
if ($_GET['step'] <= 2)
{
	protectTimeOut('step=2');

	// First check for deleted messages by all of the recipients.
	$result = db_query("
		SELECT pm.id_pm, MIN(pmr.deleted) AS allDeleted
		FROM {$db_prefix}personal_messages AS pm
			INNER JOIN {$db_prefix}pm_recipients AS pmr ON (pmr.id_pm = pm.id_pm)
		WHERE pm.deleted_by_sender = 1
		GROUP BY pm.id_pm
		HAVING allDeleted = 1", __FILE__, __LINE__);
	$array = array();
	while ($row = mysql_fetch_row($result))
		$array[] = $row[0];
	mysql_free_result($result);

	if (!empty($array))
	{
		db_query("
			DELETE FROM {$db_prefix}pm_recipients
			WHERE id_pm IN (" . implode(', ', $array) . ")", __FILE__, __LINE__);
		db_query("
			DELETE FROM {$db_prefix}personal_messages
			WHERE id_pm IN (" . implode(', ', $array) . ")
			LIMIT " . count($array), __FILE__, __LINE__);
	}
}

// Step 3: Delete any rows that are only in one of the PM tables.
if ($_GET['step'] <= 3)
{
	protectTimeOut('step=3');

	// id_pm is in the pm_recipients table but not the personal_messages table.
	$result = db_query("
		SELECT pm.id_pm
		FROM {$db_prefix}personal_messages AS pm
			LEFT JOIN {$db_prefix}pm_recipients AS pmr ON (pmr.id_pm = pm.id_pm)
		WHERE pmr.id_pm IS NULL", __FILE__, __LINE__);
	$array = array();
	while ($row = mysql_fetch_row($result))
		$array[] = $row[0];
	mysql_free_result($result);

	if (!empty($array))
		db_query("
			DELETE FROM {$db_prefix}personal_messages
			WHERE id_pm IN (" . implode(', ', $array) . ")
			LIMIT " . count($array), __FILE__, __LINE__);

	// id_pm is in the personal_messages table but not the pm_recipients table.
	$result = db_query("
		SELECT pmr.id_pm
		FROM {$db_prefix}pm_recipients AS pmr
			LEFT JOIN {$db_prefix}personal_messages AS pm ON (pm.id_pm = pmr.id_pm)
		WHERE pm.id_pm IS NULL", __FILE__, __LINE__);
	$array = array();
	while ($row = mysql_fetch_row($result))
		$array[] = $row[0];
	mysql_free_result($result);

	if (!empty($array))
		db_query("
			DELETE FROM {$db_prefix}pm_recipients
			WHERE id_pm IN (" . implode(', ', $array) . ")", __FILE__, __LINE__);
}

// Step 3: Recount the total number of IM's for all the members.
if ($_GET['step'] <= 4)
{
	protectTimeOut('step=4');

	$result = db_query("
		SELECT mem.id_member, mem.instant_messages, COUNT(*) AS count
		FROM {$db_prefix}members AS mem
			INNER JOIN {$db_prefix}pm_recipients AS pm ON (pm.id_member = mem.id_member)
		WHERE pm.deleted != 1
		GROUP BY mem.id_member
		HAVING count != instant_messages", __FILE__, __LINE__);
	$array = array();
	while ($row = mysql_fetch_assoc($result))
		$array[] = $row;
	mysql_free_result($result);

	foreach ($array as $member)
		db_query("
			UPDATE {$db_prefix}members
			SET instant_messages = $member[count]
			WHERE id_member = $member[id_member]
			LIMIT 1", __FILE__, __LINE__);
}

if ($_GET['step'] <= 5)
	echo '
		Done!';

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