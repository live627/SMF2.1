<?php
// Version: 2.0 RC1; index

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
	$settings['theme_version'] = '2.0 RC1';

	/* Set a setting that tells the theme that it can render the tabs. */
	$settings['use_tabs'] = true;

	/* Use plain buttons - as oppossed to text buttons? */
	$settings['use_buttons'] = true;

	/* Show sticky and lock status separate from topic icons? */
	$settings['separate_sticky_lock'] = true;

	/* Does this theme use the strict doctype? */
	$settings['strict_doctype'] = false;

	/* Does this theme use post previews on the message index? */
	$settings['message_index_preview'] = false;
	
	/* Set the following variable to true if this theme requires the optional theme strings file to be loaded. */
	$settings['require_theme_strings'] = false;
}

// The main sub template above the content.
function template_html_above()
{
	global $context, $settings, $options, $scripturl, $txt, $modSettings;

	// Show right to left and the character set for ease of translating.
	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"', $context['right_to_left'] ? ' dir="rtl"' : '', '><head>
	<meta http-equiv="Content-Type" content="text/html; charset=', $context['character_set'], '" />
	<meta name="description" content="', $context['page_title_html_safe'], '" />
	<meta name="keywords" content="', $context['meta_keywords'], '" />
	<script language="JavaScript" type="text/javascript" src="', $settings['default_theme_url'], '/scripts/script.js?b4"></script>
	<script language="JavaScript" type="text/javascript" src="', $settings['default_theme_url'], '/scripts/theme.js?b4"></script>
	<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
		var smf_theme_url = "', $settings['theme_url'], '";
		var smf_default_theme_url = "', $settings['default_theme_url'], '";
		var smf_images_url = "', $settings['images_url'], '";
		var smf_scripturl = "', $scripturl, '";
		var smf_iso_case_folding = ', $context['server']['iso_case_folding'] ? 'true' : 'false', ';
		var smf_charset = "', $context['character_set'], '";', $context['show_pm_popup'] ? '
		if (confirm("' . $txt['show_personal_messages'] . '"))
			window.open("' . $scripturl . '?action=pm");' : '', '
		var ajax_notification_text = "', $txt['ajax_in_progress'], '";
		var ajax_notification_cancel_text = "', $txt['modify_cancel'], '";
	// ]]></script>
	<title>', $context['page_title_html_safe'], '</title>';

	// Please don't index these Mr Robot.
	if (!empty($context['robot_no_index']))
		echo '
	<meta name="robots" content="noindex" />';

	// The ?b4 part of this link is just here to make sure browsers don't cache it wrongly.
	echo '
	<link rel="stylesheet" type="text/css" href="', $settings['theme_url'], '/style', $context['theme_variant'], '.css?b4" />';

	echo '
	<link rel="stylesheet" type="text/css" href="', $settings['default_theme_url'], '/css/print.css?b4" media="print" />';

	// IE7 needs some fixes for styles.
	if ($context['browser']['is_ie7'])
		echo '
	<link rel="stylesheet" type="text/css" href="', $settings['theme_url'], '/css/ie7.css" />';
	// ..and IE6!
	elseif ($context['browser']['is_ie6'])
		echo '
	<link rel="stylesheet" type="text/css" href="', $settings['theme_url'], '/css/ie6.css" />';
	// Firefox - all versions - too!
	elseif ($context['browser']['is_firefox'])
		echo '
	<link rel="stylesheet" type="text/css" href="', $settings['default_theme_url'], '/css/ff.css" />';

	// Show all the relative links, such as help, search, contents, and the like.
	echo '
	<link rel="help" href="', $scripturl, '?action=help" />
	<link rel="search" href="' . $scripturl . '?action=search" />
	<link rel="contents" href="', $scripturl, '" />';

	// If RSS feeds are enabled, advertise the presence of one.
	if (!empty($modSettings['xmlnews_enable']))
		echo '
	<link rel="alternate" type="application/rss+xml" title="', $context['forum_name_html_safe'], ' - RSS" href="', $scripturl, '?type=rss;action=.xml" />';

	// If we're viewing a topic, these should be the previous and next topics, respectively.
	if (!empty($context['current_topic']))
		echo '
	<link rel="prev" href="', $scripturl, '?topic=', $context['current_topic'], '.0;prev_next=prev" />
	<link rel="next" href="', $scripturl, '?topic=', $context['current_topic'], '.0;prev_next=next" />';

	// If we're in a board, or a topic for that matter, the index will be the board's index.
	if (!empty($context['current_board']))
		echo '
	<link rel="index" href="', $scripturl, '?board=', $context['current_board'], '.0" />';

	// the alternative fontsizes 
	echo '
	<link rel="alternate stylesheet" type="text/css" media="screen" title="mediumtext" href="', $settings['theme_url'], '/css/mediumtext.css" />
	<link rel="alternate stylesheet" type="text/css" media="screen" title="bigtext" href="', $settings['theme_url'], '/css/bigtext.css" />';

	// We'll have to use the cookie to remember the header...
	if ($context['user']['is_guest'])
	{
		$options['collapse_header'] = !empty($_COOKIE['upshrink']);
		$options['collapse_header_ic'] = !empty($_COOKIE['upshrinkIC']);
	}

	// Output any remaining HTML headers. (from mods, maybe?)
	echo $context['html_headers'], '
	<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
		// Create the main header object.
		var mainHeader = new smfToggle("upshrink", ', empty($options['collapse_header']) ? 'false' : 'true', ');
		mainHeader.useCookie(', $context['user']['is_guest'] ? 1 : 0, ');
		mainHeader.setOptions("collapse_header", "', $context['session_id'], '");
		mainHeader.addToggleImage("upshrink", "/upshrink.png", "/upshrink2.png");
		mainHeader.addTogglePanel("upper_section");
	// ]]></script>';
	
	// the styleswitcher script (replace with internal function?)
	echo '
	<script type="text/javascript" 	src="', $settings['theme_url'], '/scripts/styleswitcher.js"></script>';
	
	echo '
</head>
<body>';
}

