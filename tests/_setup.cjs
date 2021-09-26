
const JSDOM = require('jsdom').JSDOM;

/**
 *  Simulate browser environment for nodejs.
 */
const cfg         = { url: 'http://localhost' };
const dom         = new JSDOM('', cfg);
var $ = jQuery    = require('jquery')(dom.window);
dom.window.jQuery = dom.window.$ = $;
global.window     = dom.window;
global.document   = dom.window.document;

Object.keys(global.window).forEach((property) =>
{
	if (typeof global[ property ] === 'undefined')
		global[ property ] = global.window[ property ];
});

global.Element   = window.Element;
global.Image     = window.Image;
// maybe more of: global.Whatever = window.Whatever

global.navigator = {
	userAgent: 'node.js'
};
