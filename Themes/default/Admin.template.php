<?php
// Version: 2.0 Alpha; Admin

// This contains the html for the side bar of the admin center, which is used for all admin pages.
function template_admin_above()
{
	global $context, $settings, $options, $scripturl, $txt;

	// This is the main table - we need it so we can keep the content to the right of it.
	echo '
		<table width="100%" cellpadding="0" cellspacing="0" border="0" style="padding-top: 1ex;"><tr>
			<td width="150" valign="top" style="width: 23ex; padding-right: 10px; padding-bottom: 10px;">
				<table width="100%" cellpadding="4" cellspacing="1" border="0" class="bordercolor">';

	// For every section that appears on the sidebar...
	foreach ($context['admin_areas'] as $section)
	{
		// Show the section header - and pump up the line spacing for readability.
		echo '
					<tr>
						<td class="catbg">', $section['title'], '</td>
					</tr>
					<tr class="windowbg2">
						<td class="smalltext" style="line-height: 1.3; padding-bottom: 3ex;">';

		// For every area of this section show a link to that area (bold if it's currently selected.)
		foreach ($section['areas'] as $i => $area)
		{
			// Not supposed to be printed?
			if (empty($area[0]))
				continue;

			// Is this the current area, or just some area?
			if ($i == $context['admin_area'])
				echo '
							<b><a href="', (isset($area[3]) ? $area[3] : $scripturl . '?action=' . $context['bar_area'] . ';area=' . $i), ';sesc=', $context['session_id'], '">', $area[0], '</a></b><br />';
			else
								echo '
							<a href="', (isset($area[3]) ? $area[3] : $scripturl . '?action=' . $context['bar_area'] . ';area=' . $i), ';sesc=', $context['session_id'], '">', $area[0], '</a><br />';
				}

				echo '
						</td>
					</tr>';
		}

		// This is where the actual "main content" area for the admin section starts.
		echo '
				</table>
			</td>
			<td valign="top">';

	// If there are any "tabs" setup, this is the place to shown them.
	if (!empty($context['admin_tabs']))
	{
		echo '
				<table border="0" cellspacing="0" cellpadding="4" align="center" width="100%" class="tborder" ' , (isset($settings['use_tabs']) && $settings['use_tabs']) ? '' : 'style="margin-bottom: 2ex;"' , '>
					<tr class="titlebg">
						<td>';
		// Show a help item?
		if (!empty($context['admin_tabs']['help']))
			echo '
							<a href="', $scripturl, '?action=helpadmin;help=', $context['admin_tabs']['help'], '" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt[119], '" align="top" /></a> ';
		echo '
							', $context['admin_tabs']['title'], '
						</td>
					</tr>
					<tr class="windowbg">';

		// shall we use the tabs?
		if (!empty($settings['use_tabs']))
		{
			// Find the selected tab
			foreach($context['admin_tabs']['tabs'] as $tab)
				if (!empty($tab['is_selected']))
					$selected_tab = $tab;
			echo '
						<td class="smalltext" style="padding: 2ex;">', !empty($selected_tab['description']) ? $selected_tab['description'] : $context['admin_tabs']['description'], '</td>
					</tr>
				</table>';

			// The admin tabs.
			echo '
				<table cellpadding="0" cellspacing="0" border="0" style="margin-left: 10px;">
					<tr>
						<td class="maintab_first">&nbsp;</td>';

			// Print out all the items in this tab.
			foreach ($context['admin_tabs']['tabs'] as $tab)
			{
				if (!empty($tab['is_selected']))
				{
					echo '
						<td class="maintab_active_first">&nbsp;</td>
						<td valign="top" class="maintab_active_back">
							<a href="', $tab['href'], '">', $tab['title'], '</a>
						</td>
						<td class="maintab_active_last">&nbsp;</td>';
				}
				else
					echo '
						<td valign="top" class="maintab_back">
							<a href="', $tab['href'], '">', $tab['title'], '</a>
						</td>';
			}

			// the end of tabs
			echo '
						<td class="maintab_last">&nbsp;</td>
					</tr>
				</table><br />';
		}
		// ...if not use the old style
		else
		{
			echo '
						<td align="left"><b>';

			// Print out all the items in this tab.
			foreach ($context['admin_tabs']['tabs'] as $tab)
			{
				if (!empty($tab['is_selected']))
				{
					echo '
							<img src="', $settings['images_url'], '/selected.gif" alt="*" /> <b><a href="', $tab['href'], '">', $tab['title'], '</a></b>';

					$selected_tab = $tab;
				}
				else
					echo '
							<a href="', $tab['href'], '">', $tab['title'], '</a>';

				if (empty($tab['is_last']))
					echo ' | ';
			}

			echo '
						</b></td>
					</tr>
					<tr class="windowbg">
						<td class="smalltext" style="padding: 2ex;">', isset($selected_tab['description']) ? $selected_tab['description'] : $context['admin_tabs']['description'], '</td>
					</tr>
				</table>';
		}
	}
}

// Part of the admin layer - used with admin_above to close the table started in it.
function template_admin_below()
{
	global $context, $settings, $options;

	echo '
			</td>
		</tr>
	</table>';
}

