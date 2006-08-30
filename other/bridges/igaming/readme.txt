Unzip it, and upload the files and directories, exactly as they are, to the iGamingCMS root directory.  This will populate your iGamingCMS installation with the bridge.

Now go to your iGamingCMS admin panel.  

First, click on your Plugin Manager.  You should see a new plugin named SMF Module Package 1.0.0.  Click on it, and install the Package. (Only once!)

Now, go to your Module Manager.  Click on Install Module.  Choose a Title for the module (Forum will do), and for the file, use "smf.php".  After that is done, enable the module by clicking the box next to the module name in the list of modules.

Now you should see the module in the list on the left side bar.  Click on it.

Click Configure Module.

Type in the absolute path to your SMF installation.  A path is not a URL (use the server path, something like /home/user/public_html/forum )  Do not put a trailing slash.

Choose a user group in SMF that can be Admins in iGamingCMS.  Only users in that group will be able to login to the iGamingCMS backend.

Click Submit.

Now, you'll want a link from your frontend menu.  Click on your Menu Manager.  Click on Add Item.  Choose Link - Custom URL.  Click Continue.

Choose whatever title you wish.  In the Link URL, type "smf.php".

Let me know how it goes, and I'll see what I can do.  I know I just went through the installation with iGaming 1.3.2, and had some trouble.