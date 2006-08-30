<?php
/******************************************************************************
* language_settings.php                                                       *
*******************************************************************************
* SMF: Simple Machines Forum                                                  *
* Open-Source Project Inspired by Zef Hemel (zef@zefhemel.com)                *
* =========================================================================== *
* Software Version:           SMF 2.0 Alpha                                   *
* Software by:                Simple Machines (http://www.simplemachines.org) *
* Copyright 2001-2006 by:     Lewis Media (http://www.lewismedia.com)         *
* Support, News, Updates at:  http://www.simplemachines.org                   *
*******************************************************************************
* This program is free software; you may redistribute it and/or modify it     *
* under the terms of the provided license as published by Lewis Media.        *
*                                                                             *
* This program is distributed in the hope that it is and will be useful,      *
* but WITHOUT ANY WARRANTIES; without even any implied warranty of            *
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.                        *
*                                                                             *
* See the "license.txt" file for details of the Simple Machines license.      *
* The latest version can always be found at http://www.simplemachines.org.    *
******************************************************************************/
/*

	The language tools have been created to facilitate the creation, 
	maintenance and distribution of the language packs needed for SMF.

	The tools assume the following directory structure:

	basedir
	|
	 --	english
	|	|
	|	 --	1-0
	|	|	|
	|	|	 --	Themes
	|	|		|
	|	|		 --	classic
	|	|		|	|
	|	|		|	 --	images
	|	|		|	|	|
	|	|		|	|	 --	english
	|	|		|	| 
	|	|		|	 --	languages
	|	|		|
	|	|		 --	default
	|	|			|
	|	|			 --	etc.
	|	|
	|	 --	1-1-rc2
	|		|
	|		 --	Themes
	|			|
	|			--	etc.
	 --	dutch
	|	|
	|	 --	1-0
	|	|	|
	|	|	 --	Themes
	|	|	|
	|	|	 --	etc.
	|	|
	|	 --	etc.
	|	
	 --	dutch-utf8
		|
		 -- etc.

	Note that the names of the directories should be using official language
	and version names. This way packs created from those language packs will
	automatically have the official names.

	The tools have been created and tested on a Windows system. *nix systems 
	would probably require some modifications to let all directories created
	be chmod'ed.

	The following tools are included:
	- language_sync.php
		This tool is designed to help making SMF's language files in sync with
		their english equivalents. It'll add tags that are missing, while
		removing tags that are not found in the english files. 

		It uses two language packs to create a third one. The two
		language packs used as input are:
		* the english pack, used as a template for the target language pack.
		* the reference pack, for files/strings that were already translated.

		This tool works both on macro and mirco level. On macro level it'll
		make sure the directory structure of the package is exactly like the
		directory structure of the english package. On micro level it'll go
		through the language files and sort out which tags have already been
		translated in the reference file and which tags haven't been.

		After walking through the steps, the tool will create a new directory
		in the target language directory, containing the updated language
		files. Be sure to do a compare (e.g. using winmerge.sourceforge.net)
		between the newly created language files and the original language
		files.

		Optionally you can create compressed .zip, .tar.gz, tar.bz2 files 
		of your newly created language pack afterwards.

	- language_charset.php
		This tool will convert a language pack into a UTF-8 compatible language
		pack. It can translate language packs from the following character sets:
		* big5
		* gbk
		* ISO-8859-1
		* ISO-8859-2
		* ISO-8859-9
		* tis-620
		* UTF-8
		* windows-1251
		* windows-1253
		* windows-1255
		* windows-1256
	
		The tool will create a full language pack, converting the PHP files,
		while simply copying the non-PHP files like images and stylesheets.

	- language_createpak.php
		This tool will create packed files of a language pack. The selected
		package will be packed into .zip, .tar.gz, and .tar.bz2 format.

		You need the appropriate applications for packing (see below).
*/


// The root directory containing the language packs.
$basedir = 'd:/_/tmp/lang';

// Location of bsdtar (http://gnuwin32.sourceforge.net/packages/bsdtar.htm).
$bsdtar = '"e:/Program Files/GnuWin32/bin/bsdtar"';

// Location of zip (http://gnuwin32.sourceforge.net/packages/zip.htm)
$zip = '"e:/Program Files/GnuWin32/bin/zip"';

?>