/* ATTENTION: You don't need to run or use this file!  The convert.php script does everything for you! */

/******************************************************************************/
---~ name: "Simpleboard 1.0 and 1.1"
/******************************************************************************/
---~ version: "SMF 2.0 Alpha"
---~ settings: "/configuration.php", "../../configuration.php", "../../../configuration.php"
---~ from_prefix: "`$mosConfig_db`.$mosConfig_dbprefix"
---~ table_test: "{$from_prefix}users"

/******************************************************************************/
--- Converting members...
/******************************************************************************/

TRUNCATE {$to_prefix}members;

---* {$to_prefix}members
SELECT
	/* // !!! We could use m.name for the realName? */
	m.id AS ID_MEMBER, SUBSTRING(m.username, 1, 80) AS memberName,
	SUBSTRING(m.username, 1, 255) AS realName,
	SUBSTRING(sb.signature, 1, 65534) AS signature, sb.posts,
	sb.karma AS karmaGood, SUBSTRING(m.password, 1, 64) AS passwd,
	SUBSTRING(m.email, 1, 255) AS emailAddress,
	SUBSTRING(cd.country, 1, 255) AS location,
	IF(m.activation = 1, 0, 1) AS is_activated,
	UNIX_TIMESTAMP(m.registerDate) AS dateRegistered,
	UNIX_TIMESTAMP(m.lastvisitDate) AS lastLogin,
	IF(cd.params LIKE '%email=0%', 1, 0) AS hideEmail,
	IF(m.usertype = 'superadministrator' OR m.usertype = 'administrator', 1, 0) AS ID_GROUP,
	'' AS lngfile, '' AS buddy_list, '' AS pm_ignore_list, '' AS messageLabels,
	'' AS personalText, '' AS websiteTitle, '' AS websiteUrl, '' AS ICQ,
	'' AS AIM, '' AS YIM, '' AS MSN, '' AS timeFormat, '' AS avatar,
	'' AS usertitle, '' AS memberIP, '' AS secretQuestion, '' AS secretAnswer,
	'' AS validation_code, '' AS additionalGroups, '' AS smileySet,
	'' AS passwordSalt
FROM {$from_prefix}users AS m
	LEFT JOIN {$from_prefix}sb_users AS sb ON (sb.userid = m.id)
	LEFT JOIN {$from_prefix}contact_details AS cd ON (cd.user_id = m.id);
---*

/******************************************************************************/
--- Converting categories...
/******************************************************************************/

TRUNCATE {$to_prefix}categories;

---* {$to_prefix}categories
SELECT id AS ID_CAT, SUBSTRING(name, 1, 255) AS name, ordering AS catOrder
FROM {$from_prefix}sb_categories
WHERE parent = 0;
---*

/******************************************************************************/
--- Converting boards...
/******************************************************************************/

TRUNCATE {$to_prefix}boards;

DELETE FROM {$to_prefix}board_permissions
WHERE ID_BOARD != 0;

---* {$to_prefix}boards
SELECT
	id AS ID_BOARD, parent AS ID_CAT, ordering AS boardOrder,
	SUBSTRING(name, 1, 255) AS name,
	SUBSTRING(description, 1, 65534) AS description, '-1,0' AS memberGroups
FROM {$from_prefix}sb_categories
WHERE parent != 0;
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
	t.id AS ID_TOPIC, t.catid AS ID_BOARD, t.ordering AS isSticky, t.locked,
	t.hits AS numViews, t.userid AS ID_MEMBER_STARTED,
	MIN(m.id) AS ID_FIRST_MSG, MAX(m.id) AS ID_LAST_MSG
FROM ({$from_prefix}sb_messages AS t, {$from_prefix}sb_messages AS m)
WHERE t.parent = 0
	AND m.thread = t.id
GROUP BY t.id
HAVING ID_FIRST_MSG != 0
	AND ID_LAST_MSG != 0;
---*

---* {$to_prefix}topics (update ID_TOPIC)
SELECT t.ID_TOPIC, m.userid AS ID_MEMBER_UPDATED
FROM ({$to_prefix}topics AS t, {$from_prefix}sb_messages AS m)
WHERE m.thread = t.ID_LAST_MSG;
---*

