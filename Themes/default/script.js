var smf_formSubmitted = false;
var lastKeepAliveCheck = new Date().getTime();
var smf_editorArray = new Array();

// Define document.getElementById for Internet Explorer 4.
if (typeof(document.getElementById) == "undefined")
	document.getElementById = function (id)
	{
		// Just return the corresponding index of all.
		return document.all[id];
	}
// Define XMLHttpRequest for IE 5 and above. (don't bother for IE 4 :/.... works in Opera 7.6 and Safari 1.2!)
else if (!window.XMLHttpRequest && window.ActiveXObject)
	window.XMLHttpRequest = function ()
	{
		return new ActiveXObject(navigator.userAgent.indexOf("MSIE 5") != -1 ? "Microsoft.XMLHTTP" : "MSXML2.XMLHTTP");
	};

// Some older versions of Mozilla don't have this, for some reason.
if (typeof(document.forms) == "undefined")
	document.forms = document.getElementsByTagName("form");

// Some very basic browser detection.
var smf_browser = 'unknown';
if (navigator.userAgent.indexOf("Firefox") != -1)
	smf_browser = 'firefox';
else if (navigator.appVersion.indexOf("MSIE") != -1)
{
	temp = navigator.appVersion.split("MSIE");
	version = parseFloat(temp[1]);

	if (version >= 5.5)
		smf_browser = 'ie'
	else
		smf_browser = 'ie5';
}
else if  (navigator.appVersion.indexOf("Opera") != -1)
	smf_browser = 'opera';

// Load an XML document using XMLHttpRequest.
function getXMLDocument(sUrl, funcCallback)
{
	if (!window.XMLHttpRequest)
		return null;

	var oMyDoc = new XMLHttpRequest();
	var bAsync = typeof(funcCallback) != 'undefined';
	var oCaller = this;
	if (bAsync)
	{
		oMyDoc.onreadystatechange = function ()
		{
			if (oMyDoc.readyState != 4)
				return;

			if (oMyDoc.responseXML != null && oMyDoc.status == 200)
				funcCallback.call(oCaller, oMyDoc.responseXML);
		};
	}
	oMyDoc.open('GET', sUrl, bAsync);
	oMyDoc.send(null);

	return oMyDoc;
}

// Send a post form to the server using XMLHttpRequest.
function sendXMLDocument(sUrl, sContent, funcCallback)
{
	if (!window.XMLHttpRequest)
		return false;

	var sendDoc = new window.XMLHttpRequest();
	var oCaller = this;
	if (typeof(funcCallback) != 'undefined')
	{
		sendDoc.onreadystatechange = function ()
		{
			if (sendDoc.readyState != 4)
				return;

			if (sendDoc.responseXML != null && sendDoc.status == 200)
				funcCallback.call(oCaller, sendDoc.responseXML);
			else
				funcCallback.call(oCaller, false);
		};
	}
	sendDoc.open('POST', sUrl, true);
	if (typeof(sendDoc.setRequestHeader) != 'undefined')
		sendDoc.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
	sendDoc.send(sContent);

	return true;
}

function textToEntities(text)
{
	var entities = "";
	for (var i = 0; i < text.length; i++)
	{
		if (text.charCodeAt(i) > 127)
			entities += "&#" + text.charCodeAt(i) + ";";
		else
			entities += text.charAt(i);
	}

	return entities;
}

// Open a new window.
function reqWin(desktopURL, alternateWidth, alternateHeight, noScrollbars)
{
	if ((alternateWidth && self.screen.availWidth * 0.8 < alternateWidth) || (alternateHeight && self.screen.availHeight * 0.8 < alternateHeight))
	{
		noScrollbars = false;
		alternateWidth = Math.min(alternateWidth, self.screen.availWidth * 0.8);
		alternateHeight = Math.min(alternateHeight, self.screen.availHeight * 0.8);
	}
	else
		noScrollbars = typeof(noScrollbars) != "undefined" && noScrollbars == true;

	window.open(desktopURL, 'requested_popup', 'toolbar=no,location=no,status=no,menubar=no,scrollbars=' + (noScrollbars ? 'no' : 'yes') + ',width=' + (alternateWidth ? alternateWidth : 480) + ',height=' + (alternateHeight ? alternateHeight : 220) + ',resizable=no');

	// Return false so the click won't follow the link ;).
	return false;
}

