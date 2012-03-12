<?php

namespace mako\database\query;

/**
* Table join.
*
* @author     Frederic G. Østby
* @copyright  (c) 2008-2012 Frederic G. Østby
* @license    http://www.makoframework.com/license
*/

class Join
{
	//---------------------------------------------
	// Class variables
	//---------------------------------------------

	/**
	* Join type.
	*
	* @var string
	*/

	public $type;

	/**
	* Table we are joining.
	*
	* @var string
	*/

	public $table;

	/**
	* ON clauses.
	*
	* @var array
	*/

	public $clauses = array();

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	* Constructor.
	*
	* @access  public
	* @param   string  Join type
	* @param   string  Table we are joining
	*/

	public function __construct($type, $table)
	{
		$this->type  = $type;
		$this->table = $table;
	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	* Adds a ON clause to the join.
	*
	* @access  public
	* @param   string  Column name
	* @param   string  Operator
	* @param   string  Column name
	* @param   string  (optional) Clause separator
	*/

	public function on($column1, $operator, $column2, $separator = 'AND')
	{
		$this->clauses[] = array
		(
			'column1'   => $column1,
			'operator'  => $operator,
			'column2'   => $column2,
			'separator' => $separator,
		);

		return $this;
	}

	/**
	* Adds a OR ON clause to the join.
	*
	* @access  public
	* @param   string  Column name
	* @param   string  Operator
	* @param   string  Column name
	*/

	public function orOn($column1, $operator, $column2)
	{
		return $this->on($column1, $operator, $column2, 'OR');
	}
}

/** -------------------- End of file --------------------**/