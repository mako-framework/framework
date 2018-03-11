<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\validator\rules;

use mako\tests\TestCase;
use mako\validator\rules\Natural;

/**
 * @group unit
 */
class NaturalTest extends TestCase
{
	/**
	 *
	 */
	public function testValidatesWhenEmpty()
	{
		$rule = new Natural;

		$this->assertFalse($rule->validateWhenEmpty());
	}

	/**
	 *
	 */
	public function testWithValidValue()
	{
		$rule = new Natural;

		$this->assertTrue($rule->validate(0, []));
		$this->assertTrue($rule->validate(1, []));
		$this->assertTrue($rule->validate('1', []));
	}

	/**
	 *
	 */
	public function testWithInvalidValue()
	{
		$rule = new Natural;

		$this->assertFalse($rule->validate(1.1, []));
		$this->assertFalse($rule->validate('1.1', []));

		$this->assertSame('The foobar field must contain a natural number.', $rule->getErrorMessage('foobar'));
	}
}
