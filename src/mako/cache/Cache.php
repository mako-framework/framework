<?php

namespace mako\cache;

use \mako\cache\adapters\AdapterInterface;

/**
 * Cache wrapper.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class Cache
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	/**
	 * Cache adapter.
	 * 
	 * @var \mako\cache\adapters\AdapterInterface
	 */

	protected $adapter;

	/**
	 * Cache prefix.
	 * 
	 * @var string
	 */

	protected $prefix;

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	 * Constructor.
	 * 
	 * @access  public
	 * @param   \mako\cache\adapters\AdapterInterface  $adapters  Cache adapter
	 * @param   string                                 $prefix    (optional) Cache prefix
	 */

	public function __construct(AdapterInterface $adapter, $prefix = null)
	{
		$this->adapter = $adapter;

		$this->prefix = $prefix;
	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	 * Returns a prefixed cache key.
	 * 
	 * @access  protected
	 * @param   string     $key  Cache key
	 * @return  string
	 */

	protected function prefixedKey($key)
	{
		return $this->prefix . ':' . $key;
	}

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
		return $this->adapter->write($this->prefixedKey($key), $data, $ttl);
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
		return $this->adapter->has($this->prefixedKey($key));
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
		return $this->adapter->read($this->prefixedKey($key));
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
		return $this->adapter->delete($this->prefixedKey($key));
	}

	/**
	 * Clears the user cache.
	 *
	 * @access  public
	 * @return  boolean
	 */

	public function clear($key)
	{
		return $this->adapter->clear($this->prefixedKey($key));
	}
}

/** -------------------- End of file -------------------- **/