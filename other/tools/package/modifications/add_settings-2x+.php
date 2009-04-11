<?php
/*******************************************************************************
	This is a simplified script to add settings into SMF.

	ATTENTION: If you are trying to INSTALL this package, please access
	it directly, with a URL like the following:
		http://www.yourdomain.tld/forum/add_settings.php (or similar.)

================================================================================

	This script can be used to add new settings into the database for use
	with SMF's $modSettings array.  It is meant to be run either from the
	package manager or directly by URL.

*******************************************************************************/

// Set the below to true to overwrite already existing settings with the defaults. (not recommended.)
$overwrite_old_settings = false;

// List settings here in the format: setting_key => default_value.  Escape any "s. (" => \")
$mod_settings = array(
	'example_setting' => '1',
	'example_setting2' => '0',
);

/******************************************************************************/

// If SSI.php is in the same place as this file, and SMF isn't defined, this is being run standalone.
if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
	require_once(dirname(__FILE__) . '/SSI.php');
// Hmm... no SSI.php and no SMF?
elseif (!defined('SMF'))
	die('<strong>Error:</strong> Cannot install - please verify you put this in the same place as SMF\'s index.php.');

// Sorted out the array defined above - now insert the data!
$result = $smcFunc['db_insert']($overwrite_old_settings ? 'replace' : 'ignore'),
	'{db_prefix}settings',
	array(
		'variable' => 'string', 'value' => 'string',
	),
	$mod_settings,
	array('variable'),
);

// Uh-oh spaghetti-oh!
if ($result === false)
	echo '<strong>Error:</strong> Database modifications failed!';

?>