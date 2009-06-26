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
	$result = $smcFunc['db_query']('', '
		SELECT DISTINCT pmr.id_member
		FROM {db_prefix}pm_recipients AS pmr
			LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = pmr.id_member)
		WHERE mem.id_member IS NULL', array());
	$array = array();
	while ($row = $smcFunc['db_fetch_row']($result))
		$array[] = $row[0];
	$smcFunc['db_free_result']($result);

	if (!empty($array))
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}pm_recipients
			SET id_member = {int:guest_id}, deleted = {int:deleted}
			WHERE id_member IN ({array_int:members})',
			array(
				'guest_id' => 0,
				'deleted' => 1,
				'members' => $array,
		));
}

// Step 1: Update the messages of any senders who are no more.
if ($_GET['step'] <= 1)
{
	protectTimeOut('step=1');

	// Messages sent by deleted members.
	$result = $smcFunc['db_query']('', '
		SELECT DISTINCT pm.id_member_from
		FROM {db_prefix}personal_messages AS pm
			LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = pm.id_member_from)
		WHERE mem.id_member IS NULL', array());
	$array = array();
	while ($row = $smcFunc['db_fetch_row']($result))
		$array[] = $row[0];
	$smcFunc['db_free_result']($result);

	if (!empty($array))
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}personal_messages
			SET id_member_from = {int:id_from}, deleted_by_sender = {int:deleted}
			WHERE id_member_from IN ({array_int:members})',
			array(
				'id_from' => 0,
				'deleted' => 1,
				'members' => $array
			));
}

// Step 2: Remove any messages that should but haven't been deleted yet.
if ($_GET['step'] <= 2)
{
	protectTimeOut('step=2');

	// First check for deleted messages by all of the recipients.
	$result = $smcFunc['db_query']('', '
		SELECT pm.id_pm, MIN(pmr.deleted) AS allDeleted
		FROM {db_prefix}personal_messages AS pm
			INNER JOIN {db_prefix}pm_recipients AS pmr ON (pmr.id_pm = pm.id_pm)
		WHERE pm.deleted_by_sender = {int:deleted}
		GROUP BY pm.id_pm
		HAVING allDeleted = {int:all_deleted}',
		array(
			'deleted' => 1,
			'all_deleted' => 1
		));
	$array = array();
	while ($row = $smcFunc['db_fetch_row']($result))
		$array[] = $row[0];
	$smcFunc['db_free_result']($result);

	if (!empty($array))
	{
		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}pm_recipients
			WHERE id_pm IN ({array_int:pms})',
			array(
				'pms' => $array
		));
		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}personal_messages
			WHERE id_pm IN ({array_int:pms})
			LIMIT {int:count}',
			array(
				'pms' => $array,
				'count' => count($array),
		));
	}
}

// Step 3: Delete any rows that are only in one of the PM tables.
if ($_GET['step'] <= 3)
{
	protectTimeOut('step=3');

	// id_pm is in the pm_recipients table but not the personal_messages table.
	$result = $smcFunc['db_query']('', '
		SELECT pm.id_pm
		FROM {db_prefix}personal_messages AS pm
			LEFT JOIN {db_prefix}pm_recipients AS pmr ON (pmr.id_pm = pm.id_pm)
		WHERE pmr.id_pm IS NULL', array());
	$array = array();
	while ($row = $smcFunc['db_fetch_row']($result))
		$array[] = $row[0];
	$smcFunc['db_free_result']($result);

	if (!empty($array))
		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}personal_messages
			WHERE id_pm IN ({array_int:pms})
			LIMIT {int:count}',
			array(
				'pms' => $array,
				'count' => count($array),
		));

	// id_pm is in the personal_messages table but not the pm_recipients table.
	$result = $smcFunc['db_query']('', '
		SELECT pmr.id_pm
		FROM {db_prefix}pm_recipients AS pmr
			LEFT JOIN {db_prefix}personal_messages AS pm ON (pm.id_pm = pmr.id_pm)
		WHERE pm.id_pm IS NULL', array());
	$array = array();
	while ($row = $smcFunc['db_fetch_row']($result))
		$array[] = $row[0];
	$smcFunc['db_free_result']($result);

	if (!empty($array))
		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}pm_recipients
			WHERE id_pm IN ({array_int:pms})',
			array(
				'pms' => $array
		));
}

// Step 3: Recount the total number of IM's for all the members.
if ($_GET['step'] <= 4)
{
	protectTimeOut('step=4');

	$result = $smcFunc['db_query']('', '
		SELECT mem.id_member, mem.instant_messages, COUNT(*) AS count
		FROM {$db_prefix}members AS mem
			INNER JOIN {db_prefix}pm_recipients AS pm ON (pm.id_member = mem.id_member)
		WHERE pm.deleted != {int:deleted}
		GROUP BY mem.id_member
		HAVING count != instant_messages',
		array(
			'deleted' => 1);
	$array = array();
	while ($row = $smcFunc['db_fetch_assoc']($result))
		$array[] = $row;
	$smcFunc['db_free_result']($result);

	foreach ($array as $member)
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}members
			SET instant_messages = {int:ims}
			WHERE id_member = {int:id_mem}
			LIMIT 1',
			array(
				'ims' => $member['count'],
				'id_mem' => $member['id_member'],
		));
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