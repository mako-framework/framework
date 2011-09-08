<?php

namespace mako
{
	use \Mako;
	use \mako\log\Exception as LogException;
	
	/**
	* Logginger class.
	*
	* @author     Frederic G. Østby
	* @copyright  (c) 2008-2011 Frederic G. Østby
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
					throw new LogException(__CLASS__.": '{$name}' has not been defined in the log configuration.");
				}
				
				$class = '\mako\log\\' . static::$config['configurations'][$name]['type'];
				
				static::$instances[$name] = new $class(static::$config['configurations'][$name]);
				
				return static::$instances[$name];
			}
		}
	}
}

/** -------------------- End of file --------------------**/