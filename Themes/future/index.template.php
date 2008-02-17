<?php
// Version: 2.0 Beta 2; index

/*	This template is, perhaps, the most important template in the theme. It
	contains the main template layer that displays the header and footer of
	the forum, namely with main_above and main_below. It also contains the
	menu sub template, which appropriately displays the menu; the init sub
	template, which is there to set the theme up; (init can be missing.) and
	the linktree sub template, which sorts out the link tree.

	The init sub template should load any data and set any hardcoded options.

	The main_above sub template is what is shown above the main content, and
	should contain anything that should be shown up there.

	The main_below sub template, conversely, is shown after the main content.
	It should probably contain the copyright statement and some other things.

	The linktree sub template should display the link tree, using the data
	in the $context['linktree'] variable.

	The menu sub template should display all the relevant buttons the user
	wants and or needs.

	For more information on the templating system, please see the site at:
	http://www.simplemachines.org/
*/

// Initialize the template... mainly little settings.
function template_init()
{
	global $context, $settings, $options, $txt;

	/* Use images from default theme when using templates from the default theme?
		if this is 'always', images from the default theme will be used.
		if this is 'defaults', images from the default theme will only be used with default templates.
		if this is 'never' or isn't set at all, images from the default theme will not be used. */
	$settings['use_default_images'] = 'never';

	/* What document type definition is being used? (for font size and other issues.)
		'xhtml' for an XHTML 1.0 document type definition.
		'html' for an HTML 4.01 document type definition. */
	$settings['doctype'] = 'xhtml';

	/* The version this template/theme is for.
		This should probably be the version of SMF it was created for. */
	$settings['theme_version'] = '2.0 Beta 2';

	/* Set a setting that tells the theme that it can render the tabs. */
	$settings['use_tabs'] = true;

	/* Use plain buttons - as oppossed to text buttons? */
	$settings['use_buttons'] = true;

	/* Show sticky and lock status separate from topic icons? */
	$settings['separate_sticky_lock'] = true;

	/* Does this theme use the strict doctype? */
	$settings['strict_doctype'] = false;
}

