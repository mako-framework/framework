<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cache\stores;

use RuntimeException;

use function apcu_add;
use function apcu_clear_cache;
use function apcu_dec;
use function apcu_delete;
use function apcu_entry;
use function apcu_exists;
use function apcu_fetch;
use function apcu_inc;
use function apcu_store;
use function function_exists;

/**
 * APCU store.
 */
class APCU extends Store implements IncrementDecrementInterface
{
	/**
	 * Constructor.
	 */
	public function __construct()
	{
		if(function_exists('apcu_store') === false)
		{
			throw new RuntimeException('APCU is not available on your system.');
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function put(string $key, $data, int $ttl = 0): bool
	{
		return apcu_store($this->getPrefixedKey($key), $data, $ttl);
	}

	/**
	 * {@inheritDoc}
	 */
	public function putIfNotExists(string $key, $data, int $ttl = 0): bool
	{
		return apcu_add($this->getPrefixedKey($key), $data, $ttl);
	}

	/**
	 * {@inheritDoc}
	 */
	public function increment(string $key, int $step = 1)
	{
		return apcu_inc($this->getPrefixedKey($key), $step);
	}

	/**
	 * {@inheritDoc}
	 */
	public function decrement(string $key, int $step = 1)
	{
		return apcu_dec($this->getPrefixedKey($key), $step);
	}

	/**
	 * {@inheritDoc}
	 */
	public function has(string $key): bool
	{
		return apcu_exists($this->getPrefixedKey($key));
	}

	/**
	 * {@inheritDoc}
	 */
	public function get(string $key)
	{
		$value = apcu_fetch($this->getPrefixedKey($key), $success);

		return $success ? $value : null;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getOrElse(string $key, callable $data, int $ttl = 0)
	{
		return apcu_entry($this->getPrefixedKey($key), $data, $ttl);
	}

	/**
	 * {@inheritDoc}
	 */
	public function remove(string $key): bool
	{
		return apcu_delete($this->getPrefixedKey($key));
	}

	/**
	 * {@inheritDoc}
	 */
	public function clear(): bool
	{
		return apcu_clear_cache();
	}
}
