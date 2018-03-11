<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\validator\rules;

use mako\tests\TestCase;
use mako\validator\rules\LessThan;

/**
 * @group unit
 */
class LessThanTest extends TestCase
{
	/**
	 *
	 */
	public function testWithValidValue()
	{
		$rule = new LessThan;

		$rule->setParameters([3]);

		$this->assertTrue($rule->validate(2, []));
		$this->assertTrue($rule->validate('2', []));
	}

	/**
	 *
	 */
	public function testWithInvalidValue()
	{
		$rule = new LessThan;

		$rule->setParameters([3]);

		$this->assertFalse($rule->validate(3, []));
		$this->assertFalse($rule->validate('3', []));

		$this->assertSame('The value of the foobar field must be less than 3.', $rule->getErrorMessage('foobar'));
	}
}
