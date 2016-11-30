<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\cache;

use mako\cache\stores\StoreInterface;

/**
 * Cache wrapper.
 *
 * @author  Frederic G. Østby
 */
class Cache
{
	/**
	 * Cache store.
	 *
	 * @var \mako\cache\stores\StoreInterface
	 */
	protected $store;

	/**
	 * Cache prefix.
	 *
	 * @var string
	 */
	protected $prefix;

	/**
	 * Constructor.
	 *
	 * @access  public
	 * @param   \mako\cache\stores\StoreInterface  $store   Cache store
	 * @param   null|string                        $prefix  Cache prefix
	 */
	public function __construct(StoreInterface $store, string $prefix = null)
	{
		$this->store = $store;

		$this->prefix = $prefix;
	}

	/**
	 * Returns a prefixed cache key.
	 *
	 * @access  protected
	 * @param   string     $key  Cache key
	 * @return  string
	 */
	protected function prefixedKey(string $key): string
	{
		return empty($this->prefix) ? $key : $this->prefix . '.' . $key;
	}

	/**
	 * Store data in the cache.
	 *
	 * @access  public
	 * @param   string  $key   Cache key
	 * @param   mixed   $data  The data to store
	 * @param   int     $ttl   Time to live
	 * @return  bool
	 */
	public function put(string $key, $data, int $ttl = 0): bool
	{
		return $this->store->put($this->prefixedKey($key), $data, $ttl);
	}

	/**
	 * Returns TRUE if the cache key exists and FALSE if not.
	 *
	 * @access  public
	 * @param   string   $key  Cache key
	 * @return  bool
	 */
	public function has(string $key): bool
	{
		return $this->store->has($this->prefixedKey($key));
	}

	/**
	 * Fetch data from the cache.
	 *
	 * @access  public
	 * @param   string  $key  Cache key
	 * @return  mixed
	 */
	public function get(string $key)
	{
		return $this->store->get($this->prefixedKey($key));
	}

	/**
	 * Fetch data from the cache or store it if it doesn't already exist.
	 *
	 * @access  public
	 * @param   string    $key   Cache key
	 * @param   callable  $data  Closure that returns the data we want to store
	 * @param   int       $ttl   Time to live
	 * @return  mixed
	 */
	public function getOrElse(string $key, callable $data, int $ttl = 0)
	{
		return $this->store->getOrElse($this->prefixedKey($key), $data, $ttl);
	}

	/**
	 * Fetch data from the cache and replace it.
	 *
	 * @access  public
	 * @param   string  $key   Cache key
	 * @param   mixed   $data  The data to store
	 * @param   int     $ttl   Time to live
	 * @return  mixed
	 */
	public function getAndPut(string $key, $data, int $ttl = 0)
	{
		$storedValue = $this->get($key);

		$this->put($key, $data, $ttl);

		return $storedValue;
	}
	/**
	 * Fetch data from the cache and remove it.
	 *
	 * @access  public
	 * @param   string  $key  Cache key
	 * @return  mixed
	 */
	public function getAndRemove(string $key)
	{
		$storedValue = $this->get($key);

		if($storedValue !== false)
		{
			$this->remove($key);
		}

		return $storedValue;
	}

	/**
	 * Delete data from the cache.
	 *
	 * @access  public
	 * @param   string   $key  Cache key
	 * @return  bool
	 */
	public function remove(string $key): bool
	{
		return $this->store->remove($this->prefixedKey($key));
	}

	/**
	 * Clears the user cache.
	 *
	 * @access  public
	 * @return  bool
	 */
	public function clear(): bool
	{
		return $this->store->clear();
	}
}
