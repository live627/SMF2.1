/* ATTENTION: You don't need to run or use this file!  The convert.php script does everything for you! */

/******************************************************************************/
---~ name: "Invision Power Board 2"
/******************************************************************************/
---~ version: "SMF 2.0 Alpha"
---~ settings: "/conf_global.php"
---~ globals: INFO
---~ from_prefix: "`$INFO[sql_database]`.$INFO[sql_tbl_prefix]"
---~ table_test: "{$from_prefix}members"

/******************************************************************************/
--- Converting members...
/******************************************************************************/

TRUNCATE {$to_prefix}members;
TRUNCATE {$to_prefix}attachments;

---* {$to_prefix}members
---{
$row['signature'] = addslashes(preg_replace(
	array(
		'~<!--QuoteBegin.*?-->.+?<!--QuoteEBegin-->~is',
		'~<!--QuoteEnd-->.+?<!--QuoteEEnd-->~is',
		'~<!--c1-->.+?<!--ec1-->~is',
		'~<!--c2-->.+?<!--ec2-->~is',
		'~<a href=\'mailto:(.+?)\'>.+?</a>~is',
		'~<a href=\'(.+?)\' target=\'_blank\'>(.+?)</a>~is',
		'~<span style=\'color:([^;]+?)\'>(.+?)</span>~is',
		'~<span style=\'font-size:([^;]+?).+?\'>(.+?)</span>~is',
		'~<span style=\'font-family:([^;]+?)\'>(.+?)</span>~is',
		'~<([/]?)ul>~is',
		'~<img src=\'~i',
		'~\' border=\'0\' alt=\'user posted image\'( /)?' . '>~i',
		'~<!--emo&(.+?)-->.+?<!--endemo-->~i',
	),
	array(
		'[quote]',
		'[/quote]',
		'[code]',
		'[/code]',
		'[email]$1[/email]',
		'[url=$1]$2[/url]',
		'[color=$1]$2[/color]',
		'[size=$1]$2[/size]',
		'[font=$1]$2[/font]',
		'[$1list]',
		'[img]',
		'[/img]',
		'$1',
	), ltrim($row['signature'])));
$row['signature'] = substr(strtr(strtr($row['signature'], '<>', '[]'), array('[br /]' => '<br />')), 0, 65534);
---}
SELECT
	m.id AS ID_MEMBER, SUBSTRING(m.name, 1, 80) AS memberName,
	m.joined AS dateRegistered,
	IF(m.mgroup = 4, 1, IF(m.mgroup > 5, m.mgroup + 3, 0)) AS ID_GROUP,
	posts, m.last_visit AS lastLogin, SUBSTRING(m.name, 1, 255) AS realName,
	SUBSTRING(me.yahoo, 1, 32) AS YIM, m.msg_total AS instantMessages,
	SUBSTRING(mc.converge_pass_hash, 1, 64) AS passwd,
	SUBSTRING(mc.converge_pass_salt, 1, 5) AS passwordSalt,
	SUBSTRING(m.email, 1, 255) AS emailAddress,
	IF (m.bday_year = 0 AND m.bday_month != 0 AND m.bday_day != 0, CONCAT('0004-', m.bday_month, '-', m.bday_day), CONCAT_WS('-', IF(m.bday_year <= 4, 1, m.bday_year), IF(m.bday_month = 0, 1, m.bday_month), IF(m.bday_day = 0, 1, m.bday_day))) AS birthdate,
	SUBSTRING(me.website, 1, 255) AS websiteTitle,
	SUBSTRING(me.website, 1, 255) AS websiteUrl, me.signature,
	SUBSTRING(me.location, 1, 255) AS location,
	SUBSTRING(me.icq_number, 1, 255) AS ICQ,
	SUBSTRING(me.msnname, 1, 255) AS MSN, SUBSTRING(me.aim_name, 1, 16) AS AIM,
	m.hide_email AS hideEmail, m.email_pm AS pm_email_notify,
	SUBSTRING(IF(me.avatar_location = 'noavatar', '', me.avatar_location), 1, 255) AS avatar,
	'' AS lngfile, '' AS buddy_list, '' AS pm_ignore_list, '' AS messageLabels,
	'' AS personalText, '' AS timeFormat, '' AS usertitle, '' AS memberIP,
	'' AS secretQuestion, '' AS secretAnswer, '' AS validation_code,
	'' AS additionalGroups, '' AS smileySet, '' AS passwordSalt
