<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\validator\rules;

use mako\tests\TestCase;
use mako\validator\rules\AlphaUnicode;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class AlphaUnicodeTest extends TestCase
{
	/**
	 *
	 */
	public function testValidatesWhenEmpty(): void
	{
		$rule = new AlphaUnicode;

		$this->assertFalse($rule->validateWhenEmpty());
	}

	/**
	 *
	 */
	public function testWithValidValue(): void
	{
		$rule = new AlphaUnicode;

		$this->assertTrue($rule->validate('foobær', '', []));
	}

	/**
	 *
	 */
	public function testWithInvalidValue(): void
	{
		$rule = new AlphaUnicode;

		$this->assertFalse($rule->validate('foobær!', '', []));

		$this->assertSame('The foobar field must contain only letters.', $rule->getErrorMessage('foobar'));
	}
}
