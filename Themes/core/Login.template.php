<?php
// Version: 2.0 RC2; Login

// This is just the basic "login" form.
function template_login()
{
	global $context, $settings, $options, $scripturl, $modSettings, $txt;

	echo '
		<script type="text/javascript" src="', $settings['default_theme_url'], '/scripts/sha1.js"></script>

		<form action="', $scripturl, '?action=login2" name="frmLogin" id="frmLogin" method="post" accept-charset="', $context['character_set'], '" style="margin-top: 4ex;"', empty($context['disable_login_hashing']) ? ' onsubmit="hashLoginPassword(this, \'' . $context['session_id'] . '\');"' : '', '>
			<table border="0" width="400" cellspacing="0" cellpadding="4" class="tborder" align="center">
				<tr class="titlebg">
					<td colspan="2">
						<img src="', $settings['images_url'], '/icons/login_sm.gif" alt="" align="top" /> ', $txt['login'], '
					</td>';

	// Did they make a mistake last time?
	if (!empty($context['login_errors']))
		foreach ($context['login_errors'] as $error)
			echo '
				</tr><tr class="windowbg">
					<td align="center" colspan="2" style="padding: 1ex;">
						<strong class="error">', $error, '</strong>
					</td>';

	// Or perhaps there's some special description for this time?
	if (isset($context['description']))
		echo '
				</tr><tr class="windowbg">
					<td align="center" colspan="2">
						<strong>', $context['description'], '</strong><br />
						<br />
					</td>';

	// Now just get the basic information - username, password, etc.
	echo '
				</tr><tr class="windowbg">
					<td width="50%" align="right"><strong>', $txt['username'], ':</strong></td>
					<td><input type="text" name="user" size="20" value="', $context['default_username'], '" class="input_text" /></td>
				</tr><tr class="windowbg">
					<td align="right"><strong>', $txt['password'], ':</strong></td>
					<td><input type="password" name="passwrd" value="', $context['default_password'], '" size="20" class="input_password" /></td>
				</tr>';

	if (!empty($modSettings['enableOpenID']))
		echo '<tr class="windowbg">
					<td colspan="2" align="center"><strong>&mdash;', $txt['or'], '&mdash;</strong></td>
				</tr><tr class="windowbg">
					<td align="right"><strong>', $txt['openid'], ':</strong></td>
					<td>
						<input type="text" name="openid_url" class="input_text openid_login" size="17" />&nbsp;<em><a href="', $scripturl, '?action=helpadmin;help=register_openid" onclick="return reqWin(this.href);" class="help">(?)</a></em>
					</td>
				</tr><tr class="windowbg">
					<td colspan="2" align="center"><hr /></td>
				</tr>';

	echo '<tr class="windowbg">
					<td align="right"><strong>', $txt['mins_logged_in'], ':</strong></td>
					<td><input type="text" name="cookielength" size="4" maxlength="4" value="', $modSettings['cookieTime'], '"', $context['never_expire'] ? ' disabled="disabled"' : '', ' class="input_text" /></td>
				</tr><tr class="windowbg">
					<td align="right"><strong>', $txt['always_logged_in'], ':</strong></td>
					<td><input type="checkbox" name="cookieneverexp"', $context['never_expire'] ? ' checked="checked"' : '', ' class="input_check" onclick="this.form.cookielength.disabled = this.checked;" /></td>
				</tr><tr class="windowbg">';
	// If they have deleted their account, give them a chance to change their mind.
	if (isset($context['login_show_undelete']))
		echo '
					<td align="right"><strong class="alert">', $txt['undelete_account'], ':</strong></td>
					<td><input type="checkbox" name="undelete" class="input_check" /></td>
				</tr><tr class="windowbg">';
	echo '
					<td align="center" colspan="2"><input type="submit" value="', $txt['login'], '" style="margin-top: 2ex;" class="button_submit" /></td>
				</tr><tr class="windowbg">
					<td align="center" colspan="2" class="smalltext"><a href="', $scripturl, '?action=reminder">', $txt['forgot_your_password'], '</a><br /><br /></td>
				</tr>
			</table>

			<input type="hidden" name="hash_passwrd" value="" />
		</form>';

	// Focus on the correct input - username or password.
	echo '
		<script type="text/javascript"><!-- // --><![CDATA[
			document.forms.frmLogin.', isset($context['default_username']) && $context['default_username'] != '' ? 'passwrd' : 'user', '.focus();
		// ]]></script>';
}

