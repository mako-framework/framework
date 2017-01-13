<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
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

	/**
	 *
	 */
	public function testOrderByPriorityWithNoPriority()
	{
		$middleware = new Middleware;

		$resolved =
		[
			'bar' => ['name' => 'bar', 'middleware' => 'middleware', 'parameters' => [1, 2, 3]],
			'foo' => ['name' => 'foo', 'middleware' => 'middleware', 'parameters' => [4, 5, 6]],
			'bax' => ['name' => 'bax', 'middleware' => 'middleware', 'parameters' => [7, 8, 9]],
			'baz' => ['name' => 'baz', 'middleware' => 'middleware', 'parameters' => [0, 1, 2]],
		];

		$this->assertSame($resolved, $middleware->orderByPriority($resolved));
	}

	/**
	 *
	 */
	public function testOrderByPriorityWithPriority()
	{
		$middleware = new Middleware;

		$middleware->setPriority(['foo', 'baz', 'xxx']);

		$resolved =
		[
			'bar' => ['name' => 'bar', 'middleware' => 'middleware', 'parameters' => [1, 2, 3]],
			'foo' => ['name' => 'foo', 'middleware' => 'middleware', 'parameters' => [4, 5, 6]],
			'bax' => ['name' => 'bax', 'middleware' => 'middleware', 'parameters' => [7, 8, 9]],
			'baz' => ['name' => 'baz', 'middleware' => 'middleware', 'parameters' => [0, 1, 2]],
		];

		$ordered =
		[
			'foo' => ['name' => 'foo', 'middleware' => 'middleware', 'parameters' => [4, 5, 6]],
			'baz' => ['name' => 'baz', 'middleware' => 'middleware', 'parameters' => [0, 1, 2]],
			'bar' => ['name' => 'bar', 'middleware' => 'middleware', 'parameters' => [1, 2, 3]],
			'bax' => ['name' => 'bax', 'middleware' => 'middleware', 'parameters' => [7, 8, 9]],
		];

		$this->assertSame($ordered, $middleware->orderByPriority($resolved));
	}

	/**
	 *
	 */
	public function testOrderByPriorityWithPriorityAndNoMiddleware()
	{
		$middleware = new Middleware;

		$middleware->setPriority(['foo', 'baz']);

		$this->assertSame([], $middleware->orderByPriority([]));
	}
}
