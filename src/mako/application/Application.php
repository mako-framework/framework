<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\application;

use LogicException;
use RuntimeException;

use mako\application\Package;
use mako\autoloading\AliasLoader;
use mako\config\Config;
use mako\config\loaders\Loader;
use mako\file\FileSystem;
use mako\syringe\Container;

/**
 * Application.
 *
 * @author Frederic G. Østby
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
	 * Booted packages.
	 *
	 * @var array
	 */
	protected $packages = [];

	/**
	 * Constructor.
	 *
	 * @param string $applicationPath Application path
	 */
	public function __construct(string $applicationPath)
	{
		$this->applicationPath = $applicationPath;

		$this->boot();
	}

	/**
	 * Starts the application and returns a singleton instance of the application.
	 *
	 * @param  string                        $applicationPath Application path
	 * @return \mako\application\Application
	 */
	public static function start(string $applicationPath)
	{
		if(!empty(static::$instance))
		{
			throw new LogicException('The application has already been started.');
		}

		return static::$instance = new static($applicationPath);
	}

	/**
	 * Returns a singleton instance of the application.
	 *
	 * @return \mako\application\Application
	 */
	public static function instance()
	{
		if(empty(static::$instance))
		{
			throw new LogicException('The application has not been started yet.');
		}

		return static::$instance;
	}

	/**
	 * Returns the IoC container instance.
	 *
	 * @return \mako\syringe\Container
	 */
	public function getContainer(): Container
	{
		return $this->container;
	}

	/**
	 * Returns the config instance.
	 *
	 * @return \mako\config\Config
	 */
	public function getConfig(): Config
	{
		return $this->config;
	}

	/**
	 * Returns the application charset.
	 *
	 * @return string
	 */
	public function getCharset(): string
	{
		return $this->charset;
	}

	/**
	 * Returns the application language.
	 *
	 * @return string
	 */
	public function getLanguage(): string
	{
		return $this->language;
	}

	/**
	 * Sets the application language settings.
	 *
	 * @param array $language Application language settings
	 */
	public function setLanguage(array $language)
	{
		$this->language = $language['strings'];

		foreach($language['locale'] as $category => $locale)
		{
			setlocale($category, $locale);
		}
	}

	/**
	 * Gets the application path.
	 *
	 * @return string
	 */
	public function getPath(): string
	{
		return $this->applicationPath;
	}

	/**
	 * Returns all the application packages.
	 *
	 * @return array
	 */
	public function getPackages(): array
	{
		return $this->packages;
	}

	/**
	 * Returns a package by its name.
	 *
	 * @param  string                    $package Package name
	 * @return \mako\application\Package
	 */
	public function getPackage(string $package): Package
	{
		if(!isset($this->packages[$package]))
		{
			throw new RuntimeException(vsprintf('Unknown package [ %s ].', [$package]));
		}

		return $this->packages[$package];
	}

	/**
	 * Returns the application namespace.
	 *
	 * @param  bool   $prefix Prefix the namespace with a slash?
	 * @return string
	 */
	public function getNamespace(bool $prefix = false)
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
	 * @return bool
	 */
	public function isCommandLine(): bool
	{
		return PHP_SAPI === 'cli';
	}

	/**
	 * Returns the Mako environment. NULL is returned if no environment is specified.
	 *
	 * @return string|null
	 */
	public function getEnvironment()
	{
		return getenv('MAKO_ENV') ?: null;
	}

	/**
	 * Configure.
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
	}

	/**
	 * Registers services in the IoC container.
	 *
	 * @param string $type Service type
	 */
	protected function serviceRegistrar(string $type)
	{
		foreach($this->config->get('application.services.' . $type) as $service)
		{
			(new $service($this->container))->register();
		}
	}

	/**
	 * Registers command line services.
	 */
	protected function registerCLIServices()
	{
		$this->serviceRegistrar('cli');
	}

	/**
	 * Registers web services.
	 */
	protected function registerWebServices()
	{
		$this->serviceRegistrar('web');
	}

	/**
	 * Register services in the IoC container.
	 */
	protected function registerServices()
	{
		// Register core services

		$this->serviceRegistrar('core');

		// Register environment specific services

		if($this->isCommandLine())
		{
			$this->registerCLIServices();
		}
		else
		{
			$this->registerWebServices();
		}
	}

	/**
	 * Registers class aliases.
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
	 * Boots packages.
	 *
	 * @param string $type Package type
	 */
	protected function packageBooter(string $type)
	{
		foreach($this->config->get('application.packages.' . $type) as $package)
		{
			$package = new $package($this->container);

			$package->boot();

			$this->packages[$package->getName()] = $package;
		}
	}

	/**
	 * Boots command line packages.
	 */
	protected function bootCliPackages()
	{
		$this->packageBooter('cli');
	}

	/**
	 * Boots web packages.
	 */
	protected function bootWebPackages()
	{
		$this->packageBooter('web');
	}

	/**
	 * Boot packages.
	 */
	protected function bootPackages()
	{
		$this->packageBooter('core');

		// Register environment specific services

		if($this->isCommandLine())
		{
			$this->bootCliPackages();
		}
		else
		{
			$this->bootWebPackages();
		}
	}

	/**
	 * Creates a container instance.
	 *
	 * @return \mako\syringe\Container
	 */
	protected function containerFactory(): Container
	{
		return new Container;
	}

	/**
	 * Creates a configuration instance.
	 *
	 * @return \mako\config\Config
	 */
	protected function configFactory(): Config
	{
		return new Config(new Loader($this->container->get('fileSystem'), $this->applicationPath . '/config'), $this->getEnvironment());
	}

	/**
	 * Sets up the framework core.
	 */
	protected function initialize()
	{
		// Create IoC container instance and register it in itself so that it can be injected

		$this->container = $this->containerFactory();

		$this->container->registerInstance([Container::class, 'container'], $this->container);

		// Register self so that the application instance can be injected

		$this->container->registerInstance([Application::class, 'app'], $this);

		// Register file system instance

		$this->container->registerInstance([FileSystem::class, 'fileSystem'], new FileSystem);

		// Register config instance

		$this->config = $this->configFactory();

		$this->container->registerInstance([Config::class, 'config'], $this->config);
	}

	/**
	 * Boots the application.
	 */
	protected function boot()
	{
		// Set up the framework core

		$this->initialize();

		// Configure

		$this->configure();

		// Register services in the IoC injection container

		$this->registerServices();

		// Register class aliases

		$this->registerClassAliases();

		// Load the application bootstrap file

		$this->bootstrap();

		// Boot packages

		$this->bootPackages();
	}

	/**
	 * Runs the application.
	 */
	abstract public function run();
}
