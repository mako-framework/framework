<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\pixel\image\operations\gd;

use InvalidArgumentException;
use mako\pixel\image\Color;
use mako\pixel\image\geometry\Dimensions;
use mako\pixel\image\operations\gd\Ellipse;
use mako\tests\TestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;

#[Group('unit')]
#[RequiresPhpExtension('gd')]
class EllipseTest extends TestCase
{
	/**
	 *
	 */
	public function testMissingFillAndStroke(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessageIs('An ellipse requires a fill, a stroke, or both.');

		new Ellipse(
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

		new Ellipse(
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
		$ellipse = new Ellipse(
			new Dimensions(0, 0),
			fill: new Color(0, 0, 0),
		);

		$this->assertInstanceOf(Ellipse::class, $ellipse);
	}

	/**
	 *
	 */
	public function testOnlyStroke(): void
	{
		$ellipse = new Ellipse(
			new Dimensions(0, 0),
			stroke: new Color(0, 0, 0),
		);

		$this->assertInstanceOf(Ellipse::class, $ellipse);
	}

	/**
	 *
	 */
	public function testFillAndStroke(): void
	{
		$ellipse = new Ellipse(
			new Dimensions(0, 0),
			fill: new Color(0, 0, 0),
			stroke: new Color(0, 0, 0)
		);

		$this->assertInstanceOf(Ellipse::class, $ellipse);
	}
}
