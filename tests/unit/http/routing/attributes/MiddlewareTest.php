<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\http\routing\attributes;

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
	public function testGetMiddleware(): void
	{
		$middleware = new Middleware(FooBar::class);

		$this->assertSame(FooBar::class, $middleware->getMiddleware());
	}

	/**
	 *
	 */
	public function testGetParameters(): void
	{
		$middleware = new Middleware(FooBar::class, foo: 'bar');

		$this->assertSame(['foo' => 'bar'], $middleware->getParameters());
	}

	/**
	 *
	 */
	public function testGetMiddlewareAndParameters(): void
	{
		$middleware = new Middleware(FooBar::class, foo: 'bar');

		$this->assertSame(['middleware' => FooBar::class, 'parameters' => ['foo' => 'bar']], $middleware->getMiddlewareAndParameters());
	}
}
