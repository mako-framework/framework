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
use mako\http\routing\middleware\Middleware;

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

class FooMiddleware extends Middleware
{
	public function execute(Request $request, Response $response, Closure $next): Response
	{
		return $response->body(str_replace(' ', $this->getParameter('separator', '_'), $next($request, $response)->getBody()));
	}
}

class BarMiddleware extends Middleware
{
	public function execute(Request $request, Response $response, Closure $next): Response
	{
		return $response->body(str_replace(' ', $this->getParameter(0, '_'), $next($request, $response)->getBody()));
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

		$route->shouldReceive('getParameters')->once()->andReturn([]);

		$route->shouldReceive('getMiddleware')->once()->andReturn([]);

		$request = Mockery::mock('\mako\http\Request');

		$response = Mockery::mock('\mako\http\Response')->makePartial();

		$dispatcher = new Dispatcher($request, $response, $middleware);

		$response = $dispatcher->dispatch($route);

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

		$route->shouldReceive('getParameters')->once()->andReturn(['who' => 'Kitty']);

		$route->shouldReceive('getMiddleware')->once()->andReturn([]);

		$request = Mockery::mock('\mako\http\Request');

		$response = Mockery::mock('\mako\http\Response')->makePartial();

		$container = Mockery::mock('\mako\syringe\Container')->makePartial();

		$container->shouldReceive('get')->with('mako\http\Response')->andReturn($response);

		$dispatcher = new Dispatcher($request, $response, $middleware, $container);

		$response = $dispatcher->dispatch($route);

		$this->assertEquals('Hello, Kitty!', $response->getBody());

		$this->assertEquals(['X-Foo-Bar' => ['Foo Bar']], $response->getHeaders());
	}

	/**
	 *
	 */
	public function testControllerAction()
	{
		$middleware = Mockery::mock('\mako\http\routing\Middleware');

		$route = Mockery::mock('\mako\http\routing\Route');

		$route->shouldReceive('getAction')->once()->andReturn('mako\tests\unit\http\routing\SimpleController::foo');

		$route->shouldReceive('getParameters')->once()->andReturn([]);

		$route->shouldReceive('getMiddleware')->once()->andReturn([]);

		$request = Mockery::mock('\mako\http\Request');

		$response = Mockery::mock('\mako\http\Response')->makePartial();

		$container = Mockery::mock('\mako\syringe\Container')->makePartial();

		$container->shouldReceive('get')->with('mako\http\Response')->andReturn($response);

		$dispatcher = new Dispatcher($request, $response, $middleware, $container);

		$response = $dispatcher->dispatch($route);

		$this->assertEquals('Hello, world!', $response->getBody());

		$this->assertEquals(['X-Foo-Bar' => ['Foo Bar']], $response->getHeaders());
	}

	/**
	 *
	 */
	public function testControllerActionWithParams()
	{
		$middleware = Mockery::mock('\mako\http\routing\Middleware');

		$route = Mockery::mock('\mako\http\routing\Route');

		$route->shouldReceive('getAction')->once()->andReturn('mako\tests\unit\http\routing\SimpleController::bar');

		$route->shouldReceive('getParameters')->once()->andReturn(['who' => 'Kitty']);

		$route->shouldReceive('getMiddleware')->once()->andReturn([]);

		$request = Mockery::mock('\mako\http\Request');

		$response = Mockery::mock('\mako\http\Response')->makePartial();

		$container = Mockery::mock('\mako\syringe\Container')->makePartial();

		$container->shouldReceive('get')->with('mako\http\Response')->andReturn($response);

		$dispatcher = new Dispatcher($request, $response, $middleware, $container);

		$response = $dispatcher->dispatch($route);

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

		$route->shouldReceive('getParameters')->once()->andReturn([]);

		$route->shouldReceive('getMiddleware')->once()->andReturn([]);

		$request = Mockery::mock('\mako\http\Request');

		$response = Mockery::mock('\mako\http\Response')->makePartial();

		$container = Mockery::mock('\mako\syringe\Container')->makePartial();

		$container->shouldReceive('get')->with('mako\http\Response')->andReturn($response);

		$dispatcher = new Dispatcher($request, $response, $middleware, $container);

		$response = $dispatcher->dispatch($route);

		$this->assertEquals('Hello, world!', $response->getBody());

		$this->assertEquals(['X-Foo-Bar' => ['Foo Bar']], $response->getHeaders());
	}

