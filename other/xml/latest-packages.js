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

include_once('/home/sites/simplemachines.org/public_html/community/SSI.php');
include_once('/home/sites/custom.simplemachines.org/public_html/mods/ModSiteSettings.php');
include_once('/home/sites/custom.simplemachines.org/public_html/mods/ModSiteDBSettings.php');
include_once('/home/sites/simplemachines.org/security/settings_customize.php');

unset($_SESSION['language']);

// SMF 1.0 and SMF 1.1 Used a different URL then SMF 2.0.
if (!isset($_REQUEST['version']) || in_array(substr($_REQUEST['version'], 0, 7), array('SMF 1.0', 'SMF 1.1')))
	echo "var actionurl = '?action=pgdownload;auto;package=';";
else
	echo "var actionurl = '?action=admin;area=packages;sa=download;get;package=';";

// Pull the smf versions out of the table.
$result = $smcFunc['db_query']('', "
	SELECT id_ver, ver_name
	FROM {raw:customize_prefix}smfVersions
	WHERE public = 1",
	array(
		'customize_prefix' => $customize_prefix,
	)
);

$mod_site['smf_versions'] = array();
while ($row = $smcFunc['db_fetch_assoc']($result))
{
	$mod_site['smf_versions'][$row['id_ver']] = $row['vername'];
}
$smcFunc['db_free_result']($result);

header('Content-Type: text/javascript');

?>
if (typeof(window.smfForum_sessionvar) == "undefined")
	window.smfForum_sessionvar = 'sesc';

if (typeof(window.smfVersion) != "undefined" && window.smfVersion == "SMF 1.0")
	window.smfLatestPackages = 'As was inevitable, a few small mistakes have been found in 1.0.  You can install <a href="' + window.smfForum_scripturl + actionurl + 'http://custom.simplemachines.org/mods/downloads/smf_1-0-1_update.tar.gz;' + window.smfForum_sessionvar + '=' + window.smfForum_sessionid + '">this patch (click here to install)</a> to easily update yourself to the latest version.<br /><br />If you have any problems applying it, you can use the version posted on the downloads page - although, any modifications you have installed will need to be uninstalled.  Please post on the <a href="http://www.simplemachines.org/community/index.php">forum</a> if you need more help.';
else if (typeof(window.smfVersion) != "undefined" && window.smfVersion == "SMF 1.0.1")
	window.smfLatestPackages = 'A few problems have been found in the package manager\'s modification code, among a few other issues.  You can install <a href="' + window.smfForum_scripturl + actionurl + 'http://custom.simplemachines.org/mods/downloads/smf_1-0-2_update.tar.gz;' + window.smfForum_sessionvar + '=' + window.smfForum_sessionid + '">this patch (click here to install)</a> to easily update yourself to the latest version.<br /><br />If you have any problems applying it, you can use the version posted on the downloads page - although, any modifications you have installed will need to be uninstalled.  Please post on the <a href="http://www.simplemachines.org/community/index.php">forum</a> if you need more help.';
else if (typeof(window.smfVersion) != "undefined" && window.smfVersion == "SMF 1.0.2")
	window.smfLatestPackages = 'A problem has been found in the system that sends critical database messages.  You can install <a href="' + window.smfForum_scripturl + actionurl + 'http://custom.simplemachines.org/mods/downloads/smf_1-0-3_package.tar.gz;' + window.smfForum_sessionvar + '=' + window.smfForum_sessionid + '">this patch (click here to install)</a> to easily update yourself to the latest version.<br /><br />If you have any problems applying it, you can use the version posted on the downloads page - although, any modifications you have installed will need to be uninstalled.  Please post on the <a href="http://www.simplemachines.org/community/index.php">forum</a> if you need more help.';
else if (typeof(window.smfVersion) != "undefined" && window.smfVersion == "SMF 1.0.3")
	window.smfLatestPackages = 'A few bugs have been fixed since SMF 1.0.3, and a problem with parsing nested BBC tags addressed. You can install <a href="' + window.smfForum_scripturl + actionurl + 'http://custom.simplemachines.org/mods/downloads/smf_1-0-4_package.tar.gz;' + window.smfForum_sessionvar + '=' + window.smfForum_sessionid + '">this patch (click here to install)</a> to easily update yourself to the latest version.<br /><br />If you have any problems applying it, you can use the version posted on the downloads page - although, any modifications you have installed will need to be uninstalled. Please post on the <a href="http://www.simplemachines.org/community/index.php">forum</a> if you need more help.';
