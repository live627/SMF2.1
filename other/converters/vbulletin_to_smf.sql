/* ATTENTION: You don't need to run or use this file!  The convert.php script does everything for you! */

/******************************************************************************/
---~ name: "vBulletin 2"
/******************************************************************************/
---~ version: "SMF 2.0 Alpha"
---~ settings: "/admin/config.php", "/includes/config.php"
---~ from_prefix: "`$dbname`."
---~ table_test: "{$from_prefix}user"

/******************************************************************************/
--- Converting members...
/******************************************************************************/

TRUNCATE {$to_prefix}members;

---* {$to_prefix}members
SELECT
	userid AS ID_MEMBER, SUBSTRING(username, 1, 80) AS memberName,
	joindate AS dateRegistered, posts, SUBSTRING(username, 1, 255) AS realName,
	SUBSTRING(password, 1, 64) AS passwd,
	SUBSTRING(email, 1, 255) AS emailAddress,
	IF(usergroupid = 6, 1, IF(usergroupid = 5 OR usergroupid = 7, 2, 0)) AS ID_GROUP,
	lastvisit AS lastLogin, SUBSTRING(customtitle, 1, 255) AS personalText,
	birthday AS birthdate, SUBSTRING(homepage, 1, 255) AS websiteUrl,
	SUBSTRING(homepage, 1, 255) AS websiteTitle,
	SUBSTRING(usertitle, 1, 255) AS usertitle, SUBSTRING(icq, 1, 255) AS ICQ,
	SUBSTRING(aim, 1, 16) AS AIM, SUBSTRING(yahoo, 1, 32) AS YIM,
	emailonpm AS pm_email_notify,
	IF(showemail = 0, 1, 0) AS hideEmail, IF(invisible = 1, 0, 1) AS showOnline,
	SUBSTRING(signature, 1, 65534) AS signature,
	emailnotification AS notifyAnnouncements, '' AS lngfile, '' AS buddy_list,
	'' AS pm_ignore_list, '' AS messageLabels, '' AS location, '' AS MSN,
	'' AS timeFormat, '' AS avatar, '' AS memberIP, '' AS secretQuestion,
	'' AS secretAnswer, '' AS validation_code, '' AS additionalGroups,
	'' AS smileySet, '' AS passwordSalt
FROM {$from_prefix}user
WHERE userid != 0;
---*

/******************************************************************************/
--- Converting categories...
/******************************************************************************/

TRUNCATE {$to_prefix}categories;

---* {$to_prefix}categories
SELECT
	forumid AS ID_CAT, SUBSTRING(title, 1, 255) AS name,
	displayorder AS catOrder
FROM {$from_prefix}forum
WHERE parentid = -1;
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
	forumid AS ID_BOARD, SUBSTRING(title, 1, 255) AS name,
	SUBSTRING(description, 1, 65534) AS description,
	displayorder AS boardOrder, replycount AS numPosts,
	threadcount AS numTopics, parentid AS ID_PARENT,
	countposts = 0 AS countPosts, '-1,0' AS memberGroups
FROM {$from_prefix}forum
WHERE parentid != -1;
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
	t.pollid AS ID_POLL, t.views AS numViews, t.postuserid AS ID_MEMBER_STARTED,
	ul.userid AS ID_MEMBER_UPDATED, t.replycount AS numReplies,
	IF(t.open, 0, 1) AS locked, MIN(p.postid) AS ID_FIRST_MSG,
	MAX(p.postid) AS ID_LAST_MSG
FROM ({$from_prefix}thread AS t, {$from_prefix}post AS p)
	LEFT JOIN {$from_prefix}user AS ul ON (ul.username = t.lastposter)
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
---{
$row['body'] = preg_replace('~\[(quote)=([^\]]+)\]~i', '[$1=&quot;$2&quot;]', strtr($row['body'], array('"' => '&quot;')));
$row['body'] = preg_replace('~\[(url|email)=&quot;(.+?)&quot;\]~i', '[$1=$2]', $row['body']);
---}
SELECT
	p.postid AS ID_MSG, p.threadid AS ID_TOPIC, t.forumid AS ID_BOARD,
	p.dateline AS posterTime, p.userid AS ID_MEMBER,
	SUBSTRING(p.ipaddress, 1, 255) AS posterIP,
	SUBSTRING(IF(p.title = '', t.title, p.title), 1, 255) AS subject,
	SUBSTRING(u.email, 1, 255) AS posterEmail,
	SUBSTRING(p.username, 1, 255) AS posterName,
	p.allowsmilie AS smileysEnabled, p.editdate AS modifiedTime,
	SUBSTRING(edit_u.username, 1, 255) AS modifiedName,
	SUBSTRING(REPLACE(p.pagetext, '<br>', '<br />'), 1, 65534) AS body,
	'xx' AS icon
FROM ({$from_prefix}post AS p, {$from_prefix}thread AS t)
	LEFT JOIN {$from_prefix}user AS u ON (u.userid = p.userid)
	LEFT JOIN {$from_prefix}user AS edit_u ON (edit_u.userid = p.edituserid)
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
	p.pollid AS ID_POLL, SUBSTRING(p.question, 1, 255) AS question,
	IF(p.active = 0, 1, 0) AS votingLocked,
	IF(p.timeout = 0, 0, p.dateline + p.timeout * 86400) AS expireTime,
	t.postuserid AS ID_MEMBER, SUBSTRING(t.postusername, 1, 255) AS posterName
