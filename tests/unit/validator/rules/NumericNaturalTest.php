<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\validator\rules;

use mako\tests\TestCase;
use mako\validator\rules\NumericNatural;

/**
 * @group unit
 */
class NumericNaturalTest extends TestCase
{
	/**
	 *
	 */
	public function testValidatesWhenEmpty(): void
	{
		$rule = new NumericNatural;

		$this->assertFalse($rule->validateWhenEmpty());
	}

	/**
	 *
	 */
	public function testWithValidValue(): void
	{
		$rule = new NumericNatural;

		$this->assertTrue($rule->validate(0, []));
		$this->assertTrue($rule->validate(1, []));
		$this->assertTrue($rule->validate('1', []));
	}

	/**
	 *
	 */
	public function testWithInvalidValue(): void
	{
		$rule = new NumericNatural;

		$this->assertFalse($rule->validate(1.1, []));
		$this->assertFalse($rule->validate('1.1', []));

		$this->assertSame('The foobar field must contain a natural number.', $rule->getErrorMessage('foobar'));
	}
}
