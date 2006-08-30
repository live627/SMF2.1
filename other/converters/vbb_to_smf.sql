/* ATTENTION: You don't need to run or use this file!  The convert.php script does everything for you! */

/******************************************************************************/
---~ name: "versatileBulletinBoard 1.0.0"
/******************************************************************************/
---~ version: "SMF 2.0 Alpha"
---~ settings: "/admin/config.inc.php", "/admin/dbstart.php"
---~ from_prefix: "`$databasename`.{$dbprefix}_"
---~ table_test: "{$from_prefix}user"

/******************************************************************************/
--- Converting members...
/******************************************************************************/

TRUNCATE {$to_prefix}members;

---* {$to_prefix}members
---{
$row['signature'] = substr(stripslashes($row['signature']), 0, 65534);
---}
SELECT
	u.ID AS ID_MEMBER, SUBSTRING(u.name, 1, 80) AS memberName,
	SUBSTRING(u.name, 1, 255) AS realName, SUBSTRING(u.pass, 1, 64) AS passwd,
	SUBSTRING(u.email, 1, 255) AS emailAddress,
	SUBSTRING(u.web, 1, 255) AS websiteTitle,
	SUBSTRING(u.web, 1, 255) AS websiteUrl,
	UNIX_TIMESTAMP(u.registered) AS dateRegistered,
	SUBSTRING(u.icq, 1, 255) AS ICQ, SUBSTRING(u.aim, 1, 16) AS AIM,
	SUBSTRING(u.yahoo, 1, 32) AS YIM, SUBSTRING(u.msn, 1, 255) AS MSN,
	u.numposts AS posts, SUBSTRING(u.comment, 1, 255) AS personalText,
	u.signature, UNIX_TIMESTAMP(u.lastlogin) AS lastLogin,
	SUBSTRING(u.last_IP, 1, 255) AS memberIP,
	u.show_email != 'yes' AS hideEmail, u.birthday AS birthdate,
	CASE u.gender WHEN 'M' THEN 1 WHEN 'F' THEN 2 ELSE 0 END AS gender,
	IF(ul.level = 5, 1, 0) AS ID_GROUP
FROM {$from_prefix}user AS u
	LEFT JOIN {$from_prefix}userlevel AS ul ON (ul.user_ID = u.ID)
WHERE pass != 'impossible'
GROUP BY u.ID;
---*

/******************************************************************************/
--- Converting categories...
/******************************************************************************/

TRUNCATE {$to_prefix}categories;

---* {$to_prefix}categories
SELECT ID AS ID_CAT, corder AS catOrder, SUBSTRING(name, 1, 255) AS name
FROM {$from_prefix}category;
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
	ID AS ID_BOARD, forder AS boardOrder, category_ID AS ID_CAT,
	SUBSTRING(name, 1, 255) AS name, SUBSTRING(comment, 1, 65534) AS description,
	numposts AS numPosts, numthreads AS numTopics, parent AS ID_PARENT,
	'-1,0' AS memberGroups
FROM {$from_prefix}forum;
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
	t.ID AS ID_TOPIC, t.forum_ID AS ID_BOARD, t.closed != 'false' AS locked,
	t.user_ID AS ID_MEMBER_STARTED, t.numviews AS numViews, pt.ID AS ID_POLL,
	t.numreplies AS numReplies, t.fixed != 'false' AS isSticky,
	t.ID AS ID_FIRST_MSG, IFNULL(MAX(r.ID), t.ID) AS ID_LAST_MSG
FROM {$from_prefix}message AS t
	LEFT JOIN {$from_prefix}message AS r ON (r.reply = t.ID)
	LEFT JOIN {$from_prefix}mod_POLL_topic AS pt ON (pt.message_ID = t.ID)
WHERE t.reply = 0
	AND t.movelink = 0
GROUP BY t.ID
HAVING ID_FIRST_MSG != 0
	AND ID_LAST_MSG != 0;
---*

---* {$to_prefix}topics (update ID_TOPIC)
SELECT t.ID_TOPIC, m.user_ID AS ID_MEMBER_UPDATED
FROM ({$to_prefix}topics AS t, {$from_prefix}message AS m)
WHERE m.ID = t.ID_LAST_MSG;
---*

/******************************************************************************/
--- Converting posts (this may take some time)...
/******************************************************************************/

TRUNCATE {$to_prefix}messages;
TRUNCATE {$to_prefix}attachments;

---* {$to_prefix}messages 200
---{
$row['body'] = substr(stripslashes($row['body']), 0, 65534);
---}
SELECT
	m.ID AS ID_MSG, IF(m.reply = 0, m.ID, m.reply) AS ID_TOPIC,
	m.forum_ID AS ID_BOARD, UNIX_TIMESTAMP(m.date) AS posterTime,
	m.user_ID AS ID_MEMBER, SUBSTRING(m.user_IP, 1, 255) AS posterIP,
	SUBSTRING(m.subject, 1, 255) AS subject,
	SUBSTRING(u.email, 1, 255) AS posterEmail,
	SUBSTRING(IFNULL(u.name, m.guestname), 1, 255) AS posterName,
	SUBSTRING(REPLACE(m.content, '\r', ''), 1, 65534) AS body
