<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\validator\rules;

use mako\tests\TestCase;
use mako\validator\rules\NotEmpty;

/**
 * @group unit
 */
class NotEmptyTest extends TestCase
{
	/**
	 *
	 */
	public function testValidatesWhenEmpty(): void
	{
		$rule = new NotEmpty;

		$this->assertTrue($rule->validateWhenEmpty());
	}

	/**
	 *
	 */
	public function testWithValidValue(): void
	{
		$rule = new NotEmpty;

		$this->assertTrue($rule->validate('', 'foo', []));

		$this->assertTrue($rule->validate('foobar', 'foo', ['foo' => 'foobar']));
	}

	/**
	 *
	 */
	public function testWithInvalidValue(): void
	{
		$rule = new NotEmpty;

		$this->assertFalse($rule->validate('', 'foo', ['foo' => '']));
		$this->assertFalse($rule->validate([], 'foo', ['foo' => []]));
		$this->assertFalse($rule->validate(null, 'foo', ['foo' => null]));

		$this->assertSame('The foobar field can\'t be empty.', $rule->getErrorMessage('foobar'));
	}
}
