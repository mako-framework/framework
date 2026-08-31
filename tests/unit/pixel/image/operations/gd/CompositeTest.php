<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\pixel\image\operations\gd;

use mako\pixel\image\Color;
use mako\pixel\image\Gd;
use mako\pixel\image\geometry\Dimensions;
use mako\pixel\image\geometry\Point;
use mako\pixel\image\inspectors\gd\ColorAt;
use mako\pixel\image\operations\gd\Composite;
use mako\tests\TestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;

#[Group('unit')]
#[RequiresPhpExtension('gd')]
class CompositeTest extends TestCase
{
	/**
	 *
	 */
	public function testComposite(): void
	{
		$image = Gd::create(new Dimensions(8, 8), new Color(0, 0, 0));

		$overlay = Gd::create(new Dimensions(4, 4), new Color(255, 0, 0));

		// Composite the red 4x4 image at position (2, 2)

		$image->apply(new Composite($overlay, new Point(2, 2)));

		// The corners of the overlay should be red

		$this->assertSame('#FF0000', $image->inspect(new ColorAt(new Point(2, 2)))->toHexString());
		$this->assertSame('#FF0000', $image->inspect(new ColorAt(new Point(5, 2)))->toHexString());
		$this->assertSame('#FF0000', $image->inspect(new ColorAt(new Point(2, 5)))->toHexString());
		$this->assertSame('#FF0000', $image->inspect(new ColorAt(new Point(5, 5)))->toHexString());

		// Pixels just outside the overlay should still be black

		$this->assertSame('#000000', $image->inspect(new ColorAt(new Point(1, 1)))->toHexString());
		$this->assertSame('#000000', $image->inspect(new ColorAt(new Point(6, 2)))->toHexString());
		$this->assertSame('#000000', $image->inspect(new ColorAt(new Point(2, 6)))->toHexString());
		$this->assertSame('#000000', $image->inspect(new ColorAt(new Point(6, 6)))->toHexString());

		// The image corners should still be black

		$this->assertSame('#000000', $image->inspect(new ColorAt(new Point(0, 0)))->toHexString());
		$this->assertSame('#000000', $image->inspect(new ColorAt(new Point(7, 7)))->toHexString());
	}
}
