var smf_formSubmitted = false;
var lastKeepAliveCheck = new Date().getTime();
var smf_editorArray = new Array();

// Some very basic browser detection - from Mozilla's sniffer page.
var ua = navigator.userAgent.toLowerCase();

var is_opera = ua.indexOf("opera") != -1;
var is_opera5 = ua.indexOf("opera/5") != -1 || ua.indexOf("opera 5") != -1;
var is_opera6 = ua.indexOf("opera/6") != -1 || ua.indexOf("opera 6") != -1;
var is_opera7 = ua.indexOf("opera/7") != -1 || ua.indexOf("opera 7") != -1;
var is_opera8 = ua.indexOf("opera/8") != -1 || ua.indexOf("opera 8") != -1;
var is_opera9 = ua.indexOf("opera/9") != -1 || ua.indexOf("opera 9") != -1;
var is_opera95 = ua.indexOf("opera/9.5") != -1 || ua.indexOf("opera 9.5") != -1;
var is_opera95up = is_opera95;

var is_ff = ua.indexOf("firefox") != -1 && !is_opera;
var is_gecko = ua.indexOf('gecko') != -1 && !is_opera;

var is_safari13beta = ua.indexOf('applewebkit') != -1;

var is_ie = ua.indexOf("msie") != -1  && !is_opera;
var is_ie4 = is_ie && ua.indexOf("msie 4") != -1;
var is_ie5 = is_ie && ua.indexOf("msie 5") != -1;
var is_ie50 = is_ie && ua.indexOf("msie 5.0") != -1;
var is_ie55 = is_ie && ua.indexOf("msie 5.5") != -1;
var is_ie5up = is_ie && !is_ie4;
var is_ie6 = is_ie && ua.indexOf("msie 6") != -1;
var is_ie6up = is_ie5up && !is_ie55 && !is_ie5;
var is_ie7 = is_ie && ua.indexOf("msie 7") != -1;
var is_ie7up = is_ie6up && !is_ie6;
var is_ie7down = is_ie7 || is_ie6 || is_ie5 || is_ie4;

var is_ie8 = is_ie && ua.indexOf("msie 8") != -1;
var is_ie8up = is_ie8 && !is_ie7down;

var is_phone = ua.indexOf("iphone") != -1 || ua.indexOf("ipod") != -1;

var ajax_indicator_ele = null;

// Define document.getElementById for Internet Explorer 4.
if (typeof(document.getElementById) == "undefined")
	document.getElementById = function (id)
	{
		// Just return the corresponding index of all.
		return document.all[id];
	}
// Define XMLHttpRequest for IE 5 and above. (don't bother for IE 4 :/.... works in Opera 7.6 and Safari 1.2!)
else if (!window.XMLHttpRequest && window.ActiveXObject)
	window.XMLHttpRequest = function ()
	{
		return new ActiveXObject(is_ie5 ? "Microsoft.XMLHTTP" : "MSXML2.XMLHTTP");
	};

// Ensure the getElementsByTagName exists.
if (typeof(document.getElementsByTagName) == "undefined")
	document.getElementsByTagName = function (name)
	{
		// Just return the tags with this name.
		return document.all.tags[name];
	}

// Some older versions of Mozilla don't have this, for some reason.
if (typeof(document.forms) == "undefined")
	document.forms = document.getElementsByTagName("form");

// Load an XML document using XMLHttpRequest.
function getXMLDocument(sUrl, funcCallback)
{
	if (!window.XMLHttpRequest)
		return null;

	var oMyDoc = new XMLHttpRequest();
	var bAsync = typeof(funcCallback) != 'undefined';
	var oCaller = this;
	if (bAsync)
	{
		oMyDoc.onreadystatechange = function ()
		{
			if (oMyDoc.readyState != 4)
				return;

			if (oMyDoc.responseXML != null && oMyDoc.status == 200)
			{
				if (funcCallback.call)
				{
					funcCallback.call(oCaller, oMyDoc.responseXML);
				}
				// A primitive substitute for the call method to support IE 5.0.
				else
				{
					oCaller.tmpMethod = funcCallback;
					oCaller.tmpMethod(oMyDoc.responseXML);
					delete oCaller.tmpMethod;
				}
			}
		};
	}
	oMyDoc.open('GET', sUrl, bAsync);
	oMyDoc.send(null);

	return oMyDoc;
}

// Send a post form to the server using XMLHttpRequest.
function sendXMLDocument(sUrl, sContent, funcCallback)
{
	if (!window.XMLHttpRequest)
		return false;

	var sendDoc = new window.XMLHttpRequest();
	var oCaller = this;
	if (typeof(funcCallback) != 'undefined')
	{
		sendDoc.onreadystatechange = function ()
		{
			if (sendDoc.readyState != 4)
				return;

			if (sendDoc.responseXML != null && sendDoc.status == 200)
				funcCallback.call(oCaller, sendDoc.responseXML);
			else
				funcCallback.call(oCaller, false);
		};
	}
	sendDoc.open('POST', sUrl, true);
	if (typeof(sendDoc.setRequestHeader) != 'undefined')
		sendDoc.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
	sendDoc.send(sContent);

	return true;
}

// Character-level replacement function.
String.prototype.php_strtr = function (sFrom, sTo) {
	return this.replace(new RegExp('[' + sFrom + ']', 'g'), function (sMatch) {
		return sTo.charAt(sFrom.indexOf(sMatch));
	});
}

// Simulate PHP's strtolower (in SOME cases PHP uses ISO-8859-1 case folding).
String.prototype.php_strtolower = function () {
	return typeof(smf_iso_case_folding) != "undefined" && smf_iso_case_folding == true ? this.php_strtr(
		'ABCDEFGHIJKLMNOPQRSTUVWXYZ\x8a\x8c\x8e\x9f\xc0\xc1\xc2\xc3\xc4\xc5\xc6\xc7\xc8\xc9\xca\xcb\xcc\xcd\xce\xcf\xd0\xd1\xd2\xd3\xd4\xd5\xd6\xd7\xd8\xd9\xda\xdb\xdc\xdd\xde',
		'abcdefghijklmnopqrstuvwxyz\x9a\x9c\x9e\xff\xe0\xe1\xe2\xe3\xe4\xe5\xe6\xe7\xe8\xe9\xea\xeb\xec\xed\xee\xef\xf0\xf1\xf2\xf3\xf4\xf5\xf6\xf7\xf8\xf9\xfa\xfb\xfc\xfd\xfe'
	) : this.php_strtr('ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz');
}

// Converts all characters beyond 8-bit to HTML entities.
String.prototype.smf_toEntities = function ()
{
		var sReturn = '';
		for (var i = 0, n = this.length; i < n; i++)
			sReturn += this.charCodeAt(i) > 255 ? '&#' +  this.charCodeAt(i) + ';' : this.charAt(i);

		return sReturn;
}

