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
	/**
	 * File system instance.
	 * 
	 * @var \mako\file\FileSystem
	 */

	protected $fileSystem;

	/**
	 * Application path.
	 * 
	 * @var string
	 */

	protected $applicationPath;

	/**
	 * Configuration.
	 * 
	 * @var array
	 */

	protected $config = [];

	/**
	 * Constructor.
	 * 
	 * @access  public
	 * @param   \mako\file\FileSystem  $fileSystem       File system instance
	 * @param   string                 $applicationPath  Application path
	 */

	public function __construct(FileSystem $fileSystem, $applicationPath)
	{
		$this->fileSystem = $fileSystem;

		$this->applicationPath = $applicationPath;
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
		$found = false;

		$paths = mako_cascading_paths($this->applicationPath, 'config', $file);

		foreach($paths as $path)
		{
			if($this->fileSystem->exists($path))
			{
				$found = true;

				$config = $this->fileSystem->includeFile($path);

				break;
			}
		}

		if(!$found)
		{
			throw new RuntimeException(vsprintf("%s(): The [ %s ] config file does not exist.", [__METHOD__, $file]));
		}

		// Merge environment specific configuration

		if(mako_env() !== null)
		{
			$paths = mako_cascading_paths($this->applicationPath, 'config/' . mako_env(), $file);
			
			foreach($paths as $path)
			{
				if($this->fileSystem->exists($path))
				{
					$config = array_replace_recursive($config, $this->fileSystem->includeFile($path));

					break;
				}
			}
		}

		// Return configuration

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
		$keys = explode('.', $key, 2);

		if(!isset($this->config[$keys[0]]))
		{
			$this->config[$keys[0]] = $this->load($keys[0]);
		}

		if(!isset($keys[1]))
		{
			return $this->config[$keys[0]];
		}
		else
		{
			return Arr::get($this->config[$keys[0]], $keys[1], $default);
		}
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

		if(!isset($this->config[$config]))
		{
			$this->get($config);
		}

		Arr::set($this->config, $key, $value);
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
		return Arr::delete($this->config, $key);
	}
}