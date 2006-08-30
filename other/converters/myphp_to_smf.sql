/* ATTENTION: You don't need to run or use this file!  The convert.php script does everything for you! */

/******************************************************************************/
---~ name: "MyPHP Forum 3.0"
/******************************************************************************/
---~ version: "SMF 2.0 Alpha"
---~ settings: "/config.php"
---~ from_prefix: "`$dbname`.{$tablepre}"
---~ table_test: "{$from_prefix}member"

/******************************************************************************/
--- Converting members...
/******************************************************************************/

TRUNCATE {$to_prefix}members;

---* {$to_prefix}members
---{
$row['signature'] = substr(strtr($row['signature'], array('[mail' => '[email', '[/mail]' => '[/email]')), 0, 65534);
---}
SELECT
	uid AS ID_MEMBER, SUBSTRING(username, 1, 255) AS memberName,
	SUBSTRING(username, 1, 255) AS realName,
	SUBSTRING(password, 1, 64) AS passwd, SUBSTRING(ip, 1, 255) AS memberIP,
	SUBSTRING(email, 1, 255) AS emailAddress,
	SUBSTRING(website, 1, 255) AS websiteUrl,
	SUBSTRING(website, 1, 255) AS websiteTitle,
	SUBSTRING(aim, 1, 16) AS AIM, SUBSTRING(msn, 1, 255) AS MSN,
	SUBSTRING(location, 1, 255) AS location,
	REPLACE(sig, '\n', '<br />') AS signature,
	IF(status = 'Administrator', 1, 0) AS ID_GROUP, regdate AS dateRegistered,
	posts, SUBSTRING(yahoo, 1, 32) AS YIM, private AS hideEmail,
	SUBSTRING(tag, 1, 255) AS personalText,
	CONCAT(RIGHT(birthday, 4), '-', LEFT(birthday, 5)) AS birthdate,
	IF(gender = 'Male', 1, 2) AS gender, '' AS lngfile, '' AS buddy_list,
	'' AS pm_ignore_list, '' AS messageLabels, '' AS ICQ, '' AS timeFormat,
	'' AS avatar, '' AS usertitle, '' AS secretQuestion, '' AS secretAnswer,
	'' AS validation_code, '' AS additionalGroups, '' AS smileySet,
	'' AS passwordSalt
FROM {$from_prefix}member;
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
	fid AS ID_BOARD, 1 AS ID_CAT, SUBSTRING(name, 1, 255) AS name,
	SUBSTRING(description, 1, 65534) AS name, dorder AS boardOrder
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
	t.tid AS ID_TOPIC, t.fid AS ID_BOARD, t.replies AS numReplies,
	t.views AS numViews, t.topped = 'yes' AS isSticky, t.status = 2 AS locked,
	mem.uid AS ID_MEMBER_STARTED, MIN(p.pid) AS ID_FIRST_MSG,
	MAX(p.pid) AS ID_LAST_MSG
FROM ({$from_prefix}topic AS t, {$from_prefix}post AS p)
	LEFT JOIN {$from_prefix}member AS mem ON (BINARY mem.username = t.author)
WHERE p.tid = t.tid
GROUP BY t.tid
HAVING ID_FIRST_MSG != 0
	AND ID_LAST_MSG != 0;
---*

---* {$to_prefix}topics (update ID_TOPIC)
SELECT t.ID_TOPIC, mem.uid AS ID_MEMBER_UPDATED
FROM ({$to_prefix}topics AS t, {$from_prefix}post AS p, {$from_prefix}member AS mem)
WHERE p.pid = t.ID_LAST_MSG
	AND BINARY mem.username = p.author;
---*

/******************************************************************************/
--- Converting posts (this may take some time)...
/******************************************************************************/

TRUNCATE {$to_prefix}messages;
TRUNCATE {$to_prefix}attachments;

---* {$to_prefix}messages 200
---{
$row['body'] = substr(strtr($row['body'], array('[mail' => '[email', '[/mail]' => '[/email]')), 0, 65534);
---}
SELECT
	p.pid AS ID_MSG, p.tid AS ID_TOPIC, t.fid AS ID_BOARD,
	SUBSTRING(mem.ip, 1, 255) AS posterIP, mem.uid AS ID_MEMBER,
	p.dateline AS posterTime, SUBSTRING(p.author, 1, 255) AS posterName,
	SUBSTRING(mem.email, 1, 255) AS posterEmail,
	SUBSTRING(REPLACE(p.message, '\n', '<br />'), 1, 65534) AS body,
	SUBSTRING(IF(p.subject = '', CONCAT('Re:', t.name), p.subject), 1, 255) AS subject,
	'' AS modifiedName, 'xx' AS icon
FROM ({$from_prefix}post AS p, {$from_prefix}topic AS t)
	LEFT JOIN {$from_prefix}member AS mem ON (BINARY mem.username = p.author)
WHERE t.tid = p.tid;
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
---{
$row['body'] = strtr($row['body'], array('[mail' => '[email', '[/mail]' => '[/email]'));
---}
SELECT
	pm.id AS ID_PM, mem.uid AS ID_MEMBER_FROM, pm.time AS msgtime,
	SUBSTRING(pm.sender, 1, 255) AS fromName,
	SUBSTRING(pm.topic, 1, 255) AS subject,
	SUBSTRING(REPLACE(pm.message, '\n', '<br />'), 1, 65534) AS body
FROM {$from_prefix}privmsg AS pm
	LEFT JOIN {$from_prefix}member AS mem ON (BINARY mem.username = pm.sender);
---*

/******************************************************************************/
--- Converting personal messages (step 2)...
/******************************************************************************/

TRUNCATE {$to_prefix}pm_recipients;

---* {$to_prefix}pm_recipients
SELECT pm.id AS ID_PM, mem.uid AS ID_MEMBER, '' AS labels
FROM ({$from_prefix}privmsg AS pm, {$from_prefix}member AS mem)
WHERE BINARY mem.username = pm.receiver;
---*

/******************************************************************************/
--- Converting censored words...
/******************************************************************************/

DELETE FROM {$to_prefix}settings
WHERE variable IN ('censor_vulgar', 'censor_proper');

---# Moving censored words...
---{
$result = convert_query("
	SELECT word, replacement
	FROM {$from_prefix}words");
$censor_vulgar = array();
$censor_proper = array();
while ($row = mysql_fetch_assoc($result))
{
	$censor_vulgar[] = $row['word'];
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