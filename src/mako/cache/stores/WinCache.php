<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cache\stores;

use RuntimeException;

use function function_exists;
use function wincache_ucache_add;
use function wincache_ucache_clear;
use function wincache_ucache_delete;
use function wincache_ucache_exists;
use function wincache_ucache_get;
use function wincache_ucache_set;

/**
 * WinCache store.
 *
 * @author Frederic G. Østby
 */
class WinCache extends Store
{
	/**
	 * Constructor.
	 *
	 * @throws \RuntimeException
	 */
	public function __construct()
	{
		if(function_exists('wincache_ucache_set') === false)
		{
			throw new RuntimeException('WinCache is not available on your system.');
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function put(string $key, $data, int $ttl = 0): bool
	{
		return wincache_ucache_set($this->getPrefixedKey($key), $data, $ttl);
	}

	/**
	 * {@inheritdoc}
	 */
	public function putIfNotExists(string $key, $data, int $ttl = 0): bool
	{
		return wincache_ucache_add($this->getPrefixedKey($key), $data, $ttl);
	}

	/**
	 * {@inheritdoc}
	 */
	public function has(string $key): bool
	{
		return wincache_ucache_exists($this->getPrefixedKey($key));
	}

	/**
	 * {@inheritdoc}
	 */
	public function get(string $key)
	{
		$cache = wincache_ucache_get($this->getPrefixedKey($key), $success);

		if($success === true)
		{
			return $cache;
		}

		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function remove(string $key): bool
	{
		return wincache_ucache_delete($this->getPrefixedKey($key));
	}

	/**
	 * {@inheritdoc}
	 */
	public function clear(): bool
	{
		return wincache_ucache_clear();
	}
}
