<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\validator\rules;

use mako\tests\TestCase;
use mako\validator\rules\UUID;

/**
 * @group unit
 */
class UUIDTest extends TestCase
{
	/**
	 *
	 */
	public function testValidatesWhenEmpty(): void
	{
		$rule = new UUID;

		$this->assertFalse($rule->validateWhenEmpty());
	}

	/**
	 *
	 */
	public function testWithValidValue(): void
	{
		$rule = new UUID;

		$this->assertTrue($rule->validate('7d4a7737-899c-436b-a8ac-14ac83850687', '', []));
	}

	/**
	 *
	 */
	public function testWithInvalidValue(): void
	{
		$rule = new UUID;

		$this->assertFalse($rule->validate('foobar', '', []));

		$this->assertSame('The foobar field must contain a valid UUID.', $rule->getErrorMessage('foobar'));
	}
}
