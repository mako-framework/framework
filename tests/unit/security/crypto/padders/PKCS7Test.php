<?php

namespace mako\tests\unit\security\crypto\padders;

use mako\security\crypto\padders\PKCS7;

/**
 * @group unit
 */

class PKCS7Test extends \PHPUnit_Framework_TestCase
{
	/**
	 *
	 */

	public function testAddPadding()
	{
		$string = 'hello';

		$padder = new PKCS7;

		//

		$padd64 = $padder->addPadding($string, 64);

		$this->assertEquals(64, strlen($padd64));

		$this->assertEquals(64 - strlen($string), ord(substr($padd64, -1)));

		//

		$padd128 = $padder->addPadding($string, 128);

		$this->assertEquals(128, strlen($padd128));

		$this->assertEquals(128 - strlen($string), ord(substr($padd128, -1)));
	}

	/**
	 *
	 */

	public function testRemovePadding()
	{
		$string = 'hello';

		$padder = new PKCS7;

		$padd64 = $padder->addPadding($string, 64);

		$padd128 = $padder->addPadding($string, 128);

		$this->assertEquals($string, $padder->stripPadding($padd64));

		$this->assertEquals($string, $padder->stripPadding($padd128));

		$this->assertFalse($padder->stripPadding($string . str_repeat('x', 64 - strlen($string))));
	}
}