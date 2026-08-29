<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\pixel\image\geometry;

use mako\pixel\image\geometry\Dimensions;
use mako\pixel\image\geometry\Position;
use mako\tests\TestCase;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class PositionTest extends TestCase
{
	protected Dimensions $container;

	protected Dimensions $object;

	/**
	 *
	 */
	protected function setUp(): void
	{
		$this->container = new Dimensions(1000, 800);

		$this->object = new Dimensions(200, 100);
	}

	/**
	 *
	 */
	public function testTopLeft(): void
	{
		$point = Position::topLeft($this->container, $this->object);

		$this->assertSame(0, $point->x);
		$this->assertSame(0, $point->y);
	}

	/**
	 *
	 */
	public function testTopLeftWithMargin(): void
	{
		$point = Position::topLeft($this->container, $this->object, 10);

		$this->assertSame(10, $point->x);
		$this->assertSame(10, $point->y);
	}

	/**
	 *
	 */
	public function testTop(): void
	{
		$point = Position::top($this->container, $this->object);

		$this->assertSame(400, $point->x);
		$this->assertSame(0, $point->y);
	}

	/**
	 *
	 */
	public function testTopWithMargin(): void
	{
		$point = Position::top($this->container, $this->object, 10);

		$this->assertSame(400, $point->x);
		$this->assertSame(10, $point->y);
	}

	/**
	 *
	 */
	public function testTopRight(): void
	{
		$point = Position::topRight($this->container, $this->object);

		$this->assertSame(800, $point->x);
		$this->assertSame(0, $point->y);
	}

	/**
	 *
	 */
	public function testTopRightWithMargin(): void
	{
		$point = Position::topRight($this->container, $this->object, 10);

		$this->assertSame(790, $point->x);
		$this->assertSame(10, $point->y);
	}

	/**
	 *
	 */
	public function testCenterLeft(): void
	{
		$point = Position::centerLeft($this->container, $this->object);

		$this->assertSame(0, $point->x);
		$this->assertSame(350, $point->y);
	}

	/**
	 *
	 */
	public function testCenterLeftWithMargin(): void
	{
		$point = Position::centerLeft($this->container, $this->object, 10);

		$this->assertSame(10, $point->x);
		$this->assertSame(350, $point->y);
	}

	/**
	 *
	 */
	public function testCenter(): void
	{
		$point = Position::center($this->container, $this->object);

		$this->assertSame(400, $point->x);
		$this->assertSame(350, $point->y);
	}

	/**
	 *
	 */
	public function testCenterRight(): void
	{
		$point = Position::centerRight($this->container, $this->object);

		$this->assertSame(800, $point->x);
		$this->assertSame(350, $point->y);
	}

	/**
	 *
	 */
	public function testCenterRightWithMargin(): void
	{
		$point = Position::centerRight($this->container, $this->object, 10);

		$this->assertSame(790, $point->x);
		$this->assertSame(350, $point->y);
	}

	/**
	 *
	 */
	public function testBottomLeft(): void
	{
		$point = Position::bottomLeft($this->container, $this->object);

		$this->assertSame(0, $point->x);
		$this->assertSame(700, $point->y);
	}

	/**
	 *
	 */
	public function testBottomLeftWithMargin(): void
	{
		$point = Position::bottomLeft($this->container, $this->object, 10);

		$this->assertSame(10, $point->x);
		$this->assertSame(690, $point->y);
	}

	/**
	 *
	 */
	public function testBottom(): void
	{
		$point = Position::bottom($this->container, $this->object);

		$this->assertSame(400, $point->x);
		$this->assertSame(700, $point->y);
	}

	/**
	 *
	 */
	public function testBottomWithMargin(): void
	{
		$point = Position::bottom($this->container, $this->object, 10);

		$this->assertSame(400, $point->x);
		$this->assertSame(690, $point->y);
	}

	/**
	 *
	 */
	public function testBottomRight(): void
	{
		$point = Position::bottomRight($this->container, $this->object);

		$this->assertSame(800, $point->x);
		$this->assertSame(700, $point->y);
	}

	/**
	 *
	 */
	public function testBottomRightWithMargin(): void
	{
		$point = Position::bottomRight($this->container, $this->object, 10);

		$this->assertSame(790, $point->x);
		$this->assertSame(690, $point->y);
	}

	/**
	 *
	 */
	public function testCenterWithOddSizeDifference(): void
	{
		$point = Position::center(new Dimensions(101, 101), new Dimensions(50, 50));

		$this->assertSame(25, $point->x);
		$this->assertSame(25, $point->y);
	}
}
