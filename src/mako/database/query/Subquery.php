<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\database\query;

/**
 * Subquery container.
 *
 * @author Frederic G. Østby
 */
class Subquery
{
	/**
	 * Builder closure or query builder instance.
	 *
	 * @var \Closure|\mako\database\query\Query
	 */
	protected $query;

	/**
	 * Alias.
	 *
	 * @var string
	 */
	protected $alias = null;

	/**
	 * Constructor.
	 *
	 * @param \Closure|\mako\database\query\Query $query Builder closure or query builder instance
	 * @param string|null                         $alias Subquery alias
	 */
	public function __construct($query, ?string $alias = null)
	{
		$this->query = $query;
		$this->alias = $alias;
	}

	/**
	 * Returns the subquery alias.
	 *
	 * @return string|null
	 */
	public function getAlias(): ?string
	{
		return $this->alias;
	}

	/**
	 * Returns the builder closure or query builder instance.
	 *
	 * @return \Closure|\mako\database\query\Query
	 */
	public function getQuery()
	{
		return $this->query;
	}
}
