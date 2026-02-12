<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\session\stores;

use mako\redis\Redis as RedisClient;
use Override;
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
		protected string $prefix = 'mako:session:'
	) {
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function write(#[SensitiveParameter] string $sessionId, array $sessionData, int $dataTTL): void
	{
		$this->redis->set("{$this->prefix}{$sessionId}", serialize($sessionData), 'EX', $dataTTL);
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function read(#[SensitiveParameter] string $sessionId): array
	{
		$sessionData = $this->redis->get("{$this->prefix}{$sessionId}");

		return ($sessionData !== null) ? unserialize($sessionData, ['allowed_classes' => $this->classWhitelist]) : [];
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function delete(#[SensitiveParameter] string $sessionId): void
	{
		$this->redis->del("{$this->prefix}{$sessionId}");
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function gc(int $dataTTL): void
	{
		// Nothing here since redis handles this automatically
	}
}
