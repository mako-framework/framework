<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\validator\rules;

use mako\tests\TestCase;
use mako\validator\rules\BooleanFalse;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class BooleanFalseTest extends TestCase
{
	/**
	 *
	 */
	public function testValidatesWhenEmpty(): void
	{
		$rule = new BooleanFalse;

		$this->assertFalse($rule->validateWhenEmpty());
	}

	/**
	 *
	 */
	public function testWithValidValue(): void
	{
		$rule = new BooleanFalse;

		$this->assertTrue($rule->validate(false, '', []));
	}

	/**
	 *
	 */
	public function testWithInvalidValue(): void
	{
		$rule = new BooleanFalse;

		$this->assertFalse($rule->validate(true, '', []));

		$this->assertSame('The foobar field must contain the boolean value FALSE.', $rule->getErrorMessage('foobar'));
	}
}
