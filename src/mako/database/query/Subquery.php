<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\database\query;

use Closure;

/**
 * Subquery container.
 */
class Subquery
{
	/**
	 * Constructor.
	 */
	public function __construct(
		protected Closure $query,
		protected ?string $alias = null,
		protected bool $providesBuilderInstance = false
	) {
	}

	/**
	 * Sets the subquery alias.
	 *
	 * @return $this
	 */
	public function as(string $alias): Subquery
	{
		$this->alias = $alias;

		return $this;
	}

	/**
	 * Returns the subquery alias.
	 */
	public function getAlias(): ?string
	{
		return $this->alias;
	}

	/**
	 * Returns the builder closure.
	 */
	public function getQuery(): Closure
	{
		return $this->query;
	}

	/**
	 * Returns TRUE if the subquery provides its own query builder instance and FALSE if not.
	 */
	public function providesBuilderInstance(): bool
	{
		return $this->providesBuilderInstance;
	}
}
