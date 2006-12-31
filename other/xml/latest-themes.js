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
require_once('/home/simple/public_html/custom/themes/ThemeSiteSettings.php');
unset($_SESSION['language']);

//eaccelerator_cache_page('smf/latest_themes.js', 20);

// Get a featured theme
$themes = array();
$request = $smfFunc['db_query']('', "
	SELECT th.id_theme, th.theme_name, th.modified_time, th.downloads, th.id_package, th.id_preview,
		th.submit_time, th.id_type, a.filename, th.description, th.author_name
	FROM {$theme_prefix}featured AS fe
		LEFT JOIN {$theme_prefix}themes AS th ON (th.id_theme=fe.id_theme)
		LEFT JOIN {$theme_prefix}files AS f ON (f.id_file=th.id_package)
		LEFT JOIN {$db_prefix}attachments AS a ON (a.id_attach=f.id_attach)
	WHERE th.status=1
	ORDER BY RAND()
	LIMIT 1", __FILE__, __LINE__);
if ( $smfFunc['db_num_rows']($request) )
{
	$row = $smfFunc['db_fetch_assoc']($request);
	censorText($row['theme_name']);
	censorText($row['description']);
	$themes[$row['id_theme']] = array(
		'id' => $row['id_theme'],
		'package' => array(
			'id' => $row['id_package'],
			'name' => $row['filename'],
		),
		'short_name' => strlen($row['theme_name']) <= 20 ? $row['theme_name'] : substr($row['theme_name'], 0, 20) . '...',
		'name' => $row['theme_name'],
		'submit_time' => timeformat($row['submit_time']),
		'modify_time' => timeformat($row['modified_time']),
		'description' => parse_bbc($row['description']),
		'downloads' => $row['downloads'],
		'author_name' => $row['author_name'],
	);
	$featured = $row['id_theme'];
}
else
	$featured = 0;

// Load the theme data
$request = $smfFunc['db_query']('', "
	SELECT th.id_theme, th.theme_name, th.modified_time, th.downloads, th.id_package, th.id_preview,
		th.submit_time, th.id_type, a.filename, th.description, th.author_name
	FROM {$theme_prefix}themes AS th
	LEFT JOIN {$theme_prefix}files AS f ON (f.id_file=th.id_package)
	LEFT JOIN {$db_prefix}attachments AS a ON (a.id_attach=f.id_attach)
	WHERE th.status=1 
		AND th.id_theme != $featured
	ORDER BY submit_time DESC
	LIMIT 3", __FILE__, __LINE__);
$latest_ids = array();
while ( $row = $smfFunc['db_fetch_assoc']($request) )
{
	censorText($row['theme_name']);
	censorText($row['description']);
	$themes[$row['id_theme']] = array(
		'id' => $row['id_theme'],
		'package' => array(
			'id' => $row['id_package'],
			'name' => $row['filename'],
		),
		'short_name' => strlen($row['theme_name']) <= 20 ? $row['theme_name'] : substr($row['theme_name'], 0, 20) . '...',
		'name' => $row['theme_name'],
		'submit_time' => timeformat($row['submit_time']),
		'modify_time' => timeformat($row['modified_time']),
		'description' => parse_bbc($row['description']),
		'downloads' => $row['downloads'],
		'author_name' => $row['author_name'],
	);
	$latest_ids[] = $row['id_theme'];
}

// Grab a random theme
$request = $smfFunc['db_query']('', "
	SELECT th.id_theme, th.theme_name, th.modified_time, th.downloads, th.id_package, th.id_preview,
		th.submit_time, th.id_type, a.filename, th.description, th.author_name
	FROM {$theme_prefix}themes AS th
		LEFT JOIN {$theme_prefix}files AS f ON (f.id_file=th.id_package)
		LEFT JOIN {$db_prefix}attachments AS a ON (a.id_attach=f.id_attach)
	WHERE th.status=1 AND th.id_theme NOT IN ($featured" . (empty($latest_ids) ? '' : ',' . implode(',',$latest_ids)) . ")
	ORDER BY RAND()
	LIMIT 1", __FILE__, __LINE__);
while ( $row = $smfFunc['db_fetch_assoc']($request) )
{
	censorText($row['theme_name']);
	censorText($row['description']);
	$themes[$row['id_theme']] = array(
		'id' => $row['id_theme'],
		'package' => array(
			'id' => $row['id_package'],
			'name' => $row['filename'],
		),
		'short_name' => strlen($row['theme_name']) <= 20 ? $row['theme_name'] : substr($row['theme_name'], 0, 20) . '...',
		'name' => $row['theme_name'],
		'submit_time' => timeformat($row['submit_time']),
		'modify_time' => timeformat($row['modified_time']),
		'description' => parse_bbc($row['description']),
		'downloads' => $row['downloads'],
		'author_name' => $row['author_name'],
	);
	$random_id = $row['id_theme'];
}

$smfFunc['db_free_result']($request);


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
		author: \''.addcslashes($theme['author_name'], "'"). '\'
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
	var id_theme = smf_latestThemes[i];
	window.smfLatestThemes += '\
				<li><a style="color: black;" href="javascript:smf_themesMoreInfo(' + id_theme + ');void(0);">' + smf_themeInfo[id_theme].name + ' by ' + smf_themeInfo[id_theme].author + '</a></li>';
}

window.smfLatestThemes += '\
			</ul>\
		</td>';
if ( smf_featured !=0 || smf_random != 0 )
{
	window.smfLatestThemes += '\
		<td valign="top">';
		
	if ( smf_featured != 0 )
		window.smfLatestThemes += '\
			<center><strong>Featured Theme</center></strong>\
			<ul>\
				<li><a style="color: black;" href="javascript:smf_themesMoreInfo('+smf_featured+');void(0);">'+smf_themeInfo[smf_featured].name + ' by ' + smf_themeInfo[smf_featured].author+'</a></li>\
			</ul>';
	if ( smf_random != 0 )
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