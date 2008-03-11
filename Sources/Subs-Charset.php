<?php
/**********************************************************************************
* Subs-Charset.php                                                                *
***********************************************************************************
* SMF: Simple Machines Forum                                                      *
* Open-Source Project Inspired by Zef Hemel (zef@zefhemel.com)                    *
* =============================================================================== *
* Software Version:           SMF 2.0 Beta 3                                      *
* Software by:                Simple Machines (http://www.simplemachines.org)     *
* Copyright 2006-2008 by:     Simple Machines LLC (http://www.simplemachines.org) *
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

if (!defined('SMF'))
	die('Hacking attempt...');

/*	This file has functions in it to do with character set and string
	manipulation.  It provides these functions:

	string utf8_strtolower(string $string)
		- converts a UTF-8 string into a lowercase UTF-8 string.
		- equivalent to mb_strtolower($string, 'UTF-8')

	string utf8_strtoupper(string $string)
		- converts a UTF-8 string into a uppercase UTF-8 string.
		- equivalent to mb_strtoupper($string, 'UTF-8')

	void fix_serialized_columns()
		- fixes corrupted serialized strings after a character set conversion.

*/

// Converts the given UTF-8 string into lowercase.
function utf8_strtolower($string)
{
	static $case_folding = array(
		'A' => 'a',		'B' => 'b',		'C' => 'c',		'D' => 'd',
		'E' => 'e',		'F' => 'f',		'G' => 'g',		'H' => 'h',
		'I' => 'i',		'J' => 'j',		'K' => 'k',		'L' => 'l',
		'M' => 'm',		'N' => 'n',		'O' => 'o',		'P' => 'p',
		'Q' => 'q',		'R' => 'r',		'S' => 's',		'T' => 't',
		'U' => 'u',		'V' => 'v',		'W' => 'w',		'X' => 'x',
		'Y' => 'y',		'Z' => 'z',		'µ' => 'μ',		'À' => 'à',
		'Á' => 'á',		'Â' => 'â',		'Ã' => 'ã',		'Ä' => 'ä',
		'Å' => 'å',		'Æ' => 'æ',		'Ç' => 'ç',		'È' => 'è',
		'É' => 'é',		'Ê' => 'ê',		'Ë' => 'ë',		'Ì' => 'ì',
		'Í' => 'í',		'Î' => 'î',		'Ï' => 'ï',		'Ð' => 'ð',
		'Ñ' => 'ñ',		'Ò' => 'ò',		'Ó' => 'ó',		'Ô' => 'ô',
		'Õ' => 'õ',		'Ö' => 'ö',		'Ø' => 'ø',		'Ù' => 'ù',
		'Ú' => 'ú',		'Û' => 'û',		'Ü' => 'ü',		'Ý' => 'ý',
		'Þ' => 'þ',		'ß' => 'ss',	'Ā' => 'ā',		'Ă' => 'ă',
		'Ą' => 'ą',		'Ć' => 'ć',		'Ĉ' => 'ĉ',		'Ċ' => 'ċ',
		'Č' => 'č',		'Ď' => 'ď',		'Đ' => 'đ',		'Ē' => 'ē',
		'Ĕ' => 'ĕ',		'Ė' => 'ė',		'Ę' => 'ę',		'Ě' => 'ě',
		'Ĝ' => 'ĝ',		'Ğ' => 'ğ',		'Ġ' => 'ġ',		'Ģ' => 'ģ',
		'Ĥ' => 'ĥ',		'Ħ' => 'ħ',		'Ĩ' => 'ĩ',		'Ī' => 'ī',
		'Ĭ' => 'ĭ',		'Į' => 'į',		'İ' => 'i̇',		'Ĳ' => 'ĳ',
		'Ĵ' => 'ĵ',		'Ķ' => 'ķ',		'Ĺ' => 'ĺ',		'Ļ' => 'ļ',
		'Ľ' => 'ľ',		'Ŀ' => 'ŀ',		'Ł' => 'ł',		'Ń' => 'ń',
		'Ņ' => 'ņ',		'Ň' => 'ň',		'ŉ' => 'ʼn',	'Ŋ' => 'ŋ',
		'Ō' => 'ō',		'Ŏ' => 'ŏ',		'Ő' => 'ő',		'Œ' => 'œ',
		'Ŕ' => 'ŕ',		'Ŗ' => 'ŗ',		'Ř' => 'ř',		'Ś' => 'ś',
		'Ŝ' => 'ŝ',		'Ş' => 'ş',		'Š' => 'š',		'Ţ' => 'ţ',
		'Ť' => 'ť',		'Ŧ' => 'ŧ',		'Ũ' => 'ũ',		'Ū' => 'ū',
		'Ŭ' => 'ŭ',		'Ů' => 'ů',		'Ű' => 'ű',		'Ų' => 'ų',
		'Ŵ' => 'ŵ',		'Ŷ' => 'ŷ',		'Ÿ' => 'ÿ',		'Ź' => 'ź',
		'Ż' => 'ż',		'Ž' => 'ž',		'ſ' => 's',		'Ɓ' => 'ɓ',
		'Ƃ' => 'ƃ',		'Ƅ' => 'ƅ',		'Ɔ' => 'ɔ',		'Ƈ' => 'ƈ',
		'Ɖ' => 'ɖ',		'Ɗ' => 'ɗ',		'Ƌ' => 'ƌ',		'Ǝ' => 'ǝ',
		'Ə' => 'ə',		'Ɛ' => 'ɛ',		'Ƒ' => 'ƒ',		'Ɠ' => 'ɠ',
		'Ɣ' => 'ɣ',		'Ɩ' => 'ɩ',		'Ɨ' => 'ɨ',		'Ƙ' => 'ƙ',
		'Ɯ' => 'ɯ',		'Ɲ' => 'ɲ',		'Ɵ' => 'ɵ',		'Ơ' => 'ơ',
		'Ƣ' => 'ƣ',		'Ƥ' => 'ƥ',		'Ʀ' => 'ʀ',		'Ƨ' => 'ƨ',
		'Ʃ' => 'ʃ',		'Ƭ' => 'ƭ',		'Ʈ' => 'ʈ',		'Ư' => 'ư',
		'Ʊ' => 'ʊ',		'Ʋ' => 'ʋ',		'Ƴ' => 'ƴ',		'Ƶ' => 'ƶ',
		'Ʒ' => 'ʒ',		'Ƹ' => 'ƹ',		'Ƽ' => 'ƽ',		'Ǆ' => 'ǆ',
		'ǅ' => 'ǆ',		'Ǉ' => 'ǉ',		'ǈ' => 'ǉ',		'Ǌ' => 'ǌ',
		'ǋ' => 'ǌ',		'Ǎ' => 'ǎ',		'Ǐ' => 'ǐ',		'Ǒ' => 'ǒ',
		'Ǔ' => 'ǔ',		'Ǖ' => 'ǖ',		'Ǘ' => 'ǘ',		'Ǚ' => 'ǚ',
		'Ǜ' => 'ǜ',		'Ǟ' => 'ǟ',		'Ǡ' => 'ǡ',		'Ǣ' => 'ǣ',
		'Ǥ' => 'ǥ',		'Ǧ' => 'ǧ',		'Ǩ' => 'ǩ',		'Ǫ' => 'ǫ',
		'Ǭ' => 'ǭ',		'Ǯ' => 'ǯ',		'ǰ' => 'ǰ',		'Ǳ' => 'ǳ',
		'ǲ' => 'ǳ',		'Ǵ' => 'ǵ',		'Ƕ' => 'ƕ',		'Ƿ' => 'ƿ',
		'Ǹ' => 'ǹ',		'Ǻ' => 'ǻ',		'Ǽ' => 'ǽ',		'Ǿ' => 'ǿ',
		'Ȁ' => 'ȁ',		'Ȃ' => 'ȃ',		'Ȅ' => 'ȅ',		'Ȇ' => 'ȇ',
		'Ȉ' => 'ȉ',		'Ȋ' => 'ȋ',		'Ȍ' => 'ȍ',		'Ȏ' => 'ȏ',
		'Ȑ' => 'ȑ',		'Ȓ' => 'ȓ',		'Ȕ' => 'ȕ',		'Ȗ' => 'ȗ',
		'Ș' => 'ș',		'Ț' => 'ț',		'Ȝ' => 'ȝ',		'Ȟ' => 'ȟ',
		'Ƞ' => 'ƞ',		'Ȣ' => 'ȣ',		'Ȥ' => 'ȥ',		'Ȧ' => 'ȧ',
		'Ȩ' => 'ȩ',		'Ȫ' => 'ȫ',		'Ȭ' => 'ȭ',		'Ȯ' => 'ȯ',
		'Ȱ' => 'ȱ',		'Ȳ' => 'ȳ',		'Ⱥ' => 'ⱥ',		'Ȼ' => 'ȼ',
		'Ƚ' => 'ƚ',		'Ⱦ' => 'ⱦ',		'Ɂ' => 'ɂ',		'Ƀ' => 'ƀ',
		'Ʉ' => 'ʉ',		'Ʌ' => 'ʌ',		'Ɇ' => 'ɇ',		'Ɉ' => 'ɉ',
		'Ɋ' => 'ɋ',		'Ɍ' => 'ɍ',		'Ɏ' => 'ɏ',		'ͅ' => 'ι',
		'Ά' => 'ά',		'Έ' => 'έ',		'Ή' => 'ή',		'Ί' => 'ί',
		'Ό' => 'ό',		'Ύ' => 'ύ',		'Ώ' => 'ώ',		'ΐ' => 'ΐ',
		'Α' => 'α',		'Β' => 'β',		'Γ' => 'γ',		'Δ' => 'δ',
		'Ε' => 'ε',		'Ζ' => 'ζ',		'Η' => 'η',		'Θ' => 'θ',
		'Ι' => 'ι',		'Κ' => 'κ',		'Λ' => 'λ',		'Μ' => 'μ',
		'Ν' => 'ν',		'Ξ' => 'ξ',		'Ο' => 'ο',		'Π' => 'π',
		'Ρ' => 'ρ',		'Σ' => 'σ',		'Τ' => 'τ',		'Υ' => 'υ',
		'Φ' => 'φ',		'Χ' => 'χ',		'Ψ' => 'ψ',		'Ω' => 'ω',
		'Ϊ' => 'ϊ',		'Ϋ' => 'ϋ',		'ΰ' => 'ΰ',	'ς' => 'σ',
		'ϐ' => 'β',		'ϑ' => 'θ',		'ϕ' => 'φ',		'ϖ' => 'π',
		'Ϙ' => 'ϙ',		'Ϛ' => 'ϛ',		'Ϝ' => 'ϝ',		'Ϟ' => 'ϟ',
		'Ϡ' => 'ϡ',		'Ϣ' => 'ϣ',		'Ϥ' => 'ϥ',		'Ϧ' => 'ϧ',
		'Ϩ' => 'ϩ',		'Ϫ' => 'ϫ',		'Ϭ' => 'ϭ',		'Ϯ' => 'ϯ',
		'ϰ' => 'κ',		'ϱ' => 'ρ',		'ϴ' => 'θ',		'ϵ' => 'ε',
		'Ϸ' => 'ϸ',		'Ϲ' => 'ϲ',		'Ϻ' => 'ϻ',		'Ͻ' => 'ͻ',
		'Ͼ' => 'ͼ',		'Ͽ' => 'ͽ',		'Ѐ' => 'ѐ',		'Ё' => 'ё',
		'Ђ' => 'ђ',		'Ѓ' => 'ѓ',		'Є' => 'є',		'Ѕ' => 'ѕ',
		'І' => 'і',		'Ї' => 'ї',		'Ј' => 'ј',		'Љ' => 'љ',
		'Њ' => 'њ',		'Ћ' => 'ћ',		'Ќ' => 'ќ',		'Ѝ' => 'ѝ',
		'Ў' => 'ў',		'Џ' => 'џ',		'А' => 'а',		'Б' => 'б',
		'В' => 'в',		'Г' => 'г',		'Д' => 'д',		'Е' => 'е',
		'Ж' => 'ж',		'З' => 'з',		'И' => 'и',		'Й' => 'й',
		'К' => 'к',		'Л' => 'л',		'М' => 'м',		'Н' => 'н',
		'О' => 'о',		'П' => 'п',		'Р' => 'р',		'С' => 'с',
		'Т' => 'т',		'У' => 'у',		'Ф' => 'ф',		'Х' => 'х',
		'Ц' => 'ц',		'Ч' => 'ч',		'Ш' => 'ш',		'Щ' => 'щ',
		'Ъ' => 'ъ',		'Ы' => 'ы',		'Ь' => 'ь',		'Э' => 'э',
		'Ю' => 'ю',		'Я' => 'я',		'Ѡ' => 'ѡ',		'Ѣ' => 'ѣ',
		'Ѥ' => 'ѥ',		'Ѧ' => 'ѧ',		'Ѩ' => 'ѩ',		'Ѫ' => 'ѫ',
		'Ѭ' => 'ѭ',		'Ѯ' => 'ѯ',		'Ѱ' => 'ѱ',		'Ѳ' => 'ѳ',
		'Ѵ' => 'ѵ',		'Ѷ' => 'ѷ',		'Ѹ' => 'ѹ',		'Ѻ' => 'ѻ',
		'Ѽ' => 'ѽ',		'Ѿ' => 'ѿ',		'Ҁ' => 'ҁ',		'Ҋ' => 'ҋ',
		'Ҍ' => 'ҍ',		'Ҏ' => 'ҏ',		'Ґ' => 'ґ',		'Ғ' => 'ғ',
		'Ҕ' => 'ҕ',		'Җ' => 'җ',		'Ҙ' => 'ҙ',		'Қ' => 'қ',
		'Ҝ' => 'ҝ',		'Ҟ' => 'ҟ',		'Ҡ' => 'ҡ',		'Ң' => 'ң',
		'Ҥ' => 'ҥ',		'Ҧ' => 'ҧ',		'Ҩ' => 'ҩ',		'Ҫ' => 'ҫ',
		'Ҭ' => 'ҭ',		'Ү' => 'ү',		'Ұ' => 'ұ',		'Ҳ' => 'ҳ',
		'Ҵ' => 'ҵ',		'Ҷ' => 'ҷ',		'Ҹ' => 'ҹ',		'Һ' => 'һ',
		'Ҽ' => 'ҽ',		'Ҿ' => 'ҿ',		'Ӏ' => 'ӏ',		'Ӂ' => 'ӂ',
		'Ӄ' => 'ӄ',		'Ӆ' => 'ӆ',		'Ӈ' => 'ӈ',		'Ӊ' => 'ӊ',
		'Ӌ' => 'ӌ',		'Ӎ' => 'ӎ',		'Ӑ' => 'ӑ',		'Ӓ' => 'ӓ',
		'Ӕ' => 'ӕ',		'Ӗ' => 'ӗ',		'Ә' => 'ә',		'Ӛ' => 'ӛ',
		'Ӝ' => 'ӝ',		'Ӟ' => 'ӟ',		'Ӡ' => 'ӡ',		'Ӣ' => 'ӣ',
		'Ӥ' => 'ӥ',		'Ӧ' => 'ӧ',		'Ө' => 'ө',		'Ӫ' => 'ӫ',
		'Ӭ' => 'ӭ',		'Ӯ' => 'ӯ',		'Ӱ' => 'ӱ',		'Ӳ' => 'ӳ',
		'Ӵ' => 'ӵ',		'Ӷ' => 'ӷ',		'Ӹ' => 'ӹ',		'Ӻ' => 'ӻ',
		'Ӽ' => 'ӽ',		'Ӿ' => 'ӿ',		'Ԁ' => 'ԁ',		'Ԃ' => 'ԃ',
		'Ԅ' => 'ԅ',		'Ԇ' => 'ԇ',		'Ԉ' => 'ԉ',		'Ԋ' => 'ԋ',
		'Ԍ' => 'ԍ',		'Ԏ' => 'ԏ',		'Ԑ' => 'ԑ',		'Ԓ' => 'ԓ',
		'Ա' => 'ա',		'Բ' => 'բ',		'Գ' => 'գ',		'Դ' => 'դ',
		'Ե' => 'ե',		'Զ' => 'զ',		'Է' => 'է',		'Ը' => 'ը',
		'Թ' => 'թ',		'Ժ' => 'ժ',		'Ի' => 'ի',		'Լ' => 'լ',
		'Խ' => 'խ',		'Ծ' => 'ծ',		'Կ' => 'կ',		'Հ' => 'հ',
		'Ձ' => 'ձ',		'Ղ' => 'ղ',		'Ճ' => 'ճ',		'Մ' => 'մ',
		'Յ' => 'յ',		'Ն' => 'ն',		'Շ' => 'շ',		'Ո' => 'ո',
		'Չ' => 'չ',		'Պ' => 'պ',		'Ջ' => 'ջ',		'Ռ' => 'ռ',
		'Ս' => 'ս',		'Վ' => 'վ',		'Տ' => 'տ',		'Ր' => 'ր',
		'Ց' => 'ց',		'Ւ' => 'ւ',		'Փ' => 'փ',		'Ք' => 'ք',
		'Օ' => 'օ',		'Ֆ' => 'ֆ',		'և' => 'եւ',		'Ⴀ' => 'ⴀ',
		'Ⴁ' => 'ⴁ',		'Ⴂ' => 'ⴂ',		'Ⴃ' => 'ⴃ',		'Ⴄ' => 'ⴄ',
		'Ⴅ' => 'ⴅ',		'Ⴆ' => 'ⴆ',		'Ⴇ' => 'ⴇ',		'Ⴈ' => 'ⴈ',
		'Ⴉ' => 'ⴉ',		'Ⴊ' => 'ⴊ',		'Ⴋ' => 'ⴋ',		'Ⴌ' => 'ⴌ',
		'Ⴍ' => 'ⴍ',		'Ⴎ' => 'ⴎ',		'Ⴏ' => 'ⴏ',		'Ⴐ' => 'ⴐ',
		'Ⴑ' => 'ⴑ',		'Ⴒ' => 'ⴒ',		'Ⴓ' => 'ⴓ',		'Ⴔ' => 'ⴔ',
		'Ⴕ' => 'ⴕ',		'Ⴖ' => 'ⴖ',		'Ⴗ' => 'ⴗ',		'Ⴘ' => 'ⴘ',
		'Ⴙ' => 'ⴙ',		'Ⴚ' => 'ⴚ',		'Ⴛ' => 'ⴛ',		'Ⴜ' => 'ⴜ',
		'Ⴝ' => 'ⴝ',		'Ⴞ' => 'ⴞ',		'Ⴟ' => 'ⴟ',		'Ⴠ' => 'ⴠ',
		'Ⴡ' => 'ⴡ',		'Ⴢ' => 'ⴢ',		'Ⴣ' => 'ⴣ',		'Ⴤ' => 'ⴤ',
		'Ⴥ' => 'ⴥ',		'Ḁ' => 'ḁ',		'Ḃ' => 'ḃ',		'Ḅ' => 'ḅ',
		'Ḇ' => 'ḇ',		'Ḉ' => 'ḉ',		'Ḋ' => 'ḋ',		'Ḍ' => 'ḍ',
		'Ḏ' => 'ḏ',		'Ḑ' => 'ḑ',		'Ḓ' => 'ḓ',		'Ḕ' => 'ḕ',
		'Ḗ' => 'ḗ',		'Ḙ' => 'ḙ',		'Ḛ' => 'ḛ',		'Ḝ' => 'ḝ',
		'Ḟ' => 'ḟ',		'Ḡ' => 'ḡ',		'Ḣ' => 'ḣ',		'Ḥ' => 'ḥ',
		'Ḧ' => 'ḧ',		'Ḩ' => 'ḩ',		'Ḫ' => 'ḫ',		'Ḭ' => 'ḭ',
		'Ḯ' => 'ḯ',		'Ḱ' => 'ḱ',		'Ḳ' => 'ḳ',		'Ḵ' => 'ḵ',
		'Ḷ' => 'ḷ',		'Ḹ' => 'ḹ',		'Ḻ' => 'ḻ',		'Ḽ' => 'ḽ',
		'Ḿ' => 'ḿ',		'Ṁ' => 'ṁ',		'Ṃ' => 'ṃ',		'Ṅ' => 'ṅ',
		'Ṇ' => 'ṇ',		'Ṉ' => 'ṉ',		'Ṋ' => 'ṋ',		'Ṍ' => 'ṍ',
		'Ṏ' => 'ṏ',		'Ṑ' => 'ṑ',		'Ṓ' => 'ṓ',		'Ṕ' => 'ṕ',
		'Ṗ' => 'ṗ',		'Ṙ' => 'ṙ',		'Ṛ' => 'ṛ',		'Ṝ' => 'ṝ',
		'Ṟ' => 'ṟ',		'Ṡ' => 'ṡ',		'Ṣ' => 'ṣ',		'Ṥ' => 'ṥ',
		'Ṧ' => 'ṧ',		'Ṩ' => 'ṩ',		'Ṫ' => 'ṫ',		'Ṭ' => 'ṭ',
		'Ṯ' => 'ṯ',		'Ṱ' => 'ṱ',		'Ṳ' => 'ṳ',		'Ṵ' => 'ṵ',
		'Ṷ' => 'ṷ',		'Ṹ' => 'ṹ',		'Ṻ' => 'ṻ',		'Ṽ' => 'ṽ',
		'Ṿ' => 'ṿ',		'Ẁ' => 'ẁ',		'Ẃ' => 'ẃ',		'Ẅ' => 'ẅ',
		'Ẇ' => 'ẇ',		'Ẉ' => 'ẉ',		'Ẋ' => 'ẋ',		'Ẍ' => 'ẍ',
		'Ẏ' => 'ẏ',		'Ẑ' => 'ẑ',		'Ẓ' => 'ẓ',		'Ẕ' => 'ẕ',
		'ẖ' => 'ẖ',		'ẗ' => 'ẗ',		'ẘ' => 'ẘ',		'ẙ' => 'ẙ',
		'ẚ' => 'aʾ',	'ẛ' => 'ṡ',		'Ạ' => 'ạ',		'Ả' => 'ả',
		'Ấ' => 'ấ',		'Ầ' => 'ầ',		'Ẩ' => 'ẩ',		'Ẫ' => 'ẫ',
		'Ậ' => 'ậ',		'Ắ' => 'ắ',		'Ằ' => 'ằ',		'Ẳ' => 'ẳ',
		'Ẵ' => 'ẵ',		'Ặ' => 'ặ',		'Ẹ' => 'ẹ',		'Ẻ' => 'ẻ',
		'Ẽ' => 'ẽ',		'Ế' => 'ế',		'Ề' => 'ề',		'Ể' => 'ể',
		'Ễ' => 'ễ',		'Ệ' => 'ệ',		'Ỉ' => 'ỉ',		'Ị' => 'ị',
		'Ọ' => 'ọ',		'Ỏ' => 'ỏ',		'Ố' => 'ố',		'Ồ' => 'ồ',
		'Ổ' => 'ổ',		'Ỗ' => 'ỗ',		'Ộ' => 'ộ',		'Ớ' => 'ớ',
		'Ờ' => 'ờ',		'Ở' => 'ở',		'Ỡ' => 'ỡ',		'Ợ' => 'ợ',
		'Ụ' => 'ụ',		'Ủ' => 'ủ',		'Ứ' => 'ứ',		'Ừ' => 'ừ',
		'Ử' => 'ử',		'Ữ' => 'ữ',		'Ự' => 'ự',		'Ỳ' => 'ỳ',
		'Ỵ' => 'ỵ',		'Ỷ' => 'ỷ',		'Ỹ' => 'ỹ',		'Ἀ' => 'ἀ',
		'Ἁ' => 'ἁ',		'Ἂ' => 'ἂ',		'Ἃ' => 'ἃ',		'Ἄ' => 'ἄ',
		'Ἅ' => 'ἅ',		'Ἆ' => 'ἆ',		'Ἇ' => 'ἇ',		'Ἐ' => 'ἐ',
		'Ἑ' => 'ἑ',		'Ἒ' => 'ἒ',		'Ἓ' => 'ἓ',		'Ἔ' => 'ἔ',
		'Ἕ' => 'ἕ',		'Ἠ' => 'ἠ',		'Ἡ' => 'ἡ',		'Ἢ' => 'ἢ',
		'Ἣ' => 'ἣ',		'Ἤ' => 'ἤ',		'Ἥ' => 'ἥ',		'Ἦ' => 'ἦ',
		'Ἧ' => 'ἧ',		'Ἰ' => 'ἰ',		'Ἱ' => 'ἱ',		'Ἲ' => 'ἲ',
		'Ἳ' => 'ἳ',		'Ἴ' => 'ἴ',		'Ἵ' => 'ἵ',		'Ἶ' => 'ἶ',
		'Ἷ' => 'ἷ',		'Ὀ' => 'ὀ',		'Ὁ' => 'ὁ',		'Ὂ' => 'ὂ',
		'Ὃ' => 'ὃ',		'Ὄ' => 'ὄ',		'Ὅ' => 'ὅ',		'ὐ' => 'ὐ',
		'ὒ' => 'ὒ',	'ὔ' => 'ὔ',	'ὖ' => 'ὖ',		'Ὑ' => 'ὑ',
		'Ὓ' => 'ὓ',		'Ὕ' => 'ὕ',		'Ὗ' => 'ὗ',		'Ὠ' => 'ὠ',
		'Ὡ' => 'ὡ',		'Ὢ' => 'ὢ',		'Ὣ' => 'ὣ',		'Ὤ' => 'ὤ',
		'Ὥ' => 'ὥ',		'Ὦ' => 'ὦ',		'Ὧ' => 'ὧ',		'ᾀ' => 'ἀι',
		'ᾁ' => 'ἁι',	'ᾂ' => 'ἂι',	'ᾃ' => 'ἃι',	'ᾄ' => 'ἄι',
		'ᾅ' => 'ἅι',	'ᾆ' => 'ἆι',	'ᾇ' => 'ἇι',	'ᾈ' => 'ᾀ',
		'ᾉ' => 'ᾁ',		'ᾊ' => 'ᾂ',		'ᾋ' => 'ᾃ',		'ᾌ' => 'ᾄ',
		'ᾍ' => 'ᾅ',		'ᾎ' => 'ᾆ',		'ᾏ' => 'ᾇ',		'ᾐ' => 'ἠι',
		'ᾑ' => 'ἡι',	'ᾒ' => 'ἢι',	'ᾓ' => 'ἣι',	'ᾔ' => 'ἤι',
		'ᾕ' => 'ἥι',	'ᾖ' => 'ἦι',	'ᾗ' => 'ἧι',	'ᾘ' => 'ᾐ',
		'ᾙ' => 'ᾑ',		'ᾚ' => 'ᾒ',		'ᾛ' => 'ᾓ',		'ᾜ' => 'ᾔ',
		'ᾝ' => 'ᾕ',		'ᾞ' => 'ᾖ',		'ᾟ' => 'ᾗ',		'ᾠ' => 'ὠι',
		'ᾡ' => 'ὡι',	'ᾢ' => 'ὢι',	'ᾣ' => 'ὣι',	'ᾤ' => 'ὤι',
		'ᾥ' => 'ὥι',	'ᾦ' => 'ὦι',	'ᾧ' => 'ὧι',	'ᾨ' => 'ᾠ',
		'ᾩ' => 'ᾡ',		'ᾪ' => 'ᾢ',		'ᾫ' => 'ᾣ',		'ᾬ' => 'ᾤ',
		'ᾭ' => 'ᾥ',		'ᾮ' => 'ᾦ',		'ᾯ' => 'ᾧ',		'ᾲ' => 'ὰι',
		'ᾳ' => 'αι',	'ᾴ' => 'άι',	'ᾶ' => 'ᾶ',		'ᾷ' => 'ᾶι',
		'Ᾰ' => 'ᾰ',		'Ᾱ' => 'ᾱ',		'Ὰ' => 'ὰ',		'Ά' => 'ά',
		'ᾼ' => 'ᾳ',		'ι' => 'ι',		'ῂ' => 'ὴι',	'ῃ' => 'ηι',
		'ῄ' => 'ήι',	'ῆ' => 'ῆ',		'ῇ' => 'ῆι',	'Ὲ' => 'ὲ',
		'Έ' => 'έ',		'Ὴ' => 'ὴ',		'Ή' => 'ή',		'ῌ' => 'ῃ',
		'ῒ' => 'ῒ',	'ΐ' => 'ΐ',	'ῖ' => 'ῖ',		'ῗ' => 'ῗ',
		'Ῐ' => 'ῐ',		'Ῑ' => 'ῑ',		'Ὶ' => 'ὶ',		'Ί' => 'ί',
		'ῢ' => 'ῢ',	'ΰ' => 'ΰ',	'ῤ' => 'ῤ',		'ῦ' => 'ῦ',
		'ῧ' => 'ῧ',		'Ῠ' => 'ῠ',		'Ῡ' => 'ῡ',		'Ὺ' => 'ὺ',
		'Ύ' => 'ύ',		'Ῥ' => 'ῥ',		'ῲ' => 'ὼι',	'ῳ' => 'ωι',
		'ῴ' => 'ώι',	'ῶ' => 'ῶ',		'ῷ' => 'ῶι',	'Ὸ' => 'ὸ',
		'Ό' => 'ό',		'Ὼ' => 'ὼ',		'Ώ' => 'ώ',		'ῼ' => 'ῳ',
		'Ω' => 'ω',		'K' => 'k',		'Å' => 'å',		'Ⅎ' => 'ⅎ',
		'Ⅰ' => 'ⅰ',		'Ⅱ' => 'ⅱ',		'Ⅲ' => 'ⅲ',		'Ⅳ' => 'ⅳ',
		'Ⅴ' => 'ⅴ',		'Ⅵ' => 'ⅵ',		'Ⅶ' => 'ⅶ',		'Ⅷ' => 'ⅷ',
		'Ⅸ' => 'ⅸ',		'Ⅹ' => 'ⅹ',		'Ⅺ' => 'ⅺ',		'Ⅻ' => 'ⅻ',
		'Ⅼ' => 'ⅼ',		'Ⅽ' => 'ⅽ',		'Ⅾ' => 'ⅾ',		'Ⅿ' => 'ⅿ',
		'Ↄ' => 'ↄ',		'Ⓐ' => 'ⓐ',		'Ⓑ' => 'ⓑ',		'Ⓒ' => 'ⓒ',
		'Ⓓ' => 'ⓓ',		'Ⓔ' => 'ⓔ',		'Ⓕ' => 'ⓕ',		'Ⓖ' => 'ⓖ',
		'Ⓗ' => 'ⓗ',		'Ⓘ' => 'ⓘ',		'Ⓙ' => 'ⓙ',		'Ⓚ' => 'ⓚ',
		'Ⓛ' => 'ⓛ',		'Ⓜ' => 'ⓜ',		'Ⓝ' => 'ⓝ',		'Ⓞ' => 'ⓞ',
		'Ⓟ' => 'ⓟ',		'Ⓠ' => 'ⓠ',		'Ⓡ' => 'ⓡ',		'Ⓢ' => 'ⓢ',
		'Ⓣ' => 'ⓣ',		'Ⓤ' => 'ⓤ',		'Ⓥ' => 'ⓥ',		'Ⓦ' => 'ⓦ',
		'Ⓧ' => 'ⓧ',		'Ⓨ' => 'ⓨ',		'Ⓩ' => 'ⓩ',		'Ⰰ' => 'ⰰ',
		'Ⰱ' => 'ⰱ',		'Ⰲ' => 'ⰲ',		'Ⰳ' => 'ⰳ',		'Ⰴ' => 'ⰴ',
		'Ⰵ' => 'ⰵ',		'Ⰶ' => 'ⰶ',		'Ⰷ' => 'ⰷ',		'Ⰸ' => 'ⰸ',
		'Ⰹ' => 'ⰹ',		'Ⰺ' => 'ⰺ',		'Ⰻ' => 'ⰻ',		'Ⰼ' => 'ⰼ',
		'Ⰽ' => 'ⰽ',		'Ⰾ' => 'ⰾ',		'Ⰿ' => 'ⰿ',		'Ⱀ' => 'ⱀ',
		'Ⱁ' => 'ⱁ',		'Ⱂ' => 'ⱂ',		'Ⱃ' => 'ⱃ',		'Ⱄ' => 'ⱄ',
		'Ⱅ' => 'ⱅ',		'Ⱆ' => 'ⱆ',		'Ⱇ' => 'ⱇ',		'Ⱈ' => 'ⱈ',
		'Ⱉ' => 'ⱉ',		'Ⱊ' => 'ⱊ',		'Ⱋ' => 'ⱋ',		'Ⱌ' => 'ⱌ',
		'Ⱍ' => 'ⱍ',		'Ⱎ' => 'ⱎ',		'Ⱏ' => 'ⱏ',		'Ⱐ' => 'ⱐ',
		'Ⱑ' => 'ⱑ',		'Ⱒ' => 'ⱒ',		'Ⱓ' => 'ⱓ',		'Ⱔ' => 'ⱔ',
		'Ⱕ' => 'ⱕ',		'Ⱖ' => 'ⱖ',		'Ⱗ' => 'ⱗ',		'Ⱘ' => 'ⱘ',
		'Ⱙ' => 'ⱙ',		'Ⱚ' => 'ⱚ',		'Ⱛ' => 'ⱛ',		'Ⱜ' => 'ⱜ',
		'Ⱝ' => 'ⱝ',		'Ⱞ' => 'ⱞ',		'Ⱡ' => 'ⱡ',		'Ɫ' => 'ɫ',
		'Ᵽ' => 'ᵽ',		'Ɽ' => 'ɽ',		'Ⱨ' => 'ⱨ',		'Ⱪ' => 'ⱪ',
		'Ⱬ' => 'ⱬ',		'Ⱶ' => 'ⱶ',		'Ⲁ' => 'ⲁ',		'Ⲃ' => 'ⲃ',
		'Ⲅ' => 'ⲅ',		'Ⲇ' => 'ⲇ',		'Ⲉ' => 'ⲉ',		'Ⲋ' => 'ⲋ',
		'Ⲍ' => 'ⲍ',		'Ⲏ' => 'ⲏ',		'Ⲑ' => 'ⲑ',		'Ⲓ' => 'ⲓ',
		'Ⲕ' => 'ⲕ',		'Ⲗ' => 'ⲗ',		'Ⲙ' => 'ⲙ',		'Ⲛ' => 'ⲛ',
		'Ⲝ' => 'ⲝ',		'Ⲟ' => 'ⲟ',		'Ⲡ' => 'ⲡ',		'Ⲣ' => 'ⲣ',
		'Ⲥ' => 'ⲥ',		'Ⲧ' => 'ⲧ',		'Ⲩ' => 'ⲩ',		'Ⲫ' => 'ⲫ',
		'Ⲭ' => 'ⲭ',		'Ⲯ' => 'ⲯ',		'Ⲱ' => 'ⲱ',		'Ⲳ' => 'ⲳ',
		'Ⲵ' => 'ⲵ',		'Ⲷ' => 'ⲷ',		'Ⲹ' => 'ⲹ',		'Ⲻ' => 'ⲻ',
		'Ⲽ' => 'ⲽ',		'Ⲿ' => 'ⲿ',		'Ⳁ' => 'ⳁ',		'Ⳃ' => 'ⳃ',
		'Ⳅ' => 'ⳅ',		'Ⳇ' => 'ⳇ',		'Ⳉ' => 'ⳉ',		'Ⳋ' => 'ⳋ',
		'Ⳍ' => 'ⳍ',		'Ⳏ' => 'ⳏ',		'Ⳑ' => 'ⳑ',		'Ⳓ' => 'ⳓ',
		'Ⳕ' => 'ⳕ',		'Ⳗ' => 'ⳗ',		'Ⳙ' => 'ⳙ',		'Ⳛ' => 'ⳛ',
		'Ⳝ' => 'ⳝ',		'Ⳟ' => 'ⳟ',		'Ⳡ' => 'ⳡ',		'Ⳣ' => 'ⳣ',
		'ﬀ' => 'ff',	'ﬁ' => 'fi',	'ﬂ' => 'fl',	'ﬃ' => 'ffi',
		'ﬄ' => 'ffl',	'ﬅ' => 'st',	'ﬆ' => 'st',	'ﬓ' => 'մն',
		'ﬔ' => 'մե',	'ﬕ' => 'մի',	'ﬖ' => 'վն',	'ﬗ' => 'մխ',
		'Ａ' => 'ａ',		'Ｂ' => 'ｂ',		'Ｃ' => 'ｃ',		'Ｄ' => 'ｄ',
		'Ｅ' => 'ｅ',		'Ｆ' => 'ｆ',		'Ｇ' => 'ｇ',		'Ｈ' => 'ｈ',
		'Ｉ' => 'ｉ',		'Ｊ' => 'ｊ',		'Ｋ' => 'ｋ',		'Ｌ' => 'ｌ',
		'Ｍ' => 'ｍ',	'Ｎ' => 'ｎ',		'Ｏ' => 'ｏ',		'Ｐ' => 'ｐ',
		'Ｑ' => 'ｑ',		'Ｒ' => 'ｒ',		'Ｓ' => 'ｓ',		'Ｔ' => 'ｔ',
		'Ｕ' => 'ｕ',		'Ｖ' => 'ｖ',		'Ｗ' => 'ｗ',	'Ｘ' => 'ｘ',
		'Ｙ' => 'ｙ',		'Ｚ' => 'ｚ',		'𐐀' => '𐐨',	'𐐁' => '𐐩',
		'𐐂' => '𐐪',	'𐐃' => '𐐫',	'𐐄' => '𐐬',	'𐐅' => '𐐭',
		'𐐆' => '𐐮',	'𐐇' => '𐐯',	'𐐈' => '𐐰',	'𐐉' => '𐐱',
		'𐐊' => '𐐲',	'𐐋' => '𐐳',	'𐐌' => '𐐴',	'𐐍' => '𐐵',
		'𐐎' => '𐐶',	'𐐏' => '𐐷',	'𐐐' => '𐐸',	'𐐑' => '𐐹',
		'𐐒' => '𐐺',	'𐐓' => '𐐻',	'𐐔' => '𐐼',	'𐐕' => '𐐽',
		'𐐖' => '𐐾',	'𐐗' => '𐐿',	'𐐘' => '𐑀',	'𐐙' => '𐑁',
		'𐐚' => '𐑂',	'𐐛' => '𐑃',	'𐐜' => '𐑄',	'𐐝' => '𐑅',
		'𐐞' => '𐑆',	'𐐟' => '𐑇',	'𐐠' => '𐑈',	'𐐡' => '𐑉',
		'𐐢' => '𐑊',	'𐐣' => '𐑋',	'𐐤' => '𐑌',	'𐐥' => '𐑍',
		'𐑎' => '𐐦',	'𐑏' => '𐐧',
	);

	return strtr($string, $case_folding);
}

