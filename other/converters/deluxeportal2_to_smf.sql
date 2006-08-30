/* ATTENTION: You don't need to run or use this file!  The convert.php script does everything for you! */

/******************************************************************************/
---~ name: "Deluxe Portal 2.0"
/******************************************************************************/
---~ version: "SMF 2.0 Alpha"
---~ settings: "/config.php"
---~ from_prefix: "`$dbname`."
---~ table_test: "{$from_prefix}user"

/******************************************************************************/
--- Converting members...
/******************************************************************************/

TRUNCATE {$to_prefix}members;

ALTER TABLE {$to_prefix}members
CHANGE passwordSalt passwordSalt varchar(16) NOT NULL default '';

---* {$to_prefix}members
SELECT
	userid AS ID_MEMBER, SUBSTRING(name, 1, 80) AS memberName,
	SUBSTRING(name, 1, 255) AS realName, SUBSTRING(password, 1, 64) AS passwd,
	IF(groupid = 1, 1, IF(groupid = 9, 2, 0)) AS ID_GROUP,
	posts, SUBSTRING(title, 1, 255) AS usertitle, joindate AS dateRegistered,
	SUBSTRING(msn, 1, 255) AS MSN, SUBSTRING(email, 1, 255) AS emailAddress,
	SUBSTRING(location, 1, 255) AS location, invisible = 0 AS showOnline,
	hide_email AS hideEmail, SUBSTRING(icq, 1, 255) AS ICQ,
	SUBSTRING(aol, 1, 16) AS AIM, SUBSTRING(yahoo, 1, 32) AS YIM,
	SUBSTRING(IF(website != 'http://', website, ''), 1, 255) AS websiteUrl,
	SUBSTRING(IF(website != 'http://', website, ''), 1, 255) AS websiteTitle,
	SUBSTRING(signature, 1, 65534) AS signature, notify_pm AS pm_email_notify,
	lastactivity AS lastLogin, SUBSTRING(user_salt, 1, 5) AS passwordSalt,
	'' AS lngfile, '' AS buddy_list, '' AS pm_ignore_list, '' AS messageLabels,
	'' AS personalText, '' AS timeFormat, '' AS avatar, '' AS memberIP,
	'' AS secretQuestion, '' AS secretAnswer, '' AS validation_code,
	'' AS additionalGroups, '' AS smileySet
FROM {$from_prefix}user;
---*

/******************************************************************************/
--- Converting categories...
/******************************************************************************/

TRUNCATE {$to_prefix}categories;

---* {$to_prefix}categories
SELECT forumid AS ID_CAT, SUBSTRING(name, 1, 255) AS name, ordered AS catOrder
FROM {$from_prefix}forum
WHERE parentid = 0;
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
	forumid AS ID_BOARD, SUBSTRING(name, 1, 255) AS name,
	parentid AS ID_PARENT, ordered AS boardOrder,
	SUBSTRING(description, 1, 65534) AS description, posts AS numPosts,
	threads AS numTopics, countposts = 0 AS countPosts, '-1,0' AS memberGroups
FROM {$from_prefix}forum
WHERE parentid != 0;
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
	t.threadid AS ID_TOPIC, t.forumid AS ID_BOARD, t.sticky AS isSticky,
	IF(t.poll = '', 0, t.threadid) AS ID_POLL, t.views AS numViews,
	t.userid AS ID_MEMBER_STARTED, t.lastuserid AS ID_MEMBER_UPDATED,
	t.posts - 1 AS numReplies, t.closed AS locked,
	MIN(p.postid) AS ID_FIRST_MSG, t.lastpostid AS ID_LAST_MSG
FROM ({$from_prefix}thread AS t, {$from_prefix}post AS p)
WHERE p.threadid = t.threadid
GROUP BY t.threadid
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
	p.postid AS ID_MSG, p.threadid AS ID_TOPIC, t.forumid AS ID_BOARD,
	p.postdate AS posterTime, p.userid AS ID_MEMBER,
	SUBSTRING(p.ip, 1, 255) AS posterIP,
	SUBSTRING(IF(p.subject = '', CONCAT('Re: ', t.name), p.subject), 1, 255) AS subject,
	SUBSTRING(u.email, 1, 255) AS posterEmail,
	SUBSTRING(p.username, 1, 255) AS posterName,
	p.smilies AS smileysEnabled, p.editedby_date AS modifiedTime,
	SUBSTRING(IF(p.editedby_date = 0, '', p.editedby_username), 1, 255) AS modifiedName,
	SUBSTRING(REPLACE(p.message, '\r', ''), 1, 65534) AS body, 'xx' AS icon
