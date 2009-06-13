function smf_NewsFader(oOptions)
{
	this.opt = oOptions;

	this.oFaderHandle = document.getElementById(this.opt.sFaderControlId);

	// Fade from... what text color? To which background color?
	this.oFadeFrom = typeof(this.opt.oFadeFrom) == 'object' ? this.opt.oFadeFrom : {"r": 0, "g": 0, "b": 0};
	this.oFadeTo = typeof(this.opt.oFadeTo) == 'object' ? this.opt.oFadeTo : {"r": 255, "g": 255, "b": 255};

	// Surround each item with... anything special?
	this.sItemTemplate = typeof(this.opt.sItemTemplate) == 'string' ? this.opt.sItemTemplate : '%1$s';

	// Fade delay (in milliseconds).
	this.iFadeDelay = typeof(this.opt.iFadeDelay) == 'integer' ? this.opt.iFadeDelay : 5000;

	// The array that contains all the lines of the news for display.
	this.aFaderItems = typeof(this.opt.aFaderItems) == 'object' ? this.opt.aFaderItems : [];

	// Should we look for fader data, still?
	this.bReceivedItemsOnConstruction = typeof(this.opt.aFaderItems) == 'object';

	// The current item in smfFadeContent.
	this.iFadeIndex = -1;

	// Percent of fade (-64 to 510).
	this.iFadePercent = 510

	// Direction (in or out).
	this.bFadeSwitch = false;

	// Just make sure the page is loaded before calling the init.
	setTimeout(this.opt.sSelf + '.init();', 1);
}