FROM {$from_prefix}members AS m
	LEFT JOIN {$from_prefix}member_extra AS me ON (m.id = me.id)
	LEFT JOIN {$from_prefix}members_converge AS mc ON (m.id = mc.converge_id)
WHERE m.id != 0;
---*

/******************************************************************************/
--- Converting categories...
/******************************************************************************/

TRUNCATE {$to_prefix}categories;

---* {$to_prefix}categories
SELECT id AS ID_CAT, SUBSTRING(name, 1, 255) AS name, position AS catOrder
FROM {$from_prefix}forums
WHERE parent_id = -1;
---*

/******************************************************************************/
--- Converting boards...
/******************************************************************************/

TRUNCATE {$to_prefix}boards;

DELETE FROM {$to_prefix}board_permissions
WHERE ID_BOARD != 0;

/* The converter will set ID_CAT for us based on ID_PARENT being wrong. */
---* {$to_prefix}boards
SELECT
	id AS ID_BOARD, topics AS numTopics, posts AS numPosts,
	SUBSTRING(name, 1, 255) AS name,
	SUBSTRING(description, 1, 65534) AS description, position AS boardOrder,
	parent_id AS ID_PARENT, '-1,0' AS memberGroups
FROM {$from_prefix}forums
WHERE parent_id != -1;
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
	t.tid AS ID_TOPIC, t.pinned AS isSticky, t.forum_id AS ID_BOARD,
	t.starter_id AS ID_MEMBER_STARTED, t.last_poster_id AS ID_MEMBER_UPDATED,
	pl.pid AS ID_POLL, t.posts AS numReplies, t.views AS numViews,
	MIN(p.pid) AS ID_FIRST_MSG, MAX(p.pid) AS ID_LAST_MSG,
	t.state = 'closed' AS locked
FROM ({$from_prefix}topics AS t, {$from_prefix}posts AS p)
	LEFT JOIN {$from_prefix}polls AS pl ON (pl.tid = t.tid)
WHERE p.topic_id = t.tid
GROUP BY t.tid
HAVING ID_FIRST_MSG != 0
	AND ID_LAST_MSG != 0;
---*

/******************************************************************************/
--- Converting posts (this may take some time)...
/******************************************************************************/

TRUNCATE {$to_prefix}messages;

