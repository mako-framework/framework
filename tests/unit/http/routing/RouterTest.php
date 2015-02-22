<?php

namespace mako\tests\unit\http\routing;

use mako\http\routing\Router;

use \Mockery as m;

/**
 * @group unit
 */

class RouterTest extends \PHPUnit_Framework_TestCase
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

	public function getRequest()
	{
		$request = m::mock('\mako\http\Request');

		return $request;
	}

	/**
	 *
	 */

	public function getRouteCollection($routes)
	{
		$collection = m::mock('\mako\http\routing\Routes');

		$collection->shouldReceive('getRoutes')->andReturn($routes);

		return $collection;
	}

	/**
	 *
	 */

	public function getRouter($routes)
	{
		return new Router($this->getRouteCollection($routes));
	}

	/**
	 * @expectedException \mako\http\exceptions\NotFoundException
	 */

	public function testPageNotFound()
	{
		$route = m::mock('\mako\http\routing\Route');

		$route->shouldReceive('getRegex')->andReturn('#^/bar$#s');

		$router = $this->getRouter([$route]);

		$request = $this->getRequest();

		$request->shouldReceive('method')->andReturn('GET');

		$request->shouldReceive('path')->andReturn('/foo');

		$router->route($request);
	}

	/**
	 * @expectedException \mako\http\exceptions\MethodNotAllowedException
	 */

	public function testMethodNotAllowed()
	{
		$route = m::mock('\mako\http\routing\Route');

		$route->shouldReceive('getRegex')->andReturn('#^/foo$#s');

		$route->shouldReceive('allows')->andReturn(false);

		$route->shouldReceive('getMethods')->andReturn(['POST', 'OPTIONS']);

		$router = $this->getRouter([$route]);

		$request = $this->getRequest();

		$request->shouldReceive('method')->andReturn('GET');

		$request->shouldReceive('path')->andReturn('/foo');

		try
		{
			$router->route($request);
		}
		catch(Exception $e)
		{
			$this->assertEquals(['POST', 'OPTIONS'], $e->getAllowedMethods());

			throw $e;
		}
	}

	/**
	 *
	 */

	public function testRedirect()
	{
		$route = m::mock('\mako\http\routing\Route');

		$route->shouldReceive('getRegex')->andReturn('#^/foo$#s');

		$route->shouldReceive('allows')->andReturn(true);

		$route->shouldReceive('hasTrailingSlash')->andReturn(true);

		$router = $this->getRouter([$route]);

		$request = $this->getRequest();

		$request->shouldReceive('method')->andReturn('GET');

		$request->shouldReceive('path')->andReturn('/foo');

		$routed = $router->route($request);

		$this->assertInstanceOf('\mako\http\routing\Route', $routed[0]);

		$this->assertInstanceOf('Closure', $routed[0]->getAction());

		$this->assertSame([], $routed[1]);

		$this->assertEmpty($routed[0]->getRoute());

		$this->assertEmpty($routed[0]->getMethods());

		//

		$closure = $routed[0]->getAction();

		$request->shouldReceive('baseURL')->once()->andReturn('http://example.org');

		$request->shouldReceive('languagePrefix')->once()->andReturn('en');

		$request->shouldReceive('get')->once()->andReturn(['foo' => 'bar']);

		$response = m::mock('mako\http\Response');

		$redirect = m::mock('mako\http\responses\Redirect');

		$redirect->shouldReceive('status')->once()->with(301)->andReturn($redirect);

		$response->shouldReceive('redirect')->once()->with('http://example.org/en/foo/?foo=bar')->andReturn($redirect);

		$returnValue = $closure($request, $response);

		$this->assertInstanceOf('mako\http\responses\Redirect', $returnValue);
	}

	/**
	 *
	 */

	public function testOptionsRequest()
	{
		$route = m::mock('\mako\http\routing\Route');

		$route->makePartial();

		$route->shouldReceive('getRegex')->andReturn('#^/foo$#s');

		$route->shouldReceive('allows')->andReturn(true);

		$route->shouldReceive('hasTrailingSlash')->andReturn(false);

		$route->shouldReceive('getMethods')->andReturn(['POST', 'OPTIONS']);

		$router = $this->getRouter([$route]);

		$request = $this->getRequest();

		$request->shouldReceive('method')->andReturn('OPTIONS');

		$request->shouldReceive('path')->andReturn('/foo');

		$routed = $router->route($request);

		$this->assertInstanceOf('mako\http\routing\Route', $routed[0]);

		$this->assertInstanceOf('Closure', $routed[0]->getAction());

		$this->assertSame([], $routed[1]);

		$this->assertEmpty($routed[0]->getRoute());

		$this->assertEmpty($routed[0]->getMethods());

		//

		$closure = $routed[0]->getAction();

		$response = m::mock('mako\http\Response');

		$response->shouldReceive('header')->once()->with('allow', 'POST,OPTIONS');

		$closure($response);
	}

	/**
	 *
	 */

	public function testSuccessfulRoute()
	{
		$route = m::mock('\mako\http\routing\Route');

		$route->shouldReceive('getRegex')->andReturn('#^/foo$#s');

		$route->shouldReceive('allows')->andReturn(true);

		$route->shouldReceive('hasTrailingSlash')->andReturn(false);

		$router = $this->getRouter([$route]);

		$request = $this->getRequest();

		$request->shouldReceive('method')->andReturn('GET');

		$request->shouldReceive('path')->andReturn('/foo');

		$request->shouldReceive('setRoute')->once()->with($route);

		$routed = $router->route($request);

		$this->assertSame($route, $routed[0]);

		$this->assertSame([], $routed[1]);
	}

	/**
	 *
	 */

	public function testSuccessfulRouteWithParameters()
	{
		$route = m::mock('\mako\http\routing\Route');

		$route->shouldReceive('getRegex')->andReturn('#^/foo/(?P<id>[^/]++)$#s');

		$route->shouldReceive('allows')->andReturn(true);

		$route->shouldReceive('hasTrailingSlash')->andReturn(false);

		$router = $this->getRouter([$route]);

		$request = $this->getRequest();

		$request->shouldReceive('method')->andReturn('GET');

		$request->shouldReceive('path')->andReturn('/foo/123');

		$request->shouldReceive('setRoute')->once()->with($route);

		$routed = $router->route($request);

		$this->assertSame($route, $routed[0]);

		$this->assertSame(['id' => '123'], $routed[1]);
	}
}