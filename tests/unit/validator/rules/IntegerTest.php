<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\validator\rules;

use mako\tests\TestCase;
use mako\validator\rules\Integer;

/**
 * @group unit
 */
class IntegerTest extends TestCase
{
	/**
	 *
	 */
	public function testValidatesWhenEmpty()
	{
		$rule = new Integer;

		$this->assertFalse($rule->validateWhenEmpty());
	}

	/**
	 *
	 */
	public function testWithValidValue()
	{
		$rule = new Integer;

		$this->assertTrue($rule->validate(1, []));
		$this->assertTrue($rule->validate('1', []));
	}

	/**
	 *
	 */
	public function testWithInvalidValue()
	{
		$rule = new Integer;

		$this->assertFalse($rule->validate(1.1, []));
		$this->assertFalse($rule->validate('1.1', []));

		$this->assertSame('The foobar field must contain an integer.', $rule->getErrorMessage('foobar'));
	}
}
