<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\validator\rules;

use mako\tests\TestCase;
use mako\validator\rules\IP;
use RuntimeException;

/**
 * @group unit
 */
class IPTest extends TestCase
{
	/**
	 *
	 */
	public function testValidatesWhenEmpty(): void
	{
		$rule = new IP;

		$this->assertFalse($rule->validateWhenEmpty());
	}

	/**
	 *
	 */
	public function testWithValidValue(): void
	{
		$rule = new IP;

		$this->assertTrue($rule->validate('::1', []));
		$this->assertTrue($rule->validate('127.0.0.1', []));
	}

	/**
	 *
	 */
	public function testWithInvalidValue(): void
	{
		$rule = new IP;

		$this->assertFalse($rule->validate('127.0.0', []));

		$this->assertSame('The foobar field must contain a valid IP address.', $rule->getErrorMessage('foobar'));
	}

	/**
	 *
	 */
	public function testWithValidValueIpV4(): void
	{
		$rule = new IP('v4');

		$this->assertTrue($rule->validate('127.0.0.1', []));
	}

	/**
	 *
	 */
	public function testWithInvalidValueIpV4(): void
	{
		$rule = new IP('v4');

		$this->assertFalse($rule->validate('::1', []));

		$this->assertSame('The foobar field must contain a valid IPv4 address.', $rule->getErrorMessage('foobar'));
	}

	/**
	 *
	 */
	public function testWithValidValueIpV6(): void
	{
		$rule = new IP('v6');

		$this->assertTrue($rule->validate('::1', []));
	}

	/**
	 *
	 */
	public function testWithInvalidValueIpV6(): void
	{
		$rule = new IP('v6');

		$this->assertFalse($rule->validate('127.0.0.1', []));

		$this->assertSame('The foobar field must contain a valid IPv6 address.', $rule->getErrorMessage('foobar'));
	}

	/**
	 *
	 */
	public function testWithInvalidVersion(): void
	{
		$this->expectException(RuntimeException::class);

		$this->expectExceptionMessage('Invalid IP version [ v7 ]. The accepted versions are v4 and v6.');

		$rule = new IP('v7');

		$this->assertTrue($rule->validate('::1', []));
	}
}
