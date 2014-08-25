<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\config;

use \RuntimeException;

use \mako\file\FileSystem;
use \mako\utility\Arr;

/**
 * Config class.
 *
 * @author  Frederic G. Østby
 */

class Config
{
	use \mako\common\NamespacedFileLoaderTrait;
	
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
	 * @param   string                $environment  (optional) Environment name
	 */

	public function __construct(FileSystem $fileSystem, $path, $environment = null)
	{
		$this->fileSystem = $fileSystem;

		$this->path = $path;

		$this->environment = $environment;
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
			$namespaceSeparator = strpos($file, '::');

			$file = $namespaceSeparator === false ? $this->environment . '.' . $file : substr_replace($file, $this->environment . '.', $namespaceSeparator + 2, 0);

			foreach($this->getCascadingFilePaths($file) as $path)
			{
				if($this->fileSystem->exists($path))
				{
					$config = array_replace_recursive($config, $this->fileSystem->includeFile($path));

					break;
				}
			}
		}

		return $config;
	}

	/**
	 * Returns config value or entire config array from a file.
	 *
	 * @access  public
	 * @param   string  $key      Config key
	 * @param   mixed   $default  (optional) Default value to return if config value doesn't exist
	 * @return  mixed
	 */

	public function get($key, $default = null)
	{
		$parts = explode('.', $key, 2);

		// Check if we need to load the configuration

		if(!isset($this->configuration[$parts[0]]))
		{
			$this->configuration[$parts[0]] = $this->load($parts[0]);
		}

		// Return the configuration

		return isset($parts[1]) ? Arr::get($this->configuration[$parts[0]], $parts[1], $default) : $this->configuration[$parts[0]];
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
		$config = strtok($key, '.');

		if(!isset($this->configuration[$config]))
		{
			$this->get($config);
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