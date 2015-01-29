<?php

namespace mako\tests\unit\security;

use mako\security\Signer;

/**
 * @group unit
 */

class SignerTest extends \PHPUnit_Framework_TestCase
{
	/**
	 *
	 */

	public function testSign()
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

	public function testValidateValid()
	{
		$string = 'hello, world!';

		$signer = new Signer('foobar');

		$signed = $signer->sign($string);

		$this->assertEquals($string, $signer->validate($signed));
	}

	/**
	 *
	 */

	public function testValidateInvalid()
	{
		$string = 'hello, world!';

		$signer = new Signer('foobar');

		$this->assertFalse($signer->validate(str_repeat('0', 64) . $string));
	}
}