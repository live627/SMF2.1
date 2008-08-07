/* ATTENTION: You don't need to run or use this file!  The upgrade.php script does everything for you! */

/******************************************************************************/
--- Updating custom fields.
/******************************************************************************/

---# Adding search ability to custom fields.
---{
$smcFunc['db_alter_table']('custom_fields', array(
	'add' => array(
		'can_search' => array(
			'name' => 'can_search',
			'null' => false,
			'default' => 0,
			'type' => 'smallint',
			'size' => 255,
			'auto' => false,
		),
	),
));

if (isset($modSettings['smfVersion']) && $modSettings['smfVersion'] < '2.0 Beta 4')
{
upgrade_query("
	UPDATE {$db_prefix}custom_fields
	SET private = 3
	WHERE private = 2");
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
---#

---# Inserting the search engines.
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
	id_spider integer NOT NULL default '0',
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

---# Adding advanced password brute force protection to "members" table...
$smcFunc['db_alter_table']('members', array(
	'add' => array(
		'passwd_flood' => array(
			'name' => 'passwd_flood',
			'null' => false,
			'default' => '',
			'type' => 'varchar',
			'size' => 12,
			'auto' => false,
		),
	)
));
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
$smcFunc['db_alter_table']('attachments', array(
	'add' => array(
		'id_folder' => array(
			'name' => 'id_folder',
			'null' => false,
			'default' => 1,
			'type' => 'smallint',
			'size' => 255,
			'auto' => false,
		),
	)
));
---}
---#

/******************************************************************************/
--- Adding restore topic from recycle.
/******************************************************************************/

---# Adding restore from recycle feature...
---{
$smcFunc['db_alter_table']('topics', array(
	'add' => array(
		'id_previous_board' => array(
			'name' => 'id_previous_board',
			'null' => false,
			'default' => 0,
			'type' => 'smallint',
			'auto' => false,
		),
		'id_previous_topic' => array(
			'name' => 'id_previous_topic',
			'null' => false,
			'default' => 0,
			'type' => 'int',
			'auto' => false,
		),
	)
));
---}
---#

/******************************************************************************/
--- Adding general table indexes.
/******************************************************************************/

---# Adding index for topics table...
CREATE INDEX {$db_prefix}topics_member_started ON {$db_prefix}topics (id_member_started, id_board);
---#