/* ATTENTION: You don't need to run or use this file!  The convert.php script does everything for you! */

/******************************************************************************/
---~ name: "MyTopix 1.2.x"
/******************************************************************************/
---~ version: "SMF 2.0 Alpha"
---~ settings: "/config/settings.php"
---~ from_prefix: "`{$config['db_name']}`.{$config['db_pref']}"
---~ table_test: "{$from_prefix}members"

/******************************************************************************/
--- Converting members...
/******************************************************************************/

TRUNCATE {$to_prefix}members;

---* {$to_prefix}members
SELECT
	members_id AS ID_MEMBER, SUBSTRING(members_name, 1, 255) AS memberName,
	SUBSTRING(members_ip, 1, 255) AS memberIP,
	SUBSTRING(members_name, 1, 255) AS realName,
	SUBSTRING(members_pass, 1, 64) AS passwd, members_posts AS posts,
	SUBSTRING(members_pass_salt, 1, 5) AS passwordSalt,
	SUBSTRING(members_email, 1, 255) AS emailAddress,
	SUBSTRING(members_homepage, 1, 255) AS websiteUrl,
	SUBSTRING(members_homepage, 1, 255) AS websiteTitle,
	members_registered AS dateRegistered, members_lastaction AS lastLogin,
	IF(members_is_admin, 1, IF(members_is_super_mod, 2, 0)) AS ID_GROUP,
	members_show_email = 0 AS hideEmail,
	SUBSTRING(members_location, 1, 255) AS location,
	SUBSTRING(members_aim, 1, 16) AS AIM,
	SUBSTRING(members_icq, 1, 255) AS ICQ,
	SUBSTRING(members_yim, 1, 32) AS YIM,
	SUBSTRING(members_msn, 1, 255) AS MSN,
	members_noteNotify AS pm_email_notify,
	SUBSTRING(REPLACE(REPLACE(members_sig, '\r', ''), '\n', '<br />'), 1, 65534) AS signature,
	SUBSTRING(IF(members_avatar_type = 2, members_avatar_location, ''), 1, 255) AS avatar,
	CONCAT_WS('-', members_birth_year, members_birth_month, members_birth_day) AS birthdate,
	'' AS lngfile, '' AS buddy_list, '' AS pm_ignore_list, '' AS messageLabels,
	'' AS personalText, '' AS timeFormat, '' AS usertitle,
	'' AS secretQuestion, '' AS secretAnswer, '' AS validation_code,
	'' AS additionalGroups, '' AS smileySet
	/* // !!! members_avatar_type: 1 = gallery, 3 = upload */
FROM {$from_prefix}members
WHERE members_pass != '';
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

---* {$to_prefix}boards
SELECT
	forum_id AS ID_BOARD, forum_parent AS ID_PARENT,
	SUBSTRING(forum_name, 1, 255) AS name,
	SUBSTRING(forum_description, 1, 65534) AS description,
	forum_topics AS numTopics, forum_posts AS numPosts,
	forum_position AS boardOrder, forum_enable_post_counts = 0 AS countPosts,
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
	t.topics_id AS ID_TOPIC, t.topics_forum AS ID_BOARD,
	IF(t.topics_author = 1, 0, t.topics_author) AS ID_MEMBER_STARTED,
	IF(t.topics_last_poster = 1, 0, t.topics_last_poster) AS ID_MEMBER_UPDATED,
	t.topics_views AS numViews, t.topics_state = 0 AS locked,
	IF(t.topics_is_poll = 1, pl.poll_id, 0) AS ID_POLL,
	t.topics_pinned AS isSticky, MIN(p.posts_id) AS ID_FIRST_MSG,
	MAX(p.posts_id) AS ID_LAST_MSG
FROM ({$from_prefix}topics AS t, {$from_prefix}posts AS p)
	LEFT JOIN {$from_prefix}polls AS pl ON (pl.poll_topic = t.topics_id)
WHERE p.posts_topic = t.topics_id
	AND t.topics_moved = 0
GROUP BY t.topics_id
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
	p.posts_id AS ID_MSG, p.posts_topic AS ID_TOPIC,
	SUBSTRING(p.posts_ip, 1, 255) AS posterIP,
	IF(p.posts_author = 1, 0, p.posts_author) AS ID_MEMBER,
	p.posts_date AS posterTime, p.posts_emoticons AS smileysEnabled,
	SUBSTRING(REPLACE(REPLACE(p.posts_body, '\r', ''), '\n', '<br />'), 1, 65534) AS body,
	SUBSTRING(t.topics_title, 1, 255) AS subject,
	SUBSTRING(p.posts_author_name, 1, 255) AS posterName,
	SUBSTRING(mem.members_email, 1, 255) AS posterEmail,
	t.topics_forum AS ID_BOARD, '' AS modifiedName, 'xx' AS icon
