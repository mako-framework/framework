<?php

namespace mako\tests\unit\pixel\image;

use mako\pixel\image\ImageMagick;
use mako\tests\TestCase;

class ImageMagickTest extends TestCase
{
	/**
	 *
	 */
	public function setUp(): void
	{
		if (!extension_loaded('imagick')) {
			$this->markTestSkipped('The "imagick" extension is not enabled.');
		}
	}

	/**
	 *
	 */
	public function testDimensions(): void
	{
		$image = new ImageMagick(__DIR__ . '/fixtures/001.png');

		$this->assertSame(300, $image->getWidth());

		$this->assertSame(300, $image->getHeight());

		$this->assertSame(['width' => 300, 'height' => 300], $image->getDimensions());
	}

	/**
	 *
	 */
	public function testGetTopColors(): void
	{
		$image = new ImageMagick(__DIR__ . '/fixtures/001.png');

		$colors = $image->getTopColors();

		$this->assertCount(3, $colors);

		$this->assertSame('#0376BB', $colors[0]->toHexString());
		$this->assertSame('#B51700', $colors[1]->toHexString());
		$this->assertSame('#047101', $colors[2]->toHexString());
	}

	/**
	 *
	 */
	public function testGetTopColorsWithLimit(): void
	{
		$image = new ImageMagick(__DIR__ . '/fixtures/001.png');

		$colors = $image->getTopColors(2);

		$this->assertCount(2, $colors);

		$this->assertSame('#0376BB', $colors[0]->toHexString());
		$this->assertSame('#B51700', $colors[1]->toHexString());
	}
}
