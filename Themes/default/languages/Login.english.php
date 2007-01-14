<?php
// Version: 2.0 Alpha; Login

// Important! Before editing these language files please read the text at the topic of index.english.php.

$txt['need_username'] = 'You should fill in a username.';
$txt['no_password'] = 'You didn\'t enter your password.';
$txt['incorrect_password'] = 'Password incorrect';
$txt['choose_username'] = 'Choose username';
$txt['maintain_mode'] = 'Maintenance Mode';
$txt['registration_successful'] = 'Registration successful';
$txt['now_a_member'] = 'Success! You are now a member of the Forum.';
// Use numeric entities in the below string.
$txt['your_password'] = 'and your password is';
$txt['valid_email_needed'] = 'Please enter a valid email address, %s.';
$txt['required_info'] = 'Required Information';
$txt['identification_by_smf'] = 'Used only for identification by SMF.';
$txt['agree'] = 'I Agree';
$txt['decline'] = 'I Do Not Agree';
$txt['warning'] = 'Warning!';
$txt['only_members_can_access'] = 'Only registered members are allowed to access this section.';
$txt['login_below'] = 'Please login below or';
$txt['login_or_register'] = 'register an account';
$txt['login_with_forum'] = 'with %s.';
// Use numeric entities in the below two strings.
$txt['may_change_in_profile'] = 'You may change it after you login by going to the profile page, or by visiting this page after you login:';
$txt['your_username_is'] = 'Your username is: ';

$txt['login_hash_error'] = 'Password security has recently been upgraded.  Please enter your password again.';

$txt['register_age_confirmation'] = 'I am at least %d years old';

// Use numeric entities in the below six strings.
$txt['register_subject'] = 'Welcome to ' . $context['forum_name'];

// For the below three messages, %1$s is the display name, %2$s is the username, %3$s is the password, %4$s is the activation code, and %5$s is the activation link (the last two are only for activation.)
$txt['register_immediate_message'] = 'You are now registered with an account at ' . $context['forum_name'] . ', %1$s!' . "\n\n" . 'Your account\'s username is %2$s and its password is %3$s.' . "\n\n" . 'You may change your password after you login by going to your profile, or by visiting this page after you login:' . "\n\n" .$scripturl . '?action=profile' . "\n\n" . $txt['regards_team'];
$txt['register_activate_message'] = 'You are now registered with an account at ' . $context['forum_name'] . ', %1$s!' . "\n\n" . 'Your account\'s username is %2$s and its password is %3$s (which can be changed later.)' . "\n\n" . 'Before you can login, you first need to activate your account. To do so, please follow this link:' . "\n\n" . '%5$s' . "\n\n" . 'Should you have any problems with activation, please use the code "%4$s".' . "\n\n" . $txt['regards_team'];
$txt['register_pending_message'] = 'Your registration request at ' . $context['forum_name'] . ' has been received, %1$s.' . "\n\n" . 'The username you registered with was %2$s and the password was %3$s.' . "\n\n" . 'Before you can login and start using the forum, your request will be reviewed and approved.  When this happens, you will receive another email from this address.' . "\n\n" . $txt['regards_team'];

// For the below two messages, %1$s is the user's display name, %2$s is their username, %3$s is the activation code, and %4$s is the activation link (the last two are only for activation.)
$txt['resend_activate_message'] = 'You are now registered with an account at ' . $context['forum_name'] . ', %1$s!' . "\n\n" . 'Your username is "%2$s".' . "\n\n" . 'Before you can login, you first need to activate your account. To do so, please follow this link:' . "\n\n" . '%4$s' . "\n\n" . 'Should you have any problems with activation, please use the code "%3$s".' . "\n\n" . $txt['regards_team'];
$txt['resend_pending_message'] = 'Your registration request at ' . $context['forum_name'] . ' has been received, %1$s.' . "\n\n" . 'The username you registered with was %2$s.' . "\n\n" . 'Before you can login and start using the forum, your request will be reviewed and approved.  When this happens, you will receive another email from this address.' . "\n\n" . $txt['regards_team'];