// Convert a string to an 8 bit representation (like in PHP).
String.prototype.php_to8bit = function () {
	if (smf_charset == 'UTF-8')
	{
		var n, sReturn = '';

		for (var i = 0, iTextLen = this.length; i < iTextLen; i++)
		{
			n = this.charCodeAt(i);
			if (n < 128)
				sReturn += String.fromCharCode(n)
			else if (n < 2048)
				sReturn += String.fromCharCode(192 | n >> 6) + String.fromCharCode(128 | n & 63);
			else if (n < 65536)
				sReturn += String.fromCharCode(224 | n >> 12) + String.fromCharCode(128 | n >> 6 & 63) + String.fromCharCode(128 | n & 63);
			else
				sReturn += String.fromCharCode(240 | n >> 18) + String.fromCharCode(128 | n >> 12 & 63) + String.fromCharCode(128 | n >> 6 & 63) + String.fromCharCode(128 | n & 63);
		}

		return sReturn;
	}

	else if (typeof(this.oCharsetConversion) == 'undefined')
	{
		switch (smf_charset)
		{
			case 'ISO-8859-1':
				this.oCharsetConversion = {
					from: '',
					to: ''
				};
			break;

			case 'ISO-8859-2':
				this.oCharsetConversion = {
					from: '\u0104\u02d8\u0141\u013d\u015a\u0160\u015e\u0164\u0179\u017d\u017b\u0105\u02db\u0142\u013e\u015b\u02c7\u0161\u015f\u0165\u017a\u02dd\u017e\u017c\u0154\u0102\u0139\u0106\u010c\u0118\u011a\u010e\u0110\u0143\u0147\u0150\u0158\u016e\u0170\u0162\u0155\u0103\u013a\u0107\u010d\u0119\u011b\u010f\u0111\u0144\u0148\u0151\u0159\u016f\u0171\u0163\u02d9',
					to: '\xa1\xa2\xa3\xa5\xa6\xa9\xaa\xab\xac\xae\xaf\xb1\xb2\xb3\xb5\xb6\xb7\xb9\xba\xbb\xbc\xbd\xbe\xbf\xc0\xc3\xc5\xc6\xc8\xca\xcc\xcf\xd0\xd1\xd2\xd5\xd8\xd9\xdb\xde\xe0\xe3\xe5\xe6\xe8\xea\xec\xef\xf0\xf1\xf2\xf5\xf8\xf9\xfb\xfe\xff'
				};
			break;

			case 'ISO-8859-5':
				this.oCharsetConversion = {
					from: '\u0401\u0402\u0403\u0404\u0405\u0406\u0407\u0408\u0409\u040a\u040b\u040c\u040e\u040f\u0410\u0411\u0412\u0413\u0414\u0415\u0416\u0417\u0418\u0419\u041a\u041b\u041c\u041d\u041e\u041f\u0420\u0421\u0422\u0423\u0424\u0425\u0426\u0427\u0428\u0429\u042a\u042b\u042c\u042d\u042e\u042f\u0430\u0431\u0432\u0433\u0434\u0435\u0436\u0437\u0438\u0439\u043a\u043b\u043c\u043d\u043e\u043f\u0440\u0441\u0442\u0443\u0444\u0445\u0446\u0447\u0448\u0449\u044a\u044b\u044c\u044d\u044e\u044f\u2116\u0451\u0452\u0453\u0454\u0455\u0456\u0457\u0458\u0459\u045a\u045b\u045c\u00a7\u045e\u045f',
					to: '\xa1\xa2\xa3\xa4\xa5\xa6\xa7\xa8\xa9\xaa\xab\xac\xae\xaf\xb0\xb1\xb2\xb3\xb4\xb5\xb6\xb7\xb8\xb9\xba\xbb\xbc\xbd\xbe\xbf\xc0\xc1\xc2\xc3\xc4\xc5\xc6\xc7\xc8\xc9\xca\xcb\xcc\xcd\xce\xcf\xd0\xd1\xd2\xd3\xd4\xd5\xd6\xd7\xd8\xd9\xda\xdb\xdc\xdd\xde\xdf\xe0\xe1\xe2\xe3\xe4\xe5\xe6\xe7\xe8\xe9\xea\xeb\xec\xed\xee\xef\xf0\xf1\xf2\xf3\xf4\xf5\xf6\xf7\xf8\xf9\xfa\xfb\xfc\xfd\xfe\xff'
				};
			break;

			case 'ISO-8859-9':
				this.oCharsetConversion = {
					from: '\u011e\u0130\u015e\u011f\u0131\u015f',
					to: '\xd0\xdd\xde\xf0\xfd\xfe'
				};
			break;

			case 'ISO-8859-15':
				this.oCharsetConversion = {
					from: '\u20ac\u0160\u0161\u017d\u017e\u0152\u0153\u0178',
					to: '\xa4\xa6\xa8\xb4\xb8\xbc\xbd\xbe'
				};
			break;

			case 'tis-620':
				this.oCharsetConversion = {
					from: '\u0e01\u0e02\u0e03\u0e04\u0e05\u0e06\u0e07\u0e08\u0e09\u0e0a\u0e0b\u0e0c\u0e0d\u0e0e\u0e0f\u0e10\u0e11\u0e12\u0e13\u0e14\u0e15\u0e16\u0e17\u0e18\u0e19\u0e1a\u0e1b\u0e1c\u0e1d\u0e1e\u0e1f\u0e20\u0e21\u0e22\u0e23\u0e24\u0e25\u0e26\u0e27\u0e28\u0e29\u0e2a\u0e2b\u0e2c\u0e2d\u0e2e\u0e2f\u0e30\u0e31\u0e32\u0e33\u0e34\u0e35\u0e36\u0e37\u0e38\u0e39\u0e3a\u0e3f\u0e40\u0e41\u0e42\u0e43\u0e44\u0e45\u0e46\u0e47\u0e48\u0e49\u0e4a\u0e4b\u0e4c\u0e4d\u0e4e\u0e4f\u0e50\u0e51\u0e52\u0e53\u0e54\u0e55\u0e56\u0e57\u0e58\u0e59\u0e5a\u0e5b',
					to: '\xa1\xa2\xa3\xa4\xa5\xa6\xa7\xa8\xa9\xaa\xab\xac\xad\xae\xaf\xb0\xb1\xb2\xb3\xb4\xb5\xb6\xb7\xb8\xb9\xba\xbb\xbc\xbd\xbe\xbf\xc0\xc1\xc2\xc3\xc4\xc5\xc6\xc7\xc8\xc9\xca\xcb\xcc\xcd\xce\xcf\xd0\xd1\xd2\xd3\xd4\xd5\xd6\xd7\xd8\xd9\xda\xdf\xe0\xe1\xe2\xe3\xe4\xe5\xe6\xe7\xe8\xe9\xea\xeb\xec\xed\xee\xef\xf0\xf1\xf2\xf3\xf4\xf5\xf6\xf7\xf8\xf9\xfa\xfb'
				};
			break;

			case 'windows-1251':
				this.oCharsetConversion = {
					from: '\u0402\u0403\u201a\u0453\u201e\u2026\u2020\u2021\u20ac\u2030\u0409\u2039\u040a\u040c\u040b\u040f\u0452\u2018\u2019\u201c\u201d\u2022\u2013\u2014\u2122\u0459\u203a\u045a\u045c\u045b\u045f\u040e\u045e\u0408\u0490\u0401\u0404\u0407\u0406\u0456\u0491\u0451\u2116\u0454\u0458\u0405\u0455\u0457\u0410\u0411\u0412\u0413\u0414\u0415\u0416\u0417\u0418\u0419\u041a\u041b\u041c\u041d\u041e\u041f\u0420\u0421\u0422\u0423\u0424\u0425\u0426\u0427\u0428\u0429\u042a\u042b\u042c\u042d\u042e\u042f\u0430\u0431\u0432\u0433\u0434\u0435\u0436\u0437\u0438\u0439\u043a\u043b\u043c\u043d\u043e\u043f\u0440\u0441\u0442\u0443\u0444\u0445\u0446\u0447\u0448\u0449\u044a\u044b\u044c\u044d\u044e\u044f',
					to: '\x80\x81\x82\x83\x84\x85\x86\x87\x88\x89\x8a\x8b\x8c\x8d\x8e\x8f\x90\x91\x92\x93\x94\x95\x96\x97\x99\x9a\x9b\x9c\x9d\x9e\x9f\xa1\xa2\xa3\xa5\xa8\xaa\xaf\xb2\xb3\xb4\xb8\xb9\xba\xbc\xbd\xbe\xbf\xc0\xc1\xc2\xc3\xc4\xc5\xc6\xc7\xc8\xc9\xca\xcb\xcc\xcd\xce\xcf\xd0\xd1\xd2\xd3\xd4\xd5\xd6\xd7\xd8\xd9\xda\xdb\xdc\xdd\xde\xdf\xe0\xe1\xe2\xe3\xe4\xe5\xe6\xe7\xe8\xe9\xea\xeb\xec\xed\xee\xef\xf0\xf1\xf2\xf3\xf4\xf5\xf6\xf7\xf8\xf9\xfa\xfb\xfc\xfd\xfe\xff'
				};
			break;

			case 'windows-1253':
				this.oCharsetConversion = {
					from: '\u20ac\u201a\u0192\u201e\u2026\u2020\u2021\u2030\u2039\u2018\u2019\u201c\u201d\u2022\u2013\u2014\u2122\u203a\u0385\u0386\u2015\u0384\u0388\u0389\u038a\u038c\u038e\u038f\u0390\u0391\u0392\u0393\u0394\u0395\u0396\u0397\u0398\u0399\u039a\u039b\u039c\u039d\u039e\u039f\u03a0\u03a1\u03a3\u03a4\u03a5\u03a6\u03a7\u03a8\u03a9\u03aa\u03ab\u03ac\u03ad\u03ae\u03af\u03b0\u03b1\u03b2\u03b3\u03b4\u03b5\u03b6\u03b7\u03b8\u03b9\u03ba\u03bb\u03bc\u03bd\u03be\u03bf\u03c0\u03c1\u03c2\u03c3\u03c4\u03c5\u03c6\u03c7\u03c8\u03c9\u03ca\u03cb\u03cc\u03cd\u03ce',
					to: '\x80\x82\x83\x84\x85\x86\x87\x89\x8b\x91\x92\x93\x94\x95\x96\x97\x99\x9b\xa1\xa2\xaf\xb4\xb8\xb9\xba\xbc\xbe\xbf\xc0\xc1\xc2\xc3\xc4\xc5\xc6\xc7\xc8\xc9\xca\xcb\xcc\xcd\xce\xcf\xd0\xd1\xd3\xd4\xd5\xd6\xd7\xd8\xd9\xda\xdb\xdc\xdd\xde\xdf\xe0\xe1\xe2\xe3\xe4\xe5\xe6\xe7\xe8\xe9\xea\xeb\xec\xed\xee\xef\xf0\xf1\xf2\xf3\xf4\xf5\xf6\xf7\xf8\xf9\xfa\xfb\xfc\xfd\xfe'
				};
			break;

			case 'windows-1255':
				this.oCharsetConversion = {
					from: '\u20ac\u201a\u0192\u201e\u2026\u2020\u2021\u02c6\u2030\u2039\u2018\u2019\u201c\u201d\u2022\u2013\u2014\u02dc\u2122\u203a\u20aa\u00d7\u00f7\u05b0\u05b1\u05b2\u05b3\u05b4\u05b5\u05b6\u05b7\u05b8\u05b9\u05bb\u05bc\u05bd\u05be\u05bf\u05c0\u05c1\u05c2\u05c3\u05f0\u05f1\u05f2\u05f3\u05f4\u05d0\u05d1\u05d2\u05d3\u05d4\u05d5\u05d6\u05d7\u05d8\u05d9\u05da\u05db\u05dc\u05dd\u05de\u05df\u05e0\u05e1\u05e2\u05e3\u05e4\u05e5\u05e6\u05e7\u05e8\u05e9\u05ea\u200e\u200f',
					to: '\x80\x82\x83\x84\x85\x86\x87\x88\x89\x8b\x91\x92\x93\x94\x95\x96\x97\x98\x99\x9b\xa4\xaa\xba\xc0\xc1\xc2\xc3\xc4\xc5\xc6\xc7\xc8\xc9\xcb\xcc\xcd\xce\xcf\xd0\xd1\xd2\xd3\xd4\xd5\xd6\xd7\xd8\xe0\xe1\xe2\xe3\xe4\xe5\xe6\xe7\xe8\xe9\xea\xeb\xec\xed\xee\xef\xf0\xf1\xf2\xf3\xf4\xf5\xf6\xf7\xf8\xf9\xfa\xfd\xfe'
				};
			break;

			case 'windows-1256':
				this.oCharsetConversion = {
					from: '\u20ac\u067e\u201a\u0192\u201e\u2026\u2020\u2021\u02c6\u2030\u0679\u2039\u0152\u0686\u0698\u0688\u06af\u2018\u2019\u201c\u201d\u2022\u2013\u2014\u06a9\u2122\u0691\u203a\u0153\u200c\u200d\u06ba\u060c\u06be\u061b\u061f\u06c1\u0621\u0622\u0623\u0624\u0625\u0626\u0627\u0628\u0629\u062a\u062b\u062c\u062d\u062e\u062f\u0630\u0631\u0632\u0633\u0634\u0635\u0636\u0637\u0638\u0639\u063a\u0640\u0641\u0642\u0643\u0644\u0645\u0646\u0647\u0648\u0649\u064a\u064b\u064c\u064d\u064e\u064f\u0650\u0651\u0652\u200e\u200f\u06d2',
					to: '\x80\x81\x82\x83\x84\x85\x86\x87\x88\x89\x8a\x8b\x8c\x8d\x8e\x8f\x90\x91\x92\x93\x94\x95\x96\x97\x98\x99\x9a\x9b\x9c\x9d\x9e\x9f\xa1\xaa\xba\xbf\xc0\xc1\xc2\xc3\xc4\xc5\xc6\xc7\xc8\xc9\xca\xcb\xcc\xcd\xce\xcf\xd0\xd1\xd2\xd3\xd4\xd5\xd6\xd8\xd9\xda\xdb\xdc\xdd\xde\xdf\xe1\xe3\xe4\xe5\xe6\xec\xed\xf0\xf1\xf2\xf3\xf5\xf6\xf8\xfa\xfd\xfe\xff'
				};
			break;

			default:
				this.oCharsetConversion = {
					from: '',
					to: ''
				};
			break;
		}
	}

	var sReturn = '', iOffset = 0;
	for (var i = 0, n = this.length; i < n; i++)
	{
		iOffset = this.oCharsetConversion.from.indexOf(this.charAt(i));
		sReturn += iOffset > -1 ? this.oCharsetConversion.to.charAt(iOffset) : '&#' +  this.charCodeAt(i) + ';';
	}

	return sReturn
}

