<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\cli\output\components\progress;

use mako\cli\output\components\spinner\Frames;
use mako\tests\TestCase;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class FramesTest extends TestCase
{
	/**
	 *
	 */
	public function testWithDefaultTemplate(): void
	{
		$frames = new Frames;

		$this->assertSame(['⠋', '⠙', '⠹', '⠸', '⠼', '⠴', '⠦', '⠧', '⠇', '⠏'], $frames->getFrames());
	}

	/**
	 *
	 */
	public function testWithCustomTemplate(): void
	{
		$frames = new Frames('x%sx');

		$this->assertSame(['x⠋x', 'x⠙x', 'x⠹x', 'x⠸x', 'x⠼x', 'x⠴x', 'x⠦x', 'x⠧x', 'x⠇x', 'x⠏x'], $frames->getFrames());
	}
}