	/**
	 *
	 */
	public function testControllerWithBeforeFilter()
	{
		$middleware = Mockery::mock('\mako\http\routing\Middleware');

		$route = Mockery::mock('\mako\http\routing\Route');

		$route->shouldReceive('getAction')->once()->andReturn('mako\tests\unit\http\routing\ControllerWithBeforeFilter::foo');

		$route->shouldReceive('getParameters')->once()->andReturn([]);

		$route->shouldReceive('getMiddleware')->once()->andReturn([]);

		$request = Mockery::mock('\mako\http\Request');

		$response = Mockery::mock('\mako\http\Response')->makePartial();

		$dispatcher = new Dispatcher($request, $response, $middleware);

		$response = $dispatcher->dispatch($route);

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

		$route->shouldReceive('getParameters')->once()->andReturn([]);

		$route->shouldReceive('getMiddleware')->once()->andReturn([]);

		$request = Mockery::mock('\mako\http\Request');

		$response = Mockery::mock('\mako\http\Response')->makePartial();

		$container = Mockery::mock('\mako\syringe\Container')->makePartial();

		$container->shouldReceive('get')->with('mako\http\Response')->andReturn($response);

		$dispatcher = new Dispatcher($request, $response, $middleware, $container);

		$response = $dispatcher->dispatch($route);

		$this->assertEquals('HELLO, WORLD!', $response->getBody());
	}

	/**
	 *
	 */
	public function testMiddleware()
	{
		$middleware = Mockery::mock('\mako\http\routing\Middleware');

		$middleware->shouldReceive('get')->once()->with('test')->andReturn(FooMiddleware::class);

		$middleware->shouldReceive('orderByPriority')->once()
		->with(['test' => [['name' => 'test', 'middleware' => FooMiddleware::class, 'parameters' => []]]])
		->andReturn(['test' => [['name' => 'test', 'middleware' => FooMiddleware::class, 'parameters' => []]]]);

		$route = Mockery::mock('\mako\http\routing\Route');

		$route->shouldReceive('getMiddleware')->once()->andReturn(['test']);

		$route->shouldReceive('getAction')->once()->andReturn(function()
		{
			return 'hello, world!';
		});

		$route->shouldReceive('getParameters')->once()->andReturn([]);

		$request = Mockery::mock('\mako\http\Request');

		$response = Mockery::mock('\mako\http\Response')->makePartial();

		$container = Mockery::mock('\mako\syringe\Container')->makePartial();

		$dispatcher = new Dispatcher($request, $response, $middleware, $container);

		$response = $dispatcher->dispatch($route);

		$this->assertEquals('hello,_world!', $response->getBody());
	}

	/**
	 *
	 */
	public function testMiddlewareWithUnnamedArguments()
	{
		$middleware = Mockery::mock('\mako\http\routing\Middleware');

		$middleware->shouldReceive('get')->once()->with('test')->andReturn(BarMiddleware::class);

		$middleware->shouldReceive('orderByPriority')->once()
		->with(['test' => [['name' => 'test', 'middleware' => BarMiddleware::class, 'parameters' => [0 => '~']]]])
		->andReturn(['test' => [['name' => 'test', 'middleware' => BarMiddleware::class, 'parameters' => [0 => '~']]]]);

		$route = Mockery::mock('\mako\http\routing\Route');

		$route->shouldReceive('getMiddleware')->once()->andReturn(['test("~")']);

		$route->shouldReceive('getAction')->once()->andReturn(function()
		{
			return 'hello, world!';
		});

		$route->shouldReceive('getParameters')->once()->andReturn([]);

		$request = Mockery::mock('\mako\http\Request');

		$response = Mockery::mock('\mako\http\Response')->makePartial();

		$container = Mockery::mock('\mako\syringe\Container')->makePartial();

		$dispatcher = new Dispatcher($request, $response, $middleware, $container);

		$response = $dispatcher->dispatch($route);

		$this->assertEquals('hello,~world!', $response->getBody());
	}

