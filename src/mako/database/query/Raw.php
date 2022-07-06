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
	 *
	 * @param string $sql        Raw SQL
	 * @param array  $parameters Parameters
	 */
	public function __construct(
		protected string $sql,
		protected array $parameters = []
	)
	{}

	/**
	 * Returns the raw SQL.
	 *
	 * @return string
	 */
	public function getSql(): string
	{
		return $this->sql;
	}

	/**
	 * Returns the parameters.
	 *
	 * @return array
	 */
	public function getParameters(): array
	{
		return $this->parameters;
	}
}
