<?php
/**
* @version $Id: smf_registration.html.php,v 1.4 2006-12-03 14:43:20 orstio Exp $
* @package Mambo_4.5.1
* @copyright (C) 2000 - 2004 Miro International Pty Ltd
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* Mambo is Free Software
*/

/** ensure this file is being included by a parent file */
if (!defined('_VALID_MOS'))
	die('Direct Access to this location is not allowed.');

class HTML_smf_registration 
{
	function lostPassForm($option) 
	{
		echo '
<div class="componentheading">
	', T_('Lost your Password?'), '
</div>
<form action="index.php" method="post">
	<table cellpadding="0" cellspacing="0" border="0" width="100%" class="contentpane">
		<tr>
			<td colspan="2">', printf(T_('Please enter your Username and e-mail address then click on the Send Password button.%s You will receive a new password shortly.  Use the new password to access the site.'), '<br />'), '</td>
		</tr><tr>
			<td>', T_('Username:'), '</td>
			<td><input type="text" name="checkusername" class="inputbox" size="40" maxlength="25" /></td>
		</tr><tr>
			<td>', T_('E-mail Address:'), '</td>
			<td><input type="text" name="confirmEmail" class="inputbox" size="40" /></td>
		</tr><tr>
			<td colspan="2">
				<input type="hidden" name="option" value="', $option, '" />
				<input type="hidden" name="task" value="sendNewPass" /> 
				<input type="submit" class="button" value="', T_('Send Password'), '" />
			</td>
		</tr>
	</table>
</form>';
	}
	
	function resendActivationForm($option)
	{
		echo'
<div class="componentheading">
	', T_('Lost your Activation Code?'), '
</div>
<form action="index.php" method="post">
	<table cellpadding="0" cellspacing="0" border="0" width="100%" class="contentpane">
		<tr>
			<td colspan="2">', printf(T_('Please enter your Username and e-mail address then click on the Send Activation button.%s You will receive a new activation email shortly.  Use the new activation code to access the site.'), '<br />'), '</td>
		</tr><tr>
			<td>', T_('Username:'), '</td>
			<td><input type="text" name="checkusername" class="inputbox" size="40" maxlength="25" /></td>
		</tr><tr>
			<td>', T_('E-mail Address:'), '</td>
			<td><input type="text" name="confirmEmail" class="inputbox" size="40" /></td>
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
			alert("', html_entity_decode(T_('You must agree to the terms.')), '");
		} else if (form.name.value == "") {
			alert("', html_entity_decode(T_('Please enter your name.')), '");
		} else if (form.username.value == "") {
			alert("', html_entity_decode(T_('Please enter a user name.')), '");
		} else if (r.exec(form.username.value) || form.username.value.length < 3) {
			alert("', printf( html_entity_decode(T_("Please enter a valid %s.  No spaces, more than %d characters and containing only the characters 0-9,a-z, or A-Z")), html_entity_decode(T_('Please enter a user name.')), 2 ), '");
		} else if (form.email.value == "") {
			alert("', html_entity_decode(T_('Please enter a valid e-mail address.')), '");
		} else if (form.password.value.length < 6) {
			alert("', html_entity_decode(T_('Please enter a valid password -- more than 6 characters with no spaces and containing only the characters 0-9, a-z, or A-Z')), '");
		} else if (form.password2.value == "") {
			alert("', html_entity_decode(T_('Please verify the verification password.')), '");
		} else if ((form.password.value != "") && (form.password.value != form.password2.value)){
			alert("', html_entity_decode(T_('Password and verification do not match, please try again.')), '");
		} else if (r.exec(form.password.value)) {
			alert("', printf( html_entity_decode(T_("Please enter a valid %s.  No spaces, more than %d characters and containing only the characters 0-9,a-z, or A-Z")), html_entity_decode(T_('Password:')), 6 ), '");
		} else {
			form.submit();
		}
	}
// ]]></script>

<div class="componentheading">
	', T_('Registration'), '
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
			<td colspan="4">', T_('Fields marked with an asterisk (*) are required.'), '</td>
		</tr><tr>
			<td width="30%">', T_('Display Name:'), ' *</td>
			<td colspan="3"><input type="text" name="name" size="40" value="" class="inputbox" /></td>
		</tr><tr>
			<td>', T_('Username:'), ' *</td>
			<td colspan="3"><input type="text" name="username" size="40" value="" class="inputbox" /></td>
		</tr><tr>
			<td>', T_('E-mail:'), ' *</td>
			<td colspan="2"><input type="text" name="email" size="40" value="" class="inputbox" /></td>
			<td width="30%" align="left">
				<input type="checkbox" name="hideEmail" value="checked" class="check" />Hide email address from public?
			</td>
		</tr><tr>
			<td>', T_('Password:'), ' *</td>
			<td colspan="3"><input class="inputbox" type="password" name="password" size="40" value="" /></td>
		</tr><tr>
			<td>', T_('Verify Password:'), ' *</td>
			<td colspan="3"><input class="inputbox" type="password" name="password2" size="40" value="" /></td>
		</tr>';
		if ($im == 'on')
			echo '
		<tr>
			<td>ICQ #</td>
			<td colspan="3"><input class="inputbox" type="text" name="ICQ" size="40" value="" /></td>
		</tr><tr>
			<td>AIM</td>
			<td colspan="3"><input class="inputbox" type="text" name="AIM" size="40" value="" /></td>
		</tr><tr>
			<td>YIM</td>
			<td colspan="3"><input class="inputbox" type="text" name="YIM" size="40" value="" /></td>
		</tr><tr>
			<td>MSN</td>
			<td colspan="3"><input class="inputbox" type="text" name="MSN" size="40" value="" /></td>
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
				<input type="button" value="', T_('Send Registration'), '" class="button" onclick="submitbutton()" />
			</td>
		</tr>	
	</table>

</form>';

	}
}

?>