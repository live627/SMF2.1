// Make an editor!!
function smfEditor(sessionID, uniqueId, wysiwyg, text, editWidth, editHeight)
{
	// Create some links to the editor object.
	this.uid = uniqueId;
	var textHandle = false;
	var currentText = typeof(text) != "undefined" ? text : '';

	// How big?
	var editWidth = typeof(editWidth) != "undefined" ? editWidth : '70%';
	var editHeight = typeof(editHeight) != "undefined" ? editHeight : '150px';

	var showDebug = false;
	var richTextEnabled = typeof(wysiwyg) != "undefined" && wysiwyg == true ? 1 : 0;
	//!!! This partly works on opera - it's a rubbish browser for JS.
	var richTextPossible = is_ie5up || is_ff || is_opera9up;
	//var richTextPossible = is_ie5up || is_ff;

	var frameHandle = null;
	var frameDocument = null;
	var frameWindow = null;

	// These hold the breadcrumb.
	var breadHandle = null;

	var smileyPopupWindow = null;
	var cur_session_id = sessionID;

	// This will be used for loading the wysiwyg stuff.
	this.init = init;
	this.addButton = addButton;
	this.addSmiley = addSmiley;
	this.addSelect = addSelect;
	this.toggleView = toggleView;
	this.insertText = insertText;
	this.insertSmiley = insertSmiley;
	this.getMode = getMode;
	this.getText = getText;
	this.showMoreSmileys = showMoreSmileys;
	this.resizeTextArea = resizeTextArea;
	this.doSubmit = doSubmit;

	// Spellcheck functionaliy.
	this.spellCheckStart = spellCheckStart;
	this.spellCheckEnd = spellCheckEnd;

	// Kinda holds all the useful stuff.
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

	// Codes to call a private function
	var smfExec = new Array();
	smfExec['toggle'] = toggleView;
	//smfExec['increase_height'] = makeEditorTaller;
	//smfExec['decrease_height'] = makeEditorShorter;

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
	var fontFaces = new Array('Arial', 'Arial Black', 'Impact', 'Verdana', 'Times New Roman', 'Georgia', 'Andale Mono', 'Trebuchet MS', 'Comic Sans MS');
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
		return richTextEnabled ? 1 : 0;
	}

	// Return the current text.
	function getText(prepareEntities, modeOverride)
	{
		curMode = typeof(modeOverride) != "undefined" ? modeOverride : richTextEnabled;

		if (!curMode || !frameDocument)
		{
			text = textHandle.value;
			if (prepareEntities)
			{
				text = text.replace(/</g, '#smlt#');
				text = text.replace(/>/g, '#smgt#');
				text = text.replace(/&/g, '#smamp#');
			}
		}
		else
		{
			text = frameDocument.body.innerHTML;
			if (prepareEntities)
			{
				text = text.replace(/&lt;/g, '#smlt#');
				text = text.replace(/&gt;/g, '#smgt#');
				text = text.replace(/&amp;/g, '#smamp#');
			}
		}

		// Clean it up - including removing semi-colons.
		if (prepareEntities)
		{
			text = text.replace(/&nbsp;/g, ' ');
			text = text.replace(/;/g, '#smcol#');
		}

		// Return it.
		return text;
	}

	function init(text, editWidth, editHeight)
	{
		if (init.hasRun)
			return false;
		init.hasRun = true;

		if (!editWidth)
			editWidth = '70%';
		if (!editHeight)
			editHeight = '150px';

		// Set the textHandle.	
		textHandle = document.getElementById(uniqueId);

		// Ensure the currentText is set correctly depending on the mode.
		if (typeof(text) != "undefined" && richTextEnabled == 1)
			currentText = text;
		else if (currentText == '' && richTextEnabled == 0)
			currentText = smf_unhtmlspecialchars(getInnerHTML(textHandle));

		// Only try to do this if rich text is supported.
		if (richTextPossible)
		{
			// Make the iframe itself, stick it next to the current text area, and give it an ID.
			frameHandle = document.createElement('iframe');
			frameHandle.id = 'html_' . uid;
			textHandle.parentNode.appendChild(frameHandle);

			// Create some handy shortcuts.
			frameDocument = frameHandle.contentWindow.document;
			frameWindow = frameHandle.contentWindow;

			// Create the debug window... and stick this under the main frame - make it invisible by default.
			breadHandle = document.createElement('div');
			breadHandle.id = 'bread_' . uid;
			breadHandle.style.visibility = 'visible';
			breadHandle.style.display = 'none';
			frameHandle.parentNode.appendChild(breadHandle);

			// Size the iframe dimensions to something sensible.
			frameHandle.style.width = editWidth;
			frameHandle.style.height = editHeight;
			frameHandle.style.visibility = 'visible';

			// Only bother formatting the debug window if debug is enabled.
			if (showDebug)
			{
				breadHandle.style.width = editWidth;
				breadHandle.style.height = '20px';
				breadHandle.className = 'windowbg2';
				breadHandle.style.border = '1px black solid';
				breadHandle.style.display = '';
			}
	
			// Populate the editor with nothing by default.
			if (!is_opera9up)
			{
				frameDocument.open();
				frameDocument.write("");
				frameDocument.close();
			}

			// Mark it as editable...
			if (frameDocument.body.contentEditable)
				frameDocument.body.contentEditable = true;
			else
				frameDocument.designMode = 'on';

			// Load the style sheet and set the WYSIWYG editor to the right class...
			ssheet = frameDocument.createElement('style');
			frameDocument.documentElement.firstChild.appendChild(ssheet);
			ssheet.styleSheet.cssText = document.styleSheets['rich_edit_css'].cssText;

			// Apply the class...
			frameDocument.body.className = 'rich_editor';

			// Listen for input.
			if (is_ff)
			{
				frameDocument.addEventListener('keyup', editorKeyUp, true);
				frameDocument.addEventListener('mouseup', editorKeyUp, true);
			}
			else
			{
				frameDocument.onkeyup = editorKeyUp;
				frameDocument.onmouseup = editorKeyUp;
			}

			// Show the iframe only if wysiwyg is on - and hide the text area.
			textHandle.style.display = richTextEnabled ? 'none' : '';
			frameHandle.style.display = richTextEnabled ? '' : 'none';
			breadHandle.style.display = richTextEnabled ? '' : 'none';
		}
		// If we can't do advanced stuff then just do the basics.
		else
		{
			// Cannot have WYSIWYG anyway!
			richTextEnabled = 0;
		}

		// Clean up!
		initClose();
	}

	// The final elements of initalisation.
	function initClose()
	{
		// Set the text.
		insertText(currentText, true);

		// Better make us the focus!
		setFocus();

		// And add the select controls.
		for (i in selectControls)
			addSelect(selectControls[i]);
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
		if (selectControls['face'])
			selectControls['face'].value = '';
		if (selectControls['size'])
			selectControls['size'].value = '';
		if (selectControls['color'])
			selectControls['color'].value = '';

		// Everything else is specific to HTML mode.
		if (!richTextEnabled)
			return;

		crumb = new Array();
		allCrumbs = new Array();
		max_length = 6;

		// What is the current element?
		curTag = getCurElement();

		i = 0;
		while (curTag && curTag.nodeName.toLowerCase() != 'body' && i < max_length)
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
			// A link?
			else if (crumbname == 'a')
			{
				crumbname = 'url';
				if (urlInfo = crumb[i].getAttribute('href'))
				{
					if (urlInfo.substr(0, 3) == 'ftp')
						crumbname = 'ftp';
					else if (urlInfo.substr(0, 6) == 'mailto')
						crumbname = 'email';
				}
			}
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
				if (crumb[i].getAttribute('face') && curFontName == '')
				{
					curFontName = crumb[i].getAttribute('face').toLowerCase();
					crumbname = 'face';
				}
				if (crumb[i].getAttribute('size') && curFontSize == '')
				{
					curFontSize = crumb[i].getAttribute('size');
					crumbname = 'size';
				}
				if (crumb[i].getAttribute('color') && curFontColor == '')
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
			allCrumbs[allCrumbs.length] = crumbname;
		}

		for (i in buttonControls)
		{
			newState = in_array(buttonControls[i][1], allCrumbs);
			if (newState != buttonControls[i][4])
			{
				buttonControls[i][4] = newState;
				buttonControls[i][0].style.backgroundImage = "url(" + smf_images_url + (newState ? '/bbc/bbc_hoverbg.gif' : '/bbc/bbc_bg.gif') + ")";
			}
		}

		// Try set the font boxes correct.
		if (selectControls['face'] && typeof(selectControls['face']) == 'object')
			selectControls['face'].value = curFontName ;
		if (selectControls['size'] && typeof(selectControls['size']) == 'object')
			selectControls['size'].value = curFontSize ;
		if (selectControls['color'] && typeof(selectControls['size']) == 'object')
			selectControls['color'].value = curFontColor ;

		if (showDebug)
			setInnerHTML(breadHandle, tree);
	}

	// Set the HTML content to be that of the text box - if we are in wysiwyg mode.
	function doSubmit()
	{
		// Record what we were doing!
		document.getElementById('editor_mode').value = (richTextEnabled ? 1 : 0);

		if (richTextEnabled)
			textHandle.value = frameDocument.body.innerHTML;
	}

	// Populate the box with text.
	function insertText(text, clear)
	{
		// Erase it all?
		if (clear)
		{
			if (richTextEnabled)
				frameDocument.body.innerHTML = text;
			else
				textHandle.value = text;
		}
		else
		{
			setFocus();
			if (richTextEnabled)
			{
				// IE croaks if you have an image selected and try to insert!
				if (typeof(frameDocument.selection) != 'undefined' && frameDocument.selection.type != 'Text' && frameDocument.selection.clear)
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

						//!!! Why does this not work on opera?
						if (!is_opera)
						{
							range.setEndBefore(afterNode); 
							range.setStartBefore(afterNode); 
						}
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
			// Setup the event callback.
			createEventListener(smileyHandle);
			smileyHandle.addEventListener('click', smileyEventHandler, false);

			smileyHandle.code = code;
			smileyHandle.smileyname = smileyname;
			smileyHandle.desc = desc;
		}
	}

	function addButton(code, before, after)
	{
		codeHandle = document.getElementById('cmd_' + code);

		buttonControls[code] = Array(4);
		buttonControls[code][0] = codeHandle;
		buttonControls[code][1] = code;
		buttonControls[code][2] = before;
		buttonControls[code][3] = after;
		// This holds whether or not it's active.
		buttonControls[code][4] = false;

		// Tie all the relevant actions to the event handler.
		createEventListener(codeHandle);
		codeHandle.addEventListener('click', buttonEventHandler, false);
		codeHandle.addEventListener('mouseover', buttonEventHandler, false);
		codeHandle.addEventListener('mouseout', buttonEventHandler, false);

		codeHandle.code = code;		
	}

	// Populate/handle the select boxes.
	function addSelect(selType)
	{
		selectHandle = document.getElementById('sel_' + selType);
		if (!selectHandle)
			return;

		selectHandle.code = selType;

		selectControls[selType] = selectHandle;

		// Font face box!
		if (selType == 'face')
		{
			// Add in the other options.
			for (i = 0; i < fontFaces.length; i++)
				selectHandle.options[selectHandle.options.length] = new Option(fontFaces[i], fontFaces[i].toLowerCase());
		}

		createEventListener(selectHandle);
		selectHandle.addEventListener('change', selectEventHandler, false);
	}

	function smileyEventHandler(ev)
	{
		// Just for IE...
		if (!ev)
			ev = window.event;

		// Select the current smiley element.
		if (this.code)
			curElement = this;
		else if (ev.srcElement)
			curElement = ev.srcElement;

		// Assume it does exist?!
		if (!curElement || !curElement.code)
			return false;

		insertSmiley(curElement.smileyname, curElement.code, curElement.desc);
	}

	// Special handler for WYSIWYG.
	function smf_execCommand(command, ui, value)
	{
		return frameDocument.execCommand(command, ui, value);
	}

	function insertSmiley(name, code, desc)
	{
		// In text mode we just add it in as we always did.
		if (!richTextEnabled)
		{
			insertText(' ' + code);
		}
		// Otherwise we need to do a whole image...
		else
		{
			uniqueid = 1000 + Math.floor(Math.random() * 100000);
			insertText('<img src="' + smf_smileys_url + '/' + name + '" id="smiley_' + uniqueid + '_' + name + '" onresizestart="return false;" align="bottom" alt="" title="' + smf_htmlspecialchars(desc) + '" />');
		}
	}

	function buttonEventHandler(ev)
	{
		// IE etc...
		if (!ev)
			ev = window.event;

		// What is the current element?
		if (this.code)
			curElement = this;
		else if (ev.srcElement)
			curElement = ev.srcElement;

		if (!curElement || !buttonControls[curElement.code])
			return false;

		// Are handling a hover?
		if (ev.type == 'mouseover' || ev.type == 'mouseout')
		{
			// Work out whether we should highlight it or not. On non-WYSWIYG we highlight on mouseover, on WYSWIYG we toggle current state.
			isHighlight = ev.type == 'mouseover' ? true : false;
			if (richTextEnabled && buttonControls[curElement.code][4])
				isHighlight = !isHighlight;

			curElement.style.backgroundImage = "url(" + smf_images_url + (isHighlight ? '/bbc/bbc_hoverbg.gif' : '/bbc/bbc_bg.gif') + ")";
		}
		else if (ev.type == 'click')
		{
			setFocus();

			// An special SMF function?
			if (smfExec[curElement.code])
			{
				smfExec[curElement.code]();
			}
			else
			{
				// In text this is easy...
				if (!richTextEnabled)
				{
					// Replace?
					if (buttonControls[curElement.code][3] == '')
					{
						replaceText(buttonControls[curElement.code][2], textHandle)
					}
					// Surround!
					else
					{
						surroundText(buttonControls[curElement.code][2], buttonControls[curElement.code][3], textHandle)
					}
				}
				else
				{
					// Is it easy?
					if (simpleExec[curElement.code])
					{
						smf_execCommand(simpleExec[curElement.code], false, null);
					}
					// A link?
					else if (curElement.code == 'url' || curElement.code == 'email' || curElement.code == 'ftp')
					{
						insertLink(curElement.code);
					}
					// Maybe an image?
					else if (curElement.code == 'img')
					{
						insertImage();
					}
					// Everything else means doing something ourselves.
					else if (buttonControls[curElement.code][2])
					{
						InsertCustomHTML(curElement.code);
					}
				}
			}

			// If this is WYSWIYG toggle this button state.
			if (richTextEnabled)
			{
				buttonControls[curElement.code][4] = !buttonControls[curElement.code][4];
				curElement.style.backgroundImage = "url(" + smf_images_url + (buttonControls[curElement.code][4] ? '/bbc/bbc_hoverbg.gif' : '/bbc/bbc_bg.gif') + ")";
			}

			updateEditorControls();
		}
	}

	// Changing a select box?
	function selectEventHandler(ev)
	{
		// For IE as always!
		if (!ev)
			ev = window.event;

		// Work out what the current element is.
		if (this.code)
			curElement = this;
		else if (ev.srcElement)
			curElement = ev.srcElement;

		// Make sure it exists.
		if (!curElement || !selectControls[curElement.code])
			return false;

		setFocus();

		value = document.getElementById('sel_' + curElement.code).value;

		// Changing font face?
		if (curElement.code == 'face')
		{
			// Not in HTML mode?
			if (!richTextEnabled)
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
		else if (curElement.code == 'size')
		{
			// Are we in boring mode?
			if (!richTextEnabled)
			{
				surroundText('[size=' + value + ']', '[/size]', textHandle)
			}
			else
			{
				smf_execCommand('fontsize', false, value);
			}
		}
		// Or color even?
		else if (curElement.code == 'color')
		{
			// Are we in boring mode?
			if (!richTextEnabled)
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
			insertText(leftTag);
		else
		{
			insertText(leftTag + selection + rightTag);
		}
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
		if (type != 'email' && type != 'ftp' && is_ie)
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
				insertText('<a href="' + text + '">' + text + '</a>');
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
		if (is_ie && frameDocument.selection)
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

		if (is_ie && selection.createRange)
			return selection.createRange();

		return selection.getRangeAt(0);
	}

	// Get the current element.
	function getCurElement()
	{
		range = getRange();

		if (!range)
			return null;

		if (is_ie)
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

	// Toggle wysiwyg/normal mode.
	function toggleView(view)
	{
		if (!richTextPossible)
		{
			alert('Your browser does not support Rich Text editing.');
			return false;
		}

		// Overriding or alternating?
		if (typeof(view) != "undefined")
			richTextEnabled = view;
		else
			richTextEnabled = !richTextEnabled;

		requestParsedMessage(richTextEnabled);

		// If we're leaving WYSIWYG all buttons need to be off.
		if (!richTextEnabled)
		{
			for (i in buttonControls)
			{
				buttonControls[i][4] = false;
				buttonControls[i][0].style.backgroundImage = "url(" + smf_images_url + '/bbc/bbc_bg.gif' + ")";
			}
		}
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

		// Get the text.
		text = getText(true, !view);
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
		if (richTextEnabled != view)
			return;

		if (richTextEnabled)
		{
			frameHandle.style.display = '';
			breadHandle.style.display = '';
			textHandle.style.display = 'none';
			insertText(text, true);
		}
		else
		{
			text = text.replace(/&lt;/g, '<');
			text = text.replace(/&gt;/g, '>');
			text = text.replace(/&amp;/g, '&');
			frameHandle.style.display = 'none';
			breadHandle.style.display = 'none';
			textHandle.style.display = '';
			insertText(text, true);
		}

		// Focus, focus, focus.
		setFocus();

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
				smileyPopupWindow.document.write('<a href="javascript:void(0);" onclick="window.opener.editorHandle' + postbox + '.insertSmiley(\'' + smileys[row][i][1] + '\', \'' + smileys[row][i][0] + '\', \'' + smileys[row][i][2] + '\'); window.focus(); return false;"><img src="' + smf_smileys_url + '/' + smileys[row][i][1] + '" id="sml_' + smileys[row][i][1] + '" alt="' + smileys[row][i][2] + '" title="' + smileys[row][i][2] + '" style="padding: 4px;" border="0" /></a> ');
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

	// Set the focus for the editing window.
	function setFocus()
	{
		if (!richTextEnabled)
			textHandle.focus();
		else
			frameWindow.focus();
	}

	// Start up the spellchecker!
	function spellCheckStart()
	{
		if (!spellCheck)
			return false;

		// If we're in HTML mode we need to get the non-HTML text.
		if (richTextEnabled)
		{
			text = getText(true, 1);
			text = escape(text);

			getXMLDocument(smf_scripturl + '?action=jseditor;spell;view=0;sesc=' + cur_session_id + ';xml;message=' + text, onSpellCheckDataReceived);
		}
		// Otherwise start spellchecking right away.
		else
		{
			spellCheck('postmodify', 'message');
		}
	}

	// This contains the spellcheckable text.
	function onSpellCheckDataReceived(XMLDoc)
	{
		var text = "";
		for (var i = 0; i < XMLDoc.getElementsByTagName("message")[0].childNodes.length; i++)
			text += XMLDoc.getElementsByTagName("message")[0].childNodes[i].nodeValue;

		text = text.replace(/&lt;/g, '<');
		text = text.replace(/&gt;/g, '>');
		text = text.replace(/&amp;/g, '&');

		textHandle.value = text;
		spellCheck('postmodify', 'message', 'spellCheckEnd');
	}

	// Function called when the Spellchecker is finished and ready to pass back.
	function spellCheckEnd()
	{
		// If HTML edit put the text back!
		if (richTextEnabled)
		{
			text = getText(true, 0);
			text = escape(text);

			getXMLDocument(smf_scripturl + '?action=jseditor;spelldone;view=1;sesc=' + cur_session_id + ';xml;message=' + text, onSpellCheckCompleteDataReceived);
		}
		else
			setFocus();
	}

	// The corrected text.
	function onSpellCheckCompleteDataReceived(XMLDoc)
	{
		var text = "";
		for (var i = 0; i < XMLDoc.getElementsByTagName("message")[0].childNodes.length; i++)
			text += XMLDoc.getElementsByTagName("message")[0].childNodes[i].nodeValue;

		insertText(text, true);
		setFocus();
	}

	function resizeTextArea(newHeight, newWidth, is_change)
	{
		// Work out what the new height is.
		if (is_change)
		{
			// We'll assume pixels but may not be.
			newHeight = _calculateNewDimension(textHandle.style.height, newHeight);
			newWidth = _calculateNewDimension(textHandle.style.width, newWidth);
		}

		// Do the HTML editor - but only if it's enabled!
		if (richTextPossible)
		{
			frameHandle.style.height = newHeight;
			frameHandle.style.width = newWidth;
		}
		// Do the text box regardless!
		textHandle.style.height = newHeight;
		textHandle.style.width = newWidth;
	}

	// A utility instruction to save repetition when trying to work out what to change on a height/width.
	function _calculateNewDimension(old_size, change_size)
	{
		// We'll assume pixels but may not be.
		changeReg = change_size.toString().match(/(\d+)(\D*)/);
		curReg = old_size.toString().match(/(\d+)(\D*)/);

		if (!changeReg[2])
			changeReg[2] = 'px';

		// Both the same type?
		if (changeReg[2] == curReg[2])
			new_size = (parseInt(changeReg[1]) + parseInt(curReg[1])).toString() + changeReg[2];
		// Is the change a percentage?
		else if (changeReg[2] == '%')
			new_size = (parseInt(curReg[1]) + parseInt((parseInt(changeReg[1]) * parseInt(curReg[1])) / 100)).toString() + 'px';
		// Otherwise just guess!
		else
			new_size = (parseInt(curReg[1]) + (parseInt(changeReg[1]) / 10)).toString() + '%';

		return new_size;
	}

	init(this.text, this.editWidth, this.editHeight);
}