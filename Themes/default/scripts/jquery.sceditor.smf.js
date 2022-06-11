/**
 * Simple Machines Forum (SMF)
 *
 * @package SMF
 * @author Simple Machines https://www.simplemachines.org
 * @copyright 2022 Simple Machines and individual contributors
 * @license https://www.simplemachines.org/about/smf/license.php BSD
 *
 * @version 2.1 RC4
 */

(sceditor => {
	sceditor.icons.smf = function ()
	{
		let buttons = {};

		return {
			create(command, button)
			{
				buttons[command] = button;
			},
			update(isSourceMode, currentNode)
			{
				buttons['color'].firstChild.style.textDecoration = 'underline ' + (!isSourceMode && currentNode ? currentNode.ownerDocument.queryCommandValue('forecolor') : '');
			}
		};
	};

	sceditor.plugins.smf = function ()
	{
		let editor;
		let opts;
		let line;

		const appendEmoticon = (code, {newrow, url, tooltip}) => {
			if (newrow)
				line.appendChild(document.createElement('br'));

			const i = document.createElement("img");
			i.src = opts.emoticonsRoot + url;
			i.alt = code;
			i.title = tooltip;
			i.addEventListener('click', function (e)
			{
				if (editor.inSourceMode())
					editor.insertText(' ' + this.alt + ' ');
				else
					editor.wysiwygEditorInsertHtml(' <img src="' + this.src + '" data-sceditor-emoticon="' + this.alt + '"> ');

				e.preventDefault();
			});
			line.appendChild(i);
		};

		const createPopup = el => {
			const t = document.createElement("div");
			const cover = document.createElement('div');
			const root = document.createElement('div');

			const hide = () => {
				cover.classList.remove('show');
				document.removeEventListener('keydown', esc);
			};

			var esc = ({keyCode}) => {
				if (keyCode === 27)
					hide();
			};

			const a = document.createElement('button');

			root.appendChild(a);
			cover.appendChild(root);
			document.body.appendChild(cover);
			root.id = 'popup-container';
			cover.id = 'popup';
			a.id = 'close';
			cover.addEventListener('click', ({target}) => {
				if (target.id === 'popup')
					hide();
			});
			a.addEventListener('click', hide);
			document.addEventListener('keydown', esc);
			root.appendChild(el);
			root.appendChild(a);
			cover.classList.add('show');
			editor.hidePopup = hide;
		};

		const ev = ({children, nextSibling}, col, row) => {
			for (let i = 1; i <= 144; i++)
				children[i - 1].className = Math.ceil(i / 12) <= col && (i % 12 || 12) <= row ? 'highlight2' : 'windowbg';

			nextSibling.textContent = col + 'x' + row;
		};

		const tbl = callback => {
			const content = document.createElement('div');
			content.className = 'sceditor-insert-table';
			const div = document.createElement('div');
			div.className = 'sceditor-insert-table-grid';
			div.addEventListener('mouseleave', ev.bind(null, div, 0, 0));
			const div2 = document.createElement('div');
			div2.className = 'largetext';
			div2.textContent = '0x0';

			for (let i = 1; i <= 144; i++)
			{
				const row = i % 12 || 12;
				const col = Math.ceil(i / 12);
				const span = document.createElement('span');
				span.className = 'windowbg';
				span.addEventListener('mouseenter', ev.bind(null, div, col, row));
				span.addEventListener('click', function (col, row) {
					callback(col, row);
					editor.hidePopup();
					editor.focus();
				}.bind(null, col, row));
				div.appendChild(span);
			}
			content.appendChild(div);
			content.appendChild(div2);
			createPopup(content);
		};

		this.init = function ()
		{
			editor = this;
			opts = editor.opts;

			if (opts.emoticonsEnabled)
			{
				const emoticons = opts.emoticons;
				content = opts.smileyContainer;
				if (emoticons.dropdown)
				{
					line = document.createElement('div');
					sceditor.utils.each(emoticons.dropdown, appendEmoticon);
					content.appendChild(line);
				}

				if (emoticons.more)
				{
					const moreButton = document.createElement('button');
					moreButton.className = 'button_submit';
					moreButton.textContent = editor._('More');
					moreButton.addEventListener('click', e => {
						line = document.createElement('div');
						sceditor.utils.each(emoticons.more, appendEmoticon);
						createPopup(line);

						e.preventDefault();
					});
					content.appendChild(moreButton);
				}
				content.className = 'sceditor-insertemoticon';
			}
			editor.commands.table = {
				state(parents, firstBlock) {
					return firstBlock && firstBlock.closest('table') ? 1 : 0;
				},
				exec() {
					tbl((cols, rows) => {
						editor.wysiwygEditorInsertHtml(
						'<table><tr><td>',
						'</td>'+ Array(cols).join('<td><br></td>') + Array(rows).join('</tr><tr>' + Array(cols+1).join('<td><br></td>')) + '</tr></table>'
						);
					});
				},
				txtExec() {
					tbl((cols, rows) => {
						editor.insertText(
						'[table]\n[tr]\n[td]',
						'[/td]'+ Array(cols).join('\n[td][/td]') + Array(rows).join('\n[/tr]\n[tr]' + Array(cols+1).join('\n[td][/td]')) + '\n[/tr]\n[/table]'
						);
					});
				},
			};
			editor.insertQuoteFast = messageid =>
			{
				getXMLDocument(
					smf_prepareScriptUrl(smf_scripturl) + 'action=quotefast;quote=' + messageid + ';xml',
					XMLDoc =>
					{
						var text = '';

						for (var i = 0, n = XMLDoc.getElementsByTagName('quote')[0].childNodes.length; i < n; i++)
							text += XMLDoc.getElementsByTagName('quote')[0].childNodes[i].nodeValue;
						editor.insert(text);

						// Manually move cursor to after the quote.
						var
							rangeHelper = editor.getRangeHelper(),
							parent = rangeHelper.parentNode();
						if (parent && parent.nodeName === 'BLOCKQUOTE')
						{
							var range = rangeHelper.selectedRange();
							range.setStartAfter(parent);
							rangeHelper.selectRange(range);
						}

						ajax_indicator(false);
					}
				);
			};
		};
	};

	const setCustomTextualCommands = cmds => {
		for (let [code, description, before, after] of cmds)
		{
			const obj = {
				tooltip: description || code
			};
			if (!sceditor.commands[code])
			{
				obj.exec = function ()
				{
					this.insertText(before, after);
				};
				if (before === after)
					obj.txtExec = [before];
				else if (before)
					obj.txtExec = [before, after];
			}
			sceditor.command.set(code, obj);
		}
	};
	const createFn = sceditor.create;
	sceditor.create = (textarea, options) => {
		setCustomTextualCommands(options.customTextualCommands);
		createFn(textarea, options);
	};
})(sceditor).set(
	'pre', {
		txtExec: ["[pre]", "[/pre]"],
		exec: function () {
			this.wysiwygEditorInsertHtml('<pre>', '</pre>');
		}
	}
);
sceditor.command.set(
	'link', {
		exec: function (caller) {
			var editor = this;

			editor.commands.link._dropDown(editor, caller, function (url, text) {
				if (!editor.getRangeHelper().selectedHtml() || text) {
					text = text || url;

					editor.wysiwygEditorInsertHtml(
						'<a data-type="url" href="' +
						sceditor.escapeEntities(url) + '">' +
						sceditor.escapeEntities(text, true) + '</a>'
					);
				} else {
					// Can't just use `editor.execCommand('createlink', url)`
					// because we need to set a custom attribute.
					editor.wysiwygEditorInsertHtml(
						'<a data-type="url" href="' +
						sceditor.escapeEntities(url) + '">', '</a>'
					);
				}
			});
		}
	}
).set(
	'bulletlist', {
		txtExec: function (caller, selected) {
			if (selected)
				this.insertText(
					'[list]\n[li]' +
					selected.split(/\r?\n/).join('[/li]\n[li]') +
					'[/li]\n[/list]'
				);
			else
				this.insertText('[list]\n[li]', '[/li]\n[li][/li]\n[/list]');
		}
	}
).set(
	'orderedlist', {
		txtExec: function (caller, selected) {
			if (selected)
				this.insertText(
					'[list type=decimal]\n[li]' +
					selected.split(/\r?\n/).join('[/li]\n[li]') +
					'[/li]\n[/list]'
				);
			else
				this.insertText('[list type=decimal]\n[li]', '[/li]\n[li][/li]\n[/list]');
		}
	}
).set(
	'floatleft', {
		txtExec: ["[float=left max=45%]", "[/float]"],
		exec: function () {
			this.wysiwygEditorInsertHtml('<div class="floatleft">', '</div>');
		}
	}
).set(
	'floatright', {
		txtExec: ["[float=right max=45%]", "[/float]"],
		exec: function () {
			this.wysiwygEditorInsertHtml('<div class="floatright">', '</div>');
		}
	}
).set(
	'youtube', {
		exec: function (caller) {
			var editor = this;

			editor.commands.youtube._dropDown(editor, caller, function (id, time) {
				editor.wysiwygEditorInsertHtml('<div class="videocontainer"><div><iframe frameborder="0" allowfullscreen src="https://www.youtube-nocookie.com/embed/' + id + '?wmode=opaque&start=' + time + '" data-youtube-id="' + id + '" loading="lazy"></iframe></div></div>');
			});
		}
	}
).set(
	'email', {
		exec: function (caller)
		{
			var editor = this;

			editor.commands.email._dropDown(
				editor,
				caller,
				function (email, text)
				{
					if (!editor.getRangeHelper().selectedHtml() || text)
						editor.wysiwygEditorInsertHtml(
							'<a data-type="email" href="' +
							'mailto:' + sceditor.escapeEntities(email) + '">' +
								sceditor.escapeEntities(text || email) +
							'</a>'
						);
					else
						// Can't just use `editor.execCommand('createlink', email)`
						// because we need to set a custom attribute.
						editor.wysiwygEditorInsertHtml(
							'<a data-type="email" href="mailto:' +
							sceditor.escapeEntities(email) + '">', '</a>'
						);
				}
			);
		},
	}
).set(
	'image', {
		exec: function (caller)
		{
			var editor = this;

			editor.commands.image._dropDown(
				editor,
				caller,
				'',
				function (url, width, height)
				{
					var attrs = ['src="' + sceditor.escapeEntities(url) + '"'];

					if (width)
						attrs.push('width="' + sceditor.escapeEntities(width, true) + '"');

					if (height)
						attrs.push('height="' + sceditor.escapeEntities(height, true) + '"');

					editor.wysiwygEditorInsertHtml(
						'<img ' + attrs.join(' ') + '>'
					);
				}
			);
		}
	}
);

