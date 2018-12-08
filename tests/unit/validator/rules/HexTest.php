<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\validator\rules;

use mako\tests\TestCase;
use mako\validator\rules\Hex;

/**
 * @group unit
 */
class HexTest extends TestCase
{
	/**
	 *
	 */
	public function testValidatesWhenEmpty(): void
	{
		$rule = new Hex;

		$this->assertFalse($rule->validateWhenEmpty());
	}

	/**
	 *
	 */
	public function testWithValidValue(): void
	{
		$rule = new Hex;

		$this->assertTrue($rule->validate('FF0000', []));
	}

	/**
	 *
	 */
	public function testWithInvalidValue(): void
	{
		$rule = new Hex;

		$this->assertFalse($rule->validate('XX0000', []));

		$this->assertSame('The foobar field must contain a valid hexadecimal value.', $rule->getErrorMessage('foobar'));
	}
}
