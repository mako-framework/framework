<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cache\stores;

use mako\redis\Redis as RedisClient;

use function is_numeric;
use function serialize;
use function unserialize;

/**
 * Redis store.
 */
class Redis extends Store implements IncrementDecrementInterface
{
	/**
	 * Redis client.
	 *
	 * @var \mako\redis\Redis
	 */
	protected $redis;

	/**
	 * Class whitelist.
	 *
	 * @var array|bool
	 */
	protected $classWhitelist;

	/**
	 * Constructor.
	 *
	 * @param \mako\redis\Redis $redis          Redis client
	 * @param array|bool        $classWhitelist Class whitelist
	 */
	public function __construct(RedisClient $redis, array|bool $classWhitelist = false)
	{
		$this->redis = $redis;

		$this->classWhitelist = $classWhitelist;
	}

	/**
	 * {@inheritDoc}
	 */
	public function put(string $key, mixed $data, int $ttl = 0): bool
	{
		$key = $this->getPrefixedKey($key);

		$data = is_numeric($data) ? $data : serialize($data);

		if($ttl === 0)
		{
			return (bool) $this->redis->set($key, $data);
		}

		return (bool) $this->redis->setex($key, $ttl, $data);
	}

	/**
	 * {@inheritDoc}
	 */
	public function putIfNotExists(string $key, mixed $data, int $ttl = 0): bool
	{
		$key = $this->getPrefixedKey($key);

		$data = is_numeric($data) ? $data : serialize($data);

		if($ttl === 0)
		{
			return (bool) $this->redis->setnx($key, $data);
		}

		$lua = "return redis.call('exists', KEYS[1]) == 0 and redis.call('setex', KEYS[1], ARGV[1], ARGV[2])";

		return (bool) $this->redis->eval($lua, 1, $key, $ttl, $data);
	}

	/**
	 * {@inheritDoc}
	 */
	public function increment(string $key, int $step = 1)
	{
		return $this->redis->incrby($this->getPrefixedKey($key), $step);
	}

	/**
	 * {@inheritDoc}
	 */
	public function decrement(string $key, int $step = 1)
	{
		return $this->redis->decrby($this->getPrefixedKey($key), $step);
	}

	/**
	 * {@inheritDoc}
	 */
	public function has(string $key): bool
	{
		return (bool) $this->redis->exists($this->getPrefixedKey($key));
	}

	/**
	 * {@inheritDoc}
	 */
	public function get(string $key): mixed
	{
		$data = $this->redis->get($this->getPrefixedKey($key));

		if($data === null)
		{
			return null;
		}

		return is_numeric($data) ? $data : unserialize($data, ['allowed_classes' => $this->classWhitelist]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function remove(string $key): bool
	{
		return (bool) $this->redis->del($this->getPrefixedKey($key));
	}

	/**
	 * {@inheritDoc}
	 */
	public function clear(): bool
	{
		return (bool) $this->redis->flushdb();
	}
}
