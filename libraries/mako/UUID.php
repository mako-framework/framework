<?php

namespace mako
{
	/**
	* Class that generates and validates UUIDs.
	*
	* @author     Frederic G. Østby
	* @copyright  (c) 2008-2011 Frederic G. Østby
	* @license    http://www.makoframework.com/license
	*/

	class UUID
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
		* Checks if a UUID is valid.
		*
		* @access  public
		* @param   string  The UUID to validate
		* @return  boolean
		*/

		public static function valid($str)
		{
			return (bool) preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/i', $str);
		}

		/**
		* Generates a UUID (v4).
		*
		* @access  public
		* @return  string
		*/

		public static function generate()
		{
			return sprintf
			(
				'%04X%04X-%04X-%04X-%04X-%04X%04X%04X', 
				mt_rand(0, 65535),
				mt_rand(0, 65535),
				mt_rand(0, 65535),
				mt_rand(16384, 20479),
				mt_rand(32768, 49151),
				mt_rand(0, 65535),
				mt_rand(0, 65535),
				mt_rand(0, 65535)
			);
		}
	}
}

/** -------------------- End of file --------------------**/