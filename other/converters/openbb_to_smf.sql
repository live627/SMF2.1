/* ATTENTION: You don't need to run or use this file!  The convert.php script does everything for you! */

/******************************************************************************/
---~ name: "OpenBB 1.0.x"
/******************************************************************************/
---~ version: "SMF 2.0 Alpha"
---~ settings: "/lib/sqldata.php"
---~ from_prefix: "`{$database_server['database']}`.{$database_server['prefix']}"
---~ table_test: "{$from_prefix}profiles"

/******************************************************************************/
--- Converting members...
/******************************************************************************/

TRUNCATE {$to_prefix}members;

---* {$to_prefix}members
SELECT
	p.id AS ID_MEMBER, SUBSTRING(p.username, 1, 80) AS memberName,
	SUBSTRING(p.username, 1, 255) AS realName,
	SUBSTRING(p.password, 1, 64) AS passwd,
	SUBSTRING(p.email, 1, 255) AS emailAddress,
	SUBSTRING(homepage, 1, 255) AS websiteUrl,
	SUBSTRING(p.homepagedesc, 1, 255) AS websiteTitle,
	SUBSTRING(p.icq, 1, 255) AS ICQ, SUBSTRING(p.aim, 1, 16) AS AIM,
	SUBSTRING(p.yahoo, 1, 32) AS YIM, SUBSTRING(p.msn, 1, 255) AS MSN,
	SUBSTRING(p.location, 1, 255) AS location, p.showemail = 0 AS hideEmail,
	p.birthdate, IF(ug.isadmin, 1, IF(ug.ismoderator, 2, 0)) AS ID_GROUP,
	p.posts, p.joindate AS dateRegistered, p.timeoffset AS timeOffset,
	SUBSTRING(IF(p.avatar = 'blank.gif', '', p.avatar), 1, 255) AS avatar,
	SUBSTRING(p.custom, 1, 255) AS usertitle, p.invisible = 0 AS showOnline,
	SUBSTRING(p.sig, 1, 65534) AS signature, SUBSTRING(ip, 1, 255) AS memberIP,
	p.lastactive AS lastLogin, '' AS lngfile, '' AS buddy_list,
	'' AS pm_ignore_list, '' AS messageLabels, '' AS personalText,
	'' AS timeFormat, '' AS secretQuestion, '' AS secretAnswer,
	'' AS validation_code, '' AS additionalGroups, '' AS smileySet,
	'' AS passwordSalt
FROM {$from_prefix}profiles AS p
	LEFT JOIN {$from_prefix}usergroup AS ug ON (ug.id = p.id)
WHERE p.username != '';
---*

/******************************************************************************/
--- Converting categories...
/******************************************************************************/

TRUNCATE {$to_prefix}categories;

---* {$to_prefix}categories
SELECT 
	forumid AS ID_CAT, SUBSTRING(title, 1, 255) AS name,
	displayorder AS catOrder
FROM {$from_prefix}forum_display
WHERE type = 1;
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
	forumid AS ID_BOARD, parent AS ID_PARENT, displayorder AS boardOrder,
	SUBSTRING(title, 1, 255) AS name,
	SUBSTRING(description, 1, 65534) AS description, postcount AS numPosts,
	threadcount AS numTopics, dcount AS countPosts, '-1,0' AS memberGroups
FROM {$from_prefix}forum_display
WHERE type IN (3, 6);
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
	t.id AS ID_TOPIC, t.forumid AS ID_BOARD, t.smode AS isSticky, t.locked,
	t.posterid AS ID_MEMBER_STARTED, t.lastposterid AS ID_MEMBER_UPDATED,
	t.replies AS numReplies, t.views AS numViews, t.pollid AS ID_POLL,
	MIN(p.id) AS ID_FIRST_MSG, MAX(p.id) AS ID_LAST_MSG
FROM ({$from_prefix}topics AS t, {$from_prefix}posts AS p)
WHERE p.threadid = t.id
	AND t.totopic = 0
GROUP BY ID_TOPIC
HAVING ID_FIRST_MSG != 0
	AND ID_LAST_MSG != 0;
---*

/******************************************************************************/
--- Converting posts (this may take some time)...
/******************************************************************************/

TRUNCATE {$to_prefix}messages;
TRUNCATE {$to_prefix}attachments;

---* {$to_prefix}messages 200
---{
$row['subject'] = stripslashes($row['subject']);
$row['body'] = stripslashes($row['body']);
---}
SELECT
	p.id AS ID_MSG, p.threadid AS ID_TOPIC,
	SUBSTRING(p.poster, 1, 255) AS posterName,
	SUBSTRING(p.title, 1, 255) AS subject, p.dateline AS posterTime,
	p.lastupdate AS modifiedTime,
	SUBSTRING(p.lastupdateby, 1, 255) AS modifiedName, p.forumid AS ID_BOARD,
	m.id AS ID_MEMBER, p.dsmiley = 0 AS smileysEnabled,
	SUBSTRING(p.ip, 1, 255) AS posterIP,
	SUBSTRING(m.email, 1, 255) AS posterEmail,
	SUBSTRING(REPLACE(p.message, '\n', '<br />'), 1, 65534) AS body,
	'xx' AS icon
