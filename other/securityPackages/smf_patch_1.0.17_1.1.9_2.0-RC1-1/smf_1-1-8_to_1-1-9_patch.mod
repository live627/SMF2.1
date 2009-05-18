<edit file>
$boarddir/index.php
</edit file>

<search for>
* Software Version:           SMF 1.1.8                                           *
</search for>

<replace>
* Software Version:           SMF 1.1.9                                           *
</replace>


<search for>
$forum_version = 'SMF 1.1.8';
</search for>

<replace>
$forum_version = 'SMF 1.1.9';
</replace>


<search for>
	elseif (empty($modSettings['allow_guestAccess']) && $user_info['is_guest'] && (!isset($_REQUEST['action']) || !in_array($_REQUEST['action'], array('coppa', 'login', 'login2', 'register', 'register2', 'reminder', 'activate', 'smstats', 'help', '.xml', 'verificationcode'))))
</search for>

<replace>
	elseif (empty($modSettings['allow_guestAccess']) && $user_info['is_guest'] && (!isset($_REQUEST['action']) || !in_array($_REQUEST['action'], array('coppa', 'login', 'login2', 'register', 'register2', 'reminder', 'activate', 'smstats', 'help', 'verificationcode'))))
</replace>



<edit file>
$sourcedir/Display.php
</edit file>

<search for>
* Software Version:           SMF 1.1.4                                           *
</search for>

<replace>
* Software Version:           SMF 1.1.9                                           *
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



<edit file>
$sourcedir/Load.php
</edit file>

<search for>
* Software Version:           SMF 1.1.6                                           *
</search for>

<replace>
* Software Version:           SMF 1.1.9                                           *
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
* Software Version:           SMF 1.1.8                                           *
</search for>

<replace>
* Software Version:           SMF 1.1.9                                           *
</replace>


