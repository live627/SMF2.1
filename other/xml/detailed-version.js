<?php

header('Content-Type: text/javascript');

list($modified_since) = explode(';', $_SERVER['HTTP_IF_MODIFIED_SINCE']);
if (!empty($_SERVER['HTTP_IF_MODIFIED_SINCE']) && strtotime($modified_since) >= filemtime(__FILE__))
{
	header('HTTP/1.1 304 Not Modified');
	die;
}

// Are they a Charter Member?
if (isset($_GET['version']) && strpos($_GET['version'], '2.0') !== false)
{
?>window.smfVersions = {
	'SMF': 'SMF 2.0 Beta 1',
	'SourcesAdmin.php': '2.0 Beta 1',
	'SourcesBoardIndex.php': '2.0 Beta 1',
	'SourcesCalendar.php': '2.0 Beta 1',
	'SourcesClass-Graphics.php': '2.0 Beta 1',
	'SourcesClass-Package.php': '2.0 Beta 1',
	'SourcesDbExtra-mysql.php': '2.0 Beta 1',
	'SourcesDbExtra-postgresql.php': '2.0 Beta 1',
	'SourcesDbExtra-sqlite.php': '2.0 Beta 1',
	'SourcesDbPackages-mysql.php': '2.0 Beta 1',
	'SourcesDbPackages-postgresql.php': '2.0 Beta 1',
	'SourcesDbPackages-sqlite.php': '2.0 Beta 1',
	'SourcesDbSearch-mysql.php': '2.0 Beta 1',
	'SourcesDbSearch-postgresql.php': '2.0 Beta 1',
	'SourcesDbSearch-sqlite.php': '2.0 Beta 1',
	'SourcesDisplay.php': '2.0 Beta 1',
	'SourcesDumpDatabase.php': '2.0 Beta 1',
	'SourcesErrors.php': '2.0 Beta 1',
	'SourcesFixLanguage.php': '2.0 Beta 1',
	'SourcesGroups.php': '2.0 Beta 1',
	'SourcesHelp.php': '2.0 Beta 1',
	'SourcesKarma.php': '2.0 Beta 1',
	'SourcesLoad.php': '2.0 Beta 1',
	'SourcesLockTopic.php': '2.0 Beta 1',
	'SourcesLogInOut.php': '2.0 Beta 1',
	'SourcesManageAttachments.php': '2.0 Beta 1',
	'SourcesManageBans.php': '2.0 Beta 1',
	'SourcesManageBoards.php': '2.0 Beta 1',
	'SourcesManageCalendar.php': '2.0 Beta 1',
	'SourcesManageErrors.php': '2.0 Beta 1',
	'SourcesManageMail.php': '2.0 Beta 1',
	'SourcesManageMaintenance.php': '2.0 Beta 1',
	'SourcesManageMembergroups.php': '2.0 Beta 1',
	'SourcesManageMembers.php': '2.0 Beta 1',
	'SourcesManageNews.php': '2.0 Beta 1',
	'SourcesManagePermissions.php': '2.0 Beta 1',
	'SourcesManagePosts.php': '2.0 Beta 1',
	'SourcesManageRegistration.php': '2.0 Beta 1',
	'SourcesManageSearch.php': '2.0 Beta 1',
	'SourcesManageServer.php': '2.0 Beta 1',
	'SourcesManageSettings.php': '2.0 Beta 1',
	'SourcesManageSmileys.php': '2.0 Beta 1',
	'SourcesMemberlist.php': '2.0 Beta 1',
	'SourcesMessageIndex.php': '2.0 Beta 1',
	'SourcesModerationCenter.php': '2.0 Beta 1',
	'SourcesModlog.php': '2.0 Beta 1',
	'SourcesMoveTopic.php': '2.0 Beta 1',
	'SourcesNews.php': '2.0 Beta 1',
	'SourcesNotify.php': '2.0 Beta 1',
	'SourcesPackageGet.php': '2.0 Beta 1',
	'SourcesPackages.php': '2.0 Beta 1',
	'SourcesPersonalMessage.php': '2.0 Beta 1',
	'SourcesPoll.php': '2.0 Beta 1',
	'SourcesPost.php': '2.0 Beta 1',
	'SourcesPostModeration.php': '2.0 Beta 1',
	'SourcesPrintpage.php': '2.0 Beta 1',
	'SourcesProfile.php': '2.0 Beta 1',
	'SourcesQueryString.php': '2.0 Beta 1',
	'SourcesRecent.php': '2.0 Beta 1',
	'SourcesRegister.php': '2.0 Beta 1',
	'SourcesReminder.php': '2.0 Beta 1',
	'SourcesRemoveTopic.php': '2.0 Beta 1',
	'SourcesRepairBoards.php': '2.0 Beta 1',
	'SourcesReports.php': '2.0 Beta 1',
	'SourcesSSI.php': '2.0 Beta 1',
	'SourcesScheduledTasks.php': '2.0 Beta 1',
	'SourcesSearch.php': '2.0 Beta 1',
	'SourcesSecurity.php': '2.0 Beta 1',
	'SourcesSendTopic.php': '2.0 Beta 1',
	'SourcesSplitTopics.php': '2.0 Beta 1',
	'SourcesStats.php': '2.0 Beta 1',
	'SourcesSubs.php': '2.0 Beta 1',
	'SourcesSubs-Admin.php': '2.0 Beta 1',
	'SourcesSubs-Auth.php': '2.0 Beta 1',
	'SourcesSubs-BoardIndex.php': '2.0 Beta 1',
	'SourcesSubs-Boards.php': '2.0 Beta 1',
	'SourcesSubs-Calendar.php': '2.0 Beta 1',
	'SourcesSubs-Categories.php' : '2.0 Beta 1',
	'SourcesSubs-Charset.php' : '2.0 Beta 1',
	'SourcesSubs-Compat.php': '2.0 Beta 1',
	'SourcesSubs-Db-mysql.php': '2.0 Beta 1',
	'SourcesSubs-Db-postgresql.php': '2.0 Beta 1',
	'SourcesSubs-Db-sqlite.php': '2.0 Beta 1',
	'SourcesSubs-Editor.php': '2.0 Beta 1',
	'SourcesSubs-Graphics.php': '2.0 Beta 1',
	'SourcesSubs-List.php': '2.0 Beta 1',
	'SourcesSubs-Membergroups.php': '2.0 Beta 1',
	'SourcesSubs-Members.php': '2.0 Beta 1',
	'SourcesSubs-MembersOnline.php': '2.0 Beta 1',
	'SourcesSubs-Menu.php': '2.0 Beta 1',
	'SourcesSubs-MessageIndex.php': '2.0 Beta 1',
	'SourcesSubs-OpenID.php': '2.0 Beta 1',
	'SourcesSubs-Package.php': '2.0 Beta 1',
	'SourcesSubs-Post.php': '2.0 Beta 1',
	'SourcesSubs-Recent.php': '2.0 Beta 1',
	'SourcesSubs-Sound.php': '2.0 Beta 1',
	'SourcesThemes.php': '2.0 Beta 1',
	'SourcesViewQuery.php': '2.0 Beta 1',
	'SourcesWho.php': '2.0 Beta 1',
	'SourcesXml.php': '2.0 Beta 1',
	'DefaultAdmin.template.php': '2.0 Beta 1',
	'DefaultBoardIndex.template.php': '2.0 Beta 1',
	'DefaultCalendar.template.php': '2.0 Beta 1',
	'DefaultCombat.template.php': '2.0 Beta 1',
	'DefaultDisplay.template.php': '2.0 Beta 1',
	'DefaultErrors.template.php': '2.0 Beta 1',
	'DefaultGenericList.template.php': '2.0 Beta 1',
	'DefaultGenericMenu.template.php': '2.0 Beta 1',
	'DefaultHelp.template.php': '2.0 Beta 1',
	'DefaultLogin.template.php': '2.0 Beta 1',
	'DefaultManageAttachments.template.php': '2.0 Beta 1',
	'DefaultManageBans.template.php': '2.0 Beta 1',
	'DefaultManageBoards.template.php': '2.0 Beta 1',
	'DefaultManageCalendar.template.php': '2.0 Beta 1',
	'DefaultManageMail.template.php': '2.0 Beta 1',
	'DefaultManageMembergroups.template.php': '2.0 Beta 1',
	'DefaultManageMembers.template.php': '2.0 Beta 1',
	'DefaultManageNews.template.php': '2.0 Beta 1',
	'DefaultManagePermissions.template.php': '2.0 Beta 1',
	'DefaultManageSearch.template.php': '2.0 Beta 1',
	'DefaultManageSmileys.template.php': '2.0 Beta 1',
	'DefaultMemberlist.template.php': '2.0 Beta 1',
	'DefaultMessageIndex.template.php': '2.0 Beta 1',
	'DefaultModerationCenter.template.php': '2.0 Beta 1',
	'DefaultModlog.template.php': '2.0 Beta 1',
	'DefaultMoveTopic.template.php': '2.0 Beta 1',
	'DefaultNotify.template.php': '2.0 Beta 1',
	'DefaultPackages.template.php': '2.0 Beta 1',
	'DefaultPersonalMessage.template.php': '2.0 Beta 1',
	'DefaultPoll.template.php': '2.0 Beta 1',
	'DefaultPost.template.php': '2.0 Beta 1',
	'DefaultPrintpage.template.php': '2.0 Beta 1',
	'DefaultProfile.template.php': '2.0 Beta 1',
	'DefaultRecent.template.php': '2.0 Beta 1',
	'DefaultRegister.template.php': '2.0 Beta 1',
	'DefaultReminder.template.php': '2.0 Beta 1',
	'DefaultReports.template.php': '2.0 Beta 1',
	'DefaultSearch.template.php': '2.0 Beta 1',
	'DefaultSendTopic.template.php': '2.0 Beta 1',
	'DefaultSettings.template.php': '2.0 Beta 1',
	'DefaultSplitTopics.template.php': '2.0 Beta 1',
	'DefaultStats.template.php': '2.0 Beta 1',
	'DefaultThemes.template.php': '2.0 Beta 1',
	'DefaultWho.template.php': '2.0 Beta 1',
	'DefaultWireless.template.php': '2.0 Beta 1',
	'DefaultXml.template.php': '2.0 Beta 1',
	'Defaultindex.template.php': '2.0 Beta 1',
	'TemplatesAdmin.template.php': '2.0 Beta 1',
	'TemplatesBoardIndex.template.php': '2.0 Beta 1',
	'TemplatesCalendar.template.php': '2.0 Beta 1',
	'TemplatesDisplay.template.php': '2.0 Beta 1',
	'TemplatesErrors.template.php': '2.0 Beta 1',
	'TemplatesGenericList.template.php': '2.0 Beta 1',
	'TemplatesGenericMenu.template.php': '2.0 Beta 1',
	'TemplatesHelp.template.php': '2.0 Beta 1',
	'TemplatesLogin.template.php': '2.0 Beta 1',
	'TemplatesManageAttachments.template.php': '2.0 Beta 1',
	'TemplatesManageBans.template.php': '2.0 Beta 1',
	'TemplatesManageBoards.template.php': '2.0 Beta 1',
	'TemplatesManageCalendar.template.php': '2.0 Beta 1',
	'TemplatesManageMail.template.php': '2.0 Beta 1',
	'TemplatesManageMembergroups.template.php': '2.0 Beta 1',
	'TemplatesManageMembers.template.php': '2.0 Beta 1',
	'TemplatesManageNews.template.php': '2.0 Beta 1',
	'TemplatesManagePermissions.template.php': '2.0 Beta 1',
	'TemplatesManageSearch.template.php': '2.0 Beta 1',
	'TemplatesManageSmileys.template.php': '2.0 Beta 1',
	'TemplatesMemberlist.template.php': '2.0 Beta 1',
	'TemplatesMessageIndex.template.php': '2.0 Beta 1',
	'TemplatesModerationCenter.template.php': '2.0 Beta 1',
	'TemplatesModlog.template.php': '2.0 Beta 1',
	'TemplatesMoveTopic.template.php': '2.0 Beta 1',
	'TemplatesNotify.template.php': '2.0 Beta 1',
	'TemplatesPackages.template.php': '2.0 Beta 1',
	'TemplatesPersonalMessage.template.php': '2.0 Beta 1',
	'TemplatesPoll.template.php': '2.0 Beta 1',
	'TemplatesPost.template.php': '2.0 Beta 1',
	'TemplatesPrintpage.template.php': '2.0 Beta 1',
	'TemplatesProfile.template.php': '2.0 Beta 1',
	'TemplatesRecent.template.php': '2.0 Beta 1',
	'TemplatesRegister.template.php': '2.0 Beta 1',
	'TemplatesReminder.template.php': '2.0 Beta 1',
	'TemplatesReports.template.php': '2.0 Beta 1',
	'TemplatesSearch.template.php': '2.0 Beta 1',
	'TemplatesSendTopic.template.php': '2.0 Beta 1',
	'TemplatesSettings.template.php': '2.0 Beta 1',
	'TemplatesSplitTopics.template.php': '2.0 Beta 1',
	'TemplatesStats.template.php': '2.0 Beta 1',
	'TemplatesThemes.template.php': '2.0 Beta 1',
	'TemplatesWho.template.php': '2.0 Beta 1',
	'TemplatesWireless.template.php': '2.0 Beta 1',
	'TemplatesXml.template.php': '2.0 Beta 1',
	'Templatesindex.template.php': '2.0 Beta 1'
};

window.smfLanguageVersions = {
	'Admin': '2.0 Beta 1',
	'EmailTemplates': '2.0 Beta 1',
	'Errors': '2.0 Beta 1',
	'Help': '2.0 Beta 1',
	'index': '2.0 Beta 1',
	'Install': '2.0 Beta 1',
	'Login': '2.0 Beta 1',
	'ManageBoards': '2.0 Beta 1',
	'ManageCalendar': '2.0 Beta 1',
	'ManageMembers': '2.0 Beta 1',
	'ManagePermissions': '2.0 Beta 1',
	'ManageSettings': '2.0 Beta 1',
	'ManageSmileys': '2.0 Beta 1',
	'Manual': '2.0 Beta 1',
	'ModerationCenter': '2.0 Beta 1',
	'Modifications': '2.0 Beta 1',
	'Packages': '2.0 Beta 1',
	'PersonalMessage': '2.0 Beta 1',
	'Post': '2.0 Beta 1',
	'Profile': '2.0 Beta 1',
	'Reports': '2.0 Beta 1',
	'Search': '2.0 Beta 1',
	'Settings': '2.0 Beta 1',
	'Stats': '2.0 Beta 1',
	'Themes': '2.0 Beta 1',
	'Who': '2.0 Beta 1',
	'Wireless': '2.0 Beta 1'
};
<?php
}
// Normal user?
else
{
?>
window.smfVersions = {
	'SMF': 'SMF 1.1.3',
	'SourcesAdmin.php': '1.1',
	'SourcesBoardIndex.php': '1.1',
	'SourcesCalendar.php': '1.1',
	'SourcesDisplay.php': '1.1.2',
	'SourcesDumpDatabase.php': '1.1',
	'SourcesErrors.php': '1.1',
	'SourcesHelp.php': '1.1',
	'SourcesKarma.php': '1.1',
	'SourcesLoad.php': '1.1.2',
	'SourcesLockTopic.php': '1.1',
	'SourcesLogInOut.php': '1.1.3',
	'SourcesManageAttachments.php': '1.1',
	'SourcesManageBans.php': '1.1',
	'SourcesManageBoards.php': '1.1.2',
	'SourcesManageCalendar.php': '1.1',
	'SourcesManageErrors.php': '1.1',
	'SourcesManageMembergroups.php': '1.1.2',
	'SourcesManageMembers.php': '1.1',
	'SourcesManageNews.php': '1.1.3',
	'SourcesManagePermissions.php': '1.1.2',
	'SourcesManagePosts.php': '1.1',
	'SourcesManageRegistration.php': '1.1.2',
	'SourcesManageSearch.php': '1.1.2',
	'SourcesManageServer.php': '1.1',
	'SourcesManageSmileys.php': '1.1.1',
	'SourcesMemberlist.php': '1.1',
	'SourcesMessageIndex.php': '1.1',
	'SourcesModSettings.php': '1.1',
	'SourcesModlog.php': '1.1',
	'SourcesMoveTopic.php': '1.1',
	'SourcesNews.php': '1.1',
	'SourcesNotify.php': '1.1',
	'SourcesPackageGet.php': '1.1',
	'SourcesPackages.php': '1.1.2',
	'SourcesPersonalMessage.php': '1.1.3',
	'SourcesPoll.php': '1.1',
	'SourcesPost.php': '1.1.3',
	'SourcesPrintpage.php': '1.1',
	'SourcesProfile.php': '1.1.3',
	'SourcesQueryString.php': '1.1.3',
	'SourcesRecent.php': '1.1',
	'SourcesRegister.php': '1.1.2',
	'SourcesReminder.php': '1.1.2',
	'SourcesRemoveTopic.php': '1.1',
	'SourcesRepairBoards.php': '1.1',
	'SourcesReports.php': '1.1',
	'SourcesSSI.php': '1.1',
	'SourcesSearch.php': '1.1.2',
	'SourcesSecurity.php': '1.1',
	'SourcesSendTopic.php': '1.1',
	'SourcesSplitTopics.php': '1.1.2',
	'SourcesStats.php': '1.1',
	'SourcesSubs.php': '1.1.3',
	'SourcesSubs-Auth.php': '1.1.3',
	'SourcesSubs-Boards.php': '1.1',
	'SourcesSubs-Charset.php' : '1.1',
	'SourcesSubs-Compat.php': '1.1.2',
	'SourcesSubs-Graphics.php': '1.1.2',
	'SourcesSubs-Members.php': '1.1.2',
	'SourcesSubs-Package.php': '1.1.2',
	'SourcesSubs-Post.php': '1.1.2',
	'SourcesSubs-Sound.php': '1.1',
	'SourcesThemes.php': '1.1.2',
	'SourcesViewQuery.php': '1.1',
	'SourcesWho.php': '1.1',
	'DefaultAdmin.template.php': '1.1.1',
	'DefaultBoardIndex.template.php': '1.1',
	'DefaultCalendar.template.php': '1.1',
	'DefaultCombat.template.php': '1.1',
	'DefaultDisplay.template.php': '1.1',
	'DefaultErrors.template.php': '1.1',
	'DefaultHelp.template.php': '1.1',
	'DefaultLogin.template.php': '1.1',
	'DefaultManageAttachments.template.php': '1.1',
	'DefaultManageBans.template.php': '1.1',
	'DefaultManageBoards.template.php': '1.1',
	'DefaultManageCalendar.template.php': '1.1',
	'DefaultManageMembergroups.template.php': '1.1',
	'DefaultManageMembers.template.php': '1.1',
	'DefaultManageNews.template.php': '1.1',
	'DefaultManagePermissions.template.php': '1.1',
	'DefaultManageSearch.template.php': '1.1',
	'DefaultManageSmileys.template.php': '1.1',
	'DefaultMemberlist.template.php': '1.1',
	'DefaultMessageIndex.template.php': '1.1',
	'DefaultModlog.template.php': '1.1',
	'DefaultMoveTopic.template.php': '1.1',
	'DefaultNotify.template.php': '1.1',
	'DefaultPackages.template.php': '1.1',
	'DefaultPersonalMessage.template.php': '1.1',
	'DefaultPoll.template.php': '1.1.2',
	'DefaultPost.template.php': '1.1',
	'DefaultPrintpage.template.php': '1.1',
	'DefaultProfile.template.php': '1.1.2',
	'DefaultRecent.template.php': '1.1',
	'DefaultRegister.template.php': '1.1.2',
	'DefaultReminder.template.php': '1.1',
	'DefaultReports.template.php': '1.1',
	'DefaultSearch.template.php': '1.1.1',
	'DefaultSendTopic.template.php': '1.1',
	'DefaultSettings.template.php': '1.1',
	'DefaultSplitTopics.template.php': '1.1',
	'DefaultStats.template.php': '1.1',
	'DefaultThemes.template.php': '1.1',
	'DefaultWho.template.php': '1.1',
	'DefaultWireless.template.php': '1.1',
	'DefaultXml.template.php': '1.1',
	'Defaultindex.template.php': '1.1',
	'TemplatesAdmin.template.php': '1.1',
	'TemplatesBoardIndex.template.php': '1.1',
	'TemplatesCalendar.template.php': '1.1',
	'TemplatesDisplay.template.php': '1.1',
	'TemplatesErrors.template.php': '1.1',
	'TemplatesHelp.template.php': '1.1',
	'TemplatesLogin.template.php': '1.1',
	'TemplatesManageAttachments.template.php': '1.1',
	'TemplatesManageBans.template.php': '1.1',
	'TemplatesManageBoards.template.php': '1.1',
	'TemplatesManageCalendar.template.php': '1.1',
	'TemplatesManageMembergroups.template.php': '1.1',
	'TemplatesManageMembers.template.php': '1.1',
	'TemplatesManageNews.template.php': '1.1',
	'TemplatesManagePermissions.template.php': '1.1',
	'TemplatesManageSearch.template.php': '1.1',
	'TemplatesManageSmileys.template.php': '1.1',
	'TemplatesMemberlist.template.php': '1.1',
	'TemplatesMessageIndex.template.php': '1.1',
	'TemplatesModlog.template.php': '1.1',
	'TemplatesMoveTopic.template.php': '1.1',
	'TemplatesNotify.template.php': '1.1',
	'TemplatesPackages.template.php': '1.1',
	'TemplatesPersonalMessage.template.php': '1.1',
	'TemplatesPoll.template.php': '1.1.2',
	'TemplatesPost.template.php': '1.1',
	'TemplatesPrintpage.template.php': '1.1',
	'TemplatesProfile.template.php': '1.1.2',
	'TemplatesRecent.template.php': '1.1',
	'TemplatesRegister.template.php': '1.1.2',
	'TemplatesReminder.template.php': '1.1',
	'TemplatesReports.template.php': '1.1',
	'TemplatesSearch.template.php': '1.1',
	'TemplatesSendTopic.template.php': '1.1',
	'TemplatesSettings.template.php': '1.1',
	'TemplatesSplitTopics.template.php': '1.1',
	'TemplatesStats.template.php': '1.1',
	'TemplatesThemes.template.php': '1.1',
	'TemplatesWho.template.php': '1.1',
	'TemplatesWireless.template.php': '1.1',
	'TemplatesXml.template.php': '1.1',
	'Templatesindex.template.php': '1.1'
};

window.smfLanguageVersions = {
	'Admin': '1.1',
	'Errors': '1.1.2',
	'Help': '1.1',
	'index': '1.1.2',
	'Install': '1.1',
	'Login': '1.1.2',
	'ManageBoards': '1.1',
	'ManageCalendar': '1.1',
	'ManageMembers': '1.1',
	'ManagePermissions': '1.1',
	'ManageSmileys': '1.1',
	'Manual': '1.1',
	'ModSettings': '1.1',
	'Modifications': '1.1',
	'Packages': '1.1',
	'PersonalMessage': '1.1',
	'Post': '1.1',
	'Profile': '1.1',
	'Reports': '1.1',
	'Search': '1.1',
	'Settings': '1.1',
	'Stats': '1.1',
	'Themes': '1.1',
	'Who': '1.1',
	'Wireless': '1.1'
};
<?php
}
?>