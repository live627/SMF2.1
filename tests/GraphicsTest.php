<?php

declare(strict_types=1);

namespace SMF\Tests;

use PHPUnit\Framework\TestCase;

use gif_file;

/**
 * Needs the fixture server running: node server.js
 * Override the address with the TEST_SERVER_URL environment variable.
 */
class GraphicsTest extends TestCase
{
	public static function setUpBeforeClass(): void
	{
		require_once __DIR__ . '/../Sources/Class-Graphics.php';
		require_once __DIR__ . '/../Sources/Subs-Graphics.php';
		require_once __DIR__ . '/../Sources/ManageAttachments.php';
	}

	public function tearDown(): void
	{
		removeAttachments(['id_member' => 1]);
	}

	private static function baseUrl(): string
	{
		return rtrim(getenv('TEST_SERVER_URL') ?: 'http://localhost:8125', '/');
	}

	/**
	 * Fixtures live in tests/packages and are served by server.js.
	 *
	 * @return array[]  [path, width, height, IMAGETYPE_*]
	 */
	public function data(): array
	{
		return [
			'jpeg banner' => ['images/profile_banner.jpg', 1080, 360, IMAGETYPE_JPEG],
			'png logo' => ['images/weblogs.png', 432, 78, IMAGETYPE_PNG],
			'gif logo' => ['images/logo.gif', 276, 110, IMAGETYPE_GIF],
			'jpeg portrait' => ['images/Landscape_5.jpg', 1200, 1800, IMAGETYPE_JPEG],
		];
	}

	/**
	 * @dataProvider data
	 */
	public function test(string $path, int $width, int $height, int $type): void
	{
		global $modSettings, $smcFunc;

		$url = self::baseUrl() . '/' . $path;

		$headers = @get_headers($url);
		if ($headers === false || strpos($headers[0], '200') === false)
			$this->markTestSkipped('Could not fetch from ' . $url . '; is "node server.js" running?');

		// Make sure the fixture itself matches what the provider claims.
		$info = getimagesize(__DIR__ . '/packages/' . $path);
		$this->assertSame([$width, $height, $type], [$info[0], $info[1], $info[2]]);

		$success = downloadAvatar($url, 1, 100, 100);
		$this->assertTrue($success);

		$request = $smcFunc['db_query'](
			'',
			'
			SELECT filename, size
			FROM {db_prefix}attachments
			WHERE id_member = 1
				AND attachment_type = 1'
		);
		[$filename, $size] = $smcFunc['db_fetch_row']($request);
		$smcFunc['db_free_result']($request);

		$this->assertFileExists($modSettings['custom_avatar_dir'] . '/' . $filename);
		$this->assertEquals($size, filesize($modSettings['custom_avatar_dir'] . '/' . $filename));
		$this->assertSame(['ok', false, 2], attachDirStatus($modSettings['custom_avatar_dir'], 1));
		$this->assertTrue(checkImageContents($modSettings['custom_avatar_dir'] . '/' . $filename));
	}

	public function testGif(): void
	{
		touch('vv.gif');
		$this->assertFileExists('vv.gif');
		$gif = new gif_file;
		$gif->loadFile('vv.gif', 0);
		$this->assertFalse($gif->loaded);
		$this->assertFalse(gif_outputAsPng($gif, 'vv.png'));
		$this->assertFileNotExists('vv.png');
		unlink('vv.gif');
		$this->assertFileNotExists('vv.gif');
	}

	public function testText(): void
	{
		ob_start();
		$success = showCodeImage('test');
		$image = ob_get_contents();
		ob_end_clean();
		$this->assertTrue($success !== false);
		file_put_contents('vv.gif', $image);
		$this->assertFileExists('vv.gif');
		$gif = new gif_file;
		$gif->loadFile('vv.gif', 0);
		$this->assertTrue(gif_outputAsPng($gif, 'vv.png'));
		$this->assertFileExists('vv.png');
		unlink('vv.png');
		$this->assertFileNotExists('vv.png');
		unlink('vv.gif');
		$this->assertFileNotExists('vv.gif');
	}
}