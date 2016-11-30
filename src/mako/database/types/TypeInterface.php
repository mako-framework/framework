<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\database\types;

/**
 * Type interface.
 *
 * @author  Frederic G. Østby
 */
interface TypeInterface
{
	/**
	 * Returns the PDO parameter type.
	 *
	 * @access  public
	 * @return  int
	 */
	public function getType(): int;

	/**
	 * Returns the value.
	 *
	 * @access  public
	 * @return  mixed
	 */
	public function getValue();
}
