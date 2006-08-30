/* ATTENTION: You don't need to run or use this file!  The convert.php script does everything for you! */

/******************************************************************************/
---~ name: "YaBB SE 1.5.x"
/******************************************************************************/
---~ version: "SMF 2.0 Alpha"
---~ settings: "/Settings.php"
---~ from_prefix: "`$db_name`.$db_prefix"
---~ globals: language, timeout, timeoffset, MembersPerPage, Show_RecentBar, userpic_width, userpic_height
---~ globals: facesdir, facesurl, maxmessagedisplay, maxdisplay, Cookie_Length, RegAgree, emailpassword, emailnewpass, emailwelcome
---~ globals: allow_hide_email, timeformatstring, guestaccess, MaxMessLen, MaxSigLen, enable_ubbc, mbname
---~ globals: JrPostNum, FullPostNum, SrPostNum, GodPostNum
---~ table_test: "{$from_prefix}members"

/******************************************************************************/
--- Converting members...
/******************************************************************************/

TRUNCATE {$to_prefix}members;

---* {$to_prefix}members
SELECT
	mem.ID_MEMBER, SUBSTRING(mem.memberName, 1, 80) AS memberName,
	mem.dateRegistered, mem.posts, SUBSTRING(mem.passwd, 1, 64) AS passwd,
	SUBSTRING(mem.websiteTitle, 1, 255) AS websiteTitle,
	SUBSTRING(mem.websiteUrl, 1, 255) AS websiteUrl, mem.lastLogin,
	mem.birthdate, SUBSTRING(mem.ICQ, 1, 255) AS ICQ,
	SUBSTRING(IFNULL(mem.realName, mem.memberName), 1, 255) AS realName,
	mem.notifyOnce, REPLACE(mem.lngfile, '.lng', '') AS lngfile,
	SUBSTRING(mem.emailAddress, 1, 255) AS emailAddress,
	SUBSTRING(mem.AIM, 1, 16) AS AIM,
	SUBSTRING(mem.personalText, 1, 255) AS personalText,
	SUBSTRING(mem.timeFormat, 1, 80) AS timeFormat,
	mem.hideEmail, SUBSTRING(mem.memberIP, 1, 255) AS memberIP,
	SUBSTRING(mem.YIM, 1, 32) AS YIM,
	IF(IFNULL(mem.gender, '') = '', 0, IF(mem.gender = 'Male', 1, 2)) AS gender,
	SUBSTRING(mem.MSN, 1, 255) AS MSN,
	SUBSTRING(REPLACE(mem.signature, '<br>', '<br />'), 1, 65534) AS signature,
	SUBSTRING(mem.location, 1, 255) AS location, mem.timeOffset,
	SUBSTRING(mem.avatar, 1, 255) AS avatar,
	SUBSTRING(mem.usertitle, 1, 255) AS usertitle,
	mem.im_email_notify AS pm_email_notify, mem.karmaBad, mem.karmaGood,
	mem.notifyAnnouncements,
	SUBSTRING(mem.secretQuestion, 1, 255) AS secretQuestion,
	IF(mem.secretAnswer = '', '', MD5(mem.secretAnswer)) AS secretAnswer,
	CASE
		WHEN mem.memberGroup = 'Administrator' THEN 1
		WHEN mem.memberGroup = 'Global Moderator' THEN 2
		WHEN mg.ID_GROUP = 8 THEN 2
		WHEN mg.ID_GROUP = 1 THEN 1
		WHEN mg.ID_GROUP > 8 THEN mg.ID_GROUP
		ELSE 0
	END AS ID_GROUP, '' AS buddy_list, '' AS pm_ignore_list,
	'' AS messageLabels, '' AS validation_code, '' AS additionalGroups,
	'' AS smileySet, '' AS passwordSalt
FROM {$from_prefix}members AS mem
	LEFT JOIN {$from_prefix}membergroups AS mg ON (mg.membergroup = mem.memberGroup);
---*

UPDATE IGNORE {$to_prefix}members
SET pm_ignore_list = '*'
WHERE pm_ignore_list RLIKE '([\n,]|^)[*]([\n,]|$)';