// The main sub template above the content.
function template_html_above()
{
	global $context, $settings, $options, $scripturl, $txt, $modSettings;

	// Show right to left and the character set for ease of translating.
	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"', $context['right_to_left'] ? ' dir="rtl"' : '', '><head>
	<meta http-equiv="Content-Type" content="text/html; charset=', $context['character_set'], '" />
	<meta name="description" content="', $context['page_title'], '" />
	<meta name="keywords" content="PHP, MySQL, bulletin, board, free, open, source, smf, simple, machines, forum" />
	<script language="JavaScript" type="text/javascript" src="', $settings['default_theme_url'], '/scripts/script.js?rc2p"></script>
	<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
		var smf_theme_url = "', $settings['theme_url'], '";
		var smf_default_theme_url = "', $settings['default_theme_url'], '";
		var smf_images_url = "', $settings['images_url'], '";
		var smf_scripturl = "', $scripturl, '";
		var smf_iso_case_folding = ', $context['server']['iso_case_folding'] ? 'true' : 'false', ';
		var smf_charset = "', $context['character_set'], '";', $context['show_pm_popup'] ? '
		if (confirm("' . $txt['show_personal_messages'] . '"))
			window.open("' . $scripturl . '?action=pm");' : '', '
	// ]]></script>
	<title>', $context['page_title'], '</title>';

	// Please don't index these Mr Robot.
	if (!empty($context['robot_no_index']))
		echo '
	<meta name="robots" content="noindex" />';

	// The ?rc2 part of this link is just here to make sure browsers don't cache it wrongly.
	echo '
	<link rel="stylesheet" type="text/css" href="', $settings['theme_url'], '/classes.css?rc2" />
	<link rel="stylesheet" type="text/css" href="', $settings['theme_url'], '/core.css?rc2" />
	<link rel="stylesheet" type="text/css" href="', $settings['default_theme_url'], '/css/print.css?rc2" media="print" />';

	// load subtemplate stylesheet
	if(file_exists($settings['theme_dir']. '/css/' . $context['stylesheet'] . '.css'))
		echo '
	<link rel="stylesheet" type="text/css" href="', $settings['theme_url'], '/css/' . $context['stylesheet'] . '.css?rc2" />';
	else
		echo '
	<link rel="stylesheet" type="text/css" href="', $settings['default_theme_url'], '/css/' . $context['stylesheet'] . '.css?rc2" />';

	// IE7 needs some fixes for styles
	if ($context['browser']['is_ie7'])
		echo '
	<link rel="stylesheet" type="text/css" href="', $settings['theme_url'], '/style_ie7.css" />';
	// ..and IE6!
	elseif ($context['browser']['is_ie7'])
		echo '
	<link rel="stylesheet" type="text/css" href="', $settings['theme_url'], '/style_ie6.css" />';

	// Show all the relative links, such as help, search, contents, and the like.
	echo '
	<link rel="help" href="', $scripturl, '?action=help" />
	<link rel="search" href="' . $scripturl . '?action=search" />
	<link rel="contents" href="', $scripturl, '" />';

	// If RSS feeds are enabled, advertise the presence of one.
	if (!empty($modSettings['xmlnews_enable']))
		echo '
	<link rel="alternate" type="application/rss+xml" title="', $context['forum_name'], ' - RSS" href="', $scripturl, '?type=rss;action=.xml" />';

	// If we're viewing a topic, these should be the previous and next topics, respectively.
	if (!empty($context['current_topic']))
		echo '
	<link rel="prev" href="', $scripturl, '?topic=', $context['current_topic'], '.0;prev_next=prev" />
	<link rel="next" href="', $scripturl, '?topic=', $context['current_topic'], '.0;prev_next=next" />';

	// If we're in a board, or a topic for that matter, the index will be the board's index.
	if (!empty($context['current_board']))
		echo '
	<link rel="index" href="' . $scripturl . '?board=' . $context['current_board'] . '.0" />';

	// We'll have to use the cookie to remember the header...
	if ($context['user']['is_guest'])
		$options['collapse_header'] = !empty($_COOKIE['upshrink']);

	// Output any remaining HTML headers. (from mods, maybe?)
	echo $context['html_headers'], '
	<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
		// Create the main header object.
		var mainHeader = new smfToggle("upshrink", ', empty($options['collapse_header']) ? 'false' : 'true', ');
		mainHeader.useCookie(', $context['user']['is_guest'] ? 1 : 0, ');
		mainHeader.setOptions("collapse_header", "', $context['session_id'], '");
		mainHeader.addToggleImage("upshrink", "/upshrink.gif", "/upshrink2.gif");
		mainHeader.addTogglePanel("user_section");
		mainHeader.addTogglePanel("news_section");

		// And create the info center object.
		var infoHeader = new smfToggle("upshrinkIC", ', empty($options['collapse_header_ic']) ? 'false' : 'true', ');
		infoHeader.useCookie(', $context['user']['is_guest'] ? 1 : 0, ');
		infoHeader.setOptions("collapse_header_ic", "', $context['session_id'], '");
		infoHeader.addToggleImage("upshrink_ic", "/collapse.gif", "/expand.gif");
		infoHeader.addTogglePanel("upshrinkHeaderIC");
	// ]]></script>

</head>
<body>';
}

