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
		/** @var \mako\security\crypto\encrypters\EncrypterInterface|\Mockery\MockInterface $encrypter */
		$encrypter = Mockery::mock(EncrypterInterface::class);

		$encrypter->shouldReceive('encrypt')->once()->with('foobar')->andReturn('barfoo');

		/** @var \mako\security\Signer|\Mockery\MockInterface $signer */
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
		/** @var \mako\security\Signer|\Mockery\MockInterface $signer */
		$signer = Mockery::mock(Signer::class);

		$signer->shouldReceive('validate')->once()->with('signedbarfoo')->andReturn('barfoo');

		/** @var \mako\security\crypto\encrypters\EncrypterInterface|\Mockery\MockInterface $encrypter */
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

		/** @var \mako\security\Signer|\Mockery\MockInterface $signer */
		$signer = Mockery::mock(Signer::class);

		$signer->shouldReceive('validate')->once()->with('signedbarfoo')->andReturn(false);

		/** @var \mako\security\crypto\encrypters\EncrypterInterface|\Mockery\MockInterface $encrypter */
		$encrypter = Mockery::mock(EncrypterInterface::class);

		$crypto = new Crypto($encrypter, $signer);

		$crypto->decrypt('signedbarfoo');
	}
}
