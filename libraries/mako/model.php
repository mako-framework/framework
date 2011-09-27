<?php

namespace mako
{
	use \mako\Database;
	
	/**
	* Base model.
	*
	* @author     Frederic G. Østby
	* @copyright  (c) 2008-2011 Frederic G. Østby
	* @license    http://www.makoframework.com/license
	*/

	abstract class Model
	{
		//---------------------------------------------
		// Class variables
		//---------------------------------------------

		/**
		* Database object (PDO).
		*/

		protected $db;

		//---------------------------------------------
		// Class constructor, destructor etc ...
		//---------------------------------------------

		/**
		* Constructor.
		*
		* @access  public
		* @param   string  (optional) Name of the database to use (as defined in the config)
		*/

		public function __construct($database = null)
		{
			$this->db = Database::instance($database);
		}
		
		/**
		* Factory method making method chaining possible right off the bat.
		*
		* @access  public
		* @param   string  (optional) Name of the database to use (as defined in the config)
		* @return  Model
		*/
		
		public static function factory($database = null)
		{
			return new static($database);
		}
	}
}

/** -------------------- End of file --------------------**/