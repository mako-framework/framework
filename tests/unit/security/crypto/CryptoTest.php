<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\security\crypto;

use Mockery;
use PHPUnit_Framework_TestCase;

use mako\security\crypto\Crypto;
use mako\security\crypto\encrypters\EncrypterInterface;
use mako\security\Signer;

/**
 * @group unit
 */
class CryptoTest extends PHPUnit_Framework_TestCase
{
	/**
	 *
	 */
	public function tearDown()
	{
		Mockery::close();
	}

	/**
	 *
	 */
	public function testEncrypt()
	{
		$encrypter = Mockery::mock(EncrypterInterface::class);

		$encrypter->shouldReceive('encrypt')->once()->with('foobar')->andReturn('barfoo');

		$signer = Mockery::mock(Signer::class);

		$signer->shouldReceive('sign')->once()->with('barfoo')->andReturn('signedbarfoo');

		$crypto = new Crypto($encrypter, $signer);

		$this->assertEquals('signedbarfoo', $crypto->encrypt('foobar'));
	}

	/**
	 *
	 */
	public function testDecrypt()
	{
		$signer = Mockery::mock(Signer::class);

		$signer->shouldReceive('validate')->once()->with('signedbarfoo')->andReturn('barfoo');

		$encrypter = Mockery::mock(EncrypterInterface::class);

		$encrypter->shouldReceive('decrypt')->once()->with('barfoo')->andReturn('foobar');

		$crypto = new Crypto($encrypter, $signer);

		$this->assertEquals('foobar', $crypto->decrypt('signedbarfoo'));
	}

	/**
	 * @expectedException \mako\security\crypto\CryptoException
	 * @expectedExceptionMessage Ciphertex has been modified or an invalid authentication key has been provided.
	 */
	public function testDecryptModifiedCiphertext()
	{
		$signer = Mockery::mock(Signer::class);

		$signer->shouldReceive('validate')->once()->with('signedbarfoo')->andReturn(false);

		$encrypter = Mockery::mock(EncrypterInterface::class);

		$crypto = new Crypto($encrypter, $signer);

		$crypto->decrypt('signedbarfoo');
	}
}
