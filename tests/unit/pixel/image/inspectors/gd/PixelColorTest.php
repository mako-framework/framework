<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\pixel\image\inspectors\gd;

use mako\pixel\image\Color;
use mako\pixel\image\exceptions\ImageException;
use mako\pixel\image\Gd;
use mako\pixel\image\inspectors\gd\PixelColor;
use mako\pixel\image\operations\Point;
use mako\tests\TestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;

#[Group('unit')]
#[RequiresPhpExtension('imagick')]
class PixelColorTest extends TestCase
{
	/**
	 *
	 */
	public function testPixelColor(): void
	{
		$image = new Gd(__DIR__ . '/../../fixtures/001.png');

		$color = $image->inspect(new PixelColor(new Point(0, 0)));

		$this->assertInstanceOf(Color::class, $color);

		$this->assertSame('#B51700', $color->toHexString());

		$color = $image->inspect(new PixelColor(new Point(0, 100)));

		$this->assertInstanceOf(Color::class, $color);

		$this->assertSame('#0376BB', $color->toHexString());

		$color = $image->inspect(new PixelColor(new Point(0, 275)));

		$this->assertInstanceOf(Color::class, $color);

		$this->assertSame('#047101', $color->toHexString());
	}

	/**
	 *
	 */
	public function testPixelColorWithInvalidPosition(): void
	{
		$this->expectException(ImageException::class);
		$this->expectExceptionMessageIs('Pixel coordinates [ 1000, 100 ] are outside image bounds [ 300 x 300 ].');

		$image = new Gd(__DIR__ . '/../../fixtures/001.png');

		$image->inspect(new PixelColor(new Point(1000, 100)));
	}
}