// Remember the current position.
function storeCaret(text)
{
	// Only bother if it will be useful.
	if (typeof(text.createTextRange) != "undefined")
		text.caretPos = document.selection.createRange().duplicate();
}

// Replaces the currently selected text with the passed text.
function replaceText(text, textarea)
{
	// Attempt to create a text range (IE).
	if (typeof(textarea.caretPos) != "undefined" && textarea.createTextRange)
	{
		var caretPos = textarea.caretPos;

		caretPos.text = caretPos.text.charAt(caretPos.text.length - 1) == ' ' ? text + ' ' : text;
		caretPos.select();
	}
	// Mozilla text range replace.
	else if (typeof(textarea.selectionStart) != "undefined")
	{
		var begin = textarea.value.substr(0, textarea.selectionStart);
		var end = textarea.value.substr(textarea.selectionEnd);
		var scrollPos = textarea.scrollTop;

		textarea.value = begin + text + end;

		if (textarea.setSelectionRange)
		{
			textarea.focus();
			textarea.setSelectionRange(begin.length + text.length, begin.length + text.length);
		}
		textarea.scrollTop = scrollPos;
	}
	// Just put it on the end.
	else
	{
		textarea.value += text;
		textarea.focus(textarea.value.length - 1);
	}
}

// Surrounds the selected text with text1 and text2.
function surroundText(text1, text2, textarea)
{
	// Can a text range be created?
	if (typeof(textarea.caretPos) != "undefined" && textarea.createTextRange)
	{
		var caretPos = textarea.caretPos, temp_length = caretPos.text.length;

		caretPos.text = caretPos.text.charAt(caretPos.text.length - 1) == ' ' ? text1 + caretPos.text + text2 + ' ' : text1 + caretPos.text + text2;

		if (temp_length == 0)
		{
			caretPos.moveStart("character", -text2.length);
			caretPos.moveEnd("character", -text2.length);
			caretPos.select();
		}
		else
			textarea.focus(caretPos);
	}
	// Mozilla text range wrap.
	else if (typeof(textarea.selectionStart) != "undefined")
	{
		var begin = textarea.value.substr(0, textarea.selectionStart);
		var selection = textarea.value.substr(textarea.selectionStart, textarea.selectionEnd - textarea.selectionStart);
		var end = textarea.value.substr(textarea.selectionEnd);
		var newCursorPos = textarea.selectionStart;
		var scrollPos = textarea.scrollTop;

		textarea.value = begin + text1 + selection + text2 + end;

		if (textarea.setSelectionRange)
		{
			if (selection.length == 0)
				textarea.setSelectionRange(newCursorPos + text1.length, newCursorPos + text1.length);
			else
				textarea.setSelectionRange(newCursorPos, newCursorPos + text1.length + selection.length + text2.length);
			textarea.focus();
		}
		textarea.scrollTop = scrollPos;
	}
	// Just put them on the end, then.
	else
	{
		textarea.value += text1 + text2;
		textarea.focus(textarea.value.length - 1);
	}
}

// Checks if the passed input's value is nothing.
function isEmptyText(theField)
{
	// Copy the value so changes can be made..
	var theValue = theField.value;

	// Strip whitespace off the left side.
	while (theValue.length > 0 && (theValue.charAt(0) == ' ' || theValue.charAt(0) == '\t'))
		theValue = theValue.substring(1, theValue.length);
	// Strip whitespace off the right side.
	while (theValue.length > 0 && (theValue.charAt(theValue.length - 1) == ' ' || theValue.charAt(theValue.length - 1) == '\t'))
		theValue = theValue.substring(0, theValue.length - 1);

	if (theValue == '')
		return true;
	else
		return false;
}

