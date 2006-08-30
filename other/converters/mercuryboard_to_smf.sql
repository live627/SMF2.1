/* ATTENTION: You don't need to run or use this file!  The convert.php script does everything for you! */

/******************************************************************************/
---~ name: "MercuryBoard 1.1"
/******************************************************************************/
---~ version: "SMF 2.0 Alpha"
---~ settings: "/settings.php", "/global.php"
---~ from_prefix: "`$set[db_name]`.$set[prefix]"
---~ table_test: "{$from_prefix}users"

/******************************************************************************/
--- Converting members...
/******************************************************************************/

TRUNCATE {$to_prefix}members;

---* {$to_prefix}members
SELECT
	u.user_id AS ID_MEMBER, SUBSTRING(u.user_name, 1, 80) AS memberName,
	u.user_posts AS posts, u.user_joined AS dateRegistered,
	u.user_lastvisit AS lastLogin,
	IF(g.group_type = 'ADMIN', 1, 0) AS ID_GROUP,
	SUBSTRING(u.user_name, 1, 255) AS realName,
	u.user_pm AS instantMessages, SUBSTRING(u.user_password, 1, 64) AS passwd,
	SUBSTRING(u.user_email, 1, 255) AS emailAddress,
	u.user_birthday AS birthdate,
	SUBSTRING(u.user_homepage, 1, 255) AS websiteTitle,
	SUBSTRING(u.user_homepage, 1, 255) AS websiteUrl,
	SUBSTRING(u.user_location, 1, 255) AS location,
	SUBSTRING(u.user_icq, 1, 255) AS ICQ, SUBSTRING(u.user_aim, 1, 16) AS AIM,
	SUBSTRING(u.user_yahoo, 1, 32) AS YIM,
	SUBSTRING(u.user_msn, 1, 255) AS MSN,
	SUBSTRING(u.user_signature, 1, 65534) AS signature,
	IF(u.user_email_show, 0, 1) AS hideEmail, u.user_timezone AS timeOffset,
	SUBSTRING(IF(u.user_avatar_type != 'url', '', u.user_avatar), 1, 255) AS avatar,
	SUBSTRING(u.user_title, 1, 255) AS usertitle, '' AS lngfile,
	'' AS buddy_list, '' AS pm_ignore_list, '' AS messageLabels,
	'' AS personalText, '' AS timeFormat, '' AS memberIP, '' AS secretQuestion,
	'' AS secretAnswer, '' AS validation_code, '' AS additionalGroups,
	'' AS smileySet, '' AS passwordSalt
FROM {$from_prefix}users AS u
	LEFT JOIN {$from_prefix}groups AS g ON (g.group_id = u.user_group)
WHERE u.user_id != 1;
---*

/******************************************************************************/
--- Converting categories...
/******************************************************************************/

TRUNCATE {$to_prefix}categories;

---* {$to_prefix}categories
SELECT
	forum_id AS ID_CAT, SUBSTRING(forum_name, 1, 255) AS name, 
	forum_position AS catOrder
FROM {$from_prefix}forums
WHERE forum_parent = 0;
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
	forum_id AS ID_BOARD, forum_parent AS ID_PARENT,
	SUBSTRING(forum_name, 1, 255) AS name, forum_position AS boardOrder,
	SUBSTRING(forum_description, 1, 65534) AS description,
	forum_topics AS numTopics, forum_replies + forum_topics AS numPosts,
	'-1,0' AS memberGroups
FROM {$from_prefix}forums
WHERE forum_parent != 0;
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
	t.topic_id AS ID_TOPIC, IF(t.topic_modes & 16, 1, 0) AS isSticky,
	t.topic_forum AS ID_BOARD, t.topic_starter AS ID_MEMBER_STARTED,
	t.topic_last_poster AS ID_MEMBER_UPDATED, t.topic_replies AS numReplies,
	t.topic_views AS numViews, IF(t.topic_modes & 1, 1, 0) AS locked,
	MIN(p.post_id) AS ID_FIRST_MSG, MAX(p.post_id) AS ID_LAST_MSG
FROM ({$from_prefix}topics AS t, {$from_prefix}posts AS p)
WHERE p.post_topic = t.topic_id
GROUP BY t.topic_id
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
	p.post_id AS ID_MSG, p.post_topic AS ID_TOPIC, p.post_time AS posterTime,
	p.post_author AS ID_MEMBER, SUBSTRING(t.topic_title, 1, 255) AS subject,
	SUBSTRING(p.post_ip, 1, 255) AS posterIP,
	SUBSTRING(IFNULL(u.user_name, 'Guest'), 1, 255) AS posterName,
	t.topic_forum AS ID_BOARD,
	SUBSTRING(IFNULL(u.user_email, ''), 1, 255) AS posterEmail,
	p.post_emoticons AS smileysEnabled,
	SUBSTRING(REPLACE(p.post_text, '<br>', '<br />'), 1, 65534) AS body,
	'xx' AS icon
FROM ({$from_prefix}posts AS p, {$from_prefix}topics AS t)
	LEFT JOIN {$from_prefix}users AS u ON (u.user_id = p.post_author)
WHERE t.topic_id = p.post_topic;
---*

