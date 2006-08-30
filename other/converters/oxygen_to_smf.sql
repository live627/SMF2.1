/* ATTENTION: You don't need to run or use this file!  The convert.php script does everything for you! */

/******************************************************************************/
---~ name: "Oxygen 1.1"
/******************************************************************************/
---~ version: "SMF 2.0 Alpha"
---~ settings: "/include/config.php"
---~ from_prefix: "`$dbname`.$tablepre"
---~ table_test: "{$from_prefix}members"

/******************************************************************************/
--- Converting members...
/******************************************************************************/

TRUNCATE {$to_prefix}members;

---* {$to_prefix}members
SELECT
	uid AS ID_MEMBER, SUBSTRING(username, 1, 80) AS memberName,
	regdate AS dateRegistered, postnum AS posts,
	SUBSTRING(email, 1, 255) AS emailAddress, lastvisit AS lastLogin,
	SUBSTRING(username, 1, 255) AS realName,
	SUBSTRING(password, 1, 64) AS passwd,
	SUBSTRING(customstatus, 1, 255) AS personalText,
	SUBSTRING(site, 1, 255) AS websiteTitle,
	SUBSTRING(site, 1, 255) AS websiteUrl,
	SUBSTRING(location, 1, 255) AS location, SUBSTRING(icq, 1, 255) AS ICQ,
	SUBSTRING(aim, 1, 16) AS AIM, SUBSTRING(yahoo, 1, 32) AS YIM,
	SUBSTRING(msn, 1, 255) AS MSN, IF(showemail = 1, 0, 1) AS hideEmail,
	SUBSTRING(sig, 1, 65534) AS signature, SUBSTRING(avatar, 1, 255) AS avatar,
	CASE status
		WHEN 'Super Administrator' THEN 1
		WHEN 'Administrator' THEN 1
		WHEN 'Super Moderator' THEN 2
		ELSE 0
	END AS ID_GROUP, '' AS lngfile, '' AS buddy_list, '' AS pm_ignore_list,
	'' AS messageLabels, '' AS timeFormat, '' AS  usertitle, '' AS memberIP,
	'' AS secretQuestion, '' AS secretAnswer, '' AS validation_code, 
	'' AS additionalGroups, '' AS smileySet, '' AS passwordSalt
FROM {$from_prefix}members
WHERE uid != 0;
---*

/******************************************************************************/
--- Converting categories...
/******************************************************************************/

TRUNCATE {$to_prefix}categories;

---* {$to_prefix}categories
---{
$row['name'] = stripslashes($row['name']);
---}
SELECT fid AS ID_CAT, SUBSTRING(name, 1, 255) AS name,
displayorder + 2 AS catOrder
FROM {$from_prefix}forums
WHERE type = 'group';
---*

/******************************************************************************/
--- Converting boards...
/******************************************************************************/

TRUNCATE {$to_prefix}boards;

DELETE FROM {$to_prefix}board_permissions
WHERE ID_BOARD != 0;

/* The converter will set ID_CAT for us based on ID_PARENT being wrong. */
---* {$to_prefix}boards
---{
$row['name'] = stripslashes($row['name']);
---}
SELECT
	fid AS ID_BOARD, fup AS ID_PARENT, displayorder AS boardOrder,
	SUBSTRING(name, 1, 255) AS name,
	SUBSTRING(description, 1, 65534) AS description, threads AS numTopics,
	posts AS numPosts, '-1,0' AS memberGroups
FROM {$from_prefix}forums
WHERE type != 'group';
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
	t.tid AS ID_TOPIC, t.topped AS isSticky, t.fid AS ID_BOARD,
	IFNULL(uf.uid, 0) AS ID_MEMBER_STARTED, t.replies AS numReplies,
	t.views AS numViews, t.closed AS locked, MIN(p.pid) AS ID_FIRST_MSG,
	MAX(p.pid) AS ID_LAST_MSG, IF(t.pollopts != '', t.tid, 0) AS ID_POLL
FROM ({$from_prefix}threads AS t, {$from_prefix}posts AS p)
	LEFT JOIN {$from_prefix}members AS uf ON (BINARY uf.username = t.author)
WHERE p.tid = t.tid
GROUP BY t.tid
HAVING ID_FIRST_MSG != 0
	AND ID_LAST_MSG != 0;
---*

