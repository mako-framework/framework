<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\cache\adapters;

/**
 * Memory adapter.
 *
 * @author  Frederic G. Østby
 */

class Null implements \mako\cache\adapters\AdapterInterface
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	/**
	 * Cache data.
	 *
	 * @var array
	 */

	protected $cache = [];

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	// Nothing here

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

	public function write($key, $data, $ttl = 0)
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

	public function read($key)
	{
		if(isset($this->cache[$key]))
		{
			if($this->cache[$key]['ttl'] > time())
			{
				return $this->cache[$key]['data'];
			}
			else
			{
				$this->delete($key);

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

	public function delete($key)
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