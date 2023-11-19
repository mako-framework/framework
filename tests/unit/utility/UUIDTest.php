<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\utility;

use mako\tests\TestCase;
use mako\utility\exceptions\UUIDException;
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

		$this->assertTrue(UUID::validate('{6ba7b814-9dad-11d1-80b4-00c04fd430c8}'));

		$this->assertTrue(UUID::validate('urn:uuid:6ba7b814-9dad-11d1-80b4-00c04fd430c8'));

		$this->assertFalse(UUID::validate('6ba7b814-9dad-11d1-80b4-00c04fd430cx'));
	}

	/**
	 *
	 */
	public function testToBinary(): void
	{
		$this->assertEquals(16, strlen($uuid1 = UUID::toBinary('6ba7b814-9dad-11d1-80b4-00c04fd430c8')));

		$this->assertEquals(16, strlen($uuid2 = UUID::toBinary('{6ba7b814-9dad-11d1-80b4-00c04fd430c8}')));

		$this->assertEquals(16, strlen($uuid3 = UUID::toBinary('urn:uuid:6ba7b814-9dad-11d1-80b4-00c04fd430c8')));

		$this->assertSame($uuid1, $uuid2);

		$this->assertSame($uuid2, $uuid3);
	}

	/**
	 *
	 */
	public function testToBinaryWithInvalidInput(): void
	{
		$this->expectException(UUIDException::class);

		UUID::toBinary('nope');
	}

	/**
	 *
	 */
	public function testToHexadecimal(): void
	{
		$uuid = '6ba7b814-9dad-11d1-80b4-00c04fd430c8';

		$bin = UUID::toBinary('6ba7b814-9dad-11d1-80b4-00c04fd430c8');

		$hex = UUID::toHexadecimal($bin);

		$this->assertSame($uuid, $hex);
	}

	/**
	 *
	 */
	public function testToHexadecimalWithInvalidInput(): void
	{
		$this->expectException(UUIDException::class);

		UUID::toHexadecimal('nope');
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
		$this->expectException(UUIDException::class);

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
		$this->expectException(UUIDException::class);

		UUID::v5('nope', 'foobar');
	}

	/**
	 *
	 */
	public function testSequential(): void
	{
		$prev = UUID::sequential();

		for ($i = 0; $i < 100; $i++) {
			usleep(10); // We need to sleep since they cannot be guaranteed to be 100% sequential due to the time presision

			$uuid = UUID::sequential();

			$this->assertTrue(strcmp($prev, $uuid) <= 0);

			$prev = $uuid;
		}
	}
}
