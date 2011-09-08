<?php

namespace mako
{
	/**
	* Collection of miscelaneous UTF-8 methods.
	*
	* @author     Frederic G. Østby
	* @copyright  (c) 2008-2011 Frederic G. Østby
	* @license    http://www.makoframework.com/license
	*/

	class UTF8
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
		* Check if a string only contains ascii characters.
		*
		* @param   string  The input string
		* @return  boolean
		*/

		public static function isAscii($str)
		{
			return ! preg_match('/[^\x00-\x7F]/', $str);
		}

		/**
		* Silently remove all invalid UTF-8 characters form the input.
		*
		* @access  public
		* @param   string  The input string
		* @param   string  (optional) The input charset
		* @return  string
		*/

		public static function clean($str, $charset = 'UTF-8')
		{
			$error = error_reporting(~E_NOTICE);
			
			$str = iconv($charset,'UTF-8//IGNORE', $str);
			
			error_reporting($error);
			
			return $str;
		}
	}
}

/** -------------------- End of file --------------------**/