else if (typeof(window.smfVersion) != "undefined" && window.smfVersion == "SMF 1.0.4")
	window.smfLatestPackages = 'A security issue has been identified in SMF 1.0.4.  You can install <a href="' + window.smfForum_scripturl + actionurl + 'http://custom.simplemachines.org/mods/downloads/smf_1-0-5_package.tar.gz;' + window.smfForum_sessionvar + '=' + window.smfForum_sessionid + '">this patch (click here to install)</a> to easily update yourself to the latest version.<br /><br />If you have any problems applying it, you can use the version posted on the downloads page - although, any modifications you have installed will need to be uninstalled.  Please post on the <a href="http://www.simplemachines.org/community/index.php">forum</a> if you need more help.';
else if (typeof(window.smfVersion) != "undefined" && window.smfVersion == "SMF 1.0.5")
	window.smfLatestPackages = 'A bbc security issue has been identified in SMF 1.0.5.  You can install <a href="' + window.smfForum_scripturl + actionurl + 'http://custom.simplemachines.org/mods/downloads/smf_patch_1.0.7_1.1-RC2-1.tar.gz;' + window.smfForum_sessionvar + '=' + window.smfForum_sessionid + '">this patch (click here to install)</a> to easily update yourself to the latest version.<br /><br />If you have any problems applying it, you can use the version posted on the downloads page - although, any modifications you have installed will need to be uninstalled.  Please post on the <a href="http://www.simplemachines.org/community/index.php">forum</a> if you need more help.';
else if (typeof(window.smfVersion) != "undefined" && window.smfVersion == "SMF 1.0.6")
	window.smfLatestPackages = 'A security issue has been identified in SMF 1.0.6.  You can install <a href="' + window.smfForum_scripturl + actionurl + 'http://custom.simplemachines.org/mods/downloads/smf_patch_1.0.7_1.1-RC2-1.tar.gz;' + window.smfForum_sessionvar + '=' + window.smfForum_sessionid + '">this patch (click here to install)</a> to easily update yourself to the latest version.<br /><br />If you have any problems applying it, you can use the version posted on the downloads page - although, any modifications you have installed will need to be uninstalled.  Please post on the <a href="http://www.simplemachines.org/community/index.php">forum</a> if you need more help.';
else if (typeof(window.smfVersion) != "undefined" && window.smfVersion == "SMF 1.0.7")
	window.smfLatestPackages = 'A security issue has been identified in SMF 1.0.7.  You can install <a href="' + window.smfForum_scripturl + actionurl + 'http://custom.simplemachines.org/mods/downloads/smf_1-0-8_package.tar.gz;' + window.smfForum_sessionvar + '=' + window.smfForum_sessionid + '">this patch (click here to install)</a> to easily update yourself to the latest version.<br /><br />If you have any problems applying it, you can use the version posted on the downloads page - although, any modifications you have installed will need to be uninstalled.  Please post on the <a href="http://www.simplemachines.org/community/index.php">forum</a> if you need more help.';

else if (typeof(window.smfVersion) != "undefined" && window.smfVersion == "SMF 1.0.8")
	window.smfLatestPackages = 'A security issue has been identified in SMF 1.0.8.  You can install <a href="' + window.smfForum_scripturl + actionurl + 'http://custom.simplemachines.org/mods/downloads/smf_patch_1-0-9_1-1-rc3-1.tar.gz;' + window.smfForum_sessionvar + '=' + window.smfForum_sessionid + '">this patch (click here to install)</a> to easily update yourself to the latest version.<br /><br />If you have any problems applying it, you can use the version posted on the downloads page - although, any modifications you have installed will need to be uninstalled.  Please post on the <a href="http://www.simplemachines.org/community/index.php">forum</a> if you need more help.';
