<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\security\password;

use mako\security\password\Argon2i;
use mako\tests\TestCase;

/**
 * @group unit
 */
class Argon2iTest extends TestCase
{
	/**
	 *
	 */
	public function setUp()
	{
		if(!defined('PASSWORD_ARGON2I'))
		{
			$this->markTestSkipped('PHP has not been compiled with Argon2i support.');
		}
	}

	/**
	 *
	 */
	public function testCreate()
	{
		$password = 'foobar';

		$hasher = new Argon2i(['time_cost' => 1]);

		$hash1 = $hasher->create($password);

		$hash2 = $hasher->create($password);

		$this->assertNotEquals($password, $hash1);

		$this->assertNotEquals($password, $hash2);

		$this->assertNotEquals($hash1, $hash2);
	}

	/**
	 *
	 */
	public function testVerify()
	{
		$password = 'foobar';

		$hasher = new Argon2i;

		$hash = $hasher->create($password);

		$this->assertTrue($hasher->verify($password, $hash));

		$this->assertFalse($hasher->verify($password . 'x', $hash));
	}

	/**
	 *
	 */
	public function testNeedsRehash()
	{
		$password = 'foobar';

		$hasher1 = new Argon2i(['time_cost' => 1]);
		$hasher2 = new Argon2i(['time_cost' => 2]);

		$hash = $hasher1->create($password);

		$this->assertFalse($hasher1->needsRehash($hash));

		$this->assertTrue($hasher2->needsRehash($hash));
	}
}
