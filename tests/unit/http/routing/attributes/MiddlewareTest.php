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
