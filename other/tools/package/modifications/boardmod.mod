--------------------------------------------------------------------------------
	This is a LEGACY modification file that can be used with SMF.

	ATTENTION: If you are trying to install this manually, you should try
	the package manager.  If it will not work for you, please take a look
	at the following for information on this format:
		http://www.simplemachines.org/redirect/manual_mod_install

================================================================================

	This is a deprecated form of modification that can be simpler to use
	than the more advanced xml form.  Please avoid using this format.

--------------------------------------------------------------------------------

-- This information needs to be the same as that in the package-info.xml. --
<id>username:testing123</id>
<version>1.0</version>

-- Edit a specific file.. --
<edit file>$sourcedir/Filename.php</edit file>

-- The code to search for. --
<search for>
Search for some code to add stuff after.
</search for>

-- Add this text when we find it, after what we found. -->
<add after>
This is what to add.
</add after>

<search for>
Another search operation?
</search for>

<replace>
Yes, another.  This time, replacing!
</replace>