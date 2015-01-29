<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\session\stores;

use mako\redis\Redis as RedisClient;
use mako\session\stores\StoreInterface;

/**
 * Redis store.
 *
 * @author  Frederic G. Østby
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
	 * Constructor.
	 *
	 * @access  public
	 * @param   \mako\redis\Redis  $redis  Redis client
	 */

	public function __construct(RedisClient $redis)
	{
		$this->redis = $redis;
	}

	/**
	 * {@inheritdoc}
	 */

	public function write($sessionId, $sessionData, $dataTTL)
	{
		$this->redis->setex('sess_' . $sessionId, $dataTTL, serialize($sessionData));
	}

	/**
	 * {@inheritdoc}
	 */

	public function read($sessionId)
	{
		$sessionData = $this->redis->get('sess_' . $sessionId);

		return ($sessionData !== null) ? unserialize($sessionData) : [];
	}

	/**
	 * {@inheritdoc}
	 */

	public function delete($sessionId)
	{
		$this->redis->del('sess_' . $sessionId);
	}

	/**
	 * {@inheritdoc}
	 */

	public function gc($dataTTL)
	{
		// Nothing here since redis handles this automatically
	}
}