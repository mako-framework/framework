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
		$resolved =
		[
			'bar' => ['one'],
			'foo' => ['two'],
			'bax' => ['three'],
			'baz' => ['four'],
		];

		$middleware = new Middleware;

		$this->assertSame($resolved, $middleware->orderByPriority($resolved));
	}

	/**
	 *
	 */
	public function testOrderByPriorityWithPriority()
	{
		$resolved =
		[
			'bar' => ['three'],
			'foo' => ['one'],
			'bax' => ['four'],
			'yyy' => ['five'],
			'baz' => ['two'],
		];

		$middleware = new Middleware;

		// Test middleare priority

		$middleware->setPriority(['foo' => 1, 'baz' => 2, 'xxx' => 3, 'yyy' => 101]);

		$ordered =
		[
			'foo' => ['one'],
			'baz' => ['two'],
			'bar' => ['three'],
			'bax' => ['four'],
			'yyy' => ['five'],
		];

		$this->assertSame($ordered, $middleware->orderByPriority($resolved));

		// Test middleware priority override

		$middleware->setPriority(['foo' => 1, 'baz' => 2, 'xxx' => 3, 'yyy' => 1]);

		$ordered =
		[
			'foo' => ['one'],
			'yyy' => ['five'],
			'baz' => ['two'],
			'bar' => ['three'],
			'bax' => ['four'],
		];

		$this->assertSame($ordered, $middleware->orderByPriority($resolved));

		// Test middeware priority reset

		$middleware->resetPriority();

		$this->assertSame($resolved, $middleware->orderByPriority($resolved));
	}

	/**
	 *
	 */
	public function testOrderByPriorityWithPriorityAndNoMiddleware()
	{
		$middleware = new Middleware;

		$middleware->setPriority(['foo' => 1, 'baz' => 2]);

		$this->assertSame([], $middleware->orderByPriority([]));
	}
}
