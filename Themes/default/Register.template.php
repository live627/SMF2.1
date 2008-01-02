<?php
// Version: 2.0 Beta 2; Register

// Before registering - get their information.
function template_before()
{
	global $context, $settings, $options, $scripturl, $txt, $modSettings;

	// Make sure they've agreed to the terms and conditions.
	echo '
<script language="JavaScript" type="text/javascript" src="', $settings['default_theme_url'], '/scripts/register.js"></script>
<script language="JavaScript" type="text/javascript" src="', $settings['default_theme_url'], '/scripts/captcha.js"></script>
<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
	function verifyAgree()
	{
		if (document.forms.creator.passwrd1.value != document.forms.creator.passwrd2.value)
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
	function getAuthValue()
	{
		var obj = document.forms.creator.authenticate;
		for(i=0,n=obj.length; i < n; i++)
		{
			if (obj[i].checked)
				return obj[i].value
		}

		return "";
	}
	';

	if ($context['require_agreement'])
		echo '
	function checkAgree()
	{
		document.forms.creator.regSubmit.disabled = isEmptyText(document.forms.creator.user) || isEmptyText(document.forms.creator.email) || ', !empty($modSettings['enableOpenID']) ? '(isEmptyText(document.forms.creator.passwrd1) && getAuthValue() == "passwd") || (isEmptyText(document.forms.creator.openid_url) && getAuthValue() == "openid") || ' : '', '!document.forms.creator.regagree.checked;
		setTimeout("checkAgree();", 1000);
	}
	setTimeout("checkAgree();", 1000);';

	echo '
// ]]></script>';

	// Any errors?
	if (!empty($context['registration_errors']))
	{
		echo '
	<div class="windowbg" style="margin: 1ex; padding: 1ex 2ex; border: 1px dashed red; color: red;">
		<span style="text-decoration: underline;">', $txt['registration_errors_occurred'], '</span>
		<ul>';

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
	<table border="0" width="100%" cellpadding="3" cellspacing="0" class="tborder">
		<tr class="titlebg">
			<td>', $txt['register'], ' - ', $txt['required_info'], '</td>
		</tr><tr class="windowbg">
			<td width="100%">
				<table cellpadding="3" cellspacing="0" border="0" width="100%">
					<tr>
						<td width="40%">
							<b>', $txt['choose_username'], ':</b>
							<div class="smalltext">', $txt['identification_by_smf'], '</div>
						</td>
						<td>
							<input type="text" name="user" id="smf_autov_username" size="30" tabindex="', $context['tabindex']++, '" maxlength="25" value="', isset($context['username']) ? $context['username'] : '', '" />
							<span id="smf_autov_username_div" style="display: none;">
								<a id="smf_autov_username_link" href="#">
									<img id="smf_autov_username_img" src="', $settings['images_url'], '/icons/field_check.gif" alt="*" />
								</a>
							</span>
						</td>
					</tr><tr>
						<td width="40%">
							<b>', $txt['email'], ':</b>
							<div class="smalltext">', $txt['valid_email'], '</div>
						</td>
						<td>
							<input type="text" name="email" id="smf_autov_reserve1" size="30" tabindex="', $context['tabindex']++, '" value="', isset($context['email']) ? $context['email'] : '', '" />
							<label for="hide_email"><input type="checkbox" name="hide_email" id="hide_email" class="check" /> ', $txt['allow_user_email'], '</label>
						</td>
					</tr>
					<tr>
						<td width="40%">
							<b>', $txt['choose_pass'], ':</b>
						</td>
						<td>
							<input type="password" name="passwrd1" id="smf_autov_pwmain" size="30" tabindex="', $context['tabindex']++, '" />
							<span id="smf_autov_pwmain_div" style="display: none;">
								<img id="smf_autov_pwmain_img" src="', $settings['images_url'], '/icons/field_invalid.gif" alt="*" />
							</span>
						</td>
					</tr><tr>
						<td width="40%">
							<b>', $txt['verify_pass'], ':</b>
						</td>
						<td>
							<input type="password" name="passwrd2" id="smf_autov_pwverify" size="30" tabindex="', $context['tabindex']++, '" />
							<span id="smf_autov_pwverify_div" style="display: none;">
								<img id="smf_autov_pwverify_img" src="', $settings['images_url'], '/icons/field_valid.gif" alt="*" />
							</span>
						</td>
					</tr>';

	if (!empty($modSettings['enableOpenID']))
		echo '
					<tr>
						<td>
							<b>', $txt['openid'], ':</b>
						</td>
						<td>
							<input type="text" name="openid_url" size="30" tabindex="', $context['tabindex']++, '" value="', isset($context['openid']) ? $context['openid'] : '', '" />
							<span><img src="', $settings['images_url'], '/openid.gif" alt="', $txt['openid'], '" /></span>
						</td>
					</tr>
					<tr>
						<td>
							<b>', $txt['authenticate_label'], ':</b>
						</td>
						<td>
							<input type="radio" name="authenticate" value="passwd" id="auth_pass" ', empty($context['openid']) ? 'checked="checked" ' : '', '/>
							<label for="auth_pass">', $txt['authenticate_password'], '</label><br />
							<input type="radio" name="authenticate" value="openid" id="auth_openid" ', !empty($context['openid']) ? 'checked="checked" ' : '', '/>
							<label for="auth_openid">', $txt['authenticate_openid'], '</label>
						</td>
					</tr>';

	if ($context['visual_verification'])
	{
		echo '
					<tr valign="top">
						<td width="40%" valign="top">
							<b>', $txt['visual_verification_label'], ':</b>
							<div class="smalltext">', $txt['visual_verification_description'], '</div>
						</td>
						<td>
							<div class="smalltext" style="margin: 4px 0 8px 0;">
								<input type="text" name="visual_verification_code" value="', !empty($context['prev_verification_code']) ? $context['prev_verification_code'] : '', '" size="30" tabindex="', $context['tabindex']++, '" />
								<a href="', $context['verification_image_href'], ';sound" id="visual_verification_sound">', $txt['visual_verification_sound'], '</a> / <a href="', $scripturl, '?action=register" id="visual_verification_refresh">', $txt['visual_verification_request_new'], '</a>
							</div>';
		if ($context['use_graphic_library'])
			echo '
							<img src="', $context['verification_image_href'], '" alt="', $txt['visual_verification_description'], '" id="verification_image" /><br />';
		else
			echo '
							<img src="', $context['verification_image_href'], ';letter=1" alt="', $txt['visual_verification_description'], '" id="verification_image_1" />
							<img src="', $context['verification_image_href'], ';letter=2" alt="', $txt['visual_verification_description'], '" id="verification_image_2" />
							<img src="', $context['verification_image_href'], ';letter=3" alt="', $txt['visual_verification_description'], '" id="verification_image_3" />
							<img src="', $context['verification_image_href'], ';letter=4" alt="', $txt['visual_verification_description'], '" id="verification_image_4" />
							<img src="', $context['verification_image_href'], ';letter=5" alt="', $txt['visual_verification_description'], '" id="verification_image_5" />';
		echo '
						</td>
					</tr>';
	}

	// Are there age restrictions in place?
	if (!empty($modSettings['coppaAge']))
		echo '
					<tr>
						<td colspan="2" align="center" style="padding-top: 1ex;">
							<label for="skip_coppa"><input type="checkbox" name="skip_coppa" id="skip_coppa" tabindex="', $context['tabindex']++, '" ', !empty($context['skip_coppa']) ? 'checked="checked"' : '', ' class="check" /> <b>', $context['coppa_desc'], '.</b></label>
						</td>
					</tr>';

	// If we have some optional fields show them too!
	if (!empty($context['profile_fields']) || !empty($context['custom_fields']))
		echo '
				</table>
			</td>
		</tr>
		<tr class="windowbg">
			<td><hr /></td>
		</tr>
		<tr class="windowbg">
			<td width="100%">
				<table cellpadding="3" cellspacing="0" border="0" width="100%">';

	if (!empty($context['profile_fields']))
	{
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
					<tr valign="top">
						<td width="40%">
							<b', !empty($field['is_error']) ? ' style="color: red;"' : '', '>', $field['label'], '</b>';

			// Does it have any subtext to show?
			if (!empty($field['subtext']))
				echo '
							<div class="smalltext">', $field['subtext'], '</div>';

			echo '
						</td>
						<td>';

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
							<input type="', $field['type'] == 'password' ? 'password' : 'text', '" name="', $key, '" id="', $key, '" size="', empty($field['size']) ? 30 : $field['size'], '" value="', $field['value'], '" ', $field['input_attr'], ' />';

			// You "checking" me out? ;)
			elseif ($field['type'] == 'check')
				echo '
							<input type="hidden" name="', $key, '" value="0" /><input type="checkbox" name="', $key, '" id="', $key, '" ', !empty($field['value']) ? ' checked="checked"' : '', ' value="1" class="check" ', $field['input_attr'], ' />';

			// Always fun - select boxes!
			elseif ($field['type'] == 'select')
			{
				echo '
							<select name="', $key, '" id="', $key, '">';

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

			echo '
						</td>
					</tr>';
			}
		}
	}

	// Are there any custom fields?
	if (!empty($context['custom_fields']))
	{
		foreach ($context['custom_fields'] as $field)
		{
			echo '
					<tr valign="top">
						<td width="40%"><b>', $field['name'], ': </b><div class="smalltext">', $field['desc'], '</div></td>
						<td>', $field['input_html'], '</td>
					</tr>';
		}
	}

	echo '
				</table>
			</td>
		</tr>
	</table>';

	// Require them to agree here?
	if ($context['require_agreement'])
		echo '
	<table width="100%" align="center" border="0" cellspacing="0" cellpadding="5" class="tborder" style="border-top: 0;">
		<tr>
			<td class="windowbg2" style="padding-top: 8px; padding-bottom: 8px;">
				', $context['agreement'], '
			</td>
		</tr><tr>
			<td align="center" class="windowbg2">
				<label for="regagree"><input type="checkbox" name="regagree" onclick="checkAgree();" id="regagree" class="check" /> <b>', $txt['agree'], '</b></label>
			</td>
		</tr>
	</table>';

	echo '
	<br />
	<div align="center">
		<input type="submit" name="regSubmit" value="', $txt['register'], '" />
	</div>
</form>
<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[';

	// Uncheck the agreement thing....
	if ($context['require_agreement'])
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
	verificationHandle = new smfRegister("creator", ', empty($modSettings['password_strength']) ? 0 : $modSettings['password_strength'], ', regTextStrings);';

	// Have we got visual verification on the move?
	if ($context['visual_verification'])
		echo '
	captchaHandle = new smfCaptcha("', $context['verification_image_href'], '", ', $context['use_graphic_library'] ? 1 : 0, ');';

echo '
// ]]></script>';
}

// After registration... all done ;).
function template_after()
{
	global $context, $settings, $options, $txt, $scripturl;

	// Not much to see here, just a quick... "you're now registered!" or what have you.
	echo '
		<br />
		<table border="0" width="80%" cellpadding="3" cellspacing="0" class="tborder" align="center">
			<tr class="titlebg">
				<td>', $context['page_title'], '</td>
			</tr><tr class="windowbg">
				<td align="left">', $context['description'], '<br /><br /></td>
			</tr>
		</table>
		<br />';
}

// Template for giving instructions about COPPA activation.
function template_coppa()
{
	global $context, $settings, $options, $txt, $scripturl;

	// Formulate a nice complicated message!
	echo '
		<br />
		<table width="60%" cellpadding="4" cellspacing="0" border="0" class="tborder" align="center">
			<tr class="titlebg">
				<td>', $context['page_title'], '</td>
			</tr><tr class="windowbg">
				<td align="left">', $context['coppa']['body'], '<br /></td>
			</tr><tr class="windowbg">
				<td align="center">
					<a href="', $scripturl, '?action=coppa;form;member=', $context['coppa']['id'], '" target="_blank" class="new_win">', $txt['coppa_form_link_popup'], '</a>&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;<a href="', $scripturl, '?action=coppa;form;dl;member=', $context['coppa']['id'], '">', $txt['coppa_form_link_download'], '</a><br /><br />
				</td>
			</tr><tr class="windowbg">
				<td align="left">', $context['coppa']['many_options'] ? $txt['coppa_send_to_two_options'] : $txt['coppa_send_to_one_option'], '</td>
			</tr>';

	// Can they send by post?
	if (!empty($context['coppa']['post']))
	{
		echo '
			<tr class="windowbg">
				<td align="left"><b>1) ', $txt['coppa_send_by_post'], '</b></td>
			</tr><tr class="windowbg">
				<td align="left" style="padding-bottom: 1ex;">
					<div style="padding: 4px; width: 32ex; background-color: white; color: black; margin-left: 5ex; border: 1px solid black;">
						', $context['coppa']['post'], '
					</div>
				</td>
			</tr>';
	}

	// Can they send by fax??
	if (!empty($context['coppa']['fax']))
	{
		echo '
			<tr class="windowbg">
				<td align="left"><b>', !empty($context['coppa']['post']) ? '2' : '1', ') ', $txt['coppa_send_by_fax'], '</b></td>
			</tr><tr class="windowbg">
				<td align="left" style="padding-bottom: 1ex;">
					<div style="padding: 4px; width: 32ex; background-color: white; color: black; margin-left: 5ex; border: 1px solid black;">
						', $context['coppa']['fax'], '
					</div>
				</td>
			</tr>';
	}

	// Offer an alternative Phone Number?
	if ($context['coppa']['phone'])
	{
		echo '
			<tr class="windowbg" style="padding-bottom: 1ex;">
				<td align="left">', $context['coppa']['phone'], '</td>
			</tr>';
	}
	echo '
		</table>
		<br />';
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
					<i>', $txt['coppa_form_address'], '</i>: ', $context['ul'], '<br />
					', $context['ul'], '<br />
					', $context['ul'], '<br />
					', $context['ul'], '
				</td>
			</tr><tr>
				<td align="right">
					<i>', $txt['coppa_form_date'], '</i>: ', $context['ul'], '
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
		<link rel="stylesheet" type="text/css" href="', $settings['theme_url'], '/style.css" />
		<style type="text/css">';

	// Internet Explorer 4/5 and Opera 6 just don't do font sizes properly. (they are bigger...)
	if ($context['browser']['needs_size_fix'])
		echo '
			@import(', $settings['default_theme_url'], '/css/fonts-compat.css);';

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
				<param name="FileName" value="', $context['verification_sound_href'], ';format=.wav" />
			</object>';
	else
		echo '
			<object type="audio/x-wav" data="', $context['verification_sound_href'], ';format=.wav">
				<a href="', $context['verification_sound_href'], ';format=.wav">', $context['verification_sound_href'], ';format=.wav</a>
			</object>';
	echo '
			<br />
			<a href="', $context['verification_sound_href'], ';sound">', $txt['visual_verification_sound_again'], '</a><br />
			<a href="javascript:self.close();">', $txt['visual_verification_sound_close'], '</a><br />
			<a href="', $context['verification_sound_href'], ';format=.wav">', $txt['visual_verification_sound_direct'], '</a>
		</div>
	</body>
</html>';
}

