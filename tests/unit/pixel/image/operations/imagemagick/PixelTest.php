<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\pixel\image\operations\imagemagick;

use mako\pixel\image\Color;
use mako\pixel\image\exceptions\ImageException;
use mako\pixel\image\geometry\Dimensions;
use mako\pixel\image\geometry\Point;
use mako\pixel\image\ImageMagick;
use mako\pixel\image\inspectors\imagemagick\ColorAt;
use mako\pixel\image\operations\imagemagick\Pixel;
use mako\tests\TestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;

#[Group('unit')]
#[RequiresPhpExtension('imagick')]
class PixelTest extends TestCase
{
	/**
	 *
	 */
	public function testPixe(): void
	{
		$image = ImageMagick::create(new Dimensions(4, 4));

		$this->assertSame('#000000', $image->inspect(new ColorAt(new Point(2, 2)))->toHexString());

		$image->apply(new Pixel(new Point(2, 2), new Color(255, 0, 0)));

		$this->assertSame('#FF0000', $image->inspect(new ColorAt(new Point(2, 2)))->toHexString());
	}

	/**
	 *
	 */
	public function testPixelWithInvalidPosition(): void
	{
		$this->expectException(ImageException::class);
		$this->expectExceptionMessageIs('Pixel coordinates [ 1000, 100 ] are outside image bounds [ 4 x 4 ].');

		$image = ImageMagick::create(new Dimensions(4, 4));

		$image->apply(new Pixel(new Point(1000, 100), new Color(0, 0, 0)));
	}
}