$txt['ban_register_prohibited'] = 'Sorry, you are not allowed to register on this forum.';
$txt['under_age_registration_prohibited'] = 'Sorry, but users under the age of %d are not allowed to register on this forum.';

$txt['activate_account'] = 'Account activation';
$txt['activate_success'] = 'Your account has been successfully activated. You can now proceed to login.';
$txt['activate_not_completed1'] = 'Your email address needs to be validated before you can login.';
$txt['activate_not_completed2'] = 'Need another activation email?';
$txt['activate_after_registration'] = 'Thank you for registering. You will receive an email soon with a link to activate your account.  If you don\'t receive an email after some time, check your spam folder.';
$txt['invalid_userid'] = 'User does not exist';
$txt['invalid_activation_code'] = 'Invalid activation code';
$txt['invalid_activation_username'] = 'Username or email';
$txt['invalid_activation_new'] = 'If you registered with the wrong email address, type a new one and your password here.';
$txt['invalid_activation_new_email'] = 'New email address';
$txt['invalid_activation_password'] = 'Old password';
$txt['invalid_activation_resend'] = 'Resend activation code';
$txt['invalid_activation_known'] = 'If you already know your activation code, please type it here.';
$txt['invalid_activation_retry'] = 'Activation code';
$txt['invalid_activation_submit'] = 'Activate';

$txt['coppa_no_concent'] = 'The administrator has still not received parent/guardian consent for your account.';
$txt['coppa_need_more_details'] = 'Need more details?';

$txt['awaiting_delete_account'] = 'Your account has been marked for deletion!<br />If you wish to restore your account, please check the &quot;Reactivate my account&quot; box, and login again.';
$txt['undelete_account'] = 'Reactivate my account';

// Use numeric entities in the below three strings.
$txt['change_password'] = 'New Password Details';
$txt['change_password_login'] = 'Your login details at';
$txt['change_password_new'] = 'have been changed and your password reset. Below are your new login details.';

$txt['in_maintain_mode'] = 'This board is in Maintenance Mode.';

// These two are used as a javascript alert; please use international characters directly, not as entities.
$txt['register_agree'] = 'Please read and accept the agreement before registering.';
$txt['register_passwords_differ_js'] = 'The two passwords you entered are not the same!';

$txt['approval_after_registration'] = 'Thank you for registering. The admin must approve your registration before you may begin to use your account, you will receive an email shortly advising you of the admins decision.';

$txt['admin_settings_desc'] = 'Here you can change a variety of settings related to registration of new members.';

$txt['admin_setting_registration_method'] = 'Method of registration employed for new members';
$txt['admin_setting_registration_disabled'] = 'Registration Disabled';
$txt['admin_setting_registration_standard'] = 'Immediate Registration';
$txt['admin_setting_registration_activate'] = 'Member Activation';
$txt['admin_setting_registration_approval'] = 'Member Approval';
$txt['admin_setting_notify_new_registration'] = 'Notify administrators when a new member joins';
$txt['admin_setting_send_welcomeEmail'] = 'Send welcome email to new members';

$txt['admin_setting_password_strength'] = 'Required strength for user passwords';
$txt['admin_setting_password_strength_low'] = 'Low - 4 character minimum';
$txt['admin_setting_password_strength_medium'] = 'Medium - cannot contain username';
$txt['admin_setting_password_strength_high'] = 'High - mixture of different characters';

$txt['admin_setting_disable_visual_verification'] = 'Disable the use of the visual verification on registration';