function template_admin_register()
{
	global $context, $settings, $options, $scripturl, $txt, $modSettings;

	echo '
	<form action="', $scripturl, '?action=admin;area=regcenter" method="post" accept-charset="', $context['character_set'], '" name="postForm" id="postForm">
		<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
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
		<table border="0" cellspacing="0" cellpadding="4" align="center" width="70%" class="tborder">
			<tr class="titlebg">
				<td colspan="2" align="center">', $txt['admin_browse_register_new'], '</td>
			</tr>';
	if (!empty($context['registration_done']))
		echo '
			<tr class="windowbg2">
				<td colspan="2" align="center"><br />
					', $context['registration_done'], '
				</td>
			</tr><tr class="windowbg2">
				<td colspan="2" align="center"><hr /></td>
			</tr>';
	echo '
			<tr class="windowbg2">
				<th width="50%" align="right">
					<label for="user_input">', $txt['admin_register_username'], ':</label>
					<div class="smalltext" style="font-weight: normal;">', $txt['admin_register_username_desc'], '</div>
				</th>
				<td width="50%" align="left">
					<input type="text" name="user" id="user_input" size="30" maxlength="25" />
				</td>
			</tr><tr class="windowbg2">
				<th width="50%" align="right">
					<label for="email_input">', $txt['admin_register_email'], ':</label>
					<div class="smalltext" style="font-weight: normal;">', $txt['admin_register_email_desc'], '</div>
				</th>
				<td width="50%" align="left">
					<input type="text" name="email" id="email_input" size="30" />
				</td>
			</tr><tr class="windowbg2">
				<th width="50%" align="right">
					<label for="password_input">', $txt['admin_register_password'], ':</label>
					<div class="smalltext" style="font-weight: normal;">', $txt['admin_register_password_desc'], '</div>
				</th>
				<td width="50%" align="left">
					<input type="password" name="password" id="password_input" size="30" /><br />
				</td>
			</tr><tr class="windowbg2">
				<th width="50%" align="right">
					<label for="group_select">', $txt['admin_register_group'], ':</label>
					<div class="smalltext" style="font-weight: normal;">', $txt['admin_register_group_desc'], '</div>
				</th>
				<td width="50%" align="left">
					<select name="group" id="group_select">';

	foreach ($context['member_groups'] as $id => $name)
		echo '
						<option value="', $id, '">', $name, '</option>';
	echo '
					</select><br />
				</td>
			</tr><tr class="windowbg2">
				<th width="50%" align="right">
					<label for="emailPassword_check">', $txt['admin_register_email_detail'], ':</label>
					<div class="smalltext" style="font-weight: normal;">', $txt['admin_register_email_detail_desc'], '</div>
				</th>
				<td width="50%" align="left">
					<input type="checkbox" name="emailPassword" id="emailPassword_check" checked="checked"', !empty($modSettings['registration_method']) && $modSettings['registration_method'] == 1 ? ' disabled="disabled"' : '', ' class="check" /><br />
				</td>
			</tr><tr class="windowbg2">
				<th width="50%" align="right">
					<label for="emailActivate_check">', $txt['admin_register_email_activate'], ':</label>
				</th>
				<td width="50%" align="left">
					<input type="checkbox" name="emailActivate" id="emailActivate_check"', !empty($modSettings['registration_method']) && $modSettings['registration_method'] == 1 ? ' checked="checked"' : '', ' onclick="onCheckChange();" class="check" /><br />
				</td>
			</tr><tr class="windowbg2">
				<td width="100%" colspan="2" align="right">
					<input type="submit" name="regSubmit" value="', $txt['register'], '" />
					<input type="hidden" name="sa" value="register" />
				</td>
			</tr>
		</table>
		<input type="hidden" name="sc" value="', $context['session_id'], '" />
	</form>';
}

