<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\validator\rules;

use mako\tests\TestCase;
use mako\validator\rules\AlphanumericDash;

/**
 * @group unit
 */
class AlphanumericDashTest extends TestCase
{
	/**
	 *
	 */
	public function testValidatesWhenEmpty(): void
	{
		$rule = new AlphanumericDash;

		$this->assertFalse($rule->validateWhenEmpty());
	}

	/**
	 *
	 */
	public function testWithValidValue(): void
	{
		$rule = new AlphanumericDash;

		$this->assertTrue($rule->validate('foo-bar_1', []));
	}

	/**
	 *
	 */
	public function testWithInvalidValue(): void
	{
		$rule = new AlphanumericDash;

		$this->assertFalse($rule->validate('foo-bær_1', []));

		$this->assertSame('The foobar field must contain only numbers, letters and dashes.', $rule->getErrorMessage('foobar'));
	}
}