String.prototype.php_urlencode = function()
{
	return escape(this).replace(/\+/g, '%2b').replace('*', '%2a').replace('/', '%2f').replace('@', '%40');
}


// Open a new window.
function reqWin(desktopURL, alternateWidth, alternateHeight, noScrollbars)
{
	if ((alternateWidth && self.screen.availWidth * 0.8 < alternateWidth) || (alternateHeight && self.screen.availHeight * 0.8 < alternateHeight))
	{
		noScrollbars = false;
		alternateWidth = Math.min(alternateWidth, self.screen.availWidth * 0.8);
		alternateHeight = Math.min(alternateHeight, self.screen.availHeight * 0.8);
	}
	else
		noScrollbars = typeof(noScrollbars) != "undefined" && noScrollbars == true;

	window.open(desktopURL, 'requested_popup', 'toolbar=no,location=no,status=no,menubar=no,scrollbars=' + (noScrollbars ? 'no' : 'yes') + ',width=' + (alternateWidth ? alternateWidth : 480) + ',height=' + (alternateHeight ? alternateHeight : 220) + ',resizable=no');

	// Return false so the click won't follow the link ;).
	return false;
}

// Remember the current position.
function storeCaret(text)
{
	// Only bother if it will be useful.
	if (typeof(text.createTextRange) != "undefined")
		text.caretPos = document.selection.createRange().duplicate();
}

