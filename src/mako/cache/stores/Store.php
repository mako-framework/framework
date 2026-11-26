<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cache\stores;

use InvalidArgumentException;
use Override;
use Time\Duration;

/**
 * Base store.
 */
abstract class Store implements StoreInterface
{
	/**
	 * Prefix.
	 */
	protected string $prefix = 'mako:cache:';

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
		return "{$this->prefix}{$key}";
	}

	/**
	 * Normalizes the TTL to an integer number of seconds.
	 *
	 * Durations with a fractional second are rounded up to avoid
	 * a sub-second TTL being truncated to 0 ("cache forever").
	 */
	protected function normalizeTtl(Duration|int $ttl): int
	{
		if ($ttl instanceof Duration) {
			if ($ttl->negative) {
				throw new InvalidArgumentException('The TTL cannot be negative.');
			}

			return $ttl->nanoseconds > 0 ? $ttl->seconds + 1 : $ttl->seconds;
		}

		if ($ttl < 0) {
			throw new InvalidArgumentException('The TTL cannot be negative.');
		}

		return $ttl;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function putIfNotExists(string $key, mixed $data, Duration|int $ttl = 0): bool
	{
		if ($this->has($key) === false) {
			return $this->put($key, $data, $ttl);
		}

		return false;
	}

	/**
	 * Fetch data from the cache and replace it. Null will be returned if the item does not exist.
	 */
	public function getAndPut(string $key, mixed $data, Duration|int $ttl = 0): mixed
	{
		$storedValue = $this->get($key);

		$this->put($key, $data, $ttl);

		return $storedValue;
	}

	/**
	 * Fetch data from the cache and remove it. Null will be returned if the item does not exist.
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
	public function getOrElse(string $key, callable $data, Duration|int $ttl = 0): mixed
	{
		$cached = $this->get($key);

		if ($cached === null) {
			$cached = $data();

			$this->put($key, $cached, $ttl);
		}

		return $cached;
	}
}
