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
require_once('/home/sites/simplemachines.org/public_html/community/SSI.php');
require_once('/home/sites/custom.simplemachines.org/public_html/themes/ThemeSiteSettings.php');
include_once('/home/sites/custom.simplemachines.org/public_html/themes/ThemeSiteDBSettings.php');
include_once('/home/sites/simplemachines.org/security/settings_customize.php');

unset($_SESSION['language']);

// Save some queries, do some caching.
if (($data = cache_get_data('site_latest_themes', 3600)) == null)
{
	// Get a featured theme
	$themes = array();
	$request = $smcFunc['db_query']('', '
		SELECT th.id_theme, th.theme_name, th.modified_time, th.downloads, th.id_package, th.id_preview,
			th.submit_time, th.id_type, a.filename, th.description, th.author_name
		FROM {raw:theme_prefix}featured AS fe
			LEFT JOIN {raw:theme_prefix}themes AS th ON (th.id_theme=fe.id_theme)
			LEFT JOIN {raw:theme_prefix}files AS f ON (f.id_file=th.id_package)
			LEFT JOIN {db_prefix}attachments AS a ON (a.id_attach=f.id_attach)
		WHERE th.status=1
		ORDER BY RAND()
		LIMIT 1',
		array(
			'theme_prefix' => $theme_site_db_name . '.' . $theme_site_db_prefix,
		)
	);
	if ( $smcFunc['db_num_rows']($request) )
	{
		$row = $smcFunc['db_fetch_assoc']($request);
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
	$request = $smcFunc['db_query']('', '
		SELECT th.id_theme, th.theme_name, th.modified_time, th.downloads, th.id_package, th.id_preview,
			th.submit_time, th.id_type, a.filename, th.description, th.author_name
		FROM {raw:theme_prefix}themes AS th
		LEFT JOIN {raw:theme_prefix}files AS f ON (f.id_file=th.id_package)
		LEFT JOIN {db_prefix}attachments AS a ON (a.id_attach=f.id_attach)
		WHERE th.status=1
			AND th.id_theme != {int:featured}
		ORDER BY submit_time DESC
		LIMIT 3',
		array(
			'theme_prefix' => $theme_site_db_name . '.' . $theme_site_db_prefix,
			'featured' => $featured,
		)
	);
	$latest_ids = array();
	while ( $row = $smcFunc['db_fetch_assoc']($request) )
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
	$request = $smcFunc['db_query']('', "
		SELECT th.id_theme, th.theme_name, th.modified_time, th.downloads, th.id_package, th.id_preview,
			th.submit_time, th.id_type, a.filename, th.description, th.author_name
		FROM {raw:theme_prefix}themes AS th
			LEFT JOIN {raw:theme_prefix}files AS f ON (f.id_file=th.id_package)
			LEFT JOIN {db_prefix}attachments AS a ON (a.id_attach=f.id_attach)
		WHERE th.status=1 AND th.id_theme NOT IN ({array_int:ids})
		ORDER BY RAND()
		LIMIT 1",
		array(
			'theme_prefix' => $theme_site_db_name . '.' . $theme_site_db_prefix,
			'ids' => array_merge(array($featured), $latest_ids),
		)
	);
	while ( $row = $smcFunc['db_fetch_assoc']($request) )
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
	$smcFunc['db_free_result']($request);

	if (!empty($modSettings['cache_enable']))
		cache_put_data('site_latest_themes', array($themes, $featured, $latest_ids, $random_id), 86400);
}
else
	list($themes, $featured, $latest_ids, $random_id) = $data;

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
	window.smfLatestThemes_temp = document.getElementById("smfLatestThemesWindow").innerHTML;

	// !!! Why not just always auto?
	document.getElementById("smfLatestThemesWindow").style.overflow = "auto";
	setInnerHTML(document.getElementById("smfLatestThemesWindow"),
	'\
		<h3 style="margin: 0; padding: 4px;">' + smf_themeInfo[id].name + '</h3>\
		<h4 style="margin: 0;padding: 4px;"><a href="http://custom.simplemachines.org/themes/index.php?lemma=' + id + '">View Theme Now!</a></h4>\
		<div style="overflow: auto;">\
			<img src="http://custom.simplemachines.org/themes/index.php?action=download;lemma='+id+';image=thumb" alt="" style="float: right; margin: 10px;" />\
			<div style="padding:8px;">' + smf_themeInfo[id].desc.replace(/<a href/g, '<a href') + '</div>\
		</div>\
		<div style="padding: 4px;" class="smalltext"><a href="javascript:smf_themesBack();void(0);">(go back)</a></div>');
}

function smf_themesBack()
{
	document.getElementById("smfLatestThemesWindow").style.overflow = "";
	setInnerHTML(document.getElementById("smfLatestThemesWindow"), window.smfLatestThemes_temp);
	window.scrollTo(0, findTop(document.getElementById("smfLatestThemesWindow")) - 10);
}

window.smfLatestThemes = '\
	<div id="smfLatestThemesWindow">\
		<div>\
			<img src="http://www.simplemachines.org/smf/images/themes.png" width="102" height="98" style="float: right; margin: 0 0 10px 10px;" alt="(package)" />\
			<ul style="list-style: none; padding: 0; margin: 0 0 0 5px;">';
for(var i=0; i < smf_latestThemes.length; i++)
{
	var id_theme = smf_latestThemes[i];
	window.smfLatestThemes += '\
				<li style="list-style: none;"><a href="javascript:smf_themesMoreInfo(' + id_theme + ');void(0);">' + smf_themeInfo[id_theme].name + ' by ' + smf_themeInfo[id_theme].author + '</a></li>';
}

window.smfLatestThemes += '\
			</ul>';
if ( smf_featured !=0 || smf_random != 0 )
{

	if ( smf_featured != 0 )
		window.smfLatestThemes += '\
				<h4 style="padding: 4px 4px 0 4px; margin: 0;">Featured Theme</h4>\
				<p style="padding: 0 4px; margin: 0;">\
					<a href="javascript:smf_themesMoreInfo('+smf_featured+');void(0);">'+smf_themeInfo[smf_featured].name + ' by ' + smf_themeInfo[smf_featured].author+'</a>\
				</p>';
	if ( smf_random != 0 )
		window.smfLatestThemes += '\
				<h4 style="padding: 4px 4px 0 4px;margin: 0;">Theme of the Moment</h4>\
				<p style="padding: 0 4px; margin: 0;">\
					<a href="javascript:smf_themesMoreInfo('+smf_random+');void(0);">'+smf_themeInfo[smf_random].name + ' by ' + smf_themeInfo[smf_random].author+'</a>\
				</p>';
}
window.smfLatestThemes += '\
		</div>\
	</div>';

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