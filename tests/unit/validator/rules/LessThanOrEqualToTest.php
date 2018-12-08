<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\validator\rules;

use mako\tests\TestCase;
use mako\validator\rules\LessThanOrEqualTo;

/**
 * @group unit
 */
class LessThanOrEqualToTest extends TestCase
{
	/**
	 *
	 */
	public function testValidatesWhenEmpty(): void
	{
		$rule = new LessThanOrEqualTo;

		$this->assertFalse($rule->validateWhenEmpty());
	}

	/**
	 *
	 */
	public function testWithValidValue(): void
	{
		$rule = new LessThanOrEqualTo;

		$rule->setParameters([3]);

		$this->assertTrue($rule->validate(3, []));
		$this->assertTrue($rule->validate('3', []));
	}

	/**
	 *
	 */
	public function testWithInvalidValue(): void
	{
		$rule = new LessThanOrEqualTo;

		$rule->setParameters([3]);

		$this->assertFalse($rule->validate(4, []));
		$this->assertFalse($rule->validate('4', []));

		$this->assertSame('The value of the foobar field must be less than or equal to 3.', $rule->getErrorMessage('foobar'));
	}
}
