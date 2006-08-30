/* ATTENTION: You don't need to run or use this file!  The convert.php script does everything for you! */

/******************************************************************************/
---~ name: "IkonBoard 3.1.x"
/******************************************************************************/
---~ version: "SMF 2.0 Alpha"
---~ parameters: ib_database text=MySQL database used by IkonBoard
---~ parameters: ib_prefix text=Prefix used by IkonBoard
---~ parameters: ib_uploads text=Path to the uploads directory
---~ from_prefix: "`$ib_database`.$ib_prefix"
---~ table_test: "{$from_prefix}member_profiles"

/******************************************************************************/
--- Converting members...
/******************************************************************************/

TRUNCATE {$to_prefix}members;

ALTER TABLE {$to_prefix}members
DROP COLUMN tempID;

ALTER TABLE {$to_prefix}members
ADD COLUMN tempID varchar(32),
ADD INDEX tempID (tempID(32));

---* {$to_prefix}members
SELECT
	SUBSTRING(MEMBER_NAME, 1, 80) AS memberName,
	IF(MEMBER_GROUP = 4, 1, 0) AS ID_GROUP,
	SUBSTRING(MEMBER_PASSWORD, 1, 64) AS passwd,
	SUBSTRING(MEMBER_EMAIL, 1, 255) AS emailAddress,
	MEMBER_JOINED AS dateRegistered, SUBSTRING(MEMBER_IP, 1, 255) AS memberIP,
	SUBSTRING(IF(MEMBER_AVATAR = 'noavatar' OR INSTR(MEMBER_AVATAR, 'upload') != 0, '', MEMBER_AVATAR), 1, 255) AS avatar,
	MEMBER_POSTS AS posts, SUBSTRING(AOLNAME, 1, 16) AS AIM,
	SUBSTRING(ICQNUMBER, 1, 255) AS ICQ,
	SUBSTRING(LOCATION, 1, 255) AS location,
	SUBSTRING(REPLACE(SIGNATURE, '<br>', '<br />'), 1, 65534) AS signature,
	SUBSTRING(WEBSITE, 1, 255) AS websiteUrl,
	SUBSTRING(WEBSITE, 1, 255) AS websiteTitle,
	SUBSTRING(YAHOONAME, 1, 32) AS YIM,
	SUBSTRING(MEMBER_TITLE, 1, 255) AS personalText,
	ALLOW_ADMIN_EMAILS AS notifyAnnouncements,
	TIME_ADJUST AS timeOffset, HIDE_EMAIL AS hideEmail,
	SUBSTRING(MSNNAME, 1, 255) AS MSN, LAST_ACTIVITY AS lastLogin,
	GENDER AS gender, SUBSTRING(MEMBER_NAME, 1, 255) AS realName,
	MEMBER_ID AS tempID, '' AS lngfile, '' AS buddy_list, '' AS pm_ignore_list,
	'' AS messageLabels, '' AS timeFormat, '' AS usertitle,
	'' AS secretQuestion, '' AS secretAnswer, '' AS validation_code,
	'' AS additionalGroups, '' AS smileySet, '' AS passwordSalt
FROM {$from_prefix}member_profiles;
---*

/******************************************************************************/
--- Converting categories...
/******************************************************************************/

TRUNCATE {$to_prefix}categories;

---* {$to_prefix}categories
SELECT 
	CAT_ID AS ID_CAT, SUBSTRING(CAT_NAME, 1, 255) AS name, CAT_POS AS catOrder
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
	FORUM_ID AS ID_BOARD, FORUM_TOPICS AS numTopics, FORUM_POSTS AS numPosts,
	SUBSTRING(FORUM_NAME, 1, 255) AS name,
	SUBSTRING(FORUM_DESC, 1, 65534) AS description,
	FORUM_POSITION AS boardOrder, CATEGORY AS ID_CAT, '-1,0' AS memberGroups
