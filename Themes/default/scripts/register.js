function smfRegister(formID, passwordDifficultyLevel, regTextStrings)
{
	this.addVerify = addVerificationField;
	this.autoSetup = autoSetup;
	this.refreshMainPassword = refreshMainPassword;
	this.refreshVerifyPassword = refreshVerifyPassword;

	var verificationFields = new Array();
	var textStrings = regTextStrings ? regTextStrings : new Array();
	var passwordLevel = passwordDifficultyLevel ? passwordDifficultyLevel : 0;

	var validColor = '#F5FFF0';
	var invalidColor = '#FFF0F0';
	// Setup all the fields!
	autoSetup(formID);

	// This is a field which requires some form of verification check.
	function addVerificationField(fieldType, fieldID)
	{
		// Check the field exists.
		if (!document.getElementById(fieldID))
			return;

		// Get the handles.
		inputHandle = document.getElementById(fieldID);
		imageHandle = document.getElementById(fieldID + '_img') ? document.getElementById(fieldID + '_img') : false;
		divHandle = document.getElementById(fieldID + '_div') ? document.getElementById(fieldID + '_div') : false;

		// What is the event handler?
		eventHandler = false;
		if (fieldType == 'pwmain')
			eventHandler = refreshMainPassword;
		else if (fieldType == 'pwverify')
			eventHandler = refreshVerifyPassword;
		else if (fieldType == 'username')
			eventHandler = refreshUsername;
		else if (fieldType == 'reserved')
			eventHandler = refreshMainPassword;

		// Store this field.
		verificationFields[fieldType] = Array(6);
		verificationFields[fieldType][0] = fieldID;
		verificationFields[fieldType][1] = inputHandle;
		verificationFields[fieldType][2] = imageHandle;
		verificationFields[fieldType][3] = divHandle;
		verificationFields[fieldType][4] = fieldType;
		verificationFields[fieldType][5] = inputHandle.style.backgroundColor;

		// Step to it!
		if (eventHandler)
		{
			createEventListener(inputHandle);
			inputHandle.addEventListener('keyup', eventHandler, false);
			eventHandler();
		}

		// Make the div visible!
		if (divHandle)
			divHandle.style.display = '';
	}

	// A button to trigger a username search?
	function addUsernameSearchTrigger(elementID)
	{
		buttonHandle = document.getElementById(elementID);

		// Attach the event to this element.
		createEventListener(buttonHandle);
		buttonHandle.addEventListener('click', checkUsername, false);
	}

	// This function will automatically pick up all the necessary verification fields and initialise their visual status.
	function autoSetup(formID)
	{
		if (!document.getElementById(formID))
			return false;

		var curElement, curType;
		for (i = 0; i < document.getElementById(formID).elements.length; i++)
		{
			curElement = document.getElementById(formID).elements[i];

			// Does the ID contain the keyword 'autov'?
			if (curElement.id.indexOf('autov') != -1 && (curElement.type == 'text' || curElement.type == 'password'))
			{
				// This is probably it - but does it contain a field type?
				curType = 0;
				// Username can only be done with XML.
				if (curElement.id.indexOf('username') != -1 && window.XMLHttpRequest)
					curType = 'username';
				else if (curElement.id.indexOf('pwmain') != -1)
					curType = 'pwmain';
				else if (curElement.id.indexOf('pwverify') != -1)
					curType = 'pwverify';
				// This means this field is reserved and cannot be contained in the password!
				else if (curElement.id.indexOf('reserve') != -1)
					curType = 'reserved';

				// If we're happy let's add this element!
				if (curType)
					addVerificationField(curType, curElement.id);

				// If this is the username do we also have a button to find the user?
				if (curType == 'username' && document.getElementById(curElement.id + '_link'))
				{
					addUsernameSearchTrigger(curElement.id + '_link');
				}
			}
		}
	}

	// What is the password state?
	function refreshMainPassword()
	{
		if (!verificationFields['pwmain'])
			return false;

		var curPass = verificationFields['pwmain'][1].value;
		var stringIndex = '';

		// Is it a valid length?
		if ((curPass.length < 8 && passwordLevel >= 1) || curPass.length < 4)
			stringIndex = 'password_short';

		// More than basic?
		if (passwordLevel >= 1)
		{
			// If there is a username check it's not in the password!
			if (verificationFields['username'] && verificationFields['username'][1].value && curPass.indexOf(verificationFields['username'][1].value) != -1)
				stringIndex = 'password_reserved';

			// Any reserved fields?
			for (var i = 0; i < verificationFields.length; i++)
			{
				if (verificationFields[i][4] == 'reserved' && verificationFields[i][1].value && curPass.indexOf(verificationFields[i][1].value) != -1)
					stringIndex = 'password_reserved';
			}

			// Finally - is it hard and as such requiring mixed cases and numbers?
			if (passwordLevel > 1)
			{
				if (curPass == curPass.toLowerCase())
					stringIndex = 'password_numbercase';
				if (!curPass.match(/(\D\d|\d\D)/))
					stringIndex = 'password_numbercase';
			}
		}

		var isValid = stringIndex == '' ? true : false;
		if (stringIndex == '')
			stringIndex = 'password_valid';

		// Set the image.
		setVerificationImage(verificationFields['pwmain'][2], isValid, textStrings[stringIndex] ? textStrings[stringIndex] : '');
		verificationFields['pwmain'][1].style.backgroundColor = isValid ? validColor : invalidColor;

		// As this has changed the verification one may have too!
		if (verificationFields['pwverify'])
			refreshVerifyPassword();
	}

	// Check that the verification password matches the main one!
	function refreshVerifyPassword()
	{
		// Can't do anything without something to check again!
		if (!verificationFields['pwmain'])
			return false;

		// Check and set valid status!
		var isValid = verificationFields['pwmain'][1].value == verificationFields['pwverify'][1].value;
		alt = textStrings[isValid == 1 ? 'password_valid' : 'password_no_match'] ? textStrings[isValid == 1 ? 'password_valid' : 'password_no_match'] : '';
		setVerificationImage(verificationFields['pwverify'][2], isValid, alt);
		verificationFields['pwverify'][1].style.backgroundColor = isValid ? validColor : invalidColor;
	}

	// If the username is changed just revert the status of whether it's valid!
	function refreshUsername()
	{
		if (!verificationFields['username'])
			return false;

		// Restore the background color.
		if (verificationFields['username'][1].style.backgroundColor)
			verificationFields['username'][1].style.backgroundColor = verificationFields['username'][5];
		// Check the image is correct.
		alt = textStrings['username_check'] ? textStrings['username_check'] : '';
		setVerificationImage(verificationFields['username'][2], 'check', alt);

		// Check the password is still OK.
		refreshMainPassword();
	}

	// Check whether the username exists?
	function checkUsername()
	{
		if (!verificationFields['username'])
			return false;

		// Get the username and do nothing without one!
		var curUsername = verificationFields['username'][1].value;
		if (!curUsername)
			return false;

		ajax_indicator(true);

		// Request a search on that username.
		checkName = escape(textToEntities(curUsername).replace(/&#(\d+);/g, "%#$1%")).replace(/%26/g, "%25%23038%25");
		getXMLDocument(smf_prepareScriptUrl(smf_scripturl) + 'action=register;sa=usernamecheck;xml;username=' + checkName, checkUsernameCallback);
	}

	// Callback for getting the username data.
	function checkUsernameCallback(XMLDoc)
	{
		if (XMLDoc.getElementsByTagName("username"))
			isValid = XMLDoc.getElementsByTagName("username")[0].getAttribute("valid");
		else
			isValid = true;

		// What to alt?
		alt = textStrings[isValid == 1 ? 'username_valid' : 'username_invalid'] ? textStrings[isValid == 1 ? 'username_valid' : 'username_invalid'] : '';

		verificationFields['username'][1].style.backgroundColor = isValid == 1 ? validColor : invalidColor;
		setVerificationImage(verificationFields['username'][2], isValid == 1, alt);

		ajax_indicator(false);
	}

	// Set the image to be the correct type.
	function setVerificationImage(imageHandle, imageIcon, alt)
	{
		if (!imageHandle)
			return false;
		if (!alt)
			alt = '*';

		var curImage = imageIcon ? (imageIcon == 'check' ? 'field_check.gif' : 'field_valid.gif') : 'field_invalid.gif';
		imageHandle.src = smf_images_url + '/icons/' + curImage;
		imageHandle.alt = alt;
		imageHandle.title = alt;
	}
}