<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\database\query;

use Closure;

use mako\database\query\Raw;

/**
 * Table join.
 *
 * @author Frederic G. Østby
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
	 * @access public
	 * @param null|string $type  Join type
	 * @param null|mixed  $table Table we are joining
	 */
	public function __construct(string $type = null, $table = null)
	{
		$this->type  = $type;
		$this->table = $table;
	}

	/**
	 * Returns the join type.
	 *
	 * @access public
	 * @return string
	 */
	public function getType(): string
	{
		return $this->type;
	}

	/**
	 * Returns the table name
	 *
	 * @access public
	 * @return mixed
	 */
	public function getTable()
	{
		return $this->table;
	}

	/**
	 * Returns ON conditions.
	 *
	 * @access public
	 * @return array
	 */
	public function getConditions(): array
	{
		return $this->conditions;
	}

	/**
	 * Adds a ON condition to the join.
	 *
	 * @access public
	 * @param  string                    $column1   Column name
	 * @param  null|string               $operator  Operator
	 * @param  null|string               $column2   Column name
	 * @param  string                    $separator Condition separator
	 * @return \mako\database\query\Join
	 */
	public function on($column1, string $operator = null, $column2 = null, string $separator = 'AND'): Join
	{
		if($column1 instanceof Closure)
		{
			$join = new self;

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
	 * @access public
	 * @param  string                    $column1   Column name
	 * @param  string                    $operator  Operator
	 * @param  string                    $raw       Raw SQL
	 * @param  string                    $separator Condition separator
	 * @return \mako\database\query\Join
	 */
	public function onRaw($column1, string $operator, string $raw, string $separator = 'AND'): Join
	{
		return $this->on($column1, $operator, new Raw($raw), $separator);
	}

	/**
	 * Adds a OR ON condition to the join.
	 *
	 * @access public
	 * @param  string                    $column1  Column name
	 * @param  null|string               $operator Operator
	 * @param  null|string               $column2  Column name
	 * @return \mako\database\query\Join
	 */
	public function orOn($column1, string $operator = null, $column2 = null): Join
	{
		return $this->on($column1, $operator, $column2, 'OR');
	}

	/**
	 * Adds a raw OR ON condition to the join.
	 *
	 * @access public
	 * @param  string                    $column1  Column name
	 * @param  string                    $operator Operator
	 * @param  string                    $raw      Raw SQL
	 * @return \mako\database\query\Join
	 */
	public function orOnRaw($column1, string $operator, string $raw): Join
	{
		return $this->onRaw($column1, $operator, $raw, 'OR');
	}
}
