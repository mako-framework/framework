<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\http\routing;

use mako\common\traits\FunctionParserTrait;
use mako\http\exceptions\MethodNotAllowedException;
use mako\http\exceptions\NotFoundException;
use mako\http\Request;
use mako\http\Response;
use mako\http\response\senders\Redirect;
use mako\http\routing\constraints\ConstraintInterface;
use mako\http\routing\exceptions\RoutingException;
use mako\syringe\Container;

use function array_unique;
use function http_build_query;
use function implode;
use function in_array;
use function is_string;
use function preg_match;
use function rtrim;
use function substr;
use function vsprintf;

/**
 * Router.
 */
class Router
{
	use FunctionParserTrait;

	/**
	 * Constraints.
	 */
	protected array $constraints = [];

	/**
	 * Global constraints.
	 */
	protected array $globalConstraints = [];

	/**
	 * Constructor.
	 */
	public function __construct(
		protected Routes $routes,
		protected Container $container = new Container
	)
	{}

	/**
	 * Registers constraint.
	 */
	public function registerConstraint(string $name, ?string $constraint = null): Router
	{
		$this->constraints[$name] = $constraint ?? $name;

		return $this;
	}

	/**
	 * Sets the chosen constraint as global.
	 */
	public function setConstraintAsGlobal(array $constraint): Router
	{
		$this->globalConstraints = $constraint;

		return $this;
	}

	/**
	 * Returns TRUE if the route matches the request path and FALSE if not.
	 */
	protected function matches(Route $route, string $path): bool
	{
		if(preg_match($route->getRegex(), $path, $parameters) === 1)
		{
			$filtered = [];

			foreach($parameters as $key => $value)
			{
				if(is_string($key))
				{
					$filtered[$key] = $value;
				}
			}

			$route->setParameters($filtered);

			return true;
		}

		return false;
	}

	/**
	 * Constraint factory.
	 */
	protected function constraintFactory(string $constraint): ConstraintInterface
	{
		[$constraint, $parameters] = $this->parseFunction($constraint);

		if(!isset($this->constraints[$constraint]))
		{
			throw new RoutingException(vsprintf('No constraint named [ %s ] has been registered.', [$constraint]));
		}

		$constraint = $this->container->get($this->constraints[$constraint], $parameters);

		return $constraint;
	}

	/**
	 * Returns TRUE if all the route constraints are satisfied and FALSE if not.
	 */
	protected function constraintsAreSatisfied(Route $route): bool
	{
		foreach([...$this->globalConstraints, ...$route->getConstraints()] as $constraint)
		{
			if($this->constraintFactory($constraint)->isSatisfied() === false)
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Returns a route with a closure action that redirects to the correct URL.
	 */
	protected function redirectRoute(string $requestPath): Route
	{
		return new Route([], '', static function (Request $request) use ($requestPath)
		{
			$url = $request->getBaseURL() . ($request->isClean() ? '' : "/{$request->getScriptName()}") . rtrim("/{$request->getLanguagePrefix()}", '/') . "{$requestPath}/";

			$query = $request->getQuery()->all();

			if(!empty($query))
			{
				$url .= '?' . http_build_query($query, arg_separator: '&', encoding_type: PHP_QUERY_RFC3986);
			}

			return new Redirect($url, Redirect::MOVED_PERMANENTLY);
		});
	}

	/**
	 * Returns an array of all allowed request methods for the requested route.
	 */
	protected function getAllowedMethodsForMatchingRoutes(string $requestPath): array
	{
		$methods = [];

		foreach($this->routes->getRoutes() as $route)
		{
			if($this->matches($route, $requestPath) && $this->constraintsAreSatisfied($route))
			{
				$methods = [...$methods, ...$route->getMethods()];
			}
		}

		return array_unique($methods);
	}

	/**
	 * Returns a route with a closure action that sets the allow header.
	 */
	protected function optionsRoute(string $requestPath): Route
	{
		$allowedMethods = $this->getAllowedMethodsForMatchingRoutes($requestPath);

		return new Route([], '', static function (Response $response) use ($allowedMethods): void
		{
			$response->getHeaders()->add('Allow', implode(',', $allowedMethods));
		}, 'router:options');
	}

	/**
	 * Returns a route that throws a method not allowed exception.
	 */
	protected function methodNotAllowedRoute(array $allowedMethods): Route
	{
		return new Route([], '', static function () use ($allowedMethods): void
		{
			throw new MethodNotAllowedException($allowedMethods);
		}, 'router:405');
	}

	/**
	 * Returns a route that throws a not found exception.
	 */
	protected function notFoundRoute(): Route
	{
		return new Route([], '', static function (): void
		{
			throw new NotFoundException;
		}, 'router:404');
	}

	/**
	 * Matches and returns the appropriate route along with its parameters.
	 */
	public function route(Request $request): Route
	{
		$requestMethod = $request->getMethod();

		$requestPath = $request->getPath();

		foreach($this->routes->getRoutesByMethod($requestMethod) as $route)
		{
			if($this->matches($route, $requestPath) && $this->constraintsAreSatisfied($route))
			{
				// If the matching route is missing its trailing slash then we'll
				// redirect it (but only if it's a GET or HEAD request)

				if($route->hasTrailingSlash() && !empty($requestPath) && substr($requestPath, -1) !== '/')
				{
					if(in_array($requestMethod, ['GET', 'HEAD']))
					{
						return $this->redirectRoute($requestPath);
					}

					goto notFound;
				}

				// If this is an "OPTIONS" request then we'll collect all the allowed request methods
				// from all routes matching the requested path. We'll then add an "allows" header
				// to the matched route

				if($requestMethod === 'OPTIONS')
				{
					return $this->optionsRoute($requestPath);
				}

				// Assign the route to the request

				$request->setRoute($route);

				// Return the matched route and parameters

				return $route;
			}
		}

		// Check if there are any routes that match the pattern and constaints for other request methods

		if(!empty(($allowedMethods = $this->getAllowedMethodsForMatchingRoutes($requestPath))))
		{
			return $this->methodNotAllowedRoute($allowedMethods);
		}

		// No routes matched so we'll return a not found route

		notFound:

		return $this->notFoundRoute();
	}
}
