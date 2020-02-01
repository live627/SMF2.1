<?php
namespace PHPTDD;

class BBCTest extends BaseTestCase
{

	public function preparseProvider()
	{
		return array(
			array(
				'[black]blah[/black]',
				'[color=black]blah[/color]',
			),
			array(
				'[time]now[/time]',
				'[time]' . forum_time() . '[/time]',
			),
			array(
				'something[quote][/quote]',
				'something'
			),
			array(
				"something\nblah",
				'something<br>blah'
			),
			array(
				'something  blah',
				'something&nbsp; blah'
			),
			array(
				"something\xC2\xA0blah",
				'something&nbsp;blah'
			),
			array(
				'something[code]without a closing tag',
				'something[code]without a closing tag[/code]',
			),
			array(
				'some open list[list][li]one[/list]',
				'some open list[list][li]one[/li][/list]',
			),
			array(
				'/me likes this',
				'[me=test]likes this[/me]'
			),
			array(
				'[url=//www.google.com]Google[/url]',
				'[url=&quot;//www.google.com&quot;]Google[/url]',
			),
			array(
				'[font=something]text[/font]',
				'[font=something]text[/font]',
			),
			array(
				'something[quote][/quote]',
				'something',
			),
			array(
				'something[code]without a closing tag',
				'something[code]without a closing tag[/code]',
			),
			array(
				'some open list[list][li]one[/list]',
				'some open list[list][li]one[/li][/list]',
			),
			array(
				'some list[code][list][li]one[/list][/code]',
				'some list[code][list][li]one[/list][/code]',
			),
		);
	}

	/**
	 * @dataProvider preparseProvider
	 */
	public function testPreparseBBcode($test, $expected)
	{
		global $sourcedir;

		// Refresh the expected timestamp. Tests take awhile to run.
		if ($test == '[time]now[/time]')
			$expected = '[time]' . forum_time() . '[/time]';

		require_once($sourcedir . '/Subs-Post.php');
		preparsecode($test);

		$this->assertEquals($expected, $test);
	}

