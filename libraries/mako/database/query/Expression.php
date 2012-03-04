<?php

namespace mako\database\query;

/**
* Query expression.
*
* @author     Frederic G. Østby
* @copyright  (c) 2008-2012 Frederic G. Østby
* @license    http://www.makoframework.com/license
*/

class Expression
{
	//---------------------------------------------
	// Class variables
	//---------------------------------------------

	/**
	*
	*/

	protected $expression;

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	*
	*/

	public function __construct($expression)
	{
		$this->expression = $expression;
	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	*
	*/

	public function get()
	{
		return $this->expression;
	}
}

/** -------------------- End of file --------------------**/