FROM {$from_prefix}poll AS p
	LEFT JOIN {$from_prefix}thread AS t ON (t.pollid = p.pollid);
---*

/******************************************************************************/
--- Converting poll options...
/******************************************************************************/

---* {$to_prefix}poll_choices
---{
$no_add = true;
$keys = array('ID_POLL', 'ID_CHOICE', 'label', 'votes');

$options = explode('|||', $row['options']);
$votes = explode('|||', $row['votes']);
for ($i = 0, $n = count($options); $i < $n; $i++)
	$rows[] = $row['ID_POLL'] . ', ' . ($i + 1) . ", '" . addslashes(substr($options[$i], 0, 255)) . "', '" . $votes[$i] . "'";
---}
SELECT pollid AS ID_POLL, options, votes
FROM {$from_prefix}poll;
---*

/******************************************************************************/
--- Converting poll votes...
/******************************************************************************/

---* {$to_prefix}log_polls
SELECT pollid AS ID_POLL, userid AS ID_MEMBER, voteoption AS ID_CHOICE
FROM {$from_prefix}pollvote;
---*

/******************************************************************************/
--- Converting personal messages (step 1)...
/******************************************************************************/

TRUNCATE {$to_prefix}personal_messages;

---* {$to_prefix}personal_messages
---{
$row['body'] = preg_replace('~\[(quote)=([^\]]+)\]~i', '[$1=&quot;$2&quot;]', $row['body']);
---}
SELECT
	pm.privatemessageid AS ID_PM, pm.fromuserid AS ID_MEMBER_FROM,
	pm.dateline AS msgtime, SUBSTRING(uf.username, 1, 255) AS fromName,
	SUBSTRING(pm.title, 1, 255) AS subject,
	SUBSTRING(REPLACE(pm.message, '<br>', '<br />'), 1, 65534) AS body
FROM {$from_prefix}privatemessage AS pm
	LEFT JOIN {$from_prefix}user AS uf ON (uf.userid = pm.fromuserid)
WHERE pm.folderid != 'sent';
---*

/******************************************************************************/
--- Converting personal messages (step 2)...
/******************************************************************************/

TRUNCATE {$to_prefix}pm_recipients;

---* {$to_prefix}pm_recipients
SELECT
	privatemessageid AS ID_PM, touserid AS ID_MEMBER,
	messageread = 1 AS is_read, '' AS labels
FROM {$from_prefix}privatemessage
WHERE folderid != 'sent';
---*

/******************************************************************************/
--- Converting topic notifications...
/******************************************************************************/

TRUNCATE {$to_prefix}log_notify;

---* {$to_prefix}log_notify
SELECT userid AS ID_MEMBER, threadid AS ID_TOPIC
FROM {$from_prefix}subscribethread;
---*

/******************************************************************************/
--- Converting board notifications...
/******************************************************************************/

---* {$to_prefix}log_notify
SELECT userid AS ID_MEMBER, forumid AS ID_BOARD
FROM {$from_prefix}subscribeforum;
---*

/******************************************************************************/
--- Converting smileys...
/******************************************************************************/

UPDATE {$to_prefix}smileys
SET hidden = 1;

---{
$specificSmileys = array(
	':cool:' => 'cool',
	':(' => 'sad',
	':confused:' => 'huh',
	':mad:' => 'angry',
	':rolleyes:' => 'rolleyes',
	':eek:' => 'shocked',
	':p' => 'tongue',
	':o' => 'embarrassed',
	';)' => 'wink',
	':D' => 'grin',
	':)' => 'smiley',
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
$fp = @fopen($attachmentUploadDir . '/' . $newfilename, 'wb');
if (!$fp)
	return;

fwrite($fp, $row['filedata']);
fclose($fp);

$rows[] = "$ID_ATTACH, " . filesize($attachmentUploadDir . '/' . $newfilename) . ", '" . addslashes($row['filename']) . "', $row[ID_MSG], $row[downloads]";
$ID_ATTACH++;
---}
SELECT p.postid AS ID_MSG, a.filedata, a.counter AS downloads, a.filename
FROM ({$from_prefix}attachment AS a, {$from_prefix}post AS p)
WHERE p.attachmentid = a.attachmentid;
---*

/******************************************************************************/
--- Converting avatars...
/******************************************************************************/

---* {$to_prefix}attachments
---{
$no_add = true;
$keys = array('ID_ATTACH', 'size', 'filename', 'ID_MEMBER');

// !!! This can't be right!
$newfilename = getAttachmentFilename($row['filename'], $ID_ATTACH);
if (strlen($newfilename) > 255)
	return;
$fp = @fopen($attachmentUploadDir . '/' . $newfilename, 'wb');
if (!$fp)
	return;

fwrite($fp, $row['avatardata']);
fclose($fp);

$rows[] = "$ID_ATTACH, " . filesize($attachmentUploadDir . '/' . $newfilename) . ", '" . addslashes($row['filename']) . "', $row[ID_MEMBER]";
$ID_ATTACH++;

// !!! Break this out?
convert_query("
	UPDATE {$to_prefix}members
	SET avatar = ''
	WHERE ID_MEMBER = $row[ID_MEMBER]
	LIMIT 1");
---}
SELECT userid AS ID_MEMBER, avatardata, filename
FROM {$from_prefix}customavatar;
---*