var smf_formSubmitted = false;
var lastKeepAliveCheck = new Date().getTime();
var smf_editorArray = new Array();

// Some very basic browser detection - from Mozilla's sniffer page.
var ua = navigator.userAgent.toLowerCase();

var is_opera = ua.indexOf("opera") != -1;
var is_opera5 = ua.indexOf("opera/5") != -1 || ua.indexOf("opera 5") != -1;
var is_opera6 = ua.indexOf("opera/6") != -1 || ua.indexOf("opera 6") != -1;
var is_opera7 = ua.indexOf("opera/7") != -1 || ua.indexOf("opera 7") != -1;
var is_opera8 = ua.indexOf("opera/8") != -1 || ua.indexOf("opera 8") != -1;
var is_opera9 = ua.indexOf("opera/9") != -1 || ua.indexOf("opera 9") != -1;
var is_opera95 = ua.indexOf("opera/9.5") != -1 || ua.indexOf("opera 9.5") != -1;
var is_opera95up = is_opera95;

var is_ff = ua.indexOf("firefox") != -1 && !is_opera;
var is_gecko = ua.indexOf('gecko') != -1 && !is_opera;

var is_safari13beta = ua.indexOf('applewebkit') != -1;

var is_ie = ua.indexOf("msie") != -1  && !is_opera;
var is_ie4 = is_ie && ua.indexOf("msie 4") != -1;
var is_ie5 = is_ie && ua.indexOf("msie 5") != -1;
var is_ie50 = is_ie && ua.indexOf("msie 5.0") != -1;
var is_ie55 = is_ie && ua.indexOf("msie 5.5") != -1;
var is_ie5up = is_ie && !is_ie4;
var is_ie6 = is_ie && ua.indexOf("msie 6") != -1;
var is_ie6up = is_ie5up && !is_ie55 && !is_ie5;
var is_ie7 = is_ie && ua.indexOf("msie 7") != -1;
var is_ie7up = is_ie6up && !is_ie6;

var ajax_indicator_ele = null;

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
		return new ActiveXObject(is_ie5 ? "Microsoft.XMLHTTP" : "MSXML2.XMLHTTP");
	};

// Ensure the getElementsByTagName exists.
if (typeof(document.getElementsByTagName) == "undefined")
	document.getElementsByTagName = function (name)
	{
		// Just return the tags with this name.
		return document.all.tags[name];
	}

// Some older versions of Mozilla don't have this, for some reason.
if (typeof(document.forms) == "undefined")
	document.forms = document.getElementsByTagName("form");

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
			{
				if (funcCallback.call)
				{
					funcCallback.call(oCaller, oMyDoc.responseXML);
				}
				// A primitive substitute for the call method to support IE 5.0.
				else
				{
					oCaller.tmpMethod = funcCallback;
					oCaller.tmpMethod(oMyDoc.responseXML);
					delete oCaller.tmpMethod;
				}
			}
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
	var entities = '';
	if (smf_charset == 'UTF-8')
	{
		for (var i = 0; i < text.length; i++)
		{
			var iCharCode = text.charCodeAt(i);

			if (iCharCode < 128)
				entities += text.charAt(i);
			else if (iCharCode < 2048)
				entities += String.fromCharCode(192 | iCharCode >> 6, 128 | iCharCode & 63);
			else if (iCharCode < 65536)
				entities += String.fromCharCode(224 | iCharCode >> 12, 128 | iCharCode >> 6 & 63, 128 | iCharCode & 63);
			else
				entities += String.fromCharCode(240 | iCharCode >> 18, 128 | iCharCode >> 12 & 63, 128 | iCharCode >> 6 & 63, 128 | iCharCode & 63);
		}
	}
	else
	{
		for (var i = 0; i < text.length; i++)
		{
			if (text.charCodeAt(i) > 127)
				entities += "&#" + text.charCodeAt(i) + ";";
			else
				entities += text.charAt(i);
		}
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
	if (is_safari13beta)
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
	for (var i in theArray)
		if (theArray[i] == variable)
			return true;

	return false;
}

