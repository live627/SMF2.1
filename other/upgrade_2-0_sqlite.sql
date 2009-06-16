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

---# Adding additional spiders.
---{
$additional_spiders = array(
	'googlebot' => array('Google', 'googlebot', ''),
	'Googlebot-Mobile' => array('Google (Mobile)', 'Googlebot-Mobile', ''),
	'Googlebot-Image' => array('Google (Image)', 'Googlebot-Image', ''),
	'Mediapartners-Google' => array('Google (AdSense)', 'Mediapartners-Google', ''),
	'AdsBot-Google' => array('Google (Adwords)', 'AdsBot-Google', ''),
	'slurp' => array('Yahoo!', 'slurp', ''),
	'YahooSeeker/M1A1-R2D2' => array('Yahoo! (Mobile)', 'YahooSeeker/M1A1-R2D2', ''),
	'Yahoo-MMCrawler' => array('Yahoo! (Image)', 'Yahoo-MMCrawler', ''),
	'yahoo' => array('Yahoo! (Publisher)', 'yahoo', ''),
	'MSNBOT_Mobile' => array('MSN (Mobile)', 'MSNBOT_Mobile', ''),
	'msnbot-media' => array('MSN (Media)', 'msnbot-media', ''),
	'msnbot' => array('MSN', 'msnbot', ''),
	'twiceler' => array('Cuil', 'twiceler', ''),
	'Teoma' => array('Ask', 'Teoma', ''),
	'Baiduspider' => array('Baidu', 'Baiduspider', ''),
	'Gigabot' => array('Gigablast', 'Gigabot', ''),
	'ia_archiver-web.archive.org' => array('InternetArchive', 'ia_archiver-web.archive.org', ''),
	'ia_archiver' => array('Alexa', 'ia_archiver', ''),
	'omgilibot' => array('Omgili', 'omgilibot', ''),
	'Speedy Spider' => array('EntireWeb', 'Speedy Spider', ''),
);

// Lets get the current spiders.
$request = upgrade_query("
		SELECT user_agent
		FROM {$db_prefix}spiders");

while ($row = $smcFunc['db_fetch_assoc']($request))
	if (isset($additional_spiders[$row['user_agent']]))
		unset($additional_spiders[$row['user_agent']]);

// Do we have anything to insert?
if (!empty($additional_spiders))
{
	foreach ($additional_spiders as $spider)
		upgrade_query("
			INSERT INTO {$db_prefix}spiders (spider_name, user_agent, ip_info) VALUES ('$spider[0]', '$spider[1]', '$spider[2]')");
}
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
---{
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
--- Updating mail queue functionality.
/******************************************************************************/

---# Adding type to mail queue...
---{
$smcFunc['db_alter_table']('mail_queue', array(
	'add' => array(
		'private' => array(
			'name' => 'private',
			'null' => false,
			'default' => 0,
			'type' => 'tinyint',
			'size' => 1,
			'auto' => false,
		),
	)
));
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

---# Adding file hash.
---{
$smcFunc['db_alter_table']('attachments', array(
	'add' => array(
		'file_hash' => array(
			'name' => 'file_hash',
			'null' => false,
			'default' => '',
			'type' => 'varchar',
			'size' => 40,
			'auto' => false,
		),
	)
));
---}
---#

/******************************************************************************/
--- Providing more room for apf options.
/******************************************************************************/

---# Changing field_options column to a larger field type...
---{
$smcFunc['db_alter_table']('custom_fields', array(
	'change' => array(
		'aim' => array(
			'name' => 'field_options',
			'null' => false,
			'type' => 'text',
			'default' => ''
		)
	)
));
---}
---#

/******************************************************************************/
--- Adding extra columns to polls.
/******************************************************************************/

---# Adding reset poll timestamp and guest voters counter...
---{
$smcFunc['db_alter_table']('polls', array(
	'add' => array(
		'reset_poll' => array(
			'name' => 'reset_poll',
			'null' => false,
			'default' => 0,
			'type' => 'int',
			'size' => 10,
			'auto' => false,
		),
		'num_guest_voters' => array(
			'name' => 'num_guest_voters',
			'null' => false,
			'default' => 0,
			'type' => 'int',
			'size' => 10,
			'auto' => false,
		),
	)
));
---}
---#

---# Fixing guest voter tallys on existing polls...
---{
$request = upgrade_query("
	SELECT p.id_poll, count(lp.id_member) as guest_voters
	FROM {$db_prefix}polls AS p
		LEFT JOIN {$db_prefix}log_polls AS lp ON (lp.id_poll = p.id_poll AND lp.id_member = 0)
	WHERE lp.id_member = 0
		AND p.num_guest_voters = 0
	GROUP BY p.id_poll");

while ($request && $row = $smcFunc['db_fetch_assoc']($request))
	upgrade_query("
		UPDATE {$db_prefix}polls
		SET num_guest_voters = ". $row['guest_voters']. "
		WHERE id_poll = " . $row['id_poll'] . "
			AND num_guest_voters = 0");
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
--- Fixing aim on members for longer nicks.
/******************************************************************************/

---# Changing 'aim' to varchar to allow using email...
---{
$smcFunc['db_alter_table']('members', array(
	'change' => array(
		'aim' => array(
			'name' => 'aim',
			'null' => false,
			'type' => 'varchar',
			'size' => 255,
			'default' => ''
		)
	)
));
---}
---#

/******************************************************************************/
--- Allow for longer calendar event/holiday titles.
/******************************************************************************/

---# Changing event title column to a larger field type...
---{
$smcFunc['db_alter_table']('calendar', array(
	'change' => array(
		'title' => array(
			'name' => 'title',
			'null' => false,
			'type' => 'varchar',
			'size' => 60,
			'default' => ''
		)
	)
));
---}
---#

---# Changing holiday title column to a larger field type...
---{
$smcFunc['db_alter_table']('calendar_holidays', array(
	'change' => array(
		'title' => array(
			'name' => 'title',
			'null' => false,
			'type' => 'varchar',
			'size' => 60,
			'default' => ''
		)
	)
));
---}
---#

/******************************************************************************/
--- Making changes to the package manager.
/******************************************************************************/

---# Changing URL to SMF package server...
UPDATE {$db_prefix}package_servers
SET url = 'http://custom.simplemachines.org/packages/mods'
WHERE url = 'http://mods.simplemachines.org';
---#

/******************************************************************************/
--- Adding general table indexes.
/******************************************************************************/

---# Adding index for topics table...
CREATE INDEX {$db_prefix}topics_member_started ON {$db_prefix}topics (id_member_started, id_board);
---#

/******************************************************************************/
--- Adding indexes to optimize stats.
/******************************************************************************/

---# Adding index on total_time_logged_in...
CREATE INDEX {$db_prefix}members_total_time_logged_in ON {$db_prefix}members (total_time_logged_in);
---#
