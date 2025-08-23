<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\application;

use mako\application\exceptions\ApplicationException;
use mako\config\Config;
use mako\config\loaders\Loader;
use mako\file\FileSystem;
use mako\syringe\Container;

use function basename;
use function date_default_timezone_set;
use function mako\env;
use function mb_internal_encoding;
use function microtime;
use function rtrim;
use function setlocale;
use function sprintf;

/**
 * Application.
 */
abstract class Application
{
	/**
	 * Application start time.
	 */
	protected float $startTime;

	/**
	 * Container.
	 */
	protected Container $container;

	/**
	 * Config instance.
	 */
	protected Config $config;

	/**
	 * Application charset.
	 */
	protected string $charset;

	/**
	 * Application language.
	 */
	protected string $language;

	/**
	 * Application storage path.
	 */
	protected string $storagePath;

	/**
	 * Booted packages.
	 */
	protected array $packages = [];

	/**
	 * Constructor.
	 */
	public function __construct(
		protected string $applicationPath
	) {
		$this->startTime = microtime(true);

		$this->boot();
	}

	/**
	 * Clone.
	 */
	public function __clone()
	{
		$this->container = clone $this->container;

		$this->startTime = microtime(true);
	}

	/**
	 * Returns the application start time.
	 */
	public function getStartTime(): float
	{
		return $this->startTime;
	}

	/**
	 * Returns the container instance.
	 */
	public function getContainer(): Container
	{
		return $this->container;
	}

	/**
	 * Returns the config instance.
	 */
	public function getConfig(): Config
	{
		return $this->config;
	}

	/**
	 * Returns the application charset.
	 */
	public function getCharset(): string
	{
		return $this->charset;
	}

	/**
	 * Returns the application language.
	 */
	public function getLanguage(): string
	{
		return $this->language;
	}

	/**
	 * Sets the application language settings.
	 */
	public function setLanguage(array $language): void
	{
		$this->language = $language['strings'];

		foreach ($language['locale'] as $category => $locale) {
			setlocale($category, $locale);
		}
	}

	/**
	 * Gets the application path.
	 */
	public function getPath(): string
	{
		return $this->applicationPath;
	}

	/**
	 * Gets the application storage path.
	 */
	public function getStoragePath(): string
	{
		return $this->storagePath;
	}

	/**
	 * Returns all the application packages.
	 */
	public function getPackages(): array
	{
		return $this->packages;
	}

	/**
	 * Returns a package by its name.
	 */
	public function getPackage(string $package): Package
	{
		if (!isset($this->packages[$package])) {
			throw new ApplicationException(sprintf('Unknown package [ %s ].', $package));
		}

		return $this->packages[$package];
	}

	/**
	 * Returns the application namespace.
	 */
	public function getNamespace(bool $prefix = false): string
	{
		$namespace = basename(rtrim($this->applicationPath, '\\'));

		if ($prefix) {
			$namespace = "\\{$namespace}";
		}

		return $namespace;
	}

	/**
	 * Is the application running in the CLI?
	 */
	public function isCommandLine(): bool
	{
		return PHP_SAPI === 'cli';
	}

	/**
	 * Returns the Mako environment. NULL is returned if no environment is specified.
	 */
	public function getEnvironment(): ?string
	{
		return env('MAKO_ENV');
	}

	/**
	 * Configure.
	 */
	protected function configure(): void
	{
		$config = $this->config->get('application');

		// Set internal charset

		$this->charset = $config['charset'];

		mb_internal_encoding($this->charset);

		// Set default time zone

		date_default_timezone_set($config['timezone']);

		// Set locale information

		$this->setLanguage($config['default_language']);

		// Set storage path

		$this->storagePath = $config['storage_path'] ?? "{$this->applicationPath}/storage";
	}

	/**
	 * Registers services in the container.
	 */
	protected function serviceRegistrar(string $type): void
	{
		foreach ($this->config->get("application.services.{$type}") as $service) {
			(new $service($this, $this->container, $this->config))->register();
		}
	}

	/**
	 * Registers command line services.
	 */
	protected function registerCLIServices(): void
	{
		$this->serviceRegistrar('cli');
	}

	/**
	 * Registers web services.
	 */
	protected function registerWebServices(): void
	{
		$this->serviceRegistrar('web');
	}

	/**
	 * Register services in the container.
	 */
	protected function registerServices(): void
	{
		// Register core services

		$this->serviceRegistrar('core');

		// Register environment specific services

		if ($this->isCommandLine()) {
			$this->registerCLIServices();
		}
		else {
			$this->registerWebServices();
		}
	}

	/**
	 * Loads the application bootstrap file.
	 */
	protected function bootstrap(): void
	{
		(function ($app, $container): void {
			include "{$this->applicationPath}/bootstrap.php";
		})($this, $this->container);
	}

	/**
	 * Boots packages.
	 */
	protected function packageBooter(string $type): void
	{
		foreach ($this->config->get("application.packages.{$type}") as $package) {
			$package = new $package($this->container);

			$package->boot();

			$this->packages[$package->getName()] = $package;
		}
	}

	/**
	 * Boots command line packages.
	 */
	protected function bootCliPackages(): void
	{
		$this->packageBooter('cli');
	}

	/**
	 * Boots web packages.
	 */
	protected function bootWebPackages(): void
	{
		$this->packageBooter('web');
	}

	/**
	 * Boot packages.
	 */
	protected function bootPackages(): void
	{
		$this->packageBooter('core');

		// Register environment specific services

		if ($this->isCommandLine()) {
			$this->bootCliPackages();
		}
		else {
			$this->bootWebPackages();
		}
	}

	/**
	 * Creates a container instance.
	 */
	protected function containerFactory(): Container
	{
		return new Container;
	}

	/**
	 * Creates a configuration instance.
	 */
	protected function configFactory(): Config
	{
		return new Config(new Loader($this->container->get(FileSystem::class), "{$this->applicationPath}/config"), $this->getEnvironment());
	}

	/**
	 * Sets up the framework core.
	 */
	protected function initialize(): void
	{
		// Create container instance and register it in itself so that it can be injected

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
	protected function boot(): void
	{
		// Set up the framework core

		$this->initialize();

		// Configure

		$this->configure();

		// Register services in the IoC injection container

		$this->registerServices();

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
