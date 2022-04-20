<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\gatekeeper;

use mako\gatekeeper\adapters\AdapterInterface;
use mako\gatekeeper\Gatekeeper;
use mako\tests\TestCase;
use Mockery;

/**
 * @group unit
 */
class GatekeeperTest extends TestCase
{
	/**
	 *
	 */
	public function testConstructorWithFactory(): void
	{
		$factory = function ()
		{
			/** @var \mako\gatekeeper\adapters\AdapterInterface|\Mockery\MockInterface $adapter */
			$adapter = Mockery::mock(AdapterInterface::class);

			$adapter->shouldReceive('hello')->once();

			return $adapter;
		};

		$gatekeeper = new Gatekeeper(['foobar', $factory]);

		$gatekeeper->hello();
	}

	/**
	 *
	 */
	public function testConstructorWithInstance(): void
	{
		/** @var \mako\gatekeeper\adapters\AdapterInterface|\Mockery\MockInterface $adapter */
		$adapter = Mockery::mock(AdapterInterface::class);

		$adapter->shouldReceive('getName')->once()->andReturn('foobar');

		$adapter->shouldReceive('hello')->once();

		$gatekeeper = new Gatekeeper($adapter);

		$gatekeeper->hello();
	}

	/**
	 *
	 */
	public function testExtendWithFactory(): void
	{
		/** @var \mako\gatekeeper\adapters\AdapterInterface|\Mockery\MockInterface $adapter */
		$adapter = Mockery::mock(AdapterInterface::class);

		$adapter->shouldReceive('getName')->once()->andReturn('foobar');

		$gatekeeper = new Gatekeeper($adapter);

		$factory = function ()
		{
			/** @var \mako\gatekeeper\adapters\AdapterInterface|\Mockery\MockInterface $adapter */
			$adapter = Mockery::mock(AdapterInterface::class);

			$adapter->shouldReceive('getName')->once()->andReturn('barfoo');

			return $adapter;
		};

		$gatekeeper->extend(['barfoo', $factory]);

		$this->assertSame('barfoo', $gatekeeper->adapter('barfoo')->getName());
	}

	/**
	 *
	 */
	public function testExtendWithInstance(): void
	{
		/** @var \mako\gatekeeper\adapters\AdapterInterface|\Mockery\MockInterface $adapter */
		$adapter = Mockery::mock(AdapterInterface::class);

		$adapter->shouldReceive('getName')->once()->andReturn('foobar');

		$gatekeeper = new Gatekeeper($adapter);

		/** @var \mako\gatekeeper\adapters\AdapterInterface|\Mockery\MockInterface $adapter */
		$adapter = Mockery::mock(AdapterInterface::class);

		$adapter->shouldReceive('getName')->twice()->andReturn('barfoo');

		$gatekeeper->extend($adapter);

		$this->assertSame('barfoo', $gatekeeper->adapter('barfoo')->getName());
	}

	/**
	 *
	 */
	public function testExtendWithInstanceAndNewDefault(): void
	{
		/** @var \mako\gatekeeper\adapters\AdapterInterface|\Mockery\MockInterface $adapter */
		$adapter = Mockery::mock(AdapterInterface::class);

		$adapter->shouldReceive('getName')->once()->andReturn('foobar');

		$gatekeeper = new Gatekeeper($adapter);

		/** @var \mako\gatekeeper\adapters\AdapterInterface|\Mockery\MockInterface $adapter */
		$adapter = Mockery::mock(AdapterInterface::class);

		$adapter->shouldReceive('getName')->twice()->andReturn('barfoo');

		$gatekeeper->extend($adapter)->useAsDefaultAdapter('barfoo');

		$this->assertSame('barfoo', $gatekeeper->getName());
	}
}
