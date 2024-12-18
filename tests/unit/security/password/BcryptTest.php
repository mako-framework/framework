<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\security\password;

use mako\security\password\Bcrypt;
use mako\tests\TestCase;

/**
 * @group unit
 */
class BcryptTest extends TestCase
{
	/**
	 *
	 */
	public function testNormalizeOptions(): void
	{
		$hasher = new class extends Bcrypt {
			public function normalizeOptions(array $options): array
			{
				return parent::normalizeOptions($options);
			}
		};

		$this->assertSame(['cost' => PHP_VERSION_ID >= 80400 ? 12 : 10], $hasher->normalizeOptions([]));

		$this->assertSame(['cost' => 10], $hasher->normalizeOptions(['cost' => 10]));

		$this->assertSame(['cost' => 31], $hasher->normalizeOptions(['cost' => 40]));

		$this->assertSame(['cost' => 4], $hasher->normalizeOptions(['cost' => 1]));
	}

	/**
	 *
	 */
	public function testCreate(): void
	{
		$password = 'foobar';

		$hasher = new Bcrypt(['cost' => 4]);

		$hash1 = $hasher->create($password);

		$hash2 = $hasher->create($password);

		$this->assertNotEquals($password, $hash1);

		$this->assertNotEquals($password, $hash2);

		$this->assertNotEquals($hash1, $hash2);

		$this->assertEquals(60, strlen($hash1));

		$this->assertEquals(60, strlen($hash2));
	}

	/**
	 *
	 */
	public function testVerify(): void
	{
		$password = 'foobar';

		$hasher = new Bcrypt(['cost' => 4]);

		$hash = $hasher->create($password);

		$this->assertTrue($hasher->verify($password, $hash));

		$this->assertFalse($hasher->verify($password . 'x', $hash));
	}

	/**
	 *
	 */
	public function testNeedsRehash(): void
	{
		$password = 'foobar';

		$hasher1 = new Bcrypt(['cost' => 4]);
		$hasher2 = new Bcrypt(['cost' => 5]);

		$hash = $hasher1->create($password);

		$this->assertFalse($hasher1->needsRehash($hash));

		$this->assertTrue($hasher2->needsRehash($hash));
	}
}
