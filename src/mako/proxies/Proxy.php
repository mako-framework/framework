<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\proxies;

use \RuntimeException;

/**
 * Abstract proxy class.
 *
 * @author  Frederic G. Østby
 */

abstract class Proxy
{
	//---------------------------------------------
	// Class properties
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
	 * Returns instance of the class we're proxying.
	 * 
	 * @access  protected
	 * @return  mixed
	 */

	protected static function instance()
	{
		throw new RuntimeException(vsprintf("%s(): The [ instance ] method must be reimplemented.", [__METHOD__]));
	}

	/**
	 * Forwards call to the proxied class.
	 * 
	 * @access  public
	 * @param   string  $name       Method name
	 * @param   array   $arguments  Method arguments
	 * @return  mixed
	 */

	public static function __callStatic($name, $arguments)
	{
		return call_user_func_array([static::instance(), $name], $arguments);
	}
}

