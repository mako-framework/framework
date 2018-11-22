<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\http\routing;

use mako\http\routing\URLBuilder;
use mako\tests\TestCase;
use Mockery;

/**
 * @group unit
 */
class URLBuilderTest extends TestCase
{
	/**
	 *
	 */
	public function getRequest($langPrefix = '')
	{
		$request = Mockery::mock('\mako\http\Request');

		$request->shouldReceive('path')->andReturn('/foo/bar');

		$request->shouldReceive('baseURL')->andReturn('http://example.org');

		$request->shouldReceive('languagePrefix')->andReturn($langPrefix);

		$request->shouldReceive('scriptName')->andReturn('index.php');

		return $request;
	}

	/**
	 *
	 */
	public function getRoutes()
	{
		$route1 = Mockery::mock('\mako\http\routing\Route');

		$route1->shouldReceive('getRoute')->andReturn('/article/{id}/{slug}');

		$route2 = Mockery::mock('\mako\http\routing\Route');

		$route2->shouldReceive('getRoute')->andReturn('/article/{id}/{slug}?');

		$routes = Mockery::mock('\mako\http\routing\Routes');

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
	public function testBaseWithConfiguredURL()
	{
		$request = Mockery::mock('\mako\http\Request');

		$request->shouldReceive('languagePrefix')->once()->andReturn('');

		$request->shouldReceive('scriptName')->andReturn('index.php');

		$request->shouldReceive('baseURL')->never();

		$urlBuilder = new URLBuilder($request, $this->getRoutes(), false, 'http://example.com');

		$this->assertEquals('http://example.com', $urlBuilder->base());
	}

	/**
	 *
	 */
	public function testTo()
	{
		$urlBuilder = new URLBuilder($this->getRequest(), $this->getRoutes());

		$this->assertEquals('http://example.org/index.php/foo', $urlBuilder->to('/foo'));

		$this->assertEquals('http://example.org/index.php/foo?bar=baz', $urlBuilder->to('/foo', ['bar' => 'baz']));

		$this->assertEquals('http://example.org/index.php/foo?bar=baz&baz=bar', $urlBuilder->to('/foo', ['bar' => 'baz', 'baz' => 'bar']));

		$this->assertEquals('http://example.org/index.php/foo?bar=baz&amp;baz=bar', $urlBuilder->to('/foo', ['bar' => 'baz', 'baz' => 'bar'], '&amp;'));

		$this->assertEquals('http://example.org/index.php/no-nb/foo', $urlBuilder->to('/foo', [], '&', 'no-nb'));

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

		$this->assertEquals('http://example.org/article/0/bar', $urlBuilder->toRoute('foo', ['id' => 0, 'slug' => 'bar']));

		$this->assertEquals('http://example.org/article/{id}', $urlBuilder->toRoute('bar'));

		$this->assertEquals('http://example.org/article/1', $urlBuilder->toRoute('bar', ['id' => 1]));

		$this->assertEquals('http://example.org/article/1', $urlBuilder->toRoute('bar', ['id' => 1, 'slug' => null]));

		$this->assertEquals('http://example.org/article/1/bar?bar=baz&baz=bar', $urlBuilder->toRoute('foo', ['id' => 1, 'slug' => 'bar'], ['bar' => 'baz', 'baz' => 'bar']));

		$this->assertEquals('http://example.org/article/1/bar?bar=baz&amp;baz=bar', $urlBuilder->toRoute('foo', ['id' => 1, 'slug' => 'bar'], ['bar' => 'baz', 'baz' => 'bar'], '&amp;'));

		$this->assertEquals('http://example.org/no-nb/article/1/bar', $urlBuilder->toRoute('foo', ['id' => 1, 'slug' => 'bar'], [], '&', 'no-nb'));
	}

	/**
	 *
	 */
	public function testToCurrent()
	{
		$request = $this->getRequest();

		$query = Mockery::mock('mako\http\request\Parameters');

		$query->shouldReceive('all')->times(2)->andReturn([]);

		$request->shouldReceive('getQuery')->times(2)->andReturn($query);

		$urlBuilder = new URLBuilder($request, $this->getRoutes(), true);

		$this->assertEquals('http://example.org/foo/bar', $urlBuilder->current());

		$this->assertEquals('http://example.org/foo/bar?bar=baz&baz=bar', $urlBuilder->current(['bar' => 'baz', 'baz' => 'bar']));

		$this->assertEquals('http://example.org/foo/bar?bar=baz&amp;baz=bar', $urlBuilder->current(['bar' => 'baz', 'baz' => 'bar'], '&amp;'));

		$this->assertEquals('http://example.org/no-nb/foo/bar', $urlBuilder->current([], '&amp', 'no-nb'));
	}

	/**
	 *
	 */
	public function testToCurrentWithQueryParams()
	{
		$request = $this->getRequest();

		$query = Mockery::mock('mako\http\request\Parameters');

		$query->shouldReceive('all')->once()->andReturn(['foo' => 'bar']);

		$request->shouldReceive('getQuery')->once()->andReturn($query);

		$urlBuilder = new URLBuilder($request, $this->getRoutes(), true);

		$this->assertEquals('http://example.org/foo/bar?foo=bar', $urlBuilder->current());

		$this->assertEquals('http://example.org/foo/bar?bar=foo', $urlBuilder->current(['bar' => 'foo']));
	}
}
