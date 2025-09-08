<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\validator\rules;

use mako\tests\TestCase;
use mako\validator\rules\In;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class InTest extends TestCase
{
	/**
	 *
	 */
	public function testValidatesWhenEmpty(): void
	{
		$rule = new In([1, 2, 3]);

		$this->assertFalse($rule->validateWhenEmpty());
	}

	/**
	 *
	 */
	public function testWithValidValue(): void
	{
		$rule = new In([1, 2, 3]);

		$this->assertTrue($rule->validate(1, '', []));
		$this->assertTrue($rule->validate(2, '', []));
		$this->assertTrue($rule->validate(3, '', []));
	}

	/**
	 *
	 */
	public function testWithInvalidValue(): void
	{
		$rule = new In([1, 2, 3]);

		$this->assertFalse($rule->validate(0, '', []));

		$this->assertSame('The foobar field must contain one of the available options.', $rule->getErrorMessage('foobar'));
	}
}
