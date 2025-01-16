<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\session\stores;

use mako\redis\Redis as RedisClient;
use SensitiveParameter;

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
	public function write(#[SensitiveParameter] string $sessionId, array $sessionData, int $dataTTL): void
	{
		$this->redis->set("{$this->prefix}{$sessionId}", serialize($sessionData), 'EX', $dataTTL);
	}

	/**
	 * {@inheritDoc}
	 */
	public function read(#[SensitiveParameter] string $sessionId): array
	{
		$sessionData = $this->redis->get("{$this->prefix}{$sessionId}");

		return ($sessionData !== null) ? unserialize($sessionData, ['allowed_classes' => $this->classWhitelist]) : [];
	}

	/**
	 * {@inheritDoc}
	 */
	public function delete(#[SensitiveParameter] string $sessionId): void
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
