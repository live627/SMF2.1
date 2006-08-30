/* ATTENTION: You don't need to run or use this file!  The convert.php script does everything for you! */

/******************************************************************************/
---~ name: "UBB.threads 6.4/6.5"
/******************************************************************************/
---~ version: "SMF 2.0 Alpha"
---~ settings: "/config.inc.php", "/includes/config.inc.php"
---~ globals: config
---~ from_prefix: "`$config[dbname]`.$config[tbprefix]"
---~ table_test: "{$from_prefix}Users"

/******************************************************************************/
--- Converting members...
/******************************************************************************/

TRUNCATE {$to_prefix}members;

---* {$to_prefix}members
SELECT
	U_Number AS ID_MEMBER, SUBSTRING(U_LoginName, 1, 80) AS memberName,
	U_Totalposts AS posts, U_Registered AS dateRegistered,
	U_Laston AS lastLogin, SUBSTRING(U_Title, 1, 255) AS usertitle,
	IF(U_Status = 'Administrator', 1, 0) AS ID_GROUP,
	SUBSTRING(U_Password, 1, 64) AS passwd,
	SUBSTRING(U_Username, 1, 255) AS realName,
	SUBSTRING(U_Email, 1, 255) AS emailAddress,
	SUBSTRING(U_Homepage, 1, 255) AS websiteTitle,
	SUBSTRING(U_Homepage, 1, 255) AS websiteUrl,
	SUBSTRING(U_Location, 1, 255) AS location,
	SUBSTRING(U_Signature, 1, 65534) AS signature,
	U_TimeOffset AS timeFormat,
	SUBSTRING(IFNULL(U_Picture, ''), 1, 255) AS avatar, '' AS lngfile,
	'' AS buddy_list, '' AS pm_ignore_list, '' AS messageLabels,
	'' AS personalText, '' AS ICQ, '' AS AIM, '' AS YIM, '' AS MSN,
	'' AS timeFormat, '' AS memberIP, '' AS secretQuestion, '' AS secretAnswer,
	'' AS validation_code, '' AS additionalGroups, '' AS smileySet,
	'' AS passwordSalt
FROM {$from_prefix}Users
WHERE U_Number != 0;
---*

/******************************************************************************/
--- Converting categories...
/******************************************************************************/

TRUNCATE {$to_prefix}categories;

---* {$to_prefix}categories
SELECT Cat_Number AS ID_CAT, Cat_Title AS name
FROM {$from_prefix}Category
WHERE Cat_Number != 0
GROUP BY Cat_Number;
---*

/******************************************************************************/
--- Converting boards...
/******************************************************************************/

TRUNCATE {$to_prefix}boards;

DELETE FROM {$to_prefix}board_permissions
WHERE ID_BOARD != 0;

---* {$to_prefix}boards
SELECT
	Bo_Number AS ID_BOARD, Bo_Cat AS ID_CAT, Bo_Title AS name,
	Bo_Description AS description, Bo_Threads AS numTopics, Bo_Total AS numPosts
FROM {$from_prefix}Boards;
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
	p.B_Number AS ID_TOPIC, p.B_Sticky AS isSticky, p.B_Number AS ID_FIRST_MSG,
	p.B_PosterId AS ID_MEMBER_STARTED, p.B_Replies AS numReplies,
	p.B_Counter AS numViews, IF(p.B_Status = 'C', 1, 0) AS locked,
	b.Bo_Number AS ID_BOARD, MAX(p2.B_Number) AS ID_LAST_MSG
FROM ({$from_prefix}Posts AS p, {$from_prefix}Boards AS b, {$from_prefix}Posts AS p2)
WHERE p.B_Topic = 1
	AND b.Bo_Keyword = p.B_Board
	AND p2.B_Main = p.B_Number
GROUP BY p.B_Number
HAVING ID_FIRST_MSG != 0
	AND ID_LAST_MSG != 0;
---*

