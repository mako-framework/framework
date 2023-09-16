<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\http\routing;

use mako\http\routing\attributes\Constraint;
use mako\http\routing\attributes\Middleware;
use mako\http\routing\Route;
use mako\tests\TestCase;

// --------------------------------------------------------------------------
// START CLASSES
// --------------------------------------------------------------------------

#[Middleware('foo1')]
#[Middleware('foo2')]
#[Constraint('bar1')]
#[Constraint('bar2')]
class AttributeController
{
	#[Middleware('foo3')]
	#[Middleware('foo4')]
	#[Constraint('bar3')]
	#[Constraint('bar4')]
	public function method(): void
	{

	}

	#[Middleware('foo3')]
	#[Middleware('foo4')]
	#[Constraint('bar3')]
	#[Constraint('bar4')]
	public function __invoke(): void
	{

	}
}

// --------------------------------------------------------------------------
// END CLASSES
// --------------------------------------------------------------------------

/**
 * @group unit
 */
class RouteTest extends TestCase
{
	/**
	 *
	 */
	public function testBasicMatch(): void
	{
		$route = new Route(['GET'], '/', fn () => 'Hello, world!');

		$this->assertSame(1, preg_match($route->getRegex(), '/'));

		//

		$route = new Route(['GET'], '/foo', fn () => 'Hello, world!');

		$this->assertSame(1, preg_match($route->getRegex(), '/foo'));

		$this->assertSame(0, preg_match($route->getRegex(), '/foo/'));

		//

		$route = new Route(['GET'], '/foo/', fn () => 'Hello, world!');

		$this->assertSame(1, preg_match($route->getRegex(), '/foo'));

		$this->assertSame(1, preg_match($route->getRegex(), '/foo/'));
	}

	/**
	 *
	 */
	public function testMatchWithParameter(): void
	{
		$route = new Route(['GET'], '/foo/{id}', fn () => 'Hello, world!');

		$this->assertSame(1, preg_match($route->getRegex(), '/foo/123'));

		$this->assertSame(0, preg_match($route->getRegex(), '/foo/'));

		$this->assertSame(0, preg_match($route->getRegex(), '/foo/123/'));

		//

		$route = new Route(['GET'], '/foo/{id}/', fn () => 'Hello, world!');

		$this->assertSame(1, preg_match($route->getRegex(), '/foo/123/'));

		$this->assertSame(0, preg_match($route->getRegex(), '/foo/'));

		$this->assertSame(1, preg_match($route->getRegex(), '/foo/123/'));
	}

	/**
	 *
	 */
	public function testMatchWithParameters(): void
	{
		$route = new Route(['GET'], '/foo/{id}/{slug}', fn () => 'Hello, world!');

		$this->assertSame(1, preg_match($route->getRegex(), '/foo/123/foo-bar'));

		$this->assertSame(0, preg_match($route->getRegex(), '/foo/123'));

		$this->assertSame(0, preg_match($route->getRegex(), '/foo/123/foo-bar/'));

		//

		$route = new Route(['GET'], '/foo/{id}/{slug}/', fn () => 'Hello, world!');

		$this->assertSame(1, preg_match($route->getRegex(), '/foo/123/foo-bar/'));

		$this->assertSame(0, preg_match($route->getRegex(), '/foo/123/'));

		$this->assertSame(1, preg_match($route->getRegex(), '/foo/123/foo-bar'));
	}

	/**
	 *
	 */
	public function testMatchWithOptionalParameters(): void
	{
		$route = new Route(['GET'], '/foo/{id}/{slug}?', fn () => 'Hello, world!');

		$this->assertSame(1, preg_match($route->getRegex(), '/foo/123/foo-bar'));

		$this->assertSame(1, preg_match($route->getRegex(), '/foo/123'));

		$this->assertSame(0, preg_match($route->getRegex(), '/foo/123/'));

		//

		$route = new Route(['GET'], '/foo/{id}/{slug}?/', fn () => 'Hello, world!');

		$this->assertSame(1, preg_match($route->getRegex(), '/foo/123/foo-bar/'));

		$this->assertSame(1, preg_match($route->getRegex(), '/foo/123/'));

		$this->assertSame(1, preg_match($route->getRegex(), '/foo/123'));
	}

