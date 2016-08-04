<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\tests\unit\security\crypto;

use PHPUnit_Framework_TestCase;

use mako\security\crypto\Key;

/**
 * @group unit
 */
class KeyTest extends PHPUnit_Framework_TestCase
{
	/**
	 *
	 */
	public function testGenerate()
	{
		$key = Key::generate(16);

		$this->assertEquals(16, mb_strlen($key, '8bit'));

		$key = Key::generate(32);

		$this->assertEquals(32, mb_strlen($key, '8bit'));
	}

	/**
	 *
	 */
	public function testEncodeAndDecode()
	{
		$key = Key::generate(16);

		$this->assertEquals(16, mb_strlen($key, '8bit'));

		$encoded = Key::encode($key);

		$this->assertEquals(36, mb_strlen($encoded, '8bit')); // encoded key = 32 byte and prefix = 4 byte

		$this->assertEquals('hex:', mb_substr($encoded, 0, 4, '8bit'));

		$this->assertEquals($key, Key::decode($encoded));
	}

	/**
	 *
	 */
	public function testGenerateEncoded()
	{
		$key = Key::generateEncoded(16);

		$this->assertEquals(36, mb_strlen($key, '8bit')); // encoded key = 32 byte and prefix = 4 byte

		$key = Key::generateEncoded(32);

		$this->assertEquals(68, mb_strlen($key, '8bit')); // encoded key = 64 byte and prefix = 4 byte
	}
}