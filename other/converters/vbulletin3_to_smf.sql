/* ATTENTION: You don't need to run or use this file!  The convert.php script does everything for you! */

/******************************************************************************/
---~ name: "vBulletin 3"
/******************************************************************************/
---~ version: "SMF 2.0 Alpha"
---~ settings: "/admin/config.php", "/includes/config.php"
---~ from_prefix: "`$dbname`.$tableprefix"
---~ table_test: "{$from_prefix}user"

/******************************************************************************/
--- Converting members...
/******************************************************************************/

TRUNCATE {$to_prefix}members;

---* {$to_prefix}members
SELECT
	u.userid AS ID_MEMBER, SUBSTRING(u.username, 1, 80) AS memberName, 
	SUBSTRING(u.username, 1, 255) AS realName,
	SUBSTRING(u.password, 1, 64) AS passwd,
	SUBSTRING(u.email, 1, 255) AS emailAddress,
	SUBSTRING(u.homepage, 1, 255) AS websiteTitle,
	SUBSTRING(u.homepage, 1, 255) AS websiteUrl,
	SUBSTRING(u.icq, 1, 255) AS ICQ, SUBSTRING(u.aim, 1, 16) AS AIM,
	SUBSTRING(u.yahoo, 1, 32) AS YIM,
	SUBSTRING(IF(u.customtitle, u.usertitle, ''), 1, 255) AS usertitle,
	u.lastvisit AS lastLogin, u.joindate AS dateRegistered, u.posts,
	u.reputation AS karmaGood, u.birthday_search AS birthdate,
	SUBSTRING(u.ipaddress, 1, 255) AS memberIP,
	SUBSTRING(u.msn, 1, 255) AS MSN,
	CASE u.usergroupid WHEN 6 THEN 1 WHEN 5 THEN 2 WHEN 7 THEN 2 ELSE 0 END AS ID_GROUP,
	SUBSTRING(u.salt, 1, 5) AS passwordSalt,
	SUBSTRING(ut.signature, 1, 65534) AS signature, '' AS lngfile,
	'' AS buddy_list, '' AS pm_ignore_list, '' AS messageLabels,
	'' AS personalText, '' AS timeFormat, '' AS avatar, '' AS secretQuestion,
	'' AS secretAnswer, '' AS validation_code, '' AS additionalGroups,
	'' AS smileySet
FROM {$from_prefix}user AS u
	LEFT JOIN {$from_prefix}usertextfield AS ut ON (ut.userid = u.userid)
WHERE u.userid != 0;
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
	threadcount AS numTopics, parentid AS ID_PARENT, '-1,0' AS memberGroups
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
$row['body'] = substr(preg_replace('~\[(url|email)=&quot;(.+?)&quot;\]~i', '[$1=$2]', $row['body']), 0, 65534);
---}
SELECT
	p.postid AS ID_MSG, p.threadid AS ID_TOPIC, p.dateline AS posterTime,
	p.userid AS ID_MEMBER,
	SUBSTRING(IF(p.title = '', t.title, p.title), 1, 255) AS subject,
	SUBSTRING(p.username, 1, 255) AS posterName,
	SUBSTRING(p.ipaddress, 1, 255) AS posterIP, t.forumid AS ID_BOARD,
	p.allowsmilie AS smileysEnabled,
	REPLACE(p.pagetext, '<br>', '<br />') AS body, '' AS posterEmail,
	'' AS modifiedName, 'xx' AS icon
FROM ({$from_prefix}post AS p, {$from_prefix}thread AS t)
WHERE t.threadid = p.threadid;
---*

---* {$to_prefix}messages (update ID_MSG)
SELECT postid AS ID_MSG, username AS modifiedName, dateline AS modifiedTime
FROM {$from_prefix}editlog
ORDER BY dateline;
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
	IF(p.active = 0, 1, 0) AS votingLocked, p.multiple AS maxVotes,
	SUBSTRING(IFNULL(t.postusername, 'Guest'), 1, 255) AS posterName,
	IF(p.timeout = 0, 0, p.dateline + p.timeout * 86400) AS expireTime,
	t.postuserid AS ID_MEMBER
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
	$rows[] = $row['ID_POLL'] . ', ' . ($i + 1) . ", '" . addslashes($options[$i]) . "', '" . @$votes[$i] . "'";
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
	pm.pmid AS ID_PM, pmt.fromuserid AS ID_MEMBER_FROM, pmt.dateline AS msgtime,
	SUBSTRING(pmt.fromusername, 1, 255) AS fromName,
	SUBSTRING(pmt.title, 1, 255) AS subject,
	SUBSTRING(REPLACE(pmt.message, '<br>', '<br />'), 1, 65534) AS body
