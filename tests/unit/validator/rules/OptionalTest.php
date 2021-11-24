<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\validator\rules;

use mako\tests\TestCase;
use mako\validator\rules\Optional;

/**
 * @group unit
 */
class OptionalTest extends TestCase
{
	/**
	 *
	 */
	public function testValidatesWhenEmpty(): void
	{
		$rule = new Optional;

		$this->assertFalse($rule->validateWhenEmpty());
	}

	/**
	 *
	 */
	public function testWithValidValue(): void
	{
		$rule = new Optional;

		$this->assertTrue($rule->validate('foobar', '', []));
		$this->assertTrue($rule->validate('', '', []));
		$this->assertTrue($rule->validate(null, '', []));
		$this->assertTrue($rule->validate([], '', []));
	}

	/**
	 *
	 */
	public function testGetErrorMessage(): void
	{
		$rule = new Optional;

		$this->assertSame('', $rule->getErrorMessage('foobar'));
	}
}
