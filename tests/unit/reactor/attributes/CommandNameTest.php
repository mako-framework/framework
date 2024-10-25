<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\reactor\attributes;

use mako\reactor\attributes\CommandName;
use mako\tests\TestCase;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class CommandNameTest extends TestCase
{
	/**
	 *
	 */
	public function testGetName(): void
	{
		$commandName = new CommandName('command:name');

		$this->assertSame('command:name', $commandName->getName());
	}
}
