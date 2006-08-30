/* ATTENTION: You don't need to run or use this file!  The convert.php script does everything for you! */

/******************************************************************************/
---~ name: "FUDforum 2.6.x"
/******************************************************************************/
---~ version: "SMF 2.0 Alpha"
---~ settings: "/GLOBALS.php"
---~ globals: MSG_STORE_DIR
---~ from_prefix: "`$DBHOST_DBNAME`.$DBHOST_TBL_PREFIX"
---~ table_test: "{$from_prefix}users"

/******************************************************************************/
--- Converting members...
/******************************************************************************/

TRUNCATE {$to_prefix}members;
TRUNCATE {$to_prefix}attachments;

---* {$to_prefix}members
---{
// Try to pull out the actual URL...
if (!empty($row['avatar']) && preg_match('~<img\ssrc="(.+?)"\s~', $row['avatar'], $matches) != 0 && !empty($matches[1]))
	$row['avatar'] = $matches[1];
else
	$row['avatar'] = '';
---}
SELECT
	id AS ID_MEMBER, SUBSTRING(login, 1, 80) AS memberName,
	SUBSTRING(alias, 1, 255) AS realName, SUBSTRING(passwd, 1, 64) AS passwd,
	SUBSTRING(email, 1, 255) AS emailAddress,
	SUBSTRING(location, 1, 255) AS location, SUBSTRING(icq, 1, 255) AS ICQ,
	SUBSTRING(aim, 1, 16) AS AIM, SUBSTRING(yahoo, 1, 32) AS YIM,
	SUBSTRING(msnm, 1, 255) AS MSN, bday AS birthdate,
	join_date AS dateRegistered, posted_msg_count AS posts,
	IF(users_opt & 1048576 = 0, 0, 1) AS ID_GROUP, last_visit AS lastLogin,
	SUBSTRING(home_page, 1, 255) AS websiteUrl,
	SUBSTRING(home_page, 1, 255) AS websiteTitle,
	INET_NTOA(reg_ip) AS memberIP, SUBSTRING(avatar_loc, 1, 255) AS avatar,
	SUBSTRING(REPLACE(sig, "\n", ""), 1, 65534) AS signature, '' AS lngfile,
	'' AS buddy_list, '' AS pm_ignore_list, '' AS messageLabels,
	'' AS personalText, '' AS timeFormat, '' AS usertitle,
	'' AS secretQuestion, '' AS secretAnswer, '' AS validation_code,
	'' AS additionalGroups, '' AS smileySet, '' AS passwordSalt
FROM {$from_prefix}users
WHERE passwd != '1';
---*

/******************************************************************************/
--- Converting categories...
/******************************************************************************/

TRUNCATE {$to_prefix}categories;

---* {$to_prefix}categories
SELECT id AS ID_CAT, SUBSTRING(name, 1, 255) AS name, view_order AS catOrder
FROM {$from_prefix}cat;
---*

/******************************************************************************/
--- Converting boards...
/******************************************************************************/

TRUNCATE {$to_prefix}boards;

DELETE FROM {$to_prefix}board_permissions
WHERE ID_BOARD != 0;

---* {$to_prefix}boards
SELECT
	id AS ID_BOARD, cat_id AS ID_CAT, SUBSTRING(name, 1, 255) AS name,
	SUBSTRING(descr, 1, 65534) AS description, thread_count AS numTopics,
	post_count AS numPosts, view_order AS boardOrder, '-1,0' AS memberGroups
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
	id AS ID_TOPIC, forum_id AS ID_BOARD, replies AS numReplies,
	views AS numViews, IF(thread_opt & 4, 1, 0) AS isSticky,
	thread_opt & 1 AS locked, root_msg_id AS ID_FIRST_MSG,
	last_post_id AS ID_LAST_MSG
FROM {$from_prefix}thread
HAVING ID_FIRST_MSG != 0
	AND ID_LAST_MSG != 0;
---*

---* {$to_prefix}topics (update ID_TOPIC)
SELECT t.ID_TOPIC, m.poster_id AS ID_MEMBER_UPDATED
FROM ({$to_prefix}topics AS t, {$from_prefix}msg AS m)
WHERE m.id = t.ID_LAST_MSG;
---*

