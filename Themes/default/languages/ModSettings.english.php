<?php
// Version: 2.0 Alpha; ModSettings

// Important! Before editing these language files please read the text at the topic of index.english.php.

$txt['modSettings_desc'] = 'This page allows you to change the settings of features, mods, and basic options in your forum.  Please see the <a href="' . $scripturl . '?action=theme;sa=settings;th=%s;sesc=%s">theme settings</a> for more options.  Click the help icons for more information about a setting.';

$txt['mods_cat_features'] = 'Basic Features';
$txt['pollMode'] = 'Poll mode';
$txt['disable_polls'] = 'Disable polls';
$txt['enable_polls'] = 'Enable polls';
$txt['polls_as_topics'] = 'Show existing polls as topics';
$txt['allow_guestAccess'] = 'Allow guests to browse the forum';
$txt['userLanguage'] = 'Enable user-selectable language support';
$txt['allow_editDisplayName'] = 'Allow users to edit their displayed name?';
$txt['allow_hideOnline'] = 'Allow non-administrators to hide their online status?';
$txt['allow_hide_email'] = 'Allow users to hide their email from everyone except admins?';
$txt['guest_hideContacts'] = 'Do not reveal contact details of members to guests';
$txt['titlesEnable'] = 'Enable custom titles';
$txt['enable_buddylist'] = 'Enable buddy lists';
$txt['default_personal_text'] = 'Default personal text';
$txt['number_format'] = 'Default number format';
$txt['time_format'] = 'Default time format';
$txt['setting_time_offset'] = 'Overall time offset<div class="smalltext">(added to the member specific option.)</div>';
$txt['failed_login_threshold'] = 'Failed login threshold';
$txt['lastActive'] = 'User online time threshold';
$txt['trackStats'] = 'Track daily statistics';
$txt['hitStats'] = 'Track daily page views (must have stats enabled)';
$txt['enableCompressedOutput'] = 'Enable compressed output';
$txt['databaseSession_enable'] = 'Use database driven sessions';
$txt['databaseSession_loose'] = 'Allow browsers to go back to cached pages';
$txt['databaseSession_lifetime'] = 'Seconds before an unused session timeout';
$txt['enableErrorLogging'] = 'Enable error logging';
$txt['cookieTime'] = 'Default login cookies length (in minutes)';
$txt['localCookies'] = 'Enable local storage of cookies<div class="smalltext">(SSI won\'t work well with this on.)</div>';
$txt['globalCookies'] = 'Use subdomain independent cookies<div class="smalltext">(turn off local cookies first!)</div>';
$txt['securityDisable'] = 'Disable administration security';
$txt['send_validation_onChange'] = 'Require reactivation after email change';
$txt['approveAccountDeletion'] = 'Require admin approval when member deletes account';
$txt['autoOptMaxOnline'] = 'Maximum users online when optimizing<div class="smalltext">(0 for no max.)</div>';
$txt['autoFixDatabase'] = 'Automatically fix broken tables';
$txt['allow_disableAnnounce'] = 'Allow users to disable announcements';
$txt['disallow_sendBody'] = 'Don\'t allow post text in notifications?';
$txt['modlog_enabled'] = 'Log moderation actions';
$txt['queryless_urls'] = 'Search engine friendly URLs<div class="smalltext"><b>Apache only!</b></div>';
$txt['max_image_width'] = 'Max width of posted pictures (0 = disable)';
$txt['max_image_height'] = 'Max height of posted pictures (0 = disable)';
$txt['enableReportPM'] = 'Enable reporting of personal messages';
$txt['max_pm_recipients'] = 'Maximum number of recipients allowed in a personal message.<div class="smalltext">(0 for no limit, admins are exempt)</div>';
$txt['pm_posts_verification'] = 'Post count under which users must enter code when sending personal messages.<div class="smalltext">(0 for no limit, admins are exempt)</div>';
$txt['pm_posts_per_hour'] = 'Number of personal messages a user may send in an hour.<div class="smalltext">(0 for no limit, moderators are exempt)</div>';
$txt['showsidebarAdmin'] = 'Use a sidebar instead of dropdown-menu for admin section';
$txt['mods_cat_layout'] = 'Layout and Options';
$txt['compactTopicPagesEnable'] = 'Limit number of displayed page links';
$txt['contiguous_page_display'] = 'Contiguous pages to display:';
$txt['to_display'] = 'to display';
$txt['todayMod'] = 'Enable &quot;Today&quot; feature';
$txt['today_disabled'] = 'Disabled';
$txt['today_only'] = 'Only Today';
$txt['yesterday_today'] = 'Today &amp; Yesterday';
$txt['topbottomEnable'] = 'Enable Go Up/Go Down buttons';
$txt['onlineEnable'] = 'Show online/offline in posts and PMs';
$txt['enableVBStyleLogin'] = 'Show a quick login on every page';
$txt['defaultMaxMembers'] = 'Members per page in member list';
$txt['timeLoadPageEnable'] = 'Display time taken to create every page';
$txt['disableHostnameLookup'] = 'Disable hostname lookups?';
$txt['who_enabled'] = 'Enable who\'s online list';

