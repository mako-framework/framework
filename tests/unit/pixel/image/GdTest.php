<?php

namespace mako\tests\unit\pixel\image;

use mako\pixel\image\Gd;
use mako\tests\TestCase;

class GdTest extends TestCase
{
	/**
	 *
	 */
	public function setUp(): void
	{
		if (!extension_loaded('gd')) {
			$this->markTestSkipped('The "gd" extension is not enabled.');
		}
	}

	/**
	 *
	 */
	public function testDimensions(): void
	{
		$image = new Gd(__DIR__ . '/fixtures/001.png');

		$this->assertSame(300, $image->getWidth());

		$this->assertSame(300, $image->getHeight());

		$this->assertSame(['width' => 300, 'height' => 300], $image->getDimensions());
	}

	/**
	 * Note that the colors extracted by GD aren't 100% accurate.
	 */
	public function testGetTopColors(): void
	{
		$image = new Gd(__DIR__ . '/fixtures/001.png');

		$colors = $image->getTopColors();

		$this->assertCount(3, $colors);

		$this->assertSame('#0070C0', $colors[0]->toHexString());
		$this->assertSame('#B01000', $colors[1]->toHexString());
		$this->assertSame('#007000', $colors[2]->toHexString());
	}

	/**
	 * Note that the colors extracted by GD aren't 100% accurate.
	 */
	public function testGetTopColorsWithLimit(): void
	{
		$image = new Gd(__DIR__ . '/fixtures/001.png');

		$colors = $image->getTopColors(2);

		$this->assertCount(2, $colors);

		$this->assertSame('#0070C0', $colors[0]->toHexString());
		$this->assertSame('#B01000', $colors[1]->toHexString());
	}
}
