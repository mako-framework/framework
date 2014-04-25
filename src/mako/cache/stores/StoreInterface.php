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
	public function put($key, $data, $ttl = 0);
	public function has($key);
	public function get($key);
	public function remove($key);
	public function clear();
}