<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\pixel\image\inspectors\imagemagick;

use mako\pixel\image\ImageMagick;
use mako\pixel\image\inspectors\imagemagick\TopColors;
use mako\tests\TestCase;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class TopColorsTest extends TestCase
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
	public function testTopColors(): void
	{
		$image = new ImageMagick(__DIR__ . '/../../fixtures/001.png');

		$colors = $image->inspect(new TopColors);

		$this->assertCount(3, $colors);

		$this->assertSame('#0070C0', $colors[0]->toHexString());
		$this->assertSame('#B01000', $colors[1]->toHexString());
		$this->assertSame('#007000', $colors[2]->toHexString());
	}

	/**
	 *
	 */
	public function testTopColorsWithLimit(): void
	{
		$image = new ImageMagick(__DIR__ . '/../../fixtures/001.png');

		$colors = $image->inspect(new TopColors(2));

		$this->assertCount(2, $colors);

		$this->assertSame('#0376BB', $colors[0]->toHexString());
		$this->assertSame('#B51700', $colors[1]->toHexString());
	}
}
