<?php
// Version: 2.0 RC1; Login

// This is just the basic "login" form.
function template_login()
{
	global $context, $settings, $options, $scripturl, $modSettings, $txt;

	echo '
		<script language="JavaScript" type="text/javascript" src="', $settings['default_theme_url'], '/scripts/sha1.js"></script>

		<form action="', $scripturl, '?action=login2" name="frmLogin" id="frmLogin" method="post" accept-charset="', $context['character_set'], '" ', empty($context['disable_login_hashing']) ? ' onsubmit="hashLoginPassword(this, \'' . $context['session_id'] . '\');"' : '', '>
		<div class="tborder login">
			<h3 class="catbg"><span class="left"></span><span class="right"></span>
				<img src="', $settings['images_url'], '/icons/login_sm.gif" alt="" /> ', $txt['login'], '
			</h3>
			<span id="upperframe"><span></span></span>
			<div id="roundframe"><div class="frame">';

	// Did they make a mistake last time?
	if (!empty($context['login_errors']))
		foreach ($context['login_errors'] as $error)
			echo '
				<p class="error">', $error, '</p>';

	// Or perhaps there's some special description for this time?
	if (isset($context['description']))
		echo '
				<p class="description">', $context['description'], '</p>';

	// Now just get the basic information - username, password, etc.
	echo '
				<dl>
					<dt>', $txt['username'], ':</dt>
					<dd><input type="text" name="user" size="20" value="', $context['default_username'], '" /></dd>
					<dt>', $txt['password'], ':</dt>
					<dd><input type="password" name="passwrd" value="', $context['default_password'], '" size="20" /></dd>
				</dl>';

	if (!empty($modSettings['enableOpenID']))
		echo '<p><strong>&mdash;', $txt['or'], '&mdash;</strong></p>
				<dl>
					<dt>', $txt['openid'], ':</dt>
					<dd><input type="text" name="openid_url" class="openid_login" size="17" />&nbsp;<i><a href="', $scripturl, '?action=helpadmin;help=register_openid" onclick="return reqWin(this.href);" class="help">(?)</a></i></dd>
				</dl><hr />';

	echo '
				<dl>
					<dt>', $txt['mins_logged_in'], ':</dt>
					<dd><input type="text" name="cookielength" size="4" maxlength="4" value="', $modSettings['cookieTime'], '"', $context['never_expire'] ? ' disabled="disabled"' : '', ' /></dd>
					<dt>', $txt['always_logged_in'], ':</dt>
					<dd><input type="checkbox" name="cookieneverexp"', $context['never_expire'] ? ' checked="checked"' : '', ' class="check" onclick="this.form.cookielength.disabled = this.checked;" /></dd>';
	// If they have deleted their account, give them a chance to change their mind.
	if (isset($context['login_show_undelete']))
		echo '
					<dt class="alert">', $txt['undelete_account'], ':</dt>
					<dd><input type="checkbox" name="undelete" class="check" /></dd>';
	echo '
				</dl>
				<p><input type="submit" value="', $txt['login'], '" /></p>
				<p class="smalltext"><a href="', $scripturl, '?action=reminder">', $txt['forgot_your_password'], '</p>
				<input type="hidden" name="hash_passwrd" value="" />
			</div></div>
			<span id="lowerframe"><span></span></span>
		</div></form>';

	// Focus on the correct input - username or password.
	echo '
		<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
			document.forms.frmLogin.', isset($context['default_username']) && $context['default_username'] != '' ? 'passwrd' : 'user', '.focus();
		// ]]></script>';
}

