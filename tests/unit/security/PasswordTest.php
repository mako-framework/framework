<?php

namespace mako\tests\unit\security;

use mako\security\Password;

/**
 * @group unit
 */

class PasswordTest extends \PHPUnit_Framework_TestCase
{
	/**
	 *
	 */

	public function testHash()
	{
		$password = 'foobar';

		$hash1 = Password::hash($password, 4);

		$hash2 = Password::hash($password, 4);

		$this->assertNotEquals($password, $hash1);

		$this->assertNotEquals($password, $hash2);

		$this->assertNotEquals($hash1, $hash2);

		if(PASSWORD_DEFAULT === PASSWORD_BCRYPT)
		{
			$this->assertEquals(60, strlen($hash1));

			$this->assertEquals(60, strlen($hash2));
		}
	}

	/**
	 *
	 */

	public function testValidate()
	{
		$password = 'foobar';

		$hash = Password::hash($password, 4);

		$this->assertTrue(Password::validate('foobar', $hash));

		$this->assertFalse(Password::validate('føøbar', $hash));
	}

	/**
	 *
	 */

	public function testNeedsRehash()
	{
		$hash = Password::hash('foobar', 4);

		$this->assertFalse(Password::needsRehash($hash, 4));

		$this->assertTrue(Password::needsRehash($hash, 5));
	}

	/**
	 *
	 */

	public function testSetAndGetDefaultComputingCost()
	{
		Password::setDefaultComputingCost(12);

		$this->assertSame(12, Password::getDefaultComputingCost());
	}
}