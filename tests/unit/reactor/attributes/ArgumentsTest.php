<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\reactor\attributes;

use mako\cli\input\arguments\Argument;
use mako\reactor\attributes\CommandArguments;
use mako\tests\TestCase;
use Mockery;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class ArgumentsTest extends TestCase
{
	/**
	 *
	 */
	public function testGetArguments(): void
	{
		/** @var Argument&Mockery\LegacyMockInterface&Mockery\MockInterface $arg1 */
		$arg1 = Mockery::mock(Argument::class);
		/** @var Argument&Mockery\LegacyMockInterface&Mockery\MockInterface $arg2 */
		$arg2 = Mockery::mock(Argument::class);

		$arguments = new CommandArguments($arg1, $arg2);

		$this->assertInstanceOf(Argument::class, $arguments->getArguments()[0]);
		$this->assertInstanceOf(Argument::class, $arguments->getArguments()[1]);
	}
}