// Checks for variable in theArray.
function array_search(variable, theArray)
{
	for (var i in theArray)
		if (theArray[i] == variable)
			return i;

	return null;
}

// Find a specific radio button in its group and select it.
function selectRadioByName(radioGroup, name)
{
	if (typeof(radioGroup.length) == "undefined")
		return radioGroup.checked = true;

	for (var i = 0; i < radioGroup.length; i++)
		if (radioGroup[i].value == name)
			return radioGroup[i].checked = true;

	return false;
}

// Invert all checkboxes at once by clicking a single checkbox.
function invertAll(headerfield, checkform, mask, ignore_disabled)
{
	for (var i = 0; i < checkform.length; i++)
	{
		if (typeof(checkform[i].name) == "undefined" || (typeof(mask) != "undefined" && checkform[i].name.substr(0, mask.length) != mask && checkform[i].id.substr(0, mask.length) != mask))
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
		tempImage.src = smf_scripturl + (smf_scripturl.indexOf('?') == -1 ? '?' : '&') + 'action=keepalive;' + curTime;
		lastKeepAliveCheck = curTime;
	}

	window.setTimeout("smf_sessionKeepAlive();", 1200000);
}
window.setTimeout("smf_sessionKeepAlive();", 1200000);

// Set a theme option through javascript.
function smf_setThemeOption(option, value, theme, cur_session_id, additional_vars)
{
	// Compatibility.
	if (cur_session_id == null)
		cur_session_id = smf_session_id;

	if (additional_vars == null)
		additional_vars = '';

	var tempImage = new Image();
	tempImage.src = smf_scripturl + (smf_scripturl.indexOf("?") == -1 ? "?" : "&") + "action=jsoption;var=" + option + ";val=" + value + ";sesc=" + cur_session_id + additional_vars + (theme == null ? "" : "&id=" + theme) + ";" + (new Date().getTime());
}

function smf_avatarResize()
{
	var possibleAvatars = document.getElementsByTagName("img");

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

	doForm.hash_passwrd.value = hex_sha1(hex_sha1(doForm.user.value.php_to8bit().php_strtolower() + doForm.passwrd.value.php_to8bit()) + cur_session_id);

	// It looks nicer to fill it with asterisks, but Firefox will try to save that.
	if (is_ff != -1)
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
	if ((lastPage - firstPage) / perPage > 100)
	{
		oldLastPage = lastPage;
		lastPage = firstPage + 100 * perPage;
	}

	// Calculate the new pages.
	for (i = firstPage; i < lastPage; i += perPage)
		replacement += '<a class="navPages" href="' + baseURL.replace(/%d/, i) + '">' + (1 + i / perPage) + '</a> ';

	if (oldLastPage > 0)
		replacement += '<span style="font-weight: bold; cursor: ' + (is_ie && !is_ie6up ? 'hand' : 'pointer') + ';" onclick="expandPages(this, \'' + baseURL + '\', ' + lastPage + ', ' + oldLastPage + ', ' + perPage + ');"> ... </span> ';

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
	var themeOptions = Array(5);
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
			smf_setThemeOption(themeOptions[0], curMode ? 1 : 0, themeOptions[3] == 0 ? null : themeOptions[3], themeOptions[1], themeOptions[4]);
		}

		// Toggle the images.
		var x = 0;
		for (x = 0; x < this.imageToggles.length; x++)
		{
			var curImage = document.getElementById(this.imageToggles[x][0]);
			if (curImage)
				curImage.src = mode ? this.imageToggles[x][2] : this.imageToggles[x][1];
		}

		// Now toggle the panels.
		for (x = 0; x < this.panelToggles.length; x++)
		{
			// Inverse?
			var curMode = this.panelToggles[x][1] ? !mode : mode;
			var curPanel = document.getElementById(this.panelToggles[x][0]);
			if (curPanel)
				curPanel.style.display = curMode ? "none" : "";
		}

		this.state = mode;
	}

	// Set the theme option that should change with this.
	function setOptions(newThemeOptions, sessID, flip, themeID, preferenceKey)
	{
		themeOptions[0] = newThemeOptions;
		themeOptions[1] = sessID;
		themeOptions[2] = flip == null ? 0 : 1;
		themeOptions[3] = themeID == null ? 0 : themeID;
		themeOptions[4] = preferenceKey == null ? '' : ';admin_key=' + preferenceKey;
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
	if (ajax_indicator_ele == null)
	{
		ajax_indicator_ele = document.getElementById("ajax_in_progress");

		if (ajax_indicator_ele == null && typeof(ajax_notification_text) != null)
		{
			create_ajax_indicator_ele();
		}
	}

	if (ajax_indicator_ele != null)
	{
		if (navigator.appName == "Microsoft Internet Explorer" && !is_ie7up)
		{
			ajax_indicator_ele.style.position = "absolute";
			ajax_indicator_ele.style.top = document.documentElement.scrollTop;
		}

		ajax_indicator_ele.style.display = turn_on ? "block" : "none";
	}
}

