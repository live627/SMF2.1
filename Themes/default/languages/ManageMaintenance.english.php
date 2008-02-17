<?php
// Version: 2.0 Beta 2.1; ManageMaintenance

// Important! Before editing these language files please read the text at the top of index.english.php.

$txt['repair_zero_ids'] = 'Found topics and/or messages with topic or message IDs of 0.';
$txt['repair_missing_topics'] = 'Message #%d is in non-existent topic #%d.';
$txt['repair_missing_messages'] = 'Topic #%d contains no (actual) messages.';
$txt['repair_stats_topics_1'] = 'Topic #%d has the first message ID %d, which is incorrect.';
$txt['repair_stats_topics_2'] = 'Topic #%d has the last message ID %d, which is incorrect.';
$txt['repair_stats_topics_3'] = 'Topic #%d has the wrong number of replies, %d.';
$txt['repair_stats_topics_4'] = 'Topic #%d has the wrong number of unapproved posts, %d.';
$txt['repair_stats_topics_5'] = 'Topic #%d has the wrong approval flag set.';
$txt['repair_missing_boards'] = 'Topic #%d is in board #%d, which is missing.';
$txt['repair_missing_categories'] = 'Board #%d is in category #%d, which is missing.';
$txt['repair_missing_posters'] = 'Message #%d was posted by member #%d, who is now missing.';
$txt['repair_missing_parents'] = 'Board #%d is a child of board #%d, which is missing.';
$txt['repair_missing_polls'] = 'Topic #%d is tied to non-existent poll #%d.';
$txt['repair_missing_calendar_topics'] = 'Event #%d is tied to topic #%d, which is missing.';
$txt['repair_missing_log_topics'] = 'Topic #%d is marked as read for one or more people, but does not exist.';
$txt['repair_missing_log_topics_members'] = 'Member #%d has marked one or more topics as read, but does not exist.';
$txt['repair_missing_log_boards'] = 'Board #%d is marked as read for one or more people, but does not exist.';
$txt['repair_missing_log_boards_members'] = 'Member #%d has marked one or more boards as read, but does not exist.';
$txt['repair_missing_log_mark_read'] = 'Board #%d is marked as read for one or more people, but does not exist.';
$txt['repair_missing_log_mark_read_members'] = 'Member #%d has marked one or more boards as read, but does not exist.';
$txt['repair_missing_pms'] = 'Personal message #%d has been sent to one or more people, but does not exist.';
$txt['repair_missing_recipients'] = 'Member #%d has received one or more personal messages, but does not exist.';
$txt['repair_missing_senders'] = 'Personal message #%d was sent by member #%d, who does not exist.';
$txt['repair_missing_notify_members'] = 'Notifications have been requested by member #%d, who does not exist.';
$txt['repair_missing_cached_subject'] = 'The subject of topic #%d is currently not stored in the subject cache.';
$txt['repair_missing_topic_for_cache'] = 'Cached word \'%s\' is linked to a non-existent topic.';
$txt['repair_missing_log_poll_member'] = 'Poll #%1$d has been given a vote from member #%2$d , who is now missing.';
$txt['repair_missing_log_poll_vote'] = 'A vote was cast by member #%1$d on a non-existent poll #%2$d.';
$txt['repair_missing_thumbnail_parent'] = 'A thumbnail exists called %s, but it doesn\'t have a parent.';
$txt['repair_report_missing_comments'] = 'Report #%d of topic: &quot;%s&quot; has no comments.';
$txt['repair_comments_missing_report'] = 'Report comment #%d submitted by %s has no related report.';
$txt['repair_group_request_missing_member'] = 'A group request still exists for deleted member #%d.';
$txt['repair_group_request_missing_group'] = 'A group request still exists for deleted group #%d.';