// Convert the given UTF-8 string to uppercase.
function utf8_strtoupper($string)
{
	static $case_folding = array(
		'a' => 'A',		'b' => 'B',		'c' => 'C',		'd' => 'D',
		'e' => 'E',		'f' => 'F',		'g' => 'G',		'h' => 'H',
		'i' => 'I',		'j' => 'J',		'k' => 'K',		'l' => 'L',
		'm' => 'M',		'n' => 'N',		'o' => 'O',		'p' => 'P',
		'q' => 'Q',		'r' => 'R',		's' => 'S',		't' => 'T',
		'u' => 'U',		'v' => 'V',		'w' => 'W',		'x' => 'X',
		'y' => 'Y',		'z' => 'Z',		'μ' => 'µ',		'à' => 'À',
		'á' => 'Á',		'â' => 'Â',		'ã' => 'Ã',		'ä' => 'Ä',
		'å' => 'Å',		'æ' => 'Æ',		'ç' => 'Ç',		'è' => 'È',
		'é' => 'É',		'ê' => 'Ê',		'ë' => 'Ë',		'ì' => 'Ì',
		'í' => 'Í',		'î' => 'Î',		'ï' => 'Ï',		'ð' => 'Ð',
		'ñ' => 'Ñ',		'ò' => 'Ò',		'ó' => 'Ó',		'ô' => 'Ô',
		'õ' => 'Õ',		'ö' => 'Ö',		'ø' => 'Ø',		'ù' => 'Ù',
		'ú' => 'Ú',		'û' => 'Û',		'ü' => 'Ü',		'ý' => 'Ý',
		'þ' => 'Þ',		'ss' => 'ß',	'ā' => 'Ā',		'ă' => 'Ă',
		'ą' => 'Ą',		'ć' => 'Ć',		'ĉ' => 'Ĉ',		'ċ' => 'Ċ',
		'č' => 'Č',		'ď' => 'Ď',		'đ' => 'Đ',		'ē' => 'Ē',
		'ĕ' => 'Ĕ',		'ė' => 'Ė',		'ę' => 'Ę',		'ě' => 'Ě',
		'ĝ' => 'Ĝ',		'ğ' => 'Ğ',		'ġ' => 'Ġ',		'ģ' => 'Ģ',
		'ĥ' => 'Ĥ',		'ħ' => 'Ħ',		'ĩ' => 'Ĩ',		'ī' => 'Ī',
		'ĭ' => 'Ĭ',		'į' => 'Į',		'i̇' => 'İ',		'ĳ' => 'Ĳ',
		'ĵ' => 'Ĵ',		'ķ' => 'Ķ',		'ĺ' => 'Ĺ',		'ļ' => 'Ļ',
		'ľ' => 'Ľ',		'ŀ' => 'Ŀ',		'ł' => 'Ł',		'ń' => 'Ń',
		'ņ' => 'Ņ',		'ň' => 'Ň',		'ʼn' => 'ŉ',	'ŋ' => 'Ŋ',
		'ō' => 'Ō',		'ŏ' => 'Ŏ',		'ő' => 'Ő',		'œ' => 'Œ',
		'ŕ' => 'Ŕ',		'ŗ' => 'Ŗ',		'ř' => 'Ř',		'ś' => 'Ś',
		'ŝ' => 'Ŝ',		'ş' => 'Ş',		'š' => 'Š',		'ţ' => 'Ţ',
		'ť' => 'Ť',		'ŧ' => 'Ŧ',		'ũ' => 'Ũ',		'ū' => 'Ū',
		'ŭ' => 'Ŭ',		'ů' => 'Ů',		'ű' => 'Ű',		'ų' => 'Ų',
		'ŵ' => 'Ŵ',		'ŷ' => 'Ŷ',		'ÿ' => 'Ÿ',		'ź' => 'Ź',
		'ż' => 'Ż',		'ž' => 'Ž',		's' => 'ſ',		'ɓ' => 'Ɓ',
		'ƃ' => 'Ƃ',		'ƅ' => 'Ƅ',		'ɔ' => 'Ɔ',		'ƈ' => 'Ƈ',
		'ɖ' => 'Ɖ',		'ɗ' => 'Ɗ',		'ƌ' => 'Ƌ',		'ǝ' => 'Ǝ',
		'ə' => 'Ə',		'ɛ' => 'Ɛ',		'ƒ' => 'Ƒ',		'ɠ' => 'Ɠ',
		'ɣ' => 'Ɣ',		'ɩ' => 'Ɩ',		'ɨ' => 'Ɨ',		'ƙ' => 'Ƙ',
		'ɯ' => 'Ɯ',		'ɲ' => 'Ɲ',		'ɵ' => 'Ɵ',		'ơ' => 'Ơ',
		'ƣ' => 'Ƣ',		'ƥ' => 'Ƥ',		'ʀ' => 'Ʀ',		'ƨ' => 'Ƨ',
		'ʃ' => 'Ʃ',		'ƭ' => 'Ƭ',		'ʈ' => 'Ʈ',		'ư' => 'Ư',
		'ʊ' => 'Ʊ',		'ʋ' => 'Ʋ',		'ƴ' => 'Ƴ',		'ƶ' => 'Ƶ',
		'ʒ' => 'Ʒ',		'ƹ' => 'Ƹ',		'ƽ' => 'Ƽ',		'ǆ' => 'Ǆ',
		'ǆ' => 'ǅ',		'ǉ' => 'Ǉ',		'ǉ' => 'ǈ',		'ǌ' => 'Ǌ',
		'ǌ' => 'ǋ',		'ǎ' => 'Ǎ',		'ǐ' => 'Ǐ',		'ǒ' => 'Ǒ',
		'ǔ' => 'Ǔ',		'ǖ' => 'Ǖ',		'ǘ' => 'Ǘ',		'ǚ' => 'Ǚ',
		'ǜ' => 'Ǜ',		'ǟ' => 'Ǟ',		'ǡ' => 'Ǡ',		'ǣ' => 'Ǣ',
		'ǥ' => 'Ǥ',		'ǧ' => 'Ǧ',		'ǩ' => 'Ǩ',		'ǫ' => 'Ǫ',
		'ǭ' => 'Ǭ',		'ǯ' => 'Ǯ',		'ǰ' => 'ǰ',		'ǳ' => 'Ǳ',
		'ǳ' => 'ǲ',		'ǵ' => 'Ǵ',		'ƕ' => 'Ƕ',		'ƿ' => 'Ƿ',
		'ǹ' => 'Ǹ',		'ǻ' => 'Ǻ',		'ǽ' => 'Ǽ',		'ǿ' => 'Ǿ',
		'ȁ' => 'Ȁ',		'ȃ' => 'Ȃ',		'ȅ' => 'Ȅ',		'ȇ' => 'Ȇ',
		'ȉ' => 'Ȉ',		'ȋ' => 'Ȋ',		'ȍ' => 'Ȍ',		'ȏ' => 'Ȏ',
		'ȑ' => 'Ȑ',		'ȓ' => 'Ȓ',		'ȕ' => 'Ȕ',		'ȗ' => 'Ȗ',
		'ș' => 'Ș',		'ț' => 'Ț',		'ȝ' => 'Ȝ',		'ȟ' => 'Ȟ',
		'ƞ' => 'Ƞ',		'ȣ' => 'Ȣ',		'ȥ' => 'Ȥ',		'ȧ' => 'Ȧ',
		'ȩ' => 'Ȩ',		'ȫ' => 'Ȫ',		'ȭ' => 'Ȭ',		'ȯ' => 'Ȯ',
		'ȱ' => 'Ȱ',		'ȳ' => 'Ȳ',		'ⱥ' => 'Ⱥ',		'ȼ' => 'Ȼ',
		'ƚ' => 'Ƚ',		'ⱦ' => 'Ⱦ',		'ɂ' => 'Ɂ',		'ƀ' => 'Ƀ',
		'ʉ' => 'Ʉ',		'ʌ' => 'Ʌ',		'ɇ' => 'Ɇ',		'ɉ' => 'Ɉ',
		'ɋ' => 'Ɋ',		'ɍ' => 'Ɍ',		'ɏ' => 'Ɏ',		'ι' => 'ͅ',
		'ά' => 'Ά',		'έ' => 'Έ',		'ή' => 'Ή',		'ί' => 'Ί',
		'ό' => 'Ό',		'ύ' => 'Ύ',		'ώ' => 'Ώ',		'ΐ' => 'ΐ',
		'α' => 'Α',		'β' => 'Β',		'γ' => 'Γ',		'δ' => 'Δ',
		'ε' => 'Ε',		'ζ' => 'Ζ',		'η' => 'Η',		'θ' => 'Θ',
		'ι' => 'Ι',		'κ' => 'Κ',		'λ' => 'Λ',		'μ' => 'Μ',
		'ν' => 'Ν',		'ξ' => 'Ξ',		'ο' => 'Ο',		'π' => 'Π',
		'ρ' => 'Ρ',		'σ' => 'Σ',		'τ' => 'Τ',		'υ' => 'Υ',
		'φ' => 'Φ',		'χ' => 'Χ',		'ψ' => 'Ψ',		'ω' => 'Ω',
		'ϊ' => 'Ϊ',		'ϋ' => 'Ϋ',		'ΰ' => 'ΰ',	'σ' => 'ς',
		'β' => 'ϐ',		'θ' => 'ϑ',		'φ' => 'ϕ',		'π' => 'ϖ',
		'ϙ' => 'Ϙ',		'ϛ' => 'Ϛ',		'ϝ' => 'Ϝ',		'ϟ' => 'Ϟ',
		'ϡ' => 'Ϡ',		'ϣ' => 'Ϣ',		'ϥ' => 'Ϥ',		'ϧ' => 'Ϧ',
		'ϩ' => 'Ϩ',		'ϫ' => 'Ϫ',		'ϭ' => 'Ϭ',		'ϯ' => 'Ϯ',
		'κ' => 'ϰ',		'ρ' => 'ϱ',		'θ' => 'ϴ',		'ε' => 'ϵ',
		'ϸ' => 'Ϸ',		'ϲ' => 'Ϲ',		'ϻ' => 'Ϻ',		'ͻ' => 'Ͻ',
		'ͼ' => 'Ͼ',		'ͽ' => 'Ͽ',		'ѐ' => 'Ѐ',		'ё' => 'Ё',
		'ђ' => 'Ђ',		'ѓ' => 'Ѓ',		'є' => 'Є',		'ѕ' => 'Ѕ',
		'і' => 'І',		'ї' => 'Ї',		'ј' => 'Ј',		'љ' => 'Љ',
		'њ' => 'Њ',		'ћ' => 'Ћ',		'ќ' => 'Ќ',		'ѝ' => 'Ѝ',
		'ў' => 'Ў',		'џ' => 'Џ',		'а' => 'А',		'б' => 'Б',
		'в' => 'В',		'г' => 'Г',		'д' => 'Д',		'е' => 'Е',
		'ж' => 'Ж',		'з' => 'З',		'и' => 'И',		'й' => 'Й',
		'к' => 'К',		'л' => 'Л',		'м' => 'М',		'н' => 'Н',
		'о' => 'О',		'п' => 'П',		'р' => 'Р',		'с' => 'С',
		'т' => 'Т',		'у' => 'У',		'ф' => 'Ф',		'х' => 'Х',
		'ц' => 'Ц',		'ч' => 'Ч',		'ш' => 'Ш',		'щ' => 'Щ',
		'ъ' => 'Ъ',		'ы' => 'Ы',		'ь' => 'Ь',		'э' => 'Э',
		'ю' => 'Ю',		'я' => 'Я',		'ѡ' => 'Ѡ',		'ѣ' => 'Ѣ',
		'ѥ' => 'Ѥ',		'ѧ' => 'Ѧ',		'ѩ' => 'Ѩ',		'ѫ' => 'Ѫ',
		'ѭ' => 'Ѭ',		'ѯ' => 'Ѯ',		'ѱ' => 'Ѱ',		'ѳ' => 'Ѳ',
		'ѵ' => 'Ѵ',		'ѷ' => 'Ѷ',		'ѹ' => 'Ѹ',		'ѻ' => 'Ѻ',
		'ѽ' => 'Ѽ',		'ѿ' => 'Ѿ',		'ҁ' => 'Ҁ',		'ҋ' => 'Ҋ',
		'ҍ' => 'Ҍ',		'ҏ' => 'Ҏ',		'ґ' => 'Ґ',		'ғ' => 'Ғ',
		'ҕ' => 'Ҕ',		'җ' => 'Җ',		'ҙ' => 'Ҙ',		'қ' => 'Қ',
		'ҝ' => 'Ҝ',		'ҟ' => 'Ҟ',		'ҡ' => 'Ҡ',		'ң' => 'Ң',
		'ҥ' => 'Ҥ',		'ҧ' => 'Ҧ',		'ҩ' => 'Ҩ',		'ҫ' => 'Ҫ',
		'ҭ' => 'Ҭ',		'ү' => 'Ү',		'ұ' => 'Ұ',		'ҳ' => 'Ҳ',
		'ҵ' => 'Ҵ',		'ҷ' => 'Ҷ',		'ҹ' => 'Ҹ',		'һ' => 'Һ',
		'ҽ' => 'Ҽ',		'ҿ' => 'Ҿ',		'ӏ' => 'Ӏ',		'ӂ' => 'Ӂ',
		'ӄ' => 'Ӄ',		'ӆ' => 'Ӆ',		'ӈ' => 'Ӈ',		'ӊ' => 'Ӊ',
		'ӌ' => 'Ӌ',		'ӎ' => 'Ӎ',		'ӑ' => 'Ӑ',		'ӓ' => 'Ӓ',
		'ӕ' => 'Ӕ',		'ӗ' => 'Ӗ',		'ә' => 'Ә',		'ӛ' => 'Ӛ',
		'ӝ' => 'Ӝ',		'ӟ' => 'Ӟ',		'ӡ' => 'Ӡ',		'ӣ' => 'Ӣ',
		'ӥ' => 'Ӥ',		'ӧ' => 'Ӧ',		'ө' => 'Ө',		'ӫ' => 'Ӫ',
		'ӭ' => 'Ӭ',		'ӯ' => 'Ӯ',		'ӱ' => 'Ӱ',		'ӳ' => 'Ӳ',
		'ӵ' => 'Ӵ',		'ӷ' => 'Ӷ',		'ӹ' => 'Ӹ',		'ӻ' => 'Ӻ',
		'ӽ' => 'Ӽ',		'ӿ' => 'Ӿ',		'ԁ' => 'Ԁ',		'ԃ' => 'Ԃ',
		'ԅ' => 'Ԅ',		'ԇ' => 'Ԇ',		'ԉ' => 'Ԉ',		'ԋ' => 'Ԋ',
		'ԍ' => 'Ԍ',		'ԏ' => 'Ԏ',		'ԑ' => 'Ԑ',		'ԓ' => 'Ԓ',
		'ա' => 'Ա',		'բ' => 'Բ',		'գ' => 'Գ',		'դ' => 'Դ',
		'ե' => 'Ե',		'զ' => 'Զ',		'է' => 'Է',		'ը' => 'Ը',
		'թ' => 'Թ',		'ժ' => 'Ժ',		'ի' => 'Ի',		'լ' => 'Լ',
		'խ' => 'Խ',		'ծ' => 'Ծ',		'կ' => 'Կ',		'հ' => 'Հ',
		'ձ' => 'Ձ',		'ղ' => 'Ղ',		'ճ' => 'Ճ',		'մ' => 'Մ',
		'յ' => 'Յ',		'ն' => 'Ն',		'շ' => 'Շ',		'ո' => 'Ո',
		'չ' => 'Չ',		'պ' => 'Պ',		'ջ' => 'Ջ',		'ռ' => 'Ռ',
		'ս' => 'Ս',		'վ' => 'Վ',		'տ' => 'Տ',		'ր' => 'Ր',
		'ց' => 'Ց',		'ւ' => 'Ւ',		'փ' => 'Փ',		'ք' => 'Ք',
		'օ' => 'Օ',		'ֆ' => 'Ֆ',		'եւ' => 'և',		'ⴀ' => 'Ⴀ',
		'ⴁ' => 'Ⴁ',		'ⴂ' => 'Ⴂ',		'ⴃ' => 'Ⴃ',		'ⴄ' => 'Ⴄ',
		'ⴅ' => 'Ⴅ',		'ⴆ' => 'Ⴆ',		'ⴇ' => 'Ⴇ',		'ⴈ' => 'Ⴈ',
		'ⴉ' => 'Ⴉ',		'ⴊ' => 'Ⴊ',		'ⴋ' => 'Ⴋ',		'ⴌ' => 'Ⴌ',
		'ⴍ' => 'Ⴍ',		'ⴎ' => 'Ⴎ',		'ⴏ' => 'Ⴏ',		'ⴐ' => 'Ⴐ',
		'ⴑ' => 'Ⴑ',		'ⴒ' => 'Ⴒ',		'ⴓ' => 'Ⴓ',		'ⴔ' => 'Ⴔ',
		'ⴕ' => 'Ⴕ',		'ⴖ' => 'Ⴖ',		'ⴗ' => 'Ⴗ',		'ⴘ' => 'Ⴘ',
		'ⴙ' => 'Ⴙ',		'ⴚ' => 'Ⴚ',		'ⴛ' => 'Ⴛ',		'ⴜ' => 'Ⴜ',
		'ⴝ' => 'Ⴝ',		'ⴞ' => 'Ⴞ',		'ⴟ' => 'Ⴟ',		'ⴠ' => 'Ⴠ',
		'ⴡ' => 'Ⴡ',		'ⴢ' => 'Ⴢ',		'ⴣ' => 'Ⴣ',		'ⴤ' => 'Ⴤ',
		'ⴥ' => 'Ⴥ',		'ḁ' => 'Ḁ',		'ḃ' => 'Ḃ',		'ḅ' => 'Ḅ',
		'ḇ' => 'Ḇ',		'ḉ' => 'Ḉ',		'ḋ' => 'Ḋ',		'ḍ' => 'Ḍ',
		'ḏ' => 'Ḏ',		'ḑ' => 'Ḑ',		'ḓ' => 'Ḓ',		'ḕ' => 'Ḕ',
		'ḗ' => 'Ḗ',		'ḙ' => 'Ḙ',		'ḛ' => 'Ḛ',		'ḝ' => 'Ḝ',
		'ḟ' => 'Ḟ',		'ḡ' => 'Ḡ',		'ḣ' => 'Ḣ',		'ḥ' => 'Ḥ',
		'ḧ' => 'Ḧ',		'ḩ' => 'Ḩ',		'ḫ' => 'Ḫ',		'ḭ' => 'Ḭ',
		'ḯ' => 'Ḯ',		'ḱ' => 'Ḱ',		'ḳ' => 'Ḳ',		'ḵ' => 'Ḵ',
		'ḷ' => 'Ḷ',		'ḹ' => 'Ḹ',		'ḻ' => 'Ḻ',		'ḽ' => 'Ḽ',
		'ḿ' => 'Ḿ',		'ṁ' => 'Ṁ',		'ṃ' => 'Ṃ',		'ṅ' => 'Ṅ',
		'ṇ' => 'Ṇ',		'ṉ' => 'Ṉ',		'ṋ' => 'Ṋ',		'ṍ' => 'Ṍ',
		'ṏ' => 'Ṏ',		'ṑ' => 'Ṑ',		'ṓ' => 'Ṓ',		'ṕ' => 'Ṕ',
		'ṗ' => 'Ṗ',		'ṙ' => 'Ṙ',		'ṛ' => 'Ṛ',		'ṝ' => 'Ṝ',
		'ṟ' => 'Ṟ',		'ṡ' => 'Ṡ',		'ṣ' => 'Ṣ',		'ṥ' => 'Ṥ',
		'ṧ' => 'Ṧ',		'ṩ' => 'Ṩ',		'ṫ' => 'Ṫ',		'ṭ' => 'Ṭ',
		'ṯ' => 'Ṯ',		'ṱ' => 'Ṱ',		'ṳ' => 'Ṳ',		'ṵ' => 'Ṵ',
		'ṷ' => 'Ṷ',		'ṹ' => 'Ṹ',		'ṻ' => 'Ṻ',		'ṽ' => 'Ṽ',
		'ṿ' => 'Ṿ',		'ẁ' => 'Ẁ',		'ẃ' => 'Ẃ',		'ẅ' => 'Ẅ',
		'ẇ' => 'Ẇ',		'ẉ' => 'Ẉ',		'ẋ' => 'Ẋ',		'ẍ' => 'Ẍ',
		'ẏ' => 'Ẏ',		'ẑ' => 'Ẑ',		'ẓ' => 'Ẓ',		'ẕ' => 'Ẕ',
		'ẖ' => 'ẖ',		'ẗ' => 'ẗ',		'ẘ' => 'ẘ',		'ẙ' => 'ẙ',
		'aʾ' => 'ẚ',	'ṡ' => 'ẛ',		'ạ' => 'Ạ',		'ả' => 'Ả',
		'ấ' => 'Ấ',		'ầ' => 'Ầ',		'ẩ' => 'Ẩ',		'ẫ' => 'Ẫ',
		'ậ' => 'Ậ',		'ắ' => 'Ắ',		'ằ' => 'Ằ',		'ẳ' => 'Ẳ',
		'ẵ' => 'Ẵ',		'ặ' => 'Ặ',		'ẹ' => 'Ẹ',		'ẻ' => 'Ẻ',
		'ẽ' => 'Ẽ',		'ế' => 'Ế',		'ề' => 'Ề',		'ể' => 'Ể',
		'ễ' => 'Ễ',		'ệ' => 'Ệ',		'ỉ' => 'Ỉ',		'ị' => 'Ị',
		'ọ' => 'Ọ',		'ỏ' => 'Ỏ',		'ố' => 'Ố',		'ồ' => 'Ồ',
		'ổ' => 'Ổ',		'ỗ' => 'Ỗ',		'ộ' => 'Ộ',		'ớ' => 'Ớ',
		'ờ' => 'Ờ',		'ở' => 'Ở',		'ỡ' => 'Ỡ',		'ợ' => 'Ợ',
		'ụ' => 'Ụ',		'ủ' => 'Ủ',		'ứ' => 'Ứ',		'ừ' => 'Ừ',
		'ử' => 'Ử',		'ữ' => 'Ữ',		'ự' => 'Ự',		'ỳ' => 'Ỳ',
		'ỵ' => 'Ỵ',		'ỷ' => 'Ỷ',		'ỹ' => 'Ỹ',		'ἀ' => 'Ἀ',
		'ἁ' => 'Ἁ',		'ἂ' => 'Ἂ',		'ἃ' => 'Ἃ',		'ἄ' => 'Ἄ',
		'ἅ' => 'Ἅ',		'ἆ' => 'Ἆ',		'ἇ' => 'Ἇ',		'ἐ' => 'Ἐ',
		'ἑ' => 'Ἑ',		'ἒ' => 'Ἒ',		'ἓ' => 'Ἓ',		'ἔ' => 'Ἔ',
		'ἕ' => 'Ἕ',		'ἠ' => 'Ἠ',		'ἡ' => 'Ἡ',		'ἢ' => 'Ἢ',
		'ἣ' => 'Ἣ',		'ἤ' => 'Ἤ',		'ἥ' => 'Ἥ',		'ἦ' => 'Ἦ',
		'ἧ' => 'Ἧ',		'ἰ' => 'Ἰ',		'ἱ' => 'Ἱ',		'ἲ' => 'Ἲ',
		'ἳ' => 'Ἳ',		'ἴ' => 'Ἴ',		'ἵ' => 'Ἵ',		'ἶ' => 'Ἶ',
		'ἷ' => 'Ἷ',		'ὀ' => 'Ὀ',		'ὁ' => 'Ὁ',		'ὂ' => 'Ὂ',
		'ὃ' => 'Ὃ',		'ὄ' => 'Ὄ',		'ὅ' => 'Ὅ',		'ὐ' => 'ὐ',
		'ὒ' => 'ὒ',	'ὔ' => 'ὔ',	'ὖ' => 'ὖ',		'ὑ' => 'Ὑ',
		'ὓ' => 'Ὓ',		'ὕ' => 'Ὕ',		'ὗ' => 'Ὗ',		'ὠ' => 'Ὠ',
		'ὡ' => 'Ὡ',		'ὢ' => 'Ὢ',		'ὣ' => 'Ὣ',		'ὤ' => 'Ὤ',
		'ὥ' => 'Ὥ',		'ὦ' => 'Ὦ',		'ὧ' => 'Ὧ',		'ἀι' => 'ᾀ',
		'ἁι' => 'ᾁ',	'ἂι' => 'ᾂ',	'ἃι' => 'ᾃ',	'ἄι' => 'ᾄ',
		'ἅι' => 'ᾅ',	'ἆι' => 'ᾆ',	'ἇι' => 'ᾇ',	'ᾀ' => 'ᾈ',
		'ᾁ' => 'ᾉ',		'ᾂ' => 'ᾊ',		'ᾃ' => 'ᾋ',		'ᾄ' => 'ᾌ',
		'ᾅ' => 'ᾍ',		'ᾆ' => 'ᾎ',		'ᾇ' => 'ᾏ',		'ἠι' => 'ᾐ',
		'ἡι' => 'ᾑ',	'ἢι' => 'ᾒ',	'ἣι' => 'ᾓ',	'ἤι' => 'ᾔ',
		'ἥι' => 'ᾕ',	'ἦι' => 'ᾖ',	'ἧι' => 'ᾗ',	'ᾐ' => 'ᾘ',
		'ᾑ' => 'ᾙ',		'ᾒ' => 'ᾚ',		'ᾓ' => 'ᾛ',		'ᾔ' => 'ᾜ',
		'ᾕ' => 'ᾝ',		'ᾖ' => 'ᾞ',		'ᾗ' => 'ᾟ',		'ὠι' => 'ᾠ',
		'ὡι' => 'ᾡ',	'ὢι' => 'ᾢ',	'ὣι' => 'ᾣ',	'ὤι' => 'ᾤ',
		'ὥι' => 'ᾥ',	'ὦι' => 'ᾦ',	'ὧι' => 'ᾧ',	'ᾠ' => 'ᾨ',
		'ᾡ' => 'ᾩ',		'ᾢ' => 'ᾪ',		'ᾣ' => 'ᾫ',		'ᾤ' => 'ᾬ',
		'ᾥ' => 'ᾭ',		'ᾦ' => 'ᾮ',		'ᾧ' => 'ᾯ',		'ὰι' => 'ᾲ',
		'αι' => 'ᾳ',	'άι' => 'ᾴ',	'ᾶ' => 'ᾶ',		'ᾶι' => 'ᾷ',
		'ᾰ' => 'Ᾰ',		'ᾱ' => 'Ᾱ',		'ὰ' => 'Ὰ',		'ά' => 'Ά',
		'ᾳ' => 'ᾼ',		'ι' => 'ι',		'ὴι' => 'ῂ',	'ηι' => 'ῃ',
		'ήι' => 'ῄ',	'ῆ' => 'ῆ',		'ῆι' => 'ῇ',	'ὲ' => 'Ὲ',
		'έ' => 'Έ',		'ὴ' => 'Ὴ',		'ή' => 'Ή',		'ῃ' => 'ῌ',
		'ῒ' => 'ῒ',	'ΐ' => 'ΐ',	'ῖ' => 'ῖ',		'ῗ' => 'ῗ',
		'ῐ' => 'Ῐ',		'ῑ' => 'Ῑ',		'ὶ' => 'Ὶ',		'ί' => 'Ί',
		'ῢ' => 'ῢ',	'ΰ' => 'ΰ',	'ῤ' => 'ῤ',		'ῦ' => 'ῦ',
		'ῧ' => 'ῧ',		'ῠ' => 'Ῠ',		'ῡ' => 'Ῡ',		'ὺ' => 'Ὺ',
		'ύ' => 'Ύ',		'ῥ' => 'Ῥ',		'ὼι' => 'ῲ',	'ωι' => 'ῳ',
		'ώι' => 'ῴ',	'ῶ' => 'ῶ',		'ῶι' => 'ῷ',	'ὸ' => 'Ὸ',
		'ό' => 'Ό',		'ὼ' => 'Ὼ',		'ώ' => 'Ώ',		'ῳ' => 'ῼ',
		'ω' => 'Ω',		'k' => 'K',		'å' => 'Å',		'ⅎ' => 'Ⅎ',
		'ⅰ' => 'Ⅰ',		'ⅱ' => 'Ⅱ',		'ⅲ' => 'Ⅲ',		'ⅳ' => 'Ⅳ',
		'ⅴ' => 'Ⅴ',		'ⅵ' => 'Ⅵ',		'ⅶ' => 'Ⅶ',		'ⅷ' => 'Ⅷ',
		'ⅸ' => 'Ⅸ',		'ⅹ' => 'Ⅹ',		'ⅺ' => 'Ⅺ',		'ⅻ' => 'Ⅻ',
		'ⅼ' => 'Ⅼ',		'ⅽ' => 'Ⅽ',		'ⅾ' => 'Ⅾ',		'ⅿ' => 'Ⅿ',
		'ↄ' => 'Ↄ',		'ⓐ' => 'Ⓐ',		'ⓑ' => 'Ⓑ',		'ⓒ' => 'Ⓒ',
		'ⓓ' => 'Ⓓ',		'ⓔ' => 'Ⓔ',		'ⓕ' => 'Ⓕ',		'ⓖ' => 'Ⓖ',
		'ⓗ' => 'Ⓗ',		'ⓘ' => 'Ⓘ',		'ⓙ' => 'Ⓙ',		'ⓚ' => 'Ⓚ',
		'ⓛ' => 'Ⓛ',		'ⓜ' => 'Ⓜ',		'ⓝ' => 'Ⓝ',		'ⓞ' => 'Ⓞ',
		'ⓟ' => 'Ⓟ',		'ⓠ' => 'Ⓠ',		'ⓡ' => 'Ⓡ',		'ⓢ' => 'Ⓢ',
		'ⓣ' => 'Ⓣ',		'ⓤ' => 'Ⓤ',		'ⓥ' => 'Ⓥ',		'ⓦ' => 'Ⓦ',
		'ⓧ' => 'Ⓧ',		'ⓨ' => 'Ⓨ',		'ⓩ' => 'Ⓩ',		'ⰰ' => 'Ⰰ',
		'ⰱ' => 'Ⰱ',		'ⰲ' => 'Ⰲ',		'ⰳ' => 'Ⰳ',		'ⰴ' => 'Ⰴ',
		'ⰵ' => 'Ⰵ',		'ⰶ' => 'Ⰶ',		'ⰷ' => 'Ⰷ',		'ⰸ' => 'Ⰸ',
		'ⰹ' => 'Ⰹ',		'ⰺ' => 'Ⰺ',		'ⰻ' => 'Ⰻ',		'ⰼ' => 'Ⰼ',
		'ⰽ' => 'Ⰽ',		'ⰾ' => 'Ⰾ',		'ⰿ' => 'Ⰿ',		'ⱀ' => 'Ⱀ',
		'ⱁ' => 'Ⱁ',		'ⱂ' => 'Ⱂ',		'ⱃ' => 'Ⱃ',		'ⱄ' => 'Ⱄ',
		'ⱅ' => 'Ⱅ',		'ⱆ' => 'Ⱆ',		'ⱇ' => 'Ⱇ',		'ⱈ' => 'Ⱈ',
		'ⱉ' => 'Ⱉ',		'ⱊ' => 'Ⱊ',		'ⱋ' => 'Ⱋ',		'ⱌ' => 'Ⱌ',
		'ⱍ' => 'Ⱍ',		'ⱎ' => 'Ⱎ',		'ⱏ' => 'Ⱏ',		'ⱐ' => 'Ⱐ',
		'ⱑ' => 'Ⱑ',		'ⱒ' => 'Ⱒ',		'ⱓ' => 'Ⱓ',		'ⱔ' => 'Ⱔ',
		'ⱕ' => 'Ⱕ',		'ⱖ' => 'Ⱖ',		'ⱗ' => 'Ⱗ',		'ⱘ' => 'Ⱘ',
		'ⱙ' => 'Ⱙ',		'ⱚ' => 'Ⱚ',		'ⱛ' => 'Ⱛ',		'ⱜ' => 'Ⱜ',
		'ⱝ' => 'Ⱝ',		'ⱞ' => 'Ⱞ',		'ⱡ' => 'Ⱡ',		'ɫ' => 'Ɫ',
		'ᵽ' => 'Ᵽ',		'ɽ' => 'Ɽ',		'ⱨ' => 'Ⱨ',		'ⱪ' => 'Ⱪ',
		'ⱬ' => 'Ⱬ',		'ⱶ' => 'Ⱶ',		'ⲁ' => 'Ⲁ',		'ⲃ' => 'Ⲃ',
		'ⲅ' => 'Ⲅ',		'ⲇ' => 'Ⲇ',		'ⲉ' => 'Ⲉ',		'ⲋ' => 'Ⲋ',
		'ⲍ' => 'Ⲍ',		'ⲏ' => 'Ⲏ',		'ⲑ' => 'Ⲑ',		'ⲓ' => 'Ⲓ',
		'ⲕ' => 'Ⲕ',		'ⲗ' => 'Ⲗ',		'ⲙ' => 'Ⲙ',		'ⲛ' => 'Ⲛ',
		'ⲝ' => 'Ⲝ',		'ⲟ' => 'Ⲟ',		'ⲡ' => 'Ⲡ',		'ⲣ' => 'Ⲣ',
		'ⲥ' => 'Ⲥ',		'ⲧ' => 'Ⲧ',		'ⲩ' => 'Ⲩ',		'ⲫ' => 'Ⲫ',
		'ⲭ' => 'Ⲭ',		'ⲯ' => 'Ⲯ',		'ⲱ' => 'Ⲱ',		'ⲳ' => 'Ⲳ',
		'ⲵ' => 'Ⲵ',		'ⲷ' => 'Ⲷ',		'ⲹ' => 'Ⲹ',		'ⲻ' => 'Ⲻ',
		'ⲽ' => 'Ⲽ',		'ⲿ' => 'Ⲿ',		'ⳁ' => 'Ⳁ',		'ⳃ' => 'Ⳃ',
		'ⳅ' => 'Ⳅ',		'ⳇ' => 'Ⳇ',		'ⳉ' => 'Ⳉ',		'ⳋ' => 'Ⳋ',
		'ⳍ' => 'Ⳍ',		'ⳏ' => 'Ⳏ',		'ⳑ' => 'Ⳑ',		'ⳓ' => 'Ⳓ',
		'ⳕ' => 'Ⳕ',		'ⳗ' => 'Ⳗ',		'ⳙ' => 'Ⳙ',		'ⳛ' => 'Ⳛ',
		'ⳝ' => 'Ⳝ',		'ⳟ' => 'Ⳟ',		'ⳡ' => 'Ⳡ',		'ⳣ' => 'Ⳣ',
		'ff' => 'ﬀ',	'fi' => 'ﬁ',	'fl' => 'ﬂ',	'ffi' => 'ﬃ',
		'ffl' => 'ﬄ',	'st' => 'ﬅ',	'st' => 'ﬆ',	'մն' => 'ﬓ',
		'մե' => 'ﬔ',	'մի' => 'ﬕ',	'վն' => 'ﬖ',	'մխ' => 'ﬗ',
		'ａ' => 'Ａ',		'ｂ' => 'Ｂ',		'ｃ' => 'Ｃ',		'ｄ' => 'Ｄ',
		'ｅ' => 'Ｅ',		'ｆ' => 'Ｆ',		'ｇ' => 'Ｇ',		'ｈ' => 'Ｈ',
		'ｉ' => 'Ｉ',		'ｊ' => 'Ｊ',		'ｋ' => 'Ｋ',		'ｌ' => 'Ｌ',
		'ｍ' => 'Ｍ',	'ｎ' => 'Ｎ',		'ｏ' => 'Ｏ',		'ｐ' => 'Ｐ',
		'ｑ' => 'Ｑ',		'ｒ' => 'Ｒ',		'ｓ' => 'Ｓ',		'ｔ' => 'Ｔ',
		'ｕ' => 'Ｕ',		'ｖ' => 'Ｖ',		'ｗ' => 'Ｗ',	'ｘ' => 'Ｘ',
		'ｙ' => 'Ｙ',		'ｚ' => 'Ｚ',		'𐐨' => '𐐀',	'𐐩' => '𐐁',
		'𐐪' => '𐐂',	'𐐫' => '𐐃',	'𐐬' => '𐐄',	'𐐭' => '𐐅',
		'𐐮' => '𐐆',	'𐐯' => '𐐇',	'𐐰' => '𐐈',	'𐐱' => '𐐉',
		'𐐲' => '𐐊',	'𐐳' => '𐐋',	'𐐴' => '𐐌',	'𐐵' => '𐐍',
		'𐐶' => '𐐎',	'𐐷' => '𐐏',	'𐐸' => '𐐐',	'𐐹' => '𐐑',
		'𐐺' => '𐐒',	'𐐻' => '𐐓',	'𐐼' => '𐐔',	'𐐽' => '𐐕',
		'𐐾' => '𐐖',	'𐐿' => '𐐗',	'𐑀' => '𐐘',	'𐑁' => '𐐙',
		'𐑂' => '𐐚',	'𐑃' => '𐐛',	'𐑄' => '𐐜',	'𐑅' => '𐐝',
		'𐑆' => '𐐞',	'𐑇' => '𐐟',	'𐑈' => '𐐠',	'𐑉' => '𐐡',
		'𐑊' => '𐐢',	'𐑋' => '𐐣',	'𐑌' => '𐐤',	'𐑍' => '𐐥',
		'𐐦' => '𐑎',	'𐐧' => '𐑏',
	);

	return strtr($string, $case_folding);
}

// Fixes corrupted serialized strings after a character set conversion.
function fix_serialized_columns()
{
	global $smcFunc;

	$request = $smcFunc['db_query']('', '
		SELECT id_action, extra
		FROM {db_prefix}log_actions
		WHERE action IN ({string:remove}, {string:delete})',
		array(
			'remove' => 'remove',
			'delete' => 'delete',
		)
	);
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		if (@unserialize($row['extra']) === false && preg_match('~^(a:3:{s:5:"topic";i:\d+;s:7:"subject";s:)(\d+):"(.+)"(;s:6:"member";s:5:"\d+";})$~', $row['extra'], $matches) === 1)
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}log_actions
				SET extra = {string:extra}
				WHERE id_action = {int:current_action}',
				array(
					'current_action' => $row['id_action'],
					'extra' => $matches[1] . strlen($matches[3]) . ':"' . $matches[3] . '"' . $matches[4],
				)
			);
	}
	$smcFunc['db_free_result']($request);

	// Refresh some cached data.
	updateSettings(array(
		'memberlist_updated' => time(),
	));

}

?>