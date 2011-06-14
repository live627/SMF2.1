/* ATTENTION: You don't need to run or use this file!  The convert.php script does everything for you! */

/******************************************************************************/
---~ name: "Vanilla2"
/******************************************************************************/
---~ version: "SMF 2.0"
---~ defines: APPLICATION, PATH_CACHE
---~ settings: "/conf/config-defaults.php", "/conf/config.php"
---~ globals: Configuration
---~ from_prefix: "`{$Configuration['Database']['Name']}`.{$Configuration['Database']['DatabasePrefix']}"
---~ table_test: "{$from_prefix}User"

/******************************************************************************/
--- Converting members...
/******************************************************************************/

TRUNCATE {$to_prefix}members;

---* {$to_prefix}members
---{
$row['date_registered'] = strtotime($row['date_registered']);
$row['last_login'] = strtotime($row['last_login']);
$row['real_name'] = trim($row['real_name']) == '' ? $row['member_name'] : $row['real_name'];
---}
SELECT
	m.UserID AS id_member, m.Name as member_name, m.DateFirstVisit AS date_registered,
	(IFNULL(m.CountDiscussions, 0) + IFNULL(m.CountComments, 0)) AS posts, m.DateLastActive AS last_login,
	m.admin AS id_group, m.Password AS passwd, m.Name AS real_name, m.Email AS email_address,
	CASE m.ShowEmail WHEN 1 THEN 0 ELSE 1 END as hide_email, IFNULL(m.Photo, "") AS avatar,
	'' AS member_ip, '' AS member_ip2, '' AS password_salt,
	'' AS lngfile, '' AS buddy_list, '' AS pm_ignore_list, '' AS message_labels,
	'' AS personal_text, '' AS time_format, '' AS usertitle, '' AS secret_question,
	'' AS secret_answer, '' AS validation_code, '' AS additional_groups, '' AS smiley_set
FROM {$from_prefix}User AS m;
---*

/******************************************************************************/
--- Converting boards...
/******************************************************************************/

TRUNCATE {$to_prefix}boards;

---* {$to_prefix}boards
SELECT
	c.CategoryID AS id_board, 1 AS id_cat, 0 AS id_parent, 0 AS num_posts,
	0 AS num_topics, '-1,0' AS member_groups, SUBSTRING(c.Name, 1, 255) AS name,
	SUBSTRING(c.Description, 1, 65534) AS description, c.Sort AS board_order
FROM {$from_prefix}Category AS c;
---*

/******************************************************************************/
--- Converting topics...
/******************************************************************************/

TRUNCATE {$to_prefix}topics;
TRUNCATE {$to_prefix}log_topics;
TRUNCATE {$to_prefix}log_boards;
TRUNCATE {$to_prefix}log_mark_read;
TRUNCATE {$to_prefix}messages;

---* {$to_prefix}topics
---{
$no_add = true;

	$row['poster_time'] = strtotime($row['poster_time']);
	$row['modified_time'] = is_null($row['modified_time']) ? 0 : strtotime($row['modified_time']);
	$row['modified_name'] = is_null($row['modified_name']) ? '' : $row['modified_name'];

	convert_insert('messages',
		array(
			'id_topic', 'id_board', 'poster_time', 'id_member', 'subject', 'poster_name', 'poster_email', 'poster_ip', 'modified_time', 'modified_name', 'body'),
		array(
			$row['id_topic'], $row['id_board'], $row['poster_time'], $row['id_member_started'], $row['subject'], $row['poster_name'], $row['poster_email'], "", $row['modified_time'], $row['modified_name'], $row['body'])
	);

	$rows[] = array(
		'id_topic' => $row['id_topic'],
		'is_sticky' => $row['is_sticky'],
		'id_board' => $row['id_board'],
		'id_member_started' => $row['id_member_started'],
		'id_first_msg' => $row['modified_time'],
		'id_last_msg' => $row['modified_time'],
		'id_member_updated' => $row['id_member_started'],
		'locked' => $row['locked'],
	);
---}
SELECT
	t.DiscussionID AS id_topic, t.Announce AS is_sticky, t.CategoryID AS id_board, t.InsertUserID AS id_member_started, t.LastCommentUserID AS id_member_updated, t.body, t.CountComments AS num_replies, t.Closed AS locked, fm.name AS poster_name, fm.Email AS poster_email, t.DateInserted AS poster_time, t.name AS subject, fm.name AS modified_name, t.DateUpdated AS modified_time
