<?php

header('Content-Type: text/javascript');

list($modified_since) = explode(';', $_SERVER['HTTP_IF_MODIFIED_SINCE']);
if (!empty($_SERVER['HTTP_IF_MODIFIED_SINCE']) && strtotime($modified_since) >= filemtime(__FILE__))
{
	header('HTTP/1.1 304 Not Modified');
	die;
}

// Are they a Charter Member?
if (isset($_GET['version']) && strpos($_GET['version'], 'SMF Development Edition') !== false)
{
?>window.smfVersions = {
	'SMF': 'SMF Development Edition Alpha 1',
	'SourcesAdmin.php': 'Development Edition Alpha 1',
	'SourcesBoardIndex.php': 'Development Edition Alpha 1',
	'SourcesCalendar.php': 'Development Edition Alpha 1',
	'SourcesDbExtra-mysql.php': 'Development Edition Alpha 1',
	'SourcesDbExtra-postgresql.php': 'Development Edition Alpha 1',
	'SourcesDbExtra-sqlite.php': 'Development Edition Alpha 1',
	'SourcesDbPackages-mysql.php': 'Development Edition Alpha 1',
	'SourcesDbPackages-postgresql.php': 'Development Edition Alpha 1',
	'SourcesDbPackages-sqlite.php': 'Development Edition Alpha 1',
	'SourcesDbSearch-mysql.php': 'Development Edition Alpha 1',
	'SourcesDbSearch-postgresql.php': 'Development Edition Alpha 1',
	'SourcesDbSearch-sqlite.php': 'Development Edition Alpha 1',
	'SourcesDisplay.php': 'Development Edition Alpha 1',
	'SourcesDumpDatabase.php': 'Development Edition Alpha 1',
	'SourcesErrors.php': 'Development Edition Alpha 1',
	'SourcesFixLanguage.php': 'Development Edition Alpha 1',
	'SourcesGroups.php': 'Development Edition Alpha 1',
	'SourcesHelp.php': 'Development Edition Alpha 1',
	'SourcesKarma.php': 'Development Edition Alpha 1',
	'SourcesLoad.php': 'Development Edition Alpha 1',
	'SourcesLockTopic.php': 'Development Edition Alpha 1',
	'SourcesLogInOut.php': 'Development Edition Alpha 1',
	'SourcesManageAttachments.php': 'Development Edition Alpha 1',
	'SourcesManageBans.php': 'Development Edition Alpha 1',
	'SourcesManageBoards.php': 'Development Edition Alpha 1',
	'SourcesManageCalendar.php': 'Development Edition Alpha 1',
	'SourcesManageErrors.php': 'Development Edition Alpha 1',
	'SourcesManageMail.php': 'Development Edition Alpha 1',
	'SourcesManageMaintenance.php': 'Development Edition Alpha 1',
	'SourcesManageMembergroups.php': 'Development Edition Alpha 1',
	'SourcesManageMembers.php': 'Development Edition Alpha 1',
	'SourcesManageNews.php': 'Development Edition Alpha 1',
	'SourcesManagePermissions.php': 'Development Edition Alpha 1',
	'SourcesManagePosts.php': 'Development Edition Alpha 1',
	'SourcesManageRegistration.php': 'Development Edition Alpha 1',
	'SourcesManageSearch.php': 'Development Edition Alpha 1',
	'SourcesManageServer.php': 'Development Edition Alpha 1',
	'SourcesManageSmileys.php': 'Development Edition Alpha 1',
	'SourcesMemberlist.php': 'Development Edition Alpha 1',
	'SourcesMessageIndex.php': 'Development Edition Alpha 1',
	'SourcesModSettings.php': 'Development Edition Alpha 1',
	'SourcesModerationCenter.php': 'Development Edition Alpha 1',
	'SourcesModlog.php': 'Development Edition Alpha 1',
	'SourcesMoveTopic.php': 'Development Edition Alpha 1',
	'SourcesNews.php': 'Development Edition Alpha 1',
	'SourcesNotify.php': 'Development Edition Alpha 1',
	'SourcesPackageGet.php': 'Development Edition Alpha 1',
	'SourcesPackages.php': 'Development Edition Alpha 1',
	'SourcesPersonalMessage.php': 'Development Edition Alpha 1',
	'SourcesPoll.php': 'Development Edition Alpha 1',
	'SourcesPost.php': 'Development Edition Alpha 1',
	'SourcesPostModeration.php': 'Development Edition Alpha 1',
	'SourcesPrintpage.php': 'Development Edition Alpha 1',
	'SourcesProfile.php': 'Development Edition Alpha 1',
	'SourcesQueryString.php': 'Development Edition Alpha 1',
	'SourcesRecent.php': 'Development Edition Alpha 1',
	'SourcesRegister.php': 'Development Edition Alpha 1',
	'SourcesReminder.php': 'Development Edition Alpha 1',
	'SourcesRemoveTopic.php': 'Development Edition Alpha 1',
	'SourcesRepairBoards.php': 'Development Edition Alpha 1',
	'SourcesReports.php': 'Development Edition Alpha 1',
	'SourcesSSI.php': 'Development Edition Alpha 1',
	'SourcesScheduledTasks.php': 'Development Edition Alpha 1',
	'SourcesSearch.php': 'Development Edition Alpha 1',
	'SourcesSecurity.php': 'Development Edition Alpha 1',
	'SourcesSendTopic.php': 'Development Edition Alpha 1',
	'SourcesSplitTopics.php': 'Development Edition Alpha 1',
	'SourcesStats.php': 'Development Edition Alpha 1',
	'SourcesSubs.php': 'Development Edition Alpha 1',
	'SourcesSubs-Admin.php': 'Development Edition Alpha 1',
	'SourcesSubs-Auth.php': 'Development Edition Alpha 1',
	'SourcesSubs-Boards.php': 'Development Edition Alpha 1',
	'SourcesSubs-Categories.php' : 'Development Edition Alpha 1',
	'SourcesSubs-Charset.php' : 'Development Edition Alpha 1',
	'SourcesSubs-Compat.php': 'Development Edition Alpha 1',
	'SourcesSubs-Db-mysql.php': 'Development Edition Alpha 1',
	'SourcesSubs-Db-postgresql.php': 'Development Edition Alpha 1',
	'SourcesSubs-Db-sqlite.php': 'Development Edition Alpha 1',
	'SourcesSubs-Editor.php': 'Development Edition Alpha 1',
	'SourcesSubs-Graphics.php': 'Development Edition Alpha 1',
	'SourcesSubs-Membergroups.php': 'Development Edition Alpha 1',
	'SourcesSubs-Members.php': 'Development Edition Alpha 1',
	'SourcesSubs-Package.php': 'Development Edition Alpha 1',
	'SourcesSubs-Post.php': 'Development Edition Alpha 1',
	'SourcesSubs-Sound.php': 'Development Edition Alpha 1',
	'SourcesThemes.php': 'Development Edition Alpha 1',
	'SourcesViewQuery.php': 'Development Edition Alpha 1',
	'SourcesWho.php': 'Development Edition Alpha 1',
	'SourcesXml.php': 'Development Edition Alpha 1',
	'DefaultAdmin.template.php': 'Development Edition Alpha 1',
	'DefaultBoardIndex.template.php': 'Development Edition Alpha 1',
	'DefaultCalendar.template.php': 'Development Edition Alpha 1',
	'DefaultCombat.template.php': 'Development Edition Alpha 1',
	'DefaultDisplay.template.php': 'Development Edition Alpha 1',
	'DefaultErrors.template.php': 'Development Edition Alpha 1',
	'DefaultHelp.template.php': 'Development Edition Alpha 1',
	'DefaultLogin.template.php': 'Development Edition Alpha 1',
	'DefaultManageAttachments.template.php': 'Development Edition Alpha 1',
	'DefaultManageBans.template.php': 'Development Edition Alpha 1',
	'DefaultManageBoards.template.php': 'Development Edition Alpha 1',
	'DefaultManageCalendar.template.php': 'Development Edition Alpha 1',
	'DefaultManageMembergroups.template.php': 'Development Edition Alpha 1',
	'DefaultManageMembers.template.php': 'Development Edition Alpha 1',
	'DefaultManageNews.template.php': 'Development Edition Alpha 1',
	'DefaultManagePermissions.template.php': 'Development Edition Alpha 1',
	'DefaultManageSearch.template.php': 'Development Edition Alpha 1',
	'DefaultManageSmileys.template.php': 'Development Edition Alpha 1',
	'DefaultMemberlist.template.php': 'Development Edition Alpha 1',
	'DefaultMessageIndex.template.php': 'Development Edition Alpha 1',
	'DefaultModlog.template.php': 'Development Edition Alpha 1',
	'DefaultMoveTopic.template.php': 'Development Edition Alpha 1',
	'DefaultNotify.template.php': 'Development Edition Alpha 1',
	'DefaultPackages.template.php': 'Development Edition Alpha 1',
	'DefaultPersonalMessage.template.php': 'Development Edition Alpha 1',
	'DefaultPoll.template.php': 'Development Edition Alpha 1',
	'DefaultPost.template.php': 'Development Edition Alpha 1',
	'DefaultPrintpage.template.php': 'Development Edition Alpha 1',
	'DefaultProfile.template.php': 'Development Edition Alpha 1',
	'DefaultRecent.template.php': 'Development Edition Alpha 1',
	'DefaultRegister.template.php': 'Development Edition Alpha 1',
	'DefaultReminder.template.php': 'Development Edition Alpha 1',
	'DefaultReports.template.php': 'Development Edition Alpha 1',
	'DefaultSearch.template.php': 'Development Edition Alpha 1',
	'DefaultSendTopic.template.php': 'Development Edition Alpha 1',
	'DefaultSettings.template.php': 'Development Edition Alpha 1',
	'DefaultSplitTopics.template.php': 'Development Edition Alpha 1',
	'DefaultStats.template.php': 'Development Edition Alpha 1',
	'DefaultThemes.template.php': 'Development Edition Alpha 1',
	'DefaultWho.template.php': 'Development Edition Alpha 1',
	'DefaultWireless.template.php': 'Development Edition Alpha 1',
	'DefaultXml.template.php': 'Development Edition Alpha 1',
	'Defaultindex.template.php': 'Development Edition Alpha 1',
	'TemplatesAdmin.template.php': 'Development Edition Alpha 1',
	'TemplatesBoardIndex.template.php': 'Development Edition Alpha 1',
	'TemplatesCalendar.template.php': 'Development Edition Alpha 1',
	'TemplatesDisplay.template.php': 'Development Edition Alpha 1',
	'TemplatesErrors.template.php': 'Development Edition Alpha 1',
	'TemplatesHelp.template.php': 'Development Edition Alpha 1',
	'TemplatesLogin.template.php': 'Development Edition Alpha 1',
	'TemplatesManageAttachments.template.php': 'Development Edition Alpha 1',
	'TemplatesManageBans.template.php': 'Development Edition Alpha 1',
	'TemplatesManageBoards.template.php': 'Development Edition Alpha 1',
	'TemplatesManageCalendar.template.php': 'Development Edition Alpha 1',
	'TemplatesManageMembergroups.template.php': 'Development Edition Alpha 1',
	'TemplatesManageMembers.template.php': 'Development Edition Alpha 1',
	'TemplatesManageNews.template.php': 'Development Edition Alpha 1',
	'TemplatesManagePermissions.template.php': 'Development Edition Alpha 1',
	'TemplatesManageSearch.template.php': 'Development Edition Alpha 1',
	'TemplatesManageSmileys.template.php': 'Development Edition Alpha 1',
	'TemplatesMemberlist.template.php': 'Development Edition Alpha 1',
	'TemplatesMessageIndex.template.php': 'Development Edition Alpha 1',
	'TemplatesModlog.template.php': 'Development Edition Alpha 1',
	'TemplatesMoveTopic.template.php': 'Development Edition Alpha 1',
	'TemplatesNotify.template.php': 'Development Edition Alpha 1',
	'TemplatesPackages.template.php': 'Development Edition Alpha 1',
	'TemplatesPersonalMessage.template.php': 'Development Edition Alpha 1',
	'TemplatesPoll.template.php': 'Development Edition Alpha 1',
	'TemplatesPost.template.php': 'Development Edition Alpha 1',
	'TemplatesPrintpage.template.php': 'Development Edition Alpha 1',
	'TemplatesProfile.template.php': 'Development Edition Alpha 1',
	'TemplatesRecent.template.php': 'Development Edition Alpha 1',
	'TemplatesRegister.template.php': 'Development Edition Alpha 1',
	'TemplatesReminder.template.php': 'Development Edition Alpha 1',
	'TemplatesReports.template.php': 'Development Edition Alpha 1',
	'TemplatesSearch.template.php': 'Development Edition Alpha 1',
	'TemplatesSendTopic.template.php': 'Development Edition Alpha 1',
	'TemplatesSettings.template.php': 'Development Edition Alpha 1',
	'TemplatesSplitTopics.template.php': 'Development Edition Alpha 1',
	'TemplatesStats.template.php': 'Development Edition Alpha 1',
	'TemplatesThemes.template.php': 'Development Edition Alpha 1',
	'TemplatesWho.template.php': 'Development Edition Alpha 1',
	'TemplatesWireless.template.php': 'Development Edition Alpha 1',
	'TemplatesXml.template.php': 'Development Edition Alpha 1',
	'Templatesindex.template.php': 'Development Edition Alpha 1'
};

window.smfLanguageVersions = {
	'Admin': 'Development Edition Alpha 1',
	'Errors': 'Development Edition Alpha 1',
	'Help': 'Development Edition Alpha 1',
	'index': 'Development Edition Alpha 1',
	'Install': 'Development Edition Alpha 1',
	'Login': 'Development Edition Alpha 1',
	'ManageBoards': 'Development Edition Alpha 1',
	'ManageCalendar': 'Development Edition Alpha 1',
	'ManageMembers': 'Development Edition Alpha 1',
	'ManagePermissions': 'Development Edition Alpha 1',
	'ManageSmileys': 'Development Edition Alpha 1',
	'Manual': 'Development Edition Alpha 1',
	'ModSettings': 'Development Edition Alpha 1',
	'Modifications': 'Development Edition Alpha 1',
	'Packages': 'Development Edition Alpha 1',
	'PersonalMessage': 'Development Edition Alpha 1',
	'Post': 'Development Edition Alpha 1',
	'Profile': 'Development Edition Alpha 1',
	'Reports': 'Development Edition Alpha 1',
	'Search': 'Development Edition Alpha 1',
	'Settings': 'Development Edition Alpha 1',
	'Stats': 'Development Edition Alpha 1',
	'Themes': 'Development Edition Alpha 1',
	'Who': 'Development Edition Alpha 1',
	'Wireless': 'Development Edition Alpha 1'
};
<?php
}
// Normal user?
else
{
?>
window.smfVersions = {
	'SMF': 'SMF 1.1.2',
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
	'SourcesLogInOut.php': '1.1.2',
	'SourcesManageAttachments.php': '1.1',
	'SourcesManageBans.php': '1.1',
	'SourcesManageBoards.php': '1.1.2',
	'SourcesManageCalendar.php': '1.1',
	'SourcesManageErrors.php': '1.1',
	'SourcesManageMembergroups.php': '1.1.2',
	'SourcesManageMembers.php': '1.1',
	'SourcesManageNews.php': '1.1.2',
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
	'SourcesPersonalMessage.php': '1.1.2',
	'SourcesPoll.php': '1.1',
	'SourcesPost.php': '1.1.2',
	'SourcesPrintpage.php': '1.1',
	'SourcesProfile.php': '1.1.2',
	'SourcesQueryString.php': '1.1',
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
	'SourcesSubs.php': '1.1.2',
	'SourcesSubs-Auth.php': '1.1',
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