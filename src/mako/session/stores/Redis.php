<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\session\stores;

use mako\redis\Redis as RedisClient;

use function serialize;
use function unserialize;

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
		protected array|bool $classWhitelist = false,
		protected string $prefix = 'sess_'
	) {
	}

	/**
	 * {@inheritDoc}
	 */
	public function write(string $sessionId, array $sessionData, int $dataTTL): void
	{
		$this->redis->setex("{$this->prefix}{$sessionId}", $dataTTL, serialize($sessionData));
	}

	/**
	 * {@inheritDoc}
	 */
	public function read(string $sessionId): array
	{
		$sessionData = $this->redis->get("{$this->prefix}{$sessionId}");

		return ($sessionData !== null) ? unserialize($sessionData, ['allowed_classes' => $this->classWhitelist]) : [];
	}

	/**
	 * {@inheritDoc}
	 */
	public function delete(string $sessionId): void
	{
		$this->redis->del("{$this->prefix}{$sessionId}");
	}

	/**
	 * {@inheritDoc}
	 */
	public function gc(int $dataTTL): void
	{
		// Nothing here since redis handles this automatically
	}
}
