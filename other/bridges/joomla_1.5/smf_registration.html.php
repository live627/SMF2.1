<?php
/**
* @version $Id: smf_registration.html.php,v 1.3 2006-11-17 22:27:07 grudge Exp $
* @package Mambo_4.5.1
* @copyright (C) 2000 - 2004 Miro International Pty Ltd
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* Mambo is Free Software
*/

/** ensure this file is being included by a parent file */
if (!defined('_VALID_MOS'))
	die('Direct Access to this location is not allowed.');

/**
* @package Mambo_4.5.1
*/
class HTML_smf_registration 
{
	function lostPassForm($option) 
	{
		echo '
<div class="componentheading">
	', _PROMPT_PASSWORD, '
</div>
<form action="index.php" method="post">
	<table cellpadding="0" cellspacing="0" border="0" width="100%" class="contentpane">
		<tr>
			<td colspan="2">', _NEW_PASS_DESC, '</td>
		</tr><tr>
			<td>', _PROMPT_UNAME, '</td>
			<td><input type="text" name="checkusername" class="inputbox" size="40" maxlength="25" /></td>
		</tr><tr>
			<td>', _PROMPT_EMAIL, '</td>
			<td><input type="text" name="confirmEmail" class="inputbox" size="40" /></td>
		</tr><tr>
			<td colspan="2">
				<input type="hidden" name="option" value="', $option, '" />
				<input type="hidden" name="task" value="sendNewPass" /> 
				<input type="submit" class="button" value="', _BUTTON_SEND_PASS, '" />
			</td>
		</tr>
	</table>
</form>';
	}
	
	function resendActivationForm($option)
	{
		echo'
<div class="componentheading">
	', _PROMPT_PASSWORD, '
</div>
<form action="index.php" method="post">
	<table cellpadding="0" cellspacing="0" border="0" width="100%" class="contentpane">
		<tr>
			<td colspan="2">', _NEW_PASS_DESC, '</td>
		</tr><tr>
			<td>', _PROMPT_UNAME, '</td>
			<td><input type="text" name="checkusername" class="inputbox" size="40" maxlength="25" /></td>
		</tr><tr>
			<td colspan="2">
				<input type="hidden" name="option" value="', $option, '" />
				<input type="hidden" name="task" value="sendNewCode" /> 
				<input type="submit" class="button" value="Resend Activation" />
			</td>
		</tr>
	</table>
</form>';
	}

	function registerForm($option, $useractivation, $context, $agreement_required, $im) 
	{
		global $boarddir, $context, $db_name;
		global $db_prefix, $mosConfig_db;
		
		echo'
<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
	function submitbutton() 
	{
		var form = document.mosForm;
		var r = new RegExp("[\<|\>|\"|\'|\%|\;|\(|\)|\&|\+|\-]", "i");

		// do field validation
		if (form.agree.checked == false  && form.agree.value != "not_required") {
			alert("You must agree to the terms");
		} else if (form.name.value == "") {
			alert("', html_entity_decode(_REGWARN_NAME), '");
		} else if (form.username.value == "") {
			alert("', html_entity_decode(_REGWARN_UNAME), '");
		} else if (r.exec(form.username.value) || form.username.value.length < 3) {
			alert("', sprintf(html_entity_decode(_VALID_AZ09), html_entity_decode(_PROMPT_UNAME), 2), '");
		} else if (form.email.value == "") {
			alert("', html_entity_decode(_REGWARN_MAIL), '");
		} else if (form.password.value.length < 6) {
			alert("', html_entity_decode(_REGWARN_PASS), '");
		} else if (form.password2.value == "") {
			alert("', html_entity_decode(_REGWARN_VPASS1), '");
		} else if ((form.password.value != "") && (form.password.value != form.password2.value)){
			alert("', html_entity_decode(_REGWARN_VPASS2), '");
		} else if (r.exec(form.password.value)) {
			alert("', sprintf(html_entity_decode(_VALID_AZ09), html_entity_decode(_REGISTER_PASS), 6), '");
		} else {
			form.submit();
		}
	}
// ]]></script>

<div class="componentheading">
	', _REGISTER_TITLE, '
</div>
<form action="index.php" method="post" name="mosForm">
	<table cellpadding="0" cellspacing="0" border="0" width="100%" class="contentpane">';
		if ($agreement_required == 'on')
		{
			echo '
		<tr>
			<td colspan="4">';
			$agreement = file_exists($boarddir . '/agreement.txt') ? nl2br(implode('', file($boarddir . '/agreement.txt'))) : '';
			echo '
				', $agreement, "\n", '
			</td>
		</tr><tr>
			<td align="center" class="windowbg2" colspan="4">
				<input type="checkbox" name="agree" class="check" id="agree" /> <b>I agree</b>
			</td>
		</tr>';
		}
		else
			echo '
					<input type="hidden" name="agree" class="check" id="agree" value="not_required" />';
		echo'
		<tr>
			<td colspan="4">', _REGISTER_REQUIRED, '</td>
		</tr><tr>
			<td width="30%">', _REGISTER_NAME, ' *</td>
			<td colspan="3"><input type="text" name="name" size="40" value="" class="inputbox" /></td>
		</tr><tr>
			<td>', _REGISTER_UNAME, ' *</td>
			<td colspan="3"><input type="text" name="username" size="40" value="" class="inputbox" /></td>
		</tr><tr>
			<td>', _REGISTER_EMAIL, ' *</td>
			<td colspan="2"><input type="text" name="email" size="40" value="" class="inputbox" /></td>
			<td width="30%" align="left">
				<input type="checkbox" name="hide_email" value="checked" class="check" />Hide email address from public?
			</td>
		</tr><tr>
			<td>', _REGISTER_PASS, ' *</td>
			<td colspan="3"><input class="inputbox" type="password" name="password" size="40" value="" /></td>
		</tr><tr>
			<td>', _REGISTER_VPASS, ' *</td>
			<td colspan="3"><input class="inputbox" type="password" name="password2" size="40" value="" /></td>
		</tr>';
		if ($im == 'on')
			echo '
		<tr>
			<td>icq #</td>
			<td colspan="3"><input class="inputbox" type="text" name="icq" size="40" value="" /></td>
		</tr><tr>
			<td>aim</td>
			<td colspan="3"><input class="inputbox" type="text" name="aim" size="40" value="" /></td>
		</tr><tr>
			<td>yim</td>
			<td colspan="3"><input class="inputbox" type="text" name="yim" size="40" value="" /></td>
		</tr><tr>
			<td>msn</td>
			<td colspan="3"><input class="inputbox" type="text" name="msn" size="40" value="" /></td>
		</tr>';

		echo '
		<tr>
			<td colspan="4">&nbsp;</td>
		</tr><tr>
			<td colspan="4">
				<input type="hidden" name="id" value="0" />
				<input type="hidden" name="gid" value="0" />
				<input type="hidden" name="useractivation" value="'. $useractivation.'" />
				<input type="hidden" name="option" value="'. $option.'" />
				<input type="hidden" name="task" value="saveRegistration" />
				<input type="button" value="', _BUTTON_SEND_REG, '" class="button" onclick="submitbutton()" />
			</td>
		</tr>	
	</table>

</form>';

	}
}

?>