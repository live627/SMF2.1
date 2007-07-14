/* ATTENTION: You don't need to run or use this file!  The upgrade.php script does everything for you! */

/******************************************************************************/
--- Adding Open ID support.
/******************************************************************************/

---# Adding Open ID Assocation table...
CREATE TABLE {$db_prefix}openid_assoc (
	server_url text NOT NULL,
	handle tinytext NOT NULL,
	secret text NOT NULL,
	issued int(11) NOT NULL,
	expires int(11) NOT NULL,
	assoc_type varchar(64) NOT NULL,
	PRIMARY KEY  (`server_url`(255),`handle`(255)),
	KEY `expires` (`expires`)
) TYPE=MyISAM($db_collation);
---#

---# Adding column to hold Open ID URL
ALTER TABLE {$db_prefix}members
ADD openid_uri text NOT NULL;
---#
