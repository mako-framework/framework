<?php

namespace mako;

/**
 * Charset helper.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class Charset
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
	 * Check if a string only contains ascii characters.
	 *
	 * @param   string   $str  The input string
	 * @return  boolean
	 */

	public static function isAscii($str)
	{
		return ! preg_match('/[^\x00-\x7F]/', $str);
	}

	/**
	 * Silently remove all invalid characters.
	 *
	 * @access  public
	 * @param   string  $str      The input string
	 * @param   string  $charset  (optional) Expected charset
	 * @return  string
	 */

	public static function clean($str, $charset = MAKO_CHARSET)
	{
		$error = error_reporting(~E_NOTICE);
		
		$str = iconv($charset, $charset . '//IGNORE', $str);
		
		error_reporting($error);
		
		return $str;
	}

	/**
	 * Converts a string to the chosen charset.
	 *
	 * @access  public
	 * @param   string  $str      String to convert
	 * @param   string  $charset  (optional) Character set to convert to
	 * @return  string
	 */

	public static function convert($str, $charset = MAKO_CHARSET)
	{
		return mb_convert_encoding($str, $charset, mb_detect_encoding($str));
	}
}

/** -------------------- End of file -------------------- **/