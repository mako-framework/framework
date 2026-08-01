<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\pixel\image\operations;

use mako\pixel\image\Dimensions;
use mako\pixel\image\operations\Point;
use mako\pixel\image\operations\Vertices;
use mako\tests\TestCase;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class VerticesTest extends TestCase
{
	/**
	 *
	 */
	public function testCount(): void
	{
		$vertices = new Vertices(
			new Point(0, 0),
			new Point(0, 0)
		);

		$this->assertSame(2, count($vertices));
	}

	/**
	 *
	 */
	public function testIterate(): void
	{
		$vertices = new Vertices(
			new Point(0, 0),
			new Point(0, 0)
		);

		foreach ($vertices as $point) {
			$this->assertInstanceOf(Point::class, $point);
		}
	}

	/**
	 *
	 */
	public function testGetPoints(): void
	{
		$vertices = new Vertices(
			new Point(0, 0),
			new Point(0, 0)
		);

		$points = $vertices->getPoints();

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
		$vertices = new Vertices(
			new Point(0, 0),    // top-left
			new Point(100, 0),  // top-right
			new Point(100, 50), // bottom-right
			new Point(0, 50),   // bottom-left
		);

		$dimensions = $vertices->getDimensions();

		$this->assertSame(100, $dimensions->width);
		$this->assertSame(50, $dimensions->height);
	}

	/**
	 *
	 */
	public function testGetDimensionsWith50Offset(): void
	{
		$vertices = new Vertices(
			new Point(50, 50),   // top-left
			new Point(150, 50),  // top-right
			new Point(150, 100), // bottom-right
			new Point(50, 100),  // bottom-left
		);

		$dimensions = $vertices->getDimensions();

		$this->assertSame(100, $dimensions->width);
		$this->assertSame(50, $dimensions->height);
	}

	/**
	 *
	 */
	public function testFitTosWithZeroOffset(): void
	{
		$vertices = new Vertices(
			new Point(0, 0),    // top-left
			new Point(100, 0),  // top-right
			new Point(100, 50), // bottom-right
			new Point(0, 50),   // bottom-left
		);

		$dimensions = $vertices->getDimensions();

		$this->assertSame(100, $dimensions->width);
		$this->assertSame(50, $dimensions->height);

		$vertices = $vertices->fitTo(new Dimensions(50, 50));

		$dimensions = $vertices->getDimensions();

		$this->assertSame(50, $dimensions->width);
		$this->assertSame(25, $dimensions->height);

		$points = $vertices->getPoints();

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
		$vertices = new Vertices(
			new Point(50, 50),   // top-left
			new Point(150, 50),  // top-right
			new Point(150, 100), // bottom-right
			new Point(50, 100),  // bottom-left
		);

		$dimensions = $vertices->getDimensions();

		$this->assertSame(100, $dimensions->width);
		$this->assertSame(50, $dimensions->height);

		$vertices = $vertices->fitTo(new Dimensions(50, 50));

		$dimensions = $vertices->getDimensions();

		$this->assertSame(50, $dimensions->width);
		$this->assertSame(25, $dimensions->height);

		$points = $vertices->getPoints();

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