---* {$to_prefix}messages 200
---{
$row['body'] = addslashes(preg_replace(
	array(
		'~<!--QuoteBegin.*?-->.+?<!--QuoteEBegin-->~is',
		'~<!--QuoteEnd-->.+?<!--QuoteEEnd-->~is',
		'~<!--c1-->.+?<!--ec1-->~is',
		'~<!--c2-->.+?<!--ec2-->~is',
		'~<a href=\'mailto:(.+?)\'>.+?</a>~is',
		'~<a href=\'(.+?)\' target=\'_blank\'>(.+?)</a>~is',
		'~<span style=\'color:([^;]+?)\'>(.+?)</span>~is',
		'~<span style=\'font-size:([^;]+?).+?\'>(.+?)</span>~is',
		'~<span style=\'font-family:([^;]+?)\'>(.+?)</span>~is',
		'~<([/]?)ul>~is',
		'~<img src=\'~i',
		'~\' border=\'0\' alt=\'user posted image\'( /)?' . '>~i',
		'~<!--emo&(.+?)-->.+?<!--endemo-->~i',
	),
	array(
		'[quote]',
		'[/quote]',
		'[code]',
		'[/code]',
		'[email]$1[/email]',
		'[url=$1]$2[/url]',
		'[color=$1]$2[/color]',
		'[size=$1]$2[/size]',
		'[font=$1]$2[/font]',
		'[$1list]',
		'[img]',
		'[/img]',
		'$1',
	), ltrim($row['body'])));
$row['body'] = substr(strtr(strtr($row['body'], '<>', '[]'), array('[br /]' => '<br />')), 0, 65534);
---}
SELECT
	p.pid AS ID_MSG, p.topic_id AS ID_TOPIC, p.post_date AS posterTime,
	p.author_id AS ID_MEMBER, SUBSTRING(t.title, 1, 255) AS subject,
	SUBSTRING(p.author_name, 1, 255) AS posterName,
	SUBSTRING(p.ip_address, 1, 255) AS posterIP, p.use_emo AS smileysEnabled,
	p.edit_time AS modifiedTime, SUBSTRING(p.edit_name, 1, 255) AS modifiedName,
	t.forum_id AS ID_BOARD, REPLACE(p.post, '<br>', '<br />') AS body,
	SUBSTRING(IFNULL(m.email, 'guest@example.com'), 1, 255) AS posterEmail, 'xx' AS icon
FROM {$from_prefix}posts AS p
	LEFT JOIN {$from_prefix}topics AS t ON (t.tid = p.topic_id)
	LEFT JOIN {$from_prefix}members AS m ON (m.id = p.author_id);
---*

/******************************************************************************/
--- Converting polls...
/******************************************************************************/

TRUNCATE {$to_prefix}polls;
TRUNCATE {$to_prefix}poll_choices;
TRUNCATE {$to_prefix}log_polls;

---* {$to_prefix}polls
SELECT
	p.pid AS ID_POLL, SUBSTRING(p.poll_question, 1, 255) AS question,
	p.starter_id AS ID_MEMBER,
	SUBSTRING(IFNULL(m.name, 'Guest'), 1, 255) AS posterName
FROM {$from_prefix}polls AS p
	LEFT JOIN {$from_prefix}members AS m ON (m.id = p.starter_id);
---*

/******************************************************************************/
--- Converting poll options...
/******************************************************************************/

---* {$to_prefix}poll_choices
---{
$no_add = true;
$keys = array('ID_POLL', 'ID_CHOICE', 'label', 'votes');

$choices = @unserialize(stripslashes($row['choices']));

if (is_array($choices))
	foreach ($choices as $choice)
	{
		$choice = addslashes_recursive($choice);
		$rows[] = "$row[ID_POLL], SUBSTRING('" . implode("', 1, 255), '", $choice) . "'";
	}
---}
SELECT pid AS ID_POLL, choices
FROM {$from_prefix}polls;
---*

/******************************************************************************/
--- Converting poll logs...
/******************************************************************************/

---* {$to_prefix}log_polls
SELECT pl.pid AS ID_POLL, v.member_id AS ID_MEMBER
FROM {$from_prefix}voters AS v
	LEFT JOIN {$from_prefix}polls AS pl ON (pl.tid = v.tid);
---*

/******************************************************************************/
--- Converting personal messages (step 1)...
/******************************************************************************/

TRUNCATE {$to_prefix}personal_messages;

