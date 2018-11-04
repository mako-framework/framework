<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\http\routing;

use mako\http\response\Headers;
use mako\http\routing\constraints\Constraint;
use mako\http\routing\Router;
use mako\tests\TestCase;
use Mockery;
use Throwable;

// --------------------------------------------------------------------------
// START CLASSES
// --------------------------------------------------------------------------

class FooConstraint extends Constraint
{
	public function isSatisfied(): bool
	{
		return false;
	}
}

class BarConstraint extends Constraint
{
	public function isSatisfied(): bool
	{
		return true;
	}
}

// --------------------------------------------------------------------------
// END CLASSES
// --------------------------------------------------------------------------

/**
 * @group unit
 */
class RouterTest extends TestCase
{
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
	public function getRouter($routes, $container = null)
	{
		return new Router($this->getRouteCollection($routes), $container);
	}

	/**
	 * @expectedException \mako\http\exceptions\NotFoundException
	 */
	public function testPageNotFound()
	{
		$route = Mockery::mock('\mako\http\routing\Route');

		$route->shouldReceive('allowsMethod')->andReturn(true);

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

		$route->shouldReceive('getConstraints')->andReturn([]);

		$route->shouldReceive('allowsMethod')->andReturn(false);

		$route->shouldReceive('setParameters')/*->once()*/->with([]);

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

		$route->shouldReceive('getConstraints')->andReturn([]);

		$route->shouldReceive('allowsMethod')->andReturn(true);

		$route->shouldReceive('hasTrailingSlash')->andReturn(true);

		$route->shouldReceive('setParameters')->once()->with([]);

		$router = $this->getRouter([$route]);

		$request = $this->getRequest();

		$request->shouldReceive('method')->andReturn('GET');

		$request->shouldReceive('path')->andReturn('/foo');

		$request->shouldReceive('isClean')->andReturn(true);

		$routed = $router->route($request);

		$this->assertInstanceOf('\mako\http\routing\Route', $routed);

		$this->assertInstanceOf('Closure', $routed->getAction());

		$this->assertEmpty($routed->getRoute());

		$this->assertEmpty($routed->getMethods());

		//

		$closure = $routed->getAction();

		$request->shouldReceive('baseURL')->once()->andReturn('http://example.org');

		$request->shouldReceive('languagePrefix')->once()->andReturn('en');

		$query = Mockery::mock('mako\http\request\Parameters');

		$query->shouldReceive('all')->once()->andReturn(['foo' => 'bar']);

		$request->shouldReceive('getQuery')->once()->andReturn($query);

		$returnValue = $closure($request);

		$this->assertInstanceOf('mako\http\response\senders\Redirect', $returnValue);

		//

		$responseHeaders = Mockery::mock(Headers::class);

		$responseHeaders->shouldReceive('add')->once()->with('Location', 'http://example.org/en/foo/?foo=bar');

		$response = Mockery::mock('mako\http\Response');

		$response->shouldReceive('status')->once()->with(301);

		$response->shouldReceive('getHeaders')->once()->andReturn($responseHeaders);

		$response->shouldReceive('sendHeaders')->once();

		$returnValue->send($request, $response);
	}

