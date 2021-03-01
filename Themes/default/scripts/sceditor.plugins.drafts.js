(function (sceditor) {
	'use strict';

	sceditor.plugins.drafts = function ()
	{
		var
			base = this,
			editor,
			form = document.forms.postmodify,
			isTypeof = function (type, arg) {
				return typeof arg === type;
			},
			isUndefined = isTypeof.bind(null, 'undefined'),
			bInDraftMode = false,
			lastValue,
			interval,
			opt;

		var cancel = function ()
		{
			bInDraftMode = false;
			document.getElementById('throbber').style.display = 'none';
		};

		// Make the call to save this draft in the background
		var save = function ()
		{
			var sPostdata = editor.val();
			var sPosticon = isUndefined(form['icon']) ? 'xx' : form['icon'].value;
			var sPostsubj = isUndefined(form['subject']) ? '' : form['subject'].value;

			// nothing to save or already posting or nothing changed?
			if (isEmptyText(sPostdata) || smf_formSubmitted || lastValue == sPostdata)
				return false;

			// Still saving the last one or other?
			if (bInDraftMode)
				return cancel();

			// Flag that we are saving a draft
			document.getElementById('throbber').style.display = '';
			bInDraftMode = true;

			// Get the form elements that we want to save
			var aSections = [
				'topic=' + parseInt(form.elements['topic'].value),
				'id_draft=' + 'id_draft' in form.elements ? parseInt(form.elements['id_draft'].value) : 0,
				'subject=' + escape(sPostsubj.php_to8bit()).replace(/\+/g, "%2B"),
				'message=' + escape(sPostdata.php_to8bit()).replace(/\+/g, "%2B"),
				'icon=' + escape(sPosticon.php_to8bit()).replace(/\+/g, "%2B"),
				'save_draft=true',
				smf_session_var + '=' + smf_session_id,
			];

			if (document.getElementById('check_lock') && document.getElementById('check_lock').checked)
				aSections.push('lock=1');
			if (document.getElementById('check_sticky') && document.getElementById('check_sticky').checked)
				aSections.push('sticky=1');

			// Send in document for saving and hope for the best
			sendXMLDocument(smf_prepareScriptUrl(smf_scripturl) + "action=post2;board=" + opt.iBoard + ";xml", aSections.join("&"), done);

			// Save the latest for compare
			lastValue = sPostdata;
		};

		// function to retrieve the to and bcc values from the pseudo arrays
		var getRecipient = function (sField)
		{
			var oRecipient = form.elements[sField];
			var aRecipient = [];

			if (oRecipient)
			{
				if ('value' in oRecipient)
					aRecipient.push(parseInt(oRecipient.value));
				else
				{
					for (var i = 0, n = oRecipient.length; i < n; i++)
						aRecipient.push(parseInt(oRecipient[i].value));
				}
			}
			return aRecipient;
		};

		// Make the call to save this PM draft in the background
		var savePM = function ()
		{
			var sPostdata = editor.val();

			// nothing to save or already posting or nothing changed?
			if (isEmptyText(sPostdata) || smf_formSubmitted || lastValue == sPostdata)
				return false;

			// Still saving the last one or some other?
			if (bInDraftMode)
				return cancel();

			// Flag that we are saving
			document.getElementById('throbber').style.display = '';
			bInDraftMode = true;

			// Get the to and bcc values
			var aTo = getRecipient('recipient_to[]');
			var aBcc = getRecipient('recipient_bcc[]');

			// Get the rest of the form elements that we want to save, and load them up
			var aSections = [
				'replied_to=' + parseInt(form.elements['replied_to'].value),
				'id_pm_draft=' + 'id_pm_draft' in form.elements ? parseInt(form.elements['id_pm_draft'].value) : 0,
				'subject=' + escape(form['subject'].value.php_to8bit()).replace(/\+/g, "%2B"),
				'message=' + escape(sPostdata.php_to8bit()).replace(/\+/g, "%2B"),
				'recipient_to=' + aTo,
				'recipient_bcc=' + aBcc,
				'save_draft=true',
				smf_session_var + '=' + smf_session_id,
			];

			// Send in (post) the document for saving
			sendXMLDocument.call(this, smf_prepareScriptUrl(smf_scripturl) + "action=pm;sa=send2;xml", aSections.join("&"), done);

			// Save the latest for compare
			lastValue = sPostdata;
		};

		// Callback function of the XMLhttp request for saving the draft message
		var done = function (XMLDoc)
		{
			// If it is not valid then clean up
			if (!XMLDoc || !XMLDoc.document.getElementByIdsByTagName('draft'))
				return cancel();

			// Grab the returned draft id and saved time from the response
			sCurDraftId = XMLDoc.document.getElementByIdsByTagName('draft')[0].getAttribute('id');
			sLastSaved = XMLDoc.document.getElementByIdsByTagName('draft')[0].childNodes[0].nodeValue;

			// Update the form to show we finished, if the id is not set, then set it
			document.getElementById(opt.sLastID).value = sCurDraftId;
			document.getElementById(opt.sLastNote).innerHTML = sLastSaved;

			// hide the saved draft infobox in the event they pressed the save draft button at some point
			if (opt.sType == 'post')
				document.getElementById('draft_section').style.display = 'none';

			cancel();
		};

		var trigger = function ()
		{
			if (!bInDraftMode)
			{
				if (!!opt.bPM)
					savePM();
				else
					save();
			}
		};

		base.init = function ()
		{
			editor = this;
			opt = editor.opts.draftOptions;
			interval = setInterval(trigger, opt.iFreq);
		};

		base.signalFocusEvent = function ()
		{
			if (!interval)
			interval = setInterval(trigger, opt.iFreq);
		};

		base.signalBlurEvent = function ()
		{
			clearInterval(interval);
		};
	};
})(sceditor);