---* {$to_prefix}topics (update ID_TOPIC)
SELECT t.ID_TOPIC, uf.uid AS ID_MEMBER_UPDATED
FROM ({$to_prefix}topics AS t, {$from_prefix}posts AS p, {$from_prefix}members AS uf)
WHERE p.pid = t.ID_LAST_MSG
	AND BINARY uf.username = p.author;
---*

/******************************************************************************/
--- Converting posts (this may take some time)...
/******************************************************************************/

TRUNCATE {$to_prefix}messages;
TRUNCATE {$to_prefix}attachments;

---* {$to_prefix}messages 200
---{
$row['subject'] = substr(stripslashes($row['subject']), 0, 255);
$row['body'] = substr(preg_replace('~\[align=(center|right|left)\](.+?)\[/align\]~i', '[$1]$2[/$1]', stripslashes($row['body'])), 0, 65534);
---}
SELECT
	p.pid AS ID_MSG, p.tid AS ID_TOPIC, p.dateline AS posterTime,
	uf.uid AS ID_MEMBER, p.subject AS subject,
	SUBSTRING(p.author, 1, 255) AS posterName,
	SUBSTRING(uf.email, 1, 255) AS posterEmail,
	SUBSTRING(p.useip, 1, 255) AS posterIP, p.fid AS ID_BOARD,
	IF(p.smileyoff = 0, 1, 0) AS smileysEnabled,
	REPLACE(p.message, '<br>', '<br />') AS body, '' AS modifiedName,
	'xx' AS icon
FROM {$from_prefix}posts AS p
	LEFT JOIN {$from_prefix}members AS uf ON (BINARY uf.username = p.author);
---*

/******************************************************************************/
--- Converting polls...
/******************************************************************************/

TRUNCATE {$to_prefix}polls;
TRUNCATE {$to_prefix}poll_choices;
TRUNCATE {$to_prefix}log_polls;

---* {$to_prefix}polls
SELECT
	t.tid AS ID_POLL, SUBSTRING(t.subject, 1, 255) AS question,
	SUBSTRING(t.author, 1, 255) AS posterName, uf.uid AS ID_MEMBER
FROM {$from_prefix}threads AS t
	LEFT JOIN {$from_prefix}members AS uf ON (BINARY uf.username = t.author)
WHERE t.pollopts != '';
---*

/******************************************************************************/
--- Converting poll options...
/******************************************************************************/

---* {$to_prefix}poll_choices
---{
$no_add = true;
$keys = array('ID_POLL', 'ID_CHOICE', 'label', 'votes');

$choices = explode('#|#', $row['choices']);
foreach ($choices as $i => $choice)
{
	$choice = explode('||~|~||', $choice);
	if (isset($choice[1]))
		$rows[] = "$row[ID_POLL], " . ($i + 1) . ", SUBSTRING('" . addslashes(trim($choice[0])) . "', 1, 255), " . (int) trim($choice[1]);
}
---}
SELECT tid AS ID_POLL, pollopts AS choices
FROM {$from_prefix}threads
WHERE pollopts != '';
---*

/******************************************************************************/
--- Converting poll votes...
/******************************************************************************/

---* {$to_prefix}log_polls
SELECT t.tid AS ID_POLL, mem.uid AS ID_MEMBER
FROM ({$from_prefix}threads AS t, {$from_prefix}members AS mem)
WHERE LOCATE(CONCAT(' ', mem.username, ' '), t.pollopts, LENGTH(t.pollopts) - LOCATE('#|#', REVERSE(t.pollopts)) + 2)
	AND t.pollopts != '';
---*

/******************************************************************************/
--- Converting personal messages (step 1)...
/******************************************************************************/

TRUNCATE {$to_prefix}personal_messages;

---* {$to_prefix}personal_messages
---{
$row['subject'] = substr(stripslashes($row['subject']), 0, 255);
$row['body'] = substr(preg_replace('~\[align=(center|right|left)\](.+?)\[/align\]~i', '[$1]$2[/$1]', stripslashes($row['body'])), 0, 65534);
---}
SELECT
	pm.u2uid AS ID_PM, uf.uid AS ID_MEMBER_FROM, pm.dateline AS msgtime,
	SUBSTRING(pm.msgfrom, 1, 255) AS fromName, pm.subject AS subject,
	pm.message AS body
FROM {$from_prefix}u2u AS pm
	LEFT JOIN {$from_prefix}members AS uf ON (BINARY uf.username = pm.msgfrom)
	LEFT JOIN {$from_prefix}members AS uf2 ON (BINARY uf2.username = pm.msgto)