---* {$to_prefix}personal_messages
---{
$row['body'] = addslashes(preg_replace(
	array(
		'~<!--QuoteBegin.*?-->.+?<!--QuoteEBegin-->~is',
		'~<!--QuoteEnd-->.+?<!--QuoteEEnd-->~is',
		'~<!--c1-->.+?<!--ec1-->~is',
		'~<!--c2-->.+?<!--ec2-->~is',
		'~<a href=\'mailto:(.+?)\'>.+?</a>~is',
		'~<a href=\'(.+?)\' target=\'_blank\'>(.+?)</a>~is',
		'~<span style=\'color:([^;]+?)\'>(.+?)</span>~is',
		'~<span style=\'font-size:([^;]+?).+?\'>(.+?)</span>~is',
		'~<span style=\'font-family:([^;]+?)\'>(.+?)</span>~is',
		'~<([/]?)ul>~is',
		'~<img src=\'~i',
		'~\' border=\'0\' alt=\'user posted image\'( /)?' . '>~i',
		'~<!--emo&(.+?)-->.+?<!--endemo-->~i',
	),
	array(
		'[quote]',
		'[/quote]',
		'[code]',
		'[/code]',
		'[email]$1[/email]',
		'[url=$1]$2[/url]',
		'[color=$1]$2[/color]',
		'[size=$1]$2[/size]',
		'[font=$1]$2[/font]',
		'[$1list]',
		'[img]',
		'[/img]',
		'$1',
	), ltrim($row['body'])));
$row['body'] = strtr(strtr($row['body'], '<>', '[]'), array('[br /]' => '<br />'));
---}
SELECT
	mt.msg_id AS ID_PM, mt.msg_author_id AS ID_MEMBER_FROM,
	IF(m.mt_to_id = m.mt_from_id, 0, 1) AS deletedBySender,
	mt.msg_date AS msgtime, SUBSTRING(uf.name, 1, 255) AS fromName,
	SUBSTRING(m.mt_title, 1, 255) AS subject,
	SUBSTRING(mt.msg_post, 1, 65534) AS body
FROM ({$from_prefix}message_text AS mt, {$from_prefix}message_topics AS m)
	LEFT JOIN {$from_prefix}members AS uf ON (uf.id = m.mt_from_id)
WHERE m.mt_msg_id = mt.msg_id
GROUP BY mt.msg_id;
---*

/******************************************************************************/
--- Converting personal messages (step 2)...
/******************************************************************************/

TRUNCATE {$to_prefix}pm_recipients;

---* {$to_prefix}pm_recipients
SELECT
	mt_msg_id AS ID_PM, mt_to_id AS ID_MEMBER, MIN(mt_hide_cc) AS bcc,
	IF(MAX(mt_user_read) > 0, 1, 0) AS is_read, '' AS labels
FROM {$from_prefix}message_topics
WHERE mt_vid_folder != 'sent' OR mt_from_id != mt_to_id
GROUP BY mt_msg_id, mt_to_id;
---*

/******************************************************************************/
--- Converting topic notifications...
/******************************************************************************/

TRUNCATE {$to_prefix}log_notify;

---* {$to_prefix}log_notify
SELECT member_id AS ID_MEMBER, topic_id AS ID_TOPIC
FROM {$from_prefix}tracker;
---*

/******************************************************************************/
--- Converting board notifications...
/******************************************************************************/

---* {$to_prefix}log_notify
SELECT member_id AS ID_MEMBER, forum_id AS ID_BOARD
FROM {$from_prefix}forum_tracker;
---*

/******************************************************************************/
--- Converting moderators...
/******************************************************************************/

TRUNCATE {$to_prefix}moderators;

---* {$to_prefix}moderators
SELECT member_id AS ID_MEMBER, forum_id AS ID_BOARD
FROM {$from_prefix}moderators
WHERE member_id != -1;
---*

/******************************************************************************/
--- Converting yearly events...
/******************************************************************************/

DELETE FROM {$to_prefix}calendar_holidays
WHERE ID_HOLIDAY > 95;

---* {$to_prefix}calendar_holidays
SELECT
	SUBSTRING(title, 1, 30) AS title,
	CONCAT(year, '-', month, '-', mday) AS eventDate
FROM {$from_prefix}calendar_events
WHERE event_repeat = 1
	AND repeat_unit = 'y';
---*

/******************************************************************************/
--- Converting permissions...
/******************************************************************************/

DELETE FROM {$to_prefix}permissions
WHERE ID_GROUP > 8;

