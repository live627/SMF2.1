<?php

// Set this to the directory containing all the files we want to update.
$path = dirname(__FILE__);

// Set this to disallowed files/directories.
$disallowed = array('.svn', '.cvsignore', '.htaccess', 'index.php', '.DS_Store', 'Thumbs.db');

// Find all the files.
$files = scandir($path);

// Remove the bad files automatically.
foreach ($files as $key => $file)
	if (in_array(trim($file), array_merge($disallowed, array('.', '..', basename(__FILE__)))))
		unset($files[$key]);

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

// Now we loop through all file and do a strtr fix.
foreach ($files as $file)
{
	$file_contents = file_get_contents($path . '/' . $file);
	
	$new_contents = strtr($file_contents, $replaces);

	// Only bother and update if its been updated.
	if ($new_contents != $file_contents)
		file_put_contents($path . '/' . $file, $new_contents);
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
?>