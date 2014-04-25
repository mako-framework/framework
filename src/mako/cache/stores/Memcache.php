<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\cache\stores;

use \Memcache as PHP_Memcache;

/**
 * Memcache store.
 *
 * @author  Frederic G. Østby
 */

class Memcache implements \mako\cache\stores\StoreInterface
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	/**
	 * Memcache instance.
	 *
	 * @var \Memcache
	 */

	protected $memcache;

	/**
	 * Compression level.
	 *
	 * @var int
	 */

	protected $compressionLevel = 0;

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
		$this->memcache = new PHP_Memcache();

		if($compressData === true)
		{
			$this->compressionLevel = MEMCACHE_COMPRESSED;
		}

		// Add servers to the connection pool

		foreach($servers as $server)
		{
			$this->memcache->addServer($server['server'], $server['port'], $server['persistent_connection'], $server['weight'], $timeout);
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

	public function put($key, $data, $ttl = 0)
	{
		if($ttl !== 0)
		{
			$ttl += time();
		}

		if($this->memcache->replace($key, $data, $this->compressionLevel, $ttl) === false)
		{
			return $this->memcache->set($key, $data, $this->compressionLevel, $ttl);
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
		return ($this->memcache->get($key) !== false);
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
		return $this->memcache->get($key);
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
		return $this->memcache->delete($key, 0);
	}

	/**
	 * Clears the user cache.
	 *
	 * @access  public
	 * @return  boolean
	 */

	public function clear()
	{
		return $this->memcache->flush();
	}
}