<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\http\routing\traits;

use mako\syringe\traits\ContainerAwareTrait;

/**
 * Controller helper trait.
 */
trait ControllerHelperTrait
{
	use ContainerAwareTrait;
	use RedirectTrait {
		redirect as redirectResponse;
	}
}