DELETE FROM {$to_prefix}membergroups
WHERE ID_GROUP > 8;

---# Transforming permissions...
---{
/* These didn't make it to perms:
g_avoid_q, g_avoid_flood, g_other_topics, g_delete_own_topic, g_invite_friend,
g_icon, g_attach_max, g_avatar_upload, g_email_limit, g_append_edit, g_access_offline,
g_max_mass_pm, g_search_flood, g_edit_cutoff, g_promotion, g_hide_from_list,
*/

// These SMF perms have no equivalent but should be set.
$manual_perms = array(
	'profile_view_own',
	'profile_view_any',
	'karma_edit',
	'calendar_view',
	'mark_any_notify',
	'mark_notify',
	'view_attachments',
	'report_any',
);

while (true)
{
	pastTime($substep);

	$result = convert_query("
		SELECT
			g_id AS ID_GROUP, g_title AS groupName, g_max_messages AS maxMessages,
			g_view_board AS view_stats, g_mem_info AS view_mlist,
			g_view_board AS who_view, g_use_search AS search_posts, g_email_friend AS send_topic,
			g_edit_profile AS profile_identity_own, g_post_new_topics AS post_new,
			g_reply_own_topics AS post_reply_own, g_reply_other_topics AS post_reply_any,
			g_edit_posts AS modify_own, g_delete_own_posts AS delete_own,
			g_post_polls AS poll_post, g_post_polls AS poll_add_own, g_vote_polls AS poll_vote,
			g_use_pm AS pm_read, g_use_pm AS pm_send, g_is_supmod AS moderate_forum,
			g_is_supmod AS manage_membergroups, g_is_supmod AS manage_bans,
			g_access_cp AS manage_smileys, g_access_cp AS manage_attachments,
			g_can_remove AS delete_any, g_calendar_post AS calendar_post,
			g_calendar_post AS calendar_edit_any, g_post_closed AS lock_own,
			g_edit_topic AS modify_any, g_open_close_posts AS lock_any
		FROM {$from_prefix}groups
		WHERE g_id NOT IN (1, 4, 5)
		LIMIT $_REQUEST[start], 100");
	$perms = array();
	while ($row = mysql_fetch_assoc($result))
	{
		$row = addslashes_recursive($row);
		// If this is NOT an existing membergroup add it (1-5 = existing.)
		if ($row['ID_GROUP'] > 5)
		{
			convert_query("
				INSERT INTO {$to_prefix}membergroups
					(ID_GROUP, groupName, maxMessages, onlineColor, stars)
				VALUES
					($row[ID_GROUP] + 3, SUBSTRING('$row[groupName]', 1, 255), $row[maxMessages], '', '')");
			$groupID = $row['ID_GROUP'] + 3;
		}
		else
		{
			if ($row['ID_GROUP'] == 2)
				$groupID = -1;
			elseif ($row['ID_GROUP'] == 3)
				$groupID = 0;
			else
				$groupID = $row['ID_GROUP'];
		}

		unset($row['ID_GROUP']);
		unset($row['groupName']);
		unset($row['maxMessages']);

		foreach ($row as $key => $value)
			if ($value == 1)
				$perms[] = $groupID . ', \'' . $key . '\'';
		foreach ($manual_perms as $key)
			if ($value == 1 && $groupID != -1)
				$perms[] = $groupID . ', \'' . $key . '\'';
	}

	if (!empty($perms))
		convert_query("
			REPLACE INTO {$to_prefix}permissions
				(ID_GROUP, permission)
			VALUES (" . implode('),
				(', $perms) . ")");

	$_REQUEST['start'] += 100;
	if (mysql_num_rows($result) < 100)
		break;

	mysql_free_result($result);
}

$_REQUEST['start'] = 0;
---}
---#

/******************************************************************************/
--- Converting board permissions...
/******************************************************************************/

---# Transforming board permissions...
---{
// This is SMF equivalent permissions.
$perm_equiv = array(
	'start_perms' => array(
		'post_new' => 1,
		'poll_post' => 1,
		'poll_add_own' => 1,
		'modify_own' => 1,
		'delete_own' => 1,
	),
	'read_perms' => array(
		'mark_notify' => 1,
		'mark_any_notify' => 1,
		'poll_view' => 1,
		'poll_vote' => 1,
		'report_any' => 1,
		'send_topic' => 1,
		'view_attachments' => 1,
	),
	'reply_perms' => array(
		'post_reply_own' => 1,
		'post_reply_any' => 1,
	),
	'upload_perms' => array(
		'post_attachments' => 1,
	),
);

global $groupMask;

// We need to load the member groups that we care about at all.
$result = convert_query("
	SELECT g_id AS ID_GROUP, g_perm_id AS perms
	FROM {$from_prefix}groups
	WHERE g_id != 5 AND g_id != 1 AND g_id != 4");
$groups = array();
$groupMask = array();
while ($row = mysql_fetch_assoc($result))
{
	$groups[] = $row['ID_GROUP'];
	$groupMask[$row['ID_GROUP']] = $row['perms'];
}
mysql_free_result($result);

function magicMask(&$group)
{
	/*
	Right... don't laugh... here we explode the string to an array. Then we replace each group
	with the groups that use this mask. Then we remove duplicates and then we implode it again
	*/

	global $groupMask;

	if ($group != '*')
	{
		$groupArray = explode(',', $group);

		$newGroups = array();
		foreach ($groupMask as $id => $perms)
		{
			$perm = explode(',', $perms);
			foreach ($perm as $realPerm)
				if (in_array($realPerm, $groupArray) && !in_array($id, $newGroups))
					$newGroups[] = $id;
		}

		$group = implode(',', $newGroups);
	}
}

function smfGroup(&$group)
{
	foreach ($group as $key => $value)
	{
		// Admin doesn't need to have his permissions done.
		if ($value == 4)
			unset($group[$key]);
		elseif ($value == 2)
			$group[$key] = -1;
		elseif ($value == 3)
			$group[$key] = 0;
		elseif ($value > 5)
			$group[$key] = $value + 3;
		else
			unset($group[$key]);
	}
}

while (true)
{
	pastTime($substep);

	$result = convert_query("
		SELECT id AS ID_BOARD, permission_array
		FROM {$from_prefix}forums
		LIMIT $_REQUEST[start], 100");
	$perms = array();
	while ($row = mysql_fetch_assoc($result))
	{
		$row += unserialize($row['permission_array']);
		$row = addslashes_recursive($row);

		// Oh yea... this is the "mask -> group" conversion stuffs...
		magicMask($row['start_perms']);
		magicMask($row['reply_perms']);
		magicMask($row['read_perms']);
		magicMask($row['upload_perms']);

		// This is used for updating the groups allowed on this board.
		$affectedGroups = array();

		// This is not at all fun... or the MOST efficient code but it should work.
		// first the patented "if everything is open do didley squat" routine.
		if ($row['start_perms'] == $row['reply_perms'] && $row['start_perms'] == $row['read_perms'] && $row['start_perms'] == $row['upload_perms'])
		{
			if ($row['read_perms'] != '*')
			{
				$affectedGroups = explode(',', $row['read_perms']);
				smfGroup($affectedGroups);

				// Update the board with allowed groups - appears twice in case board is hidden... makes sense to me :)
				convert_query("
					UPDATE {$to_prefix}boards
					SET memberGroups = '" . implode(', ', $affectedGroups) . "'
					WHERE ID_BOARD = $row[ID_BOARD]");
			}
		}
		else
		{
			$tempGroup = array();
			/* The complicated stuff :)
			First we work out which groups can access the board (ie ones who can READ it) - and set the board
			permission for this. Then for every group we work out what permissions they have and add them to the array */
			if ($row['read_perms'] != '*')
			{
				$affectedGroups = explode(',', $row['read_perms']);
				smfGroup($affectedGroups);
				// Update the board with allowed groups - appears twice in case board is hidden... makes sense to me :)
				convert_query("
					UPDATE {$to_prefix}boards
					SET memberGroups = '" . implode(', ', $affectedGroups) . "'
					WHERE ID_BOARD = $row[ID_BOARD]");
			}
			else
			{
				$affectedGroups[] = -1;
				$affectedGroups[] = 0;
			}
			// Now we know WHO can access this board, lets work out what they can do!
			// Everyone who is in affectedGroups can read so...
			foreach ($affectedGroups as $group)
				$tempGroup[$group] = $perm_equiv['read_perms'];
			if ($row['start_perms'] == '*')
				$affectedGroups2 = $affectedGroups;
			else
			{
				$affectedGroups2 = explode(',', $row['start_perms']);
				smfGroup($affectedGroups2);
			}
			foreach ($affectedGroups2 as $group)
				$tempGroup[$group] = isset($tempGroup[$group]) ? array_merge($perm_equiv['start_perms'], $tempGroup[$group]) : $perm_equiv['start_perms'];
			if ($row['reply_perms'] == '*')
				$affectedGroups2 = $affectedGroups;
			else
			{
				$affectedGroups2 = explode(',', $row['reply_perms']);
				smfGroup($affectedGroups2);
			}
			foreach ($affectedGroups2 as $group)
				$tempGroup[$group] = isset($tempGroup[$group]) ? array_merge($perm_equiv['reply_perms'], $tempGroup[$group]) : $perm_equiv['reply_perms'];
			if ($row['upload_perms'] == '*')
				$affectedGroups2 = $affectedGroups;
			else
			{
				$affectedGroups2 = explode(',', $row['upload_perms']);
				smfGroup($affectedGroups2);
			}
			foreach ($affectedGroups2 as $group)
				$tempGroup[$group] = isset($tempGroup[$group]) ? array_merge($perm_equiv['upload_perms'], $tempGroup[$group]) : $perm_equiv['upload_perms'];

			// Now we have $tempGroup filled with all the permissions for each group - better do something with it!
			foreach ($tempGroup as $groupno => $group)
				foreach ($group as $permission => $dummy)
					$perms[] = '(' . $row['ID_BOARD'] . ', ' . $groupno . ', \'' . $permission . '\')';
		}
	}

	if (!empty($perms))
		convert_query("
			REPLACE INTO {$to_prefix}board_permissions
				(ID_BOARD, ID_GROUP, permission)
			VALUES " . implode(',
				', $perms));

	$_REQUEST['start'] += 100;
	if (mysql_num_rows($result) < 100)
		break;

	mysql_free_result($result);
}

$_REQUEST['start'] = 0;
---}
---#

/******************************************************************************/
--- Converting smileys...
/******************************************************************************/

UPDATE {$to_prefix}smileys
SET hidden = 1;

---{
$specificSmileys = array(
	':mellow:' => 'cool',
	':huh:' => 'huh',
	'^_^' => 'cheesy',
	':o' => 'shocked',
	';)' => 'wink',
	':P' => 'tongue',
	':D' => 'grin',
	':lol:' => 'cheesy',
	'B)' => 'cool',
	':rolleyes:' => 'rolleyes',
	'-_-' => 'smiley',
	'&lt;_&lt;' => 'smiley',
	':)' => 'smiley',
	':wub:' => 'kiss',
	':angry:' => 'angry',
	':(' => 'sad',
	':unsure:' => 'huh',
	':wacko:' => 'evil',
	':blink:' => 'smiley',
	':ph34r:' => 'afro',
);

$request = convert_query("
	SELECT MAX(smileyOrder)
	FROM {$to_prefix}smileys");
list ($count) = mysql_fetch_row($request);
mysql_free_result($request);

$request = convert_query("
	SELECT code
	FROM {$to_prefix}smileys");
$currentCodes = array();
while ($row = mysql_fetch_assoc($request))
	$currentCodes[] = $row['code'];
mysql_free_result($request);

$rows = array();
foreach ($specificSmileys as $code => $name)
{
	if (in_array($code, $currentCodes))
		continue;

	$count++;
	$rows[] = "'$code', '{$name}.gif', '$name', $count";
}

if (!empty($rows))
	convert_query("
		REPLACE INTO {$to_prefix}smileys
			(code, filename, description, smileyOrder)
		VALUES (" . implode("),
			(", $rows) . ")");
---}

ALTER TABLE {$to_prefix}smileys
ORDER BY LENGTH(code) DESC;

/******************************************************************************/
--- Converting settings...
/******************************************************************************/

---# Moving settings...
---{
$result = convert_query("
	SELECT
		conf_key AS config_name,
		IF(conf_value = '', conf_default, conf_value) AS config_value
	FROM {$from_prefix}conf_settings");
while ($row = mysql_fetch_assoc($result))
{
	$found = true;
	switch ($row['config_name'])
	{
	case 'board_name':
		$inv_forum_name = $row['config_value'];
		continue;

	case 'offline_msg':
		$inv_maintenance_message = $row['config_value'];
		continue;

	case 'hot_topic':
		$row['config_name'] = 'hotTopicPosts';
		break;

	case 'display_max_posts':
		$row['config_name'] = 'defaultMaxMessages';
		break;

	case 'display_max_topics':
		$row['config_name'] = 'defaultMaxTopics';
		break;

	case 'flood_control':
		$row['config_name'] = 'spamWaitTime';
		break;

	case 'allow_online_list':
		$row['config_name'] = 'onlineEnable';
		break;

	case 'force_login':
		break;

	default:
		$found = false;
	}

	if ($found == false)
		continue;

	convert_query("
		REPLACE INTO {$to_prefix}settings
			(variable, value)
		VALUES ('" . addslashes($row['config_name']) . "', '" . addslashes($row['config_value']) . "')");
}
mysql_free_result($result);

updateSettingsFile(array(
	'mbname' => '\'' . addcslashes($inv_forum_name, '\'\\') . '\'',
	'mmessage' => '\'' . addcslashes($inv_maintenance_message, '\'\\') . '\''
));
---}
---#

/******************************************************************************/
--- Converting attachments...
/******************************************************************************/

---* {$to_prefix}attachments
---{
$no_add = true;
$keys = array('ID_ATTACH', 'size', 'filename', 'ID_MSG', 'downloads');

if (!isset($oldAttachmentDir))
{
	$result = convert_query("
		SELECT conf_value
		FROM {$from_prefix}conf_settings
		WHERE conf_key = 'upload_dir'
		LIMIT 1");
	list ($oldAttachmentDir) = mysql_fetch_row($result);
	mysql_free_result($result);

	if (empty($oldAttachmentDir) || !file_exists($oldAttachmentDir))
		$oldAttachmentDir = $_POST['path_from'] . '/uploads';
}

$oldFilename = strtr($row['oldEncrypt'], array('upload:' => ''));
$newfilename = getAttachmentFilename($row['filename'], $ID_ATTACH);
if (strlen($newfilename) <= 255 && copy($oldAttachmentDir . '/' . $oldFilename, $attachmentUploadDir . '/' . $newfilename))
{
	$rows[] = "$ID_ATTACH, " . filesize($attachmentUploadDir . '/' . $newfilename) . ", '" . addslashes($row['filename']) . "', $row[ID_MSG], $row[downloads]";

	$ID_ATTACH++;
}
---}
SELECT
	attach_pid AS ID_MSG, attach_location AS oldEncrypt,
	attach_hits AS downloads, attach_file AS filename
FROM {$from_prefix}attachments;
---*