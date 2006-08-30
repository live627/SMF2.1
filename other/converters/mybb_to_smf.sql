/* ATTENTION: You don't need to run or use this file!  The convert.php script does everything for you! */

/******************************************************************************/
---~ name: "MyBulletinBoard 1.0"
/******************************************************************************/
---~ version: "SMF 2.0 Alpha"
---~ settings: "/inc/config.php"
---~ globals: config
---~ from_prefix: "`{$config['database']}`.{$config['table_prefix']}"
---~ table_test: "{$from_prefix}users"

/******************************************************************************/
--- Converting members...
/******************************************************************************/

TRUNCATE {$to_prefix}members;

---* {$to_prefix}members
SELECT
	uid AS ID_MEMBER, SUBSTRING(username, 1, 255) AS memberName,
	SUBSTRING(username, 1, 255) AS realName,
	SUBSTRING(password, 1, 64) AS passwd, email AS emailAddress,
	postnum AS posts, SUBSTRING(usertitle, 1, 255) AS usertitle,
	lastvisit AS lastLogin, IF(usergroup = 4, 1, 0) AS ID_GROUP,
	regdate AS dateRegistered, SUBSTRING(website, 1, 255) AS websiteUrl,
	SUBSTRING(website, 1, 255) AS websiteTitle,
	SUBSTRING(icq, 1, 255) AS ICQ, SUBSTRING(aim, 1, 16) AS AIM,
	SUBSTRING(yahoo, 1, 32) AS YIM, SUBSTRING(msn AS MSN, 1, 255) AS MSN,
	SUBSTRING(signature, 1, 65534) AS signature, hideemail AS hideEmail, 
	SUBSTRING(buddylist, 1, 255) AS buddy_list,
	SUBSTRING(regip, 1, 255) AS memberIP,
	SUBSTRING(ignorelist, 1, 255) AS pm_ignore_list,
	timeonline AS totalTimeLoggedIn,
	IF(birthday = '', '0001-01-01', CONCAT_WS('-', RIGHT(birthday, 4), SUBSTRING(birthday, LOCATE('-', birthday) + 1, LOCATE('-', birthday, LOCATE('-', birthday) + 1) - LOCATE('-', birthday) - 1), LEFT(birthday, LOCATE('-', birthday) - 1))) AS birthdate
FROM {$from_prefix}users;
---*

/******************************************************************************/
--- Converting categories...
/******************************************************************************/

TRUNCATE {$to_prefix}categories;

---* {$to_prefix}categories
SELECT fid AS ID_CAT, SUBSTRING(name, 1, 255) AS name, disporder AS catOrder
FROM {$from_prefix}forums
WHERE type = 'c';
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
	fid AS ID_BOARD, SUBSTRING(name, 1, 255) AS name, 
	SUBSTRING(description, 1, 65534) AS description, disporder AS boardOrder,
	posts AS numPosts, threads AS numTopics, pid AS ID_PARENT,
	usepostcounts != 'yes' AS countPosts, '-1,0' AS memberGroups
FROM {$from_prefix}forums
WHERE type = 'f';
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
	t.tid AS ID_TOPIC, t.fid AS ID_BOARD, t.sticky AS isSticky,
	t.poll AS ID_POLL, t.views AS numViews, t.uid AS ID_MEMBER_STARTED,
	ul.uid AS ID_MEMBER_UPDATED, t.replies AS numReplies, t.closed AS locked,
	MIN(p.pid) AS ID_FIRST_MSG, MAX(p.pid) AS ID_LAST_MSG
FROM ({$from_prefix}threads AS t, {$from_prefix}posts AS p)
	LEFT JOIN {$from_prefix}users AS ul ON (BINARY ul.username = t.lastposter)
WHERE p.tid = t.tid
GROUP BY t.tid
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
	p.pid AS ID_MSG, p.tid AS ID_TOPIC, t.fid AS ID_BOARD, p.uid AS ID_MEMBER,
	SUBSTRING(p.username, 1, 255) AS posterName, p.dateline AS posterTime,
	SUBSTRING(p.ipaddress, 1, 255) AS posterIP,
	SUBSTRING(IF(p.subject = '', t.subject, p.subject), 1, 255) AS subject,
	SUBSTRING(u.email, 1, 255) AS posterEmail, 
	p.smilieoff = 'no' AS smileysEnabled,
	SUBSTRING(edit_u.username, 1, 255) AS modifiedName,
	p.edittime AS modifiedTime,
	SUBSTRING(REPLACE(p.message, '<br>', '<br />'), 1, 65534) AS body,
	'xx' AS icon
FROM ({$from_prefix}posts AS p, {$from_prefix}threads AS t)
	LEFT JOIN {$from_prefix}users AS u ON (u.uid = p.uid)
	LEFT JOIN {$from_prefix}users AS edit_u ON (edit_u.uid = p.edituid)
WHERE t.tid = p.tid;
---*

/******************************************************************************/
--- Converting polls...
/******************************************************************************/

TRUNCATE {$to_prefix}polls;
TRUNCATE {$to_prefix}poll_choices;
TRUNCATE {$to_prefix}log_polls;