function template_body_above()
{
	global $context, $settings, $options, $scripturl, $txt, $modSettings;

	// the font-size changer
	echo '
	<p id="fontsize_panel">
		<a href="javascript:chooseStyle(\'none\', 1660)"><img src="', $settings['images_url'], '/theme/font_default.png" alt="*" /></a>
		<a href="javascript:chooseStyle(\'mediumtext\', 1660)"><img src="', $settings['images_url'], '/theme/font_medium.png" alt="*" /></a>
		<a href="javascript:chooseStyle(\'bigtext\', 1660)"><img src="', $settings['images_url'], '/theme/font_big.png" alt="*" /></a>
	</p>';
	
	echo '
	<div id="header"><div class="frame">';

	echo '
		<h1 class="forumtitle"><a href="', $scripturl, '">
			', empty($settings['header_logo_url']) ? $context['forum_name'] : '<img src="' . $settings['header_logo_url'] . '" alt="' . $context['forum_name'] . '" />' , '
		</a></h1>
		', empty($settings['site_slogan']) ? '<img id="smflogo" src="' . $settings['images_url'] . '/smflogo.' . ($context['browser']['is_ie6'] ? 'gif' : 'png') . '" alt="Simple Machines Forum" title="Simple Machines Forum" />' : '<h2 class="sitelogo">' . $settings['site_slogan'] . '</h2>', ' 
		<a href="#" onclick="mainHeader.toggle(); return false;">
			<img id="upshrink" src="', $settings['images_url'], '/', empty($options['collapse_header']) ? 'upshrink.png' : 'upshrink2.png', '" alt="*" title="', $txt['upshrink_description'], '" />
		</a>
		<ul id="upper_section" class="middletext"', empty($options['collapse_header']) ? '' : ' style="display: none;"', '>
			<li class="user">';

		// If the user is logged in, display stuff like their name, new messages, etc.
		if ($context['user']['is_logged'])
		{
			if (!empty($context['user']['avatar']))
				echo '
				<p class="avatar">', $context['user']['avatar']['image'], '</p>';

			echo '
				<ul>
					<li class="greeting">', $txt['hello_member_ndt'], ' <span>', $context['user']['name'], '</span></li>
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

			if (!empty($context['open_mod_reports']) && $context['show_open_reports'])
				echo '				
					<li><a href="', $scripturl, '?action=moderate;area=reports">', sprintf($txt['mod_reports_waiting'], $context['open_mod_reports']), '</a></li>';
		
			echo '
					<li>' , $context['current_time'], '</li>
				</ul>';
		}
		// Otherwise they're a guest - this time ask them to either register or login - lazy bums...
		else
		{
			echo '
				<script language="JavaScript" type="text/javascript" src="', $settings['default_theme_url'], '/scripts/sha1.js"></script>
				<form id="guest_form" action="', $scripturl, '?action=login2" method="post" accept-charset="', $context['character_set'], '" ', empty($context['disable_login_hashing']) ? ' onsubmit="hashLoginPassword(this, \'' . $context['session_id'] . '\');"' : '', '>
					<div class="info">' , $txt['login_or_register'], '</div>
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
					<div class="info">', $txt['quick_login_dec'], '</div>';

			if (!empty($modSettings['enableOpenID']))
				echo'
					<br /><input type="text" name="openid_url" id="openid_url" size="25" class="openid_login" />';

			echo '
					<input type="hidden" name="hash_passwrd" value="" />
				</form>';
		}
	
		echo '
			</li>
			<li class="news normaltext">';
	
		// Show a random news item? (or you could pick one from news_lines...)
		if (!empty($settings['enable_news']))
			echo '
				<h3>', $txt['news'], ': </h3>
				<p>', $context['random_news_line'], '</p>';
	
		echo '
				<form id="search_form" style="margin: 0;" action="', $scripturl, '?action=search2" method="post" accept-charset="', $context['character_set'], '">
					<input type="text" name="search" value="" />&nbsp;
					<input type="submit" name="submit" value="', $txt['search'], '" />
					<input type="hidden" name="advanced" value="0" />';

		// Search within current topic?
		if (!empty($context['current_topic']))
			echo '
					<input type="hidden" name="topic" value="', $context['current_topic'], '" />';
			// If we're on a certain board, limit it to this board ;).
		elseif (!empty($context['current_board']))
			echo '
					<input type="hidden" name="brd[', $context['current_board'], ']" value="', $context['current_board'], '" />';

		echo '</form>
			</li>
		</ul>';
	
		// Show the menu here, according to the menu sub template.
		template_menu();

		// Show the navigation tree.
		theme_linktree();
	
		echo '
	</div></div>';

	// The main content should go here.
	echo '
	<div id="content_section"><div class="frame">';
}

