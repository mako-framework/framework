<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\application;

use \LogicException;

use \mako\autoloading\AliasLoader;
use \mako\config\Config;
use \mako\file\FileSystem;
use \mako\syringe\Container;

/**
 * Application.
 *
 * @author  Frederic G. Østby
 */

abstract class Application
{
	/**
	 * Singleton instance of self.
	 * 
	 * @var \mako\application\Application
	 */

	protected static $instance;

	/**
	 * IoC container instance.
	 * 
	 * @var \mako\syringe\Container;
	 */

	protected $container;

	/**
	 * Config instance.
	 * 
	 * @var \mako\config\Config
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
	 * @param   string                         $applicationPath  Application path
	 * @return  \mako\application\Application
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
	 * @return  \mako\application\Application
	 */

	public static function instance()
	{
		if(empty(static::$instance))
		{
			throw new LogicException(vsprintf("%s(): The application has not been started yet.", [__METHOD__]));
		}

		return static::$instance;
	}
	
	/**
	 * Returns the IoC container instance.
	 * 
	 * @access  public
	 * @return  \mako\syringe\Container
	 */

	public function getContainer()
	{
		return $this->container;
	}

	/**
	 * Returns the config instance.
	 * 
	 * @access  public
	 * @return  \mako\config\Config
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
	 * Returns the application namespace.
	 * 
	 * @access  public
	 * @param   boolean  $prefix  (optional) Prefix the namespace with a slash?
	 */

	public function getApplicationNamespace($prefix = false)
	{
		$namespace = basename(rtrim($this->applicationPath, '\\'));

		if($prefix)
		{
			$namespace = '\\' . $namespace;
		}

		return $namespace;
	}

	/**
	 * Is the application running in the CLI?
	 * 
	 * @access  public
	 * @return  boolean
	 */

	public function isCommandLine()
	{
		return PHP_SAPI === 'cli';
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
	 * Register services in the IoC container.
	 * 
	 * @access  protected
	 */

	protected function registerServices()
	{
		foreach($this->config->get('application.services') as $service)
		{
			(new $service($this->container))->register();
		}
	}

	/**
	 * Registers class aliases.
	 * 
	 * @access  protected
	 */

	protected function registerClassAliases()
	{
		$aliases = $this->config->get('application.class_aliases');

		if(!empty($aliases))
		{
			$aliasLoader = new AliasLoader($aliases);

			spl_autoload_register([$aliasLoader, 'load']);
		}
	}

	/**
	 * Loads the application bootstrap file.
	 * 
	 * @access  protected
	 */

	protected function bootstrap()
	{
		$bootstrap = function($app, $container)
		{
			include $this->applicationPath . '/bootstrap.php';
		};

		$bootstrap($this, $this->container);
	}

	/**
	 * Boots the application.
	 * 
	 * @access  protected
	 */

	protected function boot()
	{
		// Create IoC container instance and register it in itself so that it can be injected

		$this->container = new Container();

		$this->container->registerInstance(['mako\syringe\Container', 'container'], $this->container);

		// Register self so that the application instance can be injected

		$this->container->registerInstance(['mako\application\Application', 'app'], $this);

		// Register file system instance

		$this->container->registerInstance(['mako\file\FileSystem', 'fileSystem'], $fileSystem = new FileSystem());

		// Register config instance

		$this->container->registerInstance(['mako\config\Config', 'config'], $this->config = new Config($fileSystem, $this->applicationPath));

		// Configure

		$this->configure();

		// Register services in the IoC injection container

		$this->registerServices();

		// Register class aliases

		$this->registerClassAliases();

		// Load the application bootstrap file

		$this->bootstrap();
	}

	/**
	 * Loads application routes.
	 * 
	 * @access  protected
	 */

	protected function loadRoutes()
	{
		$loader = function($app, $container, $routes)
		{
			include $this->applicationPath . '/routes.php';

			return $routes;
		};

		return $loader($this, $this->container, $this->container->get('routes'));
	}

	/**
	 * Runs the application.
	 * 
	 * @access  public
	 */

	abstract public function run();
}