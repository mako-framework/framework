<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\validator\rules;

use mako\tests\TestCase;
use mako\validator\rules\Required;

/**
 * @group unit
 */
class RequiredTest extends TestCase
{
	/**
	 *
	 */
	public function testWithValidValue()
	{
		$rule = new Required;

		$this->assertTrue($rule->validate('foobar', []));
	}

	/**
	 *
	 */
	public function testWithInvalidValue()
	{
		$rule = new Required;

		$this->assertFalse($rule->validate('', []));
		$this->assertFalse($rule->validate([], []));
		$this->assertFalse($rule->validate(null, []));

		$this->assertSame('The foobar field is required.', $rule->getErrorMessage('foobar'));
	}
}