---* {$to_prefix}polls
SELECT
	p.pid AS ID_POLL, SUBSTRING(p.question, 1, 255), p.closed AS votingLocked,
	t.uid AS ID_MEMBER,
	IF(p.timeout = 0, 0, p.dateline + p.timeout * 86400) AS expireTime,
	SUBSTRING(t.username, 1, 255) AS posterName
FROM {$from_prefix}polls AS p
	LEFT JOIN {$from_prefix}threads AS t ON (t.tid = p.tid);
---*

/******************************************************************************/
--- Converting poll options...
/******************************************************************************/

---* {$to_prefix}poll_choices
---{
$no_add = true;
$keys = array('ID_POLL', 'ID_CHOICE', 'label', 'votes');

$options = explode('||~|~||', $row['options']);
$votes = explode('||~|~||', $row['votes']);
for ($i = 0, $n = count($options); $i < $n; $i++)
	$rows[] = $row['ID_POLL'] . ', ' . ($i + 1) . ", SUBSTRING('" . addslashes($options[$i]) . "', 1, 255), '" . $votes[$i] . "'";
---}
SELECT pid AS ID_POLL, options, votes
FROM {$from_prefix}polls;
---*

/******************************************************************************/
--- Converting poll votes...
/******************************************************************************/

---* {$to_prefix}log_polls
SELECT pid AS ID_POLL, uid AS ID_MEMBER, voteoption AS ID_CHOICE
FROM {$from_prefix}pollvotes;
---*

/******************************************************************************/
--- Converting personal messages (step 1)...
/******************************************************************************/

TRUNCATE {$to_prefix}personal_messages;

---* {$to_prefix}personal_messages
SELECT
	pm.pmid AS ID_PM, pm.fromid AS ID_MEMBER_FROM, pm.dateline AS msgtime,
	SUBSTRING(uf.username, 1, 255) AS fromName,
	SUBSTRING(pm.subject, 1, 255) AS subject,
	SUBSTRING(REPLACE(pm.message, '<br>', '<br />'), 1, 65534) AS body
FROM {$from_prefix}privatemessages AS pm
	LEFT JOIN {$from_prefix}users AS uf ON (uf.uid = pm.fromid)
WHERE pm.folder != 2;
---*

/******************************************************************************/
--- Converting personal messages (step 2)...
/******************************************************************************/

TRUNCATE {$to_prefix}pm_recipients;

---* {$to_prefix}pm_recipients
SELECT pmid AS ID_PM, toid AS ID_MEMBER, readtime != 0 AS is_read, '' AS labels
FROM {$from_prefix}privatemessages
WHERE folder != 2;
---*

/******************************************************************************/
--- Converting topic notifications...
/******************************************************************************/

TRUNCATE {$to_prefix}log_notify;

---* {$to_prefix}log_notify
SELECT uid AS ID_MEMBER, tid AS ID_TOPIC
FROM {$from_prefix}favorites;
---*

/******************************************************************************/
--- Converting board notifications...
/******************************************************************************/

---* {$to_prefix}log_notify
SELECT uid AS ID_MEMBER, fid AS ID_BOARD
FROM {$from_prefix}forumsubscriptions;
---*

/******************************************************************************/
--- Converting censored words...
/******************************************************************************/

DELETE FROM {$to_prefix}settings
WHERE variable IN ('censor_vulgar', 'censor_proper');

---# Moving censored words...
---{
$result = convert_query("
	SELECT badword, replacement
	FROM {$from_prefix}badwords");
$censor_vulgar = array();
$censor_proper = array();
while ($row = mysql_fetch_assoc($result))
{
	$censor_vulgar[] = $row['badword'];
	$censor_proper[] = $row['replacement'];
}
mysql_free_result($result);

$censored_vulgar = addslashes(implode("\n", $censor_vulgar));
$censored_proper = addslashes(implode("\n", $censor_proper));

convert_query("
	REPLACE INTO {$to_prefix}settings
		(variable, value)
	VALUES ('censor_vulgar', '$censored_vulgar'),
		('censor_proper', '$censored_proper')");
---}
---#

/******************************************************************************/
--- Converting moderators...
/******************************************************************************/

TRUNCATE {$to_prefix}moderators;

---* {$to_prefix}moderators
SELECT uid AS ID_MEMBER, fid AS ID_BOARD
FROM {$from_prefix}moderators;
---*

/******************************************************************************/
--- Converting topic view logs...
/******************************************************************************/

TRUNCATE {$to_prefix}log_topics;

---* {$to_prefix}log_topics
SELECT tid AS ID_TOPIC, uid AS ID_MEMBER, dateline AS logTime
FROM {$from_prefix}threadsread;
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

fwrite($fp, $row['filedata']);
fclose($fp);

$rows[] = "$ID_ATTACH, $row[filesize], '" . addslashes($row['filename']) . "', $row[ID_MSG], $row[downloads]";
$ID_ATTACH++;
---}
SELECT pid AS ID_MSG, filedata, downloads, filename, filesize
FROM {$from_prefix}attachments;
---*