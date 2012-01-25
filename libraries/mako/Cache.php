<?php

namespace mako
{
	use \mako\Mako;
	use \RuntimeException;
	
	/**
	* Cache class.
	*
	* @author     Frederic G. Østby
	* @copyright  (c) 2008-2012 Frederic G. Østby
	* @license    http://www.makoframework.com/license
	*/

	class Cache
	{
		//---------------------------------------------
		// Class variables
		//---------------------------------------------
		
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
				$config = Mako::config('cache');
				
				$name = ($name === null) ? $config['default'] : $name;
				
				if(isset($config['configurations'][$name]) === false)
				{
					throw new RuntimeException(vsprintf("%s(): '%s' has not been defined in the cache configuration.", array(__METHOD__, $name)));
				}
				
				$class = '\mako\cache\\' . $config['configurations'][$name]['type'];
				
				static::$instances[$name] = new $class($config['configurations'][$name]);
				
				return static::$instances[$name];
			}
		}

		/**
		* "Magick" method for easy access to the default cache instance.
		*
		* @access  public
		* @param   string  Method name
		* @param   array   Method arguments
		* @return  mixed
		*/

		public static function __callStatic($name, $arguments)
		{
			return call_user_func_array(array(Cache::instance(), $name), $arguments);
		}
	}
}

/** -------------------- End of file --------------------**/