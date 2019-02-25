<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\utility;

use InvalidArgumentException;
use mako\tests\TestCase;
use mako\utility\UUID;

/**
 * @group unit
 */
class UUIDTest extends TestCase
{
	/**
	 *
	 */
	public function testNamespaces(): void
	{
		$this->assertEquals('6ba7b810-9dad-11d1-80b4-00c04fd430c8', UUID::DNS);

		$this->assertEquals('6ba7b811-9dad-11d1-80b4-00c04fd430c8', UUID::URL);

		$this->assertEquals('6ba7b812-9dad-11d1-80b4-00c04fd430c8', UUID::OID);

		$this->assertEquals('6ba7b814-9dad-11d1-80b4-00c04fd430c8', UUID::X500);
	}

	/**
	 *
	 */
	public function testValidate(): void
	{
		$this->assertTrue(UUID::validate('6ba7b814-9dad-11d1-80b4-00c04fd430c8'));

		$this->assertFalse(UUID::validate('6ba7b814-9dad-11d1-80b4-00c04fd430cx'));
	}

	/**
	 *
	 */
	public function testV3(): void
	{
		$this->assertEquals(3, substr(UUID::v3(UUID::DNS, 'hello'), 14, 1));

		$this->assertTrue(in_array(substr(UUID::v3(UUID::DNS, 'hello'), 19, 1), [8, 9, 'a', 'b']));

		$this->assertEquals(UUID::v3(UUID::DNS, 'hello'), UUID::v3(UUID::DNS, 'hello'));

		$this->assertNotEquals(UUID::v3(UUID::DNS, 'hello'), UUID::v3(UUID::URL, 'hello'));

		$this->assertNotEquals(UUID::v3(UUID::DNS, 'hello'), UUID::v3(UUID::DNS, 'goodbye'));

		$this->assertTrue(UUID::validate(UUID::v3(UUID::DNS, 'hello')));
	}

	/**
	 *
	 */
	public function testV3WithInvalidNamespace(): void
	{
		$this->expectException(InvalidArgumentException::class);

		UUID::v3('nope', 'foobar');
	}

	/**
	 *
	 */
	public function testV4(): void
	{
		$this->assertEquals(4, substr(UUID::v4(), 14, 1));

		$this->assertTrue(in_array(substr(UUID::v4(), 19, 1), [8, 9, 'a', 'b']));

		$this->assertTrue(UUID::validate(UUID::v4()));
	}

	/**
	 *
	 */
	public function testV5(): void
	{
		$this->assertEquals(5, substr(UUID::v5(UUID::DNS, 'hello'), 14, 1));

		$this->assertTrue(in_array(substr(UUID::v5(UUID::DNS, 'hello'), 19, 1), [8, 9, 'a', 'b']));

		$this->assertEquals(UUID::v5(UUID::DNS, 'hello'), UUID::v5(UUID::DNS, 'hello'));

		$this->assertNotEquals(UUID::v5(UUID::DNS, 'hello'), UUID::v5(UUID::URL, 'hello'));

		$this->assertNotEquals(UUID::v5(UUID::DNS, 'hello'), UUID::v5(UUID::DNS, 'goodbye'));

		$this->assertTrue(UUID::validate(UUID::v5(UUID::DNS, 'hello')));
	}

	/**
	 *
	 */
	public function testV5WithInvalidNamespace(): void
	{
		$this->expectException(InvalidArgumentException::class);

		UUID::v5('nope', 'foobar');
	}
}
