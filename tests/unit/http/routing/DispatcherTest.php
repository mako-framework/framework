<?php

use \mako\http\routing\Dispatcher;

use Mockery as m;

// --------------------------------------------------------------------------
// START CLASSES
// --------------------------------------------------------------------------

class DispatcherTestInjectMe
{
	public function helloWorld()
	{
		return 'Hello, world!';
	}
}

class DispatcherTestSimpleController extends \mako\http\routing\Controller
{
	public function foo()
	{
		$this->response->header('X-Foo-Bar', 'Foo Bar');

		return 'Hello, world!';
	}

	public function bar($who)
	{
		return 'Hello, ' . $who . '!';
	}
}

class DispatcherTestControllerWithBeforeFilter extends \mako\http\routing\Controller
{
	public function beforeFilter()
	{
		return 'Before filter';
	}

	public function foo()
	{
		return 'Hello, world!';
	}
}

class DispatcherTestControllerWithNullBeforeFilter extends \mako\http\routing\Controller
{
	public function beforeFilter()
	{
		$this->response->header('X-Foo-Bar', 'Foo Bar');
	}

	public function foo()
	{
		return 'Hello, world!';
	}
}

class DispatcherTestControllerWithAfterFilter extends \mako\http\routing\Controller
{
	public function afterFilter()
	{
		$this->response->body(strtoupper($this->response->getBody()));
	}

	public function foo()
	{
		return 'Hello, world!';
	}
}

class DispatcherTestControllerWithInjection extends \mako\http\routing\Controller
{
	protected $injectMe;

	public function __construct($request, $response, DispatcherTestInjectMe $injectMe)
	{
		parent::__construct($request, $response);

		$this->injectMe = $injectMe;
	}

	public function foo()
	{
		return $this->injectMe->helloWorld();
	}
}

// --------------------------------------------------------------------------
// END CLASSES
// --------------------------------------------------------------------------

/**
 * @group unit
 */

