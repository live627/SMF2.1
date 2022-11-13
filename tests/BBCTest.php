<?php

declare(strict_types=1);
namespace SMF\Tests;

use PHPUnit\Framework\TestCase;

class BBCTest extends TestCase
{
	/**
	 * @return string[][]
	 */
	public function preparseProvider(): array
	{
		return [
			[
				'[black]blah[/black]',
				'[color=black]blah[/color]',
			],
			[
				'[time]now[/time]',
				'[time]' . forum_time() . '[/time]',
			],
			[
				'something[quote][/quote]',
				'something',
			],
			[
				"something\nblah",
				'something<br>blah',
			],
			[
				'something  blah',
				'something&nbsp; blah',
			],
			[
				"something\xC2\xA0blah",
				'something&nbsp;blah',
			],
			[
				'something[code]without a closing tag',
				'something[code]without a closing tag[/code]',
			],
			[
				'some open list[list][li]one[/list]',
				'some open list[list][li]one[/li][/list]',
			],
			[
				'/me likes this',
				'[me=test]likes this[/me]',
			],
			[
				'[url=//www.google.com]Google[/url]',
				'[url=&quot;//www.google.com&quot;]Google[/url]',
			],
			[
				'[font=something]text[/font]',
				'[font=something]text[/font]',
			],
			[
				'something[quote][/quote]',
				'something',
			],
			[
				'something[code]without a closing tag',
				'something[code]without a closing tag[/code]',
			],
			[
				'some open list[list][li]one[/list]',
				'some open list[list][li]one[/li][/list]',
			],
			[
				'some list[code][list][li]one[/list][/code]',
				'some list[code][list][li]one[/list][/code]',
			],
		];
	}

	/**
	 * @dataProvider preparseProvider
	 *
	 */
	public function testPreparseBBcode(string $test, string $expected): void
	{
		global $sourcedir;

		// Refresh the expected timestamp. Tests take awhile to run.
		if ($test == '[time]now[/time]')
			$expected = '[time]' . forum_time() . '[/time]';

		require_once __DIR__ . '/../Sources/Subs-Post.php';
		preparsecode($test);

		$this->assertEquals($expected, $test);
	}