// Only allow form submission ONCE.
function submitonce(theform)
{
	smf_formSubmitted = true;

	// If there are any editors warn them submit is coming!
	for (var i = 0; i < smf_editorArray.length; i++)
		smf_editorArray[i].doSubmit();
}
function submitThisOnce(form)
{
	// Hateful, hateful fix for Safari 1.3 beta.
	if (navigator.userAgent.indexOf('AppleWebKit') != -1)
		return !smf_formSubmitted;

	if (typeof(form.form) != "undefined")
		form = form.form;

	for (var i = 0; i < form.length; i++)
		if (typeof(form[i]) != "undefined" && form[i].tagName.toLowerCase() == "textarea")
			form[i].readOnly = true;

	return !smf_formSubmitted;
}

// Set the "inside" HTML of an element.
function setInnerHTML(element, toValue)
{
	// IE has this built in...
	if (typeof(element.innerHTML) != 'undefined')
		element.innerHTML = toValue;
	// Otherwise, try createContextualFragment().
	else
	{
		var range = document.createRange();
		range.selectNodeContents(element);
		range.deleteContents();
		element.appendChild(range.createContextualFragment(toValue));
	}
}

// Set the "outer" HTML of an element.
function setOuterHTML(element, toValue)
{
	if (typeof(element.outerHTML) != 'undefined')
		element.outerHTML = toValue;
	else
	{
		var range = document.createRange();
		range.setStartBefore(element);
		element.parentNode.replaceChild(range.createContextualFragment(toValue), element);
	}
}

// Get the inner HTML of an element.
function getInnerHTML(element)
{
	if (typeof(element.innerHTML) != 'undefined')
		return element.innerHTML;
	else
	{
		var returnStr = '';
		for (var i = 0; i < element.childNodes.length; i++)
			returnStr += getOuterHTML(element.childNodes[i]);

		return returnStr;
	}
}

function getOuterHTML(node)
{
	if (typeof(node.outerHTML) != 'undefined')
		return node.outerHTML;

	var str = '';

	switch (node.nodeType)
	{
	// An element.
	case 1:
		str += '<' + node.nodeName;

		for (var i = 0; i < node.attributes.length; i++)
		{
			if (node.attributes[i].nodeValue != null)
				str += ' ' + node.attributes[i].nodeName + '="' + node.attributes[i].nodeValue + '"';
		}

		if (node.childNodes.length == 0 && in_array(node.nodeName.toLowerCase(), ['hr', 'input', 'img', 'link', 'meta', 'br']))
			str += ' />';
		else
			str += '>' + getInnerHTML(node) + '</' + node.nodeName + '>';
		break;

	// 2 is an attribute.

	// Just some text..
	case 3:
		str += node.nodeValue;
		break;

	// A CDATA section.
	case 4:
		str += '<![CDATA' + '[' + node.nodeValue + ']' + ']>';
		break;

	// Entity reference..
	case 5:
		str += '&' + node.nodeName + ';';
		break;

	// 6 is an actual entity, 7 is a PI.

	// Comment.
	case 8:
		str += '<!--' + node.nodeValue + '-->';
		break;
	}

	return str;
}

// Checks for variable in theArray.
function in_array(variable, theArray)
{
	for (var i = 0; i < theArray.length; i++)
	{
		if (theArray[i] == variable)
			return true;
	}
	return false;
}

// Find a specific radio button in its group and select it.
function selectRadioByName(radioGroup, name)
{
	if (typeof(radioGroup.length) == "undefined")
		return radioGroup.checked = true;

	for (var i = 0; i < radioGroup.length; i++)
	{
		if (radioGroup[i].value == name)
			return radioGroup[i].checked = true;
	}

	return false;
}

