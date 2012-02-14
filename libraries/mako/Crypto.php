<?php

namespace mako;

use \mako\Config;
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
	* @param                        string  (optional) Encryption configuration name
	* @return  mako\crypto\Adapter
	*/
	
	public static function factory($name = null)
	{
		$config = Config::get('crypto');

		$name = ($name === null) ? $config['default'] : $name;

		if(isset($config['configurations'][$name]) === false)
		{
			throw new RuntimeException(vsprintf("%s(): '%s' has not been defined in the crypto configuration.", array(__METHOD__, $name)));
		}

		$class = '\mako\crypto\\' . $config['configurations'][$name]['library'];

		return new $class($config['configurations'][$name]);
	}

	/**
	* Magic shortcut to the default crypto configuration.
	*
	* @access  public
	* @param   string  Method name
	* @param   array   Method arguments
	* @return  mixed
	*/

	public static function __callStatic($name, $arguments)
	{
		return call_user_func_array(array(Crypto::factory(), $name), $arguments);
	}
}

/** -------------------- End of file --------------------**/