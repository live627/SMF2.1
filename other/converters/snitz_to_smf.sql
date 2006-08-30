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
	MEMBER_ID AS ID_MEMBER, SUBSTRING(M_NAME, 1, 255) AS realName,
	SUBSTRING(M_PASSWORD, 1, 64) AS passwd,
	SUBSTRING(M_EMAIL, 1, 255) AS emailAddress,
	SUBSTRING(M_COUNTRY, 1, 255) AS location, 
	SUBSTRING(M_HOMEPAGE, 1, 255) AS websiteTitle,
	SUBSTRING(M_HOMEPAGE, 1, 255) AS websiteUrl,
	SUBSTRING(REPLACE(M_SIG, '\n', '<br />'), 1, 65534) AS signature,
	SUBSTRING(M_AIM, 1, 16) AS AIM, SUBSTRING(M_ICQ, 1, 255) AS ICQ,
	SUBSTRING(M_MSN, 1, 255) AS MSN, SUBSTRING(M_YAHOO, 1, 32) AS YIM,
	IF(M_LEVEL = 3, 1, 0) AS ID_GROUP, M_POSTS AS posts,
	UNIX_TIMESTAMP(M_DATE) AS dateRegistered,
	SUBSTRING(M_TITLE, 1, 255) AS usertitle,
	UNIX_TIMESTAMP(M_LASTHEREDATE) AS lastLogin, M_HIDE_EMAIL AS hideEmail,
	IF(M_RECEIVE_EMAIL, 4, 0) AS notifyTypes, M_DOB AS birthdate,
	CASE M_SEX WHEN 'Male' THEN 1 WHEN 'Female' THEN 2 ELSE 0 END AS gender,
	SUBSTRING(IF(M_USERNAME = '', M_NAME, M_USERNAME), 1, 80) AS memberName,
	SUBSTRING(M_LAST_IP, 1, 255) AS memberIP, '' AS lngfile, '' AS buddy_list,
	'' AS pm_ignore_list, '' AS messageLabels, '' AS personalText,
	'' AS timeFormat, '' AS avatar, '' AS secretQuestion, '' AS secretAnswer,
	'' AS validation_code, '' AS additionalGroups, '' AS smileySet,
	'' AS passwordSalt
FROM {$from_prefix}FORUM_MEMBERS;
---*

/******************************************************************************/
--- Converting categories...
/******************************************************************************/

TRUNCATE {$to_prefix}categories;

---* {$to_prefix}categories
SELECT
	CAT_ID AS ID_CAT, SUBSTRING(CAT_NAME, 1, 255) AS name,
	CAT_ORDER AS catOrder
FROM {$from_prefix}FORUM_CATEGORY;
---*

/******************************************************************************/
--- Converting boards...
/******************************************************************************/

TRUNCATE {$to_prefix}boards;

DELETE FROM {$to_prefix}board_permissions
WHERE ID_BOARD != 0;

---* {$to_prefix}boards
SELECT
	FORUM_ID AS ID_BOARD, CAT_ID AS ID_CAT, F_ORDER AS boardOrder,
	SUBSTRING(F_SUBJECT, 1, 255) AS name,
	SUBSTRING(F_DESCRIPTION, 1, 65534) AS description, F_TOPICS AS numTopics,
	F_COUNT AS numPosts, F_COUNT_M_POSTS = 0 AS countPosts,
	'-1,0' AS memberGroups
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
$keys = array('ID_TOPIC', 'ID_BOARD', 'posterTime', 'ID_MEMBER', 'subject', 'posterName', 'posterEmail', 'posterIP', 'body', 'modifiedName', 'modifiedTime', 'icon');

if (!in_array($row['ID_TOPIC'], $_SESSION['convert_topics']))
{
	$rows[] = "$row[ID_TOPIC], $row[ID_BOARD], $row[TposterTime], $row[TID_MEMBER], '" . addslashes(substr($row['subject'], 0, 255)) . "', '" . addslashes(substr($row['TposterName'], 0, 255)) . "', '" . addslashes(substr($row['TposterEmail'], 0, 255)) . "', '" . substr($row[TposterIP], 0, 255) . "', '" . addslashes(substr($row['Tbody'], 0, 65534)) . "', " . ($row['TmodifiedName'] == '' ? 'NULL' : "'" . addslashes(substr($row['TmodifiedName'], 0, 255)) . "'") . ", " . (int) $row['TmodifiedTime'] . ", 'xx';
	$_SESSION['convert_topics'][] = $row['ID_TOPIC'];
}