$txt['repair_currently_checking'] = 'Checking: &quot;%1$s&quot;';
$txt['repair_currently_fixing'] = 'Fixing: &quot;%1$s&quot;';
$txt['repair_operation_zero_topics'] = 'Topics with id_topic incorrectly set to zero';
$txt['repair_operation_zero_messages'] = 'Messages with id_msg incorrectly set to zero';
$txt['repair_operation_missing_topics'] = 'Messages missing topic entries';
$txt['repair_operation_missing_messages'] = 'Topics without any messages';
$txt['repair_operation_stats_topics'] = 'Topics with incorrect first or last message entries';
$txt['repair_operation_stats_topics2'] = 'Topics with the wrong number of replies';
$txt['repair_operation_stats_topics3'] = 'Topics with the wrong unapproved post count';
$txt['repair_operation_missing_boards'] = 'Topics in a non-existant board';
$txt['repair_operation_missing_categories'] = 'Boards in a non-existant category';
$txt['repair_operation_missing_posters'] = 'Messages linked to non-existant members';
$txt['repair_operation_missing_parents'] = 'Child boards with non-existant parents';
$txt['repair_operation_missing_polls'] = 'Topics linked to non-existant polls';
$txt['repair_operation_missing_calendar_topics'] = 'Events linked to non-existant topics';
$txt['repair_operation_missing_log_topics'] = 'Topic logs linked to non-existant topics';
$txt['repair_operation_missing_log_topics_members'] = 'Topic logs linked to non-existant members';
$txt['repair_operation_missing_log_boards'] = 'Board logs linked to non-existant boards';
$txt['repair_operation_missing_log_boards_members'] = 'Board logs linked to non-existant members';
$txt['repair_operation_missing_log_mark_read'] = 'Mark read data linked to non-existant boards';
$txt['repair_operation_missing_log_mark_read_members'] = 'Mark read data linked to non-existant members';
$txt['repair_operation_missing_pms'] = 'PM recipients missing the master personal message';
$txt['repair_operation_missing_recipients'] = 'PM recipients linked to a non-existant member';
$txt['repair_operation_missing_senders'] = 'Personal messages linked to a non-existant member';
$txt['repair_operation_missing_notify_members'] = 'Notification logs linked to a non-existant member';
$txt['repair_operation_missing_cached_subject'] = 'Topics missing their search cache entries';
$txt['repair_operation_missing_topic_for_cache'] = 'Search cache entries linked to non-existant topic';
$txt['repair_operation_missing_member_vote'] = 'Poll votes linked to non-existant members';
$txt['repair_operation_missing_log_poll_vote'] = 'Poll votes linked to non-existant poll';
$txt['repair_operation_report_missing_comments'] = 'Topic reports without a comment';
$txt['repair_operation_comments_missing_report'] = 'Report comments missing the topic report';
$txt['repair_operation_group_request_missing_member'] = 'Group requests missing the requesting member';
$txt['repair_operation_group_request_missing_group'] = 'Group requests for a non-existant group';

$txt['salvaged_category_name'] = 'Salvage Area';
$txt['salvaged_category_error'] = 'Unable to create Salvage Area category!';
$txt['salvaged_board_name'] = 'Salvaged Topics';
$txt['salvaged_board_description'] = 'Topics created for messages with non-existent topics';
$txt['salvaged_board_error'] = 'Unable to create Salvaged Topics board!';

$txt['database_optimize'] = 'Optimize Database';
$txt['database_numb_tables'] = 'Your database contains %d tables.';
$txt['database_optimize_attempt'] = 'Attempting to optimize your database...';
$txt['database_optimizing'] = 'Optimizing %1$s... %2$01.2f kb optimized.';
$txt['database_already_optimized'] = 'All of the tables were already optimized.';
$txt['database_opimize_unneeded'] = 'It wasn\'t necessary to optimize any tables.';
$txt['database_optimized'] = ' table(s) optimized.';
$txt['database_no_id'] = 'has a non-existent member ID';

