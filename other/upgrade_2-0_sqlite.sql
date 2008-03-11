/* ATTENTION: You don't need to run or use this file!  The upgrade.php script does everything for you! */

/******************************************************************************/
--- Updating custom fields.
/******************************************************************************/

---# Adding search ability to custom fields.
---{
$request = $smcFunc['db_query']('', '
	SELECT *
	FROM {db_prefix}custom_fields
	LIMIT 1',
	array(
	)
);
$row = $smcFunc['db_fetch_assoc']($request);
$smcFunc['db_free_result']($request);

if (!in_array('can_search', array_keys($row))
{
	$smcFunc['db_transaction']('begin');
	$smcFunc['db_query']('', "
		CREATE TEMPORARY TABLE {$db_prefix}custom_fields_tmp
		(
			id_field, col_name, field_name, field_desc, field_type, field_length, field_options,
			mask, show_reg, show_display, show_profile, private, active, bbc, default_value
		)",
		'security_override'
	);
	$smcFunc['db_query']('', "
		INSERT INTO {$db_prefix}custom_fields_tmp
		SELECT
			id_field, col_name, field_name, field_desc, field_type, field_length, field_options,
			mask, show_reg, show_display, show_profile, private, active, bbc, default_value
		FROM {$db_prefix}custom_fields",
		'security_override'
	);
	$smcFunc['db_query']('', "
		DROP TABLE {$db_prefix}custom_fields",
		'security_override'
	);
	$smcFunc['db_query']('', "
		CREATE TABLE {$db_prefix}custom_fields
		(
			id_field integer primary key,
			col_name varchar(12) NOT NULL default '',
			field_name varchar(40) NOT NULL default '',
			field_desc varchar(255) NOT NULL,
			field_type varchar(8) NOT NULL default 'text',
			field_length smallint NOT NULL default '255',
			field_options varchar(255) NOT NULL,
			mask varchar(255) NOT NULL,
			show_reg smallint NOT NULL default '0',
			show_display smallint NOT NULL default '0',
			show_profile varchar(20) NOT NULL default 'forumProfile',
			private smallint NOT NULL default '0',
			active smallint NOT NULL default '1',
			bbc smallint NOT NULL default '0',
			can_search smallint NOT NULL default '0',
			default_value varchar(8) NOT NULL default '0'
		)",
		'security_override'
	);
	$smcFunc['db_query']('', "
		INSERT INTO {$db_prefix}custom_fields
		SELECT
			id_field, col_name, field_name, field_desc, field_type, field_length, field_options,
			mask, show_reg, show_display, show_profile, private, active, bbc, default_value, 0 AS can_search
		FROM {$db_prefix}custom_fields_tmp",
		'security_override'
	);
	$smcFunc['db_query']('', "
		DROP TABLE {$db_prefix}custom_fields_tmp",
		'security_override'
	);
	$smcFunc['db_transaction']('commit');
}
---}
---#

/******************************************************************************/
--- Adding search engine tracking.
/******************************************************************************/

---# Creating spider table.
CREATE TABLE {$db_prefix}spiders (
	id_spider integer primary key,
	spider_name varchar(255) NOT NULL,
	user_agent varchar(255) NOT NULL,
	ip_info varchar(255) NOT NULL
);

---{
$smcFunc['db_insert']('ignore',
	'{db_prefix}spiders',
	array('spider_name' => 'string-255', 'user_agent' => 'string-255', 'ip_info' => 'string-255'),
	array(
		array('Google', 'googlebot', ''),
		array('Yahoo!', 'slurp', ''),
		array('MSN', 'msn', ''),
	),
	array('user_agent')
);
---}
---#

---# Creating spider hit tracking table.
CREATE TABLE {$db_prefix}log_spider_hits (
	id_spider interger NOT NULL default '0',
	session varchar(32) NOT NULL default '',
	log_time int NOT NULL,
	url varchar(255) NOT NULL,
	processed smallint NOT NULL default '0'
);

CREATE INDEX {$db_prefix}log_spider_hits_id_spider ON {$db_prefix}log_spider_hits (id_spider);
CREATE INDEX {$db_prefix}log_spider_hits_log_time ON {$db_prefix}log_spider_hits (log_time);
CREATE INDEX {$db_prefix}log_spider_hits_processed ON {$db_prefix}log_spider_hits (processed);
---#

---# Creating spider statistic table.
CREATE TABLE {$db_prefix}log_spider_stats (
	id_spider integer NOT NULL default '0',
	unique_visits smallint NOT NULL default '0',
	page_hits smallint NOT NULL default '0',
	last_seen int NOT NULL default '0',
	stat_date date NOT NULL default '0001-01-01',
	PRIMARY KEY (stat_date, id_spider)
);
---#

/******************************************************************************/
--- Adding new forum settings.
/******************************************************************************/

---# Enable cache if upgrading from 1.1 and lower.
---{
if (isset($modSettings['smfVersion']) && $modSettings['smfVersion'] <= '2.0 Beta 1')
{
	$request = upgrade_query("
		SELECT value
		FROM {$db_prefix}settings
		WHERE variable = 'cache_enable'");
	list ($cache_enable) = $smcFunc['db_fetch_row']($request);

	// No cache before 1.1.
	if ($smcFunc['db_num_rows']($request) == 0)
		upgrade_query("
			INSERT INTO {$db_prefix}settings
				(variable, value)
			VALUES ('cache_enable', '1')");
	elseif (empty($cache_enable))
		upgrade_query("
			UPDATE {$db_prefix}settings
			SET value = '1'
			WHERE variable = 'cache_enable'");
}
---}
---#

/******************************************************************************/
--- Adding weekly maintenance task.
/******************************************************************************/

---# Adding scheduled task...
---{
$smcFunc['db_insert']('ignore',
	'{db_prefix}scheduled_tasks',
	array(
		'next_time' => 'int', 'time_offset' => 'int', 'time_regularity' => 'int',
		'time_unit' => 'string', 'disabled' => 'int', 'task' => 'string',
	),
	array(
		0, 0, 1, 'w', 0, 'weekly_maintenance',
	),
	array('task')
);
---}
---#

/******************************************************************************/
--- Adding log pruning.
/******************************************************************************/

---# Adding pruning option...
---{
$smcFunc['db_insert']('ignore',
	'{db_prefix}settings',
	array('variable' => 'string-255', 'value' => 'string-65534'),
	array('pruningOptions', '30,180,180,180,30'),
	array('variable')
);
---}
---#

/******************************************************************************/
--- Updating attachments.
/******************************************************************************/

---# Adding multiple attachment path functionality.
---{
$request = $smcFunc['db_query']('', '
	SELECT *
	FROM {db_prefix}attachments
	LIMIT 1',
	array(
	)
);
$row = $smcFunc['db_fetch_assoc']($request);
$smcFunc['db_free_result']($request);

if (!in_array('id_folder', array_keys($row))
{
	$smcFunc['db_transaction']('begin');
	$smcFunc['db_query']('', "
		CREATE TEMPORARY TABLE {$db_prefix}attachments_tmp (
			id_attach, id_thumb, id_msg, id_member, attachment_type, filename,
			fileext, size, downloads, width, height, mime_type, approved
		)",
		'security_override'
	);
	$smcFunc['db_query']('', "
		INSERT INTO {$db_prefix}attachments_tmp
		SELECT
			id_attach, id_thumb, id_msg, id_member, attachment_type, filename,
			fileext, size, downloads, width, height, mime_type, approved
		FROM {$db_prefix}attachments",
		'security_override'
	);
	$smcFunc['db_query']('', "
		DROP TABLE {$db_prefix}attachments",
		'security_override'
	);
	$smcFunc['db_query']('', "
		CREATE TABLE {$db_prefix}attachments (
			id_attach integer primary key,
			id_thumb int NOT NULL default '0',
			id_msg int NOT NULL default '0',
			id_member int NOT NULL default '0',
			id_folder smallint NOT NULL default '1',
			attachment_type smallint NOT NULL default '0',
			filename varchar(255) NOT NULL,
			fileext varchar(8) NOT NULL default '',
			size int NOT NULL default '0',
			downloads int NOT NULL default '0',
			width int NOT NULL default '0',
			height int NOT NULL default '0',
			mime_type varchar(20) NOT NULL default '',
			approved smallint NOT NULL default '1'
		)",
		'security_override'
	);
	$smcFunc['db_query']('', "
		INSERT INTO {$db_prefix}attachments
		SELECT
			id_attach, id_thumb, id_msg, id_member, attachment_type, filename,
			fileext, size, downloads, width, height, mime_type, approved, 0 AS id_folder
		FROM {$db_prefix}attachments_tmp",
		'security_override'
	);
	$smcFunc['db_query']('', "
		DROP TABLE {$db_prefix}attachments_tmp",
		'security_override'
	);
	$smcFunc['db_transaction']('commit');
}
---}
---#

/******************************************************************************/
--- Adding restore topic from recycle.
/******************************************************************************/

---# Adding restore from recycle feature...
---{
$request = $smcFunc['db_query']('', '
	SELECT *
	FROM {db_prefix}topics
	LIMIT 1',
	array(
	)
);
$row = $smcFunc['db_fetch_assoc']($request);
$smcFunc['db_free_result']($request);

if (!in_array('id_previous_topic', array_keys($row))
{
	$smcFunc['db_transaction']('begin');
	$smcFunc['db_query']('', "
		CREATE TEMPORARY TABLE {$db_prefix}topics_tmp (
			id_topic, is_sticky, id_board, id_first_msg, id_last_msg, id_member_started,
			id_member_updated, id_poll, num_replies, num_views, locked, unapproved_posts, approved
		)",
		'security_override'
	);
	$smcFunc['db_query']('', "
		INSERT INTO {$db_prefix}topics_tmp
		SELECT
			id_topic, is_sticky, id_board, id_first_msg, id_last_msg, id_member_started,
			id_member_updated, id_poll, num_replies, num_views, locked, unapproved_posts, approved
		FROM {$db_prefix}topics",
		'security_override'
	);
	$smcFunc['db_query']('', "
		DROP TABLE {$db_prefix}topics",
		'security_override'
	);
	$smcFunc['db_query']('', "
		CREATE TABLE {$db_prefix}topics (
			id_topic integer primary key,
			is_sticky smallint NOT NULL default '0',
			id_board smallint NOT NULL default '0',
			id_first_msg int NOT NULL default '0',
			id_last_msg int NOT NULL default '0',
			id_member_started int NOT NULL default '0',
			id_member_updated int NOT NULL default '0',
			id_poll int NOT NULL default '0',
			id_previous_board smallint NOT NULL default '0',
			id_previous_topic int NOT NULL default '0',
			num_replies int NOT NULL default '0',
			num_views int NOT NULL default '0',
			locked smallint NOT NULL default '0',
			unapproved_posts smallint NOT NULL default '0',
			approved smallint NOT NULL default '1'
		)",
		'security_override'
	);
	$smcFunc['db_query']('', "
		INSERT INTO {$db_prefix}topics
		SELECT
			id_topic, is_sticky, id_board, id_first_msg, id_last_msg, id_member_started,
			id_member_updated, id_poll, num_replies, num_views, locked, unapproved_posts, approved,
			0 AS id_previous_board, 0 AS id_previous_topic
		FROM {$db_prefix}topics_tmp",
		'security_override'
	);
	$smcFunc['db_query']('', "
		DROP TABLE {$db_prefix}topics_tmp",
		'security_override'
	);
	$smcFunc['db_transaction']('commit');
}
---}
---#

/******************************************************************************/
--- Adding general table indexes.
/******************************************************************************/

---# Adding index for topics table...
CREATE INDEX {$db_prefix}topics_member_started ON {$db_prefix}topics (id_member_started, id_board);
---#
