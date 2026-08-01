<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\pixel\image\operations\imagemagick;

use InvalidArgumentException;
use mako\pixel\image\Color;
use mako\pixel\image\operations\imagemagick\Polygon;
use mako\pixel\image\operations\Point;
use mako\pixel\image\operations\Points;
use mako\tests\TestCase;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class PolygonTest extends TestCase
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
	public function testInvalidPoints(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessageIs('A polygon requires at least 3 points.');

		new Polygon(
			new Points(
				new Point(0, 0),
				new Point(0, 0),
			),
			new Color(0, 0, 0)
		);
	}

	/**
	 *
	 */
	public function testMissingFillAndStroke(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessageIs('A polygon requires either a fill or a stroke.');

		new Polygon(
			new Points(
				new Point(0, 0),
				new Point(0, 0),
				new Point(0, 0),
			)
		);
	}

	/**
	 *
	 */
	public function testInvalidStrokeWidth(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessageIs('Stroke width must be greater than 0.');

		new Polygon(
			new Points(
				new Point(0, 0),
				new Point(0, 0),
				new Point(0, 0),
			),
			stroke: new Color(0, 0, 0),
			strokeWidth: 0
		);
	}

	/**
	 *
	 */
	public function testOnlyFill(): void
	{
		$polygon = new Polygon(
			new Points(
				new Point(0, 0),
				new Point(0, 0),
				new Point(0, 0),
			),
			fill: new Color(0, 0, 0),
		);

		$this->assertInstanceOf(Polygon::class, $polygon);
	}

	/**
	 *
	 */
	public function testOnlyStroke(): void
	{
		$polygon = new Polygon(
			new Points(
				new Point(0, 0),
				new Point(0, 0),
				new Point(0, 0),
			),
			stroke: new Color(0, 0, 0),
		);

		$this->assertInstanceOf(Polygon::class, $polygon);
	}

	/**
	 *
	 */
	public function testFillAndStroke(): void
	{
		$polygon = new Polygon(
			new Points(
				new Point(0, 0),
				new Point(0, 0),
				new Point(0, 0),
			),
			fill: new Color(0, 0, 0),
			stroke: new Color(0, 0, 0),
		);

		$this->assertInstanceOf(Polygon::class, $polygon);
	}
}
