/* ATTENTION: You don't need to run or use this file!  The convert.php script does everything for you! */

/******************************************************************************/
---~ name: "Snitz Forums"
/******************************************************************************/
---~ version: "SMF 2.0 Alpha"
---~ parameters: snitz_database text=MySQL database used by Snitz
---~ from_prefix: "`$snitz_database`."
---~ table_test: "{$from_prefix}FORUM_MEMBERS"

/******************************************************************************/
--- Converting members...
/******************************************************************************/

TRUNCATE {$to_prefix}members;

---* {$to_prefix}members
SELECT
	MEMBER_ID AS id_member, SUBSTRING(M_NAME, 1, 255) AS real_name,
	SUBSTRING(M_PASSWORD, 1, 64) AS passwd,
	SUBSTRING(M_EMAIL, 1, 255) AS email_address,
	SUBSTRING(M_COUNTRY, 1, 255) AS location, 
	SUBSTRING(M_HOMEPAGE, 1, 255) AS website_title,
	SUBSTRING(M_HOMEPAGE, 1, 255) AS website_url,
	SUBSTRING(REPLACE(M_SIG, '\n', '<br />'), 1, 65534) AS signature,
	SUBSTRING(M_aim, 1, 16) AS aim, SUBSTRING(M_icq, 1, 255) AS icq,
	SUBSTRING(M_msn, 1, 255) AS msn, SUBSTRING(M_YAHOO, 1, 32) AS yim,
	IF(M_LEVEL = 3, 1, 0) AS id_group, M_POSTS AS posts,
	UNIX_TIMESTAMP(M_DATE) AS date_registered,
	SUBSTRING(M_TITLE, 1, 255) AS usertitle,
	UNIX_TIMESTAMP(M_LASTHEREDATE) AS last_login, M_HIDE_EMAIL AS hide_email,
	IF(M_RECEIVE_EMAIL, 4, 0) AS notify_types, M_DOB AS birthdate,
	CASE M_SEX WHEN 'Male' THEN 1 WHEN 'Female' THEN 2 ELSE 0 END AS gender,
	SUBSTRING(IF(M_USERNAME = '', M_NAME, M_USERNAME), 1, 80) AS member_name,
	SUBSTRING(M_LAST_IP, 1, 255) AS member_ip, '' AS lngfile, '' AS buddy_list,
	'' AS pm_ignore_list, '' AS message_labels, '' AS personal_text,
	'' AS time_format, '' AS avatar, '' AS secret_question, '' AS secret_answer,
	'' AS validation_code, '' AS additional_groups, '' AS smiley_set,
	'' AS password_salt, SUBSTRING(M_LAST_IP, 1, 255) AS member_ip2
FROM {$from_prefix}FORUM_MEMBERS;
---*

/******************************************************************************/
--- Converting categories...
/******************************************************************************/

TRUNCATE {$to_prefix}categories;

---* {$to_prefix}categories
SELECT
	CAT_ID AS id_cat, SUBSTRING(CAT_NAME, 1, 255) AS name,
	CAT_ORDER AS cat_order
FROM {$from_prefix}FORUM_CATEGORY;
---*

/******************************************************************************/
--- Converting boards...
/******************************************************************************/

TRUNCATE {$to_prefix}boards;

DELETE FROM {$to_prefix}board_permissions
WHERE id_board != 0;

---* {$to_prefix}boards
SELECT
	FORUM_ID AS id_board, CAT_ID AS id_cat, F_ORDER AS board_order,
	SUBSTRING(F_SUBJECT, 1, 255) AS name,
	SUBSTRING(F_DESCRIPTION, 1, 65534) AS description, F_TOPICS AS num_topics,
	F_COUNT AS num_posts, F_COUNT_M_POSTS = 0 AS count_posts,
	'-1,0' AS member_groups
FROM {$from_prefix}FORUM_FORUM;
---*

/******************************************************************************/
--- Converting posts (this may take some time)...
/******************************************************************************/

TRUNCATE {$to_prefix}messages;
TRUNCATE {$to_prefix}attachments;

---{
$_SESSION['convert_topics'] = array();
---}

