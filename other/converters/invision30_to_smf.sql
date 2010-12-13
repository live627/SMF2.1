/* ATTENTION: You don't need to run or use this file!  The convert.php script does everything for you! */

/******************************************************************************/
---~ name: "Invision Power Board 3.0"
/******************************************************************************/
---~ version: "SMF 2.0"
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
if (empty($INFO['admin_group']))
	$INFO['admin_group'] = 1;

$row['signature'] = preg_replace(
	array(
		'~<!--QuoteBegin.*?-->.+?<!--QuoteEBegin-->~is',
		'~<!--QuoteEnd-->.+?<!--QuoteEEnd-->~is',
		'~<!--quoteo\(post=(.+?):date=(.+?):name=(.+?)\)-->.+?<!--quotec-->~is',
		'~<!--quoteo-->.+?<!--quotec-->~is',
		'~<!--c1-->.+?<!--ec1-->~is',
		'~<!--c2-->.+?<!--ec2-->~is',
		'~<!--coloro:.+?--><span style=\'color:([^;]+?)\'><!--/coloro-->~is',
		'~<!--coloro:.+?--><span style="color:([^;]+?)"><!--/coloro-->~is',
		'~<!--colorc--></span><!--/colorc-->~is',
		'~<!--fonto:.+?><span style=\'font-family:([^;]+?)\'><!--/fonto-->~is',
		'~<!--fonto:.+?><span style="font-family:([^;]+?)"><!--/fonto-->~is',
		'~<!--fontc--></span><!--/fontc-->~is',
		'~<!--sizeo:.+?><span style=\'font-size:([^;]+?)\'><!--/sizeo-->~is',
		'~<!--sizeo:.+?><span style="font-size:([^;]+?)"><!--/sizeo-->~is',
		'~<!--sizeo:.+?><span style="font-size:([^;]+?);line-height:100%"><!--/sizeo-->~is',
		'~<!--sizec--></span><!--/sizec-->~is',
		'~<([/]?)ul>~is',
		'~<ol type=\'a\'>~s',
		'~<ol type=\'A\'>~s',
		'~<ol type=\'1\'>~s',
		'~<ol type=\'i\'>~s',
		'~<ol type=\'I\'>~s',
		'~</ol>~is',
		'~<img src=".+?" style="vertical-align:middle" emoid=".+?" border="0" alt="(.+?)" />~i',
		'~<img src=\'~i',
		'~\' border=\'0\' alt=\'(.+?)\'( /)?' . '>~i',
		'~<img src="~i',
		'~" border="0" alt="(.+?)"( /)?' . '>~i',
		'~<!--emo&(.+?)-->.+?<!--endemo-->~i',
		'~<strike>.+?</strike>~is',
		'~<a href="mailto:.+?">.+?</a>~is',
		'~<a href="(.+?)" target="_blank">(.+?)</a>~is',
		'~<a href=\'(.+?)\' target=\'_blank\'>(.+?)</a>~is',
	),
	array(
		'[quote]',
		'[/quote]',
		'[quote=$3]',
		'[quote]',
		'[code]',
		'[/code]',
		'[color=$1]',
		'[color=$1]',
		'[/color]',
		'[font=$1]',
		'[font=$1]',
		'[/font]',
		'[size=$1]',
		'[size=$1]',
		'[size=$1]',
		'[/size]',
		'[$1list]',
		'[list type=lower-alpha]',
		'[list type=upper-alpha]',
		'[list type=decimal]',
		'[list type=lower-roman]',
		'[list type=upper-roman]',
		'[/list]',
		'$2',
		'[img]',
		'[/img]',
		'[img]',
		'[/img]',
		'$1',
		'[s]$1[/s]',
		'[email=$1]$2[/email]',
		'[url=$1]$2[/url]',
		'[url=$1]$2[/url]',
	), ltrim(stripslashes($row['signature'])));