	/**
	 * @return (mixed|string)[][]
	 */
	public function bbcProvider(): array
	{
		global $txt, $scripturl;

		return [
			[
				'abbr',
				'[abbr=so have obtained random text]short[/abbr]',
				'<abbr title="so have obtained random text">short</abbr>',
			],
			[
				'abbr',
				'[abbr=so have obtained random &quot;quoted&quot; text]shor"q"t[/abbr]',
				'<abbr title="so have obtained random &quot;quoted&quot; text">shor"q"t</abbr>',
			],
			[
				'anchor',
				'[anchor=blah]destination[/anchor]',
				'<span id="post_blah">destination</span>',
			],
			[
				'anchor',
				'[anchor=#blah]destination[/anchor]',
				'<span id="post_#blah">destination</span>',
			],
			[
				'b',
				'[b]bold[/b]',
				'<b>bold</b>',
			],
			[
				'br',
				'First line[br]Second line',
				'First line<br>Second line',
			],
			[
				'center',
				'[center]text[/center]',
				'<div class="centertext">text</div>',
			],
			[
				'code',
				'[code]This is some code[/code]',
				'<div class="codeheader"><span class="code floatleft">' . $txt['code'] . '</span> <a class="codeoperation smf_select_text">' . $txt['code_select'] . '</a> <a class="codeoperation smf_expand_code hidden" data-shrink-txt="' . $txt['code_shrink'] . '" data-expand-txt="' . $txt['code_expand'] . '">' . $txt['code_expand'] . '</a></div><code class="bbc_code">This is some code</code>',
			],
			[
				'code',
				'[code=unparsed text]This is some code[/code]',
				'<div class="codeheader"><span class="code floatleft">' . $txt['code'] . '</span> (unparsed text) <a class="codeoperation smf_select_text">' . $txt['code_select'] . '</a> <a class="codeoperation smf_expand_code hidden" data-shrink-txt="' . $txt['code_shrink'] . '" data-expand-txt="' . $txt['code_expand'] . '">' . $txt['code_expand'] . '</a></div><code class="bbc_code">This is some code</code>',
			],
			[
				'color',
				'[color=#000]text[/color]',
				'<span style="color: #000;" class="bbc_color">text</span>',
			],
			[
				'color',
				'[color=red]text[/color]',
				'<span style="color: red;" class="bbc_color">text</span>',
			],
			[
				'color',
				'[color=blah]text[/color]',
				'<span style="color: blah;" class="bbc_color">text</span>',
			],
			[
				'color',
				'[color=rgb(255,0,130)]text[/color]',
				'<span style="color: rgb(255,0,130);" class="bbc_color">text</span>',
			],
			[
				'email',
				'[email]anything[/email]',
				'<a href="mailto:anything" class="bbc_email">anything</a>',
			],
			[
				'email',
				'[email=anything]some text[/email]',
				'<a href="mailto:anything" class="bbc_email">some text</a>',
			],
			[
				'hr',
				'Some[hr]text',
				'Some<hr>text',
			],
			[
				'i',
				'[i]Italic[/i]',
				'<i>Italic</i>',
			],
			[
				'img',
				'[img]http://example.com/image.png[/img]',
				'<img src="http://example.com/image.png" alt="" class="bbc_img" loading="lazy">',
			],
			[
				'img',
				'[img]example.com/image.png[/img]',
				'<img src="//example.com/image.png" alt="" class="bbc_img" loading="lazy">',
			],
			[
				'img',
				'[img width=100]http://example.com/image.png[/img]',
				'<img src="http://example.com/image.png" alt="" width="100" class="bbc_img resized" loading="lazy">',
			],
			[
				'img',
				'[img height=100]http://example.com/image.png[/img]',
				'<img src="http://example.com/image.png" alt="" height="100" class="bbc_img resized" loading="lazy">',
			],
			[
				'img',
				'[img height=100 width=150]http://example.com/image.png[/img]',
				'<img src="http://example.com/image.png" alt="" width="150" height="100" class="bbc_img resized" loading="lazy">',
			],
			[
				'img',
				'[img alt=some text width=150 height=100]http://example.com/image.png[/img]',
				'<img src="http://example.com/image.png" alt="some text" width="150" height="100" class="bbc_img resized" loading="lazy">',
			],
			[
				'img',
				'[img width=150 height=100 alt=some text]http://example.com/image.png[/img]',
				'<img src="http://example.com/image.png" alt="some text" width="150" height="100" class="bbc_img resized" loading="lazy">',
			],
			[
				'img',
				'[img title=some text alt=some text width=150 height=100]http://example.com/image.png[/img]',
				'<img src="http://example.com/image.png" alt="some text" title="some text" width="150" height="100" class="bbc_img resized" loading="lazy">',
			],
			[
				'img',
				'[img width=150 height=100 title=some text alt=some text]http://example.com/image.png[/img]',
				'<img src="http://example.com/image.png" alt="some text" title="some text" width="150" height="100" class="bbc_img resized" loading="lazy">',
			],
			[
				'iurl',
				'[iurl=http://www.simplemachines.net/]blah[/iurl]',
				'<a href="http://www.simplemachines.net/" class="bbc_link">blah</a>',
			],
			[
				'iurl',
				'[iurl=http://www.simplemachines.net/]blah[/iurl]',
				'<a href="http://www.simplemachines.net/" class="bbc_link">blah</a>',
			],
			[
				'left',
				'[left]blah[/left]',
				'<div class="lefttext">blah</div>',
			],
			[
				'list',
				'[list][li]item[/li][li][list][li]sub item[/li][/list][/li][li]item[/li][/list]',
				'<ul class="bbc_list"><li>item</li><li><ul class="bbc_list"><li>sub item</li></ul></li><li>item</li></ul>',
			],
			[
				'list',
				'[list][li]test[/li][/list]',
				'<ul class="bbc_list"><li>test</li></ul>',
			],
			[
				'list',
				'[list type=decimal][li]test[/li][/list]',
				'<ul class="bbc_list" style="list-style-type: decimal;"><li>test</li></ul>',
			],
			[
				'list',
				'[*]blah[+]blah[o]blah',
				'<ul class="bbc_list"><li type="disc">blah</li><li type="square">blah</li><li type="circle">blah</li></ul>',
			],
			[
				'me',
				'[me=member name]text[/me]',
				'<div class="meaction">* member name text</div>',
			],
			[
				'nobbc',
				'[nobbc][code]this is a code-block in a nobbc[/code][/nobbc]',
				'[code]this is a code-block in a nobbc[/code]',
			],
			[
				'pre',
				'[pre]this is a pre-block[/pre]',
				'<pre>this is a pre-block</pre>',
			],
			[
				'quote',
				'[quote]This is a quote[/quote]',
				'<blockquote class="bbc_standard_quote"><cite>Quote</cite>This is a quote</blockquote>',
			],
			[
				'quote',
				'[quote]This is a quote[quote]of a quote[/quote][/quote]',
				'<blockquote class="bbc_standard_quote"><cite>Quote</cite>This is a quote<blockquote class="bbc_alternate_quote"><cite>Quote</cite>of a quote</blockquote></blockquote>',
			],
			[
				'quote',
				'[quote author=unquoted author]This is a quote[/quote]',
				'<blockquote class="bbc_standard_quote"><cite>Quote from: unquoted author</cite>This is a quote</blockquote>',
			],
			[
				'quote',
				'[quote author=&quot;quoted author&quot;]This is a quote[/quote]',
				'<blockquote class="bbc_standard_quote"><cite>Quote from: quoted author</cite>This is a quote</blockquote>',
			],
			[
				'quote',
				'[quote=something]This is a quote[/quote]',
				'<blockquote class="bbc_standard_quote"><cite>Quote from: something</cite>This is a quote</blockquote>',
			],
			[
				'quote',
				'[quote author=an author link=board=1;topic=123 date=12345678]This is a quote[/quote]',
				'<blockquote class="bbc_standard_quote"><cite><a href="' . $scripturl . '?topic=123">Quote from: an author on ' . timeformat(
					12345678
				) . '</a></cite>This is a quote</blockquote>',
			],
			[
				'quote',
				'[quote author=an author link=topic=123.msg123#msg123 date=12345678]This is a quote[/quote]',
				'<blockquote class="bbc_standard_quote"><cite><a href="' . $scripturl . '?topic=123.msg123#msg123">Quote from: an author on ' . timeformat(
					12345678
				) . '</a></cite>This is a quote</blockquote>',
			],
			[
				'quote',
				'[quote author=an author link=threadid=123.msg123#msg123 date=12345678]This is a quote[/quote]',
				'<blockquote class="bbc_standard_quote"><cite><a href="' . $scripturl . '?threadid=123.msg123#msg123">Quote from: an author on ' . timeformat(
					12345678
				) . '</a></cite>This is a quote</blockquote>',
			],
			[
				'quote',
				'[quote author=an author link=action=profile;u=123 date=12345678]This is a quote[/quote]',
				'<blockquote class="bbc_standard_quote"><cite><a href="' . $scripturl . '?action=profile;u=123">Quote from: an author on ' . timeformat(
					12345678
				) . '</a></cite>This is a quote</blockquote>',
			],
			[
				'quote',
				'[quote=[url=https://www.example.com/]Example.com[/url]]The quote[/quote]',
				'<blockquote class="bbc_standard_quote"><cite>Quote from: [url=https://www.example.com/</cite>Example.com[/url]]The quote</blockquote>',
			],
			[
				'quote',
				'[quote="[url="https://www.example.com/"]Example.com[/url]"]The quote[/quote]',
				'<blockquote class="bbc_standard_quote"><cite>Quote from: "[url="https://www.example.com/"</cite>Example.com[/url]"]The quote</blockquote>',
			],
			[
				'quote',
				'[quote="[url=https://www.example.com/]Example.com[/url]"]The quote[/quote]',
				'<blockquote class="bbc_standard_quote"><cite>Quote from: "[url=https://www.example.com/</cite>Example.com[/url]"]The quote</blockquote>',
			],
			[
				'right',
				'[right]blah[/right]',
				'<div class="righttext">blah</div>',
			],
			[
				's',
				'[s]blah[/s]',
				'<s>blah</s>',
			],
			[
				'size',
				'[size=1]blah[/size]',
				'<span style="font-size: 0.7em;" class="bbc_size">blah</span>',
			],
			[
				'size',
				'[size=7]blah[/size]',
				'<span style="font-size: 3.95em;" class="bbc_size">blah</span>',
			],
			[
				'size',
				'[size=7px]blah[/size]',
				'<span style="font-size: 7px;" class="bbc_size">blah</span>',
			],
			[
				'size',
				'[size=71px]blah[/size]',
				'<span style="font-size: 71px;" class="bbc_size">blah</span>',
			],
			[
				'size',
				'[size=7pt]blah[/size]',
				'<span style="font-size: 7pt;" class="bbc_size">blah</span>',
			],
			[
				'size',
				'[size=71pt]blah[/size]',
				'<span style="font-size: 71pt;" class="bbc_size">blah</span>',
			],
			[
				'size',
				'[size=small]blah[/size]',
				'<span style="font-size: small;" class="bbc_size">blah</span>',
			],
			[
				'size',
				'[size=smaller]blah[/size]',
				'<span style="font-size: smaller;" class="bbc_size">blah</span>',
			],
			[
				'size',
				'[size=large]blah[/size]',
				'<span style="font-size: large;" class="bbc_size">blah</span>',
			],
			[
				'size',
				'[size=larger]blah[/size]',
				'<span style="font-size: larger;" class="bbc_size">blah</span>',
			],
			[
				'size',
				'[size=x-small]blah[/size]',
				'<span style="font-size: x-small;" class="bbc_size">blah</span>',
			],
			[
				'size',
				'[size=xx-small]blah[/size]',
				'<span style="font-size: xx-small;" class="bbc_size">blah</span>',
			],
			[
				'size',
				'[size=x-large]blah[/size]',
				'<span style="font-size: x-large;" class="bbc_size">blah</span>',
			],
			[
				'size',
				'[size=xx-large]blah[/size]',
				'<span style="font-size: xx-large;" class="bbc_size">blah</span>',
			],
			[
				'size',
				'[size=medium]blah[/size]',
				'<span style="font-size: medium;" class="bbc_size">blah</span>',
			],
			[
				'size',
				'[size=0.1em]blah[/size]',
				'<span style="font-size: 0.1em;" class="bbc_size">blah</span>',
			],
			[
				'size',
				'[size=9.11em]blah[/size]',
				'<span style="font-size: 9.11em;" class="bbc_size">blah</span>',
			],
			[
				'sub',
				'[sub]blah[/sub]',
				'<sub>blah</sub>',
			],
			[
				'sup',
				'[sup]blah[/sup]',
				'<sup>blah</sup>',
			],
			[
				'table',
				'[table][tr][td][table][tr][td]test[/td][/tr][/table][/td][/tr][/table]',
				'<table class="bbc_table"><tr><td><table class="bbc_table"><tr><td>test</td></tr></table></td></tr></table>',
			],
			[
				'time',
				'[time]12345678[/time]',
				'<span class="bbc_time">May 23, 1970, 09:21 PM</span>',
			],
			[
				'u',
				'[u]blah[/u]',
				'<u>blah</u>',
			],
			[
				'url',
				'[url=http://www.simplemachines.net/]blah[/url]',
				'<a href="http://www.simplemachines.net/" class="bbc_link" target="_blank" rel="noopener">blah</a>',
			],
			[
				'url',
				'[url=http://www.google.co.uk/search?hl=en&q=BA6596F&btnG=Google+Search&meta=]blah[/url]',
				'<a href="http://www.google.co.uk/search?hl=en&q=BA6596F&btnG=Google+Search&meta=" class="bbc_link" target="_blank" rel="noopener">blah</a>',
			],
		];
	}

