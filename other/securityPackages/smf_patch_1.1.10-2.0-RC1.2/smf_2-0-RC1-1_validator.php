<?php
// This combats an issue with 2.0 RC1-1. The - can make the install for believe it is doing a range of versions instead of a single version.

if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
	require_once(dirname(__FILE__) . '/SSI.php');
// If it's executed from the Packages/tmp dir.
elseif (file_exists(dirname(__FILE__) . '/../../SSI.php') && !defined('SMF'))
	require_once(dirname(__FILE__) . '/../../SSI.php');

// Incase SSI ran it, the database should contain the version for 2.0 RC1-1.
if (!isset($forum_version) && !empty($smcFunc))
{
	$request = $smcFunc['db_query']('', '
		SELECT value
		FROM {db_prefix}settings
		WHERE variable = {string:smf_version}',
		array(
			'smf_version' => 'smfVersion',
	));
	// How ever this happens, the world may never know.
	if ($smcFunc['db_num_rows'] != 1)
		return;
	else
	{
		list($forum_version) = $smcFunc['db_fetch_row']($request);
		$smcFunc['db_free_result']($request);
	}
}

// If we are not running 2.0 RC1-1, we can't run this.
if ($forum_version != 'SMF 2.0 RC1-1')
	fatal_error('You are running "' . $forum_version . '", this installer is only compatible with SMF 2.0 RC1-1', false);

?>