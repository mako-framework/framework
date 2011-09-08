<?php

namespace mako
{
	use \Mako;
	use \mako\crypto\Exception as CryptoException;
	
	/**
	* Cryptography class.
	*
	* @author     Frederic G. Østby
	* @copyright  (c) 2008-2011 Frederic G. Østby
	* @license    http://www.makoframework.com/license
	*/

	class Crypto
	{
		//---------------------------------------------
		// Class variables
		//---------------------------------------------
		
		/**
		* Holds the configuration.
		*/

		protected static $config;
		
		/**
		* Holds all the cryptography objects.
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
		* Returns an instance of the requested encryption configuration.
		*
		* @param   string  (optional) Encryption configuration name
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
					static::$config = Mako::config('crypto');
				}
				
				$name = ($name === null) ? static::$config['default'] : $name;
				
				if(isset(static::$config['configurations'][$name]) === false)
				{
					throw new CryptoException(__CLASS__.": '{$name}' has not been defined in the crypto configuration.");
				}
				
				$class = '\mako\crypto\\' . static::$config['configurations'][$name]['library'];
				
				static::$instances[$name] = new $class(static::$config['configurations'][$name]);
				
				return static::$instances[$name];
			}
		}
	}
}

/** -------------------- End of file --------------------**/