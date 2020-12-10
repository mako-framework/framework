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
	 * Builder closure.
	 *
	 * @var \Closure
	 */
	protected $query;

	/**
	 * Alias.
	 *
	 * @var string|null
	 */
	protected $alias = null;

	/**
	 * Does the subquery provide its own query builder instance?
	 *
	 * @var bool
	 */
	protected $providesBuilderInstance;

	/**
	 * Constructor.
	 *
	 * @param \Closure    $query                   Builder closure
	 * @param string|null $alias                   Subquery alias
	 * @param bool        $providesBuilderInstance Does the subquery provide its own query builder instance?
	 */
	public function __construct(Closure $query, ?string $alias = null, bool $providesBuilderInstance = false)
	{
		$this->query                   = $query;
		$this->alias                   = $alias;
		$this->providesBuilderInstance = $providesBuilderInstance;
	}

	/**
	 * Sets the subquery alias.
	 *
	 * @param  string                        $alias
	 * @return \mako\database\query\Subquery
	 */
	public function as(string $alias): Subquery
	{
		$this->alias = $alias;

		return $this;
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
	 * Returns the builder closure.
	 *
	 * @return \Closure
	 */
	public function getQuery(): Closure
	{
		return $this->query;
	}

	/**
	 * Returns TRUE if the subquery provides its own query builder instance and FALSE if not.
	 *
	 * @return bool
	 */
	public function providesBuilderInstance(): bool
	{
		return $this->providesBuilderInstance;
	}
}
