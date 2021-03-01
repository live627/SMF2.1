(function (sceditor) {
	'use strict';

	sceditor.plugins.quickReply = function ()
	{
		const regex = /\d+/;
		var
			editor,
			opts;

		var quote = function (e)
		{
			editor.insertQuoteFast(this);
			location.hash = '#' + opts.sJumpAnchor;
			e.preventDefault();
		};

		this.init = function ()
		{
			editor = this;
			opts = editor.opts.quickReplyOptions;

			var postContainers = document.querySelectorAll(opts.sPostContainersSelector);
			for (var postContainer of postContainers)
			{
				var
					post = postContainer.querySelector(opts.sPostContainerSelector),
					el = postContainer.querySelector(opts.sQuickButtonsSelector),
					msgid = post.id.match(regex)[0],
					a = el.children[0].firstElementChild;

				a.addEventListener('click', quote.bind(msgid));
			}
		};
	};
})(sceditor);
