<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cache\stores;

use Override;

/**
 * Base store.
 */
abstract class Store implements StoreInterface
{
	/**
	 * Prefix.
	 */
	protected ?string $prefix = null;

	/**
	 * Sets the cache key prefix.
	 *
	 * @return $this
	 */
	public function setPrefix(string $prefix): static
	{
		$this->prefix = $prefix;

		return $this;
	}

	/**
	 * Returns the cache key prefix.
	 */
	public function getPrefix(): ?string
	{
		return $this->prefix;
	}

	/**
	 * Returns a prefixed key.
	 */
	protected function getPrefixedKey(string $key): string
	{
		return empty($this->prefix) ? $key : "{$this->prefix}.{$key}";
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function putIfNotExists(string $key, $data, int $ttl = 0): bool
	{
		if ($this->has($key) === false) {
			return $this->put($key, $data, $ttl);
		}

		return false;
	}

	/**
	 * Fetch data from the cache and replace it. NULL will be returned if the item does not exist.
	 */
	public function getAndPut(string $key, mixed $data, int $ttl = 0): mixed
	{
		$storedValue = $this->get($key);

		$this->put($key, $data, $ttl);

		return $storedValue;
	}

	/**
	 * Fetch data from the cache and remove it. NULL will be returned if the item does not exist.
	 */
	public function getAndRemove(string $key): mixed
	{
		$storedValue = $this->get($key);

		if ($storedValue !== null) {
			$this->remove($key);
		}

		return $storedValue;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function getOrElse(string $key, callable $data, int $ttl = 0): mixed
	{
		$cached = $this->get($key);

		if ($cached === null) {
			$cached = $data();

			$this->put($key, $cached, $ttl);
		}

		return $cached;
	}
}
