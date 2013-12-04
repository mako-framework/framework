<?php

namespace mako\session;

use \mako\core\Config;
use \mako\session\AbstractionLayer;
use \SessionHandlerInterface;
use \RuntimeException;

/**
 * Session class.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class Session
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------
	
	/**
	 * Session abstraction layer instance.
	 * 
	 * @var \mako\session\AbstractionLayer
	 */

	protected static $instance;

	/**
	 * Session handlers.
	 * 
	 * @var array
	 */
	
	protected static $handlers = 
	[
		'native'   => '\mako\session\handlers\Native',
		'file'     => '\mako\session\handlers\File',
		'redis'    => '\mako\session\handlers\Redis',
		'database' => '\mako\session\handlers\Database',
	];

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
	 * Returns the session instance.
	 * 
	 * @access  public
	 * @return  \mako\session\Session
	 */

	public static function instance()
	{
		if(empty(static::$instance))
		{
			$config = Config::get('session');

			$name = $config['default'];

			if(isset($config['configurations'][$name]) === false)
			{
				throw new RuntimeException(vsprintf("%s(): [ %s ] has not been defined in the session configuration.", [__METHOD__, $name]));
			}

			$handler = static::$handlers[$config['configurations'][$name]['type']];

			$handler = new $handler($config['configurations'][$name]);

			if(!($handler instanceof SessionHandlerInterface))
			{
				throw new RuntimeException(vsprintf("%s(): The session handler must implement the \SessionHandlerInterface interface.", [__METHOD__]));
			}

			static::$instance = new AbstractionLayer($handler, $config['session_name']);
		}

		return static::$instance;
	}

	/**
	 * Registers a new session handler.
	 * 
	 * @access  public
	 * @param   string  $name   Handler name
	 * @param   string  $class  Handler class
	 */

	public static function registerHandler($name, $class)
	{
		static::$handlers[$name] = $class;
	}

	/**
	 * Magic shortcut to the session instance.
	 *
	 * @access  public
	 * @param   string  $name       Method name
	 * @param   array   $arguments  Method arguments
	 * @return  mixed
	 */

	public static function __callStatic($name, $arguments)
	{
		return call_user_func_array([static::instance(), $name], $arguments);
	}
}

/** -------------------- End of file -------------------- **/