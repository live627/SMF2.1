// This file contains javascript associated with a drop down menu.

function smfMenu(menuID)
{
	// Store this...
	var menuHandle = document.getElementById(menuID);
	enableHover(menuID);

	// Store the mouse position
	var mousePos = new Array(2);

	// List of things to kill.
	var killPile = new Array();

	this.killElement = killElement;
	this.onMenuMouseMove = onMenuMouseMove;

	// This makes the menu act correctly with internet explorer.
	function enableHover()
	{
		// Cannot find it?
		if (!menuHandle)
			return false;

		// Don't need it?
		if (!is_ie)
			return false;

		// Get all potential child nodes.
		var menuItems = menuHandle.getElementsByTagName("LI");
		for (i = 0; i < menuItems.length; i++)
		{
			// Set a unique ID to track this element.
			menuItems[i].mid = i + 1;
			menuItems[i].onmouseover = function() {
				toggleMenuVisible(this, true);
			}
			menuItems[i].onmouseout = function() {
				toggleMenuVisible(this, false);
			}
		}

		// Find out what the mouse is doing.
		createEventListener(document.body);
		document.body.addEventListener('mousemove', onMenuMouseMove, false);
	}

	function toggleMenuVisible(itemHandle, is_visible)
	{
		if (!itemHandle.mid)
			return;

		if (is_visible)
		{
			itemHandle.className += " over";
		}
		else
		{
			killPile[itemHandle.mid] = itemHandle;
			setTimeout(function() {killElement(itemHandle.mid);}, 20);
		}
	}

	// Hide this element
	function killElement(elementID)
	{
		// If it's in the pile then make it dead....
		if (killPile[elementID])
		{
			// ... but only if it's not too close to the mouse!
			itemPos = smf_itemPos(killPile[elementID]);

			// Mouse near the X boundaries?
			//!!! This doesn't really work!
			/*var tolerance = 25;
			if (mousePos[0] > itemPos[0] - tolerance && mousePos[0] < itemPos[0] + killPile[elementID].offsetWidth + tolerance)
			{
				// .. and the Y?
				if (mousePos[1] > itemPos[1] - tolerance && mousePos[1] < itemPos[1] + killPile[elementID].offsetHeight + tolerance)
				{
					setTimeout(function() {killElement(elementID);}, 10);
					return true;
				}
			}*/

			// Otherwise delete!
			killPile[elementID].className = killPile[elementID].className.replace(" over", "");
		}
	}

	function onMenuMouseMove(evnt)
	{
		if (!evnt)
			evnt = window.event;

		// Get the mouse position.
		mousePos = smf_mousePose(evnt);
	}
}