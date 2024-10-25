<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\validator\rules;

use mako\tests\TestCase;
use mako\validator\rules\Alpha;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class AlphaTest extends TestCase
{
	/**
	 *
	 */
	public function testValidatesWhenEmpty(): void
	{
		$rule = new Alpha;

		$this->assertFalse($rule->validateWhenEmpty());
	}

	/**
	 *
	 */
	public function testWithValidValue(): void
	{
		$rule = new Alpha;

		$this->assertTrue($rule->validate('foobar', '', []));
	}

	/**
	 *
	 */
	public function testWithInvalidValue(): void
	{
		$rule = new Alpha;

		$this->assertFalse($rule->validate('foobær', '', []));

		$this->assertSame('The foobar field must contain only letters.', $rule->getErrorMessage('foobar'));
	}
}
