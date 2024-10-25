<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\validator\rules;

use mako\tests\TestCase;
use mako\validator\rules\Date;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class DateTest extends TestCase
{
	/**
	 *
	 */
	public function testValidatesWhenEmpty(): void
	{
		$rule = new Date('Y-m-d');

		$this->assertFalse($rule->validateWhenEmpty());
	}

	/**
	 *
	 */
	public function testWithValidValue(): void
	{
		$rule = new Date('Y-m-d');

		$this->assertTrue($rule->validate('2018-12-24', '', []));
	}

	/**
	 *
	 */
	public function testWithInvalidValue(): void
	{
		$rule = new Date('Y-m-d');

		$this->assertFalse($rule->validate('2018-24-12', '', []));

		$this->assertSame('The foobar field must contain a valid date.', $rule->getErrorMessage('foobar'));
	}
}