FROM ({$from_prefix}pm AS pm, {$from_prefix}pmtext AS pmt)
WHERE pmt.pmtextid = pm.pmtextid
	AND pm.folderid != -1;
---*

/******************************************************************************/
--- Converting personal messages (step 2)...
/******************************************************************************/

TRUNCATE {$to_prefix}pm_recipients;

---* {$to_prefix}pm_recipients
SELECT 
	pm.pmid AS ID_PM, pm.touserid AS ID_MEMBER, pm.readtime != 0 AS is_read,
	'' AS labels
FROM {$from_prefix}pmreceipt AS pm;
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

if (!isset($vb_settings))
{
	$result = convert_query("
		SELECT varname, value
		FROM {$from_prefix}setting
		WHERE varname IN ('attachfile', 'attachpath', 'usefileavatar', 'avatarpath')
		LIMIT 4");
	$vb_settings = array();
	while ($row2 = mysql_fetch_assoc($result))
	{
		if (substr($row2['value'], 0, 2) == './')
			$row2['value'] = $_POST['path_from'] . substr($row2['value'], 1);
		$vb_settings[$row2['varname']] = $row2['value'];
	}
	mysql_free_result($result);
}

$newfilename = getAttachmentFilename($row['filename'], $ID_ATTACH);
if (empty($vb_settings['attachfile']))
{
	$fp = @fopen($attachmentUploadDir . '/' . $newfilename, 'wb');
	if (!$fp)
		return;

	fwrite($fp, $row['filedata']);
	fclose($fp);
}
elseif ($vb_settings['attachfile'] == 1)
{
	if (!copy($vb_settings['attachpath'] . '/' . $row['userid'] . '/' . $row['attachmentid'] . '.attach', $attachmentUploadDir . '/' . $newfilename))
		return;
}
elseif ($vb_settings['attachfile'] == 2)
{
	if (!copy($vb_settings['attachpath'] . '/' . chunk_split($row['userid'], 1, '/') . $row['attachmentid'] . '.attach', $attachmentUploadDir . '/' . $newfilename))
		return;
}

$rows[] = "$ID_ATTACH, " . filesize($attachmentUploadDir . '/' . $newfilename) . ", '" . addslashes($row['filename']) . "', $row[ID_MSG], $row[downloads]";
$ID_ATTACH++;
---}
SELECT
	postid AS ID_MSG, counter AS downloads, filename, filedata, userid,
	attachmentid
FROM {$from_prefix}attachment;
---*

/******************************************************************************/
--- Converting avatars...
/******************************************************************************/

---* {$to_prefix}attachments
---{
$no_add = true;
$keys = array('ID_ATTACH', 'size', 'filename', 'ID_MEMBER');

if (!isset($vb_settings))
{
	$result = convert_query("
		SELECT varname, value
		FROM {$from_prefix}setting
		WHERE varname IN ('attachfile', 'attachpath', 'usefileavatar', 'avatarpath')
		LIMIT 4");
	$vb_settings = array();
	while ($row2 = mysql_fetch_assoc($result))
	{
		if (substr($row2['value'], 0, 2) == './')
			$row2['value'] = $_POST['path_from'] . substr($row2['value'], 1);
		$vb_settings[$row2['varname']] = $row2['value'];
	}
	mysql_free_result($result);
}


$newfilename = getAttachmentFilename($row['filename'], $ID_ATTACH);
if (strlen($newfilename) > 255)
	return;
elseif (empty($vb_settings['usefileavatar']))
{
	$fp = @fopen($attachmentUploadDir . '/' . $newfilename, 'wb');
	if (!$fp)
		return;

	fwrite($fp, $row['avatardata']);
	fclose($fp);
}
elseif (!copy($vb_settings['avatarpath'] . '/avatar' . $row['userid'] . '_' . $row['avatarrevision'] . '.gif', $attachmentUploadDir . '/' . $newfilename))
	return;

$rows[] = "$ID_ATTACH, " . filesize($attachmentUploadDir . '/' . $newfilename) . ", '" . addslashes($row['filename']) . "', $row[ID_MEMBER]";
$ID_ATTACH++;
---}
SELECT ca.userid AS ID_MEMBER, ca.avatardata, ca.filename, u.avatarrevision
FROM ({$from_prefix}customavatar AS ca, {$from_prefix}user AS u)
WHERE u.userid = ca.userid;
---*