	public function bbcProvider()
	{
		global $txt, $scripturl;

		return array(
			array(
				'abbr',
				'[abbr=so have obtained random text]short[/abbr]',
				'<abbr title="so have obtained random text">short</abbr>',
			),
			array(
				'abbr',
				'[abbr=so have obtained random &quot;quoted&quot; text]shor"q"t[/abbr]',
				'<abbr title="so have obtained random &quot;quoted&quot; text">shor"q"t</abbr>',
			),
			array(
				'anchor',
				'[anchor=blah]destination[/anchor]',
				'<span id="post_blah">destination</span>',
			),
			array(
				'anchor',
				'[anchor=#blah]destination[/anchor]',
				'<span id="post_#blah">destination</span>',
			),
			array(
				'b',
				'[b]bold[/b]',
				'<b>bold</b>',
			),
			array(
				'br',
				'First line[br]Second line',
				'First line<br>Second line',
			),
			array(
				'center',
				'[center]text[/center]',
				'<div class="centertext">text</div>',
			),
			array(
				'code',
				'[code]This is some code[/code]',
				'<div class="codeheader"><span class="code floatleft">' . $txt['code'] . '</span> <a class="codeoperation smf_select_text">' . $txt['code_select'] . '</a> <a class="codeoperation smf_expand_code hidden" data-shrink-txt="' . $txt['code_shrink'] . '" data-expand-txt="' . $txt['code_expand'] . '">' . $txt['code_expand'] . '</a></div><code class="bbc_code">This is some code</code>',
			),
			array(
				'code',
				'[code=unparsed text]This is some code[/code]',
				'<div class="codeheader"><span class="code floatleft">' . $txt['code'] . '</span> (unparsed text) <a class="codeoperation smf_select_text">' . $txt['code_select'] . '</a> <a class="codeoperation smf_expand_code hidden" data-shrink-txt="' . $txt['code_shrink'] . '" data-expand-txt="' . $txt['code_expand'] . '">' . $txt['code_expand'] . '</a></div><code class="bbc_code">This is some code</code>',
			),
			array(
				'color',
				'[color=#000]text[/color]',
				'<span style="color: #000;" class="bbc_color">text</span>',
			),
			array(
				'color',
				'[color=red]text[/color]',
				'<span style="color: red;" class="bbc_color">text</span>',
			),
			array(
				'color',
				'[color=blah]text[/color]',
				'<span style="color: blah;" class="bbc_color">text</span>',
			),
			array(
				'color',
				'[color=rgb(255,0,130)]text[/color]',
				'<span style="color: rgb(255,0,130);" class="bbc_color">text</span>',
			),
			array(
				'email',
				'[email]anything[/email]',
				'<a href="mailto:anything" class="bbc_email">anything</a>',
			),
			array(
				'email',
				'[email=anything]some text[/email]',
				'<a href="mailto:anything" class="bbc_email">some text</a>',
			),
			array(
				'hr',
				'Some[hr]text',
				'Some<hr>text',
			),
			array(
				'i',
				'[i]Italic[/i]',
				'<i>Italic</i>',
			),
			array(
				'img',
				'[img]http://adomain.tld/an_image.png[/img]',
				'<img src="http://adomain.tld/an_image.png" alt="" title="" class="bbc_img resized">',
			),
			array(
				'img',
				'[img]adomain.tld/an_image.png[/img]',
				'<img src="//adomain.tld/an_image.png" alt="" title="" class="bbc_img resized">',
			),
			array(
				'img',
				'[img width=100]http://adomain.tld/an_image.png[/img]',
				'<img src="http://adomain.tld/an_image.png" alt="" title="" width="100" class="bbc_img resized">',
			),
			array(
				'img',
				'[img height=100]http://adomain.tld/an_image.png[/img]',
				'<img src="http://adomain.tld/an_image.png" alt="" title="" height="100" class="bbc_img resized">',
			),
			array(
				'img',
				'[img height=100 width=150]http://adomain.tld/an_image.png[/img]',
				'<img src="http://adomain.tld/an_image.png" alt="" title="" width="150" height="100" class="bbc_img resized">',
			),
			array(
				'img',
				'[img alt=some text width=150 height=100]http://adomain.tld/an_image.png[/img]',
				'<img src="http://adomain.tld/an_image.png" alt="some text" title="" width="150" height="100" class="bbc_img resized">',
			),
			array(
				'img',
				'[img width=150 height=100 alt=some text]http://adomain.tld/an_image.png[/img]',
				'<img src="http://adomain.tld/an_image.png" alt="some text" title="" width="150" height="100" class="bbc_img resized">',
			),
			array(
				'iurl',
				'[iurl=http://www.simplemachines.net/]blah[/iurl]',
				'<a href="http://www.simplemachines.net/" class="bbc_link">blah</a>',
			),
			array(
				'iurl',
				'[iurl=http://www.simplemachines.net/]blah[/iurl]',
				'<a href="http://www.simplemachines.net/" class="bbc_link">blah</a>',
			),
			array(
				'left',
				'[left]blah[/left]',
				'<div style="text-align: left;">blah</div>',
			),
			array(
				'list',
				'[list][li]item[/li][li][list][li]sub item[/li][/list][/li][li]item[/li][/list]',
				'<ul class="bbc_list"><li>item</li><li><ul class="bbc_list"><li>sub item</li></ul></li><li>item</li></ul>',
			),
			array(
				'list',
				'[list][li]test[/li][/list]',
				'<ul class="bbc_list"><li>test</li></ul>',
			),
			array(
				'list',
				'[list type=decimal][li]test[/li][/list]',
				'<ul class="bbc_list" style="list-style-type: decimal;"><li>test</li></ul>',
			),
			array(
				'list',
				'[*]blah[+]blah[o]blah',
				'<ul class="bbc_list"><li type="disc">blah</li><li type="square">blah</li><li type="circle">blah</li></ul>'
			),
			array(
				'me',
				'[me=member name]text[/me]',
				'<div class="meaction">* member name text</div>',
			),
			array(
				'nobbc',
				'[nobbc][code]this is a code-block in a nobbc[/code][/nobbc]',
				'[code]this is a code-block in a nobbc[/code]',
			),
			array(
				'pre',
				'[pre]this is a pre-block[/pre]',
				'<pre>this is a pre-block</pre>',
			),
			array(
				'quote',
				'[quote]This is a quote[/quote]',
				'<blockquote class="bbc_standard_quote"><cite>Quote</cite>This is a quote</blockquote>',
			),
			array(
				'quote',
				'[quote]This is a quote[quote]of a quote[/quote][/quote]',
				'<blockquote class="bbc_standard_quote"><cite>Quote</cite>This is a quote<blockquote class="bbc_alternate_quote"><cite>Quote</cite>of a quote</blockquote></blockquote>',
			),
			array(
				'quote',
				'[quote author=unquoted author]This is a quote[/quote]',
				'<blockquote class="bbc_standard_quote"><cite>Quote from: unquoted author</cite>This is a quote</blockquote>',
			),
			array(
				'quote',
				'[quote author=&quot;quoted author&quot;]This is a quote[/quote]',
				'<blockquote class="bbc_standard_quote"><cite>Quote from: quoted author</cite>This is a quote</blockquote>',
			),
			array(
				'quote',
				'[quote=something]This is a quote[/quote]',
				'<blockquote class="bbc_standard_quote"><cite>Quote from: something</cite>This is a quote</blockquote>',
			),
			array(
				'quote',
				'[quote author=an author link=board=1;topic=123 date=12345678]This is a quote[/quote]',
				'<blockquote class="bbc_standard_quote"><cite><a href="' . $scripturl . '?topic=123">Quote from: an author on ' . timeformat(12345678) . '</a></cite>This is a quote</blockquote>',
			),
			array(
				'quote',
				'[quote author=an author link=topic=123.msg123#msg123 date=12345678]This is a quote[/quote]',
				'<blockquote class="bbc_standard_quote"><cite><a href="' . $scripturl . '?topic=123.msg123#msg123">Quote from: an author on ' . timeformat(12345678) . '</a></cite>This is a quote</blockquote>',
			),
			array(
				'quote',
				'[quote author=an author link=threadid=123.msg123#msg123 date=12345678]This is a quote[/quote]',
				'<blockquote class="bbc_standard_quote"><cite><a href="' . $scripturl . '?threadid=123.msg123#msg123">Quote from: an author on ' . timeformat(12345678) . '</a></cite>This is a quote</blockquote>',
			),
			array(
				'quote',
				'[quote author=an author link=action=profile;u=123 date=12345678]This is a quote[/quote]',
				'<blockquote class="bbc_standard_quote"><cite><a href="' . $scripturl . '?action=profile;u=123">Quote from: an author on ' . timeformat(12345678) . '</a></cite>This is a quote</blockquote>',
			),
			array(
				'right',
				'[right]blah[/right]',
				'<div style="text-align: right;">blah</div>',
			),
			array(
				's',
				'[s]blah[/s]',
				'<s>blah</s>',
			),
			array(
				'size',
				'[size=1]blah[/size]',
				'<span style="font-size: 0.7em;" class="bbc_size">blah</span>',
			),
			array(
				'size',
				'[size=7]blah[/size]',
				'<span style="font-size: 3.95em;" class="bbc_size">blah</span>',
			),
			array(
				'size',
				'[size=7px]blah[/size]',
				'<span style="font-size: 7px;" class="bbc_size">blah</span>',
			),
			array(
				'size',
				'[size=71px]blah[/size]',
				'<span style="font-size: 71px;" class="bbc_size">blah</span>',
			),
			array(
				'size',
				'[size=7pt]blah[/size]',
				'<span style="font-size: 7pt;" class="bbc_size">blah</span>',
			),
			array(
				'size',
				'[size=71pt]blah[/size]',
				'<span style="font-size: 71pt;" class="bbc_size">blah</span>',
			),
			array(
				'size',
				'[size=small]blah[/size]',
				'<span style="font-size: small;" class="bbc_size">blah</span>',
			),
			array(
				'size',
				'[size=smaller]blah[/size]',
				'<span style="font-size: smaller;" class="bbc_size">blah</span>',
			),
			array(
				'size',
				'[size=large]blah[/size]',
				'<span style="font-size: large;" class="bbc_size">blah</span>',
			),
			array(
				'size',
				'[size=larger]blah[/size]',
				'<span style="font-size: larger;" class="bbc_size">blah</span>',
			),
			array(
				'size',
				'[size=x-small]blah[/size]',
				'<span style="font-size: x-small;" class="bbc_size">blah</span>',
			),
			array(
				'size',
				'[size=xx-small]blah[/size]',
				'<span style="font-size: xx-small;" class="bbc_size">blah</span>',
			),
			array(
				'size',
				'[size=x-large]blah[/size]',
				'<span style="font-size: x-large;" class="bbc_size">blah</span>',
			),
			array(
				'size',
				'[size=xx-large]blah[/size]',
				'<span style="font-size: xx-large;" class="bbc_size">blah</span>',
			),
			array(
				'size',
				'[size=medium]blah[/size]',
				'<span style="font-size: medium;" class="bbc_size">blah</span>',
			),
			array(
				'size',
				'[size=0.1em]blah[/size]',
				'<span style="font-size: 0.1em;" class="bbc_size">blah</span>',
			),
			array(
				'size',
				'[size=9.11em]blah[/size]',
				'<span style="font-size: 9.11em;" class="bbc_size">blah</span>',
			),
			array(
				'sub',
				'[sub]blah[/sub]',
				'<sub>blah</sub>',
			),
			array(
				'sup',
				'[sup]blah[/sup]',
				'<sup>blah</sup>',
			),
			array(
				'table',
				'[table][tr][td][table][tr][td]test[/td][/tr][/table][/td][/tr][/table]',
				'<table class="bbc_table"><tr><td><table class="bbc_table"><tr><td>test</td></tr></table></td></tr></table>',
			),
			array(
				'time',
				'[time]12345678[/time]',
				timeformat(12345678),
			),
			array(
				'u',
				'[u]blah[/u]',
				'<u>blah</u>',
			),
			array(
				'url',
				'[url=http://www.simplemachines.net/]blah[/url]',
				'<a href="http://www.simplemachines.net/" class="bbc_link" target="_blank" rel="noopener">blah</a>',
			),
			array(
				'url',
				'http://www.simplemachines.net/',
				'<a href="http://www.simplemachines.net/" class="bbc_link" target="_blank" rel="noopener">http://www.simplemachines.net/</a>',
			),
		);
	}

