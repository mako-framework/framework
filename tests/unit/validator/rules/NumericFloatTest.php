<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\validator\rules;

use mako\tests\TestCase;
use mako\validator\rules\NumericFloat;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class NumericFloatTest extends TestCase
{
	/**
	 *
	 */
	public function testValidatesWhenEmpty(): void
	{
		$rule = new NumericFloat;

		$this->assertFalse($rule->validateWhenEmpty());
	}

	/**
	 *
	 */
	public function testWithValidValue(): void
	{
		$rule = new NumericFloat;

		$this->assertTrue($rule->validate(1.2, '', []));
		$this->assertTrue($rule->validate('1.2', '', []));
		$this->assertTrue($rule->validate('1.0E-15', '', []));
	}

	/**
	 *
	 */
	public function testWithInvalidValue(): void
	{
		$rule = new NumericFloat;

		$this->assertFalse($rule->validate('1', '', []));

		$this->assertSame('The foobar field must contain a float.', $rule->getErrorMessage('foobar'));
	}
}
