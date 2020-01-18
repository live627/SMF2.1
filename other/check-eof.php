<?php

/**
 * Simple Machines Forum (SMF)
 *
 * @package SMF
 * @author Simple Machines http://www.simplemachines.org
 * @copyright 2020 Simple Machines and individual contributors
 * @license https://www.simplemachines.org/about/smf/license.php BSD
 *
 * @version 2.1 RC2
 */

// Stuff we will ignore.
$ignoreFiles = array(
	'\./cache/',
	'\./other/',
	'\./tests/',
	'\./vendor/',

	// Minify Stuff\.
	'\./Sources/minify/',

	// random_compat()\.
	'\./Sources/random_compat/',

	// ReCaptcha Stuff\.
	'\./Sources/ReCaptcha/',

	// We will ignore Settings\.php if this is a live dev site\.
	'\./Settings\.php',
	'\./Settings_bak\.php',
	'\./db_last_error\.php',
);

foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator('.')) as $currentFile => $fileInfo)
{
	if ($fileInfo->getExtension() == 'php')
	{
		foreach ($ignoreFiles as $if)
			if (preg_match('~' . $if . '~i', $currentFile))
				continue 2;

		$file = fopen($currentFile, 'r');

		// Error?
		if ($file === false)
			die('Error: Unable to open file ' . $currentFile . "\n");

		// Seek the end minus some bytes.
		fseek($file, -100, SEEK_END);
		$contents = fread($file, 100);

		// There is some white space here.
		if (preg_match('~\?>\s+$~', $contents, $matches))
			die('Error: End of File contains extra spaces in ' . $currentFile . "\n");

		// Test to see if its there even, SMF 2.1 base package needs it there in our main files to allow package manager to properly handle end operations.  Customizations do not need it.
		if (!preg_match('~\?>$~', $contents, $matches))
			die('Error: End of File missing in ' . $currentFile . "\n");

		// Test to see if a function/class ending is here but with no return (because we are OCD).
		if (preg_match('~}([\r]?\n)?\?>~', $contents, $matches))
			echo('Error: Incorrect return(s) after last function/class but before EOF in ' . $currentFile . "\n");

		// Test to see if a string ending is here but with no return (because we are OCD).
		if (preg_match('~;([\r]?\n)?\?>~', $contents, $matches))
			echo('Error: Incorrect return(s) after last string but before EOF in ' . $currentFile . "\n");
	}
}

?>