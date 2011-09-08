<?php

namespace mako
{
	/**
	* Mako Framework exception class.
	*
	* @author     Frederic G. Østby
	* @copyright  (c) 2008-2011 Frederic G. Østby
	* @license    http://www.makoframework.com/license
	*/

	class Exception extends \Exception
	{
		//---------------------------------------------
		// Class variables
		//---------------------------------------------

		// Nothing here

		//---------------------------------------------
		// Class constructor, destructor etc ...
		//---------------------------------------------

		/**
		* Constructor.
		*
		* @access  public
		* @param   string  Exception message
		* @param   int     Exception code
		*/

		public function __construct($message, $code = 0)
		{
			parent::__construct($message, $code);
		}
	}	
}

/** -------------------- End of file --------------------**/