<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\security\crypto\adapters;

/**
 * Adapter interface.
 *
 * @author  Frederic G. Østby
 */

interface AdapterInterface
{
	public function encrypt($data);
	public function decrypt($data);
}

