<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\database\query;

use Closure;

use mako\database\query\Raw;

/**
 * Table join.
 *
 * @author  Frederic G. Østby
 */

class Join
{
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
	 * ON conditions.
	 *
	 * @var array
	 */

	protected $conditions = [];

	/**
	 * Constructor.
	 *
	 * @access  public
	 * @param   string  $type   Join type
	 * @param   string  $table  Table we are joining
	 */

	public function __construct($type, $table)
	{
		$this->type  = $type;
		$this->table = $table;
	}

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
	 * Returns ON conditions.
	 *
	 * @access  public
	 * @return  array
	 */

	public function getConditions()
	{
		return $this->conditions;
	}

	/**
	 * Adds a ON condition to the join.
	 *
	 * @access  public
	 * @param   string       $column1    Column name
	 * @param   null|string  $operator   Operator
	 * @param   null|string  $column2    Column name
	 * @param   string       $separator  Condition separator
	 */

	public function on($column1, $operator = null, $column2 = null, $separator = 'AND')
	{
		if($column1 instanceof Closure)
		{
			$join = new self(null, null);

			$column1($join);

			$this->conditions[] =
			[
				'type'      => 'nestedJoinCondition',
				'join'      => $join,
				'separator' => $separator,
			];
		}
		else
		{
			$this->conditions[] =
			[
				'type'      => 'joinCondition',
				'column1'   => $column1,
				'operator'  => $operator,
				'column2'   => $column2,
				'separator' => $separator,
			];
		}

		return $this;
	}

	/**
	 * Adds a raw ON condition to the join.
	 *
	 * @access  public
	 * @param   string  $column1    Column name
	 * @param   string  $operator   Operator
	 * @param   string  $raw        Raw SQL
	 * @param   string  $separator  Condition separator
	 */

	public function onRaw($column1, $operator, $raw, $separator = 'AND')
	{
		return $this->on($column1, $operator, new Raw($raw), $separator);
	}

	/**
	 * Adds a OR ON condition to the join.
	 *
	 * @access  public
	 * @param   string       $column1   Column name
	 * @param   null|string  $operator  Operator
	 * @param   null|string  $column2   Column name
	 */

	public function orOn($column1, $operator = null, $column2 = null)
	{
		return $this->on($column1, $operator, $column2, 'OR');
	}

	/**
	 * Adds a raw OR ON condition to the join.
	 *
	 * @access  public
	 * @param   string  $column1   Column name
	 * @param   string  $operator  Operator
	 * @param   string  $raw       Raw SQL
	 */

	public function orOnRaw($column1, $operator, $raw)
	{
		return $this->onRaw($column1, $operator, $raw, 'OR');
	}
}