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
		SELECT DISTINCT pmr.ID_MEMBER
		FROM {$db_prefix}pm_recipients AS pmr
			LEFT JOIN {$db_prefix}members AS mem ON (mem.ID_MEMBER = pmr.ID_MEMBER)
		WHERE mem.ID_MEMBER IS NULL", __FILE__, __LINE__);
	$array = array();
	while ($row = mysql_fetch_row($result))
		$array[] = $row[0];
	mysql_free_result($result);

	if (!empty($array))
		db_query("
			UPDATE {$db_prefix}pm_recipients
			SET ID_MEMBER = 0, deleted = 1
			WHERE ID_MEMBER IN (" . implode(', ', $array) . ")", __FILE__, __LINE__);
}

// Step 1: Update the messages of any senders who are no more.
if ($_GET['step'] <= 1)
{
	protectTimeOut('step=1');

	// Messages sent by deleted members.
	$result = db_query("
		SELECT DISTINCT pm.ID_MEMBER_FROM
		FROM {$db_prefix}personal_messages AS pm
			LEFT JOIN {$db_prefix}members AS mem ON (mem.ID_MEMBER = pm.ID_MEMBER_FROM)
		WHERE mem.ID_MEMBER IS NULL", __FILE__, __LINE__);
	$array = array();
	while ($row = mysql_fetch_row($result))
		$array[] = $row[0];
	mysql_free_result($result);

	if (!empty($array))
		db_query("
			UPDATE {$db_prefix}personal_messages
			SET ID_MEMBER_FROM = 0, deletedBySender = 1
			WHERE ID_MEMBER_FROM IN (" . implode(', ', $array) . ")", __FILE__, __LINE__);
}

// Step 2: Remove any messages that should but haven't been deleted yet.
if ($_GET['step'] <= 2)
{
	protectTimeOut('step=2');

	// First check for deleted messages by all of the recipients.
	$result = db_query("
		SELECT pm.ID_PM, MIN(pmr.deleted) AS allDeleted
		FROM ({$db_prefix}personal_messages AS pm, {$db_prefix}pm_recipients AS pmr)
		WHERE pm.deletedBySender = 1
			AND pm.ID_PM = pmr.ID_PM
		GROUP BY pm.ID_PM
		HAVING allDeleted = 1", __FILE__, __LINE__);
	$array = array();
	while ($row = mysql_fetch_row($result))
		$array[] = $row[0];
	mysql_free_result($result);

	if (!empty($array))
	{
		db_query("
			DELETE FROM {$db_prefix}pm_recipients
			WHERE ID_PM IN (" . implode(', ', $array) . ")", __FILE__, __LINE__);
		db_query("
			DELETE FROM {$db_prefix}personal_messages
			WHERE ID_PM IN (" . implode(', ', $array) . ")
			LIMIT " . count($array), __FILE__, __LINE__);
	}
}

// Step 3: Delete any rows that are only in one of the PM tables.
if ($_GET['step'] <= 3)
{
	protectTimeOut('step=3');

	// ID_PM is in the pm_recipients table but not the personal_messages table.
	$result = db_query("
		SELECT pm.ID_PM
		FROM {$db_prefix}personal_messages AS pm
			LEFT JOIN {$db_prefix}pm_recipients AS pmr ON (pmr.ID_PM = pm.ID_PM)
		WHERE pmr.ID_PM IS NULL", __FILE__, __LINE__);
	$array = array();
	while ($row = mysql_fetch_row($result))
		$array[] = $row[0];
	mysql_free_result($result);

	if (!empty($array))
		db_query("
			DELETE FROM {$db_prefix}personal_messages
			WHERE ID_PM IN (" . implode(', ', $array) . ")
			LIMIT " . count($array), __FILE__, __LINE__);

	// ID_PM is in the personal_messages table but not the pm_recipients table.
	$result = db_query("
		SELECT pmr.ID_PM
		FROM {$db_prefix}pm_recipients AS pmr
			LEFT JOIN {$db_prefix}personal_messages AS pm ON (pm.ID_PM = pmr.ID_PM)
		WHERE pm.ID_PM IS NULL", __FILE__, __LINE__);
	$array = array();
	while ($row = mysql_fetch_row($result))
		$array[] = $row[0];
	mysql_free_result($result);

	if (!empty($array))
		db_query("
			DELETE FROM {$db_prefix}pm_recipients
			WHERE ID_PM IN (" . implode(', ', $array) . ")", __FILE__, __LINE__);
}

// Step 3: Recount the total number of IM's for all the members.
if ($_GET['step'] <= 4)
{
	protectTimeOut('step=4');

	$result = db_query("
		SELECT mem.ID_MEMBER, mem.instantMessages, COUNT(*) AS count
		FROM ({$db_prefix}pm_recipients AS pm, {$db_prefix}members AS mem)
		WHERE pm.deleted != 1
			AND pm.ID_MEMBER = mem.ID_MEMBER
		GROUP BY mem.ID_MEMBER
		HAVING count != instantMessages", __FILE__, __LINE__);
	$array = array();
	while ($row = mysql_fetch_assoc($result))
		$array[] = $row;
	mysql_free_result($result);

	foreach ($array as $member)
		db_query("
			UPDATE {$db_prefix}members
			SET instantMessages = $member[count]
			WHERE ID_MEMBER = $member[ID_MEMBER]
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
		<i>This repair has paused to avoid overloading your server, please click continue.</i><br />
		<br />
		<form action="', $_SERVER['PHP_SELF'], '?', $request, '" method="post">
			<input type="submit" value="Continue" />
		</form>';
	exit;
}

?>