<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\cache\stores;

/**
 * Memory store.
 *
 * @author  Frederic G. Østby
 */

class Memory implements \mako\cache\stores\StoreInterface
{
	/**
	 * Cache data.
	 *
	 * @var array
	 */

	protected $cache = [];

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
		$ttl = (((int) $ttl === 0) ? 31556926 : (int) $ttl) + time();

		$this->cache[$key] = ['data' => $data, 'ttl' => $ttl];
		
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
		return (isset($this->cache[$key]) && $this->cache[$key]['ttl'] > time());
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
		if(isset($this->cache[$key]))
		{
			if($this->cache[$key]['ttl'] > time())
			{
				return $this->cache[$key]['data'];
			}
			else
			{
				$this->remove($key);

				return false;
			}
		}
		else
		{
			return false;
		}
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
		if(isset($this->cache[$key]))
		{
			unset($this->cache[$key]);
			
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Clears the user cache.
	 *
	 * @access  public
	 * @return  boolean
	 */

	public function clear()
	{
		$this->cache = [];
		
		return true;
	}
}