<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\validator\rules;

use mako\tests\TestCase;
use mako\validator\rules\NumericInt;

/**
 * @group unit
 */
class NumericIntTest extends TestCase
{
	/**
	 *
	 */
	public function testValidatesWhenEmpty(): void
	{
		$rule = new NumericInt;

		$this->assertFalse($rule->validateWhenEmpty());
	}

	/**
	 *
	 */
	public function testWithValidValue(): void
	{
		$rule = new NumericInt;

		$this->assertTrue($rule->validate(1, '', []));
		$this->assertTrue($rule->validate('1', '', []));
	}

	/**
	 *
	 */
	public function testWithInvalidValue(): void
	{
		$rule = new NumericInt;

		$this->assertFalse($rule->validate(1.1, '', []));
		$this->assertFalse($rule->validate('1.1', '', []));

		$this->assertSame('The foobar field must contain an integer.', $rule->getErrorMessage('foobar'));
	}
}
