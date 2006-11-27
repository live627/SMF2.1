<?php
/**********************************************************************************
* smf_integration_arrays.php                                                      *
***********************************************************************************
* SMF: Simple Machines Forum                                                      *
* Open-Source Project Inspired by Zef Hemel (zef@zefhemel.com)                    *
* =============================================================================== *
* Software Version:           SMF 1.1                                             *
* Software by:                Simple Machines (http://www.simplemachines.org)     *
* Copyright 2006 by:          Simple Machines LLC (http://www.simplemachines.org) *
*           2001-2006 by:     Lewis Media (http://www.lewismedia.com)             *
* Support, News, Updates at:  http://www.simplemachines.org                       *
***********************************************************************************
* This program is free software; you may redistribute it and/or modify it under   *
* the terms of the provided license as published by Simple Machines LLC.          *
*                                                                                 *
* This program is distributed in the hope that it is and will be useful, but      *
* WITHOUT ANY WARRANTIES; without even any implied warranty of MERCHANTABILITY    *
* or FITNESS FOR A PARTICULAR PURPOSE.                                            *
*                                                                                 *
* See the "license.txt" file for details of the Simple Machines license.          *
* The latest version can always be found at http://www.simplemachines.org.        *
**********************************************************************************/


/* This file defines the arrays required for the Mambo-SMF bridge component


/** ensure this file is being included by a parent file and stop direct linking */
defined( '_VALID_MOS' ) or die( 'Direct Access to this location is not allowed.' );

global $language_conversion, $mosConfig_live_site, $mosConfig_sitename;

//define the integration functions
define('SMF_INTEGRATION_SETTINGS', serialize(array(
	'integrate_change_email' => 'integrate_change_email',
	'integrate_change_member_data' => 'integrate_change_member_data',
	'integrate_reset_pass' => 'integrate_reset_pass',
	'integrate_exit' => 'mambo_smf_exit',
	'integrate_logout' => 'integrate_logout',
	'integrate_outgoing_email' => 'integrate_outgoing_email',
	'integrate_login' => 'integrate_login',
	'integrate_validate_login' => 'integrate_validate_login',
	'integrate_redirect' => 'integrate_redirect',
	'integrate_delete_member' => 'integrate_delete_member',
	'integrate_register' => 'integrate_register',
	'integrate_pre_load' => 'integrate_pre_load',
	'integrate_whos_online' => 'integrate_whos_online',
)));

//correlate language name to ISO
$language_conversion = array(
							'aa' => 'afar',
							'ab' => 'abkhaz',
							'ae' => 'avestan',
							'af' => 'afrikaans',
							'ak' => 'akan',
							'ar' => 'arabic',
							'am' => 'amharic',
							'an' => 'aragonese',
							'as' => 'assamese',
							'av' => 'avaric',
							'ay' => 'aymara',
							'az' => 'azerbaijani',
							'ba' => 'bashkir',
							'be' => 'belarusian',
							'bg' => 'bulgarian',
							'bh' => 'bihari',
							'bi' => 'bislama',
							'bm' => 'bambara',
							'bn' => 'bangla',
							'br' => 'breton',
							'bs' => 'bosnian',
							'cr' => 'cree',
							'da' => 'danish',
							'de' => 'german',
							'dv' => 'divehi',
							'dz' => 'dzongkha',
							'en' => 'english',
							'fa' => 'farsi',
							'es' => 'spanish',
							'fr' => 'french',
							'gn' => 'guarani',
							'hr' => 'croatian',
							'hu' => 'hungarian',
							'hy' => 'armenian',
							'kr' => 'kanuri',
							'ml' => 'malayalam',
							'mo' => 'moldovan',
							'nb' => 'bokmål',
							'nl' => 'dutch',
							'nn' => 'nynorsk',
							'no' => 'norsk',
							'pl' => 'polish',
							'pt' => 'portuguese',
							'sh' => 'serbo-croatian',
							'sr' => 'serbian',
							'sq' => 'albanian',
							'sv' => 'swedish',
							'tg' => 'tajik',
							'th' => 'thai',
							'tr' => 'turkish',
							'iu' => 'inuktitut',
							'za' => 'zhuang',
							'zh' => 'chinese',
							'zu' => 'zulu',
							);

