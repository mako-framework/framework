<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\cache\stores;

use mako\cache\stores\StoreInterface;

/**
 * Memory store.
 *
 * @author  Frederic G. Østby
 */
class Memory implements StoreInterface
{
	/**
	 * Cache data.
	 *
	 * @var array
	 */
	protected $cache = [];

	/**
	 * {@inheritdoc}
	 */
	public function put($key, $data, $ttl = 0)
	{
		$ttl = (((int) $ttl === 0) ? 31556926 : (int) $ttl) + time();

		$this->cache[$key] = ['data' => $data, 'ttl' => $ttl];

		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function has($key)
	{
		return (isset($this->cache[$key]) && $this->cache[$key]['ttl'] > time());
	}

	/**
	 * {@inheritdoc}
	 */
	public function get($key)
	{
		if(isset($this->cache[$key]))
		{
			if($this->cache[$key]['ttl'] > time())
			{
				return $this->cache[$key]['data'];
			}
			else
			{
				$this->remove($key);

				return false;
			}
		}
		else
		{
			return false;
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function remove($key)
	{
		if(isset($this->cache[$key]))
		{
			unset($this->cache[$key]);

			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function clear()
	{
		$this->cache = [];

		return true;
	}
}