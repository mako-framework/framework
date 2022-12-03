<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\database\query;

use Closure;

/**
 * Table join.
 */
class Join
{
	/**
	 * ON conditions.
	 *
	 * @var array
	 */
	protected $conditions = [];

	/**
	 * Constructor.
	 *
	 * @param string|null                                                        $type    Join type
	 * @param \mako\database\query\Raw|\mako\database\query\Subquery|string|null $table   Table we are joining
	 * @param bool                                                               $lateral Is it a lateral join?
	 */
	public function __construct(
		protected ?string $type = null,
		protected Raw|Subquery|string|null $table = null,
		protected bool $lateral = false
	)
	{}

	/**
	 * Returns the join type.
	 *
	 * @return string
	 */
	public function getType(): string
	{
		return $this->type;
	}

	/**
	 * Returns the table name.
	 *
	 * @return mixed
	 */
	public function getTable(): mixed
	{
		return $this->table;
	}

	/**
	 * Is it a lateral join?
	 *
	 * @return bool
	 */
	public function isLateral(): bool
	{
		return $this->lateral;
	}

	/**
	 * Returns ON conditions.
	 *
	 * @return array
	 */
	public function getConditions(): array
	{
		return $this->conditions;
	}

	/**
	 * Adds a ON condition to the join.
	 *
	 * @param  \Closure|\mako\database\query\Raw|string $column1   Column name
	 * @param  string|null                              $operator  Operator
	 * @param  \mako\database\query\Raw|string|null     $column2   Column name
	 * @param  string                                   $separator Condition separator
	 * @return \mako\database\query\Join
	 */
	public function on(Closure|Raw|string $column1, ?string $operator = null, Raw|string|null $column2 = null, string $separator = 'AND'): Join
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
	 * @param  \mako\database\query\Raw|string $column1    Column name
	 * @param  string                          $operator   Operator
	 * @param  string                          $raw        Raw SQL
	 * @param  array                           $parameters Parameters
	 * @param  string                          $separator  Condition separator
	 * @return \mako\database\query\Join
	 */
	public function onRaw(Raw|string $column1, string $operator, string $raw, array $parameters = [], string $separator = 'AND'): Join
	{
		return $this->on($column1, $operator, new Raw($raw, $parameters), $separator);
	}

	/**
	 * Adds a OR ON condition to the join.
	 *
	 * @param  \Closure|\mako\database\query\Raw|string $column1  Column name
	 * @param  string|null                              $operator Operator
	 * @param  \mako\database\query\Raw|string|null     $column2  Column name
	 * @return \mako\database\query\Join
	 */
	public function orOn(Closure|Raw|string $column1, ?string $operator = null, Raw|string|null $column2 = null): Join
	{
		return $this->on($column1, $operator, $column2, 'OR');
	}

	/**
	 * Adds a raw OR ON condition to the join.
	 *
	 * @param  \mako\database\query\Raw|string $column1    Column name
	 * @param  string                          $operator   Operator
	 * @param  string                          $raw        Raw SQL
	 * @param  array                           $parameters Parameters
	 * @return \mako\database\query\Join
	 */
	public function orOnRaw(Raw|string $column1, string $operator, string $raw, array $parameters = []): Join
	{
		return $this->onRaw($column1, $operator, $raw, $parameters, 'OR');
	}
}
