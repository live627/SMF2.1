// Add a fix for code stuff?
if ((is_ie && !is_ie4) || is_webkit || is_ff)
	addLoadEvent(smf_codeBoxFix);

// The purpose of this code is to fix the height of overflow: auto div blocks, because IE can't figure it out for itself.
function smf_codeBoxFix()
{
	var codeFix = document.getElementsByTagName("code");
	for (var i = codeFix.length - 1; i >= 0; i--)
	{
		if (is_webkit && codeFix[i].offsetHeight < 20)
			codeFix[i].style.height = (codeFix[i].offsetHeight + 20) + "px";

		else if (is_ff && (codeFix[i].scrollWidth > codeFix[i].clientWidth || codeFix[i].clientWidth == 0))
			codeFix[i].style.overflow = "scroll";

		else if (typeof(codeFix[i].currentStyle) != 'undefined' && codeFix[i].currentStyle.overflow == "auto" && (codeFix[i].currentStyle.height == "" || codeFix[i].currentStyle.height == "auto") && (codeFix[i].scrollWidth > codeFix[i].clientWidth || codeFix[i].clientWidth == 0) && (codeFix[i].offsetHeight != 0))
			codeFix[i].style.height = (codeFix[i].offsetHeight + 24) + "px";
	}

	// !!! Is this still needed?
	if (!is_ff)
	{
		var divFix = document.getElementsByTagName("div");
		for (var i = divFix.length - 1; i > 0; i--)
		{
			if (is_webkit)
			{
				if ((divFix[i].className == "post" || divFix[i].className == "signature") && divFix[i].offsetHeight < 20)
					divFix[i].style.height = (divFix[i].offsetHeight + 20) + "px";
			}
			else
			{
				if (divFix[i].currentStyle.overflow == "auto" && (divFix[i].currentStyle.height == "" || divFix[i].currentStyle.height == "auto") && (divFix[i].scrollWidth > divFix[i].clientWidth || divFix[i].clientWidth == 0) && (divFix[i].offsetHeight != 0 || divFix[i].className == "code"))
					divFix[i].style.height = (divFix[i].offsetHeight + 24) + "px";
			}
		}
	}
}

function smf_addButton(sButtonStripId, bUseImage, oOptions)
{
	var oButtonStrip = document.getElementById(sButtonStripId);
	var aItems = oButtonStrip.getElementsByTagName('span');

	// Remove the 'last' class from the last item.
	if (aItems.length > 0)
	{
		var oLastSpan = aItems[aItems.length - 1];
		oLastSpan.className = oLastSpan.className.replace(/\s*last/, 'position_holder');
	}

	// Add the button.
	var oButtonStripList = oButtonStrip.getElementsByTagName('ul')[0];
	var oNewButton = document.createElement('li');
	setInnerHTML(oNewButton, '<a href="' + oOptions.sUrl + '" ' + (typeof(oOptions.sCustom) == 'string' ? oOptions.sCustom : '') + '><span class="last"' + (typeof(oOptions.sId) == 'string' ? ' id="' + oOptions.sId + '"': '') + '>' + oOptions.sText + '</span></a>');

	oButtonStripList.appendChild(oNewButton);
}


var main_menu = function() 
{
	var cssRule;
	var newSelector;
	for (var i = 0; i < document.styleSheets.length; i++)
		for (var x = 0; x < document.styleSheets[i].rules.length ; x++)
		{
			cssRule = document.styleSheets[i].rules[x];
			if (cssRule.selectorText.indexOf("LI:hover") != -1)
			{
				newSelector = cssRule.selectorText.replace(/LI:hover/gi, "LI.iehover");
				document.styleSheets[i].addRule(newSelector , cssRule.style.cssText);
			}
		}
		var getElm = document.getElementById("main_menu").getElementsByTagName("LI");
		for (var i=0; i<getElm.length; i++) 
		{
				getElm[i].onmouseover=function() {
				this.className+=" iehover";
		}
		getElm[i].onmouseout=function() 
		{
			this.className=this.className.replace(new RegExp(" iehover\\b"), "");
		}
	}
}
var adm_menu = function() 
{
	var cssRule;
	var newSelector;
	for (var i = 0; i < document.styleSheets.length; i++)
	for (var x = 0; x < document.styleSheets[i].rules.length ; x++)
	{
		cssRule = document.styleSheets[i].rules[x];
		if (cssRule.selectorText.indexOf("LI:hover") != -1)
		{
			newSelector = cssRule.selectorText.replace(/LI:hover/gi, "LI.iehover");
			document.styleSheets[i].addRule(newSelector , cssRule.style.cssText);
		}
	}
	//check the parent element fist!
	var possibleAdminMenu = document.getElementById("admin_menu");
	if (possibleAdminMenu)
	{
		var getElm = document.getElementById("admin_menu").getElementsByTagName("LI");
		for (var i=0; i<getElm.length; i++) 
		{
			getElm[i].onmouseover=function() 
			{
				this.className+=" iehover";
			}
            getElm[i].onmouseout=function() 
			{
				this.className=this.className.replace(new RegExp(" iehover\\b"), "");
            }
        }
   } 
}

if (window.attachEvent && is_ie6) 
{
	window.attachEvent("onload", main_menu);
	window.attachEvent("onload", adm_menu);
}
