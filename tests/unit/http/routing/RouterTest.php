<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\tests\unit\http\routing;

use Throwable;

use Mockery;
use PHPUnit_Framework_TestCase;

use mako\http\routing\Router;

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
		Mockery::close();
	}

	/**
	 *
	 */
	public function getRequest()
	{
		$request = Mockery::mock('\mako\http\Request');

		return $request;
	}

	/**
	 *
	 */
	public function getRouteCollection($routes)
	{
		$collection = Mockery::mock('\mako\http\routing\Routes');

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
		$route = Mockery::mock('\mako\http\routing\Route');

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
		$route = Mockery::mock('\mako\http\routing\Route');

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
		catch(Throwable $e)
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
		$route = Mockery::mock('\mako\http\routing\Route');

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

		$returnValue = $closure($request);

		$this->assertInstanceOf('mako\http\response\senders\Redirect', $returnValue);

		//

		$response = Mockery::mock('mako\http\Response');

		$response->shouldReceive('status')->once()->with(301);

		$response->shouldReceive('header')->once()->with('Location', 'http://example.org/en/foo/?foo=bar');

		$response->shouldReceive('sendHeaders')->once();

		$returnValue->send($request, $response);
	}

	/**
	 *
	 */
	public function testOptionsRequest()
	{
		$route = Mockery::mock('\mako\http\routing\Route');

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

		$response = Mockery::mock('mako\http\Response');

		$response->shouldReceive('header')->once()->with('allow', 'POST,OPTIONS');

		$closure($response);
	}

	/**
	 *
	 */
	public function testSuccessfulRoute()
	{
		$route = Mockery::mock('\mako\http\routing\Route');

		$route->shouldReceive('getRegex')->andReturn('#^/foo$#s');

		$route->shouldReceive('allows')->andReturn(true);

		$route->shouldReceive('hasTrailingSlash')->andReturn(false);

		$route->shouldReceive('setParameters')->with([]);

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
		$route = Mockery::mock('\mako\http\routing\Route');

		$route->shouldReceive('getRegex')->andReturn('#^/foo/(?P<id>[^/]++)$#s');

		$route->shouldReceive('allows')->andReturn(true);

		$route->shouldReceive('hasTrailingSlash')->andReturn(false);

		$route->shouldReceive('setParameters')->with(['id' => '123']);

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