---* {$to_prefix}messages 100
---{
$no_add = true;
$keys = array('id_topic', 'id_board', 'poster_time', 'id_member', 'subject', 'poster_name', 'poster_email', 'poster_ip', 'body', 'modified_name', 'modified_time', 'icon');

if (!in_array($row['id_topic'], $_SESSION['convert_topics']))
{
	$rows[] = "$row[id_topic], $row[id_board], $row[Tposter_time], $row[TID_MEMBER], '" . addslashes(substr($row['subject'], 0, 255)) . "', '" . addslashes(substr($row['Tposter_name'], 0, 255)) . "', '" . addslashes(substr($row['Tposter_email'], 0, 255)) . "', '" . substr($row[Tposter_ip], 0, 255) . "', '" . addslashes(substr($row['Tbody'], 0, 65534)) . "', " . ($row['Tmodified_name'] == '' ? 'NULL' : "'" . addslashes(substr($row['Tmodified_name'], 0, 255)) . "'") . ", " . (int) $row['Tmodified_time'] . ", 'xx';
	$_SESSION['convert_topics'][] = $row['id_topic'];
}

if (!empty($row['Rposter_time']))
	$rows[] = "$row[id_topic], $row[id_board], $row[Rposter_time], $row[RID_MEMBER], 'Re: " . addslashes(substr($row['subject'], 0, 255)) . "', '" . addslashes(substr($row['Rposter_name'], 0, 255)) . "', '" . addslashes(substr($row['Rposter_email'], 0, 255)) . "', '" . substr($row[Rposter_ip], 0, 255) . "', '" . addslashes(substr($row['Rbody'], 0, 65534) . "', " . ($row['Rmodified_name'] == '' ? 'NULL' : "'" . addslashes(substr($row['Rmodified_name'], 0, 255)) . "'") . ", " . (int) $row['Rmodified_time'] . ", 'xx';
---}
SELECT
	ft.TOPIC_ID AS id_topic, ft.FORUM_ID AS id_board, T_SUBJECT AS subject,
	UNIX_TIMESTAMP(ft.T_DATE) AS Tposter_time, T_AUTHOR AS TID_MEMBER,
	T_IP AS Tposter_ip, IFNULL(ftm.real_name, '') AS Tposter_name,
	REPLACE(REPLACE(T_MESSAGE, '\n', '<br />'), '\r', '') AS Tbody,
	IFNULL(ftm.email_address, '') AS Tposter_email, fte.real_name AS Tmodified_name,
	UNIX_TIMESTAMP(ft.T_LAST_EDIT) AS Tmodified_time,
	UNIX_TIMESTAMP(fr.R_DATE) AS Rposter_time, R_AUTHOR AS RID_MEMBER,
	R_IP AS Rposter_ip, IFNULL(frm.real_name, '') AS Rposter_name,
	REPLACE(REPLACE(R_MESSAGE, '\n', '<br />'), '\r', '') AS Rbody,
	IFNULL(frm.email_address, '') AS Rposter_email, fre.real_name AS Rmodified_name,
	UNIX_TIMESTAMP(fr.R_LAST_EDIT) AS Rmodified_time
FROM {$from_prefix}FORUM_TOPICS AS ft
	LEFT JOIN {$from_prefix}FORUM_REPLY AS fr ON (fr.TOPIC_ID = ft.TOPIC_ID)
	LEFT JOIN {$to_prefix}members AS ftm ON (ftm.id_member = ft.T_AUTHOR)
	LEFT JOIN {$to_prefix}members AS fte ON (fte.id_member = ft.T_LAST_EDITBY)
	LEFT JOIN {$to_prefix}members AS frm ON (frm.id_member = fr.R_AUTHOR)
	LEFT JOIN {$to_prefix}members AS fre ON (fre.id_member = fr.R_LAST_EDITBY)
ORDER BY IFNULL(fr.R_DATE, ft.T_DATE);
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
	t.TOPIC_ID AS id_topic, t.T_STICKY AS is_sticky, t.FORUM_ID AS id_board,
	MIN(m.id_msg) AS id_first_msg, MAX(m.id_msg) AS id_last_msg,
	t.T_AUTHOR AS id_member_started, t.T_LAST_POST_AUTHOR AS id_member_updated,
	t.T_REPLIES AS num_replies, t.T_VIEW_COUNT AS num_views,
	t.T_STATUS = 0 AS locked
FROM ({$from_prefix}FORUM_TOPICS AS t, {$to_prefix}messages AS m)
WHERE m.id_topic = t.TOPIC_ID
GROUP BY t.TOPIC_ID
HAVING id_first_msg != 0
	AND id_last_msg != 0;
---*

/******************************************************************************/
--- Converting topic notifications...
/******************************************************************************/

TRUNCATE {$to_prefix}log_notify;

---* {$to_prefix}log_notify
SELECT MEMBER_ID AS id_member, TOPIC_ID AS id_topic
FROM {$from_prefix}FORUM_SUBSCRIPTIONS
WHERE TOPIC_ID != 0;
---*

/******************************************************************************/
--- Converting board notifications...
/******************************************************************************/

---* {$to_prefix}log_notify
SELECT MEMBER_ID AS id_member, FORUM_ID AS id_board
FROM {$from_prefix}FORUM_SUBSCRIPTIONS
WHERE TOPIC_ID = 0
	AND FORUM_ID != 0;
---*

/******************************************************************************/
--- Converting censored words...
/******************************************************************************/

DELETE FROM {$to_prefix}settings
WHERE variable IN ('censor_vulgar', 'censor_proper');

---# Moving censored words...
---{
$result = convert_query("
	SELECT B_BADWORD, B_REPLACE
	FROM {$from_prefix}FORUM_BADWORDS");
$censor_vulgar = array();
$censor_proper = array();
while ($row = mysql_fetch_assoc($result))
{
	$censor_vulgar[] = $row['B_BADWORD'];
	$censor_proper[] = $row['B_REPLACE'];
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
SELECT MEMBER_ID AS id_member, FORUM_ID AS id_board
FROM {$from_prefix}FORUM_MODERATOR;
---*