FROM {$from_prefix}message AS m
	LEFT JOIN {$from_prefix}user AS u ON (u.ID = m.user_ID);
---*

/******************************************************************************/
--- Converting polls...
/******************************************************************************/

TRUNCATE {$to_prefix}polls;
TRUNCATE {$to_prefix}poll_choices;
TRUNCATE {$to_prefix}log_polls;

---* {$to_prefix}polls
SELECT
	pt.ID AS ID_POLL, SUBSTRING(pt.name, 1, 255) AS question,
	pt.creator AS ID_MEMBER, pt.active != 'true' AS votingLocked,
	SUBSTRING(u.name, 1, 255) AS posterName
FROM {$from_prefix}mod_POLL_topic AS pt
	LEFT JOIN {$from_prefix}user AS u ON (u.ID = pt.creator);
---*

/******************************************************************************/
--- Converting poll options...
/******************************************************************************/

ALTER TABLE {$to_prefix}poll_choices
ADD COLUMN tempID int(10) unsigned NOT NULL default 0;

---* {$to_prefix}poll_choices
---{
if (!isset($_SESSION['convert_last_poll']) || $_SESSION['convert_last_poll'] != $row['ID_POLL'])
{
	$_SESSION['convert_last_poll'] = $row['ID_POLL'];
	$_SESSION['convert_last_choice'] = 0;
}

$row['ID_CHOICE'] = ++$_SESSION['convert_last_choice'];
---}
/* Its name for the ID_POLL is misleading, but right. */
SELECT
	po.topic_ID AS ID_POLL, 0 AS ID_CHOICE,
	SUBSTRING(po.option_name, 1, 255) AS label, COUNT(pv.ID) AS votes,
	po.ID AS tempID
FROM {$from_prefix}mod_POLL_option AS po
	LEFT JOIN {$from_prefix}mod_POLL_vote AS pv ON (pv.xoption = po.ID)
GROUP BY po.ID
ORDER BY po.ID;
---*

/******************************************************************************/
--- Converting poll votes...
/******************************************************************************/

---* {$to_prefix}log_polls
SELECT pv.poll_ID AS ID_POLL, pv.user_ID AS ID_MEMBER, pc.ID_CHOICE
FROM ({$from_prefix}mod_POLL_vote AS pv, {$to_prefix}poll_choices AS pc)
WHERE pv.user_ID != 0
	AND pc.tempID = pv.xoption;
---*

ALTER TABLE {$to_prefix}poll_choices
DROP COLUMN tempID;

/******************************************************************************/
--- Converting personal messages (step 1)...
/******************************************************************************/

TRUNCATE {$to_prefix}personal_messages;

---* {$to_prefix}personal_messages
---{
$row['body'] = stripslashes($row['body']);
---}
SELECT
	pm.ID AS ID_PM, pm.from_user AS ID_MEMBER_FROM,
	SUBSTRING(pm.subject, 1, 255) AS subject,
	UNIX_TIMESTAMP(pm.date) AS msgtime,
	SUBSTRING(u.name, 1, 255) AS fromName,
	SUBSTRING(REPLACE(pm.body, '\r', ''), 1, 65534) AS body
FROM {$from_prefix}pm AS pm
	LEFT JOIN {$from_prefix}user AS u ON (u.ID = pm.from_user);
---*

/******************************************************************************/
--- Converting personal messages (step 2)...
/******************************************************************************/

TRUNCATE {$to_prefix}pm_recipients;

---* {$to_prefix}pm_recipients
SELECT
	ID AS ID_PM, to_user AS ID_MEMBER, b_read != 'no' AS is_read, '' AS labels
FROM {$from_prefix}pm;
---*

/******************************************************************************/
--- Converting board notifications...
/******************************************************************************/

TRUNCATE {$to_prefix}log_notify;

---* {$to_prefix}log_notify
SELECT user_ID AS ID_MEMBER, forum_ID AS ID_BOARD
FROM {$from_prefix}subs;
---*

/******************************************************************************/
--- Converting attachments...
/******************************************************************************/

---* {$to_prefix}attachments
---{
$no_add = true;
$keys = array('ID_ATTACH', 'size', 'filename', 'ID_MSG', 'downloads');

$newfilename = getAttachmentFilename($row['filename'], $ID_ATTACH);
if (strlen($newfilename) <= 255 && copy($_POST['path_from'] . '/attachments/' . $row['filename'], $attachmentUploadDir . '/' . $newfilename))
{
	$rows[] = "$ID_ATTACH, $row[size], '" . addslashes($row['filename']) . "', $row[ID_MSG], $row[downloads]";

	$ID_ATTACH++;
}
---}
SELECT message_ID AS ID_MSG, filename, size, downloads
FROM {$from_prefix}attachment;
---*