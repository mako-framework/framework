<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\database\query;

use Closure;

/**
 * Subquery container.
 *
 * @author Frederic G. Østby
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
	 * Constructor.
	 *
	 * @param \Closure    $query Builder closure
	 * @param string|null $alias Subquery alias
	 */
	public function __construct(Closure $query, ?string $alias = null)
	{
		$this->query = $query;
		$this->alias = $alias;
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
}