else if (typeof(window.smfVersion) != "undefined" && window.smfVersion == "SMF 1.0.9")
	window.smfLatestPackages = 'A security issue has been identified in SMF 1.0.9.  You can install <a href="' + window.smfForum_scripturl + actionurl + 'http://custom.simplemachines.org/mods/downloads/smf_1-0-10_patch.tar.gz;' + window.smfForum_sessionvar + '=' + window.smfForum_sessionid + '">this patch (click here to install)</a> to easily update yourself to the latest version.<br /><br />If you have any problems applying it, you can use the version posted on the downloads page - although, any modifications you have installed will need to be uninstalled.  Please post on the <a href="http://www.simplemachines.org/community/index.php">forum</a> if you need more help.';
else if (typeof(window.smfVersion) != "undefined" && window.smfVersion == "SMF 1.0.10")
	window.smfLatestPackages = 'A security issue has been identified in SMF 1.0.10.  You can install <a href="' + window.smfForum_scripturl + actionurl + 'http://custom.simplemachines.org/mods/downloads/smf_patch_1.1.3_1.0.11.tar.gz;' + window.smfForum_sessionvar + '=' + window.smfForum_sessionid + '">this patch (click here to install)</a> to easily update yourself to the latest version.<br /><br />If you have any problems applying it, you can use the version posted on the downloads page - although, any modifications you have installed will need to be uninstalled.  Please post on the <a href="http://www.simplemachines.org/community/index.php">forum</a> if you need more help.';
else if (typeof(window.smfVersion) != "undefined" && window.smfVersion == "SMF 1.0.11")
	window.smfLatestPackages = 'A few security vulnerabilities have been identified in SMF 1.0.11 as well as a few small bugs. You can install <a href="' + window.smfForum_scripturl + actionurl + 'http://custom.simplemachines.org/mods/downloads/smf_patch_1.0.12_1.1.4_2.0.b1.1.tar.gz;' + window.smfForum_sessionvar + '=' + window.smfForum_sessionid + '">this patch (click here to install)</a> to update your version of SMF to 1.0.12.<br /><br />If you have any problems applying it, you can use the version posted on the downloads page - although, any modifications you have installed will need to be uninstalled.  Please post on the <a href="http://www.simplemachines.org/community/index.php">forum</a> if you need more help.';
else if (typeof(window.smfVersion) != "undefined" && window.smfVersion == "SMF 1.0.12")
	window.smfLatestPackages = 'A few security vulnerabilities have been identified in SMF 1.0.12 as well as a few small bugs. You can install <a href="' + window.smfForum_scripturl + actionurl + 'http://custom.simplemachines.org/mods/downloads/smf_patch_1.0.13_1.1.5_2.0-b3.1.zip;' + window.smfForum_sessionvar + '=' + window.smfForum_sessionid + '">this patch (click here to install)</a> to update your version of SMF to 1.0.12.<br /><br />If you have any problems applying it, you can use the version posted on the downloads page - although, any modifications you have installed will need to be uninstalled.  Please post on the <a href="http://www.simplemachines.org/community/index.php">forum</a> if you need more help.';
else if (typeof(window.smfVersion) != "undefined" && window.smfVersion == "SMF 1.0.13")
	window.smfLatestPackages = 'A few security vulnerabilities have been identified in SMF 1.0.13 as well as a few small bugs. You can install <a href="' + window.smfForum_scripturl + actionurl + 'http://custom.simplemachines.org/mods/downloads/smf_patch_1.0.14_1.1.6.zip;' + window.smfForum_sessionvar + '=' + window.smfForum_sessionid + '">this patch (click here to install)</a> to update your version of SMF to 1.0.14.<br /><br />If you have any problems applying it, you can use the version posted on the downloads page - although, any modifications you have installed will need to be uninstalled.  Please post on the <a href="http://www.simplemachines.org/community/index.php">forum</a> if you need more help.';
else if (typeof(window.smfVersion) != "undefined" && window.smfVersion == "SMF 1.0.14")
	window.smfLatestPackages = 'A few security vulnerabilities have been identified in SMF 1.0.14. You can install <a href="' + window.smfForum_scripturl + actionurl + 'http://custom.simplemachines.org/mods/downloads/smf_patch_1.0.15_1.1.7.zip;' + window.smfForum_sessionvar + '=' + window.smfForum_sessionid + '">this patch (click here to install)</a> to update your version of SMF to 1.0.15.<br /><br />If you have any problems applying it, you can use the version posted on the downloads page - although, any modifications you have installed will need to be uninstalled.  Please post on the <a href="http://www.simplemachines.org/community/index.php">forum</a> if you need more help.';