// Invert all checkboxes at once by clicking a single checkbox.
function invertAll(headerfield, checkform, mask, ignore_disabled)
{
	for (var i = 0; i < checkform.length; i++)
	{
		if (typeof(checkform[i].name) == "undefined" || (typeof(mask) != "undefined" && checkform[i].name.substr(0, mask.length) != mask))
			continue;

		if (!checkform[i].disabled || typeof(ignore_disabled) != "undefined")
			checkform[i].checked = headerfield.checked;
	}
}

// Keep the session alive - always!
var lastKeepAliveCheck = new Date().getTime();
function smf_sessionKeepAlive()
{
	var curTime = new Date().getTime();

	// Prevent a Firefox bug from hammering the server.
	if (smf_scripturl && curTime - lastKeepAliveCheck > 900000)
	{
		var tempImage = new Image();
		tempImage.src = smf_scripturl + (smf_scripturl.indexOf("?") == -1 ? "?" : "&") + "action=keepalive;" + curTime;
		lastKeepAliveCheck = curTime;
	}

	window.setTimeout("smf_sessionKeepAlive();", 1200000);
}
window.setTimeout("smf_sessionKeepAlive();", 1200000);

// Set a theme option through javascript.
function smf_setThemeOption(option, value, theme, cur_session_id)
{
	// Compatibility.
	if (cur_session_id == null)
		cur_session_id = smf_session_id;

	var tempImage = new Image();
	tempImage.src = smf_scripturl + (smf_scripturl.indexOf("?") == -1 ? "?" : "&") + "action=jsoption;var=" + option + ";val=" + value + ";sesc=" + cur_session_id + (theme == null ? "" : "&id=" + theme) + ";" + (new Date().getTime());
}

function smf_avatarResize()
{
	var possibleAvatars = document.getElementsByTagName ? document.getElementsByTagName("img") : document.all.tags("img");

	for (var i = 0; i < possibleAvatars.length; i++)
	{
		if (possibleAvatars[i].className != "avatar")
			continue;

		var tempAvatar = new Image();
		tempAvatar.src = possibleAvatars[i].src;

		if (smf_avatarMaxWidth != 0 && tempAvatar.width > smf_avatarMaxWidth)
		{
			possibleAvatars[i].height = (smf_avatarMaxWidth * tempAvatar.height) / tempAvatar.width;
			possibleAvatars[i].width = smf_avatarMaxWidth;
		}
		else if (smf_avatarMaxHeight != 0 && tempAvatar.height > smf_avatarMaxHeight)
		{
			possibleAvatars[i].width = (smf_avatarMaxHeight * tempAvatar.width) / tempAvatar.height;
			possibleAvatars[i].height = smf_avatarMaxHeight;
		}
		else
		{
			possibleAvatars[i].width = tempAvatar.width;
			possibleAvatars[i].height = tempAvatar.height;
		}
	}

	if (typeof(window_oldAvatarOnload) != "undefined" && window_oldAvatarOnload)
	{
		window_oldAvatarOnload();
		window_oldAvatarOnload = null;
	}
}

function hashLoginPassword(doForm, cur_session_id)
{
	// Compatibility.
	if (cur_session_id == null)
		cur_session_id = smf_session_id;

	if (typeof(hex_sha1) == "undefined")
		return;
	// Are they using an email address?
	if (doForm.user.value.indexOf("@") != -1)
		return;

	// Unless the browser is Opera, the password will not save properly.
	if (typeof(window.opera) == "undefined")
		doForm.passwrd.autocomplete = "off";

	doForm.hash_passwrd.value = hex_sha1(hex_sha1(doForm.user.value.toLowerCase() + doForm.passwrd.value) + cur_session_id);

	// It looks nicer to fill it with asterisks, but Firefox will try to save that.
	if (navigator.userAgent.indexOf("Firefox/") != -1)
		doForm.passwrd.value = "";
	else
		doForm.passwrd.value = doForm.passwrd.value.replace(/./g, "*");
}

