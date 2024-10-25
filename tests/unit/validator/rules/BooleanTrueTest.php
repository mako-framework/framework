<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\validator\rules;

use mako\tests\TestCase;
use mako\validator\rules\BooleanTrue;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class BooleanTrueTest extends TestCase
{
	/**
	 *
	 */
	public function testValidatesWhenEmpty(): void
	{
		$rule = new BooleanTrue;

		$this->assertFalse($rule->validateWhenEmpty());
	}

	/**
	 *
	 */
	public function testWithValidValue(): void
	{
		$rule = new BooleanTrue;

		$this->assertTrue($rule->validate(true, '', []));
	}

	/**
	 *
	 */
	public function testWithInvalidValue(): void
	{
		$rule = new BooleanTrue;

		$this->assertFalse($rule->validate(false, '', []));

		$this->assertSame('The foobar field must contain the boolean value TRUE.', $rule->getErrorMessage('foobar'));
	}
}
