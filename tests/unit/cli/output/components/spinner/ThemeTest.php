<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\cli\output\components\spinner;

use mako\cli\output\components\spinner\Theme;
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

		$this->assertSame(['⠋', '⠙', '⠹', '⠸', '⠼', '⠴', '⠦', '⠧', '⠇', '⠏'], $theme->getFrames());
	}

	/**
	 *
	 */
	public function testWithCustomTemplate(): void
	{
		$theme = new Theme('x%sx');

		$this->assertSame(['x⠋x', 'x⠙x', 'x⠹x', 'x⠸x', 'x⠼x', 'x⠴x', 'x⠦x', 'x⠧x', 'x⠇x', 'x⠏x'], $theme->getFrames());
	}
}
