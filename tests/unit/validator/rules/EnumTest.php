<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\validator\rules;

use mako\tests\TestCase;
use mako\validator\exceptions\ValidatorException;
use mako\validator\rules\Enum;

// --------------------------------------------------------------------------
// START CLASSES
// --------------------------------------------------------------------------

if(PHP_VERSION_ID >= 80100)
{
	include __DIR__ . '/_Enums.php';
}

// --------------------------------------------------------------------------
// END CLASSES
// --------------------------------------------------------------------------

/**
 * @group unit
 */
class EnumTest extends TestCase
{
	/**
	 *{@inheritDoc}
	 */
	public function setup(): void
	{
		if(PHP_VERSION_ID < 80100)
		{
			$this->markTestSkipped('This feature requires PHP 8.1+');
		}
	}

	/**
	 *
	 */
	public function testValidatesWhenEmpty(): void
	{
		$rule = new Enum(FooEnum::class);

		$this->assertFalse($rule->validateWhenEmpty());
	}

	/**
	 *
	 */
	public function testWithValidValue(): void
	{
		$rule = new Enum(FooEnum::class);

		$this->assertTrue($rule->validate('ONE', []));
		$this->assertTrue($rule->validate('TWO', []));

		$rule = new Enum(BarEnum::class);

		$this->assertTrue($rule->validate(1, []));
		$this->assertTrue($rule->validate(2, []));
	}

	/**
	 *
	 */
	public function testWithInvalidValue(): void
	{
		$rule = new Enum(FooEnum::class);

		$this->assertFalse($rule->validate('THREE', []));
		$this->assertFalse($rule->validate('FOUR', []));

		$rule = new Enum(BarEnum::class);

		$this->assertFalse($rule->validate(3, []));
		$this->assertFalse($rule->validate(4, []));

		$this->assertSame('The foobar field must contain a valid enum value.', $rule->getErrorMessage('foobar'));
	}

	/**
	 *
	 */
	public function testWithInvalidEnum(): void
	{
		$this->expectException(ValidatorException::class);

		$this->expectExceptionMessage('[ mako\tests\unit\validator\rules\BazEnum ] is not a valid enum.');

		new Enum(BazEnum::class);
	}
}