else if (typeof(window.smfVersion) != "undefined" && window.smfVersion == "SMF 1.0.15")
	window.smfLatestPackages = 'A few security vulnerabilities have been identified in SMF 1.0.15. You can install <a href="' + window.smfForum_scripturl + actionurl + 'http://custom.simplemachines.org/mods/downloads/smf_patch_1.0.16_1.1.8.zip;' + window.smfForum_sessionvar + '=' + window.smfForum_sessionid + '">this patch (click here to install)</a> to update your version of SMF to 1.0.16.<br /><br />If you have any problems applying it, you can use the version posted on the downloads page - although, any modifications you have installed will need to be uninstalled.  Please post on the <a href="http://www.simplemachines.org/community/index.php">forum</a> if you need more help.';
else if (typeof(window.smfVersion) != "undefined" && window.smfVersion == "SMF 1.1 Beta 2" && !in_array("smf:smf_1-1-beta2-fix1", window.smfInstalledPackages))
	window.smfLatestPackages = 'A few bugs have been fixed since SMF 1.1 Beta 2, and a problem with parsing nested BBC tags addressed.  You can install <a href="' + window.smfForum_scripturl + actionurl + 'http://custom.simplemachines.org/mods/downloads/smf_1-1-beta2-fix1.tar.gz;' + window.smfForum_sessionvar + '=' + window.smfForum_sessionid + '">this patch (click here to install)</a> to easily fix the problem.<br /><br />Please post on the <a href="http://www.simplemachines.org/community/index.php">forum</a> or in the helpdesk if you need more help.';
else if (typeof(window.smfVersion) != "undefined" && window.smfVersion == "SMF 1.1 RC2" && !in_array("smf:smf-1.0.7", window.smfInstalledPackages))
	window.smfLatestPackages = 'A security issue has been identified in SMF 1.1 RC2. You can install <a href="' + window.smfForum_scripturl + actionurl + 'http://custom.simplemachines.org/mods/downloads/smf_patch_1.0.7_1.1-RC2-1.tar.gz;' + window.smfForum_sessionvar + '=' + window.smfForum_sessionid + '">this patch (click here to install)</a> to easily update yourself to the latest version.<br /><br />If you have any problems applying it, you can use the version posted on the downloads page - although, any modifications you have installed will need to be uninstalled.  Please post on the <a href="http://www.simplemachines.org/community/index.php">forum</a> if you need more help.';
else if (typeof(window.smfVersion) != "undefined" && window.smfVersion == "SMF 1.1 RC2" && !in_array("smf:smf_1-1-rc2-2", window.smfInstalledPackages))
	window.smfLatestPackages = 'A bug in PHP causes a vulnerability in SMF 1.1 RC2-1. You can install <a href="' + window.smfForum_scripturl + actionurl + 'http://custom.simplemachines.org/mods/downloads/smf_1-1-rc2-2_package.tar.gz;' + window.smfForum_sessionvar + '=' + window.smfForum_sessionid + '">this patch (click here to install)</a> to patch your version of 1.1 RC2 to 1.1 RC2-2.<br /><br />If you have any problems applying it, you can use the version posted on the downloads page - although, any modifications you have installed will need to be uninstalled.  Please post on the <a href="http://www.simplemachines.org/community/index.php">forum</a> if you need more help.';
else if (typeof(window.smfVersion) != "undefined" && window.smfVersion == "SMF 1.1")
	window.smfLatestPackages = 'A number of small bugs and a security issue have been identified in SMF 1.1 Final. You can install <a href="' + window.smfForum_scripturl + actionurl + 'http://custom.simplemachines.org/mods/downloads/smf_1-1-1_patch.tar.gz;' + window.smfForum_sessionvar + '=' + window.smfForum_sessionid + '">this patch (click here to install)</a> to update your version of SMF to 1.1.1.<br /><br />If you have any problems applying it, you can use the version posted on the downloads page - although, any modifications you have installed will need to be uninstalled.  Please post on the <a href="http://www.simplemachines.org/community/index.php">forum</a> if you need more help.';
