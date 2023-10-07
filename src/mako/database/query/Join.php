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
	 */
	protected array $conditions = [];

	/**
	 * Constructor.
	 */
	public function __construct(
		protected ?string $type = null,
		protected null|Raw|string|Subquery $table = null,
	) {
	}

	/**
	 * Returns the join type.
	 */
	public function getType(): string
	{
		return $this->type;
	}

	/**
	 * Returns the table name.
	 */
	public function getTable(): mixed
	{
		return $this->table;
	}

	/**
	 * Does this join have conditions?
	 */
	public function hasConditions(): bool
	{
		return !empty($this->conditions);
	}

	/**
	 * Returns ON conditions.
	 */
	public function getConditions(): array
	{
		return $this->conditions;
	}

	/**
	 * Adds a ON condition to the join.
	 */
	public function on(Closure|Raw|string $column1, ?string $operator = null, null|Raw|string $column2 = null, string $separator = 'AND'): Join
	{
		if ($column1 instanceof Closure) {
			$join = new self;

			$column1($join);

			$this->conditions[] = [
				'type'      => 'nestedJoinCondition',
				'join'      => $join,
				'separator' => $separator,
			];
		}
		else {
			$this->conditions[] = [
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
	 */
	public function onRaw(Raw|string $column1, string $operator, string $raw, array $parameters = [], string $separator = 'AND'): Join
	{
		return $this->on($column1, $operator, new Raw($raw, $parameters), $separator);
	}

	/**
	 * Adds a OR ON condition to the join.
	 */
	public function orOn(Closure|Raw|string $column1, ?string $operator = null, null|Raw|string $column2 = null): Join
	{
		return $this->on($column1, $operator, $column2, 'OR');
	}

	/**
	 * Adds a raw OR ON condition to the join.
	 */
	public function orOnRaw(Raw|string $column1, string $operator, string $raw, array $parameters = []): Join
	{
		return $this->onRaw($column1, $operator, $raw, $parameters, 'OR');
	}
}
