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
	public function testValidatesWhenEmpty()
	{
		$rule = new IP;

		$this->assertFalse($rule->validateWhenEmpty());
	}

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

	/**
	 *
	 */
	public function testWithValidValueIpV4()
	{
		$rule = new IP;

		$rule->setParameters(['v4']);

		$this->assertTrue($rule->validate('127.0.0.1', []));
	}

	/**
	 *
	 */
	public function testWithInvalidValueIpV4()
	{
		$rule = new IP;

		$rule->setParameters(['v4']);

		$this->assertFalse($rule->validate('::1', []));

		$this->assertSame('The foobar field must contain a valid IPv4 address.', $rule->getErrorMessage('foobar'));
	}

	/**
	 *
	 */
	public function testWithValidValueIpV6()
	{
		$rule = new IP;

		$rule->setParameters(['v6']);

		$this->assertTrue($rule->validate('::1', []));
	}

	/**
	 *
	 */
	public function testWithInvalidValueIpV6()
	{
		$rule = new IP;

		$rule->setParameters(['v6']);

		$this->assertFalse($rule->validate('127.0.0.1', []));

		$this->assertSame('The foobar field must contain a valid IPv6 address.', $rule->getErrorMessage('foobar'));
	}

	/**
	 * @expectedException RuntimeException
	 * @expectedExceptionMessage Invalid IP version [ v7 ]. The accepted versions are v4 and v6.
	 */
	public function testWithInvalidVersion()
	{
		$rule = new IP;

		$rule->setParameters(['v7']);

		$this->assertTrue($rule->validate('::1', []));
	}
}
