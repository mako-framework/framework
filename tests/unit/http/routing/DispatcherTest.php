<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\http\routing;

use Closure;
use Mockery;
use PHPUnit_Framework_TestCase;

use mako\http\Request;
use mako\http\Response;
use mako\http\routing\Dispatcher;
use mako\http\routing\middleware\MiddlewareInterface;

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
	protected $response;

	public function __construct(Response $response)
	{
		$this->response = $response;
	}

	public function foo()
	{
		$this->response->header('X-Foo-Bar', 'Foo Bar');

		return 'Hello, world!';
	}

	public function bar($who)
	{
		return 'Hello, ' . $who . '!';
	}

	public function baz(InjectMe $injectMe)
	{
		return $injectMe;
	}
}

class ControllerWithBeforeFilter extends \mako\http\routing\Controller
{
	public function beforeAction()
	{
		return 'Before action';
	}

	public function foo()
	{
		return 'Hello, world!';
	}
}

class ControllerWithNullBeforeFilter extends \mako\http\routing\Controller
{
	protected $response;

	public function __construct(Response $response)
	{
		$this->response = $response;
	}

	public function beforeAction()
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
	protected $response;

	public function __construct(Response $response)
	{
		$this->response = $response;
	}

	public function afterAction()
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

	public function __construct(InjectMe $injectMe)
	{
		$this->injectMe = $injectMe;
	}

	public function foo()
	{
		return $this->injectMe->helloWorld();
	}
}

class FooMiddleware implements MiddlewareInterface
{
	protected $separator;

	public function __construct(string $separator = null)
	{
		$this->separator = $separator ?? '_';
	}

	public function execute(Request $request, Response $response, Closure $next): Response
	{
		return $response->body(str_replace(' ', $this->separator, $next($request, $response)->getBody()));
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
		Mockery::close();
	}

	/**
	 *
	 */
	public function testClosureAction()
	{
		$middleware = Mockery::mock('\mako\http\routing\Middleware');

		$route = Mockery::mock('\mako\http\routing\Route');

		$route->shouldReceive('getAction')->once()->andReturn(function()
		{
			return 'Hello, world!';
		});

		$route->shouldReceive('getMiddleware')->once()->andReturn([]);

		$request = Mockery::mock('\mako\http\Request');

		$response = Mockery::mock('\mako\http\Response')->makePartial();

		$dispatcher = new Dispatcher($request, $response, $middleware, $route);

		$response = $dispatcher->dispatch();

		$this->assertEquals('Hello, world!', $response->getBody());
	}

	/**
	 *
	 */
	public function testClosureActionWithParams()
	{
		$middleware = Mockery::mock('\mako\http\routing\Middleware');

		$route = Mockery::mock('\mako\http\routing\Route');

		$route->shouldReceive('getAction')->once()->andReturn(function(Response $response, $who)
		{
			$response->header('X-Foo-Bar', 'Foo Bar');

			return 'Hello, ' . $who . '!';
		});

		$route->shouldReceive('getMiddleware')->once()->andReturn([]);

		$request = Mockery::mock('\mako\http\Request');

		$response = Mockery::mock('\mako\http\Response')->makePartial();

		$container = Mockery::mock('\mako\syringe\Container')->makePartial();

		$container->shouldReceive('get')->with('mako\http\Response')->andReturn($response);

		$dispatcher = new Dispatcher($request, $response, $middleware, $route, ['who' => 'Kitty'], $container);

		$response = $dispatcher->dispatch();

		$this->assertEquals('Hello, Kitty!', $response->getBody());

		$this->assertEquals(['x-foo-bar' => ['Foo Bar']], $response->getHeaders());
	}

	/**
	 *
	 */
	public function testControllerAction()
	{
		$middleware = Mockery::mock('\mako\http\routing\Middleware');

		$route = Mockery::mock('\mako\http\routing\Route');

		$route->shouldReceive('getAction')->once()->andReturn('mako\tests\unit\http\routing\SimpleController::foo');

		$route->shouldReceive('getMiddleware')->once()->andReturn([]);

		$request = Mockery::mock('\mako\http\Request');

		$response = Mockery::mock('\mako\http\Response')->makePartial();

		$container = Mockery::mock('\mako\syringe\Container')->makePartial();

		$container->shouldReceive('get')->with('mako\http\Response')->andReturn($response);

		$dispatcher = new Dispatcher($request, $response, $middleware, $route, [], $container);

		$response = $dispatcher->dispatch();

		$this->assertEquals('Hello, world!', $response->getBody());

		$this->assertEquals(['x-foo-bar' => ['Foo Bar']], $response->getHeaders());
	}

