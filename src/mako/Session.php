<?php

namespace mako;

use \mako\Config;
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
	 * Session instance.
	 * 
	 * @var \mako\session\Adapter
	 */

	protected static $instance;

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	 * Constructor.
	 *
	 * @access  public
	 * @param   array   $config  Configuration
	 */

	protected function __construct(array $config)
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
	 * @return  \mako\session\Adapter
	 */

	public static function instance()
	{
		if(empty(static::$instance))
		{
			$config = Config::get('session');

			$name = $config['default'];

			if(isset($config['configurations'][$name]) === false)
			{
				throw new RuntimeException(vsprintf("%s(): '%s' has not been defined in the session configuration.", array(__METHOD__, $name)));
			}

			$type = $config['configurations'][$name]['type'];

			$class = '\mako\session\\' . $type;

			$adapter = new $class($config['configurations'][$name]);

			if($type !== 'Native')
			{
				session_set_save_handler
				(
					array($adapter, 'sessionOpen'), 
					array($adapter, 'sessionClose'), 
					array($adapter, 'sessionRead'), 
					array($adapter, 'sessionWrite'), 
					array($adapter, 'sessionDestroy'), 
					array($adapter, 'sessionGarbageCollector')
				);
			}

			session_name($config['session_name']);

			session_start();
			
			static::$instance = $adapter;
		}

		return static::$instance;
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
		return call_user_func_array(array(static::instance(), $name), $arguments);
	}
}

/** -------------------- End of file -------------------- **/