function template_body_above()
{
	global $context, $settings, $options, $scripturl, $txt, $modSettings;

	echo '
<div id="mainframe">
	<div class="tborder">
		<div class="catbg">
			<img class="floatright" id="smflogo" src="' , $settings['images_url'] , '/smflogo.gif" alt="Simple Machines Forum" />
			<h1>';

	if (empty($settings['header_logo_url']))
		echo $context['forum_name'];
	else
		echo '
				<img src="', $settings['header_logo_url'], '" alt="', $context['forum_name'], '" />';

	echo '
			</h1>
		</div>';

	// display user name and time
	echo '
		<div id="greeting_section" class="titlebg2">
			<div id="time" class="smalltext floatright">
				' , $context['current_time'], '
				<a href="#" onclick="mainHeader.toggle(); return false;"><img id="upshrink" src="', $settings['images_url'], '/', empty($options['collapse_header']) ? 'upshrink.gif' : 'upshrink2.gif', '" alt="*" title="', $txt['upshrink_description'], '" align="bottom" style="margin: 0 1ex;" /></a>
			</div>';

	if($context['user']['is_logged'])
		echo '
			<h2>' , $txt['hello_member_ndt'], ' <em>', $context['user']['name'] , '</em></h2>';
	else
		echo '
			<h2>' , $txt['hello_guest'] , $txt['guest'] , '</h2>';

	echo '
		</div>
		<div id="user_section" class="bordercolor"', empty($options['collapse_header']) ? '' : ' style="display: none;"', '>';
		
	if (!empty($context['user']['avatar']))
		echo '
			<p id="avatar">', $context['user']['avatar']['image'], '</p>';

	// If the user is logged in, display stuff like their name, new messages, etc.
	if ($context['user']['is_logged'])
	{
		echo '
			<ul class="windowbg2">
				<li><a href="', $scripturl, '?action=unread">', $txt['unread_since_visit'], '</a></li>
				<li><a href="', $scripturl, '?action=unreadreplies">', $txt['show_unread_replies'], '</a></li>';

		// Is the forum in maintenance mode?
		if ($context['in_maintenance'] && $context['user']['is_admin'])
			echo '
				<li class="notice">', $txt['maintain_mode_on'], '</li>';

		// Are there any members waiting for approval?
		if (!empty($context['unapproved_members']))
			echo '
				<li>', $context['unapproved_members'] == 1 ? $txt['approve_thereis'] : $txt['approve_thereare'], ' <a href="', $scripturl, '?action=admin;area=viewmembers;sa=browse;type=approve">', $context['unapproved_members'] == 1 ? $txt['approve_member'] : $context['unapproved_members'] . ' ' . $txt['approve_members'], '</a> ', $txt['approve_members_waiting'], '</li>';

		// Show the total time logged in?
		if (!empty($context['user']['total_time_logged_in']))
		{
			echo '
				<li>', $txt['totalTimeLogged1'];

			// If days is just zero, don't bother to show it.
			if ($context['user']['total_time_logged_in']['days'] > 0)
				echo $context['user']['total_time_logged_in']['days'] . $txt['totalTimeLogged2'];

			// Same with hours - only show it if it's above zero.
			if ($context['user']['total_time_logged_in']['hours'] > 0)
				echo $context['user']['total_time_logged_in']['hours'] . $txt['totalTimeLogged3'];

			// But, let's always show minutes - Time wasted here: 0 minutes ;).
			echo $context['user']['total_time_logged_in']['minutes'], $txt['totalTimeLogged4'], '
				</li>';
		}

		if (!empty($context['open_mod_reports']) && $context['show_open_reports'])
			echo '
				<li><a href="', $scripturl, '?action=moderate;area=reports">', sprintf($txt['mod_reports_waiting'], $context['open_mod_reports']), '</a></li>';
		echo '
			</ul>';
	}
	// Otherwise they're a guest - this time ask them to either register or login - lazy bums...
	else
	{
		echo '
			<script language="JavaScript" type="text/javascript" src="', $settings['default_theme_url'], '/scripts/sha1.js"></script>
			<form class="windowbg" id="guest_form" action="', $scripturl, '?action=login2" method="post" accept-charset="', $context['character_set'], '" ', empty($context['disable_login_hashing']) ? ' onsubmit="hashLoginPassword(this, \'' . $context['session_id'] . '\');"' : '', '>
				' , sprintf($txt['welcome_guest'], $txt['guest_title']) , '<br />
				<input type="text" name="user" size="10" />
				<input type="password" name="passwrd" size="10" />
				<select name="cookielength">
					<option value="60">', $txt['one_hour'], '</option>
					<option value="1440">', $txt['one_day'], '</option>
					<option value="10080">', $txt['one_week'], '</option>
					<option value="43200">', $txt['one_month'], '</option>
					<option value="-1" selected="selected">', $txt['forever'], '</option>
				</select>
				<input type="submit" value="', $txt['login'], '" /><br />
				', $txt['quick_login_dec'];

		if (!empty($modSettings['enableOpenID']))
			echo'
				<br />
				<input type="text" name="openid_url" id="openid_url" size="25" class="openid_login" />';

		echo '
				<input type="hidden" name="hash_passwrd" value="" />
			</form>';
	}

	echo '
		</div>
		<div id="news_section" class="titlebg2 clearfix"', empty($options['collapse_header']) ? '' : ' style="display: none;"', '>
			<form class="floatright" id="search_form" action="', $scripturl, '?action=search2" method="post" accept-charset="', $context['character_set'], '">
				<a href="', $scripturl, '?action=search;advanced">
					<img src="'.$settings['images_url'].'/filter.gif" align="middle" style="margin: 0 1ex;" alt="" />
				</a>
				<input type="text" name="search" value="" style="width: 190px;" />&nbsp;
				<input type="submit" name="submit" value="', $txt['search'], '" style="width: 11ex;" />
				<input type="hidden" name="advanced" value="0" />';

	// Search within current topic?
	if (!empty($context['current_topic']))
		echo '
				<input type="hidden" name="topic" value="', $context['current_topic'], '" />';
		// If we're on a certain board, limit it to this board ;).
	elseif (!empty($context['current_board']))
		echo '
				<input type="hidden" name="brd[', $context['current_board'], ']" value="', $context['current_board'], '" />';

	echo '
			</form>';

	// Show a random news item? (or you could pick one from news_lines...)
	if (!empty($settings['enable_news']))
		echo '
			<div id="random_news"><h3>', $txt['news'], ':</h3> ', $context['random_news_line'], '</div>';
	
	echo '
		</div>
	</div>';

	// Show the menu here, according to the menu sub template.
	template_menu();

	// show the navigation tree
	theme_linktree();

	// The main content should go here.
	echo '
	<div id="bodyarea">';
}

