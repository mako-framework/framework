<?php

namespace mako\http\routing;

use \mako\http\Request;
use \mako\http\RequestException;

/**
 * Route.
 * 
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
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

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	 * Constructor.
	 * 
	 * @access  public
	 * @param   \mako\http\Request  $request  Request
	 */

	public function __construct(Request $request)
	{
		$this->request = $request;
	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	 * Checks if the route could have responded if the request method was different.
	 * 
	 * @access  protected
	 * @return  boolean
	 */

	protected function canRespondToDifferentMethod()
	{
		$methods = array('HEAD', 'GET', 'POST', 'PUT', 'PATCH', 'DELETE');

		// Remove the current request method from the array of possible methods

		$methods = array_diff($methods, array($this->request->method()));

		// Check if the route could have matched had the 
		// request method been any of the remaining ones

		$requestedRoute = $this->request->route();

		foreach($methods as $method)
		{
			$routes = Routes::getRoutes($method);

			foreach($routes as $route)
			{
				if($route->isMatch($requestedRoute))
				{
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Matches and returns the appropriate route.
	 * 
	 * @access  public
	 * @return  \mako\http\routing\Route
	 */

	public function route()
	{
		$routes = Routes::getRoutes($this->request->method());

		$requestedRoute = $this->request->route();

		foreach($routes as $route)
		{
			if($route->isMatch($requestedRoute))
			{
				return $route;
			}
		}

		// Throw a 405 exception if the route could have
		// been matched had the request method been different

		if($this->canRespondToDifferentMethod())
		{
			throw new RequestException(405);
		}

		// The route could have not been matched using a different request method
		// so we'll just throw a 404 exception
		
		throw new RequestException(404);
	}
}

/** -------------------- End of file -------------------- **/