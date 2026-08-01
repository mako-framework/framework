<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\pixel\image\operations;

use mako\pixel\image\Dimensions;
use mako\pixel\image\operations\Point;
use mako\pixel\image\operations\Points;
use mako\tests\TestCase;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class PointsTest extends TestCase
{
	/**
	 *
	 */
	public function testCount(): void
	{
		$points = new Points(
			new Point(0, 0),
			new Point(0, 0)
		);

		$this->assertSame(2, count($points));
	}

	/**
	 *
	 */
	public function testIterate(): void
	{
		$points = new Points(
			new Point(0, 0),
			new Point(0, 0)
		);

		foreach ($points as $point) {
			$this->assertInstanceOf(Point::class, $point);
		}
	}

	/**
	 *
	 */
	public function testGetPoints(): void
	{
		$points = new Points(
			new Point(0, 0),
			new Point(0, 0)
		);

		$points = $points->getPoints();

		$this->assertSame(2, count($points));

		foreach ($points as $point) {
			$this->assertInstanceOf(Point::class, $point);
		}
	}

	/**
	 *
	 */
	public function testGetDimensionsWithZeroOffset(): void
	{
		$points = new Points(
			new Point(0, 0),    // top-left
			new Point(100, 0),  // top-right
			new Point(100, 50), // bottom-right
			new Point(0, 50),   // bottom-left
		);

		$dimensions = $points->getDimensions();

		$this->assertSame(100, $dimensions->width);
		$this->assertSame(50, $dimensions->height);
	}

	/**
	 *
	 */
	public function testGetDimensionsWith50Offset(): void
	{
		$points = new Points(
			new Point(50, 50),   // top-left
			new Point(150, 50),  // top-right
			new Point(150, 100), // bottom-right
			new Point(50, 100),  // bottom-left
		);

		$dimensions = $points->getDimensions();

		$this->assertSame(100, $dimensions->width);
		$this->assertSame(50, $dimensions->height);
	}

	/**
	 *
	 */
	public function testFitTosWithZeroOffset(): void
	{
		$points = new Points(
			new Point(0, 0),    // top-left
			new Point(100, 0),  // top-right
			new Point(100, 50), // bottom-right
			new Point(0, 50),   // bottom-left
		);

		$dimensions = $points->getDimensions();

		$this->assertSame(100, $dimensions->width);
		$this->assertSame(50, $dimensions->height);

		$points = $points->fitTo(new Dimensions(50, 50));

		$dimensions = $points->getDimensions();

		$this->assertSame(50, $dimensions->width);
		$this->assertSame(25, $dimensions->height);

		$points = $points->getPoints();

		$this->assertSame(0, $points[0]->x);
		$this->assertSame(0, $points[0]->y);

		$this->assertSame(50, $points[1]->x);
		$this->assertSame(0, $points[1]->y);

		$this->assertSame(50, $points[2]->x);
		$this->assertSame(25, $points[2]->y);

		$this->assertSame(0, $points[3]->x);
		$this->assertSame(25, $points[3]->y);
	}

	/**
	 *
	 */
	public function testfitToWith50Offset(): void
	{
		$points = new Points(
			new Point(50, 50),   // top-left
			new Point(150, 50),  // top-right
			new Point(150, 100), // bottom-right
			new Point(50, 100),  // bottom-left
		);

		$dimensions = $points->getDimensions();

		$this->assertSame(100, $dimensions->width);
		$this->assertSame(50, $dimensions->height);

		$points = $points->fitTo(new Dimensions(50, 50));

		$dimensions = $points->getDimensions();

		$this->assertSame(50, $dimensions->width);
		$this->assertSame(25, $dimensions->height);

		$points = $points->getPoints();

		$this->assertSame(0, $points[0]->x);
		$this->assertSame(0, $points[0]->y);

		$this->assertSame(50, $points[1]->x);
		$this->assertSame(0, $points[1]->y);

		$this->assertSame(50, $points[2]->x);
		$this->assertSame(25, $points[2]->y);

		$this->assertSame(0, $points[3]->x);
		$this->assertSame(25, $points[3]->y);
	}
}
