<?php

namespace mako\tests\unit\pixel\image;

use mako\pixel\image\ImageMagick;
use mako\pixel\image\operations\imagemagick\Bitonal;
use mako\pixel\image\operations\imagemagick\Negate;
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

	/**
	 *
	 */
	public function testBitonal(): void
	{
		$image = new ImageMagick(__DIR__ . '/fixtures/002.jpg');
		$image->apply(new Bitonal);

		$colors = $image->getTopColors();

		$this->assertSame(2, count($colors));

		$this->assertSame('#FFFFFF', $colors[0]->toHexString());
		$this->assertSame('#000000', $colors[1]->toHexString());
	}

	/**
	 *
	 */
	public function testNegate(): void
	{
		$image = new ImageMagick(__DIR__ . '/fixtures/002.jpg');

		$image->apply(new Bitonal);
		$image->apply(new Negate);

		$colors = $image->getTopColors();

		$this->assertSame(2, count($colors));

		$this->assertSame('#000000', $colors[0]->toHexString());
		$this->assertSame('#FFFFFF', $colors[1]->toHexString());
	}
}