---* {$to_prefix}topics (update ID_TOPIC)
SELECT t.ID_TOPIC, m.poster_id AS ID_MEMBER_STARTED
FROM ({$to_prefix}topics AS t, {$from_prefix}msg AS m)
WHERE m.id = t.ID_FIRST_MSG;
---*

/******************************************************************************/
--- Converting posts (this may take some time)...
/******************************************************************************/

TRUNCATE {$to_prefix}messages;

---* {$to_prefix}messages 200
---{
// This is the most annoying system ever!!
if (!file_exists($GLOBALS['MSG_STORE_DIR'] . 'msg_' . $row['file_id']))
	continue;

$fp = fopen($GLOBALS['MSG_STORE_DIR'] . 'msg_' . $row['file_id'], 'rb');
fseek($fp, $row['foff']);

$row['body'] = substr(strtr(fread($fp, $row['length']), array("\n" => '')), 0, 65534);

fclose($fp);

// Clean up...
unset($row['file_id']);
unset($row['foff']);
unset($row['length']);
---}
SELECT
	m.id AS ID_MSG, m.thread_id AS ID_TOPIC, t.forum_id AS ID_BOARD,
	m.post_stamp AS posterTime, m.poster_id AS ID_MEMBER,
	SUBSTRING(m.subject, 1, 255) AS subject,
	SUBSTRING(IFNULL(u.alias, 'Guest'), 1, 255) AS posterName,
	SUBSTRING(m.ip_addr, 1, 255) AS posterIP,
	SUBSTRING(IFNULL(u.email, ''), 1, 255) AS posterEmail,
	m.msg_opt & 2 != 0 AS smileysEnabled,
	m.file_id, m.foff, m.length, '' AS modifiedName, 'xx' AS icon
FROM ({$from_prefix}msg AS m, {$from_prefix}thread AS t)
	LEFT JOIN {$from_prefix}users AS u ON (u.id = m.poster_id)
WHERE t.id = m.thread_id;
---*

/******************************************************************************/
--- Converting polls...
/******************************************************************************/

TRUNCATE {$to_prefix}polls;
TRUNCATE {$to_prefix}poll_choices;
TRUNCATE {$to_prefix}log_polls;

---* {$to_prefix}polls
SELECT
	p.id AS ID_POLL, SUBSTRING(p.name, 1, 255) AS question,
	p.owner AS ID_MEMBER,
	SUBSTRING(IFNULL(u.alias, 'Guest'), 1, 255) AS posterName,
	p.max_votes AS maxVotes, p.creation_date + p.expiry_date AS expireTime
FROM {$from_prefix}poll AS p
	LEFT JOIN {$from_prefix}users AS u ON (u.id = p.owner);
---*

---* {$to_prefix}topics (update ID_TOPIC)
SELECT t.ID_TOPIC, m.poll_id AS ID_POLL
FROM ({$to_prefix}topics AS t, {$from_prefix}msg AS m)
WHERE m.id = t.ID_FIRST_MSG
	AND m.poll_id != 0;
---*

/******************************************************************************/
--- Converting poll options...
/******************************************************************************/

/* We need this, unfortunately, for the log_polls table. */
ALTER TABLE {$to_prefix}poll_choices
ADD COLUMN tempID int(10) unsigned NOT NULL default 0;

---* {$to_prefix}poll_choices
---{
if (!isset($_SESSION['ID_CHOICE']))
	$_SESSION['ID_CHOICE'] = 1;

// Last poll id?
if (!isset($_SESSION['last_poll_id']))
	$_SESSION['last_poll_id'] = $row['ID_POLL'];
// New poll - reset choice count!
elseif ($_SESSION['last_poll_id'] != $row['ID_POLL'])
{
	$_SESSION['ID_CHOICE'] = 1;
	$_SESSION['last_poll_id'] = $row['ID_POLL'];
}
else
	$_SESSION['ID_CHOICE']++;

$row['ID_CHOICE'] = $_SESSION['ID_CHOICE'];
---}
SELECT
	id AS tempID, poll_id AS ID_POLL, 1 AS ID_CHOICE,
	SUBSTRING(name, 1, 255) AS label, `count` AS votes
FROM {$from_prefix}poll_opt;
---*

/******************************************************************************/
--- Converting poll logs...
/******************************************************************************/

---* {$to_prefix}log_polls
SELECT
	pot.poll_id AS ID_POLL, pot.user_id AS ID_MEMBER,
	pc.ID_CHOICE AS ID_CHOICE
