<?php

	class UnitTest_tidyhtml extends UnitTest
	{
		protected $_tidyPath;
		
		protected $_tests = array();
		
		protected $_ignoreErrors = array(
			'~Warning: trimming empty <[^>]+>~',
		);
		
		protected $_id_board;
		protected $_id_topic;
		protected $_id_topic2;
		protected $_id_msg;
		protected $_id_msg2;
		protected $_id_msg3;
		protected $_id_member;
		protected $_id_cat;
		
		public function __construct()
		{
			global $boarddir, $scripturl;
			
			$this->_tidyPath = $boarddir . '/other/unittest/validation/tidy.exe';
			$this->_openSpPath = $boarddir . '/other/unittest/validation/onsgmls.exe';
			
			$this->_id_board = $this->_getUnitTestBoardId();
			
			$this->_id_member = $this->_getUnitTestMemberId('admin');
			
			list($this->_id_msg, $this->_id_topic) = $this->_getUnitTestTopic($this->_id_board, $this->_id_member, 'Testing HTML tidy', 'This topic will is only there to check if the display page is properly xHTML compatible.');
			$this->_createReply($this->_id_board, $this->_id_topic, $this->_id_member, 'HTML tidy reply', 'A reply to the topic');
			
			list($this->_id_msg3, $this->_id_topic2) = $this->_getUnitTestTopic($this->_id_board, $this->_id_member, 'Testing HTML tidy - merge topic', 'This topic is needed to test the second step of a topic merge.');
			$this->_id_cat = $this->_getUnitTestCatId();


			$this->_tests = array(
				'Admin_1' => array(
					'name' => 'Admin (1)',
					'description' => "Main admin center",
					'url' => $scripturl . '?action=admin',
				),
				'Admin_2' => array(
					'name' => 'Admin (2)',
					'description' => "Copyright removal",
					'url' => $scripturl . '?action=admin;area=copyright',
				),
				'Admin_3' => array(
					'name' => 'Admin (3)',
					'description' => "Search in admin center",
					'url' => $scripturl . '?action=admin;area=search;search_term=template',
				),
				'Admin_4' => array(
					'name' => 'Admin (4)',
					'description' => "Error logs",
					'url' => $scripturl . '?action=admin;area=logs;sa=errorlog',
				),
				'Admin_5' => array(
					'name' => 'Admin (5)',
					'description' => "Admin logs",
					'url' => $scripturl . '?action=admin;area=logs;sa=adminlog',
				),
				'Admin_6' => array(
					'name' => 'Admin (6)',
					'description' => "Mod logs",
					'url' => $scripturl . '?action=admin;area=logs;sa=modlog',
				),
				'Admin_7' => array(
					'name' => 'Admin (7)',
					'description' => "Ban logs",
					'url' => $scripturl . '?action=admin;area=logs;sa=banlog',
				),
				'Admin_8' => array(
					'name' => 'Admin (8)',
					'description' => "Spider logs",
					'url' => $scripturl . '?action=admin;area=logs;sa=spiderlog',
				),
				'Admin_9' => array(
					'name' => 'Admin (9)',
					'description' => "Task logs",
					'url' => $scripturl . '?action=admin;area=logs;sa=tasklog',
				),
				'BoardIndex_1' => array(
					'name' => 'Board index (1)',
					'description' => "Board index",
					'url' => $scripturl,
				),
				'Calendar_1' => array(
					'name' => 'Calendar (1)',
					'description' => "Calendar main",
					'url' => $scripturl . '?action=calendar',
				),
				'Calendar_2' => array(
					'name' => 'Calendar (2)',
					'description' => "Calendar view week",
					'url' => $scripturl . '?action=calendar;sa=viewweek',
				),
				'Calendar_3' => array(
					'name' => 'Calendar (3)',
					'description' => "Calendar post event",
					'url' => $scripturl . '?action=calendar;sa=post',
				),
				'Display_1' => array(
					'name' => 'Display (1)',
					'description' => "Simple display screen",
					'url' => $scripturl . '?topic=' . $this->_id_topic . '.0',
				),
				'Groups_1' => array(
					'name' => 'Moderation center - Groups (1)',
					'description' => "Group overview",
					'url' => $scripturl . '?action=moderate;area=groups;sa=index',
				),
				'Groups_2' => array(
					'name' => 'Moderation center - Groups (2)',
					'description' => "Members within a group",
					'url' => $scripturl . '?action=moderate;area=groups;sa=members;group=1',
				),
				'Groups_3' => array(
					'name' => 'Moderation center - Groups (3)',
					'description' => "Group requests",
					'url' => $scripturl . '?action=moderate;area=groups;sa=requests',
				),
				'Login_1' => array(
					'name' => 'Login (1)',
					'description' => "Login screen",
					'url' => $scripturl . '?action=login',
					'id_member' => 0,
				),
				'ManageAttachments_1' => array(
					'name' => 'Manage attachments (1)',
					'description' => "Attachment list",
					'url' => $scripturl . '?action=admin;area=manageattachments',
				),
				'ManageAttachments_2' => array(
					'name' => 'Manage attachments (2)',
					'description' => "Browse avatars",
					'url' => $scripturl . '?action=admin;area=manageattachments;sa=browse;avatars',
				),
				'ManageAttachments_3' => array(
					'name' => 'Manage attachments (3)',
					'description' => "Browse thumbs",
					'url' => $scripturl . '?action=admin;area=manageattachments;sa=browse;thumbs',
				),
				'ManageAttachments_4' => array(
					'name' => 'Manage attachments (4)',
					'description' => "Attachment settings",
					'url' => $scripturl . '?action=admin;area=manageattachments;sa=attachments',
				),
				'ManageAttachments_5' => array(
					'name' => 'Manage attachments (5)',
					'description' => "Avatar settings",
					'url' => $scripturl . '?action=admin;area=manageattachments;sa=avatars',
				),
				'ManageAttachments_6' => array(
					'name' => 'Manage attachments (6)',
					'description' => "File maintenance",
					'url' => $scripturl . '?action=admin;area=manageattachments;sa=maintenance',
				),
				'ManageAttachments_7' => array(
					'name' => 'Manage attachments (7)',
					'description' => "Configure multiple upload paths",
					'url' => $scripturl . '?action=admin;area=manageattachments;sa=attachpaths',
				),
				'ManageBans_1' => array(
					'name' => 'Manage bans (1)',
					'description' => "Ban list",
					'url' => $scripturl . '?action=admin;area=ban',
				),
				'ManageBans_2' => array(
					'name' => 'Manage bans (2)',
					'description' => "Add ban",
					'url' => $scripturl . '?action=admin;area=ban;sa=add',
				),
				'ManageBans_3' => array(
					'name' => 'Manage bans (3)',
					'description' => "Ban trigger list",
					'url' => $scripturl . '?action=admin;area=ban;sa=browse',
				),
				'ManageBans_4' => array(
					'name' => 'Manage bans (4)',
					'description' => "Ban log",
					'url' => $scripturl . '?action=admin;area=ban;sa=log',
				),
				'ManageBoards_1' => array(
					'name' => 'Manage boards (1)',
					'description' => "Board overview",
					'url' => $scripturl . '?action=admin;area=manageboards',
				),
				'ManageBoards_2' => array(
					'name' => 'Manage boards (2)',
					'description' => "Edit category",
					'url' => $scripturl . '?action=admin;area=manageboards;sa=cat;cat=' . $this->_id_cat,
				),
				'ManageBoards_3' => array(
					'name' => 'Manage boards (3)',
					'description' => "Add category",
					'url' => $scripturl . '?action=admin;area=manageboards;sa=newcat',
				),
				'ManageBoards_4' => array(
					'name' => 'Manage boards (4)',
					'description' => "Board overview - move board",
					'url' => $scripturl . '?action=admin;area=manageboards;move=' . $this->_id_board . '.0',
				),
				'ManageBoards_5' => array(
					'name' => 'Manage boards (5)',
					'description' => "Edit board",
					'url' => $scripturl . '?action=admin;area=manageboards;sa=board;boardid=' . $this->_id_board . '.0',
				),
				'ManageBoards_6' => array(
					'name' => 'Manage boards (6)',
					'description' => "Add board",
					'url' => $scripturl . '?action=admin;area=manageboards;sa=newboard;cat=' . $this->_id_cat . '.0',
				),
				'ManageCalendar_1' => array(
					'name' => 'Manage calendar (1)',
					'description' => "Holiday overview",
					'url' => $scripturl . '?action=admin;area=managecalendar',
				),
				'ManageCalendar_2' => array(
					'name' => 'Manage calendar (2)',
					'description' => "Add holiday",
					'url' => $scripturl . '?action=admin;area=managecalendar;sa=editholiday',
				),
				'ManageCalendar_3' => array(
					'name' => 'Manage calendar (3)',
					'description' => "Calendar settings",
					'url' => $scripturl . '?action=admin;area=managecalendar;sa=settings',
				),
				'ManageMail_1' => array(
					'name' => 'Manage mail (1)',
					'description' => "Mail queue",
					'url' => $scripturl . '?action=admin;area=mailqueue',
				),
				'ManageMail_2' => array(
					'name' => 'Manage mail (2)',
					'description' => "Mail settings",
					'url' => $scripturl . '?action=admin;area=mailqueue;sa=settings',
				),
				'ManageMaintenance_1' => array(
					'name' => 'Manage maintenance (1)',
					'description' => "Maintenance overview",
					'url' => $scripturl . '?action=admin;area=maintain',
				),
				'ManageMaintenance_2' => array(
					'name' => 'Manage maintenance (2)',
					'description' => "Optimize tables",
					'url' => $scripturl . '?action=admin;area=maintain;sa=optimize',
				),
				'ManageMaintenance_3' => array(
					'name' => 'Manage maintenance (3)',
					'description' => "Check version",
					'url' => $scripturl . '?action=admin;area=maintain;sa=version',
				),
				'ManageMaintenance_4' => array(
					'name' => 'Manage maintenance (4)',
					'description' => "Remove inactive members",
					'url' => $scripturl . '?action=admin;area=maintain;sa=admintask;activity=maintain_members',
				),
				'ManageMaintenance_5' => array(
					'name' => 'Manage maintenance (5)',
					'description' => "Reattribute posts",
					'url' => $scripturl . '?action=admin;area=maintain;sa=admintask;activity=maintain_reattribute_posts',
				),
				'ManageMaintenance_6' => array(
					'name' => 'Manage maintenance (6)',
					'description' => "Reattribute posts",
					'url' => $scripturl . '?action=admin;area=maintain;sa=admintask;activity=maintain_old',
				),
				'ManageMaintenance_7' => array(
					'name' => 'Manage maintenance (7)',
					'description' => "Move topics",
					'url' => $scripturl . '?action=admin;area=maintain;sa=admintask;activity=move_topics_maintenance',
				),
				'ManageMaintenance_8' => array(
					'name' => 'Manage maintenance (8)',
					'description' => "Convert to UTF-8",
					'url' => $scripturl . '?action=admin;area=maintain;sa=convertutf8',
				),
				'ManageMembergroups_1' => array(
					'name' => 'Manage membergroups (1)',
					'description' => "Membergroup overview",
					'url' => $scripturl . '?action=admin;area=membergroups',
				),
				'ManageMembergroups_2' => array(
					'name' => 'Manage membergroups (2)',
					'description' => "Membergroup members overview",
					'url' => $scripturl . '?action=admin;area=membergroups;sa=members;group=1',
				),
				'ManageMembergroups_3' => array(
					'name' => 'Manage membergroups (3)',
					'description' => "Edit membergroup admin",
					'url' => $scripturl . '?action=admin;area=membergroups;sa=edit;group=1',
				),
				'ManageMembergroups_4' => array(
					'name' => 'Manage membergroups (4)',
					'description' => "Edit post count based membergroup",
					'url' => $scripturl . '?action=admin;area=membergroups;sa=edit;group=4',
				),
				'ManageMembergroups_5' => array(
					'name' => 'Manage membergroups (5)',
					'description' => "Add membergroup",
					'url' => $scripturl . '?action=admin;area=membergroups;sa=add',
				),
				'ManageMembergroups_6' => array(
					'name' => 'Manage membergroups (6)',
					'description' => "Membergroup settings",
					'url' => $scripturl . '?action=admin;area=membergroups;sa=settings',
				),
				'ManageMembers_1' => array(
					'name' => 'Manage members (1)',
					'description' => "View members",
					'url' => $scripturl . '?action=admin;area=viewmembers',
				),
				'ManageMembers_2' => array(
					'name' => 'Manage members (2)',
					'description' => "Search for members",
					'url' => $scripturl . '?action=admin;area=viewmembers;sa=search',
				),
				'ManageMembers_3' => array(
					'name' => 'Manage members (3)',
					'description' => "Show duplicates in members awaiting activation",
					'url' => $scripturl . '?action=admin;area=viewmembers;sa=browse;showdupes=1;type=activate',
				),
				'ManageNews_1' => array(
					'name' => 'Manage news (1)',
					'description' => "Show news items",
					'url' => $scripturl . '?action=admin;area=news',
				),
				'ManageNews_2' => array(
					'name' => 'Manage news (2)',
					'description' => "Compose a mailing",
					'url' => $scripturl . '?action=admin;area=news;sa=mailingmembers',
				),
				'ManageNews_3' => array(
					'name' => 'Manage news (3)',
					'description' => "News settings",
					'url' => $scripturl . '?action=admin;area=news;sa=settings',
				),
				'ManagePaid_1' => array(
					'name' => 'Manage paid subscriptions (1)',
					'description' => "Subscription settings",
					'url' => $scripturl . '?action=admin;area=paidsubscribe',
				),
				'ManagePaid_2' => array(
					'name' => 'Manage paid subscriptions (2)',
					'description' => "Subscription settings",
					'url' => $scripturl . '?action=admin;area=paidsubscribe;sa=view',
				),
				'ManagePaid_3' => array(
					'name' => 'Manage paid subscriptions (3)',
					'description' => "View subscriptions",
					'url' => $scripturl . '?action=admin;area=paidsubscribe;sa=modify',
				),
				'ManagePaid_4' => array(
					'name' => 'Manage paid subscriptions (4)',
					'description' => "View subscriptions",
					'url' => $scripturl . '?action=admin;area=paidsubscribe;sa=viewsub;sid=1',
				),
				'ManagePermissions_1' => array(
					'name' => 'Manage permissions (1)',
					'description' => "General permission overview",
					'url' => $scripturl . '?action=admin;area=permissions',
				),
				'ManagePermissions_2' => array(
					'name' => 'Manage permissions (2)',
					'description' => "General permission settings for guests [simple]",
					'url' => $scripturl . '?action=admin;area=permissions;sa=modify;group=-1;view=simple',
				),
				'ManagePermissions_3' => array(
					'name' => 'Manage permissions (3)',
					'description' => "General permission settings for guests [classic]",
					'url' => $scripturl . '?action=admin;area=permissions;sa=modify;group=-1;view=classic',
				),
				'ManagePermissions_4' => array(
					'name' => 'Manage permissions (4)',
					'description' => "Board permission overview",
					'url' => $scripturl . '?action=admin;area=permissions;sa=board',
				),
				'ManagePermissions_5' => array(
					'name' => 'Manage permissions (5)',
					'description' => "Board permission overview (edit all)",
					'url' => $scripturl . '?action=admin;area=permissions;sa=board;edit',
				),
				'ManagePermissions_6' => array(
					'name' => 'Manage permissions (6)',
					'description' => "Edit profiles",
					'url' => $scripturl . '?action=admin;area=permissions;sa=profiles',
				),
				'ManagePermissions_7' => array(
					'name' => 'Manage permissions (7)',
					'description' => "Edit profile 'default'",
					'url' => $scripturl . '?action=admin;area=permissions;sa=index;pid=1',
				),
				'ManagePermissions_8' => array(
					'name' => 'Manage permissions (8)',
					'description' => "Post moderation",
					'url' => $scripturl . '?action=admin;area=permissions;sa=postmod',
				),
				'ManagePermissions_9' => array(
					'name' => 'Manage permissions (9)',
					'description' => "Permission settings",
					'url' => $scripturl . '?action=admin;area=permissions;sa=settings',
				),
				'ManagePosts_1' => array(
					'name' => 'Manage posts and topics (1)',
					'description' => "Post settings",
					'url' => $scripturl . '?action=admin;area=postsettings',
				),
				'ManagePosts_2' => array(
					'name' => 'Manage posts and topics (2)',
					'description' => " 	 Bulletin Board Code   	",
					'url' => $scripturl . '?action=admin;area=postsettings;sa=bbc',
				),
				'ManagePosts_3' => array(
					'name' => 'Manage posts and topics (3)',
					'description' => "Censored Words",
					'url' => $scripturl . '?action=admin;area=postsettings;sa=censor',
				),
				'ManagePosts_4' => array(
					'name' => 'Manage posts and topics (4)',
					'description' => "Topic settings",
					'url' => $scripturl . '?action=admin;area=postsettings;sa=topics',
				),
				'ManageRegistration_1' => array(
					'name' => 'Manage registration (1)',
					'description' => "Register a new member",
					'url' => $scripturl . '?action=admin;area=regcenter;sa=register',
				),
				'ManageRegistration_2' => array(
					'name' => 'Manage registration (2)',
					'description' => "Registration Agreement",
					'url' => $scripturl . '?action=admin;area=regcenter;sa=agreement',
				),
				'ManageRegistration_3' => array(
					'name' => 'Manage registration (3)',
					'description' => "Set Reserved Names",
					'url' => $scripturl . '?action=admin;area=regcenter;sa=reservednames',
				),
				'ManageRegistration_4' => array(
					'name' => 'Manage registration (4)',
					'description' => "Settings",
					'url' => $scripturl . '?action=admin;area=regcenter;sa=settings',
				),
				'ManageSearch_1' => array(
					'name' => 'Manage search (1)',
					'description' => "Search weights",
					'url' => $scripturl . '?action=admin;area=managesearch;sa=weights',
				),
				'ManageSearch_2' => array(
					'name' => 'Manage search (2)',
					'description' => "Search sethod",
					'url' => $scripturl . '?action=admin;area=managesearch;sa=method',
				),
				'ManageSearch_3' => array(
					'name' => 'Manage search (3)',
					'description' => "Search settings",
					'url' => $scripturl . '?action=admin;area=managesearch;sa=settings',
				),
				'ManageSearchEngines_1' => array(
					'name' => 'Manage search engines (1)',
					'description' => "Search engine stats",
					'url' => $scripturl . '?action=admin;area=sengines;sa=stats',
				),
				'ManageSearchEngines_2' => array(
					'name' => 'Manage search engines (2)',
					'description' => "Spider Log",
					'url' => $scripturl . '?action=admin;area=sengines;sa=logs',
				),
				'ManageSearchEngines_3' => array(
					'name' => 'Manage search engines (3)',
					'description' => "Spiders",
					'url' => $scripturl . '?action=admin;area=sengines;sa=spiders',
				),
				'ManageSearchEngines_4' => array(
					'name' => 'Manage search engines (4)',
					'description' => "Settings",
					'url' => $scripturl . '?action=admin;area=sengines;sa=settings',
				),
				'ManageServer_1' => array(
					'name' => 'Manage server settings (1)',
					'description' => "Core configuration",
					'url' => $scripturl . '?action=admin;area=serversettings;sa=core',
				),
				'ManageServer_2' => array(
					'name' => 'Manage server settings (2)',
					'description' => "Feature Configuration   	",
					'url' => $scripturl . '?action=admin;area=serversettings;sa=other',
				),
				'ManageServer_3' => array(
					'name' => 'Manage server settings (3)',
					'description' => "Languages",
					'url' => $scripturl . '?action=admin;area=serversettings;sa=languages',
				),
				'ManageServer_4' => array(
					'name' => 'Manage server settings (4)',
					'description' => "Caching",
					'url' => $scripturl . '?action=admin;area=serversettings;sa=cache',
				),
				'ManageSettings_1' => array(
					'name' => 'Manage settings (1)',
					'description' => "Core features",
					'url' => $scripturl . '?action=admin;area=featuresettings;sa=core',
				),
				'ManageSettings_2' => array(
					'name' => 'Manage settings (2)',
					'description' => "Options",
					'url' => $scripturl . '?action=admin;area=featuresettings;sa=basic',
				),
				'ManageSettings_3' => array(
					'name' => 'Manage settings (3)',
					'description' => "Layout",
					'url' => $scripturl . '?action=admin;area=featuresettings;sa=layout',
				),
				'ManageSettings_4' => array(
					'name' => 'Manage settings (4)',
					'description' => "Karma",
					'url' => $scripturl . '?action=admin;area=featuresettings;sa=karma',
				),
				'ManageSettings_5' => array(
					'name' => 'Manage settings (5)',
					'description' => "Signatures",
					'url' => $scripturl . '?action=admin;area=featuresettings;sa=sig',
				),
				'ManageSettings_6' => array(
					'name' => 'Manage settings (6)',
					'description' => "Profile Fields",
					'url' => $scripturl . '?action=admin;area=featuresettings;sa=profile',
				),
				'ManageSettings_7' => array(
					'name' => 'Manage settings (7)',
					'description' => "Log Pruning",
					'url' => $scripturl . '?action=admin;area=featuresettings;sa=pruning',
				),
				'ManageSettings_8' => array(
					'name' => 'Manage settings (8)',
					'description' => "Core features",
					'url' => $scripturl . '?action=admin;area=securitysettings;sa=general',
				),
				'ManageSettings_9' => array(
					'name' => 'Manage settings (9)',
					'description' => "Core features",
					'url' => $scripturl . '?action=admin;area=securitysettings;sa=spam',
				),
				'ManageSettings_10' => array(
					'name' => 'Manage settings (10)',
					'description' => "Core features",
					'url' => $scripturl . '?action=admin;area=securitysettings;sa=moderation',
				),
				'ManageSmileys_1' => array(
					'name' => 'Manage smileys (1)',
					'description' => "Smiley Sets",
					'url' => $scripturl . '?action=admin;area=smileys;sa=editsets',
				),
				'ManageSmileys_2' => array(
					'name' => 'Manage smileys (2)',
					'description' => "Edit smiley set",
					'url' => $scripturl . '?action=admin;area=smileys;sa=modifyset;set=0',
				),
				'ManageSmileys_3' => array(
					'name' => 'Manage smileys (3)',
					'description' => "Add smiley set",
					'url' => $scripturl . '?action=admin;area=smileys;sa=modifyset',
				),
				'ManageSmileys_4' => array(
					'name' => 'Manage smileys (4)',
					'description' => "Add smiley",
					'url' => $scripturl . '?action=admin;area=smileys;sa=addsmiley',
				),
				'ManageSmileys_5' => array(
					'name' => 'Manage smileys (5)',
					'description' => "Smiley overview",
					'url' => $scripturl . '?action=admin;area=smileys;sa=editsmileys',
				),
				'ManageSmileys_6' => array(
					'name' => 'Manage smileys (6)',
					'description' => "Edit smiley",
					'url' => $scripturl . '?action=admin;area=smileys;sa=modifysmiley;smiley=1',
				),
				'ManageSmileys_7' => array(
					'name' => 'Manage smileys (7)',
					'description' => "Set smiley order",
					'url' => $scripturl . '?action=admin;area=smileys;sa=setorder',
				),
				'ManageSmileys_8' => array(
					'name' => 'Manage smileys (8)',
					'description' => "Set smiley order (move smiley)",
					'url' => $scripturl . '?action=admin;area=smileys;sa=setorder;move=1',
				),
				'ManageSmileys_9' => array(
					'name' => 'Manage smileys (9)',
					'description' => "Message icon overview",
					'url' => $scripturl . '?action=admin;area=smileys;sa=editicons',
				),
				'ManageSmileys_10' => array(
					'name' => 'Manage smileys (10)',
					'description' => "Add message icon",
					'url' => $scripturl . '?action=admin;area=smileys;sa=editicon',
				),
				'ManageSmileys_11' => array(
					'name' => 'Manage smileys (11)',
					'description' => "Edit message icon",
					'url' => $scripturl . '?action=admin;area=smileys;sa=editicon;icon=1',
				),
				'ManageSmileys_12' => array(
					'name' => 'Manage smileys (12)',
					'description' => "Message icon settings",
					'url' => $scripturl . '?action=admin;area=smileys;sa=settings',
				),
				'Memberlist_1' => array(
					'name' => 'Memberlist (1)',
					'description' => "Memberlist",
					'url' => $scripturl . '?action=mlist',
				),
				'Memberlist_2' => array(
					'name' => 'Memberlist (2)',
					'description' => "Memberlist search",
					'url' => $scripturl . '?action=mlist;sa=search',
				),
				'MessageIndex_1' => array(
					'name' => 'Message index (1)',
					'description' => "Message index as an admin",
					'url' => $scripturl . '?board=' . $this->_id_board . '.0',
				),
				'ModerationCenter_1' => array(
					'name' => 'Moderation center (1)',
					'description' => "Moderation center index",
					'url' => $scripturl . '?action=moderate',
				),
				'ModerationCenter_2' => array(
					'name' => 'Moderation center (2)',
					'description' => "Moderation center active reports",
					'url' => $scripturl . '?action=moderate;area=reports',
				),
				'ModerationCenter_3' => array(
					'name' => 'Moderation center (3)',
					'description' => "Show moderation notic",
					'url' => $scripturl . '?action=moderate;area=notice;nid=1',
				),
				'ModerationCenter_4' => array(
					'name' => 'Moderation center (4)',
					'description' => "View watched users by member",
					'url' => $scripturl . '?action=moderate;area=userwatch;sa=member',
				),
				'ModerationCenter_5' => array(
					'name' => 'Moderation center (5)',
					'description' => "View watched users by post",
					'url' => $scripturl . '?action=moderate;area=userwatch;sa=post',
				),
				'ModerationCenter_6' => array(
					'name' => 'Moderation center (6)',
					'description' => "View warnings",
					'url' => $scripturl . '?action=moderate;area=warnings;sa=log',
				),
				'ModerationCenter_7' => array(
					'name' => 'Moderation center (7)',
					'description' => "View customer templates",
					'url' => $scripturl . '?action=moderate;area=warnings;sa=templateedit',
				),
				'ModerationCenter_8' => array(
					'name' => 'Moderation center (8)',
					'description' => "Add template",
					'url' => $scripturl . '?action=moderate;area=warnings;sa=templates',
				),
				'ModerationCenter_9' => array(
					'name' => 'Moderation center (9)',
					'description' => "Moderation center settings",
					'url' => $scripturl . '?action=moderate;area=settings',
				),
				'MoveTopic_1' => array(
					'name' => 'Move topic (1)',
					'description' => "Move a topic",
					'url' => $scripturl . '?action=movetopic;topic=' . $this->_id_topic . '.0',
				),
				'Notify_1' => array(
					'name' => 'Notify (1)',
					'description' => "Confirmation of topic notification",
					'url' => $scripturl . '?action=notify;topic=' . $this->_id_topic . '.0',
				),
				'Notify_2' => array(
					'name' => 'Notify (2)',
					'description' => "Confirmation of board notification",
					'url' => $scripturl . '?action=notifyboard;board=' . $this->_id_board . '.0',
				),
				'PackageGet_1' => array(
					'name' => 'Package center (1)',
					'description' => "Browse packages",
					'url' => $scripturl . '?action=admin;area=packages;sa=browse',
				),
				'PackageGet_2' => array(
					'name' => 'Package center (2)',
					'description' => "Download packages",
					'url' => $scripturl . '?action=admin;area=packages;sa=packageget;get',
				),
				'PackageGet_3' => array(
					'name' => 'Package center (3)',
					'description' => "Browse server",
					'url' => $scripturl . '?action=admin;area=packages;sa=browse;server=1',
				),
				'PackageGet_4' => array(
					'name' => 'Package center (4)',
					'description' => "Installed packages",
					'url' => $scripturl . '?action=admin;area=packages;sa=installed',
				),
				'PackageGet_5' => array(
					'name' => 'Package center (5)',
					'description' => "File permissions",
					'url' => $scripturl . '?action=admin;area=packages;sa=perms',
				),
				'PackageGet_6' => array(
					'name' => 'Package center (6)',
					'description' => "Options",
					'url' => $scripturl . '?action=admin;area=packages;sa=options',
				),
				'PersonalMessages_1' => array(
					'name' => 'Personal messages (1)',
					'description' => "Inbox",
					'url' => $scripturl . '?action=pm',
				),
				'PersonalMessages_2' => array(
					'name' => 'Personal messages (2)',
					'description' => "Sent items",
					'url' => $scripturl . '?action=pm;f=sent',
				),
				'PersonalMessages_3' => array(
					'name' => 'Personal messages (3)',
					'description' => "Send new message",
					'url' => $scripturl . '?action=pm;sa=send',
				),
				'PersonalMessages_4' => array(
					'name' => 'Personal messages (4)',
					'description' => "Search messages",
					'url' => $scripturl . '?action=pm;sa=search',
				),
				'PersonalMessages_5' => array(
					'name' => 'Personal messages (5)',
					'description' => "Advanced search",
					'url' => $scripturl . '?action=pm;sa=search;advanced',
				),
				'PersonalMessages_6' => array(
					'name' => 'Personal messages (6)',
					'description' => "Prune messages",
					'url' => $scripturl . '?action=pm;sa=prune',
				),
				'PersonalMessages_7' => array(
					'name' => 'Personal messages (7)',
					'description' => "Manage labels",
					'url' => $scripturl . '?action=pm;sa=manlabels',
				),
				'PersonalMessages_8' => array(
					'name' => 'Personal messages (8)',
					'description' => "Manage rules",
					'url' => $scripturl . '?action=pm',
				),
				'PersonalMessages_9' => array(
					'name' => 'Personal messages (9)',
					'description' => "Add rule",
					'url' => $scripturl . '?action=pm;sa=manrules;add;rid=0',
				),
				'PersonalMessages_10' => array(
					'name' => 'Personal messages (10)',
					'description' => "Change settings",
					'url' => $scripturl . '?action=pm;sa=settings',
				),
				'Post_1' => array(
					'name' => 'Post (1)',
					'description' => "Post new topic",
					'url' => $scripturl . '?action=post;board=' . $this->_id_board . '.0',
				),
				'Post_2' => array(
					'name' => 'Post (2)',
					'description' => "Post new poll",
					'url' => $scripturl . '?action=post;board=' . $this->_id_board . '.0;poll',
				),
				'Post_3' => array(
					'name' => 'Post (3)',
					'description' => "Post new reply",
					'url' => $scripturl . '?action=post;topic=' . $this->_id_topic . '.0',
				),
				'Post_4' => array(
					'name' => 'Post (4)',
					'description' => "Announce topic",
					'url' => $scripturl . '?action=announce;sa=selectgroup;topic=' . $this->_id_topic . '.0',
				),
				'PostModeration_1' => array(
					'name' => 'Post moderation (1)',
					'description' => "Unapproved replies",
					'url' => $scripturl . '?action=moderate;area=postmod;sa=post',
				),
				'PostModeration_2' => array(
					'name' => 'Post moderation (2)',
					'description' => "Unapproved topics",
					'url' => $scripturl . '?action=moderate;area=postmod;sa=topics',
				),
				'PostModeration_3' => array(
					'name' => 'Post moderation (3)',
					'description' => "Unapproved attachments",
					'url' => $scripturl . '?action=moderate;area=postmod;sa=attachments',
				),
				'Printpage_1' => array(
					'name' => 'Print page (1)',
					'description' => "Print page",
					'url' => $scripturl . '?action=printpage;topic=' . $this->_id_topic . '.0',
				),
				'Profile_1' => array(
					'name' => 'Profile (1)',
					'description' => "Profile summary",
					'url' => $scripturl . '?action=profile;area=summary',
				),
				'Profile_2' => array(
					'name' => 'Profile (2)',
					'description' => "Show stats",
					'url' => $scripturl . '?action=profile;area=statistics',
				),
				'Profile_3' => array(
					'name' => 'Profile (3)',
					'description' => "Show posts",
					'url' => $scripturl . '?action=profile;area=showposts;sa=messages',
				),
				'Profile_4' => array(
					'name' => 'Profile (4)',
					'description' => "Show topics",
					'url' => $scripturl . '?action=profile;area=showposts;sa=topics',
				),
				'Profile_5' => array(
					'name' => 'Profile (5)',
					'description' => "Show attachments",
					'url' => $scripturl . '?action=profile;area=showposts;sa=attach',
				),
				'Profile_6' => array(
					'name' => 'Profile (6)',
					'description' => "Show permissions",
					'url' => $scripturl . '?action=profile;area=permissions',
				),
				'Profile_7' => array(
					'name' => 'Profile (7)',
					'description' => "Track user",
					'url' => $scripturl . '?action=profile;area=tracking;sa=user',
				),
				'Profile_8' => array(
					'name' => 'Profile (8)',
					'description' => "Track IP",
					'url' => $scripturl . '?action=profile;area=tracking;sa=ip',
				),
				'Profile_9' => array(
					'name' => 'Profile (9)',
					'description' => "Track edits",
					'url' => $scripturl . '?action=profile;area=tracking;sa=edits',
				),
				'Profile_10' => array(
					'name' => 'Profile (10)',
					'description' => "Account settings",
					'url' => $scripturl . '?action=profile;area=account',
				),
				'Profile_11' => array(
					'name' => 'Profile (11)',
					'description' => "Profile settings",
					'url' => $scripturl . '?action=profile;area=forumprofile',
				),
				'Profile_12' => array(
					'name' => 'Profile (12)',
					'description' => "Theme settings",
					'url' => $scripturl . '?action=profile;area=theme',
				),
				'Profile_13' => array(
					'name' => 'Profile (13)',
					'description' => "Notification settings",
					'url' => $scripturl . '?action=profile;area=notification',
				),
				'Profile_14' => array(
					'name' => 'Profile (14)',
					'description' => "Personal message preferences",
					'url' => $scripturl . '?action=profile;area=pmprefs',
				),
				'Profile_15' => array(
					'name' => 'Profile (15)',
					'description' => "Ignore boards",
					'url' => $scripturl . '?action=profile;area=ignoreboards',
				),
				'Profile_16' => array(
					'name' => 'Profile (16)',
					'description' => "Edit buddies",
					'url' => $scripturl . '?action=profile;area=buddies',
				),
				'Profile_17' => array(
					'name' => 'Profile (17)',
					'description' => "Subscriptions",
					'url' => $scripturl . '?action=profile;area=subscriptions',
				),
				'Profile_18' => array(
					'name' => 'Profile (18)',
					'description' => "Delete account",
					'url' => $scripturl . '?action=profile;area=deleteaccount',
				),
				'Recent_1' => array(
					'name' => 'Recent posts (1)',
					'description' => "Recent posts",
					'url' => $scripturl . '?action=recent',
				),
				'Recent_2' => array(
					'name' => 'Recent posts (2)',
					'description' => "Unread posts",
					'url' => $scripturl . '?action=unread',
				),
				'Recent_3' => array(
					'name' => 'Recent posts (3)',
					'description' => "Unread replies",
					'url' => $scripturl . '?action=unreadreplies',
				),
				'Register_1' => array(
					'name' => 'Register (1)',
					'description' => "Register account",
					'url' => $scripturl . '?action=register',
					'id_member' => 0,
				),
				'Reminder_1' => array(
					'name' => 'Reminder (1)',
					'description' => "Authentication reminder",
					'url' => $scripturl . '?action=reminder',
					'id_member' => 0,
				),
				'RepairBoards_1' => array(
					'name' => 'Repair boards (1)',
					'description' => "Repair check",
					'url' => $scripturl . '?action=admin;area=repairboards',
				),
				'Reports_1' => array(
					'name' => 'Reports (1)',
					'description' => "Select report type",
					'url' => $scripturl . '?action=admin;area=reports',
				),
				'Reports_2' => array(
					'name' => 'Reports (2)',
					'description' => "Boards report",
					'url' => $scripturl . '?action=admin;area=reports;rt=boards',
				),
				'Reports_3' => array(
					'name' => 'Reports (3)',
					'description' => "Board permissions report",
					'url' => $scripturl . '?action=admin;area=reports;rt=board_perms',
				),
				'Reports_4' => array(
					'name' => 'Reports (4)',
					'description' => "Membergroups report",
					'url' => $scripturl . '?action=admin;area=reports;rt=member_groups',
				),
				'Reports_5' => array(
					'name' => 'Reports (5)',
					'description' => "Group permissions report",
					'url' => $scripturl . '?action=admin;area=reports;rt=group_perms',
				),
				'Reports_6' => array(
					'name' => 'Reports (6)',
					'description' => "Staff report",
					'url' => $scripturl . '?action=admin;area=reports;rt=staff',
				),
				'Search_1' => array(
					'name' => 'Search (1)',
					'description' => "Search forum",
					'url' => $scripturl . '?action=search',
				),
				'Search_2' => array(
					'name' => 'Search (2)',
					'description' => "Search forum advanced",
					'url' => $scripturl . '?action=search;sa=advanced',
				),
				'SendTopic_1' => array(
					'name' => 'Send topic (1)',
					'description' => "Send topic",
					'url' => $scripturl . '?action=emailuser;sa=sendtopic;topic=' . $this->_id_topic . '.0',
				),
				'SendTopic_2' => array(
					'name' => 'Send topic (2)',
					'description' => "Send user a mail",
					'url' => $scripturl . '?action=emailuser;sa=email;msg=' . $this->_id_msg,
				),
				'SendTopic_3' => array(
					'name' => 'Send topic (3)',
					'description' => "Send topic",
					'url' => $scripturl . '?action=reporttm;topic=' . $this->_id_topic . '.0;msg=' . $this->_id_msg,
				),
				'SplitTopics_1' => array(
					'name' => 'Split topic (1)',
					'description' => "Split topic",
					'url' => $scripturl . '?action=splittopics;topic=' . $this->_id_topic . '.0;at=' . $this->_id_msg2,
				),
				'SplitTopics_2' => array(
					'name' => 'Split topic (2)',
					'description' => "Merge topic",
					'url' => $scripturl . '?action=mergetopics;board=' . $this->_id_board . '.0;from=' . $this->_id_topic,
				),
				'SplitTopics_3' => array(
					'name' => 'Split topic (3)',
					'description' => "Merge topic - step 2",
					'url' => $scripturl . '?action=mergetopics;sa=options;board=' . $this->_id_board . '.0;from=' . $this->_id_topic . ';to=' . $this->_id_topic2,
				),
				'Stats_1' => array(
					'name' => 'Statistics center (1)',
					'description' => "Statistics center",
					'url' => $scripturl . '?action=stats',
				),
				'Stats_2' => array(
					'name' => 'Statistics center (2)',
					'description' => "Statistics center - current month collapsed",
					'url' => $scripturl . '?action=stats;expand=' . date('Ym'),
				),
				'Themes_1' => array(
					'name' => 'Themes (1)',
					'description' => "Manage and install",
					'url' => $scripturl . '?action=admin;area=theme;sa=admin',
				),
				'Themes_2' => array(
					'name' => 'Themes (2)',
					'description' => "Theme list",
					'url' => $scripturl . '?action=admin;area=theme;sa=list',
				),
				'Themes_3' => array(
					'name' => 'Themes (3)',
					'description' => "Default theme settings",
					'url' => $scripturl . '?action=admin;area=theme;sa=settings;th=1',
				),
				'Themes_4' => array(
					'name' => 'Themes (4)',
					'description' => "Default theme options",
					'url' => $scripturl . '?action=admin;area=theme;sa=reset;th=1',
				),
				'Themes_5' => array(
					'name' => 'Themes (5)',
					'description' => "Set/reset theme options for guests and new users",
					'url' => $scripturl . '?action=admin;area=theme;sa=reset',
				),
				'Themes_6' => array(
					'name' => 'Themes (6)',
					'description' => "Set/reset theme options for all members",
					'url' => $scripturl . '?action=admin;area=theme;sa=reset;who=1',
				),
				'Themes_7' => array(
					'name' => 'Themes (7)',
					'description' => "Modify themes",
					'url' => $scripturl . '?action=admin;area=theme;sa=edit',
				),
				'Themes_8' => array(
					'name' => 'Themes (8)',
					'description' => "Browse templates",
					'url' => $scripturl . '?action=admin;area=theme;sa=edit;th=1',
				),
				'Themes_9' => array(
					'name' => 'Themes (9)',
					'description' => "Edit template",
					'url' => $scripturl . '?action=admin;area=theme;sa=edit;th=1;filename=BoardIndex.template.php',
				),
				'Themes_10' => array(
					'name' => 'Themes (10)',
					'description' => "Edit CSS",
					'url' => $scripturl . '?action=admin;area=theme;sa=edit;th=1;filename=css/admin.css',
				),
				'Themes_11' => array(
					'name' => 'Themes (11)',
					'description' => "Edit JS",
					'url' => $scripturl . '?action=admin;area=theme;sa=edit;th=1;filename=scripts/script.js',
				),
				'Who_1' => array(
					'name' => 'Whos online (1)',
					'description' => "Whos online overview",
					'url' => $scripturl . '?action=who',
				),
			);
					
		}
				
		public function initialize()
		{
			
		}
		
		public function getTests()
		{
			$tests = array();
			foreach ($this->_tests as $testID => $testInfo)
				$tests[$testID] = array(
					'name' => $testInfo['name'],
					'description' => $testInfo['description'],
				);
			
			return $tests;
		}
		
		public function doTest($testID)
		{
			global $scripturl;
			
			if (!isset($this->_tests[$testID]))
				return 'Invalid test ID given';
				
			$returnDoc = $this->_simulateClick($this->_tests[$testID]['url'], isset($this->_tests[$testID]['id_member']) ? $this->_tests[$testID]['id_member'] : $this->_id_member);
			//
			$testResults = $this->_testHtml($returnDoc['html'], $this->_openSpPath . ' -wvalid -wnon-sgml-char-ref -wno-duplicate -E0 -s ' . dirname($this->_openSpPath) . '/xml.dcl -');
			if (empty($testResults))
				$testResults = $this->_testHtml($returnDoc['html'], $this->_tidyPath . ' -errors -quiet -access -1');
				
			if (empty($testResults))
				return true;
			else
				return htmlspecialchars(implode("\n", $testResults) . "\n" . $this->_tests[$testID]['url']);
		}
		
		public function getTestDescription($testID)
		{
			if (isset($this->_tests[$testID]['description']))
				return $this->_tests[$testID]['description'];
			elseif (isset($this->_tests[$testID]))
				return 'No description available';
			else
				return 'Invalid test ID given';
		}

		protected function _testHtml($html, $tool)
		{
			global $cachedir;
			
			
			
			// Apparently windows can't handle large stdin values, therefor a file streaming is needed..
			$tempFile = $cachedir . '/tmp_validator_' . md5(mt_rand(0, 10000000000)) . '.html';
			file_put_contents($tempFile, $html);
			
			
			$descriptorspec = array(
			   0 => array('file', $tempFile, 'r'),  // stdin
			   1 => array('pipe', 'w'),  // stdout
			   2 => array('pipe', 'w') // stder
			);
			
			$process = @proc_open($tool, $descriptorspec, $pipes, null, null, array('bypass_shell' => true));
			
			if (is_resource($process))
			{
			    fclose($pipes[1]);
			
			    $errorList = array();
			    while (!feof($pipes[2]))
			    {
				    $line = trim(fgets($pipes[2], 1024), "\n\r");
				    if (empty($line))
				    	continue;
				    	
					foreach ($this->_ignoreErrors as $ignorePattern)
						if (preg_match($ignorePattern, $line) === 1)
							continue 2;
					$errorList[] = $line;
			    }
			    fclose($pipes[2]);
			    
				proc_close($process);
			}
			else
				$errorList = array('Unable to test page');
				
				
			@unlink($tempFile);
				
			return $errorList;
		}
	}
	
?>