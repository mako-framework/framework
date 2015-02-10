<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\http\routing;

use mako\http\Request;
use mako\http\Response;
use mako\http\routing\Route;
use mako\http\routing\Routes;
use mako\http\exceptions\NotFoundException;
use mako\http\exceptions\MethodNotAllowedException;

/**
 * Router.
 *
 * @author  Frederic G. Østby
 */

class Router
{
	/**
	 * Route collection.
	 *
	 * @var \mako\http\routing\Routes
	 */

	protected $routes;

	/**
	 * Constructor.
	 *
	 * @access  public
	 * @param   \mako\http\routing\Routes  $routes   Routes
	 */

	public function __construct(Routes $routes)
	{
		$this->routes  = $routes;
	}

	/**
	 * Returns a route with a closure action that redirects to the correct URL.
	 *
	 * @access  protected
	 * @param   string                    $requestPath  The requested path
	 * @return  \mako\http\routing\Route
	 */

	protected function redirectRoute($requestPath)
	{
		return new Route([], '', function(Request $request, Response $response) use ($requestPath)
		{
			$url = $request->baseURL() . rtrim('/' . $request->languagePrefix(), '/') . $requestPath . '/';

			$get = $request->get();

			if(!empty($get))
			{
				$url = $url . '?' . http_build_query($get);
			}

			return $response->redirect($url)->status(301);
		});
	}

	/**
	 * Returns an array of all allowed request methods for the requested route.
	 *
	 * @access  protected
	 * @param   string     $requestPath  The requested path
	 * @return  array
	 */

	protected function getAllowedMethodsForMatchingRoutes($requestPath)
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
	 * @access  protected
	 * @param   string                    $requestPath  The requested path
	 * @return  \mako\http\routing\Route
	 */

	protected function optionsRoute($requestPath)
	{
		$allowedMethods = $this->getAllowedMethodsForMatchingRoutes($requestPath);

		return new Route([], '', function(Response $response) use ($allowedMethods)
		{
			$response->header('allow', implode(',', $allowedMethods));
		});
	}

	/**
	 * Returns TRUE if the route matches the request path and FALSE if not.
	 *
	 * @access  protected
	 * @param   \mako\http\routing\Route  $route       Route
	 * @param   string                    $path        Request path
	 * @param   array                     $parameters  Parameters
	 * @return  boolean
	 */

	protected function matches(Route $route, $path, array &$parameters = [])
	{
		if(preg_match($route->getRegex(), $path, $parameters) > 0)
		{
			foreach($parameters as $key => $value)
			{
				if(is_int($key))
				{
					unset($parameters[$key]);
				}
			}

			return true;
		}

		return false;
	}

	/**
	 * Matches and returns the appropriate route along with its parameters.
	 *
	 * @access  public
	 * @param   \mako\http\Request  $request  Request
	 * @return  array
	 */

	public function route(Request $request)
	{
		$matched = false;

		$parameters = [];

		$requestMethod = $request->method();

		$requestPath = $request->path();

		foreach($this->routes->getRoutes() as $route)
		{
			if($this->matches($route, $requestPath, $parameters))
			{
				if(!$route->allows($requestMethod))
				{
					$matched = true;

					continue;
				}

				// Redirect to URL with trailing slash if the route should have one

				if($route->hasTrailingSlash() && !empty($requestPath) && substr($requestPath, -1) !== '/')
				{
					return [$this->redirectRoute($requestPath), []];
				}

				// If this is an "OPTIONS" request then well collect all the allowed request methods
				// from all routes matching the requested path. We'll then add an "allows" header
				// to the matched route

				if($requestMethod === 'OPTIONS')
				{
					return [$this->optionsRoute($requestPath), []];
				}

				// Assign the route to the request

				$request->setRoute($route);

				// Return the matched route and parameters

				return [$route, $parameters];
			}
		}

		if($matched)
		{
			// We found a matching route but it does not allow the request method so we'll throw a 405 exception

			throw new MethodNotAllowedException($this->getAllowedMethodsForMatchingRoutes($requestPath));
		}
		else
		{
			// No routes matched so we'll throw a 404 exception

			throw new NotFoundException($requestMethod . ': ' . $requestPath);
		}
	}
}