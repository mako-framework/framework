<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\pixel\image\operations\imagemagick;

use InvalidArgumentException;
use mako\pixel\image\Color;
use mako\pixel\image\operations\imagemagick\Circle;
use mako\tests\TestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;

#[Group('unit')]
#[RequiresPhpExtension('imagick')]
class CircleTest extends TestCase
{
	/**
	 *
	 */
	public function testMissingFillAndStroke(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessageIs('A circle requires a fill, a stroke, or both.');

		new Circle(
			10
		);
	}

	/**
	 *
	 */
	public function testInvalidStrokeWidth(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessageIs('Stroke width must be greater than 0.');

		new Circle(
			10,
			stroke: new Color(0, 0, 0),
			strokeWidth: 0
		);
	}

	/**
	 *
	 */
	public function testOnlyFill(): void
	{
		$circle = new Circle(
			10,
			fill: new Color(0, 0, 0),
		);

		$this->assertInstanceOf(Circle::class, $circle);
	}

	/**
	 *
	 */
	public function testOnlyStroke(): void
	{
		$circle = new Circle(
			10,
			stroke: new Color(0, 0, 0),
		);

		$this->assertInstanceOf(Circle::class, $circle);
	}

	/**
	 *
	 */
	public function testFillAndStroke(): void
	{
		$circle = new Circle(
			10,
			fill: new Color(0, 0, 0),
			stroke: new Color(0, 0, 0)
		);

		$this->assertInstanceOf(Circle::class, $circle);
	}
}
