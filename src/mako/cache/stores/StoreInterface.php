<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\cache\stores;

/**
 * Store interface.
 *
 * @author  Frederic G. Østby
 */
interface StoreInterface
{
	/**
	 * Store data in the cache.
	 *
	 * @access  public
	 * @param   string   $key    Cache key
	 * @param   mixed    $data   The data to store
	 * @param   int      $ttl    Time to live
	 * @return  boolean
	 */
	public function put($key, $data, $ttl = 0);

	/**
	 * Returns TRUE if the cache key exists and FALSE if not.
	 *
	 * @access  public
	 * @param   string   $key  Cache key
	 * @return  boolean
	 */
	public function has($key);

	/**
	 * Fetch data from the cache.
	 *
	 * @access  public
	 * @param   string  $key  Cache key
	 * @return  mixed
	 */
	public function get($key);

	/**
	 * Fetch data from the cache or store it if it doesn't already exist.
	 *
	 * @access  public
	 * @param   string    $key   Cache key
	 * @param   callable  $data  Closure that returns the data we want to store
	 * @param   int       $ttl   Time to live
	 * @return  mixed
	 */
	public function getOrElse($key, callable $callable, $ttl = 0);

	/**
	 * Delete data from the cache.
	 *
	 * @access  public
	 * @param   string   $key  Cache key
	 * @return  boolean
	 */
	public function remove($key);

	/**
	 * Clears the cache.
	 *
	 * @access  public
	 * @return  boolean
	 */
	public function clear();
}