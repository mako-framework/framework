<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\database\query\values\out;

use mako\database\query\values\ValueInterface;

/**
 * Value interface.
 */
interface ValueWithAliasInterface extends ValueInterface
{
	/**
	 * Sets the value alias.
	 *
	 * @return $this
	 */
	public function as(string $alias): static;

	/**
	 * Returns the value alias.
	 */
	public function getAlias(): ?string;
}
