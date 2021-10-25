<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cache\stores;

/**
 * Base store.
 *
 * @author Frederic G. Østby
 */
abstract class Store implements StoreInterface
{
	/**
	 * Prefix.
	 *
	 * @var string|null
	 */
	protected $prefix;

	/**
	 * Sets the cache key prefix.
	 *
	 * @param  string $prefix Prefix
	 * @return $this
	 */
	public function setPrefix(string $prefix)
	{
		$this->prefix = $prefix;

		return $this;
	}

	/**
	 * Returns the cache key prefix.
	 *
	 * @return string|null
	 */
	public function getPrefix(): ?string
	{
		return $this->prefix;
	}

	/**
	 * Returns a prefixed key.
	 *
	 * @param  string $key Key
	 * @return string
	 */
	protected function getPrefixedKey(string $key): string
	{
		return empty($this->prefix) ? $key : "{$this->prefix}.{$key}";
	}

	/**
	 * {@inheritDoc}
	 */
	public function putIfNotExists(string $key, $data, int $ttl = 0): bool
	{
		if($this->has($key) === false)
		{
			return $this->put($key, $data, $ttl);
		}

		return false;
	}

	/**
	 * Fetch data from the cache and replace it.
	 *
	 * @param  string $key  Cache key
	 * @param  mixed  $data The data to store
	 * @param  int    $ttl  Time to live
	 * @return mixed
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
	 * @param  string $key Cache key
	 * @return mixed
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
	 * {@inheritDoc}
	 */
	public function getOrElse(string $key, callable $data, int $ttl = 0)
	{
		$cached = $this->get($key);

		if($cached === false)
		{
			$cached = $data();

			$this->put($key, $cached, $ttl);
		}

		return $cached;
	}
}
