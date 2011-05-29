<edit file>
$boarddir/index.php
</edit file>

<search for>
* =============================================================================== *
* Software Version:           SMF 1.1.13                                          *
</search for>

<replace>
* =============================================================================== *
* Software Version:           SMF 1.1.14                                          *
</replace>

<search for>
$forum_version = 'SMF 1.1.13';
</search for>

<replace>
$forum_version = 'SMF 1.1.14';
</replace>


<edit file>
$sourcedir/Subs-Members.php
</edit file>

<search for>
* =============================================================================== *
* Software Version:           SMF 1.1.9                                           *
</search for>

<replace>
* =============================================================================== *
* Software Version:           SMF 1.1.14                                          *
</replace>

<search for>
	global $user_info, $modSettings, $db_prefix, $func;

	$checkName = $func['strtolower']($name);
</search for>

<replace>
	global $user_info, $modSettings, $db_prefix, $func, $context;

	// No cheating with entities please.
	$replaceEntities = create_function('$string', '
		$num = substr($string, 0, 1) === \'x\' ? hexdec(substr($string, 1)) : (int) $string;' . (empty($context['utf8']) ? '
		return $num < 0x20 ? \'\' : ($num < 0x80 ? chr($num) : \'&#\' . $string . \';\');' : '
		return $num < 0x20 || $num > 0x10FFFF || ($num >= 0xD800 && $num <= 0xDFFF) ? \'\' : ($num < 0x80 ? chr($num) : ($num < 0x800 ? chr(192 | $num >> 6) . chr(128 | $num & 63) : ($num < 0x10000 ? chr(224 | $num >> 12) . chr(128 | $num >> 6 & 63) . chr(128 | $num & 63) : chr(240 | $num >> 18) . chr(128 | $num >> 12 & 63) . chr(128 | $num >> 6 & 63) . chr(128 | $num & 63))));')
	);

	$name = preg_replace('~(&#(\d{1,7}|x[0-9a-fA-F]{1,6});)~e', '$replaceEntities(\'\\2\')', $name);
	$checkName = $func['strtolower']($name);
</replace>

<search for>
			// Case sensitive name?
			$reservedCheck = empty($modSettings['reserveCase']) ? $func['strtolower']($reserved) : $reserved;
</search for>

<replace>
			// The admin might've used entities too, level the playing field.
			$reservedCheck = preg_replace('~(&#(\d{1,7}|x[0-9a-fA-F]{1,6});)~e', '$replaceEntities(\'\\2\')', $reserved);

			// Case sensitive name?
			if (empty($modSettings['reserveCase']))
				$reservedCheck = $func['strtolower']($reservedCheck);

</replace>