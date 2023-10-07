<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\database\query;

/**
 * Raw SQL container.
 */
class Raw
{
	/**
	 * Constructor.
	 */
	public function __construct(
		protected string $sql,
		protected array $parameters = []
	) {
	}

	/**
	 * Returns the raw SQL.
	 */
	public function getSql(): string
	{
		return $this->sql;
	}

	/**
	 * Returns the parameters.
	 */
	public function getParameters(): array
	{
		return $this->parameters;
	}
}