// Form for editing the agreement shown for people registering to the forum.
function template_edit_agreement()
{
	global $context, $settings, $options, $scripturl, $txt;

	// Just a big box to edit the text file ;).
	echo '
		<table border="0" cellspacing="0" cellpadding="4" align="center" width="80%" class="tborder">
			<tr class="titlebg">
				<td align="center">', $txt['registration_agreement'], '</td>
			</tr>';

	// Warning for if the file isn't writable.
	if (!empty($context['warning']))
		echo '
			<tr class="windowbg2">
				<td style="color: red; font-weight: bold;" align="center">
					', $context['warning'], '
				</td>
			</tr>';

	// Are there more than one language to choose from?
	if (count($context['editable_agreements']) > 1)
	{
		echo '
			<tr class="windowbg2">
				<td align="center">
					<div align="left" style="width: 94%">
						<form action="', $scripturl, '?action=admin;area=regcenter" id="change_reg" method="post" accept-charset="', $context['character_set'], '" style="display: inline;">
							<b>', $txt['admin_agreement_select_language'], ':</b>&nbsp;
							<select name="agree_lang" onchange="document.getElementById(\'change_reg\').submit();">';

		foreach ($context['editable_agreements'] as $file => $name)
			echo '
								<option value="', $file, '" ', $context['current_agreement'] == $file ? 'selected="selected"' : '', '>', $name, '</option>';

		echo '
							</select>
							<input type="hidden" name="sa" value="agreement" />
							<input type="hidden" name="sc" value="', $context['session_id'], '" />
							<input type="submit" name="change" value="', $txt['admin_agreement_select_language_change'], '" />
						</form>
					</div>
				</td>
			</tr>';
	}

	echo '
			<tr class="windowbg2">
				<td align="center" style="padding-bottom: 1ex; padding-top: 2ex;">
					<form action="', $scripturl, '?action=admin;area=regcenter" method="post" accept-charset="', $context['character_set'], '">';

	// Show the actual agreement in an oversized text box.
	echo '
						<textarea cols="70" rows="20" name="agreement" style="width: 94%; margin-bottom: 1ex;">', $context['agreement'], '</textarea><br />
						<label for="requireAgreement"><input type="checkbox" name="requireAgreement" id="requireAgreement"', $context['require_agreement'] ? ' checked="checked"' : '', ' value="1" /> ', $txt['admin_agreement'], '.</label><br />
						<br />
						<input type="submit" value="', $txt['save'], '" />
						<input type="hidden" name="agree_lang" value="', $context['current_agreement'], '" />
						<input type="hidden" name="sa" value="agreement" />
						<input type="hidden" name="sc" value="', $context['session_id'], '" />
					</form>
				</td>
			</tr>
		</table>';
}