//Additions to the $txt array for the whos online
function add_to_txt(){

	global $txt, $mosConfig_live_site, $mosConfig_sitename, $mosConfig_sef;
	
	$txt['who_home'] = 'Viewing the home page of <a href="' . $mosConfig_live_site . '">' . $mosConfig_sitename . '</a>.';
	$txt['who_article'] = 'Viewing the article <a href="' . ($mosConfig_sef !='1' ? $mosConfig_live_site . '/index.php?option=com_content&amp;task=view&amp;id=%d&amp;Itemid=%d' : sefReltoAbs ('index.php?option=com_content&amp;task=view&amp;id=%d&amp;Itemid=%d')) . '">%s</a>.';
	$txt['who_section'] = 'Viewing the section <a href="' . ($mosConfig_sef !='1' ? $mosConfig_live_site . '/index.php?option=com_content&amp;task=section&amp;id=%d&amp;Itemid=%d' : sefReltoAbs ('index.php?option=com_content&amp;task=section&amp;id=%d&amp;Itemid=%d')) . '">%s</a>.';
	$txt['who_blogsection'] = 'Viewing the section <a href="' . ($mosConfig_sef !='1' ? $mosConfig_live_site . '/index.php?option=com_content&amp;task=blogsection&amp;id=%d&amp;Itemid=%d' : sefReltoAbs ('index.php?option=com_content&amp;task=blogsection&amp;id=%d&amp;Itemid=%d')) . '">%s</a>.';
	$txt['who_category'] = 'Viewing the category <a href="' . ($mosConfig_sef !='1' ? $mosConfig_live_site . '/index.php?option=com_content&amp;task=category&amp;id=%d&amp;Itemid=%d' : sefReltoAbs ('index.php?option=com_content&amp;task=category&amp;id=%d&amp;Itemid=%d')) . '">%s</a>.';
	$txt['who_blogcategory'] = 'Viewing the category <a href="' . ($mosConfig_sef !='1' ? $mosConfig_live_site . '/index.php?option=com_content&amp;task=category&amp;id=%d&amp;Itemid=%d' : sefReltoAbs ('index.php?option=com_content&amp;task=category&amp;id=%d&amp;Itemid=%d')) . '">%s</a>.';
	$txt['who_newsfeeds'] = 'Viewing the <a href="' . ($mosConfig_sef !='1' ? $mosConfig_live_site . '/index.php?option=com_newsfeeds&amp;task=view&amp;Itemid=%d' : sefReltoAbs ('index.php?option=com_newsfeeds&amp;task=view&amp;Itemid=%d')) . '">News Feeds</a>.';
	$txt['who_virtuemart'] = 'Shopping in the <a href="' . ($mosConfig_sef !='1' ? $mosConfig_live_site . '/index.php?option=com_virtuemart&amp;Itemid=%d' : sefReltoAbs ('index.php?option=com_virtuemart&amp;Itemid=%d')) . '">store</a>.';
	$txt['who_sitesearch'] = 'Using the <a href="' . ($mosConfig_sef !='1' ? $mosConfig_live_site . '/index.php?option=com_search&amp;Itemid=%d' : sefReltoAbs ('index.php?option=com_search&amp;Itemid=%d')) . '">Site Search</a>.';
	$txt['who_wiki'] = 'Viewing the <a href="' . ($mosConfig_sef !='1' ? $mosConfig_live_site . '/index.php?option=com_wikidoc&amp;Itemid=%d' : sefReltoAbs ('index.php?option=com_wikidoc&amp;Itemid=%d')) . '">Wiki</a>.';
}
?>