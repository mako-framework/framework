<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\http\routing;

use mako\http\routing\exceptions\RoutingException;
use mako\http\routing\Route;
use mako\http\routing\Routes;
use mako\tests\TestCase;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class RoutesTest extends TestCase
{
	/**
	 *
	 */
	public function testRouteRegistration1(): void
	{
		$routes = new Routes;

		$routes->get('/foo', fn () => 'Hello, world!');

		$this->assertCount(1, $routes->getRoutes());

		$routes->get('/bar', fn () => 'Hello, world!');

		$this->assertCount(2, $routes->getRoutes());
	}

	/**
	 *
	 */
	public function testRouteRegistration2(): void
	{
		$routes = new Routes;

		$routes->get('/foo', fn () => 'Hello, world!');

		$routes->post('/foo', fn () => 'Hello, world!');

		$routes->put('/foo', fn () => 'Hello, world!');

		$routes->patch('/foo', fn () => 'Hello, world!');

		$routes->delete('/foo', fn () => 'Hello, world!');

		$routes->all('/foo', fn () => 'Hello, world!');

		$routes->register(['OPTIONS', 'HEAD'], '/foo', fn () => 'Hello, world!');

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
		$routes = new Routes;

		$routes->get('/foo', fn () => 'Hello, world!', 'foo');

		$this->assertTrue($routes->hasNamedRoute('foo'));

		$this->assertFalse($routes->hasNamedRoute('bar'));

		$this->assertInstanceOf(Route::class, $routes->getNamedRoute('foo'));
	}

	/**
	 *
	 */
	public function testNamedRouteException(): void
	{
		$this->expectException(RoutingException::class);

		$routes = new Routes;

		$routes->getNamedRoute('foo');
	}

	/**
	 *
	 */
	public function testGroup(): void
	{
		$routes = new Routes;

		$routes->group(['prefix' => 'baz'], function ($routes): void {
			$routes->get('/foo', fn () => 'Hello, world!');

			$routes->get('/bar', fn () => 'Hello, world!');
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
		$routes = new Routes;

		$routes->group(['prefix' => 'baz'], function ($routes): void {
			$routes->group(['prefix' => 'bax'], function ($routes): void {
				$routes->get('/foo', fn () => 'Hello, world!');

				$routes->get('/bar', fn () => 'Hello, world!');
			});
		});

		$routes = $routes->getRoutes();

		$this->assertEquals('/baz/bax/foo', $routes[0]->getRoute());

		$this->assertEquals('/baz/bax/bar', $routes[1]->getRoute());
	}

	/**
	 *
	 */
	public function testGroupMiddlewareWithParameters(): void
	{
		$routes = new Routes;

		$routes->group(['middleware' => ['foo' => ['bar' => 123]]], function ($routes): void {
			$routes->get('/foo', fn () => 'Hello, world!');
		});

		$routes = $routes->getRoutes();

		$this->assertCount(1, $routes[0]->getMiddleware());

		$this->assertSame('foo', $routes[0]->getMiddleware()[0]['middleware']);
		$this->assertSame(['bar' => 123], $routes[0]->getMiddleware()[0]['parameters']);
	}

	/**
	 *
	 */
	public function testGroupMiddlewareWithoutParameters(): void
	{
		$routes = new Routes;

		$routes->group(['middleware' => ['foo']], function ($routes): void {
			$routes->get('/foo', fn () => 'Hello, world!');
		});

		$routes = $routes->getRoutes();

		$this->assertCount(1, $routes[0]->getMiddleware());

		$this->assertSame('foo', $routes[0]->getMiddleware()[0]['middleware']);
		$this->assertSame([], $routes[0]->getMiddleware()[0]['parameters']);
	}

	/**
	 *
	 */
	public function testGroupConstraintWithParameters(): void
	{
		$routes = new Routes;

		$routes->group(['constraint' => ['foo' => ['bar' => 123]]], function ($routes): void {
			$routes->get('/foo', fn () => 'Hello, world!');
		});

		$routes = $routes->getRoutes();

		$this->assertCount(1, $routes[0]->getConstraints());

		$this->assertSame('foo', $routes[0]->getConstraints()[0]['constraint']);
		$this->assertSame(['bar' => 123], $routes[0]->getConstraints()[0]['parameters']);
	}

	/**
	 *
	 */
	public function testGroupConstraintWithoutParameters(): void
	{
		$routes = new Routes;

		$routes->group(['constraint' => ['foo']], function ($routes): void {
			$routes->get('/foo', fn () => 'Hello, world!');
		});

		$routes = $routes->getRoutes();

		$this->assertCount(1, $routes[0]->getConstraints());

		$this->assertSame('foo', $routes[0]->getConstraints()[0]['constraint']);
		$this->assertSame([], $routes[0]->getConstraints()[0]['parameters']);
	}
}
