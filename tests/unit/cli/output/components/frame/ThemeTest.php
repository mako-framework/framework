<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\cli\output\components\frame;

use mako\cli\output\components\frame\Theme;
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

		$this->assertSame('┏', $theme->getTopLeftCorner());

		$this->assertSame('┓', $theme->getTopRightCorner());

		$this->assertSame('┃', $theme->getVerticalLine());

		$this->assertSame('━', $theme->getHorizontalLine());

		$this->assertSame('┗', $theme->getBottomLeftCorner());

		$this->assertSame('┛', $theme->getBottomRightCorner());
	}

	/**
	 *
	 */
	public function testWithCustomTemplate(): void
	{
		$theme = new Theme('x%sx');

		$this->assertSame('x┏x', $theme->getTopLeftCorner());

		$this->assertSame('x┓x', $theme->getTopRightCorner());

		$this->assertSame('x┃x', $theme->getVerticalLine());

		$this->assertSame('x━x', $theme->getHorizontalLine());

		$this->assertSame('x┗x', $theme->getBottomLeftCorner());

		$this->assertSame('x┛x', $theme->getBottomRightCorner());
	}
}
