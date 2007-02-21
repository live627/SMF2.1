<?php
// Version: 2.0 Alpha; EmailTemplates

// Important! Before editing these language files please read the text at the topic of index.english.php.

// Since all of these strings are being used in emails, numeric entities shold be used.

// Do not translate anything that is between {}, they are used as replacement variables and MUST remain exactly how they are.
//   Additionally do not translate the @additioinal_parmas: line or the variable names in the lines that follow it.  You may
//   translate the description of the variable.  Do not translate @description:, however you may translate the rest of that line.

// Do not use block comments in this file, they will have special meaning.

global $context;

$txt['regards_team'] = 'Regards,' . "\n" . 'The ' . $context['forum_name'] . ' Team.';

$txt['scheduled_approval_email_topic'] = 'The following topics are awaiting approval:';
$txt['scheduled_approval_email_msg'] = 'The following posts are awaiting approval:';
$txt['scheduled_approval_email_attach'] = 'The following attachments are awaiting approval:';
$txt['scheduled_approval_email_event'] = 'The following events are awaiting approval:';

$txt['emails'] = array(
	'resend_activate_message' => array(
		/* 
			@additional_params: resend_activate_message
				REALNAME: The display name for the member receiving the email.
				USERNAME:  The user name for the emmber receiving the email.
				ACTIVATIONLINK:  The url link to activate the member's account.
				ACTIVATIONCODE:  The code needed to activate the member's account.
			@description: 
		*/
		'subject' => 'Welcome to {FORUMNAME}',
		'body' => 'You are now registered with an account at {FORUMNAME}, {REALNAME}!

Your username is "{USERNAME}".

Before you can login, you first need to activate your account. To do so, please follow this link:

{ACTIVATIONLINK}

Should you have any problems with activation, please use the code "{ACTIVATIONCODE}".

{REGARDS}',
	),

	'resend_pending_message' => array(
		/* 
			@additional_params: resend_pending_message
				REALNAME: The display name for the member receiving the email.
				USERNAME:  The user name for the emmber receiving the email.
			@description: 
		*/
		'subject' => 'Welcome to {FORUMNAME}',
		'body' => 'Your registration request at {FORUMNAME} has been received, {REALNAME}.

The username you registered with was {USERNAME}.

Before you can login and start using the forum, your request will be reviewed and approved.  When this happens, you will receive another email from this address.

{REGARDS}',
	),
	'mc_group_approve' => array(
		/*
			@additional_params: mc_group_approve
				USERNAME: The user name for the member receiving the email.
				GROUPNAME: The name of the membergroup that the user was accepted into.
			@description: The request to join a particular membergroup has been accepted.
		*/
		'subject' => 'Group Membership Approval',
		'body' => '{USERNAME},

We\'re pleased to notify you that your application to join the &quot;{GROUPNAME}&quot; group at {FORUMNAME} has been accepted, and your account has been updated to include this new membergroup.

{REGARDS}',
	),
	'mc_group_reject' => array(
		/*
			@additional_params: mc_group_approve
				USERNAME: The user name for the member receiving the email.
				GROUPNAME: The name of the membergroup that the user was rejected from.
			@description: The request to join a particular membergroup has been rejected.
		*/
		'subject' => 'Group Membership Rejection',
		'body' => '{USERNAME},

We\'re sorry to notify you that your application to join the &quot;{GROUPNAME}&quot; group at {FORUMNAME} has been rejected.

{REGARDS}',
	),
	'mc_group_reject_reason' => array(
		/*
			@additional_params: mc_group_approve
				USERNAME: The user name for the member receiving the email.
				GROUPNAME: The name of the membergroup that the user was rejected from.
				REASON: Reason for the rejection.
			@description: The request to join a particular membergroup has been rejected with a reason given.
		*/
		'subject' => 'Group Membership Rejection',
		'body' => '{USERNAME},

We\'re sorry to notify you that your application to join the &quot;{GROUPNAME}&quot; group at {FORUMNAME} has been rejected.

This is due to the following reason: {REASON}

{REGARDS}',
	),
	'admin_approve_accept' => array(
		/*
			@additional_params: mc_group_approve
				USERNAME: The user name for the member receiving the email.
				PROFILELINK: The URL of the profile page.
			@description:
		*/
		'subject' => 'Welcome to {FORUMNAME}',
		'body' => 'Welcome, {USERNAME}!

Your account has been activated manually by the admin and you can now login and post. Your username is: {USERNAME}

You may change it after you login by going to the profile page, or by visiting this page after you login:
{PROFILELINK}

{REGARDS}',
	),
	'admin_approve_activation' => array(
		/*
			@additional_params: mc_group_approve
				USERNAME: The user name for the member receiving the email.
				ACTIVATIONLINK:  The url link to activate the member's account.
			@description:
		*/
		'subject' => 'Welcome to {FORUMNAME}',
		'body' => 'Welcome, {USERNAME}!

Your account on {FORUMNAME} has been approved by the forum administrator, and must now be activated before you can begin posting.  Please use the link below to activate your account:
{ACTIVATIONLINK}

{REGARDS}',
	),
	'admin_approve_reject' => array(
		/*
			@additional_params: mc_group_approve
				USERNAME: The user name for the member receiving the email.
			@description:
		*/
		'subject' => 'Registration Rejected',
		'body' => '{USERNAME},

Regrettably, your application to join {FORUMNAME} has been rejected.

{REGARDS}',
	),
	'admin_approve_delete' => array(
		/*
			@additional_params: mc_group_approve
				USERNAME: The user name for the member receiving the email.
			@description:
		*/
		'subject' => 'Account Deleted',
		'body' => '{USERNAME},

Your account on {FORUMNAME} has been deleted.  This may be because you never activated your account, in which case you should be able to register again.

{REGARDS}',
	),
	'admin_approve_remind' => array(
		/*
			@additional_params: mc_group_approve
				USERNAME: The user name for the member receiving the email.
				ACTIVATIONLINK:  The url link to activate the member's account.
			@description:
		*/
		'subject' => 'Registration Reminder',
		'body' => '{USERNAME},
You still have not activated your account at {FORUMNAME}.

Please use the link below to activate your account:
{ACTIVATIONLINK}

{REGARDS}',
	),
	'new_announcement' => array(
		/*
			@additional_params: new_announcement
				TOPICSUBJECT: The subject of the topic being announced.
				MESSAGE: The message body of the first post of the announced topic.
				TOPICLINK: A link to the topic being announced.
			@description:

		*/
		'subject' => 'New announcement: {TOPICSUBJECT}',
		'body' => '{MESSAGE}

To unsubscribe from these announcements, login to the forum and uncheck "Receive forum announcements and important notifications by email." in your profile.

You can view the full announcement by following this link:
{TOPICLINK}

{REGARDS}',
	),
	'notify_boards_once_body' => array(
		/*
			@additional_params: notify_boards_once
				TOPICSUBJECT: The subject of the topic causing the notification
				TOPICLINK: A link to the topic.
				MESSAGE: This is the body of the message.
				UNSUBSCRIBELINK: Link to unsubscribe from notifications.
			@description:
		*/
		'subject' => 'New Topic: {TOPICSUBJECT}',
		'body' => 'A new topic, \'{TOPICSUBJECT}\', has been made on a board you are watching.

You can see it at
{TOPICLINK}

More topics may be posted, but you won\'t receive more email notifications until you return to the board and read some of them.

The text of the topic is shown below:
{MESSAGE}

Unsubscribe to new topics from this board by using this link:
{UNSUBSCRIBELINK}

{REGARDS}',
	),
	'notify_boards_once' => array(
		/*
			@additional_params: notify_boards_once
				TOPICSUBJECT: The subject of the topic causing the notification
				TOPICLINK: A link to the topic.
				UNSUBSCRIBELINK: Link to unsubscribe from notifications.
			@description:
		*/
		'subject' => 'New Topic: {TOPICSUBJECT}',
		'body' => 'A new topic, \'{TOPICSUBJECT}\', has been made on a board you are watching.

You can see it at
{TOPICLINK}

More topics may be posted, but you won\'t receive more email notifications until you return to the board and read some of them.

Unsubscribe to new topics from this board by using this link:
{UNSUBSCRIBELINK}

{REGARDS}',
	),
	'notify_boards_body' => array(
		/*
			@additional_params: notify_boards_once
				TOPICSUBJECT: The subject of the topic causing the notification
				TOPICLINK: A link to the topic.
				MESSAGE: This is the body of the message.
				UNSUBSCRIBELINK: Link to unsubscribe from notifications.
			@description:
		*/
		'subject' => 'New Topic: {TOPICSUBJECT}',
		'body' => 'A new topic, \'{TOPICSUBJECT}\', has been made on a board you are watching.

You can see it at
{TOPICLINK}

More topics may be posted, but you won\'t receive more email notifications until you return to the board and read some of them.

The text of the topic is shown below:
{MESSAGE}

Unsubscribe to new topics from this board by using this link:
{UNSUBSCRIBELINK}

{REGARDS}',
	),
	'notify_boards' => array(
		/*
			@additional_params: notify_boards_once
				TOPICSUBJECT: The subject of the topic causing the notification
				TOPICLINK: A link to the topic.
				UNSUBSCRIBELINK: Link to unsubscribe from notifications.
			@description:
		*/
		'subject' => 'New Topic: {TOPICSUBJECT}',
		'body' => 'A new topic, \'{TOPICSUBJECT}\', has been made on a board you are watching.

You can see it at
{TOPICLINK}

Unsubscribe to new topics from this board by using this link:
{UNSUBSCRIBELINK}

{REGARDS}',
	),
	'request_membership' => array(
		/*
			@additional_params: request_membership
				RECPNAME: The name of the person recieving the email
				APPYNAME: The name of the person applying for group membership
				GROUPNAME: The name of the group being applied to.
				REASON: The reason given by the applicant for wanting to join the group.
				MODLINK: Link to the group moderation page.
			@description:
		*/
		'subject' => 'New Group Application',
		'body' => '{RECPNAME},
		
{APPYNAME} has requested membership to the &quot;{GROUPNAME}&quot; group. The user has given the following reason:

{REASON}

You can approve or reject this application by clicking the link below:

{MODLINK}

{REGARDS}',
	),
	'activate_reactivate' => array(
		/*
			@additional_params: activate_reactivate
				ACTIVATIONLINK:  The url link to reactivate the member's account.
				ACTIVATIONCODE:  The code needed to reactivate the member's account.
			@description: 
		*/
		'subject' => 'Welcome back to {FORUMNAME}',
		'body' => 'In order to re-validate your email address, your account has been deactivated.  Click the following link to activate it again:
{ACTIVATIONLINK}

Should you have any problems with activation, please use the code "{ACTIVATIONCODE}".

{REGARDS}',
	),
	'forgot_password' => array(
		/*
			@additional_params: forgot_password
				REALNAME: The real (display) name of the person receiving the reminder.
				REMINDLINK: The link to reset the password.
				IP: The IP address of the requester.
				MEMBERNAME: 
			@description: 
		*/
		'subject' => 'New password for {FORUMNAME}',
		'body' => 'Dear {REALNAME},
This mail was sent because the \'forgot password\' function has been applied to your account. To set a new password click the following link:
{REMINDLINK}

IP: {IP}
Username: {MEMBERNAME}

{REGARDS}',
	),
	'scheduled_approval' => array(
		/*
			@additional_params: 
				REALNAME:
				BODY:
			@description:
		*/
		'subject' => 'Summary of posts awaiting approval at {FORUMNAME}',
		'body' => '{REALNAME},
		
This email contains a summary of all items awaiting approval at {FORUMNAME}.

{BODY}

Please log in to the forum to review these items.
{SCRIPTURL}

{REGARDS}',
	),
	'happy_birthday' => array(
		/*
			@additional_params:
				REALNAME:
			@description:
		*/
		'subject' => 'Happy birthday from {FORUMNAME}.',
		'body' => 'Dear {REALNAME},

We here at {FORUMNAME} would like to wish you a happy birthday.  May this day and the year to follow be full of joy.

{REGARDS}'
	),
	'send_topic' => array(
		/*
			@additional_params:
				TOPICSUBJECT: 
				SENDERNAME: 
				RECPNAME: 
				TOPICLINK: 
			@description:
		*/
		'subject' => 'Topic: {TOPICSUBJECT} (From: {SENDERNAME})',
		'body' => 'Dear {RECPNAME},
I want you to check out "{TOPICSUBJECT}" on {FORUMNAME}.  To view it, please click this link:

{TOPICLINK}

Thanks,

{SENDERNAME}',
	),
	'send_topic_comment' => array(
		/*
			@additional_params:
				TOPICSUBJECT: 
				SENDERNAME: 
				RECPNAME: 
				TOPICLINK: 
				COMMENT: 
			@description:
		*/
		'subject' => 'Topic: {TOPICSUBJECT} (From: {SENDERNAME})',
		'body' => 'Dear {RECPNAME},
I want you to check out "{TOPICSUBJECT}" on {FORUMNAME}.  To view it, please click this link:

{TOPICLINK}

A comment has also been added regarding this topic:
{COMMENT}

Thanks,

{SENDERNAME}',
	),
	'report_to_moderator' => array(
		/* 
			@additional_params: report_to_moderator
				TOPICSUBJECT: The subject of the reported post.
				POSTERNAME: The report post's author's name.
				REPORTERNAME: The name of the person reporting the post.
				TOPICLINK: The url of the post that is being reported.
				REPORTLINK: The url of the moderation center report.
				COMMENT: The comment left by the reporter, hopefully to explain why they are reporting the post.
			@description: When a user reports a post this email is sent out to moderators and admins of that board.
		*/
		'subject' => 'Reported post: {TOPICSUBJECT} by {POSTERNAME}',
		'body' => 'The following post, "{TOPICSUBJECT}" by {POSTERNAME} has been reported by {REPORTERNAME} on a board you moderate:

The topic: {TOPICLINK}
Moderation center: {REPORTLINK}

The reporter has made the following comment:
{COMMENT}

{REGARDS}',
	),
	'change_password' => array(
		/*
			@additional_params:
				USERNAME: 
				PASSWORD: 
			@description:
		*/
		'subject' => 'New Password Details',
		'body' => 'Hey, {USERNAME}!

Your login details at {FORUMNAME} have been changed and your password reset. Below are your new login details.

Your username is &quot;{USERNAME}&quot; and your password is &quot;{PASSWORD}&quot;.

You may change it after you login by going to the profile page, or by visiting this page after you login:
{SCRIPTURL}?action=profile

{REGARDS}',
	),
	'register_activate' => array(
		/*
			@additional_params:
				REALNAME: 
				USERNAME: 
				PASSWORD: 
				ACTIVATIONLINK:  The url link to reactivate the member's account.
				ACTIVATIONCODE:  The code needed to reactivate the member's account.
			@description:
		*/
		'subject' => 'Welcome to {FORUMNAME}',
		'body' => 'You are now registered with an account at {FORUMNAME}, {REALNAME}!

Your account\'s username is {USERNAME} and its password is {PASSWORD} (which can be changed later.)

Before you can login, you first need to activate your account. To do so, please follow this link:

{ACTIVATIONLINK}

Should you have any problems with activation, please use the code "{ACTIVATIONCODE}".

{REGARDS}',
	),
	'register_immediate' => array(
		/*
			@additional_params:
				REALNAME: 
				USERNAME: 
				PASSWORD: 
			@description:
		*/
		'subject' => 'Welcome to {FORUMNAME}',
		'body' => 'You are now registered with an account at {FORUMNAME}, {REALNAME}!

Your account\'s username is {USERNAME} and its password is {PASSWORD}.

You may change your password after you login by going to your profile, or by visiting this page after you login:

{SCRIPTURL}?action=profile

{REGARDS}',
	),
	'register_pending' => array(
		/*
			@additional_params:
				REALNAME: 
				USERNAME: 
				PASSWORD: 
			@description:
		*/
		'subject' => 'Welcome to {FORUMNAME}',
		'body' => 'Your registration request at {FORUMNAME} has been received, {REALNAME}.

The username you registered with was {USERNAME} and the password was {PASSWORD}.

Before you can login and start using the forum, your request will be reviewed and approved.  When this happens, you will receive another email from this address.

{REGARDS}',
	),
	'notification_reply' => array(
		/*
			@additional_params:
				TOPICSUBJECT:
				POSTERNAME:
				TOPICLINK:
				UNSUBSCRIBELINK:
			@description:
		*/
		'subject' => 'Topic reply: {TOPICSUBJECT}',
		'body' => 'A reply has been posted to a topic you are watching by {POSTERNAME}.

View the reply at: {TOPICLINK}

Unsubscribe to this topic by using this link: {UNSUBSCRIBELINK}

{REGARDS}',
	),
	'notification_reply_body' => array(
		/*
			@additional_params:
				TOPICSUBJECT:
				POSTERNAME:
				TOPICLINK:
				UNSUBSCRIBELINK:
				MESSAGE: 
			@description:
		*/
		'subject' => 'Topic reply: {TOPICSUBJECT}',
		'body' => 'A reply has been posted to a topic you are watching by {POSTERNAME}.

View the reply at: {TOPICLINK}

Unsubscribe to this topic by using this link: {UNSUBSCRIBELINK}

The text of the reply is shown below:
{MESSAGE}

{REGARDS}',
	),
	'notification_reply_once' => array(
		/*
			@additional_params:
				TOPICSUBJECT:
				POSTERNAME:
				TOPICLINK:
				UNSUBSCRIBELINK:
			@description:
		*/
		'subject' => 'Topic reply: {TOPICSUBJECT}',
		'body' => 'A reply has been posted to a topic you are watching by {POSTERNAME}.

View the reply at: {TOPICLINK}

Unsubscribe to this topic by using this link: {UNSUBSCRIBELINK}

More replies may be posted, but you won\'t receive any more notifications until you read the topic.

{REGARDS}',
	),
	'notification_reply_body_once' => array(
		/*
			@additional_params:
				TOPICSUBJECT:
				POSTERNAME:
				TOPICLINK:
				UNSUBSCRIBELINK:
				MESSAGE: 
			@description:
		*/
		'subject' => 'Topic reply: {TOPICSUBJECT}',
		'body' => 'A reply has been posted to a topic you are watching by {POSTERNAME}.

View the reply at: {TOPICLINK}

Unsubscribe to this topic by using this link: {UNSUBSCRIBELINK}

The text of the reply is shown below:
{MESSAGE}

More replies may be posted, but you won\'t receive any more notifications until you read the topic.

{REGARDS}',
	),
	'notification_sticky' => array(
		/*
			@additional_params:
			@description:
		*/
		'subject' => 'Topic stickied: {TOPICSUBJECT}',
		'body' => 'A topic you are watching has been marked as a sticky topic by {POSTERNAME}.

View the topic at: {TOPICLINK}

Unsubscribe to this topic by using this link: {UNSUBSCRIBELINK}

{REGARDS}',
	),
	'notification_lock' => array(
		/*
			@additional_params:
			@description:
		*/
		'subject' => 'Topic locked: {TOPICSUBJECT}',
		'body' => 'A topic you are watching has been locked by {POSTERNAME}.

View the topic at: {TOPICLINK}

Unsubscribe to this topic by using this link: {UNSUBSCRIBELINK}

{REGARDS}',
	),
	'notification_unlock' => array(
		/*
			@additional_params:
			@description:
		*/
		'subject' => 'Topic unlocked: {TOPICSUBJECT}',
		'body' => 'A topic you are watching has been unlocked by {POSTERNAME}.

View the topic at: {TOPICLINK}

Unsubscribe to this topic by using this link: {UNSUBSCRIBELINK}

{REGARDS}',
	),
	'notification_remove' => array(
		/*
			@additional_params:
			@description:
		*/
		'subject' => 'Topic removed: {TOPICSUBJECT}',
		'body' => 'A topic you are watching has been removed by {POSTERNAME}.

{REGARDS}',
	),
	'notification_move' => array(
		/*
			@additional_params:
			@description:
		*/
		'subject' => 'Topic moved: {TOPICSUBJECT}',
		'body' => 'A topic you are watching has been moved to another board by {POSTERNAME}.

View the topic at: {TOPICLINK}

Unsubscribe to this topic by using this link: {UNSUBSCRIBELINK}

{REGARDS}',
	),
	'notification_merged' => array(
		/*
			@additional_params:
			@description:
		*/
		'subject' => 'Topic merged: {TOPICSUBJECT}',
		'body' => 'A topic you are watching has been merged with another topic by {POSTERNAME}.

View the new merged topic at: {TOPICLINK}

Unsubscribe to this topic by using this link: {UNSUBSCRIBELINK}

{REGARDS}',
	),
	'notification_split' => array(
		/*
			@additional_params:
			@description:
		*/
		'subject' => 'Topic split: {TOPICSUBJECT}',
		'body' => 'A topic you are watching has been split into two or more topics by {POSTERNAME}.

View what remains of this topic at: {TOPICLINK}

Unsubscribe to this topic by using this link: {UNSUBSCRIBELINK}

{REGARDS}',
	),
	'admin_notify' => array(
		/*
			@additional_params:
				USERNAME: 
				PROFILELINK: 
			@description:
		*/
		'subject' => 'A new member has joined',
		'body' => '{USERNAME} has just signed up as a new member of your forum. Click the link below to view their profile.
{PROFILELINK}

{REGARDS}',
	),
	'admin_notify_approval' => array(
		/*
			@additional_params:
				USERNAME: 
				PROFILELINK: 
				APPROVALLINK: 
			@description:
		*/
		'subject' => 'A new member has joined',
		'body' => '{USERNAME} has just signed up as a new member of your forum. Click the link below to view their profile.
{PROFILELINK}

Before this member can begin posting they must first have their account approved. Click the link below to go to the approval screen.
{APPROVALLINK}

{REGARDS}',
	),
);
?>