<search for>
* Copyright 2006 by:          Simple Machines LLC (http://www.simplemachines.org) *
</search for>

<replace>
* Copyright 2006-2009 by:     Simple Machines LLC (http://www.simplemachines.org) *
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
* Software Version:           SMF 1.1.6                                           *
</search for>

<replace>
* Software Version:           SMF 1.1.9                                           *
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
					if (preg_match('~(iframe|\\<\\?php|\\<\\?[\s=]|\\<%[\s=]|html|eval|body|script\W)~', fgets($fp, 4096)) === 1)
					{
						if (file_exists($uploadDir . '/avatar_tmp_' . $memID))
							@unlink($uploadDir . '/avatar_tmp_' . $memID);

						fatal_lang_error('smf124');
					}
				}
				fclose($fp);

				$extensions = array(
</replace>


<search for>
		$context['activate_message'] = isset($txt['account_activate_method_' . $context['member']['is_activated'] % 10]) ? $txt['account_activate_method_' . $context['member']['is_activated']] : $txt['account_not_activated'];
</search for>

<replace>
		$context['activate_message'] = isset($txt['account_activate_method_' . $context['member']['is_activated'] % 10]) ? $txt['account_activate_method_' . $context['member']['is_activated'] % 10] : $txt['account_not_activated'];
</replace>


<edit file>
$sourcedir/Security.php
</edit file>

<search for>
* Software Version:           SMF 1.1.8                                           *
</search for>

<replace>
* Software Version:           SMF 1.1.9                                           *
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
* Software Version:           SMF 1.1.7                                           *
</search for>

<replace>
* Software Version:           SMF 1.1.9                                           *
</replace>


<search for>
* Copyright 2006 by:          Simple Machines LLC (http://www.simplemachines.org) *
</search for>

<replace>
* Copyright 2006-2009 by:     Simple Machines LLC (http://www.simplemachines.org) *
</replace>


<search for>
	if (!empty($_SERVER['HTTP_X_FORWARDED_FOR']) && !empty($_SERVER['HTTP_CLIENT_IP']) && (preg_match('~^((0|10|172\.16|192\.168|255|127\.0)\.|unknown)~', $_SERVER['HTTP_CLIENT_IP']) == 0 || preg_match('~^((0|10|172\.16|192\.168|255|127\.0)\.|unknown)~', $_SERVER['REMOTE_ADDR']) != 0))
	{
		// We have both forwarded for AND client IP... check the first forwarded for as the block - only switch if it's better that way.
		if (strtok($_SERVER['HTTP_X_FORWARDED_FOR'], '.') != strtok($_SERVER['HTTP_CLIENT_IP'], '.') && '.' . strtok($_SERVER['HTTP_X_FORWARDED_FOR'], '.') == strrchr($_SERVER['HTTP_CLIENT_IP'], '.') && (preg_match('~^((0|10|172\.16|192\.168|255|127\.0)\.|unknown)~', $_SERVER['HTTP_X_FORWARDED_FOR']) == 0 || preg_match('~^((0|10|172\.16|192\.168|255|127\.0)\.|unknown)~', $_SERVER['REMOTE_ADDR']) != 0))
</search for>

<replace>
	if (!empty($_SERVER['HTTP_X_FORWARDED_FOR']) && !empty($_SERVER['HTTP_CLIENT_IP']) && (preg_match('~^((0|10|172\.(1[6-9]|2[0-9]|3[01])|192\.168|255|127)\.|unknown)~', $_SERVER['HTTP_CLIENT_IP']) == 0 || preg_match('~^((0|10|172\.(1[6-9]|2[0-9]|3[01])|192\.168|255|127)\.|unknown)~', $_SERVER['REMOTE_ADDR']) != 0))
	{
		// We have both forwarded for AND client IP... check the first forwarded for as the block - only switch if it's better that way.
		if (strtok($_SERVER['HTTP_X_FORWARDED_FOR'], '.') != strtok($_SERVER['HTTP_CLIENT_IP'], '.') && '.' . strtok($_SERVER['HTTP_X_FORWARDED_FOR'], '.') == strrchr($_SERVER['HTTP_CLIENT_IP'], '.') && (preg_match('~^((0|10|172\.(1[6-9]|2[0-9]|3[01])|192\.168|255|127)\.|unknown)~', $_SERVER['HTTP_X_FORWARDED_FOR']) == 0 || preg_match('~^((0|10|172\.(1[6-9]|2[0-9]|3[01])|192\.168|255|127)\.|unknown)~', $_SERVER['REMOTE_ADDR']) != 0))
</replace>


<search for>
	if (!empty($_SERVER['HTTP_CLIENT_IP']) && (preg_match('~^((0|10|172\.16|192\.168|255|127\.0)\.|unknown)~', $_SERVER['HTTP_CLIENT_IP']) == 0 || preg_match('~^((0|10|172\.16|192\.168|255|127\.0)\.|unknown)~', $_SERVER['REMOTE_ADDR']) != 0))
</search for>

<replace>
	if (!empty($_SERVER['HTTP_CLIENT_IP']) && (preg_match('~^((0|10|172\.(1[6-9]|2[0-9]|3[01])|192\.168|255|127)\.|unknown)~', $_SERVER['HTTP_CLIENT_IP']) == 0 || preg_match('~^((0|10|172\.(1[6-9]|2[0-9]|3[01])|192\.168|255|127)\.|unknown)~', $_SERVER['REMOTE_ADDR']) != 0))
</replace>


<search for>
				if (preg_match('~^((0|10|172\.16|192\.168|255|127\.0)\.|unknown)~', $ip) != 0 && preg_match('~^((0|10|172\.16|192\.168|255|127\.0)\.|unknown)~', $_SERVER['REMOTE_ADDR']) == 0)
</search for>

<replace>
				if (preg_match('~^((0|10|172\.(1[6-9]|2[0-9]|3[01])|192\.168|255|127)\.|unknown)~', $ip) != 0 && preg_match('~^((0|10|172\.(1[6-9]|2[0-9]|3[01])|192\.168|255|127)\.|unknown)~', $_SERVER['REMOTE_ADDR']) == 0)
</replace>


<search for>
		elseif (preg_match('~^((0|10|172\.16|192\.168|255|127\.0)\.|unknown)~', $_SERVER['HTTP_X_FORWARDED_FOR']) == 0 || preg_match('~^((0|10|172\.16|192\.168|255|127\.0)\.|unknown)~', $_SERVER['REMOTE_ADDR']) != 0)
</search for>

<replace>
		elseif (preg_match('~^((0|10|172\.(1[6-9]|2[0-9]|3[01])|192\.168|255|127)\.|unknown)~', $_SERVER['HTTP_X_FORWARDED_FOR']) == 0 || preg_match('~^((0|10|172\.(1[6-9]|2[0-9]|3[01])|192\.168|255|127)\.|unknown)~', $_SERVER['REMOTE_ADDR']) != 0)
</replace>



<edit file>
$sourcedir/Subs.php
</edit file>

<search for>
* Software Version:           SMF 1.1.6                                           *
</search for>

<replace>
* Software Version:           SMF 1.1.9                                           *
</replace>


<search for>
			<div style="white-space: normal;">The administrator doesn\'t want a copyright notice saying this is copyright 2006 - 2007 by <a href="http://www.simplemachines.org/about/copyright.php" target="_blank">Simple Machines LLC</a>, and named <a href="http://www.simplemachines.org/">SMF</a>, so the forum will honor this request and be quiet.</div>';
</search for>

<replace>
			<div style="white-space: normal;">The administrator doesn\'t want a copyright notice saying this is copyright 2006 - 2009 by <a href="http://www.simplemachines.org/about/copyright.php" target="_blank">Simple Machines LLC</a>, and named <a href="http://www.simplemachines.org/">SMF</a>, so the forum will honor this request and be quiet.</div>';
</replace>



<edit file>
$sourcedir/Subs-Graphics.php
</edit file>

<search for>
* Software Version:           SMF 1.1.7                                           *
</search for>

<replace>
* Software Version:           SMF 1.1.9                                           *
</replace>


<search for>
	$code_image = imagecreate($total_width, $max_height);
</search for>

<replace>
	$code_image = $gd2 ? imagecreatetruecolor($total_width, $max_height) : imagecreate($total_width, $max_height);
</replace>



<edit file>
$sourcedir/Subs-Members.php
</edit file>

<search for>
* Software Version:           SMF 1.1.6                                           *
</search for>

<replace>
* Software Version:           SMF 1.1.9                                           *
</replace>


<search for>
	// Some of these might be overwritten. (the lower ones that are in the arrays below.)
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
	if (isset($regOptions['theme_vars']) && array_intersect(array_keys($regOptions['theme_vars']), $reservedVars) != array())
		fatal_lang_error('theme3');

	// Some of these might be overwritten. (the lower ones that are in the arrays below.)
</replace>



<edit file>
$sourcedir/Subs-Post.php
</edit file>

<search for>
* Software Version:           SMF 1.1.8                                           *
</search for>

<replace>
* Software Version:           SMF 1.1.9                                           *
</replace>


<search for>
			$parts[$i] = preg_replace('~\[([/]?)(list|li|table|tr|td)([^\]]*)\]~ie', '\'[$1\' . strtolower(\'$2\') . \'$3]\'', $parts[$i]);
</search for>

<replace>
			$parts[$i] = preg_replace('~\[([/]?)(list|li|table|tr|td)((\s[^\]]+)*)\]~ie', '\'[$1\' . strtolower(\'$2\') . \'$3]\'', $parts[$i]);
</replace>


<search for>
	// Change breaks back to \n's.
	return preg_replace('~<br( /)?' . '>~', "\n", implode('', $parts));
</search for>

<replace>
	// Change breaks back to \n's and &nsbp; back to spaces.
	return preg_replace('~<br( /)?' . '>~', "\n", str_replace('&nbsp;', ' ', implode('', $parts)));
</replace>



<edit file>
$themedir/Recent.template.php
</edit file>

<search for>
// Version: 1.1.5; Recent
</search for>

<replace>
// Version: 1.1.9; Recent
</replace>


<search for>
			$button_set['delete'] = array('text' => 31, 'image' => 'delete.gif', 'lang' => true, 'custom' => 'onclick="return confirm(\'' . $txt[154] . '?\');"', 'url' => $scripturl . '?action=deletemsg2;msg=' . $post['id'] . ';topic=' . $post['topic'] . ';recent;sesc=' . $context['session_id']);
</search for>

<replace>
			$button_set['delete'] = array('text' => 31, 'image' => 'delete.gif', 'lang' => true, 'custom' => 'onclick="return confirm(\'' . $txt[154] . '?\');"', 'url' => $scripturl . '?action=deletemsg;msg=' . $post['id'] . ';topic=' . $post['topic'] . ';recent;sesc=' . $context['session_id']);
</replace>