<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\core;

use \mako\http\routing\Dispatcher;
use \mako\http\routing\Router;

/**
 * Web application.
 *
 * @author  Frederic G. Østby
 */

class WebApplication extends \mako\core\Application
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	// Nothing here

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	// Nothing here

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	 * Runs the application.
	 * 
	 * @access  public
	 */

	public function run()
	{
		ob_start();

		// Dispatch the request

		$request = $this->container->get('request');

		// Override the application language?

		if(($language = $request->language()) !== null)
		{
			$this->setLanguage($language);
		}

		// Load routes

		$routes = $this->loadRoutes();

		// Route the request

		$router = new Router($request, $routes);

		$route = $router->route();

		// Dispatch the request and send the response

		(new Dispatcher($routes, $route, $request, $this->container->get('response'), $this->container))->dispatch()->send();
	}
}