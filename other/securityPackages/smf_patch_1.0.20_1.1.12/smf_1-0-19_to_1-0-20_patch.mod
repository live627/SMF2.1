<edit file>
$sourcedir/DumpDatabase.php
</edit file>
<search for>
* =============================================================================== *
* Software Version:           SMF 1.0.10                                          *
</search for>

<replace>
* =============================================================================== *
* Software Version:           SMF 1.0.20                                          *
</replace>

<search for>

	// Probably MyISAM.... and it might have a comment.
	$schema_create .= $crlf . ') TYPE=' . (isset($row['Type']) ? $row['Type'] : $row['Engine']) . ($row['Comment'] != '' ? ' COMMENT="' . $row['Comment'] . '"' : '');
</search for>

<replace>
	
	// MySQL users below 4.0 can not use Engine
	if (version_compare('4', preg_replace('~\-.+?$~', '', min(mysql_get_server_info(), mysql_get_client_info()))) > 0)
		$schema_type = 'TYPE=';
	else 
		$schema_type = 'ENGINE=';

	// Probably MyISAM.... and it might have a comment.
	$schema_create .= $crlf . ') TYPE=' . (isset($row['Type']) ? $row['Type'] : $row['Engine']) . ($row['Comment'] != '' ? ' COMMENT="' . $row['Comment'] . '"' : '');
</replace>



<search for>
	// Probably MyISAM.... and it might have a comment.
	$schema_create .= $crlf . ') TYPE=' . (isset($row['Type']) ? $row['Type'] : $row['Engine']) . ($row['Comment'] != '' ? ' COMMENT="' . $row['Comment'] . '"' : '');
</search for>

<replace>
	// Probably MyISAM.... and it might have a comment.
	$schema_create .= $crlf . ') ' . $schema_type . (isset($row['Type']) ? $row['Type'] : $row['Engine']) . ($row['Comment'] != '' ? ' COMMENT="' . $row['Comment'] . '"' : '');
</replace>


<edit file>
$sourcedir/News.php
</edit file>
<search for>
* =============================================================================== *
* Software Version:           SMF 1.0.10                                          *
</search for>

<replace>
* =============================================================================== *
* Software Version:           SMF 1.0.20                                          *
</replace>

<search for>
	// Find the most recent members.
</search for>

<replace>
	if (!allowedTo('view_mlist'))
		return array();

	// Find the most recent members.
</replace>



<search for>
	// Load the member's contextual information!
	if (!loadMemberContext($_GET['u']))
</search for>

<replace>
	// Load the member's contextual information!
	if (!loadMemberContext($_GET['u']) || !allowedTo('profile_view_any'))
</replace>


<edit file>
$boarddir/index.php
</edit file>
<search for>
* =============================================================================== *
* Software Version:           SMF 1.0.19                                          *
</search for>

<replace>
* =============================================================================== *
* Software Version:           SMF 1.0.20                                          *
</replace>


<search for>

$forum_version = 'SMF 1.0.19';
</search for>

<replace>

$forum_version = 'SMF 1.0.20';
</replace>