$txt['apply_filter'] = 'Apply Filter';
$txt['applying_filter'] = 'Applying Filter';
$txt['filter_only_member'] = 'Only show the error messages of this member';
$txt['filter_only_ip'] = 'Only show the error messages of this IP address';
$txt['filter_only_session'] = 'Only show the error messages of this session';
$txt['filter_only_url'] = 'Only show the error messages of this URL';
$txt['filter_only_message'] = 'Only show the errors with the same message';
$txt['session'] = 'Session';
$txt['error_url'] = 'URL of page causing the error';
$txt['error_message'] = 'Error message';
$txt['clear_filter'] = 'Clear filter';
$txt['remove_selection'] = 'Remove Selection';
$txt['remove_filtered_results'] = 'Remove All Filtered Results';
$txt['sure_about_errorlog_remove'] = 'Are you sure you want to remove all error messages?';
$txt['reverse_direction'] = 'Reverse chronological order of list';
$txt['error_type'] = 'Type of error';
$txt['filter_only_type'] = 'Only show the errors of this type';
$txt['filter_only_file'] = 'Only show the errors from this file';
$txt['apply_filter_of_type'] = 'Apply filter of type';

$txt['errortype_all'] = 'All errors';
$txt['errortype_general'] = 'General';
$txt['errortype_general_desc'] = 'General errors that have not been categorized into another type';
$txt['errortype_critical'] = '<span style="color:red;">Critical</span>';
$txt['errortype_critical_desc'] = 'Critical errors.  These should be taken care of as quickly as possible.  Ignoring these errors can result in your forum failing and possibly security issues';
$txt['errortype_database'] = 'Database';
$txt['errortype_database_desc'] = 'Errors caused by faulty queries.  These should be looked at and reported to the SMF team.';
$txt['errortype_undefined_vars'] = 'Undefined';
$txt['errortype_undefined_vars_desc'] = 'Errors caused by the use of undefined variables, indexes, or offsets.';
$txt['errortype_template'] = 'Template';
$txt['errortype_template_desc'] = 'Errors related to the loading of templates.';
$txt['errortype_user'] = 'User';
$txt['errortype_user_desc'] = 'Errors resulting from user errors.  Includes failed passwords, trying to login when banned, and trying to do an action for which they do not have permission.';

$txt['maintain_general'] = 'General Maintenance';
$txt['maintain_recount'] = 'Recount all forum totals and statistics.';
$txt['maintain_errors'] = 'Find and repair any errors.';
$txt['maintain_logs'] = 'Empty out unimportant logs.';
$txt['maintain_cache'] = 'Empty the file cache.';
$txt['maintain_optimize'] = 'Optimize all tables to improve performance.';
$txt['maintain_version'] = 'Check all files against current versions.';
$txt['maintain_return'] = 'Back to Forum Maintenance';

$txt['maintain_backup'] = 'Backup Database';
$txt['maintain_backup_struct'] = 'Save the table structure.';
$txt['maintain_backup_data'] = 'Save the table data. (the important stuff.)';
$txt['maintain_backup_gz'] = 'Compress the file with gzip.';
$txt['maintain_backup_save'] = 'Download';

$txt['maintain_old'] = 'Remove Old Posts';
$txt['maintain_old_since_days1'] = 'Remove all topics not posted in for ';
$txt['maintain_old_since_days2'] = ' days, which are:';
$txt['maintain_old_nothing_else'] = 'Any sort of topic.';
$txt['maintain_old_are_moved'] = 'Moved topic notices.';
$txt['maintain_old_are_locked'] = 'Locked.';
$txt['maintain_old_are_not_stickied'] = 'But don\'t count stickied topics.';
$txt['maintain_old_all'] = 'All Boards';
$txt['maintain_old_choose'] = 'Choose Specific Boards';
$txt['maintain_old_remove'] = 'Remove now';
$txt['maintain_old_confirm'] = 'Are you really sure you want to delete old posts now?\\n\\nThis cannot be undone!';

$txt['maintain_members'] = 'Remove Inactive Members';
$txt['maintain_members_ungrouped'] = 'Ungrouped Members <span class="smalltext">(Members with no assigned groups)</span>';
$txt['maintain_members_since1'] = 'Remove all members who have not';
$txt['maintain_members_since2'] = 'for';
$txt['maintain_members_since3'] = 'days.';
$txt['maintain_members_activated'] = 'activated their account';
$txt['maintain_members_logged_in'] = 'logged in';
$txt['maintain_members_all'] = 'All Membergroups';
$txt['maintain_members_choose'] = 'Selected Groups';
$txt['maintain_members_confirm'] = 'Are you sure you really want to delete these member accounts?\\n\\nThis cannot be undone!';