FROM ({$from_prefix}posts AS p, {$from_prefix}topics AS t)
	LEFT JOIN {$from_prefix}members AS mem ON (mem.members_id = p.posts_author)
WHERE t.topics_id = p.posts_topic;
---*

/******************************************************************************/
--- Converting polls...
/******************************************************************************/

TRUNCATE {$to_prefix}polls;
TRUNCATE {$to_prefix}poll_choices;
TRUNCATE {$to_prefix}log_polls;

---* {$to_prefix}polls
SELECT
	p.poll_id AS ID_POLL, SUBSTRING(p.poll_question, 1, 255) AS question,
	p.poll_end_date AS expireTime, p.poll_vote_lock AS votingLocked,
	IF(t.topics_author = 1, 0, t.topics_author) AS ID_MEMBER,
	SUBSTRING(t.topics_author_name, 1, 255) AS posterName
FROM {$from_prefix}polls AS p
	LEFT JOIN {$from_prefix}topics AS t ON (t.topics_id = p.poll_topic);
---*

/******************************************************************************/
--- Converting poll options...
/******************************************************************************/

---* {$to_prefix}poll_choices
---{
$no_add = true;
$keys = array('ID_POLL', 'ID_CHOICE', 'label', 'votes');

$choices = @unserialize($row['choices']);

if (is_array($choices))
	foreach ($choices as $choice)
	{
		$choice = addslashes_recursive($choice);
		$rows[] = "$row[ID_POLL], $choice[id], SUBSTRING('$choice[choice]', 1, 255), $choice[votes]";
	}
---}
SELECT poll_id AS ID_POLL, REPLACE(poll_choices, '\r', '') AS choices
FROM {$from_prefix}polls;
---*

/******************************************************************************/
--- Converting poll votes...
/******************************************************************************/

---* {$to_prefix}log_polls
SELECT p.poll_id AS ID_POLL, v.vote_user AS ID_MEMBER
FROM ({$from_prefix}voters AS v, {$from_prefix}polls AS p)
WHERE v.vote_topic = p.poll_topic;
---*

/******************************************************************************/
--- Converting personal messages (step 1)...
/******************************************************************************/

TRUNCATE {$to_prefix}personal_messages;

---* {$to_prefix}personal_messages
SELECT
	n.notes_id AS ID_PM, n.notes_date AS msgtime,
	SUBSTRING(n.notes_title, 1, 255) AS subject,
	n.notes_sender AS ID_MEMBER_FROM,
	SUBSTRING(memf.members_name, 1, 255) AS fromName,
	SUBSTRING(REPLACE(REPLACE(n.notes_body, '\r', ''), '\n', '<br />'), 1, 65534) AS body
FROM {$from_prefix}notes AS n
	LEFT JOIN {$from_prefix}members AS memf ON (memf.members_id = n.notes_sender);
---*

/******************************************************************************/
--- Converting personal messages (step 2)...
/******************************************************************************/

TRUNCATE {$to_prefix}pm_recipients;

---* {$to_prefix}pm_recipients
SELECT 
	notes_id AS ID_PM, notes_recipient AS ID_MEMBER, notes_isRead AS is_read,
	'' AS labels
FROM {$from_prefix}notes;
---*

/******************************************************************************/
--- Converting notifications...
/******************************************************************************/

TRUNCATE {$to_prefix}log_notify;

---* {$to_prefix}log_notify
SELECT
	track_user AS ID_MEMBER, track_topic AS ID_TOPIC, track_forum AS ID_BOARD,
	track_sent AS sent
FROM {$from_prefix}tracker;
---*

/******************************************************************************/
--- Converting attachments...
/******************************************************************************/

---* {$to_prefix}attachments
---{
$no_add = true;
$keys = array('ID_ATTACH', 'size', 'filename', 'ID_MSG', 'downloads');

$newfilename = getAttachmentFilename($row['filename'], $ID_ATTACH);
if (strlen($newfilename) <= 255 && copy($_POST['path_from'] . '/uploads/attachments/' . $row['oldEncrypt'] . '.' . $row['ext'], $attachmentUploadDir . '/' . $newfilename))
{
	$rows[] = "$ID_ATTACH, $row[size], '" . addslashes($row['filename']) . "', $row[ID_MSG], $row[downloads]";

	$ID_ATTACH++;
}
---}
SELECT
	upload_post AS ID_MSG, upload_name AS filename, upload_file AS oldEncrypt,
	upload_size AS size, upload_ext AS ext, upload_hits AS downloads
FROM {$from_prefix}uploads;
---*