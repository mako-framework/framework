<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\validator\rules;

use mako\tests\TestCase;
use mako\validator\rules\Str;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class StrTest extends TestCase
{
	/**
	 *
	 */
	public function testValidatesWhenEmpty(): void
	{
		$rule = new Str;

		$this->assertFalse($rule->validateWhenEmpty());
	}

	/**
	 *
	 */
	public function testWithValidValue(): void
	{
		$rule = new Str;

		$this->assertTrue($rule->validate('1', '', []));
	}

	/**
	 *
	 */
	public function testWithInvalidValue(): void
	{
		$rule = new Str;

		$this->assertFalse($rule->validate(1, '', []));

		$this->assertSame('The foobar field must contain a string.', $rule->getErrorMessage('foobar'));
	}
}
