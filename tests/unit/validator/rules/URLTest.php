<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\validator\rules;

use mako\tests\TestCase;
use mako\validator\rules\URL;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class URLTest extends TestCase
{
	/**
	 *
	 */
	public function testValidatesWhenEmpty(): void
	{
		$rule = new URL;

		$this->assertFalse($rule->validateWhenEmpty());
	}

	/**
	 *
	 */
	public function testWithValidValue(): void
	{
		$rule = new URL;

		$this->assertTrue($rule->validate('http://foobar', '', []));
	}

	/**
	 *
	 */
	public function testWithInvalidValue(): void
	{
		$rule = new URL;

		$this->assertFalse($rule->validate('foobar', '', []));

		$this->assertSame('The foobar field must contain a valid URL.', $rule->getErrorMessage('foobar'));
	}
}
