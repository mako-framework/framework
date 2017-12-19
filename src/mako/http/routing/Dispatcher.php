<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\http\routing;

use Closure;

use mako\common\traits\FunctionParserTrait;
use mako\http\Request;
use mako\http\Response;
use mako\http\routing\Middleware;
use mako\http\routing\Route;
use mako\http\routing\middleware\MiddlewareInterface;
use mako\onion\Onion;
use mako\syringe\Container;

/**
 * Route dispatcher.
 *
 * @author Frederic G. Østby
 */
class Dispatcher
{
	use FunctionParserTrait;

	/**
	 * Request.
	 *
	 * @var \mako\http\Request
	 */
	protected $request;

	/**
	 * Response.
	 *
	 * @var \mako\http\Response
	 */
	protected $response;

	/**
	 * Route middleware.
	 *
	 * @var \mako\http\routing\Middleware
	 */
	protected $middleware;

	/**
	 * Route to be dispatched.
	 *
	 * @var \mako\http\routing\Route
	 */
	protected $route;

	/**
	 * IoC container instance.
	 *
	 * @var \mako\syringe\Container
	 */
	protected $container;

	/**
	 * Constructor.
	 *
	 * @param \mako\http\Request            $request    Request instance
	 * @param \mako\http\Response           $response   Response instance
	 * @param \mako\http\routing\Middleware $middleware Middleware collection
	 * @param \mako\http\routing\Route      $route      The route we're dispatching
	 * @param \mako\syringe\Container|null  $container  IoC container
	 */
	public function __construct(Request $request, Response $response, Middleware $middleware, Route $route, Container $container = null)
	{
		$this->request = $request;

		$this->response = $response;

		$this->middleware = $middleware;

		$this->route = $route;

		$this->container = $container ?? new Container;
	}

	/**
	 * Resolves the middleware.
	 *
	 * @param  string $middleware middleware
	 * @return array
	 */
	protected function resolveMiddleware(string $middleware): array
	{
		list($name, $parameters) = $this->parseFunction($middleware, true);

		$middleware = $this->middleware->get($name);

		return ['name' => $name, 'middleware' => $middleware, 'parameters' => $parameters];
	}

	/**
	 * Adds route middleware to the stack.
	 *
	 * @param \mako\onion\Onion $onion      Middleware stack
	 * @param array             $middleware Array of middleware
	 */
	protected function addMiddlewareToStack(Onion $onion, array $middleware)
	{
		if(empty($middleware) === false)
		{
			// Resolve middleware

			$resolved = [];

			foreach($middleware as $layer)
			{
				$layer = $this->resolveMiddleware($layer);

				$resolved[$layer['name']][] = $layer;
			}

			// Add ordered middleware to stack

			foreach($this->middleware->orderByPriority($resolved) as $name)
			{
				foreach($name as $layer)
				{
					$onion->addLayer($layer['middleware'], $layer['parameters']);
				}
			}
		}
	}

	/**
	 * Executes a closure action.
	 *
	 * @param  \Closure            $closure    Closure
	 * @param  array               $parameters Parameters
	 * @return \mako\http\Response
	 */
	protected function executeClosure(Closure $closure, array $parameters): Response
	{
		return $this->response->body($this->container->call($closure, $parameters));
	}

	/**
	 * Executs a controller action.
	 *
	 * @param  string              $controller Controller
	 * @param  array               $parameters Parameters
	 * @return \mako\http\Response
	 */
	protected function executeController(string $controller, array $parameters): Response
	{
		if(strpos($controller, '::') === false)
		{
			$method = '__invoke';
		}
		else
		{
			list($controller, $method) = explode('::', $controller, 2);
		}

		$controller = $this->container->get($controller);

		// Execute the before action method if we have one

		if(method_exists($controller, 'beforeAction'))
		{
			$returnValue = $this->container->call([$controller, 'beforeAction']);
		}

		if(empty($returnValue))
		{
			// The before action method didn't return any data so we can set the
			// response body to whatever the route action returns

			$this->response->body($this->container->call([$controller, $method], $parameters));

			// Execute the after action method if we have one

			if(method_exists($controller, 'afterAction'))
			{
				$this->container->call([$controller, 'afterAction']);
			}
		}
		else
		{
			// The before action method returned data so we'll set the response body to whatever it returned

			$this->response->body($returnValue);
		}

		return $this->response;
	}

	/**
	 * Executes the route action.
	 *
	 * @return \mako\http\Response
	 */
	protected function executeAction(): Response
	{
		$action = $this->route->getAction();

		$parameters = $this->route->getParameters();

		if($action instanceof Closure)
		{
			return $this->executeClosure($action, $parameters);
		}

		return $this->executeController($action, $parameters);
	}

	/**
	 * Dispatches the route and returns the response.
	 *
	 * @return \mako\http\Response
	 */
	public function dispatch(): Response
	{
		$onion = new Onion($this->container, null, MiddlewareInterface::class, 'setParameters');

		$this->addMiddlewareToStack($onion, $this->route->getMiddleware());

		return $onion->peel(function()
		{
			return $this->executeAction();
		}, [$this->request, $this->response]);
	}
}
