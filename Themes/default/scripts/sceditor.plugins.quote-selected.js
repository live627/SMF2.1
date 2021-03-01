(function (sceditor) {
	'use strict';

	sceditor.plugins.quoteSelected = function ()
	{
		const
			once = {
				once: true
			},
			regex = /\d+/;
		var
			textSelection,
			editor,
			opts;

		// Traverse the DOM tree in our spinoff of jQuery's closest()
		var getClosest = function (el, divID)
		{
			if (!divID)
				return;

			do
			{
				// End the loop if quick edit is detected.
				if (el.nodeName === 'TEXTAREA' || el.nodeName === 'INPUT' || el.id === 'error_box')
					break;

				if (el.id === divID)
					return el;
			}
			while (el = el.parentNode);

			return;
		};

		var getSelectedText = function (divID)
		{
			if (!divID)
				return;

			var
				s,
				e,
				text,
				selection,
				container = document.createElement("div");

			if (window.getSelection)
				selection = window.getSelection();
			else if (document.selection && document.selection.type != 'Control')
				selection = document.selection.createRange();

			// Need to be sure the selected text does belong to the right div.
			for (var i = 0; i < selection.rangeCount; i++)
			{
				s = getClosest(selection.getRangeAt(i).startContainer, divID);
				e = getClosest(selection.getRangeAt(i).endContainer, divID);

				if (s && e)
				{
					container.appendChild(selection.getRangeAt(i).cloneContents());
					text = container.innerHTML;
					break;
				}
			}

			return text;
		};

		var response = function (XMLDoc)
		{
			var response = XMLDoc.getElementsByTagName('quote')[0].firstChild.nodeValue;
			editor.insert(editor.toBBCode(response.replace('%BodyPlaceholder%', textSelection)));
			editor.focus();
			location.hash = '#' + opts.sJumpAnchor;
			e.preventDefault();
		};

		var click = function (e)
		{
			var btn = this.parentNode;
			btn.style.display = 'none';

			// Do a call to make sure this is a valid message.
			var url = [
				smf_prepareScriptUrl(smf_scripturl) + 'action=quotefast',
				';quote=' + btn.id.match(regex)[0],
				';placeholder;xml'
			];
			getXMLDocument(url.join(''), response);
		};

		this.init = function ()
		{
			editor = this;
			opts = editor.opts.quoteSelectedOptions;

			document.addEventListener('mouseup', function (e)
			{
				var el = e.target.closest(opts.sContainerSelector);
				if (!el)
					return;

				var
					msgid = el.id.match(regex)[0],
					btnid = opts.sButtonID.replace('%PostID%', msgid),
					btn = document.getElementById(btnid);

				textSelection = getSelectedText(el.id);
				btn.style.display = textSelection ? '' : 'none';

				if (textSelection)
					btn.firstElementChild.addEventListener('click', click, once);
			});
		};
	};
})(sceditor);
