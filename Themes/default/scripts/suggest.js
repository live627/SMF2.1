// This file contains javascript associated with a autosuggest control

function smfSuggest(sessionID, textID)
{
	// Store the handle to the text box.
	var textHandle = document.getElementById(textID);
	var suggestDivHandle = document.getElementById('suggest_div_' + textID);
	var lastSearch = "", lastDirtySearch = "";
	var selectedDiv = false;
	var cache = [];
	var displayData = [];
	// How many objects can we show at once?
	var maxDisplayQuantity = 15;
	// How many characters shall we start searching on?
	var minimumSearchChars = 3;

	var onMemberAddCallback = false;
	var doAutoAdd = false;

	var hideTimer = false;
	var positionComplete = false;

	this.forceAutoSuggest = autoSuggestUpdate;
	this.deleteItem = deleteAddedItem;
	this.onSubmit = onElementSubmitted;
	this.registerCallback = registerCallback;

	var xmlRequestHandle = null;

	function init()
	{
		if (!window.XMLHttpRequest)
			return false;

		// Disable autocomplete in IE.
		if (typeof(textHandle.autocomplete) != 'undefined')
			textHandle.autocomplete = 'off';

		createEventListener(textHandle);
		textHandle.addEventListener('keydown', checkEnter, false);
		textHandle.addEventListener('keyup', autoSuggestUpdate, false);
		textHandle.addEventListener('change', autoSuggestUpdate, false);
		textHandle.addEventListener('blur', autoSuggestHide, false);
		textHandle.addEventListener('focus', autoSuggestUpdate, false);

		return true;
	}

	// Was it an enter key - if so assume they are trying to select something.
	function checkEnter(ev)
	{
		if (!ev)
			ev = window.event;

		if (ev.keyCode)
			keyPress = ev.keyCode;
		else if (ev.which)
			keyPress = ev.which;

		if (keyPress == 13)
		{
			if (displayData.length > 0)
			{
				if (selectedDiv != false)
					itemClicked(selectedDiv);
				else
					onElementSubmitted();
			}

			// Do our best to stop it submitting the form!
			if (is_ie && ev.cancelBubble)
				ev.cancelBubble = true;
			else if (ev.stopPropagation)
			{
				ev.stopPropagation();
				ev.preventDefault();
			}

			return false;
		}
		// Up/Down arrow?
		if ((keyPress == 38 || keyPress == 40) && displayData.length && suggestDivHandle.style.visibility != 'hidden')
		{
			// Loop through the display data trying to find our entry.
			prevHandle = false;
			toHighlight = false;
			for (i = 0; i < displayData.length; i++)
			{
				// If we're going up and yet the top one was already selected don't go around.
				if (selectedDiv != false && selectedDiv == displayData[i] && i == 0 && keyPress == 38)
				{
					toHighlight = selectedDiv;
					break;
				}
				// If nothing is selected and we are going down then we select the first one.
				if (selectedDiv == false && keyPress == 40)
				{
					toHighlight = displayData[i];
					break;
				}

				// If the previous handle was the actual previously selected one and we're hitting down then this is the one we want.
				if (prevHandle != false && prevHandle == selectedDiv && keyPress == 40)
				{
					toHighlight = displayData[i];
					break;
				}
				// If we're going up and this is the previously selected one then we want the one before, if there was one.
				if (prevHandle != false && displayData[i] == selectedDiv && keyPress == 38)
				{
					toHighlight = prevHandle;
					break;
				}
				// Make the previous handle this!
				prevHandle = displayData[i];
			}

			// If we don't have one to highlight by now then it must be the last one that we're after.
			if (toHighlight == false)
				toHighlight = prevHandle;

			// Remove any old highlighting.
			if (selectedDiv != false)
				itemMouseOut({'srcElement': selectedDiv});
			// Mark what the selected div now is.
			selectedDiv = toHighlight;
			itemMouseOver({'srcElement': selectedDiv});
		}

		return true;
	}

	// Functions for integration.
	function registerCallback(callbackType, callbackFunction)
	{
		if (callbackType == 'onadd')
			onMemberAddCallback = callbackFunction;
	}

	// User hit submit?
	function onElementSubmitted()
	{
		return_value = true;
		// Do we have something that matches the current text?
		for (i = 0; i < cache.length; i++)
		{
			if (lastSearch.toLowerCase() == cache[i]['name'].toLowerCase().substr(0, lastSearch.length))
			{
				// Exact match?
				if (lastSearch.length == cache[i]['name'].length)
				{
					// This is the one!
					return_value = {'memberid': cache[i]['id'], 'membername': cache[i]['name']};
					break;
				}

				// If we have two matches don't find anything.
				if (return_value != true)
					return_value = false;
				return_value = {'memberid': cache[i]['id'], 'membername': cache[i]['name']};
			}
		}

		if (return_value == true || return_value == false)
			return return_value;
		else
		{
			addUserLink(return_value, true);
			return false;
		}
	}

	// Positions the box correctly on the window.
	function postitionDiv()
	{
		// Only do it once.
		if (positionComplete)
			return true;

		positionComplete = true;

		// Put the div under the text box.
		var parentPos = smf_itemPos(textHandle);

		suggestDivHandle.style.left = parentPos[0] + 'px';
		suggestDivHandle.style.top = (parentPos[1] + textHandle.offsetHeight) + 'px';
		suggestDivHandle.style.width = textHandle.style.width;

		return true;
	}

	// Do something after clicking an item.
	function itemClicked(ev)
	{
		if (!ev)
			ev = window.event;

		if (ev.srcElement)
			curElement = ev.srcElement;
		else if (ev.memberid)
			curElement = ev;
		else
			curElement = this;

		// Is there a div that we are duplicating and populating?
		if (document.getElementById('suggest_template_' + textID))
		{
			var curMember = {'memberid': curElement.memberid, 'membername': curElement.innerHTML};
			addUserLink(curMember);
		}
		// Otherwise clear things down.
		else
		{
			removeLastSearchString();
			autoSuggestUpdate();
		}

		selectedDiv = false;
	}

	// Remove the last searched for name from the search box.
	function removeLastSearchString()
	{
		// Remove the text we searched for from the div.
		tempText = textHandle.value.toLowerCase();
		tempSearch = lastSearch.toLowerCase();
		startString = tempText.indexOf(tempSearch);
		// Just attempt to remove the bits we just searched for.
		if (startString != -1)
		{
			while (startString > 0)
			{
				if (tempText.charAt(startString - 1) == '"' || tempText.charAt(startString - 1) == ',' || tempText.charAt(startString - 1) == ' ')
				{
					startString--;
					if (tempText.charAt(startString - 1) == ',')
						break;
				}
				else
					break;
			}

			// Now remove anything from startString upwards.
			textHandle.value = textHandle.value.substr(0, startString);
		}
		// Just take it all.
		else
			textHandle.value = '';
	}

	// Add a result if not already done.
	function addUserLink(curUser, fromSubmit)
	{
		// Is there a div that we are duplicating and populating?
		if (document.getElementById('suggest_template_' + textID))
		{
			// What will the new element be called?
			newID = 'suggest_template_' + textID + '_' + curUser.memberid;
			// Better not exist?
			if (!document.getElementById(newID))
			{
				brotherNode = document.getElementById('suggest_template_' + textID);

				newNode = brotherNode.cloneNode(true);
				brotherNode.parentNode.insertBefore(newNode, brotherNode);
				newNode.id = newID;

				// If it supports remove this will be the javascript.
				deleteCode = 'suggestHandle' + textID + '.deleteItem(' + curUser.memberid + ');';

				// Parse in any variables.
				newNode.innerHTML = newNode.innerHTML.replace(/::MEMBER_NAME::/g, curUser.membername).replace(/'*(::|%3A%3A)MEMBER_ID(::|%3A%3A)'*/g, curUser.memberid).replace(/'*::DELETE_MEMBER_URL::'*/g, deleteCode);

				newNode.style.visibility = 'visible';
				newNode.style.display = '';
			}
		}

		// Clear the div a bit.
		removeLastSearchString();

		// If we came from a submit, and there's still more to go, turn on auto add for all the other things.
		if (textHandle.value != '' && fromSubmit)
			doAutoAdd = true;
		else
			doAutoAdd = false;

		// Update the fellow..
		autoSuggestUpdate();

		// If there's a callback then call it.
		if (onMemberAddCallback)
		{
			onMemberAddCallback(textID, curUser.memberid);
		}
	}

	// Delete an item that has been added if at all?
	function deleteAddedItem(memberID)
	{
		// Remove the div if it exists.
		divID = 'suggest_template_' + textID + '_' + memberID;
		if (document.getElementById(divID))
		{
			nodeRemove = document.getElementById(divID);
			nodeRemove.parentNode.removeChild(nodeRemove);
		}

		return false;
	}

	// Hide the box.
	function autoSuggestHide()
	{
		// Delay to allow events to propogate through....
		hideTimer = setTimeout(function()
			{
				suggestDivHandle.style.visibility = 'hidden';
				selectedDiv = false;
			}, 250
		);
	}

	// Show the box.
	function autoSuggestShow()
	{
		if (hideTimer)
		{
			clearTimeout(hideTimer);
			hideTimer = false;
		}

		postitionDiv();

		if (suggestDivHandle.innerHTML)
			suggestDivHandle.style.visibility = 'visible';
	}

	// Populate the actual div.
	function populateDiv(results)
	{
		// Cannot have any children yet.
		while (suggestDivHandle.childNodes[0])
		{
			// Tidy up the events etc too.
			suggestDivHandle.childNodes[0].removeEventListener('mouseover', itemMouseOver, false);
			suggestDivHandle.childNodes[0].removeEventListener('mouseout', itemMouseOut, false);
			suggestDivHandle.childNodes[0].removeEventListener('click', itemClicked, false);

			suggestDivHandle.removeChild(suggestDivHandle.childNodes[0]);
		}

		// Something to display?
		if (typeof(results) == 'undefined')
		{
			displayData = [];
			return false;
		}

		var newDisplayData = [];
		for (i = 0; i < (results.length > maxDisplayQuantity ? maxDisplayQuantity : results.length); i++)
		{
			// Create the sub element
			newDivHandle = document.createElement('div');
			newDivHandle.memberid = results[i]['id'];
			newDivHandle.className = 'auto_suggest_item';
			newDivHandle.innerHTML = results[i]['name'];
			newDivHandle.style.width = textHandle.style.width;

			suggestDivHandle.appendChild(newDivHandle);

			// Attach some events to it so we can do stuff.
			createEventListener(newDivHandle);
			newDivHandle.addEventListener('mouseover', itemMouseOver, false);
			newDivHandle.addEventListener('mouseout', itemMouseOut, false);
			newDivHandle.addEventListener('click', itemClicked, false);

			newDisplayData[i] = newDivHandle;
		}

		displayData = newDisplayData;

		return true;
	}

	// Refocus the element.
	function itemMouseOver(ev)
	{
		if (!ev)
			ev = window.event;

		if (ev.srcElement)
			curElement = ev.srcElement;
		else
			curElement = this;

		selectedDiv = curElement;
		curElement.className = 'auto_suggest_item_hover';
	}

	// Onfocus the element
	function itemMouseOut(ev)
	{
		if (!ev)
			ev = window.event;

		if (ev.srcElement)
			curElement = ev.srcElement;
		else
			curElement = this;

		curElement.className = 'auto_suggest_item';
	}

	function onSuggestionReceived(oXMLDoc)
	{
		if (xmlRequestHandle.readyState != 4)
			return true;

		var sQuoteText = '';
		var members = oXMLDoc.getElementsByTagName('member');
		cache = [];
		for (var i = 0; i < members.length; i++)
		{
			cache[i] = new Array(2);
			cache[i]['id'] = members[i].getAttribute('id');
			cache[i]['name'] = members[i].childNodes[0].nodeValue;

			// If we're doing auto add and we find the exact person, then add them!
			if (doAutoAdd && lastSearch == cache[i]['name'])
			{
				return_value = {'memberid': cache[i]['id'], 'membername': cache[i]['name']};
				cache = [];
				return addUserLink(return_value, true);
			}
		}

		// Check we don't try to keep auto updating!
		doAutoAdd = false;

		// Populate the div.
		populateDiv(cache);

		// Make sure we can see it - if we can.
		if (members.length == 0)
			autoSuggestHide();
		else
			autoSuggestShow();

		return true;
	}

	// Get a new suggestion.
	function autoSuggestUpdate()
	{
		if (isEmptyText(textHandle))
		{
			cache = [];

			populateDiv();

			autoSuggestHide();

			return true;
		}

		// Nothing changed?
		if (textHandle.value == lastDirtySearch)
			return true;
		lastDirtySearch = textHandle.value;

		// We're only actually interested in the last string.
		var searchString = textHandle.value.replace(/^("[^"]+",[ ]*)+/, "").replace(/^([^,]+,[ ]*)+/, "");
		if (searchString.substr(0, 1) == '"')
			searchString = searchString.substr(1);

		// Stop replication ASAP.
		realLastSearch = lastSearch;
		lastSearch = searchString;

		// Either nothing or we've completed a sentance.
		if (searchString == "" || searchString.substr(searchString.length - 1) == '"')
		{
			populateDiv();
			return true;
		}

		// Nothing?
		if (realLastSearch == searchString)
		{
			return true;
		}
		// Too small?
		else if (searchString.length < minimumSearchChars)
		{
			cache = [];
			autoSuggestHide();
			return true;
		}
		else if (searchString.substr(0, realLastSearch.length) == realLastSearch)
		{
			// Instead of hitting the server again, just narrow down the results...
			var newcache = [], j = 0;
			var lowercaseSearch = searchString.toLowerCase();
			for (var k = 0; k < cache.length; k++)
			{
				if (cache[k]['name'].substr(0, searchString.length).toLowerCase() == lowercaseSearch)
				{
					newcache[j++] = cache[k];
				}
			}

			cache = [];
			if (newcache.length != 0)
			{
				cache = newcache;
				// Repopulate.
				populateDiv(cache);

				// Check it can be seen.
				autoSuggestShow();

				return true;
			}
		}

		// In progress means destroy!
		if (xmlRequestHandle != null && typeof(xmlRequestHandle) == "object")
			xmlRequestHandle.abort();

		// Clean the text handle.
		searchString = searchString.php_to8bit().php_urlencode();

		// Get the document.
		xmlRequestHandle = getXMLDocument(smf_prepareScriptUrl(smf_scripturl) + 'action=suggest;suggest_type=member;search=' + searchString + ';sesc=' + sessionID + ';xml;time=' + (new Date().getTime()), onSuggestionReceived);

		return true;
	}

	// Auto initialise!
	init();
}