function template_edit_reserved_words()
{
	global $context, $settings, $options, $scripturl, $txt;

	echo '
		<form action="', $scripturl, '?action=admin;area=regcenter" method="post" accept-charset="', $context['character_set'], '">
			<table border="0" cellspacing="1" class="bordercolor" align="center" cellpadding="4" width="80%">
				<tr class="titlebg">
					<td align="center">
						', $txt['admin_reserved_set'], '
					</td>
				</tr><tr>
					<td class="windowbg2" align="center">
						<div style="width: 80%;">
							<div style="margin-bottom: 2ex;">', $txt['admin_reserved_line'], '</div>
							<textarea cols="30" rows="6" name="reserved" style="width: 98%;">', implode("\n", $context['reserved_words']), '</textarea><br />

							<div align="left" style="margin-top: 2ex;">
								<label for="matchword"><input type="checkbox" name="matchword" id="matchword" ', $context['reserved_word_options']['match_word'] ? 'checked="checked"' : '', ' class="check" /> ', $txt['admin_match_whole'], '</label><br />
								<label for="matchcase"><input type="checkbox" name="matchcase" id="matchcase" ', $context['reserved_word_options']['match_case'] ? 'checked="checked"' : '', ' class="check" /> ', $txt['admin_match_case'], '</label><br />
								<label for="matchuser"><input type="checkbox" name="matchuser" id="matchuser" ', $context['reserved_word_options']['match_user'] ? 'checked="checked"' : '', ' class="check" /> ', $txt['admin_check_user'], '</label><br />
								<label for="matchname"><input type="checkbox" name="matchname" id="matchname" ', $context['reserved_word_options']['match_name'] ? 'checked="checked"' : '', ' class="check" /> ', $txt['admin_check_display'], '</label><br />
							</div>

							<input type="submit" value="', $txt['save'], '" name="save_reserved_names" style="margin: 1ex;" />
						</div>
					</td>
				</tr>
			</table>
			<input type="hidden" name="sa" value="reservednames" />
			<input type="hidden" name="sc" value="', $context['session_id'], '" />
		</form>';
}

