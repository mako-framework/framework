<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\validator\rules;

use mako\tests\TestCase;
use mako\validator\rules\NumberFloat;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class NumberFloatTest extends TestCase
{
	/**
	 *
	 */
	public function testValidatesWhenEmpty(): void
	{
		$rule = new NumberFloat;

		$this->assertFalse($rule->validateWhenEmpty());
	}

	/**
	 *
	 */
	public function testWithValidValue(): void
	{
		$rule = new NumberFloat;

		$this->assertTrue($rule->validate(1.1, '', []));
	}

	/**
	 *
	 */
	public function testWithInvalidValue(): void
	{
		$rule = new NumberFloat;

		$this->assertFalse($rule->validate(1, '', []));
		$this->assertFalse($rule->validate('1.1', '', []));

		$this->assertSame('The foobar field must contain a float.', $rule->getErrorMessage('foobar'));
	}
}
