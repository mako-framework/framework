<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\validator\plugins;

/**
 * Validator plugin interface.
 *
 * @author  Frederic G. Østby
 */

interface ValidatorPluginInterface
{
	public function getRuleName();
	public function getPackageName();
	public function validate($input, $parameters);
}