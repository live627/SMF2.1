/* ATTENTION: You don't need to run or use this file!  The convert.php script does everything for you! */

/******************************************************************************/
---~ name: "Phorum 5"
/******************************************************************************/
---~ version: "SMF 2.0 Alpha"
---~ defines: PHORUM
---~ settings: "/include/db/config.php"
---~ from_prefix: "`{$PHORUM['DBCONFIG']['name']}`.{$PHORUM['DBCONFIG']['table_prefix']}_"
---~ table_test: "{$from_prefix}users"

/******************************************************************************/
--- Converting members...
/******************************************************************************/

TRUNCATE {$to_prefix}members;

---* {$to_prefix}members
SELECT
	user_id AS ID_MEMBER, SUBSTRING(username, 1, 80) AS memberName,
	SUBSTRING(username, 1, 255) AS realName,
	SUBSTRING(password, 1, 64) AS passwd,
	SUBSTRING(email, 1, 255) AS emailAddress, hide_email AS hideEmail,
	date_added AS dateRegistered, date_last_active AS lastLogin,
	IF(hide_activity = 1, 0, 1) AS showOnline, active AS is_activated,
	SUBSTRING(signature, 1, 65534) AS signature, posts,
	IF(admin = 1, 1, 0) AS ID_GROUP, '' AS lngfile, '' AS buddy_list,
	'' AS pm_ignore_list, '' AS messageLabels, '' AS personalText,
	'' AS websiteTitle, '' AS websiteUrl, '' AS location, '' AS ICQ, '' AS AIM,
	'' AS YIM, '' AS MSN, '' AS timeFormat, '' AS avatar, '' AS usertitle,
	'' AS memberIP, '' AS secretQuestion, '' AS secretAnswer,
	'' AS validation_code, '' AS additionalGroups, '' AS smileySet,
	'' AS passwordSalt
FROM {$from_prefix}users;
---*

/******************************************************************************/
--- Converting categories...
/******************************************************************************/

TRUNCATE {$to_prefix}categories;

INSERT INTO {$to_prefix}categories
	(ID_CAT, name)
VALUES
	(1, 'General Category');

/******************************************************************************/
--- Converting boards...
/******************************************************************************/

TRUNCATE {$to_prefix}boards;

DELETE FROM {$to_prefix}board_permissions
WHERE ID_BOARD != 0;

---* {$to_prefix}boards
SELECT
	forum_id AS ID_BOARD, 1 AS ID_CAT, display_order AS boardOrder,
	SUBSTRING(name, 1, 255) AS name,
	SUBSTRING(description, 1, 65534) AS description, thread_count AS numTopics,
	message_count AS numPosts, '-1,0' AS memberGroups
FROM {$from_prefix}forums
GROUP BY forum_id;
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
	m.thread AS ID_TOPIC, m.forum_id AS ID_BOARD, m.message_id AS ID_FIRST_MSG,
	m.user_id AS ID_MEMBER_STARTED, (m.thread_count - 1) AS numReplies,
	m.viewcount AS numViews, IF(m.sort = 1, 1, 0) AS isSticky,
	MAX(m2.message_id) AS ID_LAST_MSG, m.closed AS locked
FROM ({$from_prefix}messages AS m, {$from_prefix}messages AS m2)
WHERE m.message_id = m.thread
	AND m2.thread = m.thread
	AND m.parent_id = 0
GROUP BY m.thread
HAVING ID_FIRST_MSG != 0
	AND ID_LAST_MSG != 0;
---*

---* {$to_prefix}topics (update ID_TOPIC)
SELECT t.ID_TOPIC, m.user_id AS ID_MEMBER_UPDATED
FROM ({$to_prefix}topics AS t, {$from_prefix}messages AS m)
WHERE m.message_id = t.ID_LAST_MSG;
---*

/******************************************************************************/
--- Converting posts (this may take some time)...
/******************************************************************************/

TRUNCATE {$to_prefix}messages;
TRUNCATE {$to_prefix}attachments;

---* {$to_prefix}messages 200
SELECT
	m.message_id AS ID_MSG, m.thread AS ID_TOPIC, m.datestamp AS posterTime,
	m.user_id AS ID_MEMBER, SUBSTRING(m.subject, 1, 255),
	SUBSTRING(IFNULL(u.email, m.email), 1, 255) AS posterEmail,
	SUBSTRING(IFNULL(u.username, m.author), 1, 255) AS posterName,
	m.forum_id AS ID_BOARD,
	SUBSTRING(IF(m.ip = 'localhost', '127.0.0.1', m.ip), 1, 255) AS posterIP,
	SUBSTRING(REPLACE(m.body, '<br>', '<br />'), 1, 65534) AS body,
	'' AS modifiedName, 'xx' AS icon
FROM {$from_prefix}messages AS m
	LEFT JOIN {$from_prefix}users AS u ON (u.user_id = m.user_id);
---*

/******************************************************************************/
--- Removing polls...
/******************************************************************************/

TRUNCATE {$to_prefix}polls;
TRUNCATE {$to_prefix}poll_choices;
TRUNCATE {$to_prefix}log_polls;

/******************************************************************************/
--- Converting personal messages (step 1)...
/******************************************************************************/

TRUNCATE {$to_prefix}personal_messages;

---* {$to_prefix}personal_messages
SELECT
	p.private_message_id AS ID_PM, p.from_user_id AS ID_MEMBER_FROM,
	p.datestamp AS msgtime,
	SUBSTRING(IFNULL(u.username, p.from_username), 1, 255) AS fromName,
	SUBSTRING(p.subject, 1, 255) AS subject,
	SUBSTRING(p.message, 1, 255) AS body, p.from_del_flag AS deletedBySender
FROM {$from_prefix}private_messages AS p
	LEFT JOIN {$from_prefix}users AS u ON (u.user_id = p.from_user_id);
---*

/******************************************************************************/
--- Converting personal messages (step 2)...
/******************************************************************************/

TRUNCATE {$to_prefix}pm_recipients;

---* {$to_prefix}pm_recipients
SELECT
	private_message_id AS ID_PM, to_user_id AS ID_MEMBER, read_flag AS is_read,
	to_del_flag AS deleted, '' AS labels
FROM {$from_prefix}private_messages;
---*

/******************************************************************************/
--- Converting thread suscriptions...
/******************************************************************************/

TRUNCATE {$to_prefix}log_notify;

---* {$to_prefix}log_notify
SELECT user_id AS ID_MEMBER, thread AS ID_TOPIC
FROM {$from_prefix}subscribers;
---*

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

fwrite($fp, base64_decode($row['filedata']));
fclose($fp);

$rows[] = "$ID_ATTACH, " . filesize($attachmentUploadDir . '/' . $newfilename) . ", '" . addslashes($row['filename']) . "', $row[ID_MSG], 0";
$ID_ATTACH++;
---}
SELECT file_data AS filedata, filename AS filename, message_id AS ID_MSG
FROM {$from_prefix}files;
---*