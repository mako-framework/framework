<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\http\routing;

use Closure;
use mako\http\exceptions\MethodNotAllowedException;
use mako\http\exceptions\NotFoundException;
use mako\http\Request;
use mako\http\request\Parameters;
use mako\http\Response;
use mako\http\response\Headers;
use mako\http\response\senders\Redirect;
use mako\http\routing\constraints\ConstraintInterface;
use mako\http\routing\Route;
use mako\http\routing\Router;
use mako\http\routing\Routes;
use mako\syringe\Container;
use mako\tests\TestCase;
use Mockery;
use RuntimeException;
use Throwable;

// --------------------------------------------------------------------------
// START CLASSES
// --------------------------------------------------------------------------

class FooConstraint implements ConstraintInterface
{
	public function isSatisfied(): bool
	{
		return false;
	}
}

class BarConstraint implements ConstraintInterface
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
	 *
	 */
	public function testPageNotFound(): void
	{
		$this->expectException(NotFoundException::class);

		$routes = new Routes;

		$routes->get('/bar', fn() => 'Hello, world!');

		$router = new Router($routes);

		$request = $this->getRequest();

		$request->shouldReceive('getMethod')->andReturn('GET');

		$request->shouldReceive('getPath')->andReturn('/foo');

		$router->route($request);
	}

	/**
	 *
	 */
	public function testMethodNotAllowed(): void
	{
		$this->expectException(MethodNotAllowedException::class);

		$routes = new Routes;

		$routes->post('/foo', fn() => 'Hello, world!');

		$router = new Router($routes);

		$request = $this->getRequest();

		$request->shouldReceive('getMethod')->andReturn('GET');

		$request->shouldReceive('getPath')->andReturn('/foo');

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
	public function testRedirect(): void
	{
		$routes = new Routes;

		$routes->get('/foo/', fn() => 'Hello, world!');

		$router = new Router($routes);

		$request = $this->getRequest();

		$request->shouldReceive('getMethod')->andReturn('GET');

		$request->shouldReceive('getPath')->andReturn('/foo');

		$routed = $router->route($request);

		$this->assertInstanceOf(Route::class, $routed);

		$this->assertEmpty($routed->getRoute());

		$this->assertEmpty($routed->getMethods());

		$action = $routed->getAction();

		$this->assertInstanceOf(Closure::class, $action);

		//

		$request->shouldReceive('isClean')->andReturn(true);

		$request->shouldReceive('getBaseURL')->once()->andReturn('http://example.org');

		$request->shouldReceive('getLanguagePrefix')->once()->andReturn('en');

		$query = Mockery::mock(Parameters::class);

		$query->shouldReceive('all')->once()->andReturn(['foo' => 'bar']);

		$request->shouldReceive('getQuery')->once()->andReturn($query);

		$returnValue = $action($request);

		$this->assertInstanceOf(Redirect::class, $returnValue);

		//

		$responseHeaders = Mockery::mock(Headers::class);

		$responseHeaders->shouldReceive('add')->once()->with('Location', 'http://example.org/en/foo/?foo=bar');

		$response = Mockery::mock(Response::class);

		$response->shouldReceive('setStatus')->once()->with(301);

		$response->shouldReceive('getHeaders')->once()->andReturn($responseHeaders);

		$response->shouldReceive('sendHeaders')->once();

		$returnValue->send($request, $response);
	}

	/**
	 *
	 */
	public function testPostWithMissingTrailingSlash(): void
	{
		$this->expectException(NotFoundException::class);

		//

		$routes = new Routes;

		$routes->post('/foo/', fn() => 'Hello, world!');

		$router = new Router($routes);

		$request = $this->getRequest();

		$request->shouldReceive('getMethod')->andReturn('POST');

		$request->shouldReceive('getPath')->andReturn('/foo');

		$router->route($request);
	}

	/**
	 *
	 */
	public function testRedirectWithDirtyUrl(): void
	{
		$routes = new Routes;

		$routes->get('/foo/', fn() => 'Hello, world!');

		$router = new Router($routes);

		$request = $this->getRequest();

		$request->shouldReceive('getMethod')->andReturn('GET');

		$request->shouldReceive('getPath')->andReturn('/foo');

		$routed = $router->route($request);

		$this->assertInstanceOf(Route::class, $routed);

		$this->assertEmpty($routed->getRoute());

		$this->assertEmpty($routed->getMethods());

		$action = $routed->getAction();

		$this->assertInstanceOf('Closure', $action);

		//

		$request->shouldReceive('isClean')->andReturn(false);

		$request->shouldReceive('getScriptName')->andReturn('index.php');

		$request->shouldReceive('getBaseURL')->once()->andReturn('http://example.org');

		$request->shouldReceive('getLanguagePrefix')->once()->andReturn('en');

		$query = Mockery::mock(Parameters::class);

		$query->shouldReceive('all')->once()->andReturn(['foo' => 'bar']);

		$request->shouldReceive('getQuery')->once()->andReturn($query);

		$returnValue = $action($request);

		$this->assertInstanceOf(Redirect::class, $returnValue);

		//

		$responseHeaders = Mockery::mock(Headers::class);

		$responseHeaders->shouldReceive('add')->once()->with('Location', 'http://example.org/index.php/en/foo/?foo=bar');

		$response = Mockery::mock(Response::class);

		$response->shouldReceive('setStatus')->once()->with(301);

		$response->shouldReceive('getHeaders')->once()->andReturn($responseHeaders);

		$response->shouldReceive('sendHeaders')->once();

		$returnValue->send($request, $response);
	}

	/**
	 *
	 */
	public function testOptionsRequest(): void
	{
		$routes = new Routes;

		$routes->post('/foo', fn() => 'Hello, world!');

		$router = new Router($routes);

		$request = $this->getRequest();

		$request->shouldReceive('getMethod')->andReturn('OPTIONS');

		$request->shouldReceive('getPath')->andReturn('/foo');

		$routed = $router->route($request);

		$this->assertInstanceOf(Route::class, $routed);

		$action = $routed->getAction();

		$this->assertInstanceOf('Closure', $action);

		//

		$responseHeaders = Mockery::mock(Headers::class);

		$responseHeaders->shouldReceive('add')->once()->with('Allow', 'POST,OPTIONS');

		$response = Mockery::mock(Response::class);

		$response->shouldReceive('getHeaders')->once()->andReturn($responseHeaders);

		$action($response);
	}

	/**
	 *
	 */
	public function testSuccessfulRoute(): void
	{
		$routes = new Routes;

		$routes->post('/foo', fn() => 'Hello, world!', 'post.foo');

		$routes->get('/foo', fn() => 'Hello, world!', 'get.foo');

		$router = new Router($routes);

		$request = $this->getRequest();

		$request->shouldReceive('getMethod')->andReturn('GET');

		$request->shouldReceive('getPath')->andReturn('/foo');

		$routed = $router->route($request);

		$this->assertSame('get.foo', $routed->getName());

		$this->assertSame($routed, $request->getRoute());
	}

	/**
	 *
	 */
	public function testSuccessfulRouteWithParameters(): void
	{
		$routes = new Routes;

		$routes->post('/foo/{id}', fn() => 'Hello, world!', 'post.foo');

		$routes->get('/foo/{id}', fn() => 'Hello, world!', 'get.foo');

		$router = new Router($routes);

		$request = $this->getRequest();

		$request->shouldReceive('getMethod')->andReturn('GET');

		$request->shouldReceive('getPath')->andReturn('/foo/123');

		$routed = $router->route($request);

		$this->assertSame('get.foo', $routed->getName());

		$this->assertSame($routed, $request->getRoute());

		$this->assertSame(['id' => '123'], $routed->getParameters());
	}

	/**
	 *
	 */
	public function testSatisfiedConstraint(): void
	{
		$routes = new Routes;

		$routes->get('/foo', fn() => 'Hello, world!', 'get.foo')->constraint('bar');

		$container = Mockery::mock(Container::class);

		$container->shouldReceive('get')->once()->with(BarConstraint::class, [])->andReturn(new BarConstraint);

		$router = new Router($routes, $container);

		$router->registerConstraint('bar', BarConstraint::class);

		$request = $this->getRequest();

		$request->shouldReceive('getMethod')->andReturn('GET');

		$request->shouldReceive('getPath')->andReturn('/foo');

		$routed = $router->route($request);

		$this->assertSame('get.foo', $routed->getName());

		$this->assertSame($routed, $request->getRoute());
	}

	/**
	 *
	 */
	public function testFailingConstraint(): void
	{
		$this->expectException(NotFoundException::class);

		$routes = new Routes;

		$routes->get('/foo', fn() => 'Hello, world!', 'get.foo')->constraint('foo');

		$container = Mockery::mock(Container::class);

		$container->shouldReceive('get')->times(2)->with(FooConstraint::class, [])->andReturn(new FooConstraint);

		$router = new Router($routes, $container);

		$router->registerConstraint('foo', FooConstraint::class);

		$request = $this->getRequest();

		$request->shouldReceive('getMethod')->andReturn('GET');

		$request->shouldReceive('getPath')->andReturn('/foo');

		$router->route($request);
	}

	/**
	 *
	 */
	public function testGlobalFailingConstraint(): void
	{
		$this->expectException(NotFoundException::class);

		$routes = new Routes;

		$routes->get('/foo', fn() => 'Hello, world!', 'get.foo');

		$container = Mockery::mock(Container::class);

		$container->shouldReceive('get')->times(2)->with(FooConstraint::class, [])->andReturn(new FooConstraint);

		$router = new Router($routes, $container);

		$router->registerConstraint('foo', FooConstraint::class);

		$router->setConstraintAsGlobal(['foo']);

		$request = $this->getRequest();

		$request->shouldReceive('getMethod')->andReturn('GET');

		$request->shouldReceive('getPath')->andReturn('/foo');

		$router->route($request);
	}

	/**
	 *
	 */
	public function testUnregisteredConstraint(): void
	{
		$this->expectException(RuntimeException::class);

		$this->expectExceptionMessage('No constraint named [ foo ] has been registered.');

		$routes = new Routes;

		$routes->get('/foo', fn() => 'Hello, world!', 'get.foo')->constraint('foo');

		$router = new Router($routes);

		$request = $this->getRequest();

		$request->shouldReceive('getMethod')->andReturn('GET');

		$request->shouldReceive('getPath')->andReturn('/foo');

		$router->route($request);
	}
}
