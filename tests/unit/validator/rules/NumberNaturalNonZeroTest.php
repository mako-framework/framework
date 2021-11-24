<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\validator\rules;

use mako\tests\TestCase;
use mako\validator\rules\NumberNaturalNonZero;

/**
 * @group unit
 */
class NumberNaturalNonZeroTest extends TestCase
{
	/**
	 *
	 */
	public function testValidatesWhenEmpty(): void
	{
		$rule = new NumberNaturalNonZero;

		$this->assertFalse($rule->validateWhenEmpty());
	}

	/**
	 *
	 */
	public function testWithValidValue(): void
	{
		$rule = new NumberNaturalNonZero;

		$this->assertTrue($rule->validate(1, '', []));
	}

	/**
	 *
	 */
	public function testWithInvalidValue(): void
	{
		$rule = new NumberNaturalNonZero;

		$this->assertFalse($rule->validate(0, '', []));
		$this->assertFalse($rule->validate('0', '', []));
		$this->assertFalse($rule->validate('1', '', []));

		$this->assertSame('The foobar field must contain a natural non-zero number.', $rule->getErrorMessage('foobar'));
	}
}
