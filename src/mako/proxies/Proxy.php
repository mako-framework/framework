<?php

namespace mako\proxies;

use \RuntimeException;

/**
 * Abstract proxy class.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

abstract class Proxy
{
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

/** -------------------- End of file -------------------- **/