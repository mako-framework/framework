<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cache\stores;

/**
 * Null store.
 */
class NullStore extends Store implements IncrementDecrementInterface
{
	/**
	 * {@inheritDoc}
	 */
	public function put(string $key, mixed $data, int $ttl = 0): bool
	{
		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function increment(string $key, int $step = 1)
	{
		return 0 + $step;
	}

	/**
	 * {@inheritDoc}
	 */
	public function decrement(string $key, int $step = 1)
	{
		return 0 - $step;
	}

	/**
	 * {@inheritDoc}
	 */
	public function has(string $key): bool
	{
		return false;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get(string $key): mixed
	{
		return null;
	}

	/**
	 * {@inheritDoc}
	 */
	public function remove(string $key): bool
	{
		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function clear(): bool
	{
		return true;
	}
}
