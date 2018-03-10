<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator\rules;

/**
 * With parameters interface.
 *
 * @author Frederic G. Østby
 */
interface WithParametersInterface
{
	/**
	 * Sets the validation rule parameters.
	 *
	 * @param array $parameters Parameters
	 */
	public function setParameters(array $parameters);
}
