<?php

namespace PHPTDD;

class SoundTest extends BaseTestCase
{
	public function setUp() : void
	{
		global $sourcedir;

		require_once($sourcedir . '/Subs-Sound.php');
	}

	public function test()
	{
		ob_start();
		$success = createWaveFile('test');
		$image = ob_get_contents();
		ob_end_clean();
		$this->assertTrue($success !== false);
		file_put_contents('ppk.wav', $image);
		if (($fp = fopen('ppk.wav', 'r')) !== false)
		{
			fseek($fp, 20);
			$d = fread($fp, 24);
			fclose($fp);
			$data = unpack('vfmt/vch/Vsr/Vdr/vbs/vbis/Vext/Vds', $d);
			$this->assertEquals(128000, $data['dr']); // data rate
			$this->assertEquals(1, $data['ch']); // channels
			$this->assertEquals(16000, $data['sr']); // samples rate
			$this->assertEquals(8, $data['bis']); // bits per sample
			$this->assertEquals(filesize('ppk.wav') - 44, $data['ds']); // data size
			$this->assertTrue(file_exists('ppk.wav'));
			unlink('ppk.wav');
			$this->assertFalse(file_exists('ppk.wav'));
		}
		else
			$this->fail('Could not write ppk.wav to the filesystem');
	}
}