<?php

declare(strict_types=1);

namespace SMF\Tests;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

use gif_file;

class GraphicsTest extends TestCase
{
	public static function setUpBeforeClass(): void
	{
		require_once __DIR__ . '/../Sources/Class-Graphics.php';
		require_once __DIR__ . '/../Sources/Subs-Graphics.php';
		require_once __DIR__ . '/../Sources/ManageAttachments.php';
	}

	protected function tearDown(): void
	{
		removeAttachments(['id_member' => 1]);
	}

	public function data(): array
	{
		return [
			[
				'https://pbs.twimg.com/profile_banners/2420838708/1605797668/1080x360',
				1080,
				360,
				IMAGETYPE_JPEG,
			],
			[
				'url' => 'http://weblogs.us/images/weblogs.us.png',
				432,
				78,
				IMAGETYPE_PNG,
			],
			[
				'url' => 'http://www.google.com/intl/en_ALL/images/logo.gif',
				276,
				110,
				IMAGETYPE_GIF,
			],
			[
				'url' => 'https://raw.githubusercontent.com/recurser/exif-orientation-examples/master/Landscape_5.jpg',
				1200,
				1800,
				IMAGETYPE_PNG,
			],
		];
	}

	#[\PHPUnit\Framework\Attributes\DataProvider('data')]
    #[Group('slow')]
    public function test(string $url): void
	{
		global $modSettings, $smcFunc;

		if (strpos(get_headers($url)[0], '200') === false)
			$this->markTestSkipped('Could not fetch from ' . $url . '; skipping this test method');

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
