// This file contains javascript associated with the captcha visual verification stuffs.

function smfCaptcha(imageURL, useLibrary, letterCount)
{
	// By default the letter count is five.
	if (!letterCount)
		letterCount = 5;

	autoCreate();

	// Automatically get the captcha event handlers in place and the like.
	function autoCreate()
	{
		// Is there anything to cycle images with - if so attach the refresh image functio.?
		cycleHandle = document.getElementById('visual_verification_refresh');
		if (cycleHandle)
		{
			createEventListener(cycleHandle);
			cycleHandle.addEventListener('click', refreshImages, false);
		}

		// Maybe a voice is here to spread light?
		soundHandle = document.getElementById('visual_verification_sound');
		if (soundHandle)
		{
			createEventListener(soundHandle);
			soundHandle.addEventListener('click', playSound, false);
		}
	}

	// Change the images.
	function refreshImages()
	{
		// Make sure we are using a new rand code.
		var new_url = new String(imageURL);
		new_url = new_url.substr(0, new_url.indexOf("rand=") + 5);

		// Quick and dirty way of converting decimal to hex
		var hexstr = "0123456789abcdef";
		for(var i=0; i < 32; i++)
			new_url = new_url + hexstr.substr(Math.floor(Math.random() * 16), 1);

		if (useLibrary && document.getElementById("verification_image"))
		{
			document.getElementById("verification_image").src = new_url;
		}
		else if (document.getElementById("verification_image"))
		{
			for (i = 1; i <= letterCount; i++)
				if (document.getElementById("verification_image_" + i))
					document.getElementById("verification_image_" + i).src = new_url + ";letter=" + i;
		}

		return false;
	}

	// Request a sound... play it Mr Soundman...
	function playSound()
	{
		return reqWin(imageURL + ";sound", 400, 120);
	}
}