function smfEditor(sessionID, uniqueId, wysiwyg)
{
	// Create some links to the editor object.
	this.uid = uniqueId;
	this.textHandle = 'NULL';
	this.currentText = '';
	var showDebug = true;
	var mode = typeof(wysiwyg) != "undefined" && wysiwyg == true ? 1 : 0;
	var htmlPossible = smf_browser == 'ie5' || smf_browser == 'firefox';

	var frameHandle = null;
	var frameElement = null;
	var frameDocument = null;
	var frameWindow = null;

	// These hold the breadcrumb.
	var breadHandle = null;
	var breadElement = null;

	var smileyPopupWindow = null;
	var cur_session_id = sessionID;

	// This will be used for loading the wysiwyg stuff.
	this.init = init;
	this.ButtonClickHandler = ButtonClickHandler;
	this.SmileyClickHandler = SmileyClickHandler;
	this.ButtonHoverHandler = ButtonHoverHandler;
	this.addButton = addButton;
	this.initSelect = initSelect;
	this.addSmiley = addSmiley;
	this.ToggleView = ToggleView;
	this.InsertText = InsertText;
	this.InsertSmiley = InsertSmiley;
	this.getMode = getMode;
	this.showMoreSmileys = showMoreSmileys;
	this.doSubmit = DoSubmit;
	var buttonControls = new Array();
	var selectControls = new Array();
	var smfSmileys = new Array();
	var formatQueue = new Array();

	// This is all the elements that can have a simple execCommand.
	var simpleExec = new Array();
	simpleExec['b'] = 'bold';
	simpleExec['u'] = 'underline';
	simpleExec['i'] = 'italic';
	simpleExec['s'] = 'strikethrough';
	simpleExec['left'] = 'justifyleft';
	simpleExec['center'] = 'justifycenter';
	simpleExec['right'] = 'justifyright';
	simpleExec['hr'] = 'inserthorizontalrule';
	simpleExec['list'] = 'insertunorderedlist';

	// Things which have direct HTML equivalents. [ => <
	var htmlEquiv = new Array();
	htmlEquiv['pre'] = 'pre';
	htmlEquiv['sub'] = 'sub';
	htmlEquiv['sup'] = 'sup';
	htmlEquiv['tt'] = 'tt';

	// Any special breadcrumb mappings to ensure we show a consistant tag name.
	var breadCrumbNameTags = new Array();
	breadCrumbNameTags['strike'] = 's';
	breadCrumbNameTags['strong'] = 'b';
	breadCrumbNameTags['em'] = 'i';

	var breadCrumbNameStyles = new Array();
	breadCrumbNameStyles[0] = new Array('text-decoration', 'underline', 'u');
	breadCrumbNameStyles[1] = new Array('text-decoration', 'line-through', 's');
	breadCrumbNameStyles[2] = new Array('text-align', 'left', 'left');
	breadCrumbNameStyles[3] = new Array('text-align', 'center', 'center');
	breadCrumbNameStyles[4] = new Array('text-align', 'right', 'right');
	breadCrumbNameStyles[5] = new Array('font-weight', 'bold', 'b');
	breadCrumbNameStyles[6] = new Array('font-style', 'italic', 'i');

	// All the fonts in the world.
	var foundFonts = new Array();
	// Font maps (HTML => CSS size)
	var fontSizes = new Array(0, 8, 10, 12, 14, 18, 24, 36);
	// Color maps! (hex => name)
	var fontColors = new Array();
	fontColors['#000000'] = 'black';
	fontColors['#ff0000'] = 'red';
	fontColors['#ffff00'] = 'yellow';
	fontColors['#ffc0cb'] = 'pink';
	fontColors['#008000'] = 'green';
	fontColors['#ffa500'] = 'orange';
	fontColors['#800080'] = 'purple';
	fontColors['#0000ff'] = 'blue';
	fontColors['#f5f5dc'] = 'beige';
	fontColors['#a52a2a'] = 'brown';
	fontColors['#008080'] = 'teal';
	fontColors['#000080'] = 'navy';
	fontColors['#800000'] = 'maroon';
	fontColors['#32cd32'] = 'limegreen';

	function getMode()
	{
		return mode ? 1 : 0;
	}

	function init(text)
	{
		if (init.hasRun)
			return false;
		init.hasRun = true;

		// Set the textHandle.	
		textHandle = document.getElementById(uniqueId);
		currentText = getInnerHTML(textHandle);

		// Create the iFrame element.
		if (htmlPossible)
		{
			frameElement = document.createElement('iframe');
			frameHandle = textHandle.parentNode.appendChild(frameElement);
			frameHandle.id = 'html_' . uid;
	
			// Create the debug window...
			breadElement = document.createElement('div');
			breadHandle = frameHandle.parentNode.appendChild(breadElement);
			breadHandle.id = 'bread_' . uid;

			// Show the iframe only if wysiwyg is on.
			if (mode)
			{
				textHandle.style.display = 'none';
	
				// Size the iframe to something sensible.
				frameHandle.style.width = '90%';
				frameHandle.style.height = '150px';
				frameHandle.style.visibility = 'visible';
	
				if (showDebug)
				{
					breadHandle.style.width = '90%';
					breadHandle.style.height = '20px';
					breadHandle.className = 'windowbg2';
					breadHandle.style.border = '1px black solid';
					breadHandle.style.visibility = 'visible';
				}
			}
			else
			{
				frameHandle.style.display = 'none';
				breadHandle.style.display = 'none';
			}
		}

		setTimeout(InitIframe, 100);

		if (typeof(text) != "undefined")
			currentText = text;
	}

	// Actually get the iframe up and running.
	function InitIframe()
	{
		// Finally get the document... and the window for focusing the mind (/window)
		if (htmlPossible)
		{
			frameDocument = frameElement.contentWindow.document;
			frameWindow = frameElement.contentWindow;
	
			// Populate it first.
			frameDocument.open();
			frameDocument.write("<html><head></head><body></body></html>");
			frameDocument.close();
	
			if (smf_browser != 'ie' && smf_browser != 'ie5')
			{
				frameDocument.designMode = 'off';
				frameDocument.designMode = 'on';
			}
			else
			{
				frameDocument.body.contentEditable = true;
			}

			// Attach our events.
			if (smf_browser == 'firefox')
			{
				frameDocument.addEventListener('keyup', editorKeyUp, true);
				frameDocument.addEventListener('mouseup', editorKeyUp, true);
			}
			else
			{
				frameDocument.onkeyup = editorKeyUp;
				frameDocument.onmouseup = editorKeyUp;
			}
		}

		// Insert any default text.
		InsertText(currentText, true);

		// Better make us the focus!
		if (mode)
			frameWindow.focus();
		else
			textHandle.focus();

		// Get all the fonts we can have.
		getFonts();

		// Do any select boxes.
		for (i in selectControls)
			addSelect(selectControls[i]);

		return true;
	}

	function editorKeyUp(ev)
	{
		// Apply any outstanding formatting
		if (formatQueue.length > 0)
		{
			for (i = 0; i < formatQueue.length; i++)
			{
				// Try inserting again.
				InsertCustomHTML(formatQueue[i]);
			}
			// Either way give up.
			formatQueue.length = 0;
		}

		// Rebuild the breadcrumb.
		updateEditorControls();
	}

	// Rebuild the breadcrumb etc - and set things to the correct context.
	function updateEditorControls()
	{
		// Assume nothing.
		selectControls['face'].value = '';
		selectControls['size'].value = '';
		selectControls['color'].value = '';

		// Everything else is specific to HTML mode.
		if (!mode)
			return;

		crumb = new Array();
		max_length = 6;

		// What is the current element?
		curTag = getCurElement();

		i = 0;
		while (curTag.nodeName.toLowerCase() != 'body' && i < max_length)
		{
			crumb[i] = curTag;
			curTag = curTag.parentNode;
			i++;
		}

		// Now print out the tree.
		tree = '';
		curFontName = '';
		curFontSize = '';
		curFontColor = '';
		for (i = 0; i < crumb.length; i++)
		{
			crumbname = crumb[i].nodeName.toLowerCase();

			// Does it have an alternative name?
			if (breadCrumbNameTags[crumbname])
				crumbname = breadCrumbNameTags[crumbname];
			// Don't bother with this...
			else if (crumbname == 'p')
				continue;
			else if (crumbname == 'span' || crumbname == 'div')
			{
				for (j = 0; j < breadCrumbNameStyles.length; j++)
				{
					if (crumb[i].style)
					{
						// Do we have a font?
						if (crumb[i].style.fontFamily && crumb[i].style.fontFamily != '' && curFontName == '')
						{
							curFontName = crumb[i].style.fontFamily;
							crumbname = 'face';
						}
						// ... or a font size?
						if (crumb[i].style.fontSize && crumb[i].style.fontSize != '' && curFontSize == '')
						{
							curFontSize = crumb[i].style.fontSize;
							crumbname = 'size';
						}
						// ... even color?
						if (crumb[i].style.color && crumb[i].style.color != '' && curFontColor == '')
						{
							curFontColor = crumb[i].style.color;
							if (fontColors[curFontColor])
								curFontColor = fontColors[curFontColor];
							crumbname = 'color';
						}

						if (breadCrumbNameStyles[j][0] == 'text-align' && crumb[i].style.textAlign && crumb[i].style.textAlign == breadCrumbNameStyles[j][1])
							crumbname = breadCrumbNameStyles[j][2];
						else if (breadCrumbNameStyles[j][0] == 'text-decoration' && crumb[i].style.textDecoration && crumb[i].style.textDecoration == breadCrumbNameStyles[j][1])
							crumbname = breadCrumbNameStyles[j][2];
						else if (breadCrumbNameStyles[j][0] == 'font-weight' && crumb[i].style.fontWeight && crumb[i].style.fontWeight == breadCrumbNameStyles[j][1])
							crumbname = breadCrumbNameStyles[j][2];
						else if (breadCrumbNameStyles[j][0] == 'font-style' && crumb[i].style.fontStyle && crumb[i].style.fontStyle == breadCrumbNameStyles[j][1])
							crumbname = breadCrumbNameStyles[j][2];
					}
				}
			}
			// Do we have a font?
			else if (crumbname == 'font')
			{
				if (crumb[i].getAttribute('face'))
				{
					curFontName = crumb[i].getAttribute('face').toLowerCase();
					crumbname = 'face';
				}
				if (crumb[i].getAttribute('size'))
				{
					curFontSize = crumb[i].getAttribute('size');
					crumbname = 'size';
				}
				if (crumb[i].getAttribute('color'))
				{
					curFontColor = crumb[i].getAttribute('color');
					if (fontColors[curFontColor])
						curFontColor = fontColors[curFontColor];
					crumbname = 'color';
				}
				// Something else - ignore.
				if (crumbname == 'font')
					continue;
			}

			tree += (i != 0 ? '&nbsp;<b>&gt;</b>' : '') + '&nbsp;' + crumbname;
		}

		// Try set the font boxes correct.
		if (selectControls['face'] && typeof(selectControls['face']) == 'object')
			selectControls['face'].value = curFontName ;
		if (selectControls['size'] && typeof(selectControls['size']) == 'object')
			selectControls['size'].value = curFontSize ;
		if (selectControls['color'] && typeof(selectControls['size']) == 'object')
			selectControls['color'].value = curFontColor ;

		setInnerHTML(breadElement, tree);
	}

	// Set the HTML content to be that of the text box - if we are in wysiwyg mode.
	function DoSubmit()
	{
		// Record what we were doing!
		document.getElementById('editor_mode').value = (mode ? 1 : 0);

		if (mode)
			textHandle.value = frameDocument.body.innerHTML;
	}

	// Populate the box with text.
	function InsertText(text, clear)
	{
		// Erase it all?
		if (clear)
		{
			if (mode)
				frameDocument.body.innerHTML = text;
			else
				textHandle.value = text;
		}
		else
		{
			if (mode)
			{
				frameWindow.focus();

				// IE croaks if you have an image selected and try to insert!
				if (typeof(frameDocument.selection) != 'undefined' && frameDocument.selection.type != 'Text')
					frameDocument.selection.clear();

				range = getRange();

				if (range.pasteHTML)
					range.pasteHTML(text);
				else
				{
					// This is a git - we need to do all kinds of crap. Thanks to this page:
					// http://www.richercomponents.com/Forums/ShowPost.aspx?PostID=2777
					// Create a new span element first...
					element = frameDocument.createElement('span');
					element.innerHTML = text;

					selection = getSelect();
					if (!range)
						selection.collapse(frameDocument.getElementsByTagName('body')[0].firstChild,0);

					selection.removeAllRanges();
					range.deleteContents();

					container = range.startContainer;
					pos = range.startOffset;
					range = document.createRange();

					if (container.nodeType == 3)
					{ 
						textNode = container; 
						container = textNode.parentNode; 
						var text = textNode.nodeValue; 
						var textBefore = text.substr(0, pos); 
						var textAfter = text.substr(pos); 
						var beforeNode = document.createTextNode(textBefore); 
						var afterNode = document.createTextNode(textAfter); 
						container.insertBefore(afterNode, textNode); 
						container.insertBefore(element, afterNode); 
						container.insertBefore(beforeNode, element); 
						container.removeChild(textNode); 
						range.setEndBefore(afterNode); 
						range.setStartBefore(afterNode); 
					}
					else
					{ 
						container.insertBefore(element, container.childNodes[pos]); 
						range.setEnd(container, pos + 1); 
						range.setStart(container, pos + 1); 
					} 
					selection.addRange(range); 
				}
			}
			else
			{
				textHandle.focus();
				replaceText(text, textHandle);
			}
		}
	}

	// Add all the smileys into the "knowledge base" ;)
	function addSmiley(code, smileyname, desc)
	{
		if (smfSmileys[code])
			return;

		smileyHandle = document.getElementById('sml_' + smileyname);

		smfSmileys[code] = Array(3);
		smfSmileys[code][0] = code;
		smfSmileys[code][1] = smileyname;
		smfSmileys[code][2] = desc;

		if (smileyHandle)
		{
			smileyHandle.onclick = this.SmileyClickHandler;
			smileyHandle.code = code;
			smileyHandle.smileyname = smileyname;
			smileyHandle.desc = desc;
		}
	}

	function addButton(code, before, after)
	{
		codeHandle = document.getElementById('cmd_' + code);

		buttonControls[code] = Array(3);
		buttonControls[code][0] = codeHandle;
		buttonControls[code][1] = code;
		buttonControls[code][2] = before;
		buttonControls[code][3] = after;

		// Tie all the relevant actions to the event handler.
		codeHandle.onclick = this.ButtonClickHandler;
		codeHandle.onmouseover = codeHandle.onmouseout = this.ButtonHoverHandler;
		codeHandle.code = code;		
	}

	// Because select boxes rely on things which don't yet exist we need to initialise them after the frame.
	function initSelect(selType)
	{
		selectControls[selType] = selType;
	}

	// Populate/handle the select boxes.
	function addSelect(selType)
	{
		selectHandle = document.getElementById('sel_' + selType);
		selectHandle.code = selType;

		selectControls[selType] = selectHandle;

		if (!selectHandle)
			return;

		// Font face box!
		if (selType == 'face')
		{
			// Add in the other options.
			for (i = 0; i < foundFonts.length; i++)
				selectHandle.options[selectHandle.options.length] = new Option(foundFonts[i], foundFonts[i].toLowerCase());
		}

		selectHandle.onchange = SelectChangeHandler;
	}

	// Special handler for WYSIWYG.
	function smf_execCommand(command, ui, value)
	{
		return frameDocument.execCommand(command, ui, value);
	}

	function SmileyClickHandler(ev)
	{
		// Just for IE...
		if (!ev)
			ev = window.event;

		InsertSmiley(this.smileyname, this.code, this.desc);
	}

	function InsertSmiley(name, code, desc)
	{
		// In text mode we just add it in as we always did.
		if (!mode)
		{
			InsertText(' ' + code);
		}
		// Otherwise we need to do a whole image...
		else
		{
			uniqueid = 1000 + Math.floor(Math.random() * 100000);
			InsertText('<img src="' + smf_smileys_url + '/' + name + '" id="smiley_' + uniqueid + '_' + name + '" align="bottom" alt="" title="' + desc + '" />');
		}
	}

	function ButtonClickHandler(ev)
	{
		// IE etc...
		if (!ev)
			ev = window.event;

		// In text this is easy...
		if (!mode)
		{
			textHandle.focus();

			if (buttonControls[this.code])
			{
				// Replace?
				if (buttonControls[this.code][3] == '')
				{
					replaceText(buttonControls[this.code][2], textHandle)
				}
				// Surround!
				else
				{
					surroundText(buttonControls[this.code][2], buttonControls[this.code][3], textHandle)
				}
			}
		}
		else
		{
			// Check we have the thing in focus!
			frameWindow.focus();

			// Is it easy?
			if (simpleExec[this.code])
			{
				smf_execCommand(simpleExec[this.code], false, null);
			}
			// A link?
			else if (this.code == 'url' || this.code == 'email' || this.code == 'ftp')
			{
				insertLink(this.code);
			}
			// Maybe an image?
			else if (this.code == 'img')
			{
				insertImage();
			}
			// Everything else means doing something ourselves.
			else if (buttonControls[this.code][2])
			{
				InsertCustomHTML(this.code);
			}
		}

		updateEditorControls();
	}

	// Changing a select box?
	function SelectChangeHandler(ev)
	{
		if (!ev)
			ev = window.event;

		if (!mode)
			textHandle.focus();
		else
			frameWindow.focus();

		value = document.getElementById('sel_' + this.code).value;

		// Changing font face?
		if (this.code == 'face')
		{
			// Not in HTML mode?
			if (!mode)
			{
				value = value.replace(/"/, '');
				surroundText('[font=' + value + ']', '[/font]', textHandle)
			}
			else
			{
				smf_execCommand('fontname', false, value);
			}
		}
		// Font size?
		else if (this.code == 'size')
		{
			// Are we in boring mode?
			if (!mode)
			{
				surroundText('[size=' + value + ']', '[/size]', textHandle)
			}
			else
			{
				smf_execCommand('fontsize', false, value);
			}
		}
		// Or color even?
		else if (this.code == 'color')
		{
			// Are we in boring mode?
			if (!mode)
			{
				surroundText('[color=' + value + ']', '[/color]', textHandle)
			}
			else
			{
				smf_execCommand('forecolor', false, value);
			}
		}

		updateEditorControls();
	}

	// Put in some custom HTML.
	function InsertCustomHTML(code)
	{
		if (!buttonControls[code])
			return;

		selection = getSelect(true);
		if (selection.length == 0)
			selection = '&nbsp;';

		// If there is no text don't insert yet - add to the queue instead - otherwise it gets ignored.
		//if (selection.length == 0 && (',' + formatQueue.toString() + ',').indexOf(',' + code + ',') == -1)
		//	formatQueue[selection.length] = code;

		// Are they just HTML equivalents?
		if (htmlEquiv[code])
		{
			leftTag = buttonControls[code][2].replace(/\[/g, '<');
			leftTag = leftTag.replace(/\]/g, '>');
			rightTag = buttonControls[code][3].replace(/\[/g, '<');
			rightTag = rightTag.replace(/\]/g, '>');
		}
		else
		{
			leftTag = buttonControls[code][2];
			rightTag = buttonControls[code][3];
		}

		// Are we overwriting?
		if (rightTag == '')
			InsertText(leftTag);
		else
			InsertText(leftTag + selection + rightTag);
	}

	// Do mouse over/under.
	function ButtonHoverHandler(ev)
	{
		// IE etc...
		if (!ev)
			ev = window.event;

		if (buttonControls[this.code])
			this.style.backgroundImage = "url(" + smf_images_url + (ev.type == 'mouseover' ? '/bbc/bbc_hoverbg.gif' : '/bbc/bbc_bg.gif') + ")";
	}

	// Insert a URL link.
	function insertLink(type)
	{
		if (type == 'email')
			promptText = 'Please enter the email address.';
		else if (type == 'ftp')
			promptText = 'Please enter the ftp address.';
		else
			promptText = 'Please enter the URL you wish to link to.';

		// IE has a nice prompt for this - others don't.
		if (type != 'email' && type != 'ftp' && (smf_browser == 'ie' || smf_browser == 'ie5'))
			smf_execCommand('createlink', true, 'http://');
		else
		{
			// Ask them where to link to.
			text = prompt(promptText, type == 'email' ? '' : (type == 'ftp' ? 'ftp://' : 'http://'));
			if (!text)
				return;

			if (type == 'email' && text.indexOf('mailto:') != 0)
				text = 'mailto:' + text;

			// Check if we have text selected and if not force us to have some.
			curText = getSelect(true);

			if (curText.toString().length != 0)
			{
				smf_execCommand('unlink');
				smf_execCommand('createlink', false, text);
			}
			else
			{
				InsertText('<a href="' + text + '">' + text + '</a>');
			}
		}
	}

	function insertImage(src)
	{
		if (!src)
		{
			src = prompt('Enter image location', 'http://');
			if (!src)
				return;
		}
		smf_execCommand('insertimage', false, src);
	}

	function getSelect(wantText)
	{
		if ((smf_browser == 'ie' || smf_browser == 'ie5') && frameDocument.selection)
		{
			if (wantText)
				return frameDocument.selection.createRange().text;

			return frameDocument.selection;
		}

		if (frameWindow.getSelection)
			return frameWindow.getSelection();

		return frameDocument.getSelection();
	}

	function getRange()
	{
		// Get the current selection.
		selection = getSelect();

		if (!selection)
			return;

		if ((smf_browser == 'ie' || smf_browser == 'ie5') && selection.createRange)
			return selection.createRange();

		return selection.getRangeAt(0);
	}

	// Get the current element.
	function getCurElement()
	{
		range = getRange();

		if (!range)
			return null;

		if ((smf_browser == 'ie' || smf_browser == 'ie5'))
		{
			if (range.item)
				return range.item(0);
			else
				return range.parentElement();
		}
		else
		{
			element = range.commonAncestorContainer;
			return getParentElement(element);
		}
	}

	function getParentElement(node)
	{
		if (node.nodeType == 1)
			return node;

		for (i = 0; i < 50; i++)
		{
			if (!node.parentNode)
				break;

			node = node.parentNode;
			if (node.nodeType == 1)
				return node;
		}
		return null;
	}

	// Get which fonts are actually installed - we'll only show ones the user will see - kind of tricks them ;)
	function getFonts()
	{
		var curFonts = new Array('Arial', 'Arial Black', 'Impact', 'Verdana', 'Times New Roman', 'Georgia', 'Andale Mono', 'Trebuchet MS', 'Comic Sans MS');

		// If we can't do html they can't see it anyway.
		if (!htmlPossible)
		{
			foundFonts = curFonts;
			return;
		}

		// Just an insertion point.
		curNode = getCurElement();

		// Create our test node.
		fontText = frameDocument.createTextNode('font&nbsp;testing&nbsp;-#cool');
		fontCheck = frameDocument.createElement('span');
		fontCheck.appendChild(fontText);
		curNode.appendChild(fontCheck);

		startWidth = fontCheck.offsetWidth;

		for (i = 0; i < curFonts.length; i++)
		{
			fontCheck.style.fontFamily = curFonts[i];
			if (fontCheck.offsetWidth != startWidth)
				foundFonts.push(curFonts[i]);
		}
		curNode.removeChild(fontCheck);
	}

	// Toggle wysiwyg/normal mode.
	function ToggleView(view)
	{
		// Overriding or alternating?
		if (typeof(view) != "undefined")
			mode = view;
		else
			mode = !mode;

		requestParsedMessage(mode);
	}

	// Request the message in a different form.
	function requestParsedMessage(view)
	{
		// Replace with a force reload.
		if (!window.XMLHttpRequest)
		{
			alert('Your browser does not support this function!');
			return;
		}

		// Get the right data - and also protect certain HTML elements.
		if (mode)
		{
			text = textHandle.value;
			text = text.replace(/</g, '#smlt#');
			text = text.replace(/>/g, '#smgt#');
			text = text.replace(/&/g, '#smamp#');
		}
		else
		{
			text = frameDocument.body.innerHTML;
			text = text.replace(/&lt;/g, '#smlt#');
			text = text.replace(/&gt;/g, '#smgt#');
			text = text.replace(/&amp;/g, '#smamp#');
		}

		// Clean it up - including removing semi-colons.
		text = text.replace(/&nbsp;/g, ' ');
		text = text.replace(/;/g, '#smcol#');
		text = escape(text);

		getXMLDocument(smf_scripturl + '?action=jseditor;view=' + (view ? 1 : 0) + ';sesc=' + cur_session_id + ';xml;message=' + text, onToggleDataReceived);
	}

	function onToggleDataReceived(XMLDoc)
	{
		var text = "";
		for (var i = 0; i < XMLDoc.getElementsByTagName("message")[0].childNodes.length; i++)
			text += XMLDoc.getElementsByTagName("message")[0].childNodes[i].nodeValue;

		view = XMLDoc.getElementsByTagName("message")[0].getAttribute("view");

		// Only change the text if we have the right data.
		if (mode != view)
			return;

		if (mode)
		{
			frameHandle.style.display = '';
			breadHandle.style.display = '';
			textHandle.style.display = 'none';
			InsertText(text, true);
			frameWindow.focus();
		}
		else
		{
			text = text.replace(/&lt;/g, '<');
			text = text.replace(/&gt;/g, '>');
			text = text.replace(/&amp;/g, '&');
			frameHandle.style.display = 'none';
			breadHandle.style.display = 'none';
			textHandle.style.display = '';
			InsertText(text, true);
			textHandle.focus();
		}

		// Rebuild the bread crumb!
		updateEditorControls();
	}

	// Show the "More Smileys" popup box.
	function showMoreSmileys(postbox, titleText, pickText, closeText, smf_theme_url)
	{
		var row, i;

		if (smileyPopupWindow)
			smileyPopupWindow.close();

		smileyPopupWindow = window.open("", "add_smileys", "toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,width=480,height=220,resizable=yes");
		smileyPopupWindow.document.write('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">\n<html>');
		smileyPopupWindow.document.write('\n\t<head>\n\t\t<title>' + titleText + '</title>\n\t\t<link rel="stylesheet" type="text/css" href="' + smf_theme_url + '/style.css" />\n\t</head>');
		smileyPopupWindow.document.write('\n\t<body style="margin: 1ex;">\n\t\t<table width="100%" cellpadding="5" cellspacing="0" border="0" class="tborder">\n\t\t\t<tr class="titlebg"><td align="left">' + pickText + '</td></tr>\n\t\t\t<tr class="windowbg"><td align="left">');

		for (row = 0; row < smileys.length; row++)
		{
			for (i = 0; i < smileys[row].length; i++)
			{
				smileys[row][i][2] = smileys[row][i][2].replace(/"/g, '&quot;');
				smileyPopupWindow.document.write('<a href="javascript:void(0);" onclick="window.opener.editorHandle' + postbox + '.InsertSmiley(\'' + smileys[row][i][1] + '\', \'' + smileys[row][i][0] + '\', \'' + smileys[row][i][2] + '\'); window.focus(); return false;"><img src="' + smf_smileys_url + '/' + smileys[row][i][1] + '" id="sml_' + smileys[row][i][1] + '" alt="' + smileys[row][i][2] + '" title="' + smileys[row][i][2] + '" style="padding: 4px;" border="0" /></a> ');
			}
			smileyPopupWindow.document.write("<br />");
		}

		smileyPopupWindow.document.write('</td></tr>\n\t\t\t<tr><td align="center" class="windowbg"><a href="javascript:window.close();">' + closeText + '</a></td></tr>\n\t\t</table>');
		// Do the javascript required.
		smileyPopupWindow.document.write('<script language="JavaScript" type="text/javascript">\n');
		for (row = 0; row < smileys.length; row++)
		{
			for (i = 0; i < smileys[row].length; i++)
			{
				smileyPopupWindow.document.write('\n\twindow.opener.editorHandle' + postbox + '.addSmiley("' + smileys[row][i][0] + '", "' + smileys[row][i][1] + '", "' + smileys[row][i][2] + '");');
			}
		}
		smileyPopupWindow.document.write('\n</script>');
		smileyPopupWindow.document.write('\n\t</body>\n</html>');
		smileyPopupWindow.document.close();
	}
}