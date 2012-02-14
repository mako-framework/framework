<?php

namespace mako;

/**
* Config class.
*
* @author     Frederic G. Østby
* @copyright  (c) 2008-2012 Frederic G. Østby
* @license    http://www.makoframework.com/license
*/

use \mako\Arr;
use \mako\Package;
use \RuntimeException;

class Config
{
	//---------------------------------------------
	// Class variables
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
	* Returns config value or entire config array from a file.
	*
	* @access  public
	* @param   string  Config key
	* @param   mixed   (optional) Default value to return if config value doesn't exist
	* @return  mixed
	*/

	public static function get($key, $default = null)
	{
		$key = explode('.', $key, 2);

		if(!isset(static::$config[$key[0]]))
		{
			$path = Package::path('config', $key[0]);

			if(file_exists($path) === false)
			{
				throw new RuntimeException(vsprintf("%s(): The '%s' config file does not exist.", array(__METHOD__, $key[0])));
			}	

			static::$config[$key[0]] = include($path);
		}

		if(!isset($key[1]))
		{
			return static::$config[$key[0]];
		}
		else
		{
			return Arr::get(static::$config[$key[0]], $key[1], $default);
		}
	}
}

/** -------------------- End of file --------------------**/