// Tell a guest to get lost or login!
function template_kick_guest()
{
	global $context, $settings, $options, $scripturl, $modSettings, $txt;

	// This isn't that much... just like normal login but with a message at the top.
	echo '
		<script type="text/javascript" src="', $settings['default_theme_url'], '/scripts/sha1.js"></script>

		<form action="', $scripturl, '?action=login2" method="post" accept-charset="', $context['character_set'], '" name="frmLogin" id="frmLogin"', empty($context['disable_login_hashing']) ? ' onsubmit="hashLoginPassword(this, \'' . $context['session_id'] . '\');"' : '', '>
			<table border="0" cellspacing="0" cellpadding="3" class="tborder" align="center">
				<tr class="catbg">
					<td>', $txt['warning'], '</td>
				</tr><tr>';

	// Show the message or default message.
	echo '
					<td class="windowbg" style="padding-top: 2ex; padding-bottom: 2ex;">
						', empty($context['kick_message']) ? $txt['only_members_can_access'] : $context['kick_message'], '<br />
						', $txt['login_below'], ' <a href="', $scripturl, '?action=register">', $txt['register_an_account'], '</a> ', sprintf($txt['login_with_forum'], $context['forum_name_html_safe']), '
					</td>';

	// And now the login information.
	echo '
				</tr><tr class="titlebg">
					<td><img src="', $settings['images_url'], '/icons/login_sm.gif" alt="" align="top" /> ', $txt['login'], '</td>
				</tr><tr>
					<td class="windowbg">
						<table border="0" cellpadding="3" cellspacing="0" align="center">
							<tr>
								<td align="right"><strong>', $txt['username'], ':</strong></td>
								<td><input type="text" name="user" size="20" class="input_text" /></td>
							</tr><tr>
								<td align="right"><strong>', $txt['password'], ':</strong></td>
								<td><input type="password" name="passwrd" size="20" class="input_password" /></td>
							</tr>';

	if (!empty($modSettings['enableOpenID']))
		echo '<tr>
								<td colspan="2" align="center"><strong>&mdash;', $txt['or'], '&mdash;</strong></td>
							</tr><tr>
								<td align="right"><strong>', $txt['openid'], ':</strong></td>
								<td><input type="text" name="openid_url" class="input_text openid_login" size="17" /></td>
							</tr><tr>
								<td colspan="2" align="center"><hr /></td>
							</tr>';

	echo '<tr>
								<td align="right"><strong>', $txt['mins_logged_in'], ':</strong></td>
								<td><input type="text" name="cookielength" size="4" maxlength="4" value="', $modSettings['cookieTime'], '" class="input_text" /></td>
							</tr><tr>
								<td align="right"><strong>', $txt['always_logged_in'], ':</strong></td>
								<td><input type="checkbox" name="cookieneverexp" class="input_check" onclick="this.form.cookielength.disabled = this.checked;" /></td>
							</tr><tr>
								<td align="center" colspan="2"><input type="submit" value="', $txt['login'], '" style="margin-top: 2ex;" class="button_submit" /></td>
							</tr><tr>
								<td align="center" colspan="2" class="smalltext"><a href="', $scripturl, '?action=reminder">', $txt['forgot_your_password'], '</a><br /><br /></td>
							</tr>
						</table>
					</td>
				</tr>
			</table>

			<input type="hidden" name="hash_passwrd" value="" />
		</form>';

	// Do the focus thing...
	echo '
		<script type="text/javascript"><!-- // --><![CDATA[
			document.forms.frmLogin.user.focus();
		// ]]></script>';
}

