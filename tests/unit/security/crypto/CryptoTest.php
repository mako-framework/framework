<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\security\crypto;

use mako\security\crypto\Crypto;
use mako\security\crypto\encrypters\EncrypterInterface;
use mako\security\crypto\exceptions\CryptoException;
use mako\security\Signer;
use mako\tests\TestCase;
use Mockery;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class CryptoTest extends TestCase
{
	/**
	 *
	 */
	public function testEncrypt(): void
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
	public function testDecrypt(): void
	{
		$signer = Mockery::mock(Signer::class);

		$signer->shouldReceive('validate')->once()->with('signedbarfoo')->andReturn('barfoo');

		$encrypter = Mockery::mock(EncrypterInterface::class);

		$encrypter->shouldReceive('decrypt')->once()->with('barfoo')->andReturn('foobar');

		$crypto = new Crypto($encrypter, $signer);

		$this->assertEquals('foobar', $crypto->decrypt('signedbarfoo'));
	}

	/**
	 *
	 */
	public function testDecryptModifiedCiphertext(): void
	{
		$this->expectException(CryptoException::class);

		$this->expectExceptionMessage('Ciphertex has been modified or an invalid authentication key has been provided.');

		$signer = Mockery::mock(Signer::class);

		$signer->shouldReceive('validate')->once()->with('signedbarfoo')->andReturn(false);

		$encrypter = Mockery::mock(EncrypterInterface::class);

		$crypto = new Crypto($encrypter, $signer);

		$crypto->decrypt('signedbarfoo');
	}
}
