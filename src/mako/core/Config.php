<?php

namespace mako\core;

use \mako\utility\Arr;
use \RuntimeException;

/**
 * Config class.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class Config
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

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

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	 * Constructor.
	 * 
	 * @access  public
	 * @param   string  $applicationPath  Application path
	 */

	public function __construct($applicationPath)
	{
		$this->applicationPath = $applicationPath;
	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

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

		$paths = mako_cascading_paths($this->applicationPath, '/config', $file);

		foreach($paths as $path)
		{
			if(file_exists($path))
			{
				$found = true;

				$config = include($path);

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
			$paths = mako_cascading_paths($this->applicationPath, '/config/' . mako_env(), $file);

			foreach($paths as $path)
			{
				if(file_exists($path))
				{
					$config = array_replace_recursive($config, include($path));

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

	public static function set($key, $value)
	{
		$config = strtok($key, '.');

		if(!isset($this->config[$config]))
		{
			$this->get($config);
		}

		Arr::set($this->config, $key, $value);
	}

	/**
	 * Deletes a value from the configuration.
	 *
	 * @access  public
	 * @param   string   $key  Config key
	 * @return  boolean
	 */

	public function delete($key)
	{
		return Arr::delete($this->config, $key);
	}
}

/** -------------------- End of file -------------------- **/