<?php

namespace mako\tests\unit\security\crypto\encrypters;

use mako\security\crypto\padders\PKCS7;
use mako\security\crypto\encrypters\MCrypt;

/**
 * @group unit
 * @requires extension mcrypt
 */

class MCryptTest extends \PHPUnit_Framework_TestCase
{
	/**
	 *
	 */

	public function testEncrypt()
	{
		$string = 'hello, world!';

		$mcrypt = new MCrypt('uE4cJ8YzUMev*aAuZBXezXqWr[sNwK', new PKCS7);

		$encrypted1 = $mcrypt->encrypt($string);

		$encrypted2 = $mcrypt->encrypt($string);

		$this->assertNotEquals($string, $encrypted1);

		$this->assertNotEquals($string, $encrypted2);

		$this->assertNotEquals($encrypted1, $encrypted2);
	}

	/**
	 *
	 */

	public function testDecrypt()
	{
		$string = 'hello, world!';

		$mcrypt = new MCrypt('uE4cJ8YzUMev*aAuZBXezXqWr[sNwK', new PKCS7);

		$encrypted = $mcrypt->encrypt($string);

		$this->assertNotEquals($string, $encrypted);

		$decrypted = $mcrypt->decrypt($encrypted);

		$this->assertEquals($string, $decrypted);
	}

	/**
	 *
	 */

	public function testDecryptWithInvalidBase64()
	{
		$mcrypt = new MCrypt('uE4cJ8YzUMev*aAuZBXezXqWr[sNwK', new PKCS7);

		$this->assertFalse($mcrypt->decrypt('<invalid>'));
	}
}