// Tell a guest to get lost or login!
function template_kick_guest()
{
	global $context, $settings, $options, $scripturl, $modSettings, $txt;

	// This isn't that much... just like normal login but with a message at the top.
	echo '
	<script language="JavaScript" type="text/javascript" src="', $settings['default_theme_url'], '/scripts/sha1.js"></script>
	<form action="', $scripturl, '?action=login2" method="post" accept-charset="', $context['character_set'], '" name="frmLogin" id="frmLogin"', empty($context['disable_login_hashing']) ? ' onsubmit="hashLoginPassword(this, \'' . $context['session_id'] . '\');"' : '', '>
		<div class="tborder login">
			<h3 class="catbg"><span class="left"></span><span class="right"></span>
				', $txt['warning'], '
			</h3>';

	// Show the message or default message.
	echo '
			<p class="information centertext">
				', empty($context['kick_message']) ? $txt['only_members_can_access'] : $context['kick_message'], '<br />
				', $txt['login_below'], ' <a href="', $scripturl, '?action=register">', $txt['register_an_account'], '</a> ', sprintf($txt['login_with_forum'], $context['forum_name_html_safe']), '
			</p>';

	// And now the login information.
	echo '
			<h3 class="catbg"><span class="left"></span><span class="right"></span>
				<img src="', $settings['images_url'], '/icons/login_sm.gif" alt=""  /> ', $txt['login'], '
			</h3>
			<span id="upperframe"><span></span></span>
			<div id="roundframe"><div class="frame">
				<dl>
					<dt>', $txt['username'], ':</dt>
					<dd><input type="text" name="user" size="20" /></dd>
					<dt>', $txt['password'], ':</dt>
					<dd><input type="password" name="passwrd" size="20" /></dd>';

	if (!empty($modSettings['enableOpenID']))
		echo '
				</dl>
				<p><strong>&mdash;', $txt['or'], '&mdash;</strong></p>
				<dl>
					<dt>', $txt['openid'], ':</dt>
					<dd><input type="text" name="openid_url" class="openid_login" size="17" /></dd>
				</dl>
				<hr />
				<dl>';

	echo '
					<dt>', $txt['mins_logged_in'], ':</dt>
					<dd><input type="text" name="cookielength" size="4" maxlength="4" value="', $modSettings['cookieTime'], '" /></dd>
					<dt>', $txt['always_logged_in'], ':</dt>
					<dd><input type="checkbox" name="cookieneverexp" class="check" onclick="this.form.cookielength.disabled = this.checked;" /></dd>
				</dl>
				<p class="centertext"><input type="submit" value="', $txt['login'], '" /></p>
				<p class="centertext smalltext"><a href="', $scripturl, '?action=reminder">', $txt['forgot_your_password'], '</a></p>
			</div></div>
			<span id="lowerframe"><span></span></span>
			<input type="hidden" name="hash_passwrd" value="" />
		</div>
	</form>';

	// Do the focus thing...
	echo '
		<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
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
	<div class="tborder login" id="maintenace">
		<h3 class="catbg"><span class="left"></span><span class="right"></span>
			', $context['title'], '
		</h3>
		<p class="description">
			<img class="align_left" src="', $settings['images_url'], '/construction.gif" width="40" height="40" alt="', $txt['in_maintain_mode'], '" />
			', $context['description'], '<br style="clear: both;" />
		</p>
		<h4 class="titlebg"><span class="left"></span><span class="right"></span>
			', $txt['admin_login'], '
		</h4>
		<span id="upperframe"><span></span></span>
		<div id="roundframe"><div class="frame">
			<dl>
				<dt>', $txt['username'], ':</dt>
				<dd><input type="text" name="user" size="15" /></dd>
				<dt>', $txt['password'], ':</dt>
				<dd><input type="password" name="passwrd" size="10" /></dd>
				<dt>', $txt['mins_logged_in'], ':</dt>
				<dd><input type="text" name="cookielength" size="4" maxlength="4" value="', $modSettings['cookieTime'], '" /></dd>
				<dt>', $txt['always_logged_in'], ':</dt>
				<dd><input type="checkbox" name="cookieneverexp" class="check" /></dd>
			</dl>
			<p class="centertext"><input type="submit" value="', $txt['login'], '" /></p>
		</div></div>	
		<span id="lowerframe"><span></span></span>
	</div>
</form>';
}

// This is for the security stuff - makes administrators login every so often.
function template_admin_login()
{
	global $context, $settings, $options, $scripturl, $txt;

	// Since this should redirect to whatever they were doing, send all the get data.
	echo '
<script language="JavaScript" type="text/javascript" src="', $settings['default_theme_url'], '/scripts/sha1.js"></script>

<form action="', $scripturl, $context['get_data'], '" method="post" accept-charset="', $context['character_set'], '" name="frmLogin" id="frmLogin" onsubmit="hashAdminPassword(this, \'', $context['user']['username'], '\', \'', $context['session_id'], '\');">
	<div class="tborder login" id="admin_login">
		<h3 class="titlebg">
			<img src="', $settings['images_url'], '/icons/login_sm.gif" alt="" /> ', $txt['login'], '
		</h3>
		<div class="windowbg2 centertext" style="padding: 1em;">
			<strong>', $txt['password'], ':</strong> <input type="password" name="admin_pass" size="24" /> <a href="', $scripturl, '?action=helpadmin;help=securityDisable_why" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt['help'], '"  /></a><br />';

	if (!empty($context['incorrect_password']))
		echo '
			<div class="error centertext">', $txt['admin_incorrect_password'], '</div>';

	echo '
			<input type="submit" style="margin-top: 1em;" value="', $txt['login'], '" />
		</div>
	</div>';

	// Make sure to output all the old post data.
	echo $context['post_data'], '

	<input type="hidden" name="admin_hash_pass" value="" />
</form>';

	// Focus on the password box.
	echo '
<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
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
					<td><input type="text" name="user" size="30" /></td>';

	echo '
				</tr><tr class="windowbg">
					<td align="right" width="40%">', $txt['invalid_activation_retry'], ':</td>
					<td><input type="text" name="code" size="30" /></td>
				</tr><tr class="windowbg">
					<td colspan="2" align="center" style="padding: 1ex;"><input type="submit" value="', $txt['invalid_activation_submit'], '" /></td>
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
					<td><input type="text" name="user" size="40" value="', $context['default_username'], '" /></td>
				</tr><tr class="windowbg">
					<td colspan="2" style="padding-top: 3ex; padding-left: 3ex;">', $txt['invalid_activation_new'], '</td>
				</tr><tr class="windowbg">
					<td align="right" width="40%">', $txt['invalid_activation_new_email'], ':</td>
					<td><input type="text" name="new_email" size="40" /></td>
				</tr><tr class="windowbg">
					<td align="right" width="40%">', $txt['invalid_activation_password'], ':</td>
					<td><input type="password" name="passwd" size="30" /></td>
				</tr><tr class="windowbg">';

	if ($context['can_activate'])
		echo '
					<td colspan="2" style="padding-top: 3ex; padding-left: 3ex;">', $txt['invalid_activation_known'], '</td>
				</tr><tr class="windowbg">
					<td align="right" width="40%">', $txt['invalid_activation_retry'], ':</td>
					<td><input type="text" name="code" size="30" /></td>
				</tr><tr class="windowbg">';

	echo '
					<td colspan="2" align="center" style="padding: 1ex;"><input type="submit" value="', $txt['invalid_activation_resend'], '" /></td>
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
	<div style="text-align: center">
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