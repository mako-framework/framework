<?php

namespace mako\core;

use \mako\http\Request;
use \mako\http\RequestException;
use \mako\core\Config;
use \mako\core\DebugToolbar;
use \mako\core\errors\ErrorHandler;
use \mako\core\errors\handlers\RequestExceptionHandler;

/**
 * Mako.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class App
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------
	
	// Nothing here
	
	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------
	
	/**
	 * Constructor.
	 *
	 * @access  public
	 */
	
	public function __construct()
	{
		// Nothing here
	}
	
	//---------------------------------------------
	// Class methods
	//---------------------------------------------
	
	/**
	 * Executes request and sends response.
	 *
	 * @access  public
	 * @param   string  $route  (optional) Route passed to the request handler.
	 */
	
	public function run($route = null)
	{
		// Start output buffering

		ob_start();

		// Register the RequestException handler

		ErrorHandler::handle('\mako\http\RequestException', function($exception)
		{
			$handler = new RequestExceptionHandler($exception);

			$handler->handle();

			return true; // Return true to stop further handling of the RequestException
		});

		// Create request handler instance

		$request = new Request();

		// Check if the application is offline

		if(file_exists(MAKO_APPLICATION_PATH . '/storage/offline'))
		{
			throw new RequestException(503);
		}

		// Include routes

		include MAKO_APPLICATION_PATH . '/routes.php';

		// Add debug toolbar to response?

		if(Config::get('application.debug_toolbar') === true && $request->isAjax() === false)
		{
			$request->response()->filter(function($body)
			{
				return str_replace('</body>', DebugToolbar::render() . '</body>', $body);
			});
		}

		// Execute the request

		$request->execute()->send();
	}
}

/** -------------------- End of file -------------------- **/