$row['signature'] = substr(strtr(strtr($row['signature'], '<>', '[]'), array('[br /]' => '<br />')), 0, 65534);
---}
SELECT
	m.member_id AS id_member, SUBSTRING(m.name, 1, 80) AS member_name,
	SUBSTRING(m.members_display_name, 1, 255) AS real_name, m.email AS email_address,
	SUBSTRING(m.members_pass_hash, 1, 64) AS passwd, SUBSTRING(m.members_pass_salt, 1, 8) AS password_salt, 
		SUBSTRING(pp.pp_bio_content, 1, 255) AS usertitle, m.last_visit AS last_login, 
		m.joined AS date_registered, SUBSTRING(pc.field_3, 1, 255) AS website_url,
	SUBSTRING(pc.field_3, 1, 255) AS website_title,
	SUBSTRING(pc.field_4, 1, 255) AS icq, SUBSTRING(pc.field_1, 1, 16) AS aim,
	SUBSTRING(pc.field_8, 1, 32) AS yim, SUBSTRING(pc.field_2, 1, 255) AS msn,
	SUBSTRING(pp.signature, 1, 65534) AS signature, m.ip_address AS member_ip, m.ip_address AS member_ip2, '0' AS total_time_logged_in,
	IF (ISNULL(m.bday_year) AND ISNULL(m.bday_month) AND ISNULL(m.bday_day), '0001-01-01', IF (m.bday_year = 0 AND m.bday_month != 0 AND m.bday_day != 0, CONCAT('1000-', m.bday_month, '-', m.bday_day),
		 CONCAT_WS('-', IF(m.bday_year <= 1000 OR ISNULL( m.bday_year ), 1000, m.bday_year),
		 	 IF(m.bday_month = 0 OR ISNULL( m.bday_month ), 1, m.bday_month), 
		 	 IF(m.bday_day = 0 OR ISNULL( m.bday_day ), 1, m.bday_day)))) AS birthdate,
	IFNULL(m.email_pm, 0) AS pm_email_notify,
		CASE
		WHEN (m.member_group_id = '4') THEN 1
			WHEN (m.member_group_id = '2') THEN -1
			WHEN (m.member_group_id = '6') THEN 2
		ELSE 0
	END AS id_group,
		CASE
		WHEN (pc.field_5 = 'm') THEN 1
				WHEN (pc.field_5 = 'f') THEN 2
		ELSE 0
	END AS gender
FROM {$from_prefix}members AS m
	 LEFT JOIN {$from_prefix}pfields_content AS pc ON (pc.member_id = m.member_id)
	 LEFT JOIN {$from_prefix}profile_portal AS pp ON (pp.pp_member_id = m.member_id);