WHERE pm.folder != 'outbox';
---*

/******************************************************************************/
--- Converting personal messages (step 2)...
/******************************************************************************/

TRUNCATE {$to_prefix}pm_recipients;

---* {$to_prefix}pm_recipients
SELECT
	pm.u2uid AS ID_PM, uf.uid AS ID_MEMBER, pm.isnew = 'no' AS is_read,
	'' AS labels
FROM ({$from_prefix}u2u AS pm, {$from_prefix}members AS uf)
WHERE pm.folder != 'outbox'
	AND BINARY uf.username = pm.msgto;
---*

/******************************************************************************/
--- Converting topic notifications...
/******************************************************************************/

TRUNCATE {$to_prefix}log_notify;

---* {$to_prefix}log_notify
SELECT uf.uid AS ID_MEMBER, f.tid AS ID_TOPIC
FROM ({$from_prefix}favorites AS f, {$from_prefix}members AS uf)
WHERE BINARY uf.username = f.username;
---*

/******************************************************************************/
--- Converting banned users...
/******************************************************************************/

TRUNCATE {$to_prefix}ban_items;
TRUNCATE {$to_prefix}ban_groups;

---* {$to_prefix}ban_groups
---{
// Give the ban a unique name.
$group_count = isset($group_count) ? $group_count + 1 : $_REQUEST['start'] + 1;
$row['name'] .= $group_count;
$row['ID_BAN_GROUP'] = $group_count;
---}
SELECT
	'migrated_' AS name, '' AS reason, dateline AS ban_time,
	'Migrated from XMB' AS notes, 1 AS cannot_access
FROM {$from_prefix}banned;
---*

---* {$to_prefix}ban_items
---{
// Check we give a valid ban group.
$item_count = isset($item_count) ? $item_count + 1 : $_REQUEST['start'] + 1;
$row['ID_BAN_GROUP'] = $item_count;
---}
SELECT
	ip1 AS ip_low1, ip1 AS ip_high1, ip2 AS ip_low2, ip2 AS ip_high2,
	ip3 AS ip_low3, ip3 AS ip_high3, ip4 AS ip_low4, ip4 AS ip_high4,
	'' AS hostname, '' AS email_address
FROM {$from_prefix}banned;
---*

/******************************************************************************/
--- Converting moderators...
/******************************************************************************/

TRUNCATE {$to_prefix}moderators;

---* {$to_prefix}moderators
SELECT u.uid AS ID_MEMBER, f.fid AS ID_BOARD
FROM ({$from_prefix}forums AS f, {$from_prefix}members AS u)
WHERE f.moderator != ''
	AND FIND_IN_SET(u.username, f.moderator);
---*

/******************************************************************************/
--- Converting smileys...
/******************************************************************************/

UPDATE {$to_prefix}smileys
SET hidden = 1;

---{
$specificSmileys = array(
	':)' => 'smiley',
	':(' => 'sad',
	':D' => 'grin',
	':up:' => 'grin',
	';)' => 'wink',
	':cool:' => 'cool',
	':mad:' => 'angry',
	':o' => 'shocked',
	':P' => 'tongue',
	':ange:' => 'angel',
	':-o' => 'rolleyes',
	':-p' => 'rolleyes',
	':mad:' => 'mad',
	'8)' => 'cool',
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
	$rows[] = "'$code', '{$name}.gif', '$name', $count";
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
if (strlen($fp) > 255)
	return;
$fp = @fopen($attachmentUploadDir . '/' . $newfilename, 'wb');
if (!$fp)
	return;

// Oxygen decodes several filetypes so we are going to do the opposite - by the same method!!
$toConvert = array('exe', 'bz', 'tar', 'zip', 'gz', 'bz2');
if (in_array(substr(strrchr($row['filename'], '.'), 1), $toConvert))
	$row['filedata'] = base64_decode($row['filedata']);

fwrite($fp, $row['filedata']);
fclose($fp);

$rows[] = "$ID_ATTACH, " . filesize($attachmentUploadDir . '/' . $newfilename) . ", '" . addslashes($row['filename']) . "', $row[ID_MSG], $row[downloads]";
$ID_ATTACH++;
---}
SELECT
	pid AS ID_MSG, attachment AS filedata, downloads AS downloads,
	filename AS filename
FROM {$from_prefix}attachments;
---*