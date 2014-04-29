<?php

use \mako\http\routing\Route;

class RouteTest extends PHPUnit_Framework_TestCase
{
	/**
	 * 
	 */

	public function testBasicMatch()
	{
		$route = new Route(['GET'], '/', 'FooController::fooAction');

		$this->assertTrue($route->isMatch('/'));

		//

		$route = new Route(['GET'], '/foo', 'FooController::fooAction');

		$this->assertTrue($route->isMatch('/foo'));

		$this->assertFalse($route->isMatch('/foo/'));

		//

		$route = new Route(['GET'], '/foo/', 'FooController::fooAction');

		$this->assertTrue($route->isMatch('/foo'));

		$this->assertTrue($route->isMatch('/foo/'));
	}

	/**
	 * 
	 */

	public function testMatchWithParameter()
	{
		$route = new Route(['GET'], '/foo/{id}', 'FooController::fooAction');

		$this->assertTrue($route->isMatch('/foo/123'));

		$this->assertFalse($route->isMatch('/foo/'));

		$this->assertFalse($route->isMatch('/foo/123/'));

		//

		$route = new Route(['GET'], '/foo/{id}/', 'FooController::fooAction');

		$this->assertTrue($route->isMatch('/foo/123/'));

		$this->assertFalse($route->isMatch('/foo/'));

		$this->assertTrue($route->isMatch('/foo/123/'));
	}

	/**
	 * 
	 */

	public function testMatchWithParameters()
	{
		$route = new Route(['GET'], '/foo/{id}/{slug}', 'FooController::fooAction');

		$this->assertTrue($route->isMatch('/foo/123/foo-bar'));

		$this->assertFalse($route->isMatch('/foo/123'));

		$this->assertFalse($route->isMatch('/foo/123/foo-bar/'));

		//

		$route = new Route(['GET'], '/foo/{id}/{slug}/', 'FooController::fooAction');

		$this->assertTrue($route->isMatch('/foo/123/foo-bar/'));

		$this->assertFalse($route->isMatch('/foo/123/'));

		$this->assertTrue($route->isMatch('/foo/123/foo-bar'));
	}

	/**
	 * 
	 */

	public function testMatchWithOptionalParameters()
	{
		$route = new Route(['GET'], '/foo/{id}/{slug}?', 'FooController::fooAction');

		$this->assertTrue($route->isMatch('/foo/123/foo-bar'));

		$this->assertTrue($route->isMatch('/foo/123'));

		$this->assertFalse($route->isMatch('/foo/123/'));

		//

		$route = new Route(['GET'], '/foo/{id}/{slug}?/', 'FooController::fooAction');

		$this->assertTrue($route->isMatch('/foo/123/foo-bar/'));

		$this->assertTrue($route->isMatch('/foo/123/'));

		$this->assertTrue($route->isMatch('/foo/123'));
	}

	/**
	 * 
	 */

	public function testMatchWithParameterConstraints()
	{
		$route = (new Route(['GET'], '/foo/{id}', 'FooController::fooAction'))->constraints(['id' => '[0-9]+']);

		$this->assertTrue($route->isMatch('/foo/123'));

		$this->assertFalse($route->isMatch('/foo/123/'));

		$this->assertFalse($route->isMatch('/foo/abc'));

		//

		$route = (new Route(['GET'], '/foo/{id}/', 'FooController::fooAction'))->constraints(['id' => '[0-9]+']);

		$this->assertTrue($route->isMatch('/foo/123'));

		$this->assertTrue($route->isMatch('/foo/123/'));

		$this->assertFalse($route->isMatch('/foo/abc'));

		$this->assertFalse($route->isMatch('/foo/abc/'));
	}

	/**
	 * 
	 */

	public function testHasTrailingSlash()
	{
		$route = new Route(['GET'], '/foo', 'FooController::fooAction');

		$this->assertFalse($route->hasTrailingSlash());

		//

		$route = new Route(['GET'], '/foo/', 'FooController::fooAction');

		$this->assertTrue($route->hasTrailingSlash());
	}

	/**
	 * 
	 */