---* {$to_prefix}topics (update ID_TOPIC)
SELECT t.ID_TOPIC, p.B_PosterId AS ID_MEMBER_UPDATED
FROM ({$to_prefix}topics AS t, {$from_prefix}Posts AS p)
WHERE p.B_Number = t.ID_LAST_MSG;
---*

/******************************************************************************/
--- Converting posts (this may take some time)...
/******************************************************************************/

TRUNCATE {$to_prefix}messages;
TRUNCATE {$to_prefix}attachments;

---* {$to_prefix}messages 200
SELECT
	p.B_Number AS ID_MSG, IF(p.B_Main = 0, p.B_Number, p.B_Main) AS ID_TOPIC,
	p.B_Posted AS posterTime, p.B_PosterId AS ID_MEMBER,
	SUBSTRING(p.B_Subject, 1, 255) AS subject,
	SUBSTRING(IFNULL(u.U_Username, 'Guest'), 1, 255) AS posterName,
	SUBSTRING(p.B_IP AS posterIP, 1, 255) AS posterIP,
	SUBSTRING(IFNULL(u.U_Email, ''), 1, 255) AS posterEmail,
	b.Bo_Number AS ID_BOARD,
	SUBSTRING(REPLACE(p.B_Body, '<br>', '<br />'), 1, 65534) AS body,
	'' AS modifiedName, 'xx' AS icon
FROM ({$from_prefix}Posts AS p, {$from_prefix}Boards AS b)
	LEFT JOIN {$from_prefix}Users AS u ON (u.U_Number = p.B_PosterId)
WHERE b.Bo_Keyword = p.B_Board;
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
	WHERE ID_TOPIC = $row[ID_TOPIC]
	LIMIT 1");
unset($row['ID_TOPIC']);
---}
SELECT
	pq.P_QuestionNum AS ID_POLL, SUBSTRING(pq.P_Question, 1, 255) AS question,
	IF(pq.P_ChoiceType = 'one', 1, 8) AS maxVotes, pm.P_Stop AS expireTime,
	pt.B_PosterId AS ID_MEMBER, pt.B_Number AS ID_TOPIC,
	SUBSTRING(IFNULL(u.U_Username, 'Guest'), 1, 255) AS posterName,
	pm.P_NoResults AS hideResults
FROM ({$from_prefix}Pollquestions AS pq, {$from_prefix}Pollmain AS pm, {$from_prefix}Posts AS pt)
	LEFT JOIN {$from_prefix}Users AS u ON (u.U_Number = pt.B_PosterId)
WHERE pm.P_Id = pq.P_PollId
	AND pt.B_Poll = pq.P_PollId
	AND pt.B_Main = 1
GROUP BY pq.P_PollId;
---*

/******************************************************************************/
--- Converting poll options...
/******************************************************************************/

---* {$to_prefix}poll_choices
SELECT
	po.P_QuestionNum AS ID_POLL, po.P_OptionNum AS ID_CHOICE,
	SUBSTRING(po.P_Option, 1, 255) AS label, COUNT(pv.P_QuestionNum) AS votes
FROM {$from_prefix}Polloptions AS po
	LEFT JOIN {$from_prefix}pollvotes AS pv ON (po.P_QuestionNum = pv.P_QuestionNum AND po.P_OptionNum = pv.P_OptionNum)
GROUP BY po.P_QuestionNum, po.P_OptionNum;
---*

/******************************************************************************/
--- Converting personal messages (step 1)...
/******************************************************************************/

TRUNCATE {$to_prefix}personal_messages;

---* {$to_prefix}personal_messages
SELECT
	m.M_Number AS ID_PM, m.M_Sender AS ID_MEMBER_FROM, m.M_Sent AS msgtime,
	SUBSTRING(IFNULL(u.U_Username, 'Guest'), 1, 255) AS fromName,
	SUBSTRING(m.M_Subject, 1, 255) AS subject,
	SUBSTRING(m.M_Message, 1, 65534) AS body
FROM {$from_prefix}Messages AS m
	LEFT JOIN {$from_prefix}Users AS u ON (u.U_Number = m.M_Sender);
