<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\validator\rules;

use mako\tests\TestCase;
use mako\validator\rules\MinLength;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class MinLengthTest extends TestCase
{
	/**
	 *
	 */
	public function testValidatesWhenEmpty(): void
	{
		$rule = new MinLength(3);

		$this->assertFalse($rule->validateWhenEmpty());
	}

	/**
	 *
	 */
	public function testWithValidValue(): void
	{
		$rule = new MinLength(3);

		$this->assertTrue($rule->validate('foo', '', []));
		$this->assertTrue($rule->validate('foobar', '', []));
	}

	/**
	 *
	 */
	public function testWithInvalidValue(): void
	{
		$rule = new MinLength(9);

		$this->assertFalse($rule->validate('foobar', '', []));

		$this->assertSame('The value of the foobar field must be at least 9 characters long.', $rule->getErrorMessage('foobar'));
	}
}
