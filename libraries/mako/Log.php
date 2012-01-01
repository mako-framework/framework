<?php

namespace mako
{
	use \mako\Mako;
	use \RuntimeException;
	
	/**
	* Logginger class.
	*
	* @author     Frederic G. Østby
	* @copyright  (c) 2008-2012 Frederic G. Østby
	* @license    http://www.makoframework.com/license
	*/

	class Log
	{
		//---------------------------------------------
		// Class variables
		//---------------------------------------------
		
		/**
		* Log level.
		*/
		
		const EMERGENCY = 1;
		
		/**
		* Log level.
		*/
		
		const ALERT = 2;
		
		/**
		* Log level.
		*/
		
		const CRITICAL = 3;
		
		/**
		* Log level.
		*/
		
		const ERROR = 4;
		
		/**
		* Log level.
		*/
		
		const WARNING = 5;
		
		/**
		* Log level.
		*/
		
		const NOTICE = 6;
		
		/**
		* Log level.
		*/
		
		const INFO = 7;
		
		/**
		* Log level.
		*/
		
		const DEBUG = 8;
		
		/**
		* Holds the configuration.
		*/

		protected static $config;
		
		/**
		* Holds all the logger objects.
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
		* @param   string  (optional) Log configuration name
		* @return  object
		*/
		
		public static function instance($name = null)
		{
			if(isset(static::$instances[$name]))
			{
				return static::$instances[$name];
			}
			else
			{
				if(empty(static::$config))
				{
					static::$config = Mako::config('log');
				}
				
				$name = ($name === null) ? static::$config['default'] : $name;
				
				if(isset(static::$config['configurations'][$name]) === false)
				{
					throw new RuntimeException(__CLASS__ . ": '{$name}' has not been defined in the log configuration.");
				}
				
				$class = '\mako\log\\' . static::$config['configurations'][$name]['type'];
				
				static::$instances[$name] = new $class(static::$config['configurations'][$name]);
				
				return static::$instances[$name];
			}
		}

		/**
		* "Magick" shortcut for writing to logs.
		*
		* @access  public
		* @param   string   Message type name
		* @param   string   The message to write to the log
		* @return  boolean
		*/

		public static function __callStatic($name, $args)
		{
			if(!defined('static::' . strtoupper($name)))
			{
				throw new RuntimeException(__CLASS__ . ": No such log level exists.");
			}

			return static::instance()->write($args[0], constant('static::' . strtoupper($name)));
		}
	}
}

/** -------------------- End of file --------------------**/