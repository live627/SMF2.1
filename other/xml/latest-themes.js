<?php

/*	To make this work, we just need to do a few things.

	- check window.smfThemes_writable.  If it's false, they can't install
	  anything, just look.
	- load basic information for several themes, such that they can
	  "navigate" around to look at them in the panel.
	- remember that we need to have control on the color scheme
	  (white on black, etc.); we've got the element, so we can change it.
	- the url to install is:
window.smfForum_scripturl + '?action=theme=sa=install;theme_gz=' + url_to_package + ';sesc=' + window.smfForum_sessionid
	- only poackages from the .simplemachines.org domain will be accepted.
	- we've got their langauge in $_GET['language'].

*/

// Some required files to make everything work
require_once('/home/simple/public_html/community/SSI.php');
require_once('/home/simple/public_html/themes/ThemeSiteSettings.php');
unset($_SESSION['language']);

//eaccelerator_cache_page('smf/latest_themes.js', 20);

// Get a featured theme
$themes = array();
$request = my_db_query("
	SELECT th.ID_THEME, th.themeName, th.modifiedTime, th.downloads, th.ID_PACKAGE, th.ID_PREVIEW,
		th.submitTime, th.ID_TYPE, a.filename, th.description, th.authorName
	FROM {$theme_prefix}featured AS fe
	LEFT JOIN {$theme_prefix}themes AS th ON (th.ID_THEME=fe.ID_THEME)
	LEFT JOIN {$theme_prefix}files AS f ON (f.ID_FILE=th.ID_PACKAGE)
	LEFT JOIN {$db_prefix}attachments AS a ON (a.ID_ATTACH=f.ID_ATTACH)
	WHERE th.status=1
	ORDER BY RAND()
	LIMIT 1", __FILE__, __LINE__);
if (mysql_num_rows($request))
{
	$row = mysql_fetch_assoc($request);
	censorText($row['themeName']);
	censorText($row['description']);
	$themes[$row['ID_THEME']] = array(
		'id' => $row['ID_THEME'],
		'package' => array(
			'id' => $row['ID_PACKAGE'],
			'name' => $row['filename'],
		),
		'short_name' => strlen($row['themeName']) <= 20 ? $row['themeName'] : substr($row['themeName'], 0, 20) . '...',
		'name' => $row['themeName'],
		'submit_time' => timeformat($row['submitTime']),
		'modify_time' => timeformat($row['modifiedTime']),
		'description' => parse_bbc($row['description']),
		'downloads' => $row['downloads'],
		'authorName' => $row['authorName'],
	);
	$featured = $row['ID_THEME'];
}
else
	$featured = 0;

// Load the theme data
$request = my_db_query("
	SELECT th.ID_THEME, th.themeName, th.modifiedTime, th.downloads, th.ID_PACKAGE, th.ID_PREVIEW,
		th.submitTime, th.ID_TYPE, a.filename, th.description, th.authorName
	FROM {$theme_prefix}themes AS th
	LEFT JOIN {$theme_prefix}files AS f ON (f.ID_FILE=th.ID_PACKAGE)
	LEFT JOIN {$db_prefix}attachments AS a ON (a.ID_ATTACH=f.ID_ATTACH)
	WHERE th.status=1 AND th.ID_THEME != $featured
	ORDER BY submitTime DESC
	LIMIT 3", __FILE__, __LINE__);
$latest_ids = array();
while ($row = mysql_fetch_assoc($request))
{
	censorText($row['themeName']);
	censorText($row['description']);
	$themes[$row['ID_THEME']] = array(
		'id' => $row['ID_THEME'],
		'package' => array(
			'id' => $row['ID_PACKAGE'],
			'name' => $row['filename'],
		),
		'short_name' => strlen($row['themeName']) <= 20 ? $row['themeName'] : substr($row['themeName'], 0, 20) . '...',
		'name' => $row['themeName'],
		'submit_time' => timeformat($row['submitTime']),
		'modify_time' => timeformat($row['modifiedTime']),
		'description' => parse_bbc($row['description']),
		'downloads' => $row['downloads'],
		'authorName' => $row['authorName'],
	);
	$latest_ids[] = $row['ID_THEME'];
}

// Grab a random theme
$request = my_db_query("
	SELECT th.ID_THEME, th.themeName, th.modifiedTime, th.downloads, th.ID_PACKAGE, th.ID_PREVIEW,
		th.submitTime, th.ID_TYPE, a.filename, th.description, th.authorName
	FROM {$theme_prefix}themes AS th
	LEFT JOIN {$theme_prefix}files AS f ON (f.ID_FILE=th.ID_PACKAGE)
	LEFT JOIN {$db_prefix}attachments AS a ON (a.ID_ATTACH=f.ID_ATTACH)
	WHERE th.status=1 AND th.ID_THEME NOT IN ($featured" . (empty($latest_ids) ? '' : ',' . implode(',',$latest_ids)) . ")
	ORDER BY RAND()
	LIMIT 1", __FILE__, __LINE__);
while ($row = mysql_fetch_assoc($request))
{
	censorText($row['themeName']);
	censorText($row['description']);
	$themes[$row['ID_THEME']] = array(
		'id' => $row['ID_THEME'],
		'package' => array(
			'id' => $row['ID_PACKAGE'],
			'name' => $row['filename'],
		),
		'short_name' => strlen($row['themeName']) <= 20 ? $row['themeName'] : substr($row['themeName'], 0, 20) . '...',
		'name' => $row['themeName'],
		'submit_time' => timeformat($row['submitTime']),
		'modify_time' => timeformat($row['modifiedTime']),
		'description' => parse_bbc($row['description']),
		'downloads' => $row['downloads'],
		'authorName' => $row['authorName'],
	);
	$random_id = $row['ID_THEME'];
}

mysql_free_result($request);


header('Content-Type: text/javascript');
echo '
var smf_themeInfo = {';
$temp_output = array();
foreach($themes AS $theme)
{
	
	$temp_output[] = '
	'. $theme['id']. ': {
		name: \''. addcslashes($theme['name'], "'"). '\',
		desc: \''. addcslashes($theme['description'], "'"). '\',
		file: \''. addcslashes($theme['package']['name'], "'"). '\',
		author: \''.addcslashes($theme['authorName'], "'"). '\'
	}';
}
echo implode(',', $temp_output), '
};
var smf_featured = ', (int)$featured, ';
var smf_random = ', (int)$random_id, ';
var smf_latestThemes = [', implode(', ', $latest_ids), '];';
?>

function smf_themesMoreInfo(id)
{
	window.smfLatestThemes_temp = getOuterHTML(document.getElementById("smfLatestThemesWindow"));
	setOuterHTML(document.getElementById("smfLatestThemesWindow"),
	'<table id="smfLatestThemesWindow" width="100%" cellpadding="2" cellspacing="0" style="background: white; color=black; border: 1px solid black;"><tr>\
		<td style="background: white; color:black;">\
			<div align="center" class="largetext" style="margin-bottom: 4px;">' + smf_themeInfo[id].name + '</div>\
			<div><a style="color: black;" class="largetext" href="http://themes.simplemachines.org?lemma=' + id + '">View Theme Now!</a></div>\
			<img src="http://themes.simplemachines.org?action=download;lemma='+id+';image=thumb" alt="" style="float: left; margin-right: 10px;" />\
			<div style="margin: 1ex;">' + smf_themeInfo[id].desc.replace(/<a href/g, '<a style="color: black;" href') + '</div>\
			<div align="center" class="smalltext"><a style="color: black;clear: both;" href="javascript:smf_themesBack();void(0);">(go back)</a></div>\
		</td>\
	</tr></table>');
}

function smf_themesBack()
{
	setOuterHTML(document.getElementById("smfLatestThemesWindow"), window.smfLatestThemes_temp);
	window.scrollTo(0, findTop(document.getElementById("smfLatestThemesWindow")) - 10);
}

window.smfLatestThemes = '\
	<table id="smfLatestThemesWindow" width=100%" cellpadding="5" cellspacing="0" style="background: white; color: black; border: 1px solid black;"><tr>\
		<td width="110" style="background: white; color: black;"><img src="http://www.simplemachines.org/smf/package.png" width="100" height="96" alt="(package)" /></td>\
		<td valign="top"><center><strong>Latest Themes</strong></center>\
			<ul>';
for(var i=0; i < smf_latestThemes.length; i++)
{
	var ID_THEME = smf_latestThemes[i];
	window.smfLatestThemes += '\
				<li><a style="color: black;" href="javascript:smf_themesMoreInfo(' + ID_THEME + ');void(0);">' + smf_themeInfo[ID_THEME].name + ' by ' + smf_themeInfo[ID_THEME].author + '</a></li>';
}

window.smfLatestThemes += '\
			</ul>\
		</td>';
if (smf_featured !=0 || smf_random != 0)
{
	window.smfLatestThemes += '\
		<td valign="top">';
		
	if (smf_featured != 0)
		window.smfLatestThemes += '\
			<center><strong>Featured Theme</center></strong>\
			<ul>\
				<li><a style="color: black;" href="javascript:smf_themesMoreInfo('+smf_featured+');void(0);">'+smf_themeInfo[smf_featured].name + ' by ' + smf_themeInfo[smf_featured].author+'</a></li>\
			</ul>';
	if (smf_random != 0)
		window.smfLatestThemes += '\
			<center><strong>Theme of the Moment</center></strong>\
			<ul>\
				<li><a style="color: black;" href="javascript:smf_themesMoreInfo('+smf_random+');void(0);">'+smf_themeInfo[smf_random].name + ' by ' + smf_themeInfo[smf_random].author+'</a></li>\
			</ul>';
	window.smfLatestThemes += '\
		</td>';
}
window.smfLatestThemes += '\
	</table>';

function findTop(el)
{
	if (typeof(el.tagName) == "undefined")
		return 0;

	var skipMe = in_array(el.tagName.toLowerCase(), el.parentNode ? ["tr", "tbody", "form"] : []);
	var coordsParent = el.parentNode ? "parentNode" : "offsetParent";

	if (el[coordsParent] == null || typeof(el[coordsParent].offsetTop) == "undefined")
		return skipMe ? 0 : el.offsetTop;
	else
		return (skipMe ? 0 : el.offsetTop) + findTop(el[coordsParent]);
}

function in_array(item, array)
{
	for (var i in array)
	{
		if (array[i] == item)
			return true;
	}

	return false;
}

<?php
function my_db_query($query, $file, $line)
{
	$res = mysql_query($query);
	if (!$res)
	{
		ob_clean();
		die("<pre>$query</pre>" . mysql_error() . "<br/>$file : $line");
	}
	return $res;
}
?>