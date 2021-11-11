<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\validator\rules;

use mako\tests\TestCase;
use mako\validator\rules\Arr;

/**
 * @group unit
 */
class ArrTest extends TestCase
{
	/**
	 *
	 */
	public function testValidatesWhenEmpty(): void
	{
		$rule = new Arr;

		$this->assertFalse($rule->validateWhenEmpty());
	}

	/**
	 *
	 */
	public function testWithValidValue(): void
	{
		$rule = new Arr;

		$this->assertTrue($rule->validate([], []));
	}

	/**
	 *
	 */
	public function testWithInvalidValue(): void
	{
		$rule = new Arr;

		$this->assertFalse($rule->validate(1, []));

		$this->assertSame('The foobar field must contain an array.', $rule->getErrorMessage('foobar'));
	}
}
