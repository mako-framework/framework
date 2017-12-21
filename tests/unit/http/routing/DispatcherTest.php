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

class BazMiddleware extends Middleware
{
	public function execute(Request $request, Response $response, Closure $next): Response
	{
		$response = $next($request, $response);

		$response->body('AA ' . $response->getBody() . ' AA');

		return $response;
	}
}

class BaxMiddleware extends Middleware
{
	public function execute(Request $request, Response $response, Closure $next): Response
	{
		$response = $next($request, $response);

		$response->body('BB ' . $response->getBody() . ' BB');

		return $response;
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
		$route = Mockery::mock('\mako\http\routing\Route');

		$route->shouldReceive('getAction')->once()->andReturn(function()
		{
			return 'Hello, world!';
		});

		$route->shouldReceive('getParameters')->once()->andReturn([]);

		$route->shouldReceive('getMiddleware')->once()->andReturn([]);

		$request = Mockery::mock('\mako\http\Request');

		$response = Mockery::mock('\mako\http\Response')->makePartial();

		$dispatcher = new Dispatcher($request, $response);

		$response = $dispatcher->dispatch($route);

		$this->assertEquals('Hello, world!', $response->getBody());
	}

	/**
	 *
	 */
	public function testClosureActionWithParams()
	{
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

		$dispatcher = new Dispatcher($request, $response, $container);

		$response = $dispatcher->dispatch($route);

		$this->assertEquals('Hello, Kitty!', $response->getBody());

		$this->assertEquals(['X-Foo-Bar' => ['Foo Bar']], $response->getHeaders());
	}

	/**
	 *
	 */
	public function testControllerAction()
	{
		$route = Mockery::mock('\mako\http\routing\Route');

		$route->shouldReceive('getAction')->once()->andReturn('mako\tests\unit\http\routing\SimpleController::foo');

		$route->shouldReceive('getParameters')->once()->andReturn([]);

		$route->shouldReceive('getMiddleware')->once()->andReturn([]);

		$request = Mockery::mock('\mako\http\Request');

		$response = Mockery::mock('\mako\http\Response')->makePartial();

		$container = Mockery::mock('\mako\syringe\Container')->makePartial();

		$container->shouldReceive('get')->with('mako\http\Response')->andReturn($response);

		$dispatcher = new Dispatcher($request, $response, $container);

		$response = $dispatcher->dispatch($route);

		$this->assertEquals('Hello, world!', $response->getBody());

		$this->assertEquals(['X-Foo-Bar' => ['Foo Bar']], $response->getHeaders());
	}

	/**
	 *
	 */
	public function testControllerActionWithParams()
	{
		$route = Mockery::mock('\mako\http\routing\Route');

		$route->shouldReceive('getAction')->once()->andReturn('mako\tests\unit\http\routing\SimpleController::bar');

		$route->shouldReceive('getParameters')->once()->andReturn(['who' => 'Kitty']);

		$route->shouldReceive('getMiddleware')->once()->andReturn([]);

		$request = Mockery::mock('\mako\http\Request');

		$response = Mockery::mock('\mako\http\Response')->makePartial();

		$container = Mockery::mock('\mako\syringe\Container')->makePartial();

		$container->shouldReceive('get')->with('mako\http\Response')->andReturn($response);

		$dispatcher = new Dispatcher($request, $response, $container);

		$response = $dispatcher->dispatch($route);

		$this->assertEquals('Hello, Kitty!', $response->getBody());
	}

	/**
	 *
	 */
	public function testControllerWithNullBeforeFilter()
	{
		$route = Mockery::mock('\mako\http\routing\Route');

		$route->shouldReceive('getAction')->once()->andReturn('mako\tests\unit\http\routing\ControllerWithNullBeforeFilter::foo');

		$route->shouldReceive('getParameters')->once()->andReturn([]);

		$route->shouldReceive('getMiddleware')->once()->andReturn([]);

		$request = Mockery::mock('\mako\http\Request');

		$response = Mockery::mock('\mako\http\Response')->makePartial();

		$container = Mockery::mock('\mako\syringe\Container')->makePartial();

		$container->shouldReceive('get')->with('mako\http\Response')->andReturn($response);

		$dispatcher = new Dispatcher($request, $response, $container);

		$response = $dispatcher->dispatch($route);

		$this->assertEquals('Hello, world!', $response->getBody());

		$this->assertEquals(['X-Foo-Bar' => ['Foo Bar']], $response->getHeaders());
	}

