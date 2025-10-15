<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\cli\output\components\progress;

use mako\cli\output\components\progress\Theme;
use mako\tests\TestCase;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class ThemeTest extends TestCase
{
	/**
	 *
	 */
	public function testWithDefaultTemplate(): void
	{
		$theme = new Theme;

		$this->assertSame('─', $theme->getEmpty());

		$this->assertSame('█', $theme->getFilled());
	}

	/**
	 *
	 */
	public function testWithCustomTemplate(): void
	{
		$theme = new Theme('x%sx', 'y%sy');

		$this->assertSame('x─x', $theme->getEmpty());

		$this->assertSame('y█y', $theme->getFilled());
	}
}
