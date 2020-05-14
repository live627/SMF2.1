<?php

namespace PHPTDD;

use gif_file;

class GraphicsTest extends BaseTestCase
{
	public function setUp() : void
	{
		global $sourcedir;

		require_once($sourcedir . '/Class-Graphics.php');
		require_once($sourcedir . '/Subs-Graphics.php');
		require_once($sourcedir . '/ManageAttachments.php');
	}

	public function tearDown() : void
	{
		removeAttachments(array('id_member' => 1));
	}

	public function data()
	{
		return array(
			array(
				'url' => 'https://images.pexels.com/photos/753626/pexels-photo-753626.jpeg',
				'width' => 2000,
				'height' => 1335,
				'format' => IMAGETYPE_JPEG
			),
			array(
				'url' => 'http://weblogs.us/images/weblogs.us.png',
				'width' => 432,
				'height' => 78,
				'format' => IMAGETYPE_PNG
			),
			array(
				'url' => 'http://www.google.com/intl/en_ALL/images/logo.gif',
				'width' => 276,
				'height' => 110,
				'format' => IMAGETYPE_GIF
			),
			array(
				'url' => 'https://raw.githubusercontent.com/recurser/exif-orientation-examples/master/Landscape_5.jpg',
				'width' => 1200,
				'height' => 1800,
				'format' => IMAGETYPE_PNG
			)
		);
	}

	/**
	 * @dataProvider data
	 * @group slow
	 */
	public function test(string $url)
	{
		global $modSettings, $smcFunc;

		if (strpos(get_headers($url)[0], '200') === false)
			$this->markTestSkipped('Could not fetch from ' . $url . '; skipping this test method');

		$success = downloadAvatar($url, 1, 100, 100);
		$this->assertTrue($success);

		$request = $smcFunc['db_query']('', '
			SELECT filename, size
			FROM {db_prefix}attachments
			WHERE id_member = 1
				AND attachment_type = 1');
		list ($filename, $size) = $smcFunc['db_fetch_row']($request);
		$smcFunc['db_free_result']($request);

		$this->assertTrue(file_exists($modSettings['custom_avatar_dir'] . '/' . $filename));
		$this->assertEquals($size, filesize($modSettings['custom_avatar_dir'] . '/' . $filename));
		$this->assertSame(array('ok', false, 2), attachDirStatus($modSettings['custom_avatar_dir'], 1));
		$this->assertTrue(checkImageContents($modSettings['custom_avatar_dir'] . '/' . $filename));
	}

	public function testGif()
	{
		touch('vv.gif');
		$this->assertTrue(file_exists('vv.gif'));
		$gif = new gif_file;
		$gif->loadFile('vv.gif', 0);
		$this->assertFalse($gif->loaded);
		$this->assertFalse(gif_outputAsPng($gif, 'vv.png'));
		$this->assertFalse(file_exists('vv.png'));
		unlink('vv.gif');
		$this->assertFalse(file_exists('vv.gif'));
	}

	public function testText()
	{
		ob_start();
		$success = showCodeImage('test');
		$image = ob_get_contents();
		ob_end_clean();
		$this->assertTrue($success !== false);
		file_put_contents('vv.gif', $image);
		$this->assertTrue(file_exists('vv.gif'));
		$gif = new gif_file;
		$gif->loadFile('vv.gif', 0);
		$this->assertTrue(gif_outputAsPng($gif, 'vv.png'));
		$this->assertTrue(file_exists('vv.png'));
		unlink('vv.png');
		$this->assertFalse(file_exists('vv.png'));
		unlink('vv.gif');
		$this->assertFalse(file_exists('vv.gif'));
	}
}