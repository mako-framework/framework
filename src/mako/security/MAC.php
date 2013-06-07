<?php

namespace mako\security;

use \mako\Config;

/**
 * Signs and validates strings using MACs (message authentication codes).
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class MAC
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
	 * Returns the signature.
	 * 
	 * @access  protected
	 * @param   string     $string  The string you want to sign
	 * @return  string
	 */

	protected static function signature($string)
	{
		return hash_hmac('sha1', $string, Config::get('application.secret'));
	}
	
	/**
	 * Returns a signed string.
	 * 
	 * @access  public
	 * @param   string  $string  The string you want to sign
	 * @return  string
	 */

	public static function sign($string)
	{
		return static::signature($string) . $string;
	}

	/**
	 * Returns the original string if the signature is valid or FALSE if not.
	 * 
	 * @access  public
	 * @param   string  $string  The string you want to validate
	 * @return  mixed
	 */

	public static function validate($string)
	{
		$validated = substr($string, 40);

		if(static::signature($validated) === substr($string, 0, 40))
		{
			return $validated;
		}

		return false;
	}
}

/** -------------------- End of file -------------------- **/