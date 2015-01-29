<?php

namespace mako\tests\unit\http\routing;

use mako\http\routing\URLBuilder;

use \Mockery as m;

/**
 * @group unit
 */

class URLBuilderTest extends \PHPUnit_Framework_TestCase
{
	/**
	 *
	 */

	public function tearDown()
	{
		m::close();
	}

	/**
	 *
	 */

	public function getRequest($langPrefix = '')
	{
		$request = m::mock('\mako\http\Request');

		$request->shouldReceive('path')->andReturn('/foo/bar');

		$request->shouldReceive('baseURL')->andReturn('http://example.org');

		$request->shouldReceive('languagePrefix')->andReturn($langPrefix);

		return $request;
	}

	/**
	 *
	 */

	public function getRoutes()
	{
		$route1 = m::mock('\mako\http\routing\Route');

		$route1->shouldReceive('getRoute')->andReturn('/article/{id}/{slug}');

		$route2 = m::mock('\mako\http\routing\Route');

		$route2->shouldReceive('getRoute')->andReturn('/article/{id}/{slug}?');

		$routes = m::mock('\mako\http\routing\Routes');

		$routes->shouldReceive('getNamedRoute')->withArgs(['foo'])->andReturn($route1);

		$routes->shouldReceive('getNamedRoute')->withArgs(['bar'])->andReturn($route2);

		return $routes;
	}

	/**
	 *
	 */

	public function testMatches()
	{
		$urlBuilder = new URLBuilder($this->getRequest(), $this->getRoutes());

		$this->assertTrue($urlBuilder->matches('/foo/bar'));

		$this->assertFalse($urlBuilder->matches('/foo/baz'));

		$this->assertTrue($urlBuilder->matches('/foo/[a-z]+'));

		$this->assertFalse($urlBuilder->matches('/foo/[0-9]+'));
	}

	/**
	 *
	 */

	public function testBase()
	{
		$urlBuilder = new URLBuilder($this->getRequest(), $this->getRoutes());

		$this->assertEquals('http://example.org', $urlBuilder->base());
	}

	/**
	 *
	 */

	public function testTo()
	{
		$urlBuilder = new URLBuilder($this->getRequest(), $this->getRoutes());

		$this->assertEquals('http://example.org/index.php/foo', $urlBuilder->to('/foo'));

		$this->assertEquals('http://example.org/index.php/foo?bar=baz', $urlBuilder->to('/foo', ['bar' => 'baz']));

		$this->assertEquals('http://example.org/index.php/foo?bar=baz&amp;baz=bar', $urlBuilder->to('/foo', ['bar' => 'baz', 'baz' => 'bar']));

		$this->assertEquals('http://example.org/index.php/foo?bar=baz&baz=bar', $urlBuilder->to('/foo', ['bar' => 'baz', 'baz' => 'bar'], '&'));

		$this->assertEquals('http://example.org/index.php/no-nb/foo', $urlBuilder->to('/foo', [], '&amp;', 'no-nb'));

		//

		$urlBuilder = new URLBuilder($this->getRequest(), $this->getRoutes(), true);

		$this->assertEquals('http://example.org/foo', $urlBuilder->to('/foo'));

		//

		$urlBuilder = new URLBuilder($this->getRequest('no-nb'), $this->getRoutes(), true);

		$this->assertEquals('http://example.org/no-nb/foo', $urlBuilder->to('/foo'));

		$this->assertEquals('http://example.org/foo', $urlBuilder->to('/foo', [], '&amp', false));

		$this->assertEquals('http://example.org/en-uk/foo', $urlBuilder->to('/foo', [], '&amp', 'en-uk'));
	}

	/**
	 *
	 */

	public function testToRoute()
	{
		$urlBuilder = new URLBuilder($this->getRequest(), $this->getRoutes(), true);

		$this->assertEquals('http://example.org/article/{id}/{slug}', $urlBuilder->toRoute('foo'));

		$this->assertEquals('http://example.org/article/1/bar', $urlBuilder->toRoute('foo', ['id' => 1, 'slug' => 'bar']));

		$this->assertEquals('http://example.org/article/1/bar', $urlBuilder->toRoute('foo', ['id' => 1, 'slug' => 'bar']));

		$this->assertEquals('http://example.org/article/{id}', $urlBuilder->toRoute('bar'));

		$this->assertEquals('http://example.org/article/1', $urlBuilder->toRoute('bar', ['id' => 1]));

		$this->assertEquals('http://example.org/article/1', $urlBuilder->toRoute('bar', ['id' => 1, 'slug' => null]));

		$this->assertEquals('http://example.org/article/1/bar?bar=baz&amp;baz=bar', $urlBuilder->toRoute('foo', ['id' => 1, 'slug' => 'bar'], ['bar' => 'baz', 'baz' => 'bar']));

		$this->assertEquals('http://example.org/article/1/bar?bar=baz&baz=bar', $urlBuilder->toRoute('foo', ['id' => 1, 'slug' => 'bar'], ['bar' => 'baz', 'baz' => 'bar'], '&'));

		$this->assertEquals('http://example.org/no-nb/article/1/bar', $urlBuilder->toRoute('foo', ['id' => 1, 'slug' => 'bar'], [], '&amp', 'no-nb'));
	}

	/**
	 *
	 */

	public function testToCurrent()
	{
		$request = $this->getRequest();

		$request->shouldReceive('get')->times(2)->andReturn([]);

		$urlBuilder = new URLBuilder($request, $this->getRoutes(), true);

		$this->assertEquals('http://example.org/foo/bar', $urlBuilder->current());

		$this->assertEquals('http://example.org/foo/bar?bar=baz&amp;baz=bar', $urlBuilder->current(['bar' => 'baz', 'baz' => 'bar']));

		$this->assertEquals('http://example.org/foo/bar?bar=baz&baz=bar', $urlBuilder->current(['bar' => 'baz', 'baz' => 'bar'], '&'));

		$this->assertEquals('http://example.org/no-nb/foo/bar', $urlBuilder->current([], '&amp', 'no-nb'));
	}

	/**
	 *
	 */

	public function testToCurrentWithQueryParams()
	{
		$request = $this->getRequest();

		$request->shouldReceive('get')->once()->andReturn(['foo' => 'bar']);

		$urlBuilder = new URLBuilder($request, $this->getRoutes(), true);

		$this->assertEquals('http://example.org/foo/bar?foo=bar', $urlBuilder->current());

		$this->assertEquals('http://example.org/foo/bar?bar=foo', $urlBuilder->current(['bar' => 'foo']));
	}
}