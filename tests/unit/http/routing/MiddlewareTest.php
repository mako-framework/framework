<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\tests\unit\http\routing;

use PHPUnit_Framework_TestCase;

use mako\http\routing\Middleware;

/**
 * @group unit
 */
class MiddlewareTest extends PHPUnit_Framework_TestCase
{
	/**
	 *
	 */
	public function testRegister()
	{
		$middleware = new Middleware;

		$middleware->register('bar', 'MyMiddleware');
	}

	/**
	 *
	 */
	public function testGet()
	{
		$middleware = new Middleware;

		$middleware->register('foo', 'MyMiddleware');

		$this->assertSame('MyMiddleware', $middleware->get('foo'));
	}

	/**
	 * @expectedException \RuntimeException
	 */
	public function testGetNonExisting()
	{
		$middleware = new Middleware;

		$middleware->get('foo');
	}
}
