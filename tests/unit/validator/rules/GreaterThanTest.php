<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\validator\rules;

use mako\tests\TestCase;
use mako\validator\rules\GreaterThan;

/**
 * @group unit
 */
class GreaterThanTest extends TestCase
{
	/**
	 *
	 */
	public function testValidatesWhenEmpty(): void
	{
		$rule = new GreaterThan(3);

		$this->assertFalse($rule->validateWhenEmpty());
	}

	/**
	 *
	 */
	public function testWithValidValue(): void
	{
		$rule = new GreaterThan(3);

		$this->assertTrue($rule->validate(4, []));
		$this->assertTrue($rule->validate('4', []));
	}

	/**
	 *
	 */
	public function testWithInvalidValue(): void
	{
		$rule = new GreaterThan(3);

		$this->assertFalse($rule->validate(2, []));
		$this->assertFalse($rule->validate('2', []));

		$this->assertSame('The value of the foobar field must be greater than 3.', $rule->getErrorMessage('foobar'));
	}
}
