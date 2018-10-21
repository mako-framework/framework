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
use function implode;
use function is_string;
use function preg_match;
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
	public function __construct(Routes $routes, Container $container = null)
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
		if(preg_match($route->getRegex(), $path, $parameters) > 0)
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
		list($constraint, $parameters) = $this->parseFunction($constraint);

		if(!isset($this->constraints[$constraint]))
		{
			throw new RuntimeException(vsprintf('No constraint named [ %s ] has been registered.', [$constraint]));
		}

		$constraint = $this->container->get($this->constraints[$constraint]);

		if(!empty($parameters))
		{
			$constraint->setParameters($parameters);
		}

		return $constraint;
	}

	/**
	 * Returns true if all the route constraints are satisfied and false if not.
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
			$url = $request->baseURL() . ($request->isClean() ? '' : '/' . $request->scriptName()) . rtrim('/' . $request->languagePrefix(), '/') . $requestPath . '/';

			$get = $request->getQuery()->all();

			if(!empty($get))
			{
				$url = $url . '?' . http_build_query($get);
			}

			return (new Redirect($url))->status(301);
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
			if($this->matches($route, $requestPath))
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

		return new Route([], '', function(Response $response) use ($allowedMethods)
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
		$matched = false;

		$requestMethod = $request->method();

		$requestPath = $request->path();

		foreach($this->routes->getRoutes() as $route)
		{
			if($this->matches($route, $requestPath) && $this->constraintsAreSatisfied($route))
			{
				if(!$route->allowsMethod($requestMethod))
				{
					$matched = true;

					continue;
				}

				// Redirect to URL with trailing slash if the route should have one

				if($route->hasTrailingSlash() && !empty($requestPath) && substr($requestPath, -1) !== '/')
				{
					return $this->redirectRoute($requestPath);
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

		if($matched)
		{
			// We found a matching route but it does not allow the request method so we'll throw a 405 exception

			throw new MethodNotAllowedException($this->getAllowedMethodsForMatchingRoutes($requestPath));
		}

		// No routes matched so we'll throw a 404 exception

		throw new NotFoundException($requestMethod . ': ' . $requestPath);
	}
}