	/**
	 *
	 */
	public function testControllerActionWithParams()
	{
		$middleware = Mockery::mock('\mako\http\routing\Middleware');

		$route = Mockery::mock('\mako\http\routing\Route');

		$route->shouldReceive('getAction')->once()->andReturn('mako\tests\unit\http\routing\SimpleController::bar');

		$route->shouldReceive('getMiddleware')->once()->andReturn([]);

		$request = Mockery::mock('\mako\http\Request');

		$response = Mockery::mock('\mako\http\Response')->makePartial();

		$container = Mockery::mock('\mako\syringe\Container')->makePartial();

		$container->shouldReceive('get')->with('mako\http\Response')->andReturn($response);

		$dispatcher = new Dispatcher($request, $response, $middleware, $route, ['who' => 'Kitty'], $container);

		$response = $dispatcher->dispatch();

		$this->assertEquals('Hello, Kitty!', $response->getBody());
	}

	/**
	 *
	 */
	public function testControllerWithNullBeforeFilter()
	{
		$middleware = Mockery::mock('\mako\http\routing\Middleware');

		$route = Mockery::mock('\mako\http\routing\Route');

		$route->shouldReceive('getAction')->once()->andReturn('mako\tests\unit\http\routing\ControllerWithNullBeforeFilter::foo');

		$route->shouldReceive('getMiddleware')->once()->andReturn([]);

		$request = Mockery::mock('\mako\http\Request');

		$response = Mockery::mock('\mako\http\Response')->makePartial();

		$container = Mockery::mock('\mako\syringe\Container')->makePartial();

		$container->shouldReceive('get')->with('mako\http\Response')->andReturn($response);

		$dispatcher = new Dispatcher($request, $response, $middleware, $route, [], $container);

		$response = $dispatcher->dispatch();

		$this->assertEquals('Hello, world!', $response->getBody());

		$this->assertEquals(['x-foo-bar' => ['Foo Bar']], $response->getHeaders());
	}

	/**
	 *
	 */
	public function testControllerWithBeforeFilter()
	{
		$middleware = Mockery::mock('\mako\http\routing\Middleware');

		$route = Mockery::mock('\mako\http\routing\Route');

		$route->shouldReceive('getAction')->once()->andReturn('mako\tests\unit\http\routing\ControllerWithBeforeFilter::foo');

		$route->shouldReceive('getMiddleware')->once()->andReturn([]);

		$request = Mockery::mock('\mako\http\Request');

		$response = Mockery::mock('\mako\http\Response')->makePartial();

		$dispatcher = new Dispatcher($request, $response, $middleware, $route);

		$response = $dispatcher->dispatch();

		$this->assertEquals('Before action', $response->getBody());
	}

	/**
	 *
	 */
	public function testControllerActionWithAfterFilter()
	{
		$middleware = Mockery::mock('\mako\http\routing\Middleware');

		$route = Mockery::mock('\mako\http\routing\Route');

		$route->shouldReceive('getAction')->once()->andReturn('mako\tests\unit\http\routing\ControllerWithAfterFilter::foo');

		$route->shouldReceive('getMiddleware')->once()->andReturn([]);

		$request = Mockery::mock('\mako\http\Request');

		$response = Mockery::mock('\mako\http\Response')->makePartial();

		$container = Mockery::mock('\mako\syringe\Container')->makePartial();

		$container->shouldReceive('get')->with('mako\http\Response')->andReturn($response);

		$dispatcher = new Dispatcher($request, $response, $middleware, $route, [], $container);

		$response = $dispatcher->dispatch();

		$this->assertEquals('HELLO, WORLD!', $response->getBody());
	}

	/**
	 * [testMiddleware description]
	 * @return [type] [description]
	 */
	public function testMiddleware()
	{
		$middleware = Mockery::mock('\mako\http\routing\Middleware');

		$middleware->shouldReceive('get')->once()->with('test')->andReturn(FooMiddleware::class);

		$route = Mockery::mock('\mako\http\routing\Route');

		$route->shouldReceive('getMiddleware')->once()->andReturn(['test']);

		$route->shouldReceive('getAction')->once()->andReturn(function()
		{
			return 'hello, world!';
		});

		$request = Mockery::mock('\mako\http\Request');

		$response = Mockery::mock('\mako\http\Response')->makePartial();

		$container = Mockery::mock('\mako\syringe\Container')->makePartial();

		$dispatcher = new Dispatcher($request, $response, $middleware, $route, [], $container);

		$response = $dispatcher->dispatch();

		$this->assertEquals('hello,_world!', $response->getBody());
	}

