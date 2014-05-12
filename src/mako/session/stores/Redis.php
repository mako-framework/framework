<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\session\stores;

use \mako\redis\Redis as RedisClient;

/**
 * Redis store.
 *
 * @author  Frederic G. Østby
 */

class Redis implements \mako\session\stores\StoreInterface
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
	 * Writes session data.
	 *
	 * @access  public
	 * @param   string   $sessionId    Session id
	 * @param   string   $sessionData  Session data
	 * @param   int      $dataTTL      TTL in seconds
	 */

	public function write($sessionId, $sessionData, $dataTTL)
	{
		$this->redis->setex('sess_' . $sessionId, $dataTTL, serialize($sessionData));
	}

	/**
	 * Reads and returns session data.
	 *
	 * @access  public
	 * @param   string  $sessionId  Session id
	 * @return  string
	 */

	public function read($sessionId)
	{
		$sessionData = $this->redis->get('sess_' . $sessionId);

		return ($sessionData !== null) ? unserialize($sessionData) : [];
	}

	/**
	 * Destroys the session data assiciated with the provided id.
	 *
	 * @access  public
	 * @param   string   $sessionId  Session id
	 */

	public function delete($sessionId)
	{
		$this->redis->del('sess_' . $sessionId);
	}

	/**
	 * Garbage collector that deletes expired session data.
	 *
	 * @access  public
	 * @param   int      $dataTTL  Data TTL in seconds
	 */

	public function gc($dataTTL)
	{
		// Nothing here since redis handles this automatically
	}
}