function create_ajax_indicator_ele()
{
	// Create the div for the indicator.
	ajax_indicator_ele = document.createElement("div");

	// Set the id so it'll load the style properly.
	ajax_indicator_ele.id = "ajax_in_progress";

	// Add the image in and link to turn it off.
	var cancel_link = document.createElement("a");
	cancel_link.href = "javascript:ajax_indicator(false)";
	var cancel_img = document.createElement("img");
	cancel_img.src = smf_images_url + "/icons/quick_remove.gif";

	if (typeof(ajax_notification_cancel_text) != null)
	{
		cancel_img.alt = ajax_notification_cancel_text;
		cancel_img.title = ajax_notification_cancel_text;
	}

	// Add the cancel link and image to the indicator.
	cancel_link.appendChild(cancel_img);
	ajax_indicator_ele.appendChild(cancel_link);

	// Set the text.  (Note:  You MUST append here and not overwrite.)
	ajax_indicator_ele.innerHTML += ajax_notification_text;

	// Finally attach the element to the body.
	document.body.appendChild(ajax_indicator_ele);

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

// This will escape single quotes, kinda like PHP.
function smf_addslashes(text)
{
	text = text.replace(/'/g, '\\\'');

	return text;
}

function createEventListener(oTarget)
{
	if (typeof(oTarget.addEventListener) == 'undefined')
	{
		if (oTarget.attachEvent)
		{
			oTarget.addEventListener = function (sEvent, funcHandler, bCapture)
			{
				oTarget.attachEvent("on" + sEvent, funcHandler);
			}
			oTarget.removeEventListener = function (sEvent, funcHandler, bCapture)
			{
				oTarget.detachEvent("on" + sEvent, funcHandler);
			}
		}
		else
		{
			oTarget.addEventListener = function (sEvent, funcHandler, bCapture)
			{
				oTarget["on" + sEvent] = funcHandler;
			}
			oTarget.removeEventListener = function (sEvent, funcHandler, bCapture)
			{
				oTarget["on" + sEvent] = null;
			}
		}
	}
}

function findCoords(oElement)
{
	if (typeof(oElement.tagName) == 'undefined')
		return {'iX': 0, 'iY': 0};

	var bSkipMe = in_array(oElement.tagName.toLowerCase(), ['tr', 'form', 'tbody', 'div']);

	if (oElement.parentNode == null || typeof(oElement.parentNode.offsetLeft) == 'undefined')
		return {'iX': bSkipMe ? 0 : oElement.offsetLeft, 'iY': bSkipMe ? 0 : oElement.offsetTop};
	else
	{
		var aAdd = findCoords(oElement.parentNode);
		return {'iX': (bSkipMe ? 0 : oElement.offsetLeft) + aAdd.iX, 'iY': (bSkipMe ? 0 : oElement.offsetTop) + aAdd.iY};
	}
}

// This function will retrieve the contents needed for the jump to boxes.
function grabJumpToContent()
{
	var oXMLDoc = getXMLDocument(smf_prepareScriptUrl(smf_scripturl) + "action=xmlhttp;sa=jumpto;xml");
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

// *** JumpTo class.
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
	setInnerHTML(document.getElementById(this.opt.sContainerId), this.opt.sJumpToTemplate.replace(/%select_id%/, this.opt.sContainerId + '_select').replace(/%dropdown_list%/, '<select name="' + this.opt.sContainerId + '_select" id="' + this.opt.sContainerId + '_select" ' + (typeof(document.implementation) == 'undefined' ? 'onmouseover="grabJumpToContent();" ' : '') + (typeof(document.onbeforeactivate) == 'undefined' ? 'onfocus' : 'onbeforeactivate') + '="grabJumpToContent();"><option value="?board=' + this.opt.iCurBoardId + '.0">' + sChildLevelPrefix + this.opt.sBoardPrefix + this.opt.sCurBoardName + '</option></select>&nbsp;<input type="button" value="' + this.opt.sGoButtonLabel + '" onclick="window.location.href = \'' + smf_scripturl + '?board=' + this.opt.iCurBoardId + '.0\';" />'));
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

// A global array containing all IconList objects.
var aIconLists = new Array();

// *** IconList object.
function IconList(oOptions)
{
	if (!window.XMLHttpRequest)
		return;

	this.opt = oOptions;
	this.bListLoaded = false;
	this.oContainerDiv = null;
	this.funcMousedownHandler = null;
	this.funcParent = this;
	this.iCurMessageId = 0;
	this.iCurTimeout = 0;

	this.initIcons();
}

// Replace all message icons by icons with hoverable and clickable div's.
IconList.prototype.initIcons = function ()
{
	for (var i = document.images.length - 1, iPrefixLength = this.opt.sIconIdPrefix.length; i >= 0; i--)
		if (document.images[i].id.substr(0, iPrefixLength) == this.opt.sIconIdPrefix)
			setOuterHTML(document.images[i], '<div title="' + this.opt.sLabelIconList + '" onclick="' + this.opt.sBackReference + '.openPopup(this, ' + document.images[i].id.substr(iPrefixLength) + ')" onmouseover="' + this.opt.sBackReference + '.onBoxHover(this, true)" onmouseout="' + this.opt.sBackReference + '.onBoxHover(this, false)" style="background: ' + this.opt.sBoxBackground + '; cursor: ' + (is_ie && !is_ie6up ? 'hand' : 'pointer') + '; padding: 3px; text-align: center;"><img src="' + document.images[i].src + '" alt="' + document.images[i].alt + '" style="margin: 0px; padding: ' + (is_ie ? '3px' : '3px 0px 3px 0px') + ';" /></div>');
}

// Event for the mouse hovering over the original icon.
IconList.prototype.onBoxHover = function (oDiv, bMouseOver)
{
	oDiv.style.border = bMouseOver ? this.opt.iBoxBorderWidthHover + 'px solid ' + this.opt.sBoxBorderColorHover : '';
	oDiv.style.background = bMouseOver ? this.opt.sBoxBackgroundHover : this.opt.sBoxBackground;
	oDiv.style.padding = bMouseOver ? (3 - this.opt.iBoxBorderWidthHover) + 'px' : '3px'
}

// Show the list of icons after the user clicked the original icon.
IconList.prototype.openPopup = function (oDiv, iMessageId)
{
	if (!this.bListLoaded && this.oContainerDiv == null)
	{
		// Create a container div.
		this.oContainerDiv = document.createElement('div');
		this.oContainerDiv.id = 'iconList';
		this.oContainerDiv.style.display = 'none';
		this.oContainerDiv.style.cursor = is_ie && !is_ie6up ? 'hand' : 'pointer';
		this.oContainerDiv.style.position = 'absolute';
		this.oContainerDiv.style.width = oDiv.clientWidth + 'px';
		this.oContainerDiv.style.background = this.opt.sContainerBackground;
		this.oContainerDiv.style.border = this.opt.sContainerBorder;
		this.oContainerDiv.style.padding = '1px';
		this.oContainerDiv.style.textAlign = 'center';
		document.body.appendChild(this.oContainerDiv);

		// Start to fetch its contents.
		ajax_indicator(true);
		this.tmpMethod = getXMLDocument;
		this.tmpMethod(smf_prepareScriptUrl(this.opt.sScriptUrl) + 'action=xmlhttp;sa=messageicons;board=' + this.opt.iBoardId + ';xml', this.onIconsReceived);
		delete this.tmpMethod;

		createEventListener(document.body);
	}

	this.iCurMessageId = iMessageId;

	// Set the position of the container.
	var aPos = smf_itemPos(oDiv);
	if (is_ie50)
		aPos[1] += 4;

	this.oContainerDiv.style.top = (aPos[1] + oDiv.offsetHeight) + 'px';
	this.oContainerDiv.style.left = (aPos[0] - 1) + 'px';
	this.oClickedIcon = oDiv;

	if (this.bListLoaded)
		this.oContainerDiv.style.display = 'block';

	document.body.addEventListener('mousedown', this.onWindowMouseDown, false);
}

// Setup the list of icons once it is received through xmlHTTP.
IconList.prototype.onIconsReceived = function (oXMLDoc)
{
	var icons = oXMLDoc.getElementsByTagName('smf')[0].getElementsByTagName('icon');
	var sItems = '';

	for (var i = 0, n = icons.length; i < n; i++)
		sItems += '<div onmouseover="' + this.opt.sBackReference + '.onItemHover(this, true)" onmouseout="' + this.opt.sBackReference + '.onItemHover(this, false);" onmousedown="' + this.opt.sBackReference + '.onItemMouseDown(this, \'' + icons[i].getAttribute('value') + '\');" style="padding: 3px 0px 3px 0px; margin-left: auto; margin-right: auto; border: ' + this.opt.sItemBorder + '; background: ' + this.opt.sItemBackground + '"><img src="' + icons[i].getAttribute('url') + '" alt="' + icons[i].getAttribute('name') + '" title="' + icons[i].firstChild.nodeValue + '" border="0" /></div>';

	setInnerHTML(this.oContainerDiv, sItems);
	this.oContainerDiv.style.display = 'block';
	this.bListLoaded = true;

	if (is_ie)
		this.oContainerDiv.style.width = this.oContainerDiv.clientWidth + 'px';

	ajax_indicator(false);
}

// Event handler for hovering over the icons.
IconList.prototype.onItemHover = function (oDiv, bMouseOver)
{
	oDiv.style.background = bMouseOver ? this.opt.sItemBackgroundHover : this.opt.sItemBackground;
	oDiv.style.border = bMouseOver ? this.opt.sItemBorderHover : this.opt.sItemBorder;
	if (this.iCurTimeout != 0)
		window.clearTimeout(this.iCurTimeout);
	if (bMouseOver)
		this.onBoxHover(this.oClickedIcon, true);
	else
		this.iCurTimeout = window.setTimeout(this.opt.sBackReference + '.collapseList();', 500);
}

// Event handler for clicking on one of the icons.
IconList.prototype.onItemMouseDown = function (oDiv, sNewIcon)
{
	if (this.iCurMessageId != 0)
	{
		ajax_indicator(true);
		this.tmpMethod = getXMLDocument;
		var oXMLDoc = this.tmpMethod(smf_prepareScriptUrl(this.opt.sScriptUrl) + 'action=jsmodify;topic=' + this.opt.iTopicId + ';msg=' + this.iCurMessageId + ';sesc=' + this.opt.sSessionId + ';icon=' + sNewIcon + ';xml');
		delete this.tmpMethod;
		ajax_indicator(false);

		var oMessage = oXMLDoc.responseXML.getElementsByTagName('smf')[0].getElementsByTagName('message')[0];
		if (oMessage.getElementsByTagName('error').length == 0)
		{
			if (this.opt.bShowModify && oMessage.getElementsByTagName('modified').length != 0)
				setInnerHTML(document.getElementById('modified_' + this.iCurMessageId), oMessage.getElementsByTagName('modified')[0].childNodes[0].nodeValue);
			this.oClickedIcon.getElementsByTagName('img')[0].src = oDiv.getElementsByTagName('img')[0].src;
		}
	}
}

// Event handler for clicking outside the list (will make the list disappear).
IconList.prototype.onWindowMouseDown = function ()
{
	for (var i = aIconLists.length - 1; i >= 0; i--)
	{
		aIconLists[i].funcParent.tmpMethod = aIconLists[i].collapseList;
		aIconLists[i].funcParent.tmpMethod();
		delete aIconLists[i].funcParent.tmpMethod;
	}
}

// Collapse the list of icons.
IconList.prototype.collapseList = function()
{
	this.onBoxHover(this.oClickedIcon, false);
	this.oContainerDiv.style.display = 'none';
	this.iCurMessageId = 0;
	document.body.removeEventListener('mousedown', this.onWindowMouseDown, false);
}

// Handy shortcuts for getting the mouse position on the screen - only used for IE at the moment.
function smf_mousePose(evnt)
{
	var x = 0;
	var y = 0;

	if (evnt.pageX)
	{
		y = evnt.pageY;
		x = evnt.pageX;
	}
	else if (evnt.clientX)
	{
		x = evnt.clientX + (document.documentElement.scrollLeft ? document.documentElement.scrollLeft : document.body.scrollLeft);
		y = evnt.clientY + (document.documentElement.scrollTop ? document.documentElement.scrollTop : document.body.scrollTop);
	}

	return [x, y];
}

// Short function for finding the actual position of an item.
function smf_itemPos(itemHandle)
{
	var itemX = 0;
	var itemY = 0;

	if (typeof(itemHandle.offsetParent) != 'undefined')
	{
		itemX = itemHandle.offsetLeft;
		itemY = itemHandle.offsetTop;
		while (itemHandle.offsetParent && typeof(itemHandle.offsetParent) == 'object')
		{
			itemHandle = itemHandle.offsetParent;
			itemX += itemHandle.offsetLeft;
			itemY += itemHandle.offsetTop;
		}
	}
	else if (typeof(itemHandle.x) != 'undefined')
	{
		itemX = itemHandle.x;
		itemY = itemHandle.y;
	}

	return [itemX, itemY];
}

// This function takes the script URL and prepares it to allow the query string to be appended to it.
function smf_prepareScriptUrl(sUrl)
{
	return sUrl.indexOf('?') == -1 ? sUrl + '?' : sUrl + (sUrl.charAt(sUrl.length - 1) == '?' || sUrl.charAt(sUrl.length - 1) == '&' || sUrl.charAt(sUrl.length - 1) == ';' ? '' : ';');
}

var onload_events = new Array();
function add_load_event(func)
{
	// Get the old event if there is one.
	var oldOnload = window.onload;

	// Was the old event really an event?
	if (typeof(oldOnload) != 'function')
	{
		// Since we don't have anything at this point just add it stright in.
		window.onload = func;
	}
	// So it is a function but is it our special function?
	else if(onload_events.length == 0)
	{
		// Nope it is just a regular function...
		onload_events[0] = oldOnload;
		onload_events[1] = func;
		window.onload = function() {
			for (var i=0; i < onload_events.length; i++)
			{
				if (onload_events[i])
				{
					onload_events[i]();
				}
			}
		}
	}
	else
		// Ok just add it to the list of functions to call.
		onload_events[onload_events.length] = func;
}

function smfFooterHighlight(element, value)
{
	element.src = smf_images_url + "/" + (value ? "h_" : "") + element.id + ".gif";
}

// Get the text in a code tag.
function smfSelectText(curElement, actOnElement)
{
	// The place we're looking for is one div up, and next door - if it's auto detect.
	if (actOnElement)
		var codeArea = document.getElementById(curElement);
	else
		var codeArea = curElement.parentNode.nextSibling;

	if (!codeArea)
		return false;

	// Start off with my favourite, internet explorer.
	if (document.body.createTextRange)
	{
		var curRange = document.body.createTextRange();
		curRange.moveToElementText(codeArea);
		curRange.select();
	}
	// Firefox at el.
	else if (window.getSelection)
	{
		var curSelection = window.getSelection();
		// Safari is special!
		if (curSelection.setBaseAndExtent)
		{
			curSelection.setBaseAndExtent(codeArea, 0, codeArea, 1);
		}
		else
		{
			var curRange = document.createRange();
			curRange.selectNodeContents(codeArea);

			curSelection.removeAllRanges();
			curSelection.addRange(curRange);
		}
	}

	return false;
}