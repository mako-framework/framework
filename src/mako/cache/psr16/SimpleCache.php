<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cache\psr16;

use DateInterval;
use DateTimeImmutable;
use mako\cache\psr16\exceptions\InvalidArgumentException;
use mako\cache\stores\StoreInterface;
use Override;
use Psr\SimpleCache\CacheInterface;

use function array_keys;
use function is_array;
use function iterator_to_array;
use function preg_match;

/**
 * Simple Cache adapter.
 */
class SimpleCache implements CacheInterface
{
	/**
	 * Constructor.
	 */
	public function __construct(
		protected StoreInterface $store
	) {
	}

	/**
	 * Returns a validated key.
	 */
	protected function getValidatedKey(string $key): string
	{
		if (empty($key)) {
			throw new InvalidArgumentException('A valid cache key must be a non-empty string.');
		}

		if (preg_match('/\{|\}|\@|\:|\(|\)|\/|\\\/', $key) === 1) {
			throw new InvalidArgumentException('A valid cache key can not contain any of the following characters: [ {}()/\@: ].');
		}

		return $key;
	}

	/**
	 * Returns a validated key list.
	 */
	protected function getValidatedKeys(iterable $keys): array
	{
		$validatedKeys = [];

		foreach ($keys as $key) {
			$validatedKeys[] = $this->getValidatedKey($key);
		}

		return $validatedKeys;
	}

	/**
	 * Calculates the TTL of the cache item in seconds.
	 */
	protected function calculateTTL(null|DateInterval|int $ttl): int
	{
		if ($ttl instanceof DateInterval) {
			$now = new DateTimeImmutable;

			$then = $now->add($ttl);

			return $then->getTimestamp() - $now->getTimestamp();
		}

		return $ttl ?? 0;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function get(string $key, mixed $default = null): mixed
	{
		return $this->store->get($this->getValidatedKey($key)) ?? $default;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function set(string $key, mixed $value, null|DateInterval|int $ttl = null): bool
	{
		return $this->store->put($this->getValidatedKey($key), $value, $this->calculateTTL($ttl));
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function delete(string $key): bool
	{
		return $this->store->remove($this->getValidatedKey($key));
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function clear(): bool
	{
		return $this->store->clear();
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function getMultiple(iterable $keys, mixed $default = null): iterable
	{
		$values = [];

		foreach ($this->getValidatedKeys($keys) as $key) {
			$values[$key] = $this->store->get($key) ?? $default;
		}

		return $values;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function setMultiple(iterable $values, null|DateInterval|int $ttl = null): bool
	{
		if (!is_array($values)) {
			$values = iterator_to_array($values);
		}

		$ttl = $this->calculateTTL($ttl);

		$success = true;

		foreach ($this->getValidatedKeys(array_keys($values)) as $key) {
			$success = $success && $this->store->put($key, $values[$key], $ttl);
		}

		return $success;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function deleteMultiple(iterable $keys): bool
	{
		$success = true;

		foreach ($this->getValidatedKeys($keys) as $key) {
			$success = $success && $this->store->remove($key);
		}

		return $success;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function has(string $key): bool
	{
		return $this->store->has($this->getValidatedKey($key));
	}
}
