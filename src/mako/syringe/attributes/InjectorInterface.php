<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\syringe\attributes;

use mako\syringe\Container;
use ReflectionParameter;

/**
 * Injector interface.
 */
interface InjectorInterface
{
	/**
	 * Returns the parameter value.
	 */
	public function getParameterValue(Container $container, ReflectionParameter $parameter): mixed;
}