function hashAdminPassword(doForm, username, cur_session_id)
{
	// Compatibility.
	if (cur_session_id == null)
		cur_session_id = smf_session_id;

	if (typeof(hex_sha1) == "undefined")
		return;

	doForm.admin_hash_pass.value = hex_sha1(hex_sha1(username.toLowerCase() + doForm.admin_pass.value) + cur_session_id);
	doForm.admin_pass.value = doForm.admin_pass.value.replace(/./g, "*");
}

// Shows the page numbers by clicking the dots (in compact view).
function expandPages(spanNode, baseURL, firstPage, lastPage, perPage)
{
	var replacement = '', i, oldLastPage = 0;

	// The dots were bold, the page numbers are not (in most cases).
	spanNode.style.fontWeight = 'normal';
	spanNode.onclick = '';

	// Prevent too many pages to be loaded at once.
	if ((lastPage - firstPage) / perPage > 1000)
	{
		oldLastPage = lastPage;
		lastPage = firstPage + 1000 * perPage;
	}

	// Calculate the new pages.
	for (i = firstPage; i < lastPage; i += perPage)
		replacement += '<a class="navPages" href="' + baseURL.replace(/%d/, i) + '">' + (1 + i / perPage) + '</a> ';

	if (oldLastPage > 0)
		replacement += '<span style="font-weight: bold; cursor: pointer;" onclick="expandPages(this, \'' + baseURL + '\', ' + lastPage + ', ' + oldLastPage + ', ' + perPage + ');"> ... </span> ';

	// Replace the dots by the new page links.
	setInnerHTML(spanNode, replacement);
}

// An show/hide object - like a header.
function smfToggle(uniqueId, initialState)
{
	this.uid = uniqueId;
	this.state = initialState;
	this.use_cookie = 0;
	// Needed for setting theme options - kept hidden!
	var themeOptions = Array(3);
	themeOptions[0] = null;
	this.useCookie = useCookie;
	this.toggle = toggleHeader;
	this.setOptions = setOptions;
	this.imageToggles = new Array();
	this.panelToggles = new Array();
	this.addToggleImage = addToggleImage;
	this.addTogglePanel = addTogglePanel;

	// Should the shrinker use a cookie?
	function useCookie(mode)
	{
		this.use_cookie = mode ? 1 : 0;
	}

	// Actually shrink the header!
	function toggleHeader(mode)
	{
		// Just a toggle?
		if (mode == null)
			mode = !this.state;

		// Do we need to set a cookie?
		if (this.use_cookie)
			document.cookie = this.uid + '=' + (mode ? 1 : 0);

		// Set a theme option?
		if (themeOptions[0] != null)
		{
			var curMode = themeOptions[2] ? !mode : mode;
			smf_setThemeOption(themeOptions[0], mode ? 1 : 0, null, themeOptions[1]);
		}

		// Toggle the images.
		var x = 0;
		for (x = 0; x < this.imageToggles.length; x++)
		{
			document.getElementById(this.imageToggles[x][0]).src = mode ? this.imageToggles[x][2] : this.imageToggles[x][1];
		}

		// Now toggle the panels.
		for (x = 0; x < this.panelToggles.length; x++)
		{
			// Inverse?
			var curMode = this.panelToggles[x][1] ? !mode : mode;
			document.getElementById(this.panelToggles[x][0]).style.display = curMode ? "none" : "";
		}

		this.state = mode;
	}

	// Set the theme option that should change with this.
	function setOptions(newThemeOptions, sessID, flip)
	{
		themeOptions[0] = newThemeOptions;
		themeOptions[1] = sessID;
		themeOptions[2] = flip == null ? 0 : 1;
	}

	// Add an image to toggle (id, mode = 0 image, mode = 1 image)
	function addToggleImage(imageID, mode0Image, mode1Image, useImagePath)
	{
		var curIndex = this.imageToggles.length;
		this.imageToggles[curIndex] = Array(3);
		this.imageToggles[curIndex][0] = imageID;
		this.imageToggles[curIndex][1] = (useImagePath == null ? smf_images_url : '') + mode0Image;
		this.imageToggles[curIndex][2] = (useImagePath == null ? smf_images_url : '') + mode1Image;
	}

	// Add a panel which should toggle with the header.
	function addTogglePanel(panelID, flip)
	{
		var curIndex = this.panelToggles.length;
		this.panelToggles[curIndex] = Array(2);
		this.panelToggles[curIndex][0] = panelID;
		this.panelToggles[curIndex][1] = flip == null ? 0 : 1;
	}
}