/******************************************************************************/
--- Converting polls...
/******************************************************************************/

TRUNCATE {$to_prefix}polls;
TRUNCATE {$to_prefix}poll_choices;
TRUNCATE {$to_prefix}log_polls;

---* {$to_prefix}polls
---{
convert_query("
	UPDATE {$to_prefix}topics
	SET ID_POLL = $row[ID_POLL]
	WHERE ID_TOPIC = $row[ID_POLL]
	LIMIT 1");
---}
SELECT
	t.topic_id AS ID_POLL, SUBSTRING(t.topic_title, 1, 255) AS question,
	SUBSTRING(IFNULL(u.user_name, 'Guest'), 1, 255) AS posterName,
	t.topic_starter AS ID_MEMBER
FROM {$from_prefix}topics AS t
	LEFT JOIN {$from_prefix}users AS u ON (u.user_id = t.topic_starter)
WHERE t.topic_modes & 4;
---*

/******************************************************************************/
--- Converting poll options...
/******************************************************************************/

---* {$to_prefix}poll_choices
---{
if (!isset($last_ID_POLL) || $last_ID_POLL != $row['ID_POLL'])
{
	if (isset($last_ID_POLL) && !empty($choices))
	{
		foreach ($choices as $id => $label)
			$rows[] = "$last_ID_POLL, '" . addslashes($label) . "', " . ($id + 1) . ", 0";
		$choices = array();
	}

	$last_ID_POLL = $row['ID_POLL'];
	$choices = explode("\n", $row['label']);
}

$row['label'] = substr($choices[$row['ID_CHOICE'] - 1], 0, 255);
unset($choices[$row['ID_CHOICE'] - 1]);
---}
SELECT
	t.topic_id AS ID_POLL, t.topic_poll_options AS label,
	(v.vote_option + 1) AS ID_CHOICE, COUNT(DISTINCT v.vote_user) AS votes
FROM {$from_prefix}topics AS t
	LEFT JOIN {$from_prefix}votes AS v ON (v.vote_topic = t.topic_id)
WHERE t.topic_poll_options != ''
GROUP BY t.topic_id, v.vote_option;
---*

---{
if (isset($last_ID_POLL) && !empty($choices))
{
	$rows = array();
	foreach ($choices as $id => $label)
		$rows[] = "$last_ID_POLL, '" . addslashes($label) . "', " . ($id + 1) . ", 0";
	$choices = array();

	convert_query("
		INSERT INTO {$to_prefix}poll_choices
			(ID_POLL, label, ID_CHOICE, votes)
		VALUES (" . implode('),
			(', $rows) . ")");
}
---}

/******************************************************************************/
--- Converting poll votes...
/******************************************************************************/

---* {$to_prefix}log_polls
SELECT
	vote_topic AS ID_POLL, vote_user AS ID_MEMBER,
	(vote_option + 1) AS ID_CHOICE
FROM {$from_prefix}votes;
---*

/******************************************************************************/
--- Converting personal messages (step 1)...
/******************************************************************************/

TRUNCATE {$to_prefix}personal_messages;

---* {$to_prefix}personal_messages
SELECT
	p.pm_id AS ID_PM, p.pm_from AS ID_MEMBER_FROM, p.pm_time AS msgtime,
	SUBSTRING(IFNULL(u.user_name, 'Guest'), 1, 255) AS fromName,
	SUBSTRING(p.pm_title, 1, 255) AS subject,
	SUBSTRING(p.pm_message, 1, 65534) AS body
FROM {$from_prefix}pmsystem AS p
	LEFT JOIN {$from_prefix}users AS u ON (u.user_id = p.pm_from)
WHERE p.pm_folder != 1;
---*

/******************************************************************************/
--- Converting personal messages (step 2)...
/******************************************************************************/

TRUNCATE {$to_prefix}pm_recipients;

---* {$to_prefix}pm_recipients
SELECT pm_id AS ID_PM, pm_to AS ID_MEMBER, pm_read AS is_read, '' AS labels
FROM {$from_prefix}pmsystem
WHERE pm_folder != 1;
---*

/******************************************************************************/
--- Converting attachments...
/******************************************************************************/

---* {$to_prefix}attachments
---{
$no_add = true;
$keys = array('ID_ATTACH', 'size', 'filename', 'ID_MSG', 'downloads');

// Doesn't exist?
if (!file_exists($_POST['path_from'] . '/attachments/' . $row['attach_file']))
	continue;

// Get the filesize!
$row['size'] = filesize($_POST['path_from'] . '/attachments/' . $row['attach_file']);
// Something up?
if (!is_integer($row['size']))
	continue;

$newfilename = getAttachmentFilename($row['filename'], $ID_ATTACH);
if (strlen($newfilename) <= 255 && copy($_POST['path_from'] . '/attachments/' . $row['attach_file'], $attachmentUploadDir . '/' . $newfilename))
{
	$rows[] = "$ID_ATTACH, $row[size], '" . addslashes($row['filename']) . "', $row[ID_MSG], $row[downloads]";

	$ID_ATTACH++;
}
---}
SELECT
	attach_file, attach_name AS filename, attach_post AS ID_MSG,
	attach_downloads AS downloads
FROM {$from_prefix}attach;
---*