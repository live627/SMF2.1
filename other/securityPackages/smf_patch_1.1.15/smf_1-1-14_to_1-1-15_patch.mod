<edit file>
$boarddir/index.php
</edit file>

<search for>
* =============================================================================== *
* Software Version:           SMF 1.1.14                                          *
</search for>

<replace>
* =============================================================================== *
* Software Version:           SMF 1.1.15                                          *
</replace>

<search for>
$forum_version = 'SMF 1.1.14';
</search for>

<replace>
$forum_version = 'SMF 1.1.15';
</replace>


<edit file>
$sourcedir/Subs-Members.php
</edit file>

<search for>
* =============================================================================== *
* Software Version:           SMF 1.1.14                                          *
</search for>

<replace>
* =============================================================================== *
* Software Version:           SMF 1.1.15                                          *
</replace>

<search for>
create_function('$string', '
		$num = substr($string, 0, 1) === \'x\' ? hexdec(substr($string, 1)) : (int) $string;' . (empty($context['utf8']) ? '
		return $num < 0x20 ? \'\' : ($num < 0x80 ? chr($num) : \'&#\' . $string . \';\');' : '
</search for>

<replace>
create_function('$string', '
		$num = substr($string, 0, 1) === \'x\' ? hexdec(substr($string, 1)) : (int) $string;' .
		' if ($num === 0x202E || $num === 0x202D) return \'\'; if (in_array($num, array(0x22, 0x26, 0x27, 0x3C, 0x3E))) return \'&#\' . $num . \';\';' .
		(empty($context['utf8']) ? 'return $num < 0x20 ? \'\' : ($num < 0x80 ? chr($num) : \'&#\' . $string . \';\');' : '
</replace>


<edit file>
$sourcedir/Load.php
</edit file>

<search for>
* =============================================================================== *
* Software Version:           SMF 1.1.11                                          *
</search for>

<replace>
* =============================================================================== *
* Software Version:           SMF 1.1.15                                          *
</replace>

<search for>
'entity_fix' => create_function('$string', '
			$num = substr($string, 0, 1) === \'x\' ? hexdec(substr($string, 1)) : (int) $string;
			return $num < 0x20 || $num > 0x10FFFF || ($num >= 0xD800 && $num <= 0xDFFF) ? \'\' : \'&#\' . $num . \';\';'),
</search for>

<replace>
'entity_fix' => create_function('$string', '
			$num = substr($string, 0, 1) === \'x\' ? hexdec(substr($string, 1)) : (int) $string;
			return $num < 0x20 || $num === 0x202E || $num === 0x202D || $num > 0x10FFFF || ($num >= 0xD800 && $num <= 0xDFFF) ? \'\' : \'&#\' . $num . \';\';'),
</replace>


<edit file>
$sourcedir/Subs.php
</edit file>

<search for>
* Software Version:           SMF 1.1.13                                          *
</search for>

<replace>
* Software Version:           SMF 1.1.15                                          *
</replace>

<search for>
	$forum_copyright = str_replace(array('Lewis Media', 'href="http://www.lewismedia.com/"', '2001-'), array('Simple Machines LLC', 'href="http://www.simplemachines.org/about/copyright.php" title="Free Forum Software"', ''), $forum_copyright);
</search for>

<replace>
	$forum_copyright = str_replace(array('Lewis Media', 'Simple Machines LLC', 'href="http://www.lewismedia.com/"', '2001-'), array('Simple Machines', 'Simple Machines', 'href="http://www.simplemachines.org/about/copyright.php" title="Free Forum Software"', ''), $forum_copyright);
</replace>

<search for>
	elseif (isset($modSettings['copyright_key']) && sha1($modSettings['copyright_key'] . 'banjo') == '1d01885ece7a9355bdeb22ed107f0ffa8c323026'){$found = true; return;}elseif ((strpos($forum_copyright, '<a href="http://www.simplemachines.org/" title="Simple Machines Forum" target="_blank">Powered by SMF') !== false || strpos($forum_copyright, '<a href="http://www.simplemachines.org/" onclick="this.href += \'referer.php?forum=' . urlencode($context['forum_name'] . '|' . $boardurl . '|' . $forum_version) . '\';" target="_blank">SMF') !== false || strpos($forum_copyright, '<a href="http://www.simplemachines.org/" target="_blank">SMF') !== false || strpos($forum_copyright, '<a href="http://www.simplemachines.org/" title="Simple Machines Forum" target="_blank">SMF') !== false)&&((strpos($forum_copyright, '<a href="http://www.simplemachines.org/about/copyright.php" title="Free Forum Software" target="_blank">SMF &copy;') !== false && (strpos($forum_copyright, 'Lewis Media</a>') !== false || strpos($forum_copyright, 'Simple Machines LLC</a>') !== false)) || strpos($forum_copyright, '<a href="http://www.lewismedia.com/">Lewis Media</a>') !== false || strpos($forum_copyright, '<a href="http://www.lewismedia.com/" target="_blank">Lewis Media</a>') !== false || (strpos($forum_copyright, '<a href="http://www.simplemachines.org/about/copyright.php"') !== false &&	strpos($forum_copyright, 'Simple Machines LLC') !== false))){$found = true; echo $forum_copyright;}
</search for>

<replace>
	elseif (isset($modSettings['copyright_key']) && sha1($modSettings['copyright_key'] . 'banjo') == '1d01885ece7a9355bdeb22ed107f0ffa8c323026'){$found = true; return;}elseif ((strpos($forum_copyright, '<a href="http://www.simplemachines.org/" title="Simple Machines Forum" target="_blank">Powered by SMF') !== false || strpos($forum_copyright, '<a href="http://www.simplemachines.org/" onclick="this.href += \'referer.php?forum=' . urlencode($context['forum_name'] . '|' . $boardurl . '|' . $forum_version) . '\';" target="_blank">SMF') !== false || strpos($forum_copyright, '<a href="http://www.simplemachines.org/" target="_blank">SMF') !== false || strpos($forum_copyright, '<a href="http://www.simplemachines.org/" title="Simple Machines Forum" target="_blank">SMF') !== false)&&((strpos($forum_copyright, '<a href="http://www.simplemachines.org/about/copyright.php" title="Free Forum Software" target="_blank">SMF &copy;') !== false && (strpos($forum_copyright, 'Lewis Media</a>') !== false || strpos($forum_copyright, 'Simple Machines</a>') !== false)) || strpos($forum_copyright, '<a href="http://www.lewismedia.com/">Lewis Media</a>') !== false || strpos($forum_copyright, '<a href="http://www.lewismedia.com/" target="_blank">Lewis Media</a>') !== false || (strpos($forum_copyright, '<a href="http://www.simplemachines.org/about/copyright.php"') !== false &&	strpos($forum_copyright, 'Simple Machines') !== false))){$found = true; echo $forum_copyright;}
</replace>



