<?php

namespace mako;

use \mako\Config;
use \RuntimeException;

/**
 * Logginger class.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class Log
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------
	
	/**
	 * Log level.
	 *
	 * @var int
	 */
	
	const EMERGENCY = 1;
	
	/**
	 * Log level.
	 *
	 * @var int
	 */
	
	const ALERT = 2;
	
	/**
	 * Log level.
	 *
	 * @var int
	 */
	
	const CRITICAL = 3;
	
	/**
	 * Log level.
	 *
	 * @var int
	 */
	
	const ERROR = 4;
	
	/**
	 * Log level.
	 *
	 * @var int
	 */
	
	const WARNING = 5;
	
	/**
	 * Log level.
	 *
	 * @var int
	 */
	
	const NOTICE = 6;
	
	/**
	 * Log level.
	 *
	 * @var int
	 */
	
	const INFO = 7;
	
	/**
	 * Log level.
	 *
	 * @var int
	 */
	
	const DEBUG = 8;
	
	/**
	 * Holds all the logger objects.
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
	 * Returns an instance of the requested log configuration.
	 *
	 * @param   string             $name  (optional) Log configuration name
	 * @return  \mako\log\Adapter
	 */
	
	public static function instance($name = null)
	{
		$config = Config::get('log');
			
		$name = ($name === null) ? $config['default'] : $name;
		
		if(!isset(static::$instances[$name]))
		{	
			if(isset($config['configurations'][$name]) === false)
			{
				throw new RuntimeException(vsprintf("%s(): '%s' has not been defined in the log configuration.", array(__METHOD__, $name)));
			}
			
			$class = '\mako\log\\' . $config['configurations'][$name]['type'];
			
			static::$instances[$name] = new $class($config['configurations'][$name]);
		}

		return static::$instances[$name];
	}

	/**
	 * Magic shortcut for writing to logs.
	 *
	 * @access  public
	 * @param   string   $name       Name of the log type
	 * @param   string   $arguments  Method arguments
	 * @return  boolean
	 */

	public static function __callStatic($name, $arguments)
	{
		if(!defined('static::' . strtoupper($name)))
		{
			throw new RuntimeException(vsprintf("%s(): Invalid log type.", array(__METHOD__)));
		}

		return static::instance()->write($arguments[0], constant('static::' . strtoupper($name)));
	}
}

/** -------------------- End of file -------------------- **/