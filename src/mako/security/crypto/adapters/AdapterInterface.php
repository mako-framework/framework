<?php

namespace mako\security\crypto\adapters;

/**
 * Adapter interface.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

interface AdapterInterface
{
	public function encrypt($data);
	public function decrypt($data);
}