---{
// Get the buddies.
$id_member = $row['id_member'];
$result = convert_query("
	SELECT friends_friend_id
	FROM {$from_prefix}profile_friends
	WHERE friends_member_id = {$id_member}");
if (convert_num_rows($result) > 0)
{
	$buddy = array();
	while ($row = convert_fetch_assoc($result))
		$buddy[] = $row['friends_friend_id'];
	$row['buddy_list'] = addslashes(implode(',', $buddy));
}

// Get the ignored users.
$id_member = $row['id_member'];
$result = convert_query("
	SELECT ignore_ignore_id
	FROM {$from_prefix}ignored_users
	WHERE ignore_owner_id = {$id_member}");
if (convert_num_rows($result) > 0)
{
	$buddy = array();
	while ($row = convert_fetch_assoc($result))
		$ignore[] = $row['ignore_ignore_id'];
	$row['ignore_list'] = addslashes(implode(',', $ignore));
}

if (!empty($row['additional_groups']))
{
	$temp = explode(',', $row['additional_groups']))
	$groups = array();
	foreach ($temp as $grp)
	{
		if (empty($grp))
			continue;
		
		if ($grp > 5)
			$groups[] = $grp + 3;
		elseif ($grp == $INFO['admin_group'])
			$groups[] = 1;
		elseif ($grp == 3)
			$groups[] = 0;
		else
			$groups[] = $row['id_group'];
	}
	$row['additional_groups'] = implode(',', array_unique($groups));
}
---}
---*

/******************************************************************************/
--- Converting categories...
/******************************************************************************/

TRUNCATE {$to_prefix}categories;

---* {$to_prefix}categories
SELECT id AS id_cat, SUBSTRING(name, 1, 255) AS name, position AS cat_order
FROM {$from_prefix}forums
WHERE parent_id = -1;
---*

/******************************************************************************/
--- Converting boards...
/******************************************************************************/

TRUNCATE {$to_prefix}boards;

DELETE FROM {$to_prefix}board_permissions
WHERE id_group > 4;

/* The converter will set id_cat for us based on id_parent being wrong. */
---* {$to_prefix}boards
SELECT
	b.id AS id_board, SUBSTRING(b.name, 1, 255) AS name,
	SUBSTRING(b.description, 1, 65534) AS description, b.position AS board_order,
	b.posts AS num_posts, IF (p.parent_id = -1, 0, p.parent_id) AS id_parent, b.inc_postcount AS count_posts, 
		'-1,0' AS member_groups
FROM {$from_prefix}forums AS b
		LEFT JOIN {$from_prefix}forums AS p ON (b.parent_id = p.id)
WHERE b.parent_id != -1;
---*

/******************************************************************************/
--- Converting topics...
/******************************************************************************/

TRUNCATE {$to_prefix}topics;
TRUNCATE {$to_prefix}log_topics;
TRUNCATE {$to_prefix}log_boards;
TRUNCATE {$to_prefix}log_mark_read;

---* {$to_prefix}topics 250
SELECT
	t.tid AS id_topic, t.pinned AS is_sticky, t.forum_id AS id_board,
	t.starter_id AS id_member_started, t.last_poster_id AS id_member_updated,
	IFNULL(pl.pid,0) AS id_poll, t.posts AS num_replies, t.views AS num_views,
	MIN(p.pid) AS id_first_msg, MAX(p.pid) AS id_last_msg,
	t.state = 'closed' AS locked, approved
FROM ({$from_prefix}topics AS t, {$from_prefix}posts AS p)
	LEFT JOIN {$from_prefix}polls AS pl ON (pl.tid = t.tid)
WHERE p.topic_id = t.tid
GROUP BY t.tid
HAVING id_first_msg != 0
	AND id_last_msg != 0;
---*

/******************************************************************************/
--- Converting posts (this may take some time)...
/******************************************************************************/

TRUNCATE {$to_prefix}messages;
TRUNCATE {$to_prefix}attachments;

---* {$to_prefix}messages 200
---{
$row['body'] = addslashes(preg_replace(
	array(
		'~<!--QuoteBegin.*?-->.+?<!--QuoteEBegin-->~is',
		'~<!--QuoteEnd-->.+?<!--QuoteEEnd-->~is',
		'~<!--quoteo\(post=(.+?):date=(.+?):name=(.+?)\)-->.+?<!--quotec-->~is',
		'~<!--quoteo-->.+?<!--quotec-->~is',
		'~<!--c1-->.+?<!--ec1-->~is',
		'~<!--c2-->.+?<!--ec2-->~is',
		'~<!--coloro:.+?--><span style=\'color:([^;]+?)\'><!--/coloro-->~is',
		'~<!--coloro:.+?--><span style="color:([^;]+?)"><!--/coloro-->~is',
		'~<!--colorc--></span><!--/colorc-->~is',
		'~<!--fonto:.+?><span style=\'font-family:([^;]+?)\'><!--/fonto-->~is',
		'~<!--fonto:.+?><span style="font-family:([^;]+?)"><!--/fonto-->~is',
		'~<!--fontc--></span><!--/fontc-->~is',
		'~<!--sizeo:.+?><span style=\'font-size:([^;]+?)\'><!--/sizeo-->~is',
		'~<!--sizeo:.+?><span style="font-size:([^;]+?)"><!--/sizeo-->~is',
		'~<!--sizeo:.+?><span style="font-size:([^;]+?);line-height:100%"><!--/sizeo-->~is',
		'~<!--sizec--></span><!--/sizec-->~is',
		'~<([/]?)ul>~is',
		'~<ol type=\'a\'>~s',
		'~<ol type=\'A\'>~s',
		'~<ol type=\'1\'>~s',
		'~<ol type=\'i\'>~s',
		'~<ol type=\'I\'>~s',
		'~</ol>~is',
		'~<img src=".+?" style="vertical-align:middle" emoid=".+?" border="0" alt="(.+?)" />~i',
		'~<img src=\'~i',
		'~\' border=\'0\' alt=\'(.+?)\'( /)?' . '>~i',
		'~<img src="~i',
		'~" border="0" alt="(.+?)"( /)?' . '>~i',
		'~<!--emo&(.+?)-->.+?<!--endemo-->~i',
		'~<strike>.+?</strike>~is',
		'~<a href="mailto:.+?">.+?</a>~is',
		'~<a href="(.+?)" target="_blank">(.+?)</a>~is',
		'~<a href=\'(.+?)\' target=\'_blank\'>(.+?)</a>~is',
	),
	array(
		'[quote]',
		'[/quote]',
		'[quote=$3]',
		'[quote]',
		'[code]',
		'[/code]',
		'[color=$1]',
		'[color=$1]',
		'[/color]',
		'[font=$1]',
		'[font=$1]',
		'[/font]',
		'[size=$2]',
		'[size=$2]',
		'[size=$2]',
		'[/size]',
		'[$1list]',
		'[list type=lower-alpha]',
		'[list type=upper-alpha]',
		'[list type=decimal]',
		'[list type=lower-roman]',
		'[list type=upper-roman]',
		'[/list]',
		'$2',
		'[img]',
		'[/img]',
		'[img]',
		'[/img]',
		'$1',
		'[s]$1[/s]',
		'[email=$1]$2[/email]',
		'[url=$1]$2[/url]',
		'[url=$1]$2[/url]',
	), ltrim(stripslashes($row['body']))));
$row['body'] = substr(strtr(strtr($row['body'], '<>', '[]'), array('[br /]' => '<br />')), 0, 65534);
---}
SELECT
	p.pid AS id_msg, p.topic_id AS id_topic, p.post_date AS poster_time,
	p.author_id AS id_member, SUBSTRING(t.title, 1, 255) AS subject,
	SUBSTRING(p.author_name, 1, 255) AS poster_name,
	SUBSTRING(p.ip_address, 1, 255) AS poster_ip, p.use_emo AS smileys_enabled,
	IFNULL(p.edit_time, 0) AS modified_time, SUBSTRING(p.edit_name, 1, 255) AS modified_name,
	t.forum_id AS id_board, REPLACE(p.post, '<br />', '') AS body,
	SUBSTRING(IFNULL(m.email, 'guest@example.com'), 1, 255) AS poster_email, 'xx' AS icon
FROM {$from_prefix}posts AS p
	LEFT JOIN {$from_prefix}topics AS t ON (t.tid = p.topic_id)
	LEFT JOIN {$from_prefix}members AS m ON (m.member_id = p.author_id);
---*

/******************************************************************************/
--- Converting polls...
/******************************************************************************/

TRUNCATE {$to_prefix}polls;
TRUNCATE {$to_prefix}poll_choices;
TRUNCATE {$to_prefix}log_polls;

---* {$to_prefix}polls
SELECT
	p.pid AS id_poll, SUBSTRING(p.poll_question, 1, 255) AS question,
	p.starter_id AS id_member, SUBSTRING(IFNULL(m.name, 'Guest'), 1, 255) AS poster_name
FROM {$from_prefix}polls AS p
	LEFT JOIN {$from_prefix}members AS m ON (m.member_id = p.starter_id);
---*

/******************************************************************************/
--- Converting poll options...
/******************************************************************************/

---* {$to_prefix}poll_choices
---{
$no_add = true;
$keys = array('id_poll', 'id_choice', 'label', 'votes');
$choices = @unserialize(stripslashes($row['choices']));

if (is_array($choices))
{
	foreach ($choices as $choice)
	{
		// Put the slashes back
		$choice = addslashes_recursive($choice);

		// Since we modified the poll thing, we need to stick the question in here
		$pollquestion = $choice['question'];
		$query = convert_query("
					UPDATE {$to_prefix}polls
					SET question = '$pollquestion'
					WHERE id_poll = '$row[id_poll]'");

		// Now that we've handled the question, go ahead with our choices and votes
		foreach($choice['choice'] AS $choiceid => $label)
		{
			// The keys of the votes array correspond to the keys of the choice array,
			// which are the ID_CHOICE values
			$votes = $choice['votes'][$choiceid];

			// Try to work around the multiple-questions-per-poll issue...
			if(isset($current_choices[$row['id_poll']][$choiceid]))
				continue;
			else
				$current_choices[$row['id_poll']][$choiceid] = $label;

			// Finally - a row of information!
						$rows[] = array(
						   'id_poll' => $row['id_poll'],
						   'id_choice' => $choiceid,
						   'label' => substr(addslashes($label), 0, 255),
						   'votes' => $votes,
					);
		}
	}
}
---}
SELECT pid AS id_poll, choices
FROM {$from_prefix}polls;
---*

/******************************************************************************/
--- Converting poll votes...
/******************************************************************************/

---* {$to_prefix}log_polls
---{
$ignore = true;
---}
SELECT pl.pid AS id_poll, v.member_id AS id_member
FROM {$from_prefix}voters AS v
	LEFT JOIN {$from_prefix}polls AS pl ON (pl.tid = v.tid)
WHERE v.member_id != 0;
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
		'~<!--quoteo\(post=(.+?):date=(.+?):name=(.+?)\)-->.+?<!--quotec-->~is',
		'~<!--quoteo-->.+?<!--quotec-->~is',
		'~<!--c1-->.+?<!--ec1-->~is',
		'~<!--c2-->.+?<!--ec2-->~is',
		'~<!--coloro:.+?--><span style=\'color:([^;]+?)\'><!--/coloro-->~is',
		'~<!--coloro:.+?--><span style="color:([^;]+?)"><!--/coloro-->~is',
		'~<!--colorc--></span><!--/colorc-->~is',
		'~<!--fonto:.+?><span style=\'font-family:([^;]+?)\'><!--/fonto-->~is',
		'~<!--fonto:.+?><span style="font-family:([^;]+?)"><!--/fonto-->~is',
		'~<!--fontc--></span><!--/fontc-->~is',
		'~<!--sizeo:.+?><span style=\'font-size:([^;]+?)\'><!--/sizeo-->~is',
		'~<!--sizeo:.+?><span style="font-size:([^;]+?)"><!--/sizeo-->~is',
		'~<!--sizeo:.+?><span style="font-size:([^;]+?);line-height:100%"><!--/sizeo-->~is',
		'~<!--sizec--></span><!--/sizec-->~is',
		'~<([/]?)ul>~is',
		'~<ol type=\'a\'>~s',
		'~<ol type=\'A\'>~s',
		'~<ol type=\'1\'>~s',
		'~<ol type=\'i\'>~s',
		'~<ol type=\'I\'>~s',
		'~</ol>~is',
		'~<img src=".+?" style="vertical-align:middle" emoid=".+?" border="0" alt="(.+?)" />~i',
		'~<img src=\'~i',
		'~\' border=\'0\' alt=\'(.+?)\'( /)?' . '>~i',
		'~<img src="~i',
		'~" border="0" alt="(.+?)"( /)?' . '>~i',
		'~<!--emo&(.+?)-->.+?<!--endemo-->~i',
		'~<strike>.+?</strike>~is',
		'~<a href="mailto:.+?">.+?</a>~is',
		'~<a href="(.+?)" target="_blank">(.+?)</a>~is',
		'~<a href=\'(.+?)\' target=\'_blank\'>(.+?)</a>~is',
	),
	array(
		'[quote]',
		'[/quote]',
		'[quote=$3]',
		'[quote]',
		'[code]',
		'[/code]',
		'[color=$1]',
		'[color=$1]',
		'[/color]',
		'[font=$1]',
		'[font=$1]',
		'[/font]',
		'[size=$2]',
		'[size=$2]',
		'[size=$2]',
		'[/size]',
		'[$1list]',
		'[list type=lower-alpha]',
		'[list type=upper-alpha]',
		'[list type=decimal]',
		'[list type=lower-roman]',
		'[list type=upper-roman]',
		'[/list]',
		'$2',
		'[img]',
		'[/img]',
		'[img]',
		'[/img]',
		'$1',
		'[s]$1[/s]',
		'[email=$1]$2[/email]',
		'[url=$1]$2[/url]',
		'[url=$1]$2[/url]',
	), ltrim(stripslashes($row['body']))));
