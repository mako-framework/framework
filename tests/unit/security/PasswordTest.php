<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\tests\unit\security;

use PHPUnit_Framework_TestCase;

use mako\security\Password;

/**
 * @group unit
 */
class PasswordTest extends PHPUnit_Framework_TestCase
{
	/**
	 *
	 */
	public function setUp()
	{
		if(in_array(PASSWORD_DEFAULT, [PASSWORD_BCRYPT], true) === false)
		{
			$this->fail('Missing computing options for the default hashing algorithm');
		}
	}

	/**
	 *
	 */
	protected function getOptions()
	{
		if(PASSWORD_DEFAULT === PASSWORD_BCRYPT)
		{
			return ['cost' => 4];
		}
	}

	/**
	 *
	 */
	protected function getNewOptions()
	{
		if(PASSWORD_DEFAULT === PASSWORD_BCRYPT)
		{
			return ['cost' => 5];
		}
	}

	/**
	 *
	 */
	public function testHash()
	{
		$password = 'foobar';

		$hash1 = Password::hash($password, $this->getOptions());

		$hash2 = Password::hash($password, $this->getOptions());

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

		$hash = Password::hash($password, $this->getOptions());

		$this->assertTrue(Password::validate('foobar', $hash));

		$this->assertFalse(Password::validate('føøbar', $hash));
	}

	/**
	 *
	 */
	public function testNeedsRehash()
	{
		$hash = Password::hash('foobar', $this->getOptions());

		$this->assertFalse(Password::needsRehash($hash, $this->getOptions()));

		$this->assertTrue(Password::needsRehash($hash, $this->getNewOptions()));
	}

	/**
	 *
	 */
	public function testSetAndGetDefaultComputingCost()
	{
		if(PASSWORD_DEFAULT === PASSWORD_BCRYPT)
		{
			Password::setDefaultComputingOptions(['cost' => 12]);

			$this->assertSame(['cost' => 12], Password::getDefaultComputingOptions());
		}
	}
}
