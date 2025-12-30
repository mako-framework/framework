<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\cli\output\components\hyperlink;

use mako\cli\output\components\hyperlink\Theme;
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

		$this->assertSame('%s [↗]', $theme->getFormat());
	}

	/**
	 *
	 */
	public function testWithCustomTemplate(): void
	{
		$theme = new Theme('<underlined>%s</underlined>');

		$this->assertSame('<underlined>%s [↗]</underlined>', $theme->getFormat());
	}
}
