<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\config;

use RuntimeException;

use mako\common\NamespacedFileLoaderTrait;
use mako\file\FileSystem;
use mako\utility\Arr;

/**
 * Config class.
 *
 * @author  Frederic G. Østby
 */

class Config
{
	use NamespacedFileLoaderTrait;

	/**
	 * File system instance.
	 *
	 * @var \mako\file\FileSystem
	 */

	protected $fileSystem;

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
	 * @param   \mako\file\FileSystem $fileSystem   File system instance
	 * @param   string                $path         Default path
	 * @param   string                $environment  Environment name
	 */

	public function __construct(FileSystem $fileSystem, $path, $environment = null)
	{
		$this->fileSystem = $fileSystem;

		$this->path = $path;

		$this->environment = $environment;
	}

	/**
	 * Returns the currently loaded configuration.
	 *
	 * @access  public
	 * @return  array
	 */

	public function getLoadedConfiguration()
	{
		return $this->configuration;
	}

	/**
	 * Sets the environment.
	 *
	 * @access  public
	 * @param   string  $environment  Environment name
	 */

	public function setEnvironment($environment)
	{
		$this->environment = $environment;
	}

	/**
	 * Loads the configuration file.
	 *
	 * @access  protected
	 * @param   string     $file  File name
	 * @return  array
	 */

	protected function load($file)
	{
		// Load configuration

		foreach($this->getCascadingFilePaths($file) as $path)
		{
			if($this->fileSystem->exists($path))
			{
				$config = $this->fileSystem->includeFile($path);

				break;
			}
		}

		if(!isset($config))
		{
			throw new RuntimeException(vsprintf("%s(): The [ %s ] config file does not exist.", [__METHOD__, $file]));
		}

		// Merge environment specific configuration

		if($this->environment !== null)
		{
			$namespace = strpos($file, '::');

			$namespaced = ($namespace === false) ? $this->environment . '.' . $file : substr_replace($file, $this->environment . '.', $namespace + 2, 0);

			foreach($this->getCascadingFilePaths($namespaced) as $path)
			{
				if($this->fileSystem->exists($path))
				{
					$config = array_replace_recursive($config, $this->fileSystem->includeFile($path));

					break;
				}
			}
		}

		$this->configuration[$file] = $config;
	}

	/**
	 * Parses the language key.
	 *
	 * @access  protected
	 * @param   string     $key  Language key
	 * @return  array
	 */

	protected function parseKey($key)
	{
		return (strpos($key, '.') === false) ? [$key, null] : explode('.', $key, 2);
	}

	/**
	 * Returns config value or entire config array from a file.
	 *
	 * @access  public
	 * @param   string  $key      Config key
	 * @param   mixed   $default  Default value to return if config value doesn't exist
	 * @return  mixed
	 */

	public function get($key, $default = null)
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

	public function set($key, $value)
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
	 * @param   string   $key  Config key
	 * @return  boolean
	 */

	public function remove($key)
	{
		return Arr::delete($this->configuration, $key);
	}
}