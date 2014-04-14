<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\http\routing;

use \mako\http\Request;
use \mako\http\Response;
use \mako\http\routing\Routes;
use \mako\http\routing\PageNotFoundException;
use \mako\http\routing\MethodNotAllowedException;

/**
 * Router.
 * 
 * @author  Frederic G. Østby
 */

class Router
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	/**
	 * Request.
	 * 
	 * @var \mako\http\Request
	 */

	protected $request;

	/**
	 * Route collection.
	 * 
	 * @var \mako\http\routing\Routes
	 */

	protected $routes;

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	 * Constructor.
	 * 
	 * @access  public
	 * @param   \mako\http\Request         $request  Request
	 * @param   \mako\http\routing\Routes  $routes   Routes
	 */

	public function __construct(Request $request, Routes $routes)
	{
		$this->request = $request;
		$this->routes  = $routes;
	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	 * Redirects to the requested path and adds a trailing slash.
	 * 
	 * @access  protected
	 * @param   string     $requestPath  The requested path
	 */

	protected function addTrailingSlash($requestPath)
	{
		$response = new Response($this->request);

		$url = $this->request->baseURL() . rtrim('/' . $this->request->languagePrefix(), '/') . $requestPath . '/';

		$get = $this->request->get();

		if(!empty($get))
		{
			$url = $url . '?' . http_build_query($get);
		}

		$response->body($response->redirect($url)->status(301));

		$response->send();

		exit;
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
			if($route->isMatch($requestPath))
			{
				$methods = array_merge($methods, $route->getMethods());
			}
		}

		return array_unique($methods);
	}

	/**
	 * Matches and returns the appropriate route.
	 * 
	 * @access  public
	 * @return  \mako\http\routing\Route
	 */

	public function route()
	{
		$matched = false;

		$requestMethod = $this->request->method();

		$requestPath = $this->request->path();

		foreach($this->routes->getRoutes() as $route)
		{
			if($route->isMatch($requestPath))
			{
				if(!$route->allows($requestMethod))
				{
					$matched = true;

					continue;
				}

				// Redirect to URL with trailing slash if the route should have one

				if($route->hasTrailingSlash() && !empty($requestPath) && substr($requestPath, -1) !== '/')
				{
					$this->addTrailingSlash($requestPath);
				}

				// If this is an "OPTIONS" request then well collect all the allowed request methods
				// from all routes matching the requested path. We'll then add an "allows" header
				// to the matched route

				if($requestMethod === 'OPTIONS')
				{
					$methods = $this->getAllowedMethodsForMatchingRoutes($requestPath);

					$route->headers(['allow' => implode(',', $methods)]);
				}

				// Return the matched route

				return $route;
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
			
			throw new PageNotFoundException();
		}
	}
}