<?php

declare(strict_types=1);
namespace SMF\Tests;

use PHPUnit\Framework\TestCase;

final class SecurityTest extends TestCase
{
	//~ public function domainsProvider(): array
	//~ {
		//~ return [
			//~ ['www.example.com', 'example.com'],
			//~ ['example.com', 'example.com'],
			//~ ['example.com.br', 'example.com.br'],
			//~ ['www.example.com.br', 'example.com.br'],
			//~ ['www.example.gov.br', 'example.gov.br'],
			//~ ['www.subdomain.example.com', 'example.com'],
			//~ ['subdomain.example.com', 'example.com'],
			//~ ['subdomain.example.com.br', 'example.com.br'],
			//~ ['www.subdomain.example.com.br', 'example.com.br'],
			//~ ['www.subdomain.example.biz.br', 'example.biz.br'],
			//~ ['subdomain.example.biz.br', 'example.biz.br'],
			//~ ['subdomain.example.net', 'example.net'],
			//~ ['www.subdomain.example.net', 'example.net'],
			//~ ['www.subdomain.example.co.kr', 'example.co.kr'],
			//~ ['subdomain.example.co.kr', 'example.co.kr'],
			//~ ['example.co.kr', 'example.co.kr'],
			//~ ['example.jobs', 'example.jobs'],
			//~ ['www.example.jobs', 'example.jobs'],
			//~ ['subdomain.example.jobs', 'example.jobs'],
			//~ ['insane.subdomain.example.jobs', 'example.jobs'],
			//~ ['insane.subdomain.example.com.br', 'example.com.br'],
			//~ ['www.doubleinsane.subdomain.example.com.br', 'example.com.br'],
			//~ ['www.subdomain.example.jobs', 'example.jobs'],
			//~ ['www.detran.sp.gov.br', 'sp.gov.br'],
			//~ ['www.mp.sp.gov.br', 'sp.gov.br'],
			//~ ['ny.library.museum', 'library.museum'],
			//~ ['www.ny.library.museum', 'library.museum'],
			//~ ['ny.ny.library.museum', 'library.museum'],
			//~ ['www.library.museum', 'library.museum'],
			//~ ['info.abril.com.br', 'abril.com.br'],
		//~ ];
	//~ }

	//~ /**
	 //~ * @dataProvider domainsProvider
	 //~ */
	//~ public function testDomains(string $test, string $expected): void
	//~ {
		//~ $result = get_domain($test);
		//~ $this->assertEquals($expected, $result);
	//~ }

	//~ public function autolinkProvider(): array
	//~ {
		//~ return [
			//~ [
				//~ 'http://example.com/foo',
				//~ 'example.com',
				//~ 'example.com',
			//~ ],
			//~ [
				//~ 'http://www.example.com/foo',
				//~ 'example.com',
				//~ 'example.com',
			//~ ],
			//~ [
				//~ 'http://toaster.example.com/foo',
				//~ 'toaster.example.com',
				//~ 'example.com',
			//~ ],
			//~ [
				//~ 'example.com',
				//~ 'example.com',
				//~ 'example.com',
			//~ ],
			//~ [
				//~ 'www.example.com',
				//~ 'example.com',
				//~ 'example.com',
			//~ ],
			//~ [
				//~ 'toaster.example.com',
				//~ 'toaster.example.com',
				//~ 'example.com',
			//~ ],
			//~ [
				//~ 'something random',
				//~ 'something random',
				//~ 'something random',
			//~ ],
		//~ ];
	//~ }

	//~ /**
	 //~ * @dataProvider autolinkProvider
	 //~ */
	//~ public function testAutolink(string $test, string $expected, string $expected2): void
	//~ {
		//~ $result = FindCorsBaseUrl($test);
		//~ $this->assertEquals($expected, $result);
		//~ $result = FindCorsBaseUrl($test, true);
		//~ $this->assertEquals($expected2, $result);
	//~ }

	//~ public function testMalformedDomain(): void
	//~ {
		//~ $this->assertNull(FindCorsBaseUrl('trash'));
	//~ }

	public function testCorsPolicyHeader(): void
	{
		global $boardurl, $context, $modSettings;

		$modSettings['allow_cors'] = 1;
		$modSettings['globalCookies'] = 1;
		$_SERVER['HTTP_ORIGIN'] = $boardurl;

		corsPolicyHeader(false);
		$this->assertEquals('same', $context['valid_cors_found']);
		$this->assertStringStartsWith($context['cors_domain'], $boardurl);

		$modSettings['allow_cors'] = 0;
		$context['cors_domain'] = 0;
		$modSettings['globalCookies'] = 0;
		$_SERVER['HTTP_ORIGIN'] = '';
	}

	public function corsDomainAliasProvider(): array
	{
		return [
			['http://example.com'],
			['http://www.thisistheway.com'],
			['http://toaster.food.com'],
		];
	}

	#[\PHPUnit\Framework\Attributes\DataProvider('corsDomainAliasProvider')]
    public function testCorsAlias(string $domain): void
	{
		global $context, $modSettings;

		$modSettings['allow_cors'] = 1;
		$modSettings['forum_alias_urls'] = 'http://example.com,http://www.thisistheway.com,http://toaster.food.com';

		$context['cors_domain'] = '';
		$_SERVER['HTTP_ORIGIN'] = $domain;
		corsPolicyHeader(false);
		$this->assertEquals('alias', $context['valid_cors_found']);
		$this->assertEquals($domain, $context['cors_domain']);

		$modSettings['allow_cors'] = 0;
		$context['cors_domain'] = '';
		$modSettings['forum_alias_urls'] = '';
		$_SERVER['HTTP_ORIGIN'] = '';
	}

	public function corsDomainProvider(): array
	{
		return [
			['http://example.com'],
			['http://www.example.com'],
			['http://toaster.example.com'],
		];
	}

	#[\PHPUnit\Framework\Attributes\DataProvider('corsDomainProvider')]
    public function testCorsAdditional(string $domain): void
	{
		global $context, $modSettings;

		$modSettings['allow_cors'] = 1;
		$modSettings['cors_domains'] = 'http://example.com,http://www.example.com,http://toaster.example.com';

		$context['cors_domain'] = '';
		$_SERVER['HTTP_ORIGIN'] = $domain;
		corsPolicyHeader(false);
		$this->assertEquals('additional', $context['valid_cors_found']);
		$this->assertEquals($domain, $context['cors_domain']);

		$modSettings['allow_cors'] = 0;
		$context['cors_domain'] = '';
		$modSettings['cors_domains'] = '';
		$_SERVER['HTTP_ORIGIN'] = '';
	}

	#[\PHPUnit\Framework\Attributes\DataProvider('corsDomainProvider')]
    public function testCorsAdditionalWildcard(string $domain): void
	{
		global $context, $modSettings;

		$modSettings['allow_cors'] = 1;
		$modSettings['cors_domains'] = 'http://*.example.com';

		$context['cors_domain'] = '';
		$_SERVER['HTTP_ORIGIN'] = $domain;
		corsPolicyHeader(false);
		$this->assertEquals('additional_wildcard', $context['valid_cors_found']);
		$this->assertEquals($domain, $context['cors_domain']);

		$modSettings['allow_cors'] = 0;
		$context['cors_domain'] = '';
		$modSettings['cors_domains'] = '';
		$_SERVER['HTTP_ORIGIN'] = '';
	}
}