	/**
	 *
	 */
	public function testRedirectWithDirtyUrl()
	{
		$route = Mockery::mock('\mako\http\routing\Route');

		$route->shouldReceive('getRegex')->andReturn('#^/foo$#s');

		$route->shouldReceive('getConstraints')->andReturn([]);

		$route->shouldReceive('allowsMethod')->andReturn(true);

		$route->shouldReceive('hasTrailingSlash')->andReturn(true);

		$route->shouldReceive('setParameters')->once()->with([]);

		$router = $this->getRouter([$route]);

		$request = $this->getRequest();

		$request->shouldReceive('method')->andReturn('GET');

		$request->shouldReceive('path')->andReturn('/foo');

		$request->shouldReceive('isClean')->andReturn(false);

		$request->shouldReceive('scriptName')->andReturn('index.php');

		$routed = $router->route($request);

		$this->assertInstanceOf('\mako\http\routing\Route', $routed);

		$this->assertInstanceOf('Closure', $routed->getAction());

		$this->assertEmpty($routed->getRoute());

		$this->assertEmpty($routed->getMethods());

		//

		$closure = $routed->getAction();

		$request->shouldReceive('baseURL')->once()->andReturn('http://example.org');

		$request->shouldReceive('languagePrefix')->once()->andReturn('en');

		$query = Mockery::mock('mako\http\request\Parameters');

		$query->shouldReceive('all')->once()->andReturn(['foo' => 'bar']);

		$request->shouldReceive('getQuery')->once()->andReturn($query);

		$returnValue = $closure($request);

		$this->assertInstanceOf('mako\http\response\senders\Redirect', $returnValue);

		//

		$responseHeaders = Mockery::mock(Headers::class);

		$responseHeaders->shouldReceive('add')->once()->with('Location', 'http://example.org/index.php/en/foo/?foo=bar');

		$response = Mockery::mock('mako\http\Response');

		$response->shouldReceive('status')->once()->with(301);

		$response->shouldReceive('getHeaders')->once()->andReturn($responseHeaders);

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

		$route->shouldReceive('allowsMethod')->andReturn(true);

		$route->shouldReceive('hasTrailingSlash')->andReturn(false);

		$route->shouldReceive('getMethods')->andReturn(['POST', 'OPTIONS']);

		$route->shouldReceive('setParameters')/*->once()*/->with([]);

		$router = $this->getRouter([$route]);

		$request = $this->getRequest();

		$request->shouldReceive('method')->andReturn('OPTIONS');

		$request->shouldReceive('path')->andReturn('/foo');

		$routed = $router->route($request);

		$this->assertInstanceOf('mako\http\routing\Route', $routed);

		$this->assertInstanceOf('Closure', $routed->getAction());

		$this->assertEmpty($routed->getRoute());

		$this->assertEmpty($routed->getMethods());

		//

		$closure = $routed->getAction();

		$responseHeaders = Mockery::mock(Headers::class);

		$responseHeaders->shouldReceive('add')->once()->with('Allow', 'POST,OPTIONS');

		$response = Mockery::mock('mako\http\Response');

		$response->shouldReceive('getHeaders')->once()->andReturn($responseHeaders);

		$closure($response);
	}

	/**
	 *
	 */
	public function testSuccessfulRoute()
	{
		$route = Mockery::mock('\mako\http\routing\Route');

		$route->makePartial();

		$route->shouldReceive('getRegex')->andReturn('#^/foo$#s');

		$route->shouldReceive('allowsMethod')->andReturn(true);

		$route->shouldReceive('hasTrailingSlash')->andReturn(false);

		$route->shouldReceive('setParameters')->once()->with([]);

		$router = $this->getRouter([$route]);

		$request = $this->getRequest();

		$request->shouldReceive('method')->andReturn('GET');

		$request->shouldReceive('path')->andReturn('/foo');

		$request->shouldReceive('setRoute')->once()->with($route);

		$routed = $router->route($request);

		$this->assertSame($route, $routed);
	}

	/**
	 *
	 */
	public function testSuccessfulRouteWithParameters()
	{
		$params = ['id' => '123'];

		//

		$route = Mockery::mock('\mako\http\routing\Route');

		$route->makePartial();

		$route->shouldReceive('getRegex')->andReturn('#^/foo/(?P<id>[^/]++)$#s');

		$route->shouldReceive('allowsMethod')->andReturn(true);

		$route->shouldReceive('hasTrailingSlash')->andReturn(false);

		$route->shouldReceive('setParameters')->once()->with($params);

		$router = $this->getRouter([$route]);

		$request = $this->getRequest();

		$request->shouldReceive('method')->andReturn('GET');

		$request->shouldReceive('path')->andReturn('/foo/123');

		$request->shouldReceive('setRoute')->once()->with($route);

		$routed = $router->route($request);

		$this->assertSame($route, $routed);
	}

