<?php

namespace mako\database\query;

/**
 * Table join.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class Join
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	/**
	 * Join type.
	 *
	 * @var string
	 */

	protected $type;

	/**
	 * Table we are joining.
	 *
	 * @var string
	 */

	protected $table;

	/**
	 * ON clauses.
	 *
	 * @var array
	 */

	protected $clauses = array();

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	 * Constructor.
	 *
	 * @access  public
	 * @param   string  $type  Join type
	 * @param   string  $table  Table we are joining
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
	 * Returns the join type.
	 * 
	 * @access  public
	 * @return  string
	 */

	public function getType()
	{
		return $this->type;
	}

	/**
	 * Returns the table name
	 * 
	 * @access  public
	 * @return  string
	 */

	public function getTable()
	{
		return $this->table;
	}

	/**
	 * Returns ON clauses.
	 * 
	 * @access  public
	 * @return  array
	 */

	public function getClauses()
	{
		return $this->clauses;
	}

	/**
	 * Adds a ON clause to the join.
	 *
	 * @access  public
	 * @param   string  $column1    Column name
	 * @param   string  $operator   Operator
	 * @param   string  $column2    Column name
	 * @param   string  $separator  (optional) Clause separator
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
	 * @param   string  $column1   Column name
	 * @param   string  $operator  Operator
	 * @param   string  $column2   Column name
	 */

	public function orOn($column1, $operator, $column2)
	{
		return $this->on($column1, $operator, $column2, 'OR');
	}
}

/** -------------------- End of file -------------------- **/