if (!empty($row['RposterTime']))
	$rows[] = "$row[ID_TOPIC], $row[ID_BOARD], $row[RposterTime], $row[RID_MEMBER], 'Re: " . addslashes(substr($row['subject'], 0, 255)) . "', '" . addslashes(substr($row['RposterName'], 0, 255)) . "', '" . addslashes(substr($row['RposterEmail'], 0, 255)) . "', '" . substr($row[RposterIP], 0, 255) . "', '" . addslashes(substr($row['Rbody'], 0, 65534) . "', " . ($row['RmodifiedName'] == '' ? 'NULL' : "'" . addslashes(substr($row['RmodifiedName'], 0, 255)) . "'") . ", " . (int) $row['RmodifiedTime'] . ", 'xx';
---}
SELECT
	ft.TOPIC_ID AS ID_TOPIC, ft.FORUM_ID AS ID_BOARD, T_SUBJECT AS subject,
	UNIX_TIMESTAMP(ft.T_DATE) AS TposterTime, T_AUTHOR AS TID_MEMBER,
	T_IP AS TposterIP, IFNULL(ftm.realName, '') AS TposterName,
	REPLACE(REPLACE(T_MESSAGE, '\n', '<br />'), '\r', '') AS Tbody,
	IFNULL(ftm.emailAddress, '') AS TposterEmail, fte.realName AS TmodifiedName,
	UNIX_TIMESTAMP(ft.T_LAST_EDIT) AS TmodifiedTime,
	UNIX_TIMESTAMP(fr.R_DATE) AS RposterTime, R_AUTHOR AS RID_MEMBER,
	R_IP AS RposterIP, IFNULL(frm.realName, '') AS RposterName,
	REPLACE(REPLACE(R_MESSAGE, '\n', '<br />'), '\r', '') AS Rbody,
	IFNULL(frm.emailAddress, '') AS RposterEmail, fre.realName AS RmodifiedName,
	UNIX_TIMESTAMP(fr.R_LAST_EDIT) AS RmodifiedTime
FROM {$from_prefix}FORUM_TOPICS AS ft
	LEFT JOIN {$from_prefix}FORUM_REPLY AS fr ON (fr.TOPIC_ID = ft.TOPIC_ID)
	LEFT JOIN {$to_prefix}members AS ftm ON (ftm.ID_MEMBER = ft.T_AUTHOR)
	LEFT JOIN {$to_prefix}members AS fte ON (fte.ID_MEMBER = ft.T_LAST_EDITBY)
	LEFT JOIN {$to_prefix}members AS frm ON (frm.ID_MEMBER = fr.R_AUTHOR)
	LEFT JOIN {$to_prefix}members AS fre ON (fre.ID_MEMBER = fr.R_LAST_EDITBY)
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
	t.TOPIC_ID AS ID_TOPIC, t.T_STICKY AS isSticky, t.FORUM_ID AS ID_BOARD,
	MIN(m.ID_MSG) AS ID_FIRST_MSG, MAX(m.ID_MSG) AS ID_LAST_MSG,
	t.T_AUTHOR AS ID_MEMBER_STARTED, t.T_LAST_POST_AUTHOR AS ID_MEMBER_UPDATED,
	t.T_REPLIES AS numReplies, t.T_VIEW_COUNT AS numViews,
	t.T_STATUS = 0 AS locked
FROM ({$from_prefix}FORUM_TOPICS AS t, {$to_prefix}messages AS m)
WHERE m.ID_TOPIC = t.TOPIC_ID
GROUP BY t.TOPIC_ID
HAVING ID_FIRST_MSG != 0
	AND ID_LAST_MSG != 0;
---*

/******************************************************************************/
--- Converting topic notifications...
/******************************************************************************/

TRUNCATE {$to_prefix}log_notify;

---* {$to_prefix}log_notify
SELECT MEMBER_ID AS ID_MEMBER, TOPIC_ID AS ID_TOPIC
FROM {$from_prefix}FORUM_SUBSCRIPTIONS
WHERE TOPIC_ID != 0;
---*

/******************************************************************************/
--- Converting board notifications...
/******************************************************************************/

---* {$to_prefix}log_notify
SELECT MEMBER_ID AS ID_MEMBER, FORUM_ID AS ID_BOARD
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
SELECT MEMBER_ID AS ID_MEMBER, FORUM_ID AS ID_BOARD
FROM {$from_prefix}FORUM_MODERATOR;
---*