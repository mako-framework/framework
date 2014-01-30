<?php

namespace mako\cache\adapters;

/**
 * Adapter interface.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

interface AdapterInterface
{
	public function write($key, $data, $ttl = 0);
	public function has($key);
	public function read($key);
	public function delete($key);
	public function clear();
}

/** -------------------- End of file -------------------- **/