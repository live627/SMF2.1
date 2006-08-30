/* ATTENTION: You don't need to run or use this file!  The convert.php script does everything for you! */

/******************************************************************************/
---~ name: "Land Down Under 80x"
/******************************************************************************/
---~ version: "SMF 2.0 Alpha"
---~ settings: "/datas/config.php"
---~ globals: $db_users, $db_forum_sections, $db_forum_topics, $db_forum_posts
---~ globals: $db_polls, $db_polls_options, $db_polls_voters, $db_pm
---~ from_prefix: "`{$cfg['mysqldb']}`."
---~ table_test: "{$from_prefix}$db_users"

/******************************************************************************/
--- Converting members...
/******************************************************************************/

TRUNCATE {$to_prefix}members;

---* {$to_prefix}members
SELECT
	user_id AS ID_MEMBER, user_active AS is_activated,
	SUBSTRING(user_name, 1, 80) AS memberName,
	SUBSTRING(user_name, 1, 255) AS realName,
	SUBSTRING(user_password, 1, 64) AS passwd,
	SUBSTRING(user_location, 1, 255) AS location,
	IF(user_level > 94, 1, 0) AS ID_GROUP, SUBSTRING(user_msn, 1, 255) AS MSN,
	SUBSTRING(user_icq, 1, 255) AS ICQ,
	SUBSTRING(REPLACE(user_text, '\n', '<br />'), 1, 65534) AS signature,
	SUBSTRING(user_lastip, 1, 255) AS memberIP,
	FROM_UNIXTIME(user_birthdate) AS birthdate,
	SUBSTRING(user_website, 1, 255) AS websiteTitle,
	SUBSTRING(user_website, 1, 255) AS websiteUrl, user_hideemail AS hideEmail,
	CASE user_gender WHEN 'M' THEN 1 WHEN 'F' THEN 2 ELSE 0 END AS gender,
	SUBSTRING(user_email, 1, 255) AS emailAddress, 
	user_pmnotify AS pm_email_notify, user_regdate AS dateRegistered,
	user_lastlog AS lastLogin, user_postcount AS posts, '' AS lngfile,
	'' AS buddy_list, '' AS pm_ignore_list, '' AS personalText, '' AS AIM,
	'' AS YIM, '' AS timeFormat, '' AS avatar, '' AS usertitle,
	'' AS secretQuestion, '' AS secretAnswer, '' AS validation_code,
	'' AS additionalGroups, '' AS smileySet, '' AS passwordSalt
FROM {$from_prefix}{$db_users};
---*

/******************************************************************************/
--- Converting categories...
/******************************************************************************/

TRUNCATE {$to_prefix}categories;

---* {$to_prefix}categories
SELECT DISTINCT SUBSTRING(fs_category, 1, 255) AS name
FROM {$from_prefix}{$db_forum_sections}
ORDER BY fs_order;
---*

/******************************************************************************/
--- Converting boards...
/******************************************************************************/

TRUNCATE {$to_prefix}boards;

DELETE FROM {$to_prefix}board_permissions
WHERE ID_BOARD != 0;

---* {$to_prefix}boards
SELECT
	fs.fs_id AS ID_BOARD, fs.fs_order AS boardOrder,
	SUBSTRING(fs.fs_title, 1, 255) AS name, c.ID_CAT,
	SUBSTRING(fs.fs_desc, 1, 65534) AS description,
	fs.fs_postcount AS numPosts, fs_topiccount AS numTopics,
	fs_countposts = 0 AS countPosts, '-1,0' AS memberGroups
FROM ({$from_prefix}{$db_forum_sections} AS fs, {$to_prefix}categories AS c)
WHERE BINARY c.name = fs.fs_category;
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
	t.ft_id AS ID_TOPIC, t.ft_state = 1 AS locked, t.ft_sticky AS isSticky,
	t.ft_sectionid AS ID_BOARD, t.ft_postcount - 1 AS numReplies,
	t.ft_viewcount AS numViews, t.ft_lastposterid AS ID_MEMBER_UPDATED,
	t.ft_firstposterid AS ID_MEMBER_STARTED, MIN(p.fp_id) AS ID_FIRST_MSG,
	MAX(p.fp_id) AS ID_LAST_MSG, t.ft_poll AS ID_POLL
