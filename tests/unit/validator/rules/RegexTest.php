<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\validator\rules;

use mako\tests\TestCase;
use mako\validator\rules\Regex;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class RegexTest extends TestCase
{
	/**
	 *
	 */
	public function testValidatesWhenEmpty(): void
	{
		$rule = new Regex('/^[a-z]+$/i');

		$this->assertFalse($rule->validateWhenEmpty());
	}

	/**
	 *
	 */
	public function testWithValidValue(): void
	{
		$rule = new Regex('/^[a-z]+$/i');

		$this->assertTrue($rule->validate('foobar', '', []));
	}

	/**
	 *
	 */
	public function testWithInvalidValue(): void
	{
		$rule = new Regex('/^[a-z]+$/i');

		$this->assertFalse($rule->validate('foobar1', '', []));

		$this->assertSame('The value of the foobar field does not match the required format.', $rule->getErrorMessage('foobar'));
	}
}