	/**
	 * @dataProvider bbcProvider
	 */
	public function testBBC($tag, $test, $expected)
	{
		$result = parse_bbc($test);
		$this->assertEquals($expected, $result);

		$this->assertContains($tag, array_column(parse_bbc(false), 'tag'));
	}

	public function html_to_bbcProvider()
	{
		global $context, $boardurl, $sourcedir;

		require_once($sourcedir . '/Subs-Editor.php');

		return array(
			array(
				'<hr></hr>a<div><br></div><div><hr></hr></div>',
				"[hr]\na\n\n[hr]\n",
			),
			array(
				'<table><tr><td align="right">a</td></tr></table>',
				"[table][tr][td][right]a[/right][/td][/tr][/table]",
			),
			array(
				'<table><tr><td align="right">a</tr></table>',
				"[table][tr]a[/tr][/table]",
			),
			array(
				'<img src="Smileys/default/tongue.gif" alt=":P" title="Tongue" class="smiley" border="0">',
				"[img alt=:P]$boardurl/Smileys/default/tongue.gif[/img]",
			),
			array(
				'<a href="test.html">test</a>',
				"[url=$boardurl/test.html]test[/url]",
			),
			array(
				'<ul class="bbc_list"><li>test',
				"[list]\n\t[li]test[/li]\n[/list]",
			),
			array(
				'<ul><li>a<ul><li>b</li></ul></li></ul>',
				"[list]\n\t[li]a[list]\n\t\t[li]b[/li]\n\t[/list][/li]\n[/list]",
			),
			array(
				'<ul>a<li>b<ul><li>c</li></ul></li></ul>',
				"[list]\n\t[li]a[/li]\n\t[li]b[list]\n\t\t[li]c[/li]\n\t[/list][/li]\n[/list]",
			),
			array(
				'<ul><li>a</li><ul><li>b</li></ul></ul>',
				"[list]\n\t[li]a[/li]\n\t[li][list]\n\t\t[li]b[/li]\n\t[/list][/li]\n[/list]",
			),
			array(
				'<ul></li><li>a</li></ul>',
				"[list]\n\t[li]a[/li]\n[/list]",
			),
			array(
				'</ul><ul><li>a</li></ul>',
				"[list]\n\t[li]a[/li]\n[/list]",
			),
			array(
				'<ol><li>a</li></ul>',
				"[list type=decimal]\n\t[li]a[/li]\n[/list]",
			),
			array(
				'<ul type="square"><li>a</li></ul>',
				"[list type=square]\n\t[li]a[/li]\n[/list]",
			),
			array(
				'<ul>a',
				"[list]\n\t[li]a[/li]\n[/list]",
			),
			array(
				'<ul><li><ul>a',
				"[list]\n\t[li][list]\n\t\t[li]a[/li]\n\t[/list][/li]\n[/list]",
			),
			array(
				'a<script>b</script>c',
				"ac",
			),
			array(
				'a<!-- b<ul>c -->d',
				"ad",
			),
			array(
				'a<![CDATA[b<ul>c]]>d',
				"ad",
			),
			array(
				'a<style>b</style>c',
				"ac",
			),
		);
	}

