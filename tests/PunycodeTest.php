<?php

namespace PHPTDD;

use Punycode as Punycode;

class PunycodeTest extends BaseTestCase
{

	public function setUp() : void
	{
		global $sourcedir, $user_info;

		require_once($sourcedir . '/Class-Punycode.php');
	}

	/**
	 *     * Test encoding Punycode
	 *     *
	 *
	 * @param string $decoded Decoded domain
	 * @param string $encoded Encoded domain
	 *
	 * @dataProvider domainNamesProvider
	 *
	 * @return void
	 */
	public function testEncode($decoded, $encoded): void
	{
		$Punycode = new Punycode();
		$result = $Punycode->encode($decoded);
		$this->assertEquals($encoded, $result);
	}

	/**
	 *     * Test decoding Punycode
	 *     *
	 *
	 * @param string $decoded Decoded domain
	 * @param string $encoded Encoded domain
	 *
	 * @dataProvider domainNamesProvider
	 *
	 * @return void
	 */
	public function testDecode($decoded, $encoded): void
	{
		$Punycode = new Punycode();
		$result = $Punycode->decode($encoded);
		$this->assertEquals($decoded, $result);
	}

	/**
	 *     * Test encoding Punycode in uppercase
	 *     *
	 *
	 * @param string $decoded Decoded domain
	 * @param string $encoded Encoded domain
	 *
	 * @dataProvider domainNamesProvider
	 *
	 * @return void
	 */
	public function testEncodeUppercase($decoded, $encoded): void
	{
		$Punycode = new Punycode();
		$result = $Punycode->encode(mb_strtoupper($decoded, 'UTF-8'));
		$this->assertEquals($encoded, $result);
	}

	/**
	 *     * Test decoding Punycode in uppercase
	 *     *
	 *
	 * @param string $decoded Decoded domain
	 * @param string $encoded Encoded domain
	 *
	 * @dataProvider domainNamesProvider
	 *
	 * @return void
	 */
	public function testDecodeUppercase($decoded, $encoded): void
	{
		$Punycode = new Punycode();
		$result = $Punycode->decode(strtoupper($encoded));
		$this->assertEquals($decoded, $result);
	}

	/**
	 * Provide domain names containing the decoded and encoded names
	 *
	 * @return array
	 */
	public function domainNamesProvider()
	{
		return [
			// http://en.wikipedia.org/wiki/.test_(international_domain_name)#Test_TLDs
			[
				'مثال.إختبار',
				'xn--mgbh0fb.xn--kgbechtv',
			],
			[
				'مثال.آزمایشی',
				'xn--mgbh0fb.xn--hgbk6aj7f53bba',
			],
			[
				'例子.测试',
				'xn--fsqu00a.xn--0zwm56d',
			],
			[
				'例子.測試',
				'xn--fsqu00a.xn--g6w251d',
			],
			[
				'пример.испытание',
				'xn--e1afmkfd.xn--80akhbyknj4f',
			],
			[
				'उदाहरण.परीक्षा',
				'xn--p1b6ci4b4b3a.xn--11b5bs3a9aj6g',
			],
			[
				'παράδειγμα.δοκιμή',
				'xn--hxajbheg2az3al.xn--jxalpdlp',
			],
			[
				'실례.테스트',
				'xn--9n2bp8q.xn--9t4b11yi5a',
			],
			[
				'בײַשפּיל.טעסט',
				'xn--fdbk5d8ap9b8a8d.xn--deba0ad',
			],
			[
				'例え.テスト',
				'xn--r8jz45g.xn--zckzah',
			],
			[
				'உதாரணம்.பரிட்சை',
				'xn--zkc6cc5bi7f6e.xn--hlcj6aya9esc7a',
			],
			[
				'derhausüberwacher.de',
				'xn--derhausberwacher-pzb.de',
			],
			[
				'renangonçalves.com',
				'xn--renangonalves-pgb.com',
			],
			[
				'рф.ru',
				'xn--p1ai.ru',
			],
			[
				'δοκιμή.gr',
				'xn--jxalpdlp.gr',
			],
			[
				'ফাহাদ্১৯.বাংলা',
				'xn--65bj6btb5gwimc.xn--54b7fta0cc',
			],
			[
				'𐌀𐌖𐌋𐌄𐌑𐌉·𐌌𐌄𐌕𐌄𐌋𐌉𐌑.gr',
				'xn--uba5533kmaba1adkfh6ch2cg.gr',
			],
			[
				'guangdong.广东',
				'guangdong.xn--xhq521b',
			],
			[
				'gwóźdź.pl',
				'xn--gwd-hna98db.pl',
			],
			[
				'άέήίΰαβγδεζηθικλμνξοπρσστυφχ.com',
				'xn--hxacdefghijklmnopqrstuvw0caz0a1a2a.com',
			],
		];
	}
}