FROM ({$from_prefix}{$db_forum_topics} AS t, {$from_prefix}{$db_forum_posts} AS p)
WHERE p.fp_topicid = t.ft_id
	AND ft_movedto = 0
GROUP BY t.ft_id
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
	p.fp_id AS ID_MSG, p.fp_topicid AS ID_TOPIC, p.fp_sectionid AS ID_BOARD,
	p.fp_posterid AS ID_MEMBER, 
	SUBSTRING(p.fp_postername, 1, 255) AS posterName,
	p.fp_creation AS posterTime,
	SUBSTRING(p.fp_updater, 1, 255) AS modifiedName,
	IF(p.fp_updated != p.fp_creation, p.fp_updated, 0) AS modifiedTime,
	SUBSTRING(REPLACE(p.fp_text, '\n', '<br />'), 1, 65534) AS body,
	SUBSTRING(p.fp_posterip, 1, 255) AS posterIP,
	SUBSTRING(t.ft_title, 1, 255) AS subject,
	SUBSTRING(u.user_email, 1, 255) AS posterEmail, 'xx' AS icon
FROM ({$from_prefix}{$db_forum_posts} AS p, {$from_prefix}{$db_forum_topics} AS t)
	LEFT JOIN {$from_prefix}{$db_users} AS u ON (u.user_id = p.fp_posterid)
WHERE t.ft_id = p.fp_topicid;
---*

/******************************************************************************/
--- Converting polls...
/******************************************************************************/

TRUNCATE {$to_prefix}polls;
TRUNCATE {$to_prefix}poll_choices;
TRUNCATE {$to_prefix}log_polls;

---* {$to_prefix}polls
SELECT
	p.poll_id AS ID_POLL, IF(p.poll_state != 0, 1, 0) AS votingLocked,
	SUBSTRING(p.poll_text, 1, 255) AS question, 
	t.ft_firstposterid AS ID_MEMBER,
	SUBSTRING(t.ft_firstpostername, 1, 255) AS posterName
FROM ({$from_prefix}{$db_polls} AS p, {$from_prefix}{$db_forum_topics} AS t)
WHERE p.poll_type = 1
	AND t.ft_poll = p.poll_id;
---*

/******************************************************************************/
--- Converting poll options...
/******************************************************************************/

---* {$to_prefix}poll_choices
---{
if (!isset($_SESSION['convert_last_poll']) || $_SESSION['convert_last_poll'] != $row['ID_POLL'])
{
	$_SESSION['convert_last_poll'] = $row['ID_POLL'];
	$_SESSION['convert_last_choice'] = 0;
}

$row['ID_CHOICE'] = ++$_SESSION['convert_last_choice'];
---}
SELECT
	po_pollid AS ID_POLL, 0 AS ID_CHOICE,
	SUBSTRING(po_text, 1, 255) AS label, po_count AS votes
FROM {$from_prefix}{$db_polls_options}
ORDER BY po_pollid;
---*

/******************************************************************************/
--- Converting poll votes...
/******************************************************************************/

---* {$to_prefix}log_polls
SELECT pv_pollid AS ID_POLL, pv_userid AS ID_MEMBER
FROM {$from_prefix}{$db_polls_voters};
---*

/******************************************************************************/
--- Converting personal messages (step 1)...
/******************************************************************************/

TRUNCATE {$to_prefix}personal_messages;

---* {$to_prefix}personal_messages
SELECT
	pm_id AS ID_PM, pm_fromuserid AS ID_MEMBER_FROM, pm_date AS msgtime,
	SUBSTRING(pm_fromuser, 1, 255) AS fromName,
	SUBSTRING(pm_title, 1, 255) AS subject,
	SUBSTRING(REPLACE(pm_text, '\n', '<br />'), 1, 65534) AS body
FROM {$from_prefix}{$db_pm};
---*

/******************************************************************************/
--- Converting personal messages (step 2)...
/******************************************************************************/

TRUNCATE {$to_prefix}pm_recipients;

---* {$to_prefix}pm_recipients
SELECT 
	pm_id AS ID_PM, pm_touserid AS ID_MEMBER, pm_state != 0 AS is_read,
	'' AS labels
FROM {$from_prefix}{$db_pm};
---*