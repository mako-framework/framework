<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\database\types;

/**
 * Type interface.
 */
interface TypeInterface
{
	/**
	 * Returns the PDO parameter type.
	 */
	public function getType(): int;

	/**
	 * Returns the value.
	 */
	public function getValue(): mixed;
}
