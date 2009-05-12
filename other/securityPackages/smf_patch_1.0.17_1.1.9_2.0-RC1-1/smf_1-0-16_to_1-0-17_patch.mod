<edit file>
$boarddir/index.php
</edit file>

<search for>
* Software Version:           SMF 1.0.16                                          *
</search for>

<replace>
* Software Version:           SMF 1.0.17                                          *
</replace>



<search for>
* Copyright 2006 by:          Simple Machines LLC (http://www.simplemachines.org) *
</search for>

<replace>
* Copyright 2006-2009 by:     Simple Machines LLC (http://www.simplemachines.org) *
</replace>



<search for>
$forum_version = 'SMF 1.0.16';
</search for>

<replace>
$forum_version = 'SMF 1.0.17';
</replace>


<edit file>
$sourcedir/Load.php
</edit file>

<search for>
* Software Version:           SMF 1.0.14                                          *
</search for>

<replace>
* Software Version:           SMF 1.0.17                                          *
</replace>


<search for>
		// If this is the theme_dir of the default theme, store it.
</search for>

<replace>
		// There are just things we shouldn't be able to change as members.
		if ($row['ID_MEMBER'] != 0 && in_array($row['variable'], array('actual_theme_url', 'actual_images_url', 'base_theme_dir', 'base_theme_url', 'default_images_url', 'default_theme_dir', 'default_theme_url', 'default_template', 'images_url', 'number_recent_posts', 'smiley_sets_default', 'theme_dir', 'theme_id', 'theme_layers', 'theme_templates', 'theme_url')))
			continue;

		// If this is the theme_dir of the default theme, store it.
</replace>



<edit file>
$sourcedir/PackageGet.php
</edit file>

<search for>
* Software Version:           SMF 1.0.16                                          *
</search for>

<replace>
* Software Version:           SMF 1.0.17                                          *
</replace>


<search for>
		$default_author = $listing->fetch('default-author');
</search for>

<replace>
		$default_author = htmlspecialchars($listing->fetch('default-author'));
</replace>


<search for>
			$default_title = $listing->fetch('default-website/@title');
</search for>

<replace>
			$default_title = htmlspecialchars($listing->fetch('default-website/@title'));
</replace>


<search for>
			if (in_array($package['type'], array('title', 'heading', 'text', 'rule')))
				$package['name'] = $thisPackage->fetch('.');
</search for>

<replace>
			if (in_array($package['type'], array('title', 'heading', 'text', 'rule')))
				$package['name'] = htmlspecialchars($thisPackage->fetch('.'));
</replace>


<search for>
				$package['name'] = $thisPackage->fetch('.');
				$package['link'] = '<a href="' . $package['href'] . '">' . $package['name'] . '</a>';
</search for>

<replace>
				$package['name'] = htmlspecialchars($thisPackage->fetch('.'));
				$package['link'] = '<a href="' . $package['href'] . '">' . $package['name'] . '</a>';
</replace>


<search for>
				if ($package['description'] == '')
					$package['description'] = $txt['pacman8'];
</search for>

<replace>
				if ($package['description'] == '')
					$package['description'] = $txt['pacman8'];
				else
					$package['description'] = parse_bbc(preg_replace('~\[[/]?html\]~i', '', htmlspecialchars($package['description'])));				
</replace>


<search for>
				$package['href'] = $url . '/' . $package['filename'];
</search for>

<replace>
				$package['href'] = $url . '/' . $package['filename'];
				$package['name'] = htmlspecialchars($package['name']);
</replace>


<search for>
						$package['author']['email'] = $thisPackage->fetch('author/@email');
</search for>

<replace>
						$package['author']['email'] = htmlspecialchars($thisPackage->fetch('author/@email'));
</replace>


<search for>
						$package['author']['name'] = $thisPackage->fetch('author');
</search for>

<replace>
						$package['author']['name'] = htmlspecialchars($thisPackage->fetch('author'));
</replace>


<search for>
						$package['author']['website']['name'] = $thisPackage->fetch('website/@title');
					elseif (isset($default_title))
						$package['author']['website']['name'] = $default_title;
					elseif ($thisPackage->exists('website'))
						$package['author']['website']['name'] = $thisPackage->fetch('website');
</search for>

<replace>
						$package['author']['website']['name'] = htmlspecialchars($thisPackage->fetch('website/@title'));
					elseif (isset($default_title))
						$package['author']['website']['name'] = $default_title;
					elseif ($thisPackage->exists('website'))
						$package['author']['website']['name'] = htmlspecialchars($thisPackage->fetch('website'));
</replace>







<edit file>
$sourcedir/Profile.php
</edit file>

<search for>
* Software Version:           SMF 1.0.14                                          *
</search for>

<replace>
* Software Version:           SMF 1.0.17                                          *
</replace>



<search for>
* Copyright 2006 by:          Simple Machines LLC (http://www.simplemachines.org) *
</search for>

<replace>
* Copyright 2006-2009 by:     Simple Machines LLC (http://www.simplemachines.org) *
</replace>



<search for>
	// These are the theme changes...
</search for>

<replace>
	$reservedVars = array(
		'actual_theme_url',
		'actual_images_url',
		'base_theme_dir',
		'base_theme_url',
		'default_images_url',
		'default_theme_dir',
		'default_theme_url',
		'default_template',
		'images_url',
		'number_recent_posts',
		'smiley_sets_default',
		'theme_dir',
		'theme_id',
		'theme_layers',
		'theme_templates',
		'theme_url',
	);

	// Can't change reserved vars.
	if ((isset($_POST['options']) && array_intersect(array_keys($_POST['options']), $reservedVars) != array()) || (isset($_POST['default_options']) && array_intersect(array_keys($_POST['default_options']), $reservedVars) != array()))
		fatal_lang_error(1);

	// These are the theme changes...
</replace>



<search for>
				$extensions = array(
</search for>

<replace>
				// Though not an exhaustive list, better safe than sorry.
				$fp = fopen($_FILES['attachment']['tmp_name'], 'rb');
				if (!$fp)
					fatal_lang_error('smf124');

				// Now try to find an infection.
				while (!feof($fp))
				{
					if (preg_match('~(iframe|\\<\\?php|\\<\\?\s|\\<%\s|html|eval|body|script)~', fgets($fp, 4096)) === 1)
					{
						if (file_exists($modSettings['attachmentUploadDir'] . '/avatar_tmp_' . $memID))
							@unlink($modSettings['attachmentUploadDir'] . '/avatar_tmp_' . $memID);

						fatal_lang_error('smf124');
					}
				}
				fclose($fp);

				$extensions = array(
</replace>


<edit file>
$sourcedir/Security.php
</edit file>

<search for>
* Software Version:           SMF 1.0.16                                          *
</search for>

<replace>
* Software Version:           SMF 1.0.17                                          *
</replace>


<search for>
	if (isset($_GET['confirm']) && isset($_SESSION['confirm_' . $action]) && md5($_GET['confirm'] . $_SERVER['HTTP_USER_AGENT']) !== $_SESSION['confirm_' . $action])
		return true;
		
	else
	{
		$token = md5(mt_rand() . session_id() . (string) microtime() . $modSettings['rand_seed']);
		$_SESSION['confirm_' . $action] = md5($token, $_SERVER['HTTP_USER_AGENT']);
</search for>

<replace>
	if (isset($_GET['confirm']) && isset($_SESSION['confirm_' . $action]) && md5($_GET['confirm'] . $_SERVER['HTTP_USER_AGENT']) == $_SESSION['confirm_' . $action])
		return true;
		
	else
	{
		$token = md5(mt_rand() . session_id() . (string) microtime() . $modSettings['rand_seed']);
		$_SESSION['confirm_' . $action] = md5($token . $_SERVER['HTTP_USER_AGENT']);
</replace>



<edit file>
$sourcedir/QueryString.php
</edit file>

<search for>
* Software Version:           SMF 1.0.15                                          *
</search for>

<replace>
* Software Version:           SMF 1.0.17                                          *
</replace>



<search for>
* Copyright 2006 by:          Simple Machines LLC (http://www.simplemachines.org) *
</search for>

<replace>
* Copyright 2006-2009 by:     Simple Machines LLC (http://www.simplemachines.org) *
</replace>



<search for>
	if (!empty($_SERVER['HTTP_CLIENT_IP']) && preg_match('~^((0|10|172\.16|192\.168|255|127\.0)\.|unknown)~', $_SERVER['HTTP_CLIENT_IP']) == 0)
</search for>

<replace>
	if (!empty($_SERVER['HTTP_CLIENT_IP']) && preg_match('~^((0|10|172\.(1[6-9]|2[0-9]|3[01])|192\.168|255|127)\.|unknown)~', $_SERVER['HTTP_CLIENT_IP']) == 0)
</replace>



<search for>
				if (preg_match('~^((0|10|172\.16|192\.168|255|127\.0)\.|unknown)~', $ip) != 0)
</search for>

<replace>
				if (preg_match('~^((0|10|172\.(1[6-9]|2[0-9]|3[01])|192\.168|255|127)\.|unknown)~', $ip) != 0)
</replace>



<search for>
		elseif (preg_match('~^((0|10|172\.16|192\.168|255|127\.0)\.|unknown)~', $_SERVER['HTTP_X_FORWARDED_FOR']) == 0)
</search for>

<replace>
		elseif (preg_match('~^((0|10|172\.(1[6-9]|2[0-9]|3[01])|192\.168|255|127)\.|unknown)~', $_SERVER['HTTP_X_FORWARDED_FOR']) == 0)
</replace>



<edit file>
$sourcedir/Subs.php
</edit file>

<search for>
* Software Version:           SMF 1.0.14                                          *
</search for>

<replace>
* Software Version:           SMF 1.0.17                                          *
</replace>



<search for>
* Copyright 2006 by:          Simple Machines LLC (http://www.simplemachines.org) *
</search for>

<replace>
* Copyright 2006-2009 by:     Simple Machines LLC (http://www.simplemachines.org) *
</replace>



<search for>
	echo '
		<span class="smalltext" style="display: inline; visibility: visible; font-family: Verdana, Arial, sans-serif;">';
</search for>

<replace>
	// Lewis Media no longer holds the copyright.
	$forum_copyright = str_replace(array('Lewis Media', 'href="http://www.lewismedia.com/"', '2001-'), array('Simple Machines LLC', 'href="http://www.simplemachines.org/about/copyright.php" title="Free Forum Software"', ''), $forum_copyright);

	echo '
		<span class="smalltext" style="display: inline; visibility: visible; font-family: Verdana, Arial, sans-serif;">';
</replace>



<search for>
			The administrator doesn\'t want a copyright notice saying this is copyright 2001-2005 by <a href="http://www.lewismedia.com/" target="_blank">Lewis Media</a>, and named <a href="http://www.simplemachines.org/">SMF</a>, so the forum will honor this request.';
</search for>

<replace>
			<div style="white-space: normal;">The administrator doesn\'t want a copyright notice saying this is copyright 2006 - 2009 by <a href="http://www.simplemachines.org/about/copyright.php" target="_blank">Simple Machines LLC</a>, and named <a href="http://www.simplemachines.org/">SMF</a>, so the forum will honor this request and be quiet.</div>';
</replace>



<search for>
	elseif ((strpos($forum_copyright, '<a href="http://www.simplemachines.org/" onclick="this.href += \'referer.php?forum=' . urlencode($context['forum_name'] . '|' . $boardurl . '|' . $forum_version) . '\';" target="_blank">SMF') !== false || strpos($forum_copyright, '<a href="http://www.simplemachines.org/" target="_blank">SMF') !== false || strpos($forum_copyright, '<a href="http://www.simplemachines.org/" title="Simple Machines Forum" target="_blank">SMF') !== false) && (strpos($forum_copyright, '<a href="http://www.lewismedia.com/">Lewis Media</a>') !== false || strpos($forum_copyright, '<a href="http://www.lewismedia.com/" target="_blank">Lewis Media</a>') !== false))
	{
		$found = true;
		echo $forum_copyright;
	}
</search for>

<replace>
	elseif (isset($modSettings['copyright_key']) && sha1($modSettings['copyright_key'] . 'banjo') == '1d01885ece7a9355bdeb22ed107f0ffa8c323026'){$found = true; return;}elseif ((strpos($forum_copyright, '<a href="http://www.simplemachines.org/" title="Simple Machines Forum" target="_blank">Powered by SMF') !== false || strpos($forum_copyright, '<a href="http://www.simplemachines.org/" onclick="this.href += \'referer.php?forum=' . urlencode($context['forum_name'] . '|' . $boardurl . '|' . $forum_version) . '\';" target="_blank">SMF') !== false || strpos($forum_copyright, '<a href="http://www.simplemachines.org/" target="_blank">SMF') !== false || strpos($forum_copyright, '<a href="http://www.simplemachines.org/" title="Simple Machines Forum" target="_blank">SMF') !== false)&&((strpos($forum_copyright, '<a href="http://www.simplemachines.org/about/copyright.php" title="Free Forum Software" target="_blank">SMF &copy;') !== false && (strpos($forum_copyright, 'Lewis Media</a>') !== false || strpos($forum_copyright, 'Simple Machines LLC</a>') !== false)) || strpos($forum_copyright, '<a href="http://www.lewismedia.com/">Lewis Media</a>') !== false || strpos($forum_copyright, '<a href="http://www.lewismedia.com/" target="_blank">Lewis Media</a>') !== false || (strpos($forum_copyright, '<a href="http://www.simplemachines.org/about/copyright.php"') !== false &&	strpos($forum_copyright, 'Simple Machines LLC') !== false))){$found = true; echo $forum_copyright;}
</replace>



<edit file>
$sourcedir/Register.php
</edit file>

<search for>
* Software Version:           SMF 1.0.14                                          *
</search for>

<replace>
* Software Version:           SMF 1.0.17                                          *
</replace>



<search for>
* Copyright 2006 by:          Simple Machines LLC (http://www.simplemachines.org) *
</search for>

<replace>
* Copyright 2006-2009 by:     Simple Machines LLC (http://www.simplemachines.org) *
</replace>



<search for>
	// Register them into the database.
	db_query("
		INSERT INTO {$db_prefix}members
</search for>

<replace>
	$reservedVars = array(
		'actual_theme_url',
		'actual_images_url',
		'base_theme_dir',
		'base_theme_url',
		'default_images_url',
		'default_theme_dir',
		'default_theme_url',
		'default_template',
		'images_url',
		'number_recent_posts',
		'smiley_sets_default',
		'theme_dir',
		'theme_id',
		'theme_layers',
		'theme_templates',
		'theme_url',
	);

	// Can't change reserved vars.
	if (array_intersect(array_keys($theme_vars), $reservedVars) != array())
		fatal_lang_error('theme3');

	// Register them into the database.
	db_query("
		INSERT INTO {$db_prefix}members
</replace>



<edit file>
$languagedir/index.english.php
</edit file>

<search for>
// Version: 1.0.1; index
</search for>

<replace>
// Version: 1.0.17; index
</replace>



<search for>
$forum_copyright = $context['forum_name'] . ' | Powered by <a href="http://www.simplemachines.org/" title="Simple Machines Forum" target="_blank">' . $forum_version . '</a>.<br />
&copy; 2001-2005, <a href="http://www.lewismedia.com/" target="_blank">Lewis Media</a>. All Rights Reserved.';
</search for>

<replace>
$forum_copyright = '<a href="http://www.simplemachines.org/" title="Simple Machines Forum" target="_blank">Powered by ' . $forum_version . '</a> | 
<a href="http://www.simplemachines.org/about/copyright.php" title="Free Forum Software" target="_blank">SMF &copy; 2006-2009, Simple Machines LLC</a>';
</replace>



<edit file>
$sourcedir/Display.php
</edit file>

<search for>
* Software Version:           SMF 1.0.12                                          *
</search for>

<replace>
* Software Version:           SMF 1.0.17                                          *
</replace>


<search for>
* Copyright 2006 by:          Simple Machines LLC (http://www.simplemachines.org) *
</search for>

<replace>
* Copyright 2006-2009 by:     Simple Machines LLC (http://www.simplemachines.org) *
</replace>


<search for>
	if (filesize($filename) != 0)
</search for>

<replace>
	// IE 6 just doesn't play nice. As dirty as this seems, it works.
	if ($context['browser']['is_ie6'] && isset($_REQUEST['image']))
		unset($_REQUEST['image']);

	elseif (filesize($filename) != 0)
</replace>


<search for>
				6 => 'bmp',
</search for>

<replace>
				6 => 'x-ms-bmp',
</replace>


<search for>
			if (!empty($size['mime']))
				header('Content-Type: ' . $size['mime']);
</search for>

<replace>
			if (!empty($size['mime']) && !in_array($size[2], array(4, 13)))
				header('Content-Type: ' . strtr($size['mime'], array('image/bmp' => 'image/x-ms-bmp')));
</replace>