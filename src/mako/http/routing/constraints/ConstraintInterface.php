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
	 * Sets the constraint parameters.
	 *
	 * @param array $parameters Middleware parameters
	 */
	public function setParameters(array $parameters);

	/**
	 * Returns true if the constraint is satisfied and false if not.
	 *
	 * @return bool
	 */
	public function isSatisfied(): bool;
}