FROM ({$from_prefix}poll_opt_track AS pot, {$to_prefix}poll_choices AS pc)
WHERE pc.tempID = pot.poll_opt;
---*

ALTER TABLE {$to_prefix}poll_choices
DROP tempID;

/******************************************************************************/
--- Converting personal messages (step 1)...
/******************************************************************************/

TRUNCATE {$to_prefix}personal_messages;

---* {$to_prefix}personal_messages
---{
// More of this crap!
if (!file_exists($GLOBALS['MSG_STORE_DIR'] . 'private'))
	continue;

$fp = fopen($GLOBALS['MSG_STORE_DIR'] . 'private', 'rb');
fseek($fp, $row['foff']);

$row['body'] = substr(strtr(fread($fp, $row['length']), array("\n" => '')), 0, 65534);

fclose($fp);

// Clean up...
unset($row['foff']);
unset($row['length']);
---}
SELECT
	pm.id AS ID_PM, pm.ouser_id AS ID_MEMBER_FROM, pm.post_stamp AS msgtime,
	SUBSTRING(IFNULL(uf.alias, 'Guest'), 1, 255) AS fromName,
	SUBSTRING(pm.subject, 1, 255) AS subject, pm.foff, pm.length
FROM {$from_prefix}pmsg AS pm
	LEFT JOIN {$from_prefix}users AS uf ON (uf.id = pm.ouser_id)
WHERE pm.fldr != 3;
---*

/******************************************************************************/
--- Converting personal messages (step 2)...
/******************************************************************************/

TRUNCATE {$to_prefix}pm_recipients;

---* {$to_prefix}pm_recipients
SELECT
	id AS ID_PM, duser_id AS ID_MEMBER, read_stamp != 0 AS is_read,
	'' AS labels
FROM {$from_prefix}pmsg
WHERE fldr != 3;
---*

/******************************************************************************/
--- Converting topic notifications...
/******************************************************************************/

TRUNCATE {$to_prefix}log_notify;

---* {$to_prefix}log_notify
SELECT user_id AS ID_MEMBER, thread_id AS ID_BOARD
FROM {$from_prefix}thread_notify;
---*

/******************************************************************************/
--- Converting board notifications...
/******************************************************************************/

---* {$to_prefix}log_notify
SELECT user_id AS ID_MEMBER, forum_id AS ID_BOARD
FROM {$from_prefix}forum_notify;
---*

/******************************************************************************/
--- Converting smileys...
/******************************************************************************/

UPDATE {$to_prefix}smileys
SET hidden = 1;

---{
$specificSmileys = array(
	':)' => 'smiley',
	':-)' => 'smiley',
	'=)' => 'smiley',
	':|' => 'undecided',
	':-|' => 'undecided',
	':neutral:' => 'undecided',
	':(' => 'sad',
	':-(' => 'sad',
	':sad:' => 'sad',
	':]' => 'grin',
	':-]' => 'grin',
	':brgin:' => 'grin',
	'8o' => 'shocked',
	'8-o' => 'shocked',
	':shock:' => 'shocked',
	':o' => 'shocked',
	':-o' => 'shocked',
	':eek:' => 'shocked',
	';)' => 'wink',
	':wink:' => 'wink',
	';-)' => 'wink',
	';/' => 'rolleyes',
	':p' => 'tongue',
	':-p' => 'tongue',
	':razz:' => 'tongue',
	':lol:' => 'cheesy',
	':rolleyes:' => 'rolleyes',
	'8)' => 'cool',
	'8-)' => 'cool',
	':cool:' => 'cool',
	':x' => 'angry',
	':-x' => 'angry',
	':mad:' => 'angry',
	':blush:' => 'embarrassed',
	':?' => 'huh',
	':-?' => 'huh',
	':???:' => 'huh',
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
if (strlen($newfilename) <= 255) && copy($row['location'], $attachmentUploadDir . '/' . $newfilename))
{
	$rows[] = "$ID_ATTACH, $row[size], '" . addslashes($row['filename']) . "', $row[ID_MSG], $row[downloads]";

	$ID_ATTACH++;
}
---}
SELECT
	message_id AS ID_MSG, location, dlcount AS downloads,
	original_name AS filename, fsize AS size
FROM {$from_prefix}attach;
---*