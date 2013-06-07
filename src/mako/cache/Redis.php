<?php

namespace mako\cache;

use \mako\Redis as MRedis;
use \RuntimeException;

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
	 * @var \mako\Redis
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
		$this->redis->set($this->identifier . $key, (is_numeric($value) ? $value : serialize($value)));
		
		if($ttl !== 0)
		{
			$this->redis->expire($this->identifier . $key, $ttl);
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
		$data = $this->redis->get($this->identifier . $key);

		return ($data === null) ? false : (is_numeric($data) ? $data : unserialize($data));
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
		return (bool) $this->redis->exists($this->identifier . $key);
	}

	/**
	 * Increases a stored number. Will return the incremented value on success and FALSE on failure.
	 * 
	 * @access  public
	 * @param   string  $key      Cache key
	 * @param   int     $ammount  (optional) Ammoun that the number should be increased by
	 * @return  mixed
	 */

	public function increment($key, $ammount = 1)
	{
		try
		{
			if($this->has($key))
			{
				$incremented = $this->redis->incrby($this->identifier . $key, $ammount);

				$ttl = $this->redis->ttl($this->identifier . $key);

				if($ttl > 0)
				{
					$this->redis->expire($this->identifier . $key, $ttl);
				}

				return $incremented;
			}

			return false;
		}
		catch(RuntimeException $e)
		{
			return false;
		}
	}

	/**
	 * Decrements a stored number. Will return the decremented value on success and FALSE on failure.
	 * 
	 * @access  public
	 * @param   string  $key      Cache key
	 * @param   int     $ammount  (optional) Ammoun that the number should be decremented by
	 * @return  mixed
	 */

	public function decrement($key, $ammount = 1)
	{
		try
		{
			if($this->has($key))
			{
				$incremented = $this->redis->decrby($this->identifier . $key, $ammount);

				$ttl = $this->redis->ttl($this->identifier . $key);

				if($ttl > 0)
				{
					$this->redis->expire($this->identifier . $key, $ttl);
				}

				return $incremented;
			}

			return false;
		}
		catch(RuntimeException $e)
		{
			return false;
		}
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
		return (bool) $this->redis->del($this->identifier . $key);
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

/** -------------------- End of file -------------------- **/