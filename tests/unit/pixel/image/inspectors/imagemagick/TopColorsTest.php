<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\pixel\image\inspectors\imagemagick;

use mako\pixel\image\Color;
use mako\pixel\image\geometry\Dimensions;
use mako\pixel\image\ImageMagick;
use mako\pixel\image\inspectors\imagemagick\TopColors;
use mako\tests\TestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;

#[Group('unit')]
#[RequiresPhpExtension('imagick')]
class TopColorsTest extends TestCase
{
	/**
	 *
	 */
	public function testTopColors(): void
	{
		$image = new ImageMagick(__DIR__ . '/../../fixtures/001.png');

		$colors = $image->inspect(new TopColors);

		$this->assertCount(3, $colors);

		$this->assertSame('#0376BB', $colors[0]->toHexString());
		$this->assertSame('#B51700', $colors[1]->toHexString());
		$this->assertSame('#047101', $colors[2]->toHexString());
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

	/**
	 *
	 */
	public function testTopColorsAllWhite(): void
	{
		$image = ImageMagick::create(new Dimensions(10, 10), new Color(255, 255, 255));

		$colors = $image->inspect(new TopColors);

		$this->assertCount(1, $colors);

		$this->assertSame('#FFFFFF', $colors[0]->toHexString());
	}

	/**
	 *
	 */
	public function testTopColorsAllBlack(): void
	{
		$image = ImageMagick::create(new Dimensions(10, 10), new Color(0, 0, 0));

		$colors = $image->inspect(new TopColors);

		$this->assertCount(1, $colors);

		$this->assertSame('#000000', $colors[0]->toHexString());
	}
}
