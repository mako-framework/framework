<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\validator\rules;

use mako\tests\TestCase;
use mako\validator\rules\FloatingPoint;

/**
 * @group unit
 */
class FloatingPointTest extends TestCase
{
	/**
	 *
	 */
	public function testValidatesWhenEmpty()
	{
		$rule = new FloatingPoint;

		$this->assertFalse($rule->validateWhenEmpty());
	}

	/**
	 *
	 */
	public function testWithValidValue()
	{
		$rule = new FloatingPoint;

		$this->assertTrue($rule->validate(1.2, []));
		$this->assertTrue($rule->validate('1.2', []));
	}

	/**
	 *
	 */
	public function testWithInvalidValue()
	{
		$rule = new FloatingPoint;

		$this->assertFalse($rule->validate('1', []));

		$this->assertSame('The foobar field must contain a float.', $rule->getErrorMessage('foobar'));
	}
}
