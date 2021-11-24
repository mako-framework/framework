<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\validator\rules;

use mako\tests\TestCase;
use mako\validator\rules\Before;

/**
 * @group unit
 */
class BeforeTest extends TestCase
{
	/**
	 *
	 */
	public function testValidatesWhenEmpty(): void
	{
		$rule = new Before('Y-m-d', '2018-03-11');

		$this->assertFalse($rule->validateWhenEmpty());
	}

	/**
	 *
	 */
	public function testWithValidValue(): void
	{
		$rule = new Before('Y-m-d', '2018-03-11');

		$this->assertTrue($rule->validate('2018-03-10', '', []));
	}

	/**
	 *
	 */
	public function testWithInvalidValue(): void
	{
		$rule = new Before('Y-m-d', '2018-03-11');

		$this->assertFalse($rule->validate('2018-03-12', '', []));

		$this->assertFalse($rule->validate('2017-24-12', '', []));

		$this->assertSame('The foobar field must contain a valid date before 2018-03-11.', $rule->getErrorMessage('foobar'));
	}
}
