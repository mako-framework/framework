<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\validator\rules;

use mako\tests\TestCase;
use mako\validator\rules\After;

/**
 * @group unit
 */
class AfterTest extends TestCase
{
	/**
	 *
	 */
	public function testWithValidValue()
	{
		$rule = new After;

		$rule->setParameters(['Y-m-d', '2018-03-11']);

		$this->assertTrue($rule->validate('2018-03-12', []));
	}

	/**
	 *
	 */
	public function testWithInvalidValue()
	{
		$rule = new After;

		$rule->setParameters(['Y-m-d', '2018-03-11']);

		$this->assertFalse($rule->validate('2018-03-10', []));

		$this->assertSame('The foobar field must contain a date after 2018-03-11.', $rule->getErrorMessage('foobar'));
	}
}
