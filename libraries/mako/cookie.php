<?php

namespace mako
{
	use \Mako;
	
	/**
	* Class that allows you to set and read signed cookies.
	*
	* @author     Frederic G. Østby
	* @copyright  (c) 2008-2011 Frederic G. Østby
	* @license    http://www.makoframework.com/license
	*/
	
	class Cookie
	{	
		//---------------------------------------------
		// Class variables
		//---------------------------------------------
		
		/**
		* Holds the configuration.
		*/
		
		protected static $config;
		
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
		* @param   string     Cookie name
		* @param   string     Cookie value
		* @return  string     Cookie signature
		*/
		
		protected static function signature($name, $value)
		{
			if(empty(static::$config))
			{
				static::$config = Mako::config('cookie');
			}
			
			return base64_encode(sha1($name . $value . static::$config['secret'], true)); // use binary output and encode using base64 to save a few bytes
		}
		
		/**
		* Sets a signed cookie.
		*
		* @access  public
		* @param   string   Cookie name
		* @param   string   Cookie value
		* @param   int      (optional) Time to live - if omitted or set to 0 the cookie will expire when the browser closes
		* @param   boolean  (optional) HTTPS only - if set to TRUE then the cookie should only be transmitted over a secure connection from the client
		* @param   boolean  (optional) HTTP only - if set to TRUE the cookie will be made accessible only through the HTTP protocol
		*/
		
		public static function set($name, $value, $ttl = 0, $secure = false, $httpOnly = false)
		{			
			$ttl = ($ttl > 0) ? (time() + $ttl) : 0;
			
			$value = static::signature($name, $value) . $value;
			
			setcookie($name, $value, $ttl, static::$config['path'], static::$config['domain'], $secure, $httpOnly);
		}
		
		/**
		* Reads a signed cookie. Cookies that are unsigned or have an invalid signature will be ignored.
		*
		* @access  public
		* @param   string  Cookie name
		* @param   mixed   (optional) Default value to return if the cookie doesn't exist or if the signature is invalid
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
		* @param   string  Cookie name
		*/
		
		public static function delete($name)
		{
			if(empty(static::$config))
			{
				static::$config = Mako::config('cookie');
			}
			
			setcookie($name, '', time() - 3600, static::$config['path'], static::$config['domain']);
		}
	}
}

/** -------------------- End of file --------------------**/