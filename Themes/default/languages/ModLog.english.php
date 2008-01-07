<?php
// Version: 2.0 Beta 2; ModLog

// Important! Before editing these language files please read the text at the topic of index.english.php.

global $scripturl;

$txt['modlog_date'] = 'Date';
$txt['modlog_member'] = 'Member';
$txt['modlog_position'] = 'Position';
$txt['modlog_action'] = 'Action';
$txt['modlog_ip'] = 'IP';
$txt['modlog_search_result'] = 'Search Results';
$txt['modlog_total_entries'] = 'Total Entries';
$txt['modlog_ac_approve_topic'] = 'Approved topic {%topic%} by {%member%}';
$txt['modlog_ac_approve'] = 'Approved message {%subject%} in {%topic%} by {%member%}';
$txt['modlog_ac_lock'] = 'Locked {%topic%}';
$txt['modlog_ac_sticky'] = 'Stickied {%topic%}';
$txt['modlog_ac_delete'] = 'Deleted {%subject%} by {%member%} from {%topic%}';
$txt['modlog_ac_delete_member'] = 'Deleted member {%name%}';
$txt['modlog_ac_remove'] = 'Removed topic {%topic%} from {%board%}';
$txt['modlog_ac_modify'] = 'Edited {%message%} by {%member%}';
$txt['modlog_ac_merge'] = 'Merged topics to create {%topic%}';
$txt['modlog_ac_split'] = 'Split {%topic%} to create {%new_topic%}';
$txt['modlog_ac_move'] = 'Moved {%topic%} from {%board_from%} to {%board_to%}';
$txt['modlog_ac_profile'] = 'Edit the profile of {%member%}';
$txt['modlog_ac_pruned'] = 'Pruned some posts older than {%days%} days';
$txt['modlog_ac_news'] = 'Edited the news';
$txt['modlog_enter_comment'] = 'Enter Moderation Comment';
$txt['modlog_moderation_log'] = 'Moderation Log';
$txt['modlog_moderation_log_desc'] = 'Below is a list of all the moderation actions that have been carried out by moderators of the forum.<br /><b>Please note:</b> Entries cannot be removed from this log until they are at least twenty-four hours old.';
$txt['modlog_no_entries_found'] = 'No entries found';
$txt['modlog_remove'] = 'Remove';
$txt['modlog_removeall'] = 'Remove All';
$txt['modlog_go'] = 'Go';
$txt['modlog_add'] = 'Add';
$txt['modlog_search'] = 'Quick Search';
$txt['modlog_by'] = 'By';
$txt['modlog_id'] = '<em>(ID:%1$d)</em>';

$txt['modlog_ac_ban'] = 'Added ban triggers:';
$txt['modlog_ac_ban_trigger_member'] = ' <em>Member:</em> {%member%}';
$txt['modlog_ac_ban_trigger_email'] = ' <em>Email:</em> {%email%}';
$txt['modlog_ac_ban_trigger_ip_range'] = ' <em>IP:</em> {%ip_range%}';
$txt['modlog_ac_ban_trigger_hostname'] = ' <em>Hostname:</em> {%hostname%}';

$txt['modlog_admin_log'] = 'Admin Log';
$txt['modlog_admin_log_desc'] = 'Below is a list of administration actions which have been logged on your forum.<br /><b>Please note:</b> Entries cannot be removed from this log until they are at least twenty-four hours old.<br />General moderation actions can be found <a href="' . $scripturl . '?action=moderate;sa=modlog">here</a>';

// Admin type strings.
$txt['modlog_ac_upgrade'] = 'Upgraded the forum to version {%version%}';
$txt['modlog_ac_install'] = 'Installed version {%version%}';
$txt['modlog_ac_add_board'] = 'Added a new board: &quot;{%board%}&quot;';
$txt['modlog_ac_edit_board'] = 'Edited the &quot;{%board%}&quot; board';
$txt['modlog_ac_delete_board'] = 'Deleted the &quot;{%boardname%}&quot; board';
$txt['modlog_ac_add_cat'] = 'Added a new category, &quot;{%catname%}&quot;';
$txt['modlog_ac_edit_cat'] = 'Edited the &quot;{%catname%}&quot; category';
$txt['modlog_ac_delete_cat'] = 'Deleted the &quot;{%catname%}&quot; category';

?>