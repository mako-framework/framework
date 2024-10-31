<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\cli\input\arguments;

use mako\cli\input\arguments\Argument;
use mako\cli\input\arguments\exceptions\ArgumentException;
use mako\cli\input\arguments\PositionalArgument;
use mako\tests\TestCase;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class PositionalArgumentTest extends TestCase
{
	/**
	 *
	 */
	public function testPositionalArgument(): void
	{
		$argument = new PositionalArgument('test', 'Test argument', Argument::IS_OPTIONAL, 'default');

		$this->assertSame('test', $argument->getName());

		$this->assertSame('Test argument', $argument->getDescription());

		$this->assertSame('test', $argument->getNormalizedName());

		$this->assertTrue($argument->isOptional());

		$this->assertSame('default', $argument->getDefaultValue());

		$this->assertTrue($argument->isPositional());
	}

	/**
	 *
	 */
	public function testPositionalArgumentException(): void
	{
		$this->expectException(ArgumentException::class);

		$this->expectExceptionMessage('Positional argument names cannot start with "-".');

		new PositionalArgument('-test', 'Test argument', Argument::IS_OPTIONAL, 'default');
	}
}
