<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\http\routing\constraints;

/**
 * Constraint interface.
 *
 * @author Frederic G. Østby
 */
interface ConstraintInterface
{
	/**
	 * Returns true if the constraint is satisfied and false if not.
	 *
	 * @return bool
	 */
	public function isSatisfied(): bool;
}
