<?php

namespace mako
{
	use \mako\Mako;
	use \RuntimeException;
	
	/**
	* Session class.
	*
	* @author     Frederic G. Østby
	* @copyright  (c) 2008-2011 Frederic G. Østby
	* @license    http://www.makoframework.com/license
	*/

	class Session
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
		* Starts a session using the chosen type of storage.
		*
		* @param   string  (optional) Encryption configuration name
		*/
		
		public static function start($name = null)
		{
			$config = Mako::config('session');

			$name = ($name === null) ? $config['default'] : $name;

			if(isset($config['configurations'][$name]) === false)
			{
				throw new RuntimeException(__CLASS__ . ": '{$name}' has not been defined in the session configuration.");
			}

			$type = $config['configurations'][$name]['type'];

			if(strtolower($type) !== 'native')
			{
				$class = '\mako\session\\' . $type;

				$adapter = new $class($config['configurations'][$name]);

				session_set_save_handler
				(
					array($adapter, 'open'), 
					array($adapter, 'close'), 
					array($adapter, 'read'), 
					array($adapter, 'write'), 
					array($adapter, 'destroy'), 
					array($adapter, 'gc')
				);
			}

			session_start();
		}
	}
}

/** -------------------- End of file --------------------**/