FROM ({$from_prefix}post AS p, {$from_prefix}thread AS t)
	LEFT JOIN {$from_prefix}user AS u ON (u.userid = p.userid)
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
	t.threadid AS ID_POLL, SUBSTRING(t.poll, 1, 255) AS question,
	t.userid AS ID_MEMBER, SUBSTRING(t.username, 1, 255) AS posterName,
	p.postdate + t.poll_days * 86400 AS expireTime
	/* // !!! t.poll_multiple = 1 AS maxVotes */
FROM ({$from_prefix}thread AS t, {$to_prefix}topics AS t2, {$from_prefix}post AS p)
WHERE t.poll != ''
	AND t2.ID_TOPIC = t.threadid
	AND p.postid = t2.ID_FIRST_MSG;
---*

/******************************************************************************/
--- Converting poll options...
/******************************************************************************/

---* {$to_prefix}poll_choices
SELECT
	threadid AS ID_POLL, ordered AS ID_CHOICE,
	SUBSTRING(choice, 1, 255) AS label, votes
FROM {$from_prefix}poll;
---*

/******************************************************************************/
--- Converting poll votes...
/******************************************************************************/

---* {$to_prefix}log_polls
SELECT threadid AS ID_POLL, userid AS ID_MEMBER, choice AS ID_CHOICE
FROM {$from_prefix}whovoted;
---*

/******************************************************************************/
--- Converting personal messages (step 1)...
/******************************************************************************/

TRUNCATE {$to_prefix}personal_messages;

---* {$to_prefix}personal_messages
SELECT
	privatemessageid AS ID_PM, sentdate AS msgtime,
	SUBSTRING(subject, 1, 255) AS subject, fromuserid AS ID_MEMBER_FROM,
	SUBSTRING(fromusername, 1, 255) AS fromName,
	SUBSTRING(REPLACE(message, '\r', ''), 1, 65534) AS body
FROM {$from_prefix}privatemessage
WHERE folder != 'sent';
---*

/******************************************************************************/
--- Converting personal messages (step 2)...
/******************************************************************************/

TRUNCATE {$to_prefix}pm_recipients;

---* {$to_prefix}pm_recipients
SELECT 
	privatemessageid AS ID_PM, touserid AS ID_MEMBER, isread = 1 AS is_read,
	'' AS label
FROM {$from_prefix}privatemessage
WHERE folder != 'sent';
---*

/******************************************************************************/
--- Converting topic notifications...
/******************************************************************************/

TRUNCATE {$to_prefix}log_notify;

---* {$to_prefix}log_notify
SELECT userid AS ID_MEMBER, threadid AS ID_TOPIC
FROM {$from_prefix}subscribedthread;
---*

/******************************************************************************/
--- Converting board notifications...
/******************************************************************************/

---* {$to_prefix}log_notify
SELECT userid AS ID_MEMBER, forumid AS ID_BOARD
FROM {$from_prefix}subscribedforum;
---*

/******************************************************************************/
--- Converting moderators...
/******************************************************************************/

TRUNCATE {$to_prefix}moderators;

---* {$to_prefix}moderators
SELECT userid AS ID_MEMBER, forumid AS ID_BOARD
FROM {$from_prefix}moderator;
---*

/******************************************************************************/
--- Converting topic view logs...
/******************************************************************************/

TRUNCATE {$to_prefix}log_topics;

---* {$to_prefix}log_topics
SELECT mr.threadid AS ID_TOPIC, mr.userid AS ID_MEMBER, p.postdate AS logTime
FROM ({$from_prefix}markread AS mr, {$from_prefix}post AS p)
WHERE p.postid = mr.postid
GROUP BY ID_TOPIC, ID_MEMBER;
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

fwrite($fp, $row['attachment']);
fclose($fp);

$rows[] = "$ID_ATTACH, $row[size], 0, '" . addslashes($row['filename']) . "', $row[ID_MSG]";
$ID_ATTACH++;
---}
SELECT postid AS ID_MSG, attachment, size, name AS filename
FROM {$from_prefix}attachment;
---*