	/**
	 * @dataProvider bbcProvider
	 *
	 */
	public function testBBC(string $tag, string $test, string $expected): void
	{
		$result = parse_bbc($test);
		$this->assertEquals($expected, $result);

		$this->assertContains($tag, array_column(parse_bbc(false), 'tag'));
	}

	/**
	 * @return string[][]
	 */
	public function autolinkProvider(): array
	{
		return [
			[
				'example.biz',
				'<a href="//example.biz" class="bbc_link" target="_blank" rel="noopener">example.biz</a>',
			],
			[
				'www.example.info',
				'<a href="//www.example.info" class="bbc_link" target="_blank" rel="noopener">www.example.info</a>',
			],
			[
				'ftp://user:password@ftp.example.com',
				'<a href="ftp://user:password@ftp.example.com" class="bbc_link" target="_blank" rel="noopener">ftp://user:password@ftp.example.com</a>',
			],
			[
				'ftp://ftp.is.co.za/rfc/rfc1808.txt',
				'<a href="ftp://ftp.is.co.za/rfc/rfc1808.txt" class="bbc_link" target="_blank" rel="noopener">ftp://ftp.is.co.za/rfc/rfc1808.txt</a>',
			],
			[
				'gopher://spinaltap.micro.umn.edu/00/Weather/California/Los%20Angeles',
				'<a href="gopher://spinaltap.micro.umn.edu/00/Weather/California/Los%20Angeles" class="bbc_link" target="_blank" rel="noopener">gopher://spinaltap.micro.umn.edu/00/Weather/California/Los%20Angeles</a>',
			],
			[
				'http://www.math.uio.no/faq/compression-faq/part1.html',
				'<a href="http://www.math.uio.no/faq/compression-faq/part1.html" class="bbc_link" target="_blank" rel="noopener">http://www.math.uio.no/faq/compression-faq/part1.html</a>',
			],
			[
				'mailto:mduerst@ifi.unizh.ch',
				'<a href="mailto:mduerst@ifi.unizh.ch" class="bbc_email">mailto:mduerst@ifi.unizh.ch</a>',
			],
			[
				'news:comp.infosystems.www.servers.unix',
				'<a href="news:comp.infosystems.www.servers.unix" class="bbc_link" target="_blank" rel="noopener">news:comp.infosystems.www.servers.unix</a>',
			],
			[
				'telnet://melvyl.ucop.edu/',
				'<a href="telnet://melvyl.ucop.edu/" class="bbc_link" target="_blank" rel="noopener">telnet://melvyl.ucop.edu/</a>',
			],
			[
				'foobar//www.example.com',
				'foobar<a href="//www.example.com" class="bbc_link" target="_blank" rel="noopener">//www.example.com</a>',
			],
			[
				'http://høns.dk',
				'<a href="http://xn--hns-0na.dk" class="bbc_link" target="_blank" rel="noopener">http://høns.dk</a>',
			],
			[
				'http://ar.wikipedia.org/wiki/النطاقات_العربية',
				'<a href="http://ar.wikipedia.org/wiki/النطاقات_العربية" class="bbc_link" target="_blank" rel="noopener">http://ar.wikipedia.org/wiki/النطاقات_العربية</a>',
			],
			[
				'http://example.com/foo_(bar_baz)',
				'<a href="http://example.com/foo_(bar_baz)" class="bbc_link" target="_blank" rel="noopener">http://example.com/foo_(bar_baz)</a>',
			],
			[
				'http://example.com/foo_(bar_baz',
				'<a href="http://example.com/foo_(bar_baz" class="bbc_link" target="_blank" rel="noopener">http://example.com/foo_(bar_baz</a>',
			],
			[
				'(http://example.com/foo_bar_baz)',
				'(<a href="http://example.com/foo_bar_baz" class="bbc_link" target="_blank" rel="noopener">http://example.com/foo_bar_baz</a>)',
			],
			[
				'http://example.com/(bar_(qux)_baz)_(foo)?test=test;foo=bar',
				'<a href="http://example.com/(bar_(qux)_baz)_(foo)?test=test;foo=bar" class="bbc_link" target="_blank" rel="noopener">http://example.com/(bar_(qux)_baz)_(foo)?test=test;foo=bar</a>',
			],
			[
				'example.com/?test=[(foo)]{bar}',
				'<a href="//example.com/?test=&#91;(foo)&#93;{bar}" class="bbc_link" target="_blank" rel="noopener">example.com/?test=[(foo)]{bar}</a>',
			],
			[
				'http://www.example.com/a/test?one=for#{all”',
				'<a href="http://www.example.com/a/test?one=for#{all" class="bbc_link" target="_blank" rel="noopener">http://www.example.com/a/test?one=for#{all</a>"',
			],
			[
				'http://www.example.com/a/test?one=for<all>#derp',
				'<a href="http://www.example.com/a/test?one=for" class="bbc_link" target="_blank" rel="noopener">http://www.example.com/a/test?one=for</a><all>#derp',
			],
			[
				'mailto:jsmith@example.com?subject=A%20Test&body=My%20idea%20is%3A%20%0A',
				'<a href="mailto:jsmith@example.com?subject=A%20Test&body=My%20idea%20is%3A%20%0A" class="bbc_email">mailto:jsmith@example.com?subject=A%20Test&body=My%20idea%20is%3A%20%0A</a>',
			],
			[
				'file:///Users/foo/bar.txt',
				'<a href="file:///Users/foo/bar.txt" class="bbc_link" target="_blank" rel="noopener">file:///Users/foo/bar.txt</a>',
			],
			[
				'http://example.com/',
				'<a href="http://example.com/" class="bbc_link" target="_blank" rel="noopener">http://example.com/</a>',
			],
			[
				'http://example.com//',
				'<a href="http://example.com/" class="bbc_link" target="_blank" rel="noopener">http://example.com/</a>/',
			],
			[
				'http://example.com/foo.php?',
				'<a href="http://example.com/foo.php" class="bbc_link" target="_blank" rel="noopener">http://example.com/foo.php</a>?',
			],
			[
				'foo://bar',
				'<a href="foo://bar" class="bbc_link" target="_blank" rel="noopener">foo://bar</a>',
			],
			[
				'facetime://+19995551234',
				'<a href="facetime://+19995551234" class="bbc_link" target="_blank" rel="noopener">facetime://+19995551234</a>',
			],
			[
				'Have you been to example.com? It is exemplary!',
				'Have you been to <a href="//example.com" class="bbc_link" target="_blank" rel="noopener">example.com</a>? It is exemplary!',
			],
			[
				'Have you been to example.com?It is exemplary!',
				'Have you been to <a href="//example.com" class="bbc_link" target="_blank" rel="noopener">example.com</a>?It is exemplary!',
			],
			[
				'Have you been to //example.com?It is exemplary!',
				'Have you been to <a href="//example.com" class="bbc_link" target="_blank" rel="noopener">//example.com</a>?It is exemplary!',
			],
			[
				'Have you been to http://example.com?It is exemplary!',
				'Have you been to <a href="http://example.com" class="bbc_link" target="_blank" rel="noopener">http://example.com</a>?It is exemplary!',
			],
			[
				'http://123.4.56.78/',
				'<a href="http://123.4.56.78/" class="bbc_link" target="_blank" rel="noopener">http://123.4.56.78/</a>',
			],
			[
				'https://[2001:db8:85a3:8d3:1319:8a2e:370:7348]:443/foo.html',
				'<a href="https://&#91;2001:db8:85a3:8d3:1319:8a2e:370:7348&#93;:443/foo.html" class="bbc_link" target="_blank" rel="noopener">https://[2001:db8:85a3:8d3:1319:8a2e:370:7348]:443/foo.html</a>',
			],
			[
				'http://[::1]/foo.html',
				'<a href="http://&#91;::1&#93;/foo.html" class="bbc_link" target="_blank" rel="noopener">http://[::1]/foo.html</a>',
			],
			[
				'//foo.asghetyh',
				'//foo.asghetyh',
			],
			[
				'//foo.com',
				'<a href="//foo.com" class="bbc_link" target="_blank" rel="noopener">//foo.com</a>',
			],
			[
				'qwer:111',
				'qwer:111',
			],
			[
				'ssh://localhost',
				'<a href="ssh://localhost" class="bbc_link" target="_blank" rel="noopener">ssh://localhost</a>',
			],
			[
				'ssh://toaster.local',
				'<a href="ssh://toaster.local" class="bbc_link" target="_blank" rel="noopener">ssh://toaster.local</a>',
			],
			[
				'ssh:111',
				'ssh:111',
			],
			[
				'mailto:mduérst@ifi.unizh.ch',
				'<a href="mailto:mduérst@ifi.unizh.ch" class="bbc_email">mailto:mduérst@ifi.unizh.ch</a>',
			],
			[
				'jsmith@example.com',
				'<a href="mailto:jsmith@example.com" class="bbc_email">jsmith@example.com</a>',
			],
			[
				'For an example of a valuably variable foo, go to http://www.example.com/index.php?var[foo]=value!',
				'For an example of a valuably variable foo, go to <a href="http://www.example.com/index.php?var&#91;foo&#93;=value" class="bbc_link" target="_blank" rel="noopener">http://www.example.com/index.php?var[foo]=value</a>!',
			],
			[
				'Have you been to http://www.example.com/index.php?var[foo]=value?',
				'Have you been to <a href="http://www.example.com/index.php?var&#91;foo&#93;=value" class="bbc_link" target="_blank" rel="noopener">http://www.example.com/index.php?var[foo]=value</a>?',
			],
			[
				'In parentheses (http://www.example.com/index.php?var[foo]=value), will this still be autolinked correctly? ',
				'In parentheses (<a href="http://www.example.com/index.php?var&#91;foo&#93;=value" class="bbc_link" target="_blank" rel="noopener">http://www.example.com/index.php?var[foo]=value</a>), will this still be autolinked correctly? ',
			],
			[
				'What about in brackets [http://www.example.com/index.php?var[foo]=value]?',
				'What about in brackets [<a href="http://www.example.com/index.php?var&#91;foo&#93;=value" class="bbc_link" target="_blank" rel="noopener">http://www.example.com/index.php?var[foo]=value</a>]?',
			],
			[
				'http://www.google.co.uk/search?hl=en&q=BA6596F&btnG=Google+Search&meta=',
				'<a href="http://www.google.co.uk/search?hl=en&q=BA6596F&btnG=Google+Search&meta=" class="bbc_link" target="_blank" rel="noopener">http://www.google.co.uk/search?hl=en&q=BA6596F&btnG=Google+Search&meta=</a>',
			],
			[
				'täst.de',
				'<a href="//xn--tst-qla.de=" class="bbc_link" target="_blank" rel="noopener">täst.de</a>',
			],
		];
	}

