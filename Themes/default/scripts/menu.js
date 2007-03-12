// This file contains javascript associated with a drop down menu.

function smfMenu(menuID)
{
	// Store this...
	var menuHandle = document.getElementById(menuID);
	enableHover(menuID);

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
			menuItems[i].onmouseover=function() {
				toggleMenuVisible(this, true);
			}
			menuItems[i].onmouseout=function() {
				toggleMenuVisible(this, false);
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