	/**
	 *
	 */
	public function testMatchWithParameterConstraints(): void
	{
		$route = (new Route(['GET'], '/foo/{id}', fn () => 'Hello, world!'))->patterns(['id' => '[0-9]+']);

		$this->assertSame(1, preg_match($route->getRegex(), '/foo/123'));

		$this->assertSame(0, preg_match($route->getRegex(), '/foo/123/'));

		$this->assertSame(0, preg_match($route->getRegex(), '/foo/abc'));

		//

		$route = (new Route(['GET'], '/foo/{id}/', fn () => 'Hello, world!'))->patterns(['id' => '[0-9]+']);

		$this->assertSame(1, preg_match($route->getRegex(), '/foo/123'));

		$this->assertSame(1, preg_match($route->getRegex(), '/foo/123/'));

		$this->assertSame(0, preg_match($route->getRegex(), '/foo/abc'));

		$this->assertSame(0, preg_match($route->getRegex(), '/foo/abc/'));
	}

	/**
	 *
	 */
	public function testHasTrailingSlash(): void
	{
		$route = new Route(['GET'], '/foo', fn () => 'Hello, world!');

		$this->assertFalse($route->hasTrailingSlash());

		//

		$route = new Route(['GET'], '/foo/', fn () => 'Hello, world!');

		$this->assertTrue($route->hasTrailingSlash());
	}

	/**
	 *
	 */
	public function testAllows(): void
	{
		$route = new Route(['GET'], '/foo', fn () => 'Hello, world!');

		$this->assertTrue($route->allowsMethod('GET'));

		$this->assertFalse($route->allowsMethod('POST'));

		//

		$route = new Route(['GET', 'POST'], '/foo', fn () => 'Hello, world!');

		$this->assertTrue($route->allowsMethod('GET'));

		$this->assertTrue($route->allowsMethod('POST'));
	}

	/**
	 *
	 */
	public function testGetMethods(): void
	{
		$route = new Route(['GET'], '/foo', fn () => 'Hello, world!');

		$this->assertEquals(['GET'], $route->getMethods());

		//

		$route = new Route(['GET', 'POST'], '/foo', fn () => 'Hello, world!');

		$this->assertEquals(['GET', 'POST'], $route->getMethods());
	}

	/**
	 *
	 */
	public function testGetRoute(): void
	{
		$route = new Route(['GET'], '/foo', fn () => 'Hello, world!');

		$this->assertEquals('/foo', $route->getRoute());
	}

	/**
	 *
	 */
	public function testGetAction(): void
	{
		$route = new Route(['GET'], '/foo', 'FooController');

		$this->assertEquals('FooController', $route->getAction());
	}

	/**
	 *
	 */
	public function testGetName(): void
	{
		$route = new Route(['GET'], '/foo', fn () => 'Hello, world!', 'foo');

		$this->assertEquals('foo', $route->getName());
	}

	/**
	 *
	 */
	public function testPrefix(): void
	{
		$route = (new Route(['GET'], '/foo', fn () => 'Hello, world!'))->prefix('bar');

		$this->assertEquals('/bar/foo', $route->getRoute());

		//

		$route = (new Route(['GET'], '/foo', fn () => 'Hello, world!'))->prefix('/bar');

		$this->assertEquals('/bar/foo', $route->getRoute());

		//

		$route = (new Route(['GET'], '/foo', fn () => 'Hello, world!'))->prefix('bar')->prefix('baz');

		$this->assertEquals('/bar/baz/foo', $route->getRoute());
	}