// This is the administration center home.
function template_admin()
{
	global $context, $settings, $options, $scripturl, $txt, $modSettings;

	// Welcome message for the admin.
	echo '
		<table width="100%" cellpadding="3" cellspacing="1" border="0" class="bordercolor">
			<tr class="titlebg">
				<td align="center" colspan="2" class="largetext">', $txt[208], '</td>
			</tr><tr>
				<td class="windowbg" valign="top" style="padding: 7px;">
					<b>', $txt['hello_guest'], ' ', $context['user']['name'], '!</b>
					<div style="font-size: 0.85em; padding-top: 1ex;">', $txt[644], '</div>
				</td>
			</tr>
		</table>';

	// Is there an update available?
	echo '
	<div id="update_section" style="display: none;">
		<table width="100%" cellpadding="4" cellspacing="1" border="0" class="bordercolor" style="margin-top: 1.5ex;" id="update_table">
			<tr class="titlebg">
				<td id="update_title">', $txt['update_available'], '</td>
			</tr><tr>
				<td class="windowbg" valign="top" style="padding: 0;">
					<div id="update_message" style="font-size: 0.85em; padding: 4px;">', $txt['update_message'], '</div>
				</td>
			</tr>
		</table>
	</div>';

	echo '
		<table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-top: 1.5ex;"><tr>';

	// Display the "live news" from simplemachines.org.
	echo '
			<td valign="top">
				<table width="100%" cellpadding="5" cellspacing="1" border="0" class="bordercolor">
					<tr>
						<td class="catbg">
							<a href="', $scripturl, '?action=helpadmin;help=live_news" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt[119], '" align="top" /></a> ', $txt['smf217'], '
						</td>
					</tr><tr>
						<td class="windowbg2" valign="top" style="height: 18ex; padding: 0;">
							<div id="smfAnnouncements" style="height: 18ex; overflow: auto; padding-right: 1ex;"><div style="margin: 4px; font-size: 0.85em;">', $txt['lfyi'], '</div></div>
						</td>
					</tr>
				</table>
			</td>
			<td style="width: 1ex;">&nbsp;</td>';

	// Show the user version information from their server.
	echo '
			<td valign="top" style="width: 40%;">
				<table width="100%" cellpadding="5" cellspacing="1" border="0" class="bordercolor" id="supportVersionsTable">
					<tr>
						<td class="catbg"><a href="', $scripturl, '?action=admin;credits">', $txt['support_title'], '</a></td>
					</tr><tr>
						<td class="windowbg2" valign="top" style="height: 18ex;">
							<b>', $txt['support_versions'], ':</b><br />
							', $txt['support_versions_forum'], ':
							<i id="yourVersion" style="white-space: nowrap;">', $context['forum_version'], '</i><br />
							', $txt['support_versions_current'], ':
							<i id="smfVersion" style="white-space: nowrap;">??</i><br />
							', $context['can_admin'] ? '<a href="' . $scripturl . '?action=admin;area=version">' . $txt['dvc_more'] . '</a>' : '', '<br />';

	// Display all the members who can administrate the forum.
	echo '
							<br />
							<b>', $txt[684], ':</b>
							', implode(', ', $context['administrators']);
	// If we have lots of admins... don't show them all.
	if (!empty($context['more_admins_link']))
		echo '
							 (', $context['more_admins_link'], ')';
	echo '
						</td>
					</tr>
				</table>
			</td>
		</tr></table>';

	echo '
		<table width="100%" cellpadding="5" cellspacing="0" border="0" class="tborder" style="margin-top: 1.5ex;">
			<tr valign="top" class="windowbg2">';

	$row = false;
	foreach ($context['quick_admin_tasks'] as $task)
	{
		echo '
				<td style="padding-bottom: 2ex;" width="50%">
					<div style="font-weight: bold; font-size: 1.1em;">', $task['link'], '</div>
					', $task['description'], '
				</td>';

		if ($row && !$task['is_last'])
			echo '
			</tr>
			<tr valign="top" class="windowbg2">';

		$row = !$row;
	}

	echo '
			</tr>
		</table>';

	// The below functions include all the scripts needed from the simplemachines.org site. The language and format are passed for internationalization.
	if (empty($modSettings['disable_smf_js']))
		echo '
		<script language="JavaScript" type="text/javascript" src="', $scripturl, '?action=viewsmfile;filename=current-version.js"></script>
		<script language="JavaScript" type="text/javascript" src="', $scripturl, '?action=viewsmfile;filename=latest-news.js"></script>';

	// This sets the announcements and current versions themselves ;).
	echo '
		<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
			function smfSetAnnouncements()
			{
				if (typeof(window.smfAnnouncements) == "undefined" || typeof(window.smfAnnouncements.length) == "undefined")
					return;

				var str = "<div style=\"margin: 4px; font-size: 0.85em;\">";

				for (var i = 0; i < window.smfAnnouncements.length; i++)
				{
					str += "\n	<div style=\"padding-bottom: 2px;\"><a hre" + "f=\"" + window.smfAnnouncements[i].href + "\">" + window.smfAnnouncements[i].subject + "</a> ', $txt[30], ' " + window.smfAnnouncements[i].time + "</div>";
					str += "\n	<div style=\"padding-left: 2ex; margin-bottom: 1.5ex; border-top: 1px dashed;\">"
					str += "\n		" + window.smfAnnouncements[i].message;
					str += "\n	</div>";
				}

				setInnerHTML(document.getElementById("smfAnnouncements"), str + "</div>");
			}

			function smfAnnouncementsFixHeight()
			{
				if (document.getElementById("supportVersionsTable").offsetHeight)
					document.getElementById("smfAnnouncements").style.height = (document.getElementById("supportVersionsTable").offsetHeight - 10) + "px";
			}

			function smfCurrentVersion()
			{
				var smfVer, yourVer;

				if (typeof(window.smfVersion) != "string")
					return;

				smfVer = document.getElementById("smfVersion");
				yourVer = document.getElementById("yourVersion");

				setInnerHTML(smfVer, window.smfVersion);

				var currentVersion = getInnerHTML(yourVer);
				if (currentVersion != window.smfVersion)
					setInnerHTML(yourVer, "<span style=\"color: red;\">" + currentVersion + "</span>");
			}

			// Sort out the update window
			function smfUpdateAvailable()
			{
				var updateBody;

				// Nothing to declare?
				if (typeof(window.smfUpdatePackage) == "undefined")
					return;

				updateBody = document.getElementById("update_message");

				// Are we setting a custom message?
				if (typeof(window.smfUpdateNotice) != "undefined")
					setInnerHTML(updateBody, window.smfUpdateNotice);

				// Parse in the package download URL if it exists in the string.
				document.getElementById("update-link").href = "', $scripturl, '?action=admin;area=packages;pgdownload;auto;package=" + window.smfUpdatePackage + ";sesc=', $context['session_id'], '";

				// If we decide to override life into "red" mode, do it.
				if (typeof(window.smfUpdateCritical) != "undefined")
				{
					document.getElementById("update_table").style.backgroundColor = "#aa2222";
					document.getElementById("update_title").style.backgroundColor = "#dd2222";
					document.getElementById("update_title").style.color = "white";
					document.getElementById("update_message").style.backgroundColor = "#eebbbb";
					document.getElementById("update_message").style.color = "black";
				}
				// And we can override the title if we really want.
				if (typeof(window.smfUpdateTitle) != "undefined")
					setInnerHTML(document.getElementById("update_title"), window.smfUpdateTitle);

				// Finally, make the box visible.
				document.getElementById("update_section").style.display = "";
			}';

	// IE 4 won't like it if you try to change the innerHTML before load...
	echo '

			var oldonload;
			if (typeof(window.onload) != "undefined")
				oldonload = window.onload;

			window.onload = function ()
			{
				smfSetAnnouncements();
				smfCurrentVersion();
				smfUpdateAvailable();';

	if ($context['browser']['is_ie'] && !$context['browser']['is_ie4'])
		echo '
				if (typeof(smf_codeFix) != "undefined")
					window.detachEvent("onload", smf_codeFix);
				window.attachEvent("onload",
					function ()
					{
						with (document.all.supportVersionsTable)
							style.height = parentNode.offsetHeight;
					}
				);
				if (typeof(smf_codeFix) != "undefined")
					window.attachEvent("onload", smf_codeFix);';

	echo '

				if (oldonload)
					oldonload();
			}
		// ]]></script>';
}

// Show some support information and credits to those who helped make this.
function template_credits()
{
	global $context, $settings, $options, $scripturl, $txt;

	// Show the user version information from their server.
	echo '
		<table width="100%" cellpadding="5" cellspacing="0" border="0" class="tborder">
			<tr class="titlebg">
				<td>', $txt['support_title'], '</td>
			</tr><tr>
				<td class="windowbg2">
					<b>', $txt['support_versions'], ':</b><br />
					', $txt['support_versions_forum'], ':
					<i id="yourVersion" style="white-space: nowrap;">', $context['forum_version'], '</i>', $context['can_admin'] ? ' <a href="' . $scripturl . '?action=admin;area=version">' . $txt['dvc_more'] . '</a>' : '', '<br />
					', $txt['support_versions_current'], ':
					<i id="smfVersion" style="white-space: nowrap;">??</i><br />';

	// Display all the variables we have server information for.
	foreach ($context['current_versions'] as $version)
		echo '
					', $version['title'], ':
					<i>', $version['version'], '</i><br />';

	echo '

				</td>
			</tr>
		</table>';

	// Display latest support questions from simplemachines.org.
	echo '
		<table width="100%" cellpadding="5" cellspacing="0" border="0" class="tborder" style="margin-top: 2ex;">
			<tr class="titlebg">
				<td><a href="', $scripturl, '?action=helpadmin;help=latest_support" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt[119], '" align="top" /></a> ', $txt['support_latest'], '</td>
			</tr><tr>
				<td class="windowbg2">
					<div id="latestSupport">', $txt['support_latest_fetch'], '</div>
				</td>
			</tr>
		</table>';

	// The most important part - the credits :P.
	echo '
		<table width="100%" cellpadding="5" cellspacing="0" border="0" class="tborder" style="margin-top: 2ex;">
			<tr class="titlebg">
				<td>', $txt[571], '</td>
			</tr><tr>
				<td class="windowbg2"><span style="font-size: 0.85em;" id="credits">', $context['credits'], '</span></td>
			</tr>
		</table>';

	// This makes all the support information available to the support script...
	echo '
		<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
			var smfSupportVersions = {};

			smfSupportVersions.forum = "', $context['forum_version'], '";';

	// Don't worry, none of this is logged, it's just used to give information that might be of use.
	foreach ($context['current_versions'] as $variable => $version)
		echo '
			smfSupportVersions.', $variable, ' = "', $version['version'], '";';

	// Now we just have to include the script and wait ;).
	echo '
		// ]]></script>
		<script language="JavaScript" type="text/javascript" src="', $scripturl, '?action=viewsmfile;filename=current-version.js"></script>
		<script language="JavaScript" type="text/javascript" src="', $scripturl, '?action=viewsmfile;filename=/latest-news.js"></script>
		<script language="JavaScript" type="text/javascript" src="', $scripturl, '?action=viewsmfile;filename=latest-support.js?"></script>';

	// This sets the latest support stuff.
	echo '
		<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
			function smfSetLatestSupport()
			{
				if (window.smfLatestSupport)
					setInnerHTML(document.getElementById("latestSupport"), window.smfLatestSupport);
			}

			function smfCurrentVersion()
			{
				var smfVer, yourVer;

				if (!window.smfVersion)
					return;

				smfVer = document.getElementById("smfVersion");
				yourVer = document.getElementById("yourVersion");

				setInnerHTML(smfVer, window.smfVersion);

				var currentVersion = getInnerHTML(yourVer);
				if (currentVersion != window.smfVersion)
					setInnerHTML(yourVer, "<span style=\"color: red;\">" + currentVersion + "</span>");
			}';

	// IE 4 is rather annoying, this wouldn't be necessary...
	echo '

			var oldonload;
			if (typeof(window.onload) != "undefined")
				oldonload = window.onload;

			window.onload = function ()
			{
				smfSetLatestSupport();
				smfCurrentVersion()

				if (oldonload)
					oldonload();
			}
		// ]]></script>';
}

