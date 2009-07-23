
// Handle the JavaScript surrounding the admin and moderation center.
function smf_AdminIndex(oOptions)
{
	this.opt = oOptions;
	this.init();
}

smf_AdminIndex.prototype.init = function ()
{
	window.adminIndexInstanceRef = this;
	var fHandleAdminIndexPageLoaded = function () {
		window.adminIndexInstanceRef.loadAdminIndex();
	}
	addLoadEvent(fHandleAdminIndexPageLoaded);
}

smf_AdminIndex.prototype.loadAdminIndex = function ()
{
	// Load the text box containing the latest news items.
	if (this.opt.bLoadAnnouncements)
		this.setAnnouncements();

	// Load the current SMF and your SMF version numbers.
	if (this.opt.bLoadVersions)
		this.showCurrentVersion();

	// Load the text box that sais there's a new version available.
	if (this.opt.bLoadUpdateNotification)
		this.checkUpdateAvailable();
}


smf_AdminIndex.prototype.setAnnouncements = function ()
{
	if (!('smfAnnouncements' in window) || !('length' in window.smfAnnouncements))
		return;

	var sMessages = '';
	for (var i = 0; i < window.smfAnnouncements.length; i++)
		sMessages += this.opt.sAnnouncementMessageTemplate.replace('%href%', window.smfAnnouncements[i].href).replace('%subject%', window.smfAnnouncements[i].subject).replace('%time%', window.smfAnnouncements[i].time).replace('%message%', window.smfAnnouncements[i].message);

	setInnerHTML(document.getElementById(this.opt.sAnnouncementContainerId), this.opt.sAnnouncementTemplate.replace('%content%', sMessages));
}

smf_AdminIndex.prototype.showCurrentVersion = function ()
{
	if (!('smfVersion' in window))
		return;

	var oSmfVersionContainer = document.getElementById(this.opt.sSmfVersionContainerId);
	var oYourVersionContainer = document.getElementById(this.opt.sYourVersionContainerId);

	setInnerHTML(oSmfVersionContainer, window.smfVersion);

	var sCurrentVersion = getInnerHTML(oYourVersionContainer);
	if (sCurrentVersion != window.smfVersion)
		setInnerHTML(oYourVersionContainer, this.opt.sVersionOutdatedTemplate.replace('%currentVersion%', sCurrentVersion));
}

smf_AdminIndex.prototype.checkUpdateAvailable = function ()
{
	if (!('smfUpdatePackage' in window))
		return;

	var oContainer = document.getElementById(this.opt.sUpdateNotificationContainerId);

	// Are we setting a custom title and message?
	var sTitle = 'smfUpdateTitle' in window ? window.smfUpdateTitle : this.opt.sUpdateNotificationDefaultTitle;
	var sMessage = 'smfUpdateNotice' in window ? window.smfUpdateNotice : this.opt.sUpdateNotificationDefaultMessage;

	setInnerHTML(oContainer, this.opt.sUpdateNotificationTemplate.replace('%title%', sTitle).replace('%message%', sMessage));

	// Parse in the package download URL if it exists in the string.
	document.getElementById('update-link').href = this.opt.sUpdateNotificationLink.replace('%package%', window.smfUpdatePackage);

	// If we decide to override life into "red" mode, do it.
	if ('smfUpdateCritical' in window)
	{
		document.getElementById('update_table').style.backgroundColor = '#aa2222';
		document.getElementById('update_title').style.backgroundColor = '#dd2222';
		document.getElementById('update_title').style.color = 'white';
		document.getElementById('update_message').style.backgroundColor = '#eebbbb';
		document.getElementById('update_message').style.color = 'black';
	}
}