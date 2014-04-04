<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\core;

use \Closure;
use \LogicException;

use \mako\core\Config;
use \mako\core\error\handlers\WebHandler;
use \mako\core\error\handlers\CLIHandler;
use \mako\http\routing\Dispatcher;
use \mako\http\routing\Router;

use \Monolog\Handler\HandlerInterface;

/**
 * Application.
 *
 * @author  Frederic G. Østby
 */

abstract class Application extends \mako\syringe\Syringe
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	/**
	 * Singleton instance of self.
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
	 * Application charset.
	 * 
	 * @var string
	 */

	protected $charset;

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
	 * Returns a singleton instance of the application.
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
	 * Returns the config instance.
	 * 
	 * @return \mako\core\Config
	 */

	public function getConfig()
	{
		return $this->config;
	}

	/**
	 * Returns the application charset.
	 * 
	 * @access  public
	 * @return  string
	 */

	public function getCharset()
	{
		return $this->charset;
	}

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
	 * Configure.
	 * 
	 * @access  protected
	 */

	protected function configure()
	{
		$config = $this->config->get('application');

		// Set internal charset

		$this->charset = $config['charset'];

		mb_language('uni');
		mb_regex_encoding($this->charset);
		mb_internal_encoding($this->charset);

		// Set default timezone

		date_default_timezone_set($config['timezone']);

		// Set locale information

		$this->setLanguage($config['default_language']);

		setlocale(LC_ALL, $config['locale']['locales']);

		if($config['locale']['lc_numeric'] === false)
		{
			setlocale(LC_NUMERIC, 'C');
		}
	}

	/**
	 * Register services in the dependency injection container.
	 * 
	 * @access  protected
	 */

	protected function registerServices()
	{
		foreach($this->config->get('application.services') as $service)
		{
			(new $service($this))->register();
		}
	}

	/**
	 * Register the error handler.
	 * 
	 * @access  protected
	 */

	protected function registerErrorHandler()
	{
		$this->get('errorhandler')->handle('\Exception', function($exception)
		{
			// Create handler instance

			if(PHP_SAPI === 'cli')
			{
				$handler = new CLIHandler($exception);
			}
			else
			{
				$handler = new WebHandler($exception);

				$handler->setRequest($this->get('request'));

				$handler->setResponse($this->getFresh('response'));

				$handler->setCharset($this->getCharset());
			}

			// Set logger if error logging is enabled

			if($this->config->get('application.error_handler.log_errors'))
			{
				$handler->setLogger($this->get('logger'));
			}

			// Handle the error
			
			return $handler->handle($this->config->get('application.error_handler.display_errors'));
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
	 * Boots the application.
	 * 
	 * @access  protected
	 */

	protected function boot()
	{
		// Register self so that the application instance can be injected

		$this->registerInstance(['mako\core\Application', 'app'], $this);

		// Register config instance

		$this->registerInstance(['mako\core\Config', 'config'], $this->config = new Config($this->applicationPath));

		// Configure

		$this->configure();

		// Register services in the dependency injection container

		$this->registerServices();

		// Register error handler

		$this->registerErrorHandler();

		// Load the application bootstrap file

		$this->bootstrap();
	}

	/**
	 * Prepends an exception handler to the stack.
	 * 
	 * @access  public
	 * @param   string    $exception  Exception type
	 * @param   \Closure  $handler    Exception handler
	 */

	public function handle($exception, Closure $handler)
	{
		$this->get('errorhandler')->handle($exception, $handler);
	}

	/**
	 * Attaches a monolog log handler to the stack.
	 * 
	 * @access  public
	 * @param   \Monolog\Handler\HandlerInterface $logger  Monolog log handler instance
	 */

	public function attachLogger(HandlerInterface $logger)
	{
		$this->get('logger')->pushHandler($logger);
	}

	/**
	 * Loads application routes.
	 * 
	 * @access  protected
	 */

	protected function loadRoutes()
	{
		$loader = function($app, $routes)
		{
			include $this->applicationPath . '/routes.php';

			return $routes;
		};

		return $loader($this, $this->get('routes'));
	}

	/**
	 * Runs the application.
	 * 
	 * @access  public
	 */

	abstract public function run();
}