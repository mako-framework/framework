<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\cli\input\arguments;

use mako\cli\input\arguments\Argument;
use mako\cli\input\arguments\NamedArgument;
use mako\tests\TestCase;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class NamedArgumentTest extends TestCase
{
	/**
	 *
	 */
	public function testNamedArgument(): void
	{
		$argument = new NamedArgument('test', 't', 'Test argument', Argument::IS_OPTIONAL, 'default');

		$this->assertSame('Test argument', $argument->getDescription());

		$this->assertSame('--test', $argument->getName());

		$this->assertSame('-t', $argument->getAlias());

		$this->assertTrue($argument->isOptional());

		$this->assertSame('default', $argument->getDefaultValue());
	}
}