function template_body_below()
{
	global $context, $settings, $options, $scripturl, $txt;

	echo '
	</div>';

	// Show the "Powered by" and "Valid" logos, as well as the copyright. Remember, the copyright must be somewhere!
	echo '
	<div id="footerarea" class="headerpadding topmargin">
		<ul class="horizlist smalltext">
			<li>', theme_copyright(), '</li>
			<li><a href="http://validator.w3.org/check/referer" target="_blank"> | XHTML</a></li>
			<li><a href="http://jigsaw.w3.org/css-validator/check/referer" target="_blank"> | CSS</a></li>
			<li><a href="' , $scripturl , '?action=.xml"> | RSS</a></li>
		</ul>';

		// Show the load time?
	if ($context['show_load_time'])
		echo '
		<p class="smalltext">', $txt['page_created'], $context['load_time'], $txt['seconds_with'], $context['load_queries'], $txt['queries'], '</p>';

	echo '
	</div>
</div>';
}

function template_html_below()
{
	global $context, $settings, $options, $scripturl, $txt, $modSettings;

	echo '<pre>styleshee: ' , $context['stylesheet'] , '</pre>';
	// The following will be used to let the user know that some AJAX process is running
	echo '
<div id="ajax_in_progress" style="display: none;', $context['browser']['is_ie'] && !$context['browser']['is_ie7'] ? 'position: absolute;' : '', '"><a href="javascript:ajax_indicator(false);"><img src="', $settings['images_url'], '/icons/quick_remove.gif" alt="', $txt['modify_cancel'], '" /></a>', $txt['ajax_in_progress'], '</div>
</body></html>';
}

