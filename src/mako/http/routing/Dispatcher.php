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
use mako\http\routing\exceptions\RoutingException;
use mako\http\routing\middleware\MiddlewareInterface;
use mako\onion\Onion;
use mako\syringe\Container;

use function array_diff_key;
use function array_fill_keys;
use function array_intersect_key;
use function array_keys;
use function array_merge;
use function is_array;
use function method_exists;
use function uasort;
use function vsprintf;

/**
 * Route dispatcher.
 */
class Dispatcher
{
	use FunctionParserTrait;

	/**
	 * Default middleware priority.
	 *
	 * @var int
	 */
	public const MIDDLEWARE_DEFAULT_PRIORITY = 100;

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
	 * Container.
	 *
	 * @var \mako\syringe\Container
	 */
	protected $container;

	/**
	 * Route middleware.
	 *
	 * @var array
	 */
	protected $middleware = [];

	/**
	 * Global middleware.
	 *
	 * @var array
	 */
	protected $globalMiddleware = [];

	/**
	 * Middleware priority.
	 *
	 * @var array
	 */
	protected $middlewarePriority = [];

	/**
	 * Constructor.
	 *
	 * @param \mako\http\Request           $request   Request instance
	 * @param \mako\http\Response          $response  Response instance
	 * @param \mako\syringe\Container|null $container Container
	 */
	public function __construct(Request $request, Response $response, ?Container $container = null)
	{
		$this->request = $request;

		$this->response = $response;

		$this->container = $container ?? new Container;
	}

	/**
	 * Sets the middleware priority.
	 *
	 * @param  array                         $priority Middleware priority
	 * @return \mako\http\routing\Dispatcher
	 */
	public function setMiddlewarePriority(array $priority): Dispatcher
	{
		$this->middlewarePriority = $priority + $this->middlewarePriority;

		return $this;
	}

	/**
	 * Resets middleware priority.
	 *
	 * @return \mako\http\routing\Dispatcher
	 */
	public function resetMiddlewarePriority(): Dispatcher
	{
		$this->middlewarePriority = [];

		return $this;
	}

	/**
	 * Registers middleware.
	 *
	 * @param  string                        $name       Middleware name
	 * @param  string                        $middleware Middleware class name
	 * @param  int|null                      $priority   Middleware priority
	 * @return \mako\http\routing\Dispatcher
	 */
	public function registerMiddleware(string $name, string $middleware, ?int $priority = null): Dispatcher
	{
		$this->middleware[$name] = $middleware;

		if($priority !== null)
		{
			$this->middlewarePriority[$name] = $priority;
		}

		return $this;
	}

	/**
	 * Sets the chosen middleware as global.
	 *
	 * @param  array                         $middleware Array of middleware names
	 * @return \mako\http\routing\Dispatcher
	 */
	public function setMiddlewareAsGlobal(array $middleware): Dispatcher
	{
		$this->globalMiddleware = $middleware;

		return $this;
	}

	/**
	 * Resolves the middleware.
	 *
	 * @param  string $middleware middleware
	 * @return array
	 */
	protected function resolveMiddleware(string $middleware): array
	{
		[$name, $parameters] = $this->parseFunction($middleware);

		if(!isset($this->middleware[$name]))
		{
			throw new RoutingException(vsprintf('No middleware named [ %s ] has been registered.', [$middleware]));
		}

		return ['name' => $name, 'middleware' => $this->middleware[$name], 'parameters' => $parameters];
	}

	/**
	 * Orders resolved middleware by priority.
	 *
	 * @param  array $middleware Array of middleware
	 * @return array
	 */
	protected function orderMiddlewareByPriority(array $middleware): array
	{
		if(empty($this->middlewarePriority))
		{
			return $middleware;
		}

		$priority = array_intersect_key($this->middlewarePriority, $middleware) + array_fill_keys(array_keys(array_diff_key($middleware, $this->middlewarePriority)), static::MIDDLEWARE_DEFAULT_PRIORITY);

		// Sort the priority map using stable sorting

		$position = 0;

		foreach($priority as $key => $value)
		{
			$priority[$key] = [$position++, $value];
		}

		uasort($priority, static fn($a, $b) => $a[1] === $b[1] ? ($a[0] > $b[0] ? 1 : -1) : ($a[1] > $b[1] ? 1 : -1));

		foreach($priority as $key => $value)
		{
			$priority[$key] = $value[1];
		}

		// Return sorted middleware list

		return array_merge($priority, $middleware);
	}

	/**
	 * Adds route middleware to the stack.
	 *
	 * @param \mako\onion\Onion $onion      Middleware stack
	 * @param array             $middleware Array of middleware
	 */
	protected function addMiddlewareToStack(Onion $onion, array $middleware): void
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

			foreach($this->orderMiddlewareByPriority($resolved) as $name)
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
	 * @param  \Closure            $action     Closure
	 * @param  array               $parameters Parameters
	 * @return \mako\http\Response
	 */
	protected function executeClosure(Closure $action, array $parameters): Response
	{
		return $this->response->setBody($this->container->call($action, $parameters));
	}

	/**
	 * Executes a controller action.
	 *
	 * @param  array|string        $action     Controller
	 * @param  array               $parameters Parameters
	 * @return \mako\http\Response
	 */
	protected function executeController($action, array $parameters): Response
	{
		[$controller, $method] = is_array($action) ? $action : [$action, '__invoke'];

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

			$this->response->setBody($this->container->call([$controller, $method], $parameters));

			// Execute the after action method if we have one

			if(method_exists($controller, 'afterAction'))
			{
				$this->container->call([$controller, 'afterAction']);
			}
		}
		else
		{
			// The before action method returned data so we'll set the response body to whatever it returned

			$this->response->setBody($returnValue);
		}

		return $this->response;
	}

	/**
	 * Executes the route action.
	 *
	 * @param  \mako\http\routing\Route $route Route
	 * @return \mako\http\Response
	 */
	protected function executeAction(Route $route): Response
	{
		$action = $route->getAction();

		$parameters = $route->getParameters();

		if($action instanceof Closure)
		{
			return $this->executeClosure($action, $parameters);
		}

		return $this->executeController($action, $parameters);
	}

	/**
	 * Dispatches the route and returns the response.
	 *
	 * @param  \mako\http\routing\Route $route Route
	 * @return \mako\http\Response
	 */
	public function dispatch(Route $route): Response
	{
		$onion = new Onion($this->container, null, MiddlewareInterface::class);

		$this->addMiddlewareToStack($onion, [...$this->globalMiddleware, ...$route->getMiddleware()]);

		return $onion->peel(fn() => $this->executeAction($route), [$this->request, $this->response]);
	}
}
