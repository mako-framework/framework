<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cache\stores;

use mako\redis\Redis as RedisClient;
use Override;

use function array_chunk;
use function is_numeric;
use function serialize;
use function unserialize;

/**
 * Redis store.
 */
class Redis extends Store implements IncrementDecrementInterface
{
	/**
	 * Constructor.
	 */
	public function __construct(
		protected RedisClient $redis,
		protected array|bool $classWhitelist = false
	) {
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function put(string $key, mixed $data, int $ttl = 0): bool
	{
		$key = $this->getPrefixedKey($key);

		$data = is_numeric($data) ? $data : serialize($data);

		if ($ttl === 0) {
			return (bool) $this->redis->set($key, $data);
		}

		return (bool) $this->redis->set($key, $data, 'EX', $ttl);
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function putIfNotExists(string $key, mixed $data, int $ttl = 0): bool
	{
		$key = $this->getPrefixedKey($key);

		$data = is_numeric($data) ? $data : serialize($data);

		if ($ttl === 0) {
			return (bool) $this->redis->set($key, $data, 'NX');
		}

		return (bool) $this->redis->set($key, $data, 'NX', 'EX', $ttl);
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function increment(string $key, int $step = 1): false|int
	{
		return $this->redis->incrby($this->getPrefixedKey($key), $step);
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function decrement(string $key, int $step = 1): false|int
	{
		return $this->redis->decrby($this->getPrefixedKey($key), $step);
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function has(string $key): bool
	{
		return (bool) $this->redis->exists($this->getPrefixedKey($key));
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function get(string $key): mixed
	{
		$data = $this->redis->get($this->getPrefixedKey($key));

		if ($data === null) {
			return null;
		}

		return is_numeric($data) ? $data : unserialize($data, ['allowed_classes' => $this->classWhitelist]);
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function remove(string $key): bool
	{
		return (bool) $this->redis->del($this->getPrefixedKey($key));
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function clear(): bool
	{
		$keys = $this->redis->keys($this->getPrefixedKey('*'));

		if (!empty($keys)) {
			foreach (array_chunk($keys, 100) as $chunk) {
				$this->redis->del(...$chunk);
			}
		}

		return true;
	}
}