sceditor.formats.bbcode.set(
	'abbr', {
		tags: {
			abbr: {
				title: null
			}
		},
		format(element, content) {
			return '[abbr=' + element.getAttribute('title') + ']' + content + '[/abbr]';
		},
		html: '<abbr title="{defaultattr}">{0}</abbr>'
	}
).set(
	'list', {
		breakStart: true,
		isInline: false,
		// allowedChildren: ['*', 'li'], // Disabled for SCE 2.1.2 because it triggers a bug with inserting extra line breaks
		html: function (element, attrs, content) {
			var style = '';
			var code = 'ul';
			var olTypes = new Array('decimal', 'decimal-leading-zero', 'lower-roman', 'upper-roman', 'lower-alpha', 'upper-alpha', 'lower-greek', 'upper-greek', 'lower-latin', 'upper-latin', 'hebrew', 'armenian', 'georgian', 'cjk-ideographic', 'hiragana', 'katakana', 'hiragana-iroha', 'katakana-iroha');

			if (attrs.type) {
				style = ' style="list-style-type: ' + attrs.type + '"';

				if (olTypes.indexOf(attrs.type) > -1)
					code = 'ol';
			}
			else
				style = ' style="list-style-type: disc"';

			return '<' + code + style + '>' + content + '</' + code + '>';
		}
	}
).set(
	'ul', {
		tags: {
			ul: null
		},
		breakStart: true,
		isInline: false,
		html: '<ul>{0}</ul>',
		format: function (element, content) {
			if ($(element).css('list-style-type') == 'disc')
				return '[list]' + content + '[/list]';
			else
				return '[list type=' + $(element).css('list-style-type') + ']' + content + '[/list]';
		}
	}
).set(
	'ol', {
		tags: {
			ol: null
		},
		breakStart: true,
		isInline: false,
		html: '<ol>{0}</ol>',
		format: function (element, content) {
			if ($(element).css('list-style-type') == 'none')
				return '[list type=decimal]' + content + '[/list]';
			else
				return '[list type=' + $(element).css('list-style-type') + ']' + content + '[/list]';
		}
	}
).set(
	'li', {
		tags: {
			li: null
		},
		isInline: false,
		closedBy: ['/ul', '/ol', '/list', 'li', '*', '@', '+', 'x', 'o', 'O', '0'],
		html: '<li data-bbc-tag="li">{0}</li>',
		format: function (element, content) {
			var	element = $(element),
				token = 'li',
				allowedTokens = ['li', '*', '@', '+', 'x', 'o', 'O', '0'];

			if (element.attr('data-bbc-tag') && allowedTokens.indexOf(element.attr('data-bbc-tag') > -1))
				token = element.attr('data-bbc-tag');

			return '[' + token + ']' + content + (token === 'li' ? '[/' + token + ']' : '');
		},
	}
);
sceditor.formats.bbcode.set(
	'*', {
		tags: {
			li: {
				'data-bbc-tag': ['*']
			}
		},
		isInline: false,
		closedBy: ['/ul', '/ol', '/list', 'li', '*', '@', '+', 'x', 'o', 'O', '0'],
		excludeClosing: true,
		html: '<li type="disc" data-bbc-tag="*">{0}</li>',
		format: '[*]{0}',
	}
);
sceditor.formats.bbcode.set(
	'@', {
		tags: {
			li: {
				'data-bbc-tag': ['@']
			}
		},
		isInline: false,
		closedBy: ['/ul', '/ol', '/list', 'li', '*', '@', '+', 'x', 'o', 'O', '0'],
		excludeClosing: true,
		html: '<li type="disc" data-bbc-tag="@">{0}</li>',
		format: '[@]{0}',
	}
);
sceditor.formats.bbcode.set(
	'+', {
		tags: {
			li: {
				'data-bbc-tag': ['+']
			}
		},
		isInline: false,
		closedBy: ['/ul', '/ol', '/list', 'li', '*', '@', '+', 'x', 'o', 'O', '0'],
		excludeClosing: true,
		html: '<li type="square" data-bbc-tag="+">{0}</li>',
		format: '[+]{0}',
	}
);
sceditor.formats.bbcode.set(
	'x', {
		tags: {
			li: {
				'data-bbc-tag': ['x']
			}
		},
		isInline: false,
		closedBy: ['/ul', '/ol', '/list', 'li', '*', '@', '+', 'x', 'o', 'O', '0'],
		excludeClosing: true,
		html: '<li type="square" data-bbc-tag="x">{0}</li>',
		format: '[x]{0}',
	}
);
sceditor.formats.bbcode.set(
	'o', {
		tags: {
			li: {
				'data-bbc-tag': ['o']
			}
		},
		isInline: false,
		closedBy: ['/ul', '/ol', '/list', 'li', '*', '@', '+', 'x', 'o', 'O', '0'],
		excludeClosing: true,
		html: '<li type="circle" data-bbc-tag="o">{0}</li>',
		format: '[o]{0}',
	}
);
sceditor.formats.bbcode.set(
	'O', {
		tags: {
			li: {
				'data-bbc-tag': ['O']
			}
		},
		isInline: false,
		closedBy: ['/ul', '/ol', '/list', 'li', '*', '@', '+', 'x', 'o', 'O', '0'],
		excludeClosing: true,
		html: '<li type="circle" data-bbc-tag="O">{0}</li>',
		format: '[o]{0}',
	}
);
sceditor.formats.bbcode.set(
	'0', {
		tags: {
			li: {
				'data-bbc-tag': ['0']
			}
		},
		isInline: false,
		closedBy: ['/ul', '/ol', '/list', 'li', '*', '@', '+', 'x', 'o', 'O', '0'],
		excludeClosing: true,
		html: '<li type="circle" data-bbc-tag="0">{0}</li>',
		format: '[o]{0}',
	}
).set(
	'img', {
		tags: {
			img: {
				src: null
			}
		},
		allowsEmpty: true,
		quoteType: $.sceditor.BBCodeParser.QuoteType.never,
		format: function (element, content) {
			var	element = $(element),
				attribs = '',
				style = function (name) {
					return element.style ? element.style[name] : null;
				};

			// check if this is an emoticon image
			if (typeof element.attr('data-sceditor-emoticon') !== "undefined")
				return content;

			// only add width and height if one is specified
			if (element.attr('width') || style('width'))
				attribs += " width=" + element.attr('width');
			if (element.attr('height') || style('height'))
				attribs += " height=" + element.attr('height');
			if (element.attr('alt'))
				attribs += " alt=" + element.attr('alt');

			// Is this an attachment?
			if (element.attr('data-attachment'))
			{
				attribs = " id=" + element.attr('data-attachment') + attribs;
				if (element.attr('data-type'))
					attribs += " type=" + element.attr('data-type');

				return '[attach' + attribs + ']' + element.attr('title') + '[/attach]';
			}
			else if (element.attr('title'))
				attribs += " title=" + element.attr('title');

			return '[img' + attribs + ']' + element.attr('src') + '[/img]';
		},
		html: function (token, attrs, content) {
			var	parts,
				attribs = '';

			// handle [img width=340 height=240]url[/img]
			if (typeof attrs.width !== "undefined")
				attribs += ' width="' + attrs.width + '"';
			if (typeof attrs.height !== "undefined")
				attribs += ' height="' + attrs.height + '"';
			if (typeof attrs.alt !== "undefined")
				attribs += ' alt="' + attrs.alt + '"';
			if (typeof attrs.title !== "undefined")
				attribs += ' title="' + attrs.title + '"';

			return '<img' + attribs + ' src="' + content + '">';
		}
	}
).set(
	'attach', {
		tags: {
			img: {
				'data-attachment': null
			},
			a: {
				'data-attachment': null
			}
		},
		allowsEmpty: true,
		quoteType: $.sceditor.BBCodeParser.QuoteType.never,
		format: function (element, content) {
			var	element = $(element),
				attribs = '',
				attach_type,
				style = function (name) {
					return element.style ? element.style[name] : null;
				},
				index;

			attribs += " id=" + element.attr('data-attachment');
			if (element.attr('width') || style('width'))
				attribs += " width=" + element.attr('width');
			if (element.attr('height') || style('height'))
				attribs += " height=" + element.attr('height');
			if (element.attr('alt'))
				attribs += " alt=" + element.attr('alt');

			if (typeof current_attachments !== "undefined")
				for (index = 0; index < current_attachments.length; ++index) {
					if (current_attachments[index]['attachID'] == element.attr('data-attachment')) {
						attach_type = current_attachments[index]['type'];
						break;
					}
				}

			if (element.attr('title') && attach_type.indexOf("image") === 0)
				content = element.attr('title');

			return '[attach' + attribs + ']' + content + '[/attach]';
		},
		html: function (token, attrs, content) {
			var parts,
				attribs = '',
				attach_type,
				index;

			// Handles SMF 2.1 final format
			if (typeof attrs.id !== "undefined")
				var id = attrs.id;
			// Handles format from SMF 2.1 betas
			else {
				var id = content;
				if (typeof attrs.name !== "undefined")
					content = attrs.name;
			}

			if (typeof current_attachments !== "undefined")
				for (index = 0; index < current_attachments.length; ++index) {
					if (current_attachments[index]['attachID'] == id) {
						attach_type = current_attachments[index]['type'];
						break;
					}
				}

			// If id is not an integer, bail out
			if (!$.isNumeric(id) || Math.floor(id) != +id || +id <= 0) {

				attribs += ' id=' + id;
				if (typeof attrs.width !== "undefined")
					attribs += ' width=' + attrs.width;
				if (typeof attrs.height !== "undefined")
					attribs += ' height=' + attrs.height;
				if (typeof attrs.alt !== "undefined")
					attribs += ' alt=' + attrs.alt;

				return '[attach' + attribs + ']' + content + '[/attach]';
			}

			attribs += ' data-attachment="' + id + '"'
			if (typeof attrs.alt !== "undefined")
				attribs += ' alt="' + attrs.alt + '"';

			// Is this an image?
			if ((typeof attach_type !== "undefined" && attach_type.indexOf("image") === 0)) {
				attribs += ' title="' + content + '"';
				if (typeof attrs.width !== "undefined")
					attribs += ' width="' + attrs.width + '"';
				if (typeof attrs.height !== "undefined")
					attribs += ' height="' + attrs.height + '"';

				var contentUrl = smf_scripturl +'?action=dlattach;attach='+ id + ';type=preview;thumb';
				contentIMG = new Image();
				contentIMG.src = contentUrl;
			}

			// If not an image, show a boring ol' link
			if (typeof contentUrl === "undefined" || contentIMG.getAttribute('width') == 0)
				return '<a data-type="attach" href="' + smf_scripturl + '?action=dlattach;attach=' + id + ';type=preview;file"' + attribs + '>' + content + '</a>';
			// Show our purdy li'l picture
			else
				return '<img' + attribs + ' src="' + contentUrl + '">';
		}
	}
).set(
	'email', {
		allowsEmpty: true,
		quoteType: sceditor.BBCodeParser.QuoteType.never,
		tags: {
			a: {
				'data-type': ['email']
			}
		},
		format: function (element, content)
		{
			return '[email=' + element.href.substr(7) + ']' + content + '[/email]';
		},
		html: function (token, attrs, content)
		{
			return '<a data-type="email" href="mailto:' + sceditor.escapeEntities(attrs.defaultattr || content, true) + '">' + content + '</a>';
		}
	}
).set(
	'url', {
		allowsEmpty: true,
		quoteType: sceditor.BBCodeParser.QuoteType.always,
		tags: {
			a: {
				'data-type': ['url']
			}
		},
		format: function (element, content)
		{
			return '[url=' + decodeURI(element.href) + ']' + content + '[/url]';
		},
		html: function (token, attrs, content)
		{
			return '<a data-type="url" href="' + encodeURI(attrs.defaultattr || content) + '">' + content + '</a>';
		}
	}
).set(
	'iurl', {
		allowsEmpty: true,
		quoteType: sceditor.BBCodeParser.QuoteType.always,
		tags: {
			a: {
				'data-type': ['iurl']
			}
		},
		format: function (element, content)
		{
			return '[iurl=' + decodeURI(element.href) + ']' + content + '[/iurl]';
		},
		html: function (token, attrs, content)
		{
			return '<a data-type="iurl" href="' + encodeURI(attrs.defaultattr || content) + '">' + content + '</a>';
		}
	}
).set(
	'pre', {
		tags: {
			pre: null
		},
		isBlock: true,
		format: "[pre]{0}[/pre]",
		html: "<pre>{0}</pre>\n"
	}
).set(
	'php', {
		isInline: false,
		format: "[php]{0}[/php]",
		html: '<code class="php">{0}</code>'
	}
).set(
	'code', {
		tags: {
			code: null
		},
		isInline: false,
		allowedChildren: ['#', '#newline'],
		format: function (element, content) {
			if ($(element).hasClass('php'))
				return '[php]' + content.replace('&#91;', '[') + '[/php]';

			var
				dom = sceditor.dom,
				attr = dom.attr,
				title = attr(element, 'data-title'),
				from = title ?' =' + title : '';

			return '[code' + from + ']' + content.replace('&#91;', '[') + '[/code]';
		},
		html: function (element, attrs, content) {
			var from = attrs.defaultattr ? ' data-title="' + attrs.defaultattr + '"'  : '';

			return '<code data-name="' + this.opts.txtVars.code + '"' + from + '>' + content.replace('[', '&#91;') + '</code>'
		}
	}
).set(
	'quote', {
		tags: {
			blockquote: null,
			cite: null
		},
		quoteType: sceditor.BBCodeParser.QuoteType.never,
		breakBefore: false,
		isInline: false,
		format: function (element, content)
		{
			var attrs = '';
			var author = element.getAttribute('data-author');
			var date = element.getAttribute('data-date');
			var link = element.getAttribute('data-link');

			// The <cite> contains only the graphic for the quote, so we can skip it
			if (element.tagName === 'CITE')
				return '';

			if (author)
				attrs += ' author=' + author.php_unhtmlspecialchars();
			if (link)
				attrs += ' link=' + link;
			if (date)
				attrs += ' date=' + date;

			return '[quote' + attrs + ']' + content + '[/quote]';
		},
		html: function (element, attrs, content)
		{
			var attr_author = '', author = '';
			var attr_date = '', sDate = '';
			var attr_link = '', link = '';

			if (attrs.author || attrs.defaultattr)
			{
				attr_author = attrs.author || attrs.defaultattr;
				author = bbc_quote_from + ': ' + attr_author;
			}

			if (attrs.link)
			{
				attr_link = attrs.link;
				link = attr_link.substr(0, 7) == 'http://' ? attr_link : smf_prepareScriptUrl(smf_scripturl) + attr_link;
				author = '<a href="' + link + '">' + (author || bbc_quote_from + ': ' + link) + '</a>';
			}

			if (attrs.date)
			{
				attr_date = attrs.date;
				sDate = '<date timestamp="' + attr_date + '">' + new Date(attr_date * 1000).toLocaleString() + '</date>';

				if (author !== '')
					author += ' ' + bbc_search_on;
			}

			return '<blockquote data-author="' + attr_author + '" data-date="' + attr_date + '" data-link="' + attr_link + '"><cite>' + (author || bbc_quote) + ' ' + sDate + '</cite>' + content + '</blockquote>';
		}
	}
).set(
	'font', {
		format: function (element, content) {
			var element = $(element);
			var font;

			// Get the raw font value from the DOM
			if (!element.is('font') || !(font = element.attr('face'))) {
				font = element.css('font-family');
			}

			// Strip all quotes
			font = font.replace(/['"]/g, '');

			return '[font=' + font + ']' + content + '[/font]';
		}
	}
).set(
	'member', {
		isInline: true,
		tags: {
			a: {
				'data-mention': null
			}
		},
		format: function (element, content) {
			return '[member='+ $(element).attr('data-mention') +']'+ content.replace('@','') +'[/member]';
		},
		html: function (token, attrs, content) {
			if (typeof attrs.defaultattr === "undefined" || attrs.defaultattr.length === 0)
				attrs.defaultattr = content;

			return '<a href="' + smf_scripturl +'?action=profile;u='+ attrs.defaultattr + '" class="mention" data-mention="'+ attrs.defaultattr + '">@'+ content.replace('@','') +'</a>';
		}
	}
).set(
	'float', {
		tags: {
			div: {
				"class": ["floatleft", "floatright"],
			},
		},
		isInline: false,
		skipLastLineBreak: true,
		format: function (element, content) {
			var element = $(element);
			if (!element.css('float'))
				return content;

			side = (element[0].className == 'floatleft' ? 'left' : 'right');
			max = ' max=' + (element.css('max-width') != "none" ? element.css('max-width') : '45%');

			return '[float=' + side + max + ']' + content + '[/float]';
		},
		html: function (token, attrs, content) {
			if (typeof attrs.defaultattr === "undefined")
				return content;

			floatclass = attrs.defaultattr.indexOf('left') == 0 ? 'floatleft' : 'floatright';
			style = typeof attrs.max !== "undefined" ? ' style="max-width:' + attrs.max + (+attrs.max === parseInt(attrs.max) ? 'px' : '') + ';"' : '';

			return '<div class="' + floatclass + '"' + style + '>' + content + '</div>';
		}
	}
).set(
	'youtube', {
		tags: {
			div: {
				'data-youtube-id': null
			}
		},
		isInline: false,
		skipLastLineBreak: true,
		format: el => `[youtube]${el.getAttribute('data-youtube-id')}[/youtube]`,
		html: '<div data-youtube-id="{0}"><iframe frameborder="0" src="https://www.youtube-nocookie.com/embed/{0}?wmode=opaque" allowfullscreen></iframe></div>'
	}
);