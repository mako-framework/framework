<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\cache\stores;

use mako\cache\stores\StoreInterface;
use mako\cache\stores\traits\GetOrElseTrait;

/**
 * Memory store.
 *
 * @author  Frederic G. Østby
 */
class Memory implements StoreInterface
{
	use GetOrElseTrait;

	/**
	 * Cache data.
	 *
	 * @var array
	 */
	protected $cache = [];

	/**
	 * {@inheritdoc}
	 */
	public function put(string $key, $data, int $ttl = 0): bool
	{
		$ttl = (((int) $ttl === 0) ? 31556926 : (int) $ttl) + time();

		$this->cache[$key] = ['data' => $data, 'ttl' => $ttl];

		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function has(string $key): bool
	{
		return (isset($this->cache[$key]) && $this->cache[$key]['ttl'] > time());
	}

	/**
	 * {@inheritdoc}
	 */
	public function get(string $key)
	{
		if(isset($this->cache[$key]))
		{
			if($this->cache[$key]['ttl'] > time())
			{
				return $this->cache[$key]['data'];
			}

			$this->remove($key);

			return false;
		}

		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function remove(string $key): bool
	{
		if(isset($this->cache[$key]))
		{
			unset($this->cache[$key]);

			return true;
		}

		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function clear(): bool
	{
		$this->cache = [];

		return true;
	}
}
