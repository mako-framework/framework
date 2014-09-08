<?php

namespace mako\tests\unit\http\routing;

use \mako\http\routing\Dispatcher;

use \Mockery as m;

// --------------------------------------------------------------------------
// START CLASSES
// --------------------------------------------------------------------------

class InjectMe
{
	public function helloWorld()
	{
		return 'Hello, world!';
	}
}

class SimpleController extends \mako\http\routing\Controller
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

class ControllerWithBeforeFilter extends \mako\http\routing\Controller
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

class ControllerWithNullBeforeFilter extends \mako\http\routing\Controller
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

class ControllerWithAfterFilter extends \mako\http\routing\Controller
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

class ControllerWithInjection extends \mako\http\routing\Controller
{
	protected $injectMe;

	public function __construct($request, $response, InjectMe $injectMe)
	{
		parent::__construct($request, $response);

		$this->injectMe = $injectMe;
	}

	public function foo()
	{
		return $this->injectMe->helloWorld();
	}
}

class FooFilter
{
	public function filter()
	{
		return 'Foo class filter';
	}
}

// --------------------------------------------------------------------------
// END CLASSES
// --------------------------------------------------------------------------

/**
 * @group unit
 */

class DispatcherTest extends \PHPUnit_Framework_TestCase
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
		$filters = m::mock('\mako\http\routing\Filters');

		$route = m::mock('\mako\http\routing\Route');

		$route->shouldReceive('getHeaders')->once()->andReturn([]);

		$route->shouldReceive('getAction')->once()->andReturn(function()
		{
			return 'Hello, world!';
		});

		$route->shouldReceive('getBeforeFilters')->once()->andReturn([]);

		$route->shouldReceive('getAfterFilters')->once()->andReturn([]);

		$request = m::mock('\mako\http\Request');

		$request->shouldReceive('method')->once()->andReturn('GET');

		$response = m::mock('\mako\http\Response')->makePartial();

		$dispatcher = new Dispatcher($request, $response, $filters, $route);

		$response = $dispatcher->dispatch();

		$this->assertEquals('Hello, world!', $response->getBody());
	}

	/**
	 * 
	 */

	public function testClosureActionWithParams()
	{
		$filters = m::mock('\mako\http\routing\Filters');

		$route = m::mock('\mako\http\routing\Route');

		$route->shouldReceive('getHeaders')->once()->andReturn([]);

		$route->shouldReceive('getAction')->once()->andReturn(function($request, $response, $who)
		{
			$response->header('X-Foo-Bar', 'Foo Bar');

			return 'Hello, ' . $who . '!';
		});

		$route->shouldReceive('getBeforeFilters')->once()->andReturn([]);

		$route->shouldReceive('getAfterFilters')->once()->andReturn([]);

		$request = m::mock('\mako\http\Request');

		$request->shouldReceive('method')->once()->andReturn('GET');

		$response = m::mock('\mako\http\Response')->makePartial();

		$dispatcher = new Dispatcher($request, $response, $filters, $route, ['who' => 'Kitty']);

		$response = $dispatcher->dispatch();

		$this->assertEquals('Hello, Kitty!', $response->getBody());

		$this->assertEquals(['x-foo-bar' => 'Foo Bar'], $response->getHeaders());
	}

	/**
	 * 
	 */

	public function testControllerAction()
	{
		$filters = m::mock('\mako\http\routing\Filters');

		$route = m::mock('\mako\http\routing\Route');

		$route->shouldReceive('getHeaders')->once()->andReturn([]);

		$route->shouldReceive('getAction')->once()->andReturn('mako\tests\unit\http\routing\SimpleController::foo');

		$route->shouldReceive('getBeforeFilters')->once()->andReturn([]);

		$route->shouldReceive('getAfterFilters')->once()->andReturn([]);

		$request = m::mock('\mako\http\Request');

		$request->shouldReceive('method')->once()->andReturn('GET');

		$response = m::mock('\mako\http\Response')->makePartial();

		$dispatcher = new Dispatcher($request, $response, $filters, $route);

		$response = $dispatcher->dispatch();

		$this->assertEquals('Hello, world!', $response->getBody());

		$this->assertEquals(['x-foo-bar' => 'Foo Bar'], $response->getHeaders());
	}

	/**
	 * 
	 */

	public function testControllerActionWithParams()
	{
		$filters = m::mock('\mako\http\routing\Filters');

		$route = m::mock('\mako\http\routing\Route');

		$route->shouldReceive('getHeaders')->once()->andReturn([]);

		$route->shouldReceive('getAction')->once()->andReturn('mako\tests\unit\http\routing\SimpleController::bar');

		$route->shouldReceive('getBeforeFilters')->once()->andReturn([]);

		$route->shouldReceive('getAfterFilters')->once()->andReturn([]);

		$request = m::mock('\mako\http\Request');

		$request->shouldReceive('method')->once()->andReturn('GET');

		$response = m::mock('\mako\http\Response')->makePartial();

		$dispatcher = new Dispatcher($request, $response, $filters, $route, ['who' => 'Kitty']);

		$response = $dispatcher->dispatch();

		$this->assertEquals('Hello, Kitty!', $response->getBody());
	}

	/**
	 * 
	 */

	public function testControllerWithNullBeforeFilter()
	{
		$filters = m::mock('\mako\http\routing\Filters');

		$route = m::mock('\mako\http\routing\Route');

		$route->shouldReceive('getHeaders')->once()->andReturn([]);

		$route->shouldReceive('getAction')->once()->andReturn('mako\tests\unit\http\routing\ControllerWithNullBeforeFilter::foo');

		$route->shouldReceive('getBeforeFilters')->once()->andReturn([]);

		$route->shouldReceive('getAfterFilters')->once()->andReturn([]);

		$request = m::mock('\mako\http\Request');

		$request->shouldReceive('method')->once()->andReturn('GET');

		$response = m::mock('\mako\http\Response')->makePartial();

		$dispatcher = new Dispatcher($request, $response, $filters, $route);

		$response = $dispatcher->dispatch();

		$this->assertEquals('Hello, world!', $response->getBody());

		$this->assertEquals(['x-foo-bar' => 'Foo Bar'], $response->getHeaders());
	}

	/**
	 * 
	 */

	public function testControllerWithBeforeFilter()
	{
		$filters = m::mock('\mako\http\routing\Filters');

		$route = m::mock('\mako\http\routing\Route');

		$route->shouldReceive('getHeaders')->once()->andReturn([]);

		$route->shouldReceive('getAction')->once()->andReturn('mako\tests\unit\http\routing\ControllerWithBeforeFilter::foo');

		$route->shouldReceive('getBeforeFilters')->once()->andReturn([]);

		$request = m::mock('\mako\http\Request');

		$request->shouldReceive('method')->once()->andReturn('GET');

		$response = m::mock('\mako\http\Response')->makePartial();

		$dispatcher = new Dispatcher($request, $response, $filters, $route);

		$response = $dispatcher->dispatch();

		$this->assertEquals('Before filter', $response->getBody());
	}

	/**
	 * 
	 */

	public function testControllerActionWithAfterFilter()
	{
		$filters = m::mock('\mako\http\routing\Filters');

		$route = m::mock('\mako\http\routing\Route');

		$route->shouldReceive('getHeaders')->once()->andReturn([]);

		$route->shouldReceive('getAction')->once()->andReturn('mako\tests\unit\http\routing\ControllerWithAfterFilter::foo');

		$route->shouldReceive('getBeforeFilters')->once()->andReturn([]);

		$route->shouldReceive('getAfterFilters')->once()->andReturn([]);

		$request = m::mock('\mako\http\Request');

		$request->shouldReceive('method')->once()->andReturn('GET');

		$response = m::mock('\mako\http\Response')->makePartial();

		$dispatcher = new Dispatcher($request, $response, $filters, $route);

		$response = $dispatcher->dispatch();

		$this->assertEquals('HELLO, WORLD!', $response->getBody());
	}

	/**
	 * 
	 */

	public function testRouteNullBeforeFilter()
	{
		$filters = m::mock('\mako\http\routing\Filters');

		$filters->shouldReceive('get')->once()->with('before')->andReturn(function($request, $response)
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

		$request = m::mock('\mako\http\Request');

		$request->shouldReceive('method')->once()->andReturn('GET');

		$response = m::mock('\mako\http\Response')->makePartial();

		$dispatcher = new Dispatcher($request, $response, $filters, $route);

		$response = $dispatcher->dispatch();

		$this->assertEquals('Hello, world!', $response->getBody());

		$this->assertEquals(['x-foo-bar' => 'Foo Bar'], $response->getHeaders());
	}

	/**
	 * 
	 */

	public function testRouteBeforeFilter()
	{
		$filters = m::mock('\mako\http\routing\Filters');

		$filters->shouldReceive('get')->once()->with('before')->andReturn(function()
		{
			return 'Before filter';
		});

		$route = m::mock('\mako\http\routing\Route');

		$route->shouldReceive('getHeaders')->once()->andReturn([]);

		$route->shouldReceive('getBeforeFilters')->once()->andReturn(['before']);

		$request = m::mock('\mako\http\Request');

		$request->shouldReceive('method')->once()->andReturn('GET');

		$response = m::mock('\mako\http\Response')->makePartial();

		$dispatcher = new Dispatcher($request, $response, $filters, $route);

		$response = $dispatcher->dispatch();

		$this->assertEquals('Before filter', $response->getBody());
	}

	/**
	 * 
	 */

	public function testRouteAfterFilter()
	{
		$filters = m::mock('\mako\http\routing\Filters');

		$filters->shouldReceive('get')->once()->with('after')->andReturn(function($request, $response)
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

		$request = m::mock('\mako\http\Request');

		$request->shouldReceive('method')->once()->andReturn('GET');

		$response = m::mock('\mako\http\Response')->makePartial();

		$dispatcher = new Dispatcher($request, $response, $filters, $route);

		$response = $dispatcher->dispatch();

		$this->assertEquals('HELLO, WORLD!', $response->getBody());
	}

	/**
	 * 
	 */

	public function testFilterParam()
	{
		$filters = m::mock('\mako\http\routing\Filters');

		$filters->shouldReceive('get')->once()->with('before')->andReturn(function($request, $response, $param)
		{
			return $param;
		});

		$route = m::mock('\mako\http\routing\Route');

		$route->shouldReceive('getHeaders')->once()->andReturn([]);

		$route->shouldReceive('getBeforeFilters')->once()->andReturn(['before[foobar]']);

		$request = m::mock('\mako\http\Request');

		$request->shouldReceive('method')->once()->andReturn('GET');

		$response = m::mock('\mako\http\Response')->makePartial();

		$dispatcher = new Dispatcher($request, $response, $filters, $route);

		$response = $dispatcher->dispatch();

		$this->assertEquals('foobar', $response->getBody());
	}

	/**
	 * 
	 */

	public function testFilterParams()
	{
		$filters = m::mock('\mako\http\routing\Filters');

		$filters->shouldReceive('get')->once()->with('before')->andReturn(function($request, $response, $param1, $param2)
		{
			return $param1 . $param2;
		});

		$route = m::mock('\mako\http\routing\Route');

		$route->shouldReceive('getHeaders')->once()->andReturn([]);

		$route->shouldReceive('getBeforeFilters')->once()->andReturn(['before[foobar,bazbax]']);

		$request = m::mock('\mako\http\Request');

		$request->shouldReceive('method')->once()->andReturn('GET');

		$response = m::mock('\mako\http\Response')->makePartial();

		$dispatcher = new Dispatcher($request, $response, $filters, $route);

		$response = $dispatcher->dispatch();

		$this->assertEquals('foobarbazbax', $response->getBody());
	}

	/**
	 * 
	 */

	public function testRouteHeaders()
	{
		$filters = m::mock('\mako\http\routing\Filters');

		$route = m::mock('\mako\http\routing\Route');

		$route->shouldReceive('getHeaders')->once()->andReturn(['X-Foo-Bar' => 'Foo Bar']);

		$route->shouldReceive('getAction')->once()->andReturn(function()
		{
			return 'Hello, world!';
		});

		$route->shouldReceive('getBeforeFilters')->once()->andReturn([]);

		$route->shouldReceive('getAfterFilters')->once()->andReturn([]);

		$request = m::mock('\mako\http\Request');

		$request->shouldReceive('method')->once()->andReturn('GET');

		$response = m::mock('\mako\http\Response')->makePartial();

		$dispatcher = new Dispatcher($request, $response, $filters, $route);

		$response = $dispatcher->dispatch();

		$this->assertEquals('Hello, world!', $response->getBody());

		$this->assertEquals(['x-foo-bar' => 'Foo Bar'], $response->getHeaders());
	}

	/**
	 * 
	 */

	public function _testOptionsRequest()
	{
		$filters = m::mock('\mako\http\routing\Filters');

		$route = m::mock('\mako\http\routing\Route');

		$route->shouldReceive('getHeaders')->once()->andReturn(['allow' => 'POST,OPTIONS']);

		$request = m::mock('\mako\http\Request');

		$request->shouldReceive('method')->once()->andReturn('OPTIONS');

		$response = m::mock('\mako\http\Response')->makePartial();

		$dispatcher = new Dispatcher($request, $response, $filters, $route);

		$response = $dispatcher->dispatch();

		$this->assertEmpty($response->getBody());

		$this->assertEquals(['allow' => 'POST,OPTIONS'], $response->getHeaders());
	}

	/**
	 * 
	 */

	public function testControllerInjection()
	{
		$filters = m::mock('\mako\http\routing\Filters');

		$route = m::mock('\mako\http\routing\Route');

		$route->shouldReceive('getHeaders')->once()->andReturn([]);

		$route->shouldReceive('getAction')->once()->andReturn('mako\tests\unit\http\routing\ControllerWithInjection::foo');

		$route->shouldReceive('getBeforeFilters')->once()->andReturn([]);

		$route->shouldReceive('getAfterFilters')->once()->andReturn([]);

		$request = m::mock('\mako\http\Request');

		$request->shouldReceive('method')->once()->andReturn('GET');

		$response = m::mock('\mako\http\Response')->makePartial();

		$dispatcher = new Dispatcher($request, $response, $filters, $route);

		$response = $dispatcher->dispatch();

		$this->assertEquals('Hello, world!', $response->getBody());
	}

	/**
	 * 
	 */

	public function testClassFilter()
	{
		$filters = m::mock('\mako\http\routing\Filters');

		$filters->shouldReceive('get')->once()->with('before')->andReturn('mako\tests\unit\http\routing\FooFilter');

		$route = m::mock('\mako\http\routing\Route');

		$route->shouldReceive('getHeaders')->once()->andReturn([]);

		$route->shouldReceive('getBeforeFilters')->once()->andReturn(['before']);

		$request = m::mock('\mako\http\Request');

		$request->shouldReceive('method')->once()->andReturn('GET');

		$response = m::mock('\mako\http\Response')->makePartial();

		$dispatcher = new Dispatcher($request, $response, $filters, $route);

		$response = $dispatcher->dispatch();

		$this->assertEquals('Foo class filter', $response->getBody());
	}
}