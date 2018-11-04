<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\http\routing;

use mako\http\Request;
use mako\http\request\Parameters;
use mako\http\Response;
use mako\http\response\Headers;
use mako\http\routing\constraints\Constraint;
use mako\http\routing\Router;
use mako\http\routing\Routes;
use mako\syringe\Container;
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
		$request = Mockery::mock(Request::class);

		$request->makePartial();

		return $request;
	}

	/**
	 * @expectedException \mako\http\exceptions\NotFoundException
	 */
	public function testPageNotFound()
	{
		$routes = new Routes;

		$routes->get('/bar', 'Foo::bar');

		$router = new Router($routes);

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
		$routes = new Routes;

		$routes->post('/foo', 'Foo::bar');

		$router = new Router($routes);

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
		$routes = new Routes;

		$routes->get('/foo/', 'Foo::bar');

		$router = new Router($routes);

		$request = $this->getRequest();

		$request->shouldReceive('method')->andReturn('GET');

		$request->shouldReceive('path')->andReturn('/foo');

		$routed = $router->route($request);

		$this->assertInstanceOf('\mako\http\routing\Route', $routed);

		$this->assertEmpty($routed->getRoute());

		$this->assertEmpty($routed->getMethods());

		$action = $routed->getAction();

		$this->assertInstanceOf('Closure', $action);

		//

		$request->shouldReceive('isClean')->andReturn(true);

		$request->shouldReceive('baseURL')->once()->andReturn('http://example.org');

		$request->shouldReceive('languagePrefix')->once()->andReturn('en');

		$query = Mockery::mock(Parameters::class);

		$query->shouldReceive('all')->once()->andReturn(['foo' => 'bar']);

		$request->shouldReceive('getQuery')->once()->andReturn($query);

		$returnValue = $action($request);

		$this->assertInstanceOf('mako\http\response\senders\Redirect', $returnValue);

		//

		$responseHeaders = Mockery::mock(Headers::class);

		$responseHeaders->shouldReceive('add')->once()->with('Location', 'http://example.org/en/foo/?foo=bar');

		$response = Mockery::mock(Response::class);

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
		$routes = new Routes;

		$routes->get('/foo/', 'Foo::bar');

		$router = new Router($routes);

		$request = $this->getRequest();

		$request->shouldReceive('method')->andReturn('GET');

		$request->shouldReceive('path')->andReturn('/foo');

		$routed = $router->route($request);

		$this->assertInstanceOf('\mako\http\routing\Route', $routed);

		$this->assertEmpty($routed->getRoute());

		$this->assertEmpty($routed->getMethods());

		$action = $routed->getAction();

		$this->assertInstanceOf('Closure', $action);

		//

		$request->shouldReceive('isClean')->andReturn(false);

		$request->shouldReceive('scriptName')->andReturn('index.php');

		$request->shouldReceive('baseURL')->once()->andReturn('http://example.org');

		$request->shouldReceive('languagePrefix')->once()->andReturn('en');

		$query = Mockery::mock(Parameters::class);

		$query->shouldReceive('all')->once()->andReturn(['foo' => 'bar']);

		$request->shouldReceive('getQuery')->once()->andReturn($query);

		$returnValue = $action($request);

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
		$routes = new Routes;

		$routes->post('/foo', 'Foo::bar');

		$router = new Router($routes);

		$request = $this->getRequest();

		$request->shouldReceive('method')->andReturn('OPTIONS');

		$request->shouldReceive('path')->andReturn('/foo');

		$routed = $router->route($request);

		$this->assertInstanceOf('mako\http\routing\Route', $routed);

		$action = $routed->getAction();

		$this->assertInstanceOf('Closure', $action);

		//

		$responseHeaders = Mockery::mock(Headers::class);

		$responseHeaders->shouldReceive('add')->once()->with('Allow', 'POST,OPTIONS');

		$response = Mockery::mock('mako\http\Response');

		$response->shouldReceive('getHeaders')->once()->andReturn($responseHeaders);

		$action($response);
	}

	/**
	 *
	 */
	public function testSuccessfulRoute()
	{
		$routes = new Routes;

		$routes->post('/foo', 'Foo::bar', 'post.foo');

		$routes->get('/foo', 'Foo::bar', 'get.foo');

		$router = new Router($routes);

		$request = $this->getRequest();

		$request->shouldReceive('method')->andReturn('GET');

		$request->shouldReceive('path')->andReturn('/foo');

		$routed = $router->route($request);

		$this->assertSame('get.foo', $routed->getName());

		$this->assertSame($routed, $request->getRoute());
	}

	/**
	 *
	 */
	public function testSuccessfulRouteWithParameters()
	{
		$routes = new Routes;

		$routes->post('/foo/{id}', 'Foo::bar', 'post.foo');

		$routes->get('/foo/{id}', 'Foo::bar', 'get.foo');

		$router = new Router($routes);

		$request = $this->getRequest();

		$request->shouldReceive('method')->andReturn('GET');

		$request->shouldReceive('path')->andReturn('/foo/123');

		$routed = $router->route($request);

		$this->assertSame('get.foo', $routed->getName());

		$this->assertSame($routed, $request->getRoute());

		$this->assertSame(['id' => '123'], $routed->getParameters());
	}

	/**
	 *
	 */
	public function testSatisfiedConstraint()
	{
		$routes = new Routes;

		$routes->get('/foo', 'Foo::bar', 'get.foo')->constraint('bar');

		$container = Mockery::mock('\mako\syringe\Container');

		$container->shouldReceive('get')->once()->with(BarConstraint::class)->andReturn(new BarConstraint);

		$router = new Router($routes, $container);

		$router->registerConstraint('bar', BarConstraint::class);

		$request = $this->getRequest();

		$request->shouldReceive('method')->andReturn('GET');

		$request->shouldReceive('path')->andReturn('/foo');

		$routed = $router->route($request);

		$this->assertSame('get.foo', $routed->getName());

		$this->assertSame($routed, $request->getRoute());
	}

	/**
	 * @expectedException \mako\http\exceptions\NotFoundException
	 */
	public function testFailingConstraint()
	{
		$routes = new Routes;

		$routes->get('/foo', 'Foo::bar', 'get.foo')->constraint('foo');

		$container = Mockery::mock(Container::class);

		$container->shouldReceive('get')->times(2)->with(FooConstraint::class)->andReturn(new FooConstraint);

		$router = new Router($routes, $container);

		$router->registerConstraint('foo', FooConstraint::class);

		$request = $this->getRequest();

		$request->shouldReceive('method')->andReturn('GET');

		$request->shouldReceive('path')->andReturn('/foo');

		$router->route($request);
	}

	/**
	 * @expectedException \mako\http\exceptions\NotFoundException
	 */
	public function testGlobalFailingConstraint()
	{
		$routes = new Routes;

		$routes->get('/foo', 'Foo::bar', 'get.foo');

		$container = Mockery::mock(Container::class);

		$container->shouldReceive('get')->times(2)->with(FooConstraint::class)->andReturn(new FooConstraint);

		$router = new Router($routes, $container);

		$router->registerConstraint('foo', FooConstraint::class);

		$router->setConstraintAsGlobal(['foo']);

		$request = $this->getRequest();

		$request->shouldReceive('method')->andReturn('GET');

		$request->shouldReceive('path')->andReturn('/foo');

		$router->route($request);
	}

	/**
	 * @expectedException RuntimeException
	 * @expectedExceptionMessage No constraint named [ foo ] has been registered.
	 */
	public function testUnregisteredConstraint()
	{
		$routes = new Routes;

		$routes->get('/foo', 'Foo::bar', 'get.foo')->constraint('foo');

		$router = new Router($routes);

		$request = $this->getRequest();

		$request->shouldReceive('method')->andReturn('GET');

		$request->shouldReceive('path')->andReturn('/foo');

		$router->route($request);
	}
}