FROM {$from_prefix}Discussion AS t
	LEFT JOIN {$from_prefix}User AS fm ON (t.InsertUserID = fm.UserID);
---*

/******************************************************************************/
--- Converting posts...
/******************************************************************************/

---* {$to_prefix}messages 200
---{
$row['poster_time'] = strtotime($row['poster_time']);
$row['modified_time'] = is_null($row['modified_time']) ? 0 : strtotime($row['modified_time']);
$row['modified_name'] = is_null($row['modified_name']) ? '' : $row['modified_name'];
---}
SELECT
	p.DiscussionID AS id_topic, t.CategoryID AS id_board,
	p.DateInserted AS poster_time, p.InsertUserID AS id_member, t.Name AS subject,
	m.Name AS poster_name, m.Email AS poster_email, '' AS poster_ip,
	p.DateUpdated AS modified_time, m2.Name AS modified_name, p.Body AS body
FROM {$from_prefix}Comment AS p
	LEFT JOIN {$from_prefix}Discussion AS t ON (t.DiscussionID = p.DiscussionID)
	LEFT JOIN {$from_prefix}User AS m ON (m.UserID = p.InsertUserID)
	LEFT JOIN {$from_prefix}User AS m2 ON (m2.UserID = p.UpdateUserID)
WHERE p.CommentID > 0;
---*

/******************************************************************************/
--- Converting personal messages (step 1)...
/******************************************************************************/

TRUNCATE {$to_prefix}personal_messages;

---* {$to_prefix}personal_messages
---{
$row['msgtime'] = strtotime($row['msgtime']);
---}
SELECT
	cm.MessageID AS id_pm, cm.InsertUserID AS id_member_from, cm.DateInserted AS msgtime,
	IFNULL(uc.Deleted, 0) AS deleted_by_sender, c.FirstMessageID AS id_pm_head,
	SUBSTRING(IFNULL(u.Name, "Guest"), 1, 255) AS from_name, "(No Subject)" AS subject,
	SUBSTRING(cm.body, 1, 65534) AS body
FROM {$from_prefix}Conversation AS c
	LEFT JOIN {$from_prefix}ConversationMessage AS cm ON (c.ConversationID = cm.ConversationID)
	LEFT JOIN {$from_prefix}User AS u ON (c.InsertUserID = u.UserID)
	LEFT JOIN {$from_prefix}UserConversation AS uc ON (c.InsertUserID = uc.UserID AND c.ConversationID = uc.ConversationID)
GROUP BY cm.MessageID;
---*

/******************************************************************************/
--- Converting personal messages (step 2)...
/******************************************************************************/

TRUNCATE {$to_prefix}pm_recipients;

---* {$to_prefix}pm_recipients
---{
$no_add = true;
$keys = array('id_pm', 'id_member', 'labels', 'is_read');
$invited_members = @unserialize($row['Contributors']);

if (is_array($invited_members) && !empty($invited_members))
{
	foreach ($invited_members as $invited => $id)
	{
		if (!empty($invited) && $row['msg_author_id'] != $id)
		{
			// Check if this should be marked as deleted or as is_read
			$result = convert_query("
				SELECT IFNULL(DateLastViewed, 0) AS is_read, deleted
				FROM {$from_prefix}UserConversation
				WHERE UserID = {$id} AND ConversationID = {$row['ConversationID']}");
			list ($is_read, $is_deleted) = convert_fetch_row($result);
			convert_free_result($result);

			$rows[] = array(
				'id_pm' => $row['id_pm'],
				'id_member' => $id,
				'labels' => $row['labels'],
				'is_read' => is_numeric($is_read) ? $is_read : strtotime($row['DateInserted']) < strtotime($is_read) ? 1 : 0,
				'deleted' => $is_deleted,
			);
		}
	}
}
---}
SELECT cm.MessageID AS id_pm, '-1' AS labels, c.ConversationID, cm.DateInserted, c.Contributors, cm.InsertUserID AS msg_author_id
FROM {$from_prefix}ConversationMessage AS cm
	LEFT JOIN {$from_prefix}Conversation AS c ON (c.ConversationID = cm.ConversationID)
GROUP BY cm.MessageID;
---*