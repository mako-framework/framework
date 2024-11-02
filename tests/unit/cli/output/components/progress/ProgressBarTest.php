<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\cli\output\components\progress;

use mako\cli\output\components\progress\ProgressBar;
use mako\tests\TestCase;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class ProgressBarTest extends TestCase
{
	/**
	 *
	 */
	public function testWithDefaultTemplate(): void
	{
		$progressBar = new ProgressBar;

		$this->assertSame('─', $progressBar->getEmpty());

		$this->assertSame('█', $progressBar->getFilled());
	}

	/**
	 *
	 */
	public function testWithCustomTemplate(): void
	{
		$progressBar = new ProgressBar('x%sx', 'y%sy');

		$this->assertSame('x─x', $progressBar->getEmpty());

		$this->assertSame('y█y', $progressBar->getFilled());
	}
}
