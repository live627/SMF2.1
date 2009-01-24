<?php

// Run the main script.
update_main();

function update_main()
{
	global $files, $path;

	// Set this to the directory containing all the files we want to update.
	$path = dirname(__FILE__);

	// Set this to disallowed files/directories.
	$disallowed = array('.svn', '.cvsignore', '.htaccess', 'index.php', '.DS_Store', 'Thumbs.db');

	// Find all the files.
	$files = scandir($path);

	// Remove the bad files automatically.
	foreach ($files as $key => $file)
		if (in_array(trim($file), array_merge($disallowed, array('.', '..', 'tools', basename(__FILE__)))) || (isset($_REQUEST['file']) && $_REQUEST['file'] != $file))
			unset($files[$key]);

	// No files?
	if (empty($files))
		exit('There are no such files!');

	$steps = array(
		1 => 'indices',
		2 => 'mysql_funcs',
		3 => 'short_join',
	);

	// Run each step.
	foreach ($steps as $step)
	{
		$func = 'convert_update_' . $step;
		$func();
	}
}

// Update the indexes.
function convert_update_indices()
{
	// This will hold the array of all changes soon.
	$replaces = array();

	// All our capital rows.
	$REPLACE_CAPITALS = array(
		'ID_MEMBER', 'ID_GROUP', 'ICQ', 'AIM', 'MSN', 'YIM', 'ID_CAT', 'ID_BOARD', 'ID_PARENT', 'ID_TOPIC',
		'ID_FIRST_MSG', 'ID_LAST_MSG', 'ID_MEMBER_STARTED', 'ID_MEMBER_UPDATED', 'ID_POLL', 'ID_CHOICE',
		'ID_PM', 'ID_MEMBER_FROM', 'ID_MSG', 'ID_MSG_UPDATED', 'ID_POST_GROUP', 'ID_ATTACH', 'ID_BAN_GROUP',
		'ID_TARGET', 'ID_EXECUTOR',
	);

	// Camel case ones
	$replaceCamel = array(
		'memberName', 'hideEmail', 'timeOffset', 'messageLabels', 'personalText', 'timeFormat', 'fromName',
		'secretQuestion', 'secretAnswer', 'passwordSalt', 'additionalGroups', 'smileySet', 'catOrder',
		'childLevel', 'numTopics', 'numPosts', 'boardOrder', 'isSticky', 'numReplies', 'numViews', 'endDate',
		'posterTime', 'posterName', 'modifiedName', 'posterEmail', 'smileysEnabled', 'minPosts', 'maxVotes',
		'modifiedTime', 'votingLocked', 'expireTime', 'hideResults', 'changeVote', 'notifyOnce','startDate',
		'deletedBySender', 'smileyOrder', 'lastLogin', 'realName', 'emailAddress', 'myNumReplies', 'numMsg',
		'websiteTitle', 'websiteUrl', 'groupName', 'onlineColor', 'dateRegistered', 'notifyAnnouncements',
		'instantMessages', 'unreadMessages', 'showOnline', 'memberGroups', 'lastEdited', 'totalMessages',
		'latestMember', 'latestRealName', 'totalMembers', 'totalTopics', 'disableHashTime', 'karmaBad',
		'karmaGood', 'canCollapse', 'countPosts', 'maxMessages', 'attachmentType', 'oldEncrypt', 'eventDate',
		'logTime', 'totalTimeLoggedIn', 'notifyTypes',
	);

	// Special ones
	$replace_special = array(
		'memberIP', 'memberIP2', 'maxMsgID', 'posterIP', 'tempID',
	);

	// Take care of capitals
	foreach ($REPLACE_CAPITALS as $rpl)
		$replaces[$rpl] = strtolower($rpl);

	// Do a loop to remove camels.
	foreach ($replaceCamel as $rpl)
		$replaces[$rpl] = removeCamelCase($rpl);

	// Speical ones.
	foreach ($replace_special as $rpl)
	{
		// Fix the one with IP at end and 6 characters long!
		if (in_array($rpl, array('memberIP', 'memberIP2', 'posterIP')))
			$replaces[$rpl] = substr($rpl, 0, 6) . '_' . strtolower(str_replace(substr($rpl, 0, 6), '', $rpl));
		elseif ($rpl == 'maxMsgID')
			$replaces['maxMsgID'] = 'max_msg_id';
		elseif ($rpl == 'tempID')
			$replaces['tempID'] = 'temp_id';
	}

	DoUpdates('strtr', $replaces);
}

// Replace mysql_* with convert_*
function convert_update_mysql_funcs()
{
	$querys = array(
		'query', 'fetch_asoc', 'fetch_row', 'free_result', 'num_rows',
		'result', 'insert', 'insert_id', 'affected_rows');

	$replaces = array();
	foreach ($querys as $query)
		$replaces['mysql_' . $query . '('] = 'convert_' . $query . '(';

	DoUpdates('strtr', $replaces);
}

// Remove the short joins
function convert_update_short_join()
{
	global $files, $path;

	// Now we loop through all file and do a strtr fix.
	$replaces = array();
	foreach ($files as $file)
	{
		// Get the contents of the file.
		$file_contents = file_get_contents($path . '/' . $file);

		// Try to find any short joins.
		preg_match_all('~(\t)*FROM \(([^\)]+)\)~is', $file_contents, $matches);

		// Lets loop trhough all the matches.
		foreach ($matches[2] as $key => $string)
		{
			// Explode the short join.
			$temp = explode(', ', $string);

			// Start off the new string with some padding (maybe).
			$newstring = $matches[1][$key] . 'FROM ' . $temp[0];
			unset($temp[0]);

			// Now all others we will inner join.
			foreach ($temp as $str)
				$newstring .= "\n" . $matches[1][$key] . '	INNER JOIN ' . $str;

			// Now get it ready to go out.
			$replaces[$matches[0][$key]] = $newstring;
		}
	}

	// Now do updates to all files.
	DoUpdates('strtr', $replaces);
}

// This recusrivly removes camel cases
function removeCamelCase($string)
{
	// Count the string up.
	$count = strlen($string);

	// For each character, try to find a hump.
	$new_string = '';
	for ($i = 0; $i < $count; $i++)
	{
		$temp = $string{$i};
		// We found it!
		if ($string{$i} == strtolower($string{$i}))
			$new_string .= $string{$i};
		else
			$new_string .= '_' . strtolower($string{$i});
	}

	return $new_string;
}

function DoUpdates($type = 'strtr', $updates)
{
	global $files, $path;

	if ($type == 'strtr')
	{
		// Now we loop through all file and do a strtr fix.
		foreach ($files as $file)
		{
			if (in_array($file, array('convert.php', 'index.php', 'Settings.php', 'tools')))
				continue;

			$file_contents = file_get_contents($path . '/' . $file);
		
			$new_contents = strtr($file_contents, $updates);

			// Trim the fat!
			$new_contents = trim($new_contents);

			// Only bother and update if its been updated.
			if ($new_contents != $file_contents)
				file_put_contents($path . '/' . $file, $new_contents);
		}
		return true;
	}
}

?>