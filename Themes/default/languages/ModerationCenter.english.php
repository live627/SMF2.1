<?php
// Version: 2.0 Beta 3; ModerationCenter

// Important! Before editing these language files please read the text at the top of index.english.php.

global $scripturl;

$txt['moderation_center'] = 'Moderation Center';
$txt['mc_main'] = 'Main';
$txt['mc_posts'] = 'Posts';
$txt['mc_groups'] = 'Groups';

$txt['mc_view_groups'] = 'View Groups';

$txt['mc_description'] = 'This is your &quot;Moderation Center&quot;. From here you can perform all the moderation actions assigned to yourself by the Administrator. This home page contains a summary of all the latest happenings in your community. You can personalize the layout by clicking <a href="' . $scripturl . '?action=moderate;area=settings">here</a>.';
$txt['mc_group_requests'] = 'Group Requests';
$txt['mc_unapproved_posts'] = 'Unapproved Posts';
$txt['mc_watched_users'] = 'Recent Watched Users';
$txt['mc_watched_topics'] = 'Watched Topics';
$txt['mc_scratch_board'] = 'Moderator Scratch Board';
$txt['mc_latest_news'] = 'Simple Machines Latest News';
$txt['mc_recent_reports'] = 'Recent Topic Reports';
$txt['mc_warning_log'] = 'Warning Log';
$txt['mc_notes'] = 'Moderator Notes';

$txt['mc_cannot_connect_sm'] = 'You are unable to connect to simplemachines.org\'s latest news file.';

$txt['mc_recent_reports_none'] = 'There are no outstanding reports';
$txt['mc_watched_users_none'] = 'There are not currently any watches in place.';
$txt['mc_group_requests_none'] = 'There are no open requests for group membership.';

$txt['mc_seen'] = 'last seen';
$txt['mc_groupr_by'] = 'by';

$txt['mc_reported_posts_desc'] = 'Here you can review all the post reports raised by members of the community.';
$txt['mc_reportedp_active'] = 'Active Reports';
$txt['mc_reportedp_closed'] = 'Old Reports';
$txt['mc_reportedp_by'] = 'by';
$txt['mc_reportedp_reported_by'] = 'Reported By';
$txt['mc_reportedp_last_reported'] = 'Last Reported';
$txt['mc_reportedp_none_found'] = 'No Reports Found';

$txt['mc_reportedp_details'] = 'Details';
$txt['mc_reportedp_close'] = 'Close';
$txt['mc_reportedp_open'] = 'Open';
$txt['mc_reportedp_ignore'] = 'Ignore';
$txt['mc_reportedp_unignore'] = 'Un-Ignore';
// Do not use numeric entries in the below string.
$txt['mc_reportedp_ignore_confirm'] = 'Are you sure you wish to ignore further reports about this message?\\n\\nThis will turn off further reports for all moderators of the forum.';
$txt['mc_reportedp_close_selected'] = 'Close Selected';

$txt['mc_groupr_group'] = 'Group';
$txt['mc_groupr_member'] = 'Member';
$txt['mc_groupr_reason'] = 'Reason';
$txt['mc_groupr_none_found'] = 'There are currently no outstanding group requests';
$txt['mc_groupr_submit'] = 'Submit';
$txt['mc_groupr_reason_desc'] = 'Reason to reject %s\'s request to join &quot;%s&quot;';
$txt['mc_groups_reason_title'] = 'Reasons for Rejection';
$txt['with_selected'] = 'With Selected';
$txt['mc_groupr_approve'] = 'Approve Request';
$txt['mc_groupr_reject'] = 'Reject Request (No Reason)';
$txt['mc_groupr_reject_w_reason'] = 'Reject Request with Reason';
// Do not use numeric entries in the below string.
$txt['mc_groupr_warning'] = 'Are you sure you wish to do this?';

$txt['mc_unapproved_attachments_none_found'] = 'There are currently no attachments awaiting approval';
$txt['mc_unapproved_replies_none_found'] = 'There are currently no posts awaiting approval';
$txt['mc_unapproved_topics_none_found'] = 'There are currently no topics awaiting approval';
$txt['mc_unapproved_posts_desc'] = 'From here you can approve or delete any posts awaiting moderation.';
$txt['mc_unapproved_replies'] = 'Replies';
$txt['mc_unapproved_topics'] = 'Topics';
$txt['mc_unapproved_by'] = 'by';
$txt['mc_unapproved_sure'] = 'Are you sure you want to do this?';
$txt['mc_unapproved_attach_name'] = 'Attachment Name';
$txt['mc_unapproved_attach_size'] = 'Filesize';
$txt['mc_unapproved_attach_poster'] = 'Poster';
$txt['mc_viewmodreport'] = 'Moderation Report for %1$s by %2$s';
$txt['mc_modreport_summary'] = 'There have been %1$d report(s) concerning this post.  The last report was %2$s.';
$txt['mc_modreport_whoreported_title'] = 'Members who have reported this post';
$txt['mc_modreport_whoreported_data'] = 'Reported by %1$s on %2$s.  They left the following message:';
$txt['mc_modreport_modactions'] = 'Actions taken by other moderators';
$txt['mc_modreport_mod_comments'] = 'Moderator Comments';
$txt['mc_modreport_no_mod_comment'] = 'There are not currently any moderator comments';
$txt['mc_modreport_add_mod_comment'] = 'Add Comment';

$txt['show_notice'] = 'Notice Text';
$txt['show_notice_subject'] = 'Subject';
$txt['show_notice_text'] = 'Text';

$txt['mc_watched_users_title'] = 'Watched Users';
$txt['mc_watched_users_desc'] = 'Here you can keep a track of all users who have been assigned a &quot;watch&quot; by the moderation team.';
$txt['mc_watched_users_member'] = 'View by Member';
$txt['mc_watched_users_post'] = 'View by Post';
$txt['mc_watched_users_warning'] = 'Warning Level';
$txt['mc_watched_users_last_login'] = 'Last Login';
$txt['mc_watched_users_last_post'] = 'Last Post';
$txt['mc_watched_users_no_posts'] = 'There are no posts from watched users.';
// Don't use entities in the two strings below.
$txt['mc_watched_users_delete_post'] = 'Are you sure you want to delete this post?';
$txt['mc_watched_users_delete_posts'] = 'Are you sure you want to delete these posts?';
$txt['mc_watched_users_posted'] = 'Posted';

$txt['mc_warnings_none'] = 'No warnings have been issued yet!';
$txt['mc_warnings_recipient'] = 'Recipient';

$txt['mc_prefs'] = 'Preferences';
$txt['mc_settings'] = 'Change Settings';
$txt['mc_prefs_title'] = 'Moderation Preferences';
$txt['mc_prefs_desc'] = 'This section allows you to set some personal preferences for moderation related activities such as email notifications.';
$txt['mc_prefs_homepage'] = 'Items to show on moderation homepage';
$txt['mc_prefs_homepage_desc'] = 'Use shift and ctrl to select more than one item.';
$txt['mc_prefs_latest_news'] = 'SM News';
$txt['mc_prefs_show_reports'] = 'Show open report count in forum header';
$txt['mc_prefs_notify_report'] = 'Notify of topic reports';
$txt['mc_prefs_notify_report_never'] = 'Never';
$txt['mc_prefs_notify_report_moderator'] = 'Only if it\'s a board I moderate';
$txt['mc_prefs_notify_report_always'] = 'Always';
$txt['mc_prefs_notify_approval'] = 'Notify of items awaiting approval';

// Use entities in the below string.
$txt['mc_click_add_note'] = 'Add a new note';
$txt['mc_add_note'] = 'Add';

?>