$row['body'] = strtr(strtr($row['body'], '<>', '[]'), array('[br /]' => '<br />'));
---}
SELECT
	pm.msg_id AS id_pm, pm.msg_author_id AS id_member_from, pm.msg_date AS msgtime,
	mt.mt_is_deleted AS deleted_by_sender, mt.mt_first_msg_id AS id_pm_head,
		SUBSTRING(IFNULL(m.name, "Guest"), 1, 255) AS from_name,
	SUBSTRING(mt.mt_title, 1, 255) AS subject,
	SUBSTRING(pm.msg_post, 1, 65534) AS body
FROM {$from_prefix}message_topics AS mt
		LEFT JOIN {$from_prefix}message_posts AS pm ON (mt.mt_id = pm.msg_topic_id)
		LEFT JOIN {$from_prefix}members AS m ON (m.member_id = pm.msg_author_id)
WHERE mt.mt_is_draft = 0;
---*

/******************************************************************************/
--- Converting personal messages (step 2)...
/******************************************************************************/

TRUNCATE {$to_prefix}pm_recipients;

---* {$to_prefix}pm_recipients
---{
$ignore = true;
$no_add = true;
$keys = array('id_pm', 'id_member', 'labels', 'is_read');
$invited_members = @unserialize($row['invited_members']);

$rows[] = array(
		'id_pm' => $row['id_pm'],
		'id_member' => ($row['msg_author_id'] == $row['id_member']) ? $row['mt_starter_id'] : $row['id_member'],
		'labels' => $row['labels'],
		'is_read' => $row['is_read'],
		);

if (is_array($invited_members) && !empty($invited_members))
{
	foreach ($invited_members as $invited => $id)
	{
			if (!empty($invited))
			$rows[] = array(
			'id_pm' => $row['id_pm'],
			'id_member' => ($row['msg_author_id'] == $id) ? $row['mt_starter_id'] : $id,
			'labels' => $row['labels'],
			'is_read' => $row['is_read'],
			);
	}
}
---}
SELECT pm.msg_id AS id_pm, mt.mt_to_member_id AS id_member, '-1' AS labels, 
		IF(IFNULL(mtum.map_has_unread, 1) > 0, 0, 1) AS is_read,
		mt.mt_invited_members AS invited_members, pm.msg_author_id, mt.mt_starter_id, IF(mt.mt_is_deleted = 1 AND mt.mt_starter_id = pm.msg_author_id, 1, 0) AS deleted