// This is for maintenance mode.
function template_maintenance()
{
	global $context, $settings, $options, $scripturl, $txt, $modSettings;

	// Display the administrator's message at the top.
	echo '
<form action="', $scripturl, '?action=login2" method="post" accept-charset="', $context['character_set'], '">
	<table border="0" width="86%" cellspacing="0" cellpadding="3" class="tborder" align="center">
		<tr class="titlebg">
			<td colspan="2">', $context['title'], '</td>
		</tr><tr>
			<td class="windowbg" width="44" align="center" style="padding: 1ex;">
				<img src="', $settings['images_url'], '/construction.gif" width="40" height="40" alt="', $txt['in_maintain_mode'], '" />
			</td>
			<td class="windowbg">', $context['description'], '</td>
		</tr><tr class="titlebg">
			<td colspan="2">', $txt['admin_login'], '</td>
		</tr><tr>';

	// And now all the same basic login stuff from before.
	echo '
			<td colspan="2" class="windowbg">
				<table border="0" width="90%" align="center">
					<tr>
						<td><strong>', $txt['username'], ':</strong></td>
						<td><input type="text" name="user" size="15" /></td>
						<td><strong>', $txt['password'], ':</strong></td>
						<td><input type="password" name="passwrd" size="10" /> &nbsp;</td>
					</tr><tr>
						<td><strong>', $txt['mins_logged_in'], ':</strong></td>
						<td><input type="text" name="cookielength" size="4" maxlength="4" value="', $modSettings['cookieTime'], '" class="input_text" /> &nbsp;</td>
						<td><strong>', $txt['always_logged_in'], ':</strong></td>
						<td><input type="checkbox" name="cookieneverexp" class="input_check" /></td>
					</tr><tr>
						<td align="center" colspan="4"><input type="submit" value="', $txt['login'], '" style="margin-top: 1ex; margin-bottom: 1ex;" class="button_submit" /></td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
</form>';
}

// This is for the security stuff - makes administrators login every so often.
function template_admin_login()
{
	global $context, $settings, $options, $scripturl, $txt;

	// Since this should redirect to whatever they were doing, send all the get data.
	echo '
<script type="text/javascript" src="', $settings['default_theme_url'], '/scripts/sha1.js"></script>

<form action="', $scripturl, $context['get_data'], '" method="post" accept-charset="', $context['character_set'], '" name="frmLogin" id="frmLogin" onsubmit="hashAdminPassword(this, \'', $context['user']['username'], '\', \'', $context['session_id'], '\');">
	<table border="0" width="400" cellspacing="0" cellpadding="3" class="tborder" align="center">
		<tr class="titlebg">
			<td align="left">
				<img src="', $settings['images_url'], '/icons/login_sm.gif" alt="" align="top" /> ', $txt['login'], '
			</td>
		</tr>';

	// We just need the password.
	echo '
		<tr class="windowbg">
			<td align="center" style="padding: 1ex 0;">
				<strong>', $txt['password'], ':</strong> <input type="password" name="admin_pass" size="24" class="input_password" /> <a href="', $scripturl, '?action=helpadmin;help=securityDisable_why" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt['help'], '" align="middle" /></a><br />';

	if (!empty($context['incorrect_password']))
		echo '
				<span class="smalltext error">', $txt['admin_incorrect_password'], '</span><br />';

	echo '
				<input type="submit" value="', $txt['login'], '" style="margin-top: 2ex;" class="button_submit" />
			</td>
		</tr>
	</table>';

	// Make sure to output all the old post data.
	echo $context['post_data'], '

	<input type="hidden" name="admin_hash_pass" value="" />
</form>';

	// Focus on the password box.
	echo '
<script type="text/javascript"><!-- // --><![CDATA[
	document.forms.frmLogin.admin_pass.focus();
// ]]></script>';
}