/******************************************************************************/
--- Converting posts (this may take some time)...
/******************************************************************************/

TRUNCATE {$to_prefix}messages;
TRUNCATE {$to_prefix}attachments;

---* {$to_prefix}messages 200
---{
$row['body'] = preg_replace('~[file name=.+?]http.+?[/file]~i', '', $row['body']);
$row['body'] = preg_replace('~[img size=(\d+)]~i', '[img width=$1]', $row['body']);
---}
SELECT
	m.id AS ID_MSG, m.thread AS ID_TOPIC, m.time AS posterTime,
	SUBSTRING(m.subject, 1, 255), m.userid AS ID_MEMBER,
	SUBSTRING(m.name, 1, 255) AS posterName,
	SUBSTRING(m.email, 1, 255) AS posterEmail,
	SUBSTRING(m.ip, 1, 255) AS posterIP, m.catid AS ID_BOARD,
	SUBSTRING(mt.message, 1, 65534) AS body, '' AS modifiedName, 'xx' AS icon
FROM ({$from_prefix}sb_messages AS m, {$from_prefix}sb_messages_text AS mt)
WHERE mt.mesid = m.id;
---*

/******************************************************************************/
--- Converting topic notifications...
/******************************************************************************/

TRUNCATE {$to_prefix}log_notify;

---* {$to_prefix}log_notify
SELECT DISTINCTROW userid AS ID_MEMBER, thread AS ID_TOPIC
FROM {$from_prefix}sb_subscriptions;
---*

/******************************************************************************/
--- Converting moderators...
/******************************************************************************/

TRUNCATE {$to_prefix}moderators;

---* {$to_prefix}moderators
SELECT catid AS ID_BOARD, userid AS ID_MEMBER
FROM {$from_prefix}sb_moderation;
---*

/******************************************************************************/
--- Clearing unused tables...
/******************************************************************************/

TRUNCATE {$to_prefix}personal_messages;
TRUNCATE {$to_prefix}pm_recipients;
TRUNCATE {$to_prefix}polls;
TRUNCATE {$to_prefix}poll_choices;
TRUNCATE {$to_prefix}log_polls;

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
	':redface:' => 'embarassed',
	':wink:' => 'wink',
	':biggrin:' => 'grin',
	':smilie:' => 'smiley',
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

$newfilename = getAttachmentFilename(basename($row['filelocation']), $ID_ATTACH);
if (strlen($newfilename) <= 255 && copy($row['filelocation'], $attachmentUploadDir . '/' . $newfilename))
{
	@touch($attachmentUploadDir . '/' . $newfilename, filemtime($row['filelocation']));
	$rows[] = "$ID_ATTACH, " . filesize($attachmentUploadDir . '/' . $newfilename) . ", '" . addslashes(basename($row['filelocation'])) . "', $row[ID_MSG], 0";
	$ID_ATTACH++;
}
---}
SELECT mesid AS ID_MSG, filelocation
FROM {$from_prefix}sb_attachments;
---*

/******************************************************************************/
--- Converting avatars...
/******************************************************************************/

---* {$to_prefix}attachments
---{
$no_add = true;
$keys = array('ID_ATTACH', 'size', 'filename', 'ID_MEMBER');

$newfilename = 'avatar_' . $row['ID_MEMBER'] . strrchr($row['filename'], '.');
if (strlen($newfilename) <= 255 && copy($_POST['path_from'] . '/components/com_simpleboard/avatars/, $attachmentUploadDir . '/' . $newfilename))
{
	$rows[] = "$ID_ATTACH, " . filesize($attachmentUploadDir . '/' . $newfilename) . ", '" . addslashes($newfilename) . "', $row[ID_MEMBER]";
	$ID_ATTACH++;
}
---}
SELECT userid AS ID_MEMBER, avatar AS filename
FROM {$from_prefix}sb_users
WHERE avatar != ''
	AND LOCATE('/', avatar) = 0;
---*