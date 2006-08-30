/* ATTENTION: You don't need to run or use this file!  The convert.php script does everything for you! */

/******************************************************************************/
---~ name: "Zorum 3"
/******************************************************************************/
---~ version: "SMF 2.0 Alpha"
---~ settings: "/config.php", "/constants.php"
---~ variable: "$applName = 'zorum';"
---~ from_prefix: "`$dbName`.{$applName}_"
---~ table_test: "{$from_prefix}zorumuser"

/******************************************************************************/
--- Converting members...
/******************************************************************************/

TRUNCATE {$to_prefix}members;

ALTER TABLE {$to_prefix}members
ADD COLUMN tempID int(10) unsigned NOT NULL default 0;

---* {$to_prefix}members
SELECT
	id AS tempID, SUBSTRING(name, 1, 80) AS memberName,
	SUBSTRING(name, 1, 255) AS realName,
	SUBSTRING(email, 1, 255) AS emailAddress,
	SUBSTRING(password, 1, 64) AS passwd, IF(isAdm, 1, If(isMod, 2, 0)) AS ID_GROUP,
	lastClickTime AS lastLogin, creationtime AS dateRegistered,
	postnum AS posts, showEmail = 0 AS hideEmail,
	SUBSTRING(REPLACE(signature, '\n', '<br />'), 1, 65534) AS signature,
	'' AS lngfile, '' AS buddy_list, '' AS pm_ignore_list, '' AS messageLabels,
	'' AS personalText, '' AS websiteTitle, '' AS websiteUrl, '' AS location,
	'' AS ICQ, '' AS AIM, '' AS YIM, '' AS MSN, '' AS timeFormat, '' AS avatar,
	'' AS usertitle, '' AS memberIP, '' AS secretQuestion, '' AS secretAnswer,
	'' AS validation_code, '' AS additionalGroups, '' AS smileySet,
	'' AS passwordSalt
FROM {$from_prefix}zorumuser
WHERE password != '';
---*

/******************************************************************************/
--- Converting categories...
/******************************************************************************/

TRUNCATE {$to_prefix}categories;

---* {$to_prefix}categories
SELECT id AS ID_CAT, SUBSTRING(name, 1, 255) AS name, id AS catOrder
FROM {$from_prefix}forum
WHERE iscat = 1;
---*

/******************************************************************************/
--- Converting boards...
/******************************************************************************/

TRUNCATE {$to_prefix}boards;

DELETE FROM {$to_prefix}board_permissions
WHERE ID_BOARD != 0;

---* {$to_prefix}boards
SELECT
	f.id AS ID_BOARD, IFNULL(cat.id, 1) AS ID_CAT, f.id AS boardOrder,
	SUBSTRING(f.name, 1, 255) AS name,
	SUBSTRING(f.description, 1, 65534) AS description, f.topicnum AS numTopics,
	f.postnum AS numPosts, '-1,0' AS memberGroups
FROM {$from_prefix}forum AS f
	LEFT JOIN {$from_prefix}forum AS cat ON (cat.treeidx < f.treeidx AND cat.iscat = 1)
WHERE f.iscat = 0;
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
	t.id AS ID_TOPIC, t.pid AS ID_BOARD, t.postnum - 1 AS numReplies,
	t.viewnum AS numViews, IF(t.poll, p.id, 0) AS ID_POLL,
	memf.ID_MEMBER AS ID_MEMBER_STARTED, MIN(m.id) AS ID_FIRST_MSG,
	MAX(m.id) AS ID_LAST_MSG
FROM ({$from_prefix}topic AS t, {$from_prefix}message AS m)
	LEFT JOIN {$from_prefix}poll AS p ON (p.tid = t.id)
	LEFT JOIN {$to_prefix}members AS memf ON (memf.tempID = t.ownerId)
WHERE m.tid = t.id
GROUP BY t.id
HAVING ID_FIRST_MSG != 0
	AND ID_LAST_MSG != 0;
---*

---* {$to_prefix}topics (update ID_TOPIC)
SELECT t.ID_TOPIC, meml.ID_MEMBER AS ID_MEMBER_UPDATED
FROM ({$to_prefix}topics AS t, {$from_prefix}message AS m, {$to_prefix}members AS meml)
WHERE m.id = t.ID_LAST_MSG
	AND meml.tempID = m.ownerId;
---*

/******************************************************************************/
--- Converting posts (this may take some time)...
/******************************************************************************/

TRUNCATE {$to_prefix}messages;
TRUNCATE {$to_prefix}attachments;

---* {$to_prefix}messages 200
SELECT
	m.id AS ID_MSG, m.pid AS ID_BOARD, m.tid AS ID_TOPIC,
	SUBSTRING(m.iplog, 1, 255) AS posterIP,
	SUBSTRING(IF(m.subject = '', t.subject, m.subject), 1, 255) AS subject,
	mem.ID_MEMBER, SUBSTRING(REPLACE(m.txt, '\n', '<br />'), 1, 65534) AS body,
	m.creationtime AS posterTime, m.smiley AS smileysEnabled,
	SUBSTRING(mem.emailAddress, 1, 255) AS posterEmail,
	SUBSTRING(IFNULL(mem.memberName, u.name), 1, 255) AS posterName,
	'' AS modifiedName, 'xx' AS icon
