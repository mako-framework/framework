<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cache\stores;

use RuntimeException;

/**
 * APCU store.
 *
 * @author Frederic G. Østby
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
	 * {@inheritdoc}
	 */
	public function put(string $key, $data, int $ttl = 0): bool
	{
		return apcu_store($this->getPrefixedKey($key), $data, $ttl);
	}

	/**
	 * {@inheritdoc}
	 */
	public function putIfNotExists(string $key, $data, int $ttl = 0): bool
	{
		return apcu_add($this->getPrefixedKey($key), $data, $ttl);
	}

	/**
	 * {@inheritdoc}
	 */
	public function increment(string $key, int $step = 1)
	{
		return apcu_inc($this->getPrefixedKey($key), $step);
	}

	/**
	 * {@inheritdoc}
	 */
	public function decrement(string $key, int $step = 1)
	{
		return apcu_dec($this->getPrefixedKey($key), $step);
	}

	/**
	 * {@inheritdoc}
	 */
	public function has(string $key): bool
	{
		return apcu_exists($this->getPrefixedKey($key));
	}

	/**
	 * {@inheritdoc}
	 */
	public function get(string $key)
	{
		return apcu_fetch($this->getPrefixedKey($key));
	}

	/**
	 * {@inheritdoc}
	 */
	public function getOrElse(string $key, callable $data, int $ttl = 0)
	{
		return apcu_entry($this->getPrefixedKey($key), $data, $ttl);
	}

	/**
	 * {@inheritdoc}
	 */
	public function remove(string $key): bool
	{
		return apcu_delete($this->getPrefixedKey($key));
	}

	/**
	 * {@inheritdoc}
	 */
	public function clear(): bool
	{
		return apcu_clear_cache();
	}
}
