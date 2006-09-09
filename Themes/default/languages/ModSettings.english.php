<?php
// Version: 2.0 Alpha; ModSettings

// Important! Before editing these language files please read the text at the topic of index.english.php.

$txt['smf3'] = 'This page allows you to change the settings of features, mods, and basic options in your forum.  Please see the <a href="{$scripturl}?action=theme;sa=settings;th=%s;sesc=%s">theme settings</a> for more options.  Click the help icons for more information about a setting.';

$txt['mods_cat_features'] = 'Basic Features';
$txt['pollMode'] = 'Poll mode';
$txt['smf34'] = 'Disable polls';
$txt['smf32'] = 'Enable polls';
$txt['smf33'] = 'Show existing polls as topics';
$txt['allow_guestAccess'] = 'Allow guests to browse the forum';
$txt['userLanguage'] = 'Enable user-selectable language support';
$txt['allow_editDisplayName'] = 'Allow users to edit their displayed name?';
$txt['allow_hideOnline'] = 'Allow non-administrators to hide their online status?';
$txt['allow_hideEmail'] = 'Allow users to hide their email from everyone except admins?';
$txt['guest_hideContacts'] = 'Do not reveal contact details of members to guests';
$txt['titlesEnable'] = 'Enable custom titles';
$txt['enable_buddylist'] = 'Enable buddy lists';
$txt['default_personalText'] = 'Default personal text';
$txt['number_format'] = 'Default number format';
$txt['time_format'] = 'Default time format';
$txt['time_offset'] = 'Overall time offset<div class="smalltext">(added to the member specific option.)</div>';
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
$txt['pm_posts_per_hour'] = 'Number of personal messages a user may send in an hour.<div class="smalltext">(0 for no limit, moderators are excempt)</div>';

$txt['mods_cat_layout'] = 'Layout and Options';
$txt['compactTopicPagesEnable'] = 'Limit number of displayed page links';
$txt['smf235'] = 'Contiguous pages to display:';
$txt['smf236'] = 'to display';
$txt['todayMod'] = 'Enable &quot;Today&quot; feature';
$txt['smf290'] = 'Disabled';
$txt['smf291'] = 'Only Today';
$txt['smf292'] = 'Today &amp; Yesterday';
$txt['topbottomEnable'] = 'Enable Go Up/Go Down buttons';
$txt['onlineEnable'] = 'Show online/offline in posts and PMs';
$txt['enableVBStyleLogin'] = 'Show a quick login on every page';
$txt['defaultMaxMembers'] = 'Members per page in member list';
$txt['timeLoadPageEnable'] = 'Display time taken to create every page';
$txt['disableHostnameLookup'] = 'Disable hostname lookups?';
$txt['who_enabled'] = 'Enable who\'s online list';

$txt['smf293'] = 'Karma';
$txt['karmaMode'] = 'Karma mode';
$txt['smf64'] = 'Disable karma|Enable karma total|Enable karma positive/negative';
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
	Caching will only work on your server if you have PHP compiled with one of the above optimizers, or have memcache
	available. <br /><br />
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

$txt['signature_settings'] = 'Signature Settings';
$txt['signature_settings_desc'] = 'Use the settings on this page to decide how member signatures should be treated in SMF.';
$txt['signature_settings_warning'] = 'Note that settings are not applied to existing signatures by default. Click <a href="{$scripturl}?action=admin;area=featuresettings;sa=sig;apply">here</a> to apply rules to all existing signatures.';
$txt['signature_enable'] = 'Enable signatures';
$txt['signature_max_length'] = 'Maximum allowed characters<div class="smalltext">(0 for no max.)</div>';
$txt['signature_max_lines'] = 'Maximum amount of lines<div class="smalltext">(0 for no max)</div>';
$txt['signature_allowed_bbc'] = 'Allowed Bulletin Board Codes';
$txt['signature_max_images'] = 'Maximum image count<div class="smalltext">(0 for no max - excludes smileys)</div>';
$txt['signature_max_smileys'] = 'Maximum smiley count<div class="smalltext">(0 for no max)</div>';
$txt['signature_max_image_width'] = 'Maximum width of signature images (pixels)<div class="smalltext">(0 for no max)</div>';
$txt['signature_max_image_height'] = 'Maximum height of signature images (pixels)<div class="smalltext">(0 for no max)</div>';
$txt['signature_max_font_size'] = 'Maximum font size allowed in signatures<div class="smalltext">(0 for no max)</div>';

?>