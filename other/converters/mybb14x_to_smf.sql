/* ATTENTION: You don't need to run or use this file!  The convert.php script does everything for you! */

/******************************************************************************/
---~ name: "MyBulletinBoard 1.4"
/******************************************************************************/
---~ version: "SMF 1.1"
---~ settings: "/inc/config.php"
---~ globals: config
---~ from_prefix: "`{$config['database']['database']}`.{$config['database']['table_prefix']}"
---~ table_test: "{$from_prefix}users"

/******************************************************************************/
--- Converting members...
/******************************************************************************/

TRUNCATE {$to_prefix}members;
ALTER TABLE {$to_prefix}members
CHANGE COLUMN _ _ varchar(8) NOT NULL default '';

---* {$to_prefix}members
SELECT
	uid AS id_member, SUBSTRING(username, 1, 255) AS _,
	SUBSTRING(username, 1, 255) AS _, email AS _,
	SUBSTRING(password, 1, 64) AS passwd, SUBSTRING(salt, 1, 8) AS _,
	postnum AS posts, SUBSTRING(usertitle, 1, 255) AS usertitle,
	lastvisit AS _, IF(usergroup = 4, 1, 0) AS id_group,
	regdate AS _, SUBSTRING(website, 1, 255) AS _,
	SUBSTRING(website, 1, 255) AS _,
	SUBSTRING(icq, 1, 255) AS icq, SUBSTRING(aim, 1, 16) AS aim,
	SUBSTRING(yahoo, 1, 32) AS yim, SUBSTRING(msn, 1, 255) AS msn,
	SUBSTRING(signature, 1, 65534) AS signature, hideemail AS _,
	SUBSTRING(buddylist, 1, 255) AS buddy_list,
	SUBSTRING(regip, 1, 255) AS member_ip, SUBSTRING(regip, 1, 255) AS member_ip2,
	SUBSTRING(ignorelist, 1, 255) AS pm_ignore_list,
	timeonline AS totalTimeLoggedIn,
	CASE
		WHEN birthday = '' THEN '0001-01-01'
		ELSE CONCAT_WS('-', RIGHT(birthday, 4), SUBSTRING(birthday, LOCATE('-', birthday) + 1, LOCATE('-', birthday, LOCATE('-', birthday) + 1) - LOCATE('-', birthday) - 1), LEFT(birthday, LOCATE('-', birthday) - 1))
	END AS birthdate
FROM {$from_prefix}users;
---*

/******************************************************************************/
--- Converting categories...
/******************************************************************************/

TRUNCATE {$to_prefix}categories;

---* {$to_prefix}categories
SELECT fid AS id_cat, SUBSTRING(name, 1, 255) AS name, disporder AS _
FROM {$from_prefix}forums
WHERE type = 'c';
---*

/******************************************************************************/
--- Converting boards...
/******************************************************************************/

TRUNCATE {$to_prefix}boards;

DELETE FROM {$to_prefix}board_permissions
WHERE id_board != 0;

/* The converter will set id_cat for us based on id_parent being wrong. */
---* {$to_prefix}boards
SELECT
	fid AS id_board, SUBSTRING(name, 1, 255) AS name,
	SUBSTRING(description, 1, 65534) AS description, disporder AS _,
	posts AS _, threads AS _, pid AS id_parent,
	usepostcounts != 'yes' AS _, '-1,0' AS _
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
	t.tid AS id_topic, t.fid AS id_board, t.sticky AS _,
	t.poll AS id_poll, t.views AS _, t.uid AS id_member_started,
	ul.uid AS id_member_updated, t.replies AS _, t.closed AS locked,
	MIN(p.pid) AS id_first_msg, MAX(p.pid) AS id_last_msg
FROM ({$from_prefix}threads AS t, {$from_prefix}posts AS p)
	LEFT JOIN {$from_prefix}users AS ul ON (BINARY ul.username = t.lastposter)
WHERE p.tid = t.tid
GROUP BY t.tid
HAVING id_first_msg != 0
	AND id_last_msg != 0;
---*

/******************************************************************************/
--- Converting posts (this may take some time)...
/******************************************************************************/

TRUNCATE {$to_prefix}messages;
TRUNCATE {$to_prefix}attachments;

---* {$to_prefix}messages 200
SELECT
	p.pid AS id_msg, p.tid AS id_topic, t.fid AS id_board, p.uid AS id_member,
	SUBSTRING(p.username, 1, 255) AS _, p.dateline AS _,
	SUBSTRING(p.ipaddress, 1, 255) AS poster_ip,
	SUBSTRING(IF(p.subject = '', t.subject, p.subject), 1, 255) AS subject,
	SUBSTRING(u.email, 1, 255) AS _,
	p.smilieoff = 'no' AS _,
	SUBSTRING(edit_u.username, 1, 255) AS _,
	p.edittime AS _,
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
	p.pid AS id_poll, SUBSTRING(p.question, 1, 255) AS question, p.closed AS _,
	t.uid AS id_member,
	IF(p.timeout = 0, 0, p.dateline + p.timeout * 86400) AS _,
	SUBSTRING(t.username, 1, 255) AS _