class DispatcherTest extends PHPUnit_Framework_TestCase
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

	public function testClosureAction()
	{
		$routes = m::mock('\mako\http\routing\Routes');

		$route = m::mock('\mako\http\routing\Route');

		$route->shouldReceive('getHeaders')->once()->andReturn([]);

		$route->shouldReceive('getAction')->once()->andReturn(function()
		{
			return 'Hello, world!';
		});

		$route->shouldReceive('getBeforeFilters')->once()->andReturn([]);

		$route->shouldReceive('getAfterFilters')->once()->andReturn([]);

		$route->shouldReceive('getParameters')->once()->andReturn([]);

		$request = m::mock('\mako\http\Request');

		$request->shouldReceive('method')->once()->andReturn('GET');

		$response = m::mock('\mako\http\Response')->makePartial();

		$dispatcher = new Dispatcher($routes, $route, $request, $response);

		$response = $dispatcher->dispatch();

		$this->assertEquals('Hello, world!', $response->getBody());
	}

	/**
	 * 
	 */

	public function testClosureActionWithParams()
	{
		$routes = m::mock('\mako\http\routing\Routes');

		$route = m::mock('\mako\http\routing\Route');

		$route->shouldReceive('getHeaders')->once()->andReturn([]);

		$route->shouldReceive('getAction')->once()->andReturn(function($request, $response, $who)
		{
			$response->header('X-Foo-Bar', 'Foo Bar');

			return 'Hello, ' . $who . '!';
		});

		$route->shouldReceive('getBeforeFilters')->once()->andReturn([]);

		$route->shouldReceive('getAfterFilters')->once()->andReturn([]);

		$route->shouldReceive('getParameters')->once()->andReturn(['who' => 'Kitty']);

		$request = m::mock('\mako\http\Request');

		$request->shouldReceive('method')->once()->andReturn('GET');

		$response = m::mock('\mako\http\Response')->makePartial();

		$dispatcher = new Dispatcher($routes, $route, $request, $response);

		$response = $dispatcher->dispatch();

		$this->assertEquals('Hello, Kitty!', $response->getBody());

		$this->assertEquals(['x-foo-bar' => 'Foo Bar'], $response->getHeaders());
	}

	/**
	 * 
	 */

	public function testControllerAction()
	{
		$routes = m::mock('\mako\http\routing\Routes');

		$route = m::mock('\mako\http\routing\Route');

		$route->shouldReceive('getHeaders')->once()->andReturn([]);

		$route->shouldReceive('getAction')->once()->andReturn('DispatcherTestSimpleController::foo');

		$route->shouldReceive('getBeforeFilters')->once()->andReturn([]);

		$route->shouldReceive('getAfterFilters')->once()->andReturn([]);

		$route->shouldReceive('getParameters')->once()->andReturn([]);

		$request = m::mock('\mako\http\Request');

		$request->shouldReceive('method')->once()->andReturn('GET');

		$response = m::mock('\mako\http\Response')->makePartial();

		$dispatcher = new Dispatcher($routes, $route, $request, $response);

		$response = $dispatcher->dispatch();

		$this->assertEquals('Hello, world!', $response->getBody());

		$this->assertEquals(['x-foo-bar' => 'Foo Bar'], $response->getHeaders());
	}

	/**
	 * 
	 */

	public function testControllerActionWithParams()
	{
		$routes = m::mock('\mako\http\routing\Routes');

		$route = m::mock('\mako\http\routing\Route');

		$route->shouldReceive('getHeaders')->once()->andReturn([]);

		$route->shouldReceive('getAction')->once()->andReturn('DispatcherTestSimpleController::bar');

		$route->shouldReceive('getBeforeFilters')->once()->andReturn([]);

		$route->shouldReceive('getAfterFilters')->once()->andReturn([]);

		$route->shouldReceive('getParameters')->once()->andReturn(['who' => 'Kitty']);

		$request = m::mock('\mako\http\Request');

		$request->shouldReceive('method')->once()->andReturn('GET');

		$response = m::mock('\mako\http\Response')->makePartial();

		$dispatcher = new Dispatcher($routes, $route, $request, $response);

		$response = $dispatcher->dispatch();

		$this->assertEquals('Hello, Kitty!', $response->getBody());
	}

	/**
	 * 
	 */

	public function testControllerWithNullBeforeFilter()
	{
		$routes = m::mock('\mako\http\routing\Routes');

		$route = m::mock('\mako\http\routing\Route');

		$route->shouldReceive('getHeaders')->once()->andReturn([]);

		$route->shouldReceive('getAction')->once()->andReturn('DispatcherTestControllerWithNullBeforeFilter::foo');

		$route->shouldReceive('getBeforeFilters')->once()->andReturn([]);

		$route->shouldReceive('getAfterFilters')->once()->andReturn([]);

		$route->shouldReceive('getParameters')->once()->andReturn([]);

		$request = m::mock('\mako\http\Request');

		$request->shouldReceive('method')->once()->andReturn('GET');

		$response = m::mock('\mako\http\Response')->makePartial();

		$dispatcher = new Dispatcher($routes, $route, $request, $response);

		$response = $dispatcher->dispatch();

		$this->assertEquals('Hello, world!', $response->getBody());

		$this->assertEquals(['x-foo-bar' => 'Foo Bar'], $response->getHeaders());
	}

	/**
	 * 
	 */

	public function testControllerWithBeforeFilter()
	{
		$routes = m::mock('\mako\http\routing\Routes');

		$route = m::mock('\mako\http\routing\Route');

		$route->shouldReceive('getHeaders')->once()->andReturn([]);

		$route->shouldReceive('getAction')->once()->andReturn('DispatcherTestControllerWithBeforeFilter::foo');

		$route->shouldReceive('getBeforeFilters')->once()->andReturn([]);

		$request = m::mock('\mako\http\Request');

		$request->shouldReceive('method')->once()->andReturn('GET');

		$response = m::mock('\mako\http\Response')->makePartial();

		$dispatcher = new Dispatcher($routes, $route, $request, $response);

		$response = $dispatcher->dispatch();

		$this->assertEquals('Before filter', $response->getBody());
	}

	/**
	 * 
	 */

	public function testControllerActionWithAfterFilter()
	{
		$routes = m::mock('\mako\http\routing\Routes');

		$route = m::mock('\mako\http\routing\Route');

		$route->shouldReceive('getHeaders')->once()->andReturn([]);

		$route->shouldReceive('getAction')->once()->andReturn('DispatcherTestControllerWithAfterFilter::foo');

		$route->shouldReceive('getBeforeFilters')->once()->andReturn([]);

		$route->shouldReceive('getAfterFilters')->once()->andReturn([]);

		$route->shouldReceive('getParameters')->once()->andReturn([]);

		$request = m::mock('\mako\http\Request');

		$request->shouldReceive('method')->once()->andReturn('GET');

		$response = m::mock('\mako\http\Response')->makePartial();

		$dispatcher = new Dispatcher($routes, $route, $request, $response);

		$response = $dispatcher->dispatch();

		$this->assertEquals('HELLO, WORLD!', $response->getBody());
	}

	/**
	 * 
	 */

	public function testRouteNullBeforeFilter()
	{
		$routes = m::mock('\mako\http\routing\Routes');

		$routes->shouldReceive('getFilter')->once()->with('before')->andReturn(function($request, $response)
		{
			$response->header('X-Foo-Bar', 'Foo Bar');
		});

		$route = m::mock('\mako\http\routing\Route');

		$route->shouldReceive('getHeaders')->once()->andReturn([]);

		$route->shouldReceive('getAction')->once()->andReturn(function()
		{
			return 'Hello, world!';
		});

		$route->shouldReceive('getBeforeFilters')->once()->andReturn(['before']);

		$route->shouldReceive('getAfterFilters')->once()->andReturn([]);

		$route->shouldReceive('getParameters')->once()->andReturn([]);

		$request = m::mock('\mako\http\Request');

		$request->shouldReceive('method')->once()->andReturn('GET');

		$response = m::mock('\mako\http\Response')->makePartial();

		$dispatcher = new Dispatcher($routes, $route, $request, $response);

		$response = $dispatcher->dispatch();

		$this->assertEquals('Hello, world!', $response->getBody());

		$this->assertEquals(['x-foo-bar' => 'Foo Bar'], $response->getHeaders());
	}

	/**
	 * 
	 */

	public function testRouteBeforeFilter()
	{
		$routes = m::mock('\mako\http\routing\Routes');

		$routes->shouldReceive('getFilter')->once()->with('before')->andReturn(function()
		{
			return 'Before filter';
		});

		$route = m::mock('\mako\http\routing\Route');

		$route->shouldReceive('getHeaders')->once()->andReturn([]);

		$route->shouldReceive('getBeforeFilters')->once()->andReturn(['before']);

		$request = m::mock('\mako\http\Request');

		$request->shouldReceive('method')->once()->andReturn('GET');

		$response = m::mock('\mako\http\Response')->makePartial();

		$dispatcher = new Dispatcher($routes, $route, $request, $response);

		$response = $dispatcher->dispatch();

		$this->assertEquals('Before filter', $response->getBody());
	}

	/**
	 * 
	 */

	public function testRouteAfterFilter()
	{
		$routes = m::mock('\mako\http\routing\Routes');

		$routes->shouldReceive('getFilter')->once()->with('after')->andReturn(function($request, $response)
		{
			$response->body(strtoupper($response->getBody()));
		});

		$route = m::mock('\mako\http\routing\Route');

		$route->shouldReceive('getHeaders')->once()->andReturn([]);

		$route->shouldReceive('getAction')->once()->andReturn(function()
		{
			return 'Hello, world!';
		});

		$route->shouldReceive('getBeforeFilters')->once()->andReturn([]);

		$route->shouldReceive('getAfterFilters')->once()->andReturn(['after']);

		$route->shouldReceive('getParameters')->once()->andReturn([]);

		$request = m::mock('\mako\http\Request');

		$request->shouldReceive('method')->once()->andReturn('GET');

		$response = m::mock('\mako\http\Response')->makePartial();

		$dispatcher = new Dispatcher($routes, $route, $request, $response);

		$response = $dispatcher->dispatch();

		$this->assertEquals('HELLO, WORLD!', $response->getBody());
	}

	/**
	 * 
	 */

	public function testRouteHeaders()
	{
		$routes = m::mock('\mako\http\routing\Routes');

		$route = m::mock('\mako\http\routing\Route');

		$route->shouldReceive('getHeaders')->once()->andReturn(['X-Foo-Bar' => 'Foo Bar']);

		$route->shouldReceive('getAction')->once()->andReturn(function()
		{
			return 'Hello, world!';
		});

		$route->shouldReceive('getBeforeFilters')->once()->andReturn([]);

		$route->shouldReceive('getAfterFilters')->once()->andReturn([]);

		$route->shouldReceive('getParameters')->once()->andReturn([]);

		$request = m::mock('\mako\http\Request');

		$request->shouldReceive('method')->once()->andReturn('GET');

		$response = m::mock('\mako\http\Response')->makePartial();

		$dispatcher = new Dispatcher($routes, $route, $request, $response);

		$response = $dispatcher->dispatch();

		$this->assertEquals('Hello, world!', $response->getBody());

		$this->assertEquals(['x-foo-bar' => 'Foo Bar'], $response->getHeaders());
	}

	/**
	 * 
	 */

	public function testOptionsRequest()
	{
		$routes = m::mock('\mako\http\routing\Routes');

		$route = m::mock('\mako\http\routing\Route');

		$route->shouldReceive('getHeaders')->once()->andReturn(['allow' => 'POST,OPTIONS']);

		$request = m::mock('\mako\http\Request');

		$request->shouldReceive('method')->once()->andReturn('OPTIONS');

		$response = m::mock('\mako\http\Response')->makePartial();

		$dispatcher = new Dispatcher($routes, $route, $request, $response);

		$response = $dispatcher->dispatch();

		$this->assertEmpty($response->getBody());

		$this->assertEquals(['allow' => 'POST,OPTIONS'], $response->getHeaders());
	}

	/**
	 * 
	 */

	public function testControllerInjection()
	{
		$routes = m::mock('\mako\http\routing\Routes');

		$route = m::mock('\mako\http\routing\Route');

		$route->shouldReceive('getHeaders')->once()->andReturn([]);

		$route->shouldReceive('getAction')->once()->andReturn('DispatcherTestControllerWithInjection::foo');

		$route->shouldReceive('getBeforeFilters')->once()->andReturn([]);

		$route->shouldReceive('getAfterFilters')->once()->andReturn([]);

		$route->shouldReceive('getParameters')->once()->andReturn([]);

		$request = m::mock('\mako\http\Request');

		$request->shouldReceive('method')->once()->andReturn('GET');

		$response = m::mock('\mako\http\Response')->makePartial();

		$dispatcher = new Dispatcher($routes, $route, $request, $response);

		$response = $dispatcher->dispatch();

		$this->assertEquals('Hello, world!', $response->getBody());
	}
}