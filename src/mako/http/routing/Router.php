<?php

namespace mako\http\routing;

use \mako\http\Request;
use \mako\http\Response;
use \mako\http\routing\PageNotFoundException;
use \mako\http\routing\MethodNotAllowedException;
use \mako\http\routing\URL;

/**
 * Router.
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
	 * Matches and returns the appropriate route.
	 * 
	 * @access  public
	 * @return  \mako\http\routing\Route
	 */

	public function route()
	{
		$routes = Routes::getRoutes();

		$requestMethod = $this->request->method();

		$requestedRoute = $this->request->route();

		foreach($routes as $route)
		{
			if($route->isMatch($requestedRoute))
			{
				if(!$route->allows($requestMethod))
				{
					// The matched route does not allow the request method so we'll throw an exception

					throw new MethodNotAllowedException($route->getMethods());
				}

				if($route->hasTrailingSlash() && substr($requestedRoute, -1) !== '/')
				{
					// Redirect to URL with trailing slash if the route should have one

					Response::factory()->redirect(URL::to($requestedRoute . '/', $_GET, '&'), 301);
				}

				return $route;
			}
		}

		// No routes matched so we'll throw an exception
		
		throw new PageNotFoundException();
	}
}

/** -------------------- End of file -------------------- **/