<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\validator\rules;

use mako\tests\TestCase;
use mako\validator\rules\Different;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class DifferentTest extends TestCase
{
	/**
	 *
	 */
	public function testValidatesWhenEmpty(): void
	{
		$rule = new Different('barfoo');

		$this->assertFalse($rule->validateWhenEmpty());
	}

	/**
	 *
	 */
	public function testWithValidValue(): void
	{
		$rule = new Different('barfoo');

		$this->assertTrue($rule->validate('foo', '', ['barfoo' => 'bar']));
	}

	/**
	 *
	 */
	public function testWithInvalidValue(): void
	{
		$rule = new Different('barfoo');

		$this->assertFalse($rule->validate('bar', '', ['barfoo' => 'bar']));

		$this->assertSame('The values of the foobar field and barfoo field must be different.', $rule->getErrorMessage('foobar'));
	}
}
