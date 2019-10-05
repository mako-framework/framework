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
use mako\syringe\Container;
use RuntimeException;

use function array_merge;
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
 *
 * @author Frederic G. Østby
 */
class Router
{
	use FunctionParserTrait;

	/**
	 * Route collection.
	 *
	 * @var \mako\http\routing\Routes
	 */
	protected $routes;

	/**
	 * Container.
	 *
	 * @var \mako\syringe\Container
	 */
	protected $container;

	/**
	 * Constraints.
	 *
	 * @var array
	 */
	protected $constraints = [];

	/**
	 * Global constraints.
	 *
	 * @var array
	 */
	protected $globalConstraints = [];

	/**
	 * Constructor.
	 *
	 * @param \mako\http\routing\Routes    $routes    Routes
	 * @param \mako\syringe\Container|null $container Container
	 */
	public function __construct(Routes $routes, ?Container $container = null)
	{
		$this->routes  = $routes;

		$this->container = $container ?? new Container;
	}

	/**
	 * Registers constraint.
	 *
	 * @param  string                    $name       Constraint name
	 * @param  string                    $constraint Constraint class name
	 * @return \mako\http\routing\Router
	 */
	public function registerConstraint(string $name, string $constraint): Router
	{
		$this->constraints[$name] = $constraint;

		return $this;
	}

	/**
	 * Sets the chosen constraint as global.
	 *
	 * @param  array                     $constraint Array of constraint names
	 * @return \mako\http\routing\Router
	 */
	public function setConstraintAsGlobal(array $constraint): Router
	{
		$this->globalConstraints = $constraint;

		return $this;
	}

	/**
	 * Returns TRUE if the route matches the request path and FALSE if not.
	 *
	 * @param  \mako\http\routing\Route $route Route
	 * @param  string                   $path  Request path
	 * @return bool
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
	 *
	 * @param  string                                             $constraint Constraint
	 * @return \mako\http\routing\constraints\ConstraintInterface
	 */
	protected function constraintFactory(string $constraint): ConstraintInterface
	{
		[$constraint, $parameters] = $this->parseFunction($constraint);

		if(!isset($this->constraints[$constraint]))
		{
			throw new RuntimeException(vsprintf('No constraint named [ %s ] has been registered.', [$constraint]));
		}

		$constraint = $this->container->get($this->constraints[$constraint], $parameters);

		return $constraint;
	}

	/**
	 * Returns TRUE if all the route constraints are satisfied and FALSE if not.
	 *
	 * @param  \mako\http\routing\Route $route Route
	 * @return bool
	 */
	protected function constraintsAreSatisfied(Route $route): bool
	{
		foreach(array_merge($this->globalConstraints, $route->getConstraints()) as $constraint)
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
	 *
	 * @param  string                   $requestPath The requested path
	 * @return \mako\http\routing\Route
	 */
	protected function redirectRoute(string $requestPath): Route
	{
		return new Route([], '', function(Request $request) use ($requestPath)
		{
			$url = $request->getBaseURL() . ($request->isClean() ? '' : "/{$request->getScriptName()}") . rtrim("/{$request->getLanguagePrefix()}", '/') . "{$requestPath}/";

			$query = $request->getQuery()->all();

			if(!empty($query))
			{
				$url .= '?' . http_build_query($query, '', '&', PHP_QUERY_RFC3986);
			}

			return new Redirect($url, Redirect::MOVED_PERMANENTLY);
		});
	}

	/**
	 * Returns an array of all allowed request methods for the requested route.
	 *
	 * @param  string $requestPath The requested path
	 * @return array
	 */
	protected function getAllowedMethodsForMatchingRoutes(string $requestPath): array
	{
		$methods = [];

		foreach($this->routes->getRoutes() as $route)
		{
			if($this->matches($route, $requestPath) && $this->constraintsAreSatisfied($route))
			{
				$methods = array_merge($methods, $route->getMethods());
			}
		}

		return array_unique($methods);
	}

	/**
	 * Returns a route with a closure action that sets the allow header.
	 *
	 * @param  string                   $requestPath The requested path
	 * @return \mako\http\routing\Route
	 */
	protected function optionsRoute(string $requestPath): Route
	{
		$allowedMethods = $this->getAllowedMethodsForMatchingRoutes($requestPath);

		return new Route([], '', function(Response $response) use ($allowedMethods): void
		{
			$response->getHeaders()->add('Allow', implode(',', $allowedMethods));
		});
	}

	/**
	 * Matches and returns the appropriate route along with its parameters.
	 *
	 * @param  \mako\http\Request       $request Request
	 * @return \mako\http\routing\Route
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
			throw new MethodNotAllowedException($allowedMethods);
		}

		// No routes matched so we'll throw a not found exception

		notFound:

		throw new NotFoundException;
	}
}