else if (typeof(window.smfVersion) != "undefined" && window.smfVersion == "SMF 1.1.1")
	window.smfLatestPackages = 'A number of bugs and a couple of low risk security issues have been identified in SMF 1.1.1 - and some improvements have been made to the visual verification images on registration. You can install <a href="' + window.smfForum_scripturl + actionurl + 'http://custom.simplemachines.org/mods/downloads/smf_1-1-2_patch.tar.gz;' + window.smfForum_sessionvar + '=' + window.smfForum_sessionid + '">this patch (click here to install)</a> to update your version of SMF to 1.1.2.<br /><br />If you have any problems applying it, you can use the version posted on the downloads page - although, any modifications you have installed will need to be uninstalled.  Please post on the <a href="http://www.simplemachines.org/community/index.php">forum</a> if you need more help.';
else if (typeof(window.smfVersion) != "undefined" && window.smfVersion == "SMF 1.1.2")
	window.smfLatestPackages = 'A number of bugs and a couple of low risk security issues have been identified in SMF 1.1.2 - and some improvements have been made to the package manager. You can install <a href="' + window.smfForum_scripturl + actionurl + 'http://custom.simplemachines.org/mods/downloads/smf_patch_1.1.3_1.0.11.tar.gz;' + window.smfForum_sessionvar + '=' + window.smfForum_sessionid + '">this patch (click here to install)</a> to update your version of SMF to 1.1.3.<br /><br />If you have any problems applying it, you can use the version posted on the downloads page - although, any modifications you have installed will need to be uninstalled.  Please post on the <a href="http://www.simplemachines.org/community/index.php">forum</a> if you need more help.';
else if (typeof(window.smfVersion) != "undefined" && window.smfVersion == "SMF 1.1.3")
	window.smfLatestPackages = 'A few security vulnerabilities have been identified in SMF 1.1.3 as well as a few small bugs. You can install <a href="' + window.smfForum_scripturl + actionurl + 'http://custom.simplemachines.org/mods/downloads/smf_patch_1.0.12_1.1.4_2.0.b1.1.tar.gz;' + window.smfForum_sessionvar + '=' + window.smfForum_sessionid + '">this patch (click here to install)</a> to update your version of SMF to 1.1.4.<br /><br />If you have any problems applying it, you can use the version posted on the downloads page - although, any modifications you have installed will need to be uninstalled.  Please post on the <a href="http://www.simplemachines.org/community/index.php">forum</a> if you need more help.';
else if (typeof(window.smfVersion) != "undefined" && window.smfVersion == "SMF 1.1.4")
	window.smfLatestPackages = 'A few security vulnerabilities have been identified in SMF 1.1.4 as well as a few small bugs. You can install <a href="' + window.smfForum_scripturl + actionurl + 'http://custom.simplemachines.org/mods/downloads/smf_patch_1.0.13_1.1.5_2.0-b3.1.zip;' + window.smfForum_sessionvar + '=' + window.smfForum_sessionid + '">this patch (click here to install)</a> to update your version of SMF to 1.1.5.<br /><br />If you have any problems applying it, you can use the version posted on the downloads page - although, any modifications you have installed will need to be uninstalled.  Please post on the <a href="http://www.simplemachines.org/community/index.php">forum</a> if you need more help.';
else if (typeof(window.smfVersion) != "undefined" && window.smfVersion == "SMF 1.1.5")
	window.smfLatestPackages = 'A few security vulnerabilities have been identified in SMF 1.1.5 as well as a few small bugs. You can install <a href="' + window.smfForum_scripturl + actionurl + 'http://custom.simplemachines.org/mods/downloads/smf_patch_1.0.14_1.1.6.zip;' + window.smfForum_sessionvar + '=' + window.smfForum_sessionid + '">this patch (click here to install)</a> to update your version of SMF to 1.1.6.<br /><br />If you have any problems applying it, you can use the version posted on the downloads page - although, any modifications you have installed will need to be uninstalled.  Please post on the <a href="http://www.simplemachines.org/community/index.php">forum</a> if you need more help.';
