/* ATTENTION: You don't need to run or use this file!  The convert.php script does everything for you! */

/******************************************************************************/
---~ name: "UseBB 0.5.1"
/******************************************************************************/
---~ version: "SMF 2.0 Alpha"
---~ settings: "/config.php"
---~ globals: dbs
---~ defines: INCLUDED
---~ from_prefix: "`{$dbs['dbname']}`.{$dbs['prefix']}"
---~ table_test: "{$from_prefix}members"

/******************************************************************************/
--- Converting members...
/******************************************************************************/

TRUNCATE {$to_prefix}members;

---* {$to_prefix}members
SELECT
	id AS ID_MEMBER, SUBSTRING(name, 1, 80) AS memberName,
	SUBSTRING(email, 1, 255) AS emailAddress, email_show = 0 AS hideEmail,
	SUBSTRING(passwd, 1, 64) AS passwd, regdate AS dateRegistered,
	IF(level = 3, 1, 0) AS ID_GROUP, active AS is_activated,
	SUBSTRING(active_key, 1, 10) AS validation_code,
	last_pageview AS lastLogin, hide_from_online_list = 0 AS showOnline, posts,
	SUBSTRING(avatar_remote, 1, 255) AS avatar,
	SUBSTRING(displayed_name, 1, 255) AS realName,
	SUBSTRING(signature, 1, 65534) AS signature, birthday AS birthdate,
	SUBSTRING(location, 1, 255) AS location,
	SUBSTRING(website, 1, 255) AS websiteUrl,
	SUBSTRING(website, 1, 255) AS websiteTitle,
	SUBSTRING(msnm, 1, 255) AS MSN, SUBSTRING(yahoom, 1, 32) AS YIM,
	SUBSTRING(aim, 1, 16) AS AIM, SUBSTRING(icq, 1, 255) AS ICQ
FROM {$from_prefix}members;
---*

/******************************************************************************/
--- Converting categories...
/******************************************************************************/

TRUNCATE {$to_prefix}categories;

---* {$to_prefix}categories
SELECT id AS ID_CAT, SUBSTRING(name, 1, 255) AS name, sort_id AS catOrder
FROM {$from_prefix}cats;
---*

/******************************************************************************/
--- Converting boards...
/******************************************************************************/

TRUNCATE {$to_prefix}boards;

DELETE FROM {$to_prefix}board_permissions
WHERE ID_BOARD != 0;

---* {$to_prefix}boards
SELECT
	id AS ID_BOARD, SUBSTRING(name, 1, 255) AS name, cat_id AS ID_CAT,
	SUBSTRING(descr, 1, 65534) AS description, topics AS numTopics,
	posts AS numPosts, sort_id AS boardOrder,
	increase_post_count = 0 AS countPosts, '-1,0' AS memberGroups
	/* // !!! auth? */
FROM {$from_prefix}forums;
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
	t.id AS ID_TOPIC, t.forum_id AS ID_BOARD, t.status_sticky AS isSticky,
	t.count_views AS numViews, t.count_replies AS numReplies,
	t.status_locked AS locked, t.first_post_id AS ID_FIRST_MSG,
	t.last_post_id AS ID_LAST_MSG, pf.poster_id AS ID_MEMBER_STARTED,
	pl.poster_id AS ID_MEMBER_UPDATED
FROM ({$from_prefix}topics AS t, {$from_prefix}posts AS pf, {$from_prefix}posts AS pl)
WHERE pf.id = t.first_post_id
	AND pl.id = t.last_post_id
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
	p.id AS ID_MSG, p.topic_id AS ID_TOPIC, t.forum_id AS ID_BOARD,
	p.poster_id AS ID_MEMBER, SUBSTRING(p.poster_ip_addr, 1, 255) AS posterIP,
	SUBSTRING(IF(p.poster_guest = '', mem.name, p.poster_guest), 1, 255) AS posterName,
	p.post_time AS posterTime, SUBSTRING(t.topic_title, 1, 255) AS subject,
	SUBSTRING(mem.email, 1, 255) AS posterEmail,
	p.enable_smilies AS smileysEnabled,
	SUBSTRING(edit_mem.displayed_name, 1, 255) AS modifiedName,
	p.post_edit_time AS modifiedTime,
	SUBSTRING(REPLACE(p.content, '<br>', '<br />'), 1, 65534) AS body,
	'xx' AS icon
FROM ({$from_prefix}posts AS p, {$from_prefix}topics AS t)
	LEFT JOIN {$from_prefix}members AS mem ON (mem.id = p.poster_id)
	LEFT JOIN {$from_prefix}members AS edit_mem ON (edit_mem.id = p.post_edit_by)
WHERE t.id = p.topic_id;
---*

/******************************************************************************/
--- Clearing unused tables...
/******************************************************************************/

TRUNCATE {$to_prefix}polls;
TRUNCATE {$to_prefix}poll_choices;
TRUNCATE {$to_prefix}log_polls;
TRUNCATE {$to_prefix}personal_messages;
TRUNCATE {$to_prefix}pm_recipients;

/******************************************************************************/
--- Converting topic notifications...
/******************************************************************************/

TRUNCATE {$to_prefix}log_notify;

---* {$to_prefix}log_notify
SELECT user_id AS ID_MEMBER, topic_id AS ID_TOPIC
FROM {$from_prefix}subscriptions;
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
	FROM {$from_prefix}badwords");
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

/******************************************************************************/
--- Converting moderators...
/******************************************************************************/

TRUNCATE {$to_prefix}moderators;

---* {$to_prefix}moderators
SELECT user_id AS ID_MEMBER, forum_id AS ID_BOARD
FROM {$from_prefix}moderators;
---*