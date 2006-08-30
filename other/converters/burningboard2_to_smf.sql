/* ATTENTION: You don't need to run or use this file!  The convert.php script does everything for you! */

/******************************************************************************/
---~ name: "Burning Board 2.x"
/******************************************************************************/
---~ version: "SMF 2.0 Alpha"
---~ settings: "/acp/lib/config.inc.php"
---~ from_prefix: "`$sqldb`.bb{$n}_"
---~ table_test: "{$from_prefix}users"

/******************************************************************************/
--- Converting ranks...
/******************************************************************************/

DELETE FROM {$to_prefix}membergroups
WHERE minPosts != -1
	AND ID_GROUP != 4;

---* {$to_prefix}membergroups
---{
// Do the stars!
if (trim($row['stars']) != '')
	$row['stars'] = sprintf("%d#star.gif", substr_count($row['stars'], ';') + 1);
---}
SELECT 
	SUBSTRING(ranktitle, 1, 80) AS groupName, needposts AS minPosts, 
	'' AS onlineColor, rankimages AS stars
FROM {$from_prefix}ranks
WHERE groupid NOT IN (1, 2, 3)
ORDER BY needposts;
---*

/******************************************************************************/
--- Converting members...
/******************************************************************************/

TRUNCATE {$to_prefix}members;
TRUNCATE {$to_prefix}attachments;