FROM {$from_prefix}forum_info;
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
	t.TOPIC_ID AS ID_TOPIC, t.TOPIC_STATE = 'closed' AS locked,
	t.TOPIC_POSTS AS numReplies, memf.ID_MEMBER AS ID_MEMBER_STARTED,
	meml.ID_MEMBER AS ID_MEMBER_UPDATED, pl.ID AS ID_POLL,
	t.TOPIC_VIEWS AS numViews, t.FORUM_ID AS ID_BOARD,
	t.PIN_STATE AS isSticky, MIN(p.POST_ID) AS ID_FIRST_MSG,
	MAX(p.POST_ID) AS ID_LAST_MSG
FROM ({$from_prefix}forum_topics AS t, {$from_prefix}forum_posts AS p)
	LEFT JOIN {$from_prefix}forum_polls AS pl ON (pl.POLL_ID = t.TOPIC_ID)
	LEFT JOIN {$to_prefix}members AS memf ON (memf.tempID = t.TOPIC_STARTER)
	LEFT JOIN {$to_prefix}members AS meml ON (meml.tempID = t.TOPIC_LAST_POSTER)
WHERE p.TOPIC_ID = t.TOPIC_ID
	AND t.MOVED_TO IS NULL
GROUP BY t.TOPIC_ID
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
	p.POST_ID AS ID_MSG, mem.ID_MEMBER, p.ENABLE_EMO AS smileysEnabled,
	SUBSTRING(p.IP_ADDR, 1, 255) AS posterIP, p.POST_DATE AS posterTime,
	SUBSTRING(t.TOPIC_TITLE, 1, 255) AS subject,
	SUBSTRING(REPLACE(p.POST, '<br>', '<br />'), 1, 65534) AS body,
	p.TOPIC_ID AS ID_TOPIC, p.FORUM_ID AS ID_BOARD,
	SUBSTRING(mem.memberName, 1, 255) AS posterName,
	SUBSTRING(mem.emailAddress, 1, 255) AS posterEmail
FROM {$from_prefix}forum_posts AS p
	LEFT JOIN {$from_prefix}forum_topics AS t ON (t.TOPIC_ID = p.TOPIC_ID)
	LEFT JOIN {$to_prefix}members AS mem ON (mem.tempID = p.AUTHOR);
---*

/******************************************************************************/
--- Converting polls...
/******************************************************************************/

TRUNCATE {$to_prefix}polls;
TRUNCATE {$to_prefix}poll_choices;
TRUNCATE {$to_prefix}log_polls;

---* {$to_prefix}polls
SELECT
	p.ID AS ID_POLL, SUBSTRING(p.POLL_TITLE, 1, 255) AS question,
	mem.ID_MEMBER, SUBSTRING(p.POLL_STARTER_N, 1, 255) AS posterName
FROM {$from_prefix}forum_polls AS p
	LEFT JOIN {$to_prefix}members AS mem ON (mem.tempID = p.POLL_STARTER);
---*

/******************************************************************************/
--- Converting poll options...
/******************************************************************************/

---* {$to_prefix}poll_choices
---{
$no_add = true;
$keys = array('ID_POLL', 'ID_CHOICE', 'label', 'votes');

preg_match_all('/(\d+)~::~<!--\\1-->(.+?)~=~(\d+)\|/', $row['choices'], $choices);
foreach ($choices[1] as $i => $ID_CHOICE)
	$rows[] = "$row[ID_POLL], " . ($ID_CHOICE + 1) . ", SUBSTRING('" . addslashes($choices[2][$i]) . "', 1, 255), " . $choices[3][$i];
---}
SELECT ID AS ID_POLL, POLL_ANSWERS AS choices
FROM {$from_prefix}forum_polls;
---*

/******************************************************************************/
--- Converting poll logs...
/******************************************************************************/

---* {$to_prefix}log_polls
SELECT pl.ID AS ID_POLL, mem.ID_MEMBER
FROM ({$from_prefix}forum_poll_voters AS v, {$from_prefix}forum_polls AS pl, {$to_prefix}members AS mem)
WHERE pl.POLL_ID = v.POLL_ID
	AND mem.tempID = v.MEMBER_ID;
---*

/******************************************************************************/
--- Converting personal messages (step 1)...
/******************************************************************************/

TRUNCATE {$to_prefix}personal_messages;

