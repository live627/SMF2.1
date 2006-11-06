<?php
// Version: 2.0 Alpha; Packages

function template_main()
{
	global $context, $settings, $options;
}

function template_view_package()
{
	global $context, $settings, $options, $txt, $scripturl;

	echo '
		<table border="0" width="100%" cellspacing="1" cellpadding="4" class="bordercolor">
			<tr class="titlebg">
				<td>', $txt['smf159b'], '</td>
			</tr><tr>
				<td class="windowbg2">';

	if ($context['is_installed'])
		echo '
					<b>', $txt['package_installed_warning1'], '</b><br />
					<br />
					', $txt['package_installed_warning2'], '<br />
					<br />';

	echo '
					', $txt['package_installed_warning3'], '
				</td>
			</tr>
		</table>
		<br />';

	// Do errors exist in the install? If so light them up like a christmas tree.
	if ($context['has_failure'])
	{
		echo '
				<div style="margin: 2ex; padding: 2ex; border: 2px dashed #cc3344; color: black; background-color: #ffe4e9; margin-top: 0;">
					<div style="float: left; width: 2ex; font-size: 2em; color: red;">!!</div>
						<b style="text-decoration: underline;">', $txt['package_will_fail_title'], '</b><br />
						<div style="padding-left: 6ex;">
							', $txt['package_will_fail_warning'], '
						</div>
					</div>
				</div>';
	}

	if (isset($context['package_readme']))
		echo '
		<table border="0" width="100%" cellspacing="1" cellpadding="4" class="bordercolor">
			<tr class="titlebg">
				<td>', $txt['package_install_readme'], '</td>
			</tr><tr>
				<td class="windowbg2">', $context['package_readme'], '</td>
			</tr>
		</table>
		<br />';

	echo '
	<form action="', $scripturl, '?action=admin;area=packages;sa=', $context['uninstalling'] ? 'uninstall' : 'install', $context['ftp_needed'] ? '' : '2', ';package=', $context['filename'], ';pid=', $context['install_id'], '" method="post" accept-charset="', $context['character_set'], '">
		<table border="0" width="100%" cellspacing="1" cellpadding="4" class="bordercolor">
			<tr class="titlebg">
				<td>', $context['uninstalling'] ? $txt['package_uninstall_actions'] : $txt['package42'], '</td>
			</tr>
			<tr>
				<td class="catbg">', $context['uninstalling'] ? $txt['package_uninstall_actions'] : $txt['package_install_actions'], ' &quot;', $context['package_name'], '&quot;:</td>
			</tr><tr>
				<td class="windowbg2">';

	if (empty($context['actions']))
		echo '
					<b>', $txt['package45'], '</b>';
	else
	{
		echo '
					', $txt['package44'], '
					<table border="0" cellpadding="3" cellspacing="0" width="100%" style="margin-top: 1ex;">
						<tr>
							<td width="30"></td>
							<td><b>', $txt['package_install_type'], '</b></td>
							<td width="50%"><b>', $txt['package_install_action'], '</b></td>
							<td width="20%"><b>', $txt['package_install_desc'], '</b></td>
						</tr>';

		$alternate = true;
		foreach ($context['actions'] as $i => $packageaction)
		{
			echo '
						<tr class="windowbg', $alternate ? '' : '2', '">
							<td style="padding-right: 2ex;">', $i + 1, '.</td>
							<td style="padding-right: 2ex;">', $packageaction['type'], '</td>
							<td style="padding-right: 2ex;">', $packageaction['action'], '</td>
							<td style="padding-right: 2ex;">', $packageaction['description'], '</td>
						</tr>';
			$alternate = !$alternate;
		}
					echo '
					</table>
				</td>
			</tr>';

		// What if we have custom themes we can install into? List them too!
		if (!empty($context['theme_actions']))
		{
			echo '
			<tr class="catbg">
				<td><a href="#" onclick="return swap_theme_actions();"><img id="swap_theme_image" src="', $settings['images_url'], '/', (empty($context['themes_locked']) ? 'expand.gif' : 'collapse.gif'), '" /></a> ', $txt['package_other_themes'], '</td>
			</tr>
			<tr>
				<td class="windowbg2" id="custom_changes">
					<table border="0" cellpadding="3" cellspacing="0" width="100%">
						<tr class="windowbg2">
							<td colspan="4">
								<span class="smalltext">', $txt['package_other_themes_desc'], '</span>
							</td>
						</tr>';

			// Loop through each theme and display it's name, and then it's details.
			foreach ($context['theme_actions'] as $id => $theme)
			{
				echo '
						<tr class="titlebg">
							<td>';
				if (!empty($context['themes_locked']))
					echo '
								<input type="hidden" name="custom_theme[]" value="', $id, '" />';
				echo '
								<input type="checkbox" name="custom_theme[]" id="custom_theme_', $id, '" value="', $id, '" class="check" onclick="', ($theme['has_failure'] ? 'if (!confirm(\'' . $txt['package_theme_failure_warning'] . '\')) return false;' : ''), 'invertAll(this, this.form, \'dummy_theme_', $id, '\', true);" ', !empty($context['themes_locked']) ? 'disabled="disabled" checked="checked"' : '', '/>
							</td>
							<td colspan="3">
								', $theme['name'], '
							</td>
						</tr>';

				foreach ($theme['actions'] as $action)
				{
					echo '
						<tr class="windowbg', $alternate ? '' : '2', '">
							<td width="30" style="padding-right: 2ex;">
								<input type="checkbox" name="dummy_theme_', $id, '[]" id="dummy_theme_', $id, '[]" class="check" disabled="disabled" ', !empty($context['themes_locked']) ? 'checked="checked"' : '', '/>
							</td>
							<td style="padding-right: 2ex;">', $action['type'], '</td>
							<td width="50%" style="padding-right: 2ex;">', $action['action'], '</td>
							<td width="20%" style="padding-right: 2ex;"><b>', $action['description'], '</b></td>
						</tr>';

					$alternate = !$alternate;
				}
			}

			echo '
					</table>
				</td>
			</tr>';
		}
	}

	// Are we effectively ready to install?
	if (!$context['ftp_needed'] && !empty($context['actions']))
	{
		echo '
		<tr class="titlebg">
			<td align="right">
				<input type="submit" value="', $context['uninstalling'] ? $txt['package_uninstall_now'] : $txt['package_install_now'], '" ', $context['has_failure'] ? 'onclick="return confirm(\'' . $txt['package_will_fail_popup'] . '\');"' : '', '/>
			</td>
		</tr>';
	}
	// If we need ftp information then demand it!
	elseif ($context['ftp_needed'])
	{
		echo '
			<tr>
				<td class="catbg">', $txt['package_ftp_necessary'], '</td>
			</tr><tr>
				<td class="windowbg2">
					', $txt['package_ftp_why'];

		if (!empty($context['package_ftp']['error']))
			echo '
					<div class="bordercolor" style="padding: 1px; margin: 1ex;"><div class="windowbg" style="padding: 1ex;">
						<tt>', $context['package_ftp']['error'], '</tt>
					</div></div>';

		echo '
						<table width="520" cellpadding="0" cellspacing="0" border="0" align="center" style="margin-bottom: 1ex; margin-top: 2ex;">
							<tr>
								<td width="26%" valign="top" style="padding-top: 2px; padding-right: 2ex;"><label for="ftp_server">', $txt['package_ftp_server'], ':</label></td>
								<td style="padding-bottom: 1ex;">
									<div style="float: right; margin-right: 1px;"><label for="ftp_port" style="padding-top: 2px; padding-right: 2ex;">', $txt['package_ftp_port'], ':&nbsp;</label> <input type="text" size="3" name="ftp_port" id="ftp_port" value="', $context['package_ftp']['port'], '" /></div>
									<input type="text" size="30" name="ftp_server" id="ftp_server" value="', $context['package_ftp']['server'], '" style="width: 70%;" />
								</td>
							</tr><tr>
								<td width="26%" valign="top" style="padding-top: 2px; padding-right: 2ex;"><label for="ftp_username">', $txt['package_ftp_username'], ':</label></td>
								<td style="padding-bottom: 1ex;">
									<input type="text" size="50" name="ftp_username" id="ftp_username" value="', $context['package_ftp']['username'], '" style="width: 99%;" />
								</td>
							</tr><tr>
								<td width="26%" valign="top" style="padding-top: 2px; padding-right: 2ex;"><label for="ftp_password">', $txt['package_ftp_password'], ':</label></td>
								<td style="padding-bottom: 1ex;">
									<input type="password" size="50" name="ftp_password" id="ftp_password" style="width: 99%;" />
								</td>
							</tr><tr>
								<td width="26%" valign="top" style="padding-top: 2px; padding-right: 2ex;"><label for="ftp_path">', $txt['package_ftp_path'], ':</label></td>
								<td style="padding-bottom: 1ex;">
									<input type="text" size="50" name="ftp_path" id="ftp_path" value="', $context['package_ftp']['path'], '" style="width: 99%;" />
								</td>
							</tr>
						</table>
						<div align="right" style="margin: 1ex;"><input type="submit" value="', $txt['smf154'], '" /></div>
					</td>
				</tr>';
	}
		echo '
			</table>
		</form>';

	// Some javascript for collapsing/expanded theme section.
	if (!empty($context['theme_actions']))
		echo '
	<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
		var theme_action_area = document.getElementById(\'custom_changes\');
		var swap_theme_image = document.getElementById(\'swap_theme_image\');
		var vis = ', empty($context['themes_locked']) ? 'false' : 'true', ';
		theme_action_area.style.display = vis ? "" : "none";
		function swap_theme_actions()
		{
			vis = !vis;
			theme_action_area.style.display = vis ? "" : "none";
			swap_theme_image.src = "', $settings['images_url'], '/" + (vis ? "collapse" : "expand") + ".gif";
			return false;
		}
	// ]]></script>';
}

function template_extract_package()
{
	global $context, $settings, $options, $txt, $scripturl;

	if (!empty($context['redirect_url']))
	{
		echo '
	<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
		setTimeout("doRedirect();", ', empty($context['redirect_timeout']) ? '5000' : $context['redirect_timeout'], ');

		function doRedirect()
		{
			window.location = "', $context['redirect_url'], '";
		}
	// ]]></script>';
	}

	echo '
		<table border="0" width="100%" cellspacing="1" cellpadding="4" class="bordercolor">';

	if (empty($context['redirect_url']))
	{
		echo '
			<tr class="titlebg">
				<td>', $context['uninstalling'] ? $txt['smf198b'] : $txt['package37'], '</td>
			</tr>
			<tr>
				<td class="catbg">', $txt['package_installed_extract'], '</td>
			</tr>';
	}
	else
		echo '
			<tr class="titlebg">
				<td>', $txt['package_installed_redirecting'], '</td>
			</tr>';

	echo '
			<tr>
				<td class="windowbg2" width="100%">';

	// If we are going to redirect we have a slightly different agenda.
	if (!empty($context['redirect_url']))
	{
		echo '
					', $context['redirect_text'], '<br /><br />
				</td>
			</tr><tr>
				<td class="windowbg2" width="100%" align="center">
					<a href="', $context['redirect_url'], '">', $txt['package_installed_redirect_go_now'], '</a> | <a href="', $scripturl, '?action=admin;area=packages;sa=browse">', $txt['package_installed_redirect_cancel'], '</a>';
	}
	elseif ($context['uninstalling'])
		echo '
					', $txt['package_uninstall_done'];
	elseif ($context['install_finished'])
	{
		if ($context['extract_type'] == 'avatar')
			echo '
					', $txt['package39'];
		elseif ($context['extract_type'] == 'language')
			echo '
					', $txt['package41'];
		else
			echo '
					', $txt['package_installed_done'];
	}
	else
		echo '
					', $txt['package45'];

	echo '
				</td>
			</tr>
		</table>';
}

function template_list()
{
	global $context, $settings, $options, $txt, $scripturl;

	echo '
		<table border="0" width="100%" cellspacing="1" cellpadding="4" class="bordercolor">
			<tr class="titlebg">
				<td>', $txt['smf180'], '</td>
			</tr>
			<tr>
				<td class="catbg">', $txt['smf181'], ' ', $context['filename'], ':</td>
			</tr><tr>
				<td class="windowbg2" width="100%">
					<ol>';

	foreach ($context['files'] as $fileinfo)
		echo '
						<li><a href="', $scripturl, '?action=admin;area=packages;sa=examine;package=', $context['filename'], ';file=', $fileinfo['filename'], '" title="', $txt['view'], '">', $fileinfo['filename'], '</a> (', $fileinfo['size'], ' ', $txt['package_bytes'], ')</li>';

	echo '
					</ol>
					<a href="', $scripturl, '?action=admin;area=packages">[ ', $txt['back'], ' ]</a>
				</td>
			</tr>
		</table>';
}

function template_examine()
{
	global $context, $settings, $options, $txt, $scripturl;

	echo '
		<table border="0" width="100%" cellspacing="1" cellpadding="4" class="bordercolor" style="table-layout: fixed;">
			<tr class="titlebg">
				<td>', $txt['package_examine_file'], '</td>
			</tr>
			<tr>
				<td class="catbg">', $txt['package_file_contents'], ' ', $context['filename'], ':</td>
			</tr><tr>
				<td class="windowbg2" style="width: 100%;">
					<pre style="overflow: auto; width: 100%; padding-bottom: 1ex;">', $context['filedata'], '</pre>

					<a href="', $scripturl, '?action=admin;area=packages;sa=list;package=', $context['package'], '">[ ', $txt['package14'], ' ]</a>
				</td>
			</tr>
		</table>';
}

function template_view_installed()
{
	global $context, $settings, $options, $txt, $scripturl;

	echo '
		<table border="0" width="100%" cellspacing="1" cellpadding="4" class="bordercolor">
			<tr class="titlebg">
				<td>' . $txt['package6'] . '</td>
			</tr><tr>
				<td class="windowbg2">';

	if (empty($context['installed_mods']))
	{
		echo '
					<table border="0" cellpadding="1" cellspacing="0" width="100%">
						<tr>
							<td style="padding-bottom: 1ex;">', $txt['smf189b'], '</td>
						</tr>
					</table>';
	}
	else
	{
		echo '
					<table border="0" cellpadding="1" cellspacing="0" width="100%">
						<tr>
							<td width="32"></td>
							<td width="25%">', $txt['pacman2'], '</td>
							<td width="25%">', $txt['pacman3'], '</td>
							<td width="49%"></td>
						</tr>';

		foreach ($context['installed_mods'] as $i => $file)
			echo '
						<tr>
							<td>', ++$i, '.</td>
							<td>', $file['name'], '</td>
							<td>', $file['version'], '</td>
							<td align="right"><a href="', $scripturl, '?action=admin;area=packages;sa=uninstall;package=', $file['filename'], ';pid=', $file['id'], '">[ ', $txt['smf198b'], ' ]</a></td>
						</tr>';

		echo '
					</table>
					<br />
					<a href="', $scripturl, '?action=admin;area=packages;sa=flush">[ ', $txt['smf198d'], ' ]</a>';
	}

	echo '
				</td>
			</tr>
		</table>';
}

function template_browse()
{
	global $context, $settings, $options, $txt, $scripturl, $modSettings;

	echo '
		<table width="100%" cellspacing="0" cellpadding="4" border="0" class="tborder">
			<tr class="titlebg">
				<td><a href="', $scripturl, '?action=helpadmin;help=latest_packages" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt['help'], '" align="top" /></a> ', $txt['packages_latest'], '</td>
			</tr>
			<tr>
				<td class="windowbg2" id="packagesLatest">', $txt['packages_latest_fetch'], '</td>
			</tr>
		</table>
		<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
			window.smfForum_scripturl = "', $scripturl, '";
			window.smfForum_sessionid = "', $context['session_id'], '";';

	// Make a list of already installed mods so nothing is listed twice ;).
	echo '
			window.smfInstalledPackages = ["', implode('", "', $context['installed_mods']), '"];
			window.smfVersion = "', $context['forum_version'], '";
		// ]]></script>';

	if (empty($modSettings['disable_smf_js']))
		echo '
		<script language="JavaScript" type="text/javascript" src="', $scripturl, '?action=viewsmfile;filename=latest-packages.js"></script>';

	echo '
		<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
			var tempOldOnload;

			function smfSetLatestPackages()
			{
				if (typeof(window.smfLatestPackages) != "undefined")
					setInnerHTML(document.getElementById("packagesLatest"), window.smfLatestPackages);

				if (tempOldOnload)
					tempOldOnload();
			}
		// ]]></script>';

	// Gotta love IE4, and its hatefulness...
	if ($context['browser']['is_ie4'])
		echo '
		<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
			tempOldOnload = window.onload;
			window.onload = smfSetLatestPackages;
		// ]]></script>';
	else
		echo '
		<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
			smfSetLatestPackages();
		// ]]></script>';

	echo '
		<br />

		<table border="0" width="100%" cellspacing="1" cellpadding="4" class="bordercolor">
			<tr class="titlebg">
				<td>', $txt['package3'], '</td>
			</tr>';

	if (!empty($context['available_mods']))
	{
		echo '
			<tr>
				<td class="catbg">', $txt['package7'], '</td>
			</tr><tr>
				<td class="windowbg2">
					<table border="0" cellpadding="1" cellspacing="0" width="100%">
						<tr>
							<td width="32"></td>
							<td width="25%">', $txt['pacman2'], '</td>
							<td width="25%">', $txt['pacman3'], '</td>
							<td width="49%"></td>
						</tr>';

		foreach ($context['available_mods'] as $i => $package)
		{
			echo '
						<tr>
							<td>', ++$i, '.</td>
							<td>', $package['name'], '</td>
							<td>
								', $package['version'];

			if ($package['is_installed'] && !$package['is_newer'])
				echo '
								<img src="', $settings['images_url'], '/icons/package_', $package['is_current'] ? 'installed' : 'old', '.gif" alt="" width="12" height="11" align="middle" style="margin-left: 2ex;" />';

			echo '
							</td>
							<td align="right">';

		if ($package['can_uninstall'])
			echo '
								<a href="', $scripturl, '?action=admin;area=packages;sa=uninstall;package=', $package['filename'], ';pid=', $package['installed_id'], '">[ ', $txt['smf198b'], ' ]</a>';
		elseif ($package['can_upgrade'])
			echo '
								<a href="', $scripturl, '?action=admin;area=packages;sa=install;package=', $package['filename'], '">[ ', $txt['package_upgrade'], ' ]</a>';
		elseif ($package['can_install'])
			echo '
								<a href="', $scripturl, '?action=admin;area=packages;sa=install;package=', $package['filename'], '">[ ', $txt['package11'], ' ]</a>';

		echo '
								<a href="', $scripturl, '?action=admin;area=packages;sa=list;package=', $package['filename'], '">[ ', $txt['package14'], ' ]</a>
								<a href="', $scripturl, '?action=admin;area=packages;sa=remove;package=', $package['filename'], '"', $package['is_installed'] && $package['is_current'] ? ' onclick="return confirm(\'' . $txt['package_delete_bad'] . '\');"' : '', '>[ ', $txt['package52'], ' ]</a>
							</td>
						</tr>';
		}

		echo '
					</table>
				</td>
			</tr>';
	}

	if (!empty($context['available_avatars']))
	{
		echo '
			<tr>
				<td class="catbg">', $txt['package8'], '</td>
			</tr><tr>
				<td class="windowbg2">
					<table border="0" cellpadding="1" cellspacing="0" width="100%">
						<tr>
							<td width="32"></td>
							<td width="25%">', $txt['pacman2'], '</td>
							<td width="25%">', $txt['pacman3'], '</td>
							<td width="49%"></td>
						</tr>';

		foreach ($context['available_avatars'] as $i => $package)
		{
			echo '
						<tr>
							<td>', ++$i, '.</td>
							<td>', $package['name'], '</td>
							<td>', $package['version'];

			if ($package['is_installed'] && !$package['is_newer'])
				echo '
								<img src="', $settings['images_url'], '/icons/package_', $package['is_current'] ? 'installed' : 'old', '.gif" alt="" width="12" height="11" align="middle" style="margin-left: 2ex;" />';

			echo '
							</td>
							<td align="right">';

		if ($package['can_uninstall'])
			echo '
								<a href="', $scripturl, '?action=admin;area=packages;sa=uninstall;package=', $package['filename'], ';pid=', $package['installed_id'], '">[ ', $txt['smf198b'], ' ]</a>';
		elseif ($package['can_upgrade'])
			echo '
								<a href="', $scripturl, '?action=admin;area=packages;sa=install;package=', $package['filename'], '">[ ', $txt['package_upgrade'], ' ]</a>';
		elseif ($package['can_install'])
			echo '
								<a href="', $scripturl, '?action=admin;area=packages;sa=install;package=', $package['filename'], '">[ ', $txt['package11'], ' ]</a>';

		echo '
								<a href="', $scripturl, '?action=admin;area=packages;sa=list;package=', $package['filename'], '">[ ', $txt['package14'], ' ]</a>
								<a href="', $scripturl, '?action=admin;area=packages;sa=remove;package=', $package['filename'], '">[ ', $txt['package52'], ' ]</a>
							</td>
						</tr>';
		}

		echo '
					</table>
				</td>
			</tr>';
	}

	if (!empty($context['available_languages']))
	{
		echo '
			<tr>
				<td class="catbg">' . $txt['package9'] . '</td>
			</tr><tr>
				<td class="windowbg2">
					<table border="0" cellpadding="1" cellspacing="0" width="100%">
						<tr>
							<td width="32"></td>
							<td width="25%">' . $txt['pacman2'] . '</td>
							<td width="25%">' . $txt['pacman3'] . '</td>
							<td width="49%"></td>
						</tr>';

		foreach ($context['available_languages'] as $i => $package)
		{
			echo '
						<tr>
							<td>' . ++$i . '.</td>
							<td>' . $package['name'] . '</td>
							<td>' . $package['version'];

			if ($package['is_installed'] && !$package['is_newer'])
				echo '
								<img src="', $settings['images_url'], '/icons/package_', $package['is_current'] ? 'installed' : 'old', '.gif" alt="" width="12" height="11" align="middle" style="margin-left: 2ex;" />';

			echo '
							</td>
							<td align="right">';

		if ($package['can_uninstall'])
			echo '
								<a href="', $scripturl, '?action=admin;area=packages;sa=uninstall;package=', $package['filename'], ';pid=', $package['installed_id'], '">[ ', $txt['smf198b'], ' ]</a>';
		elseif ($package['can_upgrade'])
			echo '
								<a href="', $scripturl, '?action=admin;area=packages;sa=install;package=', $package['filename'], '">[ ', $txt['package_upgrade'], ' ]</a>';
		elseif ($package['can_install'])
			echo '
								<a href="', $scripturl, '?action=admin;area=packages;sa=install;package=', $package['filename'], '">[ ', $txt['package11'], ' ]</a>';

		echo '
								<a href="', $scripturl, '?action=admin;area=packages;sa=list;package=', $package['filename'], '">[ ', $txt['package14'], ' ]</a>
								<a href="', $scripturl, '?action=admin;area=packages;sa=remove;package=', $package['filename'], '">[ ', $txt['package52'], ' ]</a>
							</td>
						</tr>';
		}

		echo '
					</table>
				</td>
			</tr>';
	}

	if (!empty($context['available_other']))
	{
		echo '
			<tr>
				<td class="catbg">' . $txt['package10'] . '</td>
			</tr><tr>
				<td class="windowbg2">
					<table border="0" cellpadding="1" cellspacing="0" width="100%">
						<tr>
							<td width="32"></td>
							<td width="25%">' . $txt['pacman2'] . '</td>
							<td width="25%">' . $txt['pacman3'] . '</td>
							<td width="49%"></td>
						</tr>';

		foreach ($context['available_other'] as $i => $package)
		{
			echo '
						<tr>
							<td>' . ++$i . '.</td>
							<td>' . $package['name'] . '</td>
							<td>' . $package['version'];

			if ($package['is_installed'] && !$package['is_newer'])
				echo '
								<img src="', $settings['images_url'], '/icons/package_', $package['is_current'] ? 'installed' : 'old', '.gif" alt="" width="12" height="11" align="middle" style="margin-left: 2ex;" />';

			echo '
							</td>
							<td align="right">';

		if ($package['can_uninstall'])
			echo '
								<a href="', $scripturl, '?action=admin;area=packages;sa=uninstall;package=', $package['filename'], ';pid=', $package['installed_id'], '">[ ', $txt['smf198b'], ' ]</a>';
		elseif ($package['can_upgrade'])
			echo '
								<a href="', $scripturl, '?action=admin;area=packages;sa=install;package=', $package['filename'], '">[ ', $txt['package_upgrade'], ' ]</a>';
		elseif ($package['can_install'])
			echo '
								<a href="', $scripturl, '?action=admin;area=packages;sa=install;package=', $package['filename'], '">[ ', $txt['package11'], ' ]</a>';

		echo '
								<a href="', $scripturl, '?action=admin;area=packages;sa=list;package=', $package['filename'], '">[ ', $txt['package14'], ' ]</a>
								<a href="', $scripturl, '?action=admin;area=packages;sa=remove;package=', $package['filename'], '"', $package['is_installed'] ? ' onclick="return confirm(\'' . $txt['package_delete_bad'] . '\');"' : '', '>[ ', $txt['package52'], ' ]</a>
							</td>
						</tr>';
		}

		echo '
					</table>
				</td>
			</tr>';
	}

	if (empty($context['available_mods']) && empty($context['available_avatars']) && empty($context['available_languages']) && empty($context['available_other']))
		echo '
			<tr>
				<td class="windowbg2">', $txt['smf189'], '</td>
			</tr>';

	echo '
		</table>
		<table border="0" width="100%" cellspacing="1" cellpadding="4">
			<tr>
				<td class="smalltext">
					', $txt['package_installed_key'], '
					<img src="', $settings['images_url'], '/icons/package_installed.gif" alt="" width="12" height="11" align="middle" style="margin-left: 1ex;" /> ', $txt['package_installed_current'], '
					<img src="', $settings['images_url'], '/icons/package_old.gif" alt="" width="12" height="11" align="middle" style="margin-left: 2ex;" /> ', $txt['package_installed_old'], '
				</td>
			</tr>
		</table>';
}

function template_servers()
{
	global $context, $settings, $options, $txt, $scripturl;

	echo '
		<table border="0" width="100%" cellspacing="1" cellpadding="4" class="bordercolor">
			<tr class="titlebg">
				<td>', $txt['package5'], '</td>
			</tr>';

	if ($context['package_download_broken'])
	{
		echo '
			<tr>
				<td class="catbg">', $txt['package_ftp_necessary'], '</td>
			</tr><tr>
				<td class="windowbg2">
					', $txt['package_ftp_why_download'];

		if (!empty($context['package_ftp']['error']))
			echo '
					<div class="bordercolor" style="padding: 1px; margin: 1ex;"><div class="windowbg" style="padding: 1ex;">
						<tt>', $context['package_ftp']['error'], '</tt>
					</div></div>';

		echo '
					<form action="', $scripturl, '?action=admin;area=packages;get" method="post" accept-charset="', $context['character_set'], '">
						<table width="520" cellpadding="0" cellspacing="0" border="0" align="center" style="margin-bottom: 1ex; margin-top: 2ex;">
							<tr>
								<td width="26%" valign="top" style="padding-top: 2px; padding-right: 2ex;"><label for="ftp_server">', $txt['package_ftp_server'], ':</label></td>
								<td style="padding-bottom: 1ex;">
									<div style="float: right; margin-right: 1px;"><label for="ftp_port" style="padding-top: 2px; padding-right: 2ex;">', $txt['package_ftp_port'], ':&nbsp;</label> <input type="text" size="3" name="ftp_port" id="ftp_port" value="', $context['package_ftp']['port'], '" /></div>
									<input type="text" size="30" name="ftp_server" id="ftp_server" value="', $context['package_ftp']['server'], '" style="width: 70%;" />
								</td>
							</tr><tr>
								<td width="26%" valign="top" style="padding-top: 2px; padding-right: 2ex;"><label for="ftp_username">', $txt['package_ftp_username'], ':</label></td>
								<td style="padding-bottom: 1ex;">
									<input type="text" size="50" name="ftp_username" id="ftp_username" value="', $context['package_ftp']['username'], '" style="width: 99%;" />
								</td>
							</tr><tr>
								<td width="26%" valign="top" style="padding-top: 2px; padding-right: 2ex;"><label for="ftp_password">', $txt['package_ftp_password'], ':</label></td>
								<td style="padding-bottom: 1ex;">
									<input type="password" size="50" name="ftp_password" id="ftp_password" style="width: 99%;" />
								</td>
							</tr><tr>
								<td width="26%" valign="top" style="padding-top: 2px; padding-right: 2ex;"><label for="ftp_path">', $txt['package_ftp_path'], ':</label></td>
								<td style="padding-bottom: 1ex;">
									<input type="text" size="50" name="ftp_path" id="ftp_path" value="', $context['package_ftp']['path'], '" style="width: 99%;" />
								</td>
							</tr>
						</table>
						<div align="right" style="margin-right: 1ex;"><input type="submit" value="', $txt['smf154'], '" /></div>
					</form>
				</td>
			</tr>';
	}

	echo '
			<tr>
				<td class="catbg">' . $txt['smf183'] . '</td>
			</tr><tr>
				<td class="windowbg2">
					<table border="0" cellpadding="1" cellspacing="0" width="100%">';
	foreach ($context['servers'] as $server)
		echo '
						<tr>
							<td>
								' . $server['name'] . '
							</td>
							<td>
								<a href="' . $scripturl . '?action=admin;area=packages;get;sa=browse;server=' . $server['id'] . '">[ ' . $txt['smf184'] . ' ]</a>
							</td>
							<td>
								<a href="' . $scripturl . '?action=admin;area=packages;get;sa=remove;server=' . $server['id'] . '">[ ' . $txt['smf138'] . ' ]</a>
							</td>
						</tr>';
	echo '
					</table>
					<br />
				</td>
			</tr><tr>
				<td class="catbg">' . $txt['smf185'] . '</td>
			</tr><tr>
				<td class="windowbg2">
					<form action="' . $scripturl . '?action=admin;area=packages;get;sa=add" method="post" accept-charset="', $context['character_set'], '">
						<table border="0" cellspacing="0" cellpadding="4">
							<tr>
								<td valign="top"><b>' . $txt['smf186'] . ':</b></td>
								<td valign="top"><input type="text" name="servername" size="40" value="SMF" /></td>
							</tr><tr>
								<td valign="top"><b>' . $txt['smf187'] . ':</b></td>
								<td valign="top"><input type="text" name="serverurl" size="50" value="http://" /></td>
							</tr><tr>
								<td colspan="2"><input type="submit" value="' . $txt['smf185'] . '" /></td>
							</tr>
						</table>
						<input type="hidden" name="sc" value="' . $context['session_id'] . '" />
					</form>
				</td>
			</tr><tr>
				<td class="catbg">', $txt['package_download_by_url'], '</td>
			</tr>
				<td class="windowbg2">
					<form action="', $scripturl, '?action=admin;area=packages;get;sa=download;byurl;sesc=', $context['session_id'], '" method="post" accept-charset="', $context['character_set'], '">
						<table border="0" cellspacing="0" cellpadding="4">
							<tr>
								<td valign="top"><b>' . $txt['smf187'] . ':</b></td>
								<td valign="top"><input type="text" name="package" size="50" value="http://" /></td>
							</tr><tr>
								<td valign="top"><b>', $txt['package_download_filename'], ':</b></td>
								<td valign="top">
									<input type="text" name="filename" size="50" /><br />
									<span class="smalltext">', $txt['package_download_filename_info'], '</span>
								</td>
							</tr><tr>
								<td colspan="2"><input type="submit" value="', $txt['smf190'], '" /></td>
							</tr>
						</table>
					</form>
				</td>
			</tr>
		</table>
		<br />
		<table width="100%" cellpadding="4" cellspacing="1" border="0" class="bordercolor">
			<tr class="titlebg">
				<td>' . $txt['package_upload_title'] . '</td>
			</tr><tr>
				<td class="windowbg2" style="padding: 8px;">
					<form action="' . $scripturl . '?action=admin;area=packages;get;sa=upload" method="post" accept-charset="', $context['character_set'], '" enctype="multipart/form-data" style="margin-bottom: 0;">
						<b>' . $txt['package_upload_select'] . ':</b> <input type="file" name="package" size="38" />
						<div style="margin: 1ex;" align="right"><input type="submit" value="' . $txt['package_upload'] . '" /></div>
						<input type="hidden" name="sc" value="' . $context['session_id'] . '" />
					</form>
				</td>
			</tr>
		</table>';
}

function template_package_list()
{
	global $context, $settings, $options, $txt, $scripturl, $smfFunc;

	echo '
		<table border="0" width="100%" cellspacing="1" cellpadding="4" class="bordercolor">
			<tr class="titlebg">
				<td>' . $context['page_title'] . '</td>
			</tr>
			<tr>
				<td width="100%" align="left" valign="middle" class="windowbg2">';

	// No packages, as yet.
	if (empty($context['package_list']))
		echo '
					<ul>
						<li>', $txt['smf189'], '</li>
					</ul>';
	// List out the packages...
	else
	{
		foreach ($context['package_list'] as $i => $packageSection)
		{
			echo '
			<h2><a href="#" onclick="ps_', $i, '.toggle(); return false;"><img id="ps_img_', $i, '" src="', $settings['images_url'], '/blank.gif" alt="*" /></a> ', $packageSection['title'], '</h2>';
			
			if (!empty($packageSection['text']))
				echo '
			<h3>', $packageSection['text'], '</h3>';

			echo '
			<div id="package_section_', $i, '" class="tborder">';

			$alt = false;

			foreach($packageSection['items'] AS $id => $package)
			{
				// Textual message. Could be empty just for a blank line...
				if ($package['is_text'])
					echo '
					', $package['name'], '<br /><br />';
				// This is supposed to be a rule..
				elseif ($package['is_line'])
					echo '
					<hr />';
				// A remote link.
				elseif ($package['is_remote'])
					echo '
					<b>', $package['link'], '</b><br /><br />';
				// Otherwise, it's a package.
				else
				{
					echo '
					<div class="windowbg', $alt ? '2' : '', '">';
					// 1. Some mod [ Download ].
					echo '
						<span style="font-size: larger;">
						<a href="#" onclick="ps_', $i, '_pkg_', $id, '.toggle(); return false;"><img id="ps_img_', $i, '_pkg_', $id, '" src="', $settings['images_url'], '/blank.gif" alt="*" /></a> ', $package['count'], '. ', $package['can_install'] ? '<b>' . $package['name'] . '</b> <a href="' . $package['download']['href'] . '">[ ' . $txt['smf190'] . ' ]</a>': $package['name'];

					// Mark as installed and current?
					if ($package['is_installed'] && !$package['is_newer'])
						echo '<img src="', $settings['images_url'], '/icons/package_', $package['is_current'] ? 'installed' : 'old', '.gif" width="12" height="11" align="middle" style="margin-left: 2ex;" alt="', $package['is_current'] ? $txt['package_installed_current'] : $txt['package_installed_old'], '" />';

					echo '</span>
						<div id="package_section_', $i, '_pkg_', $id, '">';

					// Show the mod type?
					if ($package['type'] != '')
						echo '
						', $txt['package24'], ':&nbsp; ', $smfFunc['ucwords']($smfFunc['strtolower']($package['type'])), '<br />';
					// Show the version number?
					if ($package['version'] != '')
						echo '
						', $txt['pacman3'], ':&nbsp; ', $package['version'], '<br />';
					// How 'bout the author?
					if (!empty($package['author']) && $package['author']['name'] != '' && isset($package['author']['link']))
						echo '
						', $txt['pacman4'], ':&nbsp; ', $package['author']['link'], '<br />';
					// The homepage....
					if ($package['author']['website']['link'] != '')
						echo '
						', $txt['pacman6'], ':&nbsp; ', $package['author']['website']['link'], '<br />';

					// Desciption: bleh bleh!
					// Location of file: http://someplace/.
					echo '
						', $txt['pacman10'], ':&nbsp; <a href="', $package['href'], '">', $package['href'], '</a>
							<div style="max-height: 15em; overflow: auto;">', $txt['pacman9'], ':&nbsp; ', $package['description'], '</div>
						</div>
						<br />
					</div>';
					$alt = !$alt;
				}
			}
			echo '
				</div>';

		}
		echo '
					<br />';

	}

	echo '
					</div>
					</td>
				</tr>
			</table>
			<table border="0" width="100%" cellspacing="1" cellpadding="4">
				<tr>
					<td class="smalltext">
						', $txt['package_installed_key'], '
						<img src="', $settings['images_url'], '/icons/package_installed.gif" alt="" width="12" height="11" align="middle" style="margin-left: 1ex;" /> ', $txt['package_installed_current'], '
						<img src="', $settings['images_url'], '/icons/package_old.gif" alt="" width="12" height="11" align="middle" style="margin-left: 2ex;" /> ', $txt['package_installed_old'], '
					</td>
				</tr>
			</table>';
		// Now go through and turn off all the sections.
		if (!empty($context['package_list']))
		{
			echo '
			<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[';
			foreach($context['package_list'] as $section => $ps)
			{
				echo '

					var ps_', $section, ' = new smfToggle("package_section_', $section, '", false);
					ps_', $section, '.useCookie(0);
					ps_', $section, '.addToggleImage("ps_img_', $section, '", "/upshrink.gif", "/upshrink2.gif");
					ps_', $section, '.addTogglePanel("package_section_', $section, '");
					ps_', $section, '.toggle();';

				foreach($ps['items'] AS $id => $package)
				{
					if (!$package['is_text'] && !$package['is_line'] && !$package['is_remote'])
						echo '

						var ps_', $section, '_pkg_', $id, ' = new smfToggle("package_section_', $section, '_pkg_', $id, '", false);
						ps_', $section, '_pkg_', $id, '.useCookie(0);
						ps_', $section, '_pkg_', $id, '.addToggleImage("ps_img_', $section, '_pkg_', $id, '", "/upshrink.gif", "/upshrink2.gif");
						ps_', $section, '_pkg_', $id, '.addTogglePanel("package_section_', $section, '_pkg_', $id, '");
						ps_', $section, '_pkg_', $id, '.toggle()';
				}
			}
			echo '
			// ]]></script>';
		}
}

function template_downloaded()
{
	global $context, $settings, $options, $txt, $scripturl;

	echo '
		<table border="0" width="100%" cellspacing="1" cellpadding="4" class="bordercolor">
			<tr class="titlebg">
				<td>' . $context['page_title'] . '</td>
			</tr>
			<tr>
				<td width="100%" align="left" valign="middle" class="windowbg2">
					' . (!isset($context['package_server']) ? $txt['package_uploaded_successfully'] : $txt['smf193']) . '<br /><br />
					<table border="0" cellpadding="1" cellspacing="0" width="100%">
						<tr>
							<td valign="middle">' . $context['package']['name'] . '</td>
							<td align="right" valign="middle">
								' . $context['package']['install']['link'] . '
								' . $context['package']['list_files']['link'] . '
							</td>
						</tr>
					</table>
					<br />
					<a href="' . $scripturl . '?action=admin;area=packages;get' . (isset($context['package_server']) ? ';sa=browse;server=' . $context['package_server'] : '') . '">[ ' . $txt['back'] . ' ]</a>
				</td>
			</tr>
		</table>';
}

function template_install_options()
{
	global $context, $settings, $options, $txt, $scripturl;

	echo '
		<div class="tborder">
			<div class="titlebg" style="padding: 4px;">', $txt['package_install_options'], '</div>
			<div class="windowbg" style="padding: 1ex;">
				<span class="smalltext">', $txt['package_install_options_ftp_why'], '</span>
			</div>

			<div class="windowbg2" style="padding: 4px;">
				<form action="', $scripturl, '?action=admin;area=packages;sa=options" method="post" accept-charset="', $context['character_set'], '">
					<div style="margin-top: 1ex;"><label for="pack_server" style="padding: 2px 0 0 4pt; float: left; width: 20ex; font-weight: bold;">', $txt['package_install_options_ftp_server'], ':</label> <input type="text" name="pack_server" id="pack_server" value="', $context['package_ftp_server'], '" size="30" /> <label for="pack_port" style="padding-left: 4pt; font-weight: bold;">', $txt['package_install_options_ftp_port'], ':</label> <input type="text" name="pack_port" id="pack_port" size="3" value="', $context['package_ftp_port'], '" /></div>
					<div style="margin-top: 1ex;"><label for="pack_user" style="padding: 2px 0 0 4pt; float: left; width: 20ex; font-weight: bold;">', $txt['package_install_options_ftp_user'], ':</label> <input type="text" name="pack_user" id="pack_user" value="', $context['package_ftp_username'], '" size="30" /></div>
					<br />

					<label for="package_make_backups"><input type="checkbox" name="package_make_backups" id="package_make_backups" value="1" class="check"', $context['package_make_backups'] ? ' checked="checked"' : '', ' /> ', $txt['package_install_options_make_backups'], '</label><br />
					<div align="center" style="padding-top: 2ex; padding-bottom: 1ex;"><input type="submit" name="submit" value="', $txt['save'], '" /></div>
					<input type="hidden" name="sc" value="', $context['session_id'], '" />
				</form>
			</div>
		</div>

		<div class="tborder" style="margin-top: 2ex;">
			<div class="titlebg" style="padding: 4px;">', $txt['package_cleanperms_title'], '</div>
			<div class="windowbg" style="padding: 1ex;">
				<span class="smalltext">', $txt['package_cleanperms_desc'], '</span>
			</div>

			<div class="windowbg2" style="padding: 4px;">
				<form action="', $scripturl, '?action=admin;area=cleanperms" method="post" accept-charset="', $context['character_set'], '">
					', $txt['package_cleanperms_type'], ':<br />
					<br />
					<label for="perm_type_standard"><input type="radio" name="perm_type" id="perm_type_standard" value="standard" checked="checked" class="check" /> ', $txt['package_cleanperms_standard'], '</label><br />
					<label for="perm_type_free"><input type="radio" name="perm_type" id="perm_type_free" value="free" class="check" /> ', $txt['package_cleanperms_free'], '</label><br />
					<label for="perm_type_restrictive"><input type="radio" name="perm_type" id="perm_type_restrictive" value="restrictive" class="check" /> ', $txt['package_cleanperms_restrictive'], '</label><br />

					<div align="center" style="padding-top: 2ex; padding-bottom: 1ex;"><input type="submit" name="submit" value="', $txt['package_cleanperms_go'], '" /></div>
					<input type="hidden" name="sc" value="', $context['session_id'], '" />
				</form>
			</div>
		</div>';
}

function template_ftp_required()
{
	global $context, $settings, $options, $txt, $scripturl;

	echo '
		<div class="tborder">
			<div class="titlebg" style="padding: 4px;">', $txt['package_ftp_necessary'], '</div>
			<div class="windowbg" style="padding: 4px;">
				', $txt['package_ftp_why'];

	if (!empty($context['package_ftp']['error']))
		echo '
				<div class="bordercolor" style="padding: 1px; margin: 1ex;"><div class="windowbg2" style="padding: 1ex;">
					<tt>', $context['package_ftp']['error'], '</tt>
				</div></div>';

	echo '
				<form action="', $context['package_ftp']['destination'], '" method="post" accept-charset="', $context['character_set'], '" style="margin: 0;">
					<table width="520" cellpadding="0" cellspacing="0" border="0" align="center" style="margin-bottom: 1ex; margin-top: 2ex;">
						<tr>
							<td width="26%" valign="top" style="padding-top: 2px; padding-right: 2ex;"><label for="ftp_server">', $txt['package_ftp_server'], ':</label></td>
							<td style="padding-bottom: 1ex;">
								<div style="float: right; margin-right: 1px;"><label for="ftp_port" style="padding-top: 2px; padding-right: 2ex;">', $txt['package_ftp_port'], ':&nbsp;</label> <input type="text" size="3" name="ftp_port" id="ftp_port" value="', $context['package_ftp']['port'], '" /></div>
								<input type="text" size="30" name="ftp_server" id="ftp_server" value="', $context['package_ftp']['server'], '" style="width: 70%;" />
							</td>
						</tr><tr>
							<td width="26%" valign="top" style="padding-top: 2px; padding-right: 2ex;"><label for="ftp_username">', $txt['package_ftp_username'], ':</label></td>
							<td style="padding-bottom: 1ex;">
								<input type="text" size="50" name="ftp_username" id="ftp_username" value="', $context['package_ftp']['username'], '" style="width: 99%;" />
							</td>
						</tr><tr>
							<td width="26%" valign="top" style="padding-top: 2px; padding-right: 2ex;"><label for="ftp_password">', $txt['package_ftp_password'], ':</label></td>
							<td style="padding-bottom: 1ex;">
								<input type="password" size="50" name="ftp_password" id="ftp_password" style="width: 99%;" />
							</td>
						</tr><tr>
							<td width="26%" valign="top" style="padding-top: 2px; padding-right: 2ex;"><label for="ftp_path">', $txt['package_ftp_path'], ':</label></td>
							<td style="padding-bottom: 1ex;">
								<input type="text" size="50" name="ftp_path" id="ftp_path" value="', $context['package_ftp']['path'], '" style="width: 99%;" />
							</td>
						</tr>
					</table>

					<div align="right" style="margin: 1ex;"><input type="submit" value="', $txt['smf154'], '" /></div>
					<input type="hidden" name="sc" value="', $context['session_id'], '" />
				</form>
			</div></div>';
}

?>