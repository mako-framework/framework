<?php

namespace mako
{
	use \mako\Mako;
	use \RuntimeException;
	
	/**
	* Cache class.
	*
	* @author     Frederic G. Østby
	* @copyright  (c) 2008-2011 Frederic G. Østby
	* @license    http://www.makoframework.com/license
	*/

	class Cache
	{
		//---------------------------------------------
		// Class variables
		//---------------------------------------------
		
		/**
		* Holds the configuration.
		*/

		protected static $config;
		
		/**
		* Holds all the cache objects.
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
		* @param   string  (optional) Cache configuration name
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
					static::$config = Mako::config('cache');
				}
				
				$name = ($name === null) ? static::$config['default'] : $name;
				
				if(isset(static::$config['configurations'][$name]) === false)
				{
					throw new RuntimeException(__CLASS__ . ": '{$name}' has not been defined in the cache configuration.");
				}
				
				$class = '\mako\cache\\' . static::$config['configurations'][$name]['type'];
				
				static::$instances[$name] = new $class(static::$config['configurations'][$name]);
				
				return static::$instances[$name];
			}
		}
	}
}

/** -------------------- End of file --------------------**/