function template_admin_settings()
{
	global $context, $settings, $options, $scripturl, $txt, $modSettings;

	// Javascript for the verification image.
	if ($context['use_graphic_library'])
	{
	echo '
	<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
		function refreshImages()
		{
			var imageType = document.getElementById(\'visual_verification_type_select\').value;
			document.getElementById(\'verification_image\').src = \'', $context['verification_image_href'], ';type=\' + imageType;
		}
	// ]]></script>';
	}

	echo '
	<form action="', $scripturl, '?action=admin;area=regcenter" method="post" accept-charset="', $context['character_set'], '">
		<table border="0" cellspacing="1" cellpadding="4" align="center" width="100%" class="tborder">
			<tr class="titlebg">
				<td align="center">', $txt['settings'], '</td>
			</tr>
			<tr class="windowbg2">
				<td align="center">';

	// Functions to do some nice box disabling dependant on age restrictions.
	echo '
					<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
						function checkCoppa()
						{
							var coppaDisabled = document.getElementById(\'coppaAge_input\').value == 0;
							document.getElementById(\'coppaType_select\').disabled = coppaDisabled;

							var disableContacts = coppaDisabled || document.getElementById(\'coppaType_select\').options[document.getElementById(\'coppaType_select\').selectedIndex].value != 1;
							document.getElementById(\'coppaPost_input\').disabled = disableContacts;
							document.getElementById(\'coppaFax_input\').disabled = disableContacts;
							document.getElementById(\'coppaPhone_input\').disabled = disableContacts;
						}
					// ]]></script>';
	echo '
					<table border="0" cellspacing="0" cellpadding="4" align="center" width="100%">
						<tr class="windowbg2">
							<th width="50%" align="right">
								<label for="registration_method_select">', $txt['admin_setting_registration_method'], '</label> <span style="font-weight: normal;">(<a href="', $scripturl, '?action=helpadmin;help=registration_method" onclick="return reqWin(this.href);">?</a>)</span>:
							</th>
							<td width="50%" align="left">
								<select name="registration_method" id="registration_method_select">
									<option value="0"', empty($modSettings['registration_method']) ? ' selected="selected"' : '', '>', $txt['admin_setting_registration_standard'], '</option>
									<option value="1"', !empty($modSettings['registration_method']) && $modSettings['registration_method'] == 1 ? ' selected="selected"' : '', '>', $txt['admin_setting_registration_activate'], '</option>
									<option value="2"', !empty($modSettings['registration_method']) && $modSettings['registration_method'] == 2 ? ' selected="selected"' : '', '>', $txt['admin_setting_registration_approval'], '</option>
									<option value="3"', !empty($modSettings['registration_method']) && $modSettings['registration_method'] == 3 ? ' selected="selected"' : '', '>', $txt['admin_setting_registration_disabled'], '</option>
								</select>
							</td>
						</tr>
						<tr class="windowbg2">
							<th width="50%" align="right">
								<label for="enableOpenID">', $txt['admin_setting_enableOpenID'], '</label>:
							</th>
							<td width="50%" align="left">
								<input type="checkbox" name="enableOpenID" id="enableOpenID" ', !empty($modSettings['enableOpenID']) ? 'checked="checked"' : '', ' class="check" />
								<span class="smalltext">', $txt['admin_setting_enableOpenID_warning'], '</span>
							</td>
						</tr>
						<tr class="windowbg2">
							<th width="50%" align="right">
								<label for="notify_new_registration_check">', $txt['admin_setting_notify_new_registration'], '</label>:
							</th>
							<td width="50%" align="left">
								<input type="checkbox" name="notify_new_registration" id="notify_new_registration_check" ', !empty($modSettings['notify_new_registration']) ? 'checked="checked"' : '', ' class="check" />
							</td>
						</tr><tr class="windowbg2">
							<th width="50%" align="right">
								<label for="send_welcomeEmail_check">', $txt['admin_setting_send_welcomeEmail'], '</label> <span style="font-weight: normal;">(<a href="', $scripturl, '?action=helpadmin;help=send_welcomeEmail" onclick="return reqWin(this.href);">?</a>)</span>:
							</th>
							<td width="50%" align="left">
								<input type="checkbox" name="send_welcomeEmail" id="send_welcomeEmail_check"', !empty($modSettings['send_welcomeEmail']) ? ' checked="checked"' : '', ' class="check" />
							</td>
						</tr><tr class="windowbg2">
							<th width="50%" align="right">
								<label for="password_strength_select">', $txt['admin_setting_password_strength'], '</label> <span style="font-weight: normal;">(<a href="', $scripturl, '?action=helpadmin;help=password_strength" onclick="return reqWin(this.href);">?</a>)</span>:
							</th>
							<td width="50%" align="left">
								<select name="password_strength" id="password_strength_select">
									<option value="0"', empty($modSettings['password_strength']) ? ' selected="selected"' : '', '>', $txt['admin_setting_password_strength_low'], '</option>
									<option value="1"', !empty($modSettings['password_strength']) && $modSettings['password_strength'] == 1 ? ' selected="selected"' : '', '>', $txt['admin_setting_password_strength_medium'], '</option>
									<option value="2"', !empty($modSettings['password_strength']) && $modSettings['password_strength'] == 2 ? ' selected="selected"' : '', '>', $txt['admin_setting_password_strength_high'], '</option>
								</select>
							</td>
						</tr><tr class="windowbg2" valign="top">
							<th width="50%" align="right">
								<label for="visual_verification_type_select">
									', $txt['admin_setting_image_verification_type'], ':<br />
									<span class="smalltext" style="font-weight: normal;">
										', $txt['admin_setting_image_verification_type_desc'], '
									</span>
								</label>
							</th>
							<td width="50%" align="left">
								<select name="visual_verification_type" id="visual_verification_type_select" ', $context['use_graphic_library'] ? 'onchange="refreshImages();"' : '', '>
									<option value="1" ', !empty($modSettings['visual_verification_type']) && $modSettings['visual_verification_type'] == 1 ? 'selected="selected"' : '', '>', $txt['admin_setting_image_verification_off'], '</option>
									<option value="2" ', !empty($modSettings['visual_verification_type']) && $modSettings['visual_verification_type'] == 2 ? 'selected="selected"' : '', '>', $txt['admin_setting_image_verification_vsimple'], '</option>
									<option value="3" ', !empty($modSettings['visual_verification_type']) && $modSettings['visual_verification_type'] == 3 ? 'selected="selected"' : '', '>', $txt['admin_setting_image_verification_simple'], '</option>
									<option value="4" ', !empty($modSettings['visual_verification_type']) && $modSettings['visual_verification_type'] == 4 ? 'selected="selected"' : '', '>', $txt['admin_setting_image_verification_medium'], '</option>
									<option value="5" ', !empty($modSettings['visual_verification_type']) && $modSettings['visual_verification_type'] == 5 ? 'selected="selected"' : '', '>', $txt['admin_setting_image_verification_high'], '</option>
								</select><br />';
	if ($context['use_graphic_library'])
		echo '
								<img src="', $context['verification_image_href'], ';type=', empty($modSettings['visual_verification_type']) ? 0 : $modSettings['visual_verification_type'], '" alt="', $txt['admin_setting_image_verification_sample'], '" id="verification_image" /><br />';
	else
	{
		echo '
								<span class="smalltext">', $txt['admin_setting_image_verification_nogd'], '</span>';
	}
	echo '
							</td>
						</tr><tr class="windowbg2">
							<td width="100%" colspan="2" align="center">
								<hr />
							</td>
						</tr><tr class="windowbg2" valign="top">
							<th width="50%" align="right">
								<label for="coppaAge_input">', $txt['admin_setting_coppaAge'], '</label> <span style="font-weight: normal;">(<a href="', $scripturl, '?action=helpadmin;help=coppaAge" onclick="return reqWin(this.href);">?</a>)</span>:
								<div class="smalltext" style="font-weight: normal;">', $txt['admin_setting_coppaAge_desc'], '</div>
							</th>
							<td width="50%" align="left">
								<input type="text" name="coppaAge" id="coppaAge_input" value="', !empty($modSettings['coppaAge']) ? $modSettings['coppaAge'] : '', '" size="3" maxlength="3" onkeyup="checkCoppa();" />
							</td>
						</tr><tr class="windowbg2" valign="top">
							<th width="50%" align="right">
								<label for="coppaType_select">', $txt['admin_setting_coppaType'], '</label> <span style="font-weight: normal;">(<a href="', $scripturl, '?action=helpadmin;help=coppaType" onclick="return reqWin(this.href);">?</a>)</span>:
							</th>
							<td width="50%" align="left">
								<select name="coppaType" id="coppaType_select" onchange="checkCoppa();">
									<option value="0"', empty($modSettings['coppaType']) ? ' selected="selected"' : '', '>', $txt['admin_setting_coppaType_reject'], '</option>
									<option value="1"', !empty($modSettings['coppaType']) && $modSettings['coppaType'] == 1 ? ' selected="selected"' : '', '>', $txt['admin_setting_coppaType_approval'], '</option>
								</select>
							</td>
						</tr><tr class="windowbg2" valign="top">
							<th width="50%" align="right">
								<label for="coppaPost_input">', $txt['admin_setting_coppaPost'], '</label> <span style="font-weight: normal;">(<a href="', $scripturl, '?action=helpadmin;help=coppaPost" onclick="return reqWin(this.href);">?</a>)</span>:
								<div class="smalltext" style="font-weight: normal;">', $txt['admin_setting_coppaPost_desc'], '</div>
							</th>
							<td width="50%" align="left">
								<textarea name="coppaPost" id="coppaPost_input" rows="4" cols="35">', $context['coppaPost'], '</textarea>
							</td>
						</tr><tr class="windowbg2" valign="top">
							<th width="50%" align="right">
								<label for="coppaFax_input">', $txt['admin_setting_coppaFax'], '</label> <span style="font-weight: normal;">(<a href="', $scripturl, '?action=helpadmin;help=coppaPost" onclick="return reqWin(this.href);">?</a>)</span>:
								<div class="smalltext" style="font-weight: normal;">', $txt['admin_setting_coppaPost_desc'], '</div>
							</th>
							<td width="50%" align="left">
								<input type="text" name="coppaFax" id="coppaFax_input" value="', !empty($modSettings['coppaFax']) ? $modSettings['coppaFax'] : '', '" size="22" maxlength="35" />
							</td>
						</tr><tr class="windowbg2" valign="top">
							<th width="50%" align="right">
								<label for="coppaPhone_input">', $txt['admin_setting_coppaPhone'], '</label> <span style="font-weight: normal;">(<a href="', $scripturl, '?action=helpadmin;help=coppaPost" onclick="return reqWin(this.href);">?</a>)</span>:
								<div class="smalltext" style="font-weight: normal;">', $txt['admin_setting_coppaPost_desc'], '</div>
							</th>
							<td width="50%" align="left">
								<input type="text" name="coppaPhone" id="coppaPhone_input" value="', !empty($modSettings['coppaPhone']) ? $modSettings['coppaPhone'] : '', '" size="22" maxlength="35" />
							</td>
						</tr><tr class="windowbg2">
							<td width="100%" colspan="3" align="right">
								<input type="submit" name="save" value="', $txt['save'], '" />
								<input type="hidden" name="sa" value="settings" />
							</td>
						</tr>
					</table>';

	// Handle disabling of some of the input boxes.
	echo '
					<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[';

	if (empty($modSettings['coppaAge']) || empty($modSettings['coppaType']))
		echo '
						document.getElementById(\'coppaPost_input\').disabled = true;
						document.getElementById(\'coppaFax_input\').disabled = true;
						document.getElementById(\'coppaPhone_input\').disabled = true;';
	if (empty($modSettings['coppaAge']))
		echo '
						document.getElementById(\'coppaType_select\').disabled = true;';

	echo '
					// ]]></script>
				</td>
			</tr>
		</table>
		<input type="hidden" name="sc" value="', $context['session_id'], '" />
	</form>';
}

?>