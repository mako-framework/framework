<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cache\stores;

/**
 * Store interface.
 *
 * @author Frederic G. Østby
 */
interface StoreInterface
{
	/**
	 * Store data in the cache.
	 *
	 * @param  string $key  Cache key
	 * @param  mixed  $data The data to store
	 * @param  int    $ttl  Time to live
	 * @return bool
	 */
	public function put(string $key, $data, int $ttl = 0): bool;

	/**
	 * Store data in the cache if it doesn't already exist.
	 *
	 * @param  string $key  Cache key
	 * @param  mixed  $data The data to store
	 * @param  int    $ttl  Time to live
	 * @return bool
	 */
	public function putIfNotExists(string $key, $data, int $ttl = 0): bool;

	/**
	 * Returns TRUE if the cache key exists and FALSE if not.
	 *
	 * @param  string $key Cache key
	 * @return bool
	 */
	public function has(string $key): bool;

	/**
	 * Fetch data from the cache.
	 *
	 * @param  string     $key Cache key
	 * @return mixed|null
	 */
	public function get(string $key);

	/**
	 * Fetch data from the cache or store it if it doesn't already exist.
	 *
	 * @param  string   $key      Cache key
	 * @param  callable $callable Callable that returns the data we want to store
	 * @param  int      $ttl      Time to live
	 * @return mixed
	 */
	public function getOrElse(string $key, callable $callable, int $ttl = 0);

	/**
	 * Delete data from the cache.
	 *
	 * @param  string $key Cache key
	 * @return bool
	 */
	public function remove(string $key): bool;

	/**
	 * Clears the cache.
	 *
	 * @return bool
	 */
	public function clear(): bool;
}
