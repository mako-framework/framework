<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\cache\stores;

use mako\cache\stores\StoreInterface;

/**
 * APCU store.
 *
 * @author  Frederic G. Østby
 */

class APCU implements StoreInterface
{
	/**
	 * {@inheritdoc}
	 */

	public function put($key, $data, $ttl = 0)
	{
		return apcu_store($key, $data, $ttl);
	}

	/**
	 * {@inheritdoc}
	 */

	public function has($key)
	{
		return apcu_exists($key);
	}

	/**
	 * {@inheritdoc}
	 */

	public function get($key)
	{
		return apcu_fetch($key);
	}

	/**
	 * {@inheritdoc}
	 */

	public function remove($key)
	{
		return apcu_delete($key);
	}

	/**
	 * {@inheritdoc}
	 */

	public function clear()
	{
		return apcu_clear_cache();
	}
}