$txt['karma'] = 'Karma';
$txt['karmaMode'] = 'Karma mode';
$txt['karma_options'] = 'Disable karma|Enable karma total|Enable karma positive/negative';
$txt['karmaMinPosts'] = 'Set the minimum posts needed to modify karma';
$txt['karmaWaitTime'] = 'Set wait time in hours';
$txt['karmaTimeRestrictAdmins'] = 'Restrict administrators to wait time';
$txt['karmaLabel'] = 'Karma label';
$txt['karmaApplaudLabel'] = 'Karma applaud label';
$txt['karmaSmiteLabel'] = 'Karma smite label';

$txt['caching_information'] = '<div align="center"><b><u>Important! Read this first before enabling these features.</b></u></div><br />
	SMF supports caching through the use of accelerators. The currently supported accelerators include:<br />
	<ul>
		<li>APC</li>
		<li>eAccelerator</li>
		<li>Turck MMCache</li>
		<li>Memcached</li>
		<li>Zend Platform/Performance Suite (Not Zend Optimizer)</li>
	</ul>
	Caching will work best of you have PHP compiled with one of the above optimizers, or have memcache
	available. If you do not have any optimizer installed SMF will do file based caching.<br /><br />
	SMF performs caching at a variety of levels. The higher the level of caching enabled the more CPU time will be spent
	retrieving cached information. If caching is available on your machine it is recommended that you try caching at level 1 first.
	<br /><br />
	Note that if you use memcached you need to provide the server details in the setting below. This should be entered as a comma separated list
	as shown in the example below:<br />
	&quot;server1,server2,server3:port,server4&quot;<br /><br />
	Note that if no port is specified SMF will use port 11211. SMF will attempt to perform rough/random load balancing across the servers.
	<br /><br />
	%s
	<hr />';

$txt['detected_no_caching'] = '<b style="color: red;">SMF has not been able to detect a compatible accelerator on your server.</b>';
$txt['detected_APC'] = '<b style="color: green">SMF has detected that your server has APC installed.';
$txt['detected_eAccelerator'] = '<b style="color: green">SMF has detected that your server has eAccelerator installed.';
$txt['detected_MMCache'] = '<b style="color: green">SMF has detected that your server has MMCache installed.';
$txt['detected_Zend'] = '<b style="color: green">SMF has detected that your server has Zend installed.';

$txt['cache_enable'] = 'Caching Level';
$txt['cache_off'] = 'No caching';
$txt['cache_level1'] = 'Level 1 Caching';
$txt['cache_level2'] = 'Level 2 Caching (Not Recommended)';
$txt['cache_level3'] = 'Level 3 Caching (Not Recommended)';
$txt['cache_memcached'] = 'Memcache settings';

$txt['moderation_settings_short'] = 'Moderation';
$txt['moderation_settings'] = 'Moderation Settings';
$txt['setting_warning_enable'] = 'Enable User Warning System';
$txt['setting_warning_watch'] = 'Warning level for user watch<div class="smalltext">The user warning level after which a user watch is put in place - 0 to disable.</div>';
$txt['setting_warning_moderate'] = 'Warning level for post moderation<div class="smalltext">The user warning level after which a user has all posts moderated - 0 to disable.</div>';
$txt['setting_warning_mute'] = 'Warning level for user muting<div class="smalltext">The user warning level after which a user cannot post any further - 0 to disable.</div>';
$txt['setting_user_limit'] = 'Maximum user warning points per day<div class="smalltext">This value is the maximum amount of warning points a single moderator can assign to a user in a 24 hour period - 0 for no limit.</div>';