	public function testAllows()
	{
		$route = new Route(['GET'], '/foo', 'FooController::fooAction');

		$this->assertTrue($route->allows('GET'));

		$this->assertFalse($route->allows('POST'));

		//

		$route = new Route(['GET', 'POST'], '/foo', 'FooController::fooAction');

		$this->assertTrue($route->allows('GET'));

		$this->assertTrue($route->allows('POST'));
	}

	/**
	 * 
	 */

	public function testGetMethods()
	{
		$route = new Route(['GET'], '/foo', 'FooController::fooAction');

		$this->assertEquals(['GET'], $route->getMethods());

		//

		$route = new Route(['GET', 'POST'], '/foo', 'FooController::fooAction');

		$this->assertEquals(['GET', 'POST'], $route->getMethods());
	}

	/**
	 * 
	 */

	public function testGetRoute()
	{
		$route = new Route(['GET'], '/foo', 'FooController::fooAction');

		$this->assertEquals('/foo', $route->getRoute());
	}

	/**
	 * 
	 */

	public function testGetAction()
	{
		$route = new Route(['GET'], '/foo', 'FooController::fooAction');

		$this->assertEquals('FooController::fooAction', $route->getAction());
	}

	/**
	 * 
	 */

	public function testGetName()
	{
		$route = new Route(['GET'], '/foo', 'FooController::fooAction', 'foo');

		$this->assertEquals('foo', $route->getName());
	}

	/**
	 * 
	 */

	public function testBeforeFilters()
	{
		$route = (new Route(['GET'], '/foo', 'FooController::fooAction'))->before('foo');

		$this->assertEquals(['foo'], $route->getBeforeFilters());

		//

		$route = (new Route(['GET'], '/foo', 'FooController::fooAction'))->before('foo')->before('bar');

		$this->assertEquals(['foo', 'bar'], $route->getBeforeFilters());

		//

		$route = (new Route(['GET'], '/foo', 'FooController::fooAction'))->before(['foo', 'bar']);

		$this->assertEquals(['foo', 'bar'], $route->getBeforeFilters());
	}

	/**
	 * 
	 */

	public function testAfterFilters()
	{
		$route = (new Route(['GET'], '/foo', 'FooController::fooAction'))->after('foo');

		$this->assertEquals(['foo'], $route->getAfterFilters());

		//

		$route = (new Route(['GET'], '/foo', 'FooController::fooAction'))->after('foo')->after('bar');

		$this->assertEquals(['foo', 'bar'], $route->getAfterFilters());

		//

		$route = (new Route(['GET'], '/foo', 'FooController::fooAction'))->after(['foo', 'bar']);

		$this->assertEquals(['foo', 'bar'], $route->getAfterFilters());
	}

	/**
	 * 
	 */

	public function testHeaders()
	{
		$headers = ['foo' => 'bar', 'bar' => 'foo'];

		$route = (new Route(['GET'], '/foo', 'FooController::fooAction'))->headers($headers);

		$this->assertEquals($headers, $route->getHeaders());
	}

	/**
	 * 
	 */

	public function testPrefix()
	{
		$route = (new Route(['GET'], '/foo', 'FooController::fooAction'))->prefix('bar');

		$this->assertEquals('/bar/foo', $route->getRoute());

		//

		$route = (new Route(['GET'], '/foo', 'FooController::fooAction'))->prefix('/bar');

		$this->assertEquals('/bar/foo', $route->getRoute());

		//

		$route = (new Route(['GET'], '/foo', 'FooController::fooAction'))->prefix('bar')->prefix('baz');

		$this->assertEquals('/bar/baz/foo', $route->getRoute());
	}

	/**
	 * 
	 */

	public function testParameters()
	{
		$route = new Route(['GET'], '/foo/{id}', 'FooController::fooAction');

		$route->isMatch('/foo/123');

		$this->assertEquals(['id' => '123'], $route->getParameters());

		$this->assertEquals('123', $route->param('id'));

		$this->assertEquals(null, $route->param('slug'));

		$this->assertEquals('baz', $route->param('slug', 'baz'));
	}
}