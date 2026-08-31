<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\pixel\image\operations\imagemagick;

use InvalidArgumentException;
use mako\pixel\image\Color;
use mako\pixel\image\geometry\Dimensions;
use mako\pixel\image\geometry\Point;
use mako\pixel\image\ImageMagick;
use mako\pixel\image\inspectors\imagemagick\ColorAt;
use mako\pixel\image\operations\imagemagick\Border;
use mako\tests\TestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;

#[Group('unit')]
#[RequiresPhpExtension('imagick')]
class BorderTest extends TestCase
{
	/**
	 *
	 */
	public function testNegativeWidth(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessageIs('The border width must be a non-negative number.');

		new Border(new Color(0, 0, 0), -4);
	}

	/**
	 *
	 */
	public function testBorder(): void
	{
		$image = ImageMagick::create(new Dimensions(10, 10), new Color(0, 0, 0));

		// Check that the borders are black

		$color = $image->inspect(new ColorAt(new Point(2, 0)));

		$this->assertSame('#000000', $color->toHexString());

		// Draw border

		$image->apply(new Border(new Color(255, 0, 0), 2));

		// Top, bottom, left and right edges should be red

		$this->assertSame('#FF0000', $image->inspect(new ColorAt(new Point(2, 0)))->toHexString());
		$this->assertSame('#FF0000', $image->inspect(new ColorAt(new Point(5, 9)))->toHexString());
		$this->assertSame('#FF0000', $image->inspect(new ColorAt(new Point(0, 5)))->toHexString());
		$this->assertSame('#FF0000', $image->inspect(new ColorAt(new Point(9, 5)))->toHexString());

		// First pixel inside the border should be unchanged

		$this->assertSame('#000000', $image->inspect(new ColorAt(new Point(2, 2)))->toHexString());

		// Center should be unchanged

		$this->assertSame('#000000', $image->inspect(new ColorAt(new Point(5, 5)))->toHexString());
	}
}