	/**
	 *
	 */
	public function testControllerWithBeforeFilter()
	{
		$route = Mockery::mock('\mako\http\routing\Route');

		$route->shouldReceive('getAction')->once()->andReturn('mako\tests\unit\http\routing\ControllerWithBeforeFilter::foo');

		$route->shouldReceive('getParameters')->once()->andReturn([]);

		$route->shouldReceive('getMiddleware')->once()->andReturn([]);

		$request = Mockery::mock('\mako\http\Request');

		$response = Mockery::mock('\mako\http\Response')->makePartial();

		$dispatcher = new Dispatcher($request, $response);

		$response = $dispatcher->dispatch($route);

		$this->assertEquals('Before action', $response->getBody());
	}

	/**
	 *
	 */
	public function testControllerActionWithAfterFilter()
	{
		$route = Mockery::mock('\mako\http\routing\Route');

		$route->shouldReceive('getAction')->once()->andReturn('mako\tests\unit\http\routing\ControllerWithAfterFilter::foo');

		$route->shouldReceive('getParameters')->once()->andReturn([]);

		$route->shouldReceive('getMiddleware')->once()->andReturn([]);

		$request = Mockery::mock('\mako\http\Request');

		$response = Mockery::mock('\mako\http\Response')->makePartial();

		$container = Mockery::mock('\mako\syringe\Container')->makePartial();

		$container->shouldReceive('get')->with('mako\http\Response')->andReturn($response);

		$dispatcher = new Dispatcher($request, $response, $container);

		$response = $dispatcher->dispatch($route);

		$this->assertEquals('HELLO, WORLD!', $response->getBody());
	}

	/**
	 *
	 */
	public function testMiddleware()
	{
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

		$dispatcher = new Dispatcher($request, $response, $container);

		$dispatcher->registerMiddleware('test', FooMiddleware::class);

		$response = $dispatcher->dispatch($route);

		$this->assertEquals('hello,_world!', $response->getBody());
	}

	/**
	 *
	 */
	public function testGlobalMiddleware()
	{
		$route = Mockery::mock('\mako\http\routing\Route');

		$route->shouldReceive('getMiddleware')->once()->andReturn([]);

		$route->shouldReceive('getAction')->once()->andReturn(function()
		{
			return 'hello, world!';
		});

		$route->shouldReceive('getParameters')->once()->andReturn([]);

		$request = Mockery::mock('\mako\http\Request');

		$response = Mockery::mock('\mako\http\Response')->makePartial();

		$container = Mockery::mock('\mako\syringe\Container')->makePartial();

		$dispatcher = new Dispatcher($request, $response, $container);

		$dispatcher->registerMiddleware('test', BazMiddleware::class);

		$dispatcher->setMiddlewareAsGlobal(['test']);

		$response = $dispatcher->dispatch($route);

		$this->assertEquals('AA hello, world! AA', $response->getBody());
	}

	/**
	 *
	 */
	public function testMiddlewarePriority()
	{
		$route = Mockery::mock('\mako\http\routing\Route');

		$route->shouldReceive('getMiddleware')->times(3)->andReturn(['a', 'b']);

		$route->shouldReceive('getAction')->times(3)->andReturn(function()
		{
			return 'hello, world!';
		});

		$route->shouldReceive('getParameters')->times(3)->andReturn([]);

		$request = Mockery::mock('\mako\http\Request');

		$response = Mockery::mock('\mako\http\Response')->makePartial();

		$container = Mockery::mock('\mako\syringe\Container')->makePartial();

		$dispatcher = new Dispatcher($request, $response, $container);

		$dispatcher->registerMiddleware('a', BazMiddleware::class);
		$dispatcher->registerMiddleware('b', BaxMiddleware::class);

		//

		$dispatcher->setMiddlewarePriority(['a' => 1, 'b' => 2]);

		$response = $dispatcher->dispatch($route);

		$this->assertEquals('AA BB hello, world! BB AA', $response->getBody());

		//

		$dispatcher->setMiddlewarePriority(['a' => 2, 'b' => 1]);

		$response = $dispatcher->dispatch($route);

		$this->assertEquals('BB AA hello, world! AA BB', $response->getBody());

		//

		$dispatcher->resetMiddlewarePriority();

		$response = $dispatcher->dispatch($route);

		$this->assertEquals('AA BB hello, world! BB AA', $response->getBody());
	}

