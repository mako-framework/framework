<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\validator\rules;

use mako\tests\TestCase;
use mako\validator\rules\UniqueValues;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class UniqueValuesTest extends TestCase
{
	/**
	 *
	 */
	public function testValidatesWhenEmpty(): void
	{
		$rule = new UniqueValues;

		$this->assertFalse($rule->validateWhenEmpty());
	}

	/**
	 *
	 */
	public function testWithValidValue(): void
	{
		$rule = new UniqueValues;

		$this->assertTrue($rule->validate([], '', []));
		$this->assertTrue($rule->validate([1, 2, 3], '', []));
		$this->assertTrue($rule->validate([1, 2, 3, 4], '', []));
	}

	/**
	 *
	 */
	public function testWithInvalidValue(): void
	{
		$rule = new UniqueValues;

		$this->assertFalse($rule->validate([1, 2, 3, 3], '', []));

		$this->assertSame('The foobar field must only contain unique values.', $rule->getErrorMessage('foobar'));
	}
}
