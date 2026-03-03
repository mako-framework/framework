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
		$this->assertSame(1, LoginStatus::Ok->value);
		$this->assertSame(2, LoginStatus::Banned->value);
		$this->assertSame(3, LoginStatus::NotActivated->value);
		$this->assertSame(4, LoginStatus::InvalidCredentials->value);
		$this->assertSame(5, LoginStatus::Locked->value);
	}

	/**
	 *
	 */
	public function testGetCode(): void
	{
		$this->assertSame(1, LoginStatus::Ok->getCode());
		$this->assertSame(2, LoginStatus::Banned->getCode());
		$this->assertSame(3, LoginStatus::NotActivated->getCode());
		$this->assertSame(4, LoginStatus::InvalidCredentials->getCode());
		$this->assertSame(5, LoginStatus::Locked->getCode());
	}

	/**
	 *
	 */
	public function testToBool(): void
	{
		$this->assertTrue(LoginStatus::Ok->toBool());
		$this->assertFalse(LoginStatus::Banned->toBool());
		$this->assertFalse(LoginStatus::NotActivated->toBool());
		$this->assertFalse(LoginStatus::InvalidCredentials->toBool());
		$this->assertFalse(LoginStatus::Locked->toBool());
	}
}