---*

/******************************************************************************/
--- Converting personal messages (step 2)...
/******************************************************************************/

TRUNCATE {$to_prefix}pm_recipients;

---* {$to_prefix}pm_recipients
SELECT
	M_Number AS ID_PM, M_Uid AS ID_MEMBER, M_Status != 'N' AS is_read,
	'' AS labels
FROM {$from_prefix}Messages;
---*

/******************************************************************************/
--- Converting moderators...
/******************************************************************************/

TRUNCATE {$to_prefix}moderators;

---* {$to_prefix}moderators
SELECT mods.Mod_Uid AS ID_MEMBER, b.Bo_Number AS ID_BOARD
FROM ({$from_prefix}Moderators AS mods, {$from_prefix}Boards AS b)
WHERE b.Bo_Keyword = mods.Mod_Board;
---*

/******************************************************************************/
--- Converting topic notifications...
/******************************************************************************/

TRUNCATE {$to_prefix}log_notify;

---* {$to_prefix}log_notify
SELECT F_Thread AS ID_TOPIC, F_Owner AS ID_MEMBER
FROM {$from_prefix}Favorites;
---*

/******************************************************************************/
--- Converting board notifications...
/******************************************************************************/

---* {$to_prefix}log_notify
SELECT S_Board AS ID_BOARD, S_Uid AS ID_MEMBER
FROM {$from_prefix}Subscribe;
---*

/******************************************************************************/
--- Converting buddies...
/******************************************************************************/

---# Clear out everyones buddy list just incase...
UPDATE {$to_prefix}members
SET buddy_list = '';
---#

---# Get all the buddies...
---{
while (true)
{
	pastTime($substep);

	$result = convert_query("
		SELECT Add_Owner AS ID_MEMBER, Add_Member AS ID_BUDDY
		FROM {$from_prefix}Addressbook
		LIMIT $_REQUEST[start], 250");
	while ($row = mysql_fetch_assoc($result))
	{
		$row['ID_BUDDY'] = (int) $row['ID_BUDDY'];

		convert_query("
			UPDATE {$to_prefix}members
			SET buddy_list = IF(buddy_list = '', '$row[ID_BUDDY]', CONCAT(buddy_list, ',$row[ID_BUDDY]'))
			WHERE ID_MEMBER = $row[ID_MEMBER]
			LIMIT 1");
	}

	$_REQUEST['start'] += 250;
	if (mysql_num_rows($result) < 250)
		break;

	mysql_free_result($result);
}

$_REQUEST['start'] = 0;
---}
---#

/******************************************************************************/
--- Converting attachments...
/******************************************************************************/

---* {$to_prefix}attachments
---{
$no_add = true;
$keys = array('ID_ATTACH', 'size', 'filename', 'ID_MSG', 'downloads');

// Doesn't exist?
if (empty($GLOBALS['config']['files']) || !file_exists($GLOBALS['config']['files']) || !file_exists($GLOBALS['config']['files'] . '/' . $row['filename']))
	continue;

// Try to get a better filename!
$oldFilename = $row['filename'];
$row['filename'] = strpos($oldFilename, '-') !== false ? substr($oldFilename, strpos($oldFilename, '-') + 1) : $oldFilename;
$row['size'] = filesize($GLOBALS['config']['files'] . '/' . $row['filename']);

$newfilename = getAttachmentFilename($row['filename'], $ID_ATTACH);
if (strlen($newfilename) < = 255 && copy($GLOBALS['config']['files'] . '/' . $oldFilename, $attachmentUploadDir . '/' . $newfilename))
{
	$rows[] = "$ID_ATTACH, $row[size], '" . addslashes($row['filename']) . "', $row[ID_MSG], $row[downloads]";

	$ID_ATTACH++;
}
---}
SELECT B_File AS filename, B_Number AS ID_MSG, B_FileCounter AS downloads
FROM {$from_prefix}Posts
WHERE B_FILE != 'NULL';
---*