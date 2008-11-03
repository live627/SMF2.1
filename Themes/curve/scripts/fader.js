// Fade from... what text color? To which background color?
var smfFadeFrom = {"r": 0, "g": 0, "b": 0}, smfFadeTo = {"r": 255, "g": 255, "b": 255};

// Surround each item with... anything special?
var smfFadeBefore = "<b>", smfFadeAfter = "</b>";

var foreColor, foreEl, backEl, backColor;

if (typeof(document.getElementById('smfFadeScroller').currentStyle) != "undefined")
{
	foreColor = document.getElementById('smfFadeScroller').currentStyle.color.match(/#([\da-f][\da-f])([\da-f][\da-f])([\da-f][\da-f])/);
	smfFadeFrom = {"r": parseInt(foreColor[1]), "g": parseInt(foreColor[2]), "b": parseInt(foreColor[3])};

	backEl = document.getElementById('smfFadeScroller');
	while (backEl.currentStyle.backgroundColor == "transparent" && typeof(backEl.parentNode) != "undefined")
		backEl = backEl.parentNode;

	backColor = backEl.currentStyle.backgroundColor.match(/#([\da-f][\da-f])([\da-f][\da-f])([\da-f][\da-f])/);
	smfFadeTo = {"r": eval("0x" + backColor[1]), "g": eval("0x" + backColor[2]), "b": eval("0x" + backColor[3])};
}
else if (typeof(window.opera) == "undefined" && typeof(document.defaultView) != "undefined")
{
	foreEl = document.getElementById('smfFadeScroller');

	while (document.defaultView.getComputedStyle(foreEl, null).getPropertyCSSValue("color") == null && typeof(foreEl.parentNode) != "undefined" && typeof(foreEl.parentNode.tagName) != "undefined")
		foreEl = foreEl.parentNode;

	foreColor = document.defaultView.getComputedStyle(foreEl, null).getPropertyValue("color").match(/rgb\((\d+), (\d+), (\d+)\)/);
	smfFadeFrom = {"r": parseInt(foreColor[1]), "g": parseInt(foreColor[2]), "b": parseInt(foreColor[3])};

	backEl = document.getElementById('smfFadeScroller');

	while (document.defaultView.getComputedStyle(backEl, null).getPropertyCSSValue("background-color") == null && typeof(backEl.parentNode) != "undefined" && typeof(backEl.parentNode.tagName) != "undefined")
		backEl = backEl.parentNode;

	backColor = document.defaultView.getComputedStyle(backEl, null).getPropertyValue("background-color");//.match(/rgb\((\d+), (\d+), (\d+)\)/);
	smfFadeTo = {"r": parseInt(backColor[1]), "g": parseInt(backColor[2]), "b": parseInt(backColor[3])};
}

// List all the lines of the news for display.
var smfFadeContent = new Array();
		
// Get the news from the list in boardindex
var newselement = document.getElementById('smfFadeScroller').getElementsByTagName('li');
		
// Fill the array that has previously been created
for(i=0;i<newselement.length;i++)
{
	smfFadeContent[i] = newselement[i].innerHTML;
}



// smfFadeIndex: the current item in smfFadeContent.
var smfFadeIndex = -1;
// smfFadePercent: percent of fade. (-64 to 510.)
var smfFadePercent = 510
// smfFadeSwitch: direction. (in or out)
var smfFadeSwitch = false;
// smfFadeScroller: the actual div to mess with.
var smfFadeScroller = document.getElementById('smfFadeScroller');
// The ranges to fade from for R, G, and B. (how far apart they are.)
var smfFadeRange = {
	'r': smfFadeFrom.r - smfFadeTo.r,
	'g': smfFadeFrom.g - smfFadeTo.g,
	'b': smfFadeFrom.b - smfFadeTo.b
};

// Divide by 20 because we are doing it 20 times per one ms.
smfFadeDelay /= 20;

// Start the fader!
window.setTimeout('smfFader();', 20);

// Main	fading function... called 50 times every second.
function smfFader()
{
	if (smfFadeContent.length <= 1)
		return;

	// A fix for Internet Explorer 4: wait until the document is loaded so we can use setInnerHTML().
	if (typeof(window.document.readyState) != "undefined" && window.document.readyState != "complete")
	{
		window.setTimeout('smfFader();', 20);
		return;
	}

	// Starting out?  Set up the first item.
	if (smfFadeIndex == -1)
	{
		setInnerHTML(smfFadeScroller, smfFadeBefore + smfFadeContent[0] + smfFadeAfter);
		smfFadeIndex = 1;

		// In Mozilla, text jumps around from this when 1 or 0.5, etc...
		if (typeof(smfFadeScroller.style.MozOpacity) != "undefined")
			smfFadeScroller.style.MozOpacity = "0.90";
		else if (typeof(smfFadeScroller.style.opacity) != "undefined")
			smfFadeScroller.style.opacity = "0.90";
		// In Internet Explorer, we have to define this to use it.
		else if (typeof(smfFadeScroller.style.filter) != "undefined")
			smfFadeScroller.style.filter = "alpha(opacity=100)";
	}

	// Are we already done fading in?  If so, fade out.
	if (smfFadePercent >= 510)
		smfFadeSwitch = !smfFadeSwitch;
	// All the way faded out?
	else if (smfFadePercent <= -64)
	{
		smfFadeSwitch = !smfFadeSwitch;

		// Go to the next item, or first if we're out of items.
		setInnerHTML(smfFadeScroller, smfFadeBefore + smfFadeContent[smfFadeIndex++] + smfFadeAfter);
		if (smfFadeIndex >= smfFadeContent.length)
			smfFadeIndex = 0;
	}

	// Increment or decrement the fade percentage.
	if (smfFadeSwitch)
		smfFadePercent -= 255 / smfFadeDelay * 2;
	else
		smfFadePercent += 255 / smfFadeDelay * 2;

	// If it's not outside 0 and 256... (otherwise it's just delay time.)
	if (smfFadePercent < 256 && smfFadePercent > 0)
	{
		// Easier... also faster...
		var tempPercent = smfFadePercent / 255, rounded;

		if (typeof(smfFadeScroller.style.MozOpacity) != "undefined")
		{
			rounded = Math.round(tempPercent * 100) / 100;
			smfFadeScroller.style.MozOpacity = rounded == 1 ? "0.99" : rounded;
		}
		else if (typeof(smfFadeScroller.style.opacity) != "undefined")
		{
			rounded = Math.round(tempPercent * 100) / 100;
			smfFadeScroller.style.opacity = rounded == 1 ? "0.99" : rounded;
		}
		else
		{
			var done = false;
			if (typeof(smfFadeScroller.filters.alpha) != "undefined")
			{
				// Internet Explorer 4 just can't handle "try".
				eval("try\
					{\
						smfFadeScroller.filters.alpha.opacity = Math.round(tempPercent * 100);\
						done = true;\
					}\
					catch (err)\
					{\
					}");
			}

			if (!done)
			{
				// Get the new R, G, and B. (it should be bottom + (range of color * percent)...)
				var r = Math.ceil(smfFadeTo.r + smfFadeRange.r * tempPercent);
				var g = Math.ceil(smfFadeTo.g + smfFadeRange.g * tempPercent);
				var b = Math.ceil(smfFadeTo.b + smfFadeRange.b * tempPercent);

				// Set the color in the style, thereby fading it.
				smfFadeScroller.style.color = 'rgb(' + r + ', ' + g + ', ' + b + ')';
			}
		}
	}

	// Keep going.
	window.setTimeout('smfFader();', 20);
}