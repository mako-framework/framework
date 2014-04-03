<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\session\store;

/**
 * Store interface.
 *
 * @author  Frederic G. Østby
 */

interface StoreInterface
{
	public function write($sessionId, $sessionData, $dataTTL);
	public function read($sessionId);
	public function delete($sessionId);
	public function gc($dataTTL);
}

