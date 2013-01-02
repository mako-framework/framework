<?php

namespace mako\cache;

use \mako\Redis as MRedis;

/**
 * Redis adapter.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class Redis extends \mako\cache\Adapter
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	/**
	 * Redis object.
	 *
	 * @var mako\Redis
	 */

	protected $redis;

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	 * Constructor.
	 *
	 * @access  public
	 * @param   array   $config  Configuration
	 */

	public function __construct(array $config)
	{
		parent::__construct($config['identifier']);
		
		$this->redis = new MRedis($config['configuration']);
	}

	/**
	 * Destructor.
	 *
	 * @access  public
	 */

	public function __destruct()
	{
		if($this->redis !== null)
		{
			$this->redis = null;
		}
	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	 * Store variable in the cache.
	 *
	 * @access  public
	 * @param   string   $key    Cache key
	 * @param   mixed    $value  The variable to store
	 * @param   int      $ttl    (optional) Time to live
	 * @return  boolean
	 */

	public function write($key, $value, $ttl = 0)
	{
		$this->redis->set("{$this->identifier}_{$key}", serialize($value));
		
		if($ttl !== 0)
		{
			$this->redis->expire("{$this->identifier}_{$key}", $ttl);
		}

		return true;
	}

	/**
	 * Fetch variable from the cache.
	 *
	 * @access  public
	 * @param   string  $key  Cache key
	 * @return  mixed
	 */

	public function read($key)
	{
		$data = $this->redis->get("{$this->identifier}_{$key}");

		return ($data === null) ? false : unserialize($data);
	}

	/**
	 * Delete a variable from the cache.
	 *
	 * @access  public
	 * @param   string   $key  Cache key
	 * @return  boolean
	 */

	public function delete($key)
	{
		return (bool) $this->redis->del("{$this->identifier}_{$key}");
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

/** -------------------- End of file --------------------**/