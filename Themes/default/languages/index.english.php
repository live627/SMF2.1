<?php
// Version: 2.0 Alpha; index

/* Important note about language files in SMF 2.0 upwards:
	1) All language entries in SMF 2.0 are cached. All edits should therefore be made through the admin menu. If you do
	   edit a language file manually you will not see the changes in SMF until the cache refreshes. To manually refresh
	   the cache go to Admin => Maintenance => Clean Cache.

	2) Please also note that strings should use single quotes, not double quotes for enclosing the string
	   except for line breaks.

*/

global $forum_copyright, $forum_version, $webmaster_email;

// Locale (strftime, pspell_new) and spelling. (pspell_new, can be left as '' normally.)
// For more information see:
//   - http://www.php.net/function.pspell-new
//   - http://www.php.net/function.setlocale
// Again, SPELLING SHOULD BE '' 99% OF THE TIME!!  Please read this!
$txt['lang_locale'] = 'en_US';
$txt['lang_dictionary'] = 'en';
$txt['lang_spelling'] = 'american';

// Character set and right to left?
$txt['lang_character_set'] = 'ISO-8859-1';
$txt['lang_rtl'] = false;

$txt['days'] = array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday');
$txt['days_short'] = array('Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat');
// Months must start with 1 => 'January'. (or translated, of course.)
$txt['months'] = array(1 => 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
$txt['months_titles'] = array(1 => 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
$txt['months_short'] = array(1 => 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');

$txt['newmessages0'] = 'is new';
$txt['newmessages1'] = 'are new';
$txt['newmessages3'] = 'New';
$txt['newmessages4'] = ',';

$txt['admin'] = 'Admin';
$txt['moderate'] = 'Moderate';

$txt['save'] = 'Save';

$txt['modify'] = 'Modify';
$txt['forum_index'] = '%1$s - Index';
$txt['members'] = 'Members';
$txt['board_name'] = 'Board name';
$txt['posts'] = 'Posts';

$txt['member_postcount'] = 'Posts';
$txt['no_subject'] = '(No subject)';
$txt['view_profile'] = 'View Profile';
$txt['guest_title'] = 'Guest';
$txt['author'] = 'Author';
$txt['on'] = 'on';
$txt['remove'] = 'Remove';
$txt['start_new_topic'] = 'Start new topic';

$txt['login'] = 'Login';
// Use numeric entities in the below string.
$txt['username'] = 'Username';
$txt['password'] = 'Password';

$txt['username_no_exist'] = 'That username does not exist.';

$txt['board_moderator'] = 'Board Moderator';
$txt['remove_topic'] = 'Remove Topic';
$txt['topics'] = 'Topics';
$txt['modify_msg'] = 'Modify message';
$txt['name'] = 'Name';
$txt['email'] = 'Email';
$txt['subject'] = 'Subject';
$txt['message'] = 'Message';

$txt['profile'] = 'Profile';

$txt['choose_pass'] = 'Choose password';
$txt['verify_pass'] = 'Verify password';
$txt['position'] = 'Position';

$txt['profile_of'] = 'View the profile of';
$txt['total'] = 'Total';
$txt['posts_made'] = 'Posts';
$txt['website'] = 'Website';
$txt['register'] = 'Register';

$txt['message_index'] = 'Message Index';
$txt['news'] = 'News';
$txt['home'] = 'Home';

$txt['lock_unlock'] = 'Lock/Unlock Topic';
$txt['post'] = 'Post';
$txt['error_occured'] = 'An Error Has Occurred!';
$txt['at'] = 'at';
$txt['logout'] = 'Logout';
$txt['started_by'] = 'Started by';
$txt['replies'] = 'Replies';
$txt['last_post'] = 'Last post';
$txt['admin_login'] = 'Administration Login';
// Use numeric entities in the below string.
$txt['topic'] = 'Topic';
$txt['help'] = 'Help';
$txt['remove_message'] = 'Remove message';
$txt['notify'] = 'Notify';
$txt['notify_request'] = 'Do you want a notification email if someone replies to this topic?';
// Use numeric entities in the below string.
$txt['notify_replies'] = 'Notify of replies';
$txt['move_topic'] = 'Move Topic';
$txt['move_to'] = 'Move to';
$txt['pages'] = 'Pages';
$txt['users_active'] = 'Users active in past %1$d minutes';
$txt['personal_messages'] = 'Personal Messages';
$txt['reply_quote'] = 'Reply with quote';
$txt['reply'] = 'Reply';
$txt['approve'] = 'Approve';
$txt['approve_all'] = 'approve all';
$txt['attach_awaiting_approve'] = 'Attachments awaiting approval';

$txt['msg_alert_none'] = 'No messages...';
$txt['msg_alert_you_have'] = 'you have';
$txt['msg_alert_messages'] = 'messages';
$txt['remove_message'] = 'Remove this message';

$txt['online_users'] = 'Users Online';
$txt['personal_message'] = 'Personal Message';
$txt['jump_to'] = 'Jump to';
$txt['go'] = 'go';
$txt['are_sure_remove_topic'] = 'Are you sure you want to remove this topic?';
$txt['yes'] = 'Yes';
$txt['no'] = 'No';

$txt['search_results'] = 'Search Results';
$txt['search_end_results'] = 'End of results';
$txt['search_no_results'] = 'Sorry, no matches were found';
$txt['search_on'] = 'on';

$txt['search'] = 'Search';
$txt['all'] = 'All';

$txt['back'] = 'Back';
$txt['password_reminder'] = 'Password reminder';
$txt['topic_started'] = 'Topic started by';
$txt['title'] = 'Title';
$txt['post_by'] = 'Post by';
$txt['memberlist_searchable'] = 'Searchable list of all registered members.';
$txt['welcome_member'] = 'Please welcome';
$txt['admin_center'] = 'Administration Center';
$txt['last_edit'] = 'Last Edit';
$txt['notify_deactivate'] = 'Would you like to deactivate notification on this topic?';

$txt['recent_posts'] = 'Recent Posts';

$txt['location'] = 'Location';
$txt['gender'] = 'Gender';
$txt['date_registered'] = 'Date Registered';

$txt['recent_view'] = 'View the most recent posts on the forum.';
$txt['recent_updated'] = 'is the most recently updated topic';

$txt['male'] = 'Male';
$txt['female'] = 'Female';

$txt['error_invalid_characters_username'] = 'Invalid character used in Username.';

$txt['welcome_guest'] = 'Welcome, <b>%1$s</b>. Please <a href="' . $scripturl . '?action=login">login</a> or <a href="' . $scripturl . '?action=register">register</a>.';
$txt['welcome_guest_activate'] = '<br />Did you miss your <a href="' . $scripturl . '?action=activate">activation email?</a>';
$txt['hello_member'] = 'Hey,';
// Use numeric entities in the below string.
$txt['hello_guest'] = 'Welcome,';
$txt['welmsg_hey'] = 'Hey,';
$txt['welmsg_welcome'] = 'Welcome,';
$txt['welmsg_please'] = 'Please';
$txt['select_destination'] = 'Please select a destination';

// Escape any single quotes in here twice.. 'it\'s' -> 'it\\\'s'.
$txt['posted_by'] = 'Posted by';

$txt['icon_smiley'] = 'Smiley';
$txt['icon_angry'] = 'Angry';
$txt['icon_cheesy'] = 'Cheesy';
$txt['icon_laugh'] = 'Laugh';
$txt['icon_sad'] = 'Sad';
$txt['icon_wink'] = 'Wink';
$txt['icon_grin'] = 'Grin';
$txt['icon_shocked'] = 'Shocked';
$txt['icon_cool'] = 'Cool';
$txt['icon_huh'] = 'Huh';
$txt['icon_rolleyes'] = 'Roll Eyes';
$txt['icon_tongue'] = 'Tongue';
$txt['icon_embarrassed'] = 'Embarrassed';
$txt['icon_lips'] = 'Lips sealed';
$txt['icon_undecided'] = 'Undecided';
$txt['icon_kiss'] = 'Kiss';
$txt['icon_cry'] = 'Cry';

$txt['moderator'] = 'Moderator';
$txt['moderators'] = 'Moderators';

$txt['mark_board_read'] = 'Mark Topics as Read for this Board';
$txt['views'] = 'Views';
$txt['new'] = 'New';

$txt['view_all_members'] = 'View all members';
$txt['view'] = 'View';
$txt['email'] = 'Email';

$txt['viewing_members'] = 'Viewing Members %1$s to %2$s';
$txt['of_total_members'] = 'of %1$s total members';

$txt['forgot_your_password'] = 'Forgot your password?';

$txt['date'] = 'Date';
// Use numeric entities in the below string.
$txt['from'] = 'From';
$txt['subject'] = 'Subject';
$txt['check_new_messages'] = 'Check for new messages';
$txt['to'] = 'To';

$txt['board_topics'] = 'Topics';
$txt['members_title'] = 'Members';
$txt['members_list'] = 'Members List';
$txt['new_posts'] = 'New Posts';
$txt['old_posts'] = 'No New Posts';

$txt['sendtopic_send'] = 'Send';

$txt['time_offset'] = 'Time Offset';
$txt['or'] = 'or';

$txt['no_matches'] = 'Sorry, no matches were found';

$txt['notification'] = 'Notification';

$txt['your_ban'] = 'Sorry %s, you are banned from using this forum!';
$txt['your_ban_expires'] = 'Your ban is set to expire %s';
$txt['your_ban_expires_never'] = 'Your ban is not set to expire.';

$txt['mark_as_read'] = 'Mark ALL messages as read';

$txt['hot_topics'] = 'Hot Topic (More than %1$d replies)';
$txt['very_hot_topics'] = 'Very Hot Topic (More than %1$d replies)';
$txt['locked_topic'] = 'Locked Topic';
$txt['normal_topic'] = 'Normal Topic';
$txt['participation_caption'] = 'Topic you have posted in';

$txt['go_caps'] = 'GO';

$txt['print'] = 'Print';
$txt['profile'] = 'Profile';
$txt['topic_summary'] = 'Topic Summary';
$txt['not_applicable'] = 'N/A';
$txt['message_lowercase'] = 'message';
$txt['name_in_use'] = 'This name is already in use by another member.';

$txt['total_members'] = 'Total Members';
$txt['total_posts'] = 'Total Posts';
$txt['total_topics'] = 'Total Topics';

$txt['mins_logged_in'] = 'Minutes to stay logged in';

$txt['preview'] = 'Preview';
$txt['always_logged_in'] = 'Always stay logged in';

$txt['logged'] = 'Logged';
// Use numeric entities in the below string.
$txt['ip'] = 'IP';

$txt['icq'] = 'ICQ';
$txt['www'] = 'WWW';

$txt['by'] = 'by';

$txt['hours'] = 'hours';
$txt['days_word'] = 'days';

$txt['newest_member'] = ', our newest member.';

$txt['search_for'] = 'Search for';

$txt['aim'] = 'AIM';
// In this string, please use +'s for spaces.
$txt['aim_default_message'] = 'Hi.+Are+you+there?';
$txt['yim'] = 'YIM';

$txt['maintain_mode_on'] = 'Remember, this forum is in \'Maintenance Mode\'.';

$txt['read'] = 'Read';
$txt['times'] = 'times';

$txt['forum_stats'] = 'Forum Stats';
$txt['latest_member'] = 'Latest Member';
$txt['total_cats'] = 'Total Categories';
$txt['latest_post'] = 'Latest Post';

$txt['you_have'] = 'You\'ve got';
$txt['click'] = 'Click';
$txt['here'] = 'here';
$txt['to_view'] = 'to view them.';

$txt['total_boards'] = 'Total Boards';

$txt['print_page'] = 'Print Page';

$txt['valid_email'] = 'This must be a valid email address.';

$txt['geek'] = 'I am a geek!!';
$txt['info_center_title'] = '%s - Info Center';

$txt['send_topic'] = 'Send this topic';

$txt['sendtopic_title'] = 'Send the topic &quot;%s&quot; to a friend.';
// Use numeric entities in the below three strings.
$txt['sendtopic_dear'] = 'Dear %s,';
$txt['sendtopic_this_topic'] = 'I want you to check out "%s" on %s.  To view it, please click this link';
$txt['sendtopic_thanks'] = 'Thanks';
$txt['sendtopic_sender_name'] = 'Your name';
$txt['sendtopic_sender_email'] = 'Your email address';
$txt['sendtopic_receiver_name'] = 'Recipient\'s name';
$txt['sendtopic_receiver_email'] = 'Recipient\'s email address';
$txt['sendtopic_comment'] = 'Add a comment';
// Use numeric entities in the below string.
$txt['sendtopic2'] = 'A comment has also been added regarding this topic';

$txt['hide_email'] = 'Hide email address from public?';

$txt['check_all'] = 'Check all';

// Use numeric entities in the below string.
$txt['database_error'] = 'Database Error';
$txt['try_again'] = 'Please try again.  If you come back to this error screen, report the error to an administrator.';
$txt['file'] = 'File';
$txt['line'] = 'Line';
// Use numeric entities in the below string.
$txt['tried_to_repair'] = 'SMF has detected and automatically tried to repair an error in your database.  If you continue to have problems, or continue to receive these emails, please contact your host.';
$txt['database_error_versions'] = '<b>Note:</b> It appears that your database <em>may</em> require an upgrade. Your forum\'s files are currently at version %s, while your database is at version %s. The above error might possibly go away if you execute the latest version of upgrade.php.';
$txt['template_parse_error'] = 'Template Parse Error!';
$txt['template_parse_error_message'] = 'It seems something has gone sour on the forum with the template system.  This problem should only be temporary, so please come back later and try again.  If you continue to see this message, please contact the administrator.<br /><br />You can also try <a href="javascript:location.reload();">refreshing this page</a>.';
$txt['template_parse_error_details'] = 'There was a problem loading the <tt><b>%1$s</b></tt> template or language file.  Please check the syntax and try again - remember, single quotes (<tt>\'</tt>) often have to be escaped with a slash (<tt>\\</tt>).  To see more specific error information from PHP, try <a href="' . $boardurl . '%1$s">accessing the file directly</a>.<br /><br />You may want to try to <a href="javascript:location.reload();">refresh this page</a> or <a href="' . $scripturl . '?theme=1">use the default theme</a>.';

$txt['today'] = '<b>Today</b> at ';
$txt['yesterday'] = '<b>Yesterday</b> at ';
$txt['new_poll'] = 'Post new poll';
$txt['poll_question'] = 'Question';
$txt['poll_vote'] = 'Submit Vote';
$txt['poll_total_voters'] = 'Total Voters';
$txt['shortcuts'] = 'shortcuts: hit alt+s to submit/post or alt+p to preview';
$txt['poll_results'] = 'View results';
$txt['poll_lock'] = 'Lock Voting';
$txt['poll_unlock'] = 'Unlock Voting';
$txt['poll_edit'] = 'Edit Poll';
$txt['poll'] = 'Poll';
$txt['one_day'] = '1 Day';
$txt['one_week'] = '1 Week';
$txt['one_month'] = '1 Month';
$txt['forever'] = 'Forever';
$txt['quick_login_dec'] = 'Login with username, password and session length';
$txt['one_hour'] = '1 Hour';
$txt['moved'] = 'MOVED';
$txt['moved_why'] = 'Please enter a brief description as to<br />why this topic is being moved.';
$txt['board'] = 'Board';
$txt['in'] = 'in';
$txt['smf96'] = 'Sticky Topic';

$txt['delete'] = 'Delete';

$txt['your_pms'] = 'Your Personal Messages';

$txt['kilobyte'] = 'KB';

$txt['more_stats'] = '[More Stats]';

// Use numeric entities in the below three strings.
$txt['code'] = 'Code';
$txt['quote_from'] = 'Quote from';
$txt['quote'] = 'Quote';

$txt['merge_to_topic_id'] = 'ID of target topic';
$txt['split'] = 'Split Topic';
$txt['merge'] = 'Merge Topics';
$txt['subject_new_topic'] = 'Subject For New Topic';
$txt['split_this_post'] = 'Only split this post.';
$txt['split_after_and_this_post'] = 'Split topic after and including this post.';
$txt['select_split_posts'] = 'Select posts to split.';
$txt['new_topic'] = 'New Topic';
$txt['split_successful'] = 'Topic successfully split into two topics.';
$txt['origin_topic'] = 'Origin Topic';
$txt['please_select_split'] = 'Please select which posts you wish to split.';
$txt['merge_successful'] = 'Topics successfully merged.';
$txt['new_merged_topic'] = 'Newly Merged Topic';
$txt['topic_to_merge'] = 'Topic to be merged';
$txt['target_board'] = 'Target board';
$txt['target_topic'] = 'Target topic';
$txt['merge_confirm'] = 'Are you sure you want to merge';
$txt['with'] = 'with';
$txt['merge_desc'] = 'This function will merge the messages of two topics into one topic. The messages will be sorted according to the time of posting. Therefore the earliest posted message will be the first message of the merged topic.';

$txt['set_sticky'] = 'Set topic sticky';
$txt['set_nonsticky'] = 'Set topic non-sticky';
$txt['set_lock'] = 'Lock topic';
$txt['set_unlock'] = 'Unlock topic';

$txt['search_advanced'] = 'Advanced search';

$txt['security_risk'] = 'MAJOR SECURITY RISK:';
$txt['not_removed'] = 'You have not removed ';

$txt['cache_writable_head'] = 'Performance Warning';
$txt['cache_writable'] = 'The cache directory is not writable - this will adversely affect the performance of your forum.';

$txt['page_created'] = 'Page created in ';
$txt['seconds_with'] = ' seconds with ';
$txt['queries'] = ' queries.';

$txt['report_to_mod_func'] = 'Use this function to inform the moderators and administrators of an abusive or wrongly posted message.<br /><i>Please note that your email address will be revealed to the moderators if you use this.</i>';

$txt['online'] = 'Online';
$txt['offline'] = 'Offline';
$txt['pm_online'] = 'Personal Message (Online)';
$txt['pm_offline'] = 'Personal Message (Offline)';
$txt['status'] = 'Status';

$txt['go_up'] = 'Go Up';
$txt['go_down'] = 'Go Down';

$forum_copyright = '<a href="http://www.simplemachines.org/" title="Simple Machines Forum" target="_blank">Powered by %s</a> |
 <a href="http://www.simplemachines.org/about/copyright.php" title="Free Forum Software" target="_blank">SMF &copy; 2006-2007, Simple Machines LLC</a>';

$txt['birthdays'] = 'Birthdays:';
$txt['events'] = 'Events:';
$txt['birthdays_upcoming'] = 'Upcoming Birthdays:';
$txt['events_upcoming'] = 'Upcoming Events:';
// Prompt for holidays in the calendar, leave blank to just display the holiday's name.
$txt['calendar_prompt'] = '';
$txt['calendar_month'] = 'Month:';
$txt['calendar_year'] = 'Year:';
$txt['calendar_day'] = 'Day:';
$txt['calendar_event_title'] = 'Event Title:';
$txt['calendar_post_in'] = 'Post In:';
$txt['calendar_edit'] = 'Edit Event';
$txt['event_delete_confirm'] = 'Delete this event?';
$txt['event_delete'] = 'Delete Event';
$txt['calendar_post_event'] = 'Post Event';
$txt['calendar'] = 'Calendar';
$txt['calendar_link'] = 'Link to Calendar';
$txt['calendar_link_event'] = 'Link Event';
$txt['calendar_upcoming'] = 'Upcoming Calendar';
$txt['calendar_today'] = 'Today\'s Calendar';
$txt['calendar_week'] = 'Week';
$txt['calendar_numb_days'] = 'Number of Days:';
$txt['calendar_how_edit'] = 'how do you edit these events?';
$txt['calendar_link_event'] = 'Link Event To Post:';
$txt['calendar_confirm_delete'] = 'Are you sure you want to delete this event?';
$txt['calendar_linked_events'] = 'Linked Events';

$txt['moveTopic1'] = 'Post a redirection topic';
$txt['moveTopic2'] = 'Change the topic\'s subject';
$txt['moveTopic3'] = 'New subject';
$txt['moveTopic4'] = 'Change every message\'s subject';

$txt['theme_template_error'] = 'Unable to load the \'%s\' template.';
$txt['theme_language_error'] = 'Unable to load the \'%s\' language file.';

$txt['parent_boards'] = 'Child Boards';

$txt['smtp_no_connect'] = 'Could not connect to SMTP host';
$txt['smtp_port_ssl'] = 'SMTP port setting incorrect; it should be 465 for SSL servers.';
$txt['smtp_bad_response'] = 'Couldn\'t get mail server response codes';
$txt['smtp_error'] = 'Ran into problems sending Mail. Error: ';
$txt['mail_send_unable'] = 'Unable to send mail to the email address \'%s\'';

$txt['mlist_search'] = 'Search for members';
$txt['mlist_search_again'] = 'Search again';
$txt['mlist_search_email'] = 'Search by email address';
$txt['mlist_search_messenger'] = 'Search by messenger nickname';
$txt['mlist_search_group'] = 'Search by position';
$txt['mlist_search_name'] = 'Search by name';
$txt['mlist_search_website'] = 'Search by website';
$txt['mlist_search_results'] = 'Search results for';

$txt['attach_downloaded'] = 'downloaded';
$txt['attach_viewed'] = 'viewed';
$txt['attach_times'] = 'times';

$txt['msn'] = 'MSN';

$txt['settings'] = 'Settings';
$txt['never'] = 'Never';
$txt['more'] = 'more';

$txt['hostname'] = 'Hostname';
$txt['you_are_post_banned'] = 'Sorry %s, you are banned from posting or sending personal messages on this forum.';
$txt['ban_reason'] = 'Reason';

$txt['tables_optimized'] = 'Database tables optimized';

$txt['add_poll'] = 'Add poll';
$txt['poll_options6'] = 'You may only select up to %s options.';
$txt['poll_remove'] = 'Remove Poll';
$txt['poll_remove_warn'] = 'Are you sure you want to remove this poll from the topic?';
$txt['poll_results_expire'] = 'Results will be shown when voting has closed';
$txt['poll_expires_on'] = 'Voting closes';
$txt['poll_expired_on'] = 'Voting closed';
$txt['poll_change_vote'] = 'Remove Vote';
$txt['poll_return_vote'] = 'Voting options';

$txt['quick_mod_approve'] = 'Approve selected';
$txt['quick_mod_remove'] = 'Remove selected';
$txt['quick_mod_lock'] = 'Lock selected';
$txt['quick_mod_sticky'] = 'Sticky selected';
$txt['quick_mod_move'] = 'Move selected to';
$txt['quick_mod_merge'] = 'Merge selected';
$txt['quick_mod_markread'] = 'Mark selected read';
$txt['quick_mod_go'] = 'Go!';
$txt['quickmod_confirm'] = 'Are you sure you want to do this?';

$txt['spell_check'] = 'Spell Check';

$txt['quick_reply'] = 'Quick Reply';
$txt['quick_reply_desc'] = 'With a <i>Quick-Reply</i> you can use bulletin board code and smileys as you would in a normal post, but much more conveniently.';
$txt['quick_reply_warning'] = 'Warning: this topic is currently locked!<br />Only admins and moderators can reply.';
$txt['wait_for_approval'] = 'Note: this post will not display until it\'s been approved by a moderator.';

$txt['notification_enable_board'] = 'Are you sure you wish to enable notification of new topics for this board?';
$txt['notification_disable_board'] = 'Are you sure you wish to disable notification of new topics for this board?';
$txt['notification_enable_topic'] = 'Are you sure you wish to enable notification of new replies for this topic?';
$txt['notification_disable_topic'] = 'Are you sure you wish to disable notification of new replies for this topic?';

$txt['report_to_mod'] = 'Report to moderator';

$txt['unread_topics_visit'] = 'Recent Unread Topics';
$txt['unread_topics_visit_none'] = 'No unread topics found since your last visit.  <a href="' . $scripturl . '?action=unread;all">Click here to try all unread topics</a>.';
$txt['unread_topics_all'] = 'All Unread Topics';
$txt['unread_replies'] = 'Updated Topics';

$txt['who_title'] = 'Who\'s Online';
$txt['who_and'] = ' and ';
$txt['who_viewing_topic'] = ' are viewing this topic.';
$txt['who_viewing_board'] = ' are viewing this board.';
$txt['who_member'] = 'Member';

$txt['powered_by_php'] = 'Powered by PHP';
$txt['powered_by_mysql'] = 'Powered by MySQL';
$txt['valid_html'] = 'Valid HTML 4.01!';
$txt['valid_xhtml'] = 'Valid XHTML 1.0!';
$txt['valid_css'] = 'Valid CSS!';

$txt['guest'] = 'Guest';
$txt['guests'] = 'Guests';
$txt['user'] = 'User';
$txt['users'] = 'Users';
$txt['hidden'] = 'Hidden';
$txt['buddy'] = 'Buddy';
$txt['buddies'] = 'Buddies';
$txt['most_online_ever'] = 'Most Online Ever';
$txt['most_online_today'] = 'Most Online Today';

$txt['merge_select_target_board'] = 'Select the target board of the merged topic';
$txt['merge_select_poll'] = 'Select which poll the merged topic should have';
$txt['merge_topic_list'] = 'Select topics to be merged';
$txt['merge_select_subject'] = 'Select subject of merged topic';
$txt['merge_custom_subject'] = 'Custom subject';
$txt['merge_enforce_subject'] = 'Change the subject of all the messages';
$txt['merge_include_notifications'] = 'Include notifications?';
$txt['merge_check'] = 'Merge?';
$txt['merge_no_poll'] = 'No poll';

$txt['response_prefix'] = 'Re: ';
$txt['current_icon'] = 'Current Icon';

$txt['smileys_current'] = 'Current Smiley Set';
$txt['smileys_none'] = 'No Smileys';
$txt['smileys_forum_board_default'] = 'Forum/Board Default';

$txt['search_results'] = 'Search Results';
$txt['search_no_results'] = 'No results found';

$txt['totalTimeLogged1'] = 'Total time logged in: ';
$txt['totalTimeLogged2'] = ' days, ';
$txt['totalTimeLogged3'] = ' hours and ';
$txt['totalTimeLogged4'] = ' minutes.';
$txt['totalTimeLogged5'] = 'd ';
$txt['totalTimeLogged6'] = 'h ';
$txt['totalTimeLogged7'] = 'm';

$txt['approve_thereis'] = 'There is';
$txt['approve_thereare'] = 'There are';
$txt['approve_member'] = 'one member';
$txt['approve_members'] = 'members';
$txt['approve_members_waiting'] = 'awaiting approval.';

$txt['notifyboard_turnon'] = 'Do you want a notification email when someone posts a new topic in this board?';
$txt['notifyboard_turnoff'] = 'Are you sure you do not want to receive new topic notifications for this board?';

$txt['activate_code'] = 'Your activation code is';

$txt['find_members'] = 'Find Members';
$txt['find_username'] = 'Name, username, or email address';
$txt['find_buddies'] = 'Show Buddies Only?';
$txt['find_wildcards'] = 'Allowed Wildcards: *, ?';
$txt['find_no_results'] = 'No results found';
$txt['find_results'] = 'Results';
$txt['find_close'] = 'Close';

$txt['unread_since_visit'] = 'Show unread posts since last visit.';
$txt['show_unread_replies'] = 'Show new replies to your posts.';

$txt['change_color'] = 'Change Color';

$txt['quickmod_delete_selected'] = 'Delete Selected';

// In this string, don't use entities. (&amp;, etc.)
$txt['show_personal_messages'] = 'You have received one or more new personal messages.\\nView them now (in a new window)?';

$txt['previous_next_back'] = '&laquo; previous';
$txt['previous_next_forward'] = 'next &raquo;';

$txt['movetopic_auto_board'] = '[BOARD]';
$txt['movetopic_auto_topic'] = '[TOPIC LINK]';
$txt['movetopic_default'] = 'This topic has been moved to ' . $txt['movetopic_auto_board'] . ".\n\n" . $txt['movetopic_auto_topic'];

$txt['upshrink_description'] = 'Shrink or expand the header.';

$txt['mark_unread'] = 'Mark unread';

$txt['ssi_not_direct'] = 'Please don\'t access SSI.php by URL directly; you may want to use the path (%s) or add ?ssi_function=something.';
$txt['ssi_session_broken'] = 'SSI.php was unable to load a session!  This may cause problems with logout and other functions - please make sure SSI.php is included before *anything* else in all your scripts!';

// Escape any single quotes in here twice.. 'it\'s' -> 'it\\\'s'.
$txt['preview_title'] = 'Preview post';
$txt['preview_fetch'] = 'Fetching preview...';
$txt['preview_new'] = 'New message';
$txt['error_while_submitting'] = 'The following error or errors occurred while posting this message:';

$txt['split_selected_posts'] = 'Selected posts';
$txt['split_selected_posts_desc'] = 'The posts below will form a new topic after splitting.';
$txt['split_reset_selection'] = 'reset selection';

$txt['modify_cancel'] = 'Cancel';
$txt['mark_read_short'] = 'Mark Read';

$txt['pm_short'] = 'My Messages';
$txt['hello_member_ndt'] = 'Hello';

$txt['unapproved_posts'] = 'Unapproved Posts (Topics: %d, Posts: %d)';

$txt['ajax_in_progress'] = 'Loading...';

$txt['mod_reports_waiting'] = 'There are currently %1$d moderator reports open.';

$txt['view_unread_category'] = 'Unread Posts';
?>
