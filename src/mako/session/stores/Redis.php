<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\session\stores;

use mako\redis\Redis as RedisClient;

/**
 * Redis store.
 *
 * @author Frederic G. Østby
 */
class Redis implements StoreInterface
{
	/**
	 * Redis client
	 *
	 * @var \mako\redis\Redis
	 */
	protected $redis;

	/**
	 * Class whitelist.
	 *
	 * @var bool|array
	 */
	protected $classWhitelist;

	/**
	 * Constructor.
	 *
	 * @param \mako\redis\Redis $redis          Redis client
	 * @param bool|array        $classWhitelist Class whitelist
	 */
	public function __construct(RedisClient $redis, $classWhitelist = false)
	{
		$this->redis = $redis;

		$this->classWhitelist = $classWhitelist;
	}

	/**
	 * {@inheritdoc}
	 */
	public function write(string $sessionId, array $sessionData, int $dataTTL)
	{
		$this->redis->setex('sess_' . $sessionId, $dataTTL, serialize($sessionData));
	}

	/**
	 * {@inheritdoc}
	 */
	public function read(string $sessionId): array
	{
		$sessionData = $this->redis->get('sess_' . $sessionId);

		return ($sessionData !== null) ? unserialize($sessionData, ['allowed_classes' => $this->classWhitelist]) : [];
	}

	/**
	 * {@inheritdoc}
	 */
	public function delete(string $sessionId)
	{
		$this->redis->del('sess_' . $sessionId);
	}

	/**
	 * {@inheritdoc}
	 */
	public function gc(int $dataTTL)
	{
		// Nothing here since redis handles this automatically
	}
}
