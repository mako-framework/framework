<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\security;

use mako\security\Signer;
use mako\tests\TestCase;

/**
 * @group unit
 */
class SignerTest extends TestCase
{
	/**
	 *
	 */
	public function testSign(): void
	{
		$string = 'hello, world!';

		$signer = new Signer('foobar');

		$signed = $signer->sign($string);

		$this->assertEquals(strlen($signed), strlen($string) + 64);

		$this->assertEquals($string, substr($signed, 64));
	}

	/**
	 *
	 */
	public function testValidateValid(): void
	{
		$string = 'hello, world!';

		$signer = new Signer('foobar');

		$signed = $signer->sign($string);

		$this->assertEquals($string, $signer->validate($signed));
	}

	/**
	 *
	 */
	public function testValidateInvalid(): void
	{
		$string = 'hello, world!';

		$signer = new Signer('foobar');

		$this->assertFalse($signer->validate(str_repeat('0', 64) . $string));
	}
}
