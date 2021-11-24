<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\validator\rules;

use mako\tests\TestCase;
use mako\validator\rules\Boolean;

/**
 * @group unit
 */
class BooleanTest extends TestCase
{
	/**
	 *
	 */
	public function testValidatesWhenEmpty(): void
	{
		$rule = new Boolean;

		$this->assertFalse($rule->validateWhenEmpty());
	}

	/**
	 *
	 */
	public function testWithValidValue(): void
	{
		$rule = new Boolean;

		$this->assertTrue($rule->validate(true, '', []));
		$this->assertTrue($rule->validate(false, '', []));
	}

	/**
	 *
	 */
	public function testWithInvalidValue(): void
	{
		$rule = new Boolean;

		$this->assertFalse($rule->validate(1, '', []));
		$this->assertFalse($rule->validate(0, '', []));

		$this->assertSame('The foobar field must contain a boolean.', $rule->getErrorMessage('foobar'));
	}
}
