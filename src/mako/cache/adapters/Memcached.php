<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\cache\adapters;

use \Memcached as PHP_Memcached;

/**
 * Memcached adapter.
 *
 * @author  Frederic G. Østby
 */

class Memcached implements \mako\cache\adapters\AdapterInterface
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	/**
	 * Memcached instance.
	 *
	 * @var \Memcached
	 */

	protected $memcached;

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	 * Constructor.
	 *
	 * @access  public
	 * @param   array    $servers       Memcache servers
	 * @param   int      $timeout       (optional) Timeout in seconds
	 * @param   boolean  $compressData  (optional) Compress data?
	 */

	public function __construct(array $servers, $timeout = 1, $compressData = false)
	{
		$this->memcached = new PHP_Memcached();
		
		if($timtout !== 1)
		{
			$this->memcached->setOption(PHP_Memcached::OPT_CONNECT_TIMEOUT, ($timeout * 1000)); // Multiply by 1000 to convert to ms
		}

		if($compressData === false)
		{
			$this->memcached->setOption(PHP_Memcached::OPT_COMPRESSION, false);
		}

		// Add servers to the connection pool

		foreach($servers as $server)
		{
			$this->memcached->addServer($server['server'], $server['port'], $server['weight']);
		}
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

	public function write($key, $data, $ttl = 0)
	{
		if($ttl !== 0)
		{
			$ttl += time();
		}

		if($this->memcached->replace($key, $data, $ttl) === false)
		{
			return $this->memcached->set($key, $data, $ttl);
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
		return ($this->memcached->get($key) !== false);
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
		return $this->memcached->get($key);
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
		return $this->memcached->delete($key, 0);
	}

	/**
	 * Clears the user cache.
	 *
	 * @access  public
	 * @return  boolean
	 */

	public function clear()
	{
		return $this->memcached->flush();
	}
}