---{
while (true)
{
	pastTime($substep);

	$request = convert_query("
		SELECT ID_MEMBER, im_ignore_list
		FROM {$from_prefix}members
		WHERE im_ignore_list RLIKE '[a-z]'
		LIMIT 512");
	while ($row2 = mysql_fetch_assoc($request))
	{
		$request2 = convert_query("
			SELECT ID_MEMBER
			FROM {$to_prefix}members
			WHERE FIND_IN_SET(memberName, '" . addslashes($row2['im_ignore_list']) . "')");
		$im_ignore_list = '';
		while ($row3 = mysql_fetch_assoc($request2))
			$im_ignore_list .= ',' . $row3['ID_MEMBER'];
		mysql_free_result($request2);

		convert_query("
			UPDATE {$to_prefix}members
			SET im_ignore_list = '" . substr($im_ignore_list, 1) . "'
			WHERE ID_MEMBER = $row2[ID_MEMBER]
			LIMIT 1");
	}
	if (mysql_num_rows($request) < 512)
		break;
	mysql_free_result($request);
}
---}

/******************************************************************************/
--- Converting categories...
/******************************************************************************/

TRUNCATE {$to_prefix}categories;

---* {$to_prefix}categories
SELECT ID_CAT, SUBSTRING(name, 1, 255) AS name, catOrder
FROM {$from_prefix}categories;
---*

/******************************************************************************/
--- Converting boards...
/******************************************************************************/

TRUNCATE {$to_prefix}boards;

DELETE FROM {$to_prefix}board_permissions
WHERE ID_BOARD != 0;

---* {$to_prefix}boards
SELECT
	ID_BOARD, ID_CAT, SUBSTRING(name, 1, 255) AS name, boardOrder,
	SUBSTRING(description, 1, 65534) AS description, numTopics, numPosts,
	`count` AS countPosts, '-1,0' AS memberGroups
FROM {$from_prefix}boards;
---*

---# Updating access permissions...
---{
$request = convert_query("
	SELECT membergroup, ID_GROUP
	FROM {$from_prefix}membergroups
	WHERE ID_GROUP = 1 OR ID_GROUP > 7");
$member_groups = array();
while ($row2 = mysql_fetch_row($request))
	$member_groups[trim($row2[0])] = $row2[1];
mysql_free_result($request);

$result = convert_query("
	SELECT TRIM(memberGroups) AS memberGroups, ID_CAT
	FROM {$from_prefix}categories");
while ($row2 = mysql_fetch_assoc($result))
{
	if (trim($row2['memberGroups']) == '')
		$groups = '-1,0,2';
	else
	{
		$memberGroups = array_unique(explode(',', $row2['memberGroups']));
		$groups = array(2);
		foreach ($memberGroups as $k => $check)
		{
			$memberGroups[$k] = trim($memberGroups[$k]);
			if ($memberGroups[$k] == '' || !isset($member_groups[$memberGroups[$k]]) || $member_groups[$memberGroups[$k]] == 8)
				continue;

			$groups[] = $member_groups[$memberGroups[$k]];
		}
		$groups = implode(',', array_unique($groups));
	}

	convert_query("
		UPDATE {$to_prefix}boards
		SET memberGroups = '$groups', lastUpdated = " . time() . "
		WHERE ID_CAT = $row2[ID_CAT]");
}
---}

/******************************************************************************/
--- Converting topics...
/******************************************************************************/

TRUNCATE {$to_prefix}topics;
TRUNCATE {$to_prefix}log_topics;
TRUNCATE {$to_prefix}log_boards;
TRUNCATE {$to_prefix}log_mark_read;

---* {$to_prefix}topics
SELECT
	ID_TOPIC, ID_BOARD, isSticky, IF(ID_POLL = -1, 0, ID_POLL) AS ID_POLL,
	numViews, ID_MEMBER_STARTED, ID_MEMBER_UPDATED, numReplies, locked,
	ID_FIRST_MSG, ID_LAST_MSG
FROM {$from_prefix}topics
HAVING ID_FIRST_MSG != 0
	AND ID_LAST_MSG != 0;
---*

/******************************************************************************/
--- Converting posts (this may take some time)...
/******************************************************************************/

TRUNCATE {$to_prefix}messages;
TRUNCATE {$to_prefix}attachments;

---* {$to_prefix}messages 200
SELECT
	m.ID_MSG, m.ID_TOPIC, t.ID_BOARD, m.ID_MEMBER, m.posterTime,
	SUBSTRING(m.posterName, 1, 255) AS posterName,
	SUBSTRING(m.posterEmail, 1, 255) AS posterEmail,
	SUBSTRING(m.posterIP, 1, 255) AS posterIP,
	SUBSTRING(m.subject, 1, 255) AS subject,
	m.smiliesEnabled AS smileysEnabled,
	m.modifiedTime, 
	SUBSTRING(m.modifiedName, 1, 255) AS modifiedName,
	SUBSTRING(REPLACE(m.body, '<br>', '<br />'), 1, 65534) AS body,
	'xx' AS icon
FROM ({$from_prefix}messages AS m, {$from_prefix}topics AS t)
WHERE t.ID_TOPIC = m.ID_TOPIC;
---*

/******************************************************************************/
--- Converting polls...
/******************************************************************************/

TRUNCATE {$to_prefix}polls;
TRUNCATE {$to_prefix}poll_choices;
TRUNCATE {$to_prefix}log_polls;

---* {$to_prefix}polls
SELECT
	p.ID_POLL, SUBSTRING(p.question, 1, 255) AS question, p.votingLocked,
	t.ID_MEMBER_STARTED,
	SUBSTRING(IFNULL(mem.realName, 'Guest'), 1, 255) AS posterName
FROM ({$from_prefix}polls AS p, {$from_prefix}topics AS t)
	LEFT JOIN {$from_prefix}members AS mem ON (mem.ID_MEMBER = t.ID_MEMBER_STARTED)
WHERE t.ID_POLL = p.ID_POLL;
---*

/******************************************************************************/
--- Converting poll options...
/******************************************************************************/

---* {$to_prefix}poll_choices
---{
$no_add = true;
$keys = array('ID_POLL', 'ID_CHOICE', 'label', 'votes');

for ($i = 1; $i <= 8; $i++)
{
	if (trim($row['option' . $i]) != '')
		$rows[] = "$row[ID_POLL], $i, '" . addslashes(substr($row['option' . $i], 1, 255)) . "', " . $row['votes' . $i];
}
---}
SELECT
	ID_POLL, option1, option2, option3, option4, option5, option6, option7,
	option8, votes1, votes2, votes3, votes4, votes5, votes6, votes7, votes8
FROM {$from_prefix}polls;
---*

/******************************************************************************/
--- Converting poll votes...
/******************************************************************************/

---* {$to_prefix}log_polls
---{
$no_add = true;

$members = explode(',', $row['ID_MEMBER']);
foreach ($members as $member)
	if (is_numeric($member))
		$rows[] = "$row[ID_POLL], $member, 0";
---}
SELECT ID_POLL, votedMemberIDs AS ID_MEMBER, 0 AS ID_CHOICE
FROM {$from_prefix}polls;
---*

/******************************************************************************/
--- Converting personal messages (step 1)...
/******************************************************************************/

TRUNCATE {$to_prefix}personal_messages;

---* {$to_prefix}personal_messages
SELECT
	ID_IM AS ID_PM, ID_MEMBER_FROM, msgtime,
	SUBSTRING(fromName, 1, 255) AS fromName,
	SUBSTRING(subject, 1, 255) AS subject,
	SUBSTRING(body, 1, 65534) AS body,
	IF(deletedBy = 0, 1, 0) AS deletedBySender
FROM {$from_prefix}instant_messages;
---*

/******************************************************************************/
--- Converting personal messages (step 2)...
/******************************************************************************/

TRUNCATE {$to_prefix}pm_recipients;

---* {$to_prefix}pm_recipients
SELECT
	ID_IM AS ID_PM, ID_MEMBER_TO AS ID_MEMBER, IF(readBy = 1, 1, 0) AS is_read,
	IF(deletedBy = 1, 1, 0) AS deleted, '' AS labels
FROM {$from_prefix}instant_messages;
---*

/******************************************************************************/
--- Converting topic notifications...
/******************************************************************************/

TRUNCATE {$to_prefix}log_notify;

---* {$to_prefix}log_notify
SELECT ID_MEMBER, ID_TOPIC, notificationSent AS sent
FROM {$from_prefix}log_topics
WHERE notificationSent != 0;
---*

---{
$result = convert_query("
	SELECT COUNT(*)
	FROM {$from_prefix}topics
	WHERE notifies != ''");
list ($numNotifies) = mysql_fetch_row($result);
mysql_free_result($result);

$_GET['t'] = (int) @$_GET['t'];

while ($_GET['t'] < $numNotifies)
{
	nextSubstep($substep);

	convert_query("
		INSERT IGNORE INTO {$to_prefix}log_notify
			(ID_MEMBER, ID_TOPIC)
		SELECT mem.ID_MEMBER, t.ID_TOPIC
		FROM ({$from_prefix}topics AS t, {$from_prefix}members AS mem)
		WHERE FIND_IN_SET(mem.ID_MEMBER, t.notifies)
			AND t.notifies != ''
		LIMIT $_GET[t], 512");

	$_GET['t'] += 512;
}

unset($_GET['t']);
---}

/******************************************************************************/
--- Converting attachments...
/******************************************************************************/

---* {$to_prefix}attachments
---{
$no_add = true;
$keys = array('ID_ATTACH', 'size', 'filename', 'ID_MSG', 'downloads');

if (!isset($yAttachmentDir))
{
	$result = convert_query("
		SELECT value
		FROM {$from_prefix}settings
		WHERE variable = 'attachmentUploadDir'
		LIMIT 1");
	list ($yAttachmentDir) = mysql_fetch_row($result);
	mysql_free_result($result);
}

$newfilename = getAttachmentFilename($row['filename'], $ID_ATTACH);
if (strlen($newfilename) > 255)
	return;
$fp = @fopen($attachmentUploadDir . '/' . $newfilename, 'wb');
if (!$fp)
	return;

$fp2 = @fopen($yAttachmentDir . '/' . $row['filename']);
while (!feof($fp2))
	fwrite($fp, fread($fp2, 2048));

fclose($fp);

$rows[] = "$ID_ATTACH, " . filesize($attachmentUploadDir . '/' . $newfilename) . ", '" . addslashes($row['filename']) . "', $row[ID_MSG], $row[downloads]";
$ID_ATTACH++;
---}
SELECT ID_MSG, 0 AS downloads, attachmentFilename AS filename
FROM {$from_prefix}messages
WHERE attachmentFilename IS NOT NULL
	AND attachmentFilename != '';
---*

/******************************************************************************/
--- Converting activity logs...
/******************************************************************************/

TRUNCATE {$to_prefix}log_activity;

---* {$to_prefix}log_activity
SELECT
	(year * 10000 + month * 100 + day) AS date, hits, topics, posts, registers,
	mostOn
FROM {$from_prefix}log_activity;
---*

/******************************************************************************/
--- Converting banning logs...
/******************************************************************************/

TRUNCATE {$to_prefix}log_banned;

---* {$to_prefix}log_banned
SELECT SUBSTRING(ip, 1, 16) AS ip, SUBSTRING(email, 1, 255) AS email, logTime
FROM {$from_prefix}log_banned;
---*

/******************************************************************************/
--- Converting mark as read history...
/******************************************************************************/

TRUNCATE {$to_prefix}log_boards;
TRUNCATE {$to_prefix}log_mark_read;

---* {$to_prefix}log_boards
SELECT ID_BOARD, ID_MEMBER, logTime
FROM {$from_prefix}log_boards;
---*

REPLACE INTO {$to_prefix}log_boards
	(ID_BOARD, ID_MEMBER, logTime)
SELECT lmr.ID_BOARD, lmr.ID_MEMBER, lmr.logTime
FROM {$from_prefix}log_mark_read AS lmr
	LEFT JOIN {$from_prefix}log_boards AS lb ON (lb.ID_BOARD = lmr.ID_BOARD AND lb.ID_MEMBER = lmr.ID_MEMBER)
WHERE lb.logTime < lmr.logTime;

---* {$to_prefix}log_mark_read
SELECT ID_BOARD, ID_MEMBER, logTime
FROM {$from_prefix}log_mark_read;
---*

/******************************************************************************/
--- Converting karma logs...
/******************************************************************************/

TRUNCATE {$to_prefix}log_karma;

---* {$to_prefix}log_karma
SELECT ID_TARGET, ID_EXECUTOR, logTime, action
FROM {$from_prefix}log_karma;
---*

/******************************************************************************/
--- Converting topic view logs...
/******************************************************************************/

TRUNCATE {$to_prefix}log_topics;

---* {$to_prefix}log_topics
SELECT ID_TOPIC, ID_MEMBER, logTime
FROM {$from_prefix}log_topics;
---*

/******************************************************************************/
--- Converting moderators...
/******************************************************************************/

TRUNCATE {$to_prefix}moderators;

---* {$to_prefix}moderators
---{
$no_add = true;

$members = array_unique(explode(',', $row['ID_MEMBER']));
foreach ($members as $k => $check)
{
	$members[$k] = trim($members[$k]);
	if ($members[$k] == '')
		unset($members[$k]);
}

$result_mods = convert_query("
	SELECT ID_MEMBER
	FROM {$to_prefix}members
	WHERE memberName IN ('" . implode("', '", $members) . "')
	LIMIT " . count($members));
while ($row_mods = mysql_fetch_assoc($result_mods))
	$rows[] = "$row[ID_BOARD], $row_mods[ID_MEMBER]";
mysql_free_result($result_mods);
---}
SELECT ID_BOARD, moderators AS ID_MEMBER
FROM {$from_prefix}boards;
---*

/******************************************************************************/
--- Converting banned members...
/******************************************************************************/

TRUNCATE {$to_prefix}ban_items;
TRUNCATE {$to_prefix}ban_groups;

---{
function ip2range($fullip)
{
	$ip_parts = explode('.', $fullip);
	if (count($ip_parts) != 4)
		return array();
	$ip_array = array();
	for ($i = 0; $i < 4; $i++)
	{
		if ($ip_parts[$i] == '*')
			$ip_array[$i] = array('low' => '0', 'high' => '255');
		elseif (preg_match('/^(\d{1,3})\-(\d{1,3})$/', $ip_parts[$i], $range))
			$ip_array[$i] = array('low' => $range[1], 'high' => $range[2]);
		elseif (is_numeric($ip_parts[$i]))
			$ip_array[$i] = array('low' => $ip_parts[$i], 'high' => $ip_parts[$i]);
	}
	if (count($ip_array) == 4)
		return $ip_array;
	else
		return array();
}

$request = convert_query("
	SELECT ban.type, ban.value, mem.ID_MEMBER
	FROM {$from_prefix}banned AS ban
		LEFT JOIN {$from_prefix}members AS mem ON (mem.memberName = ban.value)");
$rows = array();
while ($row2 = mysql_fetch_assoc($request))
{
	if ($row2['type'] == 'ip' && preg_match('/^\d{1,3}\.(\d{1,3}|\*)\.(\d{1,3}|\*)\.(\d{1,3}|\*)$/', $row2['value']))
	{
		$ip_parts = ip2range($row2['value']);
		$rows[] = "{$ip_parts[0]['low']}, {$ip_parts[0]['high']}, {$ip_parts[1]['low']}, {$ip_parts[1]['high']}, {$ip_parts[2]['low']}, {$ip_parts[2]['high']}, {$ip_parts[3]['low']}, {$ip_parts[3]['high']}, '', '', 0";
	}
	elseif ($row2['type'] == 'email')
		$rows[] = "0, 0, 0, 0, 0, 0, 0, 0, '', '$row2[value]', 0";
	elseif ($row2['type'] == 'username' && !empty($row2['ID_MEMBER']))
		$rows[] = "0, 0, 0, 0, 0, 0, 0, 0, '', '', $row2[ID_MEMBER]";
}
mysql_free_result($request);

// If there were values in the old table, insert them.
if (!empty($rows))
{
	convert_query("
		INSERT INTO {$to_prefix}ban_groups
			(name, ban_time, expire_time, cannot_access, reason, notes)
		VALUES ('yabbse_bans', " . time() . ", NULL, 1, '', 'Imported from YaBB SE'");
	$ID_BAN_GROUP = mysql_insert_id();

	convert_query("
		INSERT INTO {$to_prefix}ban_items
			(ID_BAN_GROUP, ip_low1, ip_high1, ip_low2, ip_high2, ip_low3, ip_high3, ip_low4, ip_high4, hostname, email_address, ID_MEMBER)
		VALUES ($ID_BAN_GROUP, " . implode("), ($ID_BAN_GROUP, ", $rows) . ')');
}
---}

/******************************************************************************/
--- Converting calendar events...
/******************************************************************************/

TRUNCATE {$to_prefix}calendar;

---* {$to_prefix}calendar
SELECT
	id AS ID_EVENT, CONCAT(year, '-', month + 1, '-', day) AS startDate,
	CONCAT(year, '-', month + 1, '-', day) AS endDate, id_board AS ID_BOARD,
	id_topic AS ID_TOPIC, id_member AS ID_MEMBER,
	SUBSTRING(title, 1, 48) AS title
FROM {$from_prefix}calendar;
---*

/******************************************************************************/
--- Converting membergroups...
/******************************************************************************/

DELETE FROM {$to_prefix}permissions
WHERE ID_GROUP > 8;

DELETE FROM {$to_prefix}membergroups
WHERE ID_GROUP > 8;

---{
$request = convert_query("
	SELECT ID_GROUP, membergroup
	FROM {$from_prefix}membergroups");
$memberGroups = array();
$setString = ',';
while ($row2 = mysql_fetch_assoc($request))
{
	$memberGroups[$row2['ID_GROUP']] = addslashes($row2['membergroup']);
	if ($row2['ID_GROUP'] > 8)
		$setString .= "
		($row2[ID_GROUP], '" . $memberGroups[$row2['ID_GROUP']] . "', '', -1, ''),";
}
mysql_free_result($request);

$grouptitles = array('Administrator', 'Global Moderator', 'Moderator', 'Newbie', 'Junior Member', 'Full Member', 'Senior Member', 'Hero');
for ($i = 1; $i < 9; $i ++)
	$memberGroups[$i] = isset($memberGroups[$i]) ? $memberGroups[$i] : $grouptitles[$i - 1];

convert_query("
	REPLACE INTO {$to_prefix}membergroups
		(ID_GROUP, groupName, onlineColor, minPosts, stars)
	VALUES (1, '$memberGroups[1]', '#FF0000', -1, '5#staradmin.gif'),
		(2, '$memberGroups[8]', '#0000FF', -1, '5#stargmod.gif'),
		(3, '$memberGroups[2]', '', -1, '5#starmod.gif'),
		(4, '$memberGroups[3]', '', 0, '1#star.gif'),
		(5, '$memberGroups[4]', '', '$JrPostNum', '2#star.gif'),
		(6, '$memberGroups[5]', '', '$FullPostNum', '3#star.gif'),
		(7, '$memberGroups[6]', '', '$SrPostNum', '4#star.gif'),
		(8, '$memberGroups[7]', '', '$GodPostNum', '5#star.gif')" .
		substr($setString, 0, -1));
---}

---{
// In addition add permissions for all the "new" groups.
$permissions = array(
	'view_mlist', 'search_posts', 'profile_view_own', 'profile_view_any', 'pm_read', 'pm_send', 'calendar_view',
	'view_stats', 'who_view', 'profile_identity_own', 'profile_extra_own',
	'profile_server_avatar', 'profile_upload_avatar', 'profile_remote_avatar', 'profile_remove_own'
);
$board_permissions = array(
	'remove_own', 'lock_own', 'mark_any_notify', 'mark_notify', 'modify_own',
	'poll_add_own', 'poll_edit_own', 'poll_lock_own', 'poll_post', 'poll_view',
	'poll_vote', 'post_attachment', 'post_new', 'post_reply_any', 'post_reply_own',
	'delete_own', 'report_any', 'send_topic', 'view_attachments'
);

$result = convert_query("
	SELECT ID_GROUP
	FROM {$to_prefix}membergroups
	WHERE ID_GROUP > 8");
$groups = array();
while ($row2 = mysql_fetch_assoc($result))
	$groups[] = $row2['ID_GROUP'];
mysql_free_result($result);

$setString = '';
foreach ($groups as $group)
{
	foreach ($permissions as $permission)
		$setString .= "
			($group, '$permission'),";
}

if (!empty($setString))
	convert_query("
		INSERT IGNORE INTO {$to_prefix}permissions
			(ID_GROUP, permission)
		VALUES " . substr($setString, 0, -1));

$setString = '';
foreach ($groups as $group)
{
	foreach ($board_permissions as $permission)
		$setString .= "
			($group, 0, '$permission'),";
}

if (!empty($setString))
	convert_query("
		INSERT IGNORE INTO {$to_prefix}board_permissions
			(ID_GROUP, ID_BOARD, permission)
		VALUES " . substr($setString, 0, -1));
---}

/******************************************************************************/
--- Converting basic settings...
/******************************************************************************/

---{
updateSettingsFile(array('mbname' => "'$mbname'"));
---}