<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\database\query\values\out;

use Override;

/**
 * Base value.
 */
abstract class Value implements ValueWithAliasInterface
{
	protected ?string $alias = null;

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function getParameters(): ?array
	{
		return null;
	}

	/**
	 * Sets the value alias.
	 *
	 * @return $this
	 */
	public function as(string $alias): static
	{
		$this->alias = $alias;

		return $this;
	}

	/**
	 * Returns the value alias.
	 */
	public function getAlias(): ?string
	{
		return $this->alias;
	}
}
