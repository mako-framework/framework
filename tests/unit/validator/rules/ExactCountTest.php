<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\validator\rules;

use mako\tests\TestCase;
use mako\validator\rules\ExactCount;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class ExactCountTest extends TestCase
{
	/**
	 *
	 */
	public function testValidatesWhenEmpty(): void
	{
		$rule = new ExactCount(3);

		$this->assertFalse($rule->validateWhenEmpty());
	}

	/**
	 *
	 */
	public function testWithValidValue(): void
	{
		$rule = new ExactCount(3);

		$this->assertFalse($rule->validate([], '', []));
		$this->assertTrue($rule->validate([1, 2, 3], '', []));
		$this->assertFalse($rule->validate([1, 2, 3, 4], '', []));
	}

	/**
	 *
	 */
	public function testWithInvalidValue(): void
	{
		$rule = new ExactCount(1);

		$this->assertFalse($rule->validate([1, 2], '', []));

		$this->assertSame('The value of the foobar field must contain exactly 1 items.', $rule->getErrorMessage('foobar'));
	}
}
