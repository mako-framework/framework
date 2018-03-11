<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\validator\rules;

use mako\tests\TestCase;
use mako\validator\rules\GreaterThanOrEqualTo;

/**
 * @group unit
 */
class GreaterThanOrEqualToTest extends TestCase
{
	/**
	 *
	 */
	public function testWithValidValue()
	{
		$rule = new GreaterThanOrEqualTo;

		$rule->setParameters([3]);

		$this->assertTrue($rule->validate(3, []));
		$this->assertTrue($rule->validate('3', []));
	}

	/**
	 *
	 */
	public function testWithInvalidValue()
	{
		$rule = new GreaterThanOrEqualTo;

		$rule->setParameters([3]);

		$this->assertFalse($rule->validate(2, []));
		$this->assertFalse($rule->validate('2', []));

		$this->assertSame('The value of the foobar field must be greater than or equal to 3.', $rule->getErrorMessage('foobar'));
	}
}