$txt['scheduled_tasks_header'] = 'All Scheduled Tasks';
$txt['scheduled_tasks_name'] = 'Task Name';
$txt['scheduled_tasks_next_time'] = 'Next Due';
$txt['scheduled_tasks_regularity'] = 'Regularity';
$txt['scheduled_tasks_enabled'] = 'Enabled';
$txt['scheduled_tasks_run_now'] = 'Run Task Now';
$txt['scheduled_tasks_save_changes'] = 'Save Changes';
$txt['scheduled_tasks_time_offset'] = '<strong>Note:</strong> All times given below are <em>server time</em> and do not take any time offsets setup within SMF into account.';
$txt['scheduled_tasks_were_run'] = 'All selected tasks were completed';

$txt['scheduled_tasks_na'] = 'N/A';
$txt['scheduled_task_approval_notification'] = 'Approval Notifications';
$txt['scheduled_task_desc_approval_notification'] = 'Send out emails to all moderators summarizing posts awaiting approval.';
$txt['scheduled_task_auto_optimize'] = 'Optimize Database';
$txt['scheduled_task_desc_auto_optimize'] = 'Optimize the database to resolve fragmentation issues.';
$txt['scheduled_task_daily_maintenance'] = 'Daily Maintenance';
$txt['scheduled_task_desc_daily_maintenance'] = 'Runs essential daily maintenance on the forum - should not be disabled.';
$txt['scheduled_task_daily_digest'] = 'Daily Notification Summary';
$txt['scheduled_task_desc_daily_digest'] = 'Emails out the daily digest for notification subscribers.';
$txt['scheduled_task_weekly_digest'] = 'Weekly Notification Summary';
$txt['scheduled_task_desc_weekly_digest'] = 'Emails out the weekly digest for notification subscribers.';
$txt['scheduled_task_fetchSMfiles'] = 'Fetch Simple Machines Files';
$txt['scheduled_task_desc_fetchSMfiles'] = 'Retrieves javascript files containing notifications of updates and other information.';
$txt['scheduled_task_birthdayemails'] = 'Send Birthday Emails';
$txt['scheduled_task_desc_birthdayemails'] = 'Sends out emails wishing members a happy birthday.';
$txt['scheduled_task_weekly_maintenance'] = 'Weekly Maintenance';
$txt['scheduled_task_desc_weekly_maintenance'] = 'Runs essential weekly maintenance on the forum - should not be disabled.';
$txt['scheduled_task_paid_subscriptions'] = 'Paid Subscription Checks';
$txt['scheduled_task_desc_paid_subscriptions'] = 'Sends out any necessary paid subscription reminders and removes expired member subscriptions.';

$txt['scheduled_task_reg_starting'] = 'Starting at %s';
$txt['scheduled_task_reg_repeating'] = 'repeating every %1$d %2$s';
$txt['scheduled_task_reg_unit_m'] = 'minute(s)';
$txt['scheduled_task_reg_unit_h'] = 'hour(s)';
$txt['scheduled_task_reg_unit_d'] = 'day(s)';
$txt['scheduled_task_reg_unit_w'] = 'week(s)';

$txt['scheduled_task_edit'] = 'Edit Scheduled Task';
$txt['scheduled_task_edit_repeat'] = 'Repeat task every';
$txt['scheduled_task_edit_pick_unit'] = 'Pick Unit';
$txt['scheduled_task_edit_interval'] = 'Interval';
$txt['scheduled_task_edit_start_time'] = 'Start Time';
$txt['scheduled_task_edit_start_time_desc'] = 'Time the first instance of the day should start (hours:minutes)';
$txt['scheduled_task_time_offset'] = 'Note the start time should be the offset against the current server time. Current server time is: %1$s';

$txt['scheduled_view_log'] = 'View Log';
$txt['scheduled_log_empty'] = 'There are currently no items in the log.';
$txt['scheduled_log'] = 'Task Log';
$txt['scheduled_log_time_run'] = 'Time Run';
$txt['scheduled_log_time_taken'] = 'Time taken';
$txt['scheduled_log_time_taken_seconds'] = '%1$d seconds';
$txt['scheduled_log_empty_log'] = 'Empty Log';

