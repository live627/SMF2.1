<?php

declare(strict_types=1);

namespace SMF\Tests;

use PHPUnit\Framework\TestCase;

final class PackagesTest extends TestCase
{
	public static function setUpBeforeClass(): void
	{
		require_once __DIR__ . '/../Sources/PackageGet.php';
		require_once __DIR__ . '/../Sources/Packages.php';
		require_once __DIR__ . '/../Sources/Subs-Package.php';
	}

	protected function tearDown(): void
	{
		unset($_REQUEST, $_GET);
	}

	public function testPackageBrowse(): void
	{
		global $context;

		loadLanguage('Admin');
		$context['admin_menu_name'] = 'admin';
		Packages();
		$this->assertEquals('browse', $context['sub_action']);
		$this->assertEquals('browse', $context['sub_template']);
		$this->assertEquals('packages_lists', $context['default_list']);
	}

	public function testPackageDownload(): void
	{
		global $context;

		$_GET[$context['session_var']] = $context['session_id'];
		$_GET[$_SESSION['session_var']] = $_SESSION['session_value'];
		$_REQUEST['package'] = 'http://127.0.0.1:8125/SimpleDesk_2.1.0.tgz';
		loadLanguage('Packages');
		PackageDownload();
		$this->assertEquals('Package downloaded successfully', $context['page_title']);
		$this->assertEquals('SimpleDesk - Integrated Helpdesk for Simple Machines Forum', $context['package']['name']);
	}

	#[\PHPUnit\Framework\Attributes\Depends('testPackageDownload')]
    public function testPackageDownloaded(): void
	{
		global $context;

		PackageBrowse();
		$this->assertEquals('idmodification', $context['packages_lists_modification']['sort']['id']);
		$this->assertCount(1, $context['packages_lists_modification']['rows']);
		$this->assertEquals(
			'SimpleDesk - Integrated Helpdesk for Simple Machines Forum',
			$context['packages_lists_modification']['rows'][0]['data']['mod_namemodification']['value']
		);
	}

	#[\PHPUnit\Framework\Attributes\Depends('testPackageDownload')]
    public function testActionInstall(): void
	{
		global $context;

		$_REQUEST['package'] = 'SimpleDesk_2.1.0.tgz';
		$_REQUEST['sa'] = 'install';
		Packages();
		$this->assertFalse($context['is_installed']);
		$this->assertFalse($context['uninstalling']);
		$this->assertCount(49, $context['actions']);
		$this->assertEquals(
			'<strong>Test successful</strong>',
			$context['actions'][7]['description'],
			$context['actions'][7]['description']
		);
	}

	#[\PHPUnit\Framework\Attributes\Depends('testPackageDownload')]
    public function testPackageRemove(): void
	{
		global $context;

		$_GET[$context['session_var']] = $context['session_id'];
		$_GET[$_SESSION['session_var']] = $_SESSION['session_value'];
		$this->assertFileExists(__DIR__ . '/../packages/SimpleDesk_2.1.0.tgz');
		$_GET['package'] = 'SimpleDesk_2.1.0.tgz';
		PackageRemove();
		$this->assertFileNotExists(__DIR__ . '/../packages/SimpleDesk_2.1.0.tgz');
	}

	public function zipProvider(): array
	{
		return [
			['store.zip'],
			['deflate.zip'],
			['deflate-ultra.zip'],
			['spanned.zip'], // has magic number 0x08074b50
		];
	}

	#[\PHPUnit\Framework\Attributes\DataProvider('zipProvider')]
    public function testZip(string $filename): void
	{
		$file_info = read_tgz_file(
			__DIR__ . '/packages/' . $filename,
			__DIR__ . '/packages/tmp'
		);
		$this->assertCount(1, $file_info);
		$this->assertFileExists(__DIR__ . '/packages/tmp/Untitled.bmp');
		unlink(__DIR__ . '/packages/tmp/Untitled.bmp');
		$this->assertFileNotExists(__DIR__ . '/packages/tmp/Untitled.bmp');
		$this->assertEquals('Untitled.bmp', $file_info[0]['filename']);
	}

