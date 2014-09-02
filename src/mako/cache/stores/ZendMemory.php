<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\cache\stores;

use \mako\cache\stores\StoreInterface;

/**
 * Zend memory store.
 *
 * @author  Frederic G. Østby
 */

class ZendMemory implements StoreInterface
{
	/**
	 * {@inheritdoc}
	 */

	public function put($key, $data, $ttl = 0)
	{
		return zend_shm_cache_store($key, $data, $ttl);
	}

	/**
	 * {@inheritdoc}
	 */

	public function has($key)
	{
		return (zend_disk_cache_fetch($key) !== false);
	}

	/**
	 * {@inheritdoc}
	 */

	public function get($key)
	{
		return zend_shm_cache_fetch($key);
	}

	/**
	 * {@inheritdoc}
	 */

	public function remove($key)
	{
		return zend_shm_cache_delete($key);
	}

	/**
	 * {@inheritdoc}
	 */

	public function clear()
	{
		return zend_shm_cache_clear();
	}
}