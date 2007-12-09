// This file contains javascript associated with a autosuggest control

function smfSuggest(sessionID, textID)
{
	// Store the handle to the text box.
	var textHandle = document.getElementById(textID);
	var suggestDivHandle = document.getElementById('suggest_div_' + textID);
	var lastSearch = "";
	var cache = [];
	var displayData = [];

	var hideTimer = false;
	var positionComplete = false;

	this.forceAutoSuggest = autoSuggestUpdate;
	this.deleteItem = deleteAddedItem;

	xmlRequestHandle = null;

	function init()
	{
		if (!window.XMLHttpRequest)
			return false;

		createEventListener(textHandle);
		textHandle.addEventListener('keydown', checkEnter, false);
		textHandle.addEventListener('keyup', autoSuggestUpdate, false);
		textHandle.addEventListener('change', autoSuggestUpdate, false);
		textHandle.addEventListener('blur', autoSuggestHide, false);
		textHandle.addEventListener('focus', autoSuggestUpdate, false);
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
				itemClicked(displayData[0]);
			}

			// Don't let it submit anything.
			void(0);
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
		var parentPos = findCoords(textHandle);

		suggestDivHandle.style.left = parentPos.iX + 'px';
		suggestDivHandle.style.top = (parentPos.iY + textHandle.offsetHeight) + 'px';
		suggestDivHandle.style.width = textHandle.style.width;
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
			// What will the new element be called?
			newID = 'suggest_template_' + textID + '_' + curElement.memberid;
			// Better not exist?
			if (!document.getElementById(newID))
			{
				brotherNode = document.getElementById('suggest_template_' + textID);
	
				newNode = brotherNode.cloneNode(true);
				brotherNode.parentNode.insertBefore(newNode, brotherNode);
				newNode.id = newID;

				// If it supports remove this will be the javascript.
				deleteCode = 'suggestHandle' + textID + '.deleteItem(' + curElement.memberid + ');';

				// Parse in any variables.
				newNode.innerHTML = newNode.innerHTML.replace(/\{MEMBER_NAME\}/g, curElement.innerHTML).replace(/'*(\{|%7B)MEMBER_ID(\}|%7D)'*/g, curElement.memberid).replace(/'*\{DELETE_MEMBER_URL\}'*/g, deleteCode);

				newNode.style.visibility = 'visible';
			}
		}

		textHandle.value = '';
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
		for (i = 0; i < results.length; i++)
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

		// This was the last search result.
		lastSearch = textHandle.value;

		var sQuoteText = '';
		var members = oXMLDoc.getElementsByTagName('member');
		cache = [];
		for (var i = 0; i < members.length; i++)
		{
			cache[i] = new Array(2);
			cache[i]['id'] = members[i].getAttribute('id');
			cache[i]['name'] = members[i].childNodes[0].nodeValue;
		}

		// Populate the div.
		populateDiv(cache);

		// Make sure we can see it - if we can.
		if (members.length == 0)
			autoSuggestHide();
		else
			autoSuggestShow();
	}

	// Get a new suggestion.
	function autoSuggestUpdate()
	{
		if (isEmptyText(textHandle))
		{
			populateDiv();

			autoSuggestHide();

			return true;
		}

		var searchString = textHandle.value.replace(/^("[^"]+",[ ]*)+/, "").replace(/^([^,]+,[ ]*)+/, "");
		if (searchString.substr(0, 1) == '"')
			searchString = searchString.substr(1);

		if (searchString == "" || searchString.substr(searchString.length - 1) == '"')
		{
			populateDiv();
			return true;
		}

		// Nothing?
		if (lastSearch == searchString)
		{
			return true;
		}
		else if (searchString.substr(0, lastSearch.length) == lastSearch && cache.length != 100)
		{
			// Instead of hitting the server again, just narrow down the results...
			var newcache = [], j = 0;
			for (var k = 0; k < cache.length; k++)
			{
				if (cache[k]['name'].substr(0, searchString.length) == searchString)
					newcache[j++] = cache[k];
			}

			if (newcache.length != 0)
			{
				lastSearch = searchString;
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
		searchString = escape(textToEntities(searchString).replace(/&#(\d+);/g, "%#$1%")).replace(/%26/g, "%25%23038%25");

		// Get the document.
		xmlRequestHandle = getXMLDocument(smf_scripturl + '?action=suggest;suggest_type=member;search=' + textHandle.value + ';sesc=' + sessionID + ';xml;' + (new Date().getTime()), onSuggestionReceived);
	}

	// Auto initialise!
	init();
}