	/**
	 * @dataProvider autolinkProvider
	 */
	public function testAutolink(string $test, string $expected): void
	{
		$result = parse_bbc($test);
		$this->assertEquals($expected, $result);
	}

	public function html_to_bbcProvider(): array
	{
		global $context, $boardurl, $sourcedir;

		require_once __DIR__ . '/../Sources/Subs-Editor.php';

		return [
			[
				'<hr></hr>a<div><br></div><div><hr></hr></div>',
				"[hr]\na\n\n[hr]\n",
			],
			[
				'<table><tr><td align="right">a</td></tr></table>',
				"[table][tr][td][right]a[/right][/td][/tr][/table]",
			],
			[
				'<table><tr><td align="right">a</tr></table>',
				"[table][tr]a[/tr][/table]",
			],
			[
				'<img src="Smileys/default/tongue.gif" alt=":P" title="Tongue" class="smiley" border="0">',
				"[img alt=:P]$boardurl/Smileys/default/tongue.gif[/img]",
			],
			[
				'<a href="test.html">test</a>',
				"[url=$boardurl/test.html]test[/url]",
			],
			[
				'<ul class="bbc_list"><li>test',
				"[list]\n\t[li]test[/li]\n[/list]",
			],
			[
				'<ul><li>a<ul><li>b</li></ul></li></ul>',
				"[list]\n\t[li]a[list]\n\t\t[li]b[/li]\n\t[/list][/li]\n[/list]",
			],
			[
				'<ul>a<li>b<ul><li>c</li></ul></li></ul>',
				"[list]\n\t[li]a[/li]\n\t[li]b[list]\n\t\t[li]c[/li]\n\t[/list][/li]\n[/list]",
			],
			[
				'<ul><li>a</li><ul><li>b</li></ul></ul>',
				"[list]\n\t[li]a[/li]\n\t[li][list]\n\t\t[li]b[/li]\n\t[/list][/li]\n[/list]",
			],
			[
				'<ul></li><li>a</li></ul>',
				"[list]\n\t[li]a[/li]\n[/list]",
			],
			[
				'</ul><ul><li>a</li></ul>',
				"[list]\n\t[li]a[/li]\n[/list]",
			],
			[
				'<ol><li>a</li></ul>',
				"[list type=decimal]\n\t[li]a[/li]\n[/list]",
			],
			[
				'<ul type="square"><li>a</li></ul>',
				"[list type=square]\n\t[li]a[/li]\n[/list]",
			],
			[
				'<ul>a',
				"[list]\n\t[li]a[/li]\n[/list]",
			],
			[
				'<ul><li><ul>a',
				"[list]\n\t[li][list]\n\t\t[li]a[/li]\n\t[/list][/li]\n[/list]",
			],
			[
				'a<script>b</script>c',
				"ac",
			],
			[
				'a<!-- b<ul>c -->d',
				"ad",
			],
			[
				'a<![CDATA[b<ul>c]]>d',
				"ad",
			],
			[
				'a<style>b</style>c',
				"ac",
			],
		];
	}

