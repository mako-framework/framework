<?php

namespace mako\validator\plugins;

/**
 * Validator plugin interface.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

interface ValidatorPluginInterface
{
	public function getRuleName();
	public function getPackageName();
	public function validate($input, $parameters);
}

