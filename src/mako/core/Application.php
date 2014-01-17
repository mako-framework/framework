<?php

namespace mako\core;

use \LogicException;

use \mako\core\Config;
use \mako\http\Request;
use \mako\http\Response;
use \mako\http\routing\Dispatcher;
use \mako\http\routing\Router;
use \mako\http\routing\Routes;
use \mako\http\routing\URLBuilder;
use \mako\security\Signer;

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
	 * Config instance.
	 * 
	 * @var \mako\core\Config
	 */

	protected $config;

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
	 * Starts the application and returns a singleton instance of the application.
	 * 
	 * @access  public
	 * @param   string                  $applicationPath  Application path
	 * @return  \mako\core\Application
	 */

	public static function start($applicationPath)
	{
		if(!empty(static::$instance))
		{
			throw new LogicException(vsprintf("%s(): The application has already been started.", [__METHOD__]));
		}

		return static::$instance = new static($applicationPath);
	}

	/**
	 * Returns singleton instance of the application.
	 * 
	 * @access  public
	 * @return  \mako\core\Application
	 */

	public static function instance()
	{
		if(empty(static::$instance))
		{
			throw new LogicException(vsprintf("%s(): The application has not been started yet.", [__METHOD__]));
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
	 * Register classes that are likely to be used in the dependency injection container.
	 * 
	 * @access  protected
	 */

	protected function registerCommon()
	{
		// Register the signer class

		$this->registerSingleton(['mako\security\Signer', 'signer'], function()
		{
			return new Signer($this->config->get('application.secret'));
		});

		// Register the request class

		$this->registerSingleton(['mako\http\Request', 'request'], function()
		{
			return new Request(['languages' => $this->config->get('application.languages')], $this->get('signer'));
		});

		// Register the response class

		$this->registerSingleton(['mako\http\Response', 'response'], 'mako\http\Response');

		// Register the route collection

		$this->registerSingleton(['mako\http\routing\Routes', 'routes'], 'mako\http\routing\Routes');

		// Register the URL builder

		$this->registerSingleton(['mako\http\routing\URLBuilder', 'urlbuilder'], function()
		{
			return new URLBuilder($this->get('request'), $this->get('routes'), $this->config->get('application.clean_urls'));
		});
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

		// Register config instance

		$this->registerInstance(['mako\core\Config', 'config'], $this->config = new Config($this->applicationPath));

		// Register common classes

		$this->registerCommon();

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
	 * Dispatches the request and returns its response.
	 * 
	 * @access  public
	 * @return  \mako\http\Response
	 */

	public function dispatch()
	{
		$request  = $this->get('request');
		$response = $this->get('response');

		// Override the application language?

		if(($language = $request->language()) !== null)
		{
			$this->setLanguage($language);
		}

		// Load routes

		$routes = $this->get('routes');

		$this->loadRoutes();

		// Route the request

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

		// Dispatch the request

		$this->dispatch()->send();
	}
}

/** -------------------- End of file -------------------- **/