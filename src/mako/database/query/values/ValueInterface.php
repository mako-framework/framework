<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\database\query\values;

use mako\database\query\compilers\Compiler;

/**
 * Value interface.
 */
interface ValueInterface
{
	/**
	 * Returns the SQL.
	 */
	public function getSql(Compiler $compiler): string;

	/**
	 * Returns the parameters.
	 */
	public function getParameters(): ?array;
}