// Replaces the currently selected text with the passed text.
function replaceText(text, textarea)
{
	// Attempt to create a text range (IE).
	if (typeof(textarea.caretPos) != "undefined" && textarea.createTextRange)
	{
		var caretPos = textarea.caretPos;

		caretPos.text = caretPos.text.charAt(caretPos.text.length - 1) == ' ' ? text + ' ' : text;
		caretPos.select();
	}
	// Mozilla text range replace.
	else if (typeof(textarea.selectionStart) != "undefined")
	{
		var begin = textarea.value.substr(0, textarea.selectionStart);
		var end = textarea.value.substr(textarea.selectionEnd);
		var scrollPos = textarea.scrollTop;

		textarea.value = begin + text + end;

		if (textarea.setSelectionRange)
		{
			textarea.focus();
			textarea.setSelectionRange(begin.length + text.length, begin.length + text.length);
		}
		textarea.scrollTop = scrollPos;
	}
	// Just put it on the end.
	else
	{
		textarea.value += text;
		textarea.focus(textarea.value.length - 1);
	}
}

// Surrounds the selected text with text1 and text2.
function surroundText(text1, text2, textarea)
{
	// Can a text range be created?
	if (typeof(textarea.caretPos) != "undefined" && textarea.createTextRange)
	{
		var caretPos = textarea.caretPos, temp_length = caretPos.text.length;

		caretPos.text = caretPos.text.charAt(caretPos.text.length - 1) == ' ' ? text1 + caretPos.text + text2 + ' ' : text1 + caretPos.text + text2;

		if (temp_length == 0)
		{
			caretPos.moveStart("character", -text2.length);
			caretPos.moveEnd("character", -text2.length);
			caretPos.select();
		}
		else
			textarea.focus(caretPos);
	}
	// Mozilla text range wrap.
	else if (typeof(textarea.selectionStart) != "undefined")
	{
		var begin = textarea.value.substr(0, textarea.selectionStart);
		var selection = textarea.value.substr(textarea.selectionStart, textarea.selectionEnd - textarea.selectionStart);
		var end = textarea.value.substr(textarea.selectionEnd);
		var newCursorPos = textarea.selectionStart;
		var scrollPos = textarea.scrollTop;

		textarea.value = begin + text1 + selection + text2 + end;

		if (textarea.setSelectionRange)
		{
			if (selection.length == 0)
				textarea.setSelectionRange(newCursorPos + text1.length, newCursorPos + text1.length);
			else
				textarea.setSelectionRange(newCursorPos, newCursorPos + text1.length + selection.length + text2.length);
			textarea.focus();
		}
		textarea.scrollTop = scrollPos;
	}
	// Just put them on the end, then.
	else
	{
		textarea.value += text1 + text2;
		textarea.focus(textarea.value.length - 1);
	}
}

// Checks if the passed input's value is nothing.
function isEmptyText(theField)
{
	// Copy the value so changes can be made..
	var theValue = theField.value;

	// Strip whitespace off the left side.
	while (theValue.length > 0 && (theValue.charAt(0) == ' ' || theValue.charAt(0) == '\t'))
		theValue = theValue.substring(1, theValue.length);
	// Strip whitespace off the right side.
	while (theValue.length > 0 && (theValue.charAt(theValue.length - 1) == ' ' || theValue.charAt(theValue.length - 1) == '\t'))
		theValue = theValue.substring(0, theValue.length - 1);

	if (theValue == '')
		return true;
	else
		return false;
}

// Only allow form submission ONCE.
function submitonce(theform)
{
	smf_formSubmitted = true;

	// If there are any editors warn them submit is coming!
	for (var i = 0; i < smf_editorArray.length; i++)
		smf_editorArray[i].doSubmit();
}
function submitThisOnce(form)
{
	// Hateful, hateful fix for Safari 1.3 beta.
	if (is_safari13beta)
		return !smf_formSubmitted;

	if (typeof(form.form) != "undefined")
		form = form.form;

	for (var i = 0; i < form.length; i++)
		if (typeof(form[i]) != "undefined" && form[i].tagName.toLowerCase() == "textarea")
			form[i].readOnly = true;

	return !smf_formSubmitted;
}

// Set the "inside" HTML of an element.
function setInnerHTML(element, toValue)
{
	// IE has this built in...
	if (typeof(element.innerHTML) != 'undefined')
		element.innerHTML = toValue;
	// Otherwise, try createContextualFragment().
	else
	{
		var range = document.createRange();
		range.selectNodeContents(element);
		range.deleteContents();
		element.appendChild(range.createContextualFragment(toValue));
	}
}

// Set the "outer" HTML of an element.
function setOuterHTML(element, toValue)
{
	if (typeof(element.outerHTML) != 'undefined')
		element.outerHTML = toValue;
	else
	{
		var range = document.createRange();
		range.setStartBefore(element);
		element.parentNode.replaceChild(range.createContextualFragment(toValue), element);
	}
}

// Get the inner HTML of an element.
function getInnerHTML(element)
{
	if (typeof(element.innerHTML) != 'undefined')
		return element.innerHTML;
	else
	{
		var returnStr = '';
		for (var i = 0; i < element.childNodes.length; i++)
			returnStr += getOuterHTML(element.childNodes[i]);

		return returnStr;
	}
}

function getOuterHTML(node)
{
	if (typeof(node.outerHTML) != 'undefined')
		return node.outerHTML;

	var str = '';

	switch (node.nodeType)
	{
	// An element.
	case 1:
		str += '<' + node.nodeName;

		for (var i = 0; i < node.attributes.length; i++)
		{
			if (node.attributes[i].nodeValue != null)
				str += ' ' + node.attributes[i].nodeName + '="' + node.attributes[i].nodeValue + '"';
		}

		if (node.childNodes.length == 0 && in_array(node.nodeName.toLowerCase(), ['hr', 'input', 'img', 'link', 'meta', 'br']))
			str += ' />';
		else
			str += '>' + getInnerHTML(node) + '</' + node.nodeName + '>';
		break;

	// 2 is an attribute.

	// Just some text..
	case 3:
		str += node.nodeValue;
		break;

	// A CDATA section.
	case 4:
		str += '<![CDATA' + '[' + node.nodeValue + ']' + ']>';
		break;

	// Entity reference..
	case 5:
		str += '&' + node.nodeName + ';';
		break;

	// 6 is an actual entity, 7 is a PI.

	// Comment.
	case 8:
		str += '<!--' + node.nodeValue + '-->';
		break;
	}

	return str;
}

// Checks for variable in theArray.
function in_array(variable, theArray)
{
	for (var i in theArray)
		if (theArray[i] == variable)
			return true;

	return false;
}

// Checks for variable in theArray.
function array_search(variable, theArray)
{
	for (var i in theArray)
		if (theArray[i] == variable)
			return i;

	return null;
}

// Find a specific radio button in its group and select it.
function selectRadioByName(radioGroup, name)
{
	if (typeof(radioGroup.length) == "undefined")
		return radioGroup.checked = true;

	for (var i = 0; i < radioGroup.length; i++)
		if (radioGroup[i].value == name)
			return radioGroup[i].checked = true;

	return false;
}

