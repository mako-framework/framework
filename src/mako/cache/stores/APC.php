<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\cache\stores;

use \mako\cache\stores\StoreInterface;

/**
 * APC store.
 *
 * @author  Frederic G. Østby
 */

class APC implements StoreInterface
{
	/**
	 * {@inheritdoc}
	 */

	public function put($key, $data, $ttl = 0)
	{
		return apc_store($key, $data, $ttl);
	}

	/**
	 * {@inheritdoc}
	 */

	public function has($key)
	{
		return apc_exists($key);
	}

	/**
	 * {@inheritdoc}
	 */

	public function get($key)
	{
		return apc_fetch($key);
	}

	/**
	 * {@inheritdoc}
	 */

	public function remove($key)
	{
		return apc_delete($key);
	}

	/**
	 * {@inheritdoc}
	 */

	public function clear()
	{
		return apc_clear_cache('user');
	}
}