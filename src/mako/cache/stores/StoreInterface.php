<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cache\stores;

/**
 * Store interface.
 */
interface StoreInterface
{
	/**
	 * Store data in the cache.
	 */
	public function put(string $key, mixed $data, int $ttl = 0): bool;

	/**
	 * Store data in the cache if it doesn't already exist.
	 */
	public function putIfNotExists(string $key, mixed $data, int $ttl = 0): bool;

	/**
	 * Returns TRUE if the cache key exists and FALSE if not.
	 */
	public function has(string $key): bool;

	/**
	 * Fetch data from the cache. NULL will be returned if the item does not exist.
	 */
	public function get(string $key): mixed;

	/**
	 * Fetch data from the cache or store it if it doesn't already exist.
	 */
	public function getOrElse(string $key, callable $callable, int $ttl = 0): mixed;

	/**
	 * Delete data from the cache.
	 */
	public function remove(string $key): bool;

	/**
	 * Clears the cache.
	 */
	public function clear(): bool;
}
