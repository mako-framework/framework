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
		$key = $this->getKey($key);

		[, $count] = $this->redis->pipeline(static function ($redis) use ($key, $expiresAt): void {
			$redis->set($key, 0, 'NX', 'EX', $expiresAt->getTimestamp() - time());
			$redis->incrBy($key, 1);
		});

		return $count;
	}
}
