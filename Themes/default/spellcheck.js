// These are variables the popup is going to want to access...
var spell_formname, spell_fieldname;

// Spell check the specified field in the specified form.
function spellCheck(formName, fieldName)
{
	// Grab the (hidden) spell checking form.
	var spellform = document.forms.spell_form;

	// Register the name of the editing form for future reference.
	spell_formname = formName;
	spell_fieldname = fieldName;

	// This should match a word (most of the time).
	var regexpWordMatch = /(?:<[^>]+>)|(?:\[[^ ][^\]]*\])|(?:&[^; ]+;)|(?:[^0-9\s\]\[{};:"\\|,<.>\/?`~!@#$%^&*()_+=]+)/g;

	// These characters can appear within words.
	var aWordCharacters = ['-', '\''];

	var aWords = new Array(), aResult = new Array();
	var sText = document.forms[formName][fieldName].value
	var bInCode = false;
	var iOffset1, iOffset2;

	// Loop through all words.
	while ((aResult = regexpWordMatch.exec(sText)) && typeof(aResult) != 'undefined')
	{
		iOffset1 = 0;
		iOffset2 = aResult[0].length - 1;

		// Strip the dashes and hyphens from the begin of the word.
		while (in_array(aResult[0].charAt(iOffset1), aWordCharacters) && iOffset1 < iOffset2)
			iOffset1++;

		// Strip the dashes and hyphens from the end of the word.
		while (in_array(aResult[0].charAt(iOffset2), aWordCharacters) && iOffset1 < iOffset2)
			iOffset2--;

		// I guess there's only dashes and hyphens in this word...
		if (iOffset1 == iOffset2)
			continue;

		// Ignore code blocks.
		if (aResult[0].substr(0, 5).toLowerCase() == '[code')
			bInCode = true;

		// Glad we're out of that code block!
		else if (bInCode && aResult[0].substr(0, 7).toLowerCase() == '[/code]')
			bInCode = false;

		// Now let's get to business.
		else if (!bInCode && !in_array(aResult[0].charAt(0), ['[', '<']) && aResult[0].toUpperCase() != aResult[0])
			aWords[aWords.length] = aResult[0].substr(iOffset1, iOffset2 - iOffset1 + 1) + '|' + (iOffset1 + sText.substr(0, aResult.index).length) + '|' + (iOffset2 + sText.substr(0, aResult.index).length);
	}

	// Open the window...
	openSpellWin(640, 480);

	// Pass the data to a form...
	spellform.spellstring.value = aWords.join('\n');

	//  and go!
	spellform.submit();

	return true;
}

// Private functions -------------------------------

// Globals...
var wordindex = -1, offsetindex = 0;
var ignoredWords = [];

// A "misspelled word" object.
function misp(word, start, end, suggestions)
{
	// The word, start index, end index, and array of suggestions.
	this.word = word;
	this.start = start;
	this.end = end;
	this.suggestions = suggestions;
}

// Replace the word in the misps array at the "wordindex" index.  The
// misps array is generated by a PHP script after the string to be spell
// checked is evaluated with pspell.
function replaceWord()
{
	var strstart = "";
	var strend;

	// If this isn't the beginning of the string then get all of the string
	// that is before the word we are replacing.
	if (misps[wordindex].start != 0)
		strstart = mispstr.slice(0, misps[wordindex].start + offsetindex);

	// Get the end of the string after the word we are replacing.
	strend = mispstr.slice(misps[wordindex].end + 1 + offsetindex);

	// Rebuild the string with the new word.
	mispstr = strstart + document.forms.spellingForm.changeto.value + strend;

	// Update offsetindex to compensate for replacing a word with a word
	// of a different length.
	offsetindex += document.forms.spellingForm.changeto.value.length - misps[wordindex].word.length;

	// Update the word so future replaceAll calls don't change it.
	misps[wordindex].word = document.forms.spellingForm.changeto.value;

	nextWord(false);
}

// Replaces all instances of currently selected word with contents chosen by user.
// Note: currently only replaces words after highlighted word.  I think we can re-index
// all words at replacement or ignore time to have it wrap to the beginning if we want
// to.
function replaceAll()
{
	var strend;
	var idx;
	var origword;
	var localoffsetindex = offsetindex;

	origword = misps[wordindex].word;

	// Re-index everything past the current word.
	for (idx = wordindex; idx < misps.length; idx++)
	{
		misps[idx].start += localoffsetindex;
		misps[idx].end += localoffsetindex;
	}

	localoffsetindex = 0;

	for (idx = 0; idx < misps.length; idx++)
	{
		if (misps[idx].word == origword)
		{
			var strstart = "";
			if (misps[idx].start != 0)
				strstart = mispstr.slice(0, misps[idx].start + localoffsetindex);

			// Get the end of the string after the word we are replacing.
			strend = mispstr.slice(misps[idx].end + 1 + localoffsetindex);

			// Rebuild the string with the new word.
			mispstr = strstart + document.forms.spellingForm.changeto.value + strend;

			// Update offsetindex to compensate for replacing a word with a word
			// of a different length.
			localoffsetindex += document.forms.spellingForm.changeto.value.length - misps[idx].word.length;
		}

		// We have to re-index everything after replacements.
		misps[idx].start += localoffsetindex;
		misps[idx].end += localoffsetindex;
	}

	// Add the word to the ignore array.
	ignoredWords[origword] = true;

	// Reset offsetindex since we re-indexed.
	offsetindex = 0;

	nextWord(false);
}

// Highlight the word that was selected using the nextWord function.
function highlightWord()
{
	var strstart = "";
	var strend;

	// If this isn't the beginning of the string then get all of the string
	// that is before the word we are replacing.
	if (misps[wordindex].start != 0)
		strstart = mispstr.slice(0, misps[wordindex].start + offsetindex);

	// Get the end of the string after the word we are replacing.
	strend = mispstr.slice(misps[wordindex].end + 1 + offsetindex);

	// Rebuild the string with a span wrapped around the misspelled word
	// so we can highlight it in the div the user is viewing the string in.
	var divptr, newValue;
	divptr = document.getElementById("spellview");

	newValue = htmlspecialchars(strstart) + '<span class="highlight" id="h1">' + misps[wordindex].word + '</span>' + htmlspecialchars(strend);
	setInnerHTML(divptr, newValue.replace(/_\|_/g, '<br />'));

	// We could use scrollIntoView, but it's just not that great anyway.
	var spellview_height = typeof(document.getElementById("spellview").currentStyle) != "undefined" ? parseInt(document.getElementById("spellview").currentStyle.height) : document.getElementById("spellview").offsetHeight;
	var word_position = document.getElementById("h1").offsetTop;
	var current_position = document.getElementById("spellview").scrollTop;

	// The spellview is not tall enough!  Scroll down!
	if (spellview_height <= (word_position + current_position))
		document.getElementById("spellview").scrollTop = word_position + current_position - spellview_height + 32;
}

// Display the next misspelled word to the user and populate the suggested spellings box.
function nextWord(ignoreall)
{
	// Push ignored word onto ignoredWords array.
	if (ignoreall)
		ignoredWords[misps[wordindex].word] = true;

	// Update the index of all words we have processed...
	// This must be done to accomodate the replaceAll function.
	if (wordindex >= 0)
	{
		misps[wordindex].start += offsetindex;
		misps[wordindex].end += offsetindex;
	}

	// Increment the counter for the array of misspelled words.
	wordindex++;

	// Draw it and quit if there are no more misspelled words to evaluate.
	if (misps.length <= wordindex)
	{
		var divptr;
		divptr = document.getElementById("spellview");
		setInnerHTML(divptr, htmlspecialchars(mispstr).replace(/_\|_/g, "<br />"));

		while (document.forms.spellingForm.suggestions.options.length > 0)
			document.forms.spellingForm.suggestions.options[0] = null;

		alert(txt['done']);
		document.forms.spellingForm.change.disabled = true;
		document.forms.spellingForm.changeall.disabled = true;
		document.forms.spellingForm.ignore.disabled = true;
		document.forms.spellingForm.ignoreall.disabled = true;

		// Put line feeds back...
		mispstr = mispstr.replace(/_\|_/g, "\n");

		// Get a handle to the field we need to re-populate.
		window.opener.document.forms[spell_formname][spell_fieldname].value = mispstr;
		if (!window.opener.spellCheckDone)
			window.opener.document.forms[spell_formname][spell_fieldname].focus();
		else
			window.opener.spellCheckDone();

		window.close();
		return true;
	}

	// Check to see if word is supposed to be ignored.
	if (typeof(ignoredWords[misps[wordindex].word]) != "undefined")
	{
		nextWord(false);
		return false;
	}

	// Clear out the suggestions box!
	while (document.forms.spellingForm.suggestions.options.length > 0)
		document.forms.spellingForm.suggestions.options[0] = null;

	// Re-populate the suggestions box if there are any suggested spellings for the word.
	if (misps[wordindex].suggestions.length)
	{
		for (var sugidx = 0; sugidx < misps[wordindex].suggestions.length; sugidx++)
		{
			var newopt = new Option(misps[wordindex].suggestions[sugidx], misps[wordindex].suggestions[sugidx]);
			document.forms.spellingForm.suggestions.options[sugidx] = newopt;

			if (sugidx == 0)
			{
				newopt.selected = true;
				document.forms.spellingForm.changeto.value = newopt.value;
				document.forms.spellingForm.changeto.select();
			}
		}
	}

	if (document.forms.spellingForm.suggestions.options.length == 0)
		document.forms.spellingForm.changeto.value = "";

	highlightWord();

	return false;
}

function htmlspecialchars(thetext)
{
	thetext = thetext.replace(/\</g, "&lt;");
	thetext = thetext.replace(/\>/g, "&gt;");
	thetext = thetext.replace(/\n/g, "<br />");
	thetext = thetext.replace(/\ \ /g, " &nbsp;");

	return thetext;
}

function openSpellWin(width, height)
{
	window.open("", "spellWindow", "toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no,width=" + width + ",height=" + height);
}