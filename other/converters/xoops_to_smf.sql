/* ATTENTION: You don't need to run or use this file!  The convert.php script does everything for you! */

/******************************************************************************/
---~ name: "XOOPS & newBB 2"
/******************************************************************************/
---~ version: "SMF 2.0 Alpha"
---~ settings: "/mainfile.php"
---~ variable: "$xoopsOption['nocommon'] = 1;"
---~ from_prefix: "`" . XOOPS_DB_NAME . "`." . XOOPS_DB_PREFIX . "_"
---~ table_test: "{$from_prefix}users"

/******************************************************************************/
--- Converting members...
/******************************************************************************/

TRUNCATE {$to_prefix}members;

---* {$to_prefix}members
SELECT
	uid AS ID_MEMBER, SUBSTRING(uname, 1, 80) AS memberName,
	user_regdate AS dateRegistered, SUBSTRING(pass, 1, 64) AS passwd,
	SUBSTRING(IF(name = '', uname, name), 1, 255) AS realName, posts,
	SUBSTRING(email, 1, 255) AS emailAddress,
	SUBSTRING(url, 1, 255) AS websiteTitle,
	SUBSTRING(url, 1, 255) AS websiteUrl, IF(rank = 7, 1, 0) AS ID_GROUP,
	SUBSTRING(user_icq, 1, 255) AS ICQ, SUBSTRING(user_aim, 1, 16) AS AIM,
	SUBSTRING(user_yim, 1, 32) AS YIM, SUBSTRING(user_msnm, 1, 255) AS MSN,
	SUBSTRING(user_sig, 1, 65534) AS signature,
	user_viewemail = 0 AS hideEmail, timezone_offset AS timeOffset,
	'' AS lngfile, '' AS buddy_list, '' AS pm_ignore_list, '' AS messageLabels,
	'' AS personalText, '' AS location, '' AS timeFormat, '' AS avatar,
	'' AS usertitle, '' AS memberIP, '' AS secretQuestion, '' AS secretAnswer,
	'' AS validation_code, '' AS additionalGroups, '' AS smileySet,
	'' AS passwordSalt
FROM {$from_prefix}users;
---*

/******************************************************************************/
--- Converting categories...
/******************************************************************************/

TRUNCATE {$to_prefix}categories;

---* {$to_prefix}categories
SELECT 
	cat_id AS ID_CAT, SUBSTRING(cat_title, 1, 255) AS name,
	cat_order AS catOrder
FROM {$from_prefix}bb_categories;
---*

/******************************************************************************/
--- Converting boards...
/******************************************************************************/

TRUNCATE {$to_prefix}boards;

DELETE FROM {$to_prefix}board_permissions
WHERE ID_BOARD != 0;

---* {$to_prefix}boards
SELECT
	forum_id AS ID_BOARD, cat_id AS ID_CAT,
	SUBSTRING(forum_name, 1, 255) AS name,
	SUBSTRING(forum_desc, 1, 65534) AS description, forum_topics AS numTopics,
	IF(forum_access = 1, '0,2', IF(forum_access = 3, '', '0,-1,2')) AS memberGroups,
	forum_posts AS numPosts
FROM {$from_prefix}bb_forums;
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
	t.topic_id AS ID_TOPIC, t.topic_sticky AS isSticky, t.forum_id AS ID_BOARD,
	t.topic_last_post_id AS ID_LAST_MSG, t.topic_poster AS ID_MEMBER_STARTED,
	t.topic_replies AS numReplies, t.topic_views AS numViews,
	t.topic_status AS locked, MIN(p.post_id) AS ID_FIRST_MSG
FROM ({$from_prefix}bb_topics AS t, {$from_prefix}bb_posts AS p)
WHERE p.topic_id = t.topic_id
GROUP BY t.topic_id
HAVING ID_FIRST_MSG != 0
	AND ID_LAST_MSG != 0;
---*

---* {$to_prefix}topics (update ID_TOPIC)
SELECT t.ID_TOPIC, p.uid AS ID_MEMBER_UPDATED
FROM ({$to_prefix}topics AS t, {$from_prefix}bb_posts AS p)
WHERE p.post_id = t.ID_LAST_MSG;
---*

/******************************************************************************/
--- Converting posts (this may take some time)...
/******************************************************************************/

TRUNCATE {$to_prefix}messages;
TRUNCATE {$to_prefix}attachments;

---* {$to_prefix}messages 200
SELECT
	p.post_id AS ID_MSG, p.topic_id AS ID_TOPIC, p.post_time AS posterTime,
	p.uid AS ID_MEMBER, SUBSTRING(p.subject, 1, 255) AS subject,
	SUBSTRING(u.email, 1, 255) AS posterEmail,
	SUBSTRING(IFNULL(u.name, 'Guest'), 1, 255) AS posterName,
	SUBSTRING(p.poster_ip, 1, 255) AS posterIP,
	IF(p.nosmiley, 0, 1) AS smileysEnabled, p.forum_id AS ID_BOARD,
	SUBSTRING(REPLACE(pt.post_text, '<br>', '<br />'), 1, 65534) AS body,
	'' AS modifiedName, 'xx' AS icon
FROM ({$from_prefix}bb_posts AS p, {$from_prefix}bb_posts_text AS pt)
	LEFT JOIN {$from_prefix}users AS u ON (u.uid = p.uid)
WHERE pt.post_id = p.post_id;
---*

/******************************************************************************/
--- Clearing unused tables...
/******************************************************************************/

TRUNCATE {$to_prefix}polls;
TRUNCATE {$to_prefix}poll_choices;
TRUNCATE {$to_prefix}log_polls;

/******************************************************************************/
--- Converting personal messages (step 1)...
/******************************************************************************/

TRUNCATE {$to_prefix}personal_messages;

---* {$to_prefix}personal_messages
SELECT
	p.msg_id AS ID_PM, p.from_userid AS ID_MEMBER_FROM, p.msg_time AS msgtime,
	SUBSTRING(IFNULL(u.name, 'Guest'), 1, 255) AS fromName,
	SUBSTRING(p.subject, 1, 255) AS subject,
	SUBSTRING(REPLACE(p.msg_text, '<br>', '<br />'), 1, 65534) AS body
FROM {$from_prefix}priv_msgs AS p
	LEFT JOIN {$from_prefix}users AS u ON (u.uid = p.from_userid);
---*

/******************************************************************************/
--- Converting personal messages (step 2)...
/******************************************************************************/

TRUNCATE {$to_prefix}pm_recipients;

---* {$to_prefix}pm_recipients
SELECT
	msg_id AS ID_PM, to_userid AS ID_MEMBER, read_msg AS is_read,
	'' AS labels
FROM {$from_prefix}priv_msgs;
---*

/******************************************************************************/
--- Converting moderators...
/******************************************************************************/

TRUNCATE {$to_prefix}moderators;

---* {$to_prefix}moderators
SELECT user_id AS ID_MEMBER, forum_id AS ID_BOARD
FROM {$from_prefix}bb_forum_mods;
---*