---{
$test = mysql_query("
	SELECT groupcombinationid
	FROM {$from_prefix}groupcombinations
	LIMIT 1");

// This is /not/ beautiful but we can support 2.0.x too with it.
if ($test)
{
	$_SESSION['group_id_clause'] = 'gc.groupids';
	$_SESSION['group_id_join'] = "LEFT JOIN {$from_prefix}groupcombinations AS gc ON (gc.groupcombinationid = u.groupcombinationid)";
	$_SESSION['password_clause'] = "IF(u.sha1_password != '', u.sha1_password, u.password)";
}
else
{
	$_SESSION['group_id_clause'] = 'u.groupid';
	$_SESSION['group_id_join'] = '';
	$_SESSION['password_clause'] = 'u.password';
}
---}

---* {$to_prefix}members
---{
// Do we need to load the groups first?
if (!isset($admin_groups))
{
	$admin_groups = array();
	$gmod_groups = array();

	$result = mysql_query("
		SELECT groupid, securitylevel
		FROM {$from_prefix}groups
		WHERE securitylevel > 1");
	if ($result)
	{
		while ($row2 = mysql_fetch_assoc($result))
		{
			if ($row2['securitylevel'] > 3)
				$admin_groups[] = $row2['groupid'];
			else
				$gmod_groups[] = $row2['groupid'];
		}
	}
	else
	{
		$result = mysql_query("
			SELECT groupid, issupermod, canuseacp
			FROM {$from_prefix}groups
			WHERE issupermod OR canuseacp");
		while ($row2 = mysql_fetch_assoc($result))
		{
			if ($row2['canuseacp'])
				$admin_groups[] = $row2['groupid'];
			else
				$gmod_groups[] = $row2['groupid'];
		}
	}
	mysql_free_result($result);
}
$allGroups = explode(',', $row['ID_GROUP']);
// Default to nothing...
$row['ID_GROUP'] = 0;
foreach ($allGroups as $ID_GROUP)
{
	// Admin?
	if (in_array($ID_GROUP, $admin_groups))
		$row['ID_GROUP'] = 1;
	elseif (in_array($ID_GROUP, $gmod_groups))
		$row['ID_GROUP'] = 2;
}

$row['signature'] = substr(preg_replace('~\[size=([789]|[012]\d)\]~is', '[size=$1pt]', $row['signature']), 0, 65534);
---}
SELECT
	u.userid AS ID_MEMBER, SUBSTRING(u.username, 1, 80) AS memberName,
	u.userposts AS posts, u.regdate AS dateRegistered, 
	{$_SESSION['group_id_clause']} AS ID_GROUP, 
	SUBSTRING(u.title, 1, 255) AS usertitle, u.lastvisit AS lastLogin, 
	SUBSTRING(u.username, 1, 255) AS realName,
	SUBSTRING(u.email, 1, 255) AS emailAddress,
	{$_SESSION['password_clause']} AS passwd,
	SUBSTRING(u.ICQ, 1, 255) AS ICQ, SUBSTRING(u.AIM, 1, 16) AS AIM,
	SUBSTRING(u.YIM, 1, 32) AS YIM, SUBSTRING(u.MSN, 1, 255) AS MSN,
	SUBSTRING(u.homepage, 1, 255) AS websiteTitle,
	SUBSTRING(u.homepage, 1, 255) AS websiteUrl, u.birthday AS birthdate,
	IF(u.invisible = 0, 1, 0) AS showOnline, u.gender,
	SUBSTRING(u.usertext, 1, 255) AS personalText,
	IF(u.showemail = 0, 1, 0) AS hideEmail, 
	u.signature AS signature, u.timezoneoffset AS timeOffset, '' AS lngfile,
	'' AS buddy_list, '' AS pm_ignore_list, '' AS messageLabels,
	'' AS location, '' AS timeFormat, '' AS avatar, '' AS memberIP,
	'' AS secretQuestion, '' AS secretAnswer, '' AS validation_code,
	'' AS additionalGroups, '' AS smileySet, '' AS passwordSalt
FROM {$from_prefix}users AS u
	{$_SESSION['group_id_join']};
---*

/******************************************************************************/
--- Converting avatars...
/******************************************************************************/

---* {$to_prefix}attachments
---{
$no_add = true;
$keys = array('ID_ATTACH', 'size', 'filename', 'ID_MEMBER');

$newfilename = getAttachmentFilename($row['filename'], $ID_ATTACH);
if (copy($_POST['path_from'] . '/images/avatars/avatar-' . $row['avatarid'] . '.' . $row['avatarextension'], $attachmentUploadDir . '/' . $newfilename))
{
	$rows[] = "$ID_ATTACH, " . filesize($attachmentUploadDir . '/' . $newfilename) . ", '" . addslashes($row['filename']) . "', $row[ID_MEMBER]";

	$ID_ATTACH++;
}
---}
SELECT
	avatarid, SUBSTRING(CONCAT(avatarname, '.', avatarextension), 1, 255) AS filename,
	userid AS ID_MEMBER, avatarextension
FROM {$from_prefix}avatars;
---*

/******************************************************************************/
--- Converting categories...
/******************************************************************************/

TRUNCATE {$to_prefix}categories;

---* {$to_prefix}categories
SELECT 
	boardid AS ID_CAT, SUBSTRING(title, 1, 255) AS name, 
	boardorder AS catOrder
FROM {$from_prefix}boards
WHERE isboard = 0;
---*

/******************************************************************************/
--- Converting boards...
/******************************************************************************/

TRUNCATE {$to_prefix}boards;

DELETE FROM {$to_prefix}board_permissions
WHERE ID_BOARD != 0;

/* The converter will set ID_CAT for us based on ID_PARENT being wrong. */
---* {$to_prefix}boards
SELECT
	boardid AS ID_BOARD, parentid AS ID_PARENT, boardorder AS boardOrder,
	SUBSTRING(title, 1, 255) AS name, SUBSTRING(description, 1, 65534),
	threadcount AS numTopics, postcount AS numPosts, '-1, 0' AS memberGroups
FROM {$from_prefix}boards
WHERE isboard = 1;
---*

/******************************************************************************/
--- Converting topics...
/******************************************************************************/

TRUNCATE {$to_prefix}topics;
TRUNCATE {$to_prefix}log_topics;
TRUNCATE {$to_prefix}log_boards;
TRUNCATE {$to_prefix}log_mark_read;

---* {$to_prefix}topics
SELECT
	t.threadid AS ID_TOPIC, t.important AS isSticky, t.boardid AS ID_BOARD,
	t.replycount AS numReplies, t.views AS numViews, t.closed AS locked,
	t.starterid AS ID_MEMBER_STARTED, t.lastposterid AS ID_MEMBER_UPDATED,
	MIN(p.postid) AS ID_FIRST_MSG, MAX(p.postid) AS ID_LAST_MSG,
	t.pollid AS ID_POLL
FROM ({$from_prefix}threads AS t, {$from_prefix}posts AS p)
WHERE p.threadid = t.threadid
GROUP BY t.threadid
HAVING ID_FIRST_MSG != 0
	AND ID_LAST_MSG != 0;
---*

/******************************************************************************/
--- Converting posts (this may take some time)...
/******************************************************************************/

TRUNCATE {$to_prefix}messages;

---* {$to_prefix}messages 200
---{
$row['body'] = preg_replace('~\[size=([789]|[012]\d)\]~is', '[size=$1pt]', $row['body']);
---}
SELECT
	p.postid AS ID_MSG, p.threadid AS ID_TOPIC, t.boardid AS ID_BOARD,
	p.posttime AS posterTime, p.userid AS ID_MEMBER,
	SUBSTRING(t.topic, 1, 255) AS subject, p.ipaddress AS posterIP,
	SUBSTRING(IFNULL(u.username, p.username), 1, 255) AS posterName,
	SUBSTRING(IFNULL(u.email, ''), 1, 255) AS posterEmail,
	allowsmilies AS smileysEnabled,
	SUBSTRING(REPLACE(p.message, '<br>', '<br />'), 1, 65534) AS body,
	'' AS modifiedName, 'xx' AS icon
FROM ({$from_prefix}posts AS p, {$from_prefix}threads AS t)
	LEFT JOIN {$from_prefix}users AS u ON (u.userid = p.userid)
WHERE t.threadid = p.threadid;
---*

/******************************************************************************/
--- Converting polls...
/******************************************************************************/

TRUNCATE {$to_prefix}polls;
TRUNCATE {$to_prefix}poll_choices;
TRUNCATE {$to_prefix}log_polls;

---* {$to_prefix}polls
SELECT
	p.pollid AS ID_POLL, SUBSTRING(p.question , 1, 255) AS question,
	t.starterid AS ID_MEMBER,
	IF(p.timeout = 0, 0, (p.starttime + 86400 * p.timeout)) AS expireTime,
	SUBSTRING(IFNULL(u.username, ''), 1, 255) AS posterName,
	choicecount AS maxVotes
FROM ({$from_prefix}polls AS p, {$from_prefix}threads AS t)
	LEFT JOIN {$from_prefix}users AS u ON (u.userid = t.starterid)
WHERE p.threadid = t.threadid;
---*

/******************************************************************************/
--- Converting poll options...
/******************************************************************************/

---* {$to_prefix}poll_choices
---{
if (!isset($_SESSION['convert_last_poll']) || $_SESSION['convert_last_poll'] != $row['ID_POLL'])
{
	$_SESSION['convert_last_poll'] = $row['ID_POLL'];
	$_SESSION['convert_last_choice'] = 0;
}

$row['ID_CHOICE'] = ++$_SESSION['convert_last_choice'];
---}
SELECT pollid AS ID_POLL, 1 AS ID_CHOICE,
SUBSTRING(polloption, 1, 255) AS label, votes
FROM {$from_prefix}polloptions
ORDER BY pollid;
---*

/******************************************************************************/
--- Converting poll votes...
/******************************************************************************/

---* {$to_prefix}log_polls
SELECT id AS ID_POLL, userid AS ID_MEMBER
FROM {$from_prefix}votes
GROUP BY ID_POLL, ID_MEMBER;
---*

/******************************************************************************/
--- Converting personal messages (step 1)...
/******************************************************************************/

TRUNCATE {$to_prefix}personal_messages;

---* {$to_prefix}personal_messages
---{
$row['body'] = preg_replace('~\[size=([789]|[012]\d)\]~is', '[size=$1pt]', $row['body']);
---}
SELECT
	pm.privatemessageid AS ID_PM, pm.senderid AS ID_MEMBER_FROM,
	IF(pm.deletepm = 2, 1, 0) AS deletedBySender, pm.sendtime AS msgtime,
	SUBSTRING(IFNULL(u.username, 'Guest'), 1, 255) AS fromName,
	SUBSTRING(pm.subject, 1, 255) AS subject,
	SUBSTRING(pm.message, 1, 65534) AS body
FROM {$from_prefix}privatemessage AS pm
	LEFT JOIN {$from_prefix}users AS u ON (u.userid = pm.senderid);
---*

/******************************************************************************/
--- Converting personal messages (step 2)...
/******************************************************************************/

TRUNCATE {$to_prefix}pm_recipients;

---* {$to_prefix}pm_recipients
SELECT
	pm.privatemessageid AS ID_PM, pm.recipientid AS ID_MEMBER, 1 AS is_read,
	IF(pm.deletepm = 1, 1, 0) AS deleted, '' AS labels
FROM {$from_prefix}privatemessage AS pm;
---*

/******************************************************************************/
--- Converting topic notifications...
/******************************************************************************/

TRUNCATE {$to_prefix}log_notify;

---* {$to_prefix}log_notify
SELECT s.userid AS ID_MEMBER, s.threadid AS ID_TOPIC
FROM {$from_prefix}subscribethreads AS s;
---*

/******************************************************************************/
--- Converting board notifications...
/******************************************************************************/

---* {$to_prefix}log_notify
SELECT s.userid AS ID_MEMBER, s.boardid AS ID_BOARD
FROM {$from_prefix}subscribeboards AS s;
---*

/******************************************************************************/
--- Converting moderators...
/******************************************************************************/

TRUNCATE {$to_prefix}moderators;

---* {$to_prefix}moderators
SELECT m.userid AS ID_MEMBER, m.boardid AS ID_BOARD
FROM {$from_prefix}moderators AS m;
---*

/******************************************************************************/
--- Converting banned users (by IP)...
/******************************************************************************/

TRUNCATE {$to_prefix}ban_items;
TRUNCATE {$to_prefix}ban_groups;

---# Moving banned entries...
---{
while (true)
{
	pastTime($substep);

	$result = convert_query("
		SELECT value
		FROM {$from_prefix}options
		WHERE varname = 'ban_ip'
		LIMIT " . (int) $_REQUEST['start'] . ", 25");
	$ban_time = time();
	$ban_count = $_REQUEST['start'] + 1;
	while ($row = mysql_fetch_assoc($result))
	{
		$ips = explode("\n", $row['value']);
		foreach ($ips as $ip)
		{
			$ip = trim($ip);
			$sections = explode('.', $ip);

			if (empty($sections[0]))
				continue;

			$ip_low1 = $sections[0];
			$ip_high1 = $sections[0];

			$ip_low2 = isset($sections[1]) && $sections[1] != '*' ? $sections[1] : 0;
			$ip_high2 = isset($sections[1]) && $sections[1] != '*' ? $sections[1] : 255;

			$ip_low3 = isset($sections[2]) && $sections[2] != '*' ? $sections[2] : 0;
			$ip_high3 = isset($sections[2]) && $sections[2] != '*' ? $sections[2] : 255;

			$ip_low4 = isset($sections[3]) && $sections[3] != '*' ? $sections[3] : 0;
			$ip_high4 = isset($sections[3]) && $sections[3] != '*' ? $sections[3] : 255;

			convert_query("
				INSERT INTO {$to_prefix}ban_groups
					(name, ban_time, expire_time, notes, cannot_access, reason)
				VALUES
					('migrated_ban_" . ($ban_count++) . "', $ban_time, 0, 'Migrated from Burning Board', 1, '')");

			$ID_BAN_GROUP = mysql_insert_id();

			if (empty($ID_BAN_GROUP))
				continue;

			convert_query("
				INSERT INTO {$to_prefix}ban_items
					(ID_BAN_GROUP, ip_low1, ip_high1, ip_low2, ip_high2, ip_low3, ip_high3, ip_low4, ip_high4, hostname, email_address)
				VALUES ($ID_BAN_GROUP, $ip_low1, $ip_high1, $ip_low2, $ip_high2, $ip_low3, $ip_high3, $ip_low4, $ip_high4, '', '')");
		}
	}

	$_REQUEST['start'] += 25;
	if (mysql_num_rows($result) < 25)
		break;

	mysql_free_result($result);
}
$_REQUEST['start'] = 0;
---}
---#

/******************************************************************************/
--- Converting banned users (by email)...
/******************************************************************************/

---# Moving banned entries...
---{
while (true)
{
	pastTime($substep);

	$result = convert_query("
		SELECT value
		FROM {$from_prefix}options
		WHERE varname = 'ban_email'
		LIMIT " . (int) $_REQUEST['start'] . ", 25");
	$ban_time = time();
	$ban_count = $_REQUEST['start'] + 1;
	while ($row = mysql_fetch_assoc($result))
	{
		$emails = explode("\n", $row['value']);
		foreach ($emails as $email)
		{
			$email = trim($email);

			if (empty($email))
				continue;

			convert_query("
				INSERT INTO {$to_prefix}ban_groups
					(name, ban_time, expire_time, notes, cannot_access, reason)
				VALUES
					('migrated_ban_" . ($ban_count++) . "', $ban_time, 0, 'Migrated from Burning Board', 1, '')");

			$ID_BAN_GROUP = mysql_insert_id();

			if (empty($ID_BAN_GROUP))
				continue;

			convert_query("
				INSERT INTO {$to_prefix}ban_items
					(ID_BAN_GROUP, email_address, hostname)
				VALUES
					($ID_BAN_GROUP, SUBSTRING('$email', 1, 255), '')");
		}
	}

	$_REQUEST['start'] += 25;
	if (mysql_num_rows($result) < 25)
		break;

	mysql_free_result($result);
}
$_REQUEST['start'] = 0;
---}
---#

/******************************************************************************/
--- Converting smileys...
/******************************************************************************/

UPDATE {$to_prefix}smileys
SET hidden = 1;

---{
$specificSmileys = array(
	':)' => 'smiley',
	':))' => 'smiley',
	':]' => 'cheesy',
	'?(' => 'huh',
	'8)' => 'cool',
	':(' => 'sad',
	':D' => 'grin',
	';(' => 'cry',
	'8o' => 'shocked',
	':O' => 'embarrassed',
	';)' => 'wink',
	':P' => 'tongue',
	':tongue:' => 'tongue',
	':baby:' => 'angel',
	':rolleyes:' => 'rolleyes',
	':evil:' => 'evil',
	'X(' => 'angry',
);

$request = convert_query("
	SELECT MAX(smileyOrder)
	FROM {$to_prefix}smileys");
list ($count) = mysql_fetch_row($request);
mysql_free_result($request);

$request = convert_query("
	SELECT code
	FROM {$to_prefix}smileys");
$currentCodes = array();
while ($row = mysql_fetch_assoc($request))
	$currentCodes[] = $row['code'];
mysql_free_result($request);

$rows = array();
foreach ($specificSmileys as $code => $name)
{
	if (in_array($code, $currentCodes))
		continue;

	$count++;
	$rows[] = "SUBSTRING('$code', 1, 30), SUBSTRING('{$name}.gif', 1, 48), SUBSTRING('$name', 1, 80), $count";
}

if (!empty($rows))
	convert_query("
		REPLACE INTO {$to_prefix}smileys
			(code, filename, description, smileyOrder)
		VALUES (" . implode("),
			(", $rows) . ")");
---}

ALTER TABLE {$to_prefix}smileys
ORDER BY LENGTH(code) DESC;

/******************************************************************************/
--- Converting attachments...
/******************************************************************************/

---* {$to_prefix}attachments
---{
$no_add = true;
$keys = array('ID_ATTACH', 'size', 'filename', 'ID_MSG', 'downloads');

$newfilename = getAttachmentFilename($row['filename'], $ID_ATTACH);
if (copy($_POST['path_from'] . '/attachments/attachment-' . $row['attachmentid'] . '.' . $row['attachmentextension'], $attachmentUploadDir . '/' . $newfilename))
{
	$rows[] = "$ID_ATTACH, " . filesize($attachmentUploadDir . '/' . $newfilename) . ", '" . addslashes($row['filename']) . "', $row[ID_MSG], $row[downloads]";

	$ID_ATTACH++;
}
---}
SELECT
	attachmentid, postid AS ID_MSG, counter AS downloads, attachmentextension,
	SUBSTRING(CONCAT(attachmentname, '.', attachmentextension), 1, 255) AS filename
FROM {$from_prefix}attachments;
---*