// Invert all checkboxes at once by clicking a single checkbox.
function invertAll(headerfield, checkform, mask, ignore_disabled)
{
	for (var i = 0; i < checkform.length; i++)
	{
		if (typeof(checkform[i].name) == "undefined" || (typeof(mask) != "undefined" && checkform[i].name.substr(0, mask.length) != mask && checkform[i].id.substr(0, mask.length) != mask))
			continue;

		if (!checkform[i].disabled || typeof(ignore_disabled) != "undefined")
			checkform[i].checked = headerfield.checked;
	}
}

// Keep the session alive - always!
var lastKeepAliveCheck = new Date().getTime();
function smf_sessionKeepAlive()
{
	var curTime = new Date().getTime();

	// Prevent a Firefox bug from hammering the server.
	if (smf_scripturl && curTime - lastKeepAliveCheck > 900000)
	{
		var tempImage = new Image();
		tempImage.src = smf_scripturl + (smf_scripturl.indexOf('?') == -1 ? '?' : '&') + 'action=keepalive;' + curTime;
		lastKeepAliveCheck = curTime;
	}

	window.setTimeout("smf_sessionKeepAlive();", 1200000);
}
window.setTimeout("smf_sessionKeepAlive();", 1200000);

// Set a theme option through javascript.
function smf_setThemeOption(option, value, theme, cur_session_id, additional_vars)
{
	// Compatibility.
	if (cur_session_id == null)
		cur_session_id = smf_session_id;

	if (additional_vars == null)
		additional_vars = '';

	var tempImage = new Image();
	tempImage.src = smf_scripturl + (smf_scripturl.indexOf("?") == -1 ? "?" : "&") + "action=jsoption;var=" + option + ";val=" + value + ";sesc=" + cur_session_id + additional_vars + (theme == null ? "" : "&id=" + theme) + ";" + (new Date().getTime());
}

function smf_avatarResize()
{
	var possibleAvatars = document.getElementsByTagName("img");

	for (var i = 0; i < possibleAvatars.length; i++)
	{
		if (possibleAvatars[i].className != "avatar")
			continue;

		var tempAvatar = new Image();
		tempAvatar.src = possibleAvatars[i].src;

		if (smf_avatarMaxWidth != 0 && tempAvatar.width > smf_avatarMaxWidth)
		{
			possibleAvatars[i].height = (smf_avatarMaxWidth * tempAvatar.height) / tempAvatar.width;
			possibleAvatars[i].width = smf_avatarMaxWidth;
		}
		else if (smf_avatarMaxHeight != 0 && tempAvatar.height > smf_avatarMaxHeight)
		{
			possibleAvatars[i].width = (smf_avatarMaxHeight * tempAvatar.width) / tempAvatar.height;
			possibleAvatars[i].height = smf_avatarMaxHeight;
		}
		else
		{
			possibleAvatars[i].width = tempAvatar.width;
			possibleAvatars[i].height = tempAvatar.height;
		}
	}

	if (typeof(window_oldAvatarOnload) != "undefined" && window_oldAvatarOnload)
	{
		window_oldAvatarOnload();
		window_oldAvatarOnload = null;
	}
}

function hashLoginPassword(doForm, cur_session_id)
{
	// Compatibility.
	if (cur_session_id == null)
		cur_session_id = smf_session_id;

	if (typeof(hex_sha1) == "undefined")
		return;
	// Are they using an email address?
	if (doForm.user.value.indexOf("@") != -1)
		return;

	// Unless the browser is Opera, the password will not save properly.
	if (typeof(window.opera) == "undefined")
		doForm.passwrd.autocomplete = "off";

	doForm.hash_passwrd.value = hex_sha1(hex_sha1(doForm.user.value.php_to8bit().php_strtolower() + doForm.passwrd.value.php_to8bit()) + cur_session_id);

	// It looks nicer to fill it with asterisks, but Firefox will try to save that.
	if (is_ff != -1)
		doForm.passwrd.value = "";
	else
		doForm.passwrd.value = doForm.passwrd.value.replace(/./g, "*");
}

function hashAdminPassword(doForm, username, cur_session_id)
{
	// Compatibility.
	if (cur_session_id == null)
		cur_session_id = smf_session_id;

	if (typeof(hex_sha1) == "undefined")
		return;

	doForm.admin_hash_pass.value = hex_sha1(hex_sha1(username.toLowerCase() + doForm.admin_pass.value) + cur_session_id);
	doForm.admin_pass.value = doForm.admin_pass.value.replace(/./g, "*");
}

// Shows the page numbers by clicking the dots (in compact view).
function expandPages(spanNode, baseURL, firstPage, lastPage, perPage)
{
	var replacement = '', i, oldLastPage = 0;

	// The dots were bold, the page numbers are not (in most cases).
	spanNode.style.fontWeight = 'normal';
	spanNode.onclick = '';

	// Prevent too many pages to be loaded at once.
	if ((lastPage - firstPage) / perPage > 100)
	{
		oldLastPage = lastPage;
		lastPage = firstPage + 100 * perPage;
	}

	// Calculate the new pages.
	for (i = firstPage; i < lastPage; i += perPage)
		replacement += '<a class="navPages" href="' + baseURL.replace(/%d/, i) + '">' + (1 + i / perPage) + '</a> ';

	if (oldLastPage > 0)
		replacement += '<span style="font-weight: bold; cursor: ' + (is_ie && !is_ie6up ? 'hand' : 'pointer') + ';" onclick="expandPages(this, \'' + baseURL + '\', ' + lastPage + ', ' + oldLastPage + ', ' + perPage + ');"> ... </span> ';

	// Replace the dots by the new page links.
	setInnerHTML(spanNode, replacement);
}

// An show/hide object - like a header.
function smfToggle(uniqueId, initialState)
{
	this.uid = uniqueId;
	this.state = initialState;
	this.use_cookie = 0;
	// Needed for setting theme options - kept hidden!
	var themeOptions = Array(5);
	themeOptions[0] = null;
	this.useCookie = useCookie;
	this.toggle = toggleHeader;
	this.setOptions = setOptions;
	this.imageToggles = new Array();
	this.panelToggles = new Array();
	this.addToggleImage = addToggleImage;
	this.addTogglePanel = addTogglePanel;

	// Should the shrinker use a cookie?
	function useCookie(mode)
	{
		this.use_cookie = mode ? 1 : 0;
	}

	// Actually shrink the header!
	function toggleHeader(mode)
	{
		// Just a toggle?
		if (mode == null)
			mode = !this.state;

		// Do we need to set a cookie?
		if (this.use_cookie)
			document.cookie = this.uid + '=' + (mode ? 1 : 0);

		// Set a theme option?
		if (themeOptions[0] != null)
		{
			var curMode = themeOptions[2] ? !mode : mode;
			smf_setThemeOption(themeOptions[0], curMode ? 1 : 0, themeOptions[3] == 0 ? null : themeOptions[3], themeOptions[1], themeOptions[4]);
		}

		// Toggle the images.
		var x = 0;
		for (x = 0; x < this.imageToggles.length; x++)
		{
			var curImage = document.getElementById(this.imageToggles[x][0]);
			if (curImage)
				curImage.src = mode ? this.imageToggles[x][2] : this.imageToggles[x][1];
		}

		// Now toggle the panels.
		for (x = 0; x < this.panelToggles.length; x++)
		{
			// Inverse?
			var curMode = this.panelToggles[x][1] ? !mode : mode;
			var curPanel = document.getElementById(this.panelToggles[x][0]);
			if (curPanel)
				curPanel.style.display = curMode ? "none" : "";
		}

		this.state = mode;
	}

	// Set the theme option that should change with this.
	function setOptions(newThemeOptions, sessID, flip, themeID, preferenceKey)
	{
		themeOptions[0] = newThemeOptions;
		themeOptions[1] = sessID;
		themeOptions[2] = flip == null ? 0 : 1;
		themeOptions[3] = themeID == null ? 0 : themeID;
		themeOptions[4] = preferenceKey == null ? '' : ';admin_key=' + preferenceKey;
	}

	// Add an image to toggle (id, mode = 0 image, mode = 1 image)
	function addToggleImage(imageID, mode0Image, mode1Image, useImagePath)
	{
		var curIndex = this.imageToggles.length;
		this.imageToggles[curIndex] = Array(3);
		this.imageToggles[curIndex][0] = imageID;
		this.imageToggles[curIndex][1] = (useImagePath == null ? smf_images_url : '') + mode0Image;
		this.imageToggles[curIndex][2] = (useImagePath == null ? smf_images_url : '') + mode1Image;
	}

	// Add a panel which should toggle with the header.
	function addTogglePanel(panelID, flip)
	{
		var curIndex = this.panelToggles.length;
		this.panelToggles[curIndex] = Array(2);
		this.panelToggles[curIndex][0] = panelID;
		this.panelToggles[curIndex][1] = flip == null ? 0 : 1;
	}
}

