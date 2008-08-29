<?php

// Set this to the directory containing all the files we want to update.
$path = dirname(__FILE__) . '/drop_in';

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
	'ID_PM', 'ID_MEMBER_FROM', 'ID_MSG', 'ID_MSG_UPDATED', 'ID_POST_GROUP', 'ID_ATTACH', 'ID_BAN_GROUP'
);

// Camel case ones
$replaceCamel = array(
	'memberName', 'hideEmail', 'timeOffset', 'messageLabels', 'personalText', 'timeFormat', 'fromName'
	'secretQuestion', 'secretAnswer', 'passwordSalt', 'additionalGroups', 'smileySet', 'catOrder',
	'childLevel', 'numTopics', 'numPosts', 'boardOrder', 'isSticky', 'numReplies', 'numViews',
	'posterTime', 'posterName', 'modifiedName', 'posterEmail', 'smileysEnabled', 'minPosts', 'maxVotes'
	'modifiedTime', 'votingLocked', 'expireTime', 'hideResults', 'changeVote', 'notifyOnce',
	'deletedBySender', 'smileyOrder', 'lastLogin', 'realName', 'emailAddress', 'myNumReplies', 'numMsg',
	'websiteTitle', 'websiteUrl', 'groupName', , 'onlineColor', 'dateRegistered', 'notifyAnnouncements'
	'instantMessages', 'unreadMessages', 'showOnline', 'memberGroups', 'lastEdited', 'totalMessages',
	'latestMember', 'latestRealName', 'totalMembers', 'totalTopics', 'disableHashTime', 'karmaBad',
	'karmaGood', 'canCollapse', 'countPosts', 'maxMessages', 'attachmentType'
);

// Special ones
$replace_special = array(
	'memberIP', 'memberIP2', 'maxMsgID', 'posterIP'
);

// Take care of capitals
foreach ($REPLACE_CAPITALS as $rpl)
	$replaces[$rpl] = strtolower($rpl);

// Do a recursive loop to remove camels.
foreach ($replaceCamel as $rpl)
	$replaces[$rpl] = removeCamelCase__recursive($rpl);

// Speical ones.
foreach ($replace_special as $rpl)
{
	// Fix the one with IP at end and 6 characters long!
	if (in_array($rpl, array('memberIP', 'memberIP2', 'posterIP')))
		$replaces[$rpl] = substr($rpl, 0, 6) . '_' . strtolower(str_replace(substr($rpl, 0, 6), '', $rpl));
	elseif ($rpl == 'maxMsgID')
		$replaces['maxMsgID'] = 'max_msg_id';
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
function removeCamelCase__recursive($string, $depth = 0)
{
	// In SMF we shouldn't have more than 5 camel cases (hopefully).
	if ($depth > 5)
		return;

	// If the string is the same, return it.
	if ($string == strtolower($string))
		return $string;

	// Count the string up.
	$count = count($string);
	$i = 0;

	// For each character, try to find a hump.
	for ($i < $count)
	{
		// We found it!
		if ($string{$i} != strtolower($string{$i}))
			break;
		++$i;
	}

	// Remove a camel hump.
	$new_string = strtolower($string{($i)});

	// Add the underscore before and after the hump.
	$string = substr($new_string, 0, ($i - 1)) . '_' . substr($new_string, $i);

	// Recursivly fix it.
	return removeCamelCase__recursive($string, ++$depth);
}
?>