FROM ({$from_prefix}message AS m, {$from_prefix}topic AS t)
	LEFT JOIN {$to_prefix}members AS mem ON (mem.tempID = m.ownerId)
	LEFT JOIN {$from_prefix}zorumuser AS u ON (u.id = m.ownerId)
WHERE t.id = m.tid;
---*

/******************************************************************************/
--- Converting polls...
/******************************************************************************/

TRUNCATE {$to_prefix}polls;
TRUNCATE {$to_prefix}poll_choices;
TRUNCATE {$to_prefix}log_polls;

---* {$to_prefix}polls
SELECT
	p.id AS ID_POLL, SUBSTRING(p.question, 1, 255) AS question, mem.ID_MEMBER,
	SUBSTRING(IFNULL(mem.memberName, u.name), 1, 255) AS posterName
FROM {$from_prefix}poll AS p
	LEFT JOIN {$to_prefix}members AS mem ON (mem.tempID = p.ownerId)
	LEFT JOIN {$from_prefix}zorumuser AS u ON (u.id = p.ownerId);
---*

/******************************************************************************/
--- Converting poll options...
/******************************************************************************/

/* This makes counting the votes up much, much easier. */
ALTER TABLE {$to_prefix}poll_choices
DROP PRIMARY KEY,
ADD COLUMN ID_TEMP int(10) unsigned NOT NULL auto_increment PRIMARY KEY;

---* {$to_prefix}poll_choices
---{
$no_add = true;
$keys = array('ID_POLL', 'ID_CHOICE', 'label', 'votes');

for ($i = 1; $i <= 10; $i++)
{
	if (trim($row['q' . $i]) != '')
		$rows[] = "$row[ID_POLL], $i, '" . addslashes(substr($row['q' . $i], 0, 255)) . "', 0";
}
---}
SELECT id AS ID_POLL, q1, q2, q3, q4, q5, q6, q7, q8, q9, q10
FROM {$from_prefix}poll;
---*

---* {$to_prefix}poll_choices (update ID_TEMP)
SELECT pc.ID_TEMP, COUNT(*) AS votes
FROM ({$to_prefix}poll_choices AS pc, {$from_prefix}subscribe AS s)
WHERE s.type = 65536
	AND s.objid = pc.ID_POLL
	AND s.info = pc.ID_CHOICE
GROUP BY pc.ID_TEMP;
---*

ALTER TABLE {$to_prefix}poll_choices
DROP COLUMN ID_TEMP,
ADD PRIMARY KEY (ID_POLL, ID_CHOICE);

/******************************************************************************/
--- Converting poll votes...
/******************************************************************************/

---* {$to_prefix}log_polls
SELECT s.objid AS ID_POLL, mem.ID_MEMBER, s.info AS ID_CHOICE
FROM ({$from_prefix}subscribe AS s, {$to_prefix}members AS mem)
WHERE s.type = 65536
	AND mem.tempID = s.userid;
---*

/******************************************************************************/
--- Converting topic notifications...
/******************************************************************************/

TRUNCATE {$to_prefix}log_notify;

---* {$to_prefix}log_notify
SELECT mem.ID_MEMBER, s.objid AS ID_TOPIC
FROM ({$from_prefix}subscribe AS s, {$to_prefix}members AS mem)
WHERE s.type = 16
	AND mem.tempID = s.userid;
---*

/******************************************************************************/
--- Converting board notifications...
/******************************************************************************/

---* {$to_prefix}log_notify
SELECT mem.ID_MEMBER, s.objid AS ID_BOARD
FROM ({$from_prefix}subscribe AS s, {$to_prefix}members AS mem)
WHERE s.type = 8
	AND mem.tempID = s.userid;
---*

/******************************************************************************/
--- Cleaning up...
/******************************************************************************/

ALTER TABLE {$to_prefix}members
DROP COLUMN tempID;

/******************************************************************************/
--- Converting attachments...
/******************************************************************************/

---* {$to_prefix}attachments
---{
$no_add = true;
$keys = array('ID_ATTACH', 'size', 'filename', 'ID_MSG', 'downloads');

$newfilename = getAttachmentFilename($row['filename'], $ID_ATTACH);
if (strlen($newfilename) > 255)
	return;
$fp = @fopen($attachmentUploadDir . '/' . $newfilename, 'wb');
if (!$fp)
	return;

fwrite($fp, $row['file']);
fclose($fp);

$rows[] = "$ID_ATTACH, $row[size], '" . addslashes($row['filename']) . "', $row[ID_MSG], $row[downloads]";
$ID_ATTACH++;
---}
SELECT
	m.id AS ID_MSG, m.downloaded AS downloads, m.att_file_upload AS filename,
	m.attsize AS size, a.file
FROM ({$from_prefix}attach AS a, {$from_prefix}message AS m)
WHERE m.id = a.id;
---*