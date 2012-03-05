<?php

namespace mako\database\query;

/**
* Raw SQL container.
*
* @author     Frederic G. Østby
* @copyright  (c) 2008-2012 Frederic G. Østby
* @license    http://www.makoframework.com/license
*/

class Raw
{
	//---------------------------------------------
	// Class variables
	//---------------------------------------------

	/**
	*
	*/

	protected $sql;

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	*
	*/

	public function __construct($sql)
	{
		$this->sql = $sql;
	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	*
	*/

	public function get()
	{
		return $this->sql;
	}
}

/** -------------------- End of file --------------------**/