	/**
	 * [testMiddleware description]
	 * @return [type] [description]
	 */
	public function testMiddlewareWithArguments()
	{
		$middleware = Mockery::mock('\mako\http\routing\Middleware');

		$middleware->shouldReceive('get')->once()->with('test')->andReturn(FooMiddleware::class);

		$route = Mockery::mock('\mako\http\routing\Route');

		$route->shouldReceive('getMiddleware')->once()->andReturn(['test:{"separator":"~"}']);

		$route->shouldReceive('getAction')->once()->andReturn(function()
		{
			return 'hello, world!';
		});

		$request = Mockery::mock('\mako\http\Request');

		$response = Mockery::mock('\mako\http\Response')->makePartial();

		$container = Mockery::mock('\mako\syringe\Container')->makePartial();

		$dispatcher = new Dispatcher($request, $response, $middleware, $route, [], $container);

		$response = $dispatcher->dispatch();

		$this->assertEquals('hello,~world!', $response->getBody());
	}

	/**
	 *
	 */
	public function testControllerInjection()
	{
		$middleware = Mockery::mock('\mako\http\routing\Middleware');

		$route = Mockery::mock('\mako\http\routing\Route');

		$route->shouldReceive('getAction')->once()->andReturn('mako\tests\unit\http\routing\ControllerWithInjection::foo');

		$route->shouldReceive('getMiddleware')->once()->andReturn([]);

		$request = Mockery::mock('\mako\http\Request');

		$response = Mockery::mock('\mako\http\Response')->makePartial();

		$dispatcher = new Dispatcher($request, $response, $middleware, $route);

		$response = $dispatcher->dispatch();

		$this->assertEquals('Hello, world!', $response->getBody());
	}

	/**
	 *
	 */
	public function testClosureWithReversedParameterOrder()
	{
		$middleware = Mockery::mock('\mako\http\routing\Middleware');

		$route = Mockery::mock('\mako\http\routing\Route');

		$route->shouldReceive('getAction')->once()->andReturn(function($world, $hello)
		{
			return $hello . ', ' . $world . '!';
		});

		$route->shouldReceive('getMiddleware')->once()->andReturn([]);

		$request = Mockery::mock('\mako\http\Request');

		$response = Mockery::mock('\mako\http\Response')->makePartial();

		$dispatcher = new Dispatcher($request, $response, $middleware, $route, ['hello' => 'Hello', 'world' => 'world']);

		$response = $dispatcher->dispatch();

		$this->assertEquals('Hello, world!', $response->getBody());
	}

	/**
	 *
	 */
	public function testClosureParameterInjection()
	{
		$middleware = Mockery::mock('\mako\http\routing\Middleware');

		$route = Mockery::mock('\mako\http\routing\Route');

		$route->shouldReceive('getAction')->once()->andReturn(function(Request $request)
		{
			return $request;
		});

		$route->shouldReceive('getMiddleware')->once()->andReturn([]);

		$request = Mockery::mock('\mako\http\Request');

		$response = Mockery::mock('\mako\http\Response')->makePartial();

		$container = Mockery::mock('\mako\syringe\Container')->makePartial();

		$container->shouldReceive('get')->with('mako\http\Request')->andReturn($request);

		$dispatcher = new Dispatcher($request, $response, $middleware, $route, [], $container);

		$response = $dispatcher->dispatch();

		$this->assertInstanceOf('mako\http\Request', $response->getBody());
	}

	/**
	 *
	 */
	public function testControllerActionParameterInjection()
	{
		$middleware = Mockery::mock('\mako\http\routing\Middleware');

		$route = Mockery::mock('\mako\http\routing\Route');

		$route->shouldReceive('getAction')->once()->andReturn('mako\tests\unit\http\routing\SimpleController::baz');

		$route->shouldReceive('getMiddleware')->once()->andReturn([]);

		$request = Mockery::mock('\mako\http\Request');

		$response = Mockery::mock('\mako\http\Response')->makePartial();

		$container = Mockery::mock('\mako\syringe\Container')->makePartial();

		$container->shouldReceive('get')->with('mako\http\Response')->andReturn($response);

		$dispatcher = new Dispatcher($request, $response, $middleware, $route, [], $container);

		$response = $dispatcher->dispatch();

		$this->assertInstanceOf('mako\tests\unit\http\routing\InjectMe', $response->getBody());
	}
}
