<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\validator\rules;

use mako\tests\TestCase;
use mako\validator\rules\IP;

/**
 * @group unit
 */
class IPTest extends TestCase
{
	/**
	 *
	 */
	public function testWithValidValue()
	{
		$rule = new IP;

		$this->assertTrue($rule->validate('::1', []));
		$this->assertTrue($rule->validate('127.0.0.1', []));
	}

	/**
	 *
	 */
	public function testWithInvalidValue()
	{
		$rule = new IP;

		$this->assertFalse($rule->validate('127.0.0', []));

		$this->assertSame('The foobar field must contain a valid IP address.', $rule->getErrorMessage('foobar'));
	}
}
