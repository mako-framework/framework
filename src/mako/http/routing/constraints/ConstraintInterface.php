<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\http\routing\constraints;

/**
 * Constraint interface.
 */
interface ConstraintInterface
{
	/**
	 * Returns true if the constraint is satisfied and false if not.
	 */
	public function isSatisfied(): bool;
}
