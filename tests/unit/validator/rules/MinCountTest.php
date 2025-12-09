<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\validator\rules;

use mako\tests\TestCase;
use mako\validator\rules\MinCount;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class MinCountTest extends TestCase
{
	/**
	 *
	 */
	public function testValidatesWhenEmpty(): void
	{
		$rule = new MinCount(3);

		$this->assertFalse($rule->validateWhenEmpty());
	}

	/**
	 *
	 */
	public function testWithValidValue(): void
	{
		$rule = new MinCount(3);

		$this->assertFalse($rule->validate([], '', []));
		$this->assertTrue($rule->validate([1, 2, 3], '', []));
		$this->assertTrue($rule->validate([1, 2, 3, 4], '', []));
	}

	/**
	 *
	 */
	public function testWithInvalidValue(): void
	{
		$rule = new MinCount(1);

		$this->assertFalse($rule->validate([], '', []));

		$this->assertSame('The value of the foobar field must contain at least 1 items.', $rule->getErrorMessage('foobar'));
	}
}
