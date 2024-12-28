<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\application;

use mako\config\Config;
use mako\file\FileSystem;
use mako\i18n\I18n;
use mako\syringe\Container;
use mako\view\ViewFactory;
use ReflectionClass;

use function dirname;
use function method_exists;
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
	protected string $path;

	/**
	 * File namespace.
	 */
	protected string $fileNamespace;

	/**
	 * Class namespace.
	 */
	protected string $classNamespace;

	/**
	 * Commands.
	 */
	protected array $commands = [];

	/**
	 * Constructor.
	 */
	public function __construct(
		protected Container $container
	) {
	}

	/**
	 * Returns the package name.
	 */
	public function getName(): string
	{
		return $this->packageName;
	}

	/**
	 * Returns the package namespace.
	 */
	public function getFileNamespace(): string
	{
		if (empty($this->fileNamespace)) {
			$this->fileNamespace = str_replace('/', '-', strtolower($this->packageName));
		}

		return $this->fileNamespace;
	}

	/**
	 * Returns the class namespace.
	 */
	public function getClassNamespace(bool $prefix = false): string
	{
		if (empty($this->classNamespace)) {
			$this->classNamespace = substr(static::class, 0, strrpos(static::class, '\\'));
		}

		return $prefix ? "\\{$this->classNamespace}" : $this->classNamespace;
	}

	/**
	 * Returns package path.
	 */
	public function getPath(): string
	{
		if (empty($this->path)) {
			$this->path = dirname((new ReflectionClass($this))->getFileName(), 2);
		}

		return $this->path;
	}

	/**
	 * Returns the path to the package configuration files.
	 */
	public function getConfigPath(): string
	{
		return "{$this->getPath()}/config";
	}

	/**
	 * Returns the path to the package i18n strings.
	 */
	public function getI18nPath(): string
	{
		return "{$this->getPath()}/resources/i18n";
	}

	/**
	 * Returns the path to the package views.
	 */
	public function getViewPath(): string
	{
		return "{$this->getPath()}/resources/views";
	}

	/**
	 * Returns the package commands.
	 */
	public function getCommands(): array
	{
		return $this->commands;
	}

	/**
	 * Gets executed at the end of the package boot sequence.
	 */
	protected function bootstrap(): void
	{
		// Nothing here
	}

	/**
	 * Boots the package.
	 */
	public function boot(): void
	{
		$fileSystem = $this->container->get(FileSystem::class);

		// Register configuration namespace

		if ($fileSystem->isDirectory($path = $this->getConfigPath())) {
			$configLoader = $this->container->get(Config::class)->getLoader();

			if (method_exists($configLoader, 'registerNamespace')) {
				$configLoader->registerNamespace($this->getFileNamespace(), $path);
			}
		}

		// Register i18n namespace

		if ($fileSystem->isDirectory($path = $this->getI18nPath()) && $this->container->has(I18n::class)) {
			$i18nLoader = $this->container->get(I18n::class)->getLoader();

			if (method_exists($i18nLoader, 'registerNamespace')) {
				$i18nLoader->registerNamespace($this->getFileNamespace(), $path);
			}
		}

		// Register view namespace

		if ($fileSystem->isDirectory($path = $this->getViewPath()) && $this->container->has(ViewFactory::class)) {
			$this->container->get(ViewFactory::class)->registerNamespace($this->getFileNamespace(), $path);
		}

		// Bootstrap package

		$this->bootstrap();
	}
}
