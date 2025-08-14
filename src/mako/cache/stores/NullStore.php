<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cache\stores;

use Override;

/**
 * Null store.
 */
class NullStore extends Store implements IncrementDecrementInterface
{
	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function put(string $key, mixed $data, int $ttl = 0): bool
	{
		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function increment(string $key, int $step = 1): false|int
	{
		return 0 + $step;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function decrement(string $key, int $step = 1): false|int
	{
		return 0 - $step;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function has(string $key): bool
	{
		return false;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function get(string $key): mixed
	{
		return null;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function remove(string $key): bool
	{
		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function clear(): bool
	{
		return true;
	}
}