	/**
	 * @dataProvider html_to_bbcProvider
	 *
	 */
	public function testHtmlToBbc(string $test, string $expected): void
	{
		$this->assertEquals($expected, html_to_bbc($test));
	}

	/**
	 * @return string[][]
	 */
	public function smileyProvider(): array
	{
		global $context, $boardurl;

		return [
			[
				'abc :) def',
				'abc <img src="' . $boardurl . '/Smileys/fugue/smiley.png" alt="&#58;&#41;" title="Smiley" class="smiley"> def',
			],
			[
				'abc :)',
				'abc <img src="' . $boardurl . '/Smileys/fugue/smiley.png" alt="&#58;&#41;" title="Smiley" class="smiley">',
			],
			[
				':) def',
				'<img src="' . $boardurl . '/Smileys/fugue/smiley.png" alt="&#58;&#41;" title="Smiley" class="smiley"> def',
			],
			[
				'abc:)def',
				'abc:)def',
			],
			[
				'[url=mailto:David@bla.com]',
				'[url=mailto:David@bla.com]',
			],
		];
	}

	/**
	 * @dataProvider smileyProvider
	 *
	 */
	public function testSmiley(string $test, string $expected): void
	{
		parsesmileys($test);
		$this->assertEquals($expected, $test);

		//~ $result = parse_bbc($test, false);
		//~ $this->assertEquals($expected, $result);
	}

