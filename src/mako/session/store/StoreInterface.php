<?php

namespace mako\session\store;

/**
 * Store interface.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

interface StoreInterface
{
	public function write($sessionId, $sessionData, $dataTTL);
	public function read($sessionId);
	public function delete($sessionId);
	public function gc($dataTTL);
}

/** -------------------- End of file -------------------- **/