// Activate your account manually?
function template_retry_activate()
{
	global $context, $settings, $options, $txt, $scripturl;

	// Just ask them for their code so they can try it again...
	echo '
		<br />
		<form action="', $scripturl, '?action=activate;u=', $context['member_id'], '" method="post" accept-charset="', $context['character_set'], '">
			<table border="0" width="600" cellpadding="4" cellspacing="0" class="tborder" align="center">
				<tr class="titlebg">
					<td colspan="2">', $context['page_title'], '</td>';

	// You didn't even have an ID?
	if (empty($context['member_id']))
		echo '
				</tr><tr class="windowbg">
					<td align="right" width="40%">', $txt['invalid_activation_username'], ':</td>
					<td><input type="text" name="user" size="30" class="input_text" /></td>';

	echo '
				</tr><tr class="windowbg">
					<td align="right" width="40%">', $txt['invalid_activation_retry'], ':</td>
					<td><input type="text" name="code" size="30" class="input_text" /></td>
				</tr><tr class="windowbg">
					<td colspan="2" align="center" style="padding: 1ex;"><input type="submit" value="', $txt['invalid_activation_submit'], '" class="button_submit" /></td>
				</tr>
			</table>
		</form>';
}

// Activate your account manually?
function template_resend()
{
	global $context, $settings, $options, $txt, $scripturl;

	// Just ask them for their code so they can try it again...
	echo '
		<br />
		<form action="', $scripturl, '?action=activate;sa=resend" method="post" accept-charset="', $context['character_set'], '">
			<table border="0" width="600" cellpadding="4" cellspacing="0" class="tborder" align="center">
				<tr class="titlebg">
					<td colspan="2">', $context['page_title'], '</td>
				</tr><tr class="windowbg">
					<td align="right" width="40%">', $txt['invalid_activation_username'], ':</td>
					<td><input type="text" name="user" size="40" value="', $context['default_username'], '" class="input_text" /></td>
				</tr><tr class="windowbg">
					<td colspan="2" style="padding-top: 3ex; padding-left: 3ex;">', $txt['invalid_activation_new'], '</td>
				</tr><tr class="windowbg">
					<td align="right" width="40%">', $txt['invalid_activation_new_email'], ':</td>
					<td><input type="text" name="new_email" size="40" class="input_text" /></td>
				</tr><tr class="windowbg">
					<td align="right" width="40%">', $txt['invalid_activation_password'], ':</td>
					<td><input type="password" name="passwd" size="30" class="input_password" /></td>
				</tr><tr class="windowbg">';

	if ($context['can_activate'])
		echo '
					<td colspan="2" style="padding-top: 3ex; padding-left: 3ex;">', $txt['invalid_activation_known'], '</td>
				</tr><tr class="windowbg">
					<td align="right" width="40%">', $txt['invalid_activation_retry'], ':</td>
					<td><input type="text" name="code" size="30" class="input_text" /></td>
				</tr><tr class="windowbg">';

	echo '
					<td colspan="2" align="center" style="padding: 1ex;"><input type="submit" value="', $txt['invalid_activation_resend'], '" class="button_submit" /></td>
				</tr>
			</table>
		</form>';
}

// OpenID can't currently do admin stuff.
function template_admin_openid_disabled()
{
	global $context, $settings, $options, $scripturl, $txt;

	// Tell them they can't do this - really sorry!
	echo '
	<div class="centertext">
		<table border="0" width="480" cellspacing="0" cellpadding="3" class="tborder" align="center">
			<tr class="titlebg">
				<td align="left">
					<img src="', $settings['images_url'], '/openid.gif" alt="" align="top" /> ', $txt['openid_admin_disabled'], '
				</td>
			</tr>
			<tr class="windowbg">
				<td align="left">
					', $txt['openid_admin_disallowed_desc'], '<br />
					<hr />
					', sprintf($txt['openid_admin_disallowed_desc2'], $scripturl . '?action=profile;area=account;u=' . $context['user']['id']), '
				</td>
			</tr>
		</table>
	</div>';
}

?>