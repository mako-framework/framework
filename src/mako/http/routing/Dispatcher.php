<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\http\routing;

use Closure;
use mako\http\Request;
use mako\http\Response;
use mako\http\routing\middleware\MiddlewareInterface;
use mako\onion\Onion;
use mako\syringe\Container;

use function array_diff_key;
use function array_fill_keys;
use function array_intersect_key;
use function array_keys;
use function asort;
use function is_array;
use function method_exists;

/**
 * Route dispatcher.
 */
class Dispatcher
{
	/**
	 * Default middleware priority.
	 *
	 * @var int
	 */
	public const MIDDLEWARE_DEFAULT_PRIORITY = 100;

	/**
	 * Global middleware.
	 */
	protected array $globalMiddleware = [];

	/**
	 * Middleware priority.
	 */
	protected array $middlewarePriority = [];

	/**
	 * Constructor.
	 */
	public function __construct(
		protected Request $request,
		protected Response $response,
		protected Container $container = new Container
	)
	{}

	/**
	 * Sets middleware priority.
	 */
	public function setMiddlewarePriority(string $middleware, int $priority): Dispatcher
	{
		$this->middlewarePriority[$middleware] = $priority;

		return $this;
	}

	/**
	 * Resets middleware priority.
	 */
	public function resetMiddlewarePriority(): Dispatcher
	{
		$this->middlewarePriority = [];

		return $this;
	}

	/**
	 * Sets the chosen middleware as global.
	 */
	public function registerGlobalMiddleware(string $middleware, mixed ...$parameters): Dispatcher
	{
		$this->globalMiddleware[] = ['middleware' => $middleware, 'parameters' => $parameters];

		return $this;
	}

	/**
	 * Orders resolved middleware by priority.
	 */
	protected function orderMiddlewareByPriority(array $middleware): array
	{
		if(empty($this->middlewarePriority))
		{
			return $middleware;
		}

		$priority = array_intersect_key($this->middlewarePriority, $middleware) + array_fill_keys(array_keys(array_diff_key($middleware, $this->middlewarePriority)), static::MIDDLEWARE_DEFAULT_PRIORITY);

		asort($priority);

		return [...$priority, ...$middleware];
	}

	/**
	 * Adds route middleware to the stack.
	 */
	protected function addMiddlewareToStack(Onion $onion, array $middleware): void
	{
		if(empty($middleware) === false)
		{
			// Group middleware

			$layers = [];

			foreach($middleware as $layer)
			{
				$layers[$layer['middleware']] = $layer;
			}

			// Add ordered middleware to stack

			foreach($this->orderMiddlewareByPriority($layers) as $layer)
			{
				$onion->addLayer($layer['middleware'], $layer['parameters']);
			}
		}
	}

	/**
	 * Executes a closure action.
	 */
	protected function executeClosure(Closure $action, array $parameters): Response
	{
		return $this->response->setBody($this->container->call($action, $parameters));
	}

	/**
	 * Executes a controller action.
	 */
	protected function executeController(array|string $action, array $parameters): Response
	{
		[$controller, $method] = is_array($action) ? $action : [$action, '__invoke'];

		$controller = $this->container->get($controller);

		// Execute the before action method if we have one

		if(method_exists($controller, 'beforeAction'))
		{
			$returnValue = $this->container->call($controller->beforeAction(...));
		}

		if(empty($returnValue))
		{
			// The before action method didn't return any data so we can set the
			// response body to whatever the route action returns

			$this->response->setBody($this->container->call([$controller, $method], $parameters));

			// Execute the after action method if we have one

			if(method_exists($controller, 'afterAction'))
			{
				$this->container->call($controller->afterAction(...));
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
	 */
	public function dispatch(Route $route): Response
	{
		$onion = new Onion($this->container, expectedInterface: MiddlewareInterface::class);

		$this->addMiddlewareToStack($onion, [...$this->globalMiddleware, ...$route->getMiddleware()]);

		return $onion->peel(fn () => $this->executeAction($route), [$this->request, $this->response]);
	}
}
