<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\cache;

use Closure;

use mako\cache\stores\StoreInterface;

/**
 * Cache wrapper.
 *
 * @author  Frederic G. Østby
 */

class Cache
{
	/**
	 * Cache store.
	 * 
	 * @var \mako\cache\stores\StoreInterface
	 */

	protected $store;

	/**
	 * Cache prefix.
	 * 
	 * @var string
	 */

	protected $prefix;

	/**
	 * Constructor.
	 * 
	 * @access  public
	 * @param   \mako\cache\stores\StoreInterface  $store   Cache store
	 * @param   string                             $prefix  Cache prefix
	 */

	public function __construct(StoreInterface $store, $prefix = null)
	{
		$this->store = $store;

		$this->prefix = $prefix;
	}

	/**
	 * Returns a prefixed cache key.
	 * 
	 * @access  protected
	 * @param   string     $key  Cache key
	 * @return  string
	 */

	protected function prefixedKey($key)
	{
		return empty($this->prefix) ? $key : $this->prefix . '.' . $key;
	}

	/**
	 * Store data in the cache.
	 *
	 * @access  public
	 * @param   string   $key    Cache key
	 * @param   mixed    $data   The data to store
	 * @param   int      $ttl    Time to live
	 * @return  boolean
	 */

	public function put($key, $data, $ttl = 0)
	{
		return $this->store->put($this->prefixedKey($key), $data, $ttl);
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
		return $this->store->has($this->prefixedKey($key));
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
		return $this->store->get($this->prefixedKey($key));
	}

	/**
	 * Fetch data from the cache or store it if it doesn't already exist.
	 * 
	 * @access  public
	 * @param   string    $key  Cache key
	 * @param   \Closure  $data  Closure that returns the data we want to store
	 * @param   int       $ttl    Time to live
	 * @return  mixed
	 */

	public function getOrElse($key, Closure $data, $ttl = 0)
	{
		if(!$this->store->has($this->prefixedKey($key)))
		{
			$data = $data();

			$this->store->put($this->prefixedKey($key), $data, $ttl);

			return $data;
		}
		else
		{
			return $this->store->get($this->prefixedKey($key));
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
		return $this->store->remove($this->prefixedKey($key));
	}

	/**
	 * Clears the user cache.
	 *
	 * @access  public
	 * @return  boolean
	 */

	public function clear()
	{
		return $this->store->clear();
	}
}