// Displays information about file versions installed, and compares them to current version.
function template_view_versions()
{
	global $context, $settings, $options, $scripturl, $txt;

	echo '
		<table width="94%" cellpadding="3" cellspacing="1" border="0" align="center" class="bordercolor">
			<tr class="titlebg">
				<td>', $txt[429], '</td>
			</tr><tr class="windowbg">
				<td class="smalltext" style="padding: 2ex;">', $txt['dvc1'], '</td>
			</tr><tr>
				<td class="windowbg2" style="padding: 1ex 0 1ex 0;">
					<table width="88%" cellpadding="2" cellspacing="0" border="0" align="center">
						<tr>
							<td width="50%"><b>', $txt[495], '</b></td><td width="25%"><b>', $txt['dvc_your'], '</b></td><td width="25%"><b>', $txt['dvc_current'], '</b></td>
						</tr>';

	// The current version of the core SMF package.
	echo '
						<tr>
							<td>', $txt[496], '</td><td><i id="yourSMF">', $context['forum_version'], '</i></td><td><i id="currentSMF">??</i></td>
						</tr>';

	// Now list all the source file versions, starting with the overall version (if all match!).
	echo '
						<tr>
							<td><a href="javascript:void(0);" onclick="return swapOption(this, \'Sources\');">', $txt['dvc_sources'], '</a></td><td><i id="yourSources">??</i></td><td><i id="currentSources">??</i></td>
						</tr>
					</table>
					<table id="Sources" width="88%" cellpadding="2" cellspacing="0" border="0" align="center">';

	// Loop through every source file displaying its version - using javascript.
	foreach ($context['file_versions'] as $filename => $version)
		echo '
						<tr>
							<td width="50%" style="padding-left: 3ex;">', $filename, '</td><td width="25%"><i id="yourSources', $filename, '">', $version, '</i></td><td width="25%"><i id="currentSources', $filename, '">??</i></td>
						</tr>';

	// Default template files.
	echo '
					</table>
					<table width="88%" cellpadding="2" cellspacing="0" border="0" align="center">
						<tr>
							<td width="50%"><a href="javascript:void(0);" onclick="return swapOption(this, \'Default\');">', $txt['dvc_default'], '</a></td><td width="25%"><i id="yourDefault">??</i></td><td width="25%"><i id="currentDefault">??</i></td>
						</tr>
					</table>
					<table id="Default" width="88%" cellpadding="2" cellspacing="0" border="0" align="center">';

	foreach ($context['default_template_versions'] as $filename => $version)
		echo '
						<tr>
							<td width="50%" style="padding-left: 3ex;">', $filename, '</td><td width="25%"><i id="yourDefault', $filename, '">', $version, '</i></td><td width="25%"><i id="currentDefault', $filename, '">??</i></td>
						</tr>';

	// Now the language files...
	echo '
					</table>
					<table width="88%" cellpadding="2" cellspacing="0" border="0" align="center">
						<tr>
							<td width="50%"><a href="javascript:void(0);" onclick="return swapOption(this, \'Languages\');">', $txt['dvc_languages'], '</a></td><td width="25%"><i id="yourLanguages">??</i></td><td width="25%"><i id="currentLanguages">??</i></td>
						</tr>
					</table>
					<table id="Languages" width="88%" cellpadding="2" cellspacing="0" border="0" align="center">';

	foreach ($context['default_language_versions'] as $language => $files)
	{
		foreach ($files as $filename => $version)
			echo '
						<tr>
							<td width="50%" style="padding-left: 3ex;">', $filename, '.<i>', $language, '</i>.php</td><td width="25%"><i id="your', $filename, '.', $language, '">', $version, '</i></td><td width="25%"><i id="current', $filename, '.', $language, '">??</i></td>
						</tr>';
	}

	echo '
					</table>';

	// Finally, display the version information for the currently selected theme - if it is not the default one.
	if (!empty($context['template_versions']))
	{
		echo '
					<table width="88%" cellpadding="2" cellspacing="0" border="0" align="center">
						<tr>
							<td width="50%"><a href="javascript:void(0);" onclick="return swapOption(this, \'Templates\');">', $txt['dvc_templates'], '</a></td><td width="25%"><i id="yourTemplates">??</i></td><td width="25%"><i id="currentTemplates">??</i></td>
						</tr>
					</table>
					<table id="Templates" width="88%" cellpadding="2" cellspacing="0" border="0" align="center">';

		foreach ($context['template_versions'] as $filename => $version)
			echo '
						<tr>
							<td width="50%" style="padding-left: 3ex;">', $filename, '</td><td width="25%"><i id="yourTemplates', $filename, '">', $version, '</i></td><td width="25%"><i id="currentTemplates', $filename, '">??</i></td>
						</tr>';

		echo '
					</table>';
	}

	echo '
				</td>
			</tr>
		</table>';

	/* Below is the hefty javascript for this. Upon opening the page it checks the current file versions with ones
	   held at simplemachines.org and works out if they are up to date.  If they aren't it colors that files number
	   red.  It also contains the function, swapOption, that toggles showing the detailed information for each of the
	   file catorgories. (sources, languages, and templates.) */
	echo '
		<script language="JavaScript" type="text/javascript" src="', $scripturl, '?action=viewsmfile;filename=detailed-version.js"></script>
		<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
			var swaps = {};

			function swapOption(sendingElement, name)
			{
				// If it is undefined, or currently off, turn it on - otherwise off.
				swaps[name] = typeof(swaps[name]) == "undefined" || !swaps[name];
				document.getElementById(name).style.display = swaps[name] ? "" : "none";

				// Unselect the link and return false.
				sendingElement.blur();
				return false;
			}

			function smfDetermineVersions()
			{
				var highYour = {"Sources": "??", "Default" : "??", "Languages": "??", "Templates": "??"};
				var highCurrent = {"Sources": "??", "Default" : "??", "Languages": "??", "Templates": "??"};
				var lowVersion = {"Sources": false, "Default": false, "Languages" : false, "Templates": false};
				var knownLanguages = [".', implode('", ".', $context['default_known_languages']), '"];

				document.getElementById("Sources").style.display = "none";
				document.getElementById("Languages").style.display = "none";
				document.getElementById("Default").style.display = "none";
				if (document.getElementById("Templates"))
					document.getElementById("Templates").style.display = "none";

				if (typeof(window.smfVersions) == "undefined")
					window.smfVersions = {};

				for (var filename in window.smfVersions)
				{
					if (!document.getElementById("current" + filename))
						continue;

					var yourVersion = getInnerHTML(document.getElementById("your" + filename));

					var versionType;
					for (var verType in lowVersion)
						if (filename.substr(0, verType.length) == verType)
						{
							versionType = verType;
							break;
						}

					if (typeof(versionType) != "undefined")
					{
						if ((highYour[versionType] < yourVersion || highYour[versionType] == "??") && !lowVersion[versionType])
							highYour[versionType] = yourVersion;
						if (highCurrent[versionType] < smfVersions[filename] || highCurrent[versionType] == "??")
							highCurrent[versionType] = smfVersions[filename];

						if (yourVersion < smfVersions[filename])
						{
							lowVersion[versionType] = yourVersion;
							document.getElementById("your" + filename).style.color = "red";
						}
					}
					else if (yourVersion < smfVersions[filename])
						lowVersion[versionType] = yourVersion;

					setInnerHTML(document.getElementById("current" + filename), smfVersions[filename]);
					setInnerHTML(document.getElementById("your" + filename), yourVersion);
				}

				if (typeof(window.smfLanguageVersions) == "undefined")
					window.smfLanguageVersions = {};

				for (filename in window.smfLanguageVersions)
				{
					for (var i = 0; i < knownLanguages.length; i++)
					{
						if (!document.getElementById("current" + filename + knownLanguages[i]))
							continue;

						setInnerHTML(document.getElementById("current" + filename + knownLanguages[i]), smfLanguageVersions[filename]);

						yourVersion = getInnerHTML(document.getElementById("your" + filename + knownLanguages[i]));
						setInnerHTML(document.getElementById("your" + filename + knownLanguages[i]), yourVersion);

						if ((highYour["Languages"] < yourVersion || highYour["Languages"] == "??") && !lowVersion["Languages"])
							highYour["Languages"] = yourVersion;
						if (highCurrent["Languages"] < smfLanguageVersions[filename] || highCurrent["Languages"] == "??")
							highCurrent["Languages"] = smfLanguageVersions[filename];

						if (yourVersion < smfLanguageVersions[filename])
						{
							lowVersion["Languages"] = yourVersion;
							document.getElementById("your" + filename + knownLanguages[i]).style.color = "red";
						}
					}
				}

				setInnerHTML(document.getElementById("yourSources"), lowVersion["Sources"] ? lowVersion["Sources"] : highYour["Sources"]);
				setInnerHTML(document.getElementById("currentSources"), highCurrent["Sources"]);
				if (lowVersion["Sources"])
					document.getElementById("yourSources").style.color = "red";

				setInnerHTML(document.getElementById("yourDefault"), lowVersion["Default"] ? lowVersion["Default"] : highYour["Default"]);
				setInnerHTML(document.getElementById("currentDefault"), highCurrent["Default"]);
				if (lowVersion["Default"])
					document.getElementById("yourDefault").style.color = "red";

				if (document.getElementById("Templates"))
				{
					setInnerHTML(document.getElementById("yourTemplates"), lowVersion["Templates"] ? lowVersion["Templates"] : highYour["Templates"]);
					setInnerHTML(document.getElementById("currentTemplates"), highCurrent["Templates"]);

					if (lowVersion["Templates"])
						document.getElementById("yourTemplates").style.color = "red";
				}

				setInnerHTML(document.getElementById("yourLanguages"), lowVersion["Languages"] ? lowVersion["Languages"] : highYour["Languages"]);
				setInnerHTML(document.getElementById("currentLanguages"), highCurrent["Languages"]);
				if (lowVersion["Languages"])
					document.getElementById("yourLanguages").style.color = "red";
			}
		// ]]></script>';

	// Internet Explorer 4 is tricky, it won't set any innerHTML until after load.
	if ($context['browser']['is_ie4'])
		echo '
		<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
			window.onload = smfDetermineVersions;
		// ]]></script>';
	else
		echo '
		<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
			smfDetermineVersions();
		// ]]></script>';
}

