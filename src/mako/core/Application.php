<?php

namespace mako\core;

use \mako\http\Request;
use \mako\http\Response;
use \mako\http\routing\Dispatcher;
use \mako\http\routing\Router;
use \mako\http\routing\Routes;

/**
 * Application.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class Application extends \mako\core\Syringe
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	/**
	 * Singleton instance.
	 * 
	 * @var \mako\core\Application
	 */

	protected static $instance;

	/**
	 * Application language.
	 * 
	 * @var string
	 */

	protected $language;

	/**
	 * Application path.
	 * 
	 * @var string
	 */

	protected $applicationPath;

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	 * Constructor.
	 * 
	 * @access  public
	 * @param   string  $applicationPath  Application path
	 */

	public function __construct($applicationPath)
	{
		$this->applicationPath = $applicationPath;

		$this->boot();
	}

	/**
	 * Returns singleton instance of the application.
	 * 
	 * @access  public
	 * @param   string                  $applicationPath  Application path
	 * @return  \mako\core\Application
	 */

	public static function instance($applicationPath)
	{
		if(empty(static::$instance))
		{
			static::$instance = new static($applicationPath);
		}

		return static::$instance;
	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	 * Returns the application language.
	 * 
	 * @access  public
	 * @return  string
	 */

	public function getLanguage()
	{
		return $this->language;
	}

	/**
	 * Sets the application language.
	 * 
	 * @access  public
	 * @param   string  $language  Application language
	 */

	public function setLanguage($language)
	{
		$this->language = $language;
	}

	/**
	 * Gets the application path.
	 * 
	 * @access  public
	 * @return  string
	 */

	public function getApplicationPath()
	{
		return $this->applicationPath;
	}

	/**
	 * Loads the application bootstrap file.
	 * 
	 * @access  protected
	 */

	protected function bootstrap()
	{
		$bootstrap = function($app)
		{
			include $this->applicationPath . '/bootstrap.php';
		};

		$bootstrap($this);
	}

	/**
	 * Boot the application core.
	 * 
	 * @access  protected
	 */

	protected function boot()
	{
		// Register self so that the application instance can be resolved

		$this->registerInstance(['mako\core\Application', 'app'], $this);

		// Load application bootstrap file

		$this->bootstrap();
	}

	/**
	 * Load application paths.
	 * 
	 * @access  public
	 */

	public function loadRoutes()
	{
		$loader = function($app, $routes)
		{
			include $this->applicationPath . '/routes.php';
		};

		$loader($this, $this->get('routes'));
	}

	/**
	 * Dispatches a request and returns its response.
	 * 
	 * @access  public
	 * @param   \mako\http\Request   $request  (optional) Request instance
	 * @return  \mako\http\Response
	 */

	public function dispatch(Request $request = null)
	{
		// If no request instance is passed then we'll use the default request and response instances

		if($request === null)
		{
			$request  = $this->get('request');
			$response = $this->get('response');
		}
		else
		{
			$response = new Response($request);
		}

		// Route the request

		$routes = $this->get('routes');

		$router = new Router($request, $routes);

		$route = $router->route();

		// Dispatch the request

		$response->body((new Dispatcher($routes, $route, $request, $response, $this))->dispatch());

		// Return the response

		return $response;
	}

	/**
	 * Runs the application.
	 * 
	 * @access  public
	 */

	public function run()
	{
		ob_start();

		// Register route collection

		$this->registerInstance(['mako\http\routing\Routes', 'routes'], new Routes);

		// Create request instance

		$request = new Request([], $this->get('config')->get('application.languages'));
		
		// Override the application language?

		if(($language = $request->language()) !== null)
		{
			$this->setLanguage($language);
		}

		// Register the main request and response instances

		$this->registerInstance(['mako\http\Request', 'request'], $request);
		$this->registerInstance(['mako\http\Response', 'response'], new Response($request));

		// Load routes and dispatch the request

		$this->loadRoutes();

		$this->dispatch()->send();
	}
}

/** -------------------- End of file -------------------- **/