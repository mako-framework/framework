<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\validator\rules;

use mako\tests\TestCase;
use mako\validator\rules\Email;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class EmailTest extends TestCase
{
	/**
	 *
	 */
	public function testValidatesWhenEmpty(): void
	{
		$rule = new Email;

		$this->assertFalse($rule->validateWhenEmpty());
	}

	/**
	 *
	 */
	public function testWithValidValue(): void
	{
		$rule = new Email;

		$this->assertTrue($rule->validate('foo@example.org', '', []));
	}

	/**
	 *
	 */
	public function testWithInvalidValue(): void
	{
		$rule = new Email;

		$this->assertFalse($rule->validate('foo@example', '', []));

		$this->assertSame('The foobar field must contain a valid e-mail address.', $rule->getErrorMessage('foobar'));
	}
}