$txt['utf8_title'] = 'Convert the database and data to UTF-8';
$txt['utf8_introduction'] = 'UTF-8 is an international character set covering nearly all languages around the world. Converting your database and data to UTF-8 can make it easier to support multiple languages on the same board. It also can enhance search and sorting capabilities for languages with non-latin characters.';
$txt['utf8_warning'] = 'If you want to convert your data and database to UTF-8, be aware of the following:
<ul>
	<li>Converting character sets might be <em>harmful</em> for your data! Make sure you have backed up your database <i>before</i> converting.</li>
	<li>Because UTF-8 is a richer character set than most other character sets, there\'s no way back, unless by restoring your database to before the conversion.</li>
	<li>After converting your data and database to UTF-8, you will need UTF-8 compatible language files.</li>
</ul>';
$txt['utf8_charset_not_supported'] = 'Conversion from %s to UTF-8 is not supported.';
$txt['utf8_detected_charset'] = 'Based on your default language file (\'%1$s\'), the character set of your data would most likely be \'%2$s\'.';
$txt['utf8_already_utf8'] = 'Your database and data already seem to be configured as UTF-8 data. No conversion is needed.';
$txt['utf8_source_charset'] = 'Data character set';
$txt['utf8_proceed'] = 'Proceed';
$txt['utf8_database_charset'] = 'Database character set';
$txt['utf8_target_charset'] = 'Convert data and database to';
$txt['utf8_utf8'] = 'UTF-8';
$txt['utf8_db_version_too_low'] = 'The version of MySQL that your database server is using is not high enough to support UTF-8 properly. A minimum version of 4.1.2 is required.';

$txt['entity_convert_title'] = 'Convert HTML-entities to UTF-8 characters';
$txt['entity_convert_only_utf8'] = 'The database needs to be in UTF-8 format before HTML-entities can be converted to UTF-8';
$txt['entity_convert_introduction'] = 'This function will convert all characters that are stored in the database as HTML-entities to UTF-8 characters. This is especially useful when you have just converted your forum from a character set like ISO-8859-1 while non-latin characters were used on the forum. The browser then sends all characters as HTML-entities. For example, the HTML-entity &amp;#945; represents the greek letter &#945; (alpha). Converting entities to UTF-8 will improve searching and sorting of text and reduce storage size.';
$txt['entity_convert_proceed'] = 'Proceed';

$txt['maintain_common_task_database'] = 'Database';
$txt['maintain_common_task_routine'] = 'Routine';
$txt['maintain_common_task_members'] = 'Members';
$txt['maintain_common_task_topics'] = 'Topics';
$txt['maintain_common_task_misc'] = 'Miscellaneous';

// Move topics out.
$txt['move_topics_maintenance'] = 'Move Topics';
$txt['move_topics_select_board'] = 'Select Board';
$txt['move_topics_from'] = 'Move topics from';
$txt['move_topics_to'] = 'to';
$txt['move_topics_now'] = 'Move now';
$txt['move_topics_confirm'] = 'Are you sure you want to move ALL the topics from &quot;%board_from%&quot; to &quot;%board_to%&quot;?';

$txt['maintain_reattribute_posts'] = 'Reattribute User Posts';
$txt['reattribute_guest_posts'] = 'Attribute guest posts made with';
$txt['reattribute_email'] = 'Email address of';
$txt['reattribute_username'] = 'Username of';
$txt['reattribute_current_member'] = 'Attribute posts to member';
$txt['reattribute_increase_posts'] = 'Add posts to users post count';
$txt['reattribute'] = 'Reattribute';
// Don't use entities in the below string.
$txt['reattribute_confirm'] = 'Are you sure you want to attribute all guests post with %type% of "%find%" to member "%member_to%"?';
$txt['reattribute_confirm_username'] = 'a username';
$txt['reattribute_confirm_email'] = 'an email address';
$txt['reattribute_cannot_find_member'] = 'Could not find member to attribute posts to.';

?>