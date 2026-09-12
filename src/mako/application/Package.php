<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\application;

use mako\application\services\Service;
use mako\config\Config;
use mako\config\loaders\NamespacedLoaderInterface as NamespacedConfigLoaderInterface;
use mako\file\FileSystem;
use mako\i18n\I18n;
use mako\i18n\loaders\NamespacedLoaderInterface as NamespacedI18nLoaderInterface;
use mako\reactor\CommandInterface;
use mako\syringe\Container;
use mako\view\ViewFactory;
use ReflectionClass;

use function dirname;
use function str_replace;
use function strrpos;
use function strtolower;
use function substr;

/**
 * Package.
 */
abstract class Package
{
	/**
	 * Package name.
	 */
	protected string $packageName;

	/**
	 * Package path.
	 */
	final protected string $path;

	/**
	 * File namespace.
	 */
	final protected string $fileNamespace;

	/**
	 * Class namespace.
	 */
	final protected string $classNamespace;

	/**
	 * Services.
	 *
	 * @var array{
	 *	core?: array<int, class-string<Service>>,
	 *	web?: array<int, class-string<Service>>,
	 *	cli?: array<int, class-string<Service>>
	 * }
	 */
	protected array $services = ['core' => [], 'web' => [], 'cli' => []];

	/**
	 * Commands.
	 *
	 * @var array<string, class-string<CommandInterface>>
	 */
	protected array $commands = [];

	/**
	 * Constructor.
	 */
	final public function __construct(
		protected Container $container
	) {
	}

	/**
	 * Returns the package name.
	 */
	final public function getName(): string
	{
		return $this->packageName;
	}

	/**
	 * Returns the package namespace.
	 */
	final public function getFileNamespace(): string
	{
		if (!isset($this->fileNamespace)) {
			$this->fileNamespace = str_replace('/', '-', strtolower($this->packageName));
		}

		return $this->fileNamespace;
	}

	/**
	 * Returns the class namespace.
	 */
	final public function getClassNamespace(bool $prefix = false): string
	{
		if (!isset($this->classNamespace)) {
			$this->classNamespace = substr(static::class, 0, strrpos(static::class, '\\'));
		}

		return $prefix ? "\\{$this->classNamespace}" : $this->classNamespace;
	}

	/**
	 * Returns package path.
	 */
	final public function getPath(): string
	{
		if (!isset($this->path)) {
			$this->path = dirname((new ReflectionClass($this))->getFileName(), 2);
		}

		return $this->path;
	}

	/**
	 * Returns the path to the package configuration files.
	 */
	final public function getConfigPath(): string
	{
		return "{$this->getPath()}/config";
	}

	/**
	 * Returns the path to the package i18n strings.
	 */
	final public function getI18nPath(): string
	{
		return "{$this->getPath()}/resources/i18n";
	}

	/**
	 * Returns the path to the package views.
	 */
	final public function getViewPath(): string
	{
		return "{$this->getPath()}/resources/views";
	}

	/**
	 * Returns the package services.
	 *
	 * @param  'cli'|'core'|'web'                $type
	 * @return array<int, class-string<Service>>
	 */
	final public function getServices(string $type): array
	{
		return $this->services[$type] ?? [];
	}

	/**
	 * Returns the package commands.
	 *
	 * @return array<string, class-string<CommandInterface>>
	 */
	final public function getCommands(): array
	{
		return $this->commands;
	}

	/**
	 * Boots the package.
	 */
	final public function boot(): void
	{
		$fileSystem = $this->container->get(FileSystem::class);

		// Register configuration namespace

		if ($fileSystem->isDirectory($path = $this->getConfigPath())) {
			$configLoader = $this->container->get(Config::class)->getLoader();

			if ($configLoader instanceof NamespacedConfigLoaderInterface) {
				$configLoader->registerNamespace($this->getFileNamespace(), $path);
			}
		}

		// Register i18n namespace

		if ($fileSystem->isDirectory($path = $this->getI18nPath()) && $this->container->has(I18n::class)) {
			$i18nLoader = $this->container->get(I18n::class)->getLoader();

			if ($i18nLoader instanceof NamespacedI18nLoaderInterface) {
				$i18nLoader->registerNamespace($this->getFileNamespace(), $path);
			}
		}

		// Register view namespace

		if ($fileSystem->isDirectory($path = $this->getViewPath()) && $this->container->has(ViewFactory::class)) {
			$this->container->get(ViewFactory::class)->registerNamespace($this->getFileNamespace(), $path);
		}
	}

	/**
	 * Initializes the package after its resource namespaces and services have been registered.
	 */
	public function bootstrap(): void
	{
		// Nothing here
	}
}
