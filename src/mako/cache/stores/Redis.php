<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\cache\stores;

use mako\cache\stores\StoreInterface;
use mako\cache\stores\traits\GetOrElseTrait;
use mako\redis\Redis as RedisClient;

/**
 * Redis store.
 *
 * @author  Frederic G. Østby
 */
class Redis implements StoreInterface
{
	use GetOrElseTrait;

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
	 * @access  public
	 * @param   \mako\redis\Redis  $redis           Redis client
	 * @param   bool|array         $classWhitelist  Class whitelist
	 */
	public function __construct(RedisClient $redis, $classWhitelist = false)
	{
		$this->redis = $redis;

		$this->classWhitelist = $classWhitelist;
	}

	/**
	 * {@inheritdoc}
	 */
	public function put(string $key, $data, int $ttl = 0): bool
	{
		$this->redis->set($key, (is_numeric($data) ? $data : serialize($data)));

		if($ttl !== 0)
		{
			$this->redis->expire($key, $ttl);
		}

		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function has(string $key): bool
	{
		return (bool) $this->redis->exists($key);
	}

	/**
	 * {@inheritdoc}
	 */
	public function get(string $key)
	{
		$data = $this->redis->get($key);

		return ($data === null) ? false : (is_numeric($data) ? $data : unserialize($data, ['allowed_classes' => $this->classWhitelist]));
	}

	/**
	 * {@inheritdoc}
	 */
	public function remove(string $key): bool
	{
		return (bool) $this->redis->del($key);
	}

	/**
	 * {@inheritdoc}
	 */
	public function clear(): bool
	{
		return (bool) $this->redis->flushdb();
	}
}