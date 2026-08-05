<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\pixel\image;

use mako\pixel\image\WebColor;
use mako\tests\TestCase;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class WebColorTest extends TestCase
{
	/**
	 *
	 */
	public function testFuchsia(): void
	{
		$this->assertSame(WebColor::Magenta, WebColor::Fuchsia);
	}

	/**
	 *
	 */
	public function testCyan(): void
	{
		$this->assertSame(WebColor::Cyan, WebColor::Aqua);
	}

	/**
	 *
	 */
	public function testToColor(): void
	{
		$color = WebColor::Red->toColor();

		$this->assertSame(255, $color->red);
		$this->assertSame(0, $color->green);
		$this->assertSame(0, $color->blue);
		$this->assertSame(255, $color->alpha);
	}

	/**
	 *
	 */
	public function testToColorWithAlpha(): void
	{
		$color = WebColor::Red->toColor(127);

		$this->assertSame(255, $color->red);
		$this->assertSame(0, $color->green);
		$this->assertSame(0, $color->blue);
		$this->assertSame(127, $color->alpha);
	}
}