	public function testSpannedZip(): void
	{
		$file_info = read_tgz_file(
			__DIR__ . '/packages/spanned.zip',
			__DIR__ . '/packages/tmp'
		);
		$this->assertCount(1, $file_info);
		$this->assertFileExists(__DIR__ . '/packages/tmp/Untitled.bmp');
		unlink(__DIR__ . '/packages/tmp/Untitled.bmp');
		$this->assertFileNotExists(__DIR__ . '/packages/tmp/Untitled.bmp');
		$this->assertEquals('Untitled.bmp', $file_info[0]['filename']);
	}

	public function testEmptyZip(): void
	{
		$this->assertSame([], read_tgz_file(__DIR__ . '/packages/empty.zip', __DIR__ . '/packages/tmp'));
	}

	public function testZipNotSupported(): void
	{
		$this->assertSame([], read_tgz_file(__DIR__ . '/packages/LZMA.zip', __DIR__ . '/packages/tmp'));
	}

	public function testLongLink(): void
	{
		$file_info = read_tgz_file(
			__DIR__ . '/packages/longlink.tar.gz',
			__DIR__ . '/packages/tmp'
		);
		$this->assertFileExists(__DIR__ . '/packages/tmp/s.txt');
		unlink(__DIR__ . '/packages/tmp/s.txt');
		$this->assertFileNotExists(__DIR__ . '/packages/tmp/s.txt');
		$this->assertCount(2, $file_info);
		$this->assertEquals(
			'01234567801234567801234567801234567801234567801234567801234567801234567801234567899999999901234567890123456780123456780123456780123456780123456780123456780123456780123456780123456789999999990123456789.txt',
			$file_info[0]['filename']
		);
		$this->assertEquals('Got it', $file_info[0]['preview']);
		$this->assertEquals('s.txt', $file_info[1]['filename']);
		$this->assertEquals('Short one', $file_info[1]['preview']);
	}

	public function testPax(): void
	{
		$file_info = read_tgz_file(
			__DIR__ . '/packages/pax_test.tgz',
			__DIR__ . '/packages/tmp'
		);
		$this->assertFileExists(__DIR__ . '/packages/tmp/4слайд-1.jpg');
		$this->assertEquals(15251, filesize(__DIR__ . '/packages/tmp/4слайд-1.jpg'));
		$this->assertFileExists(__DIR__ . '/packages/tmp/4слайд-2.jpg');
		$this->assertEquals(16671, filesize(__DIR__ . '/packages/tmp/4слайд-2.jpg'));
		$this->assertFileExists(__DIR__ . '/packages/tmp/4слайд.jpg');
		$this->assertEquals(214949, filesize(__DIR__ . '/packages/tmp/4слайд.jpg'));
		deltree(__DIR__ . '/packages/tmp');
		$this->assertFileNotExists(__DIR__ . '/packages/tmp');
		$this->assertCount(6, $file_info);
		$this->assertEquals('./._4слайд-1.jpg', $file_info[0]['filename']);
		$this->assertEquals('./4слайд-1.jpg', $file_info[1]['filename']);
		$this->assertEquals(15251, $file_info[1]['size']);
		$this->assertEquals('./._4слайд-2.jpg', $file_info[2]['filename']);
		$this->assertEquals('./4слайд-2.jpg', $file_info[3]['filename']);
		$this->assertEquals(16671, $file_info[3]['size']);
		$this->assertEquals('./._4слайд.jpg', $file_info[4]['filename']);
		$this->assertEquals('./4слайд.jpg', $file_info[5]['filename']);
	}

	public function nestedProvider(): array
	{
		return [['nested.tar.gz'], ['nested.zip']];
	}

	#[\PHPUnit\Framework\Attributes\DataProvider('nestedProvider')]
    public function testNested(string $filename): void
	{
		$this->assertFalse(read_tgz_file(__DIR__ . '/packages/' . $filename, 's.txt', true));
		$this->assertEquals('Short one', read_tgz_file(__DIR__ . '/packages/' . $filename, 's/s.txt', true));
		$this->assertEquals('Short one', read_tgz_file(__DIR__ . '/packages/' . $filename, '*/s.txt', true));
		$this->assertFileNotExists(__DIR__ . '/packages/tmp/s');
		$this->assertFileNotExists(__DIR__ . '/packages/tmp/s/s.txt');
	}

	#[\PHPUnit\Framework\Attributes\DataProvider('nestedProvider')]
    public function testListNested(string $filename): void
	{
		$file_info = read_tgz_file(__DIR__ . '/packages/' . $filename, null);
		$this->assertCount(1, $file_info);
		$this->assertFileNotExists(__DIR__ . '/packages/tmp/s');
		$this->assertFileNotExists(__DIR__ . '/packages/tmp/s/s.txt');
		$this->assertEquals('s/s.txt', $file_info[0]['filename']);
	}

