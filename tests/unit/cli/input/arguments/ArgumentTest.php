<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\cli\input\arguments;

use LogicException;
use mako\cli\input\arguments\Argument;
use mako\tests\TestCase;
use RuntimeException;

/**
 * @group unit
 */
class ArgumentTest extends TestCase
{
	/**
	 *
	 */
	public function testArgument(): void
	{
		$argument = new Argument('--test-argument', 'Test argument');

		$this->assertSame('--test-argument', $argument->getName());

		$this->assertSame('Test argument', $argument->getDescription());

		$this->assertSame('testArgument', $argument->getNormalizedName());

		$this->assertNull($argument->getAlias());
	}

	/**
	 *
	 */
	public function testArgumentWithAlias(): void
	{
		$argument = new Argument('-t|--test-argument', 'Test argument');

		$this->assertSame('--test-argument', $argument->getName());

		$this->assertSame('Test argument', $argument->getDescription());

		$this->assertSame('testArgument', $argument->getNormalizedName());

		$this->assertSame('-t', $argument->getAlias());
	}

	/**
	 *
	 */
	public function testArgumentWithInvalidName(): void
	{
		$this->expectException(RuntimeException::class);

		$this->expectExceptionMessage('Invalid argument name [ --123 ].');

		new Argument('--123');
	}

	/**
	 *
	 */
	public function testArgumentWithInvalidAlias(): void
	{
		$this->expectException(RuntimeException::class);

		$this->expectExceptionMessage('Invalid argument alias [ -po ].');

		new Argument('-po|--port');
	}

	/**
	 *
	 */
	public function testPositionalBooleanArgument(): void
	{
		$this->expectException(LogicException::class);

		$this->expectExceptionMessage("Argument can't be both positional and a boolean flag.");

		new Argument('test', '', Argument::IS_BOOL);
	}

	/**
	 *
	 */
	public function testBooleanArrayArgument(): void
	{
		$this->expectException(LogicException::class);

		$this->expectExceptionMessage("Argument can't be both a boolean flag and an array.");

		new Argument('--test', '', Argument::IS_BOOL | Argument::IS_ARRAY);
	}

	/**
	 *
	 */
	public function testBooleanIntegerArgument(): void
	{
		$this->expectException(LogicException::class);

		$this->expectExceptionMessage("Argument can't be both a boolean flag and an integer.");

		new Argument('--test', '', Argument::IS_BOOL | Argument::IS_INT);
	}

	/**
	 *
	 */
	public function testBooleanFloatArgument(): void
	{
		$this->expectException(LogicException::class);

		$this->expectExceptionMessage("Argument can't be both a boolean flag and a float.");

		new Argument('--test', '', Argument::IS_BOOL | Argument::IS_FLOAT);
	}

	/**
	 *
	 */
	public function testIntegerFloatArgument(): void
	{
		$this->expectException(LogicException::class);

		$this->expectExceptionMessage("Argument can't be both a float and an integer.");

		new Argument('--test', '', Argument::IS_INT | Argument::IS_FLOAT);
	}

	/**
	 *
	 */
	public function testIsPositional(): void
	{
		$argument = new Argument('test');

		$this->assertTrue($argument->isPositional());

		//

		$argument = new Argument('--test');

		$this->assertFalse($argument->isPositional());
	}

	/**
	 *
	 */
	public function testIsInt(): void
	{
		$argument = new Argument('--test', '', Argument::IS_INT);

		$this->assertTrue($argument->isInt());

		//

		$argument = new Argument('--test');

		$this->assertFalse($argument->isInt());
	}

	/**
	 *
	 */
	public function testIsFloat(): void
	{
		$argument = new Argument('--test', '', Argument::IS_FLOAT);

		$this->assertTrue($argument->isFloat());

		//

		$argument = new Argument('--test');

		$this->assertFalse($argument->isFloat());
	}

	/**
	 *
	 */
	public function testIsBool(): void
	{
		$argument = new Argument('--test', '', Argument::IS_BOOL);

		$this->assertTrue($argument->isBool());

		$this->assertTrue($argument->isOptional());

		//

		$argument = new Argument('--test');

		$this->assertFalse($argument->isBool());
	}

	/**
	 *
	 */
	public function testIsArray(): void
	{
		$argument = new Argument('--test', '', Argument::IS_ARRAY);

		$this->assertTrue($argument->isArray());

		//

		$argument = new Argument('--test');

		$this->assertFalse($argument->isArray());
	}

	/**
	 *
	 */
	public function testIsOptional(): void
	{
		$argument = new Argument('--test', '', Argument::IS_OPTIONAL);

		$this->assertTrue($argument->isOptional());

		//

		$argument = new Argument('--test');

		$this->assertFalse($argument->isOptional());
	}
}
