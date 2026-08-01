<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\pixel\image;

use mako\pixel\image\Dimensions;
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
		$dimensions = new Dimensions(100, 150);

		$this->assertSame(100, $dimensions->width);
		$this->assertSame(150, $dimensions->height);
	}
}
