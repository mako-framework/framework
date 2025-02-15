<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\throttle\store;

use DateTimeInterface;
use mako\chrono\Time;
use mako\redis\Redis as RedisClient;

use function hash;
use function time;

/**
 * Redis store.
 */
class Redis implements StoreInterface
{
	/**
	 * Constructor.
	 */
	public function __construct(
		protected RedisClient $redis,
		protected string $prefix = 'throttle:'
	) {
	}

	/**
	 * Returns the rate limit key.
	 */
	protected function getKey(string $key): string
	{
		return $this->prefix . hash('xxh128', $key);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getHits(string $key): int
	{
		return (int) $this->redis->get($this->getKey($key));
	}

	/**
	 * {@inheritDoc}
	 */
	public function getExpiration(string $key): ?DateTimeInterface
	{
		$ttl = $this->redis->ttl($this->getKey($key));

		return $ttl > 0 ? Time::now()->forward($ttl) : null;
	}

	/**
	 * {@inheritDoc}
	 */
	public function increment(string $key, DateTimeInterface $expiresAt): int
	{
		$lua = "return redis.call('exists', KEYS[1]) == 0 and redis.call('setex', KEYS[1], ARGV[1], ARGV[2])";

		$ttl = $expiresAt->getTimestamp() - time();

		$this->redis->eval($lua, 1, $this->getKey($key), $ttl, 0);

		return $this->redis->incrby($this->getKey($key), 1);
	}
}
