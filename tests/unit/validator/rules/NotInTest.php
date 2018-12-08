<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\validator\rules;

use mako\tests\TestCase;
use mako\validator\rules\NotIn;

/**
 * @group unit
 */
class NotInTest extends TestCase
{
	/**
	 *
	 */
	public function testValidatesWhenEmpty(): void
	{
		$rule = new NotIn;

		$this->assertFalse($rule->validateWhenEmpty());
	}

	/**
	 *
	 */
	public function testWithValidValue(): void
	{
		$rule = new NotIn;

		$rule->setParameters([[1, 2, 3]]);

		$this->assertTrue($rule->validate(0, []));
		$this->assertTrue($rule->validate(4, []));
	}

	/**
	 *
	 */
	public function testWithInvalidValue(): void
	{
		$rule = new NotIn;

		$rule->setParameters([[1, 2, 3]]);

		$this->assertFalse($rule->validate(1, []));
		$this->assertFalse($rule->validate(2, []));
		$this->assertFalse($rule->validate(3, []));

		$this->assertSame('The foobar field contains an invalid value.', $rule->getErrorMessage('foobar'));
	}
}
