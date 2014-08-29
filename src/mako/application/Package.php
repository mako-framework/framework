<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\application;

use \ReflectionClass;

use \mako\syringe\Container;

/**
 * Package.
 *
 * @author  Frederic G. Østby
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
	 * Package "src" directory.
	 * 
	 * @var string
	 */

	protected $srcDir;

	/**
	 * Package namespace.
	 * 
	 * @var string
	 */

	protected $namespace;

	/**
	 * Constructor.
	 * 
	 * @access  public
	 * @param   \mako\syringe\Container  $container  IoC container instance
	 */

	public function __construct(Container $container)
	{
		$this->container = $container;
	}

	/**
	 * Returns the package name.
	 * 
	 * @access  public
	 * @return  string
	 */

	public function getPackageName()
	{
		return $this->packageName;
	}

	/**
	 * Returns the package namespace.
	 * 
	 * @access  public
	 * @return  string
	 */

	public function getNamespace()
	{
		if($this->namespace === null)
		{
			$this->namespace = strtolower(end(explode('/', $this->packageName)));
		}

		return $this->namespace;
	}

	/**
	 * Returns package "src" directory.
	 * 
	 * @access  protected
	 * @return  string
	 */

	public function getSrcDir()
	{
		if($this->srcDir === null)
		{
			$this->srcDir = dirname((new ReflectionClass($this))->getFileName());
		}

		return $this->srcDir;
	}

	/**
	 * Returns the path to the package configuration files.
	 * 
	 * @access  public
	 * @return  string
	 */

	public function getConfigPath()
	{
		return realpath($this->getSrcDir() . '/../config');
	}

	/**
	 * Returns the path to the package i18n strings.
	 * 
	 * @access  public
	 * @return  string
	 */

	public function getI18nPath()
	{
		return realpath($this->getSrcDir() . '/../i18n');
	}

	/**
	 * Returns the path to the package views.
	 * 
	 * @access  public
	 * @return  string
	 */

	public function getViewPath()
	{
		return realpath($this->getSrcDir() . '/../views');
	}

	/**
	 * Gets executed at the end of the package boot sequence.
	 * 
	 * @access  protected
	 */

	protected function bootstrap()
	{
		// Nothing here
	}

	/**
	 * Boots the package.
	 * 
	 * @access  public
	 */

	public function boot()
	{
		// Register configuration namespace

		$this->container->get('config')->registerNamespace($this->getNamespace(), $this->getConfigPath());

		// Register i18n namespace

		if($this->container->has('i18n'))
		{
			$this->container->get('i18n')->getLoader()->registerNamespace($this->getNamespace(), $this->getI18nPath());
		}

		// Register view namespace

		if($this->container->has('viewFactory'))
		{
			$this->container->get('viewFactory')->registerNamespace($this->getNamespace(), $this->getViewPath());
		}

		// Bootstrap package

		$this->bootstrap();
	}
}