smf_NewsFader.prototype.init = function init()
{
	var oForeEl, oForeColor, oBackEl, oBackColor

	// Try to find the fore- and background colors.
	if (typeof(this.oFaderHandle.currentStyle) != "undefined")
	{
		oForeColor = this.oFaderHandle.currentStyle.color.match(/#([\da-f][\da-f])([\da-f][\da-f])([\da-f][\da-f])/);
		this.oFadeFrom = {"r": parseInt(oForeColor[1]), "g": parseInt(oForeColor[2]), "b": parseInt(oForeColor[3])};
	
		oBackEl = this.oFaderHandle;
		while (oBackEl.currentStyle.backgroundColor == "transparent" && typeof(oBackEl.parentNode) != "undefined")
			oBackEl = oBackEl.parentNode;
	
		oBackColor = oBackEl.currentStyle.backgroundColor.match(/#([\da-f][\da-f])([\da-f][\da-f])([\da-f][\da-f])/);
		this.oFadeTo = {"r": eval("0x" + oBackColor[1]), "g": eval("0x" + oBackColor[2]), "b": eval("0x" + oBackColor[3])};
	}
	else if (typeof(window.opera) == "undefined" && typeof(document.defaultView) != "undefined")
	{
		oForeEl = this.oFaderHandle;
		while (document.defaultView.getComputedStyle(oForeEl, null).getPropertyCSSValue("color") == null && typeof(oForeEl.parentNode) != "undefined" && typeof(oForeEl.parentNode.tagName) != "undefined")
			oForeEl = oForeEl.parentNode;
	
		oForeColor = document.defaultView.getComputedStyle(oForeEl, null).getPropertyValue("color").match(/rgb\((\d+), (\d+), (\d+)\)/);
		this.oFadeFrom = {"r": parseInt(oForeColor[1]), "g": parseInt(oForeColor[2]), "b": parseInt(oForeColor[3])};
	
		oBackEl = this.oFaderHandle;
		while (document.defaultView.getComputedStyle(oBackEl, null).getPropertyCSSValue("background-color") == null && typeof(oBackEl.parentNode) != "undefined" && typeof(oBackEl.parentNode.tagName) != "undefined")
			oBackEl = oBackEl.parentNode;
	
		oBackColor = document.defaultView.getComputedStyle(oBackEl, null).getPropertyValue("background-color");;
		this.oFadeTo = {"r": parseInt(oBackColor[1]), "g": parseInt(oBackColor[2]), "b": parseInt(oBackColor[3])};
	}

	// Did we get our fader items on construction, or should we be gathering them instead?
	if (!this.bReceivedItemsOnConstruction)
	{ 
		// Get the news from the list in boardindex
		var oNewsItems = this.oFaderHandle.getElementsByTagName('li');
		
		// Fill the array that has previously been created
		for (i = 0; i < oNewsItems.length; i ++)
			this.aFaderItems[i] = oNewsItems[i].innerHTML;
	}
	
	// The ranges to fade from for R, G, and B. (how far apart they are.)
	this.oFadeRange = {
		'r': this.oFadeFrom.r - this.oFadeTo.r,
		'g': this.oFadeFrom.g - this.oFadeTo.g,
		'b': this.oFadeFrom.b - this.oFadeTo.b
	};

	// Divide by 20 because we are doing it 20 times per one ms.
	this.iFadeDelay /= 20;

	// Start the fader!
	window.setTimeout(this.opt.sSelf + '.fade();', 20);
}

// Main	fading function... called 50 times every second.
smf_NewsFader.prototype.fade = function fade()
{
	if (this.aFaderItems.length <= 1)
		return;

	// A fix for Internet Explorer 4: wait until the document is loaded so we can use setInnerHTML().
	if (typeof(window.document.readyState) != "undefined" && window.document.readyState != "complete")
	{
		window.setTimeout(this.opt.sSelf + '.fade();', 20);
		return;
	}

	// Starting out?  Set up the first item.
	if (this.iFadeIndex == -1)
	{
		setInnerHTML(this.oFaderHandle, this.sItemTemplate.replace('%1$s', this.aFaderItems[0]));
		this.iFadeIndex = 1;

		// In Mozilla, text jumps around from this when 1 or 0.5, etc...
		if (typeof(this.oFaderHandle.style.MozOpacity) != "undefined")
			this.oFaderHandle.style.MozOpacity = "0.90";
		else if (typeof(this.oFaderHandle.style.opacity) != "undefined")
			this.oFaderHandle.style.opacity = "0.90";
		// In Internet Explorer, we have to define this to use it.
		else if (typeof(this.oFaderHandle.style.filter) != "undefined")
			this.oFaderHandle.style.filter = "alpha(opacity=100)";
	}

	// Are we already done fading in?  If so, fade out.
	if (this.iFadePercent >= 510)
		this.bFadeSwitch = !this.bFadeSwitch;
	// All the way faded out?
	else if (this.iFadePercent <= -64)
	{
		this.bFadeSwitch = !this.bFadeSwitch;

		// Go to the next item, or first if we're out of items.
		setInnerHTML(this.oFaderHandle, this.sItemTemplate.replace('%1$s', this.aFaderItems[this.iFadeIndex ++]));
		if (this.iFadeIndex >= this.aFaderItems.length)
			this.iFadeIndex = 0;
	}

	// Increment or decrement the fade percentage.
	if (this.bFadeSwitch)
		this.iFadePercent -= 255 / this.iFadeDelay * 2;
	else
		this.iFadePercent += 255 / this.iFadeDelay * 2;

	// If it's not outside 0 and 256... (otherwise it's just delay time.)
	if (this.iFadePercent < 256 && this.iFadePercent > 0)
	{
		// Easier... also faster...
		var tempPercent = this.iFadePercent / 255, rounded;

		if (typeof(this.oFaderHandle.style.MozOpacity) != "undefined")
		{
			rounded = Math.round(tempPercent * 100) / 100;
			this.oFaderHandle.style.MozOpacity = rounded == 1 ? "0.99" : rounded;
		}
		else if (typeof(this.oFaderHandle.style.opacity) != "undefined")
		{
			rounded = Math.round(tempPercent * 100) / 100;
			this.oFaderHandle.style.opacity = rounded == 1 ? "0.99" : rounded;
		}
		else
		{
			var done = false;
			if (typeof(this.oFaderHandle.filters.alpha) != "undefined")
			{
				try
				{
					this.oFaderHandle.filters.alpha.opacity = Math.round(tempPercent * 100);
					done = true;
				}
				catch (err)
				{
				}
			}

			if (!done)
			{
				// Get the new R, G, and B. (it should be bottom + (range of color * percent)...)
				var r = Math.ceil(this.oFadeTo.r + this.oFadeRange.r * tempPercent);
				var g = Math.ceil(this.oFadeTo.g + this.oFadeRange.g * tempPercent);
				var b = Math.ceil(this.oFadeTo.b + this.oFadeRange.b * tempPercent);

				// Set the color in the style, thereby fading it.
				this.oFaderHandle.style.color = 'rgb(' + r + ', ' + g + ', ' + b + ')';
			}
		}
	}

	// Keep going.
	window.setTimeout(this.opt.sSelf + '.fade();', 20);
}