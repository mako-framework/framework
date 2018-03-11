<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\validator\rules;

use mako\tests\TestCase;
use mako\validator\rules\AlphaUnicode;

/**
 * @group unit
 */
class AlphaUnicodeTest extends TestCase
{
	/**
	 *
	 */
	public function testValidatesWhenEmpty()
	{
		$rule = new AlphaUnicode;

		$this->assertFalse($rule->validateWhenEmpty());
	}

	/**
	 *
	 */
	public function testWithValidValue()
	{
		$rule = new AlphaUnicode;

		$this->assertTrue($rule->validate('foobær', []));
	}

	/**
	 *
	 */
	public function testWithInvalidValue()
	{
		$rule = new AlphaUnicode;

		$this->assertFalse($rule->validate('foobær!', []));

		$this->assertSame('The foobar field must contain only letters.', $rule->getErrorMessage('foobar'));
	}
}