	/**
	 *
	 */
	public function testSatisfiedConstraint()
	{
		$route = Mockery::mock('\mako\http\routing\Route');

		$route->makePartial();

		$route->shouldReceive('getRegex')->andReturn('#^/foo$#s');

		$route->shouldReceive('getConstraints')->andReturn(['bar']);

		$route->shouldReceive('allowsMethod')->andReturn(true);

		$route->shouldReceive('hasTrailingSlash')->andReturn(false);

		$route->shouldReceive('setParameters')->once()->with([]);

		$container = Mockery::mock('\mako\syringe\Container');

		$container->shouldReceive('get')->once()->with(BarConstraint::class)->andReturn(new BarConstraint);

		$router = $this->getRouter([$route], $container);

		$router->registerConstraint('bar', BarConstraint::class);

		$request = $this->getRequest();

		$request->shouldReceive('method')->andReturn('GET');

		$request->shouldReceive('path')->andReturn('/foo');

		$request->shouldReceive('setRoute')->once()->with($route);

		$routed = $router->route($request);

		$this->assertSame($route, $routed);
	}

	/**
	 * @expectedException \mako\http\exceptions\NotFoundException
	 */
	public function testFailingConstraint()
	{
		$route = Mockery::mock('\mako\http\routing\Route');

		$route->makePartial();

		$route->shouldReceive('allowsMethod')->andReturn(true);

		$route->shouldReceive('getRegex')->andReturn('#^/foo$#s');

		$route->shouldReceive('getConstraints')->andReturn(['foo']);

		$container = Mockery::mock('\mako\syringe\Container');

		$container->shouldReceive('get')->times(2)->with(FooConstraint::class)->andReturn(new FooConstraint);

		$router = $this->getRouter([$route], $container);

		$router->registerConstraint('foo', FooConstraint::class);

		$request = $this->getRequest();

		$request->shouldReceive('method')->andReturn('GET');

		$request->shouldReceive('path')->andReturn('/foo');

		$routed = $router->route($request);

		$this->assertSame($route, $routed);
	}

	/**
	 * @expectedException \mako\http\exceptions\NotFoundException
	 */
	public function testGlobalFailingConstraint()
	{
		$route = Mockery::mock('\mako\http\routing\Route');

		$route->makePartial();

		$route->shouldReceive('allowsMethod')->andReturn(true);

		$route->shouldReceive('getRegex')->andReturn('#^/foo$#s');

		$route->shouldReceive('getConstraints')->andReturn([]);

		$container = Mockery::mock('\mako\syringe\Container');

		$container->shouldReceive('get')->times(2)->with(FooConstraint::class)->andReturn(new FooConstraint);

		$router = $this->getRouter([$route], $container);

		$router->registerConstraint('foo', FooConstraint::class);

		$router->setConstraintAsGlobal(['foo']);

		$request = $this->getRequest();

		$request->shouldReceive('method')->andReturn('GET');

		$request->shouldReceive('path')->andReturn('/foo');

		$routed = $router->route($request);

		$this->assertSame($route, $routed);
	}

	/**
	 * @expectedException RuntimeException
	 * @expectedExceptionMessage No constraint named [ foo ] has been registered.
	 */
	public function testUnregisteredConstraint()
	{
		$route = Mockery::mock('\mako\http\routing\Route');

		$route->makePartial();

		$route->shouldReceive('allowsMethod')->andReturn(true);

		$route->shouldReceive('getRegex')->andReturn('#^/foo$#s');

		$route->shouldReceive('getConstraints')->andReturn(['foo']);

		$router = $this->getRouter([$route]);

		$request = $this->getRequest();

		$request->shouldReceive('method')->andReturn('GET');

		$request->shouldReceive('path')->andReturn('/foo');

		$router->route($request);
	}
}
