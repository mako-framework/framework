<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\validator\rules;

use mako\tests\TestCase;
use mako\validator\rules\Alphanumeric;

/**
 * @group unit
 */
class AlphanumericTest extends TestCase
{
	/**
	 *
	 */
	public function testValidatesWhenEmpty(): void
	{
		$rule = new Alphanumeric;

		$this->assertFalse($rule->validateWhenEmpty());
	}

	/**
	 *
	 */
	public function testWithValidValue(): void
	{
		$rule = new Alphanumeric;

		$this->assertTrue($rule->validate('foobar1', []));
	}

	/**
	 *
	 */
	public function testWithInvalidValue(): void
	{
		$rule = new Alphanumeric;

		$this->assertFalse($rule->validate('foobær1', []));

		$this->assertSame('The foobar field must contain only letters and numbers.', $rule->getErrorMessage('foobar'));
	}
}
