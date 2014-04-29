<?php

use \mako\http\routing\Router;

use Mockery as m;

/**
 * @group unit
 */

class RouterTest extends PHPUnit_Framework_TestCase
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

	public function getRouter($request, $routes)
	{
		return new Router($request, $this->getRouteCollection($routes));
	}

	/**
	 * @expectedException \mako\http\routing\PageNotFoundException
	 */

	public function testPageNotFound()
	{
		$request = $this->getRequest();

		$request->shouldReceive('method')->andReturn('GET');

		$request->shouldReceive('path')->andReturn('/foo');

		$route = m::mock('\mako\http\routing\Route');

		$route->shouldReceive('isMatch')->andReturn(false);

		$router = $this->getRouter($request, [$route]);

		$router->route();
	}

	/**
	 * @expectedException \mako\http\routing\MethodNotAllowedException
	 */

	public function testMethodNotAllowed()
	{
		$request = $this->getRequest();

		$request->shouldReceive('method')->andReturn('GET');

		$request->shouldReceive('path')->andReturn('/foo');

		$route = m::mock('\mako\http\routing\Route');

		$route->shouldReceive('isMatch')->andReturn(true);

		$route->shouldReceive('allows')->andReturn(false);

		$route->shouldReceive('getMethods')->andReturn(['POST', 'OPTIONS']);

		$router = $this->getRouter($request, [$route]);

		try
		{
			$router->route();
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
		$request = $this->getRequest();

		$request->shouldReceive('method')->andReturn('GET');

		$request->shouldReceive('path')->andReturn('/foo');

		$route = m::mock('\mako\http\routing\Route');

		$route->shouldReceive('isMatch')->andReturn(true);

		$route->shouldReceive('allows')->andReturn(true);

		$route->shouldReceive('hasTrailingSlash')->andReturn(true);

		$router = $this->getRouter($request, [$route]);

		$routed = $router->route();

		$this->assertInstanceOf('\mako\http\routing\Route', $routed);

		$this->assertEmpty($routed->getRoute());

		$this->assertEmpty($routed->getMethods());
	}

	/**
	 * 
	 */

	public function testOptionsRequest()
	{
		$request = $this->getRequest();

		$request->shouldReceive('method')->andReturn('OPTIONS');

		$request->shouldReceive('path')->andReturn('/foo');

		$route = m::mock('\mako\http\routing\Route');

		$route->makePartial();

		$route->shouldReceive('isMatch')->andReturn(true);

		$route->shouldReceive('allows')->andReturn(true);

		$route->shouldReceive('hasTrailingSlash')->andReturn(false);

		$route->shouldReceive('getMethods')->andReturn(['POST', 'OPTIONS']);

		$router = $this->getRouter($request, [$route]);

		$routed = $router->route();

		$this->assertEquals(['allow' => 'POST,OPTIONS'], $routed->getHeaders());
	}

	/**
	 * 
	 */

	public function testSuccessfulRoute()
	{
		$request = $this->getRequest();

		$request->shouldReceive('method')->andReturn('GET');

		$request->shouldReceive('path')->andReturn('/foo');

		$route = m::mock('\mako\http\routing\Route');

		$route->shouldReceive('isMatch')->andReturn(true);

		$route->shouldReceive('allows')->andReturn(true);

		$route->shouldReceive('hasTrailingSlash')->andReturn(false);

		$router = $this->getRouter($request, [$route]);

		$routed = $router->route();

		$this->assertEquals($route, $routed);
	}
}