<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\application;

use mako\application\DeferredTasks;
use mako\tests\TestCase;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class DeferredTaskTest extends TestCase
{
	/**
	 *
	 */
	public function testDefer(): void
	{
		$deferredTasks = new DeferredTasks;

		$task = function () {
			return 'foobar';
		};

		$deferredTasks->defer($task);

		$this->assertSame([$task], $deferredTasks->getTasks());
	}
}
