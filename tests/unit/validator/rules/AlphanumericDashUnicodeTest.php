<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\validator\rules;

use mako\tests\TestCase;
use mako\validator\rules\AlphanumericDashUnicode;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class AlphanumericDashUnicodeTest extends TestCase
{
	/**
	 *
	 */
	public function testValidatesWhenEmpty(): void
	{
		$rule = new AlphanumericDashUnicode;

		$this->assertFalse($rule->validateWhenEmpty());
	}

	/**
	 *
	 */
	public function testWithValidValue(): void
	{
		$rule = new AlphanumericDashUnicode;

		$this->assertTrue($rule->validate('foo-bær_1', '', []));
	}

	/**
	 *
	 */
	public function testWithInvalidValue(): void
	{
		$rule = new AlphanumericDashUnicode;

		$this->assertFalse($rule->validate('foo-bær_1.', '', []));

		$this->assertSame('The foobar field must contain only numbers, letters and dashes.', $rule->getErrorMessage('foobar'));
	}
}
