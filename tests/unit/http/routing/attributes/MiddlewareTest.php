<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\http\routing\middleware;

use mako\http\routing\attributes\Middleware;
use mako\tests\TestCase;

/**
 * @group unit
 */
class MiddlewareTest extends TestCase
{
	/**
	 *{@inheritDoc}
	 */
	public function setup(): void
	{
		if(PHP_VERSION_ID < 80000)
		{
			$this->markTestSkipped('This feature requires PHP 8.0+');
		}
	}

	/**
	 *
	 */
	public function testWithArray(): void
	{
		$middleware = new Middleware(['foobar', 'barfoo']);

		$this->assertSame(['foobar', 'barfoo'], $middleware->getMiddleware());
	}

	/**
	 *
	 */
	public function testWithString(): void
	{
		$middleware = new Middleware('foobar');

		$this->assertSame(['foobar'], $middleware->getMiddleware());
	}
}
