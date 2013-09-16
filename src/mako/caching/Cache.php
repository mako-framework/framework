<?php

namespace mako;

use \mako\Config;
use \RuntimeException;

/**
 * Cache class.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class Cache
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------
	
	/**
	 * Holds all the cache objects.
	 *
	 * @var array
	 */
	
	protected static $instances = array();

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
	 * Returns an instance of the requested cache configuration.
	 *
	 * @param   string               $name  (optional) Cache configuration name
	 * @return  \mako\cache\Adapter
	 */

	public static function instance($name = null)
	{
		$config = Config::get('cache');
			
		$name = ($name === null) ? $config['default'] : $name;

		if(!isset(static::$instances[$name]))
		{	
			if(isset($config['configurations'][$name]) === false)
			{
				throw new RuntimeException(vsprintf("%s(): '%s' has not been defined in the cache configuration.", array(__METHOD__, $name)));
			}
			
			$class = '\mako\cache\\' . $config['configurations'][$name]['type'];
			
			static::$instances[$name] = new $class($config['configurations'][$name]);
		}

		return static::$instances[$name];
	}

	/**
	 * Magic shortcut to the default cache instance.
	 *
	 * @access  public
	 * @param   string  $name       Method name
	 * @param   array   $arguments  Method arguments
	 * @return  mixed
	 */

	public static function __callStatic($name, $arguments)
	{
		return call_user_func_array(array(static::instance(), $name), $arguments);
	}
}

/** -------------------- End of file -------------------- **/