	/**
	 *
	 */
	public function testGetRegex(): void
	{
		$route = new Route(['GET'], '/', fn () => 'Hello, world!');

		$this->assertSame('#^/?$#su', $route->getRegex());

		//

		$route = new Route(['GET'], '/foo', fn () => 'Hello, world!');

		$this->assertSame('#^/foo$#su', $route->getRegex());

		//

		$route = new Route(['GET'], '/foo/', fn () => 'Hello, world!');

		$this->assertSame('#^/foo/?$#su', $route->getRegex());

		//

		$route = new Route(['GET'], '/foo/bar', fn () => 'Hello, world!');

		$this->assertSame('#^/foo/bar$#su', $route->getRegex());

		//

		$route = new Route(['GET'], '/{id}', fn () => 'Hello, world!');

		$this->assertSame('#^/(?P<id>[^/]++)$#su', $route->getRegex());

		//

		$route = new Route(['GET'], '/foo/{id}', fn () => 'Hello, world!');

		$this->assertSame('#^/foo/(?P<id>[^/]++)$#su', $route->getRegex());

		//

		$route = new Route(['GET'], '/foo/{id}/bar', fn () => 'Hello, world!');

		$this->assertSame('#^/foo/(?P<id>[^/]++)/bar$#su', $route->getRegex());

		//

		$route = new Route(['GET'], '/foo/{id}/', fn () => 'Hello, world!');

		$this->assertSame('#^/foo/(?P<id>[^/]++)/?$#su', $route->getRegex());

		//

		$route = (new Route(['GET'], '/foo/{id}', fn () => 'Hello, world!'))->patterns(['id' => '[0-9]+']);

		$this->assertSame('#^/foo/(?P<id>[0-9]+)$#su', $route->getRegex());
	}

	/**
	 *
	 */
	public function testMiddleware(): void
	{
		$route = (new Route(['GET'], '/', fn () => 'Hello, world!'))->middleware('foo');

		$this->assertSame([['middleware' => 'foo', 'parameters' => []]], $route->getMiddleware());

		//

		$route = (new Route(['GET'], '/', fn () => 'Hello, world!'))->middleware('foo')->middleware('bar');

		$this->assertSame([['middleware' => 'foo', 'parameters' => []], ['middleware' => 'bar', 'parameters' => []]], $route->getMiddleware());

		//

		$route = (new Route(['GET'], '/', fn () => 'Hello, world!'))->middleware('foo', foo: 'bar')->middleware('bar', foo: 'bar');

		$this->assertSame([['middleware' => 'foo', 'parameters' => ['foo' => 'bar']], ['middleware' => 'bar', 'parameters' => ['foo' => 'bar']]], $route->getMiddleware());
	}

	/**
	 *
	 */
	public function testSetAndGetParameters(): void
	{
		$route = new Route(['GET'], '/', fn () => 'Hello, world!');

		$parameters = ['foo' => 'bar', 'baz' => 'bax'];

		$route->setParameters($parameters);

		$this->assertSame($route->getParameter('foo'), 'bar');

		$this->assertSame($route->getParameter('baz'), 'bax');

		$this->assertNull($route->getParameter('nope'));

		$this->assertTrue($route->getParameter('nope', true));

		$this->assertSame($parameters, $route->getParameters());
	}

	/**
	 *
	 */
	public function testAttributeMiddleware(): void
	{
		$expected = [
			['middleware' => 'foo0', 'parameters' => []],
			['middleware' => 'foo1', 'parameters' => []],
			['middleware' => 'foo2', 'parameters' => []],
			['middleware' => 'foo3', 'parameters' => []],
			['middleware' => 'foo4', 'parameters' => []],
		];

		//

		$route = new Route(['GET'], '/', [AttributeController::class, 'method']);

		$route->middleware('foo0');

		$this->assertSame($expected, $route->getMiddleware());

		//

		$route = new Route(['GET'], '/', AttributeController::class);

		$route->middleware('foo0');

		$this->assertSame($expected, $route->getMiddleware());
	}

	/**
	 *
	 */
	public function testAttributeConstraints(): void
	{
		$expected = [
			['constraint' => 'bar0', 'parameters' => []],
			['constraint' => 'bar1', 'parameters' => []],
			['constraint' => 'bar2', 'parameters' => []],
			['constraint' => 'bar3', 'parameters' => []],
			['constraint' => 'bar4', 'parameters' => []],
		];

		//

		$route = new Route(['GET'], '/', [AttributeController::class, 'method']);

		$route->constraint('bar0');

		$this->assertSame($expected, $route->getConstraints());

		//

		$route = new Route(['GET'], '/', AttributeController::class);

		$route->constraint('bar0');

		$this->assertSame($expected, $route->getConstraints());
	}
}
