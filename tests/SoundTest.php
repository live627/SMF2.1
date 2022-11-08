<?php

declare(strict_types=1);

namespace SMF\Tests;

use PHPUnit\Framework\TestCase;

class SoundTest extends TestCase
{
	public function setUp() : void
	{
		global $sourcedir;

		require_once($sourcedir . '/Subs-Sound.php');
	}

	public function testCreateWaveFile(): void
	{
		ob_start();
		$success = createWaveFile('test');
		$image = ob_get_contents();
		ob_end_clean();
		$this->assertTrue($success !== false);
		file_put_contents('ppk.wav', $image);

		if (($fp = fopen('ppk.wav', 'r')) !== false)
		{
			/*
			 * Positions   Sample Value         Description
			 * 1 - 4       "RIFF"               Marks the file as a riff file. Characters are each 1. byte long.
			 * 5 - 8       File size (integer)  Size of the overall file - 8 bytes, in bytes (32-bit integer).
			 * 9 -12       "WAVE"               File Type Header. For our purposes, it always equals "WAVE".
			 * 13-16       "fmt "               Format chunk marker. Includes trailing nul
			 * 17-20       16                   Length of format data as listed above
			 * 21-22       1                    Type of format (1 is PCM) - 2 byte integer
			 * 23-24       2                    Number of Channels - 2 byte integer
			 * 25-28       44100                Sample Rate - 32 bit integer. Common values are 44100 (CD), 48000 (DAT). Sample Rate = Number of Samples per second, or Hertz.
			 * 29-32       176400               (Sample Rate * BitsPerSample * Channels) / 8.
			 * 33-34       4                    (BitsPerSample * Channels) / 8.1 - 8 bit mono2 - 8 bit stereo/16 bit mono4 - 16 bit stereo
			 * 35-36       16                   Bits per sample
			 * 37-40       "data"               "data" chunk header. Marks the beginning of the data section.
			 * 41-44       File size (data)     Size of the data section, i.e. file size - 44 bytes header.
			 */
			$d = fread($fp, 44);
			$size = filesize('ppk.wav');
			fclose($fp);
			$this->assertFileExists('ppk.wav');
			unlink('ppk.wav');
			$this->assertFileNotExists('ppk.wav');
			$data = unpack('A4id/Vsize/A4type/a4fmt/Vfmtlen/vfmttype/vch/Vsr/Vdr/vbs/vbis/A4data/Vds', $d);
			$this->assertEquals('RIFF', $data['id']);
			$this->assertIsNumeric($data['size']);
			$this->assertEquals($size - 8, $data['size']);
			$this->assertEquals(36 + $data['ds'], $data['size']);
			$this->assertEquals('WAVE', $data['type']);
			$this->assertEquals('fmt ', $data['fmt']);
			$this->assertEquals(16, $data['fmtlen']);
			$this->assertEquals(1, $data['fmttype']);
			$this->assertEquals(1, $data['ch']); // channels
			$this->assertEquals(16000, $data['sr']); // samples rate
			$this->assertEquals(16000, $data['dr']); // data rate
			$this->assertEquals(1, $data['bs']);
			$this->assertEquals(8, $data['bis']); // bits per sample
			$this->assertEquals('data', $data['data']);
			$this->assertEquals($size - 44, $data['ds']); // data size
			$this->assertEquals($data['sr'] * $data['bs'], $data['dr']);
			$this->assertEquals($data['sr'] * $data['bis'] * $data['ch'] / 8, $data['dr']);
			$this->assertEquals($data['bis'] * $data['ch'] / 8, $data['bs']);
		}
		else
			$this->fail('Could not write ppk.wav to the filesystem');
	}

	public function data(): array
	{
		return array_map(fn($x) => [$x], str_split('abcdefghijklmnopqrstuvwxyz'));
	}

	/**
	 * @dataProvider data
	 */
	public function test(string $char): void
	{
		$this->assertFileExists('./Themes/default/fonts/sound/' . $char . '.english.wav');
		if (($fp = fopen('./Themes/default/fonts/sound/' . $char . '.english.wav', 'r')) !== false)
		{
			$d = fread($fp, 44);
			$size = filesize('./Themes/default/fonts/sound/' . $char . '.english.wav');
			fclose($fp);
			$data = unpack('A4id/Vsize/A4type/a4fmt/Vfmtlen/vfmttype/vch/Vsr/Vdr/vbs/vbis/A4data/Vds', $d);
			$this->assertEquals('RIFF', $data['id']);
			$this->assertIsNumeric($data['size']);
			$this->assertEquals($size - 8, $data['size']);
			$this->assertEquals(36 + $data['ds'], $data['size']);
			$this->assertEquals('WAVE', $data['type']);
			$this->assertEquals('fmt ', $data['fmt']);
			$this->assertEquals(16, $data['fmtlen']);
			$this->assertEquals(1, $data['fmttype']);
			$this->assertEquals(1, $data['ch']); // channels
			$this->assertEquals(8000, $data['sr']); // samples rate
			$this->assertEquals(8000, $data['dr']); // data rate
			$this->assertEquals(1, $data['bs']);
			$this->assertEquals(8, $data['bis']); // bits per sample
			$this->assertEquals('data', $data['data']);
			$this->assertEquals($size - 44, $data['ds']); // data size
			$this->assertEquals($data['sr'] * $data['bs'], $data['dr']);
			$this->assertEquals($data['sr'] * $data['bis'] * $data['ch'] / 8, $data['dr']);
			$this->assertEquals($data['bis'] * $data['ch'] / 8, $data['bs']);
		}
	}
}