function ajax_indicator(turn_on)
{
	if (ajax_indicator_ele == null)
	{
		ajax_indicator_ele = document.getElementById("ajax_in_progress");

		if (ajax_indicator_ele == null && typeof(ajax_notification_text) != null)
		{
			create_ajax_indicator_ele();
		}
	}

	if (ajax_indicator_ele != null)
	{
		if (navigator.appName == "Microsoft Internet Explorer" && !is_ie7up)
		{
			ajax_indicator_ele.style.position = "absolute";
			ajax_indicator_ele.style.top = document.documentElement.scrollTop;
		}

		ajax_indicator_ele.style.display = turn_on ? "block" : "none";
	}
}

function create_ajax_indicator_ele()
{
	// Create the div for the indicator.
	ajax_indicator_ele = document.createElement("div");

	// Set the id so it'll load the style properly.
	ajax_indicator_ele.id = "ajax_in_progress";

	// Add the image in and link to turn it off.
	var cancel_link = document.createElement("a");
	cancel_link.href = "javascript:ajax_indicator(false)";
	var cancel_img = document.createElement("img");
	cancel_img.src = smf_images_url + "/icons/quick_remove.gif";

	if (typeof(ajax_notification_cancel_text) != null)
	{
		cancel_img.alt = ajax_notification_cancel_text;
		cancel_img.title = ajax_notification_cancel_text;
	}

	// Add the cancel link and image to the indicator.
	cancel_link.appendChild(cancel_img);
	ajax_indicator_ele.appendChild(cancel_link);

	// Set the text.  (Note:  You MUST append here and not overwrite.)
	ajax_indicator_ele.innerHTML += ajax_notification_text;

	// Finally attach the element to the body.
	document.body.appendChild(ajax_indicator_ele);

}