	/**
	 *
	 */
	public function testMiddlewareWithNamedArguments()
	{
		$middleware = Mockery::mock('\mako\http\routing\Middleware');

		$middleware->shouldReceive('get')->once()->with('test')->andReturn(FooMiddleware::class);

		$middleware->shouldReceive('orderByPriority')->once()
		->with(['test' => [['name' => 'test', 'middleware' => FooMiddleware::class, 'parameters' => ['separator' => '~']]]])
		->andReturn(['test' => [['name' => 'test', 'middleware' => FooMiddleware::class, 'parameters' => ['separator' => '~']]]]);

		$route = Mockery::mock('\mako\http\routing\Route');

		$route->shouldReceive('getMiddleware')->once()->andReturn(['test("separator":"~")']);

		$route->shouldReceive('getAction')->once()->andReturn(function()
		{
			return 'hello, world!';
		});

		$route->shouldReceive('getParameters')->once()->andReturn([]);

		$request = Mockery::mock('\mako\http\Request');

		$response = Mockery::mock('\mako\http\Response')->makePartial();

		$container = Mockery::mock('\mako\syringe\Container')->makePartial();

		$dispatcher = new Dispatcher($request, $response, $middleware, $container);

		$response = $dispatcher->dispatch($route);

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

		$route->shouldReceive('getParameters')->once()->andReturn([]);

		$route->shouldReceive('getMiddleware')->once()->andReturn([]);

		$request = Mockery::mock('\mako\http\Request');

		$response = Mockery::mock('\mako\http\Response')->makePartial();

		$dispatcher = new Dispatcher($request, $response, $middleware);

		$response = $dispatcher->dispatch($route);

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

		$route->shouldReceive('getParameters')->once()->andReturn(['hello' => 'Hello', 'world' => 'world']);

		$route->shouldReceive('getMiddleware')->once()->andReturn([]);

		$request = Mockery::mock('\mako\http\Request');

		$response = Mockery::mock('\mako\http\Response')->makePartial();

		$dispatcher = new Dispatcher($request, $response, $middleware);

		$response = $dispatcher->dispatch($route);

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

		$route->shouldReceive('getParameters')->once()->andReturn([]);

		$route->shouldReceive('getMiddleware')->once()->andReturn([]);

		$request = Mockery::mock('\mako\http\Request');

		$response = Mockery::mock('\mako\http\Response')->makePartial();

		$container = Mockery::mock('\mako\syringe\Container')->makePartial();

		$container->shouldReceive('get')->with('mako\http\Request')->andReturn($request);

		$dispatcher = new Dispatcher($request, $response, $middleware, $container);

		$response = $dispatcher->dispatch($route);

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

		$route->shouldReceive('getParameters')->once()->andReturn([]);

		$route->shouldReceive('getMiddleware')->once()->andReturn([]);

		$request = Mockery::mock('\mako\http\Request');

		$response = Mockery::mock('\mako\http\Response')->makePartial();

		$container = Mockery::mock('\mako\syringe\Container')->makePartial();

		$container->shouldReceive('get')->with('mako\http\Response')->andReturn($response);

		$dispatcher = new Dispatcher($request, $response, $middleware, $container);

		$response = $dispatcher->dispatch($route);

		$this->assertInstanceOf('mako\tests\unit\http\routing\InjectMe', $response->getBody());
	}
}