FROM {$from_prefix}message_topics AS mt
	LEFT JOIN {$from_prefix}message_posts AS pm ON (mt.mt_id = pm.msg_topic_id)
	LEFT JOIN {$from_prefix}message_topic_user_map AS mtum ON (mtum.map_topic_id = mt.mt_id)
WHERE mt.mt_is_draft != 1;
---*

/******************************************************************************/
--- Converting topic notifications...
/******************************************************************************/

TRUNCATE {$to_prefix}log_notify;

---* {$to_prefix}log_notify
---{
$ignore = true;
---}
SELECT member_id AS id_member, topic_id AS id_topic
FROM {$from_prefix}tracker;
---*

/******************************************************************************/
--- Converting board notifications...
/******************************************************************************/

---* {$to_prefix}log_notify
---{
$ignore = true;
---}
SELECT member_id AS id_member, forum_id AS id_board
FROM {$from_prefix}forum_tracker;
---*

/******************************************************************************/
--- Converting censored words...
/******************************************************************************/

DELETE FROM {$to_prefix}settings
WHERE variable IN ('censor_vulgar', 'censor_proper');

---# Moving censored words...
---{
$result = convert_query("
	SELECT type, swop
	FROM {$from_prefix}badwords");
$censor_vulgar = array();
$censor_proper = array();
while ($row = convert_fetch_assoc($result))
{
	$censor_vulgar[] = $row['type'];
	$censor_proper[] = $row['swop'];
}
convert_free_result($result);

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
---{
$ignore = true;

if (empty($row['id_member']))
	unset($row);
---}
SELECT member_id AS id_member, forum_id AS id_board
FROM {$from_prefix}moderators
WHERE member_id != -1;
---*

/******************************************************************************/
--- Converting calendar events...
/******************************************************************************/

TRUNCATE {$to_prefix}calendar;

---* {$to_prefix}calendar
---{
$row['start_date'] = date('Y-m-d', $row['start_date']);
$row['end_date'] = date('Y-m-d', $row['end_date']);
---}
SELECT
	event_id AS id_event, event_unixstamp AS start_date, event_unixstamp AS end_date,
	'0' AS id_board, '0' AS id_topic, SUBSTRING(event_title, 1, 30) AS title, 
		event_member_id AS id_member
FROM {$from_prefix}cal_events;
---*

/******************************************************************************/
--- Converting smileys...
/******************************************************************************/

UPDATE {$to_prefix}smileys
SET hidden = 1;

---{
$specific_smileys = array(
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
	SELECT MAX(smiley_order)
	FROM {$to_prefix}smileys");
list ($count) = convert_fetch_row($request);
convert_free_result($request);

$request = convert_query("
	SELECT code
	FROM {$to_prefix}smileys");
$current_codes = array();
while ($row = convert_fetch_assoc($request))
	$current_codes[] = $row['code'];
convert_free_result($request);

$rows = array();
foreach ($specific_smileys as $code => $name)
{
	if (in_array($code, $current_codes))
		continue;

	$count++;
	$rows[] = "'$code', '{$name}.gif', '$name', $count";
}

if (!empty($rows))
	convert_query("
		REPLACE INTO {$to_prefix}smileys
			(code, filename, description, smiley_order)
		VALUES (" . implode("),
			(", $rows) . ")");
---}

/******************************************************************************/
--- Converting attachments...
/******************************************************************************/

---* {$to_prefix}attachments
---{
$no_add = true;

if (!isset($oldAttachmentDir))
{
	$result = convert_query("
		SELECT conf_value
		FROM {$from_prefix}core_sys_conf_settings
		WHERE conf_key = 'upload_dir'
		LIMIT 1");
	list ($oldAttachmentDir) = convert_fetch_row($result);
	convert_free_result($result);

	$oldAttachmentDir = ltrim($oldAttachmentDir, '.');
}

// Is this an image???
$attachmentExtension = strtolower(substr(strrchr($row['filename'], '.'), 1));
if (!in_array($attachmentExtension, array('jpg', 'jpeg', 'gif', 'png')))
	$attachmentExtension = '';

$oldFilename = $row['filename'];
$file_hash = getAttachmentFilename($row['filename'], $id_attach, null, true);
$physical_filename = $id_attach . '_' . $file_hash;

if (strlen($physical_filename) > 255)
	return;

if (copy($oldAttachmentDir . '/' . $row['attach_location'], $attachmentUploadDir . '/' . $physical_filename))
{
	// Set the default empty values.
	$width = 0;
	$height = 0;

	// Is an an image?
	if (!empty($attachmentExtension))
	{
		list ($width, $height) = getimagesize($attachmentUploadDir . '/' . $physical_filename);
		// This shouldn't happen but apparently it might
		if(empty($width))
			$width = 0;
		if(empty($height))
			$height = 0;
	}

	$rows[] = array(
		'id_attach' => $id_attach,
		'size' => filesize($attachmentUploadDir . '/' . $physical_filename),
		'filename' => $row['filename'],
		'file_hash' => $file_hash,
		'id_msg' => $row['id_msg'],
		'downloads' => $row['attach_hits'],
		'width' => $width,
		'height' => $height,
				'approved' => $row['attach_approved']
	);

	$id_attach++;
}
---}
SELECT attach_rel_id AS id_msg, attach_hits, attach_file AS filename, attach_filesize AS filesize, attach_location, attach_approved
FROM {$from_prefix}attachments;
---*

/******************************************************************************/
--- Converting user avatars...
/******************************************************************************/

---* {$to_prefix}attachments
---{
$no_add = true;
$keys = array('id_attach', 'size', 'filename', 'id_member', 'width', 'height', 'attachment_type');

if (!isset($oldAttachmentDir) || !isset($oldAvatarDir))
{
	$result = convert_query("
		SELECT conf_value
		FROM {$from_prefix}core_sys_conf_settings
		WHERE conf_key = 'upload_dir'
		LIMIT 1");
	list ($oldAvatarDir) = convert_fetch_row($result);
	convert_free_result($result);

	if (empty($oldAttachmentDir) || !file_exists($oldAvatarDir))
		$oldAvatarDir = $_POST['path_from'] . '/uploads';
}

if (!isset($id_attach))
{
	$request = convert_query("
		SELECT MAX(ID_ATTACH)
		FROM {$to_prefix}attachments");
	list ($id_attach) = convert_fetch_row($request);
	convert_free_result($request);
}

// Find out where uploaded avatars go
$request2 = convert_query("
	SELECT value
	FROM {$to_prefix}settings
	WHERE variable = 'custom_avatar_enabled'
	LIMIT 1");

if (convert_num_rows($request2))
	list ($custom_avatar_enabled) = convert_fetch_row($request2);
else
	$custom_avatar_enabled = false;
convert_free_result($request2);

if ($custom_avatar_enabled)
{
	// Custom avatar dir.
	$request2 = convert_query("
		SELECT value
		FROM {$to_prefix}settings
		WHERE variable = 'custom_avatar_dir'
		LIMIT 1");
	list ($avatar_dir) = convert_fetch_row($request2);
	$attachmentType = '1';
}
else
{
	// Attachments dir.
	$request2 = convert_query("
		SELECT value
		FROM {$to_prefix}settings
		WHERE variable = 'attachmentUploadDir'
		LIMIT 1");
	list ($avatar_dir) = convert_fetch_row($request2);
	$attachmentType = '0';
}
convert_free_result($request2);

$smf_avatar_filename = 'avatar_' . $row['id_member'] . strrchr($row['filename'], '.');
$ipb_avatar = $oldAvatarDir . '/' . $row['filename'];

if (strlen($smf_avatar_filename) <= 255 && copy($ipb_avatar, $avatar_dir . '/' . $smf_avatar_filename))
{
	// Increase it.
	++$id_attach;

	// Get width, height, filename and ID_MEMBER
	list ($width, $height) = explode('x', $row['dimension']);
	$filesize = filesize($ipb_avatar);
	
		$rows[] = array(
		'id_attach' => $id_attach,
		'size' => $filesize,
		'filename' => addslashes($smf_avatar_filename),
				'id_member' => $row['id_member'],
		'width' => $width,
		'height' => $height,
				'attachment_type' => $attachmentType,
	);
}
---}
SELECT pp_member_id AS id_member, avatar_location AS filename, avatar_size AS dimension
FROM {$from_prefix}profile_portal
WHERE avatar_type = 'upload';
---*

/******************************************************************************/
--- Converting settings...
/******************************************************************************/

---# Moving settings...
---{
// We will do all updates once we find them all.
$update_settings = array();

$result = convert_query("
	SELECT
		conf_key AS config_name,
		IF(conf_value = '', conf_default, conf_value) AS config_value
	FROM {$from_prefix}core_sys_conf_settings");
while ($row = convert_fetch_assoc($result))
{
	switch ($row['config_name'])
	{
	case 'board_name':
		$forum_name = $row['config_value'];
		break;

	case 'offline_msg':
		$maintenance_message = str_replace("\n", '<br />', $row['config_value']);
		break;

	case 'hot_topic':
		$update_settings['hotTopicPosts'] = $row['config_value'];
		break;

	case 'display_max_posts':
		$update_settings['defaultMaxMessages'] = $row['config_value'];
		break;

	case 'display_max_topics':
		$update_settings['defaultMaxTopics'] = $row['config_value'];
		break;

	case 'flood_control':
		$update_settings['spamWaitTime'] = $row['config_value'];
		break;

	case 'allow_online_list':
		$update_settings['onlineEnable'] = $row['config_value'];
		break;

	case 'force_login':
		break;

	default:
		break;
	}
}
convert_free_result($result);

$result = convert_query("
	SELECT cs_value
	FROM {$from_prefix}cache_store
		WHERE cs_key = 'stats'");
list ($inv_stats) = convert_fetch_row($result);
$inv_stats = unserialize($inv_stats);

if (!empty($inv_stats['most_count']) && !empty($inv_stats['most_date']))
{
	$update_settings['mostOnline'] = $inv_stats['most_count'];
	$update_settings['mostDate'] = $inv_stats['most_date'];
}

// While we coulddo this in one big batch, lets do it one by one.
foreach ($update_settings as $key => $value)
	convert_query("
		REPLACE INTO {$to_prefix}settings
			(variable, value)
		VALUES ('" . addslashes($key) . "', '" . addslashes($value) . "')");

updateSettingsFile(array(
	'mbname' => '\'' . addcslashes($forum_name, '\'\\') . '\'',
	'mmessage' => '\'' . addcslashes($maintenance_message, '\'\\') . '\''
));
---}
---#