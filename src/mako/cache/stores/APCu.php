<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cache\stores;

use Override;

use function apcu_add;
use function apcu_clear_cache;
use function apcu_dec;
use function apcu_delete;
use function apcu_entry;
use function apcu_exists;
use function apcu_fetch;
use function apcu_inc;
use function apcu_store;

/**
 * APCu store.
 */
class APCu extends Store implements IncrementDecrementInterface
{
	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function put(string $key, mixed $data, int $ttl = 0): bool
	{
		return apcu_store($this->getPrefixedKey($key), $data, $ttl);
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function putIfNotExists(string $key, mixed $data, int $ttl = 0): bool
	{
		return apcu_add($this->getPrefixedKey($key), $data, $ttl);
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function increment(string $key, int $step = 1): false|int
	{
		return apcu_inc($this->getPrefixedKey($key), $step);
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function decrement(string $key, int $step = 1): false|int
	{
		return apcu_dec($this->getPrefixedKey($key), $step);
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function has(string $key): bool
	{
		return apcu_exists($this->getPrefixedKey($key));
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function get(string $key): mixed
	{
		$value = apcu_fetch($this->getPrefixedKey($key), $success);

		return $success ? $value : null;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function getOrElse(string $key, callable $data, int $ttl = 0): mixed
	{
		return apcu_entry($this->getPrefixedKey($key), $data, $ttl);
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function remove(string $key): bool
	{
		return apcu_delete($this->getPrefixedKey($key));
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function clear(): bool
	{
		return apcu_clear_cache();
	}
}
