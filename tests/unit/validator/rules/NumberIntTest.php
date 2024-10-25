<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\validator\rules;

use mako\tests\TestCase;
use mako\validator\rules\NumberInt;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class NumberIntTest extends TestCase
{
	/**
	 *
	 */
	public function testValidatesWhenEmpty(): void
	{
		$rule = new NumberInt;

		$this->assertFalse($rule->validateWhenEmpty());
	}

	/**
	 *
	 */
	public function testWithValidValue(): void
	{
		$rule = new NumberInt;

		$this->assertTrue($rule->validate(1, '', []));
	}

	/**
	 *
	 */
	public function testWithInvalidValue(): void
	{
		$rule = new NumberInt;

		$this->assertFalse($rule->validate(1.1, '', []));
		$this->assertFalse($rule->validate('1', '', []));

		$this->assertSame('The foobar field must contain a integer.', $rule->getErrorMessage('foobar'));
	}
}
