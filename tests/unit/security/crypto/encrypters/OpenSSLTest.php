<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\security\crypto\encrypters;

use mako\security\crypto\encrypters\OpenSSL;
use mako\tests\TestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;

#[Group('unit')]
#[RequiresPhpExtension('openssl')]
class OpenSSLTest extends TestCase
{
	/**
	 *
	 */
	public function testEncrypt(): void
	{
		$string = 'hello, world!';

		$openSSL = new OpenSSL('uE4cJ8YzUMev*aAuZBXezXqWr[sNwK', keyDerivationIterations: 1024);

		$encrypted1 = $openSSL->encrypt($string);

		$encrypted2 = $openSSL->encrypt($string);

		$this->assertNotEquals($string, $encrypted1);

		$this->assertNotEquals($string, $encrypted2);

		$this->assertNotEquals($encrypted1, $encrypted2);
	}

	/**
	 *
	 */
	public function testDecrypt(): void
	{
		$string = 'hello, world!';

		$openSSL = new OpenSSL('uE4cJ8YzUMev*aAuZBXezXqWr[sNwK', keyDerivationIterations: 1024);

		$encrypted = $openSSL->encrypt($string);

		$this->assertNotEquals($string, $encrypted);

		$decrypted = $openSSL->decrypt($encrypted);

		$this->assertEquals($string, $decrypted);
	}

	/**
	 *
	 */
	public function testDecryptWithInvalidBase64(): void
	{
		$openSSL = new OpenSSL('uE4cJ8YzUMev*aAuZBXezXqWr[sNwK', keyDerivationIterations: 1024);

		$this->assertFalse($openSSL->decrypt('<invalid>'));
	}
}
