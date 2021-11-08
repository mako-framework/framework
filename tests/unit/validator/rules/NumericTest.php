<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\validator\rules;

use mako\tests\TestCase;
use mako\validator\rules\Numeric;

/**
 * @group unit
 */
class NumericTest extends TestCase
{
	/**
	 *
	 */
	public function testValidatesWhenEmpty(): void
	{
		$rule = new Numeric;

		$this->assertFalse($rule->validateWhenEmpty());
	}

	/**
	 *
	 */
	public function testWithValidValue(): void
	{
		$rule = new Numeric;

		$this->assertTrue($rule->validate(1, []));
		$this->assertTrue($rule->validate(1.1, []));
		$this->assertTrue($rule->validate('1.1', []));
		$this->assertTrue($rule->validate('0433242', []));
	}

	/**
	 *
	 */
	public function testWithInvalidValue(): void
	{
		$rule = new Numeric;

		$this->assertFalse($rule->validate('x', []));
		$this->assertFalse($rule->validate('x343', []));

		$this->assertSame('The foobar field must contain a numeric value.', $rule->getErrorMessage('foobar'));
	}
}
