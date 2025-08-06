<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\syringe\attributes;

/**
 * Injector interface.
 */
interface InjectorInterface
{
	/**
	 * Returns the parameter value.
	 */
	public function getParameterValue(): mixed;
}
