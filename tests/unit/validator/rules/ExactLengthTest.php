<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\validator\rules;

use mako\tests\TestCase;
use mako\validator\rules\ExactLength;

/**
 * @group unit
 */
class ExactLengthTest extends TestCase
{
	/**
	 *
	 */
	public function testValidatesWhenEmpty(): void
	{
		$rule = new ExactLength(3);

		$this->assertFalse($rule->validateWhenEmpty());
	}

	/**
	 *
	 */
	public function testWithValidValue(): void
	{
		$rule = new ExactLength(3);

		$this->assertTrue($rule->validate('foo', '', []));
	}

	/**
	 *
	 */
	public function testWithInvalidValue(): void
	{
		$rule = new ExactLength(3);

		$this->assertFalse($rule->validate('foobar', '', []));

		$this->assertSame('The value of the foobar field must be exactly 3 characters long.', $rule->getErrorMessage('foobar'));
	}
}
