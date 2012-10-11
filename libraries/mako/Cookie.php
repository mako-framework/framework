<?php

namespace mako;

use \mako\Config;

/**
 * Class that allows you to set and read signed cookies.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2012 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class Cookie
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
	 * Returns the cookie signature.
	 *
	 * @access  protected
	 * @param   string     $name   Cookie name
	 * @param   string     $value  Cookie value
	 * @return  string
	 */
	
	protected static function signature($name, $value)
	{
		return base64_encode(sha1($name . $value . Config::get('cookie.secret'), true)); // use binary output and encode using base64 to save a few bytes
	}
	
	/**
	 * Sets a signed cookie.
	 *
	 * @access  public
	 * @param   string  $name     Cookie name
	 * @param   string  $value    Cookie value
	 * @param   int     $ttl      (optional) Time to live - if omitted or set to 0 the cookie will expire when the browser closes
	 * @param   array   $options  (optional) Cookie options
	 */

	public static function set($name, $value, $ttl = 0, array $options = array())
	{
		$options = $options + array
		(
			'path'     => Config::get('cookie.path'),
			'domain'   => Config::get('cookie.domain'),
			'secure'   => Config::get('cookie.secure'),
			'httponly' => Config::get('cookie.httponly'),
		);

		$value = static::signature($name, $value) . $value;

		setcookie($name, $value, $ttl, $options['path'], $options['domain'], $options['secure'], $options['httponly']);
	}
	
	/**
	 * Reads a signed cookie. Cookies that are unsigned or have an invalid signature will be ignored.
	 *
	 * @access  public
	 * @param   string  $name     Cookie name
	 * @param   mixed   $default  (optional) Default value to return if the cookie doesn't exist or if the signature is invalid
	 * @return  mixed
	 */
	
	public static function get($name, $default = null)
	{
		if(!isset($_COOKIE[$name]))
		{
			return $default;
		}
					
		$value = substr($_COOKIE[$name], 28);
					
		if(static::signature($name, $value) === substr($_COOKIE[$name], 0, 28))
		{
			return $value;
		}
		else
		{
			return $default;
		}
	}
	
	/**
	 * Deletes a cookie.
	 *
	 * @access  public
	 * @param   string  $name  Cookie name
	 */
	
	public static function delete($name)
	{
		setcookie($name, '', time() - 3600, Config::get('cookie.path'), Config::get('cookie.domain'));
	}
}

/** -------------------- End of file --------------------**/