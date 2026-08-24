<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\pixel\image\operations\imagemagick;

use InvalidArgumentException;
use mako\pixel\image\Color;
use mako\pixel\image\geometry\Dimensions;
use mako\pixel\image\operations\imagemagick\Rectangle;
use mako\tests\TestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;

#[Group('unit')]
#[RequiresPhpExtension('imagick')]
class RectangleTest extends TestCase
{
	/**
	 *
	 */
	public function testMissingFillAndStroke(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessageIs('A rectangle requires a fill, a stroke, or both.');

		new Rectangle(
			new Dimensions(0, 0)
		);
	}

	/**
	 *
	 */
	public function testInvalidStrokeWidth(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessageIs('Stroke width must be greater than 0.');

		new Rectangle(
			new Dimensions(0, 0),
			stroke: new Color(0, 0, 0),
			strokeWidth: 0
		);
	}

	/**
	 *
	 */
	public function testOnlyFill(): void
	{
		$rectangle = new Rectangle(
			new Dimensions(0, 0),
			fill: new Color(0, 0, 0),
		);

		$this->assertInstanceOf(Rectangle::class, $rectangle);
	}

	/**
	 *
	 */
	public function testOnlyStroke(): void
	{
		$rectangle = new Rectangle(
			new Dimensions(0, 0),
			stroke: new Color(0, 0, 0),
		);

		$this->assertInstanceOf(Rectangle::class, $rectangle);
	}

	/**
	 *
	 */
	public function testFillAndStroke(): void
	{
		$rectangle = new Rectangle(
			new Dimensions(0, 0),
			fill: new Color(0, 0, 0),
			stroke: new Color(0, 0, 0)
		);

		$this->assertInstanceOf(Rectangle::class, $rectangle);
	}
}
