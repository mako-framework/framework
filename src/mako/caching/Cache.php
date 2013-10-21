<?php

namespace mako\caching;

use \mako\core\Config;
use \mako\caching\adapters\Adapter;
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
	 * Crypto adapters.
	 * 
	 * @var array
	 */
	
	protected static $adapters = array
	(
		'apc'        => '\mako\caching\adapters\APC',
		'apcu'       => '\mako\caching\adapters\APCU',
		'database'   => '\mako\caching\adapters\Database',
		'file'       => '\mako\caching\adapters\File',
		'memcache'   => '\mako\caching\adapters\Memcache',
		'memcached'  => '\mako\caching\adapters\Memcached',
		'memory'     => '\mako\caching\adapters\Memory',
		'redis'      => '\mako\caching\adapters\Redis',
		'wincache'   => '\mako\caching\adapters\WinCache',
		'xcache'     => '\mako\caching\adapters\XCache',
		'zenddisk'   => '\mako\caching\adapters\ZendDisk',
		'zendmemory' => '\mako\caching\adapters\ZendMemory',
	);
	
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

		$name = $name ?: $config['default'];

		if(!isset(static::$instances[$name]))
		{
			if(isset($config['configurations'][$name]) === false)
			{
				throw new RuntimeException(vsprintf("%s(): '%s' has not been defined in the cache configuration.", array(__METHOD__, $name)));
			}

			$adapter = static::$adapters[$config['configurations'][$name]['type']];

			static::$instances[$name] = new $adapter($config['configurations'][$name]);

			if(!(static::$instances[$name] instanceof Adapter))
			{
				throw new RuntimeException(vsprintf("%s(): The cache adapter must extend the \mako\caching\adapters\Adapter class.", array(__METHOD__)));
			}
		}

		return static::$instances[$name];
	}

	/**
	 * Registers a new cache adapter.
	 * 
	 * @access  public
	 * @param   string  $name   Adapter name
	 * @param   string  $class  Adapter class
	 */

	public static function registerAdapter($name, $class)
	{
		static::$adapters[$name] = $class;
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