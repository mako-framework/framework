<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\pixel\image\operations\imagemagick;

use InvalidArgumentException;
use mako\pixel\image\Color;
use mako\pixel\image\Dimensions;
use mako\pixel\image\operations\imagemagick\RoundedRectangle;
use mako\tests\TestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;

#[Group('unit')]
#[RequiresPhpExtension('imagick')]
class RoundedRectangleTest extends TestCase
{
	/**
	 *
	 */
	public function testMissingFillAndStroke(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessageIs('A rounded rectangle requires a fill, a stroke, or both.');

		new RoundedRectangle(
			new Dimensions(0, 0),
			20
		);
	}

	/**
	 *
	 */
	public function testInvalidStrokeWidth(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessageIs('Stroke width must be greater than 0.');

		new RoundedRectangle(
			new Dimensions(0, 0),
			20,
			stroke: new Color(0, 0, 0),
			strokeWidth: 0
		);
	}

	/**
	 *
	 */
	public function testOnlyFill(): void
	{
		$roundedRectangle = new RoundedRectangle(
			new Dimensions(0, 0),
			20,
			fill: new Color(0, 0, 0),
		);

		$this->assertInstanceOf(RoundedRectangle::class, $roundedRectangle);
	}

	/**
	 *
	 */
	public function testOnlyStroke(): void
	{
		$roundedRectangle = new RoundedRectangle(
			new Dimensions(0, 0),
			20,
			stroke: new Color(0, 0, 0),
		);

		$this->assertInstanceOf(RoundedRectangle::class, $roundedRectangle);
	}

	/**
	 *
	 */
	public function testFillAndStroke(): void
	{
		$roundedRectangle = new RoundedRectangle(
			new Dimensions(0, 0),
			20,
			fill: new Color(0, 0, 0),
			stroke: new Color(0, 0, 0)
		);

		$this->assertInstanceOf(RoundedRectangle::class, $roundedRectangle);
	}
}