	/**
	 * @dataProvider html_to_bbcProvider
	 */
	public function test_html_to_bbc($test, $expected)
	{
		$this->assertEquals($expected, html_to_bbc($test));
	}

	public function smileyProvider()
	{
		global $context, $boardurl;

		return array(
			array(
				'abc :) def',
				'abc <img src="' . $boardurl . '/Smileys/fugue/smiley.png" alt="&#58;&#41;" title="Smiley" class="smiley"> def',
			),
			array(
				'abc :)',
				'abc <img src="' . $boardurl . '/Smileys/fugue/smiley.png" alt="&#58;&#41;" title="Smiley" class="smiley">',
			),
			array(
				':) def',
				'<img src="' . $boardurl . '/Smileys/fugue/smiley.png" alt="&#58;&#41;" title="Smiley" class="smiley"> def',
			),
			array(
				'abc:)def',
				'abc:)def',
			),
			array(
				'[url=mailto:David@bla.com]',
				'[url=mailto:David@bla.com]',
			),
		);
	}

	/**
	 * @dataProvider smileyProvider
	 */
	public function testSmiley($test, $expected)
	{
		parsesmileys($test);
		$this->assertEquals($expected, $test);

		//~ $result = parse_bbc($test, false);
		//~ $this->assertEquals($expected, $result);
	}

