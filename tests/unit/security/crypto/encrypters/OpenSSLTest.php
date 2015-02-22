<?php

namespace mako\tests\unit\security\crypto\encrypters;

use mako\security\crypto\encrypters\OpenSSL;

/**
 * @group unit
 * @requires extension openssl
 */

class OpenSSLTest extends \PHPUnit_Framework_TestCase
{
	/**
	 *
	 */

	public function testEncrypt()
	{
		$string = 'hello, world!';

		$openSSL = new OpenSSL('uE4cJ8YzUMev*aAuZBXezXqWr[sNwK');

		$encrypted1 = $openSSL->encrypt($string);

		$encrypted2 = $openSSL->encrypt($string);

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

		$openSSL = new OpenSSL('uE4cJ8YzUMev*aAuZBXezXqWr[sNwK');

		$encrypted = $openSSL->encrypt($string);

		$this->assertNotEquals($string, $encrypted);

		$decrypted = $openSSL->decrypt($encrypted);

		$this->assertEquals($string, $decrypted);
	}

	/**
	 *
	 */

	public function testDecryptWithInvalidBase64()
	{
		$openSSL = new OpenSSL('uE4cJ8YzUMev*aAuZBXezXqWr[sNwK');

		$this->assertFalse($openSSL->decrypt('<invalid>'));
	}
}