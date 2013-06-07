<?php

namespace mako\cache;

/**
 * Non-persistent memory based cache.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class Memory extends \mako\cache\Adapter
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	/**
	 * Cache data.
	 *
	 * @var array
	 */

	protected $cache = array();

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
		// Nothing here
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
		$ttl = (((int) $ttl === 0) ? 31556926 : (int) $ttl) + time();

		$this->cache[$key] = array('data' => $value, 'ttl' => $ttl);
		
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
	 * Increases a stored number. Will return the incremented value on success and FALSE on failure.
	 * 
	 * @access  public
	 * @param   string  $key      Cache key
	 * @param   int     $ammount  (optional) Ammoun that the number should be increased by
	 * @return  mixed
	 */

	public function increment($key, $ammount = 1)
	{
		if(!$this->has($key) || !is_numeric($this->cache['key']['data']))
		{
			return false;
		}

		$this->cache[$key]['data'] += $ammount;

		return (int) $this->cache[$key]['data'];
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
		if(!$this->has($key) || !is_numeric($this->cache[$key]['data']))
		{
			return false;
		}

		$this->cache[$key]['data'] -= $ammount;

		return (int) $this->cache[$key]['data'];
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
		$this->cache = array();
		
		return true;
	}
}

/** -------------------- End of file -------------------- **/