$txt['admin_setting_coppaAge'] = 'Age below which to apply registration restrictions';
$txt['admin_setting_coppaAge_desc'] = '(Set to 0 to disable)';
$txt['admin_setting_coppaType'] = 'Action to take when a user below minimum age registers';
$txt['admin_setting_coppaType_reject'] = 'Reject their registration';
$txt['admin_setting_coppaType_approval'] = 'Require parent/guardian approval';
$txt['admin_setting_coppaPost'] = 'Postal address to which approval forms should be sent';
$txt['admin_setting_coppaPost_desc'] = 'Only applies if age restriction is in place';
$txt['admin_setting_coppaFax'] = 'Fax number to which approval forms should be faxed';
$txt['admin_setting_coppaPhone'] = 'Contact number for parents to contact with age restriction queries';

$txt['admin_register'] = 'Registration of new member';
$txt['admin_register_desc'] = 'From here you can register new members into the forum, and if desired, email them their details.';
$txt['admin_register_username'] = 'New Username';
$txt['admin_register_email'] = 'Email Address';
$txt['admin_register_password'] = 'Password';
$txt['admin_register_username_desc'] = 'Username for the new member';
$txt['admin_register_email_desc'] = 'Email address of the member';
$txt['admin_register_password_desc'] = 'Password for new member';
$txt['admin_register_email_detail'] = 'Email new password to user';
$txt['admin_register_email_detail_desc'] = 'Email address required even if unchecked';
$txt['admin_register_email_activate'] = 'Require user to activate the account';
$txt['admin_register_group'] = 'Primary Membergroup';
$txt['admin_register_group_desc'] = 'Primary membergroup new member will belong to';
$txt['admin_register_group_none'] = '(no primary membergroup)';
$txt['admin_register_done'] = 'Member %s has been registered successfully!';

$txt['admin_browse_register_new'] = 'Register new member';

// Use numeric entities in the below three strings.
$txt['admin_notify_subject'] = 'A new member has joined';
$txt['admin_notify_profile'] = '%s has just signed up as a new member of your forum. Click the link below to view their profile.';
$txt['admin_notify_approval'] = 'Before this member can begin posting they must first have their account approved. Click the link below to go to the approval screen.';

$txt['coppa_title'] = 'Age Restricted Forum';
$txt['coppa_after_registration'] = 'Thank you for registering with ' . $context['forum_name'] . '.<br /><br />Because you fall under the age of {MINIMUM_AGE}, it is a legal requirement
	to obtain your parent or guardian\'s permission before you may begin to use your account.  To arrange for account activation please print off the form below:';
$txt['coppa_form_link_popup'] = 'Load Form In New Window';
$txt['coppa_form_link_download'] = 'Download Form as Text File';
$txt['coppa_send_to_one_option'] = 'Then arrange for your parent/guardian to send the completed form by:';
$txt['coppa_send_to_two_options'] = 'Then arrange for your parent/guardian to send the completed form by either:';
$txt['coppa_send_by_post'] = 'Post, to the following address:';
$txt['coppa_send_by_fax'] = 'Fax, to the following number:';
$txt['coppa_send_by_phone'] = 'Alternatively, arrange for them to phone the administrator at {PHONE_NUMBER}.';

$txt['coppa_form_title'] = 'Permission form for registration at ' . $context['forum_name'];
$txt['coppa_form_address'] = 'Address';
$txt['coppa_form_date'] = 'Date';
$txt['coppa_form_body'] = 'I {PARENT_NAME},<br /><br />Give permission for {CHILD_NAME} (child name) to become a fully registered member of the forum: ' . $context['forum_name'] . ', with the username: {USER_NAME}.<br /><br />I understand that certain personal information entered by {USER_NAME} may be shown to other users of the forum.<br /><br />Signed:<br />{PARENT_NAME} (Parent/Guardian).';

$txt['visual_verification_label'] = 'Visual verification';
$txt['visual_verification_description'] = 'Type the letters shown in the picture';
$txt['visual_verification_sound'] = 'Listen to the letters';
$txt['visual_verification_sound_again'] = 'Play again';
$txt['visual_verification_sound_close'] = 'Close window';
$txt['visual_verification_request_new'] = 'Request another image';
$txt['visual_verification_sound_direct'] = 'Having problems hearing this?  Try a direct link to it.';

?>