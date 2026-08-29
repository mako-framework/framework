<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\pixel\image\geometry;

use mako\pixel\image\geometry\Point;
use mako\tests\TestCase;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class PointTest extends TestCase
{
	/**
	 *
	 */
	public function testPoint(): void
	{
		$point = new Point(50, 100);

		$this->assertSame(50, $point->x);
		$this->assertSame(100, $point->y);
	}
}
