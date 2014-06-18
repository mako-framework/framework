<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\application;

use \mako\error\handlers\WebHandler;
use \mako\http\routing\Dispatcher;
use \mako\http\routing\Router;

/**
 * Web application.
 *
 * @author  Frederic G. Østby
 */

class Web extends \mako\application\Application
{
	/**
	 * Register the error handler.
	 * 
	 * @access  protected
	 */

	protected function registerErrorHandler()
	{
		$this->container->get('errorHandler')->handle('\Exception', function($exception)
		{
			// Create handler instance

			$handler = new WebHandler($exception);

			$handler->setRequest($this->container->get('request'));

			$handler->setResponse($this->container->getFresh('response'));

			$handler->setCharset($this->getCharset());

			// Set logger if error logging is enabled

			if($this->config->get('application.error_handler.log_errors'))
			{
				$handler->setLogger($this->container->get('logger'));
			}

			// Handle the error
			
			return $handler->handle($this->config->get('application.error_handler.display_errors'));
		});
	}

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