---* {$to_prefix}personal_messages
SELECT
	pm.MESSAGE_ID AS ID_PM, pm.DATE AS msgtime,
	SUBSTRING(pm.TITLE, 1, 255) AS subject,
	SUBSTRING(REPLACE(pm.message, '<br>', '<br />'), 1, 65534) AS body,
	SUBSTRING(pm.FROM_NAME, 1, 255) AS fromName,
	mem.ID_MEMBER AS ID_MEMBER_FROM
FROM {$from_prefix}message_data AS pm
	LEFT JOIN {$to_prefix}members AS mem ON (mem.tempID = pm.FROM_ID)
WHERE pm.VIRTUAL_DIR = 'in';
---*

/******************************************************************************/
--- Converting personal messages (step 2)...
/******************************************************************************/

TRUNCATE {$to_prefix}pm_recipients;

---* {$to_prefix}pm_recipients
SELECT
	pm.MESSAGE_ID AS ID_PM, mem.ID_MEMBER,
	(pm.READ_STATE = 1) | (pm.REPLY << 1) AS is_read,
	'' AS labels
FROM ({$from_prefix}message_data AS pm, {$to_prefix}members AS mem)
WHERE pm.VIRTUAL_DIR = 'in'
	AND mem.tempID = pm.RECIPIENT_ID;
---*

/******************************************************************************/
--- Converting topic notifications...
/******************************************************************************/

TRUNCATE {$to_prefix}log_notify;

---* {$to_prefix}log_notify
SELECT mem.ID_MEMBER, s.TOPIC_ID AS ID_TOPIC, tv.SENT AS sent
FROM ({$from_prefix}forum_subscriptions AS s, {$to_prefix}members AS mem)
	LEFT JOIN {$from_prefix}topic_views AS tv ON (tv.TOPIC_ID = s.TOPIC_ID AND tv.MEMBER_ID = s.MEMBER_ID)
WHERE s.TOPIC_ID != 0
	AND mem.tempId = s.MEMBER_ID;
---*

/******************************************************************************/
--- Converting board notifications...
/******************************************************************************/

---* {$to_prefix}log_notify
SELECT mem.ID_MEMBER, s.FORUM_ID AS ID_BOARD
FROM ({$from_prefix}forum_subscriptions AS s, {$to_prefix}members AS mem)
WHERE s.TOPIC_ID = 0
	AND mem.tempId = s.MEMBER_ID;
---*

/******************************************************************************/
--- Converting moderators...
/******************************************************************************/

TRUNCATE {$to_prefix}moderators;

---* {$to_prefix}moderators
SELECT mem.ID_MEMBER, mods.FORUM_ID AS ID_BOARD
FROM ({$from_prefix}forum_moderators AS mods, {$to_prefix}members AS mem)
WHERE mem.tempID = mods.MEMBER_ID;
---*

/******************************************************************************/
--- Converting topic view logs...
/******************************************************************************/

TRUNCATE {$to_prefix}log_topics;

---* {$to_prefix}log_topics
SELECT tv.TOPIC_ID AS ID_TOPIC, mem.ID_MEMBER, tv.VIEWED AS logTime
FROM ({$from_prefix}topic_views AS tv, {$to_prefix}members AS mem)
WHERE mem.tempID = tv.MEMBER_ID;
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

$real_filename = preg_replace('~^post-\d+-\d+-~', '', $row['filename']);
$newfilename = getAttachmentFilename($real_filename, $ID_ATTACH);
if (strlen($newfilename) <= 255 && copy($ib_uploads . '/' . $row['filename'], $attachmentUploadDir . '/' . $newfilename))
{
	$rows[] = "$ID_ATTACH, " . filesize($attachmentUploadDir . '/' . $newfilename) . ", '" . addslashes($real_filename) . "', $row[ID_MSG], $row[downloads]";

	$ID_ATTACH++;
}
---}
SELECT
	p.POST_ID AS ID_MSG, p.ATTACH_ID AS oldEncrypt, p.ATTACH_HITS AS downloads,
	a.FILE_NAME AS filename
FROM ({$from_prefix}forum_posts AS p, {$from_prefix}attachments AS a)
WHERE a.ID = p.ATTACH_ID;
---*