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
}
