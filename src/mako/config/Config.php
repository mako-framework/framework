<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\config;

use mako\config\loaders\LoaderInterface;
use mako\utility\Arr;

/**
 * Config class.
 *
 * @author  Frederic G. Østby
 */
class Config
{
	/**
	 * Loader.
	 *
	 * @var \mako\config\loaders\LoaderInterface
	 */
	 protected $loader;

	/**
	 * Environment name.
	 *
	 * @var string
	 */
	protected $environment;

	/**
	 * Configuration.
	 *
	 * @var array
	 */
	protected $configuration = [];

	/**
	 * Constructor.
	 *
	 * @access  public
	 * @param   \mako\config\loaders\LoaderInterface  $loader       Config loader
	 * @param   null|string                           $environment  Environment name
	 */
	public function __construct(LoaderInterface $loader, string $environment = null)
	{
		$this->loader = $loader;

		$this->environment = $environment;
	}

	/**
	 * Returns the config loader.
	 *
	 * @return \mako\config\loaders\LoaderInterface
	 */
	public function getLoader(): LoaderInterface
	{
		return $this->loader;
	}

	/**
	 * Returns the currently loaded configuration.
	 *
	 * @access  public
	 * @return  array
	 */
	public function getLoadedConfiguration(): array
	{
		return $this->configuration;
	}

	/**
	 * Sets the environment.
	 *
	 * @access  public
	 * @param   string  $environment  Environment name
	 */
	public function setEnvironment(string $environment)
	{
		$this->environment = $environment;
	}

	/**
	 * Parses the language key.
	 *
	 * @access  protected
	 * @param   string     $key  Language key
	 * @return  array
	 */
	protected function parseKey(string $key): array
	{
		return (strpos($key, '.') === false) ? [$key, null] : explode('.', $key, 2);
	}

	/**
	 * Loads the configuration file.
	 *
	 * @access  protected
	 * @param   string     $file  File name
	 */
	protected function load(string $file)
	{
		$this->configuration[$file] = $this->loader->load($file, $this->environment);
	}

	/**
	 * Returns config value or entire config array from a file.
	 *
	 * @access  public
	 * @param   string      $key      Config key
	 * @param   null|mixed  $default  Default value to return if config value doesn't exist
	 * @return  null|mixed
	 */
	public function get(string $key, $default = null)
	{
		list($file, $path) = $this->parseKey($key);

		if(!isset($this->configuration[$file]))
		{
			$this->load($file);
		}

		return $path === null ? $this->configuration[$file] : Arr::get($this->configuration[$file], $path, $default);
	}

	/**
	 * Sets a config value.
	 *
	 * @access  public
	 * @param   string  $key    Config key
	 * @param   mixed   $value  Config value
	 */
	public function set(string $key, $value)
	{
		list($file, $path) = $this->parseKey($key);

		if(!isset($this->configuration[$file]))
		{
			$this->load($file);
		}

		Arr::set($this->configuration, $key, $value);
	}

	/**
	 * Removes a value from the configuration.
	 *
	 * @access  public
	 * @param   string  $key  Config key
	 * @return  bool
	 */
	public function remove(string $key): bool
	{
		return Arr::delete($this->configuration, $key);
	}
}
