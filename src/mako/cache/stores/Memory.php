<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cache\stores;

/**
 * Memory store.
 *
 * @author Frederic G. Østby
 */
class Memory extends Store implements IncrementDecrementInterface
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
	public function put(string $key, $data, int $ttl = 0): bool
	{
		$ttl = (((int) $ttl === 0) ? 31556926 : (int) $ttl) + time();

		$this->cache[$key] = ['data' => $data, 'ttl' => $ttl];

		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function increment(string $key, int $step = 1)
	{
		if($this->has($key))
		{
			return $this->cache[$key]['data'] = $this->cache[$key]['data'] + $step;
		}

		$this->put($key, $incremented = 0 + $step);

		return $incremented;
	}

	/**
	 * {@inheritdoc}
	 */
	public function decrement(string $key, int $step = 1)
	{
		if($this->has($key))
		{
			return $this->cache[$key]['data'] = $this->cache[$key]['data'] - $step;
		}

		$this->put($key, $decremented = 0 - $step);

		return $decremented;
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