// Form for stopping people using naughty words, etc.
function template_edit_censored()
{
	global $context, $settings, $options, $scripturl, $txt, $modSettings;

	// First section is for adding/removing words from the censored list.
	echo '
		<form action="', $scripturl, '?action=admin;area=postsettings;sa=censor" method="post" accept-charset="', $context['character_set'], '">
			<table width="600" cellpadding="4" cellspacing="0" border="0" align="center" class="tborder">
				<tr class="titlebg">
					<td colspan="2">', $txt[135], '</td>
				</tr><tr class="windowbg2">
					<td align="center">
						<table width="100%">
							<tr>
								<td colspan="2" align="center">
									', $txt[136], '<br />';

	// Show text boxes for censoring [bad   ] => [good  ].
	foreach ($context['censored_words'] as $vulgar => $proper)
		echo '
									<div style="margin-top: 1ex;"><input type="text" name="censor_vulgar[]" value="', $vulgar, '" size="20" /> => <input type="text" name="censor_proper[]" value="', $proper, '" size="20" /></div>';

	// Now provide a way to censor more words.
	echo '
									<noscript>
										<div style="margin-top: 1ex;"><input type="text" name="censor_vulgar[]" size="20" /> => <input type="text" name="censor_proper[]" size="20" /></div>
									</noscript>
									<div id="moreCensoredWords"></div><div style="margin-top: 1ex; display: none;" id="moreCensoredWords_link"><a href="#;" onclick="addNewWord(); return false;">', $txt['censor_clickadd'], '</a></div>
									<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
										document.getElementById("moreCensoredWords_link").style.display = "";

										function addNewWord()
										{
											setOuterHTML(document.getElementById("moreCensoredWords"), \'<div style="margin-top: 1ex;"><input type="text" name="censor_vulgar[]" size="20" /> => <input type="text" name="censor_proper[]" size="20" /></div><div id="moreCensoredWords"></div>\');
										}
									// ]]></script><br />
								</td>
							</tr><tr>
								<td colspan="2"><hr /></td>
							</tr><tr>
								<th width="50%" align="right"><label for="censorWholeWord_check">', $txt['smf231'], ':</label></th>
								<td align="left"><input type="checkbox" name="censorWholeWord" value="1" id="censorWholeWord_check"', empty($modSettings['censorWholeWord']) ? '' : ' checked="checked"', ' class="check" /></td>
							</tr><tr>
								<th align="right"><label for="censorIgnoreCase_check">', $txt['censor_case'], ':</label></th>
								<td align="left">
									<input type="checkbox" name="censorIgnoreCase" value="1" id="censorIgnoreCase_check"', empty($modSettings['censorIgnoreCase']) ? '' : ' checked="checked"', ' class="check" />
								</td>
							</tr><tr>
								<td colspan="2" align="right">
									<input type="submit" name="save_censor" value="', $txt[10], '" />
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>

			<br />';

	// This table lets you test out your filters by typing in rude words and seeing what comes out.
	echo '
			<table width="600" cellpadding="4" cellspacing="0" border="0" align="center" class="tborder">
				<tr class="titlebg">
					<td>', $txt['censor_test'], '</td>
				</tr><tr class="windowbg2">
					<td align="center">
						<input type="text" name="censortest" value="', empty($context['censor_test']) ? '' : $context['censor_test'], '" />
						<input type="submit" value="', $txt['censor_test_save'], '" />
					</td>
				</tr>
			</table>

			<input type="hidden" name="sc" value="', $context['session_id'], '" />
		</form>';
}

// Template for editing bulletin board code settings.
function template_edit_bbc_settings()
{
	global $context, $settings, $options, $txt, $scripturl, $modSettings;

	echo '
	<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
		function toggleBBCDisabled(disable)
		{
			for (var i = 0; i < document.forms.bbcForm.length; i++)
			{
				if (typeof(document.forms.bbcForm[i].name) == "undefined" || (document.forms.bbcForm[i].name.substr(0, 11) != "enabledTags"))
					continue;

				document.forms.bbcForm[i].disabled = disable;
			}
			document.getElementById("select_all").disabled = disable;
		}
	// ]]></script>

	<form action="', $scripturl, '?action=admin;area=postsettings;sa=bbc" method="post" accept-charset="', $context['character_set'], '" name="bbcForm" id="bbcForm" onsubmit="toggleBBCDisabled(false);">
		<table border="0" cellspacing="0" cellpadding="4" align="center" width="80%" class="tborder">
			<tr class="titlebg">
				<td colspan="2">', $txt['manageposts_bbc_settings_title'], '</td>
			</tr><tr class="windowbg2">
				<th width="50%" align="right"><label for="enableBBC_check">', $txt['enableBBC'], '</label> <span style="font-weight: normal;">(<a href="', $scripturl, '?action=helpadmin;help=enableBBC" onclick="return reqWin(this.href);">?</a>)</span>:</th>
				<td>
					<input type="checkbox" name="enableBBC" id="enableBBC_check"', empty($modSettings['enableBBC']) ? '' : ' checked="checked"', ' onchange="toggleBBCDisabled(!this.checked);" class="check" />
				</td>
			</tr><tr class="windowbg2">
				<th width="50%" align="right"><label for="enablePostHTML_check">', $txt['enablePostHTML'], '</label> <span style="font-weight: normal;">(<a href="', $scripturl, '?action=helpadmin;help=enablePostHTML" onclick="return reqWin(this.href);">?</a>)</span>:</th>
				<td>
					<input type="checkbox" name="enablePostHTML" id="enablePostHTML_check"', empty($modSettings['enablePostHTML']) ? '' : ' checked="checked"', ' class="check" />
				</td>
			</tr><tr class="windowbg2">
				<th width="50%" align="right"><label for="autoLinkUrls_check">', $txt['autoLinkUrls'], '</label>:</th>
				<td>
					<input type="checkbox" name="autoLinkUrls" id="autoLinkUrls_check"', empty($modSettings['autoLinkUrls']) ? '' : ' checked="checked"', ' class="check" />
				</td>
			</tr><tr class="windowbg2">
				<th width="50%" align="right" valign="top"><label for="enabledBBCTags">', $txt['bbcTagsToUse'], '</label>:</th>
				<td>
					<fieldset id="enabledBBCTags">
						<legend>', $txt['bbcTagsToUse_select'], '</legend>
						<table width="100%"><tr>';
	foreach ($context['bbc_columns'] as $bbcColumn)
	{
		echo '
							<td valign="top">';
		foreach ($bbcColumn as $bbcTag)
			echo '
								<input type="checkbox" name="enabledTags[]" id="tag_', $bbcTag['tag'], '" value="', $bbcTag['tag'], '"', $bbcTag['is_enabled'] ? ' checked="checked"' : '', ' class="check" /> <label for="tag_', $bbcTag['tag'], '">', $bbcTag['tag'], '</label>', $bbcTag['show_help'] ? ' (<a href="' . $scripturl . '?action=helpadmin;help=tag_' . $bbcTag['tag'] . '" onclick="return reqWin(this.href);">?</a>)' : '', '<br />';
		echo '
							</td>';
	}
	echo '
						</tr></table><br />
						<input type="checkbox" id="select_all" onclick="invertAll(this, this.form, \'enabledTags\');"', $context['bbc_all_selected'] ? ' checked="checked"' : '', ' class="check" /> <label for="select_all"><i>', $txt['bbcTagsToUse_select_all'], '</i></label>
					</fieldset>
				</td>
			</tr><tr class="windowbg2">
				<td align="right" colspan="2">
					<input type="submit" name="save_settings" value="', $txt['manageposts_settings_submit'], '" />
				</td>
			</tr>
		</table>
		<input type="hidden" name="sc" value="', $context['session_id'], '" />
	</form>';

	if (empty($modSettings['enableBBC']))
		echo '
	<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
		toggleBBCDisabled(true);
	// ]]></script>';
}

// A not dissimilar template to the above for editing signature settings.
function template_edit_signature_settings()
{
	global $context, $settings, $options, $txt, $scripturl, $modSettings;

	echo '
	<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
		function toggleBBCDisabled(disable)
		{
			for (var i = 0; i < document.forms.bbcForm.length; i++)
			{
				if (typeof(document.forms.bbcForm[i].name) == "undefined" || (document.forms.bbcForm[i].name.substr(0, 11) != "enabledTags"))
					continue;

				document.forms.bbcForm[i].disabled = disable;
			}
			document.getElementById("select_all").disabled = disable;
		}
	// ]]></script>

	<form action="', $scripturl, '?action=admin;area=featuresettings;sa=sig" method="post" accept-charset="', $context['character_set'], '" name="bbcForm" id="bbcForm" onsubmit="toggleBBCDisabled(false);">
		<table border="0" cellspacing="0" cellpadding="4" align="center" width="80%" class="tborder">
			<tr class="titlebg">
				<td colspan="2">', $txt['signature_settings'], '</td>
			</tr><tr class="windowbg2">
				<td colspan="2" align="center" class="smalltext" style="color: red;">', $txt['signature_settings_warning'], '</td>
			</tr><tr class="windowbg2">
				<th width="50%" align="right"><label for="enabled">', $txt['signature_enable'], ':</label></th>
				<td>
					<input type="checkbox" name="enabled" id="enabled" ', $context['signature_settings']['enabled'] ? ' checked="checked"' : '', ' class="check" />
				</td>
			</tr><tr class="windowbg2">
				<th width="50%" align="right"><label for="max_length">', $txt['signature_max_length'], '</label></th>
				<td>
					<input type="text" name="max_length" id="max_length" value="', $context['signature_settings']['max_length'], '" size="6" />
				</td>
			</tr><tr class="windowbg2">
				<th width="50%" align="right"><label for="max_lines">', $txt['signature_max_lines'], '</label></th>
				<td>
					<input type="text" name="max_lines" id="max_lines" value="', $context['signature_settings']['max_lines'], '" size="6" />
				</td>
			</tr><tr class="windowbg2">
				<th width="50%" align="right"><label for="max_images">', $txt['signature_max_images'], '</label></th>
				<td>
					<input type="text" name="max_images" id="max_images" value="', $context['signature_settings']['max_images'], '" size="6" />
				</td>
			</tr><tr class="windowbg2">
				<th width="50%" align="right"><label for="max_smileys">', $txt['signature_max_smileys'], '</label></th>
				<td>
					<input type="text" name="max_smileys" id="max_smileys" value="', $context['signature_settings']['max_smileys'], '" size="6" />
				</td>
			</tr><tr class="windowbg2">
				<th width="50%" align="right"><label for="max_image_width">', $txt['signature_max_image_width'], '</label></th>
				<td>
					<input type="text" name="max_image_width" id="max_image_width" value="', $context['signature_settings']['max_image_width'], '" size="6" />
				</td>
			</tr><tr class="windowbg2">
				<th width="50%" align="right"><label for="max_image_height">', $txt['signature_max_image_height'], '</label></th>
				<td>
					<input type="text" name="max_image_height" id="max_image_height" value="', $context['signature_settings']['max_image_height'], '" size="6" />
				</td>
			</tr><tr class="windowbg2">
				<th width="50%" align="right"><label for="max_font_size">', $txt['signature_max_font_size'], '</label></th>
				<td>
					<input type="text" name="max_font_size" id="max_font_size" value="', $context['signature_settings']['max_font_size'], '" size="6" />
				</td>
			</tr><tr class="windowbg2">
				<th width="50%" align="right" valign="top"><label for="enabledBBCTags">', $txt['bbcTagsToUse'], '</label>:</th>
				<td>
					<fieldset id="enabledBBCTags">
						<legend>', $txt['bbcTagsToUse_select'], '</legend>
						<table width="100%"><tr>';
	foreach ($context['bbc_columns'] as $bbcColumn)
	{
		echo '
							<td valign="top">';
		foreach ($bbcColumn as $bbcTag)
			echo '
								<input type="checkbox" name="enabledTags[]" id="tag_', $bbcTag['tag'], '" value="', $bbcTag['tag'], '"', $bbcTag['is_enabled'] ? ' checked="checked"' : '', ' class="check" /> <label for="tag_', $bbcTag['tag'], '">', $bbcTag['tag'], '</label>', $bbcTag['show_help'] ? ' (<a href="' . $scripturl . '?action=helpadmin;help=tag_' . $bbcTag['tag'] . '" onclick="return reqWin(this.href);">?</a>)' : '', '<br />';
		echo '
							</td>';
	}
	echo '
						</tr></table><br />
						<input type="checkbox" id="select_all" onclick="invertAll(this, this.form, \'enabledTags\');"', $context['bbc_all_selected'] ? ' checked="checked"' : '', ' class="check" /> <label for="select_all"><i>', $txt['bbcTagsToUse_select_all'], '</i></label>
					</fieldset>
				</td>
			</tr><tr class="windowbg2">
				<td align="right" colspan="2">
					<input type="submit" name="save_settings" value="', $txt[10], '" />
				</td>
			</tr>
		</table>
		<input type="hidden" name="sc" value="', $context['session_id'], '" />
	</form>';

	if (empty($modSettings['enableBBC']))
		echo '
	<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
		toggleBBCDisabled(true);
	// ]]></script>';
}

// Template for forum maintenance page.
function template_maintain()
{
	global $context, $settings, $options, $txt, $scripturl, $modSettings;

	// Starts off with general maintenance procedures.
	echo '
		<table width="100%" cellpadding="4" cellspacing="1" border="0" class="bordercolor">
			<tr class="titlebg">
				<td><a href="', $scripturl, '?action=helpadmin;help=maintenance_general" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt[119], '" align="top" /></a> ', $txt['maintain_general'], '</td>
			</tr>
			<tr>
				<td class="windowbg2" style="line-height: 1.3; padding-bottom: 2ex;">
					<a href="', $scripturl, '?action=admin;area=maintain;sa=optimize">', $txt['maintain_optimize'], '</a><br />
					<a href="', $scripturl, '?action=admin;area=version">', $txt['maintain_version'], '</a><br />
					<a href="', $scripturl, '?action=admin;area=repairboards">', $txt['maintain_errors'], '</a><br />
					<a href="', $scripturl, '?action=admin;area=maintain;sa=recount">', $txt['maintain_recount'], '</a><br />
					<a href="', $scripturl, '?action=admin;area=maintain;sa=logs">', $txt['maintain_logs'], '</a><br />', $context['convert_utf8'] ? '
					<a href="' . $scripturl . '?action=admin;area=maintain;sa=convertutf8">' . $txt['utf8_title'] . '</a><br />' : '', $context['convert_entities'] ? '
					<a href="' . $scripturl . '?action=admin;area=maintain;sa=convertentities">' . $txt['entity_convert_title'] . '</a><br />' : '', '
					<a href="', $scripturl, '?action=admin;area=maintain;sa=cleancache">', $txt['maintain_cache'], '</a><br />
				</td>
			</tr>';

	// Backing up the database...?  Good idea!
	echo '
			<tr class="titlebg">
				<td><a href="', $scripturl, '?action=helpadmin;help=maintenance_backup" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt[119], '" align="top" /></a> ', $txt['maintain_backup'], '</td>
			</tr>
			<tr>
				<td class="windowbg2" style="padding-bottom: 1ex;">
					<form action="', $scripturl, '" method="get" accept-charset="', $context['character_set'], '" onsubmit="return this.struct.checked || this.data.checked;">
						<label for="struct"><input type="checkbox" name="struct" id="struct" onclick="this.form.submitDump.disabled = !this.form.struct.checked &amp;&amp; !this.form.data.checked;" class="check" /> ', $txt['maintain_backup_struct'], '</label><br />
						<label for="data"><input type="checkbox" name="data" id="data" onclick="this.form.submitDump.disabled = !this.form.struct.checked &amp;&amp; !this.form.data.checked;" checked="checked" class="check" /> ', $txt['maintain_backup_data'], '</label><br />
						<br />
						<label for="compress"><input type="checkbox" name="compress" id="compress" value="gzip" checked="checked" class="check" /> ', $txt['maintain_backup_gz'], '</label>
						<div align="right" style="margin: 1ex;"><input type="submit" id="submitDump" value="', $txt['maintain_backup_save'], '" /></div>
						<input type="hidden" name="action" value="admin" />
						<input type="hidden" name="area" value="dumpdb" />
						<input type="hidden" name="sesc" value="', $context['session_id'], '" />
					</form>
				</td>
			</tr>';

	// Pruning any older posts.
	echo '
			<tr class="titlebg">
				<td><a href="', $scripturl, '?action=helpadmin;help=maintenance_rot" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt[119], '" align="top" /></a> ', $txt['maintain_old'], '</td>
			</tr>
			<tr>
				<td class="windowbg2">
					<a name="rotLink"></a>';

	// Bit of javascript for showing which boards to prune in an otherwise hidden list.
	echo '
					<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
						var rotSwap = false;
						function swapRot()
						{
							rotSwap = !rotSwap;

							document.getElementById("rotIcon").src = smf_images_url + (rotSwap ? "/collapse.gif" : "/expand.gif");
							setInnerHTML(document.getElementById("rotText"), rotSwap ? "', $txt['maintain_old_choose'], '" : "', $txt['maintain_old_all'], '");
							document.getElementById("rotPanel").style.display = (rotSwap ? "block" : "none");

							for (var i = 0; i < document.forms.rotForm.length; i++)
							{
								if (document.forms.rotForm.elements[i].type.toLowerCase() == "checkbox")
									document.forms.rotForm.elements[i].checked = !rotSwap;
							}
						}
					// ]]></script>';

	// The otherwise hidden "choose which boards to prune".
	echo '
					<form action="', $scripturl, '?action=removeoldtopics2" method="post" accept-charset="', $context['character_set'], '" name="rotForm" id="rotForm">
						', $txt['maintain_old_since_days1'], '<input type="text" name="maxdays" value="30" size="3" />', $txt['maintain_old_since_days2'], '<br />
						<div style="padding-left: 3ex;">
							<label for="delete_type_nothing"><input type="radio" name="delete_type" id="delete_type_nothing" value="nothing" class="check" checked="checked" /> ', $txt['maintain_old_nothing_else'], '</label><br />
							<label for="delete_type_moved"><input type="radio" name="delete_type" id="delete_type_moved" value="moved" class="check" /> ', $txt['maintain_old_are_moved'], '</label><br />
							<label for="delete_type_locked"><input type="radio" name="delete_type" id="delete_type_locked" value="locked" class="check" /> ', $txt['maintain_old_are_locked'], '</label><br />
						</div>';

	if (!empty($modSettings['enableStickyTopics']))
		echo '
						<div style="padding-left: 3ex; padding-top: 1ex;">
							<label for="delete_old_not_sticky"><input type="checkbox" name="delete_old_not_sticky" id="delete_old_not_sticky" class="check" checked="checked" /> ', $txt['maintain_old_are_not_stickied'], '</label><br />
						</div>';

	echo '
						<br />
						<a href="#rotLink" onclick="swapRot();"><img src="', $settings['images_url'], '/expand.gif" alt="+" id="rotIcon" /></a> <a href="#rotLink" onclick="swapRot();"><span id="rotText" style="font-weight: bold;">', $txt['maintain_old_all'], '</span></a>
						<div style="display: none;" id="rotPanel">
							<table width="100%" cellpadding="3" cellspacing="0" border="0">
								<tr>
									<td valign="top">';

	// This is the "middle" of the list.
	$middle = count($context['categories']) / 2;

	$i = 0;
	foreach ($context['categories'] as $category)
	{
		echo '
										<span style="text-decoration: underline;">', $category['name'], '</span><br />';

		// Display a checkbox with every board.
		foreach ($category['boards'] as $board)
			echo '
										<label for="boards[', $board['id'], ']"><input type="checkbox" name="boards[', $board['id'], ']" id="boards[', $board['id'], ']" checked="checked" class="check" /> ', str_repeat('&nbsp; ', $board['child_level']), $board['name'], '</label><br />';
		echo '
										<br />';

		// Increase $i, and check if we're at the middle yet.
		if (++$i == $middle)
			echo '
									</td>
									<td valign="top">';
	}

	echo '
									</td>
								</tr>
							</table>
						</div>

						<div align="right" style="margin: 1ex;"><input type="submit" value="', $txt['maintain_old_remove'], '" onclick="return confirm(\'', $txt['maintain_old_confirm'], '\');" /></div>
						<input type="hidden" name="sc" value="', $context['session_id'], '" />
					</form>
				</td>
			</tr>
		</table>';

	// Pop up a box to say function completed if the user has been redirected back here from a function they ran.
	if ($context['maintenance_finished'])
		echo '
	<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
		setTimeout("alert(\"', $txt['maintain_done'], '\")", 120);
	// ]]></script>';
}

// Simple template for showing results of our optimization...
function template_optimize()
{
	global $context, $settings, $options, $txt, $scripturl;

	echo '
	<div class="tborder">
		<div class="titlebg" style="padding: 4px;">', $txt['maintain_optimize'], '</div>
		<div class="windowbg" style="padding: 4px;">
			', $txt['smf282'], '<br />
			', $txt['smf283'], '<br />';

	// List each table being optimized...
	foreach ($context['optimized_tables'] as $table)
		echo '
			', sprintf($txt['smf284'], $table['name'], $table['data_freed']), '<br />';

	// How did we go?
	echo '
			<br />', $context['num_tables_optimized'] == 0 ? $txt['smf285'] : $context['num_tables_optimized'] . ' ' . $txt['smf286'];

	echo '
			<br /><br />
			<a href="', $scripturl, '?action=admin;area=maintain">', $txt['maintain_return'], '</a>
		</div>
	</div>';
}

// Maintenance is a lovely thing, isn't it?
function template_not_done()
{
	global $context, $settings, $options, $txt, $scripturl;

	echo '
	<div class="tborder">
		<div class="titlebg" style="padding: 4px;">', $txt['not_done_title'], '</div>
		<div class="windowbg" style="padding: 4px;">
			', $txt['not_done_reason'];

	// !!! Maybe this is overdoing it?
	if (!empty($context['continue_percent']))
		echo '
			<div style="padding-left: 20%; padding-right: 20%; margin-top: 1ex;">
				<div style="font-size: 8pt; height: 12pt; border: 1px solid black; background-color: white; padding: 1px; position: relative;">
					<div style="padding-top: ', $context['browser']['is_safari'] || $context['browser']['is_konqueror'] ? '2pt' : '1pt', '; width: 100%; z-index: 2; color: black; position: absolute; text-align: center; font-weight: bold;">', $context['continue_percent'], '%</div>
					<div style="width: ', $context['continue_percent'], '%; height: 12pt; z-index: 1; background-color: red;">&nbsp;</div>
				</div>
			</div>';

	echo '
			<form action="', $scripturl, $context['continue_get_data'], '" method="post" accept-charset="', $context['character_set'], '" style="margin: 0;" name="autoSubmit" id="autoSubmit">
				<div style="margin: 1ex; text-align: right;"><input type="submit" name="cont" value="', $txt['not_done_continue'], '" /></div>
				', $context['continue_post_data'], '
			</form>
		</div>
	</div>
	<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
		var countdown = ', $context['continue_countdown'], ';
		doAutoSubmit();

		function doAutoSubmit()
		{
			if (countdown == 0)
				document.forms.autoSubmit.submit();
			else if (countdown == -1)
				return;

			document.forms.autoSubmit.cont.value = "', $txt['not_done_continue'], ' (" + countdown + ")";
			countdown--;

			setTimeout("doAutoSubmit();", 1000);
		}
	// ]]></script>';
}

// Template for showing settings (Of any kind really!)
function template_show_settings()
{
	global $context, $txt, $settings, $scripturl;

	if (!empty($context['settings_insert_above']))
		echo $context['settings_insert_above'];

	echo '
	<form action="', $context['post_url'], '" method="post" accept-charset="', $context['character_set'], '">
		<table width="80%" border="0" cellspacing="0" cellpadding="0" class="tborder" align="center">
			<tr><td>
				<table border="0" cellspacing="0" cellpadding="4" width="100%">';

	// Is there a custom title?
	if (isset($context['settings_title']))
		echo '
					<tr class="titlebg">
						<td colspan="3">', $context['settings_title'], '</td>
					</tr>';

	// Have we got some custom code to insert?
	if (!empty($context['settings_message']))
		echo '
					<tr>
						<td class="windowbg2" colspan="3">', $context['settings_message'], '</td>
					</tr>';

	// Now actually loop through all the variables.
	foreach ($context['config_vars'] as $config_var)
	{
		// Is it a title?
		if (is_array($config_var) && $config_var['type'] == 'title')
		{
			echo '
					<tr class="titlebg">
						<td colspan="3">
							', ($config_var['help'] ? '<a href="' . $scripturl . '?action=helpadmin;help=' . $config_var['help'] . '" onclick="return reqWin(this.href);" class="help"><img src="' . $settings['images_url'] . '/helptopics.gif" alt="' . $txt[119] . '" /></a>' : ''), ' 
							', $config_var['label'], '</td>
					</tr>';

			continue;
		}

		echo '
					<tr class="windowbg2">';

		if (is_array($config_var))
		{
			// First off, is this a span like a message?
			if (in_array($config_var['type'], array('message', 'warning')))
			{
				echo '
						<td colspan="3" align="center" ', $config_var['type'] == 'warning' ? 'style="color: red; padding: 2em;"' : '', '>
							', $config_var['label'], '
						</td>';
			}
			// Otherwise it's an input box of some kind.
			else
			{
				// Some quick helpers...
				$javascript = $config_var['javascript'];
				$disabled = !empty($config_var['disabled']) ? ' disabled="disabled"' : '';
				$subtext = !empty($config_var['subtext']) ? '<br /><span class="smalltext"> ' . $config_var['subtext'] . '</span>' : '';
	
				// Show the [?] button.
				if ($config_var['help'])
					echo '
							<td class="windowbg2" valign="top" width="16"><a name="setting_', $config_var['name'], '" href="', $scripturl, '?action=helpadmin;help=', $config_var['help'], '" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt[119], '" border="0" align="top" /></a></td>';
				else
					echo '
							<td class="windowbg2"><a name="setting_', $config_var['name'], '"></a></td>';
	
				echo '
							<td valign="top" ', ($config_var['disabled'] ? ' style="color: #777777;"' : ($config_var['invalid'] ? ' style="color: red; font-weight: bold;"' : '')), '><label for="', $config_var['name'], '">', $config_var['label'], $subtext, ($config_var['type'] == 'password' ? '<br /><i>' . $txt['admin_confirm_password'] . '</i>' : ''), '</label></td>
							<td class="windowbg2" width="50%">', 
								$config_var['preinput'];
	
				// Show a check box.
				if ($config_var['type'] == 'check')
					echo '
								<input type="checkbox"', $javascript, $disabled, ' name="', $config_var['name'], '" id="', $config_var['name'], '" ', ($config_var['value'] ? ' checked="checked"' : ''), ' value="1" class="check" />';
				// Escape (via htmlspecialchars.) the text box.
				elseif ($config_var['type'] == 'password')
					echo '
								<input type="password"', $disabled, $javascript, ' name="', $config_var['name'], '[0]"', ($config_var['size'] ? ' size="' . $config_var['size'] . '"' : ''), ' value="*#fakepass#*" onfocus="this.value = \'\'; this.form.', $config_var['name'], '.disabled = false;" /><br />
								<input type="password" disabled="disabled" id="', $config_var['name'], '" name="', $config_var['name'], '[1]"', ($config_var['size'] ? ' size="' . $config_var['size'] . '"' : ''), ' />';
				// Show a selection box.
				elseif ($config_var['type'] == 'select')
				{
					echo '
								<select name="', $config_var['name'], '" id="', $config_var['name'], '" ', $javascript, $disabled, '>';
					foreach ($config_var['data'] as $option)
						echo '
									<option value="', $option[0], '"', ($option[0] == $config_var['value'] ? ' selected="selected"' : ''), '>', $option[1], '</option>';
					echo '
								</select>';
				}
				// Text area?
				elseif ($config_var['type'] == 'large_text')
				{
					echo '
								<textarea rows="', ($config_var['size'] ? $config_var['size'] : 4), '" cols="30" ', $javascript, $disabled, ' name="', $config_var['name'], '" id="', $config_var['name'], '">', $config_var['value'], '</textarea>';
				}
				// Permission group?
				elseif ($config_var['type'] == 'permissions')
				{
					theme_inline_permissions($config_var['name']);
				}
				// Assume it must be a text box.
				else
					echo '
								<input type="text"', $javascript, $disabled, ' name="', $config_var['name'], '" id="', $config_var['name'], '" value="', $config_var['value'], '"', ($config_var['size'] ? ' size="' . $config_var['size'] . '"' : ''), ' />';
	
				echo '
								', $config_var['postinput'], '
							</td>';
			}
		}
		else
		{
			// Just show a separator.
			if ($config_var == '')
				echo '
							<td colspan="3" class="windowbg2"><hr size="1" width="100%" class="hrcolor" /></td>';
			else
				echo '
							<td colspan="3" class="windowbg2" align="center"><b>' . $config_var . '</b></td>';
		}
		echo '
					</tr>';
	}
	echo '
					</tr><tr>
						<td class="windowbg2" colspan="3" align="center" valign="middle"><input type="submit" value="', $txt[10], '"', (!empty($context['save_disabled']) ? ' disabled="disabled"' : ''), ' /></td>
					</tr>
				</table>
			</td></tr>
		</table>
		<input type="hidden" name="sc" value="', $context['session_id'], '" />
	</form>';

	if (!empty($context['settings_insert_below']))
		echo $context['settings_insert_below'];
}

// Template for listing all scheduled tasks.
function template_view_scheduled_tasks()
{
	global $context, $settings, $options, $txt, $scripturl, $modSettings;

	// Starts off with general maintenance procedures.
	echo '
	<form action="', $scripturl, '?action=admin;area=maintain;sa=tasks" method="post" accept-charset="', $context['character_set'], '">
		<table width="100%" cellpadding="4" cellspacing="1" border="0" class="bordercolor">
			<tr class="titlebg">
				<td colspan="4">', $txt['maintain_tasks'], '</td>
			</tr>
			<tr class="catbg">
				<td colspan="4">', $txt['scheduled_tasks_header'], '</td>
			</tr>
			<tr class="titlebg">
				<td>', $txt['scheduled_tasks_name'], '</td>
				<td>', $txt['scheduled_tasks_next_time'], '</td>
				<td>', $txt['scheduled_tasks_regularity'], '</td>
				<td width="6%">', $txt['scheduled_tasks_enabled'], '</td>
			</tr>';

	$alternate = 0;
	foreach ($context['tasks'] as $task)
	{
		echo '
			<tr class="', $alternate ? 'windowbg' : 'windowbg2', '">
				<td>
					<a href="', $scripturl, '?action=admin;area=maintain;sa=taskedit;tid=', $task['id'], '">', $task['name'], '</a><br />
					<span class="smalltext">', $task['desc'], '</span>
				</td>
				<td>', $task['next_time'], '</td>
				<td><span class="smalltext">', $task['regularity'], '</span></td>
				<td align="center">
					<input type="hidden" name="task[', $task['id'], ']" id="task[', $task['id'], ']" value="0" />
					<input type="checkbox" name="task[', $task['id'], ']" id="task[', $task['id'], ']" ', !$task['disabled'] ? 'checked="checked"' : '', ' class="check" />
				</td>
			</tr>';
		$alternate = !$alternate;
	}

	echo '
			<tr class="titlebg">
				<td colspan="4" align="right">
					<div style="float: left;">
						[<a href="', $scripturl, '?action=admin;area=maintain;sa=tasklog">', $txt['scheduled_view_log'], '</a>]
					</div>
					<div style="float: right;">
						<input type="hidden" name="sc" value="', $context['session_id'], '" />
						<input type="submit" name="save" value="', $txt['scheduled_tasks_save_changes'], '" />
					</div>
				</td>
			</tr>
		</table>
	</form>';
}

// A template for, you guessed it, editing a task!
function template_edit_scheduled_tasks()
{
	global $context, $settings, $options, $txt, $scripturl, $modSettings;

	// Starts off with general maintenance procedures.
	echo '
	<form action="', $scripturl, '?action=admin;area=maintain;sa=taskedit;save;tid=', $context['task']['id'], '" method="post" accept-charset="', $context['character_set'], '">
		<table align="center" width="80%" cellpadding="4" cellspacing="0" border="0" class="tborder">
			<tr class="titlebg">
				<td colspan="2">', $txt['scheduled_task_edit'], '</td>
			</tr><tr class="windowbg2" valign="top">
				<td width="30%">
					<b>', $txt['scheduled_tasks_name'], ':</b>
				</td><td width="70%">
					', $context['task']['name'], '</a><br />
					<span class="smalltext">', $context['task']['desc'], '</span>
				</td>
			</tr><tr class="windowbg2">
				<td width="30%">
					<b>', $txt['scheduled_task_edit_interval'], ':</b>
				</td><td width="70%">
					', $txt['scheduled_task_edit_repeat'], ' 
					<input type="text" name="regularity" value="', empty($context['task']['regularity']) ? 1 : $context['task']['regularity'], '" onchange="if (this.value < 1) this.value = 1;" size="2" maxlength="2" />
					<select name="unit">
						<option value="0">', $txt['scheduled_task_edit_pick_unit'], '</option>
						<option value="0">---------------------</option>
						<option value="m" ', empty($context['task']['unit']) || $context['task']['unit'] == 'm' ? 'selected="selected"' : '', '>', $txt['scheduled_task_reg_unit_m'], '</option>
						<option value="h" ', $context['task']['unit'] == 'h' ? 'selected="selected"' : '', '>', $txt['scheduled_task_reg_unit_h'], '</option>
						<option value="d" ', $context['task']['unit'] == 'd' ? 'selected="selected"' : '', '>', $txt['scheduled_task_reg_unit_d'], '</option>
						<option value="w" ', $context['task']['unit'] == 'w' ? 'selected="selected"' : '', '>', $txt['scheduled_task_reg_unit_w'], '</option>
					</select>
				</td>
			</tr><tr class="windowbg2" valign="top">
				<td width="30%">
					<b>', $txt['scheduled_task_edit_start_time'], ':</b><br />
					<span class="smalltext">', $txt['scheduled_task_edit_start_time_desc'], '</span>
				</td><td width="70%">
					<input type="text" name="offset" value="', $context['task']['offset_formatted'], '" size="6" maxlength="5" />
				</td>
			</tr><tr class="windowbg2">
				<td width="30%">
					<b>', $txt['scheduled_tasks_enabled'], ':</b>
				</td><td width="70%">
					<input type="checkbox" name="enabled" id="enabled" ', !$context['task']['disabled'] ? 'checked="checked"' : '', ' class="check" />
				</td>
			</tr><tr class="windowbg2">
				<td colspan="2" align="center">
					<input type="hidden" name="sc" value="', $context['session_id'], '" />
					<input type="submit" name="save" value="', $txt['scheduled_tasks_save_changes'], '" />
				</td>
			</tr>
		</table>
	</form>';
}

// Show the task log.
function template_task_log()
{
	global $context, $settings, $options, $txt, $scripturl, $modSettings;

	echo '
	<form action="', $scripturl, '?action=admin;area=maintain;sa=tasklog" method="post" accept-charset="', $context['character_set'], '">
		<table width="100%" cellpadding="4" cellspacing="1" border="0" class="bordercolor">
			<tr class="titlebg">
				<td colspan="3">', $txt['scheduled_log'], '</td>
			</tr>
			<tr class="catbg">
				<td colspan="3">', $context['page_index'], '</td>
			</tr>
			<tr class="titlebg">
				<td>', $txt['scheduled_tasks_name'], '</td>
				<td>', $txt['scheduled_log_time_run'], '</td>
				<td>', $txt['scheduled_log_time_taken'], '</td>
			</tr>';

	// Nothing?
	if (empty($context['log_entries']))
	{
		echo '
			<tr class="windowbg2">
				<td colspan="3" align="center">
					', $txt['scheduled_log_empty'], '
				</td>
			</tr>';
	}

	$alternate = 0;
	foreach ($context['log_entries'] as $entry)
	{
		echo '
			<tr class="', $alternate ? 'windowbg' : 'windowbg2', '">
				<td>
					', $entry['name'], '
				</td>
				<td>', $entry['time_run'], '</td>
				<td>', $entry['time_taken'], ' ', $txt['scheduled_log_time_taken_seconds'], '</td>
			</tr>';
		$alternate = !$alternate;
	}

	echo '
			<tr class="catbg">
				<td colspan="3">', $context['page_index'], '</td>
			</tr>
			<tr class="titlebg">
				<td colspan="3" align="right">
					<input type="hidden" name="sc" value="', $context['session_id'], '" />
					<input type="submit" name="deleteAll" value="', $txt['scheduled_log_empty_log'], '" />
				</td>
			</tr>
		</table>
	</form>';
}


function template_convert_utf8()
{
	global $context, $txt, $settings, $scripturl;

	echo '
		<table width="100%" cellpadding="5" cellspacing="0" border="0" class="tborder">
			<tr class="titlebg">
				<td>', $txt['utf8_title'], '</td>
			</tr><tr>
				<td class="windowbg2">
					', $txt['utf8_introduction'], '
				</td>
			</tr><tr>
				<td class="windowbg2">
					', $txt['utf8_warning'], '
				</td>
			</tr><tr>
				<td class="windowbg2">
					', $context['charset_about_detected'], isset($context['charset_warning']) ? ' <span style="color: red;">' . $context['charset_warning'] . '</span>' : '', '<br />
					<br />
				</td>
			</tr><tr>
				<td class="windowbg2" align="center">
					<form action="', $scripturl, '?action=admin;area=maintain;sa=convertutf8" method="post" accept-charset="', $context['character_set'], '">
						<table><tr>
							<th align="right">', $txt['utf8_source_charset'], ': </th>
							<td><select name="src_charset">';
	foreach ($context['charset_list'] as $charset)
		echo '
								<option value="', $charset, '"', $charset === $context['charset_detected'] ? ' selected="selected"' : '', '>', $charset, '</option>';
	echo '
							</select></td>
						</tr><tr>
							<th align="right">', $txt['utf8_database_charset'], ': </th>
							<td>', $context['database_charset'], '</td>
						</tr><tr>
							<th align="right">', $txt['utf8_target_charset'], ': </th>
							<td>', $txt['utf8_utf8'], '</td>
						</tr><tr>
							<td colspan="2" align="right"><br />
								<input type="submit" value="', $txt['utf8_proceed'], '" />
							</td>
						</tr></table>
						<input type="hidden" name="sc" value="', $context['session_id'], '" />
						<input type="hidden" name="proceed" value="1" />
					</form>
				</td>
			</tr>
		</table>';
}

function template_convert_entities()
{
	global $context, $txt, $settings, $scripturl;

	echo '
		<table width="100%" cellpadding="5" cellspacing="0" border="0" class="tborder">
			<tr class="titlebg">
				<td>', $txt['entity_convert_title'], '</td>
			</tr><tr>
				<td class="windowbg2">
					', $txt['entity_convert_introduction'], '
				</td>
			</tr>';
	if ($context['first_step'])
	{
		echo '
			<tr>
				<td class="windowbg2" align="center">
					<form action="', $scripturl, '?action=admin;area=maintain;sa=convertentities;start=0;sesc=', $context['session_id'], '" method="post" accept-charset="', $context['character_set'], '">
						<input type="submit" value="', $txt['entity_convert_proceed'], '" />
					</form>
				</td>
			</tr>';
	}
	else
	{
		echo '
			<tr>
				<td>
					<div style="padding-left: 20%; padding-right: 20%; margin-top: 1ex;">
						<div style="font-size: 8pt; height: 12pt; border: 1px solid black; background-color: white; padding: 1px; position: relative;">
							<div style="padding-top: ', $context['browser']['is_safari'] || $context['browser']['is_konqueror'] ? '2pt' : '1pt', '; width: 100%; z-index: 2; color: black; position: absolute; text-align: center; font-weight: bold;">', $context['percent_done'], '%</div>
							<div style="width: ', $context['percent_done'], '%; height: 12pt; z-index: 1; background-color: red;">&nbsp;</div>
						</div>
					</div>
					<form action="', $scripturl, $context['continue_get_data'], '" method="post" accept-charset="', $context['character_set'], '" style="margin: 0;" name="autoSubmit" id="autoSubmit">
						<div style="margin: 1ex; text-align: right;"><input type="submit" name="cont" value="', $txt['not_done_continue'], '" /></div>
					</form>
					<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
						var countdown = ', $context['last_step'] ? '-1' : '2', ';
						doAutoSubmit();

						function doAutoSubmit()
						{
							if (countdown == 0)
								document.forms.autoSubmit.submit();
							else if (countdown == -1)
								return;

							document.forms.autoSubmit.cont.value = "', $txt['not_done_continue'], ' (" + countdown + ")";
							countdown--;

							setTimeout("doAutoSubmit();", 1000);
						}
					// ]]></script>
				</td>
			</tr>';
	}
	echo '
		</table>';
}

?>