<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\http\routing;

use mako\syringe\ContainerAwareTrait;

/**
 * Base controller.
 *
 * @author  Frederic G. Østby
 */

abstract class Controller
{
	use ContainerAwareTrait;
}