FROM {$from_prefix}posts AS p
	LEFT JOIN {$from_prefix}profiles AS m ON (BINARY m.username = p.poster);
---*

/******************************************************************************/
--- Converting polls...
/******************************************************************************/

TRUNCATE {$to_prefix}polls;
TRUNCATE {$to_prefix}poll_choices;
TRUNCATE {$to_prefix}log_polls;

---* {$to_prefix}polls
SELECT
	p.id AS ID_POLL, SUBSTRING(t.title, 1, 255) AS question,
	t.posterid AS ID_MEMBER, SUBSTRING(t.poster, 1, 255) AS posterName
FROM ({$from_prefix}polls AS p, {$from_prefix}topics AS t)
WHERE t.pollid = p.id;
---*

/******************************************************************************/
--- Converting poll options...
/******************************************************************************/

---* {$to_prefix}poll_choices
---{
$no_add = true;
$keys = array('ID_POLL', 'ID_CHOICE', 'label', 'votes');

for ($i = 1; $i <= 10; $i++)
{
	if (trim($row['option' . $i]) != '')
		$rows[] = "$row[ID_POLL], $i, SUBSTRING('" . addslashes($row['option' . $i]) . "', 1, 255), " . $row['answer' . $i];
}
---}
SELECT
	id AS ID_POLL, option1, option2, option3, option4, option5, option6,
	option7, option8, option9, option10, answer1, answer2, answer3, answer4,
	answer5, answer6, answer7, answer8, answer9, answer10
FROM {$from_prefix}polls;
---*

/******************************************************************************/
--- Converting poll votes...
/******************************************************************************/

---* {$to_prefix}log_polls
SELECT p.id AS ID_POLL, m.id AS ID_MEMBER, 0 AS ID_CHOICE
FROM ({$from_prefix}polls AS p, {$from_prefix}profiles AS m)
WHERE FIND_IN_SET(m.username, p.total);
---*

/******************************************************************************/
--- Converting personal messages (step 1)...
/******************************************************************************/

TRUNCATE {$to_prefix}personal_messages;

---* {$to_prefix}personal_messages
---{
$row['subject'] = substr(stripslashes($row['subject']), 0, 255);
$row['body'] = substr(stripslashes($row['body']), 0, 65534);
---}
SELECT
	id AS ID_PM, userid AS ID_MEMBER_FROM, time AS msgtime,
	SUBSTRING(send, 1, 255) AS fromName, subject,
	REPLACE(message, '\n', '<br />') AS body
FROM {$from_prefix}pmsg
WHERE box != 'outbox';
---*

/******************************************************************************/
--- Converting personal messages (step 2)...
/******************************************************************************/

TRUNCATE {$to_prefix}pm_recipients;

---* {$to_prefix}pm_recipients
SELECT pm.id AS ID_PM, m.id AS ID_MEMBER, isread AS is_read, '' AS labels
FROM ({$from_prefix}pmsg AS pm, {$from_prefix}profiles AS m)
WHERE pm.box != 'outbox'
	AND m.username = pm.accept;
---*

/******************************************************************************/
--- Converting topic notifications...
/******************************************************************************/

TRUNCATE {$to_prefix}log_notify;

---* {$to_prefix}log_notify
SELECT m.id AS ID_MEMBER, threadid AS ID_TOPIC, visit AS sent
FROM ({$from_prefix}favorites AS f, {$from_prefix}profiles AS m)
WHERE BINARY m.username = f.username
	AND f.email = 1;
---*

/******************************************************************************/
--- Converting moderators...
/******************************************************************************/

TRUNCATE {$to_prefix}moderators;

---* {$to_prefix}moderators
SELECT modid AS ID_MEMBER, forumid AS ID_BOARD
FROM {$from_prefix}moderators;
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

fwrite($fp, $row['filecontent']);
fclose($fp);

$rows[] = "$ID_ATTACH, " . filesize($attachmentUploadDir . '/' . $newfilename) . ", '" . addslashes($row['filename']) . "', $row[ID_MSG], $row[downloads]";
$ID_ATTACH++;
---}
SELECT p.id AS ID_MSG, a.filecontent, a.downloaded AS downloads, a.filename
FROM ({$from_prefix}attachments AS a, {$from_prefix}posts AS p)
WHERE p.attachid = a.id;
---*