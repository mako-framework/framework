<?php

namespace mako\security\crypto;

use \mako\core\Config;
use \mako\security\crypto\adapters\Adapter;
use \RuntimeException;

/**
 * Cryptography class.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class Crypto
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	/**
	 * Crypto adapters.
	 * 
	 * @var array
	 */
	
	protected static $adapters = array
	(
		'mcrypt'  => '\mako\security\crypto\adapters\Mcrypt',
		'openssl' => '\mako\security\crypto\adapters\OpenSSL',
	);

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
	 * @param   string                         $name  (optional) Encryption configuration name
	 * @return  \mako\security\crypto\Adapter
	 */
	
	public static function factory($name = null)
	{
		$config = Config::get('crypto');

		$name = $name ?: $config['default'];

		if(isset($config['configurations'][$name]) === false)
		{
			throw new RuntimeException(vsprintf("%s(): '%s' has not been defined in the crypto configuration.", array(__METHOD__, $name)));
		}

		$adapter = static::$adapters[$config['configurations'][$name]['library']];

		$adapter = new $adapter($config['configurations'][$name]);

		if(!($adapter instanceof Adapter))
		{
			throw new RuntimeException(vsprintf("%s(): The crypto adapter must extend the \mako\security\crypto\adapters\Adapter class.", array(__METHOD__)));
		}

		return $adapter;
	}

	/**
	 * Registers a new Crypto adapter.
	 * 
	 * @access  public
	 * @param   string  $name   Adapter name
	 * @param   string  $class  Adapter class
	 */

	public static function registerAdapter($name, $class)
	{
		static::$adapters[$name] = $class;
	}

	/**
	 * Magic shortcut to the default crypto configuration.
	 *
	 * @access  public
	 * @param   string  $name       Method name
	 * @param   array   $arguments  Method arguments
	 * @return  mixed
	 */

	public static function __callStatic($name, $arguments)
	{
		return call_user_func_array(array(static::factory(), $name), $arguments);
	}
}

/** -------------------- End of file -------------------- **/