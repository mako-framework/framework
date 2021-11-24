<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\validator\rules;

use mako\tests\TestCase;
use mako\validator\rules\Number;

/**
 * @group unit
 */
class NumberTest extends TestCase
{
	/**
	 *
	 */
	public function testValidatesWhenEmpty(): void
	{
		$rule = new Number;

		$this->assertFalse($rule->validateWhenEmpty());
	}

	/**
	 *
	 */
	public function testWithValidValue(): void
	{
		$rule = new Number;

		$this->assertTrue($rule->validate(1, '', []));
		$this->assertTrue($rule->validate(1.1, '', []));
	}

	/**
	 *
	 */
	public function testWithInvalidValue(): void
	{
		$rule = new Number;

		$this->assertFalse($rule->validate('1', '', []));
		$this->assertFalse($rule->validate('1.1', '', []));

		$this->assertSame('The foobar field must contain a float or an integer.', $rule->getErrorMessage('foobar'));
	}
}
