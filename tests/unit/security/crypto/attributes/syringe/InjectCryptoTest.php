<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\security\crypto\attributes\syringe;

use mako\security\crypto\attributes\syringe\InjectCrypto;
use mako\security\crypto\Crypto;
use mako\security\crypto\CryptoManager;
use mako\syringe\Container;
use mako\tests\TestCase;
use Mockery;
use PHPUnit\Framework\Attributes\Group;
use ReflectionParameter;

#[Group('unit')]
class InjectCryptoTest extends TestCase
{
	/**
	 *
	 */
	public function testInjectCacheWithNull(): void
	{
		$crypto = Mockery::mock(Crypto::class);

		$cryptoManager = Mockery::mock(CryptoManager::class);

		$cryptoManager->shouldReceive('getInstance')->once()->with(null)->andReturn($crypto);

		$container = Mockery::mock(Container::class);

		$container->shouldReceive('get')->once()->with(CryptoManager::class)->andReturn($cryptoManager);

		$injector = new InjectCrypto(null);

		$reflection = Mockery::mock(ReflectionParameter::class);

		$this->assertInstanceOf(Crypto::class, $injector->getParameterValue($container, $reflection));
	}

	/**
	 *
	 */
	public function testInjectCacheWithName(): void
	{
		$crypto = Mockery::mock(Crypto::class);

		$cryptoManager = Mockery::mock(CryptoManager::class);

		$cryptoManager->shouldReceive('getInstance')->once()->with('foobar')->andReturn($crypto);

		$container = Mockery::mock(Container::class);

		$container->shouldReceive('get')->once()->with(CryptoManager::class)->andReturn($cryptoManager);

		$injector = new InjectCrypto('foobar');

		$reflection = Mockery::mock(ReflectionParameter::class);

		$this->assertInstanceOf(Crypto::class, $injector->getParameterValue($container, $reflection));
	}
}