else if (typeof(window.smfVersion) != "undefined" && window.smfVersion == "SMF 1.1.6")
	window.smfLatestPackages = 'A few security vulnerabilities have been identified in SMF 1.1.6. You can install <a href="' + window.smfForum_scripturl + actionurl + 'http://custom.simplemachines.org/mods/downloads/smf_patch_1.0.15_1.1.7.zip;' + window.smfForum_sessionvar + '=' + window.smfForum_sessionid + '">this patch (click here to install)</a> to update your version of SMF to 1.1.7.<br /><br />If you have any problems applying it, you can use the version posted on the downloads page - although, any modifications you have installed will need to be uninstalled.  Please post on the <a href="http://www.simplemachines.org/community/index.php">forum</a> if you need more help.';
else if (typeof(window.smfVersion) != "undefined" && window.smfVersion == "SMF 1.1.7")
	window.smfLatestPackages = 'A few security vulnerabilities have been identified in SMF 1.1.7. You can install <a href="' + window.smfForum_scripturl + actionurl + 'http://custom.simplemachines.org/mods/downloads/smf_patch_1.0.16_1.1.8.zip;' + window.smfForum_sessionvar + '=' + window.smfForum_sessionid + '">this patch (click here to install)</a> to update your version of SMF to 1.1.8.<br /><br />If you have any problems applying it, you can use the version posted on the downloads page - although, any modifications you have installed will need to be uninstalled.  Please post on the <a href="http://www.simplemachines.org/community/index.php">forum</a> if you need more help.';
else if (typeof(window.smfVersion) != "undefined" && window.smfVersion == "SMF 2.0 beta 1")
	window.smfLatestPackages = 'A few security vulnerabilities have been identified in SMF 2.0 beta 1 as well as a few small bugs. You can install <a href="' + window.smfForum_scripturl + actionurl + 'http://custom.simplemachines.org/mods/downloads/smf_patch_1.0.12_1.1.4_2.0.b1.1.tar.gz;' + window.smfForum_sessionvar + '=' + window.smfForum_sessionid + '">this patch (click here to install)</a> to update your version of SMF to 2.0 beta 1.1.<br /><br />If you have any problems applying it, you can use the version posted on the downloads page - although, any modifications you have installed will need to be uninstalled.  Please post on the <a href="http://www.simplemachines.org/community/index.php">forum</a> if you need more help.';
else if (typeof(window.smfVersion) != "undefined" && window.smfVersion == "SMF 2.0 Beta 3 Public")
	window.smfLatestPackages = 'A few security vulnerabilities have been identified in SMF 2.0 beta 3 as well as a few small bugs. You can install <a href="' + window.smfForum_scripturl + actionurl + 'http://custom.simplemachines.org/mods/downloads/smf_patch_1.0.13_1.1.5_2.0-b3.1.zip;' + window.smfForum_sessionvar + '=' + window.smfForum_sessionid + '">this patch (click here to install)</a> to update your version of SMF to 2.0 beta 3.1.<br /><br />If you have any problems applying it, you can use the version posted on the downloads page - although, any modifications you have installed will need to be uninstalled.  Please post on the <a href="http://www.simplemachines.org/community/index.php">forum</a> if you need more help.';
else if (typeof(window.smfVersion) == "undefined")
	window.smfLatestPackages = 'For the package manager to function properly, please upgrade to the latest version of SMF.';
