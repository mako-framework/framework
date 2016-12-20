<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\application;

use ReflectionClass;

use mako\common\NamespacedFileLoaderTrait;
use mako\syringe\Container;

/**
 * Package.
 *
 * @author Frederic G. Østby
 */
abstract class Package
{
	/**
	 * IoC container instance
	 *
	 * @var \mako\syringe\Container
	 */
	protected $container;

	/**
	 * Package name.
	 *
	 * @var string
	 */
	protected $packageName;

	/**
	 * Package path.
	 *
	 * @var string
	 */
	protected $path;

	/**
	 * File namespace.
	 *
	 * @var string
	 */
	protected $fileNamespace;

	/**
	 * Class namespace.
	 *
	 * @var string
	 */
	protected $classNamespace;

	/**
	 * Commands.
	 *
	 * @var array
	 */
	protected $commands = [];

	/**
	 * Constructor.
	 *
	 * @access public
	 * @param \mako\syringe\Container $container IoC container instance
	 */
	public function __construct(Container $container)
	{
		$this->container = $container;
	}

	/**
	 * Returns the package name.
	 *
	 * @access public
	 * @return string
	 */
	public function getName(): string
	{
		return $this->packageName;
	}

	/**
	 * Returns the package namespace.
	 *
	 * @access public
	 * @return string
	 */
	public function getFileNamespace(): string
	{
		if($this->fileNamespace === null)
		{
			$this->fileNamespace = str_replace('/', '-', strtolower($this->packageName));
		}

		return $this->fileNamespace;
	}

	/**
	 * Returns the class namespace.
	 *
	 * @access public
	 * @param  bool   $prefix Prefix the namespace with a slash?
	 * @return string
	 */
	public function getClassNamespace(bool $prefix = false): string
	{
		if($this->classNamespace === null)
		{
			$this->classNamespace = substr(static::class, 0, strrpos(static::class, '\\'));
		}

		return $prefix ? '\\' . $this->classNamespace : $this->classNamespace;
	}

	/**
	 * Returns package path.
	 *
	 * @access protected
	 * @return string
	 */
	public function getPath(): string
	{
		if($this->path === null)
		{
			$this->path = realpath(dirname((new ReflectionClass($this))->getFileName()) . '/..');
		}

		return $this->path;
	}

	/**
	 * Returns the path to the package configuration files.
	 *
	 * @access public
	 * @return string
	 */
	public function getConfigPath(): string
	{
		return realpath($this->getPath() . '/config');
	}

	/**
	 * Returns the path to the package i18n strings.
	 *
	 * @access public
	 * @return string
	 */
	public function getI18nPath(): string
	{
		return realpath($this->getPath() . '/resources/i18n');
	}

	/**
	 * Returns the path to the package views.
	 *
	 * @access public
	 * @return string
	 */
	public function getViewPath(): string
	{
		return realpath($this->getPath() . '/resources/views');
	}

	/**
	 * Returns the package commands.
	 *
	 * @access public
	 * @return array
	 */
	public function getCommands(): array
	{
		return $this->commands;
	}

	/**
	 * Gets executed at the end of the package boot sequence.
	 *
	 * @access protected
	 */
	protected function bootstrap()
	{
		// Nothing here
	}

	/**
	 * Boots the package.
	 *
	 * @access public
	 */
	public function boot()
	{
		// Register configuration namespace

		$configLoader = $this->container->get('config')->getLoader();

		if(in_array(NamespacedFileLoaderTrait::class, class_uses($configLoader)))
		{
			$configLoader->registerNamespace($this->getFileNamespace(), $this->getConfigPath());
		}

		// Register i18n namespace

		if(($path = $this->getI18nPath()) !== false && $this->container->has('i18n'))
		{
			$i18nLoader = $this->container->get('i18n')->getLoader();

			if(in_array(NamespacedFileLoaderTrait::class, class_uses($i18nLoader)))
			{
				$i18nLoader->registerNamespace($this->getFileNamespace(), $path);
			}
		}

		// Register view namespace

		if(($path = $this->getViewPath()) !== false && $this->container->has('view'))
		{
			$this->container->get('view')->registerNamespace($this->getFileNamespace(), $path);
		}

		// Bootstrap package

		$this->bootstrap();
	}
}