	#[\PHPUnit\Framework\Attributes\DataProvider('nestedProvider')]
    public function testListRestrictedNested(string $filename): void
	{
		$file_info = read_tgz_file(__DIR__ . '/packages/' . $filename, null, false, false, ['s/s.txt']);
		$this->assertCount(1, $file_info);
		$this->assertFileNotExists(__DIR__ . '/packages/tmp/s');
		$this->assertFileNotExists(__DIR__ . '/packages/tmp/s/s.txt');
		$this->assertEquals('s/s.txt', $file_info[0]['filename']);
	}

	#[\PHPUnit\Framework\Attributes\DataProvider('nestedProvider')]
    public function testInvalidRestrictedNested(string $filename): void
	{
		$file_info = read_tgz_file(__DIR__ . '/packages/' . $filename, __DIR__ . '/packages/tmp', false, false, ['a']);
		$this->assertIsArray($file_info);
		$this->assertEmpty($file_info);
		$this->assertFileExists(__DIR__ . '/packages/tmp/s');
		$this->assertFileNotExists(__DIR__ . '/packages/tmp/s/s.txt');
	}

	public function compareVersionsProvider(): array
	{
		return [
			['0', '1'],
			['0.1', '1'],
			['0.0.1', '1'],
			['2', '2.0.1'],
			['2.1dev', '2.2dev'],
			['2.1dev', '2.1beta'],
			['2.1dev', '2.1'],
			['2.1alpha', '2.1beta'],
			['2.1alpha2', '2.1alpha3'],
			['2.1alpha4', '2.1beta2'],
			['2.1beta1', '2.1beta2'],
			['2.1beta1.1', '2.1beta1.2'],
			['2.1alpha', '2.1'],
			['2.1-dev', '2.2-dev'],
			['2.1-dev', '2.1-beta'],
			['2.1-dev', '2.1-'],
			['2.1-alpha', '2.1-beta'],
			['2.1-alpha.2', '2.1-alpha.3'],
			['2.1-alpha.4', '2.1-beta.2'],
			['2.1-beta.1', '2.1-beta.2'],
			['2.1-beta.1.1', '2.1-beta.1.2'],
			['2.1-alpha', '2.1'],
		];
	}

	#[\PHPUnit\Framework\Attributes\DataProvider('compareVersionsProvider')]
    public function testCompareVersions(string $version1, string $version2): void
	{
		$this->assertSame(-1, compareVersions($version1, $version2));
		$this->assertSame(1, compareVersions($version2, $version1));
		$this->assertSame(0, compareVersions($version1, $version1));
		$this->assertSame(0, compareVersions($version2, $version2));
	}

	public function matchHighestPackageVersionProvider(): array
	{
		return [
			['2', '2 - 2.0.1', '2.1'],
			['2', '2 - 2.0.1, 1.0-1.2', '2.1'],
			['2', '1.0-1.2,2 - 2.0.1', '2.1'],
			['2', '1.*, 2 - 2.0.1', '2.1'],
		];
	}

	#[\PHPUnit\Framework\Attributes\DataProvider('matchHighestPackageVersionProvider')]
    public function testMatchHighestPackageVersion(string $expected, string $range, string $version): void
	{
		$this->assertSame($expected, matchHighestPackageVersion($range, false, $version));
	}

	#[\PHPUnit\Framework\Attributes\DataProvider('matchHighestPackageVersionProvider')]
    public function testMatchHighestPackageVersion2(string $expected, string $range, string $version): void
	{
		$this->assertSame($expected, matchHighestPackageVersion($range, true, $version));
	}

	public function matchPackageVersionProvider(): array
	{
		return [
			['2 - 2.1', '2.1'],
			['2 - 2.0.1, 1.0-1.2', '1.1'],
			['1.0-1.2,2 - 2.0.1', '1.1.1'],
			['1.*, 2 - 2.0.1', '1.2.1'],
		];
	}

	#[\PHPUnit\Framework\Attributes\DataProvider('matchPackageVersionProvider')]
    public function testMatchPackageVersion(string $range, string $version): void
	{
		$this->assertTrue(matchPackageVersion($version, $range));
	}
}
