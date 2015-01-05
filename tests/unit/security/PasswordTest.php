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

	public function testLegacyHash()
	{
		$this->assertTrue(Password::isLegacyHash(md5('foobar')));

		$this->assertFalse(Password::isLegacyHash(Password::hash('foobar', 4)));
	}

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

		$this->assertEquals(60, strlen($hash1));

		$this->assertEquals(60, strlen($hash2));
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

	public function testValidateLegacy()
	{
		$password = 'foobar';

		$hash1 = md5($password);

		$hash2 = Password::hash($password, 4);

		$this->assertTrue(Password::validate('foobar', $hash1, function($password, $hash)
		{
			return md5($password) === $hash;
		}));

		$this->assertTrue(Password::validate('foobar', $hash2, function($password, $hash)
		{
			return md5($password) === $hash;
		}));

		$this->assertFalse(Password::validate('føøbar', $hash1, function($password, $hash)
		{
			return md5($password) === $hash;
		}));

		$this->assertFalse(Password::validate('føøbar', $hash2, function($password, $hash)
		{
			return md5($password) === $hash;
		}));
	}
}