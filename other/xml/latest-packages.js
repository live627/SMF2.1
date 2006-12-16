<?php

/*	To make this work, we just need to do a few things.

	- load basic information for several packages, such that they can
	  "navigate" around to look at them in the panel.
	- give a better offering by checking window.smfInstalledPackages
	  which holds the ids of all installed packages.
	- remember that we need to have control on the color scheme
	  (white on black, etc.); we've got the element, so we can change it.
	- the url to install is:
window.smfForum_scripturl . '?action=pgdownload;auto;package=' + url_to_package + ';sesc=' + window.smfForum_sessionid
	- only packages from the .simplemachines.org domain will be accepted.
	- we've got their langauge in $_GET['language'].
	- we know their forum version in window.smfVersion.

*/

include_once('/home/simple/public_html/community/SSI.php');
include_once('/home/simple/public_html/mods/ModSiteSettings.php');

unset($_SESSION['language']);

eaccelerator_cache_page('smf/latest-packages.js', 20);

// Pull the smf versions out of the table.
$result = db_query("
	SELECT ID_VER, verName
	FROM {$customize_prefix}smfVersions
	WHERE public = 1", __FILE__, __LINE__);

$mod_site['smf_versions'] = array();
while ($row = mysql_fetch_assoc($result))
{
	$mod_site['smf_versions'][$row['ID_VER']] = $row['vername'];
}
mysql_free_result($result);

header('Content-Type: text/javascript');

?>

if (typeof(window.smfVersion) != "undefined" && window.smfVersion == "SMF 1.0")
	window.smfLatestPackages = 'As was inevitable, a few small mistakes have been found in 1.0.  You can install <a href="' + window.smfForum_scripturl + '?action=pgdownload;auto;package=http://mods.simplemachines.org/downloads/smf_1-0-1_update.tar.gz;sesc=' + window.smfForum_sessionid + '">this patch (click here to install)</a> to easily update yourself to the latest version.<br /><br />If you have any problems applying it, you can use the version posted on the downloads page - although, any modifications you have installed will need to be uninstalled.  Please post on the <a href="http://www.simplemachines.org/community/index.php">forum</a> if you need more help.';
else if (typeof(window.smfVersion) != "undefined" && window.smfVersion == "SMF 1.0.1")
	window.smfLatestPackages = 'A few problems have been found in the package manager\'s modification code, among a few other issues.  You can install <a href="' + window.smfForum_scripturl + '?action=pgdownload;auto;package=http://mods.simplemachines.org/downloads/smf_1-0-2_update.tar.gz;sesc=' + window.smfForum_sessionid + '">this patch (click here to install)</a> to easily update yourself to the latest version.<br /><br />If you have any problems applying it, you can use the version posted on the downloads page - although, any modifications you have installed will need to be uninstalled.  Please post on the <a href="http://www.simplemachines.org/community/index.php">forum</a> if you need more help.';
else if (typeof(window.smfVersion) != "undefined" && window.smfVersion == "SMF 1.0.2")
	window.smfLatestPackages = 'A problem has been found in the system that sends critical database messages.  You can install <a href="' + window.smfForum_scripturl + '?action=pgdownload;auto;package=http://mods.simplemachines.org/downloads/smf_1-0-3_package.tar.gz;sesc=' + window.smfForum_sessionid + '">this patch (click here to install)</a> to easily update yourself to the latest version.<br /><br />If you have any problems applying it, you can use the version posted on the downloads page - although, any modifications you have installed will need to be uninstalled.  Please post on the <a href="http://www.simplemachines.org/community/index.php">forum</a> if you need more help.';
else if (typeof(window.smfVersion) != "undefined" && window.smfVersion == "SMF 1.0.3")
	window.smfLatestPackages = 'A few bugs have been fixed since SMF 1.0.3, and a problem with parsing nested BBC tags addressed. You can install <a href="' + window.smfForum_scripturl + '?action=pgdownload;auto;package=http://mods.simplemachines.org/downloads/smf_1-0-4_package.tar.gz;sesc=' + window.smfForum_sessionid + '">this patch (click here to install)</a> to easily update yourself to the latest version.<br /><br />If you have any problems applying it, you can use the version posted on the downloads page - although, any modifications you have installed will need to be uninstalled. Please post on the <a href="http://www.simplemachines.org/community/index.php">forum</a> if you need more help.';
else if (typeof(window.smfVersion) != "undefined" && window.smfVersion == "SMF 1.0.4")
	window.smfLatestPackages = 'A security issue has been identified in SMF 1.0.4.  You can install <a href="' + window.smfForum_scripturl + '?action=pgdownload;auto;package=http://mods.simplemachines.org/downloads/smf_1-0-5_package.tar.gz;sesc=' + window.smfForum_sessionid + '">this patch (click here to install)</a> to easily update yourself to the latest version.<br /><br />If you have any problems applying it, you can use the version posted on the downloads page - although, any modifications you have installed will need to be uninstalled.  Please post on the <a href="http://www.simplemachines.org/community/index.php">forum</a> if you need more help.';
else if (typeof(window.smfVersion) != "undefined" && window.smfVersion == "SMF 1.0.5")
	window.smfLatestPackages = 'A bbc security issue has been identified in SMF 1.0.5.  You can install <a href="' + window.smfForum_scripturl + '?action=pgdownload;auto;package=http://mods.simplemachines.org/downloads/smf_patch_1.0.7_1.1-RC2-1.tar.gz;sesc=' + window.smfForum_sessionid + '">this patch (click here to install)</a> to easily update yourself to the latest version.<br /><br />If you have any problems applying it, you can use the version posted on the downloads page - although, any modifications you have installed will need to be uninstalled.  Please post on the <a href="http://www.simplemachines.org/community/index.php">forum</a> if you need more help.';
else if (typeof(window.smfVersion) != "undefined" && window.smfVersion == "SMF 1.0.6")
	window.smfLatestPackages = 'A security issue has been identified in SMF 1.0.6.  You can install <a href="' + window.smfForum_scripturl + '?action=pgdownload;auto;package=http://mods.simplemachines.org/downloads/smf_patch_1.0.7_1.1-RC2-1.tar.gz;sesc=' + window.smfForum_sessionid + '">this patch (click here to install)</a> to easily update yourself to the latest version.<br /><br />If you have any problems applying it, you can use the version posted on the downloads page - although, any modifications you have installed will need to be uninstalled.  Please post on the <a href="http://www.simplemachines.org/community/index.php">forum</a> if you need more help.';
else if (typeof(window.smfVersion) != "undefined" && window.smfVersion == "SMF 1.0.7")
	window.smfLatestPackages = 'A security issue has been identified in SMF 1.0.7.  You can install <a href="' + window.smfForum_scripturl + '?action=pgdownload;auto;package=http://mods.simplemachines.org/downloads/smf_1-0-8_package.tar.gz;sesc=' + window.smfForum_sessionid + '">this patch (click here to install)</a> to easily update yourself to the latest version.<br /><br />If you have any problems applying it, you can use the version posted on the downloads page - although, any modifications you have installed will need to be uninstalled.  Please post on the <a href="http://www.simplemachines.org/community/index.php">forum</a> if you need more help.';
else if (typeof(window.smfVersion) != "undefined" && window.smfVersion == "SMF 1.1 Beta 2" && !in_array("smf:smf_1-1-beta2-fix1", window.smfInstalledPackages))
	window.smfLatestPackages = 'A few bugs have been fixed since SMF 1.1 Beta 2, and a problem with parsing nested BBC tags addressed.  You can install <a href="' + window.smfForum_scripturl + '?action=pgdownload;auto;package=http://mods.simplemachines.org/downloads/smf_1-1-beta2-fix1.tar.gz;sesc=' + window.smfForum_sessionid + '">this patch (click here to install)</a> to easily fix the problem.<br /><br />Please post on the <a href="http://www.simplemachines.org/community/index.php">forum</a> or in the helpdesk if you need more help.';
else if (typeof(window.smfVersion) != "undefined" && window.smfVersion == "SMF 1.1 RC2" && !in_array("smf:smf-1.0.7", window.smfInstalledPackages))
	window.smfLatestPackages = 'A security issue has been identified in SMF 1.1 RC2. You can install <a href="' + window.smfForum_scripturl + '?action=pgdownload;auto;package=http://mods.simplemachines.org/downloads/smf_patch_1.0.7_1.1-RC2-1.tar.gz;sesc=' + window.smfForum_sessionid + '">this patch (click here to install)</a> to easily update yourself to the latest version.<br /><br />If you have any problems applying it, you can use the version posted on the downloads page - although, any modifications you have installed will need to be uninstalled.  Please post on the <a href="http://www.simplemachines.org/community/index.php">forum</a> if you need more help.';
else if (typeof(window.smfVersion) != "undefined" && window.smfVersion == "SMF 1.1 RC2" && !in_array("smf:smf_1-1-rc2-2", window.smfInstalledPackages))
	window.smfLatestPackages = 'A bug in PHP causes a vulnerability in SMF 1.1 RC2-1. You can install <a href="' + window.smfForum_scripturl + '?action=pgdownload;auto;package=http://mods.simplemachines.org/downloads/smf_1-1-rc2-2_package.tar.gz;sesc=' + window.smfForum_sessionid + '">this patch (click here to install)</a> to patch your version of 1.1 RC2 to 1.1 RC2-2.<br /><br />If you have any problems applying it, you can use the version posted on the downloads page - although, any modifications you have installed will need to be uninstalled.  Please post on the <a href="http://www.simplemachines.org/community/index.php">forum</a> if you need more help.';
else if (typeof(window.smfVersion) != "undefined" && window.smfVersion == "SMF 1.1")
	window.smfLatestPackages = 'A number of small bugs and a security issue have been identified in SMF 1.1 Final. You can install <a href="' + window.smfForum_scripturl + '?action=pgdownload;auto;package=http://mods.simplemachines.org/downloads/smf_1-1-1_patch.tar.gz;sesc=' + window.smfForum_sessionid + '">this patch (click here to install)</a> to update your version of SMF to 1.1.1.<br /><br />If you have any problems applying it, you can use the version posted on the downloads page - although, any modifications you have installed will need to be uninstalled.  Please post on the <a href="http://www.simplemachines.org/community/index.php">forum</a> if you need more help.';
else if (typeof(window.smfVersion) == "undefined")
	window.smfLatestPackages = 'For the package manager to function properly, please upgrade to the latest version of SMF.';
else
{
var smf_modificationInfo = {
<?php

$request = db_query('
	(
		SELECT
			ms.ID_MOD, ms.modName, ms.modifiedTime, ms.downloads, ms.submitTime, 1 AS type,
			ms.smfVersions, ms.ID_ATTACH_PACKAGE, a.filename, ms.description, ms.latestVersion
		FROM ' . $db_prefix . 'mods AS ms
			LEFT JOIN ' . $db_prefix . 'attachments AS a ON (a.ID_ATTACH = ms.ID_ATTACH_PACKAGE)
		WHERE ms.approved = 1
			AND ms.ID_ATTACH_PACKAGE != 0
		ORDER BY ms.ID_MOD DESC
		LIMIT 3
	)
	UNION ALL
	(
		SELECT
			ms.ID_MOD, ms.modName, ms.modifiedTime, ms.downloads, ms.submitTime, 2 AS type,
			ms.smfVersions, ms.ID_ATTACH_PACKAGE, a.filename, ms.description, ms.latestVersion
		FROM ' . $db_prefix . 'mods AS ms
			LEFT JOIN ' . $db_prefix . 'attachments AS a ON (a.ID_ATTACH = ms.ID_ATTACH_PACKAGE)
		WHERE ms.approved = 1
			AND ms.ID_ATTACH_PACKAGE != 0
		ORDER BY RAND()
		LIMIT 1
	)', __FILE__, __LINE__);
$mods = array();
while ($row = mysql_fetch_assoc($request))
{
	censorText($row['modName']);
	censorText($row['description']);

	$mods[$row['ID_MOD']] = array(
		'id' => $row['ID_MOD'],
		'attach_id' => $row['ID_ATTACH_PACKAGE'],
		'attach_filename' => $row['filename'],
		'short_name' => strlen($row['modName']) <= 20 ? $row['modName'] : substr($row['modName'], 0, 20) . '...',
		'name' => $row['modName'],
		'version' => $row['latestVersion'],
		'submit_time' => timeformat($row['submitTime']),
		'modify_time' => timeformat($row['modifiedTime']),
		'description' => doUBBC($row['description']),
		'downloads' => $row['downloads'],
		'smf_versions' => explode(',', $row['smfVersions']),
		'is_latest' => $row['type'] == 1,
		'is_last' => $row['type'] == 2,
	);
}
mysql_free_result($request);

foreach ($mod_site['smf_versions'] as $i => $ver)
{
	if (isset($mod_site['smf_versions'][trim($ver)]))
		$context['mod']['smf_versions'][$i] = $mod_site['smf_versions'][trim($ver)];
	else
		unset($context['mod']['smf_versions'][$i]);
}

$latest_ids = array();
$moment_id = 0;
foreach ($mods as $mod)
{
	echo '
	', $mod['id'], ': {
		name: \'', addcslashes($mod['name'], "'"), ' ', addcslashes($mod['version'], "'"), '\',
		versions: [\'', implode('\', \'', $mod['smf_versions']), '\'],
		desc: \'', addcslashes($mod['description'], "'"), '\',
		file: \'', addcslashes($mod['attach_filename'], "'"), '\'
	}', empty($mod['is_last']) ? ',' : '';

	if ($mod['is_latest'])
		$latest_ids[] = $mod['id'];
	else
		$moment_id = $mod['id'];
}

?>
};
var smf_latestModifications = [<?php echo implode(', ', $latest_ids); ?>];

function smf_packagesMoreInfo(id)
{
	window.smfLatestPackages_temp = getOuterHTML(document.getElementById("smfLatestPackagesWindow"));

	setOuterHTML(document.getElementById("smfLatestPackagesWindow"),
	'<table id="smfLatestPackagesWindow" width="100%" cellpadding="2" cellspacing="0" style="background: white; color: black; border: 1px solid black; height: 100px;"><tr>\
		<td style="background: white; color: black;">\
			<div align="center" class="largetext" style="margin-bottom: 4px;">' + smf_modificationInfo[id].name + '</div>\
			<div><a style="color: black;" href="' + window.smfForum_scripturl + '?action=pgdownload;auto;package=http://mods.simplemachines.org/downloads/' + id + '/' + smf_modificationInfo[id].file + ';sesc=' + window.smfForum_sessionid + '">Install Now!</a></div>\
			<div style="margin: 1ex;">' + smf_modificationInfo[id].desc.replace(/<a href/g, '<a style="color: black;" href') + '</div>\
			<div align="center" class="smalltext"><a style="color: black;" href="javascript:smf_packagesBack();void(0);">(go back)</a></div>\
		</td>\
	</tr></table>');
}

function smf_packagesBack()
{
	setOuterHTML(document.getElementById("smfLatestPackagesWindow"), window.smfLatestPackages_temp);
	window.scrollTo(0, findTop(document.getElementById("smfLatestPackagesWindow")) - 10);
}

window.smfLatestPackages = '\
	<table id="smfLatestPackagesWindow" width="100%" cellpadding="2" cellspacing="0" style="background: white; color: black; border: 1px solid black; height: 100px;"><tr>\
		<td width="110" style="background: white; color: black;"><img src="http://www.simplemachines.org/smf/package.png" width="100" height="96" alt="(package)" /></td>\
		<td valign="top" width="30%" style="background: white; color: black;">\
			<b>New Packages:</b><br />\
			<ol style="margin-top: 3px;">';

for (var i = 0; i < smf_latestModifications.length; i++)
{
	var ID_MOD = smf_latestModifications[i];

	window.smfLatestPackages += '<li><a style="color: black;" href="javascript:smf_packagesMoreInfo(' + ID_MOD + ');void(0);">' + smf_modificationInfo[ID_MOD].name + '</a></li>';
}

window.smfLatestPackages += '\
			</ol>\
		</td>\
		<td valign="top" style="background: white; color: black;">';

if (typeof(window.smfVersion) != "undefined" && (window.smfVersion < "SMF 1.0.6" || (window.smfVersion == "SMF 1.1 RC2" && !in_array('smf:smf-1.0.7', window.smfInstalledPackages))))
	window.smfLatestPackages += '\
			<b style="color: red;">Updates for SMF:</b><br />\
			<ul style="margin-top: 3px;">\
				<li><a style="color: black;" href="' + window.smfForum_scripturl + '?action=pgdownload;auto;package=http://mods.simplemachines.org/downloads/smf_patch_1.0.7_1.1-RC2-1.tar.gz;sesc=' + window.smfForum_sessionid + '">Security update (X-Forwarded-For header vulnerability)</a></li>\
			</ul>';
else
	window.smfLatestPackages += '\
			<b>Package of the Moment:</b><br />\
			<ul style="margin-top: 3px;">\
				<li><a style="color: black;" href="javascript:smf_packagesMoreInfo(<?php echo $moment_id; ?>);void(0);"><?php echo addcslashes($mods[$moment_id]['name'], "'"), ' ', addcslashes($mod['version'], "'"); ?></a></li>\
			</ul>';

window.smfLatestPackages += '\
		</td>\
	</tr></table>';
}

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
