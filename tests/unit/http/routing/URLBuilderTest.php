<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\http\routing;

use mako\http\Request;
use mako\http\request\Parameters;
use mako\http\routing\Route;
use mako\http\routing\Routes;
use mako\http\routing\URLBuilder;
use mako\tests\TestCase;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class URLBuilderTest extends TestCase
{
	/**
	 *
	 */
	public function getRequest($langPrefix = ''): MockInterface&Request
	{
		$request = Mockery::mock(Request::class);

		$request->shouldReceive('getPath')->andReturn('/foo/bar');

		$request->shouldReceive('getBaseURL')->andReturn('http://example.org');

		$request->shouldReceive('getLanguagePrefix')->andReturn($langPrefix);

		$request->shouldReceive('getScriptName')->andReturn('index.php');

		return $request;
	}

	/**
	 *
	 */
	public function getRoutes(): MockInterface&Routes
	{
		$route1 = Mockery::mock(Route::class);

		$route1->shouldReceive('getRoute')->andReturn('/article/{id}/{slug}');

		$route2 = Mockery::mock(Route::class);

		$route2->shouldReceive('getRoute')->andReturn('/article/{id}/{slug}?');

		$routes = Mockery::mock(Routes::class);

		$routes->shouldReceive('getNamedRoute')->withArgs(['foo'])->andReturn($route1);

		$routes->shouldReceive('getNamedRoute')->withArgs(['bar'])->andReturn($route2);

		return $routes;
	}

	/**
	 *
	 */
	public function testMatches(): void
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
	public function testBase(): void
	{
		$urlBuilder = new URLBuilder($this->getRequest(), $this->getRoutes());

		$this->assertEquals('http://example.org', $urlBuilder->base());
	}

	/**
	 *
	 */
	public function testBaseWithConfiguredURL(): void
	{
		$request = Mockery::mock(Request::class);

		$request->shouldReceive('getLanguagePrefix')->once()->andReturn('');

		$request->shouldReceive('getScriptName')->andReturn('index.php');

		$request->shouldReceive('getBaseURL')->never();

		$urlBuilder = new URLBuilder($request, $this->getRoutes(), false, 'http://example.com');

		$this->assertEquals('http://example.com', $urlBuilder->base());
	}

	/**
	 *
	 */
	public function testTo(): void
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
	public function testToRoute(): void
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
	public function testToCurrent(): void
	{
		$request = $this->getRequest();

		$query = Mockery::mock(Parameters::class);

		$query->shouldReceive('all')->times(2)->andReturn([]);

		(function () use ($query): void {
			$this->query = $query;
		})->bindTo($request, Request::class)();

		$urlBuilder = new URLBuilder($request, $this->getRoutes(), true);

		$this->assertEquals('http://example.org/foo/bar', $urlBuilder->current());

		$this->assertEquals('http://example.org/foo/bar?bar=baz&baz=bar', $urlBuilder->current(['bar' => 'baz', 'baz' => 'bar']));

		$this->assertEquals('http://example.org/foo/bar?bar=baz&amp;baz=bar', $urlBuilder->current(['bar' => 'baz', 'baz' => 'bar'], '&amp;'));

		$this->assertEquals('http://example.org/no-nb/foo/bar', $urlBuilder->current([], '&amp', 'no-nb'));
	}

	/**
	 *
	 */
	public function testToCurrentWithQueryParams(): void
	{
		$request = $this->getRequest();

		$query = Mockery::mock(Parameters::class);

		$query->shouldReceive('all')->once()->andReturn(['foo' => 'bar']);

		(function () use ($query): void {
			$this->query = $query;
		})->bindTo($request, Request::class)();

		$urlBuilder = new URLBuilder($request, $this->getRoutes(), true);

		$this->assertEquals('http://example.org/foo/bar?foo=bar', $urlBuilder->current());

		$this->assertEquals('http://example.org/foo/bar?bar=foo', $urlBuilder->current(['bar' => 'foo']));

		$this->assertEquals('http://example.org/foo/bar', $urlBuilder->current(null));
	}
}
