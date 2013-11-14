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
		$matched = false;

		$routes = Routes::getRoutes();

		$requestMethod = $this->request->method();

		$requestedRoute = $this->request->path();

		foreach($routes as $route)
		{
			if($route->isMatch($requestedRoute))
			{
				if(!$route->allows($requestMethod))
				{
					$matched = true;

					continue;
				}

				if($route->hasTrailingSlash() && substr($requestedRoute, -1) !== '/' && $this->request->isMain())
				{
					// Redirect to URL with trailing slash if the route should have one

					$response = new Response($this->request, $this->request->response()->redirect($requestedRoute . '/', array(), $this->request->get())->status(301));

					$response->send();

					exit;
				}

				return $route;
			}
		}

		if($matched)
		{
			// We found a matching route but it does not allow the request method so we'll throw a 405 exception

			throw new MethodNotAllowedException($route->getMethods());
		}
		else
		{
			// No routes matched so we'll throw a 404 exception
			
			throw new PageNotFoundException();
		}
	}
}

/** -------------------- End of file -------------------- **/