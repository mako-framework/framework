<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\pixel\image\operations;

use mako\pixel\image\geometry\Dimensions;
use mako\tests\TestCase;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class DimensionsTest extends TestCase
{
	/**
	 *
	 */
	public function testDimensions(): void
	{
		$dimensions = new Dimensions(50, 100);

		$this->assertSame(50, $dimensions->width);
		$this->assertSame(100, $dimensions->height);
	}
}
