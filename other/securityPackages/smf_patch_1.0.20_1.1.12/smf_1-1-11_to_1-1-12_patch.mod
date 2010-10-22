<edit file>
$sourcedir/DumpDatabase.php
</edit file>
<search for>
	@set_time_limit(600);
	@ini_set('memory_limit', '128M');
</search for>

<replace>
	@set_time_limit(600);
	if (@ini_get('memory_limit') < 256)
		@ini_set('memory_limit', '256M');
</replace>


<search for>
		// Tell the client to save this file, even though it's text.
		header('Content-Type: application/octetstream');
</search for>

<replace>
		// Tell the client to save this file, even though it's text.
		header('Content-Type: ' . ($context['browser']['is_ie'] || $context['browser']['is_opera'] ? 'application/octetstream' : 'application/octet-stream'));
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
	$schema_create .= $crlf . ') ' . $schema_type . (isset($row['Type']) ? $row['Type'] : $row['Engine']) . ($row['Comment'] != '' ? ' COMMENT="' . $row['Comment'] . '"' : '');
</replace>


<edit file>
$sourcedir/ManageSearch.php
</edit file><search for>
			db_query("
				CREATE TABLE {$db_prefix}log_search_words (
					ID_WORD " . $index_properties[$context['index_settings']['bytes_per_word']]['column_definition'] . " unsigned NOT NULL default '0',
</search for>

<replace>
			// MySQL users below 4.0 can not use Engine
			if (version_compare('4', preg_replace('~\-.+?$~', '', min(mysql_get_server_info(), mysql_get_client_info()))) > 0)
				$schema_type = 'TYPE=';
			else
				$schema_type = 'ENGINE=';

			db_query("
				CREATE TABLE {$db_prefix}log_search_words (
					ID_WORD " . $index_properties[$context['index_settings']['bytes_per_word']]['column_definition'] . " unsigned NOT NULL default '0',
</replace>



<search for>
					PRIMARY KEY (ID_WORD, ID_MSG)
				) TYPE=MyISAM", __FILE__, __LINE__);
</search for>

<replace>
					PRIMARY KEY (ID_WORD, ID_MSG)
				) " . $schema_type . "MyISAM", __FILE__, __LINE__);
</replace>


<edit file>
$sourcedir/ManageSmileys.php
</edit file><search for>
*/

function ManageSmileys()
</search for>

<replace>

	void EditMessageIcons()
		// !!!

	void sortSmileyTable()
		// !!!
*/

function ManageSmileys()
</replace>


<search for>
		updateSettings(array(
			'smiley_sets_default' => empty($context['smiley_sets'][$_POST['default_smiley_set']]) ? 'default' : $context['smiley_sets'][$_POST['default_smiley_set']],
			'smiley_sets_enable' => isset($_POST['smiley_sets_enable']) ? '1' : '0',
</search for>

<replace>
		// Make sure that the smileys are in the right order after enabling them.
		if (isset($_POST['smiley_enable']))
			sortSmileyTable();

		updateSettings(array(
			'smiley_sets_default' => empty($context['smiley_sets'][$_POST['default_smiley_set']]) ? 'default' : $context['smiley_sets'][$_POST['default_smiley_set']],
			'smiley_sets_enable' => isset($_POST['smiley_sets_enable']) ? '1' : '0',
</replace>



<search for>
			db_query("
				ALTER TABLE {$db_prefix}smileys
				ORDER BY LENGTH(code) DESC", __FILE__, __LINE__);
</search for>

<replace>
			sortSmileyTable();
</replace>


<search for>
		db_query("
			ALTER TABLE {$db_prefix}smileys
			ORDER BY LENGTH(code) DESC", __FILE__, __LINE__);
</search for>

<replace>
		sortSmileyTable();
</replace>

<search for>
?>
</search for>

<replace>
// This function sorts the smiley table by code length, it is needed as MySQL withdrew support for functions in order by.
function sortSmileyTable()
{
	global $db_prefix;

	// Add a sorting column.
	db_query("
		ALTER TABLE {$db_prefix}smileys
		ADD temp_order mediumint(8) not null", __FILE__, __LINE__);

	// Set the contents of this column.
	db_query("
		UPDATE {$db_prefix}smileys
		SET temp_order = LENGTH(code)", __FILE__, __LINE__);

	// Order the table by this column.
	db_query("
		ALTER TABLE {$db_prefix}smileys
		ORDER BY temp_order DESC", __FILE__, __LINE__);

	// Remove the sorting column.
	db_query("
		ALTER TABLE {$db_prefix}smileys
		DROP temp_order", __FILE__, __LINE__);
}

?>
</replace>



<edit file>
$sourcedir/News.php
</edit file><search for>
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
$sourcedir/PackageGet.php
</edit file>
<search for>
			'name' => htmlspecialchars($row['name']),
			'url' => htmlspecialchars($row['url']),
</search for>

<replace>
	// Load the member's contextual information!
			'name' => $row['name'],
			'url' => $row['url'],
</replace>

<search for>

	db_query("
		INSERT INTO {$db_prefix}package_servers
</search for>

<replace>
	$servername = htmlspecialchars($_POST['servername']);
	$serverurl = htmlspecialchars($_POST['serverurl']);
	
	// Make sure the URL has the correct prefix.
	if (strpos($serverurl, 'http://') !== 0 && strpos($serverurl, 'https://') !== 0)
		$serverurl = 'http://' . $serverurl;

	db_query("
		INSERT INTO {$db_prefix}package_servers
</replace>



<search for>
			(name, url)
		VALUES (SUBSTRING('$_POST[servername]', 1, 255), SUBSTRING('$_POST[serverurl]', 1, 255))", __FILE__, __LINE__);
</search for>

<replace>
			(name, url)
		VALUES (SUBSTRING('$servername', 1, 255), SUBSTRING('$serverurl', 1, 255))", __FILE__, __LINE__);
</replace>



<edit file>
$sourcedir/Subs-Package.php
</edit file>

<search for>

		if (trim($lower) <= $version && trim($upper) >= $version)
</search for>

<replace>
		$lower = explode('.', $lower);
		$upper = explode('.', $upper);
		$version = explode('.', $version);

		foreach ($upper as $key => $high)
		{
			// Let's check that this is at or below the upper... obviously.
			if (isset($version[$key]) && trim($version[$key]) > trim($high))
				return false;

			// OK, let's check it's above the lower key... if it exists!
			if (isset($lower[$key]))
			{
				// The version either needs to have something here (i.e. can't be 1.0 on a 1.0.11) AND needs to be greater or equal to.
				// Note that if it's a range lower[key] might be blank, in that case version can not be set!
				if (!empty($lower[$key]) && (!isset($version[$key]) || trim($version[$key]) < trim($lower[$key])))
					return false;
			}
		}
</replace>

<edit file>
$sourcedir/Subs-Package.php
</edit file>

<search for>

	preg_match('~^(http|ftp)(s)?://([^/:]+)(:(\d))?(.+)$~', $url, $match);
</search for>

<replace>

	preg_match('~^(http|ftp)(s)?://([^/:]+)(:(\d+))?(.+)$~', $url, $match);
</replace>



<edit file>
$themes_dir/babylon/Display.template.php
</edit file>
<search for>
	if ($context['can_remove_poll'])
		$moderationButtons[] = '<a href="' . $scripturl . '?action=removepoll;topic=' . $context['current_topic'] . '.' . $context['start'] . '" onclick="return confirm(\'' . $txt['poll_remove_warn'] . '\');">' . ($settings['use_image_buttons'] ? '<img src="' . $settings['images_url'] . '/' . $context['user']['language'] . '/admin_remove_poll.gif" alt="' . $txt['poll_remove'] . '" border="0" />' : $txt['poll_remove']) . '</a>';
</search for>

<replace>
	if ($context['can_remove_poll'])
		$moderationButtons[] = '<a href="' . $scripturl . '?action=removepoll;topic=' . $context['current_topic'] . '.' . $context['start'] . ';sesc=' . $context['session_id'] . '" onclick="return confirm(\'' . $txt['poll_remove_warn'] . '\');">' . ($settings['use_image_buttons'] ? '<img src="' . $settings['images_url'] . '/' . $context['user']['language'] . '/admin_remove_poll.gif" alt="' . $txt['poll_remove'] . '" border="0" />' : $txt['poll_remove']) . '</a>';
</replace>


<edit file>
$themes_dir/classic/Display.template.php
</edit file>
<search for>
	if ($context['can_remove_poll'])
		$moderationButtons[] = '<a href="' . $scripturl . '?action=removepoll;topic=' . $context['current_topic'] . '.' . $context['start'] . '" onclick="return confirm(\'' . $txt['poll_remove_warn'] . '\');">' . ($settings['use_image_buttons'] ? '<img src="' . $settings['images_url'] . '/' . $context['user']['language'] . '/admin_remove_poll.gif" alt="' . $txt['poll_remove'] . '" border="0" />' : $txt['poll_remove']) . '</a>';
</search for>

<replace>
	if ($context['can_remove_poll'])
		$moderationButtons[] = '<a href="' . $scripturl . '?action=removepoll;topic=' . $context['current_topic'] . '.' . $context['start'] . ';sesc=' . $context['session_id'] . '" onclick="return confirm(\'' . $txt['poll_remove_warn'] . '\');">' . ($settings['use_image_buttons'] ? '<img src="' . $settings['images_url'] . '/' . $context['user']['language'] . '/admin_remove_poll.gif" alt="' . $txt['poll_remove'] . '" border="0" />' : $txt['poll_remove']) . '</a>';
</replace>


<edit file>
$themedir/Admin.template.php
</edit file><search for>
			function smfDetermineVersions()
			{
				var highYour = {"Sources": "??", "Default" : "??", "Languages": "??", "Templates": "??"};
</search for>

<replace>
			function compareVersions(current, target)
			{
				// Are they equal, maybe?
				if (current == target)
					return false;

				var currentVersion = current.split(".");
				var targetVersion = target.split(".");

				for (var i = 0, n = (currentVersion.length > targetVersion.length ? currentVersion.length : targetVersion.length); i < n; i++)
				{
					// Make sure both are set.
					if (typeof(currentVersion[i]) == "undefined")
						currentVersion[i] = "0";
					else if (typeof(targetVersion[i]) == "undefined")
						targetVersion[i] = "0";

					// If they are same, move to the next set.
					if (currentVersion[i] == targetVersion[i])
						continue;
					// Otherwise a simple comparison...
					else
						return (parseInt(currentVersion[i]) < parseInt(targetVersion[i]));
				}

				return false;
			}

			function smfDetermineVersions()
			{
				var highYour = {"Sources": "??", "Default" : "??", "Languages": "??", "Templates": "??"};
</replace>



<search for>
					if (typeof(versionType) != "undefined")
					{
						if ((highYour[versionType] < yourVersion || highYour[versionType] == "??") && !lowVersion[versionType])
</search for>

<replace>
					if (typeof(versionType) != "undefined")
					{
						if ((compareVersions(highYour[versionType], yourVersion) || highYour[versionType] == "??") && !lowVersion[versionType])
</replace>


<search for>
							highYour[versionType] = yourVersion;
						if (highCurrent[versionType] < smfVersions[filename] || highCurrent[versionType] == "??")
</search for>

<replace>
							highYour[versionType] = yourVersion;
						if (compareVersions(highCurrent[versionType], smfVersions[filename]) || highCurrent[versionType] == "??")
</replace>


<search for>

						if (yourVersion < smfVersions[filename])
</search for>

<replace>

						if (compareVersions(yourVersion, smfVersions[filename]))
</replace>


<search for>
						}
					}
					else if (yourVersion < smfVersions[filename])
</search for>

<replace>
						}
					}
					else if (compareVersions(yourVersion, smfVersions[filename]))
</replace>


<search for>

						if ((highYour["Languages"] < yourVersion || highYour["Languages"] == "??") && !lowVersion["Languages"])
</search for>

<replace>

						if ((compareVersions(highYour["Languages"], yourVersion) || highYour["Languages"] == "??") && !lowVersion["Languages"])
</replace>


<search for>
							highYour["Languages"] = yourVersion;
						if (highCurrent["Languages"] < smfLanguageVersions[filename] || highCurrent["Languages"] == "??")
</search for>

<replace>
							highYour["Languages"] = yourVersion;
						if (compareVersions(highCurrent["Languages"], smfLanguageVersions[filename]) || highCurrent["Languages"] == "??")
</replace>


<search for>

						if (yourVersion < smfLanguageVersions[filename])
</search for>

<replace>

						if (compareVersions(yourVersion, smfLanguageVersions[filename]))
</replace>


<edit file>
$themedir/Display.template.php
</edit file>
<search for>
		'merge' => array('test' => 'can_merge', 'text' => 'smf252', 'image' => 'merge.gif', 'lang' => true, 'url' => $scripturl . '?action=mergetopics;board=' . $context['current_board'] . '.0;from=' . $context['current_topic']),
		'remove_poll' => array('test' => 'can_remove_poll', 'text' => 'poll_remove', 'image' => 'admin_remove_poll.gif', 'lang' => true, 'custom' => 'onclick="return confirm(\'' . $txt['poll_remove_warn'] . '\');"', 'url' => $scripturl . '?action=removepoll;topic=' . $context['current_topic'] . '.' . $context['start']),
</search for>

<replace>
		'merge' => array('test' => 'can_merge', 'text' => 'smf252', 'image' => 'merge.gif', 'lang' => true, 'url' => $scripturl . '?action=mergetopics;board=' . $context['current_board'] . '.0;from=' . $context['current_topic']),
		'remove_poll' => array('test' => 'can_remove_poll', 'text' => 'poll_remove', 'image' => 'admin_remove_poll.gif', 'lang' => true, 'custom' => 'onclick="return confirm(\'' . $txt['poll_remove_warn'] . '\');"', 'url' => $scripturl . '?action=removepoll;topic=' . $context['current_topic'] . '.' . $context['start'] . ';sesc=' . $context['session_id']),
</replace>

<edit file>
$boarddir/index.php
</edit file>


<search for>
* Software Version:           SMF 1.1.11                                          *
</search for>

<replace>
* Software Version:           SMF 1.1.12                                          *
</replace>


<search for>
$forum_version = 'SMF 1.1.11';
</search for>

<replace>
$forum_version = 'SMF 1.1.12';
</replace>