	public function legacyBBCProvider()
	{
		global $context;

		return array(
			['tt',
				'[tt]blah[/tt]',
				'&#91;tt]blah&#91;/tt]',
				'<span class="monospace">blah</span>', ],
			['flash',
				'[flash]blah[/flash]',
				'&#91;flash]http://blah&#91;/flash]'],
			['bdo',
				'[bdo=rtl]blah[/bdo]',
				'&#91;bdo=rtl]blah&#91;/bdo]'],
			['black',
				'[black]blah[/black]',
				'[color=black]blah[/color]'],
			['white',
				'[white]blah[/white]',
				'[color=white]blah[/color]'],
			['red',
				'[red]blah[/red]',
				'[color=red]blah[/color]'],
			['green',
				'[green]blah[/green]',
				'[color=green]blah[/color]'],
			['blue',
				'[blue]blah[/blue]',
				'[color=blue]blah[/color]'],
			['acronym',
				'[acronym]blah[/acronym]',
				'&#91;acronym]blah&#91;/acronym]'],
			['ftp',
				'[ftp]blah[/ftp]',
				'&#91;ftp]ftp://blah&#91;/ftp]'],
			['glow',
				'[glow]blah[/glow]',
				'&#91;glow]blah&#91;/glow]'],
			['move',
				'[move]blah[/move]',
				'&#91;move]blah&#91;/move]'],
			['shadow',
				'[shadow]blah[/shadow]',
				'&#91;shadow]blah&#91;/shadow]'],
		);
	}

	/**
	 * @dataProvider legacyBBCProvider
	 */
	public function testLegacyBBC($tag, $test, $expected)
	{
		preparsecode($test);
		$this->assertEquals($expected, $test);

		$this->assertContains($tag, array_column(parse_bbc(false), 'tag'));

		//~ $result = parse_bbc($test, false);
		//~ $this->assertEquals($expected, $result);
	}

	public function te6stMessageCache()
	{
		global $smcFunc, $txt, $scripturl, $context, $modSettings, $user_info;

		add_integration_function('integrate_post_parsebbc', 'pause', false);
		$modSettings['cache_enable'] = 2;
		$message = str_repeat('0', 1001);
		$key = 'message-cache-test';
		$expected = parse_bbc($message, false, $key);
		$actual = cache_get_data('parse:' . $key . '-' . md5(md5($message) . '-' . $smcFunc['json_encode']($context['browser']) . $txt['lang_locale'] . $user_info['time_offset'] . $user_info['time_format']), 240);
		$this->assertEquals($expected, $actual);
	}
}


function pause()
{
	usleep(10000);
}