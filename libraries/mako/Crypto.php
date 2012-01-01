<?php

namespace mako
{
	use \mako\Mako;
	use \RuntimeException;
	
	/**
	* Cryptography class.
	*
	* @author     Frederic G. Østby
	* @copyright  (c) 2008-2012 Frederic G. Østby
	* @license    http://www.makoframework.com/license
	*/

	class Crypto
	{
		//---------------------------------------------
		// Class variables
		//---------------------------------------------
		
		// Nothing here

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
		
		public static function factory($name = null)
		{
			$config = Mako::config('crypto');

			$name = ($name === null) ? $config['default'] : $name;

			if(isset($config['configurations'][$name]) === false)
			{
				throw new RuntimeException(__CLASS__ . ": '{$name}' has not been defined in the crypto configuration.");
			}

			$class = '\mako\crypto\\' . $config['configurations'][$name]['library'];

			return new $class($config['configurations'][$name]);
		}
	}
}

/** -------------------- End of file --------------------**/