// Mimics the PHP version of this function.
function smf_htmlspecialchars(text)
{
	text = text.replace(/&/g, '&amp;');
	text = text.replace(/</g, '&lt;');
	text = text.replace(/>/g, '&gt;');
	text = text.replace(/"/g, '&quot;');

	return text;
}

// Mimics the PHP version of this function - like the above.
function smf_unhtmlspecialchars(text)
{
	text = text.replace(/&amp;/g, '&');
	text = text.replace(/&lt;/g, '<');
	text = text.replace(/&gt;/g, '>');
	text = text.replace(/&quot;/g, '"');

	return text;
}

// This will escape single quotes, kinda like PHP.
function smf_addslashes(text)
{
	text = text.replace(/'/g, '\\\'');

	return text;
}

function createEventListener(oTarget)
{
	if (typeof(oTarget.addEventListener) == 'undefined')
	{
		if (oTarget.attachEvent)
		{
			oTarget.addEventListener = function (sEvent, funcHandler, bCapture)
			{
				oTarget.attachEvent('on' + sEvent, funcHandler);
			}
			oTarget.removeEventListener = function (sEvent, funcHandler, bCapture)
			{
				oTarget.detachEvent('on' + sEvent, funcHandler);
			}
		}
		else
		{
			oTarget.addEventListener = function (sEvent, funcHandler, bCapture)
			{
				oTarget['on' + sEvent] = funcHandler;
			}
			oTarget.removeEventListener = function (sEvent, funcHandler, bCapture)
			{
				oTarget['on' + sEvent] = null;
			}
		}
	}
}

function findCoords(oElement)
{
	if (typeof(oElement.tagName) == 'undefined')
		return {'iX': 0, 'iY': 0};

	var bSkipMe = in_array(oElement.tagName.toLowerCase(), ['tr', 'form', 'tbody', 'div']);

	if (oElement.parentNode == null || typeof(oElement.parentNode.offsetLeft) == 'undefined')
		return {'iX': bSkipMe ? 0 : oElement.offsetLeft, 'iY': bSkipMe ? 0 : oElement.offsetTop};
	else
	{
		var aAdd = findCoords(oElement.parentNode);
		return {'iX': (bSkipMe ? 0 : oElement.offsetLeft) + aAdd.iX, 'iY': (bSkipMe ? 0 : oElement.offsetTop) + aAdd.iY};
	}
}

// This function will retrieve the contents needed for the jump to boxes.
function grabJumpToContent()
{
	var oXMLDoc = getXMLDocument(smf_prepareScriptUrl(smf_scripturl) + "action=xmlhttp;sa=jumpto;xml");
	var aBoardsAndCategories = new Array();

	ajax_indicator(true);

	if (oXMLDoc.responseXML)
	{
		var items = oXMLDoc.responseXML.getElementsByTagName('smf')[0].getElementsByTagName('item');
		for (var i = 0, n = items.length; i < n; i++)
		{
			aBoardsAndCategories[aBoardsAndCategories.length] = {
				id: parseInt(items[i].getAttribute('id')),
				isCategory: items[i].getAttribute('type') == 'category',
				name: items[i].firstChild.nodeValue,
				is_current: false,
				childLevel: parseInt(items[i].getAttribute('childlevel'))
			}
		}
	}

	ajax_indicator(false);

	for (var i = 0, n = aJumpTo.length; i < n; i++)
		aJumpTo[i].fillSelect(aBoardsAndCategories);
}

// This'll contain all JumpTo objects on the page.
var aJumpTo = new Array();

// *** JumpTo class.
function JumpTo(oJumpToOptions)
{
	this.opt = oJumpToOptions;
	this.dropdownList = null;
	this.showSelect();
}

// Show the initial select box (onload). Method of the JumpTo class.
JumpTo.prototype.showSelect = function ()
{
	var sChildLevelPrefix = '';
	for (var i = this.opt.iCurBoardChildLevel; i > 0; i--)
		sChildLevelPrefix += this.opt.sBoardChildLevelIndicator;
	setInnerHTML(document.getElementById(this.opt.sContainerId), this.opt.sJumpToTemplate.replace(/%select_id%/, this.opt.sContainerId + '_select').replace(/%dropdown_list%/, '<select name="' + this.opt.sContainerId + '_select" id="' + this.opt.sContainerId + '_select" ' + (typeof(document.implementation) == 'undefined' ? 'onmouseover="grabJumpToContent();" ' : '') + (typeof(document.onbeforeactivate) == 'undefined' ? 'onfocus' : 'onbeforeactivate') + '="grabJumpToContent();"><option value="?board=' + this.opt.iCurBoardId + '.0">' + sChildLevelPrefix + this.opt.sBoardPrefix + this.opt.sCurBoardName + '</option></select>&nbsp;<input type="button" value="' + this.opt.sGoButtonLabel + '" onclick="window.location.href = \'' + smf_scripturl + '?board=' + this.opt.iCurBoardId + '.0\';" />'));
	this.dropdownList = document.getElementById(this.opt.sContainerId + '_select');
}

// Fill the jump to box with entries. Method of the JumpTo class.
JumpTo.prototype.fillSelect = function (aBoardsAndCategories)
{
	var bIE5x = typeof(document.implementation) == 'undefined';
	var iIndexPointer = 0;

	// Create an option that'll be above and below the category.
	var oDashOption = document.createElement('option');
	oDashOption.appendChild(document.createTextNode(this.opt.sCatSeparator));
	oDashOption.disabled = 'disabled';
	oDashOption.value = '';

	// Reset the events and clear the list (IE5.x only).
	if (bIE5x)
	{
		this.dropdownList.onmouseover = null;
		this.dropdownList.remove(0);
	}
	if (typeof(document.onbeforeactivate) == 'undefined')
		this.dropdownList.onfocus = null;
	else
		this.dropdownList.onbeforeactivate = null;

	// Create a document fragment that'll allowing inserting big parts at once.
	var oListFragment = bIE5x ? this.dropdownList : document.createDocumentFragment();

	// Loop through all items to be added.
	for (var i = 0, n = aBoardsAndCategories.length; i < n; i++)
	{
		var j, sChildLevelPrefix, oOption;

		// If we've reached the currently selected board add all items so far.
		if (aBoardsAndCategories[i].id == this.opt.iCurBoardId && !bIE5x)
		{
			this.dropdownList.insertBefore(oListFragment, this.dropdownList.options[0]);
			oListFragment = document.createDocumentFragment();
			continue;
		}
		else if (aBoardsAndCategories[i].id == this.opt.iCurBoardId && bIE5x)
			iIndexPointer = this.dropdownList.options.length;


		if (aBoardsAndCategories[i].isCategory)
			oListFragment.appendChild(oDashOption.cloneNode(true));
		else
			for (j = aBoardsAndCategories[i].childLevel, sChildLevelPrefix = ''; j > 0; j--)
				sChildLevelPrefix += this.opt.sBoardChildLevelIndicator;

		oOption = document.createElement('option');
		oOption.appendChild(document.createTextNode((aBoardsAndCategories[i].isCategory ? this.opt.sCatPrefix : sChildLevelPrefix + this.opt.sBoardPrefix) + aBoardsAndCategories[i].name));
		oOption.value = aBoardsAndCategories[i].isCategory ? '#' + aBoardsAndCategories[i].id : '?board=' + aBoardsAndCategories[i].id + '.0';
		oListFragment.appendChild(oOption);

		if (aBoardsAndCategories[i].isCategory)
			oListFragment.appendChild(oDashOption.cloneNode(true));
	}

	// Add the remaining items after the currently selected item.
	this.dropdownList.appendChild(oListFragment);

	if (bIE5x)
		this.dropdownList.options[iIndexPointer].selected = true;

	// Internet Explorer needs this to keep the box dropped down.
	this.dropdownList.style.width = 'auto';
	this.dropdownList.focus();

	// Add an onchange action
	this.dropdownList.onchange = function()
	{
		if (this.selectedIndex > 0 && this.options[this.selectedIndex].value)
			window.location.href = smf_scripturl + this.options[this.selectedIndex].value.substr(smf_scripturl.indexOf('?') == -1 || this.options[this.selectedIndex].value.substr(0, 1) != '?' ? 0 : 1);
	}
}

// A global array containing all IconList objects.
var aIconLists = new Array();

// *** IconList object.
function IconList(oOptions)
{
	if (!window.XMLHttpRequest)
		return;

	this.opt = oOptions;
	this.bListLoaded = false;
	this.oContainerDiv = null;
	this.funcMousedownHandler = null;
	this.funcParent = this;
	this.iCurMessageId = 0;
	this.iCurTimeout = 0;

	this.initIcons();
}

// Replace all message icons by icons with hoverable and clickable div's.
IconList.prototype.initIcons = function ()
{
	for (var i = document.images.length - 1, iPrefixLength = this.opt.sIconIdPrefix.length; i >= 0; i--)
		if (document.images[i].id.substr(0, iPrefixLength) == this.opt.sIconIdPrefix)
			setOuterHTML(document.images[i], '<div title="' + this.opt.sLabelIconList + '" onclick="' + this.opt.sBackReference + '.openPopup(this, ' + document.images[i].id.substr(iPrefixLength) + ')" onmouseover="' + this.opt.sBackReference + '.onBoxHover(this, true)" onmouseout="' + this.opt.sBackReference + '.onBoxHover(this, false)" style="background: ' + this.opt.sBoxBackground + '; cursor: ' + (is_ie && !is_ie6up ? 'hand' : 'pointer') + '; padding: 3px; text-align: center;"><img src="' + document.images[i].src + '" alt="' + document.images[i].alt + '" style="margin: 0px; padding: ' + (is_ie ? '3px' : '3px 0px 3px 0px') + ';" /></div>');
}

// Event for the mouse hovering over the original icon.
IconList.prototype.onBoxHover = function (oDiv, bMouseOver)
{
	oDiv.style.border = bMouseOver ? this.opt.iBoxBorderWidthHover + 'px solid ' + this.opt.sBoxBorderColorHover : '';
	oDiv.style.background = bMouseOver ? this.opt.sBoxBackgroundHover : this.opt.sBoxBackground;
	oDiv.style.padding = bMouseOver ? (3 - this.opt.iBoxBorderWidthHover) + 'px' : '3px'
}

// Show the list of icons after the user clicked the original icon.
IconList.prototype.openPopup = function (oDiv, iMessageId)
{
	if (!this.bListLoaded && this.oContainerDiv == null)
	{
		// Create a container div.
		this.oContainerDiv = document.createElement('div');
		this.oContainerDiv.id = 'iconList';
		this.oContainerDiv.style.display = 'none';
		this.oContainerDiv.style.cursor = is_ie && !is_ie6up ? 'hand' : 'pointer';
		this.oContainerDiv.style.position = 'absolute';
		this.oContainerDiv.style.width = oDiv.clientWidth + 'px';
		this.oContainerDiv.style.background = this.opt.sContainerBackground;
		this.oContainerDiv.style.border = this.opt.sContainerBorder;
		this.oContainerDiv.style.padding = '1px';
		this.oContainerDiv.style.textAlign = 'center';
		document.body.appendChild(this.oContainerDiv);

		// Start to fetch its contents.
		ajax_indicator(true);
		this.tmpMethod = getXMLDocument;
		this.tmpMethod(smf_prepareScriptUrl(this.opt.sScriptUrl) + 'action=xmlhttp;sa=messageicons;board=' + this.opt.iBoardId + ';xml', this.onIconsReceived);
		delete this.tmpMethod;

		createEventListener(document.body);
	}

	this.iCurMessageId = iMessageId;

	// Set the position of the container.
	var aPos = smf_itemPos(oDiv);
	if (is_ie50)
		aPos[1] += 4;

	this.oContainerDiv.style.top = (aPos[1] + oDiv.offsetHeight) + 'px';
	this.oContainerDiv.style.left = (aPos[0] - 1) + 'px';
	this.oClickedIcon = oDiv;

	if (this.bListLoaded)
		this.oContainerDiv.style.display = 'block';

	document.body.addEventListener('mousedown', this.onWindowMouseDown, false);
}

// Setup the list of icons once it is received through xmlHTTP.
IconList.prototype.onIconsReceived = function (oXMLDoc)
{
	var icons = oXMLDoc.getElementsByTagName('smf')[0].getElementsByTagName('icon');
	var sItems = '';

	for (var i = 0, n = icons.length; i < n; i++)
		sItems += '<div onmouseover="' + this.opt.sBackReference + '.onItemHover(this, true)" onmouseout="' + this.opt.sBackReference + '.onItemHover(this, false);" onmousedown="' + this.opt.sBackReference + '.onItemMouseDown(this, \'' + icons[i].getAttribute('value') + '\');" style="padding: 3px 0px 3px 0px; margin-left: auto; margin-right: auto; border: ' + this.opt.sItemBorder + '; background: ' + this.opt.sItemBackground + '"><img src="' + icons[i].getAttribute('url') + '" alt="' + icons[i].getAttribute('name') + '" title="' + icons[i].firstChild.nodeValue + '" border="0" /></div>';

	setInnerHTML(this.oContainerDiv, sItems);
	this.oContainerDiv.style.display = 'block';
	this.bListLoaded = true;

	if (is_ie)
		this.oContainerDiv.style.width = this.oContainerDiv.clientWidth + 'px';

	ajax_indicator(false);
}

// Event handler for hovering over the icons.
IconList.prototype.onItemHover = function (oDiv, bMouseOver)
{
	oDiv.style.background = bMouseOver ? this.opt.sItemBackgroundHover : this.opt.sItemBackground;
	oDiv.style.border = bMouseOver ? this.opt.sItemBorderHover : this.opt.sItemBorder;
	if (this.iCurTimeout != 0)
		window.clearTimeout(this.iCurTimeout);
	if (bMouseOver)
		this.onBoxHover(this.oClickedIcon, true);
	else
		this.iCurTimeout = window.setTimeout(this.opt.sBackReference + '.collapseList();', 500);
}

// Event handler for clicking on one of the icons.
IconList.prototype.onItemMouseDown = function (oDiv, sNewIcon)
{
	if (this.iCurMessageId != 0)
	{
		ajax_indicator(true);
		this.tmpMethod = getXMLDocument;
		var oXMLDoc = this.tmpMethod(smf_prepareScriptUrl(this.opt.sScriptUrl) + 'action=jsmodify;topic=' + this.opt.iTopicId + ';msg=' + this.iCurMessageId + ';sesc=' + this.opt.sSessionId + ';icon=' + sNewIcon + ';xml');
		delete this.tmpMethod;
		ajax_indicator(false);

		var oMessage = oXMLDoc.responseXML.getElementsByTagName('smf')[0].getElementsByTagName('message')[0];
		if (oMessage.getElementsByTagName('error').length == 0)
		{
			if (this.opt.bShowModify && oMessage.getElementsByTagName('modified').length != 0)
				setInnerHTML(document.getElementById('modified_' + this.iCurMessageId), oMessage.getElementsByTagName('modified')[0].childNodes[0].nodeValue);
			this.oClickedIcon.getElementsByTagName('img')[0].src = oDiv.getElementsByTagName('img')[0].src;
		}
	}
}

// Event handler for clicking outside the list (will make the list disappear).
IconList.prototype.onWindowMouseDown = function ()
{
	for (var i = aIconLists.length - 1; i >= 0; i--)
	{
		aIconLists[i].funcParent.tmpMethod = aIconLists[i].collapseList;
		aIconLists[i].funcParent.tmpMethod();
		delete aIconLists[i].funcParent.tmpMethod;
	}
}

// Collapse the list of icons.
IconList.prototype.collapseList = function()
{
	this.onBoxHover(this.oClickedIcon, false);
	this.oContainerDiv.style.display = 'none';
	this.iCurMessageId = 0;
	document.body.removeEventListener('mousedown', this.onWindowMouseDown, false);
}

// Handy shortcuts for getting the mouse position on the screen - only used for IE at the moment.
function smf_mousePose(oEvent)
{
	var x = 0;
	var y = 0;

	if (oEvent.pageX)
	{
		y = oEvent.pageY;
		x = oEvent.pageX;
	}
	else if (oEvent.clientX)
	{
		x = oEvent.clientX + (document.documentElement.scrollLeft ? document.documentElement.scrollLeft : document.body.scrollLeft);
		y = oEvent.clientY + (document.documentElement.scrollTop ? document.documentElement.scrollTop : document.body.scrollTop);
	}

	return [x, y];
}

// Short function for finding the actual position of an item.
function smf_itemPos(itemHandle)
{
	var itemX = 0;
	var itemY = 0;

	if (typeof(itemHandle.offsetParent) != 'undefined')
	{
		itemX = itemHandle.offsetLeft;
		itemY = itemHandle.offsetTop;
		while (itemHandle.offsetParent && typeof(itemHandle.offsetParent) == 'object')
		{
			itemHandle = itemHandle.offsetParent;
			itemX += itemHandle.offsetLeft;
			itemY += itemHandle.offsetTop;
		}
	}
	else if (typeof(itemHandle.x) != 'undefined')
	{
		itemX = itemHandle.x;
		itemY = itemHandle.y;
	}

	return [itemX, itemY];
}

// This function takes the script URL and prepares it to allow the query string to be appended to it.
function smf_prepareScriptUrl(sUrl)
{
	return sUrl.indexOf('?') == -1 ? sUrl + '?' : sUrl + (sUrl.charAt(sUrl.length - 1) == '?' || sUrl.charAt(sUrl.length - 1) == '&' || sUrl.charAt(sUrl.length - 1) == ';' ? '' : ';');
}

var onload_events = new Array();
function add_load_event(func)
{
	// Get the old event if there is one.
	var oldOnload = window.onload;

	// Was the old event really an event?
	if (typeof(oldOnload) != 'function')
	{
		// Since we don't have anything at this point just add it stright in.
		window.onload = func;
	}
	// So it is a function but is it our special function?
	else if(onload_events.length == 0)
	{
		// Nope it is just a regular function...
		onload_events[0] = oldOnload;
		onload_events[1] = func;
		window.onload = function() {
			for (var i=0; i < onload_events.length; i++)
			{
				if (onload_events[i])
				{
					onload_events[i]();
				}
			}
		}
	}
	else
		// Ok just add it to the list of functions to call.
		onload_events[onload_events.length] = func;
}

function smfFooterHighlight(element, value)
{
	element.src = smf_images_url + "/" + (value ? "h_" : "") + element.id + ".gif";
}

// Get the text in a code tag.
function smfSelectText(curElement, actOnElement)
{
	// The place we're looking for is one div up, and next door - if it's auto detect.
	if (actOnElement)
		var codeArea = document.getElementById(curElement);
	else
		var codeArea = curElement.parentNode.nextSibling;

	if (!codeArea)
		return false;

	// Start off with my favourite, internet explorer.
	if (document.body.createTextRange)
	{
		var curRange = document.body.createTextRange();
		curRange.moveToElementText(codeArea);
		curRange.select();
	}
	// Firefox at el.
	else if (window.getSelection)
	{
		var curSelection = window.getSelection();
		// Safari is special!
		if (curSelection.setBaseAndExtent)
		{
			curSelection.setBaseAndExtent(codeArea, 0, codeArea, 1);
		}
		else
		{
			var curRange = document.createRange();
			curRange.selectNodeContents(codeArea);

			curSelection.removeAllRanges();
			curSelection.addRange(curRange);
		}
	}

	return false;
}