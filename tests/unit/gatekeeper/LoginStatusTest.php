<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\gatekeeper;

use mako\gatekeeper\LoginStatus;
use mako\tests\TestCase;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class LoginStatusTest extends TestCase
{
	/**
	 *
	 */
	public function testLoginStatus(): void
	{
		$this->assertSame(1, LoginStatus::OK->value);
		$this->assertSame(2, LoginStatus::BANNED->value);
		$this->assertSame(3, LoginStatus::NOT_ACTIVATED->value);
		$this->assertSame(4, LoginStatus::INVALID_CREDENTIALS->value);
		$this->assertSame(5, LoginStatus::LOCKED->value);
	}

	/**
	 *
	 */
	public function testGetCode(): void
	{
		$this->assertSame(1, LoginStatus::OK->getCode());
		$this->assertSame(2, LoginStatus::BANNED->getCode());
		$this->assertSame(3, LoginStatus::NOT_ACTIVATED->getCode());
		$this->assertSame(4, LoginStatus::INVALID_CREDENTIALS->getCode());
		$this->assertSame(5, LoginStatus::LOCKED->getCode());
	}

	/**
	 *
	 */
	public function testToBool(): void
	{
		$this->assertTrue(LoginStatus::OK->toBool());
		$this->assertFalse(LoginStatus::BANNED->toBool());
		$this->assertFalse(LoginStatus::NOT_ACTIVATED->toBool());
		$this->assertFalse(LoginStatus::INVALID_CREDENTIALS->toBool());
		$this->assertFalse(LoginStatus::LOCKED->toBool());
	}
}