function ajax_indicator(turn_on)
{
	var indicator = document.getElementById("ajax_in_progress");
	if (indicator != null)
	{
		if (navigator.appName == "Microsoft Internet Explorer" && navigator.userAgent.indexOf("MSIE 7") == -1)
		{
			indicator.style.top = document.documentElement.scrollTop;
		}
		indicator.style.display = turn_on ? "block" : "none";
	}
}

// Mimics the PHP version of this function.
function smf_htmlspecialchars(text)
{
	text = text.replace(/&/g, '&amp;');
	text = text.replace(/</g, '&lt;');
	text = text.replace(/>/g, '&gt;');
	text = text.replace(/"/g, '&quot;');

	return text;
}

// Mimics the PHP version of this function - like the above.
function smf_unhtmlspecialchars(text)
{
	text = text.replace(/&amp;/g, '&');
	text = text.replace(/&lt;/g, '<');
	text = text.replace(/&gt;/g, '>');
	text = text.replace(/&quot;/g, '"');

	return text;
}

function createAddEventListener(oTarget)
{
	if (typeof(oTarget.addEventListener) == 'undefined')
	{
		if (oTarget.attachEvent)
		{
			oTarget.addEventListener = function (sEvent, funcHandler, bCapture)
			{
				oTarget.attachEvent("on" + sEvent, funcHandler);
			}
		}
		else
		{
			oTarget.addEventListener = function (sEvent, funcHandler, bCapture) 
			{
				oTarget["on" + sEvent] = funcHandler;
			}
		}
	}
}

// This function will retrieve the contents needed for the jump to boxes.
function grabJumpToContent()
{
	var oXMLDoc = getXMLDocument(smf_scripturl + "?action=xmlhttp;sa=jumpto;xml");
	var aBoardsAndCategories = new Array();

	ajax_indicator(true);

	if (oXMLDoc.responseXML)
	{
		var items = oXMLDoc.responseXML.getElementsByTagName('smf')[0].getElementsByTagName('item');
		for (var i = 0, n = items.length; i < n; i++)
		{
			aBoardsAndCategories[aBoardsAndCategories.length] = {
				id: parseInt(items[i].getAttribute('id')),
				isCategory: items[i].getAttribute('type') == 'category',
				name: items[i].firstChild.nodeValue,
				is_current: false,
				childLevel: parseInt(items[i].getAttribute('childlevel'))
			}
		}
	}

	ajax_indicator(false);

	for (var i = 0, n = aJumpTo.length; i < n; i++)
		aJumpTo[i].fillSelect(aBoardsAndCategories);
}

// This'll contain all JumpTo objects on the page.
var aJumpTo = new Array();

// JumpTo class.
function JumpTo(oJumpToOptions)
{
	this.opt = oJumpToOptions;
	this.dropdownList = null;
	this.showSelect();
}

// Show the initial select box (onload). Method of the JumpTo class.
JumpTo.prototype.showSelect = function ()
{
	var sChildLevelPrefix = '';
	for (var i = this.opt.iCurBoardChildLevel; i > 0; i--)
		sChildLevelPrefix += this.opt.sBoardChildLevelIndicator;
	setInnerHTML(document.getElementById(this.opt.sContainerId), this.opt.sJumpToTemplate.replace(/%select_id%/, this.opt.sContainerId + '_select').replace(/%dropdown_list%/, '<select name="' + this.opt.sContainerId + '_select" id="' + this.opt.sContainerId + '_select" ' + (typeof(document.implementation) == 'undefined' ? 'onmouseover="grabJumpToContent();" ' : '') + (typeof(document.onbeforeactivate) == 'undefined' ? 'onfocus' : 'onbeforeactivate') + '="grabJumpToContent();"><option value="?board=' + this.opt.iCurBoardId + '.0">' + sChildLevelPrefix + this.opt.sBoardPrefix + this.opt.sCurBoardName + '</option></select>'));
	this.dropdownList = document.getElementById(this.opt.sContainerId + '_select');
}

// Fill the jump to box with entries. Method of the JumpTo class.
JumpTo.prototype.fillSelect = function (aBoardsAndCategories)
{
	var bIE5x = typeof(document.implementation) == 'undefined';
	var iIndexPointer = 0;

	// Create an option that'll be above and below the category.
	var oDashOption = document.createElement('option');
	oDashOption.appendChild(document.createTextNode(this.opt.sCatSeparator));
	oDashOption.disabled = 'disabled';
	oDashOption.value = '';

	// Reset the events and clear the list (IE5.x only).
	if (bIE5x)
	{
		this.dropdownList.onmouseover = null;
		this.dropdownList.remove(0);
	}
	if (typeof(document.onbeforeactivate) == 'undefined')
		this.dropdownList.onfocus = null;
	else
		this.dropdownList.onbeforeactivate = null;

	// Create a document fragment that'll allowing inserting big parts at once.
	var oListFragment = bIE5x ? this.dropdownList : document.createDocumentFragment();

	// Loop through all items to be added.
	for (var i = 0, n = aBoardsAndCategories.length; i < n; i++)
	{
		var j, sChildLevelPrefix, oOption;

		// If we've reached the currently selected board add all items so far.
		if (aBoardsAndCategories[i].id == this.opt.iCurBoardId && !bIE5x)
		{
			this.dropdownList.insertBefore(oListFragment, this.dropdownList.options[0]);
			oListFragment = document.createDocumentFragment();
			continue;
		}
		else if (aBoardsAndCategories[i].id == this.opt.iCurBoardId && bIE5x)
			iIndexPointer = this.dropdownList.options.length;

		
		if (aBoardsAndCategories[i].isCategory)
			oListFragment.appendChild(oDashOption.cloneNode(true));
		else
			for (j = aBoardsAndCategories[i].childLevel, sChildLevelPrefix = ''; j > 0; j--)
				sChildLevelPrefix += this.opt.sBoardChildLevelIndicator;

		oOption = document.createElement('option');
		oOption.appendChild(document.createTextNode((aBoardsAndCategories[i].isCategory ? this.opt.sCatPrefix : sChildLevelPrefix + this.opt.sBoardPrefix) + aBoardsAndCategories[i].name));
		oOption.value = aBoardsAndCategories[i].isCategory ? '#' + aBoardsAndCategories[i].id : '?board=' + aBoardsAndCategories[i].id + '.0';
		oListFragment.appendChild(oOption);

		if (aBoardsAndCategories[i].isCategory)
			oListFragment.appendChild(oDashOption.cloneNode(true));
	}

	// Add the remaining items after the currently selected item.
	this.dropdownList.appendChild(oListFragment);

	if (bIE5x)
		this.dropdownList.options[iIndexPointer].selected = true;

	// Internet Explorer needs this to keep the box dropped down.
	this.dropdownList.style.width = 'auto';
	this.dropdownList.focus();

	// Add an onchange action
	this.dropdownList.onchange = function()
	{
		if (this.selectedIndex > 0 && this.options[this.selectedIndex].value)
			window.location.href = smf_scripturl + this.options[this.selectedIndex].value.substr(smf_scripturl.indexOf('?') == -1 || this.options[this.selectedIndex].value.substr(0, 1) != '?' ? 0 : 1);
	}
}