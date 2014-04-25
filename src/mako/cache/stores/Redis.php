<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\cache\stores;

use \mako\redis\Redis as RedisClient;

/**
 * Redis store.
 *
 * @author  Frederic G. Østby
 */

class Redis implements \mako\cache\stores\StoreInterface
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
	 * Store data in the cache.
	 *
	 * @access  public
	 * @param   string   $key    Cache key
	 * @param   mixed    $data   The data to store
	 * @param   int      $ttl    (optional) Time to live
	 * @return  boolean
	 */

	public function put($key, $data, $ttl = 0)
	{
		$this->redis->set($key, (is_numeric($data) ? $data : serialize($data)));
		
		if($ttl !== 0)
		{
			$this->redis->expire($key, $ttl);
		}

		return true;
	}

	/**
	 * Returns TRUE if the cache key exists and FALSE if not.
	 * 
	 * @access  public
	 * @param   string   $key  Cache key
	 * @return  boolean
	 */

	public function has($key)
	{
		return (bool) $this->redis->exists($key);
	}

	/**
	 * Fetch data from the cache.
	 *
	 * @access  public
	 * @param   string  $key  Cache key
	 * @return  mixed
	 */

	public function get($key)
	{
		$data = $this->redis->get($key);

		return ($data === null) ? false : (is_numeric($data) ? $data : unserialize($data));
	}

	/**
	 * Delete data from the cache.
	 *
	 * @access  public
	 * @param   string   $key  Cache key
	 * @return  boolean
	 */

	public function remove($key)
	{
		return (bool) $this->redis->del($key);
	}

	/**
	 * Clears the user cache.
	 *
	 * @access  public
	 * @return  boolean
	 */

	public function clear()
	{
		return (bool) $this->redis->flushdb();
	}
}