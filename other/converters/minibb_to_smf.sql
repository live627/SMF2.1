/* ATTENTION: You don't need to run or use this file!  The convert.php script does everything for you! */

/******************************************************************************/
---~ name: "MiniBB 2.0"
/******************************************************************************/
---~ version: "SMF 2.0 Alpha"
---~ settings: "/setup_options.php"
---~ from_prefix: "`$DBname`."
---~ globals: Tf, Tp, Tt, Tu, Ts, Tb, admin_usr
---~ table_test: "{$from_prefix}{$Tu}"

/******************************************************************************/
--- Converting members...
/******************************************************************************/

TRUNCATE {$to_prefix}members;

---* {$to_prefix}members
SELECT
	user_id AS ID_MEMBER, SUBSTRING(username, 1, 80) AS memberName,
	SUBSTRING(username, 1, 255) AS realName,
	UNIX_TIMESTAMP(user_regdate) AS dateRegistered,
	SUBSTRING(user_email, 1, 255) AS emailAddress,
	UNIX_TIMESTAMP(user_regdate) AS lastLogin,
	SUBSTRING(user_from, 1, 255) AS location,
	SUBSTRING(user_password, 1, 64) AS passwd,
	SUBSTRING(user_icq, 1, 255) AS ICQ,
	SUBSTRING(user_website, 1, 255) AS websiteTitle,
	SUBSTRING(user_website, 1, 255) AS websiteUrl,
	IF(user_viewemail = 1, 0, 1) AS hideEmail, num_posts AS posts,
	IF('{$admin_usr}' = username, 1, 0) AS ID_GROUP, '' AS lngfile,
	'' AS buddy_list, '' AS pm_ignore_list, '' AS messageLabels,
	'' AS personalText, '' AS AIM, '' AS YIM, '' AS MSN, '' AS timeFormat,
	'' AS signature, '' AS avatar, '' AS usertitle, '' AS memberIP,
	'' AS secretQuestion, '' AS secretAnswer, '' AS validation_code,
	'' AS additionalGroups, '' AS smileySet, '' AS passwordSalt
FROM {$from_prefix}{$Tu};
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
	forum_id AS ID_BOARD, 1 AS ID_CAT, forum_order AS boardOrder,
	SUBSTRING(forum_name, 1, 255) AS name,
	SUBSTRING(forum_desc, 1, 65534) AS description, topics_count AS numTopics,
	posts_count AS numPosts, '-1,0' AS memberGroups
FROM {$from_prefix}{$Tf};
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
	t.topic_id AS ID_TOPIC, t.sticky AS isSticky, t.forum_id AS ID_BOARD,
	t.topic_poster AS ID_MEMBER_STARTED, (t.posts_count - 1) AS numReplies,
	t.topic_views AS numViews, t.topic_status AS locked,
	MIN(p.post_id) AS ID_FIRST_MSG, MAX(p.post_id) AS ID_LAST_MSG
FROM ({$from_prefix}{$Tt} AS t, {$from_prefix}{$Tp} AS p)
WHERE p.topic_id = t.topic_id
GROUP BY t.topic_id
HAVING ID_FIRST_MSG != 0
	AND ID_LAST_MSG != 0;
---*

---* {$to_prefix}topics (update ID_TOPIC)
SELECT t.ID_TOPIC, p.poster_id AS ID_MEMBER_UPDATED
FROM ({$to_prefix}topics AS t, {$from_prefix}{$Tp} AS p)
WHERE p.post_id = t.ID_LAST_MSG;
---*

/******************************************************************************/
--- Converting posts (this may take some time)...
/******************************************************************************/

TRUNCATE {$to_prefix}messages;
TRUNCATE {$to_prefix}attachments;

---* {$to_prefix}messages 200
SELECT
	p.post_id AS ID_MSG, p.topic_id AS ID_TOPIC,
	SUBSTRING(t.topic_title, 1, 255) AS subject,
	UNIX_TIMESTAMP(p.post_time) AS posterTime,
	SUBSTRING(p.poster_ip, 1, 255) AS posterIP, p.poster_id AS ID_MEMBER,
	SUBSTRING(IFNULL(u.username, p.poster_name), 1, 255) AS posterName,
	SUBSTRING(IFNULL(u.user_email, ''), 1, 255) AS posterEmail,
	p.forum_id AS ID_BOARD,
	SUBSTRING(REPLACE(p.post_text, '<br>', '<br />'), 1, 65534) AS body,
	'' modifiedName, 'xx' AS icon
FROM ({$from_prefix}{$Tp} AS p, {$from_prefix}{$Tt} AS t)
	LEFT JOIN {$from_prefix}{$Tu} AS u ON (u.user_id = p.poster_id)
WHERE t.topic_id = p.topic_id;
---*

/******************************************************************************/
--- Clearing unused tables...
/******************************************************************************/

TRUNCATE {$to_prefix}polls;
TRUNCATE {$to_prefix}poll_choices;
TRUNCATE {$to_prefix}log_polls;
TRUNCATE {$to_prefix}personal_messages;
TRUNCATE {$to_prefix}pm_recipients;