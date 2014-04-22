<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\cache\stores;

/**
 * Store interface.
 *
 * @author  Frederic G. Østby
 */

interface StoreInterface
{
	public function write($key, $data, $ttl = 0);
	public function has($key);
	public function read($key);
	public function delete($key);
	public function clear();
}