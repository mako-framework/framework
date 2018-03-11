<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\validator\rules;

use mako\tests\TestCase;
use mako\validator\rules\Between;

/**
 * @group unit
 */
class BetweenTest extends TestCase
{
	/**
	 *
	 */
	public function testValidatesWhenEmpty()
	{
		$rule = new Between;

		$this->assertFalse($rule->validateWhenEmpty());
	}

	/**
	 *
	 */
	public function testWithValidValue()
	{
		$rule = new Between;

		$rule->setParameters([1, 3]);

		$this->assertTrue($rule->validate('1', []));
		$this->assertTrue($rule->validate('2', []));
		$this->assertTrue($rule->validate('3', []));
	}

	/**
	 *
	 */
	public function testWithInvalidValue()
	{
		$rule = new Between;

		$rule->setParameters([1, 3]);

		$this->assertFalse($rule->validate('0', []));
		$this->assertFalse($rule->validate('4', []));

		$this->assertSame('The value of the foobar field must be between 1 and 3.', $rule->getErrorMessage('foobar'));
	}
}
