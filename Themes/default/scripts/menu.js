// This file contains javascript associated with a drop down menu.

function smfMenu(menuID)
{
	// Store this...
	var menuHandle = document.getElementById(menuID);
	enableHover(menuID);

	// This makes the menu act correctly with internet explorer.
	function enableHover(menuID)
	{
		// Cannot find it?
		if (!menuHandle)
			return false;

		// Don't need it?
		if (!is_ie)
			return false;

		// For each of the children attach the hover function.
		for (i = 0; i < menuHandle.childNodes.length; i++)
		{
			// Found the list element?
			currentNode = menuHandle.childNodes[i];
			if (currentNode.nodeName == "LI")
			{
				currentNode.onmouseover=function() {
					toggleMenuVisible(this, true);
				}
				currentNode.onmouseout=function() {
					toggleMenuVisible(this, false);
				}
			}
		}
	}

	function toggleMenuVisible(itemHandle, is_visible)
	{
		if (is_visible)
			itemHandle.className += " over";
		else
			itemHandle.className = itemHandle.className.replace(" over", "");
	}
}