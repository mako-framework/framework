<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cache\stores;

use Override;
use Time\Duration;

use function time;

/**
 * Memory store.
 */
class Memory extends Store implements IncrementDecrementInterface
{
	/**
	 * Cache data.
	 */
	protected array $cache = [];

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function put(string $key, mixed $data, Duration|int $ttl = 0): bool
	{
		$ttl = $this->normalizeTtl($ttl);

		$ttl = (($ttl === 0) ? 31556926 : $ttl) + time();

		$this->cache[$key] = ['data' => $data, 'ttl' => $ttl];

		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function increment(string $key, int $step = 1): false|int
	{
		if ($this->has($key)) {
			return $this->cache[$key]['data'] = $this->cache[$key]['data'] + $step;
		}

		$this->put($key, $incremented = 0 + $step);

		return $incremented;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function decrement(string $key, int $step = 1): false|int
	{
		if ($this->has($key)) {
			return $this->cache[$key]['data'] = $this->cache[$key]['data'] - $step;
		}

		$this->put($key, $decremented = 0 - $step);

		return $decremented;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function has(string $key): bool
	{
		return isset($this->cache[$key]) && $this->cache[$key]['ttl'] > time();
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function get(string $key): mixed
	{
		if (isset($this->cache[$key])) {
			if ($this->cache[$key]['ttl'] > time()) {
				return $this->cache[$key]['data'];
			}

			$this->remove($key);
		}

		return null;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function remove(string $key): bool
	{
		if (isset($this->cache[$key])) {
			unset($this->cache[$key]);

			return true;
		}

		return false;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function clear(): bool
	{
		$this->cache = [];

		return true;
	}
}
