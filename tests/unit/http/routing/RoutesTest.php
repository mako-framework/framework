<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\http\routing;

use mako\http\routing\Routes;
use mako\tests\TestCase;

/**
 * @group unit
 */
class RoutesTest extends TestCase
{
	/**
	 *
	 */
	public function testRouteRegistration1(): void
	{
		$routes = new Routes();

		$routes->get('/foo', 'FooController::fooAction');

		$this->assertCount(1, $routes->getRoutes());

		$routes->get('/bar', 'FooController::barAction');

		$this->assertCount(2, $routes->getRoutes());
	}

	/**
	 *
	 */
	public function testRouteRegistration2(): void
	{
		$routes = new Routes();

		$routes->get('/foo', 'FooController::fooAction');

		$routes->post('/foo', 'FooController::fooAction');

		$routes->put('/foo', 'FooController::fooAction');

		$routes->patch('/foo', 'FooController::fooAction');

		$routes->delete('/foo', 'FooController::fooAction');

		$routes->all('/foo', 'FooController::fooAction');

		$routes->register(['OPTIONS', 'HEAD'], '/foo', 'FooController::fooAction');

		$routes = $routes->getRoutes();

		$this->assertEquals(['GET', 'HEAD', 'OPTIONS'], $routes[0]->getMethods());

		$this->assertEquals(['POST', 'OPTIONS'], $routes[1]->getMethods());

		$this->assertEquals(['PUT', 'OPTIONS'], $routes[2]->getMethods());

		$this->assertEquals(['PATCH', 'OPTIONS'], $routes[3]->getMethods());

		$this->assertEquals(['DELETE', 'OPTIONS'], $routes[4]->getMethods());

		$this->assertEquals(['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'], $routes[5]->getMethods());

		$this->assertEquals(['OPTIONS', 'HEAD'], $routes[6]->getMethods());
	}

	/**
	 *
	 */
	public function testNamedRoute(): void
	{
		$routes = new Routes();

		$routes->get('/foo', 'FooController::fooAction', 'foo');

		$this->assertTrue($routes->hasNamedRoute('foo'));

		$this->assertFalse($routes->hasNamedRoute('bar'));

		$this->assertInstanceOf('\mako\http\routing\Route', $routes->getNamedRoute('foo'));
	}

	/**
	 * @expectedException \RuntimeException
	 */
	public function testNamedRouteException(): void
	{
		$routes = new Routes();

		$routes->getNamedRoute('foo');
	}

	/**
	 *
	 */
	public function testGroup(): void
	{
		$routes = new Routes();

		$routes->group(['prefix' => 'baz'], function($routes): void
		{
			$routes->get('/foo', 'FooController::fooAction');

			$routes->get('/bar', 'FooController::barAction');
		});

		$routes = $routes->getRoutes();

		$this->assertEquals('/baz/foo', $routes[0]->getRoute());

		$this->assertEquals('/baz/bar', $routes[1]->getRoute());
	}

	/**
	 *
	 */
	public function testNestedGroup(): void
	{
		$routes = new Routes();

		$routes->group(['prefix' => 'baz'], function($routes): void
		{
			$routes->group(['prefix' => 'bax'], function($routes): void
			{
				$routes->get('/foo', 'FooController::fooAction');

				$routes->get('/bar', 'FooController::barAction');
			});
		});

		$routes = $routes->getRoutes();

		$this->assertEquals('/baz/bax/foo', $routes[0]->getRoute());

		$this->assertEquals('/baz/bax/bar', $routes[1]->getRoute());
	}
}