function template_body_below()
{
	global $context, $settings, $options, $scripturl, $txt;

	echo '
	</div></div>';

	// Show the "Powered by" and "Valid" logos, as well as the copyright. Remember, the copyright must be somewhere!
	echo '
	<div id="footer_section"><div class="frame">
		<ul>
			<li class="copyright">', theme_copyright(), '</li>
			<li><a id="button_xhtml" href="http://validator.w3.org/check/referer" target="_blank" class="new_win" title="', $txt['valid_html'], '"><span>XHTML</span></a></li>
			<li><a id="button_rss" href="', $scripturl , '?action=.xml" class="new_win"><span>RSS</span></a></li>
			<li class="last"><a id="button_wap2" href="', $scripturl , '?wap2" class="new_win"><span>WAP2</span></a></li>
		</ul>';

	// Show the load time?
	if ($context['show_load_time'])
		echo '
		<p>', $txt['page_created'], $context['load_time'], $txt['seconds_with'], $context['load_queries'], $txt['queries'], '</p>';

	echo '
	</div></div>';
}

function template_html_below()
{
	global $context, $settings, $options, $scripturl, $txt, $modSettings;

	echo '
</body></html>';
}

// Show a linktree. This is that thing that shows "My Community | General Category | General Discussion"..
function theme_linktree($force_show = false)
{
	global $context, $settings, $options, $shown_linktree;

	// If linktree is empty, just return - also allow an override.
	if (empty($context['linktree']) || (!empty($context['dont_default_linktree']) && !$force_show))
		return;

	//!!! Temporarily don't do it twice.
	if (!empty($shown_linktree))
		return;
	$shown_linktree = true;

	echo '
		<ul id="navigation">';

	// Each tree item has a URL and name. Some may have extra_before and extra_after.
	foreach ($context['linktree'] as $link_num => $tree)
	{
		echo '
			<li', ($link_num == count($context['linktree']) - 1) ? ' class="last"' : '', '>';
		// Show something before the link?
		if (isset($tree['extra_before']))
			echo $tree['extra_before'];

		// Show the link, including a URL if it should have one.
		echo $settings['linktree_link'] && isset($tree['url']) ? '
				<a href="' . $tree['url'] . '"><span>' . $tree['name'] . '</span></a>' : '<span>' . $tree['name'] .'</span>';

		// Show something after the link...?
		if (isset($tree['extra_after']))
			echo $tree['extra_after'];

		// Don't show a separator for the last one.
		if ($link_num != count($context['linktree']) - 1)
			echo '&nbsp;&#187;';

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
		<div id="main_menu" class="dropmenu">
			<ul>';

	foreach ($context['menu_buttons'] as $act => $button)
	{
		echo '
				<li id="button_', $act, '">
					<a', $button['active_button'] ? ' class="active"' : '', ' href="', $button['href'], '">
						<span', isset($button['is_last']) ? ' class="last"' : '', '>', $button['title'], '</span>
					</a>';
		if (!empty($button['sub_buttons']))
		{
			echo '
					<ul>';

			foreach($button['sub_buttons'] as $childbutton)
			{
				echo '
						<li>
							<a', !empty($childbutton['active_button']) ? ' class="active"' : '', ' href="', $childbutton['href'], '">
								<span', isset($childbutton['is_last']) ? ' class="last"' : '', '>', $childbutton['title'], '</span>
							</a>';
				// 3rd level menus :)				
				if (!empty($childbutton['sub_buttons']))
				{
					echo '
							<ul>';

					foreach($childbutton['sub_buttons'] as $grandchildbutton)
						echo '
								<li>
									<a', $grandchildbutton['active_button'] ? ' class="active"' : '', ' href="', $grandchildbutton['href'], '">
										<span', isset($grandchildbutton['is_last']) ? ' class="last"' : '', '>', $grandchildbutton['title'], '</span>
									</a>
								</li>';

						echo '
							</ul>';
					}
				
				echo '
						</li>';
			}	
			echo '
					</ul>';
			}
		echo '
				</li>';
	}

	echo '
			</ul>
		</div>';
}

// Generate a strip of buttons.
function template_button_strip($button_strip, $direction = '', $force_reset = false, $custom_td = '')
{
	global $settings, $context, $txt, $scripturl;

	// Create the buttons...
	$buttons = array();
	foreach ($button_strip as $key => $value)
	{
		if (!isset($value['test']) || !empty($context[$value['test']]))
			$buttons[] = '<a ' . (isset($value['active']) ? 'class="active" ' : '') . 'href="' . $value['url'] . '" ' . (isset($value['custom']) ? $value['custom'] : '') . '><span>' . $txt[$value['text']] . '</span></a>';
	}

	if (empty($buttons))
		return '';

	// Make the last one, as easy as possible.
	$buttons[count($buttons) - 1] = str_replace('<span>', '<span class="last">', $buttons[count($buttons) - 1]);

	echo '
		<div class="buttonlist', !empty($direction) ? ' align_' . $direction : '' , '">
			<ul>
				<li>', implode('</li><li>', $buttons), '</li>
			</ul>
		</div>';
}

?>