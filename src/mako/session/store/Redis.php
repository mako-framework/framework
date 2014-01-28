<?php

namespace mako\session\store;

use \mako\redis\Redis as RedisClient;

/**
 * File store.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class Redis implements \mako\session\store\StoreInterface
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	/**
	 * Redis client
	 * 
	 * @var \mako\redis\Redis
	 */

	protected $redis;

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

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

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

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
		return unserialize($this->redis->get('sess_' . $sessionId));
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

/** -------------------- End of file -------------------- **/