$txt['signature_settings_short'] = 'Signatures';
$txt['signature_settings'] = 'Signature Settings';
$txt['signature_settings_desc'] = 'Use the settings on this page to decide how member signatures should be treated in SMF.';
$txt['signature_settings_warning'] = 'Note that settings are not applied to existing signatures by default. Click <a href="' . $scripturl . '?action=admin;area=featuresettings;sa=sig;apply;sesc=%1$s">here</a> to apply rules to all existing signatures.';
$txt['signature_enable'] = 'Enable signatures';
$txt['signature_max_length'] = 'Maximum allowed characters<div class="smalltext">(0 for no max.)</div>';
$txt['signature_max_lines'] = 'Maximum amount of lines<div class="smalltext">(0 for no max)</div>';
$txt['signature_allowed_bbc'] = 'Allowed Bulletin Board Codes';
$txt['signature_max_images'] = 'Maximum image count<div class="smalltext">(0 for no max - excludes smileys)</div>';
$txt['signature_max_smileys'] = 'Maximum smiley count<div class="smalltext">(0 for no max)</div>';
$txt['signature_max_image_width'] = 'Maximum width of signature images (pixels)<div class="smalltext">(0 for no max)</div>';
$txt['signature_max_image_height'] = 'Maximum height of signature images (pixels)<div class="smalltext">(0 for no max)</div>';
$txt['signature_max_font_size'] = 'Maximum font size allowed in signatures<div class="smalltext">(0 for no max)</div>';
$txt['signature_bbc'] = 'Enabled BBC tags';

$txt['custom_profile_shorttitle'] = 'Profile Fields';
$txt['custom_profile_title'] = 'Custom Profile Fields';
$txt['custom_profile_desc'] = 'From this page you can create your own custom profile fields that fit in with your own forums requirements';
$txt['custom_profile_active'] = 'Active';
$txt['custom_profile_fieldname'] = 'Field Name';
$txt['custom_profile_fieldtype'] = 'Field Type';
$txt['custom_profile_make_new'] = 'New Field';
$txt['custom_profile_none'] = 'You have not created any custom profile fields yet!';

$txt['custom_profile_type_text'] = 'Text';
$txt['custom_profile_type_textarea'] = 'Large Text';
$txt['custom_profile_type_select'] = 'Select Box';
$txt['custom_profile_type_radio'] = 'Radio Button';
$txt['custom_profile_type_check'] = 'Checkbox';

$txt['custom_add_title'] = 'Add Profile Field';
$txt['custom_edit_title'] = 'Edit Profile Field';
$txt['custom_edit_general'] = 'Display Settings';
$txt['custom_edit_input'] = 'Input Settings';
$txt['custom_edit_advanced'] = 'Advanced Settings';
$txt['custom_edit_name'] = 'Name';
$txt['custom_edit_desc']  ='Description';
$txt['custom_edit_profile'] = 'Profile Section';
$txt['custom_edit_profile_desc'] = 'Section of profile this is edited in.';
$txt['custom_edit_profile_none'] = 'None';
$txt['custom_edit_registration'] = 'Show on Registration';
$txt['custom_edit_display'] = 'Show on Topic View';
$txt['custom_edit_picktype'] = 'Field Type';

$txt['custom_edit_max_length'] = 'Maximum Length';
$txt['custom_edit_max_length_desc'] = '(0 for no limit)';
$txt['custom_edit_dimension'] = 'Dimensions';
$txt['custom_edit_dimension_row'] = 'Rows';
$txt['custom_edit_dimension_col'] = 'Columns';
$txt['custom_edit_bbc'] = 'Allow BBC?';
$txt['custom_edit_options'] = 'Options';
$txt['custom_edit_options_desc'] = 'Leave option box blank to remove. Radio button selects default option.';
$txt['custom_edit_options_more'] = 'More';
$txt['custom_edit_default'] = 'Default State';
$txt['custom_edit_active'] = 'Active';
$txt['custom_edit_active_desc'] = 'If not selected this field will not be shown to anyone.';
$txt['custom_edit_private'] = 'Private';
$txt['custom_edit_private_desc'] = 'If selected only admins can see this field.';
$txt['custom_edit_mask'] = 'Input Mask';
$txt['custom_edit_mask_desc'] = 'For text fields an input mask can be selected to validate the data.';
$txt['custom_edit_mask_none'] = 'None';
$txt['custom_edit_mask_email'] = 'Valid Email';
$txt['custom_edit_mask_number'] = 'Numeric';
$txt['custom_edit_mask_regex'] = 'Regex (Advanced)';

// Use numeric entities in the string below!
$txt['custom_edit_delete_sure'] = 'Are you sure you wish to delete this field - all related user data will be lost!';

?>