(function (sceditor) {
	'use strict';

	sceditor.plugins.xmlPreview = function ()
	{
		var
			editor,
			form = document.forms.postmodify,
			previewButton = form.preview,
			opts;

		var preview = function (event)
		{
			event.preventDefault();

			// @todo Currently not sending option checkboxes.
			var x = [
				opts.sPostBoxContainerID + '=' + editor.val().php_to8bit().php_urlencode()
			];
			var textFields = ['subject', opts.sSessionVar, 'icon', 'guestname', 'email', 'evtitle', 'question'];
			var numericFields = [
				'board', 'topic', 'last_msg',
				'eventid', 'calendar', 'year', 'month', 'day',
				'poll_max_votes', 'poll_expire', 'poll_change_vote', 'poll_hide'
			];
			var checkboxFields = [
				'ns'
			];

			// Poll options.
			for (var i of document.getElementsByName('options[]'))
				textFields.push(i.name);

			// Text Fields.
			for (var i of textFields)
				if (i in form)
					x.push(i + '=' + form[i].value.php_to8bit().php_urlencode());

			// Numbers.
			for (var i of numericFields)
				if (i in form && 'value' in form[i])
					x.push(i + '=' + parseInt(form[i].value));

			// Checkboxes.
			for (var i of checkboxFields)
				if (i in form && form[i].checked)
					x.push(i + '=' + form[i].value);

			var url = [
				smf_prepareScriptUrl(smf_scripturl) + 'action=post2',
				opts.iCurrentBoard ? ';board=' + opts.iCurrentBoard : '',
				opts.bMakePoll ? ';poll' : '',
				';preview;xml'
			];
			sendXMLDocument(url.join(''), x.join('&'), response);

			document.getElementById(opts.sPreviewSectionContainerID).style.display = '';
			setInnerHTML(document.getElementById(opts.sPreviewSubjectContainerID), opts.sTxtPreviewTitle);
			setInnerHTML(document.getElementById(opts.sPreviewBodyContainerID), opts.sTxtPreviewFetch);
		};

		var errors = function (errors)
		{
			var
				oErrorsContainerID = document.getElementById(opts.sErrorsContainerID),
				oErrorsSeriousContainerID = document.getElementById(opts.sErrorsSeriousContainerID),
				oErrorsListContainerID = document.getElementById(opts.sErrorsListContainerID),
				oCaptionContainerID = document.getElementById(opts.oCaptionContainerID),
				errorList = [],
				numErrors = errorList.length;

			for (var error of errors.getElementsByTagName('error'))
				errorList.push(error.firstChild.nodeValue)

			oErrorsContainerID.style.display = errorList.length == 0 ? 'none' : '';
			oErrorsContainerID.className = errors.getAttribute('serious') == 1 ? 'errorbox' : 'noticebox';
			oErrorsSeriousContainerID.style.display = oErrorsContainerID.style.display;
			oErrorsListContainerID.innerHTML = opts.sErrorListBefore + errorList.join(opts.sErrorListBetween) + opts.sErrorListAfter;

			// Adjust the color of captions if the given data is erroneous.
			for (var caption of errors.getElementsByTagName('caption'))
			{
				var el = document.getElementById(opts.sCaptionContainerID.replace('%ID%', caption.getAttribute('name')));
				if (el)
					el.className = caption.getAttribute('class');
			}

			if (errors.getElementsByTagName('post_error').length == 1)
				form.className = 'has_error';
			else
				form.className = '';
		};

		var posts = function (root)
		{
			var
				ignoredReplies = [],
				ignoring,
				newPostsRoot = root.getElementsByTagName('new_posts')[0],
				newPosts = newPostsRoot ? newPostsRoot.getElementsByTagName('post') : [],
				newPostID,
				newPostsHTML = '',
				newPostsContainer = document.getElementById('recent');

			for (var newPost of newPosts)
			{
				newPostID = newPost.getAttribute("id");

				ignoring = newPost.getElementsByTagName("is_ignored")[0].firstChild.nodeValue != 0;
				if (ignoring)
					ignoredReplies.push(newPostID);

				newPostsHTML += opts.newPostsTemplate
					.replaceAll('%PostID%', newPostID)
					.replaceAll('%PosterName%', newPost.getElementsByTagName("poster")[0].firstChild.nodeValue)
					.replaceAll('%PostTime%', newPost.getElementsByTagName("time")[0].firstChild.nodeValue)
					.replaceAll('%PostBody%', newPost.getElementsByTagName("message")[0].firstChild.nodeValue)
					.replaceAll('%IgnoredStyle%', ignoring ?  'display: none' : '');
			}

			// Remove the new image from old-new replies!
			newPostsContainer.querySelectorAll('.new_posts').forEach(e => e.remove());

			newPostsContainer.firstElementChild.insertAdjacentHTML('afterend', newPostsHTML);

			for (var newPost of newPosts)
			{
				newPostID = newPost.getAttribute("id");
				var el = document.getElementById('msg_' + newPostID + '_actions');
				el.children[1].firstElementChild.addEventListener('click', () => editor.insertQuoteFast(newPostID));
			}

			// Set the new last message id.
			if ('last_msg' in form)
				form.last_msg.value = root.getElementsByTagName('last_msg')[0].firstChild.nodeValue;

			for (ignoredReply of ignoredReplies)
				new smc_Toggle({
					bToggleEnabled: true,
					bCurrentlyCollapsed: true,
					aSwappableContainers: [
						'msg_' + ignoredReply + '_body',
						'msg_' + ignoredReply + '_actions',
					],
					aSwapLinks: [
						{
							sId: 'msg_' + ignoredReply + '_ignored_link',
							msgExpanded: '',
							msgCollapsed: opts.sTxtIgnoreUserPost
						}
					]
				});
		};

		var response = function (XMLDoc)
		{
			if (!XMLDoc)
			{
				previewButton.removeEventListener('click', preview);
				previewButton.click();
			}
			var
				oPreviewSubjectContainerID = document.getElementById(opts.sPreviewSubjectContainerID),
				oPreviewBodyContainerID = document.getElementById(opts.sPreviewBodyContainerID),
				root = XMLDoc.getElementsByTagName('smf')[0],
				preview = root.getElementsByTagName('preview')[0],
				body = preview.getElementsByTagName('body')[0].firstChild.nodeValue;

			// Show the preview section.
			setInnerHTML(oPreviewSubjectContainerID, preview.getElementsByTagName('subject')[0].firstChild.nodeValue)
			setInnerHTML(oPreviewBodyContainerID, body);
			oPreviewBodyContainerID.className = 'windowbg';

			// Show a list of errors (if any).
			errors(root.getElementsByTagName('errors')[0]);

			if ('last_msg' in form)
				posts(root);

			location.hash = '#' + opts.sPreviewSectionContainerID;
		};

		this.init = function ()
		{
			editor = this;
			opts = editor.opts.previewOptions;

			if (opts.sPreviewLinkContainerID)
				previewButton = document.getElementById(opts.sPreviewLinkContainerID);

			previewButton.addEventListener('click', preview);
		};
	};
})(sceditor);