	/**
	 * @expectedException RuntimeException
	 * @expectedExceptionMessage No middleware named [ foobar ] has been registered.
	 */
	public function testUnregisteredMiddleware()
	{
		$route = Mockery::mock('\mako\http\routing\Route');

		$route->shouldReceive('getMiddleware')->once()->andReturn(['foobar']);

		$request = Mockery::mock('\mako\http\Request');

		$response = Mockery::mock('\mako\http\Response')->makePartial();

		$container = Mockery::mock('\mako\syringe\Container')->makePartial();

		$dispatcher = new Dispatcher($request, $response, $container);

		$response = $dispatcher->dispatch($route);
	}

	/**
	 *
	 */
	public function testMiddlewareWithUnnamedArguments()
	{
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

		$dispatcher = new Dispatcher($request, $response, $container);

		$dispatcher->registerMiddleware('test', BarMiddleware::class);

		$response = $dispatcher->dispatch($route);

		$this->assertEquals('hello,~world!', $response->getBody());
	}

	/**
	 *
	 */
	public function testMiddlewareWithNamedArguments()
	{
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

		$dispatcher = new Dispatcher($request, $response, $container);

		$dispatcher->registerMiddleware('test', FooMiddleware::class);

		$response = $dispatcher->dispatch($route);

		$this->assertEquals('hello,~world!', $response->getBody());
	}

	/**
	 *
	 */
	public function testControllerInjection()
	{
		$route = Mockery::mock('\mako\http\routing\Route');

		$route->shouldReceive('getAction')->once()->andReturn('mako\tests\unit\http\routing\ControllerWithInjection::foo');

		$route->shouldReceive('getParameters')->once()->andReturn([]);

		$route->shouldReceive('getMiddleware')->once()->andReturn([]);

		$request = Mockery::mock('\mako\http\Request');

		$response = Mockery::mock('\mako\http\Response')->makePartial();

		$dispatcher = new Dispatcher($request, $response);

		$response = $dispatcher->dispatch($route);

		$this->assertEquals('Hello, world!', $response->getBody());
	}

	/**
	 *
	 */
	public function testClosureWithReversedParameterOrder()
	{
		$route = Mockery::mock('\mako\http\routing\Route');

		$route->shouldReceive('getAction')->once()->andReturn(function($world, $hello)
		{
			return $hello . ', ' . $world . '!';
		});

		$route->shouldReceive('getParameters')->once()->andReturn(['hello' => 'Hello', 'world' => 'world']);

		$route->shouldReceive('getMiddleware')->once()->andReturn([]);

		$request = Mockery::mock('\mako\http\Request');

		$response = Mockery::mock('\mako\http\Response')->makePartial();

		$dispatcher = new Dispatcher($request, $response);

		$response = $dispatcher->dispatch($route);

		$this->assertEquals('Hello, world!', $response->getBody());
	}

	/**
	 *
	 */
	public function testClosureParameterInjection()
	{
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

		$dispatcher = new Dispatcher($request, $response, $container);

		$response = $dispatcher->dispatch($route);

		$this->assertInstanceOf('mako\http\Request', $response->getBody());
	}

	/**
	 *
	 */
	public function testControllerActionParameterInjection()
	{
		$route = Mockery::mock('\mako\http\routing\Route');

		$route->shouldReceive('getAction')->once()->andReturn('mako\tests\unit\http\routing\SimpleController::baz');

		$route->shouldReceive('getParameters')->once()->andReturn([]);

		$route->shouldReceive('getMiddleware')->once()->andReturn([]);

		$request = Mockery::mock('\mako\http\Request');

		$response = Mockery::mock('\mako\http\Response')->makePartial();

		$container = Mockery::mock('\mako\syringe\Container')->makePartial();

		$container->shouldReceive('get')->with('mako\http\Response')->andReturn($response);

		$dispatcher = new Dispatcher($request, $response, $container);

		$response = $dispatcher->dispatch($route);

		$this->assertInstanceOf('mako\tests\unit\http\routing\InjectMe', $response->getBody());
	}
}
