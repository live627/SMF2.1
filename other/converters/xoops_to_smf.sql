/* ATTENTION: You don't need to run or use this file!  The convert.php script does everything for you! */

/******************************************************************************/
---~ name: "XOOPS & newBB 2"
/******************************************************************************/
---~ version: "SMF 2.0 Beta 4"
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
	uid AS id_member, SUBSTRING(uname, 1, 80) AS member_name,
	user_regdate AS date_registered, SUBSTRING(pass, 1, 64) AS passwd,
	SUBSTRING(IF(name = '', uname, name), 1, 255) AS real_name, posts,
	SUBSTRING(email, 1, 255) AS email_address,
	SUBSTRING(url, 1, 255) AS website_title,
	SUBSTRING(url, 1, 255) AS website_url, IF(rank = 7, 1, 0) AS id_group,
	SUBSTRING(user_icq, 1, 255) AS icq, SUBSTRING(user_aim, 1, 16) AS aim,
	SUBSTRING(user_yim, 1, 32) AS yim, SUBSTRING(user_msnm, 1, 255) AS msn,
	SUBSTRING(user_sig, 1, 65534) AS signature,
	user_viewemail = 0 AS hide_email, timezone_offset AS time_offset,
	'' AS lngfile, '' AS buddy_list, '' AS pm_ignore_list, '' AS message_labels,
	'' AS personal_text, '' AS location, '' AS time_format, '' AS avatar,
	'' AS usertitle, '' AS member_ip, '' AS secret_question, '' AS secret_answer,
	'' AS validation_code, '' AS additional_groups, '' AS smiley_set,
	'' AS password_salt, '' AS member_ip2
FROM {$from_prefix}users;
---*

/******************************************************************************/
--- Converting categories...
/******************************************************************************/

TRUNCATE {$to_prefix}categories;

---* {$to_prefix}categories
SELECT
	cat_id AS id_cat, SUBSTRING(cat_title, 1, 255) AS name,
	cat_order AS cat_order
FROM {$from_prefix}bb_categories;
---*

/******************************************************************************/
--- Converting boards...
/******************************************************************************/

TRUNCATE {$to_prefix}boards;

DELETE FROM {$to_prefix}board_permissions
WHERE id_board != 0;

---* {$to_prefix}boards
SELECT
	forum_id AS id_board, cat_id AS id_cat,
	SUBSTRING(forum_name, 1, 255) AS name,
	SUBSTRING(forum_desc, 1, 65534) AS description, forum_topics AS num_topics,
	IF(forum_access = 1, '0,2', IF(forum_access = 3, '', '0,-1,2')) AS member_groups,
	forum_posts AS num_posts
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
	t.topic_id AS id_topic, t.topic_sticky AS is_sticky, t.forum_id AS id_board,
	t.topic_last_post_id AS id_last_msg, t.topic_poster AS id_member_started,
	t.topic_replies AS num_replies, t.topic_views AS num_views,
	t.topic_status AS locked, MIN(p.post_id) AS id_first_msg
FROM ({$from_prefix}bb_topics AS t, {$from_prefix}bb_posts AS p)
WHERE p.topic_id = t.topic_id
GROUP BY t.topic_id
HAVING id_first_msg != 0
	AND id_last_msg != 0;
---*

---* {$to_prefix}topics (update id_topic)
SELECT t.id_topic, p.uid AS id_member_updated
FROM ({$to_prefix}topics AS t, {$from_prefix}bb_posts AS p)
WHERE p.post_id = t.id_last_msg;
---*

/******************************************************************************/
--- Converting posts (this may take some time)...
/******************************************************************************/

TRUNCATE {$to_prefix}messages;
TRUNCATE {$to_prefix}attachments;

---* {$to_prefix}messages 200
SELECT
	p.post_id AS id_msg, p.topic_id AS id_topic, p.post_time AS poster_time,
	p.uid AS id_member, SUBSTRING(p.subject, 1, 255) AS subject,
	SUBSTRING(u.email, 1, 255) AS poster_email,
	SUBSTRING(IFNULL(u.name, 'Guest'), 1, 255) AS poster_name,
	SUBSTRING(p.poster_ip, 1, 255) AS poster_ip,
	IF(p.nosmiley, 0, 1) AS smileys_enabled, p.forum_id AS id_board,
	SUBSTRING(REPLACE(pt.post_text, '<br>', '<br />'), 1, 65534) AS body,
	'' AS modified_name, 'xx' AS icon
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
	p.msg_id AS id_pm, p.from_userid AS id_member_from, p.msg_time AS msgtime,
	SUBSTRING(IFNULL(u.name, 'Guest'), 1, 255) AS from_name,
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
	msg_id AS id_pm, to_userid AS id_member, read_msg AS is_read,
	'' AS labels
FROM {$from_prefix}priv_msgs;
---*

/******************************************************************************/
--- Converting moderators...
/******************************************************************************/

TRUNCATE {$to_prefix}moderators;

---* {$to_prefix}moderators
SELECT user_id AS id_member, forum_id AS id_board
FROM {$from_prefix}bb_forum_mods;
---*