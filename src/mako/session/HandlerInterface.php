<?php

namespace mako\session;

/**
 * Session handler interface.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

interface HandlerInterface
{
	public function sessionOpen($savePath, $sessionName);
	public function sessionClose();
	public function sessionRead($key);
	public function sessionWrite($key, $data);
	public function sessionDestroy($id);
	public function sessionGarbageCollector($maxLifetime);
}

/** -------------------- End of file -------------------- **/