/* ATTENTION: You don't need to run or use this file!  The upgrade.php script does everything for you! */

/******************************************************************************/
--- Adding Open ID support.
/******************************************************************************/

---# Adding Open ID Assocation table...
CREATE TABLE {$db_prefix}openid_assoc (
	server_url text NOT NULL,
	handle varchar(255) NOT NULL,
	secret text NOT NULL,
	issued int NOT NULL,
	expires int NOT NULL,
	assoc_type varchar(64) NOT NULL,
	PRIMARY KEY (server_url, handle)
);
---#

/******************************************************************************/
--- Updating custom fields.
/******************************************************************************/

---# Adding search ability to custom fields.
---{
if ($db_type == 'postgresql' && $smfFunc['db_server_info'] < 8.0)
{
	upgrade_query("
		ALTER TABLE {$db_prefix}custom_fields
		ADD COLUMN can_search smallint");

	upgrade_query("
		UPDATE {$db_prefix}custom_fields
		SET can_search = 0");

	upgrade_query("
		ALTER TABLE {$db_prefix}custom_fields
		CHANGE COLUMN can_search SET default = '0'");
}
else
{
	upgrade_query("
		ALTER TABLE {$db_prefix}custom_fields
		ADD COLUMN can_search smallint NOT NULL default '0'");
}
---}
---#

---# Enhancing privacy settings for custom fields.
---{
if (isset($modSettings['smfVersion']) && $modSettings['smfVersion'] <= '2.0 Beta 1')
{
upgrade_query("
	UPDATE {$db_prefix}custom_fields
	SET private = 2
	WHERE private = 1");
}
---}
---#

/******************************************************************************/
--- Adding new board specific features.
/******************************************************************************/

---# Implementing board redirects.
---{
if ($db_type == 'postgresql' && $smfFunc['db_server_info'] < 8.0)
{
	upgrade_query("
		ALTER TABLE {$db_prefix}boards
		ADD COLUMN redirect varchar(255)");

	upgrade_query("
		UPDATE {$db_prefix}boards
		SET redirect = ''");

	upgrade_query("
		ALTER TABLE {$db_prefix}boards
		ALTER COLUMN redirect SET NOT NULL");
		
	upgrade_query("
		ALTER TABLE {$db_prefix}boards
		ALTER COLUMN redirect SET DEFAULT ''");
}
else
{
	upgrade_query("
		ALTER TABLE {$db_prefix}boards
		ADD COLUMN redirect varchar(255) NOT NULL DEFAULT ''");
}
---}
---#