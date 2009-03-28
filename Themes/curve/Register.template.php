<?php
// Version: 2.0 RC1; Register

// Before registering - get their information.
function template_before()
{
	global $context, $settings, $options, $scripturl, $txt, $modSettings;

	// Make sure they've agreed to the terms and conditions.
	echo '
<script type="text/javascript" src="', $settings['default_theme_url'], '/scripts/register.js"></script>
<script type="text/javascript"><!-- // --><![CDATA[
	function verifyAgree()
	{
		if (currentAuthMethod == \'passwd\' && document.forms.creator.smf_autov_pwmain.value != document.forms.creator.smf_autov_pwverify.value)
		{
			alert("', $txt['register_passwords_differ_js'], '");
			return false;
		}';

	// If they haven't checked the "I agree" box, tell them and don't submit.
	if ($context['require_agreement'])
		echo '

		if (!document.forms.creator.regagree.checked)
		{
			alert("', $txt['register_agree'], '");
			return false;
		}';

	// Otherwise, let it through.
	echo '

		return true;
	}

	var currentAuthMethod = \'passwd\';
	function updateAuthMethod()
	{
		// What authentication method is being used?
		if (!document.getElementById(\'auth_openid\') || !document.getElementById(\'auth_openid\').checked)
			currentAuthMethod = \'passwd\';
		else
			currentAuthMethod = \'openid\';

		// No openID?
		if (!document.getElementById(\'auth_openid\'))
			return true;

		document.forms.creator.openid_url.disabled = currentAuthMethod == \'openid\' ? false : true;
		document.forms.creator.smf_autov_pwmain.disabled = currentAuthMethod == \'passwd\' ? false : true;
		document.forms.creator.smf_autov_pwverify.disabled = currentAuthMethod == \'passwd\' ? false : true;
		document.getElementById(\'smf_autov_pwmain_div\').style.display = currentAuthMethod == \'passwd\' ? \'\' : \'none\';
		document.getElementById(\'smf_autov_pwverify_div\').style.display = currentAuthMethod == \'passwd\' ? \'\' : \'none\';

		if (currentAuthMethod == \'passwd\')
		{
			verificationHandle.refreshMainPassword();
			verificationHandle.refreshVerifyPassword();
			document.forms.creator.openid_url.style.backgroundColor = \'\';
		}
		else
		{
			document.forms.creator.smf_autov_pwmain.style.backgroundColor = \'\';
			document.forms.creator.smf_autov_pwverify.style.backgroundColor = \'\';
			document.forms.creator.openid_url.style.backgroundColor = \'#FCE184\';
		}

		return true;
	}';

	if ($context['require_agreement'])
		echo '
	function checkAgree()
	{
		document.forms.creator.regSubmit.disabled =  (currentAuthMethod == "passwd" && (isEmptyText(document.forms.creator.smf_autov_pwmain) || isEmptyText(document.forms.creator.user) || isEmptyText(document.forms.creator.email))) || (currentAuthMethod == "openid" && isEmptyText(document.forms.creator.openid_url)) || !document.forms.creator.regagree.checked;
		setTimeout("checkAgree();", 1000);
	}
	setTimeout("checkAgree();", 1000);';

	echo '
// ]]></script>';

	// Any errors?
	if (!empty($context['registration_errors']))
	{
		echo '
	<div class="register_error">
		<span>', $txt['registration_errors_occurred'], '</span>
		<ul class="reset">';

		// Cycle through each error and display an error message.
		foreach ($context['registration_errors'] as $error)
				echo '
			<li>', $error, '</li>';

		echo '
		</ul>
	</div>';
	}

	echo '
<form action="', $scripturl, '?action=register2" method="post" accept-charset="', $context['character_set'], '" name="creator" id="creator" onsubmit="return verifyAgree();">
	<h3 class="catbg"><span class="left"></span><span class="right"></span>
		', $txt['register'], ' - ', $txt['required_info'], '
	</h3>
	<div id="register_screen">
		<dl class="register_form">
			<dt>', $txt['choose_username'], ': <span class="smalltext">', $txt['identification_by_smf'], '</span></dt>
			<dd><input type="text" name="user" id="smf_autov_username" size="30" tabindex="', $context['tabindex']++, '" maxlength="25" value="', isset($context['username']) ? $context['username'] : '', '" />
				<span id="smf_autov_username_div" style="display: none;">
					<a id="smf_autov_username_link" href="#">
						<img id="smf_autov_username_img" src="', $settings['images_url'], '/icons/field_check.gif" alt="*" />
					</a>
				</span>
			</dd>
			<dt>', $txt['email'], ': <span class="smalltext">', $txt['valid_email'], '</span></dt>
			<dd>
				<input type="text" name="email" id="smf_autov_reserve1" size="30" tabindex="', $context['tabindex']++, '" value="', isset($context['email']) ? $context['email'] : '', '" />
				<label for="allow_email"><input type="checkbox" name="allow_email" id="allow_email" tabindex="', $context['tabindex']++, '" class="check" /> ', $txt['allow_user_email'], '</label>			
			</dd>';
		// With openID disabled we put the password here.
		if (empty($modSettings['enableOpenID']))
		{
			echo'
			<dt>', $txt['choose_pass'], ':</dt>
			<dd>
				<input type="password" name="passwrd1" id="smf_autov_pwmain" size="30" tabindex="', $context['tabindex']++, '" />
				<span id="smf_autov_pwmain_div" style="display: none;">
					<img id="smf_autov_pwmain_img" src="', $settings['images_url'], '/icons/field_invalid.gif" alt="*" />
				</span>
			</dd>
			<dt>', $txt['verify_pass'], ':</dt>
			<dd>
					<input type="password" name="passwrd2" id="smf_autov_pwverify" size="30" tabindex="', $context['tabindex']++, '" />
					<span id="smf_autov_pwverify_div" style="display: none;">
						<img id="smf_autov_pwverify_img" src="', $settings['images_url'], '/icons/field_valid.gif" alt="*" />
					</span>				
			</dd>';	
		}

		if ($context['visual_verification'])
		{
			echo '
			<dt class="register_captcha">', $txt['verification'], ':</dt>
			<dd>', template_control_verification($context['visual_verification_id'], 'all'), '</dd>';
		}		

		// Are there age restrictions in place?
		if (!empty($modSettings['coppaAge']))
			echo '
			<dt></dt>
			<dd>
				<label for="skip_coppa"><input type="checkbox" name="skip_coppa" id="skip_coppa" tabindex="', $context['tabindex']++, '" ', !empty($context['skip_coppa']) ? 'checked="checked"' : '', ' class="check" /> <strong>', $context['coppa_desc'], '.</strong></label>
			</dd>';

		echo '
		</dl>';

		// If openID is enabled offer them the choice.
		if (!empty($modSettings['enableOpenID']))
		{
			echo '
		<h4 class="titlebg"><span class="left"></span><span class="right"></span>
			', $txt['authenticate_label'], ':
		</h4>
		<dl class="register_form">
			<dt>
				<input type="radio" name="authenticate" value="passwd" id="auth_pass" ', empty($context['openid']) ? 'checked="checked" ' : '', ' onclick="updateAuthMethod();" />
				<label for="auth_pass">', $txt['authenticate_password'], ':</label>
			</dt>	
			<dd></dd>
			<dt><em>', $txt['choose_pass'], ':</em></dt>
			<dd>
				<input type="password" name="passwrd1" id="smf_autov_pwmain" size="30" tabindex="', $context['tabindex']++, '" />
				<span id="smf_autov_pwmain_div" style="display: none;">
					<img id="smf_autov_pwmain_img" src="', $settings['images_url'], '/icons/field_invalid.gif" alt="*" />
				</span>			
			</dd>
			<dt><em>', $txt['verify_pass'], ':</em></dt>
			<dd>
				<input type="password" name="passwrd2" id="smf_autov_pwverify" size="30" tabindex="', $context['tabindex']++, '" />
				<span id="smf_autov_pwverify_div" style="display: none;">
					<img id="smf_autov_pwverify_img" src="', $settings['images_url'], '/icons/field_valid.gif" alt="*" />
				</span>			
			</dd>
			<dt>
				<input type="radio" name="authenticate" value="openid" id="auth_openid" ', !empty($context['openid']) ? 'checked="checked" ' : '', ' onclick="updateAuthMethod();" />
				<label for="auth_openid"><strong>', $txt['authenticate_openid'], ':</strong></label>&nbsp;<em><a href="', $scripturl, '?action=helpadmin;help=register_openid" onclick="return reqWin(this.href);" class="help">(?)</a></em>
			</dt>
			<dd>
			</dd>
			<dt><em>', $txt['authenticate_openid_url'], ':</em></dt>
			<dd>
				<input type="text" name="openid_url" id="openid_url" size="30" tabindex="', $context['tabindex']++, '" value="', isset($context['openid']) ? $context['openid'] : '', '" />
				<span><img src="', $settings['images_url'], '/openid.gif" alt="', $txt['openid'], '" /></span>				
			</dd>
		</dl>';
		}

	if (!empty($context['profile_fields']))
	{
		echo '
		<hr />
		<dl class="register_form">';

		// Any fields we particularly want?
		foreach ($context['profile_fields'] as $key => $field)
		{
			if ($field['type'] == 'callback')
			{
				if (isset($field['callback_func']) && function_exists('template_profile_' . $field['callback_func']))
				{
					$callback_func = 'template_profile_' . $field['callback_func'];
					$callback_func();
				}
			}
			else
			{
					echo '
					<dt><b', !empty($field['is_error']) ? ' style="color: red;"' : '', '>', $field['label'], '</b>';

				// Does it have any subtext to show?
				if (!empty($field['subtext']))
					echo '
						<span class="smalltext">', $field['subtext'], '</span>';

				echo '
					</dt>
					<dd>';

				// Want to put something infront of the box?
				if (!empty($field['preinput']))
					echo '
						', $field['preinput'];

				// What type of data are we showing?
				if ($field['type'] == 'label')
					echo '
						', $field['value'];

				// Maybe it's a text box - very likely!
				elseif (in_array($field['type'], array('int', 'float', 'text', 'password')))
					echo '
						<input type="', $field['type'] == 'password' ? 'password' : 'text', '" name="', $key, '" id="', $key, '" size="', empty($field['size']) ? 30 : $field['size'], '" value="', $field['value'], '" tabindex="', $context['tabindex']++, '" ', $field['input_attr'], ' />';

				// You "checking" me out? ;)
				elseif ($field['type'] == 'check')
					echo '
						<input type="hidden" name="', $key, '" value="0" /><input type="checkbox" name="', $key, '" id="', $key, '" ', !empty($field['value']) ? ' checked="checked"' : '', ' value="1" tabindex="', $context['tabindex']++, '" class="check" ', $field['input_attr'], ' />';

				// Always fun - select boxes!
				elseif ($field['type'] == 'select')
				{
					echo '
						<select name="', $key, '" id="', $key, '" tabindex="', $context['tabindex']++, '">';

					if (isset($field['options']))
					{
						// Is this some code to generate the options?
						if (!is_array($field['options']))
							$field['options'] = eval($field['options']);
						// Assuming we now have some!
						if (is_array($field['options']))
							foreach ($field['options'] as $value => $name)
								echo '
									<option value="', $value, '" ', $value == $field['value'] ? 'selected="selected"' : '', '>', $name, '</option>';
					}

					echo '
						</select>';
				}

				// Something to end with?
				if (!empty($field['postinput']))
					echo '
						', $field['postinput'];

			}
		}
	echo '
			</dd>
		</dl>';		
	}

	// Are there any custom fields?
	if (!empty($context['custom_fields']))
	{
		echo '
		<dl class="register_form">';
		foreach ($context['custom_fields'] as $field)
		{
			echo '
			<dt>', $field['name'], ': <span class="smalltext">', $field['desc'], '</span></dt>
			<dd>', $field['input_html'], '</dd>';
		}
		echo '
		</dl>';
	}

	echo '
	</div>';

	// Require them to agree here?
	if ($context['require_agreement'])
		echo '
	<p class="description">', $context['agreement'], '</p>
	<p class="centertext">
		<label for="regagree"><input type="checkbox" name="regagree" onclick="checkAgree();" id="regagree" tabindex="', $context['tabindex']++, '" class="check" ', !empty($context['regagree']) ? 'checked="checked"' : '', ' /> <strong>', $txt['agree'], '</strong></label>
	</p>';

	echo '
	<div class="centertext">
		<input type="submit" name="regSubmit" value="', $txt['register'], '" tabindex="', $context['tabindex']++, '" />
	</div>
</form>
<script type="text/javascript"><!-- // --><![CDATA[';

	// Uncheck the agreement thing....
	if ($context['require_agreement'] && empty($context['regagree']))
		echo '
	document.forms.creator.regagree.checked = false;
	document.forms.creator.regSubmit.disabled = !document.forms.creator.regagree.checked;';

	// Clever registration stuff...
	echo '
	var regTextStrings = {
		"username_valid": "', $txt['registration_username_available'], '",
		"username_invalid": "', $txt['registration_username_unavailable'], '",
		"username_check": "', $txt['registration_username_check'], '",
		"password_short": "', $txt['registration_password_short'], '",
		"password_reserved": "', $txt['registration_password_reserved'], '",
		"password_numbercase": "', $txt['registration_password_numbercase'], '",
		"password_no_match": "', $txt['registration_password_no_match'], '",
		"password_valid": "', $txt['registration_password_valid'], '"
	};
	var verificationHandle = new smfRegister("creator", ', empty($modSettings['password_strength']) ? 0 : $modSettings['password_strength'], ', regTextStrings);
	// Update the authentication status.
	updateAuthMethod();';

	echo '
// ]]></script>';
}

// After registration... all done ;).
function template_after()
{
	global $context, $settings, $options, $txt, $scripturl;

	// Not much to see here, just a quick... "you're now registered!" or what have you.
	echo '
		<div>
			<h3 class="catbg"><span class="left"></span><span class="right"></span>', $context['page_title'], '</h3>
			<p>', $context['description'], '</p>
		</div>';
}

// Template for giving instructions about COPPA activation.
function template_coppa()
{
	global $context, $settings, $options, $txt, $scripturl;

	// Formulate a nice complicated message!
	echo '
		<div>
			<h3 class="titlebg">', $context['page_title'], '</h3>
			<p>', $context['coppa']['body'], '</p>
				<span><a href="', $scripturl, '?action=coppa;form;member=', $context['coppa']['id'], '" target="_blank" class="new_win">', $txt['coppa_form_link_popup'], '</a> | <a href="', $scripturl, '?action=coppa;form;dl;member=', $context['coppa']['id'], '">', $txt['coppa_form_link_download'], '</a></span>
			</p>
			<p>', $context['coppa']['many_options'] ? $txt['coppa_send_to_two_options'] : $txt['coppa_send_to_one_option'], '</p>';

	// Can they send by post?
	if (!empty($context['coppa']['post']))
	{
		echo '
			<h4>1) ', $txt['coppa_send_by_post'], '</h4>
			<div class="coppa_contact">
				', $context['coppa']['post'], '
			</div>';
	}

	// Can they send by fax??
	if (!empty($context['coppa']['fax']))
	{
		echo '
			<h4>', !empty($context['coppa']['post']) ? '2' : '1', ') ', $txt['coppa_send_by_fax'], '</h4>
			<div class="coppa_contact">
				', $context['coppa']['fax'], '
			</div>';
	}

	// Offer an alternative Phone Number?
	if ($context['coppa']['phone'])
	{
		echo '
			<p>', $context['coppa']['phone'], '</p>';
	}
	echo '
		</div>';
}

// An easily printable form for giving permission to access the forum for a minor.
function template_coppa_form()
{
	global $context, $settings, $options, $txt, $scripturl;

	// Show the form (As best we can)
	echo '
		<table border="0" width="100%" cellpadding="3" cellspacing="0" class="tborder" align="center">
			<tr>
				<td align="left">', $context['forum_contacts'], '</td>
			</tr><tr>
				<td align="right">
					<em>', $txt['coppa_form_address'], '</em>: ', $context['ul'], '<br />
					', $context['ul'], '<br />
					', $context['ul'], '<br />
					', $context['ul'], '
				</td>
			</tr><tr>
				<td align="right">
					<em>', $txt['coppa_form_date'], '</em>: ', $context['ul'], '
					<br /><br />
				</td>
			</tr><tr>
				<td align="left">
					', $context['coppa_body'], '
				</td>
			</tr>
		</table>
		<br />';
}

// Show a window containing the spoken verification code.
function template_verification_sound()
{
	global $context, $settings, $options, $txt, $scripturl;

	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"', $context['right_to_left'] ? ' dir="rtl"' : '', '>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=', $context['character_set'], '" />
		<title>', $context['page_title'], '</title>
		<meta name="robots" content="noindex" />
		<link rel="stylesheet" type="text/css" href="', $settings['theme_url'], '/style.css" />
		<style type="text/css">';

	// Just show the help text and a "close window" link.
	echo '
		</style>
	</head>
	<body style="margin: 1ex;">
		<div class="popuptext" style="text-align: center;">';
	if ($context['browser']['is_ie'])
		echo '
			<object classid="clsid:22D6F312-B0F6-11D0-94AB-0080C74C7E95" type="audio/x-wav">
				<param name="AutoStart" value="1" />
				<param name="FileName" value="', $context['verification_sound_href'], '" />
			</object>';
	else
		echo '
			<object type="audio/x-wav" data="', $context['verification_sound_href'], '">
				<a href="', $context['verification_sound_href'], '" rel="nofollow">', $context['verification_sound_href'], '</a>
			</object>';
	echo '
			<br />
			<a href="', $context['verification_sound_href'], ';sound" rel="nofollow">', $txt['visual_verification_sound_again'], '</a><br />
			<a href="javascript:self.close();">', $txt['visual_verification_sound_close'], '</a><br />
			<a href="', $context['verification_sound_href'], '" rel="nofollow">', $txt['visual_verification_sound_direct'], '</a>
		</div>
	</body>
</html>';
}

function template_admin_register()
{
	global $context, $settings, $options, $scripturl, $txt, $modSettings;

	echo '
	<form action="', $scripturl, '?action=admin;area=regcenter" method="post" accept-charset="', $context['character_set'], '" name="postForm" id="postForm">
		<script type="text/javascript"><!-- // --><![CDATA[
			function onCheckChange()
			{
				if (document.forms.postForm.emailActivate.checked)
				{
					document.forms.postForm.emailPassword.disabled = true;
					document.forms.postForm.emailPassword.checked = true;
				}
				else
					document.forms.postForm.emailPassword.disabled = false;
			}
		// ]]></script>
		<h3 class="titlebg">
			', $txt['admin_browse_register_new'], '
		</h3>
		<div id="register_screen">';
	if (!empty($context['registration_done']))
		echo '
			<p>', $context['registration_done'], '</p><hr />';
	echo '
			<dl class="register_form">
				<dt>
					<label for="user_input">', $txt['admin_register_username'], ':</label>
					<span class="smalltext">', $txt['admin_register_username_desc'], '</span>
				</dt>
				<dd>
					<input type="text" name="user" id="user_input" tabindex="', $context['tabindex']++, '" size="30" maxlength="25" />
				</dd>
				<dt>
					<label for="email_input">', $txt['admin_register_email'], ':</label>
					<span class="smalltext">', $txt['admin_register_email_desc'], '</span>
				</dt>
				<dd>
					<input type="text" name="email" id="email_input" tabindex="', $context['tabindex']++, '" size="30" />
				</dd>
				<dt>
					<label for="password_input">', $txt['admin_register_password'], ':</label>
					<span class="smalltext">', $txt['admin_register_password_desc'], '</span>
				</dt>
				<dd>
					<input type="password" name="password" id="password_input" tabindex="', $context['tabindex']++, '" size="30" />
				</dd>
				<dt>
					<label for="group_select">', $txt['admin_register_group'], ':</label>
					<span class="smalltext">', $txt['admin_register_group_desc'], '</span>
				</dt>
				<dd>
					<select name="group" id="group_select" tabindex="', $context['tabindex']++, '">';

	foreach ($context['member_groups'] as $id => $name)
		echo '
						<option value="', $id, '">', $name, '</option>';
	echo '
					</select>
				</dd>
				<dt>
					<label for="emailPassword_check">', $txt['admin_register_email_detail'], ':</label>
					<span class="smalltext">', $txt['admin_register_email_detail_desc'], '</span>
				</dt>
				<dd>
					<input type="checkbox" name="emailPassword" id="emailPassword_check" tabindex="', $context['tabindex']++, '" checked="checked"', !empty($modSettings['registration_method']) && $modSettings['registration_method'] == 1 ? ' disabled="disabled"' : '', ' class="check" />
				</dd>
				<dt>
					<label for="emailActivate_check">', $txt['admin_register_email_activate'], ':</label>
				</dt>
				<dd>
					<input type="checkbox" name="emailActivate" id="emailActivate_check" tabindex="', $context['tabindex']++, '"', !empty($modSettings['registration_method']) && $modSettings['registration_method'] == 1 ? ' checked="checked"' : '', ' onclick="onCheckChange();" class="check" />
				</dd>
				<dt>
				</dt>
				<dd>
					<input type="submit" name="regSubmit" value="', $txt['register'], '" tabindex="', $context['tabindex']++, '" />
					<input type="hidden" name="sa" value="register" />			
				</dd>
			</dl>
		</div>
		<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
	</form>';
}

// Form for editing the agreement shown for people registering to the forum.
function template_edit_agreement()
{
	global $context, $settings, $options, $scripturl, $txt;

	// Just a big box to edit the text file ;).
	echo '
		<div class="tborder">
			<h3 class="titlebg">', $txt['registration_agreement'], '</h3>';

	// Warning for if the file isn't writable.
	if (!empty($context['warning']))
		echo '
			<h4 class="error">', $context['warning'], '</h4>';

	// Are there more than one language to choose from?
	if (count($context['editable_agreements']) > 1)
	{
		echo '
			<div class="information">
				<form action="', $scripturl, '?action=admin;area=regcenter" id="change_reg" method="post" accept-charset="', $context['character_set'], '" style="display: inline;">
					<strong>', $txt['admin_agreement_select_language'], ':</strong>&nbsp;
					<select name="agree_lang" onchange="document.getElementById(\'change_reg\').submit();" tabindex="', $context['tabindex']++, '">';

		foreach ($context['editable_agreements'] as $file => $name)
			echo '
						<option value="', $file, '" ', $context['current_agreement'] == $file ? 'selected="selected"' : '', '>', $name, '</option>';

		echo '
					</select>
					<input type="hidden" name="sa" value="agreement" />
					<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
					<input type="submit" name="change" value="', $txt['admin_agreement_select_language_change'], '" tabindex="', $context['tabindex']++, '" />
				</form>
			</div>';
	}

	echo '
			<div class="tborder">
				<form action="', $scripturl, '?action=admin;area=regcenter" method="post" accept-charset="', $context['character_set'], '">';

	// Show the actual agreement in an oversized text box.
	echo '
					<textarea cols="70" rows="20" name="agreement" style="width: 100%;">', $context['agreement'], '</textarea>
					<p>
						<label for="requireAgreement"><input type="checkbox" name="requireAgreement" id="requireAgreement"', $context['require_agreement'] ? ' checked="checked"' : '', ' tabindex="', $context['tabindex']++, '" value="1" /> ', $txt['admin_agreement'], '.</label>
					</p>
					<p><input type="submit" value="', $txt['save'], '" tabindex="', $context['tabindex']++, '" /></p>
					<input type="hidden" name="agree_lang" value="', $context['current_agreement'], '" />
					<input type="hidden" name="sa" value="agreement" />
					<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
				</form>
			</div>
		</div>';
}

function template_edit_reserved_words()
{
	global $context, $settings, $options, $scripturl, $txt;

	echo '
		<form action="', $scripturl, '?action=admin;area=regcenter" method="post" accept-charset="', $context['character_set'], '">
			<div class="tborder">
				<h3 class="titlebg">', $txt['admin_reserved_set'], '</h3>
					<div style="width: 80%;">
						<h4>', $txt['admin_reserved_line'], '</h4>
						<textarea cols="30" rows="6" name="reserved" style="width: 98%;">', implode("\n", $context['reserved_words']), '</textarea>
						<ul class="reset">
							<li><label for="matchword"><input type="checkbox" name="matchword" id="matchword" tabindex="', $context['tabindex']++, '" ', $context['reserved_word_options']['match_word'] ? 'checked="checked"' : '', ' class="check" /> ', $txt['admin_match_whole'], '</label></li>
							<li><label for="matchcase"><input type="checkbox" name="matchcase" id="matchcase" tabindex="', $context['tabindex']++, '" ', $context['reserved_word_options']['match_case'] ? 'checked="checked"' : '', ' class="check" /> ', $txt['admin_match_case'], '</label></li>
							<li><label for="matchuser"><input type="checkbox" name="matchuser" id="matchuser" tabindex="', $context['tabindex']++, '" ', $context['reserved_word_options']['match_user'] ? 'checked="checked"' : '', ' class="check" /> ', $txt['admin_check_user'], '</label></li>
							<li><label for="matchname"><input type="checkbox" name="matchname" id="matchname" tabindex="', $context['tabindex']++, '" ', $context['reserved_word_options']['match_name'] ? 'checked="checked"' : '', ' class="check" /> ', $txt['admin_check_display'], '</label></li>
						</ul>
						<input type="submit" value="', $txt['save'], '" name="save_reserved_names" tabindex="', $context['tabindex']++, '" style="margin: 1ex;" />
					</div>
			</div>
			<input type="hidden" name="sa" value="reservednames" />
			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
		</form>';
}

?>