FROM {$from_prefix}polls AS p
	LEFT JOIN {$from_prefix}threads AS t ON (t.tid = p.tid);
---*

/******************************************************************************/
--- Converting poll options...
/******************************************************************************/

---* {$to_prefix}poll_choices
---{
$no_add = true;
$keys = array('id_poll', 'id_choice', 'label', 'votes');

$options = explode('||~|~||', $row['options']);
$votes = explode('||~|~||', $row['votes']);
for ($i = 0, $n = count($options); $i < $n; $i++)
	$rows[] = $row['id_poll'] . ', ' . ($i + 1) . ", SUBSTRING('" . addslashes($options[$i]) . "', 1, 255), '" . $votes[$i] . "'";
---}
SELECT pid AS id_poll, options, votes
FROM {$from_prefix}polls;
---*

/******************************************************************************/
--- Converting poll votes...
/******************************************************************************/

---* {$to_prefix}log_polls
SELECT pid AS id_poll, uid AS id_member, voteoption AS id_choice
FROM {$from_prefix}pollvotes;
---*

/******************************************************************************/
--- Converting personal messages (step 1)...
/******************************************************************************/

TRUNCATE {$to_prefix}personal_messages;

---* {$to_prefix}personal_messages
SELECT
	pm.pmid AS id_pm, pm.fromid AS id_member_from, pm.dateline AS msgtime,
	SUBSTRING(uf.username, 1, 255) AS _,
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
SELECT pmid AS id_pm, toid AS id_member, readtime != 0 AS is_read, '-1' AS labels
FROM {$from_prefix}privatemessages
WHERE folder != 2;
---*

/******************************************************************************/
--- Converting topic notifications...
/******************************************************************************/

TRUNCATE {$to_prefix}log_notify;

---* {$to_prefix}log_notify
---{
$ignore = true;
---}
SELECT uid AS id_member, tid AS id_topic
FROM {$from_prefix}threadsubscriptions;
---*

/******************************************************************************/
--- Converting board notifications...
/******************************************************************************/

---* {$to_prefix}log_notify
---{
$ignore = true;
---}
SELECT uid AS id_member, fid AS id_board
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
SELECT uid AS id_member, fid AS id_board
FROM {$from_prefix}moderators;
---*

/******************************************************************************/
--- Converting topic view logs...
/******************************************************************************/

TRUNCATE {$to_prefix}log_topics;

---* {$to_prefix}log_topics
SELECT tid AS id_topic, uid AS id_member
FROM {$from_prefix}threadsread;
---*

/******************************************************************************/
--- Converting attachments...
/******************************************************************************/

---* {$to_prefix}attachments
---{
$no_add = true;
$keys = array('id_attach', 'size', 'filename', 'id_msg', 'downloads', 'width', 'height');

if (!isset($oldAttachmentDir))
{
	$result = convert_query("
		SELECT value
		FROM {$from_prefix}settings
		WHERE name = 'uploadspath'
		LIMIT 1");
	list ($oldAttachmentDir) = mysql_fetch_row($result);
	mysql_free_result($result);

	$oldAttachmentDir = $_POST['path_from'] . ltrim($oldAttachmentDir, '.');
}

// Is this an image???
$attachmentExtension = strtolower(substr(strrchr($row['filename'], '.'), 1));
if (!in_array($attachmentExtension, array('jpg', 'jpeg', 'gif', 'png')))
	$attachmentExtention = '';

$oldFilename = $row['attachname'];
$newfilename = getAttachmentFilename($row['filename'], $id_attach);
if (strlen($newfilename) <= 255 && copy($oldAttachmentDir . '/' . $oldFilename, $attachmentUploadDir . '/' . $newfilename))
{
	// Set the default empty values.
	$width = '0';
	$height = '0';

	// Is an an image?
	if (!empty($attachmentExtension))
		list ($width, $height) = getimagesize($oldAttachmentDir . '/' . $oldFilename);

	$rows[] = "$id_attach, " . filesize($attachmentUploadDir . '/' . $newfilename) . ", '" . addslashes($row['filename']) . "', $row[id_msg], $row[downloads], '$width', '$height'";

	$id_attach++;
}
---}
SELECT pid AS id_msg, downloads, filename, filesize, attachname
FROM {$from_prefix}attachments;
---*