	/**
	 * @return string[][]
	 */
	public function legacyBBCProvider(): array
	{
		global $context;

		return [
			[
				'tt',
				'[tt]blah[/tt]',
				'&#91;tt]blah&#91;/tt]',
				'<span class="monospace">blah</span>',
			],
			[
				'flash',
				'[flash]blah[/flash]',
				'&#91;flash]http://blah&#91;/flash]',
			],
			[
				'bdo',
				'[bdo=rtl]blah[/bdo]',
				'&#91;bdo=rtl]blah&#91;/bdo]',
			],
			[
				'black',
				'[black]blah[/black]',
				'[color=black]blah[/color]',
			],
			[
				'white',
				'[white]blah[/white]',
				'[color=white]blah[/color]',
			],
			[
				'red',
				'[red]blah[/red]',
				'[color=red]blah[/color]',
			],
			[
				'green',
				'[green]blah[/green]',
				'[color=green]blah[/color]',
			],
			[
				'blue',
				'[blue]blah[/blue]',
				'[color=blue]blah[/color]',
			],
			[
				'acronym',
				'[acronym]blah[/acronym]',
				'&#91;acronym]blah&#91;/acronym]',
			],
			[
				'ftp',
				'[ftp]blah[/ftp]',
				'&#91;ftp]ftp://blah&#91;/ftp]',
			],
			[
				'glow',
				'[glow]blah[/glow]',
				'&#91;glow]blah&#91;/glow]',
			],
			[
				'move',
				'[move]blah[/move]',
				'&#91;move]blah&#91;/move]',
			],
			[
				'shadow',
				'[shadow]blah[/shadow]',
				'&#91;shadow]blah&#91;/shadow]',
			],
		];
	}

	/**
	 * @dataProvider legacyBBCProvider
	 *
	 */
	public function testLegacyBBC($tag, string $test, string $expected): void
	{
		preparsecode($test);
		$this->assertEquals($expected, $test);

		$this->assertContains($tag, array_column(parse_bbc(false), 'tag'));

		//~ $result = parse_bbc($test, false);
		//~ $this->assertEquals($expected, $result);
	}

	public function te6stMessageCache(): void
	{
		global $cache_enable, $context, $modSettings, $smcFunc, $scripturl, $txt, $user_info;

		$cache_enable = 2;
		loadCacheAccelerator();
		$message = str_repeat('0', 1001);
		$key = 'message-cache-test';
		$expected = parse_bbc($message, false, $key);
		$cache_enable = 0;
		$actual = cache_get_data(
			'parse:' . $key . '-' . md5(md5($message) . '-' . false . $modSettings['disabledBBC'] . $smcFunc['json_encode']($context['browser']) . $txt['lang_locale'] . $user_info['time_offset'] . $user_info['time_format']),
			240
		);
		$this->assertEquals($expected, $actual);
	}
}