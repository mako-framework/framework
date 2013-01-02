<?php

namespace mako;

/**
 * Config class.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

use \mako\Arr;
use \RuntimeException;

class Config
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	/**
	 * Config array.
	 *
	 * @var array
	 */

	protected static $config;

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	 * Protected constructor since this is a static class.
	 *
	 * @access  protected
	 */

	protected function __construct()
	{
		// Nothing here
	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	 * Returns the path to the configuration file.
	 *
	 * @access  protected
	 * @param   string    $file  File name
	 * @return  string
	 */

	protected static function file($file)
	{
		$paths = array('config');

		if(!empty($_SERVER['MAKO_ENV']))
		{
			array_unshift($paths, 'config/' . $_SERVER['MAKO_ENV']);
		}

		foreach($paths as $path)
		{
			$path = mako_path($path, $file);

			if(file_exists($path))
			{
				return $path;
			}
		}

		throw new RuntimeException(vsprintf("%s(): The '%s' config file does not exist.", array(__METHOD__, $file)));
	}

	/**
	 * Returns config value or entire config array from a file.
	 *
	 * @access  public
	 * @param   string  $key      Config key
	 * @param   mixed   $default  (optional) Default value to return if config value doesn't exist
	 * @return  mixed
	 */

	public static function get($key, $default = null)
	{
		$keys = explode('.', $key, 2);

		if(!isset(static::$config[$keys[0]]))
		{
			static::$config[$keys[0]] = include(static::file($keys[0]));
		}

		if(!isset($keys[1]))
		{
			return static::$config[$keys[0]];
		}
		else
		{
			return Arr::get(static::$config[$keys[0]], $keys[1], $default);
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

		if(!isset(static::$config[$config]))
		{
			static::get($config);
		}

		Arr::set(static::$config, $key, $value);
	}

	/**
	 * Deletes a value from the configuration.
	 *
	 * @access  public
	 * @param   string   $key  Config key
	 * @return  boolean
	 */

	public static function delete($key)
	{
		return Arr::delete(static::$config, $key);
	}
}

/** -------------------- End of file --------------------**/