// Show a linktree. This is that thing that shows "My Community | General Category | General Discussion"..
function theme_linktree()
{
	global $context, $settings, $options;

	// if linktree is empty, just return
	if(empty($context['linktree']))
		return;

	echo '
	<ul id="linktree">';

	// Each tree item has a URL and name. Some may have extra_before and extra_after.
	foreach ($context['linktree'] as $link_num => $tree)
	{
		echo '
		<li' , ($link_num == count($context['linktree']) - 1) ? ' class="last"' : '' , '>';
		// Show something before the link?
		if (isset($tree['extra_before']))
			echo $tree['extra_before'];

		// Show the link, including a URL if it should have one.
		echo $settings['linktree_link'] && isset($tree['url']) ? '
				<a href="' . $tree['url'] . '">' . $tree['name'] . '</a>' : $tree['name'];

		// Show something after the link...?
		if (isset($tree['extra_after']))
			echo $tree['extra_after'];
		echo '
		</li>';
	}
	echo '
	</ul>';
}

// Show the menu up top. Something like [home] [help] [profile] [logout]...
function template_menu()
{
	global $context, $settings, $options, $scripturl, $txt;

	echo '
	<div id="main_menu">
		<ul class="clearfix">';
	
	foreach($context['menu_buttons'] AS $act => $button)
		echo '
			<li id="button' , $act[0] , '">
				<a' , $button['active_button'] ? ' class="active"' : '' , ' href="' , $button['href'] , '">
					<span' , isset($button['is_last']) ? ' class="last"' : '' , '>' , $button['active_button'] ? '<em>' : '' ,  $button['title'] , array_keys($act) , $button['active_button'] ? '</em>' : '' , '</span>
				</a>
			</li>';

	echo '
		</ul>
	</div>';
}

// Make the button lists.
function template_buttonlist($buttons, $top = true)
{
	global $settings, $context, $txt, $scripturl;

	// Create the buttons...
	foreach ($buttons as $key => $value)
	{
		if (isset($value['test']) && empty($context[$value['test']]))
		{
			unset($buttons[$key]);
			continue;
		}
		elseif (!isset($buttonlist[$key]))
		{
				// do we want to use old buttons or simply texts? if tabs are off.
				if($settings['use_buttons'] && !$settings['use_tabs'])
					$buttonlist[$key] = '<a href="' . $value['url'] . '"' . (!empty($value['sctive']) ? ' class="active"' : '') . '><img src="' . $settings['images_url'] . '/' . $context['user']['language'] . '/' . $value['image'] . '" alt="' . $txt[$value['text']] . '" /></a>';
				// we will use texts anyway!
				else
					$buttonlist[$key] = '<a href="' . $value['url'] . '"' . (!empty($value['active']) ? ' class="active"' : '') . '><span' . (($key == count($buttons) - 1) ? ' class="last"' : '') . '>' . (!empty($value['active']) ? '<em>' : '') . $txt[$value['text']] . (!empty($value['active']) ? '</em>' : '') . '</span></a>';
		}
		$buttons[$key] = $buttonlist[$key];
	}
	// empty list, so just return.
	if (empty($buttons))
		return ;

	if($settings['use_tabs'])
		echo '
		<div class="buttonlist' , !$top ? '_bottom' : '' , '">
			<ul class="clearfix">
				<li>', implode('</li><li>', $buttons) , '</li>
			</ul>
		</div>';
	else
		echo '
		<div class="oldbuttonlist">', implode('&nbsp;', $buttons) , '</div>';
}
?>