else
{
var smf_modificationInfo = {
<?php

$request = $smcFunc['db_query']('', '
	(
		SELECT
			ms.id_mod, ms.mod_name, ms.modified_time, ms.downloads, ms.submit_time, 1 AS type,
			ms.smf_versions, ms.id_attach_package, a.filename, ms.description, ms.latest_version
		FROM {raw:mod_prefix}mods AS ms
			LEFT JOIN {db_prefix}attachments AS a ON (a.id_attach = ms.id_attach_package)
		WHERE ms.approved = 1
			AND ms.id_attach_package != 0
			AND ms.id_type != 11
		ORDER BY ms.id_mod DESC
		LIMIT 3
	)
	UNION ALL
	(
		SELECT
			ms.id_mod, ms.mod_name, ms.modified_time, ms.downloads, ms.submit_time, 2 AS type,
			ms.smf_versions, ms.id_attach_package, a.filename, ms.description, ms.latest_version
		FROM {raw:mod_prefix}mods AS ms
			LEFT JOIN {db_prefix}attachments AS a ON (a.id_attach = ms.id_attach_package)
		WHERE ms.approved = 1
			AND ms.id_attach_package != 0
			AND ms.id_type != 11
		ORDER BY RAND()
		LIMIT 1
	)',
	array(
		'mod_prefix' => $mod_site_db_name . '.' . $mod_site_db_prefix,
	)
);
$mods = array();
while ($row = $smcFunc['db_fetch_assoc']($request))
{
	censorText($row['mod_name']);
	censorText($row['description']);

	$mods[$row['id_mod']] = array(
		'id' => $row['id_mod'],
		'attach_id' => $row['id_attach_package'],
		'attach_filename' => $row['filename'],
		'short_name' => strlen($row['mod_name']) <= 20 ? $row['mod_name'] : substr($row['mod_name'], 0, 20) . '...',
		'name' => $row['mod_name'],
		'version' => $row['latest_version'],
		'submit_time' => timeformat($row['submit_time']),
		'modify_time' => timeformat($row['modified_time']),
		'description' => parse_bbc($row['description']),
		'downloads' => $row['downloads'],
		'smf_versions' => explode(',', $row['smf_versions']),
		'is_latest' => $row['type'] == 1,
		'is_last' => $row['type'] == 2,
	);
}
$smcFunc['db_free_result']($request);

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
	'<div id="smfLatestPackagesWindow">\
		<h3 style="margin: 0; padding: 4px;">' + smf_modificationInfo[id].name + '</h3>\
			<h4 style="padding: 4px; margin: 0;"><a href="' + window.smfForum_scripturl + actionurl + 'http://custom.simplemachines.org/mods/downloads/' + id + '/' + smf_modificationInfo[id].file + ';' + window.smfForum_sessionvar + '=' + window.smfForum_sessionid + '">Install Now!</a></h4>\
			<div style="margin: 4px;">' + smf_modificationInfo[id].desc.replace(/<a href/g, '<a href') + '</div>\
			<div class="titlebg" style="padding: 4px; margin: 0;"><a href="javascript:smf_packagesBack();void(0);">(go back)</a></div>\
	</div>');
}

function smf_packagesBack()
{
	setOuterHTML(document.getElementById("smfLatestPackagesWindow"), window.smfLatestPackages_temp);
	window.scrollTo(0, findTop(document.getElementById("smfLatestPackagesWindow")) - 10);
}

window.smfLatestPackages = '\
	<div id="smfLatestPackagesWindow"style="overflow: auto;">\
		<h3 style="margin: 0; padding: 4px;">New Packages:</h3>\
		<img src="http://www.simplemachines.org/smf/images/package.png" width="102" height="98" style="float: right; margin: 4px;" alt="(package)" />\
		<ul style="list-style: none; margin-top: 3px; padding: 0 4px;">';

for (var i = 0; i < smf_latestModifications.length; i++)
{
	var id_mod = smf_latestModifications[i];

	window.smfLatestPackages += '<li><a href="javascript:smf_packagesMoreInfo(' + id_mod + ');void(0);">' + smf_modificationInfo[id_mod].name + '</a></li>';
}

window.smfLatestPackages += '\
		</ul>';

if (typeof(window.smfVersion) != "undefined" && (window.smfVersion < "SMF 1.0.6" || (window.smfVersion == "SMF 1.1 RC2" && !in_array('smf:smf-1.0.7', window.smfInstalledPackages))))
	window.smfLatestPackages += '\
		<h3 class="error" style="margin: 0; padding: 4px;">Updates for SMF:</h3>\
		<div style="padding: 0 4px;">\
			<a href="' + window.smfForum_scripturl + actionurl + 'http://custom.simplemachines.org/mods/downloads/smf_patch_1.0.7_1.1-RC2-1.tar.gz;' + window.smfForum_sessionvar + '=' + window.smfForum_sessionid + '">Security update (X-Forwarded-For header vulnerability)</a>\
		</div>';
else
	window.smfLatestPackages += '\
		<h3 style="margin: 0; padding: 4px;">Package of the Moment:</h3>\
		<div style="padding: 0 4px;">\
			<a href="javascript:smf_packagesMoreInfo(<?php echo $moment_id; ?>);void(0);"><?php echo addcslashes($mods[$moment_id]['name'], "'"), ' ', addcslashes($mod['version'], "'"); ?></a>\
		</div>';

window.smfLatestPackages += '\
	</div>';
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
